<?php

namespace ahrefs\AhrefsSeo;

use ahrefs\AhrefsSeo\Messages\Message;
$locals       = Ahrefs_Seo_View::get_template_variables();
$ahrefs_token = Ahrefs_Seo_Token::get();
$token        = $ahrefs_token->token_get();
$link_user    = $ahrefs_token->token_link();
$link_new     = $ahrefs_token->token_free_link();
if ( ! current_user_can( Ahrefs_Seo::CAP_SETTINGS_ACCOUNTS_SAVE ) ) {
	Message::action_not_allowed( __( 'The account is not connected. Please, contact your site administrator to set it up.', 'ahrefs-seo' ) )->show();
	return;
}
?>
<form method="post" action="" class="ahrefs-seo-wizard ahrefs-token">
	<input type="hidden" name="ahrefs_step" value="1">
	<?php
	if ( isset( $locals['page_nonce'] ) ) {
		wp_nonce_field( $locals['page_nonce'] );
	}
	?>
	<div class="card-item">
		<div class="image-wrap">
			<img src="<?php echo esc_attr( AHREFS_SEO_IMAGES_URL ); ?>ahrefs-connect.png"
				srcset="<?php echo esc_attr( AHREFS_SEO_IMAGES_URL ); ?>ahrefs-connect-2x.png 2x, 
<?php echo esc_attr( AHREFS_SEO_IMAGES_URL ); ?>ahrefs-connect-3x.png 3x"
				class="ahrefs-wp-plugin_2x">
		</div>
		<div class="step-1-wrap-left">
			<div class="step1-subtitle sub1">Get authorization code</div>
			<div class="two-rows-wrap">
				<div class="single-row-wrap">
					<p class="help-title">
					<?php
					esc_html_e( 'I’m a paying Ahrefs customer', 'ahrefs-seo' );
					?>
					</p>
					<p class="help">
					<?php
					esc_html_e( 'Automate content audits and grow organic traffic to your website with Ahrefs’ WordPress plugin. Just click the link below and connect your Ahrefs account with this plugin.', 'ahrefs-seo' );
					?>
					</p>

					<a href="<?php echo esc_attr( $link_user ); ?>" target="_blank" class="get-code-button" id="ahrefs_get">
						<span class="text">
						<?php
						esc_html_e( 'Get authorization code', 'ahrefs-seo' );
						?>
						</span>
						<img src="<?php echo esc_attr( AHREFS_SEO_IMAGES_URL . 'link-open.svg' ); ?>" alt="<?php esc_attr_e( 'Open the link', 'ahrefs-seo' ); ?>" class="icon">
					</a>
				</div>
				<div class="single-row-divider">
					<div class="line"></div>
					<div class="or">
					<?php
/* translators: Conjunction "or", like a part of "a OR b" */
					esc_html_e( 'OR', 'ahrefs-seo' );
					?>
					</div>
					<div class="line"></div>
				</div>
				<div class="single-row-wrap">
					<p class="help-title">
					<?php
					esc_html_e( 'I’m new to Ahrefs or using Ahrefs Webmaster Tools', 'ahrefs-seo' );
					?>
					</p>
					<p class="help">
					<?php
					esc_html_e( 'You can still perform content audits, but you will be required to authorise your token every few weeks. Just click the link below and connect your WordPress dashboard to Ahrefs.', 'ahrefs-seo' );
					?>
					</p>

					<a href="<?php echo esc_attr( $link_new ); ?>" target="_blank" class="get-code-button" id="ahrefs_get">
						<span class="text">
						<?php
						esc_html_e( 'Get authorization code', 'ahrefs-seo' );
						?>
						</span>
						<img src="<?php echo esc_attr( AHREFS_SEO_IMAGES_URL . 'link-open.svg' ); ?>" alt="<?php esc_attr_e( 'Open the link', 'ahrefs-seo' ); ?>" class="icon">
					</a>
				</div>
			</div>

			<div class="step1-subtitle sub2">Connect with Ahrefs</div>
			<div class="new-token-button">
				<label class="label">
				<?php
				esc_attr_e( 'Enter the authorization code you obtained in step 1:', 'ahrefs-seo' );
				?>
				</label>
				<div class="input_button">
					<input type="text" class="input-input-default-s-default 
					<?php
					if ( ! empty( $locals['error'] ) ) { // phpcs:ignore Squiz.ControlStructures.ControlSignature.NewlineAfterOpenBrace
						?>
	error
						<?php
					} ?>"
					value="<?php echo esc_attr( $token ); ?>" name="ahrefs_code" id="ahrefs_code">
					<input type="submit" name="submit" id="submit" class="button button-primary" value="<?php esc_attr_e( 'Connect with Ahrefs', 'ahrefs-seo' ); ?>">
				</div>
				<div class="ahrefs-seo-error">
					<?php
					if ( '' !== $locals['error'] ) {
						echo esc_html( $locals['error'] );
					}
					?>
				</div>
			</div>
		</div>
	</div>

	<?php
	if ( ! empty( $locals['show_button'] ) ) {
		?>
		<div class="button-wrap">
			<input type="submit" name="submit" id="submit" class="button button-primary button-hero" value="<?php esc_attr_e( 'Continue', 'ahrefs-seo' ); ?>">
		</div>
		<?php
	}
	?>
</form>
<?php 