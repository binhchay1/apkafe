<?php
/**
 * Modal
 *
 * @package Modal
 */

// phpcs:ignore
?>

<div id="ga-tracking-detected" class="row alert orange-bg white shadow mb-0 collapse show" data-toggle="collapse">
    <div class="col text-center font-weight-bold p-3">
        Google Analytics Event Tracking isn't enabled. We recommend you configure this.
        <a href="edit.php?post_type=lasso-urls&page=settings-general" class="btn ml-2">Setup</a>
        <a href="#ga-tracking-detected" class="btn red-bg ml-1" data-toggle="collapse" onclick="dismiss_ga_tracking()">Dismiss</a>
    </div>
</div>
