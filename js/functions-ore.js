function roundTo(value, decimals) {
    var i = value * Math.pow(10, decimals);
    i = Math.round(i);
    return i / Math.pow(10, decimals);
}

function setorariolavoro(id) {
    $.ajax({
        type: "POST",
        url: "./ore.php",
        data: "id=" + id + "&submit=impostaorariolavoro",
        dataType: "json",
        success: function (msg) {
            $('#oredacontratto').val(msg.orario);
            operazioniOre();
        }
    });
}

function calendario() {
    $('.showcont').html("<div id='calendar' style='font-size: 1.3em;'></div>");
    $('#calendar').fullCalendar({
        theme: true,
        header: {
            left: 'prevYear nextYear prev,next today',
            center: 'title',
            right: 'month'
        },
        height: 650,
        selectable: true,
        selectHelper: true,
        timeFormat: 'HH:mm',
        events: "./library/ore-calendario.php",
        select: function (start, end, jsEvent, view) {
            var datastartformattata = start.format("DD/MM/YYYY");
            var datastartdb = start.format("YYYY-MM-DD");
            aggiungi(datastartformattata, datastartdb);
        },
        eventDrop: function (event, delta, revertFunc) {
            if (!confirm("Si sta variando la data delle ore inserite, confermare?")) {
                revertFunc();
            } else {
                cambiadataore(event.id, event.start.format("YYYY-MM-DD"));
                $('#calendar').html("");
                calendario();
            }
        },
        eventRender: function (event, element) {
            element.prepend("<span class='deleteevent' style='color: #990000; float: right; padding-left: 3px;'><i class=\"fa fa-times-circle fa-lg\" aria-hidden=\"true\"></i></span> <span class='editevent' style='color: #000000; float: right;'><i class=\"fa fa-pencil-square-o fa-lg\" aria-hidden=\"true\"></i></span>");
            element.find(".editevent").click(function () {
                aggiornaidev(event._id);
            });
            element.find(".deleteevent").click(function () {
                if (!confirm("Stai per eliminare le ore inserite, confermare?")) {
                    return false;
                } else {
                    $('#calendar').fullCalendar('removeEvents', event._id);
                    $.ajax({
                        type: "POST",
                        url: "./ore.php",
                        data: "id=" + event._id + "&submit=deleteore",
                        dataType: "json",
                        success: function (msg) {
                        }
                    });
                }
            });
        }
    });
}

function cambiadataore(id, data) {
    $.ajax({
        type: "POST",
        url: "./ore.php",
        data: "id=" + id + "&data=" + data + "&submit=cambiadataore",
        dataType: "json",
        success: function (msg) {
        }
    });
}

/* function aggiungi */

function aggiungi(datastartformattata, datastartdb) {
    $('.showcont').show().load('./form/form-ore.php', function () {

        $('.contienirighe').sortable({
            disabled: false,
            cancel: '.nosortable, input, select',
            cursor: 'move'
        });

        setorariolavoro($('#idutente').val());

        $('#datap').val(datastartformattata);
        $('#data').val(datastartdb);
        $.validator.messages.required = '';
        $("#formore").validate({
            submitHandler: function () {
                $("#submitformore").ready(function () {
                    var datastring = $("#formore *").not(".nopost").serialize();
                    $.ajax({
                        type: "POST",
                        url: "./ore.php",
                        data: datastring + "&submit=submitformore",
                        dataType: "json",
                        success: function (msg) {
                            if (msg.msg === "ko") {
                                alert(msg.msgko);
                            } else {
                                calendario();
                            }
                        }
                    });
                });
            }
        });
    });
}

function aggiornaidev(id) {
    /*richiama dati*/
        $.ajax({
            type: "POST",
            url: "./ore.php",
            data: "id=" + id +"&submit=richiamaevento",
            dataType: "json",
            success: function (msg) {
                
                aggiorna(msg.idutente, msg.data);

            }
        });
}

