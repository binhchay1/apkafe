<?php
/**
 * Product helper functions
 *
 * @package     AAWP
 * @since       3.4.0
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Set up product data coming from API, before storing in database
 *
 * @param Flowdee\AmazonPAAPI5WP\Item $Item
 * @param bool $is_update
 * @return array|null
 */
function aawp_setup_product_data_for_database( $Item, $is_update = false ) {

    if ( ! is_object( $Item ) )
        return null;

    if ( ! method_exists( $Item, 'getASIN' ) || empty( $asin = $Item->getASIN() ) )
        return null;

    //aawp_debug( $Item->getData(), '$Item->getData()' );

    $data = array(
        'status' => 'active',
        'asin' => $asin,
        'ean' => ( method_exists( $Item, 'getEAN' ) && ! empty( $ean = $Item->getEAN() ) ) ? $ean : '',
        'isbn' => ( method_exists( $Item, 'getISBN' ) && ! empty( $isbn = $Item->getISBN() ) ) ? $isbn : '',
        'binding' => ( method_exists( $Item, 'getBinding' ) && ! empty( $binding = $Item->getBinding() ) ) ? $binding : '',
        'product_group' => ( method_exists( $Item, 'getProductGroup' ) && ! empty( $get_product_group = $Item->getProductGroup() ) ) ? $get_product_group : '',
        'title' => ( method_exists( $Item, 'getTitle' ) && ! empty( $title = $Item->getTitle() ) ) ? $title : '',
        'url' => ( method_exists( $Item, 'getURL' ) && ! empty( $url = $Item->getURL() ) ) ? aawp_replace_url_tracking_id_placeholder( $url ) : '',
        'image_ids' => '',
        'features' => ( method_exists( $Item, 'getFeatures' ) && ! empty( $features = $Item->getFeatures() ) ) ? maybe_serialize( $features ) : '',
        'attributes' => '',
        'availability' => ( method_exists( $Item, 'isInStock' ) ) ? $Item->isInStock() : '',
        'currency' => ( method_exists( $Item, 'getCurrency' ) && ! empty( $currency = $Item->getCurrency() ) ) ? $currency : '',
        'price' => ( method_exists( $Item, 'getPrice' ) && ! empty( $price = $Item->getPrice( 'amount' ) ) ) ? aawp_cleanup_product_price_amount( $price ) : '',
        'savings' => ( method_exists( $Item, 'getPriceSavings' ) && ! empty( $savings = $Item->getPriceSavings( 'amount' ) ) ) ? aawp_cleanup_product_price_amount( $savings ) : '',
        'savings_percentage' => ( method_exists( $Item, 'getPriceSavings' ) && ! empty( $savings_percentage = $Item->getPriceSavings( 'percentage' ) ) ) ? $savings_percentage : '',
        'savings_basis' => ( method_exists( $Item, 'getPriceSavingsBasis' ) && ! empty( $savings_basis = $Item->getPriceSavingsBasis( 'amount' ) ) ) ? aawp_cleanup_product_price_amount( $savings_basis ) : '',
        'salesrank' => ( method_exists( $Item, 'getSalesRank' ) && ! empty( $salesrank = $Item->getSalesRank() ) ) ? $salesrank : '',
        'is_prime' => ( method_exists( $Item, 'isPrime' ) && ! empty( $Item->isPrime() ) ) ? 1 : '',
        'is_amazon_fulfilled' => ( method_exists( $Item, 'isAmazonFulfilled' ) && ! empty( $Item->isAmazonFulfilled() ) ) ? 1 : '',
        'shipping_charges' => '',
    );

    // Collect image ids
    if ( method_exists( $Item, 'getImages' ) ) {

        $images = $Item->getImages();

        if ( $images ) {

            $image_urls = array();

            // Primary image
            if ( isset( $images['primary'] ) && $images['primary']['large'] && ! empty( $images['primary']['large']['url'] ) )
                $image_urls[] = $images['primary']['large']['url'];

            // Variants
            if ( isset( $images['variants'] ) && is_array( $images['variants'] ) && sizeof( $images['variants'] ) >0 ) {

                foreach ( $images['variants'] as $image_variant ) {

                    if ( isset( $image_variant['large'] ) && ! empty( $image_variant['large']['url'] ) && ! in_array( $image_variant['large']['url'], $image_urls ) )
                        $image_urls[] = $image_variant['large']['url'];
                }
            }

            $image_ids = aawp_get_product_image_ids_from_urls( $image_urls, true );

            if ( ! empty ( $image_ids ) )
                $data['image_ids'] = $image_ids;
        }
    }

    // Collect attributes
    $attributes = array(
        'basic_info' => ( method_exists( $Item, 'getByLineInfo' ) ) ? $Item->getByLineInfo() : array(),
        'classifications' => ( method_exists( $Item, 'getClassifications' ) ) ? $Item->getClassifications() : array(),
        'content_info' => ( method_exists( $Item, 'getContentInfo' ) ) ? $Item->getContentInfo() : array(),
        'content_rating' => ( method_exists( $Item, 'getContentRating' ) ) ? $Item->getContentRating() : array()
    );

    $data['attributes'] = maybe_serialize( $attributes );

    // Prevent overwriting specific data
    if ( ! $is_update ) {
        $rating = ( method_exists( $Item, 'getRating' ) && ! empty( $rating = $Item->getRating() ) ) ? $rating : 0;
        $reviews = ( method_exists( $Item, 'getReviews' ) && ! empty( $reviews = $Item->getReviews() ) ) ? $reviews : 0;

        if ( ( empty( $rating ) || empty( $reviews ) ) && aawp_is_crawling_reviews_activated() ) {

            $AAWP_Review_Crawler = new AAWP_Review_Crawler();

            $review_data = $AAWP_Review_Crawler->get_data( $asin );

            //aawp_debug( $data, '$data' );

            if ( ! empty( $review_data['rating'] ) )
                $rating = $review_data['rating'];

            if ( ! empty( $review_data['reviews'] ) )
                $reviews = $review_data['reviews'];
        }

        $data['rating'] = $rating;
        $data['reviews'] = $reviews;
    }

    //aawp_debug( $data, '$data' );

    // Finished
    return $data;
}

