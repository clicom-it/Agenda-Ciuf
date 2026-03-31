<?php
include '../library/controllo.php';
include '../library/config.php';
include '../library/connessione.php';
include '../library/basic.class.php';
include '../library/functions.php';

/* richiamo commesse */
$commesse = getDati("commesse", "WHERE stato='1' ORDER BY data DESC");
foreach ($commesse as $commessed) {
    $idcommessa = $commessed['id'];
    $titcommessa = $commessed['titolo'];
    $arrdatacommessa = explode("-", $commessed['data']);
    $datacomm = $arrdatacommessa[2] . "/" . $arrdatacommessa[1] . "/" . $arrdatacommessa[0];
    $arrdaticliente = explode("\n", $commessed['daticliente']);
    $daticliente = $arrdaticliente[0];
    /* inizio delle voci delle select */
    $commesseselect .= "<option disabled value=\"\" style=\"color: #ff0000; font-weight: bolder; padding-top: 5px\">$datacomm - " . str_replace('"', "", json_encode($daticliente)) . " - " . addslashes($titcommessa) . "</option>";
    /* richiamo voci lavorazioni commesse */
    $vociprofitcomm = getDati("commesse_voci", "WHERE idcomm='$idcommessa' AND statovoce = '0' ORDER BY ordine");
    foreach ($vociprofitcomm as $vociprofitcommd) {
        $idrigacommessa = $vociprofitcommd['id'];
        $idprofit = $vociprofitcommd['idprofit'];
        if ($idprofit > 0) {
            /* nome del profit center */
            $profit = getDati("profit_center", "WHERE id='$idprofit'");
            $nomedelprofit = $profit[0]['nome'];
            if (strlen($nomedelprofit) > 0) {
                $nomeprofitprint = "$nomedelprofit - ";
            }
        } else {
            $nomeprofitprint = "";
        }
        /**/
        $titvocecomm = $vociprofitcommd['nome'];
        $descvocecomm = $vociprofitcommd['descr'];
        /* voci select */
        $commesseselect .= "<option value=\"$idrigacommessa\" style=\"padding-left: 20px;\">" . str_replace('"', "", json_encode($daticliente)) . " - " . addslashes("$titcommessa - $nomeprofitprint $titvocecomm") . "</option>";
        $arr_commessevoci[] = array("idvocecomm" => $idrigacommessa, "dativocecomm" => $datacomm . " - " . $daticliente . " - " . $titcommessa . " - " . $nomeprofitprint . " " . $titvocecomm);
    }
}

$idutente = $_SESSION['id'];

if (($_SESSION['livello'] == 0 || $_SESSION['livello'] == 1 || $_SESSION['livello'] == 4)) {
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
} else {
    $utenti = getDati("utenti", "WHERE id=$idutente");
    $utentiprint .= "<option value=\"" . $idutente . "\" selected>" . $utenti[0]['nome'] . " " . $utenti[0]['cognome'] . "</option>";
}
?>

<!-- function modulo -->
<script type="text/javascript" src="./js/functions-ore.js"></script>

