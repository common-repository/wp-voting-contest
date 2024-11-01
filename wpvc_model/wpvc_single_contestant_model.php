<?php
if ( ! class_exists( 'Wpvc_Single_Contestant_Model' ) ) {
	/**
	 *  Single Contestant model.
	 */
	class Wpvc_Single_Contestant_Model {
		/**
		 *  Checking single template.
		 */
		public static function wpvc_check_voting_single_template() {
			if ( wpvc_check_active_theme( array( 'twentynineteen', 'twentyseventeen', 'twentysixteen', 'twentyfifteen', 'twentyfourteen', 'twentyeleven', 'twentytwelve', 'twentyten' ) ) ) {

				remove_action( 'ow_before_single_content', 'ow_before_single_content_wrapper', 10 );
				remove_action( 'ow_after_single_content', 'ow_after_single_content_wrapper', 10 );

				switch ( get_template() ) {

					case 'twentyseventeen':
						add_action( 'ow_before_single_content', 'ow_twenty_seventeen_support_before', 10 );
						add_action( 'ow_after_single_content', 'ow_twenty_seventeen_support_after', 10 );
						break;
					case 'twentynineteen':
						add_filter( 'twentynineteen_can_show_post_thumbnail', '__return_false' );
						add_action( 'ow_before_single_content', 'ow_twenty_nineteen_support_before', 10 );
						add_action( 'ow_after_single_content', 'ow_twenty_nineteen_support_after', 10 );
						break;
					case 'twentyfourteen':
						add_action( 'ow_before_single_content', 'ow_twenty_fourteen_support_before', 10 );
						add_action( 'ow_after_single_content', 'ow_twenty_fourteen_support_after', 10 );
						break;
					case 'twentyfifteen':
						add_action( 'ow_before_single_content', 'ow_twenty_fifteen_support_before', 10 );
						add_action( 'ow_after_single_content', 'ow_twenty_fifteen_support_after', 10 );
						break;

				}
			}
		}

	}
} else {
	die( '<h2>' . esc_html( __( 'Failed to load Voting Single Contestant model' ) ) . '</h2>' );
}

return new Wpvc_Single_Contestant_Model();

