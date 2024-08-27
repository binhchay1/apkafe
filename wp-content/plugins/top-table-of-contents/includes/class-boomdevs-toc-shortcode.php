<?php

require_once BOOMDEVS_TOC_PATH . 'includes/class-boomdevs-toc-settings.php';
require_once BOOMDEVS_TOC_PATH . 'includes/class-boomdevs-toc-utils.php';
require_once BOOMDEVS_TOC_PATH . 'includes/class-boomdevs-toc-metabox.php';

/**
 * The Shortcode plugin class.
 *
 * @since      1.0.0
 * @package    Boomdevs_Toc
 * @subpackage Boomdevs_Toc_shortcode/includes
 * @author     BoomDevs <admin@boomdevs.com>
 */

use Cocur\Slugify\SlugifyInterface;
use Masterminds\HTML5;
use TOC\UniqueSlugify;

class Boomdevs_Toc_Shortcode
{
    /**
     * @var SlugifyInterface
     */
    private $sluggifier;
    private $used;
    private $total_headings;

    /**
     * Constructor
     *
     * @param HTML5|null $htmlParser
     * @param SlugifyInterface|null $slugify
     */
    public function __construct()
    {
        $this->used       = array();
        $this->sluggifier = new UniqueSlugify();
    }

    public function shortcode_register()
    {
        add_shortcode('boomdevs_toc', [$this, 'shortcode_generator']);
    }

    /**
     * Shortcode Rander Function
     */
    public function shortcode_generator($atts)
    {

        $a = shortcode_atts(array(
            'content'  => '',
            'post_id'  => '',
        ), $atts);

        if (!empty($a['content'])) {
            $content = $a['content'];
        } else if (!empty($a['post_id'])) {
            $content = get_the_content($a['post_id']);
        } else {
            $content = get_the_content(get_the_ID());
        }

        $settings                        = Boomdevs_Toc_Settings::get_settings();
        $title                           = $settings['title'];
        $title_show_hide                 = $settings['title_show_hide_switcher'];
        $icon_show_hide                  = $settings['icon_show_hide_switcher'];
        $top_level                       = $settings['heading_top_level'] ? $settings['heading_top_level'] : 1;
        $markupFixer                     = new TOC\MarkupFixer();
        $tocGenerator                    = new TOC\TocGenerator();
        $content                         = $markupFixer->fix($content, $settings['title_hide'], $top_level, $settings['title_depth']);
        $show_heading_toggle_icon        = '';
        $show_sub_heading_toggle_icon    = '';
        $fiexd_layout_width              = '';
        $layout_padding                  = $settings['layout_padding']['top']  + $settings['layout_padding']['right'] + $settings['layout_padding']['bottom'] + $settings['layout_padding']['left'] . 'px';
        $header_title_padding = 0;
        if (is_numeric($settings['title_padding']['top']) && is_numeric($settings['title_padding']['right']) && is_numeric($settings['title_padding']['bottom']) && is_numeric($settings['title_padding']['left'])) {
            $header_title_padding = $settings['title_padding']['top'] + $settings['title_padding']['right'] + $settings['title_padding']['bottom'] + $settings['title_padding']['left'];
        }

        $header_title_padding .= 'px';

        if (Boomdevs_Toc_Utils::isProActivated()) {
            $show_heading_toggle_icon             = $settings['show_heading_toggle_icon'];
            $show_sub_heading_toggle_icon         = $settings['show_sub_heading_toggle_icon'];
            $fiexd_layout_width                   = $settings['fiexd_layout_width']['width'];
        }

        ob_start();

        $page_id = get_queried_object_id();
        $disable_auto_insert = get_post_meta($page_id, 'boomdevs_metabox', true);

        if (gettype($disable_auto_insert) === 'string') {
            $disable_auto_insert = unserialize($disable_auto_insert);
        }

        if ($disable_auto_insert['disable_auto_insert'] === '1') {
            return ob_get_clean();
        }

        $pattern = '#(?P<full_tag><(?P<tag_name>h\d)(?P<tag_extra>[^>]*)>(?P<tag_contents>[^<]*)</h\d>)#i';
        if (preg_match_all($pattern, $content, $matches, PREG_SET_ORDER)) {
            $this->total_headings = count($matches);
        }

?>
        <?php if ($this->total_headings >= intval($settings['number_of_headings'])) : ?>

            <?php if (Boomdevs_Toc_Utils::isProActivated()) { ?>
                <div class="bd_toc_progress_bar">
                    <div class="bd_toc_widget_progress_bar"></div>
                </div>
                <div>
                    <div class="bd_toc_widget_floating">
                        <div class="floating_toc_bg_overlay"></div>
                        <div class="bd_toc_widget_floating_current_heading">
                            <div class="bd_toc_widget_item">
                            <div class="bd_toc_widget_nav_overlay"></div>
                            </div>
                        </div>
                        <div class="bd_toc_floating_content list-type-<?php echo $settings['heading_list_type']; ?>">
                            <div class="bd_toc_content_list">
                                <?php
                                echo "<div class='bd_toc_content_floating_list_item'>" . $tocGenerator->getHtmlMenu($content, $settings['title_hide'],$top_level, $settings['title_depth']) . "</div>";
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php } ?>

            <div class="fit_content">
                <div class="bd_toc_container" data-fixedWidth="<?php echo $fiexd_layout_width; ?>">
                    <div class="bd_toc_wrapper" data-wrapperPadding="<?php echo $layout_padding; ?>">
                        <div class="bd_toc_wrapper_item">
                            <div class="bd_toc_header active" data-headerPadding="<?php echo $header_title_padding; ?>">
                                <div class="bd_toc_header_title">
                                    <?php
                                    if ($title_show_hide == true) {
                                        echo esc_html($title);
                                    }
                                    ?>
                                </div>
                                <div class="bd_toc_switcher_hide_show_icon">
                                    <?php
                                    if ($icon_show_hide == true) {
                                        echo '<span class="bd_toc_arrow"></span>';
                                    }
                                    ?>
                                </div>
                            </div>
                            <div class="bd_toc_content list-type-<?php echo $settings['heading_list_type']; ?>">
                                <div class="<?php echo $show_heading_toggle_icon && Boomdevs_Toc_Utils::isProActivated() || $show_sub_heading_toggle_icon && Boomdevs_Toc_Utils::isProActivated() ? 'heading_toggle_icon sub_heading_toggle_icon bd_toc_content_list' : 'bd_toc_content_list' ?> ">
                                    <?php
                                    echo "<div class='bd_toc_content_list_item'>" . $tocGenerator->getHtmlMenu($content, $settings['title_hide'], $top_level, $settings['title_depth'] ) . "</div>";
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="layout_toggle_button">
                        <span class="bd_toc_arrow"></span>
                    </div>
                </div>
            </div>

<?php
        endif;
        return ob_get_clean();
    }


