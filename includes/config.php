<?php
// Copy this to config.local.php and fill in real values, OR set env vars.
// Never commit real credentials.

return [
    'db' => [
        'host'     => getenv('DB_HOST') ?: 'localhost',
        'name'     => getenv('DB_NAME') ?: 'digital_harbor',
        'user'     => getenv('DB_USER') ?: 'root',
        'pass'     => getenv('DB_PASS') ?: '',
        'charset'  => 'utf8mb4',
    ],
    'mail' => [
        // Where contact-form messages are forwarded
        'admin_to'   => getenv('ADMIN_EMAIL') ?: 'admin@factory.services',
        'from'       => getenv('MAIL_FROM')   ?: 'noreply@digitalharbor.local',
        'from_name'  => 'Digital Harbor',
    ],
    'site' => [
        'name'      => 'Digital Harbor',
        'admin_url' => '/admin/',
        // Comma-separated origins allowed to POST to api/submit.php (CORS)
        'cors_origins' => getenv('CORS_ORIGINS') ?: '*',
    ],
    // Cookie/session secret used for CSRF tokens
    'csrf_secret' => getenv('CSRF_SECRET') ?: 'change-me-' . bin2hex(random_bytes(8)),
];
