<?php
/**
 * Component: Homepage Intro
 *
 * Displays a line of copy aligned to the bottom of the viewport on the homepage.
 * Content is managed via ACF field on the Homepage page.
 *
 * ACF Fields (Homepage Page):
 * - homepage_intro_text (text) - The intro copy to display
 *
 * @package Brace_Yourself
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Only show on homepage
if ( ! is_front_page() ) {
	return;
}

// Get intro text from the Homepage page
$front_page_id = brace_yourself_get_front_page_id();
$intro_text = '';

if ( $front_page_id ) {
	$intro_text = brace_yourself_get_field( 'homepage_intro_text', '', $front_page_id );
}

// Don't render if no text
if ( empty( $intro_text ) ) {
	return;
}
?>

<div class="homepage-intro">
	<div class="homepage-intro__container">
		<p class="homepage-intro__text"><?php echo esc_html( $intro_text ); ?></p>
	</div>
</div>
