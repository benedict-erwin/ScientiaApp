/* Variables */
var apiUrl = SiteRoot + "crud_gen";
var tbl = '#datatable-generator tbody';
var pKey = '';
var table;

/* Document Ready */
$(document).ready(function () {
    /* First Event Load */
    var enterBackspace = true;
    getGroupmenu('#fm_groupmenu');
    $("#genBO").on("change", function () {
        if ($(this).is(':checked') && $(this).val() == 'BO') {
            setTimeout(function () {
                $("#create").prop("checked", false);
                $("#read").prop("checked", true);
                $("#update").prop("checked", false);
                $("#delete").prop("checked", false);
            }, 0);
        }
    });
    $("#genBF").on("change", function () {
        if ($(this).is(':checked') && $(this).val() == 'BF') {
            setTimeout(function () {
                $("#create").prop("checked", true);
                $("#read").prop("checked", true);
                $("#update").prop("checked", true);
                $("#delete").prop("checked", true);
            }, 0);
        }
    });
    $("#m_groupmenu").on("change", function () {
        setTimeout(function () {
            $("input[name=menu]").focus();
        }, 0);
    });
    $("input[name=menu]").enterKey(function (e) {
        e.preventDefault();
    });
    $("#get_tables").on("change", function () {
        setTimeout(function () {
            $("input[name=url]").focus();
        }, 0);
    });
    $("input[name=url]").enterKey(function (e) {
        e.preventDefault();
        $("input[name=controller]").focus();
    });

    /* Datatables set_token */
    $("#datatable-generator").on('xhr.dt', function (e, settings, json, jqXHR) {
        redirectLogin(jqXHR);
        set_token(API_TOKEN, jqXHR.getResponseHeader('JWT'));
    });

    /* Datatables handler */
    table = $("#datatable-generator").DataTable({
        autoWidth: false,
        "language": {
            "emptyTable": "Tidak ada data yang tersedia",
            "zeroRecords": "Maaf, pencarian Anda tidak ditemukan",
            "info": "Menampilkan _START_ - _END_ dari _TOTAL_ data",
            "infoEmpty": "Menampilkan 0 - 0 dari 0 data",
            "infoFiltered": "(terfilter dari _MAX_ total data)",
            "searchPlaceholder": "Enter untuk mencari"
        },
        "dom": "<'row'<'col-sm-8'B><'col-sm-4'f>><'row'<'col-sm-12't>><'row'<'col-sm-4'<'pull-left' p>><'col-sm-8'<'pull-right' i>>>",
        "buttons": [
            {
                extend: "pageLength",
                className: "btn-sm bt-separ"
            },
            {
                text: "<i id='dtSpiner' class='fa fa-refresh fa-spin'></i> <span id='tx_dtSpiner'>Reload</span>",
                className: "btn-sm btReload",
                titleAttr: "Reload Data",
                action: function () {
                    dtReload(table);
                }
            },
            {
                text: "<i class='fa fa-plus-circle'></i>",
                titleAttr: "Create New",
                className: "btn-sm btn-primary btn_gen",
                action: function () {
                    btn_gen();
                }
            }
        ],
        "pagingType": "numbers",
        "lengthMenu": [
            [10, 25, 50, 100, -1],
            [10, 25, 50, 100, 'All']
        ],
        "responsive": true,
        "processing": false,
        "ordering": false,
        "serverSide": true,
        "ajax": {
            "url": apiUrl,
            "type": 'post',
            "headers": { Authorization: "Bearer " +  get_token(API_TOKEN) },
            "data": function (data, settings) {
                /* start_loader */
                $(".cssload-loader").hide();
                $("#dtableDiv").fadeIn("slow");
                $("a.btn.btn-default.btn-sm").addClass('disabled');
                $("#tx_dtSpiner").text('Please wait...');
                $("#dtSpiner").removeClass('pause-spinner');

                /* Post Data */
                data.action = 'read_menu';
                data.opsional = { 'id_groupmenu': $("select[name=fm_groupmenu]").val() };
            },
            "dataSrc": function (json) {
                /* return variable */
                var return_data = [];
                if (json.success === true) {
                    /* Redraw json result */
                    json.draw = json.message.draw;
                    json.recordsFiltered = json.message.recordsFiltered;
                    json.recordsTotal = json.message.recordsTotal;

                    /* ReOrdering json result */
                    for (var i = 0; i < json.message.data.length; i++) {
                        return_data.push({
                            0: json.message.data[i].id_menu,
                            1: json.message.data[i].no,
                            2: json.message.data[i].nama,
                            3: json.message.data[i].url,
                            4: json.message.data[i].controller,
                            5: json.message.data[i].tipe,
                            6: json.message.data[i].aktif,
                            7: json.message.data[i].is_public
                        })
                    }

                    return return_data;
                } else {
                    json.draw = null;
                    json.recordsFiltered = null;
                    json.recordsTotal = null;
                    return_data = [];
                    notification(json.error, 'warn', 2, json.message);
                    return return_data;
                }
            },
            "error": function (jqXHR, textStatus, errorThrown) {
                notification(jqXHR.responseJSON.error, 'error', 3, 'ERROR');
                console.log(jqXHR);
                console.log(textStatus);
                console.log(errorThrown);
            }
        },
        "deferRender": true,
        "columnDefs": [
            {
                "targets": [0],
                "visible": false,
                "searchable": false
            },
            {
                "targets": 6,
                "className": "dt-center",
                "render": function (data, type, row) {
                    if (data == '1') {
                        return '<span class="label label-success">AKTIF</span>';
                    } else {
                        return '<span class="label label-default">NON AKTIF</span>';
                    }
                }
            },
            {
                "targets": 7,
                "className": "dt-center",
                "render": function (data, type, row) {
                    if (data == '1') {
                        return '<span class="label label-primary">PUBLIC</span>';
                    } else {
                        return '<span class="label label-warning">PRIVATE</span>';
                    }
                }
            },
        ]
    }).on('draw', function() {
		/* stop_loader */
		checkAuth(function(){
			$("#tx_dtSpiner").text('Reload');
			$("#dtSpiner").addClass('pause-spinner');
			$("a.btn.btn-default.btn-sm").removeClass('disabled');
			setNprogressLoader("done");
		});
	});

    /* DataTable search on enter */
    enterAndSearch(table, '#datatable-generator', enterBackspace);

    /* Button Save Action */
    $('.btn_save').on('click', function () {
        crudCheck();
        saveOrUpdate('generate', apiUrl, null, '.formEditorModal:#formEditor', function () {
            setTimeout(() => {
                // window.location.reload();
            }, 600);
        });
    });

    /* Filter Groupmenu Event Change */
    $("select[name=fm_groupmenu]").on('change', function () {
        table.ajax.reload();
    });

});

