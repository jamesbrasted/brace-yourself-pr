<?php
/**
 * Search form template
 *
 * No visible label — input uses aria-label for accessibility.
 * Not required for SEO.
 *
 * @package Brace_Yourself
 */

?>
<form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<input type="search" class="search-field" placeholder="<?php echo esc_attr_x( 'Search&hellip;', 'placeholder', 'brace-yourself' ); ?>" value="<?php echo get_search_query(); ?>" name="s" aria-label="<?php echo esc_attr_x( 'Search', 'label', 'brace-yourself' ); ?>" />
	<input type="submit" class="search-submit" value="<?php echo esc_attr_x( 'Search', 'submit button', 'brace-yourself' ); ?>" />
</form>
