<?php
include '../library/controllo.php';
include '../library/config.php';
include '../library/connessione.php';
include '../library/basic.class.php';
include '../library/functions.php';

/* richiamo dati azienda da database */

$dati = getDati("dati_gestionale", "");
foreach ($dati as $datigest) {
    $id = $datigest['id'];
    $logo = $datigest['logo'];
    $ragionesociale = $datigest['ragione_sociale'];
    $sedelegale = $datigest['sede_legale'];
    $sedeoperativa1 = $datigest['sede_operativa1'];
    $sedeoperativa2 = $datigest['sede_operativa2'];
    $piva = $datigest['piva'];
    $cf = $datigest['cf'];
    $rea = $datigest['rea'];
    $tel = $datigest['tel'];
    $fax = $datigest['fax'];
    $sitoazi = $datigest['sito'];
    $email = $datigest['email'];
    $coord = $datigest['coord_bancarie'];
    $coord1 = $datigest['coord_bancarie1'];
    $coord2 = $datigest['coord_bancarie2'];
    $coord3 = $datigest['coord_bancarie3'];
    $coord4 = $datigest['coord_bancarie4'];
    $coord5 = $datigest['coord_bancarie5'];
    $coord6 = $datigest['coord_bancarie6'];
    $coord7 = $datigest['coord_bancarie7'];
    $coord8 = $datigest['coord_bancarie8'];
    $coord9 = $datigest['coord_bancarie9'];
}

if (strlen($logo) > 0) {
    $logo_gest = "<img src=\"/immagini/logo/$logo\" />";
    $close_logo = "<a href=\"javascript:;\" alt=\"Cancella logo\" title=\"Cancella logo\" onclick=\"javascript:delLogo('$id');\"><div class=\"close\"><img src=\"/immagini/close.png\" /></div></a>";
} else {
     $logo_gest = "<img src=\"/immagini/nologo.png\" />";
}

$submit = "";
if (isset($_POST['submit'])) {
    $submit = $_POST['submit'];
}
/* province */
switch ($submit) {
    
}
?>

<script type="text/javascript" src="./js/functions-impostazioni.js"></script>
<!-- upload multiplo di foto fineuploader -->
<script type="text/javascript" src="/js/fineuploader/client/fileuploader.js"></script>
<link type="text/css" href="/js/fineuploader/client/fileuploader.css" rel="Stylesheet" /> 
<div class="testo_import">
    <strong>CARICA IL LOGO DELLA TUA AZIENDA</strong><br />
    Formati ammessi: <b>.jpg, .JPG, .png, .PNG</b><br />    
</div>
<div class="logo_gest">
    <?php echo $close_logo.$logo_gest; ?>
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
        action: "/js/fineuploader/upload.php?tipo=logo",
        autoUpload: false,
        uploadButtonText: '<i class="fa fa-plus fa-lg" aria-hidden="true"></i> Seleziona o trascina qui il tuo logo',
        multiple: false,
        allowedExtensions: ['jpg', 'JPG', 'png', 'PNG'],
        //sizeLimit: 50000
        'onComplete': function (id, fileName, responseJSON) {
            var nomefile = responseJSON.nomefile;
            $.ajax({
                type: "POST",
                url: "./impostazioni.php",
                data: "nomefile=" + nomefile + "&submit=sendlogo",
                dataType: "json",
                success: function (msg) {
                    $('#messaggio').slideToggle('fast').delay(2000).slideToggle('slow');
                    $('.logo_gest').html("<a href=\"javascript:;\" alt=\"Cancella logo\" title=\"Cancella logo\" onclick=\"javascript:delLogo('"+msg.id+"');\"><div class=\"close\"><img src=\"/immagini/close.png\" /></div></a><img src=\"/immagini/logo/" + nomefile + "\" />");
                }
            });
        }
    });
    $('#uploadfile').click(function () {
        uploader.uploadStoredFiles();
    });
</script>
<br /><br />
<div class="chiudi" style="border-bottom: 1px solid #696969; margin-bottom: 15px;"></div>
<div class="testo_import">
    <strong>DATI AZIENDALI</strong><br />
