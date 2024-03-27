<?php

if (!defined('ABSPATH'))
    die;

class ZCategoriesImages
{
    public $plugin_name;
    private $placeholder;

    function __construct()
    {
        $this->plugin_name = plugin_basename(__FILE__);
        $this->placeholder = get_template_directory_uri() . '/inc/category-image/image/placeholder.png';
        add_action('admin_init', [$this, 'zAdminInit']);

        add_action('edit_term', [$this, 'zSaveTaxonomyImage']);
        add_action('create_term', [$this, 'zSaveTaxonomyImage']);
        add_filter("plugin_action_links_{$this->plugin_name}", [$this, 'zSettingsLink']);
    }

    function zAdminInit()
    {
        $z_taxonomies = get_taxonomies();
        if (is_array($z_taxonomies)) {
            $zci_options = get_option('zci_options');

            if (!is_array($zci_options))
                $zci_options = array();

            if (empty($zci_options['excluded_taxonomies']))
                $zci_options['excluded_taxonomies'] = array();

            foreach ($z_taxonomies as $z_taxonomy) {
                if (in_array($z_taxonomy, $zci_options['excluded_taxonomies']))
                    continue;
                add_action($z_taxonomy . '_add_form_fields', [$this, 'zAddTexonomyField']);
                add_action($z_taxonomy . '_edit_form_fields', [$this, 'zEditTexonomyField']);
                add_filter('manage_edit-' . $z_taxonomy . '_columns', [$this, 'zTaxonomyColumns']);
                add_filter('manage_' . $z_taxonomy . '_custom_column', [$this, 'zTaxonomyColumn'], 10, 3);

                add_action("delete_{$z_taxonomy}", function ($tt_id) {
                    delete_option('z_taxonomy_image' . $tt_id);
                    delete_option('z_taxonomy_image_id' . $tt_id);
                });
            }
        }

        if (strpos($_SERVER['SCRIPT_NAME'], 'edit-tags.php') > 0 || strpos($_SERVER['SCRIPT_NAME'], 'term.php') > 0) {
            add_action('admin_enqueue_scripts', [$this, 'zAdminEnqueue']);
            add_action('quick_edit_custom_box', [$this, 'zQuickEditCustomBox'], 10, 3);
        }

        register_setting('zci_options', 'zci_options');
        add_settings_section('zci_settings', __('Categories Images settings', 'categories-images'), [$this, 'zSectionText'], 'zci-options');
        add_settings_field('z_excluded_taxonomies', __('Excluded Taxonomies', 'categories-images'), [$this, 'zExcludedTaxonomies'], 'zci-options', 'zci_settings');
    }

    function zAdminEnqueue()
    {
        wp_enqueue_style('categories-images-styles', get_template_directory_uri() . '/inc/category-image/assets/css/zci-styles.css');
        wp_enqueue_script('categories-images-scripts', get_template_directory_uri() . '/inc/category-image/assets/js/zci-scripts.js');

        $zci_js_config = [
            'wordpress_ver' => get_bloginfo("version"),
            'placeholder' => $this->placeholder
        ];
        wp_localize_script('categories-images-scripts', 'zci_config', $zci_js_config);
    }

    function zAddTexonomyField()
    {
        if (get_bloginfo('version') >= 3.5)
            wp_enqueue_media();
        else {
            wp_enqueue_style('thickbox');
            wp_enqueue_script('thickbox');
        }

        echo '<div class="form-field">
            <input type="hidden" name="zci_taxonomy_image_id" id="zci_taxonomy_image_id" value="" />
            <label for="zci_taxonomy_image">' . __('Image', 'categories-images') . '</label>
            <input type="text" name="zci_taxonomy_image" id="zci_taxonomy_image" value="" />
            <br/>
            <button class="z_upload_image_button button">' . __('Upload/Add image', 'categories-images') . '</button>
        </div>';
    }

