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

use phpmock\phpunit\PHPMock;

/**
 * Class TRS_Helper_Tests
 */
class TRS_Helper_Tests extends \TRS_Tests
{
    use PHPMock;

    public static function setUpBeforeClass()
    {
        self::truncate_tables();
    }

    public function test_get()
    {
        $_GET['key'] = 'va\"lue';
        $this->assertEquals('va"lue', TRS_Helper::get('key'));
        $this->assertEquals('value', TRS_Helper::get('key2', 'value'));
    }

    public function test_post()
    {
        $_POST['key'] = 'va\"lue';
        $this->assertEquals('va"lue', TRS_Helper::post('key'));
        $this->assertEquals('value2', TRS_Helper::post('key2', 'value2'));
    }

    public function test_addslashes()
    {
        $this->assertEquals('a\"b', TRS_Helper::addslashes('a"b'));
        $this->assertEquals('a\\\\\"b', TRS_Helper::addslashes('a"b', true));
        $this->assertEquals(array('a\"b', 'c\"d'), TRS_Helper::addslashes(array('a"b', 'c"d')));
    }

    public function test_stripslashes()
    {
        $this->assertEquals('va"lue', TRS_Helper::stripslashes('va\"lue'));
        $this->assertEquals(array('a"b', 'c"d'), TRS_Helper::stripslashes(array('a\"b', 'c\"d')));
    }

    public function test_explode()
    {
        $this->assertEquals(array(), TRS_Helper::explode(',', ''));
        $this->assertEquals(array(), TRS_Helper::explode(',', null));
        $this->assertEquals(array('a', 'b'), TRS_Helper::explode(',', 'a,b'));
    }

    public function test_array_column()
    {
        $array = array(
            array('number' => 1, 'text' => 'one'),
            array('number' => 2, 'text' => 'two'),
            array('number' => 3, 'text' => 'three'),
            array('number' => 4, 'text' => 'four'),
            array('number' => 5, 'text' => 'five')
        );

        $this->assertEquals(array(1, 2, 3, 4, 5), TRS_Helper::array_column($array, 'number'));
        $this->assertEquals(array(), TRS_Helper::array_column($array, 'none'));
    }

    public function test_array_column_fill()
    {
        $array = array(
            array('number' => 1, 'text' => 'one'),
            array('number' => 2, 'text' => 'two'),
            array('number' => 3, 'text' => 'three'),
            array('number' => 4, 'text' => 'four'),
            array('number' => 5, 'text' => 'five')
        );

        $array_expected = array(
            array('number' => 1, 'text' => 'one', 'data' => 'something'),
            array('number' => 2, 'text' => 'two', 'data' => 'something'),
            array('number' => 3, 'text' => 'three', 'data' => 'something'),
            array('number' => 4, 'text' => 'four', 'data' => 'something'),
            array('number' => 5, 'text' => 'five', 'data' => 'something')
        );

        $this->assertEquals($array_expected, TRS_Helper::array_column_fill($array, 'data', 'something'));
    }

    public function test_encode_print_die()
    {
        $this->getFunctionMock(__NAMESPACE__, 'wp_die')
            ->expects($this->exactly(4))
            ->willReturn('');

        $response_success = array('status' => true, 'message' => 'Saved successfully!');
        $response_danger  = array('status' => false, 'message' => 'Save failed.');
        $response_warning = array('status' => 'warning', 'message' => 'Saved with warning.');
        $response_strip   = array('status' => true, 'message' => "Test me\'ssage.");

        TRS_Helper::encode_print_die($response_success);
        TRS_Helper::encode_print_die($response_danger);
        TRS_Helper::encode_print_die($response_warning);
        TRS_Helper::encode_print_die($response_strip);

        $this->expectOutputString(
            '{"status":"success","message":"Saved successfully!"}' .
            '{"status":"danger","message":"Save failed."}' .
            '{"status":"warning","message":"Saved with warning."}' .
            '{"status":"success","message":"' . "Test me'ssage." . '"}'
        );
    }

    public function test_get_range_string()
    {
        $this->assertEquals('2-3', TRS_Helper::get_range_string(2, 3));
        $this->assertEquals('2', TRS_Helper::get_range_string(2, 2));
    }

    public function test_get_range_array()
    {
        $this->assertEquals(array('from' => 2, 'to' => 3), TRS_Helper::get_range_array('2-3'));
        $this->assertEquals(array('from' => 2, 'to' => 2), TRS_Helper::get_range_array('2'));
    }

    public function test_get_sql_where_by_class()
    {
        // get sql where by class when ids array passed
        $where    = TRS_Helper::get_sql_where_by_class(array(1, 2));
        $expected = '(`class` = "" OR (FIND_IN_SET(1, `class`) AND FIND_IN_SET(2, `class`)))';
        $this->assertEquals($expected, $where);

        // get sql where by class when comma separated ids passed
        $where = TRS_Helper::get_sql_where_by_class('1,2');
        $this->assertEquals($expected, $where);

        // get sql where by class when no classes passed
        $where    = TRS_Helper::get_sql_where_by_class('');
        $expected = '(`class` = "")';
        $this->assertEquals($expected, $where);
    }

