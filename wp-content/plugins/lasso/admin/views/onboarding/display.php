<?php
use Lasso\Classes\Setting as Lasso_Setting;
use Lasso\Classes\Helper as Lasso_Helper;

$lasso_options = Lasso_Setting::lasso_get_settings();
	
$show_price_attr       = $lasso_options['show_price'] ? 'checked' : '';
$show_disclosure_attr  = $lasso_options['show_disclosure'] ? 'checked' : '';
?>

<div id="theme-customize" class="tab-item d-none" data-step="customize">
    <div class="progressbar_container">
        <?php 
            $params = array( 'active_step' => 3 );
            echo Lasso_Helper::include_with_variables( LASSO_PLUGIN_PATH . '/admin/views/onboarding/steps.php', $params ); 
        ?>
    </div>

    <div class="onboarding_header text-center mb-4">
    <h1>Customize Your Displays</h1>
    <p class="pb-3">Don't worry, you can easily change all of these settings later.</p>
    </div>
    
    <div class="onboarding_display_container pb-3">
        <div id="demo_display_box"></div>
        <div class="image_loading onboarding d-none"></div>
    </div>
    
    <form class="lasso-admin-settings-form" autocomplete="off">
    <div class="row">
        <input type="text" id="theme_name" name="theme_name" class="d-none" />
        <div class="col-lg-6 mb-lg-0 mb-5 h-100">
            <div class="form-group">
                <div class="form-row mb-4">
                    <div class="col-lg">
                        <label data-tooltip="This is the color of your badge background."><strong>Badge</strong> <i class="far fa-info-circle light-purple"></i></label>
                        <input type="text" name="display_color_main" value="<?php echo $lasso_options['display_color_main']; ?>" class="form-control color-picker" placeholder="#5E36CA" />
                    </div>
            
                    <div class="col-lg">
                        <label data-tooltip="This is the color of the title text of your display."><strong>Title</strong> <i class="far fa-info-circle light-purple"></i></label>
                        <input type="text" name="display_color_title" value="<?php echo $lasso_options['display_color_title']; ?>" class="form-control color-picker" placeholder="#FFFFFF" />
                    </div>
                </div>
                
                <div class="form-row mb-4">
                    <div class="col-lg">
                        <label data-tooltip="This is the color of the inside of your display."><strong>Background</strong> <i class="far fa-info-circle light-purple"></i></label>
                        <input type="text" name="display_color_background" value="<?php echo $lasso_options['display_color_background']; ?>" class="form-control color-picker" placeholder="#FFFFFF" />
                    </div>
                    
                    <div class="col-lg">
                        <label data-tooltip="This is text color for your badges and buttons."><strong>Button + Badge Text</strong> <i class="far fa-info-circle light-purple"></i></label>
                        <input type="text" name="display_color_button_text" value="<?php echo $lasso_options['display_color_button_text']; ?>" class="form-control color-picker" placeholder="#FFFFFF" />
                    </div>
                </div>
                
                <div class="form-row mb-4">
                    <div class="col-lg">
                        <label data-tooltip="This is the main color of the Pros Field."><strong>Pros</strong> <i class="far fa-info-circle light-purple"></i></label>
                        <input type="text" name="display_color_pros" value="<?php echo $lasso_options['display_color_pros']; ?>" class="form-control color-picker" placeholder="#FFFFFF" />
                    </div>
                    
                    <div class="col-lg">
                        <label data-tooltip="This is the main color of the Cons Field."><strong>Cons</strong> <i class="far fa-info-circle light-purple"></i></label>
                        <input type="text" name="display_color_cons" value="<?php echo $lasso_options['display_color_cons']; ?>" class="form-control color-picker" placeholder="#FFFFFF" />
                    </div>
                </div>

                <div class="form-row">
                    <div class="col-lg-6">
                        <label class="toggle m-0 mr-1">
                            <input type="checkbox" id="show_price" name="show_price" <?php echo $show_price_attr; ?> >
                            <span class="slider"></span>
                        </label>
                        <label data-tooltip="Turn this on to show the price in Displays by default. You can override this per display.">Show Price <i class="far fa-info-circle light-purple"></i></label>
                    </div>
            
                    <div class="col-lg-6">
                        <label class="toggle m-0 mr-1">
                            <input type="checkbox" id="show_disclosure" name="show_disclosure" <?php echo $show_disclosure_attr; ?> >
                            <span class="slider"></span>
                        </label>
                        <label data-tooltip="Turn this on to show the disclosure in Displays by default. You can override this per display.">Show Disclosure <i class="far fa-info-circle light-purple"></i></label>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-6 mb-lg-0 mb-5 h-100">
            <div class="form-row mb-4">
                <div class="col-lg">
                    <label data-tooltip="If you leave your display button text blank, this is what it will default to."><strong>Primary Button</strong> <i class="far fa-info-circle light-purple"></i></label>
                    <input type="text" name="primary_button_text" value="<?php echo $lasso_options['primary_button_text']; ?>" class="form-control" placeholder="Buy Now" />
                </div>
                
                <div class="col-lg">
                    <label data-tooltip="This is the color of your display's main CTA button.">&nbsp;</label>
                    <input type="text" name="display_color_button" value="<?php echo $lasso_options['display_color_button']; ?>" class="form-control color-picker" placeholder="#22BAA0" />
                </div>
            </div>
            
            <div class="form-row mb-4">
                <div class="col-lg">
                    <label data-tooltip="If you set a secondary button for your display and leave it blank, this is what it will default to."><strong>Secondary Button</strong> <i class="far fa-info-circle light-purple"></i></label>
                    <input type="text" name="secondary_button_text" value="<?php echo $lasso_options['secondary_button_text']; ?>" class="form-control" placeholder="Learn More" />
                </div>
        
                <div class="col-lg">
                    <label data-tooltip="This is the color of your display's secondary CTA button.">&nbsp;</label>
                    <input type="text" name="display_color_secondary_button" value="<?php echo $lasso_options['display_color_secondary_button']; ?>" class="form-control color-picker" placeholder="#22BAA0" />
                </div>
            </div>
            
            <div class="form-group mb-1">
                <label data-tooltip="This is the default disclosure text used with your displays."><strong>Disclosure</strong> <i class="far fa-info-circle light-purple"></i></label>
                <textarea id="disclosure_text" name="disclosure_text" class="form-control" rows="4"><?php echo $lasso_options['disclosure_text']; ?></textarea>
            </div>
        </div>
    </div>
    </form>
    
    <!-- SAVE CHANGES -->
    <div class="row align-items-center mt-4">
        <div class="col-lg text-lg-left text-left">
            <button class="btn prev-step">&larr; Change Themes</button>
        </div>
        <div class="col-lg text-lg-right text-center">
            <button class="btn btn-outline-dark bg-white text-dark next-step">Skip &rarr;</button>
            <button class="btn btn-save-display next-step">Save and Continue &rarr;</button>
        </div>
    </div> 
</div>
