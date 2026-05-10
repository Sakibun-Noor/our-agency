<?php
require_once __DIR__ . '/auth.php';

function admin_head(string $title): void {
    $u = current_user();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= e($title) ?> — Admin</title>
<link rel="stylesheet" href="/admin/admin.css">
</head>
<body>
<header class="ah">
  <div class="ah-inner">
    <a href="/admin/" class="ah-logo">
      <span class="mark"></span> Digital Harbor <span class="ah-tag">/ admin</span>
    </a>
    <?php if ($u): ?>
      <nav class="ah-nav">
        <a href="/admin/" class="<?= basename($_SERVER['PHP_SELF'])==='index.php'?'active':'' ?>">Inbox</a>
        <a href="/admin/content.php" class="<?= basename($_SERVER['PHP_SELF'])==='content.php'?'active':'' ?>">Content</a>
        <?php if ($u['role']==='admin'): ?>
          <a href="/admin/users.php" class="<?= basename($_SERVER['PHP_SELF'])==='users.php'?'active':'' ?>">Users</a>
        <?php endif; ?>
      </nav>
      <div class="ah-user">
        <span class="ah-uname"><?= e($u['name']) ?> <span class="ah-role"><?= e($u['role']) ?></span></span>
        <a href="/admin/logout.php" class="ah-out">Sign out</a>
      </div>
    <?php endif; ?>
  </div>
</header>
<main class="ap">
<?php
}

function admin_foot(): void {
?>
</main>
</body>
</html>
<?php
}
