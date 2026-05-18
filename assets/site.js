/* === Digital Harbor — site script === */
(function(){
  // Scroll reveal
  const io = new IntersectionObserver((entries)=>{
    entries.forEach(e=>{
      if(e.isIntersecting){ e.target.classList.add('in'); io.unobserve(e.target); }
    });
  },{threshold:0.12,rootMargin:'0px 0px -8% 0px'});
  document.querySelectorAll('.reveal,.reveal-stagger').forEach(el=>io.observe(el));

  // Magnetic buttons
  document.querySelectorAll('.magnet').forEach(el=>{
    el.addEventListener('mousemove',e=>{
      const r=el.getBoundingClientRect();
      const dx=(e.clientX-r.left-r.width/2);
      const dy=(e.clientY-r.top-r.height/2);
      el.style.transform=`translate(${dx*0.18}px,${dy*0.22}px)`;
    });
    el.addEventListener('mouseleave',()=>{el.style.transform=''});
  });

  // Service card mouse glow
  document.querySelectorAll('.svc').forEach(c=>{
    c.addEventListener('mousemove',e=>{
      const r=c.getBoundingClientRect();
      c.style.setProperty('--mx',(e.clientX-r.left)+'px');
      c.style.setProperty('--my',(e.clientY-r.top)+'px');
    });
  });

  // FAQ
  document.querySelectorAll('.faq-item').forEach(it=>{
    it.addEventListener('click',()=>{
      const open=it.classList.contains('open');
      // close siblings
      it.parentElement.querySelectorAll('.faq-item.open').forEach(s=>s.classList.remove('open'));
      if(!open) it.classList.add('open');
    });
  });

  // Counters
  function animateCounter(el){
    const target=parseFloat(el.dataset.to||el.textContent);
    const dur=1400;
    const decimals=parseInt(el.dataset.decimals||'0',10);
    const t0=performance.now();
    function tick(t){
      const p=Math.min(1,(t-t0)/dur);
      const eased=1-Math.pow(1-p,3);
      const v=target*eased;
      el.textContent=v.toFixed(decimals);
      if(p<1) requestAnimationFrame(tick);
      else el.textContent=target.toFixed(decimals);
    }
    requestAnimationFrame(tick);
  }
  const cio=new IntersectionObserver((entries)=>{
    entries.forEach(e=>{ if(e.isIntersecting){ animateCounter(e.target); cio.unobserve(e.target);} });
  },{threshold:0.4});
  document.querySelectorAll('.counter').forEach(el=>cio.observe(el));

  // Budget chips
  document.querySelectorAll('.budget-row').forEach(row=>{
    row.querySelectorAll('.budget-chip').forEach(chip=>{
      chip.addEventListener('click',()=>{
        row.querySelectorAll('.budget-chip').forEach(c=>c.classList.remove('active'));
        chip.classList.add('active');
      });
    });
  });

  // Marquee duplicate (so it scrolls infinitely)
  document.querySelectorAll('.marquee-track').forEach(t=>{
    if(t.dataset.dup) return; t.dataset.dup='1';
    t.innerHTML += t.innerHTML;
  });
})();

