<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
if ( ! class_exists( 'Wpvc_Common_Settings_Controller' ) ) {
	/**
	 * Common Settings Controller.
	 */
	class Wpvc_Common_Settings_Controller {
		/**
		 * Overview page.
		 */
		public function wpvc_voting_overview() {
			require_once WPVC_VIEW_PATH . 'wpvc_settings_view.php';
			wpvc_overview_view();
		}
		/**
		 * Settings page.
		 */
		public static function wpvc_voting_setting_common() {
			require_once WPVC_VIEW_PATH . 'wpvc_settings_view.php';
			wpvc_settings_view();
		}
		/**
		 * Category page.
		 */
		public static function wpvc_voting_category_list() {
			require_once WPVC_VIEW_PATH . 'wpvc_settings_view.php';
			wpvc_category_view();
		}
		/**
		 * Upgrade to Pro page.
		 */
		public static function wpvc_upgrade_text() {
			require_once WPVC_VIEW_PATH . 'wpvc_settings_view.php';
			wpvc_upgrade_text();
		}
		/**
		 * Custom Fields page.
		 */
		public static function wpvc_voting_custom_fields() {
			require_once WPVC_VIEW_PATH . 'wpvc_settings_view.php';
			wpvc_custom_fields_view();
		}
		/**
		 * Voting Logs page.
		 */
		public static function wpvc_voting_vote_logs() {
			require_once WPVC_VIEW_PATH . 'wpvc_settings_view.php';
			wpvc_voting_vote_logs_view();
		}
		/**
		 * Migration page.
		 */
		public static function wpvc_voting_migration() {
			require_once WPVC_VIEW_PATH . 'wpvc_settings_view.php';
			wpvc_migration_view();
		}
	}
} else {
	die( '<h2>' . esc_html( __( 'Failed to load Voting Common Settings Controller', 'voting-contest' ) ) . '</h2>' );
}


return new Wpvc_Common_Settings_Controller();
