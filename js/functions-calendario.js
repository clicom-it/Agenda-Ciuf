// Closure
//var arrAtelierSartoria = [65, 59, 136, 140, 100, 97, 81];
var arrAtelierSartoria = [];
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

/* prendi estensione del file */
function getFileExtension(filename)
{
    var ext = /^.+\.([^.]+)$/.exec(filename);
    return ext == null ? "" : ext[1];
}

function roundTo(value, decimals) {
    var i = value * Math.pow(10, decimals);
    i = Math.round(i);
    return i / Math.pow(10, decimals);
}

function aggiornalavoro(id) {
    $('.jconfirm').hide();
    $.ajax({
        type: "POST",
        url: "./mrgest.php",
        data: "id=" + id + "&submit=aggiornalavoro",
        dataType: "json",
        success: function (msg) {
            $('#idlavoro').val(id);
            /* riempio i campi */
            $.each(msg['dati'][0], function (index, value) {
                $("#" + index).val(value).addClass('inEdit');
            });
            if(!isAdmin_global)
                $('#tipoappuntamento').prop('disabled', true);
            selcomune(msg['dati'][0]['provincia'], msg['dati'][0]['comune']);
        }
    });
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

function pulisciidcliente() {
    if ($('#cliente').val() === "") {
        $('#idcliente').val("");
        $('#nome').val("");
        $('#cognome').val("");
        $('#sesso').val("");
        $('#provincia').val("");
        $('#comune').html("<option value=\"\">Seleziona Comune</option>");
        $('#telefono').val("");
        $('#email').val("");
    }
}

function pulisciDatas() {
    if ($('#datas').val() === "") {
        $('#datasaldo').val("");
    }
    if ($('#datac').val() === "") {
        $('#datacap').val("");
    }
    if ($('#datap1').val() === "") {
        $('#datapag1').val("");
    }
    if ($('#datap2').val() === "") {
        $('#datapag2').val("");
    }
    if ($('#datap3').val() === "") {
        $('#datapag3').val("");
    }
    if ($('#dataes').val() === "") {
        $('#dataeffettuatosaldo').val("");
    }
    if ($('#datamatrimoniop').val() === "") {
        $('#datamatrimonio').val("");
    }
    if ($('#datas1').val() === "") {
        $('#datasart1').val("");
    }
    if ($('#datas2').val() === "") {
        $('#datasart2').val("");
    }
    if ($('#datas3').val() === "") {
        $('#datasart3').val("");
    }
}

function calendario(ida) {
    $('.mostracal').html("<div id='calendar'></div>");

    var url = "./library/calendario.php";
    if (ida) {
        url = "./library/calendario.php?ida=" + ida;
    }

    if (screen.width > "768") {
        view = "twoWeek";
        boxwidth = "50%";
    } else {
        view = "basicDay";
        boxwidth = "80%";
    }
    if ($('select#idatelier').length > 0) {
        patrono = $('#idatelier option:selected').data('patrono');
        aperture_spot = $('#idatelier option:selected').data('aperture_spot');
        chiusure_spot = $('#idatelier option:selected').data('chiusure_spot');
        chiuso_dal = $('#idatelier option:selected').data('chiuso_dal');
        chiuso_al = $('#idatelier option:selected').data('chiuso_al');
    } else {
        patrono = $('#idatelier').data('patrono');
        aperture_spot = $('#idatelier').data('aperture_spot');
        chiusure_spot = $('#idatelier').data('chiusure_spot');
        chiuso_dal = $('#idatelier').data('chiuso_dal');
        chiuso_al = $('#idatelier').data('chiuso_al');
    }
    if (patrono != "") {
        holidays.push(patrono);
    }
    //console.log(holidays);
    arrApertureSpot = new Array();
    if (aperture_spot != "" && aperture_spot !== null && aperture_spot) {
        var arrSpot = aperture_spot.split(',');
        for (var p = 0; p < arrSpot.length; p++) {
            var arrDataSpot = arrSpot[p].split('/');
            arrApertureSpot.push(arrDataSpot[2] + '-' + arrDataSpot[1] + '-' + arrDataSpot[0]);
        }
    }
    arrChiusureSpot = new Array();
    if (chiusure_spot != "" && chiusure_spot !== null && chiusure_spot) {
        var arrSpot = chiusure_spot.split(',');
        for (var p = 0; p < arrSpot.length; p++) {
            var arrDataSpot = arrSpot[p].split('/');
            arrChiusureSpot.push(arrDataSpot[2] + '-' + arrDataSpot[1] + '-' + arrDataSpot[0]);
        }
    }
    $('#calendar').fullCalendar({
        theme: true,
        header: {
            left: 'prevYear nextYear prev,next today',
            center: 'title',
            right: 'month basicDay basicWeek twoWeek'
        },

        views: {
            twoWeek: {
                type: 'basic',
                duration: {weeks: 2},
                rows: 2
            }
        },
        height: 650,
        selectable: true,
        selectHelper: true,
        timeFormat: 'HH:mm',
        defaultView: view,
        events: url,
//        select: function (start, end, jsEvent, view) {
//            var datastartformattata = start.format("DD/MM/YYYY");
//            var datastartdb = start.format("YYYY-MM-DD");
//            aggiungical(datastartformattata, datastartdb);
//        },


//        eventClick: function (event) {
//           $.ajax({
//                    type: "POST",
//                    url: "./mrgest.php",
//                    data: "id=" + event._id + "&submit=infoevento",
//                    dataType: "json",
//                    success: function (msg) {
//                        $.dialog({
//                            title: 'DETTAGLIO APPUNTAMENTO',
//                            content: msg.msg,
//                            boxWidth: boxwidth,
//                            useBootstrap: false,
//                            type: 'blue'
//                        });
//                    }
//                });
//        },
        eventDrop: function (event, delta, revertFunc) {

            $.confirm({
                title: 'ATTENZIONE!',
                content: 'Si sta variando la data di un appuntamento, CONFERMI?',
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
                                data: "id=" + event.id + "&data=" + event.start.format("YYYY-MM-DD") + "&submit=cambiadataevento",
                                dataType: "json",
                                success: function (msg) {
                                }
                            });
                        }
                    },
                    cancel: {
                        text: 'NO',
                        action: function () {
                            revertFunc();
                        }
                    }
                }
            });
        },
        dayRender: function (date, cell) {
            //console.log(date);
            var datestring = jQuery.datepicker.formatDate('yy-mm-dd', date._d);
            var datestring_ma = jQuery.datepicker.formatDate('mm-dd', date._d);
            if (pasqua.indexOf(datestring) >= 0) {
                cell.css("background-color", "red");
            }
            if (holidays.indexOf(datestring_ma) >= 0) {
                cell.css("background-color", "red");
                console.log(date);
            }
            if (arrApertureSpot.indexOf(datestring) >= 0) {
                cell.css("background-color", "white");
            }
            if (arrChiusureSpot.indexOf(datestring) >= 0) {
                cell.css("background-color", "red");
            }
            if (chiuso_dal != '' && chiuso_al != '') {
                if (datestring >= chiuso_dal && datestring <= chiuso_al) {
                    cell.css("background-color", "red");
                }
            }
//            var today = new Date();
//            var end = new Date();
//            end.setDate(today.getDate() + 7);
//
//            if (date.getDate() === today.getDate()) {
//                cell.css("background-color", "red");
//            }
//
//            if (date > today && date <= end) {
//                cell.css("background-color", "yellow");
//            }

        },
        eventRender: function (event, element) {
            var icoacquistato = "";
            //console.log(event);
            var datestring = jQuery.datepicker.formatDate('yy-mm-dd', event.start._d);
            var datestring_ma = jQuery.datepicker.formatDate('mm-dd', event.start._d);
            if (event.acquistato == "") {
                icoacquistato = "<i class=\"fa fa-exclamation-triangle fa-lg\" aria-hidden=\"true\" style=\"color:yellow\"></i>";
            } else if (event.acquistato == "1") {
                icoacquistato = "<i class=\"fa fa-check fa-lg\" aria-hidden=\"true\" style=\"color:green\"></i>";
            } else if (event.acquistato == "0") {
                var iconp = "";
                if (event.idnoacquisto == 1) {
                    iconp = "NP";
                }
                icoacquistato = "<i class=\"fa fa-times fa-lg\" aria-hidden=\"true\" style=\"color:red\"></i>" + iconp;
            }
            if (event.provenienza == 'Fiera') {
                icoacquistato += "<i class=\"fa fa-circle fa-lg\" aria-hidden=\"true\" style=\"color:#FF5F1F\"></i>";
            }
            if (event.disdetto == 1) {
                icoacquistato = "<p>&nbsp;</p>";
            }
            //console.log(event.isAddetti);
            if (event.isAddetti === undefined) {
                if ((event.utenteappuntamento == event.utenteattuale) || event.livello == '5' || event.livello == '1' || event.livello == '0' || event.livello == '2') {
                    if (event.livello == '1' || event.livello == '0') {
                        element.prepend("<span class='deleteevent' style='color: #990000; float: right; padding-left: 3px;'><i class=\"fa fa-times-circle fa-lg\" aria-hidden=\"true\"></i></span> <!--<span class='editevent' style='color: #000000; float: right;'><i class=\"fa fa-pencil-square-o fa-lg\" aria-hidden=\"true\"></i></span>-->" + icoacquistato);
                    } else {
                        element.prepend(icoacquistato);
                    }
                } else {
                    element.prepend(icoacquistato);
                }
            } else {

            }


            // manda invito a evento
//                        element.find(".sendevent").click(function () {
//                            $.ajax({
//                                type: "POST",
//                                url: "./mrgest.php",
//                                data: "id=" + event._id + "&submit=mailevento",
//                                dataType: "json",
//                                success: function (msg) {
//                                    $.dialog({
//                                        title: 'INVIO E-MAIL PER LAVORO',
//                                        content: msg.msg,
//                                        boxWidth: '50%',
//                                        useBootstrap: false,
//                                        type: 'blue'
//                                    });
//                                }
//                            });
//                        });
            // info sul lavoro
//            element.find(".infoevent").click(function () {
//                $.ajax({
//                    type: "POST",
//                    url: "./mrgest.php",
//                    data: "id=" + event._id + "&submit=infoevento",
//                    dataType: "json",
//                    success: function (msg) {
//                        $.dialog({
//                            title: 'DETTAGLIO APPUNTAMENTO',
//                            content: msg.msg,
//                            boxWidth: boxwidth,
//                            useBootstrap: false,
//                            type: 'blue'
//                        });
//                    }
//                });
//            });
            if (event.isAddetti === undefined) {
                element.find(".fc-content").click(function () {
                    //console.log(event);
                    $.ajax({
                        type: "POST",
                        url: "./mrgest.php",
                        data: "id=" + event._id + (event.color == '#696969' ? '&sartoria=1' : '&sartoria=0') + "&submit=infoevento",
                        dataType: "json",
                        success: function (msg) {
                            $.dialog({
                                title: 'DETTAGLIO APPUNTAMENTO',
                                content: msg.msg,
                                boxWidth: boxwidth,
                                useBootstrap: false,
                                type: 'blue'
                            });
                        }
                    });
                });
            }

            // modifica lavoro
            element.find(".editevent").click(function () {
                goup();
                aggiornalavoro(event._id);
            });
            // cancella lavoro
            element.find(".deleteevent").click(function () {

                $.confirm({
                    title: 'ATTENZIONE!',
                    content: 'Stai per eliminare un appuntamento, CONFERMI?',
                    boxWidth: '30%',
                    useBootstrap: false,
                    buttons: {
                        confirm: {
                            text: 'SI',
                            btnClass: 'btn-blue',
                            keys: ['enter', 'shift'],
                            action: function () {
                                $('#calendar').fullCalendar('removeEvents', event._id);
                                $.ajax({
                                    type: "POST",
                                    url: "./mrgest.php",
                                    data: "id=" + event._id + "&submit=deleteevento",
                                    dataType: "json",
                                    success: function (msg) {
                                    }
                                });
                            }
                        },
                        cancel: {
                            text: 'NO'
                        }
                    }
                });

            });
        },
        viewRender: function (view, element) {
            getAddetti();
        }
    });
}

function getAddetti() {
    if (($('#contaddetti').html() != "" || $('#idatelier').val() != '')) {
        var idatelier = $('#idatelier').val();
        var solo_sartoria = 0;
        var start = $('#calendar').fullCalendar('getView').start.format('YYYY-MM-DD');
        var end = $('#calendar').fullCalendar('getView').end.format('YYYY-MM-DD');
        var tipo = $('#tipoappuntamento').val();
        if (tipo == '') {
            tipo = 2;
        }
        $.ajax({
            type: "POST",
            url: "./mrgest.php",
            data: "idatelier=" + idatelier + "&solo_sartoria=" + solo_sartoria + "&start=" + start + "&tipo=" + tipo + "&end=" + end + "&submit=seldip",
            dataType: "json",
            success: function (msg) {
                $('#contaddetti').html(msg.addetti);
            }
        });
    }
}

