<?php
/**
 * Plugin Name: WooCommerce Strike Gateway for Lightning
 * Plugin URI: https://strike.acinq.co/woocommerce
 * Description: A gateway to pay with the Bitcoin Lightning Network through the Strike API.
 * Version: 1.0.0
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
		add_filter('plugin_action_links_' . plugin_basename(__FILE__), array($this, 'plugin_action_links'));
	}

	public function plugin_action_links( $links ) {
		$plugin_links = array(
			'<a href="admin.php?page=wc-settings&tab=checkout&section=strike">' . esc_html__( 'Settings', 'woocommerce-strike' ) . '</a>',
			'<a href="mailto:strike@acinq.co">' . esc_html__( 'Contact us', 'woocommerce-gateway-stripe' ) . '</a>',
		);
		return array_merge( $plugin_links, $links );
	}

	/**
	 * Add the gateways to WooCommerce
	 */
	public function add_gateways ( $methods ) {
		$methods[] = 'WC_Gateway_Strike';
		return $methods;
	}

	public static function get_instance() {
		if (null === self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __clone() {}

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
