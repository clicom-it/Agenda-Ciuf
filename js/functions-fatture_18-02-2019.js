// Closure
(function () {
    /**
     * Approssimazione decimale di un numero.
     *
     * @param {String}  type  Il tipo di approssimazione.
     * @param {Number}  value Il numero.
     * @param {Integer} exp   L'esponente (the 10 logarithm of the adjustment base).
     * @returns {Number} Il valore approssimato.
     */
    function decimalAdjust(type, value, exp) {
        // Se exp è undefined o zero...
        if (typeof exp === 'undefined' || +exp === 0) {
            return Math[type](value);
        }
        value = +value;
        exp = +exp;
        // Se value non è un numero o exp non è un intero...
        if (isNaN(value) || !(typeof exp === 'number' && exp % 1 === 0)) {
            return NaN;
        }
        // Se value è negativo...
        if (value < 0) {
            return -decimalAdjust(type, -value, exp);
        }
        // Shift
        value = value.toString().split('e');
        value = Math[type](+(value[0] + 'e' + (value[1] ? (+value[1] - exp) : -exp)));
        // Shift back
        value = value.toString().split('e');
        return +(value[0] + 'e' + (value[1] ? (+value[1] + exp) : exp));
    }

    // Decimal round
    if (!Math.round10) {
        Math.round10 = function (value, exp) {
            return decimalAdjust('round', value, exp);
        };
    }
    // Decimal floor
    if (!Math.floor10) {
        Math.floor10 = function (value, exp) {
            return decimalAdjust('floor', value, exp);
        };
    }
    // Decimal ceil
    if (!Math.ceil10) {
        Math.ceil10 = function (value, exp) {
            return decimalAdjust('ceil', value, exp);
        };
    }
})();

function roundTo(value, decimals) {
    var i = value * Math.pow(10, decimals);
    i = Math.round(i);
    return i / Math.pow(10, decimals);
}

function mostraStampe() {
    $('.showcont').show().load('./form/form-stampefatture.php', function () {
        $.validator.messages.required = '';
        $("#stampefatture").validate({
            submitHandler: function () {
                $("#submitstampe").ready(function () {
                    var datastring = $("#stampefatture *").not(".nopost").serialize();

                    $.ajax({
                        type: "POST",
                        url: "./fatture.php",
                        data: datastring + "&submit=submitstampe",
                        dataType: "json",
                        success: function (msg) {
                            if (msg.msg === "ko") {
                                alert(msg.msgko);
                            } else {
                                location.href = '/stampafatture.php?' + datastring;
                            }
                        }
                    });
                });
            }
        });
    });
}

function mostraEsporta() {
    $('.showcont').show().load('./form/form-esportafatture.php', function () {
        $.validator.messages.required = '';
        $("#stampefatture").validate({
            submitHandler: function () {
                $("#submitstampe").ready(function () {
                    var datastring = $("#stampefatture *").not(".nopost").serialize();

                    $.ajax({
                        type: "POST",
                        url: "./fatture.php",
                        data: datastring + "&submit=submitesporta",
                        dataType: "json",
                        success: function (msg) {
                            if (msg.msg === "ko") {
                                alert(msg.msgko);
                            } else {
                                location.href = '/esportafatture.php?' + datastring;
                            }
                        }
                    });
                });
            }
        });
    });
}

function mostraEsportaFE() {
    $('.showcont').show().load('./form/form-esportafe.php', function () {
        $.validator.messages.required = '';
        $("#stampefatture").validate({
            submitHandler: function () {
                $("#submitstampe").ready(function () {
                    var datastring = $("#stampefatture *").not(".nopost").serialize();

                    $.ajax({
                        type: "POST",
                        url: "./fatture.php",
                        data: datastring + "&submit=submitesporta",
                        dataType: "json",
                        success: function (msg) {
                            if (msg.msg === "ko") {
                                alert(msg.msgko);
                            } else {
                                location.href = '/esportafe.php?' + datastring;
                            }
                        }
                    });
                });
            }
        });
    });
}

