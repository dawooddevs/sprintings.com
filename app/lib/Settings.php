<?php
/**
 * Key/value site settings with sensible defaults. Everything here is editable
 * from Dashboard → Settings.
 */
final class Settings
{
    private static ?array $cache = null;

    public static function defaults(): array
    {
        return [
            // General
            'site_name' => 'Sprintings',
            'site_short_name' => 'Sprintings',
            'tagline' => 'Printing. Done. Right.',
            'brand_description' => 'Sprintings is a full-service print and binding firm based in Long Beach, California.',
            'phone' => '(562) 294-3207',
            'sms_phone' => '',
            'email' => 'sales@sprintings.com',
            'notify_email' => 'sales@sprintings.com, support@theattitudemarketing.com',
            'communities' => 'Long Beach, CA',
            'founded_year' => '',
            'cookie_notice' => '0',

            // Branding (the dashboard also uses color_primary and color_accent)
            'logo' => '',
            'logo_light' => '',
            'favicon' => '',
            'color_primary' => '#246b7f',
            'color_accent' => '#f56f46',
            'color_highlight' => '#4d5862',

            // Homepage (text from the old homepage; editable in Settings → Homepage)
            'hero_line1' => 'Printing. Done. Right.',
            'hero_line2' => 'Same Day Service, Top Quality',
            'hero_copy' => 'Do you have a printing idea in mind? Our printing experts are ready to help you bring your ideas to life. Contact us today so we can help you with your project.',
            'hero_image' => '',
            'hero_badges' => "Same-day service\n100% money refund if not satisfied\nLocal Long Beach team",
            'home_intro_title' => 'Need Printing? Let’s (S)Print Together.',
            'home_intro_copy' => 'Customers consistently return to Sprintings due to our incredible speed and quality of customer service.',
            'about_image' => '',
            'home_values' => json_encode([
                ['title' => 'Custom Printing For Every Need', 'text' => 'We take care of all your business or organization’s printing needs, no matter how large or small it is, or the type of printing. If you can imagine it, we’ll print it.'],
                ['title' => 'Trusted Customer Service', 'text' => 'Forget online printing stores where nobody listens to you. Our sales experts will provide personal support via phone or email and ensure you get what you need done, fast!'],
                ['title' => 'Fast Results', 'text' => 'Fast delivery is the backbone of Sprintings. Our team is wired and ready to Sprint your job to the finish line and out for delivery for your events, marketing and branding needs.'],
            ], JSON_UNESCAPED_UNICODE),
            'home_problems' => 'Got printing problems? We have the solutions! Envelopes, brochures, flyers, cards and much more. Let us help you make a crater-sized impact with your next project.',
            'home_speed_title' => 'Unmatched Speed And Friendly, Expert Help',
            'home_process_title' => 'Ready. Set. (S)Print!',
            'home_process_copy' => 'Our process is designed to be fast and effortless for you:',
            'home_process' => json_encode([
                ['title' => 'Upload Artwork', 'text' => 'We’ll validate your art file to ensure print quality. If you aren’t ready with art, we’ll help create beautiful designs for your brand.'],
                ['title' => 'Order Approval', 'text' => 'You’ll get a proof of your print job for approval, along with a full price quote.'],
                ['title' => 'We (S)Print', 'text' => 'Your stunning print materials roll off the press and ship out – fast!'],
            ], JSON_UNESCAPED_UNICODE),
            'home_ways_title' => 'Printing. Done. Right. Every Way You Need It.',
            'home_ways_copy' => 'We’re helping thousands of businesses and organizations cut through the noise of a cluttered digital world with memorable impressions in print. Here are some of the ways we can help you make an impact with print.',
            'testimonials_image' => '',
            'home_ways' => json_encode([
                ['title' => 'Binding', 'text' => 'Give your content a stellar packaging. Beautiful binding for your single book mementos, weekly magazines or functional and inexpensive covers for your textbooks and phone books. Our binding service is flexible, budget-friendly and comes in attractive options.', 'link' => '/service/books/'],
                ['title' => 'Customized Design', 'text' => 'Our skilled team of graphic designers will understand your requirements, guide you and deliver design solutions suited to your taste.', 'link' => '/request-a-quote/'],
                ['title' => 'Highest Quality Paper Stock', 'text' => 'Glossy or matte, recycled or uncoated, thick or premium, we have the best paper stock with options that you will love. Our team of experts will guide you in choosing the paper that fits your design and budget, and will ensure superb quality prints that look great.', 'link' => '/products/'],
            ], JSON_UNESCAPED_UNICODE),
            'stats' => json_encode([]),
            'featured_services' => '',
            'cta_headline' => 'Get Your Printing Job Started Today',
            'cta_copy' => 'We Respond Surprisingly Quickly',
            'cta_small_print' => '',

            // Shared sections
            'guarantee_title' => 'Absolutely guaranteed',
            'guarantee_text' => 'Every time. Any reason. Or we’ll make it right.',
            'promise_title' => '100% Satisfaction Guaranteed',
            'promise_text' => 'From the moment you reach out to us, to the final printed product delivered to your door, we’ll ensure you are 100% satisfied with our speed, service and print quality.',
            'start_title' => 'Let’s Start (S)Printing Together!',
            'start_text' => 'Contact us for a quote today. Our printing experts are ready to assist you with your order.',
            'reviews_title' => 'Don’t Just Take Our Word for It',
            'reviews_url' => '',
            'newsletter_title' => 'Sign up for savings',
            'footer_credit' => '',

            // Menus ("Label | /path/" per line)
            'menu_header' => "About Us | /about-us/\nProducts | /products/\nBlog | /blog/\nContact | /contact/",
            'menu_footer' => "Home | /\nAbout Us | /about-us/\nProducts | /products/\nBlog | /blog/\nContact | /contact/",

            // Integrations
            'ghl_webhook_url' => '',
            'chat_widget' => '',
            'head_scripts' => '',
            'body_scripts' => '',
            'ga_id' => '',

            // Social
            'social_facebook' => 'https://www.facebook.com/SPRINTINGCA/',
            'social_instagram' => 'https://www.instagram.com/sprintingca',
            'social_linkedin' => 'https://linkedin.com/company/sprinting',
            'social_x' => '',

            // SEO
            'seo_title_suffix' => ' | Sprintings',
            'seo_default_description' => 'Sprintings is a full-service print and binding shop in Long Beach, CA: same-day service, top quality and a local team that listens.',
            'seo_og_image' => '',
            'seo_noindex' => '1',
        ];
    }

    private static function load(): array
    {
        if (self::$cache === null) {
            self::$cache = [];
            if (DB::connected()) {
                try {
                    foreach (DB::all('SELECT k, v FROM settings') as $r) {
                        self::$cache[$r['k']] = $r['v'];
                    }
                } catch (Throwable $e) {
                    // table may not exist yet during install
                }
            }
        }
        return self::$cache;
    }

    public static function get(string $key, $default = null)
    {
        $all = self::load();
        if (array_key_exists($key, $all) && $all[$key] !== null) {
            return $all[$key];
        }
        $d = self::defaults();
        return $d[$key] ?? $default;
    }

    public static function all(): array
    {
        return array_merge(self::defaults(), array_filter(self::load(), fn($v) => $v !== null));
    }

    public static function set(string $key, $value): void
    {
        $value = is_array($value) ? json_encode($value) : (string)$value;
        if (DB::val('SELECT COUNT(*) FROM settings WHERE k = ?', [$key])) {
            DB::update('settings', ['v' => $value], 'k = ?', [$key]);
        } else {
            DB::insert('settings', ['k' => $key, 'v' => $value]);
        }
        self::$cache = null;
    }

    public static function flush(): void
    {
        self::$cache = null;
    }
}
