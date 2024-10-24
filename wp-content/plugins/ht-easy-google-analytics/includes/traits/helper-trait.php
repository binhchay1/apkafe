<?php
/**
 * A Trait to help with managing the plugin admin functions
 */
namespace Ht_Easy_Ga4;

use Exception;
use WP_Error;

trait Helper_Trait {
	/**
    * File path of the Core plugin
    * @return string
    */
	public function get_pro_plugin_file(){
        return 'ht-easy-google-analytics-pro/ht-easy-google-analytics-pro.php';
    }

    /**
    * File path of the WooCommerce plugin
    * @return string
    */
	public function get_woocommerce_file(){
        return 'woocommerce/woocommerce.php';
    }

    /**
	 * This function checks if the Pro plugin is active in WordPress.
	 *
	 * @return bool
	 */
	public function is_pro_plugin_active(){
		if( is_plugin_active( $this->get_pro_plugin_file() ) ){
			return true;
		}

		return false;
	}

    /**
	 * This function checks if the pro plugin is inactive in WordPress.
	 *
	 * @return bool
	 */
    public function is_pro_plugin_inactive(){
        if( is_plugin_inactive( $this->get_pro_plugin_file() ) ){
			return true;
		}

		return false;
    }

    /**
     *  This function checks if the Pro plugin is installed in WordPress.
     * 
     * @return bool
     */
    public function is_pro_plugin_installed(){
        $plugins = get_plugins();
        if( isset( $plugins[ $this->get_pro_plugin_file() ]) ){
            return true;
        }

        return false;
    }

	/**
	 * This function checks if the WooCommerce plugin is active in WordPress.
	 *
	 * @return bool
	 */
	public function is_woocommerce_active(){
		if( is_plugin_active( $this->get_woocommerce_file() ) ){
			return true;
		}

		return false;
	}

    public function htga4_clean( $data ){
        if( is_array($data) ){
            foreach( $data as $key => $value ){
                $data[$key] = $this->htga4_clean($value);
            }
        } else {
            $data = sanitize_text_field($data);
        }

        return $data;
    }

    /**
     * Check if has all data to requst in ga4 API
     *
     * @return boolean
     */
    public function has_proper_request_data(){
        if( $this->get_access_token() && $this->get_option('account') && $this->get_option('property') && $this->get_option('data_stream_id')  ){
            return true;
        }

        return false;
    }

    /**
     * Returns the measurement ID for Google Analytics 4, either from the plugin's
     * options or from a separate option for the GA4 ID.
     * 
     * @return string the measurement ID. If the measurement ID is set in the plugin options, it will return
     * that value. Otherwise, it will return the value of the `ht_easy_ga4_id` option.
     */
    public function get_measurement_id(){
        if( !empty($this->get_option('measurement_id')) && $this->get_access_token() ){
            return $this->get_option('measurement_id');
        } elseif( !$this->get_access_token() && !empty($this->get_option('ht_easy_ga4_id')) ){
            return $this->get_option('ht_easy_ga4_id');
        } else {
            $ht_easy_ga4_id = get_option('ht_easy_ga4_id') ? get_option('ht_easy_ga4_id') : '';
        }
        
        return $ht_easy_ga4_id;
    }

    public function get_measurement_id2(){
        $ht_easy_ga4_id = '';

        if( !empty($this->get_option('measurement_id')) ){
            $ht_easy_ga4_id = $this->get_option('measurement_id');
        } elseif( !empty($this->get_option('ht_easy_ga4_id')) ){
            return $this->get_option('ht_easy_ga4_id');
        }
        
        return $ht_easy_ga4_id;
    }

    public function get_data($query_str){
        $get_data = wp_unslash($_GET); // phpcs:ignore

        if( !empty($get_data[$query_str]) ){
            return $get_data[$query_str];
        }

        return '';
    }

    public function get_current_tab(){
        $current_tab = 'general_options';

        if( !empty( $_GET['tab'] ) ){  // phpcs:ignore
			$current_tab =  sanitize_text_field( $_GET['tab'] ); // phpcs:ignore
		}

        return $current_tab;
    }

    /**
     * This function returns the access token stored in the 'htga4_access_token' option.
     * 
     * @return string the value of the 'htga4_access_token' option.
     */
    public function get_access_token(){
        return get_transient('htga4_access_token');
    }

