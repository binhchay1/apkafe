<?php
/**
 * Lasso Table Detail - Ajax.
 *
 * @package Pages
 */

namespace Lasso\Pages\Table_Details;

use Lasso\Libraries\Table\Table_Field_Group;
use Lasso\Libraries\Table\Table_Field_Group_Detail;

use Lasso\Classes\Helper as Lasso_Helper;
use Lasso\Classes\Setting_Enum as Lasso_Setting_Enum;
use Lasso\Classes\Table_Detail as Lasso_Table_Detail;
use Lasso\Classes\Table_Mapping as Lasso_Table_Mapping;

use Lasso\Models\Model;
use Lasso\Models\Table_Details;
use Lasso\Models\Table_Mapping;
use Lasso\Models\Table_Field_Group_Detail as Model_Table_Field_Group_Detail;
use Lasso\Models\Table_Field_Group as Model_Table_Field_Group;

use Lasso_DB;

/**
 * Lasso Table Detail - Ajax.
 */
class Ajax {
	/**
	 * Declare "Lasso ajax requests" to WordPress.
	 */
	public function register_hooks() {
		add_action( 'wp_ajax_lasso_get_table_comparison_view', array( $this, 'lasso_get_table_comparison_view' ) );
		add_action( 'wp_ajax_lasso_get_search_list_table', array( $this, 'lasso_get_search_list_table' ) );
		add_action( 'wp_ajax_lasso_get_tables', array( $this, 'lasso_get_tables' ) );
		add_action( 'wp_ajax_lasso_create_comparison_table', array( $this, 'lasso_create_comparison_table' ) );
		add_action( 'wp_ajax_lasso_delete_table', array( $this, 'lasso_delete_table' ) );
		add_action( 'wp_ajax_lasso_get_table_location_count', array( $this, 'lasso_get_table_location_count' ) );
		add_action( 'wp_ajax_lasso_edit_button_field_information', array( $this, 'lasso_edit_button_field_information' ) );
		add_action( 'wp_ajax_lasso_get_table_locations', array( $this, 'lasso_get_table_locations' ) );

	}

	/**
	 * Handler for tables page
	 */
	public function lasso_get_table_comparison_view() {
		$data       = wp_unslash( $_POST ); // phpcs:ignore
		$sub_action = $data['sub_action'];
		$lasso_id   = $data['lasso_id'] ?? 0;
		$table_id   = $data['table_id'] ?? 0;
		$html       = '';

		switch ( $sub_action ) {
			case 'add_product':
				$html = $this->add_product_to_table_mapping( $data );
				break;
			case 'add_group': // ? add all links in a group to the table
				$html = $this->add_group_to_table_mapping( $data );
				break;
			case 'add_field_to_product':
				if ( empty( $data['lasso_id'] ) ) {
					wp_send_json_error( 'Please add a Product before adding a field', 409 );
				} // @codeCoverageIgnore
				$html = $this->add_field_to_product( $data );
				break;
			case 'add_col_field':
				$table_mappings = Lasso_Table_Mapping::get_list_by_table_id( $table_id );
				if ( empty( $table_mappings ) ) {
					wp_send_json_error( 'Please add a Product before adding a field', 409 );
				} // @codeCoverageIgnore

				$html = $this->load_table( $data, true );
				break;
			case 'add_or_update_table':
				$table_id = $this->add_or_update_table( $data );
				break;
			case 'update_field':
				$this->update_field( $data );
				break;
			case 'load_table':
				$html = $this->load_table( $data );
				break;
			case 'remove_product':
				$this->remove_product( $data );
				break;
			case 'remove_fields':
				$this->remove_fields( $data );
				$html = $this->load_table( $data );
				break;
			case 'sort_products':
				$this->sort_products( $data );
				break;
			case 'sort_field_inside_group':
				$this->sort_field_inside_group( $data );
				break;
			case 'update_field_to_other_group':
				$this->update_field_to_other_group( $data );
				break;
			case 'preview_table':
				$html = $this->preview_table( $data );
				break;
			case 'remove_field':
				$this->remove_field( $data );
				$html = $this->load_table( $data );
				break;
			case 'update_product_mapping':
				$this->update_product_mapping( $data );
				$html = $this->load_table( $data );
				break;
			case 'sort_field_group':
				$this->sort_field_group( $data );
				$html = $this->load_table( $data );
				break;
			case 'clone_table':
				$this->clone_table( $data );
				break;
		}

		wp_send_json_success(
			array(
				'status'        => 1,
				'lasso_id'      => $lasso_id,
				'table_id'      => $table_id,
				'product_count' => Table_Mapping::get_count_by_table_id( $table_id ),
				'html'          => $html,
			)
		);
	} // @codeCoverageIgnore

