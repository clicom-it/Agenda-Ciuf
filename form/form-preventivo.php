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

/* seleziono numero successivo preventivo */
$valmax = maxNum("preventivi", "numero", "WHERE YEAR(data) = " . DATE("Y") . "");
$progressivo = $valmax[0]['max'] + 1;
/* dati clienti */
$clienti = getDati("clienti_fornitori", "WHERE tipo = 1");
/* metodi pagamento */
$metodipagamento = getDati("metodi_pagamento", "");
/* magazzino */
$magazzino = getDati("magazzino", "");
?>

<script type="text/javascript" src="./js/functions-preventivi.js"></script>

<!-- fine include editor -->
<script type="text/javascript">
    $(document).ready(function () {
        /**/

        /* data */
        $.datepicker.setDefaults($.datepicker.regional[ "it" ]);
        $("#datap").datepicker({
            altFormat: "yy-mm-dd",
            altField: "#data"
        });

        /* aggiungo righe preventivo */
        /**/
        var maxField = 100; //Input fields increment limitation
        var addButton = $('.add_button'); //Add button selector
        var wrapper = $('.contienirighe'); //Input field wrapper
        var fieldHTML = '<div class="rigaadd riga_prodotto_prev sizing">\n\
            <i class="fa fa-arrows-v fa-lg ordinarighe" aria-hidden="true"></i>\n\
            <input type="text" name="nome[]" class="input_moduli sizing float_moduli_small nomepz" placeholder="Cerca (codice o titolo)" title="Cerca (codice o titolo)" />\n\
            <textarea name="descr[]" class="nosortable textarea_moduli_small sizing float_moduli_40" placeholder="Descrizione" title="Descrizione"></textarea>\n\
            <input type="text" name="qta[]" class="qta input_moduli sizing float_moduli_small_10" placeholder="Q.ta" title="Q.ta" value="1" />\n\
            <input type="text" name="prezzo[]" class="prezzo input_moduli sizing float_moduli_small_10" placeholder="Prezzo" title="Prezzo" value="0" />\n\
            <input type="text" name="sconto[]" class="sconto input_moduli sizing float_moduli_small_10" placeholder="Sconto" title="Sconto" value="0" />\n\
            <input type="text" name="scontato[]" class="scontato input_moduli sizing float_moduli_small_10" placeholder="Totale" title="Totale" value="0" /><a href="javascript:;" class="remove_button"><i style="color: #CD0A0A; line-height: 35px;" class="fa fa-times fa-lg" aria-hidden="true"></i></a>\n\
            <div class="chiudi"></div>\n\
        </div>'; //New input field html 
        var x = 1; //Initial field counter is 1
        $(addButton).click(function () { //Once add button is clicked
            if (x < maxField) { //Check maximum number of input fields
                x++; //Increment field counter
                $(wrapper).append(fieldHTML); // Add field html
                /**/
                /**/
                var magazzino = <?php echo json_encode($magazzino); ?>;
                var magazz = $.map(magazzino, function (item) {
                    return {
                        label: item.codice + " " + item.titolo,
                        id: item.id,
                        codice: item.codice,
                        descrizione: item.titolo + "\n" + item.descrizione,
                        prezzo: item.prezzo
                    };

                });
                $(".nomepz").autocomplete({
                    source: magazz,
                    select: function (event, ui) {
                        event.preventDefault();
                        $(this).val(ui.item.codice.trim());
                        $(this).next('textarea').val(ui.item.descrizione.trim());
                        $(this).next('textarea').next('input').next('input').val(ui.item.prezzo);
                        $(this).next('textarea').next('input').next('input').next('input').val(0);
                        $(this).next('textarea').next('input').next('input').next('input').next('input').val(ui.item.prezzo);
                        totalepreventivo();
                    }
                });
                /**/
                /**/
            }
        });
        $(wrapper).on('click', '.remove_button', function (e) { //Once remove button is clicked
            if (confirm("Stai per eliminare il contenuto, vuoi continuare?")) {
                e.preventDefault();
                $(this).parent('div').remove(); //Remove field html
                x--; //Decrement field counter
                totalepreventivo();
            }
        });
        /**/
        /* al volo prezzi, sconti, quantità prodotti e voci del preventivo */
        $('.contienirighe').on('click', function () {
            /* modifica della quantità */
            $('.qta').unbind('keyup').on('keyup', function () {
                this.value = this.value.replace(/\,/g, '.');
                var qta = this.value;
                var prezzo = $(this).next('input').val();
                var sconto = $(this).next('input').next('input').val();
                totale = oprighe(qta, prezzo, sconto);
                $(this).next('input').next('input').next('input').val(totale);
                totalepreventivo();
            });
            /* modifica del prezzo */
            $('.prezzo').unbind('keyup').on('keyup', function () {
                this.value = this.value.replace(/\,/g, '.');
                var qta = $(this).prev('input').val();
                var prezzo = this.value;
                var sconto = $(this).next('input').val();
                totale = oprighe(qta, prezzo, sconto);
                $(this).next('input').next('input').val(totale);
                totalepreventivo();
            });
            /* modifica dello sconto */
            $('.sconto').unbind('keyup').on('keyup', function () {
                this.value = this.value.replace(/\,/g, '.');
                var qta = $(this).prev('input').prev('input').val();
                var prezzo = $(this).prev('input').val();
                var sconto = this.value;
                totale = oprighe(qta, prezzo, sconto);
                $(this).next('input').val(totale);
                totalepreventivo();
            });
            /* modifica del totale articolo/prodotto */
            $('.scontato').unbind('keyup').on('keyup', function () {
                this.value = this.value.replace(/\,/g, '.');
                var totale = this.value;
                var prezzo = $(this).prev('input').prev('input').val();
                var qta = $(this).prev('input').prev('input').prev('input').val();
                var sconto = 100 - ((totale * 100) / (qta * prezzo));
                $(this).prev('input').val(roundTo(sconto, 2));
                totalepreventivo();
            });
        });
        /* modifica totale preventivo */
        $('#totaleprev').unbind('keyup').on('keyup', function () {
            var totaleprev = this.value;
            var scontoprev = $('#scontoprev').val();
            var totalescontato = totaleprev - totaleprev * scontoprev / 100;
            $('#totalescontatoprev').val(roundTo(totalescontato, 2));
        });

        /* modifica sconto totale preventivo */
        $('#scontoprev').unbind('keyup').on('keyup', function () {
            var scontoprev = this.value;
            var totaleprev = $('#totaleprev').val();
            var totalescontato = totaleprev - totaleprev * scontoprev / 100;
            $('#totalescontatoprev').val(roundTo(totalescontato, 2));
        });
        /* modifica totale preventivo scontato */
        $('#totalescontatoprev').unbind('keyup').on('keyup', function () {
            var totalescontato = this.value;
            var totaleprev = $('#totaleprev').val();
            var scontoprev = 100 - totalescontato * 100 / totaleprev;
            $('#scontoprev').val(roundTo(scontoprev, 2));
        });
        /* metodi pagamento */
        /**/
        var metodipagamento = <?php echo json_encode($metodipagamento); ?>;
        var metodi = $.map(metodipagamento, function (item) {
            return {
                label: item.nome,
                id: item.id
            };
        });
        $(".pagamento").autocomplete({
            source: metodi
        });
        /**/
        /**/
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
                piva: item.piva
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
                    var codicefiscale = ' - C.F. ' + ui.item.codicefiscale;
                } else {
                    codicefiscale = "";
                }
                $('#idcliente').val(ui.item.id);
                var daticliente = ui.item.nome + ' ' + ui.item.cognome + '\n' + ui.item.azienda + '\n'
                        + ui.item.indirizzo + '\n' + ui.item.cap + ' ' + ui.item.comune + ' (' + ui.item.provincia + ')\n'
                        + piva + codicefiscale + '\n';
                $('#daticliente').val(removeline(daticliente));
            }
        });


    });
    function pulisciidcliente() {
        $('#cercacliente').val($('#cercacliente').val().trim());
        if ($('#cercacliente').val() === "") {
            $('#idcliente').val("");
            $('#daticliente').val("");
        }
    }
