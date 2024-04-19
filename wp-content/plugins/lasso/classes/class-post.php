<?php
/**
 * Declare class Post
 *
 * @package Post
 */

namespace Lasso\Classes;

use Lasso_Affiliate_Link;

use Lasso\Classes\Helper as Lasso_Helper;
use Lasso\Classes\Link_Location as Lasso_Link_Location;
use Lasso\Classes\Elementor as Lasso_Elementor;

use Lasso_Cron;
use simple_html_dom;

/**
 * Handler wp_posts
 * Post
 */
class Post {

	/**
	 * WP_Post
	 *
	 * @var array|WP_Post|null
	 */
	private $post;

	/**
	 * Lasso ID
	 *
	 * @var int
	 */
	private $lasso_id;

	/**
	 * Lasso URL
	 *
	 * @var object
	 */
	private $lasso_url;

	const MODE_SAVE     = 'save';
	const MODE_MONETIZE = 'monetize';
	const MODE_REMOVE   = 'remove';

	/**
	 * Post constructor.
	 *
	 * @param int    $post_id   WP_Post ID.
	 * @param int    $lasso_id  Lasso ID.
	 * @param object $lasso_url Lasso url.
	 */
	public function __construct( $post_id, $lasso_id, $lasso_url = null ) {
		$this->post      = get_post( $post_id );
		$this->lasso_id  = $lasso_id;
		$this->lasso_url = $lasso_url ? $lasso_url : Lasso_Affiliate_Link::get_lasso_url( $post_id );
	}

	/**
	 * Init instance
	 *
	 * @param int    $post_id   WP_Post ID.
	 * @param object $lasso_url Lasso url.
	 *
	 * @return Post
	 */
	public static function create_instance( $post_id, $lasso_url = null ) {
		return new self( $post_id, null, $lasso_url );
	}

	/**
	 * Get wp post ID
	 *
	 * @return int
	 */
	public function get_post_id() {
		return $this->post->ID;
	}

	/**
	 * Get lasso ID
	 *
	 * @return mixed
	 */
	public function get_lasso_id() {
		return $this->lasso_id;
	}

	/**
	 * Override content by add link location ID
	 *
	 * @param int    $link_location_id Link location ID.
	 * @param string $content          Content.
	 *
	 * @return string
	 */
	public function index_link_location( $link_location_id, $content = '' ) {
		if ( empty( $content ) ) {
			$post_content = $this->post->post_content;
		} else {
			$post_content = $content;
		}

		$post_content = str_replace( array( 'href=\">', 'href=">' ), 'href="#">', $post_content );
		$post_content = str_replace( array( 'class="\">', 'class=">', 'class=\"\\\">', 'class=\">' ), 'class="">', $post_content );
		$post_content = str_replace( array( 'data-lasso-id="\">', 'data-lasso-id=">' ), 'data-lasso-id="">', $post_content );

		// ? get lasso url from lasso id
		$lasso_url = Lasso_Affiliate_Link::get_lasso_url( $this->lasso_id );

		$html = new simple_html_dom();
		$html->load( $post_content, true, false );

		$a_tags = $html->find( 'a' );
		foreach ( $a_tags as $a ) {
			$a->setAttribute( 'data-lasso-id', $link_location_id );

			if ( $lasso_url->lasso_id > 0 && ( $a->href === $lasso_url->permalink || self::remove_param_url( $a->href ) === self::remove_param_url( $lasso_url->target_url ) ) ) {
				$a->target = ( $lasso_url->open_new_tab ) ? '_blank' : false;
				$a->setAttribute( 'data-lasso-name', $lasso_url->name );

				$a->rel = $lasso_url->enable_nofollow ? 'nofollow' : null;
				if ( ! empty( $lasso_url->enable_sponsored ) ) {
					$rel    = str_replace( 'sponsored', '', $a->rel );
					$rel    = $rel . ' sponsored';
					$a->rel = Lasso_Helper::trim( $rel );
				}

				if ( '_blank' === $a->target ) {
					$rel    = str_replace( array( 'noopener', 'noreferrer', '1' ), '', $a->rel );
					$rel    = $rel . ' noopener';
					$a->rel = Lasso_Helper::trim( $rel );
				}
			}
		}

		return $this->cast_to_string( $html );
	}

	/**
	 * Override content by remove link location ID
	 *
	 * @param string|null $content Content.
	 *
	 * @return string
	 */
	public function remove_index_link_location( $content = '' ) {
		if ( empty( $content ) ) {
			$post_content = $this->post->post_content;
		} else {
			$post_content = $content;
		}
		$html = new simple_html_dom();
		$html->load( $post_content, true, false );

		$a_tags = $html->find( 'a' );
		foreach ( $a_tags as $a ) {
			$a->removeAttribute( 'data-lasso-id' );
			$a->removeAttribute( 'data-lasso-name' );
		}
		return $this->cast_to_string( $html );
	}

