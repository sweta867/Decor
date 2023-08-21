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

namespace TableRateShipping\libraries;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class TRS_Model
 */
class TRS_Model
{
    protected static $fields = array(
        'table_rate_id',
        'instance_id',
        'class',
        'weight_from',
        'weight_to',
        'price_from',
        'price_to',
        'quantity_from',
        'quantity_to',
        'volume_from',
        'volume_to',
        'cost',
        'comment',
        'active',
        'order'
    );
    protected static $fields_range = array(
        'weight',
        'price',
        'quantity',
        'volume'
    );
    protected static $fields_zone = array(
        'zone',
        'continent',
        'country',
        'state',
        'postcode'
    );
    public $table_rate_id;
    public $instance_id;
    public $class;
    public $weight_from;
    public $weight_to;
    public $price_from;
    public $price_to;
    public $quantity_from;
    public $quantity_to;
    public $volume_from;
    public $volume_to;
    public $cost;
    public $comment;
    public $active;
    public $order;
    public $weight;
    public $price;
    public $quantity;
    public $volume;

    public static function get_wpdb()
    {
        global $wpdb;

        return $wpdb;
    }

    public static function get_table()
    {
        global $wpdb;

        return $wpdb->prefix . 'woocommerce_shipping_table_rate';
    }

    public function __construct($init = 0)
    {
        if (is_numeric($init) && $init !== 0) {
            $row = $this->get_row((int)$init);
        } elseif (is_array($init)) {
            $row = $init;
        } else {
            $row = null;
        }

        $this->set_object_vars($row);
        $this->prepare();
    }

    private function get_row($table_rate_id)
    {
        $sql = 'SELECT *
                FROM `' . self::get_table() . '`
                WHERE `table_rate_id` = ' . (int)$table_rate_id;

        return self::get_wpdb()->get_row($sql, ARRAY_A);
    }

    private function set_object_vars($row = null)
    {
        if ($row !== null) {
            foreach (array_merge(self::$fields, self::$fields_range) as $field) {
                if (isset($row[$field])) {
                    $this->$field = $row[$field];
                } else {
                    $this->$field = null;
                }
            }
        }
    }

    public function prepare()
    {
        // table rate id
        $this->table_rate_id = (int)$this->table_rate_id;
        // instance id
        $this->instance_id = (int)$this->instance_id;
        // condition class
        $this->prepare_class();
        // condition range
        $this->prepare_ranges();
        // cost
        $this->cost = trim($this->cost);
        $this->cost = ($this->cost === '') ? 0 : $this->cost;
        // comment
        $this->comment = trim($this->comment);
        // active
        $this->active = (int)$this->active;
        // order
        $this->order = (int)$this->order;
    }

    private function prepare_class()
    {
        if (!is_array($this->class)) {
            $this->class = TRS_Helper::explode(',', $this->class);
        }
        $this->class = array_unique(array_map('intval', $this->class));
        sort($this->class);
        $this->class = implode(',', $this->class);
    }

    private function prepare_ranges()
    {
        foreach (self::$fields_range as $field_range) {
            $condition = ($this->{$field_range} === '') ? -1 : $this->{$field_range};
            $from      = ($this->{$field_range . '_from'} === '') ? -1 : $this->{$field_range . '_from'};
            $to        = ($this->{$field_range . '_to'} === '') ? -1 : $this->{$field_range . '_to'};
            if ($condition !== null) {
                $condition = TRS_Helper::get_range_array($condition);
                $from      = round((float)$condition['from'], 2);
                $to        = round((float)$condition['to'], 2);
                $condition = TRS_Helper::get_range_string($from, $to);
            } else {
                $from      = ($from === null) ? -1 : round((float)$from, 2);
                $to        = ($to === null) ? -1 : round((float)$to, 2);
                $condition = TRS_Helper::get_range_string($from, $to);
            }
            $this->{$field_range . '_from'} = $from;
            $this->{$field_range . '_to'}   = $to;
            $this->{$field_range}           = $condition;
        }
    }

