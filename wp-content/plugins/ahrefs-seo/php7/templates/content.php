<?php
/**
 * Content page template
 */

declare(strict_types=1);
namespace ahrefs\AhrefsSeo;

use ahrefs\AhrefsSeo\Admin_Notice\Google_Connection;
use ahrefs\AhrefsSeo\Messages\Message;
use ahrefs\AhrefsSeo\Messages\Message_Error;
use ahrefs\AhrefsSeo\Messages\Message_Error_Single;
use ahrefs\AhrefsSeo\Messages\Message_Notice;
use ahrefs\AhrefsSeo\Messages\Message_Tip;
use ahrefs\AhrefsSeo\Messages\Message_Tip_Incompatible;

/**
* @var array<string, bool|null|Message[]> $locals {
*
*   @type Message[]|null $last_audit_stopped Reason why last audit was stopped.
*   @type Message[]|null $stop Error messages with the stop status.
* }
*/
$locals         = Ahrefs_Seo_View::get_template_variables();
$view           = Ahrefs_Seo::get()->get_view();
$stop_messages  = is_array( $locals['last_audit_stopped'] ) ? $locals['last_audit_stopped'] : [];
$is_first_audit = (bool) $locals['is_first_audit'];
$errors_array   = [];
$all_messages   = [];

$audit = ( new Content_Audit() );

$saved_messages = Ahrefs_Seo_Errors::get_saved_messages();
foreach ( $saved_messages as $value ) {
	$_message = Message::create( $value );
	if ( $_message instanceof Message_Tip_Incompatible ) {
		$stop_messages[] = $_message;
	} elseif ( $_message instanceof Message_Error ) {
		$errors_array[] = $value;
	} else {
		$all_messages[] = $_message;
	}
}
unset( $saved_messages );

?>
<div class="ahrefs_messages_block" id="wordpress_api_error" style="display: none;">
<?php Message::wordpress_api_error()->show(); ?>
</div>
<?php ( new Google_Connection() )->maybe_show(); ?>
<div class="ahrefs_messages_block content-tips-block" data-type="stop" id="content_stop_errors" style="display:none;">
	<div class="ahrefs-content-tip tip-warning tip-multi">
		<div class="caption"><?php esc_html_e( 'Errors', 'ahrefs-seo' ); ?></div>
		<div class="subitems"></div>
	</div>
	<div class="tip-single">
		<?php
		$stop_messages = array_merge( $stop_messages, is_array( $locals['stop'] ) ? $locals['stop'] : [] );
		if ( count( $stop_messages ) ) {
			Ahrefs_Seo_Errors::show_stop_errors( $stop_messages );
		}
		?>
	</div>
</div>
<?php
if ( ! $is_first_audit ) {
	require_once __DIR__ . '/parts/charts.php';
}
?>
<div class="ahrefs_messages_block" data-type="audit-tip">
	<?php
	// show tips.
	array_walk(
		$all_messages,
		function( $message ) {
			if ( is_object( $message ) && ( $message instanceof Message_Tip ) ) { // Message_Tip_Incompatible extracted to stop_messages.
				$message->show();
			}
		}
	);
	?>
</div>
<div class="ahrefs_messages_block" data-type="api-messages">
	<?php
	// show errors.
	$view->show_part( 'notices/api-messages', [ 'messages' => $errors_array ] );
	?>
</div>
<div class="ahrefs_messages_block" id="audit_delayed_google" style="display: none;">
<?php Message::audit_delayed()->show(); ?>
</div>
<div class="ahrefs_messages_block" data-type="api-delayed">
	<?php
	// show errors.
	$ids = [];
	array_walk(
		$all_messages,
		function( $message ) use ( &$ids ) {
			if ( $message instanceof Message_Notice || $message instanceof Message_Error_Single ) {
				if ( ! in_array( $message->get_id(), $ids, true ) ) { // do not show duplicated messages.
					$message->show();
					$ids[] = $message->get_id();
				}
			}
		}
	);
	unset( $ids );
	?>
</div>
<?php
if ( ! $is_first_audit ) {
	$view->show_part( 'content-tips' );
	// add placeholder for content audit table.
	$screen = $view->get_ahrefs_screen();
	if ( $screen instanceof Ahrefs_Seo_Screen_With_Table ) {
		$screen->show_table_placeholder();
	}
} else {
	$view->show_part( 'first-audit' );
}
