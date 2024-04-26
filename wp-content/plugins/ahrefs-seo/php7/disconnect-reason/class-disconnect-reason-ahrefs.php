<?php

declare(strict_types=1);

namespace ahrefs\AhrefsSeo\Disconnect_Reason;

use ahrefs\AhrefsSeo\Messages\Message;

/**
 * Disconnect reason for Ahrefs class.
 *
 * @since 0.8.4
 */
class Disconnect_Reason_Ahrefs extends Disconnect_Reason {

	protected const OPTION_NAME = 'ahrefs-seo-has-ahrefs-disconnect-reason';

	/**
	 * Get message from text string
	 *
	 * @param string $string Ahrefs token.
	 * @return Message|null
	 */
	protected function text_to_message( string $string ) : ?Message {
		return Message::account_expired( $string, false );
	}
}
