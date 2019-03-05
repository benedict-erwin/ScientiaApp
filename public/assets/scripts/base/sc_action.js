/**
 * Ajax Save | Update
 * @param {string} saveUpdate
 * @param {string} apiUrl
 * @param {number} pKey
 * @param {string} form
 * @callback callback
 */
function saveOrUpdate(saveUpdate, apiUrl, pKey, form, callback) {
    let urLink = '';
    let formData = $(form).serializeArray();
    $('.btn_save').button('loading');
    $('.btn_save').prop('disabled', true);
    $(document.body).css({ 'cursor': 'wait' });

    /* Check button text */
    if (saveUpdate == 'save') {
        urLink = apiUrl + '_create';
    } else if (saveUpdate == 'update') {
        urLink = apiUrl + '_update';
        formData.push({ name: 'pKey', value: pKey });
    } else {
        urLink = apiUrl;
    }

    if ($(form).parsley().validate({ force: true, group: 'role' })) {
        $.ajax({
            "type": 'POST',
            "headers": { JWT: get_token(API_TOKEN) },
            "url": urLink,
            "data": formData,
            "dataType": 'json',
            "success": function (result, textStatus, jqXHR) {
                set_token(API_TOKEN, jqXHR.getResponseHeader('JWT'));
                if (result.success === true) {
                    $(".btReload").click();
                    notification(result.message, 'success');
                } else {
                    notification(result.error, 'warn', 3, result.message);
                }
                $(window).scrollTop(0);
                $('.formEditorModal').modal('hide');
                $('.btn_save').button('reset');
                $(document.body).css({ 'cursor': 'default' });
                $('.btn_save').prop('disabled', false);

                /* Execute callback if exist */
                typeof callback === 'function' && callback();
            },
            "error": function (jqXHR, textStatus, errorThrown) {
                $('.formEditorModal').modal('hide');
                $('.btn_save').button('reset');
                $('.btn_save').prop('disabled', false);
                $(document.body).css({ 'cursor': 'default' });
                notification(errorThrown, 'error');
                redirectLogin(jqXHR);
                console.log(jqXHR);
                console.log(textStatus);
                console.log(errorThrown);
            }
        });
    } else {
        $('.btn_save').button('reset');
        $(document.body).css({ 'cursor': 'default' });
        $('.btn_save').prop('disabled', false);
    }
}

/**
 * Ajax Delete Single Data
 * @param {string} apiUrl
 * @param {object} data
 * @callback callback
 */
function deleteSingle(apiUrl, data, callback) {
    let msg = 'Apaka Anda yakin?<br/>Klik <font color="#4098D4"><strong>OK</strong></font> untuk mengkonfirmasi penghapusan.';
    let post_data = { 'pKey': data[0] };
    alertify.confirm('PERINGATAN!', msg, function () {
        /* Ok */
        $.ajax({
            "type": 'POST',
            "url": apiUrl + '_delete',
            "headers": { JWT: get_token(API_TOKEN) },
            "data": post_data,
            "dataType": 'json',
            "success": function (result, textStatus, jqXHR) {
                set_token(API_TOKEN, jqXHR.getResponseHeader('JWT'));
                if (result.success === true) {
                    $(".btReload").click();
                    notification(result.message, 'success');
                } else {
                    notification(result.error, 'warn', 3, result.message);
                }
                $(window).scrollTop(0);

                /* Execute callback if exist */
                typeof callback === 'function' && callback();
            },
            "error": function (jqXHR, textStatus, errorThrown) {
                notification(errorThrown, 'error');
                redirectLogin(jqXHR);
                console.log(jqXHR);
                console.log(textStatus);
                console.log(errorThrown);
            }
        });
    }, function () {
        /* Cancel */
    }).set('defaultFocus', 'cancel');
}

/**
 * Ajax Delete Multiple
 * @param {string} apiUrl
 * @param {object} table
 * @param {object} rows_selected
 * @callback callback
 */
function deleteMultiple(apiUrl, table, rows_selected, callback) {
    let rows = [];
    $.each(rows_selected, function (index, rowId) {
        rows.push(rowId);
    });

    if (rows.length > 0) {
        let msg = 'Apaka Anda yakin?<br/>Klik <font color="#4098D4"><strong>OK</strong></font> untuk mengkonfirmasi penghapusan.';
        let post_data = { 'pKey': rows };
        alertify.confirm('PERINGATAN!', msg, function () {
            /* Ok */
            $.ajax({
                "type": 'POST',
                "url": apiUrl + '_delete',
                "headers": { JWT: get_token(API_TOKEN) },
                "data": post_data,
                "dataType": 'json',
                "success": function (result, textStatus, jqXHR) {
                    set_token(API_TOKEN, jqXHR.getResponseHeader('JWT'));
                    if (result.success === true) {
                        $(".btReload").click();
                        notification(result.message, 'success');
                    } else {
                        notification(result.error, 'warn', 3, result.message);
                    }
                    rows_selected.length = table.column(0).checkboxes.deselect();
                    $(window).scrollTop(0);

                    /* Execute callback if exist */
                    typeof callback === 'function' && callback();
                },
                "error": function (jqXHR, textStatus, errorThrown) {
                    rows_selected.length = table.column(0).checkboxes.deselect();
                    notification(errorThrown, 'error');
                    redirectLogin(jqXHR);
                    console.log(jqXHR);
                    console.log(textStatus);
                    console.log(errorThrown);
                }
            });
        }, function () {
            /* Cancel */
        }).set('defaultFocus', 'cancel');
    } else {
        alertify.alert("PERINGATAN!", "Tidak ada data terpilih");
    }
}

