<?php

namespace ahrefs\AhrefsSeo\Disconnect_Reason;

use ahrefs\AhrefsSeo\Messages\Message;
/**
 * Disconnect reason for Google API class.
 *
 * @since 0.8.4
 */
class Disconnect_Reason_Google extends Disconnect_Reason {

	const OPTION_NAME = 'ahrefs-seo-has-gsc-disconnect-reason';
	/**
	 * @param string $string Message text.
	 *
	 * @return Message|null
	 */
	protected function text_to_message( $string ) {
		return Message::google_disconnected( $string );
	}
}