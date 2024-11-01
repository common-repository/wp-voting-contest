<?php
/*
Plugin Name: WP Voting Contest Lite
Plugin URI: https://wpvotingcontest.com/?download=wordpress-voting-photo-contest-plugin
Description: Quickly and seamlessly integrate an online contest with voting into your WordPress 5.0+ website. You can start many types of online contests such as photo, video, audio, essay/writing with very little effort.
Author: Ohio Web Technologies
Author URI: https://ohiowebtech.com
Version: 5.5
*/
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wpdb;
define( 'WPVC_VOTE_VERSION', '5.5' );
/*********** File path constants */
define( 'WPVC_VOTES_ABSPATH', __DIR__ . '/' );
define( 'WPVC_VOTES_PATH', plugin_dir_url( __FILE__ ) );
define( 'WPVC_VOTES_SL_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WPVC_VOTES_SL_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'WPVC_VOTES_SL_PLUGIN_FILE', __FILE__ );
define( 'WPVC_WP_VOTING_SL_STORE_API_URL', 'https://wpvotingcontest.com' );
define( 'WPVC_WP_VOTING_SL_PRODUCT_NAME', 'WordPress Voting Photo Contest Plugin' );
define( 'WPVC_WP_VOTING_SL_PRODUCT_ID', 924 );

require_once 'configuration/config.php';
register_activation_hook( __FILE__, 'wpvc_activation_init' );
add_action(
	'after_setup_theme',
	function () {
		add_image_size( 'voting-image', 300, 0, true );
		add_image_size( 'voting800', 800, 0, true );
		add_image_size( 'voting1400', 1400, 0, true );
	}
);
register_deactivation_hook( __FILE__, 'wpvc_votes_deactivation_init' );

/**
 *  Add Cookie.
 */
add_action( 'send_headers', 'wpvc_add_header', 99 );
function wpvc_add_header() {
	if ( ! session_id() ) {
		session_start();
	}

	if ( ! array_key_exists( 'wpvc_freevoting_authorize', $_COOKIE ) ) {
		$create_random_hash = 'wpvcvotingcontestadmin' . wp_rand();
		$hash               = wp_hash( $create_random_hash );
		unset( $_COOKIE['wpvc_freevoting_authorize'] );
		setcookie( 'wpvc_freevoting_authorize', $hash, ( time() + 86400 ), '/' );
	}
}

add_action( 'admin_init', 'wpvc_votes_version_updater_admin' );

if ( ! function_exists( 'wpvc_votes_version_updater_admin' ) ) {
	function wpvc_votes_version_updater_admin() {
		$wp_voting_license_key = trim( get_option( 'wp_voting_software_license_key' ) );
		if ( ! empty( $wp_voting_license_key ) ) {
			$wp_voting = new Wpvc_Vote_Updater(
				WPVC_WP_VOTING_SL_STORE_API_URL,
				__FILE__,
				array(
					'version' => '5.5',
					'license' => $wp_voting_license_key,
					'item_id' => WPVC_WP_VOTING_SL_PRODUCT_ID,
					'author'  => 'Ohio Web Technologies',
				)
			);
		}
	}
}
