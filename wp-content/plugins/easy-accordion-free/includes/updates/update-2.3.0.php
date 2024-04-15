<?php
/**
 * Update version.
 *
 * @package easy-accordion-free
 */

update_option( 'easy_accordion_free_version', '2.3.0' );
update_option( 'easy_accordion_free_db_version', '2.3.0' );

$args          = new \WP_Query(
	array(
		'post_type'      => array( 'sp_easy_accordion' ),
		'post_status'    => 'publish',
		'posts_per_page' => '500',
	)
);
$shortcode_ids = wp_list_pluck( $args->posts, 'ID' );
if ( count( $shortcode_ids ) > 0 ) {
	foreach ( $shortcode_ids as $shortcode_key => $shortcode_id ) {
		$shortcode_data = get_post_meta( $shortcode_id, 'sp_eap_shortcode_options', true );
		if ( ! is_array( $shortcode_data ) ) {
			continue;
		}
		$old_section_title_margin_bottom       = isset( $shortcode_data['section_title_margin_bottom'] ) ? $shortcode_data['section_title_margin_bottom'] : '30';
		$acc_section_title_margin_bottom       = isset( $shortcode_data['section_title_margin_bottom']['all'] ) ? $shortcode_data['section_title_margin_bottom']['all'] : $old_section_title_margin_bottom;
		$shortcode_data['eap_animation_style'] = 'normal';
		if ( ! empty( $shortcode_data['eap_section_title_typography'] ) ) {
			$shortcode_data['eap_section_title_typography']['margin-bottom'] = $acc_section_title_margin_bottom;
		}

		update_post_meta( $shortcode_id, 'sp_eap_shortcode_options', $shortcode_data );
	}
}
