<?php
/*
 * Plugin Name: SecurionPay for WooCommerce
 * Plugin URI: https://securionpay.com
 * Description: Use SecurionPay for collecting credit card payments on WooCommerce.
 * Version: 1.0.5
 * Author: Securionpay
 * Author URI: https://securionpay.com
 */

if (!defined('ABSPATH')) {
	exit(); // Exit if accessed directly
}

class Securionpay4WC {
    
    const VERSION = '1.0.5';
    
	public function __construct() {
		// Grab settings
		$this->settings = get_option('woocommerce_securionpay4wc_settings', array());
		
		// Add default values for fresh installs
		$this->settings['testmode'] = isset($this->settings['testmode']) ? $this->settings['testmode'] : 'yes';
		$this->settings['test_public_key'] = isset($this->settings['test_public_key']) ? $this->settings['test_public_key'] : '';
		$this->settings['test_secret_key'] = isset($this->settings['test_secret_key']) ? $this->settings['test_secret_key'] : '';
		$this->settings['live_public_key'] = isset($this->settings['live_public_key']) ? $this->settings['live_public_key'] : '';
		$this->settings['live_secret_key'] = isset($this->settings['live_secret_key']) ? $this->settings['live_secret_key'] : '';
		$this->settings['saved_cards'] = isset($this->settings['saved_cards']) ? $this->settings['saved_cards'] : 'yes';

		// API Info
		$this->settings['public_key'] = $this->settings['testmode'] == 'yes' ? $this->settings['test_public_key'] : $this->settings['live_public_key'];
		$this->settings['secret_key'] = $this->settings['testmode'] == 'yes' ? $this->settings['test_secret_key'] : $this->settings['live_secret_key'];
		
		// Database info location
		$this->settings['securionpay_db_location'] = $this->settings['testmode'] == 'yes' ? 'securionpay4wc_test_customer' : 'securionpay4wc_live_customer';
		
		// Hooks
		add_filter('woocommerce_payment_gateways', array($this, 'addSecurionpayGateway'));
		
		// Localization
		load_plugin_textdomain('securionpay-for-woocommerce', false, dirname(plugin_basename(__FILE__)) . '/languages');
	}

	public function addSecurionpayGateway($methods) {
		if (class_exists('WC_payment_Gateway')) {
			require_once ('classes/SecurionpayPaymentGateway.php');
			$methods[] = 'SecurionpayPaymentGateway';
		}

		return $methods;
	}
	
	public static function getTemplate($template_name, $args = array()) {
		$template_path = WC()->template_path() . '/securionpay4wc/';
		$default_path = plugin_dir_path(__FILE__) . '/templates/';

		return wc_get_template($template_name, $args, $template_path, $default_path);
	}

	public static function allowSavedCards() {
		global $securionpay4wc;
		return is_user_logged_in() && $securionpay4wc->settings['saved_cards'] === 'yes';
	}
}

$GLOBALS['securionpay4wc'] = new Securionpay4WC();

require_once ('classes/SecurionpaySavedCards.php');
SecurionpaySavedCards::registerHooks();
