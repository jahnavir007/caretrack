-- CareTrack schema (SQLite flavour used for local development and tests). Mirrors schema.mysql.sql.
CREATE TABLE IF NOT EXISTS users (
  id            INTEGER PRIMARY KEY AUTOINCREMENT,
  name          TEXT NOT NULL,
  email         TEXT NOT NULL UNIQUE,
  password_hash TEXT NOT NULL,
  role          TEXT NOT NULL DEFAULT 'parent' CHECK (role IN ('admin','staff','parent')),
  phone         TEXT NULL,
  created_at    TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS children (
  id            INTEGER PRIMARY KEY AUTOINCREMENT,
  first_name    TEXT NOT NULL,
  last_name     TEXT NOT NULL,
  date_of_birth TEXT NOT NULL,
  allergies     TEXT NULL,
  notes         TEXT NULL,
  staff_id      INTEGER NULL REFERENCES users(id) ON DELETE SET NULL,
  created_at    TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX IF NOT EXISTS idx_children_staff ON children(staff_id);
CREATE INDEX IF NOT EXISTS idx_children_name  ON children(last_name, first_name);

CREATE TABLE IF NOT EXISTS child_parent (
  child_id     INTEGER NOT NULL REFERENCES children(id) ON DELETE CASCADE,
  parent_id    INTEGER NOT NULL REFERENCES users(id)    ON DELETE CASCADE,
  relationship TEXT NOT NULL DEFAULT 'parent',
  PRIMARY KEY (child_id, parent_id)
);

CREATE TABLE IF NOT EXISTS activities (
  id            INTEGER PRIMARY KEY AUTOINCREMENT,
  child_id      INTEGER NOT NULL REFERENCES children(id) ON DELETE CASCADE,
  staff_id      INTEGER NOT NULL REFERENCES users(id),
  activity_type TEXT NOT NULL CHECK (activity_type IN ('meal','nap','learning','play','incident','medication','other')),
  description   TEXT NOT NULL,
  occurred_at   TEXT NOT NULL,
  created_at    TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX IF NOT EXISTS idx_activities_child_time ON activities(child_id, occurred_at);
