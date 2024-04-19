<?php
/**
 * Declare class Elementor
 *
 * @package Elementor
 */

namespace Lasso\Classes;

use Lasso\Classes\Post as Lasso_Post;
use Lasso\Classes\Helper as Lasso_Helper;

use Lasso_Cron;
use simple_html_dom;

/**
 *
 * Elementor
 */
class Elementor {
	/**
	 * Lasso Post
	 *
	 * @var Lasso_Post Lasso_Post Lasso Post class.
	 */
	private $lasso_post;

	/**
	 * Link location ID
	 *
	 * @var int $link_location_id Link location ID.
	 */
	private $link_location_id;

	/**
	 *  Link to redirect
	 *
	 * @var string $link_redirect Link to redirect.
	 */
	private $link_redirect;

	/**
	 * Mode monetize or save
	 *
	 * @var string $mode Mode monetize etc...
	 */
	private $mode;

	/**
	 * Elementor plugin key to get data
	 *
	 * @var string $elementor_data_key Elementor plugin key to get data.
	 */
	private $elementor_data_key = '_elementor_data';

	/**
	 * Elementor constructor.
	 *
	 * @param int    $post_id  Post ID.
	 * @param int    $lasso_id Lasso ID.
	 * @param string $mode     Mode.
	 */
	public function __construct( $post_id, $lasso_id, $mode ) {
		$this->lasso_post = new Lasso_Post( $post_id, $lasso_id );
		$this->mode       = $mode;
	}

	/**
	 * Get mode
	 *
	 * @return mixed
	 */
	private function get_mode() {
		return $this->mode;
	}

	/**
	 * Get link location ID
	 *
	 * @return mixed
	 */
	public function get_link_location_id() {
		return (int) $this->link_location_id;
	}

	/**
	 * Set link location ID
	 *
	 * @param int $link_location_id Link location ID.
	 */
	public function set_link_location_id( $link_location_id ) {
		$this->link_location_id = $link_location_id;
	}

	/**
	 * Get link to redirect
	 *
	 * @return mixed
	 */
	public function get_link_redirect() {
		return $this->link_redirect;
	}

	/**
	 * Set link to redirect
	 *
	 * @param string $link_redirect Link to redirect.
	 */
	public function set_link_redirect( $link_redirect ) {
		$this->link_redirect = $link_redirect;
	}

	/**
	 * Override post meta data of Elementor
	 */
	public function update_content() {
		$editor_data = $this->get_data();
		$this->update_link( $editor_data, $this->get_mode() );

		// ? We need the `wp_slash` in order to avoid the unslashing during the `update_post_meta`
		$json_value = wp_slash( wp_json_encode( $editor_data ) );
		update_metadata( 'post', $this->lasso_post->get_post_id(), $this->elementor_data_key, $json_value );
	}

	/**
	 * Get data key
	 *
	 * @return array
	 */
	public function get_data_key() {
		return $this->elementor_data_key;
	}

	/**
	 * Get data
	 *
	 * @return array
	 */
	public function get_data() {
		return $this->lasso_post->get_json_meta( $this->elementor_data_key );
	}

	/**
	 * Lasso will override post meta of Elementor
	 *
	 * @param array  $elements Element data.
	 * @param string $mode     Mode.
	 */
	private function update_link( &$elements, $mode ) {
		foreach ( $elements as $key => &$element ) {
			if ( isset( $element['settings']['editor'] ) ) {
				$content          = $element['settings']['editor'];
				$link_location_id = $this->get_link_location_id();

				if ( Lasso_Post::MODE_SAVE === $mode && ! empty( $link_location_id ) ) {
					$content = $this->lasso_post->index_link_location( $this->get_link_location_id(), $content );
				} elseif ( Lasso_Post::MODE_MONETIZE === $mode && ! empty( $this->get_link_redirect() ) && ! empty( $this->get_link_location_id() ) ) {
					$content = $this->lasso_post->update_link_to_content( $this->get_link_location_id(), $this->get_link_redirect(), $content );
				} elseif ( Lasso_Post::MODE_REMOVE === $mode ) {
					$content = $this->lasso_post->remove_index_link_location( $content );
				}
				$element['settings']['editor'] = $content;
			}

			// ? check childrent elements
			if ( isset( $element['elements'] ) ) {
				$this->update_link( $element['elements'], $mode );
			}
		}
	}

