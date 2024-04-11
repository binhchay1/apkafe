<?php
class Review_Slide_Admin
{
    const ID = 'review_slide';

    public function init()
    {
        add_action('admin_menu', array($this, 'add_menu_pages'), 1);
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
        add_action('wp_ajax_save_short_code', array($this, 'save_short_code'));
        add_action('wp_ajax_duplicate_short_code', array($this, 'duplicate_short_code'));
        add_action('wp_ajax_edit_short_code', array($this, 'edit_short_code'));
        add_action('wp_ajax_delete_short_code', array($this, 'delete_short_code'));
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

        if ($_GET['page'] == 'review_slide_list') {
            wp_enqueue_style('admin-review-slide-list', plugins_url('review-slide/asset/css/admin-list.css'));
            wp_enqueue_script('jquery-ui', 'https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.13.2/jquery-ui.min.js');
            wp_enqueue_script('review-slide-js', plugins_url('review-slide/asset/js/review-slide.js'));
            wp_enqueue_style('review-slide-css', plugins_url('review-slide/asset/css/review-slide.css'));
        }
    }

    function add_menu_pages()
    {
        add_menu_page('Review Slide', 'Review Slide', 10, $this->get_id() . '_list', array(&$this, 'load_view_list'), plugins_url('review-slide/asset/images/icon.png'));
    }