    function zEditTexonomyField($taxonomy)
    {
        if (get_bloginfo('version') >= 3.5)
            wp_enqueue_media();
        else {
            wp_enqueue_style('thickbox');
            wp_enqueue_script('thickbox');
        }

        if ($this->zTaxonomyImageUrl($taxonomy->term_id, NULL, TRUE) == $this->placeholder) {
            $image_url = "";
            $image_id  = "";
        } else {
            $image_url = $this->zTaxonomyImageUrl($taxonomy->term_id, NULL, TRUE);
            $image_id  = $this->zTaxonomyImageID($taxonomy->term_id);
        }
        echo '<tr class="form-field">
            <th scope="row" valign="top"><label for="zci_taxonomy_image">' . __('Image', 'categories-images') . '</label></th>
            <td><input type="hidden" name="zci_taxonomy_image_id" id="zci_taxonomy_image_id" value="' . esc_attr($image_id) . '" /><img class="zci-taxonomy-image" src="' . esc_url($this->zTaxonomyImageUrl($taxonomy->term_id, 'medium', TRUE)) . '"/><br/><input type="text" name="zci_taxonomy_image" id="zci_taxonomy_image" value="' . esc_url($image_url) . '" /><br />
            <button class="z_upload_image_button button">' . __('Upload/Add image', 'categories-images') . '</button>
            <button class="z_remove_image_button button">' . __('Remove image', 'categories-images') . '</button>
            </td>
        </tr>';
    }

    /**
     * Thumbnail column added to category admin.
     *
     * @access public
     * @param mixed $columns
     * @return void
     */
    function zTaxonomyColumns($columns)
    {
        $new_columns = array();
        $new_columns['cb'] = $columns['cb'];
        $new_columns['thumb'] = __('Image', 'categories-images');

        unset($columns['cb']);

        return array_merge($new_columns, $columns);
    }

    /**
     * Thumbnail column value added to category admin.
     *
     * @access public
     * @param mixed $columns
     * @param mixed $column
     * @param mixed $id
     * @return void
     */
    function zTaxonomyColumn($columns, $column, $id)
    {
        if ($column == 'thumb')
            $columns = '<span><img src="' . $this->zTaxonomyImageUrl($id, 'thumbnail', TRUE) . '" alt="' . __('Thumbnail', 'categories-images') . '" class="wp-post-image" /></span>';

        return $columns;
    }

    function zQuickEditCustomBox($column_name, $screen, $name)
    {
        if ($column_name == 'thumb')
            echo '<fieldset>
            <div class="thumb inline-edit-col">
                <label>
                    <span class="title"><img src="" alt="Thumbnail"/></span>
                    <span class="input-text-wrap"><input type="text" name="zci_taxonomy_image" value="" class="tax_list" /></span>
                    <span class="input-text-wrap">
                        <button class="z_upload_image_button button">' . __('Upload/Add image', 'categories-images') . '</button>
                        <button class="z_remove_image_button button">' . __('Remove image', 'categories-images') . '</button>
                    </span>
                    <span class="input-text-wrap">
                        <input type="hidden" name="zci_taxonomy_image_id" value="" />
                    </span>
                </label>
            </div>
        </fieldset>';
    }

    function zSaveTaxonomyImage($term_id)
    {
        if (isset($_POST['zci_taxonomy_image'])) {
            update_option('z_taxonomy_image' . $term_id, $_POST['zci_taxonomy_image'], false);
        }
        if (isset($_POST['zci_taxonomy_image_id'])) {
            update_option('z_taxonomy_image_id' . $term_id, $_POST['zci_taxonomy_image_id'], false);
        }
    }

    // get attachment ID by image url
    function zGetAttachmentIdByUrl($image_src)
    {
        global $wpdb;
        $query = $wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE guid = %s", $image_src);
        $id = $wpdb->get_var($query);
        return (!empty($id)) ? $id : NULL;
    }

    // get attachment ID by term id
    function zTaxonomyImageID($term_id = NULL)
    {
        if (!$term_id) {
            if (is_category())
                $term_id = get_query_var('cat');
            elseif (is_tag())
                $term_id = get_query_var('tag_id');
            elseif (is_tax()) {
                $current_term = get_term_by('slug', get_query_var('term'), get_query_var('taxonomy'));
                $term_id = $current_term->term_id;
            }
        }

        $taxonomy_image_id = get_option('z_taxonomy_image_id' . $term_id);
        return $taxonomy_image_id;
    }

