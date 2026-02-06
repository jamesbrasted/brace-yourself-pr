<?php
/**
 * Component: Hero
 *
 * ACF-powered hero section component with heading, subheading, image, and CTA.
 *
 * Fields:
 * - heading (text, required) - Main hero heading
 * - subheading (textarea) - Supporting text below heading
 * - image (image) - Hero background/image
 * - cta_text (text) - Call-to-action button text
 * - cta_link (link) - Call-to-action button link
 *
 * Usage:
 * <?php get_template_part( 'template-parts/components/hero' ); ?>
 *
 * @package Brace_Yourself
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get ACF field values with fallbacks
$heading   = brace_yourself_get_field( 'heading', '' );
$subheading = brace_yourself_get_field( 'subheading', '' );
$image     = brace_yourself_get_field( 'image', false );
$cta_text  = brace_yourself_get_field( 'cta_text', '' );
$cta_link  = brace_yourself_get_field( 'cta_link', false );

// Don't render if no heading (required field)
if ( empty( $heading ) ) {
	return;
}

// Extract image data
$image_url = '';
$image_alt = '';
if ( $image && is_array( $image ) ) {
	$image_url = isset( $image['url'] ) ? esc_url( $image['url'] ) : '';
	$image_alt = isset( $image['alt'] ) ? esc_attr( $image['alt'] ) : '';
} elseif ( $image && is_numeric( $image ) ) {
	$image_array = wp_get_attachment_image_src( $image, 'hero-large' );
	$image_url   = $image_array ? esc_url( $image_array[0] ) : '';
	$image_alt   = get_post_meta( $image, '_wp_attachment_image_alt', true );
	$image_alt   = $image_alt ? esc_attr( $image_alt ) : '';
}

// Extract CTA link data
$cta_url    = '';
$cta_target = '';
$cta_title  = '';
if ( $cta_link && is_array( $cta_link ) ) {
	$cta_url    = isset( $cta_link['url'] ) ? esc_url( $cta_link['url'] ) : '';
	$cta_target = isset( $cta_link['target'] ) ? esc_attr( $cta_link['target'] ) : '';
	$cta_title  = isset( $cta_link['title'] ) ? esc_html( $cta_link['title'] ) : '';
} elseif ( is_string( $cta_link ) ) {
	$cta_url = esc_url( $cta_link );
}
?>

<section class="hero section">
	<?php if ( $image_url ) : ?>
		<div class="hero__image">
			<img 
				src="<?php echo esc_url( $image_url ); ?>" 
				alt="<?php echo esc_attr( $image_alt ); ?>"
				loading="lazy"
			/>
		</div>
	<?php endif; ?>

	<div class="container">
		<div class="hero__content">
			<h1 class="hero__heading"><?php echo esc_html( $heading ); ?></h1>

			<?php if ( $subheading ) : ?>
				<div class="hero__subheading flow">
					<?php echo wp_kses_post( wpautop( $subheading ) ); ?>
				</div>
			<?php endif; ?>

			<?php if ( $cta_url && $cta_text ) : ?>
				<div class="hero__cta">
					<a 
						href="<?php echo esc_url( $cta_url ); ?>"
						class="btn"
						<?php if ( $cta_target ) : ?>
							target="<?php echo esc_attr( $cta_target ); ?>"
						<?php endif; ?>
						<?php if ( $cta_title ) : ?>
							title="<?php echo esc_attr( $cta_title ); ?>"
						<?php endif; ?>
					>
						<?php echo esc_html( $cta_text ); ?>
					</a>
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>
