/* Variables */;
var apiUrl = SiteRoot + 'menu';
var tbl = "#datatable-responsive tbody";
var pKey, table;
var saveUpdate = "save";

/* Document Ready */
$(document).ready(function () {
    /* First Event Load */
    var enterBackspace = true;
    getGroupmenu("#fm_groupmenu");
    initChoosen('.fm_tipe');
    initChoosen('.fm_akses');
    $("input[name=nama]").enterKey(function (e) {
        e.preventDefault();
        $("input[name=url]").focus();
    });
    $("input[name=url]").enterKey(function (e) {
        e.preventDefault();
        $("input[name=controller]").focus();
    });
    $("input[name=controller]").enterKey(function (e) {
        e.preventDefault();
        $("input[name=tipe]").focus();
    });
    $("input[name=tipe]").enterKey(function (e) {
        e.preventDefault();
        $("input[name=urut]").focus();
    });
    $("input[name=urut]").enterKey(function (e) {
        e.preventDefault();
        $(".btn_save").click();
    });

    /* Datatables set_token */
    $("#datatable-responsive").on('xhr.dt', function (e, settings, json, jqXHR) {
        redirectLogin(jqXHR);
        set_token(API_TOKEN, jqXHR.getResponseHeader('JWT'));
    });

    /* Datatables handler */
    table = $("#datatable-responsive").DataTable({
        autoWidth: false,
        language: {
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
                text: "<i class='fa fa-file-pdf-o'></i>",
                className: "btn-sm",
                extend: "pdfHtml5",
                titleAttr: "Export PDF",
                download: "open",
                orientation: 'landscape', /* portrait | landscape */
                pageSize: "LEGAL",
                title: "List of Menu",
                exportOptions: {
                    /* Show column */
                    columns: [1, 3, 5, 6, 7, 8, 9] /* [1, 2, 3] => selected column only */
                },
                customize: function (doc) {
                    /* Set Default Table Header Alignment */
                    doc.styles.tableHeader.alignment = 'left';

                    /* Set table width each column */
                    doc.content[1].table.widths = ['5%', '25%', '20%', '20%', '10%', '10%', '10%'] /* ['*', '15%', 'auto'] => each column width*/

                    /* Set column alignment */
                    var rowCount = doc.content[1].table.body.length;
                    for (i = 0; i < rowCount; i++) {
                        doc.content[1].table.body[i][0].alignment = "right"; /* 1st column align right */
                    };
                }
            },
            {
                text: "<i class='fa fa-plus-circle'></i>",
                className: "btn-sm btn-primary btn_add hidden",
                titleAttr: "Create New",
                action: function () {
                    btn_add();
                }
            },
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
            "url": apiUrl + '/read',
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
                data.action = 'read';
                data.opsional = {
                    'id_groupmenu': $("select[name=fm_groupmenu]").val(),
                    'tipe': $("select[name=fm_tipe]").val(),
                    'is_public': $("select[name=fm_akses]").val(),
                };
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
                            2: json.message.data[i].id_groupmenu,
                            3: json.message.data[i].nama,
                            4: json.message.data[i].icon,
                            5: json.message.data[i].url,
                            6: json.message.data[i].controller,
                            7: json.message.data[i].tipe,
                            8: json.message.data[i].aktif,
                            9: json.message.data[i].is_public,
                            10: json.message.data[i].urut
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
                "targets": [0, 2, 4],
                "visible": false,
                "searchable": false
            },
            {
                "targets": -1,
                "className": "dt-center",
                "data": null,
                "defaultContent":
                    '<span class="button-icon-btn button-icon-btn-cl sm-res-mg-t-30"><button title="Assign" id="btAssign" class="hidden act-setPermission btn-act btn btn-success success-icon-notika btn-reco-mg btn-button-mg waves-effect btn-xs" type="button"><i class="notika-icon notika-menus"></i></button></span>' +
                    '<span class="button-icon-btn button-icon-btn-cl sm-res-mg-t-30"><button title="Edit" id="btEdit" class="hidden btn-act act-edit btn btn-warning warning-icon-notika btn-reco-mg btn-button-mg waves-effect btn-xs" type="button"><i class="notika-icon notika-draft"></i></button></span>' +
                    '<span class="button-icon-btn button-icon-btn-cl sm-res-mg-t-30"><button title="Delete" id="btDel" class="hidden btn-act act-delete btn btn-danger danger-icon-notika btn-reco-mg btn-button-mg waves-effect btn-xs" type="button"><i class="notika-icon notika-close"></i></button></span>'
            },
            {
                "targets": 9,
                "className": "dt-center",
                "render": function (data, type, row) {
                    if (data == '1') {
                        return '<span class="label label-primary">PUBLIC</span>';
                    } else {
                        return '<span class="label label-warning">PRIVATE</span>';
                    }
                }
            },
            {
                "targets": 10,
                "className": "dt-right"
            },
            {
                "targets": 8,
                "className": "dt-center",
                "render": function (data, type, row) {
                    if (data == '1') {
                        return '<span class="label label-success">ENABLED</span>';
                    } else {
                        return '<span class="label label-default">DISABLED</span>';
                    }
                }
            }
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
    enterAndSearch(table, '#datatable-responsive', enterBackspace);

    /* Button Save Action */
    $('.btn_save').on('click', function () {
        saveOrUpdate(saveUpdate, apiUrl, pKey, '.formEditorModal:#formEditor');
    });

    /* Button Edit Action */
    $(tbl).on('click', '#btEdit', function () {
        saveUpdate = 'update';
        let data = (table.row($(this).closest('tr')).data() === undefined) ? table.row($(this).closest('li')).data() : table.row($(this).closest('tr')).data();
        pKey = data[0];

        /* Set Edit Form Value */
        getGroupmenu('#id_groupmenu', data[2]);
        $("input[name=nama]").val(data[3]);
        $("input[name=icon]").val(data[4]);
        $("input[name=url]").val(data[5]);
        $("input[name=controller]").val(data[6]);
        initChoosen('.tipe', data[7]);
        switchStatus('input[name=aktif]', data[8]);
        initChoosen('.public', data[9]);
        $("input[name=urut]").val(data[10]);
        $('.btn_save').html('<i class="fa fa-save"></i> Update');
        $('.formEditorModal > .modal-dialog > .modal-content > .modal-header > .modal-title').html('Edit Menu');
        $('.formEditorModal').modal();
    });

    /* Button Delete */
    $(tbl).on('click', '#btDel', function () {
        let data = (table.row($(this).closest('tr')).data() === undefined) ? table.row($(this).closest('li')).data() : table.row($(this).closest('tr')).data();
        deleteSingle(apiUrl, data[0], data[3]);
    });

    /* Button Assign Action */
    $(tbl).on('click', '#btAssign', function () {
        var data = (table.row($(this).closest('tr')).data() === undefined) ? table.row($(this).closest('li')).data() : table.row($(this).closest('tr')).data();
        pKey = data[0];
        getJabatan(pKey);
        $("#lb_controller").text("");
        $("#lb_controller").text("[" + data[6] + "]");
        $('.formEditorModal2').modal();
    });

    /* Filter Groupmenu Event Change */
    $("select[name=fm_groupmenu]").on('change', function () {
        $(".btReload").click();
    });

    /* Filter Tipe Event Change */
    $("select[name=fm_tipe]").on('change', function () {
        $(".btReload").click();
    });

    /* Filter Akses Event Change */
    $("select[name=fm_akses]").on('change', function () {
        $(".btReload").click();
    });

    /* Set Permission */
    $('.btn_perm').on('click', function () {
        var formData = $('#formEditor2').serializeArray();
        formData.push({ name: 'idmenu', value: pKey });
        $('.btn_perm').button('loading');
        if ($("form#formEditor2").parsley().validate({ force: true, group: 'role' })) {
            $.ajax({
                "type": 'POST',
                "headers": { Authorization: "Bearer " +  get_token(API_TOKEN) },
                "url": SiteRoot + 'c_menu_setpermission',
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
                    $('.formEditorModal2').modal('hide');
                    $('.btn_perm').button('reset');
                },
                "error": function (jqXHR, textStatus, errorThrown) {
                    $('.btn_perm').button('reset');
                    notification(errorThrown, 'error');
                    console.log(jqXHR);
                    console.log(textStatus);
                    console.log(errorThrown);
                }
            });
        } else {
            $('.btn_perm').button('reset');
        }
    });
});


