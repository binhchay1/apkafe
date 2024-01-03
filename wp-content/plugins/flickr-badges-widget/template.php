<?php
/*
    Template Class 0.0.1
    
	Copyright 2017 zourbuth.com (email: zourbuth@gmail.com)

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
 * Flickr Badges Widget Template
 * 
 * @filter flickr_badges_widget_templates
 * @filter flickr_badges_widget_template_args
 * @filter flickr_badges_widget_head
 * @since 1.3.0
 */	
class Flickr_Badges_Widget_Template {
	
	 var $textdomain;
	 
	/**
	 * Class constructor
	 * 
	 * @since 0.0.1
	 */		
	function __construct() {	
		add_filter( 'flickr_badges_widget_templates',  array( &$this, 'register_templates' ), 1, 1 );
		add_filter( 'flickr_badges_widget_template_args',  array( &$this, 'template_args' ), 1, 2 );		
		add_action( 'flickr_badges_widget_head', array( &$this, 'template_styles' ), 1, 9 );
	}

	
	/**
	 * Register pre-defined templates
	 * 
	 * @param $templates (array)
	 * @since 0.0.1
	 */	
	function register_templates( $templates ) {
		return array(
			'default'  => __( 'Default', $this->textdomain ),
			'bordered'  => __( 'Bordered', $this->textdomain ),
			'monochrome'  => __( 'Monochrome', $this->textdomain ),
		);
		
		return $templates;
	}
	

	/**
	 * Add widget shortcode based on widget id
	 * 
	 * @param $atts (array)
	 * @since 0.0.1
	 */	
	function template_args( $template, $args ) {

		switch( $args['template']['name'] ) {
			case 'default':
				$template = array(
					'margin' => array(
						'default' => '0 5px 5px 0', 
						'type' => 'text', 
						'label' => __( 'Margin', 'flickr-badges-widget' ), 
						'description' => __( 'Margin top, right, bottom and left respectively in its unit for each image.', 'flickr-badges-widget' ),
					),					
				);
				break;
			
			case 'monochrome':
				$template = array(
					'margin' => array(
						'default' => '0 5px 5px 0', 
						'type' => 'text', 
						'label' => __( 'Margin', 'flickr-badges-widget' ), 
						'description' => __( 'Margin top, right, bottom and left respectively in its unit for each image.', 'flickr-badges-widget' ),
					),
					'fader' => array(
						'default' => true, 
						'type' => 'checkbox', 
						'label' => __( 'Fade color transition', 'flickr-badges-widget' ), 
					),
				);
				break;
			
			case 'bordered':
				$template = array(
					'border' => array(
						'name' => array( 'color', 'hovercolor' ),
						'default' => array( '#DDDDDD', '#AAAAAA' ),
						'type' => array( 'color', 'color' ),
						'label' => __( 'Border Color & Hover', 'flickr-badges-widget' ), 
						'description' => __( 'Border color for all image.', 'flickr-badges-widget' ),
					),
					'thickness' => array(
						'default' => 1, 
						'type' => 'number', 
						'label' => __( 'Thickness', 'flickr-badges-widget' ), 
						'description' => __( 'Border thickness in pixels for each image.', 'flickr-badges-widget' ),
					),
					'padding' => array(
						'default' => 5, 
						'type' => 'number',
						'label' => __( 'Padding', 'flickr-badges-widget' ), 
						'description' => __( 'The space in pixels between image and border.', 'flickr-badges-widget' ),
					),
					'margin' => array(
						'default' => '0 5px 5px 0', 
						'type' => 'text',
						'label' => __( 'Margin', 'flickr-badges-widget' ), 
						'description' => __( 'Margin top, right, bottom and left respectively in its unit for each image.', 'flickr-badges-widget' ),
					),						
				);
				break;
		}
		
		return $template;
	}
	
	
	/**
	 * Template custom styles
	 * 
	 * @param $atts (array)
	 * @since 0.0.1
	 */	
	function template_styles( $args ) {
		$styles = '';
		$template = $args['template'];
		
		$transition = "-webkit-transition: all .3s linear; -moz-transition: all .3s linear; -o-transition: all .3s linear; -ms-transition: all .3s linear; transition: all .3s linear;";		
		
		switch( $template['name'] ) {
			case 'default':
				$styles .= "#{$args['id']} ul li {margin:{$args['template']['margin']};}";
				break;

			case 'monochrome':			
				$transition = $template['fader'] ? $transition : "";
					
				$styles .= "#{$args['id']} ul li {
								margin:{$template['margin']};
							}					
							#{$args['id']} ul li img {
								filter: gray; /* IE6-9 */
								-webkit-filter: grayscale(1); /* Google Chrome, Safari 6+ & Opera 15+ */
								filter: grayscale(1); /* Microsoft Edge and Firefox 35+ */
								$transition
							}

							#{$args['id']} ul li img:hover {
								-webkit-filter: grayscale(0);
								filter: none;
							}";
				break;

			case 'bordered':
				$styles .= "
					#{$args['id']} ul li {
						margin: {$args['template']['margin']};
						border: {$args['template']['thickness']}px solid {$args['template']['color']};
						padding: {$args['template']['padding']}px;
						$transition
					}
					#{$args['id']} ul li:hover {
						border-color: {$args['template']['hovercolor']};
					}";				
				break;
		}
		
		echo "<style type='text/css'>$styles</style>";
	}


} new Flickr_Badges_Widget_Template();