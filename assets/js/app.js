/* Global app utilities — toasts, modals, API helper, nav, API status. */
(function () {
    const BASE = window.APP_BASE_URL || '';

    // ----------------- API helper -----------------
    window.API = {
        async post(path, body) {
            const r = await fetch(BASE + path, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(body || {})
            });
            let json;
            try { json = await r.json(); } catch (e) { json = { success: false, error: 'Invalid response' }; }
            if (!json.success) throw new Error(json.error || ('HTTP ' + r.status));
            return json;
        },
        async get(path) {
            const r = await fetch(BASE + path);
            let json;
            try { json = await r.json(); } catch (e) { json = { success: false, error: 'Invalid response' }; }
            if (!json.success) throw new Error(json.error || ('HTTP ' + r.status));
            return json;
        }
    };

    // ----------------- Toasts -----------------
    const toastRoot = document.getElementById('toastRoot');
    window.toast = function (msg, type = 'info', duration = 3200) {
        if (!toastRoot) return alert(msg);
        const el = document.createElement('div');
        el.className = 'toast ' + type;
        el.textContent = msg;
        toastRoot.appendChild(el);
        setTimeout(() => {
            el.style.transition = 'opacity .3s, transform .3s';
            el.style.opacity = '0';
            el.style.transform = 'translateY(10px)';
            setTimeout(() => el.remove(), 320);
        }, duration);
    };

    // ----------------- Modal -----------------
    const mroot = document.getElementById('modalRoot');
    window.showModal = function ({ title, body, confirmText = 'OK', cancelText = 'Cancel', onConfirm }) {
        if (!mroot) return;
        mroot.innerHTML = `
            <div class="modal-backdrop"></div>
            <div class="modal-dialog">
                <h3>${title}</h3>
                <div>${body || ''}</div>
                <div class="modal-actions">
                    <button class="btn btn-ghost" data-close>${cancelText}</button>
                    <button class="btn btn-primary" data-confirm>${confirmText}</button>
                </div>
            </div>`;
        mroot.classList.add('open');
        const close = () => { mroot.classList.remove('open'); setTimeout(() => mroot.innerHTML = '', 300); };
        mroot.querySelector('.modal-backdrop').addEventListener('click', close);
        mroot.querySelector('[data-close]').addEventListener('click', close);
        mroot.querySelector('[data-confirm]').addEventListener('click', async () => {
            try { if (onConfirm) await onConfirm(); } finally { close(); }
        });
    };

    // ----------------- Mobile menu -----------------
    const mBtn = document.getElementById('mobileMenu');
    const nav  = document.getElementById('navLinks');
    if (mBtn && nav) mBtn.addEventListener('click', () => nav.classList.toggle('open'));

    // ----------------- API status -----------------
    const apiStatus = document.getElementById('apiStatus');
    if (apiStatus) {
        fetch(BASE + '/api/test-api.php')
            .then(r => r.json())
            .then(j => {
                if (j.success && j.reachable) {
                    apiStatus.classList.add('ok');
                    apiStatus.querySelector('.status-text').textContent = 'Pollinations AI online' + (j.latency_ms ? ` (${j.latency_ms}ms)` : '');
                } else {
                    apiStatus.classList.add('err');
                    apiStatus.querySelector('.status-text').textContent = 'API unreachable';
                }
            })
            .catch(() => {
                apiStatus.classList.add('err');
                apiStatus.querySelector('.status-text').textContent = 'API unreachable';
            });
    }

    // ----------------- Simple ID helpers -----------------
    window.uid = function () { return Math.random().toString(36).slice(2, 10); };

    // Slugify title for filenames
    window.slugify = function (s) {
        return String(s || 'presentation').toLowerCase().trim()
            .replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '').slice(0, 60) || 'presentation';
    };
})();
