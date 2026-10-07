# TenAV lead form for WordPress

**For tenav.co.uk you only need the section "Elementor contact widget (Capsule CRM + email)".** "Set up (about 5 minutes)", "Google Ads conversion tracking (optional)" and "Alternative: shortcode" describe a different, plain version of the form: don't paste `lead-form.html` anywhere on the live site.

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

If Capsule decides to show its "I'm not a robot" check, the check appears inside the form for the visitor to complete, so the lead isn't lost. A button above it opens the check in a new tab, for when it doesn't load or fit (phones, blocked third-party cookies); the contact page still shows Thank You once it's completed there. Each tab keeps track of its own enquiry, and the widget can appear more than once on a page. If Capsule hasn't answered after 20 seconds, the visitor is offered the new-tab button and the phone number.

If Capsule is down or shows an error, the visitor sees Capsule's error in the check box. The email to info@, the copy under Enquiries and the Google Ads/GA4 conversion are still recorded.

The form only talks to addresses on your home page (`/?tenav_endpoint=lead` and `/?tenav_endpoint=capsule_done`), never to `/wp-admin/`. Plugins can send logged-out visitors from `/wp-admin/` to the WordPress login screen. Easy Google Fonts did this on tenav.co.uk, so only logged-in admins' test leads got through.

### Install

1. Upload `tenav-lead-form.zip` under **Plugins → Add Plugin → Upload Plugin** (called **Add New Plugin** before WordPress 6.8) and click **Install Now**. WordPress shows **This plugin is already installed** and a Current/Uploaded table. Check Uploaded shows 1.3.1, then click **Replace current with uploaded**. The plugin stays active; only if an **Activate Plugin** button appears, click it. Don't go back to an older version: those are the ones that lost visitors' leads.
2. Quick check, before any test enquiry: open `https://tenav.co.uk/?tenav_endpoint=capsule_done` in a private window. It should say "Thank You", not show the login screen or your homepage. Then close that window.
3. In Elementor, replace everything in the HTML widget with the contents of `elementor-contact-widget.html`, then click **Publish** (older Elementor: **Update**). Do this as an Administrator, then reload the editor and check the code still contains `<script` and `FORM_ID`: if they're gone, the account can't save scripts (look for `DISALLOW_UNFILTERED_HTML` in `wp-config.php`). Replace every copy of the form, on every page or template. Don't press Let's Chat inside the editor: it emails info@ and saves an Enquiry.
4. Clear Elementor's cache (**Elementor → Tools → General → Clear Files & Data**; older versions: **Regenerate Files & Data**), then any page cache (caching plugin, host or Cloudflare).
5. **Test in a private/Incognito window,** so you are logged out like a real visitor. Use a new email address. You should see the Thank You message, the lead in Capsule, an email at info@, and a copy under **Enquiries**. Testing while logged in to WordPress can hide problems that only affect visitors.
   - On the first test, open **F12 → Network** before submitting. The `newlead` request should be status 302, with a `location` starting with your site's address and `/?tenav_endpoint=capsule_done&tenav_ref=`. If it points to `/thank-you/` or another page, set Capsule's completion redirect (if it has one) to `https://tenav.co.uk/?tenav_endpoint=capsule_done`.
   - Once, set Capsule's CAPTCHA to "Always" and test from an iPhone (Safari) and a Chrome Incognito window: the check should work in the form or through the new-tab button, and the contact should arrive in Capsule. Then set it back.
   - If you also test while logged in and the email fails, a yellow note under the Thank You message tells you why. Only logged-in admins see that note.

### If the email doesn't arrive at info@

Look under **Enquiries** in the WordPress dashboard.