/**
 * Set up product data coming from the database, before using in our plugin
 *
 * @param $data
 * @return array
 */
function aawp_setup_product_data_from_database( $data ) {

    if ( is_object( $data ) ) {

        // Convert object to array
        $data = get_object_vars( $data );

        if ( isset( $data['features'] ) )
            $data['features'] = maybe_unserialize( unserialize( $data['features'] ) );

        if ( isset( $data['attributes'] ) )
            $data['attributes'] = maybe_unserialize( unserialize( $data['attributes'] ) );

        if ( isset( $data['image_ids'] ) )
            $data['image_ids'] = ( ! empty( $data['image_ids'] ) ) ? explode( ',', $data['image_ids'] ) : array();
    }

    return $data;
}

/**
 * Generate product description
 *
 * @param $data
 * @param array $args
 * @param bool $return_html
 * @return array|mixed|string|null
 */
function aawp_generate_product_description( $data, $args = array(), $return_html = false ) {

    if ( empty( $data ) )
        return null;

    $defaults = array(
        // Silence
    );

    $args = wp_parse_args( $args, $defaults );

    // Handle options
    $options = aawp_get_options();

    if ( empty( $options['api']['country'] ) )
        return null;

    $args['description_items'] = ( isset ( $options['output']['description_items'] ) && $options['output']['description_items'] != '' ) ? intval( $options['output']['description_items'] ) : 5;
    $args['description_html'] = ( ! isset ( $options['output']['description_html'] ) || $options['output']['description_html'] == '1' ) ? 1 : 0;
    $args['description_length'] = ( !empty ( $options['output']['description_length'] ) ) ? intval( $options['output']['description_length'] ) : 200;
    $args['description_length_unlimited'] = ( !empty ( $options['output']['description_length'] ) && !isset ( $options['output']['description_length_unlimited'] ) ) ? 0 : 1;

    // Handle atts
    global $aawp_shortcode_atts;

    if ( ! empty( $aawp_shortcode_atts['description_items'] ) )
        $args['description_items'] = intval( $aawp_shortcode_atts['description_items'] );

    // DEV only
    //$args['description_items'] = 99;

    // Build description
    $description = '';
    $description_items = array();

    // Features available
    if ( ! empty ( $data['features'] ) ) {

        if ( is_array( $data['features'] ) && sizeof( $data['features'] ) > 0 ) {

            foreach ( $data['features'] as $feature ) {

                if ( ! empty( $feature ) )
                    $description_items[] = aawp_cleanup_product_feature( $feature );
            }

        } else {
            $description_items[] = aawp_cleanup_product_feature( $data['features'] );
        }
    }

    // Related to product groups
    if ( sizeof( $description_items ) < $args['description_items'] && ! empty( $data['attributes'] ) ) {
        $description_items = aawp_extend_product_description_items( $description_items, $data );
    }

    if ( ! empty( $description_items ) ) {

        if ( $return_html ) {

            if ( is_array( $description_items ) ) {

                $description = '<ul>';

                for( $i = 0; $i < $args['description_items'] && $i < count( $description_items ); $i++) {

                    $text = ( $args['description_html'] == '0' ) ? strip_tags( $description_items[$i]) : $description_items[$i];

                    if ( ! empty( $aawp_shortcode_atts['description_length'] ) ) {
                        $text = aawp_truncate_string( $text, $aawp_shortcode_atts['description_length'] );

                    } elseif ( $args['description_length_unlimited'] != 1 ) {
                        if ( !empty($args['description_length']) && strlen($text) > $args['description_length'] )
                            $text = aawp_truncate_string( $text, $args['description_length'] );
                    }

                    $description .= '<li>' . $text . '</li>';
                }

                $description .= '</ul>';

            } elseif ( is_string( $description_items ) ) {
                $text = ( $args['description_html'] == '0' ) ? strip_tags( $description_items ) : $description_items;

                $description = '<p>' . $text . '</p>';
            }

        } else {
            $description = $description_items;
        }
    }

    return $description;
}

