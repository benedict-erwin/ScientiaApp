/*
 * Javascript Function Library
 * ------------------
 * Store your reusable js function here
 *
 */

var NP_STATUS = 0;

/* Post with invisible form */
/* Ex: goPost('postFile.php', {key:'value'}) */
function goPost(path, params, method, target) {
    method = method || "post"; /* Set method to post by default if not specified. */
    target = target || "_self"; /* Set method to post by default if not specified. */

    var form = document.createElement("form");
    form.setAttribute("method", method);
    form.setAttribute("action", path);
    form.setAttribute("target", target);

    for (var key in params) {
        if (params.hasOwnProperty(key)) {
            var hiddenField = document.createElement("input");
            hiddenField.setAttribute("type", "hidden");
            hiddenField.setAttribute("name", key);
            hiddenField.setAttribute("value", params[key]);

            form.appendChild(hiddenField);
        }
    }

    document.body.appendChild(form);
    form.submit();
}

/* Enter event */
$.fn.enterKey = function (fnc) {
    return this.each(function () {
        $(this).on('keypress', function(event){
            event = event || window.event; /* For IE */
            var keycode = ((event.keyCode) ? event.keyCode : event.which);
            if (keycode === 13) {
                fnc.call(this, event);
            }
        })
    })
}

/* JS StrToLower */
function strtolower(str) {
    return (str + '').toLowerCase();
}

/* JS UCWord */
function ucwords(str) {
    return (str + '').replace(/^([a-z\u00E0-\u00FC])|\s+([a-z\u00E0-\u00FC])/g, function ($1) {
        return $1.toUpperCase();
    });
}

/* JS StrToUpper */
function strtoupper(str) {
    return (str + '')
        .toUpperCase();
}

/* JS UCFirst */
function ucfirst(str) {
    str += ''
    var f = str.charAt(0)
        .toUpperCase()
    return f + str.substr(1);
}

/* Random number with range
 * Usage:
 *     var ran = getRandomizer(0, 5);
 * alert(ran());
 */
function getRandomizer(bottom, top) {
    return function () {
        return Math.floor(Math.random() * (1 + top - bottom)) + bottom;
    }
}

/* Force exit function */
function javascript_abort() {
    throw new Error('This is not an error. This is just to abort javascript');
}

/* Preview image without upload
 * example:
 * $("#txGbr").change(function(){
 *    preViewImage(this,'#imgPreview');
 * });
 */
function preViewImage(input, preview) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();

        reader.onload = function (e) {
            $(preview).css('display', 'block');
            $(preview).attr('src', e.target.result);
        }

        reader.readAsDataURL(input.files[0]);
    }
}

function resetPreviewImage(preview) {
    $(preview).css('display', 'none');
    $(preview).attr('src', null);
}

/* Window open center screen
 * usage:
 * PopupCenter('http://www.xtf.dk','xtf','900','500');
 */
function PopupCenter(url, title, w, h) {
    /* Fixes dual-screen position    Most browsers Firefox */
    var dualScreenLeft = window.screenLeft != undefined ? window.screenLeft : screen.left;
    var dualScreenTop = window.screenTop != undefined ? window.screenTop : screen.top;

    var left = ((screen.width / 2) - (w / 2)) + dualScreenLeft;
    var top = ((screen.height / 2) - (h / 2)) + dualScreenTop;
    var newWindow = window.open(url, title, 'scrollbars=yes, width=' + w + ', height=' + h + ', top=' + top + ', left=' + left);

    /* Puts focus on the newWindow */
    if (window.focus) {
        newWindow.focus();
    }
}

/* Number Format */
function number_format(number, decimals, dec_point, thousands_sep) {
    /* http://kevin.vanzonneveld.net */
    /* +     original by: Jonas Raoni Soares Silva (http://www.jsfromhell.com) */
    number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
    var n = !isFinite(+number) ? 0 : +number,
        prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
        sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
        dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
        s = '',
        toFixedFix = function (n, prec) {
            var k = Math.pow(10, prec);
            return '' + Math.round(n * k) / k;
        };
    /* Fix for IE parseFloat(0.55).toFixed(0) = 0; */
    s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
    if (s[0].length > 3) {
        s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
    }
    if ((s[1] || '').length < prec) {
        s[1] = s[1] || '';
        s[1] += new Array(prec - s[1].length + 1).join('0');
    }
    return s.join(dec);
}