function mostraFatture(arch, filtri, paginastart) {

    if (paginastart) {
        var pagestart = parseInt(paginastart);
    }

    if (filtri) {
        var filtriarr = filtri.split("||");
        var filtro0 = {numero: filtriarr[0]};
        var filtro1 = {datait: filtriarr[1]};
        var filtro2 = {tipo: filtriarr[2]};
        var filtro3 = {daticliente: filtriarr[3]};
        var filtro4 = {metodopagamento: filtriarr[4]};
        var filtro5 = {totalefatt: filtriarr[5]};
        var filtro6 = {totalefatt_iva: filtriarr[6]};
        var filtro7 = {note: filtriarr[7]};
        var filtro8 = {stato: filtriarr[8]};
    }
    $.ajax({
        type: "POST",
        url: "./fatture.php",
        data: "arch=" + arch + "&submit=mostrafatture",
        dataType: "json",
        success: function (msg) {
            var db = {
                loadData: function (filter) {
                    return $.grep(this.clients, function (client) {
                        return (!filter.numero || client.numero.indexOf(filter.numero) > -1)
                                && (!filter.datait || client.datait.indexOf(filter.datait) > -1)
                                && (!filter.tipo || client.tipo.indexOf(filter.tipo) > -1)
                                && (!filter.daticliente.toLowerCase() || client.daticliente.toLowerCase().indexOf(filter.daticliente.toLowerCase()) > -1)
                                && (!filter.metodopagamento.toLowerCase() || client.metodopagamento.toLowerCase().indexOf(filter.metodopagamento.toLowerCase()) > -1)
                                && (!filter.totalefatt || client.totalefatt.indexOf(filter.totalefatt) > -1)
                                && (!filter.totalefatt_iva || client.totalefatt_iva.indexOf(filter.totalefatt_iva) > -1)
                                && (!filter.note.toLowerCase() || client.note.toLowerCase().indexOf(filter.note.toLowerCase()) > -1)
                                && (!filter.stato || client.stato.indexOf(filter.stato) > -1);
                    });
                }
            };

            window.db = db;

            db.clients = msg.dati;
            jsGrid.locale("it");


            if (arch == '2') {
                var campi = [
                    {name: "numero", type: "number", title: "<b>Num.</b>", css: "customRow", width: "30", filterTemplate: createTextFilterTemplate("numero", filtro0)},
                    {name: "datait", type: "text", title: "<b>Data</b>", css: "customRow", width: "40", filterTemplate: createTextFilterTemplate("datait", filtro1)},
                    {name: "tipo", type: "select", items: [
                            {name: "Seleziona tipo", Id: ""},
                            {name: "Acquisto", Id: "1"},
                        ], title: "<b>Tipo</b>", valueField: "Id", textField: "name", css: "customRow", width: "60", filterTemplate: createSelectFilterTemplate("tipo", filtro2)},
                    {name: "daticliente", type: "text", title: "<b>Cliente</b>", css: "customRow", filterTemplate: createTextFilterTemplate("daticliente", filtro3)},
                    {name: "metodopagamento", type: "text", title: "<b>Pagamento</b>", css: "customRow", filterTemplate: createTextFilterTemplate("metodopagamento", filtro4)},
                    {name: "totalefatt", type: "text", title: "<b>Totale</b>", css: "customRow", width: "40", filterTemplate: createTextFilterTemplate("totalefatt", filtro5)},
                    {name: "totalefatt_iva", type: "text", title: "<b>Totale con iva</b>", css: "customRow", width: "40", filterTemplate: createTextFilterTemplate("totalefatt_iva", filtro6)},
                    {name: "note", type: "text", title: "<b>Note</b>", css: "customRow", width: "60", filterTemplate: createTextFilterTemplate("note", filtro7)},
                    {name: "stato", type: "select", items: [
                            {name: "Seleziona stato", Id: ""},
                            {name: "Non pagata", Id: "0"},
                            {name: "Pagata", Id: "1"},
                        ], title: "<b>Stato</b>", valueField: "Id", textField: "name", css: "customRow", width: "60", filterTemplate: createSelectFilterTemplate("stato", filtro8)},
                    {type: "control", itemTemplate: function (value, item) {
                            var $result = jsGrid.fields.control.prototype.itemTemplate.apply(this, arguments);

                            var $myButton = $("<i style=\"margin-left: 5px; color: #ff0000;\"class=\"fa fa-file-pdf-o fa-lg\" aria-hidden=\"true\"></i>")
                                    .click(function (e) {
                                        var idd = item.id;
                                        window.open('./pdf/fattura.php?idfatt=' + idd + '', '_blank');
                                        e.stopPropagation();
                                    });
                            return $result.add($myButton);
                        }}
                ];
                $("#jsGrid").jsGrid({
                    width: "100%",
                    height: "580px",
                    filtering: true,
                    editing: true,
                    sorting: true,
                    paging: true,
                    autoload: true,
                    pageSize: 12,
                    pageIndex: pagestart,
                    pageButtonCount: 5,
                    controller: db,
                    deleteConfirm: "Stai per cancellare, sei sicuro?",
                    noDataContent: "Nessun record trovato",
                    fields: campi,
                    // cancella item
                    onItemDeleting: function (args) {
                        var idd = args.item.id;
                        $.ajax({
                            type: "POST",
                            url: "./fatture.php",
                            data: "id=" + idd + "&submit=delete",
                            dataType: "json",
                            success: function () {
                            }
                        });
                    },
                    // edita item
                    onItemEditing: function (args) {
                        args.cancel = true;
                        var filtri = $("#jsGrid").jsGrid("getFilter").numero + "||" + $("#jsGrid").jsGrid("getFilter").datait + "||"
                                + $("#jsGrid").jsGrid("getFilter").tipo + "||"
                                + $("#jsGrid").jsGrid("getFilter").daticliente + "||" + $("#jsGrid").jsGrid("getFilter").metodopagamento
                                + "||" + $("#jsGrid").jsGrid("getFilter").totalefatt + "||" + $("#jsGrid").jsGrid("getFilter").totalefatt_iva + "||" + $("#jsGrid").jsGrid("getFilter").note
                                + "||" + $("#jsGrid").jsGrid("getFilter").stato;

                        var paginastart = parseInt($('.jsgrid-pager-current-page').html());

                        var ide = args.item.id;
                        aggiornaAcquisto(ide, filtri, arch, paginastart);

                    }
                });
            } else {
                var campi = [
                    {name: "numero", type: "number", title: "<b>Num.</b>", css: "customRow", width: "30", filterTemplate: createTextFilterTemplate("numero", filtro0)},
                    {name: "datait", type: "text", title: "<b>Data</b>", css: "customRow", width: "40", filterTemplate: createTextFilterTemplate("datait", filtro1)},
                    {name: "tipo", type: "select", items: [
                            {name: "Seleziona tipo", Id: ""},
                            {name: "Vendita", Id: "0"},
                            {name: "Vendita PC", Id: "5"},
                            {name: "Proforma", Id: "2"},
                            {name: "Nota di credito", Id: "3"},
                            {name: "Fattura P.A.", Id: "4"},
                        ], title: "<b>Tipo</b>", valueField: "Id", textField: "name", css: "customRow", width: "60", filterTemplate: createSelectFilterTemplate("tipo", filtro2)},
                    {name: "daticliente", type: "text", title: "<b>Cliente</b>", css: "customRow", filterTemplate: createTextFilterTemplate("daticliente", filtro3)},
                    {name: "metodopagamento", type: "text", title: "<b>Pagamento</b>", css: "customRow", filterTemplate: createTextFilterTemplate("metodopagamento", filtro4)},
                    {name: "totalefatt", type: "text", title: "<b>Totale</b>", css: "customRow", width: "40", filterTemplate: createTextFilterTemplate("totalefatt", filtro5)},
                    {name: "totalefatt_iva", type: "text", title: "<b>Totale con iva</b>", css: "customRow", width: "40", filterTemplate: createTextFilterTemplate("totalefatt_iva", filtro6)},
                    {name: "note", type: "text", title: "<b>Note</b>", css: "customRow", width: "60", filterTemplate: createTextFilterTemplate("note", filtro7)},
                    {name: "stato", type: "select", items: [
                            {name: "Seleziona stato", Id: ""},
                            {name: "Non pagata", Id: "0"},
                            {name: "Pagata", Id: "1"},
                        ], title: "<b>Stato</b>", valueField: "Id", textField: "name", css: "customRow", width: "60", filterTemplate: createSelectFilterTemplate("stato", filtro8)},
                    {type: "control", itemTemplate: function (value, item) {
                            var $result = jsGrid.fields.control.prototype.itemTemplate.apply(this, arguments);

                            var $myButton = $("<i style=\"margin-left: 5px; color: #ff0000;\"class=\"fa fa-file-pdf-o fa-lg\" aria-hidden=\"true\"></i>")
                                    .click(function (e) {
                                        var idd = item.id;
                                        window.open('./pdf/fattura.php?idfatt=' + idd + '', '_blank');
                                        e.stopPropagation();
                                    });
                            var $myButton2 = $("<i style=\"margin-left: 5px; color: #2E65A1;\"class=\"fa fa-file-pdf-o fa-lg\" aria-hidden=\"true\"></i>")
                                    .click(function (e) {
                                        var idd = item.id;
                                        window.open('./pdf/fattura.php?idfatt=' + idd + '&day=120', '_blank');
                                        e.stopPropagation();
                                    });
                            var $myButton3 = $("<i style=\"margin-left: 5px; color: #2E65A1;\"class=\"fa fa-file-excel-o fa-lg\" aria-hidden=\"true\"></i>")
                                    .click(function (e) {
                                        var idd = item.id;
                                        window.open('./excel/fattura.php?idfatt=' + idd + '&day=120', '_blank');
                                        e.stopPropagation();
                                    });
                            return $result.add($myButton).add($myButton2).add($myButton3);
                        }}
                ];
                $("#jsGrid").jsGrid({
                    width: "100%",
                    height: "580px",
                    filtering: true,
                    editing: true,
                    sorting: true,
                    paging: true,
                    autoload: true,
                    pageSize: 12,
                    pageIndex: pagestart,
                    pageButtonCount: 5,
                    controller: db,
                    deleteConfirm: "Stai per cancellare, sei sicuro?",
                    noDataContent: "Nessun record trovato",
                    fields: campi,
                    // cancella item
                    onItemDeleting: function (args) {
                        var idd = args.item.id;
                        $.ajax({
                            type: "POST",
                            url: "./fatture.php",
                            data: "id=" + idd + "&submit=delete",
                            dataType: "json",
                            success: function () {
                            }
                        });
                    },
                    // edita item
                    onItemEditing: function (args) {
                        args.cancel = true;
                        var filtri = $("#jsGrid").jsGrid("getFilter").numero + "||" + $("#jsGrid").jsGrid("getFilter").datait + "||"
                                + $("#jsGrid").jsGrid("getFilter").tipo + "||"
                                + $("#jsGrid").jsGrid("getFilter").daticliente + "||" + $("#jsGrid").jsGrid("getFilter").metodopagamento
                                + "||" + $("#jsGrid").jsGrid("getFilter").totalefatt + "||" + $("#jsGrid").jsGrid("getFilter").totalefatt_iva + "||" + $("#jsGrid").jsGrid("getFilter").note
                                + "||" + $("#jsGrid").jsGrid("getFilter").stato;

                        var paginastart = parseInt($('.jsgrid-pager-current-page').html());

                        var ide = args.item.id;
                        aggiorna(ide, filtri, arch, paginastart);
                    }
                });
            }
        }
    });
    $('.showcont').html("<div id=\"jsGrid\"></div>");
}

