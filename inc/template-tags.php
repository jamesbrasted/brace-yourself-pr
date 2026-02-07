<?php
/**
 * Custom template tags for this theme
 *
 * @package Brace_Yourself
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Prints HTML with meta information for the current post-date/time.
 */
function brace_yourself_posted_on() {
	$time_string = '<time class="entry-date published updated" datetime="%1$s">%2$s</time>';
	if ( get_the_time( 'U' ) !== get_the_modified_time( 'U' ) ) {
		$time_string = '<time class="entry-date published" datetime="%1$s">%2$s</time><time class="updated" datetime="%3$s">%4$s</time>';
	}

	$time_string = sprintf(
		$time_string,
		esc_attr( get_the_date( DATE_W3C ) ),
		esc_html( get_the_date() ),
		esc_attr( get_the_modified_date( DATE_W3C ) ),
		esc_html( get_the_modified_date() )
	);

	$posted_on = sprintf(
		/* translators: %s: post date. */
		esc_html_x( 'Posted on %s', 'post date', 'brace-yourself' ),
		'<a href="' . esc_url( get_permalink() ) . '" rel="bookmark">' . $time_string . '</a>'
	);

	echo '<span class="posted-on">' . $posted_on . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * Prints HTML with meta information for the current author.
 */
function brace_yourself_posted_by() {
	$byline = sprintf(
		/* translators: %s: post author. */
		esc_html_x( 'by %s', 'post author', 'brace-yourself' ),
		'<span class="author vcard"><a class="url fn n" href="' . esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ) . '">' . esc_html( get_the_author() ) . '</a></span>'
	);

	echo '<span class="byline"> ' . $byline . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * Prints HTML with meta information for the categories, tags and comments.
 */
function brace_yourself_entry_footer() {
	// Hide category and tag text for pages.
	if ( 'post' === get_post_type() ) {
		/* translators: used between list items, there is a space after the comma */
		$categories_list = get_the_category_list( esc_html__( ', ', 'brace-yourself' ) );
		if ( $categories_list ) {
			/* translators: 1: list of categories. */
			printf( '<span class="cat-links">' . esc_html__( 'Posted in %1$s', 'brace-yourself' ) . '</span>', $categories_list ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		/* translators: used between list items, there is a space after the comma */
		$tags_list = get_the_tag_list( '', esc_html_x( ', ', 'list item separator', 'brace-yourself' ) );
		if ( $tags_list ) {
			/* translators: 1: list of tags. */
			printf( '<span class="tags-links">' . esc_html__( 'Tagged %1$s', 'brace-yourself' ) . '</span>', $tags_list ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}

	edit_post_link(
		sprintf(
			wp_kses(
				/* translators: %s: Name of current post. Only visible to screen readers */
				__( 'Edit <span class="sr-only">%s</span>', 'brace-yourself' ),
				array(
					'span' => array(
						'class' => array(),
					),
				)
			),
			wp_kses_post( get_the_title() )
		),
		'<span class="edit-link">',
		'</span>'
	);
}

/**
 * Displays an optional post thumbnail.
 *
 * Wraps the post thumbnail in an anchor element on index views, or a div
 * element on single views.
 */
function brace_yourself_post_thumbnail() {
	if ( post_password_required() || is_attachment() || ! has_post_thumbnail() ) {
		return;
	}

	if ( is_singular() ) :
		?>

		<div class="post-thumbnail">
			<?php the_post_thumbnail( 'content-large' ); ?>
		</div><!-- .post-thumbnail -->

	<?php else : ?>

		<a class="post-thumbnail" href="<?php the_permalink(); ?>" aria-hidden="true" tabindex="-1">
			<?php
			the_post_thumbnail(
				'content-medium',
				array(
					'alt' => the_title_attribute(
						array(
							'echo' => false,
						)
					),
				)
			);
			?>
		</a>

		<?php
	endif; // End is_singular().
}

/**
 * Render the site title SVG markup.
 *
 * Outputs the accessible hidden text, desktop SVG, and mobile ticker SVGs.
 * Uses a path-based SVG with exact viewBox (1400×96) — no font estimation needed.
 * fill="currentColor" inherits from the link's CSS color for easy theming.
 *
 * Keeps all SVG logic in one place — header.php just calls this function.
 */
function brace_yourself_site_title_svg() {
	$site_name = get_bloginfo( 'name' );

	// Path data for the site title SVG (viewBox: 0 0 1400 96)
	$paths = '<path d="m34.832 56.664.02 13.875 23.52-.034c11.761-.017 14.005-1.605 13.998-6.627v-.793c-.009-5.286-1.993-6.471-14.017-6.455zm72.299 11.393c.025 17.84-9.61 25.782-38.813 25.824l-68.186.097L0 1.478l66.336-.095c29.204-.042 38.86 7.344 38.883 23.201v.793c.018 12.157-5.257 19.565-20.055 20.776l.002 1.189c16.254.902 21.946 7.5 21.964 19.79zm-72.344-43.24.019 13.215 26.692-.038c7.268-.01 8.984-1.334 8.978-5.96v-.528c-.008-4.889-1.86-6.736-11.375-6.723zM146.151 24.659l.025 17.443 28.674-.04c5.947-.01 8.851-1.996 8.843-7.81v-.66c-.011-7.004-2.789-8.983-14.02-8.967zm.098 69.11-34.753.05-.132-92.5 65.542-.094c31.449-.045 41.502 6.283 41.528 24.783l.003 2.511c.02 13.743-4.859 20.357-18.6 22.095l.001 1.19c13.218 1.83 18.639 4.466 18.664 22.04l.029 19.822-34.754.05-.027-19.294c-.01-6.871-1.862-9.115-14.681-9.096l-22.861.033zM286.897 24.711 274.872 54.18h25.24L288.087 24.71zm68.979 69.111h-39.247L309.89 77.7h-44.796l-6.607 16.122h-37.529l41.229-92.5h52.461zM401.502 95.143c-38.982 0-53.65-11.232-53.65-44.796v-5.55C347.852 11.231 362.52 0 401.502 0h6.475c35.282 0 49.818 9.778 52.989 34.621h-35.413c-2.115-7.533-7.004-9.515-20.482-9.515h-.793c-19.028 0-21.011 6.872-21.011 20.614v3.832c0 13.478 1.983 20.482 21.011 20.482h.793c13.478 0 18.368-1.982 20.482-9.515h35.413c-3.171 24.843-17.707 34.621-52.989 34.621h-6.475zM557.307 93.822h-91.838v-92.5h91.838v23.654h-57.084v10.703h57.084v23.653h-57.084v10.836h57.084zM636.581 93.822h-34.885V54.708L559.938 1.322h41.229L620.724 29.6h1.057l19.558-28.278h37.131l-41.889 53.386z"/><path d="M724.195 70.037c19.821 0 22.332-7.004 22.332-20.482v-3.832c0-13.875-2.511-20.614-22.332-20.614h-2.115c-19.821 0-22.332 6.739-22.332 20.614v3.832c0 13.478 2.511 20.482 22.332 20.482zm-4.758 25.106c-39.643 0-55.104-11.232-55.104-44.796v-5.419c0-33.3 15.46-44.928 55.104-44.928h7.4c39.643 0 55.104 11.628 55.104 44.929v5.418c0 33.564-15.46 44.796-55.104 44.796zM835.352 95.143c-39.51 0-49.025-8.986-49.025-42.682V1.321h35.282v50.083c0 16.254 3.172 18.367 16.65 18.367h1.718c13.346 0 16.65-2.246 16.65-18.367V1.322h35.414V52.46c0 33.696-9.646 42.682-49.157 42.682zM931.802 24.711v17.443h28.675c5.946 0 8.853-1.982 8.853-7.797v-.66c0-7.004-2.774-8.986-14.007-8.986zm0 69.111h-34.753v-92.5h65.542c31.451 0 41.489 6.343 41.489 24.842v2.511c0 13.743-4.886 20.35-18.629 22.068v1.19c13.214 1.85 18.629 4.493 18.629 22.068v19.821h-34.75V74.529c0-6.871-1.85-9.118-14.667-9.118h-22.861zM1060.4 95.143c-29.87 0-49.95-6.74-52.86-29.204h37.13c1.59 5.418 6.74 6.476 19.82 6.476 14.54 0 17.71-1.983 17.71-6.476v-1.057c0-3.7-2.25-5.022-9.52-5.418l-24.05-.793c-29.07-1.057-38.98-7.928-38.98-26.825v-.925C1009.65 8.986 1023.92 0 1060.26 0h3.84c29.86 0 49.95 8.458 52.59 30.525h-37.13c-1.72-5.682-6.08-7.797-18.24-7.797-12.82 0-16.38 1.85-16.38 6.74v.527c0 4.097 2.24 5.287 9.51 5.682l23.79.793c29.2 1.057 39.11 7.664 39.11 26.164v.925c0 23.39-15.46 31.582-51.54 31.582h-5.41zM1212.68 93.822h-91.84v-92.5h91.84v23.654h-57.09v10.703h57.09v23.653h-57.09v10.836h57.09zM1303.82 93.822h-86.81v-92.5h34.75v65.41h52.06zM1342.92 93.822h-34.76v-92.5H1400v23.654h-57.08v10.703H1400v23.653h-57.08z"/>';

	?>
	<span class="sr-only"><?php echo esc_html( $site_name ); ?></span>
	<svg class="site-title__svg site-title__svg--desktop" viewBox="0 0 1400 96" fill="currentColor" aria-hidden="true"><?php echo $paths; ?></svg>
	<span class="site-title__ticker" aria-hidden="true">
		<svg class="site-title__svg site-title__svg--mobile" viewBox="0 0 1400 96" fill="currentColor"><?php echo $paths; ?></svg>
		<svg class="site-title__svg site-title__svg--mobile" viewBox="0 0 1400 96" fill="currentColor"><?php echo $paths; ?></svg>
	</span>
	<?php
}
