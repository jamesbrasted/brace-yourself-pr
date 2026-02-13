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

	<main id="primary" class="site-main site-main--centered">

		<?php
		while ( have_posts() ) :
			the_post();
			?>

			<article id="post-<?php the_ID(); ?>" <?php post_class( 'centered' ); ?>>
				<header class="entry-header">
					<?php
					// Keep semantic H1 for SEO and accessibility, but hide it visually.
					the_title( '<h1 class="entry-title sr-only">', '</h1>' );
					?>
				</header><!-- .entry-header -->
				<div class="centered__inner">
					<div class="centered__content flow text-heading-xl">
						<?php
						the_content();

						wp_link_pages(
							array(
								'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'brace-yourself' ),
								'after'  => '</div>',
							)
						);
						?>
					</div><!-- .centered__content -->
				</div><!-- .centered__inner -->
			</article>

			<?php
		endwhile; // End of the loop.
		?>

	</main><!-- #main -->

<?php
get_footer();
