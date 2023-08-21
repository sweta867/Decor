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
 * Class TRS_Model_Tests
 */
class TRS_Model_Tests extends \TRS_Tests
{
    use PHPMock;

    protected static $row_empty = array(
        "table_rate_id" => 0,
        "instance_id"   => 0,
        "class"         => '',
        "weight_from"   => -1,
        "weight_to"     => -1,
        "price_from"    => -1,
        "price_to"      => -1,
        "quantity_from" => -1,
        "quantity_to"   => -1,
        "volume_from"   => -1,
        "volume_to"     => -1,
        "cost"          => 0,
        "comment"       => '',
        "active"        => 0,
        "order"         => 0,
        "weight"        => -1,
        "price"         => -1,
        "quantity"      => -1,
        "volume"        => -1
    );

    public static function setUpBeforeClass()
    {
        self::truncate_tables();
        self::setup_sample_classes();
        self::setup_sample_zones();
        self::setup_sample_table_rates();
    }

    public function test_construct()
    {
        // id not passed
        $trs_model = new TRS_Model();
        $this->assertEquals(self::$row_empty, get_object_vars($trs_model));

        // id not found
        $trs_model = new TRS_Model(7);
        $this->assertEquals(self::$row_empty, get_object_vars($trs_model));

        // id found
        $trs_model = new TRS_Model(1);
        $row_one   = array(
            'table_rate_id' => 1,
            'instance_id'   => 1,
            'class'         => '1',
            'weight_from'   => 1.0,
            'weight_to'     => 2.0,
            'price_from'    => 0.0,
            'price_to'      => 0.0,
            'quantity_from' => 0.0,
            'quantity_to'   => 0.0,
            'volume_from'   => 0.0,
            'volume_to'     => 0.0,
            'cost'          => '1',
            'comment'       => 'comment one',
            'active'        => 1,
            'order'         => 1,
            'weight'        => '1-2',
            'price'         => 0.0,
            'quantity'      => 0.0,
            'volume'        => 0.0
        );
        $this->assertEquals($row_one, get_object_vars($trs_model));

        // row passed
        $trs_model = new TRS_Model($row_one);
        $this->assertEquals($row_one, get_object_vars($trs_model));
    }

    public function test_prepare()
    {
        $trs_model = new TRS_Model();

        $trs_model->class      = '2,1,2,one,two';
        $trs_model->weight     = '2-3';
        $trs_model->price      = null;
        $trs_model->price_from = 2;
        $trs_model->price_to   = 3;
        $trs_model->cost       = '';

        $trs_model->prepare();

        $row                = self::$row_empty;
        $row['class']       = '0,1,2';
        $row['weight_from'] = 2;
        $row['weight_to']   = 3;
        $row['price_from']  = 2;
        $row['price_to']    = 3;
        $row['weight']      = '2-3';
        $row['price']       = '2-3';
        $row['cost']        = 0;

        $this->assertEquals($row, get_object_vars($trs_model));
    }

    public function test_validate()
    {
        $trs_model = new TRS_Model();

        $trs_model->instance_id = 3;
        $trs_model->class       = 3;
        $trs_model->weight      = '3-2';
        $trs_model->cost        = '(tw';

        $validation = $trs_model->validate();
        $this->assertFalse($validation['results']['instance_id']['status']);
        $this->assertFalse($validation['results']['class']['status']);
        $this->assertFalse($validation['results']['ranges']['status']);
        $this->assertFalse($validation['results']['cost']['status']);

        $trs_model = new TRS_Model(1);
        $row_one   = get_object_vars($trs_model);
        unset($row_one['table_rate_id']);

        $trs_model = new TRS_Model($row_one);

        $validation = $trs_model->validate();
        $this->assertFalse($validation['results']['rule_exists']['status']);
    }

