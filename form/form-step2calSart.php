<?php
include '../library/controllo.php';
include '../library/config.php';
include '../library/connessione.php';
include '../library/basic.class.php';
include '../library/functions.php';

$checkstampe = $_GET['stampe'];

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

    case "selmodelloabito":
        $idp = $_POST['idp'];
        $idmodabito = $_POST['idmodabito'];
        $modello = getDati("profit_center", "WHERE idp = $idp ORDER BY id ");
        if ($modello) {

            $modelloabito = "<option value=\"\">Seleziona Modello di Abito</option>";
            foreach ($modello as $modellod) {
                $selected = "";
                if ($modellod['id'] == $idmodabito) {
                    $selected = "selected";
                }
                $modelloabito .= "<option value=\"" . $modellod['id'] . "\" $selected>" . $modellod['nome'] . "</option>";
            }
        } else {
            $modelloabito = "<option value=\"\">Seleziona Modello di Abito</option>";
        }

        die('{"dati" : ' . json_encode($modelloabito) . '}');

        break;

    case "sartoria":
        $idappuntamento = $_POST['idappuntamento'];
        $tipoabito = $_POST['tipoabito'];
        /*
         * 24 sposa, 25 sposo, 26 cerimonia
         * 1 sartoria sposa, 2 sartoria sposo, 3 sartoria cerimonia
         *  
         */
        if ($tipoabito == 24) {
            $idp = 1;
        } else if ($tipoabito == 25) {
            $idp = 2;
        } else if ($tipoabito == 26) {
            $idp = 3;
        }

        $dati = "<div class=\"tit_big\">SARTORIA</div>";
        $sartoria = getDati("sartoria", "WHERE id = $idp");
        foreach ($sartoria as $sartoria_d) {
            $idpsart = $sartoria_d['id'];
            $dati .= "<div class=\"titcatacc sizing\">{$sartoria_d['nome']}</div>";
            $accsartoria = getDati("sartoria", "WHERE idp = $idpsart");
            foreach ($accsartoria as $accsartoria_d) {
                $valueprezzo = "";
                $valuecosto = "";
                $valuenote = "";
                $idsartoria = $accsartoria_d['id'];
                $valorisartoriaapp = getDati("sartoria_appuntamento", "WHERE idappuntamento = '$idappuntamento' AND idsartoria = '$idsartoria' LIMIT 1");

                if ($valorisartoriaapp) {
                    $valueprezzo = $valorisartoriaapp[0]['prezzosartoria'];
                    $valuecosto = $valorisartoriaapp[0]['costosartoria'];
                    $valuenote = $valorisartoriaapp[0]['notesartoria'];
                }


                $dati .= "<div class=\"nomesart sizing\">{$accsartoria_d['nome']}</div> "
                        . "<input type=\"text\" name=\"sartid_[{$accsartoria_d['id']}]\" id=\"sartid_{$accsartoria_d['id']}\" value=\"$valueprezzo\" class=\"prezzosart input_moduli sizing float_moduli_small_15\" placeholder=\"Prezzo\" title=\"Prezzo\" onkeyup=\"saldoAcquisto();\" />"
                        . "<input type=\"text\" name=\"sartcosto_[{$accsartoria_d['id']}]\" id=\"sartcosto_{$accsartoria_d['id']}\" value=\"$valuecosto\" class=\"input_moduli sizing float_moduli_small_15\" placeholder=\"Costo\" title=\"Costo\" />"
                        . "<input type=\"text\" name=\"sartnote_[{$accsartoria_d['id']}]\" id=\"sartnote_{$accsartoria_d['id']}\" value=\"$valuenote\" class=\"input_moduli sizing float_moduli_45\" placeholder=\"Note\" title=\"Note\" />"
                        . "<div class=\"chiudi\"></div>";
            }
        }
        $dati .= "<div class=\"\"></div>";

        die('{"dati" : ' . json_encode($dati) . '}');

        break;
}
?>
<script type="text/javascript">
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

    $('#caparra').unbind('keyup').on('keyup', function () {
        this.value = this.value.replace(/\,/g, '.');
    });

    $('.prezzoacc, .prezzosart').unbind('keyup').on('keyup', function () {
        this.value = this.value.replace(/\,/g, '.');
    });

    function saldoAcquisto() {

        var totaleacc = 0;
        $('.prezzoacc').each(function () {

            if (!isNaN(this.value) && (this.value !== '')) {
                totaleacc += parseFloat(this.value);
            } else {
                totaleacc += 0;
            }
        });

        var totalesartoria = 0;
        $('.prezzosart').each(function () {

            if (!isNaN(this.value) && (this.value !== '')) {
                totalesartoria += parseFloat(this.value);
                $(this).next('input').addClass("required");
            } else {
                totalesartoria += 0;
            }
        });

//        if ($('#prezzoabito').val() === "") {
//            $('#prezzoabito').val("0");
//        }


        var prezzoabito = parseFloat($('#prezzoabito').val());

        if (!isNaN($('#prezzoabito').val()) && ($('#prezzoabito').val() !== '')) {
            prezzoabito = parseFloat($('#prezzoabito').val());
        } else {
            prezzoabito = 0;
        }


        var totalespesa = parseFloat(prezzoabito + totaleacc + totalesartoria);

//        if ($('#caparra').val() === "") {
//            $('#caparra').val("0");
//        }
//        if ($('#pag1').val() === "") {
//            $('#pag1').val("0");
//        }
//        if ($('#pag2').val() === "") {
//            $('#pag2').val("0");
//        }
//        if ($('#pag3').val() === "") {
//            $('#pag3').val("0");
//        }

//        var caparra = parseFloat($('#caparra').val());

        if (!isNaN($('#caparra').val()) && ($('#caparra').val() !== '')) {
            caparra = parseFloat($('#caparra').val());
        } else {
            caparra = 0;
        }

        if (!isNaN($('#pag1').val()) && ($('#pag1').val() !== '')) {
            pag1 = parseFloat($('#pag1').val());
        } else {
            pag1 = 0;
        }

        if (!isNaN($('#pag2').val()) && ($('#pag2').val() !== '')) {
            pag2 = parseFloat($('#pag2').val());
        } else {
            pag2 = 0;
        }

        if (!isNaN($('#pag3').val()) && ($('#pag3').val() !== '')) {
            pag3 = parseFloat($('#pag3').val());
        } else {
            pag3 = 0;
        }

        if ((caparra + pag1 + pag2 + pag3) > totalespesa) {
            alert("Importi pagamenti errati, maggiori del totale spesa, controlla!");
            saldoval = 0.00;
            return false;
        }

        $('#totalespesa').val(totalespesa);
        if (totalespesa > (caparra + pag1 + pag2 + pag3)) {
            var saldoval = parseFloat(totalespesa - (caparra + pag1 + pag2 + pag3));
        } else {
            saldoval = 0.00;
        }
        $('#saldo').val(roundTo(saldoval, 2));
    }



    function checksartoria(id) {
        var idappuntamento = $('#id').val();
        var tipoabito = $('#idtipoabito').val();
        $.ajax({
            type: "POST",
            url: "./form/form-step2calSart.php",
            data: "tipoabito=" + tipoabito + "&idappuntamento=" + idappuntamento + "&submit=sartoria",
            dataType: "json",
            success: function (msg)
            {
                $('.box_sartoria').show();
                $('#contienisartoria').html(msg.dati);
                setTimeout(function () {
                    saldoAcquisto();
                }, 500);
            }
        });
    }


    function selModabito(idp, idmodabito) {
        $.ajax({
            type: "POST",
            url: "./form/form-step2calSart.php",
            data: "idp=" + idp + "&idmodabito=" + idmodabito + "&submit=selmodelloabito",
            dataType: "json",
            success: function (msg) {
                if (msg.msg === "ko") {
                    alert(msg.msgko);
                } else {
                    $('#idmodabito').html(msg.dati);

                }
            }
        });
    }

    function puliscisartoria() {
        $('#sartoria').val("");
        $('.box_sartoria').hide();
        $('#contienisartoria').html("");
    }


