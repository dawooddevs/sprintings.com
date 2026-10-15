<?php
/**
 * Read-side content repository used by the public templates.
 */
final class Content
{
    /**
     * Public URL prefixes: services live at /SERVICE_BASE/<slug>/ and provider profiles at
     * /PROVIDER_BASE/<slug>/. Change these to match the existing site's URLs (then add
     * redirects for any old URLs in Dashboard → Redirects).
     */
    public const SERVICE_BASE = 'service';
    public const PROVIDER_BASE = 'team';

    /** Product categories (dashboard dropdown, mega-menu, products filter and homepage bento). */
    public const CATEGORIES = [
        'cards' => ['label' => 'Business Cards & Cards', 'short' => 'Cards', 'icon' => 'credit-card', 'blurb' => 'Business cards, folded and rip cards, magnets, greeting and collectors cards.'],
        'marketing' => ['label' => 'Flyers, Brochures & Marketing', 'short' => 'Marketing', 'icon' => 'file-text', 'blurb' => 'Flyers, brochures, postcards, door hangers and rack cards.'],
        'books' => ['label' => 'Booklets, Books & Catalogs', 'short' => 'Books', 'icon' => 'book-open', 'blurb' => 'Booklets, magazines, catalogs, books, calendars, notepads and bookmarks.'],
        'labels' => ['label' => 'Stickers, Labels & Tags', 'short' => 'Labels', 'icon' => 'tag', 'blurb' => 'Stickers, roll labels, hang tags and special shapes.'],
        'stationery' => ['label' => 'Stationery & Office', 'short' => 'Stationery', 'icon' => 'mail', 'blurb' => 'Letterheads, envelopes and presentation folders.'],
        'events' => ['label' => 'Events, Invitations & Menus', 'short' => 'Events', 'icon' => 'ticket', 'blurb' => 'Invitations, event tickets, table tents, mini menus and election printing.'],
        'signage' => ['label' => 'Signage & Banners', 'short' => 'Signage', 'icon' => 'flag', 'blurb' => 'Indoor and outdoor signs and banners.'],
    ];

    /** FAQ groups: where a FAQ is shown. Templates read one group with Content::faqs('<key>'). */
    public const FAQ_GROUPS = [
        'home' => 'Homepage',
        'general' => 'General',
        'ordering' => 'Ordering',
        'about' => 'About page',
        'contact' => 'Contact page',
        'quote' => 'Request a quote page',
    ];

    public const PROVIDER_TYPES = [
        'staff' => 'Team',
        'designer' => 'Design Team',
        'other' => 'Other',
    ];

    public const BODY_AREAS = [
        'neck' => 'Neck', 'shoulder' => 'Shoulder', 'back' => 'Back', 'hip' => 'Hip', 'knee' => 'Knee',
        'elbow' => 'Elbow', 'wrist-hand' => 'Wrist / Hand', 'leg' => 'Leg', 'ankle-foot' => 'Ankle / Foot', 'joint' => 'General Joint Pain',
    ];

    private static array $cache = [];

    /** Treatments for the homepage explorer: the featured list, or every published treatment in a category. */
    public static function explorerServices(string $cat): array
    {
        if ($cat === 'featured') {
            return self::servicesBySlugs(csv_list(setting('featured_services')));
        }
        return array_values(array_filter(self::services(), fn($s) => $s['category'] === $cat));
    }

    public static function services(bool $publishedOnly = true): array
    {
        $key = 'services' . (int)$publishedOnly;
        if (!isset(self::$cache[$key])) {
            $sql = 'SELECT * FROM services' . ($publishedOnly ? " WHERE status = 'published'" : '') . ' ORDER BY sort_order, title';
            self::$cache[$key] = DB::all($sql);
        }
        return self::$cache[$key];
    }

    public static function servicesByCategory(): array
    {
        $out = array_fill_keys(array_keys(self::CATEGORIES), []);
        foreach (self::services() as $s) {
            $out[$s['category']][] = $s;
        }
        return $out;
    }

    public static function service(string $slug): ?array
    {
        return DB::one("SELECT * FROM services WHERE slug = ? AND status = 'published'", [$slug]);
    }

