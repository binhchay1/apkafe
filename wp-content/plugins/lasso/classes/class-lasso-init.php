<?php
/**
 * Declare class Lasso_Init
 *
 * @package Lasso_Init
 */

use Lasso\Classes\Update_DB as Lasso_Update_DB;

/**
 * Lasso_Init
 */
class Lasso_Init {
	/**
	 * Declare vars of Lasso_Init
	 *
	 * @var array $classes List of classes
	 */
	private $classes;

	/**
	 * Declare vars of Lasso_Init
	 *
	 * @var array $ajaxes List of ajaxes
	 */
	private $ajaxes;

	/**
	 * Declare vars of Lasso_Init
	 *
	 * @var array $hooks List of hooks
	 */
	private $hooks;

	/**
	 * Construction of Lasso_Init
	 */
	public function __construct() {
		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
		add_action( 'in_plugin_update_message-' . LASSO_PLUGIN_BASE_NAME, array( $this, 'modify_plugin_update_message' ), 10, 2 );
		add_filter( 'admin_init', array( $this, 'auto_update_lasso' ) );
		add_filter( 'admin_init', array( $this, 'lasso_update_checker_loader' ) );
		add_filter( 'http_request_args', array( $this, 'add_expect_header' ) );

		// Remove default plugin information action.
		remove_all_actions( 'install_plugins_pre_plugin-information' );
		// Override action with custom plugins function for add-ons.
		add_action( 'install_plugins_pre_plugin-information', array( $this, 'install_plugin_information' ) );

		$this->classes = array(
			// ? Core Classes
			array( 'Lasso_Affiliate_Link', 'class-lasso-affiliate-link.php' ),
			array( 'Lasso_Amazon_Api', 'class-lasso-amazon-api.php' ),
			array( 'Lasso_Cron', 'class-lasso-cron.php' ),
			array( 'Lasso_DB', 'class-lasso-db.php' ),
			array( 'Lasso_DB_Script', 'class-lasso-db-script.php' ),
			array( 'Lasso_License', 'class-lasso-license.php' ),
			array( 'Lasso_Shortcode', 'class-lasso-shortcode.php' ),
		);

		$this->ajaxes = array(
			'\Lasso\Pages\Ajax',
			'\Lasso\Pages\Fields\Ajax',
			'\Lasso\Pages\Group_Urls\Ajax',
			'\Lasso\Pages\Groups\Ajax',
			'\Lasso\Pages\Import_Urls\Ajax',
			'\Lasso\Pages\Install\Ajax',
			'\Lasso\Pages\Keyword_Opportunities\Ajax',
			'\Lasso\Pages\Post_Content_History_Detail\Ajax',
			'\Lasso\Pages\Settings_Amazon\Ajax',
			'\Lasso\Pages\Settings_Display\Ajax',
			'\Lasso\Pages\Settings_General\Ajax',
			'\Lasso\Pages\Table_Details\Ajax',
			'\Lasso\Pages\Url_Details\Ajax',
		);

		$this->hooks = array(
			'\Lasso\Pages\Hook',
			'\Lasso\Pages\Fix\Hook',
			'\Lasso\Pages\Import_Urls\Hook',
			'\Lasso\Pages\Post_Type\Hook',
			'\Lasso\Pages\Redirect\Hook',
			'\Lasso\Pages\Table_Details\Hook',
			'\Lasso\Pages\Url_Details\Hook',
		);

		$this->load_classes();
		$this->update_db();
	}

