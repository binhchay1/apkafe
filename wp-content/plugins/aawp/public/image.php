<?php
$url = ( isset( $_GET['url'] ) ) ? $_GET['url'] : '';

if ( empty( $url ) )
	die( 'URL is missing.' );

$url = base64_decode( $url );

// Validate URL.
if ( ! filter_var( $url, FILTER_VALIDATE_URL ) || (
    ! preg_match('/^https:\/\/images-(cn|eu|fe|na)\.ssl-images-amazon.com\/images\/I\/(?:[A-Za-z0-9\-\+\_\%]+)\.(?:[A-Za-z0-9\_]+)\.(jpg|jpeg|png)/', $url ) &&
    ! preg_match('/^https:\/\/m\.media-amazon.com\/images\/I\/(?:[A-Za-z0-9\+\-\_\.\%]+)\.(jpg|jpeg|png)/', $url ) ) ) {
    die( 'Invalid image.' );
}

// Validate file.
if ( substr_compare( $url, '.jpg', -strlen( '.jpg' ) ) === 0 || substr_compare( $url, '.jepg', -strlen( '.jepg' ) ) === 0 ) {
    header( "Content-Type: image/jpeg" );
} elseif ( substr_compare( $url, '.png', -strlen( '.png' ) ) === 0 ) {
    header( "Content-Type: image/png" );
} else {
    die( 'Invalid image.' );
}

readfile( $url );