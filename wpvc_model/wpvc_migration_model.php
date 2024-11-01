<?php
if (!class_exists('Wpvc_Migration_Model')) {
	/**
	 *  Migration Model.
	 */
	class Wpvc_Migration_Model
	{
		/**
		 *  Get total votes count.
		 */
		public static function wpvc_get_votes_count()
		{
			global $wpdb;
			$total_posts = get_posts(
				array(
					'post_type'   => WPVC_VOTES_TYPE,
					'numberposts' => -1,
					'post_status' => 'any',
				)
			);
			if (is_array($total_posts)) {
				foreach ($total_posts as $key => $posts) {
					$post_id = $posts->ID;
					$result  = $wpdb->get_results($wpdb->prepare('SELECT * FROM `' . WPVC_VOTES_TBL . '` WHERE post_id= %d ', $post_id));
					$exists  = $wpdb->num_rows;
					if ($exists != '') {
						update_post_meta($post_id, WPVC_VOTES_CUSTOMFIELD, $exists);
					}
				}
			}
		}
		/**
		 *  Searching for post types contestants.
		 */
		public static function wpvc_get_poston_search()
		{
			$total_posts = get_posts(
				array(
					'post_type'   => WPVC_VOTES_TYPE,
					'numberposts' => -1,
					'post_status' => 'any',
				)
			);
			if (is_array($total_posts)) {
				foreach ($total_posts as $key => $posts) {
					$post_id        = $posts->ID;
					$get_post_metas = get_post_meta($post_id, WPVC_VOTES_POST, true);
					if (!empty($get_post_metas)) {
						foreach ($get_post_metas as $key => $post_meta) {
							$check_other = get_post_meta($post_id, $key, true);
							if (!$check_other) {
								update_post_meta($post_id, $key, $post_meta);
							}
						}
					}
					$check_title = get_post_meta($post_id, 'contestant-title', true);
					if (!$check_title) {
						update_post_meta($post_id, 'contestant-title', $posts->post_title);
						update_post_meta($post_id, 'contestant-desc', $posts->post_content);
					}
				}
			}
		}
		/**
		 *  Checking for react val
		 */
		public static function wpvc_check_migration_process()
		{
			global $wpdb;
			$react_val = 'react_val';
			$result    = $wpdb->get_results($wpdb->prepare('SHOW COLUMNS FROM `' . WPVC_VOTES_USER_CUSTOM_TABLE . '` LIKE %s', '%' . $react_val . '%'));
			$exists    = $wpdb->num_rows;
			return $exists;
		}
		/**
		 *  Adding react_val to the tables.
		 */
		public static function wpvc_migrate_plugin_to_react()
		{
			global $wpdb;
			$react_val = 'react_val';
			$result    = $wpdb->get_results($wpdb->prepare('SHOW COLUMNS FROM `' . WPVC_VOTES_USER_CUSTOM_TABLE . '` LIKE %s', '%' . $react_val . '%'));
			$exists    = $wpdb->num_rows;
			if ($exists == 0) {
				$backup = self::wpvc_backup_db();
				Wpvc_Installation_Model::wpvc_create_tables_owvoting();

				// Alter table needs to be added for react custom field and user table
				$alter_react     = 'ALTER TABLE ' . WPVC_VOTES_ENTRY_CUSTOM_TABLE . ' ADD `react_val` TEXT NULL DEFAULT NULL AFTER `description`';

				$alter_react_reg = 'ALTER TABLE ' . WPVC_VOTES_USER_CUSTOM_TABLE . ' ADD `react_val` TEXT NULL DEFAULT NULL AFTER `delete_time`';

				$wpdb->query($alter_react);
				$wpdb->query($alter_react_reg);
				wpvc_write_js_constants();
				self::wpvc_migrate_settings();
				$category            = self::wpvc_migrate_plugin_categories_to_react();
				$completed_migration = self::wpvc_migrate_contestants_to_react();

				$setting_saved = 0;
				$setting_check = get_option(WPVC_VOTES_SETTINGS);
				if (isset($setting_check['common'])) {
					$setting_saved = 1;
				}
			}
			self::wpvc_get_poston_search();
			$return_val = array(
				'already_migrated'    => $exists,
				'completed_migration' => $completed_migration,
				'settings'            => $setting_saved,
				'category'            => $category,
			);
			if (is_array($backup)) {
				$return_val['backup_success'] = $backup['backup_success'];
				$return_val['backup_path']    = $backup['backup_path'];
			}
			return new WP_REST_Response($return_val, 200);
		}

		/**
		 *  Backing up the database.
		 */
		public static function wpvc_backup_db()
		{
			global $wpdb;
			$database_name = DB_NAME;
			$db_user       = DB_USER;
			$db_pass       = DB_PASSWORD;
			$db_host       = DB_HOST;
			$upload_dir    = wp_upload_dir();
			$return_val    = '';
			if (is_array($upload_dir)) {
				$path             = $upload_dir['path'];
				$filename         = 'wpvc_voting_before_migration_' . date('j_F_Y') . '.sql';
				$backup_file_name = $path . '/' . $filename;
				include_once dirname(__FILE__) . '/wpvc_mysql/Mysqldump.php';
				$dump   = new Ifsnop\Mysqldump\Mysqldump('mysql:host=' . $db_host . ';dbname=' . $database_name, $db_user, $db_pass);
				$backup = $dump->start($backup_file_name);
				if (file_exists($backup_file_name)) {
					$return_val = array(
						'backup_success' => '1',
						'backup_path'    => $backup_file_name,
					);
				} else {
					$return_val = array(
						'backup_success' => '0',
						'backup_path'    => $backup_file_name,
					);
				}
			}
			return $return_val;
		}

		/**
		 * Migrate settings from previous one.
		 */
		public static function wpvc_migrate_settings()
		{
			$setting_check       = get_option(WPVC_OLD_VOTES_SETTINGS);
			$pagination          = get_option('contestpagenavi_options');
			$buy_vote_setting    = get_option(WPVC_OLD_BUYVOTES_SETTINGS);
			$video_settings      = maybe_unserialize(get_option(WPVC_OLD_VIDEO_EXTENSION_SETTINGS));
			$old_paypal_settings = get_option('ow_payment_settings');
			$old_stripe_settings = get_option('ow_stripe_settings');
			$new_setting_check   = get_option(WPVC_VOTES_SETTINGS);

			if (!isset($setting_check['common']) && $new_setting_check == '') {

				// Common Settings.
				$common['short_cont_image']                = ($setting_check['short_cont_image'] != '') ? $setting_check['short_cont_image'] : 'medium';
				$common['page_cont_image']                 = ($setting_check['page_cont_image'] != '') ? $setting_check['page_cont_image'] : 'large';
				$common['single_page_cont_image']          = ($setting_check['single_page_cont_image'] != '') ? $setting_check['single_page_cont_image'] : '100';
				$common['single_page_cont_image_px']       = ($setting_check['single_page_cont_image_px'] != '') ? $setting_check['single_page_cont_image_px'] : '%';
				$common['single_page_title']               = ($setting_check['single_page_title'] != '') ? $setting_check['single_page_title'] : 'off';
				$common['vote_prettyphoto_disable_single'] = ($setting_check['vote_prettyphoto_disable_single'] != '') ? $setting_check['vote_prettyphoto_disable_single'] : 'off';
				$common['vote_disable_single']             = ($setting_check['vote_disable_single'] != '') ? $setting_check['vote_disable_single'] : 'off';
				$common['orderby']                         = ($setting_check['orderby'] != '') ? $setting_check['orderby'] : 'date';
				$common['order']                           = ($setting_check['order'] == 'on') ? 'asc' : 'desc';
				$common['sidebar']                         = ($setting_check['vote_select_sidebar'] != '') ? $setting_check['vote_select_sidebar'] : '';
				$common['vote_sidebar']                    = ($setting_check['vote_sidebar'] != '') ? $setting_check['vote_sidebar'] : 'off';
				$common['vote_entry_form']                 = ($setting_check['vote_entry_form'] != '') ? $setting_check['vote_entry_form'] : 'off';
				$common['vote_prettyphoto_disable']        = ($setting_check['vote_prettyphoto_disable'] != '') ? $setting_check['vote_prettyphoto_disable'] : 'off';
				$common['vote_show_date_prettyphoto']      = ($setting_check['vote_show_date_prettyphoto'] != '') ? $setting_check['vote_show_date_prettyphoto'] : 'off';
				$common['vote_custom_slug']                = ($setting_check['vote_custom_slug'] != '') ? $setting_check['vote_custom_slug'] : '';
				$common['vote_newlink_tab']                = ($setting_check['vote_newlink_tab'] != '') ? $setting_check['vote_newlink_tab'] : 'off';
				$common['vote_enable_ended']               = ($setting_check['vote_enable_ended'] != '') ? $setting_check['vote_enable_ended'] : 'off';
				$common['vote_hide_account']               = ($setting_check['vote_hide_account'] != '') ? $setting_check['vote_hide_account'] : 'off';
				$common['vote_auto_login']                 = ($setting_check['vote_auto_login'] != '') ? $setting_check['vote_auto_login'] : 'on';
				$common['vote_enable_recaptcha']           = ($setting_check['vote_enable_recaptcha'] != '') ? $setting_check['vote_enable_recaptcha'] : 'off';
				$common['vote_enable_recaptcha_voting']    = ($setting_check['vote_enable_recaptcha_voting'] != '') ? $setting_check['vote_enable_recaptcha_voting'] : 'off';
				$common['vote_recapatcha_key']             = ($setting_check['vote_recapatcha_key'] != '') ? $setting_check['vote_recapatcha_key'] : '';
				$common['vote_recapatcha_secret']          = ($setting_check['vote_recapatcha_secret'] != '') ? $setting_check['vote_recapatcha_secret'] : '';
				$common['vote_openclose_menu']             = ($setting_check['vote_openclose_menu'] != '') ? $setting_check['vote_openclose_menu'] : 'off';
				$common['title']                           = ($setting_check['title'] != '') ? $setting_check['title'] : '';

				// Contest settings.
				$contest['onlyloggedinuser']         = ($setting_check['onlyloggedinuser'] != '') ? $setting_check['onlyloggedinuser'] : 'on';
				$contest['vote_onlyloggedcansubmit'] = ($setting_check['vote_onlyloggedcansubmit'] != '') ? $setting_check['vote_onlyloggedcansubmit'] : 'on';
				$contest['vote_tracking_method']     = ($setting_check['vote_tracking_method'] != '') ? $setting_check['vote_tracking_method'] : 'ip_traced';
				$contest['vote_frequency_count']     = ($setting_check['vote_frequency_count'] != '') ? $setting_check['vote_frequency_count'] : '1';
				$contest['frequency']                = ($setting_check['frequency'] != '') ? $setting_check['frequency'] : '1';
				$contest['vote_votingtype']          = ($setting_check['vote_votingtype'] != '') ? $setting_check['vote_votingtype'] : '2';
				$contest['vote_truncation_grid']     = ($setting_check['vote_truncation_grid'] != '') ? $setting_check['vote_truncation_grid'] : '';
				$contest['vote_truncation_list']     = ($setting_check['vote_truncation_list'] != '') ? $setting_check['vote_truncation_list'] : '';
				$contest['vote_publishing_type']     = ($setting_check['vote_publishing_type'] != '') ? $setting_check['vote_publishing_type'] : 'off';
				$contest['vote_grab_email_address']  = ($setting_check['vote_grab_email_address'] != '') ? $setting_check['vote_grab_email_address'] : 'off';
				$contest['vote_tobestarteddesc']     = ($setting_check['vote_tobestarteddesc'] != '') ? $setting_check['vote_tobestarteddesc'] : 'Contest not yet open for voting';
				$contest['vote_reachedenddesc']      = ($setting_check['vote_reachedenddesc'] != '') ? $setting_check['vote_reachedenddesc'] : 'There is no Contest at this time';
				$contest['vote_entriescloseddesc']   = ($setting_check['vote_entriescloseddesc'] != '') ? $setting_check['vote_entriescloseddesc'] : '';

				// Style.
				$style                         = array(
					'row_width'    => '3',
					'direction'    => 'row',
					'column_width' => '12',
					'wpvc_spacing' => '1',
				);
				$style['vote_title_alocation'] = ($setting_check['vote_title_alocation'] != '') ? $setting_check['vote_title_alocation'] : 'off';
				$style['vote_readmore']        = ($setting_check['vote_readmore'] != '') ? $setting_check['vote_readmore'] : 'off';
				$style['vote_count_showhide']  = ($setting_check['vote_count_showhide'] != '') ? $setting_check['vote_count_showhide'] : 'on';

				// color.
				$color = array(
					'votes_counter_font_size'              => '14',
					'votes_navigation_font_size'           => '14',
					'votes_navigation_text_color'          => '#FFFFFF',
					'votes_navigation_text_color_hover'    => '#ffffff',
					'votes_navigationbgcolor'              => '#305891',
					'votes_list_active'                    => '#F26E2A',
					'votes_list_inactive'                  => '#ffffff',
					'votes_grid_active'                    => '#F26E2A',
					'votes_grid_inactive'                  => '#ffffff',
					'vote_navbar_active_button_background' => '#d83c30',
					'vote_navbar_mobile_font'              => '#ffffff',
					'votes_cont_title_font_size'           => '16',
					'votes_cont_title_bgcolor'             => '#30598f',
					'votes_cont_title_color'               => '#FFFFFF',
					'votes_cont_title_color_hover'         => '#F26E2A',
					'votes_cont_title_color_grid'          => '#f26e2a',
					'votes_cont_title_color_hover_grid'    => '#30598f',
					'votes_cont_desc_font_size'            => '16',
					'votes_cont_dese_color'                => '#000000',
					'votes_readmore_font_size'             => '14',
					'votes_readmore_fontcolor'             => '#F26E2A',
					'votes_readmore_fontcolor_hover'       => '#000000',
					'votes_count_font_size'                => '16',
					'votes_count_font_color'               => '#FFFFFF',
					'votes_count_bgcolor'                  => '#3276b1',
					'votes_button_font_size'               => '16',
					'votes_button_font_color'              => '#FFFFFF',
					'votes_button_font_color_hover'        => '#FFFFFF',
					'votes_button_bgcolor'                 => '#305891',
					'votes_button_bgcolor_hover'           => '#f26e2a',
					'votes_highlight_button_bgcolor'       => '#008000',
					'votes_already_button_bgcolor'         => '#008000',
					'votes_social_font_size'               => '16',
					'votes_social_icon_color'              => '#F26E2A',
					'votes_social_icon_color_hover'        => '#30598F',
				);

				$color['votes_timerbgcolor']   = ($setting_check['votes_timerbgcolor'] != '') ? $setting_check['votes_timerbgcolor'] : '#ffffff';
				$color['votes_timertextcolor'] = ($setting_check['votes_timertextcolor'] != '') ? $setting_check['votes_timertextcolor'] : '#000000';

				// Share.
				$share['vote_fb_appid']          = ($setting_check['vote_fb_appid'] != '') ? $setting_check['vote_fb_appid'] : '';
				$share['facebook']               = ($setting_check['facebook'] != '') ? $setting_check['facebook'] : 'on';
				$share['facebook_login']         = ($setting_check['facebook_login'] != '') ? $setting_check['facebook_login'] : 'off';
				$share['file_fb_default']        = ($setting_check['file_fb_default'] != '') ? $setting_check['file_fb_default'] : 'on';
				$share['pinterest']              = ($setting_check['pinterest'] != '') ? $setting_check['pinterest'] : 'on';
				$share['file_pinterest_default'] = ($setting_check['file_pinterest_default'] != '') ? $setting_check['file_pinterest_default'] : 'on';
				$share['reddit']                 = ($setting_check['reddit'] != '') ? $setting_check['reddit'] : 'on';
				$share['file_reddit_default']    = ($setting_check['file_reddit_default'] != '') ? $setting_check['file_reddit_default'] : 'on';
				$share['linkedin']               = ($setting_check['linkedin'] != '') ? $setting_check['linkedin'] : 'on';
				$share['file_linkedin_default']  = ($setting_check['file_linkedin_default'] != '') ? $setting_check['file_linkedin_default'] : 'on';
				$share['tumblr']                 = ($setting_check['tumblr'] != '') ? $setting_check['tumblr'] : 'on';
				$share['file_tumblr_default']    = ($setting_check['file_tumblr_default'] != '') ? $setting_check['file_tumblr_default'] : 'on';
				$share['vote_tw_appid']          = ($setting_check['vote_tw_appid'] != '') ? $setting_check['vote_tw_appid'] : '';
				$share['vote_tw_secret']         = ($setting_check['vote_tw_secret'] != '') ? $setting_check['vote_tw_secret'] : '';
				$share['twitter']                = ($setting_check['twitter'] != '') ? $setting_check['twitter'] : 'on';
				$share['twitter_login']          = ($setting_check['twitter_login'] != '') ? $setting_check['twitter_login'] : 'off';
				$share['file_tw_default']        = ($setting_check['file_tw_default'] != '') ? $setting_check['file_tw_default'] : 'on';
				$share['google_login']           = ($setting_check['google_login'] != '') ? $setting_check['google_login'] : 'off';
				$share['vote_gg_clientid']       = ($setting_check['vote_gg_clientid'] != '') ? $setting_check['vote_gg_clientid'] : '';

				// plugin.
				$plugin['deactivation'] = ($setting_check['deactivation'] != '') ? $setting_check['deactivation'] : 'on';

				$length          = get_option('ow_vote_excerpt_controller_length');
				$use_word        = get_option('ow_vote_excerpt_controller_use_word');
				$ellipsis        = get_option('ow_vote_excerpt_controller_ellipsis');
				$finish_word     = get_option('ow_vote_excerpt_controller_finish_word');
				$finish_sentence = get_option('ow_vote_excerpt_controller_finish_sentence');
				$read_more       = get_option('ow_vote_excerpt_controller_read_more');
				$add_link        = get_option('ow_vote_excerpt_controller_add_link');
				$no_custom       = get_option('ow_vote_excerpt_controller_no_custom');
				$no_shortcode    = get_option('ow_vote_excerpt_controller_no_shortcode');

				// Excerpt.
				$excerpt['length']          = ($length != '') ? $length : '40';
				$excerpt['use_word']        = ($use_word != '') ? $use_word : 'on';
				$excerpt['ellipsis']        = ($ellipsis != '') ? $ellipsis : '';
				$excerpt['finish_word']     = ($finish_word != '') ? $finish_word : 'off';
				$excerpt['finish_sentence'] = ($finish_sentence != '') ? $finish_sentence : 'off';
				$excerpt['read_more']       = ($read_more != '') ? $read_more : 'Read More';
				$excerpt['add_link']        = ($add_link != '') ? $add_link : 'off';
				$excerpt['no_custom']       = ($no_custom != '') ? $no_custom : 'on';
				$excerpt['no_shortcode']    = ($no_shortcode != '') ? $no_shortcode : 'on';

				// Pagin.
				$pagin['contestant_per_page']   = ($pagination['contestant_per_page'] != '') ? $pagination['contestant_per_page'] : '10';
				$pagin['pages_text']            = ($pagination['pages_text'] != '') ? $pagination['pages_text'] : 'Page %CURRENT_PAGE% of %TOTAL_PAGES%';
				$pagin['current_text']          = ($pagination['current_text'] != '') ? $pagination['current_text'] : '%PAGE_NUMBER%';
				$pagin['page_text']             = ($pagination['page_text'] != '') ? $pagination['page_text'] : '%PAGE_NUMBER%';
				$pagin['first_text']            = ($pagination['first_text'] != '') ? $pagination['first_text'] : '« First';
				$pagin['last_text']             = ($pagination['last_text'] != '') ? $pagination['last_text'] : 'Last »';
				$pagin['prev_text']             = ($pagination['prev_text'] != '') ? $pagination['prev_text'] : '«';
				$pagin['next_text']             = ($pagination['next_text'] != '') ? $pagination['next_text'] : '»';
				$pagin['load_more_button_text'] = ($pagination['load_more_button_text'] != '') ? $pagination['load_more_button_text'] : 'Loadmore';
				$pagin['style']                 = ($pagination['style'] != '') ? $pagination['style'] : '1';
				$pagin['num_pages']             = ($pagination['num_pages'] != '') ? $pagination['num_pages'] : '5';

				// Email.
				$email['vote_notify_mail']                 = ($setting_check['vote_notify_mail'] != '') ? $setting_check['vote_notify_mail'] : 'off';
				$email['vote_admin_mail']                  = ($setting_check['vote_admin_mail'] != '') ? $setting_check['vote_admin_mail'] : '';
				$email['vote_admin_mail_content']          = ($setting_check['vote_admin_mail_content'] != '') ? $setting_check['vote_admin_mail_content'] : '';
				$email['vote_notify_contestant']           = ($setting_check['vote_notify_contestant'] != '') ? $setting_check['vote_notify_contestant'] : 'off';
				$email['vote_notify_subject']              = ($setting_check['vote_notify_subject'] != '') ? $setting_check['vote_notify_subject'] : '';
				$email['vote_contestant_submit_content']   = ($setting_check['vote_contestant_submit_content'] != '') ? $setting_check['vote_contestant_submit_content'] : '';
				$email['vote_notify_approved']             = ($setting_check['vote_notify_approved'] != '') ? $setting_check['vote_notify_approved'] : '';
				$email['vote_approve_subject']             = ($setting_check['vote_approve_subject'] != '') ? $setting_check['vote_approve_subject'] : '';
				$email['vote_contestant_approved_content'] = ($setting_check['vote_contestant_approved_content'] != '') ? $setting_check['vote_contestant_approved_content'] : '';

				if (!empty($buy_vote_setting)) {
					$wpvc_buyvotes_settings['ow_buyvotes_mode']         = ($buy_vote_setting['ow_buyvotes_mode'] != '') ? $buy_vote_setting['ow_buyvotes_mode'] : '0';
					$wpvc_buyvotes_settings['ow_buyvotes_currency']     = ($buy_vote_setting['ow_buyvotes_currency'] != '') ? $buy_vote_setting['ow_buyvotes_currency'] : 'USD';
					$wpvc_buyvotes_settings['ow_buy_paypal_enable']     = ($buy_vote_setting['ow_buy_paypal_enable'] != '') ? $buy_vote_setting['ow_buy_paypal_enable'] : 'off';
					$wpvc_buyvotes_settings['ow_buyvotes_paypal_id']    = ($buy_vote_setting['ow_buyvotes_paypal_id'] != '') ? $buy_vote_setting['ow_buyvotes_paypal_id'] : '';
					$wpvc_buyvotes_settings['ow_buy_stripe_enable']     = ($buy_vote_setting['ow_buy_stripe_enable'] != '') ? $buy_vote_setting['ow_buy_stripe_enable'] : 'on';
					$wpvc_buyvotes_settings['ow_buy_stripe_secretkey']  = ($buy_vote_setting['ow_buy_stripe_secretkey'] != '') ? $buy_vote_setting['ow_buy_stripe_secretkey'] : '';
					$wpvc_buyvotes_settings['ow_buy_stripe_publishkey'] = ($buy_vote_setting['ow_buy_stripe_publishkey'] != '') ? $buy_vote_setting['ow_buy_stripe_publishkey'] : '';
				}

				$wpvc_video_settings['ow_enable_youtube']         = ($video_settings['ow_enable_youtube'] != '') ? $video_settings['ow_enable_youtube'] : 'off';
				$wpvc_video_settings['ow_oauth_client_id']        = ($video_settings['ow_oauth_client_id'] != '') ? $video_settings['ow_oauth_client_id'] : '';
				$wpvc_video_settings['ow_oauth_secret_id']        = ($video_settings['ow_oauth_secret_id'] != '') ? $video_settings['ow_oauth_secret_id'] : '';
				$wpvc_video_settings['ow_video_upload_msg']       = ($video_settings['ow_video_upload_msg'] != '') ? $video_settings['ow_video_upload_msg'] : 'Video Uploading Please Wait';
				$wpvc_video_settings['ow_video_after_upload_msg'] = ($video_settings['ow_video_after_upload_msg'] != '') ? $video_settings['ow_video_after_upload_msg'] : 'Upload Complete. Click submit to publish !';

				if (empty($old_paypal_settings)) {
					$old_paypal_settings = array(
						'ow_paypal_currency'           => 'USD',
						'ow_paypal_client_id'          => '',
						'ow_paypal_mode'               => '0',
						'ow_paypal_thankyou'           => '',
						'payment_paypal_email_content' => '',
					);
				} else {
					$old_paypal_settings['ow_paypal_client_id'] = '';
					unset($old_paypal_settings['ow_paypal_merchant_email']);
				}

				if (empty($old_stripe_settings)) {
					$old_stripe_settings = array(
						'ow_stripe_curreny'            => 'USD',
						'ow_stripe_publishkey'         => '',
						'ow_stripe_secretkey'          => '0',
						'ow_stripe_thankyou'           => '',
						'payment_stripe_email_content' => '',
					);
				}

				$paystack_settings = array(
					'paystack_public_key'            => '',
					'ow_paystack_thankyou'           => '',
					'payment_paystack_email_content' => '',
				);

				$coinbase_settings = array(
					'coinbase_api_key'               => '',
					'coinbase_secret'                => '',
					'payment_coinbase_email_content' => '',
				);

				$paymentsettings = array(
					'paypal_settings'   => $old_paypal_settings,
					'stripe_settings'   => $old_stripe_settings,
					'paystack_settings' => $paystack_settings,
					'coinbase_settings' => $coinbase_settings,
				);

				$settings = array(
					'common'                       => $common,
					'contest'                      => $contest,
					'style'                        => $style,
					'color'                        => $color,
					'share'                        => $share,
					'plugin'                       => $plugin,
					'excerpt'                      => $excerpt,
					'pagination'                   => $pagin,
					'email'                        => $email,
					'wpvc_buyvotes_settings'       => $wpvc_buyvotes_settings,
					'wpvc_videoextension_settings' => $wpvc_video_settings,
					'paidentry'                    => $paymentsettings,
				);
				update_option(WPVC_VOTES_SETTINGS, $settings);
			}
		}
		/**
		 * Migrate settings from previous one to react js.
		 */
		public static function wpvc_migrate_plugin_categories_to_react()
		{
			$terms = get_terms(WPVC_VOTES_TAXONOMY, array('hide_empty' => false));
			if (is_array($terms)) {
				$buy_vote_setting = get_option(WPVC_OLD_BUYVOTES_SETTINGS);
				foreach ($terms as $term_val) {
					$term_id          = $term_val->term_id;

					$option       = get_option($term_id . '_' . WPVC_OLD_VOTES_SETTINGS);
					$exp_option   = get_option($term_id . '_' . WPVC_VOTES_TAXEXPIRATIONFIELD);
					$act_option   = get_option($term_id . '_' . WPVC_VOTES_TAXACTIVATIONLIMIT);
					$start_option = get_option($term_id . '_' . WPVC_VOTES_TAXSTARTTIME);

					if (is_array($option)) {
						$customfields                  = self::create_custom_fields_for_react($option['custom_field_names'], $option['imgcontest'], $option['musicfileenable']);
						$option['vote_count_per_cat']  = $option['vote_count_per_contest'];
						$option['contest_rules']       = $option['vote_contest_rules'];
						$style                         = array();
						$style['vote_count_showhide']  = ($option['votecount'] == 'on' || $option['votecount'] == '') ? 'on' : 'off';
						$style['direction']            = ($option['show_description'] == 'grid') ? 'row' : 'column';
						$style['vote_title_alocation'] = 'off';
						$style['vote_readmore']        = 'on';
						$style['row_width']            = '3';
						$style['wpvc_spacing']         = '2';

						unset($option['votecount']);
						unset($option['vote_count_per_contest']);
						unset($option['vote_contest_rules']);

						foreach ($option as $key => $value) {
							update_term_meta($term_id, $key, $value);
						}
						update_term_meta($term_id, WPVC_VOTES_TAXONOMY_ASSIGN, $customfields);
					}
					update_term_meta($term_id, 'votes_expiration', $exp_option);
					update_term_meta($term_id, 'tax_activationcount', $act_option);
					update_term_meta($term_id, 'votes_starttime', $start_option);
					update_term_meta($term_id, 'color', '');
					update_term_meta($term_id, 'style', $style);

					if (!empty($buy_vote_setting)) {
						$ow_category_buyvotes_settings       = get_option($term_id . '_ow_category_buyvotes_settings');
						$ow_category_selvotes_settings       = (get_option($term_id . '_ow_category_selvotes_settings') == 1) ? 'on' : 'off';
						$ow_category_tiered_payment_settings = (get_option($term_id . '_ow_category_tiered_payment_settings') == 1) ? 'on' : 'off';
						$ow_category_tiered_payment_show     = (get_option($term_id . '_ow_category_tiered_payment_show') == 1) ? 'on' : 'off';

						$ow_category_amountvotes       = get_term_meta($term_id, 'ow_category_amountvotes', true);
						$ow_category_amountvotesfor    = get_term_meta($term_id, 'ow_category_amountvotesfor', true);
						$ow_category_amountvotes_range = get_term_meta($term_id, 'ow_category_amountvotes_range', true);
						$ow_category_freevotes         = get_term_meta($term_id, 'ow_category_freevotes', true);

						update_term_meta($term_id, 'wpvc_category_buyvotes_settings', $ow_category_buyvotes_settings);
						update_term_meta($term_id, 'wpvc_category_selvotes_settings', $ow_category_selvotes_settings);
						update_term_meta($term_id, 'wpvc_category_tiered_payment_settings', $ow_category_tiered_payment_settings);
						update_term_meta($term_id, 'wpvc_category_tiered_payment_show', $ow_category_tiered_payment_show);

						update_term_meta($term_id, 'wpvc_category_amountvotes', $ow_category_amountvotes);
						update_term_meta($term_id, 'wpvc_category_amountvotesfor', $ow_category_amountvotesfor);
						update_term_meta($term_id, 'wpvc_category_amountvotes_range', $ow_category_amountvotes_range);
						update_term_meta($term_id, 'wpvc_category_nfree_votes', $ow_category_freevotes);
					}

					$ow_category_coupon_settings = get_option($term_id . '_ow_category_coupon_settings');
					$ow_category_paypal_settings = get_option($term_id . '_ow_category_paypal_settings');
					$payment_paypal_entry_amount = get_option($term_id . '_payment_paypal_entry_amount');
					$ow_category_stripe_settings = get_option($term_id . '_ow_category_stripe_settings');
					$payment_stripe_entry_amount = get_option($term_id . '_payment_stripe_entry_amount');

					if ($ow_category_coupon_settings != '') {
						update_term_meta($term_id, 'category_coupon_settings', $ow_category_coupon_settings);
					}

					if ($ow_category_paypal_settings != '') {
						update_term_meta($term_id, 'ow_category_paypal_settings', $ow_category_paypal_settings);
					}

					if ($payment_paypal_entry_amount != '') {
						update_term_meta($term_id, 'payment_paypal_entry_amount', $payment_paypal_entry_amount);
					}

					if ($ow_category_stripe_settings != '') {
						update_term_meta($term_id, 'ow_category_stripe_settings', $ow_category_stripe_settings);
					}

					if ($payment_stripe_entry_amount != '') {
						update_term_meta($term_id, 'payment_stripe_entry_amount', $payment_stripe_entry_amount);
					}
				}
			}
			return 1;
		}
		/**
		 *  Creating custom fields for the react.
		 *
		 * @param array $custom_field custom_field.
		 * @param array $img_contest img_contest.
		 * @param array $musicfile musicfile.
		 */
		public static function create_custom_fields_for_react($custom_field, $img_contest, $musicfile = null)
		{
			global $wpdb;
			$get_default_values = Wpvc_Shortcode_Model::wpvc_custom_fields_by_contest($img_contest, $musicfile);
			if (is_array($custom_field)) {
				foreach ($custom_field as $field) {
					if ($field != '-1') {
						$sql        = $wpdb->prepare('SELECT * FROM ' . WPVC_VOTES_ENTRY_CUSTOM_TABLE . ' WHERE system_name = %s', $field);
						$get_fields = $wpdb->get_row($sql, ARRAY_A);
						array_push($get_default_values, $get_fields);
					}
				}
			}

			if (!empty($get_default_values)) {
				foreach ($get_default_values as $key => $values) {
					if ($values['system_name'] == 'contestant-terms') {
						$values['response'] = htmlentities($values['response'], ENT_QUOTES, 'UTF-8', true);
					}
					$get_default_values[$key]['original'] = $values;
					unset($get_default_values[$key]['sequence']);
					unset($get_default_values[$key]['response']);
					unset($get_default_values[$key]['required']);
					unset($get_default_values[$key]['required_text']);
					unset($get_default_values[$key]['admin_only']);
					unset($get_default_values[$key]['delete_time']);
					unset($get_default_values[$key]['wp_user']);
					unset($get_default_values[$key]['admin_view']);
					unset($get_default_values[$key]['pretty_view']);
					unset($get_default_values[$key]['ow_file_size']);
					unset($get_default_values[$key]['grid_only']);
					unset($get_default_values[$key]['list_only']);
					unset($get_default_values[$key]['show_labels']);

					unset($get_default_values[$key]['show_labels_single']);
					unset($get_default_values[$key]['set_limit']);
					unset($get_default_values[$key]['limit_count']);
					unset($get_default_values[$key]['description']);
					unset($get_default_values[$key]['react_val']);
					unset($get_default_values[$key]['react_values']);
					unset($get_default_values[$key]['drop_values']);
				}
			}
			return $get_default_values;
		}
		/**
		 *  Migrating Contestants to react.
		 */
		public static function wpvc_migrate_contestants_to_react()
		{
			$total_posts = get_posts(
				array(
					'post_type'   => WPVC_VOTES_TYPE,
					'numberposts' => -1,
					'post_status' => 'any',
				)
			);
			if (is_array($total_posts)) {
				foreach ($total_posts as $key => $posts) {
					$post_id = $posts->ID;
					$term    = get_the_terms($post_id, WPVC_VOTES_TAXONOMY);
					if (is_array($term)) {
						$termid     = $term[0]->term_id;
						$fields     = self::wpvc_voting_get_all_custom_fields($termid);
						$imgcontest = get_term_meta($termid, 'imgcontest', true);

						if (is_array($fields)) {
							self::wpvc_migrate_post_meta_data($fields, $imgcontest, $post_id);
						}
					}
				}
			}
			return 1;
		}
		/**
		 *  Migrating post meta data.
		 *
		 * @param array $custom_field custom_field.
		 * @param array $img_contest img_contest.
		 * @param int   $post_id post_id.
		 */
		public static function wpvc_migrate_post_meta_data($custom_field, $img_contest, $post_id)
		{
			$post_metas = get_post_meta($post_id);
			if (is_array($custom_field)) {
				$i = 0;
				foreach ($custom_field as $custom) {
					$system_name = $custom->system_name;
					$type        = $custom->question_type;
					if ($type == 'FILE') {
						$key_val = 'ow_custom_attachment_' . $i;
						if (array_key_exists($key_val, $post_metas)) {
							$un_serialize_vlue = maybe_unserialize($post_metas[$key_val][0]);
							update_post_meta($post_id, 'ow_custom_attachment_' . $system_name, $un_serialize_vlue);
						}
						$i++;
					}
				}
			}
			if (is_array($post_metas)) {
				unset($post_metas['votes_count']);
				unset($post_metas['contestant-image']);
				unset($post_metas['_edit_lock']);
				unset($post_metas['_edit_last']);
				unset($post_metas['ow_contestant_link']);
				unset($post_metas['votes_viewers']);

				$insert_array = array();
				foreach ($post_metas as $post_key => $meta_val) {
					if ($meta_val[0] != '') {
						$insert_array[$post_key] = $meta_val[0];
					}
				}
				if (!empty($insert_array)) {
					update_post_meta($post_id, WPVC_VOTES_POST, $insert_array);
				}
			}
		}
		/**
		 *  Get all Custom fields.
		 *
		 * @param int   $term_id term_id.
		 * @param array $video_opt video_opt.
		 */
		public static function wpvc_voting_get_all_custom_fields($term_id = null, $video_opt = null)
		{
			global $wpdb;
			$$delete_time  = 0;
			$sql           = $wpdb->prepare('SELECT * FROM ' . WPVC_VOTES_ENTRY_CUSTOM_TABLE . ' WHERE delete_time = %d order by sequence', $delete_time);
			$custom_fields = $wpdb->get_results($sql);
			return $custom_fields;
		}
	}
} else {
	die('<h2>' . esc_html(__('Failed to load Voting Migration model')) . '</h2>');
}

return new Wpvc_Migration_Model();
