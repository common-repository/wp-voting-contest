<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
if ( ! class_exists( 'Wpvc_Rest_Actions_Controller' ) ) {
	/**
	 * Admin Rest Controller.
	 */
	class Wpvc_Rest_Actions_Controller {

		/**
		 * Get Settings.
		 */
		public static function wpvc_callback_plugin_settings_page_data() {
			if ( ( isset( $_SERVER['HTTP_AUTHORIZE_WPVC'] ) && sanitize_text_field( wp_unslash( $_SERVER['HTTP_AUTHORIZE_WPVC'] ) ) != '' ) && strtolower( sanitize_text_field( wp_unslash( $_SERVER['HTTP_AUTHORIZE_WPVC'] ) ) ) == sanitize_text_field( wp_unslash( $_COOKIE['wpvc_freevoting_authorize'] ) ) && sanitize_text_field( wp_unslash( $_SERVER['HTTP_AUTHORIZE_WPVC_REQUEST'] ) ) == 'xmlhttprequest' ) {
				$response = Wpvc_Settings_Model::wpvc_settings_page_json();
				return new WP_REST_Response( $response, 200 );
			} else {
				die( wp_json_encode( array( 'no_cheating' => 'You have no permission to access Voting contest' ) ) );
			}
		}
		/**
		 * Save Settings.
		 *
		 * @param mixed $request_data Requested data.
		 */
		public static function wpvc_callback_save_settings( $request_data ) {
			if ( ( isset( $_SERVER['HTTP_AUTHORIZE_WPVC'] ) && sanitize_text_field( wp_unslash( $_SERVER['HTTP_AUTHORIZE_WPVC'] ) ) != '' ) && strtolower( sanitize_text_field( wp_unslash( $_SERVER['HTTP_AUTHORIZE_WPVC'] ) ) ) == sanitize_text_field( wp_unslash( $_COOKIE['wpvc_freevoting_authorize'] ) ) && sanitize_text_field( wp_unslash( $_SERVER['HTTP_AUTHORIZE_WPVC_REQUEST'] ) ) == 'xmlhttprequest' ) {
				global $wpdb;
				$result = $request_data->get_params();
				update_option( WPVC_VOTES_SETTINGS, $result );
				return new WP_REST_Response( $result, 200 );
			} else {
				die( wp_json_encode( array( 'no_cheating' => 'You have no permission to access Voting contest' ) ) );
			}
		}
		/**
		 * Get Vote Logs.
		 *
		 * @param mixed $request_data Requested data.
		 */
		public static function wpvc_callback_plugin_voting_logs( $request_data ) {
			if ( ( isset( $_SERVER['HTTP_AUTHORIZE_WPVC'] ) && sanitize_text_field( wp_unslash( $_SERVER['HTTP_AUTHORIZE_WPVC'] ) ) != '' ) && strtolower( sanitize_text_field( wp_unslash( $_SERVER['HTTP_AUTHORIZE_WPVC'] ) ) ) == sanitize_text_field( wp_unslash( $_COOKIE['wpvc_freevoting_authorize'] ) ) && sanitize_text_field( wp_unslash( $_SERVER['HTTP_AUTHORIZE_WPVC_REQUEST'] ) ) == 'xmlhttprequest' ) {
				$param       = $request_data->get_params();
				$paged       = $param['paged'];
				$total_count = Wpvc_Settings_Model::wpvc_get_total_voting_count();
				$voting      = Wpvc_Settings_Model::wpvc_votings_get_paged( $paged );

				$vote_return = array();
				if ( ! empty( $voting ) ) {
					foreach ( $voting as $key => $vote_log ) {
						if ( $vote_log['email_always'] != '' ) {
							$user                 = get_user_by( 'email', $vote_log['email_always'] );
							$vote_log['username'] = $user->display_name;
						} else {
							$vote_log['username'] = '';
						}
						$vote_return[ $key ] = $vote_log;
					}
				}
				$return_val = array(
					'voting_logs' => $vote_return,
					'total_count' => $total_count,
					'paged'       => $paged,
				);
				return new WP_REST_Response( $return_val, 200 );
			} else {
				die( wp_json_encode( array( 'no_cheating' => 'You have no permission to access Voting contest' ) ) );
			}
		}
		/**
		 * Delete Vote Logs.
		 *
		 * @param mixed $request_data Requested data.
		 */
		public static function wpvc_callback_delete_voting_logs( $request_data ) {
			if ( ( isset( $_SERVER['HTTP_AUTHORIZE_WPVC'] ) && sanitize_text_field( wp_unslash( $_SERVER['HTTP_AUTHORIZE_WPVC'] ) ) != '' ) && strtolower( sanitize_text_field( wp_unslash( $_SERVER['HTTP_AUTHORIZE_WPVC'] ) ) ) == sanitize_text_field( wp_unslash( $_COOKIE['wpvc_freevoting_authorize'] ) ) && sanitize_text_field( wp_unslash( $_SERVER['HTTP_AUTHORIZE_WPVC_REQUEST'] ) ) == 'xmlhttprequest' ) {
				$param       = $request_data->get_params();
				$paged       = array_key_exists( 'paged', $param ) ? $param['paged'] : 0;
				$deleted     = Wpvc_Settings_Model::wpvc_delete_contestant_voting( $param['id'] );
				$total_count = Wpvc_Settings_Model::wpvc_get_total_voting_count();
				$voting      = Wpvc_Settings_Model::wpvc_votings_get_paged( $paged );

				$vote_return = array();
				if ( ! empty( $voting ) ) {
					foreach ( $voting as $key => $vote_log ) {
						if ( $vote_log['email_always'] != '' ) {
							$user                 = get_user_by( 'email', $vote_log['email_always'] );
							$vote_log['username'] = $user->display_name;
						} else {
							$vote_log['username'] = '';
						}
						$vote_return[ $key ] = $vote_log;
					}
				}
				$return_val = array(
					'voting_logs' => $vote_return,
					'total_count' => $total_count,
					'paged'       => $paged,
				);
				return new WP_REST_Response( $return_val, 200 );
			} else {
				die( wp_json_encode( array( 'no_cheating' => 'You have no permission to access Voting contest' ) ) );
			}
		}
		/**
		 * Delete Multiple Vote Logs.
		 *
		 * @param mixed $request_data Requested data.
		 */
		public static function wpvc_callback_delete_multiple_voting_logs( $request_data ) {
			if ( ( isset( $_SERVER['HTTP_AUTHORIZE_WPVC'] ) && sanitize_text_field( wp_unslash( $_SERVER['HTTP_AUTHORIZE_WPVC'] ) ) != '' ) && strtolower( sanitize_text_field( wp_unslash( $_SERVER['HTTP_AUTHORIZE_WPVC'] ) ) ) == sanitize_text_field( wp_unslash( $_COOKIE['wpvc_freevoting_authorize'] ) ) && sanitize_text_field( wp_unslash( $_SERVER['HTTP_AUTHORIZE_WPVC_REQUEST'] ) ) == 'xmlhttprequest' ) {
				$param       = $request_data->get_params();
				$paged       = array_key_exists( 'paged', $param ) ? $param['paged'] : 0;
				$deleted     = Wpvc_Settings_Model::wpvc_multiple_delete_contestant_voting( $param['id'] );
				$total_count = Wpvc_Settings_Model::wpvc_get_total_voting_count();
				$voting      = Wpvc_Settings_Model::wpvc_votings_get_paged( $paged );

				$vote_return = array();
				if ( ! empty( $voting ) ) {
					foreach ( $voting as $key => $vote_log ) {
						if ( $vote_log['email_always'] != '' ) {
							$user                 = get_user_by( 'email', $vote_log['email_always'] );
							$vote_log['username'] = $user->display_name;
						} else {
							$vote_log['username'] = '';
						}
						$vote_return[ $key ] = $vote_log;
					}
				}
				$return_val = array(
					'voting_logs' => $vote_return,
					'total_count' => $total_count,
					'paged'       => $paged,
				);
				return new WP_REST_Response( $return_val, 200 );
			} else {
				die( wp_json_encode( array( 'no_cheating' => 'You have no permission to access Voting contest' ) ) );
			}
		}
		/**
		 * Get Category.
		 *
		 * @param mixed $val Requested data.
		 */
		public static function wpvc_callback_plugin_category_data( $val = null ) {
			if ( ( isset( $_SERVER['HTTP_AUTHORIZE_WPVC'] ) && sanitize_text_field( wp_unslash( $_SERVER['HTTP_AUTHORIZE_WPVC'] ) ) != '' ) && strtolower( sanitize_text_field( wp_unslash( $_SERVER['HTTP_AUTHORIZE_WPVC'] ) ) ) == sanitize_text_field( wp_unslash( $_COOKIE['wpvc_freevoting_authorize'] ) ) && sanitize_text_field( wp_unslash( $_SERVER['HTTP_AUTHORIZE_WPVC_REQUEST'] ) ) == 'xmlhttprequest' ) {
				$new_taxonomy = Wpvc_Common_State_Controller::wpvc_new_taxonomy_state();
				$terms        = Wpvc_Settings_Model::wpvc_category_get_all_terms( $new_taxonomy );
				$return_val   = array(
					'taxonomy'          => $terms,
					'new_taxonomy'      => $new_taxonomy,
					'currentTerm'       => -1,
					'currentLayoutTerm' => -1,
					'error_slug'        => $val,
				);
				return new WP_REST_Response( $return_val, 200 );
			} else {
				die( wp_json_encode( array( 'no_cheating' => 'You have no permission to access Voting contest' ) ) );
			}
		}
		/**
		 * Category Update.
		 *
		 * @param mixed $request_data Requested data.
		 */
		public static function wpvc_callback_plugin_category_update( $request_data ) {
			if ( ( isset( $_SERVER['HTTP_AUTHORIZE_WPVC'] ) && sanitize_text_field( wp_unslash( $_SERVER['HTTP_AUTHORIZE_WPVC'] ) ) != '' ) && strtolower( sanitize_text_field( wp_unslash( $_SERVER['HTTP_AUTHORIZE_WPVC'] ) ) ) == sanitize_text_field( wp_unslash( $_COOKIE['wpvc_freevoting_authorize'] ) ) && sanitize_text_field( wp_unslash( $_SERVER['HTTP_AUTHORIZE_WPVC_REQUEST'] ) ) == 'xmlhttprequest' ) {
				$result = $request_data->get_params();
				if ( isset( $result['term_id'] ) ) { // Edit.
					$check_result = Wpvc_Settings_Model::wpvc_category_edit( $result, $result['term_id'] );
				} else {   // Add.
					$check_result = Wpvc_Settings_Model::wpvc_category_insert( $result );
				}
				if ( $check_result !== 0 ) {
					$return_val = self::wpvc_callback_plugin_category_data( $check_result );
					return $return_val;
				}
			} else {
				die( wp_json_encode( array( 'no_cheating' => 'You have no permission to access Voting contest' ) ) );
			}
		}
		/**
		 * Category delete.
		 *
		 * @param mixed $request_data Requested data.
		 */
		public static function wpvc_callback_plugin_category_delete( $request_data ) {
			if ( ( isset( $_SERVER['HTTP_AUTHORIZE_WPVC'] ) && sanitize_text_field( wp_unslash( $_SERVER['HTTP_AUTHORIZE_WPVC'] ) ) != '' ) && strtolower( sanitize_text_field( wp_unslash( $_SERVER['HTTP_AUTHORIZE_WPVC'] ) ) ) == sanitize_text_field( wp_unslash( $_COOKIE['wpvc_freevoting_authorize'] ) ) && sanitize_text_field( wp_unslash( $_SERVER['HTTP_AUTHORIZE_WPVC_REQUEST'] ) ) == 'xmlhttprequest' ) {
				$result   = $request_data->get_params();
				$term_ids = $result;
				foreach ( $term_ids as $term ) {
					wp_delete_term( $term, WPVC_VOTES_TAXONOMY );
				}
				$return_val = self::wpvc_callback_plugin_category_data();
				return $return_val;
			} else {
				die( wp_json_encode( array( 'no_cheating' => 'You have no permission to access Voting contest' ) ) );
			}
		}
		/**
		 * Migrate Data.
		 *
		 * @param mixed $request_data Requested data.
		 */
		public static function wpvc_callback_migrate_all_data( $request_data ) {
			if ( ( isset( $_SERVER['HTTP_AUTHORIZE_WPVC'] ) && sanitize_text_field( wp_unslash( $_SERVER['HTTP_AUTHORIZE_WPVC'] ) ) != '' ) && strtolower( sanitize_text_field( wp_unslash( $_SERVER['HTTP_AUTHORIZE_WPVC'] ) ) ) == sanitize_text_field( wp_unslash( $_COOKIE['wpvc_freevoting_authorize'] ) ) && sanitize_text_field( wp_unslash( $_SERVER['HTTP_AUTHORIZE_WPVC_REQUEST'] ) ) == 'xmlhttprequest' ) {
				$response = Wpvc_Migration_Model::wpvc_migrate_plugin_to_react();
				return $response;
			} else {
				die( wp_json_encode( array( 'no_cheating' => 'You have no permission to access Voting contest' ) ) );
			}
		}
		/**
		 * Get Custom Fields.
		 *
		 * @param mixed $request_data Requested data.
		 */
		public static function wpvc_callback_assign_custom( $request_data ) {
			if ( ( isset( $_SERVER['HTTP_AUTHORIZE_WPVC'] ) && sanitize_text_field( wp_unslash( $_SERVER['HTTP_AUTHORIZE_WPVC'] ) ) != '' ) && strtolower( sanitize_text_field( wp_unslash( $_SERVER['HTTP_AUTHORIZE_WPVC'] ) ) ) == sanitize_text_field( wp_unslash( $_COOKIE['wpvc_freevoting_authorize'] ) ) && sanitize_text_field( wp_unslash( $_SERVER['HTTP_AUTHORIZE_WPVC_REQUEST'] ) ) == 'xmlhttprequest' ) {
				global $wpdb;
				$result    = $request_data->get_params();
				$data      = $result['insertData'];
				$update_id = $result['id'];
				update_term_meta( $update_id, WPVC_VOTES_TAXONOMY_ASSIGN, $data );
				return new WP_REST_Response( $result, 200 );
			} else {
				die( wp_json_encode( array( 'no_cheating' => 'You have no permission to access Voting contest' ) ) );
			}
		}
		/**
		 * Assign Custom Fields.
		 *
		 * @param mixed $request_data Requested data.
		 */
		public static function wpvc_callback_get_assign_custom( $request_data ) {
			if ( ( isset( $_SERVER['HTTP_AUTHORIZE_WPVC'] ) && sanitize_text_field( wp_unslash( $_SERVER['HTTP_AUTHORIZE_WPVC'] ) ) != '' ) && strtolower( sanitize_text_field( wp_unslash( $_SERVER['HTTP_AUTHORIZE_WPVC'] ) ) ) == sanitize_text_field( wp_unslash( $_COOKIE['wpvc_freevoting_authorize'] ) ) && sanitize_text_field( wp_unslash( $_SERVER['HTTP_AUTHORIZE_WPVC_REQUEST'] ) ) == 'xmlhttprequest' ) {
				global $wpdb;
				$result     = $request_data->get_params();
				$cat_id     = $result['id'];
				$imgcontest = get_term_meta( $cat_id, 'imgcontest', true );
				$options    = get_term_meta( $cat_id, WPVC_VOTES_TAXONOMY_ASSIGN, true );
				if ( is_array( $options ) ) {
					foreach ( $options as $key => $opt ) {
						$get_original = Wpvc_Settings_Model::wpvc_custom_fields_get_original( $opt['id'] );
						if ( ! empty( $get_original ) ) {
							$options[ $key ]['question'] = $get_original['question'];
							$options[ $key ]['original'] = $get_original;
						}
					}
				}
				$selected_items = array( 'selected_items' => $options );
				return new WP_REST_Response( $selected_items, 200 );
			} else {
				die( wp_json_encode( array( 'no_cheating' => 'You have no permission to access Voting contest' ) ) );
			}
		}

		/**
		 * Callback Custom Fields.
		 */
		public static function wpvc_callback_plugin_custom_field() {
			if ( ( isset( $_SERVER['HTTP_AUTHORIZE_WPVC'] ) && sanitize_text_field( wp_unslash( $_SERVER['HTTP_AUTHORIZE_WPVC'] ) ) != '' ) && strtolower( sanitize_text_field( wp_unslash( $_SERVER['HTTP_AUTHORIZE_WPVC'] ) ) ) == sanitize_text_field( wp_unslash( $_COOKIE['wpvc_freevoting_authorize'] ) ) && sanitize_text_field( wp_unslash( $_SERVER['HTTP_AUTHORIZE_WPVC_REQUEST'] ) ) == 'xmlhttprequest' ) {
				$custom    = Wpvc_Settings_Model::wpvc_custom_fields_json();
				$new_field = Wpvc_Common_State_Controller::wpvc_new_custom_fields_state();
				$terms     = get_terms(
					array(
						'taxonomy'   => WPVC_VOTES_TAXONOMY,
						'hide_empty' => false,
					)
				);
				if ( ! empty( $terms ) ) {
					foreach ( $terms as $key => $term ) {
						$imgcontest          = get_term_meta( $term->term_id, 'imgcontest', true );
						$term->category_type = $imgcontest;

						if ( $imgcontest == 'music' ) {
							$musicfileenable    = get_term_meta( $term->term_id, 'musicfileenable', true );
							$term->music_enable = $musicfileenable;
						} elseif ( $imgcontest == 'video' ) {
							$video_extension = get_option( '_ow_video_extension' );
							if ( $video_extension == 1 ) {
								$term->music_enable = 'on';
							} else {
								$term->music_enable = 'off';
							}
						}
						$array = json_decode( wp_json_encode( $term ), true );
						array_merge( $array, array( 'category_type' => $imgcontest ) );
					}
				}
				$return_val = array(
					'custom'          => $custom,
					'new_customfield' => $new_field,
					'insert'          => 0,
					'deleted'         => 0,
					'updated'         => 0,
					'taxonomy'        => $terms,
				);
				return new WP_REST_Response( $return_val, 200 );
			} else {
				die( wp_json_encode( array( 'no_cheating' => 'You have no permission to access Voting contest' ) ) );
			}
		}
		/**
		 * Update Custom Fields.
		 *
		 * @param mixed $request_data Requested data.
		 */
		public static function wpvc_callback_update_customfield( $request_data ) {
			if ( ( isset( $_SERVER['HTTP_AUTHORIZE_WPVC'] ) && sanitize_text_field( wp_unslash( $_SERVER['HTTP_AUTHORIZE_WPVC'] ) ) != '' ) && strtolower( sanitize_text_field( wp_unslash( $_SERVER['HTTP_AUTHORIZE_WPVC'] ) ) ) == sanitize_text_field( wp_unslash( $_COOKIE['wpvc_freevoting_authorize'] ) ) && sanitize_text_field( wp_unslash( $_SERVER['HTTP_AUTHORIZE_WPVC_REQUEST'] ) ) == 'xmlhttprequest' ) {
				$param     = $request_data->get_params();
				$data      = $param['insertData'];
				$update_id = $param['id'];
				$added     = Wpvc_Settings_Model::wpvc_update_contestant_custom_field( $update_id, $data );
				$terms     = get_terms(
					array(
						'taxonomy'   => WPVC_VOTES_TAXONOMY,
						'hide_empty' => false,
					)
				);
				if ( ! empty( $terms ) ) {
					foreach ( $terms as $key => $term ) {
						$imgcontest          = get_term_meta( $term->term_id, 'imgcontest', true );
						$term->category_type = $imgcontest;
						$musicfileenable     = get_term_meta( $term->term_id, 'musicfileenable', true );
						$term->music_enable  = $musicfileenable;
						$array               = json_decode( wp_json_encode( $term ), true );
						array_merge( $array, array( 'category_type' => $imgcontest ) );
					}
				}

				// Get values again.
				$custom     = Wpvc_Settings_Model::wpvc_custom_fields_json();
				$new_field  = Wpvc_Common_State_Controller::wpvc_new_custom_fields_state();
				$return_val = array(
					'custom'          => $custom,
					'new_customfield' => $new_field,
					'insert'          => 0,
					'deleted'         => 0,
					'updated'         => $added,
					'taxonomy'        => $terms,
				);
				return new WP_REST_Response( $return_val, 200 );
			} else {
				die( wp_json_encode( array( 'no_cheating' => 'You have no permission to access Voting contest' ) ) );
			}
		}

		/**
		 * Get Translation Strings.
		 */
		public static function wpvc_callback_site_translations_data() {
			if ( ( isset( $_SERVER['HTTP_AUTHORIZE_WPVC'] ) && sanitize_text_field( wp_unslash( $_SERVER['HTTP_AUTHORIZE_WPVC'] ) ) != '' ) && strtolower( sanitize_text_field( wp_unslash( $_SERVER['HTTP_AUTHORIZE_WPVC'] ) ) ) == sanitize_text_field( wp_unslash( $_COOKIE['wpvc_freevoting_authorize'] ) ) && sanitize_text_field( wp_unslash( $_SERVER['HTTP_AUTHORIZE_WPVC_REQUEST'] ) ) == 'xmlhttprequest' ) {
				$translations = Wpvc_Settings_Model::wpvc_get_translations();
				$return_val   = array( 'site_translations' => $translations );
				return new WP_REST_Response( $return_val, 200 );
			} else {
				die( wp_json_encode( array( 'no_cheating' => 'You have no permission to access Voting contest' ) ) );
			}
		}

		/**
		 * Save license
		 *
		 * @param mixed $request_data Requested data.
		 */
		public static function wpvc_callback_plugin_license_update( $request_data ) {
			$param = $request_data->get_params();

			if ( isset( $param['license_key'] ) ) {
				$license = trim( $param['license_key'] );

				// Saving the Entered License Data
				update_option( 'wp_voting_software_license_key', $license );

				$api_params = array(
					'edd_action' => 'activate_license',
					'license'    => $license,
					'item_id'    => WPVC_WP_VOTING_SL_PRODUCT_ID,
					'url'        => home_url(),
				);

				$message = check_license_key( $api_params, WPVC_WP_VOTING_SL_STORE_API_URL, WPVC_WP_VOTING_SL_PRODUCT_NAME );

				update_option( 'wp_voting_software_license_status', $message );

				if ( $message == 'valid' ) {
					$return_val = array( 'license_key' => $message );
				} else {
					$return_val = array( 'license_key' => 'invalid' );
				}
				return new WP_REST_Response( $return_val, 200 );
			}
		}
	}
} else {
	die( '<h2>' . esc_html_e( 'Failed to load Voting Rest Actions Controller', 'voting-contest' ) . '</h2>' );
}


return new Wpvc_Rest_Actions_Controller();
