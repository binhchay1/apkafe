<?php

namespace ahrefs\AhrefsSeo;

$locals = Ahrefs_Seo_View::get_template_variables();
/**
@var string $title
@var string $var_enabled_name
@var bool $is_enabled
*/
$title            = $locals['title'];
$var_enabled_name = $locals['var_enabled_name'];
$is_enabled       = $locals['is_enabled'];
?>
<div class="input-wrap block-content scope-block-n block-section-singles">
	<span class="show-my-google-config show-collapsed block-subtitle">
		<label>
			<input type="checkbox" value="1" name="<?php echo esc_attr( $var_enabled_name ); ?>" class="checkbox-main" 
																<?php
																checked( $is_enabled );
																?>
			>
			<?php
			echo esc_html( $title );
			?>
			<span class="how-much-selected" data-text="
			<?php
/* translators: {0}: placeholder for the first number, {1}: placeholder for the second number in phrase "2 of 10" selected. */
			esc_attr_e( '{0} of {1}', 'ahrefs-seo' ); ?>"></span>
		</label>
	</span>
	<div class="collapsed-wrap">
		<div class="block-text">
			<div>
				<ul class="subitems-n">
					<?php 