    public function validate()
    {
        $validations = array(
            'status'  => true,
            'message' => array(),
            'results' => array()
        );

        $this->prepare();

        // instance id
        $validations['results']['instance_id'] = $this->validate_instance_id();
        // condition class
        $validations['results']['class'] = $this->validate_class();
        // condition ranges
        $validations['results']['ranges'] = $this->validate_ranges();
        // cost
        $validations['results']['cost'] = $this->validate_cost();
        // rule exists
        $validations['results']['rule_exists'] = $this->validate_rule_exists();

        return self::prepare_validations($validations);
    }

    private function validate_instance_id()
    {
        $sql   = 'SELECT COUNT(`instance_id`)
                  FROM `' . self::get_wpdb()->prefix . 'woocommerce_shipping_zone_methods`
                  WHERE `instance_id` = ' . $this->instance_id;
        $count = (int)self::get_wpdb()->get_var($sql);

        if ($count > 0) {
            return array(
                'status' => true
            );
        } else {
            return array(
                'status'  => false,
                'message' => __('Invalid "Instance Id".', 'tablerateshipping')
            );
        }
    }

    private function validate_class()
    {
        $count_obj = count(TRS_Helper::explode(',', $this->class));
        $sql       = 'SELECT COUNT(DISTINCT t.`term_id`)
                      FROM `' . self::get_wpdb()->prefix . 'terms` t
                      LEFT JOIN `' . self::get_wpdb()->prefix . 'term_taxonomy` tt ON tt.`term_id` = t.`term_id`
                      WHERE tt.`taxonomy` = "product_shipping_class" AND t.`term_id` IN (' . $this->class . ')';
        $count     = ($count_obj === 0) ? 0 : (int)self::get_wpdb()->get_var($sql);

        if ($count === $count_obj) {
            return array(
                'status' => true
            );
        } else {
            return array(
                'status'  => false,
                'message' => __('Invalid "Shipping Class".', 'tablerateshipping')
            );
        }
    }

    private function validate_ranges()
    {
        $validations = array(
            'status'  => true,
            'message' => array()
        );
        foreach (self::$fields_range as $field_range) {
            if ($this->{$field_range . '_from'} > $this->{$field_range . '_to'}) {
                $validations['status']    = false;
                $validations['message'][] = sprintf(__('Invalid "%s". Range should be from small to big number.', 'tablerateshipping'), __(ucfirst($field_range), 'tablerateshipping'));
            } else {
                $validations['status'] = $validations['status'] && true;
            }
        }
        if (count($validations['message'])) {
            $validations['message'] = implode('<br>', $validations['message']);
        } else {
            unset($validations['message']);
        }

        return $validations;
    }

    private function validate_cost()
    {
        $trs_evalmath    = new TRS_EvalMath();
        $trs_evalmath->v = array(
            'tw'   => 1,
            'tp'   => 1,
            'tq'   => 1,
            'tv'   => 1,
            'twf'  => 1,
            'twt'  => 1,
            'twi'  => 1,
            'tpf'  => 1,
            'tpt'  => 1,
            'tpi'  => 1,
            'tqf'  => 1,
            'tqt'  => 1,
            'tqi'  => 1,
            'tvf'  => 1,
            'tvt'  => 1,
            'tvi'  => 1,
            'skip' => -1,
            'stop' => false
        );
        $trs_evalmath->evaluate($this->cost);

        if ($trs_evalmath->last_error !== null) {
            return array(
                'status'  => false,
                'message' => sprintf(__('Formula Error: %s.', 'tablerateshipping'), $trs_evalmath->last_error)
            );
        }

        return array(
            'status' => true
        );
    }

