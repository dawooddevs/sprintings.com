# Sprintings Website Rebuild Brief

**Business:** Sprintings, a commercial printing company in Long Beach, CA ("Printing. Done. Right.")  
**Current site:** https://sprintings.com (WordPress, Hello Elementor theme, Elementor, Gravity Forms)  
**New site (staging):** https://ahrons23.sg-host.com (SiteGround)  
**Repository:** the new GitHub repository attached to this session  
**Kit:** `sprintings-site-kit.zip` (attached)

This brief is written for Claude Code. Read all of it, then `BACKEND.md` and `app/seed/sprintings/README.md`, before starting. It overrides your defaults, and where it conflicts with BACKEND.md, this brief wins.

---

## 1. The task

Rebuild sprintings.com as a **stunning, modern, "next-generation" website** on the custom PHP backend and dashboard in the kit, not on WordPress. Keep every piece of real content, every product, the quote forms and every existing URL. Then deploy it to ahrons23.sg-host.com over SSH from GitHub Actions.

The backend is finished and must be **reused, not rebuilt**:

- dashboard and JSON API
- database layer
- media library and form inbox
- users and roles, settings
- SEO/schema, redirects
- installer and migrations
- deploy workflow

It is the same system that runs another live site of ours, and BACKEND.md documents it. Your work:

- **Design and frontend:** a new design for the whole public website.
- **Backend extensions:** products, blog posts, quote forms with file upload (§6).
- **Content import:** all extracted content and media (§7).
- **Testing, then deploy** (§10–§11).

---

## 2. What you have been given

| Item | Where |
|---|---|
| Backend + dashboard | Kit root. Unzip the kit and move the contents of `sprintings-site/`, **including hidden files** (`.github/`, `.htaccess`, `.user.ini`, `.gitignore`), to the repository root. |
| All public content from the old site | `app/seed/sprintings/`: 51 pages, 5 blog posts, header, footer and shared sections, 20 quote-form definitions, menus, business details, 10 public Google reviews, and the media list. Read its README. |
| Images | The WordPress `wp-content/uploads/2022/` folder, which holds all 108 images the site uses (in `2022/11/` and `2022/12/`). The zip is too large to attach, so **the user has extracted it on the SiteGround server**, in `www/ahrons23.sg-host.com/sprintings-import/`, next to `public_html` and not web-accessible. Bring the images into the repository with the workflow **`.github/workflows/fetch-media.yml`** ("Fetch Sprintings media") once the SSH secrets are set: run it (workflow_dispatch) on your working branch, read its log, then `git pull`. It copies only the original files the site uses (from `media.json`) into `app/seed/sprintings/media/`, skips resized copies and fonts, warns about any missing file, and commits. If the user attaches the images directly instead, put them in the same folder. |
| Database dump, XML exports, Elementor kit | **Not needed and not provided.** Everything public was already extracted into the content pack. Private data (customer form submissions, user accounts) was deliberately excluded. Never request it, and never commit any of it. |

You probably cannot reach sprintings.com: SiteGround's bot protection blocks automated requests, and you must not try to get around it. Work from the content pack and the uploads zip. If the fetch workflow can't find the images, ask the user once to check the server folder, and build everything else in the meantime.

---

## 3. Rules

1. **Keep the real content.** Use the text from the content pack. You may:
   - tighten it for the new layout (split long paragraphs, write short card summaries and section intros taken from the existing text)
   - fix obvious typos (for example "Gauranteed", "neeeds", "businsesses", "Sumit")

   You may **not** invent:
   - facts, prices, turnaround promises or statistics
   - client logos or awards
   - testimonials beyond `reviews.json`
   - claims such as "#1 in California"

   The existing claims you can use: same-day service, the "100% money refund if not satisfied" guarantee, "Absolutely guaranteed. Every time. Any reason. Or we'll make it right.", local Long Beach team, best-in-class printers.
2. **Keep every URL** (§8). Old URLs that change, or empty pages, get 301 redirects.
3. **Don't use Gotham.** The library contains Gotham `.ttf` files, a commercial font, so don't publish them. Use a free alternative (§5).
4. **Accessibility and performance** are part of "next-gen" (§5.4).
5. **Security:**
   - Never commit `app/config.php`, databases, uploads of customer files, or secrets.
   - Uploaded artwork must not be publicly reachable (§6.3).
   - Do not bypass sgcaptcha.
