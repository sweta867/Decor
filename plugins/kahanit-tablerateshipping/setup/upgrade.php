<?php
/**
 * Table Rate Shipping by Class, Weight, Price, Quantity & Volume for WooCommerce - Kahanit
 *
 * Table Rate Shipping by Kahanit(https://www.kahanit.com/) is licensed under a
 * Creative Creative Commons Attribution-NoDerivatives 4.0 International License.
 * Based on a work at https://www.kahanit.com/.
 * Permissions beyond the scope of this license may be available at https://www.kahanit.com/.
 * To view a copy of this license, visit http://creativecommons.org/licenses/by-nd/4.0/.
 *
 * @author    Amit Sidhpura <amit@kahanit.com>
 * @copyright 2017 Kahanit
 * @license   http://creativecommons.org/licenses/by-nd/4.0/
 */

if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;

if (version_compare(get_option('tablerateshipping_version', '0.0.0'), '1.2.1', '<')) {
    $sql = 'ALTER TABLE `' . $wpdb->prefix . 'woocommerce_shipping_table_rate`
                CHANGE `weight_from`   `weight_from`   DECIMAL(24, 6) NOT NULL DEFAULT 0.000000,
	            CHANGE `weight_to`     `weight_to`     DECIMAL(24, 6) NOT NULL DEFAULT 0.000000,
	            CHANGE `price_from`    `price_from`    DECIMAL(24, 6) NOT NULL DEFAULT 0.000000,
	            CHANGE `price_to`      `price_to`      DECIMAL(24, 6) NOT NULL DEFAULT 0.000000,
	            CHANGE `quantity_from` `quantity_from` DECIMAL(24, 6) NOT NULL DEFAULT 0.000000,
	            CHANGE `quantity_to`   `quantity_to`   DECIMAL(24, 6) NOT NULL DEFAULT 0.000000,
	            CHANGE `volume_from`   `volume_from`   DECIMAL(24, 6) NOT NULL DEFAULT 0.000000,
	            CHANGE `volume_to`     `volume_to`     DECIMAL(24, 6) NOT NULL DEFAULT 0.000000';
    $wpdb->query($sql);
}

update_option('tablerateshipping_version', TRS()->version);