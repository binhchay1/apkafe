<?php

class Meow_MWAI_Rest
{
	private $core = null;
	private $namespace = 'mwai/v1';

	public function __construct( $core ) {
		$this->core = $core;
		add_action( 'rest_api_init', array( $this, 'rest_init' ) );
	}

	/**
	 * Retrieve the message from the parameters and optionally sanitize it.
	 *
	 * @param array &$params The parameters array, passed by reference.
	 * @param bool $sanitize Whether to sanitize the message using sanitize_text_field.
	 * @return string The retrieved (and optionally sanitized) message.
	 */
	function retrieve_message( &$params, $sanitize = false ) : string {
		if ( isset( $params['message'] ) ) {
			$message = $params['message'];
		}
		elseif ( isset( $params['prompt'] ) ) {
			$message = $params['prompt'];
			unset( $params['prompt'] );
			$params['message'] = $message;
			error_log( 'AI Engine: "prompt" is deprecated, please use "message" instead.' );
		}
		else {
			$message = "";
		}

		if ( $sanitize ) {
			$message = sanitize_text_field( $message );
		}

		return $message;
	}

	function rest_init() {
		try {
			// Settings Endpoints
			register_rest_route( $this->namespace, '/settings/update', array(
				'methods' => 'POST',
				'permission_callback' => [ $this->core, 'can_access_settings' ],
				'callback' => [ $this, 'rest_settings_update' ],
			) );
			register_rest_route( $this->namespace, '/settings/options', array(
				'methods' => 'GET',
				'permission_callback' => [ $this->core, 'can_access_settings' ],
				'callback' => [ $this, 'rest_settings_list' ],
			) );
			register_rest_route( $this->namespace, '/settings/reset', array(
				'methods' => 'POST',
				'permission_callback' => [ $this->core, 'can_access_settings' ],
				'callback' => [ $this, 'rest_settings_reset' ],
			) );
			register_rest_route( $this->namespace, '/settings/chatbots', array(
				'methods' => ['GET', 'POST'],
				'permission_callback' => [ $this->core, 'can_access_settings' ],
				'callback' => [ $this, 'rest_settings_chatbots' ],
			) );
			register_rest_route( $this->namespace, '/settings/themes', array(
				'methods' => ['GET', 'POST'],
				'permission_callback' => [ $this->core, 'can_access_settings' ],
				'callback' => [ $this, 'rest_settings_themes' ],
			) );

			// System Endpoints
			register_rest_route( $this->namespace, '/system/logs/list', array(
				'methods' => 'POST',
				'permission_callback' => [ $this->core, 'can_access_settings' ],
				'callback' => [ $this, 'rest_system_logs_list' ],
			) );
			register_rest_route( $this->namespace, '/system/logs/delete', array(
				'methods' => 'POST',
				'permission_callback' => [ $this->core, 'can_access_settings' ],
				'callback' => [ $this, 'rest_system_logs_delete' ],
			) );
			register_rest_route( $this->namespace, '/system/logs/meta', array(
				'methods' => 'POST',
				'permission_callback' => [ $this->core, 'can_access_settings' ],
				'callback' => [ $this, 'rest_system_logs_meta_get' ],
			) );
			register_rest_route( $this->namespace, '/system/templates', array(
				'methods' => 'POST',
				'permission_callback' => [ $this->core, 'can_access_features' ],
				'callback' => [ $this, 'rest_system_templates_save' ],
			) );
			register_rest_route( $this->namespace, '/system/templates', array(
				'methods' => 'GET',
				'permission_callback' => [ $this->core, 'can_access_features' ],
				'callback' => [ $this, 'rest_system_templates_get' ],
			) );

			// AI Endpoints
			register_rest_route( $this->namespace, '/ai/models', array(
				'methods' => 'POST',
				'permission_callback' => [ $this->core, 'can_access_features' ],
				'callback' => [ $this, 'rest_ai_models' ],
			) );
			register_rest_route( $this->namespace, '/ai/completions', array(
				'methods' => 'POST',
				'permission_callback' => [ $this->core, 'can_access_features' ],
				'callback' => [ $this, 'rest_ai_completions' ],
			) );
			register_rest_route( $this->namespace, '/ai/images', array(
				'methods' => 'POST',
				'permission_callback' => [ $this->core, 'can_access_features' ],
				'callback' => [ $this, 'rest_ai_images' ],
			) );
			register_rest_route( $this->namespace, '/ai/copilot', array(
				'methods' => 'POST',
				'permission_callback' => [ $this->core, 'can_access_features' ],
				'callback' => [ $this, 'rest_ai_copilot' ],
			) );

			register_rest_route( $this->namespace, '/ai/magic_wand', array(
				'methods' => 'POST',
				'callback' => [ $this, 'rest_ai_magic_wand' ],
				'permission_callback' => [ $this->core, 'can_access_features' ],
			) );
			register_rest_route( $this->namespace, '/ai/moderate', array(
				'methods' => 'POST',
				'permission_callback' => [ $this->core, 'can_access_settings' ],
				'callback' => [ $this, 'rest_ai_moderate' ],
			) );
			register_rest_route( $this->namespace, '/ai/transcribe_audio', array(
				'methods' => 'POST',
				'permission_callback' => [ $this->core, 'can_access_settings' ],
				'callback' => [ $this, 'rest_ai_transcribe_audio' ],
			) );
			register_rest_route( $this->namespace, '/ai/transcribe_image', array(
				'methods' => 'POST',
				'permission_callback' => [ $this->core, 'can_access_settings' ],
				'callback' => [ $this, 'rest_ai_transcribe_image' ],
			) );
			register_rest_route( $this->namespace, '/ai/json', array(
				'methods' => 'POST',
				'permission_callback' => [ $this->core, 'can_access_settings' ],
				'callback' => [ $this, 'rest_ai_json' ],
			) );

			// Helpers Endpoints
			register_rest_route( $this->namespace, '/helpers/update_post_title', array(
				'methods' => 'POST',
				'permission_callback' => [ $this->core, 'can_access_features' ],
				'callback' => [ $this, 'rest_helpers_update_title' ],
			) );
			register_rest_route( $this->namespace, '/helpers/update_post_excerpt', array(
				'methods' => 'POST',
				'permission_callback' => [ $this->core, 'can_access_features' ],
				'callback' => [ $this, 'rest_helpers_update_excerpt' ],
			) );
			register_rest_route( $this->namespace, '/helpers/create_post', array(
				'methods' => 'POST',
				'permission_callback' => [ $this->core, 'can_access_features' ],
				'callback' => [ $this, 'rest_helpers_create_post' ],
			) );
			register_rest_route( $this->namespace, '/helpers/create_image', array(
				'methods' => 'POST',
				'permission_callback' => [ $this->core, 'can_access_features' ],
				'callback' => [ $this, 'rest_helpers_create_images' ],
			) );
			register_rest_route( $this->namespace, '/helpers/count_posts', array(
				'methods' => 'GET',
				'permission_callback' => [ $this->core, 'can_access_features' ],
				'callback' => [ $this, 'rest_helpers_count_posts' ],
			) );
			register_rest_route( $this->namespace, '/helpers/posts_ids', array(
				'methods' => 'GET',
				'permission_callback' => [ $this->core, 'can_access_features' ],
				'callback' => [ $this, 'rest_helpers_posts_ids' ],
			) );
			register_rest_route( $this->namespace, '/helpers/post_types', array(
				'methods' => 'GET',
				'permission_callback' => [ $this->core, 'can_access_features' ],
				'callback' => [ $this, 'rest_helpers_post_types' ],
			) );
			register_rest_route( $this->namespace, '/helpers/post_content', array(
				'methods' => 'GET',
				'permission_callback' => [ $this->core, 'can_access_features' ],
				'callback' => [ $this, 'rest_helpers_post_content' ],
			) );

			// OpenAI Endpoints
			register_rest_route( $this->namespace, '/openai/files/list', array(
				'methods' => 'GET',
				'permission_callback' => [ $this->core, 'can_access_settings' ],
				'callback' => [ $this, 'rest_openai_files_get' ],
			) );
			register_rest_route( $this->namespace, '/openai/files/upload', array(
				'methods' => 'POST',
				'permission_callback' => [ $this->core, 'can_access_settings' ],
				'callback' => [ $this, 'rest_openai_files_upload' ],
			) );
			register_rest_route( $this->namespace, '/openai/files/delete', array(
				'methods' => 'POST',
				'permission_callback' => [ $this->core, 'can_access_settings' ],
				'callback' => [ $this, 'rest_openai_files_delete' ],
			) );
			register_rest_route( $this->namespace, '/openai/files/download', array(
				'methods' => 'POST',
				'permission_callback' => [ $this->core, 'can_access_settings' ],
				'callback' => [ $this, 'rest_openai_files_download' ],
			) );
			register_rest_route( $this->namespace, '/openai/files/finetune', array(
				'methods' => 'POST',
				'permission_callback' => [ $this->core, 'can_access_settings' ],
				'callback' => [ $this, 'rest_openai_files_finetune' ],
			) );
			register_rest_route( $this->namespace, '/openai/finetunes/list_deleted', array(
				'methods' => 'GET',
				'permission_callback' => [ $this->core, 'can_access_settings' ],
				'callback' => [ $this, 'rest_openai_deleted_finetunes_get' ],
			) );

			// register_rest_route( $this->namespace, '/openai/models', array(
			// 	'methods' => 'GET',
			// 	'permission_callback' => [ $this->core, 'can_access_settings' ],
			// 	'callback' => [ $this, 'rest_openai_models_get' ],
			// ) );

			register_rest_route( $this->namespace, '/openai/finetunes/list', array(
				'methods' => 'GET',
				'permission_callback' => [ $this->core, 'can_access_settings' ],
				'callback' => [ $this, 'rest_openai_finetunes_get' ],
			) );
			register_rest_route( $this->namespace, '/openai/finetunes/delete', array(
				'methods' => 'POST',
				'permission_callback' => [ $this->core, 'can_access_settings' ],
				'callback' => [ $this, 'rest_openai_finetunes_delete' ],
			) );
			register_rest_route( $this->namespace, '/openai/finetunes/cancel', array(
				'methods' => 'POST',
				'permission_callback' => [ $this->core, 'can_access_settings' ],
				'callback' => [ $this, 'rest_openai_finetunes_cancel' ],
			) );
			register_rest_route( $this->namespace, '/openai/incidents', array(
				'methods' => 'GET',
				'permission_callback' => [ $this->core, 'can_access_settings' ],
				'callback' => [ $this, 'rest_openai_incidents' ],
			) );	
		}
		catch ( Exception $e ) {
			var_dump( $e );
		}
	}

