<?php
ob_start('ob_gzhandler');
include './library/connessione.php';
include './library/controllo.php';
include './library/config.php';
include './library/functions.php';
include './library/basic.class.php';
/* libreria del modulo */
include './library/ore.class.php';

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
    
    case "richiamaevento":
        $idev = $_POST['id'];
        $sql = $db->prepare("SELECT DATE_FORMAT(data, '%d/%m/%Y') as datait, idutente FROM ore WHERE id = ? LIMIT 1");
        $sql->execute(array($idev));
        $res = $sql->fetch();
        $idutente = $res['idutente'];
        $data = $res['datait'];
        die('{"idutente": "' . $idutente . '", "data": "' . $data . '"}');
        break;
    
    case "impostaorariolavoro":
        $idutente = $_POST['id'];
        $sql = $db->prepare("SELECT * FROM utenti WHERE id = ?");
        try {
            $sql->execute(array($idutente));
        } catch (PDOException $e) {
            die('Connessione fallita: ' . $e->getMessage());
        }
        $res = $sql->fetch();
        $oredilavoro = $res['oregiorno'];
        if (!$oredilavoro) {
            $oredilavoro = "8:00";
        }
        die('{"orario": "' . $oredilavoro . '"}');
        break;
    case "deleteore":
        $tabella = "ore";
        $id = $_POST['id'];
        $dati = new ore($id, $tabella);
        $ore = $dati->cancella();
        die('{"msg": "ok"}');

        break;

    case "richiamagiornoore":
        $tabella = "ore";
        $idutente = $_POST['idutente'];
        $data = $_POST['data'];
        $where = "idutente = '$idutente' AND data = '$data'";
        $dati = new ore("", $tabella);
        $ore = $dati->richiamaWhere($where);
        if ($ore) {
            /* voci */
            $tabella = "ore_voci";
            $where = "idgiornoore = " . $ore[0]['id'] . " ORDER BY ordine";
            $dativoci = new ore($id, $tabella);
            $voci = $dativoci->richiamaWhere($where);
        }

        $commesse = getDati("commesse", "WHERE stato='1'");
        foreach ($commesse as $commessed) {
            $idcommessa = $commessed['id'];
            $titcommessa = $commessed['titolo'];
            $arrdatacommessa = explode("-", $commessed['data']);
            $datacomm = $arrdatacommessa[2] . "/" . $arrdatacommessa[1] . "/" . $arrdatacommessa[0];
            $arrdaticliente = explode("\n", $commessed['daticliente']);
            $daticliente = $arrdaticliente[0];
            /* inizio delle voci delle select */
            $commesseselect .= "<option disabled value=\"\" style=\"color: #ff0000; font-weight: bolder; padding-top: 5px;\">$datacomm - $daticliente - $titcommessa</option>";
            /* richiamo voci lavorazioni commesse */
            $vociprofitcomm = getDati("commesse_voci", "WHERE idcomm='$idcommessa' AND statovoce = '0' ORDER BY ordine");
            foreach ($vociprofitcomm as $vociprofitcommd) {
                $idrigacommessa = $vociprofitcommd['id'];
                $idprofit = $vociprofitcommd['idprofit'];
                /* nome del profit center */
                if ($idprofit > 0) {
                    $profit = getDati("profit_center", "WHERE id='$idprofit'");
                    $nomedelprofit = $profit[0]['nome'];
                    if (strlen($nomedelprofit) > 0) {
                        $nomeprofitprint = "$nomedelprofit - ";
                    }
                } else {
                    $nomeprofitprint = "";
                }
                /**/
                $titvocecomm = $vociprofitcommd['nome'];
                $descvocecomm = $vociprofitcommd['descr'];
                /* voci select */
                $commesseselect .= "<option value=\"$idrigacommessa\" style=\"padding-left: 20px;\">$daticliente - $titcommessa - $nomeprofitprint $titvocecomm</option>";
                $arr_commessevoci[] = array("idvocecomm" => $idrigacommessa, "dativocecomm" => $datacomm." - ".$daticliente . " - " . $titcommessa . "-" . $nomeprofitprint . " " . $titvocecomm);
            }
        }

        die('{"valori" : ' . json_encode($ore) . ', "voci" : ' . json_encode($voci) . ', "commessevoci" : ' . json_encode($arr_commessevoci) . '}');
        break;

    case "cambiadataore":

        $id = $_POST['id'];
        $data = $_POST['data'];

        $sql = $db->prepare("UPDATE ore SET data=? WHERE id = ?");
        $sql1 = $db->prepare("UPDATE ore_voci SET dataoredettaglio = ? WHERE idgiornoore = ?");

        $sql->execute(array($data, $id));
        $sql1->execute(array($data, $id));

        die('{"msg" : "ok"}');

        break;

    case "submitformore":
        $tabella = "ore";

        $ore = new ore($id, $tabella);
        /* voci delle ore */
        for ($i = 0; $i < count($_POST['descr']); $i++) {
            if ($_POST['idvocecomm'][$i] != "") {
                /* seleziono idprofit da riga commessa */
                $sql = $db->prepare("SELECT idprofit FROM commesse_voci WHERE id = ? LIMIT 1");
                $sql->execute(array($_POST['idvocecomm'][$i]));
                if ($sql->rowCount() > 0) {
                    $idprofit = $sql->fetchColumn();
                } else {
                    $idprofit = "0";
                }
            } else {
                $_POST['idvocecomm'][$i] == "0";
            }

            /**/

            if ($_POST['descr'][$i] != "") {
                $voci[$i] = array("idutente" => $_POST['idutente'], "dataoredettaglio" => $_POST['data'], "idvocecomm" => $_POST['idvocecomm'][$i], "descr" => $_POST['descr'][$i], "orelavorate" => $_POST['orelavorate'][$i], "idprofit" => $idprofit, "ordine" => $i);
            } else {
                
            }
        }
        array_filter($voci, 'strlen'); // controllo array vuoto

        /* tolgo dall'array submit e post che non mi servono */
        unset($_POST['submit'], $_POST['idvocecomm'], $_POST['descr'], $_POST['orelavorate']);

        foreach ($_POST as $k => $v) {
            $campi[] = $k;
            $valori[] = $v;
        }

        /* funzione aggiungi */
        $ore->aggiungi($campi, $valori, $voci);

        die('{"msg" : "ok"}');

        break;

    case "editformore":
        $tabella = "ore";
        $id = $_POST['id'];
        $ore = new ore($id, $tabella);
        /* voci delle ore */
        for ($i = 0; $i < count($_POST['descr']); $i++) {
            if ($_POST['idvocecomm'][$i] != "") {
                /* seleziono idprofit da riga commessa */
                $sql = $db->prepare("SELECT idprofit, idcomm FROM commesse_voci WHERE id = ? LIMIT 1");
                $sql->execute(array($_POST['idvocecomm'][$i]));
                if ($sql->rowCount() > 0) {
                    $res = $sql->fetch();
                    $idprofit = $res['idprofit'];
                    $idcomm = $res['idcomm'];
//                    $idprofit = $sql->fetchColumn();
                } else {
                    $idprofit = "0";
                    $idcomm = "0";
                }
            } else {
                $_POST['idvocecomm'][$i] = "0";
                $idcomm = "0";
                $idprofit = "0";
            }

            /**/

            if ($_POST['descr'][$i] != "") {
                if (strpos($_POST['orelavorate'][$i], ":") > 0) {
                    /* nome e cognome utente */
                    $sql = $db->prepare("SELECT nome, cognome, centro_costo, costo FROM utenti WHERE id = ?");
                    $sql->execute(array($_POST['idutente']));
                    $res = $sql->fetch();
                    $nomecognomeutente = $res['nome']." ".$res['cognome'];
                    $centrocosto = $res['centro_costo'];
                    
                    $costoorario = $res['costo'];
                    $orelavoratearr = explode(":", $_POST['orelavorate'][$i]);
                    $oresomma = floor($orelavoratearr[0]);
                    $minutisomma = ($orelavoratearr[1]*100/60)/100;                    
                    $costo = $costoorario * ($oresomma+$minutisomma);
                    /**/
                    $voci[$i] = array("idutente" => $_POST['idutente'], "dettvocecomm" => $_POST['dettvocecomm'][$i], "dataoredettaglio" => $_POST['data'], "nomecognomeutente" => $nomecognomeutente, "centro_costo" => $centrocosto, "costo" => $costo, "idcomm" => $idcomm, "idvocecomm" => $_POST['idvocecomm'][$i], "descr" => $_POST['descr'][$i], "orelavorate" => $_POST['orelavorate'][$i], "idprofit" => $idprofit, "ordine" => $i);
                } else {
                    die('{"msg": "ko", "msgko" : "Formato ore non corretto"}');
                }
            } else {
                
            }
        }
        
        array_filter($voci, 'strlen'); // controllo array vuoto


        /* tolgo dall'array submit e post che non mi servono */
        unset($_POST['submit'], $_POST['idvocecomm'], $_POST['descr'], $_POST['orelavorate'],  $_POST['dettvocecomm']);

        foreach ($_POST as $k => $v) {
            $campi[] = $k;
            $valori[] = $v;
        }

        if ($id > 0) {
            $ore->aggiorna($campi, $valori, $voci);
        } else {
            $ore->aggiungi($campi, $valori, $voci);
        }


        die('{"msg": "ok"}');
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
        <script type="text/javascript" src="./js/functions-ore.js"></script>

        <link type="text/css" rel="stylesheet" href="./js/jsgrid-1.5.1/jsgrid.min.css" />
        <link type="text/css" rel="stylesheet" href="./js/jsgrid-1.5.1/jsgrid-theme.min.css" />

        <script type="text/javascript" src="./js/jsgrid-1.5.1/jsgrid.min.js"></script>
        <script type="text/javascript" src="./js/jsgrid-1.5.1/i18n/jsgrid-it.js"></script>
        <!-- lingua italiana datepicker -->
        <script src="./js/jquery-ui-1.11.4/datepicker-it.js"></script>
        <!-- timepicker-->
        <link rel='stylesheet' href='./js/jquery-timepicker-1.3.5/jquery.timepicker.min.css' />
        <script type="text/javascript" src="./js/jquery-timepicker-1.3.5/jquery.timepicker.min.js"></script>
        <!-- full calendar -->
        <link rel='stylesheet' href='./js/fullcalendar-3.0.1/lib/cupertino/jquery-ui.min.css' />
        <link rel='stylesheet' href='./js/fullcalendar-3.0.1/fullcalendar.css' />
        <script src='./js/fullcalendar-3.0.1/lib/moment.min.js'></script>
        <script src='./js/fullcalendar-3.0.1/fullcalendar.js'></script>
        <script src='./js/fullcalendar-3.0.1/locale/it.js'></script>
        <script type="text/javascript">
            $(document).ready(function () {
                calendario();
            });
        </script>
    </head>
    <body class="colormodulo">
        <?php include './components/top.php'; ?>
        <?php if (moduloattivo("ore") > 0) { ?>
            <div class="barra_submenu sizing">
                <li class="box_submenu sizing"><i class="fa fa-clock-o fa-lg" aria-hidden="true"></i> Calendario ore</li>

            </div>
            <div class="content sizing">
                <div class="barra_op sizing">
                    <div id="messaggio" class="messaggiook">Operazione avvenuta con successo</div>
                </div>
                <div class="showcont sizing">
                    <!-- qui contenuto del modulo --> 

                </div>
            </div>
        <?php } ?>
    </body>
</html>