</script>
<script type="text/javascript" src="./js/functions-calendario.js"></script>
<form method="post" action="" id="formstep2cal" name="formstep2cal" style="font-size: 0.9em;">   
    <div class="float_primibox float_lft sizing">
        <div class="boxsx sizing">
            <div class="tit_big">DATI APPUNTAMENTO</div>        
            <input type="text" name="datap" id="datap" class="input_moduli sizing  float_moduli_50 required nopost" placeholder="Data appuntamento" title="Data appuntamento" />  
            <input type="hidden" name="data" id="data" /> 
            <input type="text" name="orario" id="orario" class="timepicker input_moduli sizing float_moduli_50 required" placeholder="Orario" title="Orario" />  
            <div class="chiudi"></div>
            <div class="nomeacc sizing">Matrimonio:</div>
            <input type="text" name="datamatrimoniop" id="datamatrimoniop"  onkeyup="pulisciDatas();" class="input_moduli sizing float_moduli_small nopost" placeholder="Data Matrimonio" title="Data Matrimonio" />   
            <input type="hidden" name="datamatrimonio" id="datamatrimonio" />     
            <div class="chiudi"></div>
            <div class="nomeacc sizing">Numero contratto:</div>
            <input type="text" name="numero_contratto" id="numero_contratto" class="input_moduli sizing float_moduli_small nopost" placeholder="Numero contratto" title="Numero contratto" />   
            <div class="chiudi"></div>
            <textarea name="note" id="note" class="textarea_moduli_small sizing" title="Note" placeholder="Note"></textarea>
            <div class="chiudi"></div>
            <select name="idtipoabito" id="idtipoabito" onchange="selModabito(this.value, ''), checksartoria(1);" class="input_moduli sizing float_moduli_small_25" placeholder="Tipo di abito" title="Tipo di abito">            
                <?php
                $abito = getDati("profit_center", "WHERE idp = 0 ORDER BY id ");
                echo "<option value=\"\">Seleziona Tipo di Abito</option>";
                foreach ($abito as $abitod) {
                    echo "<option value=\"" . $abitod['id'] . "\">" . $abitod['nome'] . "</option>";
                }
                ?>
            </select>
            <select name="idmodabito" id="idmodabito"  class="input_moduli sizing float_moduli_small_25" placeholder="Modello di abito" title="Modello di abito"> 
                <?php
                echo "<option value=\"\">Seleziona Modello di Abito</option>";
                ?>
            </select>
            <input type="hidden" name="acquistato" id="acquistato" value="0" />
            <input type="hidden" name="sartoria" id="sartoria" value="1" />
        </div>
    </div>
    <div class="float_primibox float_rgt sizing">
        <div class="boxdx sizing">
            <div class="tit_big">DATI CLIENTE</div>
            <input type="hidden" name="idcliente" id="idcliente" />
            <input type="text" name="nome" id="nome" class="input_moduli sizing  float_moduli required" placeholder="Nome" title="Nome" />            
            <input type="text" name="cognome" id="cognome" class="input_moduli sizing  float_moduli required" placeholder="Cognome" title="Cognome" />       