    private function validate_rule_exists()
    {
        $sql   = 'SELECT COUNT(`table_rate_id`)
                  FROM `' . self::get_table() . '`
                  WHERE ' . (($this->table_rate_id === 0) ? '' : '`table_rate_id` != ' . $this->table_rate_id . ' AND') . '
                      `instance_id` = ' . $this->instance_id . ' AND
                      `class` = \'' . $this->class . '\' AND
                      `weight_from` = ' . $this->weight_from . ' AND
                      `weight_to` = ' . $this->weight_to . ' AND
                      `price_from` = ' . $this->price_from . ' AND
                      `price_to` = ' . $this->price_to . ' AND
                      `quantity_from` = ' . $this->quantity_from . ' AND
                      `quantity_to` = ' . $this->quantity_to . ' AND
                      `volume_from` = ' . $this->volume_from . ' AND
                      `volume_to` = ' . $this->volume_to . ' AND
                      `cost` = \'' . TRS_Helper::addslashes($this->cost) . '\'';
        $count = (int)self::get_wpdb()->get_var($sql);

        if ($count > 0) {
            return array(
                'status'  => false,
                'message' => __('Rule exists.', 'tablerateshipping')
            );
        } else {
            return array(
                'status' => true
            );
        }
    }

    private static function prepare_validations($validations, $show_zone_number = false, $show_rule_number = false)
    {
        foreach ($validations['results'] as $key => $validation_result) {
            $validations['status'] = $validations['status'] && $validation_result['status'];
            if (!$validation_result['status']) {
                if ($show_zone_number) {
                    $validations['message'][] = sprintf(__('== Zone %d ==', 'tablerateshipping'), ($key + 1));
                }
                if ($show_rule_number) {
                    $validations['message'][] = sprintf(__('Rule %d:', 'tablerateshipping'), ($key + 1));
                }
                $validations['message'][] = $validation_result['message'];
            }
        }

        $validations['message']  = implode('<br>', $validations['message']);
        $validations['message']  = TRS_Helper::get_message_maxlen($validations['message']);
        $validations['has_true'] = (in_array(true, TRS_Helper::array_column($validations['results'], 'status'))) ? true : false;

        return $validations;
    }

    public function save()
    {
        $validation = $this->validate();
        if ($validation['status']) {
            $data = get_object_vars($this);
            $data = array_intersect_key($data, array_flip(self::$fields));
            unset($data['table_rate_id']);

            if ($this->table_rate_id === 0) {
                return $this->save_insert($data);
            } else {
                return $this->save_update($data);
            }
        } else {
            return $validation;
        }
    }

    private function save_insert($data)
    {
        $insert = self::get_wpdb()->insert(
            self::get_table(),
            $data
        );

        if ($insert === false) {
            return array(
                'status'  => false,
                'message' => __('Error saving rule.', 'tablerateshipping')
            );
        } else {
            $this->table_rate_id = self::get_wpdb()->insert_id;

            $this->order = $this->table_rate_id;
            $this->save();

            do_action('tablerateshipping_new_table_rate', $this);

            return array_merge(
                array(
                    'status'  => true,
                    'message' => __('Rule saved successfully!', 'tablerateshipping')
                ),
                get_object_vars($this)
            );
        }
    }

    private function save_update($data)
    {
        $update = self::get_wpdb()->update(
            self::get_table(),
            $data,
            array('table_rate_id' => $this->table_rate_id)
        );

        if ($update === false) {
            return array(
                'status'  => false,
                'message' => __('Error saving rule.', 'tablerateshipping')
            );
        } else {
            do_action('tablerateshipping_update_table_rate', $this);

            return array_merge(
                array(
                    'status'  => true,
                    'message' => __('Rule saved successfully!', 'tablerateshipping')
                ),
                get_object_vars($this)
            );
        }
    }

    public function delete()
    {
        $delete = self::delete_rows($this->table_rate_id);

        if ($delete['status']) {
            $this->table_rate_id = 0;
        }

        return $delete;
    }

