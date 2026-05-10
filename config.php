<?php
// Production credentials live in includes/config.local.php (gitignored).
// Loaded automatically below if present. Otherwise falls back to env vars.

if (file_exists(__DIR__ . '/config.local.php')) {
    return require __DIR__ . '/config.local.php';
}

return [
    'db' => [
        'host'     => getenv('DB_HOST') ?: 'localhost',
        'name'     => getenv('DB_NAME') ?: 'digital_harbor',
        'user'     => getenv('DB_USER') ?: 'root',
        'pass'     => getenv('DB_PASS') ?: '',
        'charset'  => 'utf8mb4',
    ],
    'mail' => [
        'admin_to'   => getenv('ADMIN_EMAIL') ?: 'admin@example.com',
        'from'       => getenv('MAIL_FROM')   ?: 'noreply@example.com',
        'from_name'  => 'Digital Harbor',
    ],
    'site' => [
        'name'      => 'Digital Harbor',
        'admin_url' => '/admin/',
        'cors_origins' => getenv('CORS_ORIGINS') ?: '*',
    ],
    'csrf_secret' => getenv('CSRF_SECRET') ?: 'dh-' . hash('sha256', 'fixed-secret-' . __FILE__),
];
