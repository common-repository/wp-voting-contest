<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
if ( ! class_exists( 'Wpvc_Vote_Updater' ) ) {
	class Wpvc_Vote_Updater {

		private $api_url  = '';
		private $api_data = array();
		private $name     = '';
		private $slug     = '';
		private $version  = '';

		public function __construct( $_api_url, $_plugin_file, $_api_data = null ) {
			$this->api_url  = trailingslashit( $_api_url );
			$this->api_data = urlencode_deep( $_api_data );
			$this->name     = plugin_basename( $_plugin_file );
			$this->slug     = basename( $_plugin_file, '.php' );
			$this->version  = $_api_data['version'];

			$this->hook();
		}

		private function hook() {
			add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'pre_set_site_transient_update_plugins_filter' ) );
			add_filter( 'plugins_api', array( $this, 'plugins_api_filter' ), 10, 3 );
		}

		public function pre_set_site_transient_update_plugins_filter( $_transient_data ) {
			if ( empty( $_transient_data ) ) {
				return $_transient_data;
			}

			$to_send = array( 'slug' => $this->slug );

			$api_response = $this->api_request( 'plugin_latest_version', $to_send );

			if ( false !== $api_response && is_object( $api_response ) ) {
				if ( version_compare( $this->version, $api_response->new_version, '<' ) ) {
					$_transient_data->response[ $this->name ] = $api_response;
				}
			}
			return $_transient_data;
		}

		public function plugins_api_filter( $_data, $_action = '', $_args = null ) {
			if ( ( $_action != 'plugin_information' ) || ! isset( $_args->slug ) || ( $_args->slug != $this->slug ) ) {
				return $_data;
			}

			$to_send = array( 'slug' => $this->slug );

			$api_response = $this->api_request( 'plugin_information', $to_send );
			if ( false !== $api_response ) {
				$_data = $api_response;
			}

			return $_data;
		}

		private function api_request( $_action, $_data ) {

			global $wp_version;

			$data = array_merge( $this->api_data, $_data );
			if ( $data['slug'] != $this->slug ) {
				return;
			}

			$api_params = array(
				'edd_action' => 'get_version',
				'license'    => $data['license'],
				'item_id'    => $data['item_id'],
				'slug'       => $this->slug,
				'author'     => $data['author'],
			);
			$request    = wp_remote_post(
				$this->api_url,
				array(
					'timeout'   => 15,
					'sslverify' => true,
					'body'      => $api_params,
				)
			);

			if ( ! is_wp_error( $request ) ) {
				$request = json_decode( wp_remote_retrieve_body( $request ) );
				if ( $request ) {
					$request->sections = maybe_unserialize( $request->sections );
				}
				return $request;
			} else {
				return false;
			}
		}
	}
} else {
	die( '<h2>' . __( 'Failed to load the Voting Updater Controller', 'voting-contest' ) . '</h2>' );
}
