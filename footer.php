<?php
/**
 * The footer template file
 *
 * @package Brace_Yourself
 */

// Don't render footer content on front page or About page
if ( is_front_page() || is_page( 'about' ) ) {
	?>
</div><!-- #page -->

<?php wp_footer(); ?>

</body>
</html>
	<?php
	return;
}
?>

	<?php if ( has_nav_menu( 'footer' ) ) : ?>
	<footer id="colophon" class="site-footer">
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
		<div class="site-info">
			<p>
				&copy; <?php echo esc_html( date( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?>.
				<?php esc_html_e( 'All rights reserved.', 'brace-yourself' ); ?>
			</p>
		</div>
	</footer><!-- #colophon -->
	<?php endif; ?>
</div><!-- #page -->

<?php wp_footer(); ?>

</body>
</html>
