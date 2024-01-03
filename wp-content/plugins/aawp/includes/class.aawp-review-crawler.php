<?php
/**
 * Class AAWP_Review_Crawler
 */
class AAWP_Review_Crawler {

    /**
     * Credentials: Country (Store)
     *
     * @var string
     */
    protected $api_country;

    /**
     * Debug switch (default set to false)
     *
     * @var bool
     */
    protected $debug = false;

    /**
     * AAWP_API constructor.
     */
    public function __construct() {

        $this->api_country = aawp_get_amazon_store();

        if ( aawp_is_debug() )
            $this->debug = true;
    }

    /**
     * Get crawled data from product page
     *
     * @param $asin
     * @return array|bool|null
     */
    public function get_data( $asin ) {

        if ( empty( $asin ) || empty ( $this->api_country ) )
            return null;

        $rating = false;

        $url = 'https://www.amazon.' . $this->api_country . '/product-reviews/' . $asin;

        // First try: wp_remote_get
        if ( function_exists( 'wp_remote_get' ) ) {

            $response = wp_remote_get( $url );
            $statusCode = null;

            if ( function_exists( 'is_wp_error' ) && ! is_wp_error( $response ) ) {

                // echo $response['response']['message'];

                // Success
                if ( isset( $response['response']['code'] ) ) {
                    $statusCode = $response['response']['code'];
                }

                if ( '200' == $statusCode ) {
                    $page = $response['body'];
                }
            }
        }

        /* Fallback: Get file by old fashioned way
        if ( ! isset ( $page ) ) {
            $page = $this->get_file($url, NULL);
        }*/

        if ( ! empty ( $page ) ) {
            $rating = $this->extract_data_from_html( $page );

            /*
            echo 'wp_remote_get try >> Result:<br>';
            var_dump( $rating );
            echo '<br>';
            */
        }

        // Fallback if no reviews are available
        if ( $rating === false ) {

            if ( ini_get('allow_url_fopen') ) {

                try {
                    // Trying to use file_get_contents
                    $opts = array(
                        'http'=>array(
                            'header' => 'Connection: close',
                            'ignore_errors' => true
                        )
                    );
                    $context = stream_context_create($opts);
                    @$page = file_get_contents($url, false, $context);

                    $rating = $this->extract_data_from_html( $page );

                    /*
                    echo 'allow_url_fopen try >> Result:<br>';
                    var_dump( $rating );
                    echo '<br>';
                    */

                } catch(Exception $ex) {
                    // Do nothing
                }
            }
        }

        // Finish
        //echo 'Result for ASIN: <a href="' . $url . '" target="_blank">' .$asin .'</a>:<br>';
        //var_dump($rating);

        return $rating;
    }

    /**
     * Extract data from HTML
     *
     * @param $html
     * @return array
     */
    private function extract_data_from_html( $html ) {

        $data = array(
            'rating' => 0,
            'reviews' => 0
        );

        if ( ! class_exists( 'DomDocument' ) || ! class_exists( 'DOMXPath' ) || ! function_exists( 'libxml_use_internal_errors' ) )
            return $data;

        libxml_use_internal_errors(true);

        $DomDocument = new DomDocument();
        $DomDocument->loadHTML( $html );
        $DomXPath = new DOMXPath( $DomDocument );

        $ratingNodeList = $DomXPath->query( "//i[contains(@class, 'averageStarRating')]" );

        if ( ! empty ( $ratingNodeList ) ) {

            foreach ( $ratingNodeList as $node ) {
                $string = $node->nodeValue; // Returns "4,6 von 5 Sternen"
                $string_array = explode(' ', $string ); // Explode after first white space, to get the rating only
                $rating = $string_array[0];
                $rating = str_replace(',','.', $rating ); // Replace comma with dot formatting
                $data['rating'] = $rating;
                break;
            }
        }

        $reviewsNodeList = $DomXPath->query( "//div[contains(@class, 'averageStarRatingNumerical')]" );

        if ( ! empty ( $reviewsNodeList ) ) {

            foreach ( $reviewsNodeList as $node ) {
                $string = $node->nodeValue;
                $string = preg_replace('/\D/', '', $string);
                $data['reviews'] = $string;
                break;
            }
        }

        return $data;
    }
}