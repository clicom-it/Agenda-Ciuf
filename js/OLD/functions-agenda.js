function listaAttesanodisplay(id) {
    $.ajax({
        type: "POST",
        url: "./mrgest.php",
        data: "id=" + id + "&submit=listaattesa",
        dataType: "json",
        success: function (msg) {
            $('.listaattesasubtit').html(msg.terapista);
            $('.listaattesaappuntamenti').html(msg.lista);
        }

    });
}


function listaAttesa(id) {
    $('.listaattesa').animate({right: '0'});
    $.ajax({
        type: "POST",
        url: "./mrgest.php",
        data: "id=" + id + "&submit=listaattesa",
        dataType: "json",
        success: function (msg) {
            $('.listaattesasubtit').html(msg.terapista);
            $('.listaattesaappuntamenti').html(msg.lista);
        }
    });
}

function setTerapista(id) {
    if (id) {
        $('#bottonelistaattesa').show().html("<a href=\"javascript:;\" onclick=\"listaAttesa('" + id + "');\"><i class=\"fa fa-list fa-lg\" aria-hidden=\"true\"></i> Lista Attesa</a>");
        $('input.timepicker').timepicker('destroy');

        $.ajax({
            type: "POST",
            url: "./mrgest.php",
            data: "id=" + id + "&submit=setterapista",
            dataType: "json",
            success: function (msg) {
                $('#idambulatorio').val(msg.ambulatorio);

                $('.presenze_inizio').html(msg.presenza);

                var arrdurata = msg.durata.split(':');

                ore = parseInt(arrdurata[0] * 60);
                minuti = parseInt(arrdurata[1]);

                iniziovisita = msg.orarioinizio;

                durata = parseInt(ore + minuti);

                if (!durata) {
                    durata = 60;
                }

                $('input.timepicker').timepicker({
                    timeFormat: 'HH:mm',
                    startTime: iniziovisita,
                    minTime: new Date(0, 0, 0, 8, 0, 0),
                    maxTime: new Date(0, 0, 0, 20, 0, 0),
                    interval: durata,
                    dynamic: false
                });
                filtraCal('', id);
            }

        });
    } else {
        $('#idambulatorio').val("0");
        $('#calendar').fullCalendar('destroy');
        $('#listaattesa').show().html("");
    }
}

function filtraCal(ida, idt) {
    if (ida) {
        $('#filtroterapisti').val("");
    }
    if (idt) {
        $('#filtrotabulatori').val("");
    }
    calendario(ida, idt);
}

function filtraCal2(ida, idt) {
    if (ida) {
        var url = "./library/calendario.php?ambulatorio=" + ida;
    }
    if (idt) {
        url = "./library/calendario.php?terapista=" + idt;
    }

    var events = {
        url: url
    };

    $('#calendar').fullCalendar('removeEventSource', events);
    $('#calendar').fullCalendar('addEventSource', events);
    $('#calendar').fullCalendar('refetchEvents');
}


/* calendario presenze */

function calendarioPresenze() {

    $('.mostracal').html("<div id='calendar'></div>");

    var url = "./library/calendariopresenze.php";


    $('#calendar').fullCalendar({
        theme: true,
        header: {
            left: 'prevYear nextYear prev,next today',
            center: 'title',
            right: 'month agendaDay agendaWeek twoWeek'
        },
        views: {
            twoWeek: {
                type: 'agenda',
                duration: {weeks: 2},
                rows: 2
            },
            agendaWeek: {

            }
        },
        allDaySlot: false,
        minTime: '07:00:00',
        maxTime: '21:00:00',
        height: 750,
        selectable: true,
        selectHelper: true,
        timeFormat: 'HH:mm',
        defaultView: 'month',
        slotDuration: '00:15:00',
        events: url,
        eventRender: function (event, element, view) {
            element.find('.fc-time').hide();
        }
    });
}