    public function test_save()
    {
        // insert success
        $trs_model              = new TRS_Model();
        $trs_model->instance_id = 2;
        $trs_model->weight      = '2-3';
        $save_success           = $trs_model->save();

        $this->assertTrue($save_success['status']);
        $this->assertEquals(4, TRS_Model::get_rows_count(2));

        // update success
        $trs_model->weight = '3-4';
        $save_success      = $trs_model->save();
        $trs_model         = new TRS_Model($trs_model->table_rate_id);

        $this->assertTrue($save_success['status']);
        $this->assertEquals('3-4', $trs_model->weight);

        // insert failure
        /* @var \wpdb_Test $wpdb */
        global $wpdb;

        $wpdb->add_method_return_false('insert');
        $trs_model_fail              = new TRS_Model();
        $trs_model_fail->instance_id = 2;
        $trs_model_fail->weight      = '4-5';
        $save_failure                = $trs_model_fail->save();

        $this->assertFalse($save_failure['status']);

        // update failure
        $wpdb->add_method_return_false('update');
        $trs_model->weight = '4-5';
        $save_failure      = $trs_model->save();

        $this->assertFalse($save_failure['status']);
    }

    public function test_delete()
    {
        $trs_model = new TRS_Model(7);

        // delete failure
        /* @var \wpdb_Test $wpdb */
        global $wpdb;

        $wpdb->add_method_return_false('query', 2);
        $delete = $trs_model->delete();

        $this->assertFalse($delete['status']);

        // delete success
        $delete = $trs_model->delete();

        $this->assertTrue($delete['status']);
        $this->assertEquals(0, $trs_model->table_rate_id);
        $this->assertEquals(3, TRS_Model::get_rows_count(2));
    }

    public function test_get_rows()
    {
        $filters = array(
            'class'   => '1',
            'weight'  => '2',
            'active'  => '1',
            'comment' => 'comment one'
        );

        $rows = TRS_Model::get_rows(1, 0, 100, 'order', 'ASC', $filters);
        $this->assertEquals(2, count($rows));
    }

    public function test_get_rows_count()
    {
        $filters = array(
            'class'   => '1',
            'weight'  => '2',
            'active'  => '1',
            'comment' => 'comment one'
        );

        $this->assertEquals(2, TRS_Model::get_rows_count(1, $filters));
    }

    public function test_insert_rows()
    {
        $rows = array(
            array('instance_id' => 2, 'weight' => 5),
            array('instance_id' => 2, 'weight' => 5),
            array('instance_id' => 2, 'weight' => 5)
        );

        $validation = TRS_Model::insert_rows($rows);

        $this->assertFalse($validation['status']);
        $this->assertEquals(3, TRS_Model::get_rows_count(2));

        $rows = array(
            array('instance_id' => 2, 'weight' => 6),
            array('instance_id' => 2, 'weight' => 7),
            array('instance_id' => 2, 'weight' => 8)
        );

        $validation = TRS_Model::insert_rows($rows);

        $this->assertTrue($validation['status']);
        $this->assertEquals(6, TRS_Model::get_rows_count(2));

        $validation = TRS_Model::insert_rows(array());

        $this->assertFalse($validation['status']);
    }

    public function test_update_rows_status()
    {
        // update failure
        /* @var \wpdb_Test $wpdb */
        global $wpdb;

        $wpdb->add_method_return_false('query');
        $update = TRS_Model::update_rows_status('1,2', 0);

        $this->assertFalse($update['status']);

        // update success
        $update = TRS_Model::update_rows_status('1,2', 0);
        $rows   = TRS_Model::get_rows(1, 0, 2);

        $this->assertTrue($update['status']);
        $this->assertEquals(0, $rows[0]['active']);
        $this->assertEquals(0, $rows[1]['active']);
    }

    public function test_update_rows_order()
    {
        TRS_Model::update_rows_order('2,1');
        $rows = TRS_Model::get_rows(1, 0, 2, 'table_rate_id');

        $this->assertEquals(2, $rows[0]['order']);
        $this->assertEquals(1, $rows[1]['order']);
    }

    public function test_delete_rows()
    {
        // delete failure
        /* @var \wpdb_Test $wpdb */
        global $wpdb;

        $wpdb->add_method_return_false('query', 2);
        $delete = TRS_Model::delete_rows('9,10,11');

        $this->assertFalse($delete['status']);

        // delete success
        $delete = TRS_Model::delete_rows('9,10,11');

        $this->assertTrue($delete['status']);
        $this->assertEquals(3, TRS_Model::get_rows_count(2));
    }

