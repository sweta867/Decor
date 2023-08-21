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
 * Class TRS_Helper
 */
class TRS_Helper
{
    protected static $shipping_classes = array();

    public static function get($key, $default_value = '')
    {
        return isset($_GET[$key]) ? self::stripslashes($_GET[$key]) : $default_value;
    }

    public static function post($key, $default_value = '')
    {
        return isset($_POST[$key]) ? self::stripslashes($_POST[$key]) : $default_value;
    }

    public static function addslashes($str, $enclosed = false)
    {
        if (is_array($str)) {
            foreach ($str as &$item) {
                $item = self::addslashes($item, $enclosed);
            }

            return $str;
        }

        $search  = array('\\', '\0', '\n', '\r', '\x1a', "'", '"');
        $replace = array('\\\\', '\\0', '\\n', '\\r', '\Z', "\'", '\"');

        if ($enclosed) {
            $str = self::addslashes($str);
        }

        return str_replace($search, $replace, $str);
    }

    public static function stripslashes($str)
    {
        if (is_array($str)) {
            foreach ($str as &$item) {
                $item = self::stripslashes($item);
            }

            return $str;
        }

        $search  = array('\\\\', '\\0', '\\n', '\\r', '\Z', "\'", '\"');
        $replace = array('\\', '\0', '\n', '\r', '\x1a', "'", '"');

        return str_replace($search, $replace, $str);
    }

    public static function explode($delimiter, $string)
    {
        if ($string === '' || $string === null) {
            return array();
        } else {
            return explode($delimiter, $string);
        }
    }

    public static function array_column($array, $column)
    {
        $result = array();
        foreach ($array as $item) {
            if (isset($item[$column])) {
                $result[] = $item[$column];
            }
        }

        return $result;
    }

    public static function array_column_fill($array, $column, $value)
    {
        return array_map(
            function ($element) use ($column, $value) {
                $element[$column] = $value;

                return $element;
            },
            $array
        );
    }

    public static function encode_print_die($response)
    {
        if (isset($response['status'])) {
            if ($response['status'] === true) {
                $response['status'] = 'success';
            } elseif ($response['status'] === false) {
                $response['status'] = 'danger';
            }
        }
        if (isset($response['message'])) {
            $response['message'] = self::stripslashes($response['message']);
        }

        echo json_encode($response);
        wp_die();
    }

    public static function get_range_string($from, $to)
    {
        if ($from === $to) {
            return $from;
        } else {
            return $from . '-' . $to;
        }
    }

    public static function get_range_array($condition)
    {
        if (is_numeric($condition)) {
            $from = $to = $condition;
        } else {
            $condition = self::explode('-', $condition);
            $from      = (isset($condition[0])) ? $condition[0] : 0;
            $to        = (isset($condition[1])) ? $condition[1] : $from;
        }

        return array(
            'from' => $from,
            'to'   => $to
        );
    }

    public static function get_sql_where_by_class($classes)
    {
        $classes = (!is_array($classes)) ? self::explode(',', $classes) : $classes;

        $where = array();
        foreach ($classes as $class) {
            $where[] = 'FIND_IN_SET(' . (int)$class . ', `class`)';
        }
        $where = implode(' AND ', $where);
        $where = '(`class` = ""' . (($where === '') ? '' : ' OR (' . $where . ')') . ')';

        return $where;
    }

    public static function get_sql_where_by_range($field, $range)
    {
        $range = (!is_array($range)) ? self::get_range_array($range) : $range;

        if ($range['from'] === $range['to']) {
            $where = '(' . (float)$range['from'] . ' BETWEEN `' . $field . '_from` AND `' . $field . '_to`' .
                ' OR (`' . $field . '_from` < 0 AND `' . $field . '_to` < 0))';
        } else {
            $where   = array();
            $where[] = '(`' . $field . '_from` BETWEEN ' . (float)$range['from'] . ' AND ' . (float)$range['to'] . ')';
            $where[] = '(`' . $field . '_to` BETWEEN ' . (float)$range['from'] . ' AND ' . (float)$range['to'] . ')';
            $where[] = '(' . (float)$range['from'] . ' BETWEEN `' . $field . '_from` AND `' . $field . '_to`)';
            $where[] = '(' . (float)$range['to'] . ' BETWEEN `' . $field . '_from` AND `' . $field . '_to`)';
            $where   = '(' . implode(' OR ', $where) . ')';
        }

        return $where;
    }

    public static function get_shipping_classes($key = 'term_id', $value = 'name')
    {
        $classes = array();

        foreach (WC()->shipping()->get_shipping_classes() as $class) {
            if (isset($class->$value) && isset($class->$key)) {
                $classes[$class->$key] = $class->$value;
            }
        }

        return $classes;
    }

    public static function get_shipping_classes_slug_from_id($class_ids)
    {
        if (!isset(self::$shipping_classes['term_id-slug'])) {
            self::$shipping_classes['term_id-slug'] = self::get_shipping_classes('term_id', 'slug');
        }

        $class_ids = self::explode(',', $class_ids);
        if (count($class_ids)) {
            foreach ($class_ids as &$class_id) {
                if (is_numeric($class_id)) {
                    if (isset(self::$shipping_classes['term_id-slug'][$class_id])) {
                        $class_id = self::$shipping_classes['term_id-slug'][$class_id];
                    } else {
                        $class_id = 'undefined';
                    }
                }
            }
        }

        return implode(',', $class_ids);
    }

    public static function get_shipping_classes_id_from_slug($class_ids)
    {
        if (!isset(self::$shipping_classes['slug-term_id'])) {
            self::$shipping_classes['slug-term_id'] = self::get_shipping_classes('slug', 'term_id');
        }

        $class_ids = array_map('trim', self::explode(',', $class_ids));
        if (count($class_ids)) {
            foreach ($class_ids as &$class_id) {
                if (!is_numeric($class_id)) {
                    if (isset(self::$shipping_classes['slug-term_id'][$class_id])) {
                        $class_id = self::$shipping_classes['slug-term_id'][$class_id];
                    } else {
                        $class_id = 0;
                    }
                }
            }
        }

        return implode(',', $class_ids);
    }

    public static function get_message_maxlen($message, $maxlen = 300)
    {
        if (strlen($message) > $maxlen) {
            $message = substr($message, 0, $maxlen);
            $message = trim($message, "<>") . '<br> &raquo; ' . __('and more...', 'tablerateshipping');
        }

        return $message;
    }

    public static function delete_zone_table_rate_methods(\WC_Shipping_Zone $zone)
    {
        $shipping_methods = $zone->get_shipping_methods();
        foreach ($shipping_methods as $shipping_method) {
            if ($shipping_method->id === 'table_rate') {
                self::delete_shipping_method($shipping_method->instance_id, $zone->get_id());
            }
        }
    }

    public static function delete_shipping_method($instance_id, $zone_id)
    {
        global $wpdb;

        $delete = $wpdb->delete(
            $wpdb->prefix . 'woocommerce_shipping_zone_methods',
            array('instance_id' => $instance_id)
        );

        if ($delete === false) {
            return array(
                'status'  => false,
                'message' => __('Error deleting shipping method.', 'tablerateshipping')
            );
        } else {
            do_action('woocommerce_shipping_zone_method_deleted', $instance_id, $zone_id);

            return array(
                'status'  => true,
                'message' => __('Shipping method deleted successfully!', 'tablerateshipping')
            );
        }
    }
}