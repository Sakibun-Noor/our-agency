<?php
// Run once after deploy. Creates schema (if missing) and seeds first admin user.
// Refuses to run after at least one user exists.

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

$err = null;
$done = false;

try {
    // Ensure schema is loaded
    $sql = file_get_contents(__DIR__ . '/../includes/schema.sql');
    if ($sql) {
        foreach (array_filter(array_map('trim', explode(';', $sql))) as $stmt) {
            if ($stmt === '' || str_starts_with($stmt, '--')) continue;
            db()->exec($stmt);
        }
    }

    // If a user already exists, lock setup
    $count = (int) db()->query('SELECT COUNT(*) FROM users')->fetchColumn();
    if ($count > 0) {
        echo '<!doctype html><meta charset=utf-8><title>Setup locked</title>';
        echo '<body style="background:#0a0b0e;color:#f5f6f8;font-family:system-ui;padding:40px;text-align:center">';
        echo '<h2>Setup is locked</h2>';
        echo '<p>An admin user already exists. Visit <a href="/admin/login.php" style="color:#00E5FF">/admin/login.php</a>.</p>';
        echo '<p style="color:#8a8f99;font-size:13px;margin-top:24px">Delete the user from DB only if you need to reset.</p>';
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name  = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $pass  = $_POST['password'] ?? '';

        if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($pass) < 8) {
            $err = 'Provide name, valid email, and a password of 8+ characters.';
        } else {
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            db()->prepare('INSERT INTO users (email, password_hash, name, role) VALUES (?, ?, ?, "admin")')
                ->execute([$email, $hash, $name]);
            $done = true;
        }
    }
} catch (Throwable $e) {
    $err = 'Error: ' . $e->getMessage();
}
?><!doctype html>
<meta charset=utf-8>
<title>Setup — Digital Harbor admin</title>
<link rel="stylesheet" href="/admin/admin.css">
<body>
<main class="ap">
<?php if ($done): ?>
  <div class="frm">
    <h2>Admin created ✓</h2>
    <p class="frm-sub">First admin seeded. You can now sign in. <strong>Delete this file</strong> from the server to fully lock setup.</p>
    <a href="/admin/login.php" class="btn btn-primary">Go to login →</a>
  </div>
<?php else: ?>
  <form class="frm" method="post" autocomplete="off">
    <h2>Create first admin</h2>
    <p class="frm-sub">No users exist yet. Set up the first admin account. After this, setup.php is locked.</p>
    <?php if ($err): ?><div class="err"><?= e($err) ?></div><?php endif; ?>
    <div>
      <label>Full name</label>
      <input type="text" name="name" required>
    </div>
    <div>
      <label>Email</label>
      <input type="email" name="email" required>
    </div>
    <div>
      <label>Password (8+ chars)</label>
      <input type="password" name="password" minlength="8" required>
    </div>
    <button type="submit" class="btn btn-primary">Create admin</button>
  </form>
<?php endif; ?>
</main>
</body>
