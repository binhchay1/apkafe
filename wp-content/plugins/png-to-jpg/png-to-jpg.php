<?php
/*
	Plugin Name: PNG to JPG
	Plugin URI: https://kubiq.sk
	Description: Convert PNG images to JPG, free up web space and speed up your webpage
	Version: 4.1
	Author: KubiQ
	Author URI: https://kubiq.sk
	Text Domain: png_to_jpg
	Domain Path: /languages
*/

/*
** TODO
** - js show when image was not converted and do not hide it
** - pagination
** - show progress of converting
** - restore PNG version
** - use more try-catch to prevent server errors
*/

class png_to_jpg{
	var $plugin_admin_page;
	var $settings;
	var $tab;
	var $image;
	var $converted_stats;

	function __construct(){
		add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ) );
		add_action( 'admin_menu', array( $this, 'plugin_menu_link' ) );
		add_action( 'init', array( $this, 'plugin_init' ) );
		add_action( 'admin_notices', array( $this, 'server_gd_library' ) );
		add_filter( 'wp_handle_upload', array( $this, 'upload_converting' ) );
		add_action( 'wp_ajax_hasTransparency', array( $this, 'hasTransparency' ) );
		add_action( 'wp_ajax_convert_old_png', array( $this, 'convert_old_png' ) );
		add_action( 'add_attachment', array( $this, 'attachment_converted_meta' ) );
		add_action( 'attachment_updated', array( $this, 'attachment_converted_meta' ) );
		add_action( 'attachment_submitbox_misc_actions', array( $this, 'attachment_submitbox_misc_actions' ), 90, 1 );
		add_action( 'admin_init', array( $this, 'alter_media_template' ) );
		add_action( 'delete_attachment', array( $this, 'delete_attachment_png_version' ), 10, 2 );
	}

	function delete_attachment_png_version( $post_id, $post ){
		if( get_post_meta( $post_id, 'png_converted', 1 ) && $filepath = get_post_meta( $post_id, '_wp_attached_file', 1 ) ){
			$uploadpath = wp_get_upload_dir();
			$parts = explode( '.', $filepath );
			array_pop( $parts );
			$filepath = $uploadpath['basedir'] . '/' . implode( '.', $parts ) . '.png';
			if( file_exists( $filepath ) ){
				@unlink( $filepath );
			}
		}
	}

	function alter_media_template(){

		function convert_stats( $content ){
			return apply_filters( 'final_output', $content );
		}

		ob_start( 'convert_stats' );

		add_filter( 'final_output', array( $this, 'final_output' ) );
		add_filter( 'wp_prepare_attachment_for_js', array( $this, 'wp_prepare_attachment_for_js' ), 10, 3 );
	}

	function final_output( $content ){
		if( strpos( $content, "data.png_converted" ) === false ){
			$content = str_replace(
				"<# if ( 'image' === data.type && ! data.uploading ) { #>",
				"<# if ( data.png_converted && data.controller.el.getAttribute('class').indexOf('edit-attachment-frame') != -1 ){ #>{{{ data.png_converted }}}<# } #><# if ( 'image' === data.type && ! data.uploading ) { #>",
				$content
			);
		}
		return $content;
	}

	function wp_prepare_attachment_for_js( $response, $attachment, $meta ){
		if( $png_converted = get_post_meta( $attachment->ID, 'png_converted', 1 ) ){
			if( $png_converted < 0 ){
				$png_converted = abs( $png_converted );
				$png_converted = '<span style="color:#f00">-' . size_format( $png_converted, 2 ) . '</span>';
			}else{
				$png_converted = size_format( $png_converted, 2 );
			}
			$response['png_converted'] = '<div class="png_converted"><strong>' . __( 'PNG to JPG saved:', 'png_to_jpg' ) . '</strong> ' . $png_converted . '</div>';
		}
		return $response;
	}

	function attachment_converted_meta( $post_id ){
		if( $this->converted_stats ){
			update_post_meta( $post_id, 'png_converted', $this->converted_stats );
		}
	}

	function attachment_submitbox_misc_actions( $post ){
		if( $png_converted = get_post_meta( $post->ID, 'png_converted', 1 ) ){
			if( $png_converted < 0 ){
				$png_converted = abs( $png_converted );
				$png_converted = '<strong style="color:#f00">-' . size_format( $png_converted, 2 ) . '</strong>';
			}else{
				$png_converted = '<strong>' . size_format( $png_converted, 2 ) . '</strong>';
			}
			echo '<div class="misc-pub-section">' . __( 'PNG to JPG saved:', 'png_to_jpg' ) . ' ' . $png_converted . '</div>';
		}
	}

	function plugins_loaded(){
		load_plugin_textdomain( 'png_to_jpg', FALSE, basename( dirname( __FILE__ ) ) . '/languages/' );
	}

	function activate(){
		if( ! get_option( 'png_to_jpg_settings', 0 ) ){
			$defaults = array(
				'general' => array(
					'upload_convert' => 0,
					'jpg_quality' => '90',
					'only_lower' => 'checked',
					'leave_original' => 'checked',
					'autodetect' => 'checked'
				)
			);
			update_option( 'png_to_jpg_settings', $defaults );
		}
	}

	function filter_plugin_actions( $links, $file ){
		$settings_link = '<a href="upload.php?page=' . basename( __FILE__ ) . '">' . __('Settings') . '</a>';
		array_unshift( $links, $settings_link );
		return $links;
	}

	function plugin_menu_link(){
		$this->plugin_admin_page = add_submenu_page(
			'upload.php',
			__( 'PNG to JPG', 'png_to_jpg' ),
			__( 'PNG to JPG', 'png_to_jpg' ),
			'manage_options',
			basename( __FILE__ ),
			array( $this, 'admin_options_page' )
		);
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'filter_plugin_actions' ), 10, 2 );
	}

	function plugin_init(){
		global $wpdb;
		$this->settings = get_option('png_to_jpg_settings');
		$this->db_tables = $wpdb->get_col('SHOW TABLES');
	}

	function plugin_admin_tabs( $current = 'general' ){
		$tabs = array(
			'general' => __('General'),
			'convert' => __( 'Convert existing PNGs', 'png_to_jpg' ),
		); ?>
		<h2 class="nav-tab-wrapper">
		<?php foreach( $tabs as $tab => $name ){ ?>
			<a class="nav-tab <?php echo $tab == $current ? 'nav-tab-active' : '' ?>" href="?page=<?php echo basename( __FILE__ ) ?>&amp;tab=<?php echo $tab ?>"><?php echo $name ?></a>
		<?php } ?>
		</h2><br><?php
	}

	function admin_options_page(){
		if( get_current_screen()->id != $this->plugin_admin_page ) return;
		$this->tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'general';
		if( isset( $_POST['plugin_sent'] ) && check_admin_referer( 'save_these_settings_' . get_current_user_id(), 'settings_nonce' ) ){
			
			$this->settings = array( 'general' => array() );
			$this->settings['general']['jpg_quality'] = intval( $_POST['jpg_quality'] );
			if( $this->settings['general']['jpg_quality'] > 100 ) $this->settings['general']['jpg_quality'] = 100;
			if( $this->settings['general']['jpg_quality'] < 1 ) $this->settings['general']['jpg_quality'] = 1;

			$this->settings['general']['upload_convert'] = intval( $_POST['upload_convert'] );
			if( $this->settings['general']['upload_convert'] < 0 || $this->settings['general']['upload_convert'] > 2 ) $this->settings['general']['upload_convert'] = 0;

			if( isset( $_POST['only_lower'] ) ) $this->settings['general']['only_lower'] = 'checked';
			if( isset( $_POST['leave_original'] ) ) $this->settings['general']['leave_original'] = 'checked';
			if( isset( $_POST['autodetect'] ) ) $this->settings['general']['autodetect'] = 'checked';

			update_option( 'png_to_jpg_settings', $this->settings );
		} ?>
		<div class="wrap">
			<h2><?php _e( 'PNG to JPG', 'png_to_jpg' ); ?></h2>
			<?php if( isset( $_POST['plugin_sent'] ) ) echo '<div class="updated"><p>' . __('Settings saved.') . '</p></div>'; ?>
			<form method="post" action="<?php echo admin_url( 'upload.php?page=' . basename( __FILE__ ) ) ?>">
				<input type="hidden" name="plugin_sent" value="1"><?php
				wp_nonce_field( 'save_these_settings_' . get_current_user_id(), 'settings_nonce' );
				$this->plugin_admin_tabs( $this->tab );
				switch( $this->tab ):
					case 'general':
						$this->tab_general();
						break;
					case 'convert':
						$this->tab_convert();
						break;
				endswitch; ?>
			</form>
		</div><?php
	}

	function server_gd_library(){
		if( ! function_exists('imagecreatefrompng') ){
			echo '<div class="error"><p>' . __( 'PNG to JPG requires gd library enabled!', 'png_to_jpg' ) . '</p></div>';
		}
	}

	function tab_general(){
		global $wpdb;
		$stats = $wpdb->get_row("SELECT COUNT(*) as converted, SUM( meta_value ) as saved FROM {$wpdb->postmeta} WHERE meta_key = 'png_converted'"); ?>
		<div class="below-h2 updated">
			<p><?php
				printf( __( '%d images converted', 'png_to_jpg' ), $stats->converted );
				if( $stats->saved < 0 ){
					$stats->saved = abs( $stats->saved );
					$stats->saved = '<span style="color:#f00">-' . size_format( $stats->saved, 2 ) . '</span>';
				}else{
					$stats->saved = size_format( $stats->saved, 2 );
				}
				echo '<br>';
				printf( __( '%s saved', 'png_to_jpg' ), $stats->saved ); ?>
			</p>
		</div>
		<table class="form-table">
			<tr>
				<th>
					<label for="q_field_1"><?php _e( 'JPG quality', 'png_to_jpg' ) ?></label> 
				</th>
				<td>
					<input type="number" min="1" max="100" step="1" name="jpg_quality" placeholder="90" value="<?php echo $this->settings['general']['jpg_quality'] ?>" id="q_field_1"> %
				</td>
			</tr>
			<tr>
				<th>
					<label for="q_field_2"><?php _e( 'Convert PNG to JPG during upload', 'png_to_jpg' ) ?></label> 
				</th>
				<td><?php
					$this->q_select(array(
						'name' => 'upload_convert',
						'id' => 'q_field_2',
						'value' => $this->settings['general']['upload_convert'],
						'options' => array(
							0 => __('No'),
							1 => __('Yes'),
							2 => __( 'Yes, but only images without transparency', 'png_to_jpg' )
						)
					)); ?>
				</td>
			</tr>
			<tr>
				<th>
					<label for="q_field_5"><?php _e( 'Only convert image if JPG filesize is lower than PNG filesize', 'png_to_jpg' ) ?></label> 
				</th>
				<td>
					<input type="checkbox" name="only_lower" value="checked" id="q_field_5" <?php echo isset( $this->settings['general']['only_lower'] ) ? $this->settings['general']['only_lower'] : '' ?>>
				</td>
			</tr>
			<tr>
				<th>
					<label for="q_field_3"><?php _e( 'Leave original PNG images on the server', 'png_to_jpg' ) ?></label> 
				</th>
				<td>
					<input type="checkbox" name="leave_original" value="checked" id="q_field_3" <?php echo isset( $this->settings['general']['leave_original'] ) ? $this->settings['general']['leave_original'] : '' ?>>
				</td>
			</tr>
			<tr>
				<th>
					<label for="q_field_4"><?php _e( 'Autodetect transparency for existing PNG images', 'png_to_jpg' ) ?></label> 
				</th>
				<td>
					<input type="checkbox" name="autodetect" value="checked" id="q_field_4" <?php echo isset( $this->settings['general']['autodetect'] ) ? $this->settings['general']['autodetect'] : '' ?>>
				</td>
			</tr>
		</table>
		<p class="submit"><input type="submit" class="button button-primary button-large" value="<?php _e('Save') ?>"></p><?php
	}

	function tab_convert(){
		global $wpdb;
		$nonce = wp_create_nonce('convert_old_png');
		wp_enqueue_media();
		$query_images = new WP_Query(array(
			'post_type' => 'attachment',
			'post_mime_type' => 'image/png',
			'post_status' => 'inherit',
			'posts_per_page' => -1,
			'no_found_rows' => 1
		)); ?>
		<div class="below-h2 error"><p><?php _e( 'Do you have backup? This operation will alter your original images and cannot be undone!', 'png_to_jpg' ) ?></p></div>
		<div class="below-h2 error">
			<p>
				<?php _e( 'Converted images will be fixed only in these tables:', 'png_to_jpg' ) ?> 
				<em><?php echo "posts, postmeta, options, yoast_seo_links, revslider_slides, revslider_static_slides, toolset_post_guid_id, fpd_products, fpd_views, blc_instances, blc_links, fv_player_videos" ?></em>. 
				<?php _e( 'If you need support for more database tables from various plugins, let me know by mail to info@kubiq.sk', 'png_to_jpg' ) ?>
			</p>
		</div>
		<?php if( isset( $this->settings['general']['autodetect'] ) ): ?>
			<div id="transparency_status_message" class="below-h2 updated">
				<button type="button" class="button right" style="margin-top:4px">
					<?php esc_html_e( 'Stop it', 'png_to_jpg' ) ?>
				</button>
				<p><img src="<?php echo admin_url('/images/loading.gif') ?>" alt="" style="vertical-align:sub">&emsp;<span><?php _e( "Please wait, I'm getting transparency status for images...", 'png_to_jpg' ) ?></span></p>
			</div>
		<?php endif ?>
		<br>
		<button type="button" class="button button-primary convert-pngs"><?php _e( 'Convert selected PNGs', 'png_to_jpg' ) ?></button>
		&emsp;
		<button type="button" class="button button-default select-transparent"><?php _e( 'Select all transparent PNGs', 'png_to_jpg' ) ?></button>
		&emsp;
		<button type="button" class="button button-default select-non-transparent"><?php _e( 'Select all non-transparent PNGs', 'png_to_jpg' ) ?></button>
		<br><br>
		<table class="wp-list-table widefat striped media">
			<thead>
				<tr>
					<th class="check-column"><input type="checkbox"></th>
					<th><?php _e('Media') ?></th>
					<?php if( isset( $this->settings['general']['autodetect'] ) ): ?>
						<th><?php _e( 'Has transparency', 'png_to_jpg' ) ?></th>
					<?php endif ?>
				</tr>
			</thead>
			<tbody><?php
				foreach( $query_images->posts as $image ){
					$file_path = get_attached_file( $image->ID );
					if( file_exists( $file_path ) && substr( $file_path, -3 ) == 'png' ){
						$transparency = get_post_meta( $image->ID, 'transparency', 1 );
						$transparency = $transparency == '' ? $transparency : (int)$transparency;
						$image->link = wp_get_attachment_url( $image->ID ); ?>
						<tr data-id="<?php echo $image->ID ?>" data-transparency="<?php echo $transparency === 1 || $transparency === 0 ? $transparency : '-' ?>">
							<th scope="row" class="check-column">
								<input type="checkbox" name="media[]" value="<?php echo $image->ID ?>" <?php if( isset( $this->settings['general']['autodetect'] ) ) echo 'disabled' ?>>
							</th>
							<td class="title column-title has-row-actions column-primary">
								<strong class="has-media-icon">
									<a href="<?php echo $image->link ?>">
										<span class="media-icon image-icon">
											<?php echo wp_get_attachment_image( $image->ID, 'thumbnail' ) ?>
										</span>
										<?php echo $image->post_title ?>
									</a>
								</strong>
								<p class="filename">
									<?php echo basename( $image->link ) ?>
								</p>
							</td>
							<?php if( isset( $this->settings['general']['autodetect'] ) ): ?>
								<td class="transparency">
									<?php echo $transparency == '' ? $transparency : ( $transparency ? 'YES' : 'NO' ) ?>
								</td>
							<?php endif ?>
						</tr><?php
					}
				} ?>
			</tbody>
		</table>
		<br>
		<button type="button" class="button button-primary convert-pngs"><?php _e( 'Convert selected PNGs', 'png_to_jpg' ) ?></button>
		&emsp;
		<button type="button" class="button button-default select-transparent"><?php _e( 'Select all transparent PNGs', 'png_to_jpg' ) ?></button>
		&emsp;
		<button type="button" class="button button-default select-non-transparent"><?php _e( 'Select all non-transparent PNGs', 'png_to_jpg' ) ?></button>

		<div id="png_preview" class="media-modal" style="display:none">
			<button type="button" class="button-link media-modal-close"><span class="media-modal-icon"></span></button>
			<div class="media-modal-content">
				<div class="edit-attachment-frame mode-select hide-menu hide-router">
					<div class="media-frame-title">
						<h1><?php _e( 'Background switch:', 'png_to_jpg' ) ?></h1>
						<div id="png-background-switch">
							<a href="#" class="bg-chess active"></a>
							<a href="#" class="bg-black"></a>
							<a href="#" class="bg-white"></a>
						</div>
					</div>
					<div class="media-frame-content bg-chess">
						<div class="media-wrapper"></div>
					</div>
				</div>
			</div>
		</div>

		<style type="text/css" media="screen">
			.widefat thead .check-column{
				padding: 10px 0 0 4px;
			}
			#png_preview .bg-chess{
				background: linear-gradient(135deg, transparent 75%, rgba(255, 255, 255, 1) 0%) 0 0, linear-gradient(-45deg, transparent 75%, rgba(255, 255, 255, 1) 0%) 15px 15px, linear-gradient(135deg, transparent 75%, rgba(255, 255, 255, 1) 0%) 15px 15px, linear-gradient(-45deg, transparent 75%, rgba(255, 255, 255, 1) 0%) 0 0, #c4c4c4;
				background-size: 30px 30px;
			}
			#png_preview .bg-black{
				background: #000;
			}
			#png_preview .bg-white{
				background: #fff;
			}
			#png_preview .media-frame-content{
				display: flex;
				align-items: flex-start;
				justify-content: flex-start;
			}
			#png_preview .media-frame-title{
				display: flex;
				-ms-align-items: center;
				align-items: center;
			}
			#png-background-switch{
				display: flex;
				-ms-align-items: center;
				align-items: center;
			}
			#png-background-switch a,
			#png-background-switch a:focus{
				display: block;
				margin: 0 10px;
				width: 20px;
				height: 20px;
				border: 2px solid #000;
				border-radius: 50%;
				outline: none;
				box-shadow: none;
			}
			#png-background-switch a.active{
				border-color: #0f0;
			}
			#png-background-switch a.bg-chess{
				background-size: 10px 10px;
			}
			#png_preview .media-wrapper{
				position: relative;
				font-size: 0;
				line-height: 1;
				transition: box-shadow 300ms ease;
			}
			#png_preview .media-wrapper:hover{
				box-shadow: 0 0 0 1px #000;
			}
			#png_preview .media-wrapper span{
				content: '';
				display: block;
				padding: 3px 4px;
				position: absolute;
				top: -1px;
				left: -1px;
				background: #000;
				font-family: Consolas, monaco, monospace;
				font-size: 11px;
				color: #fff;
				opacity: 0;
				-webkit-transition: opacity 300ms ease;
				transition: opacity 300ms ease;
			}
			#png_preview .media-wrapper:hover span{
				opacity: 1;
			}
		</style>

		<script>
			jQuery(document).ready(function($) {
				$('.has-media-icon a').click(function(event) {
					event.preventDefault();
					$('#png_preview .media-wrapper').html('<span></span><img src="' + this.href + '" alt="">');
					$('<img/>', {
						load: function(){
							$('#png_preview .media-wrapper span').text( this.width + ' x ' + this.height );
						},
						src: this.href
					});
					$('#png_preview').show();
				});
				$(document).keyup(function(event) {
					if( $('#png_preview').is(':visible') ){
						var keycode = ( event.keyCode ? event.keyCode : event.which );
						if( keycode == 27 ){
							$('#png_preview').hide();
						}
					}
				});
				$('#png-background-switch a').click(function(event) {
					event.preventDefault();
					$('#png-background-switch a').removeClass('active');
					$(this).addClass('active');
					$('#png_preview .media-frame-content').removeClass('bg-chess bg-white bg-black').addClass( $(this).attr('class') );
				});
				$('#png_preview .media-modal-close').click(function(event) {
					event.preventDefault();
					$('#png_preview').hide();
				});
				$('.select-transparent').click(function(event) {
					event.preventDefault();
					$('tr[data-transparency] input').prop( 'checked', false );
					$('tr[data-transparency=1] input').prop( 'checked', true );
				});
				$('.select-non-transparent').click(function(event) {
					event.preventDefault();
					$('tr[data-transparency] input').prop( 'checked', false );
					$('tr[data-transparency=0] input').prop( 'checked', true );
				});
				$('.convert-pngs').click(function(event) {
					event.preventDefault();
					$('#transparency_status_message span').text('<?php _e( 'Please wait, converting your PNG images is in progress...', 'png_to_jpg' ) ?>');
					$('#transparency_status_message').show();
					$('tbody tr input').prop( 'disabled', true );
					delete_selected_pngs();
				});

				window.stopPNGtoJPG = false;
				$('#transparency_status_message button').on('click', function(e){
					e.preventDefault();
					stopPNGtoJPG = true;
				});

				<?php if( isset( $this->settings['general']['autodetect'] ) ): ?>
					get_transparency();

					function get_transparency(){
						var $el = $('tbody tr[data-transparency="-"]').first();
						if( $el.length && ! stopPNGtoJPG ){
							$.post( '<?php echo admin_url('admin-ajax.php') ?>', {
								action: 'hasTransparency',
								id: $el.attr('data-id')
							}, function(response){
								var transparency = parseInt(response);
								$el.attr('data-transparency', transparency);
								$el.find('.transparency').html( transparency == 1 ? 'YES' : 'NO' );
								if( ! stopPNGtoJPG ){
									get_transparency();
								}else{
									$('#transparency_status_message').hide();
									$('tbody tr input').prop( 'disabled', false );
								}
							});
						}else{
							$('#transparency_status_message').hide();
							$('tbody tr input').prop( 'disabled', false );
						}
					}
				<?php endif; ?>

				function delete_selected_pngs(){
					var $el = $('tbody tr input:checked').first();
					if( $el.length && ! stopPNGtoJPG ){
						var $tr = $el.parent().parent();
						$.post( '<?php echo admin_url('admin-ajax.php') ?>', {
							action: 'convert_old_png',
							id: $tr.attr('data-id'),
							nonce: '<?php echo $nonce ?>'
						}, function(){
							$tr.remove();
							if( stopPNGtoJPG ){
								$('#transparency_status_message').html('<p><?php _e('Done') ?>.</p>');
								$('tbody tr input').prop('disabled', false);
							}else{
								delete_selected_pngs();
							}
						}).fail(function(){
							alert( 'Your server is not powerful enough to process image ' + $.trim( $tr.find('.filename').text() ) );
						});
					}else{
						$('#transparency_status_message').html('<p><?php _e('Done') ?>.</p>');
						$('tbody tr input').prop('disabled', false);
					}
				}
			});
		</script><?php
	}

	function q_select( $field_data = array(), $print = 1, $cols = array( 'value' => 'ID', 'text' => 'post_title' ) ){
		if( ! is_object( $field_data ) ) $field_data = (object)$field_data;
		$field_data->value = is_array( $field_data->value ) ? $field_data->value : array( $field_data->value );
		$select = sprintf(
			'<select name="%s" id="%s" %s %s>',
			$field_data->name,
			$field_data->id,
			isset( $field_data->multiple ) ? 'multiple' : '',
			isset( $field_data->size ) ? 'size="' . $field_data->size . '"' : ''
		);
		if( isset( $field_data->placeholder ) ){
			$select .= '<option value="" disabled>' . $field_data->placeholder . '</option>';
		}
		foreach( $field_data->options as $option => $value ){
			if( isset( $value->ID ) || isset( $value->term_id ) ){
				$post_id = isset( $value->ID ) ? $value->ID : $value->term_id;
				$value = (array)$value;
				if( class_exists( 'PLL_Model' ) ){
					$post_lang = pll_get_post_language( $post_id );
					if( pll_default_language() != $post_lang ) continue;
				}
				$select .= sprintf(
					'<option value="%s" %s>%s</option>',
					$value[ $cols['text'] ],
					in_array( $value[ $cols['text'] ] , $field_data->value ) ? 'selected' : '',
					$value[ $cols['value'] ]
				);
			}else{
				$select .= sprintf(
					'<option value="%s" %s>%s</option>',
					$option,
					in_array( $option, $field_data->value ) ? 'selected' : '',
					$value
				);
			}
		}
		$select .= '</select>';
		if( $print )
			echo $select;
		else
			return $select;
	}

	function upload_converting( $params ){
		if( $params['type'] == 'image/png' ) {
			if( $this->settings['general']['upload_convert'] == 1 ){
				$new_params = $this->convert_image( $params );
				if( $new_params ) $params = $new_params;
			}elseif( $this->settings['general']['upload_convert'] == 2 ){
				if( ! $this->hasTransparency( $params ) ){
					$new_params = $this->convert_image( $params );
					if( $new_params ) $params = $new_params;
				}
			}
		}
		return $params;
	}

	function convert_image( $params ){
		$stats_before = filesize( $params['file'] );
		$img = imagecreatefrompng( $params['file'] );
		$bg = imagecreatetruecolor( imagesx( $img ), imagesy( $img ) );
		imagefill( $bg, 0, 0, imagecolorallocate( $bg, 255, 255, 255 ) );
		imagealphablending( $bg, 1 );
		imagecopy( $bg, $img, 0, 0, 0, 0, imagesx( $img ), imagesy( $img ) );

		$i = 1;
		$newPath = substr( $params['file'], 0, -4 ) . '.jpg';
		$newUrl = substr( $params['url'], 0, -4 ) . '.jpg';
		while( file_exists( $newPath ) ){
			$newPath = substr( $params['file'], 0, -4 ) . '-' . $i . '.jpg';
			$newUrl = substr( $params['url'], 0, -4 ) . '-' . $i . '.jpg';
			++$i;
		}

		if( imagejpeg( $bg, $newPath, $this->settings['general']['jpg_quality'] ) ){
			$this->converted_stats = $stats_before - filesize( $newPath );
			if(
				! isset( $this->settings['general']['only_lower'] )
				|| (
					isset( $this->settings['general']['only_lower'] )
					&& $this->converted_stats > 0
				)
			){
				if( ! isset( $this->settings['general']['leave_original'] ) ){
					unlink( $params['file'] );
				}
				if( is_array( $this->image ) ){
					$this->image['new_path'] = $newPath;
					$this->image['new_url'] = $newUrl;
				}
				$params['file'] = $newPath;
				$params['url'] = $newUrl;
				$params['type'] = 'image/jpeg';
				return $params;
			}else{
				$this->converted_stats = 0;
				unlink( $newPath );
			}
		}

		return 0;
	}

	function hasTransparency( $params ){
		$transparent = 0;
		if( isset( $_POST['id'] ) ){
			$image = get_attached_file( (int) $_POST['id'] );
		}else{
			$image = $params['file'];
		}
		$handle = fopen( $image, 'rb' );
		$contents = stream_get_contents( $handle );
		fclose( $handle );
		if( ord( file_get_contents( $image, false, null, 25, 1 ) ) & 4 ){
			$transparent = 1;
		}elseif( stripos( $contents, 'PLTE' ) !== false && stripos( $contents, 'tRNS' ) !== false ){
			$transparent = 1;
		}
		if( isset( $_POST['id'] ) ){
			update_post_meta( $_POST['id'], 'transparency', $transparent );
			echo $transparent;
			exit();
		}else{
			return $transparent;
		}
	}

	function convert_old_png(){
		if( defined('DOING_AJAX') && DOING_AJAX ){
			if( ! wp_verify_nonce( $_POST['nonce'], 'convert_old_png' ) ) die ('Wrong nonce!');
			$this->image = array(
				'ID' => (int) $_POST['id']
			);
			$this->image['link'] = wp_get_attachment_url( $this->image['ID'] );
			$this->image['path'] = get_attached_file( $this->image['ID'] );
			$params = array(
				'ID' => $this->image['ID'],
				'file' => $this->image['path'],
				'url' => $this->image['link'],
			);

			if( file_exists( $this->image['path'] ) ){
				if( $this->convert_image( $params ) ){
					$this->update_image_data();
				}
			}
		}
		exit();
	}

	function update_image_data(){
		global $wpdb;

		$old_name = basename( $this->image['link'] );
		$old_name_clean = substr( $old_name, 0, -4 );
		$new_name = basename( $this->image['new_url'] );
		$new_name_clean = substr( $new_name, 0, -4 );

		$replaces = array( $old_name => $new_name );

		$thumbs = wp_get_attachment_metadata( $this->image['ID'] );
		foreach( $thumbs['sizes'] as $img ){
			$thumb = dirname( $this->image['path'] ) . '/' . $img['file'];
			if( file_exists( $thumb ) ){
				$new_thumb = substr( $img['file'], 0, -4 ) . '.jpg';
				if( $old_name_clean !== $new_name_clean ){
					$new_thumb = str_replace( $old_name_clean, $new_name_clean, $new_thumb );
				}
				$replaces[ $img['file'] ] = $new_thumb;
				unlink( $thumb );
			}
		}

		wp_update_post(array(
			'ID' => $this->image['ID'],
			'post_mime_type' => 'image/jpeg'
		));

		$wpdb->update(
			$wpdb->posts,
			array( 'guid' => $this->image['new_url'] ),
			array( 'ID' => $this->image['ID'] ),
			array( '%s' ),
			array( '%d' )
		);

		$meta = get_post_meta( $this->image['ID'], '_wp_attached_file', 1 );
		$meta = str_replace( $old_name, $new_name, $meta );
		update_post_meta( $this->image['ID'], '_wp_attached_file', $meta );

		require_once( ABSPATH . 'wp-admin/includes/image.php' );
		$attach_data = wp_generate_attachment_metadata( $this->image['ID'], $this->image['new_path'] );
		update_post_meta( $this->image['ID'], '_wp_attachment_metadata', $attach_data );

		foreach( $replaces as $old => $new ){
			// WP: wp_posts
			$wpdb->query("
				UPDATE {$wpdb->posts} 
				SET post_content = REPLACE( post_content, '/{$old}', '/{$new}') 
				WHERE post_content LIKE '%/{$old}%'
			");
			$wpdb->query("
				UPDATE {$wpdb->posts} 
				SET post_excerpt = REPLACE( post_excerpt, '/{$old}', '/{$new}') 
				WHERE post_excerpt LIKE '%/{$old}%'
			");
			// WP: wp_postmeta
			$wpdb->query("
				UPDATE {$wpdb->postmeta} 
				SET meta_value = REPLACE( meta_value, '/{$old}', '/{$new}') 
				WHERE meta_value LIKE '%/{$old}%'
			");
			// WP: wp_options
			$wpdb->query("
				UPDATE {$wpdb->options} 
				SET option_value = REPLACE( option_value, '/{$old}', '/{$new}') 
				WHERE option_value LIKE '%/{$old}%'
			");
			// Yoast SEO: wp_yoast_seo_links
			$table_name = $wpdb->prefix.'yoast_seo_links';
			if( in_array( $table_name, $this->db_tables ) ){
				$wpdb->query("
					UPDATE $table_name 
					SET url = REPLACE( url, '/{$old}', '/{$new}') 
					WHERE url LIKE '%/{$old}%'
				");
			}
			// Revolution Slider: wp_revslider_slides
			$table_name = $wpdb->prefix.'revslider_slides';
			if( in_array( $table_name, $this->db_tables ) ){
				$wpdb->query("
					UPDATE $table_name 
					SET params = REPLACE( params, '/{$old}', '/{$new}') 
					WHERE params LIKE '%/{$old}%'
				");
				$wpdb->query("
					UPDATE $table_name 
					SET layers = REPLACE( layers, '/{$old}', '/{$new}') 
					WHERE layers LIKE '%/{$old}%'
				");
			}
			// Revolution Slider: wp_revslider_static_slides
			$table_name = $wpdb->prefix.'revslider_static_slides';
			if( in_array( $table_name, $this->db_tables ) ){
				$wpdb->query("
					UPDATE $table_name 
					SET layers = REPLACE( layers, '/{$old}', '/{$new}') 
					WHERE layers LIKE '%/{$old}%'
				");
			}
			// Toolset Types: wp_toolset_post_guid_id
			$table_name = $wpdb->prefix.'toolset_post_guid_id';
			if( in_array( $table_name, $this->db_tables ) ){
				$wpdb->query("
					UPDATE $table_name 
					SET guid = REPLACE( guid, '/{$old}', '/{$new}') 
					WHERE guid LIKE '%/{$old}%'
				");
			}
			// Fancy Product Designer: wp_fpd_products
			$table_name = $wpdb->prefix.'fpd_products';
			if( in_array( $table_name, $this->db_tables ) ){
				$wpdb->query("
					UPDATE $table_name 
					SET thumbnail = REPLACE( thumbnail, '/{$old}', '/{$new}') 
					WHERE thumbnail LIKE '%/{$old}%'
				");
			}
			// Fancy Product Designer: wp_fpd_views
			$table_name = $wpdb->prefix.'fpd_views';
			if( in_array( $table_name, $this->db_tables ) ){
				$wpdb->query("
					UPDATE $table_name 
					SET thumbnail = REPLACE( thumbnail, '/{$old}', '/{$new}') 
					WHERE thumbnail LIKE '%/{$old}%' 
				");
				$wpdb->query("
					UPDATE $table_name 
					SET elements = REPLACE( elements, '/{$old}', '/{$new}') 
					WHERE elements LIKE '%/{$old}%'
				");
			}
			// Broken Link Checker: wp_blc_instances
			$table_name = $wpdb->prefix.'blc_instances';
			if( in_array( $table_name, $this->db_tables ) ){
				$wpdb->query("
					UPDATE $table_name 
					SET link_text = REPLACE( link_text, '/{$old}', '/{$new}') 
					WHERE link_text LIKE '%/{$old}%' 
				");
				$wpdb->query("
					UPDATE $table_name 
					SET raw_url = REPLACE( raw_url, '/{$old}', '/{$new}') 
					WHERE raw_url LIKE '%/{$old}%'
				");
			}
			// Broken Link Checker: wp_blc_links
			$table_name = $wpdb->prefix.'blc_links';
			if( in_array( $table_name, $this->db_tables ) ){
				$wpdb->query("
					UPDATE $table_name 
					SET url = REPLACE( url, '/{$old}', '/{$new}') 
					WHERE url LIKE '%/{$old}%'
				");
				$wpdb->query("
					UPDATE $table_name 
					SET final_url = REPLACE( final_url, '/{$old}', '/{$new}') 
					WHERE final_url LIKE '%/{$old}%'
				");
				$wpdb->query("
					UPDATE $table_name 
					SET log = REPLACE( log, '/{$old}', '/{$new}') 
					WHERE log LIKE '%/{$old}%'
				");
				$wpdb->query("
					UPDATE $table_name 
					SET result_hash = REPLACE( result_hash, '/{$old}', '/{$new}') 
					WHERE result_hash LIKE '%/{$old}%'
				");
			}
			// FV Player
			$table_name = $wpdb->prefix.'fv_player_videos';
			if( in_array( $table_name, $this->db_tables ) ){
				$wpdb->query("
					UPDATE $table_name 
					SET splash = REPLACE( splash, '/{$old}', '/{$new}') 
					WHERE splash LIKE '%/{$old}%'
				");
			}
		}
	}
}

$png_to_jpg_var = new png_to_jpg();
register_activation_hook( __FILE__, array( $png_to_jpg_var, 'activate' ) );