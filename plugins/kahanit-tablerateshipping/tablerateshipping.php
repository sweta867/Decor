<?php
/**
 * Plugin Name:       Table Rate Shipping
 * Plugin URI:        https://www.kahanit.com/
 * Description:       Table Rate Shipping by Class, Weight, Price, Quantity & Volume for WooCommerce.
 * Version:           1.2.1
 * Author:            Kahanit
 * Author URI:        https://www.kahanit.com/
 * Requires at least: 4.4
 * Tested up to:      4.9
 * Text Domain:       tablerateshipping
 */
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

use TableRateShipping\libraries\TRS_Helper;
use TableRateShipping\libraries\TRS_Model;
use TableRateShipping\libraries\TRS_Method;

/**
 * Class TRS_Setup
 */
class TRS_Setup
{
    protected static $instance;

    public $version = '1.2.1';

    public static function instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function __construct()
    {
        // plugin setup
        register_activation_hook(__FILE__, array('TRS_Setup', 'activation'));
        add_action('plugins_loaded', array($this, 'init'));
        add_filter('plugin_action_links_' . $this->plugin_basename(), array($this, 'action_links'), 10, 2);
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_filter('mime_types', array($this, 'add_csv_mime_type'), 10, 1);
        add_filter('wp_check_filetype_and_ext', array($this, 'check_csv_mime_type'), 10, 3);
        add_filter('woocommerce_shipping_methods', array($this, 'shipping_methods'), 10, 1);
        add_action('woocommerce_shipping_zone_method_deleted', array($this, 'shipping_method_deleted'), 10, 1);
        add_action('woocommerce_settings_shipping', array($this, 'shipping_zones_footer'), 11);
        add_filter('woocommerce_get_settings_shipping', array($this, 'shipping_settings'));

        // ajax requests
        add_action('wp_ajax_trs_update', array($this, 'update'));
        add_action('wp_ajax_trs_get_rows', array($this, 'get_rows'));
        add_action('wp_ajax_trs_insert_rows', array($this, 'insert_rows'));
        add_action('wp_ajax_trs_update_rows_status', array($this, 'update_rows_status'));
        add_action('wp_ajax_trs_update_rows_order', array($this, 'update_rows_order'));
        add_action('wp_ajax_trs_delete_rows', array($this, 'delete_rows'));
        add_action('wp_ajax_trs_import_csv', array($this, 'import_csv'));
        add_action('wp_ajax_trs_import_status', array($this, 'import_status'));
        add_action('wp_ajax_trs_export_csv', array($this, 'export_csv'));
        add_action('wp_ajax_trs_optimize', array($this, 'optimize'));

        // tablerateshipping hooks for wpml
        add_action('tablerateshipping_new_table_rate', array($this, 'new_comment_translation'));
        add_action('tablerateshipping_update_table_rate', array($this, 'update_comment_translation'));
        add_action('tablerateshipping_delete_table_rates', array($this, 'delete_comment_translations'), 10, 2);
        add_filter('tablerateshipping_package_data_price', array($this, 'package_data_price'));
        add_filter('tablerateshipping_package_data_class', array($this, 'package_data_class'));
    }

    public static function activation()
    {
        if (!current_user_can('activate_plugins')) {
            return;
        }

        $plugin = isset($_REQUEST['plugin']) ? $_REQUEST['plugin'] : '';
        check_admin_referer("activate-plugin_{$plugin}");

        include_once TRS()->plugin_path() . 'setup/install.php';
    }

    public function init()
    {
        load_plugin_textdomain('tablerateshipping', false, basename(dirname(__FILE__)) . '/languages');

        if (version_compare(get_option('tablerateshipping_version', '0.0.0'), $this->version, '!=')) {
            include_once $this->plugin_path() . 'setup/upgrade.php';
        }

        require_once $this->plugin_path() . 'libraries/class-trs-helper.php';
        require_once $this->plugin_path() . 'libraries/class-trs-evalmath.php';
        require_once $this->plugin_path() . 'libraries/class-trs-model.php';
        require_once $this->plugin_path() . 'libraries/class-trs-method.php';
    }

    public function action_links($actions)
    {
        $settings = array('settings' => '<a href="admin.php?page=wc-settings&tab=shipping">' . __('Settings', 'tablerateshipping') . '</a>');
        $support  = array('support' => '<a href="https://www.kahanit.com/submit-ticket" target="_blank">' . __('Support', 'tablerateshipping') . '</a>');

        return array_merge($settings, $actions, $support);
    }

