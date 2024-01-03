<?php

/**
 * Outputs the bulk translate form
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Don't access directly
};

?>
<form method="get"><table style="display: none"><tbody id="pll-bulk-translate">
	<tr id="pll-translate" class="inline-edit-row">
		<td>
			<fieldset>
				<legend class="inline-edit-legend"><?php esc_html_e( 'Bulk Translate', 'polylang-pro' ); ?></legend>
				<div class="inline-edit-col">
				<?php
				foreach ( $this->model->get_languages_list() as $language ) {
					printf(
						'<label><input name="pll-translate-lang[]" type="checkbox" value="%s" /><span class="pll-translation-flag">%s</span>%s</label>',
						esc_attr( $language->slug ),
						$language->flag,
						esc_html( $language->name )
					);
				}
				?>
				</div>
			</fieldset>
			<fieldset>
				<div class="inline-edit-col">
					<span class="title"><?php esc_html_e( 'Action', 'polylang-pro' ); ?></span>
					<label><input name="translate" type="radio" value="copy" checked="checked" /><?php esc_html_e( 'Copy originals to selected languages', 'polylang-pro' ); ?></label>
					<?php if ( 'attachment' !== $post_type ) { ?>
					<label><input name="translate" type="radio" value="sync" /><?php esc_html_e( 'Synchronize originals with selected languages', 'polylang-pro' ); ?></label>
					<?php } ?>
				</div>
			</fieldset>
			<p class="submit bulk-translate-save">
				<button type="button" class="button button-secondary cancel"><?php esc_html_e( 'Cancel' ); ?></button>
				<?php submit_button( __( 'Submit' ), 'primary', '', false ); ?>
			</p>
		</td>
	</tr>
</tbody></table></form>
