<?php
declare(strict_types=1);
namespace ahrefs\AhrefsSeo;

$locals = Ahrefs_Seo_View::get_template_variables();

/**
@var string $title
@var string $var_name
@var bool $is_enabled
@var bool $is_post
*/
$title            = $locals['title'];
$var_enabled_name = $locals['var_enabled_name'];
$is_enabled       = $locals['is_enabled'];
$is_post          = $locals['is_post'];

?>
<li class="popular-category">
	<label class="selectit"><input value="1" type="checkbox" name="<?php echo esc_attr( $var_enabled_name ); ?>" <?php checked( $is_enabled ); ?>>
	<?php
		echo esc_html( $title );
	if ( $is_post ) {
		?>
			<span class="badge-post-type" title="<?php esc_attr_e( 'Custom Post Type', 'ahrefs-seo' ); ?>"><?php esc_html_e( 'CPT', 'ahrefs-seo' ); ?></span>
			<?php
	}
	?>
	</label>
</li>
<?php