	/**
	 * Load comparison table
	 *
	 * @param array $data             An array from POST.
	 * @param bool  $add_field_column Flag to add new field column.
	 *
	 * @return false|string
	 */
	private function load_table( $data, $add_field_column = false ) {
		$html               = '';
		$table_id           = $data['table_id'] ?? 0;
		$table_style        = $data['table_style'] ?? null;
		$lasso_table_detail = Table_Details::get_by_id( $table_id );
		if ( isset( $lasso_table_detail ) ) {
			if ( ! isset( $table_style ) ) {
				$table_style = $lasso_table_detail->get_style();
			}

			$file_path = LASSO_PLUGIN_PATH . '/admin/views/tables/components/table-comparision.php';
			$html      = Lasso_Helper::include_with_variables(
				$file_path,
				array(
					'table_style'      => $table_style,
					'table_id'         => $table_id,
					'add_field_column' => $add_field_column,
				)
			);
		}

		return $html;
	}

	/**
	 * Add new field to Product
	 *
	 * @param array $data An array from POST.
	 *
	 * @return false|string
	 */
	private function add_field_to_product( $data ) {
		$html                  = '';
		$table_id              = (int) $data['table_id'] ?? 0;
		$field_id              = (int) $data['field_id'] ?? 0;
		$lasso_id              = (int) $data['lasso_id'] ?? 0;
		$field_group_id        = ! empty( $data['field_group_id'] ) ? $data['field_group_id'] : null;
		$order                 = (int) $data['order'] ?? null;
		$table                 = Lasso_Table_Detail::get_by_id( $table_id );
		$field_values          = $data['field_values'] ?? array();
		$table_mapping_product = Lasso_Table_Mapping::get_by_table_id_lasso_id( $table_id, $lasso_id );
		if ( $table && $table->is_exist_field_id( $field_id ) ) {
			wp_send_json_error( 'This field already exists, please select another field', 409 );
		} elseif ( 0 < $lasso_id && 0 < $field_id && $table_mapping_product ) {
			$table_mapping_product->add_field( $field_id, $field_group_id, $order );
			$field_group_details = Table_Field_Group_Detail::get_list_by_field_group_id_field_id( $field_group_id, $field_id );
			foreach ( $field_group_details as $field_group_detail ) {
				foreach ( $field_values as $key => $item ) {
					if ( $field_group_detail->get_lasso_id() === (int) $item['lasso_id']
						&& $field_group_detail->get_field_id() === (int) $item['field_id'] ) {
						$field_group_detail->set_field_value( $item['field_value'] );
						$field_group_detail->update();
						unset( $field_values[ $key ] );
						break;
					}
				}
			}
		}
		return $html;
	} // @codeCoverageIgnore

	/**
	 * Add a product to table comparision
	 *
	 * @param array $data               An array from POST.
	 * @param bool  $is_adding_by_group Is adding product by group.
	 *
	 * @return false|string
	 */
	private function add_product_to_table_mapping( $data, $is_adding_by_group = false ) {
		$table_id = $data['table_id'] ?? 0;
		$lasso_id = $data['lasso_id'] ?? 0;

		if ( empty( $table_id ) ) {
			wp_send_json_error( 'Please input the table name', 409 );
		}

		$list_mapping_field = Lasso_Table_Mapping::get_list_by_table_id_lasso_id( $table_id, $lasso_id );
		if ( ! empty( $list_mapping_field ) && ! $is_adding_by_group ) {
			wp_send_json_error( 'This product already exists, please select another product', 409 );
		}

		if ( $lasso_id > 0 ) {
			$post = get_post( $lasso_id );
			if ( LASSO_POST_TYPE !== $post->post_type ?? '' ) {
				wp_send_json_error( 'Invalid Lasso post', 409 );
			}
		}

		if ( ! empty( $lasso_id ) && empty( $list_mapping_field ) ) {
			Table_Mapping::add_product( $table_id, $lasso_id );
		}

		return $this->load_table( $data );
	} // @codeCoverageIgnore

