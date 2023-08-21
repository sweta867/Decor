<?php
/**
 * Plugin Name: WooCommerce Google Product Feed
 * Plugin URI: https://woocommerce.com/products/google-product-feed/
 * Description: WooCommerce extension that allows you to more easily populate advanced attributes into the Google Merchant Centre feed
 * Author: Ademti Software Ltd.
 * Version: 10.5.0
 * Woo: 18619:d55b4f852872025741312839f142447e
 * WC requires at least: 5.7
 * WC tested up to: 6.1
 * Requires PHP: 7.2.0
 * Author URI: https://www.ademti-software.co.uk/
 * License: GPLv3
 *
 * @package woocommerce-gpf
 */

defined( 'ABSPATH' ) || exit;

// The current DB schema version.
define( 'WOOCOMMERCE_GPF_DB_VERSION', 13 );

// The current version.
define( 'WOOCOMMERCE_GPF_VERSION', '10.5.0' );

$woocommerce_gpf_dirname = dirname( __FILE__ ) . '/';
require_once $woocommerce_gpf_dirname . 'vendor/woocommerce/action-scheduler/action-scheduler.php';
require_once $woocommerce_gpf_dirname . 'vendor/autoload.php';
require_once $woocommerce_gpf_dirname . 'woocommerce-product-feeds-install.php';
require_once $woocommerce_gpf_dirname . 'src/gpf/woocommerce-gpf-template-functions.php';
require_once $woocommerce_gpf_dirname . 'woocommerce-product-feeds-bootstrap.php';

register_activation_hook( __FILE__, 'woocommerce_gpf_install' );

/**
 * Run the plugin.
 */
global $woocommerce_product_feeds_main;
$woocommerce_product_feeds_main = $woocommerce_gpf_di['WoocommerceProductFeedsMain'];
$woocommerce_product_feeds_main->run();
