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
 * Plugin Name:     Woocommerce Vertical Systems Payment Gateway
 * Plugin URI:      https://github.com/moveaheadmedia/woocommerce-vertical-systems-payment-gateway
 * Description:     WordPress Plugins - Adds Vertical Systems Payment Gateway Integration To Your Woocommerce Store
 * Version:         1.0.0
 * Author:          Move Ahead Media
 * Author URI:      https://moveaheadmedia.com
 * Text Domain:     woo-vsl
 * Domain Path:     /languages
 * Requires PHP:    7.1
 * Requires WP:     5.5.0
 * WC requires at least: 3.0
 * WC tested up to: 7.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'vsl_add_plugin_settings_link' );
add_filter( 'woocommerce_payment_gateways', 'vsl_add_gateway_class' );
add_action( 'plugins_loaded', 'vsl_init_gateway_class' );

// Admin Ajax
add_action( 'wp_ajax_nopriv_woo-vsl-cancel', 'vsl_admin_order_cancel' );
add_action( 'wp_ajax_woo-vsl-cancel', 'vsl_admin_order_cancel' );
add_action( 'wp_ajax_nopriv_woo-vsl-error', 'vsl_admin_order_error' );
add_action( 'wp_ajax_woo-vsl-error', 'vsl_admin_order_error' );
add_action( 'wp_ajax_nopriv_woo-vsl-pending', 'vsl_admin_order_pending' );
add_action( 'wp_ajax_woo-vsl-pending', 'vsl_admin_order_pending' );
add_action( 'wp_ajax_nopriv_woo-vsl-refused', 'vsl_admin_order_refused' );
add_action( 'wp_ajax_woo-vsl-refused', 'vsl_admin_order_refused' );
add_action( 'wp_ajax_nopriv_woo-vsl-success', 'vsl_admin_order_success' );
add_action( 'wp_ajax_woo-vsl-success', 'vsl_admin_order_success' );

function vsl_admin_order_cancel(){
	$order = new WC_Order(base64_decode($_GET['id']));

	$order->update_status('cancelled', 'Payment Cancelled');
	// some notes to customer (replace true with false to make it private)
	$order->add_order_note( 'Your payment has been cancelled! Bank Response: ' . $_GET['reason'], true );
	// some notes to customer (replace true with false to make it private)
	$order->add_order_note( 'Cancel: ' . $_GET['reason'], false );
	$order->add_order_note( 'transactionId: ' . $_GET['transactionId'], false );
	$order->add_order_note( 'card: ' . $_GET['card'], false );

	$return_url = apply_filters( 'woocommerce_get_return_url', $order->get_checkout_order_received_url(), $order );

	if ( wp_redirect( $return_url ) ) {
		exit;
	}
}

function vsl_admin_order_error(){
	$order = new WC_Order(base64_decode($_GET['id']));

	$order->update_status('failed', 'Payment Failed');
	// some notes to customer (replace true with false to make it private)
	$order->add_order_note( 'Your payment failed! Please try again later! Bank Response: ' . $_GET['reason'], true );
	// some notes to customer (replace true with false to make it private)
	$order->add_order_note( 'Error: ' . $_GET['reason'], false );
	$order->add_order_note( 'transactionId: ' . $_GET['transactionId'], false );
	$order->add_order_note( 'card: ' . $_GET['card'], false );

	$return_url = apply_filters( 'woocommerce_get_return_url', $order->get_checkout_order_received_url(), $order );

	if ( wp_redirect( $return_url ) ) {
		exit;
	}
}

function vsl_admin_order_pending(){
	$order = new WC_Order(base64_decode($_GET['id']));

	$order->update_status('pending', 'Payment is Pending');
	// some notes to customer (replace true with false to make it private)
	$order->add_order_note( 'Your payment is still pending! Bank Response: ' . $_GET['reason'], true );
	// some notes to customer (replace true with false to make it private)
	$order->add_order_note( 'Pending: ' . $_GET['reason'], false );
	$order->add_order_note( 'transactionId: ' . $_GET['transactionId'], false );
	$order->add_order_note( 'card: ' . $_GET['card'], false );

	$return_url = apply_filters( 'woocommerce_get_return_url', $order->get_checkout_order_received_url(), $order );

	if ( wp_redirect( $return_url ) ) {
		exit;
	}
}

function vsl_admin_order_refused(){
	$order = new WC_Order(base64_decode($_GET['id']));

	$order->update_status('pending', 'Payment is Pending');
	// some notes to customer (replace true with false to make it private)
	$order->add_order_note( 'Your payment was refused! Bank Response: ' . $_GET['reason'], true );
	// some notes to customer (replace true with false to make it private)
	$order->add_order_note( 'Refused: ' . $_GET['reason'], false );
	$order->add_order_note( 'transactionId: ' . $_GET['transactionId'], false );
	$order->add_order_note( 'card: ' . $_GET['card'], false );

	$return_url = apply_filters( 'woocommerce_get_return_url', $order->get_checkout_order_received_url(), $order );

	if ( wp_redirect( $return_url ) ) {
		exit;
	}
}

function vsl_admin_order_success(){
	$order = new WC_Order(base64_decode($_GET['id']));

	// we received the payment
	$order->payment_complete();

	// some notes to customer (replace true with false to make it private)
	$order->add_order_note( 'Your order is paid! Thank you!', true );

	// some notes to customer (replace true with false to make it private)
	$order->add_order_note( 'transactionId: ' . $_GET['transactionId'], false );
	$order->add_order_note( 'card: ' . $_GET['card'], false );
	$order->add_order_note( 'cd: ' . $_GET['cd'], false );
	$order->add_order_note( 'auth: ' . $_GET['auth'], false );

	$return_url = apply_filters( 'woocommerce_get_return_url', $order->get_checkout_order_received_url(), $order );

	if ( wp_redirect( $return_url ) ) {
		exit;
	}
}

function vsl_add_plugin_settings_link( $links ): array {
	$plugin_links = array(
		'<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout' ) . '">' . __( 'Settings' ) . '</a>',
	);

	return array_merge( $plugin_links, $links );
}

function vsl_add_gateway_class( $gateways ) {
	$gateways[] = 'WC_VSL_Gateway'; // your class name is here

	return $gateways;
}

/**
 * The class itself, please note that it is inside plugins_loaded action hook
 */
function vsl_init_gateway_class() {
	if(class_exists('WC_Payment_Gateway')){
		require_once 'WC_VSL_Gateway.php';
	}
}