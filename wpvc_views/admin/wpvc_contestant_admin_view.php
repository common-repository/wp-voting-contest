<?php
if ( ! function_exists( 'wpvc_contestant_topbar' ) ) {
	/**
	 * Contestant bar.
	 */
	function wpvc_contestant_topbar() {
		remove_action( 'admin_notices', 'update_nag', 3 );
		?>
		<div id="wpvc-topbar-contestant" data-url="<?php echo esc_url( site_url( 'index.php' ) ); ?>" data-version="<?php echo esc_attr( WPVC_VOTE_VERSION ); ?>" data-adminurl="<?php echo esc_url( admin_url() ); ?>"></div>
		<?php
	}
} else {
	die( '<h2>' . esc_html_e( 'Failed to load Voting Contestant Top Bar', 'voting-contest' ) . '</h2>' );
}


?>
