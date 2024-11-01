<?php
if ( ! function_exists( 'wpvc_activation_init' ) ) {
	/**
	 *  Activation Function.
	 */
	function wpvc_activation_init() {
		wpvc_write_js_constants();
		wpvc_default_settings();
		Wpvc_Installation_Model::wpvc_create_tables_owvoting();
		$option        = get_option( WPVC_VOTES_SETTINGS );
		$plugin_option = ( is_array( $option['plugin'] ) ) ? $option['plugin'] : null;
		if ( is_array( $plugin_option ) ) {
			$deactivation = $plugin_option['deactivation'];
		}
	}
} else {
	die( '<h2>' . esc_html( __( 'Failed to load Voting Activation initial', 'voting-contest' ) ) . '</h2>' );
}

if ( ! function_exists( 'wpvc_votes_deactivation_init' ) ) {
	/**
	 *  Deactivation Function.
	 */
	function wpvc_votes_deactivation_init() {
		$option        = get_option( WPVC_VOTES_SETTINGS );
		$plugin_option = ( is_array( $option['plugin'] ) ) ? $option['plugin'] : null;
		if ( is_array( $plugin_option ) ) {
			$deactivation = $plugin_option['deactivation'];
			if ( $deactivation == 'off' ) {
				Wpvc_Installation_Model::wpvc_delete_all_contestants_owvoting();
			}
		}
	}
} else {
	die( '<h2>' . esc_html( __( 'Failed to load Voting Deactivation initial', 'voting-contest' ) ) . '</h2>' );
}

if ( ! function_exists( 'wpvc_write_js_constants' ) ) {
	/**
	 *  Writing JS Constants.
	 */
	function wpvc_write_js_constants() {
		global $wpdb;

		$react_js = 0;
		$file     = WPVC_VIEWS . 'constants.js';
		if ( file_exists( $file ) && $react_js != '1' ) {
			// Open the file to get existing content.
			$fh = fopen( $file, 'w' );
			fclose( $fh );
			$current = file_get_contents( $file );
			// Append a new person to the file.
			$current .= "export const PLUGIN_VERSION = '" . WPVC_VOTE_VERSION . "';\n";
			$current .= "export const SITE_LANG = '" . get_locale() . "';\n";

			$current .= "export const PLUGIN_NAME = '" . WPVC_WP_VOTING_SL_PRODUCT_NAME . "';\n";
			$current .= "export const PLUGIN_URL = '/wp-content/plugins/wp-voting-contest/';\n";

			$current .= "export const WPVC_VOTES_TYPE = 'contestants';\n";
			$current .= "export const WPVC_VOTES_TAXONOMY = 'contest_category';\n";
			$current .= "export const WPVC_VOTES_CUSTOMFIELD = 'votes_count';\n";
			$current .= "export const WPVC_VOTES_EXPIRATIONFIELD = 'votes_expiration';\n";
			$current .= "export const WPVC_VOTES_SETTINGS = 'wpvc_votes_settings';\n";
			$current .= "export const WPVC_SKIP_FIELDS = ['contestant-title','contestant-desc','contestant-image'];\n";

			// Write the contents back to the file.
			file_put_contents( $file, $current );
			update_option( WPVC_VOTES_REACT, '1' );
		}
	}
}

