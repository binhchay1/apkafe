<?php
/**
 * Schema Pro Block Helper.
 *
 * @package Schema Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'BSF_SP_Block_JS' ) ) {

	/**
	 * Class BSF_SP_Block_JS.
	 */
	class BSF_SP_Block_JS {

		/**
		 * Adds Google fonts for FAQ block.
		 *
		 * @since 1.15.0
		 * @param array $attr the blocks attr.
		 */
		public static function blocks_faq_gfont( $attr ) {

			$question_load_google_font = isset( $attr['questionloadGoogleFonts'] ) ? $attr['questionloadGoogleFonts'] : '';
			$question_font_family      = isset( $attr['questionFontFamily'] ) ? $attr['questionFontFamily'] : '';
			$question_font_weight      = isset( $attr['questionFontWeight'] ) ? $attr['questionFontWeight'] : '';
			$question_font_subset      = isset( $attr['questionFontSubset'] ) ? $attr['questionFontSubset'] : '';

			$answer_load_google_font = isset( $attr['answerloadGoogleFonts'] ) ? $attr['answerloadGoogleFonts'] : '';
			$answer_font_family      = isset( $attr['answerFontFamily'] ) ? $attr['answerFontFamily'] : '';
			$answer_font_weight      = isset( $attr['answerFontWeight'] ) ? $attr['answerFontWeight'] : '';
			$answer_font_subset      = isset( $attr['answerFontSubset'] ) ? $attr['answerFontSubset'] : '';

			BSF_SP_Helper::blocks_google_font( $question_load_google_font, $question_font_family, $question_font_weight, $question_font_subset );
			BSF_SP_Helper::blocks_google_font( $answer_load_google_font, $answer_font_family, $answer_font_weight, $answer_font_subset );
		}

		/**
		 * Adds Google fonts for How To block.
		 *
		 * @since 2.4.0
		 * @param array $attr the blocks attr.
		 */
		public static function blocks_how_to_gfont( $attr ) {

			$head_load_google_font = isset( $attr['headLoadGoogleFonts'] ) ? $attr['headLoadGoogleFonts'] : '';
			$head_font_family      = isset( $attr['headFontFamily'] ) ? $attr['headFontFamily'] : '';
			$head_font_weight      = isset( $attr['headFontWeight'] ) ? $attr['headFontWeight'] : '';
			$head_font_subset      = isset( $attr['headFontSubset'] ) ? $attr['headFontSubset'] : '';

			$subhead_load_google_font = isset( $attr['subHeadLoadGoogleFonts'] ) ? $attr['subHeadLoadGoogleFonts'] : '';
			$subhead_font_family      = isset( $attr['subHeadFontFamily'] ) ? $attr['subHeadFontFamily'] : '';
			$subhead_font_weight      = isset( $attr['subHeadFontWeight'] ) ? $attr['subHeadFontWeight'] : '';
			$subhead_font_subset      = isset( $attr['subHeadFontSubset'] ) ? $attr['subHeadFontSubset'] : '';

			$price_load_google_font = isset( $attr['priceLoadGoogleFonts'] ) ? $attr['priceLoadGoogleFonts'] : '';
			$price_font_family      = isset( $attr['priceFontFamily'] ) ? $attr['priceFontFamily'] : '';
			$price_font_weight      = isset( $attr['priceFontWeight'] ) ? $attr['priceFontWeight'] : '';
			$price_font_subset      = isset( $attr['priceFontSubset'] ) ? $attr['priceFontSubset'] : '';

			BSF_SP_Helper::blocks_google_font( $head_load_google_font, $head_font_family, $head_font_weight, $head_font_subset );
			BSF_SP_Helper::blocks_google_font( $subhead_load_google_font, $subhead_font_family, $subhead_font_weight, $subhead_font_subset );
			BSF_SP_Helper::blocks_google_font( $price_load_google_font, $price_font_family, $price_font_weight, $price_font_subset );
		}

		/**
		 * Adds Google fonts for HowTo Child Block.
		 *
		 * @since 2.4.0
		 * @param array $attr the blocks attr.
		 */
		public static function blocks_how_to_child_gfont( $attr ) {

			$head_load_google_font = isset( $attr['headLoadGoogleFonts'] ) ? $attr['headLoadGoogleFonts'] : '';
			$head_font_family      = isset( $attr['headFontFamily'] ) ? $attr['headFontFamily'] : '';
			$head_font_weight      = isset( $attr['headFontWeight'] ) ? $attr['headFontWeight'] : '';
			$head_font_subset      = isset( $attr['headFontSubset'] ) ? $attr['headFontSubset'] : '';

			$subhead_load_google_font = isset( $attr['subHeadLoadGoogleFonts'] ) ? $attr['subHeadLoadGoogleFonts'] : '';
			$subhead_font_family      = isset( $attr['subHeadFontFamily'] ) ? $attr['subHeadFontFamily'] : '';
			$subhead_font_weight      = isset( $attr['subHeadFontWeight'] ) ? $attr['subHeadFontWeight'] : '';
			$subhead_font_subset      = isset( $attr['subHeadFontSubset'] ) ? $attr['subHeadFontSubset'] : '';

			$cta_load_google_font = isset( $attr['ctaLoadGoogleFonts'] ) ? $attr['ctaLoadGoogleFonts'] : '';
			$cta_font_family      = isset( $attr['ctaFontFamily'] ) ? $attr['ctaFontFamily'] : '';
			$cta_font_weight      = isset( $attr['ctaFontWeight'] ) ? $attr['ctaFontWeight'] : '';
			$cta_font_subset      = isset( $attr['ctaFontSubset'] ) ? $attr['ctaFontSubset'] : '';

			BSF_SP_Helper::blocks_google_font( $cta_load_google_font, $cta_font_family, $cta_font_weight, $cta_font_subset );
			BSF_SP_Helper::blocks_google_font( $head_load_google_font, $head_font_family, $head_font_weight, $head_font_subset );
			BSF_SP_Helper::blocks_google_font( $subhead_load_google_font, $subhead_font_family, $subhead_font_weight, $subhead_font_subset );
		}

	}
}
