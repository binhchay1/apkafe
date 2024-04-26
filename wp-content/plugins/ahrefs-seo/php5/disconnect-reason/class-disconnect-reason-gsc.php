<?php

namespace ahrefs\AhrefsSeo\Disconnect_Reason;

use ahrefs\AhrefsSeo\Messages\Message;
/**
 * Disconnect reason for Google Search Console class.
 *
 * @since 0.8.4
 */
class Disconnect_Reason_GSC extends Disconnect_Reason_Google {

	/**
	 * Get message from text string
	 *
	 * @param string $string Message text.
	 * @return Message|null
	 */
	protected function text_to_message( $string ) {
		return Message::gsc_disconnected( $string, true );
	}
}