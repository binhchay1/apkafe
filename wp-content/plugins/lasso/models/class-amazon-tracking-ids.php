<?php
/**
 * Models
 *
 * @package Models
 */

namespace Lasso\Models;

/**
 * Model
 */
class Amazon_Tracking_Ids extends Model {
	/**
	 * Table name
	 *
	 * @var string
	 */
	protected $table = 'lasso_amazon_tracking_ids';

	/**
	 * Columns of the table
	 *
	 * @var array
	 */
	protected $columns = array(
		'id',
		'country_code',
		'country_name',
		'amazon_domain',
		'pa_endpoint',

		'tracking_id',
	);

	/**
	 * Primary key of the table
	 *
	 * @var string
	 */
	protected $primary_key = 'id';

	/**
	 * Default data
	 *
	 * @var array
	 */
	protected $default_data = array(
		array( '1', 'United States', 'www.amazon.com', 'webservices.amazon.com', '' ),
		array( '1', 'Canada', 'www.amazon.ca', 'webservices.amazon.ca', '' ),
		array( '33', 'France', 'www.amazon.fr', 'webservices.amazon.fr', '' ),
		array( '34', 'Spain', 'www.amazon.es', 'webservices.amazon.es', '' ),
		array( '39', 'Italy', 'www.amazon.it', 'webservices.amazon.it', '' ),

		array( '44', 'United Kingdom', 'www.amazon.co.uk', 'webservices.amazon.co.uk', '' ),
		array( '49', 'Germany', 'www.amazon.de', 'webservices.amazon.de', '' ),
		array( '52', 'Mexico', 'www.amazon.com.mx', 'webservices.amazon.com.mx', '' ),
		array( '55', 'Brazil', 'www.amazon.com.br', 'webservices.amazon.com.br', '' ),
		array( '61', 'Australia', 'www.amazon.com.au', 'webservices.amazon.com.au', '' ),

		array( '86', 'China', 'www.amazon.cn', 'webservices.amazon.cn', '' ),
		array( '81', 'Japan', 'www.amazon.co.jp', 'webservices.amazon.co.jp', '' ),
		array( '91', 'India', 'www.amazon.in', 'webservices.amazon.in', '' ),
	);

	/**
	 * Create table
	 */
	public function create_table() {
		$columns_sql = '
			id INT NOT NULL AUTO_INCREMENT,
			country_code INT NOT NULL,
			country_name varchar(20) NOT NULL,
			amazon_domain varchar(20),
			pa_endpoint varchar(50) NOT NULL,
			tracking_id varchar(200) NULL,
			PRIMARY KEY  (id),
			UNIQUE KEY  ix_amazon_tracking_ids (country_code, country_name, amazon_domain, pa_endpoint)
		';
		$sql         = '
			CREATE TABLE ' . $this->get_table_name() . ' (
				' . $columns_sql . '
			) ' . $this->get_charset_collate();

		return $this->modify_table( $sql, $this->get_table_name() );
	}

	/**
	 * Add default data
	 */
	public function add_default_data() {
		$default_data = array_map(
			function( $v ) {
				return "('" . implode( "', '", $v ) . "')";
			},
			$this->get_default_data()
		);
		$default_data = implode( ', ', $default_data );

		$query = '
			INSERT IGNORE INTO  ' . $this->get_table_name() . ' 
				(`country_code`, `country_name`, `amazon_domain`, `pa_endpoint`, `tracking_id`)
			VALUES 
				' . $default_data . '
		';

		return self::query( $query );
	}
}
