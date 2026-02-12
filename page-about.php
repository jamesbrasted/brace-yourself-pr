<?php
/**
 * The template for displaying the About page
 *
 * Displays a centered paragraph of text aligned to the vertical and horizontal
 * center of the viewport for both desktop and mobile.
 *
 * @package Brace_Yourself
 */

get_header();
?>

	<main id="primary" class="site-main site-main--about-centered">

		<?php
		while ( have_posts() ) :
			the_post();
			?>

			<article id="post-<?php the_ID(); ?>" <?php post_class( 'about-centered' ); ?>>
				<div class="about-centered__inner">
					<div class="about-centered__content flow text-heading-lg">
						<?php
						the_content();

						wp_link_pages(
							array(
								'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'brace-yourself' ),
								'after'  => '</div>',
							)
						);
						?>
					</div>
				</div>
			</article>

			<?php
		endwhile; // End of the loop.
		?>

	</main><!-- #main -->

<?php
get_footer();
