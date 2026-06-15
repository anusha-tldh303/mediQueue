<?php
declare(strict_types=1);

require_once __DIR__ . '/functions.php';

function login_user(array $user): void
{
    $_SESSION['user_id'] = (int) $user['id'];
    $_SESSION['full_name'] = (string) $user['full_name'];
    $_SESSION['email'] = (string) $user['email'];
    $_SESSION['role'] = (string) $user['role'];
}

function logout_user(): void
{
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }

    session_destroy();
}

function require_login(): void
{
    if (!is_logged_in()) {
        flash('error', 'Please log in to continue.');
        redirect('login.php');
    }
}

function require_role(string|array $roles): void
{
    require_login();

    if (!has_role($roles)) {
        flash('error', 'You do not have permission to access that page.');
        redirect('index.php');
    }
}
