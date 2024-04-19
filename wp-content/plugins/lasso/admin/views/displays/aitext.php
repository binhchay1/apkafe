<?php
/**
 * AIText
 *
 * @package AIText
 */
/** @var string $chatgpt_response */
?>

<?php if( $chatgpt_response ): ?>
<div class="lasso-container lasso-ai">
    <div class="lasso-ai-text-container">
        <div class="lasso-ai-text">
            <?php echo $chatgpt_response; ?>
        </div>
    </div>
</div>
<?php endif; ?>
