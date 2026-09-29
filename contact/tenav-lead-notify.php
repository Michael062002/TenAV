<?php
/**
 * TenAV – email a copy of every "Request a Callback" lead to info@tenav.co.uk.
 *
 * The contact component still posts each lead to Capsule CRM as before; its
 * script also sends the same details here so the team gets an inbox copy.
 *
 * Install: add this as a new snippet in the "Code Snippets" plugin
 * (set to "Run everywhere"), or paste it into the child theme's functions.php.
 * Leave out the opening "<?php" line if your snippet tool adds it for you.
 *
 * Test: while logged in as an administrator, visit
 *   https://tenav.co.uk/wp-admin/admin-ajax.php?action=tenav_lead_test
 * to send a test email and see whether WordPress could send it, and why not.
 */

if ( ! defined( 'TENAV_LEAD_NOTIFY_TO' ) ) {
	define( 'TENAV_LEAD_NOTIFY_TO', 'info@tenav.co.uk' );
}

add_action( 'wp_ajax_tenav_lead_notify', 'tenav_lead_notify' );
add_action( 'wp_ajax_nopriv_tenav_lead_notify', 'tenav_lead_notify' );
add_action( 'wp_ajax_tenav_lead_test', 'tenav_lead_test' );

// Keep the last mail error so the test page can show it.
add_action( 'wp_mail_failed', function ( $error ) {
	set_transient( 'tenav_lead_last_error', $error->get_error_message(), DAY_IN_SECONDS );
} );

function tenav_lead_send( $subject, $body, $reply_to = '' ) {

	$headers = array( 'Content-Type: text/plain; charset=UTF-8' );

	if ( $reply_to ) {
		$headers[] = 'Reply-To: ' . $reply_to;
	}

	delete_transient( 'tenav_lead_last_error' );

	return wp_mail( TENAV_LEAD_NOTIFY_TO, $subject, $body, $headers );
}

function tenav_lead_notify() {

	// Honeypot: real visitors never fill this in. Pretend success for bots.
	if ( ! empty( $_POST['website'] ) ) {
		wp_send_json_success();
	}

	$fields = array(
		'FIRST_NAME'        => 'First name',
		'LAST_NAME'         => 'Last name',
		'EMAIL'             => 'Email',
		'PHONE'             => 'Phone',
		'ORGANISATION_NAME' => 'Company',
		'NOTE'              => 'Project details',
	);

	$lead = array();

	foreach ( $fields as $key => $label ) {
		$value = isset( $_POST[ $key ] ) ? wp_unslash( $_POST[ $key ] ) : '';
		$value = ( 'NOTE' === $key ) ? sanitize_textarea_field( $value ) : sanitize_text_field( $value );

		if ( '' === $value ) {
			wp_send_json_error( array( 'message' => 'Missing field: ' . $key ), 400 );
		}

		$lead[ $key ] = $value;
	}

	$lead['EMAIL'] = sanitize_email( $lead['EMAIL'] );

	if ( ! is_email( $lead['EMAIL'] ) ) {
		wp_send_json_error( array( 'message' => 'Invalid email.' ), 400 );
	}

	// Basic flood protection: at most 5 notifications per IP every 10 minutes.
	$ip       = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	$rate_key = 'tenav_lead_' . md5( $ip );
	$count    = (int) get_transient( $rate_key );

	if ( $count >= 5 ) {
		wp_send_json_error( array( 'message' => 'Too many requests.' ), 429 );
	}

	set_transient( $rate_key, $count + 1, 10 * MINUTE_IN_SECONDS );

	$page = isset( $_POST['PAGE_URL'] ) ? esc_url_raw( wp_unslash( $_POST['PAGE_URL'] ) ) : '';

	$subject = sprintf(
		'New web lead: %s %s – %s',
		$lead['FIRST_NAME'],
		$lead['LAST_NAME'],
		$lead['ORGANISATION_NAME']
	);

	$body  = "A new callback request has been submitted on the website.\n";
	$body .= "It has also been sent to Capsule CRM.\n\n";

	foreach ( $fields as $key => $label ) {
		if ( 'NOTE' === $key ) {
			continue;
		}
		$body .= $label . ': ' . $lead[ $key ] . "\n";
	}

	$body .= "\nProject details:\n" . $lead['NOTE'] . "\n\n";

	if ( $page ) {
		$body .= 'Submitted from: ' . $page . "\n";
	}

	$body .= 'Submitted at: ' . wp_date( 'd/m/Y H:i' ) . "\n";

	if ( tenav_lead_send( $subject, $body, $lead['EMAIL'] ) ) {
		wp_send_json_success();
	}

	wp_send_json_error( array( 'message' => 'Email could not be sent.' ), 500 );
}

function tenav_lead_test() {

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Please log in to WordPress as an administrator, then open this link again.' );
	}

	$sent  = tenav_lead_send(
		'TenAV website – test lead email',
		"This is a test from the TenAV contact form notification.\n\nIf you can read this, lead emails will reach this inbox.\n"
	);
	$error = get_transient( 'tenav_lead_last_error' );

	header( 'Content-Type: text/plain; charset=UTF-8' );

	echo "TenAV lead email test\n=====================\n\n";
	echo 'Sending to: ' . TENAV_LEAD_NOTIFY_TO . "\n";
	echo 'WordPress result: ' . ( $sent ? 'SENT (handed to the mail server)' : 'FAILED' ) . "\n";

	if ( $error ) {
		echo 'Error: ' . $error . "\n";
	}

	echo "\n";

	if ( $sent ) {
		echo "If it does not arrive within a few minutes (check Junk/Spam and any\n";
		echo "quarantine in Microsoft 365 / Google Workspace), your hosting mail is\n";
		echo "being blocked. Install the \"WP Mail SMTP\" plugin and connect it to the\n";
		echo "info@tenav.co.uk mailbox provider, then run this test again.\n";
	} else {
		echo "WordPress could not send mail from this server. Install the\n";
		echo "\"WP Mail SMTP\" plugin and connect it to your email provider, then\n";
		echo "run this test again.\n";
	}

	exit;
}
