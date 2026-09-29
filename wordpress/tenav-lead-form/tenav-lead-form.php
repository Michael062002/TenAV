<?php
/**
 * Plugin Name:       TenAV Lead Form
 * Description:       Project enquiry form that emails each lead to info@tenav.co.uk and keeps a copy under Enquiries in the dashboard. Add it to a page with the [tenav_lead_form] shortcode.
 * Version:           1.0.0
 * Requires at least: 5.7
 * Requires PHP:      7.4
 * Author:            TenAV
 * License:           GPL-2.0-or-later
 * Text Domain:       tenav-lead-form
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'TENAV_LEAD_FORM_VERSION', '1.0.0' );

// Where leads are emailed. Can be overridden in wp-config.php.
if ( ! defined( 'TENAV_LEAD_FORM_RECIPIENT' ) ) {
	define( 'TENAV_LEAD_FORM_RECIPIENT', 'info@tenav.co.uk' );
}

// Submissions allowed per visitor IP address within the rate-limit window.
define( 'TENAV_LEAD_FORM_RATE_LIMIT', 5 );
define( 'TENAV_LEAD_FORM_RATE_WINDOW', 10 * MINUTE_IN_SECONDS );

/**
 * Form fields, in display order.
 *
 * @return array<string, array{label: string, required: bool, max: int}>
 */
function tenav_lead_form_fields() {
	return array(
		'first_name' => array( 'label' => 'First name', 'required' => true, 'max' => 100 ),
		'last_name'  => array( 'label' => 'Last name', 'required' => true, 'max' => 100 ),
		'email'      => array( 'label' => 'Email', 'required' => true, 'max' => 254 ),
		'phone'      => array( 'label' => 'Phone', 'required' => false, 'max' => 30 ),
		'company'    => array( 'label' => 'Company name', 'required' => false, 'max' => 150 ),
		'project'    => array( 'label' => 'Tell us about your project', 'required' => true, 'max' => 5000 ),
	);
}

/* -------------------------------------------------------------------------
 * Enquiries admin screen: every lead is also saved here, so none are lost
 * if an email goes missing.
 * ---------------------------------------------------------------------- */

add_action( 'init', 'tenav_lead_form_register_post_type' );

function tenav_lead_form_register_post_type() {
	register_post_type(
		'tenav_lead',
		array(
			'labels'          => array(
				'name'               => 'Enquiries',
				'singular_name'      => 'Enquiry',
				'menu_name'          => 'Enquiries',
				'all_items'          => 'All enquiries',
				'edit_item'          => 'Enquiry',
				'view_item'          => 'View enquiry',
				'search_items'       => 'Search enquiries',
				'not_found'          => 'No enquiries yet.',
				'not_found_in_trash' => 'No enquiries in the bin.',
			),
			'public'          => false,
			'show_ui'         => true,
			'show_in_menu'    => true,
			'show_in_rest'    => false,
			'menu_position'   => 26,
			'menu_icon'       => 'dashicons-email-alt',
			'supports'        => array( 'title' ),
			'capability_type' => 'post',
			'capabilities'    => array( 'create_posts' => 'do_not_allow' ),
			'map_meta_cap'    => true,
		)
	);
}

add_action( 'add_meta_boxes_tenav_lead', 'tenav_lead_form_add_meta_box' );

function tenav_lead_form_add_meta_box() {
	add_meta_box( 'tenav_lead_details', 'Enquiry details', 'tenav_lead_form_render_meta_box', 'tenav_lead', 'normal', 'high' );
}

