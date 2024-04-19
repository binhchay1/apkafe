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
class Url_Issue_Definitions extends Model {
	/**
	 * Table name
	 *
	 * @var string
	 */
	protected $table = 'lasso_url_issue_definitions';

	/**
	 * Columns of the table
	 *
	 * @var array
	 */
	protected $columns = array(
		'id',
		'issue_type',
		'issue_title',
		'issue_description',
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
	 * HERE ERROR DEFINITIONS QUERIES NEEDS TO BE WRITTEN BASED ON MOZILLA DOCUMENTATION
	 * write the ones from sections that Andrew mentioned in ticket
	 *
	 * @var array
	 */
	protected $default_data = array(
		// ? CLIENT ERROR RESPONSES
		array( '000', 'Amazon Out of Stock', 'Lasso-specific code for out of stock products on Amazon.' ),
		array( '400', 'Bad Request', 'The server could not understand the request due to invalid syntax.' ),
		array( '401', 'Unauthorized', 'Although the HTTP standard specifies "unauthorized", semantically this response means "unauthenticated". That is, the client must authenticate itself to get the requested response.' ),
		array( '402', 'Payment Required', 'This response code is reserved for future use. The initial aim for creating this code was using it for digital payment systems, however this status code is used very rarely and no standard convention exists.' ),
		array( '403', 'Forbidden', 'The client does not have access rights to the content; that is, it is unauthorized, so the server is refusing to give the requested resource. Unlike 401, the client\\\'s identity is known to the server.' ),

		array( '404', 'Not Found', 'The server can not find requested resource. In the browser, this means the URL is not recognized. In an API, this can also mean that the endpoint is valid but the resource itself does not exist. Servers may also send this response instead of 403 to hide the existence of a resource from an unauthorized client. This response code is probably the most famous one due to its frequent occurrence on the web.' ),
		array( '405', 'Method Not Allowed', 'The request method is known by the server but has been disabled and cannot be used. For example, an API may forbid DELETE-ing a resource. The two mandatory methods, GET and HEAD, must never be disabled and should not return this error code.' ),
		array( '406', 'Not Acceptable', 'This response is sent when the web server, after performing server-driven content negotiation, doesn\\\'t find any content that conforms to the criteria given by the user agent.' ),
		array( '407', 'Proxy Authentication Required', 'This is similar to 401 but authentication is needed to be done by a proxy.' ),
		array( '408', 'Request Timeout', 'This response is sent on an idle connection by some servers, even without any previous request by the client. It means that the server would like to shut down this unused connection. This response is used much more since some browsers, like Chrome, Firefox 27+, or IE9, use HTTP pre-connection mechanisms to speed up surfing. Also note that some servers merely shut down the connection without sending this message.' ),

		array( '409', 'Conflict', 'This response is sent when a request conflicts with the current state of the server.' ),
		array( '410', 'Gone', 'This response is sent when the requested content has been permanently deleted from server, with no forwarding address. Clients are expected to remove their caches and links to the resource. The HTTP specification intends this status code to be used for "limited-time, promotional services". APIs should not feel compelled to indicate resources that have been deleted with this status code.' ),
		array( '411', 'Length Required', 'Server rejected the request because the Content-Length header field is not defined and the server requires it.' ),
		array( '412', 'Precondition Failed', 'The client has indicated preconditions in its headers which the server does not meet.' ),
		array( '413', 'Payload Too Large', 'Request entity is larger than limits defined by server; the server might close the connection or return an Retry-After header field.' ),

		array( '414', 'URI Too Long', 'The URI requested by the client is longer than the server is willing to interpret.' ),
		array( '415', 'Unsupported Media Type', 'The media format of the requested data is not supported by the server, so the server is rejecting the request.' ),
		array( '416', 'Range Not Satisfiable', 'The range specified by the Range header field in the request can\\\'t be fulfilled; it\\\'s possible that the range is outside the size of the target URI\\\'s data.' ),
		array( '417', 'Expectation Failed', 'This response code means the expectation indicated by the Expect request header field can\\\'t be met by the server.' ),
		array( '418', 'I\\\'m a teapot', 'The server refuses the attempt to brew coffee with a teapot.' ),

		array( '421', 'Misdirected Request', 'The request was directed at a server that is not able to produce a response. This can be sent by a server that is not configured to produce responses for the combination of scheme and authority that are included in the request URI.' ),
		array( '422', 'Unprocessable Entity (WebDAV)', 'The request was well-formed but was unable to be followed due to semantic errors.' ),
		array( '423', 'Locked (WebDAV)', 'The resource that is being accessed is locked.' ),
		array( '424', 'Failed Dependency (WebDAV)', 'The request failed due to failure of a previous request.' ),
		array( '425', 'Too Early', 'Indicates that the server is unwilling to risk processing a request that might be replayed.' ),

		array( '426', 'Upgrade Required', 'The server refuses to perform the request using the current protocol but might be willing to do so after the client upgrades to a different protocol. The server sends an Upgrade header in a 426 response to indicate the required protocol(s).' ),
		array( '428', 'Precondition Required', 'The origin server requires the request to be conditional. This response is intended to prevent the \\\'lost update\\\' problem, where a client GETs a resource\\\'s state, modifies it, and PUTs it back to the server, when meanwhile a third party has modified the state on the server, leading to a conflict.' ),
		array( '429', 'Too Many Requests', 'The user has sent too many requests in a given amount of time ("rate limiting").' ),
		array( '431', 'Request Header Fields Too Large', 'The server is unwilling to process the request because its header fields are too large. The request may be resubmitted after reducing the size of the request header fields.' ),
		array( '451', 'Unavailable For Legal Reasons', 'The user-agent requested a resource that cannot legally be provided, such as a web page censored by a government.' ),

		// ? SERVER ERROR RESPONSES
		array( '500', 'Internal Server Error', 'The server has encountered a situation it doesn\\\'t know how to handle.' ),
		array( '501', 'Not Implemented', 'The request method is not supported by the server and cannot be handled. The only methods that servers are required to support (and therefore that must not return this code) are GET and HEAD.' ),
		array( '502', 'Bad Gateway', 'This error response means that the server, while working as a gateway to get a response needed to handle the request, got an invalid response.' ),
		array( '503', 'Service Unavailable', 'The server is not ready to handle the request. Common causes are a server that is down for maintenance or that is overloaded. Note that together with this response, a user-friendly page explaining the problem should be sent. This responses should be used for temporary conditions and the Retry-After: HTTP header should, if possible, contain the estimated time before the recovery of the service. The webmaster must also take care about the caching-related headers that are sent along with this response, as these temporary condition responses should usually not be cached.' ),
		array( '504', 'Gateway Timeout', 'This error response is given when the server is acting as a gateway and cannot get a response in time.' ),

		array( '505', 'HTTP Version Not Supported', 'The HTTP version used in the request is not supported by the server.' ),
		array( '506', 'Variant Also Negotiates', 'The server has an internal configuration error: the chosen variant resource is configured to engage in transparent content negotiation itself, and is therefore not a proper end point in the negotiation process.' ),
		array( '507', 'Insufficient Storage (WebDAV)', 'The method could not be performed on the resource because the server is unable to store the representation needed to successfully complete the request.' ),
		array( '508', 'Loop Detected (WebDAV)', 'The server detected an infinite loop while processing the request.' ),
		array( '510', 'Not Extended', 'Further extensions to the request are required for the server to fulfil it.' ),

		array( '511', 'Network Authentication Required', 'The 511 status code indicates that the client needs to authenticate to gain network access.' ),
	);

	/**
	 * Create table
	 */
	public function create_table() {
		$columns_sql = '
			id INT NOT NULL AUTO_INCREMENT,
			issue_type varchar(3) NOT NULL,
			issue_title varchar(100) NOT NULL,
			issue_description varchar(2500) NOT NULL,
			PRIMARY KEY  (id),
			UNIQUE KEY  issue_type (issue_type)
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
		// ? HERE ERROR DEFINITIONS QUERIES NEEDS TO BE WRITTEN BASED ON MOZILLA DOCUMENTATION
		// ? write the ones from sections that Andrew mentioned in ticket

		$default_data = array_map(
			function( $v ) {
				return "('" . implode( "', '", $v ) . "')";
			},
			$this->get_default_data()
		);
		$default_data = implode( ', ', $default_data );

		$query = '
			INSERT IGNORE INTO  ' . $this->get_table_name() . ' 
				(`issue_type`, `issue_title`, `issue_description`)
			VALUES 
				' . $default_data . '
		';

		return self::query( $query );
	}
}
