<?php
/**
 * Update version.
 *
 * @package easy-accordion-free
 */

update_option( 'easy_accordion_free_version', '2.1.14' );
update_option( 'easy_accordion_free_db_version', '2.1.14' );

$args     = new \WP_Query(
	array(
		'post_type'      => array( 'page' ),
		'post_status'    => 'publish',
		'posts_per_page' => '300',
	)
);
$post_ids = wp_list_pluck( $args->posts, 'ID' );

if ( count( $post_ids ) > 0 ) {
	add_filter( 'wp_revisions_to_keep', '__return_false' );
	foreach ( $post_ids as $post_key => $pid ) {
		$post_data    = get_post( $pid );
		$post_content = isset( $post_data->post_content ) ? $post_data->post_content : '';
		if ( ! empty( $post_content ) && ( strpos( $post_content, 'wp:sp-easy-accordion-free' ) !== false ) ) {
			$post_content = preg_replace( '/wp:sp-easy-accordion-free/i', 'wp:sp-easy-accordion-pro', $post_content );

			$gutenberg_post = array(
				'ID'           => $pid,
				'post_content' => $post_content,
			);
			// Update the post into the database.
			wp_update_post( $gutenberg_post );
		}

		$post_meta = get_post_meta( $pid, '_elementor_data', true );

		if ( ! empty( $post_meta ) && ( strpos( $post_meta, 'sp_easy_accordion_free_shortcode' ) !== false ) ) {
			$post_meta = preg_replace( '/sp_easy_accordion_free_shortcode/i', 'sp_easy_accordion_pro_shortcode', $post_meta );

			update_post_meta( $pid, '_elementor_data', $post_meta );
		}
	}
}
