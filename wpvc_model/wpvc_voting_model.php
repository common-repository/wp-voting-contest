<?php
if (!class_exists('Wpvc_Voting_Model')) {
	/**
	 *  Voting Model.
	 */
	class Wpvc_Voting_Model
	{
		/**
		 *  Saving Votes.
		 *
		 * @param int    $pid POST ID.
		 * @param int    $termid Term ID.
		 * @param array  $votesetting Vote Setting.
		 * @param int    $post_index post listing index.
		 * @param int    $manual check manual or not.
		 * @param int    $buy_vote buy vote or not .
		 * @param int    $buyvote_count buy vote count.
		 * @param string $email Email.
		 */
		public static function wpvc_save_votes($pid, $termid, $votesetting, $post_index, $manual = null, $buy_vote = false, $buyvote_count = 1, $email = null)
		{

			$tracking_method = $votesetting['vote_tracking_method'];
			$vote_count      = get_term_meta($termid, 'vote_count_per_cat', true);

			$is_votable = self::wpvc_check_is_votable($pid, $termid, $votesetting, $post_index, $email);

			if ($is_votable == 1) {
				switch ($tracking_method) {
					case 'cookie_traced':
						$useragent    = self::wpvc_cookie_voting_getbrowser();
						$voter_cookie = $useragent['name'] . '@' . $termid;
						$ip_addr      = $voter_cookie;
						break;

					case 'ip_traced':
						$ip_addr = self::wpvc_get_user_ipaddress();
						break;
				}

				$ip_always = self::wpvc_get_user_ipaddress();

				$current_user = get_user_by('id', $votesetting['user_id_profile']);
				$email_always = $current_user->user_email;
				if ($votesetting['user_id_profile'] == null) {
					$email_always = $email;
				}

				self::wpvc_update_vote_contestant($ip_addr, $vote_count, $pid, $termid, $ip_always, $email_always);

				$response = self::wpvc_get_vote_response($pid, $termid, $votesetting, $post_index, $email_always);
				return $response;
			} else {
				return $is_votable;
			}
		}
		/**
		 *  Get response after voting.
		 *
		 * @param int    $pid POST ID.
		 * @param int    $termid Term ID.
		 * @param array  $votesetting Vote Setting.
		 * @param int    $post_index post listing index.
		 * @param string $email_always Email.
		 */
		private static function wpvc_get_vote_response($pid, $termid, $votesetting, $post_index, $email_always)
		{
			$freq_count  = $votesetting['vote_frequency_count'];
			$total_count = self::wpvc_get_total_vote_count($pid);
			$is_votable  = self::wpvc_check_is_votable($pid, $termid, $votesetting);
			$voted       = 'voted';

			$resp_val = '';
			// Frequency count.
			if ($freq_count < 1) {
				$freq_count = 1;
			}

			$post_ids = 0;
			$resp_val = 'multiple';
			$post_ids = Wpvc_Voting_Save_Model::wpvc_get_voted_post_ids_date($pid, $termid, 'calendar');
			$response = array(
				'update_vote_count' => array(
					'voting_count'  => $total_count,
					'count_post_id' => $pid,
					'post_index'    => $post_index,
					'msg'           => $voted,
					'cat_select'    => 'per_calendar',
					'resp_val'      => $resp_val,
					'freq_count'    => $freq_count,
					'post_ids'      => $post_ids,
					'is_votable'    => $is_votable,
				),
			);
			return $response;
		}
		/**
		 *  Check contestants is votable.
		 *
		 * @param int    $post_id POST ID.
		 * @param int    $termid Term ID.
		 * @param array  $votesetting Vote Setting.
		 * @param int    $post_index post listing index.
		 * @param string $email Email.
		 */
		private static function wpvc_check_is_votable($post_id, $termid, $votesetting, $post_index = null, $email = null)
		{
			if ($post_index == '') {
				$term_settings = self::wpvc_get_term_settings($termid);
				if (!empty($term_settings)) {
					$end_time     = $term_settings['votes_expiration'];
					$start_time   = $term_settings['votes_starttime'];
					$current_time = current_time('timestamp', 0);
					if (($start_time != '' && strtotime($start_time) > $current_time)) {
						return 'not_started';
					} elseif (($end_time != '' && strtotime($end_time) < $current_time)) {
						return 'ended';
					}
				}
			}
			$per_calendar = Wpvc_Voting_Save_Model::wpvc_per_calendar_votes($post_id, $termid, $votesetting, $post_index, $email);
			return $per_calendar;
		}
		/**
		 *  Get Term Settings.
		 *
		 * @param int $termid Term ID.
		 */
		public static function wpvc_get_term_settings($termid)
		{
			$category_options = get_term_meta($termid);
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
			return $align_category;
		}
		/**
		 *  Check contestants before vote.
		 *
		 * @param int $pid POST ID.
		 * @param int $termid Term ID.
		 */
		public static function wpvc_check_before_post($pid, $termid)
		{
			$votesetting = get_option(WPVC_VOTES_SETTINGS);
			if (is_array($votesetting)) {
				$contest_setting = $votesetting['contest'];
				$is_votable      = self::wpvc_check_is_votable($pid, $termid, $contest_setting);
				return $is_votable;
			}
		}
		/**
		 *  Get Ip address.
		 */
		public static function wpvc_get_user_ipaddress()
		{
			foreach (array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR') as $key) {
				if (array_key_exists($key, $_SERVER) === true) {
					foreach (explode(',', sanitize_text_field(wp_unslash($_SERVER[$key]))) as $ip) {
						$ip = trim($ip);

						if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
							return $ip;
						}
					}
				}
			}
		}
		/**
		 *  Update Votes count.
		 *
		 * @param int    $ip IP address.
		 * @param int    $vote_count Vote Count.
		 * @param int    $pid POST ID.
		 * @param int    $termid Term ID.
		 * @param int    $ip_always IP address.
		 * @param string $email_always Email.
		 */
		public static function wpvc_update_vote_contestant($ip, $vote_count, $pid, $termid, $ip_always = null, $email_always = null)
		{
			global $wpdb;
			$todate   = new DateTime();
			$to_date  = $todate->format('Y-m-d H:i:s');
			$save_sql = $wpdb->prepare('INSERT INTO ' . WPVC_VOTES_TBL . ' (`ip` , `votes` , `post_id` , `termid` , `date` ,`ip_always`,`email_always` ) VALUES ( %s,%d,%d,%d,%s,%s,%s ) ', $ip, $vote_count, $pid, $termid, $to_date, $ip_always, $email_always);

			$wpdb->query($save_sql);
		}
		/**
		 *  Get total Votes count.
		 *
		 * @param int $pid POST ID.
		 */
		public static function wpvc_get_total_vote_count($pid)
		{
			global $wpdb;
			$new_sql = $wpdb->prepare('SELECT SUM(votes)  FROM ' . WPVC_VOTES_TBL . ' WHERE post_id = %d', $pid);
			$total_v = $wpdb->get_var($new_sql);
			update_post_meta($pid, WPVC_VOTES_CUSTOMFIELD, $total_v);
			return $total_v;
		}
		/**
		 *  Get Browser instance.
		 */
		public static function wpvc_cookie_voting_getbrowser()
		{
			$u_agent  = isset($_SERVER['HTTP_USER_AGENT']) ? sanitize_text_field(wp_unslash($_SERVER['HTTP_USER_AGENT'])) : '';
			$bname    = 'Unknown';
			$platform = 'Unknown';
			$version  = '';

			// First get the platform?
			if (preg_match('/linux/i', $u_agent)) {
				$platform = 'linux';
			} elseif (preg_match('/macintosh|mac os x/i', $u_agent)) {
				$platform = 'mac';
			} elseif (preg_match('/windows|win32/i', $u_agent)) {
				$platform = 'windows';
			}

			// Next get the name of the useragent yes seperately and for good reason.
			if ((preg_match('/MSIE/i', $u_agent) || preg_match('/Trident/i', $u_agent)) && !preg_match('/Opera/i', $u_agent)) {
				$bname = 'IE';
				$ub    = 'MSIE';
			} elseif (preg_match('/Edg/i', $u_agent)) {
				$bname = 'EDG';
				$ub    = 'Firefox';
			} elseif (preg_match('/Firefox/i', $u_agent)) {
				$bname = 'MF';
				$ub    = 'Firefox';
			} elseif (preg_match('/Chrome/i', $u_agent)) {
				$bname = 'GC';
				$ub    = 'Chrome';
			} elseif (preg_match('/Safari/i', $u_agent)) {
				$bname = 'AS';
				$ub    = 'Safari';
			} elseif (preg_match('/Opera/i', $u_agent)) {
				$bname = 'O';
				$ub    = 'Opera';
			} elseif (preg_match('/Netscape/i', $u_agent)) {
				$bname = 'N';
				$ub    = 'Netscape';
			}

			$known   = array('Version', $ub, 'other');
			$pattern = '#(?<browser>' . join('|', $known) .
				')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
			if (!preg_match_all($pattern, $u_agent, $matches)) {
				// we have no matching number just continue
			}

			$i = count($matches['browser']);
			if ($i != 1) {
				if (strripos($u_agent, 'Version') < strripos($u_agent, $ub)) {
					$version = $matches['version'][0];
				} else {
					$version = $matches['version'][1];
				}
			} else {
				$version = $matches['version'][0];
			}

			if ($version == null || $version == '') {
				$version = '?';
			}

			return array(
				'userAgent' => $u_agent,
				'name'      => $bname,
				'version'   => $version,
				'platform'  => $platform,
				'pattern'   => $pattern,
			);
		}
	}
} else {
	die('<h2>' . esc_html(__('Failed to load Voting Entry model')) . '</h2>');
}

return new Wpvc_Voting_Model();
