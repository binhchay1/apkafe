<?php
/*
    Gumaraphous Dialog Class 0.0.1
    
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
 * Class constructor
 * 
 * @since 0.0.1
 */	
if ( ! class_exists( 'Gumaraphous_Dialog' )) { class Gumaraphous_Dialog {
	
	 var $textdomain;
	 
	/**
	 * Class constructor
	 * 
	 * @since 0.0.1
	 */		
	function __construct() {
		$this->textdomain = '';
		add_action( 'admin_enqueue_scripts', array( &$this,'admin_enqueue_scripts' ), 1 );
		add_action( 'admin_footer-widgets.php', array( &$this,'footer_scripts' ), 2 );
	}

	
	function admin_enqueue_scripts( $hook_suffix ) {
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'wp-color-picker' );
	}
	
	
	function footer_scripts( $hook_suffix ) { ?>
		<script type="text/javascript">
		jQuery(document).ready( function($){
			$('.gcolor-picker').wpColorPicker();
			$(document).ajaxComplete( function() {
				$('.gcolor-picker').wpColorPicker();  
			});				
		});	
		</script><?php
	}		

	
	/**
	 * Add widget shortcode based on widget id
	 * 
	 * @param $atts (array)
	 * @since 0.0.1
	 */	
	public static function create_dialog( $args ) {		
		$html = ''; $array = array();
		
		$args = wp_parse_args( (array) $args, array( // merge the user-selected arguments with the defaults.
			'id' => '',
			'name' => '',
			'label' => '',
			'type' => '',
			'description' => '',
			'rows' => '',
			'size' => 3,
			'options' => '',
			'class' => '',
			'value' => '',
		)); 

		extract( $args );
		
		$description = $description ? "<span class='controlDesc'>$description</span>" : '';
		$id = is_array( $id ) ? $id[0] : $id;
		
		// Put label and its description, not for checkboxes
		if( 'checkbox' != $type && $label )
			$html .= "<label for='$id'>$label</label>$description";	

		// Check if dialog contain more than one options
		if ( is_array( $name ) ) {
			$new = $args;
			foreach ( $name as $k => $n ) { 	
				foreach ( array( 'id', 'name', 'default', 'value', 'type' ) as $a )
					$new[$a] = $args[$a][$k];	
				
				$html .= self::create_element( $new );
			}
			
		} else {
			$html .= self::create_element( $args );
		}		
		
		echo "<li>$html</li>";
	}

	
	/**
	 * Add widget shortcode based on widget id
	 * 
	 * @param $atts (array)
	 * @since 0.0.1
	 */	
	static function create_element( $args ) {		
		$html = '';

		$args = wp_parse_args( (array) $args, array( // merge the user-selected arguments with the defaults.
			'id' => '',
			'name' => '',
			'label' => '',
			'type' => '',
			'description' => '',
			'rows' => '',
			'size' => 3,
			'options' => '',
			'class' => '',
			'value' => '',
		));		
		
		extract( $args );

		switch( $type ) {
			case 'text':
			case 'number':
				$class = $class ? $class : ( 'number' == $type ? 'column-last' : 'widefat' );
				$html .= "<input class='$class' id='$id' name='$name' type='$type' value='$value' placeholder='$default' size='$size' />";																	
			break;
			
			case 'checkbox':
				$checked = checked( $value, true, false );
				$html .= "<label for='$id'>
					<input $checked class='checkbox $class' id='$id' name='$name' type='checkbox' />$label</label>$description";
			break;
			
			case 'color':
				$html .= "<input class='gcolor-picker' type='text' id='$id' name='$name' value='$value' />";
			break;
			
			case 'select':
				$html .= "<select id='$id' name='$name'>";
					foreach ( $options as $k => $option ) {
						$selected = selected( $instance['type'], $k, false );
						$option = esc_html( $option );
						$html .= "<option value='$value' $selected>$option</option>";
					}
				$html .= "</select>";
				break;
			
			case 'textarea':
				$class = $class ? $class : 'widefat';
				$html = "<textarea class='$class' id='$id' rows='5' name='$name'>$value</textarea>";
			
			case 'description':
				$html = "";
			break;
		}
		
		return $html;
	}

} new Gumaraphous_Dialog(); };