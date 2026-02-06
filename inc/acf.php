<?php
/**
 * Advanced Custom Fields Configuration
 *
 * Registers ACF field groups and provides helper functions.
 *
 * @package Brace_Yourself
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Check if ACF is active.
 *
 * @return bool True if ACF is active.
 */
function brace_yourself_acf_active() {
	return function_exists( 'acf_add_local_field_group' );
}

/**
 * Register ACF field groups.
 *
 * All field groups should be registered here using acf_add_local_field_group().
 * This keeps field definitions version-controlled and avoids database dependencies.
 */
function brace_yourself_register_acf_field_groups() {
	if ( ! brace_yourself_acf_active() ) {
		return;
	}

	// Example: Hero Section Field Group
	acf_add_local_field_group(
		array(
			'key'                   => 'group_hero_section',
			'title'                 => 'Hero Section',
			'fields'                => array(
				array(
					'key'               => 'field_hero_heading',
					'label'             => 'Heading',
					'name'              => 'heading',
					'type'              => 'text',
					'required'          => 1,
					'placeholder'       => 'Enter hero heading',
				),
				array(
					'key'               => 'field_hero_subheading',
					'label'             => 'Subheading',
					'name'              => 'subheading',
					'type'              => 'textarea',
					'rows'              => 3,
				),
				array(
					'key'               => 'field_hero_image',
					'label'             => 'Hero Image',
					'name'              => 'image',
					'type'              => 'image',
					'return_format'     => 'array',
					'preview_size'      => 'medium',
					'library'          => 'all',
				),
				array(
					'key'               => 'field_hero_cta_text',
					'label'             => 'CTA Button Text',
					'name'              => 'cta_text',
					'type'              => 'text',
				),
				array(
					'key'               => 'field_hero_cta_link',
					'label'             => 'CTA Button Link',
					'name'              => 'cta_link',
					'type'              => 'link',
				),
			),
			'location'              => array(
				array(
					array(
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => 'page',
					),
				),
			),
			'menu_order'            => 0,
			'position'              => 'normal',
			'style'                 => 'default',
			'label_placement'       => 'top',
			'instruction_placement' => 'label',
		)
	);

	// Add more field groups here as needed
}
add_action( 'acf/init', 'brace_yourself_register_acf_field_groups' );

/**
 * Get ACF field value with fallback.
 *
 * @param string $field_name Field name.
 * @param mixed  $fallback   Fallback value.
 * @param int    $post_id    Post ID (optional).
 * @return mixed Field value or fallback.
 */
function brace_yourself_get_field( $field_name, $fallback = '', $post_id = null ) {
	if ( ! brace_yourself_acf_active() ) {
		return $fallback;
	}

	$value = get_field( $field_name, $post_id );
	return $value !== false && $value !== null ? $value : $fallback;
}
