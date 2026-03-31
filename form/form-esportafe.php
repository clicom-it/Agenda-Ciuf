<?php
include '../library/controllo.php';
include '../library/config.php';
include '../library/connessione.php';
include '../library/basic.class.php';
include '../library/functions.php';

?>
<script type="text/javascript">
    $(document).ready(function () {
        /* richiama clienti */
        /* data */
        $.datepicker.setDefaults($.datepicker.regional[ "it" ]);
        $("#datadal").datepicker({
            altFormat: "yy-mm-dd",
            altField: "#data1"
        });
        $("#dataal").datepicker({
            altFormat: "yy-mm-dd",
            altField: "#data2"
        });

    });
    function pulisciidcliente() {
        $('#cliente').val($('#cliente').val().trim());
        if ($('#cliente').val() === "") {
            $('#idcliente').val("");
        }
    }
    function puliscidata1() {
        $('#datadal').val($('#datadal').val().trim());
        if ($('#datadal').val() === "") {
            $('#data1').val("");
        }
    }
    function puliscidata2() {
        $('#dataal').val($('#dataal').val().trim());
        if ($('#dataal').val() === "") {
            $('#data2').val("");
        }
    }
</script>
<form method="post" action="" id="stampefatture" name="stampefatture">
    <input type="text" name="datadal" id="datadal" class="input_moduli sizing float_moduli_small_15" placeholder="Periodo dal" title="Periodo dal" onkeyup="puliscidata1();" />
    <input type="hidden" name="data1" id="data1" /> 
    <input type="text" name="dataal" id="dataal" class="input_moduli sizing float_moduli_small_15" placeholder="Periodo al" title="Periodo al" onkeyup="puliscidata2();" />
    <input type="hidden" name="data2" id="data2" /> 
    <div class="chiudi" style="height: 30px;"></div>
    <input type="submit" class="submit_form submit_form_10 nopost" value="Esporta" id="submitstampe" />        
    <div class="chiudi" style="height: 50px;"></div>
</form>
<div class="chiudi"></div>
<div id="graficoanno"></div>