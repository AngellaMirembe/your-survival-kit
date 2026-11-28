-- Repeatable migration for the admin content editor and gallery controls.
-- Safe to run more than once. Existing admin edits are never overwritten.

-- New editable page sections. INSERT IGNORE keeps values already changed in Admin → Page content.
INSERT IGNORE INTO site_settings (setting_key, setting_value) VALUES
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

-- Gallery consent flag. Seeded photos remain visible; new uploads must be confirmed.
ALTER TABLE gallery ADD COLUMN IF NOT EXISTS consent_confirmed TINYINT(1) NOT NULL DEFAULT 1 AFTER sort_order;
