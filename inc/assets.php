<?php
/**
 * Asset Management
 *
 * Handles CSS and JavaScript enqueuing with performance optimizations.
 *
 * @package Brace_Yourself
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueue scripts and styles.
 */
function brace_yourself_scripts() {
	// Main stylesheet
	wp_enqueue_style(
		'brace-yourself-style',
		BRACE_YOURSELF_TEMPLATE_URI . '/assets/css/main.css',
		array(),
		BRACE_YOURSELF_VERSION
	);

	// Main JavaScript (loaded in footer, deferred)
	wp_enqueue_script(
		'brace-yourself-script',
		BRACE_YOURSELF_TEMPLATE_URI . '/assets/js/main.js',
		array(),
		BRACE_YOURSELF_VERSION,
		true
	);

	// Add defer attribute to script
	add_filter( 'script_loader_tag', 'brace_yourself_defer_scripts', 10, 2 );
}
add_action( 'wp_enqueue_scripts', 'brace_yourself_scripts' );

/**
 * Add defer attribute to theme scripts.
 *
 * @param string $tag    The script tag.
 * @param string $handle The script handle.
 * @return string Modified script tag.
 */
function brace_yourself_defer_scripts( $tag, $handle ) {
	$defer_scripts = array( 'brace-yourself-script' );

	if ( in_array( $handle, $defer_scripts, true ) ) {
		return str_replace( ' src', ' defer src', $tag );
	}

	return $tag;
}

/**
 * Preload critical font file.
 *
 * Only preload exactly one font file if using custom fonts.
 * This example assumes a custom font - adjust as needed.
 */
function brace_yourself_preload_fonts() {
	// Uncomment and adjust if using a custom font:
	/*
	echo '<link rel="preload" href="' . esc_url( BRACE_YOURSELF_TEMPLATE_URI . '/assets/fonts/font-name.woff2' ) . '" as="font" type="font/woff2" crossorigin>';
	*/
}
add_action( 'wp_head', 'brace_yourself_preload_fonts', 1 );
