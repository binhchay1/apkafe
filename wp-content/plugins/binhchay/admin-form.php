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
            esc_html__('Apkafe for SEO', 'apkafe-seo'),
            esc_html__('Apkafe for SEO', 'apkafe-seo'),
            'manage_options',
            $this->get_id(),
            array(&$this, 'load_view_post'),
            'dashicons-feedback'
        );

        add_submenu_page(
            $this->get_id(),
            esc_html__('Trending Search', 'apkafe-seo'),
            esc_html__('Trending Search', 'apkafe-seo'),
            'manage_options',
            $this->get_id() . '_trending',
            array(&$this, 'load_view_trending')
        );

        add_submenu_page(
            $this->get_id(),
            esc_html__('Top Game App', 'apkafe-seo'),
            esc_html__('Top Game App', 'apkafe-seo'),
            'manage_options',
            $this->get_id() . '_top_game_app',
            array(&$this, 'load_view_top_game_app')
        );
    }

    public function load_view_post()
    {
        $listPost = $this->getListPost();
        $listConfig = $this->getConfigForApkafe();

        $listTextKey = [
            'discover' => 'Discover',
            'popular_game_in_last_24h' => 'Popular game in last 24 hours',
            'popular_app_in_last_24h' => 'Popular app in last 24 hours',
            'popular_games' => 'Popular games',
            'popular_app' => 'Popular app',
            'technology_trick_popular' => 'Technology trick popular',
            'news_popular' => 'News Popular',
            'hot_game' => 'Hot game',
            'hot_app' => 'Hot app',
            'lastest_update_games' => 'Lastest update games',
            'lastest_update_app' => 'Lastest update app',
        ];

        echo '<div class="container mt-5">';
        echo "<div class='alert' role='alert' id='alert-post'></div>";

        foreach ($listConfig as $record) {
            $explode = explode(',', $record->post_id);
            $stringList = '';
            foreach ($explode as $id) {
                if (empty($stringList)) {
                    $stringList = $stringList . get_the_title($id);
                } else {
                    $stringList = $stringList . ', ' . get_the_title($id);
                }
            }
            echo '<p>List ' . $listTextKey[$record->key_post] . ' : ' . $stringList . '</p>';
        }

        echo '
            <div class="input-group mb-3">
            <input type="text" class="form-control" aria-label="Search list" 
            aria-describedby="inputGroup-sizing-default" placeholder="Enter post title" id="search-post-list">
            </div>';
        echo '<ul class="list-group ul-post">';
        foreach ($listPost as $post) {
            echo '<li class="list-group-item item-post">
                <span class="post-title">' . $post->post_title . '</span>
                <span>
                    <select class="form-select" aria-label="Selection" id="' . $post->ID . '">
                        <option value="">Open this select menu</option>
                        <option value="discover">Discover</option>
                        <option value="popular_game_in_last_24h">Popular Game In Last 24 Hours</option>
                        <option value="popular_app_in_last_24h">Popular Apps In Last 24 Hours</option>
                        <option value="popular_games">Popular games</option>
                        <option value="popular_app">Popular app</option>
                        <option value="technology_trick_popular">Technology trick popular</option>
                        <option value="news_popular">News Popular</option>
                        <option value="hot_game">Hot game</option>
                        <option value="hot_app">Hot apps</option>
                        <option value="lastest_update_games">Lastest update games</option>
                        <option value="lastest_update_app">Lastest update app</option>
                    </select>
                </span>
                </li>';
        }
        echo '</ul>';
        echo '
        <form action="' . plugin_dir_url(__DIR__) . 'binhchay/process-data-post.php' . '" method="POST" id="form-post-config">
            <input type="hidden" name="discover" value="" id="discover-form"/>
            <input type="hidden" name="popular_game_in_last_24h" value="" id="popular_game_in_last_24h-form"/>
            <input type="hidden" name="popular_app_in_last_24h" value="" id="popular_app_in_last_24h-form"/>
            <input type="hidden" name="popular_app" value="" id="popular_app-form"/>
            <input type="hidden" name="popular_games" value="" id="popular_games-form"/>
            <input type="hidden" name="technology_trick_popular" value="" id="technology_trick_popular-form"/>
            <input type="hidden" name="news_popular" value="" id="news_popular-form"/>
            <input type="hidden" name="hot_game" value="" id="hot_game-form"/>
            <input type="hidden" name="hot_app" value="" id="hot_app-form"/>
            <input type="hidden" name="lastest_update_games" value="" id="lastest_update_games-form"/>
            <input type="hidden" name="lastest_update_app" value="" id="lastest_update_app-form"/>
        </form>';
        echo '<button class="btn button-submit" id="save-posts-config" onclick="saveConfigSave()">Save</button>';
        echo '</div>';

        echo "
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const queryString = window.location.search;
                const urlParams = new URLSearchParams(queryString);
                const status = urlParams.get('status')

                if(status != null) {
                    let alert = document.getElementById('alert-post');
                    if(status == 'saved') {
                        alert.classList.add('alert-success');
                        alert.innerHTML = 'Change is saved';
                    } else {
                        alert.classList.add('alert-danger');
                        alert.innerHTML = 'Change is not saved';
                    }

                    alert.style.display = 'block';
                }
            });
            var input = document.getElementById('search-post-list');
            var lis = document.getElementsByClassName('item-post');
            var listResult = [];
            var listConfigSave = {
                'discover' : [],
                'popular_game_in_last_24h' : [],
                'popular_app_in_last_24h' : [],
                'popular_app' : [],
                'popular_games' : [],
                'technology_trick_popular' : [],
                'news_popular' : [],
                'hot_game' : [],
                'hot_app' : [],
                'lastest_update_games' : [],
                'lastest_update_app' : []
            };
            var listConfigGet = " . json_encode($listConfig) . "

            for (var y = 0; y < listConfigGet.length; y++) {
                if(listConfigGet[y].post_id != '' && listConfigGet[y].post_id != null) {
                    var split = listConfigGet[y].post_id.split(',');
                    for(var a = 0; a < split.length; a++) {
                        let select = document.getElementById(split[a]);
                        select.value = listConfigGet[y].key_post;
                    }
                }
            }

            input.onkeyup = function () {
            var filter = input.value.toUpperCase();

            for (var i = 0; i < lis.length; i++) {
                var text = lis[i].getElementsByClassName('post-title')[0].innerHTML;
                if (text.toUpperCase().indexOf(filter) == 0) 
                    lis[i].style.display = 'list-item';
                else
                    lis[i].style.display = 'none';
                }
            }

            function saveConfigSave() {
                for (var i = 0; i < lis.length; i++) {
                    let result = getSelectValues(lis[i].getElementsByTagName('select')[0]);
                    listResult.push(result);
                }
    
                for (var k = 0; k < listResult.length; k++) {
                    if(listResult[k][0] == 'discover') {
                        listConfigSave.discover.push(listResult[k][1]);
                    }

                    if(listResult[k][0] == 'popular_game_in_last_24h') {
                        listConfigSave.popular_game_in_last_24h.push(listResult[k][1]);
                    }

                    if(listResult[k][0] == 'popular_app_in_last_24h') {
                        listConfigSave.popular_app_in_last_24h.push(listResult[k][1]);
                    }

                    if(listResult[k][0] == 'popular_app') {
                        listConfigSave.popular_app.push(listResult[k][1]);
                    }

                    if(listResult[k][0] == 'popular_games') {
                        listConfigSave.popular_games.push(listResult[k][1]);
                    }

                    if(listResult[k][0] == 'technology_trick_popular') {
                        listConfigSave.technology_trick_popular.push(listResult[k][1]);
                    }

                    if(listResult[k][0] == 'news_popular') {
                        listConfigSave.news_popular.push(listResult[k][1]);
                    }

                    if(listResult[k][0] == 'hot_game') {
                        listConfigSave.hot_game.push(listResult[k][1]);
                    }

                    if(listResult[k][0] == 'hot_app') {
                        listConfigSave.hot_app.push(listResult[k][1]);
                    }

                    if(listResult[k][0] == 'lastest_update_games') {
                        listConfigSave.lastest_update_games.push(listResult[k][1]);
                    }

                    if(listResult[k][0] == 'lastest_update_app') {
                        listConfigSave.lastest_update_app.push(listResult[k][1]);
                    }
                }

                document.getElementById('discover-form').value = listConfigSave.discover;
                document.getElementById('popular_game_in_last_24h-form').value = listConfigSave.popular_game_in_last_24h;
                document.getElementById('popular_app_in_last_24h-form').value = listConfigSave.popular_app_in_last_24h;
                document.getElementById('popular_app-form').value = listConfigSave.popular_app;
                document.getElementById('popular_games-form').value = listConfigSave.popular_games;
                document.getElementById('technology_trick_popular-form').value = listConfigSave.technology_trick_popular;
                document.getElementById('news_popular-form').value = listConfigSave.news_popular;
                document.getElementById('hot_game-form').value = listConfigSave.hot_game;
                document.getElementById('hot_app-form').value = listConfigSave.hot_app;
                document.getElementById('lastest_update_games-form').value = listConfigSave.lastest_update_games;
                document.getElementById('lastest_update_app-form').value = listConfigSave.lastest_update_app;

                document.getElementById('form-post-config').submit();
            }

            function getSelectValues(select) {
                var result = [];
                var options = select && select.options;
                var opt;
              
                for (var i=0, iLen=options.length; i<iLen; i++) {
                  opt = options[i];
              
                  if (opt.selected) {
                    if(opt.value !== '') {
                        result.push(opt.value);
                        result.push(select.id);
                    }
                  }
                }

                return result;
            }
        </script>";
    }

    public function load_view_trending()
    {
        $listTrending = $this->getUrlTrending();
        $count = 1;
        $urlTrending = plugin_dir_url(__DIR__) . 'binhchay/process-data-trending.php';
        $urlDeleteTrending = plugin_dir_url(__DIR__) . 'binhchay/delete-data-trending.php';
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

    public function load_view_top_game_app()
    {
        $listProduct = $this->getListProducts();
        $listTopGame = $this->getListTopGames();
        $urlTopGame = plugin_dir_url(__DIR__) . 'binhchay/process-data-top-game.php';
        $urlTopGameDelete = plugin_dir_url(__DIR__) . 'binhchay/delete-data-top-game.php';
        $classLi = '"list-group-item item-post d-flex justify-content-between"';
        $classSpan = '"post-title"';
        $classButtonDelete = '"btn btn-danger ml-4"';
        $classButtonAdd = '"btn btn-success ml-4"';
        $funcDelete = "deleteTopGame(' + idPost + ', ' + namePost + ')";
        $funcAdd = "addTopGame(' + idPost + ', ' + namePost + ')";
        $arrIDPosted = [];

        echo '<div class="container mt-5">';
        echo "<div class='alert' role='alert' id='alert-post'></div>";

        echo '<ul class="list-group ul-posted">';
        foreach ($listTopGame as $top) {
            $arrIDPosted[] = $top->post_id;
            $idButtonDelete = "'" . $top->post_id .  "', '" . get_the_title($top->post_id) . "'";
            echo '<li class="list-group-item item-post d-flex justify-content-between" id="posted-' . $top->post_id . '">
                <span class="post-title">' . get_the_title($top->post_id) . '</span>
                <button class="btn btn-danger ml-4" onclick="deleteTopGame(' . $idButtonDelete . ')">Delete</button>
                </li>';
        }
        echo '</ul>';

        echo '
            <div class="input-group mb-3 mt-5">
            <input type="text" class="form-control" aria-label="Search list" 
            aria-describedby="inputGroup-sizing-default" placeholder="Enter post title" id="search-post-list">
            </div>';
        echo '<ul class="list-group ul-post">';
        foreach ($listProduct as $product) {
            if (!in_array($product->ID, $arrIDPosted)) {
                $idButtonAdd = "'" . $product->ID .  "', '" . $product->post_title . "'";
                echo '<li class="list-group-item item-post d-flex justify-content-between" id=' . $product->ID . '>
                <span class="post-title">' . $product->post_title . '</span><button class="btn btn-success ml-4 button-add-post" onclick="addTopGame(' . $idButtonAdd . ')">Add</button></li>';
            }
        }
        echo '</ul>';
        echo '</div>';

        echo "<script>
        var input = document.getElementById('search-post-list');
        var lis = document.getElementsByClassName('item-post');

        if (jQuery('.ul-posted li').length >= 6) {
            jQuery('.button-add-post').prop('disabled', true);
        }

        input.onkeyup = function () {
            var filter = input.value.toUpperCase();

            for (var i = 0; i < lis.length; i++) {
                var text = lis[i].getElementsByClassName('post-title')[0].innerHTML;
                if (text.toUpperCase().indexOf(filter) == 0) 
                    lis[i].style.display = 'list-item';
                else
                    lis[i].style.display = 'none';
                }
            }

        function addTopGame(idPost, namePost) {
            let data = {id: idPost};
            jQuery.ajax({
                url: '" . $urlTopGame . "',
                type: 'GET',
                dataType: 'json',
                data: data
            }).done(function(result) {
                let liPosted = '<li class=" . $classLi . "><span class=" . $classSpan . ">' + namePost + '</span><button class=" . $classButtonDelete . " onclick=" . $funcDelete . ">Delete</button></li>';
                jQuery('.ul-posted').append(liPosted);
                jQuery('.ul-post #' + idPost).remove();
                if (jQuery('.ul-posted li').length >= 6) {
                    jQuery('.button-add-post').prop('disabled', true);
                }
            });
        }

        function deleteTopGame(idPost, namePost) {
            let data = {id: idPost};
            jQuery.ajax({
                url: '" . $urlTopGameDelete . "',
                type: 'GET',
                dataType: 'json',
                data: data
            }).done(function(result) {
                let liPost = '<li class=" . $classLi . "><span class=" . $classSpan . ">' + namePost + '</span><button class=" . $classButtonAdd . " onclick=" . $funcAdd . ">Add</button></li>';
                jQuery('.ul-post').append(liPost);
                jQuery('.ul-posted #posted-' + idPost).remove();
                if (jQuery('.ul-posted li').length < 6) {
                    jQuery('.button-add-post').prop('disabled', false);
                }
            });
        }
        </script>";
        // var_dump($listProduct);
    }

    public function getListPost()
    {
        $args = array(
            'post_type' => 'post',
            'orderby'    => 'ID',
            'post_status' => 'publish',
            'order'    => 'DESC',
            'posts_per_page' => -1
        );
        $result = new WP_Query($args);

        return $result->posts;
    }

    public function getConfigForApkafe()
    {
        global $wpdb;
        $result = $wpdb->get_results("SELECT * FROM wp_binhchay");

        return $result;
    }

    public function getUrlTrending()
    {
        global $wpdb;
        $result = $wpdb->get_results("SELECT * FROM wp_trending_search");

        return $result;
    }

    public function getListProducts()
    {
        $args = array(
            'post_type' => 'product',
            'orderby'    => 'post_date',
            'post_status' => 'publish',
            'order'    => 'DESC',
            'posts_per_page' => -1
        );
        $result = new WP_Query($args);

        return $result->posts;
    }

    public function getListTopGames()
    {
        global $wpdb;
        $result = $wpdb->get_results("SELECT * FROM wp_top_games");

        return $result;
    }
}
