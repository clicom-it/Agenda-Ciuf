<?php
$host = '151.4.150.73';
$port = 9043;
$user = 'extern';
$pw = 'Soft_comm@2025';
$database = 'PassepartoutRetail';
$qry = 'Select codiceABarre, dataCreazione, importo from BuonoScontowhere dataUtilizzo is null';
// collegamento al database
$col = 'mysql:host='.$host.':'.$host.';dbname='.$database.';charset=latin1;';
$col = 'sqlsrv:Server='.$host.':'.$port.';Database='.$database.'';
// blocco try per il lancio dell'istruzione
try {
    // connessione tramite creazione di un oggetto PDO
    $db = new PDO($col, $user, $pw, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'latin1'"));
}
// blocco catch per la gestione delle eccezioni
catch (PDOException $e) {
    // notifica in caso di errorre
    echo 'Errore di connessione: ' . $e->getMessage();
}
$rs = $db->prepare($qry);
$rs->execute();
$cols = $rs->fetchAll(PDO::FETCH_ASSOC);
var_dump($cols);
?>