<?php
/**
 * Schema Pro Config.
 *
 * @package Schema Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'BSF_SP_Config' ) ) {

	/**
	 * Class BSF_SP_Config.
	 */
	class BSF_SP_Config {

		/**
		 * Block Attributes
		 *
		 * @var block_attributes
		 */
		public static $block_attributes = null;

		/**
		 * Block Assets
		 *
		 * @var block_attributes
		 */
		public static $block_assets = null;

		/**
		 * Get Widget List.
		 *
		 * @since 0.0.1
		 *
		 * @return array The Widget List.
		 */
		public static function get_block_attributes() {

			if ( null === self::$block_attributes ) {
				self::$block_attributes = array(
					'wpsp/faq'          => array(
						'slug'        => '',
						'title'       => __( 'FAQ - Schema Pro', 'wp-schema-pro' ),
						'description' => __( 'This block helps you add a FAQ section with inbuilt schema support.', 'wp-schema-pro' ),
						'default'     => true,
						'js_assets'   => array( 'wpsp-faq-js' ),
						'attributes'  => array(
							'block_id'                     => '',
							'layout'                       => 'accordion',
							'inactiveOtherItems'           => true,
							'expandFirstItem'              => false,
							'enableSchemaSupport'          => false,
							'align'                        => 'left',
							'enableSeparator'              => false,
							'rowsGap'                      => 10,
							'columnsGap'                   => 10,
							'boxBgColor'                   => '#FFFFFF',
							'boxPaddingTypeMobile'         => 'px',
							'boxPaddingTypeTablet'         => 'px',
							'boxPaddingTypeDesktop'        => 'px',
							'vBoxPaddingMobile'            => 10,
							'hBoxPaddingMobile'            => 10,
							'vBoxPaddingTablet'            => 10,
							'hBoxPaddingTablet'            => 10,
							'vBoxPaddingDesktop'           => 10,
							'hBoxPaddingDesktop'           => 10,
							'borderStyle'                  => 'solid',
							'borderWidth'                  => 1,
							'borderRadius'                 => 2,
							'borderColor'                  => '#D2D2D2',
							'questionTextColor'            => '#313131',
							'questionTextActiveColor'      => '#313131',
							'questionPaddingTypeDesktop'   => 'px',
							'vquestionPaddingMobile'       => 10,
							'vquestionPaddingTablet'       => 10,
							'vquestionPaddingDesktop'      => 10,
							'hquestionPaddingMobile'       => 10,
							'hquestionPaddingTablet'       => 10,
							'hquestionPaddingDesktop'      => 10,
							'answerTextColor'              => '#313131',
							'answerPaddingTypeDesktop'     => 'px',
							'vanswerPaddingMobile'         => 10,
							'vanswerPaddingTablet'         => 10,
							'vanswerPaddingDesktop'        => 10,
							'hanswerPaddingMobile'         => 10,
							'hanswerPaddingTablet'         => 10,
							'hanswerPaddingDesktop'        => 10,
							'iconColor'                    => '',
							'iconActiveColor'              => '',
							'gapBtwIconQUestion'           => 10,
							'questionloadGoogleFonts'      => false,
							'answerloadGoogleFonts'        => false,
							'questionFontFamily'           => 'Default',
							'questionFontWeight'           => '',
							'questionFontSubset'           => '',
							'questionFontSize'             => '',
							'questionFontSizeType'         => 'px',
							'questionFontSizeTablet'       => '',
							'questionFontSizeMobile'       => '',
							'questionLineHeight'           => '',
							'questionLineHeightType'       => 'em',
							'questionLineHeightTablet'     => '',
							'questionLineHeightMobile'     => '',
							'answerFontFamily'             => 'Default',
							'answerFontWeight'             => '',
							'answerFontSubset'             => '',
							'answerFontSize'               => '',
							'answerFontSizeType'           => 'px',
							'answerFontSizeTablet'         => '',
							'answerFontSizeMobile'         => '',
							'answerLineHeight'             => '',
							'answerLineHeightType'         => 'em',
							'answerLineHeightTablet'       => '',
							'answerLineHeightMobile'       => '',
							'icon'                         => 'fas fa-plus',
							'iconActive'                   => 'fas fa-minus',
							'iconAlign'                    => 'row',
							'iconSize'                     => 15,
							'iconSizeMobile'               => 15,
							'iconSizeTablet'               => 15,
							'iconSizeType'                 => 'px',
							'columns'                      => 2,
							'tcolumns'                     => 2,
							'mcolumns'                     => 1,
							'schema'                       => '',
							'enableToggle'                 => true,
							'questionLeftPaddingTablet'    => 10,
							'questionBottomPaddingTablet'  => 10,
							'questionLeftPaddingDesktop'   => 10,
							'questionBottomPaddingDesktop' => 10,
							'questionLeftPaddingMobile'    => 10,
							'questionBottomPaddingMobile'  => 10,
						),
					),
					'wpsp/faq-child'    => array(
						'slug'        => '',
						'title'       => __( 'FAQ - Schema Pro Child', 'wp-schema-pro' ),
						'description' => __( 'This block helps you add single FAQ.', 'wp-schema-pro' ),
						'default'     => true,
						'attributes'  => array(
							'block_id'   => '',
							'question'   => '',
							'answer'     => '',
							'icon'       => 'fas fa-plus',
							'iconActive' => 'fas fa-minus',
							'layout'     => 'accordion',
						),
					),
					'wpsp/how-to'       => array(
						'slug'        => '',
						'title'       => __( 'How-to - Schema Pro', 'wp-schema-pro' ),
						'description' => __( 'This block allows you to design attractive How-to pages or articles with automatically adding How-to Schema to your page.', 'wp-schema-pro' ),
						'default'     => true,
						'attributes'  => array(
							'block_id'                => '',
							'overallAlignment'        => 'left',
							'toolsCount'              => 1,
							'materialCount'           => 1,
							'tools'                   => '',
							'materials'               => '',
							'showTotaltime'           => false,
							'showEstcost'             => false,
							'showTools'               => false,
							'showMaterials'           => false,
							'mainimage'               => '',
							'imgSize'                 => 'thumbnail',
							'timeSpace'               => 5,
							'costSpace'               => 5,
							'time'                    => '30',
							'cost'                    => '65',
							'currencyType'            => ' USD',
							'headingAlign'            => 'left',
							'descriptionAlign'        => 'left',
							'headingColor'            => '',
							'showEstcostcolor'        => '',
							'showTotaltimecolor'      => '',
							'subHeadingColor'         => '',
							'headingTag'              => 'h3',
							'headSpace'               => 15,
							'headFontFamily'          => 'Default',
							'headFontWeight'          => '',
							'headFontSubset'          => '',
							'headFontSizeType'        => 'px',
							'headLineHeightType'      => 'em',
							'headFontSize'            => '',
							'headFontSizeTablet'      => '',
							'headFontSizeMobile'      => '',
							'headLineHeight'          => '',
							'headLineHeightTablet'    => '',
							'headLineHeightMobile'    => '',
							'subHeadFontFamily'       => 'Default',
							'subHeadFontWeight'       => '',
							'subHeadFontSubset'       => '',
							'subHeadFontSize'         => '',
							'subHeadFontSizeType'     => 'px',
							'subHeadFontSizeTablet'   => '',
							'subHeadFontSizeMobile'   => '',
							'subHeadLineHeight'       => '',
							'subHeadLineHeightType'   => 'em',
							'subHeadLineHeightTablet' => '',
							'subHeadLineHeightMobile' => '',
							'separatorSpace'          => 15,
							'headLoadGoogleFonts'     => false,
							'subHeadLoadGoogleFonts'  => false,
							'priceFontSizeType'       => 'px',
							'priceFontSize'           => '',
							'priceFontSizeTablet'     => '',
							'priceFontSizeMobile'     => '',
							'priceFontFamily'         => 'Default',
							'priceFontWeight'         => '',
							'priceFontSubset'         => '',
							'priceLineHeightType'     => 'em',
							'priceLineHeight'         => '',
							'priceLineHeightTablet'   => '',
							'priceLineHeightMobile'   => '',
							'priceLoadGoogleFonts'    => false,
							'row_gap'                 => 20,
							'step_gap'                => 20,
							'schema'                  => '',
						),
					),
					'wpsp/how-to-child' => array(
						'slug'        => '',
						'title'       => __( 'Steps', 'wp-schema-pro' ),
						'description' => __( 'This steps box allows you to place an image along with a heading and description within a single block.', 'wp-schema-pro' ),
						'default'     => true,
						'attributes'  => array(
							'classMigrate'            => false,
							'headingAlign'            => 'left',
							'headingColor'            => '',
							'subHeadingColor'         => '',
							'headFontSize'            => '',
							'headFontSizeType'        => 'px',
							'headFontSizeTablet'      => '',
							'headFontSizeMobile'      => '',
							'headFontFamily'          => '',
							'headFontWeight'          => '',
							'headFontSubset'          => '',
							'headLineHeightType'      => 'em',
							'headLineHeight'          => '',
							'headLineHeightTablet'    => '',
							'headLineHeightMobile'    => '',
							'headLoadGoogleFonts'     => false,
							'subHeadFontSize'         => '',
							'subHeadFontSizeType'     => 'px',
							'subHeadFontSizeTablet'   => '',
							'subHeadFontSizeMobile'   => '',
							'subHeadFontFamily'       => '',
							'subHeadFontWeight'       => '',
							'subHeadFontSubset'       => '',
							'subHeadLineHeightType'   => 'em',
							'subHeadLineHeight'       => '',
							'subHeadLineHeightTablet' => '',
							'subHeadLineHeightMobile' => '',
							'subHeadLoadGoogleFonts'  => false,
							'separatorWidth'          => '',
							'separatorHeight'         => '',
							'separatorWidthType'      => '%',
							'headSpace'               => '10',
							'separatorSpace'          => '10',
							'subHeadSpace'            => '10',
							'iconColor'               => '#333',
							'iconSize'                => '40',
							'iconimgPosition'         => 'above-title',
							'block_id'                => '',
							'iconHover'               => '',
							'iconimgBorderRadius'     => '0',
							'seperatorStyle'          => 'solid',
							'seperatorWidth'          => '30',
							'seperatorColor'          => '#333',
							'seperatorThickness'      => '2',
							'ctaLinkColor'            => '#333',
							'ctaFontSize'             => '',
							'ctaFontSizeType'         => 'px',
							'ctaFontSizeMobile'       => '',
							'ctaFontSizeTablet'       => '',
							'ctaFontFamily'           => '',
							'ctaFontWeight'           => '',
							'ctaFontSubset'           => '',
							'ctaLoadGoogleFonts'      => false,
							'ctaBtnLinkColor'         => '#333',
							'ctaBgColor'              => 'transparent',
							'ctaBtnVertPadding'       => '10',
							'ctaBtnHrPadding'         => '14',
							'ctaBorderStyle'          => 'solid',
							'ctaBorderColor'          => '#333',
							'ctaBorderWidth'          => '1',
							'ctaBorderRadius'         => '0',
							'iconLeftMargin'          => '5',
							'iconRightMargin'         => '10',
							'iconTopMargin'           => '5',
							'iconBottomMargin'        => '5',
							'imageSize'               => 'thumbnail',
							'imageWidthType'          => false,
							'imageWidth'              => '120',
							'seperatorSpace'          => '15',
							'ctaLinkHoverColor'       => '',
							'ctaBgHoverColor'         => '',
							'ctaBorderhoverColor'     => '',
							'ctaIconSpace'            => '5',
						),
					),

				);
			}
			return self::$block_attributes;
		}

		/**
		 * Get Block Assets.
		 *
		 * @since 1.13.4
		 *
		 * @return array The Asset List.
		 */
		public static function get_block_assets() {

			$minify = BSF_AIOSRS_Pro_Helper::bsf_schema_pro_is_wp_debug_enable() ? 'js/faq.js' : 'min-js/faq.min.js';
			$faq_js = BSF_AIOSRS_PRO_URI . 'wpsp-blocks/assets/' . $minify;

			if ( null === self::$block_assets ) {
				self::$block_assets = array(

					'wpsp-faq-js' => array(
						'src'        => $faq_js,
						'dep'        => array(),
						'skipEditor' => true,
					),
				);
			}
			return self::$block_assets;
		}
	}
}

