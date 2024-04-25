<?php
class Import_Data_Admin
{
    const ID = 'import_data';

    public function init()
    {
        add_action('admin_menu', array($this, 'add_menu_pages'), 1);
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
        add_action('wp_ajax_import_data', array($this, 'import_data'));
    }

    public function get_id()
    {
        return self::ID;
    }

    public function admin_enqueue_scripts($hook_suffix)
    {
        if (strpos($hook_suffix, $this->get_id()) === false) {
            return;
        }

        wp_enqueue_media();
        wp_enqueue_style('config-admin-form-bs', 'https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css');
        wp_enqueue_script('config-admin-form-bs', 'https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js', array('jquery'));
        wp_enqueue_script('config-admin-form-bs', 'https://code.jquery.com/jquery-3.7.1.slim.js');
    }

    function add_menu_pages()
    {
        add_menu_page('Import Data', 'Import Data', 'manage_options', $this->get_id() . '_list', array(&$this, 'load_view_list'), plugins_url('import-data/asset/images/icon.png'));
    }

    public function load_view_list()
    {
        $nonce = wp_create_nonce("import_data");
        $link = admin_url('admin-ajax.php');

        echo '<button type="button" class="btn btn-primary" id="import-data-btn">Import Data</button>';
        echo '<script>
        jQuery("#import-data-btn").click(function () {
            jQuery.post("' . $link . '", 
            {
                "action": "import_data",
                "nonce": "' . $nonce . '"
            }, 
            function(response) {
                
            });
        });
        
        </script>';
    }

    public function import_data()
    {
        if (!wp_verify_nonce($_REQUEST['nonce'], "import_data")) {
            exit("Please don't fucking hack this API");
        }

        global $wpdb;
        $args = array(
            'post_type' => 'product',
            'posts_per_page' => -1,
        );

        $query = new WP_Query($args);
        $listProduct = $query->posts;

        foreach ($listProduct as $product) {
            $product_id = $product->ID;
            $meta = get_post_meta($product_id);
            $data = [];
            $data['categories'] = get_the_terms($product->ID, 'product_cat')[0]->name;

            foreach ($meta as $key => $value) {
                if ($key == 'app-icon') {
                    $data['app-icon'] = $value[0];
                }

                if ($key == 'store-link-apple') {
                    $data['store-link-apple'] = $value[0];
                }

                if ($key == 'store-link-google') {
                    $data['store-link-google'] = $value[0];
                }

                if ($key == 'port-author-name') {
                    $data['port-author-name'] = $value[0];
                }

                if ($key == '_wc_average_rating') {
                    $data['_wc_average_rating'] = $value[0];
                }

                if ($key == 'port-version') {
                    $data['port-version'] = $value[0];
                }

                if ($key == 'custom-screenshot') {
                    $data['custom-screenshot'] = $value[0];
                }

                $data['price'] = 'Free';
            }

            $lasso_post = array(
                'post_title'   => $product->post_title,
                'post_type'    => 'lasso-urls',
                'post_name'    => $product->post_name,
                'post_status'  => 'publish',
                'post_author'           => $product->post_author,
                'post_content'          => $product->post_content,
                'post_content_filtered' => '',
                'post_excerpt'          => '',
                'comment_status'        => 'closed',
                'ping_status'           => '',
                'post_password'         => '',
                'to_ping'               => '',
                'pinged'                => '',
                'post_parent'           => 0,
                'menu_order'            => 0,
                'guid'                  => $product->guid,
                'post_date'             => $product->post_date,
                'post_date_gmt'         => $product->post_date_gmt,
                'comment_count'         => $product->comment_count,
                'post_parent'         => $product->post_parent,
                'post_modified'         => $product->post_modified,
                'post_modified_gmt'         => $product->post_modified_gmt,
                'to_ping'         => $product->to_ping,
                'meta_input'   => array(
                    'lasso_custom_redirect'  => $data['store-link-google'],
                    'lasso_final_url'        => $data['store-link-google'],

                    'rating' => $data['_wc_average_rating'],
                    'developer' => $data['port-author-name'],
                    'categories' => $data['categories'],
                    'version' => $data['port-version'],
                    'size' => '',
                    'screen_shots' => $data['custom-screenshot'],
                    'apple_url' => $data['store-link-apple'],
                    'google_play_url' => $data['store-link-google'],

                    'affiliate_desc'         => $description,
                    'price'                  => $data['price'],
                    'lasso_custom_thumbnail' => $data['app-icon'],

                    'enable_nofollow'        => 1,
                    'open_new_tab'           => 1,
                    'enable_nofollow2'       => 1,
                    'open_new_tab2'          => 1,
                    'link_cloaking'          => 1,

                    'custom_theme'           => '',
                    'disclosure_text'        => 'We earn a commission if you make a purchase, at no additional cost to you.',
                    'badge_text'             => '',
                    'buy_btn_text'           => '',
                    'second_btn_url'         => '',
                    'second_btn_text'        => '',

                    'show_price'             => 1,
                    'show_disclosure'        => 1,
                    'show_description'       => 1,
                    'enable_sponsored'       => 1,
                ),
            );

            $defaults = array(
                'post_title'   => $product->post_title,
                'post_type'    => 'lasso-urls',
                'post_name'    => $product->post_name,
                'post_status'  => 'publish',
                'post_author'           => $product->post_author,
                'post_content'          => $product->post_content,
                'post_content_filtered' => '',
                'post_excerpt'          => '',
                'comment_status'        => 'closed',
                'ping_status'           => '',
                'post_password'         => '',
                'to_ping'               => '',
                'pinged'                => '',
                'post_parent'           => 0,
                'menu_order'            => 0,
                'guid'                  => $product->guid,
                'post_date'             => $product->post_date,
                'post_date_gmt'         => $product->post_date_gmt,
                'comment_count'         => $product->comment_count,
                'post_parent'         => $product->post_parent,
                'post_modified'         => $product->post_modified,
                'post_modified_gmt'         => $product->post_modified_gmt,
                'to_ping'         => $product->to_ping,
            );

            $wpdb->insert($wpdb->posts, $defaults);
            $post_ID = $wpdb->insert_id;

            foreach ($lasso_post['meta_input'] as $field => $value) {
                update_post_meta($post_ID, $field, $value);
            }

            $parse_url = wp_parse_url($lasso_post['meta_input']['lasso_custom_redirect']);
            $dataUrlDetail = [
                'lasso_id' => $post_ID,
                'redirect_url' => $lasso_post['meta_input']['lasso_custom_redirect'],
                'base_domain' => $parse_url['host']
            ];

            $wpdb->insert('wp_lasso_url_details', $dataUrlDetail);
        }
    }
}
