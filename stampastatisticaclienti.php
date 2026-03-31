<?php
ob_start('ob_gzhandler');
include './library/connessione.php';
include './library/controllo.php';
include './library/config.php';
include './library/functions.php';
include './library/basic.class.php';
/* libreria del modulo */
include './library/fatture.class.php';

$anno = $_GET['anno'];
$idcliente = $_GET['idcliente'];

if (!$anno) {
    $anno = DATE("Y");
}

$nomecliente = "";
            if ($idcliente) {
                $daticliente = getDati("clienti_fornitori", "WHERE id = $idcliente");
                $nomecliente = "PER CLIENTE: ".$daticliente[0]['azienda'];
            }


$filename = "export_stat_fatt_cliente_" . DATE("d-m-Y") . ".xls";
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: inline; filename=$filename");
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html lang=it><head>
        <title>Titolo</title></head>
    <body>
        <table border="1">
            <tr>
                <td><strong>FATTURATO ANNUO  <?php echo $anno.$nomecliente; ?></strong></td><td></td>
            </tr>
            <tr>
                <td></td><td></td>
            </tr>
            <?php
            $where = "";
            if ($idcliente) {
                $where = "AND idcliente = $idcliente";
            }

            $sql = $db->prepare("SELECT idcliente, (SELECT azienda FROM clienti_fornitori WHERE id = idcliente) as nomecliente, SUM(Case When tipo IN('0', '4') THEN totalefatt_iva ELSE 0 end) - SUM(Case When tipo = '3' THEN totalefatt_iva ELSE 0 end) as fatturatoclienteanno FROM fatture WHERE YEAR(data) = ? $where GROUP BY idcliente ORDER BY fatturatoclienteanno DESC ");
            $sql->execute(array($anno));
            $res = $sql->fetchAll();
            foreach ($res as $row) {
                $cliente = $row['nomecliente'];
                $fatturatocliente = $row['fatturatoclienteanno'];

                print("<tr>"
                        . "<td>"
                        . "$cliente"
                        . "</td>"
                        . "<td>"
                        . "" . number_format($fatturatocliente, 2, ',', '.') . " &euro;"
                        . "</td>"
                        . "</tr>");
            }
            

            /* mensile */

            for ($i = 1; $i < 13; $i++) {

                $sql2 = $db->prepare("SELECT idcliente, (SELECT azienda FROM clienti_fornitori WHERE id = idcliente) as nomecliente, SUM(Case When tipo IN('0', '4') THEN totalefatt_iva ELSE 0 end) - SUM(Case When tipo = '3' THEN totalefatt_iva ELSE 0 end) as fatturatoclientemese FROM fatture WHERE YEAR(data) = ? AND MONTH(data) = ? $where GROUP BY idcliente ORDER BY fatturatoclientemese DESC");
                $sql2->execute(array($anno, $i));
                if ($sql2->rowCount() > 0) {
                    $res2 = $sql2->fetchAll();

                    $clientifatturatomensile = "";

                    foreach ($res2 as $row2) {
                        $cliente = $row2['nomecliente'];
                        $fatturatoclientemese = $row2['fatturatoclientemese'];

                        $clientifatturatomensile .= "<tr>"
                        . "<td>"
                        . "$cliente"
                        . "</td>"
                        . "<td>"
                        . "" . number_format($fatturatoclientemese, 2, ',', '.') . " &euro;"
                        . "</td>"
                        . "</tr>";
                    }



                    print("<tr><td></td><td></td></tr><tr><td><strong>FATTURATO MENSILE $anno - " . $arrmesiesteso[$i] . " $nomecliente </strong></td><td></td></tr>"
                            . "$clientifatturatomensile");
                }
            }
            ?>


        </table>
    </body>
</html>