    public function test_get_sql_where_by_range()
    {
        // get sql where when range is numeric
        $where    = TRS_Helper::get_sql_where_by_range('weight', 5);
        $expected = '(5 BETWEEN `weight_from` AND `weight_to` OR (`weight_from` < 0 AND `weight_to` < 0))';
        $this->assertEquals($expected, $where);

        // get sql where when rnage in not numeric
        $where    = TRS_Helper::get_sql_where_by_range('weight', '2-3');
        $expected = '((`weight_from` BETWEEN 2 AND 3) OR (`weight_to` BETWEEN 2 AND 3)' .
            ' OR (2 BETWEEN `weight_from` AND `weight_to`) OR (3 BETWEEN `weight_from` AND `weight_to`))';
        $this->assertEquals($expected, $where);
    }

    public function test_get_shipping_classes()
    {
        $this->getFunctionMock(__NAMESPACE__, 'WC')
            ->expects($this->exactly(4))
            ->willReturn(new WooCommerce_Test());

        $classes_term_id_name = array('1' => 'SClass One', '2' => 'SClass Two', '3' => 'SClass Three');
        $classes_term_id_slug = array('1' => 'sclass-one', '2' => 'sclass-two', '3' => 'sclass-three');

        $this->assertEquals($classes_term_id_name, TRS_Helper::get_shipping_classes('term_id', 'name'));
        $this->assertEquals($classes_term_id_slug, TRS_Helper::get_shipping_classes('term_id', 'slug'));
        $this->assertEquals(array_flip($classes_term_id_slug), TRS_Helper::get_shipping_classes('slug', 'term_id'));
        $this->assertEquals(array(), TRS_Helper::get_shipping_classes('term_id', 'none'));
    }

    public function test_get_shipping_classes_slug_from_id()
    {
        $this->getFunctionMock(__NAMESPACE__, 'WC')
            ->expects($this->once())
            ->willReturn(new WooCommerce_Test());

        $this->assertEquals('sclass-one,sclass-two,undefined,test', TRS_Helper::get_shipping_classes_slug_from_id('1,2,4,test'));
    }

    public function test_get_shipping_classes_id_from_slug()
    {
        $this->getFunctionMock(__NAMESPACE__, 'WC')
            ->expects($this->once())
            ->willReturn(new WooCommerce_Test());

        $this->assertEquals('1,2,0,4', TRS_Helper::get_shipping_classes_id_from_slug('sclass-one,sclass-two,test,4'));
    }

    public function test_get_message_maxlen()
    {
        $message = 'test testing';

        $this->assertEquals('test<br> &raquo; ' . __('and more...', 'tablerateshipping'), TRS_Helper::get_message_maxlen($message, 4));
        $this->assertEquals('test testing', TRS_Helper::get_message_maxlen($message, 12));
        $this->assertEquals('test testing', TRS_Helper::get_message_maxlen($message, 13));
    }

    public function test_delete_zone_table_rate_methods()
    {
        $zone = new \WC_Shipping_Zone();
        $zone->save();
        $zone->set_zone_name('Delete Zone Table Rate Methods');
        $zone->add_shipping_method('table_rate');
        $zone->add_shipping_method('table_rate');
        $zone->add_shipping_method('table_rate');
        $zone->add_shipping_method('flat_rate');
        $zone->add_shipping_method('flat_rate');

        // shipping methods delete failure
        /* @var \wpdb_Test $wpdb */
        global $wpdb;

        $wpdb->add_method_return_false('delete');
        TRS_Helper::delete_zone_table_rate_methods($zone);

        $this->assertEquals(3, count($zone->get_shipping_methods()));

        // shipping methods delete success
        TRS_Helper::delete_zone_table_rate_methods($zone);

        $this->assertEquals(2, count($zone->get_shipping_methods()));

        $zone->delete();
    }

    public function test_delete_shipping_method()
    {
        $zone = new \WC_Shipping_Zone();
        $zone->save();
        $zone->set_zone_name('Delete Zone Table Rate Methods');
        $instance_id = $zone->add_shipping_method('table_rate');

        // shipping methods delete failure
        /* @var \wpdb_Test $wpdb */
        global $wpdb;

        $wpdb->add_method_return_false('delete');
        $delete = TRS_Helper::delete_shipping_method($instance_id, $zone->get_id());

        $this->assertFalse($delete['status']);

        // shipping methods delete success
        $delete = TRS_Helper::delete_shipping_method($instance_id, $zone->get_id());

        $this->assertTrue($delete['status']);

        $zone->delete();
    }
}

/**
 * Class WooCommerce_Test
 */
class WooCommerce_Test
{
    public function shipping()
    {
        return new WC_Shipping_Test();
    }
}

/**
 * Class WC_Shipping_Test
 */
class WC_Shipping_Test
{
    public function get_shipping_classes()
    {
        return array(
            (object)array('term_id' => 1, 'name' => 'SClass One', 'slug' => 'sclass-one'),
            (object)array('term_id' => 2, 'name' => 'SClass Two', 'slug' => 'sclass-two'),
            (object)array('term_id' => 3, 'name' => 'SClass Three', 'slug' => 'sclass-three')
        );
    }
}