/* Round PHP Function */
function round(value, precision, mode) {
    /* http://kevin.vanzonneveld.net */
    /* +     original by: Philip Peterson */
    var m, f, isHalf, sgn; /* helper variables */
    precision |= 0; /* making sure precision is integer */
    m = Math.pow(10, precision);
    value *= m;
    sgn = (value > 0) | -(value < 0); /* sign of the number */
    isHalf = value % 1 === 0.5 * sgn;
    f = Math.floor(value);

    if (isHalf) {
        switch (mode) {
            case 'PHP_ROUND_HALF_DOWN':
                value = f + (sgn < 0); /* rounds .5 toward zero */
                break;
            case 'PHP_ROUND_HALF_EVEN':
                value = f + (f % 2 * sgn); /* rouds .5 towards the next even integer */
                break;
            case 'PHP_ROUND_HALF_ODD':
                value = f + !(f % 2); /* rounds .5 towards the next odd integer */
                break;
            default:
                value = f + (sgn > 0); /* rounds .5 away from zero */
        }
    }

    return (isHalf ? value : Math.round(value)) / m;
}

/* Validate range beetwen 2 dates */
function checkDate(dt1, dt2) {
    /* date 1 tgl-bln-thn */
    var spl1 = dt1.split("-");
    var bln = spl1[1];
    var tgl = spl1[0];
    var thn = spl1[2];
    var date1 = bln + "/" + tgl + "/" + thn;
    var ndt1 = new Date(date1);

    /* date 2 tgl-bln-thn */
    var spl2 = dt2.split("-");
    var bln2 = spl2[1];
    var tgl2 = spl2[0];
    var thn2 = spl2[2];
    var date2 = bln2 + "/" + tgl2 + "/" + thn2;
    var ndt2 = new Date(date2);

    /* doing check */
    if (ndt1 < ndt2) {
        return true;
    } else {
        alert("Range Periode salah\nCek Kembali range periode Anda.");
        exit();
    }
}

/* Set Focus pointer @Last Char */
function focusLastChar(id) {
    var inputField = document.getElementById(id);
    if (inputField != null && inputField.value.length != 0) {
        if (inputField.createTextRange) {
            var FieldRange = inputField.createTextRange();
            FieldRange.moveStart('character', inputField.value.length);
            FieldRange.collapse();
            FieldRange.select();
        } else if (inputField.selectionStart || inputField.selectionStart == '0') {
            var elemLen = inputField.value.length;
            inputField.selectionStart = elemLen;
            inputField.selectionEnd = elemLen;
            inputField.focus();
        }
    } else {
        inputField.focus();
    }
}

