<?php
include '../library/controllo.php';
include '../library/config.php';
include '../library/connessione.php';
include '../library/basic.class.php';
include '../library/functions.php';
include '../library/dipendenti.class.php';

$livello = $_GET['livello'];
/* elenco atelier */
if ($_SESSION["livello"] == 0 || $_SESSION["livello"] == 1) {
    /* diretto / affiliato */
    $dir_aff = " <select name=\"diraff\" id=\"diraff\" class=\"input_moduli float_moduli required soloatelier\">
        <option value=\"\">Seleziona diretto/affiliato</option>
        <option value=\"d\">Diretto</option>
        <option value=\"a\">Affiliato</option>
    </select>";

    $atelier = getDati("utenti", "WHERE livello = '5' AND attivo = 1");
    $nomeelencoatelier = " <option value=\"\">Seleziona Atelier</option>";
} else {
    $atelier = getDati("utenti", "WHERE id = " . $_SESSION['id'] . "");
}
foreach ($atelier as $atelierd) {
    $nomeelencoatelier .= "<option value=\"" . $atelierd['id'] . "\">" . $atelierd['nominativo'] . "</option>";
}


/* nazioni, regioni, province, cap */
/* nazioni */
$nazioniprint = "<option value=\"\">Seleziona nazione</option>";
$nazioni = getNazioni();
foreach ($nazioni as $nazionid) {
    $nazioniprint .= "<option value=\"" . $nazionid['tld'] . "\">" . $nazionid['nazione'] . "</option>";
}
/* regioni */
$regioniprint = "<option value=\"\">Seleziona regione</option>";
$regioni = getRegione();
foreach ($regioni as $regionid) {
    $regioniprint .= "<option value=\"" . $regionid['regione'] . "\">" . $regionid['regione'] . "</option>";
}
$submit = "";
if (isset($_POST['submit'])) {
    $submit = $_POST['submit'];
}
if (isset($_GET['id'])) {
    $cols = getDati("utenti", "where id=" . $_GET['id'] . " limit 1;")[0];
    //var_dump($cols);
}
/* province */
switch ($submit) {
    case "setprovince":
        $regione = $_POST['regione'];
        $provinceprint = "<option value=\"\">Seleziona provincia</option>";
        $province = getProvincia($regione);
        foreach ($province as $provinced) {
            $provinceprint .= "<option value=\"" . $provinced['sigla'] . "\">" . $provinced['provincia'] . "</option>";
        }
        die('{"msg" : ' . json_encode($provinceprint) . '}');
        break;

    case "setcomune":
        $provincia = $_POST['provincia'];
        $comuneprint = "<option value=\"\">Seleziona comune</option>";
        $comune = getComune($provincia);
        foreach ($comune as $comuned) {
            $comuneprint .= "<option value=\"" . $comuned['comune'] . "\">" . $comuned['comune'] . "</option>";
        }
        die('{"msg" : ' . json_encode($comuneprint) . '}');
        break;

    case "setcap":
        $comune = $_POST['comune'];
        $capprint = "<option value=\"\">Seleziona cap</option>";
        $cap = getCap($comune);
        foreach ($cap as $capd) {
            $capprint .= "<option value=\"" . $capd['cap'] . "\">" . $capd['cap'] . "</option>";
        }
        die('{"msg" : ' . json_encode($capprint) . '}');
        break;
}
?>
<style>
    .ui-timepicker-container{
        z-index:9999999999 !important;
    }
</style>
<link rel='stylesheet' href='./js/fullcalendar-3.10.0/fullcalendar.css' />
<script src='./js/fullcalendar-3.10.0/lib/moment.min.js'></script>
<script src='./js/fullcalendar-3.10.0/fullcalendar.js'></script>
<script src='./js/fullcalendar-3.10.0/locale/it.js'></script>
<!-- jquery confirm master -->
<link rel="stylesheet" href="./js/jquery-confirm-master/css/jquery-confirm.css">
<script src="./js/jquery-confirm-master/dist/jquery-confirm.min.js"></script>
<script type="text/javascript" src="./js/fineuploader/client/fileuploader.js"></script>
<link type="text/css" href="./js/fineuploader/client/fileuploader.css" rel="Stylesheet" />
<script type="text/javascript">
    /* set provincia */
    function setProvincia(regione, selezionata, ident) {
        // ident = 1 indirizzo per fatturazione
        // ident = 2 indirizzo alternativo per spedizione
        $.ajax({
            type: "POST",
            url: "./form/form-dipendenti.php",
            data: "regione=" + regione + "&submit=setprovince",
            dataType: "json",
            success: function (msg)
            {
                if (ident === '1') {
                    $('#provincia').html('');
                    $('#comune').html('');
                    $('#cap').html('');
                    $('#provincia').append(msg.msg).val(selezionata);
                } else if (ident === '2') {
                    $('#provinciaspedizione').html('');
                    $('#comunespedizione').html('');
                    $('#capspedizione').html('');
                    $('#provinciaspedizione').append(msg.msg).val(selezionata);
                }
            }
        });
    }
    /* set comune */
    function setComune(provincia, selezionata, ident) {
        // ident = 1 indirizzo per fatturazione
        // ident = 2 indirizzo alternativo per spedizione
        $.ajax({
            type: "POST",
            url: "./form/form-dipendenti.php",
            data: "provincia=" + provincia + "&submit=setcomune",
            dataType: "json",
            success: function (msg)
            {
                if (ident === '1') {
                    $('#comune').html('');
                    $('#cap').html('');
                    $('#comune').append(msg.msg).val(selezionata);
                } else if (ident === '2') {
                    $('#comunespedizione').html('');
                    $('#capspedizione').html('');
                    $('#comunespedizione').append(msg.msg).val(selezionata);
                }
            }
        });
    }
    /* set comune */
    function setCap(comune, selezionata, ident) {
        // ident = 1 indirizzo per fatturazione
        // ident = 2 indirizzo alternativo per spedizione
        $.ajax({
            type: "POST",
            url: "./form/form-dipendenti.php",
            data: "comune=" + comune + "&submit=setcap",
            dataType: "json",
            success: function (msg)
            {
                if (ident === '1') {
                    $('#cap').html('');
                    $('#cap').append(msg.msg).val(selezionata);
                } else if (ident === '2') {
                    $('#capspedizione').html('');
                    $('#capspedizione').append(msg.msg).val(selezionata);
                }
            }
        });
    }
</script>

