<?php
if ( ! class_exists( 'Wpvc_Settings_Model' ) ) {
	/**
	 *  Settings Model.
	 */
	class Wpvc_Settings_Model {

		/**
		 *  Get Plugin Settings.
		 */
		public static function wpvc_settings_page_json() {
			global $wpdb;

			$image_drop    = self::wpvc_image_settings();
			$sidebar       = self::wpvc_sidebar_setting();
			$order_by      = self::wpvc_order_by_drop();
			$track         = self::wpvc_vote_tracking();
			$vote_freq     = self::wpvc_vote_frequency();
			$page_navi     = self::wpvc_pagi_navi();
			$options       = self::wpvc_setting_values();
			$custom_fields = self::wpvc_custom_fields_json();
			$return_val    = array(
				'image_drop'          => $image_drop,
				'sidebar_d'           => $sidebar,
				'order_d'             => $order_by,
				'track'               => $track,
				'freq'                => $vote_freq,
				'pagin'               => $page_navi,
				'settings'            => $options,
				'admin_custom_fields' => $custom_fields,
			);
			return $return_val;
		}
		/**
		 *  Get Plugin Original Settings.
		 */
		public static function wpvc_setting_values() {
			global $wpdb;
			$options = get_option( WPVC_VOTES_SETTINGS );
			return $options;
		}
		/**
		 *  Get Plugin Sidebar Settings.
		 */
		public static function wpvc_sidebar_setting() {
			$sidebars       = array();
			$registered_bar = $GLOBALS['wp_registered_sidebars'];
			if ( ! empty( $registered_bar ) ) {
				foreach ( $registered_bar as $sid ) {
					$sidebars[ $sid['id'] ] = $sid['name'];
				}
			}
			return $sidebars;
		}
		/**
		 *  Get Image Sizes.
		 */
		public static function wpvc_image_settings() {
			global $_wp_additional_image_sizes;
			$sizes = array();
			foreach ( get_intermediate_image_sizes() as $s ) {
				$sizes[ $s ] = array( 0, 0 );
				if ( in_array( $s, array( 'thumbnail', 'medium', 'large' ) ) ) {
					$sizes[ $s ][0] = get_option( $s . '_size_w' );
					$sizes[ $s ][1] = get_option( $s . '_size_h' );
				} elseif ( isset( $_wp_additional_image_sizes ) && isset( $_wp_additional_image_sizes[ $s ] ) ) {
						$sizes[ $s ] = array( $_wp_additional_image_sizes[ $s ]['width'], $_wp_additional_image_sizes[ $s ]['height'] );
				}
			}
			$all_sizes = array();
			foreach ( $sizes as $size => $atts ) {
				$all_sizes[ $size ] = $size . ' - ' . implode( 'x', $atts );
			}
			return $all_sizes;
		}
		/**
		 *  Get Plugin Order By Settings.
		 */
		public static function wpvc_order_by_drop() {
			$order_by = array(
				'author'     => 'Author',
				'date'       => 'Date',
				'title'      => 'Title',
				'modified'   => 'Modified',
				'menu_order' => 'Menu Order',
				'parent'     => 'Parent',
				'id'         => 'ID',
				'votes'      => 'Votes',
				'rand'       => 'Random',
			);
			return $order_by;
		}
		/**
		 *  Get Plugin Tracking Method.
		 */
		public static function wpvc_vote_tracking() {
			$track = array(
				'ip_traced'     => 'IP Traced',
				'cookie_traced' => 'Cookie Traced',
			);
			return $track;
		}
		/**
		 *  Get Plugin Frequency.
		 */
		public static function wpvc_vote_frequency() {
			$freq = array(
				'11' => 'per Category',
			);
			return $freq;
		}
		/**
		 *  Get Plugin Navigation.
		 */
		public static function wpvc_pagi_navi() {
			$pagin = array(
				'1' => 'Normal',
				'2' => 'Drop-down List',
			);
			return $pagin;
		}

		/**
		 *  Get Plugin Custom Fields.
		 *
		 * @param int $custom_id Custom Field Id.
		 */
		public static function wpvc_custom_fields_get_original( $custom_id ) {
			global $wpdb;
			$delete_time   = 0;
			$sql           = $wpdb->prepare( 'SELECT * FROM ' . WPVC_VOTES_ENTRY_CUSTOM_TABLE . ' WHERE delete_time = %d && id = %d order by id DESC', $delete_time, $custom_id );
			$custom_fields = $wpdb->get_row( $sql, ARRAY_A );
			return $custom_fields;
		}
		/**
		 *  Get Plugin Custom Field Json.
		 */
		public static function wpvc_custom_fields_json() {
			global $wpdb;
			$delete_time   = 0;
			$sql           = $wpdb->prepare( 'SELECT * FROM ' . WPVC_VOTES_ENTRY_CUSTOM_TABLE . ' WHERE delete_time = %d order by id DESC', $delete_time );
			$custom_fields = $wpdb->get_results( $sql, ARRAY_A );
			$ret_data      = array();
			if ( count( $custom_fields ) > 0 ) {
				foreach ( $custom_fields as $custom ) {
					$react_val = json_decode( $custom['react_val'] );
					unset( $custom['react_val'] );
					$custom['react_val'] = $react_val;
					$ret_data[]          = $custom;
				}
			}
			return $ret_data;
		}
		/**
		 *  Update Plugin Custom Field.
		 *
		 * @param array $custom_fields_id Custom Fields array.
		 * @param array $input_data Original data array.
		 */
		public static function wpvc_update_contestant_custom_field( $custom_fields_id, $input_data ) {
			global $wpdb, $current_user;

			$data                     = array(
				'text_field_type' => $input_data['react_val']['text_field_type'],
				'text_area_row'   => $input_data['react_val']['text_area_row'],
				'value_placement' => $input_data['react_val']['value_placement'],
				'upload_icon'     => $input_data['react_val']['upload_icon'],
				'datepicker_only' => $input_data['react_val']['datepicker_only'],
			);
			$other_react_values       = json_encode( $data );
			$input_data['sequence']   = 0;
			$input_data['current_id'] = $current_user->ID;
			$input_data['react_val']  = $other_react_values;

			$update = $wpdb->query( $wpdb->prepare( 'UPDATE ' . WPVC_VOTES_ENTRY_CUSTOM_TABLE . ' SET question_type = %s , question = %s , description = %s , response = %s, required = %s ,admin_only =%s , required_text = %s ,pretty_view = %s , ow_file_size = %d , sequence = %d ,admin_view = %s ,grid_only = %s,list_only = %s,show_labels = %s,show_labels_single = %s,set_limit = %s,limit_count = %d ,react_val = %s WHERE id =%d', $input_data['question_type'], $input_data['question'], $input_data['description'], $input_data['response'], $input_data['required'], $input_data['admin_only'], $input_data['required_text'], $input_data['pretty_view'], $input_data['ow_file_size'], $input_data['sequence'], $input_data['admin_view'], $input_data['grid_only'], $input_data['list_only'], $input_data['show_labels'], $input_data['show_labels_single'], 'N', 0, $input_data['react_val'], $custom_fields_id ) );

			return $update;
		}
		/**
		 *  Get total Voting Count.
		 */
		public static function wpvc_get_total_voting_count() {
			global $wpdb;
			$sql        = 'SELECT log.*,pst.post_title,pst.post_author,user.display_name FROM ' . WPVC_VOTES_TBL . ' as log LEFT JOIN ' . $wpdb->prefix . 'posts as pst on log.post_id=pst.ID LEFT JOIN ' . $wpdb->prefix . 'users as user on pst.post_author=user.ID ORDER BY id DESC';
			$result     = $wpdb->get_results( $sql, ARRAY_A );
			$total_rows = $wpdb->num_rows;
			return $total_rows;
		}
		/**
		 *  Get Pagination.
		 *
		 * @param  int $paged Pagination Count.
		 */
		public static function wpvc_votings_get_paged( $paged ) {
			global $wpdb;
			$limit = 20;
			if ( $paged == 0 ) {
				$offset = 0;
			} else {
				$offset = $paged * $limit;
			}
			$sql        = $wpdb->prepare( 'SELECT log.*,pst.post_title,pst.post_author,user.display_name FROM ' . WPVC_VOTES_TBL . ' as log LEFT JOIN ' . $wpdb->prefix . 'posts as pst on log.post_id=pst.ID LEFT JOIN ' . $wpdb->prefix . 'users as user on pst.post_author=user.ID ORDER BY id DESC LIMIT %d,%d', $offset, $limit );
			$votinglogs = $wpdb->get_results( $sql, ARRAY_A );
			return $votinglogs;
		}
		/**
		 *  Get Delete Vote Count.
		 *
		 * @param  int $vote_id Vote ID.
		 */
		public static function wpvc_delete_contestant_voting( $vote_id ) {
			global $wpdb;
			// Get the Count of Votes.
			if ( ! empty( $vote_id ) ) {
				$sql   = $wpdb->prepare( 'SELECT * FROM ' . WPVC_VOTES_TBL . ' WHERE id = %d', $vote_id );
				$votes = $wpdb->get_row( $sql, ARRAY_A );
				if ( is_array( $votes ) ) {
					$post_id  = $votes['post_id'];
					$no_votes = $wpdb->get_var( $wpdb->prepare( 'SELECT votes FROM ' . WPVC_VOTES_TBL . ' WHERE id = %d', $vote_id ) );
					$wpdb->delete( WPVC_VOTES_TBL, array( 'id' => $vote_id ), array( '%d' ) );
					$vote_count = get_post_meta( $post_id, WPVC_VOTES_CUSTOMFIELD, true );

					if ( $vote_count > 0 ) {
						update_post_meta( $post_id, WPVC_VOTES_CUSTOMFIELD, $vote_count - $no_votes, $vote_count );
					}
				}
			}
		}
		/**
		 *  Multiple Delete Vote Count.
		 *
		 * @param  mixed $vote_id Vote ID.
		 */
		public static function wpvc_multiple_delete_contestant_voting( $vote_id ) {
			global $wpdb;
			// Get the Count of Votes
			if ( ! empty( $vote_id ) ) {
				foreach ( $vote_id as $vid ) {
					$sql   = $wpdb->prepare( 'SELECT * FROM ' . WPVC_VOTES_TBL . ' WHERE id = %d', $vid );
					$votes = $wpdb->get_row( $sql, ARRAY_A );
					if ( is_array( $votes ) ) {
						$post_id  = $votes['post_id'];
						$no_votes = $wpdb->get_var( $wpdb->prepare( 'SELECT votes FROM ' . WPVC_VOTES_TBL . ' WHERE id = %d', $vid ) );
						$wpdb->delete( WPVC_VOTES_TBL, array( 'id' => $vid ), array( '%d' ) );
						$vote_count = get_post_meta( $post_id, WPVC_VOTES_CUSTOMFIELD, true );
						if ( $vote_count > 0 ) {
							update_post_meta( $post_id, WPVC_VOTES_CUSTOMFIELD, $vote_count - $no_votes, $vote_count );
						}
					}
				}
			}
		}
		/**
		 *  Get all terms.
		 *
		 * @param  array $term_meta Term Meta keys.
		 */
		public static function wpvc_category_get_all_terms( $term_meta ) {
			$terms = get_terms(
				array(
					'taxonomy'   => WPVC_VOTES_TAXONOMY,
					'hide_empty' => false,
				)
			);
			unset( $term_meta['category_name'] );
			unset( $term_meta['category_name_error'] );
			unset( $term_meta['datepicker_only'] );
			$updated_terms = array();
			foreach ( $terms as $key => $term ) {
				$updated_terms[ $key ]['term_id']       = $term->term_id;
				$updated_terms[ $key ]['category_name'] = $term->name;
				$updated_terms[ $key ]['slug']          = $term->slug;
				$updated_terms[ $key ]['count']         = $term->count;
				$setting                                = self::wpvc_settings_page_json();
				foreach ( $term_meta as $innerKey => $value ) {
					if ( $innerKey == 'color' ) {
						$color = get_term_meta( $term->term_id, 'color', true );
						if ( $color == null ) {
							if ( is_array( $setting ) ) {
								$color = $setting['settings']['color'];
							}
						}
						$updated_terms[ $key ]['color'] = $color;
					} elseif ( $innerKey == 'style' ) {
						$style = get_term_meta( $term->term_id, 'style', true );
						if ( $style == null ) {
							if ( is_array( $setting ) ) {
								$style = $setting['settings']['style'];
							}
						}
						$updated_terms[ $key ]['style'] = $style;
					} else {
						$updated_terms[ $key ][ $innerKey ] = get_term_meta( $term->term_id, $innerKey, true );
					}
				}
			}
			return $updated_terms;
		}
		/**
		 *  Insert Category values.
		 *
		 * @param  array $result Insert Values.
		 */
		public static function wpvc_category_insert( $result ) {
			$term_name = $result['category_name'];
			unset( $result['category_name'] );
			unset( $result['category_name_error'] );
			unset( $result['datepicker_only'] );
			$inserted_term = wp_insert_term( $term_name, WPVC_VOTES_TAXONOMY );
			if ( ! is_wp_error( $inserted_term ) ) {
				$term_id = $inserted_term['term_id'];
				// Update each elements to term meta.
				foreach ( $result as $key => $value ) {
					if ( $key == 'contest_rules' ) {
						$values = format_to_edit( $value, true );
						update_term_meta( $term_id, $key, $values );
					} else {
						update_term_meta( $term_id, $key, $value );
					}
				}
			} else {
				return 0;
			}
		}
		/**
		 *  Edit Category values.
		 *
		 * @param  array $result Insert Values.
		 * @param  int   $term_id Term ID.
		 */
		public static function wpvc_category_edit( $result, $term_id ) {
			$term_name = $result['category_name'];
			$slug      = $result['slug'];
			unset( $result['category_name'] );
			unset( $result['category_name_error'] );
			unset( $result['category_slug_error'] );
			unset( $result['datepicker_only'] );
			$update = wp_update_term(
				$term_id,
				WPVC_VOTES_TAXONOMY,
				array(
					'name' => $term_name,
					'slug' => $slug,
				)
			);

			if ( ! is_wp_error( $update ) ) {
				// Update each elements to term meta.
				foreach ( $result as $key => $value ) {
					if ( $key == 'contest_rules' ) {
						$values = format_to_edit( $value, true );
						update_term_meta( $term_id, $key, $values );
					} else {
						update_term_meta( $term_id, $key, $value );
					}
				}
			} elseif ( isset( $update->errors['duplicate_term_slug'] ) ) {
				return 1; // Duplicate Slug Issue.
			} else {
				return 0;
			}
		}

		/**
		 *  Get Translation Strings.
		 */
		public static function wpvc_get_translations() {

			$default  = file_get_contents( WPVC_VIEWS . 'translation.json' );
			$lang     = get_locale();
			$settings = array(
				'language'     => $lang,
				'translations' => json_decode( $default, true ),
			);

			return $settings;
		}
	}
} else {
	die( '<h2>' . esc_html( __( 'Failed to load Voting Settings model' ) ) . '</h2>' );
}

return new Wpvc_Settings_Model();