<form method="post" action="" id="formore" name="formore">      
    <div class="tit_big">DIPENDENTE</div>
    <select name="idutente" id="idutente" class="input_moduli sizing float_moduli_40 required" onchange="aggiorna(this.value, '');">
        <?php echo $utentiprint ?>
    </select>
    <input type="hidden" name="oredacontratto" id="oredacontratto" class="nopost"  value="" />
    <div class="chiudi"></div>
    <div class="tit_big">DATA E ORE</div>
    <input type="text" name="datap" id="datap" class="input_moduli sizing required float_moduli_small nopost" value="<?php echo DATE("d/m/Y"); ?>" placeholder="Data" title="Data" onchange="aggiorna('', this.value);" />
    <input type="hidden" name="data" id="data" value="<?php echo DATE("Y-m-d"); ?>" />  
    <input type="text" name="entratamattino" id="entratamattino" class="timepickerem input_moduli sizing float_moduli_small" placeholder="Ora entrata mattino" title="Ora entrata mattino" />
    <input type="text" name="uscitamattino" id="uscitamattino" class="timepickerum input_moduli sizing float_moduli_small" placeholder="Ora uscita mattino" title="Ora uscita mattino" disabled />
    <input type="text" name="entratapomeriggio" id="entratapomeriggio" class="timepickerep input_moduli sizing float_moduli_small" placeholder="Ora entrata pomeriggio" title="Ora entrata pomeriggio" />
    <input type="text" name="uscitapomeriggio" id="uscitapomeriggio" class="timepickerup input_moduli sizing float_moduli_small" placeholder="Ora uscita pomeriggio" title="Ora uscita pomeriggio" disabled />
    <div class="chiudi"></div>
    <div class="tit_big">ORE ORDINARIE E STRAORDINARIE</div>
    <input type="text" name="oreordinarie" id="oreordinarie" class="input_moduli sizing required float_moduli_small" placeholder="Ore ordinarie" title="Ore ordinarie" readonly />
    <input type="text" name="orestraordinario" id="orestraordinario" class="input_moduli sizing float_moduli_small" placeholder="Ore straordinario" title="Ore straordinario" readonly />
    <div class="chiudi"></div>
    <div class="tit_big">FERIE E PERMESSI</div>
    <input type="text" name="ferie" id="ferie" class="timepicker2 input_moduli sizing float_moduli_small_10" placeholder="Ore di ferie" title="Ore di ferie" />
    <input type="text" name="permesso" id="permesso" class="timepicker2 input_moduli sizing float_moduli_small_10" placeholder="Ore di permesso" title="Ore di permesso" />
    <input type="text" name="rol" id="rol" class="timepicker2 input_moduli sizing float_moduli_small_10" placeholder="Ore di R.O.L." title="Ore di R.O.L." />
    <input type="text" name="malattia" id="malattia" class="timepicker2 input_moduli sizing float_moduli_small_10" placeholder="Ore di malattia" title="Ore di malattia" />
    <input type="text" name="noteferie" id="noteferie" class="input_moduli sizing float_moduli_50" placeholder="Note ore di ferie e permessi" title="Note ore di ferie e permessi" />
    <div class="chiudi"></div>
    <hr>
    <div class="tit_big">DETTAGLIO ORE LAVORATE</div>
    <div class="contienirighe">
        <div class="riga_prodotto_comm sizing nosortable">
            <div class="ordinarighe"></div>
            <select class="input_moduli sizing float_moduli_40 nopost" placeholder="Commessa" title="Commessa" disabled><option value="">Seleziona lavorazione commessa...</option></select>
            <input type="text" class="input_moduli sizing float_moduli_50 nopost" placeholder="Descrizione" title="Descrizione" disabled />
            <input type="text" class="timepickerlav input_moduli sizing float_moduli_small_10 nopost" placeholder="Ore lavorate" title="Ore lavorate" disabled /><a href="javascript:;" class="add_button"><i style="color: #0a0; line-height: 35px;" class="fa fa-plus fa-lg" aria-hidden="true"></i></a> 
            <div class="chiudi"></div>
        </div>
    </div>    
    <div class="chiudi"></div>
    <input type="text" class="input_moduli sizing float_moduli_small_10 nopost nopost float_rgt margin-r_35" id="oretotalilavorategiorno" placeholder="Totale Ore" title="Totale Ore" readonly />
    <div class="chiudi"></div>    
    <input type="hidden" name="id" id="id" />
    <input type="submit" id="submitformore" value="Salva" class="submit_form nopost" />
    <div class="bottone_chiudi sizing"><a href="javascript:;" class="sizing" onclick="calendario();">Chiudi</a></div>
    <div class="chiudi"></div>
