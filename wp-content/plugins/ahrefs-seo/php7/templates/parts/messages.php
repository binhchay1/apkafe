<?php
/**
 * Messages (tips, errors, notices ) template.
 * Used for Google accounts setting page.
 */

declare(strict_types=1);
namespace ahrefs\AhrefsSeo;

$locals = Ahrefs_Seo_View::get_template_variables();
$view   = Ahrefs_Seo::get()->get_view();

$messages = $locals['messages']; // array of array, not array of Messages.
foreach ( $messages as $key => $item ) {
	$messages[ $key ]['title'] = Ahrefs_Seo_Errors::get_title_for_source( $item['source'] );
}
?>
<div class="ahrefs_messages_block" data-type="audit-tip">
<?php
// Tips.
foreach ( $messages as $key => $message ) {
	if ( 'tip' === $message['type'] ) {
		$view->show_part(
			'dynamic/tip',
			[
				'title'   => $message['title'] ?? Ahrefs_Seo_Errors::get_title_for_source( $message['source'] ),
				'message' => $message['message'],
			]
		);
		unset( $messages[ $key ] );
	}
}
?>
</div>
<div class="ahrefs_messages_block" data-type="api-messages">
<?php
$errors = array_filter(
	$messages,
	function( $item ) {
		return 'error' === $item['type'];
	}
);
if ( count( $errors ) ) {
	$view->show_part( 'notices/please-contact', [ 'messages' => $errors ] );
	$messages = array_filter(
		$messages,
		function( $item ) {
			return 'error' !== $item['type'];
		}
	);
}
?>
</div>
<div class="ahrefs_messages_block" data-type="api-delayed">
<?php
// Notices.
if ( count( $messages ) ) {
	$view->show_part( 'messages/notices', [ 'messages' => $messages ] );
}
?>
</div>
