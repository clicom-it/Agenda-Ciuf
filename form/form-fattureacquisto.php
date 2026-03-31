<?php
include '../library/controllo.php';
include '../library/config.php';
include '../library/connessione.php';
include '../library/basic.class.php';
include '../library/functions.php';


$submit = "";
if (isset($_POST['submit'])) {
    $submit = $_POST['submit'];
}

/* seleziono numero successivo fattura */
$valmax = maxNum("fatture", "numero", " WHERE tipo = '1' AND YEAR(data) = " . DATE("Y") . "" );
$progressivo = $valmax[0]['max'] + 1;
/* dati fornitori */
$fornitori = getDati("clienti_fornitori", "WHERE tipo = 2");
/* elenco i profit center */
$profit = getDati("profit_center", "WHERE idp='0'");
foreach ($profit as $profitd) {
    $vociprofit .= "<option value=\"" . $profitd['id'] . "\">" . $profitd['nome'] . "</option>";
}
/* seleziona iva */
$iva = getDati("iva", "ORDER BY valore");
foreach ($iva as $ivap) {
    $ivavalore = $ivap['valore'];
    $ivapred = $ivap['predefinito'];

    if ($ivapred > 0) {
        $ivaselected = "selected";
    } else {
        $ivaselected = "";
    }
    $ivaselect .= "<option value=\"$ivavalore\" $ivaselected>$ivavalore</option>";
}
/* metodi di pagamento */
$metodi = getDati("metodi_pagamento", "ORDER BY nome");
foreach ($metodi as $metodid) {
    $metodiselect .= "<option value=\"" . $metodid['id'] . "\">" . $metodid['nome'] . "</option>";
}
?>

