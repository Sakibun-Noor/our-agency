<?php
require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/content.php';

$user = require_login();

$ok = $err = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && check_csrf($_POST['csrf'] ?? '')) {
    try {
        $current = content_load();

        // Hero
        if (isset($_POST['hero'])) {
            foreach ($_POST['hero'] as $k => $v) {
                $current['hero'][$k] = (string)$v;
            }
        }
        // Marquee — array of names
        if (isset($_POST['marquee']) && is_array($_POST['marquee'])) {
            $current['marquee'] = array_values(array_filter(array_map('trim', $_POST['marquee']), fn($s) => $s !== ''));
        }
        // Services
        if (isset($_POST['services']) && is_array($_POST['services'])) {
            $svc = [];
            foreach ($_POST['services'] as $i => $row) {
                $svc[] = [
                    'num'   => (string)($row['num']   ?? ''),
                    'title' => (string)($row['title'] ?? ''),
                    'desc'  => (string)($row['desc']  ?? ''),
                    'img'   => (string)($row['img']   ?? ''),
                    'tags'  => array_values(array_filter(array_map('trim', explode(',', (string)($row['tags'] ?? ''))), fn($s) => $s !== '')),
                ];
            }
            $current['services'] = $svc;
        }
        // Team
        if (isset($_POST['team']) && is_array($_POST['team'])) {
            $team = [];
            foreach ($_POST['team'] as $i => $row) {
                $name = trim((string)($row['name'] ?? ''));
                if ($name === '') continue;
                $team[] = [
                    'name'  => $name,
                    'role'  => (string)($row['role'] ?? ''),
                    'glyph' => strtoupper(substr((string)($row['glyph'] ?? ''), 0, 4)),
                ];
            }
            $current['team'] = $team;
        }
        // FAQ
        if (isset($_POST['faq']) && is_array($_POST['faq'])) {
            $faq = [];
            foreach ($_POST['faq'] as $row) {
                $q = trim((string)($row['q'] ?? ''));
                if ($q === '') continue;
                $faq[] = ['q' => $q, 'a' => (string)($row['a'] ?? '')];
            }
            $current['faq'] = $faq;
        }

        content_save($current, $user['id']);
        $ok = 'Saved.';
    } catch (Throwable $e) {
        $err = 'Error: ' . $e->getMessage();
    }
}

$c = content_load();
admin_head('Content');
?>
<h1 class="ap-title">Content</h1>
<p class="ap-sub">Edit hero text, services, team members, marquee names and FAQ. Changes go live on the public site within seconds.</p>

<?php if ($err): ?><div class="banner err"><?= e($err) ?></div><?php endif; ?>
<?php if ($ok):  ?><div class="banner ok"><?= e($ok) ?></div><?php endif; ?>

