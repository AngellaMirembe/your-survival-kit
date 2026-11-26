# Client Brief — Your Survival Kit

**Prepared by:** [Your Full Name] · **Reg No:** [Your Registration Number]
**Client / Business:** Your Survival Kit (youth-led charity)
**Date:** [Date of interview] · **Interviewer / Client representative:** [Name, role]

## 1. Client profile

Your Survival Kit is an upcoming, youth-led charity organisation. Its members gather everyday resources they already have and give them to children in unprivileged situations. The organisation works through collection drives, packing days and direct delivery to families and partner schools.

## 2. Goals

- Build trust and visibility for a new organisation with a professional web presence.
- Make it easy for the public to donate money or items and to volunteer.
- Let non-technical staff update stories, photos and contact details without editing code.
- Demonstrate transparency by recording every pledge and delivery.

## 3. Audience

| Audience | Need |
|---|---|
| Parents, students, young people | Understand the mission and find a way to help |
| Donors | Give confidently and see where their gift went |
| Partner schools / churches / businesses | Arrange collection drives and sponsorship |
| Beneficiary families | Find contact details and support information discreetly |

## 4. Scope

**Included:** multi-page responsive website, branded design using the supplied logo, database-driven news, gallery, donations, volunteer applications, contact messages, newsletter and a secured admin panel.

**Excluded:** live payment gateway, SMS notifications, mobile app, ongoing content writing after handover, domain renewal after the first year.

## 5. Requirements

### Functional
- Home, About, What We Do, Gallery, News, Donate, Get Involved, Contact pages.
- Admin login with role-based dashboard and CRUD for stories, gallery, donations, volunteers, messages, settings.
- Donation pledges and volunteer applications saved to MySQL with validation.
- Image upload with type and size checks.
- Newsletter signup and contact form.

### Non-functional
- Mobile-responsive on phone, tablet and desktop.
- Fast loading (compressed images, cached static assets).
- Secure: HTTPS, prepared statements, CSRF protection, hashed passwords, no direct access to includes/database.
- Accessible: semantic HTML, alt text, visible labels, keyboard-navigable gallery.

### Technical
- PHP 8.1+, MySQL/MariaDB, Apache with mod_rewrite.
- cPanel hosting, PHP 8.1 or newer, SSL certificate.
- No dependency on a CMS; all custom code in one Git repository.

### Business
- Client staff must be able to publish a story and upload a photo after a short training.
- Donations run in **test mode** — no live charges until the client authorises a payment provider.
- Client approves all published content and photos; no personal data of children published without written guardian consent.

## 6. Success criteria

- Site live on the registered domain and reachable on assessment day.
- All forms write to the database and are visible in the admin panel.
- Admin can create, edit and delete content without developer help.
- Validated HTML, no mixed-content warnings, and a submitted sitemap.
