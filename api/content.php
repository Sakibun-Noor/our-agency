<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/content.php';

$cfg = require __DIR__ . '/../includes/config.php';

// CORS — allow Vercel origin (or whatever is whitelisted in config)
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$allowed = $cfg['site']['cors_origins'];
if ($allowed === '*' || in_array($origin, array_map('trim', explode(',', $allowed)), true)) {
    header('Access-Control-Allow-Origin: ' . ($origin ?: '*'));
    header('Vary: Origin');
}
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: public, max-age=30, stale-while-revalidate=120');

try {
    $data = content_load();
    echo json_encode(['ok'=>true, 'data'=>$data], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok'=>false, 'error'=>'Server error']);
}
