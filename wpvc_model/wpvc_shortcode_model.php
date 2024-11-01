<?php
if (!class_exists('Wpvc_Shortcode_Model')) {
	/**
	 *  Shortcode Model.
	 */
	class Wpvc_Shortcode_Model
	{
		/**
		 *  Get Settings using term ID.
		 *
		 * @param int $category_id Category ID.
		 */
		public static function wpvc_settings_page_json($category_id = null)
		{
			global $wpdb;
			$options = get_option(WPVC_VOTES_SETTINGS);
			return $options;
		}
		/**
		 *  Get show contestant page .
		 *
		 * @param array $show_cont_args Shortcode attributes.
		 */
		public static function wpvc_show_contestant_page_json($show_cont_args)
		{
			global $wpdb;

			if (isset($show_cont_args['paged']) && $show_cont_args['paged'] > 0) {
				$paged = $show_cont_args['paged'];
			} else {
				$paged = 1;
			}

			// exclude attribute.
			if (isset($show_cont_args['exclude']) && $show_cont_args['exclude'] != null) :
				$excluded_ids = explode(',', $show_cont_args['exclude']);
			else :
				$excluded_ids = array();
			endif;

			// include attribute.
			if (isset($show_cont_args['include']) && $show_cont_args['include'] != null) :
				$included_ids = explode(',', $show_cont_args['include']);
			else :
				$included_ids = array();
			endif;

			$postargs = array(
				'post_type'      => WPVC_VOTES_TYPE,
				'post_status'    => 'publish',
				'posts_per_page' => $show_cont_args['postperpage'],
				'tax_query'      => array(
					array(
						'taxonomy'         => $show_cont_args['taxonomy'],
						'field'            => 'id',
						'terms'            => $show_cont_args['id'],
						'include_children' => false,
					),
				),
				'paged'          => $paged,
				'post__not_in'   => $excluded_ids,
				'post__in'       => $included_ids,
			);

			if ($show_cont_args['orderby'] == 'votes') {
				$postargs['meta_key'] = WPVC_VOTES_CUSTOMFIELD;
				$postargs['orderby']  = 'meta_value_num';
				$postargs['order']    = $show_cont_args['order'];
			} elseif ($show_cont_args['orderby'] == 'top') {
				$postargs['meta_key'] = WPVC_VOTES_CUSTOMFIELD;
				$postargs['orderby']  = 'meta_value_num';
				$postargs['order']    = 'DESC';
			} elseif ($show_cont_args['orderby'] == 'bottom') {
				$postargs['meta_key'] = WPVC_VOTES_CUSTOMFIELD;
				$postargs['orderby']  = 'meta_value_num';
				$postargs['order']    = 'ASC';
			} else {
				$postargs['orderby'] = $show_cont_args['orderby'];
				$postargs['order']   = $show_cont_args['order'];
			}
			$contest_post = new WP_Query($postargs);
			return $contest_post;
		}
		/**
		 *  Get custom field by id .
		 *
		 * @param int $id Custom field ID.
		 */
		public static function wpvc_custom_fields_by_id($id)
		{
			global $wpdb;
			$admin_only    = 'Y';
			$delete_time   = 0;
			$sql           = $wpdb->prepare('SELECT * FROM ' . WPVC_VOTES_ENTRY_CUSTOM_TABLE . ' WHERE delete_time = %d AND id= %d AND admin_only=%s', $delete_time, $id, $admin_only);
			$custom_fields = $wpdb->get_row($sql, ARRAY_A);
			return $custom_fields;
		}
		/**
		 *  Get custom field files .
		 *
		 * @param array $get_array_val File attributes.
		 */
		public static function wpvc_custom_fields_files($get_array_val)
		{
			$custom_fields = array(array('system_name' => 'contestant-image'));
			return $custom_fields;
		}

		/**
		 *  Get custom field files .
		 *
		 * @param array $contest Custom Fields.
		 * @param int   $musicfileenable Custom Fields.
		 */
		public static function wpvc_custom_fields_by_contest($contest, $musicfileenable = NULL)
		{
			global $wpdb;
			$sql = "SELECT * FROM " . WPVC_VOTES_ENTRY_CUSTOM_TABLE . " WHERE system_name LIKE '%contestant%' AND delete_time = 0 order by sequence ASC";
			$custom_fields = $wpdb->get_results($sql, ARRAY_A);
			$wpvc_video_extension = get_option('_ow_video_extension');

			$return_array = array();
			if (!empty($custom_fields)) {
				foreach ($custom_fields as $custom) {
					$needed_array = array('contestant-title', 'contestant-image', 'contestant-desc');
					switch ($contest) {
						case 'contest':
							$needed_array = array('contestant-title');
							break;
					}

					if (in_array($custom['system_name'], $needed_array)) {
						$react_values = json_decode($custom['react_val']);
						$comma_sep_values = $custom['response'] ? explode(',', $custom['response']) : "";
						$custom = array_merge($custom, array('react_values' => $react_values, 'drop_values' => $comma_sep_values));
						$return_array[] = $custom;
					}
				}
			}
			return $return_array;
		}
		/**
		 *  Insert contestants .
		 *
		 * @param int    $category_id Custom Fields.
		 * @param array  $insertdata Custom Fields.
		 * @param array  $userdata Custom Fields.
		 * @param string $post_status Custom Fields.
		 */
		public static function wpvc_insert_contestants($category_id, $insertdata, $userdata = '', $post_status = '')
		{
			global $wpvc_user_id;
			$vote_opt = get_option(WPVC_VOTES_SETTINGS);

			$args    = array(
				'post_author'  => $wpvc_user_id,
				'post_content' => $insertdata['contestant-desc'],
				'post_status'  => 'pending',
				'post_type'    => WPVC_VOTES_TYPE,
				'post_title'   => $insertdata['contestant-title'],
			);
			$post_id = wp_insert_post($args);
			wp_set_post_terms($post_id, $category_id, WPVC_VOTES_TAXONOMY);
			update_post_meta($post_id, WPVC_VOTES_POST, $insertdata);

			if (is_array($insertdata)) {
				foreach ($insertdata as $key => $post_meta) {
					update_post_meta($post_id, $key, $post_meta);
				}
			}

			return $post_id;
		}
		/**
		 *  Insert contestants .
		 *
		 * @param int   $post_id Custom Fields.
		 * @param array $insertdata Custom Fields.
		 */
		public static function wpvc_update_contestants($post_id, $insertdata)
		{

			$args    = array(
				'ID'           => $post_id,
				'post_content' => $insertdata['contestant-desc'],
				'post_status'  => 'pending',
				'post_type'    => WPVC_VOTES_TYPE,
				'post_title'   => $insertdata['contestant-title'],
			);
			$post_id = wp_update_post($args);
			update_post_meta($post_id, WPVC_VOTES_POST, $insertdata);
			if (is_array($insertdata)) {
				foreach ($insertdata as $key => $post_meta) {
					update_post_meta($post_id, $key, $post_meta);
				}
			}

			return $post_id;
		}
		/**
		 *  Get total post count .
		 *
		 * @param int $term_id Custom Fields.
		 * @param int $post_per_page Custom Fields.
		 */
		public static function wpvc_get_total_post_count($term_id = null, $post_per_page = null)
		{
			if ($term_id != null) {
				$postargs = array(
					'post_type'   => WPVC_VOTES_TYPE,
					'post_status' => 'publish',
					'tax_query'   => array(
						array(
							'taxonomy'         => WPVC_VOTES_TAXONOMY,
							'field'            => 'id',
							'terms'            => $term_id,
							'include_children' => false,
						),
					),
					'numberposts' => -1,
				);
			} else {
				$postargs = array(
					'post_type'   => WPVC_VOTES_TYPE,
					'post_status' => 'published',
					'numberposts' => -1,
				);
			}
			if ($post_per_page != 0) {
				$total_num    = count(get_posts($postargs));
				$pagin_array  = array();
				$count_pagers = ceil($total_num / $post_per_page);
				if ($total_num > $post_per_page) {
					$pagin_array = array(
						'total_posts'   => $total_num,
						'per_page_post' => $post_per_page,
						'page_nums'     => $count_pagers,
					);
				}
			} else {
				$pagin_array = array(
					'total_posts'   => 0,
					'per_page_post' => 0,
					'page_nums'     => 0,
				);
			}
			return $pagin_array;
		}
		/**
		 *  Get category options .
		 *
		 * @param array $show_cont_args Custom Fields.
		 * @param int   $post_id Custom Fields.
		 * @param int   $flag Custom Fields.
		 */
		public static function wpvc_get_category_options_and_values($show_cont_args, $post_id = null, $flag = null)
		{

			$_SESSION['wpvc_contestant_seed'] = 0;

			// To get category options.
			if ($flag == 'profile') {
				$profile_id = $show_cont_args['contests'];
				if ($profile_id != '') {
					$show_cont_args['id'] = $profile_id;
				}
			}

			if ($post_id != null && empty($show_cont_args)) {
				$term = get_the_terms($post_id, WPVC_VOTES_TAXONOMY);
				if (is_array($term)) {
					$show_cont_args['id'] = $term[0]->term_id;
				}
			}

			$vote_opt = get_option(WPVC_VOTES_SETTINGS);
			if ($vote_opt) {
				$category_options = get_term_meta($show_cont_args['id']);
				$align_category   = array();
				if (is_array($category_options)) {
					foreach ($category_options as $key => $val) {
						if ($key == 'contest_rules') {
							$align_category[$key] = format_to_edit($val[0], true);
						} else {
							$align_category[$key] = maybe_unserialize($val[0]);
						}
					}
				}

				$inter     = Wpvc_Common_Shortcode_Controller::wpvc_vote_get_thumbnail_sizes($vote_opt['common']['short_cont_image']);
				$height_tr = explode('--', $inter);
				$width_t   = $height_tr[0];
				$height_t  = $height_tr[1];
				$height    = $height_t ? $height_t : '';
				$width     = $width_t ? $width_t : '';

				$title             = $vote_opt['common']['title'] ? $vote_opt['common']['title'] : null;
				$orderby           = $vote_opt['common']['orderby'] ? $vote_opt['common']['orderby'] : 'votes_count';
				$order             = $vote_opt['common']['order'] ? $vote_opt['common']['order'] : 'desc';
				$onlyloggedinuser  = ($vote_opt['contest']['onlyloggedinuser'] == 'on') ? 1 : 0;
				$onlyloggedsubmit  = 1;
				$pagination_option = $vote_opt['pagination']['contestant_per_page'];
				$openform          = $vote_opt['common']['vote_entry_form'];

				$votes_start_time     = $align_category['votes_starttime'];
				$votes_expiration     = $align_category['votes_expiration'];
				$tax_hide_photos_live =  array_key_exists('tax_hide_photos_live', $align_category) ? $align_category['tax_hide_photos_live'] : 'off';

				if (isset($show_cont_args['orderby']) && sanitize_text_field($show_cont_args['orderby']) == '') {
					$show_cont_args['orderby'] = $orderby;
				}
				if (isset($show_cont_args['order']) && sanitize_text_field($show_cont_args['order']) == '') {
					$show_cont_args['order'] = $order;
				}

				$category_thumb = 1;
				$show_form    = 0;
				$current_time = current_time('timestamp', 0);
				if ($tax_hide_photos_live == 'on') {
					// Until live check with current time.
					if (($votes_start_time != '' && strtotime($votes_start_time) > $current_time)) {
						$category_thumb = 0;
					} elseif ($votes_start_time == '') {
						$category_thumb = 1;
						$show_form      = 1;
					} else {
						$category_thumb = 1;
					}
				} elseif (($votes_start_time != '' && strtotime($votes_start_time) > $current_time) || $votes_start_time == '') {
					$category_thumb = 1;
					$show_form      = 1;
				} elseif (($votes_expiration != '' && strtotime($votes_expiration) < $current_time)) {
					$show_form = 0;
				} else {
					$category_thumb = 1;
				}

				$category_term_disp = ($align_category['termdisplay'] == 'on') ? 1 : 0;

				$skip_payment_array = array('ow_category_paypal_settings', 'payment_paypal_entry_amount', 'ow_category_stripe_settings', 'payment_stripe_entry_amount', 'ow_category_paystack_settings', 'payment_paystack_entry_amount', 'category_coupon_settings');
				$align_category     = array_diff_key($align_category, array_flip($skip_payment_array));

				$sort_by = isset($show_cont_args['orderby']) ? 0 : 1;
				$search  = isset($show_cont_args['search']) ? 1 : 0;

				$pagination_count = (isset($show_cont_args['postperpage']) && sanitize_text_field($show_cont_args['postperpage']) != '') ? sanitize_text_field($show_cont_args['postperpage']) : $vote_opt['pagination']['contestant_per_page'];
				$pagin_response   = self::wpvc_get_total_post_count($show_cont_args['id'], $pagination_count);

				if (isset($show_cont_args['orderby']) && sanitize_text_field($show_cont_args['orderby']) == 'votes') {
					$show_cont_args['orderby'] = 'votes_count';
					$show_cont_args['order']   = sanitize_text_field($show_cont_args['order']) ? strtolower(sanitize_text_field($show_cont_args['order'])) : $vote_opt['common']['order'];
				}

				$showcount = 10;
				// For upcoming contestants.
				if ($flag == 'upcoming') {
					if ($votes_start_time == '' || strtotime($votes_start_time) < $current_time) {
						return 'no_upcoming_contest';
					}
				}

				if ($flag == 'endcontest') {
					if ($votes_expiration == '' || strtotime($votes_expiration) > $current_time) {
						return 'no_ended_contest';
					}
				}
				if ($flag == 'profile') {
					$show_form = $show_cont_args['form'];
				}

				if ($flag == 'topcontest') {
					$showcount = $show_cont_args['showcount'];
				}

				if (isset($show_cont_args['order']) && sanitize_text_field($show_cont_args['order']) != '') {
					$show_cont_args['order'] = strtolower(sanitize_text_field($show_cont_args['order']));
				}
				// Check with settings and update same.
				$show_cont_args = wp_parse_args(
					$show_cont_args,
					array(
						'height'               => $height,
						'width'                => $width,
						'title'                => $title,
						'orderby'              => $orderby,
						'order'                => strtolower($order),
						'postperpage'          => $pagination_option,
						'taxonomy'             => WPVC_VOTES_TAXONOMY,
						'id'                   => 0,
						'paged'                => 1,
						'ajaxcontent'          => 0,
						'showtimer'            => 1,
						'showform'             => $show_form,

						// Newly added.
						'openform'             => $openform,
						'showgallery'          => 1,
						'showrules'            => 0,
						'showprofile'          => 1,
						'flag_code'            => $flag,
						'showcount'            => $showcount,

						'forcedisplay'         => 1,
						'thumb'                => $category_thumb,
						'termdisplay'          => $category_term_disp,
						'pagination'           => 1,
						'sort_by'              => $sort_by,
						'onlyloggedinuser'     => $onlyloggedinuser,
						'onlyloggedsubmit'     => $onlyloggedsubmit,
						'tax_hide_photos_live' => $tax_hide_photos_live,
						'navbar'               => 1,
						'contest_type'         => $align_category['imgcontest'],
						'view'                 => 'grid',
						'search'               => $search,
						'start_time'           => $votes_start_time,
						'end_time'             => $votes_expiration,
						'showcontestants'      => 1,
						'server_time'          => wp_timezone(),

						// For react.
						'user_logged'          => is_user_logged_in(),
						'user_id_profile'      => get_current_user_id(),
						'user_can_register'    => get_option('users_can_register'),
						'allcatoption'         => apply_filters('cat_extension_update_values', $align_category, $show_cont_args['id']),
						'pagin_response'       => $pagin_response,
						'contest_url'          => isset($_COOKIE['wpvc_contestant_URL']) ? esc_url_raw(wp_unslash($_COOKIE['wpvc_contestant_URL'])) : '',
						'override_view'        => isset($_GET['view']) ? sanitize_text_field(wp_unslash($_GET['view'])) : 'grid',
						'extension_values'     => apply_filters('extension_values_hook', array()),
					)
				);
			}

			return $show_cont_args;
		}
		/**
		 *  For Add contestants.
		 *
		 * @param array $show_cont_args Custom Fields.
		 * @param int   $post_id Custom Fields.
		 */
		public static function wpvc_get_category_options_and_values_addcontestants($show_cont_args, $post_id = null)
		{

			if ($post_id != null && empty($show_cont_args)) {
				$term = get_the_terms($post_id, WPVC_VOTES_TAXONOMY);
				if (is_array($term)) {
					$show_cont_args['id'] = $term[0]->term_id;
				}
			}

			$vote_opt = get_option(WPVC_VOTES_SETTINGS);
			if ($vote_opt) {
				$category_options = get_term_meta($show_cont_args['id']);
				$align_category   = array();
				$imgcontest       = get_term_meta($show_cont_args['id'], 'imgcontest', true);
				$musicfileenable  = get_term_meta($show_cont_args['id'], 'musicfileenable', true);
				if (is_array($category_options)) {
					foreach ($category_options as $key => $val) {
						if ($key == 'contest_rules') {
							$align_category[$key] = format_to_edit($val[0], true);
						} else {
							$align_category[$key] = maybe_unserialize($val[0]);
						}
					}
				}

				$title             = $vote_opt['common']['title'] ? $vote_opt['common']['title'] : null;
				$orderby           = $vote_opt['common']['orderby'] ? $vote_opt['common']['orderby'] : 'votes_count';
				$order             = $vote_opt['common']['order'] ? $vote_opt['common']['order'] : 'desc';
				$onlyloggedinuser  = ($vote_opt['contest']['onlyloggedinuser'] == 'on') ? 1 : 0;
				$onlyloggedsubmit  = 1;
				$pagination_option = $vote_opt['pagination']['contestant_per_page'];
				$openform          = $vote_opt['common']['vote_entry_form'];

				if ($show_cont_args['orderby'] == '') {
					$show_cont_args['orderby'] = $orderby;
				}
				if ($show_cont_args['order'] == '') {
					$show_cont_args['order'] = $order;
				}

				$votes_start_time     = $align_category['votes_starttime'];
				$votes_expiration     = $align_category['votes_expiration'];
				$tax_hide_photos_live = ($align_category['tax_hide_photos_live'] != '') ? $align_category['tax_hide_photos_live'] : 'off';

				$show_form = 0;
				$current_time = current_time('timestamp', 0);
				if ($tax_hide_photos_live == 'on') {
					// Until live check with current time.
					if (($votes_start_time != '' && strtotime($votes_start_time) > $current_time)) {
						$category_thumb = 0;
					} else {
						$category_thumb = 1;
					}
				} elseif (($votes_start_time != '' && strtotime($votes_start_time) > $current_time) || $votes_start_time == '') {
					$category_thumb = 1;
					$show_form      = 1;
				} else {
					$category_thumb = 1;
				}

				$show_form = ($show_cont_args['displayform'] != null) ? $show_cont_args['displayform'] : $show_form;

				$category_term_disp = ($align_category['termdisplay'] == 'on') ? 1 : 0;

				$skip_payment_array = array('ow_category_paypal_settings', 'payment_paypal_entry_amount', 'ow_category_stripe_settings', 'payment_stripe_entry_amount', 'ow_category_paystack_settings', 'payment_paystack_entry_amount', 'category_coupon_settings');
				$align_category     = array_diff_key($align_category, array_flip($skip_payment_array));

				$sort_by = isset($show_cont_args['orderby']) ? 0 : 1;
				$search  = isset($show_cont_args['search']) ? 1 : 0;

				$pagination_count = ($show_cont_args['postperpage'] != '') ? $show_cont_args['postperpage'] : $vote_opt['pagination']['contestant_per_page'];
				$pagin_response   = self::wpvc_get_total_post_count($show_cont_args['id'], $pagination_count);
				if ($show_cont_args['orderby'] == 'votes') {
					$show_cont_args['orderby'] = 'votes_count';
					$show_cont_args['order']   = strtolower($show_cont_args['order']);
				}

				if ($show_cont_args['order'] != '') {
					$show_cont_args['order'] = strtolower($show_cont_args['order']);
				}
				// Check with settings and update same.
				$show_cont_args = wp_parse_args(
					$show_cont_args,
					array(
						'title'                => $title,
						'orderby'              => $orderby,
						'order'                => strtolower($order),
						'postperpage'          => $pagination_option,
						'taxonomy'             => WPVC_VOTES_TAXONOMY,
						'id'                   => 0,
						'paged'                => 1,
						'ajaxcontent'          => 0,
						'showtimer'            => 1,
						'displayform'          => $show_form,
						// Newly added.
						'openform'             => $openform,
						'showgallery'          => 0,
						'showrules'            => 0,
						'showtop'              => 0,
						'showprofile'          => 0,
						'showcontestants'      => 0,

						'forcedisplay'         => 1,
						'thumb'                => $category_thumb,
						'termdisplay'          => $category_term_disp,
						'pagination'           => 1,
						'sort_by'              => $sort_by,
						'onlyloggedinuser'     => $onlyloggedinuser,
						'onlyloggedsubmit'     => $onlyloggedsubmit,
						'tax_hide_photos_live' => $tax_hide_photos_live,
						'navbar'               => 1,
						'contest_type'         => $align_category['imgcontest'],
						'view'                 => 'grid',
						'search'               => $search,
						'start_time'           => $votes_start_time,
						'end_time'             => $votes_expiration,

						// For react.
						'user_logged'          => is_user_logged_in(),
						'user_id_profile'      => get_current_user_id(),
						'user_can_register'    => get_option('users_can_register'),
						'allcatoption'         => apply_filters('cat_extension_update_values', $align_category, $show_cont_args['id']),
						'pagin_response'       => $pagin_response,
						'contest_url'          => isset($_COOKIE['wpvc_contestant_URL']) ? esc_url_raw(wp_unslash($_COOKIE['wpvc_contestant_URL'])) : '',
						'override_view'        => isset($_GET['view']) ? sanitize_text_field(wp_unslash($_GET['view'])) : 'grid',
						'extension_values'     => apply_filters('extension_values_hook', array()),
					)
				);
			}
			return $show_cont_args;
		}
	}
} else {
	die('<h2>' . esc_html(__('Failed to load Voting Shortcode model')) . '</h2>');
}

return new Wpvc_Shortcode_Model();
