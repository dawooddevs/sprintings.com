<?php
/**
 * Content type definitions. One definition drives the dashboard list views,
 * the edit forms (rendered client-side from this schema) and server-side
 * validation/sanitisation.
 */
final class Resources
{
    public static function all(): array
    {
        $cats = [];
        foreach (Content::CATEGORIES as $k => $c) {
            $cats[] = ['value' => $k, 'label' => $c['label']];
        }
        $ptypes = [];
        foreach (Content::PROVIDER_TYPES as $k => $l) {
            $ptypes[] = ['value' => $k, 'label' => $l];
        }
        $quoteForms = [];
        foreach (QuoteForms::all() as $k => $f) {
            if ($f['kind'] === 'quote' && $k !== 'quote') {
                $quoteForms[] = ['value' => $k, 'label' => $f['title'] . ($f['id'] ? ' (#' . $f['id'] . ')' : '')];
            }
        }
        $status = [['value' => 'published', 'label' => 'Published'], ['value' => 'draft', 'label' => 'Draft']];
        $icons = array_map(fn($k) => ['value' => $k, 'label' => $k], array_keys(require APP . '/lib/icons.php'));
        array_unshift($icons, ['value' => '', 'label' => 'Category default']);
        $seo = [
            ['key' => 'meta_title', 'label' => 'SEO title', 'type' => 'text', 'group' => 'seo', 'max' => 70, 'help' => 'Shown in Google results and browser tabs. ~50–60 characters is ideal.'],
            ['key' => 'meta_description', 'label' => 'Meta description', 'type' => 'textarea', 'group' => 'seo', 'max' => 170, 'rows' => 3, 'help' => '~140–160 characters summarising the page.'],
        ];

        return [
            'services' => [
                'table' => 'services', 'label' => 'Products', 'singular' => 'Product', 'icon' => 'printer',
                'titleKey' => 'title', 'slug' => true, 'url' => Content::SERVICE_BASE . '/{slug}/', 'orderable' => true, 'review' => true,
                'search' => ['title', 'slug', 'excerpt'],
                'columns' => [
                    ['key' => 'image', 'label' => '', 'type' => 'thumb'],
                    ['key' => 'title', 'label' => 'Product', 'type' => 'title'],
                    ['key' => 'category', 'label' => 'Category', 'type' => 'option'],
                    ['key' => 'quote_form', 'label' => 'Quote form', 'type' => 'option'],
                    ['key' => 'status', 'label' => 'Status', 'type' => 'status'],
                    ['key' => 'updated_at', 'label' => 'Updated', 'type' => 'date'],
                ],
                'filters' => ['status' => $status, 'category' => $cats],
                'groups' => ['content' => 'Overview', 'sections' => 'Page sections', 'specs' => 'Specifications', 'faq' => 'FAQ', 'seo' => 'SEO'],
                'fields' => array_merge([
                    ['key' => 'title', 'label' => 'Product name', 'type' => 'text', 'required' => true, 'group' => 'content'],
                    ['key' => 'slug', 'label' => 'URL slug', 'type' => 'slug', 'group' => 'content', 'prefix' => '/' . Content::SERVICE_BASE . '/', 'help' => 'Keep existing slugs unchanged to protect SEO. Add a redirect if you change one.'],
                    ['key' => 'menu_label', 'label' => 'Menu label', 'type' => 'text', 'group' => 'content', 'help' => 'Optional name used in the Products menu (e.g. to tell two similar products apart).'],
                    ['key' => 'excerpt', 'label' => 'Card summary', 'type' => 'textarea', 'rows' => 3, 'group' => 'content', 'help' => 'One or two sentences used on product cards and in search.'],
                    ['key' => 'hero_text', 'label' => 'Intro', 'type' => 'textarea', 'rows' => 3, 'group' => 'content', 'help' => 'The opening paragraph under the product name.'],
                    ['key' => 'gallery', 'label' => 'Gallery images', 'type' => 'gallery', 'group' => 'content', 'help' => 'Shown in the product hero. Drag to reorder.'],
                    ['key' => 'conditions', 'label' => 'Options / types (one per line)', 'type' => 'lines', 'group' => 'sections', 'help' => 'The "We have the perfect … to fit your needs" list, shown as chips.'],
                    ['key' => 'what_is', 'label' => 'Overview', 'type' => 'richtext', 'group' => 'sections', 'help' => 'The main sales copy ("Spread the Word…"). Use H3 headings for sub-sections.'],
                    ['key' => 'how_it_works', 'label' => 'Service promise', 'type' => 'richtext', 'group' => 'sections'],
                    ['key' => 'benefits', 'label' => 'Design details (one per line)', 'type' => 'lines', 'group' => 'sections'],
                    ['key' => 'what_to_expect', 'label' => 'Related products text', 'type' => 'richtext', 'group' => 'sections', 'help' => 'Closing copy, e.g. related products or "Questions about …?".'],
                    ['key' => 'specs', 'label' => 'Specification groups', 'type' => 'repeater', 'group' => 'specs', 'add' => 'Add group', 'help' => 'E.g. Popular Sizes, Paper Stocks, Print Options. One item per line. Quote forms suggest these values.',
                        'fields' => [['key' => 'title', 'label' => 'Heading', 'type' => 'text'], ['key' => 'items', 'label' => 'Items (one per line)', 'type' => 'textarea', 'rows' => 5]]],
                    ['key' => 'faqs', 'label' => 'Frequently asked questions', 'type' => 'repeater', 'group' => 'faq', 'add' => 'Add question',
                        'fields' => [['key' => 'q', 'label' => 'Question', 'type' => 'text'], ['key' => 'a', 'label' => 'Answer', 'type' => 'textarea', 'rows' => 3]]],
                    ['key' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => $status, 'side' => true, 'default' => 'draft'],
                    ['key' => 'category', 'label' => 'Category', 'type' => 'select', 'options' => $cats, 'side' => true, 'required' => true],
                    ['key' => 'quote_form', 'label' => 'Quote form', 'type' => 'select', 'options' => $quoteForms, 'side' => true, 'default' => QuoteForms::DEFAULT_PRODUCT_FORM, 'help' => 'The form shown on this product page.'],
                    ['key' => 'image', 'label' => 'Main image', 'type' => 'image', 'side' => true, 'help' => 'Used on product cards, menus and the page hero.'],
                    ['key' => 'icon', 'label' => 'Icon', 'type' => 'select', 'options' => $icons, 'side' => true],
                    ['key' => 'featured', 'label' => 'Featured on homepage', 'type' => 'toggle', 'side' => true],
                    ['key' => 'show_in_menu', 'label' => 'Show in Products menu', 'type' => 'toggle', 'side' => true, 'default' => 1],
                    ['key' => 'coming_soon', 'label' => '"New" badge', 'type' => 'toggle', 'side' => true],
                    ['key' => 'needs_review', 'label' => 'Needs content review', 'type' => 'toggle', 'side' => true, 'help' => 'Flags this page on the dashboard until the copy is approved.'],
                    ['key' => 'related', 'label' => 'Related products', 'type' => 'relation', 'source' => 'services', 'side' => true, 'help' => '3–4 products shown at the bottom of the page.'],
                ], $seo),
            ],

            'providers' => [
                'table' => 'providers', 'label' => 'Providers', 'singular' => 'Provider', 'icon' => 'users',
                'titleKey' => 'name', 'slug' => true, 'url' => Content::PROVIDER_BASE . '/{slug}/', 'orderable' => true, 'review' => true,
                'search' => ['name', 'title', 'slug'],
                'columns' => [
                    ['key' => 'photo', 'label' => '', 'type' => 'thumb'],
                    ['key' => 'name', 'label' => 'Name', 'type' => 'title'],
                    ['key' => 'title', 'label' => 'Title', 'type' => 'text'],
                    ['key' => 'type', 'label' => 'Type', 'type' => 'option'],
                    ['key' => 'status', 'label' => 'Status', 'type' => 'status'],
                ],
                'filters' => ['status' => $status, 'type' => $ptypes],
                'groups' => ['content' => 'Profile', 'details' => 'Biography & background', 'seo' => 'SEO'],
                'fields' => array_merge([
                    ['key' => 'name', 'label' => 'Full name', 'type' => 'text', 'required' => true, 'group' => 'content'],
                    ['key' => 'slug', 'label' => 'URL slug', 'type' => 'slug', 'group' => 'content', 'prefix' => '/' . Content::PROVIDER_BASE . '/'],
                    ['key' => 'credentials', 'label' => 'Credentials', 'type' => 'text', 'group' => 'content', 'help' => 'Optional, e.g. a certification. Use verified details only.'],
                    ['key' => 'title', 'label' => 'Role / title', 'type' => 'text', 'group' => 'content'],
                    ['key' => 'short_bio', 'label' => 'Short introduction', 'type' => 'textarea', 'rows' => 3, 'group' => 'content'],
                    ['key' => 'bio', 'label' => 'Biography', 'type' => 'richtext', 'group' => 'details'],
                    ['key' => 'education', 'label' => 'Education', 'type' => 'richtext', 'group' => 'details'],
                    ['key' => 'experience', 'label' => 'Experience', 'type' => 'richtext', 'group' => 'details'],
                    ['key' => 'philosophy', 'label' => 'Approach', 'type' => 'richtext', 'group' => 'details'],
                    ['key' => 'focus', 'label' => 'Areas of focus', 'type' => 'lines', 'group' => 'details', 'help' => 'One per line.'],
                    ['key' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => $status, 'side' => true, 'default' => 'draft'],
                    ['key' => 'type', 'label' => 'Provider type', 'type' => 'select', 'options' => $ptypes, 'side' => true],
                    ['key' => 'photo', 'label' => 'Photo', 'type' => 'image', 'side' => true, 'focal' => 'photo_position', 'help' => 'Click the photo to set the focal point so faces are never cropped.'],
                    ['key' => 'photo_position', 'label' => 'Photo focal point', 'type' => 'hidden', 'side' => true, 'default' => '50% 20%'],
                    ['key' => 'locations', 'label' => 'Locations', 'type' => 'checks', 'options' => self::locationOptions(), 'side' => true],
                    ['key' => 'services', 'label' => 'Relevant services', 'type' => 'relation', 'source' => 'services', 'side' => true],
                    ['key' => 'needs_review', 'label' => 'Needs content review', 'type' => 'toggle', 'side' => true],
                ], $seo),
            ],

            'pages' => [
                'table' => 'pages', 'label' => 'Pages', 'singular' => 'Page', 'icon' => 'file-text',
                'titleKey' => 'title', 'slug' => true, 'url' => '{slug}/', 'orderable' => false, 'review' => true,
                'search' => ['title', 'slug'],
                'columns' => [
                    ['key' => 'title', 'label' => 'Title', 'type' => 'title'],
                    ['key' => 'slug', 'label' => 'URL', 'type' => 'path'],
                    ['key' => 'template', 'label' => 'Template', 'type' => 'option'],
                    ['key' => 'status', 'label' => 'Status', 'type' => 'status'],
                    ['key' => 'updated_at', 'label' => 'Updated', 'type' => 'date'],
                ],
                'filters' => ['status' => $status],
                'groups' => ['content' => 'Content', 'seo' => 'SEO'],
                'fields' => array_merge([
                    ['key' => 'title', 'label' => 'Page title (H1)', 'type' => 'text', 'required' => true, 'group' => 'content'],
                    ['key' => 'slug', 'label' => 'URL slug', 'type' => 'slug', 'group' => 'content', 'prefix' => '/'],
                    ['key' => 'eyebrow', 'label' => 'Eyebrow', 'type' => 'text', 'group' => 'content', 'help' => 'Small label above the title, e.g. ABOUT US.'],
                    ['key' => 'intro', 'label' => 'Intro text', 'type' => 'textarea', 'rows' => 3, 'group' => 'content'],
                    ['key' => 'content', 'label' => 'Body content', 'type' => 'richtext', 'group' => 'content'],
                    ['key' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => $status, 'side' => true, 'default' => 'draft'],
                    ['key' => 'template', 'label' => 'Template', 'type' => 'select', 'side' => true, 'options' => [
                        ['value' => 'default', 'label' => 'Standard content'], ['value' => 'legal', 'label' => 'Legal / narrow'],
                        ['value' => 'treatments', 'label' => 'Products index'],
                        ['value' => 'about', 'label' => 'About'], ['value' => 'contact', 'label' => 'Contact'],
                        ['value' => 'appointment', 'label' => 'Request a quote (wizard)'], ['value' => 'blog', 'label' => 'Blog listing'],
                        ['value' => 'thanks', 'label' => 'Thank you'], ['value' => 'sitemap', 'label' => 'Sitemap'],
                    ]],
                    ['key' => 'image', 'label' => 'Hero image', 'type' => 'image', 'side' => true],
                    ['key' => 'show_cta', 'label' => 'Show "Get a quote" banner', 'type' => 'toggle', 'side' => true, 'default' => 1],
                    ['key' => 'needs_review', 'label' => 'Needs content review', 'type' => 'toggle', 'side' => true],
                ], $seo),
            ],

            'testimonials' => [
                'table' => 'testimonials', 'label' => 'Reviews', 'singular' => 'Review', 'icon' => 'star',
                'titleKey' => 'name', 'slug' => false, 'orderable' => true,
                'search' => ['name', 'content'],
                'columns' => [
                    ['key' => 'name', 'label' => 'Reviewer', 'type' => 'title'],
                    ['key' => 'rating', 'label' => 'Stars', 'type' => 'text'],
                    ['key' => 'content', 'label' => 'Review', 'type' => 'excerpt'],
                    ['key' => 'featured', 'label' => 'Homepage', 'type' => 'bool'],
                    ['key' => 'status', 'label' => 'Status', 'type' => 'status'],
                ],
                'filters' => ['status' => $status],
                'groups' => ['content' => 'Review'],
                'fields' => [
                    ['key' => 'name', 'label' => 'Reviewer (as published)', 'type' => 'text', 'required' => true, 'group' => 'content'],
                    ['key' => 'label', 'label' => 'Label', 'type' => 'text', 'group' => 'content', 'help' => 'e.g. Google review'],
                    ['key' => 'content', 'label' => 'Review text', 'type' => 'textarea', 'rows' => 8, 'group' => 'content', 'help' => 'Use the original wording. Never change a review\'s meaning.'],
                    ['key' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => $status, 'side' => true, 'default' => 'draft'],
                    ['key' => 'rating', 'label' => 'Star rating', 'type' => 'select', 'side' => true, 'default' => '5', 'options' => array_map(fn($n) => ['value' => (string)$n, 'label' => $n . ' stars'], [5, 4, 3, 2, 1])],
                    ['key' => 'featured', 'label' => 'Show on homepage and product pages', 'type' => 'toggle', 'side' => true, 'default' => 1],
                ],
            ],

            'faqs' => [
                'table' => 'faqs', 'label' => 'FAQs', 'singular' => 'FAQ', 'icon' => 'message-square',
                'titleKey' => 'question', 'slug' => false, 'orderable' => true,
                'search' => ['question', 'answer'],
                'columns' => [
                    ['key' => 'question', 'label' => 'Question', 'type' => 'title'],
                    ['key' => 'grp', 'label' => 'Shown on', 'type' => 'option'],
                    ['key' => 'status', 'label' => 'Status', 'type' => 'status'],
                ],
                'filters' => ['status' => $status, 'grp' => self::options(Content::FAQ_GROUPS)],
                'groups' => ['content' => 'Question'],
                'fields' => [
                    ['key' => 'question', 'label' => 'Question', 'type' => 'text', 'required' => true, 'group' => 'content'],
                    ['key' => 'answer', 'label' => 'Answer', 'type' => 'textarea', 'rows' => 6, 'group' => 'content'],
                    ['key' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => $status, 'side' => true, 'default' => 'published'],
                    ['key' => 'grp', 'label' => 'Shown on', 'type' => 'select', 'side' => true, 'options' => self::options(Content::FAQ_GROUPS)],
                ],
            ],

            'locations' => [
                'table' => 'locations', 'label' => 'Shop Location', 'singular' => 'Location', 'icon' => 'map-pin',
                'titleKey' => 'name', 'slug' => true, 'url' => 'contact/#{slug}', 'orderable' => true,
                'search' => ['name', 'address', 'city'],
                'columns' => [
                    ['key' => 'name', 'label' => 'Shop', 'type' => 'title'],
                    ['key' => 'address', 'label' => 'Address', 'type' => 'text'],
                    ['key' => 'phone', 'label' => 'Phone', 'type' => 'text'],
                    ['key' => 'status', 'label' => 'Status', 'type' => 'status'],
                ],
                'filters' => ['status' => $status],
                'groups' => ['content' => 'Shop details', 'hours' => 'Hours', 'map' => 'Map'],
                'fields' => [
                    ['key' => 'name', 'label' => 'Shop name', 'type' => 'text', 'required' => true, 'group' => 'content'],
                    ['key' => 'slug', 'label' => 'Anchor slug', 'type' => 'slug', 'group' => 'content', 'prefix' => '#'],
                    ['key' => 'address', 'label' => 'Street address', 'type' => 'text', 'group' => 'content', 'required' => true],
                    ['key' => 'city', 'label' => 'City', 'type' => 'text', 'group' => 'content', 'half' => true],
                    ['key' => 'state', 'label' => 'State', 'type' => 'text', 'group' => 'content', 'half' => true],
                    ['key' => 'zip', 'label' => 'ZIP', 'type' => 'text', 'group' => 'content', 'half' => true, 'help' => 'Keep NAP (name, address, phone) identical everywhere.'],
                    ['key' => 'phone', 'label' => 'Phone', 'type' => 'text', 'group' => 'content', 'half' => true],
                    ['key' => 'fax', 'label' => 'Fax', 'type' => 'text', 'group' => 'content', 'half' => true],
                    ['key' => 'description', 'label' => 'Short description', 'type' => 'textarea', 'rows' => 3, 'group' => 'content'],
                    ['key' => 'hours', 'label' => 'Opening hours', 'type' => 'repeater', 'group' => 'hours', 'add' => 'Add hours row',
                        'fields' => [['key' => 'days', 'label' => 'Days', 'type' => 'text'], ['key' => 'time', 'label' => 'Hours', 'type' => 'text'], ['key' => 'schema', 'label' => 'Schema (e.g. Mo-Th 07:30-17:30)', 'type' => 'text']]],
                    ['key' => 'map_embed', 'label' => 'Google Maps embed URL or <iframe> code', 'type' => 'textarea', 'rows' => 3, 'group' => 'map', 'help' => 'Optional. Google Maps → Share → Embed a map → copy HTML. Leave empty to auto-generate from the address.'],
                    ['key' => 'directions_url', 'label' => 'Directions link', 'type' => 'text', 'group' => 'map', 'help' => 'Optional Google Business Profile link.'],
                    ['key' => 'latitude', 'label' => 'Latitude', 'type' => 'text', 'group' => 'map', 'half' => true],
                    ['key' => 'longitude', 'label' => 'Longitude', 'type' => 'text', 'group' => 'map', 'half' => true],
                    ['key' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => $status, 'side' => true, 'default' => 'published'],
                    ['key' => 'image', 'label' => 'Shop photo', 'type' => 'image', 'side' => true],
                ],
            ],

            'posts' => [
                'table' => 'posts', 'label' => 'Blog Posts', 'singular' => 'Post', 'icon' => 'file-text',
                'titleKey' => 'title', 'slug' => true, 'url' => '{post_url}', 'orderable' => false, 'review' => true,
                'search' => ['title', 'slug', 'excerpt', 'category'],
                'columns' => [
                    ['key' => 'image', 'label' => '', 'type' => 'thumb'],
                    ['key' => 'title', 'label' => 'Title', 'type' => 'title'],
                    ['key' => 'category', 'label' => 'Category', 'type' => 'text'],
                    ['key' => 'published_at', 'label' => 'Published', 'type' => 'date'],
                    ['key' => 'status', 'label' => 'Status', 'type' => 'status'],
                ],
                'filters' => ['status' => $status],
                'groups' => ['content' => 'Post', 'seo' => 'SEO'],
                'fields' => array_merge([
                    ['key' => 'title', 'label' => 'Title', 'type' => 'text', 'required' => true, 'group' => 'content'],
                    ['key' => 'slug', 'label' => 'URL slug', 'type' => 'slug', 'group' => 'content', 'prefix' => '/YYYY/MM/DD/', 'help' => 'The URL is /year/month/day/slug/ from the publish date, like the old WordPress blog.'],
                    ['key' => 'excerpt', 'label' => 'Excerpt', 'type' => 'textarea', 'rows' => 3, 'group' => 'content', 'help' => 'Shown on the blog listing.'],
                    ['key' => 'content', 'label' => 'Content', 'type' => 'richtext', 'group' => 'content'],
                    ['key' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => $status, 'side' => true, 'default' => 'draft'],
                    ['key' => 'published_at', 'label' => 'Publish date', 'type' => 'datetime', 'side' => true, 'help' => 'Changing the date changes the URL; add a redirect for the old one.'],
                    ['key' => 'category', 'label' => 'Category', 'type' => 'text', 'side' => true, 'default' => 'Uncategorized'],
                    ['key' => 'image', 'label' => 'Featured image', 'type' => 'image', 'side' => true],
                    ['key' => 'needs_review', 'label' => 'Needs content review', 'type' => 'toggle', 'side' => true],
                ], $seo),
            ],

            'redirects' => [
                'table' => 'redirects', 'label' => 'Redirects', 'singular' => 'Redirect', 'icon' => 'route',
                'titleKey' => 'source', 'slug' => false, 'orderable' => false, 'cap' => 'redirects.manage',
                'search' => ['source', 'target', 'note'],
                'columns' => [
                    ['key' => 'source', 'label' => 'From', 'type' => 'title'],
                    ['key' => 'target', 'label' => 'To', 'type' => 'path'],
                    ['key' => 'code', 'label' => 'Type', 'type' => 'text'],
                    ['key' => 'hits', 'label' => 'Hits', 'type' => 'number'],
                ],
                'filters' => [],
                'groups' => ['content' => 'Redirect'],
                'fields' => [
                    ['key' => 'source', 'label' => 'Old path', 'type' => 'text', 'required' => true, 'group' => 'content', 'placeholder' => '/old-page/', 'help' => 'Path on this site, starting with /'],
                    ['key' => 'target', 'label' => 'New path or URL', 'type' => 'text', 'required' => true, 'group' => 'content', 'placeholder' => '/service/new-page/'],
                    ['key' => 'code', 'label' => 'Redirect type', 'type' => 'select', 'group' => 'content', 'default' => '301', 'options' => [['value' => '301', 'label' => '301 — Permanent (SEO)'], ['value' => '302', 'label' => '302 — Temporary']]],
                    ['key' => 'note', 'label' => 'Note', 'type' => 'text', 'group' => 'content'],
                ],
            ],
        ];
    }

