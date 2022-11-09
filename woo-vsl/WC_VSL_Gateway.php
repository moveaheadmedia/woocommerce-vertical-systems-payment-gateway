<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class WC_VSL_Gateway extends WC_Payment_Gateway {

	protected $testmode = 'no';
	protected $merchant_id, $store_id, $user_id, $password, $auth;

	/**
	 * Constructor for the gateway.
	 */
	public function __construct() {
		// Setup general properties.
		$this->setup_properties();

		// gateways can support subscriptions, refunds, saved payment methods,
		// but in this tutorial we begin with simple payments
		$this->supports = array(
			'products'
		);

		// Method with all the options fields
		$this->init_form_fields();

		// Load the settings.
		$this->init_settings();

		// Get settings
		$this->title       = $this->get_option( 'title' );
		$this->description = $this->get_option( 'description' );
		$this->enabled     = $this->get_option( 'enabled' );
		$this->testmode    = 'yes' === $this->get_option( 'testmode' );
		$this->merchant_id = $this->testmode ? $this->get_option( 'test_merchant_id' ) : $this->get_option( 'merchant_id' );
		$this->store_id    = $this->testmode ? $this->get_option( 'test_store_id' ) : $this->get_option( 'store_id' );
		$this->user_id     = $this->testmode ? $this->get_option( 'test_user_id' ) : $this->get_option( 'user_id' );
		$this->password    = $this->testmode ? $this->get_option( 'test_password' ) : $this->get_option( 'password' );
		$this->auth    = base64_encode($this->user_id . ":" . $this->password);

		// This action hook saves the settings
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array(
			$this,
			'process_admin_options'
		) );

		// We need custom JavaScript to obtain a token
		add_action( 'woocommerce_receipt_vls', array( &$this, 'receipt_page' ) );
	}

	/**
	 * Setup general properties for the gateway.
	 */
	protected function setup_properties() {
		$this->id                 = 'vls'; // payment gateway plugin ID
		$this->icon               = ''; // URL of the icon that will be displayed on checkout page near your gateway name
		$this->has_fields         = true; // in case you need a custom credit card form
		$this->method_title       = 'VSL Gateway';
		$this->method_description = 'Vertical Systems Payment Gateway Integration.'; // will be displayed on the options page
	}

	/**
	 * Receipt Page
	 */
	function receipt_page( $order_id ) {
		$order = new WC_Order($order_id);
		echo '<a class="button pay" href="' . esc_url( $this->get_payment_link($order) ) . '">' . _( 'Pay Now' ) . '</a>';
		echo ' ';
		echo '<a class="button cancel" href="' . esc_url( $order->get_cancel_order_url() ) . '">' . _( 'Cancel order &amp; restore cart' ) . '</a>';
	}


	/**
	 * Get the secured payment gateway link for the order
	 *
	 * @param $order WC_Order
	 *
	 * @return string
	 */
	public function get_payment_link( WC_Order $order): string {

		try {
			$client = new SoapClient('https://paymentgatewaybeta.verticalsystems.co.uk/Transactions.svc?wsdl', array('trace' => 1));

			$returnUrl = $order->get_checkout_payment_url( true );

			/** @noinspection PhpUndefinedMethodInspection */
			$response = $client->GetHostedURL_Detailed(array(
				'request' => array(
					'addressDetails' => '',
					'addressVerificationDetails' => '',
					'amount' => $order->get_total(),
					'checkoutDetails' => '',
					'clientSystemTransactionId' => $order->get_id(),
					'currencyCode' => $order->get_currency(),
					'merchantId' => $this->merchant_id,
					'redirectDetails' => array(
						'cancelUrl' => admin_url( 'admin-ajax.php?action=woo-vsl-cancel&id=').base64_encode($order->get_id()),
						'errorUrl' => admin_url( 'admin-ajax.php?action=woo-vsl-error&id=').base64_encode($order->get_id()),
						'pendingUrl' => admin_url( 'admin-ajax.php?action=woo-vsl-pending&id=').base64_encode($order->get_id()),
						'refusedUrl' => admin_url( 'admin-ajax.php?action=woo-vsl-refused&id=').base64_encode($order->get_id()),
						'returnUrl' => $returnUrl,
						'successUrl' => admin_url( 'admin-ajax.php?action=woo-vsl-success&id=').base64_encode($order->get_id())
					),
					'storeId' => $this->store_id,
					'type' => 'ecom.Sale',
					'userId' => $this->user_id,
				),
				'auth' => $this->auth,
				'AppName' => 'Woocommerce',
				'AppFileRef' => $order->get_id()
			));
			return $response->GetHostedURL_DetailedResult;
		} catch (SoapFault $e) {
			return '#' . $e->getMessage();
		}

	}

	/**
	 * Plugin options, we deal with it in Step 3 too
	 */
	public function init_form_fields() {

		$this->form_fields = array(
			'enabled'          => array(
				'title'       => 'Enable/Disable',
				'label'       => 'Enable VLS Gateway',
				'type'        => 'checkbox',
				'description' => '',
				'default'     => 'no'
			),
			'title'            => array(
				'title'       => 'Title',
				'type'        => 'text',
				'description' => 'This controls the title which the user sees during checkout.',
				'default'     => 'Credit Card',
				'desc_tip'    => true,
			),
			'description'      => array(
				'title'       => 'Description',
				'type'        => 'textarea',
				'description' => 'This controls the description which the user sees during checkout.',
				'default'     => 'Pay with your credit card secured by Vertical Systems.',
			),
			'testmode'         => array(
				'title'       => 'Test mode',
				'label'       => 'Enable Test Mode',
				'type'        => 'checkbox',
				'description' => 'Place the payment gateway in test mode and use Test credentials.',
				'default'     => 'yes',
				'desc_tip'    => true,
			),
			'test_merchant_id' => array(
				'title'       => 'Test Merchant ID',
				'description' => '(Provided by VLS)',
				'type'        => 'text'
			),
			'test_store_id'    => array(
				'title'       => 'Test Store ID',
				'description' => '(Provided by VLS)',
				'type'        => 'text'
			),
			'test_user_id'     => array(
				'title'       => 'Test User ID',
				'description' => '(Provided by VLS)',
				'type'        => 'text'
			),
			'test_password'    => array(
				'title'       => 'Test Password',
				'description' => '(Provided by VLS)',
				'type'        => 'password',
			),
			'merchant_id'      => array(
				'title'       => 'Merchant ID',
				'description' => '(Provided by VLS)',
				'type'        => 'text'
			),
			'store_id'         => array(
				'title'       => 'Store ID',
				'description' => '(Provided by VLS)',
				'type'        => 'text'
			),
			'user_id'          => array(
				'title'       => 'User ID',
				'description' => '(Provided by VLS)',
				'type'        => 'text'
			),
			'password'         => array(
				'title'       => 'Password',
				'description' => '(Provided by VLS)',
				'type'        => 'password',
			)
		);


	}

	/**
	 * Process the payment and return the result.
	 *
	 * @param int $order_id Order ID.
	 *
	 * @return array
	 */
	public function process_payment( $order_id ): array {
		$order = new WC_Order( $order_id );

		$checkout_payment_url = $order->get_checkout_payment_url( true );

		return array(
			'result'   => 'success',
			'redirect' => add_query_arg( 'order', $order_id, add_query_arg( 'key', $order->get_order_key(), $checkout_payment_url ) )
		);
	}

	public function payment_fields() {

		// ok, let's display some description before the payment form
		if ( $this->description ) {
			if ( $this->testmode ) {
				$this->description = trim( $this->description );
				$this->description .= _('<p>TEST MODE ENABLED. In test mode, you can use this card number <abbr>4111 1111 1111 1517</abbr></p>');
			}
			// display the description with <p> tags etc.
			echo wpautop( wp_kses_post( $this->description ) );
		}
	}
}