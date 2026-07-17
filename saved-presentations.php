<?php
$page = 'saved';
$pageTitle = 'My Presentations';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/functions.php';
$list = list_presentations();
?>
<section class="saved-wrap">
    <div class="container">
        <div class="saved-head">
            <div>
                <h1>My Presentations</h1>
                <p class="muted">All presentations saved on this server as JSON files.</p>
            </div>
            <div class="saved-actions">
                <input type="text" id="savedSearch" placeholder="Search…">
                <input type="file" id="importJson" accept="application/json" hidden>
                <button class="btn btn-ghost" onclick="document.getElementById('importJson').click()">Import JSON</button>
                <a href="<?php echo BASE_URL; ?>/generator.php" class="btn btn-primary">+ New</a>
            </div>
        </div>

        <?php if (empty($list)): ?>
        <div class="empty-state">
            <div class="empty-illust">📭</div>
            <h3>No presentations yet</h3>
            <p>Create your first presentation and it will appear here.</p>
            <a href="<?php echo BASE_URL; ?>/generator.php" class="btn btn-primary">Create Presentation</a>
        </div>
        <?php else: ?>
        <div class="saved-grid" id="savedGrid">
            <?php foreach ($list as $p): ?>
            <div class="saved-card" data-title="<?php echo htmlspecialchars(strtolower($p['title'].' '.$p['topic'])); ?>">
                <a class="sc-thumb" href="<?php echo BASE_URL; ?>/editor.php?id=<?php echo urlencode($p['id']); ?>">
                    <div class="sc-thumb-inner">
                        <span class="sc-badge"><?php echo (int)$p['slide_count']; ?> slides</span>
                        <div class="sc-title-mock"><?php echo htmlspecialchars($p['title']); ?></div>
                    </div>
                </a>
                <div class="sc-body">
                    <h4><?php echo htmlspecialchars($p['title']); ?></h4>
                    <p class="muted"><?php echo htmlspecialchars($p['topic']); ?></p>
                    <p class="tiny">Updated <?php echo htmlspecialchars(date('M j, Y H:i', strtotime($p['updated_at'] ?: 'now'))); ?></p>
                    <div class="sc-actions">
                        <a class="btn btn-primary btn-sm" href="<?php echo BASE_URL; ?>/editor.php?id=<?php echo urlencode($p['id']); ?>">Edit</a>
                        <a class="btn btn-ghost btn-sm" href="<?php echo BASE_URL; ?>/slideshow.php?id=<?php echo urlencode($p['id']); ?>">▶ Present</a>
                        <button class="btn btn-ghost btn-sm btn-danger" data-del="<?php echo htmlspecialchars($p['id']); ?>">Delete</button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<script>
document.querySelectorAll('[data-del]').forEach(btn => {
    btn.addEventListener('click', async () => {
        if (!confirm('Delete this presentation? This cannot be undone.')) return;
        const id = btn.getAttribute('data-del');
        const r = await fetch('<?php echo BASE_URL; ?>/api/delete-presentation.php', {
            method: 'POST', headers: {'Content-Type':'application/json'},
            body: JSON.stringify({id})
        });
        const j = await r.json();
        if (j.success) { btn.closest('.saved-card').remove(); }
        else alert(j.error || 'Delete failed');
    });
});
document.getElementById('savedSearch')?.addEventListener('input', (e) => {
    const q = e.target.value.toLowerCase();
    document.querySelectorAll('.saved-card').forEach(c => {
        c.style.display = c.dataset.title.includes(q) ? '' : 'none';
    });
});
document.getElementById('importJson')?.addEventListener('change', async (e) => {
    const f = e.target.files[0]; if (!f) return;
    const text = await f.text();
    try {
        const data = JSON.parse(text);
        const r = await fetch('<?php echo BASE_URL; ?>/api/save-presentation.php', {
            method: 'POST', headers: {'Content-Type':'application/json'},
            body: JSON.stringify(data)
        });
        const j = await r.json();
        if (j.success) location.reload(); else alert(j.error || 'Import failed');
    } catch(err) { alert('Invalid JSON file'); }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
