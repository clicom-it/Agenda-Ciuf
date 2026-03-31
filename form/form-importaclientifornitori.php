<?php
include '../library/controllo.php';
include '../library/config.php';
include '../library/connessione.php';
include '../library/basic.class.php';
include '../library/functions.php';

$tipo = $_GET['tipo'];
?>

<script type="text/javascript" src="./js/functions-clientifornitori.js"></script>
<!-- upload multiplo di foto fineuploader -->
<script type="text/javascript" src="/js/fineuploader/client/fileuploader.js"></script>
<link type="text/css" href="/js/fineuploader/client/fileuploader.css" rel="Stylesheet" /> 
<div class="testo_import">
    Unico formato ammesso: <b>.xls</b> (Excel)<br />
    Il file deve avere obbligatoriamente n° 13 colonne e la prima riga di ogni colonna deve avere obbligatoriamente questo nome:<br />
    codice, nome, cognome, azienda, codice fiscale, partita iva, indirizzo, cap, localita, provincia, telefono, cellulare, email<br />
    <strong>N.B. in caso di cliente o fornitore estero, non compilare la provincia.</strong>
</div>
<div id="file-uploader">
    <noscript>
    <p>Please enable JavaScript to use file uploader.</p>
    <!-- or put a simple form for upload here -->
    </noscript>                                
</div>
<div class="caricafile">
    <a href="javascript:;" id="uploadfile"><i class="fa fa-upload fa-lg" aria-hidden="true"></i>  Upload file</a>
</div>
<script type="text/javascript">
    var uploader = new qq.FileUploader({
        element: document.getElementById('file-uploader'),
        action: "/js/fineuploader/upload.php",
        autoUpload: false,
        uploadButtonText: '<i class="fa fa-plus fa-lg" aria-hidden="true"></i> Seleziona o trascina qui il file excel',
//        debug: true,
        multiple: false,
        allowedExtensions: ['xls'],
        //sizeLimit: 50000
        'onComplete': function (id, fileName, responseJSON) {
            var nomefile = responseJSON.nomefile;
            $.ajax({
                type: "POST",
                url: "./clientifornitori.php",
                data: "tipo=" + <?php echo $tipo; ?> + "&nomefile=" + nomefile + "&submit=sendfile",
                dataType: "json",
                success: function (msg) {
                    $('#messaggio').slideToggle('fast').delay(2000).slideToggle('slow');
                }
            });
        }
    });
    $('#uploadfile').click(function () {
        uploader.uploadStoredFiles();
    });
</script>