<?php

ob_start('ob_gzhandler');
include 'controllo.php';
include 'config.php';
include 'connessione.php';
include 'functions.php';

$idateliercal = $_GET['idatelier'];
$idutente = $_GET['idutente'];
$tabella = "calendario_dipendenti";

if (!$idateliercal) {
    if ($_SESSION['livello'] == 5) {
        $idateliercal = $_SESSION['id'];
        $qry_atelier = "idatelier = '" . $idateliercal . "'";
    } elseif ($_SESSION['livello'] == 3 && ($_SESSION['ruolo'] == 1 || $_SESSION['ruolo'] == 2)) {
        $all_atelier = getAtelierUser($_SESSION['id']);
        $arrAtelier = Array();
        foreach ($all_atelier as $atelier) {
            $arrAtelier[] = $atelier['id'];
        }
        $qry_atelier = "idatelier in (" . join(",", $arrAtelier) . ")";
    }
} else {
    $qry_atelier = "idatelier = '" . $idateliercal . "'";
}
if ($idutente != '') {
    $qry_utente = "and idutente=$idutente";
}
$dati = getDatiCalDipendenti("WHERE $qry_atelier and data_cal between '" . $_GET['start'] . "' and '" . $_GET['end'] . "' $qry_utente;");

$i = 0;
$now = date('Y-m-d');
//$now = '2025-03-26';
$colori = Array('#ffb533', '#9a4711', '#119a19', '#11329a', '#9a114b', '#ee6363', '#8b2323', '#ae3ec9');
$utenti = [];
foreach ($dati as $datid) {
    if (!in_array($datid['idutente'], $utenti)) {
        $utenti[] = $datid['idutente'];
    }
}
//var_dump($utenti);
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
    if ($datid['idutente'] == $_SESSION['id'] || $_SESSION['livello'] == 5 || $_SESSION['livello'] == 1 || $_SESSION['livello'] == 0 || $_SESSION['livello'] == 2) {
        $arrayore[$i]['startEditable'] = 1;
    } else {
        $arrayore[$i]['startEditable'] = 0;
    }
    foreach ($utenti as $k => $idutente) {
        if ($idutente == $datid['idutente']) {
            $arrayore[$i]['color'] = $colori[$k];
        }
    }
    $arrayore[$i]['edit'] = 0;
    if ($_SESSION['livello'] <= 1) {
        $arrayore[$i]['edit'] = 1;
    } elseif ($_SESSION['livello'] == 5) {
        $date_start = new DateTime($_GET['start']);
        $date_end = new DateTime($_GET['end']);
        $date = new DateTime($datid['data_cal']);
        $date_now = new DateTime($now);
        $day = $date_now->format('w');
        if ($datid['data_cal'] >= $now) {
            $arrayore[$i]['edit'] = 1;
        } else {
            if ($_GET['end'] < $now && $day <= 2) {//settimane precedenti
                $day_diff = $date_now->diff($date);
                if ($day_diff->days <= 9) {
                    $arrayore[$i]['edit'] = 1;
                    $arrayore[$i]['day_diff'] = $day_diff->days;
                }
            } else {
                $arrayore[$i]['edit'] = 1;
//                if ($day >= 2) {
//                    $arrayore[$i]['edit'] = 1;
//                }
            }
        }
    } else {
        $arrayore[$i]['edit'] = 0;
    }
    $i++;
}
if ($_SESSION['livello'] == 3 && ($_SESSION['ruolo'] == 1 || $_SESSION['ruolo'] == 2)) {
    $qry_utente = "and idutente=" . $_SESSION['id'];
    $dati = getDatiEventiDipendenti("WHERE id > 0 and data_cal between '" . $_GET['start'] . "' and '" . $_GET['end'] . "' $qry_utente;");
    foreach ($dati as $datid) {
//    $date = new DateTime($datid['data_cal']);
//    $day = $date->format('w');
        if ($datid['solo_admin'] == 0) {
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
                        $elimina = true;
                    } else {
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
} else {
    $dati = getDatiEventiDipendenti("WHERE attivo = 1 and tipo in ('P','F','M','C','L') " . ($qry_atelier != '' ? " and $qry_atelier" : "") . " and data_cal between '" . $_GET['start'] . "' and '" . $_GET['end'] . "' $qry_utente;");
//var_dump($dati);
    foreach ($dati as $datid) {
//    $date = new DateTime($datid['data_cal']);
//    $day = $date->format('w');
        if ($datid['solo_admin'] == 0 || $_SESSION['stato_dipendente'] == 3 || $_SESSION['livello'] <= 1) {
            $color = "";
            $dataprint = "";
            $idgiornoore = $datid['id'];
            $arrayore[$i]['id'] = $datid['id'];
            $arrayore[$i]['color'] = '#ff0000';
            $attivo = 'Approvato';
            $arrayore[$i]['title'] = "ASSENTE\n" . $datid['nome_atelier'] . "\n" . $datid['dipendente'];
            $arrayore[$i]['start'] = $datid['data_cal'] . ($datid['ora_da'] != '' ? " " . $datid['ora_da'] : "");
            $arrayore[$i]['end'] = $datid['data_cal'] . ($datid['ora_a'] != '' ? " " . $datid['ora_a'] : "");
            if ($datid['allday'] == 1) {
                $arrayore[$i]['allDay'] = true;
            }

            $arrayore[$i]['evento'] = 1;
            $arrayore[$i]['className'] = 'evento';
            $i++;
        }
    }
}

echo json_encode($arrayore);
