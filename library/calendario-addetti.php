<?php

ob_start('ob_gzhandler');
include 'controllo.php';
include 'config.php';
include 'connessione.php';
include 'functions.php';

$idateliercal = $_GET['ida'];

$tabella = "addetti_atelier";

if (!$idateliercal) {
    $idateliercal = $_SESSION['idatelier'];
}


$dati = getDatiCalAddetti("WHERE idatelier = '" . $idateliercal . "' and data_cal between '".$_GET['start']."' and '".$_GET['end']."';");
$i = 0;
foreach ($dati as $datid) {
    $color = "";
    $dataprint = "";
    $idgiornoore = $datid['id'];
    $arrayore[$i]['id'] = $datid['id'];
    $arrayore[$i]['title'] = "Numero addetti: ".$datid['addetti'];
    $arrayore[$i]['start'] = $datid['data_cal'] . "T" . $datid['ora_da'];
    $arrayore[$i]['end'] = $datid['data_cal'] . "T" . $datid['ora_a'];
    $arrayore[$i]['allDay'] = false;
    if ($datid['idutente'] == $_SESSION['id'] || $_SESSION['livello'] == 5 || $_SESSION['livello'] == 1 || $_SESSION['livello'] == 0 || $_SESSION['livello'] == 2) {
        $arrayore[$i]['startEditable'] = 1;
    } else {
        $arrayore[$i]['startEditable'] = 0;
    }
    $i++;
}

echo json_encode($arrayore);
