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
 * Output meta description when no SEO plugin is active.
 *
 * Improves SEO and social sharing. Yoast and Rank Math output their own;
 * this runs only as a fallback so it does not duplicate or override them.
 */
function brace_yourself_meta_description() {
	if ( function_exists( 'wpseo_init' ) || class_exists( 'RankMath', false ) ) {
		return;
	}

	$description = '';
	if ( is_front_page() && is_home() ) {
		$description = get_bloginfo( 'description' );
	} elseif ( is_singular() ) {
		$description = get_the_excerpt();
		if ( ! $description && is_single() ) {
			$description = wp_trim_words( get_the_content(), 30 );
		}
	} elseif ( is_category() ) {
		$description = category_description();
	} elseif ( is_tag() ) {
		$description = tag_description();
	}

	$description = trim( $description );
	if ( $description ) {
		echo '<meta name="description" content="' . esc_attr( wp_strip_all_tags( $description ) ) . '">' . "\n";
	}
}
add_action( 'wp_head', 'brace_yourself_meta_description', 2 );

/**
 * Theme color for mobile browser UI (address bar, etc.).
 *
 * Matches theme background (#121212) for a cohesive experience.
 */
function brace_yourself_theme_color() {
	echo '<meta name="theme-color" content="#121212">' . "\n";
}
add_action( 'wp_head', 'brace_yourself_theme_color', 2 );

// No traditional widget areas are used; footer content is managed via ACF.

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

	// Artist hover preview (Roster page)
	add_image_size( 'artist-preview', 600, 600, false );
}
add_action( 'after_setup_theme', 'brace_yourself_image_sizes' );

/**
 * Limit primary navigation menu to maximum of 3 items.
 * This ensures the menu stays clean and manageable for 2-3 items.
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
			// Limit to first 3 items
			return array_slice( $items, 0, 3 );
		}
	}
	
	return $items;
}
add_filter( 'wp_get_nav_menu_items', 'brace_yourself_limit_primary_menu_items', 10, 2 );

/**
// No footer widget limiting needed; footer content is managed via ACF.

/**
 * Get the Roster page ID (the page using the Roster template).
 * Content is built from the Artist post type; the page itself needs no editor.
 *
 * @return int|false Page ID or false if no page uses the Roster template.
 */
function brace_yourself_get_roster_page_id() {
	$pages = get_pages(
		array(
			'meta_key'   => '_wp_page_template',
			'meta_value' => 'page-roster.php',
			'number'     => 1,
		)
	);
	return ! empty( $pages ) ? (int) $pages[0]->ID : false;
}

/**
 * Get the list of page IDs that do not use the content editor.
 * (ACF-only pages plus template-only pages like Roster.)
 *
 * @return int[] Array of page IDs.
 */
function brace_yourself_get_acf_only_page_ids() {
	$ids = array();

	$front_page_id = get_option( 'page_on_front' );
	if ( $front_page_id ) {
		$ids[] = (int) $front_page_id;
	}
	if ( function_exists( 'brace_yourself_get_carousel_settings_page_id' ) ) {
		$carousel_page_id = brace_yourself_get_carousel_settings_page_id();
		if ( $carousel_page_id ) {
			$ids[] = (int) $carousel_page_id;
		}
	}
	if ( function_exists( 'brace_yourself_get_footer_settings_page_id' ) ) {
		$footer_page_id = brace_yourself_get_footer_settings_page_id();
		if ( $footer_page_id ) {
			$ids[] = (int) $footer_page_id;
		}
	}
	if ( function_exists( 'brace_yourself_get_about_page_id' ) ) {
		$about_page_id = brace_yourself_get_about_page_id();
		if ( $about_page_id ) {
			$ids[] = (int) $about_page_id;
		}
	}
	$roster_page_id = brace_yourself_get_roster_page_id();
	if ( $roster_page_id ) {
		$ids[] = (int) $roster_page_id;
	}

	return $ids;
}

function brace_yourself_disable_block_editor_for_homepage( $use_block_editor, $post ) {
	if ( ! $post || 'page' !== $post->post_type ) {
		return $use_block_editor;
	}

	$ids = brace_yourself_get_acf_only_page_ids();
	if ( ! empty( $ids ) && in_array( (int) $post->ID, $ids, true ) ) {
		return false;
	}

	return $use_block_editor;
}
add_filter( 'use_block_editor_for_post', 'brace_yourself_disable_block_editor_for_homepage', 10, 2 );

/**
 * Check if we're currently editing an ACF-managed page (no content editor).
 *
 * Currently includes:
 * - Static front page (homepage)
 * - Carousel Settings page (ACF Free compatibility)
 * - Footer Settings page
 * - About page (page with slug "about")
 * - Roster page (page using the Roster template; content is from Artist CPT)
 *
 * @return bool True if editing an ACF-only page, false otherwise.
 */
function brace_yourself_is_acf_only_page() {
	$ids = brace_yourself_get_acf_only_page_ids();

	if ( empty( $ids ) ) {
		return false;
	}

	global $post;

	// Try multiple methods to get the current post ID.
	$post_id = 0;

	// GET parameters (most reliable in admin context).
	if ( isset( $_GET['post'] ) ) {
		$post_id = intval( $_GET['post'] );
	} elseif ( isset( $_POST['post_ID'] ) ) {
		// POST parameters (during save).
		$post_id = intval( $_POST['post_ID'] );
	} elseif ( isset( $post->ID ) && $post->ID ) {
		// Global post object.
		$post_id = $post->ID;
	} else {
		// Screen object fallback.
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( $screen ) {
			if ( isset( $screen->post ) && isset( $screen->post->ID ) ) {
				$post_id = $screen->post->ID;
			} elseif ( 'page' === $screen->post_type && 'post' === $screen->base && isset( $_GET['post'] ) ) {
				$post_id = intval( $_GET['post'] );
			}
		}
	}

	return ( $post_id > 0 && in_array( (int) $post_id, $ids, true ) );
}

/**
 * Hide editor and other meta boxes for ACF-only pages.
 * Content is managed entirely via ACF fields.
 *
 * @param array $hidden Array of hidden meta boxes.
 * @param object $screen Current screen object.
 * @return array Filtered array of hidden meta boxes.
 */
function brace_yourself_hide_editor_for_homepage( $hidden, $screen ) {
	if ( 'page' !== $screen->post_type ) {
		return $hidden;
	}

	if ( ! brace_yourself_is_acf_only_page() ) {
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
 * Remove editor support for ACF-only pages to completely hide the content editor.
 */
function brace_yourself_remove_editor_for_homepage() {
	if ( ! brace_yourself_is_acf_only_page() ) {
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
	if ( ! brace_yourself_is_acf_only_page() ) {
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

