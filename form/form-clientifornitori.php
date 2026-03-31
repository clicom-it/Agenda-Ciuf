<?php
include '../library/controllo.php';
include '../library/config.php';
include '../library/connessione.php';
include '../library/basic.class.php';
include '../library/functions.php';

/* metodi di pagamento */
$metodi = getDati("metodi_pagamento", "");
foreach ($metodi as $datimetodi) {
    $metodopagamento .= "<option value=\"" . $datimetodi['id'] . "\">" . $datimetodi['nome'] . "</option>";
}
/**/

/* nazioni, regioni, province, cap */
/* nazioni */
$nazioniprint = "<option value=\"\">Seleziona nazione</option>";
$nazioni = getNazioni();
foreach ($nazioni as $nazionid) {
    $nazioniprint .= "<option value=\"" . $nazionid['tld'] . "\">" . $nazionid['nazione'] . "</option>";
}
/* regioni */
$regioniprint = "<option value=\"\">Seleziona regione</option>";
$regioni = getRegione();
foreach ($regioni as $regionid) {
    $regioniprint .= "<option value=\"" . $regionid['regione'] . "\">" . $regionid['regione'] . "</option>";
}
$submit = "";
if (isset($_POST['submit'])) {
    $submit = $_POST['submit'];
}
/* province */
switch ($submit) {
    case "setprovince":
        $regione = $_POST['regione'];
        $provinceprint = "<option value=\"\">Seleziona provincia</option>";
        $province = getProvincia($regione);
        foreach ($province as $provinced) {
            $provinceprint .= "<option value=\"" . $provinced['sigla'] . "\">" . $provinced['provincia'] . "</option>";
        }
        die('{"msg" : ' . json_encode($provinceprint) . '}');
        break;

    case "setcomune":
        $provincia = $_POST['provincia'];
        $comuneprint = "<option value=\"\">Seleziona comune</option>";
        $comune = getComune($provincia);
        foreach ($comune as $comuned) {
            $comuneprint .= "<option value=\"" . $comuned['comune'] . "\">" . $comuned['comune'] . "</option>";
        }
        die('{"msg" : ' . json_encode($comuneprint) . '}');
        break;

    case "setcap":
        $comune = $_POST['comune'];
        $capprint = "<option value=\"\">Seleziona cap</option>";
        $cap = getCap($comune);
        foreach ($cap as $capd) {
            $capprint .= "<option value=\"" . $capd['cap'] . "\">" . $capd['cap'] . "</option>";
        }
        die('{"msg" : ' . json_encode($capprint) . '}');
        break;
}
?>
<script type="text/javascript">
    $(document).ready(function () {
        $('form').attr('autocomplete', 'off');
    });
    /* set provincia */
    function setProvincia(regione, selezionata, ident) {
        // ident = 1 indirizzo per fatturazione
        // ident = 2 indirizzo alternativo per spedizione
        $.ajax({
            type: "POST",
            url: "./form/form-clientifornitori.php",
            data: "regione=" + regione + "&submit=setprovince",
            dataType: "json",
            success: function (msg)
            {
                if (ident === '1') {
                    $('#provincia').html('');
                    $('#comune').html('');
                    $('#cap').html('');
                    $('#provincia').append(msg.msg).val(selezionata);
                } else if (ident === '2') {
                    $('#provinciaspedizione').html('');
                    $('#comunespedizione').html('');
                    $('#capspedizione').html('');
                    $('#provinciaspedizione').append(msg.msg).val(selezionata);
                }
            }
        });
    }
    /* set comune */
    function setComune(provincia, selezionata, ident) {
        // ident = 1 indirizzo per fatturazione
        // ident = 2 indirizzo alternativo per spedizione
        $.ajax({
            type: "POST",
            url: "./form/form-clientifornitori.php",
            data: "provincia=" + provincia + "&submit=setcomune",
            dataType: "json",
            success: function (msg)
            {
                if (ident === '1') {
                    $('#comune').html('');
                    $('#cap').html('');
                    $('#comune').append(msg.msg).val(selezionata);
                } else if (ident === '2') {
                    $('#comunespedizione').html('');
                    $('#capspedizione').html('');
                    $('#comunespedizione').append(msg.msg).val(selezionata);
                }
            }
        });
    }
    /* set comune */
    function setCap(comune, selezionata, ident) {
        // ident = 1 indirizzo per fatturazione
        // ident = 2 indirizzo alternativo per spedizione
        $.ajax({
            type: "POST",
            url: "./form/form-clientifornitori.php",
            data: "comune=" + comune + "&submit=setcap",
            dataType: "json",
            success: function (msg)
            {
                if (ident === '1') {
                    $('#cap').html('');
                    $('#cap').append(msg.msg).val(selezionata);
                } else if (ident === '2') {
                    $('#capspedizione').html('');
                    $('#capspedizione').append(msg.msg).val(selezionata);
                }
            }
        });
    }
