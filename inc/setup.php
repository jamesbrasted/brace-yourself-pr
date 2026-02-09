<?php
/**
 * Theme Setup
 *
 * Handles theme support, navigation menus, image sizes, and other core setup.
 *
 * @package Brace_Yourself
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sets up theme defaults and registers support for various WordPress features.
 */
function brace_yourself_setup() {
	// Make theme available for translation
	load_theme_textdomain( 'brace-yourself', BRACE_YOURSELF_TEMPLATE_DIR . '/languages' );

	// Add default posts and comments RSS feed links to head
	add_theme_support( 'automatic-feed-links' );

	// Let WordPress manage the document title
	add_theme_support( 'title-tag' );

	// Enable support for Post Thumbnails
	add_theme_support( 'post-thumbnails' );

	// Register navigation menus
	register_nav_menus(
		array(
			'primary' => esc_html__( 'Primary Menu', 'brace-yourself' ),
			'footer'  => esc_html__( 'Footer Menu', 'brace-yourself' ),
		)
	);

	// Switch default core markup to output valid HTML5
	add_theme_support(
		'html5',
		array(
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
			'style',
			'script',
		)
	);

	// Add theme support for selective refresh for widgets
	add_theme_support( 'customize-selective-refresh-widgets' );

	// Add support for editor styles
	add_theme_support( 'editor-styles' );
	add_editor_style( 'assets/css/editor-style.css' );

	// Add support for responsive embedded content
	add_theme_support( 'responsive-embeds' );

	// Add support for custom logo
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 250,
			'width'       => 250,
			'flex-width'  => true,
			'flex-height' => true,
		)
	);
}
add_action( 'after_setup_theme', 'brace_yourself_setup' );

/**
 * Set the content width in pixels, based on the theme's design and stylesheet.
 *
 * Priority 0 to make it available to lower priority callbacks.
 *
 * @global int $content_width
 */
function brace_yourself_content_width() {
	$GLOBALS['content_width'] = apply_filters( 'brace_yourself_content_width', 1200 );
}
add_action( 'after_setup_theme', 'brace_yourself_content_width', 0 );

/**
 * Register widget areas.
 */
function brace_yourself_widgets_init() {
	register_sidebar(
		array(
			'name'          => esc_html__( 'Footer Left', 'brace-yourself' ),
			'id'            => 'footer-left',
			'description'   => esc_html__( 'Appears on the left side of the footer (e.g. address).', 'brace-yourself' ),
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		)
	);

	register_sidebar(
		array(
			'name'          => esc_html__( 'Footer Right', 'brace-yourself' ),
			'id'            => 'footer-right',
			'description'   => esc_html__( 'Appears on the right side of the footer (e.g. email, social links).', 'brace-yourself' ),
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		)
	);
}
add_action( 'widgets_init', 'brace_yourself_widgets_init' );

/**
 * Add custom image sizes.
 *
 * These sizes are intentionally defined for performance.
 * Never output full-size images - use these sizes with srcset.
 */
function brace_yourself_image_sizes() {
	// Hero images
	add_image_size( 'hero-large', 1920, 1080, true );
	add_image_size( 'hero-medium', 1280, 720, true );
	add_image_size( 'hero-small', 768, 432, true );

	// Content images
	add_image_size( 'content-large', 1200, 800, false );
	add_image_size( 'content-medium', 800, 600, false );
	add_image_size( 'content-small', 400, 300, false );

	// Thumbnails
	add_image_size( 'thumbnail-large', 400, 400, true );
	add_image_size( 'thumbnail-medium', 300, 300, true );
	add_image_size( 'thumbnail-small', 150, 150, true );
}
add_action( 'after_setup_theme', 'brace_yourself_image_sizes' );

/**
 * Limit primary navigation menu to maximum of 4 items.
 * This ensures the menu stays clean and manageable for 2-4 items.
 *
 * @param array $items An array of menu item objects.
 * @param object $menu The menu object.
 * @return array Filtered array of menu items.
 */
function brace_yourself_limit_primary_menu_items( $items, $menu ) {
	// Only limit the primary menu location
	if ( isset( $menu->term_id ) ) {
		$locations = get_nav_menu_locations();
		if ( isset( $locations['primary'] ) && $locations['primary'] === $menu->term_id ) {
			// Limit to first 4 items
			return array_slice( $items, 0, 4 );
		}
	}
	
	return $items;
}
add_filter( 'wp_get_nav_menu_items', 'brace_yourself_limit_primary_menu_items', 10, 2 );

/**
 * Limit footer widget areas to a maximum of 1 widget each.
 * Prevents saving more than 1 widget per footer area in the admin.
 *
 * @param array $value The sidebars_widgets option value being saved.
 * @return array Filtered value with footer-left and footer-right limited to 1 item each.
 */
function brace_yourself_limit_footer_widgets_on_save( $value ) {
	if ( ! is_array( $value ) ) {
		return $value;
	}

	// Limit footer-left to 1 widget
	if ( ! empty( $value['footer-left'] ) && is_array( $value['footer-left'] ) ) {
		$value['footer-left'] = array_slice( $value['footer-left'], 0, 1 );
	}

	// Limit footer-right to 1 widget
	if ( ! empty( $value['footer-right'] ) && is_array( $value['footer-right'] ) ) {
		$value['footer-right'] = array_slice( $value['footer-right'], 0, 1 );
	}

	return $value;
}
add_filter( 'pre_update_option_sidebars_widgets', 'brace_yourself_limit_footer_widgets_on_save' );