</script>
<form method="post" action="" id="formpreventivo" name="formpreventivo">  
    <div class="tit_big">DATI PREVENTIVO</div>
    <input type="text" name="numero" id="numero" value="<?php echo $progressivo; ?>" class="input_moduli sizing required float_moduli" placeholder="Numero preventivo" title="Numero preventivo" />            
    <input type="text" name="rev" id="rev" class="input_moduli sizing float_moduli" placeholder="Revisione" title="Revisione" disabled />
    <input type="hidden" name="idp" id="idp" />
    <input type="text" name="datap" id="datap" class="input_moduli sizing required float_moduli nopost" placeholder="Data" title="Data" />
    <input type="hidden" name="data" id="data" />
    <input type="text" name="cercacliente" onkeyup="pulisciidcliente();" id="cercacliente" class="input_moduli sizing float_moduli nopost" placeholder="Cerca cliente" title="Cerca cliente" />            
    <input type="hidden" name="idcliente" id="idcliente" />
    <textarea name="daticliente" id="daticliente" class="textarea_moduli required sizing float_moduli" placeholder="Dati Cliente" title="Dati cliente"></textarea>
    <textarea name="note" id="note" class="textarea_moduli sizing float_moduli" placeholder="Info preventivo" title="Info preventivo"></textarea>
    <div class="chiudi"></div>
    <div class="tit_big">TITOLO E CORPO PREVENTIVO</div>
    <input type="text" name="titolo" id="titolo" class="input_moduli sizing required float_moduli" placeholder="Titolo preventivo" title="Titolo preventivo" />    
    <input type="text" name="titolo1" id="titolo1" class="input_moduli sizing float_moduli" placeholder="Titolo paragrafo 1" title="Titolo paragrafo 1" />    
    <input type="text" name="titolo2" id="titolo2" class="input_moduli sizing float_moduli" placeholder="Titolo paragrafo 2" title="Titolo paragrafo 2" />        
    <div class="chiudi"></div>      
    <div class="textarea_editor sizing float_moduli">
        Descrizione preventivo:<br />
        <textarea name="descrizione" id="descrizione"></textarea>
    </div>
    <div class="textarea_editor sizing float_moduli">
        Descrizione paragrafo 1:<br />
        <textarea name="descrizione1" id="descrizione1"></textarea>
    </div>
    <div class="textarea_editor sizing float_moduli">
        Descrizione paragrafo 2:<br />
        <textarea name="descrizione2" id="descrizione2"></textarea>
    </div>
    <div class="chiudi"></div>    
    <div class="tit_big">TEMPI REALIZZAZIONE, METODO DI PAGAMENTO</div>
    <textarea class="textarea_moduli sizing float_moduli_50" name="tempi_consegna" id="tempi_consegna" placeholder="Tempi di consegna" title="Tempi di consegna"></textarea>
    <input class="pagamento input_moduli sizing float_moduli_50" name="pagamento" id="pagamento" placeholder="Pagamento" title="Pagamento" />
    <div class="chiudi"></div>
    <div class="tit_big">PREZZI</div>
    <div class="contienirighe">
        <div class="riga_prodotto_prev sizing nosortable">
            <div class="ordinarighe"></div>
            <input type="text" class="input_moduli sizing float_moduli_small nopost" placeholder="Cerca (codice o titolo)" title="Cerca (codice o titolo)" disabled />
            <input type="text" class="input_moduli sizing float_moduli_40 nopost" placeholder="Descrizione" title="Descrizione" disabled />
            <input type="text" class="input_moduli sizing float_moduli_small_10 nopost" placeholder="Q.ta" title="Q.ta" disabled />
            <input type="text" class="input_moduli sizing float_moduli_small_10 nopost" placeholder="Prezzo" title="Prezzo" disabled />
            <input type="text" class="input_moduli sizing float_moduli_small_10 nopost" placeholder="Sconto" title="Sconto" disabled />
            <input type="text" class="input_moduli sizing float_moduli_small_10 nopost" placeholder="Totale" title="totale" disabled /><a href="javascript:;" class="add_button"><i style="color: #0a0; line-height: 35px;" class="fa fa-plus fa-lg" aria-hidden="true"></i></a> 
            <div class="chiudi"></div>
        </div>
    </div>    
    <div class="chiudi"></div>
    <div class="tit_big">TOTALI E CONFERMA</div>
    <input type="text" name="totaleprev" onkeyup="this.value = this.value.replace(/\,/g, '.')" id="totaleprev" class="input_moduli sizing float_moduli_small" placeholder="Totale preventivo" title="Totale preventivo" value="0" />
    <input type="text" name="scontoprev" onkeyup="this.value = this.value.replace(/\,/g, '.')" id="scontoprev" class="input_moduli sizing float_moduli_small" placeholder="Sconto" title="Sconto" value="0" />
    <input type="text" name="totalescontatoprev" id="totalescontatoprev" class="input_moduli sizing float_moduli_small" placeholder="Totale scontato" title="Totale scontato" value="0" />
    <input type="text" name="conferma" id="conferma" class="input_moduli sizing float_moduli_small" placeholder="Cod. Conferma" title="Cod. Conferma" />
    <select name="stato" id="stato" class="input_moduli sizing float_moduli_small required">
        <option value="">Stato preventivo</option>
        <option value="0">Da inviare</option>
        <option value="1">Inviato</option>
        <option value="2">Confermato</option>
        <option value="3">Da risentire</option>
        <option value="4">Fatturato</option>
        <option value="5">Archiviato</option>
    </select>
    <div class="chiudi"></div>
    <div class="tit_big">NOTE PREZZI E PREVENTIVO, ACCETTAZIONE</div>
    <textarea class="textarea_moduli sizing float_moduli_50" name="noteprezzieprev" id="noteprezzieprev" placeholder="Note prezzi e preventivo" title="Note prezzi e preventivo"></textarea>
    <span style="float: left;">Firma per accettazione:</span> <select name="accettazionepreventivo" id='accettazionepreventivo' class="input_moduli sizing float_moduli_small_10 required">
        <option value="">Vuoi inserire la firma per accettazione?</option>
        <option value="0">NO</option>
        <option value="1">SI</option>
    </select>
    <span style="float: left;">Mostra IVA:</span> <select name="mostraiva" id='mostraiva' class="input_moduli sizing float_moduli_small_10 required">
        <option value="">Vuoi mostrare il prezzo con iva?</option>
        <option value="0">NO</option>
        <option value="1">SI</option>
    </select>
    <div class="chiudi"></div>
    <!-- -->
    <input type="hidden" name="id" id="id" />
    <input type="submit" id="submitformpreventivo" value="Salva" class="submit_form nopost" />
    <input type="submit" id="submitformpreventivorevisione" value="Crea revisione" class="submit_form margin-l_10 hidden nopost" />
    <div class="bottone_chiudi sizing"><a href="javascript:;" class="sizing" onclick="mostraPreventivi();">Chiudi</a></div>
    <div class="chiudi"></div>
</form>