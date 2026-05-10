<?php
require_once __DIR__ . '/../includes/layout.php';
$user = require_admin();

$err = null;
$ok = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && check_csrf($_POST['csrf'] ?? '')) {
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $name  = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $pass  = $_POST['password'] ?? '';
        $role  = ($_POST['role'] ?? 'viewer') === 'admin' ? 'admin' : 'viewer';
        if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($pass) < 8) {
            $err = 'Provide name, valid email, password 8+ chars.';
        } else {
            try {
                $hash = password_hash($pass, PASSWORD_DEFAULT);
                db()->prepare('INSERT INTO users (email, password_hash, name, role) VALUES (?, ?, ?, ?)')
                    ->execute([$email, $hash, $name, $role]);
                $ok = 'User created.';
            } catch (PDOException $e) {
                $err = 'Email already exists.';
            }
        }
    }

    if ($action === 'delete') {
        $uid = (int)($_POST['id'] ?? 0);
        if ($uid === $user['id']) {
            $err = 'You cannot delete your own account.';
        } else {
            db()->prepare('DELETE FROM users WHERE id = ?')->execute([$uid]);
            $ok = 'User deleted.';
        }
    }

    if ($action === 'role') {
        $uid = (int)($_POST['id'] ?? 0);
        $role = ($_POST['role'] ?? 'viewer') === 'admin' ? 'admin' : 'viewer';
        if ($uid === $user['id'] && $role !== 'admin') {
            $err = 'You cannot demote yourself.';
        } else {
            db()->prepare('UPDATE users SET role = ? WHERE id = ?')->execute([$role, $uid]);
            $ok = 'Role updated.';
        }
    }
}

$users = db()->query('SELECT id,email,name,role,created_at,last_login FROM users ORDER BY created_at DESC')->fetchAll();

admin_head('Users');
?>
<h1 class="ap-title">Team</h1>
<p class="ap-sub">Manage admin and viewer accounts.</p>

<?php if ($err): ?><div class="banner err"><?= e($err) ?></div><?php endif; ?>
<?php if ($ok):  ?><div class="banner ok"><?= e($ok) ?></div><?php endif; ?>

<div class="panel" style="margin-bottom:24px">
  <div class="panel-head">/ Add user</div>
  <form method="post" style="padding:20px;display:grid;grid-template-columns:1fr 1fr 1fr 140px auto;gap:12px;align-items:end">
    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
    <input type="hidden" name="action" value="create">
    <div><label class="frm" style="display:block;font-family:var(--mono);font-size:10.5px;letter-spacing:.16em;text-transform:uppercase;color:var(--muted);margin-bottom:6px">Name</label>
      <input type="text" name="name" required style="width:100%;padding:10px 12px;background:rgba(0,0,0,.3);border:1px solid var(--line-2);border-radius:8px;color:#fff;font:inherit"></div>
    <div><label style="display:block;font-family:var(--mono);font-size:10.5px;letter-spacing:.16em;text-transform:uppercase;color:var(--muted);margin-bottom:6px">Email</label>
      <input type="email" name="email" required style="width:100%;padding:10px 12px;background:rgba(0,0,0,.3);border:1px solid var(--line-2);border-radius:8px;color:#fff;font:inherit"></div>
    <div><label style="display:block;font-family:var(--mono);font-size:10.5px;letter-spacing:.16em;text-transform:uppercase;color:var(--muted);margin-bottom:6px">Password</label>
      <input type="password" name="password" minlength="8" required style="width:100%;padding:10px 12px;background:rgba(0,0,0,.3);border:1px solid var(--line-2);border-radius:8px;color:#fff;font:inherit"></div>
    <div><label style="display:block;font-family:var(--mono);font-size:10.5px;letter-spacing:.16em;text-transform:uppercase;color:var(--muted);margin-bottom:6px">Role</label>
      <select name="role" style="width:100%;padding:10px 12px;background:rgba(0,0,0,.3);border:1px solid var(--line-2);border-radius:8px;color:#fff;font:inherit">
        <option value="viewer">Viewer</option>
        <option value="admin">Admin</option>
      </select></div>
    <button class="btn btn-primary">Add user</button>
  </form>
</div>

<div class="panel">
  <table class="inbox">
    <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Last login</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($users as $u): ?>
      <tr>
        <td><strong><?= e($u['name']) ?></strong><?= $u['id']==$user['id']?' <span class="mt">(you)</span>':'' ?></td>
        <td class="mt"><?= e($u['email']) ?></td>
        <td>
          <form method="post" style="display:inline-flex;gap:6px">
            <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="action" value="role">
            <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
            <select name="role" onchange="this.form.submit()" style="padding:5px 8px;background:rgba(0,0,0,.3);border:1px solid var(--line);border-radius:6px;color:#cfd2d8;font-size:12px">
              <option value="viewer" <?= $u['role']==='viewer'?'selected':'' ?>>Viewer</option>
              <option value="admin"  <?= $u['role']==='admin'?'selected':'' ?>>Admin</option>
            </select>
          </form>
        </td>
        <td class="mt"><?= e($u['last_login'] ? date('M j · H:i', strtotime($u['last_login'])) : '—') ?></td>
        <td>
          <?php if ($u['id'] != $user['id']): ?>
          <form method="post" onsubmit="return confirm('Delete <?= e($u['email']) ?>?')">
            <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
            <button class="btn btn-sm btn-danger">Delete</button>
          </form>
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php admin_foot(); ?>
