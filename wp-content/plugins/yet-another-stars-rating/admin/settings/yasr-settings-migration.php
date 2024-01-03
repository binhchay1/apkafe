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

if(!defined('YASR_RATING_PLUGIN_FOUND')) {
    return;
}

if(YASR_RATING_PLUGIN_FOUND === false) {
    return;
}

$import_plugin = new YasrImportRatingPlugins;

?>

<h3><?php esc_html_e('Migration Tools', 'yet-another-stars-rating'); ?></h3>

<table class="form-table yasr-settings-table" id="yasr-migrate-table">
    <tr>
        <td>
            <div>
                <?php
                    $rating_plugin_found = json_decode(YASR_RATING_PLUGIN_FOUND, true);
                    if(is_array($rating_plugin_found)) {
                        if(in_array('wppr', $rating_plugin_found)) {
                            $import_plugin->importWPPR();
                        }

                        if(in_array('kksr', $rating_plugin_found)) {
                            $import_plugin->importKKSR();
                        }

                        if(in_array('rmp', $rating_plugin_found)) {
                            $import_plugin->importrmp();
                        }

                        if(in_array('mr', $rating_plugin_found)) {
                            $import_plugin->importMR();
                        }
                    }

                    do_action('yasr_migration_page_bottom', $import_plugin->plugin_imported);
                ?>
            </div>
        </td>
    </tr>
</table>