<script type="text/javascript" src="./js/functions-fatture.js"></script>
<script type="text/javascript">
    $(document).ready(function () {
        /* data */
        $.datepicker.setDefaults($.datepicker.regional[ "it" ]);
        $("#datap").datepicker({
            altFormat: "yy-mm-dd",
            altField: "#data"
        });
        /* aggiungo righe fattura */
        /**/
        var maxField = 100; //Input fields increment limitation
        var addButton = $('.add_button'); //Add button selector
        var wrapper = $('.contienirighe'); //Input field wrapper
        var fieldHTML = '<div class="rigaadd riga_prodotto_fatt sizing">\n\
            <i class="fa fa-arrows-v fa-lg ordinarighe" aria-hidden="true"></i>\n\
            <select name="idprofit[]" class="input_moduli sizing float_moduli_small_10" placeholder="Profit Center" title="Profit Center"><option value="0">Seleziona profit center</option><?php echo $vociprofit; ?></select>\n\
            <input type="text" name="descrizione[]" class="input_moduli sizing float_moduli_40" placeholder="Descrizione" title="Descrizione" />\n\
            <input type="text" name="um[]" class="input_moduli sizing float_moduli_small_10" placeholder="U.M." title="U.M." />\n\
            <input type="text" name="qta[]" class="qta input_moduli sizing float_moduli_small_5" placeholder="Q.ta" title="Q.ta" />\n\
            <input type="text" name="importo[]" class="importo input_moduli sizing float_moduli_small_15" placeholder="Importo" title="Importo" />\n\
            <input type="text" name="sconto[]" class="sconto input_moduli sizing float_moduli_small_10" placeholder="Sconto" title="Sconto" />\n\
            <select name="iva[]"class="iva input_moduli sizing float_moduli_small_10" placeholder="Iva" title="Iva"> <?php echo $ivaselect; ?></select>\n\
            <input type="hidden" name="totalevoce[]" class="totalevoce input_moduli sizing float_moduli_small_10 nopost" />\n\
            <input type="hidden" name="totalevoceiva[]" class="totalevoceiva input_moduli sizing float_moduli_small_10 nopost" />\n\
            <input type="hidden" name="idprev[]" class="input_moduli sizing float_moduli_small_10" value="0" />\n\
            <input type="hidden" name="idfatt[]" class="input_moduli sizing float_moduli_small_10" value="0" />\n\
            <input type="hidden" name="idcomm[]" class="input_moduli sizing float_moduli_small_10" value="0" />\n\
            <input type="hidden" name="idvocecomm[]" class="input_moduli sizing float_moduli_small_10" value="0" />\n\
            <a href="javascript:;" class="remove_button"><i style="color: #CD0A0A; line-height: 35px;" class="fa fa-times fa-lg" aria-hidden="true"></i></a>\n\
            <div class="chiudi"></div>\n\
        </div>'; //New input field html 
        var x = 1; //Initial field counter is 1
        $(addButton).click(function () { //Once add button is clicked
            if (x < maxField) { //Check maximum number of input fields
                x++; //Increment field counter
                $(wrapper).append(fieldHTML); // Add field html
            }
        });
        $(wrapper).on('click', '.remove_button', function (e) { //Once remove button is clicked
            if (confirm("Stai per eliminare il contenuto, vuoi continuare?")) {
                e.preventDefault();
                $(this).parent('div').remove(); //Remove field html
                x--; //Decrement field counter
                totalefattura();
            }
        });
        /**/
        /* al volo prezzi, sconti, quantità prodotti e voci del fattura */
        $('.contienirighe').on('click', function () {
            /* modifica della quantità */
            $('.qta').unbind('on').on('keyup', function () {
                this.value = this.value.replace(/\,/g, '.');
                var qta = this.value;
                var importo = $(this).next('input').val();
                var sconto = $(this).next('input').next('input').val();
                var iva = $(this).next('input').next('input').next('select').val();
                totale = oprighe(qta, importo, sconto);
                var ivapercalcoli = parseFloat(100 + parseFloat(iva));
                totaleiva = totale * ivapercalcoli / 100;
                $(this).next('input').next('input').next('select').next('input').val(totale);
                $(this).next('input').next('input').next('select').next('input').next('input').val(totaleiva);
                totalefattura();
            });
            /* modifica dell'importo */
            $('.importo').unbind('keyup').on('keyup', function () {
                this.value = this.value.replace(/\,/g, '.');
                var qta = $(this).prev('input').val();
                var importo = this.value;
                var sconto = $(this).next('input').val();
                var iva = $(this).next('input').next('select').val();
                totale = oprighe(qta, importo, sconto);
                var ivapercalcoli = parseFloat(100 + parseFloat(iva));
                totaleiva = totale * ivapercalcoli / 100;
                $(this).next('input').next('select').next('input').val(totale);
                $(this).next('input').next('select').next('input').next('input').val(totaleiva);
                totalefattura();
            });
            /* modifica dello sconto */
            $('.sconto').unbind('keyup').on('keyup', function () {
                this.value = this.value.replace(/\,/g, '.');
                var qta = $(this).prev('input').prev('input').val();
                var importo = $(this).prev('input').val();
                var sconto = this.value;
                var iva = $(this).next('select').val();
                totale = oprighe(qta, importo, sconto);
                var ivapercalcoli = parseFloat(100 + parseFloat(iva));
                totaleiva = totale * ivapercalcoli / 100;
                $(this).next('select').next('input').val(totale);
                $(this).next('select').next('input').next('input').val(totaleiva);
                totalefattura();
            });
            /* modifica dell'iva */
            $('.iva').unbind('change').on('change', function () {
                this.value = this.value.replace(/\,/g, '.');
                var qta = $(this).prev('input').prev('input').prev('input').val();
                var importo = $(this).prev('input').prev('input').val();
                var sconto = $(this).prev('input').val();
                var iva = this.value;
                totale = oprighe(qta, importo, sconto);
                var ivapercalcoli = parseFloat(100 + parseFloat(iva));
                totaleiva = totale * ivapercalcoli / 100;
                $(this).next('input').val(totale);
                $(this).next('input').next('input').val(totaleiva);
                totalefattura();
            });
        });
        /**/
        /* richiama clienti */
        var daticlienti = <?php echo json_encode($fornitori); ?>;
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
        $("#cercacliente").autocomplete({
            source: clienti,
            select: function (event, ui) {
                if (ui.item.piva.length > 0) {
                    var piva = 'P.Iva ' + ui.item.piva;
                } else {
                    piva = "";
                }
                if (ui.item.codicefiscale.length > 0) {
                    var codicefiscale = 'C.F. ' + ui.item.codicefiscale;
                } else {
                    codicefiscale = "";
                }
                $('#idcliente').val(ui.item.id);
                var daticliente = ui.item.nome + ' ' + ui.item.cognome + '\n' + ui.item.azienda + '\n' + codicefiscale
                        + '\n' + piva + '\n' + ui.item.indirizzo + '\n' + ui.item.cap + ' ' + ui.item.comune + ' (' + ui.item.provincia + ')';

                if (ui.item.indirizzospedizione.length > 0) {
                    var destinazione = ui.item.nominativo + '\n'
                            + ui.item.indirizzospedizione + '\n' + ui.item.capspedizione + ' ' + ui.item.comunespedizione + ' (' + ui.item.provinciaspedizione + ')';
                } else {
                    destinazione = ui.item.nome + ' ' + ui.item.cognome + '\n' + ui.item.azienda
                            + '\n' + ui.item.indirizzo + '\n' + ui.item.cap + ' ' + ui.item.comune + ' (' + ui.item.provincia + ')';
                }
                $('#daticliente').val(removeline(daticliente));
                if (ui.item.metodopagamento == 0) {
                    ui.item.metodopagamento = "";
                }
                $('#idpagamento').val(ui.item.metodopagamento);
                $('#finemese_vista').val(ui.item.fm_vf);
                $('#datispedizione').val(removeline(destinazione));

                /* cerca preventivi e fatture per quel cliente */
                $.ajax({
                    type: "POST",
                    url: "./fatture.php",
                    data: "idcliente=" + ui.item.id + "&submit=preventivicommesseokcliente",
                    dataType: "json",
                    success: function (res)
                    {
                        /* preventivi */
                        var optionpreventivi = "";
                        for (var i = 0; i < res.preventivi.length; i++) {
                            optionpreventivi += "<option class=\"preventivicliente prev" + res.preventivi[i].id + " \" value=\"" + res.preventivi[i].id + "\">" + res.preventivi[i].numero + " - " + res.preventivi[i].datait + " " + res.preventivi[i].titolo + "</option>";
                        }
                        $('.preventivicliente').remove();
                        $('.collegapreventivo').append(optionpreventivi);
                        /* commesse */
                        var optioncommesse = "";
                        for (var i = 0; i < res.commesse.length; i++) {
                            optioncommesse += "<option class=\"commessecliente comm" + res.commesse[i].id + " \" value=\"" + res.commesse[i].id + "\">" + res.commesse[i].numero + " - " + res.commesse[i].datait + " " + res.commesse[i].titolo + "</option>";
                        }
                        $('.commessecliente').remove();
                        $('.collegacommessa').append(optioncommesse);
                    }
                });
                /**/
            }
        });
    });
    function pulisciidcliente() {
        $('#cercacliente').val($('#cercacliente').val().trim());
        if ($('#cercacliente').val() === "") {
            $('#idcliente').val("");
            $('#daticliente').val("");
            $('#datispedizione').val("");
            $('#idpagamento').val("");
            $('#finemese_vista').val("");
            $('.preventivicliente').remove();
            $('.preventivicollegati').html("Non ci sono preventivi collegati per questa commessa");
            $('.commessecliente').remove();
            $('.commessecollegate').html("Non ci sono preventivi collegati per questa commessa");
        }
    }
