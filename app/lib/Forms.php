<?php
/**
 * Website forms: the Sprintings quote forms, contact form and newsletter sign-up (definitions in
 * QuoteForms.php). Submissions are stored in the dashboard inbox (with any artwork files, kept
 * outside the public web space), emailed to the notification addresses, answered with an
 * auto-reply, and optionally forwarded as JSON to a webhook (Settings → Forms & Integrations).
 */
require_once __DIR__ . '/QuoteForms.php'; // also loaded by bootstrap

final class Forms
{
    /** Where artwork uploads are stored. app/ is blocked from the web by app/.htaccess. */
    public const FILES_DIR = STORAGE . '/submissions';

    /** Per-IP limits: submissions per 10 minutes and uploaded bytes per hour. */
    private const MAX_SUBMISSIONS = 5;
    private const MAX_UPLOAD_BYTES_PER_HOUR = 300 * 1024 * 1024;

    public static function token(): string
    {
        $t = (string)time();
        return $t . '.' . substr(sign('form' . $t), 0, 24);
    }

    public static function validToken(string $token): bool
    {
        [$t, $sig] = array_pad(explode('.', $token, 2), 2, '');
        if (!ctype_digit($t) || !hash_equals(substr(sign('form' . $t), 0, 24), $sig)) {
            return false;
        }
        $age = time() - (int)$t;
        return $age >= 2 && $age <= 86400;
    }

    /** Field definitions of a form, keyed by field name. */
    public static function fields(string $type): array
    {
        $out = [];
        foreach (QuoteForms::get($type)['fields'] ?? [] as $f) {
            $out[$f['name']] = $f;
        }
        return $out;
    }

    public static function handle(): void
    {
        $isAjax = str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json');
        $fail = function (string $msg, int $code = 422) use ($isAjax) {
            if ($isAjax) {
                json_out(['ok' => false, 'error' => $msg], $code);
            }
            http_response_code($code);
            header('Content-Type: text/html; charset=utf-8');
            $phone = (string)setting('phone');
            echo '<!doctype html><meta charset="utf-8"><meta name="viewport" content="width=device-width"><meta name="robots" content="noindex"><title>We couldn\'t send your request</title><body style="font-family:system-ui,sans-serif;max-width:560px;margin:4rem auto;padding:0 1rem;color:#242c33"><h1>We couldn\'t send your request</h1><p>' . e($msg) . '</p><p><a href="javascript:history.back()">Go back</a>' . ($phone ? ' or call <a href="' . e(tel_href($phone)) . '">' . e($phone) . '</a>' : '') . '.</p>';
            exit;
        };
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            $fail('Invalid request.', 405);
        }
        // A POST larger than post_max_size arrives with empty $_POST and $_FILES
        $len = (int)($_SERVER['CONTENT_LENGTH'] ?? 0);
        if (!$_POST && $len > 0) {
            $fail('Your files are too large to send in one go. Please send up to ' . QuoteForms::UPLOAD_MAX_FILES . ' files of up to 50 MB each, or email them to ' . setting('email') . '.', 413);
        }
        $type = (string)($_POST['_form'] ?? '');
        $def = QuoteForms::get($type);
        if (!$def) {
            $fail('Unknown form.');
        }
        // Spam protection: honeypot + signed timestamp + rate limits
        if (!empty($_POST['website']) || !self::validToken((string)($_POST['_token'] ?? ''))) {
            $fail('Your session expired. Please refresh the page and try again.');
        }
        $since = date('Y-m-d H:i:s', time() - 600);
        if ((int)DB::val('SELECT COUNT(*) FROM submissions WHERE ip = ? AND created_at > ?', [client_ip(), $since]) >= self::MAX_SUBMISSIONS) {
            $fail('Too many requests. Please call us instead.', 429);
        }

        $product = null;
        if (($slug = (string)($_POST['_product'] ?? '')) !== '' && preg_match('/^[a-z0-9\-]+$/', $slug)) {
            $product = Content::service($slug);
        }