/**
 * Generate product teaser
 *
 * @param $data
 * @param array $args
 * @return mixed|string
 */
function aawp_generate_product_teaser( $data, $args = array() ) {

    $description_items = aawp_generate_product_description( $data, array(
        'description_items' => 10,
        'description_html' => false,
        'description_length_unlimited' => true
    ) );

    //var_dump( $description_items );

    $teaser = '';
    $teaser_length = 90; // TODO: Setting ?

    if ( is_array( $description_items ) && sizeof( $description_items ) > 0 ) {

        foreach ( $description_items as $description_item ) {

            if ( strlen( $description_item ) > $teaser_length )
                continue;

            if ( strlen( $teaser ) < $teaser_length )
                $teaser .= ( ! empty( $teaser ) ) ? '; ' . aawp_cleanup_product_feature( $description_item ) : aawp_cleanup_product_feature( $description_item );
        }
    }

    return $teaser;
}

/**
 * Cleanup attributes feature
 *
 * @param $feature
 *
 * @return mixed
 */
function aawp_cleanup_product_feature( $feature ) {

	$feature = ltrim( $feature, "***" );

	return $feature;
}

/**
 * Extend product description items with product type related attributes
 *
 * @param $description_items
 * @param $data
 * @return mixed
 */
function aawp_extend_product_description_items( $description_items, $data ) {

    $binding = ( ! empty ( $data['binding'] ) ) ? $data['binding'] : null;
    $product_group = ( ! empty ( $data['product_group'] ) ) ? $data['product_group'] : null;

    //aawp_debug( $data, 'aawp_extend_product_description_items() >> $data' );

    /*
     * Setup data
     */
    $data_attributes = $data['attributes'];
    $data_basic_info = ( ! empty ( $data_attributes['basic_info'] ) ) ? $data_attributes['basic_info'] : null;
    $data_content_info = ( ! empty ( $data_attributes['content_info'] ) ) ? $data_attributes['content_info'] : null;
    $data_content_rating = ( ! empty ( $data_attributes['content_rating'] ) ) ? $data_attributes['content_rating'] : null;

    /*
     * Product types/groups
     */

    // Prime Video
    $is_prime_video = false;

    if ( 'Prime Video' === $binding ) {
        $is_prime_video = true;

        $description_items[] = __( 'Amazon Prime Video (Video on Demand)', 'aawp' );
    }

    // Books
    $is_book = false;

    if ( 'Book' === $product_group ) {
        $is_book = true;

        if ( 'Hardcover' === $binding )
            $description_items[] = __( 'Hardcover Book', 'aawp' );
    }

    // Kindle eBooks
    $is_ebook = false;

    if ( 'Kindle Edition' === $binding ) {
        $is_ebook = true;

        $description_items[] = __( 'Amazon Kindle Edition', 'aawp' );
    }

    // Audio Books (Audible)
    $is_audio_book = false;

    if ( 'Audible Audiobook' === $binding ) {
        $is_audio_book = true;

        $description_items[] = __( 'Audible Audiobook', 'aawp' );
    }

    // Audio CD
    $is_audio_cd = false;

    if ( 'Audio CD' === $binding ) {
        $is_audio_cd = true;

        $description_items[] = __( 'Audio CD – Audiobook', 'aawp' );
    }

    /*
     * Attributes
     */
    if ( ! empty( $data_attributes ) ) {

        /*
         * Basic Info
         */
        if ( ! empty( $data_basic_info['contributors'] ) ) {

            // Contributors
            $contributors = array(
                'actors' => array(),
                'directors' => array(),
                'writers' => array(),
                'producers' => array(),
                'authors' => array(),
                'narrators' => array()
            );

            foreach ( $data_basic_info['contributors'] as $contributor ) {

                if ( empty( $contributor['name'] ) || empty( $contributor['roleType'] ) )
                    continue;

                if ( 'actor' === $contributor['roleType'] && sizeof( $contributors['actors'] ) < 3 ) {
                    $contributors['actors'][] = $contributor['name'];

                } elseif ( 'director' === $contributor['roleType'] && sizeof( $contributors['directors'] ) < 1 ) {
                    $contributors['directors'][] = $contributor['name'];

                } elseif ( 'writer' === $contributor['roleType'] && sizeof( $contributors['writers'] ) < 1 ) {
                    $contributors['writers'][] = $contributor['name'];

                } elseif ( 'producer' === $contributor['roleType'] && sizeof( $contributors['producers'] ) < 1 ) {
                    $contributors['producers'][] = $contributor['name'];

                } elseif ( 'author' === $contributor['roleType'] && sizeof( $contributors['authors'] ) < 1 ) {
                    $contributors['authors'][] = $contributor['name'];

                } elseif ( 'narrator' === $contributor['roleType'] && sizeof( $contributors['narrators'] ) < 3 ) {
                    $contributors['narrators'][] = $contributor['name'];
                }
            }

            if ( ! empty( $actors_count = sizeof( $contributors['actors'] ) ) )
                $description_items[] = implode(', ', $contributors['actors'] ) . sprintf( esc_html( _n( ' (Actor)', ' (Actors)', $actors_count, 'aawp'  ) ), $actors_count );

            $contributors_collection = array();

            if ( ! empty( $directors_count = sizeof( $contributors['directors'] ) ) )
                $contributors_collection[] = implode(', ', $contributors['directors'] ) . sprintf( esc_html( _n( ' (Director)', ' (Directors)', $directors_count, 'aawp'  ) ), $directors_count );

            if ( ! empty ( $writers_count = sizeof( $contributors['writers'] )) )
                $contributors_collection[] = implode(', ', $contributors['writers'] ) . sprintf( esc_html( _n( ' (Writer)', ' (Writers)', $writers_count, 'aawp'  ) ), $writers_count );

            if ( ! empty ( $producers_count = sizeof( $contributors['producers'] ) ) )
                $contributors_collection[] = implode(', ', $contributors['producers'] ) . sprintf( esc_html( _n(' (Producer)', ' (Producers)', $producers_count, 'aawp' ) ), $producers_count );

            if ( ! empty ( $authors_count = sizeof( $contributors['authors'] ) ) )
                $contributors_collection[] = implode(', ', $contributors['authors'] ) . sprintf( esc_html( _n(' (Author)', ' (Authors)', $authors_count, 'aawp' ) ), $authors_count );

            if ( ! empty ( $narrators_count = sizeof( $contributors['narrators'] ) ) )
                $contributors_collection[] = implode(', ', $contributors['narrators'] ) . sprintf( esc_html( _n(' (Narrator)', ' (Narrators)', $narrators_count, 'aawp' ) ), $narrators_count );

            if ( ! empty ( $contributors_collection ) )
                $description_items[] = implode(' - ', $contributors_collection );
        }

        /*
         * Content info
         */
        //if ( ! empty ( $data_content_info['edition'] ) )
        // TODO

        // Languages
        if ( ! empty ( $data_content_info['languages'] ) ) {

            $languages = array(
                'spoken' => array(),
                'subtitled' => array(),
                'published' => array()
            );

            foreach ( $data_content_info['languages'] as $language ) {

                if ( empty( $language['value'] ) || empty( $language['type'] ) )
                    continue;

                if ( 'Spoken' === $language['type'] && sizeof( $languages['spoken'] ) < 5 ) {
                    $languages['spoken'][] = $language['value'];

                } elseif ( 'Subtitled' === $language['type'] && sizeof( $languages['subtitled'] ) < 5 ) {
                    $languages['subtitled'][] = $language['value'];

                } elseif ( 'Published' === $language['type'] && sizeof( $languages['published'] ) < 1 ) {
                    $languages['published'][] = $language['value'];
                }
            }

            if ( ! empty( $spoken_count = sizeof( $languages['spoken'] ) ) )
                $description_items[] = implode(', ', $languages['subtitled'] ) . sprintf( esc_html( _n( ' (Playback Language)', ' (Playback Languages)', $spoken_count, 'aawp'  ) ), $spoken_count );

            if ( ! empty( $subtitled_count = sizeof( $languages['subtitled'] ) ) )
                $description_items[] = implode(', ', $languages['subtitled'] ) . sprintf( esc_html( _n( ' (Subtitle)', ' (Subtitles)', $subtitled_count, 'aawp'  ) ), $subtitled_count );

            if ( ! empty( $published_count = sizeof( $languages['published'] ) ) )
                $description_items[] = implode(', ', $languages['published'] ) . sprintf( esc_html( _n( ' (Publication Language)', ' (Publication Languages)', $published_count, 'aawp'  ) ), $published_count );
        }

        //if ( ! empty ( $data_content_info['pages_count'] ) )
        // TODO

        //if ( ! empty ( $data_content_info['publication_date'] ) )
        // TODO

        /*
         * Content rating
         */

        // Audience rating
        if ( ! empty( $data_content_rating['audienceRating'] ) )
            $description_items[] = __('Audience Rating: ', 'aawp' ) . $data_content_rating['audienceRating'];

        /*
         * Additional product type/group related descriptions
         */
        if ( $is_book || $is_ebook || $is_audio_book || $is_audio_cd ) {

            $book_desc_item = array();

            if ( isset( $data_content_info['pagesCount'] ) && ! empty ( $pages_count = $data_content_info['pagesCount'] ) && is_numeric( $pages_count ) )
                $book_desc_item[] = sprintf( esc_html( _n( '%d Page', '%d Pages', $pages_count, 'aawp'  ) ), $pages_count );

            if ( isset( $data_content_info['publicationDate'] ) && ! empty ( $publication_date = $data_content_info['publicationDate'] ) )
                $book_desc_item[] = sprintf( esc_html__( '%s (Publication Date)', 'aawp' ), aawp_date( $publication_date ) );

            if ( isset( $data_basic_info['manufacturer'] ) && ! empty ( $manufacturer = $data_basic_info['manufacturer'] ) )
                $book_desc_item[] = sprintf( esc_html__( '%s (Publisher)', 'aawp' ), $manufacturer );

            if ( ! empty ( $book_desc_item ) )
                $description_items[] = implode(' - ', $book_desc_item );
        }
    }

    return $description_items;
}