6. **Search engines:** keep **Hide site from search engines** ON on staging.
7. **Git:** commit with clear messages, push to the branch this session tells you, and do not open pull requests unless asked.

---

## 4. Brand and business facts

These come from `site.json`:

| | |
|---|---|
| Name | **Sprintings**. The "(S)Print" wordplay is part of the voice ("Let's (S)Print Together", "Ready. Set. (S)Print!", "We (S)Print"). |
| Tagline | "Printing. Done. Right." |
| Phone | (562) 294-3207. Link it as `tel:+15622943207`. |
| Email | sales@sprintings.com |
| Address | 520 W. Willow Street, 101-F, Long Beach, CA 90806 |
| Hours | Mon–Thu 9:00am–6:00pm · Fri 9:00am–4:00pm · Sat–Sun closed |
| Social | facebook.com/SPRINTINGCA · instagram.com/sprintingca · linkedin.com/company/sprinting |
| Colours | Teal `#246B7F` (primary), Orange `#F56F46` (accent), text `#4D5862`, near-black `#242C33` |
| Logo | `2022/11/Sprintings_Logo.png`, only 300×92 px; favicon `2022/11/Sprintings_Favicon.png` (32 px) |

**Logo:** ask the user once for a high-resolution or SVG logo. Until then, use the PNG at its natural size or smaller on screen, never scaled up. Do not redraw or recreate the logo.

---

## 5. Design direction: "next-gen"

The current site is a standard 2022 Elementor build. The new one should feel like a **premium, modern print brand**: bold, tactile and fast. The products are physical things (paper, foil, stock, finishes), so let the design celebrate print. The direction below is a starting point; use good judgement.

### 5.1 Look and feel

- **Typography:**
  - Headings: a geometric sans close to Gotham's spirit. **Montserrat** is the closest free match. "Plus Jakarta Sans" or "Outfit" also work.
  - Body: **Inter**.
  - Self-host the fonts in `assets/fonts/` (`npm i @fontsource-variable/...` and copy the woff2 files).
  - Use big, confident display sizes for headlines, with tight tracking.
- **Colour:**
  - Teal as the brand base. Use orange for calls to action and energy.
  - Allow both deep-teal dark sections and light "paper-white" sections.
  - Add subtle paper-texture or grain overlays and CMYK-inspired accents, for example thin cyan, magenta and yellow registration-mark motifs and crop marks used as decorative details.
- **Imagery:**
  - The product photography in the uploads (tall 1400×1920 product images, product shots and the animated GIF) becomes large, layered cards with soft shadows, slight 3D tilt on hover, and "stacked paper" effects.
  - Convert large PNGs to WebP or AVIF through the media pipeline.
- **Motion:**
  - Smooth, purposeful scroll reveals.
  - A marquee of product names.
  - Magnetic or animated CTA buttons.
  - Counters only for real numbers; there are none in the content, so don't invent any.
  - A playful "sprint" motif (motion lines or a progress bar).
  - All motion respects `prefers-reduced-motion`.
- **Layout:**
  - Generous whitespace, a bento-grid product overview, and sticky section headers on long pages.
  - A persistent "Get a Quote" call to action: in the header on desktop, as a floating pill on mobile.
  - A visible click-to-call phone number.

### 5.2 Key pages and components

- **Header:**
  - Logo.
  - **Products mega-menu** with all products grouped by category, with icons and search-as-you-type.
  - About, Blog, Contact.
  - Phone, and the "Get A Quote" button.
  - A slide-in panel on mobile.
