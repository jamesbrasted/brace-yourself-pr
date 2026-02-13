<?php
/**
 * Artists System
 *
 * Registers the artist custom post type. Artists are managed in WP Admin,
 * displayed only on the Roster page. No public single or archive URLs.
 *
 * @package Brace_Yourself
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the artist custom post type.
 */
function brace_yourself_register_artist_post_type() {
	$labels = array(
		'name'                  => _x( 'Artists', 'Post type general name', 'brace-yourself' ),
		'singular_name'         => _x( 'Artist', 'Post type singular name', 'brace-yourself' ),
		'menu_name'             => _x( 'Artists', 'Admin Menu text', 'brace-yourself' ),
		'name_admin_bar'        => _x( 'Artist', 'Add New on Toolbar', 'brace-yourself' ),
		'add_new'               => __( 'Add New', 'brace-yourself' ),
		'add_new_item'          => __( 'Add New Artist', 'brace-yourself' ),
		'new_item'              => __( 'New Artist', 'brace-yourself' ),
		'edit_item'             => __( 'Edit Artist', 'brace-yourself' ),
		'view_item'             => __( 'View Artist', 'brace-yourself' ),
		'all_items'             => __( 'All Artists', 'brace-yourself' ),
		'search_items'          => __( 'Search Artists', 'brace-yourself' ),
		'parent_item_colon'     => __( 'Parent Artists:', 'brace-yourself' ),
		'not_found'             => __( 'No artists found.', 'brace-yourself' ),
		'not_found_in_trash'    => __( 'No artists found in Trash.', 'brace-yourself' ),
		'item_published'       => __( 'Artist published.', 'brace-yourself' ),
		'item_updated'         => __( 'Artist updated.', 'brace-yourself' ),
		'item_reverted_to_draft'=> __( 'Artist reverted to draft.', 'brace-yourself' ),
		'item_scheduled'        => __( 'Artist scheduled.', 'brace-yourself' ),
		'item_archived'         => __( 'Artist archived.', 'brace-yourself' ),
	);

	$args = array(
		'labels'                => $labels,
		'public'                => true,
		'publicly_queryable'    => false,
		'show_ui'               => true,
		'show_in_menu'          => true,
		'query_var'             => false,
		'rewrite'               => false,
		'has_archive'           => false,
		'hierarchical'          => false,
		'menu_position'         => 20,
		'menu_icon'             => 'dashicons-groups',
		'supports'              => array( 'title' ),
		'show_in_rest'          => true,
		'capability_type'       => 'post',
	);

	register_post_type( 'artist', $args );
}
add_action( 'init', 'brace_yourself_register_artist_post_type' );

/**
 * Force 404 for any public request to artist archive or single.
 * Ensures /artists/ and /artists/artist-name/ (or any artist URL) do not resolve.
 */
function brace_yourself_artist_force_404() {
	if ( is_post_type_archive( 'artist' ) || is_singular( 'artist' ) ) {
		global $wp_query;
		$wp_query->set_404();
		status_header( 404 );
		nocache_headers();
	}
}
add_action( 'template_redirect', 'brace_yourself_artist_force_404' );

/**
 * Remove "View" row action from the Artists list table.
 * Artists have no public URL; they are only visible on the Roster page.
 *
 * @param array   $actions Row actions.
 * @param WP_Post $post    Post object.
 * @return array Filtered actions.
 */
function brace_yourself_artist_remove_view_row_action( $actions, $post ) {
	if ( $post->post_type === 'artist' && isset( $actions['view'] ) ) {
		unset( $actions['view'] );
	}
	return $actions;
}
add_filter( 'post_row_actions', 'brace_yourself_artist_remove_view_row_action', 10, 2 );

/**
 * Remove "View" from the admin bar when editing an artist.
 *
 * @param WP_Admin_Bar $wp_admin_bar Admin bar instance.
 */
function brace_yourself_artist_remove_view_from_admin_bar( $wp_admin_bar ) {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || $screen->post_type !== 'artist' ) {
		return;
	}
	$wp_admin_bar->remove_node( 'view' );
}
add_action( 'admin_bar_menu', 'brace_yourself_artist_remove_view_from_admin_bar', 999 );

/**
 * Minimal artist edit screen (same approach as Carousel Settings).
 * Only Title + Artist Details (ACF) are shown; no excerpt, slug, revisions, etc.
 */

/**
 * Disable block editor for artists; use classic title-only edit screen.
 *
 * @param bool   $use_block_editor Whether to use the block editor.
 * @param object $post             The post object.
 * @return bool
 */
function brace_yourself_disable_block_editor_for_artist( $use_block_editor, $post ) {
	if ( ! $post || $post->post_type !== 'artist' ) {
		return $use_block_editor;
	}
	return false;
}
add_filter( 'use_block_editor_for_post', 'brace_yourself_disable_block_editor_for_artist', 10, 2 );

/**
 * Hide unneeded meta boxes on the artist edit screen.
 *
 * @param array  $hidden  Array of hidden meta box IDs.
 * @param object $screen Current screen object.
 * @return array Filtered hidden meta boxes.
 */
function brace_yourself_artist_hide_meta_boxes( $hidden, $screen ) {
	if ( ! $screen || $screen->post_type !== 'artist' ) {
		return $hidden;
	}
	$hidden[] = 'postcustom';
	$hidden[] = 'commentstatusdiv';
	$hidden[] = 'commentsdiv';
	$hidden[] = 'slugdiv';
	$hidden[] = 'authordiv';
	$hidden[] = 'revisionsdiv';
	return $hidden;
}
add_filter( 'hidden_meta_boxes', 'brace_yourself_artist_hide_meta_boxes', 10, 2 );

/**
 * Admin styles for artist edit screen: hide View link and any remaining clutter.
 */
function brace_yourself_artist_admin_styles() {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || $screen->post_type !== 'artist' ) {
		return;
	}
	$home = esc_url( home_url( '/' ) );
	?>
	<style>
		/* View link (no public URL) */
		body.post-type-artist #submitdiv .misc-pub-section a[href^="<?php echo esc_attr( $home ); ?>"] {
			display: none;
		}
		/* Permalink/slug row under title */
		body.post-type-artist #edit-slug-box,
		body.post-type-artist .permalink-display {
			display: none !important;
		}
		/* Revisions, discussion, etc. */
		body.post-type-artist #revisionsdiv,
		body.post-type-artist #commentstatusdiv,
		body.post-type-artist #commentsdiv {
			display: none !important;
		}
	</style>
	<?php
}
add_action( 'admin_head-post.php', 'brace_yourself_artist_admin_styles' );
add_action( 'admin_head-post-new.php', 'brace_yourself_artist_admin_styles' );