/* === Hardware sequence canvas — cinematic loop with seamless crossfade === */
window.HardwareSequence = function(opts){
  const canvas = opts.canvas;
  const total  = opts.total || 240;
  const path   = opts.path  || 'frames/';
  const fps    = opts.fps   || 30;
  const startDelayMs = opts.startDelayMs != null ? opts.startDelayMs : 1500;
  // Crossfade length in frames — last N frames blend into first N (seamless loop)
  const FADE   = opts.fadeFrames != null ? opts.fadeFrames : 28;
  // Crop bottom strip of source (hides AI-tool watermark on raw frames)
  const CROP_BOTTOM = opts.cropBottom != null ? opts.cropBottom : 0.085;

  const ctx = canvas.getContext('2d', {alpha:true, desynchronized:true});
  const dpr = Math.min(3, window.devicePixelRatio||1);
  const useBitmap = typeof createImageBitmap === 'function';
  const imgs = new Array(total);

  let loaded = 0;
  let ready = false;
  let lastDrawn = -1;
  let current = 0;
  let acc = 0;
  let lastT = 0;
  let speed = 1;
  let running = true;
  let firstPainted = false;
  const t0 = performance.now();

  function pad(n){return String(n).padStart(3,'0')}

  function resize(){
    const r = canvas.getBoundingClientRect();
    canvas.width  = Math.round(r.width*dpr);
    canvas.height = Math.round(r.height*dpr);
    ctx.imageSmoothingEnabled = true;
    ctx.imageSmoothingQuality = 'high';
    if(lastDrawn >= 0) drawIdx(lastDrawn);
  }
  resize();
  new ResizeObserver(resize).observe(canvas);

  function isLoaded(i){
    const b = imgs[i];
    if(!b) return false;
    if(useBitmap) return b instanceof ImageBitmap;
    return b.complete && b.naturalWidth > 0;
  }

  // Draw a single frame with cover-fit and watermark crop. Optional alpha for crossfade.
  function drawSingle(i, alpha){
    const img = imgs[i];
    if(!img) return;
    const w = canvas.width, h = canvas.height;
    const iw = useBitmap ? img.width  : img.naturalWidth;
    const ih = useBitmap ? img.height : img.naturalHeight;
    const srcW = iw;
    const srcH = ih * (1 - CROP_BOTTOM);
    const ir = srcW / srcH;
    const cr = w / h;
    let dw, dh, dx, dy;
    if(ir > cr){ dh=h; dw=h*ir; dx=(w-dw)/2; dy=0; }
    else       { dw=w; dh=w/ir; dx=0; dy=(h-dh)/2; }
    if(alpha < 1) ctx.globalAlpha = alpha;
    ctx.drawImage(img, 0, 0, srcW, srcH, dx, dy, dw, dh);
    if(alpha < 1) ctx.globalAlpha = 1;
  }

  // Compose: current frame, plus crossfade with head when in the tail region
  function drawIdx(i){
    if(!isLoaded(i)) return;
    const w = canvas.width, h = canvas.height;
    ctx.clearRect(0,0,w,h);
    ctx.imageSmoothingEnabled = true;
    ctx.imageSmoothingQuality = 'high';

    drawSingle(i, 1);

    // Crossfade tail-to-head — seam becomes invisible
    if(i >= total - FADE){
      const t = (i - (total - FADE)) / FADE;     // 0 → 1 across the fade
      const eased = t*t*(3 - 2*t);                // smoothstep
      const headIdx = Math.min(total-1, Math.floor(t * (FADE-1)));
      if(isLoaded(headIdx)) drawSingle(headIdx, eased);
    }

    lastDrawn = i;

    if(!firstPainted){
      firstPainted = true;
      const host = canvas.parentElement;
      if(host) host.classList.add('ready');
      if(opts.onFirstPaint) opts.onFirstPaint();
    }
  }

  function loop(t){
    if(!lastT) lastT = t;
    const dt = Math.min(t - lastT, 100);
    lastT = t;
    if(!ready && (t - t0) >= startDelayMs && isLoaded(0)){
      ready = true;
      if(opts.onStart) opts.onStart();
    }
    if(running && ready){
      acc += dt * speed;
      const frameMs = 1000 / fps;
      while(acc >= frameMs){
        current = (current + 1) % total;
        acc -= frameMs;
      }
      if(isLoaded(current)) drawIdx(current);
    }
    requestAnimationFrame(loop);
  }

  function loadOne(i){
    return new Promise(resolve=>{
      const url = path + pad(i+1) + '.jpg';
      if(useBitmap){
        fetch(url)
          .then(r=>r.blob())
          .then(b=>createImageBitmap(b,{resizeQuality:'high'}))
          .then(bmp=>{ imgs[i]=bmp; loaded++; if(i===0 && lastDrawn<0) drawIdx(0); resolve(); })
          .catch(()=>{ loaded++; resolve(); });
      } else {
        const im = new Image();
        im.decoding = 'async';
        im.loading  = 'eager';
        im.onload = async ()=>{
          try { if(im.decode) await im.decode(); } catch(e){}
          imgs[i]=im; loaded++;
          if(i===0 && lastDrawn<0) drawIdx(0);
          resolve();
        };
        im.onerror = ()=>{ loaded++; resolve(); };
        im.src = url;
      }
    });
  }

  async function preloadStream(){
    await loadOne(0);

    // Burst: first 90 frames AND last FADE frames (needed for crossfade)
    const burst = [];
    for(let i=1; i<Math.min(91, total); i++) burst.push(loadOne(i));
    for(let i=Math.max(91, total-FADE); i<total; i++) burst.push(loadOne(i));
    await Promise.all(burst);

    // Stream the middle in parallel
    const batchSize = 40;
    const pending = [];
    for(let i=91; i<total-FADE; i++){
      if(!imgs[i]) pending.push(loadOne(i));
    }
    for(let i=0;i<pending.length;i+=batchSize){
      Promise.all(pending.slice(i,i+batchSize));
    }
    Promise.all(pending).then(()=>{ if(opts.onReady) opts.onReady(); });
  }
  preloadStream();

  requestAnimationFrame(loop);

  return {
    setSpeed:(s)=>{ speed = Math.max(0.1, s) },
    pause:()=>{ running=false },
    play:()=>{ running=true },
    isReady:()=>ready,
    framesLoaded:()=>loaded,
  };
};

