<?php
$page      = 'home';
$pageTitle = 'Create Stunning Presentations with AI';
require_once __DIR__ . '/includes/header.php';
?>

<!-- ==================== HERO ==================== -->
<section class="hero">
    <div class="hero-bg">
        <div class="hero-blob b1"></div>
        <div class="hero-blob b2"></div>
        <div class="hero-blob b3"></div>
        <div class="hero-grid"></div>
    </div>
    <div class="container hero-inner">
        <div class="hero-badge">
            <span class="pulse-dot"></span>
            Powered by Pollinations AI — Free & Instant
        </div>
        <h1 class="hero-title">
            Create <span class="gradient-text">Stunning Presentations</span><br>
            with AI in Seconds
        </h1>
        <p class="hero-desc">
            Enter any topic and instantly generate a professional presentation with structured content,
            attractive slide designs, speaker notes, animations, and AI-generated images.
        </p>

        <form id="quickTopicForm" class="quick-topic">
            <input type="text" id="quickTopic" placeholder="e.g. Artificial Intelligence in Healthcare" required>
            <button type="submit" class="btn btn-primary btn-lg">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                Generate Presentation
            </button>
        </form>

        <div class="hero-cta-row">
            <a href="#demo" class="btn btn-ghost">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><polygon points="6 4 20 12 6 20 6 4"/></svg>
                View Demo
            </a>
            <a href="#templates" class="btn btn-ghost">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
                Explore Templates
            </a>
        </div>

        <div class="hero-stats">
            <div><b>16:9 / 4:3 / Vertical</b><span>Aspect ratios</span></div>
            <div><b>20+</b><span>Slide Layouts</span></div>
            <div><b>14</b><span>Visual Themes</span></div>
            <div><b>PPTX · PDF · PNG</b><span>Export Formats</span></div>
        </div>
    </div>
</section>

<!-- ==================== DEMO PREVIEW ==================== -->
<section class="demo" id="demo">
    <div class="container">
        <div class="section-head">
            <h2>Watch your topic become a full deck</h2>
            <p>See how a single prompt turns into a full, polished presentation with images and speaker notes.</p>
        </div>
        <div class="demo-cards">
            <div class="demo-slide s1">
                <div class="d-tag">Title Slide</div>
                <h3>Artificial Intelligence in Healthcare</h3>
                <p>How AI is reshaping diagnosis, treatment, and patient outcomes</p>
            </div>
            <div class="demo-slide s2">
                <div class="d-tag">Statistics</div>
                <div class="d-stats">
                    <div><b>87%</b><span>hospitals adopting AI</span></div>
                    <div><b>3.2×</b><span>faster diagnosis</span></div>
                    <div><b>$187B</b><span>market by 2030</span></div>
                </div>
            </div>
            <div class="demo-slide s3">
                <div class="d-tag">Features</div>
                <ul>
                    <li>AI-assisted medical imaging</li>
                    <li>Predictive patient monitoring</li>
                    <li>Robotic surgical assistants</li>
                    <li>Drug discovery acceleration</li>
                </ul>
            </div>
        </div>
    </div>
</section>

<!-- ==================== FEATURES ==================== -->
<section class="features" id="features">
    <div class="container">
        <div class="section-head">
            <span class="eyebrow">FEATURES</span>
            <h2>Everything you need to present with confidence</h2>
        </div>
        <div class="feature-grid">
            <div class="feature-card">
                <div class="feature-icon fi1">✨</div>
                <h3>AI-Generated Outline</h3>
                <p>Turn any topic into a structured outline you can review and edit before generating full slides.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon fi2">🖼️</div>
                <h3>Auto AI Images</h3>
                <p>Every slide gets a unique, context-aware image generated in your chosen visual style.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon fi3">🎨</div>
                <h3>14 Visual Themes</h3>
                <p>Modern, futuristic, glassmorphism, dark-pro and more — with matching typography and color.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon fi4">🧑‍🏫</div>
                <h3>Speaker Notes</h3>
                <p>Get expert-level notes for every slide so you always know what to say.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon fi5">📤</div>
                <h3>Export Anywhere</h3>
                <p>Download as .pptx, .pdf, .png images, or a portable JSON file — no lock-in.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon fi6">⌨️</div>
                <h3>Full Slide Editor</h3>
                <p>Edit titles, text, bullets, images, backgrounds and layouts inside a polished visual editor.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon fi7">🎬</div>
                <h3>Slideshow Mode</h3>
                <p>Present full-screen with keyboard controls, touch swipe, timer and auto-play.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon fi8">🌓</div>
                <h3>Light & Dark</h3>
                <p>Smooth theme switching that persists across sessions, with system-preference detection.</p>
            </div>
        </div>
    </div>
</section>

<!-- ==================== HOW IT WORKS ==================== -->
<section class="how" id="how">
    <div class="container">
        <div class="section-head">
            <span class="eyebrow">HOW IT WORKS</span>
            <h2>From idea to deck in 4 steps</h2>
        </div>
        <div class="how-steps">
            <div class="step">
                <div class="step-num">1</div>
                <h3>Enter your topic</h3>
                <p>Type any subject — AI, marketing, medicine, a business plan — and pick your options.</p>
            </div>
            <div class="step">
                <div class="step-num">2</div>
                <h3>Review the outline</h3>
                <p>AI writes a full outline you can edit, reorder, add to, or regenerate slide-by-slide.</p>
            </div>
            <div class="step">
                <div class="step-num">3</div>
                <h3>Generate slides + images</h3>
                <p>Full slide content and unique AI images are generated for every slide, in your style.</p>
            </div>
            <div class="step">
                <div class="step-num">4</div>
                <h3>Edit, present, export</h3>
                <p>Polish inside the editor, present full-screen, and export to PPTX, PDF or PNG.</p>
            </div>
        </div>
    </div>
