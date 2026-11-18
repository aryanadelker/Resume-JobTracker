(() => {
  "use strict";
  const defaults = {
    font_scale: "normal", high_contrast: false, dyslexia_font: false,
    reduce_motion: false, large_cursor: false, increased_spacing: false
  };
  const keys = {
    font_scale: "caFontScale", high_contrast: "caHighContrast",
    dyslexia_font: "caDyslexiaFont", reduce_motion: "caReduceMotion",
    large_cursor: "caLargeCursor", increased_spacing: "caIncreasedSpacing"
  };
  const bodyClasses = {
    high_contrast: "ca-high-contrast", dyslexia_font: "ca-dyslexia-font",
    reduce_motion: "ca-reduce-motion", large_cursor: "ca-large-cursor",
    increased_spacing: "ca-increased-spacing"
  };
  const $ = id => document.getElementById(id);
  const trigger = $("caAccessibilityTrigger");
  const panel = $("caAccessibilityPanel");
  if (!trigger || !panel) return;

  const status = $("caAccessibilityStatus");
  const apiUrl = "accessibility_api.php";

  function readControls() {
    return {
      font_scale: $("caFontScale").value,
      high_contrast: $("caHighContrast").checked,
      dyslexia_font: $("caDyslexiaFont").checked,
      reduce_motion: $("caReduceMotion").checked,
      large_cursor: $("caLargeCursor").checked,
      increased_spacing: $("caIncreasedSpacing").checked
    };
  }
  function writeControls(p) {
    $("caFontScale").value = p.font_scale || "normal";
    Object.keys(keys).filter(k => k !== "font_scale").forEach(k => {
      $(keys[k]).checked = Boolean(Number(p[k]) || p[k] === true);
    });
    apply(p);
  }
  function apply(p) {
    document.body.classList.remove("ca-font-small","ca-font-large","ca-font-extra_large");
    if (p.font_scale && p.font_scale !== "normal") document.body.classList.add("ca-font-" + p.font_scale);
    Object.entries(bodyClasses).forEach(([key, cls]) => document.body.classList.toggle(cls, Boolean(p[key])));
    try { localStorage.setItem("careeraccess_settings", JSON.stringify(p)); } catch (_) {}
  }
  function localSettings() {
    try {
      const saved = JSON.parse(localStorage.getItem("careeraccess_settings") || "null");
      return saved ? {...defaults, ...saved} : defaults;
    } catch (_) { return defaults; }
  }

  trigger.addEventListener("click", () => {
    const opening = panel.hidden;
    panel.hidden = !opening;
    trigger.setAttribute("aria-expanded", String(opening));
    if (opening) $("caFontScale").focus();
  });
  $("caAccessibilityClose").addEventListener("click", () => {
    panel.hidden = true; trigger.setAttribute("aria-expanded", "false"); trigger.focus();
  });
  $("caResetSettings").addEventListener("click", () => {
    writeControls(defaults); status.textContent = "Default settings restored. Select Save settings to keep them.";
  });
  $("caSaveSettings").addEventListener("click", async () => {
    const prefs = readControls();
    apply(prefs);
    status.textContent = "Saving settings…";
    try {
      const response = await fetch(apiUrl, {
        method: "POST", headers: {"Content-Type":"application/json"},
        body: JSON.stringify(prefs), credentials: "same-origin"
      });
      const result = await response.json();
      if (!response.ok || !result.success) throw new Error(result.message || "Could not save settings.");
      status.textContent = "Settings saved to your account.";
    } catch (error) {
      status.textContent = "Applied on this browser, but account saving failed. " + error.message;
    }
  });

  // Apply immediately when a control changes; persistence happens on Save.
  Object.values(keys).forEach(id => $(id).addEventListener("change", () => apply(readControls())));

  // Local settings are applied immediately; logged-in users then load server-saved settings.
  writeControls(localSettings());
  fetch(apiUrl, {credentials:"same-origin"})
    .then(r => r.ok ? r.json() : null)
    .then(result => {
      if (result && result.success && result.settings) writeControls({...defaults, ...result.settings});
    })
    .catch(() => {});
})();
