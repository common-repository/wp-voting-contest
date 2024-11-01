<?php
/**
 *  Get Current Theme.
 *
 * @param string $theme Theme name.
 */
function wpvc_check_active_theme( $theme ) {
	return is_array( $theme ) ? in_array( get_template(), $theme, true ) : get_template() === $theme;
}

add_action( 'wp_head', 'wpvc_sharing_contestant_function' );
/**
 *  Sharing Function.
 */
function wpvc_sharing_contestant_function() {

	if ( is_singular( WPVC_VOTES_TYPE ) ) {
		global $wpdb, $post;
		$post_id = $post->ID;
		// for sharing.
		$permalink1   = get_permalink( get_the_ID() );
		$image_path   = wpvc_vote_get_contestant_image( $post_id, 'large' );
		$ow_image_src = $image_path['ow_image_src'];
		$content_desc = html_entity_decode( strip_tags( $post->post_content ) );
		?>
				<!-- for Google -->
				<meta name="description" content="<?php echo esc_html( $content_desc ); ?>" />
				<meta name="keywords" content="" />

				<meta name="author" content="" />
				<meta name="copyright" content="" />
				<meta name="application-name" content="" />

				<!-- for Facebook -->
				<meta property="fb:app_id" content="966242223397117" />
				<meta property="og:title" content="<?php esc_html( get_the_title() ); ?>" />
				<meta property="og:type" content="article" />
				<meta property="og:url" content="<?php echo esc_html( $permalink1 ); ?>" />
				<meta property="og:image" content="<?php echo esc_html( $ow_image_src ); ?>" />

				<meta property="og:image:width" content="450" />
				<meta property="og:image:height" content="250" />
				<meta name="og:author" content="Voting"/>

				<!-- for Twitter -->
				<meta name="twitter:card" content="summary" />
				<meta name="twitter:title" content="<?php echo esc_html( get_the_title() ); ?>" />
				<meta name="twitter:description" content="<?php echo esc_html( $content_desc ); ?>" />
				<meta name="twitter:image" content="<?php echo esc_html( $ow_image_src ); ?>" />

				<link rel="image_src" href="<?php echo esc_html( $ow_image_src ); ?>"/>
			<?php
	}
}
/**
 *  Get Contestant Image.
 *
 * @param int   $post_id Post Id.
 * @param mixed $short_cont_image Image.
 */
function wpvc_vote_get_contestant_image( $post_id, $short_cont_image ) {
	if ( has_post_thumbnail( $post_id ) ) {
		$ow_image_arr            = wp_get_attachment_image_src( get_post_thumbnail_id( $post_id ), $short_cont_image );
		$data['ow_original_img'] = wp_get_attachment_url( get_post_thumbnail_id( $post_id ) ) . '?' . uniqid();
		$data['ow_image_src']    = $ow_image_arr[0];
	} else {
		$data['ow_image_src']    = WPVC_NO_IMAGE_CONTEST;
		$data['ow_original_img'] = WPVC_NO_IMAGE_CONTEST;
	}
	return $data;
}


if ( ! function_exists( 'check_license_key' ) ) {
	function check_license_key( $api_params, $store_url ) {
		$response = wp_remote_post(
			$store_url,
			array(
				'timeout'   => 15,
				'sslverify' => true,
				'body'      => $api_params,
			)
		);

		$status = '';
		if ( ! is_wp_error( $response ) && 200 == wp_remote_retrieve_response_code( $response ) ) {
			$license_data = json_decode( wp_remote_retrieve_body( $response ) );
			return $license_data->license;
		}

		return $status;
	}
}

?>