/* Function var_dump like PHP */
function var_dump() {
    /* discuss at: http://phpjs.org/functions/var_dump/ */
    /* original by: Brett Zamir (http://brett-zamir.me )*/

    var output = '',
        pad_char = ' ',
        pad_val = 4,
        lgth = 0,
        i = 0;

    var _getFuncName = function (fn) {
        var name = (/\W*function\s+([\w\$]+)\s*\(/)
            .exec(fn);
        if (!name) {
            return '(Anonymous)';
        }
        return name[1];
    };

    var _repeat_char = function (len, pad_char) {
        var str = '';
        for (var i = 0; i < len; i++) {
            str += pad_char;
        }
        return str;
    };
    var _getInnerVal = function (val, thick_pad) {
        var ret = '';
        if (val === null) {
            ret = 'NULL';
        } else if (typeof val === 'boolean') {
            ret = 'bool(' + val + ')';
        } else if (typeof val === 'string') {
            ret = 'string(' + val.length + ') "' + val + '"';
        } else if (typeof val === 'number') {
            if (parseFloat(val) == parseInt(val, 10)) {
                ret = 'int(' + val + ')';
            } else {
                ret = 'float(' + val + ')';
            }
        }
        /* The remaining are not PHP behavior because these values only exist in this exact form in JavaScript */
        else if (typeof val === 'undefined') {
            ret = 'undefined';
        } else if (typeof val === 'function') {
            var funcLines = val.toString()
                .split('\n');
            ret = '';
            for (var i = 0, fll = funcLines.length; i < fll; i++) {
                ret += (i !== 0 ? '\n' + thick_pad : '') + funcLines[i];
            }
        } else if (val instanceof Date) {
            ret = 'Date(' + val + ')';
        } else if (val instanceof RegExp) {
            ret = 'RegExp(' + val + ')';
        } else if (val.nodeName) { /* Different than PHP's DOMElement */
            switch (val.nodeType) {
                case 1:
                    if (typeof val.namespaceURI === 'undefined' || val.namespaceURI === 'http://www.w3.org/1999/xhtml') { /* Undefined namespace could be plain XML, but namespaceURI not widely supported */
                        ret = 'HTMLElement("' + val.nodeName + '")';
                    } else {
                        ret = 'XML Element("' + val.nodeName + '")';
                    }
                    break;
                case 2:
                    ret = 'ATTRIBUTE_NODE(' + val.nodeName + ')';
                    break;
                case 3:
                    ret = 'TEXT_NODE(' + val.nodeValue + ')';
                    break;
                case 4:
                    ret = 'CDATA_SECTION_NODE(' + val.nodeValue + ')';
                    break;
                case 5:
                    ret = 'ENTITY_REFERENCE_NODE';
                    break;
                case 6:
                    ret = 'ENTITY_NODE';
                    break;
                case 7:
                    ret = 'PROCESSING_INSTRUCTION_NODE(' + val.nodeName + ':' + val.nodeValue + ')';
                    break;
                case 8:
                    ret = 'COMMENT_NODE(' + val.nodeValue + ')';
                    break;
                case 9:
                    ret = 'DOCUMENT_NODE';
                    break;
                case 10:
                    ret = 'DOCUMENT_TYPE_NODE';
                    break;
                case 11:
                    ret = 'DOCUMENT_FRAGMENT_NODE';
                    break;
                case 12:
                    ret = 'NOTATION_NODE';
                    break;
            }
        }
        return ret;
    };

    var _formatArray = function (obj, cur_depth, pad_val, pad_char) {
        var someProp = '';
        if (cur_depth > 0) {
            cur_depth++;
        }

        var base_pad = _repeat_char(pad_val * (cur_depth - 1), pad_char);
        var thick_pad = _repeat_char(pad_val * (cur_depth + 1), pad_char);
        var str = '';
        var val = '';

        if (typeof obj === 'object' && obj !== null) {
            if (obj.constructor && _getFuncName(obj.constructor) === 'PHPJS_Resource') {
                return obj.var_dump();
            }
            lgth = 0;
            for (someProp in obj) {
                lgth++;
            }
            str += 'array(' + lgth + ') {\n';
            for (var key in obj) {
                var objVal = obj[key];
                if (typeof objVal === 'object' && objVal !== null && !(objVal instanceof Date) && !(objVal instanceof RegExp) && !
                    objVal.nodeName) {
                    str += thick_pad + '[' + key + '] =>\n' + thick_pad + _formatArray(objVal, cur_depth + 1, pad_val,
                        pad_char);
                } else {
                    val = _getInnerVal(objVal, thick_pad);
                    str += thick_pad + '[' + key + '] =>\n' + thick_pad + val + '\n';
                }
            }
            str += base_pad + '}\n';
        } else {
            str = _getInnerVal(obj, thick_pad);
        }
        return str;
    };

    output = _formatArray(arguments[0], 0, pad_val, pad_char);
    for (i = 1; i < arguments.length; i++) {
        output += '\n' + _formatArray(arguments[i], 0, pad_val, pad_char);
    }

    var isNode = typeof module !== 'undefined' && module.exports;
    if (isNode) {
        return console.log(output);
    }

    var d = this.window.document;

    if (d.body) {
        this.echo(output);
    } else {
        try {
            d = XULDocument; /* We're in XUL, so appending as plain text won't work */
            this.echo('<pre xmlns="http://www.w3.org/1999/xhtml" style="white-space:pre;">' + output + '</pre>');
        } catch (e) {
            this.echo(output); /* Outputting as plain text may work in some plain XML */
        }
    }
}

/* Function str_replace PHP */
function str_replace(search, replace, subject, count) {
    /*    discuss at: http://phpjs.org/functions/str_replace/ */
    /* original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net) */

    var i = 0,
        j = 0,
        temp = '',
        repl = '',
        sl = 0,
        fl = 0,
        f = [].concat(search),
        r = [].concat(replace),
        s = subject,
        ra = Object.prototype.toString.call(r) === '[object Array]',
        sa = Object.prototype.toString.call(s) === '[object Array]';
    s = [].concat(s);

    if (typeof (search) === 'object' && typeof (replace) === 'string') {
        temp = replace;
        replace = new Array();
        for (i = 0; i < search.length; i += 1) {
            replace[i] = temp;
        }
        temp = '';
        r = [].concat(replace);
        ra = Object.prototype.toString.call(r) === '[object Array]';
    }

    if (count) {
        this.window[count] = 0;
    }

    for (i = 0, sl = s.length; i < sl; i++) {
        if (s[i] === '') {
            continue;
        }
        for (j = 0, fl = f.length; j < fl; j++) {
            temp = s[i] + '';
            repl = ra ? (r[j] !== undefined ? r[j] : '') : r[0];
            s[i] = (temp)
                .split(f[j])
                .join(repl);
            if (count) {
                this.window[count] += ((temp.split(f[j])).length - 1);
            }
        }
    }
    return sa ? s : s[0];
}

/* Vertically center Bootstrap 3 modals so they aren't always stuck at the top */
function reposition() {

    var modal = $(this),
        dialog = modal.find('.modal-dialog');

    modal.css('display', 'block');

    /* Dividing by two centers the modal exactly, but dividing by three */
    /* or four works better for larger screens. */
    dialog.css("margin-top", Math.max(0, ($(window).height() - dialog.height()) / 3));

}


/* Convert string to date */
function DateFormat(inputDate, div) {
    var div = (div) ? div : '/';
    var date = new Date(inputDate);
    if (!isNaN(date.getTime())) {
        /* Months use 0 index. */
        return ("0" + date.getDate()).slice(-2) + div + ("0" + (date.getMonth() + 1)).slice(-2) + div + date.getFullYear();
    }
    return false;
}

/* Email Validator*/
function validateEmail(email) {
    var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(email);
}

/* URL Validator */
function isUrlValid(url) {
    return /^(https?|s?ftp):\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i.test(url);
}

/* Get Meta Tag Content
 * @param {string} metaName The meta tag name.
 * @return {string} The meta tag content value, or empty string if not found.
 */
function getMetaContent(metaName) {
    var metas = document.getElementsByTagName('meta');
    var re = new RegExp('\\b' + metaName + '\\b', 'i');
    var i = 0;
    var mLength = metas.length;

    for (i; i < mLength; i++) {
        if (re.test(metas[i].getAttribute('name'))) {
            return metas[i].getAttribute('content');
        }
    }

    return '';
}

/* Function Print Element
 * https://gist.github.com/benedict-erwin/75298a97e6b30242c6a5010e31191a28
 */
function printElement(elem) {
    let domClone = elem.cloneNode(true);

    let $printSection = document.getElementById("printSection");

    if (!$printSection) {
        let $printSection = document.createElement("div");
        $printSection.id = "printSection";
        document.body.appendChild($printSection);
    }

    let WindowObject = window.open('', '_blank');
    WindowObject.document.body.appendChild(domClone);
    WindowObject.print();
    WindowObject.close();

    /* $printSection.innerHTML = "";
    $printSection.appendChild(domClone);
    window.print(); */
}

/* Switch to fullscreen */
function toggleFullScreen(elem) {
    /* The below if statement seems to work better ## if ((document.fullScreenElement && document.fullScreenElement !== null) || (document.msfullscreenElement && document.msfullscreenElement !== null) || (!document.mozFullScreen && !document.webkitIsFullScreen)) { */
    if ((document.fullScreenElement !== undefined && document.fullScreenElement === null) || (document.msFullscreenElement !== undefined && document.msFullscreenElement === null) || (document.mozFullScreen !== undefined && !document.mozFullScreen) || (document.webkitIsFullScreen !== undefined && !document.webkitIsFullScreen)) {
        if (elem.requestFullScreen) {
            elem.requestFullScreen();
        } else if (elem.mozRequestFullScreen) {
            elem.mozRequestFullScreen();
        } else if (elem.webkitRequestFullScreen) {
            elem.webkitRequestFullScreen(Element.ALLOW_KEYBOARD_INPUT);
        } else if (elem.msRequestFullscreen) {
            elem.msRequestFullscreen();
        }
    } else {
        if (document.cancelFullScreen) {
            document.cancelFullScreen();
        } else if (document.mozCancelFullScreen) {
            document.mozCancelFullScreen();
        } else if (document.webkitCancelFullScreen) {
            document.webkitCancelFullScreen();
        } else if (document.msExitFullscreen) {
            document.msExitFullscreen();
        }
    }
}

/* pNotify plugin */
function notification(msg, tipe, timeOut, title) {
    tipe = (typeof (tipe) === 'undefined') ? 'info' : tipe; /* info, success, error, warn */
    timeOut = (typeof (timeOut) === 'undefined') ? 1500 : timeOut * 1000; /* default: 1.5 sec */
    title = (typeof (title) === 'undefined') ? 'Info' : title; /* set tittle */
    new PNotify({
        title: title,
        text: msg,
        type: tipe,
        styling: 'bootstrap3',
        delay: timeOut
    });
}

/* Json Pretty Print */
function syntaxHighlight(json) {
    json = JSON.stringify(json, undefined, 2);
    json = json.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    return json.replace(/("(\\u[a-zA-Z0-9]{4}|\\[^u]|[^\\"])*"(\s*:)?|\b(true|false|null)\b|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?)/g, function (match) {
        var cls = 'number';
        if (/^"/.test(match)) {
            if (/:$/.test(match)) {
                cls = 'key';
            } else {
                cls = 'string';
            }
        } else if (/true|false/.test(match)) {
            cls = 'boolean';
        } else if (/null/.test(match)) {
            cls = 'null';
        }
        return '<span class="' + cls + '">' + match + '</span>';
    });
}