/**
 * Populate Combobox / Select options
 * @param {string} apiUrl
 * @param {*} opt
 * @param {object} post_data
 * @param {*} sel
 * @param {string} opt_value
 * @param {string} opt_text
 * @param {string} opt_add
 * @param {string} txt_not_found
 */
function populateSelect(apiUrl, opt, post_data, sel, opt_value, opt_text, opt_add, txt_not_found) {
    opt.closest(':has(span i)').find('span').css('display', '');
    opt.closest('div').css('display', 'none');
    opt.chosen("destroy");
    $.ajax({
        "type": 'POST',
        "headers": { JWT: get_token(API_TOKEN) },
        "url": apiUrl,
        "data": post_data,
        "dataType": 'json',
        "success": function (result, textStatus, jqXHR) {
            set_token(API_TOKEN, jqXHR.getResponseHeader('JWT'));
            if (result.success === true) {
                opt.find('option').remove();
                if (result.message.data != undefined && result.message.data.length > 0) {
                    if (!sel) {
                        opt.append($("<option></option>").attr({ 'value': '', 'selected': 'selected' }).text('SHOW ALL'));
                    }
                    $(result.message.data).each(function (index, el) {
                        let text;
                        if (!opt_add) {
                            text = el[opt_text];
                        } else {
                            text = el[opt_text] + ' - ' + el[opt_add]
                        }
                        if (sel == el[opt_value]) {
                            opt.append($("<option></option>").attr({ 'value': el[opt_value], 'selected': 'selected' }).text(text));
                        } else {
                            opt.append($("<option></option>").attr("value", el[opt_value]).text(text));
                        }
                    });
                    opt.closest(':has(span i)').find('span').css('display', 'none');
                    opt.closest('div').css('display', '');
                    opt.chosen({
                        width: "100%",
                        search_contains: true,
                        allow_single_deselect: !0,
                        no_results_text: (!txt_not_found) ? "Oops, nothing found!" : txt_not_found
                    }).trigger("chosen:updated");
                } else {
                    opt.find('option').remove();
                    opt.append($("<option></option>").attr("value", '00').text("Oops, nothing found!"));
                }
            } else {
                notification(result.error, 'warn', 3, result.message);
            }
        },
        "error": function (jqXHR, textStatus, errorThrown) {
            notification(errorThrown, 'error');
            redirectLogin(jqXHR);
            console.log(jqXHR);
            console.log(textStatus);
            console.log(errorThrown);
        }
    });
}

/**
 * Manual init chosen
 * @param {*} selector
 * @param {*} value
 */
function initChoosen(selector, value=null) {
    let opt = $(selector);
    opt.closest(':has(span i)').find('span').css('display', '');
    opt.closest('div').css('display', 'none');
    opt.chosen("destroy");
    opt.closest(':has(span i)').find('span').css('display', 'none');
    opt.closest('div').css('display', '');
    if (value != null) {
        opt.val(value);
    }
    opt.chosen({
        width: "100%",
        search_contains: true,
        allow_single_deselect: !0,
        no_results_text: "Oops, nothing found!"
    }).trigger("chosen:updated");
}

/**
 * Toggle Switch
 * @param {*} selector
 * @param {*} status
 */
function switchStatus(selector, status) {
    if (status != $(selector).val()) {
        $(selector).click();
    }
}

/**
 * Combobox / Select options searching
 * @param {string} apiUrl
 * @param {*} opt
 * @param {object} post_data
 * @param {*} sel
 * @param {string} opt_value
 * @param {string} opt_text
 * @param {string} opt_add
 */
function findSelect(apiUrl, opt, post_data, sel, opt_value, opt_text, opt_add) {
    $.ajax({
        "type": 'POST',
        "headers": { JWT: get_token(API_TOKEN) },
        "url": apiUrl,
        "delay": 250,
        "data": post_data,
        "dataType": 'json',
        "success": function (result, textStatus, jqXHR) {
            set_token(API_TOKEN, jqXHR.getResponseHeader('JWT'));
            if (result.success === true) {
                opt.find('option').remove();
                if (result.message.data != undefined && result.message.data.length > 0) {
                    $(result.message.data).each(function (index, el) {
                        let text;
                        if (!opt_add) {
                            text = el[opt_text];
                        } else {
                            text = el[opt_text] + ' - ' + el[opt_add]
                        }
                        if (sel == el[opt_value]) {
                            opt.append($("<option></option>").attr({ 'value': el[opt_value], 'selected': 'selected' }).text(text));
                        } else {
                            opt.append($("<option></option>").attr("value", el[opt_value]).text(text));
                        }
                    });
                } else {
                    opt.find('option').remove();
                    opt.append($("<option></option>").attr("value", '00').text("Oops, nothing found!"));
                }

                opt.chosen({
                    width: "100%",
                    search_contains: true,
                    allow_single_deselect: !0,
                    no_results_text: "searching..."
                }).trigger("chosen:updated");
                opt.closest('div').find('input').val(post_data["search[value]"]);;
            } else {
                notification(result.error, 'warn', 3, result.message);
            }
        },
        "error": function (jqXHR, textStatus, errorThrown) {
            notification(errorThrown, 'error');
            redirectLogin(jqXHR);
            console.log(jqXHR);
            console.log(textStatus);
            console.log(errorThrown);
        }
    });
}