/* Get GroupMenu */
function getGroupmenu(obj, sel = null) {
    let opt = $(obj);
    let post_data = {
        'action': 'get_groupmenu',
        'draw': 1,
        'start': 0,
        'length': -1, /* All data */
        'search[value]': ''
    };
    populateSelect(apiUrl, opt, post_data, sel, 'id_groupmenu', 'nama');
}

/* Get Tables */
function getTables() {
    let opt = $(".get_tables");
    let post_data = {
        'action': 'get_tables'
    };

    /* Retrieve Groupmenu */
    opt.closest(':has(span i)').find('span').css('display', '');
    opt.closest('div').css('display', 'none');
    opt.chosen("destroy");
    $.ajax({
        "type": 'POST',
        "headers": { Authorization: "Bearer " +  get_token(API_TOKEN) },
        "url": apiUrl,
        "data": post_data,
        "dataType": 'json',
        "success": function (result, textStatus, jqXHR) {
            set_token(API_TOKEN, jqXHR.getResponseHeader('JWT'));
            if (result.success === true) {
                opt.find('option').remove();
                if (result.message.table != undefined && result.message.table.length > 0) {
                    $(result.message.table).each(function (index, el) {
                        opt.append($("<option></option>").attr("value", el).text(el));
                    });
                    opt.closest(':has(span i)').find('span').css('display', 'none');
                    opt.closest('div').css('display', '');
                    opt.chosen({
                        width: "100%",
                        search_contains: true,
                        allow_single_deselect: !0,
                        no_results_text: "Oops, nothing found!"
                    }).trigger("chosen:updated");
                } else {
                    console.log('NO DATA');
                    opt.find('option').remove();
                    opt.append($("<option></option>").attr("value", '00').text("NO GROUP MENU"));
                }
            } else {
                notification((result.message.error) ? result.message.error : result.message, 'warn');
            }
        },
        "error": function (jqXHR, textStatus, errorThrown) {
            notification(errorThrown, 'error');
            console.log(jqXHR);
            console.log(textStatus);
            console.log(errorThrown);
        }
    });
}

