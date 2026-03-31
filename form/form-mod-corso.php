<?php
include '../library/controllo.php';
include '../library/config.php';
include '../library/connessione.php';
include '../library/basic.class.php';
include '../library/functions.php';
$id = $_GET['id'];
$video = getDati("formazione_corsi", "where id=" . $id);
?>
<script type="text/javascript" src="./js/fineuploader/client/fileuploader.js"></script>
<link type="text/css" href="./js/fineuploader/client/fileuploader.css" rel="Stylesheet" />
<div class="tit_big">Modifica corso <?= $video[0]['titolo'] ?></div>
<form method="post" action="" id="form-corso">
    <input type="text" name="titolo" id="titolo" class="input_moduli sizing float_moduli required" placeholder="Titolo corso" title="Titolo corso" value="<?= $video[0]['titolo'] ?>" />
    <div class="chiudi"></div>
    <textarea name="descrizione" id="descrizione" class="textarea_moduli sizing float_moduli" placeholder="Descrizione" title="Descrizione"><?= $video[0]['descrizione'] ?></textarea>
    <div class="chiudi"></div>
    <div id="file-video">
        <noscript>
        <p>Please enable JavaScript to use file uploader.</p>
        <!-- or put a simple form for upload here -->
        </noscript>                                
    </div>
    <?php if ($video[0]['video'] != "") { ?>
        <p style="margin:20px 0;"><a href="/<?= FOLDER_FORMAZIONE ?>/<?= $id ?>/<?= $video[0]['video'] ?>" target="_blank">Guarda il video</a></p>
    <?php } ?>
    <input type="hidden" name="video" id="video" value="<?= $video[0]['video'] ?>" />
    <input type="hidden" name="id" id="id" value="<?= $video[0]['id'] ?>" />
    <input type="submit" id="submitformcorso" value="Salva" class="submit_form nopost" />
    <div class="chiudi" style="height: 100px;"></div>
</form>
<div class="tit_big_bordato">Allegati</div>
<div class="dipendenti">
    <div class="sizing">
        <div style="font-size: 1.3em;margin:20px 0;"><a class="addMedia" href="javascript:;" onclick="$('#addMedia').slideToggle('slow');"><i class="fa fa-plus-circle" aria-hidden="true"></i> Nuovo inserimento</a></div>
        <div id="addMedia" style="display: none;">
            <form method="post" action="" id="form-media">
                <input type="text" name="titolo" id="titolo" class="input_moduli sizing float_moduli required" placeholder="Titolo allegato" title="Titolo allegato" value="" />
                <div class="chiudi"></div>
                <div id="file-media">
                    <noscript>
                    <p>Please enable JavaScript to use file uploader.</p>
                    <!-- or put a simple form for upload here -->
                    </noscript>                                
                </div>
                <input type="hidden" name="idcorso" id="idcorso" value="<?= $_GET['id'] ?>" />
                <input type="hidden" name="media" id="media" value="" />
                <input type="submit" id="submitformmedia" value="Salva" class="submit_form nopost" />
                <div class="chiudi" style="height: 100px;"></div>
            </form>
        </div>
        <div id="tbAllegati"></div>
    </div>
</div>
<div class="chiudi" style="height: 100px;"></div>
<div class="bottone_chiudi sizing"><a href="javascript:;" class="sizing" onclick="corsiFormazione(<?= $video[0]['idformazione'] ?>);">Chiudi</a></div>
<script>
    $(function () {
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
                        data: datastring + "&submit=editCorso",
                        dataType: "json",
                        success: function (msg) {
                            if (msg.msg === "ko") {
                                alert(msg.msgko);
                            } else {
                                $('#messaggio').slideToggle('fast').delay(2000).slideToggle('slow');
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
        $.validator.messages.required = '';
        $("#form-media").validate({
            submitHandler: function () {
                $("#submitformmedia").ready(function () {
                    if ($('#media').val() == '') {
                        alert('Devi caricare il file');
                        return false;
                    }
                    var datastring = $("#form-media *").not(".nopost").serialize();
                    $.ajax({
                        type: "POST",
                        url: "./formazione.php",
                        data: datastring + "&submit=addMedia",
                        dataType: "json",
                        success: function (msg) {
                            if (msg.msg === "ko") {
                                alert(msg.msgko);
                            } else {
                                $('#form-media').trigger('reset');
                                $('#messaggio').slideToggle('fast').delay(2000).slideToggle('slow');
                                mostraMedia();
                                $('#addMedia').slideToggle('slow');
                            }
                        }
                    });
                });
            }
        });
        var media = new qq.FileUploader({
            element: document.getElementById('file-media'),
            action: "./js/fineuploader/upload-media.php",
            autoUpload: true,
            uploadButtonText: '<img src="./immagini/sel_upload.png" /> Seleziona il file jpg o pdf',
            //debug: true,
            multiple: false,
            allowedExtensions: ['jpg', 'JPG', 'pdf', 'PDF'],
            //sizeLimit: 50000
            'onComplete': function (id, fileName, responseJSON) {
                if (responseJSON.success) {
                    $('#media').val(responseJSON.nomefile);
                } else {
                    return false;
                }
            }
        });
        function mostraMedia() {
            $.ajax({
                type: "POST",
                url: "./formazione.php",
                data: "&submit=mostraMedia&idcorso=<?= $_GET["id"] ?>",
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
                        {name: "nomefile", title: "<b>File</b>", css: "customRow"},
                        {type: "control", itemTemplate: function (value, item) {
                                var $result = jsGrid.fields.control.prototype.itemTemplate.apply(this, arguments);
                                var $myButton = $("<i style=\"margin-left: 5px; color: #2E65A1;\"class=\"fa fa-download fa-lg\" aria-hidden=\"true\" title=\"Apri il file\"></i>")
                                        .click(function (e) {
                                            //corsiFormazione(item.id);
                                            window.open('/formazione/' + item.idcorso + '/' + item.nomefile, '_blank');
                                            e.stopPropagation();
                                        });
                                return $result.add($myButton);
                            }
                        }
                    ];


                    $("#tbAllegati").jsGrid({
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
                                data: "id=" + idd + "&submit=delMedia",
                                dataType: "json",
                                success: function () {
                                }
                            });
                        },
                        // edita item
                        onItemUpdated: function (args) {
                            var valori = $.param(args.item);
                            $.ajax({
                                type: "POST",
                                url: "./formazione.php",
                                data: valori + "&submit=editMedia",
                                dataType: "json",
                                success: function () {
                                }
                            });
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
        if ($('#tbAllegati').length > 0) {
            mostraMedia();
        }
    });
</script>
