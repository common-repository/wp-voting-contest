<?php
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}
if (!class_exists('Wpvc_Excerpt_Controller')) {
	/**
	 * Excerpt Controller.
	 */
	class Wpvc_Excerpt_Controller
	{
		/**
		 * Controller Contructor.
		 */
		public function __construct()
		{
			// $this->name = strtolower( get_class() );

			global $post;
			if (isset($post) && $post->post_type == WPVC_VOTES_TYPE) {
				echo 'yes';
				remove_all_filters('get_the_excerpt');
			}
			add_filter(
				'get_the_excerpt',
				array(
					&$this,
					'filter',
				)
			);
		}
		/**
		 * Filter Contructor.
		 *
		 * @param string $text Text.
		 */
		public function filter($text)
		{
			global $post;
			if ($post->post_type == WPVC_VOTES_TYPE) {
				$get_option      = get_option(WPVC_VOTES_SETTINGS);
				$excerpt         = $get_option['excerpt'];
				$length          = $excerpt['length'];
				$use_words       = ($excerpt['use_word'] == 'on') ? 1 : 0;
				$finish_word     = ($excerpt['finish_word'] == 'on') ? 1 : 0;
				$finish_sentence = ($excerpt['finish_sentence'] == 'on') ? 1 : 0;
				$no_shortcode    = $excerpt['no_shortcode'];
				$no_custom       = $excerpt['no_custom'];

				$text = get_the_content('');
				if (!empty($text) && $no_custom == 'off') {
					return $text;
				}

				if ($no_shortcode == 'on') {
					$text = strip_shortcodes($text);
				}

				$text = $this->text_excerpt($text, $length, $use_words, $finish_word, $finish_sentence);
				return $text;
			}
			return $text;
		}
		/**
		 * Text Excerpt function.
		 *
		 * @param string $text Text.
		 * @param string $length length.
		 * @param string $use_words use_words.
		 * @param string $finish_word finish_word.
		 * @param string $finish_sentence finish_sentence.
		 */
		public function text_excerpt($text, $length, $use_words, $finish_word, $finish_sentence)
		{
			$tokens = array();
			$out    = '';
			$w      = 0;

			// Divide the string into tokens; HTML tags, or words, followed by any whitespace.
			// (<[^>]+>|[^<>\s]+\s*).
			preg_match_all('/(<[^>]+>|[^<>\s]+)\s*/u', $text, $tokens);
			foreach ($tokens[0] as $t) { // Parse each token.
				if ($w >= $length && !$finish_sentence) { // Limit reached
					break;
				}
				if ($t[0] != '<') { // Token is not a tag.
					if ($w >= $length && $finish_sentence && preg_match('/[\?\.\!]\s*$/uS', $t) == 1) { // Limit reached, continue until ? . or ! occur at the end.
						$out .= trim($t);
						break;
					}
					if (1 == $use_words) { // Count words.
						$w++;
					} else { // Count/trim characters.
						$chars = trim($t); // Remove surrounding space.
						$c     = strlen($chars);
						if ($c + $w > $length && !$finish_sentence) { // Token is too long.
							$c = ($finish_word) ? $c : $length - $w; // Keep token to finish word.
							$t = substr($t, 0, $c);
						}
						$w += $c;
					}
				}

				// Append what's left of the token.
				$out .= $t;
			}

			return trim(force_balance_tags($out));
		}
	}
} else {
	die('<h2>' . esc_html_e('Failed to load the Voting Excerpt Controller', 'voting-contest') . '</h2>');
}

return new Wpvc_Excerpt_Controller();
