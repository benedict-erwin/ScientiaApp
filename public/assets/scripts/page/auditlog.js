/* Variables */;
var apiUrl = SiteRoot + 'auditlog';
var tbl = "#datatable-responsive tbody";
var pKey, table;
var rangeDateStart, rangeDateEnd;
var saveUpdate = "save";

/* Document Ready */
$(document).ready(function() {
    /* First Event Load */
    initChoosen('.http_method', '');
	var enterBackspace = true;
	$("input[name=ip_address]").enterKey(function (e) {
		e.preventDefault();
		$(".btn_save").click();
	});

	/* Datatables set_token */
	$("#datatable-responsive").on('xhr.dt', function(e, settings, json, jqXHR){
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
				action: function() {
					dtReload(table);
				}
			},
			{
				text: "<i class='fa fa-file-pdf-o'></i>",
				className: "btn-sm",
				extend: "pdfHtml5",
				titleAttr: "Export PDF",
				download: "open",
				pageSize: "LEGAL",
				orientation: "landscape", /* portrait | landscape */
                title: function () {
                    return 'AuditLog Periode ' + moment(rangeDateStart).format('DD-MM-YYYY') + " s/d " + moment(rangeDateEnd).format('DD-MM-YYYY');
                },
				exportOptions: {
					/* Show column */
					columns: [1, 2, 3, 4, 5, 6, 7] /* [1, 2, 3] => selected column only */
				},
				customize: function (doc) {
					/* Set Default Table Header Alignment */
					doc.styles.tableHeader.alignment = 'left';

					/* Set table width each column */
					doc.content[1].table.widths = ['5%', '10%', '20%', '20%', '20%', '10%', '15%']; /* ['*', '15%', 'auto'] => each column width*/

					/* Set column alignment */
					var rowCount = doc.content[1].table.body.length;
					for (i = 0; i < rowCount; i++) {
						doc.content[1].table.body[i][0].alignment = "right"; /* 1st column align right */
					};
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
			"url": apiUrl + '/read',
			"type": 'post',
			"headers": { Authorization: "Bearer " + get_token(API_TOKEN) },
			"data": function(data, settings){
				/* start_loader */
				$(".cssload-loader").hide();
				$("#dtableDiv").fadeIn("slow");
				$("a.btn.btn-default.btn-sm").addClass('disabled');
				$("#tx_dtSpiner").text('Please wait...');
                $("#dtSpiner").removeClass('pause-spinner');

                /* Post Data */
                data.periode_start = rangeDateStart;
                data.periode_end = rangeDateEnd;
                data.opsional = {
                    'la.http_method': $("select[name=http_method]").val(),
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
							0: json.message.data[i].idauditlog,
							1: json.message.data[i].no,
							2: json.message.data[i].username,
							3: json.message.data[i].nama,
							4: json.message.data[i].tanggal,
							5: json.message.data[i].action,
							6: json.message.data[i].http_method,
							7: json.message.data[i].ip_address,
							8: json.message.data[i].data,
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
				"visible": false
			},
			{
				"targets": 1,
				"className": "dt-center",
			},
			{
				"targets": 4,
                "className": "text-center",
                "render": function (data, type, row) {
                    return (data) ? DateFormat(data.split(' ')[0], '-') + ' ' + data.split(' ')[1] : '-';
                }
			},
			{
                "targets": 8,
                "className": "dt-center",
                "data": null,
                "defaultContent": '<i title="Tampilkan Data" id="tip_jsonMat" style="cursor:pointer;" class="notika-icon notika-menu-sidebar"></i>'
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

    /* Button Tampilkan Data */
    $(tbl).on('click', '#tip_jsonMat', function () {
        let data = (table.row($(this).closest('tr')).data() === undefined) ? table.row($(this).closest('li')).data() : table.row($(this).closest('tr')).data();
        $('#json-renderer').html('');
        $('#js_time').text((data[4]) ? DateFormat(data[4].split(' ')[0], '-') + ' ' + data[4].split(' ')[1] : '-');
        $('#js_username').text(data[2]);
        $('#js_url').text(data[5]);
        $('#js_method').text(data[6]);
        $('#js_from').text(data[7]);
        $('#json-renderer').jsonViewer(JSON.parse(data[8]));
        $('.formEditorModal').modal();
    });
});

/** Date range picker */
$(function () {
    var start = moment().subtract(29, 'days');
    var end = moment();

    function cb(start, end) {
        $('#reportrange span').html(start.format('YYYY/MM/DD') + ' - ' + end.format('YYYY/MM/DD'));
        rangeDateStart = start.format('YYYY-MM-DD');
        rangeDateEnd = end.format('YYYY-MM-DD');
    }

    $('#reportrange').daterangepicker({
        startDate: start,
        endDate: end,
        ranges: {
            'Hari ini': [moment(), moment()],
            'Kemarin': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            '7 Hari terakhir': [moment().subtract(6, 'days'), moment()],
            '30 Hari terakhir': [moment().subtract(29, 'days'), moment()],
            'Bulan ini': [moment().startOf('month'), moment().endOf('month')],
            'Bulan lalu': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
        },
        "locale": {
            "customRangeLabel": "Periode lain",
            "applyLabel": "Pilih",
            "cancelLabel": "Batal",
        }
    }, cb);

    cb(start, end);

});

/** btApply click */
$(document).on('click', '#btApply', function () {
    $(".btReload").click();
});

/* Modal on show */
$('.formEditorModal').on('shown.bs.modal', function () {
    /* code */
});

/* Modal on dissmis */
$('.formEditorModal').on('hide.bs.modal', function() {
    /* code */
});