function aggiornaListaattesa(id) {
    $('#insmodlavoro').show('slow');
    goup();
    aggiornalavoro(id);
    $('#listaattesa').prop('checked', true);
}


function cancellaListaattesa(id, idterapista) {
    $.confirm({
        title: 'ATTENZIONE!',
        content: 'Stai per eliminare una lista attesa, confermi!',
        boxWidth: '30%',
        useBootstrap: false,
        buttons: {
            confirm: {
                text: 'SI',
                btnClass: 'btn-blue',
                keys: ['enter', 'shift'],
                action: function () {
                    $.ajax({
                        type: "POST",
                        url: "./mrgest.php",
                        data: "id=" + id + "&submit=deleteevento",
                        dataType: "json",
                        success: function (msg) {
                            listaAttesa(idterapista);
                            filtraCal("", idterapista);
                        }
                    });
                }
            },
            cancel: {
                text: 'NO'
            }
        }
    });
}

function mostraStatsSpoki() {
    $('.showcont').show().load('./form/form-spoki.php', function () {
        $.validator.messages.required = '';
        $("#stampaappuntamenti").validate({
            submitHandler: function () {
                $("#submitstampeappuntamenti").ready(function () {
                    var datastring = $("#stampaappuntamenti *").not(".nopost").serialize();
                    $.ajax({
                        type: "POST",
                        url: "./mrgest.php",
                        data: datastring + "&submit=submitstampaspoki",
                        dataType: "json",
                        success: function (msg) {
                            if (msg.msg === "ko") {
                                alert(msg.msgko);
                            } else {
                                $('#contieniapp').html(msg.html);
                            }
                        }
                    });

                });
            }
        });
    });
}

function mostraOpenAI() {
    $('.showcont').show().load('./form/form-openai.php', function () {

    });
}