    public static function get_rows($instance_id = 0, $start = 0, $length = 25, $orderby = 'order', $order = 'ASC', $filters = array())
    {
        $where = 'WHERE ' . self::get_rows_where($instance_id, $filters);
        $order = 'ORDER BY `' . $orderby . '` ' . $order;
        $limit = ($length === -1) ? '' : 'LIMIT ' . (int)$start . ', ' . (int)$length;

        $sql = 'SELECT *
                FROM `' . self::get_table() . '`
                ' . $where . '
                ' . $order . '
                ' . $limit;

        $table_rates = self::get_wpdb()->get_results($sql, ARRAY_A);

        foreach ($table_rates as &$table_rate) {
            $table_rate_model = new TRS_Model($table_rate);
            $table_rate       = get_object_vars($table_rate_model);
        }

        return $table_rates;
    }

    public static function get_rows_count($instance_id = 0, $filters = array())
    {
        $where = 'WHERE ' . self::get_rows_where($instance_id, $filters);

        $sql = 'SELECT COUNT(`table_rate_id`)
             FROM `' . self::get_table() . '`
             ' . $where;

        return (int)self::get_wpdb()->get_var($sql);
    }

    private static function get_rows_where($instance_id, $filters)
    {
        $where = array('`instance_id` = ' . (int)$instance_id);
        foreach ($filters as $field => $value) {
            if ($value !== '') {
                if ($field === 'class') {
                    $where[] = TRS_Helper::get_sql_where_by_class($value);
                } elseif (in_array($field, self::$fields_range)) {
                    $where[] = TRS_Helper::get_sql_where_by_range($field, $value);
                } elseif ($field === 'active') {
                    $where[] = '`' . $field . '` = ' . (int)$value;
                } else {
                    $where[] = '`' . $field . '` = \'' . TRS_Helper::addslashes($value, true) . '\'';
                }
            }
        }

        return implode(' AND ', $where);
    }

    public static function insert_rows($records = array())
    {
        if (!is_array($records) || count($records) === 0) {
            return array(
                'status'  => false,
                'message' => __('No rules found.', 'tablerateshipping')
            );
        }

        $models_saved = array();
        $validations  = array(
            'status'  => true,
            'message' => array(),
            'results' => array()
        );

        update_option('tablerateshipping_import_rule_count', $import_rule_count = 1);
        foreach ($records as $record) {
            $trs_model  = new TRS_Model($record);
            $validation = $trs_model->save();

            $validations['results'][] = $validation;
            if (!$validation['status']) {
                break;
            }

            $models_saved[] = $trs_model->table_rate_id;

            update_option('tablerateshipping_import_rule_count', ++$import_rule_count);
        }

        // delete saved records if all records are not saved
        if (count($records) !== count($models_saved)) {
            self::delete_rows(implode(',', $models_saved));

            return self::prepare_validations($validations, false, true);
        }

        return array(
            'status'  => true,
            'message' => __('Rules saved successfully!', 'tablerateshipping')
        );
    }

    public static function update_rows_status($table_rate_ids, $active)
    {
        $table_rate_ids = implode(',', array_map('intval', TRS_Helper::explode(',', $table_rate_ids)));

        $update = false;
        if ($table_rate_ids !== '') {
            $sql    = 'UPDATE `' . self::get_table() . '`
                       SET `active` = ' . (int)$active . '
                       WHERE `table_rate_id` IN (' . $table_rate_ids . ')';
            $update = self::get_wpdb()->query($sql);
        }

        if ($update === false) {
            return array(
                'status'  => false,
                'message' => __('Error updating rules.', 'tablerateshipping')
            );
        } else {
            return array(
                'status'  => true,
                'message' => __('Rules updated successfully!', 'tablerateshipping')
            );
        }
    }

