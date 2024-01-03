<?php

if (!defined('ABSPATH')) {
    exit('You\'re not allowed to see this page');
} // Exit if accessed directly

/**
 * Manage Rich Snippets
 *
 * This class hook to yasr_filter_existing_schema, get the post meta
 * yasr_schema_additional_fields, and depending of the itemType selected
 * returns the schema info
 *
 * @author Dario Curvino <dudo>
 *
 */
class YasrRichSnippets {

    public function addHooks() {
        //Low priority to be sure that shortcodes has run
        add_filter('the_content',                 array($this, 'addSchema'), 99);

        //There are some cases where the_content doesn't run.
        //So, try to also add schema into the footer
        //https://wordpress.org/support/topic/please-add-json-structured-data-output-to-wp_head/
        //https://wordpress.org/support/topic/structured-data-not-showing/
        add_action('wp_footer',                   array($this, 'addSchema'));

        add_filter('yasr_filter_schema_title',    array($this, 'filter_title'));
        add_filter('yasr_filter_existing_schema', array($this, 'additional_schema'), 10, 2);
    }

    /**
     * @param $content
     *
     * @return string|void
     */
    public function addSchema($content) {
        $caller = current_filter();

        //assure the $content is empty
        if($caller === 'wp_footer') {
            $content = '';
        }

        //do the checks
        if($this->mustReturn($caller) === true) {
            return $content;
        }

        $post_id = get_the_ID();

        $overall_rating = false;
        $visitor_votes  = false;

        //check if ov_rating shortcode has run
        if (defined('YASR_OV_ATTRIBUTES')) {
            $ov_attributes  = json_decode(YASR_OV_ATTRIBUTES, true);
            $overall_rating = $ov_attributes;
        }

        //check if vv has run
        if(defined('YASR_VV_ATTRIBUTES')) {
            $vv_attributes = json_decode(YASR_VV_ATTRIBUTES, true);
            $visitor_votes  = $vv_attributes;
        }

        $script_type     = '<script type="application/ld+json" class="yasr-schema-graph">';
        $end_script_type = '</script>';

        $item_type_for_post = YasrDB::getItemType();

        /**
         * Use this hook to write your custom microdata from scratch
         * @param string $item_type_for_post the itemType selected for the post
         */
        $filtered_schema = apply_filters('yasr_filter_schema_jsonld', $item_type_for_post);

        //check here if filter has run and return the new schema
        if ($filtered_schema !== $item_type_for_post) {
            return $script_type . $filtered_schema . $end_script_type;
        }

        //YASR adds microdata only if is_singular() && is_main_query()
        if (is_singular() && is_main_query()) {
            $rich_snippet = $this->returnRichSnippets($post_id, $item_type_for_post, $content, $overall_rating, $visitor_votes);

            //If $rich snippet here is not false return microdata
            if($rich_snippet !== false) {
                //declare YASR_SCHEMA_RETURNED here
                if(!defined('YASR_SCHEMA_RETURNED')) {
                    define('YASR_SCHEMA_RETURNED', true);
                }

                $ld_json = $content . $script_type . json_encode($rich_snippet) . $end_script_type;

                //just do the echo and return if this is called from footer
                if ($caller === 'wp_footer') {
                    echo $ld_json;
                    return;
                }
                //return if this is called from the_content
                return $ld_json;

            }
        }

        return $content;
    } //End function

    /**
     * Return true if $content must be returned
     *
     * @author Dario Curvino <@dudo>
     *
     * @since 3.3.9
     *
     * @param $caller
     *
     * @return bool
     */
    public function mustReturn($caller) {
        //rich snippets already returned
        if(defined('YASR_SCHEMA_RETURNED')) {
            return true;
        }

        //if both shortcodes ov_rating and visitor votes didn't run, return
        if (!defined('YASR_OV_ATTRIBUTES') && !defined('YASR_VV_ATTRIBUTES')
            && !defined('YASR_PRO_UR_COMMENT_RICH_SNIPPET')) {
            return true;
        }

        //Add buddypress compatibility
        //If this is a page, return $content without adding schema.
        if (function_exists('bp_is_active') && is_page()) {
            return true;
        }

        if($caller === 'wp_footer') {
            if (is_404() || (!is_singular() && is_main_query())) {
                return true;
            }
        } else {
            if (is_404() || did_action('get_footer') || (!is_singular() && is_main_query())) {
                return true;
            }
        }

        return false;
    }

