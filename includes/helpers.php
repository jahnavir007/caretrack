<?php
declare(strict_types=1);

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function redirect(string $page, array $params = []): never
{
    $qs = http_build_query(['page' => $page] + $params);
    header('Location: index.php?' . $qs);
    exit;
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function takeFlash(): ?array
{
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $f;
}

function ageFromDob(string $dob): string
{
    $d = DateTime::createFromFormat('Y-m-d', $dob);
    if (!$d) {
        return '';
    }
    $diff = $d->diff(new DateTime('today'));
    return $diff->y > 0 ? $diff->y . ' yr' : $diff->m . ' mo';
}

function fmtDateTime(?string $ts): string
{
    return $ts ? date('d M Y, H:i', strtotime($ts)) : '';
}

/** Render a page inside the layout. */
function render(string $view, array $vars = []): void
{
    extract($vars, EXTR_SKIP);
    $flash = takeFlash();
    $currentUser = Auth::user();
    require __DIR__ . '/../pages/layout/header.php';
    require __DIR__ . '/../pages/' . $view . '.php';
    require __DIR__ . '/../pages/layout/footer.php';
}
