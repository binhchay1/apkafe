<?php

/*

Copyright 2014 Dario Curvino (email : d.curvino@gmail.com)

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>
*/

if (!defined('ABSPATH')) {
    exit('You\'re not allowed to see this page');
} // Exit if accessed directly

if (!current_user_can('manage_options')) {
    /** @noinspection ForgottenDebugOutputInspection */
    wp_die(__('You do not have sufficient permissions to access this page.', 'yet-another-stars-rating'));
}

?>

<div class="wrap">
    <h2>Yet Another Stars Rating: <?php esc_html_e('Settings', 'yet-another-stars-rating'); ?></h2>
    <?php
        settings_errors();

        if (isset($_GET['tab'])) {
            $active_tab = $_GET['tab'];
        } else {
            $active_tab = 'general_settings';
        }

        //Do the settings tab
        YasrSettings::printTabs($active_tab);
    ?>
    <div class="yasr-settingsdiv">
        <div class="yasr-settings-table">
            <?php
                YasrSettings::printTabsContent($active_tab);
            ?>

        </div> <!--End yasr-settings-table-->
    </div><!--End yasr-settingsdiv-->

    <div class="yasr-clear-both-dynamic"></div>
    <?php
        YasrSettingsRightColumn::init();
    ?>
</div><!--End div wrap-->