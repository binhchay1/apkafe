<?php

namespace ahrefs\AhrefsSeo\Keywords;

use ahrefs\AhrefsSeo\Post_Tax;
use Exception;
/**
 * Dataset implementation for TF_IDF keywords search.
 */
class Keywords_Dataset {

	/**
	 * Target posts' content
	 *
	 * @var Data_Content_Storage|null Post content, Data_Content_Storage.
	 */
	private $target = null;
	/**
	 * Initialize dataset with posts, samples are all active posts id, targets is array with post id to find keywords for.
	 *
	 * @throws Exception On empty posts targets.
	 * @param Post_Tax $posts_tax Post tax instance.
	 */
	public function __construct( Post_Tax $posts_tax ) {
		$this->load_post_content( $posts_tax );
	}
	/**
	 * Get target
	 *
	 * @return Data_Content_Storage Post content, Data_Content_Storage.
	 */
	public function get_target() {
		return $this->target;
	}
	/**
	 * Words divided by space, all tags and some punctuation replaced by '|'.
	 *
	 * @param Post_Tax $post_tax Post Tax item (post or category) for load content from.
	 * @return void
	 */
	private function load_post_content( Post_Tax $post_tax ) {
		$this->target = null;
		if ( $post_tax->exists() ) {
			if ( function_exists( 'mb_strtolower' ) ) {
				$html = mb_strtolower( $post_tax->get_title() ) . '|' . mb_strtolower( $post_tax->load_content() );
			} else {
				$html = strtolower( $post_tax->get_title() ) . '|' . strtolower( $post_tax->load_content() );
			}
			$html = str_replace( [ '<', '>' ], [ ' |<', '>| ' ], $html ); // add special divider char '|' to all tags.
			$text = wp_strip_all_tags( $html, true ); // remove all html tags.
			$text = html_entity_decode( $text ); // replace html entities by chars.
			$text       = (string) preg_replace( '![ ]{2,}!', ' ', $text );
			$text       = (string) preg_replace( '/[,\\?!\\.\\{\\}\\[\\]\\(\\):;"]+[\\s+\\|]/', '|', $text );
			$text       = (string) preg_replace( '/[\\s+\\|][,!\\?\\.\\{\\}\\[\\]\\(\\):;"]+/', '|', $text );
			$text       = str_replace( [ '| | |', '| |', '|||', '||', '| ', ' |' ], [ '|', '|', '|', '|', '|', '|' ], $text );
			$substrings = (array) preg_split( '/[\\pZ\\pC]+/u', $text, -1, PREG_SPLIT_NO_EMPTY );
			// split by any utf-8 space.
			$this->target = ( new Data_Content_Storage() )->set_content( implode( ' ', $substrings ) ); // and make a string again.
		}
	}
}