function setNazione(nazione, ident, selectcomune, selectcap) {
    if (ident === '1') {
        if (nazione !== 'IT') {
            var title = $('select#comune option:first').html();
            $('select#comune').replaceWith('<input type="text" name="comune" id="comune" class="input_moduli sizing float_moduli" placeholder="' + title + '" title="' + title + '" value="" />');
            title = $('select#cap option:first').html();
            $('select#cap').replaceWith('<input type="text" name="cap" id="cap" class="input_moduli sizing float_moduli" placeholder="' + title + '" title="' + title + '" value="" />');
            $('#regione, #provincia').hide();
        } else {
            $('#regione, #provincia').show().val("");
            $('#comune').replaceWith('<select name="comune" id="comune" class="extra input_moduli float_moduli_small" onchange="setCap($(this).val(), \'\', \'1\');"><option value="">' + selectcomune + '</option></select>');
            $('#cap').replaceWith('<select name="cap" id="cap" class="extra input_moduli float_moduli_small"><option value="">' + selectcap + '</option></select>');
        }
    } else if (ident === '2') {
        if (nazione !== 'IT') {
            var title = $('select#comunespedizione option:first').html();
            $('select#comunespedizione').replaceWith('<input type="text" name="comunespedizione" id="comunespedizione" class="input_moduli sizing float_moduli" placeholder="' + title + '" title="' + title + '" value="" />');
            title = $('select#capspedizione option:first').html();
            $('select#capspedizione').replaceWith('<input type="text" name="capspedizione" id="capspedizione" class="input_moduli sizing float_moduli" placeholder="' + title + '" title="' + title + '" value="" />');
            $('#regionespedizione, #provinciaspedizione').hide();
        } else {
            $('#regionespedizione, #provinciaspedizione').show().val("");
            $('#comunespedizione').replaceWith('<select name="comunespedizione" id="comunespedizione" class="extra input_moduli float_moduli_small" onchange="setCap($(this).val(), \'\', \'2\');"><option value="">' + selectcomune + '</option></select>');
            $('#capspedizione').replaceWith('<select name="capspedizione" id="capspedizione" class="extra input_moduli float_moduli_small"><option value="">' + selectcap + '</option></select>');
        }
    }
}

/* function aggiungi */

function aggiungi() {
    $('.showcont').show().load('./form/form-fatture.php', function () {
        /* sortable righe */
        $('.contienirighe').sortable({
            disabled: false,
            cancel: '.nosortable, input, select',
            cursor: 'move'
        });
        /**/
        /**/
        $.validator.messages.required = '';
        $("#formfattura").validate({
            submitHandler: function () {
                $("#submitformfattura").ready(function () {
                    var datastring = $("#formfattura *").not(".nopost").serialize();
                    var stato = $('#stato').val();
                    $.ajax({
                        type: "POST",
                        url: "./fatture.php",
                        data: datastring + "&submit=submitformfattura",
                        dataType: "json",
                        success: function (msg) {
                            if (msg.msg === "ko") {
                                alert(msg.msgko);
                            } else {
                                if (stato === '1') {
                                    location.href = '/fatture.php?op=archivio';
                                } else {
                                    location.href = '/fatture.php?op=fatture';
                                }
//                                var numsuccessivo = parseInt($('#numero').val()) + 1;
//                                $('#formpreventivo').trigger('reset');
//                                $('.rigaadd').remove();
//                                $('#numero').val(numsuccessivo);
//                                $('body,html').animate({scrollTop: 0}, 500);
//                                $('#messaggio').slideToggle('fast').delay(2000).slideToggle('slow');
                            }
                        }
                    });
                });
            }
        });
    });
}

function aggiorna(id, filtri, arch, paginastart) {
    $('.showcont').show().load('./form/form-fatture.php', function () {
        $('.bottone_chiudi').html("<a href=\"javascript:;\" class=\"sizing\" onclick=\"mostraFatture('" + arch + "', '" + filtri + "', '" + paginastart + "');\">Chiudi</a>");
        /*richiama dati*/
        $.ajax({
            type: "POST",
            url: "./fatture.php",
            data: "id=" + id + "&submit=richiamafattura",
            dataType: "json",
            success: function (msg) {
                /* riempio i campi */
                var valorebanca = "";
                $.each(msg['valori'][0], function (index, value) {
                    $("#" + index).val(value);
                    if (index === "coordinate") {
                        valorebanca = value;
                    }
                });
                var data = $('#data').val().split('-');
                $('#datap').val(data[2] + "/" + data[1] + "/" + data[0]);
                for (var i = 0; i < msg['voci'].length; i++) {

                    var totale = oprighe(msg['voci'][i].qta, msg['voci'][i].importo, msg['voci'][i].sconto);
                    var ivapercalcoli = parseFloat(100 + parseFloat(msg['voci'][i].iva));
                    totaleiva = totale * ivapercalcoli / 100;

                    $('.contienirighe').append('<div class="rigaadd riga_prodotto_fatt sizing">\n\
                                            <i class="fa fa-arrows-v fa-lg ordinarighe" aria-hidden="true"></i>\n\
                                            <select profit="profit' + i + '" name="idprofit[]" class="input_moduli sizing float_moduli_small_10" placeholder="Profit Center" title="Profit Center"><option value="0">Seleziona profit center</option>' + msg.vociprofit + '</select>\n\
\n\                                         <textarea name="descrizione[]" class="nosortable textarea_moduli_small sizing float_moduli_40" placeholder="Descrizione" title="Descrizione">' + msg['voci'][i].descrizione + '</textarea>\n\
                                            <input type="text" name="um[]" class="input_moduli sizing float_moduli_small_10" placeholder="U.M." title="U.M." value="' + msg['voci'][i].um + '" />\n\
                                            <input type="text" name="qta[]" class="qta input_moduli sizing float_moduli_small_5" placeholder="Q.ta" title="Q.ta" value="' + msg['voci'][i].qta + '" />\n\
                                            <input type="text" name="importo[]" class="importo input_moduli sizing float_moduli_small_15" placeholder="Importo" title="Importo" value="' + msg['voci'][i].importo + '" />\n\
                                            <input type="text" name="sconto[]" class="sconto input_moduli sizing float_moduli_small_10" placeholder="Sconto" title="Sconto" value="' + msg['voci'][i].sconto + '" />\n\
                                            <select name="iva[]" iva="iva' + i + '" class="iva input_moduli sizing float_moduli_small_10" placeholder="Iva" title="Iva">' + msg.ivaselect + ' </select>\n\
                                            <input type="hidden" name="totalevoce[]" class="totalevoce input_moduli sizing float_moduli_small_10 nopost" value="' + totale + '" />\n\
                                            <input type="hidden" name="totalevoceiva[]" class="totalevoceiva input_moduli sizing float_moduli_small_10 nopost" value="' + totaleiva + '" />\n\
                                            <input type="hidden" name="idprev[]" class="input_moduli sizing float_moduli_small_10" value="' + msg['voci'][i].idprev + '" />\n\
                                            <input type="hidden" name="idfatt[]" class="input_moduli sizing float_moduli_small_10" value="' + msg['voci'][i].idfatt + '" />\n\
                                            <input type="hidden" name="idcomm[]" class="input_moduli sizing float_moduli_small_10" value="' + msg['voci'][i].idcomm + '" />\n\
                                            <input type="hidden" name="codice_iva[]" class="input_moduli sizing float_moduli_small_10" value="' + msg['voci'][i].codice_iva + '" />\n\
                                            <input type="hidden" name="idvocecomm[]" class="input_moduli sizing float_moduli_small_10" value="' + msg['voci'][i].idvocecomm + '" />\n\
                                            <a href="javascript:;" class="remove_button"><i style="color: #CD0A0A; line-height: 35px;" class="fa fa-times fa-lg" aria-hidden="true"></i></a>\n\
                                            <div class="chiudi"></div>\n\
                                            </div>');
                    if (parseFloat(msg['voci'][i].iva) == 0) {
                        $("[iva=iva" + i + "]").val(msg['voci'][i].iva + ' ' + msg['voci'][i].codice_iva);
                    } else {
                        $("[iva=iva" + i + "]").val(msg['voci'][i].iva);
                    }
                    $("[profit=profit" + i + "]").val(msg['voci'][i].idprofit);
                }
                $('#scadenzefattura').html(msg.scadenze);
                $.datepicker.setDefaults($.datepicker.regional[ "it" ]);
                $(".datascad").datepicker({
                });
                $('#mostraaggiornascadenzefattura').show();
                /* cerca preventivi e fatture per quel cliente */
                $.ajax({
                    type: "POST",
                    url: "./fatture.php",
                    data: "idcliente=" + msg['valori'][0]['idcliente'] + "&submit=bancapreventivicommessefattureokcliente",
                    dataType: "json",
                    success: function (res)
                    {
                        /* preventivi */
                        var optionpreventivi = "";
                        for (var i = 0; i < res.preventivi.length; i++) {
                            optionpreventivi += "<option class=\"preventivicliente prev" + res.preventivi[i].id + " \" value=\"" + res.preventivi[i].id + "\">" + res.preventivi[i].numero + " - " + res.preventivi[i].datait + " " + res.preventivi[i].titolo + "</option>";
                        }
                        $('.preventivicliente').remove();
                        $('.collegapreventivo').append(optionpreventivi);
                        /* commesse */
                        var optioncommesse = "";
                        for (var i = 0; i < res.commesse.length; i++) {
                            optioncommesse += "<option class=\"commessecliente comm" + res.commesse[i].id + " \" value=\"" + res.commesse[i].id + "\">" + res.commesse[i].numero + " - " + res.commesse[i].datait + " " + res.commesse[i].titolo + "</option>";
                        }
                        $('.commessecliente').remove();
                        $('.collegacommessa').append(optioncommesse);
                        /* banche */
                        $('.banche').remove();
                        var banche = "";
                        if (res.banche[0]['coordinate'].length > 0) {
                            banche += "<option class=\"banche\" value=\"coordinate\">" + res.banche[0]['coordinate'] + "</option>";
                        }
                        for (var i = 1; i <= 9; i++) {
                            if (res.banche[0]['coordinate' + i + ''].length > 0) {
                                banche += "<option class=\"banche\" value=\"coordinate" + i + "\">" + res.banche[0]['coordinate' + i + ''] + "</option>";
                            } else {
                            }
                        }
                        $('#coordinate').append(banche);
                        $('#coordinate').val(valorebanca);
                    }
                });
            }
        });
        /* sortable righe */
        $('.contienirighe').sortable({
            disabled: false,
            cancel: '.nosortable, input, select',
            cursor: 'move'
        });
        /**/
        $.validator.messages.required = '';
        $("#formfattura").validate({
            submitHandler: function () {
                $("#submitformfattura").ready(function () {
                    var datastring = $("#formfattura *").not(".nopost").serialize();
                    var stato = $('#stato').val();
                    $.ajax({
                        type: "POST",
                        url: "./fatture.php",
                        data: datastring + "&submit=editformfattura",
                        dataType: "json",
                        success: function (msg) {
                            if (msg.msg === "ko") {
                                alert(msg.msgko);
                            } else {
//                                if (stato === '1') {
//                                    location.href = '/fatture.php?op=archivio';
//                                } else {
//                                    location.href = '/fatture.php?op=fatture';
//                                }
                                mostraFatture(arch, filtri, paginastart);
                            }
                        }
                    });
                });
            }
        });
    });
}

