<?php

namespace ahrefs\AhrefsSeo;

use ahrefs\AhrefsSeo\Messages\Message;
/**
 * Abstract class for settings.
 */
abstract class Settings_Any {

	/**
	 * Load options from request.
	 *
	 * @param Ahrefs_Seo_Screen $screen Screen instance.
	 * @return string|null Error message if any.
	 */
	public abstract function apply_options( Ahrefs_Seo_Screen $screen );
	/**
	 * Show options block.
	 *
	 * @param Ahrefs_Seo_Screen $screen Screen instance.
	 * @param Ahrefs_Seo_View   $view View instance.
	 * @param Message|null      $error Message with already happened error if any.
	 *
	 * @return void
	 */
	public abstract function show_options( Ahrefs_Seo_Screen $screen, Ahrefs_Seo_View $view, Message $error = null );
}