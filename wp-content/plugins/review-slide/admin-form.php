<?php
class Admin_Form
{
    const ID = 'config-seo';

    public function init()
    {
        add_action('admin_menu', array($this, 'add_menu_pages'), 1);
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
        add_action('wp_ajax_save_category', array($this, 'save_category'));
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

        wp_enqueue_style('config-admin-form-bs', 'https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css', REVIEW_SLIDE_ADMIN_VERSION);
        wp_enqueue_script(
            'config-admin-form-bs',
            'https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js',
            array('jquery'),
            REVIEW_SLIDE_ADMIN_VERSION,
            true
        );

        wp_enqueue_script(
            'config-admin-form-bs',
            'https://code.jquery.com/jquery-3.7.1.slim.js'
        );

        echo '
        <style>
            .button-submit {
                border: 1px solid black !important;
            }

            .post-title {
                font-weight: bold !important;
                font-size: 19px !important;
            }

            #alert-post {
                display: none;
            }
        </style>';
    }

    function add_menu_pages()
    {
        add_menu_page('Review Slide', 'Review Slide', 10, $this->get_id() . '_general', array(&$this, 'load_view_general'), plugins_url('review-slide/asset/images/icon.png'));
    }

    public function load_view_general()
    {
        $nonce = wp_create_nonce("get_game_nonce");
        $link = admin_url('admin-ajax.php');

        echo '<script>
        jQuery(document).ready( function() {
            jQuery("#save-general").on("click", function(e) {
                e.preventDefault();
                dataGeneral.h1 = jQuery("#h1-home-page").val();
        
                jQuery.post("' . $link . '", 
                    {
                        "action": "save_general",
                        "data": dataGeneral,
                        "nonce": "' . $nonce . '"
                    }, 
                    function(response) {
                        if(response == "failed") {
                            let alert = document.getElementById("alert-post");
                            if(alert.classList.contains("alert-success")) {
                                alert.classList.remove("alert-success");
                            }
                            alert.classList.add("alert-danger");
                            alert.style.display = "block";
                            alert.innerHTML = "Save failed! H1 or Description is empty.";
                        } else {
                            let alert = document.getElementById("alert-post");
                            if(alert.classList.contains("alert-danger")) {
                                alert.classList.remove("alert-danger");
                            }
                            alert.classList.add("alert-success");
                            alert.style.display = "block";
                            alert.innerHTML = "Save successfully!";
                        }
                    }
                );
            });
        });
        </script>';
    }

    public function getCustomCategory()
    {
        global $wpdb;
        $result = $wpdb->get_results("SELECT * FROM wp_category_custom");

        return $result;
    }

    public function save_general()
    {
        if (!wp_verify_nonce($_REQUEST['nonce'], "get_game_nonce")) {
            exit("Please don't fucking hack this API");
        }

        $data = $_REQUEST['data'];
        if (empty($data['h1']) || empty($data['description'])) {
            echo 'failed';
            die;
        }

        $h1Homepage = get_option('h1_homepage');
        $descriptionHomepage = get_option('description_homepage');

        if ($h1Homepage == false) {
            add_option('h1_homepage', $data['h1']);
        } else {
            update_option('h1_homepage', $data['h1']);
        }

        if ($descriptionHomepage == false) {
            add_option('description_homepage', $data['description']);
        } else {
            update_option('description_homepage', $data['description']);
        }

        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            echo 'success';
        } else {
            header("Location: " . $_SERVER["HTTP_REFERER"]);
        }
    }

    public function save_top_category()
    {
        if (!wp_verify_nonce($_REQUEST['nonce'], "get_game_nonce")) {
            exit("Please don't fucking hack this API");
        }

        $data = $_REQUEST['data'];

        if (empty($data)) {
            echo 'failed';
            die;
        }

        $topCategory = get_option('top_category_homepage');
        if ($topCategory == false) {
            $data = json_encode($data);
            add_option('top_category_homepage', $data);
        } else {
            $arrTopCategory = json_decode(json_encode(json_decode($topCategory)), true);
            $merge = array_merge($arrTopCategory, $data);
            update_option('top_category_homepage', json_encode($merge));
        }

        echo 'success';
    }

    public function search_post()
    {
        if (!wp_verify_nonce($_REQUEST['nonce'], "get_game_nonce")) {
            exit("Please don't fucking hack this API");
        }

        $data = $_REQUEST['data'];
        global $wpdb;

        if (empty($data['id']) || empty($data['url'])) {
            echo 'failed';
            die;
        }

        $getPost = url_to_postid($data['url']);
        if ($getPost == 0) {
            echo 'notfound';
            die;
        } else {
            $queryGet = "SELECT * FROM " . $wpdb->prefix . 'top_game_category WHERE category_id = "' . $data['id'] . '"';
            $result = $wpdb->get_results($queryGet);
            if (empty($result)) {
                $dataResponse = [
                    'url' => $data['url'],
                    'id' => $getPost
                ];
            } else {
                $game = $result[0]->game;
                $explode = explode(',', $game);
                if (in_array($getPost, $explode)) {
                    echo 'exists';
                    die;
                } else {
                    $dataResponse = [
                        'url' => $data['url'],
                        'id' => $getPost
                    ];
                }
            }

            $response = json_encode($dataResponse);
            echo $response;
            die;
        }
    }

    public function add_top_game()
    {
        if (!wp_verify_nonce($_REQUEST['nonce'], "get_game_nonce")) {
            exit("Please don't fucking hack this API");
        }

        global $wpdb;
        $data = $_REQUEST['data'];

        if (empty($data)) {
            echo 'failed';
            die;
        }

        $queryGet = "SELECT * FROM " . $wpdb->prefix . 'top_game_category WHERE category_id = "' . $data['category_id'] . '"';
        $result = $wpdb->get_results($queryGet);
        if (empty($result)) {
            $dataSave = $data['id'];
            $query = 'INSERT INTO ' . $wpdb->prefix . 'top_game_category (`category_id`, `game`) VALUES ';
            $query .= ' ("' . $data['category_id'] . '", "' . $dataSave . '")';
        } else {
            $game = $result[0]->game;
            if($game == '') {
                $explode = array();
                $explode[] = $data['id'];
            } else {
                $explode = explode(',', $game);
                $explode[] = $data['id'];
            }
            
            $dataSave = implode(',', $explode);
            $query = 'UPDATE ' . $wpdb->prefix . 'top_game_category';
            $query .= ' SET `category_id` = "' . $data['category_id'] . '", `game` = "' . $dataSave . '" WHERE category_id = "' . $data['category_id'] . '"';
        }

        $wpdb->query($query);

        echo 'success';
        die;
    }

    public function delete_top_game()
    {
        if (!wp_verify_nonce($_REQUEST['nonce'], "get_game_nonce")) {
            exit("Please don't fucking hack this API");
        }

        global $wpdb;
        $data = $_REQUEST['data'];

        if (empty($data)) {
            echo 'failed';
            die;
        }

        $queryGet = "SELECT * FROM " . $wpdb->prefix . 'top_game_category WHERE category_id = "' . $data['category_id'] . '"';
        $result = $wpdb->get_results($queryGet);
        $game = $result[0]->game;
        $explode = explode(',', $game);
        $newGame = array();
        foreach ($explode as $record) {
            if ($record == $data['id']) {
                continue;
            }
            $newGame[] = $record;
        }

        $dataSave = implode(',', $newGame);
        $query = 'UPDATE ' . $wpdb->prefix . 'top_game_category';
        $query .= ' SET `category_id` = "' . $data['category_id'] . '", `game` = "' . $dataSave . '" WHERE category_id = "' . $data['category_id'] . '"';
        $wpdb->query($query);

        echo 'success';
        die;
    }

    public function slugify($text, string $divider = '-')
    {
        $text = preg_replace('~[^\pL\d]+~u', $divider, $text);
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        $text = preg_replace('~[^-\w]+~', '', $text);
        $text = trim($text, $divider);
        $text = preg_replace('~-+~', $divider, $text);
        $text = strtolower($text);

        if (empty($text)) {
            return 'n-a';
        }

        return $text;
    }
}