/**
 * Generate product type specific description out of given attributes
 *
 * @param $items
 * @param $attributes
 * @param $type
 * @param bool $teaser
 *
 * @return array
 */
function aawp_generate_product_type_specific_description( $items, $attributes, $type, $teaser = false ) { // TODO: Deprecated

    $store = aawp_get_amazon_store();

    //var_dump( $type );
    //aawp_debug( $attributes );

    // Books
    if ( 'ABIS_BOOK' === $type ) {

        if ( isset( $attributes['Author'] ) )
            $items[] = ( is_array($attributes['Author'] ) ) ? implode(', ', $attributes['Author'] ) : $attributes['Author'];

        if ( isset( $attributes['Publisher'] ) )
            $items[] = sprintf( esc_html__('Publisher: %s', 'aawp'), $attributes['Publisher'] );

        if ( isset( $attributes['Edition'] ) && isset( $attributes['PublicationDate'] ) ) {
            $edition_num = preg_replace("/[^0-9]/", "", $attributes['Edition'] );
            $items[] = sprintf( esc_html__('Edition no. %d', 'aawp'), $edition_num ) . ' (' . aawp_date( $attributes['PublicationDate'], $store ) . ')';
        }

        if ( isset( $attributes['Binding'] ) && isset( $attributes['NumberOfPages'] ) )
            $items[] = $attributes['Binding'] . ': ' . sprintf( esc_html__('%d pages', 'aawp'), $attributes['NumberOfPages'] );
    }

    // DVD, BluRay, Prime Video
    if (  'ABIS_DVD' === $type || 'DOWNLOADABLE_MOVIE' === $type || 'DOWNLOADABLE_TV_SEASON' === $type ) {

        if ( isset( $attributes['Studio'] ) && isset( $attributes['ReleaseDate'] ) ) {
            $items[] = $attributes['Studio'] . ' (' . aawp_date( $attributes['ReleaseDate'], $store ) . ')';

        } elseif ( isset( $attributes['Studio'] ) && isset( $attributes['PublicationDate'] ) ) {
            $items[] = $attributes['Studio'] . ' (' . aawp_date( $attributes['PublicationDate'], $store ) . ')';
        }

        if ( isset( $attributes['Binding'] ) && isset( $attributes['AudienceRating'] ) )
            $items[] = $attributes['Binding'] . ', ' . $attributes['AudienceRating'];

        if ( isset( $attributes['RunningTime'] ) && is_numeric( $attributes['RunningTime'] ) )
            $items[] = sprintf( esc_html__('Running time: %d minutes', 'aawp'), $attributes['RunningTime'] );

        if ( isset( $attributes['Actor'] ) )
            $items[] = ( is_array( $attributes['Actor'] ) ) ? implode(', ', $attributes['Actor'] ) : $attributes['Actor'];

        if ( isset( $attributes['Languages']['Language'] ) && is_array( $attributes['Languages']['Language'] ) && sizeof( $attributes['Languages']['Language'] ) != 0 && ! $teaser )
            $items[] = aawp_get_description_attribute_languages( $attributes['Languages']['Language'] );
    }

    // Music
    if ( 'ABIS_MUSIC' === $type ) {

        if ( isset( $attributes['Artist'] ) && is_array( $attributes['Artist'] ) && isset( $attributes['Title'] ) ) {
            $items[] = implode( ", ", $attributes['Artist'] );
            $items[] = $attributes['Title'];

        } elseif ( isset( $attributes['Artist'] ) && is_array( $attributes['Artist'] ) ) {
            $items[] = implode( ", ", $attributes['Artist'] );

        } elseif ( isset( $attributes['Artist'] ) && isset( $attributes['Title'] ) ) {
            $items[] = $attributes['Artist'] . ', ' . $attributes['Title'];

        } elseif ( isset( $attributes['Artist'] ) ) {
            $items[] = $attributes['Artist'];

        } elseif ( isset( $attributes['Title'] ) ) {
            $items[] = $attributes['Title'];
        }

        if ( isset( $attributes['Label'] ) )
            $items[] = $attributes['Label'];

        if ( isset( $attributes['Binding'] ) )
            $items[] = $attributes['Binding'];
    }

    // Downloadable Music
    if ( 'DOWNLOADABLE_MUSIC_TRACK' === $type ) {

	    if ( isset( $attributes['Creator'] ) )
		    $items[] = $attributes['Creator'];

	    if ( isset( $attributes['Studio'] ) )
		    $items[] = $attributes['Studio'];

	    if ( isset( $attributes['Binding'] ) )
		    $items[] = $attributes['Binding'];

	    if ( isset( $attributes['PublicationDate'] ) )
		    $items[] = sprintf( esc_html__('Released on %s', 'aawp'), aawp_date( $attributes['PublicationDate'], $store ) );
    }

    // Toys & Games
    if ( 'TOYS_AND_GAMES' === $type ) {

        if ( isset( $attributes['Binding'] ) )
            $items[] = $attributes['Binding'];

        if ( isset( $attributes['Publisher'] ) )
            $items[] = $attributes['Publisher'];
    }

    // Toys & Games
    if ( $type == 'SHOES' ) {

        if ( isset( $attributes['Brand'] ) )
            $items[] = $attributes['Brand'];

        if ( isset( $attributes['Size'] ) )
            $items[] = $attributes['Size'];

        if ( isset( $attributes['Color'] ) )
            $items[] = $attributes['Color'];
    }

    // Fallback if nothing matches
    if ( sizeof( $items ) == 0 ) {

        if ( isset( $attributes['Author'] ) )
            $items[] = ( is_array( $attributes['Author'] ) ) ? implode(', ', $attributes['Author'] ) : $attributes['Author'];

        if ( isset( $attributes['Publisher'] ) )
            $items[] = $attributes['Publisher'];

        if ( isset( $attributes['Binding'] ) )
            $items[] = $attributes['Binding'];

        if ( isset( $attributes['Edition'] ) && isset( $attributes['PublicationDate'] ) ) {
            $edition_num = preg_replace("/[^0-9]/", "", $attributes['Edition'] );
            $items[] = sprintf( esc_html__('Edition no. %d', 'aawp'), $edition_num ) . ' (' . aawp_date( $attributes['PublicationDate'], $store ) . ')';
        }

        if ( isset( $attributes['Languages']['Language'] ) && is_array( $attributes['Languages']['Language'] ) && sizeof( $attributes['Languages']['Language'] ) != 0 && ! $teaser)
            $items[] = aawp_get_description_attribute_languages( $attributes['Languages']['Language'] );
    }

    //aawp_debug( $items );

    // Remove duplicates
    $items = array_values( array_unique( $items ) );

    return $items;
}