        $data = [];
        $fileField = null;
        foreach ($def['fields'] as $f) {
            $name = $f['name'];
            if ($f['type'] === 'file') {
                $fileField = $f;
                continue;
            }
            $v = trim((string)($_POST[$name] ?? ''));
            $v = mb_substr(strip_tags($v), 0, $f['type'] === 'textarea' ? 5000 : 250);
            if (!empty($f['required']) && $v === '') {
                $fail('Please complete the "' . $f['label'] . '" field.');
            }
            if ($f['type'] === 'email' && $v !== '' && !filter_var($v, FILTER_VALIDATE_EMAIL)) {
                $fail('Please enter a valid email address.');
            }
            if ($f['type'] === 'tel' && $v !== '' && strlen(digits($v)) < 10) {
                $fail('Please enter a valid phone number, including the area code.');
            }
            if ($f['type'] === 'number' && $v !== '' && !is_numeric($v)) {
                $fail('Please enter a number in the "' . $f['label'] . '" field.');
            }
            $data[$name] = $v;
        }
        if (!empty($def['consent']) && empty($_POST['consent'])) {
            $fail('Please confirm we may contact you about your request.');
        }

        // Artwork files: validate everything before anything is stored
        $files = $fileField ? self::collectFiles($fail) : [];
        if ($files) {
            $bytes = array_sum(array_column($files, 'size'));
            $recent = (int)DB::val('SELECT COALESCE(SUM(size),0) FROM submission_files WHERE ip = ? AND created_at > ?', [client_ip(), date('Y-m-d H:i:s', time() - 3600)]);
            if ($recent + $bytes > self::MAX_UPLOAD_BYTES_PER_HOUR) {
                $fail('You have uploaded a lot of files in the last hour. Please email your artwork to ' . setting('email') . ' instead.', 429);
            }
        }

        $name = trim((string)($data['name'] ?? trim(($data['first_name'] ?? '') . ' ' . ($data['last_name'] ?? ''))));
        $page = mb_substr((string)($_POST['_page'] ?? ''), 0, 250);
        $productTitle = $product ? (string)$product['title'] : '';
        $id = DB::insert('submissions', [
            'form' => $type,
            'name' => mb_substr($name, 0, 190),
            'email' => $data['email'] ?? '',
            'phone' => $data['phone'] ?? '',
            'product' => mb_substr($productTitle, 0, 190),
            'data' => json_encode($data, JSON_UNESCAPED_UNICODE),
            'page' => $page,
            'ip' => client_ip(),
            'status' => 'new',
            'created_at' => now(),
        ]);
        $stored = self::storeFiles($id, $files);
        if (count($stored) < count($files)) {
            log_error('Form ' . $type . ' #' . $id . ': ' . (count($files) - count($stored)) . ' file(s) could not be saved');
        }

        if (self::forward($type, $def, $data, $page, $productTitle, $stored)) {
            DB::update('submissions', ['forwarded' => 1], 'id = ?', [$id]);
        }
        self::notify($id, $type, $def, $data, $page, $productTitle, $stored);
        if ($def['kind'] !== 'newsletter') {
            self::autoReply($data);
        }

