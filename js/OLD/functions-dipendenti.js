function mostraDipendenti(tipo) {
    var op = getUrlParameter("op");
    if (!tipo) {
        if (op === "atelier") {
            tipo = '5';
        } else {
            tipo = '3';
        }
    }
    var editingValue = true;
    var deletingValue = true;
    $.ajax({
        type: "POST",
        url: "./dipendenti.php",
        data: "tipo=" + tipo + "&submit=tuttidipendenti",
        dataType: "json",
        success: function (msg) {
            if (tipo === '5') {
                var db = {
                    loadData: function (filter) {
                        return $.grep(this.clients, function (client) {
                            return (!filter.nome.toLowerCase() || client.nome.toLowerCase().indexOf(filter.nome.toLowerCase()) > -1)
                                    && (!filter.cognome.toLowerCase() || client.cognome.toLowerCase().indexOf(filter.cognome.toLowerCase()) > -1)
                                    && (!filter.nominativo.toLowerCase() || client.nominativo.toLowerCase().indexOf(filter.nominativo.toLowerCase()) > -1)
                                    && (!filter.email.toLowerCase() || client.email.toLowerCase().indexOf(filter.email.toLowerCase()) > -1)
                                    //&& (!filter.ruolo_txt.toLowerCase() || client.ruolo_txt.toLowerCase().indexOf(filter.ruolo_txt.toLowerCase()) > -1)
                                    && (!filter.telefono || client.telefono.indexOf(filter.telefono) > -1)
                                    && (!filter.cellulare || client.cellulare.indexOf(filter.cellulare) > -1)
//                                && (!filter.nomeatelier.toLowerCase() || client.nomeatelier.toLowerCase().indexOf(filter.nomeatelier.toLowerCase()) > -1)
                                    && (!filter.attivo || client.attivo.indexOf(filter.attivo) > -1);
                        });
                    }
                };

                window.db = db;

                db.clients = msg.dati;
                jsGrid.locale("it");

                var campi = [
                    {name: "nominativo", type: "text", title: "<b>Atelier</b>", css: "customRow"},
                    {name: "nome", type: "text", title: "<b>Nome</b>", css: "customRow"},
                    {name: "cognome", type: "text", title: "<b>Cognome</b>", css: "customRow"},
                    {name: "email", type: "text", title: "<b>E-mail</b>", css: "customRow"},
                    //{name: "ruolo_txt", type: "text", title: "<b>Ruolo</b>", css: "customRow"},
                    {name: "telefono", type: "text", title: "<b>Telefono</b>", css: "customRow"},
                    {name: "cellulare", type: "text", title: "<b>Cellulare</b>", css: "customRow"},
                    {name: "attivo", type: "select", items: [
                            {name: "Seleziona", Id: ""},
                            {name: "Disattivo", Id: "0"},
                            {name: "Attivo", Id: "1"}
                        ], title: "<b>Stato</b>", valueField: "Id", textField: "name", css: "customRow"},
                    {type: "control"}
                ];
            } else {
                editingValue = false;
                deletingValue = false;
                var db = {
                    loadData: function (filter) {
                        return $.grep(this.clients, function (client) {
                            //console.log(client);
                            return (!filter.nome.toLowerCase() || client.nome.toLowerCase().indexOf(filter.nome.toLowerCase()) > -1)
                                    && (!filter.cognome.toLowerCase() || client.cognome.toLowerCase().indexOf(filter.cognome.toLowerCase()) > -1)
                                    && (!filter.email.toLowerCase() || client.email.toLowerCase().indexOf(filter.email.toLowerCase()) > -1)
                                    && (!filter.iban.toLowerCase() || client.iban.toLowerCase().indexOf(filter.iban.toLowerCase()) > -1)
                                    && (!filter.ruolo_txt.toLowerCase() || client.ruolo_txt.toLowerCase().indexOf(filter.ruolo_txt.toLowerCase()) > -1)
                                    && (!filter.atelier_all_str || client.atelier_all_str.indexOf(filter.atelier_all_str) > -1)
                                    //&& (!filter.cellulare || client.cellulare.indexOf(filter.cellulare) > -1)
                                    && (!filter.nomeatelier.toLowerCase() || client.nomeatelier.toLowerCase().indexOf(filter.nomeatelier.toLowerCase()) > -1)
                                    && (!filter.attivo || client.attivo.indexOf(filter.attivo) > -1);
                        });
                    }
                };

                window.db = db;

                db.clients = msg.dati;
                jsGrid.locale("it");

                var campi = [
                    {name: "nome", type: "text", title: "<b>Nome</b>", css: "customRow"},
                    {name: "cognome", type: "text", title: "<b>Cognome</b>", css: "customRow"},
                    {name: "email", type: "text", title: "<b>E-mail</b>", css: "customRow"},
                    {name: "iban", type: "text", title: "<b>IBAN</b>", css: "customRow"},
                    {name: "ruolo_txt", type: "text", title: "<b>Ruolo</b>", css: "customRow"},
                    {name: "atelier_all_str", type: "text", title: "<b>Mostra tutti gli atelier su applicazione</b>", css: "customRow"},
                    //{name: "cellulare", type: "text", title: "<b>Cellulare</b>", css: "customRow"},
                    {name: "nomeatelier", type: "text", title: "<b>Atelier predefinito</b>", css: "customRow"},
                    {name: "attivo", type: "select", items: [
                            {name: "Seleziona", Id: ""},
                            {name: "Disattivo", Id: "0"},
                            {name: "Attivo", Id: "1"}
                        ], title: "<b>Stato</b>", valueField: "Id", textField: "name", css: "customRow"},
                    {type: "control", itemTemplate: function (value, item) {
                            //var $result = jsGrid.fields.control.prototype.itemTemplate.apply(this, arguments);
                            var editDeleteBtn = $('<input class="jsgrid-button jsgrid-edit-button" type="button" title="Edit"><input class="jsgrid-button jsgrid-delete-button" type="button" title="Delete">' +
                                    "<i style=\"margin-left: 5px; color: #2E65A1;\"class=\"fa fa-ban fa-lg\" aria-hidden=\"true\" title=\"Reset formazione\"></i>")
                                    .on('click', function (e) {
                                        //console.log(e);
                                        var target = $(e.target);
                                        if (target.hasClass('jsgrid-edit-button')) {
                                            //Based on button click you can make your customization  
                                            //console.log(item); //You can access all data based on item clicked
                                            e.stopPropagation();
                                            aggiorna(item.id);
                                        } else if (target.hasClass('fa-ban')) {
                                            e.stopPropagation();
                                            resetFormazione(item.id);
                                        } else {
                                            e.stopPropagation();
                                            if (confirm('Stai per cancellare, sei sicuro?')) {
                                                var idd = item.id;
                                                $.ajax({
                                                    type: "POST",
                                                    url: "./dipendenti.php",
                                                    data: "id=" + idd + "&submit=delete",
                                                    dataType: "json",
                                                    success: function () {
                                                    }
                                                });
                                            } else {
                                                return false;
                                            }
                                        }
                                    });
                            return editDeleteBtn;
//                            var $myButton = $("<i style=\"margin-left: 5px; color: #2E65A1;\"class=\"fa fa-ban fa-lg\" aria-hidden=\"true\" title=\"Reset formazione\"></i>")
//                                    .click(function (e) {
//                                        resetFormazione(item.id);
//                                        e.stopPropagation();
//                                    });
//                            return $result.add($myButton);

                        }
                    }
                ];
            }

            $("#jsGrid").jsGrid({
                width: "100%",
                height: "520px",
                filtering: true,
                editing: editingValue,
                deleting: deletingValue,
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
                        url: "./dipendenti.php",
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
                    aggiorna(ide);
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

function aggiungi(livello) {
    $('.showcont').show().load('./form/form-dipendenti.php?livello=' + livello, function () {


        if (livello === '5') {
            $(".dipatelier").hide();
        } else {
            $(".soloatelier").hide();
        }

        /**/
        $.validator.messages.required = '';
        $("#formdipendenti").validate({
            rules: {
                email: {
                    email: true
                }
            },
            submitHandler: function () {
                $("#submitformdipendenti").ready(function () {
                    var pass = CryptoJS.MD5($('#password').val());
                    $('#password').val(pass);

                    var datastring = $("#formdipendenti *").not(".nopost").serialize();
                    $.ajax({
                        type: "POST",
                        url: "./dipendenti.php",
                        data: datastring + "&livello=" + livello + "&submit=submitformdipendenti",
                        dataType: "json",
                        success: function (msg) {
                            if (msg.msg === "ko") {
                                alert(msg.msgko);
                            } else {
                                $('#formdipendenti').trigger('reset');
                                $('#messaggio').slideToggle('fast').delay(2000).slideToggle('slow');
                                mostraDipendenti(livello);
                            }
                        }
                    });
                });
            }
        });
    });
}

function aggiorna(id) {
    $('.showcont').hide();
    $('.showcont-form').show().load('./form/form-dipendenti.php?id=' + id, function () {
        /*richiamadati*/
        $.ajax({
            type: "POST",
            url: "./dipendenti.php",
            data: "id=" + id + "&submit=richiamadipendente",
            dataType: "json",
            success: function (msg) {
                /* riempio i campi */
                $.each(msg['valori'][0], function (index, value) {
                    if (index != 'solo_sartoria' && index != 'online' && index != 'non_gestito' && index != 'no_mrage' && index != 'atelier_all' && index != 'village')
                        $("#" + index).val(value);
                });

                if ($('#livello').val() === '5') {
                    $(".dipatelier").hide();
                    var arrapert = msg['valori'][0]['aperture'].split(',');

                    for (var i = 0; i < arrapert.length; i++) {
                        $('#apert_' + arrapert[i]).prop('checked', true);
                    }
                    if ($('#solo_sartoria').length > 0) {
                        if (msg['valori'][0]['solo_sartoria'] == 1) {
                            $('#solo_sartoria').prop('checked', true);
                        }
                    }
                    if ($('#village').length > 0) {
                        if (msg['valori'][0]['village'] == 1) {
                            $('#village').prop('checked', true);
                        }
                    }
                    if ($('#online').length > 0) {
                        if (msg['valori'][0]['online'] == 1) {
                            $('#online').prop('checked', true);
                        }
                    }
                    if ($('#non_gestito').length > 0) {
                        if (msg['valori'][0]['non_gestito'] == 1) {
                            $('#non_gestito').prop('checked', true);
                        }
                    }
                    $('.orariaperture').show();
                } else {
                    $(".soloatelier").hide();
                    $('.orariaperture').hide();
                    if ($('#no_mrage').length > 0) {
                        if (msg['valori'][0]['no_mrage'] == 1) {
                            $('#no_mrage').prop('checked', true);
                        }
                    }
                    if ($('#atelier_all').length > 0) {
                        if (msg['valori'][0]['atelier_all'] == 1) {
                            $('#atelier_all').prop('checked', true);
                        }
                    }
                    if ($('#livello').val() === '3') {
                        if (msg['valori'][0]['retribuzione'].length > 0) {
                            $('#ore_settimana').val(msg['valori'][0]['retribuzione'][0]['ore_settimana']);
                            $('#stipendio_netto').val(msg['valori'][0]['retribuzione'][0]['stipendio_netto']);
                            //console.log($('input[name="retribuzione[]"]').get(0));
                            for (var i = 0; i < msg['valori'][0]['retribuzione'][0]['dati_retribuzione'].length; i++) {
                                var data_inizio_r = $('input[name="data_inizio_r[]"]').get(i);
                                $(data_inizio_r).val(msg['valori'][0]['retribuzione'][0]['dati_retribuzione'][i]['data_inizio_r']);
                                var retribuzione = $('input[name="retribuzione[]"]').get(i);
                                $(retribuzione).val(msg['valori'][0]['retribuzione'][0]['dati_retribuzione'][i]['retribuzione']);
                                var data_fine_r = $('input[name="data_fine_r[]"]').get(i);
                                $(data_fine_r).val(msg['valori'][0]['retribuzione'][0]['dati_retribuzione'][i]['data_fine_r']);
                            }
                        }
                    }
                }
                $('#password').val('');
                $('#password').removeClass("required");
                if (msg['valori'][0]['nazione'] != 'IT' && msg['valori'][0]['nazione'] != '') {
                    var title = $('select#comune option:first').html();
                    $('select#comune').replaceWith('<input type="text" name="comune" id="comune" class="input_moduli sizing float_moduli" value="' + msg['valori'][0]['comune'] + '" />');
                    title = $('select#cap option:first').html();
                    $('select#cap').replaceWith('<input type="text" name="cap" id="cap" class="input_moduli sizing float_moduli" value="' + msg['valori'][0]['cap'] + '" />');
                    $('#regione, #provincia').hide();
                } else {
                    //1 indirizzo per fatturazione
//                    setTimeout(function () {
//                        setProvincia(msg['valori'][0]['regione'], msg['valori'][0]['provincia'], '1');
//                    }, 200);
//                    setTimeout(function () {
//                        setComune(msg['valori'][0]['provincia'], msg['valori'][0]['comune'], '1');
//                    }, 500);
//                    setTimeout(function () {
//                        setCap(msg['valori'][0]['comune'], msg['valori'][0]['cap'], '1');
//                    }, 800);

                    $.ajax({
                        url: setProvincia(msg['valori'][0]['regione'], msg['valori'][0]['provincia'], '1'),
                        success: function () {
                            $.ajax({
                                url: setComune(msg['valori'][0]['provincia'], msg['valori'][0]['comune'], '1'),
                                success: function () {
                                    setCap(msg['valori'][0]['comune'], msg['valori'][0]['cap'], '1');
                                }
                            });
                        }
                    });
                }
            }
        });
        /**/
        $.validator.messages.required = '';
        $("#formdipendenti").validate({
            rules: {
                email: {
                    email: true
                }
            },
            submitHandler: function () {
                $("#submitformdipendenti").ready(function () {
                    var livello = $('#livello').val();
                    if ($('#password').val().length > 0) {
                        var pass = CryptoJS.MD5($('#password').val());
                        $('#password').val(pass);
                    }
                    var datastring = $("#formdipendenti *").not(".nopost").serialize();
                    $.ajax({
                        type: "POST",
                        url: "./dipendenti.php",
                        data: datastring + "&submit=editformdipendenti",
                        dataType: "json",
                        success: function (msg) {
                            if (msg.msg === "ko") {
                                alert(msg.msgko);
                            } else {
                                //mostraDipendenti(livello);
                                $('.showcont').show();
                                $('.showcont-form').html('').hide();
                            }
                        }
                    });
                });
            }
        });
    });
}

function chiudiDipendenti() {
    $('.showcont').show();
    $('.showcont-form').html('').hide();
}

function aggiungiMessaggio() {
    $('.showcont').show().load('./form/form-messaggio.php', function () {

        /**/
        $.validator.messages.required = '';
        $("#form-messaggio").validate({
            submitHandler: function () {
                $("#submitformmessaggio").ready(function () {
                    var datastring = $("#form-messaggio *").not(".nopost").serialize();
                    $.ajax({
                        type: "POST",
                        url: "./dipendenti.php",
                        data: datastring + "&submit=submitformmessaggio",
                        dataType: "json",
                        success: function (msg) {
                            if (msg.msg === "ko") {
                                alert(msg.msgko);
                            } else {
                                $('#form-messaggio').trigger('reset');
                                $('#messaggio').slideToggle('fast').delay(2000).slideToggle('slow');
                                mostraMessaggi();
                            }
                        }
                    });
                });
            }
        });
    });
}

function mostraMessaggi() {
    $.ajax({
        type: "POST",
        url: "./dipendenti.php",
        data: "submit=tuttimessaggi",
        dataType: "json",
        success: function (msg) {
            var db = {
                loadData: function (filter) {
                    return $.grep(this.clients, function (client) {
                        return (!filter.titolo.toLowerCase() || client.titolo.toLowerCase().indexOf(filter.titolo.toLowerCase()) > -1)
                                && (!filter.descrizione.toLowerCase() || client.descrizione.toLowerCase().indexOf(filter.descrizione.toLowerCase()) > -1)
                                && (!filter.nomeutente.toLowerCase() || client.nomeutente.toLowerCase().indexOf(filter.nomeutente.toLowerCase()) > -1)
                                && (!filter.nomeatelier.toLowerCase() || client.nomeatelier.toLowerCase().indexOf(filter.nomeatelier.toLowerCase()) > -1)
                                && (!filter.tipo_str.toLowerCase() || client.tipo_str.toLowerCase().indexOf(filter.tipo_str.toLowerCase()) > -1)
                                && (!filter.date_invio.toLowerCase() || client.date_invio.toLowerCase().indexOf(filter.date_invio.toLowerCase()) > -1);
                    });
                }
            };

            window.db = db;

            db.clients = msg.dati;
            jsGrid.locale("it");

            var campi = [
                {name: "tipo_str", type: "text", title: "<b>Tipo</b>", css: "customRow"},
                {name: "titolo", type: "text", title: "<b>Titolo</b>", css: "customRow"},
                {name: "descrizione", type: "text", title: "<b>Messaggio</b>", css: "customRow"},
                {name: "nomeatelier", type: "text", title: "<b>Atelier</b>", css: "customRow"},
                {name: "nomeutente", type: "text", title: "<b>Utente</b>", css: "customRow"},
                {name: "date_invio", type: "text", title: "<b>Log invio</b>", css: "customRow"},
                {type: "control", itemTemplate: function (value, item) {
                        var $result = jsGrid.fields.control.prototype.itemTemplate.apply(this, arguments);
                        var $myButton = $("<i style=\"margin-left: 5px; color: #2E65A1;\"class=\"fa " + (item.attivo == 1 ? "fa-circle" : "fa-circle-o") + " fa-lg\" aria-hidden=\"true\" title=\"Mostra/Nascondi\"></i>")
                                .click(function (e) {
                                    attivaMessaggi(item.id, (item.attivo == 1 ? 0 : 1));
                                    e.stopPropagation();
                                });
                        var $myButton2 = $("<i style=\"margin-left: 5px; color: #2E65A1;\"class=\"fa fa-paper-plane fa-lg\" aria-hidden=\"true\" title=\"Invia\"></i>")
                                .click(function (e) {
                                    inviaMessaggio(item.id);
                                    e.stopPropagation();
                                });
                        return $result.add($myButton).add($myButton2);
                    }
                }
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
                        url: "./dipendenti.php",
                        data: "id=" + idd + "&submit=delMessaggio",
                        dataType: "json",
                        success: function () {
                        }
                    });
                },
                // edita item
                onItemEditing: function (args) {
                    args.cancel = true;
                    var ide = args.item.id;
                    aggiornaMessaggio(ide);
                }
            });
        }

    });
    $('.showcont').html("<div id=\"jsGrid\"></div>");
}

function aggiornaMessaggio(id) {
    $('.showcont').show().load('./form/form-messaggio.php?id=' + id, function () {

        /**/
        $.validator.messages.required = '';
        $("#form-messaggio").validate({
            submitHandler: function () {
                $("#submitformmessaggio").ready(function () {
                    var datastring = $("#form-messaggio *").not(".nopost").serialize();
                    $.ajax({
                        type: "POST",
                        url: "./dipendenti.php",
                        data: datastring + "&submit=editformmessaggio",
                        dataType: "json",
                        success: function (msg) {
                            if (msg.msg === "ko") {
                                alert(msg.msgko);
                            } else {
                                $('#form-messaggio').trigger('reset');
                                $('#messaggio').slideToggle('fast').delay(2000).slideToggle('slow');
                                mostraMessaggi();
                            }
                        }
                    });
                });
            }
        });
    });
}

function mostraFormazione() {
    $.ajax({
        type: "POST",
        url: "./formazione.php",
        data: "submit=tuttiformazione",
        dataType: "json",
        success: function (msg) {
            var db = {
                loadData: function (filter) {
                    return $.grep(this.clients, function (client) {
                        return (!filter.titolo.toLowerCase() || client.titolo.toLowerCase().indexOf(filter.titolo.toLowerCase()) > -1)
                                && (!filter.ruoli.toLowerCase() || client.ruoli.toLowerCase().indexOf(filter.ruoli.toLowerCase()) > -1);
                    });
                }
            };

            window.db = db;

            db.clients = msg.dati;
            jsGrid.locale("it");


            var campi = [
                {name: "titolo", type: "text", title: "<b>Titolo</b>", css: "customRow"},
                {name: "ruoli", type: "text", title: "<b>Ruoli</b>", css: "customRow"},
                {type: "control", itemTemplate: function (value, item) {
                        var $result = jsGrid.fields.control.prototype.itemTemplate.apply(this, arguments);
                        var $myButton3 = $("<i style=\"margin-left: 5px; color: #2E65A1;\"class=\"fa " + (item.attivo == 1 ? "fa-circle" : "fa-circle-o") + " fa-lg\" aria-hidden=\"true\" title=\"Mostra/Nascondi\"></i>")
                                .click(function (e) {
                                    attivaFormazione(item.id, (item.attivo == 1 ? 0 : 1));
                                    e.stopPropagation();
                                });
                        var $myButton = $("<i style=\"margin-left: 5px; color: #2E65A1;\"class=\"fa fa-video-camera fa-lg\" aria-hidden=\"true\" title=\"Gestione Lezioni\"></i>")
                                .click(function (e) {
                                    corsiFormazione(item.id);
                                    e.stopPropagation();
                                });
//                        var $myButton2 = $("<i style=\"margin-left: 5px; color: #2E65A1;\"class=\"fa fa-list fa-lg\" aria-hidden=\"true\" title=\"Gestione Test\"></i>")
//                                .click(function (e) {
//                                    testFormazione(item.id, 0);
//                                    e.stopPropagation();
//                                });
                        return $result.add($myButton3).add($myButton);
                    }
                }
            ];

            $("#jsGrid").jsGrid({
                width: "100%",
                height: "520px",
                filtering: true,
                editing: true,
                sorting: true,
                paging: true,
                autoload: true,
                pageSize: 1000,
                pageButtonCount: 5,
                controller: db,
                deleteConfirm: "Stai per cancellare, sei sicuro?",
                noDataContent: "Nessun record trovato",
                fields: campi,
                rowClass: function (item, itemIndex) {
                    return "client-" + itemIndex;
                },
                // cancella item
                onItemDeleting: function (args) {
                    var idd = args.item.id;
                    $.ajax({
                        type: "POST",
                        url: "./formazione.php",
                        data: "id=" + idd + "&submit=delFormazione",
                        dataType: "json",
                        success: function () {
                        }
                    });
                },
                onItemEditing: function (args) {
                    args.cancel = true;
                    var ide = args.item.id;
                    editFormazione(ide);
                },
                onItemUpdated: function (args) {
                    var valori = $.param(args.item);
                    $.ajax({
                        type: "POST",
                        url: "./formazione.php",
                        data: valori + "&submit=editFormazione",
                        dataType: "json",
                        success: function () {
                            mostraFormazione();
                        }
                    });
                },
                onRefreshed: function () {
                    var $gridData = $("#jsGrid .jsgrid-grid-body tbody");
                    $gridData.sortable({
                        update: function (e, ui) {
                            // array of indexes
                            e.stopPropagation();
                            var clientIndexRegExp = /\s*client-(\d+)\s*/;
                            var indexes = $.map($gridData.sortable("toArray", {attribute: "class"}), function (classes) {
                                return clientIndexRegExp.exec(classes)[1];
                            });
                            // arrays of items
                            var items = $.map($gridData.find("tr"), function (row) {
                                return $(row).data("JSGridItem");
                            });
                            var row_sorted = new Array();
                            for (var i = 0; i < items.length; i++) {
                                row_sorted.push(items[i].id);
                            }
                            //console && console.log("Reordered items", items);
                            $.ajax({
                                type: "POST",
                                url: "./formazione.php",
                                data: "rows=" + row_sorted.join(',') + "&submit=sortFormazione",
                                dataType: "json",
                                success: function () {
                                }
                            });
                        }
                    });
                }
            });
        }

    });
    $('.showcont').html("<p style=\"margin:10px 0;\"><input type=\"checkbox\" id=\"ordina_formazione\" /> Ordina righe</p><div id=\"jsGrid\"></div>");
    $('#ordina_formazione').unbind('click').click(function () {
        if ($(this).is(':checked')) {
            $("#jsGrid").jsGrid('option', 'editing', false);
            $('#jsGrid .jsgrid-control-field').hide();
        } else {
            mostraFormazione();
        }
    });
}

function aggiungiFormazione() {
    $('.showcont').show().load('./form/form-formazione.php', function () {

        /**/
        $.validator.messages.required = '';
        $("#form-formazione").validate({
            submitHandler: function () {
                $("#submitformformazione").ready(function () {
                    var datastring = $("#form-formazione *").not(".nopost").serialize();
                    $.ajax({
                        type: "POST",
                        url: "./formazione.php",
                        data: datastring + "&submit=submitformformazione",
                        dataType: "json",
                        success: function (msg) {
                            if (msg.msg === "ko") {
                                alert(msg.msgko);
                            } else {
                                $('#form-formazione').trigger('reset');
                                $('#messaggio').slideToggle('fast').delay(2000).slideToggle('slow');
                                mostraFormazione();
                            }
                        }
                    });
                });
            }
        });
    });
}

function editFormazione(id) {
    $('.showcont').show().load('./form/form-formazione.php?id=' + id, function () {

        /**/
        $.validator.messages.required = '';
        $("#form-formazione").validate({
            submitHandler: function () {
                $("#submitformformazione").ready(function () {
                    var datastring = $("#form-formazione *").not(".nopost").serialize();
                    $.ajax({
                        type: "POST",
                        url: "./formazione.php",
                        data: datastring + "&submit=editFormazione",
                        dataType: "json",
                        success: function (msg) {
                            if (msg.msg === "ko") {
                                alert(msg.msgko);
                            } else {
                                $('#form-formazione').trigger('reset');
                                $('#messaggio').slideToggle('fast').delay(2000).slideToggle('slow');
                                mostraFormazione();
                            }
                        }
                    });
                });
            }
        });
    });
}

function corsiFormazione(id) {
    $('.showcont').show().load('./form/form-corsi-formazione.php?id=' + id, function () {

    });
}

function modCorso(id) {
    $('.showcont').show().load('./form/form-mod-corso.php?id=' + id, function () {

    });
}

function testFormazione(idformazione, idlezione) {
    $('.showcont').show().load('./form/form-test-formazione.php?idformazione=' + idformazione + '&idlezione=' + idlezione, function () {

    });
}

function mostraDomande(idformazione, idlezione) {
    $.ajax({
        type: "POST",
        url: "./formazione.php",
        data: "&submit=mostraDomande&idformazione=" + idformazione + '&idlezione=' + idlezione,
        dataType: "json",
        success: function (msg) {

            var db = {
                loadData: function (filter) {
                    return $.grep(this.clients, function (client) {
                        return (!filter.titolo || client.titolo.indexOf(filter.nome) > -1);
                    });
                }
            };

            window.db = db;

            db.clients = msg.dati;
            jsGrid.locale("it");

            var campi = [
                {name: "titolo", type: "text", title: "<b>Titolo</b>", css: "customRow"},
                {name: "tipo", type: "select", title: "<b>Tipo</b>", css: "customRow", items: [
                        {name: "Aperta", Id: "2"},
                        {name: "Singola", Id: "0"},
                        {name: "Multipla", Id: "1"}], valueField: "Id", textField: "name"
                },
                {type: "control", itemTemplate: function (value, item) {
                        var $result = jsGrid.fields.control.prototype.itemTemplate.apply(this, arguments);
                        var $myButton = $("<i style=\"margin-left: 5px; color: #2E65A1;\"class=\"fa fa-list-ol fa-lg\" aria-hidden=\"true\" title=\"Gestione risposte\"></i>")
                                .click(function (e) {
                                    risposteDomanda(item.id);
                                    e.stopPropagation();
                                });
                        return $result.add($myButton);
                    }
                }
            ];


            $("#tbDomande").jsGrid({
                width: "100%",
                height: "300px",
                inserting: false,
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
                rowClass: function (item, itemIndex) {
                    return "client-" + itemIndex;
                },
                // cancella item
                onItemDeleting: function (args) {
                    var idd = args.item.id;
                    $.ajax({
                        type: "POST",
                        url: "./formazione.php",
                        data: "id=" + idd + "&submit=delDomanda",
                        dataType: "json",
                        success: function () {
                        }
                    });
                },
                // edita item
                onItemEditing: function (args) {
//                            args.cancel = true;
//                            var ide = args.item.id;
                },
                onItemUpdated: function (args) {
                    var valori = $.param(args.item);
                    $.ajax({
                        type: "POST",
                        url: "./formazione.php",
                        data: valori + "&submit=editDomanda",
                        dataType: "json",
                        success: function () {
                            mostraDomande(idformazione, idlezione);
                        }
                    });
                },
                onRefreshed: function () {
                    var $gridData = $("#tbDomande .jsgrid-grid-body tbody");
                    $gridData.sortable({
                        update: function (e, ui) {
                            // array of indexes
                            e.stopPropagation();
                            var clientIndexRegExp = /\s*client-(\d+)\s*/;
                            var indexes = $.map($gridData.sortable("toArray", {attribute: "class"}), function (classes) {
                                return clientIndexRegExp.exec(classes)[1];
                            });
                            // arrays of items
                            var items = $.map($gridData.find("tr"), function (row) {
                                return $(row).data("JSGridItem");
                            });
                            var row_sorted = new Array();
                            for (var i = 0; i < items.length; i++) {
                                row_sorted.push(items[i].id);
                            }
                            //console && console.log("Reordered items", items);
                            $.ajax({
                                type: "POST",
                                url: "./formazione.php",
                                data: "rows=" + row_sorted.join(',') + "&submit=sortDomande",
                                dataType: "json",
                                success: function () {
                                }
                            });
                        }
                    });
                }
            });
        }

    });
}

function risposteDomanda(id) {
    $('.showcont').show().load('./form/form-risposte-test.php?id=' + id, function () {

    });
}

function attivaFormazione(id, attivo) {
    $.ajax({
        type: "POST",
        url: "./formazione.php",
        data: "id=" + id + "&attivo=" + attivo + "&submit=attivaFormazione",
        dataType: "json",
        success: function () {
            mostraFormazione();
        }
    });
}

function attivaCorsi(idformazione, id, attivo) {
    $.ajax({
        type: "POST",
        url: "./formazione.php",
        data: "id=" + id + "&attivo=" + attivo + "&submit=attivaCorso",
        dataType: "json",
        success: function () {
            corsiFormazione(idformazione);
        }
    });
}

function attivaMessaggi(id, attivo) {
    $.ajax({
        type: "POST",
        url: "./dipendenti.php",
        data: "id=" + id + "&attivo=" + attivo + "&submit=attivaMessaggi",
        dataType: "json",
        success: function () {
            mostraMessaggi();
        }
    });
}

function inviaMessaggio(id) {
    if (confirm('Stai per inviare il messaggio, procedere?')) {
        $.ajax({
            type: "POST",
            url: "./dipendenti.php",
            data: "id=" + id + "&submit=inviaMessaggio",
            dataType: "json",
            success: function (msg) {
                alert(msg.error);
            }
        });
    } else {
        return false;
    }
}

function aggiungiLink() {
    $('.showcont').show().load('./form/form-link.php', function () {

        /**/
        $.validator.messages.required = '';
        $("#form-link").validate({
            submitHandler: function () {
                $("#submitformlink").ready(function () {
                    var datastring = $("#form-link *").not(".nopost").serialize();
                    $.ajax({
                        type: "POST",
                        url: "./dipendenti.php",
                        data: datastring + "&submit=submitformlink",
                        dataType: "json",
                        success: function (msg) {
                            if (msg.msg === "ko") {
                                alert(msg.msgko);
                            } else {
                                $('#form-link').trigger('reset');
                                $('#messaggio').slideToggle('fast').delay(2000).slideToggle('slow');
                                mostraLink();
                            }
                        }
                    });
                });
            }
        });
    });
}

function mostraLink() {
    $.ajax({
        type: "POST",
        url: "./dipendenti.php",
        data: "submit=tuttilink",
        dataType: "json",
        success: function (msg) {
            var db = {
                loadData: function (filter) {
                    return $.grep(this.clients, function (client) {
                        return (!filter.titolo.toLowerCase() || client.titolo.toLowerCase().indexOf(filter.titolo.toLowerCase()) > -1)
                                && (!filter.descrizione.toLowerCase() || client.descrizione.toLowerCase().indexOf(filter.descrizione.toLowerCase()) > -1)
                                && (!filter.link.toLowerCase() || client.link.toLowerCase().indexOf(filter.link.toLowerCase()) > -1);
                    });
                }
            };

            window.db = db;

            db.clients = msg.dati;
            jsGrid.locale("it");

            var campi = [
                {name: "titolo", type: "text", title: "<b>Titolo</b>", css: "customRow"},
                {name: "descrizione", type: "text", title: "<b>Messaggio</b>", css: "customRow"},
                {name: "link", type: "text", title: "<b>Link</b>", css: "customRow"},
                {type: "control", itemTemplate: function (value, item) {
                        var $result = jsGrid.fields.control.prototype.itemTemplate.apply(this, arguments);
                        var $myButton = $("<i style=\"margin-left: 5px; color: #2E65A1;\"class=\"fa " + (item.attivo == 1 ? "fa-circle" : "fa-circle-o") + " fa-lg\" aria-hidden=\"true\" title=\"Mostra/Nascondi\"></i>")
                                .click(function (e) {
                                    attivaLink(item.id, (item.attivo == 1 ? 0 : 1));
                                    e.stopPropagation();
                                });
                        return $result.add($myButton);
                    }
                }
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
                        url: "./dipendenti.php",
                        data: "id=" + idd + "&submit=delLink",
                        dataType: "json",
                        success: function () {
                        }
                    });
                },
                // edita item
                onItemEditing: function (args) {
                    args.cancel = true;
                    var ide = args.item.id;
                    aggiornaLink(ide);
                }
            });
        }

    });
    $('.showcont').html("<div id=\"jsGrid\"></div>");
}

