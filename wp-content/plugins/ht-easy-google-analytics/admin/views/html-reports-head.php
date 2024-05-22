<?php
    $current_admin_url = htga4()->get_current_admin_url();
    $last_7_days_url  = add_query_arg( 'date_range', 'last_7_days', $current_admin_url );
    $last_15_days_url = add_query_arg( 'date_range', 'last_15_days', $current_admin_url );
    $last_30_days_url = add_query_arg( 'date_range', 'last_30_days', $current_admin_url );

    $measurement_id = htga4()->get_option( 'measurement_id' );

    $userinfo_data = htga4()->get_userinfo_data_prepared();
    $data_stream_data = htga4()->get_datastream_data_prepared();
?>

<div class="ht_easy_ga4_reports_head">
    <div class="ht_easy_ga4_reports_user_card">
        <?php
        if( empty($userinfo_data['error']) && empty($data_stream_data['error']) ){
            ?>
                <div class="ht_easy_ga4_reports_user_thumb">
                    <img src="<?php echo esc_url( $userinfo_data['picture'] ); ?>" alt="">
                </div>
                <div class="ht_easy_ga4_reports_user_info">
                    <h3><?php echo esc_html( $userinfo_data['name'] ); ?></h3>
                    <p><?php echo esc_html( $userinfo_data['email'] ); ?></p>
                    <p><?php echo esc_html( $data_stream_data['displayName'] ); ?> &#60;<?php echo esc_html( $this->get_option( 'measurement_id' ) ); ?>&#62;</p>
                </div>
                <?php
        } else {
            // @todo show error notice
        }
        ?>
    </div>

    <div class="ht_easy_ga4_reports_toolbar">

        <div class="ht_easy_ga4_reports_filter">
            <a href="<?php echo esc_url( $last_7_days_url ); ?>" class="ht_easy_ga4_reports_filter_button <?php echo esc_attr( htga4()->get_current_class( 'last_7_days' ) ); ?>"><?php echo esc_html__( 'Last 7 days', 'ht-easy-ga4' ); ?></a>
            <a href="<?php echo esc_url( $last_15_days_url ); ?>" class="ht_easy_ga4_reports_filter_button <?php echo esc_attr( htga4()->get_current_class( 'last_15_days' ) ); ?>"><?php echo esc_html__( 'Last 15 days', 'ht-easy-ga4' ); ?></a>
            <a href="<?php echo esc_url( $last_30_days_url ); ?>" class="ht_easy_ga4_reports_filter_button <?php echo esc_attr( htga4()->get_current_class( 'last_30_days' ) ); ?>"><?php echo esc_html__( 'Last 30 days', 'ht-easy-ga4' ); ?></a>
            <button type="button" class="ht_easy_ga4_reports_filter_button ht_easy_ga4_reports_filter_custom_range <?php echo esc_attr( htga4()->get_current_class( 'custom' ) ); ?>"><?php echo esc_html__( 'Custom', 'ht-easy-ga4' ); ?></span>
        </div>

        <label for="ht_easy_ga4_reports_compare_field" class="ht_easy_ga4_reports_compare">
            <span class="ht_easy_ga4_reports_compare_icon">
                <input type="checkbox" id="ht_easy_ga4_reports_compare_field">
                <span class="ht_easy_ga4_reports_compare_field_icon"></span>
            </span>
            <span class="ht_easy_ga4_reports_compare_label"><?php echo esc_html__( 'Compare to previous period', 'ht-easy-ga4' ); ?></span>
        </label>

    </div>
</div><!-- .ht_easy_ga4_reports_head -->