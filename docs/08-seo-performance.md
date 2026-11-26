# SEO, Performance & Content Checklist

## On-page SEO

- [x] Unique `<title>` and meta description for every page (`$pageTitle`, `$pageDescription`)
- [x] Canonical URL and Open Graph tags in `includes/header.php`
- [x] One `<h1>` per page, logical heading hierarchy
- [x] Descriptive `alt` text on all images (including placeholders)
- [x] Friendly URLs via `.htaccess`: `/about`, `/what-we-do`, `/news/my-story`
- [x] Dynamic XML sitemap at `/sitemap.xml` (`sitemap.php` + rewrite rule)
- [x] `robots.txt` with real sitemap URL (update before deployment)
- [ ] Verify site in Google Search Console and submit sitemap — **live step**

## Keyword-targeted article

**Target keyword:** "survival kit for children"
**Published article:** *How to Build a Survival Kit for Children in Need*
**URL:** `/news/how-to-build-a-survival-kit-for-children-in-need`
**Length:** 600+ words (seeded in `database/your_survival_kit.sql`)

The article uses the keyword in the title, slug, meta description, first paragraph and two subheadings, and answers practical questions donors actually ask. Add internal links from the Donate page and home page to strengthen it.

## Performance

- [x] Bootstrap and icons loaded from CDN; custom CSS/JS cached via `.htaccess` `ExpiresByType`
- [x] Images are `loading="lazy"` in grids and lists
- [x] Uploaded images capped at 5 MB; compress before uploading (e.g. TinyPNG / Squoosh)
- [x] PHP output is plain HTML with no heavy framework
- [ ] Run PageSpeed Insights / GTmetrix before and after optimisation and save both reports — **live step**

## Version control

See `09-git-workflow.md`. Minimum: 8 meaningful commits and one feature branch merged by pull request.
