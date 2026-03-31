function roundTo(value, decimals) {
    var i = value * Math.pow(10, decimals);
    i = Math.round(i);
    return i / Math.pow(10, decimals);
}

function mostraPreventivi(arch, filtri, paginastart) {
    tinymce.remove("#descrizione");
    tinymce.remove("#descrizione1");
    tinymce.remove("#descrizione2");

    if (paginastart) {
        var pagestart = parseInt(paginastart);
    }

    if (filtri) {
        var filtriarr = filtri.split("||");
        var filtro0 = {numero: filtriarr[0]};
        var filtro1 = {rev: filtriarr[1]};
        var filtro2 = {datait: filtriarr[2]};
        var filtro3 = {daticliente: filtriarr[3]};
        var filtro4 = {titolo: filtriarr[4]};
        var filtro5 = {tempi_consegna: filtriarr[5]};
        var filtro6 = {pagamento: filtriarr[6]};
        var filtro7 = {totaleprev: filtriarr[7]};
        var filtro8 = {totalescontatoprev: filtriarr[8]};
        var filtro9 = {stato: filtriarr[9]};
    }

    $.ajax({
        type: "POST",
        url: "./preventivi.php",
        data: "arch=" + arch + "&submit=mostrapreventivi",
        dataType: "json",
        success: function (msg) {
            var db = {
                loadData: function (filter) {
                    return $.grep(this.clients, function (client) {
                        return (!filter.numero || client.numero.indexOf(filter.numero) > -1)
                                && (!filter.rev || client.rev.indexOf(filter.rev) > -1)
                                && (!filter.datait || client.datait.indexOf(filter.datait) > -1)
                                && (!filter.daticliente.toLowerCase() || client.daticliente.toLowerCase().indexOf(filter.daticliente.toLowerCase()) > -1)
                                && (!filter.titolo.toLowerCase() || client.titolo.toLowerCase().indexOf(filter.titolo.toLowerCase()) > -1)
                                && (!filter.tempi_consegna.toLowerCase() || client.tempi_consegna.toLowerCase().indexOf(filter.tempi_consegna.toLowerCase()) > -1)
                                && (!filter.pagamento.toLowerCase() || client.pagamento.toLowerCase().indexOf(filter.pagamento.toLowerCase()) > -1)
                                && (!filter.totaleprev || client.totaleprev.indexOf(filter.totaleprev) > -1)
                                && (!filter.totalescontatoprev || client.totalescontatoprev.indexOf(filter.totalescontatoprev) > -1)
                                && (!filter.stato || client.stato.indexOf(filter.stato) > -1);
                    });
                }
            };

            window.db = db;

            db.clients = msg.dati;
            jsGrid.locale("it");
            var campi = [
                {name: "numero", type: "text", title: "<b>Num.</b>", css: "customRow", width: "30", filterTemplate: createTextFilterTemplate("numero", filtro0)},
                {name: "rev", type: "text", title: "<b>Rev.</b>", css: "customRow", width: "30", filterTemplate: createTextFilterTemplate("rev", filtro1)},
                {name: "datait", type: "text", title: "<b>Data</b>", css: "customRow", width: "40", filterTemplate: createTextFilterTemplate("datait", filtro2)},
                {name: "daticliente", type: "text", title: "<b>Cliente</b>", css: "customRow", filterTemplate: createTextFilterTemplate("daticliente", filtro3)},
                {name: "titolo", type: "text", title: "<b>Titolo</b>", css: "customRow", filterTemplate: createTextFilterTemplate("titolo", filtro4)},
                {name: "tempi_consegna", type: "text", title: "<b>Tempi di consegna</b>", css: "customRow", filterTemplate: createTextFilterTemplate("tempi_consegna", filtro5)},
                {name: "pagamento", type: "text", title: "<b>Pagamento</b>", css: "customRow", filterTemplate: createTextFilterTemplate("pagamento", filtro6)},
                {name: "totaleprev", type: "text", title: "<b>Totale</b>", css: "customRow", width: "60", filterTemplate: createTextFilterTemplate("totaleprev", filtro7)},
                {name: "totalescontatoprev", type: "text", title: "<b>Totale scontato</b>", css: "customRow", width: "60", filterTemplate: createTextFilterTemplate("totalescontatoprev", filtro8)},
                {name: "stato", type: "select", items: [
                        {name: "Seleziona stato", Id: ""},
                        {name: "Da inviare", Id: "0"},
                        {name: "Inviato", Id: "1"},
                        {name: "Confermato", Id: "2"},
                        {name: "Da risentire", Id: "3"},
                        {name: "Fatturato", Id: "4"},
                        {name: "Archiviato", Id: "5"},
                    ], title: "<b>Stato</b>", valueField: "Id", textField: "name", css: "customRow", width: "60", filterTemplate: createSelectFilterTemplate("stato", filtro9)},
                {type: "control", itemTemplate: function (value, item) {
                        var $result = jsGrid.fields.control.prototype.itemTemplate.apply(this, arguments);
                        var $myButton = $("<i style=\"margin-left: 5px; color: #2E65A1;\"class=\"fa fa-files-o fa-lg\" aria-hidden=\"true\"></i>")
                                .click(function (e) {
                                    var filtri = $("#jsGrid").jsGrid("getFilter").numero + "||" + $("#jsGrid").jsGrid("getFilter").rev + "||"
                                            + $("#jsGrid").jsGrid("getFilter").datait + "||"
                                            + $("#jsGrid").jsGrid("getFilter").daticliente + "||" + $("#jsGrid").jsGrid("getFilter").titolo
                                            + "||" + $("#jsGrid").jsGrid("getFilter").tempi_consegna + "||" + $("#jsGrid").jsGrid("getFilter").pagamento
                                            + "||" + $("#jsGrid").jsGrid("getFilter").totaleprev + "||" + $("#jsGrid").jsGrid("getFilter").totalescontatoprev
                                            + "||" + $("#jsGrid").jsGrid("getFilter").stato;
                                    copyPrev(item.id, filtri, arch);
                                    e.stopPropagation();
                                });
                        var $myButton2 = $("<i style=\"margin-left: 5px; color: #ff0000;\"class=\"fa fa-file-pdf-o fa-lg\" aria-hidden=\"true\"></i>")
                                .click(function (e) {
                                    var idd = item.id;
                                    window.open('./pdf/preventivo.php?idprev=' + idd + '', '_blank');
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
                        url: "./preventivi.php",
                        data: "id=" + idd + "&submit=delete",
                        dataType: "json",
                        success: function () {
                        }
                    });
                },
                // edita item
                onItemEditing: function (args) {
                    args.cancel = true;
                    var filtri = $("#jsGrid").jsGrid("getFilter").numero + "||" + $("#jsGrid").jsGrid("getFilter").rev + "||"
                            + $("#jsGrid").jsGrid("getFilter").datait + "||"
                            + $("#jsGrid").jsGrid("getFilter").daticliente + "||" + $("#jsGrid").jsGrid("getFilter").titolo
                            + "||" + $("#jsGrid").jsGrid("getFilter").tempi_consegna + "||" + $("#jsGrid").jsGrid("getFilter").pagamento
                            + "||" + $("#jsGrid").jsGrid("getFilter").totaleprev + "||" + $("#jsGrid").jsGrid("getFilter").totalescontatoprev
                            + "||" + $("#jsGrid").jsGrid("getFilter").stato;

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
    /* editor */
    tinymce.remove("#descrizione");
    tinymce.remove("#descrizione1");
    tinymce.remove("#descrizione2");
    $('.showcont').show().load('./form/form-preventivo.php', function () {
        /* sortable righe */
        $('.contienirighe').sortable({
            disabled: false,
            cancel: '.nosortable, input',
            cursor: 'move'
        });
        /**/
        /**/
        initEditor('descrizione');
        initEditor('descrizione1');
        initEditor('descrizione2');
        /**/
        $.validator.messages.required = '';
        $("#formpreventivo").validate({
            submitHandler: function () {
                $("#submitformpreventivo").ready(function () {
                    var datastring = $("#formpreventivo *").not(".nopost").serialize();
                    var stato = $('#stato').val();
                    $.ajax({
                        type: "POST",
                        url: "./preventivi.php",
                        data: datastring + "&submit=submitformpreventivi",
                        dataType: "json",
                        success: function (msg) {
                            if (msg.msg === "ko") {
                                alert(msg.msgko);
                            } else {
                                if (stato === '5' || stato === '4') {
                                    location.href = '/preventivi.php?op=archivio';
                                } else {
                                    location.href = '/preventivi.php?op=preventivi';
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

    /*richiama dati*/
    $.ajax({
        type: "POST",
        url: "./preventivi.php",
        data: "id=" + id + "&submit=richiamapreventivo",
        dataType: "json",
        success: function (msg) {

            $('.showcont').show().load('./form/form-preventivo.php', function () {

                /* editor */
                tinymce.remove("#descrizione");
                tinymce.remove("#descrizione1");
                tinymce.remove("#descrizione2");

                $('.bottone_chiudi').html("<a href=\"javascript:;\" class=\"sizing\" onclick=\"mostraPreventivi('" + arch + "', '" + filtri + "', '" + paginastart + "');\">Chiudi</a>");

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
                $('#submitformpreventivorevisione').show();
                /* sortable righe */
                $('.contienirighe').sortable({
                    disabled: false,
                    cancel: '.nosortable, input',
                    cursor: 'move'
                });
                /**/

                initEditor('descrizione');
                initEditor('descrizione1');
                initEditor('descrizione2');


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

                var action = '';
                $.validator.messages.required = '';
                $("#formpreventivo").validate({
                    submitHandler: function () {
                        if (action === 'Salva') {
                            $("#submitformpreventivo").ready(function () {
                                var datastring = $("#formpreventivo *").not(".nopost").serialize();
                                var stato = $('#stato').val();
                                $.ajax({
                                    type: "POST",
                                    url: "./preventivi.php",
                                    data: datastring + "&submit=editformpreventivo",
                                    dataType: "json",
                                    success: function (msg) {
                                        if (msg.msg === "ko") {
                                            alert(msg.msgko);
                                        } else {
//                                            if (stato === '5' || stato === '4') {
//                                                mostraPreventivi(1, filtri);
//                                            } else {
//                                                mostraPreventivi('', filtri);
//                                            }
                                            mostraPreventivi(arch, filtri, paginastart);
                                        }
                                    }
                                });
                            });
                        } else {
                            $("#submitformpreventivo").ready(function () {
                                var datastring = $("#formpreventivo *").not(".nopost").serialize();
                                var stato = $('#stato').val();
                                $.ajax({
                                    type: "POST",
                                    url: "./preventivi.php",
                                    data: datastring + "&revisione=1&submit=submitformpreventivi",
                                    dataType: "json",
                                    success: function (msg) {
                                        if (msg.msg === "ko") {
                                            alert(msg.msgko);
                                        } else {
//                                            if (stato === '5' || stato === '4') {
//                                                location.href = '/preventivi.php?op=archivio';
//                                            } else {
//                                                location.href = '/preventivi.php?op=preventivi';
//                                            }
                                            mostraPreventivi(arch, filtri, paginastart);
//                                            var numsuccessivo = parseInt($('#numero').val()) + 1;
//                                            $('#formpreventivo').trigger('reset');
//                                            $('.rigaadd').remove();
//                                            $('#numero').val(numsuccessivo);
//                                            $('body,html').animate({scrollTop: 0}, 500);
//                                            $('#messaggio').slideToggle('fast').delay(2000).slideToggle('slow');
                                        }
                                    }
                                });
                            });
                        }

                    }
                });
                $('#formpreventivo :submit').click(function () {
                    action = $(this).val();
                });
            });


        }
    });

}

function oprighe(qta, prezzo, sconto) {
    var totale = qta * prezzo - qta * prezzo * sconto / 100;
    return roundTo(totale, 2);
}

function totalepreventivo() {
    var somma = 0;
    $('.scontato').each(function () {
        somma += parseFloat(this.value);
    });
    var scontoulteriore = $('#scontoprev').val();
    $('#totaleprev').val(roundTo(somma, 2));
    if (scontoulteriore) {
        var totalescontato = somma - somma * scontoulteriore / 100;
        $('#totalescontatoprev').val(roundTo(totalescontato, 2));
    }
}

function copyPrev(id, filtri, arch) {
    $.ajax({
        type: "POST",
        url: "./preventivi.php",
        data: "id=" + id + "&submit=copyprev",
        dataType: "json",
        success: function (msg) {
            if (msg.msg === "ko") {
                alert(msg.msgko);
            } else {
                alert("Preventivo creato con successo!");
                mostraPreventivi(arch, filtri);
            }
        }
    });
}