    // get taxonomy image url for the given term_id (Place holder image by default)
    function zTaxonomyImageUrl($term_id = NULL, $size = 'full', $return_placeholder = FALSE)
    {
        if (!$term_id) {
            if (is_category())
                $term_id = get_query_var('cat');
            elseif (is_tag())
                $term_id = get_query_var('tag_id');
            elseif (is_tax()) {
                $current_term = get_term_by('slug', get_query_var('term'), get_query_var('taxonomy'));
                $term_id = $current_term->term_id;
            }
        }

        $taxonomy_image_url = get_option('z_taxonomy_image' . $term_id);
        if (!empty($taxonomy_image_url)) {
            $attachment_id = $this->zGetAttachmentIdByUrl($taxonomy_image_url);
            if (empty($attachment_id)) {
                $attachment_id = $this->zTaxonomyImageID($term_id);
            }
            if (!empty($attachment_id)) {
                $taxonomy_image_url = wp_get_attachment_image_src($attachment_id, $size);
                $taxonomy_image_url = $taxonomy_image_url[0];
            }
        }

        if ($return_placeholder)
            return ($taxonomy_image_url != '') ? $taxonomy_image_url : $this->placeholder;
        else
            return $taxonomy_image_url;
    }

    // display taxonomy image for the given term_id
    function zTaxonomyImage($term_id = NULL, $size = 'full', $attr = NULL, $echo = TRUE)
    {
        if (!$term_id) {
            if (is_category())
                $term_id = get_query_var('cat');
            elseif (is_tag())
                $term_id = get_query_var('tag_id');
            elseif (is_tax()) {
                $current_term = get_term_by('slug', get_query_var('term'), get_query_var('taxonomy'));
                $term_id = $current_term->term_id;
            }
        }

        $taxonomy_image_url = get_option('z_taxonomy_image' . $term_id);
        if (!empty($taxonomy_image_url)) {
            $attachment_id = $this->zGetAttachmentIdByUrl($taxonomy_image_url);
            if (empty($attachment_id)) {
                $attachment_id = $this->zTaxonomyImageID($term_id);
            }
            if (!empty($attachment_id))
                $taxonomy_image = wp_get_attachment_image($attachment_id, $size, FALSE, $attr);
            else {
                $image_attr = '';
                if (is_array($attr)) {
                    if (!empty($attr['class']))
                        $image_attr .= ' class="' . $attr['class'] . '" ';
                    if (!empty($attr['alt']))
                        $image_attr .= ' alt="' . $attr['alt'] . '" ';
                    if (!empty($attr['width']))
                        $image_attr .= ' width="' . $attr['width'] . '" ';
                    if (!empty($attr['height']))
                        $image_attr .= ' height="' . $attr['height'] . '" ';
                    if (!empty($attr['title']))
                        $image_attr .= ' title="' . $attr['title'] . '" ';
                }
                $taxonomy_image = '<img src="' . $taxonomy_image_url . '" ' . $image_attr . '/>';
            }
        } else {
            $taxonomy_image = '';
        }

        if ($echo)
            echo $taxonomy_image;
        else
            return $taxonomy_image;
    }

    function zSettingsPage()
    {
        if (!current_user_can('manage_options'))
            wp_die(__('You do not have sufficient permissions to access this page.', 'categories-images'));
        require_once '/inc/category-image/templates/admin.php';
    }

    function zSettingsLink($links)
    {
        $settings_link = '<a href="admin.php?page=zci_settings">Settings</a>';
        array_push($links, $settings_link);
        return $links;
    }

    function zSectionText()
    {
        echo '<p>' . __('Please select the taxonomies you want to exclude it from Categories Images plugin', 'categories-images') . '</p>';
    }

    function zExcludedTaxonomies()
    {
        $options = get_option('zci_options');
        $disabled_taxonomies = ['nav_menu', 'link_category', 'post_format'];
        foreach (get_taxonomies() as $tax) : if (in_array($tax, $disabled_taxonomies)) continue; ?>
            <input type="checkbox" name="zci_options[excluded_taxonomies][<?php echo $tax ?>]" value="<?php echo $tax ?>" <?php checked(isset($options['excluded_taxonomies'][$tax])); ?> /> <?php echo $tax; ?><br />
<?php endforeach;
    }
}

if (class_exists('ZCategoriesImages')) {
    $z_categories_images = new ZCategoriesImages();

    function z_taxonomy_image_url($term_id = NULL, $size = 'full', $return_placeholder = FALSE)
    {
        $zci = new ZCategoriesImages();
        return $zci->zTaxonomyImageUrl($term_id, $size, $return_placeholder);
    }

    function z_taxonomy_image_id($term_id = NULL)
    {
        $zci = new ZCategoriesImages();
        return $zci->zTaxonomyImageID($term_id);
    }

    function z_taxonomy_image($term_id = NULL, $size = 'full', $attr = NULL, $echo = TRUE)
    {
        $zci = new ZCategoriesImages();
        return $zci->zTaxonomyImage($term_id, $size, $attr, $echo);
    }
}