function oprighe(qta, importo, sconto) {
    var totale = parseFloat(qta * importo - qta * importo * sconto / 100);
//    return roundTo(totale, 2);
    return totale;
}

function totalefattura() {
    var totale = 0;
    $('.totalevoce').each(function () {
        if (!isNaN(this.value)) {
            totale += parseFloat(this.value);
        } else {
            totale += 0;
        }
    });
    $('#totalefatt').val(Math.round10(totale, -2));

    var totaleiva = 0;
    $('.totalevoceiva').each(function () {
        if (!isNaN(this.value)) {
            totaleiva += parseFloat(this.value);
        } else {
            totaleiva += 0;
        }
    });
    $('#totalefatt_iva').val(Math.round10(totaleiva, -2));
    calcolascadenze();
}
/* preventivi collegati */
function collegaPreventivo(id) {
    $.ajax({
        type: "POST",
        url: "./fatture.php",
        data: "id=" + id + "&submit=collegapreventivo",
        dataType: "json",
        success: function (msg) {
            $('.prev' + msg['valori'][0]['id']).hide();
            /* id preventivo collegato */
            var idprev = msg['valori'][0]['id'];
            /* data leggibile */
            var data = msg['valori'][0]['data'].split('-');
            /**/
            /* quale totale preventivo */
            var totale = msg['valori'][0]['totaleprev'];
            if (totale.length > 0) {
                var totaleprint = totale;
            } else {
                totaleprint = msg['valori'][0]['totalescontatoprev'];
            }
            /* voci preventivo */
            var vociprev = "";
//            var vocipreffatt = "";
            for (var i = 0; i < msg['voci'].length; i++) {
                vociprev += "<div class=\"nomevoceprev sizing\">" + msg['voci'][i].nome + "</div>\n\
                             <div class=\"descvoceprev sizing\">" + msg['voci'][i].descr + "</div>\n\
                             <div class=\"qtavoceprev sizing\">" + msg['voci'][i].qta + "</div>\n\
                             <div class=\"prezzovoceprev sizing\">" + msg['voci'][i].scontato + "</div>\n\
                             <div class=\"chiudi\"></div>";

                var ivapercalcoli = parseFloat(100 + parseFloat(msg.ivadefault));
                var totaleiva = parseFloat(msg['voci'][i].scontato * ivapercalcoli / 100);

                $('.contienirighe').append('<div class="rigaadd riga_prodotto_fatt sizing rigaprevcomm_' + idprev + '">\n\
                                            <i class="fa fa-arrows-v fa-lg ordinarighe" aria-hidden="true"></i>\n\
                                            <select profit="profit' + i + '" name="idprofit[]" class="input_moduli sizing float_moduli_small_10" placeholder="Profit Center" title="Profit Center"><option value="0">Seleziona profit center</option>' + msg.vociprofit + '</select>\n\
                                            <textarea name="descrizione[]" class="nosortable textarea_moduli_small sizing float_moduli_40" placeholder="Descrizione" title="Descrizione">Rif. prev.: ' + msg['valori'][0]['numero'] + '.' + data[0] + ' : ' + msg['voci'][i].nome + ' ' + msg['voci'][i].descr + '</textarea>\n\
                                            <input type="text" name="um[]" class="input_moduli sizing float_moduli_small_10" placeholder="U.M." title="U.M." />\n\
                                            <input type="text" name="qta[]" class="qta input_moduli sizing float_moduli_small_5" placeholder="Q.ta" title="Q.ta" value="' + msg['voci'][i].qta + '" />\n\
                                            <input type="text" name="importo[]" class="importo input_moduli sizing float_moduli_small_15" placeholder="Importo" title="Importo" value="' + msg['voci'][i].prezzo + '" />\n\
                                            <input type="text" name="sconto[]" class="sconto input_moduli sizing float_moduli_small_10" placeholder="Sconto" title="Sconto" value="' + msg['voci'][i].sconto + '" />\n\
                                            <select name="iva[]"class="iva input_moduli sizing float_moduli_small_10" placeholder="Iva" title="Iva">' + msg.ivaselect + ' </select>\n\
                                            <input type="hidden" name="totalevoce[]" class="totalevoce input_moduli sizing float_moduli_small_10 nopost" value="' + msg['voci'][i].scontato + '" />\n\
                                            <input type="hidden" name="totalevoceiva[]" class="totalevoceiva input_moduli sizing float_moduli_small_10 nopost" value="' + totaleiva + '" />\n\
                                            <input type="hidden" name="idprev[]" class="input_moduli sizing float_moduli_small_10" value="' + idprev + '" />\n\
                                            <input type="hidden" name="idfatt[]" class="input_moduli sizing float_moduli_small_10" value="0" />\n\
                                            <input type="hidden" name="idcomm[]" class="input_moduli sizing float_moduli_small_10" value="0" />\n\
                                            <input type="hidden" name="codice_iva[]" class="input_moduli sizing float_moduli_small_10" value="' + msg['voci'][i].codice_iva + '" />\n\
                                            <input type="hidden" name="idvocecomm[]" class="input_moduli sizing float_moduli_small_10" value="0" />\n\
                                            <a href="javascript:;" class="remove_button"><i style="color: #CD0A0A; line-height: 35px;" class="fa fa-times fa-lg" aria-hidden="true"></i></a>\n\
                                            <div class="chiudi"></div>\n\
                                            </div>');

            }
            /**/
            var htmlprev = "<div class=\"rigaprevcomm sizing rigaprevcomm_" + idprev + "\" >\n\
                            <input type=\"hidden\" name=\"idprevfatt[]\" value=\"" + idprev + "\" />\n\
                            <div class=\"numpreventivo sizing\">Prev. N° " + msg['valori'][0]['numero'] + "</div>\n\
                            <div class=\"datapreventivo sizing\">" + data[2] + "/" + data[1] + "/" + data[0] + "</div>\n\
                            <div class=\"titolopreventivo sizing\">" + msg['valori'][0]['titolo'] + "</div>\n\
                            <div class=\"totalepreventivo sizing\">&euro; " + totaleprint + "</div>\n\
                            <div class=\"eliminapreventivo sizing\">\n\
                            <a href=\"javascript:;\" onclick=\"javascript:$('.vociprev_" + idprev + "').slideToggle();\"><i class=\"fa fa-info-circle fa-lg\" aria-hidden=\"true\" style=\"color: #000000;\"></i></a> \n\
                            <a href=\"javascript:;\" onclick=\"delprevcomm('" + idprev + "');\"><i class=\"fa fa-times fa-lg\" aria-hidden=\"true\"></i></a>\n\
                            </div>\n\
                            <div class=\"chiudi\"></div>\n\
                            <div class=\"vociprev_" + idprev + "\" style=\"display: none;\">\n\
                            " + vociprev + "\n\
                            </div>\n\
                            <div class=\"chiudi\"></div>\n\
                            </div>";

            $('.noprevcoll').hide();
            $('.preventivicollegati').append(htmlprev);
            totalefattura();
        }
    });
}