	/**
	 * Load php files and classes
	 */
	private function load_classes() {
		include_once LASSO_PLUGIN_PATH . '/libs/background_processing/lasso-wp-async-request.php';
		include_once LASSO_PLUGIN_PATH . '/libs/background_processing/lasso-wp-background-process.php';
		include_once LASSO_PLUGIN_PATH . '/classes/background_processing/class-lasso-process.php';
		include_once LASSO_PLUGIN_PATH . '/libs/plugin_update_checker/plugin-update-checker.php';
		include_once LASSO_PLUGIN_PATH . '/libs/amazon_api_v5/amazon-api-v5.php';

		// ? Require library: PHP Simple HTML DOM
		// ? http://simplehtmldom.sourceforge.net/
		if ( ! class_exists( 'simple_html_dom' ) || ! class_exists( 'simple_html_dom_node' ) || ! function_exists( 'str_get_html' ) ) {
			include_once LASSO_PLUGIN_PATH . '/libs/simple_html_dom/simple_html_dom.php';
		}

		$this->initalize_classes();
		$this->initalize_ajaxes();
		$this->initalize_hooks();

		include_once LASSO_PLUGIN_PATH . '/classes/background_processing/class-lasso-process-check-issue.php';
		include_once LASSO_PLUGIN_PATH . '/classes/background_processing/class-lasso-process-update-amazon.php';
		include_once LASSO_PLUGIN_PATH . '/classes/background_processing/class-lasso-process-import-all.php';
		include_once LASSO_PLUGIN_PATH . '/classes/background_processing/class-lasso-process-link-database.php';
		include_once LASSO_PLUGIN_PATH . '/classes/background_processing/class-lasso-process-revert-all.php';
		include_once LASSO_PLUGIN_PATH . '/classes/background_processing/class-lasso-process-scan-link.php';
		include_once LASSO_PLUGIN_PATH . '/classes/background_processing/class-lasso-process-scan-keyword.php';
		include_once LASSO_PLUGIN_PATH . '/classes/background_processing/class-lasso-process-remove-attribute.php';
		include_once LASSO_PLUGIN_PATH . '/classes/background_processing/class-lasso-process-replace-shortcode.php';
		include_once LASSO_PLUGIN_PATH . '/classes/background_processing/class-lasso-process-add-amazon.php';
		include_once LASSO_PLUGIN_PATH . '/classes/background_processing/class-lasso-process-auto-monetize.php';
		include_once LASSO_PLUGIN_PATH . '/classes/background_processing/class-lasso-process-pretty-link-final-url.php';
		include_once LASSO_PLUGIN_PATH . '/classes/background_processing/class-lasso-process-data-sync-link-location.php';
		include_once LASSO_PLUGIN_PATH . '/classes/background_processing/class-lasso-process-data-sync-lasso-links.php';
		include_once LASSO_PLUGIN_PATH . '/classes/background_processing/class-lasso-process-update-category-for-imported-pretty-links.php';
		include_once LASSO_PLUGIN_PATH . '/classes/background_processing/class-lasso-process-create-webp-image.php';
		include_once LASSO_PLUGIN_PATH . '/classes/background_processing/class-lasso-process-create-webp-image-table.php';
		include_once LASSO_PLUGIN_PATH . '/classes/background_processing/class-lasso-process-bulk-add-links.php';
		include_once LASSO_PLUGIN_PATH . '/classes/background_processing/class-lasso-process-data-sync-authors.php';

		include_once LASSO_PLUGIN_PATH . '/classes/data_sync/class-lasso-data-sync.php';
		include_once LASSO_PLUGIN_PATH . '/classes/data_sync/class-lasso-data-sync-content.php';
		include_once LASSO_PLUGIN_PATH . '/classes/data_sync/class-lasso-data-sync-link-location.php';
		include_once LASSO_PLUGIN_PATH . '/classes/data_sync/class-lasso-data-sync-lasso-links.php';
		include_once LASSO_PLUGIN_PATH . '/classes/data_sync/class-lasso-data-sync-authors.php';

		include_once LASSO_PLUGIN_PATH . '/classes/background_processing/class-lasso-process-data-sync-content.php';
	}

	/**
	 * Create an object of class
	 */
	public function initalize_classes() {
		foreach ( $this->classes as $class ) {
			include_once LASSO_PLUGIN_PATH . '/classes/' . $class[1];
			new $class[0]();
		}
	}

	/**
	 * Register Ajax hooks
	 */
	public function initalize_ajaxes() {
		foreach ( $this->ajaxes as $ajax_class ) {
			$ajax_object = new $ajax_class();
			$ajax_object->register_hooks();
		}
	}

