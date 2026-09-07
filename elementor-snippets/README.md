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

After the doctype fix, the widget still rendered **completely blank** on the
live site, even though a plain `<style>` + `<div>` test in the same Custom
HTML widget worked fine. That isolated the second bug: the original markup
used an inline `<svg>` logo and a `data:image/jpeg;base64,...` background
image. WordPress's default content filter (`wp_kses`), which applies to any
account without the `unfiltered_html` capability, does not allow `<svg>`,
`<path>`, `<polygon>` tags or `data:` URIs — so on save it silently stripped
almost the entire widget, leaving nothing to render.

Fixed by removing both risk factors:
- The inline SVG logo is now two plain `<img>` files — `kubus-logo-white.png`
  (default) and `kubus-logo-green.png` (icon in brand green), cross-faded
  with a CSS `opacity` transition on `:hover`/`:focus-visible` — reproducing
  the original "icon turns brand-green on hover, lettering stays white"
  effect without any `<svg>` markup.
- The background photo is now a real file (`kubus-bg.jpg`) referenced by
  URL instead of embedded as a giant inline base64 string.

Both `<img>`, `<style>` and `<a>` survive `wp_kses` filtering, so this
version works regardless of the saving account's `unfiltered_html`
capability.

### To use in Elementor
1. Upload the three files in `assets/` (`kubus-bg.jpg`, `kubus-logo-white.png`,
   `kubus-logo-green.png`) to the WordPress Media Library and copy each
   file's URL.
2. Open `kubus-hero-card.html` and replace the three placeholders —
   `PASTE-KUBUS-BG-IMAGE-URL-HERE`, `PASTE-KUBUS-LOGO-WHITE-URL-HERE`,
   `PASTE-KUBUS-LOGO-GREEN-URL-HERE` — with those Media Library URLs.
3. Add a **Custom HTML** widget (not Text Editor) and paste the result in.