    public function load_view_list()
    {
        $listShortCode = $this->getListShortCode();
        $nonce = wp_create_nonce("save_review_slide");
        $link = admin_url('admin-ajax.php');
        add_thickbox();

        echo '
        <div class="container">
            <div class="d-flex justify-content-center">
            <span><h2>Review slide <small>with short code</small></h2></span>
            <span class="d-flex" style="align-items: center; margin-left: 10px"><a href="#TB_inline?width=1200&height=550&inlineId=add-slide" class="btn btn-success thickbox">Add slide</a></span>
            </div>
            <ul class="responsive-table">
                <li class="table-header">
                    <div class="col col-4">Title</div>
                    <div class="col col-3">Images</div>
                    <div class="col col-4">Short code</div>
                    <div class="col col-3">Action</div>
                </li>';
        foreach ($listShortCode as $code) {
            $images = explode(',', $code->images);
            echo '
            <li class="table-row">
                <div class="col col-4">' . $code->title . '</div>
                <div class="col col-3">';
            foreach ($images as $id) {
                $url = wp_get_attachment_image_url($id, array(50, 50));
                echo '<img src="' . $url . '" />';
            }

            echo '</div>
                <div class="col col-4">review-slide-shortcode-' . $code->short_code . '</div>
                <div class="col col-3">
                    <a href="#TB_inline?height=550&inlineId=edit-slide-' . $code->id . '" class="btn btn-warning thickbox">Edit</a>
                    <button data-id="' . $code->id . '" type="button" class="btn btn-primary" style="margin-left: 10px" id="duplicate-review-slide-' . $code->id . '">Duplicate</button>
                    <button data-id="' . $code->id . '" type="button" class="btn btn-danger" style="margin-left: 10px" id="delete-review-slide-' . $code->id . '">Delete</button>
                </div>
            </li>';

            echo '<script>
            jQuery("#duplicate-review-slide-' . $code->id . '").on("click", function(e) {
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

            jQuery("#delete-review-slide-' . $code->id . '").on("click", function(e) {
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
            </script>';

            echo '<div id="edit-slide-' . $code->id . '" style="display:none;">';
            echo '<h4>Edit slide</h4>';
            echo '<button type="button" class="btn btn-create-review-slide" id="btn-edit-review-slide-' . $code->id . '">Save change</button>';
            echo '<div id="area-review-slide">';
            echo '<div class="group-review-slide">';
            echo '<label>Title</label>';
            echo '<input id="title-slide-' . $code->id . '" type="text" value="' . $code->title . '" style="margin-left: 15px;" required/>';
            echo '<input id="id-slide-' . $code->id . '" type="hidden" value="' . $code->id . '"/>';
            echo '<label style="margin-left: 20px">Short code</label>';
            echo '<input id="short-code-slide-' . $code->id . '" type="text" value="' . $code->short_code . '" required style="margin-left: 15px;"/> <small>Prefix: review-slide-shortcode-</small>';
            echo '<ul class="binhchay-gallery">';
            foreach ($images as $id) {
                $url = wp_get_attachment_image_url($id, array(50, 50));
                echo '<li class="binhchay-li-item" data-id="' . $id . '">
                        <img src="' . $url . '" /><br>
                        <button type="button" class="btn binhchay-gallery-remove" onclick="removeMediaHandler(jQuery(this))">Delete</button>
                    </li>';
            }
            echo '</ul>
                <input id="review-slide-' . $code->id . '" type="hidden" value="' . $code->images . '" />
                <button type="button" class="button binhchay-upload-button" onclick="addMediaHandle(jQuery(this))">Add Images</button>';
            echo '</div>';
            echo '</div>';
            echo '</div>';
            echo "<script>
            jQuery('.binhchay-gallery').sortable({
                items: 'li',
                cursor: '-webkit-grabbing',
                scrollSensitivity: 40,
        
                stop: function (event, ui) {
                    ui.item.removeAttr('style');
        
                    let sort = new Array()
                    const container = jQuery(this)
        
                    container.find('li').each(function (index) {
                    sort.push(jQuery(this).attr('data-id'));
                    });
        
                    container.parent().next().val(sort.join());
                }
            });
            
            </script>";

            echo '<script>
            jQuery("#btn-edit-review-slide-' . $code->id . '").on("click", function(e) {
                e.preventDefault();
                let title_slide = jQuery("#title-slide-' . $code->id . '").val();
                let review_slide = jQuery("#review-slide-' . $code->id . '").val();
                let short_code_slide = jQuery("#short-code-slide-' . $code->id . '").val();
                let id = jQuery("#id-slide-' . $code->id . '").val();

                if(title_slide == "" || review_slide == "" || short_code_slide == "") {
                    return;
                }

                let data = {
                    "title_slide": title_slide,
                    "review_slide": review_slide,
                    "short_code_slide": short_code_slide,
                    "id": id
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
            </script>';
        }

        echo '</ul></div>';

        echo '<div id="add-slide" style="display:none;">';
        echo '<h4>Add slide</h4>';
        echo '<button type="button" class="btn btn-create-review-slide" id="btn-create-review-slide">Create slide</button>';
        echo '<div id="area-review-slide">';
        echo '<div class="group-review-slide">';
        echo '<label>Title</label>';
        echo '<input id="title-slide" type="text" required/>';
        echo '<label style="margin-left: 20px">Short code</label>';
        echo '<input id="short-code-slide" type="text" required/> <small>Prefix: review-slide-shortcode-</small>';
        echo '<ul class="binhchay-gallery">';
        echo '</ul>
                <input id="review-slide" type="hidden" />
                <button type="button" class="button binhchay-upload-button" onclick="addMediaHandle(jQuery(this))">Add Images</button>';
        echo "</div>";
        echo '</div>';
        echo "<script>
        jQuery('.binhchay-gallery').sortable({
            items: 'li',
            cursor: '-webkit-grabbing',
            scrollSensitivity: 40,
        
            stop: function (event, ui) {
                ui.item.removeAttr('style');
        
                let sort = new Array()
                const container = jQuery(this)
        
                container.find('li').each(function (index) {
                    sort.push(jQuery(this).attr('data-id'));
                });
        
                container.parent().next().val(sort.join());
            }
        });
        </script>";
        echo '<script>
            jQuery("#btn-create-review-slide").on("click", function(e) {
                e.preventDefault();
                let title_slide = jQuery("#title-slide").val();
                let review_slide = jQuery("#review-slide").val();
                let short_code_slide = jQuery("#short-code-slide").val();

                if(title_slide == "" || review_slide == "" || short_code_slide == "") {
                    return;
                }

                let data = {
                    "title_slide": title_slide,
                    "review_slide": review_slide,
                    "short_code_slide": short_code_slide
                };

                jQuery.post("' . $link . '", 
                {
                    "action": "save_short_code",
                    "data": data,
                    "nonce": "' . $nonce . '"
                }, 
                function(response) {
                    if(response == "failed") {
                        
                    } else {
                        location.reload();
                    }
                });
            });
            </script>';
        echo '</div>';
    }

    public function getListShortCode()
    {
        global $wpdb;
        $result = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "review_slide");

        return $result;
    }

    public function save_short_code()
    {
        if (!wp_verify_nonce($_REQUEST['nonce'], "save_review_slide")) {
            exit("Please don't fucking hack this API");
        }

        global $wpdb;

        $data = $_REQUEST['data'];
        if (empty($data['title_slide']) || empty($data['review_slide']) || empty($data['short_code_slide'])) {
            echo 'failed';
            die;
        }

        $query = 'INSERT INTO ' . $wpdb->prefix . 'review_slide (`title`, `images`, `short_code`) VALUES ';
        $query .= ' ("' . $data['title_slide'] . '", "' . $data['review_slide'] . '", "' . $data['short_code_slide'] . '")';
        $wpdb->query($query);

        echo 'success';
    }

    public function edit_short_code()
    {
        if (!wp_verify_nonce($_REQUEST['nonce'], "save_review_slide")) {
            exit("Please don't fucking hack this API");
        }

        global $wpdb;

        $data = $_REQUEST['data'];

        if (empty($data['title_slide']) || empty($data['review_slide']) || empty($data['short_code_slide'])) {
            echo 'failed';
            die;
        }

        $query = 'UPDATE ' . $wpdb->prefix . 'review_slide';
        $query .= ' SET `title` = "' . $data['title_slide'] . '", `images` = "' . $data['review_slide'] . '", `short_code` = "' . $data['short_code_slide'] . '"';
        $query .= ' WHERE id = "' . $data['id'] . '"';
        $wpdb->query($query);

        echo 'success';
    }

    public function duplicate_short_code()
    {
        if (!wp_verify_nonce($_REQUEST['nonce'], "save_review_slide")) {
            exit("Please don't fucking hack this API");
        }

        global $wpdb;

        $data = $_REQUEST['data'];

        if (empty($data)) {
            echo 'failed';
            die;
        }

        $result = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "review_slide WHERE id = '" . $data['id'] . "'");

        $query = 'INSERT INTO ' . $wpdb->prefix . 'review_slide (`title`, `images`, `short_code`) VALUES ';
        $query .= ' ("' . $result[0]->title . '", "' . $result[0]->images . '", "' . $result[0]->short_code . '")';
        $wpdb->query($query);

        echo 'success';
    }

    public function delete_short_code()
    {
        if (!wp_verify_nonce($_REQUEST['nonce'], "save_review_slide")) {
            exit("Please don't fucking hack this API");
        }

        global $wpdb;

        $data = $_REQUEST['data'];

        if (empty($data)) {
            echo 'failed';
            die;
        }

        $query = 'DELETE FROM ' . $wpdb->prefix . 'review_slide';
        $query .= ' WHERE id = "' . $data['id'] . '"';
        $wpdb->query($query);

        echo 'success';
    }
}
