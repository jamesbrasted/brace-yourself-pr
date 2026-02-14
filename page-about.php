<?php
/**
 * Template Name: About
 *
 * The template for displaying the About page.
 * Displays a centered line of copy from the About page ACF field, aligned to the
 * vertical and horizontal center of the viewport for both desktop and mobile.
 * Content is managed via ACF (one text field); the block editor is hidden.
 *
 * ACF Fields (About Page):
 * - about_intro_text (text) - The intro copy to display
 *
 * @package Brace_Yourself
 */

$about_body_class = function ( $classes ) {
	$classes[] = 'about-page';
	return $classes;
};
add_filter( 'body_class', $about_body_class );
get_header();
remove_filter( 'body_class', $about_body_class );
?>

	<main id="primary" class="site-main site-main--centered">

		<?php
		while ( have_posts() ) :
			the_post();

			$intro_text = brace_yourself_get_field( 'about_intro_text', '', get_the_ID() );
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
						<?php if ( ! empty( $intro_text ) ) : ?>
							<p class="about-intro__text"><?php echo esc_html( $intro_text ); ?></p>
						<?php endif; ?>
					</div><!-- .centered__content -->
				</div><!-- .centered__inner -->
			</article>

			<?php
		endwhile; // End of the loop.
		?>

	</main><!-- #main -->

<?php
get_footer();
