<?php
/**
 * The header template file
 *
 * @package Brace_Yourself
 */

?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">

	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<?php
// Load background carousel component
get_template_part( 'template-parts/components/background-carousel' );
?>

<div id="page" class="site">
	<a class="skip-link" href="#primary"><?php esc_html_e( 'Skip to content', 'brace-yourself' ); ?></a>

	<header id="masthead" class="site-header">
		<div class="site-branding">
				<?php
				the_custom_logo();
				if ( is_front_page() && is_home() ) :
					?>
					<h1 class="site-title">
						<a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home" class="site-title__link">
							<?php brace_yourself_site_title_svg(); ?>
						</a>
					</h1>
					<?php
				else :
					?>
					<p class="site-title">
						<a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home" class="site-title__link">
							<?php brace_yourself_site_title_svg(); ?>
						</a>
					</p>
					<?php
				endif;
			$brace_yourself_description = get_bloginfo( 'description', 'display' );
			if ( $brace_yourself_description || is_customize_preview() ) :
				?>
				<p class="site-description"><?php echo $brace_yourself_description; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
			<?php endif; ?>
		</div><!-- .site-branding -->

		<nav id="site-navigation" class="main-navigation">
			<div class="container">
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'primary',
						'menu_id'        => 'primary-menu',
						'menu_class'     => 'primary-menu',
						'container'      => false,
					)
				);
				?>
			</div><!-- .container -->
		</nav><!-- #site-navigation -->
	</header><!-- #masthead -->
