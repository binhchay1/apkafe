<?php
/**
 * Products Database Class
 *
 * This class is for interacting with the products' database table
 *
 * @package     AAWP
 * @since       3.6
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * AAWP_DB_Products Class
 *
 * @since 2.1
 */
class AAWP_DB_Products extends AAWP_DB {

	/**
	 * The metadata type.
	 *
	 * @access public
	 * @since  3.6
	 * @var string
	 */
	public $meta_type = 'product';

	/**
	 * The name of the date column.
	 *
	 * @access public
	 * @since  3.6
	 * @var string
	 */
	public $date_key = 'date_created';

	/**
	 * The name of the cache group.
	 *
	 * @access public
	 * @since  3.6
	 * @var string
	 */
	public $cache_group = 'products';

	/**
	 * Get things started
	 *
	 * @access  public
	 * @since   3.6
	*/
	public function __construct() {

		global $wpdb;

		$this->table_name  = $wpdb->prefix . 'aawp_products';
		$this->primary_key = 'id';
		$this->version     = '3.11';

		/*
        if ( ! $this->table_exists( $this->table_name ) ) {
            $this->create_table();
        }
		*/
	}

	/**
	 * Get columns and formats
	 *
	 * @access  public
	 * @since   2.1
	*/
	public function get_columns() {
        return array(
            'id' => '%d',
            'status' => '%s',
            'asin' => '%s',
            'ean' => '%s',
            'isbn' => '%s',
            'binding' => '%s',
            'product_group' => '%s',
            'title' => '%s',
            'url' => '%s',
            'image_ids' => '%s',
            'features' => '%s',
            'attributes' => '%s',
            'availability' => '%s',
            'currency' => '%s',
            'price' => '%s',
            'savings' => '%s',
            'savings_percentage' => '%f',
            'savings_basis' => '%s',
            'salesrank' => '%f',
            'is_prime' => '%f',
            'is_amazon_fulfilled' => '%f',
            'shipping_charges' => '%s',
            'rating' => '%s',
            'reviews' => '%f',
            'reviews_updated' => '%s',
            'date_created' => '%s',
            'date_updated' => '%s'
        );
    }

	/**
	 * Get default column values
	 *
	 * @access  public
	 * @since   2.1
	*/
	public function get_column_defaults() {
        return array(
            'status' => 'active',
            'asin' => '',
            'ean' => '',
            'isbn' => '',
            'binding' => '',
            'product_group' => '',
            'title' => '',
            'url' => '',
            'image_ids' => '',
            'features' => '',
            'attributes' => '',
            'availability' => '',
            'currency' => '',
            'price' => '',
            'savings' => '',
            'savings_percentage' => 0,
            'savings_basis' => '',
            'salesrank' => '',
            'is_prime' => 0,
            'is_amazon_fulfilled' => 0,
            'shipping_charges' => '',
            'rating' => 0,
            'reviews' => 0,
            'reviews_updated' => date('Y-m-d H:i:s'),
            'date_created' => date('Y-m-d H:i:s'),
            'date_updated' => date('Y-m-d H:i:s')
        );
	}

    /**
     * Add a new product
     *
     * @param mixed $data
     * @return int
     */
	public function add( $data = array() ) {

        $args = aawp_setup_product_data_for_database( $data );

        if ( ! $args )
            return false;

		$product = $this->get_product_by( 'asin', $args['asin'], false );

		if ( $product && isset( $product->id ) ) {
			// update an existing product
			$this->update( $product->id, $data );

			return $product->id;
		} else {
			return $this->insert( $args, 'product' );
		}
	}

    /**
     * Insert a new product
     *
     * @param Flowdee\AmazonPAAPI5WP\Item $data
     * @param string $type
     * @return int
     */
	public function insert( $data, $type = '' ) {

	    // Insert into database
		$result = parent::insert( $data, $type );

		if ( $result ) {
			$this->set_last_changed();
		}

		return $result;
	}

    /**
     * Update an existing product
     *
     * @param $row_id
     * @param mixed $data
     * @param string $where
     *
     * @return bool
     */
	public function update( $row_id, $data = array(), $where = '' ) {

        $data = aawp_setup_product_data_for_database( $data, true );

        if ( ! $data )
            return false;

        // Overwrite date_updated
        $data['date_updated'] = date( 'Y-m-d H:i:s' );

        // Insert into database
		$result = parent::update( $row_id, $data, $where );

		if ( $result ) {
			$this->set_last_changed();
		}

		return $result;
	}