</script>
<script type="text/javascript" src="./js/functions-clientifornitori.js"></script>
<form method="post" action="" id="formutenti" name="formutenti">
    <input type="hidden" value="" id="tipo" name="tipo" />
    <!--<input type="text" name="codice" id="codice" class="input_moduli sizing float_moduli" placeholder="Codice" title="Codice" />-->  
    <!--<input type="checkbox" id="amministrazione" name="amministrazione" style="width:auto;" value="1"> Amministrazione-->
    <div class="chiudi"></div>    
    <input type="text" name="nome" id="nome" class="input_moduli sizing  float_moduli required" placeholder="Nome" title="Nome" />            
    <input type="text" name="cognome" id="cognome" class="input_moduli sizing  float_moduli required" placeholder="Cognome" title="Cognome" />       

<!--<input type="text" name="azienda" id="azienda" class="input_moduli sizing required float_moduli" placeholder="Azienda" title="Azienda" />-->
    <!--<input type="text" name="codicefiscale" id="codicefiscale" class="input_moduli sizing float_moduli" placeholder="Codice fiscale" title="Codice fiscale" />-->
    <select name="sesso" id="sesso" class="extra input_moduli float_moduli_small">
        <option value="">Seleziona Sesso</option>
        <option value="Maschio">Maschio</option>
        <option value="Femmina">Femmina</option>
    </select>
    <!--<input type="text" name="piva" id="piva" class="input_moduli sizing float_moduli" placeholder="P.Iva" title="P.Iva" />-->
    
    <input type="text" name="email" id="email" class="input_moduli sizing float_moduli" placeholder="Email" title="Email" />
    <!--<input type="password" name="password" id="password" class="input_moduli sizing extra" placeholder="<?php echo FORM_PASSWORD; ?>" title="<?php echo FORM_PASSWORD; ?>" />-->
    <input type="text" name="telefono" id="telefono" class="input_moduli sizing  float_moduli required" placeholder="Telefono" title="Telefono" />
    <!--<input type="text" name="cellulare" id="cellulare" class="input_moduli sizing  float_moduli" placeholder="Cellulare" title="Cellulare" />-->
    <div class="chiudi"></div>    
    <!--    <input type="text" name="indirizzo" id="indirizzo" class="input_moduli sizing  float_moduli" placeholder="Indirizzo" title="Indirizzo" />
    <input type="text" name="codice_sdi" id="codice_sdi" class="input_moduli sizing  float_moduli" placeholder="Codice SDI" title="Codice SDI" />
    <input type="text" name="pec" id="pec" class="input_moduli sizing  float_moduli" placeholder="PEC" title="PEC" />-->
    <div class="chiudi"></div>
    <!--<input type="checkbox" id="pa" name="pa" style="width:auto;" value="1"> Pubblica Amministrazione-->
    <div class="chiudi" style="height: 20px;"></div>
