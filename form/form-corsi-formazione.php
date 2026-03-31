<?php
include '../library/controllo.php';
include '../library/config.php';
include '../library/connessione.php';
include '../library/basic.class.php';
include '../library/functions.php';

$id = $_GET['id'];
$formazione = getDati("formazione", "where id=$id limit 1;")[0];
?>
<script type="text/javascript" src="./js/fineuploader/client/fileuploader.js"></script>
<link type="text/css" href="./js/fineuploader/client/fileuploader.css" rel="Stylesheet" />
<div class="tit_big"><?= $formazione['titolo'] ?></div>
<div class="tit_big_bordato">Corsi</div>
<div class="dipendenti">
    <div class="sizing">
        <div style="font-size: 1.3em;margin:20px 0;"><a class="addCorso" href="javascript:;" onclick="$('#addCorso').slideToggle('slow');"><i class="fa fa-plus-circle" aria-hidden="true"></i> Nuovo inserimento</a> <input type="checkbox" id="ordina" /> Ordina righe</div>
        <div id="addCorso" style="display: none;">
            <form method="post" action="" id="form-corso">
                <input type="text" name="titolo" id="titolo" class="input_moduli sizing float_moduli required" placeholder="Titolo corso" title="Titolo corso" value="" />
                <div class="chiudi"></div>
                <textarea name="descrizione" id="descrizione" class="textarea_moduli sizing float_moduli" placeholder="Descrizione" title="Descrizione"></textarea>
                <div class="chiudi"></div>
                <div id="file-video">
                    <noscript>
                    <p>Please enable JavaScript to use file uploader.</p>
                    <!-- or put a simple form for upload here -->
                    </noscript>                                
                </div>
                <input type="hidden" name="idformazione" id="idformazione" value="<?= $_GET['id'] ?>" />
                <input type="hidden" name="video" id="video" value="" />
                <input type="submit" id="submitformcorso" value="Salva" class="submit_form nopost" />
                <div class="chiudi" style="height: 100px;"></div>
            </form>
        </div>
        <div id="tbCorsi"></div>
    </div>
</div>
<div class="bottone_chiudi sizing"><a href="javascript:;" class="sizing" onclick="mostraFormazione();">Chiudi</a></div>
<script>
    $(function () {
        $.datepicker.setDefaults($.datepicker.regional[ "it" ]);
        $('#ordina').unbind('click').click(function () {
            if ($(this).is(':checked')) {
                $("#tbCorsi").jsGrid('option', 'editing', false);
                $('#tbCorsi .jsgrid-control-field').hide();
            } else {
                mostraCorsi();
            }
        });
        function mostraCorsi() {
            $.ajax({
                type: "POST",
                url: "./formazione.php",
                data: "&submit=mostraCorsi&idformazione=<?= $_GET["id"] ?>",
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
                        {name: "descrizione", type: "text", title: "<b>Descrizione</b>", css: "customRow"},
                        {name: "video", type: "text", title: "<b>Video</b>", css: "customRow"},
                        {type: "control", itemTemplate: function (value, item) {
                                var $result = jsGrid.fields.control.prototype.itemTemplate.apply(this, arguments);
                                var $myButton3 = $("<i style=\"margin-left: 5px; color: #2E65A1;\"class=\"fa " + (item.attivo == 1 ? "fa-circle" : "fa-circle-o") + " fa-lg\" aria-hidden=\"true\" title=\"Mostra/Nascondi\"></i>")
                                .click(function (e) {
                                    attivaCorsi(<?= $_GET["id"] ?>, item.id, (item.attivo == 1 ? 0 : 1));
                                    e.stopPropagation();
                                });
                                var $myButton = $("<i style=\"margin-left: 5px; color: #2E65A1;\"class=\"fa fa-video-camera fa-lg\" aria-hidden=\"true\" title=\"Guarda il video\"></i>")
                                        .click(function (e) {
                                            //corsiFormazione(item.id);
                                            window.open('./video.php?id=' + item.id + '', '_blank');
                                            e.stopPropagation();
                                        });
                                var $myButton2 = $("<i style=\"margin-left: 5px; color: #2E65A1;\"class=\"fa fa-list fa-lg\" aria-hidden=\"true\" title=\"Gestione Test\"></i>")
                                .click(function (e) {
                                    testFormazione(<?= $_GET['id'] ?> ,item.id);
                                    e.stopPropagation();
                                });
                                return $result.add($myButton3).add($myButton).add($myButton2);
                            }
                        }
                    ];


                    $("#tbCorsi").jsGrid({
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
                                data: "id=" + idd + "&submit=delCorso",
                                dataType: "json",
                                success: function () {
                                }
                            });
                        },
                        // edita item
                        onItemEditing: function (args) {
                            args.cancel = true;
                            var ide = args.item.id;
                            modCorso(ide);
                        },
                        onRefreshed: function () {
                            var $gridData = $("#tbCorsi .jsgrid-grid-body tbody");
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
                                        data: "rows=" + row_sorted.join(',') + "&submit=sortCorsi",
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
        if ($('#tbCorsi').length > 0) {
            mostraCorsi();
        }
        $.validator.messages.required = '';
        $("#form-corso").validate({
            submitHandler: function () {
                $("#submitformcorso").ready(function () {
                    if ($('#video').val() == '') {
                        alert('Devi caricare il video del corso');
                        return false;
                    }
                    var datastring = $("#form-corso *").not(".nopost").serialize();
                    $.ajax({
                        type: "POST",
                        url: "./formazione.php",
                        data: datastring + "&submit=addCorso",
                        dataType: "json",
                        success: function (msg) {
                            if (msg.msg === "ko") {
                                alert(msg.msgko);
                            } else {
                                $('#form-corso').trigger('reset');
                                $('#messaggio').slideToggle('fast').delay(2000).slideToggle('slow');
                                mostraCorsi();
                                $('#addCorso').slideToggle('slow');
                            }
                        }
                    });
                });
            }
        });
        var video = new qq.FileUploader({
            element: document.getElementById('file-video'),
            action: "./js/fineuploader/upload-video.php",
            autoUpload: true,
            uploadButtonText: '<img src="./immagini/sel_upload.png" /> Seleziona o trascina qui il video mp4',
            //debug: true,
            multiple: false,
            allowedExtensions: ['mp4', 'MP4'],
            //sizeLimit: 50000
            'onComplete': function (id, fileName, responseJSON) {
                if (responseJSON.success) {
                    $('#video').val(responseJSON.nomefile);
                } else {
                    return false;
                }
            }
        });
    });
</script>