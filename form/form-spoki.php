<?php
include '../library/controllo.php';
include '../library/config.php';
include '../library/connessione.php';
include '../library/basic.class.php';
include '../library/functions.php';
?>

<script type="text/javascript" src="./js/functions-agenda.js"></script>
<script type="text/javascript">
    $(document).ready(function () {
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

<form method="post" action="" id="stampaappuntamenti" name="stampaappuntamenti">
    <input type="text" name="datap" id="datap" onkeyup="puliscidata();" class="input_moduli sizing float_moduli_small nopost" placeholder="Periodo dal" title="Periodo dal" /> 
    <input type="hidden" name="data" id="data" />
    <input type="text" name="datap2" id="datap2" onkeyup="puliscidata2();" class="input_moduli sizing float_moduli_small nopost" placeholder="Periodo al" title="Periodo al" /> 
    <input type="hidden" name="data2" id="data2" />
    <div class="chiudi"></div>
    <input type="submit" class="submit_form submit_form_10 nopost" value="Invia" id="submitstatisticheannoclienti" />
    <div class="chiudi" style="height: 20px;"></div>
</form>
<div class="chiudi"></div>
<div id="contieniapp" style="font-size: 1em;"></div>
<div class="chiudi"></div>

