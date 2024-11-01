<?php
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}
if (!class_exists('Wpvc_Front_Rest_Register_Controller')) {
	/**
	 * Admin Rest Controller.
	 */
	class Wpvc_Front_Rest_Register_Controller
	{
		/**
		 * Controller Contructor.
		 */
		public function __construct()
		{
			add_action('rest_api_init', array($this, 'wpvc_front_rest_api_register'));
			add_filter('rest_prepare_contestants', array($this, 'wpvc_contestant_meta_filters'), 10, 3);
			add_filter('rest_contestants_collection_params', array($this, 'wpvc_filter_add_rest_orderby_params'), 10, 2);
			add_filter('rest_contestants_query', array($this, 'wpvc_filter_rest_contestants_query'), 10, 2);

			include_once ABSPATH . 'wp-admin/includes/plugin.php';
			if (is_plugin_active('js_composer/js_composer.php') && defined('WPB_VC_VERSION')) {
				WPBMap::addAllMappedShortcodes();
			}
		}
		/**
		 * API Register.
		 */
		public function wpvc_front_rest_api_register()
		{
			$GLOBALS['wpvc_user_id'] = get_current_user_id();
			/****************************** Front end Rest */
			register_rest_route(
				'wpvc-voting/v1',
				'/wpvcgetshowcontestant',
				array(
					'methods'       => WP_REST_Server::ALLMETHODS,
					'callback'      => array('Wpvc_Front_Rest_Actions_Controller', 'wpvc_callback_showallcontestants'),
					'show_in_index' => false,
				)
			);

			register_rest_route(
				'wpvc-voting/v1',
				'/wpvcsubmitentry',
				array(
					'methods'       => WP_REST_Server::CREATABLE,
					'callback'      => array('Wpvc_Front_Rest_Actions_Controller', 'wpvc_callback_submit_entry'),
					'show_in_index' => false,
				)
			);

			register_rest_route('wpvc-voting/v1', '/wpvcuploadfiles', [
				'methods'  => WP_REST_Server::CREATABLE,
				'callback' => array('Wpvc_Front_Rest_Actions_Controller', 'wpvc_upload_files'),
				'show_in_index' => FALSE
			]);

			register_rest_route(
				'wpvc-voting/v1',
				'/wpvcsavevotes',
				array(
					'methods'       => WP_REST_Server::CREATABLE,
					'callback'      => array('Wpvc_Front_Rest_Actions_Controller', 'wpvc_callback_save_votes'),
					'show_in_index' => false,
				)
			);

			register_rest_route(
				'wpvc-voting/v1',
				'/wpvcsendemail',
				array(
					'methods'       => WP_REST_Server::CREATABLE,
					'callback'      => array('Wpvc_Front_Rest_Actions_Controller', 'wpvc_callback_send_email'),
					'show_in_index' => false,
				)
			);

			register_rest_route(
				'wpvc-voting/v1',
				'/wpvclogon',
				array(
					'methods'       => WP_REST_Server::CREATABLE,
					'callback'      => array('Wpvc_Front_Rest_Actions_Controller', 'wpvc_callback_user_logon'),
					'show_in_index' => false,
				)
			);

			register_rest_route(
				'wpvc-voting/v1',
				'/wpvcregister',
				array(
					'methods'       => WP_REST_Server::CREATABLE,
					'callback'      => array('Wpvc_Front_Rest_Actions_Controller', 'wpvc_callback_user_register'),
					'show_in_index' => false,
				)
			);

			register_rest_route(
				'wpvc-voting/v1',
				'/wpvcgetregister',
				array(
					'methods'       => WP_REST_Server::CREATABLE,
					'callback'      => array('Wpvc_Front_Rest_Actions_Controller', 'wpvc_callback_user_get_register'),
					'show_in_index' => false,
				)
			);

			register_rest_route(
				'wpvc-voting/v1',
				'/wpvcresetpassword',
				array(
					'methods'       => WP_REST_Server::CREATABLE,
					'callback'      => array('Wpvc_Front_Rest_Actions_Controller', 'wpvc_callback_reset_password'),
					'show_in_index' => false,
				)
			);

			register_rest_route(
				'wpvc-voting/v1',
				'/wpvcdeletecontestant',
				array(
					'methods'       => WP_REST_Server::CREATABLE,
					'callback'      => array('Wpvc_Front_Rest_Actions_Controller', 'wpvc_callback_delete_contestant'),
					'show_in_index' => false,
				)
			);
		}
		/**
		 * API Filter.
		 *
		 * @param mixed $params Parameters.
		 */
		public function wpvc_filter_add_rest_orderby_params($params)
		{
			$params['orderby']['enum'][]   = WPVC_VOTES_CUSTOMFIELD;
			$params['orderby']['enum'][]   = 'votes';
			$params['orderby']['enum'][]   = 'name';
			$params['orderby']['enum'][]   = 'wpvc_judge_score';
			$params['orderby']['enum'][]   = 'rand';
			$params['orderby']['enum'][]   = 'menu_order';
			$params['per_page']['maximum'] = WPVC_PAGINATION_MAX;
			return $params;
		}
		/**
		 * API Filter REST Query.
		 *
		 * @param mixed $query_vars Query variables.
		 * @param mixed $request Request Data.
		 */
		public function wpvc_filter_rest_contestants_query($query_vars, $request)
		{
			$orderby    = $request->get_param('orderby');
			$search     = $request->get_param('search');
			$contst_cat = $request->get_param('contest_category');

			if ($search != '') {
				// Empty s to stop search on post title.
				$query_vars['s'] = '';
				if (is_array($contst_cat)) {
					$imgcontest  = get_term_meta($contst_cat[0], 'imgcontest', true);
					$options     = get_term_meta($contst_cat[0], WPVC_VOTES_TAXONOMY_ASSIGN, true);
					$meta_search = array('relation' => 'OR');
					if (!empty($options)) {
						foreach ($options as $key => $cate) {
							$system_name                 = $cate['system_name'];
							$meta_search[$system_name] = array(
								'key'     => $system_name,
								'value'   => $search,
								'compare' => 'RLIKE',
							);
						}
					} else {
						$not_saved = Wpvc_Shortcode_Model::wpvc_custom_fields_by_contest('contest');
						if (!empty($not_saved)) {
							foreach ($not_saved as $key => $cate) {
								$system_name                 = $cate['system_name'];
								$meta_search[$system_name] = array(
									'key'     => $system_name,
									'value'   => $search,
									'compare' => 'RLIKE',
								);
							}
						}
					}
				} else {
					$not_saved = Wpvc_Shortcode_Model::wpvc_custom_fields_by_contest('contest');
					if (!empty($not_saved)) {
						foreach ($not_saved as $key => $cate) {
							$system_name                 = $cate['system_name'];
							$meta_search[$system_name] = array(
								'key'     => $system_name,
								'value'   => $search,
								'compare' => 'RLIKE',
							);
						}
					}
				}

				$query_vars['meta_query'] = $meta_search;
			}

			if (isset($orderby) && ($orderby === 'votes_count' || $orderby === 'votes')) {
				$query_vars['meta_key'] = WPVC_VOTES_CUSTOMFIELD;
				$query_vars['orderby']  = 'meta_value_num';
			}

			if (isset($orderby) && ($orderby === 'rand')) {
				$seed = (isset($_SESSION['wpvc_contestant_seed']) && $_SESSION['wpvc_contestant_seed'] != 0) ? sanitize_text_field($_SESSION['wpvc_contestant_seed']) : '';
				if ($seed == '') {
					$seed                             = wp_rand();
					$_SESSION['wpvc_contestant_seed'] = $seed;
				}
				$query_vars['orderby'] = 'RAND(' . $seed . ')';
			} else {
				$_SESSION['wpvc_contestant_seed'] = 0;
			}

			if (isset($orderby) && $orderby === 'wpvc_judge_score') {
				$query_vars['meta_key'] = 'wpvc_judge_score';
				$query_vars['orderby']  = 'meta_value_num';
			}

			return $query_vars;
		}
		/**
		 * Meta Filter REST Query.
		 *
		 * @param mixed $data Data.
		 * @param mixed $post Post Object.
		 * @param mixed $context Meta Context.
		 */
		public function wpvc_contestant_meta_filters($data, $post, $context)
		{
			$vote_opt                 = get_option(WPVC_VOTES_SETTINGS);
			$check_param              = $context->get_params();
			$data->data['post_title'] = html_entity_decode(get_the_title($post->ID), ENT_QUOTES | ENT_HTML5);
			include_once ABSPATH . 'wp-admin/includes/plugin.php';
			if (is_plugin_active('js_composer/js_composer.php') && defined('WPB_VC_VERSION')) {
				$content      = wp_strip_all_tags(do_shortcode(get_the_excerpt($post->ID)));
				$full_content = apply_filters('the_content', $post->post_content);
			} else {
				$content      = html_entity_decode(get_the_excerpt($post->ID), ENT_QUOTES | ENT_HTML5);
				$full_content = html_entity_decode(get_the_content($post->ID), ENT_QUOTES | ENT_HTML5);
			}

			$data->data['post_excerpt'] = (has_excerpt($post) != null) ? $post->post_excerpt : $content;
			$data->data['full_content'] = $full_content;

			$featured_image_id    = $data->data['featured_media']; // get featured image id.
			$featured_image_url   = wp_get_attachment_image_src($featured_image_id, 'medium'); // get url of the original size.
			$featured_image_large = wp_get_attachment_image_src($featured_image_id, 'large');
			if ($featured_image_url) {
				$data->data['featured_image_url'] = $featured_image_url[0];
			} else {
				$data->data['featured_image_url'] = WPVC_NO_IMAGE_CONTEST;
			}

			if ($featured_image_large) {
				$data->data['featured_image_large_url'] = $featured_image_large[0];
			} else {
				$data->data['featured_image_url'] = WPVC_NO_IMAGE_CONTEST;
			}

			// short_cont_image - Listing Page.
			if ($vote_opt['common']['short_cont_image'] != null) {
				$short_cont_image               = wp_get_attachment_image_src($featured_image_id, $vote_opt['common']['short_cont_image']);
				$data->data['short_cont_image'] = $short_cont_image[0];
			}
			// page_cont_image - Single Contestant.
			if ($vote_opt['common']['page_cont_image'] != null) {
				$page_cont_image               = wp_get_attachment_image_src($featured_image_id, $vote_opt['common']['page_cont_image']);
				$data->data['page_cont_image'] = $page_cont_image[0];
			}

			$term = get_the_terms($post->ID, WPVC_VOTES_TAXONOMY);
			if ($term) {
				$data->data['term_id']            = $termid = $term[0]->term_id;
				$data->data['term_name']          = $term[0]->name;
				$data->data['contestant_count']   = $term[0]->count;
				$data->data['vote_button_status'] = Wpvc_Voting_Model::wpvc_check_before_post($post->ID, $termid);
				$imgcontest                       = get_term_meta($termid, 'imgcontest', true);
				$data->data['img_contest']        = $imgcontest;
				if (isset($_SERVER['HTTP_AUTHORIZE_WPVC_REQUESTMETHOD']) && $_SERVER['HTTP_AUTHORIZE_WPVC_REQUESTMETHOD'] === 'authorprofile') {
					$category_options = get_term_meta($termid);
					$align_category   = array();
					$imgcontest       = get_term_meta($termid, 'imgcontest', true);
					$musicfileenable  = get_term_meta($termid, 'musicfileenable', true);
					if (is_array($category_options)) {
						foreach ($category_options as $key => $val) {
							if ($key == 'contest_rules') {
								$align_category[$key] = format_to_edit($val[0], true);
							} else {
								$align_category[$key] = maybe_unserialize($val[0]);
							}
						}
					}
					$data->data['allcatoption']  = $align_category;
					$custom_fields               = Wpvc_Front_Rest_Actions_Controller::wpvc_get_custom_fields($termid, $post->ID);
					$data->data['custom_fields'] = $custom_fields;
				}
			}

			// Votes Count.
			$votes_count = get_post_meta($post->ID, WPVC_VOTES_CUSTOMFIELD, true);
			if ($votes_count) {
				$data->data[WPVC_VOTES_CUSTOMFIELD] = $votes_count;
			} else {
				$data->data[WPVC_VOTES_CUSTOMFIELD] = 0;
			}

			$wpvc_video_extension = get_option('_ow_video_extension');

			// Get Custom_fields.
			$get_custom_fields = Wpvc_Front_Rest_Actions_Controller::wpvc_get_custom_fields($termid);
			$get_post_metas    = get_post_meta($post->ID, WPVC_VOTES_POST, true);
			if (!empty($get_custom_fields)) {
				$custom_values   = array();
				$custom_fields   = $get_custom_fields['custom_field'];
				$musicfileenable = get_term_meta($termid, 'musicfileenable', true);
				if (is_array($custom_fields)) {
					foreach ($custom_fields as $cus_key => $fields) {
						$system_name = $fields['system_name'];
						// System file type upload / file.
						if ($fields['question_type'] == 'FILE') {
							$post_image                    = get_post_meta($post->ID, 'ow_custom_attachment_' . $system_name, true);
							$custom_values[$system_name] = empty($post_image) ? '' : $post_image['url'];
						} else {
							if (is_array($get_post_metas) && array_key_exists($system_name, $get_post_metas)) {
								$custom_values[$system_name] = $get_post_metas[$system_name];
							}
						}
					}
					$data->data['custom_fields_value'] = $custom_values;
				}
			}

			// get author.
			$author                     = get_the_author();
			$author_email               = get_the_author_meta('user_email');
			$data->data['author_name']  = $author;
			$data->data['author_email'] = $author_email;

			// Viewers Count.
			$views_count = get_post_meta($post->ID, WPVC_VOTES_VIEWERS, true);
			if ($views_count) {
				$data->data[WPVC_VOTES_VIEWERS] = $views_count;
			} else {
				$data->data[WPVC_VOTES_VIEWERS] = 0;
			}

			$next_post     = get_next_post(true, '', WPVC_VOTES_TAXONOMY);
			$previous_post = get_previous_post(true, '', WPVC_VOTES_TAXONOMY);
			if (!empty($next_post)) {
				$data->data['next_link']  = get_permalink($next_post->ID);
				$data->data['next_title'] = $next_post->post_title;
			} else {
				$data->data['next_link']  = null;
				$data->data['next_title'] = null;
			}

			if (!empty($previous_post)) {
				$data->data['previous_link']  = get_permalink($previous_post->ID);
				$data->data['previous_title'] = $previous_post->post_title;
			} else {
				$data->data['previous_link']  = null;
				$data->data['previous_title'] = null;
			}

			return $data;
		}
	}
} else {
	die('<h2>' . esc_html_e('Failed to load Voting Front Rest Actions Controller', 'voting-contest') . '</h2>');
}

return new Wpvc_Front_Rest_Register_Controller();
