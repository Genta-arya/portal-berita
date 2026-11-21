document.querySelectorAll('[data-copy]').forEach(b => b.addEventListener('click', async () => { try { await navigator.clipboard.writeText(b.dataset.copy); b.textContent = 'Tautan disalin'; } catch { window.prompt('Salin tautan berikut', b.dataset.copy); } }));
const article=document.querySelector('[data-article-id]');
document.querySelectorAll('.prose iframe').forEach(frame=>{const button=document.createElement('button');button.textContent='Muat video YouTube';button.type='button';button.addEventListener('click',()=>{frame.loading='lazy';frame.setAttribute('allowfullscreen','');button.replaceWith(frame);});frame.replaceWith(button);});
if(article)fetch(`/berita/${article.dataset.articleId}/view`,{method:'POST',headers:{'X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content,'Accept':'application/json'},credentials:'same-origin'}).catch(()=>{});
const carousel = document.querySelector('[data-headline-carousel]');
if (carousel) {
 const track = carousel.querySelector('.carousel-slides');
 const slides = [...track.querySelectorAll('[data-slide]')];
 const dots = [...carousel.querySelectorAll('[data-indicator]')];
 const status = carousel.querySelector('[data-carousel-status]');
 const pause = carousel.querySelector('[data-autoplay-toggle]');
 const prevBtn = carousel.querySelector('[data-carousel-prev]');
 const nextBtn = carousel.querySelector('[data-carousel-next]');
 const motion = matchMedia('(prefers-reduced-motion: reduce)');
 let index = 0, timer, settle, drag = null, suppressClick = false;
 let hovered = false, focused = false, inView = true, userPaused = false;
 if (prevBtn) prevBtn.addEventListener('click', (e) => { e.preventDefault(); e.stopPropagation(); go(index - 1); });
 if (nextBtn) nextBtn.addEventListener('click', (e) => { e.preventDefault(); e.stopPropagation(); go(index + 1); });
 dots.forEach((dot, dotIndex) => {
  dot.style.cursor = 'pointer';
  dot.addEventListener('click', (e) => { e.preventDefault(); e.stopPropagation(); go(dotIndex); });
 });
 if (slides.length > 1) {
  carousel.classList.add('carousel-enhanced');
  slides.forEach(slide => { slide.hidden = false; });
  carousel.querySelector('[data-carousel-controls]').hidden = false;
  const restart = () => {
   clearTimeout(timer);
   const running = !motion.matches && !userPaused && !hovered && !focused && !document.hidden && inView && !drag;
   carousel.classList.toggle('is-playing', running);
   status.setAttribute('aria-live', running ? 'off' : 'polite');
   if (running) timer = setTimeout(() => go(index + 1), 6000);
  };
  const mark = next => {
   index = (next + slides.length) % slides.length;
   dots.forEach((dot, i) => dot.classList.toggle('is-active', i === index));
   status.textContent = `Berita ${index + 1} dari ${slides.length}`;
  };
  const go = next => {
   mark(next);
   track.scrollTo({left: index * track.clientWidth, behavior: motion.matches ? 'instant' : 'smooth'});
   restart();
  };
  track.addEventListener('scroll', () => {
   clearTimeout(settle);
   settle = setTimeout(() => { mark(Math.round(track.scrollLeft / track.clientWidth)); restart(); }, 140);
  }, {passive: true});
  carousel.addEventListener('keydown', event => {
   if (event.target !== carousel) return;
   if (event.key === 'ArrowLeft' || event.key === 'ArrowRight') { event.preventDefault(); go(index + (event.key === 'ArrowRight' ? 1 : -1)); }
  });
  track.addEventListener('dragstart', event => event.preventDefault());
  track.addEventListener('pointerdown', event => {
   if (event.pointerType !== 'mouse' || event.button !== 0) return;
   drag = {id:event.pointerId, x:event.clientX, left:track.scrollLeft, moved:false};
   clearTimeout(timer);
  });
  track.addEventListener('pointermove', event => {
   if (!drag) return;
   const distance = event.clientX - drag.x;
   if (Math.abs(distance) > 6 && !drag.moved) { drag.moved = true; track.setPointerCapture(event.pointerId); track.classList.add('is-dragging'); }
   if (drag.moved) { event.preventDefault(); track.scrollLeft = drag.left - distance; }
  });
  const endDrag = event => {
   if (!drag) return;
   const previous = drag;
   drag = null;
   if (track.hasPointerCapture(previous.id)) track.releasePointerCapture(previous.id);
   track.classList.remove('is-dragging');
   if (previous.moved) {
    suppressClick = true;
    setTimeout(() => { suppressClick = false; }, 0);
    const distance = event.clientX - previous.x;
    go(index + (Math.abs(distance) > 45 ? (distance < 0 ? 1 : -1) : 0));
   } else restart();
  };
  track.addEventListener('pointerup', endDrag);
  track.addEventListener('pointercancel', endDrag);
  window.addEventListener('pointerup', endDrag);
  track.addEventListener('click', event => { if (suppressClick) { event.preventDefault(); event.stopPropagation(); } }, true);
  track.addEventListener('touchstart', () => clearTimeout(timer), {passive:true});
  track.addEventListener('touchend', restart, {passive:true});
  carousel.addEventListener('pointerenter', event => { if (event.pointerType === 'mouse') { hovered = true; restart(); } });
  carousel.addEventListener('pointerleave', () => { hovered = false; restart(); });
  carousel.addEventListener('focusin', () => { focused = true; restart(); });
  carousel.addEventListener('focusout', event => { if (!carousel.contains(event.relatedTarget)) { focused = false; restart(); } });
  pause.addEventListener('click', () => { userPaused = !userPaused; pause.textContent = userPaused ? 'Putar carousel otomatis' : 'Jeda carousel otomatis'; pause.setAttribute('aria-pressed', String(userPaused)); restart(); });
  document.addEventListener('visibilitychange', restart);
  motion.addEventListener('change', restart);
  new IntersectionObserver(entries => { inView = entries[0].isIntersecting; restart(); }, {threshold:0.15}).observe(carousel);
  new ResizeObserver(() => { track.scrollTo({left:index * track.clientWidth, behavior:'instant'}); }).observe(track);
  mark(0); restart();
 }
}
const categoryDropdown = document.querySelector('[data-category-dropdown]');
if(categoryDropdown){
 document.addEventListener('click',event=>{if(!categoryDropdown.contains(event.target))categoryDropdown.open=false;});
 categoryDropdown.addEventListener('keydown',event=>{if(event.key==='Escape'){categoryDropdown.open=false;categoryDropdown.querySelector('summary').focus();}});
 categoryDropdown.addEventListener('focusout',event=>{if(event.relatedTarget&&!categoryDropdown.contains(event.relatedTarget))categoryDropdown.open=false;});
}
const latestTicker=document.querySelector('[data-latest-ticker]');
if(latestTicker){
    const tickerPause=latestTicker.querySelector('[data-ticker-pause]');
    if(tickerPause){
        tickerPause.addEventListener('click',event=>{
            const paused=latestTicker.classList.toggle('is-paused');
            event.currentTarget.setAttribute('aria-pressed',String(paused));
            event.currentTarget.setAttribute('aria-label',paused?'Putar berita berjalan':'Jeda berita berjalan');
            const iconSpan = event.currentTarget.querySelector('span');
            if(iconSpan) iconSpan.textContent=paused?'▷':'Ⅱ';
        });
    }
}

// Dark & Light Mode Toggle Initialization
(function initTheme() {
    const themeToggleBtn = document.getElementById('theme-toggle-btn');
    if (!themeToggleBtn) return;

    const syncThemeUI = (isDark) => {
        themeToggleBtn.setAttribute('aria-pressed', isDark ? 'true' : 'false');
        themeToggleBtn.setAttribute('aria-label', isDark ? 'Ganti ke mode terang' : 'Ganti ke mode gelap');
        const textLabel = themeToggleBtn.querySelector('[data-theme-label]');
        if (textLabel) {
            textLabel.textContent = isDark ? 'Mode Terang' : 'Mode Gelap';
        }
    };

    const initialDark = document.documentElement.getAttribute('data-theme') === 'dark';
    syncThemeUI(initialDark);
})();

// Helper for horizontal drag-to-scroll swipe on tracks
function enableDragScroll(track) {
    if (!track) return;
    let isDown = false;
    let startX = 0;
    let scrollLeft = 0;
    let hasDragged = false;

    track.addEventListener('mousedown', (e) => {
        isDown = true;
        hasDragged = false;
        startX = e.pageX - track.offsetLeft;
        scrollLeft = track.scrollLeft;
        track.classList.add('is-dragging');
    });

    const stopDragging = () => {
        if (!isDown) return;
        isDown = false;
        track.classList.remove('is-dragging');
    };

    track.addEventListener('mouseleave', stopDragging);
    track.addEventListener('mouseup', stopDragging);

    track.addEventListener('mousemove', (e) => {
        if (!isDown) return;
        const x = e.pageX - track.offsetLeft;
        const walk = (x - startX) * 1.5;
        if (Math.abs(walk) > 6) {
            hasDragged = true;
        }
        track.scrollLeft = scrollLeft - walk;
    });

    // Suppress link click if dragged
    track.addEventListener('click', (e) => {
        if (hasDragged) {
            e.preventDefault();
            e.stopPropagation();
            hasDragged = false;
        }
    }, true);
}

// Topic Shelf Horizontal Swipe & Nav
document.querySelectorAll('[data-topic-shelf]').forEach(shelf => {
    const track = shelf.querySelector('[data-topic-track]');
    const prevBtn = shelf.querySelector('[data-topic-prev]');
    const nextBtn = shelf.querySelector('[data-topic-next]');
    if (!track) return;
    enableDragScroll(track);
    if (prevBtn) {
        prevBtn.addEventListener('click', (e) => {
            e.preventDefault();
            track.scrollBy({ left: -260, behavior: 'smooth' });
        });
    }
    if (nextBtn) {
        nextBtn.addEventListener('click', (e) => {
            e.preventDefault();
            track.scrollBy({ left: 260, behavior: 'smooth' });
        });
    }
});

// Flex Slider for Featured Category
document.querySelectorAll('[data-flex-slider]').forEach(slider => {
    const track = slider.querySelector('[data-flex-track]');
    const prevBtn = slider.querySelector('[data-flex-prev]');
    const nextBtn = slider.querySelector('[data-flex-next]');
    if (!track) return;
    enableDragScroll(track);
    if (prevBtn) {
        prevBtn.addEventListener('click', (e) => {
            e.preventDefault();
            track.scrollBy({ left: -340, behavior: 'smooth' });
        });
    }
    if (nextBtn) {
        nextBtn.addEventListener('click', (e) => {
            e.preventDefault();
            track.scrollBy({ left: 340, behavior: 'smooth' });
        });
    }
});