    public static function update_rows_order($order)
    {
        $order = array_map('intval', explode(',', $order));

        $min_order = (int)self::get_wpdb()->get_var(
            'SELECT MIN(`order`)
             FROM `' . self::get_table() . '`
             WHERE `table_rate_id` IN (' . implode(',', $order) . ')'
        );
        $min_order = (!$min_order) ? 0 : ($min_order - 1);

        $sql   = 'UPDATE `' . self::get_table() . '`
                  SET `order` = (CASE
                      {{cases}}
                  END)
                  WHERE `table_rate_id` IN (' . implode(',', $order) . ');';
        $cases = '';
        foreach ($order as $key => $item) {
            $cases .= 'WHEN `table_rate_id` = ' . $item . ' then ' . (++$min_order) . PHP_EOL;
        }
        $sql = str_replace('{{cases}}', $cases, $sql);
        self::get_wpdb()->query($sql);

        return array(
            'status'  => true,
            'message' => __('Order updated successfully!', 'tablerateshipping')
        );
    }

    public static function delete_rows($table_rate_ids)
    {
        $table_rate_ids = implode(',', array_map('intval', TRS_Helper::explode(',', $table_rate_ids)));
        $instance_id    = self::get_instance_id_by_table_rate_ids($table_rate_ids);

        $delete = false;
        if ($table_rate_ids !== '') {
            $sql    = 'DELETE FROM `' . self::get_table() . '`
                       WHERE `table_rate_id` IN (' . $table_rate_ids . ')';
            $delete = self::get_wpdb()->query($sql);
        }

        if ($delete === false) {
            return array(
                'status'  => false,
                'message' => __('Error deleting rules.', 'tablerateshipping')
            );
        } else {
            do_action('tablerateshipping_delete_table_rates', $table_rate_ids, $instance_id);

            return array(
                'status'  => true,
                'message' => __('Rules deleted successfully!', 'tablerateshipping')
            );
        }
    }

    public static function delete_rows_by_instance_id($instance_id)
    {
        return self::delete_rows(implode(',', self::get_table_rate_ids_by_instance_id($instance_id)));
    }

    public static function import_csv($attachment_id, $instance_id = 0)
    {
        wc_set_time_limit(0);

        $instance_id = (int)$instance_id;
        $validations = array(
            'status'  => true,
            'message' => array(),
            'results' => array()
        );

        $data = self::import_csv_data($attachment_id);
        if (!$data['status']) {
            return $data;
        }

        update_option('tablerateshipping_import_zone_count', $import_zone_count = 1);
        foreach ($data['records'] as $item) {
            $instance_id_save = self::import_csv_data_instance($instance_id, $item);
            if ($instance_id_save !== null) {
                $item['table_rates'] = TRS_Helper::array_column_fill($item['table_rates'], 'instance_id', $instance_id_save);
                $validation          = self::insert_rows($item['table_rates']);
            } else {
                $validation = array(
                    'status'  => false,
                    'message' => __('Zone table rates skipped, no matching zone name found.', 'tablerateshipping')
                );
            }
            $validations['results'][] = $validation;

            update_option('tablerateshipping_import_zone_count', ++$import_zone_count);
        }

        $validations = self::prepare_validations($validations, true, false);
        if (!$validations['status']) {
            if ($validations['has_true']) {
                $validations['status'] = 'warning';
            }
            return $validations;
        }

        return array(
            'status'  => true,
            'message' => __('Rules saved successfully!', 'tablerateshipping')
        );
    }

    private static function import_csv_data($attachment_id)
    {
        $file = get_attached_file($attachment_id);
        if ($file === false) {
            return array(
                'status'  => false,
                'message' => __('File not found.', 'tablerateshipping')
            );
        }

        $csv_sep = get_option('tablerateshipping_csv_separator', ',');
        $records = array(0 => array('table_rates' => array()));
        $count   = 1;

        if (($handle = fopen($file, 'r')) !== false) {
            $data   = fgetcsv($handle, 0, $csv_sep);
            $header = self::import_csv_data_header($data);
            if (!count($header)) {
                return array(
                    'status'  => false,
                    'message' => __('Invalid CSV data.', 'tablerateshipping')
                );
            }

            while (($data = fgetcsv($handle, 0, $csv_sep)) !== false) {
                if (isset($header['zone']) && trim($data[$header['zone']]) !== '') {
                    $records[$count] = self::import_csv_data_zone($data, $header);
                    $count++;
                }
                $records[$count - 1]['table_rates'][] = self::import_csv_data_record($data, $header);
            }
            fclose($handle);
        }

        if (count($records[0]['table_rates']) === 0) {
            unset($records[0]);
        }

        return array(
            'status'  => true,
            'records' => $records
        );
    }

