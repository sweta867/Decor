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

/**
 * Class TRS_Method_Tests
 */
class TRS_Method_Tests extends \TRS_Tests
{
    public static function setUpBeforeClass()
    {
        self::truncate_tables();
        self::setup_sample_classes();
        self::setup_sample_zones();
    }

    public function test_construct()
    {
        $trs_method = new TRS_Method(1);

        $this->assertEquals(1, $trs_method->instance_id);
    }

    public function test_calculate_shipping()
    {
        $rows = array(
            array('instance_id' => 1, 'class' => '1', 'cost' => 23, 'active' => 1, 'comment' => 'Cheap Rate'),
            array('instance_id' => 1, 'class' => '2', 'cost' => 24, 'active' => 1, 'comment' => 'Sale Rate'),
            array('instance_id' => 1, 'class' => '1,2', 'cost' => 25, 'active' => 1, 'comment' => 'Cheap Rate')
        );
        TRS_Model::insert_rows($rows);

        $trs_method = new TRS_Method_Test(1);
        $trs_method->set_option('comment_title', 'yes');

        // test shipping per order
        $trs_method->set_option('calculation_type', 'order');
        $trs_method->rate = array();
        $trs_method->calculate_shipping($this->get_package());

        $this->assertEquals('Cheap Rate', $trs_method->rate['label']);
        $this->assertEquals(25, $trs_method->rate['cost']);

        // test shipping per line
        $trs_method->set_option('calculation_type', 'line');
        $trs_method->rate = array();
        $trs_method->calculate_shipping($this->get_package());

        $this->assertEquals('Table Rates', $trs_method->rate['label']);
        $this->assertEquals(71, $trs_method->rate['cost']);

        // test shipping per class
        $trs_method->set_option('calculation_type', 'class');
        $trs_method->rate = array();
        $trs_method->calculate_shipping($this->get_package());

        $this->assertEquals('Table Rates', $trs_method->rate['label']);
        $this->assertEquals(47, $trs_method->rate['cost']);

        // test no shipping found order
        TRS_Model::delete_rows('1,2,3');
        $trs_method->set_option('calculation_type', 'order');
        $trs_method->rate = array('will not set');
        $trs_method->calculate_shipping($this->get_package());

        $this->assertEquals(array('will not set'), $trs_method->rate);

        // test no shipping found line
        $trs_method->set_option('calculation_type', 'line');
        $trs_method->rate = array('will not set');
        $trs_method->calculate_shipping($this->get_package());

        $this->assertEquals(array('will not set'), $trs_method->rate);

        // test no shipping found class
        $trs_method->set_option('calculation_type', 'class');
        $trs_method->rate = array('will not set');
        $trs_method->calculate_shipping($this->get_package());

        $this->assertEquals(array('will not set'), $trs_method->rate);
    }

    public function test_get_package_data()
    {
        $trs_method = new TRS_Method_Test(1);
        $package    = $this->get_package();

        $reflection = new \ReflectionClass(get_class($trs_method));
        $method     = $reflection->getMethod('get_package_data');
        $method->setAccessible(true);

        $package_data = $method->invokeArgs($trs_method, array($package));

        // test shipping classes
        $this->assertEquals('1,2', $package_data['cl']);

        // test actual weight
        $this->assertEquals('38', $package_data['tw']);

        // test volumetric weight
        $trs_method->set_option('weight_type', 'volumetric');
        $trs_method->set_option('volume_divisor', '14');
        $package_data = $method->invokeArgs($trs_method, array($package));
        $this->assertEquals('36', $package_data['tw']);

        // test greater weight
        $trs_method->set_option('weight_type', 'greater');
        $package_data = $method->invokeArgs($trs_method, array($package));
        $this->assertEquals('38', $package_data['tw']);

        // test smaller weight
        $trs_method->set_option('weight_type', 'smaller');
        $package_data = $method->invokeArgs($trs_method, array($package));
        $this->assertEquals('36', $package_data['tw']);

        // test price tax incl
        $trs_method->set_option('price_type', 'incl');
        $package_data = $method->invokeArgs($trs_method, array($package));
        $this->assertEquals('102', $package_data['tp']);

        // test price tax excl
        $trs_method->set_option('price_type', 'excl');
        $package_data = $method->invokeArgs($trs_method, array($package));
        $this->assertEquals('81', $package_data['tp']);
    }

    public function test_generate_table_rates_html()
    {
        $trs_method = new TRS_Method_Test(1);

        $trs_container = $trs_method->generate_table_rates_html();
        $this->assertRegExp('/<div id="trs-container">/', $trs_container);
    }

    private function get_package()
    {
        $package = array(
            'contents' => array(
                array(
                    'quantity'   => '1',
                    'line_tax'   => '5',
                    'line_total' => '25',
                    'data'       => new WC_Product_Test(
                        array(
                            'needs_shipping'    => true,
                            'shipping_class_id' => 1,
                            'weight'            => 5,
                            'length'            => 2,
                            'width'             => 3,
                            'height'            => 4,
                        )
                    )
                ),
                array(
                    'quantity'   => '2',
                    'line_tax'   => '7',
                    'line_total' => '27',
                    'data'       => new WC_Product_Test(
                        array(
                            'needs_shipping'    => true,
                            'shipping_class_id' => 2,
                            'weight'            => 6,
                            'length'            => 3,
                            'width'             => 4,
                            'height'            => 5,
                        )
                    )
                ),
                array(
                    'quantity'   => '3',
                    'line_tax'   => '9',
                    'line_total' => '29',
                    'data'       => new WC_Product_Test(
                        array(
                            'needs_shipping'    => true,
                            'shipping_class_id' => 2,
                            'weight'            => 7,
                            'length'            => 4,
                            'width'             => 5,
                            'height'            => 6,
                        )
                    )
                )
            )
        );

        return $package;
    }
}

/**
 * Class WC_Product_Test
 */
class WC_Product_Test
{
    public $needs_shipping;
    public $shipping_class_id;
    public $weight;
    public $length;
    public $width;
    public $height;

    public function __construct($init = array())
    {
        foreach ($init as $key => $value) {
            $this->$key = $value;
        }
    }

    public function __call($name, $arguments)
    {
        $property = preg_replace('/^get_/', '', $name);

        return $this->$property;
    }
}

/**
 * Class TRS_Method_Test
 */
class TRS_Method_Test extends TRS_Method
{
    public $options = array(
        'title'            => 'Table Rates',
        'tax_status'       => 'taxable',
        'calculation_type' => 'order',
        'comment_title'    => 'no',
        'weight_type'      => 'actual',
        'volume_divisor'   => '5000',
        'price_type'       => 'incl'
    );
    public $rate = array();

    public function __construct($instance_id = 0)
    {
        parent::__construct($instance_id);
    }

    public function set_option($key, $value)
    {
        return (isset($this->options[$key])) ? $this->options[$key] = $value : false;
    }

    public function get_option($key, $empty_value = null)
    {
        return (isset($this->options[$key])) ? $this->options[$key] : $empty_value;
    }

    public function add_rate($args = array())
    {
        $this->rate = $args;
    }
}