<?php
use Lasso\Classes\Helper as Lasso_Helper;

$should_show_import_step = Lasso_Helper::should_show_import_page();

$steps = array(
    'Activate',
    'Theme',
    'Customize',
    'Amazon',
    'Analytics',
    'Imports',
    'Done',
);

$import_key = array_search( 'Imports', $steps );
if ( ! $should_show_import_step ) {
    unset( $steps[ $import_key ] );
}
$step_width = floor( 100 / count( $steps ) );
?>

<ul class="progressbar">
<?php foreach($steps as $step => $name): 
    $current_step = $step + 1;
    $complete_class = $current_step < $active_step ? 'complete' : '';
    $active_class = $current_step === $active_step ? 'active' : '';
    $classes = 'step-' . $current_step . ' ' . $complete_class . ' ' . $active_class;
    $classes = Lasso_Helper::trim( $classes );
    $data_step = str_replace( ' ', '-', strtolower( $name ) );
?>
    <li class="<?php echo $classes; ?>" data-step="<?php echo $data_step; ?>" style="width: <?php echo $step_width; ?>%">
        <?php echo $name ?>
    </li>
<?php endforeach; ?>
</ul>