/* Get URL last Path */
function getCurrentPath() {
    var windowURL = window.location.href;
    if (window.location.href.search("\\?") != -1) {
        len = windowURL.substring(windowURL.lastIndexOf("\?"), windowURL.length).length;
        pageURL = windowURL.substring(windowURL.lastIndexOf("\?"), len);
        pageURL = pageURL.substring(pageURL.lastIndexOf("/"), pageURL.length);
    }else{
        pageURL = windowURL.substring(windowURL.lastIndexOf("/"), windowURL.length);
    }
    return pageURL.split('?')[0];
}

/* Read a page's GET URL variables
 * Usage :
 * query string: ?foo=lorem&bar=&baz
 * var foo = getParameterByName('foo'); // "lorem"
 * var bar = getParameterByName('bar'); // "" (present with empty value)
 * var baz = getParameterByName('baz'); // "" (present with no value)
 * var qux = getParameterByName('qux'); // null (absent)
 */
function getParameterByName(name, url) {
    if (!url) url = window.location.href;
    name = name.replace(/[\[\]]/g, '\\$&');
    var regex = new RegExp('[?&]' + name + '(=([^&#]*)|&|#|$)'),
        results = regex.exec(url);
    if (!results) return null;
    if (!results[2]) return '';
    return decodeURIComponent(results[2].replace(/\+/g, ' '));
}

