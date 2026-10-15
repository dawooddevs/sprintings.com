<?php
/**
 * Seeds the database on a fresh install: services (app/seed/services.php), core pages and
 * redirects. Runs once, from the installer.
 *
 * Providers, locations, testimonials and homepage FAQs start empty. Add them in the
 * dashboard only from information the practice supplies; never invent them.
 */
function seed_database(): void
{
    $now = now();

    // ---------- Services ----------
    $services = require __DIR__ . '/services.php';
    foreach ($services as $i => $s) {
        $faqs = array_map(fn($f) => ['q' => $f[0], 'a' => $f[1]], $s['faqs'] ?? []);
        DB::insert('services', [
            'slug' => $s['slug'],
            'title' => $s['title'],
            'menu_label' => $s['menu_label'] ?? '',
            'category' => $s['category'],
            'excerpt' => $s['excerpt'],
            'hero_text' => $s['hero_text'],
            'image' => '',
            'icon' => $s['icon'] ?? '',
            'what_is' => Html::paragraphs($s['what_is']),
            'conditions' => $s['conditions'],
            'how_it_works' => Html::paragraphs($s['how_it_works']),
            'benefits' => $s['benefits'],
            'what_to_expect' => Html::paragraphs($s['what_to_expect']),
            'faqs' => json_encode($faqs),
            'related' => $s['related'],
            'body_areas' => $s['body_areas'],
            'featured' => (int)($s['featured'] ?? 0),
            'coming_soon' => (int)($s['coming_soon'] ?? 0),
            'show_in_menu' => (int)($s['show_in_menu'] ?? 1),
            'needs_review' => 1,
            'sort_order' => ($i + 1) * 10,
            'status' => $s['status'] ?? 'published',
            'meta_title' => $s['meta_title'] ?? $s['title'],
            'meta_description' => $s['meta_description'] ?? str_limit($s['excerpt'], 155),
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    // ---------- Pages ----------
    foreach (seed_pages() as $i => $p) {
        DB::insert('pages', array_merge([
            'eyebrow' => '', 'intro' => '', 'image' => '', 'content' => '', 'template' => 'default', 'show_cta' => 1, 'is_system' => 0,
            'needs_review' => 0, 'status' => 'published', 'meta_title' => '', 'meta_description' => '',
        ], $p, ['sort_order' => ($i + 1) * 10, 'created_at' => $now, 'updated_at' => $now]));
    }

    // Sprintings content (products, pages, posts, location, reviews, menus) is imported from
    // app/seed/sprintings/ — see BRIEF.md §6. Redirects for the old URLs are listed there too.
}

function seed_pages(): array
{
    $name = Settings::get('site_name');
    return [
        [
            'slug' => 'privacy-policy', 'title' => 'Privacy Policy', 'eyebrow' => 'Legal', 'template' => 'legal', 'show_cta' => 0, 'needs_review' => 1,
            'intro' => 'How we collect, use and protect information submitted through this website.',
            'content' => '<h2>Information we collect</h2><p>When you request a quote, contact us or sign up for offers on this website, we collect the information you provide, such as your name, email address, phone number, company, project details and any artwork files you upload. We also collect basic, non-identifying usage information (such as pages visited) to improve the website.</p><h2>How we use information</h2><p>We use your information to respond to your request, prepare quotes and proofs, produce and deliver your order, and communicate with you about it. If you sign up for offers, we may email you about promotions; you can unsubscribe at any time. We do not sell your personal information.</p><h2>Artwork and files</h2><p>Files you upload are used only to quote and produce your job and are stored securely.</p><h2>Third-party services</h2><p>This website may use third-party services for maps, reviews and analytics. These providers process information according to their own privacy policies.</p><h2>Contact</h2><p>Questions about this policy? Contact us at ' . Settings::get('email') . '.</p>',
            'meta_title' => 'Privacy Policy', 'meta_description' => 'Privacy policy for the ' . $name . ' website.',
        ],
        [
            'slug' => 'terms-and-conditions', 'title' => 'Terms & Conditions', 'eyebrow' => 'Legal', 'template' => 'legal', 'show_cta' => 0, 'needs_review' => 1,
            'intro' => 'The terms that apply to your use of this website.',
            'content' => '<h2>Website information</h2><p>The content on this website is provided for general information. Product descriptions, options and turnaround times may change, and every order is confirmed with a quote and proof before production.</p><h2>Quotes and orders</h2><p>Submitting a quote request does not create an order. Prices, quantities, materials and delivery dates are confirmed with you before we print.</p><h2>Your artwork</h2><p>You confirm that you have the right to print any artwork, logos or content you send us.</p><h2>Changes</h2><p>We may update these terms from time to time. Continued use of the website means you accept the current terms.</p>',
            'meta_title' => 'Terms & Conditions', 'meta_description' => 'Terms and conditions for the ' . $name . ' website.',
        ],
        [
            'slug' => 'sitemap', 'title' => 'Sitemap', 'eyebrow' => 'Sitemap', 'template' => 'sitemap', 'is_system' => 1, 'show_cta' => 0,
            'intro' => 'Every page on this website in one place.',
            'meta_title' => 'Sitemap', 'meta_description' => 'A complete list of pages on the ' . $name . ' website.',
        ],
    ];
}
