<?php
require_once __DIR__ . '/../includes/auth.php';

start_session_once();
if (current_user()) { header('Location: /admin/'); exit; }

$err = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!check_csrf($_POST['csrf'] ?? '')) {
        $err = 'Session expired — refresh and retry.';
    } else {
        $email = trim($_POST['email'] ?? '');
        $pass  = $_POST['password'] ?? '';
        $u = login_user($email, $pass);
        if ($u) { header('Location: /admin/'); exit; }
        $err = 'Invalid email or password.';
    }
}
?><!doctype html>
<meta charset=utf-8>
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Sign in — Admin</title>
<link rel="stylesheet" href="/admin/admin.css">
<body>
<main class="ap">
  <form class="frm" method="post">
    <h2>Sign in</h2>
    <p class="frm-sub">Digital Harbor — admin dashboard.</p>
    <?php if ($err): ?><div class="err"><?= e($err) ?></div><?php endif; ?>
    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
    <div>
      <label>Email</label>
      <input type="email" name="email" required autofocus value="<?= e($_POST['email'] ?? '') ?>">
    </div>
    <div>
      <label>Password</label>
      <input type="password" name="password" required>
    </div>
    <button type="submit" class="btn btn-primary">Sign in</button>
  </form>
</main>
</body>