<script type="text/javascript" src="./js/functions-dipendenti.js"></script>
<form method="post" action="" id="formdipendenti" name="formdipendenti">    
    <input type="hidden" value="" id="livello" name="livello" />
    <div class="tit_big">DATI</div>
    <input type="text" name="nominativo" id="nominativo" class="input_moduli sizing  float_moduli required soloatelier" placeholder="Nominativo Atelier" title="Nominativo Atelier" />
    <input type="text" name="nome" id="nome" class="input_moduli sizing  float_moduli required" placeholder="Nome" title="Nome" />            
    <input type="text" name="cognome" id="cognome" class="input_moduli sizing  float_moduli required" placeholder="Cognome" title="Cognome" />
    <?php if ($livello == 3 || $cols['livello'] == '3') { ?>
        <div class="chiudi"></div>
        <select name="stato_dipendente" id="stato_dipendente" class="input_moduli float_moduli_small">
            <option value="0">Stato utente</option>
            <option value="1">Formatore</option>
            <option value="2">In formazione</option>
            <option value="3">Superadmin</option>
        </select>
        <select name="ruolo" id="ruolo" class="input_moduli float_moduli_small">
            <?php
            $ruoli = getDati("ruolo", "order by id;");
            ?>
            <?php foreach ($ruoli as $ruolo) { ?>
                <option value="<?= $ruolo['id'] ?>"><?= $ruolo['valore'] ?></option>
            <?php } ?>
        </select>
    <?php } ?>
    <?php echo $dir_aff; ?>
    <div class="chiudi"></div>
    <div class="soloatelier">
        <div class="tit_big">DATI FISCALI</div>
        <input type="text" name="ragionesociale" id="ragionesociale" class="input_moduli sizing  float_moduli soloatelier" placeholder="Ragione sociale Atelier" title="Ragione sociale Atelier" />
        <textarea name="sedelegale" id="sedelegale" class="textarea_moduli sizing  float_moduli" placeholder="Indirizzo Sede Legale" title="Indirizzo Sede Legale"></textarea>     
        <textarea name="datipagamento" id="datipagamento" class="textarea_moduli sizing  float_moduli" placeholder="Dati pagamento" title="Dati pagamento"></textarea>  
        <?php if ($livello == 5 || $cols['livello'] == 5) { //atelier?>
            <input type="text" name="codicefiscale" id="codicefiscale" class="input_moduli sizing  float_moduli soloatelier" placeholder="Codice fiscale Atelier" title="Codice fiscale Atelier" />
        <?php } ?>
        <input type="text" name="piva" id="piva" class="input_moduli sizing  float_moduli soloatelier" placeholder="P.iva Atelier" title="P.iva Atelier" />
        <div class="chiudi">
    <!--    <textarea name="datipagamento" id="datipagamento" class="textarea_moduli sizing  float_moduli" placeholder="Dati pagamento" title="Dati pagamento"></textarea>    
        <div class="chiudi"></div>-->
        </div>
    </div>
    <?php if ($livello == 3 || $cols['livello'] == 3) { //dipendente?>
        <div class="tit_big">LOGIN</div>
        <input type="text" name="email" id="email" class="input_moduli sizing float_moduli" placeholder="Email" title="Email" /> 
    <!--        <input type="text" name="username" id="username" class="input_moduli sizing float_moduli" placeholder="Username" title="Username" autocomplete="off" />-->
        <input type="password" name="password" id="password" class="input_moduli sizing float_moduli required" placeholder="Password" title="Password" autocomplete="off" />
        <div class="chiudi"></div>
        <div class="tit_big">INFO GENERALI</div>
        <input type="text" name="data_nascita" id="data_nascita" class="input_moduli sizing float_moduli float_moduli_small_25" placeholder="Data nascita" title="Data nascita" />
        <?php
        $province_all = getProvinciaAll();
        $comune_all = getComuneAll();
        ?>
        <select name="provincia_nascita" id="provincia_nascita" class="extra input_moduli float_moduli_small">
            <option value="">Seleziona provincia nascita</option>
            <?php foreach ($province_all as $prov) { ?>
                <option value="<?= $prov['sigla'] ?>"><?= $prov['provincia'] ?></option>
            <?php } ?>
        </select>
        <input type="text" name="comune_nascita" id="comune_nascita" class="input_moduli sizing float_moduli float_moduli_small_25" placeholder="Comune di nascita" title="Comune di nascita" />
        <input type="text" name="codicefiscale" id="codicefiscale" class="input_moduli sizing float_moduli float_moduli_small_25" placeholder="Codice fiscale" title="Codice fiscale" />
        <input type="text" name="iban" id="iban" class="input_moduli sizing float_moduli float_moduli_small_25" placeholder="Iban" title="Iban" />
        <input type="text" name="cellulare" id="cellulare" class="input_moduli sizing float_moduli float_moduli_small_25" placeholder="Cellulare" title="Cellulare" />
    <?php } else { //atelier ?>
        <div class="tit_big">LOGIN</div>
        <input type="text" name="email" id="email" class="input_moduli sizing float_moduli" placeholder="Email" title="Email" /> 
    <!--        <input type="text" name="username" id="username" class="input_moduli sizing required float_moduli" placeholder="Username" title="Username" autocomplete="off" />    -->
        <input type="password" name="password" id="password" class="input_moduli sizing float_moduli required" placeholder="Password" title="Password" autocomplete="off" />
        <div class="chiudi"></div>
        <div class="tit_big">DATI DI CONTATTO</div>          
        <input type="text" name="telefono" id="telefono" class="input_moduli sizing  float_moduli" placeholder="Telefono" title="Telefono" />
        <input type="text" name="cellulare" id="cellulare" class="input_moduli sizing  float_moduli" placeholder="Cellulare" title="Cellulare" />
    <?php } ?>

    <div class="chiudi"></div> 
    <div class="tit_big">INDIRIZZO</div>
    <input type="text" name="indirizzo" id="indirizzo" class="input_moduli sizing  float_moduli" placeholder="Indirizzo" title="Indirizzo" />
    <div class="chiudi"></div>
    <select name="nazione" id="nazione" class="extra input_moduli float_moduli_small" onchange="setNazione($(this).val(), '1', 'Seleziona comune', 'Seleziona cap');">
        <?php echo $nazioniprint; ?>
    </select>
    <select name="regione" id="regione" class="extra input_moduli float_moduli_small" onchange="setProvincia($(this).val(), '', '1');">
        <?php echo $regioniprint; ?>
    </select>
    <select name="provincia" id="provincia" class="extra input_moduli float_moduli_small" onchange="setComune($(this).val(), '', '1');">
        <option value="">Seleziona provincia</option>
    </select>
    <select name="comune" id="comune" class="extra input_moduli float_moduli_small" onchange="setCap($(this).val(), '', '1');">
        <option value="">Seleziona comune</option>
    </select>
    <select name="cap" id="cap" class="extra input_moduli float_moduli_small">
        <option value="">Seleziona cap</option>
    </select>     
    <div class="chiudi"></div>  
    <select name="idatelier" id="idatelier" class="extra input_moduli float_moduli_small dipatelier">
        <?php echo $nomeelencoatelier; ?>
    </select>
    <select name="attivo" id="attivo" class="input_moduli float_moduli_small required">
        <option value="">Attiva/Disattiva</option>
        <option value="0">Disattivo</option>
        <option value="1">Attivo</option>
    </select>  
    <?php if ($_SESSION["livello"] == 0 || $_SESSION["livello"] == 1) { ?>
        <input type="checkbox" id="solo_sartoria" name="solo_sartoria" value="1" /> Solo sartoria
    <?php } ?>
    <?php if (($livello == 3 || $cols['livello'] == 3) && ($_SESSION["livello"] == 0 || $_SESSION["livello"] == 1)) { ?>
        <input type="checkbox" id="no_mrage" name="no_mrage" value="1" /> Non pu&ograve; accedere a Mr. Age
    <?php } ?>
    <?php if (($livello == 3 || $cols['livello'] == 3) && ($_SESSION["livello"] == 0 || $_SESSION["livello"] == 1)) { ?>
        <input type="checkbox" id="atelier_all" name="atelier_all" value="1" /> Vedi tutti gli atelier su applicazione<div class="chiudi"></div>
        <div class="tit_big">RETRIBUZIONE</div>
        <input type="text" name="ore_settimana" id="ore_settimana" class="input_moduli sizing float_moduli float_moduli_small_25" placeholder="Ore settimana" title="Ore settimana" />
        <input type="text" name="stipendio_netto" id="stipendio_netto" class="input_moduli sizing float_moduli float_moduli_small_25" placeholder="Stipendio netto" title="Stipendio netto" />
        <div class="chiudi"></div>
        <?php for ($i = 1; $i <= 5; $i++) { ?>
            <input type="text" name="data_inizio_r[]" class="input_moduli sizing float_moduli float_moduli_small_25 isDate" placeholder="Data inizio" title="Data inizio" />
            <input type="text" name="retribuzione[]" class="input_moduli sizing float_moduli float_moduli_small_25" placeholder="Retribuzione" title="Retribuzione" />
            <input type="text" name="data_fine_r[]" class="input_moduli sizing float_moduli float_moduli_small_25 isDate" placeholder="Data fine" title="Data fine" />
            <div class="chiudi"></div>
        <?php } ?>
    <?php } ?>
    <div class="chiudi"></div>
    <div class="orariaperture sizing">
        <div class="soloatelier">
            <div class="tit_big">APERTURE</div>
            <input type="checkbox" id="apert_0" name="apert[]" value="0" /> Domenica <input type="text" name="inizio0" id="inizio0" class="input_moduli sizing float_moduli_small_10 timepicker3" placeholder="Inizio mattino" title="Inizio mattino" /> <input type="text" name="fine0" id="fine0" class="input_moduli sizing float_moduli_small_10 timepicker3" placeholder="Fine mattino" title="Fine mattino" /> <input type="text" name="iniziop0" id="iniziop0" class="input_moduli sizing float_moduli_small_10 timepicker3" placeholder="Inizio pomeriggio" title="Inizio pomeriggio" /> <input type="text" name="finep0" id="finep0" class="input_moduli sizing float_moduli_small_10 timepicker3" placeholder="Fine pomeriggio" title="Fine pomeriggio" /> <input type="text" name="addetti0" id="addetti0" class="input_moduli sizing float_moduli_small_5" placeholder="Addetti" title="Addetti" />
            <div class="chiudi"></div>
            <input type="checkbox" id="apert_1" name="apert[]" value="1" /> Lunedì <input type="text" name="inizio1" id="inizio1" class="input_moduli sizing float_moduli_small_10 timepicker3" placeholder="Inizio mattino" title="Inizio mattino" /> <input type="text" name="fine1" id="fine1" class="input_moduli sizing float_moduli_small_10 timepicker3" placeholder="Fine mattino" title="Fine mattino" /> <input type="text" name="iniziop1" id="iniziop1" class="input_moduli sizing float_moduli_small_10 timepicker3" placeholder="Inizio pomeriggio" title="Inizio pomeriggio" /> <input type="text" name="finep1" id="finep1" class="input_moduli sizing float_moduli_small_10 timepicker3" placeholder="Fine pomeriggio" title="Fine pomeriggio" /> <input type="text" name="addetti1" id="addetti1" class="input_moduli sizing float_moduli_small_5" placeholder="Addetti" title="Addetti" />
            <div class="chiudi"></div>
            <input type="checkbox" id="apert_2" name="apert[]" value="2" /> Martedì <input type="text" name="inizio2" id="inizio2" class="input_moduli sizing float_moduli_small_10 timepicker3" placeholder="Inizio mattino" title="Inizio mattino" /> <input type="text" name="fine2" id="fine2" class="input_moduli sizing float_moduli_small_10 timepicker3" placeholder="Fine mattino" title="Fine mattino" /> <input type="text" name="iniziop2" id="iniziop2" class="input_moduli sizing float_moduli_small_10 timepicker3" placeholder="Inizio pomeriggio" title="Inizio pomeriggio" /> <input type="text" name="finep2" id="finep2" class="input_moduli sizing float_moduli_small_10 timepicker3" placeholder="Fine pomeriggio" title="Fine pomeriggio" /> <input type="text" name="addetti2" id="addetti2" class="input_moduli sizing float_moduli_small_5" placeholder="Addetti" title="Addetti" />
            <div class="chiudi"></div>
            <input type="checkbox" id="apert_3" name="apert[]" value="3" /> Mercoledì <input type="text" name="inizio3" id="inizio3" class="input_moduli sizing float_moduli_small_10 timepicker3" placeholder="Inizio mattino" title="Inizio mattino" /> <input type="text" name="fine3" id="fine3" class="input_moduli sizing float_moduli_small_10 timepicker3" placeholder="Fine mattino" title="Fine mattino" /> <input type="text" name="iniziop3" id="iniziop3" class="input_moduli sizing float_moduli_small_10 timepicker3" placeholder="Inizio pomeriggio" title="Inizio pomeriggio" /> <input type="text" name="finep3" id="finep3" class="input_moduli sizing float_moduli_small_10 timepicker3" placeholder="Fine pomeriggio" title="Fine pomeriggio" /> <input type="text" name="addetti3" id="addetti3" class="input_moduli sizing float_moduli_small_5" placeholder="Addetti" title="Addetti" />
            <div class="chiudi"></div>
            <input type="checkbox" id="apert_4" name="apert[]" value="4" /> Giovedì <input type="text" name="inizio4" id="inizio4" class="input_moduli sizing float_moduli_small_10 timepicker3" placeholder="Inizio mattino" title="Inizio mattino" /> <input type="text" name="fine4" id="fine4" class="input_moduli sizing float_moduli_small_10 timepicker3" placeholder="Fine mattino" title="Fine mattino" /> <input type="text" name="iniziop4" id="iniziop4" class="input_moduli sizing float_moduli_small_10 timepicker3" placeholder="Inizio pomeriggio" title="Inizio pomeriggio" /> <input type="text" name="finep4" id="finep4" class="input_moduli sizing float_moduli_small_10 timepicker3" placeholder="Fine pomeriggio" title="Fine pomeriggio" /> <input type="text" name="addetti4" id="addetti4" class="input_moduli sizing float_moduli_small_5" placeholder="Addetti" title="Addetti" />
            <div class="chiudi"></div>
            <input type="checkbox" id="apert_5" name="apert[]"value="5" /> Venerdì <input type="text" name="inizio5" id="inizio5" class="input_moduli sizing float_moduli_small_10 timepicker3" placeholder="Inizio mattino" title="Inizio mattino" /> <input type="text" name="fine5" id="fine5" class="input_moduli sizing float_moduli_small_10 timepicker3" placeholder="Fine mattino" title="Fine mattino" /> <input type="text" name="iniziop5" id="iniziop5" class="input_moduli sizing float_moduli_small_10 timepicker3" placeholder="Inizio pomeriggio" title="Inizio pomeriggio" /> <input type="text" name="finep5" id="finep5" class="input_moduli sizing float_moduli_small_10 timepicker3" placeholder="Fine pomeriggio" title="Fine pomeriggio" /> <input type="text" name="addetti5" id="addetti5" class="input_moduli sizing float_moduli_small_5" placeholder="Addetti" title="Addetti" />
            <div class="chiudi"></div>
            <input type="checkbox" id="apert_6" name="apert[]" value="6" /> Sabato <input type="text" name="inizio6" id="inizio6" class="input_moduli sizing float_moduli_small_10 timepicker3" placeholder="Inizio mattino" title="Inizio mattino" /> <input type="text" name="fine6" id="fine6" class="input_moduli sizing float_moduli_small_10 timepicker3" placeholder="Fine mattino" title="Fine mattino" /> <input type="text" name="iniziop6" id="iniziop6" class="input_moduli sizing float_moduli_small_10 timepicker3" placeholder="Inizio pomeriggio" title="Inizio pomeriggio" /> <input type="text" name="finep6" id="finep6" class="input_moduli sizing float_moduli_small_10 timepicker3" placeholder="Fine pomeriggio" title="Fine pomeriggio" /> <input type="text" name="addetti6" id="addetti6" class="input_moduli sizing float_moduli_small_5" placeholder="Addetti" title="Addetti" />
            <div class="chiudi"></div>
            <input type="text" name="addetti" id="addetti" class="input_moduli sizing required float_moduli_small_10" placeholder="Numero addetti" title="Numero addetti" />
            <?php if ($_SESSION["livello"] == 0 || $_SESSION["livello"] == 1 || $_SESSION["id"] == 107) { ?>
                <input type="text" name="patrono" id="patrono" class="input_moduli sizing required float_moduli_small_10" placeholder="Patrono: mese-giorno es. 11-24" title="Patrono" />
            <?php } ?>
            <input type="checkbox" id="online" name="online" value="1" /> Online
            <input type="text" name="data_apertura" id="data_apertura" class="input_moduli sizing float_moduli_small_10" placeholder="Data di apertura: es.13/05/2023" title="Data di apertura" />
            <input type="text" name="aperture_spot" id="aperture_spot" class="input_moduli sizing float_moduli_small_10" placeholder="Aperture spot es.13/05/2023,02/06/2023" title="Aperture spot sep. da virgola" />
            <div class="chiudi"></div>
            <input type="text" name="chiusure_spot" id="chiusure_spot" class="input_moduli sizing float_moduli_small_10" placeholder="Chiusure spot es.13/05/2023,02/06/2023" title="Chiusure spot sep. da virgola" />
            <input type="text" name="chiuso_dal" id="chiuso_dal" class="input_moduli sizing float_moduli_small_10" placeholder="Chiuso dal" title="Chiuso dal" />
            <input type="text" name="chiuso_al" id="chiuso_al" class="input_moduli sizing float_moduli_small_10" placeholder="Chiuso al" title="Chiuso al" />
            <?php if ($_SESSION["livello"] == 0 || $_SESSION["livello"] == 1 || $_SESSION["id"] == 107) { ?>
                <input type="checkbox" id="non_gestito" name="non_gestito" value="1" /> Non gestito da centralino
            <?php } ?>
            <div class="chiudi"></div>
            <input type="text" name="date_trunk" id="date_trunk" class="input_moduli sizing float_moduli_small_10" placeholder="Date Trunk Show es.13/05/2023,02/06/2023" title="Date Trunk Show sep. da virgola" />
            <input type="text" name="orari_trunk" id="orari_trunk" class="input_moduli sizing float_moduli_small_10" placeholder="Orario Trunk Show es.10-18" title="Orario Trunk Show es.10-18" />
            <input type="number" name="app_trunk" id="app_trunk" class="input_moduli sizing float_moduli_small_10" placeholder="N. appuntamenti/ora Trunk Show" title="N. appuntamenti/ora Trunk Show" />
            <input type="text" name="titolo_trunk" id="titolo_trunk" class="input_moduli sizing float_moduli_small_15" placeholder="Titolo Trunk Show" title="Titolo Trunk Show" />
            <div class="chiudi"></div>
        </div>
    </div>
    <input type="hidden" name="id" id="id" />
    <input type="submit" id="submitformdipendenti" value="Salva" class="submit_form nopost" />
    <div class="bottone_chiudi sizing"><a href="javascript:;" class="sizing" onclick="chiudiDipendenti();">Chiudi</a></div>
    <div class="chiudi"></div>
