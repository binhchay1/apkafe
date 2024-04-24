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

        $args = array(
            'post_type' => 'product',
            'posts_per_page' => -1,
        );

        $query = new WP_Query($args);
        $listProduct = $query->posts;

        foreach ($listProduct as $product) {
            $product_id = $product->ID;
            $meta = get_post_meta($product_id);

            foreach ($meta as $key => $value) {
                $lasso_post = array(
                    'post_title'   => $apple_product['title'],
                    'post_type'    => LASSO_POST_TYPE,
                    'post_name'    => $apple_product['title'],
                    'post_content' => '',
                    'post_status'  => 'publish',
                    'meta_input'   => array(
                        'lasso_custom_redirect'  => $url,
                        'lasso_final_url'        => $get_final_url,

                        'rating' => !empty($google_product['rating']) ? $google_product['rating'] : $apple_product['rating'],
                        'developer' => !empty($google_product['developer']) ? $google_product['developer'] : $apple_product['developer'],
                        'categories' => $google_product['categories'],
                        'version' => $apple_product['version'],
                        'size' => $apple_product['size'],
                        'screen_shots' => $google_product['screen_shots'],
                        'apple_url' => $apple_product['base_url'],
                        'google_play_url' => $google_product['base_url'],

                        'affiliate_desc'         => $description,
                        'price'                  => $apple_product['price'],
                        'lasso_custom_thumbnail' => $apple_product['thumbnail'],

                        'enable_nofollow'        => $post_data['enable_nofollow'] ?? $lasso_url->enable_nofollow,
                        'open_new_tab'           => $post_data['open_new_tab'] ?? $lasso_url->open_new_tab,
                        'enable_nofollow2'       => $post_data['enable_nofollow2'] ?? $lasso_url->enable_nofollow2,
                        'open_new_tab2'          => $post_data['open_new_tab2'] ?? $lasso_url->open_new_tab2,
                        'link_cloaking'          => $post_data['link_cloaking'] ?? $lasso_url->link_cloaking,

                        'custom_theme'           => $post_data['theme_name'] ?? $lasso_url->display->theme,
                        'disclosure_text'        => trim($post_data['disclosure_text'] ?? $lasso_url->display->disclosure_text),
                        'badge_text'             => $post_data['badge_text'] ?? $lasso_url->display->badge_text,
                        'buy_btn_text'           => $post_data['buy_btn_text'] ?? $lasso_url->display->primary_button_text,
                        'second_btn_url'         => $post_data['second_btn_url'] ?? $lasso_url->display->secondary_url,
                        'second_btn_text'        => $post_data['second_btn_text'] ?? $lasso_url->display->secondary_button_text,

                        'show_price'             => $post_data['show_price'] ?? $lasso_url->display->show_price,
                        'show_disclosure'        => $post_data['show_disclosure'] ?? $lasso_url->display->show_disclosure,
                        'show_description'       => $show_description,
                        'enable_sponsored'       => $post_data['enable_sponsored'] ?? $lasso_url->enable_sponsored,
                    ),
                );

                $defaults = array(
                    'post_author'           => $user_id,
                    'post_content'          => '',
                    'post_content_filtered' => '',
                    'post_title'            => '',
                    'post_excerpt'          => '',
                    'post_status'           => 'draft',
                    'post_type'             => 'post',
                    'comment_status'        => '',
                    'ping_status'           => '',
                    'post_password'         => '',
                    'to_ping'               => '',
                    'pinged'                => '',
                    'post_parent'           => 0,
                    'menu_order'            => 0,
                    'guid'                  => '',
                    'import_id'             => 0,
                    'context'               => '',
                );

                $postarr = wp_parse_args($lasso_post, $defaults);
            }

            echo '<pre>';
            var_dump($meta);
            echo '</pre>';
            die;
        }
    }
}