/**
 * Redirect Login if token expired/invalid
 * @param {*} jqXHR ajax xhr resutl
 */
function redirectLogin(jqXHR) {
    if (jqXHR.status == 498 || jqXHR.status == 419) {
        del_token(API_TOKEN);
        window.location.reload();
    }
}

/* DataTable Ajax Reload */
function dtReload(table, time) {
    var time = (isNaN(time)) ? 100 : time;
    setTimeout(function () {
        table.ajax.reload(null, false); /* keep pagination */
        table.column(0).checkboxes.deselect();
    }, time);
}

/* DataTable search on enter */
function enterAndSearch(table, selector, enterBackspace) {
    $(selector + '_filter input').unbind();
    $(selector + '_filter input').bind('keyup', function (event) {
        let val = this.value;
        let len = val.length;
        if (event.keyCode === 13 && len > 0) table.search(this.value).draw();
        if (len > 0) enterBackspace = true;
        if (enterBackspace) {
            if (event.keyCode === 8 && len == 0) {
                table.search('').draw();
                enterBackspace = false;
            }
        }
    });
}

/* Sort Json Result */
function sortJson(arr, prop, asc) {
    json = arr.sort(function (a, b) {
        if (asc) {
            return (a[prop] > b[prop]) ? 1 : ((a[prop] < b[prop]) ? -1 : 0);
        } else {
            return (b[prop] > a[prop]) ? 1 : ((b[prop] < a[prop]) ? -1 : 0);
        }
    });
    return json;
}

/* Check if string is valid json */
function isJSON(text) {
    if (typeof text !== "string") {
        return false;
    }
    try {
        JSON.parse(text);
        return true;
    }
    catch (error) {
        return false;
    }
}

