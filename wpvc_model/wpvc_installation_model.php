<?php
if (!class_exists('Wpvc_Installation_Model')) {
	/**
	 *  Installation Model.
	 */
	class Wpvc_Installation_Model
	{
		/**
		 *  Create Tables.
		 */
		public static function wpvc_create_tables_owvoting()
		{
			global $wpdb;
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			/************* Create Tables if table not exists */
			$vote_tbl_sql = 'CREATE TABLE IF NOT EXISTS ' . WPVC_VOTES_TBL . ' (
                            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                            ip VARCHAR( 255 ) NOT NULL,
                            votes INT NOT NULL DEFAULT 0,
                            post_id INT NOT NULL,
                            termid VARCHAR( 255 ) NOT NULL DEFAULT "0",
                            ip_always VARCHAR( 255 ) NOT NULL DEFAULT "0",
                            email_always VARCHAR( 255 ) NOT NULL DEFAULT "0",
                            date DATETIME
                        )';

			$contestant_custom_table = 'CREATE TABLE IF NOT EXISTS ' . WPVC_VOTES_ENTRY_CUSTOM_TABLE . " (
                `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `sequence` int(11) NOT NULL DEFAULT 0,
                `question_type` enum('TEXT','TEXTAREA','MULTIPLE','SINGLE','DROPDOWN','FILE','DATE','TERMS') CHARACTER SET utf8 NOT NULL DEFAULT 'TEXT',
                `question` text CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
                `system_name` varchar(45) DEFAULT NULL,
                `response` text CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
                `required` enum('Y','N') NOT NULL DEFAULT 'N',
                `required_text` text DEFAULT NULL,
                `admin_only` enum('Y','N') NOT NULL DEFAULT 'N',
                `delete_time` varchar(45) DEFAULT '0',
                `wp_user` int(22) DEFAULT 1,
                `admin_view` varchar(5) NOT NULL DEFAULT 'N',
                `pretty_view` enum('Y','N') NOT NULL DEFAULT 'N',
                `ow_file_size` int(11) NOT NULL DEFAULT 0,
                `grid_only` enum('Y','N') NOT NULL DEFAULT 'N',
                `list_only` enum('Y','N') NOT NULL DEFAULT 'N',
                `show_labels` enum('Y','N') NOT NULL DEFAULT 'N',
                `show_labels_single` varchar(255) NOT NULL DEFAULT 'N',
                `set_limit` enum('Y','N') NOT NULL DEFAULT 'N',
                `limit_count` int(11) DEFAULT 0,
                `description` text CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
                `react_val` text DEFAULT NULL,
                PRIMARY KEY (`id`),KEY `wp_user` (`wp_user`),KEY `system_name` (`system_name`),KEY `admin_only` (`admin_only`)
            )ENGINE=InnoDB";

			$contestant_register_custom_table = 'CREATE TABLE IF NOT EXISTS ' . WPVC_VOTES_USER_CUSTOM_TABLE . " (
                                    `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                                    `sequence` int(11) NOT NULL DEFAULT 0,
                                    `question_type` enum('TEXT','TEXTAREA','MULTIPLE','SINGLE','DROPDOWN','FILE','DATE') CHARACTER SET utf8 NOT NULL DEFAULT 'TEXT',
                                    `question` text CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
                                    `system_name` varchar(45) DEFAULT NULL,
                                    `response` text CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
                                    `required` enum('Y','N') NOT NULL DEFAULT 'N',
                                    `required_text` text DEFAULT NULL,
                                    `admin_only` enum('Y','N') NOT NULL DEFAULT 'N',
                                    `delete_time` varchar(45) DEFAULT '0',
                                    `react_val` text DEFAULT NULL,
                                    `wp_user` int(22) DEFAULT 1,
                                    PRIMARY KEY (`id`),KEY `wp_user` (`wp_user`),KEY `system_name` (`system_name`),KEY `admin_only` (`admin_only`)
                                    )ENGINE=InnoDB";

			$contestant_track_table = 'CREATE TABLE IF NOT EXISTS ' . WPVC_VOTES_ENTRY_TRACK . ' (
                                        `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                                        `user_id_map` int(11) NOT NULL DEFAULT 0,
                                        `ip` varchar(255) NOT NULL DEFAULT 0,
                                        `count_post` int(11) NOT NULL DEFAULT 1,
                                        `ow_term_id` int(11) NOT NULL DEFAULT 0,
                                        `post_id` int(11) NULL,
                                        PRIMARY KEY (`id`)
                                    ) ENGINE=InnoDB';

			ob_start();
			dbDelta($vote_tbl_sql);
			dbDelta($contestant_custom_table);
			dbDelta($contestant_register_custom_table);
			dbDelta($contestant_track_table);

			$track_show_labels_single = $wpdb->get_results($wpdb->prepare('SHOW COLUMNS FROM `' . WPVC_VOTES_ENTRY_CUSTOM_TABLE . '` LIKE %s', '%' . 'show_labels_single' . '%'));
			if (empty($track_show_labels_single)) {
				$track_show_labels_single_sql = "ALTER TABLE `" . WPVC_VOTES_ENTRY_CUSTOM_TABLE . "` ADD `show_labels_single` VARCHAR(255) NOT NULL DEFAULT 'N' AFTER `pretty_view`";
				$result = $wpdb->query($track_show_labels_single_sql);
			}

			$track_react_val = $wpdb->get_results($wpdb->prepare('SHOW COLUMNS FROM `' . WPVC_VOTES_ENTRY_CUSTOM_TABLE . '` LIKE %s', '%' . 'react_val' . '%'));

			if (empty($track_react_val)) {
				$track_react_val_sql = 'ALTER TABLE `' . WPVC_VOTES_ENTRY_CUSTOM_TABLE . '` ADD `react_val` TEXT NULL DEFAULT NULL AFTER `pretty_view`';
				$result = $wpdb->query($track_react_val_sql);
			}

			$track_description = $wpdb->get_results($wpdb->prepare('SHOW COLUMNS FROM `' . WPVC_VOTES_ENTRY_CUSTOM_TABLE . '` LIKE %s', '%' . 'description' . '%'));
			if (empty($track_description)) {
				$track_description_sql = 'ALTER TABLE `' . WPVC_VOTES_ENTRY_CUSTOM_TABLE . '` ADD `description` TEXT NOT NULL AFTER `pretty_view`';
				$result = $wpdb->query($track_description_sql);
			}

			// ALTER TABLE `votes_custom_field_contestant` CHANGE `wpvc_file_size` `ow_file_size` INT NOT NULL DEFAULT '0';
			$track_wpvc_file_size_rename = $wpdb->get_results($wpdb->prepare('SHOW COLUMNS FROM `' . WPVC_VOTES_ENTRY_CUSTOM_TABLE . '` LIKE %s', '%' . 'wpvc_file_size' . '%'));
			if (!empty($track_wpvc_file_size_rename)) {
				$wpvc_file_size_rename = "ALTER TABLE `" . WPVC_VOTES_ENTRY_CUSTOM_TABLE . "` CHANGE `wpvc_file_size` `ow_file_size` INT NOT NULL DEFAULT '0'";
				$result = $wpdb->query($wpvc_file_size_rename);
			}

			// ALTER TABLE `votes_custom_field_contestant` CHANGE `shwpvc_labels` `show_labels` ENUM('Y','N') NOT NULL DEFAULT 'N';
			$track_shwpvc_labels_rename = $wpdb->get_results($wpdb->prepare('SHOW COLUMNS FROM `' . WPVC_VOTES_ENTRY_CUSTOM_TABLE . '` LIKE %s', '%' . 'shwpvc_labels' . '%'));
			if (!empty($track_shwpvc_labels_rename)) {
				$shwpvc_labels_rename = "ALTER TABLE `" . WPVC_VOTES_ENTRY_CUSTOM_TABLE . "` CHANGE `shwpvc_labels` `show_labels` ENUM('Y','N') NOT NULL DEFAULT 'N'";
				$result = $wpdb->query($shwpvc_labels_rename);
			}

			// ALTER TABLE `votes_custom_field_contestant` CHANGE `question_type` `question_type` ENUM('TEXT','TEXTAREA','MULTIPLE','SINGLE','DROPDOWN','FILE','DATE','TERMS') CHARACTER SET utf8 NOT NULL DEFAULT 'TEXT';
			$addTERMS = "ALTER TABLE `" . WPVC_VOTES_ENTRY_CUSTOM_TABLE . "` CHANGE `question_type` `question_type` ENUM('TEXT','TEXTAREA','MULTIPLE','SINGLE','DROPDOWN','FILE','DATE','TERMS') CHARACTER SET utf8 NOT NULL DEFAULT 'TEXT'";
			$result = $wpdb->query($addTERMS);

			// ALTER TABLE `votes_custom_registeration_contestant` CHANGE `question_type` `question_type` ENUM('TEXT','TEXTAREA','MULTIPLE','SINGLE','DROPDOWN','FILE','DATE') CHARACTER SET utf8 NOT NULL DEFAULT 'TEXT';
			$addFILE_DATE = "ALTER TABLE `" . WPVC_VOTES_USER_CUSTOM_TABLE . "` CHANGE `question_type` `question_type` ENUM('TEXT','TEXTAREA','MULTIPLE','SINGLE','DROPDOWN','FILE','DATE') CHARACTER SET utf8 NOT NULL DEFAULT 'TEXT'";
			$result = $wpdb->query($addFILE_DATE);

			// ALTER TABLE `votes_post_contestant_track` CHANGE `wpvc_term_id` `ow_term_id` INT NOT NULL DEFAULT '0';
			$track_wpvc_term_id_rename = $wpdb->get_results($wpdb->prepare('SHOW COLUMNS FROM `' . WPVC_VOTES_ENTRY_TRACK . '` LIKE  %s', '%' . 'wpvc_term_id' . '%'));
			if (!empty($track_wpvc_term_id_rename)) {
				$wpvc_term_id_rename = "ALTER TABLE `" . WPVC_VOTES_ENTRY_TRACK . "` CHANGE `wpvc_term_id` `ow_term_id` INT NOT NULL DEFAULT '0'";
				$result = $wpdb->query($wpvc_term_id_rename);
			}

			// ALTER TABLE `votes_post_contestant_track` ADD `post_id` INT(11) NULL AFTER `ow_term_id`;
			$trackresult = $wpdb->get_results($wpdb->prepare('SHOW COLUMNS FROM `' . WPVC_VOTES_ENTRY_TRACK . '` LIKE %s', '%' . 'post_id' . '%'));
			if (empty($trackresult)) {
				$post_track_alter = 'ALTER TABLE `' . WPVC_VOTES_ENTRY_TRACK . '` ADD `post_id` INT(11) NULL AFTER `ow_term_id`';
				$result = $wpdb->query($post_track_alter);
			}

			/****** Check for the description and title field already there */
			$field_desc_check     = Wpvc_Installation_Model::wpvc_voting_get_contestant_desc();
			$field_title_check    = Wpvc_Installation_Model::wpvc_voting_get_title_desc();
			$field_video_check    = Wpvc_Installation_Model::wpvc_voting_get_wpvc_video_url();
			$field_music_check    = Wpvc_Installation_Model::wpvc_voting_get_wpvc_music_url();
			$field_email_check    = Wpvc_Installation_Model::wpvc_voting_get_wpvc_email_address();
			$field_image_check    = Wpvc_Installation_Model::wpvc_voting_get_wpvc_image();
			$field_muci_url_check = Wpvc_Installation_Model::wpvc_voting_get_music_url_alternate();
			$field_terms_check    = Wpvc_Installation_Model::wpvc_voting_get_terms();

			if (empty($field_desc_check)) {
				// Add the Custom Field in the Table VOTES_ENTRY_CUSTOM_TABLE
				$wpdb->insert(
					WPVC_VOTES_ENTRY_CUSTOM_TABLE,
					array(
						'question_type' => 'TEXTAREA',
						'question'      => 'Description',
						'system_name'   => 'contestant-desc',
						'required'      => 'Y',
						'admin_only'    => 'Y',
						'admin_view'    => 'Y',
					),
					array(
						'%s',
						'%s',
						'%s',
						'%s',
						'%s',
						'%s',
					)
				);
			}

			if (empty($field_title_check)) {
				// Add the Custom Field in the Table VOTES_ENTRY_CUSTOM_TABLE
				$wpdb->insert(
					WPVC_VOTES_ENTRY_CUSTOM_TABLE,
					array(
						'sequence'      => 0,
						'question_type' => 'TEXT',
						'question'      => 'Title',
						'system_name'   => 'contestant-title',
						'required'      => 'Y',
						'admin_only'    => 'Y',
						'admin_view'    => 'Y',
					),
					array(
						'%s',
						'%s',
						'%s',
						'%s',
						'%s',
						'%s',
						'%s',
					)
				);
			}

			if (empty($field_terms_check)) {
				// Add the Custom Field in the Table VOTES_ENTRY_CUSTOM_TABLE
				$wpdb->insert(
					WPVC_VOTES_ENTRY_CUSTOM_TABLE,
					array(
						'sequence'      => 0,
						'question_type' => 'TERMS',
						'question'      => 'Terms & Conditions',
						'system_name'   => 'contestant-terms',
						'required'      => 'Y',
						'admin_only'    => 'Y',
						'admin_view'    => 'Y',
					),
					array(
						'%s',
						'%s',
						'%s',
						'%s',
						'%s',
						'%s',
						'%s',
					)
				);
			}

			if (empty($field_video_check)) {
				// Add the Custom Field in the Table VOTES_ENTRY_CUSTOM_TABLE
				$wpdb->insert(
					WPVC_VOTES_ENTRY_CUSTOM_TABLE,
					array(
						'sequence'      => 0,
						'question_type' => 'TEXT',
						'question'      => 'Video Link URL',
						'system_name'   => 'contestant-ow_video_url',
						'required'      => 'Y',
						'admin_only'    => 'Y',
						'admin_view'    => 'Y',
					),
					array(
						'%s',
						'%s',
						'%s',
						'%s',
						'%s',
						'%s',
						'%s',
					)
				);
			}

			if (empty($field_music_check)) {
				// Add the Custom Field in the Table VOTES_ENTRY_CUSTOM_TABLE
				$wpdb->insert(
					WPVC_VOTES_ENTRY_CUSTOM_TABLE,
					array(
						'sequence'      => 4,
						'question_type' => 'FILE',
						'question'      => 'Music Upload URL',
						'system_name'   => 'contestant-ow_music_url',
						'required'      => 'Y',
						'admin_only'    => 'Y',
						'admin_view'    => 'Y',
						'response'      => 'mp3',
					),
					array(
						'%s',
						'%s',
						'%s',
						'%s',
						'%s',
						'%s',
						'%s',
						'%s',
					)
				);
			}

			// Update mp3 value in contestant-ow_music_url - For Upgrading version in plugin
			$wpdb->update(
				WPVC_VOTES_ENTRY_CUSTOM_TABLE,
				array(
					'response' => 'mp3',    // string
				),
				array('system_name' => 'contestant-ow_music_url'),
				array(
					'%s',   // value1
				),
				array('%s')
			);

			if (empty($field_email_check)) {
				// Add the Custom Field in the Table EMAIl_ADDRESS
				$wpdb->insert(
					WPVC_VOTES_ENTRY_CUSTOM_TABLE,
					array(
						'sequence'      => 3,
						'question_type' => 'TEXT',
						'question'      => 'Email Address of Contestant',
						'system_name'   => 'contestant-email_address',
						'required'      => 'Y',
						'admin_only'    => 'Y',
						'admin_view'    => 'N',
						'required_text' => 'Enter the Email Address of Contestant',
					),
					array(
						'%s',
						'%s',
						'%s',
						'%s',
						'%s',
						'%s',
						'%s',
						'%s',
					)
				);
			}

			if (empty($field_image_check)) {
				// Add the Custom Field in the Table VOTES_ENTRY_CUSTOM_TABLE
				$wpdb->insert(
					WPVC_VOTES_ENTRY_CUSTOM_TABLE,
					array(
						'sequence'      => 4,
						'question_type' => 'FILE',
						'question'      => 'Image',
						'system_name'   => 'contestant-image',
						'required'      => 'Y',
						'admin_only'    => 'Y',
						'admin_view'    => 'Y',
						'response'      => '',
						'required_text' => 'Enter the Image of Contestant',
					),
					array(
						'%s',
						'%s',
						'%s',
						'%s',
						'%s',
						'%s',
						'%s',
						'%s',
						'%s',
					)
				);
			}

			if (empty($field_muci_url_check)) {
				// Add the Custom Field in the Table VOTES_ENTRY_CUSTOM_TABLE
				$wpdb->insert(
					WPVC_VOTES_ENTRY_CUSTOM_TABLE,
					array(
						'sequence'      => 5,
						'question_type' => 'TEXT',
						'question'      => 'Music Link URL',
						'system_name'   => 'contestant-ow_music_url_link',
						'required'      => 'Y',
						'admin_only'    => 'Y',
						'admin_view'    => 'Y',
						'response'      => '',
						'required_text' => 'Enter the Music URL of Contestant',
					),
					array(
						'%s',
						'%s',
						'%s',
						'%s',
						'%s',
						'%s',
						'%s',
						'%s',
						'%s',
					)
				);
			}

			$react_val = '{"text_field_type":"text","text_area_row":"3","value_placement":"Bottom","upload_icon":"camera_alt","datepicker_only":"MM/dd/yyyy"}';

			$update_react     = $wpdb->prepare('UPDATE ' . WPVC_VOTES_ENTRY_CUSTOM_TABLE . ' SET `react_val` = %s WHERE `react_val` is NULL', $react_val);
			$update_react_reg = $wpdb->prepare('UPDATE ' . WPVC_VOTES_USER_CUSTOM_TABLE . ' SET `react_val` = %s WHERE `react_val` is NULL', $react_val);
			$wpdb->query($update_react);
			$wpdb->query($update_react_reg);
		}

		static function wpvc_voting_get_contestant_desc()
		{
			global $wpdb;
			$sql     = $wpdb->prepare('SELECT * FROM ' . WPVC_VOTES_ENTRY_CUSTOM_TABLE . ' WHERE system_name = %s', 'contestant-desc');
			$desc_rs = $wpdb->get_results($sql);
			return $desc_rs;
		}

		static function wpvc_voting_get_title_desc()
		{
			global $wpdb;
			$sql     = $wpdb->prepare('SELECT * FROM ' . WPVC_VOTES_ENTRY_CUSTOM_TABLE . ' WHERE system_name = %s', 'contestant-title');
			$desc_rs = $wpdb->get_results($sql);
			return $desc_rs;
		}

		static function wpvc_voting_get_terms()
		{
			global $wpdb;
			$sql     = $wpdb->prepare('SELECT * FROM ' . WPVC_VOTES_ENTRY_CUSTOM_TABLE . ' WHERE system_name = %s', 'contestant-terms');
			$desc_rs = $wpdb->get_results($sql);
			return $desc_rs;
		}

		static function wpvc_voting_get_wpvc_video_url()
		{
			global $wpdb;
			$sql     = $wpdb->prepare('SELECT * FROM ' . WPVC_VOTES_ENTRY_CUSTOM_TABLE . ' WHERE system_name = %s', 'contestant-ow_video_url');
			$desc_rs = $wpdb->get_results($sql);
			return $desc_rs;
		}

		static function wpvc_voting_get_wpvc_music_url()
		{
			global $wpdb;
			$sql     = $wpdb->prepare('SELECT * FROM ' . WPVC_VOTES_ENTRY_CUSTOM_TABLE . ' WHERE system_name = %s', 'contestant-ow_music_url');
			$desc_rs = $wpdb->get_results($sql);
			return $desc_rs;
		}

		static function wpvc_voting_get_wpvc_email_address()
		{
			global $wpdb;
			$sql     = $wpdb->prepare('SELECT * FROM ' . WPVC_VOTES_ENTRY_CUSTOM_TABLE . ' WHERE system_name = %s', 'contestant-email_address');
			$desc_rs = $wpdb->get_results($sql);
			return $desc_rs;
		}

		static function wpvc_voting_get_wpvc_image()
		{
			global $wpdb;
			$sql     = $wpdb->prepare('SELECT * FROM ' . WPVC_VOTES_ENTRY_CUSTOM_TABLE . ' WHERE system_name = %s', 'contestant-image');
			$desc_rs = $wpdb->get_results($sql);
			return $desc_rs;
		}

		static function wpvc_voting_get_music_url_alternate()
		{
			global $wpdb;
			$sql     = $wpdb->prepare('SELECT * FROM ' . WPVC_VOTES_ENTRY_CUSTOM_TABLE . ' WHERE system_name = %s', 'contestant-ow_music_url_link');
			$desc_rs = $wpdb->get_results($sql);
			return $desc_rs;
		}

		public static function wpvc_delete_all_contestants_owvoting()
		{
			global $wpdb;

			$mycustomposts = get_posts(
				array(
					'post_type'   => WPVC_VOTES_TYPE,
					'numberposts' => -1,
					'post_status' => 'any',
				)
			);
			if (count($mycustomposts) > 0) {
				foreach ($mycustomposts as $mypost) {
					wp_delete_post($mypost->ID, true);
				}
			}
			$taxonomy = WPVC_VOTES_TAXONOMY;

			$terms = get_terms($taxonomy, array('hide_empty' => false));
			$count = count($terms);
			if ($count > 0) {
				foreach ($terms as $term) {
					wp_delete_term($term->term_id, $taxonomy);
					delete_option($term->term_id . '_' . WPVC_VOTES_TAXACTIVATIONLIMIT);
					delete_option($term->term_id . '_' . WPVC_VOTES_TAXSTARTTIME);
					delete_option($term->term_id . '_' . WPVC_VOTES_TAXEXPIRATIONFIELD);
					delete_option($term->term_id . '_' . WPVC_VOTES_SETTINGS);
				}
			}
			delete_option(WPVC_VOTES_SETTINGS);
			delete_option(WPVC_VOTES_GENERALSTARTTIME);
			delete_option(WPVC_VOTES_GENERALEXPIRATIONFIELD);

			// Delete all tables on deactivation.
			$vote_table = 'DROP TABLE IF EXISTS ' . WPVC_VOTES_TBL;
			$wpdb->query($wpdb->prepare($vote_table));

			$contestant_cutom_tbl = 'DROP TABLE IF EXISTS ' . WPVC_VOTES_ENTRY_CUSTOM_TABLE;
			$wpdb->query($wpdb->prepare($contestant_cutom_tbl));

			$contest_reg_tbl = 'DROP TABLE IF EXISTS ' . WPVC_VOTES_USER_CUSTOM_TABLE;
			$wpdb->query($wpdb->prepare($contest_reg_tbl));

			$contest_entry_trk = 'DROP TABLE IF EXISTS ' . WPVC_VOTES_ENTRY_TRACK;
			$wpdb->query($wpdb->prepare($contest_entry_trk));
		}
	}
} else {
	die('<h2>' . esc_html(__('Failed to load Voting Installation model')) . '</h2>');
}

return new Wpvc_Installation_Model();