	function rest_settings_list() {
		return new WP_REST_Response( [
			'success' => true,
			'options' => $this->core->get_all_options()
		], 200 );
	}

	function rest_settings_update( $request ) {
		try {
			$params = $request->get_json_params();
			$value = $params['options'];
			$options = $this->core->update_options( $value );
			$success = !!$options;
			$message = __( $success ? 'OK' : "Could not update options.", 'ai-engine' );
			return new WP_REST_Response([ 'success' => $success, 'message' => $message, 'options' => $options ], 200 );
		}
		catch ( Exception $e ) {
			$message = apply_filters( 'mwai_ai_exception', $e->getMessage() );
			return new WP_REST_Response([ 'success' => false, 'message' => $message ], 500 );
		}
	}

	function rest_settings_reset() {
		try {
			$options = $this->core->reset_options();
			$success = !!$options;
			$message = __( $success ? 'OK' : "Could not reset options.", 'ai-engine' );
			return new WP_REST_Response([ 'success' => $success, 'message' => $message, 'options' => $options ], 200 );
		}
		catch ( Exception $e ) {
			$message = apply_filters( 'mwai_ai_exception', $e->getMessage() );
			return new WP_REST_Response([ 'success' => false, 'message' => $message ], 500 );
		}
	}

	function rest_ai_models( $request ) {
		try {
			$params = $request->get_json_params();
			$envId = $params['envId'];
			$engine = Meow_MWAI_Engines_Factory::get( $this->core, $envId );
			$models = $engine->retrieve_models();
			return new WP_REST_Response([ 'success' => true, 'models' => $models ], 200 );
		}
		catch ( Exception $e ) {
			$message = apply_filters( 'mwai_ai_exception', $e->getMessage() );
			return new WP_REST_Response([ 'success' => false, 'message' => $message ], 500 );
		}
	}

