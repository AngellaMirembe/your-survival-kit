-- =========================================================
-- Your Survival Kit — update an existing local database
-- Run:  sudo mysql your_survival_kit < database/update-uganda-content.sql
-- Safe to run more than once. It overwrites the seeded demo details.
-- =========================================================

-- Contact details: real details supplied by the team.
-- Only replaces the old placeholder values, so later edits made in Admin → Settings survive.
UPDATE site_settings SET setting_value = ''
  WHERE setting_key IN ('site_phone','facebook','instagram','twitter','tiktok')
    AND setting_value IN ('+255 700 000 000', 'https://facebook.com/', 'https://instagram.com/', 'https://twitter.com/', 'https://tiktok.com/');
UPDATE site_settings SET setting_value = 'Yoursurvivalkit2025@gmail.com'
  WHERE setting_key IN ('site_email','support_email')
    AND setting_value IN ('info@your-survival-kit.org','support@your-survival-kit.org','');
UPDATE site_settings SET setting_value = 'Uganda — meetings arranged by message'
  WHERE setting_key = 'site_address' AND setting_value IN ('Dar es Salaam, Tanzania','Uganda','');
UPDATE site_settings SET setting_value = 'We reply when we can — usually within a few days. We are a small volunteer team.'
  WHERE setting_key = 'office_hours' AND setting_value IN ('Mon - Fri, 9:00am - 5:00pm','');
UPDATE site_settings SET setting_value = 'Mobile Money only. No money is charged on this website — we will send you the payment number and you complete the payment on your own phone.'
  WHERE setting_key = 'donation_note' AND setting_value LIKE 'Test mode:%';
UPDATE site_settings SET setting_value = 'Your Survival Kit is a small, youth-led group in Uganda gathering what we have and giving it to children in underserved communities.'
  WHERE setting_key = 'about_short' AND setting_value = 'Your Survival Kit is a youth-led charity gathering what we have and giving it to children in unprivileged situations.';

INSERT INTO site_settings (setting_key, setting_value) VALUES
  ('whatsapp_number',     '+256 765 976851'),
  ('whatsapp_channel',    'YOUR SURVIVAL KIT'),
  ('mobile_money_number', '+256 790 866761'),
  ('mobile_money_name',   'Amuron Rachel'),
  ('smtp_host',           'smtp.gmail.com'),
  ('smtp_port',           '587'),
  ('smtp_user',           'Yoursurvivalkit2025@gmail.com'),
  ('smtp_pass',           ''),
  ('smtp_secure',         'tls')
ON DUPLICATE KEY UPDATE setting_value =
  IF(setting_key LIKE 'smtp_%' AND setting_value <> '', setting_value, VALUES(setting_value));

-- The block above fills empty SMTP fields with Gmail defaults and preserves
-- anything already saved by the administrator (including the app password).

-- New tables / columns for events and confirmed newsletter subscribers.
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

-- The event supplied by the team. INSERT IGNORE keeps later admin edits.
INSERT IGNORE INTO events (id, title, description, event_date, event_time, location, image, status) VALUES
(1, 'The Power of Us',
 'Your Survival Kit’s first big gathering — where fun meets generosity. Live games, surprise auction moments, play for a purpose, good vibes and good food. Tickets: Early bird UGX 35,000 / Ordinary UGX 40,000. For more information contact +256 757 476546, +256 790 866761 or +256 765 976851.',
 '2027-01-17', 'Midday – 7:00 PM', 'Palm Gardens and Guest House, Kisaasi', 'event-power-of-us.jpg', 'published');

ALTER TABLE subscribers
  ADD COLUMN IF NOT EXISTS verify_token VARCHAR(64) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS unsubscribe_token VARCHAR(64) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS verified_at DATETIME DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS unsubscribed_at DATETIME DEFAULT NULL;

-- No invented impact figures (only resets rows still showing the old demo numbers).
UPDATE stats SET label='Children supported', value=0, suffix='' WHERE id=1 AND value IN (1250, 0);
UPDATE stats SET label='Kits packed', value=0, suffix='' WHERE id=2 AND value IN (3400, 0);
UPDATE stats SET label='Volunteers', value=0, suffix='' WHERE id=3 AND value IN (180, 0);
UPDATE stats SET label='Outreach days', value=0, suffix='' WHERE id=4 AND value IN (24, 0);

-- Real outreach photos on the three seeded stories (do not overwrite later admin edits).
UPDATE news SET image='outreach-16.jpg' WHERE id=1 AND image IN ('kit.jpg','');
UPDATE news SET image='outreach-19.jpg' WHERE id=2 AND image IN ('volunteer.jpg','');
UPDATE news SET image='outreach-10.jpg' WHERE id=3 AND image IN ('children.jpg','');
UPDATE news SET content = REPLACE(content, 'across the country', 'across Uganda') WHERE id=2;
UPDATE news SET content = REPLACE(content, 'a winter jacket', 'a school bag') WHERE id=3;

-- Gallery: leader portrait plus 28 outreach photos. INSERT IGNORE keeps captions edited in admin.
INSERT IGNORE INTO gallery (id, title, caption, category, image, sort_order) VALUES
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
  (29, 'Outreach photo 28', 'A moment from a Your Survival Kit outreach in Uganda. Caption to be confirmed by the team.', 'Outreach', 'outreach-28.jpg', 28)
;
