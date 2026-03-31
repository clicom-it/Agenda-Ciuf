<?php
include '../library/controllo.php';
include '../library/config.php';
include '../library/connessione.php';
include '../library/basic.class.php';
include '../library/functions.php';


/* dati clienti */
$fornitori = getDati("clienti_fornitori", "WHERE tipo = 2");

?>

<script type="text/javascript" src="./js/functions-statistiche.js"></script>
<script type="text/javascript">
    $(document).ready(function () {        
        /* richiama clienti */
        var datifornitori = <?php echo json_encode($fornitori); ?>;
        var fornitori = $.map(datifornitori, function (item) {
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
        $("#fornitore").autocomplete({
            source: fornitori,
            select: function (event, ui) {
                
                $('#idfornitore').val(ui.item.id);
            }
        });
        
        $("#fornitore2").autocomplete({
            source: fornitori,
            select: function (event, ui) {
                
                $('#idfornitore2').val(ui.item.id);
            }
        });
    });
    function pulisciidcliente() {
        $('#fornitore').val($('#fornitore').val().trim());
        if ($('#fornitore').val() === "") {
            $('#idfornitore').val("");
        }
    }
    
    function pulisciidcliente2() {
        $('#fornitore2').val($('#fornitore2').val().trim());
        if ($('#fornitore2').val() === "") {
            $('#idfornitore2').val("");
        }
    }
</script>
<div class="graficostatistiche sizing">
    <form method="post" action="" id="statisticheanno" name="statisticheanno">
        <input type="text" name="anno" class="required input_moduli sizing float_moduli_small" placeholder="Anno per statistiche" title="Anno per statistiche" /> 
        <input type="text" name="fornitore" id="fornitore" onkeyup="pulisciidcliente();" class="input_moduli sizing float_moduli_small" placeholder="Fornitore" title="Fornitore" /> 
        <input type="hidden" name="idfornitore" id="idfornitore" value="" />
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
        <input type="text" name="fornitore2" id="fornitore2" onkeyup="pulisciidcliente2();" class="input_moduli sizing float_moduli_small" placeholder="Fornitore" title="Fornitore" /> 
        <input type="hidden" name="idfornitore2" id="idfornitore2" value="" />
        <input type="submit" class="submit_form submit_form_10 nopost" value="Invia" id="submitstatisticheanno2" />
        <div class="chiudi" style="height: 20px;"></div>
    </form>
    <div class="chiudi"></div>
    <div id="graficoanno2"></div>
    <div class="chiudi"></div>
</div>
<div class="chiudi"></div>