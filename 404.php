<?php
/**
 * The template for displaying 404 pages (not found)
 *
 * @package Brace_Yourself
 */

get_header();
?>

	<main id="primary" class="site-main site-main--centered">

		<section class="error-404 not-found centered">
			<div class="centered__inner">
				<div class="centered__content flow">
					<header class="page-header">
						<h1 class="page-title text-heading-md"><?php esc_html_e( 'Oops! That page can&rsquo;t be found.', 'brace-yourself' ); ?></h1>
					</header><!-- .page-header -->
					<p><?php esc_html_e( 'It looks like nothing was found at this location.', 'brace-yourself' ); ?></p>
				</div><!-- .centered__content -->
			</div><!-- .centered__inner -->
		</section><!-- .error-404 -->

	</main><!-- #main -->

<?php
get_footer();
