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
 * Preload critical assets for faster LCP.
 *
 * Preloads the main stylesheet and primary heading font so the browser
 * discovers them before parsing the rest of the document.
 */
function brace_yourself_preload_critical_assets() {
	// Main CSS (browser will use this when it hits the enqueued stylesheet).
	$css_url = BRACE_YOURSELF_TEMPLATE_URI . '/assets/css/main.css';
	if ( BRACE_YOURSELF_VERSION ) {
		$css_url = add_query_arg( 'ver', BRACE_YOURSELF_VERSION, $css_url );
	}
	echo '<link rel="preload" href="' . esc_url( $css_url ) . '" as="style">' . "\n";

	// Primary heading font (Pilat Bold) – improves LCP text rendering.
	echo '<link rel="preload" href="' . esc_url( BRACE_YOURSELF_TEMPLATE_URI . '/assets/fonts/Pilat-Bold.woff2' ) . '" as="font" type="font/woff2" crossorigin>' . "\n";
}
add_action( 'wp_head', 'brace_yourself_preload_critical_assets', 1 );