    public static function servicesBySlugs(array $slugs): array
    {
        $by = [];
        foreach (self::services() as $s) {
            $by[$s['slug']] = $s;
        }
        $out = [];
        foreach ($slugs as $slug) {
            if (isset($by[$slug])) {
                $out[] = $by[$slug];
            }
        }
        return $out;
    }

    public static function providers(): array
    {
        if (!isset(self::$cache['providers'])) {
            self::$cache['providers'] = DB::all("SELECT * FROM providers WHERE status = 'published' ORDER BY sort_order, name");
        }
        return self::$cache['providers'];
    }

    public static function provider(string $slug): ?array
    {
        return DB::one("SELECT * FROM providers WHERE slug = ? AND status = 'published'", [$slug]);
    }

    public static function page(string $slug): ?array
    {
        return DB::one("SELECT * FROM pages WHERE slug = ? AND status = 'published'", [$slug]);
    }

    public static function locations(): array
    {
        if (!isset(self::$cache['locations'])) {
            self::$cache['locations'] = DB::all("SELECT * FROM locations WHERE status = 'published' ORDER BY sort_order, name");
        }
        return self::$cache['locations'];
    }

    public static function testimonials(bool $featuredOnly = false): array
    {
        $sql = "SELECT * FROM testimonials WHERE status = 'published' AND content IS NOT NULL AND content <> ''" . ($featuredOnly ? ' AND featured = 1' : '') . ' ORDER BY sort_order, id';
        return DB::all($sql);
    }

    public static function faqs(string $group): array
    {
        return DB::all("SELECT * FROM faqs WHERE grp = ? AND status = 'published' ORDER BY sort_order, id", [$group]);
    }

    public static function serviceUrl(array $s): string
    {
        return url(self::SERVICE_BASE . '/' . $s['slug'] . '/');
    }

    public static function providerUrl(array $p): string
    {
        return url(self::PROVIDER_BASE . '/' . $p['slug'] . '/');
    }

    public static function categoryLabel(string $cat): string
    {
        return self::CATEGORIES[$cat]['label'] ?? ucfirst($cat);
    }

    public static function serviceIcon(array $s): string
    {
        return $s['icon'] ?: (self::CATEGORIES[$s['category']]['icon'] ?? 'activity');
    }

    /** Related services: explicit list first, then same-category siblings. */
    public static function related(array $s, int $limit = 4): array
    {
        $out = self::servicesBySlugs(csv_list($s['related'] ?? ''));
        if (count($out) < $limit) {
            foreach (self::services() as $o) {
                if ($o['category'] === $s['category'] && $o['slug'] !== $s['slug'] && !in_array($o, $out, true)) {
                    $out[] = $o;
                }
                if (count($out) >= $limit) {
                    break;
                }
            }
        }
        return array_slice(array_values(array_filter($out, fn($o) => $o['slug'] !== $s['slug'])), 0, $limit);
    }

    public static function bodyAreaMap(): array
    {
        // Group matches per area and category, then interleave categories so
        // each area shows a balanced mix (max 8) rather than one category.
        $buckets = array_fill_keys(array_keys(self::BODY_AREAS), []);
        foreach (self::services() as $s) {
            foreach (csv_list($s['body_areas']) as $a) {
                if (isset($buckets[$a])) {
                    $buckets[$a][$s['category']][] = [
                        'title' => $s['menu_label'] ?: $s['title'],
                        'url' => self::serviceUrl($s),
                        'category' => self::CATEGORIES[$s['category']]['short'] ?? '',
                    ];
                }
            }
        }
        $map = [];
        foreach ($buckets as $area => $cats) {
            $map[$area] = [];
            while (count($map[$area]) < 8 && $cats) {
                foreach ($cats as $c => &$items) {
                    if ($items && count($map[$area]) < 8) {
                        $map[$area][] = array_shift($items);
                    }
                    if (!$items) {
                        unset($cats[$c]);
                    }
                }
                unset($items);
            }
        }
        return $map;
    }

    /**
     * Downloadable files (e.g. price lists or artwork templates) found in the Media Library by 'file'
     * name or set in Settings → Downloads. Items with no file are hidden. Empty until Sprintings
     * supplies files; the Downloads settings tab is hidden while this is empty.
     */
    public const FORM_GROUPS = [];

