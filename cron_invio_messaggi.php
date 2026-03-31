<?php

include './library/connessione.php';
include './library/config.php';
include './library/functions.php';
include './library/basic.class.php';
include './library/calendario.class.php';
include './library/phpmailer/PHPMailerAutoload.php';
ini_set("max_execution_time", 600);
$qry = "select * from invio_messaggio where inviato=0 order by data_ora";
$rs = $db->prepare($qry);
$rs->execute();
while ($col = $rs->fetch(PDO::FETCH_ASSOC)) {
    $idmessaggio = $col['idmessaggio'];
    //$mess = getDati("messaggi_app", "where id=" . $idmessaggio)[0];
    $qry = "select * from log_invio_messaggio where idmessaggio=$idmessaggio and inviato=0 group by idutente order by data_ora;";
    //die($qry);
    $rs2 = $db->prepare($qry);
    $rs2->execute();
    $num_da_inviare = $rs2->RowCount();
    $num_inviati = 0;
    while ($col2 = $rs2->fetch(PDO::FETCH_ASSOC)) {
        $idutente = $col2['idutente'];
        $response = inviaNotificaPushUtente($idutente, $idmessaggio);
        if ($response) {
            $qry = "update log_invio_messaggio set inviato=1 where idmessaggio=$idmessaggio and idutente=$idutente;";
            $db->exec($qry);
            $num_inviati++;
        }
    }
    if ($num_inviati == $num_da_inviare) {
        $qry = "update invio_messaggio set inviato=1 where idmessaggio=$idmessaggio;";
        $db->exec($qry);
    }
}
?>
