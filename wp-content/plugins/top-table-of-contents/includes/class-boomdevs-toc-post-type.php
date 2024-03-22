<?php

require_once BOOMDEVS_TOC_PATH . 'includes/class-boomdevs-toc-settings.php';
require_once BOOMDEVS_TOC_PATH . 'includes/class-boomdevs-toc-shortcode.php';
/**
 * The Pages plugin class.
 *
 * @since      1.0.0
 * @package    Boomdevs_Toc
 * @subpackage Boomdevs_Toc_Pages
 * @author     BoomDevs <admin@boomdevs.com>
 */
class Boomdevs_Toc_Post_Type {

    public function boomdevs_toc_post_types( $content ) {

        $settings = Boomdevs_Toc_Settings::get_settings();

        $shortcode_content = '';

        if ( ! empty( $settings['select_post_type'] ) && !is_front_page() ) {
            $toc_shortcode = new Boomdevs_Toc_Shortcode();
            foreach ( $settings['select_post_type'] as $value ) {
                if ( get_post_type() === $value ) {
                    if ( $value === 'post' || $value === 'page' ) {
                        $shortcode_content = is_singular( 'post' ) || is_singular( 'page' ) ? $toc_shortcode->shortcode_generator( $content ) : '';
                        
                        switch($settings['select_toc_position']) {
                            case 'before':
                                preg_match_all('/(\<h[1-6](.*?))\>(.*)(<\/h[1-6]>)/i', $content, $matches);
                                if (isset($matches[0]) && count($matches[0]) > 0) {
                                    $toc_with_heading = $shortcode_content . $matches[0][0];
                                    return str_replace($matches[0][0], $toc_with_heading, $content);
                                }
                                break;

                            case 'after':
                                preg_match_all('/(\<h[1-6](.*?))\>(.*)(<\/h[1-6]>)/i', $content, $matches);
                                if (isset($matches[0]) && count($matches[0]) > 0) {
                                    $toc_with_heading = $matches[0][0] . $shortcode_content;
                                    return str_replace($matches[0][0], $toc_with_heading, $content);
                                }
                                break;

                            case 'afterpara':
                                preg_match_all('%(<p[^>]*>.*?</p>)%i', $content, $matches);
                                if (isset($matches[1]) && count($matches[1]) > 0) {
                                    $first_para = $matches[1][0];
                                    $toc_with_heading = $first_para . $shortcode_content;
                                    return str_replace($first_para, $toc_with_heading, $content);
                                }
                                break;

                            case 'top':
                                preg_match_all('/(\<h[1-6](.*?))\>(.*)(<\/h[1-6]>)/i', $content, $matches);
                                return $shortcode_content . $content;
                                break;

                            case 'bottom':
                                return $content . $shortcode_content;
                                break;
                            default:
                                echo $shortcode_content;
                                break;
                        }
                        
                    } else {
                        if(is_singular( $value ) || is_singular( 'page' )) {
                            echo $toc_shortcode->shortcode_generator( $content );
                        }
                    }
                }
            }
        }

        return $content;
    }
}