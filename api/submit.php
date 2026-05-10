<?php
require_once __DIR__ . '/../includes/db.php';

$cfg = require __DIR__ . '/../includes/config.php';

// CORS (only useful if InfinityFree challenge isn't blocking — keep for completeness)
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$allowed = $cfg['site']['cors_origins'];
if ($allowed === '*' || in_array($origin, array_map('trim', explode(',', $allowed)), true)) {
    header('Access-Control-Allow-Origin: ' . ($origin ?: '*'));
    header('Vary: Origin');
}
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

// Accept either JSON body (fetch) or form-encoded (native form POST with redirect)
$raw = file_get_contents('php://input');
$data = [];
$isJson = false;
if ($raw && str_starts_with(trim($raw), '{')) {
    $data = json_decode($raw, true) ?: [];
    $isJson = true;
} else {
    $data = $_POST;
}

// Where to redirect form-POST submissions back to (set in config)
$returnUrl = rtrim(($cfg['site']['return_url'] ?? 'https://our-agency-tau.vercel.app/contact.html'), '/');

// If submission came from the embedded iframe form, redirect inside the iframe (same-origin).
$isIframe = !empty($_POST['from_iframe']);
if ($isIframe) {
    $returnUrl = '/form.php';
}

function form_redirect(string $url, bool $ok): void {
    $sep = (strpos($url, '?') !== false) ? '&' : '?';
    header('Location: ' . $url . $sep . ($ok ? 'sent=1' : 'err=1') . '#top');
    exit;
}

function fail(string $msg, int $code, bool $isJson, string $returnUrl): void {
    if ($isJson) {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode(['ok' => false, 'error' => $msg]);
    } else {
        form_redirect($returnUrl, false);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    fail('Method not allowed', 405, $isJson, $returnUrl);
}

$name    = trim($data['name']    ?? '');
$email   = trim($data['email']   ?? '');
$company = trim($data['company'] ?? '');
$budget  = trim($data['budget']  ?? '');
$scope   = trim($data['scope']   ?? '');
$msg     = trim($data['message'] ?? '');

// Honeypot — bots fill hidden 'website' field
if (!empty($data['website'])) {
    if ($isJson) { echo json_encode(['ok' => true]); exit; }
    form_redirect($returnUrl, true);
}

$errors = [];
if ($name === '' || mb_strlen($name) > 200) $errors[] = 'name';
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'email';
if ($msg === '' || mb_strlen($msg) > 5000) $errors[] = 'message';
if ($errors) {
    fail('Invalid input', 400, $isJson, $returnUrl);
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

    if ($isJson) {
        header('Content-Type: application/json');
        echo json_encode(['ok' => true, 'id' => (int)$id]);
    } else {
        form_redirect($returnUrl, true);
    }
} catch (Throwable $e) {
    fail('Server error', 500, $isJson, $returnUrl);
}
