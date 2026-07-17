/* ============================================================
   Editor — loads presentation, renders slide, handles all edits.
   ============================================================ */
(function () {
    if (!document.body.classList.contains('page-editor')) return;

    const BASE = window.APP_BASE_URL;
    const shell = document.querySelector('.editor-shell');
    const presId = shell.dataset.id;

    const canvasEl = document.getElementById('slideCanvas');
    const thumbList = document.getElementById('slideThumbList');
    const notesPanel = document.getElementById('notesPanel');
    const notesText = document.getElementById('notesText');
    const deckTitleInput = document.getElementById('deckTitleInput');
    const saveBadge = document.getElementById('saveBadge');

    // Undo/redo history
    const undoStack = { stack: [], pointer: -1 };
    function snapshot() {
        // Truncate future
        undoStack.stack = undoStack.stack.slice(0, undoStack.pointer + 1);
        undoStack.stack.push(JSON.stringify(state.presentation));
        if (undoStack.stack.length > 60) undoStack.stack.shift();
        undoStack.pointer = undoStack.stack.length - 1;
    }
    function undo() {
        if (undoStack.pointer <= 0) return;
        undoStack.pointer--;
        state.presentation = JSON.parse(undoStack.stack[undoStack.pointer]);
        renderAll(); markDirty();
    }
    function redo() {
        if (undoStack.pointer >= undoStack.stack.length - 1) return;
        undoStack.pointer++;
        state.presentation = JSON.parse(undoStack.stack[undoStack.pointer]);
        renderAll(); markDirty();
    }

    // ------------- State -------------
    const state = {
        presentation: null,
        current: 0,
        dirty: false,
    };
    window.__editorState = state;

    // ------------- Load / init -------------
    async function init() {
        if (presId) {
            try {
                const j = await API.get('/api/load-presentation.php?id=' + encodeURIComponent(presId));
                state.presentation = j.presentation;
            } catch (err) {
                toast('Could not load presentation: ' + err.message, 'error');
                state.presentation = blank();
            }
        } else {
            state.presentation = blank();
        }
        deckTitleInput.value = state.presentation.title || 'Untitled';
        applyAspect();
        renderAll();
        snapshot();
    }

    function blank() {
        return {
            id: '', title: 'Untitled Presentation', topic: '',
            theme: 'modern', imageStyle: 'professional', aspect: '16:9',
            slides: [{
                id: uid(), layout: 'title',
                title: 'Untitled Presentation', subtitle: '',
                bullets: [], stats: [], quote:{text:'',author:''},
                comparison:{left_title:'',left:[],right_title:'',right:[]},
                timeline:[], process:[], takeaways:[],
                paragraph: '', notes: '', image: '',
                transition: 'fade', duration: 6,
                showNumber: true, footer: '',
            }],
        };
    }

    function applyAspect() {
        const a = state.presentation.aspect || '16:9';
        canvasEl.classList.remove('a-4-3','a-1-1','a-9-16');
        if (a === '4:3')  canvasEl.classList.add('a-4-3');
        if (a === '1:1')  canvasEl.classList.add('a-1-1');
        if (a === '9:16') canvasEl.classList.add('a-9-16');
    }

    // ------------- Renderer (shared) -------------
    // Exposed globally so slideshow.js can reuse the exact same HTML.
    window.SlideRenderer = {
        html(slide, theme, opts) {
            opts = opts || {};
            const idx = opts.index || 0;
            const total = opts.total || 1;
            const editable = !!opts.editable;
            const ce = editable ? ' contenteditable="true" data-edit-key' : '';
            const cek = (k) => editable ? ` contenteditable="true" data-edit-key="${k}"` : '';
            const t = theme || 'modern';
            const layout = slide.layout || 'bullets';

            let bg = '';
            let bgClass = '';
            if (slide.background) {
                if (slide.background.startsWith('http') || slide.background.startsWith('/')) {
                    bg = `style="background-image:url('${slide.background}');background-size:cover;background-position:center;"`;
                } else {
                    bg = `style="background:${slide.background};"`;
                }
            }

            const footHtml = `
                <div class="s-foot">
                    <span>${escapeHtml(slide.footer || '')}</span>
                    ${slide.showNumber !== false ? `<span class="s-num">${idx + 1} / ${total}</span>` : ''}
                </div>`;

            const titleHtml = `<h1 class="s-title"${cek('title')}>${escapeHtml(slide.title || '')}</h1>`;
            const subtitleHtml = slide.subtitle ? `<div class="s-subtitle"${cek('subtitle')}>${escapeHtml(slide.subtitle)}</div>` : '';
            const paraHtml = slide.paragraph ? `<div class="s-para"${cek('paragraph')}>${escapeHtml(slide.paragraph)}</div>` : '';
            const bulletsHtml = (slide.bullets && slide.bullets.length)
                ? `<ul class="s-bullets">${slide.bullets.map((b, i) => `<li${editable?` contenteditable="true" data-edit-key="bullets.${i}"`:''}>${escapeHtml(b)}</li>`).join('')}</ul>`
                : '';

            const imgHtml = slide.image
                ? `<div class="s-image" data-image style="background-image:url('${slide.image}')"></div>`
                : `<div class="s-image loading" data-image><div class="s-image-err">No image — use the Image tab</div></div>`;

            let inner = '';
            let classes = '';

            switch (layout) {
                case 'title':
                    inner = titleHtml + subtitleHtml;
                    break;
                case 'title-subtitle':
                    inner = titleHtml + subtitleHtml + paraHtml;
                    break;
                case 'thank-you':
                    inner = `<h1 class="s-title"${cek('title')}>${escapeHtml(slide.title || 'Thank You')}</h1>` +
                            (slide.subtitle ? `<div class="s-subtitle"${cek('subtitle')}>${escapeHtml(slide.subtitle)}</div>` : `<div class="s-subtitle">Questions?</div>`);
                    break;
                case 'section-divider':
                    inner = titleHtml + subtitleHtml;
                    break;
                case 'text-image':
                    classes = 'layout-split';
                    inner = `<div>${titleHtml}${subtitleHtml}${paraHtml}${bulletsHtml}</div>` + imgHtml;
                    break;
                case 'image-text':
                    classes = 'layout-split reverse';
                    inner = imgHtml + `<div>${titleHtml}${subtitleHtml}${paraHtml}${bulletsHtml}</div>`;
                    break;
                case 'full-image':
                    inner = imgHtml;
                    break;
                case 'image-overlay':
                    inner = `<div class="s-bg" ${slide.image ? `style="background-image:url('${slide.image}')"` : ''}></div>` +
                            `<div class="s-content">${titleHtml}${subtitleHtml}${paraHtml}${bulletsHtml}</div>`;
                    break;
                case 'two-column': {
                    classes = 'layout-two-col';
                    const c = slide.comparison || {};
                    inner = titleHtml + `
                        <div>
                            <div class="col-title"${cek('comparison.left_title')}>${escapeHtml(c.left_title || 'Column A')}</div>
                            <ul>${(c.left||[]).map(x => `<li>${escapeHtml(x)}</li>`).join('') || bulletsHtml}</ul>
                        </div>
                        <div>
                            <div class="col-title"${cek('comparison.right_title')}>${escapeHtml(c.right_title || 'Column B')}</div>
                            <ul>${(c.right||[]).map(x => `<li>${escapeHtml(x)}</li>`).join('')}</ul>
                        </div>`;
                    break;
                }
                case 'statistics': {
                    classes = 'layout-stats';
                    const s = slide.stats || [];
                    inner = titleHtml + subtitleHtml +
                        `<div class="s-stats">${s.map(x => `
                            <div class="s-stat">
                                <b>${escapeHtml(x.value)}</b>
                                <span>${escapeHtml(x.label)}</span>
                            </div>`).join('')}</div>` + paraHtml;
                    break;
                }
                case 'comparison': {
                    classes = 'layout-compare';
                    const c = slide.comparison || {};
                    inner = titleHtml + `
                        <div class="cmp">
                            <div>
                                <h4${cek('comparison.left_title')}>${escapeHtml(c.left_title || 'Option A')}</h4>
                                <ul>${(c.left||[]).map(x => `<li>${escapeHtml(x)}</li>`).join('')}</ul>
                            </div>
                            <div>
                                <h4${cek('comparison.right_title')}>${escapeHtml(c.right_title || 'Option B')}</h4>
                                <ul>${(c.right||[]).map(x => `<li>${escapeHtml(x)}</li>`).join('')}</ul>
                            </div>
                        </div>`;
                    break;
                }
                case 'timeline': {
                    classes = 'layout-timeline';
                    const t2 = slide.timeline || [];
                    inner = titleHtml + `<div class="tl">${t2.map(x => `
                        <div>
                            <h5>${escapeHtml(x.label)}</h5>
                            <p>${escapeHtml(x.text)}</p>
                        </div>`).join('')}</div>`;
                    break;
                }
                case 'process': {
                    classes = 'layout-process';
                    const p = slide.process || [];
                    inner = titleHtml + `<div class="pr">${p.map((x, i) => `
                        <div>
                            <div class="step">${escapeHtml(x.step || (i+1))}</div>
                            <h5>${escapeHtml(x.label)}</h5>
                            <p>${escapeHtml(x.text)}</p>
                        </div>`).join('')}</div>`;
                    break;
                }
                case 'features':
                case 'problem-solution':
                case 'bullets':
                    inner = titleHtml + subtitleHtml + paraHtml + bulletsHtml;
                    break;
                case 'quote':
                    inner = `<div class="s-quote"${cek('quote.text')}>${escapeHtml((slide.quote && slide.quote.text) || slide.title || '')}</div>` +
                            `<div class="s-author"${cek('quote.author')}>— ${escapeHtml((slide.quote && slide.quote.author) || '')}</div>`;
                    break;
                case 'takeaways': {
                    classes = 'layout-takeaways';
                    const tks = slide.takeaways && slide.takeaways.length ? slide.takeaways : slide.bullets;
                    inner = titleHtml + subtitleHtml +
                        `<div class="tks">${(tks || []).map(x => `<div>${escapeHtml(x)}</div>`).join('')}</div>`;
                    break;
                }
                default:
                    inner = titleHtml + subtitleHtml + paraHtml + bulletsHtml;
            }

            return `<div class="slide ${classes}" data-theme="${t}" data-layout="${layout}" ${bg}>
                        ${inner}
                        ${layout === 'title' || layout === 'thank-you' || layout === 'section-divider' ? '' : footHtml}
                    </div>`;
        },

        thumb(slide, theme, i, total) {
            const t = theme || 'modern';
            return `
                <div class="st-preview slide" data-theme="${t}" data-layout="${slide.layout}">
                    <div class="stp-title">${escapeHtml(slide.title || 'Slide')}</div>
                    <div class="stp-lines">
                        <div class="stp-line"></div>
                        <div class="stp-line" style="width:60%"></div>
                        <div class="stp-line" style="width:40%"></div>
                    </div>
                </div>`;
        }
    };

    // ------------- Render all -------------
    function renderAll() {
        renderCanvas();
        renderThumbs();
        deckTitleInput.value = state.presentation.title;
        applyAspect();
    }

    function renderCanvas() {
        const p = state.presentation;
        const s = p.slides[state.current];
        if (!s) { canvasEl.innerHTML = ''; return; }
        canvasEl.innerHTML = SlideRenderer.html(s, p.theme, {
            index: state.current, total: p.slides.length, editable: true
        });

        // Attach live edit handlers
        canvasEl.querySelectorAll('[contenteditable="true"]').forEach(el => {
            el.addEventListener('input', () => {
                const key = el.dataset.editKey;
                writeKey(s, key, el.innerText);
                markDirty();
                updateThumb(state.current);
            });
        });

        // Prop panel sync
        document.getElementById('propLayout').value = s.layout || 'bullets';
        document.getElementById('propTransition').value = s.transition || 'fade';
        document.getElementById('propDuration').value = s.duration || 6;
        document.getElementById('propFooter').value = s.footer || '';
        document.getElementById('propShowNumber').checked = s.showNumber !== false;
        document.getElementById('propImagePrompt').value = s.imagePrompt || '';
        notesText.value = s.notes || '';
    }

    function writeKey(obj, key, val) {
        if (!key) return;
        if (key.includes('.')) {
            const parts = key.split('.');
            let cur = obj;
            for (let i = 0; i < parts.length - 1; i++) {
                const p = parts[i];
                if (/^\d+$/.test(p)) {
                    // numeric index into array
                    cur = cur[parseInt(p, 10)];
                } else {
                    if (!cur[p]) cur[p] = {};
                    cur = cur[p];
                }
                if (cur == null) return;
            }
            const last = parts[parts.length - 1];
            if (/^\d+$/.test(last)) cur[parseInt(last, 10)] = val;
            else cur[last] = val;
        } else {
            obj[key] = val;
        }
    }

    function renderThumbs() {
        const p = state.presentation;
        thumbList.innerHTML = '';
        p.slides.forEach((s, i) => {
            const t = document.createElement('div');
            t.className = 'slide-thumb' + (i === state.current ? ' active' : '');
            t.innerHTML = `
                <div class="st-num">${i + 1}</div>
                <div class="st-actions">
                    <button data-t-act="dup" title="Duplicate">⧉</button>
                    <button data-t-act="del" title="Delete">✕</button>
                </div>
                ${SlideRenderer.thumb(s, p.theme, i, p.slides.length)}`;
            t.addEventListener('click', (e) => {
                if (e.target.closest('.st-actions')) return;
                state.current = i; renderAll();
            });
            t.querySelector('[data-t-act="dup"]').addEventListener('click', (e) => {
                e.stopPropagation();
                const copy = JSON.parse(JSON.stringify(s));
                copy.id = uid();
                p.slides.splice(i + 1, 0, copy);
                state.current = i + 1;
                snapshot(); markDirty(); renderAll();
            });
            t.querySelector('[data-t-act="del"]').addEventListener('click', (e) => {
                e.stopPropagation();
                if (p.slides.length <= 1) return toast('At least one slide required', 'error');
                p.slides.splice(i, 1);
                state.current = Math.min(state.current, p.slides.length - 1);
                snapshot(); markDirty(); renderAll();
            });
            thumbList.appendChild(t);
        });
    }

    function updateThumb(i) {
        // Refresh only one thumbnail (cheaper than renderThumbs)
        const t = thumbList.children[i]; if (!t) return;
        const preview = t.querySelector('.st-preview .stp-title');
        if (preview) preview.textContent = state.presentation.slides[i].title || 'Slide';
    }

    // ------------- Add / Undo / Redo -------------
    document.getElementById('btnAddSlide').addEventListener('click', () => {
        const p = state.presentation;
        p.slides.splice(state.current + 1, 0, {
            id: uid(), layout: 'bullets', title: 'New Slide', subtitle: '',
            bullets: ['New point'], stats: [], quote:{text:'',author:''},
            comparison:{left_title:'',left:[],right_title:'',right:[]},
            timeline:[], process:[], takeaways:[],
            paragraph: '', notes: '', image: '', transition: 'fade', duration: 6,
            showNumber: true, footer: '',
        });
        state.current++;
        snapshot(); markDirty(); renderAll();
    });

    document.getElementById('tbUndo').addEventListener('click', undo);
    document.getElementById('tbRedo').addEventListener('click', redo);

    document.addEventListener('keydown', (e) => {
        if ((e.ctrlKey || e.metaKey) && e.key === 'z' && !e.shiftKey) { e.preventDefault(); undo(); }
        else if ((e.ctrlKey || e.metaKey) && (e.key === 'y' || (e.key === 'z' && e.shiftKey))) { e.preventDefault(); redo(); }
    });

    // ------------- Toolbar -------------
    document.getElementById('tbSpeakerNotes').addEventListener('click', () => {
        notesPanel.classList.toggle('hidden');
    });
    notesText.addEventListener('input', () => {
        state.presentation.slides[state.current].notes = notesText.value;
        markDirty();
    });
    deckTitleInput.addEventListener('input', () => {
        state.presentation.title = deckTitleInput.value;
        markDirty();
    });

    document.getElementById('tbPresent').addEventListener('click', async () => {
        await forceSave();
        if (state.presentation.id) location.href = BASE + '/slideshow.php?id=' + state.presentation.id;
    });

    document.getElementById('tbRegenText').addEventListener('click', async () => {
        const s = state.presentation.slides[state.current];
        const btn = document.getElementById('tbRegenText'); btn.disabled = true;
        try {
            const r = await API.post('/api/generate-content.php', {
                topic:    state.presentation.topic || state.presentation.title,
                audience: (state.presentation.meta && state.presentation.meta.audience) || 'General Audience',
                tone:     (state.presentation.meta && state.presentation.meta.tone) || 'Professional',
                language: (state.presentation.meta && state.presentation.meta.language) || 'English',
                slide: {
                    title: s.title, summary: s.paragraph || (s.bullets||[]).join(' · '),
                    layout: s.layout, image_prompt: s.imagePrompt,
                }
            });
            Object.assign(s, {
                title: r.content.title || s.title,
                subtitle: r.content.subtitle || s.subtitle,
                paragraph: r.content.paragraph || s.paragraph,
                bullets: r.content.bullets || s.bullets,
                stats: r.content.stats || s.stats,
                quote: r.content.quote || s.quote,
                comparison: r.content.comparison || s.comparison,
                timeline: r.content.timeline || s.timeline,
                process: r.content.process || s.process,
                takeaways: r.content.takeaways || s.takeaways,
                notes: r.content.notes || s.notes,
                imagePrompt: r.content.image_prompt || s.imagePrompt,
            });
            snapshot(); markDirty(); renderAll();
            toast('Text regenerated', 'success');
        } catch (err) {
            toast('Regenerate failed: ' + err.message, 'error');
        } finally { btn.disabled = false; }
    });

    document.getElementById('tbRegenImage').addEventListener('click', regenerateImage);
    document.getElementById('btnRegenerateImage').addEventListener('click', regenerateImage);
    async function regenerateImage() {
        const s = state.presentation.slides[state.current];
        const btn = document.getElementById('btnRegenerateImage'); btn.disabled = true;
        try {
            const promptText = document.getElementById('propImagePrompt').value || s.imagePrompt || s.title;
            const r = await API.post('/api/generate-image.php', {
                prompt: promptText, title: s.title,
                topic: state.presentation.topic || state.presentation.title,
                style: state.presentation.imageStyle || 'professional',
                aspect: state.presentation.aspect || '16:9',
                seed: Math.floor(Math.random() * 999999),
            });
            s.image = r.url;
            s.imagePrompt = promptText;
            snapshot(); markDirty(); renderAll();
            toast('Image regenerated', 'success');
        } catch (err) {
            toast('Image failed: ' + err.message, 'error');
        } finally { btn.disabled = false; }
    }

    document.getElementById('btnRemoveImage').addEventListener('click', () => {
        state.presentation.slides[state.current].image = '';
        snapshot(); markDirty(); renderAll();
    });

    document.getElementById('propImageUpload').addEventListener('change', async (e) => {
        const f = e.target.files[0]; if (!f) return;
        const reader = new FileReader();
        reader.onload = () => {
            state.presentation.slides[state.current].image = reader.result;
            snapshot(); markDirty(); renderAll();
            toast('Custom image applied', 'success');
        };
        reader.readAsDataURL(f);
    });

    // Layout switcher
    document.getElementById('tbLayout').addEventListener('click', () => {
        document.getElementById('propLayout').focus();
    });
    document.getElementById('propLayout').addEventListener('change', (e) => {
        state.presentation.slides[state.current].layout = e.target.value;
        snapshot(); markDirty(); renderAll();
    });
    document.getElementById('propTransition').addEventListener('change', (e) => {
        state.presentation.slides[state.current].transition = e.target.value; markDirty();
    });
    document.getElementById('propDuration').addEventListener('change', (e) => {
        state.presentation.slides[state.current].duration = parseInt(e.target.value, 10) || 6; markDirty();
    });
    document.getElementById('propFooter').addEventListener('input', (e) => {
        state.presentation.slides[state.current].footer = e.target.value; markDirty(); renderAll();
    });
    document.getElementById('propShowNumber').addEventListener('change', (e) => {
        state.presentation.slides[state.current].showNumber = e.target.checked; markDirty(); renderAll();
    });
    document.getElementById('propBg').addEventListener('input', (e) => {
        state.presentation.slides[state.current].background = e.target.value; markDirty(); renderAll();
    });
    document.getElementById('applyGrad').addEventListener('click', () => {
        const c1 = document.getElementById('propGrad1').value;
        const c2 = document.getElementById('propGrad2').value;
        state.presentation.slides[state.current].background = `linear-gradient(135deg, ${c1}, ${c2})`;
        snapshot(); markDirty(); renderAll();
    });

    // Theme popup
    document.getElementById('tbTheme').addEventListener('click', () => {
        const themes = ['modern','corporate','minimal','futuristic','tech','creative','educational','luxury','dark-pro','clean','gradient','glass','nature','custom'];
        showModal({
            title: 'Choose theme',
            body: `<div class="chip-row" style="margin-top:10px">${themes.map(t => `<button class="chip" data-t="${t}">${t}</button>`).join('')}</div>`,
            confirmText: 'Close', cancelText: '',
            onConfirm: () => {}
        });
        setTimeout(() => {
            document.querySelectorAll('.modal-dialog [data-t]').forEach(b => {
                b.addEventListener('click', () => {
                    state.presentation.theme = b.dataset.t;
                    snapshot(); markDirty(); renderAll();
                    document.querySelector('.modal-backdrop').click();
                });
            });
        }, 50);
    });

    // Props tabs
    document.querySelectorAll('.pt-btn').forEach(b => {
        b.addEventListener('click', () => {
            document.querySelectorAll('.pt-btn').forEach(x => x.classList.remove('active'));
            document.querySelectorAll('.props-pane').forEach(x => x.classList.remove('active'));
            b.classList.add('active');
            document.querySelector(`.props-pane[data-pane="${b.dataset.tab}"]`).classList.add('active');
        });
    });

    // AI text tools
    document.querySelectorAll('[data-textop]').forEach(btn => {
        btn.addEventListener('click', async () => {
            const s = state.presentation.slides[state.current];
            const op = btn.dataset.textop;
            const map = {
                rewrite:     'Rewrite the content of this slide in a cleaner, more engaging way. Keep the same layout keys.',
                shorten:     'Shorten every text field on this slide by at least 30% while keeping meaning. Same JSON shape.',
                expand:      'Expand the content of this slide with more detail while keeping bullets short. Same JSON shape.',
                professional:'Rewrite in a polished, corporate, professional tone. Same JSON shape.',
                grammar:     'Fix all grammar and spelling. Keep meaning. Same JSON shape.',
            };
            btn.disabled = true;
            try {
                const r = await API.post('/api/generate-content.php', {
                    topic:    state.presentation.topic || state.presentation.title,
                    audience: (state.presentation.meta && state.presentation.meta.audience) || 'General Audience',
                    tone:     op === 'professional' ? 'Professional' : ((state.presentation.meta && state.presentation.meta.tone) || 'Professional'),
                    language: (state.presentation.meta && state.presentation.meta.language) || 'English',
                    slide: {
                        title: s.title,
                        summary: (map[op] + '\n\nExisting content:\n' + JSON.stringify({
                            title: s.title, subtitle: s.subtitle, paragraph: s.paragraph, bullets: s.bullets
                        })),
                        layout: s.layout,
                        image_prompt: s.imagePrompt,
                    }
                });
                Object.assign(s, {
                    title: r.content.title || s.title,
                    subtitle: r.content.subtitle || s.subtitle,
                    paragraph: r.content.paragraph || s.paragraph,
                    bullets: (r.content.bullets && r.content.bullets.length) ? r.content.bullets : s.bullets,
                });
                snapshot(); markDirty(); renderAll();
                toast('Text updated', 'success');
            } catch (err) { toast(err.message, 'error'); }
            finally { btn.disabled = false; }
        });
    });

    // Export menu open/close
    const exportBtn = document.getElementById('tbExport');
    const exportMenu = exportBtn.closest('.tb-menu');
    exportBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        exportMenu.classList.toggle('open');
    });
    document.addEventListener('click', () => exportMenu.classList.remove('open'));

    // ------------- Save -------------
    let saveTimer = null;
    function markDirty() {
        state.dirty = true;
        saveBadge.textContent = 'Unsaved changes'; saveBadge.classList.add('saving');
        if (saveTimer) clearTimeout(saveTimer);
        saveTimer = setTimeout(forceSave, 1400);
        // Local draft backup
        try { localStorage.setItem('draft_' + (state.presentation.id || 'new'), JSON.stringify(state.presentation)); } catch(e) {}
    }

    async function forceSave() {
        try {
            const r = await API.post('/api/save-presentation.php', state.presentation);
            state.presentation.id = r.id;
            state.dirty = false;
            saveBadge.classList.remove('saving');
            saveBadge.textContent = 'All changes saved';
            // Update URL without reload
            if (r.id && !location.search.includes('id=')) {
                window.history.replaceState(null, '', '?id=' + r.id);
            }
        } catch (err) {
            saveBadge.textContent = 'Save failed';
            console.error(err);
        }
    }
    window.forceSave = forceSave;

    window.addEventListener('beforeunload', (e) => {
        if (state.dirty) { e.preventDefault(); e.returnValue = ''; }
    });

    function escapeHtml(s) {
        return String(s || '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    }

    init();
})();
