# Elementor HTML snippets

Drop-in fragments for Elementor's **Custom HTML** widget (not the Text Editor
widget — that one runs content through `wpautop`/`wp_kses_post` and strips
`<style>` tags).

## kubus-hero-card.html

Found and fixed after the original pasted version broke the whole page in
Elementor. Root cause: the file being pasted in included a leftover
`<!doctype html><html><head>...<style>body{margin:0;...}</style></head><body>`
wrapper around the actual card markup. Elementor drops that raw HTML straight
into the live page DOM, so the unscoped `body { margin/padding/font/background/
color }` and `* { box-sizing: border-box }` rules inside it applied to the
**entire site**, not just the card — overriding the theme's global typography,
background and spacing everywhere on the page.

This version:
- Contains only the fragment (`<style>` + markup) — no `doctype`/`html`/`head`/
  `body`/`title` wrapper.
- Scopes every selector under `.tenav-kubus-card` and namespaces the CSS
  custom properties (`--kb-*`) so nothing leaks into or clashes with the
  theme.
- Adds an `@media (hover: none)` fallback so the description/CTA (normally
  revealed on `:hover`) are also visible on touch devices, which have no
  hover state.

### To use in Elementor
1. Add a **Custom HTML** widget (not Text Editor).
2. Paste the full contents of `kubus-hero-card.html` into it.
3. The WordPress user saving it needs the `unfiltered_html` capability
   (Administrators on a standard single-site install). Editors/Authors, or
   any role restricted by a security plugin, will have the `<style>` tag and
   other markup silently stripped on save, which looks identical to "the
   hover effect doesn't work."