    private static function import_csv_data_header($data)
    {
        $header = array();
        foreach ($data as $key => $value) {
            if (in_array($value, array_merge(self::$fields_zone, self::$fields, self::$fields_range))) {
                $header[$value] = $key;
            }
        }

        return $header;
    }

    private static function import_csv_data_zone($data, $header)
    {
        $record = array('table_rates' => array());
        foreach (self::$fields_zone as $field_zone) {
            if (isset($header[$field_zone]) && isset($data[$header[$field_zone]])) {
                $record[$field_zone] = $data[$header[$field_zone]];
            }
        }

        return $record;
    }

    private static function import_csv_data_record($data, $header)
    {
        $record = array();
        foreach (array_merge(self::$fields, self::$fields_range) as $field) {
            if (isset($header[$field]) && isset($data[$header[$field]])) {
                if ($field === 'class') {
                    $data[$header[$field]] = TRS_Helper::get_shipping_classes_id_from_slug($data[$header[$field]]);
                }
                $record[$field] = $data[$header[$field]];
            }
        }

        return $record;
    }

    private static function import_csv_data_instance($instance_id, $item)
    {
        static $deleted_methods = array();

        if ($instance_id) {
            $zone_name        = self::get_zone_name_by_instance_id($instance_id);
            $instance_id_save = (!isset($item['zone']) || $item['zone'] === $zone_name) ? $instance_id : null;
        } else {
            if (isset($item['zone'])) {
                $item['zone_id'] = self::get_zone_id_by_zone_name($item['zone']);
                $instance_zone   = self::get_zone_by_data($item);
                if (!isset($deleted_methods[$item['zone']])) {
                    TRS_Helper::delete_zone_table_rate_methods($instance_zone);
                    $deleted_methods[$item['zone']] = 1;
                }
                $instance_id_save = $instance_zone->add_shipping_method('table_rate');
            } else {
                $instance_id_save = null;
            }
        }

        return $instance_id_save;
    }

    public static function export_csv($instance_id = 0)
    {
        $instance_id = (int)$instance_id;
        $csv_sep     = get_option('tablerateshipping_csv_separator', ',');

        if ($instance_id) {
            $rules = self::export_csv_data_instance($instance_id);
        } else {
            $rules = self::export_csv_data_zones();
        }

        if (count($rules) > 0) {
            header('Content-type: text/csv');
            header('Content-Type: application/force-download; charset=UTF-8');
            header('Cache-Control: no-store, no-cache');
            header('Content-disposition: attachment; filename="export.csv"');
            $csv = fopen("php://output", 'w');
            fputcsv($csv, array_keys(reset($rules)), $csv_sep);
            foreach ($rules as $row) {
                fputcsv($csv, $row, $csv_sep);
            }
            fclose($csv);
        } else {
            echo __('No records found', 'tablerateshipping');
        }

        wp_die();
    }

    private static function export_csv_data_instance($instance_id)
    {
        $rules = self::get_rows($instance_id, 0, -1);

        $fields = self::get_fields_export(false);
        foreach ($rules as &$rule) {
            $rule = array_intersect_key($rule, array_flip($fields));
            $rule = array_merge(array_flip($fields), $rule);

            foreach (self::$fields_range as $field_range) {
                $rule[$field_range] = ($rule[$field_range] < 0) ? '' : $rule[$field_range];
            }

            $rule['class'] = TRS_Helper::get_shipping_classes_slug_from_id($rule['class']);
        }

        return $rules;
    }