	function rest_ai_completions( $request ) {
		try {
			$params = $request->get_json_params();
			$message = $this->retrieve_message( $params );
			$query = new Meow_MWAI_Query_Text( $message );
			$query->inject_params( $params );

			// Handle streaming
			$stream = $params['stream'] ?? false;
			$streamCallback = null;
			if ( $stream ) { 
				$streamCallback = function( $reply ) {
					//$raw = _wp_specialchars( $reply, ENT_NOQUOTES, 'UTF-8', true );
					$raw = $reply;
					$this->core->stream_push( [ 'type' => 'live', 'data' => $raw ] );
					if ( ob_get_level() > 0 ) {
						ob_flush();
					}
					flush();
				};
				header( 'Cache-Control: no-cache' );
				header( 'Content-Type: text/event-stream' );
				header( 'X-Accel-Buffering: no' ); // This is useful to disable buffering in nginx through headers.
				ob_implicit_flush( true );
				ob_end_flush();
			}

			// Process Reply
			$reply = $this->core->run_query( $query, $streamCallback );
			$restRes = [
				'success' => true,
				'data' => $reply->result,
				'usage' => $reply->usage
			];
			if ( $stream ) {
				$this->core->stream_push( [ 'type' => 'end', 'data' => json_encode( $restRes ) ] );
				die();
			}
			return new WP_REST_Response( $restRes, 200 );
		}
		catch ( Exception $e ) {
			$message = apply_filters( 'mwai_ai_exception', $e->getMessage() );
			if ( $stream ) { 
				$this->core->stream_push( [ 'type' => 'error', 'data' => $message ] );
			}
			else {
				return new WP_REST_Response([ 'success' => false, 'message' => $message ], 500 );
			}
		}
	}

