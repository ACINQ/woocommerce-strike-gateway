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
		'testmode' => array(
			'title'       => __('Test mode', 'woocommerce-strike'),
			'label'       => __('Payments will be made over TESTNET, coins hold no value.', 'woocommerce-strike'),
			'type'        => 'checkbox',
			'description' => __('If you enable test mode, you will interact with the <strong>TEST</strong> Strike API, and payments will be made for TESTNET. Don\'t use this in production!!', 'woocommerce-strike'),
			'default'     => 'no'
		),
		'test_secret_key' => array(
			'title'       => __('Test mode Secret Key', 'woocommerce-strike'),
			'type'        => 'text',
			'description' => __('Use the secret key provided by the  <strong>TEST</strong> Strike environment', 'woocommerce-strike'),
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
