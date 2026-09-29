# TenAV lead form

A self-contained enquiry form (`index.html`) with no build step or dependencies. The fields are first name, last name, email, phone, company name and "Tell us about your project". Each submission is emailed to **info@tenav.co.uk**.

## How submissions reach the inbox

The site is static HTML, so the form uses [FormSubmit](https://formsubmit.co) to turn a submission into an email. FormSubmit is free and needs no account or API key.

- The email subject looks like `New project enquiry: Jane Smith (Acme Ltd)`. Its body is a table of the answers.
- Hitting **Reply** in the inbox replies straight to the person who filled in the form.
- A hidden "honeypot" field catches most spam bots.

## One-time setup (required)

> **The form only sends from a real website.** FormSubmit rejects submissions from a page opened as a file on your computer or shown in a preview window. In that case the form shows a yellow "Preview only" notice. Upload it to your site before testing.

1. Put the page on the live site and open it from its `https://` address. Send a test enquiry.
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

## Troubleshooting

When sending fails, the red error box shows the actual reason in small text underneath:

| Reason shown | What to do |
| --- | --- |
| Yellow "Preview only" notice | The page isn't on a website yet. Upload it and test from the live `https://` page. |
| "This form needs Activation…" | Find the FormSubmit email in the info@tenav.co.uk inbox and click **Activate Form**. Check the spam/junk folder too. |
| "Could not connect to the email service" | Something is blocking formsubmit.co, such as an ad blocker, a strict company network or the site's security settings. Try another browser or network. |
| Success message shows but no email arrives | Check spam/junk in info@tenav.co.uk. If it's there, mark it "not spam" and add the FormSubmit sender to your safe senders list. |

## Customising

- **Colours:** edit the variables at the top of the `<style>` block. `--accent` is the button and focus colour.
- **Required fields:** first name, last name, email and project details are required. Phone and company are optional. To change this:
  1. Add or remove `required` on the input.
  2. Update the matching case in the `validate()` function in the script.
  3. Update the "(optional)" label text.
- **Wording:** the heading, intro, placeholder and thank-you message are plain text in the HTML.
