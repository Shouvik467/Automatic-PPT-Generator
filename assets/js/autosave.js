/* Periodic autosave + draft recovery.
   Editor already saves after every change; this file adds:
   - a heartbeat save every 20s
   - draft recovery on load (offer to restore local draft if server copy is older).
*/
(function () {
    if (!document.body.classList.contains('page-editor')) return;

    setInterval(() => {
        if (window.__editorState && window.__editorState.dirty && window.forceSave) {
            window.forceSave();
        }
    }, 20000);

    // Attempt draft recovery once state is loaded
    let checked = false;
    const iv = setInterval(() => {
        if (checked) { clearInterval(iv); return; }
        const s = window.__editorState;
        if (!s || !s.presentation) return;
        checked = true;
        const key = 'draft_' + (s.presentation.id || 'new');
        try {
            const draft = localStorage.getItem(key);
            if (!draft) return;
            const d = JSON.parse(draft);
            if (d && d.updated_at && s.presentation.updated_at && d.updated_at > s.presentation.updated_at) {
                showModal({
                    title: 'Restore unsaved changes?',
                    body: '<p>We found unsaved edits from your last session. Restore them?</p>',
                    confirmText: 'Restore',
                    cancelText: 'Discard',
                    onConfirm: () => {
                        Object.assign(s.presentation, d);
                        location.reload();
                    }
                });
            }
        } catch (_) {}
    }, 500);
})();
