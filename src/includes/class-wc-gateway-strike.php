<?php
if (!defined('ABSPATH')) {
	exit;
}

class WC_Gateway_Strike extends WC_Payment_Gateway {

	/**
	 * Logging enabled?
	 *
	 * @var bool
	 */
	public $logging;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->id                   = 'strike';
		$this->method_title         = __('WooCommerce Strike Gateway for Lightning', 'woocommerce-strike');
		$this->method_description   = __('This gateway uses Strike to generate a Lightning Network Payment Request for your customers. Your shop is notified as soon when the payment is received.', 'woocommerce-strike');
		$this->has_fields           = true;

		// Load the form fields.
		$this->init_form_fields();

		// Get setting values.
		$this->title                  = $this->get_option('title');
		$this->description            = $this->get_option('description');
		$this->enabled                = $this->get_option('enabled');
		$this->strike_image           = $this->get_option('strike_image', '');
		$this->live_secret_key        = $this->get_option('live_secret_key');
		$this->test_secret_key        = $this->get_option('test_secret_key');
		$this->testmode               = 'yes' === $this->get_option('testmode');
		$this->logging                = 'yes' === $this->get_option('logging');
		$this->secret_key             = $this->testmode ? $this->test_secret_key : $this->live_secret_key;
		$this->order_button_text = __('Pay with Lightning', 'woocommerce-strike');
		
		// set plugin properties according to the settings
		$this->endpoint 						= $this->testmode ? 'https://api.dev.strike.acinq.co/api/v1' : 'https://api.strike.acinq.co/api/v1';
		$this->view_transaction_url = $this->testmode ? 'https://dev.strike.acinq.co/dashboard/charges/%s' : 'https://strike.acinq.co/dashboard/charges/%s';
		
