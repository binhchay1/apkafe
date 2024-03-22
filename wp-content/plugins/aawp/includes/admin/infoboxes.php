<?php
/**
 * Infoboxes
 *
 * @package     AAWP\Settings
 * @since       3.2.0
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/*
 * Infobox styles
 */
function aawp_settings_display_infoboxes() {

    ?>
    <style>
        .aawp-infobox-list-social { list-style: none; margin-left: 0; overflow: auto; margin-bottom: 0; }
        .aawp-infobox-list-social li { box-sizing: border-box; display: inline-block; padding: 5px; float: left; border-radius: 100%; margin-bottom: 0; }
        .aawp-infobox-list-social li + li { margin-left: 10px; }
        .aawp-infobox-list-social li.facebook { background-color: #3b5999; }
        .aawp-infobox-list-social li.twitter { background-color: #55acee; }
        .aawp-infobox-list-social li a { display: block; color: #fff; text-decoration: none; }
        .aawp-infobox-list-social li a:visited { color: #fff; }
        .aawp-infobox-list-social li a:hover { color: #fff; text-decoration: none; }
        .aawp-infobox-list-social li a:active { color: #fff; text-decoration: none; }
        .aawp-infobox-list-social li a:focus { color: #fff; text-decoration: none; }
        .aawp-infobox-list-dotted { list-style: outside; margin-left: 15px; }
        .aawp-infobox-responsive-img { display: block; max-width: 100%; height: auto; margin: 0 auto; }

        #mc_embed_signup div.mce_inline_error { background: darkred; font-weight: normal; }
        #mce-success-response { padding: 10px; background: lightgreen; color: #333; }
    </style>
    <?php
}
add_action( 'aawp_settings_infoboxes', 'aawp_settings_display_infoboxes', 10 );
add_action( 'aawp_support_infoboxes', 'aawp_settings_display_infoboxes', 10 );

/*
 * Support
 */
function aawp_settings_infobox_support() {

    $title = __( 'Resources & Support', 'aawp' );
    $header = aawp_get_settings_infobox_support_header();
    $links = aawp_get_settings_infobox_support_links();

    ob_start();

    ?>

    <?php if ( isset( $header['url'] ) && isset( $header['img'] ) ) { ?>
        <a href="<?php echo esc_url( $header['url'] ); ?>" target="_blank" rel="nofollow">
            <img src="<?php echo $header['img']; ?>" class="aawp-infobox-responsive-img">
        </a>
    <?php } ?>

    <p><?php _e( 'In order to make it as simple as possible for you, we created a detailed online documentation.', 'aawp' ); ?></p>

    <?php if ( is_array( $links ) && sizeof( $links ) > 0 ) { ?>
        <ul class="aawp-infobox-list-dotted">
            <?php foreach ( $links as $url => $label ) { ?>
                <li><a href="<?php echo esc_url( $url ); ?>" target="_blank" rel="nofollow"><?php echo esc_html( $label ); ?></a></li>
            <?php } ?>
        </ul>
    <?php } ?>

    <ul class="aawp-infobox-list-social">
        <li class="facebook"><a href="https://www.facebook.com/amazon.affiliate.wordpress/" rel="nofollow" target="_blank"><span class="dashicons dashicons-facebook-alt"></span></a></li>
        <li class="twitter"><a href="https://twitter.com/AmazonPlugin" rel="nofollow" target="_blank"><span class="dashicons dashicons-twitter"></span></a></li>
        <li><strong><?php _e( 'Connect with us!', 'aawp' ); ?></strong></li>
    </ul>

    <?php

    $body = ob_get_clean();

    aawp_settings_infobox_render( $title, $body );
}
add_action( 'aawp_settings_infoboxes', 'aawp_settings_infobox_support', 20 );
add_action( 'aawp_support_infoboxes', 'aawp_settings_infobox_support', 20 );

function aawp_get_settings_infobox_support_header() {

    $header = array(
        'img' => aawp_get_assets_url() . 'img/banner-service.png',
        'url' => ( aawp_is_lang_de() ) ? 'https://aawp.de/' : 'https://getaawp.com/'
    );

    return $header;
}

function aawp_get_settings_infobox_support_links() {

    $links = array();

    if ( aawp_is_lang_de() ) {
        $links['https://aawp.de/'] = __( 'Plugin Website', 'aawp' );
        $links['https://aawp.de/benutzerkonto/'] = __( 'Manage Account', 'aawp' );
        $links['https://aawp.de/docs/'] = __( 'Documentation', 'aawp' );
        $links['https://aawp.de/changelog/'] = __( 'Changelog', 'aawp' );
        $links['https://aawp.de/kontakt/'] = __( 'Support', 'aawp' );
    } else {
        $links['https://getaawp.com/'] = __( 'Plugin Website', 'aawp' );
        $links['https://getaawp.com/dashboard/'] = __( 'Manage Account', 'aawp' );
        $links['https://getaawp.com/docs/'] = __( 'Documentation', 'aawp' );
        $links['https://getaawp.com/changelog/'] = __( 'Changelog', 'aawp' );
        $links['https://getaawp.com/help/'] = __( 'Support', 'aawp' );
    }

    return $links;
}

function aawp_settings_infobox_newsletter() {

    $title = __( "Upcoming features and discounts", 'aawp' );

    $list_id = ( aawp_is_lang_de() ) ? 'affcba954b' : 'a53860b7ad';

    ob_start();
    ?>

    <?php _e( "Don't miss any update and news about the plugin and sign up for our newsletter.", 'aawp'); ?>

    <!-- Begin MailChimp Signup Form -->
    <div id="mc_embed_signup">
        <form action="//aawp.us10.list-manage.com/subscribe/post?u=cc9fc194eb9ba7a4d8616c2cb&amp;id=<?php echo $list_id; ?>" method="post" id="mc-embedded-subscribe-form" name="mc-embedded-subscribe-form" class="validate" target="_blank" novalidate>
            <div id="mc_embed_signup_scroll">

                <div class="mc-field-group">
                    <p>
                        <label for="mce-EMAIL"><?php _e('Email address', 'aawp'); ?></label>
                        <input type="email" value="" name="EMAIL" class="required email text-regular" id="mce-EMAIL" style="width: 100%;">
                    </p>
                </div>
                <div id="mce-responses" class="clear">
                    <div class="response" id="mce-error-response" style="display:none"></div>
                    <div class="response" id="mce-success-response" style="display:none;"></div>
                </div>    <!-- real people should not fill this in and expect good things - do not remove this or risk form bot signups-->
                <div style="position: absolute; left: -5000px;" aria-hidden="true"><input type="text" name="b_cc9fc194eb9ba7a4d8616c2cb_<?php echo $list_id; ?>" tabindex="-1" value=""></div>
                <div class="clear">
                    <p>
                        <input type="submit" value="<?php _e('Subscribe', 'aawp'); ?>" name="subscribe" id="mc-embedded-subscribe" class="button button-primary" />
                    </p>
                </div>
            </div>
        </form>
    </div>
    <script type='text/javascript' src='//s3.amazonaws.com/downloads.mailchimp.com/js/mc-validate.js'></script><script type='text/javascript'>(function($) {window.fnames = new Array(); window.ftypes = new Array();fnames[0]='EMAIL';ftypes[0]='email';fnames[1]='FNAME';ftypes[1]='text';fnames[2]='LNAME';ftypes[2]='text';
            $.extend($.validator.messages, {
                required: "<?php _e('This field is required.', 'aawp'); ?>", // Dieses Feld ist ein Pflichtfeld.
                email: "<?php _e('Please enter a valid email address.', 'aawp'); ?>" // Geben Sie bitte eine gültige E-Mail Adresse ein.
            });}(jQuery));var $mcj = jQuery.noConflict(true);</script>
    <!--End mc_embed_signup-->

    <?php
    $body = ob_get_clean();

    aawp_settings_infobox_render( $title, $body );
}
//add_action( 'aawp_settings_infoboxes', 'aawp_settings_infobox_newsletter', 30 );
//add_action( 'aawp_support_infoboxes', 'aawp_settings_infobox_newsletter', 30 );

function aawp_settings_infobox_translations() {

    if ( aawp_is_lang_de() )
        return;

    $title = __( "Help us translate the plugin", "aawp" );

    ob_start();
    ?>
    <p>
        <?php _e( "If you are native speaker of a country which translations are not available yet for the plugin, we appreciate your help to <strong>translate the plugin into your language</strong> as well!", "aawp" ); ?>
    </p>
    <p>
        <?php _e( "Translations can be added simply by visiting our translation page.", 'aawp'); ?>
    </p>
    <p>
        <a href="https://translate.aawp.de" class="button" target="_blank" rel="nofollow"><?php _e('Click here to submit translations', 'aawp'); ?></a>
    </p>
    <?php
    $body = ob_get_clean();

    aawp_settings_infobox_render( $title, $body );
}
//add_action( 'aawp_settings_infoboxes', 'aawp_settings_infobox_translations', 40 );
//add_action( 'aawp_support_infoboxes', 'aawp_settings_infobox_translations', 40 );

/*
 * Render HTML
 */
function aawp_settings_infobox_render( $title, $body ) {
    echo '<div class="postbox">';
    echo '<h3><span>' . esc_html( $title ) . '</span></h3>';
    echo '<div class="inside">';
    echo $body;
    echo '</div>';
    echo '</div>';
}