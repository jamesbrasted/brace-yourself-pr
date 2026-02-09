<?php
/**
 * The sidebar containing the main widget area
 *
 * @package Brace_Yourself
 */

// Don't show sidebar on front page
if ( is_front_page() ) {
	return;
}

if ( ! is_active_sidebar( 'sidebar-1' ) ) {
	return;
}
?>

<aside id="secondary" class="widget-area">
	<?php dynamic_sidebar( 'sidebar-1' ); ?>
</aside><!-- #secondary -->
