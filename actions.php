<?php

/**
 * Exit if accessed directly.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handle pretty download URLs for grid posts.
 *
 * Looks up a grid post by slug, increments its download count,
 * and redirects to the worksheet hub PDF.
 *
 * @return void
 */
function wm_grid_download_redirect(): void {

	$slug = sanitize_title( get_query_var( 'wm_download_slug', '' ) );

	if ( empty( $slug ) ) {
		return;
	}

	$grid = get_page_by_path( $slug, OBJECT, 'grid' );

	if ( ! $grid ) {
		wp_die( esc_html__( 'Grid not found.', 'wm' ), '', array( 'response' => 404 ) );
	}

	$wkst_id = absint( get_field( WKSTHB_KEY, $grid->ID ) );

	if ( $wkst_id <= 0 ) {
		wp_die( esc_html__( 'Invalid worksheet ID.', 'wm' ), '', array( 'response' => 404 ) );
	}

	wm_increment_download_count( $grid->ID );

	$url = esc_url( WKSTHB_URL . $wkst_id . '/' );

	?>
	<!DOCTYPE html>
	<html>
	<head>
		<meta charset="utf-8">
		<title><?php esc_html_e( 'Downloading...', 'wm' ); ?></title>
		<script>window.location.href = <?php echo wp_json_encode( $url ); ?>;</script>
	</head>
	<body>
		<p><?php esc_html_e( 'Your download will begin shortly.', 'wm' ); ?> <a href="<?php echo $url; ?>"><?php esc_html_e( 'Click here', 'wm' ); ?></a> <?php esc_html_e( 'if it does not start automatically.', 'wm' ); ?></p>
	</body>
	</html>
	<?php
	exit;
}

add_action( 'template_redirect', 'wm_grid_download_redirect' );

function wm_download_redirect(): void {

	/**
	 * If this is not the "Download" page, do nothing.
	 */
	if ( ! is_page( 'download' ) ) {
		return;
	}

	/**
	 * Set up required variables.
	 */
	$post_id   = absint( $_GET['post_id'] ?? 0 );
	$expires   = absint( $_GET['expires'] ?? 0 );
	$signature = trim( $_GET['signature'] ?? '' );

	// Validate that $post_id exists.
	if ( ! get_post( $post_id ) ) {
		die( 'Invalid post ID.' );
	}

	// Validate the download URL is not expired.
	if ( wm_download_url_is_expired( $expires ) ) {
		die( 'Download URL is expired. Please refresh the worksheet page and try downloading again.' );
	}

	// Validate signature.
	if ( ! wm_signature_is_valid( $post_id, $expires, $signature ) ) {
		die( 'Invalid signature.' );
	}

	// Validate that the Worksheet ID exists.
	$wkst_id = absint( get_field( WKSTHB_KEY, $post_id ) );
	if ( $wkst_id <= 0 ) {
		die( 'Invalid worksheet ID.' );
	}

	/**
	 * Update download counter.
	 */
	wm_increment_download_count( $post_id );

	// Redirect to Download file
	wp_redirect( WKSTHB_URL . $wkst_id . '/' );
	die;
}

add_action( 'template_redirect', 'wm_download_redirect' );

function wm_register_buy_button_block(): void {
	acf_register_block_type( [
		'name'            => 'buy-button',
		'title'           => __( 'Buy Button' ),
		'description'     => 'A buy button.',
		'render_template' => get_stylesheet_directory() . '/template-parts/blocks/buy-button/buy-button.php',
		'icon'            => 'button',
		'keywords'        => [ 'buy', 'button', 'gumroad' ],
	] );
}

add_action( 'acf/init', 'wm_register_buy_button_block' );

function wm_add_promos(): void {
	include_once get_stylesheet_directory() . '/template-parts/promos.php';
}

add_action( 'wm_after_hero', 'wm_add_promos' );

function wm_email( $atts ): string {

	$email = '';

	foreach ( str_split( EMAIL_ADDRESS ) as $char ) {

		$replace = rand( 0, 1 ) === 1;

		$email .= $replace
			? mb_encode_numericentity( $char, [ 0x000000, 0x10ffff, 0, 0xffffff ], 'UTF-8' )
			: $char;
	}

	return $email;
}

add_shortcode( 'wm_email', 'wm_email' );