/* === Object section circuit canvas (lightweight stylized board) === */
window.CircuitCanvas = function(canvas){
  const ctx = canvas.getContext('2d');
  const dpr = Math.min(2, window.devicePixelRatio||1);
  let w=0, h=0;
  function resize(){
    const r=canvas.getBoundingClientRect();
    w=canvas.width=Math.round(r.width*dpr);
    h=canvas.height=Math.round(r.height*dpr);
  }
  resize(); new ResizeObserver(resize).observe(canvas);

  const nodes=[];
  const cols=12, rows=12;
  for(let i=0;i<cols;i++)for(let j=0;j<rows;j++){
    nodes.push({x:i,y:j,phase:Math.random()*Math.PI*2});
  }
  // signals travel along axes
  const sigs=[];
  function addSig(){
    const horiz=Math.random()<0.5;
    sigs.push({
      h:horiz, x:Math.floor(Math.random()*cols), y:Math.floor(Math.random()*rows),
      pos:0, len:3+Math.random()*4, speed:0.06+Math.random()*0.05,
      color:Math.random()<0.5?'cyan':'purple'
    });
  }
  for(let i=0;i<14;i++) addSig();

  function frame(t){
    ctx.clearRect(0,0,w,h);
    const px = w/cols, py = h/rows;
    const off = px/2;
    // grid lines
    ctx.strokeStyle='rgba(255,255,255,0.04)';
    ctx.lineWidth=1*dpr;
    for(let i=0;i<=cols;i++){ ctx.beginPath(); ctx.moveTo(i*px,0); ctx.lineTo(i*px,h); ctx.stroke(); }
    for(let j=0;j<=rows;j++){ ctx.beginPath(); ctx.moveTo(0,j*py); ctx.lineTo(w,j*py); ctx.stroke(); }
    // nodes
    nodes.forEach(n=>{
      const a = 0.25 + 0.25*Math.sin(t*0.001+n.phase);
      ctx.fillStyle = `rgba(180,200,220,${a})`;
      ctx.beginPath(); ctx.arc(n.x*px+off, n.y*py+off, 1.4*dpr, 0, Math.PI*2); ctx.fill();
    });
    // signals
    sigs.forEach(s=>{
      s.pos += s.speed;
      const len=s.len;
      const start=s.pos-len, end=s.pos;
      const colA=s.color==='cyan'?'rgba(0,229,255,':'rgba(157,92,255,';
      for(let k=0;k<len*4;k++){
        const p = start + (k/(len*4))*len;
        if(s.h){
          const x = (p)%cols;
          if(x<0||x>cols) continue;
          const cx = x*px+off, cy = s.y*py+off;
          const a = 1 - Math.abs((p-end+len/2)/(len/2));
          if(a<=0) continue;
          ctx.fillStyle = colA + (a*0.9)+')';
          ctx.beginPath(); ctx.arc(cx,cy,1.6*dpr,0,Math.PI*2); ctx.fill();
        } else {
          const y = (p)%rows;
          if(y<0||y>rows) continue;
          const cx = s.x*px+off, cy = y*py+off;
          const a = 1 - Math.abs((p-end+len/2)/(len/2));
          if(a<=0) continue;
          ctx.fillStyle = colA + (a*0.9)+')';
          ctx.beginPath(); ctx.arc(cx,cy,1.6*dpr,0,Math.PI*2); ctx.fill();
        }
      }
      if(s.pos > cols+len){ s.pos=-len; s.x=Math.floor(Math.random()*cols); s.y=Math.floor(Math.random()*rows); s.h=Math.random()<0.5; }
    });
    // central glow
    const g = ctx.createRadialGradient(w/2,h/2,0,w/2,h/2,Math.min(w,h)/2);
    g.addColorStop(0,'rgba(0,229,255,0.18)');
    g.addColorStop(0.4,'rgba(157,92,255,0.10)');
    g.addColorStop(1,'rgba(0,0,0,0)');
    ctx.fillStyle=g;
    ctx.fillRect(0,0,w,h);

    requestAnimationFrame(frame);
  }
  requestAnimationFrame(frame);
};