function aggiornaLink(id) {
    $('.showcont').show().load('./form/form-link.php?id=' + id, function () {

        /**/
        $.validator.messages.required = '';
        $("#form-link").validate({
            submitHandler: function () {
                $("#submitformlink").ready(function () {
                    var datastring = $("#form-link *").not(".nopost").serialize();
                    $.ajax({
                        type: "POST",
                        url: "./dipendenti.php",
                        data: datastring + "&submit=editformLink",
                        dataType: "json",
                        success: function (msg) {
                            if (msg.msg === "ko") {
                                alert(msg.msgko);
                            } else {
                                $('#form-link').trigger('reset');
                                $('#messaggio').slideToggle('fast').delay(2000).slideToggle('slow');
                                mostraLink();
                            }
                        }
                    });
                });
            }
        });
    });
}

function attivaLink(id, attivo) {
    $.ajax({
        type: "POST",
        url: "./dipendenti.php",
        data: "id=" + id + "&attivo=" + attivo + "&submit=attivaLink",
        dataType: "json",
        success: function () {
            mostraLink();
        }
    });
}

function resetFormazione(id) {
    if (confirm('Stai per fare il reset della formazione, procedere?')) {
        $.ajax({
            type: "POST",
            url: "./dipendenti.php",
            data: "id=" + id + "&submit=resetFormazione",
            dataType: "json",
            success: function () {
                alert('Reset formazione eseguita con successo!');
                return false;
            }
        });
    } else {
        return false;
    }
}