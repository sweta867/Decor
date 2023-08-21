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

if (!class_exists('WC_Shipping_Method')) {
    return;
}

/**
 * Class TRS_Method
 */
class TRS_Method extends \WC_Shipping_Method
{
    public function __construct($instance_id = 0)
    {
        parent::__construct($instance_id);

        $this->id                   = 'table_rate';
        $this->method_title         = __('Table Rates', 'tablerateshipping');
        $this->method_description   = __('Lets you charge shipping cost based on shipping class, weight, price, quanity and volume.', 'tablerateshipping');
        $this->supports             = array(
            'shipping-zones',
            'instance-settings',
            'instance-settings-modal'
        );
        $this->instance_form_fields = array(
            'title'            => array(
                'title'   => __('Method Title', 'tablerateshipping') . '<a href="#" class="trs-jump trs-jump-icon" title="' . __('Method Title', 'tablerateshipping') . '"></a>',
                'type'    => 'text',
                'default' => __('Table Rate', 'tablerateshipping')
            ),
            'table_rates'      => array(
                'title'       => __('Table Rates', 'tablerateshipping'),
                'type'        => 'table_rates',
                'instance_id' => $this->instance_id
            ),
            'tax_status'       => array(
                'title'   => __('Tax Status', 'tablerateshipping') . '<a href="#" class="trs-jump trs-jump-icon" title="' . __('Tax Status', 'tablerateshipping') . '"></a>',
                'type'    => 'select',
                'default' => 'taxable',
                'class'   => 'wc-enhanced-select',
                'options' => array(
                    'taxable' => __('Taxable', 'tablerateshipping'),
                    'none'    => __('None', 'tablerateshipping')
                )
            ),
            'weight_type'      => array(
                'title'   => __('Weight Type', 'tablerateshipping') . '<a href="#" class="trs-jump trs-jump-icon" title="' . __('Weight Type', 'tablerateshipping') . '"></a>',
                'type'    => 'select',
                'default' => 'actual',
                'class'   => 'wc-enhanced-select',
                'options' => array(
                    'actual'     => __('Use actual weight', 'tablerateshipping'),
                    'volumetric' => __('Use volumetric weight', 'tablerateshipping'),
                    'greater'    => __('Use greater among actual and volumetric weights', 'tablerateshipping'),
                    'smaller'    => __('Use smaller among actual and volumetric weights', 'tablerateshipping')
                )
            ),
            'volume_divisor'   => array(
                'title'   => __('Volumetric Divisor', 'tablerateshipping') . '<a href="#" class="trs-jump trs-jump-icon" title="' . __('Volumetric Divisor', 'tablerateshipping') . '"></a>',
                'type'    => 'text',
                'default' => '5000'
            ),
            'price_type'       => array(
                'title'   => __('Price Type', 'tablerateshipping') . '<a href="#" class="trs-jump trs-jump-icon" title="' . __('Price Type', 'tablerateshipping') . '"></a>',
                'type'    => 'select',
                'default' => 'incl',
                'class'   => 'wc-enhanced-select',
                'options' => array(
                    'incl' => __('Tax incl.', 'tablerateshipping'),
                    'excl' => __('Tax excl.', 'tablerateshipping')
                )
            ),
            'calculation_type' => array(
                'title'   => __('Calculation Type', 'tablerateshipping') . '<a href="#" class="trs-jump trs-jump-icon" title="' . __('Calculation Type', 'tablerateshipping') . '"></a>',
                'type'    => 'select',
                'class'   => 'wc-enhanced-select',
                'default' => 'order',
                'options' => array(
                    'order' => __('Per order', 'tablerateshipping'),
                    'line'  => __('Per line', 'tablerateshipping'),
                    'class' => __('Per class', 'tablerateshipping')
                )
            ),
            'comment_title'    => array(
                'title'   => __('Use Comment as Title', 'tablerateshipping') . '<a href="#" class="trs-jump trs-jump-icon" title="' . __('Use Comment as Title', 'tablerateshipping') . '"></a>',
                'type'    => 'checkbox',
                'default' => 'no',
                'label'   => ' '
            )
        );
        $this->title                = $this->get_option('title');
        $this->tax_status           = $this->get_option('tax_status');

        add_action('woocommerce_update_options_shipping_' . $this->id, array($this, 'process_admin_options'));
    }

    public function calculate_shipping($package = array())
    {
        switch ($this->get_option('calculation_type')) {
            case 'order':
                $this->calculate_shipping_per_order($package);
                break;
            case 'line':
                $this->calculate_shipping_per_line($package);
                break;
            case 'class':
                $this->calculate_shipping_per_class($package);
                break;
        }
    }

