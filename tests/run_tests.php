#!/usr/bin/env php
<?php
/**
 * Dependency-free test runner (no PHPUnit needed):   php tests/run_tests.php
 * Uses an in-memory SQLite database so it runs anywhere PHP + pdo_sqlite is available.
 */
declare(strict_types=1);

require __DIR__ . '/../includes/Database.php';
require __DIR__ . '/../includes/Repository.php';
require __DIR__ . '/../includes/Validator.php';

$passed = 0;
$failed = 0;
function check(bool $cond, string $name): void
{
    global $passed, $failed;
    if ($cond) {
        $passed++;
        echo "  ok   $name\n";
    } else {
        $failed++;
        echo "  FAIL $name\n";
    }
}
function section(string $t): void { echo "\n$t\n"; }

Database::reset();
$pdo = Database::connect(['driver' => 'sqlite', 'sqlite_path' => ':memory:']);
Database::migrate($pdo, 'sqlite');
$repo = new Repository($pdo);

section('Validator');
check(Validator::user(['name' => 'A', 'email' => 'bad', 'password' => 'short', 'role' => 'x']) !== [] , 'rejects invalid user');
check(Validator::user(['name' => 'Asha', 'email' => 'a@b.co', 'password' => 'Password123!', 'role' => 'staff']) === [], 'accepts valid user');
check(isset(Validator::child(['first_name' => 'A', 'last_name' => 'Sharma', 'date_of_birth' => '2021-06-14'])['first_name']), 'child: short first name rejected');
check(isset(Validator::child(['first_name' => 'Aarav', 'last_name' => 'Sharma', 'date_of_birth' => date('Y-m-d', strtotime('+1 day'))])['date_of_birth']), 'child: future DOB rejected');
check(isset(Validator::child(['first_name' => 'Aarav', 'last_name' => 'Sharma', 'date_of_birth' => '1990-01-01'])['date_of_birth']), 'child: over-18 DOB rejected');
check(Validator::child(['first_name' => 'Aarav', 'last_name' => 'Sharma', 'date_of_birth' => '2021-06-14']) === [], 'child: valid record accepted');
check(isset(Validator::activity(['child_id' => '1', 'activity_type' => 'meal', 'description' => 'ok', 'occurred_at' => '2030-01-01T10:00'])['occurred_at']), 'activity: future time rejected');
check(isset(Validator::activity(['child_id' => 'x', 'activity_type' => 'dance', 'description' => 'ok', 'occurred_at' => 'now'])['activity_type']), 'activity: bad type rejected');

section('Users & login');
$admin  = $repo->createUser('Asha Admin', 'Admin@CareTrack.dev', 'Password123!', 'admin');
$staff  = $repo->createUser('Sunil Staff', 'staff@caretrack.dev', 'Password123!', 'staff');
$parent = $repo->createUser('Priya Parent', 'parent@caretrack.dev', 'Password123!', 'parent');
$other  = $repo->createUser('Other Parent', 'other@caretrack.dev', 'Password123!', 'parent');
check($repo->findUserByEmail('admin@caretrack.dev')['role'] === 'admin', 'email is normalised to lowercase');
check($repo->verifyLogin('admin@caretrack.dev', 'Password123!')['id'] === $admin, 'login succeeds with correct password');
check($repo->verifyLogin('admin@caretrack.dev', 'wrong') === null, 'login fails with wrong password');
check(!array_key_exists('password_hash', $repo->verifyLogin('admin@caretrack.dev', 'Password123!')), 'password hash is not leaked to session');
try { $repo->createUser('Dup', 'admin@caretrack.dev', 'Password123!', 'parent'); check(false, 'duplicate email rejected'); }
catch (PDOException $e) { check(true, 'duplicate email rejected by UNIQUE constraint'); }

section('Children (transactions & guardians)');
$childId = $repo->createChild(['first_name' => 'Aarav', 'last_name' => 'Sharma', 'date_of_birth' => '2021-06-14', 'allergies' => 'Peanuts', 'notes' => '', 'staff_id' => $staff], [$parent, $parent, $other]);
$child   = $repo->findChild($childId);
check($child['first_name'] === 'Aarav' && $child['staff_name'] === 'Sunil Staff', 'child created with staff join');
check(count($child['guardians']) === 2, 'duplicate guardian ids de-duplicated (2 guardians)');
$repo->updateChild($childId, ['first_name' => 'Aarav', 'last_name' => 'Sharma', 'date_of_birth' => '2021-06-14', 'allergies' => '', 'notes' => 'Updated', 'staff_id' => null], [$parent]);
$child = $repo->findChild($childId);
check($child['notes'] === 'Updated' && $child['allergies'] === null && count($child['guardians']) === 1, 'update replaces guardians and fields atomically');

// Transaction rollback: invalid parent id violates FK -> whole insert must roll back
$before = (int) $pdo->query('SELECT COUNT(*) FROM children')->fetchColumn();
try {
    $repo->createChild(['first_name' => 'Ghost', 'last_name' => 'Child', 'date_of_birth' => '2022-01-01', 'allergies' => '', 'notes' => '', 'staff_id' => null], [99999]);
    check(false, 'FK violation should throw');
} catch (PDOException $e) {
    $after = (int) $pdo->query('SELECT COUNT(*) FROM children')->fetchColumn();
    check($after === $before, 'failed guardian link rolls back the child insert (transaction)');
}

section('Role-based visibility');
$second = $repo->createChild(['first_name' => 'Meera', 'last_name' => 'Iyer', 'date_of_birth' => '2022-11-02', 'allergies' => '', 'notes' => '', 'staff_id' => $staff], [$other]);
check(count($repo->listChildrenFor(['id' => $staff, 'role' => 'staff'])) === 2, 'staff sees all children');
check(count($repo->listChildrenFor(['id' => $parent, 'role' => 'parent'])) === 1, 'parent sees only linked children');
check($repo->parentCanSeeChild($parent, $childId) && !$repo->parentCanSeeChild($parent, $second), 'parentCanSeeChild enforces link');

section('Activities');
$repo->logActivity($childId, $staff, 'meal', 'Ate lunch', '2026-01-10 12:30');
$repo->logActivity($childId, $staff, 'nap', 'Slept well', '2026-01-10 14:00');
$repo->logActivity($second, $staff, 'play', 'Outdoor play', '2026-01-11 10:00');
$acts = $repo->activitiesForChild($childId);
check(count($acts) === 2 && $acts[0]['activity_type'] === 'nap', 'activities listed newest first');
check(count($repo->recentActivities(['id' => $parent, 'role' => 'parent'])) === 2, 'parent recent feed limited to own children');
check(count($repo->recentActivities(['id' => $staff, 'role' => 'staff'])) === 3, 'staff recent feed shows all');
check($repo->counts() === ['children' => 2, 'parents' => 2, 'staff' => 2, 'activities' => 3], 'dashboard counts');

section('Cascade delete');
$repo->deleteChild($childId);
check($repo->findChild($childId) === null, 'child removed');
check((int) $pdo->query("SELECT COUNT(*) FROM activities WHERE child_id = $childId")->fetchColumn() === 0, 'activities cascade-deleted');
check((int) $pdo->query("SELECT COUNT(*) FROM child_parent WHERE child_id = $childId")->fetchColumn() === 0, 'guardian links cascade-deleted');
check($repo->findUser($parent) !== null, 'parent user is NOT deleted');

echo "\n$passed passed, $failed failed\n";
exit($failed ? 1 : 0);
