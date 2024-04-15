<?php
/**
 * Framework fields.class file.
 *
 * @link       https://shapedplugin.com/
 * @since      2.0.0
 *
 * @package    easy-accordion-free
 * @subpackage easy-accordion-free/framework
 */

if ( ! defined( 'ABSPATH' ) ) {
	die; } // Cannot access directly.

if ( ! class_exists( 'SP_EAP_Fields' ) ) {
	/**
	 *
	 * Fields Class
	 *
	 * @since 1.0.0
	 * @version 1.0.0
	 */
	abstract class SP_EAP_Fields extends SP_EAP_Abstract {

		/**
		 * Field
		 *
		 * @var array
		 */
		public $field = array();

		/**
		 * Value
		 *
		 * @var string
		 */
		public $value = '';

		/**
		 * Unique
		 *
		 * @var string
		 */
		public $unique = '';

		/**
		 * Where
		 *
		 * @var string
		 */
		public $where = '';

		/**
		 * Parent
		 *
		 * @var string
		 */
		public $parent = '';

		/**
		 * Constructor of the class.
		 *
		 * @param array  $field field.
		 * @param string $value value.
		 * @param string $unique unique.
		 * @param string $where where.
		 * @param string $parent parent.
		 */
		public function __construct( $field = array(), $value = '', $unique = '', $where = '', $parent = '' ) {
			$this->field  = $field;
			$this->value  = $value;
			$this->unique = $unique;
			$this->where  = $where;
			$this->parent = $parent;
		}

		/**
		 * Field name.
		 *
		 * @param array $nested_name Field name.
		 */
		public function field_name( $nested_name = '' ) {

			$field_id   = ( ! empty( $this->field['id'] ) ) ? $this->field['id'] : '';
			$unique_id  = ( ! empty( $this->unique ) ) ? $this->unique . '[' . $field_id . ']' : $field_id;
			$field_name = ( ! empty( $this->field['name'] ) ) ? $this->field['name'] : $unique_id;
			$tag_prefix = ( ! empty( $this->field['tag_prefix'] ) ) ? $this->field['tag_prefix'] : '';

			if ( ! empty( $tag_prefix ) ) {
				$nested_name = str_replace( '[', '[' . $tag_prefix, $nested_name );
			}

			return $field_name . $nested_name;

		}

		/**
		 * Field attributes.
		 *
		 * @param array $custom_atts Field attributes.
		 */
		public function field_attributes( $custom_atts = array() ) {

			$field_id   = ( ! empty( $this->field['id'] ) ) ? $this->field['id'] : '';
			$attributes = ( ! empty( $this->field['attributes'] ) ) ? $this->field['attributes'] : array();

			if ( ! empty( $field_id ) && empty( $attributes['data-depend-id'] ) ) {
				$attributes['data-depend-id'] = $field_id;
			}

			if ( ! empty( $this->field['placeholder'] ) ) {
				$attributes['placeholder'] = $this->field['placeholder'];
			}

			$attributes = wp_parse_args( $attributes, $custom_atts );

			$atts = '';

			if ( ! empty( $attributes ) ) {
				foreach ( $attributes as $key => $value ) {
					if ( 'only-key' === $value ) {
						$atts .= ' ' . esc_attr( $key );
					} else {
						$atts .= ' ' . esc_attr( $key ) . '="' . esc_attr( $value ) . '"';
					}
				}
			}

			return $atts;

		}

		/**
		 * Field before.
		 */
		public function field_before() {
			return ( ! empty( $this->field['before'] ) ) ? wp_kses_post( $this->field['before'] ) : '';
		}

		/**
		 * Field after.
		 */
		public function field_after() {

			$output  = ( ! empty( $this->field['after'] ) ) ? wp_kses_post( $this->field['after'] ) : '';
			$output .= ( ! empty( $this->field['desc'] ) ) ? '<div class="clear"></div><div class="eapro-text-desc">' . wp_kses_post( $this->field['desc'] ) . '</div>' : '';
			$output .= ( ! empty( $this->field['help'] ) ) ? '<div class="eapro-help"><span class="eapro-help-text">' . wp_kses_post( $this->field['help'] ) . '</span><i class="fa fa-question-circle"></i></div>' : '';
			$output .= ( ! empty( $this->field['_error'] ) ) ? '<div class="eapro-text-error">' . wp_kses_post( $this->field['_error'] ) . '</div>' : '';

			return $output;

		}

		/**
		 * Field data.
		 *
		 * @param string  $type Field type.
		 * @param boolean $term Field term.
		 * @param array   $query_args Field query_args.
		 */
		public static function field_data( $type = '', $term = false, $query_args = array() ) {

			$options      = array();
			$array_search = false;

			// sanitize type name.
			if ( in_array( $type, array( 'page', 'pages' ), true ) ) {
				$option = 'page';
			} elseif ( in_array( $type, array( 'post', 'posts' ), true ) ) {
				$option = 'post';
			} elseif ( in_array( $type, array( 'category', 'categories' ), true ) ) {
				$option = 'category';
			} elseif ( in_array( $type, array( 'tag', 'tags' ), true ) ) {
				$option = 'post_tag';
			} elseif ( in_array( $type, array( 'menu', 'menus' ), true ) ) {
				$option = 'nav_menu';
			} else {
				$option = '';
			}

			// switch type.
			switch ( $type ) {

				case 'page':
				case 'pages':
				case 'post':
				case 'posts':
					// term query required for ajax select.
					if ( ! empty( $term ) ) {

						$query = new WP_Query(
							wp_parse_args(
								$query_args,
								array(
									's'              => $term,
									'post_type'      => $option,
									'post_status'    => 'publish',
									'posts_per_page' => 25,
								)
							)
						);

					} else {

						$query = new WP_Query(
							wp_parse_args(
								$query_args,
								array(
									'post_type'   => $option,
									'post_status' => 'publish',
								)
							)
						);

					}

					if ( ! is_wp_error( $query ) && ! empty( $query->posts ) ) {
						foreach ( $query->posts as $item ) {
							$options[ $item->ID ] = $item->post_title;
						}
					}

					break;

				case 'sp_easy_accordion':
					$eap_get_specific = array(
						'post_type' => 'sp_easy_accordion',
					);
					$query_args       = array_merge( $query_args, $eap_get_specific );
					$all_posts        = get_posts( $query_args );

					if ( ! is_wp_error( $all_posts ) && ! empty( $all_posts ) ) {
						foreach ( $all_posts as $post_obj ) {
							$options[ $post_obj->ID ] = isset( $post_obj->post_title ) && ! empty( $post_obj->post_title ) ? $post_obj->post_title : 'Untitled';
						}
					}
					wp_reset_postdata();
					break;

				case 'terms':
				case 'term':
					global $post;
					$saved_meta = get_post_meta( $post->ID, 'sp_eap_upload_options', true );
					if ( isset( $saved_meta['eap_post_taxonomy'] ) && '' !== $saved_meta['eap_post_taxonomy'] ) {
						$terms = get_terms( $saved_meta['eap_post_taxonomy'] );
						foreach ( $terms as $key => $value ) {
							$options[ $value->term_id ] = $value->name;
						}
					} else {
						$post_types       = get_post_types( array( 'public' => true ) );
						$post_type_list   = array();
						$post_type_number = 1;
						foreach ( $post_types as $post_type => $label ) {
							$post_type_list[ $post_type_number++ ] = $label;
						}
						$taxonomy_names  = get_object_taxonomies( $post_type_list['1'], 'names' );
						$taxonomy_number = 1;
						foreach ( $taxonomy_names as $taxonomy => $label ) {
							$taxonomy_terms[ $taxonomy_number++ ] = $label;
						}
						$terms = get_terms( $taxonomy_terms['1'] );
						foreach ( $terms as $key => $value ) {
							$options[ $value->term_id ] = $value->name;
						}
					}

					break;

				case 'category':
				case 'categories':
				case 'tag':
				case 'tags':
				case 'menu':
				case 'menus':
					if ( ! empty( $term ) ) {

						$query = new WP_Term_Query(
							wp_parse_args(
								$query_args,
								array(
									'search'     => $term,
									'taxonomy'   => $option,
									'hide_empty' => false,
									'number'     => 25,
								)
							)
						);

					} else {

						$query = new WP_Term_Query(
							wp_parse_args(
								$query_args,
								array(
									'taxonomy'   => $option,
									'hide_empty' => false,
								)
							)
						);

					}

					if ( ! is_wp_error( $query ) && ! empty( $query->terms ) ) {
						foreach ( $query->terms as $item ) {
							$options[ $item->term_id ] = $item->name;
						}
					}

					break;

				case 'user':
				case 'users':
					if ( ! empty( $term ) ) {

						$query = new WP_User_Query(
							array(
								'search'  => '*' . $term . '*',
								'number'  => 25,
								'orderby' => 'title',
								'order'   => 'ASC',
								'fields'  => array( 'display_name', 'ID' ),
							)
						);

					} else {

						$query = new WP_User_Query( array( 'fields' => array( 'display_name', 'ID' ) ) );

					}

					if ( ! is_wp_error( $query ) && ! empty( $query->get_results() ) ) {
						foreach ( $query->get_results() as $item ) {
							$options[ $item->ID ] = $item->display_name;
						}
					}

					break;

				case 'sidebar':
				case 'sidebars':
					global $wp_registered_sidebars;

					if ( ! empty( $wp_registered_sidebars ) ) {
						foreach ( $wp_registered_sidebars as $sidebar ) {
							$options[ $sidebar['id'] ] = $sidebar['name'];
						}
					}

					$array_search = true;

					break;

				case 'role':
				case 'roles':
					global $wp_roles;

					if ( ! empty( $wp_roles ) ) {
						if ( ! empty( $wp_roles->roles ) ) {
							foreach ( $wp_roles->roles as $role_key => $role_value ) {
								$options[ $role_key ] = $role_value['name'];
							}
						}
					}

					$array_search = true;

					break;

				case 'post_type':
				case 'post_types':
					$post_types = get_post_types( array( 'show_in_nav_menus' => true ), 'objects' );

					if ( ! is_wp_error( $post_types ) && ! empty( $post_types ) ) {
						foreach ( $post_types as $post_type ) {
							$options[ $post_type->name ] = $post_type->labels->name;
						}
					}

					$array_search = true;

					break;

				default:
					if ( function_exists( $type ) ) {
						if ( ! empty( $term ) ) {
							$options = call_user_func( $type, $query_args );
						} else {
							$options = call_user_func( $type, $term, $query_args );
						}
					}

					break;

			}

			// Array search by "term".
			if ( ! empty( $term ) && ! empty( $options ) && ! empty( $array_search ) ) {
				$options = preg_grep( '/' . $term . '/i', $options );
			}

			// Make multidimensional array for ajax search.
			if ( ! empty( $term ) && ! empty( $options ) ) {
				$arr = array();
				foreach ( $options as $option_key => $option_value ) {
					$arr[] = array(
						'value' => $option_key,
						'text'  => $option_value,
					);
				}
				$options = $arr;
			}

			return $options;

		}

		/**
		 * Field wp query data title.
		 *
		 * @param string $type Post type.
		 * @param string $values Post values.
		 */
		public function field_wp_query_data_title( $type, $values ) {

			$options = array();

			if ( ! empty( $values ) && is_array( $values ) ) {

				foreach ( $values as $value ) {

					switch ( $type ) {

						case 'post':
						case 'posts':
						case 'page':
						case 'pages':
							$title = get_the_title( $value );

							if ( ! is_wp_error( $title ) && ! empty( $title ) ) {
								$options[ $value ] = $title;
							}

							break;

						case 'category':
						case 'categories':
						case 'tag':
						case 'tags':
						case 'menu':
						case 'menus':
							$term = get_term( $value );

							if ( ! is_wp_error( $term ) && ! empty( $term ) ) {
								$options[ $value ] = $term->name;
							}

							break;

						case 'user':
						case 'users':
							$user = get_user_by( 'id', $value );

							if ( ! is_wp_error( $user ) && ! empty( $user ) ) {
								$options[ $value ] = $user->display_name;
							}

							break;

						case 'sidebar':
						case 'sidebars':
							global $wp_registered_sidebars;

							if ( ! empty( $wp_registered_sidebars[ $value ] ) ) {
									$options[ $value ] = $wp_registered_sidebars[ $value ]['name'];
							}

							break;

						case 'role':
						case 'roles':
							global $wp_roles;

							if ( ! empty( $wp_roles ) && ! empty( $wp_roles->roles ) && ! empty( $wp_roles->roles[ $value ] ) ) {
								$options[ $value ] = $wp_roles->roles[ $value ]['name'];
							}

							break;

						case 'post_type':
						case 'post_types':
							$post_types = get_post_types( array( 'show_in_nav_menus' => true ) );

							if ( ! is_wp_error( $post_types ) && ! empty( $post_types ) && ! empty( $post_types[ $value ] ) ) {
								$options[ $value ] = ucfirst( $value );
							}

							break;

						default:
							if ( function_exists( $type . '_title' ) ) {
								$options[ $value ] = call_user_func( $type . '_title', $value );
							} else {
								$options[ $value ] = ucfirst( $value );
							}

							break;

					}
				}
			}

			return $options;

		}

	}
}