    private static ?array $formGroups = null;

    /** FORM_GROUPS with each item resolved to a url and its source (setting or library); missing items are dropped. */
    public static function formGroups(): array
    {
        if (self::$formGroups !== null) {
            return self::$formGroups;
        }
        $library = Media::byMatchKey('document');
        $groups = [];
        foreach (self::FORM_GROUPS as $gk => $g) {
            $items = [];
            foreach ($g['items'] as $f) {
                $set = isset($f['setting']) ? trim((string)setting($f['setting'], '')) : '';
                $path = Media::findByName($library, $f['file'])['path'] ?? null;
                if ($set !== '') {
                    $items[] = $f + ['url' => media_url($set), 'source' => 'setting'];
                } elseif ($path) {
                    $items[] = $f + ['url' => media_url($path), 'source' => 'library'];
                }
            }
            if ($items) {
                $groups[$gk] = ['items' => $items] + $g;
            }
        }
        return self::$formGroups = $groups;
    }

    /** Short list for menus and sidebars: every available download. */
    public static function patientForms(): array
    {
        $out = [];
        foreach (self::formGroups() as $g) {
            foreach ($g['items'] as $f) {
                $out[] = ['label' => $f['label'], 'url' => $f['url'], 'available' => true, 'download' => true, 'source' => $f['source']];
            }
        }
        return $out;
    }

    /**
     * Site videos. Each is found in the Media Library by 'file' name (or one of its 'alt' names,
     * see Media::matchKey), unless a different file is chosen in Settings → Videos. Add entries
     * here (and a matching setting in Settings/Resources) when the practice supplies videos.
     */
    public const VIDEOS = [];

    private static ?array $videos = null;

    /** All VIDEOS resolved: url, mime and source ('setting', 'library' or 'missing'). */
    public static function videos(): array
    {
        if (self::$videos !== null) {
            return self::$videos;
        }
        $library = Media::byMatchKey('video');
        $out = [];
        foreach (self::VIDEOS as $key => $v) {
            $set = trim((string)setting($v['setting'], ''));
            $row = null;
            foreach (array_merge([$v['file']], $v['alt'] ?? []) as $name) {
                if ($row = Media::findByName($library, $name)) break;
            }
            $path = $set !== '' ? $set : ($row['path'] ?? '');
            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            $out[$key] = $v + [
                'url' => $path !== '' ? media_url($path) : '',
                'mime' => ['mp4' => 'video/mp4', 'webm' => 'video/webm', 'mov' => 'video/mp4'][$ext] ?? 'video/mp4',
                'source' => $set !== '' ? 'setting' : ($row ? 'library' : 'missing'),
            ];
        }
        return self::$videos = $out;
    }

    /** The requested videos that are available, in order. */
    public static function videoList(array $keys): array
    {
        $all = self::videos();
        return array_values(array_filter(array_map(fn($k) => $all[$k] ?? null, $keys), fn($v) => $v && $v['url'] !== ''));
    }

    /**
     * Find a provider's headshot among image rows by name: every part of their name except titles,
     * credentials and initials must appear in the file name ("Dr. Jane Smith.png",
     * "jane-smith.jpg" and "provider-dr-jane-smith.webp" all match Dr. Jane Smith), falling
     * back to the last name alone. Newest upload wins.
     */
    public static function providerPhotoMatch(array $provider, array $images): ?array
    {
        $skip = ['dr', 'doctor', 'md', 'dc', 'do', 'pa', 'pac', 'np', 'provider', 'headshot', 'photo', 'image', 'img'];
        $words = fn(string $s): array => array_values(array_filter(explode('-', slugify($s)), fn($w) => strlen($w) > 1 && !in_array($w, $skip, true)));
        $need = $words((string)$provider['name']);
        if (!$need) {
            return null;
        }
        // Full name first; then the last name alone (e.g. "Dr.-Smith.png"), as last names are unique on the team.
        foreach ([$need, [end($need)]] as $tokens) {
            $best = null;
            foreach ($images as $m) {
                $name = pathinfo((string)($m['original_name'] ?: $m['path']), PATHINFO_FILENAME);
                $have = array_merge($words($name), $words(pathinfo((string)$m['path'], PATHINFO_FILENAME)));
                if (!array_diff($tokens, $have) && (!$best || (int)$m['id'] > (int)$best['id'])) {
                    $best = $m;
                }
            }
            if ($best) {
                return $best;
            }
        }
        return null;
    }

