<?php
if (!defined('ABSPATH')) {
	exit;
}

return apply_filters('wc_strike_settings',
	array(
		'enabled' => array(
			'title'       => __('Enable/Disable', 'woocommerce-strike'),
			'label'       => __('Enable Strike', 'woocommerce-strike'),
			'type'        => 'checkbox',
			'description' => '',
			'default'     => 'no'
		),
		'title' => array(
			'title'       => __('Title', 'woocommerce-strike'),
			'type'        => 'text',
			'description' => __('This controls the title which the user sees during checkout.', 'woocommerce-strike'),
			'default'     => __('Strike', 'woocommerce-strike'),
			'desc_tip'    => true,
		),
		'description' => array(
			'title'       => __('Description', 'woocommerce-strike'),
			'type'        => 'text',
			'description' => __('This controls the description which the user sees during checkout.', 'woocommerce-strike'),
			'default'     => __('Pay instantly with Bitcoin through the Lightning Network.', 'woocommerce-strike'),
			'desc_tip'    => true,
		),
		'live_secret_key' => array(
			'title'       => __('Production Secret Key', 'woocommerce-strike'),
			'type'        => 'text',
			'description' => __('The <strong>PRODUCTION</strong> secret key provided by <a target="_blank" href="https://strike.acinq.co/login">Strike</a> (in Account > API Keys)', 'woocommerce-strike'),
			'default'     => '',
			'desc_tip'    => false,
		),
		'webhook' => array(
			'title'       => __('Your shop webhook', 'woocommerce-strike'),
			'type'        => 'text',
			'custom_attributes' => array('readonly' => 'readonly'),
			'description' => __('Save this url in your <a href="https://strike.acinq.co/dashboard/account/hooks" target="_blank">Strike account</a> to help Strike notify your shop when a payment is received.', 'woocommerce-strike'),
			'default'     => _(home_url('/') . '?wc-api=WC_Gateway_Strike', 'woocommerce-strike'),
			'desc_tip'    => false,
		),
		'sandbox' => array(
			'title' 			=> __('Sandbox', 'woocommerce-strike'),
			'type'  			=> 'title',
			'description' => __('This section controls the Strike sandbox mode for Testnet.', 'woocommerce-strike')
		),
		'testmode' => array(
			'title'       => __('Enable test mode', 'woocommerce-strike'),
			'label'       => __('Payments will be made over TESTNET, coins hold no value.', 'woocommerce-strike'),
			'type'        => 'checkbox',
			'description' => __('If you enable test mode, you will interact with the <strong>Sandbox</strong> Strike API, and payments will be made for Testnet. Don\'t use this in production!!', 'woocommerce-strike'),
			'default'     => 'no'
		),
		'test_secret_key' => array(
			'title'       => __('Test mode Secret Key', 'woocommerce-strike'),
			'type'        => 'text',
			'description' => __('Use the secret key provided by the  <strong>Sandbox</strong> Strike environment', 'woocommerce-strike'),
			'default'     => '',
			'desc_tip'    => false,
		),
		'logging' => array(
			'title'       => __('Logging', 'woocommerce-strike'),
			'label'       => __('Log debug messages', 'woocommerce-strike'),
			'type'        => 'checkbox',
			'description' => __('Save debug messages to the WooCommerce System Status log.', 'woocommerce-strike'),
			'default'     => 'no',
			'desc_tip'    => true,
		),
	)
);
