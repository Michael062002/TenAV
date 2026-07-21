# TenTechnology — Showroom Visit landing page

## The one file you need
**`showroom-visit-landing-page.html`** is fully self-contained. All the
TenTechnology brand colours and the handful of CSS rules core blocks can't
express on their own (button hover state, orange numbered-list markers, the
registration card's shadow, etc.) are embedded in a `<style>` block at the
very top of the page (Section 0), inside a Custom HTML block. No theme
edits, no `functions.php` changes, nothing to enqueue.

**Install:** create a new WP Page → block editor → (⋮) menu → **Code
editor** → paste the whole file in → switch back to the visual editor →
save/preview. That's it.

## Optional extra
| File | What it is |
|---|---|
| `functions-snippet.php` | Registers the five TenTechnology colours as named swatches in the block editor's Color picker UI, so whoever edits this page later sees "TenTechnology Navy" etc. instead of a raw hex value when they change a section's background. Purely a nicety for editing — the page renders correctly with or without it. |
| `event-landing.css` | The same CSS that's now embedded in Section 0 of the HTML file, kept here standalone in case you'd rather load it as a real enqueued stylesheet instead of an inline `<style>` block. Not required. |

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
