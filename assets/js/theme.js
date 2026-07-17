/* Theme (light/dark) — persisted in localStorage, respects OS preference. */
(function () {
    const KEY = 'appTheme';
    const btn = document.getElementById('themeToggle');

    function apply(theme) {
        document.documentElement.setAttribute('data-theme', theme);
        localStorage.setItem(KEY, theme);
    }

    function current() {
        return document.documentElement.getAttribute('data-theme') || 'light';
    }

    if (btn) {
        btn.addEventListener('click', () => {
            const next = current() === 'dark' ? 'light' : 'dark';
            apply(next);
        });
    }

    // React to system changes ONLY when user has not set a preference explicitly.
    // (We record explicit choice above; if none saved, initial value came from OS pref.)
    if (window.matchMedia) {
        const mm = window.matchMedia('(prefers-color-scheme: dark)');
        try {
            mm.addEventListener('change', (e) => {
                if (!localStorage.getItem(KEY)) {
                    apply(e.matches ? 'dark' : 'light');
                }
            });
        } catch (_) {}
    }
})();