	function rest_ai_images( $request ) {
		try {
			$params = $request->get_json_params();
			$message = $this->retrieve_message( $params );
			$query = new Meow_MWAI_Query_Image( $message );
			$query->inject_params( $params );
			$reply = $this->core->run_query( $query );
			return new WP_REST_Response([ 'success' => true, 'data' => $reply->results, 'usage' => $reply->usage ], 200 );
		}
		catch ( Exception $e ) {
			$message = apply_filters( 'mwai_ai_exception', $e->getMessage() );
			return new WP_REST_Response([ 'success' => false, 'message' => $message ], 500 );
		}
	}

	function rest_ai_magic_wand( $request ) {
		try {
			$params = $request->get_json_params();
			$action = isset( $params['action'] ) ? $params['action'] : null;
			$data = isset( $params['data'] ) ? $params['data'] : null;
			if ( empty( $data ) || empty( $action ) ) {
				return new WP_REST_Response([ 'success' => false, 'message' => "An action and some data are required." ], 500 );
			}
			$data = apply_filters( 'mwai_magic_wand_' . $action, "", $data );
			return new WP_REST_Response([  'success' => true, 'data' => $data ], 200 );
		}
		catch ( Exception $e ) {
			$message = apply_filters( 'mwai_ai_exception', $e->getMessage() );
			return new WP_REST_Response([ 'success' => false, 'message' => $message ], 500 );
		}
	}

	function rest_ai_copilot( $request ) {
		try {
			$params = $request->get_json_params();
			$action = sanitize_text_field( $params['action'] );
			$message = $this->retrieve_message( $params, true );
			if ( empty( $action ) || empty( $message ) ) {
				return new WP_REST_Response([ 'success' => false, 'message' => "Copilot needs an action and a prompt." ], 500 );
			}
			$query = new Meow_MWAI_Query_Text( $message, 2048 );
			$query->set_scope( 'admin-tools' );
			$model = $this->core->get_option( 'ai_default_model' );
			$env = $this->core->get_option( 'ai_default_env' );
			if ( !empty( $model ) ) {
				$query->set_model( $model );
			}
			if ( !empty( $env ) ) {
				$query->set_env_id( $env );
			}
			$reply = $this->core->run_query( $query );
			return new WP_REST_Response([ 'success' => true, 'data' => $reply->result ], 200 );
		}
		catch ( Exception $e ) {
			$message = apply_filters( 'mwai_ai_exception', $e->getMessage() );
			return new WP_REST_Response([ 'success' => false, 'message' => $message ], 500 );
		}
	}

	function rest_helpers_update_title( $request ) {
		try {
			$params = $request->get_json_params();
			$title = sanitize_text_field( $params['title'] );
			$postId = intval( $params['postId'] );
			$post = get_post( $postId );
			if ( !$post ) {
				throw new Exception( 'There is no post with this ID.' );
			}
			$post->post_title = $title;
			//$post->post_name = sanitize_title( $title );
			wp_update_post( $post );
			return new WP_REST_Response([ 'success' => true, 'message' => "Title updated." ], 200 );
		}
		catch ( Exception $e ) {
			$message = apply_filters( 'mwai_ai_exception', $e->getMessage() );
			return new WP_REST_Response([ 'success' => false, 'message' => $message ], 500 );
		}
	}

	function rest_helpers_update_excerpt( $request ) {
		try {
			$params = $request->get_json_params();
			$excerpt = sanitize_text_field( $params['excerpt'] );
			$postId = intval( $params['postId'] );
			$post = get_post( $postId );
			if ( !$post ) {
				throw new Exception( 'There is no post with this ID.' );
			}
			$post->post_excerpt = $excerpt;
			wp_update_post( $post );
			return new WP_REST_Response([ 'success' => true, 'message' => "Excerpt updated." ], 200 );
		}
		catch ( Exception $e ) {
			$message = apply_filters( 'mwai_ai_exception', $e->getMessage() );
			return new WP_REST_Response([ 'success' => false, 'message' => $message ], 500 );
		}
	}

	function rest_helpers_create_post( $request ) {
		try {
			$params = $request->get_json_params();
			$title = sanitize_text_field( $params['title'] );
			$content = sanitize_textarea_field( $params['content'] );
			$excerpt = sanitize_text_field( $params['excerpt'] );
			$postType = sanitize_text_field( $params['postType'] );
			$post = new stdClass();
			$post->post_title = $title;
			$post->post_excerpt = $excerpt;
			$post->post_content = $content;
			$post->post_status = 'draft';
			$post->post_type = isset( $postType ) ? $postType : 'post';
			// TODO: Let's try to avoid using Markdown to create the Post
			// Instead, we should create Gutenberg Blocks, or simple HTML.
			// Then, we can get rid of the library for Markdown.
			$post->post_content = $this->core->markdown_to_html( $post->post_content );
			$postId = wp_insert_post( $post );
			return new WP_REST_Response([ 'success' => true, 'postId' => $postId ], 200 );
		}
		catch ( Exception $e ) {
			$message = apply_filters( 'mwai_ai_exception', $e->getMessage() );
			return new WP_REST_Response([ 'success' => false, 'message' => $message ], 500 );
		}
	}

