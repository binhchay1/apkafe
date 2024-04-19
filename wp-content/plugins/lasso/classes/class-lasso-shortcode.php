<?php
/**
 * Declare class Lasso_Shortcode
 *
 * @package Lasso_Shortcode
 */

use ThirstyAffiliates\Models\Shortcodes as Lasso_Thirsty_Models_Shortcode;
use ThirstyAffiliates\Helpers\Plugin_Constants as Lasso_Thirsty_Helpers_Plugin_Constants;
use ThirstyAffiliates\Helpers\Helper_Functions as Lasso_Thirsty_Helpers_Helper_Functions;

use Lasso\Classes\Encrypt;
use Lasso\Classes\Cache_Per_Process as Lasso_Cache_Per_Process;
use Lasso\Classes\Enum as Lasso_Enum;
use Lasso\Classes\Helper as Lasso_Helper;
use Lasso\Classes\Import as Lasso_Import;
use Lasso\Classes\Link_Location as Lasso_Link_Location;
use Lasso\Classes\Setting_Enum as Lasso_Setting_Enum;
use Lasso\Classes\Setting as Lasso_Setting;

use Lasso\Models\Revert;
use Lasso\Models\Model;

/**
 * Lasso_Shortcode
 */
class Lasso_Shortcode {
	const OBJECT_KEY                = 'lasso_core_shortcode';
	const ATTRS_FILTER              = 'lasso_core_shortcode_attrs_filter';
	const COMPETITOR_SHORTCODES     = array(
		'amazon',
		'aawp',
		'amalinkspro',

		'easyazon_link',
		'easyazon-link',
		'simpleazon-link',

		'easyazon_image',
		'easyazon-image',
		'easyazon-image-link',
		'simpleazon-image',

		'easyazon_cta',
		'easyazon-cta',

		'easyazon_infoblock',
		'easyazon_block',
		'easyazon-block',
	);
	const AAWP_SUPPORT_FIELDS_VALUE = array(
		'title',
		'image',
		'thumb',
		'button',
		'price',
		'list_price',
		'amount_saved',
		'percentage_saved',
		'rating',
		'star_rating',
		'description',
		'reviews',
		'url',
		'link',
		'last_update',
	);

	/**
	 * Construction of Lasso_Shortcode
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'other_shortcodes_integration' ) );

		add_shortcode( 'lasso-attr', array( $this, 'lasso_attributes_shortcode' ) );
		add_shortcode( 'lasso', array( $this, 'lasso_core_shortcode' ) );

		// ? AmaLinks Pro plugin
		add_shortcode( 'amalinkspro', array( $this, 'amalinkspro_shortcode' ) );

		// ? AAWP plugin
		add_shortcode( 'amazon', array( $this, 'aawp_amazon_shortcode' ) );
		add_shortcode( 'aawp', array( $this, 'aawp_amazon_shortcode' ) );

		// ? Earnist plugin - Legacy
		add_shortcode( 'earnist', array( $this, 'earnist_main_shortcode' ) );
		add_shortcode( 'earnist_link', array( $this, 'earnist_link_shortcode' ) );

		// ? EasyAzon plugin - Link
		add_shortcode( 'easyazon_link', array( $this, 'easyazon_link_shortcode' ) );
		add_shortcode( 'easyazon-link', array( $this, 'easyazon_link_shortcode' ) );
		add_shortcode( 'simpleazon-link', array( $this, 'easyazon_link_shortcode' ) );

		// ? EasyAzon plugin - Image
		add_shortcode( 'easyazon_image', array( $this, 'easyazon_image_shortcode' ) );
		add_shortcode( 'easyazon-image', array( $this, 'easyazon_image_shortcode' ) );
		add_shortcode( 'easyazon-image-link', array( $this, 'easyazon_image_shortcode' ) );
		add_shortcode( 'simpleazon-image', array( $this, 'easyazon_image_shortcode' ) );

		// ? EasyAzon plugin - Button
		add_shortcode( 'easyazon_cta', array( $this, 'easyazon_button_shortcode' ) );
		add_shortcode( 'easyazon-cta', array( $this, 'easyazon_button_shortcode' ) );

		// ? EasyAzon plugin - Display box
		add_shortcode( 'easyazon_infoblock', array( $this, 'easyazon_box_shortcode' ) );
		add_shortcode( 'easyazon_block', array( $this, 'easyazon_box_shortcode' ) );
		add_shortcode( 'easyazon-block', array( $this, 'easyazon_box_shortcode' ) );

		// ? Handle Lasso shortcodes in Widgets text
		add_filter( 'widget_text', array( $this, 'lasso_widget_text' ), 1 );

		// ? Easy Affiliate Links plugin
		if ( class_exists( 'EAFL_Blocks' ) && defined( 'EAFL_POST_TYPE' ) ) {
			// ? Easy Affiliate Link plugin - Link
			add_shortcode( 'eafl', array( $this, 'easy_affiliate_link_shortcode' ) );

			remove_action( 'init', array( EAFL_Blocks::class, 'register_blocks' ) );
			add_action( 'init', array( $this, 'eafl_block' ) );

			remove_action( 'rest_api_init', array( EAFL_API_Links::class, 'api_register_data' ) );
			add_action( 'rest_api_init', array( $this, 'eafl_api_register_data' ) );
		}

		add_filter( 'the_content', array( $this, 'lasso_replace_other_plugin_shortcode_display' ), 10 );
		add_filter( self::ATTRS_FILTER, array( $this, 'lasso_shortcode_attrs_filter' ), 10, 1 );
	}

	/**
	 * Other shortcodes integration
	 */
	public function other_shortcodes_integration() {
		// ? Thirsty Affiliate plugin
		remove_shortcode( 'thirstylink' );
		add_shortcode( 'thirstylink', array( $this, 'thirstylink_shortcode' ) );
	}

	/**
	 * Shortcode template
	 *
	 * @param array $atts Attribute of shortcode.
	 */
	public function lasso_attributes_shortcode( $atts ) {
		$lasso_aff = new Lasso_Affiliate_Link();

		$post_id   = $atts['id'] ?? 0;
		$attr_name = $atts['attr_name'] ?? '';

		$attributes = $lasso_aff->search_attributes( $post_id, $attr_name );
		if ( count( $attributes ) > 0 ) {
			foreach ( $attributes as $value ) {
				$attribute = $value['meta_value'];
				$attribute = json_decode( $attribute );

				if ( $attribute->name === $attr_name ) {
					return nl2br( $attribute->value );
				}
			}
		}

		return '';
	}

