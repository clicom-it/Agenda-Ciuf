<?php
include '../library/controllo.php';
include '../library/config.php';
include '../library/connessione.php';
include '../library/basic.class.php';
include '../library/functions.php';


/* dati clienti */
$clienti = getDati("clienti_fornitori", "WHERE tipo = 1");
/* centri di costo */
$cdc = getDati("centri_costo", "");
foreach ($cdc as $cdcd) {
    $cdcselect .= "<option value=\"" . $cdcd['id'] . "\">" . $cdcd['nome'] . "</option>";
}
/* profit center */
$profit = getDati("profit_center", "WHERE idp = 0");
foreach ($profit as $profitd) {
    $profitselect .= "<option value=\"" . $profitd['id'] . "\">" . $profitd['nome'] . "</option>";
}

?>

<script type="text/javascript" src="./js/functions-statistiche.js"></script>
<script type="text/javascript">
    $(document).ready(function () {
        /* richiama clienti */
        var daticlienti = <?php echo json_encode($clienti); ?>;
        var clienti = $.map(daticlienti, function (item) {
            return {
                label: item.cognome + " " + item.nome + " " + item.azienda,
                id: item.id,
                nome: item.nome,
                cognome: item.cognome,
                azienda: item.azienda,
                indirizzo: item.indirizzo,
                comune: item.comune,
                cap: item.cap,
                provincia: item.provincia,
                codicefiscale: item.codicefiscale,
                piva: item.piva,
                metodopagamento: item.metodopagamento,
                fm_vf: item.fm_vf,
                /* destinazione fattura */
                nominativo: item.nominativo,
                indirizzospedizione: item.indirizzospedizione,
                regionespedizione: item.regionespedizione,
                provinciaspedizione: item.provinciaspedizione,
                comunespedizione: item.comunespedizione,
                capspedizione: item.capspedizione
            };
        });
        $("#cliente").autocomplete({
            source: clienti,
            select: function (event, ui) {
                $('#idcliente').val(ui.item.id);
                /* cerca banche preventivi e fatture per quel cliente */
                $.ajax({
                    type: "POST",
                    url: "./statistiche.php",
                    data: "idcliente=" + ui.item.id + "&submit=commessecliente",
                    dataType: "json",
                    success: function (res)
                    {
                        /* preventivi */

                        /* commesse */
                        var optioncommesse = "";
                        for (var i = 0; i < res.commesse.length; i++) {
                            optioncommesse += "<option class=\"commessecliente comm" + res.commesse[i].id + " \" value=\"" + res.commesse[i].id + "\">" + res.commesse[i].numero + " - " + res.commesse[i].datait + " " + res.commesse[i].titolo + "</option>";
                        }
                        $('.commessecliente').remove();
                        $('.collegacommessa').append(optioncommesse);

                    }
                });
            }
        });

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
            $('.commessecliente').remove();
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
<form method="post" action="" id="statistichecommesse" name="statistichecommesse">
    <input type="text" name="cliente" id="cliente" onkeyup="pulisciidcliente();" class="input_moduli sizing float_moduli_small" placeholder="Cliente" title="Cliente" /> 
    <input type="hidden" name="idcliente" id="idcliente" value="" />
    <select name="collegacommessa" id="collegacommessa" class="input_moduli sizing float_moduli collegacommessa" placeholder="Collega una commessa" title="Collega una commessa">
        <option value="">Seleziona una commessa</option>
    </select>
    <input type="text" name="datadal" id="datadal" class="input_moduli sizing float_moduli_small_15" placeholder="Periodo dal" title="Periodo dal" onkeyup="puliscidata1();" />
    <input type="hidden" name="data1" id="data1" /> 
    <input type="text" name="dataal" id="dataal" class="input_moduli sizing float_moduli_small_15" placeholder="Periodo al" title="Periodo al" onkeyup="puliscidata2();" />
    <input type="hidden" name="data2" id="data2" /> 
    <div class="chiudi"></div>
    <select name="profitcenter" id="profitcenter" class="input_moduli sizing float_moduli" placeholder="Profit Center" title="Profit Center">
        <option value="">Seleziona una profit center</option>        
        <?php echo $profitselect; ?>
    </select>
    <select name="cdc" id="cdc" class="input_moduli sizing float_moduli" placeholder="Centro di costo" title="Centro di costo">
        <option value="">Seleziona una Centro di costo</option>
        <?php echo $cdcselect; ?>
    </select>
    <div class="chiudi"></div>
    Stati commesse: <input type="checkbox" id="aperta" name="aperta" checked> Aperte <input type="checkbox" id="chiusa" name="chiusa" checked> Chiuse  <input type="checkbox" id="dafatturare" name="dafatturare" checked> Da fatturare  <input type="checkbox" id="fatturata" name="fatturata" checked> Fatturate
    <div class="chiudi" style="height: 30px;"></div>
    <input type="submit" class="submit_form submit_form_10 nopost" value="Invia" id="submitstatistichecommesse" />        
    <div class="chiudi" style="height: 50px;"></div>
</form>
<div class="chiudi"></div>
<div id="graficoanno"></div>