<?php
/**
 * Woo VSL
 *
 * @package   woo-vsl
 * @author    Move Ahead Media <ali@moveaheadmedia.co.uk>
 * @copyright 2022 Woo VSL
 * @license   MIT
 * @link      https://moveaheadmedia.com
 *
 * Plugin Name:     Woo VSL
 * Plugin URI:      https://moveaheadmedia.com
 * Description:     a WordPress Woocommerce Plugins - Adds Vertical Systems Payment Gateway Integration To Your Woocommerce Store
 * Version:         1.0.0
 * Author:          Move Ahead Media
 * Author URI:      https://moveaheadmedia.com
 * Text Domain:     woo-vsl
 * Domain Path:     /languages
 * Requires PHP:    7.1
 * Requires WP:     5.5.0
 * Namespace:       WooVsl
 */

declare( strict_types = 1 );

/**
 * Define the default root file of the plugin
 *
 * @since 1.0.0
 */
const WOO_VSL_PLUGIN_FILE = __FILE__;

/**
 * Load PSR4 autoloader
 *
 * @since 1.0.0
 */
$woo_vsl_autoloader = require plugin_dir_path( WOO_VSL_PLUGIN_FILE ) . 'vendor/autoload.php';

/**
 * Setup hooks (activation, deactivation, uninstall)
 *
 * @since 1.0.0
 */
register_activation_hook( __FILE__, [ 'WooVsl\Config\Setup', 'activation' ] );
register_deactivation_hook( __FILE__, [ 'WooVsl\Config\Setup', 'deactivation' ] );
register_uninstall_hook( __FILE__, [ 'WooVsl\Config\Setup', 'uninstall' ] );

/**
 * Bootstrap the plugin
 *
 * @since 1.0.0
 */
if ( ! class_exists( '\WooVsl\Bootstrap' ) ) {
	wp_die( __( 'Woo VSL is unable to find the Bootstrap class.', 'woo-vsl' ) );
}
add_action(
	'plugins_loaded',
	static function () use ( $woo_vsl_autoloader ) {
		/**
		 * @see \WooVsl\Bootstrap
		 */
		try {
			new \WooVsl\Bootstrap( $woo_vsl_autoloader );
		} catch ( Exception $e ) {
			wp_die( __( 'Woo VSL is unable to run the Bootstrap class.', 'woo-vsl' ) );
		}
	}
);

/**
 * Create a main function for external uses
 *
 * @return \WooVsl\Common\Functions
 * @since 1.0.0
 */
function woo_vsl(): \WooVsl\Common\Functions {
	return new \WooVsl\Common\Functions();
}