    /**
     * Return rich snippets, or false if both aggregateRating and Review are empty after the last filter
     *
     * @author Dario Curvino <@dudo>
     * @since 2.8.0
     * @param $post_id
     * @param $review_choosen
     * @param $content
     * @param $overall_rating
     * @param $visitor_votes
     *
     * @return array|bool
     */
    public function returnRichSnippets($post_id, $review_choosen, $content, $overall_rating, $visitor_votes) {
        $rich_snippet_data = $this->richSnippetsGetData($post_id);
        $cleaned_content   = wp_strip_all_tags(strip_shortcodes($content));

        $rich_snippet = array();

        //Add everywhere except for blogPosting
        if ($review_choosen !== 'BlogPosting') {
            $review = $this->richSnippetsReview($rich_snippet_data, $cleaned_content, $overall_rating);
            if($review) {
                $rich_snippet['Review'] = $review;
            }

            //if both are included, google will index AggregateRating instead of Review.
            //So, if post is selected as review, exclude AggregateRating
            if($rich_snippet_data['is_post_a_review'] !== 'yes') {
                $aggregate_rating = $this->richSnippetsAggregateRating($visitor_votes);
                if($aggregate_rating) {
                    $rich_snippet['aggregateRating'] = $this->richSnippetsAggregateRating($visitor_votes);
                }
            }
        }

        //Use this hook to manage itemTypes
        //if doesn't exists a filter for yasr_filter_existing_schema, put $rich_snippet into $more_rich_snippet
        $filtered_rich_snippet = apply_filters('yasr_filter_existing_schema', $rich_snippet, $rich_snippet_data);

        if ($filtered_rich_snippet !== $rich_snippet && is_array($filtered_rich_snippet)) {
            $rich_snippet = $filtered_rich_snippet;
        }

        //if review and aggregateRating are still false after the filter, do not return rich snippets
        if((!isset($rich_snippet['Review'])) && !isset($rich_snippet['aggregateRating'])) {
            return false;
        }

        return array_merge(
            $this->richSnippetsCommon($review_choosen, $rich_snippet_data, $cleaned_content),
            $rich_snippet
        );
    }

    /**
     * Returns all data that is not in the post meta (author name, date, etc)
     *
     * @author Dario Curvino <@dudo>
     * @since 2.8.0
     *
     * @param $post_id
     *
     * @return array
     */
    public function richSnippetsGetData ($post_id) {
        $data_to_return = array();

        $data_to_return['author']           = get_the_author();
        //use this hook to change the itemType name
        $data_to_return['review_name']      = wp_strip_all_tags(apply_filters('yasr_filter_schema_title', $post_id));

        $data_to_return['date']             = get_the_date('c');
        $data_to_return['date_modified']    = get_the_modified_date('c');
        $data_to_return['is_post_a_review'] = get_post_meta($post_id, 'yasr_post_is_review', true);

        $data_to_return = array_merge($data_to_return, $this->imagesAttributes());

        $publisher_image_index = 'logo';
        if (YASR_PUBLISHER_TYPE === 'Person') {
            $publisher_image_index = 'image';
        }

        $data_to_return['publisher'] = array(
            '@type'                => YASR_PUBLISHER_TYPE,
            'name'                 => wp_strip_all_tags(YASR_PUBLISHER_NAME),

            $publisher_image_index => array(
                '@type'  => 'ImageObject',
                'url'    => $data_to_return['logo_image_url'],
                'width'  => $data_to_return['logo_image_size'][0],
                'height' => $data_to_return['logo_image_size'][1]
            ),
        );

        return $data_to_return;
    }

    /**
     * Helper method to returns images attributes of both publisher and featured image
     *
     * If a post has not featured image, publisher logo will be used instead
     *
     * @author Dario Curvino <@dudo>
     * @since  3.1.1
     * @return array
     */
    private function imagesAttributes() {
        $logo_image_url = '';
        $post_image_url = $logo_image_url; //this will be overwritten if it has_post_thumbnail is true

        //default values
        $post_image_size[0] = 0;
        $post_image_size[1] = 0;
        $logo_image_size[0] = 0;
        $logo_image_size[1] = 0;

        if (YASR_PUBLISHER_LOGO) {
            $logo_image_url = YASR_PUBLISHER_LOGO;
            $post_image_url = $logo_image_url; //this will be overwritten if it has_post_thumbnail is true

            $post_image_size = yasr_getimagesize(YASR_PUBLISHER_LOGO);
            $logo_image_size = yasr_getimagesize(YASR_PUBLISHER_LOGO);
        }

        //if exists featured image get the url and overwrite the variable
        if (has_post_thumbnail()) {
            $post_image_url          = wp_get_attachment_url(get_post_thumbnail_id());
            $post_image_size         = yasr_getimagesize($post_image_url);
        }

        return array (
            'post_image_size' => $post_image_size,
            'post_image_url'  => $post_image_url,
            'logo_image_size' => $logo_image_size,
            'logo_image_url'  => $logo_image_url
        );
    }

