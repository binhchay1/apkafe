<?php
/**
* Loading Google Analytics 4 scripts in header.
*/
class Ht_Easy_Ga4{

	/**
   * [$_instance]
   * @var null
  */
  private static $_instance = null;

  /**
   * [instance] Initializes a singleton instance
   * @return [Easy_Google_Analytics]
  */
  public static function instance() {
    if ( is_null( self::$_instance ) ) {
      self::$_instance = new self();
    }
    return self::$_instance;
  }
	
	function __construct(){
    add_action( 'init', [ $this, 'i18n' ] );
    add_action( 'plugins_loaded', [ $this, 'init' ] );
	}

	/**
   * [i18n] Load Text Domain
   * @return [void]
  */
  public function i18n() {
    load_plugin_textdomain( 'ht-easy-ga4', false, dirname( plugin_basename( HT_EASY_GA4_ROOT ) ) . '/languages/' );
  }

  public function init() {
  	// Plugins Required File
  	$this->includes();

    //add settings in plugin action
    add_filter('plugin_action_links_'.HT_EASY_GA4_BASE,function($links){
      $link = sprintf("<a href='%s'>%s</a>",esc_url(admin_url('admin.php?page=ht-easy-ga4-setting-page')),__('Settings','ht-easy-ga4'));

      array_push($links,$link);

      return $links;
    });

  	if(ht_easy_ga4_get_id()){
  		add_action( 'wp_head', [ $this, 'ht_easy_ga4_header_scirpt_render' ] );
  	}

  }
  public function includes() {
    require_once ( HT_EASY_GA4_PATH . 'admin/Recommended_Plugins.php' );
    require_once ( HT_EASY_GA4_PATH . 'admin/admin-init.php' );
  }

 	public function ht_easy_ga4_header_scirpt_render(){
 		?>
    <!-- Global site tag (gtag.js) - Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo esc_js(ht_easy_ga4_get_id()); ?>"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());

      gtag('config', <?php echo "'".esc_js(ht_easy_ga4_get_id())."'"; ?>);
    </script>
 		<?php
 	}

}

Ht_Easy_Ga4::instance();
