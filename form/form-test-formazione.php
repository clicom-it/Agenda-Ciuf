<?php
include '../library/controllo.php';
include '../library/config.php';
include '../library/connessione.php';
include '../library/basic.class.php';
include '../library/functions.php';

$idformazione = $_GET['idformazione'];
$idlezione = $_GET['idlezione'];
if ($idlezione > 0) {
    $element = getDati("formazione_corsi", "where id=$idlezione limit 1;")[0];
} else {
    $element = getDati("formazione", "where id=$idformazione limit 1;")[0];
}
?>
<div class="tit_big"><?= $element['titolo'] ?></div>
<div class="tit_big_bordato">Domande Test</div>
<div class="dipendenti">
    <div class="sizing">
        <div style="font-size: 1.3em;margin:20px 0;"><a class="addCorso" href="javascript:;" onclick="$('#addDomanda').slideToggle('slow');"><i class="fa fa-plus-circle" aria-hidden="true"></i> Nuovo inserimento</a> <input type="checkbox" id="ordina" /> Ordina righe</div>
        <div id="addDomanda" style="display: none;">
            <form method="post" action="" id="form-domanda">
                <input type="text" name="titolo" id="titolo" class="input_moduli sizing float_moduli required" placeholder="Domanda" title="Domanda" value="" />
                <div class="chiudi"></div>
                <select id="tipo" name="tipo" class="input_moduli float_moduli_small">
                    <option value="0">Singola</option>
                    <option value="1">Multipla</option>
                    <option value="2">Aperta</option>
                </select>
                <div class="chiudi"></div>
                <input type="hidden" name="idformazione" id="idformazione" value="<?= $idformazione ?>" />
                <input type="hidden" name="idlezione" id="idlezione" value="<?= $idlezione ?>" />
                <input type="submit" id="submitformdomanda" value="Salva" class="submit_form nopost" />
                <div class="chiudi" style="height: 100px;"></div>
            </form>
        </div>
        <div id="tbDomande"></div>
    </div>
</div>
<div class="bottone_chiudi sizing"><a href="javascript:;" class="sizing" onclick="<?= ($idlezione > 0 ? 'corsiFormazione(' . $idformazione . ');' : 'mostraFormazione();') ?>">Chiudi</a></div>
<script>
            $(function () {
                $('#ordina').unbind('click').click(function () {
                    if ($(this).is(':checked')) {
                        $("#tbDomande").jsGrid('option', 'editing', false);
                        $('#tbDomande .jsgrid-control-field').hide();
                    } else {
                        testFormazione(<?= $idformazione ?>, <?= $idlezione ?>);
                    }
                });
                if ($('#tbDomande').length > 0) {
                    mostraDomande(<?= $idformazione ?>, <?= $idlezione ?>);
                }
                $.validator.messages.required = '';
                $("#form-domanda").validate({
                    submitHandler: function () {
                        $("#submitformdomanda").ready(function () {
                            var datastring = $("#form-domanda *").not(".nopost").serialize();
                            $.ajax({
                                type: "POST",
                                url: "./formazione.php",
                                data: datastring + "&submit=addDomanda",
                                dataType: "json",
                                success: function (msg) {
                                    if (msg.msg === "ko") {
                                        alert(msg.msgko);
                                    } else {
                                        $('#form-domanda').trigger('reset');
                                        $('#messaggio').slideToggle('fast').delay(2000).slideToggle('slow');
                                        mostraDomande(<?= $idformazione ?>, <?= $idlezione ?>);
                                        $('#addDomanda').slideToggle('slow');
                                    }
                                }
                            });
                        });
                    }
                });
            });
</script>