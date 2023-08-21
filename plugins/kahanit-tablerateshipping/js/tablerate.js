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

(function ($) {
    var settings,
        defaults = {
            page_view: 'method',
            shipping_classes: [],
            instance_id: 0,
            language: {
                'Showing {0} to {1} of {2} items': 'Showing {0} to {1} of {2} items',
                'Items per page': 'Items per page',
                '- Any -': '- Any -',
                'Save': 'Save',
                'Choose CSV': 'Choose CSV',
                'Are you sure?': 'Are you sure?',
                'No rules selected.': 'No rules selected.',
                'Imported &raquo; zone: {0} &raquo; rule: {1}.': 'Imported &raquo; zone: {0} &raquo; rule: {1}.',
                'Reload page to see the result.': 'Reload page to see the result.'
            }
        },
        $cntnr = '',
        $table = '',
        $actions = '',
        $alert = '',
        alertTemplate = '<div class="alert">\
            <a href="javascript:void(0)" class="alert-close dashicons dashicons-no-alt"></a>\
            <div class="alert-message"></div>\
        </div>',
        addNewTemplate = '<tr class="trs-tr-addnew">\
            <td class="column-cb"></td>\
            <td class="column-class"><select multiple/></td>\
            <td class="column-weight"><input type="text"></td>\
            <td class="column-price"><input type="text"></td>\
            <td class="column-quantity"><input type="text"></td>\
            <td class="column-volume"><input type="text"></td>\
            <td class="column-cost"><input type="text"></td>\
            <td class="column-comment"><input type="text"></td>\
            <td class="column-status"><input type="checkbox" checked></td>\
            <td class="column-actions"></td>\
        </tr>',
        ajaxRequestQue = 0,
        submitButton = '',
        submitForm = false;

    $.fn.tableRateShipping = function (options) {
        settings = $.extend({}, defaults, options);
        $cntnr = this;

        if (settings.page_view === 'landing') {
            $cntnr.find('.import').on('click', onClickCSVImport);
        } else {
            $table = this.find('table');
            $cntnr.closest('form').attr('id', 'trs-form');
            setInstanceId();
            setupDataTable();
            setupDataTableFilters();
            setupSortable();
            setupActions();
            setupRowSelected();
            setupHelp();
            setupSubmitButton();
        }

        setupAlert();
    };

    var setInstanceId = function () {
        if (typeof getUrlParameter('instance_id') === 'undefined') {
            settings.instance_id = $cntnr.closest('form').find('input[name="instance_id"]').val();
        } else {
            settings.instance_id = getUrlParameter('instance_id');
        }
    };

    var setupDataTable = function () {
        $table.DataTable({
            "ajax": {
                "url": "admin-ajax.php?action=trs_get_rows",
                "data": function (data) {
                    data.instance_id = settings.instance_id;
                }
            },
            "dom": '<"trs-table-tools"pil><"trs-table-table"rt>',
            "language": {
                "info": format(settings.language['Showing {0} to {1} of {2} items'], '_START_', '_END_', '_TOTAL_'),
                "infoEmpty": format(settings.language['Showing {0} to {1} of {2} items'], '0', '0', '0'),
                "lengthMenu": "<span>" + settings.language['Items per page'] + "</span> _MENU_"
            },
            "processing": true,
            "serverSide": true,
            "pageLength": 25,
            "pagingType": "numbers",
            "aLengthMenu": [
                [5, 10, 25, 50, 75, 100],
                [5, 10, 25, 50, 75, 100]
            ],
            "ordering": false,
            "columns": [
                {"data": "table_rate_id", "class": "trs-column column-cb check-column"},   // 0
                {"data": "class", "class": "trs-column column-class"},                     // 1
                {"data": "weight", "class": "trs-column column-weight"},                   // 2
                {"data": "price", "class": "trs-column column-price"},                     // 3
                {"data": "quantity", "class": "trs-column column-quantity"},               // 4
                {"data": "volume", "class": "trs-column column-volume"},                   // 5
                {"data": "cost", "class": "trs-column column-cost"},                       // 6
                {"data": "comment", "class": "trs-column column-comment"},                 // 7
                {"data": "active", "class": "trs-column column-status"},                   // 8
                {"data": "table_rate_id", "class": "trs-column column-actions"}            // 9
            ],
            "columnDefs": [{
                render: function (data, type, row) {
                    return '<th scope="row" class="check-column">\
                        <label class="screen-reader-text" for="cb-select-' + data + '"></label>\
                        <input id="cb-select-' + data + '" type="checkbox" name="post[]" value="1">\
                    </th>';
                },
                targets: 0
            }, {
                render: function (data, type, row) {
                    return getEditableHtml('multiselect', 'class', data);
                },
                targets: 1
            }, {
                render: function (data, type, row) {
                    return getEditableHtml('text', 'weight', data);
                },
                targets: 2
            }, {
                render: function (data, type, row) {
                    return getEditableHtml('text', 'price', data);
                },
                targets: 3
            }, {
                render: function (data, type, row) {
                    return getEditableHtml('text', 'quantity', data);
                },
                targets: 4
            }, {
                render: function (data, type, row) {
                    return getEditableHtml('text', 'volume', data);
                },
                targets: 5
            }, {
                render: function (data, type, row) {
                    return getEditableHtml('text', 'cost', data);
                },
                targets: 6
            }, {
                render: function (data, type, row) {
                    return getEditableHtml('text', 'comment', data);
                },
                targets: 7
            }, {
                render: function (data, type, row) {
                    return getEditableHtml('toggle', 'active', data);
                },
                targets: 8
            }, {
                render: function (data, type, row) {
                    return '<span class="delete dashicons dashicons-trash"></span>' +
                        '<span class="move dashicons dashicons-move"></span>';
                },
                targets: 9
            }],
            "rowCallback": function (row, data, index) {
                $(row).attr('id', 'trs-tr-' + data.table_rate_id);
                $(row).attr('data-id', data.table_rate_id);
            },
            "drawCallback": function (settings) {
                $table.find('thead .column-cb :checkbox').prop('checked', false);
                $table.find('.editable').on('init', onInitEditable);
                $table.find('.editable').on('click', onClickEditable);
                $table.find('.editable').trigger('init');
                $table.find('.delete').on('click', onClickDelete);
                setupAddNewRow();
                if (submitForm) {
                    submitForm = false;
                    submitButton.click();
                }
            }
        });
    };

    var setupDataTableFilters = function () {
        $table.dataTable().api().columns().every(function () {
            var $this = this,
                $parent = $table.find('.columns-filters td').eq(this.index());

            if ($parent.hasClass('column-class')) {
                var $select = $parent.find('select');

                addShippingClassOptions($select);
                $select.append('<option value="-1">' + settings.language['- Any -'] + '</option>');
                $select.select2();
            }

            $('input, select', $parent).on('keyup change', function () {
                var value = $(this).val();

                value = ($(this).is('select') && $(this).prop('multiple'))
                    ? (value || []).sort().join(',') : value;
                if ($this.search() !== value) {
                    $this.search(value).draw();
                }
            });

            $('input', $parent).on('keypress', function (e) {
                if (e.keyCode === 13) {
                    e.preventDefault();
                }
            });
        });
    };

    var setupHelp = function () {
        // move help to footer
        $cntnr.closest('form').append($cntnr.find('#trs-help'));
        $cntnr.closest('form').find('.trs-jump').click(function (e) {
            e.preventDefault();
            var text = $(this).attr('title') || $(this).text(),
                elem = $(this).closest('form').find('#trs-help strong').filter(function () {
                    return $(this).text() === text + ':';
                });
            if (elem.length) {
                var scrollContainer,
                    scrollTop;
                if (settings.page_view === 'method') {
                    scrollContainer = $('body, html');
                    scrollTop = elem.offset().top - $('#wpadminbar').outerHeight() - 30;
                } else {
                    var modalHeader = $('.wc-backbone-modal-header');
                    scrollContainer = $('.wc-modal-shipping-method-settings');
                    scrollTop = scrollContainer.scrollTop() + elem.offset().top - modalHeader.offset().top - modalHeader.outerHeight() - 30;
                }
                var animate_count = 0;
                scrollContainer.animate({
                    scrollTop: scrollTop
                }, 500, function () {
                    if (!animate_count) {
                        elem.closest('li').effect("highlight", {color: "#fffbc6"}, 500);
                        animate_count++;
                    }
                });
            }
        });
    };

    var setupRowSelected = function () {
        $('body')
            .on('click', 'thead .column-cb :checkbox', function () {
                if ($(this).prop('checked')) {
                    $table.find('tbody tr').addClass('trs-tr-selected');
                } else {
                    $table.find('tbody tr').removeClass('trs-tr-selected');
                }
            })
            .on('click', 'tbody .column-cb :checkbox', function () {
                $.each($table.find('tbody .column-cb :checkbox'), function () {
                    if ($(this).prop('checked')) {
                        $(this).closest('tr').addClass('trs-tr-selected');
                    } else {
                        $(this).closest('tr').removeClass('trs-tr-selected');
                    }
                });
            });
    };

    var setupAlert = function () {
        $alert = $(alertTemplate);
        $cntnr.append($alert);
        $alert.find('.alert-close').on('click blur', function () {
            $alert.stop().fadeOut('slow');
        });
    };

    var setupActions = function () {
        $actions = $('.trs-actions-jump');
        $cntnr.find('.trs-table-tools').append($actions);
        $actions.find('.update-status').on('click', onClickUpdateStatusSelected);
        $actions.find('.delete').on('click', onClickDeleteSelected);
        $actions.find('.import').on('click', onClickCSVImport);
        $actions.find('.export').attr('href', 'admin-ajax.php?action=trs_export_csv' +
            '&instance_id=' + settings.instance_id);
        $actions.find('.optimize').on('click', function () {
            ajaxRequest({
                'action': 'trs_optimize'
            }, $(this));
        });
        $actions.find('.reload').on('click', function () {
            $table.dataTable().api().page($table.api().page.info().page).draw(false);
        });
    };

    var setupSortable = function () {
        $table.find('tbody').sortable({
            items: 'tr:not(".trs-tr-addnew")',
            axis: 'y',
            delay: 150,
            handle: '.move',
            update: onUpdateOrder
        });
    };

    var setupSubmitButton = function () {
        submitButton = (settings.page_view === 'method')
            ? $table.closest('form').find('.submit .woocommerce-save-button')
            : $table.closest('.wc-backbone-modal-shipping-method-settings').find('footer #btn-ok');
        submitButton.click(confirmSubmit);
    };

    var getEditableHtml = function (type, field, data) {
        var $html = $('<div><span class="editable"/></div>');

        $html.find('.editable').attr('data-type', type);
        $html.find('.editable').attr('data-field', field);
        $html.find('.editable').html(data).checkEmpty();

        return $html.html();
    };

    var onInitEditable = function () {
        var $this = $(this);
        switch ($this.attr('data-type')) {
            case 'multiselect':
                setEditableMultiSelect($this);
                break;
            case 'text':
                $this.attr('data-value', $this.html());
                break;
            case 'toggle':
                setEditableToggle($this);
                break;
        }
    };

    var onClickEditable = function () {
        var $this = $(this);
        switch ($this.attr('data-type')) {
            case 'multiselect':
                onClickEditableMultiSelect($this);
                break;
            case 'text':
                onClickEditableText($this);
                break;
            case 'toggle':
                onClickEditableToggle($this);
                break;
        }
    };

    var onClickEditableMultiSelect = function (field) {
        var input = $('<select multiple/>'),
            inputSelect2,
            inputSelect2Data,
            container,
            selection;

        // setup select options
        var json = split(',', field.attr('data-value'));
        addShippingClassOptions(input);

        input.val(json);
        field.html(input).checkEmpty();
        inputSelect2 = input.select2();
        inputSelect2
            .on("select2-open select2:open", function () {
                field.data("open", true);
            })
            .on("select2-close select2:close", function () {
                field.data("open", false);
            })
            .on('select2:update', function () {
                $(this).select2('destroy');
                var valueBefore = field.attr('data-value'),
                    value = ($(this).val() || []).sort().join(','),
                    changed = !(value === valueBefore);
                if (changed) {
                    field.html('');

                    ajaxRequest({
                        'action': 'trs_update',
                        'table_rate_id': field.closest('tr').attr('data-id'),
                        'field': field.attr('data-field'),
                        'value': value
                    }, field).success(function (response) {
                        if (response.status === 'success') {
                            field.html(response[field.attr('data-field')]);
                        } else {
                            field.html(field.attr('data-value'));
                        }
                        setEditableMultiSelect(field);
                        field.checkEmpty();
                    });
                } else {
                    field.html(field.attr('data-value'));
                    setEditableMultiSelect(field);
                }
                field.checkEmpty();
            })
            .select2('open');

        inputSelect2Data = inputSelect2.data('select2');
        container = (typeof inputSelect2Data.$container !== 'undefined') ? inputSelect2Data.$container : inputSelect2Data.container;
        selection = (typeof inputSelect2Data.$selection !== 'undefined') ? inputSelect2Data.$selection : inputSelect2Data.selection;
        container.on('click', function (e) {
            e.stopPropagation();
        });
        selection.on('focusout', function (e) {
            window.setTimeout(function () {
                var isActive = container.hasClass('select2-container--focus') || container.hasClass('select2-container-active');
                if (!isActive && !field.data("open")) {
                    field.data("open", true);
                    inputSelect2.trigger('select2:update');
                }
            }, 5);
        });
    };

    var onClickEditableText = function (field) {
        var input = $('<input type="text">');

        input.val(field.attr('data-value'));
        field.html(input).checkEmpty();
        input
            .on('click', function (e) {
                e.stopPropagation();
            })
            .on('keypress', function (e) {
                if (e.keyCode === 13) {
                    e.preventDefault();
                    $(this).trigger('blur');
                }
            })
            .on('keyup', function (e) {
                if (e.keyCode === 27) {
                    e.preventDefault();
                    $(this).val(field.attr('data-value'));
                    $(this).trigger('blur');
                }
            })
            .on('blur', function () {
                var changed = !($(this).val() === field.attr('data-value'));
                if (changed) {
                    field.html('');

                    ajaxRequest({
                        'action': 'trs_update',
                        'table_rate_id': field.closest('tr').attr('data-id'),
                        'field': field.attr('data-field'),
                        'value': $(this).val()
                    }, field).success(function (response) {
                        if (response.status === 'success') {
                            field.html(response[field.attr('data-field')]);
                        } else {
                            field.html(field.attr('data-value'));
                        }
                        field.attr('data-value', field.html());
                        field.checkEmpty();
                    });
                } else {
                    field.html(field.attr('data-value'));
                }
                field.checkEmpty();
            })
            .focus();
    };

    var onClickEditableToggle = function (field) {
        var value = parseInt(field.attr('data-value')) || 0;

        ajaxRequest({
            'action': 'trs_update',
            'table_rate_id': field.closest('tr').attr('data-id'),
            'field': field.attr('data-field'),
            'value': +!value
        }, field).success(function (response) {
            if (response.status === 'success') {
                field.html(response[field.attr('data-field')]);
            } else {
                field.html(field.attr('data-value'));
            }
            setEditableToggle(field);
        });
    };

    var setEditableMultiSelect = function (field) {
        var value = split(',', field.html());
        field.html('');
        field.attr('data-value', value.join(','));
        $.each(value, function (index, value) {
            if (typeof settings.shipping_classes[value] !== 'undefined') {
                field.append($('<span class="editable-option" data-id="' + value + '">' + settings.shipping_classes[value] + '</span>'));
            }
        });
    };

    var setEditableToggle = function (field) {
        var value = parseInt(field.html()) || 0;
        field.html('');
        field.attr('data-value', value);
        field.addClass('dashicons');
        field.removeClass('dashicons-yes');
        field.removeClass('dashicons-no-alt');
        if (value) {
            field.addClass('dashicons-yes');
        } else {
            field.addClass('dashicons-no-alt');
        }
    };

    var onClickDelete = function () {
        if (confirm(settings.language['Are you sure?'])) {
            var $this = $(this);

            ajaxRequest({
                'action': 'trs_delete_rows',
                'table_rate_id': $this.closest('tr').attr('data-id')
            }, $this).success(function (response) {
                if (response.status === 'success') {
                    $table.dataTable().api().page($table.api().page.info().page).draw(false);
                }
            });
        }
    };

    var onUpdateOrder = function (e, ui) {
        ajaxRequest({
            'action': 'trs_update_rows_order',
            'order': $(this).sortable('toArray')
        }, ui.item.find('.move')).success(function (response) {
            if (response.status === 'success') {
                ui.item.effect("highlight", {color: "#fffbc6"}, 500);
            } else {
                $table.dataTable().api().page($table.api().page.info().page).draw(false);
            }
        });
    };

    var setupAddNewRow = function () {
        var addNewRowsCount = $table.find('tbody .trs-tr-addnew').length;

        if (addNewRowsCount && $(this).closest('.trs-tr-addnew').next('.trs-tr-addnew').length) {
            return;
        }

        var $addNewRow = $(addNewTemplate),
            $class = $addNewRow.find('.column-class select');

        addShippingClassOptions($class);
        $class.select2();
        if (addNewRowsCount === 0) {
            $addNewRow.find('.column-actions').html('<span class="addnew button-primary" data-text="' + settings.language['Save'].replace('"', '&quot;') + '"></span>');
        } else {
            $addNewRow.find('.column-actions').html('<span class="remove dashicons dashicons-trash"></span>');
        }

        $addNewRow.find('input').change(setupAddNewRow).keypress(function (e) {
            if (e.keyCode === 13) {
                e.preventDefault();
                $table.find('.addnew').trigger('click');
            }
        });
        $addNewRow.find('select').change(setupAddNewRow);
        $addNewRow.find('.remove').click(function () {
            $(this).closest('tr').remove();
        });
        $addNewRow.find('.addnew').click(onClickAddNew);

        $table.find('tbody').append($addNewRow);
    };

    var onClickAddNew = function () {
        var saveData = prepareAddNew();

        if (saveData.length > 0) {
            ajaxRequest({
                'action': 'trs_insert_rows',
                'data': JSON.stringify(saveData)
            }, $(this)).success(function (response) {
                if (response.status === 'success') {
                    $table.dataTable().api().page('last').draw(false);
                }
            });
        }
    };

    var onClickUpdateStatusSelected = function () {
        var table_rate_id = getSelectedRules();
        if (table_rate_id.length > 0) {
            ajaxRequest({
                'action': 'trs_update_rows_status',
                'table_rate_id': table_rate_id.join(','),
                'active': $(this).attr('data-active')
            }, $(this)).success(function (response) {
                if (response.status === 'success') {
                    $table.dataTable().api().page($table.api().page.info().page).draw(false);
                }
            });
        } else {
            alert(settings.language['No rules selected.']);
        }
    };

    var onClickDeleteSelected = function () {
        if (confirm(settings.language['Are you sure?'])) {
            var table_rate_id = getSelectedRules();
            if (table_rate_id.length > 0) {
                ajaxRequest({
                    'action': 'trs_delete_rows',
                    'table_rate_id': table_rate_id.join(',')
                }, $(this)).success(function (response) {
                    if (response.status === 'success') {
                        $table.dataTable().api().page($table.api().page.info().page).draw(false);
                    }
                });
            } else {
                alert(settings.language['No rules selected.']);
            }
        }
    };

    var onClickCSVImport = function () {
        var $this = $(this);
        if (wp.media.frames.csv_frame) {
            wp.media.frames.csv_frame.open();
        } else {
            wp.media.frames.csv_frame = wp.media({
                title: settings.language['Choose CSV'],
                multiple: false,
                library: {
                    type: 'text/csv'
                },
                button: {
                    text: settings.language['Choose CSV']
                }
            });
            wp.media.frames.csv_frame.on('select', function () {
                var data = {
                    'action': 'trs_import_csv',
                    'attachment_id': wp.media.frames.csv_frame.state().get('selection').first().id
                };

                if ($table !== '') {
                    data['instance_id'] = settings.instance_id;
                }

                var interval,
                    sendreq = true,
                    $status = $('<span class="trs-import-status"></span>');

                $this.prop('disabled', true);
                $this.closest('.button-group').after($status);
                interval = setInterval(function () {
                    if (sendreq) {
                        sendreq = false;
                        ajaxRequest({'action': 'trs_import_status'}, $this)
                            .success(function (response) {
                                if (typeof response.zone !== 'undefined' && typeof response.rule !== 'undefined') {
                                    $status.html(format(settings.language['Imported &raquo; zone: {0} &raquo; rule: {1}.'], response.zone, response.rule));
                                }
                                sendreq = true;
                            });
                    }
                }, 3000);

                ajaxRequest(data, $this)
                    .complete(function () {
                        $this.prop('disabled', false);
                        $status.remove();
                        clearInterval(interval);
                    })
                    .success(function (response) {
                        if (response.status === 'success' || response.status === 'warning') {
                            if ($table !== '') {
                                $table.dataTable().api().page('first').draw(false);
                            } else {
                                $alert.find('.alert-message')
                                    .append('<div class="alert-reload">' + settings.language['Reload page to see the result.'] + '</div>');
                            }
                        }
                    });
            });
            wp.media.frames.csv_frame.open();
        }
    };

    var prepareAddNew = function () {
        var addNewRows = $table.find('tbody .trs-tr-addnew'),
            saveData = [],
            saveDataTemp = [];

        addNewRows.each(function () {
            saveDataTemp = {
                class: ($(this).find('.column-class select').val() || []).join(','),
                weight: $(this).find('.column-weight input').val(),
                price: $(this).find('.column-price input').val(),
                quantity: $(this).find('.column-quantity input').val(),
                volume: $(this).find('.column-volume input').val(),
                cost: $(this).find('.column-cost input').val(),
                comment: $(this).find('.column-comment input').val(),
                active: $(this).find('.column-status input').is(':checked')
            };

            var validData = false;
            $.each(saveDataTemp, function (index, value) {
                value = (value === null) ? [] : value;

                if (($.isArray(value) && value.length > 0)
                    || (!$.isArray(value) && typeof value !== 'boolean' && $.trim(value) !== '')) {
                    validData = true
                }
            });

            if (validData) {
                saveDataTemp.instance_id = settings.instance_id;
                saveData.push(saveDataTemp);
            }
        });

        return saveData;
    };

    var getSelectedRules = function () {
        var table_rate_id = [];

        $.each($table.find('tbody .column-cb :checkbox:checked'), function () {
            table_rate_id.push($(this).closest('tr').attr('data-id'));
        });

        return table_rate_id;
    };

    var addShippingClassOptions = function ($select) {
        $.each(settings.shipping_classes, function (index, value) {
            $select.append('<option value="' + index + '">' + value + '</option>');
        });
    };

    var split = function (delimiter, string) {
        if (string === '') {
            return [];
        } else {
            return string.split(delimiter);
        }
    };

    var ajaxRequest = function (data, button) {
        var action = data.action;
        delete data.action;

        return $.ajax({
            url: 'admin-ajax.php?action=' + action,
            type: 'POST',
            dataType: 'json',
            data: data,
            beforeSend: function () {
                if (submitButton !== '' && ++ajaxRequestQue === 1) {
                    submitButton.prop('disabled', true);
                }
                if (addAjaxRequest(button) === 1) {
                    button.addClass('dashicons-backup-trs');
                }
            },
            complete: function () {
                if (submitButton !== '' && --ajaxRequestQue === 0) {
                    submitButton.prop('disabled', false);
                }
                if (removeAjaxRequest(button) === 0) {
                    button.removeClass('dashicons-backup-trs');
                }
            },
            success: function (response) {
                if (typeof response.status !== 'undefined' && response.status !== '' &&
                    typeof response.message !== 'undefined' && response.message !== '') {
                    $alert.removeClass('alert-success');
                    $alert.removeClass('alert-danger');
                    $alert.removeClass('alert-warning');
                    $alert.find('.alert-message').html(response.message);
                    $alert.addClass('alert-' + response.status).stop().fadeIn('slow');
                    $alert.find('.alert-close').focus();
                }
            }
        });
    };

    var addAjaxRequest = function (button) {
        if (typeof button.attr('data-que') === 'undefined') {
            button.attr('data-que', 1);
        } else {
            button.attr('data-que', parseInt(button.attr('data-que')) + 1);
        }

        return parseInt(button.attr('data-que'));
    };

    var removeAjaxRequest = function (button) {
        button.attr('data-que', parseInt(button.attr('data-que')) - 1);

        return parseInt(button.attr('data-que'));
    };

    var confirmSubmit = function () {
        var saveData = prepareAddNew();

        if (saveData.length > 0) {
            $table.find('tbody .trs-tr-addnew .addnew').trigger('click');
            submitForm = true;
            return false;
        }
    };

    var getUrlParameter = function getUrlParameter(sParam) {
        var sPageURL = decodeURIComponent(window.location.search.substring(1)),
            sURLVariables = sPageURL.split('&'),
            sParameterName,
            i;

        for (i = 0; i < sURLVariables.length; i++) {
            sParameterName = sURLVariables[i].split('=');

            if (sParameterName[0] === sParam) {
                return (typeof sParameterName[1] === 'undefined') ? true : sParameterName[1];
            }
        }
    };

    var format = function (format) {
        var args = Array.prototype.slice.call(arguments, 1);
        return format.replace(/{(\d+)}/g, function (match, number) {
            return typeof args[number] !== 'undefined' ? args[number] : match;
        });
    };

    $.fn.checkEmpty = function () {
        var type = this.attr('data-type'),
            field = this.attr('data-field'),
            data = this.html();

        if (field !== 'cost' && field !== 'comment'
            && (data === '' || data === '-1')) {
            this.attr('data-value', '');
            this.html('');
            this.addClass('empty').attr('data-text', settings.language['- Any -']);
        } else {
            this.removeClass('empty').removeAttr('data-text');
        }
    };
})(jQuery);