<?php

ob_start('ob_gzhandler');
include './library/config.php';
include './library/connessione.php';
include './library/functions.php';

include './library/phpmailer/PHPMailerAutoload.php';

function sendsms($telefonosms, $messaggiosms) {

    $usersms = 'comeinunafav';
    $passwordsms = 'C0m3i4U4Af';
    $sendersms = "COMEINUNAFA";

    //set POST variables
    $url = 'http://sms.clicom.it/sms/send.php';
    $fields = array(
        'user' => urlencode($usersms),
        'pass' => urlencode($passwordsms),
        'rcpt' => urlencode($telefonosms),
        'data' => urlencode($messaggiosms),
        'sender' => urlencode($sendersms),
        'qty' => 'h',
        'operation' => 'TEXT'
    );

    //url-ify the data for the POST
    foreach ($fields as $key => $value) {
        $fields_string .= $key . '=' . $value . '&';
    }
    rtrim($fields_string, '&');

    //open connection
    $ch = curl_init();

    //set the url, number of POST vars, POST data
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, count($fields));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    //execute post
    $result = curl_exec($ch);
    //close connection
    curl_close($ch);

    return $result;
}

/* remember appuntamento */
$sql = $db->prepare("SELECT *, DATE_FORMAT(data, '%d/%m') dataapp, TIME_FORMAT(orario, '%H:%i') orarioapp FROM calendario WHERE data <= (CURDATE() + INTERVAL 2 DAY) AND ricordaapp = 0");
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

    $usersms = 'comeinunafav';
    $passwordsms = 'C0m3i4U4Af';
    $sendersms = "COMEINUNAFA";

    $messaggiosms = "Ciao $nomecliente ricorda l'appuntamento del $dataapp alle $orarioapp da Come in una Favola $nomeatelier e Invia a $emailatelier esempi di abiti di tuo interesse";

    sendsms($telefonosms, $messaggiosms);


    if (strpos($result, "OK") == 0 || strpos($result, "OK") > 0) {
        $sqla = $db->prepare("UPDATE calendario SET ricordaapp = ? WHERE id = ?");
        $sqla->execute(array(1, $idappuntamento));
    }
    sleep(5);
}


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
    $messaggiosms = "Ciao $nomecliente, come va da sposati? Se vi va, mandateci le foto della vostra favola cosi le pubblichiamo!, Inviale a ufficiovendite@comeinunafavola.it";

    sendsms($telefonosms, $messaggiosms);


    if (strpos($result, "OK") == 0 || strpos($result, "OK") > 0) {
        $sqla = $db->prepare("UPDATE calendario SET augurimatr = ? WHERE id = ?");
        $sqla->execute(array(1, $idappuntamento));
    }
    sleep(5);
}

/* grazie per aver acquistato */
$sql = $db->prepare("SELECT * FROM calendario WHERE data <= (CURDATE() + INTERVAL 1 DAY) AND grazieacq = 0 AND acquistato = 1");
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

    $usersms = 'comeinunafav';
    $passwordsms = 'C0m3i4U4Af';
    $sendersms = "COMEINUNAFA";

    $messaggiosms = "Ciao $nomecliente, l'Atelier Come in una Favola $nomeatelier ti ringrazia per l'acquisto. Siamo sempre a disposizione per qualsiasi esigenza";

    sendsms($telefonosms, $messaggiosms);

    if (strpos($result, "OK") == 0 || strpos($result, "OK") > 0) {
        $sqla = $db->prepare("UPDATE calendario SET grazieacq = ? WHERE id = ?");
        $sqla->execute(array(1, $idappuntamento));
    }
    sleep(5);
}

/* ricorda saldo acquisto */
$sql = $db->prepare("SELECT * FROM calendario WHERE datasaldo = ? AND inviatosaldo = ?");
$sql->execute(array(DATE("Y-m-d"), 0));
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

    $usersms = 'comeinunafav';
    $passwordsms = 'C0m3i4U4Af';
    $sendersms = "COMEINUNAFA";

    $messaggiosms = "Ciao $nomecliente, l'Atelier Come in una Favola $nomeatelier ti ricorda che oggi è la data concordata per il saldo del tuo acquisto: $saldo €.";

    sendsms($telefonosms, $messaggiosms);

    if (strpos($result, "OK") == 0 || strpos($result, "OK") > 0) {
        $sqla = $db->prepare("UPDATE calendario SET inviatosaldo = ? WHERE id = ?");
        $sqla->execute(array(1, $idappuntamento));
    }
    sleep(5);
}