	/**
	 * Add a product to table comparision
	 *
	 * @param array $data An array from POST.
	 *
	 * @return false|string
	 */
	private function add_group_to_table_mapping( $data ) {
		$lasso_db = new Lasso_DB();

		$group_id = $data['lasso_id'] ?? 0;
		$where    = $group_id ? Model::prepare( 't.term_id = %d', $group_id ) : '1 = 1';
		$sql      = $lasso_db->get_urls_in_group( '', $where );
		$links    = Model::get_results( $sql );

		foreach ( $links as $link ) {
			Table_Mapping::add_product( $data['table_id'], $link->ID );
		}

		return $this->load_table( $data );
	}

	/**
	 * Update field value from table comparison
	 *
	 * @param array $data An array from POST.
	 */
	private function update_field( $data ) {
		$table_id       = $data['table_id'] ?? 0;
		$lasso_id       = $data['lasso_id'] ?? 0;
		$field_id       = $data['field_id'] ?? 0;
		$field_value    = $data['field_value'] ?? null;
		$field_group_id = $data['field_group_id'] ?? null;
		$badge_text     = $data['badge_text'] ?? null;
		$table          = Lasso_Table_Detail::get_by_id( $table_id );
		if ( $table ) {
			$table_product = Table_Mapping::get_by_table_id_lasso_id( $table->get_id(), $lasso_id );
			if ( isset( $badge_text ) ) {
				$table_product->set_badge_text( $badge_text );
				$table_product->update();
			}
		}

		$table_field_mapping = Table_Field_Group_Detail::get_by_field_id_field_group_id_lasso_id( $field_id, $field_group_id, $lasso_id );
		if ( isset( $table_field_mapping ) ) {
			$field_value = apply_filters( 'lasso_filter_table_field_value', $field_value, $field_id );
			$table_field_mapping->set_field_value( $field_value );
			$table_field_mapping->update();
			wp_send_json_success(
				array(
					'status' => 1,
					'html'   => '',
				)
			);
		} else {
			wp_send_json_error( 'Unexpected error. Please contact your developer', 409 );
		}
	} // @codeCoverageIgnore

	/**
	 * Add or Update table comparison
	 *
	 * @param array $data an array from POST.
	 *
	 * @return mixed
	 */
	private function add_or_update_table( $data ) {
		if ( empty( trim( $data['title'] ) ) ) {
			wp_send_json_error( 'Please input table name', 409 );
		}
		$table_id           = $data['table_id'] ?? 0;
		$data['id']         = (int) $table_id;
		$lasso_table_detail = new Table_Details( (object) $data );
		if ( 0 === $lasso_table_detail->get_id() ) {
			$lasso_table_detail->insert();
		} else {
			$lasso_table_detail = Table_Details::get_by_id( $table_id );
			if ( isset( $lasso_table_detail ) ) {
				$lasso_table_detail->set_title( $data['title'] );
				$lasso_table_detail->set_style( $data['style'] );
				$lasso_table_detail->set_theme( $data['theme'] );
				$lasso_table_detail->set_show_headers_horizontal( $data['show_headers_horizontal'] );
				$lasso_table_detail->update();
			}
		}
		$lasso_table_detail->set_show_field_name( $data['show_field_name'] );

		return $lasso_table_detail->get_id();
	} // @codeCoverageIgnore

	/**
	 * Remove product out table mapping and also remove fields belong to that product
	 *
	 * @param array $data an array from POST.
	 */
	private function remove_product( $data ) {
		$table_id = $data['table_id'] ?? 0;
		$lasso_id = $data['lasso_id'] ?? 0;
		$fields   = Lasso_Table_Mapping::get_list_by_table_id_lasso_id( $table_id, $lasso_id );
		if ( ! empty( $fields ) ) {
			foreach ( $fields as $field ) {

				$field_groups = Table_Field_Group::get_list_field_by_table_id_lasso_id( $table_id, $lasso_id );
				foreach ( $field_groups as $field_group ) {
					$field_group_details = Table_Field_Group_Detail::get_list_by_lasso_id_field_group_id( $lasso_id, $field_group->get_field_group_id() );
					foreach ( $field_group_details as $field_group_detail ) {
						$field_group_detail->delete();

					}
					$field_group->delete();
				}
				$field->delete();
			}
		}
	}

