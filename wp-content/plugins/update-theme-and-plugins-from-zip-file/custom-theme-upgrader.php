<?php
/* Update Theme and Plugins from Zip File by Jeff Sherk */

class JDS_UTPZIP_Theme_Upgrader extends Theme_Upgrader {
	public function install_package( $args = array() ) {
		global $wp_filesystem;

		//error_reporting( E_ALL ); //debug only
		//ini_set( 'display_errors', 1 ); //debug only

		if ( empty( $args['source'] ) || empty( $args['destination'] ) ) {
			return parent::install_package( $args );
		}

		$source_files = array_keys( $wp_filesystem->dirlist( $args['source'] ) );
		$remote_destination = $wp_filesystem->find_folder( $args['destination'] );

		if ( 1 === count( $source_files ) && $wp_filesystem->is_dir( trailingslashit( $args['source'] ) . $source_files[0] . '/' ) ) { // One folder. Get contents.
			$destination = trailingslashit( $remote_destination ) . trailingslashit( $source_files[0] );
		} elseif ( 0 === count( $source_files ) ) {
			// Empty zip
			return parent::install_package( $args );
		} else { // Single file only.
			$destination = trailingslashit( $remote_destination ) . trailingslashit( basename( $args['source'] ) );
		}

		if ( is_dir( $destination ) && file_exists( "$destination/style.css" ) ) {
			$args['clear_destination'] = true;
			$this->upgrade_strings();
			$this->strings['installing_package'] = __( 'Upgrading your theme...', 'update-theme-and-plugins-from-zip-file' );
			
			$backup_enabled = get_option("jdsutpzip_backup_enabled");
			if ($backup_enabled == "on") {
				$this->strings['remove_old'] = __( 'Backing up old version of the theme...', 'update-theme-and-plugins-from-zip-file' );
			} else {
				$this->strings['remove_old'] = __( 'No backup of old version of theme created. Backup option disabled in settings.', 'update-theme-and-plugins-from-zip-file' );
			}
		}

		return parent::install_package( $args );
	}

	public function clear_destination( $destination ) {
		global $wp_filesystem;

		if ( ! is_dir( $destination ) || ! file_exists( "$destination/style.css" ) ) {
			// New install
			return parent::clear_destination( $destination );
		}
		
		$backup_enabled = get_option("jdsutpzip_backup_enabled");
		if ($backup_enabled == "on") { //backup enabled
		
			$backup_url = $this->create_backup( $destination );
	
			if ( ! is_wp_error( $backup_url ) ) {

				$this->skin->feedback( sprintf( __( 'Backup Created: Download zip file from <a href="%1$s">here</a>', 'update-theme-and-plugins-from-zip-file' ), $backup_url ) );
	
				$this->upgrade_strings();
				$this->skin->feedback( 'remove_old' );
	
				return parent::clear_destination( $destination );
			}
	
			print_r($backup_url); //display the error array... not optimum but works okay for now
			//$this->skin->error( sprintf(__( 'NO BACKUP CREATED: Error accessing/creating backup directory/file <a href="%1$s">here</a>.', 'update-theme-and-plugins-from-zip-file' ), $backup_url ) );

		} else { //backup disabled

		}

		$this->upgrade_strings();
		$this->skin->feedback( 'remove_old' );

		return parent::clear_destination( $destination );
	}

	private function create_backup( $directory ) {
		require_once( ABSPATH . 'wp-admin/includes/file.php' );

		$wp_upload_dir = wp_upload_dir();

		$zip_path = $wp_upload_dir['path'];
		$zip_url  = $wp_upload_dir['url'];

		if ( ! is_dir( $zip_path ) ) {
			return new WP_Error( 'jds-utpzip-cannot-backup-no-destination-path', __( 'BACKUP NOT CREATED: Theme backup can not be created since a destination path for the backup file could not be found: '.$zip_path, 'update-theme-and-plugins-from-zip-file' ) );
		}

		$headers = array(
			'name'    => 'Theme Name',
			'version' => 'Version',
		);
		$data = get_file_data( "$directory/style.css", $headers );

		$rand_string = $this->get_random_characters( 10, 20 );
		$zip_file = basename( $directory ) . "-{$data['version']}-$rand_string.zip";

		// Reduce the chance that a timeout will occur while creating the zip file.
		@set_time_limit( 600 );

		// Attempt to increase memory limits.
		$this->set_minimum_memory_limit( '256M' );

		$zip_path .= "/$zip_file";
		$zip_url  .= "/$zip_file";

		require_once( ABSPATH . 'wp-admin/includes/class-pclzip.php' );

		$archive = new PclZip( $zip_path );

		$zip_result = $archive->create( $directory, PCLZIP_OPT_REMOVE_PATH, dirname( $directory ) );

		if ( 0 === $zip_result ) {
			return new WP_Error( 'jds-utpzip-cannot-backup-zip-failed', __( 'BACKUP NOT CREATED: Theme backup can not be created as creation of the zip file failed with the following error: '.$archive->errorInfo(true), 'update-theme-and-plugins-from-zip-file' ) );
		}

		$attachment = array(
			'post_mime_type' => 'application/zip',
			'guid'           => $zip_url,
			'post_title'     => sprintf( __( 'Theme Backup - %1$s - %2$s', 'update-theme-and-plugins-from-zip-file' ), $data['name'], $data['version'] ),
			'post_content'   => '',
		);

		$id = wp_insert_attachment( $attachment, $zip_path );

		if ( ! is_wp_error( $id ) ) {
			wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $zip_path ) );
		}

		return $zip_url;
	}

	private function get_random_characters( $min_length, $max_length ) {
		$characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
		$rand_string = '';
		$length = rand( $min_length, $max_length );

		for ( $count = 0; $count < $length; $count++ ) {
			$rand_string .= $characters[ rand( 0, strlen( $characters ) - 1 ) ];
		}

		return $rand_string;
	}

	function set_minimum_memory_limit( $new_memory_limit ) {
		$memory_limit = @ini_get( 'memory_limit' );

		if ( $memory_limit > -1 ) {
			$unit = strtolower( substr( $memory_limit, -1 ) );
			$new_unit = strtolower( substr( $new_memory_limit, -1 ) );

			$memory_limit = intval( $memory_limit );
			$new_memory_limit = intval( $new_memory_limit );

			if ( 'm' == $unit ) {
				$memory_limit *= 1048576;
			} elseif ( 'g' == $unit ) {
				$memory_limit *= 1073741824;
			} elseif ( 'k' == $unit ) {
				$memory_limit *= 1024;
			}

			if ( 'm' == $new_unit ) {
				$new_memory_limit *= 1048576;
			} else if ( 'g' == $new_unit ) {
				$new_memory_limit *= 1073741824;
			} else if ( 'k' == $new_unit ) {
				$new_memory_limit *= 1024;
			}

			if ( (int) $memory_limit < (int) $new_memory_limit ) {
				@ini_set( 'memory_limit', $new_memory_limit );
			}
		}
	}
}