<!--            <select name="sesso" id="sesso" class="extra input_moduli float_moduli">
                <option value="">Seleziona Sesso</option>
                <option value="Maschio">Maschio</option>
                <option value="Femmina">Femmina</option>
            </select>-->
            <select name="tipoappuntamento" id="tipoappuntamento" class=" required input_moduli sizing float_moduli_small_25">
                <option value="5">Sartoria</option>
            </select>

            <input type="text" name="email" id="email" class="input_moduli sizing float_moduli_small_25" placeholder="Email" title="Email" />
            <input type="text" name="telefono" id="telefono" class="input_moduli sizing  float_moduli_small_25 required" placeholder="Telefono" title="Telefono" />
            <select name="provincia" id="provincia" class="input_moduli sizing float_moduli_small_25" onchange="selcomune(this.value);">
                <?php
                $pr = getDati("province", "GROUP BY sigla ORDER BY regione ");
                echo "<option value=\"\">Seleziona Provincia</option>";
                foreach ($pr as $prd) {
                    echo "<option value=\"" . $prd['sigla'] . "\">" . $prd['provincia'] . "</option>";
                }
                ?>
            </select>
            <select name="comune" id="comune" class="input_moduli sizing float_moduli_small_25">
                <option value="">Seleziona Comune</option>                        
            </select>
        </div>
        <div class="chiudi" style="height: 20px;"></div> 
    </div>
    <div class="chiudi"></div>
    <div class="app_passato sizing">    <!-- controllo prima o dopo appuntamento -->
        <div class="tit_big_bordato sizing">DATI POST APPUNTAMENTO</div>
        <div class="float_primibox float_lft sizing">
            <div class="boxsx sizing">
                <div class="tit_big">APPUNTAMENTI SARTORIA</div> 
                <input type="text" name="datas1" id="datas1" class="input_moduli sizing  float_moduli nopost" onkeyup="pulisciDatas();" placeholder="Data appuntamento 1 sartoria" title="Data appuntamento 1 sartoria" />  
                <input type="hidden" name="datasart1" id="datasart1" />
                <input type="text" name="orariosart1" id="orariosart1" class="timepicker input_moduli sizing float_moduli" placeholder="Orario 1 sartoria" title="Orario 1 sartoria" /> 
                <div class="chiudi" style="height: 10px"></div>
                <input type="text" name="datas2" id="datas2" class="input_moduli sizing  float_moduli nopost" onkeyup="pulisciDatas();" placeholder="Data appuntamento 2 sartoria" title="Data appuntamento 2 sartoria" />  
                <input type="hidden" name="datasart2" id="datasart2" />
                <input type="text" name="orariosart2" id="orariosart2" class="timepicker input_moduli sizing float_moduli" placeholder="Orario 2 sartoria" title="Orario 2 sartoria" /> 
                <div class="chiudi" style="height: 10px"></div>
                <input type="text" name="datas3" id="datas3" class="input_moduli sizing  float_moduli nopost" onkeyup="pulisciDatas();" placeholder="Data appuntamento 3 sartoria" title="Data appuntamento 3 sartoria" />  
                <input type="hidden" name="datasart3" id="datasart3" />
                <input type="text" name="orariosart3" id="orariosart3" class="timepicker input_moduli sizing float_moduli" placeholder="Orario 3 sartoria" title="Orario 3 sartoria" /> 
            </div>
        </div>
        <div class="float_primibox float_rgt sizing">
            <div class="boxdx sizing">
                <div class="tit_big">DATI PAGAMENTO</div>
                <div class="chiudi" style="height: 10px"></div>
                Caparra:
                <div class="chiudi" style="height: 10px"></div>
                <input type="text" name="caparra" id="caparra" class="input_moduli sizing float_moduli" placeholder="Caparra" title="Caparra" onkeyup="saldoAcquisto();" />  
                <select name="idtipopagcaparra" id="idtipopagcaparra" class="input_moduli sizing float_moduli" placeholder="Metodo pagamento caparra" title="Metodo pagamento caparra">
                    <option value="">Seleziona metodo pagamento caparra</option>
                    <?php echo $metodopagamento; ?>
                </select>
                <input type="text" name="datac" id="datac" class="input_moduli sizing  float_moduli nopost" onkeyup="pulisciDatas();" placeholder="Data caparra" title="Data caparra" />  
                <input type="hidden" name="datacap" id="datacap" />
                <div class="chiudi" style="height: 10px"></div>
                Pagamento 1:
                <div class="chiudi" style="height: 10px"></div>
                <input type="text" name="pag1" id="pag1" class="input_moduli sizing float_moduli" placeholder="Pagamento 1" title="Pagamento 1" onkeyup="saldoAcquisto();" />  
                <select name="idpag1" id="idpag1" class="input_moduli sizing float_moduli" placeholder="Metodo pagamento 1" title="Metodo pagamento 1">
                    <option value="">Seleziona metodo pagamento 1</option>
                    <?php echo $metodopagamento; ?>
                </select>
                <input type="text" name="datap1" id="datap1" class="input_moduli sizing  float_moduli nopost" onkeyup="pulisciDatas();" placeholder="Data pagamento 1" title="Data pagamento 1" />  
                <input type="hidden" name="datapag1" id="datapag1" />
                <div class="chiudi" style="height: 10px"></div>
                Pagamento 2:
                <div class="chiudi" style="height: 10px"></div>
                <input type="text" name="pag2" id="pag2" class="input_moduli sizing float_moduli" placeholder="Pagamento 2" title="Pagamento 2" onkeyup="saldoAcquisto();" />  
                <select name="idpag2" id="idpag2" class="input_moduli sizing float_moduli" placeholder="Metodo pagamento 2" title="Metodo pagamento 2">
                    <option value="">Seleziona metodo pagamento 2</option>
                    <?php echo $metodopagamento; ?>
                </select>
                <input type="text" name="datap2" id="datap2" class="input_moduli sizing  float_moduli nopost" onkeyup="pulisciDatas();" placeholder="Data pagamento 2" title="Data pagamento 2" />  
                <input type="hidden" name="datapag2" id="datapag2" />
                <div class="chiudi" style="height: 10px"></div>
                Pagamento 3:
                <div class="chiudi" style="height: 10px"></div>
                <input type="text" name="pag3" id="pag3" class="input_moduli sizing float_moduli" placeholder="Pagamento 3" title="Pagamento 3" onkeyup="saldoAcquisto();" />  
                <select name="idpag3" id="idpag3" class="input_moduli sizing float_moduli" placeholder="Metodo pagamento 3" title="Metodo pagamento 3">
                    <option value="">Seleziona metodo pagamento 3</option>
                    <?php echo $metodopagamento; ?>
                </select>
                <input type="text" name="datap3" id="datap3" class="input_moduli sizing  float_moduli nopost" onkeyup="pulisciDatas();" placeholder="Data pagamento 3" title="Data pagamento 3" />  
                <input type="hidden" name="datapag3" id="datapag3" />
                <div class="chiudi" style="height: 10px; border-top: 2px solid #696969;"></div>
                <strong>Saldo:</strong>
                <div class="chiudi" style="height: 10px"></div>
                <input type="text" name="saldo" id="saldo" class="input_moduli sizing float_moduli" placeholder="Saldo" title="Saldo" readonly />  
                <select name="idtipopagsaldo" id="idtipopagsaldo" class="input_moduli sizing float_moduli" placeholder="Metodo pagamento Saldo" title="Metodo pagamento Saldo">
                    <option value="">Seleziona metodo pagamento saldo</option>
                    <?php echo $metodopagamento; ?>
                </select>     
                <input type="text" name="dataes" id="dataes" class="input_moduli sizing  float_moduli nopost" onkeyup="pulisciDatas();" placeholder="Data saldo" title="Data saldo" />  
                <input type="hidden" name="dataeffettuatosaldo" id="dataeffettuatosaldo" />
                <div class="chiudi" style="height: 10px; border-top: 2px solid #696969;"></div>
                <strong>Totale Spesa:</strong>
                <div class="chiudi" style="height: 10px"></div>
                <input type="text" name="totalespesa" id="totalespesa" class="input_moduli sizing float_moduli" placeholder="Totale spesa" title="Totale spesa" readonly style="border:2px solid green" /> 
                <input type="text" name="datas" id="datas" class="input_moduli sizing  float_moduli nopost" onkeyup="pulisciDatas();" placeholder="Data del saldo/ritiro" title="Data del saldo/ritiro" />  
                <input type="hidden" name="datasaldo" id="datasaldo" />
                <input type="text" name="orariosaldo" id="orariosaldo" class="timepicker input_moduli sizing float_moduli" placeholder="Orario saldo/ritiro" title="Orario saldo/ritiro" /> 
            </div>
        </div>
        <div class="float_primibox float_lft sizing" style="margin: 10px 0 0 0;">
            <div class="box_sartoria sizing" style="width:100%;">
                <div id="contienisartoria">
                    <div class="chiudi"></div>
                </div>
            </div>
        </div>
        <div class="chiudi" style="height: 20px;"></div>     
    </div>
    <!-- -->
    <input type="hidden" name="id" id="id" />
    <input type="submit" id="submitformstep2cal" value="Salva" class="submit_form nopost" />
    <?php if ($checkstampe > 0) { ?>
        <div class="bottone_chiudi sizing"><a class="sizing" href="mrgest.php?op=stampe">Chiudi</a></div>
    <?php } else { ?>
        <div class="bottone_chiudi sizing"><a class="sizing" href="mrgest.php">Chiudi</a></div>
    <?php } ?>
    <div class="bottone_chiudi sizing" id="mostrapdf"></div>
    <div class="messaggiook" id="messaggiobottom" style="float: left; margin-top: 10px !important; margin-left: 10px !important; height: 35px; line-height: 35px;">Operazione avvenuta con successo</div>
    <div class="chiudi"></div>

    <div class="chiudi"></div>
</form>