    private static function export_csv_data_zones()
    {
        $rules = array();

        $zones      = \WC_Shipping_Zones::get_zones();
        $zone_empty = array_fill_keys(self::$fields_zone, '');
        foreach ($zones as $zone) {
            $zone_data = self::get_data_by_zone($zone);
            foreach ($zone['shipping_methods'] as $shipping_method) {
                if ($shipping_method->id === 'table_rate') {
                    $method_rules = self::export_csv_data_instance($shipping_method->instance_id);
                    $first        = true;
                    foreach ($method_rules as &$method_rule) {
                        if ($first) {
                            $method_rule = array_merge($zone_data, $method_rule);
                            $first       = false;
                        } else {
                            $method_rule = array_merge($zone_empty, $method_rule);
                        }
                    }
                    $rules = array_merge($rules, $method_rules);
                }
            }
        }

        return $rules;
    }

    public static function optimize()
    {
        $sql  = 'SELECT GROUP_CONCAT(`table_rate_id` SEPARATOR ",") AS `table_rate_id`, `instance_id`
                 FROM `' . self::get_table() . '`
                 WHERE `instance_id` NOT IN (
                     SELECT `instance_id`
                     FROM `' . self::get_wpdb()->prefix . 'woocommerce_shipping_zone_methods`
                 )
                 GROUP BY `instance_id`';
        $rows = self::get_wpdb()->get_results($sql, ARRAY_A);

        $delete = true;
        foreach ($rows as $row) {
            $delete_rows = self::delete_rows($row['table_rate_id']);
            $delete      = $delete && $delete_rows['status'];
        }

        if ($delete !== false) {
            return array(
                'status'  => true,
                'message' => __('Table optimized successfully!', 'tablerateshipping')
            );
        } else {
            return array(
                'status'  => false,
                'message' => __('Error optimizing table, please try again.', 'tablerateshipping')
            );
        }
    }

    public static function get_shipping($instance_id, $package_data = array())
    {
        $sql = 'SELECT *
                FROM `' . self::get_table() . '`
                WHERE `instance_id` = ' . (int)$instance_id . '
                AND ' . TRS_Helper::get_sql_where_by_class($package_data['cl']) . '
                AND ' . TRS_Helper::get_sql_where_by_range('weight', $package_data['tw']) . '
                AND ' . TRS_Helper::get_sql_where_by_range('price', $package_data['tp']) . '
                AND ' . TRS_Helper::get_sql_where_by_range('quantity', $package_data['tq']) . '
                AND ' . TRS_Helper::get_sql_where_by_range('volume', $package_data['tv']) . '
                AND active = 1
                ORDER BY `order` ASC';

        $results = self::get_wpdb()->get_results($sql, ARRAY_A);

        $shipping = false;
        foreach ($results as $result) {
            $package_data_temp = $package_data;
            $package_data_temp = array_merge(
                $package_data_temp,
                array(
                    'twf' => $result['weight_from'],
                    'twt' => $result['weight_to'],
                    'twi' => $package_data_temp['tw'] - $result['weight_from'],
                    'tpf' => $result['price_from'],
                    'tpt' => $result['price_to'],
                    'tpi' => $package_data_temp['tp'] - $result['price_from'],
                    'tqf' => $result['quantity_from'],
                    'tqt' => $result['quantity_to'],
                    'tqi' => $package_data_temp['tq'] - $result['quantity_from'],
                    'tvf' => $result['volume_from'],
                    'tvt' => $result['volume_to'],
                    'tvi' => $package_data_temp['tv'] - $result['volume_from']
                )
            );

            $trs_evalmath    = new TRS_EvalMath();
            $trs_evalmath->v = $package_data_temp;
            $shipping        = $trs_evalmath->evaluate($result['cost']);

            if ($shipping < 0) {
                continue;
            } elseif ($shipping === false) {
                break;
            } else {
                $result['cost'] = $shipping;
                $shipping       = $result;
                break;
            }
        }

        return $shipping;
    }

    public static function get_table_rate_ids_by_instance_id($instance_id)
    {
        $sql  = 'SELECT `table_rate_id`
                 FROM `' . self::get_table() . '`
                 WHERE `instance_id` = ' . (int)$instance_id;
        $rows = self::get_wpdb()->get_results($sql, ARRAY_A);

        return TRS_Helper::array_column($rows, 'table_rate_id');
    }

