/* === Smooth momentum scroll (Lenis). Tuned for pronounced inertia. === */
(function(){
  if (typeof Lenis === 'undefined') return;

  // Respect reduced-motion preference — don't intercept native scroll
  if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

  const isTouch = window.matchMedia('(pointer: coarse)').matches;

  const lenis = new Lenis({
    // Sub-frame interpolation factor — lower = smoother (more frames to reach target)
    lerp: 0.075,
    // Long tail after wheel stops gives that webdorks feel
    duration: 1.6,
    easing: t => 1 - Math.pow(1 - t, 4),  // ease-out quartic
    smoothWheel: true,
    // Enable inertia on touch (Lenis takes over native momentum)
    syncTouch: isTouch,
    touchInertiaMultiplier: 35,           // strength of touch flick momentum
    touchMultiplier: 1.6,
    wheelMultiplier: 1.0,
    infinite: false,
    autoResize: true,
  });

  // Drive RAF loop
  function raf(time){ lenis.raf(time); requestAnimationFrame(raf); }
  requestAnimationFrame(raf);

  // Anchor links — let Lenis handle in-page nav so the scroll has inertia too
  document.addEventListener('click', (e) => {
    const a = e.target.closest('a[href^="#"]');
    if (!a) return;
    const id = a.getAttribute('href');
    if (id.length < 2) return;
    const tgt = document.querySelector(id);
    if (!tgt) return;
    e.preventDefault();
    lenis.scrollTo(tgt, { offset: -40, duration: 1.4, easing: t => 1 - Math.pow(1 - t, 3) });
  });

  // Pause smooth scroll when user opens an iframe-form / dropdown that needs native focus
  window.__lenis = lenis;
})();
