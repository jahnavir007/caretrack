-- CareTrack schema (MySQL 8). Normalised to 3NF with foreign keys and indexes.
CREATE TABLE IF NOT EXISTS users (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  name          VARCHAR(100) NOT NULL,
  email         VARCHAR(190) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role          ENUM('admin','staff','parent') NOT NULL DEFAULT 'parent',
  phone         VARCHAR(20) NULL,
  created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS children (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  first_name    VARCHAR(60) NOT NULL,
  last_name     VARCHAR(60) NOT NULL,
  date_of_birth DATE NOT NULL,
  allergies     VARCHAR(255) NULL,
  notes         TEXT NULL,
  staff_id      INT NULL,
  created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_children_staff FOREIGN KEY (staff_id) REFERENCES users(id) ON DELETE SET NULL,
  INDEX idx_children_staff (staff_id),
  INDEX idx_children_name (last_name, first_name)
) ENGINE=InnoDB;

-- Many-to-many: a child can have several guardians, a parent can have several children.
CREATE TABLE IF NOT EXISTS child_parent (
  child_id     INT NOT NULL,
  parent_id    INT NOT NULL,
  relationship VARCHAR(30) NOT NULL DEFAULT 'parent',
  PRIMARY KEY (child_id, parent_id),
  CONSTRAINT fk_cp_child  FOREIGN KEY (child_id)  REFERENCES children(id) ON DELETE CASCADE,
  CONSTRAINT fk_cp_parent FOREIGN KEY (parent_id) REFERENCES users(id)    ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS activities (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  child_id      INT NOT NULL,
  staff_id      INT NOT NULL,
  activity_type ENUM('meal','nap','learning','play','incident','medication','other') NOT NULL,
  description   VARCHAR(500) NOT NULL,
  occurred_at   DATETIME NOT NULL,
  created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_act_child FOREIGN KEY (child_id) REFERENCES children(id) ON DELETE CASCADE,
  CONSTRAINT fk_act_staff FOREIGN KEY (staff_id) REFERENCES users(id),
  INDEX idx_activities_child_time (child_id, occurred_at)
) ENGINE=InnoDB;
