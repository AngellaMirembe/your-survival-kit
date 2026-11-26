# Suggested Git Workflow

```bash
git init
git add .
git commit -m "chore: initial project structure and config"

git add includes/config.php includes/functions.php
git commit -m "feat: PDO connection, helpers and CSRF protection"

git add database/your_survival_kit.sql
git commit -m "feat: database schema and starter content"

git add assets/css/style.css assets/js/main.js
git commit -m "style: branded Bootstrap 5 theme and interactions"

git add index.php about.php what-we-do.php
git commit -m "feat: public homepage and information pages"

git add gallery.php news.php news-post.php
git commit -m "feat: database-driven gallery and news pages"

git add donate.php volunteer.php contact.php newsletter.php
git commit -m "feat: donation, volunteer and contact forms"

git checkout -b feature/admin-panel
git add admin/
git commit -m "feat: secured admin dashboard with CRUD screens"
git checkout main
git merge feature/admin-panel   # or open a Pull Request on GitHub and merge there

git add sitemap.php robots.txt .htaccess
git commit -m "feat: SEO urls, sitemap and security headers"

git add README.md docs/
git commit -m "docs: client brief, proposal, handover and security report"
```

On GitHub, open a **Pull Request** from `feature/admin-panel` into `main`, add a short description of the CRUD screens, and merge it. Screenshot the PR and commit history as evidence.

> Add a `.gitignore` with `uploads/*`, `!uploads/.htaccess`, `docs/credentials.md`, and any local secrets before pushing.
