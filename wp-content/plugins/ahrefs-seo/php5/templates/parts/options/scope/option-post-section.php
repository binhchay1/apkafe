<?php

namespace ahrefs\AhrefsSeo;

$locals = Ahrefs_Seo_View::get_template_variables();
/**
@var string $title
@var string $var_enabled_name
@var string $var_name
@var array<string,string> $taxonomies_list
@var string|null $selected_taxonomy Selected taxonomy name
@var bool $is_enabled
@var bool $id_mode Filter by individual pages ID.
@var array<string,string[]> $tax_values  Index is tax name, value is list of selected term ID.
@var int[] $id_values List of enabled page ID.
*/
$title             = $locals['title'];
$var_enabled_name  = $locals['var_enabled_name'];
$var_name          = $locals['var_name'];
$taxonomies_list   = $locals['taxonomies_list'];
$selected_taxonomy = $locals['selected_taxonomy'];
$is_enabled        = $locals['is_enabled'];
$id_mode           = $locals['id_mode'];
$tax_values        = $locals['tax_values'];
$id_values         = $locals['id_values'];
?>
<div class="input-wrap block-content scope-block-n post-tax-option">
	<span href="#" class="show-collapsed block-subtitle">
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
			if ( $id_mode ) {
				/* translators: {0}: placeholder for the first number, {1}: placeholder for the second number in phrase "2 of 10" selected. */
				esc_attr_e( '{0} of {1}', 'ahrefs-seo' );
			} else {
				/* translators: {0}: placeholder for the first number, {1}: placeholder for the second number in phrase "2 of 10 categories" selected. */
				esc_attr_e( '{0} of {1} categories', 'ahrefs-seo' );
			} ?>"></span>
		</label>
	</span>

	<div class="collapsed-wrap">
		<div class="block-text">
			<div>
				<?php
				if ( $id_mode ) { // by page ID mode.
					$content    = new Ahrefs_Seo_Content_Settings();
					$pages_list = $content->get_pages_list();
					$_tax       = 'ID';
					$_tax_title = 'ID';
					?>
					<ul
						class="subitems-n"
						data-tax="<?php echo esc_attr( $_tax ); ?>"
						data-title="<?php echo esc_attr( $_tax_title ); ?>"
						data-var="<?php echo esc_attr( $var_name . '___' . $_tax ); ?>"
						>
						<?php
						foreach ( $pages_list as $id => $title ) {
							?>
							<li>
								<label class="selectit">
									<input type="checkbox" name="pages[]" value="<?php echo esc_attr( "{$id}" ); ?>" id="<?php echo esc_attr( 'page_' . $id ); ?>" 
																							<?php
																							checked( in_array( $id, $id_values, true ) );
																							?>
		>
										<?php
										echo esc_html( $title );
										?>
								</label><span class="height26px"></span>
							</li>
									<?php
						}
						?>
					</ul>
					<?php
				} else { // by taxonomy mode.
					foreach ( $taxonomies_list as $_tax => $_tax_title ) {
						if ( $_tax === $selected_taxonomy ) {
							$checked_items = isset( $tax_values[ $_tax ] ) && is_array( $tax_values[ $_tax ] ) ? $tax_values[ $_tax ] : [];
							?>
							<ul
								class="subitems-n"
								data-tax="<?php echo esc_attr( $_tax ); ?>"
								data-title="<?php echo esc_attr( $_tax_title ); ?>"
								data-var="<?php echo esc_attr( $var_name . '___' . $_tax ); ?>"
								>
								<?php
								Helper_Content::get()->terms_checklists( $_tax, $checked_items );
								?>
							</ul>
							<?php
						}
					}
				}
				?>
			</div>


		</div>
	</div>
</div>
<?php 