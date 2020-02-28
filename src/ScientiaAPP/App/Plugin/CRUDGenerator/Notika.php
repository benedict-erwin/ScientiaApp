<?php

/**
 * @project    ScientiaAPP - Web Apps Skeleton & CRUD Generator
 * @package    App\Controllers\Privates
 * @author     Benedict E. Pranata
 * @copyright  (c) 2018 benedict.erwin@gmail.com
 * @created    on Wed Sep 05 2018
 * @license    GNU GPLv3 <https://www.gnu.org/licenses/gpl-3.0.en.html>
 **/

namespace App\Plugin\CRUDGenerator;

use App\Lib\Stringer;

class Notika
{
    public function filterTableTop(array $data = [])
    {
        $html = '';
        $groups = Stringer::fill_chunck($data, ceil(count($data) / 4));
        foreach ($groups as $group) {
            $html .= "\n\t\t\t\t\t\t\t\t<div class=\"row margin-bt-20\">";

            foreach ($group as $col) {
                $html .= "\n\t\t\t\t\t\t\t\t\t<div class=\"col-lg-3 col-md-3 col-sm-3 col-xs-12\">";
                $html .= "\n\t\t\t\t\t\t\t\t\t\t<div class=\"nk-int-mk sl-dp-mn\">";
                $html .= "\n\t\t\t\t\t\t\t\t\t\t\t<h2>Filter " . ucwords($col["REFERENCED_COLUMN_NAME"]) . "</h2>";
                $html .= "\n\t\t\t\t\t\t\t\t\t\t</div>";
                $html .= "\n\t\t\t\t\t\t\t\t\t\t<span style=\"margin-left: 10px;\"><i class=\"fa fa-spinner fa-spin\"></i> Please wait...</span>";
                $html .= "\n\t\t\t\t\t\t\t\t\t\t<div class=\"chosen-select-act fm-cmp-mg\" style=\"display:none;\">";
                $html .= "\n\t\t\t\t\t\t\t\t\t\t\t<select id=\"" . $col['REFERENCED_TABLE_NAME'] . "\" name=\"" . $col['REFERENCED_TABLE_NAME'] . "\" class=\"chosen " . $col["REFERENCED_COLUMN_NAME"] . "\"></select>";
                $html .= "\n\t\t\t\t\t\t\t\t\t\t</div>";
                $html .= "\n\t\t\t\t\t\t\t\t\t</div>";
            }

            $html .= "\n\t\t\t\t\t\t\t\t</div>";
        }
        return $html;
    }

