function mostraClientifornitori(id, filtri) {
    var op = getUrlParameter("op");
    if (!id) {
        if (op === "clienti") {
            id = '1';
        } else {
            id = '2';
        }
    }

    if (filtri) {
        var filtriarr = filtri.split("||");
        var filtro0 = {nome: filtriarr[0]};
        var filtro1 = {cognome: filtriarr[1]};
        var filtro2 = {sesso: filtriarr[2]};
        var filtro3 = {email: filtriarr[3]};
        var filtro4 = {telefono: filtriarr[4]};
    }

    $.ajax({
        type: "POST",
        url: "./clientifornitori.php",
        data: "id=" + id + "&submit=tutticlientifornitori",
        dataType: "json",
        success: function (msg) {
            var db = {
                loadData: function (filter) {
                    return $.grep(this.clients, function (client) {
                        return (!filter.nome.toLowerCase() || client.nome.toLowerCase().indexOf(filter.nome.toLowerCase()) > -1)
                                && (!filter.cognome.toLowerCase() || client.cognome.toLowerCase().indexOf(filter.cognome.toLowerCase()) > -1)
                                && (!filter.sesso.toLowerCase() || client.sesso.indexOf(filter.piva) > -1)
                                && (!filter.email.toLowerCase() || client.email.toLowerCase().indexOf(filter.email.toLowerCase()) > -1)
                                && (!filter.telefono || client.telefono.indexOf(filter.telefono) > -1);
                    });
                }
            };

            window.db = db;

            db.clients = msg.dati;
            jsGrid.locale("it");
            if (id === '1') {
                var campi = [
                    {name: "nome", type: "text", title: "<b>Nome</b>", css: "customRow", filterTemplate: createTextFilterTemplate("nome", filtro0)},
                    {name: "cognome", type: "text", title: "<b>Cognome</b>", css: "customRow", filterTemplate: createTextFilterTemplate("cognome", filtro1)},
                    {name: "sesso", type: "text", title: "<b>Sesso</b>", css: "customRow", filterTemplate: createTextFilterTemplate("sesso", filtro2)},
                    {name: "email", type: "text", title: "<b>E-mail</b>", css: "customRow", filterTemplate: createTextFilterTemplate("email", filtro3)},
                    {name: "telefono", type: "text", title: "<b>Telefono</b>", css: "customRow", filterTemplate: createTextFilterTemplate("telefono", filtro4)},
                    {type: "control"}
                ];
            } else if (id === '2') {
                var campi = [
//                    {name: "nome", type: "text", title: "<b>Nome</b>", css: "customRow"},
//                    {name: "cognome", type: "text", title: "<b>Cognome</b>", css: "customRow"},
                    {name: "azienda", type: "text", title: "<b>Azienda</b>", css: "customRow", filterTemplate: createTextFilterTemplate("azienda", filtro0)},
                    {name: "piva", type: "text", title: "<b>P.Iva</b>", css: "customRow", filterTemplate: createTextFilterTemplate("piva", filtro1)},
                    {name: "codicefiscale", type: "text", title: "<b>C.F.</b>", css: "customRow", filterTemplate: createTextFilterTemplate("codicefiscale", filtro2)},
                    {name: "email", type: "text", title: "<b>E-mail</b>", css: "customRow", filterTemplate: createTextFilterTemplate("email", filtro3)},
                    {name: "telefono", type: "text", title: "<b>Telefono</b>", css: "customRow", filterTemplate: createTextFilterTemplate("telefono", filtro4)},
                    {type: "control"}
                ];
            }

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
                        url: "./clientifornitori.php",
                        data: "id=" + idd + "&submit=delete",
                        dataType: "json",
                        success: function () {
                        }
                    });
                },
                // edita item
                onItemEditing: function (args) {
                    args.cancel = true;
                    var filtri = $("#jsGrid").jsGrid("getFilter").nome + "||" + $("#jsGrid").jsGrid("getFilter").cognome + "||"
                            + $("#jsGrid").jsGrid("getFilter").sesso + "||"
                            + $("#jsGrid").jsGrid("getFilter").email + "||" + $("#jsGrid").jsGrid("getFilter").telefono;
                    var ide = args.item.id;
                    aggiorna(ide, filtri, id);
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

function selcomune(prov, comune = "") {
    $.ajax({
        type: "POST",
        url: "./mrgest.php",
        data: "prov=" + prov + "&comune=" + comune + "&submit=selcom",
        dataType: "json",
        success: function (msg) {
            $('#comune').html(msg.msg);

        }
    });
}

/* function aggiungi */

function aggiungi(tipo) {
    $('.showcont').show().load('./form/form-clientifornitori.php', function () {
        /**/
        if (tipo) {
            $('#tipo').val(tipo);
        }
        $.validator.messages.required = '';
        $("#formutenti").validate({
            rules: {
                email: {
                    email: true
                }
            },
            submitHandler: function () {
                $("#submitformutenti").ready(function () {

                    var datastring = $("#formutenti *").not(".nopost").serialize();
                    $.ajax({
                        type: "POST",
                        url: "./clientifornitori.php",
                        data: datastring + "&submit=submitformutenti",
                        dataType: "json",
                        success: function (msg) {
                            if (msg.msg === "ko") {
                                alert(msg.msgko);
                            } else {
                                $('#formutenti').trigger('reset');
                                $('#messaggio').slideToggle('fast').delay(2000).slideToggle('slow');
                            }
                        }
                    });
                });
            }
        });
    });
}

function aggiorna(id, filtri, idclifor) {
    $('.showcont').show().load('./form/form-clientifornitori.php', function () {
        /*richiamadati*/
        $.ajax({
            type: "POST",
            url: "./clientifornitori.php",
            data: "id=" + id + "&submit=richiamaclientefornitore",
            dataType: "json",
            success: function (msg) {
                $('.bottone_chiudi').html("<a href=\"javascript:;\" class=\"sizing\" onclick=\"mostraClientifornitori('" + idclifor + "', '" + filtri + "');\">Chiudi</a>");
                /* riempio i campi */
                $.each(msg['valori'][0], function (index, value) {
                    $("#" + index).val(value);
                });
                if ($('#amministrazione').val() === '1') {
                    $('#amministrazione').prop('checked', true);
                } else {
                    $('#amministrazione').val('1');
                }
                
                setTimeout(function () {
                    selcomune(msg['valori'][0]['provincia'], msg['valori'][0]['comune']);
//                        setProvincia(msg['valori'][0]['regionespedizione'], msg['valori'][0]['provinciaspedizione'], '2');
                    }, 200);
                
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

//                    $.ajax({
//                        url: setProvincia(msg['valori'][0]['regione'], msg['valori'][0]['provincia'], '1'),
//                        success: function () {
//                            $.ajax({
//                                url: setComune(msg['valori'][0]['provincia'], msg['valori'][0]['comune'], '1'),
//                                success: function () {
//                                    setCap(msg['valori'][0]['comune'], msg['valori'][0]['cap'], '1');
//                                }
//                            });
//                        }
//                    });

                }
                if (msg['valori'][0]['nazionespedizione'] != 'IT' && msg['valori'][0]['nazionespedizione'] != '') {
                    var title = $('select#comunespedizione option:first').html();
                    $('select#comunespedizione').replaceWith('<input type="text" name="comunespedizione" id="comunespedizione" class="input_moduli sizing float_moduli" value="' + msg['valori'][0]['comunespedizione'] + '" />');
                    title = $('select#capspedizione option:first').html();
                    $('select#capspedizione').replaceWith('<input type="text" name="capspedizione" id="capspedizione" class="input_moduli sizing float_moduli" value="' + msg['valori'][0]['capspedizione'] + '" />');
                    $('#regionespedizione, #provinciaspedizione').hide();
                } else {
                    // 2 indirizzo per spedizione
//                    setTimeout(function () {
//                        setProvincia(msg['valori'][0]['regionespedizione'], msg['valori'][0]['provinciaspedizione'], '2');
//                    }, 200);
//                    setTimeout(function () {
//                        setComune(msg['valori'][0]['provinciaspedizione'], msg['valori'][0]['comunespedizione'], '2');
//                    }, 500);
//                    setTimeout(function () {
//                        setCap(msg['valori'][0]['comunespedizione'], msg['valori'][0]['capspedizione'], '2');
//                    }, 800);

//                    $.ajax({
//                        url: setProvincia(msg['valori'][0]['regionespedizione'], msg['valori'][0]['provinciaspedizione'], '2'),
//                        success: function () {
//                            $.ajax({
//                                url: setComune(msg['valori'][0]['provinciaspedizione'], msg['valori'][0]['comunespedizione'], '2'),
//                                success: function () {
//                                    setCap(msg['valori'][0]['comunespedizione'], msg['valori'][0]['capspedizione'], '2');
//                                }
//                            });
//                        }
//                    });


                }
                if (msg['valori'][0]['nazionespedizione']) {
                    $('#spedizione').slideToggle();
                }
            }
        });
        /**/
        $.validator.messages.required = '';
        $("#formutenti").validate({
            rules: {
                email: {
                    email: true
                }
            },
            submitHandler: function () {
                $("#submitformutenti").ready(function () {

                    var datastring = $("#formutenti *").not(".nopost").serialize();
                    var tipo = $('#tipo').val();
                    $.ajax({
                        type: "POST",
                        url: "./clientifornitori.php",
                        data: datastring + "&submit=editformutenti",
                        dataType: "json",
                        success: function (msg) {
                            if (msg.msg === "ko") {
                                alert(msg.msgko);
                            } else {
                                mostraClientifornitori(tipo, filtri);
                            }
                        }
                    });
                });
            }
        });
    });
}

/* importa clienti */
function importa(id) {
    $('.showcont').show().load('./form/form-importaclientifornitori.php?tipo=' + id + '');
}