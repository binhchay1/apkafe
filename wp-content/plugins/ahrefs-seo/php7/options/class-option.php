<?php

declare(strict_types=1);

namespace ahrefs\AhrefsSeo\Options;

/**
 * Options class.
 *
 * @since 0.9.4
 */
abstract class Option {
	protected const OPTION_BASE = 'ahrefs-seo-options-a-';

	/** @var bool */
	protected $is_enabled = false;

	/**
	 * Is this option enabled by user?
	 *
	 * @return bool
	 */
	public function is_enabled() : bool {
		return $this->is_enabled;
	}
	/**
	 * Enable option.
	 *
	 * @param bool $enabled This option is enabled.
	 * @return Option
	 */
	public function set_enabled( bool $enabled = true ) : self {
		$this->load_options();
		$this->is_enabled = $enabled;
		$this->save_options();
		return $this;
	}

	/**
	 * Render view with options
	 *
	 * @return void
	 */
	abstract public function render_view() : void;
	/**
	 * The view has a section with options vs single option only
	 *
	 * @return bool
	 */
	public function has_sub_options() : bool {
		return false;
	}
	/**
	 * Get options hash.
	 *
	 * @return string
	 */
	public function get_options_hash() : string {
		return str_replace( __NAMESPACE__, '', get_class( $this ) ) . ( $this->has_sub_options() ? 'F' : 'S' ) . ( $this->is_enabled ? 'E' : 'D' );
	}
	/**
	 * Load options from request
	 *
	 * @return bool Success.
	 */
	public function load_options_from_request() : bool {
		$this->save_options();
		return true;
	}
	/**
	 * Import options from older plugin versions.
	 *
	 * @return void
	 */
	abstract public function import_from_older_version() : void;

	/**
	 * Save options to DB
	 *
	 * @return self
	 */
	abstract protected function save_options() : self;
	/**
	 * Load options from DB
	 *
	 * @return self
	 */
	abstract protected function load_options() : self;
	/**
	 * Get name for option save/load.
	 *
	 * @return string Option name.
	 */
	abstract protected function get_option_name() : string;
	/**
	 * Return name of var for using in render and load from request option
	 *
	 * @param string $suffix Suffix for the variable name.
	 * @return string
	 */
	abstract protected function get_var_name( string $suffix = '' ) : string;
}