    /**
     * HTML Template Generator
     *
     * @param array $data
     * @return string
     */
    public function stringHTML($data = [])
    {
        $tipe = null;
        $nama = null;
        $html = '';

        /* Breadcomb Area */
        $html .= "<!-- Breadcomb area Start-->";
        $html .= "\n<div class=\"breadcomb-area\">";
        $html .= "\n\t<div class=\"container\">";
        $html .= "\n\t\t<div class=\"row\">";
        $html .= "\n\t\t\t<div class=\"col-lg-12 col-md-12 col-sm-12 col-xs-12\">";
        $html .= "\n\t\t\t\t<div class=\"breadcomb-list\">";
        $html .= "\n\t\t\t\t\t<div class=\"row\">";
        $html .= "\n\t\t\t\t\t\t<div class=\"col-lg-6 col-md-6 col-sm-6 col-xs-12\">";
        $html .= "\n\t\t\t\t\t\t\t<div class=\"breadcomb-wp\">";
        $html .= "\n\t\t\t\t\t\t\t\t<div class=\"breadcomb-icon\">";
        $html .= "\n\t\t\t\t\t\t\t\t\t<i class=\"" . $data['icon_groupmenu'] . " animated infinite flip\"></i>";
        $html .= "\n\t\t\t\t\t\t\t\t</div>";
        $html .= "\n\t\t\t\t\t\t\t\t<div class=\"breadcomb-ctn\">";
        $html .= "\n\t\t\t\t\t\t\t\t\t<h2>" . $data['menu'] . "</h2>";
        $html .= "\n\t\t\t\t\t\t\t\t\t<p>Add / Edit / Delete</p>";
        $html .= "\n\t\t\t\t\t\t\t\t</div>";
        $html .= "\n\t\t\t\t\t\t\t</div>";
        $html .= "\n\t\t\t\t\t\t</div>";
        $html .= "\n\t\t\t\t\t\t<div class=\"col-lg-6 col-md-6 col-sm-6 col-xs-3\">";
        $html .= "\n\t\t\t\t\t\t\t<div class=\"breadcomb-report\">";
        $html .= "\n\t\t\t\t\t\t\t\t<button id=\"tikClock\" data-toggle=\"tooltip\" data-placement=\"left\" title=\"\" class=\"btn\">";
        $html .= "\n\t\t\t\t\t\t\t\t\t<i class=\"fa fa-clock-o\"></i>";
        $html .= "\n\t\t\t\t\t\t\t\t</button>";
        $html .= "\n\t\t\t\t\t\t\t</div>";
        $html .= "\n\t\t\t\t\t\t</div>";
        $html .= "\n\t\t\t\t\t</div>";
        $html .= "\n\t\t\t\t</div>";
        $html .= "\n\t\t\t</div>";
        $html .= "\n\t\t</div>";
        $html .= "\n\t</div>";
        $html .= "\n</div>";
        $html .= "\n<!-- Breadcomb area End-->";
        //-->

        /* Main Content + DataTables */
        $html .= "\n<!-- Main area Start-->";
        $html .= "\n<div class=\"colr-area\">";
        $html .= "\n\t<div class=\"container\">";
        $html .= "\n\t\t<div class=\"row\">";
        $html .= "\n\t\t\t<div class=\"col-lg-12 col-md-12 col-sm-12 col-xs-12\">";
        $html .= "\n\t\t\t\t<div class=\"color-wrap\">";
        $html .= "\n\t\t\t\t\t<div class=\"color-hd\">";
        $html .= "\n\t\t\t\t\t\t<div class=\"x_content\">";
        $html .= "\n\t\t\t\t\t\t\t<div class=\"cssload-loader\">Please Wait</div>";
        $html .= "\n\t\t\t\t\t\t\t<div id=\"dtableDiv\" style=\"display:none;\">";

        /* # Filter */
        if (!empty($data['relation']['relation'])) {
            $idx = 0;
            $fmSelect = null;
            $html .= $this->generator->filterTableTop($data['relation']['relation']);
            foreach ($data['relation']['relation'] as $col) {
                /* Select - Modal Form */
                $fmSelect .= "\n\t\t\t\t\t<div class=\"form-group-15\">";
                $fmSelect .= "\n\t\t\t\t\t\t<label>" . ucwords($col["REFERENCED_COLUMN_NAME"]) . " <span class=\"required\">*</span></label>";
                $fmSelect .= "\n\t\t\t\t\t\t<div class=\"nk-int-st\">";
                $fmSelect .= "\n\t\t\t\t\t\t\t<span style=\"margin-left: 10px;\"><i class=\"fa fa-spinner fa-spin\"></i> Please wait...</span>";
                $fmSelect .= "\n\t\t\t\t\t\t\t<div class=\"chosen-select-act fm-cmp-mg\" style=\"display:none;\">";
                $fmSelect .= "\n\t\t\t\t\t\t\t\t<select id=\"" . $col["REFERENCED_COLUMN_NAME"] . "\" name=\"" . $col["REFERENCED_COLUMN_NAME"] . "\" class=\"chosen " . $col["REFERENCED_COLUMN_NAME"] . "\" required=\"required\" data-parsley-group=\"role\"></select>";
                $fmSelect .= "\n\t\t\t\t\t\t\t</div>";
                $fmSelect .= "\n\t\t\t\t\t\t</div>";
                $fmSelect .= "\n\t\t\t\t\t</div>";
            }
        }
        // #-->

        /* DataTables */
        $html .= "\n\t\t\t\t\t\t\t\t<table id=\"datatable-responsive\"";
        $html .= "\n\t\t\t\t\t\t\t\t\tclass=\"table table-striped table-bordered table-hover dt-responsive wrap\"";
        $html .= "\n\t\t\t\t\t\t\t\t\tcellspacing=\"0\" width=\"100%\">";
        $html .= "\n\t\t\t\t\t\t\t\t\t<thead>";
        $html .= "\n\t\t\t\t\t\t\t\t\t\t<tr>";

        /* Set Table Header */
        foreach ($data['dtcols'] as $idx => $cols) {
            $cols = strtoupper(strtolower(str_replace("_", " ", $cols)));
            if ($idx == 0) {
                $html .= "\n\t\t\t\t\t\t\t\t\t\t\t<th width=\"1%\"></th>";
            } elseif ($idx == 1) {
                $html .= "\n\t\t\t\t\t\t\t\t\t\t\t<th width=\"2%\" class=\"text-center\">NO</th>";
                $html .= "\n\t\t\t\t\t\t\t\t\t\t\t<th width=\"10%\">" . $cols . "</th>";
            } elseif ($idx > 1) {
                $html .= "\n\t\t\t\t\t\t\t\t\t\t\t<th width=\"10%\">" . $cols . "</th>";
            }
        }
        $html .= "\n\t\t\t\t\t\t\t\t\t\t\t<th width=\"12%\" class=\"all\">ACTION</th>";
        $html .= "\n\t\t\t\t\t\t\t\t\t\t</tr>";

        $html .= "\n\t\t\t\t\t\t\t\t\t</thead>";
        $html .= "\n\t\t\t\t\t\t\t\t</table>";
        //-->

        $html .= "\n\t\t\t\t\t\t\t</div>";
        $html .= "\n\t\t\t\t\t\t</div>";
        $html .= "\n\t\t\t\t\t</div>";
        $html .= "\n\t\t\t\t</div>";
        $html .= "\n\t\t\t</div>";
        $html .= "\n\t\t</div>";
        $html .= "\n\t</div>";
        $html .= "\n</div>";
        $html .= "\n<!-- Main area End-->";
        //-->

        /* Modal Form */
        $html .= "\n<!-- #region modal -->";
        $html .= "\n<div class=\"modal fade formEditorModal\" data-backdrop=\"static\" role=\"dialog\" aria-hidden=\"true\">";
        $html .= "\n\t<div class=\"modal-dialog modals-default\">";
        $html .= "\n\t\t<div class=\"modal-content\">";
        $html .= "\n\t\t\t<div class=\"modal-header form-group-15\">";
        $html .= "\n\t\t\t\t<button type=\"button\" class=\"close\" data-dismiss=\"modal\">&times;</button>";
        $html .= "\n\t\t\t\t<h4 class=\"modal-title\">New " . $data['menu'] . "</h4>";
        $html .= "\n\t\t\t</div>";
        $html .= "\n\t\t\t<div class=\"modal-body\">";
        $html .= "\n\t\t\t\t<form id=\"formEditor\" data-parsley-validate class=\"form-horizontal form-label-left\">";

        /* Generate select if relation */
        $html .= (isset($fmSelect)) ? $fmSelect : '';

        /* Generate Input Field */
        foreach ($data['describe'] as $desc) {
            /* input name */
            $nama = strtolower($desc['Field']);
            $label = ucwords(str_replace("_", " ", $nama));
            $isNull = (strtoupper(trim($desc['Null'])) == 'NO') ? "required=\"required\"" : "";
            $spanReq = (strtoupper(trim($desc['Null'])) == 'NO') ? "<span class=\"required\">*</span>" : "";

            /* Set input type */
            if (Stringer::is_like(['char'], $desc['Type'])) {
                $tipe = "<input type=\"text\" class=\"form-control input-sm\" name=\"" . $nama . "\" placeholder=\"" . $label . "\" data-parsley-group=\"role\" " . $isNull . ">";
            } elseif (Stringer::is_like(['int', 'decimal', 'float'], $desc['Type'])) {
                $tipe = "<input type=\"number\" class=\"form-control input-sm\" name=\"" . $nama . "\" placeholder=\"" . $label . "\" data-parsley-group=\"role\" " . $isNull . ">";
            } elseif (Stringer::is_like(['datetime', 'timestamp'], $desc['Type'])) {
                $tipe = "<input type=\"datetime-local\" class=\"form-control input-sm\" name=\"" . $nama . "\" placeholder=\"mm/dd/yyyy 12:59 AM\" data-parsley-group=\"role\" " . $isNull . ">";
            } elseif (Stringer::is_like(['date'], $desc['Type'])) {
                $tipe = "<input type=\"date\" class=\"form-control input-sm\" name=\"" . $nama . "\" placeholder=\"mm/dd/yyyy\" data-parsley-group=\"role\" " . $isNull . ">";
            } elseif (Stringer::is_like(['time'], $desc['Type'])) {
                $tipe = "<input type=\"time\" class=\"form-control input-sm\" name=\"" . $nama . "\" placeholder=\"12:59 AM\" data-parsley-group=\"role\" " . $isNull . ">";
            } elseif (Stringer::is_like(['text'], $desc['Type'])) {
                $tipe = "<textarea class=\"form-control auto-size\" rows=\"2\" placeholder=\"Input " . $label . " here...\" name=\"" . $nama . "\" " . $isNull . "></textarea>";
            }

            /* Skip if PrimaryKey */
            if ($desc['Key'] != 'PRI') {
                $html .= "\n\t\t\t\t\t<div class=\"form-group-15\">";
                $html .= "\n\t\t\t\t\t\t<label>" . $label . $spanReq . "</label>";
                $html .= "\n\t\t\t\t\t\t<div class=\"nk-int-st\">";
                $html .= "\n\t\t\t\t\t\t\t" . $tipe;
                $html .= "\n\t\t\t\t\t\t</div>";
                $html .= "\n\t\t\t\t\t</div>";
            }
        }

        $html .= "\n\t\t\t\t</form>";
        $html .= "\n\t\t\t</div>";
        $html .= "\n\t\t\t<div class=\"modal-footer\">";
        $html .= "\n\t\t\t\t<button type=\"button\" class=\"btn_save btn btn-default notika-btn-default waves-effect\" data-loading-text=\"Loading...\">";
        $html .= "\n\t\t\t\t\t<i class=\"fa fa-save\"></i> Save";
        $html .= "\n\t\t\t\t</button>";
        $html .= "\n\t\t\t\t<button type=\"button\" class=\"btn_cancel btn btn-danger notika-btn-danger waves-effect\" data-dismiss=\"modal\">";
        $html .= "\n\t\t\t\t\t<i class=\"fa fa-times\"></i> Close";
        $html .= "\n\t\t\t\t</button>";
        $html .= "\n\t\t\t</div>";
        $html .= "\n\t\t</div>";
        $html .= "\n\t</div>";
        $html .= "\n</div>";
        $html .= "\n<!-- #endregion modal -->";
        //-->

        return $html;
    }