	/**
	 * Lasso Display Boxes
	 *
	 * @param array  $atts    Attributes of shortcode.
	 * @param string $content content of shortcode.
	 */
	public function lasso_core_shortcode( $atts, $content = null ) {
		if ( Lasso_Helper::is_amp_plugin_loaded() ) {
			// ? amp_post_template_css is AMP's hook
			add_action( 'amp_post_template_css', array( $this, 'lasso_hook_css_inline' ), 1000 );

			// ? Lasso GA is working but we comment this line because AMP format does't allow custom script, Google Console would be reported the error
			// add_action( 'amp_post_template_footer', array( $this, 'lasso_hook_amp_post_template_footer' ), 1000 );
		}

		if ( Lasso_Helper::is_jetpack_plugin_loaded() ) {
			// ? Jetpack support the jetpack_photon_skip_for_url func so we can return a original image
			add_action( 'jetpack_photon_skip_for_url', array( $this, 'lasso_jetpack_skip_image' ), 1000 );
		}

		// ? Set flag to know we using shortcode display
		Lasso_Cache_Per_Process::get_instance()->set_cache( self::OBJECT_KEY, true );

		if ( Lasso_Helper::is_shortcode_attrs_invalid( $atts ) ) {
			$atts = Lasso_Helper::repair_shortcode_attr( $atts );
		}

		// ? Fix shortcode attributes case start with '&quot;' and end with '&quot;'
		$atts = array_map(
			function( $value ) {
				return preg_replace( '/^&quot;|&quot;$/', '', $value );
			},
			$atts
		);

		// ? Custom attrs information. Ex: The custom description from AAWP.
		$atts = apply_filters( self::ATTRS_FILTER, $atts );

		$slug            = $atts['slug'] ?? '';
		$post_id         = $atts['id'] ?? '';
		$prompt          = $atts['prompt'] ?? '';
		$format          = $atts['format'] ?? 'html';
		$link_id         = $atts['link_id'] ?? '';
		$link            = $atts['link'] ?? ''; // ? aawp shortcode
		$identifier      = $atts['identifier'] ?? ''; // ? easyazon shortcode
		$ref             = $atts['ref'] ?? '';
		$anchor_id       = $atts['anchor_id'] ?? '';
		$style           = $atts['style'] ?? '';
		$type            = $atts['type'] ?? '';
		$bullets         = $atts['bullets'] ?? '';
		$theme           = $atts['theme'] ?? '';
		$ga              = $atts['ga'] ?? '';
		$title           = $atts['title'] ?? '';
		$title_url       = $atts['title_url'] ?? '';
		$title_type      = $atts['title_type'] ?? '';
		$price           = $atts['price'] ?? '';
		$prime           = $atts['prime'] ?? '';
		$description     = $atts['description'] ?? '';
		$description     = str_replace( LASSO_BR_CODE, '<br>', $description );
		$categories      = $atts['categories'] ?? '';
		$category        = $atts['category'] ?? '';
		$limit           = $atts['limit'] ?? '';
		$badge           = $atts['badge'] ?? '';
		$show_toc        = $atts['show_toc'] ?? '';
		$hide_header     = $atts['hide_header'] ?? 'false';
		$latest          = $atts['latest'] ?? 'false';
		$demo            = $atts['demo'] ?? 'false';
		$brag            = isset( $atts['brag'] ) ? true : null;
		$width           = $atts['width'] ?? '';
		$primary_url     = $atts['primary_url'] ?? '';
		$primary_text    = $atts['primary_text'] ?? '';
		$secondary_text  = $atts['secondary_text'] ?? '';
		$image_url       = $atts['image_url'] ?? '';
		$image_alt       = $atts['image_alt'] ?? '';
		$disclosure_text = $atts['disclosure_text'] ?? '';
		$rating          = $atts['rating'] ?? '';
		$fields          = $atts['fields'] ?? '';
		$fields_value    = $atts['value'] ?? '';
		$field           = $atts['field'] ?? '';
		$pros            = $atts['pros'] ?? '';
		$pros            = str_replace( '-br-', "\n", $pros );
		$pros_label      = $atts['pros_label'] ?? 'Pros';
		$cons            = $atts['cons'] ?? '';
		$cons            = str_replace( '-br-', "\n", $cons );
		$cons_label      = $atts['cons_label'] ?? 'Cons';
		$button_type     = isset( $atts['button_type'] ) && ( LASSO_SECONDARY_TYPE_BTN === $atts['button_type'] ) ? LASSO_SECONDARY_TYPE_BTN : LASSO_PRIMARY_TYPE_BTN;
		$secondary_url   = ( 'button' === $type && LASSO_SECONDARY_TYPE_BTN === $button_type )
			? ( $atts['primary_url'] ?? '' )
			: ( $atts['secondary_url'] ?? '' );
		$show_pros_cons  = $atts['show_pros_cons'] ?? '';
		$basis_price     = $atts['basis_price'] ?? '';
		$tracking_id     = $atts['tracking_id'] ?? '';
		$grid_disclosure = $atts['disclosure'] ?? '';
		$amazon_url      = trim( $atts['amazon_url'] ?? '' );
		$geni_url        = trim( $atts['geni_url'] ?? '' );
		$is_from_widget  = isset( $atts['origin'] ) && 'widget' === $atts['origin'] ? true : false;

		$sitestripe       = 'true' === ( $atts['sitestripe'] ?? '' );
		$sitestripe_style = $atts['sitestripe_style'] ?? 'width:240px;height:400px;';

		$show_disclosure_grid = Lasso_Setting::lasso_get_setting( 'show_disclosure_grid', false );
		$grid_disclosure      = 'enable' === $grid_disclosure || $show_disclosure_grid ? true : false;

		// ? slug should be prioritized over id if both are present
		if ( $slug ) {
			$tmp_post_id = Lasso_DB::get_post_id_by_slug( $slug );
			$post_id     = $tmp_post_id ? $tmp_post_id : $post_id;
		}

		if ( ! $post_id && $prompt ) {
			$chatgpt_response = self::get_chatgpt_response( $prompt, $format );
			$html             = Lasso_Helper::include_with_variables(
				LASSO_PLUGIN_PATH . '/admin/views/displays/aitext.php',
				array(
					'chatgpt_response' => $chatgpt_response,
				)
			);
			return $html;
		}

		if ( empty( $anchor_id ) ) {
			$suffix    = empty( $link_id ) ? uniqid() : $link_id;
			$unique_id = empty( $category ) ? $post_id : $category;
			$anchor_id = 'lasso-anchor-id-' . $unique_id . '-' . $suffix;
		}

		// ? Compact attribute only support for Grid view
		$is_show_description = true;
		$is_show_disclosure  = $is_show_description;
		$is_show_fields      = $is_show_disclosure;
		if ( Lasso_Helper::compare_string( Lasso_Setting::DISPLAY_TYPE_GRID, $type ) && ! empty( $atts['compact'] ) ) {
			$compact             = Lasso_Helper::cast_to_boolean( $atts['compact'] );
			$is_show_description = ! $compact;
			$is_show_disclosure  = $is_show_description;
			$is_show_fields      = $is_show_disclosure;
		}

		if ( 'gallery' === $type ) {
			$columns = isset( $atts['columns'] ) && in_array( $atts['columns'], array( '2', '3', '4', '5' ) ) ? $atts['columns'] : Lasso_Setting_Enum::GALLERY_COLUMNS_DEFAULT; // phpcs:ignore
		} else {
			$columns = isset( $atts['columns'] ) && in_array( $atts['columns'], array( '1', '2', '3', '4', '5' ) ) ? $atts['columns'] : Lasso_Setting_Enum::GRID_COLUMNS_DEFAULT; // phpcs:ignore
		}

		$settings = Lasso_Setting::lasso_get_settings();
		$lasso_db = new Lasso_DB();

		$sitestripe = $settings['keep_site_stripe_ui'] ? $sitestripe : false;

		// ? fix raw amazon url in post content, not in gutenberg editor
		if ( ! $post_id && $amazon_url ) {
			$product_id = Lasso_Amazon_Api::get_product_id_country_by_url( $amazon_url );
			$post_id    = $lasso_db->get_lasso_id_by_product_id_and_type( $product_id, Lasso_Amazon_Api::PRODUCT_TYPE, $amazon_url );
		}

		// ? fix raw geni.us url in post content, not in gutenberg editor
		if ( ! $post_id && $geni_url ) {
			$temp_lasso_id = $lasso_db->get_lasso_id_by_geni_url( $geni_url );
			$post_id       = $temp_lasso_id ? $temp_lasso_id : $post_id;
		}

		$enable_brag_mode    = $brag ?? $settings['enable_brag_mode'];
		$lasso_affiliate_url = Lasso_Helper::add_params_to_url( $settings['lasso_affiliate_URL'], array( 'utm_source' => 'brag' ) );
		$lasso_url           = Lasso_Affiliate_Link::get_lasso_url( $post_id );

		// ? fix custom attributes for the same (lasso_id) shortcodes
		$lasso_url = Lasso_Affiliate_Link::clone_lasso_url_obj( $lasso_url );

		// ? Apply custom tracking id to amazon urls
		if ( $tracking_id && Lasso_Amazon_Api::is_amazon_url( $lasso_url->public_link ) ) {
			$lasso_url->public_link = Lasso_Amazon_Api::get_amazon_product_url( $lasso_url->public_link, true, false, $tracking_id );
			if ( $lasso_url->display->secondary_url ) {
				$lasso_url->display->secondary_url = Lasso_Amazon_Api::get_amazon_product_url( $lasso_url->display->secondary_url, true, false, $tracking_id );
			}
		}

		// ? Toolbox style display box
		if ( '' !== $category || '' !== $categories ) {
			if ( '' !== $categories ) {
				$category = $categories;
			}

			ob_start();
			if ( 'list' === $type ) {
				require LASSO_PLUGIN_PATH . '/admin/views/displays/list.php';
			} elseif ( 'gallery' === $type ) {
				require LASSO_PLUGIN_PATH . '/admin/views/displays/gallery.php';
			} else {
				require LASSO_PLUGIN_PATH . '/admin/views/displays/grid.php';
			}
			Lasso_Cache_Per_Process::get_instance()->un_set( self::OBJECT_KEY );
			return ob_get_clean();
		} elseif ( 'image' === $style || 'image' === $type ) {
			// ? Image style
			$template = '/admin/views/displays/image.php';

		} elseif ( 'button' === $type ) {
			// ? Button style
			$template = '/admin/views/displays/button.php';
		} elseif ( Lasso_Helper::compare_string( Lasso_Setting_Enum::DISPLAY_TYPE_TABLE, $type ) ) {
			$template = '/admin/views/displays/table.php';
		} elseif ( $fields && in_array( $fields_value, self::AAWP_SUPPORT_FIELDS_VALUE, true ) ) {
			$template = '/admin/views/displays/aawp/fields.php';
		} else {
			// ? Default single-style display box
			$template = '/admin/views/displays/single.php';
		}

		// ? aawp shortcode with link attribute
		if ( $lasso_url->lasso_id > 0 && (
				( $link && $lasso_url->amazon->amazon_id === $link )
				|| ( $identifier && $lasso_url->amazon->amazon_id === $identifier && ! isset( $atts['align'] ) )
				|| 'link' === $type
				|| ( $lasso_url->amazon->amazon_id && ! $lasso_url->amazon->default_product_name && ! $lasso_url->amazon->base_url )
			)
		) {
			$lasso_url->name = $title ? $title : $lasso_url->name;
			Lasso_Cache_Per_Process::get_instance()->un_set( self::OBJECT_KEY );
			return '<a href="' . $lasso_url->public_link . '" target="' . $lasso_url->html_attribute->target . '" ' . $lasso_url->html_attribute->rel . ' data-lasso-id="' . $lasso_url->lasso_id . '" data-lasso-name="' . $lasso_url->name . '">' . html_entity_decode( $lasso_url->name ) . '</a>';
		}

		if ( $lasso_url->lasso_id > 0 || 'true' === $demo || Lasso_Helper::compare_string( Lasso_Setting_Enum::DISPLAY_TYPE_TABLE, $type ) ) {
			ob_start();
			require LASSO_PLUGIN_PATH . $template;
			Lasso_Cache_Per_Process::get_instance()->un_set( self::OBJECT_KEY );
			return ob_get_clean();
		}

		Lasso_Cache_Per_Process::get_instance()->un_set( self::OBJECT_KEY );
		return '';
	}

