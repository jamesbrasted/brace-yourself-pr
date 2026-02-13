<?php
/**
 * Template Name: Roster
 *
 * Displays the list of artists. Use this template for the static page titled "Roster"
 * (typically with slug "roster"). Artists are ordered alphabetically by title.
 *
 * @package Brace_Yourself
 */

$roster_body_class = function ( $classes ) {
	$classes[] = 'roster-page';
	return $classes;
};
add_filter( 'body_class', $roster_body_class );
get_header();
remove_filter( 'body_class', $roster_body_class );

$artist_query = new WP_Query(
	array(
		'post_type'              => 'artist',
		'post_status'             => 'publish',
		'posts_per_page'         => -1,
		'orderby'                => 'title',
		'order'                  => 'ASC',
		'no_found_rows'          => true,
		'update_post_meta_cache' => false,
		'update_post_term_cache' => false,
	)
);
?>

	<main id="primary" class="site-main">

		<?php
		while ( have_posts() ) :
			the_post();
			?>
			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
				<header class="entry-header">
					<?php
					// Keep semantic H1 for SEO and accessibility, but hide it visually.
					the_title( '<h1 class="entry-title sr-only">', '</h1>' );
					?>
				</header><!-- .entry-header -->
				<div class="entry-content centered__content flow text-heading-xl">
					<?php if ( $artist_query->have_posts() ) : ?>
						<ul class="roster" data-module="roster">
							<?php
							while ( $artist_query->have_posts() ) {
								$artist_query->the_post();
								$artist_id  = get_the_ID();
								$subtitle   = get_field( 'subtitle', $artist_id );
								$link       = get_field( 'link', $artist_id );
								$hover_img  = get_field( 'hover_image', $artist_id );
								$has_sub    = is_string( $subtitle ) && trim( $subtitle ) !== '';
								$has_link   = is_string( $link ) && trim( $link ) !== '' && esc_url( $link ) !== '';
								$has_image  = is_array( $hover_img ) && ! empty( $hover_img['ID'] );
								$link_attrs = ( $has_link && strpos( $link, home_url() ) !== 0 ) ? ' rel="noopener noreferrer" target="_blank"' : '';
								?>
								<li class="artist__item">
									<?php if ( $has_link ) : ?>
										<a href="<?php echo esc_url( $link ); ?>" class="artist__name"<?php echo $link_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( get_the_title() ); ?></a>
									<?php else : ?>
										<span class="artist__name"><?php echo esc_html( get_the_title() ); ?></span>
									<?php endif; ?>
									<?php if ( $has_sub ) : ?>
										<span class="artist__subtitle text-caption"><?php echo esc_html( trim( $subtitle ) ); ?></span>
									<?php endif; ?>
									<?php
									if ( $has_image ) {
										echo wp_get_attachment_image(
											(int) $hover_img['ID'],
											'artist-preview',
											false,
											array(
												'class'    => 'artist__preview',
												'loading'  => 'lazy',
												'decoding' => 'async',
												'alt'      => '',
											)
										);
									}
									?>
								</li>
								<?php
							}
							?>
						</ul>
						<?php
						wp_reset_postdata();
					endif;
					?>
				</div>
			</article>
			<?php
		endwhile;
		?>

	</main>

<?php
get_footer();
