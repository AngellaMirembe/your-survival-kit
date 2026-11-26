# Your Survival Kit — Website Project

A fully functional, multi-page charity website for **Your Survival Kit**, a youth-led organisation that gathers everyday essentials and gives them to children in unprivileged situations.

## Stack

| Layer | Technology |
|---|---|
| Frontend | HTML5, CSS3, Bootstrap 5.3, Bootstrap Icons, vanilla JavaScript |
| Backend | PHP 8.1+ (plain PHP, no framework) |
| Database | MySQL / MariaDB via PDO prepared statements |
| Server | Apache with `mod_rewrite` (XAMPP locally, cPanel in production) |

No CMS is used. This is a custom PHP application, chosen because the client needs a small, fast, secure site with a few database-driven features and non-technical staff who can edit content through a simple admin panel.

## Quick start (XAMPP)

1. Copy the whole `your-survival-kit` folder into `C:\xampp\htdocs\` (or your XAMPP `htdocs` folder).
2. Start **Apache** and **MySQL** in XAMPP.
3. Open **phpMyAdmin** → **Import** → choose `database/your_survival_kit.sql` → **Go**.
4. Open `includes/config.php` and set your database credentials (default: `root` / empty password).
5. Visit `http://localhost/your-survival-kit/` in your browser.
6. Staff login: `http://localhost/your-survival-kit/admin/login.php`
   - Email: `admin@your-survival-kit.org`
   - Password: `ChangeMe123!` — **change it immediately** in Admin → Settings.

> Base paths and URLs are detected automatically, so the same files work in a subfolder locally and at a domain root on the live server.

## Going live (cPanel)

1. Create the MySQL database + user in cPanel and add the user to the database with **All Privileges**.
2. Import `database/your_survival_kit.sql` through cPanel phpMyAdmin.
3. Upload the **contents** of this folder to `public_html` (not the folder itself).
4. Edit `includes/config.php` with the live DB credentials.
5. Point your domain's DNS / nameservers to the hosting account.
6. Install the free SSL certificate in cPanel and force HTTPS (already configured in `.htaccess` for non-local hosts).
7. Create professional email accounts (`info@`, `support@`) in cPanel → Email Accounts.
8. Update `robots.txt` with your real sitemap URL.

See `docs/deployment-guide.md` for the full checklist.

## What is included

### Public pages
- **Home** (`index.php`) — hero, editable impact counters, mission, programmes preview, latest stories, CTA
- **About** (`about.php`) — story, values, youth-led rationale, team placeholders
- **What We Do** (`what-we-do.php`) — four programmes and selection policy
- **Gallery** (`gallery.php`) — database-driven photo grid, category filters, lightbox, placeholders
- **News** (`news.php`) — featured story, paginated article list
- **Article** (`news-post.php?slug=…`) — full story, author, share links, related posts, view counter
- **Donate** (`donate.php`) — pledge form recorded in MySQL, impact tiers, item donations
- **Get Involved** (`volunteer.php`) — volunteer application form
- **Contact** (`contact.php`) — message form, contact details, FAQ, map placeholder
- **404** (`404.php`), **Newsletter endpoint** (`newsletter.php`), **Sitemap** (`sitemap.php`)

### Admin panel (`/admin/`)
Login with rate limiting, then manage:
- Dashboard with counts, recent pledges, messages and a **SQL JOIN** of news + authors
- News & Stories — full **CRUD** with image upload
- Gallery — full **CRUD** with validated uploads
- Donations — view, filter, change status, delete
- Volunteers — view, filter, change status, delete
- Messages — read, mark read/unread, delete
- Subscribers — add and remove
- Settings — edit site text, impact stats (CRUD) and change password

### Security implemented
- PDO prepared statements for every dynamic query
- `htmlspecialchars()` on all output via `e()`
- CSRF tokens on every form
- `password_hash()` / `password_verify()` for staff passwords
- Session regeneration on login, 15-minute lockout after 5 failed attempts
- Direct access blocked to `includes/`, `database/` and PHP files in `uploads/`
- Upload MIME/size validation and safe random filenames
- HTTPS forced on live hosts, security headers, directory indexing disabled

### SEO implemented
- Friendly URLs (`/about`, `/news/my-story`, `/sitemap.xml`) via `.htaccess`
- Per-page titles, meta descriptions, canonical and Open Graph tags
- Dynamic XML sitemap + `robots.txt`, semantic headings and image alt text

## Folder map

```
your-survival-kit/
├── index.php, about.php, what-we-do.php, gallery.php, news.php,
│   news-post.php, donate.php, volunteer.php, contact.php,
│   newsletter.php, 404.php, sitemap.php, robots.txt, .htaccess
├── admin/            Admin panel (login, dashboard, CRUD pages)
├── includes/         config, helpers, auth, header, footer (not web-accessible)
├── assets/           css/style.css, js/main.js, img/ (logo + placeholders)
├── uploads/          Runtime uploads (writable by the server)
├── database/         your_survival_kit.sql — schema + starter content
└── docs/             Client engagement & professional documentation pack
```

## Replacing placeholders

All placeholder images live in `assets/img/`. Replace them with real, consented photos. Gallery photos and story cover images can be uploaded in the admin panel. Team names, contact addresses, social links and map embed are placeholders too — update them in **Admin → Settings** and in the contact page.

## Default credentials (change before going live)

| Item | Value |
|---|---|
| Admin email | `admin@your-survival-kit.org` |
| Admin password | `ChangeMe123!` |
| Database | `your_survival_kit` |
| DB user | `root` (local default) |
