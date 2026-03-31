<?php

ob_start('ob_gzhandler');
include './library/config.php';
include './library/connessione.php';
include './library/functions.php';

//include './library/phpmailer/PHPMailerAutoload.php';



/* grazie per aver acquistato */
$sql = $db->prepare("SELECT * FROM calendario WHERE idatelier != 137 AND data >= (CURDATE() + INTERVAL 1 DAY) AND grazieacq = 0 AND acquistato = 1");
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

    $nomecliente = $row['nome'];
    $cognomecliente = $row['cognome'];

   

    $messaggiosms = "Ciao $nomecliente, l'Atelier Come in una Favola di $nomeatelier ti ringrazia per l'acquisto. Siamo sempre a disposizione per qualsiasi esigenza";

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
        $sqla = $db->prepare("UPDATE calendario SET grazieacq = ? WHERE id = ?");
        $sqla->execute(array(1, $idappuntamento));
    }
    sleep(5);
}
