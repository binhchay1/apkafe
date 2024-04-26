<?php
declare(strict_types=1);
namespace ahrefs\AhrefsSeo\Messages;

use ahrefs\AhrefsSeo\Ahrefs_Seo;
use ahrefs\AhrefsSeo\Ahrefs_Seo_Api;
use ahrefs\AhrefsSeo\Ahrefs_Seo_Errors;
use ahrefs\AhrefsSeo\Ahrefs_Seo_View;

/**
 * Base class for all messages: error, notice, tip.
 *
 * @since 0.7.5
 */
abstract class Message {

	public const TYPE_ACCESS            = 'access';
	public const TYPE_ERROR             = 'error';
	public const TYPE_ERROR_SINGLE      = 'error-single';
	public const TYPE_TIP               = 'tip';
	public const TYPE_TIP_COMPATIBILITY = 'tip-compatibility';
	public const TYPE_NOTICE            = 'notice';

	protected const TEMPLATES_BASE = 'dynamic/';
	protected const TEMPLATE       = 'error';

	/** @var string $id */
	protected $id;
	/** @var string $type */
	protected $type;
	/**  @var string $title */
	protected $title;
	/**  @var string $message */
	protected $message;
	/**  @var string[] $buttons */
	protected $buttons;
	/**  @var string[] $classes */
	protected $classes;
	/**  @var bool $not_dismissible */
	protected $not_dismissible;

	/**
	 * Create message from fields.
	 *
	 * @param array<string,mixed> $message_fields {
	 *   Message fields.
	 *
	 *   @type string $message Message text.
	 *   @type string|null $title Optional title.
	 *   @type string|null $type Type, one of Message::TYPE_*.
	 *   @type string|string[]|null $classes Additional classes for message html container.
	 *   @type string|null $source Source of message, one of: 'ahrefs', 'google', 'compatibility', 'general', 'noindex', 'WordPress', 'content audit'.
	 *   @type string[]|null $buttons Optional buttons list, @see dynamic/buttons template.
	 * }
	 */
	public function __construct( array $message_fields ) {
		$this->type    = $message_fields['type'] ?? $this::TYPE_ERROR;
		$this->classes = [];
		if ( isset( $message_fields['classes'] ) ) {
			$this->classes = is_array( $message_fields['classes'] ) ? $message_fields['classes'] : ( is_string( $message_fields['classes'] ) ? [ $message_fields['classes'] ] : [] );
		}

		$this->title = '';
		if ( isset( $message_fields['title'] ) ) {
			$this->title = $message_fields['title'];
		} elseif ( isset( $message_fields['source'] ) ) {
			$this->title = Ahrefs_Seo_Errors::get_title_for_source( $message_fields['source'] );
		}
		$this->message         = $message_fields['message'] ?? '';
		$this->id              = md5( $this->message );
		$this->buttons         = isset( $message_fields['buttons'] ) && is_array( $message_fields['buttons'] ) ? $message_fields['buttons'] : [];
		$this->not_dismissible = $message_fields['not_dismissible'] ?? false;
	}

	/**
	 * Return JSON representation of message
	 *
	 * @return string
	 */
	public function save_json() : string {
		return (string) wp_json_encode( $this->get_fields() );
	}

	/**
	 * Return JSON representation of message
	 *
	 * @return string
	 */
	public function __toString() : string {
		return (string) wp_json_encode( $this->get_fields() );
	}

	/**
	 * Get message ID
	 *
	 * @return string
	 */
	public function get_id() : string {
		return $this->id;
	}

