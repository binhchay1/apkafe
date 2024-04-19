<?php
/**
 * Declare class Html_Helper
 *
 * @package Html_Helper
 */

namespace Lasso\Classes;

use Lasso\Classes\Cache_Per_Process as Lasso_Cache_Per_Process;
use Lasso\Classes\Link_Location as Lasso_Link_Location;
use Lasso\Classes\Setting as Lasso_Setting;
use Lasso\Models\Fields as Model_Field;
use Lasso\Libraries\Field\Lasso_Object_Field;

use Lasso\Classes\Helper as Lasso_Helper;

/**
 * Html_Helper
 */
class Html_Helper {

	/**
	 * Render a select option html
	 *
	 * @param string      $name           A name of select tag.
	 * @param array       $options        The list options.
	 * @param null|string $selected_value A Selected value.
	 *
	 * @return string
	 */
	public static function render_select_option( $name, $options, $selected_value = null ) {

		$select_html = '<select name="' . $name . '" class="form-control">';
		foreach ( $options as $option => $display ) {
			$selected_theme_option = '';
			if ( $selected_value === $option ) {
				$selected_theme_option = 'selected';
			}
			$select_html .= '<option value="' . $option . '" ' . $selected_theme_option . ' >' . $display . '</option>';
		}
		$select_html .= '</select>';
		return $select_html;
	}

	/**
	 * Render html by display type
	 *
	 * @param array $data An array of post meta data.
	 *
	 * @return string
	 */
	public static function render_view_by_display_type( $data ) {
		$html                   = '';
		$custom_width_css_class = '';
		$custom_style           = '';
		if ( ! empty( $data['width'] ) ) {
			list ( $width, $custom_width_css_class ) = self::determine_css_class_by_width( $data['width'], $data['custom_width'] );
			$custom_style                            = " style='max-width: %spx; margin: 0 auto;' ";
			$custom_style                            = sprintf( $custom_style, $width );
		}
		$number_of_column = ! empty( $data['number_of_column'] ) ? $data['number_of_column'] : 3;
		switch ( $data['display_type'] ) {
			case Lasso_Setting::DISPLAY_TYPE_LIST:
				$html .= '<ol class="lasso-list-ol lasso-list-style-decimal" ' . $custom_style . ' >';
				foreach ( $data['items'] as $item_html ) {
					$html .= $item_html;
				}
				$html .= '</ol>';
				break;
			case Lasso_Setting::DISPLAY_TYPE_GRID:
				$html .= '<div class="lasso-grid-wrap">';
				$html .= "<div class='lasso-grid-row {$custom_width_css_class} lasso-grid-{$number_of_column}' {$custom_style}>";
				foreach ( $data['items'] as $item_html ) {
					$html .= $item_html;
				}
				$html .= '</div>';
				$html .= '</div>';
				break;
			default:
				$html .= '<div class="lasso-single" ' . $custom_style . '>';
				if ( count( $data['items'] ) > 0 ) {
					$html .= $data['items'][0];
				}
				$html .= '</div>';
		}
		return $html;
	}

	/**
	 * Return width and class_name
	 *
	 * @param int  $width        Width from dropdown.
	 * @param null $custom_width Customize width from User.
	 *
	 * @return array
	 */
	private static function determine_css_class_by_width( $width, $custom_width = null ) {
		$custom_width_css_class = '';
		if ( Lasso_Setting::W_CUSTOM !== $width ) {
			$custom_width_css_class = 'w-%s';
			$custom_width_css_class = sprintf( $custom_width_css_class, $width );
		} else {
			if ( ! empty( $custom_width ) ) {
				$width = $custom_width;
				if ( $custom_width <= 599 ) {
					$custom_width_css_class = 'w-custom-500';
				} elseif ( $custom_width > 599 && $custom_width <= Lasso_Setting::W_650 ) {
					$custom_width_css_class = 'w-custom-600';
				} elseif ( $custom_width > Lasso_Setting::W_650 && $custom_width <= Lasso_Setting::W_750 ) {
					$custom_width_css_class = 'w-750';
				} elseif ( $custom_width > Lasso_Setting::W_750 && $custom_width <= Lasso_Setting::W_800 ) {
					$custom_width_css_class = 'w-800';
				} elseif ( $custom_width > Lasso_Setting::W_800 && $custom_width <= Lasso_Setting::W_1000 ) {
					$custom_width_css_class = 'w-1000';
				}
			} else {
				$custom_width_css_class = 'w-750';
				$width                  = Lasso_Setting::W_750;
			}
		}

		return array( $width, $custom_width_css_class );
	}

	/**
	 * Render html template
	 *
	 * @deprecated Should use Lasso\Classes\Lasso_Helper::include_with_variables().
	 *
	 * @param string $file_path        Absolute path to file.
	 * @param array  $args             Parameters.
	 * @param bool   $output_ajax_html Output a ajax html string or not.
	 *
	 * @return false|string
	 */
	public static function render_template( $file_path, $args, $output_ajax_html = true ) {
		if ( file_exists( $file_path ) ) {
			extract( $args ); // phpcs:ignore
			if ( $output_ajax_html ) {
				ob_start();
				require $file_path;
				$html = ob_get_clean();
				return $html;
			} else {
				require $file_path;
			}
		}
		return false;
	}

