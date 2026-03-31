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

if (isset($_GET['idutente'])) {
    $idutente = $_GET['idutente'];
} else {
    $idutente = $_SESSION['id'];
}
if (isset($_GET['idatelier'])) {
    $idatelier = $_GET['idatelier'];
    if ($idutente == $_SESSION['id']) {
        $idutente = '';
    }
} else {
    $idatelier = '';
}
$dati = getDatiCalDipendenti("WHERE id > 0 " . ($idutente != '' ? "and idutente = '" . $idutente . "'" : "") . " and data_cal between '" . $_GET['start'] . "' and '" . $_GET['end'] . "' " . ($idatelier != '' ? " and idatelier=$idatelier" : "") . ";");
$now = date('Y-m-d');
$i = 0;
$colori = Array('#ffb533', '#9a4711', '#119a19', '#11329a', '#9a114b', '#ee6363', '#8b2323', '#ae3ec9');
$utenti = [];
foreach ($dati as $datid) {
    if (!in_array($datid['idutente'], $utenti)) {
        $utenti[] = $datid['idutente'];
    }
}
if ($_GET['solo_richieste'] == '') {
    foreach ($dati as $datid) {
//    $date = new DateTime($datid['data_cal']);
//    $day = $date->format('w');
        $color = "";
        $dataprint = "";
        $idgiornoore = $datid['id'];
        $arrayore[$i]['id'] = $datid['id'];
        $arrayore[$i]['title'] = $datid['nome_atelier'] . "\n" . $datid['dipendente'];
        $arrayore[$i]['start'] = $datid['data_cal'] . " " . $datid['ora_da'];
        $arrayore[$i]['end'] = $datid['data_cal'] . " " . $datid['ora_a'];
        //$arrayore[$i]['allDay'] = false;
        $arrayore[$i]['color'] = '#5f9ea0';
        $arrayore[$i]['evento'] = 0;
        foreach ($utenti as $k => $idutente) {
            if($idutente == $datid['idutente']) {
                $arrayore[$i]['color'] = $colori[$k];
            }
        }
        $i++;
    }
} else {
    $idutente = $idatelier = '';
}
$dati = getDatiEventiDipendenti("WHERE id > 0 " . ($idutente != '' ? "and idutente = '" . $idutente . "'" : "") . " and data_cal between '" . $_GET['start'] . "' and '" . $_GET['end'] . "'" . ($idatelier != '' ? " and idatelier=$idatelier" : "") . ";");
//var_dump($dati);
foreach ($dati as $datid) {
//    $date = new DateTime($datid['data_cal']);
//    $day = $date->format('w');
    if ($datid['solo_admin'] == 0 || $_SESSION['stato_dipendente'] == 3 || $_SESSION['livello'] <= 1) {
        $color = "";
        $dataprint = "";
        $elimina = $richiesta_elimina = false;
        $idgiornoore = $datid['id'];
        $arrayore[$i]['id'] = $datid['id'];
        switch ($datid['attivo']) {
            case 0:
                $arrayore[$i]['color'] = '#69a2de';
                $attivo = 'Da approvare';
                if ($datid['data_cal'] > $now) {
                    $elimina = true;
                }
                break;

            case 1:
                $arrayore[$i]['color'] = '#14a64e';
                $attivo = 'Approvato';
                if ($datid['data_cal'] > $now) {
                    $richiesta_elimina = true;
                }
                break;

            case 2:
                $arrayore[$i]['color'] = '#ff0000';
                $attivo = 'Rifiutato';
                break;
        }
        $arrayore[$i]['title'] = $datid['nome_atelier'] . "\n" . $datid['dipendente'] . "\n" . $datid['evento_tipo'] . "\n" . $attivo;
        $arrayore[$i]['start'] = $datid['data_cal'] . ($datid['ora_da'] != '' ? " " . $datid['ora_da'] : "");
        $arrayore[$i]['end'] = $datid['data_cal'] . ($datid['ora_a'] != '' ? " " . $datid['ora_a'] : "");
        if ($datid['allday'] == 1) {
            $arrayore[$i]['allDay'] = true;
        }

        $arrayore[$i]['evento'] = 1;
        $arrayore[$i]['className'] = 'evento';
        $arrayore[$i]['elimina'] = $elimina;
        $arrayore[$i]['richiesta_elimina'] = $richiesta_elimina;
        $i++;
    }
}
echo json_encode($arrayore);
