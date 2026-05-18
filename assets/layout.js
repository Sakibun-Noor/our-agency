/* === Layout: nav + footer injection, theme variables, tweaks panel === */
(function(){
  const PAGES = [
    {id:'work',label:'Work',href:'work.html'},
    {id:'services',label:'Services',href:'services.html'},
    {id:'about',label:'About',href:'about.html'},
    {id:'contact',label:'Contact',href:'contact.html'},
  ];

  const root = document.documentElement;
  const body = document.body;
  const page = body.dataset.page || 'home';
  const baseHref = body.dataset.base || ''; // for nested pages: '../'

  /* === Theme (dark / light) === */
  const THEME_KEY = 'dh_theme';
  function getInitialTheme(){
    try{
      const saved = localStorage.getItem(THEME_KEY);
      if(saved==='light'||saved==='dark') return saved;
    }catch(e){}
    if(window.matchMedia && window.matchMedia('(prefers-color-scheme: light)').matches) return 'light';
    return 'dark';
  }
  function setTheme(t){
    root.dataset.theme = t;
    try{ localStorage.setItem(THEME_KEY, t); }catch(e){}
    document.querySelectorAll('.nav-theme').forEach(b=>b.setAttribute('aria-label', t==='dark'?'Switch to light theme':'Switch to dark theme'));
    if(typeof window.__applyTheme === 'function' && window.__tweaks) window.__applyTheme(window.__tweaks);
  }
  setTheme(getInitialTheme());

  /* === Inject NAV === */
  const navMount = document.querySelector('[data-mount="nav"]');
  if(navMount){
    navMount.innerHTML = `
      <nav class="nav" aria-label="Primary">
        <div class="nav-inner">
          <a href="${baseHref}index.html" class="nav-logo magnet">
            <span class="mark"></span>
            <span>Digital Harbor</span>
          </a>
          <div class="nav-links" id="navLinks">
            ${PAGES.map(p=>`<a href="${baseHref}${p.href}" class="${p.id===page?'active':''}">${p.label}</a>`).join('')}
          </div>
          <button class="nav-theme" type="button" aria-label="Toggle theme">
            <svg class="ico-sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"/></svg>
            <svg class="ico-moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
          </button>
          <a href="${baseHref}contact.html" class="nav-cta magnet">
            <span>Start a project</span> <span class="arrow">→</span>
          </a>
          <button class="nav-burger" type="button" aria-label="Menu" aria-expanded="false">
            <span></span><span></span><span></span>
          </button>
        </div>
      </nav>`;

    const themeBtn = navMount.querySelector('.nav-theme');
    if(themeBtn){
      themeBtn.addEventListener('click', ()=>{
        setTheme(root.dataset.theme==='light'?'dark':'light');
      });
    }
    const burger = navMount.querySelector('.nav-burger');
    const links = navMount.querySelector('#navLinks');
    if(burger && links){
      burger.addEventListener('click', ()=>{
        const open = links.classList.toggle('open');
        burger.classList.toggle('open', open);
        burger.setAttribute('aria-expanded', open);
      });
      links.querySelectorAll('a').forEach(a=>a.addEventListener('click',()=>{
        links.classList.remove('open');burger.classList.remove('open');
        burger.setAttribute('aria-expanded','false');
      }));
    }
  }

  /* === Inject FOOTER === */
  const footMount = document.querySelector('[data-mount="footer"]');
  if(footMount){
    footMount.innerHTML = `
      <footer class="footer" data-screen-label="footer">
        <div class="shell">
          <div class="reveal">
            <div class="eyebrow" style="margin-bottom:32px">[ Ready / standby ]</div>
            <h2 class="foot-headline">Build the<br><em>operating system</em><br>for your brand.</h2>
          </div>
          <div class="foot-row reveal">
            <a href="${baseHref}contact.html" class="btn btn-primary magnet" style="font-size:15px;padding:18px 28px">
              Start a project <span class="arrow">→</span>
            </a>
            <form class="foot-form" onsubmit="event.preventDefault();this.querySelector('button').textContent='Sent ✓'">
              <input type="email" placeholder="hello@yourcompany.com" required>
              <button type="submit">Get the deck <span>→</span></button>
            </form>
          </div>
          <div class="foot-cols">
            <div>
              <h5>Studio</h5>
              <p style="font-family:var(--display);font-weight:300;font-size:22px;letter-spacing:-0.02em;line-height:1.2;color:var(--link-text);margin-top:-4px;max-width:32ch">
                A small senior studio building digital systems — strategy, brand, product, growth.
              </p>
            </div>
            <div>
              <h5>Sitemap</h5>
              <ul>
                <li><a href="${baseHref}index.html">Home</a></li>
                <li><a href="${baseHref}work.html">Selected work</a></li>
                <li><a href="${baseHref}services.html">Services</a></li>
                <li><a href="${baseHref}about.html">About</a></li>
                <li><a href="${baseHref}contact.html">Contact</a></li>
              </ul>
            </div>
            <div>
              <h5>Contact</h5>
              <ul>
                <li><a href="mailto:hello@digitalharbor.studio">hello@digitalharbor.studio</a></li>
                <li><a href="#">+1 (415) 555-0136</a></li>
                <li><a href="#">3rd &amp; Brannan, San Francisco</a></li>
              </ul>
            </div>
            <div>
              <h5>Channels</h5>
              <ul>
                <li><a href="#">LinkedIn ↗</a></li>
                <li><a href="#">Twitter / X ↗</a></li>
                <li><a href="#">Dribbble ↗</a></li>
                <li><a href="#">Read.cv ↗</a></li>
              </ul>
            </div>
          </div>
          <div class="foot-bottom">
            <div>© 2025 Digital Harbor — All systems operational</div>
            <div class="stat"><span class="dot"></span> 04 active engagements · Q3'25</div>
            <div>v.4.2 / build 0240</div>
          </div>
        </div>
      </footer>`;
  }

  /* === Apply theme from localStorage (cross-page sync) === */
  const ACCENTS = {
    cyan:    { dark:{a:'#00E5FF',b:'#9D5CFF'}, light:{a:'#0099B8',b:'#6B3DC9'} },
    purple:  { dark:{a:'#9D5CFF',b:'#00E5FF'}, light:{a:'#6B3DC9',b:'#0099B8'} },
    blue:    { dark:{a:'#3B82F6',b:'#00E5FF'}, light:{a:'#1D4ED8',b:'#0099B8'} },
    maroon:  { dark:{a:'#3B82F6',b:'#00E5FF'}, light:{a:'#1D4ED8',b:'#0099B8'} },
    lime:    { dark:{a:'#B6FF3C',b:'#00E5FF'}, light:{a:'#5C8F1A',b:'#0099B8'} },
  };
  function applyTheme(t){
    const cfg = ACCENTS[t.accent] || ACCENTS.cyan;
    const mode = root.dataset.theme === 'light' ? 'light' : 'dark';
    const acc = cfg[mode];
    root.style.setProperty('--accent', acc.a);
    root.style.setProperty('--accent-2', acc.b);
    root.style.setProperty('--cyan', acc.a);
    root.style.setProperty('--purple', acc.b);
    root.dataset.hwpos = t.hwPosition || 'right';
    window.__hwSpeed = parseFloat(t.hwSpeed || 1);
    if(window.__hwInstance) window.__hwInstance.setSpeed(window.__hwSpeed);
  }
  function readDefaults(){
    // pull EDITMODE block JSON if present
    const scripts = document.querySelectorAll('script');
    for(const s of scripts){
      const m = (s.textContent||'').match(/\/\*EDITMODE-BEGIN\*\/([\s\S]*?)\/\*EDITMODE-END\*\//);
      if(m){ try{ return JSON.parse(m[1]); }catch(e){} }
    }
    return {accent:'cyan',hwPosition:'right',hwSpeed:1};
  }
  let tweaks = readDefaults();
  // localStorage overrides defaults so changes persist across pages within a session
  try{
    const saved = JSON.parse(localStorage.getItem('dh_tweaks')||'null');
    if(saved) tweaks = Object.assign({}, tweaks, saved);
  }catch(e){}
  applyTheme(tweaks);
  window.__tweaks = tweaks;
  window.__applyTheme = applyTheme;

  function setTweak(k,v){
    tweaks[k]=v;
    localStorage.setItem('dh_tweaks', JSON.stringify(tweaks));
    applyTheme(tweaks);
    // persist to file via host
    const edits = {}; edits[k]=v;
    try{ window.parent.postMessage({type:'__edit_mode_set_keys', edits}, '*'); }catch(e){}
  }

  /* === Tweaks panel === */
  const PANEL_HTML = `
    <div class="tweaks-panel" role="dialog" aria-label="Tweaks">
      <div class="tw-head">
        <span class="tw-dot"></span>
        <span class="tw-title">Tweaks</span>
        <button class="tw-close" aria-label="Close">×</button>
      </div>
      <div class="tw-body">
        <div class="tw-section">
          <div class="tw-label">Accent color</div>
          <div class="tw-swatches" data-key="accent">
            ${Object.entries(ACCENTS).map(([k,v])=>`<button class="tw-swatch" data-val="${k}" style="background:linear-gradient(135deg,${v.a},${v.b})" aria-label="${k}"><span class="tw-swatch-name">${k}</span></button>`).join('')}
          </div>
        </div>
        <div class="tw-section">
          <div class="tw-label">Hardware position</div>
          <div class="tw-segment" data-key="hwPosition">
            <button data-val="left">Left</button>
            <button data-val="center">Center</button>
            <button data-val="right">Right</button>
          </div>
        </div>
        <div class="tw-section">
          <div class="tw-label">Hardware playback speed <span class="tw-val" data-show="hwSpeed">1.0×</span></div>
          <input type="range" min="0.25" max="3" step="0.05" data-key="hwSpeed" class="tw-range">
        </div>
        <div class="tw-foot">
          <span class="tw-mono">/// Tweaks sync across pages via localStorage</span>
        </div>
      </div>
    </div>`;

  const PANEL_CSS = `
    .tweaks-panel{position:fixed;right:18px;bottom:18px;z-index:200;width:320px;
      background:rgba(8,9,12,0.92);backdrop-filter:blur(18px);
      border:1px solid var(--line-2);border-radius:18px;color:#fff;font-family:var(--body);
      box-shadow:0 30px 60px -10px rgba(0,0,0,.7), 0 0 0 1px rgba(255,255,255,.03) inset;
      transform:translateY(20px);opacity:0;pointer-events:none;
      transition:transform .35s cubic-bezier(.2,.7,.2,1),opacity .35s;}
    .tweaks-panel.open{transform:none;opacity:1;pointer-events:auto}
    .tw-head{display:flex;align-items:center;gap:10px;padding:14px 16px;border-bottom:1px solid var(--line)}
    .tw-dot{width:8px;height:8px;border-radius:50%;background:var(--accent);box-shadow:0 0 10px var(--accent)}
    .tw-title{font-family:var(--mono);font-size:11px;letter-spacing:.18em;text-transform:uppercase;color:#cfd2d8;flex:1}
    .tw-close{margin-left:auto;background:transparent;border:1px solid var(--line-2);color:#cfd2d8;width:26px;height:26px;border-radius:50%;cursor:pointer;font-size:16px;line-height:1;display:grid;place-items:center}
    .tw-close:hover{background:#fff;color:#000}
    .tw-body{padding:16px}
    .tw-section{padding:12px 0;border-bottom:1px dashed var(--line)}
    .tw-section:last-of-type{border-bottom:none}
    .tw-label{font-family:var(--mono);font-size:10.5px;letter-spacing:.16em;text-transform:uppercase;color:var(--muted);margin-bottom:12px;display:flex;justify-content:space-between;align-items:center}
    .tw-val{color:#fff;font-size:11px}
    .tw-swatches{display:grid;grid-template-columns:repeat(4,1fr);gap:8px}
    .tw-swatch{position:relative;height:46px;border-radius:10px;border:1px solid var(--line-2);cursor:pointer;background:#222;overflow:hidden}
    .tw-swatch.active{border-color:#fff;box-shadow:0 0 0 1px #fff}
    .tw-swatch-name{position:absolute;left:6px;bottom:5px;font-family:var(--mono);font-size:9px;letter-spacing:.06em;text-transform:uppercase;color:#fff;text-shadow:0 1px 2px rgba(0,0,0,.6);opacity:.9}
    .tw-segment{display:grid;grid-template-columns:repeat(3,1fr);gap:0;border:1px solid var(--line-2);border-radius:10px;overflow:hidden;background:rgba(0,0,0,.3)}
    .tw-segment button{padding:10px 0;background:transparent;color:#cfd2d8;border:none;cursor:pointer;font-family:var(--body);font-size:13px;border-right:1px solid var(--line)}
    .tw-segment button:last-child{border-right:none}
    .tw-segment button.active{background:#fff;color:#000}
    .tw-range{width:100%;-webkit-appearance:none;appearance:none;height:4px;background:var(--line-2);border-radius:4px;outline:none}
    .tw-range::-webkit-slider-thumb{-webkit-appearance:none;width:16px;height:16px;border-radius:50%;background:#fff;cursor:pointer;box-shadow:0 0 0 3px rgba(0,229,255,.25)}
    .tw-range::-moz-range-thumb{width:16px;height:16px;border-radius:50%;background:#fff;cursor:pointer;border:none;box-shadow:0 0 0 3px rgba(0,229,255,.25)}
    .tw-foot{margin-top:8px;padding-top:12px;border-top:1px solid var(--line)}
    .tw-mono{font-family:var(--mono);font-size:10px;color:var(--muted);letter-spacing:.06em}
    @media(max-width:520px){.tweaks-panel{left:8px;right:8px;width:auto;bottom:8px}}`;

  const styleEl = document.createElement('style');
  styleEl.textContent = PANEL_CSS;
  document.head.appendChild(styleEl);

  const wrap = document.createElement('div');
  wrap.innerHTML = PANEL_HTML;
  const panel = wrap.firstElementChild;
  document.body.appendChild(panel);

  function syncUI(){
    panel.querySelectorAll('[data-key="accent"] .tw-swatch').forEach(b=>b.classList.toggle('active', b.dataset.val===tweaks.accent));
    panel.querySelectorAll('[data-key="hwPosition"] button').forEach(b=>b.classList.toggle('active', b.dataset.val===tweaks.hwPosition));
    const r = panel.querySelector('[data-key="hwSpeed"]');
    if(r) r.value = tweaks.hwSpeed;
    const lab = panel.querySelector('[data-show="hwSpeed"]');
    if(lab) lab.textContent = (parseFloat(tweaks.hwSpeed)).toFixed(2)+'×';
  }
  syncUI();

  panel.querySelectorAll('.tw-swatch').forEach(b=>b.addEventListener('click',()=>{ setTweak('accent',b.dataset.val); syncUI(); }));
  panel.querySelectorAll('[data-key="hwPosition"] button').forEach(b=>b.addEventListener('click',()=>{ setTweak('hwPosition',b.dataset.val); syncUI(); }));
  panel.querySelector('[data-key="hwSpeed"]').addEventListener('input', e=>{
    setTweak('hwSpeed', parseFloat(e.target.value));
    syncUI();
  });

  /* === Host protocol === */
  function show(){ panel.classList.add('open'); }
  function hide(){ panel.classList.remove('open'); try{ window.parent.postMessage({type:'__edit_mode_dismissed'},'*'); }catch(e){} }
  panel.querySelector('.tw-close').addEventListener('click', hide);

  window.addEventListener('message', e=>{
    if(!e || !e.data) return;
    if(e.data.type==='__activate_edit_mode') show();
    if(e.data.type==='__deactivate_edit_mode') hide();
  });
  try{ window.parent.postMessage({type:'__edit_mode_available'}, '*'); }catch(e){}
})();