    /**
     * @param data_name The parameter "data_name" is a string that represents the name of the data that
     * needs to be retrieved from the "htga4_api_data" option.
     * 
     * @return string|array value of the specified key from the 'htga4_api_data' option array. If the specified
     * key is not found or the 'htga4_api_data' option array is empty, an empty string will be
     * returned.
     */
    public function get_api_data( $data_name ){
        $api_data = get_option('htga4_api_data', array(
            'userinfo' => array(),
            'accounts' => array(),
            'properties' => array(),
            'reports' => array(),
            'data_stream' => array(),
            'data_streams' => array()
        ));

        return !empty($api_data[$data_name]) ? $api_data[$data_name] : '';
    }

    /**
     * This function updates the API data stored in the WordPress options table with the provided data
     * for a specific data name.
     * 
     * @param data_name a string representing the name of the data being updated in the API data array.
     * @param data  is the data that needs to be updated in the API. It could be an array, object,
     * or any other data type.
     * 
     * @return void
     */
    public function update_api_data( $data_name, $data ){
        $api_data = (array) get_option('htga4_api_data', array(
            'userinfo' => array(),
            'accounts' => array(),
            'properties' => array(),
            'reports' => array(),
            'data_stream' => array(),
            'data_streams' => array()
        ));

        if( empty($api_data[$data_name]) ){
            $api_data[$data_name] = $data;
        }

        update_option('htga4_api_data', $api_data);
    }

	public function get_auth_url() {
		$auth_url = 'https://accounts.google.com/o/oauth2/auth';
        $nonce = wp_create_nonce( 'htga4_save_key_nonce' );
        
        $state = admin_url( 'admin.php?page=ht-easy-ga4-setting-page&_wpnonce=' . $nonce );
        if( $this->is_ngrok_url() ){
            $state = $this->get_ngrok_url() . '/wp-admin/admin.php?page=ht-easy-ga4-setting-page&_wpnonce=' . $nonce;
        }

		$auth_url = add_query_arg( 'client_id', $this->get_config('client_id'), $auth_url );
		$auth_url = add_query_arg( 'redirect_uri', $this->get_config('redirect_uris'), $auth_url );
		$auth_url = add_query_arg( 'state', urlencode($state), $auth_url );
		$auth_url = add_query_arg( 'scope', 'https://www.googleapis.com/auth/userinfo.email+https://www.googleapis.com/auth/analytics.readonly+https://www.googleapis.com/auth/userinfo.profile', $auth_url );
		$auth_url = add_query_arg( 'access_type', 'offline', $auth_url );
		$auth_url = add_query_arg( 'prompt', 'consent', $auth_url );
		$auth_url = add_query_arg( 'response_type', 'code', $auth_url );

		return $auth_url;
	}