    public static function hours(array $loc): array
    {
        return json_list($loc['hours'] ?? '[]');
    }

    /**
     * Combined weekly hours for several locations, grouped into rows of consecutive days with the
     * same hours everywhere: [['days' => 'Tue – Wed', 'iso' => [2, 3], 'times' => [slug => '7:30 AM – 5:30 PM' | null]], …].
     * Built from the schema.org strings ("Mo-Th 07:30-17:30"); returns [] if any location lacks them.
     */
    public static function hoursTable(array $locs): array
    {
        $codes = ['Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa', 'Su'];
        $names = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
        $fmt = function (string $t): string {
            [$h, $m] = array_map('intval', explode(':', $t));
            return ($h % 12 ?: 12) . ':' . str_pad((string)$m, 2, '0', STR_PAD_LEFT) . ($h < 12 ? ' AM' : ' PM');
        };
        $week = [];
        foreach ($locs as $l) {
            $days = array_fill(0, 7, null);
            foreach (self::hours($l) as $h) {
                if (empty($h['schema'])) {
                    if (stripos((string)($h['time'] ?? ''), 'closed') === false) return [];
                    continue;
                }
                foreach ((array)$h['schema'] as $spec) {
                    if (!preg_match('/^([A-Za-z,\-]+)\s+(\d{1,2}:\d{2})-(\d{1,2}:\d{2})$/', trim($spec), $m)) return [];
                    foreach (explode(',', $m[1]) as $part) {
                        $range = explode('-', $part);
                        $a = array_search($range[0], $codes, true);
                        $b = array_search($range[1] ?? $range[0], $codes, true);
                        if ($a === false || $b === false) return [];
                        for ($d = $a; $d <= $b; $d++) $days[$d] = $fmt($m[2]) . ' – ' . $fmt($m[3]);
                    }
                }
            }
            $week[$l['slug']] = $days;
        }
        $rows = [];
        for ($d = 0; $d < 7; $d++) {
            $times = array_map(fn($days) => $days[$d], $week);
            $last = count($rows) - 1;
            if ($last >= 0 && $rows[$last]['times'] === $times) {
                $rows[$last]['to'] = $d;
            } else {
                $rows[] = ['from' => $d, 'to' => $d, 'times' => $times];
            }
        }
        return array_map(fn($r) => [
            'days' => $names[$r['from']] . ($r['to'] > $r['from'] ? ' – ' . $names[$r['to']] : ''),
            'iso' => range($r['from'] + 1, $r['to'] + 1), // ISO weekday numbers, for highlighting "today" in the browser
            'times' => $r['times'],
        ], $rows);
    }

    public static function mapsDirections(array $loc): string
    {
        if (!empty($loc['directions_url'])) {
            return $loc['directions_url'];
        }
        $q = $loc['address'] . ', ' . $loc['city'] . ', ' . $loc['state'] . ' ' . $loc['zip'];
        return 'https://www.google.com/maps/dir/?api=1&destination=' . rawurlencode($q);
    }

    public static function mapEmbed(array $loc): string
    {
        if (!empty($loc['map_embed'])) {
            if (preg_match('~src="([^"]+)"~', $loc['map_embed'], $m)) {
                return html_entity_decode($m[1]);
            }
            return $loc['map_embed'];
        }
        $q = $loc['address'] . ', ' . $loc['city'] . ', ' . $loc['state'] . ' ' . $loc['zip'];
        return 'https://www.google.com/maps?q=' . rawurlencode(setting('site_name') . ', ' . $q) . '&output=embed';
    }

    public static function fullAddress(array $loc): string
    {
        return $loc['address'] . ', ' . $loc['city'] . ', ' . $loc['state'] . ' ' . $loc['zip'];
    }

    // ------------------------------------------------------------------ Products (extras)