	/**
	 * Earnist main shortcode backwards compatability
	 *
	 * @param array $atts Attributes of shortcode.
	 */
	public function earnist_main_shortcode( $atts ) {
		$lasso_db  = new Lasso_DB();
		$legacy_id = $atts['id'] ?? '';

		if ( '' === $legacy_id ) {
			return '';
		}

		$result     = $lasso_db->earnist_shortcode_query( $legacy_id );
		$atts['id'] = isset( $result[0]->lasso_id ) ? $result[0]->lasso_id : null;

		return count( $result ) > 0 ? $this->lasso_core_shortcode( $atts ) : '';
	}

	/**
	 * Earnist link shortcode backwards compatability
	 *
	 * @param array  $atts     Attributes of shortcode.
	 * @param string $content  Content of shortcode. Default to null.
	 */
	public function earnist_link_shortcode( $atts, $content = null ) {
		$lasso_db  = new Lasso_DB();
		$legacy_id = $atts['id'] ?? '';
		$result    = $lasso_db->earnist_shortcode_query( $legacy_id );

		// ? don't have any results - we're a new import
		if ( ! isset( $result[0] ) ) {
			$post_id = $legacy_id;
		}

		$post_id = $result[0]->lasso_id ?? false;

		if ( ! $post_id ) {
			return '';
		}

		$amzn    = new Lasso_Amazon_Api();
		$product = $amzn->get_amazon_product_by_id( $post_id );
		$url     = isset( $product['url'] ) && $product['url'] ? $product['url'] : get_the_permalink( $post_id );
		$link    = '<a rel="nofollow" target="_BLANK" href="' . $url . '">' . $content . '</a>';

		return $link;
	}

	/**
	 * Thirsty Affiliate shortcode
	 *
	 * @param array  $atts Attributes of shortcode.
	 * @param string $content  Content of shortcode. Default to null.
	 */
	public function thirstylink_shortcode( $atts, $content = null ) {
		$ids    = $atts['ids'] ?? '';
		$linkid = $atts['linkid'] ?? '';

		if ( '' === $ids && '' === $linkid ) {
			return '';
		}

		// ? get the link ID
		if ( '' === $linkid ) {
			$ids     = isset( $atts['ids'] ) ? array_map( 'intval', explode( ',', $ids ) ) : array();
			$count   = count( $ids );
			$key     = 1 === $count ? 0 : wp_rand( 0, $count - 1 );
			$link_id = $ids[ $key ];
		} else {
			$link_id = $linkid;
		}
		$link_id   = (int) $link_id;
		$lasso_url = Lasso_Affiliate_Link::get_lasso_url( $link_id );

		if ( $lasso_url->lasso_id > 0 ) { // ? the post was imported to Lasso
			$atts['id'] = $link_id;

			return '<a href="' . $lasso_url->public_link . '" data-lasso-id="' . $lasso_url->lasso_id . '" data-lasso-name="' . $lasso_url->name . '">' . $content . '</a>';
		} elseif ( $this->class_exists( 'ThirstyAffiliates' ) ) { // ? the post is not imported to Lasso, using default shortcode
			$thirsty_class     = new ThirstyAffiliates();
			$plugin_constants  = Lasso_Thirsty_Helpers_Plugin_Constants::get_instance( $thirsty_class );
			$helper_functions  = Lasso_Thirsty_Helpers_Helper_Functions::get_instance( $thirsty_class, $plugin_constants );
			$thirsty_shortcode = new Lasso_Thirsty_Models_Shortcode( $thirsty_class, $plugin_constants, $helper_functions );

			return $thirsty_shortcode->thirstylink_shortcode( $atts, $content );
		}

		// ? the plugin is deactivated, using Lasso to support the old shortcode
		$redirect_url = get_post_meta( $link_id, '_ta_destination_url', true );
		$title        = get_the_title( $link_id );

		$content = '<a href="' . $redirect_url . '" target="_blank" rel="nofollow">' . $title . '</a>';

		return $content;
	}

	/**
	 * AAWP amazon shortcode
	 *
	 * @param array  $atts Attributes of shortcode.
	 * @param string $content  Content of shortcode. Default to null.
	 */
	public function aawp_amazon_shortcode( $atts, $content = null ) {
		$lasso_db         = new Lasso_DB();
		$lasso_amazon_api = new Lasso_Amazon_Api();

		$link   = $atts['link'] ?? '';
		$box    = $atts['box'] ?? '';
		$fields = $atts['fields'] ?? '';

		$amazon_id    = '';
		$title        = '';
		$redirect_url = '';

		// ? get Amazon product id
		if ( '' !== $link ) {
			$amazon_id = $link;
			$title     = $atts['title'] ?? $title;
			$title     = $atts['link_title'] ?? $title;
		} elseif ( '' !== $box ) {
			$amazon_id = $box;
			$title     = $atts['title'] ?? $title;
			$title     = $atts['link_title'] ?? $title;
			$title     = $atts['button_text'] ?? $title;
		} elseif ( '' !== $fields ) {
			$amazon_id = $fields;
		}

		// ? AAWP is installed
		if ( $this->class_exists( 'AAWP_Core' ) ) {
			$aawp_core = new AAWP_Core();

			return $aawp_core->render_shortcode( $atts, $content );
		}

		if ( '' === $amazon_id ) {
			return '';
		}

		$amazon_url  = Lasso_Amazon_Api::get_default_product_domain( $amazon_id );
		$url_details = $lasso_db->get_url_details_by_product_id( $amazon_id, Lasso_Amazon_Api::PRODUCT_TYPE, false, $amazon_url );
		if ( $url_details ) {
			$lasso_id   = $url_details->lasso_id;
			$atts['id'] = $lasso_id;

			// ? Amazon box: The content could be using as custom HTML description. Refer: https://getaawp.com/docs/article/product-boxes/
			if ( '' !== $box ) {
				$atts['description'] = $atts['description'] ?? $content;
			}

			return $this->lasso_core_shortcode( $atts );
		}

		$redirect_url = '' === $redirect_url ? $lasso_amazon_api->get_amazon_link_by_product_id( $amazon_id ) : $redirect_url;
		$redirect_url = $lasso_amazon_api->get_amazon_product_url( $redirect_url );
		$title        = '' === $title ? 'Amazon product' : $title;

		$content = '<a href="' . $redirect_url . '" target="_blank" rel="nofollow">' . $title . '</a>';

		return $content;
	}

