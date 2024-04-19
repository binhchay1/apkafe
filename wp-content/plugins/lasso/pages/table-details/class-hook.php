<?php
/**
 * Lasso Table Details - Hook.
 *
 * @package Pages
 */

namespace Lasso\Pages\Table_Details;

use Lasso\Models\Fields as Lasso_Fields;
use Lasso\Classes\Helper as Lasso_Helper;


/**
 * Lasso Table Details - Hook.
 */
class Hook {
	/**
	 * Declare "Lasso register hook events" to WordPress.
	 *
	 * @codeCoverageIgnore Coverage ignore.
	 */
	public function register_hooks() {
		add_filter( 'lasso_filter_table_field_value', array( $this, 'create_webp_image' ), 10, 2 );
	}

	/**
	 * Create webp image for table image field
	 *
	 * @param string $field_value Field value.
	 * @param int    $field_id    Field Id.
	 * @return string|null
	 *
	 * @codeCoverageIgnore Coverage ignore.
	 */
	public function create_webp_image( $field_value, $field_id ) {
		if ( Lasso_Fields::IMAGE_FIELD_ID === intval( $field_id )
			&& strpos( $field_value, '.webp' ) === false
			&& LASSO_DEFAULT_THUMBNAIL !== $field_value ) {
			if ( ! $field_value || strpos( $field_value, 'lasso-no-thumbnail.jpg' ) !== false ) {
				return LASSO_DEFAULT_THUMBNAIL;
			}

			$webp_image_url = Lasso_Helper::create_webp_image_from_url( $field_value );
			$field_value    = $webp_image_url ? $webp_image_url : $field_value;
		}

		return $field_value;
	}
}