if ( ! function_exists( 'wpvc_default_settings' ) ) {
	/**
	 *  Default Settings.
	 */
	function wpvc_default_settings() {
		global $wpdb;
		$setting_check = get_option( WPVC_VOTES_SETTINGS );
		if ( $setting_check == '' ) {
			$settings = array(
				'common'     => array(
					'short_cont_image'                => 'medium',
					'page_cont_image'                 => 'large',
					'single_page_cont_image'          => '100',
					'single_page_cont_image_px'       => '%',
					'single_page_title'               => 'off',
					'vote_prettyphoto_disable_single' => 'off',
					'vote_disable_single'             => 'off',
					'orderby'                         => 'date',
					'order'                           => 'desc',
					'sidebar'                         => '',
					'vote_sidebar'                    => 'on',
					'vote_entry_form'                 => 'off',
					'vote_prettyphoto_disable'        => 'off',
					'vote_show_date_prettyphoto'      => 'off',
					'vote_custom_slug'                => '',
					'vote_newlink_tab'                => 'off',
					'vote_enable_ended'               => 'off',
					'vote_hide_account'               => 'off',
					'vote_auto_login'                 => 'on',
					'vote_enable_recaptcha'           => 'off',
					'vote_enable_recaptcha_voting'    => 'off',
					'vote_recapatcha_key'             => '',
					'vote_recapatcha_secret'          => '',
					'vote_openclose_menu'             => 'off',
					'title'                           => '',
				),
				'contest'    => array(
					'onlyloggedinuser'         => 'on',
					'vote_onlyloggedcansubmit' => 'on',
					'vote_tracking_method'     => 'ip_traced',
					'vote_frequency_count'     => '1',
					'frequency'                => '1',
					'vote_votingtype'          => '2',
					'vote_truncation_grid'     => '',
					'vote_truncation_list'     => '',
					'vote_publishing_type'     => 'off',
					'vote_grab_email_address'  => 'off',
					'vote_tobestarteddesc'     => 'Contest not yet open for voting',
					'vote_reachedenddesc'      => 'There is no Contest at this time',
					'vote_entriescloseddesc'   => 'Contest already Started',
				),
				'style'      => array(
					'row_width'            => '3',
					'direction'            => 'row',
					'column_width'         => '12',
					'wpvc_spacing'         => '1',
					'vote_title_alocation' => 'off',
					'vote_readmore'        => 'off',
					'vote_count_showhide'  => 'on',
				),
				'color'      => array(
					'votes_timerbgcolor'                   => '#ffffff',
					'votes_timertextcolor'                 => '#000000',
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
				),
				'share'      => array(
					'vote_fb_appid'          => '',
					'facebook'               => 'on',
					'facebook_login'         => 'off',
					'file_fb_default'        => 'on',
					'threads'                => 'on',
					'pinterest'              => 'on',
					'file_pinterest_default' => 'on',
					'reddit'                 => 'on',
					'file_reddit_default'    => 'on',
					'linkedin'               => 'on',
					'file_linkedin_default'  => 'on',
					'tumblr'                 => 'on',
					'file_tumblr_default'    => 'on',
					'vote_tw_appid'          => '',
					'vote_tw_secret'         => '',
					'twitter'                => 'on',
					'twitter_login'          => 'off',
					'file_tw_default'        => 'on',
					'google_login'           => 'off',
					'vote_gg_clientid'       => '',
				),
				'plugin'     => array(
					'deactivation' => 'on',
					'loadscript'   => 'on',
				),
				'excerpt'    => array(
					'length'          => '40',
					'use_word'        => 'on',
					'ellipsis'        => '',
					'finish_word'     => 'off',
					'finish_sentence' => 'off',
					'read_more'       => 'Read More',
					'add_link'        => 'off',
					'no_custom'       => 'on',
					'no_shortcode'    => 'on',
				),
				'pagination' => array(
					'contestant_per_page'   => '10',
					'pages_text'            => 'Page %CURRENT_PAGE% of %TOTAL_PAGES%',
					'current_text'          => '%PAGE_NUMBER%',
					'page_text'             => '%PAGE_NUMBER%',
					'first_text'            => '« First',
					'last_text'             => 'Last »',
					'prev_text'             => '«',
					'next_text'             => '»',
					'load_more_button_text' => 'Loadmore',
					'style'                 => '1',
					'num_pages'             => '5',
				),
				'email'      => array(
					'vote_notify_mail'                 => 'off',
					'vote_from_mail'                   => '',
					'vote_admin_mail'                  => '',
					'vote_admin_mail_content'          => '',
					'vote_notify_contestant'           => 'off',
					'vote_notify_subject'              => '',
					'vote_contestant_submit_content'   => '',
					'vote_notify_approved'             => 'off',
					'vote_approve_subject'             => '',
					'vote_contestant_approved_content' => '',
				),
			);
			update_option( WPVC_VOTES_SETTINGS, $settings );
		}
	}
}
