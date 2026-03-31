<?php
ob_start('ob_gzhandler');
include './library/connessione.php';
include './library/config.php';
include './library/functions.php';
include './library/basic.class.php';
/* libreria del modulo */
include './library/dipendenti.class.php';
$video = getDati("formazione_corsi", "where id=" . $_GET['id'])[0];
?>
<!DOCTYPE html>
<html lang="it">
    <head></head>
    <body style="text-align: center;">
        <video controls>
            <source src="/<?=FOLDER_FORMAZIONE?>/<?=$_GET['id']?>/<?=$video['video']?>" type="video/mp4">
        </video> 
    </body>
</html>