function aggiorna(idutente, giorno) {
    if (!idutente) {
        idutente = $('#idutente').val();
    }

    if (!giorno) {
        giorno = $('#data').val();
    } else {
        giornoa = giorno.split("/");
        giorno = giornoa[2] + "-" + giornoa[1] + "-" + giornoa[0];
    }
    $('.showcont').show().load('./form/form-ore.php', function () {
        
        setorariolavoro(idutente);

        /*richiama dati*/
        $.ajax({
            type: "POST",
            url: "./ore.php",
            data: "idutente=" + idutente + "&data=" + giorno + "&submit=richiamagiornoore",
            dataType: "json",
            success: function (msg) {
                /* riempio i campi */
                $.each(msg['valori'][0], function (index, value) {
                    $("#" + index).val(value);
                });
                var giornoarr = giorno.split('-');
                $('#datap').val(giornoarr[2] + "/" + giornoarr[1] + "/" + giornoarr[0]);
                $('#data').val(giorno);
                if (msg.voci) {
                    for (var i = 0; i < msg['voci'].length; i++) {
                        $('.contienirighe').append('<div class="rigaadd riga_prodotto_comm sizing">\n\
                                                <i class="fa fa-arrows-v fa-lg ordinarighe" aria-hidden="true"></i>\n\
                                                <input type="text" name="dettvocecomm[]" class="cercavocicomm input_moduli sizing float_moduli_40" placeholder="Cerca lavorazione" title="Cerca lavorazione" value="' + msg['voci'][i].dettvocecomm + '" />\n\
                                                <input type="hidden" name=\"idvocecomm[]\" value="' + msg['voci'][i].idvocecomm + '" />\n\
                                                <input type="text" name="descr[]" value="' + msg['voci'][i].descr + '" class="input_moduli sizing float_moduli_50" placeholder="Descrizione" title="Descrizione" />\n\
                                                <input type="text" name="orelavorate[]" value="' + msg['voci'][i].orelavorate + '" class="orelav timepickerlav input_moduli sizing float_moduli_small_10" placeholder="Costo" title="Costo" /><a href="javascript:;" class="remove_button"><i style="color: #CD0A0A; line-height: 35px;" class="fa fa-times fa-lg" aria-hidden="true"></i></a>\n\
                                                <div class="chiudi"></div>\n\
                                                </div>');
                        $("[vocecomm=vocecomm" + i + "]").val(msg['voci'][i].idvocecomm);
                    }

                }

                var dativocicommesse = msg.commessevoci;
                var vocicomm = $.map(dativocicommesse, function (item) {
                    return {
                        label: item.dativocecomm,
                        id: item.idvocecomm
                    };
                });
                $(".cercavocicomm").autocomplete({
                    source: vocicomm,
                    select: function (event, ui) {
                        $(this).next('input').val(ui.item.id);
                        /**/
                    }
                });

                $('input.timepickerlav').timepicker({
                    timeFormat: 'HH:mm',
                    minTime: new Date(0, 0, 0, 0, 15, 0),
                    maxTime: new Date(0, 0, 0, 12, 0, 0),
                    interval: 15,
                    dynamic: false,
                    change: function (time) {
                        totaleOre();
                    }
                });
                $('.timepickerum, .timepickerup').prop('disabled', false);
                $('#idutente').val(idutente);
                totaleOre();
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
        $("#formore").validate({
            submitHandler: function () {
                $("#submitformore").ready(function () {
                    var datastring = $("#formore *").not(".nopost").serialize();
                    var data = $('#datap').val();
                    $.ajax({
                        type: "POST",
                        url: "./ore.php",
                        data: datastring + "&submit=editformore",
                        dataType: "json",
                        success: function (msg) {
                            if (msg.msg === "ko") {
                                alert(msg.msgko);
                            } else {
                                $('#messaggio').slideToggle('fast').delay(2000).slideToggle('slow');
//                                aggiorna(idutente, data);
                                calendario();
                            }
                        }
                    });
                });
            }
        });
    });
}

function totaleOre() {
    var hour = 0;
    var minute = 0;
    $('.orelav').each(function () {
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
    $('#oretotalilavorategiorno').val(hour + ":" + minute);
//    $('#oretotalilavorategiorno').val(oretotali);
}

function operazioniOre() {

    var oredacontratto = $('#oredacontratto').val();

    var time1 = $('#entratamattino').val();
    var time2 = $('#uscitamattino').val();
    var time3 = $('#entratapomeriggio').val();
    var time4 = $('#uscitapomeriggio').val();

    var ferie = $('#ferie').val();
    var permesso = $('#permesso').val();
    var rol = $('#rol').val();
    var malattia = $('#malattia').val();

    if (!time1) {
        time1 = "00:00";
    }
    if (!time2) {
        time2 = "00:00";
    }
    if (!time3) {
        time3 = "00:00";
    }
    if (!time4) {
        time4 = "00:00";
    }
    if (!ferie) {
        ferie = "00:00";
    }
    if (!permesso) {
        permesso = "00:00";
    }
    if (!rol) {
        rol = "00:00";
    }
    if (!malattia) {
        malattia = "00:00";
    }
    /* orari timbrate */
    var splitTime1 = time1.split(':');
    var splitTime2 = time2.split(':');
    var splitTime3 = time3.split(':');
    var splitTime4 = time4.split(':');
    /**/

    /* orario timbrate mattino */
    oremattino = parseInt(splitTime2[0]) - parseInt(splitTime1[0]);
    minutimattino = parseInt(splitTime2[1]) - parseInt(splitTime1[1]);

    /* orario timbrate pomeriggio */
    orepomeriggio = parseInt(splitTime4[0]) - parseInt(splitTime3[0]);
    minutipomeriggio = parseInt(splitTime4[1]) - parseInt(splitTime3[1]);

    /* ferie, permessi, ecc. */
    var arrFerie = ferie.split(':');
    var arrPermesso = permesso.split(':');
    var arrRol = rol.split(':');
    var arrMalattia = malattia.split(':');
    /**/
    /* calcolo totale ore giorno */
    var hour = 0;
    var minute = 0;

    hour = parseInt(oremattino + orepomeriggio + parseInt(arrFerie[0]) + parseInt(arrPermesso[0]) + parseInt(arrRol[0]) + parseInt(arrMalattia[0]));
    minute = parseInt(minutimattino + minutipomeriggio + parseInt(arrFerie[1]) + parseInt(arrPermesso[1]) + parseInt(arrRol[1]) + parseInt(arrMalattia[1]));

    minutisommareore = parseInt(minute / 60);

    hour = hour + minutisommareore;
    minute = minute % 60;
    /**/
    /* ore da contratto */
    arrOredacontratto = oredacontratto.split(':');

    orecontratto = arrOredacontratto[0];
    minuticontratto = arrOredacontratto[1];
    /**/
    if (hour > 0 || hour == 0) {
        if (minute > 0) {
            oretotalicalcolo = parseFloat(hour + ".5").toFixed(1);
        } else {
            oretotalicalcolo = parseFloat(hour + '.0').toFixed(1);
        }

        if (minuticontratto > 0) {
            orecontrattocalcolo = parseFloat(orecontratto + ".5").toFixed(1);
        } else {
            orecontrattocalcolo = parseFloat(orecontratto + '.0').toFixed(1);
        }

        diff = (parseFloat(oretotalicalcolo) - parseFloat(orecontrattocalcolo)).toFixed(1);

        if (diff > 0) {

            arrDiff = diff.split('.');

            if (arrDiff[1] > 0) {
                differenza = arrDiff[0] + ":30";
            } else {
                differenza = arrDiff[0] + ":00";
            }

            $('#orestraordinario').val(differenza);
            $('#oreordinarie').val(orecontratto + ":" + minuticontratto);
            if ($('#oreordinarie').val() == "00:00") {
                $('#oreordinarie').val("");
            }
        } else {

            arrOretot = oretotalicalcolo.split('.');

            if (arrOretot[1] > 0) {
                oretotali = arrOretot[0] + ":30";
            } else {
                oretotali = arrOretot[0] + ":00";
            }

            $('#orestraordinario').val("");
            $('#oreordinarie').val(oretotali);
            if ($('#oreordinarie').val() == "0:00") {
                $('#oreordinarie').val("");
            }
        }
    }
}