# Domain, Hosting & cPanel Deployment Guide

## 1. Domain and DNS

1. Register a unique domain, e.g. `your-survival-kit.org`.
2. Point the domain to the hosting account using the host's nameservers, or set an `A` record to the server IP and a `CNAME` for `www`.
3. Screenshot the DNS / nameserver page and date it — this is evidence for Task 2.

## 2. cPanel setup

1. **Databases → MySQL Databases**: create database `your_survival_kit`, create a user with a strong password, and add the user with **All Privileges**.
2. **phpMyAdmin → Import**: upload `database/your_survival_kit.sql`.
3. **File Manager** (or FTP): upload the **contents** of the project folder to `public_html`.
4. Edit `includes/config.php`: set `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`.
5. Create a **staging subdomain** (e.g. `staging.yourdomain.org`) pointing to a copy of the site and test there first.
6. Set file permissions: files `644`, folders `755`; `uploads/` must be writable (`755` or `775` depending on the server).

## 3. Email accounts

cPanel → **Email Accounts** → create:
- `info@yourdomain.org`
- `support@yourdomain.org`

Send and receive a test message through webmail at `/webmail`. Take dated screenshots.

## 4. SSL / HTTPS

cPanel → **Security → SSL/TLS Status → Run AutoSSL**. Once issued, the root `.htaccess` forces HTTPS on all non-localhost requests. Verify there are no mixed-content warnings: all internal asset URLs are generated from `BASE_URL`, which follows the current protocol.

## 5. Staging → live checklist

- [ ] Database imported and credentials updated
- [ ] Admin login works and default password changed
- [ ] All forms insert records and appear in the admin panel
- [ ] SSL active, HTTPS redirect working
- [ ] `robots.txt` sitemap URL updated to real domain
- [ ] Sitemap loads in browser (valid XML)
- [ ] Contact details and social links updated in Settings
- [ ] Placeholder photos replaced with consented images
- [ ] Full backup downloaded and stored safely
- [ ] Temporary examiner account created (delete after marking)

## 6. Evidence to capture (dated screenshots)

Domain registration / DNS, cPanel home, MySQL database + user, staged subdomain, webmail inbox/sent, SSL certificate, File Manager, backup + restore, Search Console verification, speed test before/after.
