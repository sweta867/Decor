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

ini_set('display_errors', 'on');
error_reporting(E_ALL);

require_once(dirname(__FILE__) . '/../../../../../wp-load.php');

/**
 * Class TRS_PHPUnit_Bootstrap
 */
class TRS_PHPUnit_Bootstrap
{
    protected static $instance = null;

    public function __construct()
    {
        $this->setup_database();
    }

    public function setup_database()
    {
        global $wpdb, $table_prefix;

        $wpdb_test    = new wpdb_Test(DB_USER, DB_PASSWORD, DB_NAME, DB_HOST);
        $wpdb         = $wpdb_test;
        $table_prefix = 'wp_trstest_';
        wp_set_wpdb_vars();

        $tables = file_get_contents(TRS()->plugin_path() . 'tests/phpunit/data/database.sql');
        $tables = str_replace('{{prefix}}', $wpdb->prefix, $tables);
        $tables = str_replace('{{charset_collate}}', $wpdb->get_charset_collate(), $tables);
        $tables = array_map('trim', explode('{{separator}}', $tables));

        foreach ($tables as $table) {
            $wpdb->query($table);
        }
    }

    public static function instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}

/**
 * Class wpdb_Test
 */
class wpdb_Test extends \wpdb
{
    protected $method_return_false = array();
    protected $method_nth_call = array(
        'insert' => 0,
        'update' => 0,
        'delete' => 0,
        'query'  => 0
    );
    protected $method_print_query = array();

    public function add_method_return_false($method, $nth_call = 1, $print_query = false)
    {
        $this->method_return_false[$method] = 1;
        $this->method_nth_call[$method]     = $nth_call;
        $this->method_print_query[$method]  = $print_query;
    }

    public function delete_method_return_false($method)
    {
        unset($this->method_return_false[$method]);
    }

    public function insert($table, $data, $format = null)
    {
        $this->method_nth_call['insert']--;

        if (isset($this->method_return_false['insert']) && $this->method_nth_call['insert'] === 0) {
            $this->delete_method_return_false('insert');

            if ($this->method_print_query['insert']) {
                echo $table . ', ' . json_encode($data) . PHP_EOL;
            }

            return false;
        }

        return parent::insert($table, $data, $format);
    }

    public function update($table, $data, $where, $format = null, $where_format = null)
    {
        $this->method_nth_call['update']--;

        if (isset($this->method_return_false['update']) && $this->method_nth_call['update'] === 0) {
            $this->delete_method_return_false('update');

            if ($this->method_print_query['update']) {
                echo $table . ', ' . json_encode($data) . ', ' . json_encode($where) . PHP_EOL;
            }

            return false;
        }

        return parent::update($table, $data, $where, $format, $where_format);
    }

    public function delete($table, $where, $where_format = null)
    {
        $this->method_nth_call['delete']--;

        if (isset($this->method_return_false['delete']) && $this->method_nth_call['delete'] === 0) {
            $this->delete_method_return_false('delete');

            if ($this->method_print_query['delete']) {
                echo $table . ', ' . json_encode($where) . PHP_EOL;
            }

            return false;
        }

        return parent::delete($table, $where, $where_format);
    }

    public function query($query)
    {
        $this->method_nth_call['query']--;

        if (isset($this->method_return_false['query']) && $this->method_nth_call['query'] === 0) {
            $this->delete_method_return_false('query');

            if ($this->method_print_query['query']) {
                echo $query . PHP_EOL;
            }

            return false;
        }

        return parent::query($query);
    }
}

/**
 * Class TRS_Tests
 */
class TRS_Tests extends \PHPUnit_Framework_TestCase
{
    public static function truncate_tables()
    {
        global $wpdb;

        $wpdb->query('TRUNCATE TABLE `' . $wpdb->prefix . 'term_taxonomy`;');
        $wpdb->query('TRUNCATE TABLE `' . $wpdb->prefix . 'terms`;');
        $wpdb->query('TRUNCATE TABLE `' . $wpdb->prefix . 'icl_translations`;');
        $wpdb->query('TRUNCATE TABLE `' . $wpdb->prefix . 'woocommerce_shipping_zones`;');
        $wpdb->query('TRUNCATE TABLE `' . $wpdb->prefix . 'woocommerce_shipping_zone_methods`;');
        $wpdb->query('TRUNCATE TABLE `' . $wpdb->prefix . 'woocommerce_shipping_zone_locations`;');
        $wpdb->query('TRUNCATE TABLE `' . $wpdb->prefix . 'woocommerce_shipping_table_rate`;');
    }

