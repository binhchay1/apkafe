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

$custom_post_types = YasrCustomPostTypes::getCustomPostTypes();
$cr_setting = new YasrSettingsRankings($custom_post_types);


?>

<h3> <?php esc_html_e('Rankings Options', 'yasr-pro');?> </h3>

<table class="form-table yasr-settings-table" id="yasr-pro-charts-options">
    <!--Builder Container-->
    <tr>
        <td>
            <div id="yasr-builder-shortcode-container" class="yasr-builder-shortcode-container">

                <div id="yasr-builder-shortcode"
                      class="yasr-rankings-div-shortcode">
                </div>

                <div id="yasr-builder-shortcode-buttons-container">
                    <button class='button-primary' id="yasr-builder-button-preview">
                        <?php esc_html_e('Ranking preview', 'yet-another-stars-rating'); ?>
                    </button>
                    &nbsp;&nbsp;&nbsp;
                    <button class="button-secondary yasr-copy-shortcode" id="yasr-builder-copy-shortcode">
                        <span class="dashicons dashicons-admin-page" style="vertical-align: middle"></span>
                        <?php esc_html_e('Copy Shortcode', 'yet-another-stars-rating') ?>
                    </button>
                </div>

                <div id="yasr-builder-preview"></div>

            </div>

            <!-- First Row -->
            <div id="yasr-builder-params-container" class="yasr-settings-row-33">
                <?php
                //Data Source
                $cr_setting->selectRanking();
                //settings for select set id
                $cr_setting->multiset();
                //rows
                $cr_setting->rows();
                //default view or VV
                $cr_setting->vvDefaultView();
                //required votes
                $cr_setting->vvRequiredVotes();
                //star size
                $cr_setting->size();
                //custom text for overall rating
                $cr_setting->ovCustomText();
                //Date
                $cr_setting->setDate();
                //settings for display name
                $cr_setting->usernameDisplay();
                ?>
            </div>

            <hr style="border: 1px dotted #ddd; margin-top: 20px;"/>

            <!--Second Row -->
            <div class="yasr-builder-ranking-container" style="margin-top: 20px">
                <?php
                $cr_setting->categories();

                if ($custom_post_types) {
                    $cr_setting->cpt();
                }
                ?>
            </div>
        </td>

    </tr>

</table>
