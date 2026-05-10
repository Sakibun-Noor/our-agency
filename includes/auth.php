<?php
require_once __DIR__ . '/db.php';

function start_session_once(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_set_cookie_params([
            'lifetime' => 0,
            'path'     => '/',
            'secure'   => !empty($_SERVER['HTTPS']),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_start();
    }
}

function login_user(string $email, string $password): ?array {
    $stmt = db()->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    if (!$user) return null;
    if (!password_verify($password, $user['password_hash'])) return null;

    start_session_once();
    session_regenerate_id(true);
    $_SESSION['uid']   = (int)$user['id'];
    $_SESSION['role']  = $user['role'];
    $_SESSION['name']  = $user['name'];
    $_SESSION['email'] = $user['email'];

    db()->prepare('UPDATE users SET last_login = NOW() WHERE id = ?')
        ->execute([$user['id']]);
    return $user;
}

function logout_user(): void {
    start_session_once();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

function current_user(): ?array {
    start_session_once();
    if (empty($_SESSION['uid'])) return null;
    return [
        'id'    => $_SESSION['uid'],
        'role'  => $_SESSION['role'] ?? 'viewer',
        'name'  => $_SESSION['name'] ?? '',
        'email' => $_SESSION['email'] ?? '',
    ];
}

function require_login(): array {
    $u = current_user();
    if (!$u) {
        header('Location: /admin/login.php');
        exit;
    }
    return $u;
}

function require_admin(): array {
    $u = require_login();
    if ($u['role'] !== 'admin') {
        http_response_code(403);
        echo 'Forbidden — admin role required.';
        exit;
    }
    return $u;
}

function csrf_token(): string {
    start_session_once();
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function check_csrf(?string $token): bool {
    start_session_once();
    return !empty($_SESSION['csrf']) && is_string($token) && hash_equals($_SESSION['csrf'], $token);
}

function e(?string $s): string {
    return htmlspecialchars((string)$s, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}