	/**
	 * Remove same fields
	 *
	 * @param array $data am array from POST.
	 */
	private function remove_fields( $data ) {
		$table_id           = $data['table_id'] ?? 0;
		$field_group_id     = $data['field_group_id'] ?? 0;
		$table_field_groups = Table_Field_Group::get_list_by_table_id_field_group_id( $table_id, $field_group_id );
		foreach ( $table_field_groups as $table_field_group ) {
			$table_field_group_details = Table_Field_Group_Detail::get_list_field_group_id( $table_field_group->get_field_group_id(), false );
			foreach ( $table_field_group_details as $table_field_group_detail ) {
				$table_field_group_detail->delete();
			}
			$table_field_group->delete();

		}
	}

	/**
	 * Sort products in table comparison.
	 *
	 * @param array $data an array from POST.
	 */
	private function sort_products( $data ) {
		$table_id = $data['table_id'] ?? 0;
		$data     = $data['data'] ?? 0;
		foreach ( $data as $item ) {
			$table_mapping_product = Lasso_Table_Mapping::get_by_table_id_lasso_id( $table_id, $item['lasso_id'] );
			if ( isset( $table_mapping_product ) ) {
				$table_mapping_product->set_order( $item['order'] );
				$table_mapping_product->update();
			}
		}
	}

	/**
	 * Sort fields inside group
	 *
	 * @param array $data an array from POST.
	 */
	private function sort_field_inside_group( $data ) {
		$table_id             = $data['table_id'];
		$data                 = $data['data'] ?? array();
		$data_clone           = $data;
		$tb_products_mappings = Table_Mapping::get_list_by_table_id( $table_id );

		if ( ! is_array( $data ) || empty( $data ) ) {
			wp_send_json_error( 'Invalid data', 409 );
			return;
		}

		foreach ( $data as $key => $item ) {
			unset( $data_clone[ $key ] );
			foreach ( $data_clone as $value ) {
				if ( $value['field_id'] === $item['field_id'] ) {
					wp_send_json_error( 'Field already existed in this cell', 409 );
					return;
				}
			}
			$table_field_group_detail = Table_Field_Group_Detail::get_by_field_id_field_group_id_lasso_id( $item['field_id'], $item['field_group_id'], $item['lasso_id'] );
			if ( isset( $table_field_group_detail ) ) {
				$table_field_group_detail->set_order( $item['order'] );
				$table_field_group_detail->update();
				foreach ( $tb_products_mappings as $table_mapping ) {
					if ( $table_mapping->get_lasso_id() === $table_field_group_detail->get_lasso_id() ) {
						continue;
					}
					$tb_field_group_detail_other = Table_Field_Group_Detail::get_by_field_id_field_group_id_lasso_id( $item['field_id'], $item['field_group_id'], $table_mapping->get_lasso_id() );
					$tb_field_group_detail_other->set_order( $item['order'] );
					$tb_field_group_detail_other->update();
				}
			}
		}
	} // @codeCoverageIgnore

