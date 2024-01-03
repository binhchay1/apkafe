<?php
/**
 * Template Functions
 *
 * @package     AAWP
 * @since       3.4.0
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'AAWP_Template_Functions') ) {

    class AAWP_Template_Functions extends AAWP_Functions {

        public $store;

        public $atts;
        public $type;

        public $items; // Array of product arrays
        /** @var AAWP_Product $item */
        public $item; // Product Class
        public $item_index; // Pointer on current item inside an array

        public $request_keys;

        // Global variables
        public $options;

        // Template Variables
        public $layout_template;
        public $product_template;
        public $shortcode_template;

        /**
         * Constructor
         */
        public function __construct() {

            // Call parent constructor first
            parent::__construct();

            // Prepare variables
            $this->options = aawp_get_options();

            //$this->store = ( isset( $atts['store'] ) ) ? esc_html( $atts['store'] ) : $this->options['api']['country'];
        }

        /**
         * Get template part
         *
         * @param null $slug
         * @param bool $load
         *
         * @return mixed
         *
         * Since 3.2.0
         */
        public function get_template_part( $slug = null, $load = false ) {

            if ( empty( $slug ) && ! $load )
                $load = true;

            // Setup possible parts
            $templates = array();

            if ( ! empty( $slug ) )
                $templates[] = $slug . '.php';

            // Return the part that is found
            return $this->locate_template( $templates, $load, false );
        }

        /**
         * Retrieve the name of the highest priority template file that exists.
         *
         * @param $template_names
         * @param bool $load
         * @param bool $require_once
         *
         * @return bool|string
         *
         * Since 3.2.0
         */
        private function locate_template( $template_names, $load = false, $require_once = true ) {

            // No file found yet
            $located = false;

            $template_stack = array();

            // check custom directories
            $template_stack = apply_filters( 'aawp_template_stack', $template_stack, $template_names );

            // check plugin directory
            $template_stack[] = trailingslashit( AAWP_PLUGIN_DIR . 'templates/parts' );
            $template_stack[] = trailingslashit( AAWP_PLUGIN_DIR . 'templates/products' );
            $template_stack[] = trailingslashit( AAWP_PLUGIN_DIR . 'templates' );

            // check plugin directory for deprecated templates as well
            $template_stack[] = trailingslashit( AAWP_PLUGIN_DIR . 'templates/deprecated' );

            //aawp_debug( $template_stack );

            // Try to find a template file
            foreach ( (array) $template_names as $template_name ) {

                // Continue if template is empty
                if ( empty( $template_name ) ) {
                    continue;
                }

                // Trim off any slashes from the template name
                $template_name = ltrim( $template_name, '/' );

                // Loop through template stack.
                foreach ( (array) $template_stack as $template_location ) {

                    // Continue if $template_location is empty.
                    if ( empty( $template_location ) ) {
                        continue;
                    }

                    // Check locations
                    if ( file_exists( trailingslashit( $template_location ) . $template_name ) ) {
                        $located = trailingslashit( $template_location ) . $template_name;
                        break 2;
                    }
                }
            }

            if ( true == $load ) {
                if ( ! empty( $located ) ) {
                    include $located;
                } else {
                    _e('Template not found.', 'aawp');
                }
            } else {
                return $located;
            }
        }

        /**
         * Setup product for usage in template
         *
         * @param $i
         * @param $product_id
         */
        public function setup_item ( $i, $item ) {
            $this->item = new AAWP_Product( $item );
            $this->item_index++;

            $timestamp = $this->item->get_timestamp();

            $this->timestamp_product = ( ! empty( $timestamp ) ) ? $timestamp : 0;
        }

        /**
         * Product template render function
         *
         * @param null $template
         */
        public function the_product_template( $template = null ) {

            if ( empty( $template ) && ! empty ( $this->product_template ) && ( $this->layout_template != 'widget' || strpos($this->product_template, 'widget-') !== false || ! empty( $this->shortcode_template ) ) )
                $template = $this->product_template;

            //echo 'the_product_template: ' . $template . '<br>';

            // Allow dynamic selection
            $template = apply_filters( 'aawp_product_template', $template, $this->atts );

            //echo 'the_product_template (filtered): ' . $template . '<br>';

            if ( empty ( $template ) ) {
                if ( 'widget' === $this->layout_template ) {
                    $template = 'widget-vertical';
                } else {
                    $template = 'horizontal';
                }
            }

            // Load template
            $this->get_template_part( 'products/' . $template, $load = true );
        }

        /**
         * Get layout template wrapper classes
         *
         * @param $classes
         *
         * @return string
         */
        public function get_wrapper_classes( $classes ) {

            $classes = apply_filters( 'aawp_template_wrapper_classes', $classes, $this->layout_template, $this->atts );

            return $classes;
        }

        /**
         * Collecting product container classes
         *
         * @param string $classes
         *
         * @return mixed|string
         */
        public function get_product_container_classes( $classes = 'aawp-product' ) {

            $class_extensions = array();

            if ( $this->get_product_is_sale() )
                array_push( $class_extensions, 'ribbon', 'sale' );

            if ( $this->get_inline_info() && ! empty ( $this->options['general']['inline_info_text'] ) )
                $class_extensions[] = 'inline-info';

            if ( $style = $this->get_style() )
                $class_extensions[] = 'style-' . $style;

            if ( ( isset( $this->atts['image_size'] ) && 'large' === $this->atts['image_size'] ) || ( isset( $this->options['output']['image_size'] ) && 'large' == $this->options['output']['image_size'] ) ) {
                $class_extensions[] = 'css-adjust-image-large';
            }

            // Hook for manipulation the extensions
            $class_extensions = apply_filters( 'aawp_template_container_class_extensions', $class_extensions, $this->type, $this->atts );

            //aawp_debug($class_extensions);

            // Adding the class extensions to the default classes
            if ( is_array( $class_extensions ) && sizeof( $class_extensions ) > 0 )  {

                foreach ( $class_extensions as $extension ) {
                    $classes .= ' aawp-product--' . str_replace( '_', '-', $extension);
                }
            }

            // Hook for manipulation the classes
            $classes = apply_filters( 'aawp_template_container_classes', $classes, $this->type, $this->atts );

            // Finally adding custom classes coming from shortcode attributes
            if ( ! empty( $this->atts['class'] ) ) {
                $custom_classes = sanitize_text_field( $this->atts['class'] );
                $custom_classes = str_replace( ",", " ", $custom_classes ); // Transform comma to space
                $classes .= ' ' . rtrim ( $custom_classes );
            }

            // Finish
            return $classes;
        }

        /**
         * Get product box style
         *
         * @return string
         */
        public function get_style() {

            $style = ( !empty ( $this->options['functions'][$this->type . '_style'] ) ) ? $this->options['functions'][$this->type . '_style'] : null;

            // Remove standard style if set
            $style = ( 'standard' != $style && '0' != $style ) ? $style : null;

            return ( !empty( $this->atts['style'] ) ) ? $this->atts['style'] : $style;
        }

        /**
         * Display the product box ribbons
         */
        public function the_product_ribbons() {

            // Possible ribbon types
            $ribbon_types = array(
                'sale'
            );

            // Allow adding more
            $ribbon_types = apply_filters( 'aawp_func_ribbon_types', $ribbon_types, $this->item_type );

            // Loop types and maybe display each of them
            foreach ( $ribbon_types as $type ) {
                $this->display_ribbon( $type );
            }
        }

        /**
         * Display a single product box ribbon
         *
         * @param string $type
         */
        public function display_ribbon( $type = 'sale' ) {

            $ribbon = $this->get_ribbon( $type );

            if ( $ribbon && ! empty( $ribbon['type'] ) && ! empty( $ribbon['text'] ) ) {
                echo '<span class="aawp-product__ribbon aawp-product__ribbon--' . $ribbon['type'] . '">' . $ribbon['text'] . '</span>';
            }
        }

        /**
         * Return a single ribbon
         *
         * @param string $type
         *
         * @return array|bool|mixed
         */
        public function get_ribbon( $type = 'sale' ) {

            $ribbon = false;

            if ( 'sale' === $type )
                $ribbon = $this->get_sale_ribbon();

            $ribbon = apply_filters( 'aawp_func_ribbon', $ribbon, $type );

            if ( isset( $ribbon['text'] ) && strpos( $ribbon['text'], '%NUMBER%' ) !== false) {
                $ribbon['text'] = str_replace( '%NUMBER%', $this->item_index, $ribbon['text'] );
            }

            if ( isset( $ribbon['text'] ) && strpos( $ribbon['text'], '%PRICE_REDUCTION%' ) !== false ) {
                $price_reduction = $this->get_product_sale_discount();
                $price_reduction_text = ( ! empty( $price_reduction ) ) ? $price_reduction : '';
                $ribbon['text'] = str_replace( '%PRICE_REDUCTION%', $price_reduction_text, $ribbon['text'] );
            }

            return $ribbon;
        }

        /**
         * Get sale ribbon
         *
         * @return array|bool
         */
        public function get_sale_ribbon() {

            if ( ! $this->get_product_is_sale() )
                return false;

            // Overwrite text via shortcode
            if ( isset( $this->atts['sale_ribbon'] ) && 'none' === $this->atts['sale_ribbon'] ) {
                return false;
            } elseif ( isset( $this->atts['sale_ribbon_text'] ) ) {
                $ribbon_text = esc_html( $this->atts['sale_ribbon_text'] );
            } else {
                $ribbon_text = ( isset ( $this->options['output']['pricing_sale_ribbon_text'] ) ) ? $this->options['output']['pricing_sale_ribbon_text'] : __( 'Sale', 'aawp' );
            }

            if ( empty( $ribbon_text ) )
                return false;

            $ribbon = array(
                'type' => 'sale',
                'class' => 'aawp-product__ribbon aawp-product__ribbon--sale',
                'text' => $ribbon_text
            );

            return $ribbon;
        }

        /**
         * Get product type
         *
         * @return string
         */
        public function get_product_type() {
            return $this->item->get_type();
        }

        /**
         * Get EAN
         */
        public function get_product_ean() {
            return $this->item->get_ean();
        }

        /**
         * Get ISBN
         */
        public function get_product_isbn() {
            return $this->item->get_isbn();
        }

        /**
         * Get product url
         *
         * @param string $type
         * @param string $origin
         *
         * @return mixed|null|string
         */
        public function get_product_url( $type = 'basic', $origin = 'default' ) {

            if ( ! empty ( $this->atts['link_overwrite'] ) )
                return $this->atts['link_overwrite'];

            $type = apply_filters( 'aawp_func_product_url_type', $type, $origin, $this->atts );

            $url = $this->item->get_url( $type );

            // Get tracking id
            $tracking_id = ( ! empty( $this->atts['tracking_id'] ) ) ? trim( $this->atts['tracking_id'] ) : aawp_get_default_tracking_id();

            // Replace placeholder tracking id
            $url = aawp_replace_url_tracking_id_placeholder( $url, $tracking_id, false );

            // Hook
            $url = apply_filters( 'aawp_template_product_url', $url, $type, $this->atts );

            return $url;
        }

        /**
         * Get product image
         *
         * @param string $size
         *
         * @return mixed|string
         */
        public function get_product_image( $size = '' ) {

        	$custom_image = ( ! empty( $this->atts['image'] ) && is_string( $this->atts['image'] ) && strpos( $this->atts['image'], 'http') !== false ) ? $this->atts['image'] : false;

	        // Custom image via shortcode
	        //if ( ! empty( $this->atts['image'] ) && is_string( $this->atts['image'] ) && strpos( $this->atts['image'], 'http') !== false )
		      //  return esc_html( $this->atts['image'] );

	        if ( $custom_image && strpos( $custom_image, 'images-amazon.com') === false && $custom_image && strpos( $custom_image, 'media-amazon.com' ) === false ) {
		        return $custom_image;
	        }

	        $image = ( $custom_image ) ? $custom_image : null;

        	if ( ! $image ) {

		        // Size
		        if ( ! empty( $this->atts['image_size'] ) ) {
			        $size = $this->atts['image_size'];

		        } elseif ( empty( $size ) ) {

			        if ( isset( $this->options['output']['image_quality'] ) && 'high' == $this->options['output']['image_quality'] ) {
				        $size = 'large';
			        } else {
				        $size = ( isset( $this->options['output']['image_size'] ) ) ? $this->options['output']['image_size'] : 'medium';
			        }
		        }

		        // Number
		        $number = ( ! empty( $this->atts['image'] ) && is_numeric( $this->atts['image'] ) ) ? intval( $this->atts['image'] ) : 1;

		        // Get image
		        $image = $this->item->get_image( $number, $size );

		        // If no image was found, return placeholder
		        if ( empty( $image ) )
			        return apply_filters( 'aawp_func_product_image_placeholder', AAWP_PLUGIN_URL . 'assets/img/placeholder-' . $size . '.jpg' );
	        }

	        // Maybe use image proxy
	        if ( '1' == aawp_get_option( 'image_proxy', 'output' ) && ini_get('allow_url_fopen') ) {

	            /*
	            aawp_debug_log( 'Image Proxy >> $image: ' . $image );
                aawp_debug_log( 'Image Proxy >> base64_encode($image): ' . base64_encode( $image ) );
                aawp_debug_log( 'Image Proxy >> rawurlencode($image): ' . rawurlencode( $image ) );
                aawp_debug_log( 'Image Proxy >> rawurlencode(base64_encode($image)): ' . rawurlencode( base64_encode( $image ) ) );
                */

		        $image = add_query_arg( array(
			        'url' => base64_encode( $image )
		        ), aawp_get_public_url() . 'image.php' );
	        }

            // Return final image
            return apply_filters( 'aawp_func_product_image', $image );
        }

        /**
         * Get amount of available product images
         *
         * @return int
         */
        public function get_product_image_count() {
            return $this->item->get_image_count();
        }

        /**
         * Get product image alt tag
         *
         * @return mixed|string
         */
        public function get_product_image_alt() {

            if ( !empty( $this->atts['image_alt'] ) )
                return $this->cleanup_html_attributes( $this->atts['image_alt'] );

            $product_title = $this->get_product_title();

            $image_alt = rtrim( $product_title, $this->title_adding );

            // Cleanup
            $image_alt = $this->cleanup_html_attributes( $image_alt);

            return $image_alt;
        }

        /**
         * Get product image link url
         *
         * @return mixed|null|string
         */
        public function get_product_image_link() {
            return ( ! empty( $this->atts['image_link'] ) ) ? $this->atts['image_link'] : $this->get_product_url();
        }

        /**
         * Get product image link title
         *
         * @return mixed|null|string
         */
        public function get_product_image_link_title() {

            $image_link_title = $this->get_product_title();

            if ( $this->image_link_title_adding == 0 ) {
                $image_link_title = rtrim( $image_link_title, $this->title_adding );
            }

            // Cleanup
            $image_link_title = $this->cleanup_html_attributes( $image_link_title);

            return $image_link_title;
        }

        /**
         * Get product image title tag
         *
         * @return string
         */
        public function get_product_image_title() {

            $image_title = ( ! empty( $this->atts['image_title'] ) ) ? sanitize_text_field( $this->atts['image_title'] ) : '';

            $image_title = $this->cleanup_html_attributes( $image_title );

            return $image_title;
        }

        /**
         * Maybe output product image title tag
         */
        public function the_product_image_title() {
            $image_title = $this->get_product_image_title();

            if ( ! empty ( $image_title ) ) {
                echo 'title="' . $image_title . '"';
            }
        }

        /**
         * Get product thumb as HTML
         *
         * @param string $size
         *
         * @return string
         */
        public function get_product_thumb( $size = 'medium' ) {

            if ( isset( $this->atts['image_size'] ) )
                $size = $this->atts['image_size'];

            $image = $this->get_product_image( $size );

            if ( $image ) {
                $image_alt = $this->get_product_image_alt();
                $image_classes = ( !empty( $this->atts['image_class'] ) ) ? sanitize_text_field( $this->atts['image_class'] ) : '';
                $image_title = $this->get_product_image_title();
                $image_width = null;
                $image_height = null;

                // Handling aligns
                if ( isset( $this->atts['image_align'] ) ) {

                    $image_align_class = '';

	                if ( 'center' === $this->atts['image_align'] )
		                $image_align_class = 'aligncenter';

                    if ( 'left' === $this->atts['image_align'] )
                        $image_align_class = 'alignleft';

                    if ( 'right' === $this->atts['image_align'] )
                        $image_align_class = 'alignright';

                    if ( ! empty( $image_align_class ) )
                        $image_classes = ( ! empty( $image_classes ) ) ? $image_classes . ' ' . $image_align_class : $image_align_class;
                }

                // Handling sizes
                $image_width = ( isset( $this->atts['image_width'] ) && is_numeric( $this->atts['image_width'] ) ) ? intval( $this->atts['image_width'] ) : 0;
                $image_height = ( isset( $this->atts['image_height'] ) && is_numeric( $this->atts['image_height'] ) ) ? intval( $this->atts['image_height'] ) : 0;

                // Building basic image
                $thumb = '<img src="' . $image . '" alt="' . $image_alt . '"';

                if ( ! empty ( $image_classes ) )
                    $thumb .= ' class="' . $image_classes . '"';

                if ( ! empty ( $image_title ) )
                    $thumb .= ' title="' . $image_title . '"';

                if ( $image_width && $image_height )
                    $thumb .= ' width="' . $image_width . '" height="' . $image_height . '"';

                $thumb .= ' />';

                // Building image link
                $image_link = $this->get_product_image_link();
                $image_link_title = $this->get_product_image_link_title();

                if ( !empty( $image_link) && $image_link != 'none' ) {
                    $thumb = '<a href="' . $image_link . '" title="' . $image_link_title . '" rel="nofollow noopener sponsored" target="_blank">' . $thumb . '</a>';
                }

                return $thumb;
            } else {
                return null;
            }
        }

        /**
         * Get product title
         *
         * @param bool $intact
         *
         * @return null|string
         */
        public function get_product_title( $intact = false ) {

            $title = $this->item->get_title();

            if ( $intact ) {

                $title = str_replace (array( '(', ')', ';', '"', "'", ':', ', ', '. ' ), ' ', $title );
                $title = trim( $title );

                return ( ! empty( $title ) ) ? $title : null;

            // Overwritten via shortcode
            } elseif ( ! empty( $this->atts['title'] ) ) {
                return $this->atts['title'] . $this->title_adding;

            } else {

                if ( ! empty( $this->atts['title_length'] ) ) {
                    $title = $this->truncate($title, $this->atts['title_length'], 'title');

                } elseif ( $this->title_length_unlimited != 1 ) {

                    if ( !empty( $this->title_length ) && strlen( $title ) > $this->title_length )
                        $title = $this->truncate($title, $this->title_length, 'title');
                }

                return $title . $this->title_adding;
            }
        }

        /**
         * Get product link
         *
         * @param string $type
         *
         * @return string
         */
        public function get_product_link( $type = 'basic' ) {

            $link = null;

            $url = $this->get_product_url( $type );
            $title = $this->get_product_title();

            if ( $url && $title ) {
                $link = '<a href="' . $url . '" title="' . $title . '" target="_blank" rel="nofollow noopener sponsored">' . $title . '</a>';
            }

            return apply_filters( 'aawp_func_product_link', $link, $type );
        }

        /**
         * Get product link title
         *
         * @return mixed|null|string
         */
        public function get_product_link_title() {

            if ( ! empty( $this->atts['link_title'] ) )
                return $this->cleanup_html_attributes( $this->atts['link_title'] );

            $link_title = $this->get_product_title();

            // Cleanup title
            $link_title = $this->cleanup_html_attributes( $link_title );

            // Remove title adding
            $link_title = rtrim( $link_title, $this->title_adding );

            return $link_title;
        }

        /**
         * Get product description
         *
         * @param bool $html
         *
         * @return array|null|string
         */
        public function get_product_description( $html = true ) {

            // Return if description should be skipped
            if ( ( isset( $this->atts['description'] ) && $this->atts['description'] == 'none') || ( isset( $this->atts['description_items'] ) && ( intval( $this->atts['description_items'] ) === 0 || $this->atts['description_items'] == 'none') ) )
                return null;

            // Custom overwrite
            if ( !empty( $this->atts['description'] ) ) {
                return $this->atts['description'];

            // Loop items
            } else {

                $description = $this->item->get_description( array( 'html' => $html ) );

                // Add custom adding
                if ( isset ( $this->atts['description_text'] ) )
                    $description .= '<p class="description-text">' . $this->atts['description_text'] . '</p>';

                // Check and output
                if ( ! empty ( $description ) )
                    return $description;
            }

            return null;
        }

        /**
         * Get product attributes
         *
         * @param bool $key
         *
         * @return mixed|null
         */
        public function get_product_attributes( $key = false ) {

            $attributes = $this->get_attributes();

            if ( $key && isset( $attributes[$key] ) )
                return $attributes[$key];

            return $attributes;
        }

        /**
         * Get attributes of a product
         *
         * @return mixed|null
         */
        private function get_attributes() {
            return $this->item->get_attributes();
        }

        /**
         * get product teaser
         *
         * @param string $format
         *
         * @return array|bool|null|string
         */
        public function get_product_teaser( $format = 'list' ) {

            $teaser = null;

            // Overwrite by shortcode
            if ( isset( $this->atts['teaser'] ) && 'none' === $this->atts['teaser'] )
                return null;

            // Hiding teaser via description="none" as well
            if ( isset( $this->atts['description'] ) && 'none' === $this->atts['description'] )
                return null;

            // Custom teaser provided?
            if ( isset( $this->atts['teaser'] ) ) {
                return esc_html( $this->atts['teaser'] );

            } else {

                // Hidden via settings?
                if ( ! $this->teaser )
                    return null;

                $teaser = $this->item->get_teaser();

                // Maybe truncate
                if ( ! empty ( $teaser ) && ! $this->teaser_length_unlimited && ! empty ( $this->teaser_length ) ) {
                    $teaser = aawp_truncate_string( $teaser, $this->teaser_length );
                }

                // Format
                /* TODO: Deprecated?
                if ( 'paragraph' === $format ) {

                    if ( ! empty( $teaser ) && is_array( $teaser ) && sizeof( $teaser ) > 0 ) {
                        $teaser = implode( ' - ', $teaser );
                    } else {
                        $teaser = null;
                    }
                }
                */
            }

            return $teaser;
        }

        /**
         * Get product rating
         *
         * @param string $type
         *
         * @return float|null
         */
        public function get_product_rating( $type = 'rating' ) {

            if ( 'reviews' === $type )
                return $this->get_product_reviews();

            // Return false if star_rating and reviews are set to none
            //if ( ! empty( $this->atts['star_rating'] ) && $this->atts['star_rating'] === 'none' && ! empty( $this->atts['reviews'] ) && $this->atts['reviews'] === 'none' )
              //  return null;

            // Allow manual overwriting
            if ( 'rating' == $type && ! empty ( $this->atts['rating'] ) )
                return (float) $this->atts['rating'];

            // Get stored rating
            $rating = $this->item->get_rating();

            return ( $rating ) ;
        }

        /**
         * Get product reviews
         *
         * @param bool $show_label
         *
         * @return float|null|string
         */
        public function get_product_reviews( $show_label = true ) {

            if ( ! empty( $this->atts['reviews'] ) && $this->atts['reviews'] === 'none' )
                return null;

            if ( $this->show_reviews == '0' )
                return null;

            $count = $this->item->get_reviews();

            if ( ! empty( $count ) ) {

                $label = ( ! empty ( $this->options['output']['reviews_label'] ) ) ? $this->options['output']['reviews_label'] : __( 'Reviews', 'aawp' );

                return ( $show_label ) ? $this->format_number( $count, false ) . ' ' . $label : $count;
            }

            return null;
        }

        /**
         * Display product star rating
         *
         * @param array $args
         */
        public function the_product_star_rating( $args = array() ) {
            echo $this->get_product_star_rating( $args );
        }

        /**
         * Get product star rating
         *
         * @param array $args
         *
         * @return mixed|null|string
         */
        public function get_product_star_rating( $args = array() ) { // TODO: Replace in templates with "the_product_star_rating"

            if ( ! empty( $this->atts['star_rating'] ) && $this->atts['star_rating'] === 'none' )
                return null;

            // Default values
            if ( !isset($args['force']) ) $args['force'] = false;
            if ( !isset($args['link']) ) $args['link'] = true;

            // Start
            if ( $this->star_rating_size == '0' && $args['force'] == false )
                return null;

            $rating = $this->get_product_rating();

            if ( ! $rating )
                return null;

            //== Handle Rating value and fit Amazon Star Definitions (half vs. full)
            if ( floatval( $rating ) > 5 )
                $rating = 5;

            if ( strpos($rating, ',') !== false ) {
                $delimiter = ',';
            } else {
                $delimiter = '.';
            }

            $rating_parts = explode( $delimiter, $rating );

            if ( isset($rating_parts[1]) ) {

                if ( intval($rating_parts[1]) > 2 && intval($rating_parts[1]) < 8 ) {
                    $rating = $rating_parts[0] . '.5';
                } else {
                    $rating = round($rating);
                }
            } else {
                $rating = $rating_parts[0];
            }

            //== Convert rating to percentage
            $percentage = ( 100 * floatval( $rating ) ) / 5;

            //== Size
            if ( isset( $args['size'] ) ) {
                $size = $args['size'];
            } else if ( $args['force'] ) {
                $size = (!empty($this->atts['star_rating_size'])) ? $this->atts['star_rating_size'] : 'small';
            } else {
                $size = (!empty($this->atts['star_rating_size'])) ? $this->atts['star_rating_size'] : $this->star_rating_size;
            }

            //== Handle links
            $url = null;

            if ( ! empty( $this->atts['star_rating_link'] ) ) {
                // Hide link
                if ( 'none' == $this->atts['star_rating_link'] ) {
                    $url = false;
                } elseif ( 'reviews' == $this->atts['star_rating_link'] && $this->get_product_url('reviews') ) {
                    $url = $this->get_product_url('reviews');
                    // Detail page
                } elseif ( 'detail_page' == $this->atts['star_rating_link'] ) {
                    $url = $this->get_product_url();
                    // Post ID
                } elseif ( is_numeric( $this->atts['star_rating_link'] ) ) {
                    $url = get_permalink( intval ( $this->atts['star_rating_link'] ) );
                    // Custom URL
                } else {
                    $url = $this->atts['star_rating_link'];
                }

            } else {

                if ( 'reviews' == $this->star_rating_link && $this->get_product_url('reviews') ) {
                    $url = $this->get_product_url('reviews');
                } elseif ( 'detail_page' == $this->star_rating_link ) {
                    $url = $this->get_product_url();
                }
            }

            //== Return star rating maybe with link
            $classes = 'aawp-star-rating';
            $classes .= ' aawp-star-rating--' . $size;

            $classes = apply_filters( 'aawp_star_rating_classes', $classes, $this->atts );

            if ( ! empty ( $url ) && $args['link'] ) {
                $star_rating = '<a class="' . $classes . '" href="' . $url . '" title="' . __('Reviews on Amazon', 'aawp') . '" rel="nofollow noopener sponsored" target="_blank"><span style="width: ' . $percentage . '%;"></span></a>';
            } else {
                $star_rating = '<span class="' . $classes . '"><span style="width: ' . $percentage . '%;"></span></span>';
            }

            $star_rating = apply_filters( 'aawp_star_rating', $star_rating );

            return $star_rating;
        }

        /**
         * Check if advertised price should be shown
         *
         * @return bool
         */
        public function show_advertised_price() {

            if ( isset( $this->atts['price'] ) ) {

                if ( 'show' === $this->atts['price'] )
                    return true;

                if ( 'hide' === $this->atts['price'] )
                    return false;

                if ( 'none' === $this->atts['price'] ) // Deprecated
                    return false;
            }

            if ( $this->pricing_advertised_price === 'hidden' )
                return false;

            return true;
        }

        /**
         * Check if current product is on sale
         *
         * @return bool
         */
        public function product_is_on_sale() {

            $is_discounted = $this->item->is_discounted();

            return ( $is_discounted );
        }

        /**
         * Check if old price should be shown
         *
         * @return bool
         */
        public function sale_show_old_price() {
            return ( isset ( $this->options['output']['pricing_show_old_price'] ) && $this->options['output']['pricing_show_old_price'] == '1' ) ? true : false;
        }

        /**
         * Check if price reduction should be shown
         *
         * @return bool
         */
        public function sale_show_price_reduction() {
            return ( isset ( $this->options['output']['pricing_show_price_reduction'] ) && $this->options['output']['pricing_show_price_reduction'] == '1' ) ? true : false;
        }

        /**
         * Get product price
         *
         * @param string $type
         * @param string $format
         *
         * @return mixed|null|string
         */
        public function get_product_price( $type = 'display', $format = 'formatted' ) {

            // Custom pricing
            if ( 'display' === $type && ! empty( $this->atts['price'] ) && 'show' != $this->atts['price'] )
                return ( in_array( $this->atts['price'], array( 'hide', 'none' ) ) ) ? null : esc_html( $this->atts['price'] );

            // Deprecated pricing types
            if ( 'old' === $type ) {
                $type = 'list';
            } elseif ( in_array( $type, array( 'used' ) ) ) {
                return null;
            }

            // Default handling
            $return_formatted = ( 'formatted' === $format ) ? true : false;

            return $this->item->get_price( $type, $return_formatted );
        }

        /**
         * Get product price saving
         *
         * @param bool $formatted
         * @return int|mixed|null
         */
        public function get_product_price_saving( $formatted = true ) {
            return $this->item->get_price_savings( $formatted );
        }

        /**
         * Get product price saving percentage
         *
         * @param bool $formatted
         * @return float|int|string
         */
        public function get_product_price_saving_percentage( $formatted = true ) {
            return $this->item->get_price_savings_percentage( $formatted );
        }

        /**
         * Get product pricing
         *
         * @param string $type
         * @param string $format
         *
         * @return mixed|null|string
         */
        public function get_product_pricing( $type = 'display', $format = 'formatted' ) { // TODO: Replacing in templates
            return $this->get_product_price( $type, $format );
        }

        /**
         * Get product "saved" text
         *
         * @return string
         */
        public function get_saved_text() {
            //return sprintf( __( 'Saved %s', 'aawp' ), $this->get_product_sale_discount() );
            return '&#8722;' . $this->get_product_sale_discount();
        }

        /**
         * Get product sale discount
         *
         * @return mixed|null|string
         */
        public function get_product_sale_discount() {

            // Check if displaying discount not forbidden
            if ( $this->pricing_reduction != 'hidden' ) {
                $type = $this->pricing_reduction . '_saved';
            } else {
                return null;
            }

            $price_discount = ( 'percentage_saved' === $type ) ? $this->get_product_price_saving_percentage() : $this->get_product_price_saving();

            //$pricing = $this->get_product_pricing($type);

            return $price_discount;
        }

        /**
         * Maybe display product related prime logo
         *
         * @param bool $echo
         *
         * @return mixed
         */
        public function the_product_check_prime_logo($echo = true) {

            // Check if extended functions are available
            if ( ! function_exists( 'aawp_country_has_prime' ) || ! function_exists( 'aawp_get_amazon_prime_url' ) || ! function_exists( 'aawp_get_prime_check_logo' ) )
                return;

            // Check if prime is available
            if ( ! aawp_country_has_prime( $this->api_country ) || ! $this->product_is_prime() || ! $this->show_prime_logo() )
                return;

            $prime_logo = aawp_get_prime_check_logo( $this->atts );

            if ( ! $echo )
                return $prime_logo;

            echo $prime_logo;
        }

        /**
         * Check if the current product is prime
         *
         * @return bool
         */
        public function product_is_prime() {

            /* TODO: Deprecated ?
            $availability = $this->item->get_availability();

            if ( 'unknown' === $availability )
                return false;
            */

            return $this->item->is_prime();
        }

        /**
         * Display a button
         *
         * @param string $type
         */
        public function the_button( $type = 'standard' ) {
            echo $this->get_button( $type, $echo = false );
        }

        /**
         * Get button
         *
         * @param string $type
         * @param bool $echo
         *
         * @return mixed/null
         */
        public function get_button($type = 'standard', $echo = true ) { // TODO: Replace in templates

            $button_args = array();

            if ( $type == 'standard' ) {

                if ( ! empty( $this->atts['button'] ) && 'none' === $this->atts['button'] ) {
                    return null;
                }

                if ( isset( $this->options['output']['button_hide'] ) && '1' == $this->options['output']['button_hide'] )
                    return null;

                if ( ! empty( $this->atts['button_text'] ) ) {
                    $button_text = $this->atts['button_text'];
                } else {
                    $button_text = ( ! empty ( $this->options['output']['button_text'] ) ) ? $this->options['output']['button_text'] : __( 'Buy on Amazon', 'aawp' );
                }

                if ( strpos( $button_text, '%price%' ) !== false) {
                    $price_placeholder_text = $this->get_product_price();

                    if ( ! empty( $price_placeholder_text ) && preg_match('/[0-9]/', $price_placeholder_text) ) {
                        $button_text = str_replace('%price%', $price_placeholder_text, $button_text );
                    } else {
                        $button_text = str_replace('%price%', '', $button_text );
                    }
                }

                // Maybe overwrite button class
                if ( ! empty( $this->atts['button_class'] ) ) {
                    $button_classes = esc_html( $this->atts['button_class'] );

                // Or building our own classes
                } else {

                    $button_classes = 'aawp-button';

                    $button_classes .= ' aawp-button--buy';

                    // Style
	                if ( ! empty( $this->atts['button_style'] ) ) {
	                	$button_classes .= ' aawp-button aawp-button--' . $this->atts['button_style'];
	                } elseif ( ! empty ($this->options['output']['button_style']) && 'standard' != $this->options['output']['button_style'] ) {
		                $button_classes .= ' aawp-button aawp-button--' . $this->options['output']['button_style'];
	                }

                    if (isset ($this->options['output']['button_style_rounded']) && $this->options['output']['button_style_rounded'] == '1')
                        $button_classes .= ' rounded';

                    if (isset ($this->options['output']['button_style_shadow']) && $this->options['output']['button_style_shadow'] == '1')
                        $button_classes .= ' shadow';

                    // Icon
                    if ( isset( $this->atts['button_icon'] ) && 'hide' === $this->atts['button_icon'] ) {
                        $button_icon = false;
                    } elseif ( isset( $this->atts['button_icon'] ) && in_array( $this->atts['button_icon'], array( 'black', 'white', 'amazon-black', 'amazon-white' ) ) ) {
                        $button_icon = $this->atts['button_icon'];
                    } elseif ( ! isset( $this->atts['button_icon'] ) && isset( $this->options['output']['button_icon_hide'] ) && '1' == $this->options['output']['button_icon_hide'] ) {
                        $button_icon = false;
                    } elseif ( ! isset( $this->atts['button_icon'] ) || ( isset( $this->atts['button_icon'] ) && 'show' === $this->atts['button_icon'] ) ) {
                        $button_icon = ( isset ( $this->options['output']['button_icon'] ) ) ? $this->options['output']['button_icon'] : 'black';
                    }

                    if ( ! empty( $button_icon ) )
                        $button_classes .= ' aawp-button--icon aawp-button--icon-' . $button_icon;
                }

                $button_args = array(
                    'classes' => $button_classes,
                    'url' => $this->get_product_url('basic', $origin = 'button' ),
                    'icon' => ( ! empty( $button_icon ) ) ? $button_icon : '',
                    'text' => $button_text,
                    'title' => $button_text,
                    'target' => '_blank',
                    'rel' => 'nofollow noopener sponsored',
                    'attributes' => ''
                );
            }

            $button_args = apply_filters( 'aawp_func_button', $button_args, $type, $this->atts );

            $button = aawp_get_button_html( $button_args );

            if ( $button ) {

                if ( $echo ) {
                    echo $button;
                } else {
                    return $button;
                }

            } else {
                return null;
            }

            /*
            // TODO: Deprecated; old template loading
            if ( $button ) {
                $template_file = $this->get_template_part( 'parts/button' );

                if ( ! empty( $template_file ) ) {

                    if ( $echo ) {
                        include $template_file;
                    } else {

                        ob_start();
                        include $template_file;
                        return ob_get_clean();
                    }

                    //include $template_file;
                }

            } else {
                return null;
            }
            */
        }

        /**
         * Get product salesrank
         *
         * @param string $format
         *
         * @return null|string
         */
        public function get_product_salesrank( $format = 'formatted' ) {

            $salesrank = $this->item->get_salesrank();

            if ( ! empty( $salesrank ) && 'formatted' === $format ) {
                $salesrank = $this->format_number( $salesrank, $use_decimals = false );
            }

            return $salesrank;
        }

        /**
         * Get product date updated
         *
         * @return string
         */
        public function get_product_date_updated() {
            return $this->item->get_date_updated();
        }

        /**
         * Get product timestamp
         *
         * @return string
         */
        public function get_product_timestamp() {
            return $this->item->get_timestamp();
        }

        /**
         * Get product last update
         *
         * @return string
         */
        public function get_product_last_update() {
            return $this->item->get_last_update();
        }

        /**
         * Get product number
         */
        public function get_product_numbering() {
            return $this->item_index;
        }

        /**
         * Returning template variables
         *
         * @param array $default
         *
         * @return array
         */
        public function get_template_variables( $default = array() ) {
            return ( is_array( $this->template_variables ) ) ? $this->template_variables : $default;
        }

        /**
         * Returning single template variable
         *
         * @param $key
         * @param null $default
         *
         * @return null
         */
        public function get_template_variable( $key, $default = null ) {

            $variables = $this->template_variables;

            return ( isset( $variables[$key] ) ) ? $variables[$key] : $default;
        }

        /**************************************************
         * Deprecated functions
         **************************************************/
        public function get_classes( $classes, $widget = false ) {

            // Box
            if ($classes === 'box') {
                $classes = 'aawp-box';
            }

            // Type
            if ( !empty($this->atts['type']) ) {
                $classes .= $this->atts['type'];
            }

            // Settings Styles
            $style = $this->get_style();

            if ( $style && $style != 'standard' )
                $classes .= ' ' . $style;

            if ($this->func_id == 'bestseller' || $this->get_product_is_sale())
                $classes .= ' ribbon sale';

            if ( $this->get_inline_info() && !empty ( $this->options['general']['inline_info_text'] ) )
                $classes .= ' inline-info';

            // Custom Classes via Shortcode
            if ( ! empty( $this->atts['class'] ) ) {
                $custom_classes = sanitize_text_field( $this->atts['class'] );
                $custom_classes = str_replace( ",", " ", $custom_classes ); // Transform comma to space
                $classes .= ' ' . rtrim ( $custom_classes );
            }

            // Finish
            return $classes;
        }

        public function the_product_container( $echo = true ) {

            // TODO: Move this into a unique way to handle (table-builder.php > aawp_the_table_product_data())

            $output = '';

            $attributes = array();

            // Product ID
            $attributes['product-id'] = $this->get_product_id();
            $attributes['product-title'] = '%title%';

            $attributes = apply_filters( 'aawp_product_container_attributes', $attributes );

            if ( sizeof( $attributes ) != 0 ) {

                foreach ( $attributes as $key => $value ) {

                    // Handle placeholders
                    $value = $this->replace_item_placeholders( $value );

                    // Add attribute to output
                    if ( ! empty ( $value ) )
                        $output .= ' data-aawp-' . $key . '="' . str_replace('"', "'", $value) . '"';
                }
            }

            if ( ! $echo )
                return $output;

            if ( ! empty ( $output ) )
                echo $output;
        }

        public function replace_item_placeholders( $placeholder ) {

            if ( '%title%' === $placeholder )
                return $this->get_product_title( true );

            if ( '%price%' === $placeholder )
                return $this->get_product_price();

            return $placeholder;
        }

        /**
         * Since 3.4.3
         */
        public function get_product_is_sale() {
            return $this->product_is_on_sale();
        }

        /**
         * Since 3.4.3
         *
         * Expecting "standard" or "ribbon"
         */
        public function is_sale_discount_position ( $position ) {
            return ( 'standard' === $position && $this->sale_show_price_reduction() ) ? true : false; // Fallback
        }

        /**
         * Since 3.4.3
         */
        public function show_sale_discount() {
            return true; // Fallback
        }

        /**
         * Since 3.6.0
         *
         * Get product editorial review
         *
         * @return string/null
         */
        public function get_product_editorial_review() {
            return null; // No longer supported
        }
    }
}