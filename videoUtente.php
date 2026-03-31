<?php
ob_start('ob_gzhandler');
include './library/connessione.php';
include './library/config.php';
include './library/functions.php';
include './library/basic.class.php';
include './library/calendario.class.php';

$token = $_GET['t'];
if ($token != "" && verificaToken($token)) {
    $qry = "select * from docs_atelier where MD5(id) = ? limit 1;";
    $rs = $db->prepare($qry);
    $valori = Array($_GET['id']);
    $rs->execute($valori);
    $col = $rs->fetch(PDO::FETCH_ASSOC);
    $src = '/' . FOLDER_UTENTI . '/' . $col['idatelier'] . '/' . $col['idfather'] . '/' . $col['nomefile'];
}
?>
<!DOCTYPE html>
<html lang="it">
    <head></head>
    <body style="text-align: center;">
        <video controls>
            <source src="<?= $src ?>" type="video/mp4">
        </video> 
    </body>
</html>