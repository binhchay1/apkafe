<?php
declare(strict_types=1);
namespace ahrefs\AhrefsSeo;

use ahrefs\AhrefsSeo\Content_Tips\Tip_Keywords_Popup;
use ahrefs\AhrefsSeo\Messages\Message;
use ahrefs\AhrefsSeo\Options\Countries;

$locals = Ahrefs_Seo_View::get_template_variables();

/*
(1) If we have at least one cached suggestion:
- show cached version of suggested keywords in the popup immediately;
- no need to show loader;
- if received suggestions are different from existing: replace all items at the table with updated suggestions.
(2) But what to do if we have no cached suggestions exists (when we tried to load it, but nothing found, example: any new short post)?
- show popup dialog with empty keywords table and description as for no keywords found ("We couldn’t find any keyword recommendations for (post title) because the content length is too short. Try improving the content by increasing the amount of words or just go ahead and add your own keywords.").
- show loader.
- If new search will return something - update description and keywords table.
(3) Last case, when this post does not have cached suggestions, and we do not run research in the past (example: user added a lot of posts to analysis and clicked on "Change keywords" link for one of them).
- show popup dialog with description is: ‘We are generating a list of recommended keywords for [post name].’ and empty keywords table.
- show loader
- update description and suggested keywords table with new data.
*/

$post_tax = $locals['post_tax'];

if ( ! is_null( $post_tax ) && ( $post_tax instanceof Post_Tax ) && $post_tax->exists() ) {
	$id                  = (string) $post_tax;
	$post_title          = $post_tax->get_title( true );
	$url_view            = $post_tax->get_url();
	$data                = Ahrefs_Seo_Keywords::get()->get_suggestions( $post_tax, true );
	$is_approved         = Ahrefs_Seo_Data_Content::get()->is_keyword_approved( $post_tax );
	$is_imported         = Ahrefs_Seo_Data_Content::get()->is_keyword_imported( $post_tax );
	$country_code        = $post_tax->get_country_code();
	$country_code_ahrefs = Countries::get_country_code_ahrefs( $country_code );
	?>
	<div class="ahrefs-seo-modal-keywords" id="ahrefs_seo_modal_keywords" data-id="<?php echo esc_attr( $id ); ?>">
		<div class="keywords-wrap-body">
			<div class="keyword-header"><?php esc_html_e( 'Select target keyword for', 'ahrefs-seo' ); ?> <a class="keyword-post-title" href="<?php echo esc_attr( $url_view ); ?>"><?php echo esc_html( $post_title ); ?></a></div>

			<?php
			if ( ! $is_approved && ! $is_imported ) { // allow tip for the suggested and not imported keyword only.
				( new Tip_Keywords_Popup() )->maybe_show_tip();
			}
			?>
			<div class="keyword-save-error"></div>
			<div class="keyword-table-wrap">
				<div class="keyword-choice-wrap">
					<div class="keyword-title"><?php esc_html_e( 'Target keyword', 'ahrefs-seo' ); ?>
						<span class="help-small" title="
						<?php
							printf(
								/* translators: %s: Link to help article with "how to choose the right target keyword." anchor. */
								esc_attr__( 'The content audit is based on your article’s ranking performance for this keyword. Learn more on %s', 'ahrefs-seo' ),
								( new Ahrefs_Seo_Table_Content() )->prepare_quotes( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- we substitute hardcoded html link and already escaped translated text.
									sprintf(
										"<a href='%s' target='blank' class='internal-hint-a'>%s</a>", // html code must not contain double quotes.
										'https://ahrefs.com/blog/keyword-research/', // no double quotes!
										esc_html( ( new Ahrefs_Seo_Table_Content() )->prepare_quotes( esc_html__( 'how to choose the right target keyword.', 'ahrefs-seo' ) ) )
									)
								)
							);
						// phpcs:ignore Squiz.PHP.EmbeddedPhp.ContentAfterEnd,Squiz.PHP.EmbeddedPhp.ContentBeforeEnd
						?>">&nbsp;</span></div>
					<div class="keyword-input-wrap">
						<input type="text" class="keyword-input" value="" maxlength="191">
						<input type="hidden" class="source-input" value="" maxlength="50">
						</div>
					<div class="keyword-title">
						<?php esc_html_e( 'Suggested keywords', 'ahrefs-seo' ); ?>
						<div class="row-loader loader-transparent inline-loader" id="loader_suggested_keywords"><div class="loader"></div></div>
					</div>
					<table id="keyword_results" class="keyword-results-table" style="width:100%" data-id="<?php echo esc_attr( $id ); ?>">
					</table>
				</div>
			</div>
		</div>
		<div class="keywords-buttons">
			<a href="#" class="button button-hero button-primary" id="ahrefs_seo_keyword_submit">
				<span class="text"><?php esc_html_e( 'Apply', 'ahrefs-seo' ); ?></span>
				<span class="disabled"><div class="row-loader loader-transparent inline-loader" id="loader_suggested_keywords2"><div class="loader"></div></div></span>
			</a>
			<a href="#" class="button button-hero button-cancel" id="ahrefs_seo_keyword_cancel"><?php esc_html_e( 'Cancel', 'ahrefs-seo' ); ?></a>
		</div>

		<script type="text/javascript">
			content.keyword_country_code = <?php echo wp_json_encode( $country_code_ahrefs ); ?>;
			content.keyword_country_code3 = <?php echo wp_json_encode( $country_code ); ?>;
			content.keyword_data_set = <?php echo wp_json_encode( $data['keywords'] ); ?>;
			content.keyword_data_total_clicks = <?php echo wp_json_encode( $data['total_clicks'] ); ?>;
			content.keyword_data_total_impr = <?php echo wp_json_encode( $data['total_impr'] ); ?>;
			content.keyword_data_not_approved = <?php echo wp_json_encode( ! $is_approved ); ?>;
			content.keyword_popup_update_table(); // initialize table with existing suggestions.
			content.keyword_popup_update_suggestions( <?php echo wp_json_encode( $id ); ?> ); // run update for new suggestions.
		</script>
	</div>
	<?php
	// show errors inside popup dialog.
	if ( ! is_null( $data['errors'] ) ) {
		?>
		<script type="text/javascript">
			content.keyword_show_error( <?php echo wp_json_encode( $data['errors'] ); ?> );
		</script>
		<?php
	}
} else {
	$message = ( isset( $locals['error'] ) && ( $locals['error'] instanceof Message ) ) ? $locals['error'] :
	( Message::create(
		[
			'type'    => 'error-single',
			'title'   => '',
			'message' => __( 'This page cannot be found. It is possible that you’ve archived the page or changed the page ID. Please reload the page & try again.', 'ahrefs-seo' ),
		]
	) );
	$message->show();
	?>
	<script type="text/javascript">
		jQuery( '#TB_ajaxContent' ).css( 'height','120px' );
	</script>
	<?php
}
