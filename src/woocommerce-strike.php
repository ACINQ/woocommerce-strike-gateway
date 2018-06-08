<?php
/**
 * Plugin Name: WooCommerce Strike Gateway for lightning
 * Plugin URI: https://strike.acinq.co/woocommerce
 * Description: A gateway to pay with the Bitcoin Lightning Network through the Strike API.
 * Version: 0.0.1
 * Author: ACINQ
 * Author URI: https://acinq.co
 * Requires at least: 4.7
 * Tested up to: 4.7
 * License: Apache-2.0
 */
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

define('WC_STRIKE_MAIN_FILE', __FILE__);
if (!class_exists('WC_Strike')) :

class WC_Strike {

	/**
	 * @var Singleton The reference the *Singleton* instance of this class
	 */
	private static $instance;

	/**
	 * @var Reference to logging class.
	 */
	private static $log;

	/**
	 * Protected constructor to prevent creating a new instance of the
	 * *Singleton* via the `new` operator from outside of this class.
	 */
	protected function __construct () {
		add_action('plugins_loaded', array($this, 'init'));
	}

	/**
	 * Init the plugin after plugins_loaded so environment variables are set.
	 */
	public function init () {
		// Don't hook anything else in the plugin if we're in an incompatible environment
		if (!function_exists('curl_init') && is_plugin_active(plugin_basename(__FILE__))) {
			echo '<div class="notice notice-error">Strike Gateway: cURL is not installed.</strong></p></div>';
			return;
		}

		// Init the gateway itself
		$this->init_gateways();
	}

	/**
	 * Initialize the gateway. Called very early - in the context of the plugins_loaded action
	 *
	 * @since 1.0.0
	 */
	public function init_gateways () {
		if (!class_exists('WC_Payment_Gateway')) {
			return;
		}
		include_once(dirname(__FILE__) . '/includes/class-wc-gateway-strike.php');
		add_filter('woocommerce_payment_gateways', array($this, 'add_gateways'));
	}

	/**
	 * Add the gateways to WooCommerce
	 *
	 * @since 1.0.0
	 */
	public function add_gateways ( $methods ) {
		$methods[] = 'WC_Gateway_Strike';
		return $methods;
	}

	/**
	 * Returns the *Singleton* instance of this class.
	 *
	 * @return Singleton The *Singleton* instance.
	 */
	public static function get_instance() {
		if (null === self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Private clone method to prevent cloning of the instance of the
	 * *Singleton* instance.
	 *
	 * @return void
	 */
	private function __clone() {}

	/**
	 * Private unserialize method to prevent unserializing of the *Singleton*
	 * instance.
	 *
	 * @return void
	 */
	private function __wakeup() {}

	public static function log($message) {
		if (empty(self::$log)) {
			self::$log = new WC_Logger();
		}

		self::$log->add('woocommerce-strike', $message);

		if (defined('WP_DEBUG') && WP_DEBUG) {
			error_log('(strike) ' . $message);
		}
	}
}

$GLOBALS['wc_strike'] = WC_Strike::get_instance();

endif;

?>
