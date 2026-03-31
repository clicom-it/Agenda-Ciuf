<?php

ob_start('ob_gzhandler');
include 'controllo.php';
include 'config.php';
include 'connessione.php';
include 'functions.php';
include 'basic.class.php';

$tabella = "ore";

if (($_SESSION['livello'] == 0 || $_SESSION['livello'] == 1 || $_SESSION['livello'] == 4)) {
    $idutente = 0;
    $where = "";
} else {
    $idutente = $_SESSION['id'];
    $where = "WHERE idutente = '$idutente'";
}

$ore = getDati($tabella, $where);
$i = 0;
foreach ($ore as $ored) {
    $dataarr = explode("-", $ored['data']);
    $datajs = $dataarr[2]."/".$dataarr[1]."/".$dataarr[0];
    
    $idgiornoore = $ored['id'];
    /* utente */
    $idu = $ored['idutente'];
    $datiu = getDati("utenti", "WHERE id='$idu'");
    $utente = $datiu[0]['nome'] . " " . $datiu[0]['cognome'];
    /* utente */
    $arrayore[$i]['id'] = $ored['id'];
    $arrayore[$i]['title'] = $utente . "\n" . $ored['entratamattino'] . "-" . $ored['uscitamattino'] . " / " . $ored['entratapomeriggio'] . "-" . $ored['uscitapomeriggio'];
    $arrayore[$i]['start'] = $ored['data'];
    $arrayore[$i]['editable'] = 1;
//    $arrayore[$i]['url'] = "javascript:aggiorna('$idu', '$datajs')";
    $i++;
}

echo json_encode($arrayore);