    public function enqueue_scripts()
    {
        $page = TRS_Helper::get('page', '');
        $tab  = TRS_Helper::get('tab', '');

        if ($page === 'wc-settings' && $tab === 'shipping') {
            wp_enqueue_style('tablerateshipping-style', $this->plugin_dir_url() . 'css/tablerate.css', array(), $this->version);

            $datatables_path = $this->plugin_dir_url() . 'js/jquery.dataTables.min.js';
            $script_path     = $this->plugin_dir_url() . 'js/tablerate.js';

            wp_enqueue_media();
            wp_enqueue_script('jquery-ui-sortable');
            wp_enqueue_script('jquery-effects-highlight');
            wp_enqueue_script('tablerateshipping-datatables', $datatables_path, array('jquery'), '1.10.13');
            wp_enqueue_script(
                'tablerateshipping-script',
                $script_path,
                array(
                    'tablerateshipping-datatables',
                    'jquery-ui-sortable',
                    'jquery-effects-highlight'
                ),
                $this->version,
                true
            );
        }
    }

    public function add_csv_mime_type($existing_mimes)
    {
        $existing_mimes['csv'] = 'text/csv';

        return $existing_mimes;
    }

    public function check_csv_mime_type($data, $file, $filename)
    {
        $filename = strtolower($filename);
        if (preg_match('/\.csv$/i', $filename, $matches)) {
            $data['ext']  = 'csv';
            $data['type'] = 'text/csv';
        }

        return $data;
    }

    public function shipping_methods($shipping_methods)
    {
        $shipping_methods['table_rate'] = TRS_Method::class;

        return $shipping_methods;
    }

    public function shipping_method_deleted($instance_id)
    {
        TRS_Model::delete_rows_by_instance_id($instance_id);
    }

    public function shipping_zones_footer()
    {
        $section     = trim(TRS_Helper::get('section', ''));
        $instance_id = TRS_Helper::get('instance_id', -1);
        $zone_id     = TRS_Helper::get('zone_id', -1);

        if ($section === '' && $instance_id === -1 && $zone_id === -1) { ?>
            <div id="trs-form">
                <div id="trs-container" class="trs-container-zone">
                    <h2><?= __('Table Rate Shipping', 'tablerateshipping') ?></h2>
                    <div class="trs-zone-tools button-group">
                        <button type="button" class="import button"><?= __('Import CSV', 'tablerateshipping') ?></button>
                        <a target="_blank" class="export button"
                           href="admin-ajax.php?action=trs_export_csv"><?= __('Export CSV', 'tablerateshipping') ?></a>
                    </div>
                    <div id="trs-help" class="trs-help-zone">
                        <ul>
                            <li><?= __('You can import and export zone details with table rate shipping rules.', 'tablerateshipping') ?></li>
                            <li><?= __('To get sample CSV format you can export CSV after creating few rules.', 'tablerateshipping') ?></li>
                            <li><?= __('If zone name already exists it will be updated else created new.', 'tablerateshipping') ?></li>
                        </ul>
                    </div>
                    <script type="text/javascript">
                        jQuery(function ($) {
                            $('#trs-container').tableRateShipping({
                                page_view: 'landing',
                                language: <?=
                                json_encode(
                                    array(
                                        'Showing {0} to {1} of {2} items'               => __('Showing {0} to {1} of {2} items', 'tablerateshipping'),
                                        'Items per page'                                => __('Items per page', 'tablerateshipping'),
                                        '- Any -'                                       => __('- Any -', 'tablerateshipping'),
                                        'Save'                                          => __('Save', 'tablerateshipping'),
                                        'Choose CSV'                                    => __('Choose CSV', 'tablerateshipping'),
                                        'Are you sure?'                                 => __('Are you sure?', 'tablerateshipping'),
                                        'No rules selected.'                            => __('No rules selected.', 'tablerateshipping'),
                                        'Imported &raquo; zone: {0} &raquo; rule: {1}.' => __('Imported &raquo; zone: {0} &raquo; rule: {1}.', 'tablerateshipping'),
                                        'Reload page to see the result.'                => __('Reload page to see the result.', 'tablerateshipping')
                                    )
                                ) ?>
                            });
                        });
                    </script>
                </div>
            </div>
        <?php }
    }

    public function shipping_settings($settings)
    {
        $settings_table_rate = array(
            array(
                'id'   => 'shipping_tablerateshipping',
                'name' => __('Table Rate Shipping', 'tablerateshipping'),
                'type' => 'title'
            ),
            array(
                'id'       => 'tablerateshipping_csv_separator',
                'name'     => __('CSV Separator', 'tablerateshipping'),
                'type'     => 'text',
                'desc'     => 'It is the separator used to render CSV file to import and export rules. Most commonly used separators are comma (,) and semicolon (;).',
                'desc_tip' => true
            ),
            array(
                'id'   => 'shipping_tablerateshipping',
                'type' => 'sectionend'
            )
        );

        return array_merge($settings, $settings_table_rate);
    }

    public function update()
    {
        $table_rate_id     = TRS_Helper::post('table_rate_id', 0);
        $field             = TRS_Helper::post('field', '');
        $value             = TRS_Helper::post('value', '');
        $trs_model         = new TRS_Model($table_rate_id);
        $trs_model->$field = $value;

        TRS_Helper::encode_print_die($trs_model->save());
    }

