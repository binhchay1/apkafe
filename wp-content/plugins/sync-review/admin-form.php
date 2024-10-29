<?php
class SYNC_REVIEW_ADMIN
{
    const ID = 'sync_review';

    public function init()
    {
        add_action('admin_menu', array($this, 'add_menu_pages'), 1);
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
        add_action('wp_ajax_sync_review', array($this, 'sync_review'));
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

        if ($_GET['page'] == 'group_post_list') {
            wp_enqueue_style('admin-group-post-list', plugins_url('group-post/asset/css/admin-list.css'));
            wp_enqueue_script('jquery-ui', 'https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.13.2/jquery-ui.min.js');
            wp_enqueue_style('group-post-css', plugins_url('group-post/asset/css/group-post.css'));
        }
    }

    function add_menu_pages()
    {
        add_menu_page('Sync Review', 'Sync Review', 'manage_options', $this->get_id() . '_list', array(&$this, 'load_view_list'), plugins_url('group-post/asset/images/icon.png'));
    }

    public function load_view_list()
    {
        $nonce = wp_create_nonce("save_group_post");
        $link = admin_url('admin-ajax.php');
        add_thickbox();

        echo '
        <div class="container-fluid" style="width: 88% !important; margin-top: 20px"><button class="btn btn-primary">Click</button></div>';
        echo '<script>
            jQuery("button").on("click", function(e) {
                e.preventDefault();

                jQuery.post("' . $link . '", 
                {
                    "action": "sync_review",
                    "nonce": "' . $nonce . '"
                }, 
                function(response) {
                });
            });
            </script>';
    }

    public function sync_review()
    {
        if (!wp_verify_nonce($_REQUEST['nonce'], "save_group_post")) {
            exit("Please don't fucking hack this API");
        }

        global $wpdb;

        $result = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "user_review");
        $arrUpdate = array();

        foreach ($result as $row) {
            if (array_key_exists($row->post_id, $arrUpdate)) {
                $arrUpdate[$row->post_id] = $arrUpdate[$row->post_id] + 1;
            } else {
                $arrUpdate[$row->post_id] = 1;
            }
        }

        foreach ($arrUpdate as $post_id => $count) {
            if (metadata_exists('post', $post_id, 'count-review')) {
                update_post_meta($post_id, 'count-review', $count);
            } else {
                add_post_meta($post_id, 'count-review', $count);
            }
        }

        echo 'success';
    }
}