function tenav_lead_form_render_meta_box( $post ) {
	echo '<table class="form-table" role="presentation"><tbody>';

	foreach ( tenav_lead_form_fields() as $key => $field ) {
		$value = (string) get_post_meta( $post->ID, '_tenav_' . $key, true );

		if ( '' === $value ) {
			$output = '&ndash;';
		} elseif ( 'email' === $key ) {
			$output = '<a href="' . esc_url( 'mailto:' . $value ) . '">' . esc_html( $value ) . '</a>';
		} elseif ( 'phone' === $key ) {
			$output = '<a href="' . esc_url( 'tel:' . preg_replace( '/[^\d+]/', '', $value ) ) . '">' . esc_html( $value ) . '</a>';
		} elseif ( 'project' === $key ) {
			$output = nl2br( esc_html( $value ) );
		} else {
			$output = esc_html( $value );
		}

		printf( '<tr><th scope="row">%s</th><td>%s</td></tr>', esc_html( $field['label'] ), $output ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above.
	}

	$page = (string) get_post_meta( $post->ID, '_tenav_page', true );
	printf(
		'<tr><th scope="row">Received</th><td>%s</td></tr>',
		esc_html( get_the_date( 'j F Y, H:i', $post ) )
	);
	if ( $page ) {
		printf( '<tr><th scope="row">Sent from</th><td><a href="%1$s">%2$s</a></td></tr>', esc_url( $page ), esc_html( $page ) );
	}

	echo '</tbody></table>';
}

add_filter( 'manage_tenav_lead_posts_columns', 'tenav_lead_form_list_columns' );

function tenav_lead_form_list_columns( $columns ) {
	return array(
		'cb'             => $columns['cb'],
		'title'          => 'Name',
		'tenav_email'    => 'Email',
		'tenav_phone'    => 'Phone',
		'tenav_company'  => 'Company',
		'tenav_received' => 'Received',
	);
}

add_action( 'manage_tenav_lead_posts_custom_column', 'tenav_lead_form_list_column_value', 10, 2 );

function tenav_lead_form_list_column_value( $column, $post_id ) {
	switch ( $column ) {
		case 'tenav_email':
			$email = (string) get_post_meta( $post_id, '_tenav_email', true );
			echo '<a href="' . esc_url( 'mailto:' . $email ) . '">' . esc_html( $email ) . '</a>';
			break;
		case 'tenav_phone':
			echo esc_html( (string) get_post_meta( $post_id, '_tenav_phone', true ) );
			break;
		case 'tenav_company':
			echo esc_html( (string) get_post_meta( $post_id, '_tenav_company', true ) );
			break;
		case 'tenav_received':
			echo esc_html( get_the_date( 'j M Y, H:i', $post_id ) );
			break;
	}
}

// Enquiries are stored as private posts; don't label every row "Private".
add_filter( 'display_post_states', 'tenav_lead_form_hide_private_state', 10, 2 );

function tenav_lead_form_hide_private_state( $states, $post ) {
	if ( 'tenav_lead' === $post->post_type ) {
		unset( $states['private'] );
	}
	return $states;
}

/* -------------------------------------------------------------------------
 * Front end: the [tenav_lead_form] shortcode.
 * ---------------------------------------------------------------------- */

add_action( 'wp_enqueue_scripts', 'tenav_lead_form_register_assets' );

function tenav_lead_form_register_assets() {
	wp_register_style( 'tenav-lead-form', plugins_url( 'assets/lead-form.css', __FILE__ ), array(), TENAV_LEAD_FORM_VERSION );
	wp_register_script( 'tenav-lead-form', plugins_url( 'assets/lead-form.js', __FILE__ ), array(), TENAV_LEAD_FORM_VERSION, true );

	// Load the stylesheet in the <head> when we can tell the page uses the form, to avoid a flash of unstyled content.
	$post = get_post();
	if ( $post && has_shortcode( $post->post_content, 'tenav_lead_form' ) ) {
		wp_enqueue_style( 'tenav-lead-form' );
	}
}

add_shortcode( 'tenav_lead_form', 'tenav_lead_form_shortcode' );

function tenav_lead_form_shortcode( $atts ) {
	static $instance = 0;
	++$instance;

	$atts = shortcode_atts(
		array(
			'title' => 'Start your project',
			'intro' => 'Tell us a little about yourself and what you have in mind, and our team will get back to you.',
		),
		$atts,
		'tenav_lead_form'
	);

	wp_enqueue_style( 'tenav-lead-form' );
	wp_enqueue_script( 'tenav-lead-form' );

	$uid    = 1 === $instance ? 'tenav-lead' : 'tenav-lead-' . $instance;
	$page   = is_singular() ? get_permalink() : home_url( '/' );
	$status = isset( $_GET['tenav_lead'] ) ? sanitize_key( wp_unslash( $_GET['tenav_lead'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- display only.
	$sent   = 'sent' === $status;

	$placeholders = array(
		'project' => 'What are you looking to achieve? Include the type of space, timescales and budget if you know them.',
	);
	$autocomplete = array(
		'first_name' => 'given-name',
		'last_name'  => 'family-name',
		'email'      => 'email',
		'phone'      => 'tel',
		'company'    => 'organization',
	);
	$types = array(
		'email' => 'email',
		'phone' => 'tel',
	);

	ob_start();
	?>
	<div class="tenav-lead" id="<?php echo esc_attr( $uid ); ?>">
		<?php if ( '' !== $atts['title'] ) : ?>
			<h2 class="tenav-lead__title"><?php echo esc_html( $atts['title'] ); ?></h2>
		<?php endif; ?>
		<?php if ( '' !== $atts['intro'] ) : ?>
			<p class="tenav-lead__intro"><?php echo esc_html( $atts['intro'] ); ?></p>
		<?php endif; ?>

		<form class="tenav-lead__form" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post"<?php echo $sent ? ' hidden' : ''; ?>>
			<div class="tenav-lead__alert" role="alert"<?php echo 'error' === $status ? '' : ' hidden'; ?>>
				<?php if ( 'error' === $status ) : ?>
					Sorry, your enquiry could not be sent. Please check the form and try again, or email us directly at <a href="mailto:<?php echo esc_attr( TENAV_LEAD_FORM_RECIPIENT ); ?>"><?php echo esc_html( TENAV_LEAD_FORM_RECIPIENT ); ?></a>.
				<?php endif; ?>
			</div>

			<input type="hidden" name="action" value="tenav_lead">
			<input type="hidden" name="tenav_page" value="<?php echo esc_url( $page ); ?>">
			<input type="hidden" name="tenav_ts" value="<?php echo esc_attr( (string) time() ); ?>">

			<div class="tenav-lead__hp" aria-hidden="true">
				<label for="<?php echo esc_attr( $uid ); ?>-hp">Leave this field empty</label>
				<input type="text" id="<?php echo esc_attr( $uid ); ?>-hp" name="tenav_hp" tabindex="-1" autocomplete="off">
			</div>

			<div class="tenav-lead__grid">
				<?php foreach ( tenav_lead_form_fields() as $key => $field ) : ?>
					<?php
					$id       = $uid . '-' . str_replace( '_', '-', $key );
					$is_wide  = in_array( $key, array( 'company', 'project' ), true );
					$type     = isset( $types[ $key ] ) ? $types[ $key ] : 'text';
					$common   = sprintf(
						'id="%1$s" name="%2$s" maxlength="%3$d" aria-describedby="%1$s-error"%4$s',
						esc_attr( $id ),
						esc_attr( $key ),
						(int) $field['max'],
						$field['required'] ? ' required' : ''
					);
					?>
					<div class="tenav-lead__field<?php echo $is_wide ? ' tenav-lead__field--full' : ''; ?>">
						<label for="<?php echo esc_attr( $id ); ?>">
							<?php echo esc_html( $field['label'] ); ?>
							<?php if ( ! $field['required'] ) : ?>
								<span class="tenav-lead__optional">(optional)</span>
							<?php endif; ?>
						</label>
						<?php if ( 'project' === $key ) : ?>
							<textarea <?php echo $common; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in sprintf. ?> placeholder="<?php echo esc_attr( $placeholders['project'] ); ?>"></textarea>
						<?php else : ?>
							<input type="<?php echo esc_attr( $type ); ?>" <?php echo $common; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in sprintf. ?> autocomplete="<?php echo esc_attr( $autocomplete[ $key ] ); ?>">
						<?php endif; ?>
						<p class="tenav-lead__error" id="<?php echo esc_attr( $id ); ?>-error"></p>
					</div>
				<?php endforeach; ?>
			</div>

			<div class="tenav-lead__footer">
				<p class="tenav-lead__privacy">We'll only use your details to respond to your enquiry.</p>
				<button type="submit" class="tenav-lead__submit">
					<span class="tenav-lead__spinner" aria-hidden="true"></span>
					<span class="tenav-lead__submit-label">Send enquiry</span>
				</button>
			</div>
		</form>

		<div class="tenav-lead__success" role="status" tabindex="-1"<?php echo $sent ? '' : ' hidden'; ?>>
			<h3 class="tenav-lead__success-title">Thanks, we've got it</h3>
			<p>Your enquiry has been sent to our team and we'll be in touch soon.</p>
		</div>
	</div>
	<?php
	return ob_get_clean();
}

/* -------------------------------------------------------------------------
 * Submission handling.
 * ---------------------------------------------------------------------- */

add_action( 'admin_post_nopriv_tenav_lead', 'tenav_lead_form_handle_submission' );
add_action( 'admin_post_tenav_lead', 'tenav_lead_form_handle_submission' );

function tenav_lead_form_handle_submission() {
	// phpcs:disable WordPress.Security.NonceVerification.Missing -- public form; a nonce would break on cached pages. Spam is handled by the honeypot, timing check and rate limit.
	$wants_json = wp_is_json_request();
	$page       = isset( $_POST['tenav_page'] ) ? wp_validate_redirect( esc_url_raw( wp_unslash( $_POST['tenav_page'] ) ), '' ) : '';

	if ( 'POST' !== ( isset( $_SERVER['REQUEST_METHOD'] ) ? $_SERVER['REQUEST_METHOD'] : '' ) ) {
		wp_safe_redirect( home_url( '/' ) );
		exit;
	}

	// Bots fill the hidden field or submit instantly; quietly pretend it worked.
	$rendered_at = isset( $_POST['tenav_ts'] ) ? (int) $_POST['tenav_ts'] : 0;
	if ( ! empty( $_POST['tenav_hp'] ) || ( $rendered_at && time() - $rendered_at < 3 ) ) {
		tenav_lead_form_respond( true, $wants_json, $page );
	}

	$rate_key = 'tenav_lead_rl_' . md5( isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '' );
	$attempts = (int) get_transient( $rate_key );
	if ( $attempts >= TENAV_LEAD_FORM_RATE_LIMIT ) {
		tenav_lead_form_respond(
			false,
			$wants_json,
			$page,
			array( 'message' => 'You have sent several enquiries in a short time. Please wait a few minutes, or email us directly at ' . TENAV_LEAD_FORM_RECIPIENT . '.' ),
			429
		);
	}

	list( $values, $errors ) = tenav_lead_form_validate( wp_unslash( $_POST ) );
	// phpcs:enable

	if ( $errors ) {
		tenav_lead_form_respond(
			false,
			$wants_json,
			$page,
			array(
				'message' => 'Please check the highlighted fields and try again.',
				'errors'  => $errors,
			),
			400
		);
	}

	set_transient( $rate_key, $attempts + 1, TENAV_LEAD_FORM_RATE_WINDOW );

	$lead_id    = tenav_lead_form_save( $values, $page );
	$mail_error = '';
	$emailed    = tenav_lead_form_send_email( $values, $page, $lead_id, $mail_error );
	$is_admin   = current_user_can( 'manage_options' );

	if ( ! $emailed ) {
		error_log( 'TenAV Lead Form: email to ' . TENAV_LEAD_FORM_RECIPIENT . ' failed: ' . ( $mail_error ? $mail_error : 'wp_mail() returned false' ) ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
	}

	if ( ! $emailed && ! $lead_id ) {
		tenav_lead_form_respond(
			false,
			$wants_json,
			$page,
			array(
				'message' => 'Sorry, your enquiry could not be sent. Please try again, or email us directly at ' . TENAV_LEAD_FORM_RECIPIENT . '.',
				'detail'  => $is_admin ? 'The website could not send the email or save the enquiry. ' . $mail_error : '',
			),
			500
		);
	}

	$data = array();
	// Only site admins see this, so the team knows email needs fixing even though the lead was saved.
	if ( ! $emailed && $is_admin ) {
		$data['adminNotice'] = 'Admin note: the enquiry was saved under Enquiries in the dashboard, but WordPress could not send the email' .
			( $mail_error ? ' (' . $mail_error . ')' : '' ) . '. Installing the "WP Mail SMTP" plugin usually fixes this.';
	}

	tenav_lead_form_respond( true, $wants_json, $page, $data );
}

/**
 * Sanitises and validates submitted values.
 *
 * @param array $raw Unslashed $_POST.
 * @return array{0: array<string, string>, 1: array<string, string>} Values and per-field error messages.
 */
function tenav_lead_form_validate( $raw ) {
	$values = array();
	$errors = array();

	foreach ( tenav_lead_form_fields() as $key => $field ) {
		$value = isset( $raw[ $key ] ) && is_string( $raw[ $key ] ) ? $raw[ $key ] : '';
		$value = 'project' === $key ? sanitize_textarea_field( str_replace( "\r\n", "\n", $value ) ) : sanitize_text_field( $value );

		$values[ $key ] = mb_substr( trim( $value ), 0, $field['max'] );
	}

	$messages = array(
		'first_name' => 'Please enter your first name.',
		'last_name'  => 'Please enter your last name.',
		'email'      => 'Please enter your email address.',
		'project'    => 'Please tell us a little about your project.',
	);
	foreach ( $messages as $key => $message ) {
		if ( '' === $values[ $key ] ) {
			$errors[ $key ] = $message;
		}
	}

	if ( '' !== $values['email'] && ! is_email( $values['email'] ) ) {
		$errors['email'] = 'Please enter a valid email address, like name@company.co.uk.';
	}

	if ( '' !== $values['phone'] ) {
		$digits = preg_replace( '/\D/', '', $values['phone'] );
		if ( ! preg_match( '/^\+?[\d\s().-]{7,30}$/', $values['phone'] ) || strlen( $digits ) < 7 ) {
			$errors['phone'] = 'Please enter a valid phone number.';
		}
	}

	return array( $values, $errors );
}

/**
 * Stores the enquiry as a private post so it shows under Enquiries in the dashboard.
 *
 * @return int Post ID, or 0 on failure.
 */
function tenav_lead_form_save( $values, $page ) {
	$title = trim( $values['first_name'] . ' ' . $values['last_name'] );
	if ( '' !== $values['company'] ) {
		$title .= ' – ' . $values['company'];
	}

	$meta = array( '_tenav_page' => $page );
	foreach ( $values as $key => $value ) {
		$meta[ '_tenav_' . $key ] = $value;
	}

	$post_id = wp_insert_post(
		array(
			'post_type'   => 'tenav_lead',
			'post_status' => 'private',
			'post_title'  => $title,
			'meta_input'  => $meta,
		),
		true
	);

	return is_wp_error( $post_id ) ? 0 : (int) $post_id;
}

/**
 * Emails the enquiry to the TenAV inbox, with Reply-To set to the person who filled in the form.
 *
 * @param string $error Receives the mailer's error message on failure.
 * @return bool Whether WordPress accepted the email for delivery.
 */
function tenav_lead_form_send_email( $values, $page, $lead_id, &$error ) {
	$name    = trim( $values['first_name'] . ' ' . $values['last_name'] );
	$subject = 'New project enquiry: ' . $name . ( '' !== $values['company'] ? ' (' . $values['company'] . ')' : '' );

	$lines = array(
		'New project enquiry from the website',
		'',
		'Name:     ' . $name,
		'Email:    ' . $values['email'],
		'Phone:    ' . ( '' !== $values['phone'] ? $values['phone'] : '-' ),
		'Company:  ' . ( '' !== $values['company'] ? $values['company'] : '-' ),
		'',
		'Project details:',
		$values['project'],
		'',
		'---',
		'Reply to this email to respond directly to ' . $values['first_name'] . '.',
	);
	if ( $page ) {
		$lines[] = 'Sent from: ' . $page;
	}
	if ( $lead_id ) {
		$lines[] = 'Saved in WordPress: ' . admin_url( 'post.php?post=' . $lead_id . '&action=edit' );
	}

	// wp_mail() splits Reply-To on commas and quotes the name itself, so keep the display name to plain characters.
	$reply_name = trim( str_replace( array( ',', '"', '<', '>', ';' ), '', $name ) );
	$headers    = array( 'Reply-To: ' . ( '' !== $reply_name ? $reply_name . ' ' : '' ) . '<' . $values['email'] . '>' );

	$capture = function ( $wp_error ) use ( &$error ) {
		$error = $wp_error->get_error_message();
	};
	add_action( 'wp_mail_failed', $capture );
	$sent = wp_mail( apply_filters( 'tenav_lead_form_recipient', TENAV_LEAD_FORM_RECIPIENT ), $subject, implode( "\n", $lines ), $headers );
	remove_action( 'wp_mail_failed', $capture );

	return (bool) $sent;
}

/**
 * Sends the result back: JSON for the in-page submit, or a redirect back to the form without JavaScript.
 */
function tenav_lead_form_respond( $ok, $wants_json, $page, $data = array(), $status = 200 ) {
	if ( $wants_json ) {
		if ( $ok ) {
			wp_send_json_success( $data );
		}
		wp_send_json_error( $data, $status );
	}

	$back = $page ? $page : wp_get_referer();
	$back = $back ? remove_query_arg( 'tenav_lead', $back ) : home_url( '/' );
	wp_safe_redirect( add_query_arg( 'tenav_lead', $ok ? 'sent' : 'error', $back ) . '#tenav-lead' );
	exit;
}
