<?php
/**
 * SEO: structured data and social meta
 *
 * Outputs JSON-LD and Open Graph / Twitter Card only when no SEO plugin
 * (Yoast, Rank Math) is active. Zero impact on design.
 *
 * @package Brace_Yourself
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether an SEO plugin is handling meta and schema.
 *
 * @return bool
 */
function brace_yourself_has_seo_plugin() {
	return function_exists( 'wpseo_init' ) || class_exists( 'RankMath', false );
}

/**
 * Output JSON-LD WebSite schema in head.
 */
function brace_yourself_json_ld_website() {
	if ( brace_yourself_has_seo_plugin() ) {
		return;
	}

	$data = array(
		'@context' => 'https://schema.org',
		'@type'    => 'WebSite',
		'name'     => get_bloginfo( 'name' ),
		'url'      => home_url( '/' ),
	);

	$description = get_bloginfo( 'description' );
	if ( $description ) {
		$data['description'] = $description;
	}

	echo '<script type="application/ld+json">' . wp_json_encode( $data ) . "</script>\n";
}
add_action( 'wp_head', 'brace_yourself_json_ld_website', 5 );

/**
 * Output basic Open Graph and Twitter Card meta tags.
 */
function brace_yourself_og_meta() {
	if ( brace_yourself_has_seo_plugin() ) {
		return;
	}

	$title       = wp_get_document_title();
	$description = '';
	$url         = home_url( '/' );
	$image       = '';
	$type        = 'website';

	if ( is_singular() ) {
		$url = get_permalink();
		if ( is_singular( 'post' ) ) {
			$type = 'article';
		}
		$post = get_post();
		$description = has_excerpt() ? get_the_excerpt() : ( $post ? wp_trim_words( $post->post_content, 30 ) : '' );
		$image       = get_the_post_thumbnail_url( null, 'content-large' );
	} elseif ( is_front_page() && is_home() ) {
		$description = get_bloginfo( 'description' );
	} elseif ( is_category() ) {
		$url         = get_category_link( get_queried_object_id() );
		$description = category_description();
	} elseif ( is_tag() ) {
		$url         = get_tag_link( get_queried_object_id() );
		$description = tag_description();
	} elseif ( is_author() ) {
		$url = get_author_posts_url( get_queried_object_id() );
	}

	$description = trim( wp_strip_all_tags( $description ) );
	if ( ! $description ) {
		$description = get_bloginfo( 'description' );
	}

	?>
	<meta property="og:type" content="<?php echo esc_attr( $type ); ?>">
	<meta property="og:url" content="<?php echo esc_url( $url ); ?>">
	<meta property="og:title" content="<?php echo esc_attr( $title ); ?>">
	<meta property="og:description" content="<?php echo esc_attr( $description ); ?>">
	<?php if ( $image ) : ?>
	<meta property="og:image" content="<?php echo esc_url( $image ); ?>">
	<?php endif; ?>
	<meta property="og:site_name" content="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
	<meta name="twitter:card" content="<?php echo $image ? 'summary_large_image' : 'summary'; ?>">
	<meta name="twitter:title" content="<?php echo esc_attr( $title ); ?>">
	<meta name="twitter:description" content="<?php echo esc_attr( $description ); ?>">
	<?php if ( $image ) : ?>
	<meta name="twitter:image" content="<?php echo esc_url( $image ); ?>">
	<?php endif; ?>
	<?php
}
add_action( 'wp_head', 'brace_yourself_og_meta', 3 );
