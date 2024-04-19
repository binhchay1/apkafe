<?php
use Lasso\Classes\Helper as Lasso_Helper;
?>

<div id="theme-select" class="tab-item d-none text-center" data-step="theme">
    <div class="progressbar_container">
        <?php 
            $params = array( 'active_step' => 2 );
            echo Lasso_Helper::include_with_variables( LASSO_PLUGIN_PATH . '/admin/views/onboarding/steps.php', $params ); 
        ?>
    </div>

    <h1>Choose a Default Display Box Theme</h1>
    <p class="pb-4">This is an example of how a product will look on your site with Lasso.</p>

    <div class="row">
        <div class="col-6 pl-1 pr-3 h-100 text-center">
            <div class="choose_display_onboard cactus">
                <div class="display_name badge green-bg white">Cactus</div>
                <img src="<?php echo LASSO_PLUGIN_URL; ?>admin/assets/images/displays/cactus.jpg" />
            </div>
            <div class="choose_display_onboard flow">
                <div class="display_name badge green-bg white">Flow</div>
                <img src="<?php echo LASSO_PLUGIN_URL; ?>admin/assets/images/displays/flow.jpg" />
            </div>
            <div class="choose_display_onboard lab">
                <div class="display_name badge green-bg white">Lab</div>
                <img src="<?php echo LASSO_PLUGIN_URL; ?>admin/assets/images/displays/lab.jpg" />
            </div>
            <div class="choose_display_onboard money">
                <div class="display_name badge green-bg white">Money</div>
                <img src="<?php echo LASSO_PLUGIN_URL; ?>admin/assets/images/displays/money.jpg" />
            </div>
        </div>
        
        <div class="col-6 pl-3 pr-1 h-100 text-center">
            <div class="choose_display_onboard cutter">
                <div class="display_name badge green-bg white">Cutter</div>
                <img src="<?php echo LASSO_PLUGIN_URL; ?>admin/assets/images/displays/cutter.jpg" />
            </div>
            <div class="choose_display_onboard geek">
                <div class="display_name badge green-bg white">Geek</div>
                <img src="<?php echo LASSO_PLUGIN_URL; ?>admin/assets/images/displays/geek.jpg" />
            </div>
            <div class="choose_display_onboard llama">
                <div class="display_name badge green-bg white">Llama</div>
                <img src="<?php echo LASSO_PLUGIN_URL; ?>admin/assets/images/displays/llama.jpg" />
            </div>
            <div class="choose_display_onboard splash">
                <div class="display_name badge green-bg white">Splash</div>
                <img src="<?php echo LASSO_PLUGIN_URL; ?>admin/assets/images/displays/splash.jpg" />
            </div>
        </div>
    </div>
</div>
