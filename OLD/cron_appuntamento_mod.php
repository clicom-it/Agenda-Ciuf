<?php

ob_start('ob_gzhandler');
include './library/config.php';
include './library/connessione.php';
include './library/functions.php';
ini_set("max_execution_time", 900);
$time = date('H:i');
$time_start = '12:00';
$time_end = '14:00';
$tipo_log = 'Modifica';
/* modifica appuntamento */
# CRON */25 12-14 * * *
//$qry = "SELECT *, DATE_FORMAT(data, '%d/%m') dataapp, TIME_FORMAT(orario, '%H:%i') orarioapp,"
//            . "COALESCE((select inviato_modifica from calendario_spoki cs where cs.idcalendario=c.id limit 1), 0) as inviato_modifica,"
//            . "COALESCE((select modificato_sale10 from calendario_spoki cs where cs.idcalendario=c.id limit 1), 0) as modificato_sale10"
//            . " FROM calendario c "
//            . "WHERE idatelier != 137 and idatelier != 75 and idatelier != 71 AND data = (CURDATE() + INTERVAL 10 DAY) AND tipoappuntamento IN (1,2,3,4,6) AND disdetto = 0 and acquistato!='1' /*and id=93556*/ "
//            . "having inviato_modifica=0 and modificato_sale10=0 limit 30;";
//die($qry);
if ($time >= $time_start && $time <= $time_end) {
    $qry = "SELECT *, DATE_FORMAT(data, '%d/%m') dataapp, TIME_FORMAT(orario, '%H:%i') orarioapp,"
            . "COALESCE((select inviato_modifica from calendario_spoki cs where cs.idcalendario=c.id limit 1), 0) as inviato_modifica,"
            . "COALESCE((select modificato_sale10 from calendario_spoki cs where cs.idcalendario=c.id limit 1), 0) as modificato_sale10"
            . " FROM calendario c "
            . "WHERE idatelier != 137 and idatelier != 75 and idatelier != 71 AND data = (CURDATE() + INTERVAL 10 DAY) AND tipoappuntamento IN (1,2,3,4,6) AND disdetto = 0 and acquistato!='1' /*and id=93556*/ "
            . "having inviato_modifica=0 and modificato_sale10=0 limit 30;";
//die($qry);
    $sql = $db->prepare($qry);
    $sql->execute();
    $res = $sql->fetchAll(PDO::FETCH_ASSOC);
    logReminder($qry, $time, $tipo_log);
    foreach ($res as $row) {
        $idappuntamento = $row['id'];
        $dataapp = $row['data'];
        $orarioapp = $row['orario'];

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
                '&citta=' . urlencode($atelier['nominativo']) .
                '&compleanno=' .
                '&data=' . urlencode($dataapp) .
                '&ora=' . urlencode($orarioapp . ':00') .
                '&idapp=' . $idappuntamento;
        $endpoint = URL_WS . 'modificaAppuntamentoSPoki';
        //die($request);
        $response = callWS($endpoint, $request);
        $json = json_decode($response, false);
        if ($json->success == 1) {
            $qry = "select idcalendario from calendario_spoki where idcalendario=? limit 1;";
            $rs = $db->prepare($qry);
            $valori = Array($idappuntamento);
            $rs->execute($valori);
            if ($rs->RowCount() == 0) {
                $qry = "insert into calendario_spoki (inviato_modifica, data_inviato_modifica, idcalendario) values (?,?,?);";
            } else {
                $qry = "update calendario_spoki set inviato_modifica=?, data_inviato_modifica=? where idcalendario=?;";
            }
            $rs = $db->prepare($qry);
            $valori = Array(1, date('Y-m-d'), $idappuntamento);
            $rs->execute($valori);
        }
        sleep(1);
    }
}