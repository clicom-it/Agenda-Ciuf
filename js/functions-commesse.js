function roundTo(value, decimals) {
    var i = value * Math.pow(10, decimals);
    i = Math.round(i);
    return i / Math.pow(10, decimals);
}

function numberFormat(number, decimals, dec_point, thousands_sep)
{
    number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
    var n = !isFinite(+number) ? 0 : +number,
            prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
            sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
            dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
            s = '',
            toFixedFix = function (n, prec)
            {
                var k = Math.pow(10, prec);
                return '' + Math.round(n * k) / k;
            };
    // Fix for IE parseFloat(0.55).toFixed(0) = 0;
    s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
    if (s[0].length > 3)
    {
        s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
    }
    if ((s[1] || '').length < prec)
    {
        s[1] = s[1] || '';
        s[1] += new Array(prec - s[1].length + 1).join('0');
    }
    return s.join(dec);
}

function mostraCommesse(arch, filtri, paginastart) {

    if (paginastart) {
        var pagestart = parseInt(paginastart);
    }

    if (filtri) {
        var filtriarr = filtri.split("||");
        var filtro0 = {numero: filtriarr[0]};
        var filtro1 = {datait: filtriarr[1]};
        var filtro2 = {daticliente: filtriarr[2]};
        var filtro3 = {titolo: filtriarr[3]};
        var filtro4 = {totalecomm: filtriarr[4]};
        var filtro5 = {margine: filtriarr[5]};
        var filtro6 = {orelavorate: filtriarr[6]};
        var filtro7 = {stato: filtriarr[7]};
        var filtro8 = {costifornitori: filtriarr[8]};
    }

    $.ajax({
        type: "POST",
        url: "./commesse.php",
        data: "arch=" + arch + "&submit=mostracommesse",
        dataType: "json",
        success: function (msg) {
            var db = {
                loadData: function (filter) {
                    return $.grep(this.clients, function (client) {
                        return (!filter.numero || client.numero.indexOf(filter.numero) > -1)
                                && (!filter.datait || client.datait.indexOf(filter.datait) > -1)
                                && (!filter.daticliente.toLowerCase() || client.daticliente.toLowerCase().indexOf(filter.daticliente.toLowerCase()) > -1)
                                && (!filter.titolo.toLowerCase() || client.titolo.toLowerCase().indexOf(filter.titolo.toLowerCase()) > -1)
                                && (!filter.totalecomm || client.totalecomm.indexOf(filter.totalecomm) > -1)
                                && (!filter.totalecosti || client.totalecosti.indexOf(filter.totalecosti) > -1)
                                && (!filter.margine || client.margine.indexOf(filter.margine) > -1)
                                && (!filter.stato || client.stato.indexOf(filter.stato) > -1)
                                && (!filter.costifornitori || client.costifornitori.indexOf(filter.costifornitori) > -1);
                    });
                }
            };

            window.db = db;

            db.clients = msg.dati;
            jsGrid.locale("it");
            var campi = [
                {name: "numero", type: "text", title: "<b>Num.</b>", css: "customRow", width: "30", editing: false, filterTemplate: createTextFilterTemplate("numero", filtro0)},
                {name: "datait", type: "text", title: "<b>Data</b>", css: "customRow", width: "40", editing: false, filterTemplate: createTextFilterTemplate("datait", filtro1)},
                {name: "daticliente", type: "text", title: "<b>Cliente</b>", css: "customRow", editing: false, filterTemplate: createTextFilterTemplate("daticliente", filtro2)},
                {name: "titolo", type: "text", title: "<b>Titolo</b>", css: "customRow", editing: false, filterTemplate: createTextFilterTemplate("titolo", filtro3)},
                {name: "totalecomm", type: "text", title: "<b>Totale commessa</b>", css: "customRow", width: "40", editing: false, filterTemplate: createTextFilterTemplate("totalecomm", filtro4)},
                {name: "totalecosti", type: "text", title: "<b>Totale costi</b>", css: "customRow", width: "40", editing: false, filterTemplate: createTextFilterTemplate("totalecosti", filtro5)},
                {name: "margine", type: "text", title: "<b>Margine</b>", css: "customRow", width: "40", editing: false, filterTemplate: createTextFilterTemplate("margine", filtro6)},
                {name: "stato", type: "select", items: [
                        {name: "Seleziona...", Id: ""},
                        {name: "Chiusa", Id: "0"},
                        {name: "Aperta", Id: "1"},
                        {name: "Da fatturare", Id: "2"},
                        {name: "Fatturata", Id: "3"},
                    ], title: "<b>Stato</b>", valueField: "Id", textField: "name", css: "customRow", width: "40", filterTemplate: createSelectFilterTemplate("stato", filtro7)},
                {name: "costifornitori", type: "select", items: [
                        {name: "Seleziona..", Id: ""},
                        {name: "Non presenti", Id: "0"},
                        {name: "Presenti", Id: "1"}
                    ], title: "<b>Costi Fornitori</b>", valueField: "Id", textField: "name", css: "customRow", width: "40", editing: false, filterTemplate: createSelectFilterTemplate("costifornitori", filtro8)},
                {type: "control", width: "40", editButton: false, itemTemplate: function (value, item) {
                        var $result = jsGrid.fields.control.prototype.itemTemplate.apply(this, arguments);
                        var $myButton = $("<i style=\"margin-left: 5px; color: #2E65A1;font-size: 1.6em;\" class=\"fa fa-pencil fa-lg\" aria-hidden=\"true\"></i>")
                                .click(function (e) {
                                    var filtri = $("#jsGrid").jsGrid("getFilter").numero + "||" + $("#jsGrid").jsGrid("getFilter").datait + "||"
                                            + $("#jsGrid").jsGrid("getFilter").daticliente + "||"
                                            + $("#jsGrid").jsGrid("getFilter").titolo + "||" + $("#jsGrid").jsGrid("getFilter").totalecomm + "||" + $("#jsGrid").jsGrid("getFilter").totalecosti
                                            + "||" + $("#jsGrid").jsGrid("getFilter").margine + "||" + $("#jsGrid").jsGrid("getFilter").stato + "||" + $("#jsGrid").jsGrid("getFilter").costifornitori;

                                    var paginastart = parseInt($('.jsgrid-pager-current-page').html());
                                    aggiorna(item.id, filtri, arch, paginastart);
                                    e.stopPropagation();
                                });
                        return $result.add($myButton);
                    }}

            ];

            $("#jsGrid").jsGrid({
                width: "100%",
                height: "520px",
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
                        url: "./commesse.php",
                        data: "id=" + idd + "&submit=delete",
                        dataType: "json",
                        success: function () {
                        }
                    });
                },
                // edita item
//                onItemEditing: function (args) {
//                    args.cancel = true;
//                    var ide = args.item.id;
//                    aggiorna(ide);
//                }
                onItemUpdated: function (args) {
                    var valori = $.param(args.item);
                    $.ajax({
                        type: "POST",
                        url: "./commesse.php",
                        data: valori + "&submit=editstatocommessa",
                        dataType: "json",
                        success: function () {
                        }
                    });
                }
            });

        }

    });
    if (arch == 1) {
        var msgsez = "Commesse Archiviate";
    } else {
        msgsez = "Commesse Aperte";
    }
    $('#titcommsez').html(msgsez);
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
    $('.showcont').show().load('./form/form-commesse.php', function () {
        /* sortable righe */
        $('.contienirighe').sortable({
            disabled: false,
            cancel: '.nosortable, input, select',
            cursor: 'move'
        });
        /**/
        /**/
        $.validator.messages.required = '';
        $("#formcommesse").validate({
            submitHandler: function () {
                $("#submitformcommesse").ready(function () {
                    var datastring = $("#formcommesse *").not(".nopost").serialize();
                    var stato = $('#stato').val();
                    $.ajax({
                        type: "POST",
                        url: "./commesse.php",
                        data: datastring + "&submit=submitformcommesse",
                        dataType: "json",
                        success: function (msg) {
                            if (msg.msg === "ko") {
                                alert(msg.msgko);
                            } else {
                                if (stato === '3') {
                                    location.href = '/commesse.php?op=archivio';
                                } else {
                                    location.href = '/commesse.php?op=commesse';
                                }
//                                var numsuccessivo = parseInt($('#numero').val()) + 1;
//                                $('#formcommesse').trigger('reset');
//                                $('.preventivicliente').remove();
//                                $('.rigaprevcomm').remove();
//                                $('.noprevcoll').show();
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
    $('.showcont').show().load('./form/form-commesse.php', function () {
        $('.bottone_chiudi').html("<a href=\"javascript:;\" class=\"sizing\" onclick=\"mostraCommesse('" + arch + "', '" + filtri + "', '" + paginastart + "');\">Chiudi</a>");
        /*richiama dati*/
        $.ajax({
            type: "POST",
            url: "./commesse.php",
            data: "id=" + id + "&submit=richiamacommessa",
            dataType: "json",
            success: function (msg) {
                /* riempio i campi */
                $.each(msg['valori'][0], function (index, value) {
                    $("#" + index).val(value);
                });

                $('#totaleinterni').val(msg.totaleinterni);
                $('#totaleesterni').val(msg.totaleesterni);
                
                var totcomm = parseFloat($('#totalecomm').val());
                var totalecosticommessa = parseFloat($('#totalecosti').val());
                $('#margine').val(roundTo(totcomm - totalecosticommessa, 2));

                var data = $('#data').val().split('-');
                $('#datap').val(data[2] + "/" + data[1] + "/" + data[0]);
                for (var i = 0; i < msg['voci'].length; i++) {

                    if (msg['voci'][i].statovoce > 0) {
                        var readonly = "readonly";
                        var classhidden = "hidden";
                        var error = "bkg_red";
                    } else {
                        readonly = "";
                        classhidden = "";
                        error = "";
                    }

                    $('.contienirighe').append('<div class="rigaadd riga_prodotto_comm sizing">\n\
                                                <input type=\"hidden\" name="idlavcomm[]" ' + readonly + ' value="' + msg['voci'][i].id + '" />\n\
                                                <i class="fa fa-arrows-v fa-lg ordinarighe" aria-hidden="true"></i>\n\
                                                <select profit="profit' + i + '" name="idprofit[]" ' + readonly + ' class="' + error + ' input_moduli sizing float_moduli_small_10" placeholder="Profit Center" title="Profit Center"><option value="0">Seleziona profit center</option>' + msg.vociprofit + '</select>\n\
                                                <input type="text" name="nome[]" ' + readonly + ' value="' + msg['voci'][i].nome + '" class="' + error + ' input_moduli sizing float_moduli_small nomepz" placeholder="Nome" title="Nome" />\n\
                                                <textarea name="descr[]" ' + readonly + ' class="' + error + ' nosortable textarea_moduli_small input_moduli sizing float_moduli_50_50" placeholder="Descrizione" title="Descrizione">' + msg['voci'][i].descr + '</textarea>\n\
                                                <input type="text" name="oreprev[]" ' + readonly + ' value="' + msg['voci'][i].oreprev + '" class="' + error + ' oreprev input_moduli sizing float_moduli_small_10 timepicker" placeholder="Ore previste" title="Ore previste" />\n\
                                                <input type="text" name="prezzocliente[]" ' + readonly + ' value="' + msg['voci'][i].prezzocliente + '" class="' + error + ' prezzocliente input_moduli sizing float_moduli_small_10" placeholder="Prezzo cliente" title="Prezzo cliente" />\n\
                                                <input type="hidden" name="costo[]" ' + readonly + ' value="' + msg['voci'][i].costo + '" class="' + error + ' costo input_moduli sizing float_moduli_small_10" placeholder="Costo" title="Costo" />\n\
                                                <input type="hidden" name="statovoce[]" ' + readonly + ' class="costo input_moduli sizing float_moduli_small_10" value="' + msg['voci'][i].statovoce + '" />\n\
                                                <a href="javascript:;" class="' + classhidden + ' remove_button"><i style="color: #CD0A0A; line-height: 35px;" class="fa fa-times fa-lg" aria-hidden="true"></i></a>\n\
                                                <div class="chiudi"></div>\n\
                                                </div>');
                    $("[profit=profit" + i + "]").val(msg['voci'][i].idprofit);
                }
                
                /**/
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
                        $(this).next('textarea').next('input').val('00:00');
                        $(this).next('textarea').next('input').next('input').val(ui.item.prezzo);
                        totaleorepreviste();
                        totalecommessa();
                    }
                });
                /**/
                /**/
                
                
                var valoriore = msg.ore;
                $(".timepicker").autocomplete({
                    source: valoriore,
                    change: function (event, ui) {
                        totaleorepreviste();
                    }
                });
                /* cerca preventivi per quel cliente */
                $.ajax({
                    type: "POST",
                    url: "./commesse.php",
                    data: "idcliente=" + msg['valori'][0]['idcliente'] + "&submit=preventiviokcliente",
                    dataType: "json",
                    success: function (res)
                    {
                        var optionpreventivi = "";
                        for (var i = 0; i < res.preventivi.length; i++) {
                            optionpreventivi += "<option class=\"preventivicliente prev" + res.preventivi[i].id + " \" value=\"" + res.preventivi[i].id + "\">" + res.preventivi[i].numero + " - " + res.preventivi[i].datait + " " + res.preventivi[i].titolo + "</option>";
                        }
                        $('.preventivicliente').remove();
                        $('.collegapreventivo').append(optionpreventivi);
                    }
                });
                /* compilo preventivi */
                for (var i = 0; i < msg['prevcoll'].length; i++) {
                    collegaPreventivo(msg['prevcoll'][i].idpreventivo);
                }
                /* ore lavorate */
                if (msg.riepilogoore) {
//                    $('#riepilogoore').html(msg.riepilogoore);
//                    $('#orelavorate').val(sommaoreminuti('orelavt'));
                }

                /* totali lavorazioni */
                if (msg.riepilogocostitotali) {
                    $('#riepilogototalilavorazione').html(msg.riepilogocostitotali);


                    /* richiama fornitori */
                    var datifornitori = msg.fornitori;
                    var fornitori = $.map(datifornitori, function (item) {
                        return {
                            label: item.cognome + " " + item.nome + " " + item.azienda,
                            id: item.id
                        };
                    });
                    $(".cercafornitore").autocomplete({
                        source: fornitori,
                        select: function (event, ui) {
                            $(this).next('input').val(ui.item.id);
                            /**/
                        }
                    });

                    $('.cercafornitore').unbind('keyup').on('keyup', function () {
                        if (this.value === "") {
                            $(this).next('input').val("");
                        }
                    });
                    /* centri di costo */
                    var daticentridicosto = msg.centridicosto;
                    var centridicosto = $.map(daticentridicosto, function (item) {
                        return {
                            label: item.nome,
                            id: item.id
                        };
                    });
                    $(".cercacdc").autocomplete({
                        source: centridicosto,
                        minLength: 0,
                        select: function (event, ui) {
                            $(this).next('input').val(ui.item.id);
                            /**/
                        }
                    }).focus(function () {
                        //Use the below line instead of triggering keydown
                        $(this).autocomplete("search", "");
                    });

                    $('.cercacdc').unbind('keyup').on('keyup', function () {
                        if (this.value === "") {
                            $(this).next('input').val("");
                        }
                    });
                }
                /**/
                $('#costototaleoredautenti').val(msg.costototaleoredautenti);
                totalecommessa();

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
        $("#formcommesse").validate({
            submitHandler: function () {
                $("#submitformcommesse").ready(function () {
                    var datastring = $("#formcommesse *").not(".nopost").serialize();
                    var stato = $('#stato').val();
                    $.ajax({
                        type: "POST",
                        url: "./commesse.php",
                        data: datastring + "&submit=editformcommesse",
                        dataType: "json",
                        success: function (msg) {
                            if (msg.msg === "ko") {
                                alert(msg.msgko);
                            } else {
                                $('#messaggiobottom').slideToggle('fast').delay(2000).slideToggle('slow');
                                setTimeout(function () {
                                    aggiorna(id, filtri, arch, paginastart);
                                }, 2000);
                            }
                        }
                    });
                });
            }
        });
    });
}

function modificacosto(idcostocomm, idvocecomm, costoprecedente) {
    $('#rigariepilogocosti_' + idcostocomm + ' input').prop('disabled', false);
    $('#comandi_' + idcostocomm).html("<a href=\"javascript:;\" class=\"addbutton\" style=\"line-height: 35px;\" onclick=\"salvamodificacosto('" + idcostocomm + "', '" + idvocecomm + "', '" + costoprecedente + "')\"><i class=\"fa fa-floppy-o fa-lg\" aria-hidden=\"true\"></i></a>");
}

function salvamodificacosto(idcostocomm, idvocecomm, costop) {
    var datastring = $("#rigariepilogocosti_" + idcostocomm + " *").serialize();

    var costopassato = $('#rigacosto_' + idcostocomm).val();
    var costopassatomod = costopassato.replace(",", ".");
    $('#rigacosto_' + idcostocomm).val(costopassatomod);

    var costoattuale = parseFloat(costop - parseFloat(costopassatomod));
    /* ridefinisco costo precedente per uleriore modifica */
    var costoprecedente = $('#rigacosto_' + idcostocomm).val();
    /* pulisci costo totale voce */
    var costototalevoce1 = parseFloat(0.00);
    costototalevoce1 += $('#costitotalilav_' + idvocecomm).html();
    var costototalevoce = parseFloat(costototalevoce1.replace(".", "").replace(",", "."));

    $.ajax({
        type: "POST",
        url: "./commesse.php",
        data: datastring + "&idcostocomm=" + idcostocomm + "&submit=modificacosto",
        dataType: "json",
        success: function (msg) {
            if (msg.msg === "ko") {
                alert(msg.msgko);
            } else {
                $('#rigariepilogocosti_' + idcostocomm + ' input').prop('disabled', true);
                $('#comandi_' + idcostocomm).html("<a href=\"javascript:;\" class=\"addbutton\" onclick=\"modificacosto('" + idcostocomm + "', '" + idvocecomm + "', '" + costoprecedente + "');\"><i class=\"fa fa-pencil fa-lg\" aria-hidden=\"true\" style=\"color: #000000; line-height: 35px;\"></i></a> <a href=\"javascript:;\" class=\"addbutton\" onclick=\"eliminacosto('" + idcostocomm + "', '" + idvocecomm + "');\"><i class=\"fa fa-times fa-lg\" aria-hidden=\"true\" style=\"color: #cd0a0a; line-height: 35px;\"></i></a>");
                /* totale costi */
                $('#costitotalilav_' + idvocecomm).html(numberFormat(parseFloat(costototalevoce - costoattuale), 2, ",", "."));
                var totalecosticommattuale = parseFloat($('#totalecosti').val());
                $('#totalecosti').val(roundTo(totalecosticommattuale - costoattuale, 2));
                /* somma solo costi esterni */
                var totalecostiesterni = parseFloat($('#totaleesterni').val());
                $('#totaleesterni').val(roundTo(totalecostiesterni - costoattuale, 2));
                /* margine commessa */
                var totalecommessa = parseFloat($('#totalecomm').val());
                var totalecosticommessa = parseFloat($('#totalecosti').val());
                $('#margine').val(roundTo(totalecommessa - totalecosticommessa, 2));

                /* somme e margini */
                var costiesternilav = parseFloat(costototalevoce - costoattuale); /* per calcolo costi esterni */
                /* pulisci costi interni lavorazioni */
                var costototaleore1 = $('#costitotaliorelav_' + idvocecomm).html();
                var costototaleore = parseFloat(costototaleore1.replace(".", "").replace(",", ".")); /* per calcolo costi interni */
                /* totale entrate lav */
                var prezzoclientelav1 = $('#prezzoclientelav_' + idvocecomm).html();
                var prezzoclientelav = parseFloat(prezzoclientelav1.replace(".", "").replace(",", ".")); /* per calcolo totale entrate */
                /* calcoli */
                $('#costieorelav_' + idvocecomm).html(numberFormat(parseFloat(costiesternilav + costototaleore), 2, ",", "."));
                $('#marginelav_' + idvocecomm).html(numberFormat(parseFloat(prezzoclientelav - (costiesternilav + costototaleore)), 2, ",", "."));
                /* rosso o nero a seconda se + o - */
                if (parseFloat(prezzoclientelav - (costiesternilav + costototaleore)) >= 0) {
                    $('#marginelav_' + idvocecomm).css("color", "black");
                } else {
                    $('#marginelav_' + idvocecomm).css("color", "red");
                }
            }
        }
    });
}

function aggiungicosto(idvocecomm) {
    var datastring = $("#riepilogocosti_" + idvocecomm + " *").serialize();
    var idcomm = $('#id').val();

    var costopassato = $('#costo_' + idvocecomm).val();
    var costopassatomod = costopassato.replace(",", ".");
    var costoattuale = parseFloat(costopassatomod);
    /* pulisci costo totale voce */
    var costototalevoce1 = $('#costitotalilav_' + idvocecomm).html();
    var costototalevoce = parseFloat(costototalevoce1.replace(".", "").replace(",", "."));
    /**/
    $.ajax({
        type: "POST",
        url: "./commesse.php",
        data: datastring + "&idvocecomm=" + idvocecomm + "&idcomm=" + idcomm + "&submit=aggiungicosto",
        dataType: "json",
        success: function (msg) {
            if (msg.msg === "ko") {
                alert(msg.msgko);
            } else {
                $('#formadd_' + idvocecomm + ' input').val("");
                $('#riepilogocosti_' + idvocecomm).append(msg.rigacosto);

                /* richiama fornitori */
                var datifornitori = msg.fornitori;
                var fornitori = $.map(datifornitori, function (item) {
                    return {
                        label: item.cognome + " " + item.nome + " " + item.azienda,
                        id: item.id
                    };
                });
                $(".cercafornitore").autocomplete({
                    source: fornitori,
                    select: function (event, ui) {
                        $(this).next('input').val(ui.item.id);
                        /**/
                    }
                });

                $('.cercafornitore').unbind('keyup').on('keyup', function () {
                    if (this.value === "") {
                        $(this).next('input').val("");
                    }
                });
                /* centri di costo */
                var daticentridicosto = msg.centridicosto;
                var centridicosto = $.map(daticentridicosto, function (item) {
                    return {
                        label: item.nome,
                        id: item.id
                    };
                });
                $(".cercacdc").autocomplete({
                    source: centridicosto,
                    select: function (event, ui) {
                        $(this).next('input').val(ui.item.id);
                        /**/
                    }
                });

                $('.cercacdc').unbind('keyup').on('keyup', function () {
                    if (this.value === "") {
                        $(this).next('input').val("");
                    }
                });
                /* totale costo esterni + inerni */
                $('#costitotalilav_' + idvocecomm).html(numberFormat(parseFloat(costototalevoce + costoattuale), 2, ",", "."));
                var totalecosticommattuale = parseFloat($('#totalecosti').val());
                $('#totalecosti').val(roundTo(totalecosticommattuale + costoattuale, 2));
                /* somma solo costi  */
                var totalecostiesterni = parseFloat($('#totaleesterni').val());
                $('#totaleesterni').val(roundTo(totalecostiesterni + costoattuale, 2));
                /* margine commessa */
                var totalecommessa = parseFloat($('#totalecomm').val());
                var totalecosticommessa = parseFloat($('#totalecosti').val());
                $('#margine').val(roundTo(totalecommessa - totalecosticommessa, 2));


                /**/
                /* somme e margini */
                var costiesternilav = parseFloat(costototalevoce + costoattuale); /* per calcolo costi esterni */
                /* pulisci costi interni lavorazioni */
                var costototaleore1 = $('#costitotaliorelav_' + idvocecomm).html();
                var costototaleore = parseFloat(costototaleore1.replace(".", "").replace(",", ".")); /* per calcolo costi interni */
                /* totale entrate lav */
                var prezzoclientelav1 = $('#prezzoclientelav_' + idvocecomm).html();
                var prezzoclientelav = parseFloat(prezzoclientelav1.replace(".", "").replace(",", ".")); /* per calcolo totale entrate */
                /* calcoli */
                $('#costieorelav_' + idvocecomm).html(numberFormat(parseFloat(costiesternilav + costototaleore), 2, ",", "."));
                $('#marginelav_' + idvocecomm).html(numberFormat(parseFloat(prezzoclientelav - (costiesternilav + costototaleore)), 2, ",", "."));
                /* rosso o nero a seconda se + o - */
                if (parseFloat(prezzoclientelav - (costiesternilav + costototaleore)) >= 0) {
                    $('#marginelav_' + idvocecomm).css("color", "black");
                } else {
                    $('#marginelav_' + idvocecomm).css("color", "red");
                }
            }
        }
    });
}

function eliminacosto(idcostocomm, idvocecomm) {
    if (confirm("Stai per eliminare il contenuto, vuoi continuare?")) {
        var costoattuale = parseFloat($('#rigacosto_' + idcostocomm).val());
        /* pulisci costo totale voce */
        var costototalevoce1 = 0;
        costototalevoce1 += $('#costitotalilav_' + idvocecomm).html();
        var costototalevoce = parseFloat(costototalevoce1.replace(".", "").replace(",", "."));
        /**/
        $.ajax({
            type: "POST",
            url: "./commesse.php",
            data: "id=" + idcostocomm + "&submit=eliminacosto",
            dataType: "json",
            success: function (msg) {
                if (msg.msg === "ko") {
                    alert(msg.msgko);
                } else {
                    $('#rigariepilogocosti_' + idcostocomm).remove();
                    /**/
                    $('#costitotalilav_' + idvocecomm).html(numberFormat(parseFloat(costototalevoce - costoattuale), 2, ",", "."));
                    var totalecosticommattuale = parseFloat($('#totalecosti').val());
                    $('#totalecosti').val(roundTo(totalecosticommattuale - costoattuale, 2));
                    /* solo costi esterni */
                    var totalecostiesterni = parseFloat($('#totaleesterni').val());
                    $('#totaleesterni').val(roundTo(totalecostiesterni - costoattuale, 2));
                    /* margine commessa */
                var totalecommessa = parseFloat($('#totalecomm').val());
                var totalecosticommessa = parseFloat($('#totalecosti').val());
                $('#margine').val(roundTo(totalecommessa - totalecosticommessa, 2));

                    /* somme e margini */
                    var costiesternilav = parseFloat(costototalevoce - costoattuale); /* per calcolo costi esterni */
                    /* pulisci costi interni lavorazioni */
                    var costototaleore1 = $('#costitotaliorelav_' + idvocecomm).html();
                    var costototaleore = parseFloat(costototaleore1.replace(".", "").replace(",", ".")); /* per calcolo costi interni */
                    /* totale entrate lav */
                    var prezzoclientelav1 = $('#prezzoclientelav_' + idvocecomm).html();
                    var prezzoclientelav = parseFloat(prezzoclientelav1.replace(".", "").replace(",", ".")); /* per calcolo totale entrate */
                    /* calcoli */
                    $('#costieorelav_' + idvocecomm).html(numberFormat(parseFloat(costiesternilav + costototaleore), 2, ",", "."));
                    $('#marginelav_' + idvocecomm).html(numberFormat(parseFloat(prezzoclientelav - (costiesternilav + costototaleore)), 2, ",", "."));
                    /* rosso o nero a seconda se + o - */
                    if (parseFloat(prezzoclientelav - (costiesternilav + costototaleore)) >= 0) {
                        $('#marginelav_' + idvocecomm).css("color", "black");
                    } else {
                        $('#marginelav_' + idvocecomm).css("color", "red");
                    }
                }
            }
        });
    }
}

function oprighe(qta, prezzo, sconto) {
    var totale = qta * prezzo - qta * prezzo * sconto / 100;
    return roundTo(totale, 2);
}

function totalecommessa() {
    var sommaprezzi = 0;

    $('.prezzocliente').each(function () {
        if (!this.value) {
        } else {
            sommaprezzi += parseFloat(this.value);
        }
    });

    $('#totalecomm').val(numberFormat((roundTo(sommaprezzi, 2)), 2, ".", ""));
    /**/
    /* attivare temporaneo solo se si vuole aggiornare commesse vecchie con ore dipendenti */

//    var sommacosti = 0;
//    /*$('.costo').each(function () {
//     if (!this.value) {
//     } else {
//     sommacosti += parseFloat(this.value);
//     }
//     
//     });*/
//    var costidaore = 0;
//    if ($('#costototaleoredautenti').val() > 0) {
//        costidaore += parseFloat($('#costototaleoredautenti').val());
//        sommacosti += costidaore;
//    }
//
//    $('#totalecosti').val(roundTo(sommacosti, 2));
    /**/
    /**/
}

function totaleorepreviste() {
    var hour = 0;
    var minute = 0;
    $('.oreprev').each(function () {
        var str = this.value;
        if (str.indexOf(":") > 0) {

        } else {
            alert("Formato ore non corretto");
            return false;
        }

        var splitTime = this.value.split(':');
        hour += parseInt(splitTime[0]);
        minute += parseInt(splitTime[1]);
    });
    minutisommareore = parseInt(minute / 60);
    hour = hour + minutisommareore;
    minute = minute % 60;
    $('#orepreviste').val(hour + ":" + minute);
}

function sommaoreminuti(classesomma) {
    var hour = 0;
    var minute = 0;
    $('.' + classesomma).each(function () {
        var str = $(this).html();
        if (str.indexOf(":") > 0) {

        } else {
            alert("Formato ore non corretto");
            return false;
        }

        var splitTime = $(this).html().split(':');
        hour += parseInt(splitTime[0]);
        minute += parseInt(splitTime[1]);
    });
    minutisommareore = parseInt(minute / 60);
    hour = hour + minutisommareore;
    minute = minute % 60;

    if (hour < 10) {
        hour = "0" + hour;
    }

    if (minute == "0") {
        minute = "00";
    }

    return hour + ":" + minute;
}

function collegaPreventivo(id) {
    $.ajax({
        type: "POST",
        url: "./commesse.php",
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
            for (var i = 0; i < msg['voci'].length; i++) {
                vociprev += "<div class=\"nomevoceprev sizing\">" + msg['voci'][i].nome + "</div>\n\
                             <div class=\"descvoceprev sizing\">" + msg['voci'][i].descr + "</div>\n\
                             <div class=\"qtavoceprev sizing\">" + msg['voci'][i].qta + "</div>\n\
                             <div class=\"prezzovoceprev sizing\">" + msg['voci'][i].scontato + "</div>\n\
                             <div class=\"chiudi\"></div>";
            }
            /**/
            var htmlprev = "<div class=\"rigaprevcomm sizing rigaprevcomm_" + idprev + "\" >\n\
                     <input type=\"hidden\" name=\"idprevcomm[]\" value=\"" + idprev + "\" />\n\
       <input type=\"hidden\" name=\"numeroprevcomm[]\" value=\"" + msg['valori'][0]['numero'] + "\" />\n\
       <input type=\"hidden\" name=\"dataprevcomm[]\" value=\"" + data[2] + "/" + data[1] + "/" + data[0] + "\" />\n\
       <input type=\"hidden\" name=\"titoloprevcomm[]\" value=\"" + msg['valori'][0]['titolo'] + "\" />\n\
       <input type=\"hidden\" name=\"totaleprevcomm[]\" value=\"" + totaleprint + "\" />\n\
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
        }
    });
}

function delprevcomm(id) {
    if (confirm("Stai per eliminare il contenuto, vuoi continuare?")) {
        $('.rigaprevcomm_' + id).remove();
        $('.prev' + id).show();
    }
}