    /**
     * Returns the 'Review' type
     *
     * @author Dario Curvino <@dudo>
     * @since 2.8.0
     * @param $rich_snippet_data
     * @param $cleaned_content
     * @param $overall_rating
     *
     * @return array|false
     */
    private function richSnippetsReview($rich_snippet_data, $cleaned_content, $overall_rating) {
        if ($overall_rating) {
            $rich_snippet = array(
                '@type'         => 'Review',
                'name'          => $rich_snippet_data['review_name'],
                'reviewBody'    => $cleaned_content,
                'author'        => array(
                    '@type' => 'Person',
                    'name'  => $rich_snippet_data['author']
                ),
                'datePublished' => $rich_snippet_data['date'],
                'dateModified'  => $rich_snippet_data['date_modified'],
                'reviewRating'  => array(
                    '@type'       => 'Rating',
                    'ratingValue' => $overall_rating,
                    'bestRating'  => 5,
                    'worstRating' => 1
                ),
            );
            $rich_snippet['publisher'] = $rich_snippet_data['publisher'];

            return $rich_snippet;
        }
        return false;
    }

    /**
     * @author Dario Curvino <@dudo>
     * @since 2.8.0
     *
     * @param $visitor_votes
     *
     * @return array|false
     */
    private function richSnippetsAggregateRating ($visitor_votes) {
        if ($visitor_votes && $visitor_votes['sum_votes'] !== 0 && $visitor_votes['number_of_votes'] !== 0) {
            $average_rating = $visitor_votes['sum_votes'] / $visitor_votes['number_of_votes'];
            $average_rating = round($average_rating, 1);

            return array(
                '@type'       => 'AggregateRating',
                'ratingValue' => $average_rating,
                'ratingCount' => $visitor_votes['number_of_votes'],
                'bestRating'  => 5,
                'worstRating' => 1,
            );
        }
        return false;
    }

    /**
     * @author Dario Curvino <@dudo>
     * @since  2.9.7
     * @return array
     */
    private function richSnippetsCommon ($review_choosen, $rich_snippet_data, $cleaned_content) {
        $rich_snippet = array();
        $rich_snippet['@context']    = 'https://schema.org/';
        $rich_snippet['@type']       = $review_choosen;
        $rich_snippet['name']        = $rich_snippet_data['review_name'];

        $rich_snippet['description'] = wp_trim_words($cleaned_content, 55, '...');

        $rich_snippet['image'] = array(
            '@type'  => 'ImageObject',
            'url'    => $rich_snippet_data['post_image_url'],
            'width'  => $rich_snippet_data['post_image_size'][0],
            'height' => $rich_snippet_data['post_image_size'][1]
        );

        return $rich_snippet;
    }

    /**
     * @author Dario Curvino <@dudo>
     * @param $rich_snippet      array contains common data for all rich snippets
     * @param $rich_snippet_data array contains all data that is not post_meta (author name, date, etc.)
     *
     * @return array
     */
    public function additional_schema($rich_snippet, $rich_snippet_data) {
        $post_meta = $this->post_meta();

        //avoid undefined
        $more_rich_snippet = array();

        //get the select itemType
        $review_choosen = YasrDB::getItemType();

        if($review_choosen === 'BlogPosting') {
            $more_rich_snippet = $this->blogPosting($rich_snippet, $rich_snippet_data);
        }
        if($review_choosen === 'Product') {
            $more_rich_snippet = $this->itemProduct($post_meta);
        }
        if($review_choosen === 'LocalBusiness') {
            $more_rich_snippet = $this->localBusiness($post_meta);
        }
        if($review_choosen === 'Recipe') {
            $more_rich_snippet = $this->recipe($post_meta);
        }
        if($review_choosen === 'SoftwareApplication') {
            $more_rich_snippet = $this->softwareApplication($post_meta);
        }
        if($review_choosen === 'Book') {
            $more_rich_snippet = $this->book($post_meta);
        }
        if($review_choosen === 'Movie') {
            $more_rich_snippet = $this->movie($post_meta);
        }

        if(is_array($rich_snippet) && is_array($more_rich_snippet)) {
            return array_merge($rich_snippet, $more_rich_snippet);
        }
        return $rich_snippet;

    }