    private function calculate_shipping_per_order($package = array())
    {
        $comment  = array();
        $shipping = TRS_Model::get_shipping($this->instance_id, $this->get_package_data($package));
        if (isset($shipping['cost'])) {
            $cost                                = $shipping['cost'];
            $comment[$shipping['table_rate_id']] = $shipping['comment'];
        } else {
            $cost = false;
        }

        $this->add_table_rate($package, $cost, $comment);
    }

    private function calculate_shipping_per_line($package = array())
    {
        $comment = array();
        $cost    = false;
        foreach ($package['contents'] as $line) {
            $shipping = TRS_Model::get_shipping($this->instance_id, $this->get_package_data(array('contents' => array($line))));
            if (isset($shipping['cost'])) {
                $cost                                = (float)$cost + $shipping['cost'];
                $comment[$shipping['table_rate_id']] = $shipping['comment'];
            } else {
                $cost = false;
                break;
            }
        }
        $this->add_table_rate($package, $cost, $comment);
    }

    private function calculate_shipping_per_class($package = array())
    {
        $classes = array();
        foreach ($package['contents'] as $line) {
            /** @var \WC_Product $product */
            $product = $line['data'];

            $classes[$product->get_shipping_class_id()][] = $line;
        }
        $comment = array();
        $cost    = false;
        foreach ($classes as $class) {
            $shipping = TRS_Model::get_shipping($this->instance_id, $this->get_package_data(array('contents' => $class)));
            if (isset($shipping['cost'])) {
                $cost                                = (float)$cost + $shipping['cost'];
                $comment[$shipping['table_rate_id']] = $shipping['comment'];
            } else {
                $cost = false;
                break;
            }
        }
        $this->add_table_rate($package, $cost, $comment);
    }

    private function add_table_rate($package, $cost, $comment)
    {
        if ($cost !== false) {
            $rate = array(
                'id'      => $this->get_rate_id(),
                'label'   => $this->title,
                'cost'    => $cost,
                'package' => $package
            );

            $comment       = array_unique($comment);
            $label         = (count($comment) === 1) ? reset($comment) : '';
            $comment_title = strtolower($this->get_option('comment_title')) === 'yes' && $label !== '';
            $rate['id']    = ($comment_title) ? $rate['id'] . '-' . key($comment) : $rate['id'];
            $rate['label'] = ($comment_title) ? $label : $rate['label'];

            $this->add_rate($rate);
        }
    }

    private function get_package_data($package = array())
    {
        $package_data = array(
            'cl'   => array(),
            'tw'   => 0,
            'tp'   => 0,
            'tq'   => 0,
            'tv'   => 0,
            'skip' => -1,
            'stop' => false
        );

        $weight_type    = $this->get_option('weight_type');
        $volume_divisor = (float)$this->get_option('volume_divisor');
        $price_type     = $this->get_option('price_type');

        foreach ($package['contents'] as $item) {
            /* @var \WC_Product $product */
            $product = $item['data'];

            if ($product->needs_shipping()) {
                // class data
                $package_data['cl'][] = $product->get_shipping_class_id();

                // weight data
                $weight             = (float)$product->get_weight();
                $package_data['tw'] += $weight * $item['quantity'];

                // price data
                $add_tax            = ($price_type === 'incl') ? $item['line_tax'] : 0;
                $package_data['tp'] += $item['line_total'] + $add_tax;

                // quantity data
                $package_data['tq'] += $item['quantity'];

                // volume data
                $length             = (float)$product->get_length();
                $width              = (float)$product->get_width();
                $height             = (float)$product->get_height();
                $volume             = $length * $width * $height;
                $package_data['tv'] += $volume * $item['quantity'];
            }
        }

        $volumetric_weight = ($volume_divisor > 0) ? $package_data['tv'] / $volume_divisor : $package_data['tv'];
        switch ($weight_type) {
            case 'volumetric':
                $package_data['tw'] = $volumetric_weight;
                break;
            case 'greater':
                $package_data['tw'] = max($package_data['tw'], $volumetric_weight);
                break;
            case 'smaller':
                $package_data['tw'] = min($package_data['tw'], $volumetric_weight);
                break;
        }

        $package_data['cl'] = array_unique(array_map('intval', $package_data['cl']));
        $package_data['cl'] = implode(',', $package_data['cl']);
        $package_data['cl'] = apply_filters('tablerateshipping_package_data_class', $package_data['cl']);

        $package_data['tp'] = apply_filters('tablerateshipping_package_data_price', $package_data['tp']);

        return $package_data;
    }

    public function generate_table_rates_html()
    {
        ob_start();
        echo '</table>';
        include dirname(__FILE__) . '/../templates/field_table_rates.php';
        echo '<table class="form-table">';

        return ob_get_clean();
    }
}