<?php

function create_meta_boxes_review_slide()
{
    add_meta_box('review-slide-meta-boxes', 'Review Slide', 'review_slide_meta_boxes_callback', 'post');
}
add_action('add_meta_boxes', 'create_meta_boxes_review_slide');

function review_slide_meta_boxes_callback($post)
{
    $image_ids = ($image_ids = get_post_meta($post->ID, '_review_slide', true)) ? $image_ids : array();

    wp_nonce_field('review_slide_meta_boxes_save', 'review_slide_meta_nonce');
    wp_enqueue_script('jquery-ui', 'https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.13.2/jquery-ui.min.js');
    wp_enqueue_script('review-slide-js', plugins_url('review-slide/asset/js/review-slide.js'));
    wp_enqueue_style('review-slide-css', plugins_url('review-slide/asset/css/review-slide.css'));

    echo '<button type="button" class="btn btn-create-review-slide" id="btn-create-review-slide">Create slide</button>';
    echo '<div id="area-review-slide">';

    if (!empty($image_ids)) {
        $arrImage = json_decode($image_ids, true);

        foreach ($arrImage as $title => $strImage) {
            $listImage = explode(',', $strImage);
            echo '<div class="group-review-slide">';
            echo '<label>Title</label>
            <input type="text" name="title_slide[]" value="' . $title . '"/>';
            echo '<ul class="binhchay-gallery">';
            foreach ($listImage as $key => $id) {
                $url = wp_get_attachment_image_url($id, array(80, 80));
                if ($url) {
                    echo '<li class="binhchay-li-item" data-id="' . $id . '">
                        <img src="' . $url . '" /><br>
                        <button type="button" class="btn binhchay-gallery-remove" onclick="removeMediaHandler(jQuery(this))">Delete</button>
                    </li>';
                } else {
                    unset($listImage[$key]);
                }
            }
            echo '</ul>
            <input type="hidden" name="review_slide[]" value="' . implode(',', $listImage) . '" />
            <button type="button" class="button binhchay-upload-button" onclick="addMediaHandle(jQuery(this))">Add Images</button>
            <button type="button" class="button binhchay-delete-slide" onclick="deleteSlideHandle(jQuery(this))">Delete slide</button>';
            echo "</div><script>
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
        }
    }

    echo '</div>';
}

function review_slide_meta_boxes_save($post_id)
{
    if (isset($_POST['review_slide_meta_nonce'])) {
        $_nonce = $_POST['review_slide_meta_nonce'];
        if (!isset($_nonce)) {
            return;
        }

        if (!wp_verify_nonce($_nonce, 'review_slide_meta_boxes_save')) {
            return;
        }

        $listTitle = $_POST['title_slide'];
        $listImage = $_POST['review_slide'];
        $arrInput = [];

        foreach ($listTitle as $key => $value) {
            $arrInput[$value] = $listImage[$key];
        }

        $dataReview = json_encode($arrInput);
        update_post_meta($post_id, '_review_slide', $dataReview);
    }
}
add_action('save_post', 'review_slide_meta_boxes_save');

function review_slide_shortcode()
{
    global $post;
    $get_post_meta = get_post_meta($post->ID, '_review_slide');
    $review_slide = json_decode($get_post_meta[0], true);
    $odd = 1;

    if (!empty($review_slide)) {
        $content = '';
        $content .= '<section class="w-11/12 lg:w-10/12 mx-auto mb-10 lg:mb-16 relative">';
        $content .= '<h2 class="sm:text-3xl text-2xl font-bold flex justify-center items-center mb-6 lg:mb-8"><span>Reviews</span></h2>
                <p class="italic text-witty-black-700 text-center mx-auto mb-4 lg:mb-8 prose">
                The following reviews were gathered from other, a business software review platform where users can rate and review
                different software products.
                </p>';
        foreach ($review_slide as $title => $strImg) {
            $listImg = explode(',', $strImg);
            $countImg = count($listImg);
            $width = $countImg * 7;
            $setIdKeyFrames = str_replace(' ', '-', strtolower($title));

            if (!isset($countImg) || $countImg == 0) {
                continue;
            }

            if ($odd % 2 == 0) {
                $classes = 'img-ticker-reverse';
            } else {
                $classes = 'img-ticker';
            }

            $content .= '<style>@keyframes ticker-kf-' . $setIdKeyFrames . ' {
                0% {
                    transform: translate3d(0, 0, 0);
                }
    
                100% {
                    transform: translate3d(-' . $width .  'rem, 0, 0);
                }
            }
    
            .img-ticker {
                animation: ticker-kf-' . $setIdKeyFrames . ' 75s linear infinite;
            }
    
            .img-ticker-reverse {
                animation: ticker-kf-' . $setIdKeyFrames . ' 75s linear infinite;
                animation-direction: reverse;
            }</style>';
            $content .= '<h2 class="text-2xl font-bold text-center mb-4">' . $title . '</h2>';
            $content .= '<div class="overflow-hidden w-full relative">';
            $content .= '<div class="w-10 md:w-40 h-full left-0 top-0 absolute z-20 bg-gradient-to-r from-white to-transparent"></div>';
            $content .= '<div class="flex ' . $classes . ' -mx-4">';

            foreach ($listImg as $id) {
                $url = wp_get_attachment_image_url($id, array(150, 150));
                $content .= '<div class="bg-white p-5 rounded-md border border-gray-300 mx-4 self-start flex-none">';
                $content .= '<img src="' . $url . '" class="item-img-short-review-slide">';
                $content .= '</div>';
            }

            foreach ($listImg as $id) {
                $url = wp_get_attachment_image_url($id, array(150, 150));
                $content .= '<div class="bg-white p-5 rounded-md border border-gray-300 mx-4 self-start flex-none">';
                $content .= '<img src="' . $url . '" class="item-img-short-review-slide">';
                $content .= '</div>';
            }

            $content .= '</div>';
            $content .= '<div class="w-10 md:w-40 h-full right-0 top-0 absolute z-20 bg-gradient-to-r from-transparent to-white"></div>';
            $content .= '</div>';

            $odd++;
        }
        $content .= '</section>';

        return $content;
    }
}

add_shortcode('review-slide-shortcode', 'review_slide_shortcode');

function review_slide_shortcode_style()
{
    global $post;
    if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'review-slide-shortcode')) {
        wp_enqueue_style('review-slide-css', plugins_url('review-slide/asset/css/alpine.css'));
    }
}
add_action('wp_enqueue_scripts', 'review_slide_shortcode_style');

remove_action('shutdown', 'wp_ob_end_flush_all', 1);
add_action('shutdown', function () {
    while (@ob_end_flush());
});
