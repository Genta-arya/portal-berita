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
 document.addEventListener('click',event=>{
   if(categoryDropdown.open && !categoryDropdown.contains(event.target)) {
     categoryDropdown.open=false;
   }
 });
 categoryDropdown.addEventListener('keydown',event=>{if(event.key==='Escape'){categoryDropdown.open=false;categoryDropdown.querySelector('summary').focus();}});
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
            if(iconSpan) iconSpan.textContent=paused?'\u25b7':'\u2161';
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

// Trending topics open a keyboard-accessible, timed story viewer.
const storyDialog = document.querySelector('[data-story-dialog]');
if (storyDialog) {
    const topics = JSON.parse(document.getElementById('topic-story-data').textContent);
    const get = selector => storyDialog.querySelector(selector);
    const progress = get('.topic-stories-progress');
    const reducedMotion = matchMedia('(prefers-reduced-motion: reduce)');
    let topicIndex = 0, storyIndex = 0, elapsed = 0, last = 0, frame = 0;
    let opener, previousOverflow;
    const duration = 8000;
    const storyImages = new Map();
    let renderVersion = 0, imageLoading = false;
    function loadStoryImage(url) {
        if (!storyImages.has(url)) {
            const image = new Image();
            const ready = new Promise(resolve => {
                image.onload = async () => {
                    try { await image.decode(); } catch {}
                    resolve(image);
                };
                image.onerror = () => resolve(null);
            });
            image.src = url;
            storyImages.set(url, ready);
        }
        return storyImages.get(url);
    }
    const storySequence = topics.flatMap(topic => topic.articles);
    function preloadNeighbors(item) {
        const index = storySequence.indexOf(item);
        [storySequence[index - 1], storySequence[index + 1], storySequence[index + 2]]
            .forEach(neighbor => { if (neighbor?.image) loadStoryImage(neighbor.image); });
    }
    function render() {
        const topic = topics[topicIndex], item = topic.articles[storyIndex];
        get('[data-story-prev]').disabled = storyIndex === 0;
        get('[data-story-next]').disabled = storyIndex === topic.articles.length - 1;
        elapsed = 0; last = 0;
        get('#story-topic-title').textContent = topic.name;
        get('[data-story-time]').textContent = item.time || '';
        get('[data-story-title]').textContent = item.title;
        get('[data-story-summary]').textContent = item.summary;
        get('[data-story-summary]').hidden = !item.summary;
        get('[data-story-link]').href = item.url;
        get('[data-story-image-link]').href = item.url;
        get('[data-story-image-link]').setAttribute('aria-label', `Baca berita: ${item.title}`);
        const version = ++renderVersion;
        const avatar = get('[data-story-avatar]');
        const image = get('[data-story-image]');
        const backdrop = get('.topic-stories-backdrop');
        // Clear every old image immediately, including the blurred background.
        image.hidden = true;
        image.removeAttribute('src');
        avatar.hidden = true;
        avatar.removeAttribute('src');
        get('[data-story-initials]').hidden = false;
        backdrop.style.backgroundImage = 'none';
        imageLoading = !!item.image;
        get('.topic-stories-visual').setAttribute('aria-busy', String(imageLoading));
        if (item.image) {
            loadStoryImage(item.image).then(loaded => {
                if (version !== renderVersion) return;
                imageLoading = false;
                last = 0;
                get('.topic-stories-visual').setAttribute('aria-busy', 'false');
                if (!loaded) return;
                image.src = loaded.src;
                image.alt = item.alt;
                image.hidden = false;
                avatar.src = loaded.src;
                avatar.hidden = false;
                get('[data-story-initials]').hidden = true;
                backdrop.style.backgroundImage = `url(${JSON.stringify(loaded.src)})`;
            });
        }
        preloadNeighbors(item);
        progress.replaceChildren(...topic.articles.map((_, index) => {
            const segment = document.createElement('span'), fill = document.createElement('i');
            fill.style.transform = `scaleX(${index < storyIndex ? 1 : 0})`;
            segment.append(fill); return segment;
        }));
    }
    function move(direction) {
        const nextIndex = storyIndex + direction;
        if (nextIndex < 0 || nextIndex >= topics[topicIndex].articles.length) return;
        storyIndex = nextIndex;
        render();
    }
    function tick(now) {
        if (!storyDialog.open) return;
        if (last && !reducedMotion.matches && !document.hidden && !imageLoading) elapsed += Math.min(now - last, 250);
        last = now;
        const fill = progress.children[storyIndex]?.firstChild;
        if (fill) fill.style.transform = `scaleX(${Math.min(elapsed / duration, 1)})`;
        if (elapsed >= duration) {
            if (storyIndex < topics[topicIndex].articles.length - 1) move(1);
            else storyDialog.close();
        }
        if (storyDialog.open) frame = requestAnimationFrame(tick);
    }
    document.querySelectorAll('[data-story-topic]').forEach(link => {
        link.addEventListener('click', event => {
            if (event.defaultPrevented || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
            const index = topics.findIndex(topic => String(topic.id) === link.dataset.storyTopic);
            if (index < 0 || !topics[index].articles.length) return;
            event.preventDefault(); opener = link; topicIndex = index; storyIndex = 0;
            previousOverflow = document.body.style.overflow;
            document.body.style.overflow = 'hidden';
            render(); storyDialog.showModal(); get('[data-story-close]').focus();
            frame = requestAnimationFrame(tick);
        });
    });
    get('[data-story-close]').addEventListener('click', () => storyDialog.close());
    get('[data-story-prev]').addEventListener('click', () => move(-1));
    get('[data-story-next]').addEventListener('click', () => move(1));
    storyDialog.addEventListener('click', event => { if (event.target === storyDialog) storyDialog.close(); });
    storyDialog.addEventListener('keydown', event => {
        if (event.key === 'ArrowRight' || event.key === 'ArrowLeft') { event.preventDefault(); move(event.key === 'ArrowRight' ? 1 : -1); }
    });
    let touchStart;
    get('.topic-stories-body').addEventListener('touchstart', event => { touchStart = event.touches[0].clientX; }, { passive: true });
    get('.topic-stories-body').addEventListener('touchend', event => {
        if (touchStart == null) return;
        const distance = event.changedTouches[0].clientX - touchStart;
        if (Math.abs(distance) > 55) move(distance < 0 ? 1 : -1);
        touchStart = null;
    }, { passive: true });
    storyDialog.addEventListener('close', () => {
        ++renderVersion;
        cancelAnimationFrame(frame); document.body.style.overflow = previousOverflow;
        opener?.focus();
    });
}
