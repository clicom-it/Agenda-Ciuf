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
        /* richiama clienti */
        richiamaclienti();
        $('#cliente').autocomplete({
            minLength: 3,
            source: function (request, response) {
                var idatelier__ = 0;
                var solo_sartoria__ = 0;
                if ($('#idatelier').length > 0) {
                    if ($('#idatelier').val() > 0) {
                        idatelier__ = $('#idatelier').val();
                        solo_sartoria__ = $('#idatelier option:selected').data('solo_sartoria');
                    } else {
                        idatelier__ = <?= ($_SESSION['livello'] == 0 || $_SESSION['livello'] == 1 ? 0 : $_SESSION['id']) ?>;
                        solo_sartoria__ = <?= $_SESSION['solo_sartoria'] ?>;
                    }
                } else {
                    idatelier__ = <?= ($_SESSION['livello'] == 0 || $_SESSION['livello'] == 1 ? 0 : $_SESSION['id']) ?>;
                    solo_sartoria__ = <?= $_SESSION['solo_sartoria'] ?>;
                }

                $.ajax({
                    type: "POST",
                    url: 'mrgest.php',
                    dataType: "jsonp",
                    data: {
                        q: request.term,
                        idatelier: idatelier__,
                        solo_sartoria: solo_sartoria__,
                        submit: 'cercaClienti'
                    },
                    success: function (data) {
                        //console.log(data);
                        response($.map(data, function (item) {
                            return {
                                label: item.cognome + ' ' + item.nome,
                                value: item.id,
                                nome: item.nome,
                                cognome: item.cognome,
                                sesso: item.sesso,
                                provincia: item.provincia,
                                comune: item.comune,
                                telefono: item.telefono,
                                email: item.email
                            }
                        }));
                    }
                });
            },
            //source: arrCat,
            select: function (event, ui) {
                event.preventDefault();
                //console.log(ui.item);
                $('#stampaappuntamenti #idcliente').val(ui.item.value);
                $('#stampaappuntamenti #cliente').val(ui.item.cognome + ' ' +ui.item.nome);
            }
        });
    });
</script>

<form method="post" action="" id="stampaappuntamenti" name="stampaappuntamenti">
    <?php
    if ($_SESSION["livello"] == 0 || $_SESSION["livello"] == 1 || $_SESSION["livello"] == 2 || $_SESSION["ruolo"] == CENTRALINO) {
        ?>
        <!--<div class="nomeacc sizing" style="width: auto; font-weight: bolder;">Atelier:</div>-->
        <select name="idatelier" id="idatelier" class="input_moduli sizing float_moduli_small required" placeholder="Seleziona Atelier" title="Seleziona Atelier">
            <?php
            if ($_SESSION['livello'] == '5') {
                $and = "AND id = " . $_SESSION['id'] . "";
            }
            $atelier = getAtelier($and);
            if ($_SESSION["livello"] == 0 || $_SESSION["livello"] == 1) {
                echo "<option value=\"\">Seleziona Atelier</option>";
            }
            foreach ($atelier as $atelierd) {
                echo "<option value=\"" . $atelierd['id'] . "\" data-solo_sartoria=\"" . $atelierd['solo_sartoria'] . "\">" . $atelierd['nominativo'] . "</option>";
            }
            ?>
        </select>

        <?php
    } else {
         if (count($_SESSION['atelier_collegati']) > 0) { ?>
            <select name="idatelier" id="idatelier" class="input_moduli float_moduli">
                <?php
                foreach ($_SESSION['atelier_collegati'] as $idatelier2) {
                    $atelier2 = getDati("utenti", "where id=$idatelier2 limit 1;");
                    ?>
                    <option value="<?= $idatelier2 ?>"><?= $atelier2[0]['nominativo'] ?></option>
                <?php } ?>
            </select>
        <?php
        } else {
        $and = "AND id = " . $_SESSION['idatelier'] . "";
        $atelier = getAtelier($and);
        echo "<input type=\"hidden\" value=\"" . $atelier[0]['id'] . "\" id=\"idatelier\" name=\"idatelier\" />";
        }
    }
    ?>
    <input type="text" name="cliente" id="cliente" class="nopost input_moduli sizing float_moduli_small" placeholder="Cliente" title="Cliente" /> 
    <input type="hidden" name="idcliente" id="idcliente" value="" />
    <div class="chiudi"></div>
    <input type="text" name="datap" id="datap" onkeyup="puliscidata();" class="input_moduli sizing float_moduli_small nopost" placeholder="Appuntamenti dal" title="Appuntamenti dal" /> 
    <input type="hidden" name="data" id="data" />
    <input type="text" name="datap2" id="datap2" onkeyup="puliscidata2();" class="input_moduli sizing float_moduli_small nopost" placeholder="Appuntamenti al" title="Appuntamenti al" /> 
    <input type="hidden" name="data2" id="data2" />
    <div class="chiudi"></div>
    <input type="submit" class="submit_form submit_form_10 nopost" value="Invia" id="submitstatisticheannoclienti" />
    <a href="/mrgest.php?submit=esporta_all" target="_blank" class="submit_form submit_form_10 nopost button-esporta">Esporta tutto</a>
    <a href="#" target="_blank" class="submit_form submit_form_10 nopost button-esporta" id="esporta_sel">Esporta selezione</a>
    <!--<div style="float: left; padding-left: 20px; padding-top: 10px; height: 15px;"><a href="javascript:;" onclick="javascript:printDiv('contieniapp');"><i class="fa fa-print fa-lg" aria-hidden="true"></i> stampa selezione</a></div>-->
    <div class="chiudi" style="height: 20px;"></div>
</form>
<div class="chiudi"></div>
<div id="contieniapp" style="font-size: 1em;"></div>
<div class="chiudi"></div>

