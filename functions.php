<?php
/**
 * Brace Yourself Theme Functions
 *
 * This file only loads files from /inc/ directory.
 * All theme logic is organized into modular files.
 *
 * @package Brace_Yourself
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define theme constants
define( 'BRACE_YOURSELF_VERSION', '1.0.0' );
define( 'BRACE_YOURSELF_TEMPLATE_DIR', get_template_directory() );
define( 'BRACE_YOURSELF_TEMPLATE_URI', get_template_directory_uri() );

// Load theme setup
require_once BRACE_YOURSELF_TEMPLATE_DIR . '/inc/setup.php';

// Load asset management
require_once BRACE_YOURSELF_TEMPLATE_DIR . '/inc/assets.php';

// Load Artists system (CPT)
require_once BRACE_YOURSELF_TEMPLATE_DIR . '/inc/artists.php';

// Load ACF configuration
require_once BRACE_YOURSELF_TEMPLATE_DIR . '/inc/acf.php';

// Load performance optimizations
require_once BRACE_YOURSELF_TEMPLATE_DIR . '/inc/performance.php';

// Load SEO (JSON-LD, Open Graph) — no output when Yoast/Rank Math active
require_once BRACE_YOURSELF_TEMPLATE_DIR . '/inc/seo.php';

// Load template tags
require_once BRACE_YOURSELF_TEMPLATE_DIR . '/inc/template-tags.php';

// Load carousel functionality
require_once BRACE_YOURSELF_TEMPLATE_DIR . '/inc/carousel.php';
