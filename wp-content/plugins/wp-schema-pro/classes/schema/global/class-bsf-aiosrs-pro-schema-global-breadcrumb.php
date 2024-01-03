<?php
/**
 * Schemas Template.
 *
 * @package Schema Pro
 * @since 1.1.0
 */

if ( ! class_exists( 'BSF_AIOSRS_Pro_Schema_Global_Breadcrumb' ) ) {

	/**
	 * AIOSRS Schemas Initialization
	 *
	 * @since 1.1.0
	 */
	class BSF_AIOSRS_Pro_Schema_Global_Breadcrumb {

		/**
		 * Render Schema.
		 *
		 * @param  array $post Current Post Array.
		 * @return array
		 */
		public static function render( $post ) {
			$schema = array();

			$schema['@context'] = 'https://schema.org';
			$schema['@type']    = 'BreadcrumbList';

			$breadcrumb_list = BSF_AIOSRS_Pro_Schema_Template::get_breadcrumb_list();
			foreach ( $breadcrumb_list as $key => $breadcrumb ) {
				$schema['itemListElement'][ $key ]['@type']        = 'ListItem';
				$schema['itemListElement'][ $key ]['position']     = $key + 1;
				$schema['itemListElement'][ $key ]['item']['@id']  = $breadcrumb['url'];
				$schema['itemListElement'][ $key ]['item']['name'] = $breadcrumb['title'];
			}

			return apply_filters( 'wp_schema_pro_global_schema_breadcrumb', $schema, $post );
		}

	}
}