    /** [key => label] to the [['value'=>…, 'label'=>…]] option format used by the dashboard. */
    private static function options(array $map): array
    {
        return array_map(fn($k, $l) => ['value' => $k, 'label' => $l], array_keys($map), $map);
    }

    /** Location checkboxes for provider profiles, from the Locations table. */
    private static function locationOptions(): array
    {
        try {
            return array_map(fn($l) => ['value' => $l['slug'], 'label' => $l['name']], DB::all('SELECT slug, name FROM locations ORDER BY sort_order, name'));
        } catch (Throwable $e) {
            return [];
        }
    }

    public static function get(string $type): ?array
    {
        return self::all()[$type] ?? null;
    }

    /** Settings screen definition (admin only). */
    public static function settings(): array
    {
        $tabs = [
            'general' => ['label' => 'General', 'icon' => 'building', 'fields' => [
                ['key' => 'site_name', 'label' => 'Business name', 'type' => 'text'],
                ['key' => 'site_short_name', 'label' => 'Short name', 'type' => 'text'],
                ['key' => 'tagline', 'label' => 'Tagline', 'type' => 'text'],
                ['key' => 'brand_description', 'label' => 'Short description (footer and search engines)', 'type' => 'textarea', 'rows' => 2],
                ['key' => 'phone', 'label' => 'Main phone', 'type' => 'text', 'half' => true],
                ['key' => 'email', 'label' => 'Public email', 'type' => 'text', 'half' => true],
                ['key' => 'notify_email', 'label' => 'Form notification emails', 'type' => 'text', 'help' => 'Quote requests and messages are emailed here. Separate several addresses with commas.'],
                ['key' => 'communities', 'label' => 'Areas served (comma separated)', 'type' => 'text', 'help' => 'Used in local search schema.'],
                ['key' => 'cookie_notice', 'label' => 'Show cookie notice', 'type' => 'toggle'],
            ]],
            'branding' => ['label' => 'Branding', 'icon' => 'sparkles', 'fields' => [
                ['key' => 'logo', 'label' => 'Logo (light backgrounds)', 'type' => 'image', 'help' => 'An SVG or a PNG at least 600 px wide looks sharpest. It is never shown larger than its real size.'],
                ['key' => 'logo_light', 'label' => 'Logo (dark backgrounds)', 'type' => 'image', 'help' => 'Optional white version for the footer.'],
                ['key' => 'favicon', 'label' => 'Favicon', 'type' => 'image'],
                ['key' => 'color_primary', 'label' => 'Primary (teal)', 'type' => 'color', 'third' => true],
                ['key' => 'color_accent', 'label' => 'Accent (orange)', 'type' => 'color', 'third' => true],
                ['key' => 'color_highlight', 'label' => 'Text', 'type' => 'color', 'third' => true],
            ]],
            'homepage' => ['label' => 'Homepage', 'icon' => 'heart', 'fields' => [
                ['key' => 'hero_line1', 'label' => 'Headline', 'type' => 'text', 'half' => true],
                ['key' => 'hero_line2', 'label' => 'Sub-headline', 'type' => 'text', 'half' => true],
                ['key' => 'hero_copy', 'label' => 'Hero text', 'type' => 'textarea', 'rows' => 3],
                ['key' => 'hero_image', 'label' => 'Hero image', 'type' => 'image'],
                ['key' => 'hero_badges', 'label' => 'Hero highlights', 'type' => 'lines', 'help' => 'One per line. Only use real claims.'],
                ['key' => 'home_intro_title', 'label' => '"Need printing?" heading', 'type' => 'text'],
                ['key' => 'home_intro_copy', 'label' => '"Need printing?" text', 'type' => 'textarea', 'rows' => 2],
                ['key' => 'about_image', 'label' => '"Need printing?" image', 'type' => 'image'],
                ['key' => 'home_values', 'label' => 'Value cards', 'type' => 'repeater', 'add' => 'Add card',
                    'fields' => [['key' => 'title', 'label' => 'Title', 'type' => 'text'], ['key' => 'text', 'label' => 'Text', 'type' => 'textarea', 'rows' => 3]]],
                ['key' => 'home_problems', 'label' => 'Problems & solutions band', 'type' => 'textarea', 'rows' => 3],
                ['key' => 'home_speed_title', 'label' => 'Speed band heading', 'type' => 'text'],
                ['key' => 'home_process_title', 'label' => 'Process heading', 'type' => 'text', 'half' => true],
                ['key' => 'home_process_copy', 'label' => 'Process intro', 'type' => 'text', 'half' => true],
                ['key' => 'home_process', 'label' => 'Process steps', 'type' => 'repeater', 'add' => 'Add step',
                    'fields' => [['key' => 'title', 'label' => 'Title', 'type' => 'text'], ['key' => 'text', 'label' => 'Text', 'type' => 'textarea', 'rows' => 2]]],
                ['key' => 'home_ways_title', 'label' => '"Every way you need it" heading', 'type' => 'text'],
                ['key' => 'home_ways_copy', 'label' => '"Every way you need it" text', 'type' => 'textarea', 'rows' => 3],
                ['key' => 'testimonials_image', 'label' => '"Every way you need it" image', 'type' => 'image'],
                ['key' => 'home_ways', 'label' => 'Services', 'type' => 'repeater', 'add' => 'Add service',
                    'fields' => [['key' => 'title', 'label' => 'Title', 'type' => 'text'], ['key' => 'text', 'label' => 'Text', 'type' => 'textarea', 'rows' => 3], ['key' => 'link', 'label' => 'Link (path)', 'type' => 'text']]],
                ['key' => 'featured_services', 'label' => 'Featured products (homepage showcase)', 'type' => 'relation', 'source' => 'services', 'help' => 'Leave empty to show the products marked "Featured on homepage".'],
                ['key' => 'cta_headline', 'label' => 'Closing call-to-action heading', 'type' => 'text', 'half' => true],
                ['key' => 'cta_copy', 'label' => 'Closing call-to-action text', 'type' => 'text', 'half' => true],
            ]],
            'sections' => ['label' => 'Shared Sections', 'icon' => 'layers', 'fields' => [
                ['key' => 'guarantee_title', 'label' => 'Guarantee heading', 'type' => 'text', 'half' => true],
                ['key' => 'guarantee_text', 'label' => 'Guarantee text', 'type' => 'text', 'half' => true],
                ['key' => 'promise_title', 'label' => 'Satisfaction promise heading (product pages)', 'type' => 'text'],
                ['key' => 'promise_text', 'label' => 'Satisfaction promise text', 'type' => 'textarea', 'rows' => 3],
                ['key' => 'start_title', 'label' => '"Let\'s start" heading (product pages)', 'type' => 'text'],
                ['key' => 'start_text', 'label' => '"Let\'s start" text', 'type' => 'textarea', 'rows' => 2],
                ['key' => 'reviews_title', 'label' => 'Reviews heading', 'type' => 'text', 'half' => true],
                ['key' => 'reviews_url', 'label' => 'Google Business Profile URL', 'type' => 'text', 'half' => true, 'help' => 'Optional. Adds a "See all reviews on Google" link.'],
                ['key' => 'newsletter_title', 'label' => 'Footer sign-up heading', 'type' => 'text', 'half' => true],
                ['key' => 'footer_credit', 'label' => 'Footer credit', 'type' => 'text', 'half' => true, 'help' => 'Optional, e.g. "Website by …". Leave empty to hide.'],
            ]],
            'menus' => ['label' => 'Menus', 'icon' => 'menu', 'fields' => [
                ['key' => 'menu_header', 'label' => 'Header menu', 'type' => 'lines', 'help' => 'One link per line: Label | /path/. A line with /products/ opens the Products mega-menu.'],
                ['key' => 'menu_footer', 'label' => 'Footer menu', 'type' => 'lines', 'help' => 'One link per line: Label | /path/'],
            ]],
            'integrations' => ['label' => 'Forms & Integrations', 'icon' => 'zap', 'fields' => [
                ['key' => 'ghl_webhook_url', 'label' => 'Webhook URL for form submissions', 'type' => 'text', 'help' => 'Optional (e.g. a GoHighLevel or Zapier inbound webhook). Every submission is also POSTed here as JSON.'],
                ['key' => 'chat_widget', 'label' => 'Chat / text widget code', 'type' => 'code', 'help' => 'E.g. the LeadConnector chat widget script.'],
                ['key' => 'ga_id', 'label' => 'Google Analytics 4 measurement ID', 'type' => 'text', 'placeholder' => 'G-XXXXXXXXXX'],
                ['key' => 'head_scripts', 'label' => 'Extra <head> code', 'type' => 'code', 'help' => 'Tracking pixels, verification tags, etc.'],
                ['key' => 'body_scripts', 'label' => 'Extra code before </body>', 'type' => 'code'],
            ]],
            'social' => ['label' => 'Social', 'icon' => 'globe', 'fields' => [
                ['key' => 'social_facebook', 'label' => 'Facebook URL', 'type' => 'text'],
                ['key' => 'social_instagram', 'label' => 'Instagram URL', 'type' => 'text'],
                ['key' => 'social_linkedin', 'label' => 'LinkedIn URL', 'type' => 'text'],
                ['key' => 'social_x', 'label' => 'X / Twitter URL', 'type' => 'text'],
            ]],
            'seo' => ['label' => 'SEO', 'icon' => 'search', 'fields' => [
                ['key' => 'seo_noindex', 'label' => 'Hide site from search engines (staging)', 'type' => 'toggle', 'help' => 'Keep ON while on the staging domain. Turn OFF at launch.'],
                ['key' => 'seo_title_suffix', 'label' => 'Title suffix', 'type' => 'text'],
                ['key' => 'seo_default_description', 'label' => 'Default meta description', 'type' => 'textarea', 'rows' => 3],
                ['key' => 'seo_og_image', 'label' => 'Default social share image', 'type' => 'image'],
            ]],
        ];
        // Downloadable files: only when Content::FORM_GROUPS lists some
        $dl = [];
        foreach (Content::FORM_GROUPS as $g) {
            foreach ($g['items'] as $i) {
                if (!empty($i['setting'])) $dl[] = ['key' => $i['setting'], 'label' => $i['label'], 'type' => 'file'];
            }
        }
        if ($dl) {
            $tabs['forms'] = ['label' => 'Downloads', 'icon' => 'file-text', 'fields' => $dl];
        }
        return $tabs;
    }

