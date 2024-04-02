<?php
$getMeta = get_post_meta(get_the_ID());
$category = get_the_category(get_the_ID());
$related = get_posts(array('category__in' => wp_get_post_categories($post->ID), 'numberposts' => 6, 'post__not_in' => array($post->ID)));

$h1_top_list = '';
$sapo_top_list = '';
$outer_top_list = '';
$review = '';
$summary = '';
$feature = '';
$pros = '';
$cons = '';

if (in_array('h1_top_list', $getMeta)) {
    $h1_top_list = $getMeta['h1_top_list'];
}

if (in_array('sapo_top_list', $getMeta)) {
    $sapo_top_list = $getMeta['sapo_top_list'];
}

if (in_array('outer_top_list', $getMeta)) {
    $outer_top_list = $getMeta['outer_top_list'];
}

if (in_array('review', $getMeta)) {
    $review = $getMeta['review'];
}

if (in_array('summary', $getMeta)) {
    $summary = $getMeta['summary'];
}

if (in_array('feature', $getMeta)) {
    $feature = $getMeta['feature'];
}

if (in_array('pros', $getMeta)) {
    $pros = $getMeta['pros'];
}

if (in_array('cons', $getMeta)) {
    $cons = $getMeta['cons'];
}

?>

<link rel="stylesheet" type="text/css" href="/css/review.css" />
<div class="main_bar">
    <div id="article" class="widget">
        <div class="widget_head">
            <ul id="breadcrumbs" class="bread_crumb">
                <li><a href="<?php echo home_url() ?>">Home</a></li>
                <li><i class="fa fa-angle-double-right"></i></li>
                <li><a href="<?php echo get_category_link($category[0]->term_id) ?>"><?php echo $category[0]->name ?></a></li>
                <li><i class="fa fa-angle-double-right"></i></li>
                <li><a class="active" href="<?php echo get_permalink(get_the_ID()) ?>"><?php echo the_title() ?></a></li>
            </ul>
            <div class="clear"></div>
        </div>
        <div class="pad10">
            <h1 class="main_head ac"><?php echo $h1_top_list ?></h1>

            <div class="sapo-review">
                <p><?php echo $sapo_top_list ?></p>
            </div>

            <div>
                <?php echo $outer_top_list ?>
            </div>
            <div class="clear mb20"></div>

            <div class="main_img_wrap">
                <img id="primaryimage" src="<?php echo get_the_post_thumbnail_url(get_the_ID()) ?>" alt="<?php echo the_title() ?>">
            </div>

            <div class="feature-highlight">
                <?php the_content() ?>
            </div>
            <div class="clear mb20"></div>

            <div class="feature-highlight">
                <?php echo $review ?>
            </div>
            <div class="clear mb20"></div>

            <div class="feature-highlight">
                <?php echo $summary ?>
            </div>
            <div class="clear mb20"></div>

            <div class="feature-highlight">
                <?php echo $feature ?>
            </div>
            <div class="clear mb20"></div>

            <div>
                <table class="table-pros-cons">
                    <tr>
                        <th>Pros</th>
                        <th>Cons</th>
                    </tr>
                    <tr>
                        <td><?php echo $pros ?></td>
                        <td><?php echo $cons ?></td>
                    </tr>
                </table>
            </div>
            <div class="clear mb20"></div>

            <div class="fs-19">
                <div class="accordion">
                    <h4>FAQ</h4>
                    <details>
                        <summary>What are some random questions to ask?</summary>
                        <p>That's exactly the reason we created this random question generator. There are hundreds of random questions to choose from so you're able to find the perfect random question to ask friends, family and people you want to get to know better.</p>
                    </details>
                    <details>
                        <summary>Do you include common questions?</summary>
                        <p>This generator doesn't include most common questions. The thought is that you can come up with common questions on your own so most of the questions in this generator are questions that elicit a bit more information that a typical common question.</p>
                    </details>
                    <details>
                        <summary>Can I use this for 21 questions?</summary>
                        <p>Yes! there are two ways that you can use this question generator depending on what you're after. You can indicate that you want 21 questions generated and you'll instantly have a random list of 21 questions to use. If you want to curate the 21 questions to use, you can spend some time on the generator until you find 21 questions you like, then use those the next time you play the 21 questions game.</p>
                    </details>
                    <details>
                        <summary>Are these questions for girls or for boys?</summary>
                        <p>The questions in this generator are gender neutral and can be used to ask either male of females (or any other gender the person identifies with). These questions were created to elicit interesting and thoughtful answers and aren't specific to a specific type of person.</p>
                    </details>
                </div>
            </div>
            <div class="clear mb20"></div>
        </div>
        <div class="clear mb20"></div>

        <div class="widget">
            <h2 class="widget_head">Recommended for you</h2>
            <div class="main_list_item">
                <?php foreach ($related as $post) { ?>
                    <a class="side_list_item" href="<?php echo get_permalink($post->ID) ?>">
                        <?php echo get_the_post_thumbnail($post->ID) ?>
                        <p class="title"><?php echo get_the_title($post->ID) ?></p>
                        <p class="category"><?php echo get_the_category($post->ID)[0]->name ?></p>
                    </a>
                <?php } ?>
            </div>
        </div>
        <div class="clear"></div>
        <div id="respond">
            <div id="comments_wrap" class="widget">
                <h2 class="widget_head">Leave a Comment</h2>
                <div class="pad10">
                    <form method="post" name="cmnt_form" id="cmnt_form">
                        <div id="cmnt_form_err"></div>
                        <div class="clear mb15"></div>
                        <input type="text" maxlength="50" name="cmnt_name" id="cmnt_name" placeholder="Your Name">
                        <span class="txt_err" id="cmnt_name_err"></span>
                        <div class="clear mb15"></div>
                        <input type="text" maxlength="50" name="cmnt_email" id="cmnt_email" placeholder="Your Email">
                        <span class="txt_err" id="cmnt_email_err"></span>
                        <div class="clear mb15"></div>
                        <div class="ac-textarea">
                            <textarea name="cmnt_text" id="cmnt_text" cols="70" rows="10"></textarea>
                            <span class="txt_err" id="cmnt_text_err"></span>
                        </div>
                        <div class="clear mb15"></div>
                        <div class="ac-submit clearfix">
                            <input type="hidden" name="cmnt_art_id" id="cmnt_art_id" value="cGd1RlgzQUZ4c28zZVhNcmdqbC9nQT09">
                            <input type="hidden" name="cmnt_type" id="cmnt_type" value="0">
                            <input type="hidden" name="cmnt_reply_id" id="cmnt_reply_id" value="0">
                            <input type="hidden" name="cmnt_slang_id" id="cmnt_slang_id" value="">
                            <button onclick="manage_cmnt();" name="submit" type="button">Submit</button>
                        </div>
                    </form>
                    <ol class="comments-tree-list" id="comments_list_items">
                    </ol>
                </div>
            </div>
        </div>
    </div>