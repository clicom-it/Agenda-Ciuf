<?php

include '../library/controllo.php';
include '../library/config.php';
include '../library/connessione.php';
include '../library/phpgraphlib-master/phpgraphlib.php';
include '../library/basic.class.php';
include '../library/functions.php';

$anno = $_GET['anno'];

$idcliente = $_GET['idcliente'];
if ($idcliente) {
    $where = "AND idcliente = $idcliente";
    $daticliente = getDati("clienti_fornitori", "WHERE id = $idcliente");
    $nomecliente = $daticliente[0]['azienda'];
}

/* richiamo i mesi */
$sql = $db->prepare('SELECT SUM(totalefatt_iva) as somma FROM fatture WHERE MONTH(data) = ? AND YEAR(data) = ? AND tipo = ? ' . $where . '');
for ($i = 1; $i <= 12; $i++) {
    $sql->execute(array($i, $anno, 0));
    $res = $sql->fetchAll();
    foreach ($res as $row) {
        $data[$arrmesi[$i]] = round($row['somma'], 2);
    }
}
/* creo il grafico */
$graph = new PHPGraphLib(800, 600);
$graph->addData($data);
$graph->setTitle("Fatturato totale per mese anno " . $anno . " $nomecliente");
$graph->setDataPoints(true);
$graph->setDataPointColor('blue');
$graph->setLine(true);
$graph->setLineColor('blue');
$graph->setTextColor("black");
$graph->setDataValues(true);
$graph->setGradient('silver', 'gray');
$graph->createGraph();
?> 