#!/usr/bin/env php
<?php
/**
 * Creates the schema and seeds demo data.
 *   php bin/setup.php            # SQLite (default) -> storage/caretrack.sqlite
 *   DB_DRIVER=mysql DB_NAME=caretrack DB_USER=root DB_PASSWORD=secret php bin/setup.php
 */
declare(strict_types=1);

$config = require __DIR__ . '/../config/config.php';
require __DIR__ . '/../includes/Database.php';
require __DIR__ . '/../includes/Repository.php';

$pdo = Database::connect($config['db']);
Database::migrate($pdo, $config['db']['driver']);
$repo = new Repository($pdo);

if ($repo->findUserByEmail('admin@caretrack.dev')) {
    echo "Database already seeded.\n";
    exit(0);
}

$admin  = $repo->createUser('Asha Admin', 'admin@caretrack.dev', 'Password123!', 'admin', '+91 98450 00001');
$staff  = $repo->createUser('Sunil Staff', 'staff@caretrack.dev', 'Password123!', 'staff', '+91 98450 00002');
$parent = $repo->createUser('Priya Parent', 'parent@caretrack.dev', 'Password123!', 'parent', '+91 98450 00003');
$parent2 = $repo->createUser('Rahul Parent', 'rahul@caretrack.dev', 'Password123!', 'parent');

$aarav = $repo->createChild(['first_name' => 'Aarav', 'last_name' => 'Sharma', 'date_of_birth' => '2021-06-14', 'allergies' => 'Peanuts', 'notes' => 'Loves drawing.', 'staff_id' => $staff], [$parent, $parent2]);
$meera = $repo->createChild(['first_name' => 'Meera', 'last_name' => 'Iyer', 'date_of_birth' => '2022-11-02', 'allergies' => '', 'notes' => '', 'staff_id' => $staff], [$parent]);
$kabir = $repo->createChild(['first_name' => 'Kabir', 'last_name' => 'Nair', 'date_of_birth' => '2020-03-09', 'allergies' => 'Dairy', 'notes' => '', 'staff_id' => $admin], []);

$repo->logActivity($aarav, $staff, 'meal', 'Ate most of lunch (rice and dal).', date('Y-m-d 12:30', strtotime('-1 day')));
$repo->logActivity($aarav, $staff, 'nap', 'Slept 1 h 20 min.', date('Y-m-d 14:00', strtotime('-1 day')));
$repo->logActivity($meera, $staff, 'learning', 'Practised colours and shapes.', date('Y-m-d 10:15', strtotime('-2 days')));
$repo->logActivity($kabir, $admin, 'incident', 'Minor scrape on knee during play; cleaned and bandaged, parents informed.', date('Y-m-d 16:05', strtotime('-3 days')));

echo "Schema created and demo data seeded.\nLogins (password Password123!): admin@caretrack.dev, staff@caretrack.dev, parent@caretrack.dev\n";
