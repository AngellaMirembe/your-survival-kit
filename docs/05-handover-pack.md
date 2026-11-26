# Handover Pack — Your Survival Kit

## 1. What the client receives

- Full website source folder (or Git repository access).
- Database SQL file and cPanel database credentials.
- Administrator login for the staff panel.
- This guide plus a 30-minute recorded training session.
- 7-day bug-fix warranty and optional maintenance retainer.

## 2. Administrator quick guide

### Sign in
Go to `/admin/login.php`. Use the email and password supplied at handover. Change the password immediately in **Settings → Change password**.

### Publish a story
1. **Admin → News & Stories → New story**.
2. Enter title, optional summary, category and content.
3. Set status to **Published** and choose a publish date.
4. Upload a cover image (JPG/PNG/WebP/GIF, max 5 MB).
5. Click **Save story**. It appears on the public News page immediately.

### Add a gallery photo
**Admin → Gallery → Add photo** → title, category, optional caption, image → Save. Use *sort order* to control the position.

### Handle donations
**Admin → Donations**: filter by status, update a pledge to *Confirmed* or *Completed* after the gift is received, and export details manually if needed. Remember: the site runs in test mode — no online charge is made.

### Handle volunteers and messages
**Admin → Volunteers** and **Admin → Messages**: read applications, change their status and reply by email from the message detail screen.

### Change site text and numbers
**Admin → Settings**: edit contact details, social links, donation note, and the impact counters shown on the home page.

## 3. Credentials sheet (fill in and give securely)

| Item | Value |
|---|---|
| Live URL | https://[domain] |
| Admin login | https://[domain]/admin/login.php |
| Admin email | [email] |
| Temporary admin password (examiner) | [password — delete after marking] |
| cPanel URL | https://[domain]:2083 |
| cPanel username | [username] |
| Database name | your_survival_kit |
| Database user | [user] |
| Webmail | https://[domain]/webmail |

> Never send passwords in the same message as usernames. Share by phone or in person.

## 4. Training session record

- Date: [date] · Attendees: [names] · Duration: 30 minutes
- Topics covered: login, stories, gallery, donations, volunteers, messages, settings, backups.
- Client sign-off: ____________________

## 5. Maintenance / support options

| Package | Includes | Price |
|---|---|---|
| Basic (pay-as-you-go) | Bug fixes quoted per incident | [amount]/hr |
| Standard retainer | Updates, backups, monthly security check, 4 content edits | [amount]/mo |
| Hybrid | Retainer + additional hours at discounted rate | [amount]/mo + [amount]/hr |

## 6. Backup and restore

Weekly: cPanel → **Backup Wizard → Full Backup**. Before any edit: database export from phpMyAdmin. Restore by importing the `.sql` file and uploading the archived files. A tested restore is documented in `07-security-report.md`.