	/**
	 * Update fields when User drag field to other cell
	 *
	 * @param array $data an array from POST.
	 */
	private function update_field_to_other_group( $data ) {
		$table_id            = (int) $data['table_id'] ?? 0;
		$from_lasso_id       = (int) $data['from_lasso_id'] ?? 0;
		$to_lasso_id         = (int) $data['to_lasso_id'] ?? 0;
		$field_id            = (int) $data['field_id'] ?? 0;
		$to_group_id         = $data['to_group_id'] ?? null;
		$from_group_id       = $data['from_group_id'] ?? null;
		$field_values_backup = array();
		$field_group         = Table_Field_Group::get_by_table_id_lasso_id_field_group_id( $table_id, $to_lasso_id, $to_group_id );
		if ( empty( $field_group ) ) {
			// ? This case happen when users drag a fields from column(row) to a empty column(row)
			$table_mapping_product = Lasso_Table_Mapping::get_by_table_id_lasso_id( $table_id, $to_lasso_id );
			if ( $table_mapping_product ) {
				$table_mapping_product->add_field( $field_id, $to_group_id );
				$current_field_group_details = Table_Field_Group_Detail::get_list_by_field_group_id_field_id( $from_group_id, $field_id );
				foreach ( $current_field_group_details as $current_field_group_detail ) {
					$field_values_backup[ $table_id . '-' . $current_field_group_detail->get_lasso_id() . '-' . $current_field_group_detail->get_field_id() ] = $current_field_group_detail->get_field_value();
					$current_field_group_detail->delete();
				}
				$from_field_groups = Table_Field_Group::get_list_by_table_id_field_group_id( $table_id, $from_group_id );
				foreach ( $from_field_groups as $field_group ) {
					$field_group_details = Table_Field_Group_Detail::get_list_by_lasso_id_field_group_id( $field_group->get_lasso_id(), $from_group_id );
					if ( empty( $field_group_details ) ) {
						$field_group->set_field_id( 0 );
						$field_group->update();
					}
				}
			}
		} else {
			if ( $from_lasso_id === $to_lasso_id ) {
				$field_group_details = Table_Field_Group_Detail::get_list_by_lasso_id_field_group_id( $to_lasso_id, $to_group_id );
			} else {
				$field_group_details = Table_Field_Group_Detail::get_list_by_table_id_lasso_id( $table_id, $to_lasso_id );
			}

			foreach ( $field_group_details as $field_group_detail ) {
				if ( $field_group_detail->get_field_id() === $field_id ) {
					wp_send_json_error( 'This field already exists, please select another field', 409 );
					return;
				}
			}

			$field_group_detail = Table_Field_Group_Detail::get_by_field_id_field_group_id_lasso_id( $field_id, $to_group_id, $to_lasso_id );
			if ( ! isset( $field_group_detail ) ) {
				$lasso_table_mapping = Lasso_Table_Mapping::get_by_table_id_lasso_id( $table_id, $to_lasso_id );
				$lasso_table_mapping->add_field( $field_id, $to_group_id );

				// ? We should delete fields from old group in the group details
				$current_field_group_details = Table_Field_Group_Detail::get_list_by_field_group_id_field_id( $from_group_id, $field_id );
				foreach ( $current_field_group_details as $current_field_group_detail ) {
					$field_values_backup[ $table_id . '-' . $current_field_group_detail->get_lasso_id() . '-' . $current_field_group_detail->get_field_id() ] = $current_field_group_detail->get_field_value();
					$current_field_group_detail->delete();
				}

				$current_field_group_details = Table_Field_Group_Detail::get_list_field_group_id( $from_group_id );
				$next_field_id               = 0;
				if ( ! empty( $current_field_group_details ) ) {
					$next_field_id = $current_field_group_details[0]->get_field_id();
				}
				$fields_groups = Table_Field_Group::get_list_by_table_id_field_group_id( $table_id, $from_group_id );
				foreach ( $fields_groups as $field_group ) {
					// ? If we delete the first field in the group, we should update other fields to table field group
					if ( Lasso_Helper::compare_string( $field_group->get_field_id(), $field_id ) ) {
						$field_group->set_field_id( $next_field_id );
						$field_group->update();
					} else {
						$field_group_details = Table_Field_Group_Detail::get_list_by_field_group_id_field_id( $from_group_id, $next_field_id );
						if ( empty( $field_group_details ) ) {
							$field_group->set_field_id( 0 );
							$field_group->update();
						}
					}
				}
			}
		}

		$field_groups = Table_Field_Group::get_list_by_table_id_field_group_id( $table_id, $from_group_id );
		foreach ( $field_groups as $field_group ) {
			$field_group_details = Table_Field_Group_Detail::get_list_field_group_id( $field_group->get_field_group_id() );
			if ( empty( $field_group_details ) ) {
				$field_group->delete();
			}
		}
		$field_group_details = Table_Field_Group_Detail::get_list_by_field_group_id_field_id( $to_group_id, $field_id );
		foreach ( $field_group_details as $field_group_detail ) {
			$field_value = $field_values_backup[ $table_id . '-' . $field_group_detail->get_lasso_id() . '-' . $field_group_detail->get_field_id() ] ?? null;
			if ( isset( $field_value ) ) {
				$field_group_detail->set_field_value( $field_value );
				$field_group_detail->update();
			}
		}
	} // @codeCoverageIgnore

	/**
	 * Preview table
	 *
	 * @param array $data an array from POST.
	 *
	 * @return string
	 */
	private function preview_table( $data ) {
		$html     = '';
		$table_id = $data['table_id'] ?? 0;
		$table    = Table_Details::get_by_id( $table_id );
		if ( $table ) {
			$short_code = '[lasso type="%s" table_id="%d" ]';
			$short_code = sprintf( $short_code, Lasso_Setting_Enum::DISPLAY_TYPE_TABLE, $table->get_id() );
			$html       = do_shortcode( $short_code );
		}
		return $html;
	}