- **Home:** rebuild the existing sections in a modern way, in this order:
  - hero "Printing. Done. Right." / "Same Day Service, Top Quality", with the CTA
  - "Need Printing? Let's (S)Print Together", with the three value cards: custom printing, trusted customer service, fast results
  - the problems and solutions band
  - "Unmatched speed and friendly, expert help"
  - "Ready. Set. (S)Print!", a 3-step process: Upload Artwork, Order Approval, We (S)Print
  - "Printing. Done. Right. Every Way You Need It.": Binding, Customized Design, Highest Quality Paper Stock
  - a product showcase (bento grid by category)
  - the Google reviews (`reviews.json`)
  - the satisfaction guarantee
  - the quote / contact block with the map

  Use `pages/home.md` for all the text.
- **Products index (`/products/`):** a filterable grid by category with search, where each card shows image, name and a one-line summary.
- **Product page (`/service/<slug>/`), one template for all 41 products:**
  - hero with product name, intro and gallery (the image carousel images)
  - "We have the perfect … to fit your needs" options as chips
  - the sales sections: "Spread the Word…", "Our Service Promise", "Design Details", "Related Products"
  - FAQs where present
  - the **product's own quote form** (§6.2), in a sticky side panel on desktop
  - contact details and map
  - related products
- **Get a Quote (`/request-a-quote/`):** the main quote form, as a polished **multi-step wizard** (§6.2).
- **About (`/about-us/`), Contact (`/contact/`), Blog (`/blog/` plus posts), Thank You (`/thank-you/`, noindex), Privacy and Terms** (already seeded, `needs_review`).
- **Footer:**
  - "Call Now" bar
  - menu, social links
  - "Sign up for savings" email form (§6.2)
  - "Absolutely guaranteed — Every time. Any reason. Or we'll make it right."
  - address and hours
  - © Sprintings with the current year
  - legal links
  - The old footer says "Website By Verimo Media". Ask the user which credit, if any, to show; don't copy it automatically.

### 5.3 What to avoid

Generic template look, stock "hero with a laptop", invented numbers, low-contrast grey-on-white text, layout shift, and heavy JavaScript frameworks. Plain HTML, CSS and vanilla JS, like the kit, is the expectation.

### 5.4 Quality bar