	/**
	 * Override Easy Affiliate Link shortcode. Replace by Lasso link if imported.
	 *
	 * @param array  $atts    Shortcode attributes.
	 * @param string $content Shortcode content.
	 */
	public function easy_affiliate_link_shortcode( $atts, $content = null ) {
		$atts = shortcode_atts(
			array(
				'id'   => false,
				'text' => false,
			),
			$atts,
			'eafl_link'
		);

		$output = $content;
		$id     = intval( $atts['id'] );

		if ( $id ) {
			$post_type = get_post_type( $id );

			if ( EAFL_POST_TYPE === $post_type ) {
				return EAFL_Shortcode::link_shortcode( $atts, $content );
			} elseif ( LASSO_POST_TYPE === $post_type ) {
				$lasso_attrs = array(
					'id'    => $id,
					'type'  => 'link',
					'title' => $atts['text'] ? $atts['text'] : $content,
				);
				$link_output = $this->lasso_core_shortcode( $lasso_attrs );

				$output = '<span class="lasso">' . $link_output . '</span>';
			}
		}

		return $output;
	}

	/**
	 * Easyazon shortcode
	 *
	 * @param array  $atts    Attributes of shortcode.
	 * @param string $content Content.
	 */
	public function easyazon_link_shortcode( $atts, $content = null ) {
		$lasso_db         = new Lasso_DB();
		$lasso_amazon_api = new Lasso_Amazon_Api();

		$asin = $atts['asin'] ?? $atts['identifier'] ?? '';

		if ( empty( $asin ) ) {
			return '';
		}

		$product = $lasso_db->get_easyazon_product( $asin );
		if ( ! $product ) {
			return '';
		}

		$amazon_url  = Lasso_Amazon_Api::get_default_product_domain( $asin );
		$url_details = $lasso_db->get_url_details_by_product_id( $asin, Lasso_Amazon_Api::PRODUCT_TYPE, false, $amazon_url );
		if ( $url_details ) {
			$lasso_id   = $url_details->lasso_id;
			$atts['id'] = $lasso_id;

			return $this->lasso_core_shortcode( $atts );
		}

		if ( class_exists( 'EasyAzon_Components_Shortcodes_Link' ) ) {
			return EasyAzon_Components_Shortcodes_Link::shortcode( $atts, $content );
		}

		$product      = unserialize( $product->option_value ); // phpcs:ignore
		$title        = $content ? $content : $product['title'];
		$redirect_url = $product['url'];
		$redirect_url = $lasso_amazon_api->get_amazon_product_url( $redirect_url );

		$content = '<a href="' . $redirect_url . '" target="_blank" rel="nofollow">' . $title . '</a>';

		return $content;
	}

	/**
	 * Easyazon shortcode
	 *
	 * @param array  $atts    Attributes of shortcode.
	 * @param string $content Content.
	 */
	public function easyazon_image_shortcode( $atts, $content = null ) {
		$lasso_db = new Lasso_DB();

		$asin = $atts['asin'] ?? $atts['identifier'] ?? '';

		if ( empty( $asin ) ) {
			return '';
		}

		$product = $lasso_db->get_easyazon_product( $asin );
		if ( ! $product ) {
			return '';
		}

		$amazon_url  = Lasso_Amazon_Api::get_default_product_domain( $asin );
		$url_details = $lasso_db->get_url_details_by_product_id( $asin, Lasso_Amazon_Api::PRODUCT_TYPE, false, $amazon_url );
		if ( $url_details ) {
			$lasso_id     = $url_details->lasso_id;
			$atts['id']   = $lasso_id;
			$atts['type'] = 'image';

			unset( $atts['identifier'] );
			unset( $atts['asin'] );

			return $this->lasso_core_shortcode( $atts );
		}

		if ( class_exists( 'EasyAzon_Addition_Components_Shortcodes_Image' ) ) {
			return EasyAzon_Addition_Components_Shortcodes_Image::shortcode( $atts, $content );
		}

		$final_sc_content = array_map(
			function( $key, $value ) {
				return $key . '="' . $value . '"';
			},
			array_keys( $atts ),
			array_values( $atts )
		);

		$content = '[easyazon_image ' . implode( ' ', $final_sc_content ) . ']';

		return $content;
	}

	/**
	 * Easyazon shortcode
	 *
	 * @param array  $atts    Attributes of shortcode.
	 * @param string $content Content.
	 */
	public function easyazon_button_shortcode( $atts, $content = null ) {
		$lasso_db = new Lasso_DB();

		$asin = $atts['asin'] ?? $atts['identifier'] ?? '';

		if ( empty( $asin ) ) {
			return '';
		}

		$product = $lasso_db->get_easyazon_product( $asin );
		if ( ! $product ) {
			return '';
		}

		$amazon_url  = Lasso_Amazon_Api::get_default_product_domain( $asin );
		$url_details = $lasso_db->get_url_details_by_product_id( $asin, Lasso_Amazon_Api::PRODUCT_TYPE, false, $amazon_url );
		if ( $url_details ) {
			$lasso_id     = $url_details->lasso_id;
			$atts['id']   = $lasso_id;
			$atts['type'] = 'button';

			unset( $atts['identifier'] );
			unset( $atts['asin'] );

			return $this->lasso_core_shortcode( $atts );
		}

		if ( class_exists( 'EasyAzon_Addition_Components_Shortcodes_CTA' ) ) {
			return EasyAzon_Addition_Components_Shortcodes_CTA::shortcode( $atts, $content );
		}

		$final_sc_content = array_map(
			function( $key, $value ) {
				return $key . '="' . $value . '"';
			},
			array_keys( $atts ),
			array_values( $atts )
		);

		$content = '[easyazon_cta ' . implode( ' ', $final_sc_content ) . ']';

		return $content;
	}

	/**
	 * Easyazon shortcode
	 *
	 * @param array  $atts    Attributes of shortcode.
	 * @param string $content Content.
	 */
	public function easyazon_box_shortcode( $atts, $content = null ) {
		$lasso_db = new Lasso_DB();

		$asin = $atts['asin'] ?? $atts['identifier'] ?? '';

		if ( empty( $asin ) ) {
			return '';
		}

		$product = $lasso_db->get_easyazon_product( $asin );
		if ( ! $product ) {
			return '';
		}

		$amazon_url  = Lasso_Amazon_Api::get_default_product_domain( $asin );
		$url_details = $lasso_db->get_url_details_by_product_id( $asin, Lasso_Amazon_Api::PRODUCT_TYPE, false, $amazon_url );
		if ( $url_details ) {
			$lasso_id   = $url_details->lasso_id;
			$atts['id'] = $lasso_id;

			unset( $atts['identifier'] );
			unset( $atts['asin'] );

			return $this->lasso_core_shortcode( $atts );
		}

		if ( class_exists( 'EasyAzon_Addition_Components_Shortcodes_InfoBlock' ) ) {
			return EasyAzon_Addition_Components_Shortcodes_InfoBlock::shortcode( $atts, $content );
		}

		$final_sc_content = array_map(
			function( $key, $value ) {
				return $key . '="' . $value . '"';
			},
			array_keys( $atts ),
			array_values( $atts )
		);

		$content = '[easyazon_infoblock ' . implode( ' ', $final_sc_content ) . ']';

		return $content;
	}

	/**
	 * AmaLinks Pro shortcode
	 *
	 * @param array  $atts Attributes of shortcode.
	 * @param string $content Content of shortcode.
	 */
	public function amalinkspro_shortcode( $atts, $content = null ) {
		$lasso_amazon_api = new Lasso_Amazon_Api();
		$lasso_db         = new Lasso_DB();

		// ? after importing to Lasso
		$amazon_link       = $atts['apilink'] ?? '';
		$amazon_link       = $lasso_amazon_api->get_amazon_product_url( $amazon_link );
		$asin              = $lasso_amazon_api->get_product_id_by_url( $amazon_link );
		$amazon_product_db = $lasso_amazon_api->get_amazon_product_from_db( $asin );
		$amazon_url        = Lasso_Amazon_Api::get_default_product_domain( $asin );
		$url_details       = $lasso_db->get_url_details_by_product_id( $asin, Lasso_Amazon_Api::PRODUCT_TYPE, false, $amazon_url );

		if ( $amazon_product_db && $url_details ) {
			$lasso_id   = $url_details->lasso_id;
			$atts['id'] = $lasso_id;

			return $this->lasso_core_shortcode( $atts );
		}

		// ? use default AmaLinks Pro shortcode function
		if ( $this->class_exists( 'Ama_Links_Pro' ) ) {
			$amalinkpro        = new Ama_Links_Pro();
			$amalinkpro_public = new Ama_Links_Pro_Public( $amalinkpro->get_Ama_Links_Pro(), $amalinkpro->get_version() );
			$atts['apilink']   = $amazon_link;

			return $amalinkpro_public->amalinkspro_shortcode_functions( $atts, $content );
		}

		return $content;
	}

	/**
	 * Check whether a class exists or not
	 *
	 * @param string $class_name Class name.
	 */
	public function class_exists( $class_name ) {
		return class_exists( $class_name );
	}

