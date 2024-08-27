<?php

class Boomdevs_Toc_Utils {
    public static function isProActivated() {
        if (class_exists('SureCart\Licensing\Client')) {
			$activation_key = get_option('toptableofcontentspro_license_options');

            if( $activation_key && count($activation_key) > 0 && isset($activation_key['sc_license_key']) && $activation_key['sc_license_key'] !== '') {
                return true;
            }
		} else {
            global $boomdevs_toc_pro_license;

            if ($boomdevs_toc_pro_license) {
                return $boomdevs_toc_pro_license->is_valid();
            }
        }

        return false;
    }

}