<?php

/**
 * @author Dario Curvino <@dudo>
 * @since  3.3.1
 */
class YasrStats
{
    /**
     * @author Dario Curvino <@dudo>
     * @since  refactor in class sice 3.3.1
     *
     * @param $active_tab
     *
     * @return void
     */
    public static function printTabs( $active_tab )
    {
        ?>
        <h2 class="nav-tab-wrapper yasr-no-underline">

            <a href="?page=yasr_stats_page&tab=logs" class="nav-tab
                <?php 
        echo  ( $active_tab === 'logs' ? 'nav-tab-active' : '' ) ;
        ?>"
            >
                <?php 
        esc_html_e( 'Visitor Votes', 'yet-another-stars-rating' );
        ?>
            </a>

            <a href="?page=yasr_stats_page&tab=logs_multi" class="nav-tab
                <?php 
        echo  ( $active_tab === 'logs_multi' ? 'nav-tab-active' : '' ) ;
        ?>"
            >
                <?php 
        esc_html_e( 'MultiSet', 'yet-another-stars-rating' );
        ?>
            </a>

            <a href="?page=yasr_stats_page&tab=overall" class="nav-tab
                <?php 
        echo  ( $active_tab === 'overall' ? 'nav-tab-active' : '' ) ;
        ?>"
            >
                <?php 
        esc_html_e( 'Overall Rating', 'yet-another-stars-rating' );
        ?>
            </a>

            <a href="?page=yasr_stats_page&tab=yasr_csv_export" id="yasr_csv_export" class="nav-tab
                <?php 
        echo  ( $active_tab === 'yasr_csv_export' ? 'nav-tab-active' : '' ) ;
        ?>"
            >
                <?php 
        esc_html_e( 'Export data', 'yet-another-stars-rating' );
        ?>
            </a>
            <?php 
        /**
         * Use this hook to add a tab into yasr_stats_page
         */
        do_action( 'yasr_add_stats_tab', $active_tab );
        ?>

            <a href="?page=yasr_settings_page-pricing" class="nav-tab">
                <?php 
        esc_html_e( 'Upgrade', 'yet-another-stars-rating' );
        ?>
            </a>

        </h2>

        <?php 
    }
    
    /**
     * Print tabs content of yasr stats page
     *
     * @author Dario Curvino <@dudo>
     * @since  3.3.1
     *
     * @param $active_tab
     *
     * @return void
     */
    public static function printTabsContent( $active_tab )
    {
        
        if ( $active_tab === 'logs' || $active_tab === '' ) {
            ?>
            <form action="#" id="" method="POST">
                <?php 
            wp_nonce_field( 'yasr-delete-stats-logs', 'yasr-nonce-delete-stats-logs' );
            $yasr_stats_log_table = new YasrStatsListTable( $active_tab );
            $yasr_stats_log_table->prepare_items();
            $yasr_stats_log_table->display();
            ?>
            </form>

            <?php 
        }
        
        //End if tab 'logs'
        
        if ( $active_tab === 'logs_multi' ) {
            ?>
            <form action="#" id="" method="POST">
                <?php 
            wp_nonce_field( 'yasr-delete-stats-logs', 'yasr-nonce-delete-stats-logs' );
            $yasr_stats_log_table = new YasrStatsListTable( $active_tab );
            $yasr_stats_log_table->prepare_items();
            $yasr_stats_log_table->display();
            ?>
            </form>
            <?php 
        }
        
        //End if tab 'general_settings'
        
        if ( $active_tab === 'overall' ) {
            ?>
            <form action="#" id="" method="POST">
                <?php 
            wp_nonce_field( 'yasr-delete-stats-logs', 'yasr-nonce-delete-stats-logs' );
            $yasr_stats_log_table = new YasrStatsListTable( $active_tab );
            $yasr_stats_log_table->prepare_items();
            $yasr_stats_log_table->display();
            ?>
            </form>
            <?php 
        }
        
        //End if tab 'overall'
    }

}