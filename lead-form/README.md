# TenAV lead form

A self-contained enquiry form (`index.html`) with no build step or dependencies. The fields are first name, last name, email, phone, company name and "Tell us about your project". Each submission is emailed to **info@tenav.co.uk**.

## How submissions reach the inbox

The site is static HTML, so the form uses [FormSubmit](https://formsubmit.co) to turn a submission into an email. FormSubmit is free and needs no account or API key.

- The email subject looks like `New project enquiry: Jane Smith (Acme Ltd)`. Its body is a table of the answers.
- Hitting **Reply** in the inbox replies straight to the person who filled in the form.
- A hidden "honeypot" field catches most spam bots.

## One-time setup (required)

1. Put the page on the live site, open it and send a test enquiry.
2. FormSubmit emails info@tenav.co.uk asking you to **confirm the form**. Click the button in that email.
   - Until you confirm, submissions are **not delivered**.
   - Until then, the form shows an error with a link to email info@tenav.co.uk directly.
3. Send another test. It should arrive in the inbox.

### Optional: hide the email address from the page source

After you confirm, FormSubmit's email also gives you a random alias (e.g. `a1b2c3d4e5...`). Swap the address for that alias in the two places it appears in `index.html`:

```html
action="https://formsubmit.co/YOUR_ALIAS"
data-endpoint="https://formsubmit.co/ajax/YOUR_ALIAS"
```

This stops scrapers from reading info@tenav.co.uk out of the form code. It also stops anyone from reusing the endpoint to send mail to that address.

## Adding it to the website

- **Standalone page:** upload `index.html` as, for example, `/contact/index.html`.
- **Inside an existing page:** copy the `<style>` block, the `<main class="lead-card">…</main>` block and the `<script>` block into the page.
- **Website builder (Wix, Squarespace, WordPress, etc.):** use an "Embed HTML" / "Custom code" block and paste in the whole file. You can also host the file and load it in an `<iframe>`.

## Customising

- **Colours:** edit the variables at the top of the `<style>` block. `--accent` is the button and focus colour.
- **Required fields:** first name, last name, email and project details are required. Phone and company are optional. To change this:
  1. Add or remove `required` on the input.
  2. Update the matching case in the `validate()` function in the script.
  3. Update the "(optional)" label text.
- **Wording:** the heading, intro, placeholder and thank-you message are plain text in the HTML.