    /**
     * Javascript Code Generator
     *
     * @param array $data
     * @return string
     */
    public function stringJS($data = [])
    {
        /* Get Relation */
        if (!empty($data['relation']['relation'])) {
            $postOpt = [];
            $getSelect = [];
            $jsPopulate = null;
            $changeSelect = [];

            foreach ($data['relation']['relation'] as $col) {
                $isChar = (empty($col['IS_CHAR']) ? $col['REFERENCED_COLUMN_NAME'] : $col['IS_CHAR']);
                $jsPopulate .= "\n/* Get " . $col['REFERENCED_TABLE_NAME'] . " */";
                $jsPopulate .= "\nfunction get" . $col['REFERENCED_TABLE_NAME'] . "(obj, sel = null) {";
                $jsPopulate .= "\n\tlet opt = \$(obj);";
                $jsPopulate .= "\n\tlet url = SiteRoot + '" . $col['REFERENCED_TABLE_NAME'] . "/read';";
                $jsPopulate .= "\n\tlet post_data = {";
                $jsPopulate .= "\n\t\t'draw': 1,";
                $jsPopulate .= "\n\t\t'start': 0,";
                $jsPopulate .= "\n\t\t'length': -1, /* All data */";
                $jsPopulate .= "\n\t\t'search[value]': ''";
                $jsPopulate .= "\n\t}";
                $jsPopulate .= "\n\tpopulateSelect(url, opt, post_data, sel, '" . $col['REFERENCED_COLUMN_NAME'] . "', '" . $isChar . "');";
                $jsPopulate .= "\n};\n";

                $getSelect[] = "get" . $col['REFERENCED_TABLE_NAME'] . "('#" . $col['REFERENCED_COLUMN_NAME'] . "', '0');";
                $changeSelect[] = "select[name=" . $col['REFERENCED_TABLE_NAME'] . "]";
                $postOpt[] = "'" . $col['REFERENCED_COLUMN_NAME'] . "': \$(\"select[name=" . $col['REFERENCED_TABLE_NAME'] . "]\").val(),";
            }
        }

        /* JS String start */
        $js = "/* Variables */;";
        $js .= "\nvar apiUrl = SiteRoot + '" . $data['url'] . "';";
        $js .= "\nvar tbl = \"#datatable-responsive tbody\";";
        $js .= "\nvar pKey, table;";
        $js .= "\nvar saveUpdate = \"save\";\n";

        //start DocumentReady
        $js .= "\n/* Document Ready */";
        $js .= "\n\$(document).ready(function() {";
        $js .= "\n\t/* First Event Load */";
        $js .= "\n\tvar enterBackspace = true;\n";

        /* Call populateSelect */
        if (isset($getSelect) && !empty($getSelect)) {
            foreach ($getSelect as $selOpt) {
                $js .= "\n\t" . str_replace(['#', ", '0'"], ['.', ''], $selOpt);
            }
            $js .= "\n\n";
        }

        /* Generate enterKey */
        $count = count($data['describe']);
        $i = 0;
        foreach ($data['describe'] as $desc) {
            /* input name */
            $nama = strtolower($desc['Field']);
            if ($desc['Key'] == 'PRI') {
                $count = $count - 1;
            }

            /* Set input type */
            if (Stringer::is_like(['char', 'int', 'decimal', 'float', 'date'], $desc['Type'])) {
                $tipe = "input[name=" . $nama . "]";
            } elseif (Stringer::is_like(['text'], $desc['Type'])) {
                $tipe = "textarea[name=" . $nama . "]";
            }

            /* Skip if PrimaryKey */
            if ($desc['Key'] != 'PRI') {
                /* Event Save on enter input */
                $i++;
                if ($i === $count) {
                    $js .= "\t\$(\"" . $tipe . "\").enterKey(function (e) {";
                    $js .= "\n\t\te.preventDefault();";
                    $js .= "\n\t\t\$(\".btn_save\").click();";
                    $js .= "\n\t});\n";
                }
            }
        }

        $js .= "\n\t/* Datatables set_token */";
        $js .= "\n\t\$(\"#datatable-responsive\").on('xhr.dt', function(e, settings, json, jqXHR){";
        $js .= "\n\t\tredirectLogin(jqXHR);";
        $js .= "\n\t\tset_token(API_TOKEN, jqXHR.getResponseHeader('JWT'));";
        $js .= "\n\t});\n";

        $js .= "\n\t/* Datatables handler */";
        $js .= "\n\ttable = \$(\"#datatable-responsive\").DataTable({";
        $js .= "\n\t\tautoWidth: false,";
        $js .= "\n\t\tlanguage: {";

        $js .= "\n\t\t\t\"emptyTable\": \"Tidak ada data yang tersedia\",";
        $js .= "\n\t\t\t\"zeroRecords\": \"Maaf, pencarian Anda tidak ditemukan\",";
        $js .= "\n\t\t\t\"info\": \"Menampilkan _START_ - _END_ dari _TOTAL_ data\",";
        $js .= "\n\t\t\t\"infoEmpty\": \"Menampilkan 0 - 0 dari 0 data\",";
        $js .= "\n\t\t\t\"infoFiltered\": \"(terfilter dari _MAX_ total data)\",";
        $js .= "\n\t\t\t\"searchPlaceholder\": \"Enter untuk mencari\"";

        $js .= "\n\t\t},";
        $js .= "\n\t\t\"dom\": \"<'row'<'col-sm-8'B><'col-sm-4'f>><'row'<'col-sm-12't>><'row'<'col-sm-4'<'pull-left' p>><'col-sm-8'<'pull-right' i>>>\",";

        //start button
        $js .= "\n\t\t\"buttons\": [";
        $js .= "\n\t\t\t{";
        $js .= "\n\t\t\t\textend: \"pageLength\",";
        $js .= "\n\t\t\t\tclassName: \"btn-sm bt-separ\"";
        $js .= "\n\t\t\t},";
        $js .= "\n\t\t\t{";
        $js .= "\n\t\t\t\ttext: \"<i id='dtSpiner' class='fa fa-refresh fa-spin'></i> <span id='tx_dtSpiner'>Reload</span>\",";
        $js .= "\n\t\t\t\tclassName: \"btn-sm btReload\",";
        $js .= "\n\t\t\t\ttitleAttr: \"Reload Data\",";
        $js .= "\n\t\t\t\taction: function() {";
        $js .= "\n\t\t\t\t\tdtReload(table);";
        $js .= "\n\t\t\t\t}";
        $js .= "\n\t\t\t},";

        /* Export PDF */
        $js .= "\n\t\t\t{";
        $js .= "\n\t\t\t\ttext: \"<i class='fa fa-file-pdf-o'></i>\",";
        $js .= "\n\t\t\t\tclassName: \"btn-sm\",";
        $js .= "\n\t\t\t\textend: \"pdfHtml5\",";
        $js .= "\n\t\t\t\ttitleAttr: \"Export PDF\",";
        $js .= "\n\t\t\t\tdownload: \"open\",";
        $js .= "\n\t\t\t\tpageSize: \"LEGAL\",";
        $js .= "\n\t\t\t\torientation: \"portrait\", /* portrait | landscape */";
        $js .= "\n\t\t\t\ttitle: function () { return '" . $data['menu'] . "';},";
        $js .= "\n\t\t\t\texportOptions: {";
        $js .= "\n\t\t\t\t\t/* Show column */";
        $js .= "\n\t\t\t\t\tcolumns: \":visible\" /* [1, 2, 3] => selected column only */";
        $js .= "\n\t\t\t\t},";
        $js .= "\n\t\t\t\tcustomize: function (doc) {";
        $js .= "\n\t\t\t\t\t/* Set Default Table Header Alignment */";
        $js .= "\n\t\t\t\t\tdoc.styles.tableHeader.alignment = 'left';\n";
        $js .= "\n\t\t\t\t\t/* Set table width each column */";
        $js .= "\n\t\t\t\t\tdoc.content[1].table.widths = Array(doc.content[1].table.body[0].length + 1).join('*').split(''); /* ['*', '15%', 'auto'] => each column width*/\n";
        $js .= "\n\t\t\t\t\t/* Set column alignment */";
        $js .= "\n\t\t\t\t\tvar rowCount = doc.content[1].table.body.length;";
        $js .= "\n\t\t\t\t\tfor (i = 0; i < rowCount; i++) {";
        $js .= "\n\t\t\t\t\t\tdoc.content[1].table.body[i][0].alignment = \"right\"; /* 1st column align right */";
        $js .= "\n\t\t\t\t\t};";
        $js .= "\n\t\t\t\t}";
        $js .= "\n\t\t\t},";
        //-->

        /* Create */
        $js .= "\n\t\t\t{";
        $js .= "\n\t\t\t\ttext: \"<i class='fa fa-plus-circle'></i>\",";
        $js .= "\n\t\t\t\tclassName: \"btn-sm btn-primary btn_add hidden\",";
        $js .= "\n\t\t\t\ttitleAttr: \"Create New\",";
        $js .= "\n\t\t\t\taction: function() {";
        $js .= "\n\t\t\t\t\tbtn_add();";
        $js .= "\n\t\t\t\t}";
        $js .= "\n\t\t\t},";
        //-->

        /* Delete */
        $js .= "\n\t\t\t{";
        $js .= "\n\t\t\t\ttext: \"<i class='fa fa-trash'></i>\",";
        $js .= "\n\t\t\t\tclassName: \"btn-sm btn-danger btDels act-delete hidden\",";
        $js .= "\n\t\t\t\ttitleAttr: \"Multiple Delete\",";
        $js .= "\n\t\t\t},";
        //-->

        $js .= "\n\t\t],";
        //End button

        $js .= "\n\t\t\"pagingType\": \"numbers\",";
        $js .= "\n\t\t\"lengthMenu\": [";
        $js .= "\n\t\t\t[10, 25, 50, 100, -1],";
        $js .= "\n\t\t\t[10, 25, 50, 100, 'All']";
        $js .= "\n\t\t],";
        $js .= "\n\t\t\"responsive\": true,";
        $js .= "\n\t\t\"processing\": false,";
        $js .= "\n\t\t\"ordering\": false,";
        $js .= "\n\t\t\"serverSide\": true,";
        $js .= "\n\t\t\"ajax\": {";
        $js .= "\n\t\t\t\"url\": apiUrl + '/read',";
        $js .= "\n\t\t\t\"type\": 'post',";
        $js .= "\n\t\t\t\"headers\": { Authorization: \"Bearer \" + get_token(API_TOKEN) },";
        $js .= "\n\t\t\t\"data\": function(data, settings){";
        $js .= "\n\t\t\t\t/* start_loader */";
        $js .= "\n\t\t\t\t\$(\".cssload-loader\").hide();";
        $js .= "\n\t\t\t\t\$(\"#dtableDiv\").fadeIn(\"slow\");";
        $js .= "\n\t\t\t\t\$(\"a.btn.btn-default.btn-sm\").addClass('disabled');";
        $js .= "\n\t\t\t\t\$(\"#tx_dtSpiner\").text('Please wait...');";
        $js .= "\n\t\t\t\t\$(\"#dtSpiner\").removeClass('pause-spinner');\n";

        //start combobox optional
        if (isset($postOpt) && !empty($postOpt)) {
            $optIdx = 0;
            $optCtr = count($postOpt) - 1;
            $js .= "\n\t\t\t\t/* Post Data */";
            $js .= "\n\t\t\t\tdata.opsional = {";
            foreach ($postOpt as $opsional) {
                $js .= "\n\t\t\t\t\t" . ($optCtr == $optIdx ? trim($opsional, ',') : $opsional);
                $optIdx++;
            }
            $js .= "\n\t\t\t\t};\n";
        }
        //end combobox

        $js .= "\n\t\t\t},";
        $js .= "\n\t\t\t\"dataSrc\": function(json) {";

        //start json reordering
        $js .= "\n\t\t\t\t/* return variable */";
        $js .= "\n\t\t\t\tvar return_data = [];";
        $js .= "\n\t\t\t\tif (json.success === true) {";
        $js .= "\n\t\t\t\t\t/* Redraw json result */";
        $js .= "\n\t\t\t\t\tjson.draw = json.message.draw;";
        $js .= "\n\t\t\t\t\tjson.recordsFiltered = json.message.recordsFiltered;";
        $js .= "\n\t\t\t\t\tjson.recordsTotal = json.message.recordsTotal;\n";
        $js .= "\n\t\t\t\t\t/* ReOrdering json result */";
        $js .= "\n\t\t\t\t\tfor (var i = 0; i < json.message.data.length; i++) {";
        $js .= "\n\t\t\t\t\t\treturn_data.push({";

        foreach ($data['dtcols'] as $idx => $cols) {
            if ($idx == 0) {
                $js .= "\n\t\t\t\t\t\t\t$idx: json.message.data[i].$cols,";
            } elseif ($idx == 1) {
                $js .= "\n\t\t\t\t\t\t\t$idx: json.message.data[i].no,";
                $js .= "\n\t\t\t\t\t\t\t" . ($idx + 1) . ": json.message.data[i].$cols,";
            } elseif ($idx > 1) {
                $js .= "\n\t\t\t\t\t\t\t" . ($idx + 1) . ": json.message.data[i].$cols,";
            }
        }

        $js .= "\n\t\t\t\t\t\t})";
        $js .= "\n\t\t\t\t\t}";
        $js .= "\n\t\t\t\t\treturn return_data;";
        $js .= "\n\t\t\t\t} else {";
        $js .= "\n\t\t\t\t\tjson.draw = null;";
        $js .= "\n\t\t\t\t\tjson.recordsFiltered = null;";
        $js .= "\n\t\t\t\t\tjson.recordsTotal = null;";
        $js .= "\n\t\t\t\t\treturn_data = [];";
        $js .= "\n\t\t\t\t\tnotification(json.error, 'warn', 2, json.message);";
        $js .= "\n\t\t\t\t\treturn return_data;";
        $js .= "\n\t\t\t\t}";
        $js .= "\n\t\t\t},";
        $js .= "\n\t\t\t\"error\": function (jqXHR, textStatus, errorThrown) {";
        $js .= "\n\t\t\t\tnotification(jqXHR . responseJSON . error, 'error', 3, 'ERROR');";
        $js .= "\n\t\t\t\tconsole.log(jqXHR);";
        $js .= "\n\t\t\t\tconsole.log(textStatus);";
        $js .= "\n\t\t\t\tconsole.log(errorThrown);";
        $js .= "\n\t\t\t}";
        $js .= "\n\t\t},";
        //end json reordering

        //start column definition
        $js .= "\n\t\t\"deferRender\": true,";
        $js .= "\n\t\t\"columnDefs\": [";
        $js .= "\n\t\t\t{";
        $js .= "\n\t\t\t\t\"targets\": 0,";
        $js .= "\n\t\t\t\t\"className\": \"select-checkbox\",";
        $js .= "\n\t\t\t\t\"checkboxes\": {";
        $js .= "\n\t\t\t\t\t\"selectRow\": true";
        $js .= "\n\t\t\t\t},";
        $js .= "\n\t\t\t\t\"render\": function () {";
        $js .= "\n\t\t\t\t\treturn '';";
        $js .= "\n\t\t\t\t}";
        $js .= "\n\t\t\t},";
        $js .= "\n\t\t\t{";
        $js .= "\n\t\t\t\t\"targets\": 1,";
        $js .= "\n\t\t\t\t\"className\": \"dt-center\",";
        $js .= "\n\t\t\t},";
        $js .= "\n\t\t\t{";
        $js .= "\n\t\t\t\t\"targets\": -1,";
        $js .= "\n\t\t\t\t\"className\": \"dt-center\",";
        $js .= "\n\t\t\t\t\"data\": null,";
        $js .= "\n\t\t\t\t\"defaultContent\":";
        $js .= "\n\t\t\t\t\t'<span class=\"button-icon-btn button-icon-btn-cl sm-res-mg-t-30\"><button title=\"Edit\" id=\"btEdit\" class=\"hidden btn-act act-edit btn btn-warning warning-icon-notika btn-reco-mg btn-button-mg waves-effect btn-xs\" type=\"button\"><i class=\"notika-icon notika-draft\"></i></button></span>' +";
        $js .= "\n\t\t\t\t\t'<span class=\"button-icon-btn button-icon-btn-cl sm-res-mg-t-30\"><button title=\"Delete\" id=\"btDel\" class=\"hidden btn-act act-delete btn btn-danger danger-icon-notika btn-reco-mg btn-button-mg waves-effect btn-xs\" type=\"button\"><i class=\"notika-icon notika-close\"></i></button></span>'";
        $js .= "\n\t\t\t}";
        $js .= "\n\t\t],";
        $js .= "\n\t\t\"select\": {";
        $js .= "\n\t\t\t\"style\": \"multi\",";
        $js .= "\n\t\t\t\"selector\": \"td:first-child\",";
        $js .= "\n\t\t}";
        $js .= "\n\t}).on('draw', function() {\n";
        $js .= "\n\t\t/* stop_loader */\n";
        $js .= "\n\t\tcheckAuth(function(){\n";
        $js .= "\n\t\t\t\$(\"#tx_dtSpiner\").text('Reload');\n";
        $js .= "\n\t\t\t\$(\"#dtSpiner\").addClass('pause-spinner');\n";
        $js .= "\n\t\t\t\$(\"a.btn.btn-default.btn-sm\").removeClass('disabled');\n";
        $js .= "\n\t\t\tsetNprogressLoader(\"done\");\n";
        $js .= "\n\t\t});\n";
        $js .= "\n\t});\n";
        //end column definition

        //enterAndSearch
        $js .= "\n\t/* DataTable search on enter */";
        $js .= "\n\tenterAndSearch(table, '#datatable-responsive', enterBackspace)\n";
        //-->

        /* Create */
        $js .= "\n\t/* Button Save Action */";
        $js .= "\n\t\$('.btn_save').on('click', function() {";
        $js .= "\n\t\tsaveOrUpdate(saveUpdate, apiUrl, pKey, '.formEditorModal:#formEditor');";
        $js .= "\n\t});\n";
        //-->

        /* Update */
        $js .= "\n\t/* Button Edit Action */";
        $js .= "\n\t\$(tbl).on( 'click', '#btEdit', function () {";
        $js .= "\n\t\tsaveUpdate = 'update';";
        $js .= "\n\t\tlet data = (table.row($(this).closest('tr')).data() === undefined) ? table.row($(this).closest('li')).data() : table.row($(this).closest('tr')).data();";
        $js .= "\n\t\tpKey = data[0];\n";
        $js .= "\n\t\t/* Set Edit Form Value */";

        foreach ($data['dtcols'] as $idx => $cols) {
            if ($idx >= 1) {
                if (Stringer::is_like(['date'], $data['describe'][$idx]['Type'])) {
                    $js .= "\n\t\t\$(\"input[name=$cols]\").val(data[" . ($idx + 1) . "]);";
                } elseif (Stringer::is_like(['time'], $data['describe'][$idx]['Type'])) {
                    $js .= "\n\t\t\$(\"input[name=$cols]\").val(data[" . ($idx + 1) . "]);";
                } elseif (Stringer::is_like(['timestamp', 'datetime'], $data['describe'][$idx]['Type'])) {
                    $js .= "\n\t\t\$(\"input[name=$cols]\").val(data[" . ($idx + 1) . "]);";
                } elseif (Stringer::is_like(['char', 'int', 'decimal', 'float'], $data['describe'][$idx]['Type'])) {
                    $js .= "\n\t\t\$(\"input[name=$cols]\").val(data[" . ($idx + 1) . "]);";
                } elseif (Stringer::is_like(['text'], $data['describe'][$idx]['Type'])) {
                    $js .= "\n\t\t\$(\"textarea[name=$cols]\").val(data[" . ($idx + 1) . "]);";
                } else {
                    $js .= "\n\t\t\$(\"input[name=$cols]\").val(data[" . ($idx + 1) . "]);";
                }
            }
        }

        $js .= "\n\t\t\$('.btn_save').html('<i class=\"fa fa-save\"></i> Update');";
        $js .= "\n\t\t\$('.modal-title').html('Edit " . $data['menu'] . "');";
        $js .= "\n\t\t\$('.formEditorModal').modal();";
        $js .= "\n\t});\n";
        //-->

        /* Delete Single */
        $js .= "\n\t/* Button Delete */";
        $js .= "\n\t\$(tbl).on( 'click', '#btDel', function () {";
        $js .= "\n\t\tlet data = (table.row($(this).closest('tr')).data() === undefined) ? table.row($(this).closest('li')).data() : table.row($(this).closest('tr')).data();";
        $js .= "\n\t\tdeleteSingle(apiUrl, data[0], data[2]);";
        $js .= "\n\t});\n";
        //-->

        /* Delete Multiple */
        $js .= "\n\t/* Button Delete Multi */";
        $js .= "\n\t\$('.btDels').on( 'click', function () {";
        $js .= "\n\t\tlet rows_selected = table.column(0).checkboxes.selected();";
        $js .= "\n\t\tdeleteMultiple(apiUrl, table, rows_selected);";
        $js .= "\n\t});";
        //-->

        $js .= "\n});\n";
        //<-- end DocumentReady

        /* populateSelect */
        $js .= isset($jsPopulate) ? $jsPopulate : '';

        /* changeSelect Function */
        if (isset($changeSelect) && !empty($changeSelect)) {
            foreach ($changeSelect as $csl) {
                $js .= "\n/* Filter " . $data['menu'] . " Event Change */";
                $js .= "\n\$(\"" . $csl . "\").on('change', function() {";
                $js .= "\n\t\$(\".btReload\").click();";
                $js .= "\n});\n";
            }
        }

        /* Action Button Create */
        $js .= "\n/* Button Create Action */";
        $js .= "\nfunction btn_add() {";
        $js .= "\n\tid = '';";
        $js .= "\n\tsaveUpdate = 'save';";

        /* Call populateSelect */
        if (isset($getSelect) && !empty($getSelect)) {
            foreach ($getSelect as $selOpt) {
                $js .= "\n\t" . $selOpt;
            }
        }

        $js .= "\n\t\$('.btn_save').html('<i class=\"fa fa-save\"></i> Save');";
        $js .= "\n\t\$('.modal-title').html('New " . $data['menu'] . "');";
        $js .= "\n\t\$('.formEditorModal form')[0].reset();";
        $js .= "\n\t$('.formEditorModal').modal();";
        $js .= "\n};\n";

        /* Modal Show */
        $js .= "\n/* Modal on show */";
        $js .= "\n\$('.formEditorModal').on('shown.bs.modal', function () {";
        $js .= "\n\t/* code */";

        /* Generate onFocus */
        $j = 0;
        foreach ($data['describe'] as $desc) {
            /* input name */
            $nama = strtolower($desc['Field']);

            /* Set input type */
            if (Stringer::is_like(['char', 'int', 'decimal', 'float', 'date', 'time'], $desc['Type'])) {
                $tipe = "input[name=" . $nama . "]";
            } elseif (Stringer::is_like(['text'], $desc['Type'])) {
                $tipe = "textarea[name=" . $nama . "]";
            }
            /* Skip if PrimaryKey */
            if ($desc['Key'] != 'PRI') {
                /* Set focus on modal show */
                if ($j === 0) {
                    $js .= "\n\t\$(\"" . $tipe . "\").focus();";
                }
                $j++;
            }
        }

        $js .= "\n});\n";

        /* Modal Close */
        $js .= "\n/* Modal on dissmis */";
        $js .= "\n\$('.formEditorModal').on('hide.bs.modal', function() {";
        $js .= "\n\t/* code */";
        $js .= "\n\t$(\"form#formEditor\").parsley().reset();";
        $js .= "\n});\n";

        /* Return String JS */
        return $js;
    }
}
