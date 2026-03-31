<?php
include '../library/controllo.php';
include '../library/config.php';
include '../library/connessione.php';
include '../library/basic.class.php';
include '../library/functions.php';
?>
<link type="text/css" rel="stylesheet" href="./js/jsgrid-1.5.1/jsgrid.min.css" />
<link type="text/css" rel="stylesheet" href="./js/jsgrid-1.5.1/jsgrid-theme.min.css" />

<script type="text/javascript" src="./js/jsgrid-1.5.1/jsgrid.min.js"></script>
<script type="text/javascript" src="./js/jsgrid-1.5.1/i18n/jsgrid-it.js"></script>
<script type="text/javascript" src="./js/functions-statistiche.js"></script>
<script type="text/javascript">
    $(document).ready(function () {
        $('form').attr('autocomplete', 'off');

        $.datepicker.setDefaults($.datepicker.regional[ "it" ]);
        $("#datap").datepicker({
            altFormat: "yy-mm-dd",
            altField: "#data"
        });

        $("#datap2").datepicker({
            altFormat: "yy-mm-dd",
            altField: "#data2"
        });
    });
</script>

<form method="post" action="" id="formstatisticheadmin" name="formstatisticheadmin">
    <?php
    if ($_SESSION["livello"] == 0 || $_SESSION["livello"] == 1) {
        ?>
        <select name="idatelier" id="idatelier" class="input_moduli sizing float_moduli_small" onchange="attdiraff(this.value);" placeholder="Seleziona Atelier" title="Seleziona Atelier">
            <option value="">Seleziona Atelier</option>
            <?php
            $atelier = getAtelier("");
            foreach ($atelier as $atelierd) {
                echo "<option value=\"" . $atelierd['id'] . "\">" . $atelierd['nominativo'] . "</option>";
            }
            ?>
        </select>
        <?php
    } else if ($_SESSION["livello"] == 5) {
        ?>
        <!--<select name="idatelier" id="idatelier" class="input_moduli sizing float_moduli_small" onchange="attdiraff(this.value);" placeholder="Seleziona Atelier" title="Seleziona Atelier">-->

        <?php
        $and = "AND id = " . $_SESSION['id'] . "";

        $atelier = getAtelier($and);

        foreach ($atelier as $atelierd) {
//                echo "<option value=\"" . $atelierd['id'] . "\">" . $atelierd['nominativo'] . "</option>";
            echo "<input type=\"hidden\" name=\"idatelier\" id=\"idatelier\" value=\"" . $atelierd['id'] . "\">";
        }
        ?>
        <!--</select>-->
        <?php
    }
    ?>    
    <?php
    if ($_SESSION["livello"] == 0 || $_SESSION["livello"] == 1) {
        ?>
        <select name="diraff" id="diraff" class="input_moduli sizing float_moduli_small" onchange="attidat(this.value);" placeholder="Seleziona Diretti o Affiliati" title="Seleziona Diretti o Affiliati">
            <option value="">Seleziona Diretti o Affiliati</option>
            <option value="d">Diretti</option>
            <option value="a">Affiliati</option>
        </select>
        <?php
    }
    ?>
    <div class="chiudi"></div>
    <input type="text" name="datap" id="datap" onkeyup="puliscidata();" class="input_moduli sizing float_moduli_small nopost" placeholder="Periodo dal" title="Periodo dal" /> 
    <input type="hidden" name="data" id="data" />
    <input type="text" name="datap2" id="datap2" onkeyup="puliscidata2();" class="input_moduli sizing float_moduli_small nopost" placeholder="Periodo al" title="Periodo al" /> 
    <input type="hidden" name="data2" id="data2" />
    <div class="chiudi"></div>
    <input type="submit" class="submit_form submit_form_10 nopost" value="Invia" id="submitformstatisticheadmin" />
    <input type="reset" style="margin-left:10px; background-color: #f89406;" class="submit_form submit_form_10 nopost" value="Reset" onclick="location.href = '/statistiche.php?op=admin';" />
    <div class="chiudi" style="height: 20px;"></div>
</form>
<div class="chiudi"></div>
<div id="contienistat"></div> <!-- appuntamenti -->
<div id="contienistat8"></div> <!-- dipendenti -->
<div id="contienistat5"></div> <!-- provenienza -->
<div id="contienistat6"></div> <!-- motivo no acquisto -->
<div id="contienistat2"></div> <!-- abiti -->
<div id="contienistat7"></div> <!-- modelli abiti -->
<div id="contienistat9"></div> <!-- bollini -->
<div id="contienistat3"></div> <!-- accessori -->
<div id="contienistat4"></div> <!-- sartoria -->
<div class="chiudi"></div>

