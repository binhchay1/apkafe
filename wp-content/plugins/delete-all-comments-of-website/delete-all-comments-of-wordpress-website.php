<?php
    /*
    Plugin Name: Delete All Comments of wordpress
    Plugin URI: http://www.navneetsoni.com/plugins/delete-comments
    Description: Plugin to delete all comments of wordpress website (Approved, Pending, Spam)
    Author: Navneet Soni
    Version: 5.3
    Author URI: http://www.navneetsoni.com 
    */
	
if ( ! function_exists( 'nonu_fs' ) ) {
    // Create a helper function for easy SDK access.
    function nonu_fs() {
        global $nonu_fs;

        if ( ! isset( $nonu_fs ) ) {
            // Include Freemius SDK.
            require_once dirname(__FILE__) . '/freemius/start.php';

            $nonu_fs = fs_dynamic_init( array(
                'id'                  => '7346',
                'slug'                => 'delete-all-comments-of-website',
                'type'                => 'plugin',
                'public_key'          => 'pk_3b87748f4797c99614f13caffb811',
                'is_premium'          => false,
                'has_addons'          => false,
                'has_paid_plans'      => false,
                'menu'                => array(
                    'slug'           => 'delete_comment',
                    'account'        => false,
                    'support'        => false,
                    'parent'         => array(
                        'slug' => 'tools.php',
                    ),
                ),
            ) );
        }

        return $nonu_fs;
    }

    // Init Freemius.
    nonu_fs();
    // Signal that SDK was initiated.
    do_action( 'nonu_fs_loaded' );
}	
	

add_action( 'admin_enqueue_scripts', 'my_admin_scripts_nav' );




add_action( 'admin_menu', 'nav_delete_all_comment' );
define( 'NAV_COMENT_PLUGIN_URI', plugin_dir_url( __FILE__ ) );

// Create WordPress admin menu

function my_admin_scripts_nav() {
wp_enqueue_style( 'et1-bloodfm-menu-icon', NAV_COMENT_PLUGIN_URI . '/include/sweetalert.css', array(), 1.0 );
wp_enqueue_script( 'et1-bffloom-menu-icon', NAV_COMENT_PLUGIN_URI . '/include/sweetalert.min.js', array(), 1.0 );
}




if( !function_exists("nav_delete_all_comment") )
{
function nav_delete_all_comment(){

  $page_title = 'Delete All comment !!';
  $menu_title = 'Delete Comments';
   $capability = 'install_plugins';
  $menu_slug  = 'delete_comment';
  $function   = 'nav_delete_comment';

  add_management_page( $page_title,
                 $menu_title,
                 $capability,
                 $menu_slug,
                 $function );
}
}
  function nav_log_me($message){
        if (WP_DEBUG === true){
            if (is_array($message) || is_object($message)){
                error_log(print_r($message, true));
            }
            else{
                error_log($message);
            }
        }
    }
        
