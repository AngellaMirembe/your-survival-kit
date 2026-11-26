# Viva Preparation Notes

## Explain your decisions

- **Why custom PHP instead of WordPress?** The client needs five focused database features and very little content. No plugin updates, no theme bloat, faster pages, simpler training for non-technical staff.
- **Why Bootstrap 5?** Rapid responsive layout, accessible components, well-documented, familiar to markers.
- **Why PDO?** Named/positional prepared statements, `ATTR_EMULATE_PREPARES = false`, consistent error handling.
- **How is the site secured?** Walk through HTTPS, CSRF, `e()` output escaping, password hashing, rate limiting, upload checks and `.htaccess` access rules.
- **Where is a JOIN used?** Dashboard "Latest stories" joins `news.author_id` to `admin_users.id`; news listing pages do the same.
- **How is CRUD demonstrated?** Story and gallery screens implement all four operations; donations, volunteers, messages and subscribers implement update/delete flows.
- **How does a donation work?** Form → validation → PDO insert with `pending` status → admin sees pledge → status updated when gift is received. No live charge.

## Live demonstration checklist

1. Open the live site on the domain and confirm the padlock / HTTPS.
2. Submit a test donation and show it in Admin → Donations.
3. Create a story with an image, publish it, and show it on the public News page.
4. Add a gallery photo and show the lightbox.
5. Send a contact message and mark it read in admin.
6. Show the sitemap, robots.txt and a meta description.
7. Show one GitHub commit list and the merged feature branch PR.

## Likely fault-setting scenarios to troubleshoot

- Wrong DB credentials → `config.php` error page; check DB user/host.
- Blank page after upload → re-import SQL or fix file permissions (`644`/`755`).
- `.php` source downloaded instead of executing → PHP handler missing in cPanel.
- "Too many failed attempts" → wait 15 minutes or clear `login_lock_until` in the session.
- Broken images after moving folders → `BASE_PATH`/`BASE_URL` auto-detection; check `asset()` URLs.
- Mixed-content warning → ensure `BASE_URL` uses `https` behind a proxy (`HTTP_X_FORWARDED_PROTO`).

## Documents to have printed

Client brief, proposal, quotation + invoice, NDA, consent letter, handover pack, security report, evidence portfolio with dated screenshots, and this viva sheet.
