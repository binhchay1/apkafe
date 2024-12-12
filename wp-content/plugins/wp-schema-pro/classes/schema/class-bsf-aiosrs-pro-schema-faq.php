<?php
/**
 * Schemas Template.
 *
 * @package Schema Pro
 * @since 1.0.0
 */

if ( ! class_exists( 'BSF_AIOSRS_Pro_Schema_FAQ' ) ) {

	/**
	 * AIOSRS Schemas Initialization
	 *
	 * @since 1.0.0
	 */
	class BSF_AIOSRS_Pro_Schema_FAQ {

		/**
		 * Render Schema.
		 *
		 * @param  array $data Meta Data.
		 * @param  array $post Current Post Array.
		 * @return array
		 */
		public static function render( array $data, array $post ) {
			global $post; // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.VariableRedeclaration
			$schema = array();
			if ( isset( $data['question-answer'][0]['question'] ) && ! empty( $data['question-answer'][0]['question'] ) ) {

				$schema['@context'] = 'https://schema.org';
				$schema['@type']    = 'FAQPage';
				foreach ( $data['question-answer'] as $key => $value ) {
					if ( isset( $value['question'] ) && ! empty( $value['question'] ) ) {
						$schema['mainEntity'][ $key ]['@type'] = 'Question';
						$schema['mainEntity'][ $key ]['name']  = $value['question'];
					}
					if ( isset( $value['answer'] ) && ! empty( $value['answer'] ) ) {
						$schema['mainEntity'][ $key ]['acceptedAnswer']['@type'] = 'Answer';
						$schema['mainEntity'][ $key ]['acceptedAnswer']['text']  = $value['answer'];
					}
				}
			}
			return apply_filters( 'wp_schema_pro_schema_faq', $schema, $data, $post );
		}

	}
}
