<?php

/*

Copyright 2014 Dario Curvino (email : d.curvino@gmail.com)

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>
*/

if (!defined('ABSPATH')) {
    exit('You\'re not allowed to see this page');
} // Exit if accessed directly

/**
 * Print additional itemType fields above the editor
 * @uses class YasrPhpFieldsHelper
 *
 * Class YasrMetaboxSchemaFields
 */
class YasrMetaboxSchemaFields {

    private $saved_data;

    public function __construct($saved_data) {
        $this->saved_data = $saved_data;
        new YasrPhpFieldsHelper('yasr-element-row-container-label-input');
    }

    /**
     * Generate fields for Product ItemType
     */
    public function product() {
        ?>
        <!--product-->
        <div id="yasr-metabox-info-snippet-container-product"
             class="yasr-metabox-info-snippet-container-child">
            <?php
                $product_array = array(
                    'brand' => array(
                        'label'       => 'Brand',
                        'name'        => 'yasr_product_brand',
                        'description' => __('The brand of the product.', 'yet-another-stars-rating')
                    ),
                    'sku' => array(
                        'label'       => 'Sku',
                        'name'        => 'yasr_product_sku',
                        'description' => __('Merchant-specific identifier for product.' , 'yet-another-stars-rating')
                    ),
                    'global_identifier_select' => array(
                        'type'        => 'select',
                        'options'     =>  array('Select...', 'gtin8', 'gtin13', 'gtin14', 'mpn', 'isbn'),
                        'label'       => 'Global Identifier',
                        'name'        => 'yasr_product_global_identifier_select'
                    ),
                    'global_identifier_value' => array(
                        'label'       => 'Value',
                        'name'        => 'yasr_product_global_identifier_value',
                        'description' => sprintf(
                            __('Select global identifier. More info %s here %s', 'yet-another-stars-rating'),
                            '<a href="https://schema.org/Product" target="_blank">','</a>'
                        )
                    )
                );

                $this->echoFields($product_array);

                $this->offer(
                    'yasr_product_price',
                    'yasr_product_price_currency',
                    'yasr_product_price_valid_until',
                    'yasr_product_price_availability',
                    'yasr_product_price_url'
                );
            ?>
        </div>
        <!--End product-->
        <?php
    }

    /**
     * Generate fields for localBuisness
     */
    public function localBuinsess() {
        ?>
        <div id="yasr-metabox-info-snippet-container-localbusiness"
             class="yasr-metabox-info-snippet-container-child">

            <?php
                $localbuisness_array = array(
                    'address' => array(
                        'label'       => 'Address',
                        'name'        => 'yasr_localbusiness_address',
                        'description' => __('The physical location of the business. ', 'yet-another-stars-rating')
                    ),
                    'pricerange' => array(
                        'label'       => 'PriceRange',
                        'name'        => 'yasr_localbusiness_pricerange',
                        'description' => __('The relative price range of a business, commonly specified by either a numerical range
                                            (for example, "$10-15") or a normalized number of currency signs (for example, "$$$")' ,
                                          'yet-another-stars-rating')
                    ),
                    'telephone' => array(
                        'label'       => 'Telephone',
                        'name'        => 'yasr_localbusiness_telephone',
                        'description' => __('A business phone number meant to be the primary contact method for customers.' , 'yet-another-stars-rating')
                    )
                );

                $this->echoFields($localbuisness_array);
            ?>
        </div>
        <?php
    }