	/**
	 * Remove field
	 *
	 * @param array $data an array from POST.
	 */
	private function remove_field( $data ) {
		$table_id       = $data['table_id'] ?? 0;
		$field_id       = $data['field_id'] ?? 0;
		$lasso_id       = $data['lasso_id'] ?? 0;
		$field_group_id = $data['field_group_id'] ?? '';

		$table = Lasso_Table_Detail::get_by_id( $table_id );
		if ( $table ) {
			$table_product = Lasso_Table_Mapping::get_by_table_id_lasso_id( $table->get_id(), $lasso_id );
			if ( ! empty( $field_group_id ) ) {
				// ? Remove out field to table field group
				$field_group = Table_Field_Group::get_by_table_id_lasso_id_field_group_id( $table->get_id(), $table_product->get_lasso_id(), $field_group_id );
				if ( $field_group ) {
					$field_group_details = Table_Field_Group_Detail::get_list_by_field_group_id_field_id( $field_group->get_field_group_id(), $field_id );
					foreach ( $field_group_details as $field_group_detail ) {
						$field_group_detail->delete();
					}

					$field_group_details = Table_Field_Group_Detail::get_list_field_group_id( $field_group->get_field_group_id() );
					if ( empty( $field_group_details ) ) {
						$field_groups = Table_Field_Group::get_list_by_table_id_field_group_id( $table->get_id(), $field_group->get_field_group_id() );
						foreach ( $field_groups as $field_group ) {
							$field_group->delete();
						}
					}
				}
			}
		}

	}

	/**
	 * Update mapping product information.
	 *
	 * @param array $data an array from POST.
	 */
	private function update_product_mapping( $data ) {
		$table_id   = $data['table_id'] ?? 0;
		$lasso_id   = $data['lasso_id'] ?? 0;
		$title      = $data['title'] ?? null;
		$table      = Lasso_Table_Detail::get_by_id( $table_id );
		$badge_text = $data['badge_text'] ?? null;
		if ( $table ) {
			$table_product = Table_Mapping::get_by_table_id_lasso_id( $table->get_id(), $lasso_id );
			$should_update = false;
			if ( ! is_null( $title ) ) {
				$table_product->set_title( $title );
				$should_update = true;
			}
			if ( ! ( is_null( $badge_text ) ) ) {
				$table_product->set_badge_text( trim( $badge_text ) );
				$should_update = true;
			}

			if ( $should_update ) {
				$table_product->update();
			}
		}
	}

	/**
	 * Sort field group
	 *
	 * @param array $data an array from POST.
	 */
	private function sort_field_group( $data ) {
		$table_id = $data['table_id'] ?? 0;
		$data     = $data['data'] ?? array();
		$table    = Lasso_Table_Detail::get_by_id( $table_id );
		if ( $table ) {
			foreach ( $data as $item ) {
				$field_group_id = $item['field_group_id'] ?? '';
				if ( ! $field_group_id ) {
					continue;
				}

				$table_field_groups = Table_Field_Group::get_list_by_table_id_field_group_id( $table->get_id(), $field_group_id );
				foreach ( $table_field_groups as $table_field_group ) {
					$table_field_group->set_order( $item['order'] );
					$table_field_group->update();
				}
			}
		}
	}

	/**
	 * Check field is inside product title cell
	 *
	 * @param int $group_id Group ID.
	 *
	 * @return bool
	 */
	private function is_from_product_cell( $group_id ) {
		return 'title-field' === $group_id;
	}

