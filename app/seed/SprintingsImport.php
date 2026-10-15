<?php
/**
 * Imports the Sprintings content pack (app/seed/sprintings/) into the database: media, the 41
 * products, core pages, blog posts, reviews, the shop location and redirects.
 *
 * Used by the installer (seed_database()) and by app/cli/import-sprintings.php for local resets.
 * It is idempotent: rows are matched by slug (or name) and updated, so it can be re-run.
 * Everything imported is flagged needs_review = 1 for approval in the dashboard.
 *
 * Copy rules (BRIEF.md §3): the original text is kept; obvious typos are fixed (TYPOS). Passages
 * copied from another printer's website (they name GotPrint or make that company's claims about
 * prices, free sample kits, an online designer, instant proofs or production times) are left off
 * the site and listed in docs/content-review.md for the owner to decide on.
 */
final class SprintingsImport
{
    public const DIR = APP . '/seed/sprintings';
    public const MEDIA_FOLDER = 'sprintings-import';

    /** Product slug => category (Content::CATEGORIES). */
    public const CATEGORY = [
        'business-cards' => 'cards', 'folded-business-cards' => 'cards', 'rip-business-cards' => 'cards',
        'business-card-magnets' => 'cards', 'collectors-cards' => 'cards', 'greeting-cards' => 'cards',
        'flyers' => 'marketing', 'flyer-printing' => 'marketing', 'club-flyers' => 'marketing', 'staggered-cut-flyers' => 'marketing',
        'brochures' => 'marketing', 'brochures-2' => 'marketing', 'postcards' => 'marketing', 'postcards-paper' => 'marketing',
        'postcard-magnets' => 'marketing', 'door-hangers' => 'marketing', 'door-hanger-printing' => 'marketing', 'rack-cards' => 'marketing',
        'booklets' => 'books', 'booklets-catalogs' => 'books', 'booklets-magazines-catalogs' => 'books', 'books' => 'books',
        'calendars' => 'books', 'notepads' => 'books', 'bookmarks' => 'books',
        'stickers' => 'labels', 'stickers-roll-labels' => 'labels', 'roll-labels' => 'labels', 'hang-tags' => 'labels',
        'folded-hang-tags' => 'labels', 'special-shapes' => 'labels',
        'letterheads' => 'stationery', 'letterheads-paper' => 'stationery', 'envelope-printing' => 'stationery', 'folders' => 'stationery',
        'invitations' => 'events', 'event-tickets' => 'events', 'table-tents' => 'events', 'mini-menus' => 'events', 'election-printing' => 'events',
        'signage-banners' => 'signage',
    ];

    /** Product icons where the category icon is too generic. */
    public const ICON = [
        'business-card-magnets' => 'magnet', 'postcard-magnets' => 'magnet', 'election-printing' => 'vote', 'books' => 'book-open',
        'bookmarks' => 'bookmark', 'calendars' => 'calendar', 'notepads' => 'notebook', 'folders' => 'folder',
        'envelope-printing' => 'mailbox', 'invitations' => 'gift', 'greeting-cards' => 'gift', 'mini-menus' => 'utensils',
        'event-tickets' => 'ticket', 'table-tents' => 'presentation', 'stickers' => 'sticker', 'stickers-roll-labels' => 'sticker',
        'roll-labels' => 'sticker', 'hang-tags' => 'tag', 'folded-hang-tags' => 'tag', 'special-shapes' => 'scissors',
        'door-hangers' => 'file', 'door-hanger-printing' => 'file', 'letterheads' => 'file-text', 'letterheads-paper' => 'file-text',
        'signage-banners' => 'flag', 'club-flyers' => 'sparkles', 'staggered-cut-flyers' => 'layers', 'rack-cards' => 'layers',
        'booklets' => 'book-open', 'booklets-catalogs' => 'book-open', 'booklets-magazines-catalogs' => 'book-open',
    ];

    /** Menu labels that tell same-named products apart (from the old Products mega-menu). */
    public const MENU_LABEL = [
        'letterheads-paper' => 'Letterheads Paper',
        'postcards-paper' => 'Postcards Paper',
    ];

    /** Card summaries where the page's first sentence makes a poor summary (taken from the page's own text). */
    public const EXCERPT = [
        'roll-labels' => 'Our labels and finishes provide protection from moisture, abrasion, scratches, and harsh conditions.',
    ];

    /** Products shown in the homepage showcase (one or two per category, with good photography). */
    public const FEATURED = ['business-cards', 'flyers', 'brochures', 'postcards-paper', 'booklets', 'stickers', 'invitations', 'signage-banners', 'door-hangers', 'letterheads'];

    /** Obvious typos in the original copy. */
    public const TYPOS = [
        'Gauranteed' => 'Guaranteed', 'neeeds' => 'needs', 'businsesses' => 'businesses', 'Sumit' => 'Submit',
        'Envelops' => 'Envelopes', 'customers whom demand' => 'customers who demand', 'Her are some' => 'Here are some',
        'make the a crater' => 'make a crater', 'We take of all' => 'We take care of all', 'ensure your get' => 'ensure you get',
        'is backbone of' => 'is the backbone of', '6 Simples Steps' => '6 Simple Steps', 'BOTH  indoor' => 'BOTH indoor',
    ];

