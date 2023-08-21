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

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

delete_option('tablerateshipping_version');
delete_option('tablerateshipping_csv_separator');
delete_option('tablerateshipping_import_zone_count');
delete_option('tablerateshipping_import_rule_count');

global $wpdb;

$sql = 'DROP TABLE IF EXISTS `' . $wpdb->prefix . 'woocommerce_shipping_table_rate`';

return $wpdb->query($sql);