/**
 * Get description attribute languages
 *
 * @param $data
 *
 * @return string
 */
function aawp_get_description_attribute_languages($data) { // TODO: Deprecated?
    $languages = '';

    if ( isset($data['Name'] ) ) {
        // only one language
        return $data['Name'];
    } else {
        // more than one language available
        foreach ($data as $language) {

            if (isset( $language['Name'] ) && strpos( $languages, $language['Name'] ) === false) {
                if ($languages != '') {
                    $languages .= ', ';
                }

                $languages .= $language['Name'];
                // TODO: Maybe sort by name
            }
        }

        return $languages;
    }
}

/**
 * Get product image ids from image urls
 *
 * @param $image_urls
 * @param bool $return_as_string
 * @return array|string|null
 */
function aawp_get_product_image_ids_from_urls( $image_urls, $return_as_string = false ) {

    if ( empty ( $image_urls ) || ! is_array( $image_urls ) || sizeof( $image_urls ) === 0 )
        return null;

    $image_ids = array();

    $image_search_replace = array(
        'https://m.media-amazon.com/images/I/' => '', // Default media CDN
        '.jpg' => '', // Default file extension
        '._SL500_' => '' // Not needed filename string
    );

    foreach ( $image_urls as $image_url ) {

        if ( empty( $image_url ) )
            continue;

        $image_id = strtr( $image_url, $image_search_replace );

        if ( ! empty ( $image_id ) && ! in_array( $image_id , $image_ids ) )
            $image_ids[] = $image_id;
    }

    return ( $return_as_string ) ? implode(',', $image_ids ) : $image_ids;
}

