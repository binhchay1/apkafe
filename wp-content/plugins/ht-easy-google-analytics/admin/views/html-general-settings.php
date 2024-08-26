<?php
	$email          = get_option( 'htga4_email' );
	$user_roles     = $this->get_roles_dropdown_options();
	$selected_roles = $this->get_option( 'exclude_roles', array() );

	$login_url            = $this->get_auth_url();
	$access_token         = $this->get_access_token();
	$permission_optin     = true;
	$opt_data_stream_id   = $this->get_option( 'data_stream_id' );
	$is_pro_plugin_active = $this->is_pro_plugin_active();

	$ht_easy_ga4_id     = $this->get_option( 'ht_easy_ga4_id' );
	$opt_account        = $this->get_option( 'account' );
	$opt_measurement_id = $this->get_option( 'measurement_id' );

	$accounts = $this->get_accounts_data_prepared();
if ( ! empty( $accounts['error'] ) ) {
	$accounts = array(
		'error' => __( 'Error: ', 'ht-easy-ga4' ) . $accounts['error']['message'],
	);
}

	$properties   = htga4()->get_properties_data_prepared();
	$opt_property = $this->get_option( 'property' );

if ( ! empty( $properties['error'] ) ) {
	$properties = array(
		'error' => __( 'Error: ', 'ht-easy-ga4' ) . $properties['error']['message'],
	);
}

	$data_streams = htga4()->get_data_streams_data_prepared();
	$opt_account  = $this->get_option( 'account' );

if ( ! empty( $data_streams['error'] ) ) {
	$data_streams = array(
		'error' => __( 'Error: ', 'ht-easy-ga4' ) . $data_streams['error']['message'],
	);
}
?>

