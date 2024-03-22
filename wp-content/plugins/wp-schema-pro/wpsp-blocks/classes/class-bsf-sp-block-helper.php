<?php
/**
 * Schema Pro Blocks Helper.
 *
 * @package Schema Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'BSF_SP_Block_Helper' ) ) {

	/**
	 * Class BSF_SP_Block_Helper.
	 */
	final class BSF_SP_Block_Helper {


		/**
		 * Get FAQ CSS.
		 *
		 * @since 2.2.0
		 * @param array  $attr The block attributes.
		 * @param string $id The selector ID.
		 */
		public static function get_faq_css( $attr, $id ) {

			$defaults = BSF_SP_Helper::$block_list['wpsp/faq']['attributes'];

			$attr = array_merge( $defaults, $attr );

			$icon_color        = $attr['iconColor'];
			$icon_active_color = $attr['iconActiveColor'];

			$attr['questionBottomPaddingDesktop'] = ( 10 === $attr['questionBottomPaddingDesktop'] && 10 !== $attr['vquestionPaddingDesktop'] ) ? $attr['vquestionPaddingDesktop'] : $attr['questionBottomPaddingDesktop'];

			$attr['questionLeftPaddingDesktop'] = ( 10 === $attr['questionLeftPaddingDesktop'] && 10 !== $attr['hquestionPaddingDesktop'] ) ? $attr['hquestionPaddingDesktop'] : $attr['questionLeftPaddingDesktop'];

			$attr['questionBottomPaddingTablet'] = ( 10 === $attr['questionBottomPaddingTablet'] && 10 !== $attr['vquestionPaddingTablet'] ) ? $attr['vquestionPaddingTablet'] : $attr['questionBottomPaddingTablet'];

			$attr['questionLeftPaddingTablet'] = ( 10 === $attr['questionLeftPaddingTablet'] && 10 !== $attr['hquestionPaddingTablet'] ) ? $attr['hquestionPaddingTablet'] : $attr['questionLeftPaddingTablet'];

			$attr['questionBottomPaddingMobile'] = ( 10 === $attr['questionBottomPaddingMobile'] && 10 !== $attr['vquestionPaddingMobile'] ) ? $attr['vquestionPaddingMobile'] : $attr['questionBottomPaddingMobile'];

			$attr['questionLeftPaddingMobile'] = ( 10 === $attr['questionLeftPaddingMobile'] && 10 !== $attr['hquestionPaddingMobile'] ) ? $attr['hquestionPaddingMobile'] : $attr['questionLeftPaddingMobile'];

			if ( ! isset( $attr['iconColor'] ) || '' === $attr['iconColor'] ) {

				$icon_color = $attr['questionTextColor'];
			}
			if ( ! isset( $attr['iconActiveColor'] ) || '' === $attr['iconActiveColor'] ) {

				$icon_active_color = $attr['questionTextActiveColor'];
			}

			$icon_size   = BSF_SP_Helper::get_css_value( $attr['iconSize'], $attr['iconSizeType'] );
			$t_icon_size = BSF_SP_Helper::get_css_value( $attr['iconSizeTablet'], $attr['iconSizeType'] );
			$m_icon_size = BSF_SP_Helper::get_css_value( $attr['iconSizeMobile'], $attr['iconSizeType'] );

			$selectors = array(
				' .wpsp-icon svg'                      => array(
					'width'     => $icon_size,
					'height'    => $icon_size,
					'font-size' => $icon_size,
					'fill'      => $icon_color,
				),
				' .wpsp-icon-active svg'               => array(
					'width'     => $icon_size,
					'height'    => $icon_size,
					'font-size' => $icon_size,
					'fill'      => $icon_active_color,
				),
				' .wpsp-faq-child__outer-wrap'         => array(
					'margin-bottom' => BSF_SP_Helper::get_css_value( $attr['rowsGap'], 'px' ),
				),
				' .wpsp-faq-item'                      => array(
					'background-color' => $attr['boxBgColor'],
					'border-style'     => $attr['borderStyle'],
					'border-width'     => BSF_SP_Helper::get_css_value( $attr['borderWidth'], 'px' ),
					'border-radius'    => BSF_SP_Helper::get_css_value( $attr['borderRadius'], 'px' ),
					'border-color'     => $attr['borderColor'],
				),
				' .wpsp-faq-item .wpsp-question'       => array(
					'color' => $attr['questionTextColor'],
				),
				' .wpsp-faq-item.wpsp-faq-item-active .wpsp-question' => array(
					'color' => $attr['questionTextActiveColor'],
				),
				' .wpsp-faq-item:hover .wpsp-question' => array(
					'color' => $attr['questionTextActiveColor'],
				),
				' .wpsp-faq-questions-button'          => array(
					'padding-top'    => BSF_SP_Helper::get_css_value( $attr['vquestionPaddingDesktop'], $attr['questionPaddingTypeDesktop'] ),
					'padding-bottom' => BSF_SP_Helper::get_css_value( $attr['questionBottomPaddingDesktop'], $attr['questionPaddingTypeDesktop'] ),
					'padding-right'  => BSF_SP_Helper::get_css_value( $attr['hquestionPaddingDesktop'], $attr['questionPaddingTypeDesktop'] ),
					'padding-left'   => BSF_SP_Helper::get_css_value( $attr['questionLeftPaddingDesktop'], $attr['questionPaddingTypeDesktop'] ),
				),
				' .wpsp-faq-content span'              => array(
					'margin-top'    => BSF_SP_Helper::get_css_value( $attr['vanswerPaddingDesktop'], $attr['answerPaddingTypeDesktop'] ),
					'margin-bottom' => BSF_SP_Helper::get_css_value( $attr['vanswerPaddingDesktop'], $attr['answerPaddingTypeDesktop'] ),
					'margin-right'  => BSF_SP_Helper::get_css_value( $attr['hanswerPaddingDesktop'], $attr['answerPaddingTypeDesktop'] ),
					'margin-left'   => BSF_SP_Helper::get_css_value( $attr['hanswerPaddingDesktop'], $attr['answerPaddingTypeDesktop'] ),
				),
				'.wpsp-faq-icon-row .wpsp-faq-item .wpsp-faq-icon-wrap' => array(
					'margin-right' => BSF_SP_Helper::get_css_value( $attr['gapBtwIconQUestion'], 'px' ),
				),
				'.wpsp-faq-icon-row-reverse .wpsp-faq-item .wpsp-faq-icon-wrap' => array(
					'margin-left' => BSF_SP_Helper::get_css_value( $attr['gapBtwIconQUestion'], 'px' ),
				),
				' .wpsp-faq-item:hover .wpsp-icon svg' => array(
					'fill' => $icon_active_color,
				),
				' .wpsp-faq-item .wpsp-faq-questions-button.wpsp-faq-questions' => array(
					'flex-direction' => $attr['iconAlign'],
				),
				' .wpsp-faq-item .wpsp-faq-content p'  => array(
					'color' => $attr['answerTextColor'],
				),
			);

			$t_selectors = array(
				' .wpsp-faq-questions-button' => array(
					'padding-top'    => BSF_SP_Helper::get_css_value( $attr['vquestionPaddingTablet'], $attr['questionPaddingTypeDesktop'] ),
					'padding-bottom' => BSF_SP_Helper::get_css_value( $attr['questionBottomPaddingTablet'], $attr['questionPaddingTypeDesktop'] ),
					'padding-right'  => BSF_SP_Helper::get_css_value( $attr['hquestionPaddingTablet'], $attr['questionPaddingTypeDesktop'] ),
					'padding-left'   => BSF_SP_Helper::get_css_value( $attr['questionLeftPaddingTablet'], $attr['questionPaddingTypeDesktop'] ),
				),
				' .wpsp-faq-content span'     => array(
					'margin-top'    => BSF_SP_Helper::get_css_value( $attr['vanswerPaddingTablet'], $attr['answerPaddingTypeDesktop'] ),
					'margin-bottom' => BSF_SP_Helper::get_css_value( $attr['vanswerPaddingTablet'], $attr['answerPaddingTypeDesktop'] ),
					'margin-right'  => BSF_SP_Helper::get_css_value( $attr['hanswerPaddingTablet'], $attr['answerPaddingTypeDesktop'] ),
					'margin-left'   => BSF_SP_Helper::get_css_value( $attr['hanswerPaddingTablet'], $attr['answerPaddingTypeDesktop'] ),
				),
				' .wpsp-icon svg'             => array(
					'width'     => $t_icon_size,
					'height'    => $t_icon_size,
					'font-size' => $t_icon_size,
				),
				' .wpsp-icon-active svg'      => array(
					'width'     => $t_icon_size,
					'height'    => $t_icon_size,
					'font-size' => $t_icon_size,
				),
			);
			$m_selectors = array(
				' .wpsp-faq-questions-button' => array(
					'padding-top'    => BSF_SP_Helper::get_css_value( $attr['vquestionPaddingMobile'], $attr['questionPaddingTypeDesktop'] ),
					'padding-bottom' => BSF_SP_Helper::get_css_value( $attr['questionBottomPaddingMobile'], $attr['questionPaddingTypeDesktop'] ),
					'padding-right'  => BSF_SP_Helper::get_css_value( $attr['hquestionPaddingMobile'], $attr['questionPaddingTypeDesktop'] ),
					'padding-left'   => BSF_SP_Helper::get_css_value( $attr['questionLeftPaddingMobile'], $attr['questionPaddingTypeDesktop'] ),
				),
				' .wpsp-faq-content span'     => array(
					'margin-top'    => BSF_SP_Helper::get_css_value( $attr['vanswerPaddingMobile'], $attr['answerPaddingTypeDesktop'] ),
					'margin-bottom' => BSF_SP_Helper::get_css_value( $attr['vanswerPaddingMobile'], $attr['answerPaddingTypeDesktop'] ),
					'margin-right'  => BSF_SP_Helper::get_css_value( $attr['hanswerPaddingMobile'], $attr['answerPaddingTypeDesktop'] ),
					'margin-left'   => BSF_SP_Helper::get_css_value( $attr['hanswerPaddingMobile'], $attr['answerPaddingTypeDesktop'] ),
				),
				' .wpsp-icon svg'             => array(
					'width'     => $m_icon_size,
					'height'    => $m_icon_size,
					'font-size' => $m_icon_size,
				),
				' .wpsp-icon-active svg'      => array(
					'width'     => $m_icon_size,
					'height'    => $m_icon_size,
					'font-size' => $m_icon_size,
				),
			);

			if ( 'accordion' === $attr['layout'] && true === $attr['inactiveOtherItems'] ) {

				$selectors[' .wp-block-wpsp-faq-child.wpsp-faq-child__outer-wrap .wpsp-faq-content '] = array(
					'display' => 'none',
				);
			}
			if ( 'accordion' === $attr['layout'] && true === $attr['expandFirstItem'] ) {

				$selectors[' .wpsp-faq__wrap.wpsp-buttons-layout-wrap > .wpsp-faq-child__outer-wrap:first-child > .wpsp-faq-child__wrapper .wpsp-faq-item.wpsp-faq-item-active .wpsp-faq-content '] = array(
					'display' => 'block',
				);
			}
			if ( true === $attr['enableSeparator'] ) {

				$selectors[' .wpsp-faq-child__outer-wrap .wpsp-faq-content '] = array(
					'border-style'        => 'solid',
					'border-top-color'    => $attr['borderColor'],
					'border-top-width'    => BSF_SP_Helper::get_css_value( $attr['borderWidth'], 'px' ),
					'border-right-width'  => '0px',
					'border-bottom-width' => '0px',
					'border-left-width'   => '0px',
				);
			}
			if ( 'grid' === $attr['layout'] ) {

				$selectors['.wpsp-faq-layout-grid .wpsp-faq__wrap .wpsp-faq-child__outer-wrap '] = array(
					'text-align' => $attr['align'],
				);
				$selectors['.wpsp-faq-layout-grid .wpsp-faq__wrap.wpsp-buttons-layout-wrap ']    = array(
					'grid-template-columns' => 'repeat(' . $attr['columns'] . ', 1fr)',
					'grid-column-gap'       => BSF_SP_Helper::get_css_value( $attr['columnsGap'], 'px' ),
					'grid-row-gap'          => BSF_SP_Helper::get_css_value( $attr['rowsGap'], 'px' ),
					'display'               => 'grid',
				);
				$t_selectors['.wpsp-faq-layout-grid .wpsp-faq__wrap.wpsp-buttons-layout-wrap ']  = array(
					'grid-template-columns' => 'repeat(' . $attr['tcolumns'] . ', 1fr)',
				);
				$m_selectors['.wpsp-faq-layout-grid .wpsp-faq__wrap.wpsp-buttons-layout-wrap ']  = array(
					'grid-template-columns' => 'repeat(' . $attr['mcolumns'] . ', 1fr)',
				);
			}

			$combined_selectors = array(
				'desktop' => $selectors,
				'tablet'  => $t_selectors,
				'mobile'  => $m_selectors,
			);

			$combined_selectors = BSF_SP_Helper::get_typography_css( $attr, 'question', ' .wpsp-faq-questions-button .wpsp-question', $combined_selectors );
			$combined_selectors = BSF_SP_Helper::get_typography_css( $attr, 'answer', ' .wpsp-faq-item .wpsp-faq-content p', $combined_selectors );

			return BSF_SP_Helper::generate_all_css( $combined_selectors, '.wpsp-block-' . $id );
		}

		/**
		 * Get How To CSS
		 *
		 * @since 2.4.0
		 * @param array  $attr The block attributes.
		 * @param string $id The selector ID.
		 * @return array The Widget List.
		 */
		public static function get_how_to_css( $attr, $id ) {
			$defaults = BSF_SP_Helper::$block_list['wpsp/how-to']['attributes'];

			$attr = array_merge( $defaults, $attr );

			$t_selectors = array();
			$m_selectors = array();

			$selectors = array(
				' .wpsp-how-to-main-wrap' => array(
					'text-align' => $attr['overallAlignment'],
				),

				' .wpsp-how-to-main-wrap p.wpsp-howto-desc-text' => array(
					'margin-bottom' => BSF_SP_Helper::get_css_value( $attr['row_gap'], 'px' ),
				),

				' .wpsp-how-to-main-wrap .wpsp-howto__source-wrap' => array(
					'margin-bottom' => BSF_SP_Helper::get_css_value( $attr['row_gap'], 'px' ),
				),

				' .wpsp-how-to-main-wrap span.wpsp-howto__time-wrap' => array(
					'margin-bottom' => BSF_SP_Helper::get_css_value( $attr['row_gap'], 'px' ),
				),

				' .wpsp-how-to-main-wrap span.wpsp-howto__cost-wrap' => array(
					'margin-bottom' => BSF_SP_Helper::get_css_value( $attr['row_gap'], 'px' ),
				),

				' .wpsp-tools__wrap .wpsp-how-to-tools-child__wrapper:last-child' => array(
					'margin-bottom' => '0px',
				),

				' .wpsp-how-to-materials .wpsp-how-to-materials-child__wrapper:last-child' => array(
					'margin-bottom' => BSF_SP_Helper::get_css_value( $attr['row_gap'], 'px' ),
				),

				' .wpsp-howto-steps__wrap .wp-block-wpsp-how-to-child' => array(
					'margin-bottom' => BSF_SP_Helper::get_css_value( $attr['step_gap'], 'px' ),
				),

				' .wpsp-howto-steps__wrap .wp-block-wpsp-how-to-child:last-child' => array(
					'margin-bottom' => '0px',
				),

				' span.wpsp-howto__time-wrap .wpsp-howto-timeNeeded-value' => array(
					'margin-left' => BSF_SP_Helper::get_css_value( $attr['timeSpace'], 'px' ),
				),

				' span.wpsp-howto__cost-wrap .wpsp-howto-estcost-value' => array(
					'margin-left' => BSF_SP_Helper::get_css_value( $attr['costSpace'], 'px' ),
				),

				' .wpsp-how-to-main-wrap .wpsp-howto-heading-text' => array(
					'color' => $attr['headingColor'],
				),

				' .wpsp-howto-desc-text'  => array(
					'color' => $attr['subHeadingColor'],
				),

				' .wpsp-howto__wrap span.wpsp-howto__time-wrap p' => array(
					'color' => $attr['subHeadingColor'],
				),

				' .wpsp-howto__wrap span.wpsp-howto__cost-wrap p' => array(
					'color' => $attr['subHeadingColor'],
				),

				' .wpsp-howto__wrap span.wpsp-howto__time-wrap h4.wpsp-howto-timeNeeded-text' => array(
					'color' => $attr['showTotaltimecolor'],
				),

				' .wpsp-howto__wrap span.wpsp-howto__cost-wrap h4.wpsp-howto-estcost-text' => array(
					'color' => $attr['showTotaltimecolor'],
				),

				' .wpsp-how-to-tools__wrap .wpsp-howto-req-tools-text' => array(
					'color' => $attr['showTotaltimecolor'],
				),

				'  .wpsp-how-to-materials__wrap .wpsp-howto-req-materials-text' => array(
					'color' => $attr['showTotaltimecolor'],
				),

				' .wpsp-how-to-steps__wrap .wpsp-howto-req-steps-text' => array(
					'color' => $attr['showTotaltimecolor'],
				),
			);

			$selectors[' .wpsp-tools__wrap .wpsp-how-to-tools-child__wrapper'] = array(
				'color' => $attr['subHeadingColor'],
			);

			$selectors[' .wpsp-how-to-materials .wpsp-how-to-materials-child__wrapper'] = array(
				'color' => $attr['subHeadingColor'],
			);

			$combined_selectors = array(
				'desktop' => $selectors,
				'tablet'  => $t_selectors,
				'mobile'  => $m_selectors,
			);

			$combined_selectors = BSF_SP_Helper::get_typography_css( $attr, 'subHead', ' p', $combined_selectors );
			$combined_selectors = BSF_SP_Helper::get_typography_css( $attr, 'price', ' h4', $combined_selectors );
			$combined_selectors = BSF_SP_Helper::get_typography_css( $attr, 'head', ' .wpsp-howto-heading-text', $combined_selectors );
			$combined_selectors = BSF_SP_Helper::get_typography_css( $attr, 'subHead', ' .wpsp-tools .wpsp-tools__label', $combined_selectors );
			$combined_selectors = BSF_SP_Helper::get_typography_css( $attr, 'subHead', ' .wpsp-materials .wpsp-materials__label', $combined_selectors );

			return BSF_SP_Helper::generate_all_css( $combined_selectors, ' .wpsp-block-' . $id );
		}

		/**
		 * Get Howto child CSS
		 *
		 * @since 2.4.0
		 * @param array  $attr The block attributes.
		 * @param string $id The selector ID.
		 * @return array The Widget List.
		 */
		public static function get_how_to_child_css( $attr, $id ) {

			$defaults = BSF_SP_Helper::$block_list['wpsp/how-to-child']['attributes'];

			$attr = array_merge( $defaults, (array) $attr );

			$m_selectors = array();
			$t_selectors = array();

			$cta_icon_size   = BSF_SP_Helper::get_css_value( $attr['ctaFontSize'], $attr['ctaFontSizeType'] );
			$m_cta_icon_size = BSF_SP_Helper::get_css_value( $attr['ctaFontSizeMobile'], $attr['ctaFontSizeType'] );
			$t_cta_icon_size = BSF_SP_Helper::get_css_value( $attr['ctaFontSizeTablet'], $attr['ctaFontSizeType'] );
			$icon_size       = BSF_SP_Helper::get_css_value( $attr['iconSize'], 'px' );

			$selectors = array(
				' .wpsp-ifb-icon'              => array(
					'height'      => $icon_size,
					'width'       => $icon_size,
					'line-height' => $icon_size,
				),
				' .wpsp-ifb-icon > span'       => array(
					'font-size'   => $icon_size,
					'height'      => $icon_size,
					'width'       => $icon_size,
					'line-height' => $icon_size,
					'color'       => $attr['iconColor'],
				),
				' .wpsp-ifb-icon svg'          => array(
					'fill' => $attr['iconColor'],
				),
				' .wpsp-ifb-icon:hover > span' => array(
					'color' => $attr['iconHover'],
				),
				' .wpsp-ifb-icon:hover svg'    => array(
					'fill' => $attr['iconHover'],
				),

				' .wpsp-how-to__link-to-all:hover ~ .wpsp-how-to__content-wrap .wpsp-ifb-icon svg' => array(
					'fill' => $attr['iconHover'],
				),

				' .wpsp-how-to__content-wrap .wpsp-ifb-imgicon-wrap' => array(
					'margin-left'   => BSF_SP_Helper::get_css_value( $attr['iconLeftMargin'], 'px' ),
					'margin-right'  => BSF_SP_Helper::get_css_value( $attr['iconRightMargin'], 'px' ),
					'margin-top'    => BSF_SP_Helper::get_css_value( $attr['iconTopMargin'], 'px' ),
					'margin-bottom' => BSF_SP_Helper::get_css_value( $attr['iconBottomMargin'], 'px' ),
				),
				' .wpsp-howto-child .wpsp-ifb-image-content img' => array(
					'border-radius' => BSF_SP_Helper::get_css_value( $attr['iconimgBorderRadius'], 'px' ),
				),
				// Title Style.
				' .wpsp-ifb-title'             => array(
					'color'         => $attr['headingColor'],
					'margin-bottom' => $attr['headSpace'] . 'px',
				),
				// Description Style.
				' .wpsp-ifb-desc'              => array(
					'color'         => $attr['subHeadingColor'],
					'margin-bottom' => BSF_SP_Helper::get_css_value( $attr['subHeadSpace'], 'px' ),
				),
				// Seperator.
				' .wpsp-ifb-separator'         => array(
					'width'            => BSF_SP_Helper::get_css_value( $attr['seperatorWidth'], $attr['separatorWidthType'] ),
					'border-top-width' => BSF_SP_Helper::get_css_value( $attr['seperatorThickness'], 'px' ),
					'border-top-color' => $attr['seperatorColor'],
					'border-top-style' => $attr['seperatorStyle'],
				),
				' .wpsp-ifb-separator-parent'  => array(
					'margin-bottom' => BSF_SP_Helper::get_css_value( $attr['seperatorSpace'], 'px' ),
				),
				// CTA icon space.
				' .wpsp-ifb-align-icon-after'  => array(
					'margin-left' => BSF_SP_Helper::get_css_value( $attr['ctaIconSpace'], 'px' ),
				),
				' .wpsp-ifb-align-icon-before' => array(
					'margin-right' => BSF_SP_Helper::get_css_value( $attr['ctaIconSpace'], 'px' ),
				),
			);

			$selectors[' .wpsp-howto-child-cta-link']                          = array(
				'color' => $attr['ctaLinkColor'],
			);
			$selectors[' .wpsp-ifb-cta .wpsp-howto-child-cta-link:hover']      = array(
				'color' => $attr['ctaLinkHoverColor'],
			);
			$selectors[' .wpsp-howto-child-cta-link .wpsp-ifb-button-icon']    = array(
				'font-size'   => $cta_icon_size,
				'height'      => $cta_icon_size,
				'width'       => $cta_icon_size,
				'line-height' => $cta_icon_size,
			);
			$selectors[' .wpsp-howto-child-cta-link .wpsp-ifb-text-icon']      = array(
				'font-size'   => $cta_icon_size,
				'height'      => $cta_icon_size,
				'width'       => $cta_icon_size,
				'line-height' => $cta_icon_size,
			);
			$selectors[' .wpsp-howto-child-cta-link svg']                      = array(
				'fill' => $attr['ctaLinkColor'],
			);
			$selectors[' .wpsp-howto-child-cta-link:hover svg']                = array(
				'fill' => $attr['ctaLinkHoverColor'],
			);
			$selectors[' .wpsp-ifb-button-wrapper .wpsp-howto-child-cta-link'] = array(
				'color'            => $attr['ctaBtnLinkColor'],
				'background-color' => $attr['ctaBgColor'],
				'border-style'     => $attr['ctaBorderStyle'],
				'border-color'     => $attr['ctaBorderColor'],
				'border-radius'    => BSF_SP_Helper::get_css_value( $attr['ctaBorderRadius'], 'px' ),
				'border-width'     => BSF_SP_Helper::get_css_value( $attr['ctaBorderWidth'], 'px' ),
				'padding-top'      => BSF_SP_Helper::get_css_value( $attr['ctaBtnVertPadding'], 'px' ),
				'padding-bottom'   => BSF_SP_Helper::get_css_value( $attr['ctaBtnVertPadding'], 'px' ),
				'padding-left'     => BSF_SP_Helper::get_css_value( $attr['ctaBtnHrPadding'], 'px' ),
				'padding-right'    => BSF_SP_Helper::get_css_value( $attr['ctaBtnHrPadding'], 'px' ),

			);
			$selectors[' .wpsp-ifb-button-wrapper .wpsp-howto-child-cta-link svg']       = array(
				'fill' => $attr['ctaBtnLinkColor'],
			);
			$selectors[' .wpsp-ifb-button-wrapper .wpsp-howto-child-cta-link:hover']     = array(
				'color'            => $attr['ctaLinkHoverColor'],
				'background-color' => $attr['ctaBgHoverColor'],
				'border-color'     => $attr['ctaBorderhoverColor'],
			);
			$selectors[' .wpsp-ifb-button-wrapper .wpsp-howto-child-cta-link:hover svg'] = array(
				'fill' => $attr['ctaLinkHoverColor'],
			);

			if ( $attr['imageWidthType'] ) {
				// Image.
				$selectors[' .wpsp-ifb-image-content img'] = array(
					'width'     => BSF_SP_Helper::get_css_value( $attr['imageWidth'], 'px' ),
					'max-width' => BSF_SP_Helper::get_css_value( $attr['imageWidth'], 'px' ),
				);
			}

			if ( 'above-title' === $attr['iconimgPosition'] || 'below-title' === $attr['iconimgPosition'] ) {
				$selectors[' .wpsp-how-to__content-wrap'] = array(
					'text-align' => $attr['headingAlign'],
				);
			}

			$m_selectors = array(
				' .wpsp-howto-child-cta-link .wpsp-ifb-button-icon' => array(
					'font-size'   => $m_cta_icon_size,
					'height'      => $m_cta_icon_size,
					'width'       => $m_cta_icon_size,
					'line-height' => $m_cta_icon_size,
				),
				' .wpsp-howto-child-cta-link .wpsp-ifb-text-icon' => array(
					'font-size'   => $m_cta_icon_size,
					'height'      => $m_cta_icon_size,
					'width'       => $m_cta_icon_size,
					'line-height' => $m_cta_icon_size,
				),
			);

			$t_selectors = array(
				' .wpsp-howto-child-cta-link .wpsp-ifb-button-icon' => array(
					'font-size'   => $t_cta_icon_size,
					'height'      => $t_cta_icon_size,
					'width'       => $t_cta_icon_size,
					'line-height' => $t_cta_icon_size,
				),
				' .wpsp-howto-child-cta-link .wpsp-ifb-text-icon' => array(
					'font-size'   => $t_cta_icon_size,
					'height'      => $t_cta_icon_size,
					'width'       => $t_cta_icon_size,
					'line-height' => $t_cta_icon_size,
				),
			);

			$combined_selectors = array(
				'desktop' => $selectors,
				'tablet'  => $t_selectors,
				'mobile'  => $m_selectors,
			);

			$combined_selectors = BSF_SP_Helper::get_typography_css( $attr, 'head', ' .wpsp-ifb-title', $combined_selectors );
			$combined_selectors = BSF_SP_Helper::get_typography_css( $attr, 'subHead', ' .wpsp-ifb-desc', $combined_selectors );
			$combined_selectors = BSF_SP_Helper::get_typography_css( $attr, 'cta', ' .wpsp-howto-child-cta-link', $combined_selectors );

			$base_selector = '.wp-block-wpsp-how-to-child.wpsp-block-';

			return BSF_SP_Helper::generate_all_css( $combined_selectors, $base_selector . $id );
		}

		/**
		 *  Prepare if class 'Schema_Pro_Loader' exist.
		 *  Kicking this off by calling 'get_instance()' method
		 */

	}
}