</form>
<?php
if ($cols["livello"] == "5" && $_SESSION["diraff"] == 'd' && ($_SESSION["livello"] == 0 || $_SESSION["livello"] == 1 || $_SESSION["livello"] == 5)) {
    $dipendenti = getDipendentiAtelier($_GET['id']);
    ?>
    <div class="dipendenti sizing">
        <div class="soloatelier">
            <div class="tit_big">CALENDARIO TURNI DIPENDENTI</div>
            <div style="font-size: 1.3em;margin:20px 0;"><a class="addCalendarDip btn-inserimento" href="javascript:;"><i class="fa fa-plus-circle" aria-hidden="true"></i> Nuovo inserimento</a></div>
            <?php foreach ($dipendenti as $dip) { ?>
                <div style="margin:10px 0;"><?= $dip['cognome'] . ' ' . $dip['nome'] ?>: <span style="font-weight:bold;"><?= floatval($dip['ore_settimana']) ?> </span> ORE SETTIMANA - <b>ORE INSERITE: </b><span style="font-weight: bold;" id="ore-<?= $dip['id'] ?>">0</span></div>
            <?php } ?>
            <div id="calendarDipendenti"></div>
        </div>
    </div>
<?php } ?>
<?php if ($cols["livello"] == "5" && ($_SESSION["livello"] == 0 || $_SESSION["livello"] == 1)) { ?>
    <div class="dipendenti sizing">
        <div class="soloatelier">
            <div class="tit_big">LOG ACCESSI/OPERAZIONI</div>
            <div id="jsGridLog"></div>
        </div>
    </div>
<?php } ?>
<?php if ($_GET["id"] != "") { ?>
    <div class="dipendenti sizing">
        <div class="soloatelier">
            <div class="tit_big">CALENDARIO ADDETTI AGGIUNTIVI</div>
            <div style="font-size: 1.3em;margin:20px 0;"><a class="addCalendar btn-inserimento" href="javascript:;"><i class="fa fa-plus-circle" aria-hidden="true"></i> Nuovo inserimento</a></div>
            <div id="calendarAddetti"></div>
        </div>
    </div>
<?php } ?>
<?php if ($_GET["id"] != "" && $cols["livello"] == "3" && ($_SESSION["livello"] == 0 || $_SESSION["livello"] == 1)) { ?>
    <div class="dipendenti sizing">
        <div class="soloatelier">
            <div class="tit_big">ATELIER COLLEGATI</div>
            <div id="jsGridAtelier"></div>
        </div>
    </div>
<?php } ?>
<?php if ($cols["livello"] == "5" && $_GET["id"] != "") { ?>
    <div class="chiudi" style="height: 100px;"></div>
    <div class="dipendenti sizing">
        <div class="">
            <div class="tit_big">DOCUMENTI ATELIER</div>
            <div class="dipendenti">
                <div class="sizing">
                    <div style="font-size: 1.3em;margin:20px 0;"><a class="addMedia" href="javascript:;" onclick="$('#addCartella').slideToggle('slow');"><i class="fa fa-plus-circle" aria-hidden="true"></i> Nuova cartella</a></div>
                    <div id="addCartella" style="display: none;">
                        <form method="post" id="form-cartella">
                            <input type="text" name="titolo" id="titolo" class="input_moduli sizing float_moduli required" placeholder="Titolo cartella" title="Titolo cartella" value="" />
                            <div class="chiudi"></div>
                            <input type="hidden" name="idatelier_media" id="idatelier_media" value="<?= $_GET['id'] ?>" />
                            <input type="hidden" name="idfather" id="idfather" value="0" />
                            <input type="submit" id="submitformcartella" value="Salva" class="submit_form nopost" />
                            <div class="chiudi" style="height: 100px;"></div>
                        </form>
                    </div>
                    <div style="font-size: 1.3em;margin:20px 0;"><a class="addMedia" href="javascript:;" onclick="$('#addMedia').slideToggle('slow');"><i class="fa fa-plus-circle" aria-hidden="true"></i> Nuovo documento/video</a></div>
                    <div id="addMedia" style="display: none;">
                        <form method="post" action="" id="form-media">
                            <input type="text" name="titolo" id="titolo" class="input_moduli sizing float_moduli required" placeholder="Titolo allegato" title="Titolo allegato" value="" />
                            <div class="chiudi"></div>
                            <div id="file-media">
                                <noscript>
                                <p>Please enable JavaScript to use file uploader.</p>
                                <!-- or put a simple form for upload here -->
                                </noscript>                                
                            </div>
                            <input type="hidden" name="idatelier_media" id="idatelier_media" value="<?= $_GET['id'] ?>" />
                            <input type="hidden" name="idfather" id="idfather" value="0" />
                            <input type="hidden" name="media" id="media" value="" />
                            <input type="submit" id="submitformmedia" value="Salva" class="submit_form nopost" />
                            <div class="chiudi" style="height: 100px;"></div>
                        </form>
                    </div>
                    <div id="cnt-navbar" style="margin:10px 0;padding: 10px 0;"><a href="javascript:;" id="back-navbar" data-idatelier="<?= $_GET['id'] ?>" data-idcartella="0"><i class="fa fa-arrow-left fa-lg" aria-hidden="true"></i></a><span data-idfather="0">  Home :: </span></div>
                    <div id="cnt-cartelle">
                        <?php
                        $folders = dipendenti::getCartelleFilesAtelier($_GET['id'], 0, 1);
                        $files = dipendenti::getCartelleFilesAtelier($_GET['id'], 0, 0);
                        foreach ($folders as $folder) {
                            ?>
                            <div class="folder" id="riga_<?= $folder['id'] ?>">
                                <i class="fa fa-folder fa-lg" aria-hidden="true"></i><a href="javascript:;" class="btn-cartella" data-idfather="0" data-idcartella="<?= $folder['id'] ?>" data-idatelier="<?= $_GET['id'] ?>"> <span id="titolo<?= $folder['id'] ?>"><?= $folder['titolo'] ?></span></a>
                                <div class="ico-folder-docs">
                                    <a href="javascript:;" class="editFolder" data-idfolder="<?= $folder['id'] ?>"><i class="fa fa-edit fa-lg" aria-hidden="true"></i></a>
                                    <a href="javascript:;" class="delFolder" data-idfolder="<?= $folder['id'] ?>"><i class="fa fa-trash fa-lg" aria-hidden="true"></i></a>
                                </div>
                            </div>    
                            <?php
                        }
                        foreach ($files as $file) {
                            ?>
                            <div class="folder" id="riga_<?= $file['id'] ?>">
                                <i class="fa fa-file fa-lg" aria-hidden="true"></i><a href="/<?= FOLDER_UTENTI ?>/<?= $file['idatelier'] ?>/<?= $file['idfather'] ?>/<?= $file['nomefile'] ?>" target="_blank" class="btn-file" data-idfather="0" data-idcartella="<?= $file['id'] ?>" data-idatelier="<?= $_GET['id'] ?>"> <span id="titolo<?= $file['id'] ?>"><?= $file['titolo'] ?></span></a>
                                <div class="ico-folder-docs">
                                    <a href="javascript:;" class="editFolder" data-idfolder="<?= $file['id'] ?>"><i class="fa fa-edit fa-lg" aria-hidden="true"></i></a>
                                    <a href="javascript:;" class="delFolder" data-idfolder="<?= $file['id'] ?>"><i class="fa fa-trash fa-lg" aria-hidden="true"></i></a>
                                </div>
                            </div>    
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="chiudi" style="height: 100px;"></div>
<?php } ?>
<?php if ($cols["livello"] == "3" && $_GET["id"] != "") { ?>
    <div class="dipendenti sizing">
        <div class="">
            <div class="tit_big">DOCUMENTI</div>
            <div class="dipendenti">
                <div class="sizing">
                    <div style="font-size: 1.3em;margin:20px 0;"><a class="addMedia btn-inserimento" href="javascript:;" onclick="$('#addMedia').slideToggle('slow');"><i class="fa fa-plus-circle" aria-hidden="true"></i> Nuovo inserimento</a></div>
                    <div id="addMedia" style="display: none;">
                        <form method="post" action="" id="form-media">
                            <input type="text" name="titolo" id="titolo" class="input_moduli sizing float_moduli required" placeholder="Titolo allegato" title="Titolo allegato" value="" />
                            <div class="chiudi"></div>
                            <div id="file-media">
                                <noscript>
                                <p>Please enable JavaScript to use file uploader.</p>
                                <!-- or put a simple form for upload here -->
                                </noscript>                                
                            </div>
                            <input type="hidden" name="idutente" id="idutente" value="<?= $_GET['id'] ?>" />
                            <input type="hidden" name="media" id="media" value="" />
                            <input type="submit" id="submitformmedia" value="Salva" class="submit_form nopost" />
                            <div class="chiudi" style="height: 100px;"></div>
                        </form>
                    </div>
                    <div id="jsGridAllegati"></div>
                </div>
            </div>
        </div>
    </div>
<?php } ?>
<?php if ($cols["livello"] == "3" && $_GET["id"] != "") { ?>
    <div class="dipendenti sizing">
        <div class="">
            <div class="tit_big">ATELIER GESTITI</div>
            <div id="jsGridDipAtelier"></div>
        </div>
    </div>
<?php } ?>
<?php if ($cols["livello"] == "3" && $cols["stato_dipendente"] == 1 && $_GET["id"] != "") { ?>
    <div class="dipendenti sizing">
        <div class="">
            <div class="tit_big">RISORSE GESTITE</div>
            <div id="jsGridDipGest"></div>
        </div>
    </div>
<?php } ?>
<script type="text/javascript">
    $(document).ready(function () {
        $.datepicker.setDefaults($.datepicker.regional[ "it" ]);
        $("#data_cal, #data_apertura,#chiuso_dal,#chiuso_al,#data_nascita,#caldip_data_dal,#caldip_data_al, .isDate").datepicker({
            changeYear: true,
            yearRange: "1950:c"
        });
        $('input.timepicker2').timepicker({
            timeFormat: 'HH:mm',
            minTime: new Date(0, 0, 0, 0, 15, 0),
            maxTime: new Date(0, 0, 0, 2, 0, 0),
            interval: 15,
            dynamic: false,
            scrollbar: true
        });

        $('input.timepicker3').timepicker({
            timeFormat: 'HH:mm',
            minTime: new Date(0, 0, 0, 8, 00, 0),
            maxTime: new Date(0, 0, 0, 20, 0, 0),
            interval: 15,
            dynamic: false,
            scrollbar: true
        });

        function mostraDip() {
            $.ajax({
                type: "POST",
                url: "./dipendenti.php",
                data: "&submit=tuttidipendentiDip&idatelier=<?= $_GET["id"] ?>",
                dataType: "json",
                success: function (msg) {

                    var db = {
                        loadData: function (filter) {
                            return $.grep(this.clients, function (client) {
                                return (!filter.nome || client.nome.indexOf(filter.nome) > -1);
                            });
                        }
                    };

                    window.db = db;

                    db.clients = msg.dati;
                    jsGrid.locale("it");

                    var campi = [
                        {name: "nome", type: "text", title: "<b>Nome</b>", css: "customRow"},
                        {name: "cognome", type: "text", title: "<b>Cognome</b>", css: "customRow"},
                        {name: "attivo", type: "select", items: [
                                {name: "Seleziona", Id: ""},
                                {name: "Disattivo", Id: "0"},
                                {name: "Attivo", Id: "1"}
                            ], title: "<b>Stato</b>", valueField: "Id", textField: "name", css: "customRow"},
                        {type: "control"}
                    ];


                    $("#jsGridDip").jsGrid({
                        width: "100%",
                        height: "520px",
                        inserting: true,
                        editing: true,
                        sorting: true,
                        paging: true,
                        autoload: true,
                        pageSize: 12,
                        pageButtonCount: 5,
                        controller: db,
                        deleteConfirm: "Stai per cancellare, sei sicuro?",
                        noDataContent: "Nessun record trovato",
                        fields: campi,
                        // cancella item
                        onItemDeleting: function (args) {
                            var idd = args.item.id;
                            $.ajax({
                                type: "POST",
                                url: "./dipendenti.php",
                                data: "id=" + idd + "&submit=deleteDip",
                                dataType: "json",
                                success: function () {
                                }
                            });
                        },
                        // edita item
                        onItemUpdated: function (args) {
                            var valori = $.param(args.item);
                            $.ajax({
                                type: "POST",
                                url: "./dipendenti.php",
                                data: valori + "&submit=editDip",
                                dataType: "json",
                                success: function () {
                                    mostraDip();
                                }
                            });
                        },
                        onItemInserted: function (args) {
                            var valori = $.param(args.item);
                            $.ajax({
                                type: "POST",
                                url: "./dipendenti.php",
                                data: valori + "&submit=insertDip&idatelier=<?= $_GET["id"] ?>",
                                dataType: "json",
                                success: function () {
                                    mostraDip();
                                }
                            });
                        }
                    });
                }

            });
        }
        if ($('#jsGridDip').length > 0) {
            mostraDip();
        }
        function mostraAtelierCollegati() {
            $.ajax({
                type: "POST",
                url: "./dipendenti.php",
                data: "&submit=tuttiAtelierCollegati&idatelier=<?= $_GET["id"] ?>",
                dataType: "json",
                success: function (msg) {

                    var db = {
                        loadData: function (filter) {
                            return $.grep(this.clients, function (client) {
                                return (!filter.nome || client.nome.indexOf(filter.nome) > -1);
                            });
                        }
                    };

                    window.db = db;

                    db.clients = msg.dati;
                    jsGrid.locale("it");

                    var campi = [
                        {name: "idatelier2", type: "select", title: "<b>Atelier Collegati</b>", valueField: "id", textField: "nominativo", css: "customRow", items: msg.atelier},
                        {type: "control"}
                    ];


                    $("#jsGridAtelier").jsGrid({
                        width: "100%",
                        height: "300px",
                        inserting: true,
                        editing: false,
                        sorting: true,
                        paging: true,
                        autoload: true,
                        pageSize: 12,
                        pageButtonCount: 5,
                        controller: db,
                        deleteConfirm: "Stai per cancellare, sei sicuro?",
                        noDataContent: "Nessun record trovato",
                        fields: campi,
                        // cancella item
                        onItemDeleting: function (args) {
                            var idd = args.item.id;
                            $.ajax({
                                type: "POST",
                                url: "./dipendenti.php",
                                data: "id=" + idd + "&submit=deleteAtelierCollegati",
                                dataType: "json",
                                success: function () {
                                }
                            });
                        },
                        // edita item
                        onItemUpdated: function (args) {
                            var valori = $.param(args.item);
                            $.ajax({
                                type: "POST",
                                url: "./dipendenti.php",
                                data: valori + "&submit=editAtelierCollegati",
                                dataType: "json",
                                success: function () {
                                    mostraDip();
                                }
                            });
                        },
                        onItemInserted: function (args) {
                            var valori = $.param(args.item);
                            $.ajax({
                                type: "POST",
                                url: "./dipendenti.php",
                                data: valori + "&submit=insertAtelierCollegati&idatelier=<?= $_GET["id"] ?>",
                                dataType: "json",
                                success: function () {
                                    mostraAtelierCollegati();
                                }
                            });
                        }
                    });
                }

            });
        }
        if ($('#jsGridDipAtelier').length > 0) {
            mostraAtelierCollegati();
        }
        function mostraAtelierLog() {
            $.ajax({
                type: "POST",
                url: "./dipendenti.php",
                data: "&submit=logAtelier&idatelier=<?= $_GET["id"] ?>",
                dataType: "json",
                success: function (msg) {

                    var db = {
                        loadData: function (filter) {
                            return $.grep(this.clients, function (client) {
                                return (!filter.data_ora_it || client.nome.indexOf(filter.data_ora_it) > -1);
                            });
                        }
                    };

                    window.db = db;

                    db.clients = msg.dati;
                    jsGrid.locale("it");

                    var campi = [
                        {name: "data_ora_it", type: "text", title: "<b>Data ora</b>", css: "customRow"},
                        {name: "utente", type: "text", title: "<b>Utente</b>", css: "customRow"},
                        {name: "location", type: "text", title: "<b>Indirizzo IP</b>", css: "customRow"},
                        {name: "operazione", type: "text", title: "<b>Operazione</b>", css: "customRow"}
                    ];


                    $("#jsGridLog").jsGrid({
                        width: "100%",
                        height: "300px",
                        inserting: false,
                        editing: false,
                        sorting: false,
                        paging: true,
                        autoload: true,
                        pageSize: 12,
                        pageButtonCount: 5,
                        controller: db,
                        deleteConfirm: "Stai per cancellare, sei sicuro?",
                        noDataContent: "Nessun record trovato",
                        fields: campi
                    });
                }

            });
        }
        if ($('#jsGridLog').length > 0) {
            mostraAtelierLog();
        }
        if ($('#calendarAddetti').length > 0) {
            var url = "./library/calendario-addetti.php?ida=<?= $_GET['id'] ?>";
            if (screen.width > "768") {
                //view = "twoWeek";
                view = "month";
                boxwidth = "50%";
            } else {
                view = "basicDay";
                boxwidth = "80%";
            }

            $('#calendarAddetti').fullCalendar({
                theme: true,
                header: {
                    left: 'prevYear nextYear prev,next today',
                    center: 'title',
                    right: 'month basicDay basicWeek twoWeek'
                },

                views: {
                    twoWeek: {
                        type: 'basic',
                        duration: {weeks: 2},
                        rows: 2
                    }
                },
                height: 650,
                selectable: true,
                selectHelper: true,
                timeFormat: 'HH:mm',
                displayEventEnd: true,
                defaultView: view,
                events: url,
                eventRender: function (event, element) {
                    var icoacquistato = "";
                    if ((event.utenteappuntamento == event.utenteattuale) || event.livello == '5' || event.livello == '1' || event.livello == '0' || event.livello == '2') {
                        element.prepend("<span class='deleteevent' style='color: #990000; float: right; padding-left: 3px;z-index:999;position:relative;'><i class=\"fa fa-times-circle fa-lg\" aria-hidden=\"true\"></i></span> "+
                                "<span class='editevent' style='color: #000000; float: right;z-index:999;position:relative;'><i class=\"fa fa-pencil-square-o fa-lg\" aria-hidden=\"true\"></i></span>" + icoacquistato);
                    } else {
                        element.prepend(icoacquistato);
                    }
                    element.find(".editevent").click(function () {
                        //console.log('ok');
                        $.ajax({
                            type: "POST",
                            url: "./dipendenti.php",
                            data: "id=" + event.id + "&submit=getAddetti",
                            dataType: "json",
                            success: function (msg) {
                                var contentHtml = '<input type="text" name="data_cal" value="' + msg.dati[0].data_cal + '" id="data_cal" class="input_moduli sizing float_moduli_small_15" placeholder="Data" title="Data" style="border:1px solid silver" />' +
                                        '<input type="text" name="ora_da" id="ora_da" value="' + msg.dati[0].ora_da + '" class="input_moduli sizing float_moduli_small_15 timepicker4" placeholder="Ora da" title="Ora da" style="border:1px solid silver" />' +
                                        '<input type="text" name="ora_a" id="ora_a" value="' + msg.dati[0].ora_a + '" class="input_moduli sizing float_moduli_small_15 timepicker4" placeholder="Ora a" title="Ora a" style="border:1px solid silver" />' +
                                        '<input type="text" name="addetti_cal" value="' + msg.dati[0].addetti + '" id="addetti_cal" class="input_moduli sizing float_moduli_small_15" placeholder="Addetti" title="Addetti" style="border:1px solid silver" />';
                                $.confirm({
                                    title: 'MODIFICA ADDETTI A CALENDARIO',
                                    content: contentHtml,
                                    boxWidth: '30%',
                                    useBootstrap: false,
                                    onContentReady: function () {
                                        //console.log('ok');
                                        $("#data_cal").datepicker();
                                        $('input.timepicker4').timepicker({
                                            timeFormat: 'HH:mm',
                                            minTime: new Date(0, 0, 0, 8, 00, 0),
                                            maxTime: new Date(0, 0, 0, 20, 0, 0),
                                            interval: 30,
                                            dynamic: false,
                                            scrollbar: true
                                        });
                                    },
                                    buttons: {
                                        confirm: {
                                            text: 'SALVA',
                                            btnClass: 'btn-green',
                                            keys: ['enter', 'shift'],
                                            action: function () {
                                                var data_cal = encodeURIComponent($('#data_cal').val());
                                                var ora_da = encodeURIComponent($('#ora_da').val());
                                                var ora_a = encodeURIComponent($('#ora_a').val());
                                                var addetti = encodeURIComponent($('#addetti_cal').val());
                                                if (data_cal != "" && ora_da != "" && ora_a != "" && addetti != "") {
                                                    $.ajax({
                                                        type: "POST",
                                                        url: "./dipendenti.php",
                                                        data: "idatelier=<?= $_GET['id'] ?>&data_cal=" + data_cal + "&ora_da=" + ora_da + "&ora_a=" + ora_a + "&addetti=" + addetti +
                                                                "&id=" + event.id + "&submit=aggiornaAddetti",
                                                        dataType: "json",
                                                        success: function (msg) {
                                                            $('#calendarAddetti').fullCalendar('refetchEvents');
                                                        }
                                                    });
                                                } else {
                                                    return false;
                                                }
                                            }
                                        },
                                        cancel: {
                                            text: 'ANNULLA',
                                            action: function () {

                                            }
                                        }
                                    }
                                });
                            }
                        });
                    });
                    element.find(".deleteevent").click(function () {

                        $.confirm({
                            title: 'ATTENZIONE!',
                            content: 'Stai per eliminare il dato, CONFERMI?',
                            boxWidth: '30%',
                            useBootstrap: false,
                            buttons: {
                                confirm: {
                                    text: 'SI',
                                    btnClass: 'btn-blue',
                                    keys: ['enter', 'shift'],
                                    action: function () {
                                        $('#calendar').fullCalendar('removeEvents', event.id);
                                        $.ajax({
                                            type: "POST",
                                            url: "./dipendenti.php",
                                            data: "id=" + event.id + "&submit=eliminaAddetti",
                                            dataType: "json",
                                            success: function (msg) {
                                                $('#calendarAddetti').fullCalendar('refetchEvents');
                                            }
                                        });
                                    }
                                },
                                cancel: {
                                    text: 'NO'
                                }
                            }
                        });

                    });
                }
            });
        }
        $('.addCalendar').unbind('click').click(function () {
            var contentHtml = '<input type="text" name="data_cal_dal" id="data_cal_dal" class="input_moduli sizing float_moduli_small_15" placeholder="Data dal" title="Data dal" style="border:1px solid silver" />' +
                    '<input type="text" name="data_cal_al" id="data_cal_al" class="input_moduli sizing float_moduli_small_15" placeholder="Data al" title="Data al" style="border:1px solid silver" />' +
                    '<input type="text" name="ora_da" id="ora_da" class="input_moduli sizing float_moduli_small_15 timepicker4" placeholder="Ora da" title="Ora da" style="border:1px solid silver" />' +
                    '<input type="text" name="ora_a" id="ora_a" class="input_moduli sizing float_moduli_small_15 timepicker4" placeholder="Ora a" title="Ora a" style="border:1px solid silver" />' +
                    '<input type="text" name="addetti_cal" id="addetti_cal" class="input_moduli sizing float_moduli_small_15" placeholder="Addetti" title="Addetti" style="border:1px solid silver" />';
            $.confirm({
                title: 'INSERISCI ADDETTI A CALENDARIO',
                content: contentHtml,
                boxWidth: '30%',
                useBootstrap: false,
                onContentReady: function () {
                    //console.log('ok');
                    $("#data_cal_dal,#data_cal_al").datepicker();
                    $('input.timepicker4').timepicker({
                        timeFormat: 'HH:mm',
                        minTime: new Date(0, 0, 0, 8, 00, 0),
                        maxTime: new Date(0, 0, 0, 20, 0, 0),
                        interval: 30,
                        dynamic: false,
                        scrollbar: true
                    });
                },
                buttons: {
                    confirm: {
                        text: 'SALVA',
                        btnClass: 'btn-green',
                        keys: ['enter', 'shift'],
                        action: function () {
                            var data_cal_dal = encodeURIComponent($('#data_cal_dal').val());
                            var data_cal_al = encodeURIComponent($('#data_cal_al').val());
                            var ora_da = encodeURIComponent($('#ora_da').val());
                            var ora_a = encodeURIComponent($('#ora_a').val());
                            var addetti = encodeURIComponent($('#addetti_cal').val());
                            if (data_cal_dal != "" && ora_da != "" && ora_a != "" && addetti != "") {
                                $.ajax({
                                    type: "POST",
                                    url: "./dipendenti.php",
                                    data: "idatelier=<?= $_GET['id'] ?>&data_cal_dal=" + data_cal_dal + "&data_cal_al=" + data_cal_al + "&ora_da=" + ora_da + "&ora_a=" + ora_a + "&addetti=" + addetti + "&submit=inserisciAddetti",
                                    dataType: "json",
                                    success: function (msg) {
                                        $('#calendarAddetti').fullCalendar('refetchEvents');
                                    }
                                });
                            } else {
                                return false;
                            }
                        }
                    },
                    cancel: {
                        text: 'ANNULLA',
                        action: function () {

                        }
                    }
                }
            });
        });
<?php if($_GET['id']) { ?>
        /* calendario turni */
        if ($('#calendarDipendenti').length > 0) {
            var settimana_dal_curr = null;
            var settimana_al_curr = null;
            var url = "./library/calendario-turni.php?ida=<?= $_GET['id'] ?>";
            if (screen.width > "768") {
                //view = "twoWeek";
                view = "agendaWeek";
                boxwidth = "50%";
            } else {
                view = "basicDay";
                boxwidth = "80%";
            }

            $('#calendarDipendenti').fullCalendar({
                theme: true,
                header: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'month agendaWeek'
                },
                views: {
                    twoWeek: {
                        type: 'agenda',
                        duration: {weeks: 2},
                        rows: 2
                    },
                    agendaWeek: {

                    }
                },
                height: 650,
                disableDragging: true,
                selectable: true,
                selectHelper: true,
                timeFormat: 'HH:mm',
                displayEventEnd: true,
                defaultView: view,
                minTime: '08:00:00',
                maxTime: '24:00:00',
                events: url,
                allDaySlot: false,
                slotDuration: '00:30:00',
                slotMinutes: 30,
                slotEventOverlap: false,
                viewRender: function (view, element) {
                    var giorno_dal = view.start._d.getDate();
                    var mese_dal = view.start._d.getMonth() + 1;
                    if (mese_dal < 10) {
                        mese_dal = pad(mese_dal, 2);
                    }
                    if (giorno_dal < 10) {
                        giorno_dal = pad(giorno_dal, 2);
                    }
                    var settimana_dal = view.start._d.getFullYear() + '-' + mese_dal.toString() + '-' + giorno_dal;
                    var giorno_al = view.end._d.getDate();
                    if (giorno_al < 10) {
                        giorno_al = pad(giorno_al, 2);
                    }
                    var mese_al = view.end._d.getMonth() + 1;
                    if (mese_al < 10) {
                        mese_al = pad(mese_al, 2);
                    }
                    var settimana_al = view.end._d.getFullYear() + '-' + mese_al.toString() + '-' + giorno_al;
                    settimana_dal_curr = settimana_dal;
                    settimana_al_curr = settimana_al;
                    getOreSettimana(<?= $_GET['id'] ?>, settimana_dal, settimana_al);
                    //console.log(settimana_dal + ' ' + settimana_al);
                },
                eventRender: function (event, element) {
                    var icoacquistato = "";
                    if ((event.utenteappuntamento == event.utenteattuale) || event.livello == '5' || event.livello == '1' || event.livello == '0' || event.livello == '2') {
                        element.prepend("<span class='deleteevent-dip' style='color: #990000; float: right; padding-left: 3px;position:relative;z-index:99;'><i class=\"fa fa-times-circle fa-lg\" aria-hidden=\"true\"></i></span> <span class='editevent-dip' style='color: #000000; float: right;position:relative;z-index:99;'><i class=\"fa fa-pencil-square-o fa-lg\" aria-hidden=\"true\"></i></span>" + icoacquistato);
                    } else {
                        element.prepend(icoacquistato);
                    }
                    element.find(".editevent-dip").click(function () {
                        //console.log(event);
                        $.ajax({
                            type: "POST",
                            url: "./dipendenti.php",
                            data: "id=" + event.id + "&submit=getTurnoDip",
                            dataType: "json",
                            success: function (msg) {
                                var contentHtml = '<input type="text" name="data_cal" value="' + msg.dati[0].data_cal + '" id="data_cal" class="input_moduli sizing float_moduli_small_15" placeholder="Data" title="Data" style="border:1px solid silver" />' +
                                        '<input type="text" name="ora_da" id="ora_da" value="' + msg.dati[0].ora_da + '" class="input_moduli sizing float_moduli_small_15 timepicker4" placeholder="Ora da" title="Ora da" style="border:1px solid silver" />' +
                                        '<input type="text" name="ora_a" id="ora_a" value="' + msg.dati[0].ora_a + '" class="input_moduli sizing float_moduli_small_15 timepicker4" placeholder="Ora a" title="Ora a" style="border:1px solid silver" />' +
                                        '<select name="caldip_idutente" id="caldip_idutente" class="input_moduli sizing float_moduli_35" style="border:1px solid silver"><?php
foreach ($dipendenti as $col) {
    echo '<option value="' . $col['id'] . '">' . addslashes($col['cognome'] . ' ' . $col['nome']) . '</option>';
}
?></select>';
                                $.confirm({
                                    title: 'MODIFICA TURNO A CALENDARIO',
                                    content: contentHtml,
                                    boxWidth: '30%',
                                    useBootstrap: false,
                                    onContentReady: function () {
                                        $("#data_cal").datepicker();
                                        $('input.timepicker4').timepicker({
                                            timeFormat: 'HH:mm',
                                            minTime: new Date(0, 0, 0, 8, 00, 0),
                                            maxTime: new Date(0, 0, 0, 20, 0, 0),
                                            interval: 30,
                                            dynamic: false,
                                            scrollbar: true
                                        });
                                        $('#caldip_idutente').val(msg.dati[0].idutente);
                                    },
                                    buttons: {
                                        confirm: {
                                            text: 'SALVA',
                                            btnClass: 'btn-green',
                                            keys: ['enter', 'shift'],
                                            action: function () {
                                                var data_cal = encodeURIComponent($('#data_cal').val());
                                                var ora_da = encodeURIComponent($('#ora_da').val());
                                                var ora_a = encodeURIComponent($('#ora_a').val());
                                                var idutente = encodeURIComponent($('#caldip_idutente').val());
                                                if (data_cal != "" && ora_da != "" && ora_a != "" && idutente != "") {
                                                    $.ajax({
                                                        type: "POST",
                                                        url: "./dipendenti.php",
                                                        data: "idatelier=<?= $_GET['id'] ?>&data_cal=" + data_cal + "&ora_da=" + ora_da + "&ora_a=" + ora_a + "&idutente=" + idutente +
                                                                "&id=" + event.id + "&submit=aggiornaTurnoDip",
                                                        dataType: "json",
                                                        success: function (msg) {
                                                            $('#calendarDipendenti').fullCalendar('refetchEvents');
                                                            getOreSettimana(<?= $_GET['id'] ?>, settimana_dal_curr, settimana_al_curr);
                                                        }
                                                    });
                                                } else {
                                                    return false;
                                                }
                                            }
                                        },
                                        cancel: {
                                            text: 'ANNULLA',
                                            action: function () {

                                            }
                                        }
                                    }
                                });
                            }
                        });
                    });
                    element.find(".deleteevent-dip").click(function () {

                        $.confirm({
                            title: 'ATTENZIONE!',
                            content: 'Stai per eliminare il dato, CONFERMI?',
                            boxWidth: '30%',
                            useBootstrap: false,
                            buttons: {
                                confirm: {
                                    text: 'SI',
                                    btnClass: 'btn-blue',
                                    keys: ['enter', 'shift'],
                                    action: function () {
                                        $('#calendarDipendenti').fullCalendar('removeEvents', event.id);
                                        $.ajax({
                                            type: "POST",
                                            url: "./dipendenti.php",
                                            data: "id=" + event.id + "&submit=eliminaTurnoDip",
                                            dataType: "json",
                                            success: function (msg) {
                                                $('#calendarDipendenti').fullCalendar('refetchEvents');
                                                getOreSettimana(<?= $_GET['id'] ?>, settimana_dal_curr, settimana_al_curr);
                                            }
                                        });
                                    }
                                },
                                cancel: {
                                    text: 'NO'
                                }
                            }
                        });

                    });
                }
            });
        }
        $('.addCalendarDip').unbind('click').click(function () {
            var contentHtml = '<input type="text" name="caldip_data_dal" id="caldip_data_dal" class="input_moduli sizing float_moduli_small_15" placeholder="Data dal" title="Data dal" style="border:1px solid silver" />' +
                    '<input type="text" name="caldip_data_al" id="caldip_data_al" class="input_moduli sizing float_moduli_small_15" placeholder="Data al" title="Data al" style="border:1px solid silver" />' +
                    '<input type="text" name="caldip_ora_da" id="caldip_ora_da" class="input_moduli sizing float_moduli_small_15 timepicker4" placeholder="Ora da" title="Ora da" style="border:1px solid silver" />' +
                    '<input type="text" name="caldip_ora_a" id="caldip_ora_a" class="input_moduli sizing float_moduli_small_15 timepicker4" placeholder="Ora a" title="Ora a" style="border:1px solid silver" />' +
                    '<select name="caldip_idutente" id="caldip_idutente" class="input_moduli sizing float_moduli_small_15" style="border:1px solid silver"><?php
foreach ($dipendenti as $col) {
    echo '<option value="' . $col['id'] . '">' . addslashes($col['cognome'] . ' ' . $col['nome']) . '</option>';
}
?></select>';
            $.confirm({
                title: 'INSERISCI TURNO A CALENDARIO',
                content: contentHtml,
                boxWidth: '30%',
                useBootstrap: false,
                onContentReady: function () {
                    //console.log('ok');
                    $("#caldip_data_dal,#caldip_data_al").datepicker();
                    $('input.timepicker4').timepicker({
                        timeFormat: 'HH:mm',
                        minTime: new Date(0, 0, 0, 8, 00, 0),
                        maxTime: new Date(0, 0, 0, 20, 0, 0),
                        interval: 30,
                        dynamic: false,
                        scrollbar: true
                    });
                },
                buttons: {
                    confirm: {
                        text: 'SALVA',
                        btnClass: 'btn-green',
                        keys: ['enter', 'shift'],
                        action: function () {
                            var data_cal_dal = encodeURIComponent($('#caldip_data_dal').val());
                            var data_cal_al = encodeURIComponent($('#caldip_data_al').val());
                            var ora_da = encodeURIComponent($('#caldip_ora_da').val());
                            var ora_a = encodeURIComponent($('#caldip_ora_a').val());
                            var idutente = encodeURIComponent($('#caldip_idutente').val());
                            if (data_cal_dal != "" && ora_da != "" && ora_a != "" && idutente != "") {
                                $.ajax({
                                    type: "POST",
                                    url: "./dipendenti.php",
                                    data: "idatelier=<?= $_GET['id'] ?>&data_cal_dal=" + data_cal_dal + "&data_cal_al=" + data_cal_al + "&ora_da=" + ora_da + "&ora_a=" + ora_a + "&idutente=" + idutente + "&submit=inserisciTurnoDip",
                                    dataType: "json",
                                    success: function (msg) {
                                        $('#calendarDipendenti').fullCalendar('refetchEvents');
                                        getOreSettimana(<?= $_GET['id'] ?>, settimana_dal_curr, settimana_al_curr);
                                    }
                                });
                            } else {
                                return false;
                            }
                        }
                    },
                    cancel: {
                        text: 'ANNULLA',
                        action: function () {

                        }
                    }
                }
            });
        });
<?php } ?>
        function mostraDipCollegati() {
            $.ajax({
                type: "POST",
                url: "./dipendenti.php",
                data: "&submit=tuttidipendentiGest&idutente=<?= $_GET['id'] ?>",
                dataType: "json",
                success: function (msg) {

                    var db = {
                        loadData: function (filter) {
                            return $.grep(this.clients, function (client) {
                                return (!filter.nome || client.nome.indexOf(filter.nome) > -1);
                            });
                        }
                    };

                    window.db = db;

                    db.clients = msg.dati;
                    jsGrid.locale("it");

                    var campi = [
                        {name: "idutente", type: "select", title: "<b>Cognome Nome</b>", css: "customRow", items: msg.utenti, valueField: "id", textField: "cognome_nome"},
                        {type: "control"}
                    ];


                    $("#jsGridDipGest").jsGrid({
                        width: "100%",
                        height: "520px",
                        inserting: true,
                        editing: true,
                        sorting: true,
                        paging: true,
                        autoload: true,
                        pageSize: 12,
                        pageButtonCount: 5,
                        controller: db,
                        deleteConfirm: "Stai per cancellare, sei sicuro?",
                        noDataContent: "Nessun record trovato",
                        fields: campi,
                        // cancella item
                        onItemDeleting: function (args) {
                            var idd = args.item.id;
                            $.ajax({
                                type: "POST",
                                url: "./dipendenti.php",
                                data: "id=" + idd + "&submit=deleteDipGest",
                                dataType: "json",
                                success: function () {
                                }
                            });
                        },
                        // edita item
                        onItemUpdated: function (args) {
                            var valori = $.param(args.item);
                            $.ajax({
                                type: "POST",
                                url: "./dipendenti.php",
                                data: valori + "&submit=editDipGest",
                                dataType: "json",
                                success: function () {
                                    mostraDipCollegati();
                                }
                            });
                        },
                        onItemInserted: function (args) {
                            var valori = $.param(args.item);
                            $.ajax({
                                type: "POST",
                                url: "./dipendenti.php",
                                data: valori + "&submit=insertDipGest&idformatore=<?= $_GET["id"] ?>",
                                dataType: "json",
                                success: function () {
                                    mostraDipCollegati();
                                }
                            });
                        }
                    });
                }

            });
        }
        if ($('#jsGridDipGest').length > 0) {
            mostraDipCollegati();
        }
        function mostraAtelierCollegatiUtente() {
            $.ajax({
                type: "POST",
                url: "./dipendenti.php",
                data: "&submit=tuttiAtelierCollegati&table=atelier_utente&idutente=<?= $_GET["id"] ?>",
                dataType: "json",
                success: function (msg) {

                    var db = {
                        loadData: function (filter) {
                            return $.grep(this.clients, function (client) {
                                return (!filter.nome || client.nome.indexOf(filter.nome) > -1);
                            });
                        }
                    };

                    window.db = db;

                    db.clients = msg.dati;
                    jsGrid.locale("it");

                    var campi = [
                        {name: "idatelier", type: "select", title: "<b>Nome Atelier</b>", valueField: "id", textField: "nominativo", css: "customRow", items: msg.atelier},
                        {type: "control"}
                    ];


                    $("#jsGridDipAtelier").jsGrid({
                        width: "100%",
                        height: "300px",
                        inserting: true,
                        editing: false,
                        sorting: true,
                        paging: true,
                        autoload: true,
                        pageSize: 12,
                        pageButtonCount: 5,
                        controller: db,
                        deleteConfirm: "Stai per cancellare, sei sicuro?",
                        noDataContent: "Nessun record trovato",
                        fields: campi,
                        // cancella item
                        onItemDeleting: function (args) {
                            var idd = args.item.id;
                            $.ajax({
                                type: "POST",
                                url: "./dipendenti.php",
                                data: "id=" + idd + "&table=atelier_utente&submit=deleteAtelierCollegati",
                                dataType: "json",
                                success: function () {
                                }
                            });
                        },
                        // edita item
                        onItemUpdated: function (args) {
                            var valori = $.param(args.item);
                            $.ajax({
                                type: "POST",
                                url: "./dipendenti.php",
                                data: valori + "&table=atelier_utente&submit=editAtelierCollegati",
                                dataType: "json",
                                success: function () {
                                    mostraAtelierCollegatiUtente();
                                }
                            });
                        },
                        onItemInserted: function (args) {
                            var valori = $.param(args.item);
                            $.ajax({
                                type: "POST",
                                url: "./dipendenti.php",
                                data: valori + "&table=atelier_utente&submit=insertAtelierCollegati&idutente=<?= $_GET["id"] ?>",
                                dataType: "json",
                                success: function () {
                                    mostraAtelierCollegatiUtente();
                                }
                            });
                        }
                    });
                }

            });
        }
        if ($('#jsGridDipAtelier').length > 0) {
            mostraAtelierCollegatiUtente();
        }
<?php if ($cols["livello"] == "5" && $_GET["id"] != "") { ?>
            var idfather_current = 0;
            $('#back-navbar').unbind('click').click(function () {
                var btn = $(this);
                var idcartella = parseInt(btn.attr('data-idcartella'));
                var idatelier = btn.data('idatelier');
                clickCartella(idcartella, idatelier, btn, 1);
            });
            var clickCartella = function (idcartella, idatelier, btn, isBack) {
                $.ajax({
                    type: "POST",
                    url: "./formazione.php",
                    data: "idfather=" + idcartella + "&idatelier=" + idatelier + "&submit=listFoldersAtelier",
                    dataType: "json",
                    success: function (msg) {
                        if (msg.msg === "ko") {
                            alert(msg.msgko);
                        } else {
                            if (msg.folders.length >= 0) {
                                $('#cnt-cartelle').html('');
                                if (btn) {
                                    if (btn.text() != '') {
                                        $('#cnt-navbar').append('<span id="' + idcartella + '" data-idfather="' + btn.data('idfather') + '">' + btn.text() + ' :: </span>');
                                    }
                                }
                                if (isBack == 1 && $('#cnt-navbar span').length > 1) {
                                    $('#cnt-navbar span').last().remove();
                                }
                                var idfather = $('#cnt-navbar span').last().data('idfather');
                                //console.log(idfather);
                                if (!idfather) {
                                    idfather = 0;
                                }

                                $('#back-navbar').attr('data-idcartella', idfather);
                                $("#form-cartella #idfather").val(idcartella);
                                $("#form-media #idfather").val(idcartella);
                                for (var i = 0; i < msg.folders.length; i++) {
                                    $('#cnt-cartelle').append('<div class="folder" id="riga_' + msg.folders[i].id + '"><i class="fa fa-folder fa-lg" aria-hidden="true"></i><a href="javascript:;" class="btn-cartella" data-idfather="' + msg.folders[i].idfather + '" data-idcartella="' + msg.folders[i].id + '" data-idatelier="' + msg.folders[i].idatelier + '"> <span id="titolo' + msg.folders[i].id + '">' + msg.folders[i].titolo + '</span></a><div class="ico-folder-docs">' +
                                            '<a href="javascript:;" class="editFolder" data-idfolder="' + msg.folders[i].id + '"><i class="fa fa-edit fa-lg" aria-hidden="true"></i></a>' +
                                            '<a href="javascript:;" class="delFolder" data-idfolder="' + msg.folders[i].id + '"><i class="fa fa-trash fa-lg" aria-hidden="true"></i></a>' +
                                            '</div></div>');
                                }
                                for (var i = 0; i < msg.files.length; i++) {
                                    $('#cnt-cartelle').append('<div class="folder" id="riga_' + msg.files[i].id + '"><i class="fa fa-file fa-lg" aria-hidden="true"></i><a href="/<?= FOLDER_UTENTI ?>/' + msg.files[i].idatelier + '/' + msg.files[i].idfather + '/' + msg.files[i].nomefile + '" target="_blank" class="btn-file" data-idfather="' + msg.files[i].idfather + '" data-idcartella="' + msg.files[i].id + '" data-idatelier="' + msg.files[i].idatelier + '"> <span id="titolo' + msg.files[i].id + '">' + msg.files[i].titolo + '</span></a><div class="ico-folder-docs">' +
                                            '<a href="javascript:;" class="editFolder" data-idfolder="' + msg.files[i].id + '"><i class="fa fa-edit fa-lg" aria-hidden="true"></i></a>' +
                                            '<a href="javascript:;" class="delFolder" data-idfolder="' + msg.files[i].id + '"><i class="fa fa-trash fa-lg" aria-hidden="true"></i></a>' +
                                            '</div></div>');
                                }
                                setEventsCartelle();
                            }
                        }
                    }
                });
            };
            var setEventsCartelle = function () {
                $('.btn-cartella').unbind('click').click(function () {
                    var btn = $(this);
                    var idcartella = btn.data('idcartella');
                    var idatelier = btn.data('idatelier');
                    clickCartella(idcartella, idatelier, btn, 0);
                });
                $('.editFolder').unbind('click').click(function () {
                    var btn = $(this);
                    var idfolder = btn.data('idfolder');
                    var titolo = $('span#titolo' + idfolder).html();
                    $('a[data-idcartella="' + idfolder + '"]').hide();
                    $('#riga_' + idfolder).append('<span id="titolo_new' + idfolder + '">' +
                            '<input type="text" id="editTitolo' + idfolder + '" value="' + titolo + '" /><button id="salva' + idfolder + '" class="saveTitolo">SALVA</button><button id="annulla' + idfolder + '" class="annullaTitolo">ANNULLA</button>' +
                            '</span>');
                    $('#salva' + idfolder).unbind('click').click(function () {
                        var new_titolo = $('#editTitolo' + idfolder).val();
                        $.ajax({
                            type: "POST",
                            url: "./formazione.php",
                            data: "titolo=" + encodeURIComponent(new_titolo) + "&idfolder=" + idfolder + "&submit=renameCartellaAtelier",
                            dataType: "json",
                            success: function (msg) {
                                $('span#titolo_new' + idfolder).remove();
                                $('span#titolo' + idfolder).html(new_titolo);
                                $('a[data-idcartella="' + idfolder + '"]').show();
                            }
                        });
                    });
                    $('#annulla' + idfolder).unbind('click').click(function () {
                        $('span#titolo_new' + idfolder).remove();
                        $('a[data-idcartella="' + idfolder + '"]').show();
                    });
                });
                $('.delFolder').unbind('click').click(function () {
                    var btn = $(this);
                    var idfolder = btn.data('idfolder');
                    if (confirm('Stai per eliminare la riga selezionata, vuoi procedere?')) {
                        $.ajax({
                            type: "POST",
                            url: "./formazione.php",
                            data: "idfolder=" + idfolder + "&submit=deleteCartellaAtelier",
                            dataType: "json",
                            success: function (msg) {
                                $('#riga_' + idfolder).remove();
                            }
                        });
                    } else {
                        return false;
                    }
                });
                $('#cnt-cartelle').sortable({
                    cancel: 'a.btn-cartella, a.btn-file',
                    update: function () {
                        var ordine = $(this).sortable('serialize');
                        $.ajax({
                            type: "POST",
                            url: "./formazione.php",
                            data: ordine + "&submit=ordinaFolderAtelier",
                            dataType: "json",
                            success: function ()
                            {
                            }
                        });
                    }
                });
            };
            $.validator.messages.required = '';
            $("#form-media").validate({
                submitHandler: function () {
                    $("#submitformmedia").ready(function () {
                        if ($('#media').val() == '') {
                            alert('Devi caricare il documento');
                            return false;
                        }
                        var datastring = $("#form-media *").not(".nopost").serialize();
                        $.ajax({
                            type: "POST",
                            url: "./formazione.php",
                            data: datastring + "&submit=addDocsAtelier",
                            dataType: "json",
                            success: function (msg) {
                                if (msg.msg === "ko") {
                                    alert(msg.msgko);
                                } else {
                                    $('#form-media').trigger('reset');
                                    $('#messaggio').slideToggle('fast').delay(2000).slideToggle('slow');
                                    var idcartella = $("#form-media #idfather").val();
                                    var idatelier = $("#form-media #idatelier_media").val();
                                    clickCartella(idcartella, idatelier, null, 0);
                                    $('#addMedia').slideToggle('slow');
                                }
                            }
                        });
                    });
                }
            });
            $("#form-cartella").validate({
                submitHandler: function () {
                    $("#submitformcartella").ready(function () {
                        var datastring = $("#form-cartella *").not(".nopost").serialize();
                        $.ajax({
                            type: "POST",
                            url: "./formazione.php",
                            data: datastring + "&submit=addCartellaAtelier",
                            dataType: "json",
                            success: function (msg) {
                                if (msg.msg === "ko") {
                                    alert(msg.msgko);
                                } else {
                                    $('#form-cartella').trigger('reset');
                                    $('#messaggio').slideToggle('fast').delay(2000).slideToggle('slow');
                                    $('#cnt-cartelle').append('<div class="folder"><a href="javascript:;" class="btn-cartella" data-idcartella="' + msg.idcartella + '" data-idatelier="' + $("#form-cartella #idatelier_media").val() + '"><i class="fa fa-folder fa-lg" aria-hidden="true"></i> ' + msg.titolo + '</a></div>');
                                    setEventsCartelle();
                                    $('#addCartella').slideToggle('slow');
                                }
                            }
                        });
                    });
                }
            });
            var media = new qq.FileUploader({
                element: document.getElementById('file-media'),
                action: "./js/fineuploader/upload-media.php",
                autoUpload: true,
                uploadButtonText: '<img src="./immagini/sel_upload.png" /> Seleziona il documento/video',
                //debug: true,
                multiple: true,
                allowedExtensions: ['jpg', 'JPG', 'pdf', 'PDF', 'doc', 'DOC', 'docx', 'DOCX', 'xls', 'XLS', 'xlsx', 'XLSX', 'mp4', 'MP4'],
                //sizeLimit: 50000
                'onComplete': function (id, fileName, responseJSON) {
                    if (responseJSON.success) {
                        $('#media').val(responseJSON.nomefile);
                    } else {
                        return false;
                    }
                }
            });
            setEventsCartelle();
<?php } ?>
<?php if ($cols["livello"] == "3" && $_GET["id"] != "") { ?>
            $.validator.messages.required = '';
            $("#form-media").validate({
                submitHandler: function () {
                    $("#submitformmedia").ready(function () {
                        if ($('#media').val() == '') {
                            alert('Devi caricare il documento');
                            return false;
                        }
                        var datastring = $("#form-media *").not(".nopost").serialize();
                        $.ajax({
                            type: "POST",
                            url: "./formazione.php",
                            data: datastring + "&submit=addDocsUtenti",
                            dataType: "json",
                            success: function (msg) {
                                if (msg.msg === "ko") {
                                    alert(msg.msgko);
                                } else {
                                    $('#form-media').trigger('reset');
                                    $('#messaggio').slideToggle('fast').delay(2000).slideToggle('slow');
                                    mostraMedia();
                                    $('#addMedia').slideToggle('slow');
                                }
                            }
                        });
                    });
                }
            });
            var media = new qq.FileUploader({
                element: document.getElementById('file-media'),
                action: "./js/fineuploader/upload-media.php",
                autoUpload: true,
                uploadButtonText: '<img src="./immagini/sel_upload.png" /> Seleziona il documento',
                //debug: true,
                multiple: false,
                allowedExtensions: ['jpg', 'JPG', 'pdf', 'PDF', 'doc', 'DOC', 'docx', 'DOCX', 'xls', 'XLS', 'xlsx', 'XLSX'],
                //sizeLimit: 50000
                'onComplete': function (id, fileName, responseJSON) {
                    if (responseJSON.success) {
                        $('#media').val(responseJSON.nomefile);
                    } else {
                        return false;
                    }
                }
            });
            function mostraMedia() {
                $.ajax({
                    type: "POST",
                    url: "./formazione.php",
                    data: "&submit=mostraDocsUtenti&idutente=<?= $_GET["id"] ?>",
                    dataType: "json",
                    success: function (msg) {

                        var db = {
                            loadData: function (filter) {
                                return $.grep(this.clients, function (client) {
                                    return (!filter.titolo || client.titolo.indexOf(filter.nome) > -1);
                                });
                            }
                        };

                        window.db = db;

                        db.clients = msg.dati;
                        jsGrid.locale("it");

                        var campi = [
                            {name: "titolo", type: "text", title: "<b>Titolo</b>", css: "customRow"},
                            {name: "nomefile", title: "<b>File</b>", css: "customRow"},
                            {type: "control", itemTemplate: function (value, item) {
                                    var $result = jsGrid.fields.control.prototype.itemTemplate.apply(this, arguments);
                                    var $myButton = $("<i style=\"margin-left: 5px; color: #2E65A1;\"class=\"fa fa-download fa-lg\" aria-hidden=\"true\" title=\"Apri il file\"></i>")
                                            .click(function (e) {
                                                //corsiFormazione(item.id);
                                                window.open('/<?= FOLDER_UTENTI ?>/' + item.idutente + '/' + item.nomefile, '_blank');
                                                e.stopPropagation();
                                            });
                                    return $result.add($myButton);
                                }
                            }
                        ];


                        $("#jsGridAllegati").jsGrid({
                            width: "100%",
                            height: "300px",
                            inserting: false,
                            editing: true,
                            sorting: true,
                            paging: true,
                            autoload: true,
                            pageSize: 12,
                            pageButtonCount: 5,
                            controller: db,
                            deleteConfirm: "Stai per cancellare, sei sicuro?",
                            noDataContent: "Nessun record trovato",
                            fields: campi,
                            rowClass: function (item, itemIndex) {
                                return "client-" + itemIndex;
                            },
                            // cancella item
                            onItemDeleting: function (args) {
                                var idd = args.item.id;
                                $.ajax({
                                    type: "POST",
                                    url: "./formazione.php",
                                    data: "id=" + idd + "&submit=delDocsUtente",
                                    dataType: "json",
                                    success: function () {
                                    }
                                });
                            },
                            // edita item
                            onItemUpdated: function (args) {
                                var valori = $.param(args.item);
                                $.ajax({
                                    type: "POST",
                                    url: "./formazione.php",
                                    data: valori + "&submit=editDocsUtente",
                                    dataType: "json",
                                    success: function () {
                                    }
                                });
                            }
                        });
                    }

                });
            }
            if ($('#jsGridAllegati').length > 0) {
                mostraMedia();
            }
<?php } ?>
    });
</script>