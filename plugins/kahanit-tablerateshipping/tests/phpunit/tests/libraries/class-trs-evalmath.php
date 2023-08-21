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
 * Class TRS_EvalMath_Tests
 */
class TRS_EvalMath_Tests extends \TRS_Tests
{
    public function test_evaluate()
    {
        $trs_evalmath    = new TRS_EvalMath();
        $trs_evalmath->v = array('tw' => 1.4);
        $result          = $trs_evalmath->e('2*ceil(tw)+15');

        $this->assertEquals(19, $result);
    }
}