    /** Clean a submitted value according to its field definition. */
    public static function clean(array $f, $v)
    {
        $type = $f['type'];
        switch ($type) {
            case 'richtext':
                return Html::clean((string)$v);
            case 'code':
                return (string)$v;
            case 'toggle':
                return $v === true || $v === 1 || $v === '1' || $v === 'true' ? 1 : 0;
            case 'number':
                return (int)$v;
            case 'slug':
                return slugify((string)$v);
            case 'select':
                $v = (string)$v;
                $allowed = array_map(fn($o) => (string)$o['value'], $f['options']);
                return in_array($v, $allowed, true) ? $v : (string)($f['default'] ?? ($allowed[0] ?? ''));
            case 'checks':
                $vals = is_array($v) ? $v : csv_list((string)$v);
                $allowed = array_map(fn($o) => (string)$o['value'], $f['options']);
                return implode(',', array_values(array_intersect($allowed, array_map('strval', $vals))));
            case 'relation':
                $vals = is_array($v) ? $v : csv_list((string)$v);
                return implode(',', array_filter(array_map(fn($s) => slugify((string)$s), $vals)));
            case 'repeater':
                $rows = is_string($v) ? json_decode($v, true) : $v;
                $out = [];
                foreach ((array)$rows as $row) {
                    if (!is_array($row)) continue;
                    $r = [];
                    $empty = true;
                    foreach ($f['fields'] as $sf) {
                        $val = trim(strip_tags((string)($row[$sf['key']] ?? '')));
                        if ($sf['key'] === 'schema') {
                            $val = array_values(array_filter(array_map('trim', explode(';', $val))));
                        } elseif ($sf['key'] === 'value' && is_numeric($val)) {
                            $val = (int)$val;
                        }
                        if ($val !== '' && $val !== []) $empty = false;
                        $r[$sf['key']] = $val;
                    }
                    if (!$empty) $out[] = $r;
                }
                return json_encode($out, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            case 'gallery':
                $vals = is_array($v) ? $v : (json_decode((string)$v, true) ?: []);
                $vals = array_values(array_unique(array_filter(array_map(fn($p) => trim((string)$p), $vals), fn($p) => preg_match('~^uploads/~', $p) && !str_contains($p, '..'))));
                return json_encode($vals, JSON_UNESCAPED_SLASHES);
            case 'datetime':
                $t = strtotime((string)$v);
                return $t ? date('Y-m-d H:i:s', $t) : now();
            case 'image':
            case 'file':
                $v = trim((string)$v);
                if ($v === '' || preg_match('~^(uploads/|https://)~', $v)) {
                    return str_contains($v, '..') ? '' : $v;
                }
                return '';
            case 'color':
                return preg_match('/^#[0-9a-f]{6}$/i', (string)$v) ? strtolower($v) : '';
            case 'hidden':
                return mb_substr(trim(strip_tags((string)$v)), 0, 60);
            case 'lines':
            case 'textarea':
                return trim(strip_tags((string)$v));
            default:
                return mb_substr(trim(strip_tags((string)$v)), 0, (int)($f['max'] ?? 500) ?: 500);
        }
    }

    /** Convert a stored row into form values for the editor. */
    public static function present(array $def, array $row): array
    {
        $out = ['id' => (int)$row['id']];
        foreach ($def['fields'] as $f) {
            $v = $row[$f['key']] ?? ($f['default'] ?? '');
            if ($f['type'] === 'gallery') {
                $v = array_values(array_filter((array)json_list((string)$v), 'is_string'));
            } elseif ($f['type'] === 'datetime') {
                $v = $v ? date('Y-m-d\TH:i', strtotime((string)$v)) : '';
            } elseif ($f['type'] === 'repeater') {
                $rows = json_list((string)$v);
                foreach ($rows as &$r) {
                    if (isset($r['schema']) && is_array($r['schema'])) {
                        $r['schema'] = implode('; ', $r['schema']);
                    }
                }
                unset($r);
                $v = $rows;
            } elseif ($f['type'] === 'checks' || $f['type'] === 'relation') {
                $v = csv_list((string)$v);
            } elseif ($f['type'] === 'toggle') {
                $v = (int)$v === 1;
            }
            $out[$f['key']] = $v;
        }
        foreach (['created_at', 'updated_at', 'status', 'is_system'] as $k) {
            if (array_key_exists($k, $row)) {
                $out[$k] = $row[$k];
            }
        }
        if (($def['table'] ?? '') === 'posts' && !empty($row['slug'])) {
            $out['_url'] = Content::postUrl($row);
        } elseif (!empty($def['url']) && !empty($row['slug'])) {
            $out['_url'] = url(str_replace('{slug}', $row['slug'], $def['url']));
        } elseif (($def['table'] ?? '') === 'pages') {
            $out['_url'] = url($row['slug'] . '/');
        }
        return $out;
    }
}
