<?php

ob_start('ob_gzhandler');
include './library/connessione.php';
include './library/config.php';
include './library/functions.php';
include './library/basic.class.php';
include './library/calendario.class.php';

$token = $_GET['t'];
if ($token != "" && verificaToken($token)) {
    $qry = "select * from media_corsi where MD5(id) = ? limit 1;";
    $rs = $db->prepare($qry);
    $valori = Array($_GET['id']);
    $rs->execute($valori);
    $col = $rs->fetch(PDO::FETCH_ASSOC);
    $path_file = $_SERVER['DOCUMENT_ROOT'] . '/' . FOLDER_FORMAZIONE . '/' . $col['idcorso'] . '/' . $col['nomefile'];    
    $mime = mime_content_type($path_file);
    $video = file_get_contents($path_file);
    header('Content-Type: ' . $mime . '; charset=utf-8');
    die($video);
}