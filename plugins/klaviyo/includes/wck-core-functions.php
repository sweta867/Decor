<?php

/**
 * WooCommerceKlaviyo Core Functions
 *
 * General core functions available on both the front-end and admin.
 *
 * @author    Klaviyo
 * @category  Core
 * @package   WooCommerceKlaviyo/Functions
 * @version   2.0.0
 */

if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

include('wck-cart-rebuild.php');
include('wck-added-to-cart.php');
include('wck-cart-functions.php');
include('wck-viewed-product.php');
/**
 * Adds WooCommerce Checkout Block compatibility - should only be
 * included if WooCommerce is activated and integration is setup
 * e.g. we have a public API key set.
 */
$klaviyo_settings = get_option('klaviyo_settings');
if (isset($klaviyo_settings['klaviyo_public_api_key'])) {
    include('wck-checkout-block.php');
}
