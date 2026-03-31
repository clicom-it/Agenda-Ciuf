<?php

ob_start('ob_gzhandler');
include './library/config.php';
include './library/connessione.php';
include './library/functions.php';
ini_set("max_execution_time", 900);
# CRON */10 12-14 * * *
/* remember appuntamento */
$giorni = $_GET['g'];
$tipo_qry = $_GET['tipo'];
$time = date('H:i');
$time_start = '12:00';
$time_end = '14:00';
$tipo_log = 'Remind';
//$qry = "SELECT *, DATE_FORMAT(data, '%d/%m') dataapp, TIME_FORMAT(orario, '%H:%i') orarioapp,"
//            . "COALESCE((select inviato_remember$giorni from calendario_spoki cs where cs.idcalendario=c.id limit 1), 0) as inviato_remember"
//            . " FROM calendario c "
//            . "WHERE idatelier != 137 AND data = (CURDATE() + INTERVAL $giorni DAY) AND data >= CURDATE() AND tipoappuntamento IN ($tipo_qry) AND disdetto = 0 and acquistato!='1' "
//            //. "and id=93463 "
//            . "having inviato_remember=0 limit 20;";
//die($qry);
if ($giorni != '' && $tipo_qry != '' && $time >= $time_start && $time <= $time_end) {
    $qry = "SELECT *, DATE_FORMAT(data, '%d/%m') dataapp, TIME_FORMAT(orario, '%H:%i') orarioapp,"
            . "COALESCE((select inviato_remember$giorni from calendario_spoki cs where cs.idcalendario=c.id limit 1), 0) as inviato_remember"
            . " FROM calendario c "
            . "WHERE idatelier != 137 and idatelier != 75 and idatelier != 71 AND data = (CURDATE() + INTERVAL $giorni DAY) AND data >= CURDATE() AND tipoappuntamento IN ($tipo_qry) AND disdetto = 0 and acquistato!='1' "
            //. "and id=93463 "
            . "having inviato_remember=0 limit 20;";
    $sql = $db->prepare($qry);
    $sql->execute();
    $res = $sql->fetchAll(PDO::FETCH_ASSOC);
    logReminder($qry, $time, $tipo_log);
    //die($qry);
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
                '&giorni=' . $giorni . '&tipo=' . $tipo;
        $endpoint = URL_WS . 'rememberAppuntamentoSPoki';
        $response = callWS($endpoint, $request);
        //die($response);
        $json = json_decode($response, false);
        if ($json->success == 1) {
            $qry = "select idcalendario from calendario_spoki where idcalendario=? limit 1;";
            $rs = $db->prepare($qry);
            $valori = Array($idappuntamento);
            $rs->execute($valori);
            if ($rs->RowCount() == 0) {
                $qry = "insert into calendario_spoki (inviato_remember$giorni, idcalendario) values (?,?);";
            } else {
                $qry = "update calendario_spoki set inviato_remember$giorni=? where idcalendario=?;";
            }
            $rs = $db->prepare($qry);
            $valori = Array(1, $idappuntamento);
            $rs->execute($valori);
        }
        sleep(1);
    }
}