<?php

declare(strict_types=1);

namespace ahrefs\AhrefsSeo\Content_Tips;

/**
 * Get tip by ID and group actions for all tips.
 *
 * @since 0.8.4
 */
class Tips {

	/**
	 * Return tip instance
	 *
	 * @param string       $tip_id Tip ID.
	 * @param TipData|null $data Tip Data instance.
	 * @return Tip_Dropped_Article|Tip_Duplicated|Tip_Keywords_Popup|Tip_Last_Audit|Tip_Suggested_First|Tip_Suggested_Subsequent|Tip_Expand_Suggestion|null Null if no tip for ID found.
	 */
	public static function get( string $tip_id, ?TipData $data = null ) : ?Tip {
		switch ( strtolower( $tip_id ) ) {
			case Tip_Dropped_Article::ID:
				return new Tip_Dropped_Article( $data );
			case Tip_Duplicated::ID:
				return new Tip_Duplicated( $data );
			case Tip_Keywords_Popup::ID:
				return new Tip_Keywords_Popup( $data );
			case Tip_Last_Audit::ID:
				return new Tip_Last_Audit( $data );
			case Tip_Suggested_First::ID:
				return new Tip_Suggested_First( $data );
			case Tip_Suggested_Subsequent::ID:
				return new Tip_Suggested_Subsequent( $data );
			case Tip_Expand_Suggestion::ID:
				return new Tip_Expand_Suggestion( $data );
		}
		return null;
	}

	/**
	 * Get all existing tip instances
	 *
	 * @param TipData|null $data Tip Data instance.
	 * @return Tip[]
	 */
	protected static function get_all( ?TipData $data = null ) : array {
		return [
			new Tip_Dropped_Article( $data ),
			new Tip_Duplicated( $data ),
			new Tip_Keywords_Popup( $data ),
			new Tip_Last_Audit( $data ),
			new Tip_Suggested_First( $data ),
			new Tip_Suggested_Subsequent( $data ),
			new Tip_Expand_Suggestion( $data ),
		];
	}

	/**
	 * Get all tip instances for Content Audit screen.
	 * The order of the tips:
	 *   Last audit expired
	 *   Has suggested keywords
	 *   Has duplicated keywords
	 *   Has drops from well-performing
	 *
	 * @param TipData|null $data Tip Data instance.
	 * @return Tip[]
	 */
	public static function at_content_screen( ?TipData $data = null ) : array {
		return [
			new Tip_Last_Audit( $data ),
			new Tip_Suggested_First( $data ),
			new Tip_Suggested_Subsequent( $data ),
			new Tip_Duplicated( $data ),
			new Tip_Dropped_Article( $data ),
		];
	}

	/**
	 * Set all tips allowed (so user maybe will see and close them)
	 *
	 * @param TipData|null $data Tip Data instance.
	 * @return void
	 */
	public static function set_all_tips_allowed( ?TipData $data ) : void {
		foreach ( self::get_all( $data ) as $tip ) {
			$tip->allow();
		}
	}

	/**
	 * Maybe no need to show some tips: check each tip.
	 *
	 * @param TipData|null $data Tip Data instance.
	 * @return void
	 */
	public static function maybe_do_not_show_more( ?TipData $data ) : void {
		foreach ( self::get_all( $data ) as $tip ) {
			if ( ! $tip->need_to_show() ) {
				$tip->hide();
			}
		}
	}

}
