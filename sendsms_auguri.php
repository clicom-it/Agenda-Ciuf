<?php

ob_start('ob_gzhandler');
include './library/config.php';
include './library/connessione.php';
include './library/functions.php';

//include './library/phpmailer/PHPMailerAutoload.php';



/* auguri matrimonio */

$sql = $db->prepare("SELECT * FROM calendario WHERE datamatrimonio <= (CURDATE() - INTERVAL 30 DAY) AND augurimatr = 0 AND datamatrimonio != '0000-00-00'");
$sql->execute();
$res = $sql->fetchAll();
foreach ($res as $row) {
    $idappuntamento = $row['id'];
    $dataapp = $row['dataapp'];
    $orarioapp = $row['orarioapp'];

    $telefonosms = "+39" . $row['telefono'];
    $emailutente = $row['email'];
    $emailatelier = $row['emailatelier'];

    $idatelier = $row['idatelier'];
    $nomeatelier = getDati("utenti", "WHERE id=$idatelier")[0]['nominativo'];
    
    $direttoaffiliato = getDati("utenti", "WHERE id=$idatelier")[0]['diraff'];
    
    if ($direttoaffiliato == "d") {
        $emailatelier = "laurazampetti@comeinunafavola.it";
    }

    $nomecliente = $row['nome'];
    $cognomecliente = $row['cognome'];

    $usersms = 'comeinunafav';
    $passwordsms = 'C0m3i4U4Af';
    $sendersms = "COMEINUNAFA";

//    $messaggiosms = "Ciao $nomecliente, come va da sposati? Ci farebbe piacere ricevere qualche foto dell'evento, se ti va inviale a $emailatelier";
    $messaggiosms = "Ciao $nomecliente, come va da sposati? Se vi va, mandateci le foto della vostra favola cosi le pubblichiamo! Inviale a socialmedia@comeinunafavola.it";

    $result = sendsms($telefonosms, $messaggiosms);


    if (strpos($result, "OK") == 0 || strpos($result, "OK") > 0) {
        $sqla = $db->prepare("UPDATE calendario SET augurimatr = ? WHERE id = ?");
        $sqla->execute(array(1, $idappuntamento));
    }
    sleep(5);
}