	/**
	 * Render a toggle option
	 *
	 * @param string      $name    Name.
	 * @param string      $label   Label.
	 * @param null|string $value   Default value.
	 * @param bool        $checked Default checked.
	 * @param array       $addition_attrs An array attributes.
	 *
	 * @return mixed
	 */
	public static function render_toggle_option( $name, $label, $value = null, $checked = false, $addition_attrs = array() ) {
		Lasso_Helper::include_with_variables(
			LASSO_PLUGIN_PATH . '/admin/views/components/checkbox.php',
			array(
				'name'           => $name,
				'value'          => $value,
				'label'          => $label,
				'checked'        => $checked ? 'checked' : '',
				'addition_attrs' => $addition_attrs,
			),
			false
		);
	}

	/**
	 * Render attrs html
	 *
	 * @param array $attrs array('lasso-id' => 1).
	 *
	 * @return string
	 */
	public static function render_attrs( $attrs ) {
		$attr_html = '';
		foreach ( $attrs as $key => $value ) {
			$data_attribute = 'data-%s="%s"';
			$attr_html     .= sprintf( $data_attribute, $key, $value ) . ' ';
		}
		return $attr_html;
	}

	/**
	 * Build image lazyload attributes
	 *
	 * @return string
	 */
	public static function build_img_lazyload_attributes() {
		$result = 'loading="lazy"'; // ? WP default lazyload.

		if ( Lasso_Helper::is_ezoic_plugin_active() ) {
			$result = 'class="ezlazyload"';
		} elseif ( Lasso_Helper::is_wp_rocket_lazyload_image_enabled() ) {
			$result = 'class="rocket-lazyload"';
		}

		return $result;
	}

	/**
	 * Render Pros / Cons html
	 *
	 * @param int    $field_id    Field id.
	 * @param string $field_value Field value.
	 * @return string
	 */
	public static function render_pros_cons_field( $field_id, $field_value ) {
		$html       = '<ul>';
		$field_id   = intval( $field_id );
		$list_style = '';

		if ( Lasso_Object_Field::PROS_FIELD_ID === $field_id ) {
			$list_style = '<span class="lasso-check"><span class="lasso-check-content"></span></span>';
		} elseif ( Lasso_Object_Field::CONS_FIELD_ID === $field_id ) {
			$list_style = '<span class="lasso-x"><span class="lasso-x-1"></span><span class="lasso-x-2"></span></span>';
		}

		if ( ! $list_style || ! $field_value ) {
			return '';
		}

		$bits = explode( "\n", $field_value ); // phpcs:ignore
		foreach ( $bits as $bit ) {
			if ( empty( $bit ) ) {
				continue;
			}
			$html .= '<li>' . $list_style . $bit . '</li>';
		}

		$html .= '</ul>';

		return $html;
	}

	/**
	 * Render List html
	 *
	 * @param int    $field_type  Field type.
	 * @param string $field_value Field value.
	 * @return string
	 */
	public static function render_list_field( $field_type, $field_value ) {
		$html_tag = '';
		if ( Model_Field::FIELD_TYPE_BULLETED_LIST === $field_type ) {
			$html_tag = 'ul';
		} elseif ( Model_Field::FIELD_TYPE_NUMBERED_LIST === $field_type ) {
			$html_tag = 'ol';
		}

		if ( ! $html_tag ) {
			return '';
		}

		if ( ! $field_value ) {
			return '';
		}

		$html = '<' . $html_tag . ' class="list">';

		$bits = explode( "\n", $field_value ); // phpcs:ignore
		foreach ( $bits as $bit ) {
			if ( empty( $bit ) ) {
				continue;
			}
			$html .= '<li>' . $bit . '</li>';
		}

		$html .= '</' . $html_tag . '>';

		return $html;
	}

	/**
	 * Render field types html
	 */
	public static function render_field_types() {
		$fields = array(
			Model_Field::FIELD_TYPE_TEXT          => array(
				'far fa-text', // ? icon
				'Short Text', // ? label
				true, // ? default
			),
			Model_Field::FIELD_TYPE_TEXT_AREA     => array(
				'far fa-paragraph', // ? icon
				'Long Text', // ? label
				false, // ? default
			),
			Model_Field::FIELD_TYPE_NUMBER        => array(
				'far fa-hashtag', // ? icon
				'Number', // ? label
				false, // ? default
			),
			Model_Field::FIELD_TYPE_RATING        => array(
				'far fa-star', // ? icon
				'Rating', // ? label
				false, // ? default
			),
			Model_Field::FIELD_TYPE_BULLETED_LIST => array(
				'far fa-paragraph', // ? icon
				'Bulleted List', // ? label
				false, // ? default
			),
			Model_Field::FIELD_TYPE_NUMBERED_LIST => array(
				'far fa-paragraph', // ? icon
				'Numbered List', // ? label
				false, // ? default
			),
		);

		$lasso_setting = new Lasso_Setting();
		if ( $lasso_setting->is_lasso_table_page() ) {
			$fields[ Model_Field::FIELD_TYPE_BULLETED_LIST ] = array(
				'far fa-paragraph', // ? icon
				'Bulleted List', // ? label
				false, // ? default
			);

			$fields[ Model_Field::FIELD_TYPE_NUMBERED_LIST ] = array(
				'far fa-paragraph', // ? icon
				'Numbered List', // ? label
				false, // ? default
			);
		}

		$option_html = '';
		foreach ( $fields as $key => $field ) {
			$default      = $field[2] ? 'default' : '';
			$option_html .= '<option data-icon="' . $field[0] . '" value="' . $key . '" ' . $default . '>' . $field[1] . '</option>';
		}

		$html = '
			<select id="field-type-picker" data-show-content="true" class="selectpicker form-control">
				' . $option_html . '
			</select>
		';

		return $html;
	}