    /** Competitor brand in descriptive copy → neutral wording (the claims themselves match this page's specs). */
    private const BRAND_FIX = [
        'from GotPrint.com' => 'from Sprintings', 'GotPrint’s postcard magnets' => 'Our postcard magnets',
        'your GotPrint postcard magnets' => 'your postcard magnets', 'At GotPrint, we offer' => 'At Sprintings, we offer',
        'GotPrint offers' => 'Sprintings offers', 'GotPrint is quick' => 'Sprintings is quick',
    ];

    /** Passages that make another company's claims: held back for review. */
    private const HOLD_BACK = '~GotPrint|\$\s?\d|online designer|online design tool|sample kit|instant online proof|business days|DVD or CD|free templates|guideline templates|design packages?~i';

    private array $log = [];
    private array $review = [];
    private array $mediaMap = [];
    private array $mediaMeta = [];
    private bool $withMedia;

    public function __construct(bool $withMedia = true)
    {
        $this->withMedia = $withMedia;
    }

    /** Run the whole import. Returns log lines. */
    public function run(): array
    {
        $this->loadMediaMeta();
        if ($this->withMedia) {
            $this->importMedia();
        }
        $this->importSettings();
        $this->importLocation();
        $this->importProducts();
        $this->importPages();
        $this->importPosts();
        $this->importReviews();
        $this->importRedirects();
        $this->writeReviewDoc();
        return $this->log;
    }

    public function reviewItems(): array
    {
        return $this->review;
    }

    // ================================================================== Media

    private function loadMediaMeta(): void
    {
        $m = json_decode((string)file_get_contents(self::DIR . '/media.json'), true) ?: [];
        foreach ($m['attachments'] ?? [] as $a) {
            $this->mediaMeta[$a['file']] = $a;
        }
    }

    /** The local seed file for a WordPress upload path (the PNGs were converted to JPEG/WebP). */
    private function seedFile(string $wpFile): ?string
    {
        $wpFile = ltrim(preg_replace('~^.*?/wp-content/uploads/~', '', $wpFile), '/');
        $p = self::DIR . '/media/' . $wpFile;
        if (is_file($p)) {
            return $p;
        }
        $stem = self::DIR . '/media/' . preg_replace('/\.[a-z0-9]+$/i', '', $wpFile);
        foreach (['jpg', 'webp', 'png', 'jpeg', 'gif'] as $ext) {
            if (is_file($stem . '.' . $ext)) {
                return $stem . '.' . $ext;
            }
        }
        return null;
    }

    /** Readable alt text from a file name: "Sprinting_Flyers_Img_Two.jpg" → "Flyers sample 2". */
    public static function readableName(string $file): string
    {
        $n = pathinfo($file, PATHINFO_FILENAME);
        $n = preg_replace('/-e\d{6,}$/', '', $n);
        $n = preg_replace('/^Sprintings?[_-]?(Product[_-])?/i', '', $n);
        $n = str_replace(['_', '-'], ' ', $n);
        $nums = ['One' => 1, 'Two' => 2, 'Three' => 3];
        $n = preg_replace_callback('/\bImg (One|Two|Three)\b/', fn($m) => 'sample ' . $nums[$m[1]], $n);
        $n = preg_replace('/\b(Img|image)\b/i', '', $n);
        $n = str_replace('Folden', 'Folded', $n);
        return trim(preg_replace('/\s+/', ' ', $n));
    }

    private function importMedia(): void
    {
        $files = [];
        foreach ($this->mediaMeta as $file => $a) {
            if (!empty($a['used_on']) && !preg_match('/\.(ttf|otf|woff2?)$/i', $file)) {
                $files[$file] = $a;
            }
        }
        $files['2022/11/Sprintings_Home_Img_One.png'] = $files['2022/11/Sprintings_Home_Img_One.png'] ?? ['alt' => '', 'title' => ''];
        $n = 0;
        $missing = [];
        foreach ($files as $file => $a) {
            if ($this->media('/wp-content/uploads/' . $file) !== '') {
                $n++;
            } else {
                $missing[] = $file;
            }
        }
        $this->log[] = "media: $n files in the library" . ($missing ? ' (' . count($missing) . ' missing: ' . implode(', ', array_slice($missing, 0, 5)) . ')' : '');
    }