- **Lighthouse mobile:** Performance ≥ 90; Accessibility, Best Practices and SEO ≥ 95 (SEO's noindex warning on staging is expected).
- **Accessibility:**
  - WCAG AA contrast. Check orange `#F56F46` with white text: it is about 2.9:1 and **fails** for normal text. Use a darker orange such as `#C94A22` for text buttons, or near-black text on orange.
  - Keyboard access for the mega-menu, wizard, galleries and accordions; visible focus.
- **Layout:** no horizontal scrolling at 360, 390, 768, 1024 or 1440 px. Images have width and height set; fonts load with `font-display: swap`.

---

## 6. Backend extensions

Follow the existing patterns in `app/lib/Resources.php`, `admin/api.php`, `admin/assets/admin.js`, `app/schema.php` and `Content.php`.

### 6.1 Products (the existing "services" resource)

- **Naming:** relabel the resource **Products** everywhere in the dashboard: sidebar, titles, buttons and health checks.
- **Remove medical wording.** The backend was first built for a medical practice. Change these labels:

  | Old label | New label |
  |---|---|
  | sidebar group "Patients" | "Customers" |
  | "Patient" / "Patient name" on testimonials | "Reviewer" |
  | Settings group "Patient Forms" (and `Content::FORM_GROUPS`) | "Downloads", or hide it if unused |
  | FAQ group "New patients" | "Ordering" |
  | Settings subtitle mentioning "patient forms" | (remove the mention) |

  Also replace the medical appointment form fields in `Forms.php` (§6.2). Search the code for `patient`, `treatment`, `provider` and `insurance` when finishing, and fix any user-visible leftovers.
- **URLs:** `Content::SERVICE_BASE` is already `'service'`, so product URLs are `/service/<slug>/` as on the old site. The products index is the page `/products/`, using the `treatments` template, renamed in the dashboard to "Products index".
- **Field mapping.** Keep the columns, change the labels, and hide `body_areas`:

  | Column | Dashboard label |
  |---|---|
  | `excerpt` | Card summary |
  | `hero_text` | Intro |
  | `what_is` | Overview, the "Spread the Word…" section |
  | `conditions` | Options / types (one per line), the "perfect … to fit your needs" list |
  | `how_it_works` | Service promise |
  | `benefits` | Design details (one per line) |
  | `what_to_expect` | Related products text |
  | `faqs` | FAQs |
  | `related` | Related products |
  | `image` | Main image |

- **New columns.** Add them in `schema.php` (fresh installs) and in a migration (existing installs):
  - `gallery`: JSON list of media paths, edited with the media picker (multi-select)
  - `quote_form`: the key of a quote-form definition (§6.2), chosen from a select
- **Categories:** `Content::CATEGORIES` has a first proposal of 7 print categories. Assign every product, and adjust the categories if a better grouping emerges.
- **Hide unused modules:** Providers and Locations from the sidebar.
  - Keep **one Location**: the Long Beach shop, with hours, used for the footer, map and LocalBusiness schema.
  - Rename **Testimonials** to **Reviews**, and import `reviews.json` with the label "Google review".
- **Schema:** `Seo::organization()` outputs `MedicalBusiness`. Change it to `LocalBusiness` (Sprintings is a print shop), with address, geo (from the location), opening hours, phone and `sameAs` social links.

### 6.2 Quote forms

`app/lib/Forms.php` currently defines medical appointment, contact and benefits forms. Replace them with Sprintings' forms, defined from `forms.json`:

| Key | From Gravity Form | Used on |
|---|---|---|
| `quote` | #1 Main Site Quote: name, email\*, phone\*, company, address, project name, quantity, size, paper, color, finish, **file upload**, comments | `/request-a-quote/`, header CTA |
| `contact` | #2 Contact Us Directly | `/contact/` |
| `product_general` | #3 Product General Form | products using #3 |
| `paper_size` | #5 Paper Size Form | products using #5 |
| one per product form | #6–#20, for example Booklets, Calendars, Club Flyers, Stickers. Each has product-specific fields such as Paper Size, Paper Stocks, Print Options, Binding Options and Specialty Finishes. | `used_on` in forms.json |
| `newsletter` | the footer "Sign up for savings" Elementor form (email only) | footer |

**Implementation:**

- **Definitions:**
  - Store them in one PHP config array (`app/lib/QuoteForms.php`) generated from forms.json: key, title, fields (name, label, type, required, choices, placeholder), submit label and success message.
  - Use snake_case field names.
  - Mark email and phone required where Gravity Forms did. Form #4 is trashed; skip it.
- **Products:** give each product the `quote_form` from forms.json `used_on`. Products not listed use `product_general`.
- **Rendering:**
  - One partial renders any definition.
  - The main quote form becomes a 3-step wizard: contact details → project specs → artwork upload and comments.
  - It works **without JavaScript** too, as one long form.
- **Submission:** keep the backend's `/api/form` flow, extended so that:
  - **Consent:** there is no consent checkbox on the original forms, so make consent optional per form. Add a short privacy notice line under each form linking to `/privacy-policy/`.
  - **Name:** `name` fills the inbox name; for the quote form, use first and last name.
  - **Email subjects:** "New Quote Request — {form title}" for quotes, "New submission from {form title}" for others.
  - **Recipients:** set `notify_email` to `sales@sprintings.com, support@theattitudemarketing.com`, the same recipients as the old notifications. Accept a comma-separated list. Make the installer keep a value the seed already set.
  - **Auto-reply:** keep the customer auto-reply. Gravity Forms sent "We have received your inquiry" to the submitter. The message is: "Thank you for contacting us! We will get in touch with you shortly."
  - **Response:** JSON success or error for AJAX submissions, and a redirect to `/thank-you/` for the no-JavaScript version.
- **Dashboard:** the Form Inbox shows the product, all fields and the attachment.

### 6.3 Artwork file upload (new)

- **Accepted files:** PDF, AI, EPS, PSD, PNG, JPG, TIFF and ZIP, **up to 50 MB** each, up to 5 files per submission. Validate both the extension and the MIME type.
- **Storage:**
  - Store files **outside the web root, or in `app/storage/submissions/`**, which `.htaccess` already blocks.
  - Use random file names, and keep the original name in the database.
  - The current deploy excludes `app/storage/*.sqlite*`, `*.log` and `sessions/`. **Add `app/storage/submissions/` to the rsync excludes**, so deploys never touch customer files.
- **Access:** downloads go only through an authenticated dashboard endpoint, `admin/api.php?r=submissions.file&id=…`, which streams the file.
- **Email:** show download links in the notification email, pointing to the dashboard. Never attach large files.
- **Rate limits:** keep the existing rate limits, and count upload bytes.

### 6.4 Blog (new resource)

The kit has no blog. Add a `posts` resource:

- **Table and fields:** slug, title, excerpt, content (rich text), featured image, category, published_at, status, SEO title and description, needs_review.
- **Dashboard:** CRUD and search like Pages.
- **Public routes:**
  - `/blog/`: the listing, paginated, 9 per page
  - `/YYYY/MM/DD/<slug>/`: the post, using the same permalinks as WordPress
  - `/blog/feed/` (RSS) is optional
  - `/category/<slug>/` only if more than one category exists
- **Sitemap:** add posts to `sitemap.xml`.
- **Post page:** reading time, related posts, and a share link.

### 6.5 Pages and other content

- **Pages:** About, Contact, Thank You, the products index and the legal pages are records in the pages table. Their text comes from the content pack. Give About and Contact templates of their own.
- **Shared sections** ("Services Foot", "Call Now Banner", "Contact Section"): frontend partials. Keep their text editable through settings where it is likely to change, such as the guarantee text.
- **FAQs:** the `toggle` items on pages become FAQs; attach them to their product or page.

---

## 7. Importing the content

Build the import into the seed (`app/seed/seed.php`), so `/install/` creates the complete site. Also add a re-runnable `app/cli/import-sprintings.php` for local resets. After the server install, deliver further content changes as **migrations** only.

1. **Products:** the 41 pages under `/service/` (`pages/service__*.md`). Parse the Markdown by its headings:
   - the first `##` is the title
   - the first paragraph is the intro
   - "We have the perfect … to fit your needs" with its list becomes the options
   - "Spread the Word…" is the overview
   - "Our Service Promise" is the service promise
   - "… Design Details" is the design details list
   - "…: Related Products" is the related-products text
   - "Image gallery: …" is the gallery
   - the first image is the main image

   Pages that don't follow the pattern exactly, such as `club-flyers`, `election-printing`, `folded-business-cards` and `rip-business-cards`, need a closer read; keep all of their text. Write excerpts of 1–2 sentences from the page's own text. Set `needs_review = 1` on everything imported.
2. **Pages:** Home, About Us, Contact, Products, Blog, Request a Quote and Thank You, from `pages/*.md`.
3. **Blog:** the 5 posts, from `posts/*.md`, keeping dates and URLs.
4. **Settings:** business details, social links, colours and the logo, from `site.json`.
5. **Reviews:** from `reviews.json`.
6. **Location:** the Long Beach location, with hours.
7. **Menus:** the header and footer from `site.json` menus. The Products menu lists every product by category.
8. **Media:**
   - Import the **original** files used (`media.json` → `used_on` not empty), from `app/seed/sprintings/media/` (filled by the fetch workflow, §2), into the Media Library through the backend's pipeline: database row, WebP version, thumbnail and dimensions. `Media::upload()` expects an HTTP upload, so add a `Media::importLocal($path, $folder)` that reuses the same processing.
   - Use the alt text from media.json, or a readable version of the file name.
   - **Skip:** WordPress's resized copies (`-300x200` and similar) and the Gotham `.ttf` files.
   - The originals stay in the repository under `app/seed/sprintings/media/` (about 45 MB), so the installer can import them on the server. After the import is verified, the user may delete `sprintings-import/` on the server.
   - **Convert the huge PNGs** (several are 1400×1920 and 2–3.6 MB) to high-quality JPEG or WebP before committing. Keep transparency where it is actually used.
9. **SEO:** Yoast has no custom titles or descriptions.
   - Titles: `<Page or product title> | Sprintings`.
   - Descriptions: written from each page's own first paragraph, under 155 characters, with `needs_review = 1`.

---

## 8. URLs and redirects

**Keep these URLs, all of them live:**

- `/`, `/about-us/`, `/products/`, `/blog/`, `/contact/`, `/request-a-quote/`, `/thank-you/` (noindex)
- the 41 `/service/<slug>/` product pages
- the 5 posts at `/2022/11/<dd>/<slug>/`

**301 redirects:**

| From | To |
|---|---|
| `/service/` (empty page) | `/products/` |
| `/booklets-and-magazines/` (empty) | `/service/booklets-magazines-catalogs/` |
| `/invitations/` (empty) | `/service/invitations/` |
| `/category/uncategorized/` | `/blog/` |

**Near-duplicate product pages:** keep all of them live, and **report the pairs to the user** (the list is in the content pack README) with a recommendation. Do not merge or redirect them without approval. Their quote forms differ; the newer set has product-specific forms.

---

## 9. Integrations

- **Google map:** the old site embedded a Google Map of the service area. Use a lightweight click-to-load map, or an embed of the address, lazy-loaded.
- **Google reviews:** show `reviews.json` natively, with the "Google review" label and star rating. Link to the Google profile if the user provides the URL.
- **Analytics and chat:** Settings → Integrations accepts GA4, a chat widget and head/body scripts. Leave them empty unless the user supplies codes.

---

## 10. Testing (required before every deploy)

- **Local check:**
  - PHP lint passes.
  - Install locally with SQLite, and run the importer.
  - Every URL in §8 returns 200 or the right 301.
- **Crawl:**
  - No internal 404s.
  - No links to `sprintings.com/wp-content` or other WordPress paths.
  - No `elementor` or `gravityform` leftovers in the HTML.
- **Forms:**
  - Submit each form type: quote with a file, contact, a product form, newsletter.
  - Each appears in the Form Inbox, the file downloads only when logged in, and a direct URL to the file returns 403 or 404.
- **Playwright screenshots** of home, products index, 3 product pages, request-a-quote, about, contact, blog and a post, at 390 px and 1440 px. Review them yourself, and fix spacing, overflow and contrast before showing the user.
- **No errors:** no console errors and no PHP warnings.
- **Lighthouse** against the targets in §5.4.

---

## 11. Deploy to SiteGround (ahrons23.sg-host.com)

The user sets up the SSH key and GitHub secrets. You run the workflow. Follow `BACKEND.md` §9:

1. **Secrets:** confirm the user has added `SSH_HOST`, `SSH_USER`, `SSH_PRIVATE_KEY` and, if used, `SSH_PASSPHRASE`. The default target is `www/ahrons23.sg-host.com/public_html/`. The port is 18765.
2. **Empty folder:** `public_html` must be empty, or hold only SiteGround's default page. The user removed the previous site; the first-deploy check stops if another app's `index.php` is present.
3. **First deploy:** run **Deploy to SiteGround** with **first_deploy** ticked. Read the job log.
4. **Install:** the user opens `https://ahrons23.sg-host.com/install/`, chooses MySQL (host `localhost`), creates the admin account and keeps "Hide from search engines" ON. **Database credentials are typed only into the installer, never into chat.**
5. **Later deploys:** without first_deploy. They run migrations automatically.
6. **Checking:** you may not be able to load the live site, because of sgcaptcha. Ask the user to check pages and send a test quote, with a file, from their browser.
7. **Email:** sent with PHP `mail()` from SiteGround. If the test emails land in spam, tell the user how to set up SPF/DKIM in Site Tools → Email → Authentication.

---

## 12. Order of work and reporting

1. Set up the repository from the kit. Once the user confirms the SSH secrets, run **Fetch Sprintings media** and `git pull` (§2).
2. Backend extensions (§6), then the importer (§7). Check the content locally.
3. Design system: tokens, type and components. Then build the pages. **Show the user screenshots of the homepage and one product page early** for feedback, before building everything.
4. Testing (§10), then deploy (§11).
5. **Final report:**
   - what was built and what is live
   - the duplicate-product recommendation
   - items needing the user: a high-resolution logo, the Google reviews profile URL, the footer credit, email authentication, and the `needs_review` content to approve in the dashboard
   - how to edit products, quote forms and blog posts in the dashboard

Keep progress messages short.
