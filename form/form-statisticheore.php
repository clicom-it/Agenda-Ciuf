<?php
include '../library/controllo.php';
include '../library/config.php';
include '../library/connessione.php';
include '../library/basic.class.php';
include '../library/functions.php';

/* dati clienti */
$clienti = getDati("clienti_fornitori", "WHERE tipo = 1");

$utenti = getDati("utenti", "WHERE id != 1");
$utentiprint = "<option value=\"\">Seleziona dipendente...</option>";
if ($idutente == 1) {
    $utentiprint .= "<option value=\"1\" selected>root</option>";
}
foreach ($utenti as $utentid) {

    if ($idutente == $utentid['id']) {
        $selected = "selected";
    } else {
        $selected = "";
    }
    $utentiprint .= "<option value=\"" . $utentid['id'] . "\" $selected>" . $utentid['nome'] . " " . $utentid['cognome'] . "</option>";
}
?>

<!-- function modulo -->
<script type="text/javascript" src="./js/functions-ore.js"></script>
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
        
    });
    function pulisciidcliente() {
        $('#cliente').val($('#cliente').val().trim());
        if ($('#cliente').val() === "") {
            $('#idcliente').val("");
        }
    }
    
</script>

<form method="post" action="" id="formstatisticheore" name="formstatisticheore">     
    <select name="idutente" id="idutente" class="input_moduli sizing float_moduli_40 required">
        <?php echo $utentiprint ?>
    </select>
    <input type="text" name="data1" id="data1" class="input_moduli sizing required float_moduli_small nopost" value="" placeholder="Dal" title="Dal" />
    <input type="hidden" name="data" id="data" value="" />  
    <input type="text" name="datap2" id="datap2" class="input_moduli sizing required float_moduli_small nopost" value="" placeholder="Al" title="Al" />
    <input type="hidden" name="data2" id="data2" value="" />  
    <input type="text" name="cliente" id="cliente" class="input_moduli sizing float_moduli_small nopost" onkeyup="pulisciidcliente()" value="" placeholder="Cliente" title="Cliente" />
    <input type="hidden" name="idcliente" id="idcliente" value="" />  
    <input type="submit" class="submit_form submit_form_10 nopost" value="Invia" id="submitstatisticheore" /> 
    <div class="chiudi"></div>
</form>
<div class="statisticheore sizing" id="statisticheoredipforn">
    
</div>
<script type="text/javascript">
    $(document).ready(function () {
        /* data */
        $.datepicker.setDefaults($.datepicker.regional[ "it" ]);
        $("#data1").datepicker({
            altFormat: "yy-mm-dd",
            altField: "#data"
        });
        $("#datap2").datepicker({
            altFormat: "yy-mm-dd",
            altField: "#data2"
        });
    });
</script>