</script>
<form method="post" action="" id="formfattura" name="formfattura">  
    <div class="tit_big">DATI FATTURA</div>
    <input type="text" name="numero" id="numero" value="<?php echo $progressivo; ?>" class="input_moduli sizing required float_moduli" placeholder="Numero fattura acquisto" title="Numero fattura acquisto" readonly />   
    <input type="text" name="datap" id="datap" class="input_moduli sizing required float_moduli nopost" placeholder="Data" title="Data" onchange="calcolascadenze();" />
    <input type="hidden" name="data" id="data" />    
    <input type="text" name="cercacliente" onkeyup="pulisciidcliente();" id="cercacliente" class="input_moduli sizing float_moduli nopost" placeholder="Cerca fornitore" title="Cerca fornitore" />            
    <input type="hidden" name="idcliente" id="idcliente" />
    <textarea name="daticliente" id="daticliente" class="textarea_moduli required sizing float_moduli" placeholder="Dati Fornitore" title="Dati Fornitore"></textarea>
    <textarea name="datispedizione" id="datispedizione" class="textarea_moduli sizing float_moduli" placeholder="Destinazione fattura" title="Destinazione fattura"></textarea>
    <textarea name="note" id="note" class="textarea_moduli sizing float_moduli" placeholder="Note fattura" title="Note fattura"></textarea>
    <div class="chiudi"></div>    
    <select name="tipo" id="tipo" class="input_moduli sizing float_moduli_small required">
        <option value="1">Acquisto</option>
    </select>
    <select name="idpagamento" id="idpagamento" class="input_moduli sizing float_moduli_small required" onchange="calcolascadenze();">
        <option value="">Seleziona metodo di pagamento...</option>
        <?php echo $metodiselect; ?>
    </select>
    <select name="finemese_vista" id="finemese_vista" class="input_moduli sizing float_moduli_small required" onchange="calcolascadenze();">
        <option value="">Tempo di pagamento...</option>
        <option value="0">Fine mese</option>
        <option value="1">Vista fattura</option>
    </select>
    <div class="chiudi"></div>
    
    <div class="tit_big">VOCI FATTURA</div>
    <div class="contienirighe">
        <div class="riga_prodotto_fatt sizing nosortable">
            <div class="ordinarighe"></div>
            <select class="input_moduli sizing float_moduli_small_10 nopost" placeholder="Profit Center" title="Profit Center" disabled><option value="">Seleziona profit center</option></select>
            <input type="text" class="input_moduli sizing float_moduli_40 nopost" placeholder="Descrizione" title="Descrizione" disabled />
            <input type="text" class="input_moduli sizing float_moduli_small_10 nopost" placeholder="U.M." title=U.M." disabled />
            <input type="text" class="input_moduli sizing float_moduli_small_5 nopost" placeholder="Q.ta" title="Q.ta" disabled />
            <input type="text" class="input_moduli sizing float_moduli_small_15 nopost" placeholder="Importo" title="Importo" disabled />
            <input type="text" class="input_moduli sizing float_moduli_small_10 nopost" placeholder="Sconto" title="Sconto" disabled />
            <select name="iva"class="input_moduli sizing float_moduli_small_10 nopost" placeholder="Iva" title="Iva" disabled>
                <?php echo $ivaselect; ?>
            </select>
            <a href="javascript:;" class="add_button"><i style="color: #0a0; line-height: 35px;" class="fa fa-plus fa-lg" aria-hidden="true"></i></a> 
            <div class="chiudi"></div>
        </div>
    </div>    
    <div class="chiudi"></div>
    <div class="tit_big">TOTALE</div>
    <input type="text" name="totalefatt" id="totalefatt" class="input_moduli sizing float_moduli_small" placeholder="Totale fattura" title="Totale fattura" readonly />
    <input type="text" name="totalefatt_iva" id="totalefatt_iva" class="input_moduli sizing float_moduli_small" placeholder="Totale ivato" title="Totale ivato" readonly />
    <select name="stato" id="stato" class="input_moduli sizing float_moduli_small required">
        <option value="">Stato fattura</option>
        <option value="0">Non pagata</option>
        <option value="1">Pagata</option>
    </select>
    <div class="chiudi"></div>
    <hr>
    <!-- -->
    <div class="tit_big">SCADENZE FATTURA</div>
    <div id="scadenzefattura"></div>
    <div id="mostraaggiornascadenzefattura" style="display: none;">
        <input type="hidden" value ="1" name="modificascadenze"/>
        <div class="chiudi"></div>
    </div>
    <div class="chiudi"></div>
    <hr>
    <!-- -->
    <input type="hidden" name="id" id="id" />
    <input type="submit" id="submitformfattura" value="Salva" class="submit_form nopost" />
    <div class="bottone_chiudi sizing"><a href="javascript:;" class="sizing" onclick="mostraFatture('2');">Chiudi</a></div>
    <div class="chiudi"></div>
</form>