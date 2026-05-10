<?php
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json');

$user = current_user();
if (!$user) { http_response_code(401); echo json_encode(['ok'=>false,'error'=>'Unauthorized']); exit; }

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); echo json_encode(['ok'=>false,'error'=>'Method not allowed']); exit;
}

if (!check_csrf($_POST['csrf'] ?? '')) {
    http_response_code(403); echo json_encode(['ok'=>false,'error'=>'Bad CSRF']); exit;
}

if (empty($_FILES['file']) || !is_uploaded_file($_FILES['file']['tmp_name'])) {
    http_response_code(400); echo json_encode(['ok'=>false,'error'=>'No file']); exit;
}

$f = $_FILES['file'];
if ($f['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400); echo json_encode(['ok'=>false,'error'=>'Upload error '.$f['error']]); exit;
}
if ($f['size'] > 5 * 1024 * 1024) {
    http_response_code(400); echo json_encode(['ok'=>false,'error'=>'Max 5MB']); exit;
}

$mime = mime_content_type($f['tmp_name']) ?: '';
$allowed = [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/webp' => 'webp',
    'image/gif'  => 'gif',
];
if (!isset($allowed[$mime])) {
    http_response_code(400); echo json_encode(['ok'=>false,'error'=>'Only JPG/PNG/WEBP/GIF allowed']); exit;
}

$dir = dirname(__DIR__) . '/uploads';
if (!is_dir($dir)) {
    if (!mkdir($dir, 0755, true) && !is_dir($dir)) {
        http_response_code(500); echo json_encode(['ok'=>false,'error'=>'Cannot create uploads dir']); exit;
    }
}

$ext  = $allowed[$mime];
$slug = bin2hex(random_bytes(6));
$name = ($_POST['purpose'] ?? 'img') . '-' . date('Ymd-His') . '-' . $slug . '.' . $ext;
$dest = $dir . '/' . $name;

if (!move_uploaded_file($f['tmp_name'], $dest)) {
    http_response_code(500); echo json_encode(['ok'=>false,'error'=>'Save failed']); exit;
}
@chmod($dest, 0644);

// Public URL — uploads is at htdocs/uploads/, served at /uploads/
$proto = !empty($_SERVER['HTTPS']) ? 'https' : 'http';
$host  = $_SERVER['HTTP_HOST'] ?? 'myagency.page.gd';
$url   = "$proto://$host/uploads/$name";

echo json_encode(['ok'=>true, 'url'=>$url, 'name'=>$name]);
