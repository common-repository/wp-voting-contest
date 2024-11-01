<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
if ( ! class_exists( 'Wpvc_Common_Shortcode_Controller' ) ) {
	/**
	 * Common Shortcode Controller.
	 */
	class Wpvc_Common_Shortcode_Controller {
		/**
		 * Get thumbnail Size.
		 *
		 * @param mixed $get_seperate Size of image.
		 */
		public static function wpvc_vote_get_thumbnail_sizes( $get_seperate ) {
			global $_wp_additional_image_sizes;
			$sizes = array();
			foreach ( get_intermediate_image_sizes() as $s ) {
				if ( in_array( $s, array( $get_seperate ) ) ) {
					$sizes[ $s ][0] = get_option( $s . '_size_w' );
					$sizes[ $s ][1] = get_option( $s . '_size_h' );
				}
			}
			$all_sizes = array();
			foreach ( $sizes as $size => $atts ) {
				$all_sizes[ $size ] = implode( '--', $atts );
			}
			return $all_sizes[ $get_seperate ];
		}


	}
} else {
	die( '<h2>' . esc_html( __( 'Failed to load the Voting Common Shortcode Controller', 'voting-contest' ) ) . '</h2>' );
}

return new Wpvc_Common_Shortcode_Controller();