	function rest_helpers_create_images( $request ) {
		try {
			$params = $request->get_json_params();
			$title = sanitize_text_field( $params['title'] );
			$caption = sanitize_text_field( $params['caption'] );
			$alt = sanitize_text_field( $params['alt'] );
			$description = sanitize_text_field( $params['description'] );
			$url = $params['url'];
			$filename = sanitize_text_field( $params['filename'] );
			$attachmentId = $this->core->add_image_from_url( $url, $filename, $title, $description, $caption, $alt );
			return new WP_REST_Response([ 'success' => true, 'attachmentId' => $attachmentId ], 200 );
		}
		catch ( Exception $e ) {
			$message = apply_filters( 'mwai_ai_exception', $e->getMessage() );
			return new WP_REST_Response([ 'success' => false, 'message' => $message ], 500 );
		}
	}

	function rest_openai_files_get() {
		try {
			$envId = isset( $_GET['envId'] ) ? $_GET['envId'] : null;
			$purposeFilter = isset( $_GET['purpose'] ) ? $_GET['purpose'] : null;
			$openai = Meow_MWAI_Engines_Factory::get_openai( $this->core, $envId );
			$files = $openai->list_files( $purposeFilter );
			return new WP_REST_Response([ 'success' => true, 'files' => $files ], 200 );
		}
		catch ( Exception $e ) {
			$message = apply_filters( 'mwai_ai_exception', $e->getMessage() );
			return new WP_REST_Response([ 'success' => false, 'message' => $message ], 500 );
		}
	}

	function rest_openai_deleted_finetunes_get() {
		try {
			$envId = isset( $_GET['envId'] ) ? $_GET['envId'] : null;
			$legacy = isset( $_GET['legacy'] ) ? $_GET['legacy'] === 'true' : false;
			$openai = Meow_MWAI_Engines_Factory::get_openai( $this->core, $envId );
			$finetunes = $openai->list_deleted_finetunes( $legacy );
			return new WP_REST_Response([ 'success' => true, 'finetunes' => $finetunes ], 200 );
		}
		catch ( Exception $e ) {
			$message = apply_filters( 'mwai_ai_exception', $e->getMessage() );
			return new WP_REST_Response([ 'success' => false, 'message' => $message ], 500 );
		}
	}

	function rest_openai_finetunes_get() {
		try {
			$envId = isset( $_GET['envId'] ) ? $_GET['envId'] : null;
			$legacy = isset( $_GET['legacy'] ) ? $_GET['legacy'] === 'true' : false;
			$openai = Meow_MWAI_Engines_Factory::get_openai( $this->core, $envId );
			$finetunes = $openai->list_finetunes( $legacy );
			return new WP_REST_Response([ 'success' => true, 'finetunes' => $finetunes ], 200 );
		}
		catch ( Exception $e ) {
			$message = apply_filters( 'mwai_ai_exception', $e->getMessage() );
			return new WP_REST_Response([ 'success' => false, 'message' => $message ], 500 );
		}
	}

	function rest_openai_files_upload( $request ) {
		try {
			$params = $request->get_json_params();
			$envId = $params['envId'];;
			$filename = sanitize_text_field( $params['filename'] );
			$data = $params['data'];
			$openai = Meow_MWAI_Engines_Factory::get_openai( $this->core, $envId );
			$file = $openai->upload_file( $filename, $data );
			return new WP_REST_Response([ 'success' => true, 'file' => $file ], 200 );
		}
		catch ( Exception $e ) {
			$message = apply_filters( 'mwai_ai_exception', $e->getMessage() );
			return new WP_REST_Response([ 'success' => false, 'message' => $message ], 500 );
		}
	}

	function rest_openai_files_delete( $request ) {
		try {
			$params = $request->get_json_params();
			$envId = $params['envId'];;
			$fileId = $params['fileId'];
			$openai = Meow_MWAI_Engines_Factory::get_openai( $this->core, $envId );
			$openai->delete_file( $fileId );
			return new WP_REST_Response([ 'success' => true ], 200 );
		}
		catch ( Exception $e ) {
			$message = apply_filters( 'mwai_ai_exception', $e->getMessage() );
			return new WP_REST_Response([ 'success' => false, 'message' => $message ], 500 );
		}
	}

