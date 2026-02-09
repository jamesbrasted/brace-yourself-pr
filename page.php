<?php
/**
 * The template for displaying all pages
 *
 * This is the template that displays all pages by default.
 * Use this template for pages that use ACF flexible content or custom fields.
 *
 * @package Brace_Yourself
 */

get_header();
?>

	<main id="primary" class="site-main">

		<?php
		while ( have_posts() ) :
			the_post();

			get_template_part( 'template-parts/content', 'page' );

		endwhile; // End of the loop.
		?>

	</main><!-- #main -->

<?php
// Don't show sidebar on front page
if ( ! is_front_page() ) {
	get_sidebar();
}
get_footer();
