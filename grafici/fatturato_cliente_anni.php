<?php

include '../config/connessione.php';
include '../library/phpgraphlib/phpgraphlib.php';
include '../library/basic.class.php';
include '../library/fatture.class.php';

$anno1 = $_GET['anno1'];
$anno2 = $_GET['anno2'];
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
        $sql->execute(array($i, $anno1, $cliente));
    } else {
        $sql->execute(array($i, $anno1));
    }
    $res = $sql->fetchAll();
    foreach ($res as $row) {
        $data[$arrmesi[$i]] = round($row['somma'], 2);
    }
}

$sql = $db->prepare('SELECT SUM(totale) as somma FROM fatture_vendita WHERE MONTH(data_fattura) = ? AND YEAR(data_fattura) = ? ' . $where . '');

for ($i = 1; $i <= 12; $i++) {
    if ($cliente) {
        $sql->execute(array($i, $anno2, $cliente));
    } else {
        $sql->execute(array($i, $anno2));
    }
    $res = $sql->fetchAll();
    foreach ($res as $row) {
        $data2[$arrmesi[$i]] = round($row['somma'], 2);
    }
}
/* creo il grafico */
$graph = new PHPGraphLib(750, 400);
$graph->setTitle($nomecliente . " anni:" . $anno1 . " - " . $anno2);
$graph->addData($data, $data2);
$graph->setBarColor('blue', 'green');
$graph->setTextColor("black");
$graph->setLegend(true);
$graph->setTitleLocation('left');
$graph->setLegendTitle($anno1, $anno2);



//$graph->setTextColor("black");
$graph->setDataValues(true);
$graph->setDataValueColor('black');
//$graph->setGradient('silver', 'gray');
//$graph->createGraph();
$graph->createGraph();
?> 