	/**
	 * Lasso will override post meta of Elementor
	 *
	 * @param array  $elements           Element data.
	 * @param string $mode               Mode.
	 * @param array  $lasso_location_ids Lasso location ids.
	 */
	public function scan_site_stripe_data( &$elements, $mode, &$lasso_location_ids ) {
		$cron    = new Lasso_Cron();
		$post_id = $this->lasso_post->get_post_id();

		foreach ( $elements as $key => &$element ) {
			if ( isset( $element['settings']['html'] ) ) {
				$element['settings']['html'] = $this->scan_site_stripe_html( $post_id, $element['settings']['html'], $lasso_location_ids );
			}
			if ( isset( $element['settings']['editor'] ) ) {
				$element['settings']['editor'] = $this->scan_site_stripe_html( $post_id, $element['settings']['editor'], $lasso_location_ids );
			}
			if ( isset( $element['settings']['shortcode'] ) ) {
				$element['settings']['shortcode'] = $this->scan_site_stripe_html( $post_id, $element['settings']['shortcode'], $lasso_location_ids );
			}
			if ( isset( $element['settings']['image']['url'] ) ) {
				$content = '';
				$img_url = $element['settings']['image']['url'];
				$img_url = $cron->scan_site_stripe_image_url( $img_url, $post_id, $content, $lasso_location_ids );

				$element['settings']['image']['url'] = $img_url;
			}

			// ? check childrent elements
			if ( isset( $element['elements'] ) ) {
				$this->scan_site_stripe_data( $element['elements'], $mode, $lasso_location_ids );
			}
		}
	}

	/**
	 * Scan SiteStripe data from content.
	 *
	 * @param int    $post_id Post ID.
	 * @param string $content Content.
	 * @param array  $lasso_location_ids Link location IDs.
	 *
	 * @return string
	 */
	public function scan_site_stripe_html( $post_id, $content, &$lasso_location_ids ) {
		$html = new simple_html_dom();
		$html->load( $content, true, false );

		$img_tags    = $html->find( 'img' ); // ? Find image tags in the html (SiteStripe images)
		$iframe_tags = $html->find( 'iframe' ); // ? Find iframe tags in the html (SiteStripe images)

		$cron = new Lasso_Cron();
		$cron->scan_site_stripe_data_image( $post_id, $img_tags, $lasso_location_ids );
		$cron->scan_site_stripe_data_iframe( $post_id, $iframe_tags, $lasso_location_ids );

		return (string) $html;
	}

	/**
	 * Replaces the old shortcode with the new shortcode in the post's Elementor data
	 *
	 * @param int    $post_id The post ID of the post want to update.
	 * @param string $old_shortcode The shortcode want to replace.
	 * @param string $replace_with The new shortcode want to replace the old one with.
	 * @param array  $elementor_ids_scanned List elementor id scanned.
	 * @param array  $elementor_data Elementor data.
	 */
	public static function fix_shortcode_elementor( $post_id, $old_shortcode, $replace_with, &$elementor_ids_scanned, &$elementor_data ) {
		if ( Lasso_Helper::is_wp_elementor_plugin_actived() && Lasso_Helper::is_built_with_elementor( $post_id ) ) {
			$elementor_id = self::replace_elementor_shortcode( $elementor_data, $old_shortcode, $replace_with, $elementor_ids_scanned );

			if ( $elementor_id ) {
				$elementor_ids_scanned[] = $elementor_id;
			}
		}
	}