    /**
     * Generate fields for recipe itemType
     */
    public function recipe() {
        $recipe_array = array(
            array(
                'label'       => 'cookTime',
                'name'        => 'yasr_recipe_cooktime',
                'description' => __('The time it takes to actually cook the dish in ISO 8601 format.', 'yet-another-stars-rating')
            ),
            array(
                'label'       => 'prepTime',
                'name'        => 'yasr_recipe_preptime',
                'description' => __('The length of time it takes to prepare the dish, in ISO 8601 format.', 'yet-another-stars-rating')
            ),
            array(
                'label'       => 'description',
                'name'        => 'yasr_recipe_description',
                'description' => __('A short summary describing the dish.', 'yet-another-stars-rating')
            ),
            array(
                'label'       => 'keywords',
                'name'        => 'yasr_recipe_keywords',
                'description' => __('Other terms for your recipe', 'yet-another-stars-rating')
            ),
            array(
                'label'       => 'nutrition',
                'name'        => 'yasr_recipe_nutrition',
                'description' => __('The number of calories', 'yet-another-stars-rating')
            ),
            array(
                'label'       => 'recipeCategory',
                'name'        => 'yasr_recipe_recipecategory',
                'description' => __('The type of meal or course your recipe is about', 'yet-another-stars-rating')
            ),
            array(
                'label'       => 'recipeCuisine',
                'name'        => 'yasr_recipe_recipecuisine',
                'description' => __('The region associated with your recipe. For example, "Mediterranean", or "American".',
                                    'yet-another-stars-rating')
            ),
            array(
                'type'        => 'textarea',
                'label'       => 'recipeIngredient',
                'name'        => 'yasr_recipe_recipeingredient',
                'description' => sprintf(
                                    __('A single ingredient used in the recipe, e.g. sugar, flour or garlic. %s 
                                        Use return %s to separate the ingredients.', 'yet-another-stars-rating'),
                                    '<br />', '<strong>(&#8629;)</strong>')
            ),
            array(
                'type'        => 'textarea',
                'label'       => 'recipeInstructions',
                'name'        => 'yasr_recipe_recipeinstructions',
                'description' => sprintf(
                                    __('The steps to make dish. %s Use return %s to separate the steps.', 'yet-another-stars-rating'),
                               '<br />', '<strong>(&#8629;)</strong>')
            ),
            array(
                'label'       => 'Video',
                'name'        => 'yasr_recipe_video',
                'description' => __('A video depicting the steps to make the dish (URL)', 'yet-another-stars-rating')
            )
        ); //End recipe array
        ?>

        <!-- Recipe -->
        <div id="yasr-metabox-info-snippet-container-recipe"
             class="yasr-metabox-info-snippet-container-child">

            <?php $this->echoFields($recipe_array); ?>

            <div class="yasr-element-row-container-description">
                <?php
                    echo sprintf(
                        __('More info %s here %s and %s here %s', 'yet-another-stars-rating'),
                        '<a href="https://developers.google.com/search/docs/data-types/recipe" target="_blank">','</a>',
                        '<a href="https://schema.org/Recipe" target="_blank">','</a>'
                    );
                ?>
            </div>
        </div>
        <!-- End Recipe -->
        <?php
    }

    /**
     * Generate fields for softwareApplication itemType
     */
    public function softwareApplication() {
        ?>
        <div id="yasr-metabox-info-snippet-container-software"
             class="yasr-metabox-info-snippet-container-child">
        <?php
            $software_array = array(
                'application' => array(
                    'label'       => 'Application Category',
                    'name'        => 'yasr_softwareapplication_category',
                    'description' => __('Type of software application, e.g. \'Game, Multimedia\'. ', 'yet-another-stars-rating')
                ),
                'os' => array(
                    'label'       => 'Operating System',
                    'name'        => 'yasr_softwareapplication_os',
                    'description' => __('Operating systems supported e.g. (Windows 11, OSX 12, Android 12)' , 'yet-another-stars-rating')
                )
            );

            $this->echoFields($software_array);

            $this->offer('yasr_softwareapplication_price',
                'yasr_softwareapplication_price_currency',
                'yasr_softwareapplication_price_valid_until',
                'yasr_softwareapplication_price_availability',
                'yasr_softwareapplication_price_url');

            ?>

        </div>
        <?php
    }

    /**
     * Generate fields for books
     */
    public function book() {
        ?>
        <div id="yasr-metabox-info-snippet-container-book"
             class="yasr-metabox-info-snippet-container-child">

            <?php
            $book_array = array(
                'author' => array(
                    'label'       => 'author',
                    'name'        => 'yasr_book_author',
                    'description' => __('The author of the book. ', 'yet-another-stars-rating')
                ),
                'book_edition' => array(
                    'label'       => 'bookEdition',
                    'name'        => 'yasr_book_bookedition',
                    'description' => __('The edition of the book. ', 'yet-another-stars-rating')
                ),
                'book_format' => array(
                    'type'        => 'select',
                    'options'     =>  array('Select...', 'AudiobookFormat', 'EBook', 'GraphicNovel', 'Hardcover', 'Paperback'),
                    'label'       => 'bookFormat',
                    'name'        => 'yasr_book_bookformat',
                    'description' => __('The format of the book. ', 'yet-another-stars-rating')
                ),
                'isbn'        => array(
                    'label'       => 'ISBN',
                    'name'        => 'yasr_book_isbn',
                    'description' => sprintf(
                                        __('The ISBN of the book. Google only supports ISBN-13. More info %s here %s',
                                            'yet-another-stars-rating'),
                                    '<a href="https://developers.google.com/search/docs/data-types/book#isbn-and-other-supported-identifiers">','</a>'
                    )
                ),
                'pages'       => array(
                    'label'       => 'numberOfPages',
                    'name'        => 'yasr_book_number_of_pages',
                    'description' => __('The number of pages in the book, must be a number.' ,'yet-another-stars-rating')
                ),

            );

            $this->echoFields($book_array);
            ?>
        </div>
        <?php
    }


    /**
     * Generate fields for movie
     */
    public function movie() {
        ?>
        <div id="yasr-metabox-info-snippet-container-movie"
             class="yasr-metabox-info-snippet-container-child">

            <?php
            $movie_array = array(
                'actor' => array (
                    'type'        => 'textarea',
                    'label'       => 'actor',
                    'name'        => 'yasr_movie_actor',
                    'description' => sprintf(
                                            __('Insert one or more actors %s. Use return %s to separate the actors.',
                                                'yet-another-stars-rating'),
                                            '<br />', '<strong>(&#8629;)</strong>')
                ),
                'director' => array (
                    'type'        => 'textarea',
                    'label'       => 'director',
                    'name'        => 'yasr_movie_director',
                    'description' => sprintf(
                        __('Insert one or more director %s. Use return %s to separate the directors.',
                            'yet-another-stars-rating'),
                        '<br />', '<strong>(&#8629;)</strong>')
                ),
                'duration' => array (
                    'label'       => 'duration',
                    'name'        => 'yasr_movie_duration',
                    'description' => __('The duration of the movie in ISO 8601 date format.',
                            'yet-another-stars-rating')
                ),
                'dateCreated' => array (
                    'label'       => 'dateCreated',
                    'name'        => 'yasr_movie_datecreated',
                    'description' => __('The date on which the Movie was created.',
                        'yet-another-stars-rating')
                ),

            );

            $this->echoFields($movie_array);
            ?>
        </div>
        <?php
    }

    /**
     * Print the additional fields
     *
     * @param $itemType_array
     */
    private function echoFields($itemType_array) {
        $string_input = false;

        foreach ($itemType_array as $property) {
            if (isset($property['type'])) {
                if ($property['type'] === 'select') {
                    $default_value = esc_attr($this->saved_data[$property['name']]);

                    //if is not saved the default value for the select, use N/A
                    if($default_value === '') {
                        $default_value = 'Select...';
                    }

                    $string_input = YasrPhpFieldsHelper::select(
                        '', $property['label'], $property['options'], $property['name'], '', $default_value
                    );
                }
                elseif ($property['type'] === 'textarea') {
                    $string_input = YasrPhpFieldsHelper::textArea('', $property['label'], $property['name'], '', '',
                                                            $this->saved_data[$property['name']] );
                }
            }
            else {
                $string_input = YasrPhpFieldsHelper::text(
                    '', $property['label'], $property['name'], '', $property['label'], esc_attr($this->saved_data[$property['name']])
                );
            }

            echo yasr_kses($string_input);

            if(isset($property['description'] ) && $property['description'] !== '') {
                echo yasr_kses('<div class="yasr-element-row-container-description">'
                     . $property['description'] .
                     '</div>');
            }

        }
    }


    /**
     * Generate offers fields.
     * get params for "name" html value
     *
     * @param $price_name
     * @param $currency_name
     * @param $valid_until_name
     * @param $availability_name
     * @param $url_name
     */
    private function offer($price_name, $currency_name, $valid_until_name, $availability_name, $url_name) {
        $saved_data = $this->saved_data;
        ?>
        <div style="border: 1px dotted #cacaca; width: max-content; margin: 10px; padding: 5px;">
            <?php
                $offer_array = array(
                    'price' => array(
                        'label'       => 'price',
                        'name'        => $price_name,
                    ),
                    'currency' => array(
                        'label'       => 'currency',
                        'name'        => $currency_name,
                    ),
                    'valid_until' => array(
                        'label'       => 'priceValidUntil',
                        'name'        => $valid_until_name
                    ),
                    'availability' => array(
                        'label'       => 'availability',
                        'name'        => $availability_name
                    ),
                    'url' => array(
                        'label'       => 'url',
                        'name'        => $url_name,
                    )
                );

                foreach ($offer_array as $property => $info) {
                    if ($property === 'availability') {
                        $array_global_availability = array (
                            'Select...', 'Discontinued', 'InStock', 'InStoreOnly', 'LimitedAvailability',
                            'OnlineOnly', 'OutOfStock', 'PreOrder', 'PreSale', 'SoldOut'
                        );

                        $string_input = YasrPhpFieldsHelper::select(
                            '', $info['label'], $array_global_availability, $info['name'], '', esc_attr($saved_data[$info['name']])
                        );
                    }
                    else {
                        $string_input = YasrPhpFieldsHelper::text(
                            '', $info['label'], $info['name'], '', '', esc_attr($saved_data[$info['name']])
                        );
                    }
                    echo $string_input;
                }
            ?>

            <div class="yasr-element-row-container-description">
                <?php
                    echo sprintf(
                        __('More info %s here %s', 'yet-another-stars-rating'),
                        '<a href="https://schema.org/Offer" target="_blank">','</a>'
                    );
                ?>
            </div>

        </div>
        <?php
    }

}