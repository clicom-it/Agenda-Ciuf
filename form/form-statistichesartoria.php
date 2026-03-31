<?php
include '../library/controllo.php';
include '../library/config.php';
include '../library/connessione.php';
include '../library/basic.class.php';
include '../library/functions.php';

/* dati clienti */
$clienti = getDati("utenti", "WHERE livello = '5'");
?>

<script type="text/javascript" src="./js/functions-statistiche.js"></script>
<script type="text/javascript">
    $(document).ready(function () {
        $("#datap").datepicker({
            altFormat: "yy-mm-dd"
        });

        $("#datap2").datepicker({
            altFormat: "yy-mm-dd"
        });
        $('form').attr('autocomplete', 'off');
        /* richiama clienti */
        var daticlienti = <?php echo json_encode($clienti); ?>;
        var clienti = $.map(daticlienti, function (item) {
            return {
                label: item.nominativo,
                id: item.id

            };
        });
        $("#atelier").autocomplete({
            source: clienti,
            select: function (event, ui) {

                $('#idatelier').val(ui.item.id);
            }
        });

        $("#atelier2").autocomplete({
            source: clienti,
            select: function (event, ui) {

                $('#idatelier2').val(ui.item.id);
            }
        });
    });
    function pulisciidcliente() {
        $('#atelier').val($('#atelier').val().trim());
        if ($('#atelier').val() === "") {
            $('#idatelier').val("");
        }
    }

    function pulisciidcliente2() {
        $('#atelier2').val($('#atelier2').val().trim());
        if ($('#atelier2').val() === "") {
            $('#idatelier2').val("");
        }
    }
</script>
<div class="graficostatistiche sizing">
    <form method="post" action="" id="statisticheanno" name="statisticheanno">
        <input type="text" name="datap" id="datap" onkeyup="puliscidata();" class="input_moduli sizing float_moduli_small" placeholder="Periodo dal" title="Periodo dal" /> 
        <input type="text" name="datap2" id="datap2" onkeyup="puliscidata2();" class="input_moduli sizing float_moduli_small" placeholder="Periodo al" title="Periodo al" /> 
        <div class="chiudi"></div>
        <input type="text" name="anno" class="input_moduli sizing float_moduli_small" placeholder="Anno per statistiche" title="Anno per statistiche" /> 
        <?php if ($_SESSION['livello'] == 1 || $_SESSION['livello'] == 0) { ?>
            <input type="text" name="atelier" id="atelier" onkeyup="pulisciidcliente();" class="input_moduli sizing float_moduli_small required" placeholder="Atelier" title="Atelier" /> 
            <input type="hidden" name="idatelier" id="idatelier" value="" />
        <?php } elseif (count($_SESSION['atelier_collegati']) > 0) { ?>
            <select name="idatelier" id="idatelier" class="input_moduli float_moduli">
                <?php
                foreach ($_SESSION['atelier_collegati'] as $idatelier2) {
                    $atelier2 = getDati("utenti", "where id=$idatelier2 limit 1;");
                    ?>
                    <option value="<?= $idatelier2 ?>"><?= $atelier2[0]['nominativo'] ?></option>
                <?php } ?>
            </select>
        <?php } ?>
        <input type="submit" class="submit_form submit_form_10 nopost" value="Invia" id="submitstatisticheanno" />        
        <div class="chiudi" style="height: 20px;"></div>
    </form>
    <div class="chiudi"></div>
    <div id="graficoanno1"></div>
    <div class="chiudi"></div>
</div>
<div class="graficostatistiche sizing">
    <form method="post" action="" id="statisticheanno2" name="statisticheanno2">
        <input type="text" name="anno2" class="required input_moduli sizing float_moduli" placeholder="Anno per statistiche (confronto)" title="Anno per statistiche (confronto)" />
        <?php if ($_SESSION['livello'] == 1 || $_SESSION['livello'] == 0) { ?>
            <input type="text" name="atelier2" id="atelier2" onkeyup="pulisciidcliente2();" class="input_moduli sizing float_moduli_small required" placeholder="Atelier" title="Atelier" /> 
            <input type="hidden" name="idatelier2" id="idatelier2" value="" />
        <?php } elseif (count($_SESSION['atelier_collegati']) > 0) { ?>
            <select name="idatelier2" id="idatelier2" class="input_moduli float_moduli">
                <?php
                foreach ($_SESSION['atelier_collegati'] as $idatelier2) {
                    $atelier2 = getDati("utenti", "where id=$idatelier2 limit 1;");
                    ?>
                    <option value="<?= $idatelier2 ?>"><?= $atelier2[0]['nominativo'] ?></option>
                <?php } ?>
            </select>
        <?php } ?>
        <input type="submit" class="submit_form submit_form_10 nopost" value="Invia" id="submitstatisticheanno2" />
        <div class="chiudi" style="height: 20px;"></div>
    </form>
    <div class="chiudi"></div>
    <div id="graficoanno2"></div>
    <div class="chiudi"></div>
</div>
<div class="chiudi"></div>