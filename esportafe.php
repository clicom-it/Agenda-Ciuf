<?php

ob_start('ob_gzhandler');
include './library/connessione.php';
include './library/controllo.php';
include './library/config.php';
include './library/functions.php';
include './library/basic.class.php';
/* libreria del modulo */
include './library/fatture.class.php';

$tabella = "fatture";
$dati = new fatture($id, $tabella);
$idcliente = $_GET['idcliente'];
$dal_db = $_GET['data1'];
$al_db = $_GET['data2'];

/* sistemo periodo */
if (!$dal_db) {
    $dal_db = DATE('Y') . "-01-01";
}
if (!$al_db) {
    $al_db = DATE('Y-m-d');
}

/*
 * 
 * create random dir */
$dir = randomPassword(21);
$old = umask(0);
mkdir("./tmp/$dir", 0777, true);
umask($old);
/**/

$where = "data BETWEEN '" . $dal_db . "' AND '" . $al_db . "' ORDER BY tipo, numero, data;";

$fattura = $dati->richiamaWheredata($where);

foreach ($fattura as $fatturad) {
    $numero = $fatturad['numero'];
    $datafatt = $fatturad['data'];
    $daticliente = str_replace("\n", "<br />", $fatturad['daticliente']);
    $arrdaticli = explode("<br />", $daticliente);
    $firma = $arrdaticli[0];

    $content = "";
    $id = $fatturad['id'];
    $tipo = $fatturad['tipo'];

    $xml = generaFatturaElettronica($fatturad);
    $path = './tmp/' . $dir . '/fattura_' . $numero . '_' . $datafatt . '_' . pulisciImmagine($firma) . '.xml';
    file_put_contents($path, $xml);
}
//die();
/* create zip file */
$zip = new ZipArchive;
$download = 'tmp/fatture_' . $dir . '.zip';
$zip->open($download, ZipArchive::CREATE);
foreach (glob("tmp/$dir/*.xml") as $file) {
    $zip->addFile($file, basename($file));
}
$zip->close();

/* cancello la dir */
// Cancello tutti i file nella cartella
$path = "./tmp/$dir/";

foreach (glob($path . "*.*") as $file) {
    unlink($file);
}
// cancello la dir temporanea
rmdir("./tmp/$dir");
//
header('Content-Type: application/zip');
header("Content-Disposition: attachment; filename = $download");
header('Content-Length: ' . filesize($download));
header("Location: $download");