function getDipat(idatelier) {
    if ($('select#idatelier').length > 0) {
        var solo_sartoria = $('#idatelier option:selected').data('solo_sartoria');
    } else {
        var solo_sartoria = $('#solo_sartoria').val();
    }
    if (solo_sartoria == 1) {
        $('#provenienza').removeClass('required');
        $('#tipoappuntamento').val(5);
    } else {
        $('#provenienza').addClass('required');
        //$('#tipoappuntamento').val('');
    }
    var calendar = $('#calendar').fullCalendar('getCalendar');
    var view = calendar.view;
    //var start = view.start._d;
    var start = $('#calendar').fullCalendar('getView').start.format('YYYY-MM-DD');
    var end = $('#calendar').fullCalendar('getView').end.format('YYYY-MM-DD');
    var tipo = $('#tipoappuntamento').val();
    if (tipo == '') {
        tipo = 2;
    }
    $.ajax({
        type: "POST",
        url: "./mrgest.php",
        data: "idatelier=" + idatelier + "&solo_sartoria=" + solo_sartoria + "&start=" + start + "&tipo=" + tipo + "&end=" + end + "&submit=seldip",
        dataType: "json",
        success: function (msg) {
            $('#idutente').html(msg.msg);
            $('#contaperture').html(msg.aperture);
            $('#contaddetti').html(msg.addetti);
            //richiamaclienti(idatelier, solo_sartoria);
        }
    });
}

function setCampiSartoria() {
    if ($('select#idatelier').length > 0) {
        var solo_sartoria = $('#idatelier option:selected').data('solo_sartoria');
        var patrono = $('#idatelier option:selected').data('patrono');
        holidays = ["01-01", "01-06", "04-25", "05-01",
            "06-02", "08-15", "11-01", "12-08",
            "12-25", "12-26",
            "01-01", "01-06", "04-25", "05-01"];
        //console.log(holidays);
        //console.log(arrChiusureSpot);
        if (patrono != "") {
            holidays.push(patrono);
        }
        $("#datap").datepicker("refresh");
    } else {
        var solo_sartoria = $('#solo_sartoria').val();
        var patrono = $('#idatelier').data('patrono');
        console.log(patrono);
        holidays = ["01-01", "01-06", "04-25", "05-01",
            "06-02", "08-15", "11-01", "12-08",
            "12-25", "12-26",
            "01-01", "01-06", "04-25", "05-01"];
        if (patrono != "") {
            holidays.push(patrono);
        }
    }
    if (solo_sartoria == 1) {
        $('#provincia,#provenienza,#comune,#disdetto').hide();
        $('#numero_contratto').show();
    } else {
        $('#provincia,#provenienza,#comune,#disdetto').show();
        $('#numero_contratto').hide();
    }
}

