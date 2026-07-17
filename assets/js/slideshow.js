/* Full slideshow viewer with keyboard, touch, auto-play, notes, timer. */
(function () {
    if (!document.body.classList.contains('page-slideshow')) return;

    const BASE = window.APP_BASE_URL;
    const shell = document.querySelector('.slideshow-shell');
    const presId = shell.dataset.id;
    const holder = document.getElementById('ssHolder');
    const stage = document.getElementById('ssStage');

    const state = {
        pres: null,
        idx: 0,
        playing: false,
        interval: null,
        duration: 5,
        startedAt: null,
    };

    async function init() {
        try {
            const j = await API.get('/api/load-presentation.php?id=' + encodeURIComponent(presId));
            state.pres = j.presentation;
        } catch (err) {
            document.getElementById('ssTitle').textContent = 'Failed to load';
            toast('Load failed: ' + err.message, 'error');
            return;
        }
        document.getElementById('ssTitle').textContent = state.pres.title;
        document.getElementById('ssTotal').textContent = state.pres.slides.length;
        applyAspect();
        renderThumbs();
        show(0);
        state.startedAt = Date.now();
        setInterval(updateTimer, 1000);
    }

    function applyAspect() {
        const a = state.pres.aspect || '16:9';
        holder.classList.remove('a-4-3','a-1-1','a-9-16');
        if (a === '4:3')  holder.classList.add('a-4-3');
        if (a === '1:1')  holder.classList.add('a-1-1');
        if (a === '9:16') holder.classList.add('a-9-16');
    }

    function show(i) {
        if (i < 0) i = 0;
        if (i > state.pres.slides.length - 1) i = state.pres.slides.length - 1;
        state.idx = i;
        const s = state.pres.slides[i];
        holder.innerHTML = window.SlideRenderer.html(s, state.pres.theme, {
            index: i, total: state.pres.slides.length, editable: false
        });
        document.getElementById('ssCur').textContent = i + 1;
        document.getElementById('ssProgressBar').style.width = ((i + 1) / state.pres.slides.length * 100) + '%';
        document.getElementById('ssNotesText').textContent = s.notes || '(no notes)';
        // Highlight active thumb
        document.querySelectorAll('.ss-thumb').forEach((t, k) => t.classList.toggle('active', k === i));
        // Scroll active thumb into view
        const active = document.querySelectorAll('.ss-thumb')[i];
        if (active) active.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
    }

    function next() { show(Math.min(state.idx + 1, state.pres.slides.length - 1)); }
    function prev() { show(Math.max(state.idx - 1, 0)); }
    function first(){ show(0); }
    function last() { show(state.pres.slides.length - 1); }

    function renderThumbs() {
        const box = document.getElementById('ssThumbs');
        box.innerHTML = '';
        state.pres.slides.forEach((s, i) => {
            const t = document.createElement('div');
            t.className = 'ss-thumb';
            t.innerHTML = `<div class="stp-title">${escapeHtml(s.title || 'Slide ' + (i+1))}</div>`;
            t.addEventListener('click', () => show(i));
            box.appendChild(t);
        });
    }

    // Play / pause
    function play() {
        if (state.playing) return pause();
        state.playing = true;
        document.getElementById('ssPlay').textContent = '⏸';
        state.interval = setInterval(() => {
            if (state.idx >= state.pres.slides.length - 1) { pause(); return; }
            next();
        }, state.duration * 1000);
    }
    function pause() {
        state.playing = false;
        clearInterval(state.interval);
        document.getElementById('ssPlay').textContent = '▶';
    }

    // Timer
    function updateTimer() {
        if (!state.startedAt) return;
        const s = Math.floor((Date.now() - state.startedAt) / 1000);
        const mm = String(Math.floor(s / 60)).padStart(2, '0');
        const ss = String(s % 60).padStart(2, '0');
        document.getElementById('ssTimer').textContent = mm + ':' + ss;
    }

    // Controls
    document.getElementById('ssPrev').addEventListener('click', prev);
    document.getElementById('ssNext').addEventListener('click', next);
    document.getElementById('ssPlay').addEventListener('click', play);
    document.getElementById('ssDuration').addEventListener('change', e => {
        state.duration = parseInt(e.target.value, 10) || 5;
        if (state.playing) { pause(); play(); }
    });
    document.getElementById('ssNotes').addEventListener('click', () => {
        document.getElementById('ssNotesPanel').classList.toggle('hidden');
    });
    document.getElementById('ssFull').addEventListener('click', toggleFullscreen);
    function toggleFullscreen() {
        if (!document.fullscreenElement) shell.requestFullscreen?.();
        else document.exitFullscreen?.();
    }

    // Keyboard
    document.addEventListener('keydown', (e) => {
        if (e.key === 'ArrowRight' || e.key === ' ' || e.key === 'PageDown') { e.preventDefault(); next(); }
        else if (e.key === 'ArrowLeft' || e.key === 'PageUp') { e.preventDefault(); prev(); }
        else if (e.key === 'Home') { first(); }
        else if (e.key === 'End')  { last(); }
        else if (e.key === 'f' || e.key === 'F') { toggleFullscreen(); }
        else if (e.key === 'p' || e.key === 'P') { play(); }
        else if (e.key === 'n' || e.key === 'N') { document.getElementById('ssNotesPanel').classList.toggle('hidden'); }
    });

    // Touch swipe
    let touchStartX = 0;
    stage.addEventListener('touchstart', (e) => { touchStartX = e.touches[0].clientX; }, { passive: true });
    stage.addEventListener('touchend',   (e) => {
        const dx = e.changedTouches[0].clientX - touchStartX;
        if (Math.abs(dx) > 60) { if (dx < 0) next(); else prev(); }
    });

    // Mouse click on stage (right half → next, left → prev)
    stage.addEventListener('click', (e) => {
        const rect = stage.getBoundingClientRect();
        if (e.clientX - rect.left > rect.width / 2) next(); else prev();
    });

    function escapeHtml(s) {
        return String(s || '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    }

    // We rely on SlideRenderer defined in editor.js. Include it here as a copy
    // so slideshow works standalone (no editor page loaded).
    if (!window.SlideRenderer) {
        // Minimal inline renderer copy (kept in sync with editor.js).
        window.SlideRenderer = makeRenderer();
    }

    function makeRenderer() {
        return {
            html(slide, theme, opts) {
                opts = opts || {};
                const idx = opts.index || 0;
                const total = opts.total || 1;
                const t = theme || 'modern';
                const layout = slide.layout || 'bullets';
                let bg = '';
                if (slide.background) {
                    if (slide.background.startsWith('http') || slide.background.startsWith('/')) {
                        bg = `style="background-image:url('${slide.background}');background-size:cover;background-position:center;"`;
                    } else bg = `style="background:${slide.background};"`;
                }
                const foot = `
                    <div class="s-foot">
                        <span>${escapeHtml(slide.footer || '')}</span>
                        ${slide.showNumber !== false ? `<span class="s-num">${idx+1} / ${total}</span>` : ''}
                    </div>`;
                const title = `<h1 class="s-title">${escapeHtml(slide.title || '')}</h1>`;
                const sub   = slide.subtitle ? `<div class="s-subtitle">${escapeHtml(slide.subtitle)}</div>` : '';
                const para  = slide.paragraph ? `<div class="s-para">${escapeHtml(slide.paragraph)}</div>` : '';
                const bullets = (slide.bullets && slide.bullets.length)
                    ? `<ul class="s-bullets">${slide.bullets.map(b => `<li>${escapeHtml(b)}</li>`).join('')}</ul>` : '';
                const image = slide.image
                    ? `<div class="s-image" style="background-image:url('${slide.image}')"></div>`
                    : `<div class="s-image loading"></div>`;

                let inner = '', cls = '';
                switch (layout) {
                    case 'title':          inner = title + sub; break;
                    case 'title-subtitle': inner = title + sub + para; break;
                    case 'thank-you':      inner = `<h1 class="s-title">${escapeHtml(slide.title || 'Thank You')}</h1>` +
                                                  (slide.subtitle ? `<div class="s-subtitle">${escapeHtml(slide.subtitle)}</div>` : ''); break;
                    case 'section-divider': inner = title + sub; break;
                    case 'text-image':     cls='layout-split';         inner = `<div>${title}${sub}${para}${bullets}</div>` + image; break;
                    case 'image-text':     cls='layout-split reverse'; inner = image + `<div>${title}${sub}${para}${bullets}</div>`; break;
                    case 'full-image':     inner = image; break;
                    case 'image-overlay':  inner = `<div class="s-bg" ${slide.image?`style="background-image:url('${slide.image}')"`:''}></div><div class="s-content">${title}${sub}${para}${bullets}</div>`; break;
                    case 'two-column': {
                        cls = 'layout-two-col';
                        const c = slide.comparison || {};
                        inner = title + `
                            <div><div class="col-title">${escapeHtml(c.left_title||'Column A')}</div>
                                 <ul>${(c.left||[]).map(x=>`<li>${escapeHtml(x)}</li>`).join('') || bullets}</ul></div>
                            <div><div class="col-title">${escapeHtml(c.right_title||'Column B')}</div>
                                 <ul>${(c.right||[]).map(x=>`<li>${escapeHtml(x)}</li>`).join('')}</ul></div>`;
                        break;
                    }
                    case 'statistics': {
                        cls = 'layout-stats';
                        const st = slide.stats || [];
                        inner = title + sub + `<div class="s-stats">${st.map(x=>`<div class="s-stat"><b>${escapeHtml(x.value)}</b><span>${escapeHtml(x.label)}</span></div>`).join('')}</div>` + para;
                        break;
                    }
                    case 'comparison': {
                        cls = 'layout-compare';
                        const c = slide.comparison || {};
                        inner = title + `<div class="cmp">
                            <div><h4>${escapeHtml(c.left_title||'A')}</h4><ul>${(c.left||[]).map(x=>`<li>${escapeHtml(x)}</li>`).join('')}</ul></div>
                            <div><h4>${escapeHtml(c.right_title||'B')}</h4><ul>${(c.right||[]).map(x=>`<li>${escapeHtml(x)}</li>`).join('')}</ul></div>
                        </div>`;
                        break;
                    }
                    case 'timeline': {
                        cls = 'layout-timeline';
                        inner = title + `<div class="tl">${(slide.timeline||[]).map(x=>`<div><h5>${escapeHtml(x.label)}</h5><p>${escapeHtml(x.text)}</p></div>`).join('')}</div>`;
                        break;
                    }
                    case 'process': {
                        cls = 'layout-process';
                        inner = title + `<div class="pr">${(slide.process||[]).map((x,i)=>`<div><div class="step">${escapeHtml(x.step||(i+1))}</div><h5>${escapeHtml(x.label)}</h5><p>${escapeHtml(x.text)}</p></div>`).join('')}</div>`;
                        break;
                    }
                    case 'quote':
                        inner = `<div class="s-quote">${escapeHtml((slide.quote&&slide.quote.text)||slide.title||'')}</div><div class="s-author">— ${escapeHtml((slide.quote&&slide.quote.author)||'')}</div>`;
                        break;
                    case 'takeaways': {
                        cls = 'layout-takeaways';
                        const tks = (slide.takeaways && slide.takeaways.length) ? slide.takeaways : slide.bullets;
                        inner = title + sub + `<div class="tks">${(tks||[]).map(x=>`<div>${escapeHtml(x)}</div>`).join('')}</div>`;
                        break;
                    }
                    default:
                        inner = title + sub + para + bullets;
                }
                return `<div class="slide ${cls}" data-theme="${t}" data-layout="${layout}" ${bg}>${inner}${['title','thank-you','section-divider'].includes(layout)?'':foot}</div>`;
            }
        };
    }

    init();
})();