    public function test_delete_rows_by_instance_id()
    {
        // delete failure
        /* @var \wpdb_Test $wpdb */
        global $wpdb;

        $wpdb->add_method_return_false('query', 3);
        $delete = TRS_Model::delete_rows_by_instance_id(2);

        $this->assertFalse($delete['status']);

        // delete success
        $delete = TRS_Model::delete_rows_by_instance_id(2);

        $this->assertTrue($delete['status']);
        $this->assertEquals(0, TRS_Model::get_rows_count(2));
    }

    public function test_import_csv()
    {
        $path_instance      = TRS()->plugin_path() . 'tests/phpunit/data/import-instance.csv';
        $path_instance_none = TRS()->plugin_path() . 'tests/phpunit/data/import-instance-none.csv';
        $path_zones         = TRS()->plugin_path() . 'tests/phpunit/data/import-zones.csv';
        $path_zones_partial = TRS()->plugin_path() . 'tests/phpunit/data/import-zones-partial.csv';

        $this->getFunctionMock(__NAMESPACE__, 'get_attached_file')
            ->expects($this->exactly(6))
            ->willReturn($path_instance, $path_instance_none, $path_zones, false, $path_instance, $path_zones_partial);

        // import instance csv
        $validation = TRS_Model::import_csv(1, 2);

        $this->assertTrue($validation['status']);

        // import instance csv with error
        $validation = TRS_Model::import_csv(1, 2);

        $this->assertFalse($validation['status']);

        // import zones csv
        $validation = TRS_Model::import_csv(1, 0);

        $this->assertTrue($validation['status']);

        // import invalid file
        $validation = TRS_Model::import_csv(1, 2);

        $this->assertFalse($validation['status']);

        // import instance csv for zones
        $validation = TRS_Model::import_csv(1, 0);

        $this->assertFalse($validation['status']);

        // import zones csv with warning
        $validation = TRS_Model::import_csv(1, 0);

        $this->assertEquals('warning', $validation['status']);
    }

    public function test_import_csv_data()
    {
        $path_instance = TRS()->plugin_path() . 'tests/phpunit/data/import-instance.csv';
        $path_zones    = TRS()->plugin_path() . 'tests/phpunit/data/import-zones.csv';
        $path_empty    = TRS()->plugin_path() . 'tests/phpunit/data/import-empty.csv';

        $trs_model  = new TRS_Model();
        $reflection = new \ReflectionClass(get_class($trs_model));
        $method     = $reflection->getMethod('import_csv_data');
        $method->setAccessible(true);

        $this->getFunctionMock(__NAMESPACE__, 'get_attached_file')
            ->expects($this->exactly(4))
            ->willReturn($path_instance, $path_zones, $path_empty, false);

        // import instance csv
        $table_rates = $method->invokeArgs($trs_model, array(1));

        $this->assertTrue($table_rates['status']);
        $this->assertEquals(3, count($table_rates['records'][0]['table_rates']));

        // import zones csv
        $table_rates = $method->invokeArgs($trs_model, array(1));

        $this->assertTrue($table_rates['status']);
        $this->assertEquals(3, count($table_rates['records'][1]['table_rates']));
        $this->assertEquals(3, count($table_rates['records'][2]['table_rates']));

        // import empty csv
        $table_rates = $method->invokeArgs($trs_model, array(1));

        $this->assertFalse($table_rates['status']);

        // import invalid file
        $table_rates = $method->invokeArgs($trs_model, array(1));

        $this->assertFalse($table_rates['status']);
    }