	/**
	 * Replace other plugin shortcode with Lasso shortcode
	 *
	 * @param string $content Post content.
	 */
	public function lasso_replace_other_plugin_shortcode_display( $content ) {
		global $post;

		$lasso_db     = new Lasso_DB();
		$lasso_helper = new Lasso_Helper();

		$competitor_shortcodes = self::COMPETITOR_SHORTCODES;

		$new_content = str_replace( '[lasso ref=">[lasso ref="', '">[lasso ref="', $content );
		$content     = ! empty( $new_content ) ? $new_content : $content;

		$post_type = get_post_type( $post );
		$cpt       = Lasso_Helper::get_cpt_support();
		if ( in_array( $post_type, $cpt, true ) ) { // ? Only supported post types
			preg_match_all(
				'/' . get_shortcode_regex() . '/s',
				$content,
				$matches,
				PREG_SET_ORDER
			);

			foreach ( $matches as $key => $shortcode_arr ) {
				$shortcode       = $shortcode_arr[0];
				$shortcode_name  = $shortcode_arr[2];
				$content_between = $shortcode_arr[5];
				$attributes      = $lasso_helper->get_attributes( $shortcode );

				$type     = $attributes['type'] ?? '';
				$category = $attributes['category'] ?? '';

				$is_competitor_shortcode   = in_array( $shortcode_name, $competitor_shortcodes, true );
				$is_lasso_single_shortcode = ! in_array( $type, array( 'grid', 'list', 'gallery', 'table' ), true );
				$lasso_cat                 = get_term_by( 'slug', $category, LASSO_CATEGORY );

				// ? fix lasso grid/list/gallery shortcode is replaced
				if ( ! $is_lasso_single_shortcode && $is_competitor_shortcode && $category && $lasso_cat ) {
					$new_content = $this->replace_shortcode( $content, $shortcode, $attributes, '' );
					$content     = ! empty( $new_content ) ? $new_content : $content;
				}

				if ( $is_competitor_shortcode ) {
					$aawp_table_id = 0;
					if ( in_array( $shortcode_name, array( 'aawp', 'amazon' ), true ) && isset( $attributes['table'] ) ) {
						$aawp_table_id = intval( $attributes['table'] );
					}

					if ( $aawp_table_id ) {
						$is_post_imported_into_lasso = Lasso_Import::is_post_imported_into_lasso( $aawp_table_id, 'aawp_table' );

						if ( $is_post_imported_into_lasso ) {
							$aawp_table_revert_item = ( new Revert() )->get_revert_data( $aawp_table_id, 'aawp', '[amazon table="' . $aawp_table_id . '"]' );
							$attributes['id']       = $aawp_table_revert_item->get_lasso_id(); // ? Lasso table attribute
							$attributes['type']     = 'table'; // ? Lasso table attribute

							$new_content = $this->replace_shortcode( $content, $shortcode, $attributes, $content_between );
							$content     = ! empty( $new_content ) ? $new_content : $content;
						}
						continue;
					}

					$lasso_id = intval( $attributes['id'] ?? 0 );

					// ? AAWP plugin
					$amazon_id = $attributes['link'] ?? $attributes['box'] ?? $attributes['fields'] ?? '';

					if ( 'amalinkspro' === $shortcode_name ) {
						$asin        = $attributes['asin'] ?? '';
						$amazon_link = $attributes['apilink'] ?? '';
						$amazon_id   = Lasso_Amazon_Api::get_product_id_by_url( $amazon_link );
						$amazon_id   = empty( $amazon_id ) ? $asin : $amazon_id;
					}

					if ( in_array( $shortcode_name, array( 'easyazon_link', 'easyazon-link', 'simpleazon-link' ), true ) ) {
						$amazon_id          = $attributes['identifier'] ?? $attributes['asin'] ?? '';
						$attributes['type'] = 'link';

						if ( isset( $attributes['keywords'] ) && ! $attributes['keywords'] && $content_between ) {
							$attributes['keywords'] = $content_between;
						}
					}

					if ( in_array( $shortcode_name, array( 'easyazon_image', 'easyazon-image', 'easyazon-image-link', 'simpleazon-image' ), true ) ) {
						$amazon_id          = $attributes['identifier'] ?? $attributes['asin'] ?? '';
						$attributes['type'] = 'image';
					}

					if ( in_array( $shortcode_name, array( 'easyazon_cta', 'easyazon-cta' ), true ) ) {
						$amazon_id          = $attributes['identifier'] ?? $attributes['asin'] ?? '';
						$attributes['type'] = 'button';
					}

					if ( in_array( $shortcode_name, array( 'easyazon_block', 'easyazon-block', 'easyazon_infoblock' ), true ) ) {
						$amazon_id = $attributes['identifier'] ?? $attributes['asin'] ?? '';
					}

					if ( empty( $amazon_id ) ) {
						continue;
					}

					$sql      = '
						select lasso_id, wpp.post_name
						from ' . Model::get_wp_table_name( LASSO_REVERT_DB ) . ' as lr
						left join ' . Model::get_wp_table_name( 'posts' ) . ' as wpp
							on wpp.ID = lr.lasso_id
						where wpp.post_type = "' . LASSO_POST_TYPE . '" 
							and wpp.post_status = "publish" 
							and lr.old_uri = "' . $amazon_id . '"
					';
					$result   = Model::get_row( $sql );
					$lasso_id = intval( $result->lasso_id ?? 0 );
					if ( 0 === $lasso_id ) {
						$amazon_url    = Lasso_Amazon_Api::get_default_product_domain( $amazon_id );
						$lasso_post_id = $lasso_db->get_lasso_id_by_product_id_and_type( $amazon_id, Lasso_Amazon_Api::PRODUCT_TYPE, $amazon_url );
						$lasso_id      = $lasso_post_id ? $lasso_post_id : $lasso_id;
					}

					$lasso_url                   = Lasso_Affiliate_Link::get_lasso_url( $lasso_id );
					$is_post_imported_into_lasso = Lasso_Import::is_post_imported_into_lasso( $lasso_url->lasso_id );
					if ( $lasso_url->lasso_id > 0 && $is_post_imported_into_lasso ) {
						$attributes['id']  = $lasso_url->lasso_id;
						$attributes['ref'] = $lasso_url->slug;

						$new_content = $this->replace_shortcode( $content, $shortcode, $attributes, $content_between );
						$content     = ! empty( $new_content ) ? $new_content : $content;
					}
				} elseif ( in_array( $shortcode_name, array( 'thirstylink' ), true ) ) {
					$lasso_id                    = intval( $attributes['ids'] ?? 0 );
					$lasso_url                   = Lasso_Affiliate_Link::get_lasso_url( $lasso_id );
					$is_post_imported_into_lasso = Lasso_Import::is_post_imported_into_lasso( $lasso_url->lasso_id );
					if ( $lasso_url->lasso_id > 0 && $is_post_imported_into_lasso ) {
						$attributes['id']  = $lasso_url->lasso_id;
						$attributes['ref'] = $lasso_url->slug;

						$new_content = $this->replace_shortcode( $content, $shortcode, $attributes, $content_between );
						$content     = ! empty( $new_content ) ? $new_content : $content;
					}
				} elseif ( 'lasso' === $shortcode_name ) {
					$lasso_id = intval( $attributes['id'] ?? 0 );

					// ? Set flag to know we using shortcode display
					Lasso_Cache_Per_Process::get_instance()->set_cache( self::OBJECT_KEY, true );
					$lasso_url = Lasso_Affiliate_Link::get_lasso_url( $lasso_id );

					// ? old shortcode is AAWP table plugin shortcode and imported to Lasso
					$aawp_table_id = 0;
					if ( isset( $attributes['table'] ) && isset( $attributes['type'] ) && 'table' === $attributes['type'] && isset( $attributes['id'] ) ) {
						$aawp_table_id = intval( $attributes['table'] );
					}

					$is_post_imported_into_lasso = $aawp_table_id
						? Lasso_Import::is_post_imported_into_lasso( $aawp_table_id, 'aawp_table' )
						: Lasso_Import::is_post_imported_into_lasso( $lasso_url->lasso_id );

					$should_covert_lasso_shortcode_to_original_shortcode = false;
					// ? fix lasso shortcode of post that is reverted
					if ( $aawp_table_id ) {
						$should_covert_lasso_shortcode_to_original_shortcode = ! $is_post_imported_into_lasso ? true : false;
					} elseif ( $is_lasso_single_shortcode ) { // ? don't replace lasso shortcode type: grid, list, gallery because they don't have any lasso id
						$should_covert_lasso_shortcode_to_original_shortcode = 0 === $lasso_url->lasso_id || ! $is_post_imported_into_lasso ? true : false;
					}

					if ( $should_covert_lasso_shortcode_to_original_shortcode ) {
						$replace_with = self::covert_lasso_shortcode_to_original_shortcode( $shortcode, array(), '', $is_post_imported_into_lasso, true );

						$new_content = str_replace( $shortcode, $replace_with, $content );
						$content     = ! empty( $new_content ) && ! empty( $replace_with ) ? $new_content : $content;
					}
				}
			}
		}

		$content = str_replace( 'href=""', 'href="#"', $content );

		if ( Lasso_Helper::is_wp_elementor_plugin_actived() ) {
			return $content;
		}

		// ? TA plugin is deactivated
		$html = new simple_html_dom();
		$html->load( $content, true, false );

		$ta_tags = $html->find( 'ta' ); // ? Find ta tags in the html (ThirstyAffiliate links)

		// ? TA tags
		$is_ta_deactivated = ! isset( $GLOBALS['thirstyaffiliates'] ) || ! class_exists( 'ThirstyAffiliates' ) || ! function_exists( 'ThirstyAffiliates' );
		foreach ( $ta_tags as $key => $ta ) {
			$tag_id   = $ta->linkid ?? '';
			$tag_href = $ta->href ?? '';

			$lasso_url = Lasso_Affiliate_Link::get_lasso_url( $tag_id );
			$lasso_id  = $lasso_url->lasso_id;

			if ( $lasso_id > 0 ) {
				$rel_attr        = $lasso_url->enable_nofollow ? 'rel="nofollow"' : '';
				$target_attr     = 'target="' . $lasso_url->html_attribute->target . '"';
				$lasso_id_attr   = 'data-lasso-id="' . $lasso_id . '"';
				$lasso_name_attr = 'data-lasso-name="' . $lasso_url->name . '"';
				$href_attr       = 'href="' . $lasso_url->public_link . '"';
				$ta->outertext   = '<a ' . $href_attr . ' ' . $lasso_id_attr . ' ' . $lasso_name_attr . ' ' . $target_attr . ' ' . $rel_attr . '>' . $ta->innertext . '</a>';
			} elseif ( $is_ta_deactivated ) {
				// ? not lasso post -> convert <ta> tags to <a> tags
				$target_attr   = 'target="_blank"';
				$lasso_id_attr = 'data-lasso-id="' . $tag_id . '"';
				$href_attr     = 'href="' . $tag_href . '"';
				$ta->outertext = '<a ' . $href_attr . ' ' . $lasso_id_attr . ' ' . $target_attr . '>' . $ta->innertext . '</a>';
			}
		}

		$content = (string) $html;

		return trim( $content );
	}

