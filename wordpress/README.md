# TenAV lead form for WordPress

A project enquiry form that asks for:
- first name
- last name
- email
- phone (optional)
- company name (optional)
- "Tell us about your project"

Each enquiry:
- **is emailed to info@tenav.co.uk** by your own WordPress site. No outside service or sign-up is involved.
- **is also saved in the dashboard under Enquiries**, so a lead is never lost even if an email goes astray.

There are two parts:

| File | What it is |
| --- | --- |
| `lead-form.html` | The form itself, as **plain HTML with no JavaScript**. You paste it into your page. |
| `tenav-lead-form.zip` | A small plugin that sends the email behind the scenes. HTML on its own can't send email, so this does it. It adds nothing to your pages, so it won't affect your Google Ads tag or anything else in your site's header. |

## Set up (about 5 minutes)

1. **Install the plugin (one time).**
   1. In WordPress, go to **Plugins → Add New Plugin → Upload Plugin**.
   2. Choose `tenav-lead-form.zip` and click **Install Now**.
   3. Click **Activate**.
2. **Add the form to your page.**
   1. Edit the page, for example Contact.
   2. Add a **Custom HTML** block, then open `lead-form.html` in Notepad (or any text editor).
   3. Copy everything and paste it into the block. Click **Update**.
   - With Elementor or Divi, paste it into their **HTML** widget/module instead.
3. **Test it.** Open the page, fill in the form and press **Send enquiry**.
   - The enquiry should arrive at info@tenav.co.uk. Check spam/junk the first time.
   - It will also appear under **Enquiries** in the WordPress dashboard.

## Google Ads conversion tracking (optional)

To count each enquiry as a conversion, send people to a thank-you page after they submit:

1. Create a normal WordPress page, e.g. **Thank you**, at `/thank-you/`.
2. In the HTML you pasted, find this line:

   ```html
   <input type="hidden" name="tenav_redirect" value="">
   ```

3. Put the page address in the quotes:

   ```html
   <input type="hidden" name="tenav_redirect" value="/thank-you/">
   ```

4. In Google Ads, set up the conversion as a **page load** on URLs containing `/thank-you`.

If you leave `tenav_redirect` empty, the thank-you message appears in place of the form on the same page.

## If the email doesn't arrive

The enquiry is still saved under **Enquiries**, so nothing is lost.

Missing emails almost always mean your web host isn't set up to send email. This is common and easy to fix:
1. Install the free **WP Mail SMTP** plugin.
2. Connect it to the email account you already use for tenav.co.uk (Microsoft 365, Google Workspace, or your host's mailbox).
3. Send another test.

## Changing the form

- **Wording:** edit the text in the HTML directly, e.g. the heading, intro, button label and thank-you message. Don't change any `name="..."` values, or the plugin won't recognise the fields.
- **Colours:** change `--tenav-accent` and `--tenav-accent-hover` near the top of the `<style>` section.
- **Send to a different address:** add this line to `wp-config.php`:

  ```php
  define( 'TENAV_LEAD_FORM_RECIPIENT', 'sales@tenav.co.uk' );
  ```

### Alternative: shortcode instead of HTML

If you'd rather not paste HTML, add a **Shortcode** block containing `[tenav_lead_form]`. This version uses a little JavaScript to check fields and send without reloading the page. It still leaves your Google tag alone.

## Spam protection

- A hidden trap field that bots fill in.
- A limit of 5 enquiries per visitor every 10 minutes.
- The shortcode version also rejects forms submitted too fast for a person.

Spam is dropped silently.

## Privacy

Enquiries are stored in your WordPress database. You can delete old ones from **Enquiries** (tick them → Bulk actions → Move to Bin) in line with your data retention policy.
