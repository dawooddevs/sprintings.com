# Sprintings — Website + Dashboard

The new **sprintings.com** website (a printing company in Long Beach, CA), replacing WordPress/Elementor. It runs on plain PHP 8 + MySQL with an app-style dashboard at `/admin/`. Staging: **ahrons23.sg-host.com**.

- **`BRIEF.md`** — the project instructions: what to build and how to check it.
- **`BACKEND.md`** — the technical reference for the backend.
- **`app/seed/sprintings/`** — all public content extracted from the WordPress site (see its README).

## Local development

```bash
npm install
php app/cli/install.php --driver=sqlite --username=admin --email=you@example.com --password='choose-one'
php -S 127.0.0.1:8080 index.php     # site http://127.0.0.1:8080, dashboard /admin/
npm run build                       # after CSS/JS edits; commit the .min files
```

## Deploy

GitHub → Actions → **Deploy to SiteGround** → Run workflow. Tick **first_deploy** the first time, then open `/install/`. See BACKEND.md §9.
