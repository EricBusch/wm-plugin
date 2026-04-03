<?php

/**
 * Exit if accessed directly.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


add_filter( 'ss_skip_crawl_theme_directories', function ( $directories ) {
	$directories[] = 'node_modules';

	return $directories;
} );