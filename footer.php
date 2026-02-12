<?php
/**
 * The footer template file
 *
 * @package Brace_Yourself
 */

// Don't render footer content on front page
if ( is_front_page() ) {
	?>
</div><!-- #page -->

<?php wp_footer(); ?>

</body>
</html>
	<?php
	return;
}
?>

	<?php
	// Determine if footer has any ACF-driven columns.
	$footer_page_id = function_exists( 'brace_yourself_get_footer_settings_page_id' ) ? brace_yourself_get_footer_settings_page_id() : 0;
	$footer_columns = array();

	if ( $footer_page_id ) {
		for ( $i = 1; $i <= 4; $i++ ) {
			$content = get_field( 'footer_column_' . $i, $footer_page_id );
			$footer_columns[ $i ] = $content;
		}
	}

	$has_footer_columns = false;
	foreach ( $footer_columns as $content ) {
		if ( $content ) {
			$has_footer_columns = true;
			break;
		}
	}
	?>

	<?php if ( has_nav_menu( 'footer' ) || $has_footer_columns ) : ?>
	<footer id="colophon" class="site-footer text-caption">
		<?php if ( has_nav_menu( 'footer' ) ) : ?>
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'footer',
					'menu_id'        => 'footer-menu',
					'menu_class'     => 'footer-menu',
					'container'      => false,
					'depth'          => 1,
				)
			);
			?>
		<?php endif; ?>

		<?php if ( $has_footer_columns ) : ?>
			<div class="site-footer__inner">
				<?php foreach ( $footer_columns as $i => $column_content ) : ?>
					<?php if ( $column_content ) : ?>
						<div class="site-footer__column site-footer__column--<?php echo esc_attr( $i ); ?>">
							<?php echo wp_kses_post( $column_content ); ?>
						</div>
					<?php endif; ?>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</footer><!-- #colophon -->
	<?php endif; ?>
</div><!-- #page -->

<?php wp_footer(); ?>

</body>
</html>
