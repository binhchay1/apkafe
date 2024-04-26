<?php

declare(strict_types=1);

namespace ahrefs\AhrefsSeo\Disconnect_Reason;

use ahrefs\AhrefsSeo\Messages\Message;

/**
 * Abstract Disconnect reason class.
 *
 * @since 0.8.4
 */
abstract class Disconnect_Reason {

	protected const OPTION_NAME = '';
	/**
	 * @var Message|null
	 */
	protected $cached_reason;

	/**
	 * Save disconnect reason
	 *
	 * @param string|null $string Message text.
	 * @return void
	 */
	public function save_reason( ?string $string = null ) : void {
		$message = ! is_null( $string ) ? $this->text_to_message( $string ) : null;
		update_option( static::OPTION_NAME, ! is_null( $message ) ? $message->save_json() : null );
		$this->cached_reason = $message;
	}

	/**
	 * Get disconnect reason
	 *
	 * @return Message|null
	 */
	public function get_reason() : ?Message {
		if ( is_null( $this->cached_reason ) ) {
			$json                = get_option( static::OPTION_NAME, null );
			$this->cached_reason = ! is_null( $json ) ? Message::load_json( $json ) : null;
		}
		return $this->cached_reason;
	}

	/**
	 * Clean disconnect reason
	 *
	 * @return void
	 */
	public function clean_reason() : void {
		delete_option( static::OPTION_NAME );
		$this->cached_reason = null;
	}

	/**
	 * Get message from text string
	 *
	 * @param string $string Message text.
	 * @return Message|null
	 */
	abstract protected function text_to_message( string $string ) : ?Message;
}