	/**
	 * Replaces the shortcode in the Elementor editor with the new shortcode
	 *
	 * @param array  $elements The array of elements that you want to search through.
	 * @param string $old_shortcode The shortcode want to replace.
	 * @param string $replace_with The shortcode want to replace the old shortcode with.
	 * @param array  $elementor_ids_scanned List elementor id scanned.
	 */
	public static function replace_elementor_shortcode( &$elements, $old_shortcode, $replace_with, $elementor_ids_scanned ) {
		foreach ( $elements as &$element ) {
			$widget_type = $element['widgetType'] ?? null;

			if ( 'shortcode' === $widget_type ) {
				$element_id = $element['id'] ?? '';
				if ( in_array( $element_id, $elementor_ids_scanned, true ) ) {
					continue;
				}

				$elementor_shortcode = $element['settings']['shortcode'] ?? '';
				$elementor_shortcode = str_replace( '[lasso rel="', '[lasso ref="', $elementor_shortcode );
				$elementor_shortcode = trim( $elementor_shortcode );
				// ? if original_link is not a shortcode, set it to empty
				// ? this won't replace a shortcode with a link and show a raw link in post content
				$elementor_shortcode = strpos( $elementor_shortcode, '[' ) !== false && strpos( $elementor_shortcode, ']' ) ? $elementor_shortcode : '';

				// ? Get shortcode type
				preg_match( '/' . get_shortcode_regex() . '/s', $elementor_shortcode, $matches );
				$shortcode_type = $matches[2];

				if ( 'lasso' === $shortcode_type ) {
					$element['settings']['shortcode'] = $elementor_shortcode === $old_shortcode
						? $replace_with
						: $element['settings']['shortcode'];

					return $element['id'];
				}
			} elseif ( 'lasso_shortcode' === $widget_type ) {
				$element_id = $element['id'] ?? '';
				if ( in_array( $element_id, $elementor_ids_scanned, true ) ) {
					continue;
				}

				$elementor_shortcode = $element['settings']['lasso_shortcode'] ?? '';
				$elementor_shortcode = str_replace( '[lasso rel="', '[lasso ref="', $elementor_shortcode );
				$elementor_shortcode = trim( $elementor_shortcode );
				// ? if original_link is not a shortcode, set it to empty
				// ? this won't replace a shortcode with a link and show a raw link in post content
				$elementor_shortcode = strpos( $elementor_shortcode, '[' ) !== false && strpos( $elementor_shortcode, ']' ) ? $elementor_shortcode : '';

				// ? Get shortcode type
				preg_match( '/' . get_shortcode_regex() . '/s', $elementor_shortcode, $matches );
				$shortcode_type = $matches[2];

				if ( 'lasso' === $shortcode_type ) {
					$element['settings']['lasso_shortcode'] = $elementor_shortcode === $old_shortcode
						? $replace_with
						: $element['settings']['lasso_shortcode'];

					return $element['id'];
				}
			} elseif ( 'text-editor' === $widget_type ) {
				$widget_editor_content = $element['settings']['editor'];
				$widget_editor_content = str_replace( '[lasso rel="', '[lasso ref="', $widget_editor_content );
				$pos                   = strpos( $widget_editor_content, $old_shortcode );

				if ( false !== $pos ) {
					$widget_editor_content         = substr_replace( $widget_editor_content, $replace_with, $pos, strlen( $old_shortcode ) );
					$element['settings']['editor'] = $widget_editor_content;

					return $element['id'];
				}
			} elseif ( isset( $element['elements'] ) ) {
				if ( is_array( $element['elements'] ) && ! empty( $element['elements'] ) ) {
					$element_id = self::replace_elementor_shortcode( $element['elements'], $old_shortcode, $replace_with, $elementor_ids_scanned );
					if ( $element_id ) {
						return $element_id;
					}
				}
			}
		}

		return false;
	}

	/**
	 * Get elementor data
	 *
	 * @param int $post_id Post ID.
	 *
	 * @return array
	 */
	public static function get_elementor_data( $post_id ) {
		$elementor_data = array();

		if ( Lasso_Helper::is_wp_elementor_plugin_actived() && Lasso_Helper::is_built_with_elementor( $post_id ) ) {
			$elementor_data = Lasso_Helper::get_elementor_json_meta( $post_id, '_elementor_data' );
		}

		return $elementor_data;
	}

	/**
	 * Update elementor data
	 *
	 * @param int   $post_id Post ID.
	 * @param array $elementor_data Elementor data.
	 */
	public static function update_elementor_data( $post_id, $elementor_data ) {
		if ( Lasso_Helper::is_wp_elementor_plugin_actived() && Lasso_Helper::is_built_with_elementor( $post_id ) && ! empty( $elementor_data ) ) {
			// ? We need the `wp_slash` in order to avoid the unslashing during the `update_post_meta`
			$json_value = wp_slash( wp_json_encode( $elementor_data ) );

			// ? Don't use `update_post_meta` that can't handle `revision` post type
			update_metadata( 'post', $post_id, '_elementor_data', $json_value );
		}
	}
}
