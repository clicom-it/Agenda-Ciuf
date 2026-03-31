<?php

ob_start('ob_gzhandler');
include './library/connessione.php';
include './library/controllo.php';
include './library/config.php';
include './library/functions.php';
include './library/basic.class.php';
include './library/calendario.class.php';

$token = $_GET['t'];
if ($token != "" && verificaToken($token)) {
    $qry = "select * from file_appuntamenti where MD5(id) = ? limit 1;";
    $rs = $db->prepare($qry);
    $valori = Array($_GET['id']);
    $rs->execute($valori);
    $col = $rs->fetch(PDO::FETCH_ASSOC);
    $path_file = $_SERVER['DOCUMENT_ROOT'] . '/appuntamenti/' . $col['idappuntamento'] . '/' . $col['nomefile'];
    $pdf = file_get_contents($path_file);
    header('Content-Type: application/pdf; charset=utf-8');
    die($pdf);
}