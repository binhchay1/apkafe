<?php
//Get custom options for widget
global $wl_options;
if((!$wl_options = get_option('leafcolor')) || !is_array($wl_options) ) $wl_options = array();

/**
 * Add custom properties to every widget
 *
 * Add: custom-variation textbox for adding CSS classes
 *
 **/
 
add_action( 'sidebar_admin_setup', 'leafcolor_expand_control');
// adds in the admin control per widget, but also processes import/export
function leafcolor_expand_control(){
	global $wp_registered_widgets, $wp_registered_widget_controls, $wl_options;
	
	// ADD EXTRA CUSTOM FIELDS TO EACH WIDGET CONTROL
	// pop the widget id on the params array (as it's not in the main params so not provided to the callback)
	foreach ( $wp_registered_widgets as $id => $widget )
	{	// controll-less widgets need an empty function so the callback function is called.
		if (!$wp_registered_widget_controls[$id])
			wp_register_widget_control($id,$widget['name'], 'leafcolor_empty_control');
		
		$wp_registered_widget_controls[$id]['callback_ct_redirect']=$wp_registered_widget_controls[$id]['callback'];
		$wp_registered_widget_controls[$id]['callback']='leafcolor_widget_add_custom_fields';
		array_push($wp_registered_widget_controls[$id]['params'],$id);	
	}
	
	// UPDATE CUSTOM FIELDS OPTIONS (via accessibility mode?)
	if ( 'post' == strtolower($_SERVER['REQUEST_METHOD']) )
	{	foreach ( (array) $_POST['widget-id'] as $widget_number => $widget_id )
			if (isset($_POST[$widget_id.'-leafcolor']))
				$wl_options[$widget_id]=trim($_POST[$widget_id.'-leafcolor']);
	}
	
	update_option('leafcolor', $wl_options);
}

/* Empty function for callback */
function leafcolor_empty_control() {}

function leafcolor_widget_add_custom_fields() {
	global $wp_registered_widget_controls, $wl_options;

	$params=func_get_args();
	
	$id=array_pop($params);
	// go to the original control function
	$callback=$wp_registered_widget_controls[$id]['callback_ct_redirect'];
	if (is_callable($callback))
		call_user_func_array($callback, $params);	
	$value = !empty( $wl_options[$id ] ) ? htmlspecialchars( stripslashes( $wl_options[$id ] ),ENT_QUOTES ) : '';
	//var_dump(get_option('leafcolor'));
	
	// dealing with multiple widgets - get the number. if -1 this is the 'template' for the admin interface
	$number=isset($params[0]['number'])?$params[0]['number']:'';
	if ($number==-1) {$number="__i__"; $value="";}
	$id_disp=$id;
	if ($number) $id_disp=$wp_registered_widget_controls[$id]['id_base'].'-'.$number;
	
	// output our extra widget logic field
	echo "<p><label for='".esc_attr($id_disp)."-leafcolor'>".__('Custom Variation', 'leafcolor').": <input class='widefat' type='text' name='".esc_attr($id_disp)."-leafcolor' id='".esc_attr($id_disp)."-leafcolor' value='".esc_attr($value)."' /></label></p>";
}

/*Add custom fields 3*/
// Get custom options for widget
global $wl_options_style;
if((!$wl_options_style = get_option('leafcolor2')) || !is_array($wl_options_style) ) $wl_options_style = array();

if ( is_admin() )
{
    add_action( 'sidebar_admin_setup', 'widget_style_expand_control' );
}

function widget_style_expand_control()
{   
    global $wp_registered_widgets, $wp_registered_widget_controls, $wl_options_style;

    // ADD EXTRA WIDGET LOGIC FIELD TO EACH WIDGET CONTROL
    // pop the widget id on the params array (as it's not in the main params so not provided to the callback)
    foreach ( $wp_registered_widgets as $id => $widget )
    {   // controll-less widgets need an empty function so the callback function is called.
        if (!$wp_registered_widget_controls[$id])
            wp_register_widget_control($id,$widget['name'], 'widget_style_empty_control');
        $wp_registered_widget_controls[$id]['callback_style_redirect'] = $wp_registered_widget_controls[$id]['callback'];
        $wp_registered_widget_controls[$id]['callback'] = 'widget_style_extra_control';
        array_push( $wp_registered_widget_controls[$id]['params'], $id );   
    }
	// UPDATE CUSTOM FIELDS OPTIONS (via accessibility mode?)
	if ( 'post' == strtolower($_SERVER['REQUEST_METHOD']) )
	{	foreach ( (array) $_POST['widget-id'] as $widget_number => $widget_id )
			if (isset($_POST[$widget_id.'-leafcolor2']))
				$wl_options_style[$widget_id]=trim($_POST[$widget_id.'-leafcolor2']);
	}
	
	update_option('leafcolor2', $wl_options_style);
}

// added to widget functionality in 'widget_style_expand_control' (above)
function widget_style_empty_control() {}