		// Hooks.
		wp_register_style( 'wc_strike_css', WC_HTTPS::force_https_url(plugins_url('/assets/css/wc_strike.css', WC_STRIKE_MAIN_FILE)));
		add_action('admin_notices', array( $this, 'admin_notices'));
		add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
		add_action('woocommerce_receipt_' . $this->id, array($this, 'show_payment'));
		add_action('woocommerce_thankyou_' . $this->id, array($this, 'show_payment'));
		add_action('woocommerce_email_before_order_table', array($this, 'email_instructions'), 10, 3);
		// listen for http requests on /?wc-api=wc_gateway_strike
		add_action('woocommerce_api_wc_gateway_strike', array($this, 'strike_webhook_endpoint'));
	}

	/**
	 * Add content to the WC emails.
	 *
	 * @access public
	 * @param WC_Order $order
	 * @param bool $sent_to_admin
	 * @param bool $plain_text
	 */
	public function email_instructions ($order, $sent_to_admin, $plain_text = false) {
		if (!$sent_to_admin && $this->id === $order->payment_method && $order->has_status('pending')) {
			echo wpautop(wptexturize('Thanks for your trust.')) . PHP_EOL;
		}
	}

	/**
	 * Amount to pay, in satoshis
	 */
	public function get_amount_satoshi ($total) {
		return $total * 1000 * 100000;
	}

	/**
	 * Check if SSL is enabled and notify the user
	 */
	public function admin_notices() {
		if ('no' === $this->enabled) {
			return;
		}

		// Show message if enabled and FORCE SSL is disabled and WordpressHTTPS plugin is not detected.
		if ((function_exists('wc_site_is_https') && !wc_site_is_https()) && ('no' === get_option('woocommerce_force_ssl_checkout') && !class_exists('WordPressHTTPS'))) {
			echo '<div class="error"><p>' . sprintf(__('Strike is enabled, but the <a href="%s">force SSL option</a> is disabled; <strong>Strike will not be available</strong> as long as SSL is not enabled.', 'woocommerce-strike'), admin_url('admin.php?page=wc-settings&tab=checkout')) . '</p></div>';
		}
		
		$setting_link = admin_url('admin.php?page=wc-settings&tab=checkout&section=' . $this->id);
		if ($this->testmode) {
			echo sprintf(__('<div class="notice notice-warning"><p><strong>Lightning Strike plugin is <a href="%s">in TEST mode</a>, payments with this gateway use TESTNET coins which hold no value!</strong></p></div>'), $setting_link);
		} else {
			if (empty($this->live_secret_key) && !(isset($_GET['page'], $_GET['section']) && 'wc-settings' === $_GET['page'] && 'strike' === $_GET['section'])) {
				echo "<div class='notice notice-warning'><p><strong>";
				echo sprintf(__('The Lightning Strike plugin is almost ready. To get started, <a href="%s">set your production secret api key</a>.', 'woocommerce-strike'), $setting_link);
				echo "</strong></p></div>";
			}
		}
	}
	
	/**
	 * Check if this gateway is enabled.
	 * 
	 * - SSL must be activated.
	 * - testing mode is ON and test_secret_key is set
	 *   OR
	 *   testing mode is off and live_secret_key is set
	 */
	public function is_available() {
		if ('yes' === $this->enabled) {
			if (!is_ssl()) {
				return false;
			}
			if ($this->is_mainnet_on()) {
				return true;
			}
			if ($this->is_testmode_on()) {
				return true;
			}
		}
		return false;
	}
	
	private function is_mainnet_on() {
		return !empty($this->live_secret_key) && !$this->testmode;
	}
	
	private function is_testmode_on() {
		return !empty($this->test_secret_key) && $this->testmode;
	}

	public function get_icon() {
		$style = version_compare(WC()->version, '2.6', '>=' ) ? 'style="height:1.4em; margin-left: 0.3em;"' : '';
		$icon = '<img src="' . WC_HTTPS::force_https_url(plugins_url('/assets/images/eclair-256x142.png', WC_STRIKE_MAIN_FILE)) . '" alt="Strike" height="24" ' . $style . ' />';

		return apply_filters('woocommerce_gateway_icon', $icon, $this->id);
	}


	/**
	 * Initialise Gateway Settings Form Fields
	 */
	public function init_form_fields() {
		$this->form_fields = include('settings-strike.php');
	}

	/**
	 * Process the payment
	 */
	public function process_payment($order_id) {
		try {
			$order = wc_get_order($order_id);
			// Handle payment.
			if ($order->get_total() > 0) {
				// Make the request.
				$response = $this->create_charge($this->generate_payment_request($order));
				if (is_wp_error($response)) {
					throw new Exception((isset($localized_messages[$response->get_error_code()]) ? $localized_messages[$response->get_error_code()] : $response->get_error_message()));
				}
				// Process valid response.
				$this->process_response($response, $order);
			} else {
				// if order value is 0, we can bypass the payment step and directly complete the order
				$order->payment_complete();
			}
			// Remove cart.
			WC()->cart->empty_cart();
			return array('result' => 'success', 'redirect' => $this->get_return_url($order));
		} catch (Exception $e) {
			wc_add_notice($e->getMessage(), 'error' );
			WC_Strike::log(sprintf(__('Error: %s', 'woocommerce-strike'), $e->getMessage()));
			return array('result' => 'fail', 'redirect' => '');
		}
	}

	/**
	 * Generate the request for the payment.
	 * @param  WC_Order $order
	 * @return array()
	 */
	protected function generate_payment_request($order) {
		$post_data                = array();
		$post_data['currency']    = strtolower($order->get_currency() ? $order->get_currency() : get_woocommerce_currency());
		$post_data['amount']      = $this->get_amount_satoshi($order->get_total(), $post_data['currency']);
		$post_data['description'] = sprintf(__('%s - Order %s', 'woocommerce-strike' ), wp_specialchars_decode(get_bloginfo('name'), ENT_QUOTES), $order->get_order_number());
		return $post_data;
	}

	public function process_response($response, $order) {
		// Store charge data
		update_post_meta($order->id, '_strike_charge_id', $response->id);
		update_post_meta($order->id, '_strike_payment_hash', $response->payment_hash);
		update_post_meta($order->id, '_strike_payment_request', $response->payment_request);
		update_post_meta($order->id, '_strike_amount_satoshi', $response->amount_satoshi);
		update_post_meta($order->id, '_transaction_id', $response->id);
		// add a note to the order
		$message = sprintf(__('Lightning payment is pending (charge: %s)', 'woocommerce-strike'), $response->id);
		$order->add_order_note($message);

		return $response;
	}

	/**
	 * Output for the order received page.
	 */
	public function show_payment($order_id) {
		if ($order_id) {
			$order = wc_get_order($order_id);
			wp_enqueue_style('wc_strike_css');
			$payment_request = get_post_meta($order->get_id(), '_strike_payment_request', true);
			$payment_hash = get_post_meta($order->get_id(), '_strike_payment_hash', true);
			$amount_satoshi = get_post_meta($order->get_id(), '_strike_amount_satoshi', true);
			if ($order->needs_payment()) {
				require __DIR__.'/payment_required.php';
			} elseif ($order->has_status(array('processing', 'completed'))) {
				require __DIR__.'/payment_completed.php';
			}
		}
	}

	/**
	 * Retrieve an order from a Strike charge id.
	 */
	private function get_order_for_charge($charge_id) {
		global $wpdb;
		// Faster than get_posts()
		$order_id = $wpdb->get_var($wpdb->prepare("SELECT post_id FROM {$wpdb->prefix}postmeta WHERE meta_key = '_strike_charge_id' AND meta_value = %s", $charge_id));
		if ($order_id > 0) {
			$order = wc_get_order($order_id);
			WC_Strike::log(sprintf(__('found order=%s', 'woocommerce-strike'), json_encode($order)));
			return $order;
		}
		WC_Strike::log(sprintf(__('order could not be found for charge=%s', 'woocommerce-strike'), $charge_id));
		return false;
	}

	/**
	 * Listens to POST and GET http requests, to either complete an order if Strike acknowledges the payment 
	 * of an order, or to check if an order is completed.
	 */
	public function strike_webhook_endpoint() {
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			$body = json_decode(file_get_contents('php://input'));
			if (isset($body->object) && $body->object == 'event' && isset($body->data) && isset($body->data->id)) {
				$charge_id = $body->data->id;
				WC_Strike::log(sprintf(__('received charge=%s payment notification', 'woocommerce-strike'), $charge_id));
				$order = $this->get_order_for_charge($charge_id);
				if ($order !== false) {
					if ($order->has_status('pending')) {
						$verification = $this->get_charge($charge_id);
						if ($verification->paid) {
							$order->payment_complete();
							wc_reduce_stock_levels($order->get_id());
							WC_Strike::log(sprintf(__('order has been completed, paid by charge %s.', 'woocommerce-strike'), $charge_id));
						} else {
							WC_Strike::log(sprintf(__('order=%s with charge=%s does not exist in Strike or has not been paid yet', 'woocommerce-strike'), $order->id, $charge_id));
						}
					} else {
						WC_Strike::log(sprintf(__('order=%s has already been paid', 'woocommerce-strike'), $order->id));
					}
				}
			} else {
				WC_Strike::log(sprintf(__('received incorrect notification that will be ignored', 'woocommerce-strike')));
			}
		} else if ($_SERVER['REQUEST_METHOD'] == 'GET') {
			if (isset($_GET['id'])) {
				$order_id = $_GET['id'];
				$order = wc_get_order($order_id);
				if ($order->is_paid()) {
					wp_send_json(true);
				} else {
					wp_send_json(false);
				}
			}
		}
		exit();
	}
	
	/**
	 * Send a charge creation request to Strike
	 *
	 * @param array $request
	 * @param string $api
	 * @return array|WP_Error
	 */
	public function create_charge($body) {
		WC_Strike::log($this->endpoint . ' POST charge=' . print_r($body, true));
		$response = wp_remote_post(
			$this->endpoint . '/charges',
			array(
				'method'     => 'POST',
				'headers'    => array('Authorization' => 'Basic ' . base64_encode($this->secret_key. ':')),
				'body'       => apply_filters('woocommerce_strike_request_body', $body),
				'timeout'    => 60, // in seconds
				'user-agent' => 'WooCommerce ' . WC()->version
			)
		);

		$response_status = wp_remote_retrieve_response_code($response);
		WC_Strike::log( "Response Status: " . print_r( $response_status, true));
		if (!($response_status >= 200 && $response_status < 300) || is_wp_error($response) || empty($response['body'])) {
			WC_Strike::log("Error Response: " . print_r( $response, true));
			return new WP_Error('strike_error', __('There was a problem connecting to the payment gateway.', 'woocommerce-strike'));
		}
		$parsed_response = json_decode($response['body']);
		if (!empty( $parsed_response->code)) {
			return new WP_Error($parsed_response->code, $parsed_response->message);
		} else {
			return $parsed_response;
		}
	}
	
	/**
	 * Retrieve a charge from Strike
	 *
	 * @param array $request
	 * @param string $api
	 * @return array|WP_Error
	 */
	public function get_charge($charge_id) {
		WC_Strike::log($this->endpoint . ' GET charge=' . $charge_id);
		$response = wp_remote_get(
			$this->endpoint . '/charges/' . $charge_id,
			array(
				'method'     => 'GET',
				'headers'    => array('Authorization' => 'Basic ' . base64_encode($this->secret_key . ':')),
				'timeout'    => 60, // in seconds
				'user-agent' => 'WooCommerce ' . WC()->version
			)
		);

		$response_status = wp_remote_retrieve_response_code($response);
		WC_Strike::log('response status: ' . print_r($response_status, true));
		if (!($response_status >= 200 && $response_status < 300) || is_wp_error($response) || empty($response['body'])) {
			WC_Strike::log("error in response: " . print_r($response, true));
			return new WP_Error('strike_error', __('There was a problem connecting to the payment gateway.', 'woocommerce-strike'));
		}
		$parsed_response = json_decode($response['body']);
		if (!empty($parsed_response->code)) {
			return new WP_Error($parsed_response->code, $parsed_response->message);
		} else {
			return $parsed_response;
		}
	}
	
}
