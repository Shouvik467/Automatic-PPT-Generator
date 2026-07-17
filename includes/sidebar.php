<?php
/**
 * Reusable sidebar used by the editor.
 * Rendered inside pages that need it; kept minimal — most logic lives in JS.
 */
?>
<aside class="editor-sidebar" id="editorSidebar">
    <div class="sidebar-head">
        <h3>Slides</h3>
        <button class="btn-icon" id="btnAddSlide" title="Add slide">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
        </button>
    </div>
    <div class="slide-list" id="slideThumbList"></div>
</aside>