// added to widget functionality in 'widget_style_expand_control' (above)
function widget_style_extra_control()
{   
    global $wp_registered_widget_controls, $wl_options_style;

    $params = func_get_args();
    $id = array_pop($params);

    // go to the original control function
    $callback = $wp_registered_widget_controls[$id]['callback_style_redirect'];
    if ( is_callable($callback) )
        call_user_func_array($callback, $params);       

    $value = !empty( $wl_options_style[$id] ) ? htmlspecialchars( stripslashes( $wl_options_style[$id ] ),ENT_QUOTES ) : '';

    // dealing with multiple widgets - get the number. if -1 this is the 'template' for the admin interface
	if(isset($params[0]['number']))
		$number = $params[0]['number'];
    if ($number == -1) {
        $number = "%i%"; 
        $value = "";
    }
    $id_disp=$id;
    if ( isset($number) ) 
        $id_disp = $wp_registered_widget_controls[$id]['id_base'].'-'.$number;

    // output our extra widget logic field
    echo "
	<p id='ia-".$id_disp."'><label for='".$id_disp."-leafcolor2'>".__('Widget Style', 'leafcolor').": 
	<select name='".$id_disp."-leafcolor2' id='".$id_disp."-leafcolor2'>
	  <option value='' ".($value==''?'selected="selected"':'').">Default</option>
	  <option value='boxed' ".($value=='boxed'?'selected="selected"':'').">Boxed</option>
	</select>
	</label></p>";
}
/**
 *  End Add custom properties to every widget
 */
/**
 * Hook before widget 
 */
if(!is_admin()){
	add_filter('dynamic_sidebar_params', 'leafcolor_hook_before_style_widget'); 	
	function leafcolor_hook_before_style_widget($params){
		/* Add custom variation classs to widgets */
		global $wl_options_style;
		$id=$params[0]['widget_id'];
		$classe_to_add = !empty( $wl_options_style[$id ] ) ? htmlspecialchars( stripslashes( $wl_options_style[$id ] ),ENT_QUOTES ) : '';
		
		if(preg_match('/icon-\w+\s*/',$classe_to_add,$matches)){
			if(ot_get_option( 'righttoleft', 0)){
				$params[0]['after_title'] = '<i class="'.$matches[0].'"></i>' . $params[0]['after_title'];
			} else {
				$params[0]['before_title'] .= '<i class="'.$matches[0].'"></i>';
			}
			$classe_to_add = str_replace('icon-','wicon-',$classe_to_add); // replace "icon-xxx" class to not add Awesome Icon before div.widget
		};
		
		if ($params[0]['before_widget'] != ""){  
			$classe_to_add = 'class="'.$classe_to_add.' ';
			$params[0]['before_widget'] = implode($classe_to_add, explode('class="', $params[0]['before_widget'], 2)); //replace only 1st class="
		}else{
			$classe_to_add = $classe_to_add;
			$params[0]['before_widget'] = '<div class="'.$classe_to_add.'">';
			$params[0]['after_widget'] = '</div>';
		}
		
		return $params;
	}
}
/**
 * End Add custom properties to every widget
 */
 
/**
 * Hook before widget 
 */
if(!is_admin()){
	add_filter('dynamic_sidebar_params', 'leafcolor_hook_before_width_widget'); 	
	function leafcolor_hook_before_width_widget($params){
		/* Add custom variation classs to widgets */
		global $wl_options_width;
		$id=$params[0]['widget_id'];
		$classe_to_add = !empty( $wl_options_width[$id ] ) ? htmlspecialchars( stripslashes( $wl_options_width[$id ] ),ENT_QUOTES ) : '';
		
		if(preg_match('/icon-\w+\s*/',$classe_to_add,$matches)){
			if(ot_get_option( 'righttoleft', 0)){
				$params[0]['after_title'] = '<i class="'.$matches[0].'"></i>' . $params[0]['after_title'];
			} else {
				$params[0]['before_title'] .= '<i class="'.$matches[0].'"></i>';
			}
			$classe_to_add = str_replace('icon-','wicon-',$classe_to_add); // replace "icon-xxx" class to not add Awesome Icon before div.widget
		};
		
		if ($params[0]['before_widget'] != ""){  
			$classe_to_add = 'class="'.$classe_to_add.' ';
			//$params[0]['before_widget'] = str_replace('class="',$classe_to_add,$params[0]['before_widget']);
			$params[0]['before_widget'] = implode($classe_to_add, explode('class="', $params[0]['before_widget'], 2)); //replace only 1st class="
		}else{
			$classe_to_add = $classe_to_add;
			$params[0]['before_widget'] = '<div class="'.$classe_to_add.'">';
			$params[0]['after_widget'] = '</div>';
		}
		
		return $params;
	}
}
/**
 * End Add custom properties to every widget
 */

/**
 * Hook before widget 
 */
if(!is_admin()){
	add_filter('dynamic_sidebar_params', 'leafcolor_hook_before_widget'); 	
	function leafcolor_hook_before_widget($params){
		/* Add custom variation classs to widgets */
		global $wl_options;
		$id=$params[0]['widget_id'];
		$classe_to_add = !empty( $wl_options[$id ] ) ? htmlspecialchars( stripslashes( $wl_options[$id ] ),ENT_QUOTES ) : '';
		
		if(preg_match('/icon-\w+\s*/',$classe_to_add,$matches)){
			if(ot_get_option( 'righttoleft', 0)){
				$params[0]['after_title'] = '<i class="'.$matches[0].'"></i>' . $params[0]['after_title'];
			} else {
				$params[0]['before_title'] .= '<i class="'.$matches[0].'"></i>';
			}
			$classe_to_add = str_replace('icon-','wicon-',$classe_to_add); // replace "icon-xxx" class to not add Awesome Icon before div.widget
		};
		
		if ($params[0]['before_widget'] != ""){  
			$classe_to_add = 'class="'.$classe_to_add.' ';
			$params[0]['before_widget'] = str_replace('class="',$classe_to_add,$params[0]['before_widget']);
		}else{
			$classe_to_add = $classe_to_add;
			$params[0]['before_widget'] = '<div class="'.$classe_to_add.'">';
			$params[0]['after_widget'] = '</div>';
		}
		
		return $params;
	}
}