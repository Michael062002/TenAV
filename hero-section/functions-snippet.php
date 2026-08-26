<?php
/**
 * Enqueues the TenAV hero section's CSS/JS as real WordPress assets
 * instead of inline <style>/<script> in page content.
 *
 * Add this to your child theme's functions.php, or (preferred, since it
 * survives a theme switch/update) a small site-specific plugin.
 *
 * Update the two file paths below to wherever hero.css / hero.js
 * actually live in your theme or plugin, and update the slug(s) in
 * tenav_hero_should_enqueue() to match the page(s) that use the hero.
 */

add_action( 'wp_enqueue_scripts', 'tenav_hero_enqueue_assets' );

function tenav_hero_should_enqueue() {
	// Only load these assets on the page(s) that actually use the hero,
	// instead of on every page site-wide. Add every slug that uses it.
	return is_page( array( 'own-everything-either-side-of-the-port' ) );
}

function tenav_hero_enqueue_assets() {
	if ( ! tenav_hero_should_enqueue() ) {
		return;
	}

	// Poppins, loaded directly — the theme's own enqueue was confirmed
	// 404ing on the 700 weight in the browser console.
	wp_enqueue_style(
		'tenav-hero-poppins',
		'https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;800&display=swap',
		array(),
		null
	);

	wp_enqueue_style(
		'tenav-hero',
		get_stylesheet_directory_uri() . '/hero-section/hero.css',
		array(),
		'1.0.0'
	);

	wp_enqueue_script(
		'tenav-hero',
		get_stylesheet_directory_uri() . '/hero-section/hero.js',
		array(),
		'1.0.0',
		true // load in the footer
	);
}
