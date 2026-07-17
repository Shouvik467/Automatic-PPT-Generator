/* Export handlers: PPTX (PptxGenJS), PDF (jsPDF+html2canvas), PNG, JSON, print. */
(function () {
    if (!document.body.classList.contains('page-editor')) return;

    document.querySelectorAll('#exportMenu [data-export]').forEach(btn => {
        btn.addEventListener('click', async () => {
            const type = btn.dataset.export;
            document.getElementById('exportMenu').closest('.tb-menu').classList.remove('open');
            if (window.forceSave) await window.forceSave();
            const s = window.__editorState;
            if (!s || !s.presentation) return;
            try {
                if (type === 'pptx')  await exportPPTX(s.presentation);
                if (type === 'pdf')   await exportPDF(s.presentation);
                if (type === 'png')   await exportPNG(s.presentation);
                if (type === 'json')  exportJSON(s.presentation);
                if (type === 'print') printDeck(s.presentation);
            } catch (err) {
                toast('Export failed: ' + err.message, 'error', 5000);
                console.error(err);
            }
        });
    });

    // ---------- PPTX ----------
    async function exportPPTX(pres) {
        if (typeof PptxGenJS === 'undefined') return toast('PptxGenJS not loaded', 'error');
        toast('Building PowerPoint…', 'info');
        const pptx = new PptxGenJS();
        pptx.title = pres.title || 'Presentation';
        pptx.company = pres.organization || '';
        pptx.subject = pres.topic || '';
        pptx.layout = pres.aspect === '4:3' ? 'LAYOUT_4x3' :
                      pres.aspect === '1:1' ? 'LAYOUT_WIDE' :
                      pres.aspect === '9:16'? 'LAYOUT_WIDE' : 'LAYOUT_WIDE';

        const THEME = themeColors(pres.theme);
        for (let i = 0; i < pres.slides.length; i++) {
            const s = pres.slides[i];
            const slide = pptx.addSlide();
            slide.background = { color: THEME.bg.replace('#','') };
            const layout = s.layout || 'bullets';

            // Title slide / thank-you = big centered
            if (layout === 'title' || layout === 'thank-you' || layout === 'section-divider') {
                slide.background = { color: THEME.accent.replace('#','') };
                slide.addText(s.title || '', {
                    x: 0.5, y: 2.4, w: 12, h: 1.8,
                    fontSize: 44, bold: true, color: 'FFFFFF', align: 'center',
                    fontFace: 'Calibri'
                });
                if (s.subtitle) slide.addText(s.subtitle, {
                    x: 0.5, y: 4.4, w: 12, h: 0.6,
                    fontSize: 22, color: 'FFFFFFCC', align: 'center'
                });
                continue;
            }

            // Common title
            slide.addText(s.title || '', {
                x: 0.5, y: 0.35, w: 12, h: 0.9,
                fontSize: 32, bold: true, color: THEME.title.replace('#',''), fontFace: 'Calibri'
            });
            if (s.subtitle) slide.addText(s.subtitle, {
                x: 0.5, y: 1.1, w: 12, h: 0.5,
                fontSize: 18, color: THEME.body.replace('#',''), italic: true
            });

            const hasImage = !!s.image && ['text-image','image-text','full-image','image-overlay'].includes(layout);

            // Body content
            if (['text-image','image-text'].includes(layout)) {
                const textX = layout === 'text-image' ? 0.5 : 6.7;
                const imgX  = layout === 'text-image' ? 6.7 : 0.5;
                await addImageToSlide(slide, s.image, imgX, 1.6, 6.0, 4.6);
                addBullets(slide, s, textX, 1.6, 6.0);
            } else if (layout === 'full-image') {
                await addImageToSlide(slide, s.image, 0.5, 1.6, 12.3, 5.2);
            } else if (layout === 'image-overlay') {
                await addImageToSlide(slide, s.image, 0, 0, 13.3, 7.5);
                slide.addShape('rect', { x: 0, y: 0, w: 13.3, h: 7.5, fill: { color: '000000', transparency: 55 } });
                slide.addText(s.title || '', { x: 0.6, y: 5.4, w: 12, h: 1, fontSize: 34, bold: true, color: 'FFFFFF' });
                if (s.subtitle) slide.addText(s.subtitle, { x: 0.6, y: 6.3, w: 12, h: 0.5, fontSize: 18, color: 'FFFFFFCC' });
            } else if (layout === 'statistics') {
                const st = s.stats || [];
                const n = Math.max(1, st.length);
                const w = 12 / n;
                st.forEach((x, k) => {
                    slide.addShape('roundRect', {
                        x: 0.5 + k * w + 0.1, y: 2.0, w: w - 0.2, h: 3.0,
                        fill: { color: hexBrighten(THEME.accent, 92) }, line: { color: THEME.accent.replace('#',''), width: 1 },
                        rectRadius: 0.2
                    });
                    slide.addText(x.value || '', {
                        x: 0.5 + k * w + 0.1, y: 2.2, w: w - 0.2, h: 1.4,
                        fontSize: 48, bold: true, color: THEME.accent.replace('#',''), align: 'center'
                    });
                    slide.addText(x.label || '', {
                        x: 0.5 + k * w + 0.1, y: 3.8, w: w - 0.2, h: 1.0,
                        fontSize: 14, color: THEME.body.replace('#',''), align: 'center'
                    });
                });
            } else if (layout === 'comparison' || layout === 'two-column') {
                const c = s.comparison || {};
                const boxes = [
                    { title: c.left_title  || 'Column A', items: c.left  || [], x: 0.5 },
                    { title: c.right_title || 'Column B', items: c.right || [], x: 6.9 },
                ];
                boxes.forEach(box => {
                    slide.addShape('roundRect', { x: box.x, y: 1.7, w: 6.0, h: 5.1, fill: { color: hexBrighten(THEME.accent, 94) }, line: { color: 'EEEEEE' }, rectRadius: 0.15 });
                    slide.addText(box.title, { x: box.x + 0.2, y: 1.85, w: 5.6, h: 0.6, fontSize: 20, bold: true, color: THEME.accent.replace('#','') });
                    slide.addText(box.items.map(t => ({ text: t, options: { bullet: true } })), {
                        x: box.x + 0.3, y: 2.5, w: 5.5, h: 4.2, fontSize: 14, color: THEME.body.replace('#','')
                    });
                });
            } else if (layout === 'quote') {
                slide.addText(`"${(s.quote && s.quote.text) || s.title || ''}"`, {
                    x: 1, y: 2.2, w: 11.3, h: 2.8, fontSize: 30, italic: true, bold: true,
                    color: THEME.title.replace('#',''), align: 'center'
                });
                if (s.quote && s.quote.author) slide.addText('— ' + s.quote.author, {
                    x: 1, y: 5.2, w: 11.3, h: 0.6, fontSize: 16, color: THEME.body.replace('#',''), align: 'center'
                });
            } else if (layout === 'takeaways') {
                const tks = (s.takeaways && s.takeaways.length) ? s.takeaways : (s.bullets || []);
                tks.slice(0, 6).forEach((t, k) => {
                    const col = k % 2;
                    const row = Math.floor(k / 2);
                    slide.addShape('roundRect', {
                        x: 0.5 + col * 6.4, y: 1.7 + row * 1.7, w: 6.0, h: 1.5,
                        fill: { color: hexBrighten(THEME.accent, 94) },
                        line: { color: THEME.accent.replace('#',''), width: 2 },
                        rectRadius: 0.15
                    });
                    slide.addText(t, {
                        x: 0.7 + col * 6.4, y: 1.85 + row * 1.7, w: 5.6, h: 1.2,
                        fontSize: 15, color: THEME.body.replace('#','')
                    });
                });
            } else {
                // Default: bullets
                addBullets(slide, s, 0.6, 1.6, 12.1);
            }

            // Footer
            if (s.footer)  slide.addText(s.footer,   { x: 0.5, y: 7.0, w: 8, h: 0.3, fontSize: 10, color: '888888' });
            if (s.showNumber !== false) slide.addText(`${i+1} / ${pres.slides.length}`, { x: 11, y: 7.0, w: 2, h: 0.3, fontSize: 10, color: '888888', align: 'right' });
        }

        await pptx.writeFile({ fileName: slugify(pres.title) + '.pptx' });
        toast('PowerPoint downloaded', 'success');
    }

    function addBullets(slide, s, x, y, w) {
        const items = [];
        if (s.paragraph) items.push({ text: s.paragraph, options: { fontSize: 16 } });
        (s.bullets || []).forEach(b => items.push({ text: b, options: { bullet: true, fontSize: 18 } }));
        if (items.length) slide.addText(items, { x, y, w, h: 5.4, color: '333333' });
    }

    async function addImageToSlide(slide, url, x, y, w, h) {
        if (!url) return;
        try {
            let dataUrl = url;
            if (url.startsWith('http') || url.startsWith('/')) {
                dataUrl = await urlToDataUrl(url);
            }
            slide.addImage({ data: dataUrl, x, y, w, h });
        } catch (e) {
            console.warn('image embed failed', e);
        }
    }

    function urlToDataUrl(url) {
        return new Promise((resolve, reject) => {
            const img = new Image();
            img.crossOrigin = 'anonymous';
            img.onload = () => {
                const c = document.createElement('canvas');
                c.width = img.naturalWidth; c.height = img.naturalHeight;
                c.getContext('2d').drawImage(img, 0, 0);
                try { resolve(c.toDataURL('image/jpeg', 0.9)); }
                catch (e) { reject(e); }
            };
            img.onerror = () => reject(new Error('image load failed'));
            img.src = url + (url.includes('?') ? '&' : '?') + 'cors=1';
        });
    }

    // ---------- PDF ----------
    async function exportPDF(pres) {
        if (!window.jspdf || typeof html2canvas === 'undefined') return toast('PDF libraries not loaded', 'error');
        toast('Building PDF…', 'info');
        const { jsPDF } = window.jspdf;
        const [pw, ph] = pres.aspect === '4:3' ? [1024, 768] :
                         pres.aspect === '1:1' ? [1024, 1024] :
                         pres.aspect === '9:16'? [720, 1280] : [1280, 720];
        const orient = pw >= ph ? 'landscape' : 'portrait';
        const pdf = new jsPDF({ orientation: orient, unit: 'px', format: [pw, ph] });

        const holder = document.createElement('div');
        holder.style.position = 'fixed'; holder.style.left = '-99999px';
        holder.style.width = pw + 'px'; holder.style.height = ph + 'px';
        document.body.appendChild(holder);

        for (let i = 0; i < pres.slides.length; i++) {
            holder.innerHTML = window.SlideRenderer.html(pres.slides[i], pres.theme, { index: i, total: pres.slides.length, editable: false });
            const slideEl = holder.firstElementChild;
            slideEl.style.width = pw + 'px'; slideEl.style.height = ph + 'px';
            await new Promise(r => setTimeout(r, 250)); // let images load
            const canvas = await html2canvas(slideEl, { useCORS: true, scale: 1.4, backgroundColor: null });
            const dataUrl = canvas.toDataURL('image/jpeg', 0.9);
            if (i > 0) pdf.addPage([pw, ph], orient);
            pdf.addImage(dataUrl, 'JPEG', 0, 0, pw, ph);
        }
        document.body.removeChild(holder);
        pdf.save(slugify(pres.title) + '.pdf');
        toast('PDF downloaded', 'success');
    }

    // ---------- PNG (each slide) ----------
    async function exportPNG(pres) {
        if (typeof html2canvas === 'undefined') return toast('html2canvas not loaded', 'error');
        toast('Rendering PNGs…', 'info');
        const [pw, ph] = pres.aspect === '4:3' ? [1024, 768] :
                         pres.aspect === '1:1' ? [1024, 1024] :
                         pres.aspect === '9:16'? [720, 1280] : [1280, 720];
        const holder = document.createElement('div');
        holder.style.position = 'fixed'; holder.style.left = '-99999px';
        holder.style.width = pw + 'px'; holder.style.height = ph + 'px';
        document.body.appendChild(holder);

        for (let i = 0; i < pres.slides.length; i++) {
            holder.innerHTML = window.SlideRenderer.html(pres.slides[i], pres.theme, { index: i, total: pres.slides.length, editable: false });
            const el = holder.firstElementChild;
            el.style.width = pw + 'px'; el.style.height = ph + 'px';
            await new Promise(r => setTimeout(r, 250));
            const canvas = await html2canvas(el, { useCORS: true, scale: 1.5, backgroundColor: null });
            const a = document.createElement('a');
            a.href = canvas.toDataURL('image/png');
            a.download = `${slugify(pres.title)}-slide-${String(i+1).padStart(2,'0')}.png`;
            a.click();
            await new Promise(r => setTimeout(r, 120));
        }
        document.body.removeChild(holder);
        toast('PNG slides downloaded', 'success');
    }

    // ---------- JSON ----------
    function exportJSON(pres) {
        const blob = new Blob([JSON.stringify(pres, null, 2)], { type: 'application/json' });
        const a = document.createElement('a');
        a.href = URL.createObjectURL(blob);
        a.download = slugify(pres.title) + '.json';
        a.click();
        toast('JSON downloaded', 'success');
    }

    // ---------- Print ----------
    function printDeck(pres) {
        const w = window.open('', '_blank');
        if (!w) return toast('Popup blocked', 'error');
        const styles = Array.from(document.styleSheets).map(s => {
            try { return `<link rel="stylesheet" href="${s.href}">`; } catch (e) { return ''; }
        }).join('');
        const body = pres.slides.map((s, i) =>
            `<div class="print-slide">${window.SlideRenderer.html(s, pres.theme, { index: i, total: pres.slides.length })}</div>`
        ).join('');
        w.document.write(`<!DOCTYPE html><html><head><title>${pres.title}</title>${styles}
            <style>
              @page { size: landscape; margin: 0; }
              body { margin: 0; }
              .print-slide { width: 100vw; height: 100vh; page-break-after: always; overflow: hidden; }
              .print-slide .slide { width: 100%; height: 100%; }
            </style></head><body>${body}</body></html>`);
        w.document.close();
        setTimeout(() => { w.focus(); w.print(); }, 600);
    }

    // ---------- Helpers ----------
    function themeColors(t) {
        const map = {
            modern:      { bg:'#ffffff', accent:'#667eea', title:'#1e1b4b', body:'#374151' },
            corporate:   { bg:'#ffffff', accent:'#0f4c81', title:'#0b1220', body:'#374151' },
            minimal:     { bg:'#ffffff', accent:'#111827', title:'#111827', body:'#4b5563' },
            futuristic:  { bg:'#ffffff', accent:'#0ea5e9', title:'#0c1b3e', body:'#374151' },
            tech:        { bg:'#0b1120', accent:'#0ea5e9', title:'#e2e8f0', body:'#cbd5e1' },
            creative:    { bg:'#ffffff', accent:'#ec4899', title:'#1f0b1c', body:'#4b5563' },
            educational: { bg:'#ffffff', accent:'#f59e0b', title:'#1c1917', body:'#374151' },
            luxury:      { bg:'#0d0d0d', accent:'#c9a227', title:'#f4f4f5', body:'#e5e5e5' },
            'dark-pro':  { bg:'#0f172a', accent:'#a78bfa', title:'#e2e8f0', body:'#cbd5e1' },
            clean:       { bg:'#ffffff', accent:'#111827', title:'#111827', body:'#374151' },
            gradient:    { bg:'#fff5f2', accent:'#f43f5e', title:'#1f0b1c', body:'#374151' },
            glass:       { bg:'#eff6ff', accent:'#a855f7', title:'#1f0b1c', body:'#374151' },
            nature:      { bg:'#ecfdf5', accent:'#10b981', title:'#064e3b', body:'#065f46' },
            custom:      { bg:'#ffffff', accent:'#6366f1', title:'#111827', body:'#374151' },
        };
        return map[t] || map.modern;
    }
    function hexBrighten(hex, pct) {
        // Return a hex color of the same hue but lighter, for PPTX backgrounds
        hex = hex.replace('#','');
        let r = parseInt(hex.slice(0,2),16), g = parseInt(hex.slice(2,4),16), b = parseInt(hex.slice(4,6),16);
        r = Math.round(r + (255 - r) * (pct / 100));
        g = Math.round(g + (255 - g) * (pct / 100));
        b = Math.round(b + (255 - b) * (pct / 100));
        return [r,g,b].map(x => x.toString(16).padStart(2,'0')).join('');
    }
})();