/**
 * Disable block editor (Gutenberg) for the homepage.
 * Homepage content is managed via ACF fields, so blocks are not needed.
 *
 * @param bool   $use_block_editor Whether to use the block editor.
 * @param object $post The post object.
 * @return bool Filtered value.
 */
function brace_yourself_disable_block_editor_for_homepage( $use_block_editor, $post ) {
	if ( ! $post ) {
		return $use_block_editor;
	}

	// Disable block editor if this post/page is set as the front page
	$front_page_id = get_option( 'page_on_front' );
	if ( $front_page_id && $post->ID == $front_page_id ) {
		return false;
	}

	return $use_block_editor;
}
add_filter( 'use_block_editor_for_post', 'brace_yourself_disable_block_editor_for_homepage', 10, 2 );

/**
 * Check if we're currently editing the homepage.
 *
 * @return bool True if editing homepage, false otherwise.
 */
function brace_yourself_is_editing_homepage() {
	$front_page_id = get_option( 'page_on_front' );
	if ( ! $front_page_id ) {
		return false;
	}

	global $post;
	
	// Try multiple methods to get the post ID
	$post_id = 0;
	
	// Check GET parameters first (most reliable in admin context)
	if ( isset( $_GET['post'] ) ) {
		$post_id = intval( $_GET['post'] );
	}
	// Check POST parameters (during save)
	elseif ( isset( $_POST['post_ID'] ) ) {
		$post_id = intval( $_POST['post_ID'] );
	}
	// Check global post object
	elseif ( isset( $post->ID ) && $post->ID ) {
		$post_id = $post->ID;
	}
	// Check screen object
	else {
		$screen = get_current_screen();
		if ( $screen ) {
			// Try to get post ID from screen
			if ( isset( $screen->post ) && isset( $screen->post->ID ) ) {
				$post_id = $screen->post->ID;
			}
			// Or check if we're on the edit page for this post type
			elseif ( 'page' === $screen->post_type && 'post' === $screen->base ) {
				// We're on a page edit screen, try to get ID from query vars
				$post_id = isset( $_GET['post'] ) ? intval( $_GET['post'] ) : 0;
			}
		}
	}
	
	return ( $post_id > 0 && $post_id == $front_page_id );
}

/**
 * Hide editor and other meta boxes for the homepage.
 * Homepage content is managed entirely via ACF fields.
 *
 * @param array $hidden Array of hidden meta boxes.
 * @param object $screen Current screen object.
 * @return array Filtered array of hidden meta boxes.
 */
function brace_yourself_hide_editor_for_homepage( $hidden, $screen ) {
	if ( 'page' !== $screen->post_type ) {
		return $hidden;
	}

	if ( ! brace_yourself_is_editing_homepage() ) {
		return $hidden;
	}

	// Hide the content editor
	$hidden[] = 'postcustom';
	$hidden[] = 'commentstatusdiv';
	$hidden[] = 'commentsdiv';
	$hidden[] = 'slugdiv';
	$hidden[] = 'authordiv';
	$hidden[] = 'revisionsdiv';
	
	return $hidden;
}
add_filter( 'hidden_meta_boxes', 'brace_yourself_hide_editor_for_homepage', 10, 2 );

/**
 * Remove editor support for homepage to completely hide the content editor.
 */
function brace_yourself_remove_editor_for_homepage() {
	if ( ! brace_yourself_is_editing_homepage() ) {
		return;
	}

	remove_post_type_support( 'page', 'editor' );
	remove_post_type_support( 'page', 'thumbnail' );
	remove_post_type_support( 'page', 'excerpt' );
	remove_post_type_support( 'page', 'trackbacks' );
	remove_post_type_support( 'page', 'custom-fields' );
	remove_post_type_support( 'page', 'comments' );
	remove_post_type_support( 'page', 'revisions' );
	remove_post_type_support( 'page', 'author' );
	remove_post_type_support( 'page', 'page-attributes' );
}
add_action( 'admin_init', 'brace_yourself_remove_editor_for_homepage' );

/**
 * Enqueue admin styles to hide editor elements on homepage.
 */
function brace_yourself_hide_homepage_editor_styles() {
	if ( ! brace_yourself_is_editing_homepage() ) {
		return;
	}

	$screen = get_current_screen();
	if ( ! $screen || 'page' !== $screen->post_type ) {
		return;
	}
		?>
		<style>
			/* Hide editor and related meta boxes */
			#postdivrich,
			#post-body-content,
			#wp-content-editor-container,
			#wp-content-editor-tools,
			.editor-post-text-editor,
			.block-editor-writing-flow,
			.edit-post-visual-editor,
			.components-panel,
			.editor-post-title,
			#post-status-info,
			#minor-publishing-actions,
			#misc-publishing-actions,
			#post-preview,
			.wp-block-editor,
			.block-editor-block-list__layout {
				display: none !important;
			}
			
			/* Hide page attributes, featured image, etc. */
			#pageparentdiv,
			#postimagediv,
			#postexcerpt,
			#trackbacksdiv,
			#commentstatusdiv,
			#commentsdiv,
			#slugdiv,
			#authordiv,
			#revisionsdiv {
				display: none !important;
			}
		</style>
		<?php
}
add_action( 'admin_head', 'brace_yourself_hide_homepage_editor_styles' );

