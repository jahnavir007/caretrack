<?php
declare(strict_types=1);

/** Session-based authentication and role checks. */
final class Auth
{
    public static function start(string $sessionName): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_name($sessionName);
            session_start();
        }
        if (empty($_SESSION['csrf'])) {
            $_SESSION['csrf'] = bin2hex(random_bytes(16));
        }
    }

    public static function login(array $user): void
    {
        session_regenerate_id(true);
        $_SESSION['user'] = $user;
    }

    public static function logout(): void
    {
        $_SESSION = [];
        session_destroy();
    }

    public static function user(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    public static function check(): bool
    {
        return isset($_SESSION['user']);
    }

    public static function requireLogin(): array
    {
        if (!self::check()) {
            header('Location: index.php?page=login');
            exit;
        }
        return $_SESSION['user'];
    }

    /** Only staff and admin may manage records; parents get a read-only view. */
    public static function requireStaff(): array
    {
        $user = self::requireLogin();
        if (!in_array($user['role'], ['staff', 'admin'], true)) {
            http_response_code(403);
            echo 'Forbidden: staff access required.';
            exit;
        }
        return $user;
    }

    public static function requireAdmin(): array
    {
        $user = self::requireLogin();
        if ($user['role'] !== 'admin') {
            http_response_code(403);
            echo 'Forbidden: admin access required.';
            exit;
        }
        return $user;
    }

    public static function csrfField(): string
    {
        return '<input type="hidden" name="csrf" value="' . htmlspecialchars($_SESSION['csrf']) . '">';
    }

    public static function verifyCsrf(): void
    {
        if (($_POST['csrf'] ?? '') !== ($_SESSION['csrf'] ?? null)) {
            http_response_code(419);
            echo 'Invalid or expired form token. Please go back and try again.';
            exit;
        }
    }
}
