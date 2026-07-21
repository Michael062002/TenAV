# TenTechnology — Showroom Visit landing page

Three files, all in this folder:

| File | What it is |
|---|---|
| `showroom-visit-landing-page.html` | Full Gutenberg block markup. Paste it into a new WP Page via the block editor's **Code editor** view (⋮ menu → Code editor), then switch back to the visual editor. |
| `event-landing.css` | Small companion stylesheet for the handful of things core blocks can't do (button hover state, orange numbered-list markers, the registration card's shadow, footer link colours). |
| `functions-snippet.php` | Registers the TenTechnology colour palette in the block editor and enqueues `event-landing.css`. Add its contents to your theme's `functions.php`. |

## Install order
1. Copy `event-landing.css` into your active theme's root folder (same level as `style.css`).
2. Add the contents of `functions-snippet.php` to your theme's `functions.php`.
3. Create the Page, paste in `showroom-visit-landing-page.html` via Code editor, save/preview.

## What's already wired in
- Your registration widget (`kubuseventregistration.html`) is embedded as-is in Section 6's Custom HTML block — nothing was changed in its markup, CSS, or JS. You still need to set `FORM_ENDPOINT_URL` inside it before going live (see the comment block at the top of that section).
- A code comment sits next to the widget flagging that it doesn't currently collect **Job Title** or **Company Size** — both are recommended as *required* fields for lead qualification. Pattern to follow and exact places to touch (`validate()`, `buildPayload()`) are called out in that comment; nothing was added automatically so the working widget isn't altered without sign-off.
- Colour palette (`tentech-navy` `#0B1A2B`, `tentech-orange` `#C97A3D`, `tentech-callout` `#E7ECF3`, `tentech-charcoal` `#16233A`, `tentech-muted` `#6B7280`) is registered as named editor swatches, not hardcoded — the client can still change section backgrounds/colours from the block editor's Color panel.
- One `<h1>` (hero), `<h2>` for every subsequent section — correct heading hierarchy.
- Hero body text uses white/callout-blue on navy and orange-on-navy for the CTA button — all comfortably pass WCAG AA contrast.

## Still needed from you
- Event **Date / Time / Location / Duration** — currently `[EVENT DATE]` etc. placeholders in Section 4 and Section 6's H2 ("Reserve Your Spot for [EVENT DATE]").
- TenTechnology logo image (Section 1) and a hero graphic (Section 2, right column) — both are placeholder `<img>` tags pointing at non-existent URLs; replace via **Add Media** in the block editor once pasted in.
- Real URLs for the footer's Privacy Policy / Contact / Manage Preferences links (currently `#`).
- Confirm the qualifying-line wording under the registration widget ("By registering, you confirm you're evaluating network infrastructure or workplace AV solutions for your organisation...") matches your actual filtering criteria — easy to edit directly in the block editor since it's a plain Paragraph block.

## Note on the widget's colours
The registration widget uses its own scoped palette (Kubus Navy `#0E1F29`, Kubus Orange `#B96E36`) which is close to but not pixel-identical to the page's TenTechnology palette. Because the widget's CSS is scoped to `.kubus-event-reg`, this is cosmetic only and doesn't clash with the surrounding page — flagged in-line in case you want the two exactly aligned.
