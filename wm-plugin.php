<?php
/**
 * Plugin Name: WM
 * Description: The WM plugin.
 * Version: 1.0.0
 * Requires at least: 6.5.3
 * Requires PHP: 8.0
 * Text Domain: wm
 */

/**
 * Exit if accessed directly.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Define Version and Paths
 */
define( 'WM_VERSION', '1.0.0' );
define( 'WM_URL', plugin_dir_url( __FILE__ ) ); // https://example.com/wp-content/plugins/wm-plugin/
define( 'WM_PATH', plugin_dir_path( __FILE__ ) ); // /absolute/path/to/wp-content/plugins/wm-plugin/
define( 'WM_BASENAME', plugin_basename( __FILE__ ) ); // wm-plugin/wm-plugin.php
define( 'WM_PLUGIN_FILE', __FILE__ ); // /absolute/path/to/wp-content/plugins/wm-plugin/wm-plugin.php

/**
 * Require Files
 */
require_once dirname( WM_PLUGIN_FILE ) . '/functions.php';
require_once dirname( WM_PLUGIN_FILE ) . '/filters.php';
require_once dirname( WM_PLUGIN_FILE ) . '/actions.php';
//require_once dirname( WM_PLUGIN_FILE ) . '/importer.php';

/**
 * Custom Image Sizes
 */
add_image_size( 'wm-collection-top', 1340, 1000, [ 'center', 'top' ] );
add_image_size( 'wm-collection-wkst', 1340, 364, [ 'center', 'top' ] );

add_theme_support( 'responsive-embeds' );