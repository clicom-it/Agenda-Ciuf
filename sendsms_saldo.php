<?php

ob_start('ob_gzhandler');
include './library/config.php';
include './library/connessione.php';
include './library/functions.php';

//include './library/phpmailer/PHPMailerAutoload.php';

/* ricorda saldo acquisto */
$sql = $db->prepare("SELECT *, DATE_FORMAT(datasaldo, '%d/%m/%Y') as datasaldoit FROM calendario WHERE idatelier != 137 AND datasaldo <= (CURDATE() + INTERVAL 3 DAY) AND datasaldo >= CURDATE() AND inviatosaldo = ? AND saldo > 0 AND datasaldo != '0000-00-00'");
$sql->execute(array(0));
$res = $sql->fetchAll();
foreach ($res as $row) {
    $idappuntamento = $row['id'];

    $saldo = $row['saldo'];

    $datasaldoit = $row['datasaldoit'];

    $telefonosms = "+39" . $row['telefono'];
    $emailutente = $row['email'];
    $emailatelier = $row['emailatelier'];

    $idatelier = $row['idatelier'];
    $nomeatelier = getDati("utenti", "WHERE id=$idatelier")[0]['nominativo'];

    $nomecliente = $row['nome'];
    $cognomecliente = $row['cognome'];

    

    $messaggiosms = "Ciao $nomecliente, l'Atelier Come in una Favola di $nomeatelier ti ricorda che il $datasaldoit è la data concordata per il saldo del tuo acquisto: $saldo €.";

    
    $usersms = 'goldoni@comeinunafavola.it';
    $passwordsms = 'Esendex2024!';
    $sendersms = "ComeinunaFa";

    $auth = loginSMS($usersms, $passwordsms);

    $smsSent = sendSMSnew($auth, array(
        "message" => $messaggiosms,
        "message_type" => MESSAGE_HIGH_QUALITY,
        "returnCredits" => false,
        "recipient" => array($telefonosms),
        "sender" => $sendersms     // Place here a custom sender if desired
    ));

    if ($smsSent->result == "OK") {
        $sqla = $db->prepare("UPDATE calendario SET inviatosaldo = ? WHERE id = ?");
        $sqla->execute(array(1, $idappuntamento));
    }
    sleep(5);
}





