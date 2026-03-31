<?php
ob_start('ob_gzhandler');
include './library/connessione.php';
include './library/controllo.php';
include './library/config.php';
include './library/functions.php';
include './library/basic.class.php';
/* libreria del modulo */
include './library/scadenziario.class.php';
/**/
$submit = "";
if (isset($_POST['submit'])) {
    $submit = $_POST['submit'];
}

$op = "";
if (isset($_GET['op'])) {
    $op = $_GET['op'];
}

switch ($submit) {
    case "mostrascadenze":
        $tabella = "fatture_scadenze";
        $tipo = $_POST['tipo'];
        $datiscad = new scadenziario($id, $tabella);
        $where = "sufattura = '1' AND tiposcadenza IN ($tipo,3,4,5,6) ORDER BY datascadenza ASC";
        $scadenze = $datiscad->richiamaWheredatascad($where);

        for ($i = 0; $i < count($scadenze); $i++) {
            $sql = $db->prepare("SELECT numero, DATE_FORMAT(data, '%d/%m/%Y') as datait, YEAR(data) as annofatt, daticliente, metodopagamento, tipo FROM fatture WHERE id = ? LIMIT 1");
            $sql->execute(array($scadenze[$i]['idfattura']));
            $res = $sql->fetch();
            $numero = $res['numero'];
            $datafatt = $res['datait'];
            $annofatt = $res['annofatt'];
            $daticliente = $res['daticliente'];
            $metodopagamento = $res['metodopagamento'];
            $tipofattura = $res['tipo'];
            $scadenze[$i]['numerofattura'] = $numero . "/" . $annofatt;
            $scadenze[$i]['datafattura'] = $datafatt;
            $scadenze[$i]['daticliente'] = $daticliente;
            $scadenze[$i]['metodopagamento'] = $metodopagamento;
            $scadenze[$i]['tipo'] = $tipofattura;
            if ($tipofattura == 3) {
                $scadenze[$i]['importoscadenza'] = -$scadenze[$i]['importoscadenza'];
            } else {
                $scadenze[$i]['importoscadenza'] = $scadenze[$i]['importoscadenza'];
            }
        }

        die('{"dati" : ' . json_encode($scadenze) . '}');
        break;

    case "editscadenza":
        $tabella = "fatture_scadenze";
        $id = $_POST['id'];
        
        $campi = array("notescadenza", "stato");
        $valori = array($_POST['notescadenza'], $_POST['stato']);

        $dati = new scadenziario($id, $tabella);
        $dati->aggiorna($campi, $valori);
        die('{"msg" : "ok"}');
        break;

    default:
        break;
}
?>
<!DOCTYPE html>
<html lang="it">
    <head>
        <?php include './components/header.php'; ?>
        <!-- header del modulo -->
        <script type="text/javascript" src="./js/functions-scadenziario.js"></script>

        <link type="text/css" rel="stylesheet" href="./js/jsgrid-1.5.1/jsgrid.min.css" />
        <link type="text/css" rel="stylesheet" href="./js/jsgrid-1.5.1/jsgrid-theme.min.css" />

        <script type="text/javascript" src="./js/jsgrid-1.5.1/jsgrid.min.js"></script>
        <script type="text/javascript" src="./js/jsgrid-1.5.1/i18n/jsgrid-it.js"></script>
        <!-- lingua italiana datepicker -->
        <script src="./js/jquery-ui-1.11.4/datepicker-it.js"></script>

        <script type="text/javascript">
            $(document).ready(function () {
                mostraScadenze(0);
            });
        </script>


    </head>
    <body class="colormodulo">
        <?php include './components/top.php'; ?>
        <?php if (moduloattivo("preventivi") > 0) { ?>
            <div class="barra_submenu sizing">
                <li class="box_submenu sizing"><a href="scadenziario.php"><i class="fa fa-file-text-o fa-lg" aria-hidden="true"></i> Scadenziario</a></li>
            </div>
            <div class="content sizing">
                <div class="barra_op sizing">
                    <div class="bottone sizing">SCADENZIARIO</div>           
                    <div id="messaggio" class="scadenzetot">Totale scadenze: <span id="totalescadenze" style="font-weight: bolder;"></span></div>

                </div>
                <div class="showcont sizing">
                    <!-- qui contenuto del mostra anagrafiche e aggiungi anagrafiche --> 
                </div>

            </div>
        <?php } ?>
    </body>
</html>