/* domini collegati */
function collegaDominio(id) {
    if (id > 0) {
        $.ajax({
            type: "POST",
            url: "./fatture.php",
            data: "id=" + id + "&submit=collegadominio",
            dataType: "json",
            success: function (msg) {
                $('.dom' + msg['valori'][0]['id']).hide();
                /* id dominio collegato */
                var iddom = msg['valori'][0]['id'];

                /**/
                /* prezzo dominio */
                var totaleprint = "";
                var totale = msg['valori'][0]['prezzo'];
                if (totale.length > 0) {
                    totaleprint = totale;
                }

                /* lavoro con le date */
                /* controllo se la fattura ha una data */
                var datafatt = $('#data').val();
                if (datafatt) {
                    var datafattarr = datafatt.split("-");
                    var year = parseInt(datafattarr[0]);
                } else {
                    var currentTime = new Date();
                    var year = currentTime.getFullYear();
                }


                var data = msg['valori'][0]['dataattivazione'];
                var dataarr = data.split('-');
//                var datarinnovostart = dataarr[2] + "/" + dataarr[1] + "/" + year;
//                var datarinnovoend = dataarr[2] + "/" + dataarr[1] + "/" + parseInt(year + 1);
                var datarinnovostart = "01/01/" + year;
                var datarinnovoend = "31/12/" + parseInt(year);
                var rinnovoprint = "Canone annuale mantenimento servizi, dal " + datarinnovostart + " al " + datarinnovoend;

                /**/


                var ivapercalcoli = parseFloat(100 + parseFloat(msg.ivadefault));
                var totaleiva = parseFloat(msg['valori'][0]['prezzo'] * ivapercalcoli / 100);

                $('.contienirighe').append('<div class="rigaadd riga_prodotto_fatt sizing rigadom_' + iddom + '">\n\
                                            <i class="fa fa-arrows-v fa-lg ordinarighe" aria-hidden="true"></i>\n\
                                            <select profit="profit" name="idprofit[]" class="input_moduli sizing float_moduli_small_10" placeholder="Profit Center" title="Profit Center"><option value="0">Seleziona profit center</option>' + msg.vociprofit + '</select>\n\
                                            <textarea name="descrizione[]" class="nosortable textarea_moduli_small sizing float_moduli_40" placeholder="Descrizione" title="Descrizione">' + msg['valori'][0]['dominio'] + '\n' + msg['valori'][0]['descrizione'] + '\n' + rinnovoprint + '</textarea>\n\
                                            <input type="text" name="um[]" class="input_moduli sizing float_moduli_small_10" placeholder="U.M." title="U.M." value="N" />\n\
                                            <input type="text" name="qta[]" class="qta input_moduli sizing float_moduli_small_5" placeholder="Q.ta" title="Q.ta" value="1" />\n\
                                            <input type="text" name="importo[]" class="importo input_moduli sizing float_moduli_small_15" placeholder="Importo" title="Importo" value="' + msg['valori'][0]['prezzo'] + '" />\n\
                                            <input type="text" name="sconto[]" class="sconto input_moduli sizing float_moduli_small_10" placeholder="Sconto" title="Sconto" value="0.00" />\n\
                                            <select name="iva[]"class="iva input_moduli sizing float_moduli_small_10" placeholder="Iva" title="Iva">' + msg.ivaselect + ' </select>\n\
                                            <input type="hidden" name="totalevoce[]" class="totalevoce input_moduli sizing float_moduli_small_10 nopost" value="' + msg['valori'][0]['prezzo'] + '" />\n\
                                            <input type="hidden" name="totalevoceiva[]" class="totalevoceiva input_moduli sizing float_moduli_small_10 nopost" value="' + totaleiva + '" />\n\
                                            <input type="hidden" name="idprev[]" class="input_moduli sizing float_moduli_small_10" value="0" />\n\
                                            <input type="hidden" name="idfatt[]" class="input_moduli sizing float_moduli_small_10" value="0" />\n\
                                            <input type="hidden" name="idcomm[]" class="input_moduli sizing float_moduli_small_10" value="0" />\n\
                                            <input type="hidden" name="codice_iva[]" class="input_moduli sizing float_moduli_small_10" value="" />\n\
                                            <input type="hidden" name="idvocecomm[]" class="input_moduli sizing float_moduli_small_10" value="0" />\n\
                                            <a href="javascript:;" class="remove_button"><i style="color: #CD0A0A; line-height: 35px;" class="fa fa-times fa-lg" aria-hidden="true"></i></a>\n\
                                            <div class="chiudi"></div>\n\
                                            </div>');


                /**/
                var htmldom = "<div class=\"rigaprevcomm sizing rigadom_" + iddom + "\" >\n\
                            <div class=\"numpreventivo sizing\">&nbsp;</div>\n\
                            <div class=\"datapreventivo sizing\">&nbsp;</div>\n\
                            <div class=\"titolopreventivo sizing\">Dominio: " + msg['valori'][0]['dominio'] + " - " + msg['valori'][0]['descrizione'] + "</div>\n\
                            <div class=\"totalepreventivo sizing\">&euro; " + totaleprint + "</div>\n\
                            <div class=\"eliminapreventivo sizing\">\n\
                            <a href=\"javascript:;\" onclick=\"deldom('" + iddom + "');\"><i class=\"fa fa-times fa-lg\" aria-hidden=\"true\"></i></a>\n\
                            </div>\n\
                            <div class=\"chiudi\"></div>\n\
                            </div>";

                $('.nodominicoll').hide();
                $('.dominicollegati').append(htmldom);
                totalefattura();
            }
        });
    }
}

