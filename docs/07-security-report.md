# Security Report — Your Survival Kit

**Date:** [date] · **URL:** [live URL] · **Tester:** [Your Name]

## Measures implemented

| Area | Implementation | Evidence |
|---|---|---|
| Transport | Free SSL certificate; `.htaccess` forces HTTPS for all non-localhost hosts; `BASE_URL` uses current protocol so no mixed content | [screenshot] |
| SQL injection | Every dynamic query uses PDO prepared statements with `ATTR_EMULATE_PREPARES = false` | `includes/*.php`, `admin/*.php` |
| XSS | All dynamic output passes through `e()` (`htmlspecialchars` with `ENT_QUOTES`) | `includes/functions.php` |
| CSRF | Random per-session token on every form, verified with `hash_equals()` | `csrf_field()` / `csrf_verify()` |
| Authentication | `password_hash()` (bcrypt/Argon2), `password_verify()`, session regeneration on login | `admin/login.php` |
| Brute force | 5 failed attempts → 15-minute lockout | `includes/auth.php` |
| Authorisation | `require_login()` at the top of every admin page; admin pages marked `noindex` | `admin/includes/header.php` |
| File access | `Require all denied` in `includes/` and `database/`; `.htaccess` blocks `.sql`, `.md`, `.log`, `.ini`, `.bak` | directory `.htaccess` files |
| Uploads | MIME check via `finfo`, `getimagesize()`, 5 MB limit, random filename, PHP execution denied in `uploads/` | `upload_image()` |
| Headers | `X-Content-Type-Options`, `X-Frame-Options`, `Referrer-Policy`, `Permissions-Policy` | root `.htaccess` |
| Hardening | Directory indexing off, unused plugins/modules none (custom stack), default admin renamed, updates applied | [screenshot] |

## Backup and restore test

- **Backup taken:** [date/time] via cPanel Full Backup + phpMyAdmin SQL export.
- **Restore test:** deleted one test story, re-imported the SQL dump, confirmed the story returned and the site loaded without errors on [date].
- **Schedule:** weekly full backup, daily database export (cPanel cron or manual).

## Residual risks / notes

- Donations are test-mode pledges only; a PCI-compliant provider must be integrated before taking live payments.
- The temporary examiner account must be deleted after marking.
- Client must approve photos before publication and keep guardian consent on file.

## Sign-off

Developer: ____________________ Date: ____________