    /**
     * Update product reviews
     *
     * @param $row_id
     * @param array $data
     * @return bool
     */
    public function update_reviews( $row_id, $data = array() ) {

        //aawp_debug( $data, 'update_reviews() >> $row_id: ' . $row_id );

        if ( ! $data || empty ( $data['rating'] ) || empty ( $data['reviews'] ) )
            return false;

        // Overwrite date_updated
        $data['reviews_updated'] = date( 'Y-m-d H:i:s' );

        // Insert into database
        $result = parent::update( $row_id, $data );

        if ( $result ) {
            $this->set_last_changed();
        }

        return $result;
    }

    /**
     * Delete a product
     *
     * @param bool $id
     *
     * @return bool|false|int
     */
	public function delete( $id = false ) {

		if ( empty( $id ) ) {
			return false;
		}

		$product = $this->get_product_by( 'id', $id, false );

		if ( $product->id > 0 ) {

			global $wpdb;

			$result = $wpdb->delete( $this->table_name, array( 'id' => $product->id ), array( '%d' ) );

			if ( $result ) {
				$this->set_last_changed();
			}

			return $result;

		} else {
			return false;
		}

	}

    /**
     * Checks if a product exists
     *
     * @param string $value
     * @param string $field
     *
     * @return bool
     */
	public function exists( $value = '', $field = 'asin' ) {

		$columns = $this->get_columns();
		if ( ! array_key_exists( $field, $columns ) ) {
			return false;
		}

		return (bool) $this->get_column_by( 'id', $field, $value );

	}

    /**
     * Retrieves a single product from the database
     *
     * @param string $field
     * @param int $value
     * @param bool $format_result
     *
     * @return array|bool|null|object
     */
	public function get_product_by( $field = 'id', $value = 0, $format_result = true ) {

		if ( empty( $field ) || empty( $value ) ) {
			return NULL;
		}

		if ( 'id' == $field ) {
			// Make sure the value is numeric to avoid casting objects, for example,
			// to int 1.
			if ( ! is_numeric( $value ) ) {
				return false;
			}

			$value = intval( $value );

			if ( $value < 1 ) {
				return false;
			}

		} elseif ( 'asin' === $field ) {

			$value = trim( $value );
		}

		if ( ! $value ) {
			return false;
		}

		/*
		echo 'get_product_by >>> ';
		echo 'field: ' . $field;
        echo ' - value: ';
		var_dump( $value );
		echo '<br>';
        */

        global $wpdb;

        $product = $wpdb->get_row( "SELECT * FROM {$this->table_name} WHERE {$field} = '{$value}' LIMIT 1;" );

		return ( $format_result ) ? aawp_setup_product_data_from_database( $product ) : $product;
	}

