<?php
/**
    Widget - Flickr Badges Widget
    
    For another improvement, you can drop email to zourbuth@gmail.com or visit http://zourbuth.com
    
    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
 
class Flickr_Badges_Widget extends WP_Widget {
	
	var $prefix; 
	var $textdomain;
	var $transient;
	
	
	/**
	 * Set up the widget's unique name, ID, class, description, and other options
	 * @since 1.2.1
	**/			
	function __construct() {
	
		// Set default variable for the widget instances
		$this->prefix = 'zflickr';	
		$this->textdomain = 'flickr-badges-widget';
		$this->transient = "_fbw_transient_.{$this->number}";
		
		// Set up the widget control options
		$control_options = array(
			'width' => 444,
			'height' => 350,
			'id_base' => $this->prefix
		);
				
		// Add some informations to the widget
		$widget_options = array('classname' => 'widget_flickr', 'description' => __( '[+] Displays a Flickr photo stream from an ID', $this->textdomain ) );
		
		// Create the widget
		parent::__construct( $this->prefix, __('Flickr Badge', $this->textdomain), $widget_options, $control_options );
		
		// Load additional scripts and styles file to the widget admin area
		add_action( 'load-widgets.php', array(&$this, 'widget_admin') );
		
		// Load the widget stylesheet for the widgets screen.
		if ( is_active_widget( false, false, $this->id_base, false ) && ! is_admin() ) {			
			wp_enqueue_style( 'fbw', FLICKR_BADGES_WIDGET_URL . 'css/widget.css', false, 0.7, 'screen' );
			add_action( 'wp_head', array( &$this, 'print_script' ) );
		}
	}
	
	
	/**
	 * Push all script and style from the widget "Custom Style & Script" box.
	 * @since 1.2.1
	**/	
	function print_script() {
		foreach ( (array) $this->get_settings() as $key => $setting ) {			
			do_action( 'flickr_badges_widget_head', $setting );
			
			if ( ! empty( $setting['custom'] ) ) 
				echo $setting['custom'];
		}
	}
	
	
	/**
	 * Push additional script and style files to the widget admin area
	 * @since 1.2.1
	**/		
	function widget_admin() {
		wp_enqueue_style( 'z-flickr-admin', FLICKR_BADGES_WIDGET_URL . 'css/dialog.css' );
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'z-flickr-admin', FLICKR_BADGES_WIDGET_URL . 'js/jquery.dialog.js' );	
	}
	
		
	/**
	 * Outputs another item
	 * @since 1.2.2
	 */
	function fes_load_utility() {
		// Check the nonce and if not isset the id, just die.
		//$nonce = $_POST['nonce'];
		//if ( ! wp_verify_nonce( $nonce, 'fes-nonce' ) )
		//	die();

		if ( false === ( $res = get_transient( '_premium_plugins' ) ) ) {
		
			$request = wp_remote_get( "http://marketplace.envato.com/api/edge/collection:4204349.json" );

			if ( ! is_wp_error( $request ) )
				$res = json_decode( wp_remote_retrieve_body( $request ) );
				
			set_transient( '_premium_plugins', $res, 60*60*24*7 ); // cache for a week
		}
		
		if( isset( $res->collection ) )			
			foreach( $res->collection as $item )
				echo "<a href='{$item->url}?ref=zourbuth'><img src='{$item->thumbnail}'></a>&nbsp;";
	}

	
	/**
	 * Push the widget stylesheet widget.css into widget admin page
	 * @since 1.2.1
	**/		
	function widget( $args, $instance ) {
		extract( $args );

		// Set up the arguments for wp_list_categories().
		$instance = flickr_badges_widget_parse_args( (array) $instance );
			
		echo $before_widget; // print the before widget
		
		if ( $instance['title'] )
			echo $before_title . $instance['title'] . $after_title;			

		echo flickr_badges_widget( $instance );
				
		echo $after_widget; // print the after widget
	}

	
	/**
	 * Widget update functino
	 * @since 1.2.1
	**/		
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		delete_transient( $this->id ); // delete cached data
		
		// Update template arguments if new template selected					
		$instance['template'] 		= $new_instance['template']; // maintain template name
		
		foreach ( flickr_badges_widget_template_args( $new_instance ) as $k => $tpl ) {
			if ( 'checkbox' == $tpl['type'] )
				if ( $new_instance['template']['name'] == $old_instance['template']['name'] )
					$instance['template'][$k] = isset( $new_instance['template'][$k] ) ? 1 : 0;	
				else
					$instance['template'][$k] = $tpl['default'];	
			else
				$instance['template'][$k] = isset( $new_instance['template'][$k] ) ? $new_instance['template'][$k] : $tpl['default'];	
		}
		
		$instance['id'] 			= $this->id;
		$instance['type'] 			= strip_tags( $new_instance['type'] );
		$instance['flickr_id'] 		= strip_tags( $new_instance['flickr_id'] );
		$instance['count'] 			= (int) $new_instance['count'];
		$instance['cached']			= (int) $new_instance['cached'];	
		$instance['display'] 		= strip_tags( $new_instance['display'] );
		$instance['size']			= strip_tags( $new_instance['size'] );
		$instance['title']			= strip_tags( $new_instance['title'] );
		$instance['target_blank']	= isset( $new_instance['target_blank'] ) ? 1 : 0;
		$instance['copyright']		= isset( $new_instance['copyright'] ) ? 1 : 0;
		$instance['tab']			= $new_instance['tab'];
		$instance['intro_text'] 	= $new_instance['intro_text'];
		$instance['outro_text']		= $new_instance['outro_text'];
		$instance['custom']			= $new_instance['custom'];

		return $instance;
	}

	
	/**
	 * Widget form function
	 * @since 1.2.1
	**/		
	function form( $instance ) {

		$instance = flickr_badges_widget_parse_args( $instance );

		$types = array( 
			'user'  => esc_attr__( 'user', $this->textdomain ), 
			'group' => esc_attr__( 'group', $this->textdomain )
		);
		
		$templates = apply_filters( 'flickr_badges_widget_templates', array() );

		$sizes = array(
			's' => esc_attr__( 'Standard', $this->textdomain ), 
			't' => esc_attr__( 'Thumbnail', $this->textdomain ),
			'm' => esc_attr__( 'Medium', $this->textdomain )
		);
		$displays = array( 
			'latest' => esc_attr__( 'latest', $this->textdomain ),
			'random' => esc_attr__( 'random', $this->textdomain )
		);
		
		$tabs = array( 
			__( 'General', $this->textdomain ),  
			__( 'Advanced', $this->textdomain ),
			__( 'Template', $this->textdomain ),
			__( 'Customs', $this->textdomain ),
			__( 'Feeds', $this->textdomain ),
			__( 'Supports', $this->textdomain ) 
		);
		?>
		
		<div class="pluginName">Flickr Badges Widget
			<span class="pluginVersion"><?php echo FLICKR_BADGES_WIDGET_VERSION; ?></span>
		</div>
		<script type="text/javascript">
			// Tabs function
			jQuery(document).ready(function($){
				// Tabs function
				$('ul.nav-tabs li').each(function(i) {
					$(this).bind("click", function(){
						var liIndex = $(this).index();
						var content = $(this).parent("ul").next().children("li").eq(liIndex);
						$(this).addClass('active').siblings("li").removeClass('active');
						$(content).show().addClass('active').siblings().hide().removeClass('active');
	
						$(this).parent("ul").find("input").val(0);
						$('input', this).val(1);
					});
				});
				
				// Widget background
				$("#fbw-<?php echo $this->id; ?>").closest(".widget-inside").addClass("ntotalWidgetBg");
			});
		</script>
		
		<div id="fbw-<?php echo $this->id ; ?>" class="totalControls tabbable tabs-left">
			<ul class="nav nav-tabs">
				<?php foreach ($tabs as $key => $tab ) : ?>
					<li class="fes-<?php echo $key; ?> <?php echo $instance['tab'][$key] ? 'active' : '' ; ?>"><?php echo $tab; ?><input type="hidden" name="<?php echo $this->get_field_name( 'tab' ); ?>[]" value="<?php echo $instance['tab'][$key]; ?>" /></li>
				<?php endforeach; ?>							
			</ul>
			
			<ul class="tab-content">
				<li class="tab-pane <?php if ( $instance['tab'][0] ) : ?>active<?php endif; ?>">
					<ul>
						<li>
							<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title', $this->textdomain); ?></label>
							<span class="controlDesc"><?php _e( 'Give the widget title, or leave it empty for no title.', $this->textdomain ); ?></span>
							<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr( $instance['title'] ); ?>" />
						</li>
						<li>
							<label for="<?php echo $this->get_field_id('type'); ?>"><?php _e( 'Type', $this->textdomain ); ?></label>
							<span class="controlDesc"><?php _e( 'The type of images from user or group.', $this->textdomain ); ?></span>
							<select id="<?php echo $this->get_field_id( 'type' ); ?>" name="<?php echo $this->get_field_name( 'type' ); ?>">
								<?php foreach ( $types as $k => $v ) { ?>
									<option value="<?php echo esc_attr( $k ); ?>" <?php selected( $instance['type'], $k ); ?>><?php echo esc_html( $v ); ?></option>
								<?php } ?>
							</select>				
						</li>
						<li>
							<label for="<?php echo $this->get_field_id('flickr_id'); ?>"><?php _e('Flickr ID', $this->textdomain); ?></label>
							<span class="controlDesc"><?php _e( 'Put the flickr ID here, go to <a href="http://goo.gl/PM6rZ" target="_blank">Flickr NSID Lookup</a> if you don\'t know your ID. Example: 71865026@N00', $this->textdomain ); ?></span>
							<input id="<?php echo $this->get_field_id('flickr_id'); ?>" name="<?php echo $this->get_field_name('flickr_id'); ?>" type="text" value="<?php echo esc_attr( $instance['flickr_id'] ); ?>" />							
						</li>
						<li>
							<label for="<?php echo $this->get_field_id('count'); ?>"><?php _e('Number', $this->textdomain); ?></label>
							<span class="controlDesc"><?php _e( 'Number of images shown from 1 to 10', $this->textdomain ); ?></span>
							<input class="column-last" id="<?php echo $this->get_field_id('count'); ?>" name="<?php echo $this->get_field_name('count'); ?>" type="text" value="<?php echo esc_attr( $instance['count'] ); ?>" size="3" />
						</li>
						<li>
							<label for="<?php echo $this->get_field_id('display'); ?>"><?php _e('Display Method', $this->textdomain); ?></label>
							<span class="controlDesc"><?php _e( 'Get the image from recent or use random function.', $this->textdomain ); ?></span>
							<select id="<?php echo $this->get_field_id( 'display' ); ?>" name="<?php echo $this->get_field_name( 'display' ); ?>">
								<?php foreach ( $displays as $k => $v ) { ?>
									<option value="<?php echo esc_attr( $k ); ?>" <?php selected( $instance['display'], $k ); ?>><?php echo esc_html( $v ); ?></option>
								<?php } ?>
							</select>	
						</li>
						<li>
							<label for="<?php echo $this->get_field_id('sizes'); ?>"><?php _e( 'Sizes', $this->textdomain ); ?></label>
							<span class="controlDesc"><?php _e( 'Represents the size of the image', $this->textdomain ); ?></span>
							<select id="<?php echo $this->get_field_id( 'size' ); ?>" name="<?php echo $this->get_field_name( 'size' ); ?>">
								<?php foreach ( $sizes as $k => $v ) { ?>
									<option value="<?php echo $k; ?>" <?php selected( $instance['size'], $k ); ?>><?php echo $v; ?></option>
								<?php } ?>
							</select>				
						</li>							
					</ul>
				</li>

				<li class="tab-pane <?php if ( $instance['tab'][1] ) : ?>active<?php endif; ?>">
					<ul>
						<li>
							<label for="<?php echo $this->get_field_id('cached'); ?>"><?php _e( 'Cached Transient', $this->textdomain); ?></label>
							<span class="controlDesc"><?php _e( 'Total seconds to store the cached.', $this->textdomain ); ?></span>
							<input class="column-last" id="<?php echo $this->get_field_id('cached'); ?>" name="<?php echo $this->get_field_name('cached'); ?>" type="number" value="<?php echo esc_attr( $instance['cached'] ); ?>" />
						</li>
						<li>
							<label for="<?php echo $this->get_field_id( 'target_blank' ); ?>">
							<input class="checkbox" type="checkbox" <?php checked( $instance['target_blank'], true ); ?> id="<?php echo $this->get_field_id( 'target_blank' ); ?>" name="<?php echo $this->get_field_name( 'target_blank' ); ?>" /><?php _e( 'Target blank', $this->textdomain ); ?></label>
							<span class="controlDesc"><?php _e( 'Open image in a new window or tab.', $this->textdomain ); ?></span>
						</li>
						<li>
							<label for="<?php echo $this->get_field_id( 'copyright' ); ?>">
							<input class="checkbox" type="checkbox" <?php checked( $instance['copyright'], true ); ?> id="<?php echo $this->get_field_id( 'copyright' ); ?>" name="<?php echo $this->get_field_name( 'copyright' ); ?>" /><?php _e( 'Show Copyright', $this->textdomain ); ?></label>
							<span class="controlDesc"><?php _e( 'Display the plugin name with link in the front end.', $this->textdomain ); ?></span>
						</li>	
					</ul>
				</li>

				<li class="tab-pane <?php if ( $instance['tab'][2] ) : ?>active<?php endif; ?>">
					<ul>						
						<li>
							<label for="<?php echo $this->get_field_id('template'); ?>-name"><?php _e( 'Template', $this->textdomain ); ?></label>
							<span class="controlDesc"><?php _e( 'Select a template from list below.<br /><a href="http://zourbuth.com/flickr-badges-widget-pro/"><b>Upgrade to PRO</b></a> for premium templates.', $this->textdomain ); ?></span>
							<select onchange="wpWidgets.save(jQuery(this).closest('div.widget'),0,1,0);" id="<?php echo $this->get_field_id( 'template' ); ?>-name" name="<?php echo $this->get_field_name( 'template' ); ?>[name]">
								<?php foreach ( $templates as $k => $v ) { ?>
									<option value="<?php echo esc_attr( $k ); ?>" <?php selected( $instance['template']['name'], $k ); ?>><?php echo esc_html( $v ); ?></option>
								<?php } ?>
							</select>
							<?php do_action( 'zg_purchase_button' ); ?>
						</li>															
						<?php
							// Template Parsing
							$tmpl = flickr_badges_widget_template_args( $instance ); 
							unset( $tmpl['name'] ); // avoid to create option for 'name'

							// Get template dialog arguments and set the value
							foreach ( (array) $tmpl as $k => $v ) {
								$dlg = $v;
								
								$dlg['id'] = $this->get_field_id( 'template' ) ."-$k";
								$dlg['name'] = $this->get_field_name( 'template' ) ."[$k]";																
								$dlg['value'] = isset( $instance['template'][$k] ) ? $instance['template'][$k] : $v['default'];

								// Overwrite if each dialog have multiple options
								if ( isset( $v['name'] ) && is_array( $v['name'] ) ) {
									$dlg['id'] = $dlg['name'] = $dlg['value'] = array();
									foreach( $v['name'] as $i => $n ) {
										$dlg['id'][$i] = $this->get_field_id( 'template' ) ."-$n";
										$dlg['name'][$i] = $this->get_field_name( 'template' ) ."[$n]";
										$dlg['value'][$i] = isset( $instance['template'][$n] ) ? $instance['template'][$n] : $v['default'][$i];
									}
								}

								Gumaraphous_Dialog::create_dialog( $dlg );
							}
						?>
					</ul>
				</li>

				<li class="tab-pane <?php if ( $instance['tab'][3] ) : ?>active<?php endif; ?>">
					<ul>
						<li>
							<label><?php _e( 'Shortcode & Function', $this->textdomain ) ; ?></label>
							<span class="controlDesc">								
								<?php _e( '<strong>Note</strong>: Drag this widget to the "Inactive Widgets" at the bottom of this page if you want to use this as a shortcode to your content or PHP function in your template with the codes above.', $this->textdomain ); ?>
								<span class="shortcode" style="padding: 10px; border: 1px solid #ddd; display: inline-block; margin-top: 10px;">
									<?php _e( 'Widget Shortcode: ', $this->textdomain ); ?><?php echo '[flickr-badge-widget id="' . $this->number . '"]'; ?><br />
									<?php _e( 'PHP Function: ', $this->textdomain ); ?><?php echo '&lt;?php echo do_shortcode(\'[flickr-badge-widget id="' . $this->number . '"]\'); ?&gt;'; ?>						
								</span>
							</span>
						</li>					
						<li>
							<label for="<?php echo $this->get_field_id('intro_text'); ?>"><?php _e( 'Intro Text', $this->textdomain ); ?></label>
							<span class="controlDesc"><?php _e( 'This option will display addtional text before the widget content and HTML supports.', $this->textdomain ); ?></span>
							<textarea name="<?php echo $this->get_field_name( 'intro_text' ); ?>" id="<?php echo $this->get_field_id( 'intro_text' ); ?>" rows="2" class="widefat"><?php echo esc_textarea($instance['intro_text']); ?></textarea>
						</li>
						<li>
							<label for="<?php echo $this->get_field_id('outro_text'); ?>"><?php _e( 'Outro Text', $this->textdomain ); ?></label>
							<span class="controlDesc"><?php _e( 'This option will display addtional text after widget and HTML supports.', $this->textdomain ); ?></span>
							<textarea name="<?php echo $this->get_field_name( 'outro_text' ); ?>" id="<?php echo $this->get_field_id( 'outro_text' ); ?>" rows="2" class="widefat"><?php echo esc_textarea($instance['outro_text']); ?></textarea>
							
						</li>				
						<li>
							<label for="<?php echo $this->get_field_id('custom'); ?>"><?php _e( 'Custom Script & Stylesheet', $this->textdomain ) ; ?></label>
							<span class="controlDesc"><?php _e( 'Use this box for additional widget CSS style of custom javascript. Current widget selector: ', $this->textdomain ); ?><?php echo '<tt>#' . $this->id . '</tt>'; ?></span>
							<textarea name="<?php echo $this->get_field_name( 'custom' ); ?>" id="<?php echo $this->get_field_id( 'custom' ); ?>" rows="5" class="widefat code"><?php echo htmlentities($instance['custom']); ?></textarea>
						</li>
					</ul>
				</li>
				
				<li class="tab-pane <?php if ( $instance['tab'][4] ) : ?>active<?php endif; ?>">
					<ul>
						<li>
							<h4><?php _e( 'Zourbuth Blog Feeds', $this->textdomain ) ; ?></h4>
							<?php wp_widget_rss_output( 'http://www.codecheese.com/category/wordpress/feed/', array( 'items' => 10 ) ); ?>
						</li>
					</ul>
				</li>
				<li class="tab-pane <?php if ( $instance['tab'][5] ) : ?>active<?php endif; ?>">
					<ul>
						<li>
							<p><strong>Our Premium Plugins</strong></p>
							<p><?php $this->fes_load_utility(); ?></p>
						</li>
						<li>	
							<a href="http://www.ground6.com/wordpress-plugins/flickr-badges-widget/"><b>Have a questions? Please feel free to contact supports section</b></a><br /><br />
							
							<a target="_blank" href="http://feedburner.google.com/fb/a/mailverify?uri=zourbuth&amp;loc=en_US">Subscribe to zourbuth by Email</a><br />
							<?php _e( 'Like my work? Please consider to ', $this->textdomain ); ?><a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=W6D3WAJTVKAFC" title="Donate"><?php _e( 'donate', $this->textdomain ); ?></a>.<br /><br />
							
							If you like this plugin, please <a href="http://wordpress.org/support/view/plugin-reviews/flickr-badges-widget">give a good rating</a>.<br /><br />
							
							<span style="font-size: 11px;"><a href="http://wordpress.org/extend/plugins/flickr-badges-widget/"><span style="color: #0063DC; font-weight: bold;">Flick</span><span style="color: #FF0084; font-weight: bold;">r</span> Badges Widget</a> &copy; Copyright <a href="http://zourbuth.com">Zourbuth</a> <?php echo date("Y"); ?></span>.
						</li>
						<?php do_action( 'flickr_badges_widget_head' , $instance ); ?>
					</ul>
				</li>

			</ul>
		</div>			
		<?php
	}
}