/* Get GroupMenu */
function getGroupmenu(obj, sel = null) {
    let url = SiteRoot + 'groupmenu/read';
    let opt = $(obj);
    let post_data = {
        'draw': 1,
        'start': 0,
        'length': -1, /* Max int (unsigned) */
        'search[value]': ''
    };
    populateSelect(url, opt, post_data, sel, 'id_groupmenu', 'nama');
}

/* Get Jabatan Menu */
function getJabatan(id) {
    var cbx = $("#cb_jabatan");
    var post_data = {
        'idmenu': id,
        'draw': 1,
        'start': 0,
        'length': -1, /* All Data */
        'search[value]': ''
    };
    cbx.append("<i class='fa fa-refresh fa-spin'></i> Please wait...")

    /* Retrieve Jabatan Menu */
    $.ajax({
        "type": 'POST',
        "headers": { Authorization: "Bearer " +  get_token(API_TOKEN) },
        "url": SiteRoot + 'c_menu_jabatanmenu',
        "data": post_data,
        "dataType": 'json',
        "success": function (result, textStatus, jqXHR) {
            set_token(API_TOKEN, jqXHR.getResponseHeader('JWT'));
            if (result.success === true) {
                cbx.html('');
                if (result.message != undefined && result.message.length > 0) {
                    var num = 0; var prs;
                    $(result.message).each(function (index, el) {
                        prs = (num == 0) ? ' data-parsley-mincheck="1" data-parsley-group="role"' : '';
                        cek = (el.ID_ROLE) ? (el.ID_ROLE == el.idrole ? 'checked' : '') : '';
                        cbx.append(
                            '<div class="form-check">' +
                            '<input class="form-check-input" id="jabatan_' + el.idrole + '" type="checkbox" name="ID_ROLE[]" ' + ' value="' + el.idrole + '" ' + cek + '> ' +
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

/* Status Change Event */
$('input[name=aktif]').on('change', function () {
    if (this.value == 1) {
        this.value = 0;
        $('.lbSwitch').text('DISABLED');
    } else {
        this.value = 1;
        $('.lbSwitch').text('ENABLED');
    }
});

/* Tipe Change Event */
$('#tipe').on('change', function(){
    if (this.value == 'MENU') {
        $('#tx_star').html('');
        $('#tx_star').removeClass('required');
        $('input[name=controller]').removeAttr('data-parsley-group');
    }else{
        $('#tx_star').html('*');
        $('#tx_star').addClass('required');
        $('input[name=controller]').attr('data-parsley-group', 'role');
    }
});

/* Button Create Action */
function btn_add() {
    id = '';
    saveUpdate = 'save';
    getGroupmenu('#id_groupmenu', ($("#fm_groupmenu").val() ? $("#fm_groupmenu").val() : '0'));
    initChoosen('.tipe');
    initChoosen('.public');
    $('.btn_save').html('<i class="fa fa-save"></i> Save');
    $('.formEditorModal > .modal-dialog > .modal-content > .modal-header > .modal-title').html('New Menu');
    $('.formEditorModal form')[0].reset();
    $('.formEditorModal').modal();
    switchStatus('input[name=aktif]', 0);
};

/* Modal on show */
$('.formEditorModal').on('shown.bs.modal', function () {
    $("input[name=nama]").focus();
    $('#tipe').trigger('change');
});

/* Modal on dissmis */
$('.formEditorModal').on('hide.bs.modal', function () {
    /* code */
    $("form#formEditor").parsley().reset();
    switchStatus('input[name=aktif]', 0);
});

/* Modal 2 on dissmis */
$('.formEditorModal2').on('hide.bs.modal', function () {
    $("form#formEditor2").parsley().reset();
    $("#cb_jabatan").html('');
});
