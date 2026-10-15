<?php
/**
 * Per-request SEO state: title, description, canonical, Open Graph and JSON-LD schema.
 */
final class Seo
{
    public static string $title = '';
    public static string $description = '';
    public static string $canonical = '';
    public static string $image = '';
    public static string $type = 'website';
    public static bool $noindex = false;
    public static array $schema = [];
    public static array $breadcrumbs = [];

    public static function set(array $o): void
    {
        foreach ($o as $k => $v) {
            if (property_exists(self::class, $k)) {
                self::${$k} = $v;
            }
        }
    }

    public static function fullTitle(): string
    {
        $suffix = (string)setting('seo_title_suffix');
        $t = trim(self::$title);
        if ($t === '') {
            return setting('site_name') . ' — ' . setting('tagline');
        }
        if ($suffix !== '' && !str_contains($t, trim($suffix, ' |-'))) {
            return $t . $suffix;
        }
        return $t;
    }

    public static function addSchema(array $s): void
    {
        self::$schema[] = $s;
    }

    public static function head(): string
    {
        $desc = self::$description ?: (string)setting('seo_default_description');
        $canonical = self::$canonical ?: abs_url(strtok($_SERVER['REQUEST_URI'] ?? '/', '?'));
        $image = self::$image ?: (string)setting('seo_og_image');
        $robots = (self::$noindex || setting('seo_noindex') === '1') ? 'noindex, nofollow' : 'index, follow, max-image-preview:large';
        $h = [];
        $h[] = '<title>' . e(self::fullTitle()) . '</title>';
        $h[] = '<meta name="description" content="' . e(str_limit($desc, 165)) . '">';
        $h[] = '<link rel="canonical" href="' . e($canonical) . '">';
        $h[] = '<meta name="robots" content="' . $robots . '">';
        $h[] = '<meta property="og:site_name" content="' . e(setting('site_name')) . '">';
        $h[] = '<meta property="og:type" content="' . e(self::$type) . '">';
        $h[] = '<meta property="og:title" content="' . e(self::fullTitle()) . '">';
        $h[] = '<meta property="og:description" content="' . e(str_limit($desc, 200)) . '">';
        $h[] = '<meta property="og:url" content="' . e($canonical) . '">';
        $h[] = '<meta property="og:locale" content="en_US">';
        if ($image) {
            $h[] = '<meta property="og:image" content="' . e(abs_url($image)) . '">';
            $h[] = '<meta name="twitter:card" content="summary_large_image">';
        } else {
            $h[] = '<meta name="twitter:card" content="summary">';
        }
        $schemas = array_merge([self::organization()], self::$schema);
        if (self::$breadcrumbs) {
            $items = [];
            foreach (self::$breadcrumbs as $i => $b) {
                $items[] = ['@type' => 'ListItem', 'position' => $i + 1, 'name' => $b[0], 'item' => abs_url($b[1])];
            }
            $schemas[] = ['@context' => 'https://schema.org', '@type' => 'BreadcrumbList', 'itemListElement' => $items];
        }
        foreach ($schemas as $s) {
            $h[] = '<script type="application/ld+json">' . json_encode($s, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) . '</script>';
        }
        return implode("\n  ", $h);
    }

    /** LocalBusiness schema for the print shop: address, geo and opening hours come from the shop location. */
    public static function organization(): array
    {
        $shop = Content::shop();
        $same = array_values(array_filter([setting('social_facebook'), setting('social_instagram'), setting('social_linkedin'), setting('social_x'), setting('reviews_url')]));
        $org = [
            '@context' => 'https://schema.org',
            '@type' => 'LocalBusiness',
            '@id' => abs_url('') . '#organization',
            'name' => setting('site_name'),
            'alternateName' => setting('site_short_name') !== setting('site_name') ? setting('site_short_name') : '',
            'slogan' => setting('tagline'),
            'description' => setting('brand_description'),
            'url' => abs_url(''),
            'telephone' => preg_replace('/^tel:/', '', tel_href((string)setting('phone'))),
            'email' => setting('email'),
            'areaServed' => array_map(fn($c) => ['@type' => 'City', 'name' => $c], csv_list(setting('communities'))),
        ];
        if ($shop) {
            $org['address'] = [
                '@type' => 'PostalAddress',
                'streetAddress' => $shop['address'],
                'addressLocality' => $shop['city'],
                'addressRegion' => $shop['state'],
                'postalCode' => $shop['zip'],
                'addressCountry' => 'US',
            ];
            if ($shop['latitude'] !== '' && $shop['longitude'] !== '') {
                $org['geo'] = ['@type' => 'GeoCoordinates', 'latitude' => (float)$shop['latitude'], 'longitude' => (float)$shop['longitude']];
            }
            $hours = [];
            $days = ['Mo' => 'Monday', 'Tu' => 'Tuesday', 'We' => 'Wednesday', 'Th' => 'Thursday', 'Fr' => 'Friday', 'Sa' => 'Saturday', 'Su' => 'Sunday'];
            foreach (Content::hours($shop) as $row) {
                foreach ((array)($row['schema'] ?? []) as $spec) {
                    if (preg_match('/^([A-Za-z,\-]+)\s+(\d{1,2}:\d{2})-(\d{1,2}:\d{2})$/', trim($spec), $m)) {
                        $list = [];
                        foreach (explode(',', $m[1]) as $part) {
                            $r = explode('-', $part);
                            $keys = array_keys($days);
                            $a = array_search($r[0], $keys, true);
                            $b = array_search($r[1] ?? $r[0], $keys, true);
                            if ($a === false || $b === false) continue;
                            for ($d = $a; $d <= $b; $d++) $list[] = $days[$keys[$d]];
                        }
                        if ($list) {
                            $hours[] = ['@type' => 'OpeningHoursSpecification', 'dayOfWeek' => $list, 'opens' => $m[2], 'closes' => $m[3]];
                        }
                    }
                }
            }
            if ($hours) {
                $org['openingHoursSpecification'] = $hours;
            }
            if ($shop['image'] ?? '') {
                $org['image'] = abs_url((string)$shop['image']);
            }
            $org['hasMap'] = Content::mapsDirections($shop);
        }
        $org = array_filter($org, fn($v) => $v !== '' && $v !== null && $v !== []);
        if (setting('logo')) {
            $org['logo'] = abs_url(setting('logo'));
            $org['image'] = $org['image'] ?? abs_url(setting('logo'));
        }
        if ($same) {
            $org['sameAs'] = $same;
        }
        return $org;
    }

    public static function faqSchema(array $faqs): ?array
    {
        $items = [];
        foreach ($faqs as $f) {
            $q = $f['question'] ?? $f['q'] ?? '';
            $a = $f['answer'] ?? $f['a'] ?? '';
            if ($q && $a) {
                $items[] = ['@type' => 'Question', 'name' => $q, 'acceptedAnswer' => ['@type' => 'Answer', 'text' => strip_tags($a)]];
            }
        }
        return $items ? ['@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => $items] : null;
    }
}