    /**
     * Auto id Add in Heading
     */
    public function boomdevs_toc_auto_id_headings($content)
    {
        $post_id = get_the_ID(); // Get the current post ID
        $vc_post_flag = get_post_meta($post_id, 'vcv-be-editor', true);

        if($post_id && $vc_post_flag === 'fe') {
            global $wp_version;

            // @codingStandardsIgnoreLine
            if (version_compare($wp_version, '5.2', '>=')) {
                $content = get_the_content('', false, $post_id);
            } else {
                $post = get_post($post_id);
                setup_postdata($post);
                $content = get_the_content('', false);
                wp_reset_postdata();
            }

            if (strpos($content, '<!--vcv no format-->') === false) {
                // Call wpautop for non VCWB sourceId
                $content = wpautop($content);
            }
        }


        $page_id = get_queried_object_id();
        $auto_insert_arr = 'a:1:{s:19:"disable_auto_insert";s:1:"0";}';
        add_post_meta($page_id, 'boomdevs_metabox', $auto_insert_arr, true);
        $disable_auto_insert = get_post_meta($page_id, 'boomdevs_metabox', true);
        if (gettype($disable_auto_insert) === 'string') {
            $disable_auto_insert = unserialize($disable_auto_insert);
        }

        //Disable Automatic Heading Anchors
        $settings = Boomdevs_Toc_Settings::get_settings();
        if (!empty($settings['exclude_post_type'])) {
            foreach ($settings['exclude_post_type'] as $value) {
                if (get_post_type() === $value) {
                    return $content;
                }
            }
        }

        //Same heading automatic heading anchors
        $content = preg_replace_callback("/\<h([1|2|3|4|5|6])/", function ($matches) {
            static $num = 1;
            $hTag = $matches[1];
            return '<h' . $hTag . ' id="boomdevs_' . $num++ . '"';
        }, $content);

        $pattern = '#<(?P<tag_name>h[1-6])(?P<class>[^>]*)(?P<tag_extra>[^>]*)>(?P<tag_contents>.*?)<\/h[1-6]>#i';

        if (preg_match_all($pattern, $content, $matches, PREG_SET_ORDER)) {
            $same_heading = [];
            foreach ($matches as $match) {
                $tag_name = $match['tag_name'];
                $tag_extra = $match['tag_extra'];
                $tag_contents = $match['tag_contents'];
                $full_tag = $match[0];;
                $tag_class = $match['class'];
                $without_nbsp_title = str_replace("&nbsp;", " ", $tag_contents);
                $slug = mb_strtolower(
                    preg_replace('/([?]|\p{P}|\s)+/u', '-', strip_tags($without_nbsp_title))
                );

                $id = trim($slug, '-');
                $id = str_replace("-8211", "", $id);
                $id = str_replace("8220-", "", $id);
                $id = str_replace("8221-", "", $id);
                $id = str_replace("-amp", "", $id);
                $id = str_replace("-8217", "", $id);

                $new_heading_id = $id;

                if (in_array($id, $same_heading)) {
                    $new_heading_id = $new_heading_id . '-' . count(array_keys($same_heading, $id));
                }

                $same_heading[] = $id;
                $new_tag = "<$tag_name id='$new_heading_id' $tag_class $tag_extra>$tag_contents</$tag_name>";
                $content = str_replace($full_tag, $new_tag, $content);
            }
        }
        return $content;
    }
}