    public function test_export_csv()
    {
        $this->getFunctionMock(__NAMESPACE__, 'header')
            ->expects($this->any())
            ->willReturn(null);
        $this->getFunctionMock(__NAMESPACE__, 'wp_die')
            ->expects($this->any())
            ->willReturn(null);

        ob_start();
        TRS_Model::export_csv(3);
        TRS_Model::export_csv(0);
        $content  = ob_get_clean();
        $content  = str_replace("\r\n", "\n", trim($content));
        $expected = 'class,weight,price,quantity,volume,cost,comment,active
"sclass-one,sclass-two",1,,,,26,FedEx,1
,2,,,,27,FedEx,1
,3,,,,28,FedEx,1
zone,continent,country,state,postcode,class,weight,price,quantity,volume,cost,comment,active
Asia,AS,IN,IN:GJ,"364290
364291...364295",sclass-one,2-3,0,0,0,2,"comment one",0
,,,,,sclass-one,1-2,0,0,0,1,"comment one",0
,,,,,sclass-two,0,0,0,0,3,"comment three",1
Asia,AS,IN,IN:GJ,"364290
364291...364295",,1,,,,26,FedEx,1
,,,,,,2,,,,27,FedEx,1
,,,,,,3,,,,28,FedEx,1
"Asia One",AS,"IN
JP","IN:GJ
JP:JP01","364290
364291","sclass-one,sclass-two",1,,,,26,FedEx,1
,,,,,,2,,,,27,FedEx,1
,,,,,,3,,,,28,FedEx,1
"Asia One",AS,"IN
JP","IN:GJ
JP:JP01","364290
364291","sclass-one,sclass-two",1,,,,26,FedEx,1
,,,,,,2,,,,27,FedEx,1
,,,,,,3,,,,28,FedEx,1
"Asia Two",AS,IN,IN:GJ,364291,,1,,,,26,FedEx,1
,,,,,,2,,,,27,FedEx,1
,,,,,,3,,,,28,FedEx,1';
        $expected = str_replace("\r\n", "\n", trim($expected));

        $this->assertEquals($expected, $content);

        // export when no records found
        TRS_Model::delete_rows_by_instance_id(5);

        ob_start();
        TRS_Model::export_csv(5);
        $content = ob_get_clean();

        $this->assertEquals(__('No records found', 'tablerateshipping'), $content);
    }

    public function test_optimize()
    {
        TRS_Model::get_wpdb()->query(
            'UPDATE `' . TRS_Model::get_table() . '`
             SET `instance_id` = 5000 WHERE `instance_id` = 1'
        );

        // optimize failure
        /* @var \wpdb_Test $wpdb */
        global $wpdb;

        $wpdb->add_method_return_false('query', 3);
        $optimize = TRS_Model::optimize();

        $this->assertFalse($optimize['status']);

        // optimize success
        $optimize = TRS_Model::optimize();

        $this->assertTrue($optimize['status']);
        $this->assertEquals(0, TRS_Model::get_rows_count(5000));
    }

    public function test_get_shipping()
    {
        $rows = array(
            array(
                'instance_id' => 1,
                'class'       => '1,2',
                'weight'      => '5-7',
                'price'       => '100-120',
                'quantity'    => '0-6',
                'volume'      => '250-300',
                'cost'        => '15',
                'active'      => 1
            ),
            array(
                'instance_id' => 1,
                'class'       => '2',
                'cost'        => '17',
                'active'      => 1
            )
        );
        TRS_Model::delete_rows_by_instance_id(1);
        TRS_Model::insert_rows($rows);
        $package_data = array(
            'cl'   => '1',
            'tw'   => 5,
            'tp'   => 110,
            'tq'   => 3,
            'tv'   => 275,
            'skip' => -1,
            'stop' => false
        );

        // test by all pass
        $shipping = TRS_Model::get_shipping(1, $package_data);
        $this->assertEquals(15, $shipping['cost']);

        // test by instance fail
        $shipping = TRS_Model::get_shipping(5000, $package_data);
        $this->assertFalse($shipping);

        // test by class fail
        $shipping = TRS_Model::get_shipping(1, array_merge($package_data, array('cl' => '0')));
        $this->assertFalse($shipping);

        // test by weight fail
        $shipping = TRS_Model::get_shipping(1, array_merge($package_data, array('tw' => '8')));
        $this->assertFalse($shipping);

        // test by price fail
        $shipping = TRS_Model::get_shipping(1, array_merge($package_data, array('tp' => '99')));
        $this->assertFalse($shipping);

        // test by quantity fail
        $shipping = TRS_Model::get_shipping(1, array_merge($package_data, array('tq' => '7')));
        $this->assertFalse($shipping);

        // test by volume fail
        $shipping = TRS_Model::get_shipping(1, array_merge($package_data, array('tv' => '249')));
        $this->assertFalse($shipping);

        // test rule skipped
        TRS_Model::get_wpdb()->query(
            'UPDATE `' . TRS_Model::get_table() . '`
             SET `cost` = "skip" WHERE `instance_id` = 1 AND `cost` = "15"'
        );
        $shipping = TRS_Model::get_shipping(1, array_merge($package_data, array('cl' => '2')));
        $this->assertEquals(17, $shipping['cost']);

        // test by status fail
        TRS_Model::get_wpdb()->query(
            'UPDATE `' . TRS_Model::get_table() . '`
             SET `cost` = "15", `active` = 0 WHERE `instance_id` = 1 AND `cost` = "skip"'
        );
        $shipping = TRS_Model::get_shipping(1, $package_data);
        $this->assertFalse($shipping);
    }

