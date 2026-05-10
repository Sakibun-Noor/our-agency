<?php
require_once __DIR__ . '/../includes/layout.php';
$user = require_login();

$filter = $_GET['f'] ?? 'all';
$where = '';
$params = [];
if (in_array($filter, ['unread','read','replied','archived'], true)) {
    $where = 'WHERE status = ?';
    $params[] = $filter;
}

$rows = db()->prepare("SELECT id,name,email,company,scope,budget,LEFT(message,200) AS preview,status,created_at
                        FROM messages $where ORDER BY created_at DESC LIMIT 200");
$rows->execute($params);
$messages = $rows->fetchAll();

$counts = db()->query("SELECT status, COUNT(*) c FROM messages GROUP BY status")
              ->fetchAll(PDO::FETCH_KEY_PAIR);
$total = array_sum($counts);
$unread = (int)($counts['unread'] ?? 0);

admin_head('Inbox');
?>
<h1 class="ap-title">Inbox</h1>
<p class="ap-sub">Contact form submissions from <code>/contact.html</code>.</p>

<div class="toolbar">
  <div class="left">
    <span class="stat">Total <strong><?= $total ?></strong></span>
    <span class="stat">Unread <strong><?= $unread ?></strong></span>
  </div>
  <div class="filter">
    <a href="?f=all"      class="<?= $filter==='all'?'active':'' ?>">All</a>
    <a href="?f=unread"   class="<?= $filter==='unread'?'active':'' ?>">Unread</a>
    <a href="?f=read"     class="<?= $filter==='read'?'active':'' ?>">Read</a>
    <a href="?f=replied"  class="<?= $filter==='replied'?'active':'' ?>">Replied</a>
    <a href="?f=archived" class="<?= $filter==='archived'?'active':'' ?>">Archived</a>
  </div>
</div>

<div class="panel">
<?php if (empty($messages)): ?>
  <div class="empty">
    <h3>No messages yet.</h3>
    <p>Submissions to <code>/contact.html</code> will appear here in real time.</p>
  </div>
<?php else: ?>
<table class="inbox">
  <thead>
    <tr>
      <th>From</th>
      <th>Subject / preview</th>
      <th>Scope · Budget</th>
      <th>Received</th>
      <th>Status</th>
    </tr>
  </thead>
  <tbody>
  <?php foreach ($messages as $m): ?>
    <tr class="row-link <?= $m['status']==='unread'?'row-unread':'' ?>"
        onclick="location.href='/admin/message.php?id=<?= (int)$m['id'] ?>'">
      <td>
        <div><strong><?= e($m['name']) ?></strong></div>
        <div class="mt"><?= e($m['email']) ?></div>
        <?php if ($m['company']): ?><div class="mt"><?= e($m['company']) ?></div><?php endif; ?>
      </td>
      <td class="preview"><?= e($m['preview']) ?>…</td>
      <td>
        <?php if ($m['scope']): ?><div><?= e($m['scope']) ?></div><?php endif; ?>
        <?php if ($m['budget']): ?><div class="mt">$<?= e($m['budget']) ?>K</div><?php endif; ?>
      </td>
      <td class="mt"><?= e(date('M j · H:i', strtotime($m['created_at']))) ?></td>
      <td><span class="pill <?= e($m['status']) ?>"><?= e($m['status']) ?></span></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
<?php endif; ?>
</div>

<?php admin_foot(); ?>
