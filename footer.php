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

	<?php if ( has_nav_menu( 'footer' ) || is_active_sidebar( 'footer-left' ) || is_active_sidebar( 'footer-right' ) ) : ?>
	<footer id="colophon" class="site-footer">
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

		<?php if ( is_active_sidebar( 'footer-left' ) || is_active_sidebar( 'footer-right' ) ) : ?>
			<div class="site-footer__inner">
				<?php if ( is_active_sidebar( 'footer-left' ) ) : ?>
					<div class="site-footer__left">
						<?php dynamic_sidebar( 'footer-left' ); ?>
					</div>
				<?php endif; ?>
				<?php if ( is_active_sidebar( 'footer-right' ) ) : ?>
					<div class="site-footer__right">
						<?php dynamic_sidebar( 'footer-right' ); ?>
					</div>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</footer><!-- #colophon -->
	<?php endif; ?>
</div><!-- #page -->

<?php wp_footer(); ?>

</body>
</html>
