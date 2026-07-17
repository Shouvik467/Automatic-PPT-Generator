<?php
$page = 'slideshow';
$pageTitle = 'Slideshow';
require_once __DIR__ . '/includes/header.php';
$id = isset($_GET['id']) ? preg_replace('/[^a-zA-Z0-9_\-]/', '', $_GET['id']) : '';
?>

<div class="slideshow-shell" data-id="<?php echo htmlspecialchars($id); ?>">
    <div class="ss-topbar" id="ssTopbar">
        <div class="ss-tb-left">
            <a href="<?php echo BASE_URL; ?>/editor.php?id=<?php echo htmlspecialchars($id); ?>" class="btn btn-ghost btn-sm">← Exit</a>
            <span class="ss-title" id="ssTitle">Presentation</span>
        </div>
        <div class="ss-tb-center">
            <button class="ss-icon" id="ssPrev" title="Prev (←)">‹</button>
            <span class="ss-counter"><span id="ssCur">1</span> / <span id="ssTotal">1</span></span>
            <button class="ss-icon" id="ssNext" title="Next (→)">›</button>
        </div>
        <div class="ss-tb-right">
            <button class="ss-icon" id="ssPlay" title="Play/Pause (P)">▶</button>
            <select id="ssDuration" title="Slide duration">
                <option value="3">3s</option>
                <option value="5" selected>5s</option>
                <option value="8">8s</option>
                <option value="12">12s</option>
                <option value="20">20s</option>
            </select>
            <button class="ss-icon" id="ssNotes" title="Notes (N)">🗒</button>
            <button class="ss-icon" id="ssFull" title="Full screen (F)">⛶</button>
        </div>
    </div>

    <div class="ss-stage" id="ssStage">
        <div class="ss-slide-holder" id="ssHolder"></div>
        <div class="ss-progress"><div class="ss-progress-bar" id="ssProgressBar"></div></div>
    </div>

    <aside class="ss-notes-panel hidden" id="ssNotesPanel">
        <h4>Speaker Notes</h4>
        <div id="ssNotesText"></div>
        <div class="ss-timer">Timer: <span id="ssTimer">00:00</span></div>
    </aside>

    <div class="ss-thumbs" id="ssThumbs"></div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
