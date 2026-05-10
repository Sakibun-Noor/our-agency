<?php
require_once __DIR__ . '/../includes/db.php';

$cfg = require __DIR__ . '/../includes/config.php';

// CORS
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$allowed = $cfg['site']['cors_origins'];
if ($allowed === '*' || in_array($origin, array_map('trim', explode(',', $allowed)), true)) {
    header('Access-Control-Allow-Origin: ' . ($origin ?: '*'));
    header('Vary: Origin');
}
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
    exit;
}

header('Content-Type: application/json');

// Accept either JSON body or form-encoded
$raw = file_get_contents('php://input');
$data = [];
if ($raw && str_starts_with(trim($raw), '{')) {
    $data = json_decode($raw, true) ?: [];
} else {
    $data = $_POST;
}

$name    = trim($data['name']    ?? '');
$email   = trim($data['email']   ?? '');
$company = trim($data['company'] ?? '');
$budget  = trim($data['budget']  ?? '');
$scope   = trim($data['scope']   ?? '');
$msg     = trim($data['message'] ?? '');

// Honeypot — bots fill hidden 'website' field
if (!empty($data['website'])) {
    echo json_encode(['ok' => true]); exit; // pretend success
}

$errors = [];
if ($name === '' || mb_strlen($name) > 200) $errors[] = 'name';
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'email';
if ($msg === '' || mb_strlen($msg) > 5000) $errors[] = 'message';
if ($errors) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Invalid input', 'fields' => $errors]);
    exit;
}

try {
    $stmt = db()->prepare('INSERT INTO messages (name,email,company,budget,scope,message,ip,user_agent)
                           VALUES (?,?,?,?,?,?,?,?)');
    $stmt->execute([
        $name, $email,
        $company ?: null,
        $budget  ?: null,
        $scope   ?: null,
        $msg,
        $_SERVER['REMOTE_ADDR'] ?? null,
        substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500),
    ]);
    $id = db()->lastInsertId();

    // Forward to admin email
    $to      = $cfg['mail']['admin_to'];
    $subject = "[Digital Harbor] New brief — $name";
    $body    = "New contact form submission\n"
             . str_repeat('-', 40) . "\n"
             . "Name:    $name\n"
             . "Email:   $email\n"
             . "Company: " . ($company ?: '—') . "\n"
             . "Scope:   " . ($scope   ?: '—') . "\n"
             . "Budget:  " . ($budget  ?: '—') . "\n\n"
             . "Message:\n$msg\n\n"
             . str_repeat('-', 40) . "\n"
             . "View in admin: http" . (!empty($_SERVER['HTTPS'])?'s':'') . "://" . $_SERVER['HTTP_HOST'] . "/admin/message.php?id=$id\n";
    $headers = "From: {$cfg['mail']['from_name']} <{$cfg['mail']['from']}>\r\n"
             . "Reply-To: $email\r\n"
             . "X-Mailer: PHP/" . phpversion();
    @mail($to, $subject, $body, $headers); // best-effort; never fail the form on mail failure

    echo json_encode(['ok' => true, 'id' => (int)$id]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Server error']);
}
