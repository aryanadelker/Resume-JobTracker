<?php
/*
 * Include this file once on each page where you want the accessibility
 * controls and saved preferences to apply.
 */
?>
<link rel="stylesheet" href="assets/css/accessibility.css">
<script src="assets/js/accessibility.js" defer></script>

<div class="ca-accessibility">
    <button type="button" class="ca-accessibility__trigger"
            id="caAccessibilityTrigger"
            aria-expanded="false" aria-controls="caAccessibilityPanel">
        Accessibility
    </button>

    <section class="ca-accessibility__panel" id="caAccessibilityPanel"
             aria-labelledby="caAccessibilityTitle" hidden>
        <div class="ca-accessibility__header">
            <h2 id="caAccessibilityTitle">Accessibility settings</h2>
            <button type="button" id="caAccessibilityClose"
                    class="ca-accessibility__close" aria-label="Close accessibility settings">×</button>
        </div>

        <label for="caFontScale">Text size</label>
        <select id="caFontScale">
            <option value="small">Small</option>
            <option value="normal" selected>Default</option>
            <option value="large">Large</option>
            <option value="extra_large">Extra large</option>
        </select>

        <fieldset>
            <legend>Display and reading</legend>
            <label><input type="checkbox" id="caHighContrast"> High contrast</label>
            <label><input type="checkbox" id="caDyslexiaFont"> Use a more readable font</label>
            <label><input type="checkbox" id="caReduceMotion"> Reduce animations</label>
            <label><input type="checkbox" id="caLargeCursor"> Larger pointer</label>
            <label><input type="checkbox" id="caIncreasedSpacing"> Increase text spacing</label>
        </fieldset>

        <div class="ca-accessibility__actions">
            <button type="button" id="caSaveSettings">Save settings</button>
            <button type="button" id="caResetSettings" class="ca-secondary">Reset</button>
        </div>
        <p id="caAccessibilityStatus" class="ca-accessibility__status"
           role="status" aria-live="polite"></p>
        <p class="ca-accessibility__hint">Settings apply to pages where this accessibility panel is included.</p>
    </section>
</div>
