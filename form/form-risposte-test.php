<?php
include '../library/controllo.php';
include '../library/config.php';
include '../library/connessione.php';
include '../library/basic.class.php';
include '../library/functions.php';

$id = $_GET['id'];
$domanda = getDati("domande_test", "where id=$id limit 1;")[0];
?>
<div class="tit_big"><?= $domanda['titolo'] ?></div>
<div class="tit_big_bordato">Risposte</div>
<div class="dipendenti">
    <div class="sizing">
        <div style="font-size: 1.3em;margin:20px 0;"><a class="addCorso" href="javascript:;" onclick="$('#addRisp').slideToggle('slow');"><i class="fa fa-plus-circle" aria-hidden="true"></i> Nuovo inserimento</a> <input type="checkbox" id="ordina" /> Ordina righe</div>
        <div id="addRisp" style="display: none;">
            <form method="post" action="" id="form-risp">
                <input type="text" name="titolo" id="titolo" class="input_moduli sizing float_moduli required" placeholder="Risposta" title="Risposta" value="" />
                <div class="chiudi"></div>
                <input type="text" name="punteggio" id="punteggio" class="input_moduli sizing float_moduli required" placeholder="Punteggio" title="Punteggio" value="" />
                <div class="chiudi"></div>
                <input type="checkbox" id="corretta" name="corretta" /> Corretta
                <div class="chiudi"></div>
                <input type="hidden" name="iddomanda" id="iddomanda" value="<?= $_GET['id'] ?>" />
                <input type="submit" id="submitformrisp" value="Salva" class="submit_form nopost" />
                <div class="chiudi" style="height: 100px;"></div>
            </form>
        </div>
        <div id="tbRisp"></div>
    </div>
</div>
<div class="bottone_chiudi sizing"><a href="javascript:;" class="sizing" onclick="testFormazione(<?= $domanda['idformazione'] ?>, <?= $domanda['idlezione'] ?>);">Chiudi</a></div>
<script>
    $(function () {
        $.datepicker.setDefaults($.datepicker.regional[ "it" ]);
        $('#ordina').unbind('click').click(function () {
            if ($(this).is(':checked')) {
                $("#tbRisp").jsGrid('option', 'editing', false);
                $('#tbRisp .jsgrid-control-field').hide();
            } else {
                rispDomanda();
            }
        });
        function rispDomanda() {
            $.ajax({
                type: "POST",
                url: "./formazione.php",
                data: "&submit=mostraRisp&iddomanda=<?= $_GET["id"] ?>",
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
                        {name: "punteggio", type: "text", title: "<b>Punteggio</b>", css: "customRow"},
                        {name: "corretta", type: "checkbox", title: "<b>Corretta</b>", css: "customRow"},
                        {type: "control"}
                    ];


                    $("#tbRisp").jsGrid({
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
                                data: "id=" + idd + "&submit=delRisp",
                                dataType: "json",
                                success: function () {
                                }
                            });
                        },
                        // edita item
                        onItemEditing: function (args) {

                        },
                        onItemUpdated: function (args) {
                            var valori = $.param(args.item);
                            $.ajax({
                                type: "POST",
                                url: "./formazione.php",
                                data: valori + "&submit=editRisp",
                                dataType: "json",
                                success: function () {
                                    rispDomanda();
                                }
                            });
                        },
                        onRefreshed: function () {
                            var $gridData = $("#tbRisp .jsgrid-grid-body tbody");
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
                                        data: "rows=" + row_sorted.join(',') + "&submit=sortRisp",
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
        if ($('#tbRisp').length > 0) {
            rispDomanda();
        }
        $.validator.messages.required = '';
        $("#form-risp").validate({
            submitHandler: function () {
                $("#submitformrisp").ready(function () {
                    var datastring = $("#form-risp *").not(".nopost").serialize();
                    $.ajax({
                        type: "POST",
                        url: "./formazione.php",
                        data: datastring + "&submit=addRisp",
                        dataType: "json",
                        success: function (msg) {
                            if (msg.msg === "ko") {
                                alert(msg.msgko);
                            } else {
                                $('#form-risp').trigger('reset');
                                $('#messaggio').slideToggle('fast').delay(2000).slideToggle('slow');
                                rispDomanda();
                                $('#addRisp').slideToggle('slow');
                            }
                        }
                    });
                });
            }
        });
    });
</script>