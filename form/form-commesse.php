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

/* seleziono numero successivo commessa */
$valmax = maxNum("commesse", "numero", "WHERE YEAR(data) = " . DATE("Y") . "");
$progressivo = $valmax[0]['max'] + 1;
/* dati clienti */
$clienti = getDati("clienti_fornitori", "WHERE tipo = 1");
/* elenco i profit center */
$profit = getDati("profit_center", "WHERE idp='0'");
foreach ($profit as $profitd) {
    $vociprofit .= "<option value=\"" . $profitd['id'] . "\">" . $profitd['nome'] . "</option>";
}

/* array ore lavorazioni */
$arrore[] = '"00:30"';
for ($i = 1; $i <= 200; $i++) {
    if ($i < 10) {
        $pref = "0";
    } else {
        $pref = "";
    }
    $arrore[] = '"' . $pref . $i . ':00"';
    $arrore[] = '"' . $pref . $i . ':30"';
}
$orecomplete = join(",", $arrore);

/* magazzino */
$magazzino = getDati("magazzino", "");
?>

<script type="text/javascript" src="./js/functions-commesse.js"></script>
<script type="text/javascript">
    $(document).ready(function () {
        /* data */
        $.datepicker.setDefaults($.datepicker.regional[ "it" ]);
        $("#datap").datepicker({
            altFormat: "yy-mm-dd",
            altField: "#data"
        });

        /* aggiungo righe commesse */
        /**/
        var maxField = 100; //Input fields increment limitation
        var addButton = $('.add_button'); //Add button selector
        var wrapper = $('.contienirighe'); //Input field wrapper
        var fieldHTML = '<div class="rigaadd riga_prodotto_comm sizing">\n\
            <input type=\"hidden\" name="idlavcomm[]" value="0" />\n\
            <i class="fa fa-arrows-v fa-lg ordinarighe" aria-hidden="true"></i>\n\
            <select name="idprofit[]" class="input_moduli sizing float_moduli_small_10" placeholder="Profit Center" title="Profit Center"><option value="0">Seleziona profit center</option><?php echo $vociprofit; ?></select>\n\
            <input type="text" name="nome[]" class="input_moduli sizing float_moduli_small nomepz" placeholder="Cerca (codice o titolo)" title="Cerca (codice o titolo)" />\n\
            <textarea name="descr[]" class="nosortable textarea_moduli_small sizing float_moduli_50_50" placeholder="Descrizione" title="Descrizione"></textarea>\n\
            <input type="text" name="oreprev[]" class="oreprev input_moduli sizing float_moduli_small_10 timepicker" placeholder="Ore previste" title="Ore previste" value="00:00" />\n\
            <input type="text" name="prezzocliente[]" class="prezzocliente input_moduli sizing float_moduli_small_10" placeholder="Prezzo cliente" title="Prezzo cliente" value="0" />\n\
            <input type="hidden" name="costo[]" class="costo input_moduli sizing float_moduli_small_10" placeholder="Costo" title="Costo" value="0" />\n\
            <input type="hidden" name="statovoce[]" class="costo input_moduli sizing float_moduli_small_10" value="0" />\n\
            <a href="javascript:;" class="remove_button"><i style="color: #CD0A0A; line-height: 35px;" class="fa fa-times fa-lg" aria-hidden="true"></i></a>\n\
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
                        $(this).next('textarea').next('input').next('input').val('00:00');
                        $(this).next('textarea').next('input').next('input').val(ui.item.prezzo);
                        totaleorepreviste();
                        totalecommessa();
                    }
                });
                /**/
                /**/
            }
            var valori = [<?php echo $orecomplete; ?>];
            $(".timepicker").autocomplete({
                source: valori,
                change: function (event, ui) {
                    totaleorepreviste();
                }
            });
        });
        $(wrapper).on('click', '.remove_button', function (e) { //Once remove button is clicked
            if (confirm("Stai per eliminare il contenuto, vuoi continuare?")) {
                e.preventDefault();
                $(this).parent('div').remove(); //Remove field html
                x--; //Decrement field counter
                totalecommessa();
                totaleorepreviste();
            }
        });
        /**/
        /* al volo prezzi e costi */
        $('.contienirighe').on('click', function () {
            /* inserimento/modifica dei prezzi */
            $('.prezzocliente').unbind('keyup').on('keyup', function () {
                this.value = this.value.replace(/\,/g, '.');
                totalecommessa();
            });
            /* inserimento/modifica costi */
            $('.costo').unbind('keyup').on('keyup', function () {
                this.value = this.value.replace(/\,/g, '.');
                totalecommessa();
            });
        });        
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
                    var codicefiscale = 'C.F. ' + ui.item.codicefiscale;
                } else {
                    codicefiscale = "";
                }
                $('#idcliente').val(ui.item.id);
                var daticliente = ui.item.nome + ' ' + ui.item.cognome + '\n' + ui.item.azienda + '\n' + codicefiscale
                        + '\n' + piva + '\n' + ui.item.indirizzo + '\n' + ui.item.cap + ' ' + ui.item.comune + ' (' + ui.item.provincia + ')';
                $('#daticliente').val(removeline(daticliente));

                /* cerca preventivi per quel cliente */
                $.ajax({
                    type: "POST",
                    url: "./commesse.php",
                    data: "idcliente=" + ui.item.id + "&submit=preventiviokcliente",
                    dataType: "json",
                    success: function (res)
                    {
                        var optionpreventivi = "";
                        for (var i = 0; i < res.preventivi.length; i++) {
                            optionpreventivi += "<option class=\"preventivicliente prev" + res.preventivi[i].id + " \" value=\"" + res.preventivi[i].id + "\">" + res.preventivi[i].numero + " - " + res.preventivi[i].datait + " " + res.preventivi[i].titolo + "</option>";
                        }
                        $('.preventivicliente').remove();
                        $('.collegapreventivo').append(optionpreventivi);
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
            $('.preventivicliente').remove();
            $('.preventivicollegati').html("Non ci sono preventivi collegati per questa commessa");
        }
    }
</script>
<form method="post" action="" id="formcommesse" name="formcommesse">  
    <div class="tit_big">DATI COMMESSA</div>
    <input type="text" name="numero" id="numero" value="<?php echo $progressivo; ?>" class="input_moduli sizing required float_moduli" placeholder="Numero commessa" title="Numero commessa" />                
    <!--<input type="hidden" name="idp" id="idp" />-->
    <input type="text" name="datap" id="datap" class="input_moduli sizing required float_moduli nopost" placeholder="Data" title="Data" />
    <input type="hidden" name="data" id="data" />
    <input type="text" name="cercacliente" onkeyup="pulisciidcliente();" id="cercacliente" class="input_moduli sizing float_moduli nopost" placeholder="Cerca cliente" title="Cerca cliente" />            
    <input type="hidden" name="idcliente" id="idcliente" />
    <textarea name="daticliente" id="daticliente" class="textarea_moduli required sizing float_moduli" placeholder="Dati cliente" title="Dati cliente"></textarea>
    <div class="chiudi"></div>
    <div class="tit_big">DETTAGLI COMMESSA</div>
    <input type="text" name="titolo" id="titolo" class="input_moduli sizing required float_moduli" placeholder="Titolo commessa" title="Titolo commessa" />   
    <textarea name="descrizione" id="descrizione" class="textarea_moduli sizing float_moduli" placeholder="Descrizione commessa" title="Descrizione commessa"></textarea>
    <textarea name="note" id="note" class="textarea_moduli sizing float_moduli" placeholder="Note commessa" title="Note commessa"></textarea>
    <div class="chiudi"></div>
    <!-- preventivi collegati alla commessa -->
    <hr>
    <div class="tit_big">PREVENTIVI COLLEGATI</div>
    <div class="preventivi">
        <select name="colleagapreventivo" class="input_moduli sizing float_moduli nopost collegapreventivo" placeholder="Collega un preventivo" title="Collega un preventivo" onchange="collegaPreventivo($(this).val());">
            <option value="">Seleziona un preventivo</option>
        </select>
        <div class="chiudi" style="height: 20px;"></div>
        <div class="preventivicollegati">
            <div class="noprevcoll">Non ci sono preventivi collegati per questa commessa</div>
        </div>
    </div>
    <div class="chiudi"></div>
    <hr>
    <!-- LAVORAZIONI -->
    <div class="tit_big">LAVORAZIONI</div>
    <div class="contienirighe">
        <div class="riga_prodotto_comm sizing nosortable">
            <div class="ordinarighe"></div>
            <select class="input_moduli sizing float_moduli_small_10 nopost" placeholder="Profit Center" title="Profit Center" disabled><option value="">Seleziona profit center</option></select>
            <input type="text" class="input_moduli sizing float_moduli_small nopost" placeholder="Cerca (codice o titolo)" title="Cerca (codice o titolo)" disabled />
            <input type="text" class="input_moduli sizing float_moduli_50_50 nopost" placeholder="Descrizione" title="Descrizione" disabled />
            <input type="text" class="input_moduli sizing float_moduli_small_10 nopost" placeholder="Ore previste" title="Ore previste" disabled />
            <input type="text" class="input_moduli sizing float_moduli_small_10 nopost" placeholder="Prezzo cliente" title="Prezzo cliente" disabled />
            <input type="hidden" class="input_moduli sizing float_moduli_small_10 nopost" placeholder="Costo" title="Costo" disabled /><a href="javascript:;" class="add_button"><i style="color: #0a0; line-height: 35px;" class="fa fa-plus fa-lg" aria-hidden="true"></i></a> 
            <div class="chiudi"></div>
        </div>
    </div>    
    <div class="chiudi"></div>
    <hr>
    <!-- COSTI SU LAVORAZIONI -->
    <div class="tit_big">QUADRO DI SINTESI</div>
    <div class="rigadettorecomm">
        <div class="profitcomm sizing">PROFIT CENTER</div>
        <div class="nomevocecomm0 sizing">NOME</div>
        <div class="nomevocecomm2 sizing">DESCRIZIONE</div>
        <div class="orelavoratecomm sizing">COSTI INTERNI (ore)</div>
        <div class="orelavoratecomm sizing">COSTI ESTERNI</div>
        <div class="orelavoratecomm sizing">TOTALE COSTI</div>
        <div class="orelavoratecomm sizing">TOTALE ENTRATE</div>
        <div class="orelavoratecomm sizing">MARGINE</div>
        <div class="comandilavoratecomm sizing">&nbsp;</div>
        <div class="chiudi"></div>
    </div>
    <div id="riepilogototalilavorazione">
    </div>
    <div class="chiudi"></div>
    <!-- -->
    <!-- COSTI SU LAVORAZIONI -->
<!--    <div class="tit_big">COSTI SU LAVORAZIONI</div>
    <div class="rigadettorecomm">
        <div class="profitcomm sizing">PROFIT CENTER</div>
        <div class="nomevocecomm sizing">NOME</div>
        <div class="desccdc sizing">DESCRIZIONE</div>
        <div class="orelavoratecomm sizing">COSTI TOTALI</div>
        <div class="comandilavoratecomm sizing">&nbsp;</div>
        <div class="chiudi"></div>
    </div>
    <div id="riepilogocostilavorazioni">
    </div>-->
    <div class="chiudi"></div>
    <!-- -->
    <!-- ore lavorate sulla commessa -->
<!--    <hr>
    <div class="tit_big">ORE LAVORATE</div>
    <div class="orelavorate">
        <div class="rigadettorecomm">
            <div class="profitcomm sizing">PROFIT CENTER</div>
            <div class="nomevocecomm sizing">NOME</div>
            <div class="descvocecomm sizing">DESCRIZIONE</div>
            <div class="orelavoratecomm sizing">ORE LAVORATE TOTALI</div>
            <div class="orelavoratecomm sizing">ORE PREVISTE</div>
            <div class="orelavoratecomm sizing">COSTI TOTALI</div>
            <div class="comandilavoratecomm sizing">&nbsp;</div>
            <div class="chiudi"></div>
        </div>
        <div id="riepilogoore">
            Non ci sono ore lavorate per questa commessa
        </div>
        <input type="hidden" name="costototaleoredautenti" id="costototaleoredautenti" class="nopost" value="0" />
    </div>-->
    <div class="chiudi"></div>
    <hr>
    <!-- -->
    <div class="tit_big">PREZZI, COSTI, ORE</div>
    <div class="float_moduli_small padding_b5">Totale commessa</div>
    <div class="float_moduli_small padding_b5">Totale costi</div>
    <div class="float_moduli_small padding_b5">Ore previste</div>
    <div class="float_moduli_small padding_b5">Ore lavorate</div>
    <div class="float_moduli_small padding_b5">Stato commessa</div>
    <div class="chiudi"></div>
    <input type="text" name="totalecomm" onkeyup="this.value = this.value.replace(/\,/g, '.')" id="totalecomm" class="input_moduli sizing float_moduli_small" placeholder="Totale commessa" title="Totale commessa" value="0" readonly />
    <input type="text" name="totalecosti" onkeyup="this.value = this.value.replace(/\,/g, '.')" id="totalecosti" class="input_moduli sizing float_moduli_small" placeholder="Totale costi" title="Totale costi" value="0" readonly />
    <input type="text" name="orepreviste" onkeyup="this.value = this.value.replace(/\,/g, '.')" id="orepreviste" class="input_moduli sizing float_moduli_small" placeholder="Ore previste totali" title="Ore previste totali" value="00:00" readonly />
    <input type="text" name="orelavorate" onkeyup="this.value = this.value.replace(/\,/g, '.')" id="orelavorate" class="input_moduli sizing float_moduli_small" placeholder="Ore lavorate totali" title="Ore lavorate totali" value="00:00" readonly />
    <select name="stato" id="stato" class="input_moduli sizing float_moduli_small required">
        <option value="">Stato commessa</option>
        <option value="0">Chiusa</option>
        <option value="1">Aperta</option>
        <option value="2">Da fatturare</option>
        <option value="3">Fatturata</option>
    </select>
    <!-- -->
    <div class="chiudi"></div>
    <input type="hidden" name="id" id="id" />
    <input type="submit" id="submitformcommesse" value="Salva" class="submit_form nopost" />
    <div class="bottone_chiudi sizing"><a href="javascript:;" class="sizing" onclick="mostraCommesse();">Chiudi</a></div> <div id="messaggiobottom" class="messaggiookbottom" style="float: left;">Operazione avvenuta con successo</div>
    <div class="chiudi"></div>
</form>
<script type="text/javascript">
    $(document).ready(function () {
        var valori = [<?php echo $orecomplete; ?>];
        $(".timepicker").autocomplete({
            source: valori,
            change: function (event, ui) {
                totaleorepreviste();
            }
        });
    });
</script>