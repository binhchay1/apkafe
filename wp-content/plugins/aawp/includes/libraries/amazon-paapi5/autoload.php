<?php
/**
 * Amazon Product Advertising API v5
 *
 * The use of this library is strictly prohibited without explicit permission.
 *
 * Copyright 2020 flowdee. All Rights Reserved.
 *
 * Twitter: https://twitter.com/flowdee
 * GitHub: https://github.com/flowdee
 *
 * @version: 1.1.2
 */
namespace Flowdee\AmazonPAAPI5WP;

// Load core files.
require_once( 'Core/AmazonAPI.php' );
require_once( 'Core/AWSSignatureV4.php' );
require_once( 'Core/Configuration.php' );
require_once( 'Core/Item.php' );

// Load request files.
require_once( 'Requests/BaseRequest.php' );
require_once( 'Requests/GetItemsRequest.php' );
require_once( 'Requests/GetVariationsRequest.php' );
require_once( 'Requests/SearchItemsRequest.php' );
require_once( 'Requests/TestRequest.php' );

// Load resource files.
require_once( 'Resources/GetItemsResource.php' );
require_once( 'Resources/GetVariationsResource.php' );
require_once( 'Resources/SearchItemsResource.php' );