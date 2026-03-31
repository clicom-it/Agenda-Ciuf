<?php

ob_start('ob_gzhandler');
include 'controllo.php';
include 'config.php';
include 'connessione.php';
include 'functions.php';

$idateliercal = $_GET['ida'];

$tabella = "calendario_dipendenti";

if (!$idateliercal) {
    $idateliercal = $_SESSION['idatelier'];
}


$dati = getDatiCalDipendenti("WHERE idatelier = '" . $idateliercal . "' and data_cal between '".$_GET['start']."' and '".$_GET['end']."';");

$i = 0;
foreach ($dati as $datid) {
//    $date = new DateTime($datid['data_cal']);
//    $day = $date->format('w');
    $color = "";
    $dataprint = "";
    $idgiornoore = $datid['id'];
    $arrayore[$i]['id'] = $datid['id'];
    $arrayore[$i]['title'] = $datid['dipendente'];
    $arrayore[$i]['start'] = $datid['data_cal'] . " " . $datid['ora_da'];
    $arrayore[$i]['end'] = $datid['data_cal'] . " " . $datid['ora_a'];
    //$arrayore[$i]['allDay'] = false;
    $arrayore[$i]['color'] = '#5f9ea0';
    if ($datid['idutente'] == $_SESSION['id'] || $_SESSION['livello'] == 5 || $_SESSION['livello'] == 1 || $_SESSION['livello'] == 0 || $_SESSION['livello'] == 2) {
        $arrayore[$i]['startEditable'] = 1;
    } else {
        $arrayore[$i]['startEditable'] = 0;
    }
    $i++;
}

echo json_encode($arrayore);
