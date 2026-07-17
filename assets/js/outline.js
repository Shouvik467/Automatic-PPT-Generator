/* Outline UI: edit titles/summaries, reorder, add/delete, per-slide regenerate. */
(function () {
    if (!document.body.classList.contains('page-generator')) return;

    const LAYOUTS = ['title','title-subtitle','bullets','text-image','image-text','two-column','full-image','image-overlay','quote','statistics','comparison','timeline','process','features','problem-solution','section-divider','takeaways','thank-you'];

    const listEl = document.getElementById('outlineList');
    let outline = null;

    function render(o) {
        outline = o;
        document.getElementById('outlineTitle').textContent    = o.title || 'Your Outline';
        document.getElementById('outlineSubtitle').textContent = o.subtitle || '';
        listEl.innerHTML = '';
        o.slides.forEach((s, i) => listEl.appendChild(rowFor(s, i)));
    }

    function rowFor(slide, idx) {
        const row = document.createElement('div');
        row.className = 'outline-slide';
        row.draggable = true;
        row.dataset.idx = idx;
        row.innerHTML = `
            <div class="os-num" title="Drag to reorder">${idx + 1}</div>
            <div class="os-body">
                <input class="os-title" value="${escapeHtml(slide.title)}" placeholder="Slide title">
                <textarea class="os-summary" rows="2" placeholder="Slide summary">${escapeHtml(slide.summary)}</textarea>
                <div class="os-meta">
                    <select class="os-layout">
                        ${LAYOUTS.map(l => `<option value="${l}" ${l===slide.layout?'selected':''}>${l}</option>`).join('')}
                    </select>
                    <input class="os-imgprompt" placeholder="Image prompt (optional)" value="${escapeHtml(slide.image_prompt)}">
                </div>
            </div>
            <div class="os-actions">
                <button title="Move up"  data-act="up">↑</button>
                <button title="Move down" data-act="down">↓</button>
                <button title="Duplicate" data-act="dup">⧉</button>
                <button title="Regenerate one" data-act="regen">✨</button>
                <button title="Delete" data-act="del">✕</button>
            </div>
        `;

        // Input syncing
        row.querySelector('.os-title')    .addEventListener('input', e => slide.title = e.target.value);
        row.querySelector('.os-summary')  .addEventListener('input', e => slide.summary = e.target.value);
        row.querySelector('.os-layout')   .addEventListener('change', e => slide.layout = e.target.value);
        row.querySelector('.os-imgprompt').addEventListener('input', e => slide.image_prompt = e.target.value);

        // Actions
        row.querySelectorAll('.os-actions button').forEach(b => {
            b.addEventListener('click', () => handleAction(b.dataset.act, idx));
        });

        // Drag & drop
        row.addEventListener('dragstart', e => {
            row.classList.add('dragging');
            e.dataTransfer.setData('text/plain', idx);
        });
        row.addEventListener('dragend', () => row.classList.remove('dragging'));
        row.addEventListener('dragover', e => e.preventDefault());
        row.addEventListener('drop', e => {
            e.preventDefault();
            const from = parseInt(e.dataTransfer.getData('text/plain'), 10);
            const to = idx;
            if (isNaN(from) || from === to) return;
            const [moved] = outline.slides.splice(from, 1);
            outline.slides.splice(to, 0, moved);
            renumber(); render(outline);
        });

        return row;
    }

    async function handleAction(act, idx) {
        const slide = outline.slides[idx];
        if (act === 'up' && idx > 0) {
            [outline.slides[idx-1], outline.slides[idx]] = [outline.slides[idx], outline.slides[idx-1]];
            renumber(); render(outline);
        }
        if (act === 'down' && idx < outline.slides.length - 1) {
            [outline.slides[idx+1], outline.slides[idx]] = [outline.slides[idx], outline.slides[idx+1]];
            renumber(); render(outline);
        }
        if (act === 'del' && outline.slides.length > 1) {
            outline.slides.splice(idx, 1); renumber(); render(outline);
        }
        if (act === 'dup') {
            const copy = JSON.parse(JSON.stringify(slide));
            outline.slides.splice(idx + 1, 0, copy); renumber(); render(outline);
        }
        if (act === 'regen') {
            // Ask the AI for a new title + summary + prompt for this one slide
            try {
                const form = window.__genState.form;
                const body = {
                    topic:    form.topic,
                    audience: form.audience,
                    tone:     form.tone,
                    language: form.language,
                    slide: {
                        title: slide.title,
                        summary: slide.summary,
                        layout: slide.layout,
                        image_prompt: slide.image_prompt,
                    }
                };
                const r = await API.post('/api/generate-content.php', body);
                slide.title = r.content.title || slide.title;
                slide.summary = r.content.paragraph || r.content.bullets?.join(' · ') || slide.summary;
                slide.image_prompt = r.content.image_prompt || slide.image_prompt;
                render(outline);
                toast('Slide refreshed', 'success');
            } catch (err) {
                toast('Regenerate failed: ' + err.message, 'error');
            }
        }
    }

    function renumber() {
        outline.slides.forEach((s, i) => s.index = i + 1);
    }

    document.getElementById('btnAddOutlineSlide').addEventListener('click', () => {
        if (!outline) return;
        outline.slides.push({
            index: outline.slides.length + 1,
            title: 'New Slide',
            purpose: '',
            summary: '',
            layout: 'bullets',
            image_prompt: '',
        });
        render(outline);
    });

    function escapeHtml(s) {
        return String(s || '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    }

    window.OutlineUI = {
        render,
        getOutline: () => outline,
    };
})();