/**
 * Build product image url based on image, size and api country
 *
 * @param string $image_id
 * @param string $size
 * @return null|string
 */
function aawp_build_product_image_url( $image_id, $size = 'medium' ) {

    if ( empty( $image_id ) )
        return null;

    $is_png = ( substr( $image_id, -strlen( '.png' ) ) === '.png' ) ? true : false;

    if ( $is_png )
        $image_id = strtr( $image_id, '.png', '' );

    $image_url = aawp_get_product_image_source();

    if ( empty( $image_url ) )
        return null;

    $image_url .= $image_id;

    if ( 'small' === $size ) {
        $image_url .= '._SL75_';
    } elseif ( 'medium' === $size ) {
        $image_url .= '._SL160_';
    }

    $image_url .= ( $is_png ) ? '.png' : '.jpg';

    return $image_url;
}

/**
 * Get image sources for all available CDN endpoints
 *
 * @return string/null
 */
function aawp_get_product_image_source() {
    return 'https://m.media-amazon.com/images/I/';

    // TODO: Deprecated CDNs?

    $country = aawp_get_amazon_store();

    if ( empty( $country ) )
        return null;

    // Defining endpoint
    if ( 'cn' === $country ) {
        $endpoint = 'cn';
    } elseif ( 'co.jp' === $country ) {
        $endpoint = 'fe';
    } elseif ( in_array( $country, aawp_get_amazon_euro_countries() ) ) {
        $endpoint = 'eu';
    } else {
        $endpoint = 'na';
    }

    // Defining sources available
    $sources_available = array(
        'na' => 'https://images-na.ssl-images-amazon.com/images/I/',
        'eu' => 'https://images-eu.ssl-images-amazon.com/images/I/',
        'cn' => 'https://images-cn.ssl-images-amazon.com/images/I/',
        'fe' => 'https://images-fe.ssl-images-amazon.com/images/I/'
    );

    return ( isset( $sources_available[$endpoint] ) ) ? $sources_available[$endpoint] : null;
}

