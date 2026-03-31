<?php

ob_start('ob_gzhandler');
include '../library/controllo.php';
include '../library/config.php';
include '../library/connessione.php';
include '../library/functions.php';
include '../library/basic.class.php';
/* libreria del modulo */
include '../library/fatture.class.php';

/* dati preventivo e voci preventivo */
$tabella = "fatture";
$id = $_GET['idfatt'];
$dati = new fatture($id, $tabella);

if (is_array($id)) {
    
} else {
    $where = "id = $id";
    $fattura = $dati->richiamaWheredata($where);
    $numero = $fattura[0]['numero'];
    $datafatt = $fattura[0]['data'];
    $daticliente = str_replace("\n", "<br />", $fattura[0]['daticliente']);
    $arrdaticli = explode("<br />", $daticliente);
    $firma = $arrdaticli[0];
    $xml = generaFatturaElettronica($fattura[0]);
    header('Content-Disposition: attachment; filename="fattura_' . ((int) $fattura[0]['tipo'] == 6 ? 'FPC' : '') . ((int) $fattura[0]['tipo'] == 5 ? 'FPC' : '') . $numero . '_' . $datafatt . '_' . pulisciImmagine($firma) . '.xml"');
    header('Content-Type: text/xml'); # Don't use application/force-download - it's not a real MIME type, and the Content-Disposition header is sufficient
    header('Content-Length: ' . strlen($xml));
    header('Connection: close');
    die($xml);
}
?>
