<?php

include '../config/connessione.php';
include '../library/phpgraphlib/phpgraphlib.php';
include '../library/basic.class.php';
include '../library/fatture.class.php';

$anno = $_GET['anno'];
$cliente = $_GET['cliente'];

if ($cliente) {
    $where = "AND cliente = ?";
    /* nome cliente */
    $sql = $db->prepare('SELECT azienda FROM clienti_fornitori WHERE id = ?');
    $sql->execute(array($cliente));
    $res = $sql->fetch();
    $nomecliente = utf8_decode(rawurldecode(stripslashes($res['azienda'])));
}

$arrmesi = array(
    "1" => "Gen",
    "2" => "Feb",
    "3" => "Mar",
    "4" => "Apr",
    "5" => "Mag",
    "6" => "Giu",
    "7" => "Lug",
    "8" => "Ago",
    "9" => "Set",
    "10" => "Ott",
    "11" => "Nov",
    "12" => "Dic"
);

/* richiamo i mesi */
$sql = $db->prepare('SELECT SUM(totale) as somma FROM fatture_vendita WHERE MONTH(data_fattura) = ? AND YEAR(data_fattura) = ? ' . $where . '');

for ($i = 1; $i <= 12; $i++) {
    if ($cliente) {
        $sql->execute(array($i, $anno, $cliente));
    } else {
        $sql->execute(array($i, $anno));
    }
    $res = $sql->fetchAll();
    foreach ($res as $row) {
        $data[$arrmesi[$i]] = round($row['somma'], 2);
    }
}
/* creo il grafico */
$graph = new PHPGraphLib(750, 400);
$graph->addData($data);
$graph->setTitle("Fatturato per mese anno " . $anno . " cliente: " . $nomecliente . "");
$graph->setDataPoints(true);
$graph->setDataPointColor('blue');
$graph->setLine(true);
$graph->setLineColor('blue');
$graph->setTextColor("black");
$graph->setDataValues(true);
$graph->setGradient('silver', 'gray');
$graph->createGraph();
?> 