<form method="post" class="htga4 htga4_general_options" action="">
	<?php settings_fields( 'ht-easy-ga4-settings-option' ); ?>

	<table class="form-table" role="presentation">
		<tbody>
			<tr class="htga4_login">
				<th scope="row" style="width: 20%;">
					<?php echo esc_html__( 'Authentication with Google', 'ht-easy-ga4' ); ?>
				</th>
				<td>
				<?php
				if ( ! $access_token ) {
					printf(
						'<a class="button" href="%1$s" target="_blank">%2$s</a>',
						esc_url( $login_url ),
						esc_html__( 'Sign in with your Google Analytics account', 'ht-easy-ga4' )
					);

					echo '<br>';
					$this->render_login_notice( 'manually_set_tracking_id' );

				} else {
					/* translators: 1$s logout text */
					$logout_text = sprintf( esc_html__( 'Logout (%s)', 'ht-easy-ga4' ), $email );

					printf(
						/* translators: 1$s logout url, 2$s logout text */
						'<a class="button" href="%1$s">%2$s</a>',
						esc_url( add_query_arg( 'htga4_logout', 'yes', add_query_arg( 'page', 'ht-easy-ga4-setting-page', get_admin_url( '', 'admin.php' ) ) ) ),
						esc_html( $logout_text )
					);

					if ( $permission_optin === false ) {
						echo '<br>';
						$this->render_login_notice( 'insufficient_permission' );
					}
				}
				?>
				</td>
			</tr>

			<?php if ( ! $access_token ) : ?>
			<tr class="htga4-tracking-id">
				<th scope="row" style="width: 20%;">
					<?php echo esc_html__( 'GA4 Tracking ID ', 'ht-easy-ga4' ); ?>
					<span><?php echo esc_html__( 'Sample Measurement ID:  G-08F1MTVENK', 'ht-easy-ga4' ); ?></span>
				</th>
				<td>
					<input type="text" id="ht_easy_ga4_id" placeholder="G-XXXXXXXXXX" name="ht_easy_ga4_id" value="<?php echo esc_attr( $ht_easy_ga4_id ); ?>"/>
					<p class="desc"><?php echo esc_html__( 'Manually add the GA4 Tracking / Measurement ID here.', 'ht-easy-ga4' ); ?></p>
				</td>
			</tr>
			<?php endif; ?>

			<?php if ( $access_token ) : ?>
			<tr class="htga4-chosse-property">
				<th scope="row" style="width: 20%;">
					<?php echo esc_html__( 'Choose Property: ', 'ht-easy-ga4' ); ?>
					<span><?php echo esc_html__( 'Choose property from your Google Analytics account', 'ht-easy-ga4' ); ?></span>
				</th>
				<td>
					<input type="hidden" class="htga4_data_stream_id" name="data_stream_id" value='<?php echo esc_attr( $opt_data_stream_id ); ?>'>
					<div class="htga4_accounts_wrapper">
						<span>
							<?php
								echo esc_html__( 'Select account', 'ht-easy-ga4' );

								$select_message = esc_html__( 'Select account', 'ht-easy-ga4' );
							if ( ! empty( $this->accounts_result['error'] ) ) {
								$select_message = $this->accounts_result['error']['message'];
							}
							?>
							<select name="account" id="" class="htga4-select-account">
								<option value=""><?php echo esc_html( $select_message ); ?></option>
								<?php
								foreach ( $accounts as $account_id => $account_name ) {
									printf(
										'<option value="%1$s" %3$s>%2$s <%1$s></option>',
										esc_html( $account_id ),
										esc_html( $account_name ),
										selected( $opt_account, $account_id, false )
									);
								}
								?>
							</select>
						</span>

						<span>
							<?php echo esc_html__( 'Select property', 'ht-easy-ga4' ); ?>
							<select name="property" class="htga4-select-property" <?php echo ! $opt_account ? 'disabled' : ''; ?>>
								<option value=""><?php echo esc_html__( 'Select property', 'ht-easy-ga4' ); ?></option>
								<?php
								foreach ( $properties as $property_id => $property_name ) {
									printf(
										'<option value="%1$s" %3$s>%2$s <%1$s></option>',
										esc_html( $property_id ),
										esc_html( $property_name ),
										selected( $opt_property, $property_id, false )
									);
								}
								?>
							</select>
						</span>
					</div>
				</td>
			</tr><!-- Choose Property -->	

				<?php if ( $this->get_current_tab() != 'standard_reports' ) : ?>
			<tr>
				<th scope="row" style="width: 20%;">
					<?php echo esc_html__( 'Measurement ID: ', 'ht-easy-ga4' ); ?>
					<span><?php echo esc_html__( 'Select Measurement ID to start tracking & view reports.', 'ht-easy-ga4' ); ?></span>
				</th>
				<td>
					<select name="measurement_id" class="htga4-select-measurement-id" <?php echo ! $opt_property ? 'disabled' : ''; ?>>                     
						<?php
						$select_message = esc_html__( 'Select measurement ID', 'ht-easy-ga4' );
						if ( $opt_property ) {
							$measurement_result = $this->request_data_streams( $opt_property );

							if ( ! empty( $measurement_result['error'] ) ) {
								$select_message = $measurement_result['error']['message'];
							}
						}
						?>

						<option value=""><?php echo esc_html( $select_message ); ?></option>
						<?php
						if ( $opt_property ) {
							foreach ( $data_streams as $data_stream_id => $arr ) {
								printf(
									'<option data-stream_id="%s" value="%s" %s>%s &#60;%s&#62;</option>',
									esc_attr( $data_stream_id ),
									esc_attr( $arr['measurement_id'] ),
									selected( $opt_measurement_id, $arr['measurement_id'], false ),
									esc_html( $arr['display_name'] ),
									esc_html( $arr['measurement_id'] ),
								);
							}
						}
						?>
					</select>
				</td>
			</tr> <!-- Measurement id -->
			<?php endif; ?> 

			<?php endif; ?>

			<tr class="htga4-exclude-tracking-for">
				<th scope="row" style="width: 20%;">
					<?php echo esc_html__( 'Exclude Tracking For', 'ht-easy-ga4' ); ?> <span><?php echo esc_html__( 'The users of the selected Role(s) will not be tracked', 'ht-easy-ga4' ); ?></span>
				</th>
				<td>
					<div class="htga4-select2-parent">
						<input type="hidden" name="exclude_roles[]" value="">
						<select id="exclude_roles" name="exclude_roles[]" multiple data-placeholder="<?php echo esc_html__( 'Select Role', 'ht-easy-ga4' ); ?>">
						<?php
						foreach ( $user_roles as $role_slug => $role_label ) {
							$selected = in_array( $role_slug, $selected_roles ) ? 'selected' : ''; // phpcs:ignore
							$disabled = $role_slug === 'administrator' ? '' : 'disabled';

							if ( $is_pro_plugin_active ) {
								echo "<option value='$role_slug' $selected>$role_label</option>"; // phpcs:ignore
							} else {
								echo "<option value='$role_slug' $selected $disabled>$role_label</option>";  // phpcs:ignore
							}
						}
						?>
						</select>
					</div>

					<?php if ( ! $is_pro_plugin_active ) : ?>
					<p class="desc">
						<?php
						printf(
							/* translators: 1$s Premium version link */
							esc_html__( 'Excluding multiple roles together available in the %1$s version.', 'ht-easy-ga4' ),
							'<a href="https://hasthemes.com/plugins/google-analytics-plugin-for-wordpress?utm_source=wp-org&utm_medium=ht-ga4&utm_campaign=htga4_exclude_multiple_roles" target="_blank">' . esc_html__( 'Premium', 'ht-easy-ga4' ) . '</a>'
						);
						?>
					</p>
					<?php endif; ?>
				</td>
			</tr>
		</tbody>
	</table>

	<button type="submit" id="submit" class="button button-primary"><?php echo esc_html__( 'Save Changes', 'ht-easy-ga4' ); ?></button>
</form>
