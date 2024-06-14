<?php
class Group_Post_Admin
{
    const ID = 'group_post';

    public function init()
    {
        add_action('admin_menu', array($this, 'add_menu_pages'), 1);
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
        add_action('wp_ajax_save_short_code', array($this, 'save_short_code'));
        add_action('wp_ajax_duplicate_short_code', array($this, 'duplicate_short_code'));
        add_action('wp_ajax_edit_short_code', array($this, 'edit_short_code'));
        add_action('wp_ajax_delete_short_code', array($this, 'delete_short_code'));
        add_action('wp_ajax_get_list_post_category', array($this, 'get_list_post_category'));
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
        add_menu_page('Group Post', 'Group Post', 'manage_options', $this->get_id() . '_list', array(&$this, 'load_view_list'), plugins_url('group-post/asset/images/icon.png'));
    }

    public function load_view_list()
    {
        $listShortCode = $this->getListShortCode();
        $listCategory = $this->getListCategory();

        $nonce = wp_create_nonce("save_group_post");
        $link = admin_url('admin-ajax.php');
        add_thickbox();

        echo '
        <div class="container-fluid" style="width: 88% !important; margin-top: 20px">
            <div class="d-flex justify-content-center">
            <span><h2>Group post<small style="margin-left: 10px">with short code</small></h2></span>
            <span class="d-flex" style="align-items: center; margin-left: 10px"><a href="#TB_inline?width=1200&height=550&inlineId=add-slide" class="btn btn-success thickbox">Add group</a></span>
            </div>
            <ul class="responsive-table" style="margin-top: 20px">
                <li class="table-header">
                    <div class="col col-1">Title</div>
                    <div class="col col-3">Description</div>
                    <div class="col col-4">Short code</div>
                    <div class="col col-3">Action</div>
                </li>';
        foreach ($listShortCode as $code) {
            $listID = explode(',', $code->post_id);

            echo '<li class="table-row">';
            echo '<div class="col col-1">' . $code->title . '</div>';
            echo '<div class="col col-3" style="overflow: hidden;">' . $code->description . '</div>';
            echo '<div class="col col-4">
                <span id="">[group-post-shortcode-' . $code->short_code . ']</span>
                <button class="btn" id="copy-shortcode-' . $code->id . '" style="padding: 0 !important; margin-bottom: 7px;"><img style="width: 15px !important" src="' . plugins_url('group-post/asset/images/icon-copy.jpg') . '"></button>
                </div>';
            echo '<div class="col col-3">
                    <a href="#TB_inline?height=550&inlineId=edit-slide-' . $code->id . '" class="btn btn-warning thickbox">Edit</a>
                    <button data-id="' . $code->id . '" type="button" class="btn btn-primary" style="margin-left: 10px" id="duplicate-group-post-' . $code->id . '">Duplicate</button>
                    <button data-id="' . $code->id . '" type="button" class="btn btn-danger" style="margin-left: 10px" id="delete-group-post-' . $code->id . '">Delete</button>
                </div>
            </li>';

            echo '<script>
            jQuery("#duplicate-group-post-' . $code->id . '").on("click", function(e) {
                e.preventDefault();
                let id = jQuery(this).attr("data-id");

                let data = {
                    "id": id
                };

                jQuery.post("' . $link . '", 
                {
                    "action": "duplicate_short_code",
                    "data": data,
                    "nonce": "' . $nonce . '"
                }, 
                function(response) {
                    location.reload();
                });
            });

            jQuery("#delete-group-post-' . $code->id . '").on("click", function(e) {
                e.preventDefault();
                let id = jQuery(this).attr("data-id");

                let data = {
                    "id": id
                };

                jQuery.post("' . $link . '", 
                {
                    "action": "delete_short_code",
                    "data": data,
                    "nonce": "' . $nonce . '"
                }, 
                function(response) {
                    location.reload();
                });
            });

            jQuery("#copy-shortcode-' . $code->id . '").on("click", function(e) {
                e.preventDefault();
                jQuery(this).prev().text();
                var temp = jQuery("<input>");
                jQuery("body").append(temp);
                temp.val(jQuery(this).prev().text()).select();
                document.execCommand("copy");
                temp.remove();
            });

            </script>';

            echo '<div id="edit-slide-' . $code->id . '" style="display:none;">';
            echo '<div style="display: flex; margin-top: 10px"><h4>Edit group</h4>';
            echo '<button type="button" class="btn btn-create-group-post" id="btn-edit-group-post-' . $code->id . '">Save change</button></div>';
            echo '<div id="area-group-post">';
            echo '<div class="group-group-post">';
            echo '<label>Title</label>';
            echo '<input id="title-group-post-' . $code->id . '" type="text" value="' . $code->title . '" style="margin-left: 15px;" />';
            echo '<input id="id-group-post-' . $code->id . '" type="hidden" value="' . $code->id . '"/>';
            echo '<label style="margin-left: 20px">Short code</label>';
            echo '<input id="short-code-group-post-' . $code->id . '" type="text" value="' . $code->short_code . '" style="margin-left: 15px;" disabled /> <small>Prefix: group-post-shortcode-</small>';
            echo '<label>Description</label>';
            echo '<input style="margin-top: 20px; margin-left: 20px; width: 85%" id="description-group-post-' . $code->id . '" type="text" value="' . $code->description . '"/>';
            echo '<div style="margin-top: 20px"><label>Category</label>';
            echo '<select class="ml-20" id="category-group-post-edit-' . $code->id . '" type="text">';
            foreach ($listCategory as $category) {
                if ($category->term_id == $code->category) {
                    echo '<option value="' . $category->term_id . '" selected>' . $category->cat_name . '</option>';
                } else {
                    echo '<option value="' . $category->term_id . '">' . $category->cat_name . '</option>';
                }
            }
            echo '</select></div>';
            echo '<ul class="ul-list-post-group-post" id="list-post-edit-group-post-' . $code->id . '">';
            foreach ($listID as $id) {
                echo '<li id="item-edit-with-' . $id . '" class="list-item-category-post">' . get_the_title($id) . '<span class="btn-x-delete" data-id="' . $id . '"> X </span></li>';
            }
            echo '</ul>';
            echo '<input id="group-post-' . $code->id . '" type="hidden" />';
            echo '</div>';

            echo '<script>
            var stringGroupPostEdit_' . $code->id . ' = "' . $code->post_id . '";
            var arrayGroupPostEdit_' . $code->id . ' = stringGroupPostEdit_' . $code->id . '.split(",");
            jQuery("#group-post-' . $code->id . '").val(arrayGroupPostEdit_' . $code->id . ');

            jQuery("#category-group-post-edit-' . $code->id . '").on("change", function() {
                let category_id = jQuery(this).val();
                let data = {
                    "category_id": category_id
                };

                jQuery.post("' . $link . '", 
                {
                    "action": "get_list_post_category",
                    "data": data,
                    "nonce": "' . $nonce . '"
                }, 
                function(response) {
                    arrayGroupPostEdit_' . $code->id . ' = [];
                    jQuery("#list-post-edit-group-post-' . $code->id . '").empty();
                    for(let i in response) {
                        let strAppend = `<li id="item-add-with-` + response[i].ID + `" class="list-item-category-post">` + response[i].post_title + `<span class="btn-x-delete" data-id="` + response[i].ID + `"> X </span></li>`;
                        jQuery("#list-post-edit-group-post-' . $code->id . '").append(strAppend);
                        arrayGroupPostEdit_' . $code->id . '.push(response[i].ID);
                    }

                    jQuery("#group-post-' . $code->id . '").val(arrayGroupPostEdit_' . $code->id . ');
                });
            });

            jQuery("#btn-edit-group-post-' . $code->id . '").on("click", function(e) {
                e.preventDefault();
            
                let title_group_post_' . $code->id . ' = jQuery("#title-group-post-' . $code->id . '").val();
                let group_post_' . $code->id . ' = jQuery("#group-post-' . $code->id . '").val();
                let short_code_group_post_' . $code->id . ' = jQuery("#short-code-group-post-' . $code->id . '").val();
                let description_group_post_' . $code->id . ' = jQuery("#description-group-post-' . $code->id . '").val();
                let category_group_post_' . $code->id . ' = jQuery("#category-group-post-edit-' . $code->id . '").val();
                let id_group_post_' . $code->id . ' = jQuery("#id-group-post-' . $code->id . '").val();

                if(title_group_post_' . $code->id . ' == "" || group_post_' . $code->id . ' == "" || short_code_group_post_' . $code->id . ' == "" || category_group_post_' . $code->id . ' == "") {
                    return;
                }

                let data = {
                    "id_group_post": id_group_post_' . $code->id . ',
                    "title_group_post": title_group_post_' . $code->id . ',
                    "group_post": group_post_' . $code->id . ',
                    "short_code_group_post": short_code_group_post_' . $code->id . ',
                    "description_group_post": description_group_post_' . $code->id . ',
                    "category_group_post": category_group_post_' . $code->id . '
                };

                jQuery.post("' . $link . '", 
                {
                    "action": "edit_short_code",
                    "data": data,
                    "nonce": "' . $nonce . '"
                }, 
                function(response) {
                    location.reload();
                });
            });

            jQuery("#list-post-edit-group-post-' . $code->id . '").on("click", "li .btn-x-delete", function() {
                let idDelete = jQuery(this).attr("data-id");
                let index = arrayGroupPostEdit_' . $code->id . '.indexOf(idDelete);
                let idLi = "#list-post-edit-group-post-' . $code->id . '  #item-edit-with-" + idDelete;
                if (index !== -1) {
                    arrayGroupPostEdit_' . $code->id . '.splice(index, 1);
                }
                    
                jQuery("#group-post-' . $code->id . '").val(arrayGroupPostEdit_' . $code->id . ');
                jQuery(idLi).remove();
            });
            </script>';
        }

        echo '</ul></div>';

        $auto_short_code = 1;
        if (end($listShortCode)) {
            $auto_short_code = end($listShortCode)->id;
        }
        echo '<div id="add-slide" style="display:none;">';
        echo '<div style="display: flex; margin-top: 10px"><h4>Add group</h4>';
        echo '<button type="button" class="btn btn-create-group-post" id="btn-create-group-post">Create group</button></div>';
        echo '<div id="area-group-post">';
        echo '<div class="group-group-post">';
        echo '<label>Title</label>';
        echo '<input id="title-group-post" type="text" class="ml-20"/>';
        echo '<label style="margin-left: 20px">Short code</label>';
        echo '<input id="short-code-group-post" type="text" value="' . $auto_short_code . '" class="ml-20" disabled/> <small>Prefix: group-post-shortcode-</small>';
        echo '<div class="area-description-group-post"><label>Description</label>';
        echo '<textarea class="description-group-post-textarea" id="description-group-post" required></textarea></div>';
        echo '<div style="margin-top: 20px"><label>Category</label>';
        echo '<select class="ml-20" id="category-group-post" type="text">';
        foreach ($listCategory as $category) {
            echo '<option value="' . $category->term_id . '">' . $category->cat_name . '</option>';
        }
        echo '</select></div>';
        echo '<ul class="ul-list-post-group-post" id="list-post-add-group-post">';
        echo '</ul>';
        echo '<input id="group-post" type="hidden" />';
        echo "</div>";
        echo '<script>
            var arrayGroupPost = [];
            jQuery("#btn-create-group-post").on("click", function(e) {
                e.preventDefault();
                let title_group_post = jQuery("#title-group-post").val();
                let group_post = jQuery("#group-post").val();
                let short_code_group_post = jQuery("#short-code-group-post").val();
                let description_group_post = jQuery("#description-group-post").val();
                let category_group_post = jQuery("#category-group-post").val();

                if(title_group_post == "" || group_post == "" || short_code_group_post == "" || category_group_post == "") {
                    return;
                }

                let data = {
                    "title_group_post": title_group_post,
                    "group_post": group_post,
                    "short_code_group_post": short_code_group_post,
                    "description_group_post": description_group_post,
                    "category_group_post": category_group_post,
                };

                jQuery.post("' . $link . '", 
                {
                    "action": "save_short_code",
                    "data": data,
                    "nonce": "' . $nonce . '"
                }, 
                function(response) {
                    location.reload();
                });
            });

            jQuery("#category-group-post").on("change", function() {
                let category_id = jQuery(this).val();
                let data = {
                    "category_id": category_id
                };

                jQuery.post("' . $link . '", 
                {
                    "action": "get_list_post_category",
                    "data": data,
                    "nonce": "' . $nonce . '"
                }, 
                function(response) {
                    arrayGroupPost = [];
                    jQuery("#list-post-add-group-post").empty();
                    for(let i in response) {
                        let strAppend = `<li id="item-add-with-` + response[i].ID + `" class="list-item-category-post">` + response[i].post_title + `<span class="btn-x-delete" data-id="` + response[i].ID + `"> X </span></li>`;
                        jQuery("#list-post-add-group-post").append(strAppend);
                        arrayGroupPost.push(response[i].ID);
                    }

                    jQuery("#group-post").val(arrayGroupPost);
                });
            });

            jQuery("#list-post-add-group-post").on("click", "li .btn-x-delete", function() {
                let idDelete = jQuery(this).attr("data-id");
                let index = arrayGroupPost.indexOf(idDelete);
                let idLi = "#list-post-add-group-post  #item-add-with-" + idDelete;
                if (index !== -1) {
                    arrayGroupPost.splice(index, 1);
                }

                jQuery(idLi).remove();
            });
            </script>';
        echo '</div>';
    }