    /**
     * The function takes a parameter and returns an array of date ranges based on the parameter value
     * or the value of a custom date range passed through the GET request.
     * 
     * @param string param
     * 
     * @return array with two sub-arrays: 'current' and 'previous'. Each sub-array contains two
     * key-value pairs: 'start_date' and 'end_date'.
     */
    public function get_date_range( $param ) {
        // Today's date
        $current_end_date   = date('Y-m-d'); // phpcs:ignore
        $get_data       = wp_unslash($_GET);

        if( !empty($get_data['date_range']) && strpos($get_data['date_range'], ',') ){
            $param = 'custom';
        }
        
        switch ( $param ) {
            case 'last_7_days':
                $current_start_date = date('Y-m-d', strtotime('-7 days', strtotime($current_end_date))); // phpcs:ignore
                $current_end_date = 'yesterday';

                $previous_start_date = date('Y-m-d', strtotime('-14 days', strtotime($current_end_date))); // phpcs:ignore
                $previous_end_date = date('Y-m-d', strtotime('-8 days', strtotime($current_end_date))); // phpcs:ignore
                break;

            case 'last_15_days':
                $current_start_date = date('Y-m-d', strtotime('-15 days', strtotime($current_end_date))); // phpcs:ignore
                $current_end_date = 'yesterday';

                $previous_start_date = date('Y-m-d', strtotime('-30 days', strtotime($current_end_date))); // phpcs:ignore
                $previous_end_date = date('Y-m-d', strtotime('-16 days', strtotime($current_end_date))); // phpcs:ignore
                break;

            case 'custom':
                $date_range_arr     = explode(',', $get_data['date_range']);
                $current_start_date = sanitize_text_field($date_range_arr[0]);
                $current_end_date = sanitize_text_field($date_range_arr[1]);

                $d1 = new \DateTime($current_start_date);
                $d2 = new \DateTime($current_end_date);
                $interval = $d1->diff($d2);
                $count = $interval->days + 1;

                $previous_start_date = date('Y-m-d', strtotime("-$count days", strtotime($current_start_date))); // phpcs:ignore
                $previous_end_date = date('Y-m-d', strtotime("-$count days", strtotime($current_end_date))); // phpcs:ignore
                break;
            default:
                // last_30_days
                $current_start_date = date('Y-m-d', strtotime('-30 days', strtotime($current_end_date))); // phpcs:ignore
                $current_end_date = 'yesterday';

                $previous_start_date = date('Y-m-d', strtotime('-60 days', strtotime($current_end_date))); // phpcs:ignore
                $previous_end_date = date('Y-m-d', strtotime('-31 days', strtotime($current_end_date))); // phpcs:ignore
                break;
        }
        
        return array(
            'current' => array(
                'start_date' => $current_start_date,
                'end_date' => $current_end_date
            ),
            'previous' => array(
                'start_date' => $previous_start_date,
                'end_date' => $previous_end_date
            )
        );
    }

    /**
     * Checks if a given date range matches the current date range selected by the
     * user and returns a CSS class name accordingly.
     * 
     * @param date_range
     * 
     * @return string value of 'htga4-current' if the condition is met, otherwise it returns an empty
     * string.
     */
    public function get_current_class( $date_range ){
        $get_date_range = isset($_GET['date_range']) ? sanitize_text_field($_GET['date_range']) : 'last_30_days';
        if( strpos($get_date_range, ',') && $date_range == 'custom' ){
            return 'htga4-current';
        }
        
        return $get_date_range === $date_range ? 'htga4-current' : '';
    }

    /**
     * Returns the current admin URL.
     * 
     * @return string the current admin URL with certain query arguments removed.
     */
    public function get_current_admin_url() {
        $uri = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
        $uri = preg_replace( '|^.*/wp-admin/|i', '', $uri );
    
        if ( ! $uri ) {
            return '';
        }
    
        return remove_query_arg( array( '_wpnonce', '_wc_notice_nonce', 'wc_db_update', 'wc_db_update_nonce', 'wc-hide-notice' ), admin_url( $uri ) );
    }

    public function calculate_bounce_rate( $dataset = [] ){
        if( empty($dataset) ){
            return 0;
        }

        $total_sessions = count($dataset);
        $sum_bounceRates = array_sum($dataset);
        $average_bounceRate = $sum_bounceRates / $total_sessions;
        $bounce_rate_percentage = $average_bounceRate * 100;

        return round($bounce_rate_percentage, 1);;
    }

	public function render_growth( $previous_total = 0, $current_total = 0, $context = '' ){
		$growth = 0;
		$previous_total = $previous_total;
		$current_total 	= $current_total;

		if ( $previous_total > 0 ) {
			$growth = ( ( $current_total - $previous_total ) / $previous_total ) * 100;
		}

		$growth = round( $growth );

		$head_class = '';
		$icon_class = '';

		if ( $growth > 0 ) {
			$head_class = 'ht_easy_ga4_report_card_head_difference_positive';
			$icon_class = 'dashicons-arrow-up-alt';
		} elseif ( $growth < 0 ) {
			$head_class = 'ht_easy_ga4_report_card_head_difference_negative';
			$icon_class = 'dashicons-arrow-down-alt';
		}
		?>
		<h3 class="ht_easy_ga4_report_card_head_count">
            <?php if($this->get_current_tab() === 'ecommerce_reports'){

                if( $context === 'average_purchase_revenue' ||  $context === 'purchase_revenue'){
                    echo wp_kses_post(get_woocommerce_currency_symbol()) . esc_html( round($current_total) );
                } else {
                    echo esc_html( round($current_total, 1) );
                }
                
            } else {
                echo esc_html( round($current_total, 1) ) ?><?php echo fmod($current_total, 1) > 0 ? '%' : '';
            }
            ?>
        </h3>
		<div class="ht_easy_ga4_report_card_head_difference <?php echo esc_attr($head_class) ?>">
			<i class="dashicons <?php echo esc_attr($icon_class) ?>"></i>
			<p><span class="ht_growth_count"><?php echo esc_html($growth) ?>%</span> <span><?php echo esc_html__('vs. previous period', 'ht-easy-ga4') ?></span></p>
		</div>
		<?php
	}