	/**
	 * Replace other shortcode with Lasso shortcode and return the content.
	 *
	 * @param string $content          Post content.
	 * @param string $shortcode        Other plugin shortcode.
	 * @param array  $lasso_attributes Array attributes of Lasso shortcode.
	 * @param array  $content_between  Shortcode content. Default to empty.
	 *
	 * @return string $content New post content.
	 */
	private function replace_shortcode( $content, $shortcode, $lasso_attributes, $content_between = '' ) {
		$final_sc_content = array_map(
			function( $key, $value ) {
				return $key . '="' . $value . '"';
			},
			array_keys( $lasso_attributes ),
			array_values( $lasso_attributes )
		);
		$lasso_shortcode  = '[lasso ' . implode( ' ', $final_sc_content ) . ']';

		$new_content = str_replace( $shortcode, $lasso_shortcode, $content );
		$content     = ! empty( $new_content ) ? $new_content : $content;

		return $content;
	}

	/**
	 * Hook to render inline css to header
	 */
	public function lasso_hook_css_inline() {
		Lasso_Helper::render_css_inline();
	}

	/**
	 * Hook to Jetpack plugin, so we can return a original image
	 */
	public function lasso_jetpack_skip_image() {
		return true;
	}

	/**
	 * Hook to render inline Lasso GA script
	 */
	public function lasso_hook_amp_post_template_footer() {
		Lasso_Helper::render_lasso_ga_js();
	}

	/**
	 * Handle Lasso shortcode attributes if import from another plugins.
	 *
	 * @param array $atts Lasso shortcode attributes.
	 */
	public function lasso_shortcode_attrs_filter( $atts ) {
		$post_id = $atts['id'] ?? '';
		$link_id = $atts['link_id'] ?? '';

		// ? Get custom description from plugin if existed
		if ( $post_id && $link_id ) {
			// ? Priority lasso shortcode custom description
			$atts['description'] = $atts['description'] ?? '';

			if ( ! $atts['description'] ) {
				$link_location = new Lasso_Link_Location( $link_id );

				if ( ! $link_location->get_id() ) {
					return $atts;
				}

				// ? Get custom description from AAWP shortcode box
				$link_origin_slug = $link_location->get_original_link_slug();

				// ? Amazon box
				if ( ( false !== strpos( $link_origin_slug, '[amazon ' ) || false !== strpos( $link_origin_slug, '[aawp ' ) ) && false !== strpos( $link_origin_slug, 'box' ) ) {
					// ? Amazon box: The content could be using as custom HTML description. Refer: https://getaawp.com/docs/article/product-boxes/
					$aawp_shortcode_regex = get_shortcode_regex( array( 'amazon', 'aawp' ) );
					if ( ! $atts['description'] && preg_match_all( '~' . $aawp_shortcode_regex . '~s', $link_origin_slug, $matches ) && array_key_exists( 2, $matches ) ) {
						$shortcode_content = $matches[5][0] ?? '';
						if ( $shortcode_content ) {
							$atts['description'] = $shortcode_content;
						}
					}
				}
			}
		}

		return $atts;
	}