/**
 * Get product image url served locally on our site
 *
 * @param $image_id
 * @param string $size
 *
 * @return string
 */
function aawp_build_product_local_image_url( $image_id, $size = 'medium' ) {

	$file_name = aawp_cleanup_product_image_id( $image_id );

	if ( 'small' === $size ) {
		$file_name .= '._SL75_';
	} elseif ( 'medium' === $size ) {
		$file_name .= '._SL160_';
	}

	$file_name .= '.jpg';

	if ( aawp_product_local_image_exists( $file_name ) )
		return aawp_get_product_local_image_url( $file_name );

	$remote_image_url = aawp_build_product_image_url( $image_id, $size );

	$downloaded_image = aawp_download_product_image( $file_name, $remote_image_url );

	return ( is_array( $downloaded_image ) && isset( $downloaded_image['url'] ) ) ? $downloaded_image['url'] : '';
}

/**
 * Check whether the usage of product local images is enabled or not
 *
 * @return bool
 */
function aawp_is_product_local_images_enabled() {
	return apply_filters( 'aawp_product_local_images_enabled', false );
}

/**
 * Check whether product images cache is activated or not
 *
 * @return bool
 */
function aawp_is_product_local_images_activated() {

	if ( ! aawp_is_product_local_images_enabled() )
		return false;

	$local_images = aawp_get_option( 'local_images', 'general' );

	return ( '1' == $local_images ) ? true : false;
}

/**
 * @return string
 */
