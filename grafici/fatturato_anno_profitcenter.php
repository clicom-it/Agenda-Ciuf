<?php

include '../library/controllo.php';
include '../library/config.php';
include '../library/connessione.php';
include '../library/phpgraphlib-master/phpgraphlib.php';
include '../library/basic.class.php';
include '../library/functions.php';

$anno = $_GET['anno'];
$mese = $_GET['mese'];
$idcliente = $_GET['idcliente'];

if ($idcliente) {
    $where = "AND idcliente = $idcliente";
    $daticliente = getDati("clienti_fornitori", "WHERE id = $idcliente");
    $nomecliente = $daticliente[0]['azienda'];
}

$dati = meseprofit($anno, $mese, $where);

/* creo il grafico */
$graph = new PHPGraphLib(800, 600);
$graph->addData($dati);
$graph->setTitle("Fatturato per Profit center ".$arrmesiesteso[$mese]." " . $anno . " $nomecliente");
$graph->setDataPoints(true);
$graph->setDataPointColor('blue');
$graph->setLineColor('blue');
$graph->setTextColor("black");
$graph->setDataValues(true);
$graph->setGradient('silver', 'gray');
$graph->setXValuesHorizontal(true);
$graph->createGraph();
?> 