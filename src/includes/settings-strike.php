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
		'live_secret_key' => array(
			'title'       => __('Production Secret Key', 'woocommerce-strike'),
			'type'        => 'text',
			'description' => __('The <strong>Production</strong> secret key provided by <a target="_blank" href="https://strike.acinq.co/login">Strike</a> (in Account > API Keys)', 'woocommerce-strike'),
			'default'     => '',
			'desc_tip'    => false,
		),
		'webhook' => array(
			'title'       => __('Your shop webhook', 'woocommerce-strike'),
			'type'        => 'text',
			'custom_attributes' => array('readonly' => 'readonly'),
			'description' => __('Save this url in your <a href="https://strike.acinq.co/dashboard/account/hooks" target="_blank">Strike account</a> so that Strike can notify your shop when a payment is received.', 'woocommerce-strike'),
			'default'     => __(home_url('/') . '?wc-api=WC_Gateway_Strike', 'woocommerce-strike'),
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
		'sandbox' => array(
			'title' 			=> __('Sandbox', 'woocommerce-strike'),
			'type'  			=> 'title',
			'description' => __('This section controls the Strike sandbox mode for Testnet.', 'woocommerce-strike')
		),
		'testmode' => array(
			'title'       => __('Enable test mode', 'woocommerce-strike'),
			'label'       => __('Payments will be made over Testnet, coins hold no value.', 'woocommerce-strike'),
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
		'checkout_page' => array(
			'title' 			=> __('Checkout page labels', 'woocommerce-strike'),
			'type'  			=> 'title',
			'description' => __('This section controls the labels that are displayed to your customers.', 'woocommerce-strike')
		),
		'title' => array(
			'title'       => __('Title', 'woocommerce-strike'),
			'type'        => 'text',
			'description' => __('This controls the name of the Strike gateway which the user sees during checkout.', 'woocommerce-strike'),
			'default'     => __('Strike', 'woocommerce-strike'),
			'desc_tip'    => true,
		),
		'description' => array(
			'title'       => __('Description', 'woocommerce-strike'),
			'type'        => 'text',
			'description' => __('This controls the description of the gateway which the user sees during checkout.', 'woocommerce-strike'),
			'default'     => __('Pay instantly with the Bitcoin Lightning Network.', 'woocommerce-strike'),
			'desc_tip'    => true,
		),
		'show_howto' => array(
			'title'       => __('Show Help', 'woocommerce-strike'),
			'label'       => __('Show `How To Pay with Lightning` help message.', 'woocommerce-strike'),
			'type'        => 'checkbox',
			'description' => __('Display a help message to the user when he\'s paying with Lightning.', 'woocommerce-strike'),
			'default'     => 'yes',
			'desc_tip'    => true,
		),
		'howto' => array(
			'title'       => __('Help message', 'woocommerce-strike'),
			'type'        => 'textarea',
			'description' => __('This controls the <em>`How To Pay with Lightning`</em> help message which the user sees during checkout.', 'woocommerce-strike'),
			'default'     => __('There are several lightning apps on the market, including app for mobile phones.<br />For android, you can use <a href="https://play.google.com/store/apps/details?id=fr.acinq.eclair.wallet.mainnet2">Eclair Wallet</a> or <a href="https://play.google.com/store/apps/details?id=com.lightning.walletapp">Anton Kumaigorodski\'s Lightning Wallet</a>.', 'woocommerce-strike'),
			'desc_tip'    => false,
		),
	)
);