    /**
     * @author Dario Curvino <@dudo>
     * @param $rich_snippet
     * @param $rich_snippet_data
     *
     * @return mixed
     */
    private function blogPosting($rich_snippet, $rich_snippet_data) {
        $rich_snippet['datePublished']    = $rich_snippet_data['date'];
        $rich_snippet['headline']         = $rich_snippet_data['review_name'];
        $rich_snippet['mainEntityOfPage'] = array(
            '@type' => 'WebPage',
            '@id'   => get_permalink()
        );
        $rich_snippet['author']           = array(
            '@type' => 'Person',
            'name'  => $rich_snippet_data['author']
        );

        $rich_snippet['dateModified'] = $rich_snippet_data['date_modified'];

        $rich_snippet['image'] = array(
            '@type'  => 'ImageObject',
            'url'    => $rich_snippet_data['post_image_url'],
            'width'  => $rich_snippet_data['post_image_size'][0],
            'height' => $rich_snippet_data['post_image_size'][1]
        );

        //blogposting doesn't allow 'Person' has a publisher
        $rich_snippet['publisher'] = $rich_snippet_data['publisher'];
        $rich_snippet['publisher']['@type'] = 'Organization';

        return $rich_snippet;
    }

    /**
     * @author Dario Curvino <@dudo>
     * @param $post_meta
     *
     * @return array
     */
    private function itemProduct($post_meta) {
        $global_identifer_name = $post_meta['yasr_product_global_identifier_select'];

        $rich_snippet['brand'] = array(
            '@type' => 'Brand',                         //This is always just 'Brand'
            'name'  => $post_meta['yasr_product_brand'] //the brand name of the product
        );

        $rich_snippet['sku']                  = $post_meta['yasr_product_sku'];
        $rich_snippet[$global_identifer_name] = $post_meta['yasr_product_global_identifier_value'];

        //Can't use !empty here, because emprty return true with 0, and 0 is a valid price
        if($post_meta['yasr_product_price'] !== '') {
            $rich_snippet['offers'] = array(
                '@type'           => 'Offer',
                'price'           => $post_meta['yasr_product_price'],
                'priceCurrency'   => $post_meta['yasr_product_price_currency'],
                'priceValidUntil' => $post_meta['yasr_product_price_valid_until'],
                'availability'    => $post_meta['yasr_product_price_availability'],
                'url'             => $post_meta['yasr_product_price_url'],
            );
        }

        return $rich_snippet;
    }

    /**
     * @author Dario Curvino <@dudo>
     * @param $post_meta
     *
     * @return array
     */
    private function localBusiness($post_meta) {
        $rich_snippet['address']            = $post_meta['yasr_localbusiness_address'];
        $rich_snippet['priceRange']         = $post_meta['yasr_localbusiness_pricerange'];
        $rich_snippet['telephone']          = $post_meta['yasr_localbusiness_telephone'];

        return $rich_snippet;
    }

    /**
     * @author Dario Curvino <@dudo>
     * @since  2.9.1
     * @param  $post_meta
     *
     * @return array
     */
    private function recipe($post_meta) {
        $instruction_array_clean = array();
        $ingredient_array        = array();

        if(!empty($post_meta['yasr_recipe_recipeinstructions'])) {
            $instruction_array = explode(PHP_EOL, $post_meta['yasr_recipe_recipeinstructions']);
            $i=0;
            foreach ($instruction_array as $instrunction) {
                $instruction_array_clean[$i]['@type'] = "HowToStep";
                $instruction_array_clean[$i]['text'] = $instrunction;
                $i++;
            }
        }

        if(!empty($post_meta['yasr_recipe_recipeingredient'])) {
            $ingredient_array = explode(PHP_EOL, $post_meta['yasr_recipe_recipeingredient']);
        }

        if(!empty($post_meta['yasr_recipe_nutrition'])) {
            $rich_snippet['nutrition'] = array(
                "@type"    => "NutritionInformation",
                "calories" => $post_meta['yasr_recipe_nutrition'] . " calories",
            );

        }

        $rich_snippet['author'] = array(
            '@type' => 'Person',
            'name'  => get_the_author()
        );

        $rich_snippet['cookTime']           = $post_meta['yasr_recipe_cooktime'];
        $rich_snippet['description']        = $post_meta['yasr_recipe_description'];
        $rich_snippet['keywords']           = $post_meta['yasr_recipe_keywords'];
        $rich_snippet['prepTime']           = $post_meta['yasr_recipe_preptime'];
        $rich_snippet['recipeCategory']     = $post_meta['yasr_recipe_recipecategory'];
        $rich_snippet['recipeCuisine']      = $post_meta['yasr_recipe_recipecuisine'];
        $rich_snippet['recipeIngredient']   = $ingredient_array;
        $rich_snippet['recipeInstructions'] = $instruction_array_clean;
        $rich_snippet['video']              = $post_meta['yasr_recipe_video'];

        return $rich_snippet;
    }