function mostraStampeappuntamenti() {
    $('.showcont').show().load('./form/form-appuntamenti.php', function () {
        var atelier = $('#idatelier').val();
        richiamaclienti(atelier);
        $.validator.messages.required = '';
        $("#stampaappuntamenti").validate({
            submitHandler: function () {
                $("#submitstampeappuntamenti").ready(function () {
                    var datastring = $("#stampaappuntamenti *").not(".nopost").serialize();
                    $.ajax({
                        type: "POST",
                        url: "./mrgest.php",
                        data: datastring + "&submit=submitstampaappuntamenti",
                        dataType: "json",
                        success: function (msg) {
                            if (msg.msg === "ko") {
                                alert(msg.msgko);
                            } else {
//                                $('#contieniapp').html("");
//                                $('#contieniapp').append(msg.dati);
                                if (msg.dati) {
                                    $('#esporta_sel').attr('href', '/mrgest.php?submit=esporta_sel&' + datastring);
                                    $('#esporta_sel').show();
                                    var db = {
                                        loadData: function (filter) {
                                            return $.grep(this.clients, function (client) {
                                                return (!filter.datait.toLowerCase() || client.datait.toLowerCase().indexOf(filter.datait.toLowerCase()) > -1)
                                                        && (!filter.orario.toLowerCase() || client.orario.toLowerCase().indexOf(filter.orario.toLowerCase()) > -1)
                                                        && (!filter.nome.toLowerCase() || client.nome.toLowerCase().indexOf(filter.nome.toLowerCase()) > -1)
                                                        && (!filter.cognome.toLowerCase() || client.cognome.toLowerCase().indexOf(filter.cognome.toLowerCase()) > -1)
                                                        && (!filter.email.toLowerCase() || client.email.toLowerCase().indexOf(filter.email.toLowerCase()) > -1)
                                                        && (!filter.telefono.toLowerCase() || client.telefono.toLowerCase().indexOf(filter.telefono.toLowerCase()) > -1)
                                                        && (!filter.tipoapp.toLowerCase() || client.tipoapp.toLowerCase().indexOf(filter.tipoapp.toLowerCase()) > -1)
                                                        && (!filter.nominativoatelier.toLowerCase() || client.nominativoatelieriente.toLowerCase().indexOf(filter.nominativoatelier.toLowerCase()) > -1)
                                                        && (!filter.dipendente.toLowerCase() || client.dipendente.toLowerCase().indexOf(filter.dipendente.toLowerCase()) > -1)
                                                        && (!filter.datamatrimonio || client.datamatrimonio.indexOf(filter.datamatrimonio) > -1)
                                                        && (!filter.acquistato.toLowerCase() || client.acquistato.toLowerCase().indexOf(filter.acquistato.toLowerCase()) > -1);
                                            });
                                        }
                                    };

                                    window.db = db;

                                    db.clients = msg.dati;
                                    jsGrid.locale("it");

                                    var campi = [
                                        {name: "datait", type: "text", title: "<b>Data appuntamento</b>", css: "customRow", itemTemplate: function (value, item) {
                                                var newDate = new Date(value);
                                                if (isNaN(newDate.getDate())) {
                                                    return '';
                                                } else {
                                                    var giorno = newDate.getDate();
                                                    var mese = newDate.getMonth() + 1;
                                                    var newDateIt = (giorno < 10 ? '0' : '') + giorno + '/' + (mese < 10 ? '0' : '') + mese + '/' + newDate.getFullYear();
                                                    return newDateIt;
                                                }

                                            }},
                                        {name: "orario", type: "text", title: "<b>Ora appuntamento</b>", css: "customRow"},
                                        {name: "nome", type: "text", title: "<b>Nome</b>", css: "customRow"},
                                        {name: "cognome", type: "text", title: "<b>Cognome</b>", css: "customRow"},
                                        {name: "email", type: "text", title: "<b>E-mail</b>", css: "customRow"},
                                        {name: "telefono", type: "text", title: "<b>Telefono</b>", css: "customRow"},
                                        {name: "tipoapp", type: "text", title: "<b>Tipo</b>", css: "customRow"},
                                        {name: "nominativoatelier", type: "text", title: "<b>Atelier</b>", css: "customRow"},
                                        {name: "dipendente", type: "text", title: "<b>Gestito da</b>", css: "customRow"},
                                        {name: "datamatrimonio", type: "date", title: "<b>Data Matrimonio</b>", css: "customRow", itemTemplate: function (value, item) {
                                                var newDate = new Date(value);
                                                if (isNaN(newDate.getDate())) {
                                                    return '';
                                                } else {
                                                    var giorno = newDate.getDate();
                                                    var mese = newDate.getMonth() + 1;
                                                    var newDateIt = (giorno < 10 ? '0' : '') + giorno + '/' + (mese < 10 ? '0' : '') + mese + '/' + newDate.getFullYear();
                                                    return newDateIt;
                                                }

                                            }},
                                        {name: "acquistato", type: "text", title: "<b>Il cliente ha acquistato</b>", css: "customRow"},
                                        {type: "control", editButton: false, deleteButton: false, itemTemplate: function (value, item) {
                                                var $result = jsGrid.fields.control.prototype.itemTemplate.apply(this, arguments);

                                                var $myButton = $("<i style=\"margin-left: 5px; color: #ff0000;\"class=\"fa fa-file-pdf-o fa-lg\" aria-hidden=\"true\"></i>")
                                                        .click(function (e) {
                                                            var idd = item.id;
                                                            window.open('./pdf/appuntamento.php?idapp=' + idd + '', '_blank');
                                                            e.stopPropagation();
                                                        });


                                                var editextra = item.editextra;
                                                if (editextra > 0) {
                                                    var $myButton2 = $("<i style=\"margin-left: 5px; color: #2E65A1;\"class=\"fa fa-edit fa-lg\" aria-hidden=\"true\"></i>")
                                                            .click(function (e) {
                                                                var idd = item.id;
                                                                javascript:editstep2(idd, '1');
                                                                e.stopPropagation();
                                                            });
                                                } else {
                                                    var $myButton2 = "";
                                                }
                                                return $result.add($myButton).add($myButton2);

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
                                            //aggiorna(ide);
                                        }
                                    });
                                } else {
                                    $('#contieniapp').html("Nessun risultato trovato");
                                }
                            }
                        }
                    });
                    $('#contieniapp').html("<div id=\"jsGrid\"></div>");
                });
            }
        });
    });
}