<form method="post" id="contentForm" onsubmit="return true;">
  <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

  <!-- Hero -->
  <div class="panel" style="margin-bottom:24px">
    <div class="panel-head">/ Hero</div>
    <div style="padding:20px;display:grid;grid-template-columns:1fr 1fr;gap:14px">
      <div style="grid-column:span 2">
        <label class="lab">Eyebrow</label>
        <input type="text" name="hero[eyebrow]" value="<?= e($c['hero']['eyebrow']) ?>" class="inp">
      </div>
      <div><label class="lab">H1 Part 1 (sans)</label><input type="text" name="hero[h1_1]" value="<?= e($c['hero']['h1_1']) ?>" class="inp"></div>
      <div><label class="lab">H1 Part 2 (italic serif)</label><input type="text" name="hero[h1_2]" value="<?= e($c['hero']['h1_2']) ?>" class="inp"></div>
      <div><label class="lab">H1 Part 3 (sans)</label><input type="text" name="hero[h1_3]" value="<?= e($c['hero']['h1_3']) ?>" class="inp"></div>
      <div><label class="lab">H1 Part 4 (accent serif italic)</label><input type="text" name="hero[h1_4]" value="<?= e($c['hero']['h1_4']) ?>" class="inp"></div>
      <div style="grid-column:span 2">
        <label class="lab">Subtitle</label>
        <textarea name="hero[sub]" class="inp" rows="3"><?= e($c['hero']['sub']) ?></textarea>
      </div>
      <div><label class="lab">CTA primary</label><input type="text" name="hero[cta1]" value="<?= e($c['hero']['cta1']) ?>" class="inp"></div>
      <div><label class="lab">CTA secondary</label><input type="text" name="hero[cta2]" value="<?= e($c['hero']['cta2']) ?>" class="inp"></div>
    </div>
  </div>

  <!-- Marquee -->
  <div class="panel" style="margin-bottom:24px">
    <div class="panel-head">/ Marquee names</div>
    <div style="padding:20px;display:flex;flex-direction:column;gap:8px" id="marqueeRows">
      <?php foreach ($c['marquee'] as $i => $name): ?>
        <input type="text" name="marquee[]" value="<?= e($name) ?>" class="inp" placeholder="Name (leave blank to remove)">
      <?php endforeach; ?>
      <input type="text" name="marquee[]" value="" class="inp" placeholder="+ add another">
    </div>
  </div>

  <!-- Services -->
  <div class="panel" style="margin-bottom:24px">
    <div class="panel-head">/ Services (7 cards)</div>
    <div style="padding:20px;display:flex;flex-direction:column;gap:18px">
      <?php foreach ($c['services'] as $i => $s): ?>
        <fieldset style="border:1px solid var(--line);border-radius:12px;padding:16px">
          <legend style="padding:0 8px;font-family:var(--mono);font-size:11px;letter-spacing:.14em;text-transform:uppercase;color:var(--muted)">Card #<?= $i+1 ?></legend>
          <div style="display:grid;grid-template-columns:140px 1fr 1fr;gap:12px">
            <div>
              <label class="lab">Image</label>
              <div class="thumb-wrap">
                <img id="svcimg-<?= $i ?>" src="<?= e($s['img']) ?>" data-default="<?= e($s['img']) ?>" alt="" style="width:100%;aspect-ratio:16/10;object-fit:cover;border:1px solid var(--line);border-radius:8px;background:#000;display:block">
              </div>
              <input type="hidden" name="services[<?= $i ?>][img]" id="svcurl-<?= $i ?>" value="<?= e($s['img']) ?>">
              <input type="file" accept="image/*" data-target="svc" data-index="<?= $i ?>" class="inp" style="margin-top:6px;font-size:11px;padding:6px">
            </div>
            <div>
              <label class="lab">Number label</label>
              <input type="text" name="services[<?= $i ?>][num]" value="<?= e($s['num']) ?>" class="inp">
              <label class="lab" style="margin-top:10px">Title (HTML allowed: &lt;br&gt;, &amp;)</label>
              <input type="text" name="services[<?= $i ?>][title]" value="<?= e($s['title']) ?>" class="inp">
              <label class="lab" style="margin-top:10px">Tags (comma separated)</label>
              <input type="text" name="services[<?= $i ?>][tags]" value="<?= e(implode(', ', $s['tags'] ?? [])) ?>" class="inp">
            </div>
            <div>
              <label class="lab">Description</label>
              <textarea name="services[<?= $i ?>][desc]" class="inp" rows="6"><?= e($s['desc']) ?></textarea>
            </div>
          </div>
        </fieldset>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Team -->
  <div class="panel" style="margin-bottom:24px">
    <div class="panel-head">/ Team</div>
    <div style="padding:20px;display:flex;flex-direction:column;gap:12px">
      <?php for ($i = 0; $i < max(3, count($c['team']) + 1); $i++):
        $m = $c['team'][$i] ?? ['name'=>'','role'=>'','glyph'=>''];
      ?>
        <div style="display:grid;grid-template-columns:1fr 1fr 90px;gap:10px">
          <input type="text" name="team[<?= $i ?>][name]"  value="<?= e($m['name'])  ?>" placeholder="Name" class="inp">
          <input type="text" name="team[<?= $i ?>][role]"  value="<?= e($m['role'])  ?>" placeholder="Role" class="inp">
          <input type="text" name="team[<?= $i ?>][glyph]" value="<?= e($m['glyph']) ?>" placeholder="XX" maxlength="4" class="inp">
        </div>
      <?php endfor; ?>
    </div>
  </div>

  <!-- FAQ -->
  <div class="panel" style="margin-bottom:24px">
    <div class="panel-head">/ FAQ</div>
    <div style="padding:20px;display:flex;flex-direction:column;gap:14px">
      <?php for ($i = 0; $i < max(6, count($c['faq']) + 1); $i++):
        $f = $c['faq'][$i] ?? ['q'=>'','a'=>''];
      ?>
        <div>
          <input type="text" name="faq[<?= $i ?>][q]" value="<?= e($f['q']) ?>" placeholder="Question (blank to remove)" class="inp" style="margin-bottom:6px">
          <textarea name="faq[<?= $i ?>][a]" class="inp" rows="3" placeholder="Answer"><?= e($f['a']) ?></textarea>
        </div>
      <?php endfor; ?>
    </div>
  </div>

  <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:8px">
    <button type="submit" class="btn btn-primary">Save changes</button>
  </div>
</form>

<style>
  .lab{display:block;font-family:var(--mono);font-size:10.5px;letter-spacing:.16em;text-transform:uppercase;color:var(--muted);margin-bottom:6px}
  .inp{width:100%;padding:10px 12px;background:rgba(0,0,0,.3);border:1px solid var(--line-2);border-radius:8px;color:#fff;font:inherit;outline:none;transition:border-color .2s;font-size:13px}
  .inp:focus{border-color:var(--accent)}
  textarea.inp{resize:vertical;min-height:60px;line-height:1.5}
  fieldset legend{color:var(--accent)}
</style>

<script>
  // Image upload — POST to /admin/upload.php, swap preview, set hidden URL field.
  document.querySelectorAll('input[type="file"][data-target="svc"]').forEach(inp => {
    inp.addEventListener('change', async () => {
      if (!inp.files || !inp.files[0]) return;
      const idx = inp.dataset.index;
      const fd = new FormData();
      fd.append('file', inp.files[0]);
      fd.append('csrf', '<?= e(csrf_token()) ?>');
      fd.append('purpose', 'service-thumb');
      try {
        const res = await fetch('/admin/upload.php', { method:'POST', body: fd, credentials:'include' });
        const json = await res.json();
        if (!json.ok) throw new Error(json.error || 'Upload failed');
        document.getElementById('svcimg-' + idx).src = json.url + '?t=' + Date.now();
        document.getElementById('svcurl-' + idx).value = json.url;
      } catch (err) {
        alert('Upload failed: ' + err.message);
      }
    });
  });
</script>

<?php admin_foot(); ?>
