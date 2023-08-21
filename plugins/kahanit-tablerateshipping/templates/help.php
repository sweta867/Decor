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

$debug_mode_link = (version_compare(WC()->version, '3.0.0', '>=')) ? 'admin.php?page=wc-settings&tab=shipping&section=options' : 'admin.php?page=wc-status&tab=tools' ?>
<div id="trs-help"><p class="trs-help-title"><?= __('== Help ==', 'tablerateshipping') ?></p>
    <ul>
        <li><strong><?= __('Options', 'tablerateshipping') ?>:</strong>
            <ul>
                <li><strong><?= __('Debug Mode', 'tablerateshipping') ?>:</strong> <?= sprintf(__('While configuring and testing plugin enable shipping debug mode option <a href="%s" target="_blank">here</a> to bypass shipping rate cache.', 'tablerateshipping'), $debug_mode_link) ?></li>
                <li><strong><?= __('Method Title', 'tablerateshipping') ?>:</strong> <?= sprintf(__('It is shown as shipping method name to customers during checkout. If "%s" is enabled then comment in matched rule is shown as method name.', 'tablerateshipping'), '<a href="#" class="trs-jump">' . __('Use Comment as Title', 'tablerateshipping') . '</a>') ?></li>
                <li><strong><?= __('Table Rates', 'tablerateshipping') ?>:</strong>
                    <ul>
                        <li><strong><?= __('New Rule', 'tablerateshipping') ?>:</strong> <?= sprintf(__('Fill the values in last row of table and click "%s" to create new rule. You can add multiple rules at a time, while filling values in a rule, new empty row will be added below it.', 'tablerateshipping'), '<a href="#" class="trs-jump">' . __('Save', 'tablerateshipping') . '</a>') ?></li>
                        <li><strong><?= __('Update Rule', 'tablerateshipping') ?>:</strong> <?= __('When you click table cell it allows editing that value. You can hit escape to exit without saving and hit enter or click anywhere outside edit field to update the value.', 'tablerateshipping') ?></li>
                        <li><strong><?= __('Rule Priority', 'tablerateshipping') ?>:</strong> <?= __('Rules are checked from first to last to check for limits and if package details are in rule limits that rule is used for shipping cost and it will stop looking further.', 'tablerateshipping') ?></li>
                        <li><strong><?= __('Bulk Actions', 'tablerateshipping') ?>:</strong>
                            <ul>
                                <li><strong><?= __('Enable', 'tablerateshipping') ?>:</strong> <?= __('Enable selected rules.', 'tablerateshipping') ?></li>
                                <li><strong><?= __('Disable', 'tablerateshipping') ?>:</strong> <?= __('Disable selected rules.', 'tablerateshipping') ?></li>
                                <li><strong><?= __('Delete', 'tablerateshipping') ?>:</strong> <?= __('Deleted selected rules.', 'tablerateshipping') ?></li>
                                <li><strong><?= __('CSV Import', 'tablerateshipping') ?>:</strong> <?= sprintf(__('Import rules from uploaded CSV file to table. To get sample CSV file format, create few rules in table and click "%s". In shipping class column "class" you can insert comma separated shipping class ids or shipping class slugs. Separator specified in "%s" is used while rendering CSV file.', 'tablerateshipping'), '<a href="#" class="trs-jump">' . __('CSV Export', 'tablerateshipping') . '</a>', '<a href="admin.php?page=wc-settings&tab=shipping&section=options" target="_blank">' . __('CSV Separator', 'tablerateshipping') . '</a>') ?></li>
                                <li><strong><?= __('CSV Export', 'tablerateshipping') ?>:</strong> <?= sprintf(__('Export rules from table to CSV file. Separator specified in "%s" field is used while generating CSV file.', 'tablerateshipping'), '<a href="admin.php?page=wc-settings&tab=shipping&section=options" target="_blank">' . __('CSV Separator', 'tablerateshipping') . '</a>') ?></li>
                                <li><strong><?= __('Optimize', 'tablerateshipping') ?>:</strong> <?= __('Delete orphaned rules from database table.', 'tablerateshipping') ?></li>
                                <li><strong><?= __('Reload', 'tablerateshipping') ?>:</strong> <?= __('Reload table rates table.', 'tablerateshipping') ?></li>
                            </ul>
                        </li>
                        <li><strong><?= __('Limits', 'tablerateshipping') ?>:</strong> <?= __('Package details are checked against all 5 limits. For particular rule to be considered all 5 package details should be in defined limits. If you want to skip any column, just leave it blank.', 'tablerateshipping') ?></li>
                        <li><strong><?= __('Filters', 'tablerateshipping') ?>:</strong>
                            <ul>
                                <li><?= __('When you select shipping classes to filter all the rules that satisfy that filter are shown. E.g. when you select "Class One" all the rules that contains "Class One" and "- Any -" are shown.', 'tablerateshipping') ?></li>
                                <li><?= __('You can filter weight, price, quantity &amp; volume by fixed value and range. All the rules that come under that filter will be shown. E.g. when you type "4" all the rules like "4", "1-5", "1-4" &amp; "4-7" are shown and when you type "3-6" all the rules like "4", "1-3", "6-8", "4-5" &amp; "1-8" are shown.', 'tablerateshipping') ?></li>
                                <li><?= __('When you filter by cost, comment &amp; active all rules that exactly matches that filter are shown.', 'tablerateshipping') ?></li>
                            </ul>
                        </li>
                        <li><strong><?= __('Shipping Class', 'tablerateshipping') ?>:</strong> <?= __('Select shipping classes. You can select multiple classes.', 'tablerateshipping') ?></li>
                        <li><strong><?= __('Weight', 'tablerateshipping') ?>:</strong> <?= sprintf(__('Define weight limit. It can be fixed value or range. You can define range as 10.5-15.5, to meet this range package weight should be equal or in between upper and lower limit. Weight can be actual or volumetric based on selected option in "%s".', 'tablerateshipping'), '<a href="#" class="trs-jump">' . __('Weight Type', 'tablerateshipping') . '</a>') ?></li>
                        <li><strong><?= __('Price', 'tablerateshipping') ?>:</strong> <?= sprintf(__('Define price limit. It can be defined same as weight in fixed value or range. Price can be tax incl. or excl. based on selected option in "%s".', 'tablerateshipping'), '<a href="#" class="trs-jump">' . __('Price Type', 'tablerateshipping') . '</a>') ?></li>
                        <li><strong><?= __('Quantity', 'tablerateshipping') ?>:</strong> <?= __('Define quantity limit. It can be defined same as weight in fixed value or range.', 'tablerateshipping') ?></li>
                        <li><strong><?= __('Volume', 'tablerateshipping') ?>:</strong> <?= __('Define volume limit. It can be defined same as weight in fixed value or range. Product volume is calculated as length x width x height.', 'tablerateshipping') ?></li>
                        <li><strong><?= __('Cost', 'tablerateshipping') ?>:</strong> <?= sprintf(__('Define shipping cost for the limits in the row. It can be a fixed value or "%s".', 'tablerateshipping'), '<a href="#" class="trs-jump">' . __('Cost Formula', 'tablerateshipping') . '</a>') ?></li>
                        <li><strong><?= __('Comment', 'tablerateshipping') ?>:</strong> <?= sprintf(__('Define comment or rule description. If "%s" is enabled it can be used as shipping method title.', 'tablerateshipping'), '<a href="#" class="trs-jump">' . __('Use Comment as Title', 'tablerateshipping') . '</a>') ?></li>
                        <li><strong><?= __('Status', 'tablerateshipping') ?>:</strong> <?= __('Enable or Disable rule. Rule is only checked if it is enabled.', 'tablerateshipping') ?></li>
                        <li><strong><?= __('Actions', 'tablerateshipping') ?>:</strong>
                            <ul>
                                <li><strong><?= __('Delete', 'tablerateshipping') ?>:</strong> <?= __('Delete rule.', 'tablerateshipping') ?></li>
                                <li><strong><?= __('Move', 'tablerateshipping') ?>:</strong> <?= sprintf(__('You can drag drop rules to arrange it based on "%s".', 'tablerateshipping'), '<a href="#" class="trs-jump">' . __('Rule Priority', 'tablerateshipping') . '</a>') ?></li>
                            </ul>
                        </li>
                        <li><strong><?= __('Save', 'tablerateshipping') ?>:</strong> <?= __('Save new rules.', 'tablerateshipping') ?></li>
                    </ul>
                </li>
                <li><strong><?= __('Tax Status', 'tablerateshipping') ?>:</strong> <?= __('Select whether or not to apply tax to the shipping amount.', 'tablerateshipping') ?></li>
                <li><strong><?= __('Weight Type', 'tablerateshipping') ?>:</strong>
                    <ul>
                        <li><strong><?= __('Use actual weight', 'tablerateshipping') ?>:</strong> <?= __('In this type actual weight is used.', 'tablerateshipping') ?></li>
                        <li><strong><?= __('Use volumetric weight', 'tablerateshipping') ?>:</strong> <?= __('In this type volumetric weight is used.', 'tablerateshipping') ?></li>
                        <li><strong><?= __('Use greater among actual and volumetric weights', 'tablerateshipping') ?>:</strong> <?= __('In this type greater among actual and volumetric weights is used.', 'tablerateshipping') ?></li>
                        <li><strong><?= __('Use smaller among actual and volumetric weights', 'tablerateshipping') ?>:</strong> <?= __('In this type smaller among actual and volumetric weights is used.', 'tablerateshipping') ?></li>
                    </ul>
                </li>
                <li><strong><?= __('Volumetric Divisor', 'tablerateshipping') ?>:</strong> <?= __('It is the volume divisor used to calculate volumetric weight.', 'tablerateshipping') ?></li>
                <li><strong><?= __('Price Type', 'tablerateshipping') ?>:</strong>
                    <ul>
                        <li><strong><?= __('Tax incl.', 'tablerateshipping') ?>:</strong> <?= __('In this type tax inclusive price is used.', 'tablerateshipping') ?></li>
                        <li><strong><?= __('Tax excl.', 'tablerateshipping') ?>:</strong> <?= __('In this type tax exclusive price is used.', 'tablerateshipping') ?></li>
                    </ul>
                </li>
                <li><strong><?= __('Calculation Type', 'tablerateshipping') ?>:</strong>
                    <ul>
                        <li><strong><?= __('Per order', 'tablerateshipping') ?>:</strong> <?= __('In this type all cart items are considered as one package. All combined shipping classes and totaled weight, price, quantity and volume are checked for matching rule.', 'tablerateshipping') ?></li>
                        <li><strong><?= __('Per line', 'tablerateshipping') ?>:</strong> <?= __('In this type every line in cart is considered as separate package. Each line is checked for matching rule and found prices are totaled to get final shipping.', 'tablerateshipping') ?></li>
                        <li><strong><?= __('Per class', 'tablerateshipping') ?>:</strong> <?= __('In this type all the line with common shipping class is considered as separate package. Each package is checked for matching rule and found prices are totaled to get final shipping.', 'tablerateshipping') ?></li>
                    </ul>
                </li>
                <li><strong><?= __('Use Comment as Title', 'tablerateshipping') ?>:</strong> <?=
                    sprintf(
                        __('If it is enabled and if "%s" is "%s" then comment of matching rule is shown as shipping method title during checkout and if "%s" is "%s" or "%s" and if all found rules have same comment then comment is used else "%s" is used as shipping method title.', 'tablerateshipping'),
                        '<a href="#" class="trs-jump">' . __('Calculation Type', 'tablerateshipping') . '</a>',
                        '<a href="#" class="trs-jump">' . __('Per order', 'tablerateshipping') . '</a>',
                        '<a href="#" class="trs-jump">' . __('Calculation Type', 'tablerateshipping') . '</a>',
                        '<a href="#" class="trs-jump">' . __('Per line', 'tablerateshipping') . '</a>',
                        '<a href="#" class="trs-jump">' . __('Per class', 'tablerateshipping') . '</a>',
                        '<a href="#" class="trs-jump">' . __('Method Title', 'tablerateshipping') . '</a>'
                    )
                    ?></li>
            </ul>
        </li>
        <li><strong><?= __('Features', 'tablerateshipping') ?>:</strong>
            <ul>
                <li><strong><?= __('Cost Formula', 'tablerateshipping') ?>:</strong> <?= __('In formula you can use below operators, variables and functions. E.g. 2*ceil(twi)+2.5. Using formulas you can implement complex shipping requirements.', 'tablerateshipping') ?>
                    <ul>
                        <li><strong><?= __('Operators', 'tablerateshipping') ?>:</strong> <?= __('+, -, * and /.', 'tablerateshipping') ?></li>
                        <li><strong><?= __('Variables', 'tablerateshipping') ?>:</strong> <?= __('Suffix f, t and i in below variables means upper limit, lower limit and increase in current rule. E.g. if weight range limit is 10-20 and package weight is 13 then tw = 13, twf = 10, twt = 20 and twi = 3.', 'tablerateshipping') ?>
                            <ul>
                                <li><strong>tw:</strong> <?= __('Total weight.', 'tablerateshipping') ?> <strong>twf:</strong> <?= __('Total weight from.', 'tablerateshipping') ?> <strong>twt:</strong> <?= __('Total weight to.', 'tablerateshipping') ?> <strong>twi:</strong> <?= __('Total weight increase.', 'tablerateshipping') ?></li>
                                <li><strong>tp:</strong> <?= __('Total price(tax incl.).', 'tablerateshipping') ?> <strong>tpf:</strong> <?= __('Total price(tax incl.) from.', 'tablerateshipping') ?> <strong>tpt:</strong> <?= __('Total price(tax incl.) to.', 'tablerateshipping') ?> <strong>tpi:</strong> <?= __('Total price(tax incl.) increase.', 'tablerateshipping') ?></li>
                                <li><strong>tq:</strong> <?= __('Total quantity.', 'tablerateshipping') ?> <strong>tqf:</strong> <?= __('Total quantity from.', 'tablerateshipping') ?> <strong>tqt:</strong> <?= __('Total quantity to.', 'tablerateshipping') ?> <strong>tqi:</strong> <?= __('Total quantity increase.', 'tablerateshipping') ?></li>
                                <li><strong>tv:</strong> <?= __('Total volume.', 'tablerateshipping') ?> <strong>tvf:</strong> <?= __('Total volume from.', 'tablerateshipping') ?> <strong>tvt:</strong> <?= __('Total volume to.', 'tablerateshipping') ?> <strong>tvi:</strong> <?= __('Total volume increase.', 'tablerateshipping') ?></li>
                                <li><strong>skip:</strong> <?= __('Skip current rule.', 'tablerateshipping') ?></li>
                                <li><strong>stop:</strong> <?= __('Stop checking further and discard shipping method.', 'tablerateshipping') ?></li>
                            </ul>
                        </li>
                        <li><strong><?= __('Functions', 'tablerateshipping') ?>:</strong>
                            <ul>
                                <li><strong>ceil():</strong> <?= __('Return numbers rounded up to the nearest integer. E.g. ceil(2.4) = 3.', 'tablerateshipping') ?></li>
                                <li><strong>floor():</strong> <?= __('Return numbers rounded down to the nearest integer. E.g. ceil(2.4) = 2.', 'tablerateshipping') ?></li>
                                <li><strong>sqrt():</strong> <?= __('Return square root of numbers. E.g. sqrt(9) = 3.', 'tablerateshipping') ?></li>
                                <li><strong>abs():</strong> <?= __('Return absolute value of numbers. E.g. abs(-4.2) = 4.2.', 'tablerateshipping') ?></li>
                            </ul>
                        </li>
                    </ul>
                </li>
            </ul>
        </li>
        <li><strong><?= __('Use Cases', 'tablerateshipping') ?>:</strong>
            <ul>
                <li><strong><?= __('Shipping by increase in weight, price, quantity or volume', 'tablerateshipping') ?>:</strong>
                    <ul>
                        <li><?= sprintf(__('E.g. if 0-10kg then $15 and if 10-100kg then $2 per kg weight increase:<br>When weight range 0-10 shipping cost is 15 and when weight range is 10-100 shipping cost is 15+ceil(twi), where "%s" is increase in weight and "%s" rounds number up to nearest integer.', 'tablerateshipping'), '<a href="#" class="trs-jump">twi</a>', '<a href="#" class="trs-jump">ceil()</a>') ?></li>
                    </ul>
                </li>
            </ul>
        </li>
    </ul>
    <p><?= sprintf(__('Please submit ticket <a href="%s" target="_blank">here</a> to get further help.', 'tablerateshipping'), 'https://www.kahanit.com/submit-ticket') ?></p>
</div>