<?php

include '../library/controllo.php';
include '../library/config.php';
include '../library/connessione.php';
include '../library/phpgraphlib-master/phpgraphlib.php';
include '../library/phpgraphlib-master/phpgraphlib_pie.php';
include '../library/basic.class.php';
include '../library/functions.php';

$anno = $_GET['anno'];
$idcliente = $_GET['idcliente'];


if ($idcliente) {
    $where = "AND idcliente = $idcliente";
    $daticliente = getDati("clienti_fornitori", "WHERE id = $idcliente");
    $nomecliente = $daticliente[0]['azienda'];
}

$data = totaleprofit($anno, $where);

/* creo il grafico */
$graph = new PHPGraphLibPie(800, 300);
$graph->addData($data);
$graph->setTitle('Percentuali Profit center sul fatturato');
$graph->setLabelTextColor('black');
$graph->setLegendTextColor('black');
$graph->createGraph();
?> 