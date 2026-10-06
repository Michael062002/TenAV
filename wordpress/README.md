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

## Elementor contact widget (Capsule CRM + email)

`elementor-contact-widget.html` is the TenAV contact section with the "Request a Callback" form. When someone presses **Let's Chat**:

1. Their details go to **Capsule CRM**, in the background. The visitor stays on the page.
2. A copy is **emailed to info@tenav.co.uk**, and saved under **Enquiries** in WordPress.
3. The **Thank You** message appears in the form once Capsule has confirmed it received the lead.

Your `/thank-you/` page is not loaded at all. Capsule is pointed at a blank page provided by the plugin, just so the form can tell the lead arrived.

If Capsule decides to show its "I'm not a robot" check, the check appears inside the form for the visitor to complete, so the lead isn't lost. If the visitor opens the check in a new tab instead, the contact page still shows Thank You once they complete it.

The form only talks to addresses on your home page (`/?tenav_endpoint=lead` and `/?tenav_endpoint=capsule_done`), never to `/wp-admin/`. Plugins can send logged-out visitors from `/wp-admin/` to the WordPress login screen. Easy Google Fonts did this on tenav.co.uk, so only logged-in admins' test leads got through.

### Install

1. Upload `tenav-lead-form.zip` under **Plugins → Add New Plugin → Upload Plugin**. You need version 1.3.0 or later: if an older version is installed, choose **Replace current with uploaded**. Then **Activate**.
2. In Elementor, replace everything in the HTML widget with the contents of `elementor-contact-widget.html`, then click **Update**.
3. Clear any page cache (your caching plugin, host or Cloudflare).
4. **Test in a private/Incognito window,** so you are logged out like a real visitor. Use a new email address. You should see the Thank You message, the lead in Capsule, an email at info@, and a copy under **Enquiries**. Testing while logged in to WordPress can hide problems that only affect visitors.
   - Quick check: open `https://tenav.co.uk/?tenav_endpoint=capsule_done` in the private window. It should say "Thank You", not show the WordPress login screen.
   - If you also test while logged in and the email fails, a yellow note under the Thank You message tells you why. Only logged-in admins see that note.

### If the email doesn't arrive at info@

Look under **Enquiries** in the WordPress dashboard.

- **The enquiry is there:** WordPress received it, but your web host isn't delivering its email. This is common, especially when sending to your own domain. Install the free **WP Mail SMTP** plugin and connect it to the account you use for tenav.co.uk (Microsoft 365, Google Workspace or your host's mailbox). Its **Email Test** tab confirms delivery.
- **The enquiry isn't there:** the form couldn't reach the plugin. Check the plugin is active, then press **F12 → Console** on the contact page and submit again. The warning shown explains why.

### If leads don't appear in Capsule

- **Capsule settings:** check the website form integration is switched on in Capsule.
- **Form key:** if you reset the form key, copy the new `FORM_ID` into this code.
- **Existing contacts:** if the email address already belongs to a contact, look in that contact's history rather than for a new lead.

### Google Ads and GA4 (Google Tag Manager)

When a lead goes through, the form sends this event to Google Tag Manager:

```js
{ event: 'tenav_lead_submitted', form_id: 'tenavForm', lead_confirmed_by: 'website' /* or 'capsule' */ }
```

It is sent once per page view, as soon as either your website (email sent and saved under Enquiries) or Capsule confirms the lead. It is still sent when Capsule's check is completed in a new tab, and never for a form that fails validation.

Set up GTM (container `GTM-PFK5GG7`) to use it:

1. **Triggers → New → Custom Event.** Event name: `tenav_lead_submitted`. Fires on: All Custom Events. Name it **TenAV lead submitted**. Save.
2. Open the Google Ads tag **Submit lead form** (`AW-1019138282/4uhsCLSK6vEaEOqh--UD`). Remove the Element Visibility trigger on `#tenavSuccess > p:nth-child(2)` and add **TenAV lead submitted**. Save.
3. Open the GA4 event tag `contact_form_submit`. Remove its trigger for the old HubSpot form (`hsForm_e3b3dfe7-9a69-4c79-93a0-06ca07b1cbf5`) and add **TenAV lead submitted**. Save.
4. If nothing else uses the old Element Visibility and HubSpot triggers, delete them. Keeping them on the same tags would count leads twice.
5. **Preview**, open the contact page, send a test enquiry and check that `tenav_lead_submitted` appears and both tags fire once. Then **Submit → Publish**.

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
- A limit of 10 enquiries per visitor every 10 minutes.
- The shortcode version also rejects forms submitted too fast for a person.

Spam is dropped silently.

## Privacy

Enquiries are stored in your WordPress database. You can delete old ones from **Enquiries** (tick them → Bulk actions → Move to Bin) in line with your data retention policy.
