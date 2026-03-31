<?php

ob_start('ob_gzhandler');
include './library/connessione.php';
include './library/config.php';
include './library/functions.php';
include './library/basic.class.php';
include './library/calendario.class.php';

$token = $_GET['t'];
if ($token != "" && verificaToken($token)) {
    if ($_GET['a'] == 1) {
        $table = "docs_atelier";
    } else {
        $table = "docs_utenti";
    }
    $qry = "select * from $table where MD5(id) = ? limit 1;";
    $rs = $db->prepare($qry);
    $valori = Array($_GET['id']);
    $rs->execute($valori);
    $col = $rs->fetch(PDO::FETCH_ASSOC);
    if ($_GET['a'] == 1) {
        $path_file = $_SERVER['DOCUMENT_ROOT'] . '/' . FOLDER_UTENTI . '/' . $col['idatelier'] . '/' . $col['idfather'] . '/' . $col['nomefile'];
    } else {
        $path_file = $_SERVER['DOCUMENT_ROOT'] . '/' . FOLDER_UTENTI . '/' . $col['idutente'] . '/' . $col['nomefile'];
    }
    //die($path_file);
    $mime = mime_content_type($path_file);
    $video = file_get_contents($path_file);
    header('Content-Type: ' . $mime . '; charset=utf-8');
    header('Content-disposition:inline; filename="' . $col['nomefile'] . '"');
    die($video);
}