function mostraStampeagenda() {
    $('.showcont').show().load('./form/form-stampaagenda.php', function () {
        $.validator.messages.required = '';
        $("#stampaappuntamenti").validate({
            submitHandler: function () {
                $("#submitstampeappuntamenti").ready(function () {
                    var datastring = $("#stampaappuntamenti *").not(".nopost").serialize();

                    $.ajax({
                        type: "POST",
                        url: "./mrgest.php",
                        data: datastring + "&submit=submitstampaagenda",
                        dataType: "json",
                        success: function (msg) {
                            if (msg.msg === "ko") {
                                alert(msg.msgko);
                            } else {
                                $('#contieniapp').html("");
                                $('#contieniapp').append(msg.dati);
                            }
                        }
                    });
                });
            }
        });
    });
}

function pulisciidcliente() {
    if ($('#cliente').val() === "") {
        $('#idcliente').val("");
        $('#telefono').val("");
    }
}

function puliscidata() {
    if ($('#datap').val() === "") {
        $('#data').val("");
    }
}

function puliscidata2() {
    if ($('#datap2').val() === "") {
        $('#data2').val("");
    }
}

function richiamaclienti(idatelier, solo_sartoria) {
    //console.log($('select#idatelier').length);
    if ($('select#idatelier').length > 0) {
        solo_sartoria = $('#idatelier option:selected').data('solo_sartoria');
        //console.log(solo_sartoria);
    }
    $.ajax({
        type: "POST",
        url: "./mrgest.php",
        data: "idatelier=" + idatelier + "&solo_sartoria=" + (solo_sartoria ? solo_sartoria : 0) + "&submit=richiamaclienti",
        dataType: "json",
        success: function (msg) {
            var daticlienti = msg.clienti;
            var clienti = $.map(daticlienti, function (item) {
                return {
                    label: item.cognome + " " + item.nome + " " + item.azienda,
                    id: item.id,
                    nome: item.nome,
                    cognome: item.cognome,
                    azienda: item.azienda,
                    email: item.email,
                    indirizzo: item.indirizzo,
                    comune: item.comune,
                    cap: item.cap,
                    sesso: item.sesso,
                    telefono: item.telefono,
                    provincia: item.provincia,
                    codicefiscale: item.codicefiscale,
                    piva: item.piva,
                    metodopagamento: item.metodopagamento,
                    fm_vf: item.fm_vf,
                    /* destinazione fattura */
                    nominativo: item.nominativo,
                    indirizzospedizione: item.indirizzospedizione,
                    regionespedizione: item.regionespedizione,
                    provinciaspedizione: item.provinciaspedizione,
                    comunespedizione: item.comunespedizione,
                    capspedizione: item.capspedizione
                };
            });

//            $("#cliente").autocomplete({
//                source: clienti,
//                select: function (event, ui) {
//                    $('#idcliente').val(ui.item.id);
//                    $('#nome').val(ui.item.nome);
//                    $('#cognome').val(ui.item.cognome);
//                    $('#sesso').val(ui.item.sesso);
//                    $('#provincia').val(ui.item.provincia);
//                    $('#comune').val(ui.item.comune);
//                    $('#telefono').val(ui.item.telefono);
//                    $('#email').val(ui.item.email);
//
//                    selcomune(ui.item.provincia, ui.item.comune);
//
//                }
//            });
        }
    });

}