function deldom(id) {
    if (confirm("Stai per eliminare il contenuto, vuoi continuare?")) {
        $('.rigadom_' + id).remove();
        $('.dom' + id).show();
        totalefattura();
    }
}

function delprevcomm(id) {
    if (confirm("Stai per eliminare il contenuto, vuoi continuare?")) {
        $('.rigaprevcomm_' + id).remove();
        $('.prev' + id).show();
        totalefattura();
    }
}
/* commesse collegate */
function collegaCommessa(id) {
    if (id > 0) {
        $.ajax({
            type: "POST",
            url: "./fatture.php",
            data: "id=" + id + "&submit=collegacommessa",
            dataType: "json",
            success: function (msg) {
                $('.comm' + msg['valori'][0]['id']).hide();
                /* id commessa collegato */
                var idcomm = msg['valori'][0]['id'];
                /* data leggibile */
                var data = msg['valori'][0]['data'].split('-');
                /**/
                /* voci commessa */
                var vocicomm = "";
//            var vocipreffatt = "";
                for (var i = 0; i < msg['voci'].length; i++) {
                    if (msg['voci'][i].statovoce > 0) {
                        var colorred = "colorred";
                    } else {
                        colorred = "";
                    }
                    vocicomm += "<div class=\"" + colorred + " nomevoceprev sizing\">" + msg['voci'][i].nome + "</div>\n\
                             <div class=\"" + colorred + " descvoceprev sizing\">" + msg['voci'][i].descr + "</div>\n\
                             <div class=\"" + colorred + " qtavoceprev sizing\"></div>\n\
                             <div class=\"" + colorred + " prezzovoceprev sizing\">" + msg['voci'][i].prezzocliente + "</div>\n\
                             <div class=\"chiudi\"></div>";

                    var ivapercalcoli = parseFloat(100 + parseFloat(msg.ivadefault));
                    var totaleiva = parseFloat(msg['voci'][i].prezzocliente * ivapercalcoli / 100);

                    if (msg['voci'][i].statovoce > 0) {
                    } else {

                        $('.contienirighe').append('<div class="rigaadd riga_prodotto_fatt sizing rigacomm_' + idcomm + '">\n\
                                            <i class="fa fa-arrows-v fa-lg ordinarighe" aria-hidden="true"></i>\n\
                                            <select profit="profit' + i + '" name="idprofit[]" class="input_moduli sizing float_moduli_small_10" placeholder="Profit Center" title="Profit Center"><option value="0">Seleziona profit center</option>' + msg.vociprofit + '</select>\n\
                                            <textarea name="descrizione[]" class="nosortable textarea_moduli_small sizing float_moduli_40" placeholder="Descrizione" title="Descrizione">' + msg['voci'][i].nome + ' - ' + msg['voci'][i].descr + '</textarea>\n\
                                            <input type="text" name="um[]" class="input_moduli sizing float_moduli_small_10" placeholder="U.M." title="U.M." />\n\
                                            <input type="text" name="qta[]" class="qta input_moduli sizing float_moduli_small_5" placeholder="Q.ta" title="Q.ta" value="1" />\n\
                                            <input type="text" name="importo[]" class="importo input_moduli sizing float_moduli_small_15" placeholder="Importo" title="Importo" value="' + msg['voci'][i].prezzocliente + '" />\n\
                                            <input type="text" name="sconto[]" class="sconto input_moduli sizing float_moduli_small_10" placeholder="Sconto" title="Sconto" value="" />\n\
                                            <select name="iva[]"class="iva input_moduli sizing float_moduli_small_10" placeholder="Iva" title="Iva">' + msg.ivaselect + ' </select>\n\
                                            <input type="hidden" name="totalevoce[]" class="totalevoce input_moduli sizing float_moduli_small_10 nopost" value="' + msg['voci'][i].prezzocliente + '" />\n\
                                            <input type="hidden" name="totalevoceiva[]" class="totalevoceiva input_moduli sizing float_moduli_small_10 nopost" value="' + totaleiva + '" />\n\
                                            <input type="hidden" name="idprev[]" class="input_moduli sizing float_moduli_small_10" value="0" />\n\
                                            <input type="hidden" name="idfatt[]" class="input_moduli sizing float_moduli_small_10" value="0" />\n\
                                            <input type="hidden" name="idcomm[]" class="input_moduli sizing float_moduli_small_10" value="' + idcomm + '" />\n\
                                            <input type="hidden" name="codice_iva[]" class="input_moduli sizing float_moduli_small_10" value="" />\n\
                                            <input type="hidden" name="idvocecomm[]" class="input_moduli sizing float_moduli_small_10" value="' + msg['voci'][i].id + '" />\n\
                                            <a href="javascript:;" class="remove_button"><i style="color: #CD0A0A; line-height: 35px;" class="fa fa-times fa-lg" aria-hidden="true"></i></a>\n\
                                            <div class="chiudi"></div>\n\
                                            </div>');
                        $("[profit=profit" + i + "]").val(msg['voci'][i].idprofit);
                    }

                }
                /**/
                var htmlcomm = "<div class=\"rigaprevcomm sizing rigacomm_" + idcomm + "\" >\n\
                            <input type=\"hidden\" name=\"idcommfatt[]\" value=\"" + idcomm + "\" />\n\
                            <div class=\"numpreventivo sizing\">Comm. N° " + msg['valori'][0]['numero'] + "</div>\n\
                            <div class=\"datapreventivo sizing\">" + data[2] + "/" + data[1] + "/" + data[0] + "</div>\n\
                            <div class=\"titolopreventivo sizing\">" + msg['valori'][0]['titolo'] + "</div>\n\
                            <div class=\"totalepreventivo sizing\">&euro; " + msg['valori'][0]['totalecomm'] + "</div>\n\
                            <div class=\"eliminapreventivo sizing\">\n\
                            <a href=\"javascript:;\" onclick=\"javascript:$('.vocicomm_" + idcomm + "').slideToggle();\"><i class=\"fa fa-info-circle fa-lg\" aria-hidden=\"true\" style=\"color: #000000;\"></i></a> \n\
                            <a href=\"javascript:;\" onclick=\"delcommfatt('" + idcomm + "');\"><i class=\"fa fa-times fa-lg\" aria-hidden=\"true\"></i></a>\n\
                            </div>\n\
                            <div class=\"chiudi\"></div>\n\
                            <div class=\"vocicomm_" + idcomm + "\" style=\"display: none;\">\n\
                            " + vocicomm + "\n\
                            </div>\n\
                            <div class=\"chiudi\"></div>\n\
                            </div>";

                $('.nocommessecoll').hide();
                $('.commessecollegate').append(htmlcomm);
                totalefattura();
            }
        });
    }
}

