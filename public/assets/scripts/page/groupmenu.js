/* Variables */;
var apiUrl = SiteRoot + 'c_groupmenu';
var tbl = "#datatable-responsive tbody";
var pKey, table;
var saveUpdate = "save";

/* Document Ready */
$(document).ready(function () {
	/* First Event Load */
	var enterBackspace = true;
	$("input[name=aktif]").enterKey(function (e) {
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
			"zeroRecords": "Maaf, pencarian Anda tidak ditemukan",
			"info": "Menampilkan _START_ - _END_ dari _TOTAL_ data",
			"infoEmpty": "Data belum tersedia",
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
                title: "List of Groupmenu",
				exportOptions: {
					/* Show column */
					columns: [1, 2, 3, 5] /* [1, 2, 3] => selected column only */
				},
				customize: function (doc) {
					/* Set Default Table Header Alignment */
					doc.styles.tableHeader.alignment = 'left';

					/* Set table width each column */
                    doc.content[1].table.widths = ['5%', '35%', '35%', '25%']; /* ['*', '15%', 'auto'] => each column width*/

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
                className: "btn-sm btn-danger btDels act-delete hidden",
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
		"ordering": false,
		"serverSide": true,
		"ajax": {
			"url": apiUrl + '_read',
			"type": 'post',
			"headers": { JWT: get_token(API_TOKEN) },
			"data": function (data, settings) {
                /* start_loader */
                $(".cssload-loader").hide();
                $("#dtableDiv").fadeIn("slow");
				$("a.btn.btn-default.btn-sm").addClass('disabled');
				$("#tx_dtSpiner").text('Please wait...');
				$("#dtSpiner").removeClass('pause-spinner');

			},
			"dataSrc": function (json) {
				/* stop_loader */
                checkAuth(function () {
                    $("#tx_dtSpiner").text('Reload');
                    $("#dtSpiner").addClass('pause-spinner');
                    $("a.btn.btn-default.btn-sm").removeClass('disabled');
                    setNprogressLoader("done");
                });

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
							0: json.message.data[i].id_groupmenu,
							1: json.message.data[i].no,
							2: json.message.data[i].nama,
							3: json.message.data[i].icon,
							4: json.message.data[i].urut,
							5: json.message.data[i].aktif,
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
			}
		},
		"columnDefs": [
			{
				"targets": 0,
				"className": "select-checkbox",
				"checkboxes": {
					"selectRow": true
				},
				"render": function () {
					return '';
				}
			},
			{
				"targets": 1,
				"className": "dt-center",
			},
			{
				"targets": 3,
				"render": function (data) {
					return '<i class="' + data + '"></i>&nbsp;&nbsp;&nbsp;&nbsp;' + data;
				}
			},
			{
				"targets": 4,
				"className": "dt-right",
			},
			{
				"targets": 5,
				"className": "dt-center",
				"render": function (data) {
					if (data == '1') {
						return '<span class="label label-success">ENABLED</span>';
					} else {
						return '<span class="label label-default">DISABLED</span>';
					}
				}
			},
			{
				"targets": -1,
				"className": "dt-center",
				"data": null,
				"defaultContent":
					'<span class="button-icon-btn button-icon-btn-cl sm-res-mg-t-30"><button title="Edit" id="btEdit" class="hidden btn-act act-edit btn btn-warning warning-icon-notika btn-reco-mg btn-button-mg waves-effect btn-xs" type="button"><i class="notika-icon notika-draft"></i></button></span>' +
					'<span class="button-icon-btn button-icon-btn-cl sm-res-mg-t-30"><button title="Delete" id="btDel" class="hidden btn-act act-delete btn btn-danger danger-icon-notika btn-reco-mg btn-button-mg waves-effect btn-xs" type="button"><i class="notika-icon notika-close"></i></button></span>'
			}
		],
		"select": {
			"style": "multi",
			"selector": "td:first-child",
		}
	});

	/* DataTable search on enter */
	enterAndSearch(table, '#datatable-responsive', enterBackspace)

	/* Button Save Action */
	$('.btn_save').on('click', function () {
		saveOrUpdate(saveUpdate, apiUrl, pKey, '#formEditor');
	});

	/* Button Edit Action */
	$(tbl).on('click', '#btEdit', function () {
		saveUpdate = 'update';
		let data = (table.row($(this).closest('tr')).data() === undefined) ? table.row($(this).closest('li')).data() : table.row($(this).closest('tr')).data();
		pKey = data[0];

		/* Set Edit Form Value */
		$("input[name=nama]").val(data[2]);
		$("input[name=icon]").val(data[3]);
		$("input[name=urut]").val(data[4]);
        switchStatus('input[name=aktif]', data[5]);
		$('.btn_save').html('<i class="fa fa-save"></i> Update');
		$('.modal-title').html('Edit Groupmenu');
		$('.formEditorModal').modal();
	});

	/* Button Delete */
	$(tbl).on('click', '#btDel', function () {
		let data = (table.row($(this).closest('tr')).data() === undefined) ? table.row($(this).closest('li')).data() : table.row($(this).closest('tr')).data();
		deleteSingle(apiUrl, data);
	});

	/* Button Delete Multi */
	$('.btDels').on('click', function () {
		let rows_selected = table.column(0).checkboxes.selected();
		deleteMultiple(apiUrl, table, rows_selected);
	});
});

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

/* Button Create Action */
function btn_add() {
	id = '';
	saveUpdate = 'save';
	$('.btn_save').html('<i class="fa fa-save"></i> Save');
	$('.modal-title').html('New Groupmenu');
	$('.formEditorModal form')[0].reset();
	$('.formEditorModal').modal();
    switchStatus('input[name=aktif]', 0);
};

/* Modal on show */
$('.formEditorModal').on('shown.bs.modal', function () {
	/* code */
	$("input[name=nama]").focus();
});

/* Modal on dissmis */
$('.formEditorModal').on('hide.bs.modal', function () {
	/* code */
    $("form#formEditor").parsley().reset();
    switchStatus('input[name=aktif]', 0);
});