	/**
	 * Register hooks
	 */
	public function initalize_hooks() {
		foreach ( $this->hooks as $hook_class ) {
			$hook_object = new $hook_class();
			$hook_object->register_hooks();
		}
	}

	/**
	 * Load lasso textdomain
	 */
	public function load_textdomain() {
		load_plugin_textdomain( LASSO_TEXT_DOMAIN, false, LASSO_PLUGIN_PATH . '/languages' );
	}

	/**
	 * Change update message in WP plugin list page
	 *
	 * @param array $plugin_data An array of plugin metadata.
	 * @param array $response    An array of metadata about the available plugin update.
	 */
	public function modify_plugin_update_message( $plugin_data, $response ) {
		// ? display message
		if ( ! Lasso_License::get_license_status() ) {
			echo '
                <br />
                To enable updates, please enter your license key from the 
                <a target="_blank" href="https://app.getlasso.co/account" rel="nofollow">Account</a> page. 
                If you don\'t have a licence key, please see 
                <a target="_blank" href="https://getlasso.co/pricing/" rel="nofollow">details &amp; pricing</a>.
            ';
		}
	}

	/**
	 * Auto enable update Lasso in the first time
	 */
	public function auto_update_lasso() {
		$lasso_option_name   = 'lasso_auto_update';
		$lasso_update_option = get_option( $lasso_option_name, false );

		// ? Only set auto update for Lasso once
		if ( ! $lasso_update_option ) {
			$asset  = 'lasso/lasso';
			$option = 'auto_update_plugins';

			$auto_updates = (array) get_site_option( $option, array() );

			// ? Enable auto update lasso
			$auto_updates[] = $asset;
			$auto_updates   = array_unique( $auto_updates );

			// ? Remove items that have been deleted since the site option was last updated.
			$all_items    = apply_filters( 'all_plugins', get_plugins() );
			$auto_updates = array_intersect( $auto_updates, array_keys( $all_items ) );

			update_site_option( $option, $auto_updates );
			update_option( $lasso_option_name, true );
		}
	}

	/**
	 * Force to run a background process to scan link in post/page
	 */
	public static function force_to_run_new_scan() {
		require_once ABSPATH . 'wp-includes/pluggable.php';

		$scan_link_process = new Lasso_Process_Force_Scan_All_Posts();
		$scan_link_process->reset_data();
		$scan_link_process->force_to_run_new_scan();
	}

	/**
	 * By default, cURL sends the "Expect" header all the time which severely impacts
	 * performance. Instead, we'll send it if the body is larger than 1 mb like
	 * Guzzle does.
	 *
	 * @param array $arguments Arguments.
	 */
	public function add_expect_header( $arguments ) {
		$body = $arguments['body'];

		if ( is_array( $body ) ) {
			$body = wp_json_encode( $body );
		}

		if ( is_array( $arguments['headers'] ) && isset( $arguments['headers']['expect'] ) && ! empty( $body ) && strlen( $body ) > 1048576 ) {
			$arguments['headers']['expect'] = '100-Continue';
		}

		return $arguments;
	}