    /** Gallery image paths for a product (main image first when it is not already in the gallery). */
    public static function gallery(array $s, bool $withMain = true): array
    {
        $g = array_values(array_filter(array_map('strval', json_list($s['gallery'] ?? '[]')), fn($p) => $p !== ''));
        if ($withMain && !empty($s['image']) && !in_array($s['image'], $g, true)) {
            array_unshift($g, $s['image']);
        }
        return $g;
    }

    /** Specification groups for a product: [['title' => 'Popular Sizes', 'items' => [...]], …]. */
    public static function specs(array $s): array
    {
        $out = [];
        foreach (json_list($s['specs'] ?? '[]') as $row) {
            $title = trim((string)($row['title'] ?? ''));
            $items = is_array($row['items'] ?? null) ? $row['items'] : lines((string)($row['items'] ?? ''));
            $items = array_values(array_filter(array_map('trim', $items), 'strlen'));
            if ($title !== '' || $items) {
                $out[] = ['title' => $title, 'items' => $items];
            }
        }
        return $out;
    }

    /** Spec items whose group title matches one of $titles (used for quote-form suggestions). */
    public static function specItems(array $s, array $titles): array
    {
        $want = array_map(fn($t) => slugify($t), $titles);
        $out = [];
        foreach (self::specs($s) as $g) {
            if (in_array(slugify($g['title']), $want, true)) {
                foreach ($g['items'] as $i) {
                    if (mb_strlen($i) <= 80) $out[] = $i;
                }
            }
        }
        return array_values(array_unique($out));
    }

    // ------------------------------------------------------------------ Blog

    public static function posts(int $limit = 0, int $offset = 0, string $category = ''): array
    {
        $sql = "SELECT * FROM posts WHERE status = 'published'" . ($category !== '' ? ' AND category = ?' : '') . ' ORDER BY published_at DESC, id DESC';
        if ($limit > 0) {
            $sql .= ' LIMIT ' . (int)$limit . ' OFFSET ' . max(0, (int)$offset);
        }
        return DB::all($sql, $category !== '' ? [$category] : []);
    }

    public static function postCount(string $category = ''): int
    {
        return (int)DB::val("SELECT COUNT(*) FROM posts WHERE status = 'published'" . ($category !== '' ? ' AND category = ?' : ''), $category !== '' ? [$category] : []);
    }

    /** Distinct published categories: [slug => label]. */
    public static function postCategories(): array
    {
        $out = [];
        foreach (DB::all("SELECT DISTINCT category FROM posts WHERE status = 'published' AND category <> '' ORDER BY category") as $r) {
            $out[slugify($r['category'])] = $r['category'];
        }
        return $out;
    }

    /** A published post by its permalink parts (/YYYY/MM/DD/slug/). */
    public static function postByPermalink(string $y, string $m, string $d, string $slug): ?array
    {
        $p = DB::one("SELECT * FROM posts WHERE slug = ? AND status = 'published'", [$slug]);
        if (!$p || substr((string)$p['published_at'], 0, 10) !== "$y-$m-$d") {
            return null;
        }
        return $p;
    }

    public static function postPath(array $p): string
    {
        $t = strtotime((string)$p['published_at']) ?: time();
        return date('Y/m/d', $t) . '/' . $p['slug'] . '/';
    }

    public static function postUrl(array $p): string
    {
        return url(self::postPath($p));
    }

    public static function readingTime(string $html): int
    {
        return max(1, (int)round(str_word_count(strip_tags($html)) / 220));
    }

    // ------------------------------------------------------------------ Site-wide helpers

    /** The main shop (first published location), or null. */
    public static function shop(): ?array
    {
        return self::locations()[0] ?? null;
    }

    /** A menu from Settings (one "Label | /path/" per line). */
    public static function menu(string $key): array
    {
        $out = [];
        foreach (lines((string)setting($key)) as $line) {
            $parts = array_map('trim', explode('|', $line, 2));
            if (count($parts) === 2 && $parts[0] !== '' && $parts[1] !== '') {
                $out[] = ['label' => $parts[0], 'url' => $parts[1]];
            }
        }
        return $out;
    }

    /** Published reviews (testimonials) with text; featured only for the homepage carousel. */
    public static function reviews(bool $featuredOnly = false): array
    {
        return self::testimonials($featuredOnly);
    }
}