	/**
	 * Update Link
	 *
	 * @param int    $link_location_id Link location ID.
	 * @param string $new_url          Link to redirect.
	 * @param string $content          Content.
	 * @param string $new_anchor_text  Anchor text.
	 *
	 * @return string
	 */
	public function update_link_to_content( $link_location_id, $new_url, $content = '', $new_anchor_text = '' ) {
		if ( empty( $content ) ) {
			$post_content = $this->post->post_content;
		} else {
			$post_content = $content;
		}

		// ? keep break line for "php simple html dom" library
		// ? https://stackoverflow.com/questions/4812691/preserve-line-breaks-simple-html-dom-parser
		$html = new simple_html_dom();
		$html->load( $post_content, true, false );

		$a_tags = $html->find( 'a' ); // ? Find a tags in the html

		// ? Handle urls
		foreach ( $a_tags as $a ) {
			$a_lasso_id = $a->getAttribute( 'data-lasso-id' );
			$a_lasso_id = intval( $a_lasso_id );
			$a_lasso_id = $a_lasso_id && $a_lasso_id > 0 ? $a_lasso_id : 0;

			$lasso_link_location = new Lasso_Link_Location( $a_lasso_id );
			if ( $lasso_link_location->get_id() === $link_location_id ) {
				$a->href = $new_url;
				if ( '' !== $new_anchor_text ) {
					$a->innertext = $new_anchor_text;
				}
				$a->rel    = Lasso_Affiliate_Link::enable_nofollow_noindex( $this->lasso_id ) ? 'nofollow' : null;
				$a->target = Lasso_Affiliate_Link::open_new_tab( $this->lasso_id ) ? '_blank' : false;

				// ? fix tabnabbing issue
				if ( '_blank' === $a->target ) {
					$rel    = trim( strval( $a->rel ?? '' ) );
					$rel    = str_replace( array( 'noopener', 'noreferrer', '1' ), '', $rel );
					$rel    = trim( $rel . ' noopener' );
					$a->rel = $rel;
				}
				break;
			}
		}

		return $this->cast_to_string( $html );
	}

	/**
	 * Get JSON meta
	 *
	 * @param string $key Meta key.
	 *
	 * @return array
	 */
	public function get_json_meta( $key ) {
		$meta = get_post_meta( $this->post->ID, $key, true );

		if ( is_string( $meta ) && ! empty( $meta ) ) {
			$meta = json_decode( $meta, true );
		}

		if ( empty( $meta ) ) {
			$meta = array();
		}

		return $meta;
	}

	/**
	 * Cast to string
	 *
	 * @param string $str String.
	 *
	 * @return string
	 */
	private function cast_to_string( $str ) {
		return (string) $str;
	}

	/**
	 * Do update content for plugin
	 *
	 * @param int    $post_id          Post ID.
	 * @param string $mode             Mode.
	 * @param null   $lasso_id         Lasso ID.
	 * @param null   $link_location_id Link location ID.
	 * @param null   $redirect_url     Link to redirect.
	 */
	public static function update_content_to_plugin( $post_id, $mode = self::MODE_SAVE, $lasso_id = null, $link_location_id = null, $redirect_url = null ) {
		if ( class_exists( Lasso_Elementor::class ) && Lasso_Helper::is_wp_elementor_plugin_actived() && Lasso_Helper::is_built_with_elementor( $post_id ) ) {
			$lasso_elementor = new Lasso_Elementor( $post_id, $lasso_id, $mode );
			$lasso_elementor->set_link_location_id( $link_location_id );
			$lasso_elementor->set_link_redirect( $redirect_url );
			$lasso_elementor->update_content();
		}
	}

	/**
	 * Check to hide description base on display type
	 *
	 * @return bool
	 */
	public function is_show_description() {
		if ( $this->lasso_url->display->show_description ) {
			return true;
		}

		$is_show     = true;
		$description = preg_replace( '/ +/', ' ', $this->lasso_url->description );
		preg_match( '/<p>\s<\/p>/', $description, $output_array );
		if ( in_array( $this->lasso_url->description, array( '', '<p><br></p>' ), true ) || 1 <= count( $output_array ) ) {
			$is_show = false;
		} else {
			update_post_meta( $this->get_post_id(), 'show_description', 1 );
		}

		return $is_show;
	}

	/**
	 * Check to hide title base on display type
	 *
	 * @return bool
	 */
	public function is_show_title() {
		$is_show = true;
		if ( empty( $this->lasso_url->name ) ) {
			$is_show = false;
		}
		return $is_show;
	}

	/**
	 * Remove param url
	 *
	 * @param string $url Url.
	 *
	 * @return string
	 */
	public function remove_param_url( $url ) {
		return preg_replace( '/\?.*/', '', $url );
	}
}
