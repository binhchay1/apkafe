<?php
use Lasso\Classes\Helper as Lasso_Helper;

use Lasso\Libraries\Lasso_URL;

// ? Wrong aawp fields structure
if ( ! $fields_value ) {
	return '';
}

if ( in_array( $fields_value, Lasso_Shortcode::AAWP_SUPPORT_FIELDS_VALUE, true ) ) {
	if ( 'hide' === $title ) {
		$lasso_url->name = '';
	} elseif ( '' !== $title ) {
		$lasso_url->name = $title;
	}

	$lasso_url_obj = new Lasso_URL( $lasso_url );

	echo '<span class="lasso-aawp-fields">';
	if ( 'title' === $fields_value ) {
		echo esc_html( $lasso_url->name );
	} elseif ( 'image' === $fields_value ) {
		echo esc_html( $lasso_url->image_src );
	} elseif ( 'thumb' === $fields_value ) {
		$output = Lasso_Helper::include_with_variables(
			LASSO_PLUGIN_PATH . '/admin/views/displays/aawp/fields/thumb.php',
			array(
				'lasso_url'     => $lasso_url,
				'lasso_url_obj' => $lasso_url_obj,
				'atts'          => $atts,
			),
			false
		);

		echo $output;
	} elseif ( 'button' === $fields_value ) {
		$output = Lasso_Helper::include_with_variables(
			LASSO_PLUGIN_PATH . '/admin/views/displays/aawp/fields/button.php',
			array(
				'lasso_url'     => $lasso_url,
				'lasso_url_obj' => $lasso_url_obj,
				'settings'      => $settings,
				'theme'         => $theme
			),
			false
		);

		echo $output;
	} elseif ( 'price' === $fields_value ) {
		echo esc_html( $lasso_url->price );
	} elseif ( 'list_price' === $fields_value ) {
		echo esc_html( $lasso_url->amazon->savings_basis );
	} elseif ( 'amount_saved' === $fields_value ) {
		echo esc_html( $lasso_url->amazon->savings_amount );
	} elseif ( 'percentage_saved' === $fields_value ) {
		echo esc_html( $lasso_url->amazon->savings_percent );
	} elseif ( 'rating' === $fields_value ) {
		echo $lasso_url->amazon->rating;
	} elseif ( 'star_rating' === $fields_value ) {
		$output = Lasso_Helper::include_with_variables(
			LASSO_PLUGIN_PATH . '/admin/views/displays/aawp/fields/star-rating.php',
			array(
				'lasso_url' => $lasso_url,
			),
			false
		);

		echo $output;
	} elseif ( 'description' === $fields_value ) {
		if ( 'hide' === $description ) {
			$lasso_url->description = '';
		} elseif ( '' !== $description ) {
			$lasso_url->description = $description;
		}

		$output = Lasso_Helper::include_with_variables(
			LASSO_PLUGIN_PATH . '/admin/views/displays/aawp/fields/description.php',
			array(
				'lasso_url'           => $lasso_url,
				'is_show_description' => $is_show_description,
			),
			false
		);

		echo $output;
	} elseif ( 'url' === $fields_value ) {
		echo esc_html( $lasso_url->public_link );
	} elseif ( 'link' === $fields_value && $lasso_url->name ) {
		echo '<a ' . $lasso_url_obj->render_attributes($lasso_url->title_url) . '>' . $lasso_url->name . '</a>';
	} elseif ( 'reviews' === $fields_value ) {
		echo number_format( intval( $lasso_url->amazon->reviews ) ) . ' Reviews';
	} elseif ( 'last_update' === $fields_value && $lasso_url->display->show_price && '' !== $lasso_url->price ) {
		echo $lasso_url->display->last_updated;
	} else {
		echo '';
	}
	echo '</span>';
} else {
	// ? AAWP is installed
	if ( class_exists( 'AAWP_Core' ) ) {
		$aawp_core = new AAWP_Core();

		echo $aawp_core->render_shortcode( $atts, $content );
	} else {
		echo '';
	}
}
