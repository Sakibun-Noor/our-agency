<?php
// Embedded contact form. Lives at /form.php on InfinityFree, iframed by Vercel.
// Same-origin POST to /api/submit.php → cookies travel, JS challenge bypassed.

$sent = isset($_GET['sent']) && $_GET['sent'] === '1';
$err  = isset($_GET['err'])  && $_GET['err']  === '1';
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Brief</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
<style>
  :root{
    --fg:#f5f6f8;--muted:#8a8f99;--line:rgba(255,255,255,0.10);--line-2:rgba(255,255,255,0.18);
    --accent:#00E5FF;--body:'Inter',ui-sans-serif,system-ui,sans-serif;
    --mono:'JetBrains Mono',ui-monospace,Menlo,monospace;
  }
  *{box-sizing:border-box;margin:0;padding:0}
  html,body{background:transparent;color:var(--fg);font-family:var(--body);font-size:14px;-webkit-font-smoothing:antialiased}
  body{padding:0;line-height:1.5;letter-spacing:-0.01em;overflow-x:hidden}
  .form{display:flex;flex-direction:column;gap:22px}
  .field{display:flex;flex-direction:column;gap:8px}
  label{font-family:var(--mono);font-size:10.5px;letter-spacing:.18em;text-transform:uppercase;color:var(--muted)}
  input,textarea,select{
    background:transparent;border:none;border-bottom:1px solid var(--line-2);
    color:var(--fg);font:inherit;font-size:16px;padding:10px 0;outline:none;
    transition:border-color .25s;width:100%;
  }
  input:focus,textarea:focus,select:focus{border-color:var(--accent)}
  textarea{min-height:100px;resize:vertical}
  select{appearance:none;background-image:url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='12' height='8'><path d='M1 1l5 5 5-5' stroke='%23888' fill='none' stroke-width='1.5'/></svg>");background-repeat:no-repeat;background-position:right 4px center;padding-right:24px}
  select option{background:#0a0b0e}
  .chips{display:flex;gap:8px;flex-wrap:wrap}
  .chip{padding:8px 14px;border:1px solid var(--line-2);border-radius:999px;font-family:var(--mono);font-size:11px;letter-spacing:.14em;color:#cfd2d8;cursor:pointer;text-transform:uppercase;background:transparent;transition:all .2s}
  .chip:hover{border-color:#fff}
  .chip.active{background:var(--accent);color:#000;border-color:var(--accent)}
  .btn{
    display:inline-flex;align-items:center;gap:10px;padding:14px 22px;border-radius:999px;
    font-size:14px;font-weight:500;border:1px solid #fff;background:#fff;color:#000;
    cursor:pointer;transition:box-shadow .25s;font-family:var(--body);align-self:flex-start;
  }
  .btn:hover{box-shadow:0 0 0 1px var(--accent),0 0 40px -4px var(--accent)}
  .btn:disabled{opacity:.6;cursor:not-allowed}
  .ok{padding:14px 18px;border-radius:10px;font-family:var(--mono);font-size:12px;letter-spacing:.14em;background:rgba(34,197,94,.12);border:1px solid rgba(34,197,94,.40);color:#86efac}
  .err{padding:14px 18px;border-radius:10px;font-family:var(--mono);font-size:12px;letter-spacing:.14em;background:rgba(255,77,109,.12);border:1px solid rgba(255,77,109,.40);color:#ffaab9}
  .hp{position:absolute;left:-9999px;opacity:0;pointer-events:none}

  /* Light theme — body class set by parent via postMessage or query */
  body.light{
    --fg:#0F0F10;--muted:#6E665B;
    --line:rgba(15,15,16,0.10);--line-2:rgba(15,15,16,0.20);
    --accent:#1D4ED8;
  }
  body.light select option{background:#fff;color:#000}
</style>
</head>
<body>
<?php if ($sent): ?>
  <div class="ok">✓ SENT — WE'LL BE IN TOUCH WITHIN ONE BUSINESS DAY</div>
  <p style="margin-top:18px;color:var(--muted);font-size:13px">Send another? <a href="form.php" style="color:var(--accent)">Reset form →</a></p>
<?php else: ?>
  <?php if ($err): ?><div class="err" style="margin-bottom:18px">FAILED — TRY AGAIN OR EMAIL DIRECTLY</div><?php endif; ?>
  <form class="form" method="POST" action="/api/submit.php">
    <div class="field">
      <label for="name">/ Name</label>
      <input type="text" id="name" name="name" placeholder="Your name" required>
    </div>
    <div class="field">
      <label for="email">/ Email</label>
      <input type="email" id="email" name="email" placeholder="you@company.com" required>
    </div>
    <div class="field">
      <label for="company">/ Company</label>
      <input type="text" id="company" name="company" placeholder="Company name">
    </div>
    <div class="field">
      <label>/ Budget</label>
      <div class="chips" id="budgetRow">
        <button type="button" class="chip" data-val="40-90">$40–90K</button>
        <button type="button" class="chip active" data-val="90-180">$90–180K</button>
        <button type="button" class="chip" data-val="180-400">$180–400K</button>
        <button type="button" class="chip" data-val="400+">$400K+</button>
      </div>
      <input type="hidden" name="budget" id="budgetVal" value="90-180">
    </div>
    <div class="field">
      <label for="scope">/ Scope</label>
      <select id="scope" name="scope">
        <option value="brand">Brand &amp; identity</option>
        <option value="product">Product &amp; UX</option>
        <option value="engineering">Engineering build</option>
        <option value="growth">Growth &amp; CRO</option>
        <option value="retainer">Quarterly retainer</option>
        <option value="other">Other / unsure</option>
      </select>
    </div>
    <div class="field">
      <label for="message">/ Brief</label>
      <textarea id="message" name="message" placeholder="What are you building? What's the deadline? What's keeping you up?" required></textarea>
    </div>
    <input type="text" name="website" tabindex="-1" autocomplete="off" class="hp" aria-hidden="true">
    <input type="hidden" name="from_iframe" value="1">
    <button type="submit" class="btn">Send brief →</button>
  </form>
<?php endif; ?>

<script>
  // Theme sync via postMessage from Vercel parent
  window.addEventListener('message', (e) => {
    if (e.data && e.data.theme === 'light') document.body.classList.add('light');
    else document.body.classList.remove('light');
  });
  // Theme via query string fallback
  if (new URLSearchParams(location.search).get('theme') === 'light') document.body.classList.add('light');

  // Auto-resize parent on content change
  function resize(){
    const h = document.documentElement.scrollHeight;
    parent.postMessage({ type:'iframe-height', height: h }, '*');
  }
  resize();
  new ResizeObserver(resize).observe(document.body);

  // Budget chip toggling
  document.querySelectorAll('#budgetRow .chip').forEach(c => {
    c.addEventListener('click', () => {
      document.querySelectorAll('#budgetRow .chip').forEach(x => x.classList.remove('active'));
      c.classList.add('active');
      document.getElementById('budgetVal').value = c.dataset.val;
    });
  });

  // After successful submit, tell parent
  <?php if ($sent): ?>parent.postMessage({ type:'form-sent' }, '*');<?php endif; ?>
</script>
</body>
</html>
