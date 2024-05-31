<?php

/**
 * Exit if accessed directly.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

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

	// Validate Turnstile request
	// @link https://suleymanozcan.medium.com/how-to-use-cloudflare-turnstile-with-vanilla-php-e409e5addb14
	$turnstile_secret   = TURNSTILE_SECRET_KEY;
	$turnstile_response = $_GET['cf-turnstile-response'];
	$url                = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';
	$post_fields        = "secret=$turnstile_secret&response=$turnstile_response";

	$ch = curl_init( $url );
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
	curl_setopt( $ch, CURLOPT_POST, true );
	curl_setopt( $ch, CURLOPT_POSTFIELDS, $post_fields );
	$response = curl_exec( $ch );
	curl_close( $ch );

	$response_data = json_decode( $response );
	if ( $response_data->success !== true ) {
		die( 'Captcha Failed' );
	}

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
