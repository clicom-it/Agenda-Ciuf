<?php

ob_start('ob_gzhandler');
include './library/config.php';
include './library/connessione.php';
include './library/functions.php';

//include './library/phpmailer/PHPMailerAutoload.php';

/* ricorda saldo acquisto */
$sql = $db->prepare("SELECT * FROM calendario WHERE idatelier != 137 AND datamatrimonio = ? AND giornomatrimonio = ? AND datamatrimonio != '0000-00-00' AND acquistato = ?");
$sql->execute(array(DATE("Y-m-d"), 0, 1));
$res = $sql->fetchAll();
foreach ($res as $row) {
    $idappuntamento = $row['id'];
    $dataapp = $row['dataapp'];
    $orarioapp = $row['orarioapp'];
    
    $saldo = $row['saldo'];

    $telefonosms = "+39" . $row['telefono'];
    $emailutente = $row['email'];
    $emailatelier = $row['emailatelier'];

    $idatelier = $row['idatelier'];
    $nomeatelier = getDati("utenti", "WHERE id=$idatelier")[0]['nominativo'];

    $nomecliente = $row['nome'];
    $cognomecliente = $row['cognome'];

    

    $messaggiosms = "Lo staff di Come in una Favola di $nomeatelier vi augura tantissimi auguri affinché questo giorno sia l'inizio di un felice e lungo futuro insieme.";

    $usersms = 'info@comeinunafavola.it';
    $passwordsms = 'C0m3i4U4Af!';
    $sendersms = "COMEINUNAFA";

    $auth = loginSMS($usersms, $passwordsms);

    $smsSent = sendSMSnew($auth, array(
        "message" => $messaggiosms,
        "message_type" => MESSAGE_HIGH_QUALITY,
        "returnCredits" => false,
        "recipient" => array($telefonosms),
        "sender" => $sendersms     // Place here a custom sender if desired
    ));

    if ($smsSent->result == "OK") {
        $sqla = $db->prepare("UPDATE calendario SET augurimatr = ? WHERE id = ?");
        $sqla->execute(array(1, $idappuntamento));
    }
    sleep(5);
}