    public function get_rows()
    {
        $start       = (int)TRS_Helper::get('start', 0);
        $length      = (int)TRS_Helper::get('length', 25);
        $instance_id = (int)TRS_Helper::get('instance_id', 0);
        $columns     = TRS_Helper::get('columns', array());

        $filters = array();
        foreach ($columns as $column) {
            $filters[$column['data']] = $column['search']['value'];
        }

        $table_rates       = TRS_Model::get_rows($instance_id, $start, $length, 'order', 'ASC', $filters);
        $table_rates_count = TRS_Model::get_rows_count($instance_id, $filters);

        TRS_Helper::encode_print_die(
            array(
                'recordsTotal'    => $table_rates_count,
                'recordsFiltered' => $table_rates_count,
                'data'            => $table_rates
            )
        );
    }

    public function insert_rows()
    {
        $data = TRS_Helper::post('data', array());
        $data = json_decode($data, true);

        TRS_Helper::encode_print_die(TRS_Model::insert_rows($data));
    }

    public function update_rows_status()
    {
        $table_rate_id = TRS_Helper::post('table_rate_id', '');
        $active        = TRS_Helper::post('active', 1);

        TRS_Helper::encode_print_die(TRS_Model::update_rows_status($table_rate_id, $active));
    }

    public function update_rows_order()
    {
        $order = (array)TRS_Helper::post('order', array());
        $order = array_map(
            function ($value) {
                return (int)preg_replace('/^trs-tr-/', '', $value);
            },
            $order
        );
        $order = implode(',', $order);

        TRS_Helper::encode_print_die(TRS_Model::update_rows_order($order));
    }

    public function delete_rows()
    {
        $table_rate_id = TRS_Helper::post('table_rate_id', '');

        TRS_Helper::encode_print_die(TRS_Model::delete_rows($table_rate_id));
    }

    public function import_csv()
    {
        $instance_id   = (int)TRS_Helper::post('instance_id', 0);
        $attachment_id = (int)TRS_Helper::post('attachment_id', 0);

        TRS_Helper::encode_print_die(TRS_Model::import_csv($attachment_id, $instance_id));
    }

    public function import_status()
    {
        TRS_Helper::encode_print_die(
            array(
                'zone' => get_option('tablerateshipping_import_zone_count', 0),
                'rule' => get_option('tablerateshipping_import_rule_count', 0)
            )
        );
    }

    public function export_csv()
    {
        $instance_id = (int)TRS_Helper::get('instance_id', 0);

        TRS_Model::export_csv($instance_id);
    }

    public function optimize()
    {
        TRS_Helper::encode_print_die(TRS_Model::optimize());
    }

    public function new_comment_translation(TRS_Model $table_rate)
    {
        if (function_exists('icl_register_string')) {
            $name = 'table_rate' . $table_rate->instance_id . '-' . $table_rate->table_rate_id . '_shipping_method_title';
            icl_register_string('woocommerce', $name, $table_rate->comment);
        }
    }

    public function update_comment_translation(TRS_Model $table_rate)
    {
        if (function_exists('icl_register_string')) {
            $name = 'table_rate' . $table_rate->instance_id . '-' . $table_rate->table_rate_id . '_shipping_method_title';
            icl_register_string('woocommerce', $name, $table_rate->comment);
        }
    }

    public function delete_comment_translations($table_rate_ids, $instance_id = 0)
    {
        if (function_exists('icl_unregister_string')) {
            $table_rate_ids = TRS_Helper::explode(',', $table_rate_ids);
            foreach ($table_rate_ids as $table_rate_id) {
                $name = 'table_rate' . $instance_id . '-' . $table_rate_id . '_shipping_method_title';
                icl_unregister_string('woocommerce', $name);
            }
        }
    }

    public function package_data_price($price)
    {
        global $woocommerce_wpml;

        if (isset($woocommerce_wpml)
            && !empty($woocommerce_wpml)
            && isset($woocommerce_wpml->multi_currency)
            && !empty($woocommerce_wpml->multi_currency)) {
            $price = $woocommerce_wpml->multi_currency->prices->unconvert_price_amount($price);
        }

        return $price;
    }

    public function package_data_class($class)
    {
        global $sitepress, $woocommerce_wpml;

        if (isset($sitepress) && !empty($sitepress) && isset($woocommerce_wpml) && !empty($woocommerce_wpml)) {
            $class = TRS_Helper::explode(',', $class);
            foreach ($class as &$item) {
                $item = apply_filters('translate_object_id', $item, 'product_shipping_class', false, $sitepress->get_default_language());
            }
            $class = implode(',', array_map('intval', $class));
        }

        return $class;
    }

    public function plugin_path()
    {
        return plugin_dir_path(__FILE__);
    }

    public function plugin_basename()
    {
        return plugin_basename(__FILE__);
    }

    public function plugin_dir_url()
    {
        return plugin_dir_url(__FILE__);
    }
}

if (!function_exists("TRS")) {
    function TRS()
    {
        return TRS_Setup::instance();
    }
}

TRS();