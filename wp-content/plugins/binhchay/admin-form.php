<?php
class Apkafe_Admin_Form
{
    const ID = 'apkafe-seo';

    public function init()
    {
        add_action('admin_menu', array($this, 'add_menu_page'), 1);
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
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
        wp_enqueue_style('apkafe-admin-form-bs', 'https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css', BINHCHAY_ADMIN_VERSION);
        wp_enqueue_script(
            'apkafe-admin-form-bs',
            'https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js',
            array('jquery'),
            BINHCHAY_ADMIN_VERSION,
            true
        );

        wp_enqueue_script(
            'apkafe-admin-form-bs',
            'https://code.jquery.com/jquery-3.7.1.slim.js'
        );

        echo '
        <style>
            .ul-post {
                height : 400px !important;
                overflow: scroll;
                overflow-x : hidden;
            }

            .button-submit {
                border: 1px solid black !important;
            }

            #alert-post {
                display: none;
            }
            
            .ul-posted {
                max-height : 400px !important;
                overflow: scroll;
                overflow-x : hidden;
            }
        </style>';
    }

    public function add_menu_page()
    {
        add_menu_page(
            esc_html__('Trending Search', 'apkafe-seo'),
            esc_html__('Trending Search', 'apkafe-seo'),
            'manage_options',
            $this->get_id(),
            array(&$this, 'load_view_trending'),
            'dashicons-feedback'
        );
    }

    public function load_view_trending()
    {
        $listTrending = $this->getUrlTrending();
        $count = 1;
        $urlTrending = '/process-data-trending.php';
        $urlDeleteTrending = '/delete-data-trending.php';
        $classText = "'text-danger'";
        $functionDelete = "'deleteTrending(this.id)'";
        $idText = "id='td-";

        echo '
        <style>
            .form-control {
                width: 49% !important;
            }

            a:hover {
                cursor: pointer;
            }
        </style>';

        echo '<div class="container mt-5">';
        echo '<label for="basic-url" class="form-label">Add trending search</label>
        <div class="form-group mb-3 d-flex justify-content-between">
            <input type="text" class="form-control" placeholder="Enter title" id="title-input">
            <input type="text" class="form-control" placeholder="Enter link" id="link-input">
        </div>
        <div><button class="btn btn-success" id="store-trending" onclick="saveTrending()">Save</button></div>';
        echo '
        <table class="table" id="table-trending">
            <thead>
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">Title</th>
                    <th scope="col">Link</th>
                    <th scope="col">Action</th>
                </tr>
            </thead>
            <tbody>';
        foreach ($listTrending as $trending) {
            echo    '<tr id="td-' . $trending->id . '">
                    <th scope="row">' . $count . '</th>
                    <td>' . $trending->title . '</td>
                    <td>' . $trending->url . '</td>
                    <td><a class="' . 'text-danger' . '" id="' . $trending->id . '" onclick="' . 'deleteTrending(this.id)' . '">x</a></td>
                    </tr>';

            $count++;
        }

        echo    '</tbody>
        </table>';
        echo '</div>';

        echo '<script>
            function saveTrending() {

                let title = jQuery("#title-input").val();
                let url = jQuery("#link-input").val();
                let data = {"title": title, "url": url};

                jQuery.ajax({
                    url: "' . $urlTrending . '",
                    type: "GET",
                    dataType: "json",
                    data: data
                }).done(function(result) {
                    let rowCount = jQuery("#table-trending tr").length;
                    let currentId = 0;
                    if(rowCount == 1) {
                        currentId = 1;
                    } else {
                        let lastId = jQuery("#table-trending tr:last").attr("id");
                        let split = lastId.split("-");
                        currentId = parseInt(split[1]) + 1;
                    }
                    
                    let newTr = "<tr ' . $idText . '" + currentId + "' . "'" . '><th>" + rowCount + "</th><td>" + title + "</td><td>" + url + "</td><td><a id=" + currentId + " class=' . $classText . ' onclick=' . $functionDelete . '>x</a></td></tr>";
                    
                    jQuery("#table-trending tbody").append(newTr);
                });
            }

            function deleteTrending(id) {
                let data = {"id": id};
                jQuery.ajax({
                    url: "' . $urlDeleteTrending . '",
                    type: "GET",
                    dataType: "json",
                    data: data
                }).done(function(result) {
                    let idTr = "#td-" + id;
                    let deleteTr = jQuery(idTr);
                    deleteTr.remove();
                });
            }
        </script>';
    }

    public function getUrlTrending()
    {
        global $wpdb;
        $result = $wpdb->get_results("SELECT * FROM wp_trending_search");

        return $result;
    }
}
