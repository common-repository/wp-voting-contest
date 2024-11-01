<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
if ( ! class_exists( 'Wpvc_Email_Controller' ) ) {
	/**
	 * Email Controller.
	 */
	class Wpvc_Email_Controller {

		/**
		 * Controller Contructor.
		 */
		public function __construct() {
			add_action( 'transition_post_status', array( $this, 'wpvc_send_publish_email_from_admin' ), 10, 3 );
		}

		/**
		 * Checking Email to send.
		 *
		 * @param int   $post_id Contestant ID.
		 * @param mixed $userdata User Data.
		 * @param mixed $insertdata Inserted DATA ID.
		 */
		public function wpvc_contestant_check_email( $post_id, $userdata = '', $insertdata = '' ) {
			$vote_opt = get_option( WPVC_VOTES_SETTINGS );
			if ( is_array( $vote_opt ) ) {
				$email   = $vote_opt['email'];
				$contest = $vote_opt['contest'];
				// Check ADMIN EMAIL NOTIFICATION is set to on.
				if ( $email['vote_notify_mail'] === 'on' ) {
					self::wpvc_sending_admin( $post_id, $insertdata, $userdata, $vote_opt );
				}

				// Check CONTESTANT SUCCESS EMAIL NOTIFICATION is set to on and Auto Approve to off.
				if ( $email['vote_notify_contestant'] === 'on' && $contest['vote_publishing_type'] === 'off' ) {
					self::wpvc_insert_contestants_mail( $post_id, $insertdata, $userdata, $vote_opt );
				} // Check CONTESTANT PUBLISHED EMAIL NOTIFICATION is set to on and Auto Approve to on.
				elseif ( $email['vote_notify_approved'] === 'on' && $contest['vote_publishing_type'] === 'on' ) {
					self::wpvc_send_published_mail( $post_id, $insertdata, $userdata, $vote_opt );
				} // Check CONTESTANT SUCCESS EMAIL NOTIFICATION is set to on and Auto Approve to on and CONTESTANT PUBLISHED EMAIL NOTIFICATION is set to off.
				elseif ( $email['vote_notify_contestant'] === 'on' && $contest['vote_publishing_type'] === 'on' && $email['vote_notify_approved'] === 'off' ) {
					self::wpvc_insert_contestants_mail( $post_id, $insertdata, $userdata, $vote_opt );
				} else {
					return;
				}
			} else {
				return;
			}
		}
		/**
		 * Send Email When Contestant Published.
		 *
		 * @param string $new_status New Status.
		 * @param string $old_status Old Status.
		 * @param mixed  $post Post Object.
		 */
		public function wpvc_send_publish_email_from_admin( $new_status, $old_status, $post ) {
			if ( ( 'publish' === $new_status && 'publish' !== $old_status ) && 'contestants' === $post->post_type ) {
				if ( ! ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
					$insertdata = get_post_meta( $post->ID, WPVC_VOTES_POST, true );
					$vote_opt   = get_option( WPVC_VOTES_SETTINGS );
					$email      = $vote_opt['email'];
					if ( $email['vote_notify_approved'] === 'on' ) {
						$template = file_get_contents( WPVC_VOTES_ABSPATH . 'wpvc_email/emailbutton.php' );
						// $variables['thanks']  = 'Your Submission is Approved';
						$variables['subject'] = $email['vote_approve_subject'];
						self::set_translation_variables( $variables, 'your_submission_approved' );
						$variables['content'] = self::wpvc_replace_custom_fields( $post->ID, $email['vote_contestant_approved_content'], $insertdata );

						// contestant_link.
						$variables['contestant_link'] = get_the_permalink( $post->ID );

						if ( $insertdata['contestant-email_address'] != null ) {
							self::wpvc_send_mail( $insertdata['contestant-email_address'], $variables['subject'], $template, $variables );
						} else {
							return;
						}
					}
				} else {
					return;
				}
			} else {
				return;
			}
		}
		/**
		 * Sending Mail to the Contestants who posted.
		 *
		 * @param int   $post_id Contestant ID.
		 * @param mixed $insertdata Inserted DATA ID.
		 * @param mixed $userdata User Data.
		 * @param mixed $vote_opt Settings.
		 */
		public function wpvc_insert_contestants_mail( $post_id, $insertdata, $userdata = '', $vote_opt = null ) {
			$email = $vote_opt['email'];

			$template = file_get_contents( WPVC_VOTES_ABSPATH . 'wpvc_email/email.php' );

			// $variables['thanks']  = 'Thank you for your submission';
			$variables['subject'] = $email['vote_notify_subject'];

			self::set_translation_variables( $variables, 'thanks_for_submission' );
			$variables['content'] = self::wpvc_replace_custom_fields( $post_id, $email['vote_contestant_submit_content'], $insertdata );

			// Check if the user is logged in.
			if ( $userdata['user_logged'] === true ) {
				$current_user = get_user_by( 'id', $userdata['user_id_profile'] );
				$to           = $current_user->user_email;
				self::wpvc_send_mail( $to, $variables['subject'], $template, $variables );
			} elseif ( $insertdata['contestant-email_address'] != null ) {
				self::wpvc_send_mail( $insertdata['contestant-email_address'], $variables['subject'], $template, $variables );
			} else {
				return;
			}
		}
		/**
		 * Sending Mail to the Contestants when published.
		 *
		 * @param int   $post_id Contestant ID.
		 * @param mixed $insertdata Inserted DATA ID.
		 * @param mixed $userdata User Data.
		 * @param mixed $vote_opt Settings.
		 */
		public function wpvc_send_published_mail( $post_id, $insertdata, $userdata = '', $vote_opt = null ) {
			$email = $vote_opt['email'];
			// Check CONTESTANT PUBLISHED EMAIL NOTIFICATION is set to on.
			if ( $email['vote_notify_approved'] === 'on' ) {
				$template = file_get_contents( WPVC_VOTES_ABSPATH . 'wpvc_email/emailbutton.php' );

				// $variables['thanks']  = 'Your Submission is Approved';
				$variables['subject'] = $email['vote_approve_subject'];
				self::set_translation_variables( $variables, 'your_submission_approved' );

				$variables['content'] = self::wpvc_replace_custom_fields( $post_id, $email['vote_contestant_approved_content'], $insertdata );

				// contestant_link.
				$variables['contestant_link'] = get_the_permalink( $post_id );

				// Check if the user is logged in.
				if ( $userdata['user_logged'] === true ) {
					$current_user = get_user_by( 'id', $userdata['user_id_profile'] );
					$to           = $current_user->user_email;
					self::wpvc_send_mail( $to, $variables['subject'], $template, $variables );
				} elseif ( $insertdata['contestant-email_address'] != null ) {
					self::wpvc_send_mail( $insertdata['contestant-email_address'], $variables['subject'], $template, $variables );
				} else {
					return;
				}
			}
		}
		/**
		 * Sending Email to admin.
		 *
		 * @param int   $post_id Contestant ID.
		 * @param mixed $insertdata Inserted DATA ID.
		 * @param mixed $userdata User Data.
		 * @param mixed $vote_opt Settings.
		 */
		public function wpvc_sending_admin( $post_id, $insertdata, $userdata = null, $vote_opt = null ) {
			$email = $vote_opt['email'];

			$template = file_get_contents( WPVC_VOTES_ABSPATH . 'wpvc_email/emailbutton.php' );

			// $variables['thanks']  = 'A user has added a contestant';
			$translations         = self::get_translations_array();
			$variables['subject'] = $translations['contestant_added']; // 'CONTESTANT ADDED';
			self::set_translation_variables( $variables, 'user_added_contestant' );

			$variables['content'] = self::wpvc_replace_custom_fields( $post_id, $email['vote_admin_mail_content'], $insertdata );

			// contestant_link.
			$variables['contestant_link'] = get_bloginfo( 'url' ) . '/wp-admin/post.php?post=' . $post_id . '&action=edit';

			// Check if the user is logged in.
			if ( $email['vote_admin_mail'] === '' ) {
				$to = get_option( 'admin_email' );
				self::wpvc_send_mail( $to, $variables['subject'], $template, $variables );
			} elseif ( $email['vote_admin_mail'] != null ) {
				self::wpvc_send_mail( $email['vote_admin_mail'], $variables['subject'], $template, $variables );
			} else {
				return;
			}
		}
		/**
		 * Replace the Custom Fields data in the Mail Content.
		 *
		 * @param int   $post_id Contestant ID.
		 * @param mixed $content User Data.
		 * @param mixed $insertdata Inserted DATA ID.
		 */
		public function wpvc_replace_custom_fields( $post_id, $content, $insertdata ) {

			// Fetch Custom Fields.
			$custom_fields = Wpvc_Settings_Model::wpvc_custom_fields_json();

			// Old Version - replacing the existing string.
			$content = str_replace( 'customfield_', '', $content );

			// Feature Image.
			if ( strpos( $content, '{contestant-image}' ) !== false ) {
				$featured_img_url = get_the_post_thumbnail_url( $post_id, 'full' );
				$replace_content  = '<a target="_blank" href="' . $featured_img_url . '">' . __( 'Link', 'voting-contest' ) . '</a>';
				$content          = str_replace( '{contestant-image}', $replace_content . '<br/>', $content );
			}

			// Contestant Link.
			if ( strpos( $content, '{contestant-link}' ) !== false ) {
				$replace_content = get_permalink( $post_id, false );
				$content         = str_replace( '{contestant-link}', $replace_content . '<br/>', $content );
			}

			// Contestant Email Address.
			if ( strpos( $content, '{contestant-email_address}' ) !== false ) {
				$email_address   = get_post_meta( $post_id, 'contestant-email_address', true );
				$replace_content = '<a href="mailto:' . $email_address . '">' . $email_address . '</a>';
				$content         = str_replace( '{contestant-email_address}', $replace_content . '<br/>', $content );
			}

			foreach ( $custom_fields as $custom_field ) {
				// DATE Field Type.
				if ( $custom_field['question_type'] == 'DATE' ) {
					$date_field = $insertdata[ $custom_field['system_name'] ];
					if ( $date_field != null ) {
						$replace_content = date( 'Y-m-d', strtotime( $date_field ) );
						$content         = str_replace( '{' . $custom_field['system_name'] . '}', $replace_content . '<br/>', $content );
					} else {
						$content = str_replace( '{' . $custom_field['system_name'] . '}', '' . '<br/>', $content );
					}
				}
				// FILE Field Type.
				elseif ( $custom_field['question_type'] == 'FILE' ) {
					$uploaded_file = get_post_meta( $post_id, 'ow_custom_attachment_' . $custom_field['system_name'], true );
					if ( ! empty( $uploaded_file ) ) {
						$file_url        = $uploaded_file['url'];
						$replace_content = '<a target="_blank" href="' . $file_url . '">' . __( 'Link', 'voting-contest' ) . '</a>';
						$content         = str_replace( '{' . $custom_field['system_name'] . '}', $replace_content . '<br/>', $content );
					} else {
						$content = str_replace( '{' . $custom_field['system_name'] . '}', '' . '<br/>', $content );
					}
				} elseif ( strpos( $content, '{' . $custom_field['system_name'] . '}' ) !== false ) {
					$content = str_replace( '{' . $custom_field['system_name'] . '}', $insertdata[ $custom_field['system_name'] ] . '<br/>', $content );
				}
			}

			return $content;
		}
		/**
		 * Reset Password Link.
		 *
		 * @param mixed $user_data User Data.
		 * @param mixed $key Keys.
		 */
		public function wpvc_send_reset_password( $user_data, $key ) {
			$user_login = $user_data->user_login;
			$user_email = $user_data->user_email;

			$message  = __( 'Someone requested that the password be reset for the following account:' ) . '<br/>';
			$message .= network_home_url( '/' ) . '<br/>';
			// Username for the Email.
			$message .= sprintf( __( 'Username: %s' ), $user_login ) . '<br/>';
			$message .= __( 'If this was a mistake, just ignore this email and nothing will happen.' ) . '<br/>';
			$message .= __( 'To reset your password, visit the following address:' ) . '<br/>';
			$message .= '<a href=' . network_site_url( "wp-login.php?action=rp&key=$key&login=" . rawurlencode( $user_login ), 'login' ) . '>Link</a><br/>';

			if ( is_multisite() ) {
				$blogname = $GLOBALS['current_site']->site_name;
			} else {
				$blogname = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
			}

			$title = sprintf( __( '[%s] Password Reset' ), $blogname );

			$title   = apply_filters( 'retrieve_password_title', $title );
			$message = apply_filters( 'retrieve_password_message', $message, $key );

			$template                    = file_get_contents( WPVC_VOTES_ABSPATH . 'wpvc_email/email.php' );
			$variables['thanks_message'] = $title;
			$variables['subject']        = $title;
			$variables['content']        = $message;

			$variables['sitename'] = get_bloginfo( 'name' );
			foreach ( $variables as $key => $value ) {
				$template = str_replace( '{{ ' . $key . ' }}', $value, $template );
			}
			$email_content = $template;
			$headers       = array( 'Content-Type: text/html; charset=UTF-8', 'From: Wordpress<wordpress@mysite.com>' );

			if ( ! wp_mail( $user_email, $title, $email_content, $headers ) ) {
				return false;
			} else {
				return true;
			}
		}
		/**
		 * Common Mail Sending function.
		 *
		 * @param string $to To email address.
		 * @param string $subject Subject.
		 * @param string $template Template.
		 * @param array  $variables Data.
		 */
		public function wpvc_send_mail( $to, $subject, $template, $variables ) {
			$variables['sitename'] = get_bloginfo( 'name' );
			foreach ( $variables as $key => $value ) {
				$template = str_replace( '{{ ' . $key . ' }}', $value, $template );
			}
			$email_content = $template;
			$headers       = array( 'Content-Type: text/html; charset=UTF-8' );
			$vote_opt      = get_option( WPVC_VOTES_SETTINGS );
			if ( ! empty( $vote_opt ) ) {
				$email = $vote_opt['email'];
				if ( $email['vote_from_mail'] != '' ) {
					$headers[] = 'From: ' . get_bloginfo( 'name' ) . ' <' . $email['vote_from_mail'] . '>';
				}
			}
			wp_mail( $to, $subject, $email_content, $headers );
		}

		/**
		 * Function to get the translation file contents
		 *
		 * @return array translation key value pairs
		 */
		public function get_translations_array() {
			$lang = file_get_contents( WPVC_VIEWS . 'translation.json' );
			return json_decode( $lang, true );
		}

		public function set_translation_variables( &$variables, $thanks_message_key = '' ) {
			$translations                = self::get_translations_array();
			$variables['thanks']         = $translations['thanks'];
			$variables['copyrights']     = $translations['copyrights'];
			$variables['rights']         = $translations['all_rights_reserved'];
			$variables['thanks_message'] = '';
			if ( $thanks_message_key != '' ) {
				$variables['thanks_message'] = $translations[ $thanks_message_key ];
			}
		}
	}
} else {
	die( '<h2>' . esc_html_e( 'Failed to load the Voting Email Controller', 'voting-contest' ) . '</h2>' );
}

return new Wpvc_Email_Controller();