	/**
	 * Factory method to create message
	 *
	 * @param array $message_fields Message fields.
	 * @return Message_Error|Message_Error_Single|Message_Notice|Message_Tip|Message_Tip_Incompatible|Message_Access Message instance.
	 */
	public static function create( array $message_fields ) : self {
		switch ( $message_fields['type'] ) {
			case self::TYPE_TIP:
				if ( isset( $message_fields['source'] ) && 'compatibility' === $message_fields['source'] ) {
					return new Message_Tip_Incompatible( $message_fields );
				}
				return new Message_Tip( $message_fields );
			case self::TYPE_TIP_COMPATIBILITY:
				return new Message_Tip_Incompatible( $message_fields );
			case self::TYPE_NOTICE:
				return new Message_Notice( $message_fields );
			case self::TYPE_ERROR: // error.
				return new Message_Error( $message_fields );
			case self::TYPE_ERROR_SINGLE:
				return new Message_Error_Single( $message_fields );
			case self::TYPE_ACCESS:
				return new Message_Access( $message_fields );
		}
		return new Message_Error( $message_fields );
	}

	/**
	 * Factory method to create message from json
	 *
	 * @param string $json_fields JSON with message fields.
	 * @return Message_Error|Message_Error_Single|Message_Notice|Message_Tip|Message_Tip_Incompatible|Message_Access|null Message instance or null.
	 */
	public static function load_json( string $json_fields ) : ?self {
		$fields = json_decode( $json_fields, true );

		return ! empty( $fields ) && is_array( $fields ) ? self::create( $fields ) : null;
	}

	/**
	 * Get view instance
	 *
	 * @return Ahrefs_Seo_View
	 */
	protected static function get_view() : Ahrefs_Seo_View {
		static $view = null;
		if ( is_null( $view ) ) {
			$view = Ahrefs_Seo::get()->get_view();
		}
		return $view;
	}

	/**
	 * Get template name
	 *
	 * @return string
	 */
	public function get_template() : string {
		return $this::TEMPLATE;
	}

	/**
	 * Update message, add prefix to current message string, update id.
	 *
	 * @param string $prefix String to prepend before current message.
	 * @return Message
	 */
	public function add_message_prefix( string $prefix ) : self {
		if ( 0 !== stripos( $this->message, $prefix ) ) { // if it does not have prefix already.
			$this->message = $prefix . $this->message;
			$this->id      = md5( $this->message );
		}
		return $this;
	}

	/**
	 * Return fields of message.
	 *
	 * @return array<string, string|string[]|bool>
	 */
	protected function get_fields() : array {
		return [
			'id'              => $this->id,
			'classes'         => $this->classes,
			'type'            => $this->type,
			'template'        => $this->get_template(),
			'title'           => $this->title,
			'message'         => $this->message,
			'buttons'         => $this->buttons,
			'not_dismissible' => $this->not_dismissible,
		];
	}

	/**
	 * Show template with message
	 *
	 * @return void
	 */
	public function show() : void {
		$this::get_view()->show_part( self::TEMPLATES_BASE . $this->get_template(), $this->get_fields() );
	}

	/**
	 * Return rendered html with message
	 *
	 * @since 0.9.5
	 *
	 * @return string
	 */
	public function html() : string {
		ob_start();
		$this->show();
		return (string) ob_get_clean();
	}

	/**
	 * Return account disconnected tip
	 *
	 * @param bool $ahrefs_disconnected Ahrefs account disconnected.
	 * @param bool $google_disconnected Google account disconnected.
	 * @return Message
	 */
	public static function account_disconnected( bool $ahrefs_disconnected, bool $google_disconnected ) : self {
		$buttons = $ahrefs_disconnected ? [ 'ahrefs' ] : ( $google_disconnected ? [ 'google' ] : [] );
		return self::create(
			[
				'type'    => self::TYPE_TIP,
				'source'  => $ahrefs_disconnected ? 'ahrefs' : 'google',
				'classes' => [ 'tip-warning' ],
				'title'   => __( 'Some of your accounts have been disconnected', 'ahrefs-seo' ),
				'message' => current_user_can( Ahrefs_Seo::CAP_SETTINGS_ACCOUNTS_SAVE ) ? __( 'The plugin needs Ahrefs, GSC and GA accounts to be connected to run content audits since content suggestion are based on these data. You can check your connected accounts in the settings.', 'ahrefs-seo' ) : __( 'The plugin needs Ahrefs, GSC and GA accounts to be connected to run content audits since content suggestion are based on these data. Please ask the website administrator to check connected accounts in the plugin settings.', 'ahrefs-seo' ),
				'buttons' => $buttons,
			]
		);
	}