    /**
     * Retrieve products from the database
     *
     * @param array $args
     * @param bool $format_results
     *
     * @return array|null|object
     */
    public function get_products( $args = array(), $format_results = true ) {

        global $wpdb;

        $defaults = array(
            'number'         => 10,
            'offset'         => 0,
            'id'             => 0,
            'status'         => 'active',
            'asin'           => '',
            'images_missing' => '',
            'reviews_updated' => '',
            'date_created'   => '',
            'date_updated'   => '',
            //'date'    => array(),
            'fields'         => false,
            's'              => '',
            'order'          => 'DESC',
            'orderby'        => 'id'
        );

        $args = wp_parse_args( $args, $defaults );

        //aawp_debug( $args, 'get_products $args' );

        $where = '';

        // ID
        if ( ! empty( $args['id'] ) ) {

            if ( is_array( $args['id'] ) ) {
                $ids = implode( ',', $args['id'] );
            } else {
                $ids = intval( $args['id'] );
            }

            if ( ! empty( $where ) ) {
                $where .= "AND `id` IN( {$ids} ) ";
            } else {
                $where .= "WHERE `id` IN( {$ids} ) ";
            }

        }

        // Status
        if( ! empty( $args['status'] ) ) {

            if( is_array( $args['status'] ) )
                $statuss = implode( ',', $args['status'] );
            else
                $statuss = $args['status'];

            if( ! empty( $where ) ) {
                $where .= "AND `status` IN( '{$statuss}' ) ";
            } else {
                $where .= "WHERE `status` IN( '{$statuss}' ) ";
            }

        }

        // ASIN
        if ( ! empty( $args['asin'] ) ) {

            if ( is_array( $args['asin'] ) ) {
                $asins = implode( "','", $args['asin'] );
            } elseif ( strpos( $args['asin'], ',' ) !== false ) {
                $asins = str_replace( ",", "','", $args['asin'] );
            } else {
                $asins = $args['asin'];
            }

            if( ! empty( $where ) ) {
                $where .= "AND `asin` IN( '{$asins}' ) ";
            } else {
                $where .= "WHERE `asin` IN( '{$asins}' ) ";
            }

        }

        // Images missing
        if( ! empty( $args['images_missing'] ) && true === $args['images_missing'] ) {

            if( ! empty( $where ) ) {
                $where .= "AND `image_ids` = '' ";
            } else {
                $where .= "WHERE `image_ids` = '' ";
            }

        }

        // Outdated only
        if ( isset( $args['outdated'] ) && true === $args['outdated'] ) {

            $general_options = aawp_get_options( 'general' );

            if ( ! empty( $general_options['cache_duration'] ) && is_numeric( $general_options['cache_duration'] ) ) {

                $cache_duration = intval( $general_options['cache_duration'] );

                if ( ! empty( $where ) ) {
                    $where .= "AND `date_updated` < DATE_SUB(NOW(), INTERVAL $cache_duration MINUTE) ";
                } else {
                    $where .= "WHERE `date_updated` < DATE_SUB(NOW(), INTERVAL $cache_duration MINUTE) ";
                }
            }
        }

        // Outdated only
        if ( isset( $args['reviews_outdated'] ) && true === $args['reviews_outdated'] ) {

            $rating_cache_duration = 4320; // 3 Days

            if ( ! empty( $where ) ) {
                $where .= "AND `reviews_updated` < DATE_SUB(NOW(), INTERVAL $rating_cache_duration MINUTE) ";
            } else {
                $where .= "WHERE `reviews_updated` < DATE_SUB(NOW(), INTERVAL $rating_cache_duration MINUTE) ";
            }
        }

        // With or without reviews
        if ( isset( $args['has_reviews'] ) ) {

            if ( true === $args['has_reviews'] ) {

                if ( ! empty( $where ) ) {
                    $where .= "AND `reviews` > 0 ";
                } else {
                    $where .= "WHERE `reviews` > 0 ";
                }

            } else {

                if ( ! empty( $where ) ) {
                    $where .= "AND `reviews` = 0 ";
                } else {
                    $where .= "WHERE `reviews` = 0 ";
                }
            }

        }

        // Fields to return
        if( $args['fields'] ) {
            $fields = $args['fields'];
        } else {
            $fields = '*';
        }

        if ( 'DESC' === strtoupper( $args['order'] ) ) {
            $order = 'DESC';
        } else {
            $order = 'ASC';
        }

        $columns = array(
            'id',
            'status',
            'asin',
            'reviews_updated',
            'date_created',
            'date_updated'
        );

        //$orderby = array_key_exists( $args['orderby'], $columns ) ? $args['orderby'] : 'id';
        $orderby = ( in_array( $args['orderby'], $columns ) ) ? $args['orderby'] : 'id';

        /*
        echo '<strong>SQL query:</strong><br>';
        echo '$fields: ' . $fields . '<br>';
        echo '$where: ' . $where . '<br>';
        echo '$orderby: ' . $orderby . '<br>';
        echo '$order: ' . $order . '<br>';
        echo '$args[number]: ' . $args['number'] . '<br>';
        */

        $products = $wpdb->get_results( $wpdb->prepare( "SELECT {$fields} FROM " . $this->table_name . " {$where}ORDER BY {$orderby} {$order} LIMIT %d,%d;", absint( $args['offset'] ), absint( $args['number'] ) ) );

        return ( $format_results ) ? $this->format_product_results( $products ) : $products;

    }

