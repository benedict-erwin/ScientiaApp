/* Variables */;
var apiUrl = SiteRoot + 'user';
var tbl = "#datatable-responsive tbody";
var pKey, table;
var saveUpdate = "save";

/* Document Ready */
$(document).ready(function() {
    /* First Event Load */
    var enterBackspace = true;
    getJabatan('#fm_jabatan');
    $("input[name=nama]").enterKey(function (e) {
        e.preventDefault();
        $("input[name=email]").focus();
    });
    $("input[name=email]").enterKey(function (e) {
        e.preventDefault();
        //$("#c_idjabatan").select2('open');
    });
    $("#c_idjabatan").on("change", function () {
        setTimeout(function () {
            $("input[name=telpon]").focus();
        }, 0);
    });
    $("input[name=telpon]").enterKey(function (e) {
        e.preventDefault();
        $("input[name=username]").focus();
    });
    $("input[name=username]").enterKey(function (e) {
        e.preventDefault();
        $("input[name=password]").focus();
    });
    $("input[name=password]").enterKey(function (e) {
        e.preventDefault();
        $(".btn_save").click();
    });

	/* Datatables set_token */
	$("#datatable-responsive").on('xhr.dt', function(e, settings, json, jqXHR){
        redirectLogin(jqXHR);
        set_token(API_TOKEN, jqXHR.getResponseHeader('JWT'));
	});

	/* Datatables handler */
	var table = $("#datatable-responsive").DataTable({
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
                orientation: 'portrait', /* portrait | landscape */
                pageSize: "LEGAL",
                title: "List of User",
                exportOptions: {
                    /* Show column */
                    columns: [1, 2, 3, 4, 6] /* [1, 2, 3] => selected column only */
                },
                customize: function (doc) {
                    /* Set Default Table Header Alignment */
                    doc.styles.tableHeader.alignment = 'left';

                    /* Set table width each column */
                    doc.content[1].table.widths = ['5%', '30%', '15%', '30%', '20%']; /* ['*', '15%', 'auto'] => each column width*/

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
            {
                text: "<i class='fa fa-trash'></i>",
                className: "btn-sm btn-danger btDels act-delete",
                titleAttr: "Multiple Delete",
            },
        ],
        "pagingType": "numbers",
		"lengthMenu": [
			[10, 25, 50, 100, -1],
			[10, 25, 50, 100, 'All']
		],
		"responsive": true,
		"processing": false,
        "ordering": true,
        "order": [[2, "asc"]],
		"serverSide": true,
		"ajax": {
			"url": apiUrl + '/read',
			"type": 'post',
			"headers": { Authorization: "Bearer " +  get_token(API_TOKEN) },
			"data": function(data, settings){
                /* start_loader */
                $(".cssload-loader").hide();
                $("#dtableDiv").fadeIn("slow");
                $("a.btn.btn-default.btn-sm").addClass('disabled');
                $("#tx_dtSpiner").text('Please wait...');
                $("#dtSpiner").removeClass('pause-spinner');

				/* Post Data */
                data.opsional = {
                    'idrole': $("#fm_jabatan").val(),
                };
			},
			"dataSrc": function(json) {
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
							0: json.message.data[i].iduser,
							1: json.message.data[i].no,
							2: json.message.data[i].nama,
							3: json.message.data[i].username,
							4: json.message.data[i].email,
							5: json.message.data[i].idrole,
							6: json.message.data[i].telpon,
							7: json.message.data[i].role,
							8: json.message.data[i].lastlogin,
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
                "targets": 0,
                "className": "select-checkbox",
                "orderable": false,
                "checkboxes": {
                    "selectRow": true
                },
                "render": function (data, type, full, meta) {
                    return '';
                }
            },
            {
                "targets": 1,
                "className": "dt-center",
                "orderable": false
            },
            {
                "targets": 4,
                "render": function (data, type, row) {
                    return '<a href="mailto:' + data + '" target="_blank">' + data + '</a>';
                }
            },
            {
                "targets": 5,
                "visible": false,
                "searchable": false,
                "orderable": false
            },
            {
                "targets": 7,
                "orderable": false,
                "render": function (data, type, row) {
                    return strtoupper(data);
                }
            },
            {
                "targets": 8,
                "className": "dt-center",
                "orderable": false,
                "render": function (data, type, row) {
                    return (data) ? DateFormat(data.split(' ')[0], '-') + ' ' + data.split(' ')[1]:'-';
                }
            },
            {
                "targets": -1,
                "className": "dt-center",
                "orderable": false,
                "data": null,
                "defaultContent":
                    '<span class="button-icon-btn button-icon-btn-cl sm-res-mg-t-30"><button title="Edit" id="btEdit" class="hidden btn-act btn act-edit btn-warning warning-icon-notika btn-reco-mg btn-button-mg waves-effect btn-xs" type="button"><i class="notika-icon notika-draft"></i></button></span>' +
                    '<span class="button-icon-btn button-icon-btn-cl sm-res-mg-t-30"><button title="Delete" id="btDel" class="hidden btn-act btn act-delete btn-danger danger-icon-notika btn-reco-mg btn-button-mg waves-effect btn-xs" type="button"><i class="notika-icon notika-close"></i></button></span>'
            }
        ],
        "select": {
            "style": "multi",
            "selector": "td:first-child",
        }
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
        $("input[name=nama]").val(data[2]);
        $("input[name=email]").val(data[4]);
        getJabatan('#idjabatan', data[5]);
        $("input[name=telpon]").val(data[6]);
        $("input[name=username]").val(data[3]);
        $("input[name=password]").removeAttr('required');
        $('.btn_save').html('<i class="fa fa-save"></i> Update');
        $('.modal-title').html('Edit User');
        $('.formEditorModal').modal();
    });

    /* Button Delete */
    $(tbl).on('click', '#btDel', function () {
        let data = (table.row($(this).closest('tr')).data() === undefined) ? table.row($(this).closest('li')).data() : table.row($(this).closest('tr')).data();
        deleteSingle(apiUrl, data[0], data[2]);
    });

    /* Button Delete Multi */
    $('.btDels').on('click', function () {
        let rows_selected = table.column(0).checkboxes.selected();
        deleteMultiple(apiUrl, table, rows_selected);
    });

    /* Filter Principle Event Change */
    $("select[name=fm_jabatan]").on('change', function () {
        $(".btReload").click();
    });

});

/* Get GroupMenu */
function getJabatan(obj, sel = null) {
    let url = SiteRoot + 'role/read';
    let opt = $(obj);
    let post_data = {
        'draw': 1,
        'start': 0,
        'length': -1, /* All data */
        'search[value]': ''
    };
    populateSelect(url, opt, post_data, sel, 'idrole', 'deskripsi');
}


/* Button Create Action */
function btn_add() {
    id = '';
    saveUpdate = 'save';
    $('input[name=password]').attr('required', 'required');
    getJabatan('#idjabatan', ($("#fm_jabatan").val() ? $("#fm_jabatan").val() : '0'));
	$('.btn_save').html('<i class="fa fa-save"></i> Save');
	$('.modal-title').html('New User');
	$('.formEditorModal form')[0].reset();
	$('.formEditorModal').modal();
};

/* Modal on show */
$('.formEditorModal').on('shown.bs.modal', function () {
    /* code */
    $("input[name=nama]").focus();
});

/* Modal on dissmis */
$('.formEditorModal').on('hide.bs.modal', function() {
	/* code */
    $("form#formEditor").parsley().reset();
    $('input[name=password]').val('');
});