	/**
	 * Return account disconnected tip
	 *
	 * @param string $token Current token.
	 * @param bool   $was_a_free_account It was a free Ahrefs account.
	 * @return Message
	 */
	public static function account_expired( string $token, bool $was_a_free_account ) : self {
		$text = sprintf(
			$was_a_free_account ?
			/* translators: %s: token */
			__( 'Your Free Ahrefs account has been disconnected because token %s has been expired or revoked.', 'ahrefs-seo' )
			/* translators: %s: token */
			: __( 'Your Ahrefs account has been disconnected because token %s has been expired or revoked.', 'ahrefs-seo' ),
			esc_html( $token )
		);

		return self::create(
			[
				'type'    => self::TYPE_TIP,
				'source'  => 'ahrefs',
				'classes' => [ 'tip-warning', 'tip-ahrefs' ],
				'title'   => __( 'Some of your accounts have been disconnected', 'ahrefs-seo' ),
				'message' => $text,
			]
		);
	}

	/**
	 * Return ahrefs account limited tip
	 *
	 * @return Message
	 */
	public static function ahrefs_limited() : self {
		$info = Ahrefs_Seo_Api::get()->get_subscription_info( true ); // use cached info.
		$plan = is_array( $info ) && isset( $info['subscription'] ) ? $info['subscription'] : '';
		return self::create(
			[
				'type'    => self::TYPE_TIP,
				'source'  => 'ahrefs',
				'classes' => [ 'tip-warning', 'tip-ahrefs-account-limited' ],
				'title'   => __( 'Your Ahrefs account has been disconnected', 'ahrefs-seo' ),
				'message' => sprintf(
					/* Translators: %s: plan name */
					__( 'Your Ahrefs account has been disconnected because you have used up all monthly integration rows available on your %s plan. Please consider upgrading your plan now or wait until your limits reset. The report will not be updated till the limits are reset.', 'ahrefs-seo' ),
					esc_html( $plan )
				),
				'buttons' => [ 'ahrefs' ],
			]
		);
	}

	/**
	 * Return google account is not suitable tip
	 *
	 * @return Message
	 */
	public static function not_suitable_account() : self {
		return self::create(
			[
				'type'    => self::TYPE_TIP,
				'source'  => 'google',
				'classes' => [ 'tip-warning' ],
				'title'   => __( 'Your Google account is connected but profiles selected is not suitable', 'ahrefs-seo' ),
				'message' => __( 'You might have selected the wrong Google profiles or connected the wrong Google account. You can check your connected accounts in the settings.', 'ahrefs-seo' ),
				'buttons' => [ 'google' ],
			]
		);
	}

	/**
	 * Return delayed audit with Google or Ahrefs as reason
	 *
	 * @param string $source Source of error, one of: 'ahrefs', 'google', 'compatibility', 'general', 'noindex', 'WordPress', 'content audit'.
	 * @return Message
	 */
	public static function audit_delayed( string $source = 'google' ) : self {
		$service = 'google' === $source ? 'Google Analytics & Search Console API' : 'Ahrefs API';
		return self::create(
			[
				'type'    => self::TYPE_NOTICE,
				'source'  => 'google',
				'classes' => [],
				'title'   => '',
				/* Translators: %s: source of issue */
				'message' => sprintf( __( 'We are experiencing some downtime from %s so the content audit run is taking a little longer than usual. The audit is still running in the background so please check back in a couple of minutes! The content audit will be delayed by approximately 15 minutes.', 'ahrefs-seo' ), $service ),
				'buttons' => [],
			]
		);
	}

	/**
	 * Return WordPress API error tip
	 *
	 * @return Message
	 */
	public static function wordpress_api_error() : self {
		return self::create(
			[
				'type'    => self::TYPE_TIP,
				'source'  => 'wordpress',
				'classes' => [ 'tip-warning', 'is-dismissible' ],
				'title'   => __( 'WordPress API Error', 'ahrefs-seo' ),
				'message' => __( 'Something went wrong while querying the WordPress API. Please refresh the page & try again.', 'ahrefs-seo' ),
				'buttons' => [ 'refresh_page', 'close' ],
			]
		);
	}

