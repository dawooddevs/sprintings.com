# Sprintings content pack

Extracted on 2026-10-15 from the WordPress site https://sprintings.com (Hello Elementor theme, Elementor, ElementsKit, Gravity Forms, Yoast, Trustindex). Sources:

- the database dump
- three WordPress XML exports
- the Elementor site-kit export

**Only public website content is included.** These were deliberately left out:

- user accounts
- Gravity Forms entries (719 customer submissions)
- Elementor form submissions
- comments, logs and analytics tables
- every other option

Do not ask for the database dump to get them; the site doesn't need them.

## Files

| File | Contents |
|---|---|
| `index.json` | Every page and post: title, URL, source file, WordPress ID |
| `pages/*.md` | One file per published page (51). The front matter holds title, URL, parent, WordPress template, featured image and SEO fields (Yoast has no custom titles or descriptions set). The body holds the page content in order: headings, text, lists, images, image galleries, icon boxes (contact details), FAQ toggles and buttons. Placeholders mark shared sections, quote forms, the Google map and the reviews widget. File names follow the URL: `service__business-cards.md` is `/service/business-cards/`. |
| `posts/*.md` | The 5 blog posts (Nov 2022), with date, category, excerpt, featured image and full text. URLs use `/YYYY/MM/DD/slug/`. |
| `templates/*.md` | Shared Elementor parts: `118-head` (header), `119-foot` (footer: "Call now" bar, menu, social links, "Sign up for savings" email form, "Absolutely guaranteed" note, copyright), `142-services-foot` (reviews and promise block used on product pages), `307-contact-section`, `557-call-now-banner`, `1301-reviews-tab`, `1508-global-form`, `2000-form-section`, and `1033-…megamenu` (the Products mega-menu that lists all 41 product pages) |
| `forms.json` | The 20 Gravity Forms definitions: field types, labels, required flags and choices, plus button text, notification recipients and subjects, the confirmation message, and `used_on` (which pages use the form). Form 1 "Main Site Quote" includes an artwork **file upload**. |
| `site.json` | Name, URL and permalink rules; business details (phone, email, address, hours, social links, taken verbatim from the header, footer and contact sections); brand colours and current fonts; the Head and Foot menus |
| `media.json` | All 132 Media Library items: file, URL, alt text, title, size and dimensions, plus `used_on` (108 are used, including the logo and favicon). `referenced_but_not_in_library` lists 1 image the homepage uses that isn't registered in the library (`2022/11/Sprintings_Home_Img_One.png`); check whether it is in the uploads folder. |
| `reviews.json` | The 10 public Google reviews the site shows through the Trustindex widget: name, rating, date, text and reply |

## Media files

The images are **not** in this pack. The user supplies the WordPress uploads (`wp-content/uploads/2022/`) separately, as `sprintings-uploads.zip`. Every used image is in `2022/11/` or `2022/12/`. Import the original files, not WordPress's resized copies (`-300x200` and similar). 16 `.ttf` files in the library are the commercial **Gotham** font: do **not** use or publish them.

## Notes on the content

- **Products:**
  - Product pages live under `/service/` (the `/service/` page itself is empty).
  - There are two generations of product pages. The **2022 set** has IDs 58–81 and slugs such as `business-cards`, `flyer-printing`, `postcards`, `brochures` and `letterheads`. The **later set** has IDs 549–1019 and slugs such as `flyers`, `postcards-paper`, `brochures-2`, `letterheads-paper`, `door-hangers` and `stickers`.
  - Some pairs overlap: Flyers / Flyer Printing, Postcards / Postcards Paper, Brochures / Brochure Printing, Letterheads / Letterheads Paper, Door Hangers / Door Hanger Printing, Stickers / Stickers-Roll Labels / Roll Labels, and the Booklets variants. All of them are linked from the mega-menu and indexed.
- **Empty pages:** `/booklets-and-magazines/`, `/invitations/` (top level) and `/service/` have no content.
- **Copy:** the text contains a few typos from the original site (for example "Gauranteed", "neeeds", "businsesses", "Sumit"). Fix obvious typos; otherwise keep the wording.
