<?php

ob_start('ob_gzhandler');
include './library/config.php';
include './library/connessione.php';
include './library/functions.php';

//include './library/phpmailer/PHPMailerAutoload.php';


/* remember appuntamento */
//$sql = $db->prepare("SELECT * FROM calendario WHERE acquistato = 1 AND data >= '2022-05-01' AND idatelier = 91");
//$sql = $db->prepare("SELECT * FROM calendario WHERE acquistato = 1 AND data >= '2022-05-01' AND idatelier = 86");
//$sql = $db->prepare("SELECT * FROM calendario WHERE acquistato = 1 AND data >= '2022-05-01' AND idatelier = 81");
//$sql = $db->prepare("SELECT * FROM calendario WHERE acquistato = 1 AND data >= '2022-05-01' AND idatelier = 59");
//$sql = $db->prepare("SELECT * FROM calendario WHERE acquistato = 1 AND data >= '2022-05-01' AND idatelier = 79");
//$sql = $db->prepare("SELECT * FROM calendario WHERE acquistato = 1 AND data >= '2022-05-01' AND idatelier = 103");
//$sql = $db->prepare("SELECT * FROM calendario WHERE acquistato = 1 AND data >= '2022-05-01' AND idatelier = 90");
//$sql = $db->prepare("SELECT * FROM calendario WHERE acquistato = 1 AND data >= '2022-05-01' AND idatelier = 60");
//$sql = $db->prepare("SELECT * FROM calendario WHERE acquistato = 1 AND data >= '2022-05-01' AND idatelier = 113");
//$sql = $db->prepare("SELECT * FROM calendario WHERE acquistato = 1 AND data >= '2022-05-01' AND idatelier = 65");
//$sql = $db->prepare("SELECT * FROM calendario WHERE acquistato = 1 AND data >= '2022-05-01' AND idatelier = 82");
//$sql = $db->prepare("SELECT * FROM calendario WHERE acquistato = 1 AND data >= '2022-05-01' AND idatelier = 97");
//$sql = $db->prepare("SELECT * FROM calendario WHERE acquistato = 1 AND data >= '2022-05-01' AND idatelier = 126");
//$sql = $db->prepare("SELECT * FROM calendario WHERE acquistato = 1 AND data >= '2022-05-01' AND idatelier = 49");
//$sql = $db->prepare("SELECT * FROM calendario WHERE acquistato = 1 AND data >= '2022-05-01' AND idatelier = 61");
//$sql = $db->prepare("SELECT * FROM calendario WHERE acquistato = 1 AND data >= '2022-05-01' AND idatelier = 57");
//$sql = $db->prepare("SELECT * FROM calendario WHERE acquistato = 1 AND data >= '2022-05-01' AND idatelier = 129");
//$sql = $db->prepare("SELECT * FROM calendario WHERE acquistato = 1 AND data >= '2022-05-01' AND idatelier = 101");
//$sql = $db->prepare("SELECT * FROM calendario WHERE acquistato = 1 AND data >= '2022-05-01' AND idatelier = 100");
//$sql = $db->prepare("SELECT * FROM calendario WHERE acquistato = 1 AND data >= '2022-05-01' AND idatelier = 111");
$sql = $db->prepare("SELECT * FROM calendario WHERE acquistato = 1 AND data >= '2022-05-01' AND idatelier = 93");

$sql->execute();
$res = $sql->fetchAll();

$i = 1;

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

    $messaggiosms = "Come in una Favola Summer Sales 
28 | 29 | 30 luglio
Abiti sposa 850€
Abiti sposo 450€
Abiti cerimonia 150€ 
Prenota ora un appuntamento! ";
//    $result = sendsms($telefonosms, $messaggiosms);

//    sleep(3);
    echo "$i - ". $telefonosms."<br />";    
    $i++;
}