	/**
	 * Return html field name comparison table
	 *
	 * @param string $field_name Field name.
	 * @return string
	 */
	public static function get_html_field_name_comparison_table( $field_name ) {
		if ( 'Price' !== $field_name ) {
			return '<div class="field-name"><strong>' . $field_name . ':</strong></div>';
		}
	}

	/**
	 * Get rocket icon of Font Awesome 5
	 */
	public static function get_plus_icon() {
		return '
			<svg class="svg-inline--fa fa-plus fa-w-12" aria-hidden="true" focusable="false" data-prefix="far" data-icon="plus" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512" data-fa-i2svg=""><path fill="currentColor" d="M368 224H224V80c0-8.84-7.16-16-16-16h-32c-8.84 0-16 7.16-16 16v144H16c-8.84 0-16 7.16-16 16v32c0 8.84 7.16 16 16 16h144v144c0 8.84 7.16 16 16 16h32c8.84 0 16-7.16 16-16V288h144c8.84 0 16-7.16 16-16v-32c0-8.84-7.16-16-16-16z"></path></svg>
		';
	}

	/**
	 * Generate attributes html
	 *
	 * @param array $attrs An attributes array.
	 *
	 * @return string
	 */
	public static function generate_attrs( $attrs ) {
		$attrs_str = '';
		foreach ( $attrs as $key => $attr ) {
			$attrs_str .= $key . '="' . $attr . '" ';
		}
		return $attrs_str;
	}

	/**
	 * Get link location display information.
	 *
	 * @param string $link_type    Link Location type.
	 * @param string $display_type Link Location display type.
	 * @param string $anchor_text  Link Location anchor text.
	 * @param int    $lasso_id     Lasso ID.
	 */
	public static function get_link_location_displays( $link_type, $display_type, $anchor_text, $lasso_id ) {
		$icon_class       = 'fa-link'; // ? Icon class.
		$type_description = 'a text link'; // ? Text type description.
		$link_color       = 'light-purple'; // ? Link Color

		$is_lasso_shortcode = Lasso_Link_Location::is_lasso_shortcode( $display_type );

		if ( ( isset( $anchor_text ) ) && ( strpos( $anchor_text, 'img' ) > 0 ) ) {
			$icon_class       = 'fa-image';
			$type_description = 'an image link';
		} elseif ( 'keyword' === $link_type ) {
			$icon_class       = 'fa-key';
			$type_description = 'a keyword mention';
		} elseif ( Lasso_Link_Location::LINK_TYPE_LASSO === $link_type && $is_lasso_shortcode ) {
			$icon_class       = 'fa-pager';
			$type_description = 'a single link display';
		}

		// ? Link Color
		if ( Lasso_Link_Location::LINK_TYPE_LASSO === $link_type && $lasso_id > 0 ) {
			$link_color = 'green';
		} elseif ( 'keyword' === $link_type ) {
			$link_color = 'light-purple';
		}

		return array( $icon_class, $type_description, $link_color );
	}

	/**
	 * Get brag icon
	 */
	public static function get_brag_icon() {
		$cache_key  = 'lasso_brag_icon';
		$brag_cache = Lasso_Cache_Per_Process::get_instance()->get_cache( $cache_key, '' );

		if ( $brag_cache ) {
			return $brag_cache;
		}

		if ( ! Lasso_Helper::is_show_brag_icon() ) {
			return '';
		}

		$lasso_settings = Lasso_Setting::lasso_get_settings();
		$lasso_url      = $lasso_settings['lasso_affiliate_URL'] ?? false;

		$icon_brag           = esc_url( LASSO_PLUGIN_URL . 'admin/assets/images/lasso-icon-brag.png' );
		$lasso_affiliate_url = Lasso_Helper::add_params_to_url( $lasso_url, array( 'utm_source' => 'brag' ) );
		$img_attr            = self::build_img_lazyload_attributes();
		$icon                = '
			<a class="lasso-brag" href="' . $lasso_affiliate_url . '" target="_blank" rel="nofollow noindex">
				<img src="' . $icon_brag . '" ' . $img_attr . ' alt="Lasso Brag" width="30" height="30">
			</a>
		';
		Lasso_Cache_Per_Process::get_instance()->set_cache( $cache_key, $icon );

		return $icon;
	}
}
