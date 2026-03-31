function roundTo(value, decimals) {
    var i = value * Math.pow(10, decimals);
    i = Math.round(i);
    return i / Math.pow(10, decimals);
}


function importa() {
    $.ajax({
        type: "POST",
        url: "./ecommerce.php",
        data: "submit=importa",
        dataType: "json",
        success: function (msg) {
            mostraOrdini();
        }

    });
}

function mostraOrdini(arch, filtri, paginastart) {
    $.ajax({
        type: "POST",
        url: "./ecommerce.php",
        data: "arch=" + arch + "&submit=mostraordini",
        dataType: "json",
        success: function (msg) {
            var db = {
                loadData: function (filter) {
                    return $.grep(this.clients, function (client) {
                        return (!filter.numero.toLowerCase() || client.numero.toLowerCase().indexOf(filter.numero.toLowerCase()) > -1)
                                && (!filter.datait.toLowerCase() || client.datait.toLowerCase().indexOf(filter.datait.toLowerCase()) > -1)
                                && (!filter.daticliente.toLowerCase() || client.daticliente.toLowerCase().indexOf(filter.daticliente.toLowerCase()) > -1)
                                && (!filter.spedizione.toLowerCase() || client.spedizione.toLowerCase().indexOf(filter.spedizione.toLowerCase()) > -1)
                                && (!filter.totale.toLowerCase() || client.totale.toLowerCase().indexOf(filter.totale.toLowerCase()) > -1)
                                && (!filter.commissione_pagamento.toLowerCase() || client.commissione_pagamento.toLowerCase().indexOf(filter.commissione_pagamento.toLowerCase()) > -1)
                                && (!filter.appuntamento.toLowerCase() || client.appuntamento.toLowerCase().indexOf(filter.appuntamento.toLowerCase()) > -1)
                                && (!filter.buono.toLowerCase() || client.buono.toLowerCase().indexOf(filter.buono.toLowerCase()) > -1)
                                && (!filter.codice_buono.toLowerCase() || client.codice_buono.toLowerCase().indexOf(filter.codice_buono.toLowerCase()) > -1)
                                && (!filter.stato.toLowerCase() || client.stato.toLowerCase().indexOf(filter.stato.toLowerCase()) > -1);
                    });
                }
            };

            window.db = db;

            db.clients = msg.dati;
            jsGrid.locale("it");
            var campi = [
                {name: "numero", type: "text", title: "<b>Numero Ordine</b>", width: "50", css: "customRow"},
                {name: "datait", type: "text", title: "<b>Data ordine</b>", width: "60", css: "customRow"},
                {name: "daticliente", type: "text", title: "<b>Nome/Azienda</b>", width: "150", css: "customRow"},
                {name: "commissione_pagamento", type: "text", title: "<b>Commissione pagamento</b>", width: "70", css: "customRow"},
                {name: "spedizione", type: "text", title: "<b>Spedizione</b>", width: "70", css: "customRow"},
                {name: "appuntamento", type: "text", title: "<b>Cons. su appuntamento</b>", width: "70", css: "customRow"},
                {name: "buono", type: "text", title: "<b>Buono sconto</b>", width: "70", css: "customRow"},
                {name: "codice_buono", type: "text", title: "<b>Codice buono sconto</b>", width: "70", css: "customRow"},
                {name: "metodopagamento", type: "text", title: "<b>Metodo di pagamento</b>", css: "customRow"},
                {name: "totale", type: "text", title: "<b>Totale</b>", width: "50", css: "customRow"},
                {name: "stato", type: "select", items: [
                        {name: "Seleziona", Id: ""},
                        {name: "Non fatturato", Id: "0"},
                        {name: "Fatturato", Id: "1"}
                    ], title: "<b>Stato</b>", valueField: "Id", textField: "name", width: "50", css: "customRow"},
                {type: "control", width: "40", css: "customRow"}
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
                        url: "./ecommerce.php",
                        data: "id=" + idd + "&submit=delete",
                        dataType: "json",
                        success: function () {
                        }
                    });
                },
                // edita item
                onItemEditing: function (args) {
                    args.cancel = true;
                    var ide = args.item.id;
                    aggiorna(ide, filtri, arch, paginastart);
                }
            });
        }

    });
    $('.showcont').html("<div id=\"jsGrid\"></div>");
}