</section>

<!-- ==================== TEMPLATES ==================== -->
<section class="templates" id="templates">
    <div class="container">
        <div class="section-head">
            <span class="eyebrow">TEMPLATES</span>
            <h2>Start from a theme you love</h2>
        </div>
        <div class="template-grid">
            <?php
            $themes = [
                ['modern','Modern','#667eea','#764ba2'],
                ['corporate','Corporate','#0f4c81','#2c7da0'],
                ['minimal','Minimal','#eaeaea','#c7c7c7'],
                ['futuristic','Futuristic','#0ea5e9','#7c3aed'],
                ['dark-pro','Dark Pro','#111827','#1f2937'],
                ['gradient','Gradient','#f43f5e','#f59e0b'],
                ['glass','Glassmorphism','#38bdf8','#a855f7'],
                ['nature','Nature','#10b981','#065f46'],
                ['luxury','Luxury','#111','#c9a227'],
                ['creative','Creative','#ec4899','#8b5cf6'],
            ];
            foreach ($themes as $t): ?>
                <a class="template-card" href="<?php echo BASE_URL; ?>/generator.php?theme=<?php echo $t[0]; ?>"
                   style="background: linear-gradient(135deg, <?php echo $t[2]; ?>, <?php echo $t[3]; ?>);">
                    <div class="tc-body">
                        <div class="tc-mock">
                            <div class="tc-bar"></div>
                            <div class="tc-bar w60"></div>
                            <div class="tc-bar w40"></div>
                        </div>
                        <h3><?php echo $t[1]; ?></h3>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ==================== BENEFITS ==================== -->
<section class="benefits">
    <div class="container benefits-inner">
        <div>
            <span class="eyebrow">WHY US</span>
            <h2>Built for speed. Designed for impact.</h2>
            <ul class="benefit-list">
                <li><span>⚡</span> Generate a full deck in under 60 seconds</li>
                <li><span>🔒</span> Your API key stays server-side (PHP)</li>
                <li><span>💾</span> No database — presentations saved as JSON</li>
                <li><span>📱</span> Fully responsive: desktop, tablet, mobile</li>
                <li><span>🎯</span> Audience & tone-aware content generation</li>
                <li><span>🖼️</span> Unique AI image per slide, never duplicated</li>
            </ul>
            <a href="<?php echo BASE_URL; ?>/generator.php" class="btn btn-primary btn-lg">Start Creating Free</a>
        </div>
        <div class="benefits-visual">
            <div class="bv-card b1">🚀 Instant outline</div>
            <div class="bv-card b2">🎨 Themed slides</div>
            <div class="bv-card b3">🧠 Smart speaker notes</div>
            <div class="bv-card b4">📤 PPTX / PDF export</div>
        </div>
    </div>
</section>

<!-- ==================== FAQ ==================== -->
<section class="faq" id="faq">
    <div class="container">
        <div class="section-head">
            <span class="eyebrow">FAQ</span>
            <h2>Frequently asked questions</h2>
        </div>
        <div class="faq-list">
            <?php
            $faqs = [
                ['Do I need a database?', 'No. Everything is saved as JSON files inside the storage/ folder. Works on any PHP host, including Hostinger shared hosting.'],
                ['Where does the AI come from?', 'From the Pollinations AI API. It generates both slide content and images. You can optionally add an API key for higher limits.'],
                ['Can I edit the slides after generation?', 'Yes. The full visual editor lets you change text, bullets, images, backgrounds, layouts and speaker notes.'],
                ['Which export formats are supported?', 'PowerPoint (.pptx via PptxGenJS), PDF (via jsPDF + html2canvas), individual PNG slides, and portable JSON.'],
                ['Is my presentation private?', 'Yes. Presentations are stored on your own server as JSON files. Nothing is uploaded elsewhere by the app.'],
                ['Does it work on mobile?', 'Yes. The UI and slideshow work responsively on phones and tablets, with touch and swipe support.'],
            ];
            foreach ($faqs as $i => $f): ?>
            <details class="faq-item" <?php echo $i === 0 ? 'open' : ''; ?>>
                <summary><?php echo htmlspecialchars($f[0]); ?><span class="fchev">+</span></summary>
                <p><?php echo htmlspecialchars($f[1]); ?></p>
            </details>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ==================== CTA ==================== -->
<section class="cta-final">
    <div class="container cta-inner">
        <h2>Ready to create your next masterpiece?</h2>
        <p>Turn any idea into a beautiful deck in less than a minute.</p>
        <a href="<?php echo BASE_URL; ?>/generator.php" class="btn btn-primary btn-xl">
            Generate My Presentation
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
        </a>
    </div>
</section>

<script>
document.getElementById('quickTopicForm').addEventListener('submit', function(e){
    e.preventDefault();
    var v = document.getElementById('quickTopic').value.trim();
    if (!v) return;
    localStorage.setItem('pendingTopic', v);
    window.location.href = '<?php echo BASE_URL; ?>/generator.php?topic=' + encodeURIComponent(v);
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
