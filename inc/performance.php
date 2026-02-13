<?php
/**
 * Performance Optimizations
 *
 * Non-negotiable performance improvements for production.
 *
 * @package Brace_Yourself
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Disable emojis.
 */
function brace_yourself_disable_emojis() {
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );
	remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
	remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
	remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );

	// Remove emoji DNS prefetch
	add_filter(
		'emoji_svg_url',
		function() {
			return false;
		}
	);
}
add_action( 'init', 'brace_yourself_disable_emojis' );

/**
 * Disable oEmbed.
 */
function brace_yourself_disable_embeds() {
	// Remove oEmbed discovery links
	remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );

	// Remove oEmbed-specific JavaScript from the front-end and back-end
	remove_action( 'wp_head', 'wp_oembed_add_host_js' );

	// Remove all embeds rewrite rules
	add_filter( 'rewrite_rules_array', 'brace_yourself_disable_embeds_rewrites' );

	// Remove filter of the oEmbed result before any HTTP request is made
	remove_filter( 'oembed_dataparse', 'wp_filter_oembed_result', 10 );

	// Remove oEmbed discovery links
	remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );

	// Remove oEmbed-specific JavaScript from the front-end and back-end
	remove_action( 'wp_head', 'wp_oembed_add_host_js' );
}
add_action( 'init', 'brace_yourself_disable_embeds', 9999 );

/**
 * Remove oEmbed rewrite rules.
 *
 * @param array $rules Rewrite rules.
 * @return array Modified rewrite rules.
 */
function brace_yourself_disable_embeds_rewrites( $rules ) {
	foreach ( $rules as $rule => $rewrite ) {
		if ( false !== strpos( $rewrite, 'embed=true' ) ) {
			unset( $rules[ $rule ] );
		}
	}
	return $rules;
}

/**
 * Remove block editor CSS if not using block editor.
 */
function brace_yourself_remove_block_editor_css() {
	// Remove block editor stylesheets
	wp_dequeue_style( 'wp-block-library' );
	wp_dequeue_style( 'wp-block-library-theme' );
	wp_dequeue_style( 'wc-block-style' ); // WooCommerce block styles

	// Remove classic themes stylesheet
	wp_dequeue_style( 'classic-theme-styles' );
}
add_action( 'wp_enqueue_scripts', 'brace_yourself_remove_block_editor_css', 100 );

/**
 * Remove wp_global_styles.
 */
function brace_yourself_remove_global_styles() {
	remove_action( 'wp_enqueue_scripts', 'wp_enqueue_global_styles' );
	remove_action( 'wp_footer', 'wp_enqueue_global_styles', 1 );
}
add_action( 'init', 'brace_yourself_remove_global_styles' );

/**
 * Lazy load images by default.
 *
 * WordPress 5.5+ has native lazy loading, but we ensure it's enabled.
 */
function brace_yourself_lazy_load_images( $attr, $attachment, $size ) {
	// Ensure loading="lazy" is set
	if ( ! isset( $attr['loading'] ) ) {
		$attr['loading'] = 'lazy';
	}

	return $attr;
}
add_filter( 'wp_get_attachment_image_attributes', 'brace_yourself_lazy_load_images', 10, 3 );

/**
 * Add responsive image sizes attribute.
 *
 * Ensures proper srcset and sizes attributes for performance.
 */
function brace_yourself_responsive_image_sizes( $attr, $attachment, $size ) {
	// Ensure sizes attribute is set for better srcset selection
	if ( ! isset( $attr['sizes'] ) && wp_attachment_is_image( $attachment->ID ) ) {
		$attr['sizes'] = '(max-width: 768px) 100vw, (max-width: 1200px) 80vw, 1200px';
	}

	return $attr;
}
add_filter( 'wp_get_attachment_image_attributes', 'brace_yourself_responsive_image_sizes', 10, 3 );

/**
 * Do not remove query strings from theme assets.
 * Keeping ?ver= on script/style URLs ensures cache busting when BRACE_YOURSELF_VERSION
 * is bumped, so browsers fetch new CSS/JS after deploy. Removed previous filter that
 * stripped ver= and could cause stale assets.
 */
