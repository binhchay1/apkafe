<?php
class Boomdevs_Toc_Utils {
    public static function isProActivated() {
        
        global $boomdevs_toc_pro_license;

        if ($boomdevs_toc_pro_license) {
            return $boomdevs_toc_pro_license->is_valid();
        }

        return false;
    }
}