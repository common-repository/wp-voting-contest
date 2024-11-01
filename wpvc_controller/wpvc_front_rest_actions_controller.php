<?php
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}
if (!class_exists('Wpvc_Front_Rest_Actions_Controller')) {
	/**
	 * Front Rest Controller.
	 */
	class Wpvc_Front_Rest_Actions_Controller
	{
		/**
		 * Save Votes Rest.
		 *
		 * @param mixed $request_data Requested Data.
		 */
		public static function wpvc_callback_save_votes($request_data)
		{
			if ((isset($_SERVER['HTTP_AUTHORIZE_WPVC']) && sanitize_text_field(wp_unslash($_SERVER['HTTP_AUTHORIZE_WPVC'])) != '') && strtolower(sanitize_text_field(wp_unslash($_SERVER['HTTP_AUTHORIZE_WPVC']))) == sanitize_text_field(wp_unslash($_COOKIE['wpvc_freevoting_authorize'])) && sanitize_text_field(wp_unslash($_SERVER['HTTP_AUTHORIZE_WPVC_REQUEST'])) == 'xmlhttprequest') {
				$param       = $request_data->get_params();
				$post_id     = $param['postid'];
				$category_id = $param['category_id'];
				$votesetting = $param['votesetting'];
				$post_index  = $param['postind'];
				$email       = $param['email'];
				if ($post_id != '' && $category_id != '') {
					$response = Wpvc_Voting_Model::wpvc_save_votes($post_id, $category_id, $votesetting, $post_index, null, false, '', $email);
				}
				return new WP_REST_Response($response, 200);
			} else {
				die(wp_json_encode(array('no_cheating' => 'You have no permission to access Voting contest')));
			}
		}
		/**
		 * Send Email.
		 *
		 * @param mixed $request_data Requested Data.
		 */
		public static function wpvc_callback_send_email($request_data)
		{
			if ((isset($_SERVER['HTTP_AUTHORIZE_WPVC']) && sanitize_text_field(wp_unslash($_SERVER['HTTP_AUTHORIZE_WPVC'])) != '') && strtolower(sanitize_text_field(wp_unslash($_SERVER['HTTP_AUTHORIZE_WPVC']))) == sanitize_text_field(wp_unslash($_COOKIE['wpvc_freevoting_authorize'])) && sanitize_text_field(wp_unslash($_SERVER['HTTP_AUTHORIZE_WPVC_REQUEST'])) == 'xmlhttprequest') {
				$param      = $request_data->get_params();
				$post_id    = $param['post_id'];
				$userdata   = $param['userdata'];
				$insertdata = $param['insertdata'];
				$gateway    = $param['gateway'];
				$email_ctrl = new Wpvc_Email_Controller();
				$email_ctrl->wpvc_contestant_check_email($post_id, $userdata, $insertdata, $gateway);
				return new WP_REST_Response(array('new_post_id' => $post_id), 200);
			} else {
				die(wp_json_encode(array('no_cheating' => 'You have no permission to access Voting contest')));
			}
		}

		/**
		 * User Logon.
		 *
		 * @param mixed $request_data Requested Data.
		 */
		public static function wpvc_callback_user_logon($request_data)
		{

			if ((isset($_SERVER['HTTP_AUTHORIZE_WPVC']) && sanitize_text_field(wp_unslash($_SERVER['HTTP_AUTHORIZE_WPVC'])) != '') && strtolower(sanitize_text_field(wp_unslash($_SERVER['HTTP_AUTHORIZE_WPVC']))) == sanitize_text_field(wp_unslash($_COOKIE['wpvc_freevoting_authorize'])) && sanitize_text_field(wp_unslash($_SERVER['HTTP_AUTHORIZE_WPVC_REQUEST'])) == 'xmlhttprequest') {
				$param = $request_data->get_params();
				$logon = $param['login'];

				if (!empty($logon)) {
					$creds['user_login']    = $logon['username'];
					$creds['user_password'] = $logon['password'];
					$creds['remember']      = (array_key_exists('remember_me', $logon) && $logon['remember_me'] == 'on') ? true : false;
					$user                   = wp_signon($creds, false);
					$response               = array('user_login' => $user);
					return new WP_REST_Response($response, 200);
				}
				return;
			} else {
				die(wp_json_encode(array('no_cheating' => 'You have no permission to access Voting contest')));
			}
		}
		/**
		 * Reset Password.
		 *
		 * @param mixed $request_data Requested Data.
		 */
		public static function wpvc_callback_reset_password($request_data)
		{
			if ((isset($_SERVER['HTTP_AUTHORIZE_WPVC']) && sanitize_text_field(wp_unslash($_SERVER['HTTP_AUTHORIZE_WPVC'])) != '') && strtolower(sanitize_text_field(wp_unslash($_SERVER['HTTP_AUTHORIZE_WPVC']))) == sanitize_text_field(wp_unslash($_COOKIE['wpvc_freevoting_authorize'])) && sanitize_text_field(wp_unslash($_SERVER['HTTP_AUTHORIZE_WPVC_REQUEST'])) == 'xmlhttprequest') {
				$param      = $request_data->get_params();
				$user_login = sanitize_text_field($param['login']);

				if (!empty($user_login)) {
					global $wpdb, $current_site;

					if (empty($user_login)) {
						return false;
					} elseif (strpos($user_login, '@')) {
						$user_data = get_user_by('email', trim($user_login));
						if (empty($user_data)) {
							return false;
						}
					} else {
						$login     = trim($user_login);
						$user_data = get_user_by('login', $login);
					}

					if (!$user_data) {
						$response = array('forgot_error' => 'User Does not exists');
						return new WP_REST_Response($response, 200);
					} else {
						$user_login = $user_data->user_login;
						do_action('retrieve_password', $user_login);
						$key = wp_generate_password(20, false);
						do_action('retrieve_password_key', $user_login, $key);
						if (empty($wp_hasher)) {
							require_once ABSPATH . 'wp-includes/class-phpass.php';
							$wp_hasher = new PasswordHash(8, true);
						}
						$hashed = time() . ':' . $wp_hasher->HashPassword($key);
						$wpdb->update($wpdb->users, array('user_activation_key' => $hashed), array('user_login' => $user_login));
						$email_ctrl = new Wpvc_Email_Controller();
						$email_res  = $email_ctrl->wpvc_send_reset_password($user_data, $key);
						if ($email_res == true) {
							$response = array('forgot_success' => 'Link for password reset has been emailed to you. Please check your email');
						} else {
							$response = array('forgot_error' => 'The e-mail could not be sent');
						}

						return new WP_REST_Response($response, 200);
					}
				}
				return;
			} else {
				die(wp_json_encode(array('no_cheating' => 'You have no permission to access Voting contest')));
			}
		}
		/**
		 * Get Register.
		 */
		public static function wpvc_callback_user_get_register()
		{
			if ((isset($_SERVER['HTTP_AUTHORIZE_WPVC']) && sanitize_text_field(wp_unslash($_SERVER['HTTP_AUTHORIZE_WPVC'])) != '') && strtolower(sanitize_text_field(wp_unslash($_SERVER['HTTP_AUTHORIZE_WPVC']))) == sanitize_text_field(wp_unslash($_COOKIE['wpvc_freevoting_authorize'])) && sanitize_text_field(wp_unslash($_SERVER['HTTP_AUTHORIZE_WPVC_REQUEST'])) == 'xmlhttprequest') {
				$reg_form = array();
				if (!empty($reg_form)) {
					$new_reg_array = array();
					$i             = 0;
					foreach ($reg_form as $registerform) {
						$react_values        = json_decode($registerform['react_val']);
						$comma_sep_values    = explode(',', $registerform['response']);
						$registerform        = array_merge(
							$registerform,
							array(
								'react_values' => $react_values,
								'drop_values'  => $comma_sep_values,
							)
						);
						$new_reg_array[$i] = maybe_unserialize($registerform);
						$i++;
					}
					$response = array('register_form' => $new_reg_array);
					return new WP_REST_Response($response, 200);
				} else {
					return new WP_REST_Response('', 200);
				}
			} else {
				die(wp_json_encode(array('no_cheating' => 'You have no permission to access Voting contest')));
			}
		}
		/**
		 * Reset Password.
		 *
		 * @param mixed $request_data Requested Data.
		 */
		public static function wpvc_callback_user_register($request_data)
		{
			if ((isset($_SERVER['HTTP_AUTHORIZE_WPVC']) && sanitize_text_field(wp_unslash($_SERVER['HTTP_AUTHORIZE_WPVC'])) != '') && strtolower(sanitize_text_field(wp_unslash($_SERVER['HTTP_AUTHORIZE_WPVC']))) == sanitize_text_field(wp_unslash($_COOKIE['wpvc_freevoting_authorize'])) && sanitize_text_field(wp_unslash($_SERVER['HTTP_AUTHORIZE_WPVC_REQUEST'])) == 'xmlhttprequest') {
				$param            = $request_data->get_params();
				$register         = $param['register'];
				$zn_error_message = array();
				if (!empty($register)) {

					if (username_exists($register['username'])) {
						$zn_error_message['error'] = __('The username already exists', 'voting-contest');
					}
					if (email_exists($register['email'])) {
						$zn_error_message['error'] = __('This email address has already been used', 'voting-contest');
					}

					$votes_settings = get_option(WPVC_VOTES_SETTINGS);

					if (empty($zn_error_message)) {
						$user_data = array(
							'ID'           => '',
							'user_pass'    => $register['password'],
							'user_login'   => $register['username'],
							'display_name' => $register['username'],
							'user_email'   => $register['email'],
							'role'         => get_option('default_role'), // Use default role or another role, e.g. 'editor'
						);
						$user_id   = wp_insert_user($user_data);

						update_user_meta($user_id, WPVC_VOTES_USER_META, $register);

						$creds['user_login']    = $register['username'];
						$creds['user_password'] = $register['password'];
						$user                   = wp_signon($creds, false);
						$response               = array('user_registration' => $user);
						return new WP_REST_Response($response, 200);
					} else {
						$response = array('user_registration' => $zn_error_message);
						return new WP_REST_Response($response, 200);
					}
				}
				return;
			} else {
				die(wp_json_encode(array('no_cheating' => 'You have no permission to access Voting contest')));
			}
		}

		/**
		 * Submit Entry.
		 *
		 * @param mixed $request_data Requested Data.
		 */
		public static function wpvc_callback_submit_entry($request_data)
		{
			if ((isset($_SERVER['HTTP_AUTHORIZE_WPVC']) && sanitize_text_field(wp_unslash($_SERVER['HTTP_AUTHORIZE_WPVC'])) != '') && strtolower(sanitize_text_field(wp_unslash($_SERVER['HTTP_AUTHORIZE_WPVC']))) == sanitize_text_field(wp_unslash($_COOKIE['wpvc_freevoting_authorize'])) && sanitize_text_field(wp_unslash($_SERVER['HTTP_AUTHORIZE_WPVC_REQUEST'])) == 'xmlhttprequest') {
				$param             = $request_data->get_params();
				$category_id       = $param['category_id'];
				$insertdata        = $param['insertData'];
				$userdata          = $param['userdata'];
				$post_status       = $param['post_status'];
				$get_file_sys_name = array();
				if (!empty($insertdata)) {
					$getkey_values = array();
					foreach ($insertdata as $data => $key) {
						array_push($getkey_values, $data);
					}
					$get_file_sys_name = Wpvc_Shortcode_Model::wpvc_custom_fields_files($getkey_values);
				}

				if ($category_id != '') {
					$post_id = Wpvc_Shortcode_Model::wpvc_insert_contestants($category_id, $insertdata, $userdata, $post_status);
				} else {
					$post_id = '';
				}

				$response = array(
					'new_post_id'   => $post_id,
					'file_sys_name' => $get_file_sys_name,
				);
				return new WP_REST_Response($response, 200);
			} else {
				die(wp_json_encode(array('no_cheating' => 'You have no permission to access Voting contest')));
			}
		}

		/**
		 * Upload Files.
		 *
		 * @param mixed $_POST posted file.
		 */
		public static function wpvc_upload_files()
		{
			if ($_SERVER['HTTP_AUTHORIZE_WPVC'] != '' && strtolower($_SERVER['HTTP_AUTHORIZE_WPVC']) == $_COOKIE['wpvc_freevoting_authorize'] && $_SERVER['HTTP_AUTHORIZE_WPVC_REQUEST'] == 'xmlhttprequest') {

				require_once(ABSPATH . 'wp-admin/includes/image.php');
				require_once(ABSPATH . 'wp-admin/includes/file.php');
				require_once(ABSPATH . 'wp-admin/includes/media.php');

				try {
					$post_id  = $_POST['post_id'];

					$system_name  = $_POST['system_name'];
					$filename = $_FILES["file"]["name"];

					$absolutePath = wp_get_upload_dir()['path'] . '/' . $filename;

					$wp_filetype = wp_check_filetype($filename, null);

					$attachment = array(
						'post_mime_type' => $wp_filetype['type'],
						'post_title' => sanitize_file_name($filename),
						'post_content' => '',
						'post_status' => 'inherit'
					);
					$attach_id = media_handle_upload('file', $post_id, $attachment);
					if (is_wp_error($attach_id)) {
						// There was an error uploading the image.
						$error_string = $attach_id->get_error_message();
						$response = array('success' => false, 'error_message' => $error_string);
						return new WP_REST_Response($response, 200);
					}
				} catch (Exception $e) {
					$response = array('success' => false, 'error_message' => $e->getMessage());
					return new WP_REST_Response($response, 200);
				}

				// $attach_id = wp_insert_attachment($attachment, $absolutePath, $post_id);

				if ($system_name === 'contestant-ow_video_upload_url') {
					$value = wp_get_attachment_url($attach_id);
					update_post_meta($post_id, '_ow_video_upload_attachment', $attach_id);
					update_post_meta($post_id, 'contestant-ow_video_url', $value);
					update_post_meta($post_id, '_ow_video_upload_url', $value);
				}

				if ($system_name === 'contestant-ow_music_url') {
					$value = wp_get_attachment_url($attach_id);
					update_post_meta($post_id, '_ow_music_upload_attachment', $attach_id);
					update_post_meta($post_id, '_ow_music_upload_url', htmlentities($value, ENT_QUOTES));
				}

				require_once(ABSPATH . 'wp-admin/includes/image.php');
				$attach_data = wp_generate_attachment_metadata($attach_id, $absolutePath);
				$res1   = wp_update_attachment_metadata($attach_id, $attach_data);


				if ($system_name === 'contestant-image') {
					$res2   = set_post_thumbnail($post_id, $attach_id);
				} else {
					$value = wp_get_attachment_url($attach_id);
					$custom_attachment = array(
						'file'  => $absolutePath,
						'url'   => $value,
						'type'  => $wp_filetype['type'],
						'error' => false,
					);

					update_post_meta($post_id, 'ow_custom_attachment_' . $system_name, $custom_attachment);
				}

				$response = array('success' => true, 'system_name' => $system_name);
				return new WP_REST_Response($response, 200);
			}
		}

		/**
		 * Submit Entry Message.
		 */
		public static function wpvc_get_sucess_msg_submit_entry()
		{
			$settings = get_option(WPVC_VOTES_SETTINGS);
			if (is_array($settings)) {
				$auto_arrove = $settings['contest']['vote_publishing_type'];
				if ($auto_arrove == 'on') {
					return 'approve_entry';
				} else {
					return 'not_approved';
				}
			}
		}
		/**
		 * Show all contestants.
		 *
		 * @param mixed $request_data Requested Data.
		 */
		public static function wpvc_callback_showallcontestants($request_data)
		{

			$param        = $request_data->get_params();
			$category_id  = array_key_exists('id', $param) ? $param['id'] : '';
			$return_alert = self::wpvc_get_sucess_msg_submit_entry();
			if ($category_id != '') {
				$postID = array_key_exists('postID', $param) ? $param['postID'] : null;
				// Admin end EditContestant  Send postID.
				$custom_fields = Wpvc_Front_Rest_Actions_Controller::wpvc_get_custom_fields($category_id, $postID);
				$get_settings  = Wpvc_Shortcode_Model::wpvc_settings_page_json($category_id);

				$selcategory_options = get_term_meta($category_id, '', true);
				$align_category      = array();
				if (is_array($selcategory_options)) {
					foreach ($selcategory_options as $key => $val) {
						if ($key == 'contest_rules') {
							$align_category[$key] = format_to_edit($val[0], true);
						} else {
							$align_category[$key] = maybe_unserialize($val[0]);
						}
					}
					$align_category['id'] = $category_id;
				}

				$respon   = array(
					'settings'           => $get_settings,
					'search_text'        => '',
					'paginate_value'     => '',
					'selcategoryoptions' => apply_filters('cat_extension_update_values', $align_category, $category_id),
					'ret_alert'          => $return_alert,
				);
				$response = array_merge($respon, $custom_fields);
				return new WP_REST_Response($response, 200);
			} else {
				$custom_fields = Wpvc_Front_Rest_Actions_Controller::wpvc_get_custom_fields();
				$get_settings  = Wpvc_Shortcode_Model::wpvc_settings_page_json();

				$new_taxonomy = Wpvc_Common_State_Controller::wpvc_new_taxonomy_state();
				$terms        = Wpvc_Settings_Model::wpvc_category_get_all_terms($new_taxonomy);

				$respon   = array(
					'settings'           => $get_settings,
					'taxonomy'           => $terms,
					'new_taxonomy'       => $new_taxonomy,
					'category_term'      => 0,
					'sort_order'         => 0,
					'search_text'        => '',
					'paginate_value'     => '',
					'allpaginateval'     => '',
					'selcategoryoptions' => null,
					'ret_alert'          => $return_alert,
				);
				$response = array_merge($respon, $custom_fields);
				return new WP_REST_Response($response, 200);
			}
		}
		/**
		 * Get Custom Fields.
		 *
		 * @param int  $category_id Term ID.
		 * @param int  $postID Post ID.
		 * @param bool $admin Admin or not .
		 */
		public static function wpvc_get_custom_fields($category_id = null, $postID = null, $admin = false)
		{
			$wpvc_video_extension = get_option('_ow_video_extension');
			$imgcontest           = '';

			$contestant_form = array();
			$create_custom   = array();
			if ($category_id != '') {
				$category_options = get_term_meta($category_id, 'contest_category_assign_custom', true);
				$imgcontest       = get_term_meta($category_id, 'imgcontest', true);
				$musicfileenable  = get_term_meta($category_id, 'musicfileenable', true);

				$custom_fields   = maybe_unserialize($category_options);

				$settings = Wpvc_Shortcode_Model::wpvc_settings_page_json($category_id);
				if (is_array($custom_fields)) {
					$i = 0;
					foreach ($custom_fields as $custom) {
						$submitentry_form = $custom['system_name'];

						// get original custom fields from the dragged one.
						$getcustom = Wpvc_Shortcode_Model::wpvc_custom_fields_by_id($custom['id']);
						if (!empty($getcustom)) {
							// Sending the original form.
							$react_values        = json_decode($getcustom['react_val']);
							$comma_sep_values    = explode(',', $getcustom['response']);
							$getcustom           = array_merge(
								$getcustom,
								array(
									'react_values' => $react_values,
									'drop_values'  => $comma_sep_values,
								)
							);
							$create_custom[$i] = maybe_unserialize($getcustom);
							// To save the values of form on redux.
							$contestant_form[$submitentry_form] = '';
						}
						$i++;
					}
				} else {

					// If no fields assigned get default fields.
					$getcustom     = Wpvc_Shortcode_Model::wpvc_custom_fields_by_contest($imgcontest, $musicfileenable);
					$create_custom = maybe_unserialize($getcustom);
					if (is_array($create_custom)) {
						$keys = array();
						foreach ($create_custom as $key => $custom) {

							if ($custom['system_name'] == 'contestant-ow_video_upload_url') {
								if ($wpvc_video_extension != 1) {
									$keys[] = $key;
									continue;
								}
							}

							// hide url from user and admin edit page.
							if ($custom['system_name'] == 'contestant-ow_video_url' && $wpvc_video_extension == 1) {
								$admin_edit  = $settings['wpvc_videoextension_settings']['ow_enable_video_url'] == 'on' ? true : false;
								$current_url = wp_get_referer();
								if (strpos($current_url, 'action=edit') !== false) {
									if (($postID != null && !$admin_edit) || ($postID == null && !$admin && !$admin_edit)) {
										$keys[] = $key;
										continue;
									}
								}
							}

							$submitentry_form                     = $custom['system_name'];
							$contestant_form[$submitentry_form] = '';
						}

						foreach ($keys as $key) {
							array_splice($create_custom, $key, 1);
						}
					}
				}
			}

			if (!empty($create_custom)) {
				$create_custom = array_values($create_custom);
			}

			// Admin end EditContestant - Get Values assigned for the custom fields.
			if ($postID != null) {
				// Splitting the file array and all array values.
				$allValues = Wpvc_Front_Rest_Actions_Controller::wpvc_get_custom_fields_values($category_id, $postID);
				wp_set_post_terms($postID, $category_id, WPVC_VOTES_TAXONOMY);
				$contestant_form  = $allValues['custom_values'];
				$newUploadedFiles = $allValues['newUploadedFiles'];

				$return_array = array(
					'contestant_form'  => $contestant_form,
					'custom_field'     => $create_custom,
					'newUploadedFiles' => $newUploadedFiles,
				);
			} else {
				$return_array = array(
					'contestant_form' => $contestant_form,
					'custom_field'    => $create_custom,
				);
				$return_array = apply_filters('cat_extension_payment_entry', $return_array);
			}
			return $return_array;
		}
		/**
		 * Get Custom Fields Values.
		 *
		 * @param int $category_id Term ID.
		 * @param int $postID Post ID.
		 */
		public static function wpvc_get_custom_fields_values($termid, $postID)
		{
			// Get Custom_fields.
			$get_custom_fields = Wpvc_Front_Rest_Actions_Controller::wpvc_get_custom_fields($termid, null, true);
			$get_post_metas    = get_post_meta($postID, WPVC_VOTES_POST, true);
			if (!empty($get_custom_fields)) {
				$custom_values    = array();
				$newUploadedFiles = array();
				$custom_fields    = $get_custom_fields['custom_field'];

				if (is_array($custom_fields)) {
					foreach ($custom_fields as $cus_key => $fields) {
						$system_name = $fields['system_name'];
						if ($fields['question_type'] == 'FILE') {
							$post_image                       = get_post_meta($postID, 'ow_custom_attachment_' . $system_name, true);
							$custom_values[$system_name]    = empty($post_image) ? '' : $post_image['url'];
							$newUploadedFiles[$system_name] = '';
						} else {
							$custom_values[$system_name] = (is_array($get_post_metas)) ? $get_post_metas[$system_name] : '';
							if ($custom_values[$system_name] == '') {
								$custom_values[$system_name] = get_post_meta($postID, $system_name, true);
							}
							if (array_key_exists('contestant-ow_video_url', $custom_values) && $custom_values[$system_name] == '') {
								$get_post_url                  = get_post_meta($postID, 'contestant-ow_video_url', true);
								$custom_values[$system_name] = $get_post_url;
							}
						}
					}
				}
			}
			return array(
				'custom_values'    => $custom_values,
				'newUploadedFiles' => $newUploadedFiles,
			);
		}
		/**
		 * Delete Contestant.
		 *
		 * @param mixed $request_data Requested Data.
		 */
		public static function wpvc_callback_delete_contestant($request_data)
		{
			if ((isset($_SERVER['HTTP_AUTHORIZE_WPVC']) && sanitize_text_field(wp_unslash($_SERVER['HTTP_AUTHORIZE_WPVC'])) != '') && strtolower(sanitize_text_field(wp_unslash($_SERVER['HTTP_AUTHORIZE_WPVC']))) == sanitize_text_field(wp_unslash($_COOKIE['wpvc_freevoting_authorize'])) && sanitize_text_field(wp_unslash($_SERVER['HTTP_AUTHORIZE_WPVC_REQUEST'])) == 'xmlhttprequest') {
				$param       = $request_data->get_params();
				$postID      = $param['id'];
				$delete_post = '';
				if ($postID != '') {
					$delete_post = wp_delete_post($postID);
				}
				if ($delete_post != '') {
					$respon = array('deleted' => $delete_post->ID);
				}
				$respon = array('deleted' => $delete_post->ID);
				return new WP_REST_Response($respon, 200);
			} else {
				die(wp_json_encode(array('no_cheating' => 'You have no permission to access Voting contest')));
			}
		}
	}
} else {
	die('<h2>' . esc_html_e('Failed to load Voting Front Rest Actions Controller', 'voting-contest') . '</h2>');
}

return new Wpvc_Front_Rest_Actions_Controller();