	/**
	 * Convert Lasso shortcodes to original shortcodes (AAWP, EasyAzon,...)
	 *
	 * @param string    $lasso_shortcode Lasso shortcode.
	 * @param array     $custom_attributes Custom attributes will be added to the shortcode. Default to empty array.
	 * @param string    $current_shortcode Current shortcode. Default to empty.
	 * @param null|bool $is_post_imported_into_lasso Is post imported into Lasso. Default to null.
	 * @param bool      $is_rendering_content Is rendering content. Default to false.
	 */
	public static function covert_lasso_shortcode_to_original_shortcode( $lasso_shortcode, $custom_attributes = array(), $current_shortcode = '', $is_post_imported_into_lasso = null, $is_rendering_content = false ) {
		$lasso_helper = new Lasso_Helper();

		$shortcode_list = Lasso_Cron::SHORTCODE_LIST;
		$pattern        = get_shortcode_regex( $shortcode_list );

		if ( preg_match_all( '~' . $pattern . '~s', $lasso_shortcode, $matches ) && array_key_exists( 2, $matches ) ) {
			$is_shortcode_encode     = strpos( $lasso_shortcode, '&quot;' ) !== false;
			$is_shortcode_addslashes = strpos( $lasso_shortcode, '\u0022' ) !== false;

			$link_slug = $is_shortcode_addslashes ? $lasso_shortcode : stripcslashes( $lasso_shortcode );
			$link_slug = $is_shortcode_encode ? html_entity_decode( $link_slug ) : $link_slug;

			$shortcode_type  = $matches[2][0] ?? 'lasso';
			$content_between = $matches[5][0] ?? '';
			$attributes      = $lasso_helper->get_attributes( $link_slug );

			$id        = $attributes['id'] ?? '';
			$ids       = $attributes['ids'] ?? '';
			$type      = $attributes['type'] ?? '';
			$src       = $attributes['src'] ?? '';
			$key       = $attributes['key'] ?? '';
			$align     = $attributes['align'] ?? '';
			$tag       = $attributes['tag'] ?? '';
			$fields    = $attributes['fields'] ?? '';
			$link_id   = $attributes['link_id'] ?? '';
			$asin      = $attributes['asin'] ?? '';
			$look      = $attributes['look'] ?? '';
			$classname = $attributes['classname'] ?? '';
			$keywords  = $custom_attributes['keywords'] ?? '';
			// ? old shortcode is AAWP table plugin shortcode and imported to Lasso
			$aawp_table_id = 0;
			if ( isset( $attributes['table'] ) && in_array( $shortcode_type, array( 'aawp', 'amazon', 'lasso' ), true ) ) {
				$aawp_table_id = intval( $attributes['table'] );
			}

			// ? Prevent duplicate query when the variable $is_post_imported_into_lasso is setted.
			$is_post_imported_into_lasso = null !== $is_post_imported_into_lasso ? $is_post_imported_into_lasso : Lasso_Import::is_post_imported_into_lasso( $id );

			$content_between = str_replace( "\n", '', $content_between );
			$content_between = str_replace( "\r", '', $content_between );
			$content_between = preg_replace( '/\s+/', ' ', $content_between );

			if ( 'lasso' === $shortcode_type && ! $is_post_imported_into_lasso ) {
				// ? AAWP plugin
				// ? Attribute fields = hide is Lasso's fields attribute, It is not AAWP
				$aawp_amazon_id = $attributes['link'] ?? $attributes['box'] ?? $attributes['fields'] ?? '';
				if ( ( $aawp_amazon_id || $aawp_table_id ) && 'hide' !== $fields ) {
					$shortcode_type = 'amazon';
				}

				// ? EasyAzon plugin
				$easyazon_amazon_id = $attributes['identifier'] ?? $attributes['asin'] ?? '';
				if ( ! $easyazon_amazon_id && $src && $tag ) {
					$amz = Lasso_Helper::get_easyazon_product_by_image_url( $src );
					if ( $amz ) {
						$attributes['identifier'] = $amz['identifier'];
						$easyazon_amazon_id       = $amz['identifier'];
						$type                     = 'image';
					}
					if ( ! $easyazon_amazon_id ) {
						$lasso_link_location = new Lasso_Link_Location( $attributes['link_id'] );
						$easyazon_amazon_id  = $lasso_link_location->get_product_id();
					}
				}
				if ( $easyazon_amazon_id ) {
					if ( $content_between || 'link' === $type ) {
						$shortcode_type  = 'easyazon_link';
						$content_between = $content_between ? $content_between : get_the_title( $id );
					} elseif ( $src || 'image' === $type ) {
						$shortcode_type = 'easyazon_image';
					} elseif ( $key || 'button' === $type ) {
						$shortcode_type = 'easyazon_cta';
					} elseif ( ( ! $content_between && $align ) || 'single' === $type ) {
						$shortcode_type = 'easyazon_infoblock';
					} else {
						$shortcode_type  = 'easyazon_link';
						$content_between = $content_between ? $content_between : get_the_title( $id );
					}
				}

				// ? ThirstyAffiliates plugin
				if ( $ids ) {
					$shortcode_type = 'thirstylink';
				}

				$apilink = $attributes['apilink'] ?? '';

				// ? AAWP
				if ( $look && ( $asin || $aawp_table_id ) ) {
					if ( $is_rendering_content ) {
						$shortcode_type  = 'aawp';
						$content_between = null;
					} else {
						$attributes['className'] = $classname;

						if ( $link_id && ! $classname ) {
							$lasso_class             = Lasso_Enum::LASSO_LL_ATTR . '-' . $link_id;
							$attributes['className'] = $lasso_class;
						}

						unset( $attributes['ref'] );
						unset( $attributes['rel'] );
						unset( $attributes['link_id'] );
						unset( $attributes['id'] );
						unset( $attributes['classname'] );
						unset( $attributes['box'] );

						return '<!-- wp:aawp/aawp-block ' . wp_json_encode( $attributes ) . ' /-->';
					}
				} elseif ( $asin || $apilink ) { // ? AmaLinkPro plugin
					$lasso_amazon_api = new Lasso_Amazon_Api();

					$shortcode_type    = 'amalinkspro';
					$ama_product_id    = Lasso_Amazon_Api::get_product_id_by_url( $apilink );
					$amazon_product_db = $lasso_amazon_api->get_amazon_product_from_db( $ama_product_id );
					$content_between   = $amazon_product_db['default_product_name'] ?? 'Buy on Amazon';
				}
			}

			if ( $keywords && 'easyazon_link' === $shortcode_type ) {
				$shortcode_type  = 'easyazon_link';
				$content_between = $keywords;
			}

			if ( 'amalinkspro' === $shortcode_type && $attributes['apilink'] ?? '' ) {
				$attributes['apilink'] = Lasso_Amazon_Api::get_amazon_product_url( $attributes['apilink'] );
			}

			// ? remove lasso attributes if it is not Lasso shortcode
			if ( 'lasso' !== $shortcode_type ) {
				unset( $attributes['id'] );
				if ( $aawp_table_id ) {
					unset( $attributes['type'] );
				}
			} elseif ( $current_shortcode ) {
				return $current_shortcode;
			}

			$attributes = array_merge( $attributes, $custom_attributes );

			$final_sc_content = array_map(
				function( $key, $value ) {
					return $key . '="' . $value . '"';
				},
				array_keys( $attributes ),
				array_values( $attributes )
			);

			$shortcode_start = '[' . $shortcode_type . ' ' . implode( ' ', $final_sc_content ) . ']';
			$shortcode_end   = '[/' . $shortcode_type . ']';
			if ( $content_between ) {
				$lasso_shortcode = $shortcode_start . $content_between . $shortcode_end;
			} else {
				$lasso_shortcode = $shortcode_start;
			}
		}

		return $lasso_shortcode;
	}

	/**
	 * Register block. Overwrite default function from EAFL plugin
	 */
	public function eafl_block() {
		if ( function_exists( 'register_block_type' ) ) {
			$block_settings = array(
				'attributes'      => array(
					'id'        => array(
						'type'    => 'string',
						'default' => '',
					),
					'type'      => array(
						'type'    => 'string',
						'default' => 'text',
					),
					'text'      => array(
						'type'    => 'string',
						'default' => '',
					),
					'textAlign' => array(
						'type'    => 'string',
						'default' => 'left',
					),
					'className' => array(
						'type'    => 'string',
						'default' => '',
					),
					'updated'   => array(
						'type'    => 'number',
						'default' => 0,
					),
				),
				'render_callback' => array( $this, 'lasso_render_easy_affilate_link_block' ),
			);
			register_block_type( 'easy-affiliate-links/easy-affiliate-link', $block_settings );
		}
	}

	/**
	 * Customize block html. Overwrite default function from EAFL plugin
	 *
	 * @param array $atts Attributes array.
	 */
	public function lasso_render_easy_affilate_link_block( $atts ) {
		$lasso_id = intval( $atts['id'] ?? 0 );
		if ( get_post_type( $lasso_id ) === LASSO_POST_TYPE ) {
			$lasso_attrs = array(
				'id'    => $lasso_id,
				'type'  => 'link',
				'title' => $atts['text'] ?? '',
			);
			$link_output = $this->lasso_core_shortcode( $lasso_attrs );

			return '<div class="lasso">' . $link_output . '</div>';
		}

		// ? default html from EAFL plugin
		return EAFL_Blocks::render_easy_affilate_link_block( $atts );
	}