    /**
     * This function retrieves a specific option value from the 'ht_easy_ga4_options' array or returns
     * a default value if the option is not set.
     * 
     * @param option_name The name of the option to retrieve from the options array.
     * @param default The default value to return if the option is not set or does not exist.
     * 
     * @return string|array
     */
    public function get_option( $option_name = '', $default = null ) {
        $options = get_option( 'ht_easy_ga4_options' );
        // Look into the updated options first
        if( isset( $options[$option_name] ) ){

            return $options[$option_name];

        } elseif( get_option('ht_easy_ga4_id') ) {

            // If not found in the updated options, look into the old options
            return get_option('ht_easy_ga4_id');
            
        } else {
            return $default;
        }

        return $default;
    }

    /**
     * @param  [string] $section
     * @param  [string] $option_key
     * @param  string $new_value
     * 
     * @return [string]
     */
    function update_option( $section, $option_key, $new_value ){
        $options_data = get_option( $section );

        if( isset( $options_data[$option_key] ) ){
            $options_data[$option_key] = $new_value;
        }else{
            $options_data = array( $option_key => $new_value );
        }

        update_option( $section, $options_data );
    }

    public function get_config_file(){
		// if wp environment is test and debug is true then use test config file
		if( defined('WP_ENVIRONMENT_TYPE') && WP_ENVIRONMENT_TYPE === 'development' ){
			return HT_EASY_GA4_PATH .'/includes/config-test.json';
		} else {
			return HT_EASY_GA4_PATH .'/includes/config.json';
		}
	}

    /**
    * @param name The name of the configuration value to retrieve from the config.json.
    * 
    * @return string|array
    */
    public function get_config( $name = '' ){
        $file         =  $this->get_config_file();

        $return_value = '';
        $config_arr   = array();

        if( is_readable($file) ){
            $file_content = file_get_contents( $file ); // phpcs:ignore
            $config_arr   = json_decode( $file_content, true );
        }

        if( !empty($name) ){
            $return_value = isset($config_arr['web'][$name]) ? $config_arr['web'][$name] : '';

            if( $name === 'redirect_uris' && is_array($return_value) ){
                $return_value = current($return_value);
            }

            if( $name === 'javascript_origins' && is_array($return_value) ){
                $return_value = current($return_value);
            }
        } else {
            $return_value = $config_arr;
        }

        return $return_value;
    }

    /**
    * Returns user roles with key => value pair.
    * 
    * @return array
    */
	public function get_roles_dropdown_options(){
		global $wp_roles;
		$options = array();

		if ( ! empty( $wp_roles ) ) {
		  if ( ! empty( $wp_roles->roles ) ) {
			foreach ( $wp_roles->roles as $role_key => $role_value ) {
			  $options[$role_key] = $role_value['name'];
			}
		  }
		}

		return $options;
	}
    
    public function str_contains($haystack, $needle) {
        return $needle !== '' && mb_strpos($haystack, $needle) !== false;
    }

    public function is_ngrok_url(){
        $forwarded_host = !empty($_SERVER['HTTP_X_FORWARDED_HOST']) ? $_SERVER['HTTP_X_FORWARDED_HOST'] : ''; // development mode

        if( $forwarded_host == 'dominant-fleet-swan.ngrok-free.app' ){
            return true;
        }

        return false;
    }

    public function get_ngrok_url(){
        return 'https://dominant-fleet-swan.ngrok-free.app';
    }

    /**
     * Make unique transient suffix using account, property, and data stream ID
     * 
     * @return string
     */
    public function get_unique_transient_suffix(){
        return $this->get_option('account') . '_' . $this->get_option('property') . '_' . $this->get_option('data_stream_id');
    }
}