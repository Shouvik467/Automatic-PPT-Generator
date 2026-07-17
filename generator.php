<?php
$page = 'generator';
$pageTitle = 'Create a Presentation';
require_once __DIR__ . '/includes/header.php';
$prefTopic = isset($_GET['topic']) ? htmlspecialchars($_GET['topic']) : '';
$prefTheme = isset($_GET['theme']) ? htmlspecialchars($_GET['theme']) : 'modern';
?>

<section class="gen-wrap">
    <div class="container">
        <div class="gen-header">
            <h1>Create your presentation</h1>
            <p>Fill in the details and let AI craft your deck.</p>
        </div>

        <!-- STEP INDICATOR -->
        <div class="step-indicator" id="stepIndicator">
            <div class="si-step active" data-step="1"><span>1</span> Setup</div>
            <div class="si-step" data-step="2"><span>2</span> Outline</div>
            <div class="si-step" data-step="3"><span>3</span> Generating</div>
            <div class="si-step" data-step="4"><span>4</span> Ready</div>
        </div>

        <!-- STEP 1: FORM -->
        <div class="gen-panel" id="panelForm">
            <form id="genForm" class="gen-form" autocomplete="off">
                <div class="grid-2">
                    <div class="field field-lg">
                        <label>Presentation Topic *</label>
                        <input type="text" name="topic" id="fTopic" required
                               placeholder="e.g. Artificial Intelligence in Healthcare"
                               value="<?php echo $prefTopic; ?>">
                    </div>
                    <div class="field">
                        <label>Presentation Title <span class="hint">(optional)</span></label>
                        <input type="text" name="title" id="fTitle" placeholder="Auto-generated if empty">
                    </div>
                </div>

                <div class="field">
                    <label>Short Description <span class="hint">(what should the deck emphasize?)</span></label>
                    <textarea name="description" id="fDesc" rows="2" placeholder="Optional context / goals / angle…"></textarea>
                </div>

                <div class="grid-3">
                    <div class="field">
                        <label>Number of Slides</label>
                        <select name="slideCount" id="fSlideCount">
                            <option value="5">5 slides</option>
                            <option value="8">8 slides</option>
                            <option value="10" selected>10 slides</option>
                            <option value="12">12 slides</option>
                            <option value="15">15 slides</option>
                            <option value="20">20 slides</option>
                            <option value="custom">Custom…</option>
                        </select>
                        <input type="number" min="3" max="30" id="fSlideCustom" class="hidden" placeholder="Enter number">
                    </div>
                    <div class="field">
                        <label>Presentation Type</label>
                        <select name="type" id="fType">
                            <?php foreach (['Business','Education','School Project','College Assignment','Technology','Marketing','Sales','Startup Pitch','Product Presentation','Company Profile','Medical','Research','Training','Portfolio','Custom'] as $o): ?>
                                <option><?php echo $o; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="field">
                        <label>Tone</label>
                        <select name="tone" id="fTone">
                            <?php foreach (['Professional','Academic','Corporate','Creative','Friendly','Persuasive','Inspirational','Technical','Simple','Formal'] as $o): ?>
                                <option><?php echo $o; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="grid-3">
                    <div class="field">
                        <label>Target Audience</label>
                        <select name="audience" id="fAudience">
                            <?php foreach (['Students','Teachers','Customers','Investors','Employees','Management','Researchers','Developers','General Audience','Custom Audience'] as $o): ?>
                                <option><?php echo $o; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="field">
                        <label>Language</label>
                        <select name="language" id="fLanguage">
                            <?php foreach (['English','Spanish','French','German','Italian','Portuguese','Arabic','Hindi','Chinese','Japanese','Korean','Russian','Turkish','Indonesian','Dutch'] as $o): ?>
                                <option><?php echo $o; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="field">
                        <label>Aspect Ratio</label>
                        <select name="aspect" id="fAspect">
                            <option value="16:9" selected>16:9 (Widescreen)</option>
                            <option value="4:3">4:3 (Standard)</option>
                            <option value="1:1">1:1 (Square)</option>
                            <option value="9:16">9:16 (Vertical)</option>
                        </select>
                    </div>
                </div>

                <div class="field">
                    <label>Visual Theme</label>
                    <div class="theme-picker" id="themePicker">
                        <?php
                        $themes = [
                            ['modern','Modern'],['corporate','Corporate'],['minimal','Minimal'],
                            ['futuristic','Futuristic'],['tech','Technology'],['creative','Creative'],
                            ['educational','Educational'],['luxury','Luxury'],['dark-pro','Dark Pro'],
                            ['clean','Clean White'],['gradient','Gradient'],['glass','Glassmorphism'],
                            ['nature','Nature'],['custom','Custom']
                        ];
                        foreach ($themes as $t):
                            $active = $t[0] === $prefTheme ? 'active' : '';
                        ?>
                        <label class="theme-chip <?php echo $active; ?>" data-theme="<?php echo $t[0]; ?>">
                            <input type="radio" name="theme" value="<?php echo $t[0]; ?>" <?php echo $active ? 'checked' : ''; ?>>
                            <span class="chip-swatch t-<?php echo $t[0]; ?>"></span>
                            <span><?php echo $t[1]; ?></span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="field">
                    <label>Image Style</label>
                    <div class="chip-row" id="stylePicker">
                        <?php foreach (['realistic','professional','illustration','flat-vector','3d-render','futuristic','cinematic','educational','corporate','minimal','abstract','infographic','watercolor','isometric'] as $s): ?>
                            <label class="chip <?php echo $s==='professional'?'active':''; ?>" data-style="<?php echo $s; ?>">
                                <input type="radio" name="imageStyle" value="<?php echo $s; ?>" <?php echo $s==='professional'?'checked':''; ?>>
                                <?php echo ucwords(str_replace('-', ' ', $s)); ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="grid-2">
                    <div class="field">
                        <label>Author Name <span class="hint">(shown on title/footer)</span></label>
                        <input type="text" name="author" id="fAuthor" placeholder="Your name">
                    </div>
                    <div class="field">
                        <label>Organization <span class="hint">(optional)</span></label>
                        <input type="text" name="organization" id="fOrg" placeholder="Company / School">
                    </div>
                </div>

                <div class="field">
                    <label class="checkbox">
                        <input type="checkbox" id="fAutoImages" checked>
                        <span>Automatically generate AI images for every suitable slide</span>
                    </label>
                </div>

                <div class="gen-actions">
                    <button type="submit" class="btn btn-primary btn-lg" id="btnGenerateOutline">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><path d="M12 3v3M12 18v3M5.6 5.6l2.1 2.1M16.3 16.3l2.1 2.1M3 12h3M18 12h3M5.6 18.4l2.1-2.1M16.3 7.7l2.1-2.1"/></svg>
                        Generate Outline
                    </button>
                    <button type="button" class="btn btn-ghost" id="btnReset">Reset</button>
                </div>
            </form>
        </div>

        <!-- STEP 2: OUTLINE -->
        <div class="gen-panel hidden" id="panelOutline">
            <div class="outline-head">
                <div>
                    <h2 id="outlineTitle">Your Outline</h2>
                    <p id="outlineSubtitle" class="muted"></p>
                </div>
                <div class="outline-actions">
                    <button class="btn btn-ghost" id="btnBackToForm">← Edit setup</button>
                    <button class="btn btn-ghost" id="btnRegenOutline">🔁 Regenerate outline</button>
                    <button class="btn btn-primary" id="btnStartBuild">
                        Build Full Presentation →
                    </button>
                </div>
            </div>
            <div class="outline-list" id="outlineList"></div>
            <button class="btn btn-ghost btn-block" id="btnAddOutlineSlide">
                + Add Slide
            </button>
        </div>

        <!-- STEP 3: GENERATING -->
        <div class="gen-panel hidden" id="panelBuild">
            <div class="build-status">
                <div class="build-spinner">
                    <div class="ring"></div>
                    <div class="ring r2"></div>
                    <div class="ring r3"></div>
                </div>
                <h2 id="buildTitle">Generating your presentation…</h2>
                <p id="buildSub">Writing slide content and creating AI images.</p>
                <div class="progress"><div class="progress-fill" id="buildProgress" style="width:0%"></div></div>
                <p class="progress-label"><span id="buildDone">0</span> / <span id="buildTotal">0</span> slides</p>
                <div class="build-log" id="buildLog"></div>
                <button class="btn btn-ghost" id="btnCancelBuild">Cancel</button>
            </div>
        </div>

        <!-- STEP 4: READY -->
        <div class="gen-panel hidden" id="panelReady">
            <div class="ready-hero">
                <div class="ready-check">✓</div>
                <h2>Your presentation is ready!</h2>
                <p class="muted" id="readySub"></p>
                <div class="ready-actions">
                    <a class="btn btn-primary btn-lg" id="btnOpenEditor" href="#">Open in Editor</a>
                    <a class="btn btn-ghost btn-lg" id="btnOpenSlideshow" href="#">▶ Start Slideshow</a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
