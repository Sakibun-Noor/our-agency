<?php
require_once __DIR__ . '/../includes/layout.php';
$user = require_login();

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) { header('Location: /admin/'); exit; }

// Action handlers
if ($_SERVER['REQUEST_METHOD'] === 'POST' && check_csrf($_POST['csrf'] ?? '')) {
    $action = $_POST['action'] ?? '';
    if ($action === 'status' && in_array($_POST['status'] ?? '', ['unread','read','replied','archived'], true)) {
        db()->prepare('UPDATE messages SET status = ? WHERE id = ?')
            ->execute([$_POST['status'], $id]);
        header("Location: /admin/message.php?id=$id&ok=status");
        exit;
    }
    if ($action === 'delete' && $user['role'] === 'admin') {
        db()->prepare('DELETE FROM messages WHERE id = ?')->execute([$id]);
        header('Location: /admin/?ok=deleted');
        exit;
    }
}

$stmt = db()->prepare('SELECT * FROM messages WHERE id = ?');
$stmt->execute([$id]);
$m = $stmt->fetch();
if (!$m) { http_response_code(404); echo 'Not found'; exit; }

// Mark as read on first view
if ($m['status'] === 'unread') {
    db()->prepare('UPDATE messages SET status = "read" WHERE id = ?')->execute([$id]);
    $m['status'] = 'read';
}

admin_head('Message · ' . $m['name']);
?>
<a href="/admin/" class="btn btn-sm" style="margin-bottom:18px">← Inbox</a>
<h1 class="ap-title"><?= e($m['name']) ?></h1>
<p class="ap-sub"><?= e($m['email']) ?> &middot; <?= e(date('F j, Y · H:i', strtotime($m['created_at']))) ?></p>

<?php if (!empty($_GET['ok'])): ?>
  <div class="banner ok">Updated.</div>
<?php endif; ?>

<div class="msg">
  <div class="body"><?= e($m['message']) ?></div>
  <div>
    <div class="meta">
      <dl>
        <dt>Status</dt><dd><span class="pill <?= e($m['status']) ?>"><?= e($m['status']) ?></span></dd>
        <?php if ($m['company']): ?><dt>Company</dt><dd><?= e($m['company']) ?></dd><?php endif; ?>
        <?php if ($m['scope']): ?><dt>Scope</dt><dd><?= e($m['scope']) ?></dd><?php endif; ?>
        <?php if ($m['budget']): ?><dt>Budget</dt><dd>$<?= e($m['budget']) ?>K</dd><?php endif; ?>
        <dt>Email</dt><dd><a href="mailto:<?= e($m['email']) ?>" style="color:var(--accent)"><?= e($m['email']) ?></a></dd>
        <?php if ($m['ip']): ?><dt>IP</dt><dd class="mt"><?= e($m['ip']) ?></dd><?php endif; ?>
        <dt>Received</dt><dd class="mt"><?= e($m['created_at']) ?></dd>
      </dl>
    </div>

    <form method="post" class="actions">
      <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
      <input type="hidden" name="action" value="status">
      <a class="btn btn-primary" href="mailto:<?= e($m['email']) ?>?subject=<?= rawurlencode('Re: your brief') ?>&body=<?= rawurlencode("Hi " . $m['name'] . ",\n\n") ?>">Reply via email →</a>
      <?php if ($m['status'] !== 'replied'): ?>
        <button name="status" value="replied" class="btn">Mark as replied</button>
      <?php endif; ?>
      <?php if ($m['status'] !== 'archived'): ?>
        <button name="status" value="archived" class="btn">Archive</button>
      <?php else: ?>
        <button name="status" value="read" class="btn">Restore</button>
      <?php endif; ?>
    </form>

    <?php if ($user['role'] === 'admin'): ?>
    <form method="post" class="actions" onsubmit="return confirm('Delete this message permanently?')">
      <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
      <input type="hidden" name="action" value="delete">
      <button class="btn btn-danger">Delete permanently</button>
    </form>
    <?php endif; ?>
  </div>
</div>

<?php admin_foot(); ?>