function editstep2(id, stampe, solo_sartoria, isSartoria) {
    $('.jconfirm').hide();
    var atelier_corrente = parseInt($('#idatelier').val());
    //console.log(atelier_corrente);
    //console.log($.inArray(atelier_corrente, arrAtelierSartoria));
    if ($.inArray(atelier_corrente, arrAtelierSartoria) >= 0 && isSartoria == 1) {
        $('.showcont').show().load('./form/form-step2calSartoria.php?stampe=' + stampe + '&id=' + id, function () {
            $.datepicker.setDefaults($.datepicker.regional[ "it" ]);
            $("#datap").datepicker({
                altFormat: "yy-mm-dd",
                altField: "#data"
            });
            $("#datac").datepicker({
                altFormat: "yy-mm-dd",
                altField: "#datacap"
            });
            /* data pag1 */
            $.datepicker.setDefaults($.datepicker.regional[ "it" ]);
            $("#datap1").datepicker({
                altFormat: "yy-mm-dd",
                altField: "#datapag1"
            });
            /* data pag2 */
            $.datepicker.setDefaults($.datepicker.regional[ "it" ]);
            $("#datap2").datepicker({
                altFormat: "yy-mm-dd",
                altField: "#datapag2"
            });
            /* data pag3 */
            $.datepicker.setDefaults($.datepicker.regional[ "it" ]);
            $("#datap3").datepicker({
                altFormat: "yy-mm-dd",
                altField: "#datapag3"
            });
            /* data saldo */
            $.datepicker.setDefaults($.datepicker.regional[ "it" ]);
            $("#datas").datepicker({
                altFormat: "yy-mm-dd",
                altField: "#datasaldo"
            });
            /* data saldo effettuato */
            $.datepicker.setDefaults($.datepicker.regional[ "it" ]);
            $("#dataes").datepicker({
                altFormat: "yy-mm-dd",
                altField: "#dataeffettuatosaldo"
            });
            /* data sartoria 1 */
            $("#datas1").datepicker({
                altFormat: "yy-mm-dd",
                altField: "#datasart1"
            });
            /* data sartoria 2 */
            $("#datas2").datepicker({
                altFormat: "yy-mm-dd",
                altField: "#datasart2"
            });
            /* data sartoria 3 */
            $("#datas3").datepicker({
                altFormat: "yy-mm-dd",
                altField: "#datasart3"
            });
            /* data sartoria 4 */
            $("#datas4").datepicker({
                altFormat: "yy-mm-dd",
                altField: "#datasart4"
            });
            $('input.timepicker').timepicker({
                timeFormat: 'HH:mm',
                minTime: new Date(0, 0, 0, 6, 0, 0),
                maxTime: new Date(0, 0, 0, 20, 0, 0),
                interval: 30,
                dynamic: false,
                dropdown: true,
                scrollbar: true
            });
            $('#mostrapdf').html("<a class=\"sizing\" href=\"./pdf/appuntamentoSartoria.php?idapp=" + id + "\" target=\"new\"><i style=\"color: #fffffff;\" class=\"fa fa-file-pdf-o fa-lg\" aria-hidden=\"true\"></i> Stampa pdf</a>");
            $.ajax({
                type: "POST",
                url: "./mrgest.php",
                data: "id=" + id + "&submit=step2calSartoria",
                dataType: "json",
                success: function (msg) {
                    var idappunt = id;
                    var idmodabito = "";

                    /* riempio i campi */
                    $.each(msg['valori'][0], function (index, value) {
                        $("#" + index).val(value);
                        if (index === "idmodabito") {
                            idmodabito = value;
                        }
                    });
                    $.each(msg['pag_sartoria'][0], function (index, value) {
                        if (index != 'id' && index != 'idappuntamento')
                            $("#" + index).val(value);
                    });
                    $('.box_sartoria').show();
                    /* riempio i campi sartoria */
                    checksartoria('1');
                    var tipoabito = $('#idtipoabito').val();
                    if (tipoabito > 0) {
                        selModabito(tipoabito, idmodabito);
                    }
                    $('#txt-cliente').html(msg['valori'][0].cliente + '<br>E-mail: ' + msg['valori'][0].email + '<br>Tel: ' + msg['valori'][0].telefono + '<br>Data matrimonio: ' + msg['valori'][0].datamatrimonioit);
                }
            });
            /**/
            $.validator.messages.required = '';
            $("#formstep2cal").validate({
                rules: {
                    email: {
                        email: true
                    },
                    idnoacquisto: {
                        required: function () {
                            return $('#acquistato').val() == '0';
                        }
                    },
                    idmodabito: {
                        required: function () {
                            return $('#idtipoabito').val() != '';
                        }
                    },
                    idtipopagcaparra: {
                        required: function () {
                            return $('#caparra').val() != '0.00' && $('#caparra').val() != '' && $('#caparra').val() != '0';
                        }
                    },
                    datac: {
                        required: function () {
                            return $('#caparra').val() != '0.00' && $('#caparra').val() != '' && $('#caparra').val() != '0';
                        }
                    },

                    idpag1: {
                        required: function () {
                            return $('#pag1').val() != '0.00' && $('#caparra').val() != '' && $('#caparra').val() != '0';
                        }
                    },
                    datap1: {
                        required: function () {
                            return $('#pag1').val() != '0.00' && $('#caparra').val() != '' && $('#caparra').val() != '0';
                        }
                    },
                    idpag2: {
                        required: function () {
                            return $('#pag2').val() != '0.00' && $('#caparra').val() != '' && $('#caparra').val() != '0';
                        }
                    },
                    datap2: {
                        required: function () {
                            return $('#pag2').val() != '0.00' && $('#caparra').val() != '' && $('#caparra').val() != '0';
                        }
                    },
                    idpag3: {
                        required: function () {
                            return $('#pag3').val() != '0.00' && $('#caparra').val() != '' && $('#caparra').val() != '0';
                        }
                    },
                    datap3: {
                        required: function () {
                            return $('#pag3').val() != '0.00' && $('#caparra').val() != '' && $('#caparra').val() != '0';
                        }
                    }
//                datas: {
//                    required: function () {
//                        return $('#totalespesa').val() != '0.00' && $('#totalespesa').val() != '' && $('#totalespesa').val() != '0';
//                    }
//                }
                },
                submitHandler: function () {
                    $("#submitformstep2cal").ready(function () {
                        var datastring = $("#formstep2cal *").not(".nopost").serialize();
                        $.ajax({
                            type: "POST",
                            url: "./mrgest.php",
                            data: datastring + "&submit=editformstep2calSartoria",
                            dataType: "json",
                            success: function (msg) {
                                if (msg.msg === "ko") {
                                    alert(msg.msgko);
                                } else {
                                    $('#messaggio').slideToggle('fast').delay(2000).slideToggle('slow');
                                    $('#messaggiobottom').slideToggle('fast').delay(2000).slideToggle('slow');
//                                setTimeout(function () {
//                                location.href = '/mrgest.php';
//                                }, 2000);
                                    setTimeout(function () {
                                        editstep2(id, 1, 0, 1);
                                    }, 2000);
                                }
                            }
                        });
                    });
                }
            });
        });
    } else if (solo_sartoria == 1) {
        $('.showcont').show().load('./form/form-step2calSart.php?stampe=' + stampe + '&id=' + id, function () {
            $.datepicker.setDefaults($.datepicker.regional[ "it" ]);
            $("#datap").datepicker({
                altFormat: "yy-mm-dd",
                altField: "#data"
            });
            $("#datac").datepicker({
                altFormat: "yy-mm-dd",
                altField: "#datacap"
            });
            /* data pag1 */
            $.datepicker.setDefaults($.datepicker.regional[ "it" ]);
            $("#datap1").datepicker({
                altFormat: "yy-mm-dd",
                altField: "#datapag1"
            });
            /* data pag2 */
            $.datepicker.setDefaults($.datepicker.regional[ "it" ]);
            $("#datap2").datepicker({
                altFormat: "yy-mm-dd",
                altField: "#datapag2"
            });
            /* data pag3 */
            $.datepicker.setDefaults($.datepicker.regional[ "it" ]);
            $("#datap3").datepicker({
                altFormat: "yy-mm-dd",
                altField: "#datapag3"
            });
            /* data saldo */
            $.datepicker.setDefaults($.datepicker.regional[ "it" ]);
            $("#datas").datepicker({
                altFormat: "yy-mm-dd",
                altField: "#datasaldo"
            });
            /* data saldo effettuato */
            $.datepicker.setDefaults($.datepicker.regional[ "it" ]);
            $("#dataes").datepicker({
                altFormat: "yy-mm-dd",
                altField: "#dataeffettuatosaldo"
            });
            /* datamatrimonio */
            $("#datamatrimoniop").datepicker({
                altFormat: "yy-mm-dd",
                altField: "#datamatrimonio"
            });
            /* data sartoria 1 */
            $("#datas1").datepicker({
                altFormat: "yy-mm-dd",
                altField: "#datasart1"
            });
            /* data sartoria 2 */
            $("#datas2").datepicker({
                altFormat: "yy-mm-dd",
                altField: "#datasart2"
            });
            /* data sartoria 3 */
            $("#datas3").datepicker({
                altFormat: "yy-mm-dd",
                altField: "#datasart3"
            });
            $('input.timepicker').timepicker({
                timeFormat: 'HH:mm',
                minTime: new Date(0, 0, 0, 6, 0, 0),
                maxTime: new Date(0, 0, 0, 20, 0, 0),
                interval: 30,
                dynamic: false,
                dropdown: true,
                scrollbar: true
            });
            //$('#mostrapdf').html("<a class=\"sizing\" href=\"./pdf/appuntamento.php?idapp=" + id + "\" target=\"new\"><i style=\"color: #fffffff;\" class=\"fa fa-file-pdf-o fa-lg\" aria-hidden=\"true\"></i> Stampa pdf</a>");
            $.ajax({
                type: "POST",
                url: "./mrgest.php",
                data: "id=" + id + "&submit=step2cal",
                dataType: "json",
                success: function (msg) {
                    var idappunt = id;
                    var idmodabito = "";

                    /* riempio i campi */
                    $.each(msg['valori'][0], function (index, value) {
                        $("#" + index).val(value);
                        if (index === "idmodabito") {
                            idmodabito = value;
                        }
                    });
                    if (msg['valori'][0]['provincia'].length > 0) {
                        selcomune(msg['valori'][0]['provincia'], msg['valori'][0]['comune']);
                    }
                    /* riempio i campi accessori */
                    if (msg['accessori']) {
                        for (var i = 0; i <= msg['accessori'].length; i++)
                            $.each(msg['accessori'][i], function (indexa, valuea) {
                                $("#" + indexa).val(valuea);
                            });
                    }

                    $('.box_sartoria').show();
                    /* riempio i campi sartoria */
                    checksartoria('1');


                    var tipoabito = $('#idtipoabito').val();
                    if (tipoabito > 0) {
                        selModabito(tipoabito, idmodabito);
                    }

                }
            });
            /**/
            $.validator.messages.required = '';
            $("#formstep2cal").validate({
                rules: {
                    email: {
                        email: true
                    },
                    idnoacquisto: {
                        required: function () {
                            return $('#acquistato').val() == '0';
                        }
                    },
                    idmodabito: {
                        required: function () {
                            return $('#idtipoabito').val() != '';
                        }
                    },
                    idtipopagcaparra: {
                        required: function () {
                            return $('#caparra').val() != '0.00' && $('#caparra').val() != '' && $('#caparra').val() != '0';
                        }
                    },
                    datac: {
                        required: function () {
                            return $('#caparra').val() != '0.00' && $('#caparra').val() != '' && $('#caparra').val() != '0';
                        }
                    },

                    idpag1: {
                        required: function () {
                            return $('#pag1').val() != '0.00' && $('#caparra').val() != '' && $('#caparra').val() != '0';
                        }
                    },
                    datap1: {
                        required: function () {
                            return $('#pag1').val() != '0.00' && $('#caparra').val() != '' && $('#caparra').val() != '0';
                        }
                    },
                    idpag2: {
                        required: function () {
                            return $('#pag2').val() != '0.00' && $('#caparra').val() != '' && $('#caparra').val() != '0';
                        }
                    },
                    datap2: {
                        required: function () {
                            return $('#pag2').val() != '0.00' && $('#caparra').val() != '' && $('#caparra').val() != '0';
                        }
                    },
                    idpag3: {
                        required: function () {
                            return $('#pag3').val() != '0.00' && $('#caparra').val() != '' && $('#caparra').val() != '0';
                        }
                    },
                    datap3: {
                        required: function () {
                            return $('#pag3').val() != '0.00' && $('#caparra').val() != '' && $('#caparra').val() != '0';
                        }
                    }
//                datas: {
//                    required: function () {
//                        return $('#totalespesa').val() != '0.00' && $('#totalespesa').val() != '' && $('#totalespesa').val() != '0';
//                    }
//                }
                },
                submitHandler: function () {
                    $("#submitformstep2cal").ready(function () {
                        var datastring = $("#formstep2cal *").not(".nopost").serialize();
                        $.ajax({
                            type: "POST",
                            url: "./mrgest.php",
                            data: datastring + "&submit=editformstep2cal",
                            dataType: "json",
                            success: function (msg) {
                                if (msg.msg === "ko") {
                                    alert(msg.msgko);
                                } else {
                                    $('#messaggio').slideToggle('fast').delay(2000).slideToggle('slow');
                                    $('#messaggiobottom').slideToggle('fast').delay(2000).slideToggle('slow');
//                                setTimeout(function () {
//                                location.href = '/mrgest.php';
//                                }, 2000);
                                    setTimeout(function () {
                                        editstep2(id, 0, 1, isSartoria);
                                    }, 2000);
                                }
                            }
                        });
                    });
                }
            });
        });
    } else {
        $('.showcont').show().load('./form/form-step2cal.php?stampe=' + stampe + '&id=' + id, function () {
            /* data caparra */
            $.datepicker.setDefaults($.datepicker.regional[ "it" ]);
            $("#datac").datepicker({
                altFormat: "yy-mm-dd",
                altField: "#datacap"
            });
            /* data pag1 */
            $.datepicker.setDefaults($.datepicker.regional[ "it" ]);
            $("#datap1").datepicker({
                altFormat: "yy-mm-dd",
                altField: "#datapag1"
            });
            /* data pag2 */
            $.datepicker.setDefaults($.datepicker.regional[ "it" ]);
            $("#datap2").datepicker({
                altFormat: "yy-mm-dd",
                altField: "#datapag2"
            });
            /* data pag3 */
            $.datepicker.setDefaults($.datepicker.regional[ "it" ]);
            $("#datap3").datepicker({
                altFormat: "yy-mm-dd",
                altField: "#datapag3"
            });
            /* data saldo */
            $.datepicker.setDefaults($.datepicker.regional[ "it" ]);
            $("#datas").datepicker({
                altFormat: "yy-mm-dd",
                altField: "#datasaldo"
            });
            /* data saldo effettuato */
            $.datepicker.setDefaults($.datepicker.regional[ "it" ]);
            $("#dataes").datepicker({
                altFormat: "yy-mm-dd",
                altField: "#dataeffettuatosaldo"
            });
            /* data */
            $.datepicker.setDefaults($.datepicker.regional[ "it" ]);
            $("#datap").datepicker({
                altFormat: "yy-mm-dd",
                altField: "#data"
            });
            /* datamatrimonio */
            $("#datamatrimoniop").datepicker({
                altFormat: "yy-mm-dd",
                altField: "#datamatrimonio"
            });
            /* data sartoria 1 */
            $("#datas1").datepicker({
                altFormat: "yy-mm-dd",
                altField: "#datasart1"
            });
            /* data sartoria 2 */
            $("#datas2").datepicker({
                altFormat: "yy-mm-dd",
                altField: "#datasart2"
            });
            /* data sartoria 3 */
            $("#datas3").datepicker({
                altFormat: "yy-mm-dd",
                altField: "#datasart3"
            });
            /*
             * 
             * 
             * 
             */

            $('input.timepicker').timepicker({
                timeFormat: 'HH:mm',
                minTime: new Date(0, 0, 0, 6, 0, 0),
                maxTime: new Date(0, 0, 0, 20, 0, 0),
                interval: 30,
                dynamic: false,
                dropdown: true,
                scrollbar: true
            });

            //$('#mostrapdf').html("<a class=\"sizing\" href=\"./pdf/appuntamento.php?idapp=" + id + "\" target=\"new\"><i style=\"color: #fffffff;\" class=\"fa fa-file-pdf-o fa-lg\" aria-hidden=\"true\"></i> Stampa pdf</a>");

            /*richiamadati*/
            $.ajax({
                type: "POST",
                url: "./mrgest.php",
                data: "id=" + id + "&submit=step2cal",
                dataType: "json",
                success: function (msg) {
                    var idappunt = id;

                    /* file */
                    var uploader2 = new qq.FileUploader({
                        element: document.getElementById('file-uploader2'),
                        action: "./js/fineuploader/upload.php?id=" + idappunt + "&tipo=file",
                        autoUpload: false,
                        uploadButtonText: '<img src="./immagini/sel_upload.png" /> Seleziona o trascina qui i files',
                        //debug: true,
                        multiple: true,
                        allowedExtensions: ['pdf', 'PDF', 'doc', 'DOC', 'docx', 'DOCX', 'jpg', 'JPG', 'jpeg', 'JPEG', 'png', 'PNG'],
                        //sizeLimit: 50000
                        'onComplete': function (id, fileName, responseJSON) {
                            if (responseJSON.success) {
                                $.ajax({
                                    type: "POST",
                                    url: "./mrgest.php",
                                    data: "id=" + idappunt + "&file=" + responseJSON.nomefile + "&submit=sendfile",
                                    dataType: "json",
                                    success: function (msgfile) {
                                        var ext = getFileExtension(responseJSON.nomefile).toLowerCase();
                                        var file = "<li class=\"sizing\" id=\"filecont_" + msgfile.id + "\">\n\
                                                    <a href=\"./appuntamenti/" + idappunt + "/" + responseJSON.nomefile + "\" target=\"new\"><div class=\"file_cnt\"><img src=\"./immagini/ext/" + ext + ".svg\" />" + responseJSON.nomefile + "\n\
                                                    </div></a>\n\
                                                    <div class=\"float_rgt\">\n\
                                                    <a href=\"javascript:;\" onclick=\"javascript:deleteFile('" + msgfile.id + "', '" + idappunt + "', 'Stai per eliminare il file, vuoi continuare?')\"><img src=\"./immagini/deleteimg.png\" /></a>\n\
                                                    </div>\n\
                                                    <div class=\"chiudi\"></div>\n\
                                                    </li>";
                                        $('#elencofiledx').append(file);
                                    }
                                });
                            } else {
                                return false;
                            }
                        }
                    });
                    $('#uploadfile2').click(function () {
                        uploader2.uploadStoredFiles();
                    });
                    var files = "";
                    for (var i = 0; i < msg['datifiles'].length; i++) {


                        var ext = getFileExtension(msg['datifiles'][i].nomefile).toLowerCase();
                        files += "<li class=\"sizing\" id=\"filecont_" + msg['datifiles'][i].id + "\">\n\
                                 <a href=\"./appuntamenti/" + idappunt + "/" + msg['datifiles'][i].nomefile + "\" target=\"new\"><div class=\"file_cnt\"><img src=\"./immagini/ext/" + ext + ".svg\" />" + msg['datifiles'][i].nomefile + "\n\
                                 </div></a>\n\
                                 <div class=\"float_rgt\">\n\
                                 <a href=\"javascript:;\" onclick=\"javascript:deleteFile('" + msg['datifiles'][i].id + "', '" + msg['datifiles'][i].idappuntamento + "', 'Stai per eliminare il file, vuoi continuare?')\"><img src=\"./immagini/deleteimg.png\" /></a>\n\
                                 </div>\n\
                                 <div class=\"chiudi\"></div>\n\
                                 </li>";
                    }
                    $('#elencofiledx').append(files);


                    if (msg.passato > 0) {
                        $('.app_passato').show();
                    }

                    var idmodabito = "";

                    /* riempio i campi */
                    $.each(msg['valori'][0], function (index, value) {
                        $("#" + index).val(value);
                        if (index === "idmodabito") {
                            idmodabito = value;
                        }
                    });
                    if (msg['valori'][0]['provincia'].length > 0) {
                        selcomune(msg['valori'][0]['provincia'], msg['valori'][0]['comune']);
                    }
                    /* riempio i campi accessori */
                    if (msg['accessori']) {
                        for (var i = 0; i <= msg['accessori'].length; i++)
                            $.each(msg['accessori'][i], function (indexa, valuea) {
                                $("#" + indexa).val(valuea);
                            });
                    }

                    /* mostro o meno sartoria */
                    if ($.inArray(atelier_corrente, arrAtelierSartoria) < 0) {
                        var sart = $('#sartoria').val();
                        if (sart === '1') {
                            $('.box_sartoria').show();
                            /* riempio i campi sartoria */
                            checksartoria('1');

                        } else if (sart === '0') {
                            $('.box_sartoria').hide();
                        }
                    }


                    var tipoabito = $('#idtipoabito').val();
                    if (tipoabito > 0) {
                        selModabito(tipoabito, idmodabito);
                    }

                }
            });
            /**/
            $.validator.messages.required = '';
            $("#formstep2cal").validate({
                rules: {
                    email: {
                        email: true
                    },
                    idnoacquisto: {
                        required: function () {
                            return $('#acquistato').val() == '0';
                        }
                    },
                    idmodabito: {
                        required: function () {
                            return $('#idtipoabito').val() != '';
                        }
                    },
                    idtipopagcaparra: {
                        required: function () {
                            return $('#caparra').val() != '0.00' && $('#caparra').val() != '' && $('#caparra').val() != '0';
                        }
                    },
                    datac: {
                        required: function () {
                            return $('#caparra').val() != '0.00' && $('#caparra').val() != '' && $('#caparra').val() != '0';
                        }
                    },

                    idpag1: {
                        required: function () {
                            return $('#pag1').val() != '0.00' && $('#caparra').val() != '' && $('#caparra').val() != '0';
                        }
                    },
                    datap1: {
                        required: function () {
                            return $('#pag1').val() != '0.00' && $('#caparra').val() != '' && $('#caparra').val() != '0';
                        }
                    },
                    idpag2: {
                        required: function () {
                            return $('#pag2').val() != '0.00' && $('#caparra').val() != '' && $('#caparra').val() != '0';
                        }
                    },
                    datap2: {
                        required: function () {
                            return $('#pag2').val() != '0.00' && $('#caparra').val() != '' && $('#caparra').val() != '0';
                        }
                    },
                    idpag3: {
                        required: function () {
                            return $('#pag3').val() != '0.00' && $('#caparra').val() != '' && $('#caparra').val() != '0';
                        }
                    },
                    datap3: {
                        required: function () {
                            return $('#pag3').val() != '0.00' && $('#caparra').val() != '' && $('#caparra').val() != '0';
                        }
                    }
//                datas: {
//                    required: function () {
//                        return $('#totalespesa').val() != '0.00' && $('#totalespesa').val() != '' && $('#totalespesa').val() != '0';
//                    }
//                }
                },
                submitHandler: function () {
                    $("#submitformstep2cal").ready(function () {
                        var datastring = $("#formstep2cal *").not(".nopost").serialize();
                        $.ajax({
                            type: "POST",
                            url: "./mrgest.php",
                            data: datastring + "&submit=editformstep2cal",
                            dataType: "json",
                            success: function (msg) {
                                if (msg.msg === "ko") {
                                    alert(msg.msgko);
                                } else {
                                    $('#messaggio').slideToggle('fast').delay(2000).slideToggle('slow');
                                    $('#messaggiobottom').slideToggle('fast').delay(2000).slideToggle('slow');
//                                setTimeout(function () {
//                                location.href = '/mrgest.php';
//                                }, 2000);
                                    setTimeout(function () {
                                        editstep2(id);
                                    }, 2000);
                                }
                            }
                        });
                    });
                }
            });
        });
    }

}

