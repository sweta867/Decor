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
?>

<div id="trs-container">
    <div class="trs-title"><?= __('Table Rates', 'tablerateshipping') ?><a href="#" class="trs-jump trs-jump-icon" title="<?= __('Table Rates', 'tablerateshipping') ?>"></a></div>
    <div class="trs-actions-jump">
        <div class="trs-actions button-group">
            <button type="button" class="button update-status" data-active="1" data-text="<?= __('Enable', 'tablerateshipping') ?>"></button>
            <button type="button" class="button update-status" data-active="0" data-text="<?= __('Disable', 'tablerateshipping') ?>"></button>
            <button type="button" class="button delete" data-text="<?= __('Delete', 'tablerateshipping') ?>"></button>
            <button type="button" class="button import" data-text="<?= __('CSV Import', 'tablerateshipping') ?>"></button>
            <a target="_blank" class="button export" href="" data-text="<?= __('CSV Export', 'tablerateshipping') ?>"></a>
            <button type="button" class="button optimize" data-text="<?= __('Optimize', 'tablerateshipping') ?>"></button>
            <button type="button" class="button reload" data-text="<?= __('Reload', 'tablerateshipping') ?>"></button>
        </div>
        <a href="#" class="trs-jump trs-jump-icon" title="<?= __('Bulk Actions', 'tablerateshipping') ?>"></a>
    </div>
    <table id="trs-table" class="wp-list-table widefat striped">
        <thead>
        <tr class="columns-info">
            <th class="manage-column column-cb check-column"></th>
            <th colspan="5" scope="col" class="manage-column column-limits">
                <span class="column-name"><?= __('Limits', 'tablerateshipping') ?></span>
                <a href="#" class="trs-jump trs-jump-icon" title="<?= __('Limits', 'tablerateshipping') ?>"></a>
            </th>
            <th colspan="4" scope="col" class="manage-column column-nothing"></th>
        </tr>
        <tr class="columns-filters">
            <td class="manage-column column-cb check-column"></td>
            <td class="manage-column column-class">
                <select multiple/>
            </td>
            <td class="manage-column column-weight">
                <input type="text">
            </td>
            <td class="manage-column column-price">
                <input type="text">
            </td>
            <td class="manage-column column-quantity">
                <input type="text">
            </td>
            <td class="manage-column column-volume">
                <input type="text">
            </td>
            <td class="manage-column column-cost">
                <input type="text">
            </td>
            <td class="manage-column column-comment">
                <input type="text">
            </td>
            <td class="manage-column column-status">
                <select>
                    <option value=""></option>
                    <option value="1"><?= __('Active', 'tablerateshipping') ?></option>
                    <option value="0"><?= __('Inactive', 'tablerateshipping') ?></option>
                </select>
            </td>
            <th class="manage-column column-filters">
                <span class="column-name"><?= __('Filters', 'tablerateshipping') ?></span>
                <a href="#" class="trs-jump trs-jump-icon" title="<?= __('Filters', 'tablerateshipping') ?>"></a>
            </th>
        </tr>
        <tr>
            <th class="manage-column column-cb check-column">
                <label class="screen-reader-text" for="cb-select-all-1"><?= __('Select All', 'tablerateshipping') ?></label>
                <input id="cb-select-all-1" type="checkbox">
            </th>
            <th class="manage-column column-class">
                <span class="column-name"><?= __('Shipping Class', 'tablerateshipping') ?></span>
                <a href="#" class="trs-jump trs-jump-icon" title="<?= __('Shipping Class', 'tablerateshipping') ?>"></a>
            </th>
            <th class="manage-column column-weight">
                <span class="column-name"><?= __('Weight', 'tablerateshipping') ?></span>
                <a href="#" class="trs-jump trs-jump-icon" title="<?= __('Weight', 'tablerateshipping') ?>"></a>
            </th>
            <th class="manage-column column-price">
                <span class="column-name"><?= __('Price', 'tablerateshipping') ?></span>
                <a href="#" class="trs-jump trs-jump-icon" title="<?= __('Price', 'tablerateshipping') ?>"></a>
            </th>
            <th class="manage-column column-quantity">
                <span class="column-name"><?= __('Quantity', 'tablerateshipping') ?></span>
                <a href="#" class="trs-jump trs-jump-icon" title="<?= __('Quantity', 'tablerateshipping') ?>"></a>
            </th>
            <th class="manage-column column-volume">
                <span class="column-name"><?= __('Volume', 'tablerateshipping') ?></span>
                <a href="#" class="trs-jump trs-jump-icon" title="<?= __('Volume', 'tablerateshipping') ?>"></a>
            </th>
            <th class="manage-column column-cost">
                <span class="column-name"><?= __('Cost', 'tablerateshipping') ?></span>
                <a href="#" class="trs-jump trs-jump-icon" title="<?= __('Cost', 'tablerateshipping') ?>"></a>
            </th>
            <th class="manage-column column-comment">
                <span class="column-name"><?= __('Comment', 'tablerateshipping') ?></span>
                <a href="#" class="trs-jump trs-jump-icon" title="<?= __('Comment', 'tablerateshipping') ?>"></a>
            </th>
            <th class="manage-column column-status">
                <span class="column-name"><?= __('Status', 'tablerateshipping') ?></span>
                <a href="#" class="trs-jump trs-jump-icon" title="<?= __('Status', 'tablerateshipping') ?>"></a>
            </th>
            <th class="manage-column column-actions">
                <span class="column-name"><?= __('Actions', 'tablerateshipping') ?></span>
                <a href="#" class="trs-jump trs-jump-icon" title="<?= __('Actions', 'tablerateshipping') ?>"></a>
            </th>
        </tr>
        </thead>
    </table>
    <script type="text/javascript">
        jQuery(function ($) {
            $('#trs-container').tableRateShipping({
                page_view: '<?= ((\TableRateShipping\libraries\TRS_Helper::get('instance_id', -1) === -1) ? 'zone' : 'method') ?>',
                shipping_classes: <?= json_encode(\TableRateShipping\libraries\TRS_Helper::get_shipping_classes()) ?>,
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
    <?php include dirname(__FILE__) . '/help.php' ?>
</div>