	/**
	 * Display plugin information in dialog box form.
	 *
	 * @since 2.7.0
	 *
	 * @global string $tab
	 */
	public function install_plugin_information() {
		global $tab;

		$plugin_name = $_REQUEST['plugin'] ?? ''; // phpcs:ignore
		if ( empty( $plugin_name ) ) {
			return;
		}

		if ( 'affiliate-plugin' === $plugin_name || 'lasso' === $plugin_name ) {
			$lasso_folder_name = 'lasso';
			$lasso_link        = Lasso_License::get_plugin_update_url();
			$lasso_update      = new Lasso_Puc_v4p4_Plugin_UpdateChecker( $lasso_link, LASSO_PLUGIN_MAIN_FILE, $lasso_folder_name );
			$api               = $lasso_update->requestInfo( array( 'checking_for_updates' => '1' ) );
		} else {
			$api = plugins_api(
				'plugin_information',
				array(
					'slug' => wp_unslash( $plugin_name ),
				)
			);
		}

		// @codingStandardsIgnoreStart
		if ( is_wp_error( $api ) || ! $api ) {
			wp_die( $api );
		}

		$plugins_allowedtags = array(
			'a'          => array(
				'href'   => array(),
				'title'  => array(),
				'target' => array(),
			),
			'abbr'       => array( 'title' => array() ),
			'acronym'    => array( 'title' => array() ),
			'code'       => array(),
			'pre'        => array(),
			'em'         => array(),
			'strong'     => array(),
			'div'        => array( 'class' => array() ),
			'span'       => array( 'class' => array() ),
			'p'          => array(),
			'br'         => array(),
			'ul'         => array(),
			'ol'         => array(),
			'li'         => array(),
			'h1'         => array(),
			'h2'         => array(),
			'h3'         => array(),
			'h4'         => array(),
			'h5'         => array(),
			'h6'         => array(),
			'img'        => array(
				'src'   => array(),
				'class' => array(),
				'alt'   => array(),
			),
			'blockquote' => array( 'cite' => true ),
		);

		$plugins_section_titles = array(
			'description'  => _x( 'Description', 'Plugin installer section title' ),
			'installation' => _x( 'Installation', 'Plugin installer section title' ),
			'faq'          => _x( 'FAQ', 'Plugin installer section title' ),
			'screenshots'  => _x( 'Screenshots', 'Plugin installer section title' ),
			'changelog'    => _x( 'Changelog', 'Plugin installer section title' ),
			'reviews'      => _x( 'Reviews', 'Plugin installer section title' ),
			'other_notes'  => _x( 'Other Notes', 'Plugin installer section title' ),
		);

		// Sanitize HTML.
		foreach ( (array) $api->sections as $section_name => $content ) {
			$api->sections[ $section_name ] = wp_kses( $content, $plugins_allowedtags );
		}

		foreach ( array( 'version', 'author', 'requires', 'tested', 'homepage', 'downloaded', 'slug' ) as $key ) {
			if ( isset( $api->$key ) ) {
				$api->$key = wp_kses( $api->$key, $plugins_allowedtags );
			}
		}

		$_tab = esc_attr( $tab );

		// Default to the Description tab, Do not translate, API returns English.
		$section = isset( $_REQUEST['section'] ) ? wp_unslash( $_REQUEST['section'] ) : 'description';
		if ( empty( $section ) || ! isset( $api->sections[ $section ] ) ) {
			$section_titles = array_keys( (array) $api->sections );
			$section        = reset( $section_titles );
		}

		iframe_header( __( 'Plugin Installation' ) );

		$_with_banner = '';

		if ( ! empty( $api->banners ) && ( ! empty( $api->banners['low'] ) || ! empty( $api->banners['high'] ) ) ) {
			$_with_banner = 'with-banner';
			$low          = empty( $api->banners['low'] ) ? $api->banners['high'] : $api->banners['low'];
			$high         = empty( $api->banners['high'] ) ? $api->banners['low'] : $api->banners['high'];
			?>
			<style type="text/css">
				#plugin-information-title.with-banner {
					background-image: url( <?php echo esc_url( $low ); ?> );
				}
				@media only screen and ( -webkit-min-device-pixel-ratio: 1.5 ) {
					#plugin-information-title.with-banner {
						background-image: url( <?php echo esc_url( $high ); ?> );
					}
				}
			</style>
			<?php
		}

		echo '<div id="plugin-information-scrollable">';
		echo "<div id='{$_tab}-title' class='{$_with_banner}'><div class='vignette'></div><h2>{$api->name}</h2></div>";
		echo "<div id='{$_tab}-tabs' class='{$_with_banner}'>\n";

		foreach ( (array) $api->sections as $section_name => $content ) {
			if ( 'reviews' === $section_name && ( empty( $api->ratings ) || 0 === array_sum( (array) $api->ratings ) ) ) {
				continue;
			}

			if ( isset( $plugins_section_titles[ $section_name ] ) ) {
				$title = $plugins_section_titles[ $section_name ];
			} else {
				$title = ucwords( str_replace( '_', ' ', $section_name ) );
			}

			$class       = ( $section_name === $section ) ? ' class="current"' : '';
			$href        = add_query_arg(
				array(
					'tab'     => $tab,
					'section' => $section_name,
				)
			);
			$href        = esc_url( $href );
			$san_section = esc_attr( $section_name );
			echo "\t<a name='$san_section' href='$href' $class>$title</a>\n";
		}

		echo "</div>\n";

		?>
	<div id="<?php echo $_tab; ?>-content" class='<?php echo $_with_banner; ?>'>
		<div class="fyi">
			<ul>
				<?php if ( ! empty( $api->version ) ) { ?>
					<li><strong><?php _e( 'Version:' ); ?></strong> <?php echo $api->version; ?></li>
				<?php } if ( ! empty( $api->author ) ) { ?>
					<li><strong><?php _e( 'Author:' ); ?></strong> <?php echo links_add_target( $api->author, '_blank' ); ?></li>
				<?php } if ( ! empty( $api->last_updated ) ) { ?>
					<li><strong><?php _e( 'Last Updated:' ); ?></strong>
						<?php
						/* translators: %s: Human-readable time difference. */
						printf( __( '%s ago' ), human_time_diff( strtotime( $api->last_updated ) ) );
						?>
					</li>
				<?php } if ( ! empty( $api->requires ) ) { ?>
					<li>
						<strong><?php _e( 'Requires WordPress Version:' ); ?></strong>
						<?php
						/* translators: %s: Version number. */
						printf( __( '%s or higher' ), $api->requires );
						?>
					</li>
				<?php } if ( ! empty( $api->tested ) ) { ?>
					<li><strong><?php _e( 'Compatible up to:' ); ?></strong> <?php echo $api->tested; ?></li>
				<?php } if ( ! empty( $api->requires_php ) ) { ?>
					<li>
						<strong><?php _e( 'Requires PHP Version:' ); ?></strong>
						<?php
						/* translators: %s: Version number. */
						printf( __( '%s or higher' ), $api->requires_php );
						?>
					</li>
				<?php } if ( isset( $api->active_installs ) ) { ?>
					<li><strong><?php _e( 'Active Installations:' ); ?></strong>
					<?php
					if ( $api->active_installs >= 1000000 ) {
						$active_installs_millions = floor( $api->active_installs / 1000000 );
						printf(
							/* translators: %s: Number of millions. */
							_nx( '%s+ Million', '%s+ Million', $active_installs_millions, 'Active plugin installations' ),
							number_format_i18n( $active_installs_millions )
						);
					} elseif ( 0 == $api->active_installs ) {
						_ex( 'Less Than 10', 'Active plugin installations' );
					} else {
						echo number_format_i18n( $api->active_installs ) . '+';
					}
					?>
					</li>
				<?php } if ( ! empty( $api->slug ) && empty( $api->external ) ) { ?>
					<li><a target="_blank" href="<?php echo __( 'https://wordpress.org/plugins/' ) . $api->slug; ?>/"><?php _e( 'WordPress.org Plugin Page &#187;' ); ?></a></li>
				<?php } if ( ! empty( $api->homepage ) ) { ?>
					<li><a target="_blank" href="<?php echo esc_url( $api->homepage ); ?>"><?php _e( 'Plugin Homepage &#187;' ); ?></a></li>
				<?php } if ( ! empty( $api->donate_link ) && empty( $api->contributors ) ) { ?>
					<li><a target="_blank" href="<?php echo esc_url( $api->donate_link ); ?>"><?php _e( 'Donate to this plugin &#187;' ); ?></a></li>
				<?php } ?>
			</ul>
			<?php if ( ! empty( $api->rating ) ) { ?>
				<h3><?php _e( 'Average Rating' ); ?></h3>
				<?php
				wp_star_rating(
					array(
						'rating' => $api->rating,
						'type'   => 'percent',
						'number' => $api->num_ratings,
					)
				);
				?>
				<p aria-hidden="true" class="fyi-description">
					<?php
					printf(
						/* translators: %s: Number of ratings. */
						_n( '(based on %s rating)', '(based on %s ratings)', $api->num_ratings ),
						number_format_i18n( $api->num_ratings )
					);
					?>
				</p>
				<?php
			}

			if ( ! empty( $api->ratings ) && array_sum( (array) $api->ratings ) > 0 ) {
				?>
				<h3><?php _e( 'Reviews' ); ?></h3>
				<p class="fyi-description"><?php _e( 'Read all reviews on WordPress.org or write your own!' ); ?></p>
				<?php
				foreach ( $api->ratings as $key => $ratecount ) {
					// Avoid div-by-zero.
					$_rating    = $api->num_ratings ? ( $ratecount / $api->num_ratings ) : 0;
					$aria_label = esc_attr(
						sprintf(
							/* translators: 1: Number of stars (used to determine singular/plural), 2: Number of reviews. */
							_n(
								'Reviews with %1$d star: %2$s. Opens in a new tab.',
								'Reviews with %1$d stars: %2$s. Opens in a new tab.',
								$key
							),
							$key,
							number_format_i18n( $ratecount )
						)
					);
					?>
					<div class="counter-container">
							<span class="counter-label">
								<?php
								printf(
									'<a href="%s" target="_blank" aria-label="%s">%s</a>',
									"https://wordpress.org/support/plugin/{$api->slug}/reviews/?filter={$key}",
									$aria_label,
									/* translators: %s: Number of stars. */
									sprintf( _n( '%d star', '%d stars', $key ), $key )
								);
								?>
							</span>
							<span class="counter-back">
								<span class="counter-bar" style="width: <?php echo 92 * $_rating; ?>px;"></span>
							</span>
						<span class="counter-count" aria-hidden="true"><?php echo number_format_i18n( $ratecount ); ?></span>
					</div>
					<?php
				}
			}
			if ( ! empty( $api->contributors ) ) {
				?>
				<h3><?php _e( 'Contributors' ); ?></h3>
				<ul class="contributors">
					<?php
					foreach ( (array) $api->contributors as $contrib_username => $contrib_details ) {
						$contrib_name = $contrib_details['display_name'];
						if ( ! $contrib_name ) {
							$contrib_name = $contrib_username;
						}
						$contrib_name = esc_html( $contrib_name );

						$contrib_profile = esc_url( $contrib_details['profile'] );
						$contrib_avatar  = esc_url( add_query_arg( 's', '36', $contrib_details['avatar'] ) );

						echo "<li><a href='{$contrib_profile}' target='_blank'><img src='{$contrib_avatar}' loading='lazy' width='18' height='18' alt='' />{$contrib_name}</a></li>";
					}
					?>
				</ul>
						<?php if ( ! empty( $api->donate_link ) ) { ?>
					<a target="_blank" href="<?php echo esc_url( $api->donate_link ); ?>"><?php _e( 'Donate to this plugin &#187;' ); ?></a>
				<?php } ?>
					<?php } ?>
		</div>
		<div id="section-holder">
		<?php
		$requires_php = isset( $api->requires_php ) ? $api->requires_php : null;
		$requires_wp  = isset( $api->requires ) ? $api->requires : null;

		$compatible_php = is_php_version_compatible( $requires_php );
		$compatible_wp  = is_wp_version_compatible( $requires_wp );
		$tested_wp      = ( empty( $api->tested ) || version_compare( get_bloginfo( 'version' ), $api->tested, '<=' ) );

		if ( ! $compatible_php ) {
			echo '<div class="notice notice-error notice-alt"><p>';
			_e( '<strong>Error:</strong> This plugin <strong>requires a newer version of PHP</strong>.' );
			if ( current_user_can( 'update_php' ) ) {
				printf(
					/* translators: %s: URL to Update PHP page. */
					' ' . __( '<a href="%s" target="_blank">Click here to learn more about updating PHP</a>.' ),
					esc_url( wp_get_update_php_url() )
				);

				wp_update_php_annotation( '</p><p><em>', '</em>' );
			} else {
				echo '</p>';
			}
			echo '</div>';
		}

		if ( ! $tested_wp ) {
			echo '<div class="notice notice-warning notice-alt"><p>';
			_e( '<strong>Warning:</strong> This plugin <strong>has not been tested</strong> with your current version of WordPress.' );
			echo '</p></div>';
		} elseif ( ! $compatible_wp ) {
			echo '<div class="notice notice-error notice-alt"><p>';
			_e( '<strong>Error:</strong> This plugin <strong>requires a newer version of WordPress</strong>.' );
			if ( current_user_can( 'update_core' ) ) {
				printf(
					/* translators: %s: URL to WordPress Updates screen. */
					' ' . __( '<a href="%s" target="_parent">Click here to update WordPress</a>.' ),
					self_admin_url( 'update-core.php' )
				);
			}
			echo '</p></div>';
		}

		foreach ( (array) $api->sections as $section_name => $content ) {
			$content = links_add_base_url( $content, 'https://wordpress.org/plugins/' . $api->slug . '/' );
			$content = links_add_target( $content, '_blank' );

			$san_section = esc_attr( $section_name );

			$display = ( $section_name === $section ) ? 'block' : 'none';

			echo "\t<div id='section-{$san_section}' class='section' style='display: {$display};'>\n";
			echo $content;
			echo "\t</div>\n";
		}
		echo "</div>\n";
		echo "</div>\n";
		echo "</div>\n"; // #plugin-information-scrollable
		echo "<div id='$tab-footer'>\n";
		if ( ! empty( $api->download_link ) && ( current_user_can( 'install_plugins' ) || current_user_can( 'update_plugins' ) ) ) {
			$status = install_plugin_install_status( $api );
			switch ( $status['status'] ) {
				case 'install':
					if ( $status['url'] ) {
						if ( $compatible_php && $compatible_wp ) {
							echo '<a data-slug="' . esc_attr( $api->slug ) . '" id="plugin_install_from_iframe" class="button button-primary right" href="' . $status['url'] . '" target="_parent">' . __( 'Install Now' ) . '</a>';
						} else {
							printf(
								'<button type="button" class="button button-primary button-disabled right" disabled="disabled">%s</button>',
								_x( 'Cannot Install', 'plugin' )
							);
						}
					}
					break;
				case 'update_available':
					if ( $status['url'] ) {
						if ( $compatible_php ) {
							echo '<a data-slug="' . esc_attr( $api->slug ) . '" data-plugin="' . esc_attr( $status['file'] ) . '" id="plugin_update_from_iframe" class="button button-primary right" href="' . $status['url'] . '" target="_parent">' . __( 'Install Update Now' ) . '</a>';
						} else {
							printf(
								'<button type="button" class="button button-primary button-disabled right" disabled="disabled">%s</button>',
								_x( 'Cannot Update', 'plugin' )
							);
						}
					}
					break;
				case 'newer_installed':
					/* translators: %s: Plugin version. */
					echo '<a class="button button-primary right disabled">' . sprintf( __( 'Newer Version (%s) Installed' ), $status['version'] ) . '</a>';
					break;
				case 'latest_installed':
					echo '<a class="button button-primary right disabled">' . __( 'Latest Version Installed' ) . '</a>';
					break;
			}
		}
		echo "</div>\n";

		iframe_footer();
		exit;
		// @codingStandardsIgnoreEnd
	}

	/**
	 * Execute Update Database
	 *
	 * @return void
	 */
	public function update_db() {
		new Lasso_Update_DB();
	}

	/**
	 * Load needed files
	 */
	public function lasso_update_checker_loader() {
		include_once LASSO_PLUGIN_PATH . '/libs/plugin_update_checker/plugin-update-checker.php';
	}
}
