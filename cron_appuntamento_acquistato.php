<?php

ob_start('ob_gzhandler');
include './library/config.php';
include './library/connessione.php';
include './library/functions.php';
ini_set("max_execution_time", 900);
/* appuntamento acquistato, solo sposa e sposo */
# CRON */20 12-14 * * *
$giorni = 1;
$tipo_qry = $_GET['tipo'];
$time = date('H:i');
$time_start = '12:00';
$time_end = '14:00';
$tipo_log = 'Acquistato';
if ($giorni != '' && $tipo_qry != '' && $time >= $time_start && $time <= $time_end) {
    $qry = "SELECT *, DATE_FORMAT(data, '%d/%m') dataapp, TIME_FORMAT(orario, '%H:%i') orarioapp,"
            . "COALESCE((select inviato_acquistato from calendario_spoki cs where cs.idcalendario=c.id limit 1), 0) as inviato_acquistato,"
            . "COALESCE((select data_acquistato from calendario_spoki cs where cs.idcalendario=c.id limit 1), '') as data_acquistato"
            . " FROM calendario c "
            . "WHERE idatelier != 137 and idatelier != 75 and idatelier != 71 AND tipoappuntamento IN ($tipo_qry) AND disdetto = 0 and acquistato = '1' "
            //. "and id=94236 "
            . "having inviato_acquistato=0 and CURDATE() = (data_acquistato + INTERVAL $giorni DAY) limit 20;";
    //die($qry);
    $sql = $db->prepare($qry);
    $sql->execute();
    $res = $sql->fetchAll(PDO::FETCH_ASSOC);
    logReminder($qry, $time, $tipo_log);
    foreach ($res as $row) {
        $idappuntamento = $row['id'];
        $dataapp = $row['data'];
        $orarioapp = $row['orario'];
        $tipo = $row['tipoappuntamento'];
        $telefono = "+39" . $row['telefono'];
        $emailutente = $row['email'];
        $emailatelier = $row['emailatelier'];

        $idatelier = $row['idatelier'];
        $atelier = getDati("utenti", "WHERE id=$idatelier")[0];
        $nomeatelier = $atelier['nominativo'];

        $nomecliente = $row['nome'];
        $cognomecliente = $row['cognome'];
        $request = 'phone=' . urlencode($telefono) . '&first_name=' . urlencode($nomecliente) .
                '&last_name=' . urlencode($cognomecliente) .
                '&email=' . urlencode($emailutente) .
                '&language=it' .
                '&citta=' . urlencode($atelier['comune']) .
                '&compleanno=' .
                '&data=' . urlencode($dataapp) .
                '&ora=' . urlencode($orarioapp) .
                '&giorni=' . $giorni . '&tipo=' . $tipo.'&action=acquistato';
        $endpoint = URL_WS . 'postAppuntamentoSPoki';
        $response = callWS($endpoint, $request);
        //die($response);
        $json = json_decode($response, false);
        if ($json->success == 1) {
            $qry = "select idcalendario from calendario_spoki where idcalendario=? limit 1;";
            $rs = $db->prepare($qry);
            $valori = Array($idappuntamento);
            $rs->execute($valori);
            if ($rs->RowCount() == 0) {
                $qry = "insert into calendario_spoki (inviato_acquistato, idcalendario) values (?,?);";
            } else {
                $qry = "update calendario_spoki set inviato_acquistato=? where idcalendario=?;";
            }
            $rs = $db->prepare($qry);
            $valori = Array(1, $idappuntamento);
            $rs->execute($valori);
        }
        sleep(1);
    }
}