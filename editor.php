<?php
$page = 'editor';
$pageTitle = 'Slide Editor';
require_once __DIR__ . '/includes/header.php';
$id = isset($_GET['id']) ? preg_replace('/[^a-zA-Z0-9_\-]/', '', $_GET['id']) : '';
?>
<div class="editor-shell" data-id="<?php echo htmlspecialchars($id); ?>">

    <?php require __DIR__ . '/includes/sidebar.php'; ?>

    <section class="editor-main">
        <div class="editor-toolbar" id="editorToolbar">
            <div class="tb-group tb-title">
                <input type="text" id="deckTitleInput" placeholder="Untitled Presentation" class="deck-title-input">
                <span class="save-badge" id="saveBadge">All changes saved</span>
            </div>

            <div class="tb-group">
                <button class="tb-btn" id="tbUndo" title="Undo (Ctrl+Z)">↶</button>
                <button class="tb-btn" id="tbRedo" title="Redo (Ctrl+Y)">↷</button>
            </div>

            <div class="tb-group">
                <button class="tb-btn" id="tbRegenText" title="Regenerate slide text">✨ Text</button>
                <button class="tb-btn" id="tbRegenImage" title="Regenerate slide image">🖼️ Image</button>
                <button class="tb-btn" id="tbSpeakerNotes" title="Toggle speaker notes">🗒️ Notes</button>
                <button class="tb-btn" id="tbLayout" title="Change layout">🧩 Layout</button>
            </div>

            <div class="tb-group tb-right">
                <button class="tb-btn" id="tbTheme" title="Change theme">🎨 Theme</button>
                <button class="tb-btn primary" id="tbPresent">▶ Present</button>
                <div class="tb-menu">
                    <button class="tb-btn" id="tbExport">⤓ Export ▾</button>
                    <div class="tb-menu-list" id="exportMenu">
                        <button data-export="pptx">Export as PowerPoint (.pptx)</button>
                        <button data-export="pdf">Export as PDF</button>
                        <button data-export="png">Export slides as PNG</button>
                        <button data-export="json">Export JSON</button>
                        <button data-export="print">Print</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="editor-canvas-wrap">
            <div class="editor-canvas" id="slideCanvas">
                <!-- Slide is rendered here by editor.js -->
            </div>

            <div class="editor-notes hidden" id="notesPanel">
                <label>Speaker Notes</label>
                <textarea id="notesText" placeholder="Write notes for this slide…"></textarea>
            </div>
        </div>
    </section>

    <aside class="editor-props" id="editorProps">
        <div class="props-tabs">
            <button class="pt-btn active" data-tab="slide">Slide</button>
            <button class="pt-btn" data-tab="text">Text</button>
            <button class="pt-btn" data-tab="image">Image</button>
        </div>

        <div class="props-body">
            <div class="props-pane active" data-pane="slide">
                <div class="pf">
                    <label>Layout</label>
                    <select id="propLayout">
                        <?php
                        $layouts = ['title','title-subtitle','bullets','text-image','image-text','two-column','full-image','image-overlay','quote','statistics','comparison','timeline','process','features','problem-solution','section-divider','takeaways','thank-you'];
                        foreach ($layouts as $l) echo '<option value="'.$l.'">'.ucwords(str_replace('-',' ',$l)).'</option>';
                        ?>
                    </select>
                </div>
                <div class="pf">
                    <label>Transition</label>
                    <select id="propTransition">
                        <?php foreach (['fade','slide-left','slide-right','zoom','flip','dissolve','none'] as $t) echo '<option>'.$t.'</option>'; ?>
                    </select>
                </div>
                <div class="pf">
                    <label>Auto duration (seconds)</label>
                    <input type="number" id="propDuration" min="2" max="60" value="6">
                </div>
                <div class="pf">
                    <label>Background</label>
                    <input type="color" id="propBg" value="#ffffff">
                </div>
                <div class="pf">
                    <label>Background gradient</label>
                    <div class="grad-row">
                        <input type="color" id="propGrad1" value="#667eea">
                        <input type="color" id="propGrad2" value="#764ba2">
                        <button class="btn btn-ghost btn-sm" id="applyGrad">Apply</button>
                    </div>
                </div>
                <div class="pf">
                    <label>Footer text</label>
                    <input type="text" id="propFooter" placeholder="Deck footer / date">
                </div>
                <div class="pf">
                    <label class="checkbox">
                        <input type="checkbox" id="propShowNumber" checked>
                        <span>Show slide number</span>
                    </label>
                </div>
            </div>

            <div class="props-pane" data-pane="text">
                <div class="pf">
                    <label>Font family</label>
                    <select id="propFont">
                        <option value="Inter">Inter</option>
                        <option value="Space Grotesk">Space Grotesk</option>
                        <option value="Georgia">Georgia</option>
                        <option value="Merriweather">Merriweather</option>
                        <option value="Courier New">Courier</option>
                    </select>
                </div>
                <div class="pf pf-row">
                    <div><label>Title size</label><input type="number" id="propTitleSize" value="44"></div>
                    <div><label>Body size</label><input type="number" id="propBodySize" value="20"></div>
                </div>
                <div class="pf pf-row">
                    <div><label>Title color</label><input type="color" id="propTitleColor" value="#111827"></div>
                    <div><label>Body color</label><input type="color" id="propBodyColor" value="#374151"></div>
                </div>
                <div class="pf">
                    <label>Text align</label>
                    <div class="btn-seg">
                        <button data-align="left">◧</button>
                        <button data-align="center" class="active">◨</button>
                        <button data-align="right">◫</button>
                    </div>
                </div>
                <div class="pf">
                    <label>AI text tools</label>
                    <div class="btn-col">
                        <button class="btn btn-ghost" data-textop="rewrite">✨ Rewrite</button>
                        <button class="btn btn-ghost" data-textop="shorten">✂ Shorten</button>
                        <button class="btn btn-ghost" data-textop="expand">➕ Expand</button>
                        <button class="btn btn-ghost" data-textop="professional">💼 Make professional</button>
                        <button class="btn btn-ghost" data-textop="grammar">🔤 Fix grammar</button>
                    </div>
                </div>
            </div>

            <div class="props-pane" data-pane="image">
                <div class="pf">
                    <label>Image prompt</label>
                    <textarea id="propImagePrompt" rows="3"></textarea>
                </div>
                <div class="pf">
                    <button class="btn btn-primary btn-block" id="btnRegenerateImage">🔄 Regenerate image</button>
                </div>
                <div class="pf">
                    <label>Upload custom image</label>
                    <input type="file" id="propImageUpload" accept="image/*">
                </div>
                <div class="pf">
                    <label>Image fit</label>
                    <select id="propImageFit">
                        <option value="cover">Cover</option>
                        <option value="contain">Contain</option>
                        <option value="fill">Fill</option>
                    </select>
                </div>
                <div class="pf">
                    <label class="checkbox">
                        <input type="checkbox" id="propImageBg">
                        <span>Use image as slide background</span>
                    </label>
                </div>
                <div class="pf">
                    <button class="btn btn-ghost btn-block" id="btnRemoveImage">Remove image</button>
                </div>
            </div>
        </div>
    </aside>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