    public function getListShortCode()
    {
        global $wpdb;
        $result = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "group_post");

        return $result;
    }

    public function save_short_code()
    {
        if (!wp_verify_nonce($_REQUEST['nonce'], "save_group_post")) {
            exit("Please don't fucking hack this API");
        }

        global $wpdb;

        $data = $_REQUEST['data'];

        if (empty($data['title_group_post']) || empty($data['group_post']) || empty($data['short_code_group_post']) || empty($data['category_group_post'])) {
            echo 'failed';
            die;
        }

        $query = 'INSERT INTO ' . $wpdb->prefix . 'group_post (`title`, `post_id`, `short_code`, `description`, `category`) VALUES ';
        $query .= ' ("' . $data['title_group_post'] . '", "' . $data['group_post'] . '", "' . $data['short_code_group_post'] . '", "' . $data['description_group_post'] . '", "' . $data['category_group_post'] . '")';
        $wpdb->query($query);

        echo 'success';
    }

    public function edit_short_code()
    {
        if (!wp_verify_nonce($_REQUEST['nonce'], "save_group_post")) {
            exit("Please don't fucking hack this API");
        }

        global $wpdb;

        $data = $_REQUEST['data'];

        if (empty($data['title_group_post']) || empty($data['group_post']) || empty($data['short_code_group_post']) || empty($data['category_group_post']) || empty($data['id_group_post'])) {
            echo 'failed';
            die;
        }

        $query = 'UPDATE ' . $wpdb->prefix . 'group_post';
        $query .= ' SET `title` = "' . $data['title_group_post'] . '", `post_id` = "' . $data['group_post'] . '", `short_code` = "' . $data['short_code_group_post'] . '", `description` = "' . $data['description_group_post'] . '", `category` = "' . $data['category_group_post'] . '"';
        $query .= ' WHERE id = "' . $data['id_group_post'] . '"';

        $wpdb->query($query);

        echo 'success';
    }

    public function duplicate_short_code()
    {
        if (!wp_verify_nonce($_REQUEST['nonce'], "save_group_post")) {
            exit("Please don't fucking hack this API");
        }

        global $wpdb;

        $data = $_REQUEST['data'];

        if (empty($data)) {
            echo 'failed';
            die;
        }

        $result = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "group_post WHERE id = '" . $data['id'] . "'");
        $getLast = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "group_post ORDER BY id DESC");
        $short_code = (int) $getLast[0]->id + 1;

        $query = 'INSERT INTO ' . $wpdb->prefix . 'group_post (`title`, `post_id`, `short_code`, `description`, `category`) VALUES ';
        $query .= ' ("' . $result[0]->title . '", "' . $result[0]->post_id . '", "' . $short_code . '", "' . $result[0]->description . '", "' . $result[0]->category . '")';
        $wpdb->query($query);

        echo 'success';
    }

    public function delete_short_code()
    {
        if (!wp_verify_nonce($_REQUEST['nonce'], "save_group_post")) {
            exit("Please don't fucking hack this API");
        }

        global $wpdb;

        $data = $_REQUEST['data'];

        if (empty($data)) {
            echo 'failed';
            die;
        }

        $query = 'DELETE FROM ' . $wpdb->prefix . 'group_post';
        $query .= ' WHERE id = "' . $data['id'] . '"';
        $wpdb->query($query);

        echo 'success';
    }

    public function get_list_post_category()
    {
        if (!wp_verify_nonce($_REQUEST['nonce'], "save_group_post")) {
            exit("Please don't fucking hack this API");
        }

        $data = $_REQUEST['data'];

        if (empty($data['category_id'])) {
            echo 'failed';
            die;
        }

        $args = array('cat' => $data['category_id'], 'orderby' => 'post_date', 'order' => 'DESC', 'post_status' => 'publish', 'posts_per_page' => -1);
        $result = query_posts($args);

        wp_send_json($result);
    }

    public function getListCategory()
    {
        $categories = get_categories();

        return $categories;
    }
}
