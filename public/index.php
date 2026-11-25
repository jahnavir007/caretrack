<?php
/**
 * CareTrack front controller. Routes ?page=... to the matching handler.
 * Run locally: php -S localhost:8080 -t public   (after `php bin/setup.php`)
 */
declare(strict_types=1);

$config = require __DIR__ . '/../config/config.php';
require __DIR__ . '/../includes/Database.php';
require __DIR__ . '/../includes/Repository.php';
require __DIR__ . '/../includes/Validator.php';
require __DIR__ . '/../includes/Auth.php';
require __DIR__ . '/../includes/helpers.php';

Auth::start($config['app']['session_name']);
$pdo  = Database::connect($config['db']);
$repo = new Repository($pdo);

$page   = $_GET['page'] ?? (Auth::check() ? 'dashboard' : 'login');
$method = $_SERVER['REQUEST_METHOD'];

switch ($page) {
    // ----------------------------------------------------------- auth
    case 'login':
        if ($method === 'POST') {
            Auth::verifyCsrf();
            $user = $repo->verifyLogin($_POST['email'] ?? '', $_POST['password'] ?? '');
            if ($user) {
                Auth::login($user);
                redirect('dashboard');
            }
            render('login', ['error' => 'Invalid email or password.', 'email' => $_POST['email'] ?? '']);
            break;
        }
        render('login', ['error' => null, 'email' => '']);
        break;

    case 'logout':
        Auth::logout();
        header('Location: index.php?page=login');
        exit;

    // ------------------------------------------------------ dashboard
    case 'dashboard':
        $user = Auth::requireLogin();
        render('dashboard', [
            'counts'     => $user['role'] === 'parent' ? null : $repo->counts(),
            'children'   => $repo->listChildrenFor($user),
            'activities' => $repo->recentActivities($user, 10),
        ]);
        break;

    // ------------------------------------------------------- children
    case 'children':
        $user = Auth::requireLogin();
        render('children/index', ['children' => $repo->listChildrenFor($user)]);
        break;

    case 'child':
        $user = Auth::requireLogin();
        $id   = (int) ($_GET['id'] ?? 0);
        if ($user['role'] === 'parent' && !$repo->parentCanSeeChild((int) $user['id'], $id)) {
            http_response_code(403);
            echo 'Forbidden';
            break;
        }
        $child = $repo->findChild($id);
        if (!$child) {
            http_response_code(404);
            echo 'Child not found';
            break;
        }
        render('children/show', ['child' => $child, 'activities' => $repo->activitiesForChild($id)]);
        break;

    case 'child_new':
    case 'child_edit':
        $user  = Auth::requireStaff();
        $id    = (int) ($_GET['id'] ?? 0);
        $child = $page === 'child_edit' ? $repo->findChild($id) : null;
        if ($page === 'child_edit' && !$child) {
            http_response_code(404);
            echo 'Child not found';
            break;
        }
        $errors = [];
        $input  = $child ?? ['first_name' => '', 'last_name' => '', 'date_of_birth' => '', 'allergies' => '', 'notes' => '', 'staff_id' => '', 'guardians' => []];
        if ($method === 'POST') {
            Auth::verifyCsrf();
            $input   = array_merge($input, $_POST);
            $errors  = Validator::child($_POST);
            $parents = array_map('intval', $_POST['parent_ids'] ?? []);
            if (!$errors) {
                if ($child) {
                    $repo->updateChild($id, $_POST, $parents);
                    flash('success', 'Child record updated.');
                    redirect('child', ['id' => $id]);
                }
                $newId = $repo->createChild($_POST, $parents);
                flash('success', 'Child added.');
                redirect('child', ['id' => $newId]);
            }
        }
        $selected = isset($input['guardians']) ? array_column($input['guardians'], 'id') : array_map('intval', $_POST['parent_ids'] ?? []);
        render('children/form', [
            'child'    => $child,
            'input'    => $input,
            'errors'   => $errors,
            'parents'  => $repo->usersByRole('parent'),
            'staff'    => array_merge($repo->usersByRole('staff'), $repo->usersByRole('admin')),
            'selected' => $selected,
        ]);
        break;

    case 'child_delete':
        Auth::requireStaff();
        if ($method !== 'POST') {
            http_response_code(405);
            echo 'Method not allowed';
            break;
        }
        Auth::verifyCsrf();
        $repo->deleteChild((int) ($_POST['id'] ?? 0));
        flash('success', 'Child record and related activities removed.');
        redirect('children');

    // ----------------------------------------------------- activities
    case 'activity_new':
        $user   = Auth::requireStaff();
        $errors = [];
        $input  = ['child_id' => $_GET['child_id'] ?? '', 'activity_type' => 'meal', 'description' => '', 'occurred_at' => date('Y-m-d\TH:i')];
        if ($method === 'POST') {
            Auth::verifyCsrf();
            $input  = array_merge($input, $_POST);
            $errors = Validator::activity($_POST);
            if (!$errors) {
                $repo->logActivity((int) $_POST['child_id'], (int) $user['id'], $_POST['activity_type'], $_POST['description'], $_POST['occurred_at']);
                flash('success', 'Activity logged.');
                redirect('child', ['id' => (int) $_POST['child_id']]);
            }
        }
        render('activities/form', ['input' => $input, 'errors' => $errors, 'children' => $repo->listChildrenFor($user)]);
        break;

    // ---------------------------------------------------------- users
    case 'users':
        Auth::requireAdmin();
        render('users/index', ['parents' => $repo->usersByRole('parent'), 'staff' => $repo->usersByRole('staff'), 'admins' => $repo->usersByRole('admin')]);
        break;

    case 'user_new':
        Auth::requireAdmin();
        $errors = [];
        $input  = ['name' => '', 'email' => '', 'role' => 'parent', 'phone' => ''];
        if ($method === 'POST') {
            Auth::verifyCsrf();
            $input  = array_merge($input, $_POST);
            $errors = Validator::user($_POST);
            if (!$errors && $repo->findUserByEmail($_POST['email'])) {
                $errors['email'] = 'That email is already registered.';
            }
            if (!$errors) {
                $repo->createUser($_POST['name'], $_POST['email'], $_POST['password'], $_POST['role'], $_POST['phone'] ?? null);
                flash('success', 'User created.');
                redirect('users');
            }
        }
        render('users/form', ['input' => $input, 'errors' => $errors]);
        break;

    default:
        http_response_code(404);
        render('notfound');
}
