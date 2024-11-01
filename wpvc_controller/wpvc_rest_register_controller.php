<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
if ( ! class_exists( 'Wpvc_Rest_Register_Controller' ) ) {
	/**
	 * Admin Rest Controller.
	 */
	class Wpvc_Rest_Register_Controller {

		/**
		 * Controller Constructor.
		 */
		public function __construct() {
			add_action( 'rest_api_init', array( $this, 'wpvc_rest_api_register' ) );
		}
		/**
		 *  Get needed things for all settings on setting page.
		 */
		public function wpvc_rest_api_register() {
			register_rest_route(
				'wpvc-voting/v1',
				'/wpvcsettingfetch',
				array(
					'methods'       => 'GET',
					'callback'      => array( 'Wpvc_Rest_Actions_Controller', 'wpvc_callback_plugin_settings_page_data' ),
					'show_in_index' => false,
				)
			);

			register_rest_route(
				'wpvc-voting/v1',
				'/wpvcupdatesetting',
				array(
					'methods'       => 'POST',
					'callback'      => array( 'Wpvc_Rest_Actions_Controller', 'wpvc_callback_save_settings' ),
					'show_in_index' => false,
				)
			);

			register_rest_route(
				'wpvc-voting/v1',
				'/wpvcvotinglogsfetch',
				array(
					'methods'       => 'GET',
					'callback'      => array( 'Wpvc_Rest_Actions_Controller', 'wpvc_callback_plugin_voting_logs' ),
					'show_in_index' => false,
				)
			);

			register_rest_route(
				'wpvc-voting/v1',
				'/wpvcvotingdelete',
				array(
					'methods'       => 'POST',
					'callback'      => array( 'Wpvc_Rest_Actions_Controller', 'wpvc_callback_delete_voting_logs' ),
					'show_in_index' => false,
				)
			);

			register_rest_route(
				'wpvc-voting/v1',
				'/wpvcvotingmultipledelete',
				array(
					'methods'       => 'POST',
					'callback'      => array( 'Wpvc_Rest_Actions_Controller', 'wpvc_callback_delete_multiple_voting_logs' ),
					'show_in_index' => false,
				)
			);

			register_rest_route(
				'wpvc-voting/v1',
				'/wpvcmigratecontestants',
				array(
					'methods'       => 'POST',
					'callback'      => array( 'Wpvc_Rest_Actions_Controller', 'wpvc_callback_migrate_all_data' ),
					'show_in_index' => false,
				)
			);

			/****************************** Category Rest */
			register_rest_route(
				'wpvc-voting/v1',
				'/wpvccategoryfetch',
				array(
					'methods'       => 'GET',
					'callback'      => array( 'Wpvc_Rest_Actions_Controller', 'wpvc_callback_plugin_category_data' ),
					'show_in_index' => false,
				)
			);

			register_rest_route(
				'wpvc-voting/v1',
				'/wpvccategoryupdate',
				array(
					'methods'       => 'POST',
					'callback'      => array( 'Wpvc_Rest_Actions_Controller', 'wpvc_callback_plugin_category_update' ),
					'show_in_index' => false,
				)
			);
			register_rest_route(
				'wpvc-voting/v1',
				'/wpvccategorydelete',
				array(
					'methods'       => 'POST',
					'callback'      => array( 'Wpvc_Rest_Actions_Controller', 'wpvc_callback_plugin_category_delete' ),
					'show_in_index' => false,
				)
			);

			/****************************** Custom Fields */
			register_rest_route(
				'wpvc-voting/v1',
				'/wpvccustomfieldsfetch',
				array(
					'methods'       => 'GET',
					'callback'      => array( 'Wpvc_Rest_Actions_Controller', 'wpvc_callback_plugin_custom_field' ),
					'show_in_index' => false,
				)
			);

			register_rest_route(
				'wpvc-voting/v1',
				'/wpvcupdatecustomfield',
				array(
					'methods'       => 'POST',
					'callback'      => array( 'Wpvc_Rest_Actions_Controller', 'wpvc_callback_update_customfield' ),
					'show_in_index' => false,
				)
			);

			register_rest_route(
				'wpvc-voting/v1',
				'/wpvcassigncustom',
				array(
					'methods'       => 'POST',
					'callback'      => array( 'Wpvc_Rest_Actions_Controller', 'wpvc_callback_assign_custom' ),
					'show_in_index' => false,
				)
			);

			register_rest_route(
				'wpvc-voting/v1',
				'/wpvcgetassigncustom',
				array(
					'methods'       => 'POST',
					'callback'      => array( 'Wpvc_Rest_Actions_Controller', 'wpvc_callback_get_assign_custom' ),
					'show_in_index' => false,
				)
			);

			/****************************** Custom Reg Fields */

			register_rest_route(
				'wpvc-voting/v1',
				'/wpvcgetsitetranslations',
				array(
					'methods'       => 'GET',
					'callback'      => array( 'Wpvc_Rest_Actions_Controller', 'wpvc_callback_site_translations_data' ),
					'show_in_index' => false,
				)
			);

			/** Save license */
			register_rest_route(
				'wpvc-voting/v1',
				'/wpvcsavelicense',
				array(
					'methods'       => 'POST',
					'callback'      => array( 'Wpvc_Rest_Actions_Controller', 'wpvc_callback_plugin_license_update' ),
					'show_in_index' => false,
				)
			);
		}
	}
} else {
	die( '<h2>' . esc_html_e( 'Failed to load Voting Rest Actions Controller', 'voting-contest' ) . '</h2>' );
}


return new Wpvc_Rest_Register_Controller();
