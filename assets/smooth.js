/* === Smooth momentum scroll (Lenis). === */
(function(){
  if (typeof Lenis === 'undefined') {
    console.warn('[smooth] Lenis not loaded');
    return;
  }

  const isTouch = window.matchMedia('(pointer: coarse)').matches;

  // Use lerp-only mode (continuous interpolation, no fixed duration).
  // 0.06 = pronounced inertia; 0.1 = snappier; 0.04 = very floaty.
  const lenis = new Lenis({
    lerp: 0.06,
    smoothWheel: true,
    syncTouch: isTouch,
    touchMultiplier: 1.8,
    wheelMultiplier: 1.0,
    infinite: false,
    autoResize: true,
  });

  function raf(time){ lenis.raf(time); requestAnimationFrame(raf); }
  requestAnimationFrame(raf);

  // Expose for debug + anchor usage
  window.__lenis = lenis;
  console.log('[smooth] Lenis active', lenis);

  // Smooth in-page anchor links
  document.addEventListener('click', (e) => {
    const a = e.target.closest('a[href^="#"]');
    if (!a) return;
    const id = a.getAttribute('href');
    if (id.length < 2) return;
    const tgt = document.querySelector(id);
    if (!tgt) return;
    e.preventDefault();
    lenis.scrollTo(tgt, { offset: -40, duration: 1.2 });
  });
})();
