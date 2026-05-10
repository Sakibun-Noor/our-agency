/* Content hydrator — fetches /api/content.php from admin host and patches DOM. */
(function(){
  const API = 'https://myagency.page.gd/api/content.php';

  function setText(el, txt){ if (el && txt != null) el.textContent = txt; }
  function setHTML(el, html){ if (el && html != null) el.innerHTML = html; }

  async function load(){
    try {
      const res = await fetch(API, { credentials: 'omit' });
      if (!res.ok) return;
      const json = await res.json();
      if (!json || !json.ok) return;
      apply(json.data);
    } catch (e) { /* admin offline — keep static fallbacks */ }
  }

  function apply(d){
    // ---- Hero ----
    const h = d.hero || {};
    const eyebrow = document.querySelector('.hero-eyebrow');
    if (eyebrow) {
      // keep the dot, replace the text after it
      const text = eyebrow.lastChild;
      if (text && text.nodeType === 3) text.nodeValue = ' ' + (h.eyebrow || '');
      else {
        // fallback: rebuild
        eyebrow.innerHTML = '<span class="dot"></span> ' + escapeHtml(h.eyebrow || '');
      }
    }
    const h1 = document.querySelector('.hero h1');
    if (h1 && (h.h1_1 || h.h1_2 || h.h1_3 || h.h1_4)) {
      h1.innerHTML =
        escapeHtml(h.h1_1 || '') + ' ' +
        '<span class="serif"><em>' + escapeHtml(h.h1_2 || '') + '</em></span><br>' +
        escapeHtml(h.h1_3 || '') + ' ' +
        '<span class="accent">' + escapeHtml(h.h1_4 || '') + '</span>';
    }
    const sub = document.querySelector('.hero-sub');
    if (sub && h.sub != null) sub.textContent = h.sub;

    const ctas = document.querySelectorAll('.hero-cta .btn');
    if (ctas[0] && h.cta1 != null) ctas[0].firstChild && (ctas[0].innerHTML = escapeHtml(h.cta1) + ' <span class="arrow">→</span>');
    if (ctas[1] && h.cta2 != null) ctas[1].textContent = h.cta2;

    // ---- Marquee ----
    if (Array.isArray(d.marquee) && d.marquee.length) {
      const track = document.querySelector('.marquee-track');
      if (track && !track.dataset.dup) {
        const icons = ['', 'dot', 'diam'];
        const items = d.marquee.map((name, i) =>
          `<span class="logo"><span class="ico ${icons[i % 3]}"></span> ${escapeHtml(name)}</span>`
        ).join('');
        // Repeat enough to fill width — typical 3 names → repeat 3x
        track.innerHTML = (items + items + items);
        track.dataset.dup = '1';
      }
    }

    // ---- Services ----
    if (Array.isArray(d.services)) {
      const cards = document.querySelectorAll('.services .svc');
      d.services.forEach((s, i) => {
        const card = cards[i];
        if (!card) return;
        const numEl = card.querySelector('.svc-num');
        const titleEl = card.querySelector('.svc-title');
        const descEl = card.querySelector('.svc-desc');
        const imgEl = card.querySelector('.svc-thumb img');
        const tagsEl = card.querySelector('.svc-tags');
        if (numEl) numEl.textContent = s.num || '';
        if (titleEl) titleEl.innerHTML = sanitizeMinimal(s.title || '');
        if (descEl) descEl.textContent = s.desc || '';
        if (imgEl && s.img) imgEl.src = s.img;
        if (tagsEl) {
          const tags = Array.isArray(s.tags) ? s.tags : [];
          if (tags.length) {
            tagsEl.innerHTML = tags.map(t => `<span class="svc-tag">${escapeHtml(t)}</span>`).join('');
            tagsEl.style.display = '';
          } else {
            tagsEl.innerHTML = '';
          }
        }
      });
    }

    // ---- Team (about page) ----
    if (Array.isArray(d.team)) {
      const members = document.querySelectorAll('.team .member');
      d.team.forEach((m, i) => {
        const card = members[i];
        if (!card) return;
        const nameEl = card.querySelector('.info h4');
        const roleEl = card.querySelector('.info p');
        const glyphEl = card.querySelector('.glyph');
        if (nameEl) nameEl.textContent = m.name || '';
        if (roleEl) roleEl.textContent = m.role || '';
        if (glyphEl) glyphEl.textContent = m.glyph || '';
      });
    }

    // ---- FAQ ----
    if (Array.isArray(d.faq)) {
      const faqContainer = document.querySelector('.faq-list');
      if (faqContainer && d.faq.length) {
        faqContainer.innerHTML = d.faq.map((f, i) => `
          <div class="faq-item${i===1?' open':''}">
            <div class="faq-q"><h4>${escapeHtml(f.q)}</h4><div class="plus"></div></div>
            <div class="faq-a"><div><p>${escapeHtml(f.a)}</p></div></div>
          </div>
        `).join('');
        // Re-bind FAQ click since we replaced the DOM
        faqContainer.querySelectorAll('.faq-item').forEach(it => {
          it.addEventListener('click', () => {
            const open = it.classList.contains('open');
            faqContainer.querySelectorAll('.faq-item.open').forEach(s => s.classList.remove('open'));
            if (!open) it.classList.add('open');
          });
        });
      }
    }
  }

  function escapeHtml(s){
    return String(s).replace(/[&<>"']/g, c =>
      ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c])
    );
  }
  // For service titles — allow only <br> tag (admins type with line breaks)
  function sanitizeMinimal(s){
    return escapeHtml(s).replace(/&lt;br\s*\/?&gt;/gi, '<br>');
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', load);
  } else {
    load();
  }
})();
