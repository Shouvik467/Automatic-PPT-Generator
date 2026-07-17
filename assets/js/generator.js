/* Presentation generator — Step 1 (form) + orchestration for outline + build */
(function () {
    if (!document.body.classList.contains('page-generator')) return;

    const BASE = window.APP_BASE_URL;
    const state = {
        form: null,
        outline: null,   // { title, subtitle, slides:[...] , meta }
        presentation: null,
    };
    window.__genState = state;

    // ------------- Theme chip picker -------------
    document.querySelectorAll('#themePicker .theme-chip').forEach(chip => {
        chip.addEventListener('click', () => {
            document.querySelectorAll('#themePicker .theme-chip').forEach(c => c.classList.remove('active'));
            chip.classList.add('active');
            chip.querySelector('input').checked = true;
        });
    });
    document.querySelectorAll('#stylePicker .chip').forEach(chip => {
        chip.addEventListener('click', () => {
            document.querySelectorAll('#stylePicker .chip').forEach(c => c.classList.remove('active'));
            chip.classList.add('active');
            chip.querySelector('input').checked = true;
        });
    });

    // ------------- Custom slide count -------------
    const slideSel = document.getElementById('fSlideCount');
    const slideCustom = document.getElementById('fSlideCustom');
    slideSel.addEventListener('change', () => {
        if (slideSel.value === 'custom') {
            slideCustom.classList.remove('hidden');
            slideCustom.focus();
        } else {
            slideCustom.classList.add('hidden');
        }
    });

    // ------------- Prefill topic from ?topic= -------------
    const params = new URLSearchParams(location.search);
    const preTopic = params.get('topic') || localStorage.getItem('pendingTopic') || '';
    if (preTopic) document.getElementById('fTopic').value = preTopic;
    localStorage.removeItem('pendingTopic');

    // ------------- Reset button -------------
    document.getElementById('btnReset').addEventListener('click', () => {
        document.getElementById('genForm').reset();
        document.querySelectorAll('#themePicker .theme-chip').forEach(c => c.classList.remove('active'));
        document.querySelector('#themePicker .theme-chip[data-theme="modern"]')?.classList.add('active');
        document.querySelectorAll('#stylePicker .chip').forEach(c => c.classList.remove('active'));
        document.querySelector('#stylePicker .chip[data-style="professional"]')?.classList.add('active');
    });

    // ------------- Read form -------------
    function readForm() {
        const themeInput = document.querySelector('input[name="theme"]:checked');
        const styleInput = document.querySelector('input[name="imageStyle"]:checked');
        let count = slideSel.value;
        if (count === 'custom') count = slideCustom.value || 10;
        count = Math.max(3, Math.min(30, parseInt(count, 10) || 10));

        return {
            topic:       document.getElementById('fTopic').value.trim(),
            title:       document.getElementById('fTitle').value.trim(),
            description: document.getElementById('fDesc').value.trim(),
            slideCount:  count,
            type:        document.getElementById('fType').value,
            tone:        document.getElementById('fTone').value,
            audience:    document.getElementById('fAudience').value,
            language:    document.getElementById('fLanguage').value,
            aspect:      document.getElementById('fAspect').value,
            theme:       themeInput ? themeInput.value : 'modern',
            imageStyle:  styleInput ? styleInput.value : 'professional',
            author:      document.getElementById('fAuthor').value.trim(),
            organization:document.getElementById('fOrg').value.trim(),
            autoImages:  document.getElementById('fAutoImages').checked,
        };
    }

    // ------------- Step navigation -------------
    function showPanel(name) {
        ['Form','Outline','Build','Ready'].forEach(n => {
            document.getElementById('panel' + n).classList.toggle('hidden', n !== name);
        });
        const map = { Form: 1, Outline: 2, Build: 3, Ready: 4 };
        document.querySelectorAll('.si-step').forEach(s => {
            const n = parseInt(s.dataset.step, 10);
            s.classList.remove('active', 'done');
            if (n < map[name]) s.classList.add('done');
            if (n === map[name]) s.classList.add('active');
        });
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    // ------------- STEP 1 → generate outline -------------
    document.getElementById('genForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const data = readForm();
        if (!data.topic) { toast('Please enter a topic', 'error'); return; }
        state.form = data;

        const btn = document.getElementById('btnGenerateOutline');
        const original = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-inline"></span> Generating outline…';

        try {
            const j = await API.post('/api/generate-outline.php', data);
            state.outline = j.outline;
            window.OutlineUI.render(state.outline);
            showPanel('Outline');
            toast('Outline ready — review, edit, or regenerate', 'success');
        } catch (err) {
            toast('Failed: ' + err.message, 'error', 5000);
        } finally {
            btn.disabled = false;
            btn.innerHTML = original;
        }
    });

    // Back / regenerate / add slide are handled in outline.js
    document.getElementById('btnBackToForm').addEventListener('click', () => showPanel('Form'));
    document.getElementById('btnRegenOutline').addEventListener('click', async () => {
        const btn = document.getElementById('btnRegenOutline');
        btn.disabled = true; btn.textContent = 'Regenerating…';
        try {
            const j = await API.post('/api/generate-outline.php', state.form);
            state.outline = j.outline;
            window.OutlineUI.render(state.outline);
            toast('Outline regenerated', 'success');
        } catch (err) {
            toast('Regenerate failed: ' + err.message, 'error');
        } finally {
            btn.disabled = false; btn.textContent = '🔁 Regenerate outline';
        }
    });

    // ------------- STEP 2 → build -------------
    let buildAborted = false;
    document.getElementById('btnStartBuild').addEventListener('click', async () => {
        // Pull latest outline from UI
        state.outline = window.OutlineUI.getOutline();
        if (!state.outline || !state.outline.slides.length) {
            toast('No slides in outline', 'error'); return;
        }

        showPanel('Build');
        buildAborted = false;
        const total = state.outline.slides.length;
        document.getElementById('buildTotal').textContent = total;
        document.getElementById('buildDone').textContent = 0;
        document.getElementById('buildProgress').style.width = '0%';
        document.getElementById('buildLog').innerHTML = '';

        // Prepare presentation object
        const pres = {
            id: '', // assigned by server on save
            title: state.outline.title,
            subtitle: state.outline.subtitle,
            topic: state.form.topic,
            theme: state.form.theme,
            imageStyle: state.form.imageStyle,
            aspect: state.form.aspect,
            author: state.form.author,
            organization: state.form.organization,
            meta: state.form,
            slides: [],
        };

        for (let i = 0; i < total; i++) {
            if (buildAborted) break;
            const outlineSlide = state.outline.slides[i];
            logBuild(`Writing content for slide ${i+1}: ${outlineSlide.title}`);
            let content = null;
            try {
                const cRes = await API.post('/api/generate-content.php', {
                    topic:    state.form.topic,
                    audience: state.form.audience,
                    tone:     state.form.tone,
                    language: state.form.language,
                    slide:    outlineSlide,
                });
                content = cRes.content;
                logBuild(`  ✓ Content OK`, 'ok');
            } catch (err) {
                logBuild(`  ✗ Content failed: ${err.message}`, 'err');
                content = {
                    title: outlineSlide.title,
                    subtitle: '',
                    paragraph: outlineSlide.summary,
                    bullets: [], stats: [], quote: {text:'',author:''},
                    comparison:{left_title:'',left:[],right_title:'',right:[]},
                    timeline:[], process:[], takeaways:[],
                    notes: '', image_prompt: outlineSlide.image_prompt,
                };
            }

            // Image URL (Pollinations returns a URL immediately)
            let imageUrl = '';
            if (state.form.autoImages && !['title-subtitle','quote','section-divider','takeaways','thank-you'].includes(outlineSlide.layout)) {
                try {
                    const iRes = await API.post('/api/generate-image.php', {
    prompt: content.image_prompt || outlineSlide.image_prompt,
    title: content.title,
    topic: state.form.topic,
    style: state.form.imageStyle,
    aspect: state.form.aspect,
    download: true
});
                    imageUrl = iRes.url;
                    logBuild(`  ✓ Image ready`, 'ok');
                } catch (err) {
                    logBuild(`  ✗ Image failed: ${err.message}`, 'err');
                }
            }

            pres.slides.push({
                id: uid(),
                index: i + 1,
                layout: outlineSlide.layout,
                title: content.title,
                subtitle: content.subtitle,
                paragraph: content.paragraph,
                bullets: content.bullets || [],
                stats: content.stats || [],
                quote: content.quote || {text:'',author:''},
                comparison: content.comparison || {left_title:'',left:[],right_title:'',right:[]},
                timeline: content.timeline || [],
                process: content.process || [],
                takeaways: content.takeaways || [],
                notes: content.notes || '',
                image: imageUrl,
                imagePrompt: content.image_prompt || outlineSlide.image_prompt,
                transition: 'fade',
                duration: 6,
                background: '',
                footer: state.form.organization || '',
                showNumber: true,
            });

            document.getElementById('buildDone').textContent = i + 1;
            document.getElementById('buildProgress').style.width = ((i + 1) / total * 100) + '%';
        }

        if (buildAborted) {
            toast('Generation cancelled', 'info');
            showPanel('Form');
            return;
        }

        // Save presentation
        try {
            const saved = await API.post('/api/save-presentation.php', pres);
            pres.id = saved.id;
            state.presentation = pres;
            // Store id in localStorage as recent
            const recent = JSON.parse(localStorage.getItem('recentPresentations') || '[]');
            recent.unshift({ id: pres.id, title: pres.title, at: Date.now() });
            localStorage.setItem('recentPresentations', JSON.stringify(recent.slice(0, 20)));

            document.getElementById('readySub').textContent = `${pres.title} — ${pres.slides.length} slides`;
            document.getElementById('btnOpenEditor').href = BASE + '/editor.php?id=' + pres.id;
            document.getElementById('btnOpenSlideshow').href = BASE + '/slideshow.php?id=' + pres.id;
            showPanel('Ready');
            toast('Presentation ready!', 'success');
        } catch (err) {
            logBuild('Save failed: ' + err.message, 'err');
            toast('Could not save: ' + err.message, 'error');
        }
    });

    document.getElementById('btnCancelBuild').addEventListener('click', () => {
        buildAborted = true;
    });

    function logBuild(text, cls = '') {
        const box = document.getElementById('buildLog');
        const line = document.createElement('div');
        if (cls) line.className = cls;
        line.textContent = text;
        box.appendChild(line);
        box.scrollTop = box.scrollHeight;
    }
})();
