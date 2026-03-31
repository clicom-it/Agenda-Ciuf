<?php
include '../library/controllo.php';
include '../library/config.php';
include '../library/connessione.php';
include '../library/basic.class.php';
include '../library/functions.php';
?>
<div class="graficostatistiche sizing">
    <form method="post" action="/dipendenti.php" id="form-file-dip" name="form-file-dip" target="_blank">
        <input type="text" name="datap" id="datap" onkeyup="puliscidata();" class="input_moduli sizing float_moduli_small required" placeholder="Periodo dal" title="Periodo dal" /> 
        <input type="hidden" name="data_dal" id="data_dal" />
        <input type="text" name="datap2" id="datap2" onkeyup="puliscidata2();" class="input_moduli sizing float_moduli_small" placeholder="Periodo al" title="Periodo al" /> 
        <input type="hidden" name="data_al" id="data_al" />
        <div class="chiudi" style="height: 20px;"></div>
        <select name="iduser[]" id="iduser" class="input_moduli sizing float_moduli_small" onchange="attdiraff(this.value);" placeholder="Seleziona Operatore" title="Seleziona Operatore" multiple>
            <option value="">Seleziona l'operatore</option>
            <?php
            $atelier = getOperatori("");
            foreach ($atelier as $atelierd) {
                echo "<option value=\"" . $atelierd['id'] . "\">" . $atelierd['cognome'] . " " . $atelierd['nome'] . "</option>";
            }
            ?>
        </select>
        <div class="chiudi" style="height: 20px;"></div>
        <input type="submit" class="submit_form submit_form_10 nopost" value="Invia" id="submitformfiledip" />
        <div class="chiudi" style="height: 20px;"></div>
        <input type="hidden" name="submit" value="getReportAppTot" />
    </form>
    <div class="chiudi"></div>
    <div id="graficoanno1"></div>
    <div class="chiudi"></div>
</div>
<script>
    $(function () {
        $('form').attr('autocomplete', 'off');
        $.datepicker.setDefaults($.datepicker.regional[ "it" ]);
        $("#datap").datepicker({
            altFormat: "yy-mm-dd",
            altField: "#data_dal",
            changeYear: true,
            yearRange: "c-10:c+10"
        });

        $("#datap2").datepicker({
            altFormat: "yy-mm-dd",
            altField: "#data_al",
            changeYear: true,
            yearRange: "c-10:c+10"
        });
        $.validator.messages.required = '';
        $("#form-file-dip").validate({
            submitHandler: function (form) {
                form.submit();
            }
        });
        $('select#iduser').select2({
            placeholder: 'Seleziona uno o piu\' operatori'
        });
    });
</script>