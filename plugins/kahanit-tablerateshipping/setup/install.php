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

update_option('tablerateshipping_version', TRS()->version);
update_option('tablerateshipping_csv_separator', ',');
update_option('tablerateshipping_import_zone_count', 0);
update_option('tablerateshipping_import_rule_count', 0);

global $wpdb;

$sql = 'CREATE TABLE IF NOT EXISTS `' . $wpdb->prefix . 'woocommerce_shipping_table_rate` (
            `table_rate_id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            `instance_id`   BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
            `class`         TEXT                NOT NULL,
            `weight_from`   DECIMAL(24, 6)      NOT NULL DEFAULT 0.000000,
            `weight_to`     DECIMAL(24, 6)      NOT NULL DEFAULT 0.000000,
            `price_from`    DECIMAL(24, 6)      NOT NULL DEFAULT 0.000000,
            `price_to`      DECIMAL(24, 6)      NOT NULL DEFAULT 0.000000,
            `quantity_from` DECIMAL(24, 6)      NOT NULL DEFAULT 0.000000,
            `quantity_to`   DECIMAL(24, 6)      NOT NULL DEFAULT 0.000000,
            `volume_from`   DECIMAL(24, 6)      NOT NULL DEFAULT 0.000000,
            `volume_to`     DECIMAL(24, 6)      NOT NULL DEFAULT 0.000000,
            `cost`          TEXT                NOT NULL,
            `comment`       TEXT                NOT NULL,
            `active`        TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
            `order`         BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
            PRIMARY KEY (`table_rate_id`)
        ) ' . $wpdb->get_charset_collate();

return $wpdb->query($sql);