    /**
     * Count the total number of products in the database
     *
     * @param array $args
     *
     * @return mixed|null|string
     */
    public function count( $args = array() ) {

        global $wpdb;

        $defaults = array(
            'status'  => ''
        );

        $args  = wp_parse_args( $args, $defaults );

        $where = '';

        if( ! empty( $args['status'] ) ) {

            if( is_array( $args['status'] ) ) {
                $statuss = implode( ',', $args['status'] );
            } else {
                $statuss = intval( $args['status'] );
            }

            if( ! empty( $where ) ) {
                $where .= " AND `status` IN( '{$statuss}' ) ";
            } else {
                $where .= " WHERE `status` IN( '{$statuss}' ) ";
            }

        }

        $key   = 'aawp_db' . md5( '_products_' . serialize( $args ) );
        $count = get_transient( $key );

        if ( $count === false ) {
            $count = $wpdb->get_var( "SELECT COUNT(ID) FROM " . $this->table_name . "{$where};" );
            set_transient( $key, $count, 10800 );
        }

        return $count;

    }

	/**
	 * Sets the last_changed cache key for products.
	 *
	 * @access public
	 * @since  3.6
	 */
	public function set_last_changed() {
		wp_cache_set( 'last_changed', microtime(), $this->cache_group );
	}

	/**
	 * Retrieves the value of the last_changed cache key for products.
	 *
	 * @access public
	 * @since  2.8
	 */
	public function get_last_changed() {
		if ( function_exists( 'wp_cache_get_last_changed' ) ) {
			return wp_cache_get_last_changed( $this->cache_group );
		}

		$last_changed = wp_cache_get( 'last_changed', $this->cache_group );
		if ( ! $last_changed ) {
			$last_changed = microtime();
			wp_cache_set( 'last_changed', $last_changed, $this->cache_group );
		}

		return $last_changed;
	}

    /**
     * Prepare product data for database
     *
     * @param Flowdee\AmazonPAAPI5WP\Item $data
     * @param bool $is_update
     * @return array|null
     */
    public function prepare_product_data( $data, $is_update = false ) {


    }

    /**
     * Format product results before returning
     *
     * @param $products
     *
     * @return array
     */
    function format_product_results( $products ) {

        if ( ! $products )
            return $products;

        if ( is_array( $products ) ) {

            foreach ( $products as $product_key => $product ) {

                $product = aawp_setup_product_data_from_database( $product );

                // Replace
                if ( is_array( $product ) )
                    $products[$product_key] = $product;
            }
        }

        return $products;
    }

	/**
	 * Create the table
     *
     * http://webcheatsheet.com/sql/interactive_sql_tutorial/sql_datatypes.php
	 *
	 * @access  public
	 * @since   2.1
	*/
	public function create_table() {

		global $wpdb;

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

        $charset_collate = '';

        if ( ! empty( $wpdb->charset ) ) {
            $charset_collate .= "DEFAULT CHARACTER SET {$wpdb->charset}";
        }

        if ( ! empty( $wpdb->collate ) ) {
            $charset_collate .= " COLLATE {$wpdb->collate}";
        }

        $sql = "CREATE TABLE " . $this->table_name . " (
		id bigint(20) NOT NULL AUTO_INCREMENT,
		status varchar(20),
		asin varchar(20) NOT NULL,
		ean varchar(20),
		isbn varchar(20),
		binding varchar(100),
		product_group varchar(100),
		title text,
		url longtext,
		image_ids longtext,
		features longtext,
		attributes longtext,
		availability varchar(20),
		currency varchar(10),
		price varchar(50),
		savings varchar(50),
		savings_percentage tinyint(3),
		savings_basis varchar(50),
		salesrank bigint(10),
		is_prime tinyint(1),
		is_amazon_fulfilled tinyint(1),
		shipping_charges varchar(50),
		rating varchar(10),
		reviews bigint(20),
		reviews_updated datetime,
		date_created datetime NOT NULL,
		date_updated datetime NOT NULL,
		PRIMARY KEY  (id),
		UNIQUE KEY asin (asin),
		KEY status (status),
		KEY reviews (reviews),
		KEY reviews_updated (reviews_updated),
		KEY date_updated (date_updated)
		) {$charset_collate};";

		dbDelta( $sql );

        if ( $this->table_exists($this->table_name ) ) {
            update_option($this->table_name . '_db_version', $this->version );
        }
	}

}