	/**
	 * Return GSC or GA disconnected error tip
	 *
	 * @param string $text Message text.
	 * @param bool   $is_gsc True: GSC disconnected, False: GA disconnected.
	 * @return Message
	 */
	public static function gsc_disconnected( string $text = '', bool $is_gsc = true ) : self {
		return self::create(
			[
				'type'    => self::TYPE_TIP,
				'source'  => 'google',
				'title'   => $is_gsc ? __( 'Search Console disconnected', 'ahrefs-seo' ) : __( 'Goggle Analytics disconnected', 'ahrefs-seo' ),
				'message' => $text,
				'classes' => [ 'tip-warning', 'tip-google' ],
			]
		);
	}

	/**
	 * Return Google disconnected by user tip
	 *
	 * @param string $text Message text.
	 * @return Message
	 */
	public static function google_disconnected( string $text = '' ) : self {
		return self::create(
			[
				'type'    => self::TYPE_TIP,
				'source'  => 'google',
				'title'   => __( 'Google Analytics & Search Console disconnected', 'ahrefs-seo' ),
				'message' => $text,
				'classes' => [ 'tip-notice', 'tip-google' ],
			]
		);
	}

	/**
	 * Return Google API error message
	 *
	 * @since 0.8.1
	 *
	 * @param string $text Message text.
	 * @return Message
	 */
	public static function google_api_error( string $text ) : self {
		return self::create(
			[
				'type'    => self::TYPE_ERROR,
				'source'  => 'google',
				'title'   => __( 'Google API error', 'ahrefs-seo' ),
				'message' => $text,
				'classes' => [ 'tip-warning', 'tip-google' ],
			]
		);
	}

	/**
	 * Return error message if something is not allowed for current user.
	 *
	 * @since 0.9.5
	 *
	 * @param string|null $text Custom text.
	 * @return Message
	 */
	public static function action_not_allowed( ?string $text = null ) : self {
		return self::create(
			[
				'type'            => self::TYPE_ACCESS,
				'source'          => 'wordpress',
				'title'           => '',
				'message'         => $text ?? __( 'Sorry, you do not have sufficient permissions to perform this action.', 'ahrefs-seo' ),
				'classes'         => [ 'tip-warning', 'tip-access' ],
				'not_dismissible' => true,
			]
		);
	}

	/**
	 * Return error message when edit (save) settings is not allowed for current user.
	 *
	 * @since 0.9.5
	 *
	 * @return Message
	 */
	public static function edit_not_allowed() : self {
		return self::action_not_allowed( __( 'Sorry, you do not have sufficient permissions to edit these settings.', 'ahrefs-seo' ) );
	}

	/**
	 * Return error message if something is not allowed for current user.
	 *
	 * @since 0.9.5
	 *
	 * @param string|null $text Custom text.
	 * @return Message
	 */
	public static function view_not_allowed( ?string $text = null ) : self {
		return self::create(
			[
				'type'            => self::TYPE_ACCESS,
				'source'          => 'wordpress',
				'title'           => '',
				'message'         => $text ?? __( 'Sorry, you do not have sufficient permissions to access these settings.', 'ahrefs-seo' ),
				'classes'         => [ 'tip-warning', 'tip-access' ],
				'not_dismissible' => true,
			]
		);
	}

	/**
	 * Return Google generic error message
	 *
	 * @since 0.9.11
	 *
	 * @param string $text Message text.
	 * @return Message
	 */
	public static function google_generic_error( string $text ) : self {
		return self::create(
			[
				'type'    => self::TYPE_ERROR_SINGLE,
				'source'  => 'google',
				'title'   => '',
				'message' => $text,
				'classes' => [ 'tip-warning', 'tip-google' ],
			]
		);
	}

	/**
	 * Get message text
	 *
	 * @return string Message.
	 */
	public function get_text() : string {
		return (string) $this->message;
	}
}