    public static function get_instance_id_by_table_rate_ids($table_rate_ids)
    {
        $table_rate_ids = implode(',', array_map('intval', TRS_Helper::explode(',', $table_rate_ids)));

        $instance_id = 0;
        if ($table_rate_ids !== '') {
            $sql         = 'SELECT `instance_id`
                            FROM `' . self::get_table() . '`
                            WHERE `table_rate_id` IN (' . $table_rate_ids . ')';
            $instance_id = (int)self::get_wpdb()->get_var($sql);
        }

        return $instance_id;
    }

    public static function get_zone_name_by_instance_id($instance_id)
    {
        if (!$instance_id) {
            return null;
        }

        $sql = 'SELECT sz.*
                FROM `' . self::get_wpdb()->prefix . 'woocommerce_shipping_zones` sz
                LEFT JOIN `' . self::get_wpdb()->prefix . 'woocommerce_shipping_zone_methods` zm
                    ON zm.`zone_id` = sz.`zone_id`
                WHERE zm.`instance_id` = ' . (int)$instance_id;

        $zone = self::get_wpdb()->get_row($sql, ARRAY_A);

        if (is_array($zone) && isset($zone['zone_name'])) {
            return $zone['zone_name'];
        } else {
            return null;
        }
    }

    public static function get_zone_id_by_zone_name($zone_name)
    {
        $sql = 'SELECT `zone_id`
                FROM `' . self::get_wpdb()->prefix . 'woocommerce_shipping_zones`
                WHERE `zone_name` = "' . TRS_Helper::addslashes($zone_name, true) . '"';

        return self::get_wpdb()->get_var($sql);
    }

    public static function get_zone_by_data($data)
    {
        if (!isset($data['zone_id'])) {
            $data['zone_id'] = null;
        }

        $zone = new \WC_Shipping_Zone($data['zone_id']);
        $zone->clear_locations();

        foreach (self::$fields_zone as $location_type) {
            if (isset($data[$location_type])) {
                if ($location_type === 'zone') {
                    $zone->set_zone_name(TRS_Helper::addslashes($data[$location_type]));
                    continue;
                }
                if ($location_type === 'postcode') {
                    $data[$location_type] = strtoupper($data[$location_type]);
                }

                $locations = TRS_Helper::explode(',', preg_replace('/[\r\n,]+/', ',', $data[$location_type]));
                foreach ($locations as $location) {
                    $zone->add_location($location, $location_type);
                }
            }
        }

        $zone->save();

        return $zone;
    }

    public static function get_data_by_zone($zone)
    {
        $data = array(
            'zone'      => $zone['zone_name'],
            'continent' => array(),
            'country'   => array(),
            'state'     => array(),
            'postcode'  => array()
        );

        foreach ($zone['zone_locations'] as $location) {
            $data[$location->type][] = $location->code;
        }

        $data['continent'] = implode(PHP_EOL, $data['continent']);
        $data['country']   = implode(PHP_EOL, $data['country']);
        $data['state']     = implode(PHP_EOL, $data['state']);
        $data['postcode']  = implode(PHP_EOL, $data['postcode']);

        return $data;
    }

    public static function get_fields_export($include_fields_zone = true)
    {
        $fields = self::$fields;

        array_splice($fields, 3, 0, self::$fields_range);
        unset($fields[array_search('table_rate_id', $fields)]);
        unset($fields[array_search('instance_id', $fields)]);
        foreach (self::$fields_range as $field_range) {
            unset($fields[array_search($field_range . '_from', $fields)]);
            unset($fields[array_search($field_range . '_to', $fields)]);
        }
        unset($fields[array_search('order', $fields)]);

        if ($include_fields_zone) {
            array_splice($fields, 0, 0, self::$fields_zone);
        }

        return array_values($fields);
    }
}