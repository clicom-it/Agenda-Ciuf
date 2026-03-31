function roundTo(value, decimals) {
    var i = value * Math.pow(10, decimals);
    i = Math.round(i);
    return i / Math.pow(10, decimals);
}

function mostraDDT(arch, filtri, paginastart) {

    if (paginastart) {
        var pagestart = parseInt(paginastart);
    }

    if (filtri) {
        var filtriarr = filtri.split("||");
        var filtro0 = {numero: filtriarr[0]};
        var filtro1 = {datait: filtriarr[1]};
        var filtro2 = {partenza: filtriarr[2]};
        var filtro3 = {daticliente: filtriarr[3]};
        var filtro4 = {destinazione: filtriarr[4]};
        var filtro5 = {causale: filtriarr[5]};
        var filtro6 = {colli: filtriarr[6]};
        var filtro7 = {peso: filtriarr[7]};
        var filtro8 = {stato: filtriarr[8]};
    }

    $.ajax({
        type: "POST",
        url: "./ddt.php",
        data: "arch=" + arch + "&submit=mostraddt",
        dataType: "json",
        success: function (msg) {
            var db = {
                loadData: function (filter) {
                    return $.grep(this.clients, function (client) {
                        return (!filter.numero || client.numero.indexOf(filter.numero) > -1)
                                && (!filter.datait || client.datait.indexOf(filter.datait) > -1)
                                && (!filter.partenza || client.partenza.indexOf(filter.partenza) > -1)
                                && (!filter.daticliente.toLowerCase() || client.daticliente.toLowerCase().indexOf(filter.daticliente.toLowerCase()) > -1)
                                && (!filter.destinazione.toLowerCase() || client.destinazione.toLowerCase().indexOf(filter.destinazione.toLowerCase()) > -1)
                                && (!filter.causale.toLowerCase() || client.causale.toLowerCase().indexOf(filter.causale.toLowerCase()) > -1)
                                && (!filter.colli.toLowerCase() || client.colli.toLowerCase().indexOf(filter.colli.toLowerCase()) > -1)
                                && (!filter.peso || client.peso.indexOf(filter.peso) > -1)
                                && (!filter.stato || client.stato.indexOf(filter.stato) > -1);
                    });
                }
            };

            window.db = db;

            db.clients = msg.dati;
            jsGrid.locale("it");
            var campi = [
                {name: "numero", type: "text", title: "<b>Num.</b>", css: "customRow", width: "20", filterTemplate: createTextFilterTemplate("numero", filtro0)},
                {name: "datait", type: "text", title: "<b>Data</b>", css: "customRow", width: "30", filterTemplate: createTextFilterTemplate("datait", filtro1)},
                {name: "partenza", type: "text", title: "<b>Partenza</b>", css: "customRow", width: "50", filterTemplate: createTextFilterTemplate("partenza", filtro2)},
                {name: "daticliente", type: "text", title: "<b>Cliente</b>", css: "customRow", filterTemplate: createTextFilterTemplate("daticliente", filtro3)},
                {name: "destinazione", type: "text", title: "<b>Destinazione</b>", css: "customRow", filterTemplate: createTextFilterTemplate("destinazione", filtro4)},
                {name: "causale", type: "text", title: "<b>Causale</b>", css: "customRow", width: "30", filterTemplate: createTextFilterTemplate("causale", filtro5)},
                {name: "colli", type: "text", title: "<b>Colli</b>", css: "customRow", width: "30", filterTemplate: createTextFilterTemplate("colli", filtro6)},
                {name: "peso", type: "text", title: "<b>Peso</b>", css: "customRow", width: "30", filterTemplate: createTextFilterTemplate("peso", filtro7)},
                {name: "stato", type: "select", items: [
                        {name: "Seleziona stato", Id: ""},
                        {name: "Fatturato", Id: "4"},
                        {name: "Archiviato", Id: "5"}
                    ], title: "<b>Stato</b>", valueField: "Id", textField: "name", css: "customRow", width: "50", filterTemplate: createSelectFilterTemplate("stato", filtro8)},
                {type: "control", width: "30", itemTemplate: function (value, item) {
                        var $result = jsGrid.fields.control.prototype.itemTemplate.apply(this, arguments);
                        var $myButton = $("<i style=\"margin-left: 5px; color: #2E65A1;\"class=\"fa fa-files-o fa-lg\" aria-hidden=\"true\"></i>")
                                .click(function (e) {
                                    var filtri = $("#jsGrid").jsGrid("getFilter").numero + "||" + $("#jsGrid").jsGrid("getFilter").datait + "||"
                                            + $("#jsGrid").jsGrid("getFilter").partenza + "||"
                                            + $("#jsGrid").jsGrid("getFilter").daticliente + "||" + $("#jsGrid").jsGrid("getFilter").destinazione
                                            + "||" + $("#jsGrid").jsGrid("getFilter").causale + "||" + $("#jsGrid").jsGrid("getFilter").colli
                                            + "||" + $("#jsGrid").jsGrid("getFilter").peso + "||" + $("#jsGrid").jsGrid("getFilter").stato;
                                    copyDDT(item.id, filtri, arch);
                                    e.stopPropagation();
                                });
                        var $myButton2 = $("<i style=\"margin-left: 5px; color: #ff0000;\"class=\"fa fa-file-pdf-o fa-lg\" aria-hidden=\"true\"></i>")
                                .click(function (e) {
                                    var idd = item.id;
                                    window.open('./pdf/ddt.php?idddt=' + idd + '', '_blank');
                                    e.stopPropagation();
                                });
                        return $result.add($myButton).add($myButton2);
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
                pageSize: 10,
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
                        url: "./ddt.php",
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
                            + $("#jsGrid").jsGrid("getFilter").partenza + "||"
                            + $("#jsGrid").jsGrid("getFilter").daticliente + "||" + $("#jsGrid").jsGrid("getFilter").destinazione
                            + "||" + $("#jsGrid").jsGrid("getFilter").causale + "||" + $("#jsGrid").jsGrid("getFilter").colli
                            + "||" + $("#jsGrid").jsGrid("getFilter").peso + "||" + $("#jsGrid").jsGrid("getFilter").stato;

                    var paginastart = parseInt($('.jsgrid-pager-current-page').html());

                    var ide = args.item.id;
                    aggiorna(ide, filtri, arch, paginastart);
                }
            });
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

    $('.showcont').show().load('./form/form-ddt.php', function () {
        /* sortable righe */
        $('.contienirighe').sortable({
            disabled: false,
            cancel: '.nosortable, input',
            cursor: 'move'
        });
        /**/
        $.validator.messages.required = '';
        $("#formddt").validate({
            submitHandler: function () {
                $("#submitformddt").ready(function () {
                    var datastring = $("#formddt *").not(".nopost").serialize();
                    var stato = $('#stato').val();
                    $.ajax({
                        type: "POST",
                        url: "./ddt.php",
                        data: datastring + "&submit=submitformddt",
                        dataType: "json",
                        success: function (msg) {
                            if (msg.msg === "ko") {
                                alert(msg.msgko);
                            } else {
                                if (stato === '5' || stato === '4') {
                                    location.href = '/ddt.php?op=archivio';
                                } else {
                                    location.href = '/ddt.php?op=preventivi';
                                }
                            }
                        }
                    });
                });
            }
        });
    });
}

function aggiorna(id, filtri, arch, paginastart) {

    /*richiama dati*/
    $.ajax({
        type: "POST",
        url: "./ddt.php",
        data: "id=" + id + "&submit=richiamaddt",
        dataType: "json",
        success: function (msg) {

            $('.showcont').show().load('./form/form-ddt.php', function () {


                $('.bottone_chiudi').html("<a href=\"javascript:;\" class=\"sizing\" onclick=\"mostraDDT('" + arch + "', '" + filtri + "', '" + paginastart + "');\">Chiudi</a>");

                /* riempio i campi */
                $.each(msg['valori'][0], function (index, value) {
                    $("#" + index).val(value);
                });
                var data = $('#data').val().split('-');
                $('#datap').val(data[2] + "/" + data[1] + "/" + data[0]);
                for (var i = 0; i < msg['voci'].length; i++) {
                    $('.contienirighe').append('<div class="rigaadd riga_prodotto_prev sizing">\n\
                                                <i class="fa fa-arrows-v fa-lg ordinarighe" aria-hidden="true"></i><input type="text" name="nome[]" value="' + msg['voci'][i].nome + '" class="input_moduli sizing float_moduli_small nomepz" placeholder="Cerca (codice o titolo)" title="Cerca (codice o titolo)" />\n\
                                                <textarea name="descr[]" class="nosortable textarea_moduli_small sizing float_moduli_40" placeholder="Descrizione" title="Descrizione">' + msg['voci'][i].descr + '</textarea>\n\
                                                <input type="text" name="qta[]" value="' + msg['voci'][i].qta + '" class="qta input_moduli sizing float_moduli_small_10" placeholder="Q.ta" title="Q.ta" />\n\
                                                <input type="text" name="prezzo[]" value="' + msg['voci'][i].prezzo + '" class="prezzo input_moduli sizing float_moduli_small_10" placeholder="Prezzo" title="Prezzo" />\n\
                                                <input type="text" name="sconto[]" value="' + msg['voci'][i].sconto + '" class="sconto input_moduli sizing float_moduli_small_10" placeholder="Sconto" title="Sconto" />\n\
                                                <input type="text" name="scontato[]" value="' + msg['voci'][i].scontato + '" class="scontato input_moduli sizing float_moduli_small_10" placeholder="Totale" title="Totale" /><a href="javascript:;" class="remove_button"><i style="color: #CD0A0A; line-height: 35px;" class="fa fa-times fa-lg" aria-hidden="true"></i></a>\n\
                                                <div class="chiudi"></div>\n\
                                                </div>');
                }
                /* sortable righe */
                $('.contienirighe').sortable({
                    disabled: false,
                    cancel: '.nosortable, input',
                    cursor: 'move'
                });
                /**/

                var magazz = $.map(msg.magazzino, function (item) {
                    return {
                        label: item.codice + " " + item.titolo,
                        id: item.id,
                        codice: item.codice,
                        descrizione: item.titolo + "\n" + item.descrizione,
                        prezzo: item.prezzo
                    };

                });
                $(".nomepz").autocomplete({
                    source: magazz,
                    select: function (event, ui) {
                        event.preventDefault();
                        $(this).val(ui.item.codice.trim());
                        $(this).next('textarea').val(ui.item.descrizione.trim());
                        $(this).next('textarea').next('input').next('input').val(ui.item.prezzo);
                        $(this).next('textarea').next('input').next('input').next('input').val(0);
                        $(this).next('textarea').next('input').next('input').next('input').next('input').val(ui.item.prezzo);
                        totalepreventivo();
                    }
                });

                $.validator.messages.required = '';
                $("#formddt").validate({
                    submitHandler: function () {
                            $("#submitformddt").ready(function () {
                                var datastring = $("#formddt *").not(".nopost").serialize();
                                var stato = $('#stato').val();
                                $.ajax({
                                    type: "POST",
                                    url: "./ddt.php",
                                    data: datastring + "&submit=editformddt",
                                    dataType: "json",
                                    success: function (msg) {
                                        if (msg.msg === "ko") {
                                            alert(msg.msgko);
                                        } else {
                                            mostraDDT(arch, filtri, paginastart);
                                        }
                                    }
                                });
                            });
         

                    }
                });
            });


        }
    });

}

function oprighe(qta, prezzo, sconto) {
    var totale = qta * prezzo - qta * prezzo * sconto / 100;
    return roundTo(totale, 2);
}

function copyDDT(id, filtri, arch) {
    $.ajax({
        type: "POST",
        url: "./ddt.php",
        data: "id=" + id + "&submit=copyddt",
        dataType: "json",
        success: function (msg) {
            if (msg.msg === "ko") {
                alert(msg.msgko);
            } else {
                alert("DDT creato con successo!");
                mostraDDT(arch, filtri);
            }
        }
    });
}

/* orologio */
function Orario()
{
    var data = new Date();
    var giorno = data.getDate();
    var mese = data.getMonth() + 1;
    var yy = data.getFullYear();
    var hh = data.getHours();
    var mm = data.getMinutes();
    var ss = data.getSeconds();
    $('#partenza').val(giorno + "/" + mese + "/" + yy + " " + hh + ":" + mm + ":" + ss);
    window.setTimeout("Orario()", 1000);
}