	/**
	 * Get tables by search key
	 */
	public function lasso_get_search_list_table() {
        $data               = wp_unslash( $_POST ); // phpcs:ignore
		$page_number        = intval( $data['page_number'] ?? 1 );
		$limit              = $data['limit'] ?? 10;
		$search             = $data['search_term'] ?? '';
		$search             = str_replace( ' ', '%', $search );
		$search_body        = $search ? "%$search%" : '';
		$search_term_string = '' !== $search ? Model::prepare( 'AND title LIKE %s', $search_body ) : '';
		$table_detail_model = new Table_Details();
		$tables_results     = $table_detail_model->get_search_list( $page_number, $limit, $search_term_string );
		$total_table        = $search ? count( $tables_results ) : $table_detail_model->total_count();
		$html               = '';

		if ( ! empty( $tables_results ) ) {
			foreach ( $tables_results as $table ) {
				$html .= Lasso_Helper::include_with_variables(
					LASSO_PLUGIN_PATH . '/admin/views/tables/components/table-row.php',
					array(
						'table' => $table,
					)
				);
			}
		} else {
			$html .= Lasso_Helper::include_with_variables(
				LASSO_PLUGIN_PATH . '/admin/views/education/list-table-empty.php'
			);
		}

		wp_send_json_success(
			array(
				'status'      => 1,
				'html'        => $html,
				'total_table' => $total_table,
				'page'        => $page_number,
			)
		);
	} // @codeCoverageIgnore

	/**
	 * Get tables
	 */
	public function lasso_get_tables() {
		$post = wp_unslash( $_POST ); // phpcs:ignore

		$search = str_replace( ' ', '%', $post['search_key'] );
		$limit  = $post['limit'];
		$page   = $post['page'];

		$lasso_db = new Lasso_DB();

		// ? Process Sorting
		$search_term_string = '' !== $search ? "AND title LIKE '%" . $search . "%'" : '';
		$order_by           = 'id';
		$order_type         = 'desc';

		$table_detail_model = new Table_Details();
		$sql                = $table_detail_model->get_tables_query( $search_term_string );

		$tables_sql = $lasso_db->set_order( $sql, $order_by, $order_type );
		$tables_sql = Lasso_Helper::paginate( $tables_sql, $page, $limit );

		$data  = Model::get_results( $tables_sql );
		$count = Model::get_count( $sql );

		wp_send_json_success(
			array(
				'post'  => $post,
				'count' => $count,
				'page'  => $page,
				'data'  => $data,
			)
		);
	} // @codeCoverageIgnore

	/**
	 * Clone a comparison table
	 *
	 * @param array $data an array from POST.
	 */
	private function clone_table( $data ) {
		$table_id     = $data['table_id'] ?? 0;
		$table_detail = Table_Details::get_by_id( $table_id );
		if ( $table_detail ) {
			$table_detail->clone_table();
			$total_table  = count( Lasso_Table_Detail::get_list( null, null, true ) );
			$current_page = ceil( $total_table / Lasso_Setting_Enum::LIMIT_TABLE_ON_PAGE );
			wp_send_json_success(
				array(
					'status'       => 1,
					'current_page' => $current_page,
				)
			);
		} else {
			wp_send_json_error( 'Table not found', 409 );
		}
	} // @codeCoverageIgnore

	/**
	 * Create new comparison table.
	 */
	public function lasso_create_comparison_table() {
		$data        = wp_unslash( $_POST ); // phpcs:ignore
		$table_name  = $data['table_name'] ?? null;
		$table_style = $data['table_style'] ?? Lasso_Setting_Enum::TABLE_STYLE_COLUMN;
		$table_theme = $data['$table_theme'] ?? Lasso_Setting_Enum::THEME_CACTUS;

		if ( $table_name ) {
			$table_detail = new Table_Details();
			$table_detail->set_title( $table_name );
			$table_detail->set_style( $table_style );
			$table_detail->set_theme( $table_theme );
			$table_detail->set_show_title( 0 );
			$table_detail->set_show_headers_horizontal( Table_Details::ENABLE_SHOW_HEADERS_HORIZONTAL );
			$table_detail->insert();

			wp_send_json_success(
				array(
					'status'       => 1,
					'table_id'     => $table_detail->get_id(),
					'redirect_url' => $table_detail->get_link_detail(),
				)
			);
		} else {
			wp_send_json_error( 'Table name is not found', 409 );
		}
	} // @codeCoverageIgnore