	function rest_openai_finetunes_cancel( $request ) {
		try {
			$params = $request->get_json_params();
			$envId = $params['envId'];;
			$finetuneId = $params['finetuneId'];
			$openai = Meow_MWAI_Engines_Factory::get_openai( $this->core, $envId );
			$openai->cancel_finetune( $finetuneId );
			return new WP_REST_Response([ 'success' => true ], 200 );
		}
		catch ( Exception $e ) {
			$message = apply_filters( 'mwai_ai_exception', $e->getMessage() );
			return new WP_REST_Response([ 'success' => false, 'message' => $message ], 500 );
		}
	}

	function rest_openai_finetunes_delete( $request ) {
		try {
			$params = $request->get_json_params();
			$envId = $params['envId'];;
			$modelId = $params['modelId'];
			$openai = Meow_MWAI_Engines_Factory::get_openai( $this->core, $envId );
			$openai->delete_finetune( $modelId );
			return new WP_REST_Response([ 'success' => true ], 200 );
		}
		catch ( Exception $e ) {
			$message = apply_filters( 'mwai_ai_exception', $e->getMessage() );
			return new WP_REST_Response([ 'success' => false, 'message' => $message ], 500 );
		}
	}

	function rest_openai_files_download( $request ) {
		try {
			$params = $request->get_json_params();
			$envId = $params['envId'];;
			$fileId = $params['fileId'];
			$openai = Meow_MWAI_Engines_Factory::get_openai( $this->core, $envId );
			$filename = $openai->download_file( $fileId );
			$data = file_get_contents( $filename );
			return new WP_REST_Response([ 'success' => true, 'data' => $data ], 200 );
		}
		catch ( Exception $e ) {
			$message = apply_filters( 'mwai_ai_exception', $e->getMessage() );
			return new WP_REST_Response([ 'success' => false, 'message' => $message ], 500 );
		}
	}

	function rest_openai_files_finetune( $request ) {
		try {
			$params = $request->get_json_params();
			$envId = $params['envId'];;
			$fileId = $params['fileId'];
			$model = $params['model'];
			$suffix = $params['suffix'];
			$hyperparams = [
				"nEpochs" => isset( $params['nEpochs'] ) ? $params['nEpochs'] : null,
				"batchSize" => isset( $params['batchSize'] ) ? $params['batchSize'] : null,
			];
			$openai = Meow_MWAI_Engines_Factory::get_openai( $this->core, $envId );
			$finetune = $openai->run_finetune( $fileId, $model, $suffix, $hyperparams );
			return new WP_REST_Response([ 'success' => true, 'finetune' => $finetune ], 200 );
		}
		catch ( Exception $e ) {
			$message = apply_filters( 'mwai_ai_exception', $e->getMessage() );
			return new WP_REST_Response([ 'success' => false, 'message' => $message ], 500 );
		}
	}

	function rest_openai_incidents() {
		try {
			$transient = get_transient( 'mwai_openai_incidents' );
			if ( $transient ) {
				return new WP_REST_Response([ 'success' => true, 'incidents' => $transient ], 200 );
			}
			$openai = Meow_MWAI_Engines_Factory::get_openai( $this->core );
			$incidents = $openai->get_incidents();
			set_transient( 'mwai_openai_incidents', $incidents, 60 * 10 );
			return new WP_REST_Response([ 'success' => true, 'incidents' => $incidents ], 200 );
		}
		catch ( Exception $e ) {
			$message = apply_filters( 'mwai_ai_exception', $e->getMessage() );
			return new WP_REST_Response([ 'success' => false, 'message' => $message ], 500 );
		}
	}

	function rest_helpers_count_posts( $request ) {
		try {
			$params = $request->get_query_params();
			$postType = $params['postType'];
			$postStatus = !empty( $params['postStatus'] ) ? explode( ',', $params['postStatus'] ) : [ 'publish' ];
			$count = wp_count_posts( $postType );
			$count = array_sum( array_intersect_key( (array)$count, array_flip( $postStatus ) ) );
			return new WP_REST_Response([ 'success' => true, 'count' => $count ], 200 );
		}
		catch ( Exception $e ) {
			$message = apply_filters( 'mwai_ai_exception', $e->getMessage() );
			return new WP_REST_Response([ 'success' => false, 'message' => $message ], 500 );
		}
	}

	function rest_helpers_posts_ids( $request ) {
		try {
			$params = $request->get_query_params();
			$postType = $params['postType'];
			$postStatus = !empty( $params['postStatus'] ) ? explode( ',', $params['postStatus'] ) : [ 'publish' ];
			$posts = get_posts( [
				'posts_per_page' => -1,
				'post_type' => $postType,
				'post_status' => $postStatus,
				'fields' => 'ids'
			] );
			return new WP_REST_Response([ 'success' => true, 'postIds' => $posts ], 200 );
		}
		catch ( Exception $e ) {
			$message = apply_filters( 'mwai_ai_exception', $e->getMessage() );
			return new WP_REST_Response([ 'success' => false, 'message' => $message ], 500 );
		}
	}