/* cancella file */

function deleteFile(id, idappuntamento, mess) {
    if (confirm(mess)) {
        $.ajax({
            type: "POST",
            url: "./mrgest.php",
            data: "id=" + id + "&idappuntamento=" + idappuntamento + "&submit=eliminafile",
            dataType: "json",
            success: function ()
            {
                $('#filecont_' + id).remove();
            }
        });
    }
}

function nosartoria(id) {
    if (id == "0") {
        $('#sartoria').val("0");
    } else {
        $('#sartoria').val("");
    }
}

function agendaDip() {
    var url = "./library/calendario-turni-dip.php";
    var url_dip = './library/calendario-turni-dip.php';
    var isEdit = false;
    var idutente = 0;
    var idatelier = 0;
    var settimana_dal_curr = null;
    var settimana_al_curr = null;
    var calendar = null;
    if ($('select#iddipendente').length > 0) {
        $('select#iddipendente').select2({
            placeholder: 'Inserisci il cognome del dipendente'
        });
        $('select#iddipendente').on('select2:select', function (e) {
            var data = e.params.data;
            //console.log($(data.element).data('atelier'));
            var atelier = $(data.element).data('atelier');
            idutente = data.id;
            //console.log(url);
            calendar.fullCalendar('removeEventSources');
            $('#calendarDipendenti').fullCalendar('removeEvents');
            url_dip = "./library/calendario-turni-dip.php?idutente=" + idutente;
            $('#calendarDipendenti').fullCalendar('addEventSource', url_dip);
            //$('#calendarDipendenti').fullCalendar('refetchEvents');
            if ($('select#idatelier').length > 0) {
                if (atelier != '') {
                    var arrAtelier = atelier.toString().split(',');
                    //console.log(arrAtelier);
                    $('select#idatelier option').each(function () {
                        var opt = $(this);
                        if (inArray(opt.prop('value'), arrAtelier)) {
                            opt.prop('disabled', false);
                        } else {
                            opt.prop('disabled', true);
                        }
                    });
                    $('select#idatelier').select2();
                }
            }
        });
        isEdit = true;
    }
    if ($('select#idatelier').length > 0) {
        $('select#idatelier').select2({
            placeholder: 'Inserisci l\'atelier'
        });
        $('select#idatelier').on('select2:select', function (e) {
            var data = e.params.data;
            //console.log(data);
            idatelier = data.id;
            //console.log(url);
            $('#calendarDipendenti').fullCalendar('removeEventSources');
            $('#calendarDipendenti').fullCalendar('removeEvents');
            url_dip = "./library/calendario-turni-dip.php?idatelier=" + idatelier;
            $('#calendarDipendenti').fullCalendar('addEventSource', url_dip);
            //$('#calendarDipendenti').fullCalendar('refetchEvents');
            if ($('select#iddipendente').length > 0) {
                $('select#iddipendente option').each(function () {
                    var opt = $(this);
                    if (opt.data('atelier')) {
                        var atelier = opt.data('atelier').toString();
                        if (atelier != '') {
                            var arrAtelier = atelier.split(',');
                            if (inArray(idatelier.toString(), arrAtelier)) {
                                opt.prop('disabled', false);
                                //console.log(idatelier);
                                //console.log(arrAtelier);
                            } else {
                                opt.prop('disabled', true);
                            }
                        } else {
                            opt.prop('disabled', true);
                        }
                    } else {
                        opt.prop('disabled', true);
                    }
                });
            }
            $.ajax({
                type: "POST",
                url: "./dipendenti.php",
                data: "idatelier=" + idatelier + "&submit=getDipendentiAtelier",
                dataType: "json",
                success: function (result) {
                    //console.log(result);
                    $('#html-dipendenti').html('');
                    $('#cnt-ore-dip').html('');
                    for (var i = 0; i < result.dipendenti.length; i++) {
                        $('#html-dipendenti').append('<option value="' + result.dipendenti[i].id + '">' + result.dipendenti[i].cognome + ' ' + result.dipendenti[i].nome + '</option>');
                        $('#cnt-ore-dip').append('<div style="margin:10px 0;">' + result.dipendenti[i].cognome + ' ' + result.dipendenti[i].nome + ': <span style="font-weight:bold;">' + (result.dipendenti[i].ore_settimana ? result.dipendenti[i].ore_settimana : '0') + ' </span> ORE SETTIMANA - <b>ORE INSERITE: </b><span style="font-weight: bold;" id="ore-' + result.dipendenti[i].id + '">0</span></div>');
                    }
                    getOreSettimana(idatelier, settimana_dal_curr, settimana_al_curr);
                }
            });
        });
        isEdit = true;
        $('#reset-filtri').unbind('click').click(function () {
            $('select#iddipendente').val(null).trigger('change');
            $('select#idatelier').val(null).trigger('change');
            idutente = 0;
            idatelier = 0;
            url_dip = './library/calendario-turni-dip.php';
            $('#calendarDipendenti').fullCalendar('removeEventSources');
            $('#calendarDipendenti').fullCalendar('removeEvents');
            $('#calendarDipendenti').fullCalendar('addEventSource', url_dip);
        });
    }
    if ($('#solo_richieste').length > 0) {
        $('#solo_richieste').unbind('click').click(function () {
            var btn = $(this);
            if (btn.is(':checked')) {
                $('#calendarDipendenti').fullCalendar('removeEventSources');
                $('#calendarDipendenti').fullCalendar('removeEvents');
                url_dip = "./library/calendario-turni-dip.php?solo_richieste=1";
                $('#calendarDipendenti').fullCalendar('addEventSource', url_dip);
                $.ajax({
                    type: "POST",
                    url: "./dipendenti.php",
                    data: "submit=tutte_richieste",
                    dataType: "json",
                    success: function (msg) {
                        $('#cnt-richieste').html(msg.html);
                        $('#cnt-richieste').css('height', '300px');
                        $('#btn-approva-all').unbind('click').click(function () {
                            if ($('input[name="idevento[]"]:checked').length > 0) {
                                var qrystring = 'attivo=' + $('#attivo_mod').val();
                                $('input[name="idevento[]"]:checked').each(function () {
                                    var opt = $(this);
                                    qrystring += '&idevento[]=' + opt.prop('value');
                                });
                                $.ajax({
                                    type: "POST",
                                    url: "./dipendenti.php",
                                    data: qrystring + "&submit=modAttivoEvento",
                                    dataType: "json",
                                    success: function (msg) {
                                        var attivo_mod = ($('#attivo_mod').val() == '1' ? 'Approvato' : 'Rifiutato');
                                        $('input[name="idevento[]"]:checked').each(function () {
                                            var opt = $(this);
                                            var idevento = opt.prop('value');
                                            $('#attivo_' + idevento).html(attivo_mod);
                                            opt.prop('checked', false);
                                        });
                                    }
                                });
                            } else {
                                alert('Devi selezionare almeno una riga.');
                                return false;
                            }
                        });
                        $('#btn-cerca-txt').unbind('click').click(function () {
                            var $q = $('#cerca_txt').val();
                            if ($q !== '') {
                                var re = new RegExp($q, 'gi');
                                $('.row-richieste').each(function () {
                                    var row = $(this);
                                    var targetHtml = row.find('td.col-richieste').html();
                                    if (re.test(targetHtml)) {
                                        row.show();
                                    } else {
                                        row.hide();
                                    }
                                });
                            } else {
                                $('.row-richieste').show();
                            }
                        });
                        $('#reset-cerca-txt').unbind('click').click(function () {
                            $('.row-richieste').show();
                        });
                        $("#cnt-richieste .editevent").unbind('click').click(function () {
                            var idevento = $(this).data('idevento');
                            //console.log(event);
                            $.ajax({
                                type: "POST",
                                url: "./dipendenti.php",
                                data: "id=" + idevento + "&submit=getEventoDip",
                                dataType: "json",
                                success: function (msg) {
                                    var evento = msg.dati[0];
                                    var contentHtml = '<select id="caldip_idatelier" class="input_moduli sizing float_moduli_35" style="border:1px solid silver">';
                                    for (var i = 0; i < msg.atelier.length; i++) {
                                        contentHtml += '<option value="' + msg.atelier[i].idatelier + '"' + (msg.atelier[i].idatelier == evento.idatelier ? ' selected' : '') + '>' + msg.atelier[i]['nome'] + '</option>';
                                    }
                                    contentHtml += '<input type="text" name="caldip_data_dal" id="caldip_data_dal" class="input_moduli sizing float_moduli_small_15" placeholder="Data dal" title="Data dal" style="border:1px solid silver" value="' + evento.data_cal + '" autocomplete="off" />' +
                                            //'<input type="text" name="caldip_data_al" id="caldip_data_al" class="input_moduli sizing float_moduli_small_15" placeholder="Data al" title="Data al" style="border:1px solid silver" />' +
                                            '<input type="text" name="caldip_ora_da" id="caldip_ora_da" class="input_moduli sizing float_moduli_small_15 timepicker4" placeholder="Ora da" title="Ora da" style="border:1px solid silver" value="' + (evento.ora_da ? evento.ora_da : '') + '" autocomplete="off" />' +
                                            '<input type="text" name="caldip_ora_a" id="caldip_ora_a" class="input_moduli sizing float_moduli_small_15 timepicker4" placeholder="Ora a" title="Ora a" style="border:1px solid silver" value="' + (evento.ora_a ? evento.ora_a : '') + '" autocomplete="off" /><div class="chiudi"></div>' +
                                            '<select id="evento_tipo" class="input_moduli sizing float_moduli_35" style="border:1px solid silver">';
                                    $('#html-richiesta option').each(function () {
                                        contentHtml += '<option value="' + $(this).prop('value') + '"' + ($(this).prop('value') == evento.tipo ? ' selected' : '') + '>' + $(this).text() + '</option>';
                                    });
                                    contentHtml += '</select><input type="checkbox" id="allday" name="allday" value="1"' + (evento.allday == 1 ? ' checked' : '') + ' /> Tutto il giorno<div class="chiudi"></div>' +
                                            '<textarea id="note" name="note" class="input_moduli sizing float_moduli_small_50" style="border:1px solid silver;height:100px;" placeholder="Note">' + evento.note + '</textarea>' +
                                            '<select id="attivo" name="attivo" class="input_moduli sizing float_moduli_35" style="border:1px solid silver">' +
                                            '<option value="1"' + (evento.attivo == 1 ? ' selected' : '') + '>Approvato</option>' +
                                            '<option value="0"' + (evento.attivo == 0 ? ' selected' : '') + '>Da approvare</option>' +
                                            '<option value="2"' + (evento.attivo == 2 ? ' selected' : '') + '>Rifiutato</option>' +
                                            '</select>' +
                                            '<style>' +
                                            '.ui-timepicker-container{' +
                                            'z-index:9999999999 !important;' +
                                            '}' +
                                            '</style>';
                                    $.confirm({
                                        title: 'MODIFICA RICHIESTA',
                                        content: contentHtml,
                                        boxWidth: '50%',
                                        useBootstrap: false,
                                        onContentReady: function () {
                                            $("#data_cal").datepicker();
                                            $('input.timepicker4').timepicker({
                                                timeFormat: 'HH:mm',
                                                minTime: new Date(0, 0, 0, 8, 00, 0),
                                                maxTime: new Date(0, 0, 0, 21, 0, 0),
                                                interval: 30,
                                                dynamic: false,
                                                scrollbar: true
                                            });
                                        },
                                        buttons: {
                                            confirm: {
                                                text: 'SALVA',
                                                btnClass: 'btn-green',
                                                keys: ['enter', 'shift'],
                                                action: function () {
                                                    var data_cal_dal = encodeURIComponent($('#caldip_data_dal').val());
                                                    //var data_cal_al = encodeURIComponent($('#caldip_data_al').val());
                                                    var ora_da = encodeURIComponent($('#caldip_ora_da').val());
                                                    var ora_a = encodeURIComponent($('#caldip_ora_a').val());
                                                    var evento_tipo = encodeURIComponent($('#evento_tipo').val());
                                                    var idatelier = encodeURIComponent($('#caldip_idatelier').val());
                                                    var allday = 0;
                                                    if ($('#allday').is(':checked')) {
                                                        allday = 1;
                                                    }
                                                    var note = encodeURIComponent($('#note').val());
                                                    var attivo = encodeURIComponent($('#attivo').val());
                                                    switch (evento_tipo) {
                                                        case '':

                                                            break;
                                                    }
                                                    if (data_cal_dal != "" && evento_tipo != "") {
                                                        $.ajax({
                                                            type: "POST",
                                                            url: "./dipendenti.php",
                                                            data: "idatelier=&data_cal_dal=" + data_cal_dal +
                                                                    //"&data_cal_al=" + data_cal_al + 
                                                                    "&ora_da=" + ora_da + "&ora_a=" + ora_a +
                                                                    "&allday=" + allday + "&note=" + note +
                                                                    "&evento_tipo=" + evento_tipo +
                                                                    "&attivo=" + attivo +
                                                                    "&id=" + evento.id +
                                                                    "&idatelier=" + idatelier +
                                                                    "&submit=aggiornaEventoDip",
                                                            dataType: "json",
                                                            success: function (msg) {
                                                                $('#calendarDipendenti').fullCalendar('refetchEvents');

                                                            }
                                                        });
                                                    } else {
                                                        return false;
                                                    }
                                                }
                                            },
                                            cancel: {
                                                text: 'ANNULLA',
                                                action: function () {

                                                }
                                            }
                                        }
                                    });
                                }
                            });
                        });
                        $("#cnt-richieste .deleteevent").unbind('click').click(function () {
                            var idevento = $(this).data('idevento');
                            $.confirm({
                                title: 'ATTENZIONE!',
                                content: 'Stai per eliminare il dato, CONFERMI?',
                                boxWidth: '30%',
                                useBootstrap: false,
                                buttons: {
                                    confirm: {
                                        text: 'SI',
                                        btnClass: 'btn-blue',
                                        keys: ['enter', 'shift'],
                                        action: function () {
                                            $('#calendarDipendenti').fullCalendar('removeEvents', idevento);
                                            $.ajax({
                                                type: "POST",
                                                url: "./dipendenti.php",
                                                data: "id=" + idevento + "&submit=eliminaEventoDip",
                                                dataType: "json",
                                                success: function (msg) {
                                                    $('#calendarDipendenti').fullCalendar('refetchEvents');
                                                }
                                            });
                                        }
                                    },
                                    cancel: {
                                        text: 'NO'
                                    }
                                }
                            });

                        });
                    }
                });
            } else {
                $('#calendarDipendenti').fullCalendar('removeEventSources');
                $('#calendarDipendenti').fullCalendar('removeEvents');
                $('#cnt-richieste').html('');
                $('#cnt-richieste').css('height', '0px');
            }
        });
    }
    if (screen.width > "768") {
        //view = "twoWeek";
        view = "agendaWeek";
        boxwidth = "50%";
    } else {
        view = "basicDay";
        boxwidth = "80%";
    }
    calendar = $('#calendarDipendenti').fullCalendar({
        theme: true,
        header: {
            left: 'prev,next today',
            center: 'title',
            right: 'month agendaWeek'
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
        height: 750,
        disableDragging: true,
        selectable: true,
        selectHelper: true,
        timeFormat: 'HH:mm',
        displayEventEnd: true,
        defaultView: view,
        minTime: '08:00:00',
        maxTime: '24:00:00',
        events: url,
        allDaySlot: true,
        slotDuration: '00:15:00',
        slotMinutes: 15,
        slotEventOverlap: false,
        viewRender: function (view, element) {
            var giorno_dal = view.start._d.getDate();
            var mese_dal = view.start._d.getMonth() + 1;
            if (mese_dal < 10) {
                mese_dal = pad(mese_dal, 2);
            }
            if (giorno_dal < 10) {
                giorno_dal = pad(giorno_dal, 2);
            }
            var settimana_dal = view.start._d.getFullYear() + '-' + mese_dal.toString() + '-' + giorno_dal;
            var giorno_al = view.end._d.getDate();
            if (giorno_al < 10) {
                giorno_al = pad(giorno_al, 2);
            }
            var mese_al = view.end._d.getMonth() + 1;
            if (mese_al < 10) {
                mese_al = pad(mese_al, 2);
            }
            var settimana_al = view.end._d.getFullYear() + '-' + mese_al.toString() + '-' + giorno_al;
            settimana_dal_curr = settimana_dal;
            settimana_al_curr = settimana_al;
            getOreSettimana(idatelier, settimana_dal, settimana_al);
            //console.log(settimana_dal + ' ' + settimana_al);
        },
        eventRender: function (event, element) {
            if (event.richiesta_elimina) {
                element.prepend("<span class='richiesta-deleteevent-dip' style='color: #990000; float: right; padding-left: 3px;position:relative;z-index:99;'><i class=\"fa fa-times-circle fa-lg\" aria-hidden=\"true\"></i></span>");
                element.find(".richiesta-deleteevent-dip").click(function () {
                    $.confirm({
                        title: 'ATTENZIONE!',
                        content: 'Stai per inviare la richiesta eliminare il dato, CONFERMI?',
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
                                        url: "./dipendenti.php",
                                        data: "id=" + event.id + "&submit=eliminaEventoDipRichiesta",
                                        dataType: "json",
                                        success: function (msg) {
                                            alert('Richiesta inviata con successo!');
                                            return false;
                                        }
                                    });
                                }
                            },
                            cancel: {
                                text: 'NO'
                            }
                        }
                    });
                });
            }
            if ((isEdit || event.elimina) && event.evento == 1) {
                element.prepend("<span class='deleteevent' style='color: #990000; float: right; padding-left: 3px;position:relative;z-index:99;cursor:pointer;'><i class=\"fa fa-times-circle fa-lg\" aria-hidden=\"true\"></i></span>");
                if (isEdit) {
                    element.prepend("<span class='editevent' style='color: #000000; float: right;position:relative;z-index:99;cursor:pointer;'><i class=\"fa fa-pencil-square-o fa-lg\" aria-hidden=\"true\"></i></span>");
                }
                element.find(".editevent").click(function () {
                    //console.log(event);
                    $.ajax({
                        type: "POST",
                        url: "./dipendenti.php",
                        data: "id=" + event.id + "&submit=getEventoDip",
                        dataType: "json",
                        success: function (msg) {
                            var evento = msg.dati[0];
                            var contentHtml = '<select id="caldip_idatelier" class="input_moduli sizing float_moduli_35" style="border:1px solid silver">';
                            for (var i = 0; i < msg.atelier.length; i++) {
                                contentHtml += '<option value="' + msg.atelier[i].idatelier + '"' + (msg.atelier[i].idatelier == evento.idatelier ? ' selected' : '') + '>' + msg.atelier[i]['nome'] + '</option>';
                            }
                            contentHtml += '<input type="text" name="caldip_data_dal" id="caldip_data_dal" class="input_moduli sizing float_moduli_small_15" placeholder="Data dal" title="Data dal" style="border:1px solid silver" value="' + evento.data_cal + '" autocomplete="off" />' +
                                    //'<input type="text" name="caldip_data_al" id="caldip_data_al" class="input_moduli sizing float_moduli_small_15" placeholder="Data al" title="Data al" style="border:1px solid silver" />' +
                                    '<input type="text" name="caldip_ora_da" id="caldip_ora_da" class="input_moduli sizing float_moduli_small_15 timepicker4" placeholder="Ora da" title="Ora da" style="border:1px solid silver" value="' + (evento.ora_da ? evento.ora_da : '') + '" autocomplete="off" />' +
                                    '<input type="text" name="caldip_ora_a" id="caldip_ora_a" class="input_moduli sizing float_moduli_small_15 timepicker4" placeholder="Ora a" title="Ora a" style="border:1px solid silver" value="' + (evento.ora_a ? evento.ora_a : '') + '" autocomplete="off" /><div class="chiudi"></div>' +
                                    '<select id="evento_tipo" class="input_moduli sizing float_moduli_35" style="border:1px solid silver">';
                            $('#html-richiesta option').each(function () {
                                contentHtml += '<option value="' + $(this).prop('value') + '"' + ($(this).prop('value') == evento.tipo ? ' selected' : '') + '>' + $(this).text() + '</option>';
                            });
                            contentHtml += '</select><input type="checkbox" id="allday" name="allday" value="1"' + (evento.allday == 1 ? ' checked' : '') + ' /> Tutto il giorno<div class="chiudi"></div>' +
                                    '<textarea id="note" name="note" class="input_moduli sizing float_moduli_small_50" style="border:1px solid silver;height:100px;" placeholder="Note">' + evento.note + '</textarea>' +
                                    '<select id="attivo" name="attivo" class="input_moduli sizing float_moduli_35" style="border:1px solid silver">' +
                                    '<option value="1"' + (evento.attivo == 1 ? ' selected' : '') + '>Approvato</option>' +
                                    '<option value="0"' + (evento.attivo == 0 ? ' selected' : '') + '>Da approvare</option>' +
                                    '<option value="2"' + (evento.attivo == 2 ? ' selected' : '') + '>Rifiutato</option>' +
                                    '</select>' +
                                    '<style>' +
                                    '.ui-timepicker-container{' +
                                    'z-index:9999999999 !important;' +
                                    '}' +
                                    '</style>';
                            $.confirm({
                                title: 'MODIFICA RICHIESTA',
                                content: contentHtml,
                                boxWidth: '50%',
                                useBootstrap: false,
                                onContentReady: function () {
                                    $("#data_cal").datepicker();
                                    $('input.timepicker4').timepicker({
                                        timeFormat: 'HH:mm',
                                        minTime: new Date(0, 0, 0, 8, 00, 0),
                                        maxTime: new Date(0, 0, 0, 21, 0, 0),
                                        interval: 30,
                                        dynamic: false,
                                        scrollbar: true
                                    });
                                },
                                buttons: {
                                    confirm: {
                                        text: 'SALVA',
                                        btnClass: 'btn-green',
                                        keys: ['enter', 'shift'],
                                        action: function () {
                                            var data_cal_dal = encodeURIComponent($('#caldip_data_dal').val());
                                            //var data_cal_al = encodeURIComponent($('#caldip_data_al').val());
                                            var ora_da = encodeURIComponent($('#caldip_ora_da').val());
                                            var ora_a = encodeURIComponent($('#caldip_ora_a').val());
                                            var evento_tipo = encodeURIComponent($('#evento_tipo').val());
                                            var idatelier = encodeURIComponent($('#caldip_idatelier').val());
                                            var allday = 0;
                                            if ($('#allday').is(':checked')) {
                                                allday = 1;
                                            }
                                            var note = encodeURIComponent($('#note').val());
                                            var attivo = encodeURIComponent($('#attivo').val());
                                            switch (evento_tipo) {
                                                case '':

                                                    break;
                                            }
                                            if (data_cal_dal != "" && evento_tipo != "") {
                                                $.ajax({
                                                    type: "POST",
                                                    url: "./dipendenti.php",
                                                    data: "idatelier=&data_cal_dal=" + data_cal_dal +
                                                            //"&data_cal_al=" + data_cal_al + 
                                                            "&ora_da=" + ora_da + "&ora_a=" + ora_a +
                                                            "&allday=" + allday + "&note=" + note +
                                                            "&evento_tipo=" + evento_tipo +
                                                            "&attivo=" + attivo +
                                                            "&id=" + evento.id +
                                                            "&idatelier=" + idatelier +
                                                            "&submit=aggiornaEventoDip",
                                                    dataType: "json",
                                                    success: function (msg) {
                                                        $('#calendarDipendenti').fullCalendar('refetchEvents');
                                                        getOreSettimana(idatelier, settimana_dal_curr, settimana_al_curr);
                                                    }
                                                });
                                            } else {
                                                return false;
                                            }
                                        }
                                    },
                                    cancel: {
                                        text: 'ANNULLA',
                                        action: function () {

                                        }
                                    }
                                }
                            });
                        }
                    });
                });
                element.find(".deleteevent").click(function () {

                    $.confirm({
                        title: 'ATTENZIONE!',
                        content: 'Stai per eliminare il dato, CONFERMI?',
                        boxWidth: '30%',
                        useBootstrap: false,
                        buttons: {
                            confirm: {
                                text: 'SI',
                                btnClass: 'btn-blue',
                                keys: ['enter', 'shift'],
                                action: function () {
                                    $('#calendarDipendenti').fullCalendar('removeEvents', event.id);
                                    $.ajax({
                                        type: "POST",
                                        url: "./dipendenti.php",
                                        data: "id=" + event.id + "&submit=eliminaEventoDip",
                                        dataType: "json",
                                        success: function (msg) {
                                            $('#calendarDipendenti').fullCalendar('refetchEvents');
                                            getOreSettimana(idatelier, settimana_dal_curr, settimana_al_curr);
                                        }
                                    });
                                }
                            },
                            cancel: {
                                text: 'NO'
                            }
                        }
                    });

                });
            }
            // solo admin
            if (event.edit == 1 && event.evento == 0) {
                element.prepend("<span class='deleteevent-dip' style='color: #990000; float: right; padding-left: 3px;position:relative;z-index:99;cursor:pointer;'><i class=\"fa fa-times-circle fa-lg\" aria-hidden=\"true\"></i></span>");
                if (event.edit == 1) {
                    element.prepend("<span class='editevent-dip' style='color: #000000; float: right;position:relative;z-index:99;cursor:pointer;'><i class=\"fa fa-pencil-square-o fa-lg\" aria-hidden=\"true\"></i></span>");
                }

            }
            element.find(".editevent-dip").click(function () {
                //console.log(event);
                $.ajax({
                    type: "POST",
                    url: "./dipendenti.php",
                    data: "id=" + event.id + "&submit=getTurnoDip",
                    dataType: "json",
                    success: function (msg) {
                        var contentHtml = '<style>.ui-timepicker-container{ z-index:9999999999 !important;}</style>' +
                                '<input type="text" name="data_cal" value="' + msg.dati[0].data_cal + '" id="data_cal" class="input_moduli sizing float_moduli_small_15" placeholder="Data" title="Data" style="border:1px solid silver" />' +
                                '<input type="text" name="ora_da" id="ora_da" value="' + msg.dati[0].ora_da + '" class="input_moduli sizing float_moduli_small_15 timepicker4" placeholder="Ora da" title="Ora da" style="border:1px solid silver" />' +
                                '<input type="text" name="ora_a" id="ora_a" value="' + msg.dati[0].ora_a + '" class="input_moduli sizing float_moduli_small_15 timepicker4" placeholder="Ora a" title="Ora a" style="border:1px solid silver" />' +
                                '<select name="caldip_idutente" id="caldip_idutente" class="input_moduli sizing float_moduli_35" style="border:1px solid silver">';
                        $('#html-dipendenti option').each(function () {
                            contentHtml += '<option value="' + $(this).prop('value') + '">' + $(this).text() + '</option>';
                        });
                        contentHtml += '</select>';
                        $.confirm({
                            title: 'MODIFICA TURNO A CALENDARIO',
                            content: contentHtml,
                            boxWidth: '50%',
                            useBootstrap: false,
                            onContentReady: function () {
                                $("#data_cal").datepicker();
                                $('input.timepicker4').timepicker({
                                    timeFormat: 'HH:mm',
                                    minTime: new Date(0, 0, 0, 8, 00, 0),
                                    maxTime: new Date(0, 0, 0, 21, 0, 0),
                                    interval: 30,
                                    dynamic: false,
                                    scrollbar: true
                                });
                                $('#caldip_idutente').val(msg.dati[0].idutente);
                            },
                            buttons: {
                                confirm: {
                                    text: 'SALVA',
                                    btnClass: 'btn-green',
                                    keys: ['enter', 'shift'],
                                    action: function () {
                                        var data_cal = encodeURIComponent($('#data_cal').val());
                                        var ora_da = encodeURIComponent($('#ora_da').val());
                                        var ora_a = encodeURIComponent($('#ora_a').val());
                                        var idutente = encodeURIComponent($('#caldip_idutente').val());
                                        if (data_cal != "" && ora_da != "" && ora_a != "" && idutente != "") {
                                            $.ajax({
                                                type: "POST",
                                                url: "./dipendenti.php",
                                                data: "idatelier=<?= $_GET['id'] ?>&data_cal=" + data_cal + "&ora_da=" + ora_da + "&ora_a=" + ora_a + "&idutente=" + idutente +
                                                        "&id=" + event.id + "&submit=aggiornaTurnoDip",
                                                dataType: "json",
                                                success: function (msg) {
                                                    $('#calendarDipendenti').fullCalendar('refetchEvents');
                                                    getOreSettimana(idatelier, settimana_dal_curr, settimana_al_curr);
                                                }
                                            });
                                        } else {
                                            return false;
                                        }
                                    }
                                },
                                cancel: {
                                    text: 'ANNULLA',
                                    action: function () {

                                    }
                                }
                            }
                        });
                    }
                });
            });
            element.find(".deleteevent-dip").click(function () {

                $.confirm({
                    title: 'ATTENZIONE!',
                    content: 'Stai per eliminare il dato, CONFERMI?',
                    boxWidth: '30%',
                    useBootstrap: false,
                    buttons: {
                        confirm: {
                            text: 'SI',
                            btnClass: 'btn-blue',
                            keys: ['enter', 'shift'],
                            action: function () {
                                $('#calendarDipendenti').fullCalendar('removeEvents', event.id);
                                $.ajax({
                                    type: "POST",
                                    url: "./dipendenti.php",
                                    data: "id=" + event.id + "&submit=eliminaTurnoDip",
                                    dataType: "json",
                                    success: function (msg) {
                                        $('#calendarDipendenti').fullCalendar('refetchEvents');
                                        getOreSettimana(idatelier, settimana_dal_curr, settimana_al_curr);
                                    }
                                });
                            }
                        },
                        cancel: {
                            text: 'NO'
                        }
                    }
                });
            });
        }
    });
    $('.addRichiestaDip').unbind('click').click(function () {
        var contentHtml = '<div id="cnt-richiesta-dip">';
        if ($('#html-dipendenti').length > 0) {
            contentHtml += '<select id="caldip_iddipendente" class="input_moduli sizing float_moduli_35" style="border:1px solid silver">' +
                    '<option value="">Seleziona dipendente</option>';
            $('#html-dipendenti option').each(function () {
                contentHtml += '<option value="' + $(this).prop('value') + '" data-atelier="' + $(this).data('atelier') + '">' + $(this).text() + '</option>';
            });
            contentHtml += '</select>';
        }
        contentHtml += '<select id="caldip_idatelier" class="input_moduli sizing float_moduli_35" style="border:1px solid silver">';
        contentHtml += '<option value="0">Seleziona atelier</option>';
        $('#html-atelier option').each(function () {
            contentHtml += '<option value="' + $(this).prop('value') + '">' + $(this).text() + '</option>';
        });
        contentHtml += '</select>' +
                '<input type="text" name="caldip_data_dal" id="caldip_data_dal" class="input_moduli sizing float_moduli_small_15" placeholder="Data dal" title="Data dal" style="border:1px solid silver" autocomplete="off" />' +
                '<input type="text" name="caldip_data_al" id="caldip_data_al" class="input_moduli sizing float_moduli_small_15" placeholder="Data al" title="Data al" style="border:1px solid silver" autocomplete="off" />' +
                '<input type="text" name="caldip_ora_da" id="caldip_ora_da" class="input_moduli sizing float_moduli_small_15 timepicker4" placeholder="Ora da" title="Ora da" style="border:1px solid silver" autocomplete="off" />' +
                '<input type="text" name="caldip_ora_a" id="caldip_ora_a" class="input_moduli sizing float_moduli_small_15 timepicker4" placeholder="Ora a" title="Ora a" style="border:1px solid silver" autocomplete="off" /><div class="chiudi"></div>' +
                '<select id="evento_tipo" class="input_moduli sizing float_moduli_35" style="border:1px solid silver">';
        $('#html-richiesta option').each(function () {
            contentHtml += '<option value="' + $(this).prop('value') + '">' + $(this).text() + '</option>';
        });
        contentHtml += '</select><input type="checkbox" id="allday" name="allday" value="1" /> Tutto il giorno<div class="chiudi"></div>' +
                '<textarea id="note" name="note" class="input_moduli sizing float_moduli_small_50" style="border:1px solid silver;height:100px;" placeholder="Note"></textarea>' +
                '<style>' +
                '.ui-timepicker-container{' +
                'z-index:9999999999 !important;' +
                '}' +
                '</style></div>';
        var isAdmin = false; 
        $.confirm({
            title: 'NUOVA RICHIESTA',
            content: contentHtml,
            boxWidth: '50%',
            useBootstrap: false,
            onContentReady: function () {
                $("#caldip_data_dal,#caldip_data_al").datepicker();
                $('input.timepicker4').timepicker({
                    timeFormat: 'HH:mm',
                    minTime: new Date(0, 0, 0, 8, 00, 0),
                    maxTime: new Date(0, 0, 0, 21, 0, 0),
                    interval: 30,
                    dynamic: false,
                    scrollbar: true
                });               
                if ($('#cnt-richiesta-dip select#caldip_iddipendente').length > 0) {
                    isAdmin = true;
                    $('#cnt-richiesta-dip select#caldip_iddipendente').unbind('change').change(function () {
                        if ($('#cnt-richiesta-dip select#caldip_iddipendente option:selected').length > 0) {
                            var atelier = $('#cnt-richiesta-dip select#caldip_iddipendente option:selected').data('atelier');
                            if (atelier != '' && atelier) {
                                var arrAtelier = atelier.toString().split(',');
                                $('#cnt-richiesta-dip #caldip_idatelier option').each(function () {
                                    var opt = $(this);
                                    var opt_value = parseInt($(this).prop('value'));
                                    if (opt_value > 0) {
                                        if (inArray(opt_value, arrAtelier)) {
                                            opt.show();
                                        } else {
                                            opt.hide();
                                        }
                                    }
                                });
                            } else if ($('#cnt-richiesta-dip select#caldip_idatelier').length > 0) {
                                if ($('#cnt-richiesta-dip select#caldip_idatelier option:selected').length > 0) {
                                    var opt_atelier = $('#cnt-richiesta-dip select#idatelier option:selected').prop('value');
                                    if (opt_atelier != '') {
                                        $('#cnt-richiesta-dip #caldip_idatelier').val(opt_atelier);
                                    }
                                }
                            }
                        }
                    });
                }
            },
            buttons: {
                confirm: {
                    text: 'SALVA',
                    btnClass: 'btn-green',
                    keys: ['enter', 'shift'],
                    action: function () {
                        var data_cal_dal = encodeURIComponent($('#caldip_data_dal').val());
                        var data_cal_al = encodeURIComponent($('#caldip_data_al').val());
                        var ora_da = encodeURIComponent($('#caldip_ora_da').val());
                        var ora_a = encodeURIComponent($('#caldip_ora_a').val());
                        var evento_tipo = encodeURIComponent($('#evento_tipo').val());
                        var idatelier = encodeURIComponent($('#caldip_idatelier').val());
                        var allday = 0;
                        if ($('#allday').is(':checked')) {
                            allday = 1;
                        }
                        var note = encodeURIComponent($('#note').val());
                        switch (evento_tipo) {
                            case '':

                                break;
                        }
                        if (data_cal_dal != "" && evento_tipo != "") {
                            $.ajax({
                                type: "POST",
                                url: "./dipendenti.php",
                                data: "idatelier=&data_cal_dal=" + data_cal_dal + "&data_cal_al=" + data_cal_al + "&ora_da=" + ora_da + "&ora_a=" + ora_a +
                                        "&allday=" + allday + "&note=" + note +
                                        (isEdit ? "&idutente=" + idutente : "") +
                                        (isAdmin ? "&idutente=" +  $('select#caldip_iddipendente').val() : "") +
                                        "&idatelier=" + idatelier +
                                        "&evento_tipo=" + evento_tipo + "&submit=inserisciEventoDip",
                                dataType: "json",
                                success: function (msg) {
                                    $('#calendarDipendenti').fullCalendar('refetchEvents');

                                }
                            });
                        } else {
                            alert('Devi selezionare il tipo e la data.');
                            return false;
                        }
                    }
                },
                cancel: {
                    text: 'ANNULLA',
                    action: function () {

                    }
                }
            }
        });
    });
    if ($('.addTurnoDip').length > 0) {
        $('.addTurnoDip').unbind('click').click(function () {
            var contentHtml = '<select id="caldip_idatelier" class="input_moduli sizing float_moduli_35" style="border:1px solid silver">';
            contentHtml += '<option value="0">Seleziona atelier</option>';
            $('#html-atelier option').each(function () {
                contentHtml += '<option value="' + $(this).prop('value') + '">' + $(this).text() + '</option>';
            });
            contentHtml += '<style>.ui-timepicker-container{ z-index:9999999999 !important;}</style>' +
                    '<input type="text" name="caldip_data_dal" id="caldip_data_dal" class="input_moduli sizing float_moduli_small_15" placeholder="Data dal" title="Data dal" style="border:1px solid silver" />' +
                    '<input type="text" name="caldip_data_al" id="caldip_data_al" class="input_moduli sizing float_moduli_small_15" placeholder="Data al" title="Data al" style="border:1px solid silver" />' +
                    '<input type="text" name="caldip_ora_da" id="caldip_ora_da" class="input_moduli sizing float_moduli_small_15 timepicker4" placeholder="Ora da" title="Ora da" style="border:1px solid silver" />' +
                    '<input type="text" name="caldip_ora_a" id="caldip_ora_a" class="input_moduli sizing float_moduli_small_15 timepicker4" placeholder="Ora a" title="Ora a" style="border:1px solid silver" />' +
                    '<select name="caldip_idutente" id="caldip_idutente" class="input_moduli sizing float_moduli_35" style="border:1px solid silver">';
            $('#html-dipendenti option').each(function () {
                contentHtml += '<option value="' + $(this).prop('value') + '"' + (idutente == $(this).prop('value') ? ' selected' : '') + '>' + $(this).text() + '</option>';
            });
            contentHtml += '</select>';
            $.confirm({
                title: 'INSERISCI TURNO A CALENDARIO',
                content: contentHtml,
                boxWidth: '50%',
                useBootstrap: false,
                onContentReady: function () {
                    //console.log('ok');
                    $("#caldip_data_dal,#caldip_data_al").datepicker();
                    $('input.timepicker4').timepicker({
                        timeFormat: 'HH:mm',
                        minTime: new Date(0, 0, 0, 8, 00, 0),
                        maxTime: new Date(0, 0, 0, 21, 0, 0),
                        interval: 30,
                        dynamic: false,
                        scrollbar: true
                    });
                },
                buttons: {
                    confirm: {
                        text: 'SALVA',
                        btnClass: 'btn-green',
                        keys: ['enter', 'shift'],
                        action: function () {
                            var data_cal_dal = encodeURIComponent($('#caldip_data_dal').val());
                            var data_cal_al = encodeURIComponent($('#caldip_data_al').val());
                            var ora_da = encodeURIComponent($('#caldip_ora_da').val());
                            var ora_a = encodeURIComponent($('#caldip_ora_a').val());
                            var idutente = encodeURIComponent($('#caldip_idutente').val());
                            var idatelier = encodeURIComponent($('#caldip_idatelier').val());
                            if (data_cal_dal != "" && ora_da != "" && ora_a != "" && idutente != "") {
                                $.ajax({
                                    type: "POST",
                                    url: "./dipendenti.php",
                                    data: "idatelier=" + idatelier + "&data_cal_dal=" + data_cal_dal + "&data_cal_al=" + data_cal_al + "&ora_da=" + ora_da + "&ora_a=" + ora_a + "&idutente=" + idutente + "&submit=inserisciTurnoDip",
                                    dataType: "json",
                                    success: function (msg) {
                                        $('#calendarDipendenti').fullCalendar('refetchEvents');
                                        //getOreSettimana(idatelier, settimana_dal_curr, settimana_al_curr);
                                    }
                                });
                            } else
                            {
                                return false;
                            }

                        }
                    },
                    cancel: {
                        text: 'ANNULLA',
                        action: function () {

                        }
                    }
                }
            });
        });
    }
}