function aggiorna(id, filtri, arch, paginastart) {

    /*richiama dati*/
    $.ajax({
        type: "POST",
        url: "./ecommerce.php",
        data: "id=" + id + "&submit=richiamaordine",
        dataType: "json",
        success: function (msg) {

            $('.showcont').show().load('./form/form-ordine.php', function () {


                $('.bottone_chiudi').html("<a href=\"javascript:;\" class=\"sizing\" onclick=\"mostraOrdini('" + arch + "', '" + filtri + "', '" + paginastart + "');\">Chiudi</a>");

                /* riempio i campi */
                $.each(msg['valori'][0], function (index, value) {
                    $("#" + index).val(value);
                });
                var data = $('#data').val().split('-');
                $('#datap').val(data[2] + "/" + data[1] + "/" + data[0]);
                for (var i = 0; i < msg['voci'].length; i++) {
                    if (msg['voci'][i].scontato === '0.00') {
                        totaleriga = oprighe(msg['voci'][i].qta, msg['voci'][i].prezzo, 0);
                    } else {
                        totaleriga = msg['voci'][i].scontato;
                    }

                    $('.contienirighe').append('<div class="rigaadd riga_prodotto_prev sizing">\n\
                                                <i class="fa fa-arrows-v fa-lg ordinarighe" aria-hidden="true"></i><input type="text" name="nome[]" value="' + msg['voci'][i].nome + '" class="input_moduli sizing float_moduli_small nomepz" placeholder="Cerca (codice o titolo)" title="Cerca (codice o titolo)" />\n\
                                                <textarea name="descr[]" class="nosortable textarea_moduli_small sizing float_moduli_40" placeholder="Descrizione" title="Descrizione">' + msg['voci'][i].descr + '</textarea>\n\
                                                <input type="text" name="qta[]" value="' + msg['voci'][i].qta + '" class="qta input_moduli sizing float_moduli_small_10" placeholder="Q.ta" title="Q.ta" />\n\
                                                <input type="text" name="prezzo[]" value="' + msg['voci'][i].prezzo + '" class="prezzo input_moduli sizing float_moduli_small_10" placeholder="Prezzo" title="Prezzo" />\n\
                                                <input type="text" name="sconto[]" value="' + msg['voci'][i].sconto + '" class="sconto input_moduli sizing float_moduli_small_10" placeholder="Sconto" title="Sconto" />\n\
                                                <input type="text" name="scontato[]" value="' + totaleriga + '" class="scontato input_moduli sizing float_moduli_small_10" placeholder="Totale" title="Totale" /><a href="javascript:;" class="remove_button"><i style="color: #CD0A0A; line-height: 35px;" class="fa fa-times fa-lg" aria-hidden="true"></i></a>\n\
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

//                        totalepreventivo();
                    }
                });
                totaleordine();

                $.validator.messages.required = '';
                $("#formordine").validate({
                    submitHandler: function () {
                        $("#submitformordine").ready(function () {
                            var datastring = $("#formordine *").not(".nopost").serialize();
                            var stato = $('#stato').val();
                            $.ajax({
                                type: "POST",
                                url: "./ecommerce.php",
                                data: datastring + "&submit=editformordine",
                                dataType: "json",
                                success: function (msg) {
                                    if (msg.msg === "ko") {
                                        alert(msg.msgko);
                                    } else {
                                        mostraOrdini(arch, filtri, paginastart);
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

function totaleordine() {
    var somma = 0;
    $('.scontato').each(function () {
        somma += parseFloat(this.value);
    });
    var spedizione = parseFloat($('#spedizione').val());
    var commissione = parseFloat($('#commissione_pagamento').val());
    var appuntamento = parseFloat($('#appuntamento').val());
    var buono = parseFloat($('#buono').val());

    var sommatotale = somma + spedizione / 1.22 + commissione / 1.22 + appuntamento / 1.22 - buono / 1.22;

    $('#totaleivaesclusa').val(roundTo(sommatotale, 2));

    var ivapercalcoli = parseFloat(100 + parseFloat(22));
    totaleiva = sommatotale * ivapercalcoli / 100;

    $('#totale').val(roundTo(totaleiva, 2));

}