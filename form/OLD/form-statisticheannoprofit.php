<?php
include '../library/controllo.php';
include '../library/config.php';
include '../library/connessione.php';
include '../library/basic.class.php';
include '../library/functions.php';


/* dati clienti */
$clienti = getDati("clienti_fornitori", "WHERE tipo = 1");

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
            }
        });
        
        $("#cliente2").autocomplete({
            source: clienti,
            select: function (event, ui) {
                
                $('#idcliente2').val(ui.item.id);
            }
        });
    });
    function pulisciidcliente() {
        $('#cliente').val($('#cliente').val().trim());
        if ($('#cliente').val() === "") {
            $('#idcliente').val("");
        }
    }
    
    function pulisciidcliente2() {
        $('#cliente2').val($('#cliente2').val().trim());
        if ($('#cliente2').val() === "") {
            $('#idcliente2').val("");
        }
    }
</script>
<div class="graficostatistiche sizing">
    <form method="post" action="" id="statisticheanno" name="statisticheanno">
        <input type="text" name="anno" class="required input_moduli sizing float_moduli_small" placeholder="Anno per statistiche" title="Anno per statistiche" /> 
        <input type="text" name="cliente" id="cliente" onkeyup="pulisciidcliente();" class="input_moduli sizing float_moduli_small" placeholder="Cliente" title="Cliente" /> 
        <input type="hidden" name="idcliente" id="idcliente" value="" />
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
        <input type="text" name="cliente2" id="cliente2" onkeyup="pulisciidcliente2();" class="input_moduli sizing float_moduli_small" placeholder="Cliente" title="Cliente" /> 
        <input type="hidden" name="idcliente2" id="idcliente2" value="" />
        <input type="submit" class="submit_form submit_form_10 nopost" value="Invia" id="submitstatisticheanno2" />
        <div class="chiudi" style="height: 20px;"></div>
    </form>
    <div class="chiudi"></div>
    <div id="graficoanno2"></div>
    <div class="chiudi"></div>
</div>
<div class="chiudi"></div>