</form>
<script type="text/javascript">
    $(document).ready(function () {
        /* data */
        $.datepicker.setDefaults($.datepicker.regional[ "it" ]);
        $("#datap").datepicker({
            altFormat: "yy-mm-dd",
            altField: "#data"
        });
         /* orario */
        $('input.timepickerem').timepicker({
            timeFormat: 'HH:mm',
            minTime: new Date(0, 0, 0, 6, 0, 0),
            maxTime: new Date(0, 0, 0, 20, 0, 0),
            interval: 30,
            dynamic: false,
            change: function (time) {
                $('#uscitamattino').prop('disabled', false);
                operazioniOre();
            }
        });

        $('input.timepickerep').timepicker({
            timeFormat: 'HH:mm',
            minTime: new Date(0, 0, 0, 6, 0, 0),
            maxTime: new Date(0, 0, 0, 20, 0, 0),
            interval: 30,
            dynamic: false,
            change: function (time) {
                $('#uscitapomeriggio').prop('disabled', false);
                operazioniOre();
            }
        });

        $('input.timepickerum, input.timepickerup').timepicker({
            timeFormat: 'HH:mm',
            minTime: new Date(0, 0, 0, 6, 0, 0),
            maxTime: new Date(0, 0, 0, 20, 0, 0),
            interval: 30,
            dynamic: false,
            change: function (time) {
                operazioniOre();
            }
        });

        $('input.timepicker2').timepicker({
            timeFormat: 'HH:mm',
            minTime: new Date(0, 0, 0, 0, 30, 0),
            maxTime: new Date(0, 0, 0, 8, 0, 0),
            interval: 30,
            dynamic: false,
            change: function (time) {
                operazioniOre();
            }
        });

        /* aggiungo righe commesse */
        /**/
        var maxField = 1000; //Input fields increment limitation
        var addButton = $('.add_button'); //Add button selector
        var wrapper = $('.contienirighe'); //Input field wrapper
        /*var fieldHTML = '<div class="rigaadd riga_prodotto_comm sizing">\n\
         <i class="fa fa-arrows-v fa-lg ordinarighe" aria-hidden="true"></i>\n\
         <select name="idvocecomm[]" class="input_moduli sizing float_moduli_40" placeholder="Commessa" title="Commessa"><option value="0">Seleziona lavorazione commessa...</option><?php echo $commesseselect; ?></select>\n\
         <input type="text" name="descr[]" class="input_moduli sizing float_moduli_50" placeholder="Descrizione" title="Descrizione" />\n\
         <input type="text" name="orelavorate[]" class="orelav timepickerlav input_moduli sizing float_moduli_small_10" placeholder="Ore lavorate" title="Ore lavorate" value="0" /><a href="javascript:;" class="remove_button"><i style="color: #CD0A0A; line-height: 35px;" class="fa fa-times fa-lg" aria-hidden="true"></i></a>\n\
         <div class="chiudi"></div>\n\
         </div>';*/ //New input field html 
        var fieldHTML = '<div class="rigaadd riga_prodotto_comm sizing">\n\
            <i class="fa fa-arrows-v fa-lg ordinarighe" aria-hidden="true"></i>\n\
            <input type="text" name="dettvocecomm[]" class="cercavocicomm input_moduli sizing float_moduli_40" placeholder="Cerca lavorazione" title="Cerca lavorazione" />\n\
            <input type="hidden" name=\"idvocecomm[]\" />\n\
            <input type="text" name="descr[]" class="input_moduli sizing float_moduli_50" placeholder="Descrizione" title="Descrizione" />\n\
            <input type="text" name="orelavorate[]" class="orelav timepickerlav input_moduli sizing float_moduli_small_10" placeholder="Ore lavorate" title="Ore lavorate" value="0" /><a href="javascript:;" class="remove_button"><i style="color: #CD0A0A; line-height: 35px;" class="fa fa-times fa-lg" aria-hidden="true"></i></a>\n\
            <div class="chiudi"></div>\n\
        </div>';
        var x = 1; //Initial field counter is 1
        $(addButton).click(function () { //Once add button is clicked
            if (x < maxField) { //Check maximum number of input fields
                x++; //Increment field counter
                $(wrapper).append(fieldHTML); // Add field html
            }
            $('input.timepickerlav').timepicker({
                timeFormat: 'HH:mm',
                minTime: new Date(0, 0, 0, 0, 15, 0),
                maxTime: new Date(0, 0, 0, 12, 0, 0),
                interval: 15,
                dynamic: false,
                change: function (time) {
                    totaleOre();
                }
            });


            /**/
            var dativocicommesse = <?php echo json_encode($arr_commessevoci); ?>;
            var vocicomm = $.map(dativocicommesse, function (item) {
                return {
                    label: item.dativocecomm,
                    id: item.idvocecomm
                };
            });
            $(".cercavocicomm").autocomplete({
                source: vocicomm,
                select: function (event, ui) {
                    $(this).next('input').val(ui.item.id);
                    /**/
                }
            });
            /**/
        });
        $(wrapper).on('click', '.remove_button', function (e) { //Once remove button is clicked
            if (confirm("Stai per eliminare il contenuto, vuoi continuare?")) {
                e.preventDefault();
                $(this).parent('div').remove(); //Remove field html
                x--; //Decrement field counter
                totaleOre();
            }
        });

        $('.contienirighe').on('click', function () {
            /* modifica della quantità */
            $('.cercavocicomm').unbind('keyup').on('keyup', function () {
                if (this.value === "") {
                    $(this).next('input').val("");
                }
            });
        });

    });
</script>