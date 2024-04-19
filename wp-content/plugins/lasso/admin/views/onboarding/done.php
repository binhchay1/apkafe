<?php
use Lasso\Classes\Helper as Lasso_Helper;
?>
<div id="scan" class="tab-item d-none text-center" data-step="done">
    <div class="progressbar_container">
        <?php 
            $params = array( 'active_step' => 7 );
            echo Lasso_Helper::include_with_variables( LASSO_PLUGIN_PATH . '/admin/views/onboarding/steps.php', $params ); 
        ?>
    </div>
    
    <h1 class="font-weight-bold">You're Ready</h1>
    <p>That's it! You're now ready to add Lasso Displays to your site.</p>
    <div class="pt-4">
        <a href="edit.php?post_type=lasso-urls&page=dashboard" type="button" class="btn btn-done badge-pill font-weight-bold hover-down mx-1">
            Go to Dashboard
        </a>
    </div>
</div>