	/**
	 * Register api for EAFL post
	 */
	public function eafl_api_register_data() {
		if ( function_exists( 'register_rest_field' ) ) {
			register_rest_field(
				EAFL_POST_TYPE,
				'link',
				array(
					'get_callback'    => array( EAFL_API_Links::class, 'api_get_link_data' ),
					'update_callback' => null,
					'schema'          => null,
				)
			);
		}

		register_rest_route(
			'wp/v2',
			EAFL_POST_TYPE . '/(?P<id>\d+)',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'single_eafl_post' ),
				'args'                => array(
					'id' => array(
						'validate_callback' => array( __CLASS__, 'api_validate_numeric' ),
					),
				),
				'permission_callback' => '__return_true',
			)
		);

		register_rest_route(
			'wp/v2',
			EAFL_POST_TYPE . '/(?P<id>\d+)',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'single_eafl_post_update_insert' ),
				'args'                => array(
					'id' => array(
						'validate_callback' => array( __CLASS__, 'api_validate_numeric' ),
					),
				),
				'permission_callback' => '__return_true',
			)
		);
	}

	/**
	 * Validate ID in API call.
	 *
	 * @param    mixed           $param Parameter to validate.
	 * @param    WP_REST_Request $request Current request.
	 * @param    mixed           $key Key.
	 */
	public static function api_validate_numeric( $param, $request, $key ) {
		return is_numeric( $param );
	}

	/**
	 * Handle response of the Rest API v2 for EAFL post
	 *
	 * @param array $data API data.
	 */
	public function single_eafl_post( $data ) {
		$post_id = intval( $data['id'] ?? 0 );
		$error   = new WP_Error(
			'rest_post_invalid_id',
			__( 'Invalid post ID.' ),
			array( 'status' => 404 )
		);

		if ( $post_id <= 0 ) {
			return $post_id;
		}

		// ? EAFL post was imported to Lasso
		$post_type = get_post_type( $post_id );
		if ( LASSO_POST_TYPE === $post_type || EAFL_POST_TYPE === $post_type ) {
			$post = get_post( $post_id );
			if ( ! $post ) {
				return $error;
			}

			$link = new EAFL_Link( $post );

			return array(
				'link' => $link->get_data(),
			);
		}
	}

	/**
	 * Handle response of the Rest API v2 for EAFL post - update insert
	 *
	 * @param array $request API data.
	 */
	public function single_eafl_post_update_insert( $request ) {
		$post_id = intval( $request['id'] ?? 0 );
		$error   = new WP_Error(
			'rest_post_invalid_id',
			__( 'Invalid post ID.' ),
			array( 'status' => 404 )
		);

		// ? EAFL post was imported to Lasso
		$post_type = get_post_type( $post_id );
		if ( LASSO_POST_TYPE === $post_type || EAFL_POST_TYPE === $post_type ) {
			$params = $request->get_params();
			$link   = isset( $params['link'] ) ? EAFL_Link_Sanitizer::sanitize( $params['link'] ) : array();

			self::update_easy_affiliate_link( $post_id, $link );
			$post = get_post( $post_id );
			$link = new EAFL_Link( $post );

			return array(
				'link' => $link->get_data(),
			);
		}
	}

	/**
	 * Save Easy Affiliate link fields. (EAFL_Link_Saver::update_link)
	 * We have to rewrite this function because they call EAFL_Link_Saver::update_searchable_content, that validate post type.
	 *
	 * @since    2.0.0
	 * @param    int   $id Post ID of the link.
	 * @param    array $link Link fields to save.
	 */
	public static function update_easy_affiliate_link( $id, $link ) {
		// Filters.
		$link = apply_filters( 'eafl_save_link', $link, $id );
		if ( isset( $link['name'] ) ) {
			$link['name'] = apply_filters( 'eafl_save_eafl_name', $link['name'], $id ); }
		if ( isset( $link['slug'] ) ) {
			$link['slug'] = apply_filters( 'eafl_save_eafl_slug', $link['slug'], $id ); }

		// Link Taxonomies.
		if ( isset( $link['categories'] ) ) {
			wp_set_object_terms( $id, $link['categories'], 'eafl_category', false );
		}

		// Meta fields.
		$meta = array();

		if ( isset( $link['description'] ) ) {
			$meta['eafl_description'] = $link['description']; }
		if ( isset( $link['nofollow'] ) ) {
			$meta['eafl_nofollow'] = $link['nofollow']; }
		if ( isset( $link['sponsored'] ) ) {
			$meta['eafl_sponsored'] = $link['sponsored']; }
		if ( isset( $link['ugc'] ) ) {
			$meta['eafl_ugc'] = $link['ugc']; }
		if ( isset( $link['redirect_type'] ) ) {
			$meta['eafl_redirect_type'] = $link['redirect_type']; }
		if ( isset( $link['cloak'] ) ) {
			$meta['eafl_cloak'] = $link['cloak']; }
		if ( isset( $link['target'] ) ) {
			$meta['eafl_target'] = $link['target']; }
		if ( isset( $link['url'] ) ) {
			$meta['eafl_url'] = $link['url']; }
		if ( isset( $link['html'] ) ) {
			$meta['eafl_html'] = $link['html']; }
		if ( isset( $link['type'] ) ) {
			$meta['eafl_type'] = $link['type']; }
		if ( isset( $link['text'] ) ) {
			$meta['eafl_text'] = $link['text']; }
		if ( isset( $link['classes'] ) ) {
			$meta['eafl_classes'] = $link['classes']; }
		if ( isset( $link['status_ignore'] ) ) {
			$meta['eafl_status_ignore'] = $link['status_ignore']; }

		// WPUPG Image.
		if ( isset( $link['wpupg_custom_image_id'] ) ) {
			if ( $link['wpupg_custom_image_id'] ) {
				$meta['wpupg_custom_image_id'] = $link['wpupg_custom_image_id'];
			} else {
				delete_post_meta( $id, 'wpupg_custom_image_id' );
			}
		}

		// Set technical slug for HTML Code links so they don't take over useful slugs.
		if ( isset( $link['type'] ) && 'html' === $link['type'] ) {
			$link['slug'] = 'eafl-html-code-link';
		}

		$meta = apply_filters( 'eafl_link_save_meta', $meta, $id, $link );

		// Post Fields.
		$post = array(
			'ID'          => $id,
			'post_status' => 'publish',
			'meta_input'  => $meta,
		);

		if ( isset( $link['name'] ) ) {
			$post['post_title'] = $link['name']; }
		if ( isset( $link['slug'] ) ) {
			$post['post_name'] = $link['slug']; }

		wp_update_post( $post );

		EAFL_Link_Manager::invalidate_link( $id );
		self::easy_affiliate_link_update_searchable_content( $id );
	}

	/**
	 * Easy Affiliate link - update the searchable content for a link.
	 *
	 * @since    2.0.0
	 * @param    int $id Post ID of the link.
	 */
	public static function easy_affiliate_link_update_searchable_content( $id ) {
		$link = new EAFL_Link( get_post( $id ) );

		$content  = $link->id();
		$content .= ' ' . $link->description();
		$content .= ' ' . $link->slug();
		$content .= ' ' . $link->url();
		$content .= ' ' . $link->categories_list();

		// Link text.
		foreach ( $link->text() as $text ) {
			$content .= ' ' . $text;
		}

		// Update link.
		$post = array(
			'ID'           => $id,
			'post_content' => $content,
		);
		wp_update_post( $post );
	}

	/**
	 * Handle Lasso shortcodes in Widget text
	 *
	 * @param string $widget_text Widget text.
	 * @return mixed
	 */
	public function lasso_widget_text( $widget_text ) {
		if ( Lasso_Helper::is_wp_elementor_plugin_actived() ) {
			return $widget_text;
		}

		if ( has_shortcode( $widget_text, 'lasso' ) ) {
			$shortcode = 'lasso';
		} else {
			$shortcode = false;
		}

		if ( $shortcode ) {
			$widget_text = str_replace( '[' . $shortcode, '[' . $shortcode . ' origin="widget"', $widget_text );
		}

		return $widget_text;
	}

	/**
	 * Get chat response from Lasso API
	 *
	 * @param string $message      Message from user.
	 * @param string $format       Format of response. Default to text. Value: text or html.
	 * @param int    $cache_reset  Reset cache. Default to 0.
	 * @param array  $optional_data Optional data.
	 */
	public static function get_chatgpt_response( $message, $format = 'text', $cache_reset = 0, $optional_data = array() ) {
		$message = trim( $message );
		$hash    = md5( serialize( $message ) ); // phpcs:ignore

		$cache_key = 'lasso_chatgpt_response_' . $format . $hash;

		$response_modified = $optional_data['response_modified'] ?? null;
		$is_from_modal     = $optional_data['is_from_modal'] ?? false;
		$is_from_modal     = Lasso_Helper::cast_to_boolean( $is_from_modal );

		$is_add_shortcode = $optional_data['is_add_shortcode'] ?? false;
		$is_add_shortcode = Lasso_Helper::cast_to_boolean( $is_add_shortcode );

		// ? Check if the response is cached in the WordPress database
		$cached_response = get_transient( $cache_key );
		if ( false !== $cached_response && '' !== wp_strip_all_tags( $cached_response ) && ! $cache_reset ) {
			if ( $is_from_modal && '' !== wp_strip_all_tags( $response_modified ) ) {
				$hash_response_modified = md5( serialize( trim( $response_modified ) ) ); // phpcs:ignore
				$hash_response          = md5( serialize( trim( $cached_response ) ) ); // phpcs:ignore

				if ( $hash_response_modified !== $hash_response || $is_add_shortcode ) {
					$cached_response = $response_modified;
					set_transient( $cache_key, $response_modified );
					return $cached_response;
				}
			} elseif ( ! $is_from_modal ) {
				return $cached_response;
			}
		}

		$html_content   = 'html' === $format ? '- The output should be HTML. That means all paragraphs are wrapped in <p> tags.' : '';
		$format_content = "
			Instructions: 
			- Make sure the response contains details that show it is authoritative on the subject.
			- Respond in a casual tone with simple, short and concise sentences. 
			$html_content
			- Keep the section short and don't include a conclusion. This is a small section of a larger article.
		";

		$headers  = Lasso_Helper::get_lasso_headers();
		$api_link = LASSO_LINK . '/chat';
		$data     = array(
			'message' => $message . $format_content,
		);

		$body = Encrypt::encrypt_aes( $data );
		$res  = Lasso_Helper::send_request( 'post', $api_link, $body, $headers );

		if ( 200 !== $res['status_code'] ) {
			return '';
		}

		$chat_message = $res['response']->message ?? '';
		if ( $chat_message ) {
			set_transient( $cache_key, $chat_message );
		}

		return $chat_message;
	}
}