// Create WordPress plugin page
if( !function_exists("nav_delete_comment") )
{
function nav_delete_comment(){
?>


<?php  
 global $wpdb;
 if(isset($_POST['nav_delete_comment'])) {
     $favcolor_nav = $_POST['nav_delete_comment'];
 } else {
     $favcolor_nav = "";
 }
 
  // if($date_from_nav =  $_POST['nav_delete_commentfrom'] == "") { $date_from_nav = '2000-01-01';  } else { $date_from_nav =   $_POST['nav_delete_commentfrom']; }
  //if($date_to_nav = $_POST['nav_delete_commentto'] == "") { $date_to_nav = date('Y-m-d');  } else { $date_to_nav =  $_POST['nav_delete_commentto']; }
   
  
switch ($favcolor_nav)
{
case "nav_delete_all":
      
	if(wp_verify_nonce($_POST['nav@final_delete'], 'nav@final_delete')) {
                if(true){
                    if($wpdb->query("DELETE FROM $wpdb->comments WHERE comment_type = 'comment'") != FALSE){
                            $wpdb->query("Update $wpdb->posts set comment_count = 0 where post_author != 0");
                            $wpdb->query("OPTIMIZE TABLE $wpdb->commentmeta");
                            $wpdb->query("OPTIMIZE TABLE $wpdb->comments"); ?>
                            <script>
							
							swal("Whooo!", "All comments have been deleted.! if I save your lot of time so Buy a coffee for me.", "success" , {
                              buttons: {
                                //cancel: "Sorry i can't Buy! ",
                                catch: {
                                  text: "Buy me a Beer ( Of course, you saved my time)",
                                  value: "coffee",
                                },
                               
                              },
                            })
                            .then((value) => {
                              switch (value) {
                             
                                case "coffee":
                                     window.open("https://instavoty.com/RgaeZ", "_blank");
                                  break;
                                default:
                                 
                              }
                            });
                            	</script>
                            
                            
						
                  <?php  }
                    else{
                            nav_log_me('Error occured when deleting wpdb comments table');
                            ?>
							<script>
							swal("Sorry!", "No comment found between these dates !", "warning")
							</script>
                    <?Php }
                }
                else{
                    nav_log_me('Error occured when deleting wpdb commentmeta table'); ?>
					<script>
							swal("Sorry!", "No comment found between these dates !", "warning")
							</script>

             <?php   }
            } // End of verify_nonce
            else{
                nav_log_me('Security failure');
                die("Security Validation Failure");
            } // End of Security   

  break;
case "nav_delete_moderation":
    
	if(wp_verify_nonce($_POST['nav@final_delete'], 'nav@final_delete')) {
               if($wpdb->query("DELETE FROM $wpdb->comments WHERE comment_approved = 0") != FALSE){
                            $wpdb->query("OPTIMIZE TABLE $wpdb->comments"); 
							if ( get_option( '_transient_as_comment_count' ) !== false ) {
						      // The option already exists, so we just update it.
						      update_option( '_transient_as_comment_count', "" );
                             }
							
							?>
							<script>
							
							swal("Whooo!", "All moderation comments have been deleted! if I save your lot of time so Buy a Beer for me", "success" , {
                              buttons: {
                               // cancel: "Sorry i can't Buy!  ",
                                catch: {
                                  text: "Buy me a Beer ( Of course, you saved my time)",
                                  value: "coffee",
                                },
                                
                              },
                            })
                            .then((value) => {
                              switch (value) {
                             
                                case "coffee":
                                     window.open('https://instavoty.com/RgaeZ', '_blank');
                                  break;
                                default:
                                 
                              }
                            });
                            	</script>
							
							
	      <?php  }  // End of verify_nonce
		   else{
                            nav_log_me('Error occured when deleting wpdb comments table'); ?>
                          
							<script>
							swal("Sorry!", "No comment found between these dates !", "warning")
							</script>

		 <?php   }      }
            else {
                nav_log_me('Security failure');
                die("Security Validation Failure");
            } // End of Security 
	
  break;
case "nav_delete_approved":
   
    if(wp_verify_nonce($_POST['nav@final_delete'], 'nav@final_delete')) {
                
                    if($wpdb->query("DELETE FROM $wpdb->comments WHERE comment_approved = 1") != FALSE){
                            $wpdb->query("Update $wpdb->posts set comment_count = 0 where post_author != 0");
                            $wpdb->query("OPTIMIZE TABLE $wpdb->comments");
							
							if ( get_option( '_transient_as_comment_count' ) !== false ) {
						      // The option already exists, so we just update it.
						      update_option( '_transient_as_comment_count', "" );
                             }
							
							?>
							<script>
							
							swal("Whooo!", "All approved comments have been deleted ! if I save your lot of time so Buy a Beer for me.", "success" , {
                              buttons: {
                               // cancel: "Sorry i can't Buy! ",
                                catch: {
                                  text: "Buy me a Beer ( Of course, you saved my time)",
                                  value: "coffee",
                                },
                               
                              },
                            })
                            .then((value) => {
                              switch (value) {
                             
                                case "coffee":
                                     window.open('https://instavoty.com/RgaeZ', '_blank');
                                  break;
                                default:
                                 
                              }
                            });
                            	</script>
                    <?php }
                    else{
                            nav_log_me('Error occured when deleting wpdb comments table'); ?>
							<script>
							swal("Sorry!", "No comment found !", "warning")
							</script>
                 <?php   }
               
            } // End of verify_nonce
            else{
                nav_log_me('Security failure');
                die("Security Validation Failure");
            } // End of Security   

  break;
case "nav_delete_spam":

     if(wp_verify_nonce($_POST['nav@final_delete'], 'nav@final_delete')) {
                
                    if($wpdb->query("DELETE FROM $wpdb->comments WHERE comment_approved = 'spam'") != FALSE){
                            $wpdb->query("Update $wpdb->posts set comment_count = 0 where post_author != 0");
                            $wpdb->query("OPTIMIZE TABLE $wpdb->comments");
                           if ( get_option( '_transient_as_comment_count' ) !== false ) {
						      // The option already exists, so we just update it.
						      update_option( '_transient_as_comment_count', "" );
                             }

							?>
							<script>
							
							swal("Whooo!", "All spam comments have been deleted.! if I save your lot of time so Buy a Beer for me.", "success" , {
                              buttons: {
                               // cancel: "Sorry i can't Buy! ",
                                catch: {
                                  text: "Buy me a Beer ( Of course, you saved my time)",
                                  value: "coffee",
                                }
                               
                              },
                            })
                            .then((value) => {
                              switch (value) {
                             
                                case "coffee":
                                     window.open('https://instavoty.com/RgaeZ', '_blank');
                                  break;
                                default:
                                 
                              }
                            });
                            	</script>
							
                  <?Php  }
                    else{
                            nav_log_me('Error occured when deleting wpdb comments table'); ?>
							<script>
							swal("Sorry!", "No comment found between these dates !", "warning")
							</script>
                    <?php }
               
            } // End of verify_nonce
            else{
                nav_log_me('Security failure');
                die("Security Validation Failure");
            } // End of Security   

  break;
case "nav_delete_trash":
    
	if(wp_verify_nonce($_POST['nav@final_delete'], 'nav@final_delete')) {
                
                    if($wpdb->query("DELETE FROM $wpdb->comments WHERE comment_approved = 'trash'") != FALSE){
                            $wpdb->query("Update $wpdb->posts set comment_count = 0 where post_author != 0");
                            $wpdb->query("OPTIMIZE TABLE $wpdb->comments"); 
							if ( get_option( '_transient_as_comment_count' ) !== false ) {
						      // The option already exists, so we just update it.
						      update_option( '_transient_as_comment_count', "" );
                             }
							
							?>
							<script>
							
							swal("Whooo!", "All trash comments have been deleted! if I save your lot of time so Buy a Beer for me.", "success" , {
                              buttons: {
                               // cancel: "Sorry i can't Buy! ",
                                catch: {
                                  text: "Buy me a Beer",
                                  value: "coffee",
                                },
                               
                              },
                            })
                            .then((value) => {
                              switch (value) {
                             
                               
                                case "coffee":
                                     window.open('https://instavoty.com/RgaeZ', '_blank');
                                  break;
                                default:
                                 
                              }
                            });
                            	</script>
                   <?php } 
                    else{
                            nav_log_me('Error occured when deleting wpdb comments table'); ?>
							<script>
							swal("Sorry!", "No comment found between these dates!", "warning")
							</script>
                    <?php }
               
            } // End of verify_nonce
            else{
                nav_log_me('Security failure');
                die("Security Validation Failure");
            } // End of Security   

  break;
}
$comments_count = wp_count_comments();
?>
<div id="wpbody" role="main">
   <div id="wpbody-content" aria-label="Main content" tabindex="0" style="overflow: hidden;">
      <div class="wrap columns-2 seed_wnb">
         <!-- Screen icons are no longer used as of WordPress 3.8. -->            
         <h2><b>Delete all comments with filter !!! </b></h2>
         <div id="poststuff">
            <div id="post-body" class="metabox-holder columns-2">
               <div id="post-body-content">
                  <form action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>" method="post">
                      <input type="hidden" name="nav@final_delete" value="<?php echo wp_create_nonce('nav@final_delete'); ?>">
					  <div class="postbox seedprod-postbox">
                        <div class="handlediv" title="Click to toggle"><br></div>
                        <h3 style="color:red" >Delete comments !</h3>
                        <div class="inside">
                           <p style="color:red;">Note : Deleted Comment will not be recovered ! & Note: All comments count will be refresh once any new comments will be added.</p>
						   <table class="form-table">
                              <tbody>
                                 <tr>
                                    <th scope="row">All Comments:</th>
                                    <td><input type="radio" name="nav_delete_comment" required <?php if($comments_count->total_comments<=0) { echo 'disabled' ;  } ?>  value="nav_delete_all"> <br><small class="description">Note : Delete all comments  (A to z)</small></td>
                                 </tr>
								  <tr>
                                    <th scope="row"> Comments in moderation:</th>
                                     <td><input type="radio" name="nav_delete_comment" required <?php if($comments_count->moderated<=0) { echo 'disabled' ;  } ?> value="nav_delete_moderation"> <br><small class="description">Note : Delete all comments which mark as  moderation.</small></td>
                                 </tr>
								  <tr>
                                    <th scope="row">Comments approved:</th>
                                 <td><input type="radio" name="nav_delete_comment" required <?php if($comments_count->approved<=0) { echo 'disabled' ;  } ?>  value="nav_delete_approved"> <br><small class="description">Note : Delete all comments which mark as  approved .</small></td>
                                 </tr>
								  <tr>
                                    <th scope="row">Comments in Spam:</th>
                                   <td><input type="radio" name="nav_delete_comment" required <?php if($comments_count->spam<=0) { echo 'disabled' ;  } ?> value="nav_delete_spam"> <br><small class="description">Note : Delete all comments which mark as  Spam .</small></td>
                                 </tr>
								 <tr>
                                    <th scope="row">Comments in Trash:</th>
                                   <td><input type="radio" name="nav_delete_comment" required <?php if($comments_count->trash<=0) { echo 'disabled' ;  } ?> value="nav_delete_trash"> <br><small class="description">Note : Delete all comments which mark as  Trash .</small></td>
                                 </tr>
                              </tbody>
                           </table>
                          
                        </div>
                     </div>
					 
					 
					 
             <!--   <div class="postbox seedprod-postbox">
                        <div class="handlediv" title="Click to toggle"><br></div>
                        <h3 style="color:green;">Advance Filter :</h3>
                        <div class="inside">
                           <table class="form-table">
                              <tbody>
							    <tr>
                                    <th scope="row">Select date:</th>
                                   <td>From:<input type="date" name="nav_delete_commentfrom" value="nav_delete_spam">
								       To:<input type="date" name="nav_delete_commentto" value="nav_delete_spam"> <br><small class="description">Note : Delete all comments between these dates (not working on delete all comment and approved comment) .</small></td>
                                 </tr>
                                <tr>
                                    <th scope="row">Delete </th>
                                    <td><input type="checkbox" name="" value="1" checked="">Note : Delete only comments which have links or spamming url, For example see image below -<br></td>
                                 </tr> 
                                
                              </tbody>
                           </table>
                        </div>
                     </div>  -->
                     <p>
                        <?php submit_button('Delete Now'); ?> 
                        <!-- <input id="reset" name="reset" type="submit" value="Reset" class="button-secondary"/>   -->
                     </p>
                   
                  </form>
				   
				 
			
				   <div class="postbox seedprod-postbox">
                        <div class="handlediv" title="Click to toggle"><br></div>
                        <h2 style="color:green;text-align:center;font-size:30px">PEOPLE WHO SUPPORTS THIS PLUGIN ( Total supporter 119+)  :</h2>
					    <h2 style="color:red;text-align:center;font-size:20px">*** Buy 3 beer I will add your name here (OUR plugin active install is 50K+) So don't miss this  :)  ***</h2>
					    
						
					   
					     <style>.bmc-button img{height: 34px !important;width: 35px !important;margin-bottom: 1px !important;box-shadow: none !important;border: none !important;vertical-align: middle !important;}.bmc-button{padding: 7px 15px 7px 10px !important;line-height: 35px !important;height:51px !important;text-decoration: none !important;display:inline-flex !important;color:#ffffff !important;background-color:#FF813F !important;border-radius: 5px !important;border: 1px solid transparent !important;padding: 7px 15px 7px 10px !important;font-size: 28px !important;letter-spacing:0.6px !important;box-shadow: 0px 1px 2px rgba(190, 190, 190, 0.5) !important;-webkit-box-shadow: 0px 1px 2px 2px rgba(190, 190, 190, 0.5) !important;margin: 0 auto !important;font-family:'Cookie', cursive !important;-webkit-box-sizing: border-box !important;box-sizing: border-box !important;}.bmc-button:hover, .bmc-button:active, .bmc-button:focus {-webkit-box-shadow: 0px 1px 2px 2px rgba(190, 190, 190, 0.5) !important;text-decoration: none !important;box-shadow: 0px 1px 2px 2px rgba(190, 190, 190, 0.5) !important;opacity: 0.85 !important;color:#ffffff !important;}</style><link href="https://fonts.googleapis.com/css?family=Cookie" rel="stylesheet"><a class="bmc-button" target="_blank" href="https://instavoty.com/RgaeZ"><img src="https://cdn.buymeacoffee.com/buttons/bmc-new-btn-logo.svg" alt="Buy me a coffee"><span style="margin-left:5px;font-size:28px !important;">Buy me a Beers</span></a>
					   
                        <div class="inside">
                           <table class="form-table">
                              <tbody>
							    <tr>
                                   <td><img  src="<?php echo NAV_COMENT_PLUGIN_URI; ?>/support/1.png"></td>
								   <td><img src="<?php echo NAV_COMENT_PLUGIN_URI; ?>/support/2.png"></td>
                                 </tr>
                                <tr>
                                   <td><img src="<?php echo NAV_COMENT_PLUGIN_URI; ?>/support/3.png"></td>
								   <td><img src="<?php echo NAV_COMENT_PLUGIN_URI; ?>/support/4.png"></td>
                                 </tr> 
								 <tr>
                                   <td><img src="<?php echo NAV_COMENT_PLUGIN_URI; ?>/support/5.png"></td>
								   <td><img src="<?php echo NAV_COMENT_PLUGIN_URI; ?>/support/6.png"></td>
                                 </tr> 
								 <tr>
                                   <td><img src="<?php echo NAV_COMENT_PLUGIN_URI; ?>/support/7.png"></td>
								   <td><img src="<?php echo NAV_COMENT_PLUGIN_URI; ?>/support/8.png"></td>
                                 </tr> 
								 <tr>
                                   <td><img src="<?php echo NAV_COMENT_PLUGIN_URI; ?>/support/9.png"></td>
								   <td><img src="<?php echo NAV_COMENT_PLUGIN_URI; ?>/support/10.png"></td>
                                 </tr> 
                                
                              </tbody>
                           </table>
                        </div>
						<div class="inside">
						
						 
						
						</div>
                     </div>  
				   
				   
				   
               </div>
               <!-- #post-body-content -->
               <div id="postbox-container-1" class="postbox-container">
                  <div id="side-sortables" class="meta-box-sortables ui-sortable">
                     
                     <div class="postbox support-postbox" style="background-color: #fcf8e3">
                        <div class="handlediv" title="Click to toggle"><br></div>
                        <h3 class="hndle ui-sortable-handle"><span style="color:green">Comments of your Website</span></h3>
                        <div class="inside">
                           <div class="support-widget">
                                   <b>Total Comments:</b> 
                                   <?php echo $comments_count->total_comments; ?> </br>
                               
                                  <b> Comments in moderation: :</b> 
                                   <?php echo $comments_count->moderated; ?> </br>
                                
                                    <b>Comments approved: </b> 
                                  <?php echo $comments_count->approved; ?> </br>
                               
                                   <b>Comments in Spam:</b> 
                                   <?php echo $comments_count->spam ; ?> </br>
                                
                                   <b> Comments in Trash: </b> 
                                   <?php echo $comments_count->trash ; ?> </br>
								   <b style="color:red">Note: All comments count will be refresh once any new comments will be added.</b>
                           </div>
                        </div>
                     </div>
	   
	     <iframe id='kofiframe' src='https://instavoty.com/RgaeZ/?hidefeed=true&widget=true&embed=true&preview=true' style='border:none;width:100%;padding:4px;background:#f9f9f9;' height='712' title='navneetsoni'></iframe>
						
	                 
					  
                     <div class="postbox rss-postbox" style="background-color:#d9edf7; background-color:#dff0d8">
                        
                     
                        <br><br>
                     </div>
                    <a href='https://ko-fi.com/X8X125O9R' target='_blank'><img height='36' style='border:0px;height:36px;' src='https://cdn.ko-fi.com/cdn/kofi1.png?v=3' border='0' alt='Buy Me a Coffee at ko-fi.com' /></a>
                        
                  </div>
                  
                  
               </div>
               
                  
                     <!-- #postbox-container-1 -->
                  
               
            </div>
            
               
                  <!-- #post-body -->
               
            
         </div>
         
            
               <!-- #poststuff -->
            
         
      </div>
      
        
            <div class="clear"></div>
         
      
   </div>
   <!-- wpbody-content -->
   
      
         <div class="clear"></div>
      
   
</div>
<!-- wpbody -->
<?php
}
}
?>