    public function test_get_table_rate_ids_by_instance_id()
    {
        $this->assertEquals(array(12, 13, 14), TRS_Model::get_table_rate_ids_by_instance_id(2));
    }

    public function test_get_instance_id_by_table_rate_ids()
    {
        $this->assertEquals(2, TRS_Model::get_instance_id_by_table_rate_ids('12,13,14'));
    }

    public function test_get_zone_name_by_instance_id()
    {
        $this->assertEquals('Asia', TRS_Model::get_zone_name_by_instance_id(1));
        $this->assertNull(TRS_Model::get_zone_name_by_instance_id(5000));
        $this->assertNull(TRS_Model::get_zone_name_by_instance_id(null));
    }

    public function test_get_zone_id_by_zone_name()
    {
        $this->assertEquals(1, TRS_Model::get_zone_id_by_zone_name('Asia'));
        $this->assertNull(TRS_Model::get_zone_name_by_instance_id('Africa'));
    }

    public function test_get_zone_by_data_and_get_data_by_zone()
    {
        $zone_data = array(
            'zone'      => 'Asia Two',
            'postcode'  => '364290' . PHP_EOL . '364291',
            'continent' => 'AS',
            'country'   => 'IN' . PHP_EOL . 'JP',
            'state'     => 'GJ' . PHP_EOL . 'JP01'
        );
        $locations = array(
            (object)array('code' => 'AS', 'type' => 'continent'),
            (object)array('code' => 'IN', 'type' => 'country'),
            (object)array('code' => 'JP', 'type' => 'country'),
            (object)array('code' => 'GJ', 'type' => 'state'),
            (object)array('code' => 'JP01', 'type' => 'state'),
            (object)array('code' => '364290', 'type' => 'postcode'),
            (object)array('code' => '364291', 'type' => 'postcode')
        );

        $zone = TRS_Model::get_zone_by_data($zone_data);

        $this->assertTrue(is_int($zone->get_id()) && $zone->get_id() > 0);
        $this->assertEquals('Asia Two', $zone->get_zone_name());
        $this->assertEquals($locations, $zone->get_zone_locations());

        $zone->delete();

        $zone = array(
            'zone_name'      => 'Asia Two',
            'zone_locations' => $locations
        );

        $this->assertEquals($zone_data, TRS_Model::get_data_by_zone($zone));
    }

    public function test_get_fields_export()
    {
        $array_true  = array(
            'zone',
            'continent',
            'country',
            'state',
            'postcode',
            'class',
            'weight',
            'price',
            'quantity',
            'volume',
            'cost',
            'comment',
            'active'
        );
        $array_false = array(
            'class',
            'weight',
            'price',
            'quantity',
            'volume',
            'cost',
            'comment',
            'active'
        );

        $this->assertEquals($array_true, TRS_Model::get_fields_export());
        $this->assertEquals($array_false, TRS_Model::get_fields_export(false));
    }
}