	/**
	 * Delete a table comparison.
	 *
	 * @param int  $custom_table_id Custom table id.
	 * @param bool $revert_table    Whether we are reverting table.
	 */
	public function lasso_delete_table( $custom_table_id = 0, $revert_table = false ) {
		$data         = wp_unslash( $_POST ); // phpcs:ignore
		$table_id     = $custom_table_id ? $custom_table_id : $data['table_id'] ?? 0;
		$table_detail = Table_Details::get_by_id( $table_id );

		if ( $table_detail ) {
			$status = 1;
			$msg    = '';
			if ( 0 === $table_detail->get_total_locations() || $revert_table ) {
				$table_id                = $table_detail->get_id();
				$table_group_detail_name = ( new Model_Table_Field_Group_Detail() )->get_table_name();
				$table_group_name        = ( new Model_Table_Field_Group() )->get_table_name();
				$table_mapping_name      = ( new Table_Mapping() )->get_table_name();
				$table_detail_name       = ( new Table_Details() )->get_table_name();

				$sql   = '
					DELETE FROM ' . $table_group_detail_name . ' 
					WHERE field_group_id IN ( 
						SELECT field_group_id 
						FROM ' . $table_group_name . ' 
						WHERE table_id = %d 
					)';
				$query = Model::prepare( $sql, $table_id );
				Model::query( $query );

				$sql   = '
					DELETE FROM ' . $table_group_name . ' 
					WHERE table_id = %d
				';
				$query = Model::prepare( $sql, $table_id );
				Model::query( $query );

				$sql   = '
					DELETE FROM ' . $table_mapping_name . ' 
					WHERE table_id = %d
				';
				$query = Model::prepare( $sql, $table_id );
				Model::query( $query );

				$sql   = '
					DELETE FROM ' . $table_detail_name . '
					WHERE id = %d
				';
				$query = Model::prepare( $sql, $table_id );
				Model::query( $query );
			} else {
				$status = 0;
				$msg    = 'Delete unsuccessful since ' . $table_detail->get_title() . ' are using';
			}

			$result = array(
				'status' => $status,
				'msg'    => $msg,
			);

			if ( $custom_table_id ) {
				return $result;
			} else {
				wp_send_json_success( $result );
			}
		} else {
			wp_send_json_error( 'Table is not found', 409 );
		}
	} // @codeCoverageIgnore

	/**
	 * Get Table location count
	 */
	public function lasso_get_table_location_count() {
		$data           = wp_unslash( $_POST ); // phpcs:ignore
		$table_id       = $data['table_id'] ?? 0;
		$table_detail   = Table_Details::get_by_id( $table_id );
		$location_count = $table_detail->get_total_locations();

		wp_send_json_success(
			array(
				'status'         => 1,
				'location_count' => $location_count,
			)
		);
	} // @codeCoverageIgnore

	/**
	 * Edit button field information
	 */
	public function lasso_edit_button_field_information() {
		$data                  = wp_unslash( $_POST ); // phpcs:ignore
		$field_group_detail_id = $data['field_group_detail_id'];
		$button_text           = $data['button_text'];
		$button_url            = $data['button_url'];

		$field_group_detail = new Model_Table_Field_Group_Detail();
		$field_group_detail = $field_group_detail->get_one( $field_group_detail_id );
		$field_value        = wp_json_encode(
			array(
				'button_text' => $button_text,
				'url'         => $button_url,
			)
		);
		$field_group_detail->set_field_value( $field_value );
		$field_group_detail->update();

		wp_send_json_success(
			array(
				'status' => 1,
			)
		);
	} // @codeCoverageIgnore

	/**
	 * Get table locations
	 */
	public function lasso_get_table_locations() {
		$data        = wp_unslash( $_POST ); // phpcs:ignore
		$table_id    = $data['table_id'] ?? null;
		$page_number = $data['page_number'] ?? 1;
		$limit       = $data['limit'] ?? 10;
		$table       = Table_Details::get_by_id( $table_id );
		if ( ! is_null( $table->get_id() ) ) {
			$total_count = ( Table_Details::get_by_id( $table_id ) )->get_total_locations();
			$posts_sql   = $table->get_locations_query( array( 'p.ID', 'p.post_title' ) );
			$posts_sql   = Lasso_Helper::paginate( $posts_sql, $page_number, $limit );
			$posts       = Model::get_results( $posts_sql );

			foreach ( $posts as $post ) {
				$post->edit_post = get_edit_post_link( $post->ID );
				$post->post_link = esc_url( get_permalink( $post->ID ) );
			}

			wp_send_json_success(
				array(
					'status' => 1,
					'page'   => $page_number,
					'count'  => $total_count,
					'datas'  => $posts,
				)
			);
		} else {
			wp_send_json_error( 'Table is not found', 409 );
		}
	} // @codeCoverageIgnore
}
