<?php
/*
    Main Plugin Class 0.0.1
    
	Copyright 2016 zourbuth.com (email: zourbuth@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


if ( ! defined( 'ABSPATH' ) ) // exit if is accessed directly
	exit;


/**
 * Class constructor
 * 
 * @since 0.0.1
 */	
class Flickr_Badges_Widget_Main {
	
	 var $textdomain;
	 
	/**
	 * Class constructor
	 * 
	 * @since 0.0.1
	 */		
	function __construct() {	
		add_shortcode( 'flickr-badge-widget',  array( &$this, 'widget_shortcode' ) );		
	}

	
	/**
	 * Add widget shortcode based on widget id
	 * 
	 * @param $atts (array)
	 * @since 0.0.1
	 */	
	function widget_shortcode( $atts ) {
		$atts = shortcode_atts( array(
			'id' => false,
		), $atts, 'urlink' );
		
		$html = '';		

		$options = get_option( 'widget_zflickr' );
		$widget_id = $atts['id'];
		$instance = $options[$widget_id];		
		
		$args = wp_parse_args( (array) $instance, fbw_default_args() );
							
		return flickr_badges_widget( $args );
	}


} new Flickr_Badges_Widget_Main();


/**
 * Default arguments
 * 
 * @since 0.0.1
 */	
function fbw_default_args() {
	return array(
		'id'			=> '',
		'title'			=> esc_attr__( 'Flickr Widget', 'flickr-badges-widget' ),
		'type'			=> 'user',
		'flickr_id'		=> '', // 71865026@N00
		'count'			=> 9,
		'display'		=> 'display',
		'size'			=> 's',
		'target_blank'	=> true,
		'template'		=> array(
			'name' => 'default',
		),
		'cached'		=> 3600,
		'copyright'		=> true,
		'tab'			=> array( 0 => true, 1 => false, 2 => false, 3 => false , 4 => false, 5 => false ),
		'intro_text'	=> '',
		'outro_text'	=> '',
		'custom'		=> '',		
	);
}


/**
 * Default arguments
 * 
 * @since 1.2.9
 */	
function flickr_badges_widget_parse_args( $arr = array() ) {
	$args = wp_parse_args( $arr, fbw_default_args() );
	
	$template = flickr_badges_widget_template_args( $args ); // set default template argument if's not existed	

	foreach( $template as $k => $tpl )
		$args['template'][$k] = isset( $arr['template'][$k] ) ? $arr['template'][$k] : $tpl['default'];	

	return $args;
}


/**
 * Default template arguments
 * 
 * @since 1.2.9
 */	
function flickr_badges_widget_template_args( $args = array() ) {
	$template = isset( $args['template'] ) ? $args['template'] : array();
	return apply_filters( 'flickr_badges_widget_template_args', $template, $args );
}


/**
 * Output flickrs images
 * 
 * @params see fbw_default_args()
 * @since 0.0.1
 */	
function flickr_badges_widget( $args ) {

	$output = '';
	
	$dir = '';
	if ( function_exists( 'is_rtl' ) ) // get the user direction, rtl or ltr
		$dir = is_rtl() ? 'rtl' : 'ltr';
	
	if ( ! empty( $args['intro_text'] ) )
		$output .= '<p>' . do_shortcode( $args['intro_text'] ) . '</p>';

	$protocol = is_ssl() ? 'https' : 'http';

	if ( ! empty( $args['flickr_id'] ) ) { // if the widget have an ID, we can continue

		if ( false === ( $cached = get_transient( $args['id'] ) ) ) {

			$response = wp_remote_get( "$protocol://www.flickr.com/badge_code_v2.gne", array(
				'body' => array( 
					'count' => $args['count'], 
					'display' => $args['display'], 
					'size' => $args['size'], 
					'layout' => 'x', 
					'source' => $args['type'],
					$args['type'] => $args['flickr_id'],
				),
			) );

			if ( is_wp_error( $response ) ) {
				$output .= 'Error - '. $response->get_error_message();
			} else {
				$body = wp_remote_retrieve_body( $response );

				$dom = new DOMDocument( '1.0', 'utf-8' );
				@$dom->loadHTML( $body );

				$divs = $dom->getElementsByTagName( 'div' );
				$span = $dom->getElementsByTagName( 'span' );

				$fdom = new DOMDocument();
				
				$wrap = $fdom->createElement( 'div' );
				$wrap->setAttribute( 'id', "fbw-{$args['id']}" );
				$wrap->setAttribute( 'class', "flickr-badge-wrapper $dir zframe-flickr-wrap-$dir {$args['template']['name']}" );			
				
				$ul = $fdom->createElement( 'ul' );				
								
				foreach ( $divs as $div ) {
					
					if ( $args['target_blank'] ) // open image in new window or tab
						$div->firstChild->setAttribute( 'target', '_blank' );										
					
					$li = $fdom->createElement( 'li' );
					$li->setAttribute( 'id', $div->getAttribute( 'id' ) );
					$li->setAttribute( 'class', $div->getAttribute( 'class' ) );					
					$li->appendChild( $fdom->importNode( $div->firstChild, true ) );
					
					$ul->appendChild( $li );
				}
				
				$wrap->appendChild( $ul );
				$fdom->appendChild( $wrap );
				$fdom->appendChild( $fdom->importNode( $span->item(0), true ) )	; // this is a tracker from flickr.com								
				
				$fdom = apply_filters( 'flickr_badges_widget_dom', $fdom, $args );
				$save = $fdom->SaveHTML();
				set_transient( $args['id'], $save, $args['cached'] );
				$output .= $save;
			}

		} else {
			$output .= $cached;
		}

	} else {
		$output .= '<p>' . __( 'Please provide an Flickr ID', 'flickr-badges-widget' ) . '</p>';
	}

	if ( ! empty( $args['outro_text'] ) )
		$output .= '<p>' . do_shortcode( $args['outro_text'] ) . '</p>';

	if ( $args['copyright'] )
		$output .= '<a href="http://wordpress.org/extend/plugins/flickr-badges-widget/">
			<span style="font-size: 11px;"><span style="color: #0063DC; font-weight: bold;">Flick</span><span style="color: #FF0084; font-weight: bold;">r</span> Badges Widget</span>
			</a>';

	return $output;
}


/**
 * Debugging purpose
 * 
 * @params $arr array()
 * @since 1.3.0
 */	
function fbw_debugr( $arr ) {
	echo '<pre style="font-size:10px;line-height:10px;">'. print_r( $arr, true ) . '</pre>'; 
}