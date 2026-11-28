-- =========================================================
-- Your Survival Kit — MySQL database schema + starter content
-- Import through phpMyAdmin (Import tab) or:
--   mysql -u root -p < database/your_survival_kit.sql
-- =========================================================
CREATE DATABASE IF NOT EXISTS your_survival_kit
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE your_survival_kit;

-- ---------------------------------------------------------
-- 1. Administrators
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS admin_users (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name          VARCHAR(120) NOT NULL,
  email         VARCHAR(190) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role          ENUM('admin','editor') NOT NULL DEFAULT 'editor',
  created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Default login: admin@your-survival-kit.org  /  ChangeMe123!
-- CHANGE THIS PASSWORD IMMEDIATELY AFTER FIRST LOGIN.
INSERT INTO admin_users (name, email, password_hash, role) VALUES
('Site Administrator', 'admin@your-survival-kit.org',
 '$2y$12$.SYjFDfjAjGV3r.bUuAjvepG1C07Zqc0W67MnGZ7ftljbrAlrMzCi', 'admin');

-- ---------------------------------------------------------
-- 2. Editable site settings (key / value)
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS site_settings (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  setting_key   VARCHAR(80) NOT NULL UNIQUE,
  setting_value TEXT,
  updated_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO site_settings (setting_key, setting_value) VALUES
('site_name',        'Your Survival Kit'),
('tagline',          'Young people. Real hope. Brighter futures.'),
('site_email',            'yoursurvivalkit25@gmail.com'),
('support_email',         'yoursurvivalkit25@gmail.com'),
('site_phone',            ''),
('site_address',          'Uganda — meetings arranged by message'),
('office_hours',          'We reply when we can — usually within a few days. We are a small volunteer team.'),
('facebook',              ''),
('instagram',             ''),
('twitter',               ''),
('tiktok',                ''),
('whatsapp_number',       '+256 765 976851'),
('whatsapp_channel',      'YOUR SURVIVAL KIT'),
('mobile_money_number',   '+256 790 866761'),
('mobile_money_name',     'Amuron Rachel'),
('donation_note',         'Mobile Money only. No money is charged on this website — we will send you the payment number and you complete the payment on your own phone.'),
('about_short',           'Your Survival Kit is a small, youth-led group in Uganda gathering what we have and giving it to children in underserved communities.'),
('footer_note',           'Gather what we have. Give what matters.'),
('smtp_host',             'smtp.gmail.com'),
('smtp_port',             '587'),
('smtp_user',             'yoursurvivalkit25@gmail.com'),
('smtp_pass',             ''),
('smtp_secure',           'tls'),
('about_team_eyebrow', 'Meet the team'),
('about_team_title', 'Young people, working together'),
('about_team_text', 'Your Survival Kit is a small, youth-led group in Uganda. These are the hands that collect, pack and deliver each kit — neighbours, students and volunteers who show up because they care.'),
('about_team_images', '["outreach-12.jpg","outreach-13.jpg","outreach-14.jpg","outreach-15.jpg"]'),
('about_story_paragraph', 'The work is done by a small core team and rotating volunteers who organise collection drives, sort donations in borrowed spaces and make deliveries in person. Roles change with what needs doing — showing up matters more than a title.'),
('home_hero_eyebrow', 'Young people. Real hope. Brighter futures.'),
('home_hero_title', 'Gather what we have.'),
('home_hero_title_accent', 'Give what matters.'),
('home_hero_text', 'We gather everyday essentials, pack them with care and hand them directly to children in communities we know.'),
('home_hero_images', '["outreach-01.jpg","outreach-02.jpg","outreach-03.jpg"]'),
('home_hero_credit', 'Young volunteers and neighbours making it happen'),
('home_how_title', 'How it works'),
('home_how_text', 'Three simple steps. No warehouses, no middlemen — just a short chain we can account for.'),
('home_how_steps', '[{"title":"Gather","text":"Families and young people give what they can, or a small gift lets us buy what is missing."},{"title":"Sort & pack","text":"Volunteers check every item, group by age and need, and add a handwritten note."},{"title":"Deliver","text":"We hand each kit to a school or family ourselves and confirm it arrived."}]'),
('what_intro_title', 'More than a bag of supplies'),
('what_intro_text', 'Every kit is built around what a child actually needs for school and care. We keep the list short, useful and honest.'),
('volunteer_intro_text', 'You do not need money to make a difference. Time, attention and a little organisation go a long way.'),
('events_intro_text', 'Collection drives, packing days and outreaches. Confirm your place on WhatsApp and we will share the exact meeting point.');

-- ---------------------------------------------------------
-- 3. Home-page impact statistics (editable in admin)
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS stats (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  label      VARCHAR(100) NOT NULL,
  value      INT NOT NULL DEFAULT 0,
  suffix     VARCHAR(20) NOT NULL DEFAULT '',
  icon       VARCHAR(60) NOT NULL DEFAULT 'bi-heart-fill',
  sort_order INT NOT NULL DEFAULT 0
) ENGINE=InnoDB;

-- Deliberately zero until the team confirms real figures. No invented impact numbers.
INSERT INTO stats (label, value, suffix, icon, sort_order) VALUES
('Children supported', 0, '', 'bi-emoji-smile', 1),
('Kits packed',        0, '', 'bi-bag-heart', 2),
('Volunteers',         0, '', 'bi-people', 3),
('Outreach days',      0, '', 'bi-geo-alt', 4);

-- ---------------------------------------------------------
-- 4. News / Stories  (author joins to admin_users)
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS news (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title         VARCHAR(220) NOT NULL,
  slug          VARCHAR(240) NOT NULL UNIQUE,
  category      VARCHAR(80) NOT NULL DEFAULT 'News',
  excerpt       TEXT,
  content       LONGTEXT,
  image         VARCHAR(255),
  author_id     INT UNSIGNED DEFAULT NULL,
  status        ENUM('draft','published') NOT NULL DEFAULT 'draft',
  views         INT NOT NULL DEFAULT 0,
  published_at  DATETIME DEFAULT NULL,
  created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_status (status),
  KEY idx_published (published_at),
  CONSTRAINT fk_news_author FOREIGN KEY (author_id) REFERENCES admin_users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

INSERT INTO news (title, slug, category, excerpt, content, image, author_id, status, views, published_at) VALUES
('How to Build a Survival Kit for Children in Need',
 'how-to-build-a-survival-kit-for-children-in-need',
 'Guides',
 'A practical, compassionate guide to putting together a survival kit for children — and why each item matters more than you think.',
 '<p>When we talk about a survival kit for children, we are not talking about something extreme or complicated. We are talking about a small collection of everyday things that tells a child: you are seen, you are safe, and someone cares about you. At Your Survival Kit, we have learned that the simplest items often carry the greatest emotional weight.</p>'
 '<h2>Start with what a child actually needs</h2>'
 '<p>Before you buy anything, think about the child who will receive the kit. Age, climate, school routine and family circumstances all matter. A kit for a child starting school looks different from one for a family displaced by flooding. Ask questions, listen, and then choose items that fit the reality of that child rather than an idea of what we think they need.</p>'
 '<h2>The core list</h2>'
 '<p>A good survival kit for children usually includes a few layers. First comes personal care: a toothbrush, toothpaste, a comb, soap and a clean towel. These small things restore dignity in a way that is hard to measure. Second comes protection: a warm sweater or blanket, socks, and a raincoat where the climate calls for one. Third comes nourishment: a reusable water bottle and a few non-perishable, age-appropriate snacks.</p>'
 '<p>Fourth, and often forgotten, comes comfort and learning. A notebook, pencils, a small storybook and a soft toy or handmade card can turn a bundle of supplies into something a child looks forward to opening. Write a short note with your name and a kind message. Children remember the note long after the soap is finished.</p>'
 '<h2>Choose a container that can be reused</h2>'
 '<p>A sturdy backpack, a bucket with a lid or a cloth bag works well. Avoid single-use plastic where possible. The container becomes part of the gift: a school bag can be used every day, and a bucket can carry water long after the kit is emptied. Label it gently with the child name or a drawing so it feels personal rather than institutional.</p>'
 '<h2>Keep it safe and age-appropriate</h2>'
 '<p>Check expiry dates on food and medicine. Avoid sharp objects, small parts for very young children, and anything that might be unsafe in a crowded home. If you are adding medicines, include only common items such as plasters and follow local guidance. When in doubt, leave it out and donate through a trusted organisation instead.</p>'
 '<h2>Why youth-led giving matters</h2>'
 '<p>Young people often notice what older systems miss. We see the child walking to school without a raincoat, the sibling sharing one exercise book, the quiet student who has never had a new pencil. Because we are close to these realities, we can respond quickly and with less waste. A youth-led charity does not need a big warehouse to make a difference; it needs attention, honesty and a habit of showing up.</p>'
 '<h2>How to get involved</h2>'
 '<p>You can build a kit at home with your family, pool resources with friends, or contribute through our donation page. Every contribution is recorded and matched to a real need. If you prefer to give your time, join a collection drive or help us sort and pack. The point is not the size of the gift but the consistency of the care behind it.</p>'
 '<p>A survival kit for children is, in the end, a promise in a backpack. It says that their comfort matters, their education matters, and their childhood matters. That promise is something every one of us can help keep.</p>',
 'outreach-16.jpg', 1, 'published', 342, '2026-10-12 09:30:00'),

('Why Young People Should Lead Community Change',
 'why-young-people-should-lead-community-change',
 'Opinion',
 'Youth-led charity is not a trend. It is a practical answer to problems that adult systems have overlooked for too long.',
 '<p>There is a quiet shift happening in communities across Uganda. Young people are no longer waiting to be invited into decision-making; they are organising, collecting, delivering and reporting back. At Your Survival Kit, we believe this is not a trend. It is a practical response to needs that established systems often miss.</p>'
 '<h2>Proximity is an advantage</h2>'
 '<p>A young volunteer walking the same road to school sees things a distant office cannot see. Who is missing a sweater? Which family ran out of notebooks? Proximity turns observation into action within days rather than months. That speed matters when a child is uncomfortable or embarrassed.</p>'
 '<h2>Learning by doing</h2>'
 '<p>Running a charity teaches budgeting, logistics, communication and empathy at the same time. Young members learn to count donations, negotiate with suppliers, write to partners and speak to families respectfully. These are skills no classroom can fully simulate. The work is the training.</p>'
 '<h2>Accountability to peers</h2>'
 '<p>Youth teams tend to check each other openly. When records are shared in a group and every gift is logged, trust grows naturally. Our donors can see exactly what was collected and where it went. Transparency is not a policy document here; it is a habit.</p>'
 '<h2>Intergenerational partnership</h2>'
 '<p>Leadership by young people does not mean excluding adults. The strongest teams pair youthful energy with the experience of mentors who help with governance, safeguarding and finance. We welcome that partnership, provided the voice of the young leader remains central.</p>'
 '<h2>The next step</h2>'
 '<p>If you are young and want to help, start small and start now. Collect ten notebooks. Visit one family. Write one honest report. Momentum builds from visible, repeated action, and children notice when someone keeps coming back.</p>',
 'outreach-19.jpg', 1, 'published', 198, '2026-10-28 14:00:00'),

('Our First Community Collection Drive: What We Learned',
 'our-first-community-collection-drive',
 'Impact',
 'A short reflection on our first collection drive and the three lessons that changed how we work.',
 '<p>Our first community collection drive was smaller than we imagined and more useful than we expected. We set up a table outside a local school, explained our mission to anyone who stopped, and waited to see what would happen.</p>'
 '<h2>Lesson one: clarity wins</h2>'
 '<p>People wanted to help, but they needed a specific list. Once we wrote down "notebooks, socks, soap, raincoats" on a large sheet, donations became focused and sorting became faster. Vague appeals feel generous; specific appeals get results.</p>'
 '<h2>Lesson two: relationships over transactions</h2>'
 '<p>The most valuable conversations were not the biggest donations. They were the conversations where a parent told us which child needed a school bag or which family had lost income that month. That context helped us place each item where it mattered most.</p>'
 '<h2>Lesson three: record everything</h2>'
 '<p>We now log every donation the same day, with the donor name and the destination. It protects the charity, reassures donors and helps us plan the next drive. Good records are a form of respect.</p>'
 '<p>We are grateful to everyone who contributed. The next drive is already being planned, and we invite you to join us.</p>',
 'outreach-10.jpg', 1, 'published', 121, '2026-11-10 10:15:00');

-- ---------------------------------------------------------
-- 5. Gallery (real outreach photos are seeded; edit captions in the admin panel)
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS gallery (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title      VARCHAR(180) NOT NULL,
  caption    TEXT,
  category   VARCHAR(80) NOT NULL DEFAULT 'General',
  image      VARCHAR(255) NOT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  consent_confirmed TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO gallery (id, title, caption, category, image, sort_order) VALUES
  (1, 'Akatucungura Shanis Pretty', 'Leader of Your Survival Kit and a young debater.', 'Team', 'leader-shanis.jpg', 0),
  (2, 'Outreach photo 01', 'A moment from a Your Survival Kit outreach in Uganda. Caption to be confirmed by the team.', 'Outreach', 'outreach-01.jpg', 1),
  (3, 'Outreach photo 02', 'A moment from a Your Survival Kit outreach in Uganda. Caption to be confirmed by the team.', 'Outreach', 'outreach-02.jpg', 2),
  (4, 'Outreach photo 03', 'A moment from a Your Survival Kit outreach in Uganda. Caption to be confirmed by the team.', 'Outreach', 'outreach-03.jpg', 3),
  (5, 'Outreach photo 04', 'A moment from a Your Survival Kit outreach in Uganda. Caption to be confirmed by the team.', 'Outreach', 'outreach-04.jpg', 4),
  (6, 'Outreach photo 05', 'A moment from a Your Survival Kit outreach in Uganda. Caption to be confirmed by the team.', 'Outreach', 'outreach-05.jpg', 5),
  (7, 'Outreach photo 06', 'A moment from a Your Survival Kit outreach in Uganda. Caption to be confirmed by the team.', 'Outreach', 'outreach-06.jpg', 6),
  (8, 'Outreach photo 07', 'A moment from a Your Survival Kit outreach in Uganda. Caption to be confirmed by the team.', 'Outreach', 'outreach-07.jpg', 7),
  (9, 'Outreach photo 08', 'A moment from a Your Survival Kit outreach in Uganda. Caption to be confirmed by the team.', 'Outreach', 'outreach-08.jpg', 8),
  (10, 'Outreach photo 09', 'A moment from a Your Survival Kit outreach in Uganda. Caption to be confirmed by the team.', 'Outreach', 'outreach-09.jpg', 9),
  (11, 'Outreach photo 10', 'A moment from a Your Survival Kit outreach in Uganda. Caption to be confirmed by the team.', 'Outreach', 'outreach-10.jpg', 10),
  (12, 'Outreach photo 11', 'A moment from a Your Survival Kit outreach in Uganda. Caption to be confirmed by the team.', 'Outreach', 'outreach-11.jpg', 11),
  (13, 'Outreach photo 12', 'A moment from a Your Survival Kit outreach in Uganda. Caption to be confirmed by the team.', 'Outreach', 'outreach-12.jpg', 12),
  (14, 'Outreach photo 13', 'A moment from a Your Survival Kit outreach in Uganda. Caption to be confirmed by the team.', 'Outreach', 'outreach-13.jpg', 13),
  (15, 'Outreach photo 14', 'A moment from a Your Survival Kit outreach in Uganda. Caption to be confirmed by the team.', 'Outreach', 'outreach-14.jpg', 14),
  (16, 'Outreach photo 15', 'A moment from a Your Survival Kit outreach in Uganda. Caption to be confirmed by the team.', 'Outreach', 'outreach-15.jpg', 15),
  (17, 'Outreach photo 16', 'A moment from a Your Survival Kit outreach in Uganda. Caption to be confirmed by the team.', 'Outreach', 'outreach-16.jpg', 16),
  (18, 'Outreach photo 17', 'A moment from a Your Survival Kit outreach in Uganda. Caption to be confirmed by the team.', 'Outreach', 'outreach-17.jpg', 17),
  (19, 'Outreach photo 18', 'A moment from a Your Survival Kit outreach in Uganda. Caption to be confirmed by the team.', 'Outreach', 'outreach-18.jpg', 18),
  (20, 'Outreach photo 19', 'A moment from a Your Survival Kit outreach in Uganda. Caption to be confirmed by the team.', 'Outreach', 'outreach-19.jpg', 19),
  (21, 'Outreach photo 20', 'A moment from a Your Survival Kit outreach in Uganda. Caption to be confirmed by the team.', 'Outreach', 'outreach-20.jpg', 20),
  (22, 'Outreach photo 21', 'A moment from a Your Survival Kit outreach in Uganda. Caption to be confirmed by the team.', 'Outreach', 'outreach-21.jpg', 21),
  (23, 'Outreach photo 22', 'A moment from a Your Survival Kit outreach in Uganda. Caption to be confirmed by the team.', 'Outreach', 'outreach-22.jpg', 22),
  (24, 'Outreach photo 23', 'A moment from a Your Survival Kit outreach in Uganda. Caption to be confirmed by the team.', 'Outreach', 'outreach-23.jpg', 23),
  (25, 'Outreach photo 24', 'A moment from a Your Survival Kit outreach in Uganda. Caption to be confirmed by the team.', 'Outreach', 'outreach-24.jpg', 24),
  (26, 'Outreach photo 25', 'A moment from a Your Survival Kit outreach in Uganda. Caption to be confirmed by the team.', 'Outreach', 'outreach-25.jpg', 25),
  (27, 'Outreach photo 26', 'A moment from a Your Survival Kit outreach in Uganda. Caption to be confirmed by the team.', 'Outreach', 'outreach-26.jpg', 26),
  (28, 'Outreach photo 27', 'A moment from a Your Survival Kit outreach in Uganda. Caption to be confirmed by the team.', 'Outreach', 'outreach-27.jpg', 27),
  (29, 'Outreach photo 28', 'A moment from a Your Survival Kit outreach in Uganda. Caption to be confirmed by the team.', 'Outreach', 'outreach-28.jpg', 28);

-- ---------------------------------------------------------
-- 6. Donation pledges (test mode — no live charges)
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS donations (
  id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  donor_name     VARCHAR(150) NOT NULL,
  email          VARCHAR(190) NOT NULL,
  phone          VARCHAR(40) DEFAULT NULL,
  amount         DECIMAL(10,2) NOT NULL,
  currency       VARCHAR(10) NOT NULL DEFAULT 'UGX',
  frequency      ENUM('once','monthly') NOT NULL DEFAULT 'once',
  method         VARCHAR(60) NOT NULL DEFAULT 'mobile_money',
  message        TEXT,
  status         ENUM('pending','confirmed','completed','cancelled') NOT NULL DEFAULT 'pending',
  created_at     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_status (status)
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- 7. Contact messages
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS messages (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name       VARCHAR(150) NOT NULL,
  email      VARCHAR(190) NOT NULL,
  subject    VARCHAR(200) NOT NULL DEFAULT 'General enquiry',
  message    TEXT NOT NULL,
  is_read    TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- 8. Volunteer applications
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS volunteers (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name        VARCHAR(150) NOT NULL,
  email       VARCHAR(190) NOT NULL,
  phone       VARCHAR(40) DEFAULT NULL,
  interest    VARCHAR(120) NOT NULL DEFAULT 'General help',
  availability VARCHAR(120) DEFAULT NULL,
  message     TEXT,
  status      ENUM('new','contacted','joined','declined') NOT NULL DEFAULT 'new',
  created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- 9. Upcoming events (managed in Admin → Events)
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS events (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title       VARCHAR(180) NOT NULL,
  description TEXT,
  event_date  DATE NOT NULL,
  event_time  VARCHAR(60) DEFAULT NULL,
  location    VARCHAR(180) DEFAULT NULL,
  image       VARCHAR(255) DEFAULT NULL,
  status      ENUM('draft','published') NOT NULL DEFAULT 'published',
  created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_date (event_date),
  KEY idx_status (status)
) ENGINE=InnoDB;

-- The event supplied by the team (poster supplied as an image).
INSERT INTO events (id, title, description, event_date, event_time, location, image, status) VALUES
(1, 'The Power of Us',
 'Your Survival Kit’s first big gathering — where fun meets generosity. Live games, surprise auction moments, play for a purpose, good vibes and good food. Tickets: Early bird UGX 35,000 / Ordinary UGX 40,000. For more information contact +256 757 476546, +256 790 866761 or +256 765 976851.',
 '2027-01-17', 'Midday – 7:00 PM', 'Palm Gardens and Guest House, Kisaasi', 'event-power-of-us.jpg', 'published');

-- ---------------------------------------------------------
-- 10. Newsletter subscribers (double opt-in)
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS subscribers (
  id                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  email             VARCHAR(190) NOT NULL UNIQUE,
  verify_token      VARCHAR(64) DEFAULT NULL,
  unsubscribe_token VARCHAR(64) DEFAULT NULL,
  verified_at       DATETIME DEFAULT NULL,
  unsubscribed_at   DATETIME DEFAULT NULL,
  created_at        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =========================================================
-- END OF SCHEMA
-- =========================================================