function agendaDipAtelier() {
    var url = "./library/calendario-turni-atelier.php";
    var url_dip = './library/calendario-turni-atelier.php';
    var idutente = 0;
    var idatelier = 0;
    var settimana_dal_curr = null;
    var settimana_al_curr = null;
    if ($('select#iddipendente').length > 0) {
        $('select#iddipendente').select2({
            placeholder: 'Inserisci il cognome del dipendente'
        });
        $('select#iddipendente').on('select2:select', function (e) {
            var data = e.params.data;
            //console.log(data);
            idutente = data.id;
            //console.log(url);
            $('#calendarDipendenti').fullCalendar('removeEventSources');
            $('#calendarDipendenti').fullCalendar('removeEvents');
            url_dip = "./library/calendario-turni-atelier.php?idutente=" + idutente;
            $('#calendarDipendenti').fullCalendar('addEventSource', url_dip);
            //$('#calendarDipendenti').fullCalendar('refetchEvents');
        });
    }
    if ($('select#idatelier').length > 0) {
        $('select#idatelier').select2({
            placeholder: 'Inserisci l\'atelier'
        });
        $('select#idatelier').on('select2:select', function (e) {
            var data = e.params.data;
            //console.log(data);
            idatelier = data.id;
            //console.log(url);
            $('#calendarDipendenti').fullCalendar('removeEventSources');
            $('#calendarDipendenti').fullCalendar('removeEvents');
            url_dip = "./library/calendario-turni-atelier.php?idatelier=" + idatelier;
            $('#calendarDipendenti').fullCalendar('addEventSource', url_dip);
            $.ajax({
                type: "POST",
                url: "./dipendenti.php",
                data: "idatelier=" + idatelier + "&submit=getDipendentiAtelier",
                dataType: "json",
                success: function (result) {
                    //console.log(result);
                    $('#html-dipendenti').html('');
                    $('#cnt-ore-dip').html('');
                    for (var i = 0; i < result.dipendenti.length; i++) {
                        $('#html-dipendenti').append('<option value="' + result.dipendenti[i].id + '">' + result.dipendenti[i].cognome + ' ' + result.dipendenti[i].nome + '</option>');
                        $('#cnt-ore-dip').append('<div style="margin:10px 0;">' + result.dipendenti[i].cognome + ' ' + result.dipendenti[i].nome + ': <span style="font-weight:bold;">' + (result.dipendenti[i].ore_settimana ? result.dipendenti[i].ore_settimana : '0') + ' </span> ORE SETTIMANA - <b>ORE INSERITE: </b><span style="font-weight: bold;" id="ore-' + result.dipendenti[i].id + '">0</span></div>');
                    }
                    getOreSettimana(idatelier, settimana_dal_curr, settimana_al_curr);
                }
            });
            //$('#calendarDipendenti').fullCalendar('refetchEvents');
        });
        $('#reset-filtri').unbind('click').click(function () {
            $('select#iddipendente').val(null).trigger('change');
            $('select#idatelier').val(null).trigger('change');
            idutente = 0;
            idatelier = 0;
            $('#calendarDipendenti').fullCalendar('refetchEvents');
        });
    } else {
        idatelier = $('select#iddipendente').data('idatelier');
        $('#reset-filtri').unbind('click').click(function () {
            $('select#iddipendente').val(null).trigger('change');
            $('select#idatelier').val(null).trigger('change');
            idutente = 0;
            $('#calendarDipendenti').fullCalendar('removeEventSources');
            $('#calendarDipendenti').fullCalendar('removeEvents');
            url_dip = './library/calendario-turni-atelier.php';
            $('#calendarDipendenti').fullCalendar('addEventSource', url_dip);
            $('#calendarDipendenti').fullCalendar('refetchEvents');
        });
    }
    if (screen.width > "768") {
        //view = "twoWeek";
        view = "agendaWeek";
        boxwidth = "50%";
    } else {
        view = "basicDay";
        boxwidth = "80%";
    }
    $('.addRichiestaDip').unbind('click').click(function () {
        var contentHtml = '<select id="caldip_idatelier" class="input_moduli sizing float_moduli_35" style="border:1px solid silver">';
        $('#html-atelier option').each(function () {
            contentHtml += '<option value="' + $(this).prop('value') + '">' + $(this).text() + '</option>';
        });
        contentHtml += '</select>' +
                '<input type="text" name="caldip_data_dal" id="caldip_data_dal" class="input_moduli sizing float_moduli_small_15" placeholder="Data dal" title="Data dal" style="border:1px solid silver" autocomplete="off" />' +
                '<input type="text" name="caldip_data_al" id="caldip_data_al" class="input_moduli sizing float_moduli_small_15" placeholder="Data al" title="Data al" style="border:1px solid silver" autocomplete="off" />' +
                '<input type="text" name="caldip_ora_da" id="caldip_ora_da" class="input_moduli sizing float_moduli_small_15 timepicker4" placeholder="Ora da" title="Ora da" style="border:1px solid silver" autocomplete="off" />' +
                '<input type="text" name="caldip_ora_a" id="caldip_ora_a" class="input_moduli sizing float_moduli_small_15 timepicker4" placeholder="Ora a" title="Ora a" style="border:1px solid silver" autocomplete="off" /><div class="chiudi"></div>' +
                '<select id="evento_tipo" class="input_moduli sizing float_moduli_35" style="border:1px solid silver">';
        $('#html-richiesta option').each(function () {
            contentHtml += '<option value="' + $(this).prop('value') + '">' + $(this).text() + '</option>';
        });
        contentHtml += '</select><input type="checkbox" id="allday" name="allday" value="1" /> Tutto il giorno<div class="chiudi"></div>' +
                '<textarea id="note" name="note" class="input_moduli sizing float_moduli_small_50" style="border:1px solid silver;height:100px;" placeholder="Note"></textarea>' +
                '<style>' +
                '.ui-timepicker-container{' +
                'z-index:9999999999 !important;' +
                '}' +
                '</style>';
        $.confirm({
            title: 'NUOVA RICHIESTA',
            content: contentHtml,
            boxWidth: '50%',
            useBootstrap: false,
            onContentReady: function () {
                $("#caldip_data_dal,#caldip_data_al").datepicker();
                $('input.timepicker4').timepicker({
                    timeFormat: 'HH:mm',
                    minTime: new Date(0, 0, 0, 8, 00, 0),
                    maxTime: new Date(0, 0, 0, 21, 0, 0),
                    interval: 30,
                    dynamic: false,
                    scrollbar: true
                });
            },
            buttons: {
                confirm: {
                    text: 'SALVA',
                    btnClass: 'btn-green',
                    keys: ['enter', 'shift'],
                    action: function () {
                        var data_cal_dal = encodeURIComponent($('#caldip_data_dal').val());
                        var data_cal_al = encodeURIComponent($('#caldip_data_al').val());
                        var ora_da = encodeURIComponent($('#caldip_ora_da').val());
                        var ora_a = encodeURIComponent($('#caldip_ora_a').val());
                        var evento_tipo = encodeURIComponent($('#evento_tipo').val());
                        var idatelier = encodeURIComponent($('#caldip_idatelier').val());
                        var allday = 0;
                        if ($('#allday').is(':checked')) {
                            allday = 1;
                        }
                        var note = encodeURIComponent($('#note').val());
                        switch (evento_tipo) {
                            case '':

                                break;
                        }
                        if (data_cal_dal != "" && evento_tipo != "") {
                            $.ajax({
                                type: "POST",
                                url: "./dipendenti.php",
                                data: "idatelier=&data_cal_dal=" + data_cal_dal + "&data_cal_al=" + data_cal_al + "&ora_da=" + ora_da + "&ora_a=" + ora_a +
                                        "&allday=" + allday + "&note=" + note +
                                        "&idatelier=" + idatelier +
                                        "&evento_tipo=" + evento_tipo + "&submit=inserisciEventoDip",
                                dataType: "json",
                                success: function (msg) {
                                    $('#calendarDipendenti').fullCalendar('refetchEvents');

                                }
                            });
                        } else {
                            alert('Devi selezionare il tipo e la data.');
                            return false;
                        }
                    }
                },
                cancel: {
                    text: 'ANNULLA',
                    action: function () {

                    }
                }
            }
        });
    });
    $('#calendarDipendenti').fullCalendar({
        theme: true,
        header: {
            left: 'prev,next today',
            center: 'title',
            right: 'month agendaWeek'
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
        height: 750,
        disableDragging: true,
        selectable: true,
        selectHelper: true,
        timeFormat: 'HH:mm',
        displayEventEnd: true,
        defaultView: view,
        minTime: '08:00:00',
        maxTime: '24:00:00',
        events: url,
        allDaySlot: true,
        slotDuration: '00:15:00',
        slotMinutes: 15,
        slotEventOverlap: false,
        viewRender: function (view, element) {
            var giorno_dal = view.start._d.getDate();
            var mese_dal = view.start._d.getMonth() + 1;
            if (mese_dal < 10) {
                mese_dal = pad(mese_dal, 2);
            }
            if (giorno_dal < 10) {
                giorno_dal = pad(giorno_dal, 2);
            }
            var settimana_dal = view.start._d.getFullYear() + '-' + mese_dal.toString() + '-' + giorno_dal;
            var giorno_al = view.end._d.getDate();
            if (giorno_al < 10) {
                giorno_al = pad(giorno_al, 2);
            }
            var mese_al = view.end._d.getMonth() + 1;
            if (mese_al < 10) {
                mese_al = pad(mese_al, 2);
            }
            var settimana_al = view.end._d.getFullYear() + '-' + mese_al.toString() + '-' + giorno_al;
            settimana_dal_curr = settimana_dal;
            settimana_al_curr = settimana_al;
            getOreSettimana(idatelier, settimana_dal, settimana_al);
            //console.log(settimana_dal + ' ' + settimana_al);
        },
        eventRender: function (event, element) {
            //console.log(event);
            if (event.edit == 1) {
                element.prepend("<span class='deleteevent-dip' style='color: #990000; float: right; padding-left: 3px;position:relative;z-index:99;'><i class=\"fa fa-times-circle fa-lg\" aria-hidden=\"true\"></i></span> <span class='editevent-dip' style='color: #000000; float: right;position:relative;z-index:99;'><i class=\"fa fa-pencil-square-o fa-lg\" aria-hidden=\"true\"></i></span>");
            } else {

            }
            if (event.richiesta_elimina) {
                element.prepend("<span class='richiesta-deleteevent-dip' style='color: #990000; float: right; padding-left: 3px;position:relative;z-index:99;'><i class=\"fa fa-times-circle fa-lg\" aria-hidden=\"true\"></i></span>");
                element.find(".richiesta-deleteevent-dip").click(function () {
                    $.confirm({
                        title: 'ATTENZIONE!',
                        content: 'Stai per inviare la richiesta eliminare il dato, CONFERMI?',
                        boxWidth: '50%',
                        useBootstrap: false,
                        buttons: {
                            confirm: {
                                text: 'SI',
                                btnClass: 'btn-blue',
                                keys: ['enter', 'shift'],
                                action: function () {
                                    $.ajax({
                                        type: "POST",
                                        url: "./dipendenti.php",
                                        data: "id=" + event.id + "&submit=eliminaEventoDipRichiesta",
                                        dataType: "json",
                                        success: function (msg) {
                                            alert('Richiesta inviata con successo!');
                                            return false;
                                        }
                                    });
                                }
                            },
                            cancel: {
                                text: 'NO'
                            }
                        }
                    });
                });
            }
            if (event.elimina && event.evento == 1) {
                element.prepend("<span class='deleteevent' style='color: #990000; float: right; padding-left: 3px;position:relative;z-index:99;cursor:pointer;'><i class=\"fa fa-times-circle fa-lg\" aria-hidden=\"true\"></i></span>");
                element.find(".deleteevent").click(function () {
                    $.confirm({
                        title: 'ATTENZIONE!',
                        content: 'Stai per eliminare il dato, CONFERMI?',
                        boxWidth: '50%',
                        useBootstrap: false,
                        buttons: {
                            confirm: {
                                text: 'SI',
                                btnClass: 'btn-blue',
                                keys: ['enter', 'shift'],
                                action: function () {
                                    $('#calendarDipendenti').fullCalendar('removeEvents', event.id);
                                    $.ajax({
                                        type: "POST",
                                        url: "./dipendenti.php",
                                        data: "id=" + event.id + "&submit=eliminaEventoDip",
                                        dataType: "json",
                                        success: function (msg) {
                                            $('#calendarDipendenti').fullCalendar('refetchEvents');
                                            getOreSettimana(idatelier, settimana_dal_curr, settimana_al_curr);
                                        }
                                    });
                                }
                            },
                            cancel: {
                                text: 'NO'
                            }
                        }
                    });

                });
            }
            element.find(".editevent-dip").click(function () {
                //console.log(event);
                $.ajax({
                    type: "POST",
                    url: "./dipendenti.php",
                    data: "id=" + event.id + "&submit=getTurnoDip",
                    dataType: "json",
                    success: function (msg) {
                        var contentHtml = '<style>.ui-timepicker-container{ z-index:9999999999 !important;}</style>' +
                                '<input type="text" name="data_cal" value="' + msg.dati[0].data_cal + '" id="data_cal" class="input_moduli sizing float_moduli_small_15" placeholder="Data" title="Data" style="border:1px solid silver" />' +
                                '<input type="text" name="ora_da" id="ora_da" value="' + msg.dati[0].ora_da + '" class="input_moduli sizing float_moduli_small_15 timepicker4" placeholder="Ora da" title="Ora da" style="border:1px solid silver" />' +
                                '<input type="text" name="ora_a" id="ora_a" value="' + msg.dati[0].ora_a + '" class="input_moduli sizing float_moduli_small_15 timepicker4" placeholder="Ora a" title="Ora a" style="border:1px solid silver" />' +
                                '<select name="caldip_idutente" id="caldip_idutente" class="input_moduli sizing float_moduli_35" style="border:1px solid silver">';
                        $('#html-dipendenti option').each(function () {
                            contentHtml += '<option value="' + $(this).prop('value') + '">' + $(this).text() + '</option>';
                        });
                        contentHtml += '</select>';
                        $.confirm({
                            title: 'MODIFICA TURNO A CALENDARIO',
                            content: contentHtml,
                            boxWidth: '50%',
                            useBootstrap: false,
                            onContentReady: function () {
                                $("#data_cal").datepicker();
                                $('input.timepicker4').timepicker({
                                    timeFormat: 'HH:mm',
                                    minTime: new Date(0, 0, 0, 8, 00, 0),
                                    maxTime: new Date(0, 0, 0, 21, 0, 0),
                                    interval: 30,
                                    dynamic: false,
                                    scrollbar: true
                                });
                                $('#caldip_idutente').val(msg.dati[0].idutente);
                            },
                            buttons: {
                                confirm: {
                                    text: 'SALVA',
                                    btnClass: 'btn-green',
                                    keys: ['enter', 'shift'],
                                    action: function () {
                                        var data_cal = encodeURIComponent($('#data_cal').val());
                                        var ora_da = encodeURIComponent($('#ora_da').val());
                                        var ora_a = encodeURIComponent($('#ora_a').val());
                                        var idutente = encodeURIComponent($('#caldip_idutente').val());
                                        if (data_cal != "" && ora_da != "" && ora_a != "" && idutente != "") {
                                            $.ajax({
                                                type: "POST",
                                                url: "./dipendenti.php",
                                                data: "idatelier=<?= $_GET['id'] ?>&data_cal=" + data_cal + "&ora_da=" + ora_da + "&ora_a=" + ora_a + "&idutente=" + idutente +
                                                        "&id=" + event.id + "&submit=aggiornaTurnoDip",
                                                dataType: "json",
                                                success: function (msg) {
                                                    $('#calendarDipendenti').fullCalendar('refetchEvents');
                                                    getOreSettimana(idatelier, settimana_dal_curr, settimana_al_curr);
                                                }
                                            });
                                        } else {
                                            return false;
                                        }
                                    }
                                },
                                cancel: {
                                    text: 'ANNULLA',
                                    action: function () {

                                    }
                                }
                            }
                        });
                    }
                });
            });
            element.find(".deleteevent-dip").click(function () {

                $.confirm({
                    title: 'ATTENZIONE!',
                    content: 'Stai per eliminare il dato, CONFERMI?',
                    boxWidth: '50%',
                    useBootstrap: false,
                    buttons: {
                        confirm: {
                            text: 'SI',
                            btnClass: 'btn-blue',
                            keys: ['enter', 'shift'],
                            action: function () {
                                $('#calendarDipendenti').fullCalendar('removeEvents', event.id);
                                $.ajax({
                                    type: "POST",
                                    url: "./dipendenti.php",
                                    data: "id=" + event.id + "&submit=eliminaTurnoDip",
                                    dataType: "json",
                                    success: function (msg) {
                                        $('#calendarDipendenti').fullCalendar('refetchEvents');
                                        getOreSettimana(idatelier, settimana_dal_curr, settimana_al_curr);
                                    }
                                });
                            }
                        },
                        cancel: {
                            text: 'NO'
                        }
                    }
                });
            });
        }
    });
    $('.addTurnoDip').unbind('click').click(function () {
        var contentHtml = '<style>.ui-timepicker-container{ z-index:9999999999 !important;}</style>' +
                '<input type="text" name="caldip_data_dal" id="caldip_data_dal" class="input_moduli sizing float_moduli_small_15" placeholder="Data dal" title="Data dal" style="border:1px solid silver" />' +
                '<input type="text" name="caldip_data_al" id="caldip_data_al" class="input_moduli sizing float_moduli_small_15" placeholder="Data al" title="Data al" style="border:1px solid silver" />' +
                '<input type="text" name="caldip_ora_da" id="caldip_ora_da" class="input_moduli sizing float_moduli_small_15 timepicker4" placeholder="Ora da" title="Ora da" style="border:1px solid silver" />' +
                '<input type="text" name="caldip_ora_a" id="caldip_ora_a" class="input_moduli sizing float_moduli_small_15 timepicker4" placeholder="Ora a" title="Ora a" style="border:1px solid silver" />' +
                '<select name="caldip_idutente" id="caldip_idutente" class="input_moduli sizing float_moduli_35" style="border:1px solid silver">';
        $('#html-dipendenti option').each(function () {
            contentHtml += '<option value="' + $(this).prop('value') + '"' + (idutente == $(this).prop('value') ? ' selected' : '') + '>' + $(this).text() + '</option>';
        });
        contentHtml += '</select>';
        $.confirm({
            title: 'INSERISCI TURNO A CALENDARIO',
            content: contentHtml,
            boxWidth: '50%',
            useBootstrap: false,
            onContentReady: function () {
                //console.log('ok');
                $("#caldip_data_dal,#caldip_data_al").datepicker();
                $('input.timepicker4').timepicker({
                    timeFormat: 'HH:mm',
                    minTime: new Date(0, 0, 0, 8, 00, 0),
                    maxTime: new Date(0, 0, 0, 21, 0, 0),
                    interval: 30,
                    dynamic: false,
                    scrollbar: true
                });
            },
            buttons: {
                confirm: {
                    text: 'SALVA',
                    btnClass: 'btn-green',
                    keys: ['enter', 'shift'],
                    action: function () {
                        var data_cal_dal = encodeURIComponent($('#caldip_data_dal').val());
                        var data_cal_al = encodeURIComponent($('#caldip_data_al').val());
                        var ora_da = encodeURIComponent($('#caldip_ora_da').val());
                        var ora_a = encodeURIComponent($('#caldip_ora_a').val());
                        var idutente = encodeURIComponent($('#caldip_idutente').val());
                        if (data_cal_dal != "" && ora_da != "" && ora_a != "" && idutente != "") {
                            $.ajax({
                                type: "POST",
                                url: "./dipendenti.php",
                                data: "idatelier=" + idatelier + "&data_cal_dal=" + data_cal_dal + "&data_cal_al=" + data_cal_al + "&ora_da=" + ora_da + "&ora_a=" + ora_a + "&idutente=" + idutente + "&submit=inserisciTurnoDip",
                                dataType: "json",
                                success: function (msg) {
                                    $('#calendarDipendenti').fullCalendar('refetchEvents');
                                    getOreSettimana(idatelier, settimana_dal_curr, settimana_al_curr);
                                }
                            });
                        } else
                        {
                            return false;
                        }

                    }
                },
                cancel: {
                    text: 'ANNULLA',
                    action: function () {

                    }
                }
            }
        });
    });
}

function fileDip()
{
    $('.showcont').show().load('./form/form-file-dip.php', function () {

    });
}

function reportApp()
{
    $('.showcont').show().load('./form/form-report-appuntamenti.php', function () {

    });
}

function reportAppTot()
{
    $('.showcont').show().load('./form/form-report-appuntamenti-tot.php', function () {

    });
}