</div>
<form method="post" action="" id="formdatiazienda" name="formdatiazienda">        
    <input type="text" name="ragione_sociale" id="ragione_sociale" class="input_moduli sizing  float_moduli_50 required" placeholder="Ragione Sociale" title="Ragione Sociale" value="<?php echo $ragionesociale; ?>" />            
    <div class="chiudi"></div>
    <textarea name="sede_legale" id="sede_legale" class="textarea_moduli sizing  float_moduli" placeholder="Sede Legale" title="Sede Legale"><?php echo $sedelegale; ?></textarea>     
    <textarea name="sede_operativa1" id="sede_operativa1" class="textarea_moduli sizing  float_moduli" placeholder="Sede operativa" title="Sede operativa"><?php echo $sedeoperativa1; ?></textarea>     
    <textarea name="sede_operativa2" id="sede_operativa2" class="textarea_moduli sizing  float_moduli" placeholder="Sede operativa 2" title="Sede operativa 2"><?php echo $sedeoperativa2; ?></textarea>     
    <div class="chiudi"></div>
    <input type="text" name="piva" id="piva" class="input_moduli sizing float_moduli" placeholder="P.Iva" title="P.Iva" value="<?php echo $piva; ?>" />
    <input type="text" name="cf" id="cf" class="input_moduli sizing float_moduli" placeholder="Codice fiscale" title="Codice fiscale" value="<?php echo $cf; ?>" />
    <input type="text" name="rea" id="rea" class="input_moduli sizing float_moduli" placeholder="REA" title="REA" value="<?php echo $rea; ?>" /> 
    <div class="chiudi"></div>       
    <input type="text" name="tel" id="tel" class="input_moduli sizing float_moduli" placeholder="Telefono" title="Telefono" value="<?php echo $tel; ?>" />  
    <input type="text" name="fax" id="fax" class="input_moduli sizing float_moduli" placeholder="Fax" title="Fax" value="<?php echo $fax; ?>" />  
    <input type="text" name="email" id="email" class="input_moduli sizing float_moduli" placeholder="Email" title="Email" value="<?php echo $email; ?>" />  
    <div class="chiudi"></div>      
    <input type="text" name="sito" id="sito" class="input_moduli sizing  float_moduli" placeholder="Sito Web" title="Sito Web" value="<?php echo $sitoazi; ?>" />
    <div class="chiudi"></div>    
<!--     <div class="tit_big">COORDINATE BANCARIE</div>
    <textarea name="coord_bancarie" id="coord_bancarie" class="textarea_moduli sizing  float_moduli" placeholder="Coordinate Bancarie 1" title="Coordinate Bancarie 1"><?php echo $coord; ?></textarea>     
    <textarea name="coord_bancarie1" id="coord_bancarie1" class="textarea_moduli sizing  float_moduli" placeholder="Coordinate Bancarie 2" title="Coordinate Bancarie 2"><?php echo $coord1; ?></textarea>
    <textarea name="coord_bancarie2" id="coord_bancarie2" class="textarea_moduli sizing  float_moduli" placeholder="Coordinate Bancarie 3" title="Coordinate Bancarie 3"><?php echo $coord2; ?></textarea>
    <textarea name="coord_bancarie3" id="coord_bancarie3" class="textarea_moduli sizing  float_moduli" placeholder="Coordinate Bancarie 4" title="Coordinate Bancarie 4"><?php echo $coord3; ?></textarea>
    <textarea name="coord_bancarie4" id="coord_bancarie4" class="textarea_moduli sizing  float_moduli" placeholder="Coordinate Bancarie 5" title="Coordinate Bancarie 5"><?php echo $coord4; ?></textarea>
    <textarea name="coord_bancarie5" id="coord_bancarie5" class="textarea_moduli sizing  float_moduli" placeholder="Coordinate Bancarie 6" title="Coordinate Bancarie 6"><?php echo $coord5; ?></textarea>
    <textarea name="coord_bancarie6" id="coord_bancarie6" class="textarea_moduli sizing  float_moduli" placeholder="Coordinate Bancarie 7" title="Coordinate Bancarie 7"><?php echo $coord6; ?></textarea>
    <textarea name="coord_bancarie7" id="coord_bancarie7" class="textarea_moduli sizing  float_moduli" placeholder="Coordinate Bancarie 8" title="Coordinate Bancarie 8"><?php echo $coord7; ?></textarea>
    <textarea name="coord_bancarie8" id="coord_bancarie8" class="textarea_moduli sizing  float_moduli" placeholder="Coordinate Bancarie 9" title="Coordinate Bancarie 9"><?php echo $coord8; ?></textarea>
    <textarea name="coord_bancarie9" id="coord_bancarie9" class="textarea_moduli sizing  float_moduli" placeholder="Coordinate Bancarie 10" title="Coordinate Bancarie 10"><?php echo $coord9; ?></textarea>
    
    <div class="chiudi"></div>-->
    <input type="submit" id="submitformdatiazienda" value="Salva" class="submit_form nopost" />
    <div class="chiudi"></div>
</form>