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

### Round 3: still blank, even single-file with `<img src="data:...">`

The `<img src="data:...">` single-file version above still rendered blank
on the live site, despite a bare `<style>` + `<div>` test in the same
widget working. That combination — style survives, image attribute doesn't
— pins down *where* the filtering happens: `wp_kses` (and, separately, a
`Content-Security-Policy: img-src` header some hosts/security plugins send)
both act on **HTML attribute values**. Neither one parses the text content
of a `<style>` block, which is exactly why the style-only test always got
through untouched.

`kubus-hero-card-single-file.html` was rewritten accordingly: every image
(background photo, both logo states) moved out of `<img src>` and into the
`<style>` block itself, as `background-image: var(--kb-...)` CSS custom
properties, still holding the same embedded base64 data. There is now no
`data:` URI anywhere in an HTML attribute for `wp_kses` to strip — verified
by simulating that filter against the file and diffing the rendered output
(byte-identical). This is still a single paste, nothing to upload.

This only leaves one thing it can't work around: a CSP `img-src` header
blocks `data:` images at the *browser* level, in CSS `background-image`
just as much as `<img src>`, and no HTML-side change can get past that. If
this version is still blank, open the browser DevTools Console on the live
page — a line like *"Refused to load the image '...' because it violates
the following Content Security Policy directive: img-src ..."* confirms
it. At that point swap the three `url("data:...")` values at the top of
the file for real `https://` URLs (WordPress Media Library uploads are
same-origin and always allowed by `img-src`) — everything else in the file
stays the same.

### To use in Elementor

**`kubus-hero-card-single-file.html`** (recommended) — one paste, nothing
to upload. Add a **Custom HTML** widget (not Text Editor) and paste the
whole file in. See "Round 3" above for the one remaining failure mode
(CSP blocking `data:` images) and how to spot/fix it.

**`kubus-hero-card.html` + `assets/`** — same markup, but the three images
are referenced by URL instead of embedded, for a permanent production
setup: upload the files in `assets/` (`kubus-bg.jpg`, `kubus-logo-white.png`,
`kubus-logo-green.png`) to the WordPress Media Library, copy each file's
URL, and replace the three placeholders in `kubus-hero-card.html`
(`PASTE-KUBUS-BG-IMAGE-URL-HERE`, `PASTE-KUBUS-LOGO-WHITE-URL-HERE`,
`PASTE-KUBUS-LOGO-GREEN-URL-HERE`). Immune to both `wp_kses` and CSP
`img-src`, at the cost of one extra setup step.
