</main>

<footer class="app-footer">
    <div class="footer-inner">
        <div class="footer-col">
            <div class="footer-brand">
                <span class="brand-text">AI PPT<span class="brand-accent">Generator</span></span>
            </div>
            <p class="footer-text">Create stunning AI-generated presentations from any topic. Powered by Pollinations AI. Built with pure PHP + JavaScript — no database required.</p>
        </div>
        <div class="footer-col">
            <h4>Product</h4>
            <a href="<?php echo BASE_URL; ?>/generator.php">Create Presentation</a>
            <a href="<?php echo BASE_URL; ?>/saved-presentations.php">My Presentations</a>
            <a href="<?php echo BASE_URL; ?>/index.php#features">Features</a>
            <a href="<?php echo BASE_URL; ?>/index.php#how">How it works</a>
        </div>
        <div class="footer-col">
            <h4>Resources</h4>
            <a href="<?php echo BASE_URL; ?>/index.php#templates">Templates</a>
            <a href="<?php echo BASE_URL; ?>/index.php#faq">FAQ</a>
            <a href="https://pollinations.ai" target="_blank" rel="noopener">Pollinations AI</a>
        </div>
        <div class="footer-col">
            <h4>Status</h4>
            <div class="api-status" id="apiStatus">
                <span class="status-dot"></span>
                <span class="status-text">Checking API…</span>
            </div>
        </div>
    </div>
    <div class="footer-bottom">
        <span>© <?php echo date('Y'); ?> <?php echo APP_NAME; ?>. All rights reserved.</span>
        <span>v<?php echo APP_VERSION; ?></span>
    </div>
</footer>

<div id="toastRoot" class="toast-root" aria-live="polite"></div>
<div id="modalRoot" class="modal-root"></div>

<script src="<?php echo BASE_URL; ?>/assets/js/theme.js"></script>
<script src="<?php echo BASE_URL; ?>/assets/js/app.js"></script>
<?php if (($page ?? '') === 'generator'): ?>
    <script src="<?php echo BASE_URL; ?>/assets/js/generator.js"></script>
    <script src="<?php echo BASE_URL; ?>/assets/js/outline.js"></script>
<?php endif; ?>
<?php if (($page ?? '') === 'editor'): ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/gh/gitbrent/PptxGenJS@3.12.0/dist/pptxgen.bundle.js"></script>
    <script src="<?php echo BASE_URL; ?>/assets/js/editor.js"></script>
    <script src="<?php echo BASE_URL; ?>/assets/js/export.js"></script>
    <script src="<?php echo BASE_URL; ?>/assets/js/autosave.js"></script>
<?php endif; ?>
<?php if (($page ?? '') === 'slideshow'): ?>
    <script src="<?php echo BASE_URL; ?>/assets/js/slideshow.js"></script>
<?php endif; ?>
</body>
</html>