	function rest_helpers_post_content( $request ) {
		try {
			$params = $request->get_query_params();
			$offset = (int)$params['offset'];
			$postType = $params['postType'];
			$postStatus = isset( $params['postStatus'] ) ? explode( ',', $params['postStatus'] ) : [ 'publish' ];
			$postId = (int)$params['postId'];

			$post = null;
			if ( !empty( $postId ) ) {
				$post = get_post( $postId );
				if ( $post->post_status !== 'publish' && $post->post_status !== 'future'
					&& $post->post_status !== 'draft' && $post->post_status !== 'private' ) {
					$post = null;
				}
			}
			else {
				$posts = get_posts( [
					'posts_per_page' => 1,
					'post_type' => $postType,
					'offset' => $offset,
					'post_status' => $postStatus,
				] );
				$post = count( $posts ) === 0 ? null : $posts[0];
			}
			if ( !$post ) {
				return new WP_REST_Response([ 'success' => false, 'message' => 'Post not found' ], 404 );
			}
			$cleanPost = $this->core->get_post( $post );
			return new WP_REST_Response([ 'success' => true, 'content' => $cleanPost['content'],
				'checksum' => $cleanPost['checksum'], 'language' => $cleanPost['language'], 'excerpt' => $cleanPost['excerpt'],
				'postId' => $cleanPost['postId'], 'title' => $cleanPost['title'], 'url' => $cleanPost['url'] ], 200 );
		}
		catch ( Exception $e ) {
			$message = apply_filters( 'mwai_ai_exception', $e->getMessage() );
			return new WP_REST_Response([ 'success' => false, 'message' => $message ], 500 );
		}
	}

	function rest_system_templates_get( $request ) {
		try {
			$params = $request->get_query_params();
			$category = $params['category'];
			$templates = [];
			$templates_option = get_option( 'mwai_templates', [] );
			if ( !is_array( $templates_option ) ) {
				update_option( 'mwai_templates', [] );
			}
			$categories = array_column( $templates_option, 'category' );
			$index = array_search( $category, $categories );
			$templates = [];
			if ( $index !== false ) {
				$templates = $templates_option[$index]['templates'];
			}
			return new WP_REST_Response([ 'success' => true, 'templates' => $templates ], 200 );
		}
		catch ( Exception $e ) {
			$message = apply_filters( 'mwai_ai_exception', $e->getMessage() );
			return new WP_REST_Response([ 'success' => false, 'message' => $message ], 500 );
		}
	}

	function rest_system_templates_save( $request ) {
		try {
			$params = $request->get_json_params();
			$category = $params['category'];
			$templates = $params['templates'];
			$templates_option = get_option( 'mwai_templates', [] );
			$categories = array_column( $templates_option, 'category' );
			$index = array_search( $category, $categories );
			if ( $index !== false && $index >= 0 ) {
				$templates_option[$index]['templates'] = $templates;
			}
			else {
				$group = [ 'category' => $category, 'templates' => $templates ];
				$templates_option[] = $group;
			}

			update_option( 'mwai_templates', $templates_option );
			return new WP_REST_Response([ 'success' => true ], 200 );
		}
		catch ( Exception $e ) {
			$message = apply_filters( 'mwai_ai_exception', $e->getMessage() );
			return new WP_REST_Response([ 'success' => false, 'message' => $message ], 500 );
		}
	}

	function rest_system_logs_list( $request ) {
		try {
			$params = $request->get_json_params();
			$offset = $params['offset'];
			$limit = $params['limit'];
			$filters = $params['filters'];
			$sort = $params['sort'];
			$logs = apply_filters( 'mwai_stats_logs', [], $offset, $limit, $filters, $sort );
			return new WP_REST_Response([ 'success' => true, 'total' => $logs['total'], 'logs' => $logs['rows'] ], 200 );
		}
		catch ( Exception $e ) {
			$message = apply_filters( 'mwai_ai_exception', $e->getMessage() );
			return new WP_REST_Response([ 'success' => false, 'message' => $message ], 500 );
		}
	}

	function rest_system_logs_delete( $request ) {
		try {
			$params = $request->get_json_params();
			$logIds = $params['logIds'];
			$success = apply_filters( 'mwai_stats_logs_delete', true, $logIds );
			return new WP_REST_Response([ 'success' => $success ], 200 );
		}
		catch ( Exception $e ) {
			$message = apply_filters( 'mwai_ai_exception', $e->getMessage() );
			return new WP_REST_Response([ 'success' => false, 'message' => $message ], 500 );
		}
	}

	function rest_system_logs_meta_get( $request ) {
		try {
			$params = $request->get_json_params();
			$logId = $params['logId'];
			$metaKeys = $params['metaKeys'];
			$data = apply_filters( 'mwai_stats_logs_meta', [], $logId, $metaKeys );
			return new WP_REST_Response([ 'success' => true, 'data' => $data ], 200 );
		}
		catch ( Exception $e ) {
			$message = apply_filters( 'mwai_ai_exception', $e->getMessage() );
			return new WP_REST_Response([ 'success' => false, 'message' => $message ], 500 );
		}
	}

