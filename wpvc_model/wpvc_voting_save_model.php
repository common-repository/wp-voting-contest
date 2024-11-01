<?php
if (!class_exists('Wpvc_Voting_Save_Model')) {
	/**
	 *  Voting Save Model.
	 */
	class Wpvc_Voting_Save_Model
	{
		/**
		 *  Get voted post id.
		 *
		 * @param int    $pid POST ID.
		 * @param int    $termid Term ID.
		 * @param string $date_val Vote Setting.
		 * @param string $hourscalc post listing index.
		 */
		public static function wpvc_get_voted_post_ids_date($pid, $termid, $date_val = null, $hourscalc = null)
		{
			global $wpdb;
			if ($date_val == 'calendar') {
				$from = date('Y-m-d 00:00:01');
				$to   = date('Y-m-d 23:59:59');
			} elseif ($date_val == 'hours') {
				$now = new DateTime();
				$now->sub(new DateInterval('PT' . $hourscalc . 'H'));
				$from = $now->format('Y-m-d H:i:s');
				$to   = date('Y-m-d H:i:s');
			}

			$new_sql = $wpdb->prepare('SELECT post_id  FROM ' . WPVC_VOTES_TBL . ' WHERE date between %s AND %s && termid= %d  ORDER BY id DESC', $from, $to, $termid);

			$voted_val = $wpdb->get_results($new_sql, ARRAY_A);
			return $voted_val;
		}
		/**
		 *  Get checking voted for date.
		 *
		 * @param int    $pid POST ID.
		 * @param int    $termid Term ID.
		 * @param string $from From date.
		 * @param string $to To date.
		 * @param string $where condition.
		 */
		public static function wpvc_check_if_voted_date($pid = null, $termid = null, $from = null, $to = null, $where = null)
		{
			global $wpdb;

			if (isset($where['ip_always'])) {
				$new_sql = $wpdb->prepare('SELECT date  FROM ' . WPVC_VOTES_TBL . ' WHERE ip = %s && ip_always = %s && date between %s AND %s && post_id = %d && termid= %d  ORDER BY id DESC', $where['ip'], $where['ip_always'], $from, $to, $pid, $termid);
			} else {
				$new_sql = $wpdb->prepare('SELECT date  FROM ' . WPVC_VOTES_TBL . ' WHERE ip = %s && date between %s AND %s && post_id = %d && termid= %d  ORDER BY id DESC', $where['ip'], $from, $to, $pid, $termid);
			}

			$voted_val = $wpdb->get_results($new_sql, ARRAY_A);
			$num_rows  = $wpdb->num_rows;
			return $num_rows;
		}
		/**
		 *  Get Track Method.
		 *
		 * @param int    $pid POST ID.
		 * @param int    $termid Term ID.
		 * @param array  $votesetting Settings.
		 * @param string $email Email.
		 */
		private static function get_track_method_where($pid, $termid, $votesetting, $email)
		{
			// Addded for tracking method.
			$tracking_method = $votesetting['vote_tracking_method'];
			switch ($tracking_method) {
				case 'cookie_traced':
					$useragent    = Wpvc_Voting_Model::wpvc_cookie_voting_getbrowser();
					$voter_cookie = $useragent['name'] . '@' . $termid;
					$ip_addr      = Wpvc_Voting_Model::wpvc_get_user_ipaddress();
					$where        = array(
						'ip'        => $voter_cookie,
						'ip_always' => $ip_addr,
					);
					break;

				case 'ip_traced':
					$ip_addr = Wpvc_Voting_Model::wpvc_get_user_ipaddress();
					$where   = array('ip' => $ip_addr);
					break;
			}
			return $where;
		}
		/**
		 *  Get Per day vote Method.
		 *
		 * @param int    $post_id POST ID.
		 * @param int    $termid Term ID.
		 * @param array  $votesetting Settings.
		 * @param int    $post_index Index of Post.
		 * @param string $email Email.
		 */
		public static function wpvc_per_calendar_votes($post_id, $termid, $votesetting, $post_index = null, $email = null)
		{

			$freq_count  = $votesetting['vote_frequency_count'];
			$voting_type = $votesetting['vote_votingtype'];

			if ($freq_count < 1) {
				$freq_count = 1;
			}
			$where       = self::get_track_method_where($post_id, $termid, $votesetting, $email);
			$from_date   = date('Y-m-d 00:00:01');
			$to_date     = date('Y-m-d 23:59:59');
			$post_count  = self::wpvc_check_if_voted_date($post_id, $termid, $from_date, $to_date, $where);

			if ($freq_count == 1) {
				if ($post_count == 0) {
					return true;
				} else {
					if ($voting_type == 1) {
						$response = array(
							'update_vote_count' => array(
								'msg'        => 'already',
								'post_index' => $post_index,
								'poptitle'   => 'vote_limit_reached',
								'popcontent' => 'pls_vote_tomo_for_cal',
								'freq_count' => $freq_count,
							),
						);
					} else {
						$response = array(
							'update_vote_count' => array(
								'msg'        => 'already',
								'post_index' => $post_index,
								'poptitle'   => 'vote_limit_reached',
								'popcontent' => 'pls_vote_tomo_for_cal_split',
								'freq_count' => $freq_count,
							),
						);
					}

					return $response;
				}
			}
		}
	}
} else {
	die('<h2>' . esc_html(__('Failed to load Voting Save model')) . '</h2>');
}

return new Wpvc_Voting_Save_Model();
