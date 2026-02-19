<?php
/**
 * The front page template
 *
 * Used when the site has a static front page. Homepage content is built from
 * the background carousel and homepage intro (loaded in the header); the page
 * itself has no editor, so we do not output an empty article.
 *
 * @package Brace_Yourself
 */

get_header();
?>

	<main id="primary" class="site-main">
		<?php
		while ( have_posts() ) :
			the_post();
			// Semantic H1 for SEO/accessibility only; no article wrapper (page has no content).
			the_title( '<h1 class="sr-only">', '</h1>' );
		endwhile;
		?>
	</main><!-- #primary -->

<?php
get_footer();