- **The enquiry is there:** WordPress received it, but your web host isn't delivering its email. This is common, especially when sending to your own domain. Install the free **WP Mail SMTP** plugin and connect it to the account you use for tenav.co.uk (Microsoft 365, Google Workspace or your host's mailbox). Its **Email Test** tab confirms delivery.
- **The enquiry isn't there:** the form couldn't reach the plugin. Check the plugin is active, then press **F12 → Console** on the contact page and submit again. The warning shown explains why:
  - "You have sent several enquiries in a short time": the spam limit (10 accepted enquiries per connection; the 10-minute timer restarts with each one). Wait 10 minutes, or test logged in as an admin, who is exempt.
  - "unexpected response (status 403)" or "(status 503)": a firewall (Cloudflare WAF, Wordfence or the host's) blocked the request. Allow requests to `?tenav_endpoint=lead`.
  - "unexpected response (status 200) … Is the TenAV Lead Form plugin … active?": the plugin is missing or older than 1.3.1, or a cache answered.

### If the Thank You message never appears

Close every tab showing the contact page, then open `https://tenav.co.uk/?tenav_endpoint=capsule_done` in a new private/Incognito window.

- **You see the WordPress login screen:** the plugin isn't active or is older than 1.3.1. Update it.
- **You see your homepage:** a page cache is ignoring the query string. The in-page Thank You still works, but a check finished in a new tab won't update the contact page. Exclude addresses containing `tenav_endpoint` from your caching plugin, host cache or Cloudflare cache rules.
- **You see "Thank You", but the form never shows it:** on the contact page press **F12 → Network**, tick **Preserve log**, send a test, and click the request for `?tenav_endpoint=capsule_done`. If its response headers include `content-security-policy` with `frame-ancestors 'none'` (or a list without your own site), your host, `.htaccess` or Cloudflare adds it: remove it for addresses containing `tenav_endpoint`, or change it to `frame-ancestors 'self'`. `x-frame-options: DENY` is harmless here, because the plugin's own `frame-ancestors 'self'` takes priority. Capsule still received these leads, so check Capsule before asking visitors to resend.
- **A plugin delays or combines JavaScript** (for example WP Rocket "Delay JavaScript execution", LiteSpeed Cache or Autoptimize): exclude the contact page from that setting if the form behaves differently from a private-window test with the setting off.

### If leads don't appear in Capsule

- **Capsule settings:** check the website form integration is switched on in Capsule.
- **Form key:** if you reset the form key, copy the new `FORM_ID` into this code.
- **Completion redirect:** if Capsule's form settings have one, it must be `https://tenav.co.uk/?tenav_endpoint=capsule_done`, not `/thank-you/`.
- **Existing contacts:** if the email address is already in Capsule, Capsule may add the enquiry to that contact or create a second contact. Search Capsule for the email address and check both. Use a brand-new email address for each test.

### Google Ads and GA4 (Google Tag Manager)

When a lead goes through, the form sends this event to Google Tag Manager:

```js
{ event: 'tenav_lead_submitted', form_id: 'tenavForm', lead_confirmed_by: 'website' /* or 'capsule' */, lead_id: 'muy61prxiigpzl' }
```

It is sent once per page view, as soon as either your website (email sent and saved under Enquiries) or Capsule confirms the lead. It is still sent when Capsule's check is completed in a new tab, and never for a form that fails validation or a bot caught by the spam trap. `lead_id` is unique per enquiry. `lead_confirmed_by` only says which system answered first: to check both received a lead, look in Capsule and Enquiries.

Change GTM only after every copy of the form runs this code: the old code sends no `tenav_lead_submitted` event. Set up GTM (container `GTM-PFK5GG7`) to use it:

1. **Triggers → New → Custom Event.** Event name: `tenav_lead_submitted`. Fires on: All Custom Events (don't filter on `form_id`). Name it **TenAV lead submitted**. Save.
2. Open the Google Ads tag **Submit lead form** (`AW-1019138282/4uhsCLSK6vEaEOqh--UD`). Remove the Element Visibility trigger on `#tenavSuccess > p:nth-child(2)` and add **TenAV lead submitted**. Optional: create a Data Layer Variable for `lead_id` and put it in the tag's **Transaction ID**, so Google Ads drops duplicates. Save.
3. Open the GA4 event tag `contact_form_submit`. Remove its trigger for the old HubSpot form (`hsForm_e3b3dfe7-9a69-4c79-93a0-06ca07b1cbf5`) and add **TenAV lead submitted**. Replace any built-in `{{Form …}}` variables in its parameters. Save.
4. Read the **Firing Triggers** column under Tags. Switch every tag still on the old Element Visibility or HubSpot trigger (including any Microsoft Ads, Meta or LinkedIn tags) to **TenAV lead submitted**, then delete the two old triggers. Keeping an old trigger on a tag either counts leads twice or misses leads.
5. **Preview**, accept the cookie banner, send a test enquiry, and check that `tenav_lead_submitted` appears and both tags fire once. Then **Submit → Publish and Create Version** with a version name. To undo: **Versions →** previous version **→ ⋮ → Publish**.
6. In Google Ads (**Goals → Conversions → Summary**), set **Submit lead form** to Count **One** and **Primary**. In every goal, set any other action that measures the same enquiry (for example `contact_form_submit` imported from GA4) to **Secondary**, or each lead counts twice. View the contact page's source: `4uhsCLSK6vEaEOqh--UD` should not appear (only GTM sends it), and the GTM snippet should end `'dataLayer','GTM-PFK5GG7')`.

Google Ads and GA4 will show fewer leads than Enquiries, because visitors who decline cookies aren't counted.

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
