# Strike Gateway Plugin

This woocommerce plugin is a payment gateway for the Strike API, which enables payments with the Bitcoin Ligthning Network.

Note that Strike offers an [sandbox](https://dev.strike.acinq.co) to test payments with TESTNET coins on. This plugin has a test mode that enable the use of this sandbox.

## Usage

Requirements: Woocommerce 3+ and HTTPS

* Download the latest release and install it in the `wp-content/plugins` folder of your woocommerce website.
* Get your api key from the Account > Api Keys page in the Strike dashboard, and save it as plugin secret key in the plugin's settings page in your shop.

## Developers

Note: the structure of the project is taken from https://github.com/nezhar/wordpress-docker-compose. Thanks a lot! 

If you are interested in contributing to the development of this rpoject, follows these steps:

* Make sure you have the latest versions of **Docker** and **Docker Compose** installed on your machine.

* Then run this command to start a wordpress site from scratch:

```shell
sudo docker-compose up 
```

* You can access the wordpress website on `https://localhost:9999`. Complete the wordpress installation and install the WooCommerce plugin. Make sure that your shop uses the Bitcoin currency.

* Enable and configure the Strike Gateway plugin. Use the Test mode!

* Plugin code is in the `/src`. Changes in the code are automatically loaded to the website.
