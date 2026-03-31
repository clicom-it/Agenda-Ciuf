<?php

ob_start('ob_gzhandler');
include './library/config.php';
include './library/connessione.php';
include './library/functions.php';

//include './library/phpmailer/PHPMailerAutoload.php';


/* remember appuntamento */
$sql = $db->prepare("SELECT *, DATE_FORMAT(data, '%d/%m') dataapp, TIME_FORMAT(orario, '%H:%i') orarioapp FROM calendario WHERE idatelier != 137 AND data <= (CURDATE() + INTERVAL 2 DAY) AND data >= CURDATE() AND tipoappuntamento IN (1,2,6) AND ricordaapp = 0 AND disdetto = 0");
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

   

//    $messaggiosms = "Ciao $nomecliente conferma l'appuntamento del $dataapp alle $orarioapp da Come in una Favola $nomeatelier e Invia a $emailatelier esempi di abiti di tuo interesse";
    $messaggiosms = "Ciao $nomecliente ti ricordiamo l'appuntamento del $dataapp alle $orarioapp da Come in una Favola $nomeatelier. Per favore, avvisaci in caso di disdetta inviando un messaggio al 3400733351";

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
        $sqla = $db->prepare("UPDATE calendario SET ricordaapp = ? WHERE id = ?");
        $sqla->execute(array(1, $idappuntamento));
    }
    sleep(5);
}