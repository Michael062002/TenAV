<?php
/**
 * TenTechnology "Showroom Visit" landing page — functions.php snippet.
 *
 * Add this to your active theme's functions.php (or a small site-specific
 * plugin). It does two things the page markup relies on:
 *
 *   1. Registers the TenTechnology brand colours as named swatches in the
 *      block editor's Color settings, so the palette used in
 *      showroom-visit-landing-page.html (backgroundColor/textColor
 *      "tentech-navy", "tentech-orange", etc.) shows up correctly and stays
 *      editable by the client — not just hardcoded inline styles.
 *   2. Enqueues event-landing.css for the handful of rules core blocks
 *      can't express (button hover state, numbered-list marker colour,
 *      the registration widget's card shadow, footer link colours).
 *
 * If your theme already declares its own editor-color-palette via
 * theme.json, merge these five TenTechnology colours into that file
 * instead of using add_theme_support() here — theme.json takes
 * precedence and the two can conflict.
 */

/**
 * Register wide/full block alignment support and the TenTechnology palette.
 * Skipped automatically if the active theme already defines a palette via
 * theme.json (see note above).
 */
function tentech_landing_page_theme_support() {
	add_theme_support( 'align-wide' );

	add_theme_support( 'editor-color-palette', array(
		array(
			'name'  => 'White',
			'slug'  => 'white',
			'color' => '#FFFFFF',
		),
		array(
			'name'  => 'TenTechnology Navy',
			'slug'  => 'tentech-navy',
			'color' => '#0B1A2B',
		),
		array(
			'name'  => 'TenTechnology Orange',
			'slug'  => 'tentech-orange',
			'color' => '#C97A3D',
		),
		array(
			'name'  => 'TenTechnology Callout',
			'slug'  => 'tentech-callout',
			'color' => '#E7ECF3',
		),
		array(
			'name'  => 'TenTechnology Charcoal',
			'slug'  => 'tentech-charcoal',
			'color' => '#16233A',
		),
		array(
			'name'  => 'TenTechnology Muted Grey',
			'slug'  => 'tentech-muted',
			'color' => '#6B7280',
		),
	) );
}
add_action( 'after_setup_theme', 'tentech_landing_page_theme_support' );

/**
 * Enqueue the companion stylesheet for the showroom visit landing page.
 * Loaded site-wide for simplicity; scope this to the specific page template
 * or slug with is_page() if you'd rather not load it everywhere.
 */
function tentech_landing_page_styles() {
	wp_enqueue_style(
		'tentech-event-landing',
		get_stylesheet_directory_uri() . '/event-landing.css',
		array(),
		'1.0.0'
	);
}
add_action( 'wp_enqueue_scripts', 'tentech_landing_page_styles' );

/**
 * The registration widget's Custom HTML block includes an inline <script>.
 * WordPress only strips <script> tags from Custom HTML blocks for users
 * without the unfiltered_html capability (by default, non-admins on
 * single-site, or anyone on multisite). If the page will be edited by an
 * admin on a single-site install, no change is needed. If editors without
 * unfiltered_html need to maintain this page, either:
 *   a) grant unfiltered_html to that role, or
 *   b) move the widget's <script> contents into a properly enqueued .js
 *      file instead of relying on the Custom HTML block to preserve it.
 */
