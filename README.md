# Sprintings — Website + Dashboard

The new **sprintings.com** website (a printing company in Long Beach, CA), replacing WordPress/Elementor. It runs on plain PHP 8 + MySQL with an app-style dashboard at `/admin/`. Staging: **ahrons23.sg-host.com**.

- **`BRIEF.md`**: the project instructions.
- **`BACKEND.md`**: the technical reference for the backend.
- **`app/seed/sprintings/`**: all public content extracted from the WordPress site (see its README).
- **`docs/content-review.md`**: copied text from the old product pages that is held back from the new site.

## Editing the site (Dashboard → `/admin/`)

| To change… | Go to |
|---|---|
| A product's text, images, options, specs, FAQs or quote form | **Products** → the product. Tabs: Overview (name, card summary, intro, gallery), Page sections, Specifications, FAQ, SEO. The side panel sets status, category, **Quote form**, main image and related products. Drag rows in the list to reorder the menu. |
| Blog posts | **Blog Posts**. The URL is `/year/month/day/slug/` from the publish date. |
| About, Contact, Products index, Request a Quote, Thank You, legal pages | **Pages** |
| Homepage text, shared sections (guarantee, reviews heading, footer credit), menus | **Settings** → Homepage / Shared Sections / Menus |
| Phone, email, quote-notification recipients | **Settings** → General (`Form notification emails`, comma separated) |
| Shop address, hours and map | **Settings** → *Shop location & hours* |
| Google reviews | **Reviews** (only published reviews with text and “Show on homepage” appear) |
| Quote requests and artwork files | **Form Inbox** (files download only for signed-in users) |
| The quote-form fields themselves | `app/lib/QuoteForms.php` (generated from the Gravity Forms export; edit the PHP) |

Everything imported is flagged **Needs content review** — approve items from the dashboard home.

## Local development

```bash
npm install
php app/cli/install.php --driver=sqlite --username=admin --email=you@example.com --password='choose-one'
php -S 127.0.0.1:8080 index.php     # site http://127.0.0.1:8080, dashboard /admin/
npm run build                       # after CSS/JS edits; commit the .min files
php app/cli/import-sprintings.php   # re-run the content import (local only)
```

## Deploy

GitHub → Actions → **Deploy to SiteGround** → Run workflow. Tick **first_deploy** the first time, then open `/install/`. See BACKEND.md §9. After the site is installed, deliver content changes as migrations in `app/migrations/`.