    /**
     * @author Dario Curvino <@dudo>
     * @param $post_meta
     *
     * @return array
     */
    private function softwareApplication($post_meta) {
        $rich_snippet['applicationCategory'] = $post_meta['yasr_softwareapplication_category'];
        $rich_snippet['operatingSystem']     = $post_meta['yasr_softwareapplication_os'];

        //Can't use !empty here, because emprty return true with 0, and 0 is a valid price
        if($post_meta['yasr_softwareapplication_price'] !== '') {
            $rich_snippet['offers'] = array(
                '@type'           => 'Offer',
                'price'           => $post_meta['yasr_softwareapplication_price'],
                'priceCurrency'   => $post_meta['yasr_softwareapplication_price_currency'],
                'priceValidUntil' => $post_meta['yasr_softwareapplication_price_valid_until'],
                'availability'    => $post_meta['yasr_softwareapplication_price_availability'],
                'url'             => $post_meta['yasr_softwareapplication_price_url'],
            );

        }
        return $rich_snippet;
    }

    /**
     * @author Dario Curvino <@dudo>
     * @param $post_meta
     *
     * @return array
     */
    private function book($post_meta) {

        if(!empty($post_meta['yasr_book_author'])) {
            $rich_snippet['author'] = array(
                '@type'           => 'Person',
                'name'            => $post_meta['yasr_book_author'],
            );
        }

        $rich_snippet['bookEdition']    = $post_meta['yasr_book_bookedition'];
        $rich_snippet['bookFormat']     = $post_meta['yasr_book_bookformat'];
        $rich_snippet['isbn']           = $post_meta['yasr_book_isbn'];
        $rich_snippet['numberOfPages']  = $post_meta['yasr_book_number_of_pages'];

        return $rich_snippet;
    }

    /**
     * @author Dario Curvino <@dudo>
     * @param $post_meta
     *
     * @return array
     */
    private function movie($post_meta) {
        $actors_array_clean     = array();
        $director_array_clean   = array();

        if(!empty($post_meta['yasr_movie_actor'])) {
            $actors_array = explode(PHP_EOL, $post_meta['yasr_movie_actor']);
            $i=0;
            foreach ($actors_array as $actor) {
                $actors_array_clean[$i]['@type'] = "Person";
                $actors_array_clean[$i]['name'] = $actor;

                $i++;
            }
        }

        $rich_snippet['actor'] = $actors_array_clean;

        if(!empty($post_meta['yasr_movie_director'])) {
            $director_array = explode(PHP_EOL, $post_meta['yasr_movie_director']);
            $i=0;
            foreach ($director_array as $director) {
                $director_array_clean[$i]['@type'] = "Person";
                $director_array_clean[$i]['name'] = $director;

                $i++;
            }
        }

        $rich_snippet['director']    = $director_array_clean;
        $rich_snippet['duration']    = $post_meta['yasr_movie_duration'];
        $rich_snippet['dateCreated'] = $post_meta['yasr_movie_datecreated'];

        return $rich_snippet;
    }

    /**
     * Get the postmeta for the itemTypes
     *
     * @author Dario Curvino <@dudo>
     * @return array|mixed
     */
    public function post_meta() {
        $post_meta = get_post_meta(get_the_ID(), 'yasr_schema_additional_fields', true);
        //avoid undefined
        if(!is_array($post_meta)) {
            $post_meta = array();
        }

        $array_item_type_info = YasrRichSnippetsItemTypes::returnAdditionalFields();

        foreach ($array_item_type_info as $item_type) {
            //avoid undefined
            if(!isset($post_meta[$item_type])) {
                $post_meta[$item_type] = '';
            }
        }

        return $post_meta;
    }


    /**
     * Filter the title that will be used in the rich snippets
     * Use a user defined title if found, or the post title otherwise
     * @author Dario Curvino <@dudo>
     *
     * @param $post_id
     *
     * @return mixed|string
     */
    public function filter_title($post_id) {
        $saved_data = $this->post_meta();

        //if is not empty, overwrite the title with custom itemType name
        if(!empty($saved_data['yasr_schema_title'])) {
            $schema_title = $saved_data['yasr_schema_title'];
        } else {
            //Here I don't use get_the_title because it run after filters are applied.
            //This causes that stars near title will appear in schema title
            //https://wordpress.stackexchange.com/questions/257499/get-title-without-filterthe-title
            $schema_title = get_post_field('post_title', $post_id, 'raw');
        }
        return $schema_title;
    }
}