/* Force Download */
function forceDownload(href) {
    var anchor = document.createElement('a');
    anchor.href = href;
    anchor.download = href;
    document.body.appendChild(anchor);
    anchor.click();
}

/* Logout Ajax */
function kickAss() {
    setNprogressLoader("start");
    $.ajax({
        "type": 'GET',
        "url": SiteRoot + 'clogout',
        "dataType": 'json',
        "headers": { Authorization: "Bearer " + get_token(API_TOKEN) },
        "success": function (data, textStatus, jqXHR) {
            del_token(API_TOKEN);
            setNprogressLoader("done");
            if (data.success === true) {
                notification(data.message, 'success');
            } else {
                notification((data.message.error) ? data.message.error : data.message, 'warn');
            }

            setTimeout(function () {
                window.location.replace('login');
            }, 1000);
        },
        "error": function (jqXHR, textStatus, errorThrown) {
            setNprogressLoader("done");
            notification(errorThrown, 'error');
            redirectLogin(jqXHR);
            console.log(jqXHR);
            console.log(textStatus);
            console.log(errorThrown);
        }
    });
}


/* Check Page Auth */
function checkAuth(callback) {
    post_data = { 'path': getCurrentPath() };
    $.ajax({
        "type": 'POST',
        "url": SiteRoot + 'cauth',
        "data": post_data,
        "dataType": 'json',
        "headers": { Authorization: "Bearer " + get_token(API_TOKEN) },
        "success": function (data, textStatus, jqXHR) {
            set_token(API_TOKEN, jqXHR.getResponseHeader('JWT'));

            if (data.success === true) {
                /* Set Permission */
                var perm = data.message;

                /** Permission List **/

                /* Create */
                if (perm.indexOf('create') != -1) {
                    $('.btn_add').removeClass('hidden');
                    $('.btn_add').prop('disabled', false);
                } else {
                    $('.btn_add').addClass('hidden');
                    $('.btn_add').prop('disabled', true);
                }

                /* Read */
                if (perm.indexOf('read') != -1) {
                    $('#dtableDiv').css('display', '');
                    $('#dtableDiv').prop('disabled', false);
                } else {
                    if(perm.indexOf('index') != -1){
                        $('#dtableDiv').css('display', '');
                        $('#dtableDiv').prop('disabled', false);
                    }else{
                        $('#dtableDiv').css('display', 'none');
                        $('#dtableDiv').prop('disabled', true);
                    }
                }

                /* Update */
                if (perm.indexOf('update') != -1) {
                    $('.act-edit').removeClass('hidden');
                    $('.act-edit').prop('disabled', false);
                } else {
                    $('.act-edit').addClass('hidden');
                    $('.act-edit').prop('disabled', true);
                }

                /* Delete */
                if (perm.indexOf('delete') != -1) {
                    $('.act-delete').removeClass('hidden');
                    $('.act-delete').prop('disabled', false);
                } else {
                    $('.act-delete').addClass('hidden');
                    $('.act-delete').prop('disabled', true);
                }

                /* setPermission */
                if (perm.indexOf('setPermission') != -1 && perm.indexOf('jabatanMenu') != -1) {
                    $('.act-setPermission').removeClass('hidden');
                    $('.act-setPermission').prop('disabled', false);
                } else {
                    $('.act-setPermission').addClass('hidden');
                    $('.act-setPermission').prop('disabled', true);
                }
            }
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
}

/* Start CSS Loader */
function start_loader() {
    $("#css_loader").addClass('is-active');
}

/* Stop CSS Loader */
function stop_loader() {
    $("#css_loader").removeClass('is-active');
}

/* Set Token */
function set_token(key, value) {
    if (typeof (Storage) !== "undefined") {
        window.localStorage.setItem(key, value);
    } else {
        notification('Your browser didn\'t supported, please update!', 'warn');
    }
}

/* Get Token */
function get_token(key) {
    if (typeof (Storage) !== "undefined") {
        return window.localStorage.getItem(key);
    } else {
        notification('Your browser didn\'t supported, please update!', 'warn');
        return false;
    }
}

/* Del Token */
function del_token(key) {
    if (typeof (Storage) !== "undefined") {
        return window.localStorage.removeItem(key);
    } else {
        notification('Your browser didn\'t supported, please update!', 'warn');
    }
}

/* NProgress */
if (typeof NProgress != 'undefined') {
    $(document).ready(function () {
        setNprogressLoader("start");
    });
}

/* Document Ready */
$(document).ready(function () {
    /* Prevent loadMenu on Login Page */
    if (getCurrentPath() != '/login') {
        setNprogressLoader("start");
        $("ul#grpMenu").html('<i class="fa fa-spinner fa-spin"></i> Generating menus...');
        checkAuth();
        loadMenu();
    }

    /* Open setting modal */
    $("#btSetting").on('click', function (e) {
        e.preventDefault();
        getProfile();
    });

    /* Update profile */
    $(".btn_save_profile").on('click', function (e) {
        e.preventDefault();
        updateProfile();
    });

    /* Logout */
    $("#btLogout").on('click', function (e) {
        e.preventDefault();
        kickAss();
    });
})

function setNprogressLoader(status) {
    if(status == "start"){
        if(NP_STATUS == 0){
            NProgress.start();
            NP_STATUS = 1;
        }
    }else if(status == "done"){
        if(NP_STATUS == 1){
            NProgress.done();
            NP_STATUS = 0;
        }
    }
}

function updateProfile() {
    setNprogressLoader("start");
    var btn = $('.btn_save_profile');
    var formData = $('form#formModalEditProfile').serializeArray();
    btn.button('loading');
    btn.prop('disabled', true);

    formData.push({ name: 'action', value: 'update_profile' });
    if ($("form#formModalEditProfile").parsley().validate({ force: true, group: 'role' })) {
        $.ajax({
            "type": 'POST',
            "url": SiteRoot + 'user/update_profile',
            "headers": { Authorization: "Bearer " + get_token(API_TOKEN) },
            "dataType": 'json',
            "data": formData,
            "success": function (result, textStatus, jqXHR) {
                setNprogressLoader("done");
                set_token(API_TOKEN, jqXHR.getResponseHeader('JWT'));
                if (result.success === true) {
                    btn.button('reset');
                    btn.prop('disabled', false);
                    $('.formModalEditProfile').modal('hide');
                    notification(result.message, 'success');
                } else {
                    btn.button('reset');
                    btn.prop('disabled', false);
                    notification((result.error) ? result.error : result.message, 'warn', 3, result.message);
                }
            },
            "error": function (jqXHR, textStatus, errorThrown) {
                setNprogressLoader("done");
                notification(errorThrown, 'error');
                redirectLogin(jqXHR);
                $('.formModalEditProfile').modal('hide');
                console.log(jqXHR);
                console.log(textStatus);
                console.log(errorThrown);
            }
        });
    }else{
        setNprogressLoader("done");
        btn.button('reset');
        btn.prop('disabled', false);
    }

}

$(document).on('click', '#show_password', function() {
    if ($("#fp_password").attr("type") === "password") {
        $("#fp_password").attr("type", "text");
        $(".glyphicon")
            .removeClass("glyphicon-eye-open")
            .addClass("glyphicon-eye-close");
    }else{
        $("#fp_password").attr("type", "password");
        $(".glyphicon")
            .removeClass("glyphicon-eye-close")
            .addClass("glyphicon-eye-open");
    }
});

function getProfile() {
    setNprogressLoader("start");
    $.ajax({
        "type": 'GET',
        "url": SiteRoot + 'user/profile',
        "headers": { Authorization: "Bearer " + get_token(API_TOKEN) },
        "dataType": 'json',
        "success": function (result, textStatus, jqXHR) {
            setNprogressLoader("done");
            set_token(API_TOKEN, jqXHR.getResponseHeader('JWT'));
            if (result.success === true) {
                $("input[name=fp_nama]").val(result.message.nama);
                $("input[name=fp_email]").val(result.message.email);
                $("input[name=fp_telpon]").val(result.message.telpon);
                $("input[name=fp_username]").val(result.message.username);
                $("input[name=fp_role]").val(strtoupper(result.message.role));
                $("input[name=fp_password]").val('');
                $('.formModalEditProfile').modal();
            } else {
                notification((result.message.error) ? result.message.error : result.message, 'warn');
            }
        },
        "error": function (jqXHR, textStatus, errorThrown) {
            setNprogressLoader("done");
            notification(errorThrown, 'error');
            redirectLogin(jqXHR);
            console.log(jqXHR);
            console.log(textStatus);
            console.log(errorThrown);
        }
    });
}

