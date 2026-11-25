# CareTrack — Child Care Management System

A web application for a child-care centre: staff manage **children, guardians (parents), staff assignments and daily activity records**; parents get a read-only view of their own children. Built with **PHP 8 + PDO (MySQL)**, **Bootstrap 5** and vanilla **JavaScript**, with a normalised relational schema, transactions for multi-table writes, server- and client-side validation, CSRF protection and role-based access.

## Features
- **Roles:** `admin` (everything + user management), `staff` (manage children, log activities), `parent` (read-only, only their linked children).
- **Children:** create / edit / delete with allergies, notes and a key staff member; many-to-many **guardian links** maintained atomically with the child record in one transaction.
- **Activities:** meals, naps, learning, play, incidents, medication — timestamped, attributed to the staff member who logged them, shown newest-first per child and in a dashboard feed.
- **Data integrity:** foreign keys with `ON DELETE CASCADE` (child → guardian links & activities) / `SET NULL` (staff), unique emails, enum-constrained roles and activity types, indexes on the lookups the UI performs.
- **Security basics:** prepared statements only, `password_hash`/`password_verify`, session regeneration on login, CSRF tokens on every POST, output escaping with `e()`.
- **Validation:** `Validator` class on the server (source of truth) mirrored by Bootstrap's client-side validation and a DOB sanity check in `public/js/app.js`.

## Schema
```
users(id, name, email UNIQUE, password_hash, role ENUM, phone)
children(id, first_name, last_name, date_of_birth, allergies, notes, staff_id → users)
child_parent(child_id → children, parent_id → users, relationship)   -- many-to-many
activities(id, child_id → children, staff_id → users, activity_type ENUM, description, occurred_at)
```
`sql/schema.mysql.sql` is the production schema; `sql/schema.sqlite.sql` mirrors it so the app and tests run anywhere without a MySQL server.

## Run locally
```bash
# SQLite (zero setup)
php bin/setup.php                       # creates storage/caretrack.sqlite + demo data
php -S localhost:8080 -t public         # open http://localhost:8080

# MySQL
mysql -u root -p -e "CREATE DATABASE caretrack CHARACTER SET utf8mb4"
DB_DRIVER=mysql DB_NAME=caretrack DB_USER=root DB_PASSWORD=secret php bin/setup.php
DB_DRIVER=mysql DB_NAME=caretrack DB_USER=root DB_PASSWORD=secret php -S localhost:8080 -t public
```
Demo logins (password `Password123!`): `admin@caretrack.dev` · `staff@caretrack.dev` · `parent@caretrack.dev`.

## Tests
```bash
php tests/run_tests.php      # 28 checks: validators, login, transactions/rollback, role visibility, cascades
```
No PHPUnit dependency — the runner uses an in-memory SQLite database.

## Project layout
```
public/index.php      front controller (routing, auth checks, form handling)
includes/             Database (PDO factory + migrate), Repository (all SQL, transactions), Validator, Auth, helpers
pages/                PHP views (layout, login, dashboard, children/, activities/, users/)
public/css, public/js Bootstrap theme overrides, client-side validation & confirmations
sql/                  MySQL and SQLite schemas
bin/setup.php         migrate + seed
tests/run_tests.php   test runner
```

## Design notes (interview talking points)
- **Why a many-to-many table for guardians?** A child can have two parents and a parent can have several children; `child_parent` keeps that in 3NF instead of comma-separated ids.
- **Why transactions in `createChild`/`updateChild`?** The child row and its guardian links must change together; a failed link insert (e.g. a bad parent id) rolls back the child insert — covered by a test.
- **Why both client and server validation?** Client-side gives instant feedback; server-side is the only one that can be trusted.
- **Parent isolation** is enforced in SQL (`JOIN child_parent … WHERE parent_id = ?`) and re-checked in the controller (`parentCanSeeChild`) before showing a record.