    public static function setup_sample_zones()
    {
        global $wpdb;

        $wpdb->query(
            'INSERT INTO `' . $wpdb->prefix . 'woocommerce_shipping_zones`
             (`zone_id`, `zone_name`, `zone_order`) VALUES
             (1, "Asia", 0);'
        );
        $wpdb->query(
            'INSERT INTO `' . $wpdb->prefix . 'woocommerce_shipping_zone_methods`
             (`instance_id`, `zone_id`, `method_id`, `method_order`, `is_enabled`) VALUES
             (1, 1, "table_rate", 1, 1),
             (2, 1, "table_rate", 2, 1);'
        );
        $wpdb->query(
            'INSERT INTO `' . $wpdb->prefix . 'woocommerce_shipping_zone_locations`
             (`location_id`, `zone_id`, `location_code`, `location_type`) VALUES
             (1, 1, "AS", "continent"),
             (2, 1, "IN", "country"),
             (3, 1, "IN:GJ", "state"),
             (4, 1, "364290", "postcode"),
             (5, 1, "364291...364295", "postcode");'
        );
    }

    public static function setup_sample_classes()
    {
        global $wpdb;

        $wpdb->query(
            'INSERT INTO `' . $wpdb->prefix . 'term_taxonomy`
             (`term_taxonomy_id`, `term_id`, `taxonomy`, `description`, `parent`, `count`) VALUES
             (1, 1, "product_shipping_class", "", 0, 0),
             (2, 2, "product_shipping_class", "", 0, 0),
             (3, 3, "product_type", "", 0, 0);'
        );
        $wpdb->query(
            'INSERT INTO `' . $wpdb->prefix . 'terms` (`term_id`, `name`, `slug`, `term_group`) VALUES
             (1, "SClass One", "sclass-one", 0),
             (2, "SClass Two", "sclass-two", 0),
             (3, "simple", "simple", 0);'
        );
        $wpdb->query(
            'INSERT INTO `' . $wpdb->prefix . 'icl_translations` (`translation_id`,`element_type`,`element_id`,
                 `trid`,`language_code`,`source_language_code`) VALUES
             (1, "tax_product_shipping_class", 1, 80, "en", NULL),
             (2, "tax_product_shipping_class", 2, 81, "en", NULL);'
        );
    }

    public static function setup_sample_table_rates()
    {
        global $wpdb;

        $wpdb->query(
            'INSERT  INTO `' . $wpdb->prefix . 'woocommerce_shipping_table_rate`
             (`table_rate_id`,`instance_id`,`class`,`weight_from`,`weight_to`,`price_from`,`price_to`,
                 `quantity_from`,`quantity_to`,`volume_from`,`volume_to`,`cost`,`comment`,`active`,`order`) VALUES
             (1, 1, "1",   1, 2, 0, 0, 0, 0, 0, 0, "1", "comment one",   1, 1),
             (2, 1, "1",   2, 3, 0, 0, 0, 0, 0, 0, "2", "comment one",   1, 2),
             (3, 1, "2",   0, 0, 0, 0, 0, 0, 0, 0, "3", "comment three", 1, 3),
             (4, 2, "2",   0, 0, 0, 0, 0, 0, 0, 0, "4", "comment four",  1, 4),
             (5, 2, "1,2", 0, 0, 0, 0, 0, 0, 0, 0, "5", "comment five",  1, 5),
             (6, 2, "1,2", 0, 0, 0, 0, 0, 0, 0, 0, "6", "comment six",   1, 6);'
        );
    }
}

TRS_PHPUnit_Bootstrap::instance();