<!--    <select name="nazione" id="nazione" class="extra input_moduli float_moduli_small" onchange="setNazione($(this).val(), '1', 'Seleziona comune', 'Seleziona cap');">
        <?php echo $nazioniprint; ?>
    </select>
    <select name="regione" id="regione" class="extra input_moduli float_moduli_small" onchange="setProvincia($(this).val(), '', '1');">
        <?php echo $regioniprint; ?>
    </select>
    <select name="provincia" id="provincia" class="extra input_moduli float_moduli_small" onchange="setComune($(this).val(), '', '1');">
        <option value="">Seleziona provincia</option>
    </select>
    <select name="comune" id="comune" class="extra input_moduli float_moduli_small" onchange="setCap($(this).val(), '', '1');">
        <option value="">Seleziona comune</option>
    </select>
    <select name="cap" id="cap" class="extra input_moduli float_moduli_small">
        <option value="">Seleziona cap</option>
    </select>     -->
    <select name="provincia" id="provincia" class="input_moduli sizing float_moduli_small" onchange="selcomune(this.value);">
                        <?php
                        $pr = getDati("province", "GROUP BY sigla ORDER BY regione ");
                        echo "<option value=\"\">Seleziona Provincia</option>";
                        foreach ($pr as $prd) {
                            echo "<option value=\"" . $prd['sigla'] . "\">" . $prd['provincia'] . "</option>";
                        }
                        ?>
                    </select>
                    <select name="comune" id="comune" class="input_moduli sizing float_moduli_small">
                        <option value="">Seleziona Comune</option>                        
                    </select>
    <div class="chiudi"></div>    
    <!-- spedizione -->           
    <!--    <a href="javascript:;" onclick="$('#spedizione').slideToggle();"><i class="fa fa-plus-square-o fa-lg" aria-hidden="true"></i> Aggiungi indirizzo</a>
    <div id="spedizione" style="display: none;">        
        <div class="tit_big">INDIRIZZO SECONDARIO</div>
        <input type="text" name="nominativo" id="nominativo" class="input_moduli sizing float_moduli" placeholder="Nominativo" title="Nominativo" /> 
        <input type="text" name="indirizzospedizione" id="indirizzospedizione" class="input_moduli sizing float_moduli" placeholder="Indirizzo" title="Indirizzo" />
        <div class="chiudi"></div>
        <select name="nazionespedizione" id="nazionespedizione" class="extra input_moduli float_moduli_small" onchange="setNazione($(this).val(), '2', 'Seleziona comune', 'Seleziona cap');">
    <?php echo $nazioniprint; ?>
        </select>
        <select name="regionespedizione" id="regionespedizione" class="extra input_moduli float_moduli_small" onchange="setProvincia($(this).val(), '', '2');">
    <?php echo $regioniprint; ?>
        </select>
        <select name="provinciaspedizione" id="provinciaspedizione" class="extra input_moduli float_moduli_small" onchange="setComune($(this).val(), '', '2');">
            <option value="">Seleziona provincia</option>
        </select>
        <select name="comunespedizione" id="comunespedizione" class="extra input_moduli float_moduli_small" onchange="setCap($(this).val(), '', '2');">
            <option value="">Seleziona comune</option>
        </select>
        <select name="capspedizione" id="capspedizione" class="extra input_moduli float_moduli_small">
            <option value="">Seleziona cap</option>
        </select>  
        <div class="chiudi"></div>        
    </div>-->
    <!--    <div class="tit_big">PAGAMENTO</div>
        <select name="metodopagamento" id="metodopagamento" class="extra input_moduli float_moduli_small">
            <option value="0">Seleziona metodo di pagamento</option>
    <?php echo $metodopagamento; ?>
        </select>-->
    <!--    <input type="text" name="giornopagamento" id="giornopagamento" class="input_moduli sizing  float_moduli_small" placeholder="Giorno pagamento" title="Giorno pagamento" />
        <select name="fm_vf" id="fm_vf" class="extra input_moduli float_moduli_small">
            <option value="">Seleziona fine mese o data fattura</option>
            <option value="0">Fine mese</option>
            <option value="1">Data Fattura</option>
        </select>-->
    <div class="chiudi"></div>
    <!--    <div class="tit_big">COORDINATE BANCARIE</div>
        <textarea name="coordinate" id="coordinate" class="textarea_moduli sizing float_moduli" placeholder="Cordinate bancarie 1" title="Cordinate bancarie 1"></textarea>  
        <textarea name="coordinate1" id="coordinate1" class="textarea_moduli sizing float_moduli" placeholder="Coordinate Bancarie 2" title="Coordinate Bancarie 2"></textarea>
        <textarea name="coordinate2" id="coordinate2" class="textarea_moduli sizing float_moduli" placeholder="Coordinate Bancarie 3" title="Coordinate Bancarie 3"></textarea>
        <textarea name="coordinate3" id="coordinate3" class="textarea_moduli sizing float_moduli" placeholder="Coordinate Bancarie 4" title="Coordinate Bancarie 4"></textarea>
        <textarea name="coordinate4" id="coordinate4" class="textarea_moduli sizing float_moduli" placeholder="Coordinate Bancarie 5" title="Coordinate Bancarie 5"></textarea>
        <textarea name="coordinate5" id="coordinate5" class="textarea_moduli sizing float_moduli" placeholder="Coordinate Bancarie 6" title="Coordinate Bancarie 6"></textarea>
        <textarea name="coordinate6" id="coordinate6" class="textarea_moduli sizing float_moduli" placeholder="Coordinate Bancarie 7" title="Coordinate Bancarie 7"></textarea>
        <textarea name="coordinate7" id="coordinate7" class="textarea_moduli sizing float_moduli" placeholder="Coordinate Bancarie 8" title="Coordinate Bancarie 8"></textarea>
        <textarea name="coordinate8" id="coordinate8" class="textarea_moduli sizing float_moduli" placeholder="Coordinate Bancarie 9" title="Coordinate Bancarie 9"></textarea>
        <textarea name="coordinate9" id="coordinate9" class="textarea_moduli sizing float_moduli" placeholder="Coordinate Bancarie 10" title="Coordinate Bancarie 10"></textarea>-->



    <div class="chiudi"></div>
    <!-- -->
    <input type="hidden" name="id" id="id" />
    <input type="submit" id="submitformutenti" value="Salva" class="submit_form nopost" />
    <div class="bottone_chiudi sizing"><a href="javascript:;" class="sizing" onclick="mostraClientifornitori('');">Chiudi</a></div>
    <div class="chiudi"></div>
</form>