function delcommfatt(id) {
    if (confirm("Stai per eliminare il contenuto, vuoi continuare?")) {
        $('.rigacomm_' + id).remove();
        $('.comm' + id).show();
        totalefattura();
    }
}
/* calcola scadenze */
function calcolascadenze() {
    var metodopagamento = $('#idpagamento').val();
    var finemese_vista = $('#finemese_vista').val();
    var datafatt = $('#data').val();
    var totalefatt_iva = $('#totalefatt_iva').val();
    if (parseFloat(totalefatt_iva) > 0 && datafatt.length > 0 && parseInt(metodopagamento) > 0) {
        $.ajax({
            type: "POST",
            url: "./fatture.php",
            data: "metodopagamento=" + metodopagamento + "&finemese_vista=" + finemese_vista + "&datafatt=" + datafatt + "&totalefatt_iva=" + totalefatt_iva + "&submit=calcoloscadenze",
            dataType: "json",
            success: function (msg) {
                if (msg.msg === "ko") {
                    alert(msg.msgko);
                } else {
                    $('#scadenzefattura').html(msg.scadenze);
                    $.datepicker.setDefaults($.datepicker.regional[ "it" ]);
                    $(".datascad").datepicker({
                    });
                }
            }
        });
    } else {
        $('#scadenzefattura').html("");
    }
}

/* preventivi collegati */
function collegaFattura(id) {
    if (id > 0) {
        $.ajax({
            type: "POST",
            url: "./fatture.php",
            data: "id=" + id + "&submit=collegafattura",
            dataType: "json",
            success: function (msg) {
                $('.fatt' + msg['valori'][0]['id']).hide();
                /* id fattura collegato */
                var idfatt = msg['valori'][0]['id'];
                /* data leggibile */
                var data = msg['valori'][0]['data'].split('-');
                /**/


                /* voci preventivo */
                var vocifatt = "";
//            var vocipreffatt = "";
                for (var i = 0; i < msg['voci'].length; i++) {

                    vocifatt += "<div class=\"nomevoceprev sizing\">&nbsp;</div>\n\
                             <div class=\"descvoceprev sizing\">" + msg['voci'][i].descrizione + "</div>\n\
                             <div class=\"qtavoceprev sizing\">" + msg['voci'][i].qta + "</div>\n\
                             <div class=\"prezzovoceprev sizing\">" + msg['voci'][i].importo + "</div>\n\
                             <div class=\"chiudi\"></div>";

                    var totale = oprighe(msg['voci'][i].qta, msg['voci'][i].importo, msg['voci'][i].sconto);
                    var ivapercalcoli = parseFloat(100 + parseFloat(msg['voci'][i].iva));
                    totaleiva = totale * ivapercalcoli / 100;

                    $('.contienirighe').append('<div class="rigaadd riga_prodotto_fatt sizing rigafatt_' + idfatt + '">\n\
                                            <i class="fa fa-arrows-v fa-lg ordinarighe" aria-hidden="true"></i>\n\
                                            <select profit="profit' + i + '" name="idprofit[]" class="input_moduli sizing float_moduli_small_10" placeholder="Profit Center" title="Profit Center"><option value="0">Seleziona profit center</option>' + msg.vociprofit + '</select>\n\
                                            <textarea name="descrizione[]" class="nosortable textarea_moduli_small sizing float_moduli_40" placeholder="Descrizione" title="Descrizione">' + msg['voci'][i].descrizione + '</textarea>\n\
                                            <input type="text" name="um[]" class="input_moduli sizing float_moduli_small_10" placeholder="U.M." title="U.M." value="' + msg['voci'][i].um + '" />\n\
                                            <input type="text" name="qta[]" class="qta input_moduli sizing float_moduli_small_5" placeholder="Q.ta" title="Q.ta" value="' + msg['voci'][i].qta + '" />\n\
                                            <input type="text" name="importo[]" class="importo input_moduli sizing float_moduli_small_15" placeholder="Importo" title="Importo" value="' + msg['voci'][i].importo + '" />\n\
                                            <input type="text" name="sconto[]" class="sconto input_moduli sizing float_moduli_small_10" placeholder="Sconto" title="Sconto" value="' + msg['voci'][i].sconto + '" />\n\
                                            <select name="iva[]" iva="iva' + i + '" class="iva input_moduli sizing float_moduli_small_10" placeholder="Iva" title="Iva">' + msg.ivaselect + ' </select>\n\
                                            <input type="hidden" name="totalevoce[]" class="totalevoce input_moduli sizing float_moduli_small_10 nopost" value="' + totale + '" />\n\
                                            <input type="hidden" name="totalevoceiva[]" class="totalevoceiva input_moduli sizing float_moduli_small_10 nopost" value="' + totaleiva + '" />\n\
                                            <input type="hidden" name="idprev[]" class="input_moduli sizing float_moduli_small_10" value="0" />\n\
                                            <input type="hidden" name="idfatt[]" class="input_moduli sizing float_moduli_small_10" value="' + idfatt + '" />\n\
                                            <input type="hidden" name="idcomm[]" class="input_moduli sizing float_moduli_small_10" value="0" />\n\
                                            <input type="hidden" name="idvocecomm[]" class="input_moduli sizing float_moduli_small_10" value="0" />\n\
                                            <a href="javascript:;" class="remove_button"><i style="color: #CD0A0A; line-height: 35px;" class="fa fa-times fa-lg" aria-hidden="true"></i></a>\n\
                                            <div class="chiudi"></div>\n\
                                            </div>');

                    $("[iva=iva" + i + "]").val(msg['voci'][i].iva);
                    $("[profit=profit" + i + "]").val(msg['voci'][i].idprofit);

                }
                /**/
                var htmlfatt = "<div class=\"rigaprevcomm sizing rigafatt_" + idfatt + "\" >\n\
                            <input type=\"hidden\" name=\"idprevfatt[]\" value=\"" + idfatt + "\" />\n\
                            <div class=\"numpreventivo sizing\">Fatt. N° " + msg['valori'][0]['numero'] + "</div>\n\
                            <div class=\"datapreventivo sizing\">" + data[2] + "/" + data[1] + "/" + data[0] + "</div>\n\
                            <div class=\"titolopreventivo sizing\"> &nbsp; </div>\n\
                            <div class=\"totalepreventivo sizing\">&euro; " + msg['valori'][0]['totalefatt_iva'] + "</div>\n\
                            <div class=\"eliminapreventivo sizing\">\n\
                            <a href=\"javascript:;\" onclick=\"javascript:$('.vocifatt_" + idfatt + "').slideToggle();\"><i class=\"fa fa-info-circle fa-lg\" aria-hidden=\"true\" style=\"color: #000000;\"></i></a> \n\
                            <a href=\"javascript:;\" onclick=\"delfatt('" + idfatt + "');\"><i class=\"fa fa-times fa-lg\" aria-hidden=\"true\"></i></a>\n\
                            </div>\n\
                            <div class=\"chiudi\"></div>\n\
                            <div class=\"vocifatt_" + idfatt + "\" style=\"display: none;\">\n\
                            " + vocifatt + "\n\
                            </div>\n\
                            <div class=\"chiudi\"></div>\n\
                            </div>";

                $('.nofattcoll').hide();
                $('.fatturecollegate').append(htmlfatt);
                totalefattura();
            }
        });
    }
}

function delfatt(id) {
    if (confirm("Stai per eliminare il contenuto, vuoi continuare?")) {
        $('.rigafatt_' + id).remove();
        $('.fatt' + id).show();
        totalefattura();
    }
}

