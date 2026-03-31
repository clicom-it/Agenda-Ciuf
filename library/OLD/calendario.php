<?php

ob_start('ob_gzhandler');
include 'controllo.php';
include 'config.php';
include 'connessione.php';
include 'functions.php';
$arrAtelierSartoria = Array(65, 59, 136, 140, 100, 97, 81);
$idateliercal = $_GET['ida'];

$tabella = "calendario";

if (!$idateliercal) {
    $idateliercal = $_SESSION['idatelier'];
}


$dati = getDatiCal("WHERE idatelier = '" . $idateliercal . "' and "
        . "(`data` between '{$_GET['start']}' and '{$_GET['end']}' or "
        . "`datasart1` between '{$_GET['start']}' and '{$_GET['end']}' or "
        . "`datasart2` between '{$_GET['start']}' and '{$_GET['end']}' or "
        . "`datasart3` between '{$_GET['start']}' and '{$_GET['end']}' or "
        . "`datasart4` between '{$_GET['start']}' and '{$_GET['end']}' or "
        . "datasaldo between '{$_GET['start']}' and '{$_GET['end']}'"
        . ")");
$datiAtelier = getDati("utenti", "WHERE id = " . $idateliercal . "");
$addetti = $datiAtelier[0]['addetti'];
if ($datiAtelier[0]['nominativo'] != '') {
    logAccesso($_SESSION["id"], 'consulta agenda di ' . $datiAtelier[0]['nominativo'], $idateliercal);
}
$i = 0;
$arr_data = Array();
foreach ($dati as $datid) {
    $color = "";
    $dataprint = "";
    if ($datid['datamatrimonioit'] != "00/00/0000") {
        $dataprint = $datid['datamatrimonioit'];
    }

    $daticommesso = getDati("utenti", "WHERE id = " . $datid['idutente'] . " and livello='3' and attivo=1 and ruolo in (" . ADDETTO_VENDITE . "," . DISTRICT_MANAGER . "," . FRANCHISING . ", " . STORE_MANAGER . ")");

    if ($datid['tipoappuntamento'] == "1") {
        $tipoapp = "Appuntamento Sposa";
        $color = "#db00cc";
    } else if ($datid['tipoappuntamento'] == "2") {
        $tipoapp = "Appuntamento Sposo";
        $color = "#1a00a4";
    } else if ($datid['tipoappuntamento'] == "3") {
        $tipoapp = "Cerimonia Donna";
        $color = "#f5a9f0";
    } else if ($datid['tipoappuntamento'] == "4") {
        $tipoapp = "Cerimonia Uomo";
        $color = "#69a2de";
    } else if ($datid['tipoappuntamento'] == "5") {
        $tipoapp = "Sartoria";
        $color = "#696969";
    } else if ($datid['tipoappuntamento'] == "6") {
        $tipoapp = "Appuntamento Sposa e Sposo";
        $color = "#75507b";
    } else if ($datid['tipoappuntamento'] == "7") {
        $tipoapp = "Ferie";
        $color = "#cccc00";
    } else if ($datid['tipoappuntamento'] == "8") {
        $tipoapp = "Lista";
        $color = "#ffa343";
    } else if ($datid['tipoappuntamento'] == "9") {
        $tipoapp = "Trunk show";
        $color = "#c9b8a7";
    }
    if ($datid['disdetto'] == "1") {
        $color = "#ff0000";
    }
    if ($datid['provenienza'] == "Fiera") {
        //$color = "#FF5F1F";
    }
    if ($datid['online'] == 1) {
        $txt_online = 'ONLINE';
    } else {
        $txt_online = '';
    }
    if ($daticommesso) {
        $nome_commesso = $daticommesso[0]['nome'] . " " . $daticommesso[0]['cognome'];
    } else {
        $nome_commesso = '';
    }
    if ($daticommesso[0]['attivo'] == 0 && ($_SESSION['livello'] == 5 || $_SESSION['livello'] == 3)) {
        $nome_commesso = '';
    }
    $idgiornoore = $datid['id'];
    $arrayore[$i]['id'] = $datid['id'];
    $arrayore[$i]['title'] = $datid['nome'] . " " . $datid['cognome'] . "\n Tipo: " . $tipoapp . "\n Matrimonio: " . $dataprint .
            "\n Gestito: " . $nome_commesso . ($txt_online != "" ? "\n" . $txt_online : '');
    $arrayore[$i]['start'] = $datid['data'] . " " . $datid['orario'];
    $arrayore[$i]['utenteappuntamento'] = $datid['idutente'];
    $arrayore[$i]['utenteattuale'] = $_SESSION['id'];
    $arrayore[$i]['livello'] = $_SESSION['livello'];
    $arrayore[$i]['acquistato'] = $datid['acquistato'];
    $arrayore[$i]['idnoacquisto'] = $datid['idnoacquisto'];
    $arrayore[$i]['provenienza'] = $datid['provenienza'];
    if ($datid['idutente'] == $_SESSION['id'] || $_SESSION['livello'] == 5 || $_SESSION['livello'] == 1 || $_SESSION['livello'] == 0 || $_SESSION['livello'] == 2) {
        $arrayore[$i]['startEditable'] = 1;
    } else {
        $arrayore[$i]['startEditable'] = 0;
    }

    $arrayore[$i]['color'] = $color;
    $arrayore[$i]['disdetto'] = $datid['disdetto'];
    /* appuntamento saldo/ritiro */

    if ($datid['datas'] != "00/00/0000") {

        $i++;

        $daticommesso = getDati("utenti", "WHERE id = " . $datid['idutente'] . " and livello='3' and attivo=1 and ruolo in (" . ADDETTO_VENDITE . "," . DISTRICT_MANAGER . "," . FRANCHISING . ", " . STORE_MANAGER . ")");

        $color = "#229f47";
        $tipoapp = "Saldo/ritiro";

        $idgiornoore = $datid['id'];
        $arrayore[$i]['id'] = $datid['id'];
        $arrayore[$i]['title'] = $datid['nome'] . " " . $datid['cognome'] . "\n Tipo: " . $tipoapp . "\n Gestito: " . $daticommesso[0]['nome'] . " " . $daticommesso[0]['cognome'] . ($txt_online != "" ? "\n" . $txt_online : '');
        $arrayore[$i]['start'] = $datid['datasaldo'] . " " . $datid['orariosaldo'];
        $arrayore[$i]['utenteappuntamento'] = $datid['idutente'];
        $arrayore[$i]['utenteattuale'] = $_SESSION['id'];
        $arrayore[$i]['acquistato'] = $datid['acquistato'];

        $arrayore[$i]['startEditable'] = 0;

        $arrayore[$i]['color'] = $color;
    } else {
        
    }

    /* appuntamento sartoria 1 */

    if ($datid['datas1'] != "00/00/0000") {

        $i++;

        $daticommesso = getDati("utenti", "WHERE id = " . $datid['idutente'] . " and livello='3' and attivo=1 and ruolo in (" . ADDETTO_VENDITE . "," . DISTRICT_MANAGER . "," . FRANCHISING . ", " . STORE_MANAGER . ")");

        $color = "#696969";
        $tipoapp = "1° sartoria";

        $idgiornoore = $datid['id'];
        $arrayore[$i]['id'] = $datid['id'];
        $arrayore[$i]['title'] = $datid['nome'] . " " . $datid['cognome'] . "\n Tipo: " . $tipoapp . "\n Gestito: " . $daticommesso[0]['nome'] . " " . $daticommesso[0]['cognome'] . ($txt_online != "" ? "\n" . $txt_online : '');
        $arrayore[$i]['start'] = $datid['datasart1'] . " " . $datid['orariosart1'];
        $arrayore[$i]['utenteappuntamento'] = $datid['idutente'];
        $arrayore[$i]['utenteattuale'] = $_SESSION['id'];
        $arrayore[$i]['acquistato'] = $datid['acquistato'];

        $arrayore[$i]['startEditable'] = 0;

        $arrayore[$i]['color'] = $color;
    }

    /* appuntamento sartoria 1 */

    if ($datid['datas2'] != "00/00/0000") {
        $i++;

        $daticommesso = getDati("utenti", "WHERE id = " . $datid['idutente'] . " and livello='3' and attivo=1 and ruolo in (" . ADDETTO_VENDITE . "," . DISTRICT_MANAGER . "," . FRANCHISING . ", " . STORE_MANAGER . ")");

        $color = "#696969";
        $tipoapp = "2° sartoria";

        $idgiornoore = $datid['id'];
        $arrayore[$i]['id'] = $datid['id'];
        $arrayore[$i]['title'] = $datid['nome'] . " " . $datid['cognome'] . "\n Tipo: " . $tipoapp . "\n Gestito: " . $daticommesso[0]['nome'] . " " . $daticommesso[0]['cognome'] . ($txt_online != "" ? "\n" . $txt_online : '');
        $arrayore[$i]['start'] = $datid['datasart2'] . " " . $datid['orariosart2'];
        $arrayore[$i]['utenteappuntamento'] = $datid['idutente'];
        $arrayore[$i]['utenteattuale'] = $_SESSION['id'];
        $arrayore[$i]['acquistato'] = $datid['acquistato'];

        $arrayore[$i]['startEditable'] = 0;

        $arrayore[$i]['color'] = $color;
    }

    /* appuntamento sartoria 3 */

    if ($datid['datas3'] != "00/00/0000") {

        $i++;

        $daticommesso = getDati("utenti", "WHERE id = " . $datid['idutente'] . " and livello='3' and attivo=1 and ruolo in (" . ADDETTO_VENDITE . "," . DISTRICT_MANAGER . "," . FRANCHISING . ", " . STORE_MANAGER . ")");

        $color = "#696969";
        $tipoapp = "3° sartoria";

        $idgiornoore = $datid['id'];
        $arrayore[$i]['id'] = $datid['id'];
        $arrayore[$i]['title'] = $datid['nome'] . " " . $datid['cognome'] . "\n Tipo: " . $tipoapp . "\n Gestito: " . $daticommesso[0]['nome'] . " " . $daticommesso[0]['cognome'] . ($txt_online != "" ? "\n" . $txt_online : '');
        $arrayore[$i]['start'] = $datid['datasart3'] . " " . $datid['orariosart3'];
        $arrayore[$i]['utenteappuntamento'] = $datid['idutente'];
        $arrayore[$i]['utenteattuale'] = $_SESSION['id'];
        $arrayore[$i]['acquistato'] = $datid['acquistato'];

        $arrayore[$i]['startEditable'] = 0;

        $arrayore[$i]['color'] = $color;
    }

    /* appuntamento sartoria 4 */

    if ($datid['datas4'] != "00/00/0000") {

        $i++;

        $daticommesso = getDati("utenti", "WHERE id = " . $datid['idutente'] . " and livello='3' and attivo=1 and ruolo in (" . ADDETTO_VENDITE . "," . DISTRICT_MANAGER . "," . FRANCHISING . ", " . STORE_MANAGER . ")");

        $color = "#696969";
        $tipoapp = "4° sartoria";

        $idgiornoore = $datid['id'];
        $arrayore[$i]['id'] = $datid['id'];
        $arrayore[$i]['title'] = $datid['nome'] . " " . $datid['cognome'] . "\n Tipo: " . $tipoapp . "\n Gestito: " . $daticommesso[0]['nome'] . " " . $daticommesso[0]['cognome'] . ($txt_online != "" ? "\n" . $txt_online : '');
        $arrayore[$i]['start'] = $datid['datasart4'] . " " . $datid['orariosart4'];
        $arrayore[$i]['utenteappuntamento'] = $datid['idutente'];
        $arrayore[$i]['utenteattuale'] = $_SESSION['id'];
        $arrayore[$i]['acquistato'] = $datid['acquistato'];

        $arrayore[$i]['startEditable'] = 0;

        $arrayore[$i]['color'] = $color;
    }

    $i++;
}
if ($idateliercal != "") {
    $current = $_GET['start'];
    $date_start = new DateTime($current);
    $end = $_GET['end'];
    while ($current <= $end) {
        $qry = "select SUM(addetti) from addetti_atelier where idatelier=$idateliercal and data_cal='$current' group by data_cal";
        $rs = $db->prepare($qry);
        $rs->execute();
        $num_addetti = (int) $rs->fetchColumn();
        $day_week = $date_start->format('w');
        $qry = "select addetti" . $day_week . " from atelier_addetti where idatelier=$idateliercal limit 1;";
        $rs = $db->prepare($qry);
        $rs->execute();
        $addetti_atelier = (int) $rs->fetchColumn();
        $arrayore[$i]['title'] = "Addetti: " . (($addetti_atelier > 0 ? $addetti_atelier : $addetti) + $num_addetti);
        $arrayore[$i]['start'] = $current;
        $arrayore[$i]['isAddetti'] = "1";
        $arrayore[$i]['editable'] = false;
        $date_start->modify('+1 day');
        $current = $date_start->format('Y-m-d');
        $i++;
    }
}

echo json_encode($arrayore);
