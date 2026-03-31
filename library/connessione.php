<?php

define('TIMEZONE', 'Europe/Paris');
date_default_timezone_set(TIMEZONE);

// collegamento al database
$col = 'mysql:host=localhost;dbname=agecome_db2;charset=latin1;';
// blocco try per il lancio dell'istruzione
try {
    // connessione tramite creazione di un oggetto PDO
    $db = new PDO($col, 'agecome_usr', '!gQrOlkEoQ34bu8#', array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'latin1'"));
}
// blocco catch per la gestione delle eccezioni
catch (PDOException $e) {
    // notifica in caso di errorre
    echo 'Errore di connessione: ' . $e->getMessage();
}