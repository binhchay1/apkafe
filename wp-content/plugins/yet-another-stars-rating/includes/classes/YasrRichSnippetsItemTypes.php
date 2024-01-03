<?php

/**
 * @author Dario Curvino <@dudo>
 * @since  3.3.7
 */
class YasrRichSnippetsItemTypes {

    /**
     * @var array
     */
    public static $itemTypes = array(
        'BlogPosting',
        'Book',
        'Course',
        'CreativeWorkSeason',
        'CreativeWorkSeries',
        'Episode',
        'Event',
        'Game',
        'LocalBusiness',
        'MediaObject',
        'Movie',
        'MusicPlaylist',
        'MusicRecording',
        'Organization',
        'Product',
        'Recipe',
        'SoftwareApplication'
    );

    /**
     * here the array member must contain main itemtype name
     * e.g. yasr_softwareapplication contain 'SoftwareApplication'
     */
    public static $additionalFields =  array(
        'yasr_schema_title',
        'yasr_book_author',
        'yasr_book_bookedition',
        'yasr_book_bookformat',
        'yasr_book_isbn',
        'yasr_book_number_of_pages',
        'yasr_localbusiness_address',
        'yasr_localbusiness_pricerange',
        'yasr_localbusiness_telephone',
        'yasr_movie_actor',
        'yasr_movie_datecreated',
        'yasr_movie_director',
        'yasr_movie_duration',
        'yasr_product_brand',
        'yasr_product_global_identifier_select',
        'yasr_product_global_identifier_value',
        'yasr_product_price',
        'yasr_product_price_availability',
        'yasr_product_price_currency',
        'yasr_product_price_url',
        'yasr_product_price_valid_until',
        'yasr_product_sku',
        'yasr_recipe_cooktime',
        'yasr_recipe_description',
        'yasr_recipe_keywords',
        'yasr_recipe_nutrition',
        'yasr_recipe_preptime',
        'yasr_recipe_recipecategory',
        'yasr_recipe_recipecuisine',
        'yasr_recipe_recipeingredient',
        'yasr_recipe_recipeinstructions',
        'yasr_recipe_video',
        'yasr_softwareapplication_category',
        'yasr_softwareapplication_os',
        'yasr_softwareapplication_price',
        'yasr_softwareapplication_price_availability',
        'yasr_softwareapplication_price_currency',
        'yasr_softwareapplication_price_url',
        'yasr_softwareapplication_price_valid_until',
    );

    /**
     * Run filter and return the array of the supported itemTypes
     *
     * @author Dario Curvino <@dudo>
     * @since  3.3.7
     * @return array
     */
    public static function returnItemTypes() {
        /**
         * Use this hook to add (or eventually remove) supported itemTypes
         *
         * @param array $itemTypes an array containing all the default supported itemTypes
         */
        return apply_filters('yasr_filter_itemtypes', self::$itemTypes);
    }


    /**
     * @author Dario Curvino <@dudo>
     *
     * @since  3.3.7
     * @return mixed|null
     */
    public static function returnAdditionalFields () {
        /**
         * Use this hook to add optional fields for an itemType
         *
         * Here the array member must contain main itemType name
         *
         * E.g. if you want to add the filed 'price' to 'SoftwareApplication, you need to add
         * yasr_softwareapplication_price
         *
         * @param array $additionalFields an array containing all the default supported additional fields
         */
        return apply_filters('yasr_filter_itemtypes_fields', self::$additionalFields);
    }

    /**
     * Check if the given string is a supported itemType
     *
     * @param string $item_type
     *
     * @since 2.1.5
     * @return bool
     */
    public static function isSupported($item_type) {
        $supported_schema_array = self::returnItemTypes();

        if (in_array($item_type, $supported_schema_array)) {
            return true;
        }

        return false;
    }

} //End class