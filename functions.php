<?php

/**
 * Exit if accessed directly.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get total number of printable PDF pages. This currently includes
 * Collections and Worksheets.
 *
 * @return int
 */
function wm_get_total_printable_pdf_page_count(): int {

	$key = 'total_printable_pdf_pages';

	if ( false === ( $page_count = get_transient( $key ) ) ) {

		$page_count     = 0;
		$worksheet_ids  = wm_get_all_worksheets( [ 'fields' => 'ids' ] );
		$collection_ids = wm_get_all_collections( [ 'fields' => 'ids' ] );

		$page_count += count( $worksheet_ids );

		foreach ( $collection_ids as $collection_id ) {
			$page_count += absint( get_field( 'page_count', $collection_id ) );
		}

		set_transient( $key, $page_count, MONTH_IN_SECONDS );
	}

	return $page_count;
}

/**
 * Get total number of vocabulary words. This currently includes
 * Collections and Worksheets.
 *
 * @return int
 */
function wm_get_total_vocabulary_word_count(): int {

	$key = 'total_vocabulary_words';

	if ( false === ( $word_count = get_transient( $key ) ) ) {

		$word_count     = 0;
		$worksheet_ids  = wm_get_all_worksheets( [ 'fields' => 'ids' ] );
		$collection_ids = wm_get_all_collections( [ 'fields' => 'ids' ] );

		$word_count += count( $worksheet_ids );

		foreach ( $collection_ids as $collection_id ) {
			$word_count += absint( get_field( 'word_count', $collection_id ) );
		}

		set_transient( $key, $word_count, MONTH_IN_SECONDS );
	}

	return $word_count;
}

/**
 * Get total Collections.
 *
 * @return int
 */
function wm_get_total_collections(): int {

	$key = 'total_collections';

	if ( false === ( $collection_count = get_transient( $key ) ) ) {
		$collection_count = count( wm_get_all_collections( [ 'fields' => 'ids' ] ) );
		set_transient( $key, $collection_count, MONTH_IN_SECONDS );
	}

	return $collection_count;
}

/**
 * Get total Worksheets.
 *
 * @return int
 */
function wm_get_total_worksheets(): int {

	$key = 'total_worksheets';

	if ( false === ( $worksheet_count = get_transient( $key ) ) ) {
		$worksheet_count = count( wm_get_all_worksheets( [ 'fields' => 'ids' ] ) );
		set_transient( $key, $worksheet_count, MONTH_IN_SECONDS );
	}

	return $worksheet_count;
}

/**
 * Get all Collections
 *
 * @param array $args
 *
 * @return array
 */
function wm_get_all_collections( array $args = [] ): array {
	return get_posts( wp_parse_args( $args, [
		'post_type'      => 'collection',
		'post_status'    => 'publish',
		'posts_per_page' => - 1,
		'orderby'        => 'title',
		'order'          => 'ASC',
		'fields'         => 'all',
	] ) );
}

/**
 * Get all Worksheets.
 *
 * @param array $args
 *
 * @return array
 */
function wm_get_all_worksheets( array $args = [] ): array {
	return get_posts( wp_parse_args( $args, [
		'post_type'      => 'worksheet',
		'post_status'    => 'publish',
		'posts_per_page' => - 1,
		'orderby'        => 'title',
		'order'          => 'ASC',
		'fields'         => 'all',
	] ) );
}

/**
 * Get all Samples.
 *
 * @param array $args
 *
 * @return array
 */
function wm_get_all_samples( array $args = [] ): array {
	return get_posts( wp_parse_args( $args, [
		'post_type'      => 'sample',
		'post_status'    => 'publish',
		'posts_per_page' => - 1,
		'orderby'        => 'ID',
		'order'          => 'ASC',
		'fields'         => 'all',
	] ) );
}

/**
 * Get all Grids.
 *
 * @param array $args
 *
 * @return array
 */
function wm_get_all_grids( array $args = [] ): array {
	return get_posts( wp_parse_args( $args, [
		'post_type'      => 'grid',
		'post_status'    => 'publish',
		'posts_per_page' => - 1,
		'orderby'        => 'ID',
		'order'          => 'ASC',
		'fields'         => 'all',
	] ) );
}

function wm_get_all_collection_groups( array $args = [] ): array {
	return get_terms( wp_parse_args( $args, [
		'taxonomy'   => 'group',
		'hide_empty' => false,
		'orderby'    => 'name',
		'order'      => 'asc',
	] ) );
}

function wm_get_all_worksheet_themes( array $args = [] ): array {
	return get_terms( wp_parse_args( $args, [
		'taxonomy'   => 'worksheet_theme',
		'hide_empty' => false,
		'orderby'    => 'name',
		'order'      => 'asc',
	] ) );
}

/**
 * Return a "signature" for the $data.
 *
 * @param string $data
 *
 * @return bool|string
 * @since 0.9.68
 *
 */
function wm_hash_hmac( string $data ): bool|string {
	$algo = 'sha256';
	$key  = wm_get_hash();

	return hash_hmac( $algo, $data, $key );
}

/**
 * Returns the Hash value to use in signed requests.
 *
 * This function will only return a valid MD5 hash. If the value returned
 * from the database does not exist OR is an invalid MD5 hash, this
 * function will create and save a new MD5 hash and return the new hash.
 *
 * @return string
 * @since 0.9.68
 *
 */
function wm_get_hash(): string {

	$option = 'wm_hash';
	$hash   = get_option( $option, false );

	if ( wm_is_valid_md5( $hash ) ) {
		return $hash;
	}

	$password = wp_generate_password( 64, true, true );
	$hash     = wp_hash( $password );

	update_option( $option, $hash, false );

	return $hash;
}

/**
 * Validates the validity of an MD5 hash.
 *
 * @see https://stackoverflow.com/a/14300703
 *
 * @param string $md5
 *
 * @return bool
 */
function wm_is_valid_md5( string $md5 = '' ): bool {
	return boolval( preg_match( '/^[a-f0-9]{32}$/', $md5 ) );
}

function wm_get_download_url( int $post_id ): string {
	$qs = sprintf( 'post_id=%d&expires=%d', $post_id, wm_get_expiration_timestamp() );
	$qs = $qs . '&signature=' . wm_hash_hmac( $qs );

	return get_permalink( get_page_by_path( 'download' ) ) . '?' . $qs;
}

// This was for Turnstile integration...
function wm_get_download_input_fields( int $post_id ): array {
	$fields = [];

	$fields['post_id']   = $post_id;
	$fields['expires']   = wm_get_expiration_timestamp();
	$fields['signature'] = wm_hash_hmac( http_build_query( $fields ) );

	return $fields;
}

function wm_download_expiry_in_seconds(): int {
	return HOUR_IN_SECONDS * 12;
}

function wm_get_expiration_timestamp(): int {
	return absint( date_i18n( 'U' ) ) + wm_download_expiry_in_seconds();
}

function wm_download_url_is_expired( int $expiration_timestamp ): bool {
	return absint( date_i18n( 'U' ) ) > $expiration_timestamp;
}

function wm_signature_is_valid( int $post_id, int $expires, string $signature ): bool {
	$attempted_signature = wm_hash_hmac( sprintf( 'post_id=%d&expires=%d', $post_id, $expires ) );

	return $attempted_signature === $signature;
}

function wm_increment_download_count( int $post_id ): void {
	$key            = 'download_count';
	$download_count = absint( get_field( $key, $post_id ) );
	$download_count = $download_count + 1;
	update_field( $key, $download_count, $post_id );
}

function wm_current_user_is_admin(): bool {
	return current_user_can( 'manage_options' );
}

