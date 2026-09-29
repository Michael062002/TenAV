# TenAV Lead Form (WordPress plugin)

This plugin adds a project enquiry form to your WordPress site. The form asks for:
- first name
- last name
- email
- phone (optional)
- company name (optional)
- "Tell us about your project"

Each enquiry:
- **is emailed to info@tenav.co.uk** by your own WordPress site. No outside service or sign-up is involved.
- **is also saved in the dashboard under Enquiries**, so a lead is never lost even if an email goes astray.

## Install (about 2 minutes)

1. Download **`tenav-lead-form.zip`**.
2. In WordPress, go to **Plugins → Add New Plugin → Upload Plugin**. Choose the zip, click **Install Now**, then **Activate**.
3. Open the page where you want the form, such as Contact, in the editor.
4. Add a **Shortcode** block and type:

   ```
   [tenav_lead_form]
   ```

   With Elementor, Divi or WPBakery, use their "Shortcode" widget/module instead.
5. Click **Update**, then open the page and send yourself a test enquiry.

## Check it worked

- The test should arrive at **info@tenav.co.uk**. Check spam/junk the first time.
- It will also appear under **Enquiries** in the WordPress dashboard menu.
- Hitting **Reply** on the email replies straight to the person who filled in the form.

### If the email doesn't arrive

The enquiry is still saved under **Enquiries**, so nothing is lost.

Missing emails almost always mean your web host isn't set up to send email. This is common and easy to fix:
1. Install the free **WP Mail SMTP** plugin.
2. Connect it to the email account you already use for tenav.co.uk (Microsoft 365, Google Workspace, or your host's mailbox).
3. Send another test.

If you are logged in to WordPress when you test, the thank-you message tells you when the email failed and why. Visitors never see this.

## Customising

- **Heading and intro text:** change them in the shortcode:

  ```
  [tenav_lead_form title="Get a quote" intro="Tell us about your space and we'll be in touch."]
  ```

  Use `title=""` to hide the heading.
- **Colours:** go to **Appearance → Customize → Additional CSS**, or **Appearance → Editor → Styles → Additional CSS** on block themes. Add:

  ```css
  .tenav-lead { --tenav-accent: #1d4ed8; --tenav-accent-hover: #1e40af; }
  ```

- **Send to a different address:** add this line to `wp-config.php`:

  ```php
  define( 'TENAV_LEAD_FORM_RECIPIENT', 'sales@tenav.co.uk' );
  ```

## Spam protection

- A hidden trap field that bots fill in.
- A check that rejects forms submitted too fast for a person.
- A limit of 5 enquiries per visitor every 10 minutes.

Spam is dropped silently, so bots can't tell they were caught.

## Privacy

Enquiries are stored in your WordPress database. You can delete old ones from **Enquiries** (tick them → Bulk actions → Move to Bin) in line with your data retention policy.