/* Get Jabatan */
function getJabatan() {
    var cbx = $("#cb_jabatan");
    var post_data = {
        'action': 'get_jabatan',
        'draw': 1,
        'start': 0,
        'length': -1, /* All Data */
        'search[value]': ''
    };

    /* Loader */
    cbx.append("<i class='fa fa-refresh fa-spin'></i> Please wait...")

    /* Retrieve Jabatan */
    $.ajax({
        "type": 'POST',
        "headers": { Authorization: "Bearer " +  get_token(API_TOKEN) },
        "url": apiUrl,
        "data": post_data,
        "dataType": 'json',
        "success": function (result, textStatus, jqXHR) {
            set_token(API_TOKEN, jqXHR.getResponseHeader('JWT'));
            if (result.success === true) {
                cbx.html('');
                if (result.message.data != undefined && result.message.data.length > 0) {
                    var num = 0; var prs;
                    $(result.message.data).each(function (index, el) {
                        prs = (num == 0) ? ' data-parsley-mincheck="1" data-parsley-group="role"' : '';
                        cbx.append(
                            '<div class="form-check">' +
                            '<input class="form-check-input" id="jabatan_' + el.idrole + '" type="checkbox" name="id_jabatan[]" ' + prs + ' value="' + el.idrole + '"> ' +
                            '<label class="form-check-label for="jabatan_' + el.idrole + '"> ' + el.deskripsi + '</label>' +
                            '</div>'
                        );
                        num++;
                    });
                } else {
                    console.log('NO DATA');
                    cbx.html('');
                    cbx.append('<div class="form-check">NO DATA FOUND!</div>');
                }
            } else {
                notification((result.message.error) ? result.message.error : result.message, 'warn');
            }
        },
        "error": function (jqXHR, textStatus, errorThrown) {
            notification(errorThrown, 'error');
            console.log(jqXHR);
            console.log(textStatus);
            console.log(errorThrown);
        }
    });
}

/* Button Create Action */
function btn_gen() {
    id = '';
    getGroupmenu('#m_groupmenu', ($("#fm_groupmenu").val() ? $("#fm_groupmenu").val() : '0'));
    $('.btn_save').html('<i class="fa fa-save"></i> Save');
    $('.formEditorModal form')[0].reset();
    $('#inactive').prop('checked', true);
    $('.formEditorModal').modal();
};

/* CRUD Checker */
function crudCheck() {
    if ($("#genBO").is(':checked') && $("#genBO").val() == 'BO') {
        $(":checkbox[value=r]").prop("checked", true);
    }
    if ($("#genBF").is(':checked') && $("#genBF").val() == 'BF') {
        $(":checkbox[value=c]").prop("checked", true);
        $(":checkbox[value=r]").prop("checked", true);
        $(":checkbox[value=u]").prop("checked", true);
        $(":checkbox[value=d]").prop("checked", true);
    }
}

/* Modal on show */
$('.formEditorModal').on('shown.bs.modal', function () {
    getTables();
    getJabatan();
    crudCheck();
});

/* Modal on dissmis */
$('.formEditorModal').on('hide.bs.modal', function () {
    $("form#formEditor").parsley().reset();
    $("#cb_jabatan").html('');
});
