<?php
declare(strict_types=1);
namespace ahrefs\AhrefsSeo\Messages;

use ahrefs\AhrefsSeo\Ahrefs_Seo_Compatibility;

/**
 * Incompatible tip message
 *
 * @since 0.7.5
 */
class Message_Tip_Incompatible extends Message_Tip {

	protected const TEMPLATE = 'tip-incompatible';

	/** @var string[] */
	protected $plugins = [];
	/** @var string[] */
	protected $themes = [];

	/**
	 * Create message from fields.
	 *
	 * @param array<string,mixed> $message_fields Message fields.
	 */
	public function __construct( array $message_fields ) {
		parent::__construct( $message_fields );
		$this->type = 'tip-compatibility'; // overwrite type.
		// what was a reason of incompatibility.
		$this->plugins = $message_fields['plugins'] ?? [];
		$this->themes  = $message_fields['themes'] ?? [];
	}

	/**
	 * Return fields of message.
	 *
	 * @return array<string, string|string[]|bool>
	 */
	protected function get_fields() : array {
		$result            = parent::get_fields();
		$result['plugins'] = $this->plugins;
		$result['themes']  = $this->themes;
		return $result;
	}

	/**
	 * Show template with message
	 *
	 * @return void
	 */
	public function show() : void {
		parent::show();
		Ahrefs_Seo_Compatibility::set_message_displayed( $this->message ); // important: set message displayed.
	}

	/**
	 * Get incompatible plugins list
	 *
	 * @return string[] Plugins list or empty array.
	 */
	public function get_plugins() : array {
		return $this->plugins;
	}

	/**
	 * Get incompatible themes list
	 *
	 * @return string[] Themes list or empty array.
	 */
	public function get_themes() : array {
		return $this->themes;
	}
}