function aawp_get_product_local_images_dirname() {
	return 'aawp/products';
}

/**
 * Get uploads course images path
 *
 * @return null|string
 */
function aawp_get_product_local_images_path() {

	$upload_dir = wp_upload_dir();

	if ( $upload_dir['error'] !== false )
		return null;

	$path = trailingslashit( $upload_dir['basedir'] . '/' . aawp_get_product_local_images_dirname() );

	return $path;
}

/**
 * Check whether downloaded image already exists or not
 *
 * @param $file_name
 *
 * @return bool|null
 */
function aawp_product_local_image_exists( $file_name ) {

	$uploads_path = aawp_get_product_local_images_path();

	$file_path = $uploads_path . $file_name;

	return ( file_exists( $file_path ) ) ? true : false;
}

/**
 * Get uploads product images url
 *
 * @return null|string
 */
function aawp_get_product_local_images_url() {

	$upload_dir = wp_upload_dir();

	if ( $upload_dir['error'] !== false )
		return null;

	$path = trailingslashit( $upload_dir['baseurl'] . '/' . aawp_get_product_local_images_dirname() );

	return $path;
}

/**
 * Get uploads product image url
 *
 * @param $file_name
 *
 * @return null|string
 */
function aawp_get_product_local_image_url( $file_name ) {

	$uploads_url = aawp_get_product_local_images_url();

	$file_url = $uploads_url . $file_name;

	return $file_url;
}

/**
 * Download course image
 *
 * @param $file_name
 * @param $file_url
 *
 * @return array|null
 */
function aawp_download_product_image( $file_name, $file_url ) {

	// Download image
	$request = wp_remote_get( $file_url );

	$file = wp_remote_retrieve_body( $request );

	if ( ! $file )
		return null;

	// Upload image
	$file_extension = substr( $file_url , strrpos( $file_url, '.' ) + 1 );

	if ( ! in_array( $file_extension, array( 'jpg', 'jpeg', 'png' ) ) )
		return array( 'error' => __( 'Sorry, this file type is not permitted for security reasons.', 'aawp' ) );

	$file_upload_dir = aawp_get_product_local_images_path();

	$new_file = $file_upload_dir . $file_name;

	// Are we able to create the upload folder?
	if ( ! wp_mkdir_p( $file_upload_dir ) ) {
		return array( 'error' => sprintf(
		/* translators: %s: directory path */
			__( 'Unable to create directory %s. Is its parent directory writable by the server?', 'aawp' ),
			$file_upload_dir
		) );
	}

	// Are we able to create the file?
	$ifp = @ fopen( $new_file, 'wb' );

	if ( ! $ifp )
		return array( 'error' => sprintf( __( 'Could not write file %s', 'aawp' ), $new_file ) );

	// Finally write the file
	@fwrite( $ifp, $file );
	fclose( $ifp );
	clearstatcache();

	// Set correct file permissions
	$stat = @ stat( dirname( $new_file ) );
	$perms = $stat['mode'] & 0007777;
	$perms = $perms & 0000666;
	@ chmod( $new_file, $perms );
	clearstatcache();

	// Prepare uploaded file
	$file_upload_url = aawp_get_product_local_images_url();

	$file_url = $file_upload_url . $file_name;

	$upload = array(
		'path' => $new_file,
		'url' => $file_url,
		'type' => $file_extension,
		'error' => false
	);

	return $upload;
}

/**
 * Cleanup product image id
 *
 * @param $image_id
 *
 * @return mixed
 */
function aawp_cleanup_product_image_id( $image_id ) {

	$image_id = str_replace('%2B', '+', $image_id );

	return $image_id;
}

/**
 * Cleanup product price
 *
 * @param $price
 * @return float|int
 */
function aawp_cleanup_product_price_amount( $price ) {

    if ( empty ( $price ) )
        return 0;

    $price = str_replace( ',', '.', $price );

    return (float) $price;
}

/**
 * Cleanup product title
 *
 * @param $title
 * @return string
 */
function aawp_cleanup_product_title( $title ) {

    if ( empty( $title ) )
        return $title;

    $title = aawp_strip_text_formatting( $title );

    return $title;
}

/**
 * Cleanup product features
 *
 * @param array/string $features
 * @return string
 */
function aawp_cleanup_product_features( $features ) {

    if ( empty( $features ) )
        return $features;

    if ( is_array( $features ) && sizeof( $features ) > 0 ) {

        foreach ( $features as $key => $feature ) {
            $features[$key] = aawp_strip_text_formatting( $feature );
        }

    } elseif ( is_string( $features ) ) {
        $features = aawp_strip_text_formatting( $features );
    }

    return $features;
}