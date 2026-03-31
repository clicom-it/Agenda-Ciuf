<?php
ob_start('ob_gzhandler');
include './library/connessione.php';
include './library/controllo.php';
include './library/config.php';
include './library/functions.php';
include './library/basic.class.php';
/* libreria del modulo */
include './library/fatture.class.php';

$tabella = "fatture";
$dati = new fatture($id, $tabella);
$idcliente = $_GET['idcliente'];
$dal_db = $_GET['data1'];
$al_db = $_GET['data2'];

/* sistemo periodo */
if (!$dal_db) {
    $dal_db = DATE('Y') . "-01-01";
}
if (!$al_db) {
    $al_db = DATE('Y-m-d');
}
/* stato fattura */
if ($_GET['pagata']) {
    $arrstato[] = "'1'";
}
if ($_GET['nonpagata']) {
    $arrstato[] = "'0'";
}
if ($arrstato) {
$statofattura = "AND stato IN (" . join($arrstato, ",") . ")";
}
/* tipo fattura */
if ($_GET['vendita']) {
    $arrtipo[] = "'0'";
}
if ($_GET['proforma']) {
    $arrtipo[] = "'2'";
}
if ($_GET['nota']) {
    $arrtipo[] = "'3'";
}
if ($_GET['acquisto']) {
    $arrtipo[] = "'1'";
}
if ($_GET['pa']) {
    $arrtipo[] = "'4'";
}
if ($arrtipo) {
$tipofattura = "AND tipo IN (" . join($arrtipo, ",") . ")";
}
/* cliente */
if ($idcliente > 0) {
    $andcliente = "AND idcliente = $idcliente";
}
/**/

$where = "data BETWEEN '" . $dal_db . "' AND '" . $al_db . "' $tipofattura $statofattura $andcliente ORDER BY tipo, numero, data";

$fatture = $dati->richiamaWheredata($where);


$filename = "export_fatture_".DATE("d-m-Y").".xls";
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: inline; filename=$filename");
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html lang=it><head>
        <title>Titolo</title></head>
    <body>
        <table border="1">
            <tr>
                <td><b>Num.</b></td>
                <td><b>Data</b></td>
                <td><b>Tipo</b></td>
                <td><b>Cliente</b></td>
                <td><b>Pagamento</b></td>
                <td><b>Totale</b></td>
                <td><b>Totale con iva</b></td>
                <td><b>Stato</b></td>
            </tr>
            <?php
            if ($fatture) {
                foreach ($fatture as $fattured) {

                    $idcliente = $fattured['idcliente'];
                    $daticliente = getDati("clienti_fornitori", "WHERE id = $idcliente");

                    $tipo = $fattured['tipo'];
                    if ($tipo == '0') {
                        $tipof = "Vendita";
                    } else if ($tipo == '2') {
                        $tipof = "Proforma";
                    } else if ($tipo == '3') {
                        $tipof = "Nota di credito";
                    } else if ($tipo == '1') {
                        $tipof = "Acquisto";
                    } else if ($tipo == '4') {
                        $tipof = "Pubblica Amministrazione";
                    }
                    
                    if ($tipo == 3) {
                        $totnoiva = number_format(-$fattured['totalefatt'], 2, ",", "");
                        $totiva = number_format(-$fattured['totalefatt_iva'], 2, ",", "");
                    } else {
                        $totnoiva = number_format($fattured['totalefatt'], 2, ",", "");
                        $totiva = number_format($fattured['totalefatt_iva'], 2, ",", "");
                    }

                    $stato = $fattured['stato'];
                    if ($stato > 0) {
                        $statof = "Pagata";
                    } else {
                        $statof = "Non pagata";
                    }
                    print("<tr>"
                            . "<td>" . $fattured['numero'] . "</td>"
                            . "<td>" . $fattured['datait'] . "</td>"
                            . "<td>" . $tipof . "</td>"
                            . "<td>" . $daticliente[0]['azienda'] . "</td>"
                            . "<td>" . $fattured['metodopagamento'] . "</td>"
                            . "<td>" . $totnoiva . "</td>"
                            . "<td>" . $totiva . "</td>"
                            . "<td>" . $statof . "</td>"
                            . "</tr>");
                }
            }
            ?>


        </table>
    </body>
</html>