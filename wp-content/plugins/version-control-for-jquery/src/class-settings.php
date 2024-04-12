<?php

namespace LI\VCFJ;

// Block direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Settings {

	use Traits\Initialise;

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	public function add_admin_menu(): void {
		add_options_page( 'jQuery Version Control', __( 'jQuery Version Control', 'version-control-for-jquery' ), 'manage_options', 'version_control_for_jquery', array( $this, 'render_page' ) );
	}

	public function register_settings(): void {
		register_setting( 'vcfj_settings_page', 'vcfj_settings' );

		add_settings_section(
			'vcfj_pluginPage_section',
			'',
			array( $this, 'section_callback' ),
			'vcfj_settings_page'
		);

		add_settings_field(
			'vcfj_cdn',
			__( 'Select your preferred CDN.', 'version-control-for-jquery' ),
			array( $this, 'output_cdn_options' ),
			'vcfj_settings_page',
			'vcfj_pluginPage_section'
		);

		add_settings_field(
			'vcfj_core_version',
			__( 'Select your desired jQuery Core version.', 'version-control-for-jquery' ),
			array( $this, 'select_core_version' ),
			'vcfj_settings_page',
			'vcfj_pluginPage_section'
		);

		add_settings_field(
			'vcfj_migrate_version',
			__( 'Select your desired jQuery Migrate version.', 'version-control-for-jquery' ),
			array( $this, 'select_migrate_version' ),
			'vcfj_settings_page',
			'vcfj_pluginPage_section'
		);

		add_settings_field(
			'vcfj_core_disable',
			__( 'Disable jQuery Core?', 'version-control-for-jquery' ),
			array( $this, 'output_disable_checkbox' ),
			'vcfj_settings_page',
			'vcfj_pluginPage_section',
			array( 'option' => 'core' )
		);

		add_settings_field(
			'vcfj_migrate_disable',
			__( 'Disable jQuery Migrate?', 'version-control-for-jquery' ),
			array( $this, 'output_disable_checkbox' ),
			'vcfj_settings_page',
			'vcfj_pluginPage_section',
			array( 'option' => 'migrate' )
		);
	}

	public function output_cdn_options(): void {
		$cdn = Helpers::get_cdn();

		$cdns = array(
			'cdnjs'    => 'cdnjs',
			'google'   => 'Google',
			'jquery'   => 'jQuery',
			'jsdelivr' => 'jsDelivr',
		);

		echo '<select name="vcfj_settings[vcfj_cdn]">';
		foreach ( $cdns as $value => $label ) {
			echo sprintf( '<option value="%1$s" %2$s>%3$s</option>', esc_attr( $value ), selected( $cdn, $value, false ), esc_html( $label ) );
		}
		echo '</select>';
	}

	public function select_core_version(): void {
		$version = Helpers::get_version( 'core' );

		$versions = array(
			'latest'    => esc_html__( 'Latest', 'version-control-for-jquery' ),
			'git-build' => 'Git Build',
			'3.7.1'     => '3.7.1',
			'3.7.0'     => '3.7.0',
			'3.6.4'     => '3.6.4',
			'3.6.3'     => '3.6.3',
			'3.6.2'     => '3.6.2',
			'3.6.1'     => '3.6.1',
			'3.6.0'     => '3.6.0',
			'3.5.1'     => '3.5.1',
			'3.5.0'     => '3.5.0',
			'3.4.1'     => '3.4.1',
			'3.4.0'     => '3.4.0',
			'3.3.1'     => '3.3.1',
			'3.3.0'     => '3.3.0',
			'3.2.1'     => '3.2.1',
			'3.2.0'     => '3.2.0',
			'3.1.1'     => '3.1.1',
			'3.1.0'     => '3.1.0',
			'3.0.0'     => '3.0.0',
			'2.2.4'     => '2.2.4',
			'2.2.3'     => '2.2.3',
			'2.2.2'     => '2.2.2',
			'2.2.1'     => '2.2.1',
			'2.2.0'     => '2.2.0',
			'2.1.4'     => '2.1.4',
			'2.1.3'     => '2.1.3',
			'2.1.2'     => '2.1.2',
			'2.1.1'     => '2.1.1',
			'2.1.0'     => '2.1.0',
			'2.0.3'     => '2.0.3',
			'2.0.2'     => '2.0.2',
			'2.0.1'     => '2.0.1',
			'2.0.0'     => '2.0.0',
			'1.12.4'    => '1.12.4',
			'1.12.3'    => '1.12.3',
			'1.12.2'    => '1.12.2',
			'1.12.1'    => '1.12.1',
			'1.12.0'    => '1.12.0',
			'1.11.3'    => '1.11.3',
			'1.11.2'    => '1.11.2',
			'1.11.1'    => '1.11.1',
			'1.11.0'    => '1.11.0',
			'1.10.2'    => '1.10.2',
			'1.10.1'    => '1.10.1',
			'1.10.0'    => '1.10.0',
			'1.9.1'     => '1.9.1',
			'1.9.0'     => '1.9.0',
			'1.8.3'     => '1.8.3',
			'1.8.2'     => '1.8.2',
			'1.8.1'     => '1.8.1',
			'1.8.0'     => '1.8.0',
			'1.7.2'     => '1.7.2',
			'1.7.1'     => '1.7.1',
			'1.7'       => '1.7.0',
			'1.6.4'     => '1.6.4',
			'1.6.3'     => '1.6.3',
			'1.6.2'     => '1.6.2',
			'1.6.1'     => '1.6.1',
			'1.6.0'     => '1.6.0',
			'1.5.2'     => '1.5.2',
			'1.5.1'     => '1.5.1',
			'1.5'       => '1.5.0',
			'1.4.4'     => '1.4.4',
			'1.4.3'     => '1.4.3',
			'1.4.2'     => '1.4.2',
			'1.4.1'     => '1.4.1',
			'1.4.0'     => '1.4.0',
			'1.3.2'     => '1.3.2',
			'1.3.1'     => '1.3.1',
			'1.3'       => '1.3.0',
			'1.2.6'     => '1.2.6',
			'1.2.5'     => '1.2.5',
			'1.2.4'     => '1.2.4',
			'1.2.3'     => '1.2.3',
			'1.2.2'     => '1.2.2',
			'1.2.1'     => '1.2.1',
			'1.2'       => '1.2.0',
		);

		$this->output_select( 'core', $version, $versions );
	}

	public function select_migrate_version(): void {
		$version = Helpers::get_version( 'migrate' );

		$versions = array(
			'latest'    => esc_html__( 'Latest', 'version-control-for-jquery' ),
			'git-build' => 'Git Build',
			'3.4.1'     => '3.4.1',
			'3.4.0'     => '3.4.0',
			'3.3.2'     => '3.3.2',
			'3.3.1'     => '3.3.1',
			'3.3.0'     => '3.3.0',
			'3.2.0'     => '3.2.0',
			'3.1.0'     => '3.1.0',
			'3.0.1'     => '3.0.1',
			'3.0.0'     => '3.0.0',
			'1.4.1'     => '1.4.1',
			'1.4.0'     => '1.4.0',
			'1.3.0'     => '1.3.0',
			'1.2.1'     => '1.2.1',
			'1.2.0'     => '1.2.0',
			'1.1.1'     => '1.1.1',
			'1.1.0'     => '1.1.0',
			'1.0.0'     => '1.0.0',
		);

		$this->output_select( 'migrate', $version, $versions );
	}

	public function output_disable_checkbox( array $args ): void {
		$name    = sprintf( 'vcfj_%s_disable', $args['option'] );
		$checked = Helpers::is_disabled( $args['option'] ) ? 'checked="checked"' : '';

		echo sprintf( '<input type="checkbox" name="vcfj_settings[%1$s]" value="1" %2$s />', esc_attr( $name ), esc_attr( $checked ) );
	}

	private function output_select( string $type, string $current, array $versions ): void {
		$select_name = sprintf( 'vcfj_%s_version', $type );

		echo sprintf( '<select name="vcfj_settings[%s]">', esc_attr( $select_name ) );
		foreach ( $versions as $version => $label ) {
			echo sprintf( '<option value="%1$s" %2$s>%3$s</option>', esc_attr( $version ), selected( $current, $version, false ), esc_html( $label ) );
		}
		echo '</select>';
	}

	public function section_callback(): void {
		echo '<p>' . esc_html__( 'Use the dropdown selectors below to select your desired version of jQuery. Please note that the plugin defaults to the latest stable version.', 'version-control-for-jquery' ) . '</p>';
	}

	public function render_page(): void { ?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Version Control for jQuery', 'version-control-for-jquery' ); ?></h1>
			<form action="options.php" method="post">
				<?php
				settings_fields( 'vcfj_settings_page' );
				do_settings_sections( 'vcfj_settings_page' );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}

}

Settings::initialise();
