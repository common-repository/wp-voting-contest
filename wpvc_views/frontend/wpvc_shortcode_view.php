<?php
if ( ! function_exists( 'wpvc_common_div_to_get' ) ) {
	/**
	 * Front end Page.
	 *
	 * @param mixed $shortcode Shortcode.
	 */
	function wpvc_common_div_to_get( $shortcode ) {
		?>
		<div id="wpvc-root-shortcode" data-rootcode="<?php echo esc_attr( $shortcode ); ?>" data-url="<?php echo esc_url( site_url( 'index.php' ) ); ?>"></div>
		<?php
	}
} else {
	die( '<h2>' . esc_html_e( 'Failed to load Voting Shortcode view', 'voting-contest' ) . '</h2>' );
}

if ( ! function_exists( 'wpvc_showcontestants_view' ) ) {
	/**
	 * Front end Page.
	 *
	 *  @param mixed $show_cont_args Shortcode.
	 */
	function wpvc_showcontestants_view( $show_cont_args ) {
		$term_id   = $show_cont_args['id'];
		$show_args = htmlspecialchars( wp_json_encode( $show_cont_args ), ENT_QUOTES, 'UTF-8' );
		?>
		<div id="wpvc-showcontestants-page-<?php echo esc_attr( $term_id ); ?>" class="wpvc_show_contestants" data-shortcode="showcontestants" data-url="<?php echo esc_url( site_url( 'index.php' ) ); ?>" data-args='<?php echo esc_attr( $show_args ); ?>' ></div>
		<?php
	}
} else {
	die( '<h2>' . esc_html_e( 'Failed to load Voting Showcontestants view', 'voting-contest' ) . '</h2>' );
}

if ( ! function_exists( 'wpvc_addcontestants_view' ) ) {
		/**
		 * Front end Page.
		 *
		 * @param mixed $show_cont_args Shortcode.
		 */
	function wpvc_addcontestants_view( $show_cont_args ) {
		$term_id   = $show_cont_args['id'];
		$show_args = htmlspecialchars( wp_json_encode( $show_cont_args ), ENT_QUOTES, 'UTF-8' );
		?>
		<div id="wpvc-addcontestant-page-<?php echo esc_attr( $term_id ); ?>" class="wpvc_show_contestants" data-shortcode="addcontestants" data-url="<?php echo esc_url( site_url( 'index.php' ) ); ?>" data-args='<?php echo esc_attr( $show_args ); ?>' ></div>
		<?php
	}
} else {
	die( '<h2>' . esc_html_e( 'Failed to load Voting Addcontestants view', 'voting-contest' ) . '</h2>' );
}

if ( ! function_exists( 'wpvc_upcoming_showcontestants_view' ) ) {
		/**
		 * Front end Page.
		 *
		 * @param mixed $show_cont_args Shortcode.
		 */
	function wpvc_upcoming_showcontestants_view( $show_cont_args ) {
		$show_args = htmlspecialchars( wp_json_encode( $show_cont_args ), ENT_QUOTES, 'UTF-8' );
		?>
		<div id="wpvc-upcomingcontestants-page" class="wpvc_show_contestants" data-shortcode="showcontestants" data-url="<?php echo esc_url( site_url( 'index.php' ) ); ?>" data-args='<?php echo esc_attr( $show_args ); ?>' ></div>
		<?php
	}
} else {
	die( '<h2>' . esc_html_e( 'Failed to load Voting Upcoming Contestants view', 'voting-contest' ) . '</h2>' );
}


if ( ! function_exists( 'wpvc_endcontest_showcontestants_view' ) ) {
		/**
		 * Front end Page.
		 *
		 * @param mixed $show_cont_args Shortcode.
		 */
	function wpvc_endcontest_showcontestants_view( $show_cont_args ) {
		$show_args = htmlspecialchars( wp_json_encode( $show_cont_args ), ENT_QUOTES, 'UTF-8' );
		?>
		<div id="wpvc-endcontestants-page" class="wpvc_show_contestants" data-shortcode="showcontestants" data-url="<?php echo esc_url( site_url( 'index.php' ) ); ?>" data-args='<?php echo esc_attr( $show_args ); ?>' ></div>
		<?php
	}
} else {
	die( '<h2>' . esc_html_e( 'Failed to load Voting End Contestants view', 'voting-contest' ) . '</h2>' );
}


?>