	function rest_ai_moderate( $request ) {
		try {
			$params = $request->get_json_params();
			$envId = $params['envId'];
			$text = $params['text'];
			if ( !$text ) {
				return new WP_REST_Response([ 'success' => false, 'message' => 'Text not found.' ], 404 );
			}
			$openai = Meow_MWAI_Engines_Factory::get_openai( $this->core, $envId );
			$results = $openai->moderate( $text );
			return new WP_REST_Response([ 'success' => true, 'results' => $results ], 200 );
		}
		catch ( Exception $e ) {
			$message = apply_filters( 'mwai_ai_exception', $e->getMessage() );
			return new WP_REST_Response([ 'success' => false, 'message' => $message ], 500 );
		}
	}
	
	function rest_ai_transcribe_audio( $request ) {
		try {
			$params = $request->get_json_params();
			$query = new Meow_MWAI_Query_Transcribe();
			$query->inject_params( $params );
			$query->set_scope('admin-tools');
			$reply = $this->core->run_query( $query );
			return new WP_REST_Response([ 'success' => true, 'data' => $reply->result ], 200 );
		}
		catch ( Exception $e ) {
			$message = apply_filters( 'mwai_ai_exception', $e->getMessage() );
			return new WP_REST_Response([ 'success' => false, 'message' => $message ], 500 );
		}
	}

	function rest_ai_transcribe_image( $request ) {
		try {
			global $mwai;
			$params = $request->get_json_params();
			$message = $this->retrieve_message( $params );
			$url = !empty( $params['url'] ) ? $params['url'] : null;
			$path = !empty( $params['path'] ) ? $params['path'] : null;
			$result = $mwai->simpleVisionQuery( $message, $url, $path );
			return new WP_REST_Response([ 'success' => true, 'data' => $result ], 200 );
		}
		catch ( Exception $e ) {
			$message = apply_filters( 'mwai_ai_exception', $e->getMessage() ); 
			return new WP_REST_Response([ 'success' => false, 'message' => $message ], 500 );
		}
	}

	function rest_ai_json( $request ) {
		try {
			global $mwai;
			$params = $request->get_json_params();
			$message = $this->retrieve_message( $params );
			$result = $mwai->simpleJsonQuery( $message );
			return new WP_REST_Response([ 'success' => true, 'data' => $result ], 200 );
		}
		catch ( Exception $e ) {
			$message = apply_filters( 'mwai_ai_exception', $e->getMessage() ); 
			return new WP_REST_Response([ 'success' => false, 'message' => $message ], 500 );
		}
	}

	function rest_helpers_post_types() {
		try {
			$postTypes = $this->core->get_post_types();
			return new WP_REST_Response([ 'success' => true, 'postTypes' => $postTypes ], 200 );
		}
		catch ( Exception $e ) {
			$message = apply_filters( 'mwai_ai_exception', $e->getMessage() );
			return new WP_REST_Response([ 'success' => false, 'message' => $message ], 500 );
		}
	}

	function rest_settings_themes( $request ) {
		try {
			$method = $request->get_method();
			if ( $method === 'GET' ) {
				$themes = $this->core->get_themes();
				return new WP_REST_Response([ 'success' => true, 'themes' => $themes ], 200 );
			}
			else if ( $method === 'POST' ) {
				$params = $request->get_json_params();
				$themes = $params['themes'];
				$themes = $this->core->update_themes( $themes );
				return new WP_REST_Response([ 'success' => true, 'themes' => $themes ], 200 );
			}
		}
		catch ( Exception $e ) {
			$message = apply_filters( 'mwai_ai_exception', $e->getMessage() );
			return new WP_REST_Response([ 'success' => false, 'message' => $message ], 500 );
		}
	}

	function rest_settings_chatbots( $request ) {
		try {
			$method = $request->get_method();
			if ( $method === 'GET' ) {
				$chatbots = $this->core->get_chatbots();
				return new WP_REST_Response([ 'success' => true, 'chatbots' => $chatbots ], 200 );
			}
			else if ( $method === 'POST' ) {
				$params = $request->get_json_params();
				$chatbots = $params['chatbots'];
				$chatbots = $this->core->update_chatbots( $chatbots );
				return new WP_REST_Response([ 'success' => true, 'chatbots' => $chatbots ], 200 );
			}
			return new WP_REST_Response([ 'success' => false, 'message' => 'Method not allowed' ], 405 );
		}
		catch ( Exception $e ) {
			$message = apply_filters( 'mwai_ai_exception', $e->getMessage() );
			return new WP_REST_Response([ 'success' => false, 'message' => $message ], 500 );
		}
	}
}
