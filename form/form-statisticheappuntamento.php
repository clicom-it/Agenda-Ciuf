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
        $.datepicker.setDefaults($.datepicker.regional[ "it" ]);
        $("#datap").datepicker({
            altFormat: "yy-mm-dd",
            altField: "#data",
            changeYear: true,
            yearRange: "c-10:c+10"
        });

        $("#datap2").datepicker({
            altFormat: "yy-mm-dd",
            altField: "#data2",
            changeYear: true,
            yearRange: "c-10:c+10"
        });
        $("#formstatisticheconv").validate({
            submitHandler: function (form) {
                form.submit();
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
<div style="width: 100%;margin: 20px 0 50px;">
    <h3 style="font-size: 18px;margin: 0 0 20px 0;">Esportazione globale</h3>
    <form method="post" action="" id="formstatisticheconv" name="formstatisticheconv" target="_blank">
        <input type="text" name="datap" id="datap" onkeyup="puliscidata();" class="input_moduli sizing float_moduli_small nopost required" placeholder="Periodo dal" title="Periodo dal" /> 
        <input type="hidden" name="data" id="data" />
        <input type="text" name="datap2" id="datap2" onkeyup="puliscidata2();" class="input_moduli sizing float_moduli_small nopost required" placeholder="Periodo al" title="Periodo al" /> 
        <input type="hidden" name="data2" id="data2" />
        <div class="chiudi"></div>
        <div class="chiudi" style="height: 20px;"></div>
        <input type="submit" class="submit_form submit_form_10 nopost" value="Esporta" id="submitformstatisticheconv" />
        <input type="hidden" name="submit" value="statisticheconv_periodo" />
        <div class="chiudi" style="height: 20px;"></div>
    </form>    
</div>
<div class="graficostatistiche sizing">
    <form method="post" action="" id="statisticheanno" name="statisticheanno">
        <input type="text" name="anno" class="required input_moduli sizing float_moduli_small" placeholder="Anno per statistiche" title="Anno per statistiche" /> 
        <?php if ($_SESSION['livello'] == 1 || $_SESSION['livello'] == 0 || $_SESSION['ruolo'] == CENTRALINO) { ?>
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
        <a style="margin-left:10px; background-color: #cecece;display: none;color:#696969;text-align: center;line-height: 35px;" class="submit_form submit_form_10 nopost" id="btn-esporta" target="_blank">Esporta</a>
        <div class="chiudi" style="height: 20px;"></div>
    </form>
    <div class="chiudi"></div>
    <div id="graficoanno1"></div>
    <div class="chiudi"></div>
</div>
<div class="graficostatistiche sizing">
    <form method="post" action="" id="statisticheanno2" name="statisticheanno2">
        <input type="text" name="anno2" class="required input_moduli sizing float_moduli" placeholder="Anno per statistiche (confronto)" title="Anno per statistiche (confronto)" />
        <?php if ($_SESSION['livello'] == 1 || $_SESSION['livello'] == 0 || $_SESSION['ruolo'] == CENTRALINO) { ?>
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