/* aggiungi acquisto */
function aggiungiAcquisto() {
    $('.showcont').show().load('./form/form-fattureacquisto.php', function () {
        /* sortable righe */
        $('.contienirighe').sortable({
            disabled: false,
            cancel: '.nosortable, input, select',
            cursor: 'move'
        });
        /**/
        /**/
        $.validator.messages.required = '';
        $("#formfattura").validate({
            submitHandler: function () {
                $("#submitformfattura").ready(function () {
                    var datastring = $("#formfattura *").not(".nopost").serialize();
                    var stato = $('#stato').val();
                    $.ajax({
                        type: "POST",
                        url: "./fatture.php",
                        data: datastring + "&submit=submitformfattura",
                        dataType: "json",
                        success: function (msg) {
                            if (msg.msg === "ko") {
                                alert(msg.msgko);
                            } else {
                                location.href = '/fatture.php?op=acquisto';
                            }
                        }
                    });
                });
            }
        });
    });
}
/* aggiorna acquisto */
function aggiornaAcquisto(id, filtri, arch, paginastart) {
    $('.showcont').show().load('./form/form-fattureacquisto.php', function () {
        $('.bottone_chiudi').html("<a href=\"javascript:;\" class=\"sizing\" onclick=\"mostraFatture('" + arch + "', '" + filtri + "', '" + paginastart + "');\">Chiudi</a>");
        /*richiama dati*/
        $.ajax({
            type: "POST",
            url: "./fatture.php",
            data: "id=" + id + "&submit=richiamafattura",
            dataType: "json",
            success: function (msg) {
                /* riempio i campi */
                $.each(msg['valori'][0], function (index, value) {
                    $("#" + index).val(value);
                });
                var data = $('#data').val().split('-');
                $('#datap').val(data[2] + "/" + data[1] + "/" + data[0]);
                for (var i = 0; i < msg['voci'].length; i++) {

                    var totale = oprighe(msg['voci'][i].qta, msg['voci'][i].importo, msg['voci'][i].sconto);
                    var ivapercalcoli = parseFloat(100 + parseFloat(msg['voci'][i].iva));
                    totaleiva = totale * ivapercalcoli / 100;

                    $('.contienirighe').append('<div class="rigaadd riga_prodotto_fatt sizing">\n\
                                            <i class="fa fa-arrows-v fa-lg ordinarighe" aria-hidden="true"></i>\n\
                                            <select profit="profit' + i + '" name="idprofit[]" class="input_moduli sizing float_moduli_small_10" placeholder="Profit Center" title="Profit Center"><option value="0">Seleziona profit center</option>' + msg.vociprofit + '</select>\n\
                                            <input type="text" name="descrizione[]" class="input_moduli sizing float_moduli_40" placeholder="Descrizione" title="Descrizione" value="' + msg['voci'][i].descrizione + '" />\n\
                                            <input type="text" name="um[]" class="input_moduli sizing float_moduli_small_10" placeholder="U.M." title="U.M." value="' + msg['voci'][i].um + '" />\n\
                                            <input type="text" name="qta[]" class="qta input_moduli sizing float_moduli_small_5" placeholder="Q.ta" title="Q.ta" value="' + msg['voci'][i].qta + '" />\n\
                                            <input type="text" name="importo[]" class="importo input_moduli sizing float_moduli_small_15" placeholder="Importo" title="Importo" value="' + msg['voci'][i].importo + '" />\n\
                                            <input type="text" name="sconto[]" class="sconto input_moduli sizing float_moduli_small_10" placeholder="Sconto" title="Sconto" value="' + msg['voci'][i].sconto + '" />\n\
                                            <select name="iva[]" iva="iva' + i + '" class="iva input_moduli sizing float_moduli_small_10" placeholder="Iva" title="Iva">' + msg.ivaselect + ' </select>\n\
                                            <input type="hidden" name="totalevoce[]" class="totalevoce input_moduli sizing float_moduli_small_10 nopost" value="' + totale + '" />\n\
                                            <input type="hidden" name="totalevoceiva[]" class="totalevoceiva input_moduli sizing float_moduli_small_10 nopost" value="' + totaleiva + '" />\n\
                                            <input type="hidden" name="idprev[]" class="input_moduli sizing float_moduli_small_10" value="' + msg['voci'][i].idprev + '" />\n\
                                            <input type="hidden" name="idfatt[]" class="input_moduli sizing float_moduli_small_10" value="' + msg['voci'][i].idfatt + '" />\n\
                                            <input type="hidden" name="idcomm[]" class="input_moduli sizing float_moduli_small_10" value="' + msg['voci'][i].idcomm + '" />\n\
                                            <input type="hidden" name="idvocecomm[]" class="input_moduli sizing float_moduli_small_10" value="' + msg['voci'][i].idvocecomm + '" />\n\
                                            <a href="javascript:;" class="remove_button"><i style="color: #CD0A0A; line-height: 35px;" class="fa fa-times fa-lg" aria-hidden="true"></i></a>\n\
                                            <div class="chiudi"></div>\n\
                                            </div>');
                    $("[iva=iva" + i + "]").val(msg['voci'][i].iva);
                    $("[profit=profit" + i + "]").val(msg['voci'][i].idprofit);
                }
                $('#scadenzefattura').html(msg.scadenze);
                $.datepicker.setDefaults($.datepicker.regional[ "it" ]);
                $(".datascad").datepicker({
                });
                $('#mostraaggiornascadenzefattura').show();
                /* cerca preventivi e fatture per quel cliente */
                $.ajax({
                    type: "POST",
                    url: "./fatture.php",
                    data: "idcliente=" + msg['valori'][0]['idcliente'] + "&submit=preventivicommesseokcliente",
                    dataType: "json",
                    success: function (res)
                    {
                        /* preventivi */
                        var optionpreventivi = "";
                        for (var i = 0; i < res.preventivi.length; i++) {
                            optionpreventivi += "<option class=\"preventivicliente prev" + res.preventivi[i].id + " \" value=\"" + res.preventivi[i].id + "\">" + res.preventivi[i].numero + " - " + res.preventivi[i].datait + " " + res.preventivi[i].titolo + "</option>";
                        }
                        $('.preventivicliente').remove();
                        $('.collegapreventivo').append(optionpreventivi);
                        /* commesse */
                        var optioncommesse = "";
                        for (var i = 0; i < res.commesse.length; i++) {
                            optioncommesse += "<option class=\"commessecliente comm" + res.commesse[i].id + " \" value=\"" + res.commesse[i].id + "\">" + res.commesse[i].numero + " - " + res.commesse[i].datait + " " + res.commesse[i].titolo + "</option>";
                        }
                        $('.commessecliente').remove();
                        $('.collegacommessa').append(optioncommesse);
                    }
                });
            }
        });
        /* sortable righe */
        $('.contienirighe').sortable({
            disabled: false,
            cancel: '.nosortable, input, select',
            cursor: 'move'
        });
        /**/
        $.validator.messages.required = '';
        $("#formfattura").validate({
            submitHandler: function () {
                $("#submitformfattura").ready(function () {
                    var datastring = $("#formfattura *").not(".nopost").serialize();
                    var stato = $('#stato').val();
                    $.ajax({
                        type: "POST",
                        url: "./fatture.php",
                        data: datastring + "&submit=editformfattura",
                        dataType: "json",
                        success: function (msg) {
//                            if (msg.msg === "ko") {
//                                alert(msg.msgko);
//                            } else {
//                                location.href = '/fatture.php?op=acquisto';
//                            }
                            mostraFatture(arch, filtri, paginastart);
                        }
                    });
                });
            }
        });
    });
}

/* setta tipo di fattura */

function settaTipo(tipo) {
    if (tipo == 3) {
        $('#boxcontieninotacredito').show();
        $('#boxcontienipreventivi').hide();
        $('#boxcontienicommesse').hide();
        $('#boxcontienidomini').hide();
    } else {
        $('#boxcontieninotacredito').hide();
        $('#boxcontienipreventivi').show();
        $('#boxcontienicommesse').show();
        $('#boxcontienidomini').show();
    }
}