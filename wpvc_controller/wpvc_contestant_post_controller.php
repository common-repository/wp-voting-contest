<?php
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}
if (!class_exists('Wpvc_Contestant_Post_Controller')) {
	/**
	 * Contestant Post Controller.
	 */
	class Wpvc_Contestant_Post_Controller
	{
		/**
		 * Controller Contructor.
		 */
		public function __construct()
		{
			// Tab menu on contestant.
			add_action('wp_after_admin_bar_render', array($this, 'wpvc_contestant_custom_menu_bar'));
			add_filter('manage_edit-' . WPVC_VOTES_TYPE . '_columns', array($this, 'wpvc_contestant_post_add_columns'));
			add_filter('manage_edit-' . WPVC_VOTES_TYPE . '_sortable_columns', array($this, 'wpvc_votes_custom_post_page_sort'), 10, 2);
			// Get the values of the custom added fields.
			add_action('manage_' . WPVC_VOTES_TYPE . '_posts_custom_column', array($this, 'wpvc_custom_new_votes_column'), 10, 2);
			// Custom contestant meta boxes.
			add_action('add_meta_boxes', array($this, 'wpvc_custom_meta_box_contestant'));

			// Add Sorting Code in the Contestants.
			add_action('pre_get_posts', array($this, 'wpvc_manage_wp_posts_be_qe_pre_get_posts'), 1);
			add_filter('posts_clauses', array($this, 'wpvc_contest_category_clauses'), 10, 2);
		}
		/**
		 * Pre get posts.
		 *
		 * @param mixed $query Wp Query.
		 */
		public function wpvc_manage_wp_posts_be_qe_pre_get_posts($query)
		{
			if (is_admin() && !wp_doing_ajax()) {

				if (isset($query->query_vars['post_type'])) {
					if ($query->query_vars['post_type'] == 'contestants' && $query->query_vars['orderby'] == 'votes' && $query->query_vars['post_status'] == 'publish') {
						$query->set('meta_key', WPVC_VOTES_CUSTOMFIELD);
						$query->set('orderby', 'meta_value_num');
					}
				}
				return $query;
			}
		}
		/**
		 * Contest category clauses.
		 *
		 * @param mixed $clauses clauses.
		 * @param mixed $wp_query Wp Query.
		 */
		public function wpvc_contest_category_clauses($clauses, $wp_query)
		{
			global $wpdb;

			if (isset($wp_query->query_vars['post_type'])) {
				if ($wp_query->query_vars['post_type'] == 'contestants') {
					if (isset($wp_query->query['orderby']) && 'contest_category' == $wp_query->query['orderby']) {
						$clauses['join']    .= " LEFT JOIN (
                            SELECT object_id, GROUP_CONCAT(name ORDER BY name ASC) AS color
                            FROM $wpdb->term_relationships
                            INNER JOIN $wpdb->term_taxonomy USING (term_taxonomy_id)
                            INNER JOIN $wpdb->terms USING (term_id)
                            WHERE taxonomy = 'contest_category'
                            GROUP BY object_id
                        ) AS color_terms ON ($wpdb->posts.ID = color_terms.object_id)";
						$clauses['orderby']  = 'color_terms.color ';
						$clauses['orderby'] .= ('ASC' == strtoupper($wp_query->get('order'))) ? 'ASC' : 'DESC';
					} elseif (isset($wp_query->query['orderby']) && 'title' == $wp_query->query['orderby']) {
						$clauses['orderby']  = 'post_title ';
						$clauses['orderby'] .= ('ASC' == strtoupper($wp_query->get('order'))) ? 'ASC' : 'DESC';
					} elseif (isset($wp_query->query['orderby']) && 'name' == $wp_query->query['orderby']) {
						$clauses['orderby']  = 'post_title ';
						$clauses['orderby'] .= ('ASC' == strtoupper($wp_query->get('order'))) ? 'ASC' : 'DESC';
					} elseif (isset($wp_query->query['orderby']) && 'votes' == $wp_query->query['orderby'] && $wp_query->query_vars['post_status'] == 'publish') {
						$clauses['orderby'] = 'CAST(meta_value as unsigned)';
						$clauses['order']  .= ('ASC' == strtoupper($wp_query->get('order'))) ? 'ASC' : 'DESC';
					} elseif (isset($wp_query->query['orderby']) && 'votes' == $wp_query->query['orderby']) {
						$post_table         = $wpdb->prefix . 'posts';
						$order              = ('ASC' == strtoupper($wp_query->get('order'))) ? 'ASC' : 'DESC';
						$clauses['where']  .= " AND $wpdb->postmeta.meta_key='votes_count'";
						$clauses['join']   .= " LEFT JOIN $wpdb->postmeta ON $post_table.ID = $wpdb->postmeta.post_id";
						$clauses['orderby'] = "$wpdb->postmeta.meta_value + 0 $order";
					}
				}
			}
			return $clauses;
		}
		/**
		 * Contestant Nav Bar.
		 */
		public static function wpvc_contestant_custom_menu_bar()
		{
			global $post_type, $pagenow;
			$page = isset($_GET['page']) ? sanitize_text_field(wp_unslash($_GET['page'])) : '';
			if (($page == 'contestants' || strpos($page, 'wpvc') !== false || (isset($_GET['post_type']) && sanitize_text_field(wp_unslash($_GET['post_type'])) == 'contestants') || $post_type == 'contestants')) {
				require_once WPVC_VIEW_PATH . 'wpvc_contestant_admin_view.php';
				wpvc_contestant_topbar();
			}
		}

		/**
		 * Add columns to custom post(contestants).
		 *
		 * @param mixed $add_columns Add columns.
		 */
		public function wpvc_contestant_post_add_columns($add_columns)
		{
			unset($add_columns['author']);
			unset($add_columns['taxonomy-contest_category']);
			unset($add_columns['comments']);
			unset($add_columns['title']);
			unset($add_columns['date']);
			$add_columns['cb']                  = '<input type="checkbox" />';
			$add_columns['cb']                  = '<input type="checkbox" />';
			$add_columns['image']               = __('Featured Image', 'voting-contest');
			$add_columns['title']               = __('Title', 'voting-contest');
			$add_columns['info']                = __('Info', 'voting-contest');
			$add_columns[WPVC_VOTES_TAXONOMY] = __('Contest Category', 'voting-contest');
			$add_columns['votes']               = __('Votes', 'voting-contest');

			return $add_columns;
		}
		/**
		 * Specify the columns that need to be sortable.
		 *
		 * @param mixed $columns Columns.
		 */
		public function wpvc_votes_custom_post_page_sort($columns)
		{
			$columns[WPVC_VOTES_TAXONOMY] = 'contest_category';
			$columns['votes']               = 'votes';
			return $columns;
		}
		/**
		 * Specify the columns that need to be sortable.
		 *
		 * @param mixed $column Columns.
		 * @param int   $post_id Post ID.
		 */
		public function wpvc_custom_new_votes_column($column, $post_id)
		{
			$terms = get_the_terms($post_id, WPVC_VOTES_TAXONOMY);
			if (!empty($terms)) {
				$current_term_id = $terms[0]->term_id;
				$imgcontest      = get_term_meta($current_term_id, 'imgcontest', true);
			} else {
				$current_term_id = $imgcontest = '';
			}

			switch ($column) {

				case WPVC_VOTES_TAXONOMY:
					if (!empty($terms)) {
						$out = array();
						foreach ($terms as $c) {
							$_taxonomy_title = esc_html(sanitize_term_field('name', $c->name, $c->term_id, 'category', 'display'));
						}
						echo esc_attr($_taxonomy_title);
					} else {
						esc_html_e('Uncategorized', 'voting-contest');
					}
					break;

				case 'image':
					if ($imgcontest == 'photo') {
						if (!has_post_thumbnail($post_id)) {
							$attimages = get_attached_media('image', $post_id);
							foreach ($attimages as $image) {
								if ($image->menu_order == 0) {
									set_post_thumbnail($post_id, $image->ID);
								}
							}
						}
						if (has_post_thumbnail($post_id)) {
							$image_arr = wp_get_attachment_image_src(get_post_thumbnail_id($post_id), 'thumbnail');
							$image_src = htmlspecialchars($image_arr[0]);
							echo "<img src='" . esc_url($image_src) . "' width='200' height='150' class='left-img-thumb' />";
						} else {
							echo '<img src=' . esc_url(WPVC_NO_IMAGE_CONTEST) . " width='200px' class='left-img-thumb' />";
						}
					}

					break;

				case 'votes':
					if ($current_term_id != '') {
						$votes = get_post_meta($post_id, WPVC_VOTES_CUSTOMFIELD, 'true');
					}
					echo $votes == null ? 0 : esc_attr($votes);
					break;

				case 'info':
					echo "<i class='owvotingicon owicon-date'></i><span class='ow_admininfo'>" . get_the_date() . '</span> <br/>';
					$author = get_the_author();
					$author = ($author != null) ? $author : 'Admin';
					echo "<i class='owvotingicon owicon-authors'></i><span class='ow_admininfo'>" . esc_attr($author) . '</span> <br/>';
					echo "<i class='owvotingicon owicon-imgcontest'></i><span class='ow_admininfo'>" . esc_attr(ucfirst($imgcontest)) . '</span>';
					break;
			}
		}
		/**
		 *  Add the custom meta boxes on add/edit.
		 */
		public function wpvc_custom_meta_box_contestant()
		{
			add_meta_box('votesstatus', __('Votes For this Contestant', 'voting-contest'), array($this, 'wpvc_votes_count_meta_box'), WPVC_VOTES_TYPE, 'normal', 'high', array('__block_editor_compatible_meta_box' => true));
			add_meta_box('votecustomfields', __('Custom Fields', 'voting-contest'), array($this, 'wpvc_votes_contestant_custom_field_meta_box'), WPVC_VOTES_TYPE, 'normal', 'high');
		}
		/**
		 *  Votes count metabox.
		 *
		 * @param mixed $post Post Object.
		 */
		public function wpvc_votes_count_meta_box($post)
		{
			$cnt = get_post_meta($post->ID, WPVC_VOTES_CUSTOMFIELD, true);
?>
			<h1>
				<?php
				echo $cnt ? esc_attr($cnt . ' ') : '0' . ' ';
				esc_html_e('Votes', 'voting-contest');
				?>
			</h1>
			<?php $cnt = ($cnt == null) ? 0 : $cnt; ?>
<?php
		}

		/**
		 *  Custom Field metabox.
		 *
		 * @param mixed $post Post Object.
		 */
		public function wpvc_votes_contestant_custom_field_meta_box($post)
		{
			global $post;
			global $pagenow;
			$page =  in_array($pagenow, array('post.php',)) ? 'edit' : 'new';

			echo "<div id='wpvc_admin_custom'  data-url='" . esc_url(site_url('index.php')) . "' data-originalurl='" . esc_url(site_url()) . "'>
                    <input type='hidden' value='" . $page . "' id='wpvcPageNow' />
                    <input type='hidden' value='contestants' id='currentwpvcPage' />
                </div>";
		}
	}
} else {
	die('<h2>' . esc_html_e('Failed to load Voting Contestant Post Controller', 'voting-contest') . '</h2>');
}


return new Wpvc_Contestant_Post_Controller();