        $thanks = url('thank-you/');
        if ($isAjax) {
            json_out(['ok' => true, 'message' => $def['success'], 'redirect' => $def['kind'] === 'newsletter' ? '' : $thanks]);
        }
        redirect($thanks . ($def['kind'] === 'newsletter' ? '?signup=1' : ''), 303);
    }

    /** Validated uploads from $_FILES['files'] (multiple). */
    private static function collectFiles(callable $fail): array
    {
        $in = $_FILES['files'] ?? null;
        if (!$in || !isset($in['name'])) {
            return [];
        }
        $names = (array)$in['name'];
        $out = [];
        foreach ($names as $i => $orig) {
            $err = (int)((array)$in['error'])[$i];
            if ($err === UPLOAD_ERR_NO_FILE || (string)$orig === '') {
                continue;
            }
            $orig = mb_substr(basename(str_replace('\\', '/', (string)$orig)), 0, 250);
            if ($err === UPLOAD_ERR_INI_SIZE || $err === UPLOAD_ERR_FORM_SIZE) {
                $fail('"' . $orig . '" is larger than 50 MB.', 413);
            }
            if ($err !== UPLOAD_ERR_OK) {
                $fail('"' . $orig . '" did not upload completely. Please try again.');
            }
            $tmp = (string)((array)$in['tmp_name'])[$i];
            $size = (int)((array)$in['size'])[$i];
            if (!is_uploaded_file($tmp)) {
                $fail('Upload failed. Please try again.');
            }
            if ($size > QuoteForms::UPLOAD_MAX_BYTES) {
                $fail('"' . $orig . '" is larger than 50 MB.', 413);
            }
            $ext = strtolower(pathinfo($orig, PATHINFO_EXTENSION));
            if (!isset(QuoteForms::UPLOAD_TYPES[$ext])) {
                $fail('"' . $orig . '" is not an accepted file type. Please send PDF, AI, EPS, PSD, PNG, JPG, TIFF or ZIP files.');
            }
            $mime = self::detectMime($tmp);
            if (!in_array($mime, QuoteForms::UPLOAD_TYPES[$ext], true)) {
                $fail('"' . $orig . '" does not look like a valid .' . $ext . ' file.');
            }
            $out[] = ['tmp' => $tmp, 'name' => $orig, 'ext' => $ext, 'mime' => $mime, 'size' => $size];
            if (count($out) > QuoteForms::UPLOAD_MAX_FILES) {
                $fail('Please send no more than ' . QuoteForms::UPLOAD_MAX_FILES . ' files. Zip them together, or email them to ' . setting('email') . '.');
            }
        }
        return $out;
    }

    private static function detectMime(string $file): string
    {
        if (class_exists('finfo')) {
            $m = (string)(new finfo(FILEINFO_MIME_TYPE))->file($file);
        } elseif (function_exists('mime_content_type')) {
            $m = (string)@mime_content_type($file);
        } else {
            $m = 'application/octet-stream';
        }
        return strtolower($m);
    }

    /** Move validated uploads to app/storage/submissions/YYYY/MM/<random>.<ext> and record them. */
    private static function storeFiles(int $submissionId, array $files): array
    {
        $stored = [];
        if (!$files) {
            return $stored;
        }
        $sub = date('Y/m');
        $dir = self::FILES_DIR . '/' . $sub;
        if (!is_dir($dir) && !@mkdir($dir, 0750, true)) {
            log_error('Could not create ' . $dir);
            return $stored;
        }
        foreach ($files as $f) {
            $rel = $sub . '/' . bin2hex(random_bytes(16)) . '.' . $f['ext'];
            if (!move_uploaded_file($f['tmp'], self::FILES_DIR . '/' . $rel)) {
                continue;
            }
            @chmod(self::FILES_DIR . '/' . $rel, 0640);
            $fid = DB::insert('submission_files', [
                'submission_id' => $submissionId,
                'original_name' => $f['name'],
                'stored_name' => $rel,
                'mime' => $f['mime'],
                'size' => $f['size'],
                'ip' => client_ip(),
                'created_at' => now(),
            ]);
            $stored[] = ['id' => $fid, 'name' => $f['name'], 'size' => $f['size']];
        }
        return $stored;
    }

    /** Absolute path of a stored upload, or null if missing or outside the uploads folder. */
    public static function filePath(array $row): ?string
    {
        $rel = (string)$row['stored_name'];
        if (!preg_match('~^\d{4}/\d{2}/[a-f0-9]{32}\.[a-z0-9]{2,5}$~', $rel)) {
            return null;
        }
        $p = self::FILES_DIR . '/' . $rel;
        return is_file($p) ? $p : null;
    }

    /** Delete a submission's stored files and their rows. */
    public static function deleteFiles(int $submissionId): int
    {
        $n = 0;
        foreach (DB::all('SELECT * FROM submission_files WHERE submission_id = ?', [$submissionId]) as $r) {
            if ($p = self::filePath($r)) {
                @unlink($p);
            }
            $n += DB::delete('submission_files', 'id = ?', [$r['id']]);
        }
        return $n;
    }

    public static function fileUrl(int $fileId): string
    {
        return abs_url('admin/api.php?r=submissions.file&id=' . $fileId);
    }

    /** Recipients from a comma/semicolon separated list (only valid addresses). */
    public static function recipients(string $list): array
    {
        $out = [];
        foreach (preg_split('/[,;]+/', $list) as $a) {
            $a = trim($a);
            if ($a !== '' && filter_var($a, FILTER_VALIDATE_EMAIL)) {
                $out[strtolower($a)] = $a;
            }
        }
        return array_values($out);
    }

    private static function label(string $key, array $def): string
    {
        foreach ($def['fields'] as $f) {
            if ($f['name'] === $key) {
                return $f['label'];
            }
        }
        return ucwords(str_replace('_', ' ', $key));
    }

    private static function forward(string $type, array $def, array $data, string $page, string $product, array $files): bool
    {
        $hook = trim((string)setting('ghl_webhook_url'));
        if ($hook === '' || !preg_match('~^https://~', $hook) || !function_exists('curl_init')) {
            return false;
        }
        $payload = array_merge($data, [
            'form' => $type,
            'form_name' => $def['title'],
            'product' => $product,
            'full_name' => trim((string)($data['name'] ?? trim(($data['first_name'] ?? '') . ' ' . ($data['last_name'] ?? '')))),
            'files' => array_map(fn($f) => ['name' => $f['name'], 'url' => self::fileUrl($f['id'])], $files),
            'source' => 'Website',
            'page' => abs_url($page ?: '/'),
            'submitted_at' => date('c'),
        ]);
        $ch = curl_init($hook);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 6,
            CURLOPT_CONNECTTIMEOUT => 4,
        ]);
        curl_exec($ch);
        $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return $code >= 200 && $code < 300;
    }

    private static function fromHeader(): string
    {
        $host = preg_replace('/^www\./', '', parse_url(site_origin(), PHP_URL_HOST) ?: 'localhost');
        $name = preg_replace('/[^\w .&\'-]/u', '', (string)setting('site_short_name')) ?: 'Website';
        return 'From: ' . $name . ' <noreply@' . $host . '>';
    }

    private static function notify(int $id, string $type, array $def, array $data, string $page, string $product, array $files): void
    {
        $to = self::recipients((string)setting('notify_email'));
        if (!$to || !function_exists('mail')) {
            return;
        }
        $lines = [];
        if ($product !== '') {
            $lines[] = 'Product: ' . $product;
        }
        foreach ($data as $k => $v) {
            if ($v !== '') {
                $lines[] = self::label($k, $def) . ': ' . $v;
            }
        }
        if ($files) {
            $lines[] = '';
            $lines[] = 'Artwork files (sign in to the dashboard to download):';
            foreach ($files as $f) {
                $lines[] = '- ' . $f['name'] . ' (' . number_format($f['size'] / 1048576, 1) . ' MB): ' . self::fileUrl($f['id']);
            }
        }
        $lines[] = '';
        $lines[] = 'Page: ' . abs_url($page ?: '/');
        $lines[] = 'View in dashboard: ' . abs_url('admin/#/submissions/' . $id);
        $subject = $def['kind'] === 'quote'
            ? 'New Quote Request — ' . $def['title'] . ($product !== '' ? ' (' . $product . ')' : '')
            : 'New submission from ' . $def['title'];
        $headers = [self::fromHeader(), 'Content-Type: text/plain; charset=UTF-8'];
        if (!empty($data['email']) && filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $headers[] = 'Reply-To: ' . $data['email'];
        }
        @mail(implode(', ', $to), self::encodeSubject($subject), implode("\n", $lines), implode("\r\n", $headers));
    }

    /** The customer auto-reply that Gravity Forms used to send. */
    private static function autoReply(array $data): void
    {
        $email = (string)($data['email'] ?? '');
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || !function_exists('mail')) {
            return;
        }
        $body = "Thank you for contacting us! We will get in touch with you shortly.\n\n"
            . setting('site_name') . ' — ' . setting('tagline') . "\n"
            . setting('phone') . ' · ' . setting('email') . "\n" . abs_url('');
        $headers = [self::fromHeader(), 'Content-Type: text/plain; charset=UTF-8'];
        $reply = self::recipients((string)setting('email'));
        if ($reply) {
            $headers[] = 'Reply-To: ' . $reply[0];
        }
        @mail($email, self::encodeSubject('We have received your inquiry'), $body, implode("\r\n", $headers));
    }

    private static function encodeSubject(string $s): string
    {
        return preg_match('/[^\x20-\x7e]/', $s) ? '=?UTF-8?B?' . base64_encode($s) . '?=' : $s;
    }
}
