<?php

declare(strict_types=1);

namespace ahrefs\AhrefsSeo\Content_Tips;

use ahrefs\AhrefsSeo\Ahrefs_Seo;

/**
 * Class for content audit tips: handle events.
 *
 * @since 0.8.4
 */
abstract class Tip {

	/**
	 * Define unique ID for each child class.
	 */
	public const ID = 'tip';
	/**
	 * Define here correct template for each child class.
	 */
	protected const TEMPLATE = '';
	/**
	 * Templates subdir for all content tips.
	 */
	protected const TEMPLATE_SUBDIR = 'content-tips/';

	/**
	 * @var TipData
	 */
	protected $data;

	/**
	 * Constructor
	 *
	 * @param TipData|null $data Tip Data instance to use.
	 */
	public function __construct( ?TipData $data = null ) {
		$this->data = ! is_null( $data ) ? $data : new TipData();
	}

	/**
	 * Set Tip Data instance to use
	 *
	 * @param TipData $data Tip Data instance.
	 * @return void
	 */
	public function set_data( TipData $data ) : void {
		$this->data = $data;
	}

	/**
	 * Display tip content
	 *
	 * @param bool $show_hidden Show block as hidden.
	 * @return void
	 */
	public function show( bool $show_hidden = false ) : void {
		$view = Ahrefs_Seo::get()->get_view();
		if ( $show_hidden ) {
			$view->show_part(
				$this::TEMPLATE_SUBDIR . 'header-hidden',
				[
					'tip_id'      => $this::ID,
					'show_hidden' => $show_hidden,
				]
			);
		}
		$view->show_part(
			$this::TEMPLATE_SUBDIR . $this::TEMPLATE,
			[
				'tip_id'      => $this::ID,
				'show_hidden' => $show_hidden,
			]
		);
		if ( $show_hidden ) {
			$view->show_part(
				$this::TEMPLATE_SUBDIR . 'footer-hidden',
				[
					'tip_id'      => $this::ID,
					'show_hidden' => $show_hidden,
				]
			);
		}
	}

	/**
	 * Display tip on screen if allowed.
	 *
	 * @return void
	 */
	public function maybe_show_tip() : void {
		if ( $this->need_to_show() ) {
			$this->show();
		}
	}

	/**
	 * Need to show the tip.
	 * Basically when it was not closed by user.
	 *
	 * @return bool
	 */
	public function need_to_show() : bool {
		return ! $this->data->is_tip_closed( $this );
	}

	/**
	 * Hide tip when it was closed by user
	 *
	 * @return void
	 */
	public function hide() : void {
		$this->data->set_tip_closed( $this, true );
	}

	/**
	 * Allow to show tip, reset "closed by user"
	 *
	 * @return void
	 */
	public function allow() : void {
		$this->data->set_tip_closed( $this, false );
	}

}