    /** Library path for a WordPress image URL, importing it on first use. '' when unavailable. */
    private function media(?string $wpUrl): string
    {
        $wpUrl = trim((string)$wpUrl);
        if ($wpUrl === '') {
            return '';
        }
        if (isset($this->mediaMap[$wpUrl])) {
            return $this->mediaMap[$wpUrl];
        }
        $file = ltrim(preg_replace('~^.*?/wp-content/uploads/~', '', $wpUrl), '/');
        $src = $this->seedFile($file);
        if (!$src) {
            return $this->mediaMap[$wpUrl] = '';
        }
        if (!$this->withMedia) {
            // Re-run without copying files: reuse what an earlier import put in the library
            $row = DB::one('SELECT path FROM media WHERE original_name = ? AND folder = ? ORDER BY id DESC', [basename($src), self::MEDIA_FOLDER]);
            return $this->mediaMap[$wpUrl] = $row['path'] ?? '';
        }
        // "-e1669181866112" files are WordPress edits of another image; keep their own file
        $meta = $this->mediaMeta[$file] ?? null;
        $alt = trim(html_entity_decode((string)($meta['alt'] ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        $decorative = preg_match('/(Icon|Arrow|Animate)/i', $file);
        if ($alt === '' && !$decorative) {
            $alt = self::readableName($file);
        }
        $title = trim((string)($meta['title'] ?? '')) ?: self::readableName($file);
        try {
            $row = Media::importLocal($src, self::MEDIA_FOLDER, $alt, $title);
        } catch (Throwable $e) {
            $this->log[] = 'media: ' . basename($src) . ' failed: ' . $e->getMessage();
            return $this->mediaMap[$wpUrl] = '';
        }
        return $this->mediaMap[$wpUrl] = $row['path'];
    }

    // ================================================================== Markdown helpers

    /** [front matter array, body lines]. */
    private function readMd(string $rel): array
    {
        $raw = (string)file_get_contents(self::DIR . '/' . $rel);
        $front = [];
        $body = $raw;
        if (preg_match('/^---\n(.*?)\n---\n(.*)$/s', $raw, $m)) {
            foreach (explode("\n", $m[1]) as $line) {
                if (preg_match('/^([a-z_]+):\s*(.*)$/', $line, $kv)) {
                    $v = json_decode($kv[2], true);
                    $front[$kv[1]] = $v === null && $kv[2] !== 'null' ? $kv[2] : $v;
                }
            }
            $body = $m[2];
        }
        $lines = array_map('rtrim', explode("\n", $body));
        return [$front, $lines];
    }

    public static function fixText(string $s): string
    {
        $s = strtr($s, self::TYPOS);
        // The brand is "Sprintings"; the old copy sometimes says "Sprinting"
        $s = preg_replace('/\bSprinting\b(?![_\-])/', 'Sprintings', $s);
        return $s;
    }

    /** Apply typo and brand fixes; returns null (and records it) when the passage is held back. */
    private function vet(string $text, string $where): ?string
    {
        $text = self::fixText($text);
        $text = strtr($text, self::BRAND_FIX);
        if (preg_match(self::HOLD_BACK, $text)) {
            $this->review[] = ['where' => $where, 'text' => $text];
            return null;
        }
        return $text;
    }

    /** Inline Markdown (bold, links) to HTML. */
    private static function inline(string $s): string
    {
        $s = e(html_entity_decode($s, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        $s = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $s);
        $s = preg_replace_callback('/\[([^\]]+)\]\(([^)]+)\)/', function ($m) {
            $href = html_entity_decode($m[2]);
            $href = preg_replace('~^https?://(www\.)?sprintings\.com~i', '', $href);
            return Html::safeUrl($href) ? '<a href="' . e($href) . '">' . $m[1] . '</a>' : $m[1];
        }, $s);
        return $s;
    }

    private static function isPlaceholder(string $line): bool
    {
        return (bool)preg_match('/^\[(SHARED SECTION|QUOTE FORM|GOOGLE MAP|GOOGLE REVIEWS|ELEMENTOR FORM|BLOG POSTS|image: dynamic|NAVIGATION|Button:|shortcode)/i', $line)
            || str_starts_with($line, '<!--');
    }

    /** Convert a list of plain lines (paragraph per line, "- " bullets, "1. " items, images) to HTML. */
    private function linesToHtml(array $lines, string $where, int $headingLevel = 3): string
    {
        $out = '';
        $list = null;
        $close = function () use (&$out, &$list) {
            if ($list) {
                $out .= '</' . $list . '>';
                $list = null;
            }
        };
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || self::isPlaceholder($line)) {
                continue;
            }
            if (preg_match('/^#{2,4}\s+(.+)$/', $line, $m)) {
                $close();
                $h = $this->vet($m[1], $where);
                if ($h !== null) {
                    $out .= '<h' . $headingLevel . '>' . self::inline($h) . '</h' . $headingLevel . '>';
                }
                continue;
            }
            if (preg_match('/^!\[([^\]]*)\]\(([^)]+)\)$/', $line, $m)) {
                $close();
                $p = $this->media($m[2]);
                if ($p !== '') {
                    $meta = Media::lookup($p);
                    $out .= '<figure><img src="' . e($p) . '" alt="' . e($meta['alt'] ?? self::readableName($m[2])) . '"' . ($meta && $meta['width'] ? ' width="' . (int)$meta['width'] . '" height="' . (int)$meta['height'] . '"' : '') . '></figure>';
                }
                continue;
            }
            if (preg_match('/^(?:[-*•]|\d+\.)\s+(.+)$/', $line, $m)) {
                $type = preg_match('/^\d+\./', $line) ? 'ol' : 'ul';
                $t = $this->vet($m[1], $where);
                if ($t === null) {
                    continue;
                }
                if ($list !== $type) {
                    $close();
                    $out .= '<' . $type . '>';
                    $list = $type;
                }
                $out .= '<li>' . self::inline($t) . '</li>';
                continue;
            }
            $close();
            $t = $this->vet($line, $where);
            if ($t !== null) {
                $out .= '<p>' . self::inline($t) . '</p>';
            }
        }
        $close();
        return $out;
    }

    /** Split body lines into blocks by ## / #### headings. */
    private static function blocks(array $lines): array
    {
        $blocks = [];
        $cur = ['level' => 0, 'title' => '', 'lines' => []];
        foreach ($lines as $line) {
            if (preg_match('/^(#{2,4})\s+(.*)$/', $line, $m)) {
                $blocks[] = $cur;
                $cur = ['level' => strlen($m[1]), 'title' => trim($m[2]), 'lines' => []];
                continue;
            }
            $cur['lines'][] = $line;
        }
        $blocks[] = $cur;
        return array_values(array_filter($blocks, fn($b) => $b['title'] !== '' || array_filter($b['lines'], fn($l) => trim($l) !== '')));
    }

    private static function textLines(array $lines): array
    {
        return array_values(array_filter(array_map('trim', $lines), fn($l) => $l !== '' && !self::isPlaceholder($l)));
    }

    private static function excerpt(string $text, int $max = 155): string
    {
        $text = trim(preg_replace('/\s+/', ' ', strip_tags(html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8'))));
        if (mb_strlen($text) <= $max) {
            return $text;
        }
        // Whole sentences that fit; otherwise cut at a word
        $out = '';
        foreach (preg_split('/(?<=[.!?])\s+/', $text) as $sentence) {
            if (mb_strlen(trim($out . ' ' . $sentence)) > $max) {
                break;
            }
            $out = trim($out . ' ' . $sentence);
        }
        return $out !== '' ? $out : str_limit($text, $max);
    }

    private function upsert(string $table, string $keyCol, array $row): int
    {
        $existing = DB::one('SELECT id FROM ' . DB::ident($table) . ' WHERE ' . DB::ident($keyCol) . ' = ?', [$row[$keyCol]]);
        if ($existing) {
            unset($row['created_at']);
            DB::update($table, $row, 'id = ?', [$existing['id']]);
            return (int)$existing['id'];
        }
        return DB::insert($table, $row);
    }

    // ================================================================== Settings & location

    private function importSettings(): void
    {
        $site = json_decode((string)file_get_contents(self::DIR . '/site.json'), true) ?: [];
        $b = $site['business'] ?? [];
        $social = $b['social'] ?? [];
        $find = fn(string $host) => current(array_filter($social, fn($u) => str_contains($u, $host))) ?: '';
        $set = [
            'site_name' => $site['name'] ?? 'Sprintings',
            'site_short_name' => $site['name'] ?? 'Sprintings',
            'phone' => $b['phone'] ?? '',
            'email' => $b['email'] ?? '',
            'social_facebook' => $find('facebook.com'),
            'social_instagram' => $find('instagram.com'),
            'social_linkedin' => $find('linkedin.com'),
            'color_primary' => '#246b7f',
            'color_accent' => '#f56f46',
            'color_highlight' => '#4d5862',
        ];
        // Notification recipients: the Gravity Forms admin notification
        $forms = json_decode((string)file_get_contents(self::DIR . '/forms.json'), true) ?: [];
        foreach ($forms as $f) {
            if ((int)$f['id'] === 1) {
                foreach ($f['notifications'] ?? [] as $n) {
                    if (($n['name'] ?? '') === 'Admin Notification') {
                        $set['notify_email'] = implode(', ', Forms::recipients((string)$n['to']));
                    }
                }
            }
        }
        // Menus from the WordPress menus
        foreach (['menu_header' => 'Head Menu', 'menu_footer' => 'Foot Menu'] as $key => $menu) {
            $items = [];
            foreach ($site['menus'][$menu] ?? [] as $i) {
                if ($key === 'menu_header' && $i['url'] === '/') {
                    continue; // the logo links home
                }
                $items[] = $i['label'] . ' | ' . $i['url'];
            }
            if ($items) {
                $set[$key] = implode("\n", $items);
            }
        }
        if ($this->withMedia) {
            $set['logo'] = $this->media('/wp-content/uploads/2022/11/Sprintings_Logo.png');
            $set['favicon'] = $this->media('/wp-content/uploads/2022/11/Sprintings_Favicon.png');
            $set['hero_image'] = $this->media('/wp-content/uploads/2022/11/Sprintings_Home_Animate.gif');
            $set['about_image'] = $this->media('/wp-content/uploads/2022/11/Sprintings_Home_Img_One.png');
            $set['testimonials_image'] = $this->media('/wp-content/uploads/2022/11/Sprintings_Home_Img_Three.png');
            $set['seo_og_image'] = $this->media('/wp-content/uploads/2022/11/Sprinting_flyer_image.png');
        }
        foreach ($set as $k => $v) {
            if ($v !== '' && $v !== null) {
                Settings::set($k, $v);
            }
        }
        $this->log[] = 'settings: business details, social links, colours, logo and menus';
    }

    private function importLocation(): void
    {
        $now = now();
        $this->upsert('locations', 'slug', [
            'slug' => 'long-beach',
            'name' => 'Long Beach',
            'address' => '520 W. Willow Street, 101-F',
            'city' => 'Long Beach',
            'state' => 'CA',
            'zip' => '90806',
            'phone' => (string)setting('phone'),
            'fax' => '',
            'hours' => json_encode([
                ['days' => 'Monday – Thursday', 'time' => '9:00 AM – 6:00 PM', 'schema' => ['Mo-Th 09:00-18:00']],
                ['days' => 'Friday', 'time' => '9:00 AM – 4:00 PM', 'schema' => ['Fr 09:00-16:00']],
                ['days' => 'Saturday – Sunday', 'time' => 'Closed', 'schema' => []],
            ], JSON_UNESCAPED_UNICODE),
            'map_embed' => '',
            'directions_url' => '',
            'image' => '',
            'description' => 'Our print shop on W. Willow Street in Long Beach, CA.',
            'latitude' => '',
            'longitude' => '',
            'sort_order' => 10,
            'status' => 'published',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $this->log[] = 'location: Long Beach shop with opening hours';
    }

    // ================================================================== Products

    /** Quote form key for each product URL, from forms.json used_on. */
    private function quoteFormMap(): array
    {
        $map = [];
        foreach (QuoteForms::all() as $key => $f) {
            if ($f['kind'] !== 'quote' || $key === 'quote') {
                continue;
            }
            foreach ($f['used_on'] as $u) {
                if (preg_match('~^/service/([a-z0-9\-]+)/$~', $u, $m)) {
                    $map[$m[1]] = $key;
                }
            }
        }
        return $map;
    }

    /** Parse one product page into service columns. */
    public function parseProduct(string $file): array
    {
        [$front, $lines] = $this->readMd($file);
        $slug = basename((string)($front['url'] ?? ''), '/') ?: preg_replace('/^service__|\.md$/', '', basename($file));
        // Cut at the contact block; trailing "## Other Product" headings after it are prev/next links
        $main = [];
        $trail = [];
        $inContact = false;
        $afterContact = false;
        foreach ($lines as $l) {
            if (preg_match('/^- \*\*(Phone|Email|Address|Hours)\*\*/', $l)) {
                $inContact = true;
                continue;
            }
            if ($inContact) {
                $afterContact = true;
            }
            if ($afterContact) {
                if (preg_match('/^##\s+(.+)$/', $l, $m)) {
                    $trail[] = trim($m[1]);
                }
                continue;
            }
            $main[] = $l;
        }
        $blocks = self::blocks($main);
        $title = self::fixText((string)($blocks[0]['title'] ?? $front['title'] ?? $slug));
        $where = 'Product “' . $title . '” (/service/' . $slug . '/)';

        $intro = '';
        $overview = '';
        $options = [];
        $promise = '';
        $design = [];
        $closing = '';
        $faqs = [];
        $specs = [];
        $images = [];
        $gallery = [];

        $collectMedia = function (array &$ls) use (&$images, &$gallery) {
            $keep = [];
            foreach ($ls as $l) {
                if (preg_match('/^Image gallery:\s*(.+)$/', trim($l), $m)) {
                    foreach (array_map('trim', explode(',', $m[1])) as $u) {
                        if ($u !== '') $gallery[] = $u;
                    }
                    continue;
                }
                if (preg_match('/^!\[[^\]]*\]\(([^)]+)\)$/', trim($l), $m)) {
                    $images[] = $m[1];
                    continue;
                }
                $keep[] = $l;
            }
            $ls = $keep;
        };

        foreach ($blocks as $i => $b) {
            $collectMedia($b['lines']);
            $t = $b['title'];
            $text = self::textLines($b['lines']);
            if ($i === 0) {
                // Title block: first paragraph is the intro; the rest is overview
                $rest = [];
                foreach ($text as $l) {
                    if ($intro === '' && !preg_match('/^[-*]\s/', $l)) {
                        $v = $this->vet($l, $where);
                        if ($v !== null) {
                            $intro = $v;
                            continue;
                        }
                        continue;
                    }
                    $rest[] = $l;
                }
                $overview .= $this->linesToHtml($rest, $where);
                continue;
            }
            if (!$text && ($t === $title || strcasecmp($t, $front['title'] ?? '') === 0 || $b['level'] === 2 && $t === $blocks[0]['title'])) {
                continue; // the heading above the quote form
            }
            if (preg_match('/^If You.re Interested In/i', $t)) {
                continue;
            }
            if (preg_match('/^We have the perfect .+ to fit your needs:?$/i', $t)) {
                foreach ($text as $l) {
                    $l = preg_replace('/^[-*]\s+/', '', $l);
                    if (($v = $this->vet($l, $where)) !== null) $options[] = $v;
                }
                continue;
            }
            if (preg_match('/^Our Service Promise$/i', $t)) {
                $promise .= $this->linesToHtml($text, $where);
                continue;
            }
            if (preg_match('/(Design Details|Specs)$/i', $t) && $text && count(array_filter($text, fn($l) => preg_match('/^[-*]\s/', $l))) === count($text)) {
                foreach ($text as $l) {
                    if (($v = $this->vet(preg_replace('/^[-*]\s+/', '', $l), $where)) !== null) $design[] = $v;
                }
                continue;
            }
            if (preg_match('/: Related Products$/i', $t) || preg_match('/^(Questions About|Have a Question About)/i', $t)) {
                $h = $this->vet($t, $where);
                $closing .= ($h !== null ? '<h3>' . self::inline($h) . '</h3>' : '') . $this->linesToHtml($text, $where);
                continue;
            }
            if (preg_match('/^Frequently Asked Questions/i', $t)) {
                $q = null;
                $a = [];
                $flush = function () use (&$q, &$a, &$faqs, $where) {
                    if ($q === null) return;
                    $qa = $q . "\n" . implode("\n", $a);
                    if ($this->vet($qa, $where . ' — FAQ') !== null) {
                        $key = mb_strtolower($q);
                        if (!isset($faqs[$key])) {
                            $faqs[$key] = ['q' => self::fixText($q), 'a' => self::fixText(implode("\n\n", $a))];
                        }
                    }
                    $q = null;
                    $a = [];
                };
                foreach ($text as $l) {
                    if (preg_match('/^\*\*Q:\s*(.+?)\*\*$/', $l, $m)) {
                        $flush();
                        $q = trim($m[1]);
                        continue;
                    }
                    if ($q !== null) $a[] = $l;
                }
                $flush();
                continue;
            }
            if ($b['level'] === 2 && str_ends_with(rtrim($t), ':')) {
                // Specification group (## Popular Sizes:, ## Paper Stocks:, …); duplicates are merged
                $name = rtrim(trim(rtrim(rtrim($t), ':')));
                $items = [];
                foreach ($text as $l) {
                    $l = preg_replace('/^[-*]\s+/', '', $l);
                    $items[] = self::fixText($l); // specifications are product options: kept as they are
                }
                $k = slugify($name);
                if (isset($specs[$k])) {
                    $specs[$k]['items'] = array_values(array_unique(array_merge($specs[$k]['items'], $items)));
                } else {
                    $specs[$k] = ['title' => $name, 'items' => array_values(array_unique($items))];
                }
                continue;
            }
            // Anything else is a sales-copy section of the overview
            $h = $this->vet($t, $where);
            $body = $this->linesToHtml($text, $where);
            if ($body !== '' || $h !== null) {
                $overview .= ($h !== null ? '<h3>' . self::inline($h) . '</h3>' : '') . $body;
            }
        }
        // Pages whose title block has no text: the first paragraph of the copy becomes the intro
        if ($intro === '' && preg_match('~<p>(.*?)</p>~s', $overview, $m)) {
            $intro = html_entity_decode(strip_tags($m[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $overview = preg_replace('~<p>' . preg_quote($m[1], '~') . '</p>~', '', $overview, 1);
            $overview = preg_replace('~^<h3>[^<]*</h3>(?=<h3>|$)~', '', $overview);
        }
        // Election printing lists many short product types: use them as the options chips too
        if (!$options && preg_match_all('~<h3>([^<]{3,40})</h3><p>~', $overview, $m) && count($m[1]) >= 8) {
            $options = array_map(fn($s) => html_entity_decode($s, ENT_QUOTES | ENT_HTML5, 'UTF-8'), $m[1]);
        }
        $gallery = array_values(array_unique($gallery));
        $images = array_values(array_unique($images));
        return [
            'slug' => $slug,
            'title' => $title,
            'intro' => $intro,
            'overview' => $overview,
            'options' => array_values(array_unique($options)),
            'promise' => $promise,
            'design' => $design,
            'closing' => $closing,
            'faqs' => array_values($faqs),
            'specs' => array_values($specs),
            'image_urls' => array_values(array_unique(array_merge($images, $gallery))),
            'gallery_urls' => $gallery ?: $images,
            'trail' => $trail,
            'wp_id' => (int)($front['wordpress_id'] ?? 0),
        ];
    }

    private function importProducts(): void
    {
        $forms = $this->quoteFormMap();
        $files = glob(self::DIR . '/pages/service__*.md') ?: [];
        $parsed = [];
        foreach ($files as $f) {
            $p = $this->parseProduct('pages/' . basename($f));
            $parsed[$p['slug']] = $p;
        }
        // Order: by category, then the old mega-menu order (alphabetical)
        $cats = array_keys(Content::CATEGORIES);
        uasort($parsed, function ($a, $b) use ($cats) {
            $ca = array_search(self::CATEGORY[$a['slug']] ?? 'marketing', $cats, true);
            $cb = array_search(self::CATEGORY[$b['slug']] ?? 'marketing', $cats, true);
            return [$ca, $a['title'], $a['slug']] <=> [$cb, $b['title'], $b['slug']];
        });
        $titles = [];
        foreach ($parsed as $p) {
            $titles[mb_strtolower($p['title'])][] = $p['slug'];
        }
        $now = now();
        $i = 0;
        foreach ($parsed as $slug => $p) {
            $i++;
            $gallery = array_values(array_filter(array_map(fn($u) => $this->media($u), $p['gallery_urls'])));
            $image = $gallery[0] ?? '';
            // Related: the old page's next/previous product links, then siblings in the category
            $related = [];
            foreach ($p['trail'] as $t) {
                foreach ($titles[mb_strtolower(self::fixText($t))] ?? [] as $s) {
                    if ($s !== $slug) $related[] = $s;
                }
            }
            foreach ($parsed as $o) {
                if (count($related) >= 4) break;
                if ($o['slug'] !== $slug && (self::CATEGORY[$o['slug']] ?? '') === (self::CATEGORY[$slug] ?? '') && !in_array($o['slug'], $related, true)) {
                    $related[] = $o['slug'];
                }
            }
            $source = $p['intro'] !== '' ? $p['intro'] : strip_tags($p['overview']);
            $excerpt = self::EXCERPT[$slug] ?? self::excerpt($source, 160);
            $metaDesc = self::excerpt($source, 155);
            if ($excerpt === '') {
                $excerpt = 'Custom ' . mb_strtolower($p['title']) . ' printed by our Long Beach, CA team.';
                $metaDesc = $p['title'] . ' printing from Sprintings in Long Beach, CA. Request a quote today.';
            }
            $this->upsert('services', 'slug', [
                'slug' => $slug,
                'title' => $p['title'],
                'menu_label' => self::MENU_LABEL[$slug] ?? '',
                'category' => self::CATEGORY[$slug] ?? 'marketing',
                'excerpt' => $excerpt,
                'hero_text' => $p['intro'],
                'image' => $image,
                'icon' => self::ICON[$slug] ?? '',
                'what_is' => Html::clean($p['overview']),
                'conditions' => implode("\n", $p['options']),
                'how_it_works' => Html::clean($p['promise']),
                'benefits' => implode("\n", $p['design']),
                'what_to_expect' => Html::clean($p['closing']),
                'faqs' => json_encode($p['faqs'], JSON_UNESCAPED_UNICODE),
                'related' => implode(',', array_slice(array_values(array_unique($related)), 0, 4)),
                'body_areas' => '',
                'gallery' => json_encode($gallery, JSON_UNESCAPED_SLASHES),
                'specs' => json_encode(array_map(fn($g) => ['title' => $g['title'], 'items' => implode("\n", $g['items'])], $p['specs']), JSON_UNESCAPED_UNICODE),
                'quote_form' => $forms[$slug] ?? QuoteForms::DEFAULT_PRODUCT_FORM,
                'featured' => in_array($slug, self::FEATURED, true) ? 1 : 0,
                'coming_soon' => 0,
                'show_in_menu' => 1,
                'needs_review' => 1,
                'sort_order' => $i * 10,
                'status' => 'published',
                'meta_title' => $p['title'],
                'meta_description' => $metaDesc,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
        Settings::set('featured_services', implode(',', array_values(array_filter(self::FEATURED, fn($s) => isset($parsed[$s])))));
        $this->log[] = 'products: ' . count($parsed) . ' imported';
    }

    // ================================================================== Pages

    private function importPages(): void
    {
        $now = now();
        $pages = [];

        // About Us: the text appears twice on the old page (desktop and mobile copies)
        [, $lines] = $this->readMd('pages/about-us.md');
        $paras = [];
        $aboutImages = [];
        foreach (self::textLines($lines) as $l) {
            if (preg_match('/^!\[[^\]]*\]\(([^)]+)\)$/', $l, $m)) {
                $aboutImages[] = $m[1];
                continue;
            }
            if (preg_match('/^(#{2,4}|- \*\*)/', $l)) {
                continue;
            }
            $key = preg_replace('/[^a-z]/', '', strtolower($l));
            $paras[$key] = $l;
        }
        $paras = array_values($paras);
        $pages[] = [
            'slug' => 'about-us', 'title' => 'About Us', 'eyebrow' => 'About Sprintings', 'template' => 'about',
            'intro' => self::fixText(array_shift($paras) ?? ''),
            'content' => $this->linesToHtml($paras, 'About Us page'),
            'image' => $this->media($aboutImages[1] ?? ($aboutImages[0] ?? '')),
            'meta_title' => 'About Us',
        ];
        $pages[] = [
            'slug' => 'contact', 'title' => 'Contact Us', 'eyebrow' => 'Contact', 'template' => 'contact',
            'intro' => 'Ask a question or just say hello!',
            'content' => '<h2>Request a quote or just say hello.</h2>',
            'meta_title' => 'Contact',
        ];
        $pages[] = [
            'slug' => 'products', 'title' => 'Products', 'eyebrow' => 'Everything we print', 'template' => 'treatments',
            'intro' => 'Envelopes, brochures, flyers, cards and much more. If you can imagine it, we’ll print it.',
            'meta_title' => 'Products',
        ];
        $pages[] = [
            'slug' => 'blog', 'title' => 'Blog', 'eyebrow' => 'Blog', 'template' => 'blog',
            'intro' => 'Sprintings Blog: Inspirations to Boost Printing Performance',
            'meta_title' => 'Blog',
        ];
        $pages[] = [
            'slug' => 'request-a-quote', 'title' => 'Request a Quote', 'eyebrow' => 'Get a quote', 'template' => 'appointment', 'show_cta' => 0,
            'intro' => 'Let’s Start Your Project',
            'content' => '<p>Do you have a printing idea in mind? Our printing experts are ready to help you bring your ideas to life. Contact us today so we can help you with your project.</p>',
            'meta_title' => 'Request a Quote',
        ];
        $pages[] = [
            'slug' => 'thank-you', 'title' => 'Thank You', 'eyebrow' => 'Request received', 'template' => 'thanks', 'show_cta' => 0, 'is_system' => 1,
            'intro' => 'Thank you for requesting a quote. Our team will get right with you to confirm your order and gather any other information needed for your order. Please feel free to reach out to us directly on our contact form or by calling us here.',
            'meta_title' => 'Thank You',
        ];
        foreach ($pages as $i => $p) {
            $desc = self::excerpt($p['intro'] !== '' ? $p['intro'] : strip_tags($p['content'] ?? ''), 155);
            $this->upsert('pages', 'slug', array_merge([
                'eyebrow' => '', 'intro' => '', 'image' => '', 'content' => '', 'template' => 'default', 'show_cta' => 1, 'is_system' => 1,
                'status' => 'published', 'meta_title' => '', 'meta_description' => $desc,
            ], $p, ['content' => Html::clean($p['content'] ?? ''), 'needs_review' => 1, 'sort_order' => ($i + 1) * 10, 'created_at' => $now, 'updated_at' => $now]));
        }
        $this->log[] = 'pages: ' . count($pages) . ' (about, contact, products, blog, request a quote, thank you)';
    }

    // ================================================================== Blog

    private function importPosts(): void
    {
        $now = now();
        $n = 0;
        foreach (glob(self::DIR . '/posts/*.md') ?: [] as $f) {
            [$front, $lines] = $this->readMd('posts/' . basename($f));
            $slug = basename((string)$front['url'], '/');
            $title = self::fixText((string)$front['title']);
            // Drop the title heading; #### headings become H2s in the post body
            $body = [];
            foreach ($lines as $l) {
                if (preg_match('/^##\s+/', $l) && trim(substr($l, 3)) === trim((string)$front['title'])) continue;
                $body[] = $l;
            }
            $html = $this->linesToHtml($body, 'Blog post “' . $title . '”', 2);
            $excerpt = self::fixText(html_entity_decode((string)($front['excerpt'] ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            $cats = (array)($front['categories'] ?? []);
            $this->upsert('posts', 'slug', [
                'slug' => $slug,
                'title' => $title,
                'excerpt' => $excerpt,
                'content' => Html::clean($html),
                'image' => $this->media((string)($front['featured_image'] ?? '')),
                'category' => (string)($cats[0] ?? 'Uncategorized'),
                'published_at' => date('Y-m-d H:i:s', strtotime((string)$front['date'])),
                'needs_review' => 1,
                'status' => 'published',
                'meta_title' => $title,
                'meta_description' => self::excerpt($excerpt, 155),
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $n++;
        }
        $this->log[] = "posts: $n imported";
    }

    // ================================================================== Reviews, redirects

    private function importReviews(): void
    {
        $reviews = json_decode((string)file_get_contents(self::DIR . '/reviews.json'), true) ?: [];
        $now = now();
        foreach ($reviews as $i => $r) {
            $text = trim(html_entity_decode((string)$r['text'], ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            $rating = (int)round((float)$r['rating']);
            $name = trim((string)$r['name']);
            $row = [
                'name' => $name,
                'label' => 'Google review',
                'content' => $text,
                'rating' => $rating,
                // Shown on the homepage and product pages: 4–5 star reviews with text
                'featured' => $rating >= 4 && $text !== '' ? 1 : 0,
                'sort_order' => ($i + 1) * 10,
                'status' => 'published',
                'created_at' => $r['date'] ? $r['date'] . ' 12:00:00' : $now,
                'updated_at' => $now,
            ];
            $this->upsert('testimonials', 'name', $row);
        }
        $this->log[] = 'reviews: ' . count($reviews) . ' Google reviews';
    }

    private function importRedirects(): void
    {
        $now = now();
        foreach ([
            ['/service/', '/products/', 'Empty WordPress page'],
            ['/booklets-and-magazines/', '/service/booklets-magazines-catalogs/', 'Empty WordPress page'],
            ['/invitations/', '/service/invitations/', 'Empty WordPress page'],
            ['/category/uncategorized/', '/blog/', 'WordPress category archive'],
        ] as [$from, $to, $note]) {
            $this->upsert('redirects', 'source', ['source' => $from, 'target' => $to, 'code' => 301, 'hits' => 0, 'note' => $note, 'created_at' => $now, 'updated_at' => $now]);
        }
        $this->log[] = 'redirects: 4 old URLs';
    }

    // ================================================================== Review document

    private function writeReviewDoc(): void
    {
        if (!$this->review) {
            return;
        }
        $this->log[] = count($this->review) . ' copied passage(s) held back for review (docs/content-review.md)';
        $dir = ROOT . '/docs';
        if (!is_dir($dir) || !is_writable($dir)) {
            return; // the file is generated in the repository; the server copy is not needed
        }
        $md = "# Content held back for review\n\nGenerated by the content import (app/seed/SprintingsImport.php). These passages are on the old\nsprintings.com product pages but were copied from another printer's website: they name GotPrint or make\nthat company's claims (prices, free sample kits, an online designer tool, instant proofs, production\ntimes). They are **not** shown on the new site. Add any that are true for Sprintings back in\nDashboard → Products (or FAQs), reworded as needed.\n";
        $group = [];
        foreach ($this->review as $r) {
            $group[$r['where']][] = $r['text'];
        }
        foreach ($group as $where => $texts) {
            $md .= "\n## " . $where . "\n\n";
            foreach (array_unique($texts) as $t) {
                $md .= '> ' . str_replace("\n", "\n> ", $t) . "\n\n";
            }
        }
        @file_put_contents($dir . '/content-review.md', $md);
    }
}
