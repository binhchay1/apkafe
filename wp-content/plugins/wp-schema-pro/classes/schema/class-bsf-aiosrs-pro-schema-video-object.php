<?php
/**
 * Schemas Template.
 *
 * @package Schema Pro
 * @since 1.0.0
 */

if ( ! class_exists( 'BSF_AIOSRS_Pro_Schema_Video_Object' ) ) {

	/**
	 * AIOSRS Schemas Initialization
	 *
	 * @since 1.0.0
	 */
	class BSF_AIOSRS_Pro_Schema_Video_Object {

		/**
		 * Render Schema.
		 *
		 * @param  array<string, mixed> $data Meta Data.
		 * @param  array<string, mixed> $post Current Post Array.
		 * @return array<string, mixed>
		 */
		public static function render( array $data, array $post ): array {
			$schema = array();

			$schema['@context'] = 'https://schema.org';
			$schema['@type']    = 'VideoObject';

			$schema['name'] = ! empty( $data['name'] ) && is_string( $data['name'] ) ? wp_strip_all_tags( $data['name'] ) : null;

			$schema['description'] = ! empty( $data['description'] ) && is_string( $data['description'] ) ? wp_strip_all_tags( $data['description'] ) : null;

			if ( isset( $data['orgnization-name'] ) && is_string( $data['orgnization-name'] ) && ! empty( $data['orgnization-name'] ) ) {
				$schema['publisher']['@type'] = 'Organization';
				$schema['publisher']['name']  = wp_strip_all_tags( $data['orgnization-name'] );
			}

			if ( isset( $data['site-logo'] ) && is_array( $data['site-logo'] ) && ! empty( $data['site-logo'] ) ) {
				$schema['publisher']['@type'] = 'Organization';
				$schema['publisher']['logo']  = BSF_AIOSRS_Pro_Schema_Template::get_image_schema( $data['site-logo'], 'ImageObject' );
			}

			if ( isset( $data['image'] ) && is_array( $data['image'] ) && ! empty( $data['image'] ) ) {
				$schema['thumbnailUrl'] = BSF_AIOSRS_Pro_Schema_Template::get_image_schema( $data['image'], 'URL' );
			}

			$schema['uploadDate'] = ! empty( $data['upload-date'] ) && is_string( $data['upload-date'] ) ? wp_strip_all_tags( $data['upload-date'] ) : null;

			$schema['duration'] = ! empty( $data['duration'] ) && is_string( $data['duration'] ) ? wp_strip_all_tags( $data['duration'] ) : null;

			if ( isset( $data['content-url'] ) && is_string( $data['content-url'] ) && ! empty( $data['content-url'] ) ) {
				$schema['contentUrl'] = esc_url( $data['content-url'] );
			}

			if ( isset( $data['embed-url'] ) && is_string( $data['embed-url'] ) && ! empty( $data['embed-url'] ) ) {
				$schema['embedUrl'] = esc_url( $data['embed-url'] );
			}

			$schema['expires'] = ! empty( $data['expires-date'] ) && is_string( $data['expires-date'] ) ? wp_strip_all_tags( $data['expires-date'] ) : null;

			if ( isset( $data['interaction-count'] ) && is_string( $data['interaction-count'] ) && ! empty( $data['interaction-count'] ) ) {
				$schema['interactionStatistic']['@type']                    = 'InteractionCounter';
				$schema['interactionStatistic']['interactionType']['@type'] = 'WatchAction';
				$schema['interactionStatistic']['userInteractionCount']     = wp_strip_all_tags( $data['interaction-count'] );
			}

			if ( isset( $data['thumbnail-url'] ) && is_string( $data['thumbnail-url'] ) && ! empty( $data['thumbnail-url'] ) ) {
				$schema['thumbnailUrl'] = $data['thumbnail-url'];
			}

			if ( isset( $data['clip'] ) && is_array( $data['clip'] ) && ! empty( $data['clip'] ) ) {
				foreach ( $data['clip'] as $key => $value ) {
					if ( is_array( $value ) ) {
						$schema['hasPart'][ $key ]['@type']       = 'Clip';
						$schema['hasPart'][ $key ]['name']        = isset( $value['clip-name'] ) && is_string( $value['clip-name'] ) ? wp_strip_all_tags( $value['clip-name'] ) : null;
						$schema['hasPart'][ $key ]['startOffset'] = isset( $value['clip-start-offset'] ) && is_string( $value['clip-start-offset'] ) ? wp_strip_all_tags( $value['clip-start-offset'] ) : null;
						$schema['hasPart'][ $key ]['endOffset']   = isset( $value['clip-end-offset'] ) && is_string( $value['clip-end-offset'] ) ? wp_strip_all_tags( $value['clip-end-offset'] ) : null;
						$schema['hasPart'][ $key ]['url']         = isset( $value['clip-url'] ) && is_string( $value['clip-url'] ) ? esc_url( $value['clip-url'] ) : null;
					}
				}
			}

			if ( isset( $data['seekto-action-start-offset'] ) && is_string( $data['seekto-action-start-offset'] ) && ! empty( $data['seekto-action-start-offset'] ) && isset( $data['seekto-action-target'] ) && is_string( $data['seekto-action-target'] ) && ! empty( $data['seekto-action-target'] ) ) {
				$schema['potentialAction']['@type']             = 'SeekToAction';
				$schema['potentialAction']['target']            = esc_url( $data['seekto-action-target'] ) . '?t={seek_to_second_number}';
				$schema['potentialAction']['startOffset-input'] = 'required name=seek_to_second_number';
			}

			if ( isset( $data['regions-allowed'] ) && is_string( $data['regions-allowed'] ) && ! empty( $data['regions-allowed'] ) ) {
				$schema['regionsAllowed'] = wp_strip_all_tags( $data['regions-allowed'] );
			}

			if ( isset( $data['is-live-broadcast'] ) && $data['is-live-broadcast'] && is_bool( $data['is-live-broadcast'] ) ) {
				$schema['publication']['@type']           = 'BroadcastEvent';
				$schema['publication']['isLiveBroadcast'] = true;
				$schema['publication']['startDate']       = isset( $data['start-date'] ) && is_string( $data['start-date'] ) ? wp_strip_all_tags( $data['start-date'] ) : null;
				$schema['publication']['endDate']         = isset( $data['end-date'] ) && is_string( $data['end-date'] ) ? wp_strip_all_tags( $data['end-date'] ) : null;
			}

			return apply_filters( 'wp_schema_pro_schema_video_object', $schema, $data, $post );
		}

	}
}


