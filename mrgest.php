<?php
ob_start('ob_gzhandler');
include './library/connessione.php';
include './library/controllo.php';
include './library/config.php';
require './library/PHPMailer-master/vendor/autoload.php';
include './library/functions.php';
include './library/basic.class.php';
include './library/calendario.class.php';
/* dati clienti */
//$clienti = getDati("clienti_fornitori", "WHERE tipo = '1' AND idatelier = " . $_SESSION['idatelier'] . "");
$submit = "";
if (isset($_POST['submit'])) {
    $submit = $_POST['submit'];
} else {
    $submit = $_GET['submit'];
}
$op = "";
if (isset($_GET['op'])) {
    $op = $_GET['op'];
}
switch ($submit) {

    case "eliminafile":
        $id = $_POST['id'];
        $idappuntamento = $_POST['idappuntamento'];

        $where = "id = $id";
        $files = new calendario($id, "file_appuntamenti");
        $datifile = $files->richiamaWhere($where);
        $files->delFile($idappuntamento, $datifile[0]['nomefile']);
        die('{"msg" : "ok"}');

        break;

    case "sendfile":
        $id = $_POST['id'];
        $file = $_POST['file'];

        $files = new calendario($id, "file_appuntamenti");
        $campi = array("idappuntamento", "nomefile", "titolo");
        $valori = array($id, $file, $file);
        $lastid = $files->aggiungiFile($campi, $valori);

        die('{"id": "' . $lastid . '"}');

        break;

    case "submitstampaappuntamenti":

        $data_dal = $_POST['data'];
        $data_al = $_POST['data2'];
        $idcliente = $_POST['idcliente'];
        $idatelier = $_POST['idatelier'];

        if (!$data_dal) {
            $data_dal = "";
        }

        if (!$data_al) {
            $data_al = "3000-01-01";
        }

        if ($idcliente > 0) {
            $andcliente = " AND idcliente = $idcliente";
        }

        $where = "WHERE data BETWEEN '" . $data_dal . "' AND '" . $data_al . "' $andcliente " . ($idatelier != '' ? "AND idatelier = $idatelier" : "");
        //die($where);
        $dati = getDatiCalStampa($where);

        foreach ($dati as $datid) {
            $arrappuntamenti['id'] = $idappuntamento = $datid['id'];
            $arrappuntamenti['datait'] = $data = $datid['data'];
            $arrappuntamenti['datamatrimonio'] = $datamatrimonio = $datid['datamatrimonio'];
            $arrappuntamenti['orario'] = $orario = $datid['orariodb'];
            $arrappuntamenti['nome'] = $datid['nome'];
            $arrappuntamenti['cognome'] = $datid['cognome'];
            $arrappuntamenti['email'] = $datid['email'];
            $arrappuntamenti['telefono'] = $datid['telefono'];
            $arrappuntamenti['cliente'] = $cliente = $datid['nome'] . " " . $datid['cognome'];
            $arrappuntamenti['idatelier'] = $idatelier = $datid['idatelier'];
            $arrappuntamenti['iddipendente'] = $iddipendente = $datid['idutente'];
            $atelier = getDati("utenti", "WHERE id = '$idatelier'");
            $arrappuntamenti['nominativoatelier'] = $atelier[0]['nominativo'];
            $arrappuntamenti['dipendente'] = getDati("utenti", "WHERE id = '$iddipendente'")[0]['nome'] . " " . getDati("utenti", "WHERE id = '$iddipendente'")[0]['cognome'];
            if ($datid['acquistato'] == "") {
                $arrappuntamenti['acquistato'] = "NON COMPILATO";
            } else if ($datid['acquistato'] > 0) {
                $arrappuntamenti['acquistato'] = "SI";
            } else if ($datid['acquistato'] == 0) {
                $arrappuntamenti['acquistato'] = "NO";
            }
            $solo_sartoria = $atelier['solo_sartoria'];
            if (($_SESSION['id'] == $iddipendente) || $_SESSION['livello'] == '5' || $_SESSION['livello'] == '3' || $_SESSION['livello'] <= 1) {
                $comandiextra = "<a href=\"javascript:;\" onclick=\"javascript:editstep2('$idappuntamento', '1', $solo_sartoria, {$datid['sartoria']})\"><i class=\"fa fa-edit fa-lg\"></i></a>";
                $arrappuntamenti['editextra'] = 1;
            } else {
                $comandiextra = "";
                $arrappuntamenti['editextra'] = 0;
            }
            if ($datid['tipoappuntamento'] == "1") {
                $tipoapp = "Appuntamento Sposa";
                $color = "#db00cc";
            } else if ($datid['tipoappuntamento'] == "2") {
                $tipoapp = "Appuntamento Sposo";
                $color = "#1a00a4";
            } else if ($datid['tipoappuntamento'] == "3") {
                $tipoapp = "Cerimonia Donna";
                $color = "#f5a9f0";
            } else if ($datid['tipoappuntamento'] == "4") {
                $tipoapp = "Cerimonia Uomo";
                $color = "#69a2de";
            } else if ($datid['tipoappuntamento'] == "5") {
                $tipoapp = "Sartoria";
                $color = "#696969";
            } else if ($datid['tipoappuntamento'] == "6") {
                $tipoapp = "Appuntamento Sposa e Sposo";
                $color = "#75507b";
            } else if ($datid['tipoappuntamento'] == "7") {
                $tipoapp = "Ferie";
                $color = "#cccc00";
            } else if ($datid['tipoappuntamento'] == "8") {
                $tipoapp = "Lista";
                $color = "#ffa343";
            } else if ($datid['tipoappuntamento'] == "9") {
                $tipoapp = "Trunk show";
                $color = "#c9b8a7";
            }
            $arrappuntamenti['tipoapp'] = $tipoapp;
            $appuntamenti .= "<tr>"
                    . "<td style=\"padding: 5px; border: 1px solid #cccccc;\">$data</td>"
                    . "<td style=\"padding: 5px; border: 1px solid #cccccc;\">$orario</td>"
                    . "<td style=\"padding: 5px; border: 1px solid #cccccc; width: 20%;\">" . getDati("utenti", "WHERE id = '$idatelier'")[0]['nominativo'] . "</td>"
                    . "<td style=\"padding: 5px; border: 1px solid #cccccc; width: 20%;\">" . getDati("utenti", "WHERE id = '$iddipendente'")[0]['nome'] . " " . getDati("utenti", "WHERE id = '$iddipendente'")[0]['cognome'] . "</td>"
                    . "<td style=\"padding: 5px; border: 1px solid #cccccc\">$cliente</td>"
                    . "<td style=\"padding: 5px; border: 1px solid #cccccc\">$datamatrimonio</td>"
                    . "<td style=\"padding: 5px; border: 1px solid #cccccc\"><a href=\"./pdf/appuntamento.php?idapp=$idappuntamento\" target=\"new\"><i style=\"margin-right: 5px; color: #ff0000;\"class=\"fa fa-file-pdf-o fa-lg\" aria-hidden=\"true\"></i></a>$comandiextra</td>"
                    . "</tr>";

            $arrapp[] = $arrappuntamenti;
        }

        $appuntamentiprint = "<table style=\"width: 100%;\">"
                . "<tr>"
                . "<td style=\"padding: 5px; border: 1px solid #cccccc; width: 10%; font-weight: bolder;\">Data App.</td>"
                . "<td style=\"padding: 5px; border: 1px solid #cccccc; width: 10%; font-weight: bolder;\">Orario</td>"
                . "<td style=\"padding: 5px; border: 1px solid #cccccc; width: 20%; font-weight: bolder;\">Atelier</td>"
                . "<td style=\"padding: 5px; border: 1px solid #cccccc; width: 20%; font-weight: bolder;\">Gestito da</td>"
                . "<td style=\"padding: 5px; border: 1px solid #cccccc; font-weight: bolder;\">Cliente</td>"
                . "<td style=\"padding: 5px; border: 1px solid #cccccc; font-weight: bolder;\">Data Matrimonio</td>"
                . "<td style=\"padding: 5px; border: 1px solid #cccccc; font-weight: bolder;\">&nbsp;</td>"
                . "</tr>"
                . "$appuntamenti"
                . "</table>";

//        die('{"dati" : ' . json_encode($appuntamentiprint) . ', "datiarr": ' . json_encode($arrappuntamenti) . '}');
        die('{"dati": ' . json_encode($arrapp) . '}');

        break;

    case "editformstep2calSartoria":
        $id = $_POST['id'];
        if (count($_POST['sartid_']) > 0) {
            $sql = $db->prepare("DELETE FROM sartoria_appuntamento WHERE idappuntamento = ?");
            $sql->execute(array($id));
        }
        foreach ($_POST['sartid_'] as $ks => $vs) {
            $nomesart = "";
            if (strlen($vs) > 0) {

                $nomesart = getDati("sartoria", "WHERE id = '$ks'")[0]['nome'];

                $sql = $db->prepare("INSERT INTO sartoria_appuntamento (idappuntamento, idsartoria, nomesartoria, prezzosartoria, costosartoria, notesartoria) VALUES (?,?,?,?,?,?)");
                $sql->execute(array($id, $ks, $nomesart, $vs, $_POST['sartcosto_'][$ks], $_POST['sartnote_'][$ks]));
            }
        }
        $_POST['nomepagamentocaparra'] = getDati("metodi_pagamento", "WHERE id = '{$_POST['idtipopagcaparra']}'")[0]['nome'];
        if (is_null($_POST['nomepagamentocaparra']))
            $_POST['nomepagamentocaparra'] = '';
        $_POST['nomepagamentosaldo'] = getDati("metodi_pagamento", "WHERE id = '{$_POST['idtipopagsaldo']}'")[0]['nome'];
        if (is_null($_POST['nomepagamentosaldo']))
            $_POST['nomepagamentosaldo'] = '';
        $_POST['nomepag1'] = getDati("metodi_pagamento", "WHERE id = '{$_POST['idpag1']}'")[0]['nome'];
        if (is_null($_POST['nomepag1']))
            $_POST['nomepag1'] = '';
        $_POST['nomepag2'] = getDati("metodi_pagamento", "WHERE id = '{$_POST['idpag2']}'")[0]['nome'];
        if (is_null($_POST['nomepag2']))
            $_POST['nomepag2'] = '';
        $_POST['nomepag3'] = getDati("metodi_pagamento", "WHERE id = '{$_POST['idpag3']}'")[0]['nome'];
        if (is_null($_POST['nomepag3']))
            $_POST['nomepag3'] = '';
        $sql = $db->prepare("DELETE FROM sartoria_pagamenti WHERE idappuntamento = ?");
        $sql->execute(array($id));
        $qry = "insert into sartoria_pagamenti (`idappuntamento`, `caparra`, `totalespesa`, `saldo`, `datasaldo`, `idtipopagcaparra`, "
                . "`nomepagamentocaparra`, `idtipopagsaldo`, `nomepagamentosaldo`, `pag1`, `idpag1`, `nomepag1`, `pag2`, `idpag2`, `nomepag2`, "
                . "`pag3`, `idpag3`, `nomepag3`, `datapag1`, `datapag2`, `datapag3`, `datacap`, `dataeffettuatosaldo`, `orariosaldo`) values "
                . "(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);";
        $valori = Array($id, $_POST['caparra'], $_POST['totalespesa'], $_POST['saldo'], $_POST['datasaldo'], $_POST['idtipopagcaparra'], $_POST['nomepagamentocaparra'],
            $_POST['idtipopagsaldo'], $_POST['nomepagamentosaldo'], $_POST['pag1'], $_POST['idpag1'], $_POST['nomepag1'], $_POST['pag2'], $_POST['idpag2'], $_POST['nomepag2'],
            $_POST['pag3'], $_POST['idpag3'], $_POST['nomepag3'], $_POST['datapag1'], $_POST['datapag2'], $_POST['datapag3'], $_POST['datacap'], $_POST['dataeffettuatosaldo'], $_POST['orariosaldo']);
        $rs = $db->prepare($qry);
        //var_dump($valori);
        $rs->execute($valori);
        $tabella = "calendario";
        $calendario = new calendario($id, $tabella);
        $campi = Array('datasart1', 'datasart2', 'datasart3', 'datasart4', 'orariosart1', 'orariosart2', 'orariosart3', 'orariosart4');
        $valori = Array($_POST['datasart1'], $_POST['datasart2'], $_POST['datasart3'], $_POST['datasart4'], $_POST['orariosart1'], $_POST['orariosart2'], $_POST['orariosart3'], $_POST['orariosart4']);
        /* funzione aggiungi */
        $calendario->aggiorna($campi, $valori);
        die('{"msg" : "ok"}');
        break;

    case "editformstep2cal":

//        if ($_POST['acquistato'] == "") {
//            $_POST['acquistato'] = "";            
//        }
//        
//        if ($_POST['idtipoabito'] == "") {
//            $_POST['idtipoabito'] = "";            
//        }
//        
//        if ($_POST['sartoria'] == "") {
//            $_POST['sartoria'] = "";            
//        }

        $id = $_POST['id'];
        $dati = getDatiCal("WHERE id = '$id' limit 1");
        $_POST['nomepagamentocaparra'] = getDati("metodi_pagamento", "WHERE id = '{$_POST['idtipopagcaparra']}'")[0]['nome'];
        $_POST['nomepagamentosaldo'] = getDati("metodi_pagamento", "WHERE id = '{$_POST['idtipopagsaldo']}'")[0]['nome'];
        $_POST['nomepag1'] = getDati("metodi_pagamento", "WHERE id = '{$_POST['idpag1']}'")[0]['nome'];
        $_POST['nomepag2'] = getDati("metodi_pagamento", "WHERE id = '{$_POST['idpag2']}'")[0]['nome'];
        $_POST['nomepag3'] = getDati("metodi_pagamento", "WHERE id = '{$_POST['idpag3']}'")[0]['nome'];
        $_POST['nometipoabito'] = getDati("profit_center", "WHERE id = '{$_POST['idtipoabito']}'")[0]['nome'];
        $_POST['nomemodabito'] = getDati("profit_center", "WHERE id = '{$_POST['idmodabito']}'")[0]['nome'];

        $_POST['nomenoacquisto'] = getDati("motivonoacq", "WHERE id = '{$_POST['idnoacquisto']}'")[0]['nome'];

        $_POST['bollino'] = getDati("bollini", "WHERE id = '{$_POST['idbollino']}'")[0]['nome'];
        /* accessori */
        if (count($_POST['accid_']) > 0) {
            $sql = $db->prepare("DELETE FROM accessori_appuntamento WHERE idappuntamento = ?");
            $sql->execute(array($id));
        }
        if($_POST['accid_']) {
            foreach ($_POST['accid_'] as $k => $v) {
                $nomeacc = "";
                if (strlen($v) > 0) {

                    $nomeacc = getDati("accessori", "WHERE id = '$k'")[0]['nome'];

                    $sql = $db->prepare("INSERT INTO accessori_appuntamento (idappuntamento, idaccessorio, nomeaccessorio, prezzoaccessorio, noteaccessorio) VALUES (?,?,?,?,?)");
                    $sql->execute(array($id, $k, $nomeacc, $v, $_POST['accnote_'][$k]));
                }
            }    
        }
        

        /**/
        /* sartoria */
        if($_POST['sartid_']) {
            if (count($_POST['sartid_']) > 0) {
            $sql = $db->prepare("DELETE FROM sartoria_appuntamento WHERE idappuntamento = ?");
            $sql->execute(array($id));
            }
            foreach ($_POST['sartid_'] as $ks => $vs) {
                $nomesart = "";
                if (strlen($vs) > 0) {

                    $nomesart = getDati("sartoria", "WHERE id = '$ks'")[0]['nome'];

                    $sql = $db->prepare("INSERT INTO sartoria_appuntamento (idappuntamento, idsartoria, nomesartoria, prezzosartoria, costosartoria, notesartoria) VALUES (?,?,?,?,?,?)");
                    $sql->execute(array($id, $ks, $nomesart, $vs, $_POST['sartcosto_'][$ks], $_POST['sartnote_'][$ks]));
                }
            }
        }
        
        /**/

        $_POST['cliente'] = $_POST['nome'] . " " . $_POST['cognome'];

        $tabella = "calendario";
        $calendario = new calendario($id, $tabella);
        /* tolgo dall'array submit */
        unset($_POST['submit'], $_POST['accid_'], $_POST['accnote_'], $_POST['sartid_'], $_POST['sartcosto_'], $_POST['sartnote_'], $_POST['idlavoro']);

        foreach ($_POST as $k => $v) {
            /* controllo email */
            $campi[] = $k;
            $valori[] = $v;
        }
        /* funzione aggiungi */
        $calendario->aggiorna($campi, $valori);

        /* cliente aggiorno i dati */
        $idcliente = $_POST['idcliente'];
        $tabellacli = "clienti_fornitori";
        $clienti = new calendario($idcliente, $tabellacli);
        $campicli[] = "nome";
        $campicli[] = "cognome";
        $campicli[] = "telefono";
        $campicli[] = "email";
        $campicli[] = "provincia";
        $campicli[] = "comune";
        $campicli[] = "sesso";
        $valoricli[] = $_POST['nome'];
        $valoricli[] = $_POST['cognome'];
        $valoricli[] = $_POST['telefono'];
        $valoricli[] = $_POST['email'];
        $valoricli[] = $_POST['provincia'];
        $valoricli[] = $_POST['comune'];
        $valoricli[] = $_POST['sesso'];
        //die("$campicli, $valoricli");
        $clienti->aggiorna($campicli, $valoricli);
        /**/
        $now = date('Y-m-d');
        if ($dati[0]['acquistato'] != $_POST['acquistato']) {
            $qry = "select idcalendario from calendario_spoki where idcalendario=? limit 1;";
            $rs = $db->prepare($qry);
            $valori = Array($id);
            $rs->execute($valori);
            if ($_POST['acquistato'] == '1') {
                $campo = 'data_acquistato';
            } else {
                $campo = 'data_non_acquistato';
            }
            if ($rs->RowCount() == 0) {
                $qry = "insert into calendario_spoki ($campo, idcalendario) values (?,?);";
            } else {
                $qry = "update calendario_spoki set $campo=? where idcalendario=?;";
            }
            $rs = $db->prepare($qry);
            $valori = Array($now, $id);
            $rs->execute($valori);
        }
        die('{"msg" : "ok"}');
        break;

    /* richiama valori per lo step 2 del form */
    case "step2cal":

        $id = $_POST['id'];

        /* richiama files */
        $files = new calendario($id, "file_appuntamenti");
        $datifile = $files->richiamaWhere("idappuntamento=$id");
        /**/

        $dati = getDatiCal("WHERE id = '$id'");

        $dataappuntamento = new DateTime($dati[0]['data'] . " " . $dati[0]['orario']);

        $dataadesso = new DateTime("now");

        if ($dataappuntamento > $dataadesso) {
            $checkdata = 0;
        } else {
            $checkdata = 1;
        }
        /* accessori */
        $accessori = getDati("accessori_appuntamento", "WHERE idappuntamento = $id");
        foreach ($accessori as $accessorid) {
            $accarr[] = array("accid_{$accessorid['idaccessorio']}" => $accessorid['prezzoaccessorio'], "accnote_{$accessorid['idaccessorio']}" => $accessorid['noteaccessorio']);
        }
        /**/
        /* sartoria */
        $sartoria = getDati("sartoria_appuntamento", "WHERE idappuntamento = $id");
        foreach ($sartoria as $sartoriad) {
            $sartarr[] = array("sartid_{$sartoriad['idsartoria']}" => $sartoriad['prezzosartoria'], "sartnote_{$sartoriad['idsartoria']}" => $sartoriad['notesartoria']);
        }

        /**/

        $dati[0]['datap'] = $dati[0]['datait'];
        $dati[0]['datamatrimoniop'] = $dati[0]['datamatrimonioit'];

        if ($dati[0]['datamatrimoniop'] == "00/00/0000") {
            $dati[0]['datamatrimoniop'] = "";
            $dati[0]['datamatrimonio'] = "";
        }

        if ($dati[0]['datas'] == "00/00/0000") {
            $dati[0]['datas'] = "";
            $dati[0]['datasaldo'] = "";
        }

        if ($dati[0]['datac'] == "00/00/0000") {
            $dati[0]['datac'] = "";
            $dati[0]['datacap'] = "";
        }

        if ($dati[0]['datap1'] == "00/00/0000") {
            $dati[0]['datap1'] = "";
            $dati[0]['datapag1'] = "";
        }

        if ($dati[0]['datap2'] == "00/00/0000") {
            $dati[0]['datap2'] = "";
            $dati[0]['datapag2'] = "";
        }

        if ($dati[0]['datap3'] == "00/00/0000") {
            $dati[0]['datap3'] = "";
            $dati[0]['datapag3'] = "";
        }

        if ($dati[0]['dataes'] == "00/00/0000") {
            $dati[0]['dataes'] = "";
            $dati[0]['dataeffettuatosaldo'] = "";
        }

        $dati[0]['orario'] = substr($dati[0]['orario'], 0, -3);
        $dati[0]['orariosart1'] = substr($dati[0]['orariosart1'], 0, -3);
        $dati[0]['orariosart2'] = substr($dati[0]['orariosart2'], 0, -3);
        $dati[0]['orariosart3'] = substr($dati[0]['orariosart3'], 0, -3);
        $dati[0]['orariosaldo'] = substr($dati[0]['orariosaldo'], 0, -3);

        die('{"datifiles" : ' . json_encode($datifile) . ', "valori" : ' . json_encode($dati) . ', "passato" : ' . json_encode($checkdata) . ', "accessori" : ' . json_encode($accarr) . ', "sartoria" : ' . json_encode($sartarr) . '}');

        break;

    case "step2calSartoria":

        $id = $_POST['id'];

        $dati = getDatiCal("WHERE id = '$id'");
        unset($dati[0]['caparra'], $dati[0]['idtipopagcaparra'], $dati[0]['datacap'], $dati[0]['pag1'], $dati[0]['idpag1'], $dati[0]['datapag1'], $dati[0]['pag2'],
                $dati[0]['idpag2'], $dati[0]['datapag2'], $dati[0]['datap1'], $dati[0]['datap2'], $dati[0]['datac'], $dati[0]['pag3'],
                $dati[0]['idpag3'], $dati[0]['datapag3'], $dati[0]['datap3'], $dati[0]['saldo'], $dati[0]['idtipopagsaldo'], $dati[0]['dataes'], $dati[0]['dataeffettuatosaldo'],
                $dati[0]['totalespesa'], $dati[0]['datas'], $dati[0]['datasaldo'], $dati[0]['orariosaldo']);
        $dataappuntamento = new DateTime($dati[0]['data'] . " " . $dati[0]['orario']);

        $dataadesso = new DateTime("now");

        if ($dataappuntamento > $dataadesso) {
            $checkdata = 0;
        } else {
            $checkdata = 1;
        }

        /* sartoria */
        $sartoria = getDati("sartoria_appuntamento", "WHERE idappuntamento = $id");
        foreach ($sartoria as $sartoriad) {
            $sartarr[] = array("sartid_{$sartoriad['idsartoria']}" => $sartoriad['prezzosartoria'], "sartnote_{$sartoriad['idsartoria']}" => $sartoriad['notesartoria']);
        }

        /**/
        $pag_sartoria = getDati("sartoria_pagamenti", "WHERE idappuntamento = $id limit 1;");
        if (count($pag_sartoria) > 0) {
            $pag_sartoria[0]['datac'] = formatDate($pag_sartoria[0]['datacap']);
            $pag_sartoria[0]['datas'] = formatDate($pag_sartoria[0]['datasaldo']);
            $pag_sartoria[0]['datap1'] = formatDate($pag_sartoria[0]['datapag1']);
            $pag_sartoria[0]['datap2'] = formatDate($pag_sartoria[0]['datapag2']);
            $pag_sartoria[0]['datap3'] = formatDate($pag_sartoria[0]['datapag3']);
            $pag_sartoria[0]['dataes'] = formatDate($pag_sartoria[0]['dataeffettuatosaldo']);
            if ($pag_sartoria[0]['datas'] == "00/00/0000") {
                $pag_sartoria[0]['datas'] = "";
                $pag_sartoria[0]['datasaldo'] = "";
            }

            if ($pag_sartoria[0]['datac'] == "00/00/0000") {
                $pag_sartoria[0]['datac'] = "";
                $pag_sartoria[0]['datacap'] = "";
            }

            if ($pag_sartoria[0]['datap1'] == "00/00/0000") {
                $pag_sartoria[0]['datap1'] = "";
                $pag_sartoria[0]['datapag1'] = "";
            }

            if ($pag_sartoria[0]['datap2'] == "00/00/0000") {
                $pag_sartoria[0]['datap2'] = "";
                $pag_sartoria[0]['datapag2'] = "";
            }

            if ($pag_sartoria[0]['datap3'] == "00/00/0000") {
                $pag_sartoria[0]['datap3'] = "";
                $pag_sartoria[0]['datapag3'] = "";
            }

            if ($pag_sartoria[0]['dataes'] == "00/00/0000") {
                $pag_sartoria[0]['dataes'] = "";
                $pag_sartoria[0]['dataeffettuatosaldo'] = "";
            }
        }
        $dati[0]['datap'] = $dati[0]['datait'];

        if ($dati[0]['datamatrimoniop'] == "00/00/0000") {
            $dati[0]['datamatrimoniop'] = "";
            $dati[0]['datamatrimonio'] = "";
        }

        $dati[0]['orario'] = substr($dati[0]['orario'], 0, -3);
        $dati[0]['orariosart1'] = substr($dati[0]['orariosart1'], 0, -3);
        $dati[0]['orariosart2'] = substr($dati[0]['orariosart2'], 0, -3);
        $dati[0]['orariosart3'] = substr($dati[0]['orariosart3'], 0, -3);
        $dati[0]['orariosart4'] = substr($dati[0]['orariosart4'], 0, -3);
        $dati[0]['orariosaldo'] = substr($dati[0]['orariosaldo'], 0, -3);

        die('{"valori" : ' . json_encode($dati, JSON_INVALID_UTF8_IGNORE) . ', "passato" : ' . json_encode($checkdata) . ', "sartoria" : ' . json_encode($sartarr, JSON_INVALID_UTF8_IGNORE) . ', "pag_sartoria" : ' . json_encode($pag_sartoria, JSON_INVALID_UTF8_IGNORE) . '}');

        break;

    case "mandaemaillavoro":
        include './library/ics/ICS.php';
        $id = $_POST['idlavoro'];

        $dati = getDati("calendario", "WHERE id = '$id'");
        $data = $dati[0]['data'];
        $titolo = $dati[0]['titolo'];
        $cliente = $dati[0]['cliente'];
        $orario = $dati[0]['orario'];
        $orariofine = $dati[0]['orariofine'];
        $luogo = $dati[0]['luogo'];
        $partecipanti = $dati[0]['partecipanti'];
        $descrizione = $dati[0]['descrizione'];
        $url = $dati[0]['url'];

        $ics = new ICS(array(
            'location' => $luogo,
            'description' => $descrizione,
            'dtstart' => $data . " " . $orario,
            'dtend' => $data . " " . $orariofine,
            'summary' => $titolo,
            'url' => $url
        ));

        file_put_contents("tmp/invito.ics", $ics->to_string());

        $dati = getDati("calendario", "WHERE id = '$id'");

        $data = explode("-", $dati[0]['consegna']);
        $datait = $data[2] . "/" . $data[1] . "/" . $data[0];

        $body = "Clicom soluzioni web";

        $subject = "Invito per evento " . $dati[0]['titolo'] . "";
        /**/

        $emailarr = explode(",", trim($_POST['emailsend']));

        //include './library/phpmailer/PHPMailerAutoload.php';
        //require './library/PHPMailer-master/vendor/autoload.php';
        $allegato = "tmp/invito.ics";

        for ($i = 0; $i < count($emailarr); $i++) {
            $invio = sendMail($emailarr[$i], $subject, $body, "noreply@mrgest.com", "Clicom soluzioni web", $allegato);
        }
        if ($invio) {
            unlink($allegato);
            die('{"msg" : "1", "print" : "E-mail inviata con successo!"}');
        } else {
            die('{"msg" : "0", "print" : "Errore nell\'invio della e-mail!"}');
        }



        break;

    case "cercaClienti":
        $q = addslashes($_POST['q']);
        $idatelier = $_POST['idatelier'];
        $solo_sartoria = $_POST['solo_sartoria'];
        if ($solo_sartoria == '')
            $solo_sartoria = $_SESSION['solo_sartoria'];
        //die("WHERE tipo = '1' and (nome like '%$q%' or cognome like '%$q%')" . ($solo_sartoria == 0 && $idatelier > 0 ? " AND idatelier = " . $idatelier : ""));
        $clienti = getDati("clienti_fornitori", "WHERE tipo = '1' and (nome like '%$q%' or cognome like '%$q%')" . ($solo_sartoria == 0 && $idatelier > 0 ? " AND idatelier = " . $idatelier : ""));
        $json = json_encode($clienti, JSON_INVALID_UTF8_IGNORE);
        die($_GET['callback'] . '(' . $json . ');');
        break;

    case "richiamaclienti":

        $idatelier = $_POST['idatelier'];
        if (!$idatelier || $idatelier == 'undefined') {
            $idatelier = $_SESSION['idatelier'];
            $solo_sartoria = $_SESSION['solo_sartoria'];
        } else {
            $solo_sartoria = ($_POST['solo_sartoria'] != "" ? $_POST['solo_sartoria'] : 0);
        }
        //die("WHERE tipo = '1'" . ($solo_sartoria == 0 ? " AND idatelier = " . $idatelier : ""));
        $clienti = getDati("clienti_fornitori", "WHERE tipo = '1'" . ($solo_sartoria == 0 ? " AND idatelier = " . $idatelier : ""));
        //var_dump($clienti);
//        $clienti = getDati("clienti_fornitori", "");
        die('{"clienti" : ' . json_encode($clienti, JSON_INVALID_UTF8_IGNORE) . '}');
        break;

    case "mailevento":
        $id = $_POST['id'];
        $dati = getDati("calendario", "WHERE id = $id");

        $lavoro = "<div style=\"font-size: 1.2em; line-height: 22px;\">"
                . "<span style=\"font-size: 1.3em; font-weight: bolder;\">" . $dati[0]['cliente'] . "</span>"
                . "<br /><br />"
                . "Titolo: <strong>" . $dati[0]['titolo'] . "</strong>"
                . "<br />"
                . "Descrizione: <strong>" . $dati[0]['descrizione'] . "</strong>"
                . "<br />"
                . "Dalle ore alle ore: <strong>" . substr($dati[0]['orario'], 0, -3) . " / " . substr($dati[0]['orariofine'], 0, -3) . "</strong>"
                . "<br />"
                . "Luogo: <strong>" . $dati[0]['luogo'] . "</strong>"
                . "<br />"
                . "Partecipanti: <strong>" . $dati[0]['partecipanti'] . "</strong>"
                . "<br />"
                . "Url: <strong><a href=\"" . $dati[0]['url'] . "\" target=\"new\" style=\"color: #2E65A1\">" . $dati[0]['url'] . "</a></strong>"
                . "<br /><br />"
                . "<strong>Manda invito a:</strong> (inserisci indirizzi email separati da virgola)<br />"
                . "<form method=\"post\" id=\"formmandaemail\" name=\"formmandaemail\">"
                . "<input type=\"hidden\" id=\"idlavoro\" name=\"idlavoro\" value=\"$id\" />"
                . "A: <input type=\"text\" id=\"emailsend\" name=\"emailsend\" style=\"width: 70%;\" class=\"required\" value=\"\" /> "
                . "<input type=\"submit\" style=\"margin-left: 10px;\" id=\"submitformmandaemail\" class=\"nopost\" value=\"Invia\" />"
                . "</form>"
                . "<div class=\"chiudi\"></div>"
                . "<div id=\"messaggioemail\" class=\"messaggiook\" style=\"display: none; float: left; margin-top: 20px;\"></div>"
                . "</div>"
                . "<script type=\"text/javascript\">
            $(document).ready(function () {"
                . "$.validator.messages.required = '';
                $(\"#formmandaemail\").validate({
                    submitHandler: function () {
                        $(\"#\").ready(function () {
                            var datastring = $(\"#formmandaemail *\").not(\".nopost\").serialize();
                            $.ajax({
                                type: \"POST\",
                                url: \"./mrgest.php\",
                                data: datastring + \"&submit=mandaemaillavoro\",
                                dataType: \"json\",
                                success: function (msg) {
                                if (msg.msg == 1) {
                                
                                   $('#messaggioemail').html(msg.print).slideToggle('fast').delay(2000).slideToggle('slow');
                                   } else {
                                   alert(msg.print);
                                   return false;
                                   }
                                }
                            });
                        });
                    }
                });
                });
                </script>";

        die('{"msg" : ' . json_encode($lavoro) . '}');
        break;

    case "infoevento":
        //$arrAtelierSartoria = Array(65, 59, 136, 140, 100, 97, 81);
        $arrAtelierSartoria = Array();
        $id = $_POST['id'];
        $isSartoria = $_POST['sartoria'];
        $dati = getDatiCal("WHERE id = $id");
        $atelier = getDati("utenti", "WHERE id = '{$dati[0]['idatelier']}'");
        $daticommesso = getDati("utenti", "WHERE id = '" . $dati[0]['idutente'] . "' and livello='3' and attivo=1 and ruolo in (" . ADDETTO_VENDITE . "," . DISTRICT_MANAGER . "," . FRANCHISING . ", " . STORE_MANAGER . ")");
        $mostra_comandi = false;
        if (($_SESSION['id'] == $dati[0]['idutente']) || $_SESSION['livello'] == '5' || $_SESSION['livello'] == 1 || $_SESSION['livello'] == 0) {
            $mostra_comandi = true;
        } elseif ($_SESSION['livello'] == '3') {
            $mostra_comandi = true;
        }
        if ($mostra_comandi) {
            $comandiextra = "<p>&nbsp;</p><p><a href=\"javascript:;\" onclick=\"javascript:goup();aggiornalavoro('$id')\" style=\"color: #696969;margin-right:30px;\"><i class=\"fa fa-edit fa-2x\"></i> Modifica appuntamento</a>"
                    . "<a href=\"javascript:;\" onclick=\"javascript:editstep2('$id', 0, {$atelier[0]['solo_sartoria']}, $isSartoria)\" style=\"color: #696969;\"><i class=\"fa fa-edit fa-2x\"></i> Entra nella scheda</a></p>";
        } else {
            $comandiextra = "";
        }
        if (in_array($_SESSION['id'], $arrAtelierSartoria) && $isSartoria == 1) {
            $comandiextra = "";
        }
        $motivonoacquisto = getDati("motivonoacq", "WHERE id= '" . $dati[0]['idnoacquisto'] . "'")[0]['nome'];

        $acquistatoreport = "";

        if ($dati[0]['acquistato'] > 0) {
            $acquistatoreport = "<br /> <span style=\"color: green; font-size: 1.2em;\">Acquistato: <strong>SI</strong></span>";
        } else if ($dati[0]['acquistato'] === "0") {
            $acquistatoreport = "<br /> <span style=\"color: darkred; font-size: 1.2em;\">Acquistato: <strong>NO</strong></span>";
        } else {
            $acquistatoreport = "<br /> <span style=\"color: grey; font-size: 1.2em;\">Acquistato: <strong>ANCORA DA INDICARE</strong></span>";
        }

        $lavoro = "<div style=\"font-size: 1.2em; line-height: 22px; width: 100%;\">"
                . "<div style=\"position: absolute; right: 0; border: 1px solid #cccccc; padding: 5px; top: 20px; background:rgba:(255,255,255, 0.7); font-size: 0.8em;\">Motivo non acquisto:<br />$motivonoacquisto</div>"
                . "<span style=\"font-size: 1.3em; font-weight: bolder;\">" . $dati[0]['titolo'] . "</span>"
                . "<br />"
                . "Appuntamento gestito da: <strong>" . $daticommesso[0]['nome'] . " " . $daticommesso[0]['cognome'] . "</strong>"
                . "<br /><br />"
                . "Cliente: <strong>" . $dati[0]['nome'] . " " . $dati[0]['cognome'] . "</strong>"
                . "<br />"
                . "Orario: <strong>" . substr($dati[0]['orario'], 0, -3) . "</strong>"
                . "<br />"
                . "Data Matrimonio: <strong>" . ($dati[0]['datamatrimonioit'] != '00/00/0000' ? $dati[0]['datamatrimonioit'] : '') . "</strong>"
                . "<br />"
                . "Telefono: <strong>" . $dati[0]['telefono'] . "</strong>"
                . "<br />"
                . "Email: <strong>" . $dati[0]['email'] . "</strong>"
                . "<br />"
                . ($dati[0]['numero_contratto'] != "" ? "Numero contratto: <strong>" . $dati[0]['numero_contratto'] . "</strong>"
                . "<br />" : "")
                . ($dati[0]['comune'] != "" ?
                "Residente: <strong>" . $dati[0]['comune'] . " (" . $dati[0]['provincia'] . ")</strong>"
                . "<br />" : "")
                . ($dati[0]['provenienza'] != "" ? "Come ci ha trovato: <strong>" . $dati[0]['provenienza'] . "</strong>"
                . "<br />" : "")
                . "Note: <strong>" . $dati[0]['note'] . "</strong<br><br>"
                . "$acquistatoreport"
                . "<br />"
                . "$comandiextra"
                . "</div>";

        die('{"msg" : ' . json_encode($lavoro) . '}');
        break;

    case "aggiornalavoro":
        $id = $_POST['id'];

        $dati = getDatiCal("WHERE id = $id");

        unset($dati[0]['id']);

        $dati[0]['datap'] = $dati[0]['datait'];
        $dati[0]['datamatrimoniop'] = $dati[0]['datamatrimonioit'];

        $dati[0]['orario'] = substr($dati[0]['orario'], 0, -3);

        unset($dati[0]['id']);

        die('{"dati" : ' . json_encode($dati) . '}');

        break;

    case "submitmodificalavoro":

        $id = $_POST['idlavoro'];
        $dati = getDatiCal("WHERE id = $id");
        $atelier = getDati("utenti", "WHERE id = '" . $_POST['idatelier'] . "'")[0];
        /* controllo disdetto */

        $disdetto = $_POST['disdetto'];

        if ($disdetto > 0) {
            $_POST['idnoacquisto'] = 9;
            $_POST['nomenoacquisto'] = getDati("motivonoacq", "WHERE id = '{$_POST['idnoacquisto']}'")[0]['nome'];
        } else {
            $_POST['idnoacquisto'] = "";
            $_POST['nomenoacquisto'] = "";
        }

        /* fine disdetto */

        if ($_POST['idclientemod'] > 0) {
            unset($_POST['idcliente'], $_POST['cliente']);

            $_POST['idcliente'] = $_POST['idclientemod'];
            $_POST['cliente'] = $_POST['clientemod'];

            unset($_POST['idclientemod'], $_POST['clientemod']);
        }


        if ($_POST['idatelier'] > 0) {
            if (!$_POST['idutente'] || $_POST['idutente'] == 0) {
                //$_POST['idutente'] = $_POST['idatelier'];
                $_POST['emailatelier'] = getDati("utenti", "WHERE id = '" . $_POST['idatelier'] . "'")[0]['email'];
            }
        } else {

            $_POST['idutente'] = $_SESSION['id'];
            $_POST['idatelier'] = $_SESSION['idatelier'];
            $_POST['emailatelier'] = $_SESSION['email'];
        }

        $tabella = "calendario";
        $calendario = new calendario($id, $tabella);
        /* tolgo dall'array submit */
        unset($_POST['submit'], $_POST['idlavoro']);

        foreach ($_POST as $k => $v) {
            if ($k != 'solo_sartoria') {
                $campi[] = $k;
                $valori[] = $v;
            }
        }
        /* funzione aggiungi */
        $calendario->aggiorna($campi, $valori);
        $now = date('Y-m-d');
        if ($dati[0]['acquistato'] != '1' && $_POST['data'] >= $now) {
            if ($dati[0]['idatelier'] != $_POST['idatelier'] ||
                    $dati[0]['data'] != $_POST['data'] ||
                    $dati[0]['orario'] != $_POST['orario'] . ':00' ||
                    $dati[0]['nome'] != $_POST['nome'] ||
                    $dati[0]['cognome'] != $_POST['cognome'] ||
                    $dati[0]['telefono'] != $_POST['telefono'] ||
                    $dati[0]['email'] != $_POST['email'] ||
                    $dati[0]['disdetto'] != $_POST['disdetto']) {//modifica dati per spoki
                //var_dump($_POST, $dati[0]);
                //die();
                if ($_POST['idatelier'] != 137 && $_POST['idatelier'] != 75 && $_POST['idatelier'] != 71) {
                    #SPOKI
                    $telefono = (strpos($_POST['telefono'], '+39') === false ? '+39' : '') . $_POST['telefono'];
                    $campi_agg = Array();
                    if ($dati[0]['idatelier'] != $_POST['idatelier']) {
                        $campi_agg[] = 'idatelier';
                    }
                    if ($dati[0]['data'] != $_POST['data']) {
                        $campi_agg[] = 'data';
                    }
                    if ($dati[0]['orario'] != $_POST['orario']) {
                        $campi_agg[] = 'orario';
                    }
//            if ($dati[0]['nome'] != $_POST['nome']) {
//                $campi_agg[] = 'nome';
//            }
//            if ($dati[0]['cognome'] != $_POST['cognome']) {
//                $campi_agg[] = 'cognome';
//            }
                    $str_campi_agg = '';
                    foreach ($campi_agg as $campo) {
                        $str_campi_agg .= '&campi_agg[]=' . urlencode($campo);
                    }
                    $request = 'phone=' . urlencode($telefono) . '&first_name=' . urlencode($_POST['nome']) .
                            '&last_name=' . urlencode($_POST['cognome']) .
                            '&email=' . urlencode($_POST['email']) .
                            '&language=it' .
                            '&citta=' . urlencode($atelier['nominativo']) .
                            '&compleanno=' .
                            '&data=' . urlencode($_POST['data']) .
                            '&ora=' . urlencode($_POST['orario']) .
                            '&idutente=' . $_POST['idcliente'] .
                            '&disdetto=' . $_POST['disdetto'] .
                            '&idapp=' . $id .
                            $str_campi_agg;
                    $endpoint = URL_WS . 'updateContattoSPoki';
                    $response = callWS($endpoint, $request);
                }
            }
        }
        if ($dati[0]['disdetto'] != $_POST['disdetto'] && $_POST['disdetto'] == 1) {
            $qry = "select idcalendario from calendario_spoki where idcalendario=? limit 1;";
            $rs = $db->prepare($qry);
            $valori = Array($id);
            $rs->execute($valori);
            if ($rs->RowCount() == 0) {
                $qry = "insert into calendario_spoki (data_disdetto, idcalendario) values (?,?);";
            } else {
                $qry = "update calendario_spoki set data_disdetto=? where idcalendario=?;";
            }
            $rs = $db->prepare($qry);
            $valori = Array($now, $id);
            $rs->execute($valori);
        }
        if ($dati[0]['acquistato'] != $_POST['acquistato']) {
            $qry = "select idcalendario from calendario_spoki where idcalendario=? limit 1;";
            $rs = $db->prepare($qry);
            $valori = Array($id);
            $rs->execute($valori);
            if ($_POST['acquistato'] == '1') {
                $campo = 'data_acquistato';
            } else {
                $campo = 'data_non_acquistato';
            }
            if ($rs->RowCount() == 0) {
                $qry = "insert into calendario_spoki ($campo, idcalendario) values (?,?);";
            } else {
                $qry = "update calendario_spoki set $campo=? where idcalendario=?;";
            }
            $rs = $db->prepare($qry);
            $valori = Array($now, $id);
            $rs->execute($valori);
        }
        die('{"msg" : "ok"}');
        break;

    case "submitinsmodlavoro":

        if ($_POST['idatelier'] > 0) {
            if (!$_POST['idutente'] || $_POST['idutente'] == 0) {
                $_POST['idutente'] = $_POST['idatelier'];
            }
            $_POST['emailatelier'] = getDati("utenti", "WHERE id = '" . $_POST['idatelier'] . "'")[0]['email'];
        } else {
            $_POST['idutente'] = $_SESSION['id'];
            $_POST['idatelier'] = $_SESSION['idatelier'];
            $_POST['emailatelier'] = $_SESSION['email'];
        }

        /* aggiungi modifica cliente */

        $idcliente = $_POST['idcliente'];
        $tabellacli = "clienti_fornitori";
        $clienti = new calendario($idcliente, $tabellacli);
        $campicli[] = "nome";
        $campicli[] = "cognome";
        $campicli[] = "telefono";
        $campicli[] = "email";
        $campicli[] = "provincia";
        $campicli[] = "comune";
//        $campicli[] = "sesso";
        $campicli[] = "idatelier";
        $campicli[] = "tipo";
        $valoricli[] = $_POST['nome'];
        $valoricli[] = $_POST['cognome'];
        $valoricli[] = $_POST['telefono'];
        $valoricli[] = $_POST['email'];
        $valoricli[] = $_POST['provincia'];
        $valoricli[] = $_POST['comune'];
//        $valoricli[] = $_POST['sesso'];
        $valoricli[] = $_POST['idatelier'];
        $valoricli[] = '1';
        $_POST['cliente'] = $_POST['nome'] . " " . $_POST['cognome'];
        $_POST['data_insert'] = date('Y-m-d');
        $_POST['idutente_insert'] = $_SESSION["id"];
        if ($_POST['idcliente'] > 0) {
            $clienti->aggiorna($campicli, $valoricli);
        } else {

            $clienti->aggiungi($campicli, $valoricli);
            $lastid = $db->lastInsertId();
            $_POST['idcliente'] = $lastid;
        }

        if ($_POST['idlavoro'] > 0) {
            die('{"msg": "modifica", "idlavoro" : "' . $_POST['idlavoro'] . '", "idcliente" : "' . $_POST['idcliente'] . '" , "cliente" : "' . $_POST['cliente'] . '"}');
        }

        $tabella = "calendario";
        $calendario = new calendario($id, $tabella);
        /* tolgo dall'array submit */
        unset($_POST['submit'], $_POST['idlavoro'], $_POST['solo_sartoria']);

        foreach ($_POST as $k => $v) {
            /* controllo email */
            $campi[] = $k;
            $valori[] = $v;
        }
        /* funzione aggiungi */
        $calendario->aggiungi($campi, $valori);
        $idapp = $db->lastInsertId();
        /* invio email */
        //include './library/phpmailer/PHPMailerAutoload.php';
        
        $emailatelier = $_POST['emailatelier'];
        $emailcliente = $_POST['email'];

        $servizioclienti = "servizioclienti@comeinunafavola.it";
        $atelier = getDati("utenti", "WHERE id = " . $_POST['idatelier'] . "")[0];
        $nomeatelier = $atelier['nominativo'];
        $datiatelier = getDati("utenti", "WHERE id = " . $_POST['idatelier'] . "");
        $provincia = getDati("province", "WHERE sigla = '" . $datiatelier[0]['provincia'] . "'");
        if ($datiatelier[0]['comune'] != '' && $datiatelier[0]['comune'] != $provincia[0]['provincia']) {
            $comune = " " . $datiatelier[0]['comune'] . "(" . $datiatelier[0]['provincia'] . ")";
        } else {
            $comune = '';
        }
        $indirizzoatelier = $datiatelier[0]['indirizzo'] . $comune;

        $daticliente = $_POST['nome'];

        $dataapp = explode("-", $_POST['data']);
        $dataemail = $dataapp[2] . "/" . $dataapp[1] . "/" . $dataapp[0];

        $subject = "Conferma appuntamento Come in una Favola $nomeatelier";

        $body = "Ciao $daticliente, <br />ti confermiamo l'appuntamento del $dataemail alle ore " . $_POST['orario'] . ", da Come in una Favola $nomeatelier, in via $indirizzoatelier.<br /><br />"
                . "Ps. ti ricordiamo che i nostri appuntamenti sono One-Shot!<br />
                   Ciò significa che ti consentiamo di venire un'unica volta nei nostri negozi per effettuare la tua prima prova gratuita e senza obbligo d'acquisto. Questo perché essendo il nostro format unico come i nostri abiti, le richieste sono sempre elevate e diamo a tutti la possibilità di recarsi da noi.<br /><br />"
                . ".. ti aspettiamo per farti vivere la tua giornata da sogno!<br />"
                . "Lo Staff di Come in una Favola";
        //$emailcliente = "max@clicom.it";
        //$servizioclienti = 'ancio17@gmail.com';
        if ($_POST['idatelier'] != 137 && $_POST['idatelier'] != 75 && $_POST['idatelier'] != 71) {
            if ($_SESSION['livello'] == 2) {

                $invio = sendMail($emailcliente, $subject, $body, $servizioclienti, "Come in una favola", "");
                $invio2 = sendMail($emailatelier, $subject, $body, $servizioclienti, "Come in una favola", "");
                $invio3 = sendMail($servizioclienti, $subject, $body, "noreply@comeinunafavola.it", "Come in una favola", "");
            } else {
                $invio = sendMail($emailcliente, $subject, $body, $servizioclienti, "Come in una favola", "");
            }
            #SPOKI
            if ($emailcliente != 'max@clicom.it') {
                $telefono = (strpos($_POST['telefono'], '+39') === false ? '+39' : '') . $_POST['telefono'];
                $request = 'phone=' . urlencode($telefono) . '&first_name=' . urlencode($_POST['nome']) .
                        '&last_name=' . urlencode($_POST['cognome']) .
                        '&email=' . urlencode($_POST['email']) .
                        '&language=it' .
                        '&citta=' . urlencode($nomeatelier) .
                        '&compleanno=' .
                        '&data=' . urlencode($_POST['data']) .
                        '&ora=' . urlencode($_POST['orario']) .
                        '&idutente=' . $_POST['idcliente'] .
                        '&idapp=' . $idapp;
                $endpoint = URL_WS . 'insertContattoSPoki';
                $response = callWS($endpoint, $request);
            }
        }

        die('{"msg" : "ok"}');

        break;
    case "deleteevento":
        $id = $_POST['id'];
        $tabella = "calendario";
        $dati = new calendario($id, $tabella);
        $preventivi = $dati->cancella();
        die('{"msg" : "ok"}');
        break;

    case "cambiadataevento":
        $id = $_POST['id'];
        $data = $_POST['data'];
        $sql = $db->prepare("UPDATE calendario SET data = ? WHERE id = ?");
        $sql->execute(array($data, $id));
        die('{"msg" : "ok"}');
        break;

    case "controllo":
        if (isset($_SESSION['username']) && isset($_SESSION['password'])) {
            $msg = 1;
        } else {
            $msg = 0;
        }
        die('{"msg" : "' . $msg . '"}');
        break;
    case "cambialogin":
        $id = $_POST['id'];
        $user = $_POST['user'];
        $pass = $_POST['pass'];

        if (!$pass) {
            $sql = $db->prepare("UPDATE utenti SET username = ? WHERE id = ?");
            try {
                $sql->execute(array($user, $id));
            } catch (PDOException $e) {
                die('Connessione fallita: ' . $e->getMessage());
            }
        } else {
            $sql = $db->prepare("UPDATE utenti SET username = ?, password = ? WHERE id = ?");
            try {
                $sql->execute(array($user, $pass, $id));
            } catch (PDOException $e) {
                die('Connessione fallita: ' . $e->getMessage());
            }
        }
        die('{"msg" : "Aggiornamento eseguito" }');
        break;

    case "selcom":
        $prov = $_POST['prov'];
        $comune = $_POST['comune'];
        $com = getDati("cap_localita", "WHERE provincia = '$prov' GROUP BY comune ORDER BY comune");
        $comuni = "<option value=\"\">Seleziona Comune</option>";

        foreach ($com as $comd) {
            $select = "";
            if ($comune && ($comd['comune'] == $comune)) {
                $select = "selected";
            }
            $comuni .= "<option value=\"{$comd['comune']}\" $select>{$comd['comune']}</option>";
        }
        die('{"msg" : ' . json_encode($comuni) . '}');
        break;

    case "seldip":
        $idatelierdip = $_POST['idatelier'];
        $dipendenti = getDati("utenti", "WHERE (idatelier = $idatelierdip or id in (select idutente from atelier_utente where idatelier=$idatelierdip)) AND attivo = 1 and livello='3' ORDER BY nome;");
        $dati = "<option value=\"\">Seleziona dipendente</option>";
        foreach ($dipendenti as $dipendentid) {
            $dati .= "<option value=\"{$dipendentid['id']}\">" . $dipendentid['nome'] . " " . $dipendentid['cognome'] . "</option>";
        }

        $datiatelier = getDati("utenti", "WHERE id = $idatelierdip");

        $giorniaperture = explode(",", $datiatelier[0]['aperture']);
        if ($datiatelier[0]['aperture']) {
            foreach ($giorniaperture as $ga) {
                $giorno = $arrgiorni[$ga];

                $apertmatt = $datiatelier[0]['inizio' . $ga];
                $chiusmatt = $datiatelier[0]['fine' . $ga];
                $apertpome = $datiatelier[0]['iniziop' . $ga];
                $chiuspome = $datiatelier[0]['finep' . $ga];

                $apertureprint .= "<div class=\"boxaperture sizing\">"
                        . "<strong>$giorno</strong><br />"
                        . "$apertmatt - $chiusmatt<br />"
                        . "$apertpome - $chiuspome"
                        . "</div>";
            }

            $aperturanegozio = "<div class=\"aperture sizing\">"
                    . "$apertureprint"
                    . "<div class=\"chiudi\"></div>"
                    . "</div>";
        }
        $mostra_addetti = false;
        if ($idatelierdip != "" && $_POST['tipo'] != '') {
            $_POST['idstore'] = $idatelierdip;
            $start = $_POST['start'];
            $end = $_POST['end'];
            $date_start = new DateTime($start);
            $current = $start;
            $html = '<div class="aperture sizing">';
            while ($current < $end) {
                $_POST['day'] = $date_start->format('w');
                $_POST['data_app'] = formatDate($current);
                $orari = getOrari();
                if (!$orari) {
                    $orari = [];
                }
                if (count($orari) > 0) {
                    $mostra_addetti = true;
                }
                $html .= '<div class="boxaperture sizing">';
                switch ($_POST['day']) {
                    case 1:
                        $html .= '<b>Lun ' . $date_start->format('d') . '</b>';
                        break;

                    case 2:
                        $html .= '<b>Mar ' . $date_start->format('d') . '</b>';
                        break;

                    case 3:
                        $html .= '<b>Mer ' . $date_start->format('d') . '</b>';
                        break;

                    case 4:
                        $html .= '<b>Gio ' . $date_start->format('d') . '</b>';
                        break;

                    case 5:
                        $html .= '<b>Ven ' . $date_start->format('d') . '</b>';
                        break;

                    case 6:
                        $html .= '<b>Sab ' . $date_start->format('d') . '</b>';
                        break;

                    case 0:
                        $html .= '<b>Dom ' . $date_start->format('d') . '</b>';
                        break;
                }
                foreach ($orari as $orario) {
                    $html .= '<br>' . $orario['orario'] . ' add. ' . ($orario['addetti'] - $orario['num_app']);
                }
                $html .= '</div>';
                if ($_POST['day'] == 0) {
                    $html .= '<div class="chiudi"></div>';
                }
                $date_start->modify('+1 day');
                $current = $date_start->format('Y-m-d');
            }
            $html .= '</div>';
        } else {
            $html = '';
        }
        $mostra_addetti = '';
        //var_dump($orari);
        die('{"msg" : ' . json_encode($dati) . ', "aperture" : ' . json_encode($aperturanegozio, JSON_INVALID_UTF8_IGNORE) . ', "addetti" : ' . ($mostra_addetti ? json_encode($html, JSON_INVALID_UTF8_IGNORE) : 'null') . '}');

        break;

    case 'esporta_all':
        $qry = "select nome, cognome, DATE_FORMAT(datamatrimonio, '%d/%m/%Y') as data_matrimonio, (select nominativo from utenti u where u.id=c.idatelier limit 1) as atelier, "
                . "(select CONCAT(nome, ' ', cognome) from utenti u where u.id=c.idutente limit 1) as dipendente,"
                . "telefono, email, acquistato, data, idutente, orario, tipoappuntamento from calendario c"
                . " order by idatelier,data;";
        //die($qry);
        $rs = $db->prepare($qry);
        $rs->execute();
        $csv = '"data appuntamento";"ora appuntamento";"nome";"cognome";"email";"telefono";"tipo appuntamento";"atelier";"data matrimonio";"gestito da";"il cliente ha acquistato"' . "\n";
        while ($col = $rs->fetch(PDO::FETCH_ASSOC)) {
            $dipendente = $col['dipendente'];
            if ($col['acquistato'] == "") {
                $acquistato = "NON COMPILATO";
            } else if ($col['acquistato'] > 0) {
                $acquistato = "SI";
            } else if ($col['acquistato'] == 0) {
                $acquistato = "NO";
            }
            if ($col['tipoappuntamento'] == "1") {
                $tipoapp = "Appuntamento Sposa";
                $color = "#db00cc";
            } else if ($col['tipoappuntamento'] == "2") {
                $tipoapp = "Appuntamento Sposo";
                $color = "#1a00a4";
            } else if ($col['tipoappuntamento'] == "3") {
                $tipoapp = "Cerimonia Donna";
                $color = "#f5a9f0";
            } else if ($col['tipoappuntamento'] == "4") {
                $tipoapp = "Cerimonia Uomo";
                $color = "#69a2de";
            } else if ($col['tipoappuntamento'] == "5") {
                $tipoapp = "Sartoria";
                $color = "#696969";
            } else if ($col['tipoappuntamento'] == "6") {
                $tipoapp = "Appuntamento Sposa e Sposo";
                $color = "#75507b";
            } else if ($col['tipoappuntamento'] == "7") {
                $tipoapp = "Ferie";
                $color = "#cccc00";
            } else if ($col['tipoappuntamento'] == "8") {
                $tipoapp = "Lista";
                $color = "#ffa343";
            } else if ($col['tipoappuntamento'] == "9") {
                $tipoapp = "Trunk show";
                $color = "#c9b8a7";
            }
            $csv .= '"' . formatDate($col['data']) . '";"' . $col['orario'] . '";"' . str_replace('"', '', $col['nome']) . '";"' . str_replace('"', '', $col['cognome']) . '";"' . str_replace('"', '', $col['email']) . '";"' . str_replace('"', '', $col['telefono']) . '";"' . str_replace('"', '', $tipoapp) . '";'
                    . '"' . str_replace('"', '', $col['atelier']) . '";"' . str_replace('"', '', $col['data_matrimonio']) . '";"' . $dipendente . '";"' . $acquistato . '"' . "\n";
        }
        header("Content-type: text/csv");
        header("Content-Disposition: attachment; filename=export.csv");
        header("Pragma: no-cache");
        header("Expires: 0");
        die($csv);
        break;

    case 'esporta_sel':
        $data_dal = $_GET['data'];
        $data_al = $_GET['data2'];
        $idcliente = $_GET['idcliente'];
        $idatelier = $_GET['idatelier'];
        if (!$data_dal) {
            $data_dal = "";
        }

        if (!$data_al) {
            $data_al = "3000-01-01";
        }

        if ($idcliente > 0) {
            $andcliente = " AND idcliente = $idcliente";
        }

        $where = "WHERE data BETWEEN '" . $data_dal . "' AND '" . $data_al . "' $andcliente " . ($idatelier != '' ? "AND idatelier = $idatelier" : "");
        $qry = "select nome, cognome, DATE_FORMAT(datamatrimonio, '%d/%m/%Y') as data_matrimonio, (select nominativo from utenti u where u.id=c.idatelier limit 1) as atelier, "
                . "telefono, email, acquistato, data, idutente, orario from calendario c"
                . " where data BETWEEN '" . $data_dal . "' AND '" . $data_al . "' $andcliente " . ($idatelier != '' ? "AND idatelier = $idatelier" : "") . " order by data, orario;";
        //die($qry);
        $rs = $db->prepare($qry);
        $rs->execute();
        $csv = '"data appuntamento";"ora appuntamento";"nome";"cognome";"email";"telefono";"atelier";"data matrimonio";"gestito da";"il cliente ha acquistato"' . "\n";
        while ($col = $rs->fetch(PDO::FETCH_ASSOC)) {
            $dipendente = getDati("utenti", "WHERE id = {$col['idutente']}")[0]['nome'] . " " . getDati("utenti", "WHERE id = {$col['idutente']}")[0]['cognome'];
            if ($col['acquistato'] == "") {
                $acquistato = "NON COMPILATO";
            } else if ($col['acquistato'] > 0) {
                $acquistato = "SI";
            } else if ($col['acquistato'] == 0) {
                $acquistato = "NO";
            }
            $csv .= '"' . formatDate($col['data']) . '";"' . $col['orario'] . '";"' . str_replace('"', '', $col['nome']) . '";"' . str_replace('"', '', $col['cognome']) . '";"' . str_replace('"', '', $col['email']) . '";"' . str_replace('"', '', $col['telefono']) . '";'
                    . '"' . str_replace('"', '', $col['atelier']) . '";"' . str_replace('"', '', $col['data_matrimonio']) . '";"' . $dipendente . '";"' . $acquistato . '"' . "\n";
        }
        header("Content-type: text/csv");
        header("Content-Disposition: attachment; filename=export-selezione.csv");
        header("Pragma: no-cache");
        header("Expires: 0");
        die($csv);
        break;

    case 'submitstampaspoki':
        $dal = $_POST['data'];
        $al = $_POST['data2'];
        $html = '';
        $qry = "select COUNT(idcalendario) from calendario_spoki where data_inviato_modifica between ? and ? limit 1;";
        $rs = $db->prepare($qry);
        $valori = Array($dal, $al);
        $rs->execute($valori);
        $num_inviati_modifica = (int) $rs->fetchColumn();
        $qry = "select COUNT(idcalendario) from calendario_spoki where data_inviato_modifica between ? and ? and modificato_sale10=? limit 1;";
        $rs = $db->prepare($qry);
        $valori = Array($dal, $al, 1);
        $rs->execute($valori);
        $num_modificati = (int) $rs->fetchColumn();
        $qry = "select COUNT(idcalendario) from calendario_spoki where data_inviato_modifica between ? and ? and modificato_sale10=? and idcalendario in "
                . "(select id from calendario where (acquistato='1' or idnoacquisto!='') and data between ? and ?)";
        $rs = $db->prepare($qry);
        $valori = Array($dal, $al, 1, $dal, $al);
        $rs->execute($valori);
        $num_svolti = (int) $rs->fetchColumn();
        $qry = "select COUNT(idcalendario) from calendario_spoki where data_inviato_modifica between ? and ? and modificato_sale10=? and idcalendario in "
                . "(select id from calendario where acquistato='1' and data between ? and ?)";
        $rs = $db->prepare($qry);
        $valori = Array($dal, $al, 1, $dal, $al);
        $rs->execute($valori);
        $num_conv = (int) $rs->fetchColumn();
        if ($num_conv > 0) {
            $perc_conv = round($num_conv / $num_svolti * 100, 2);
        } else {
            $perc_conv = 0;
        }
        $html = '<p style="margin-bottom:10px;">Totale messaggi "Modifica il  tuo appuntamento" inviati: <b>' . $num_inviati_modifica . '</b></p>'
                . '<p style="margin-bottom:10px;">Totale appuntamenti modificati: <b>' . $num_modificati . '</b> di cui convertiti il <b>' . $perc_conv . '% (' . $num_conv . ' su ' . $num_svolti . ' svolti)</b></p>';
        die('{"html":' . json_encode($html) . '}');
        break;
}
?>
<!DOCTYPE html>
<html lang="it">
    <head>
        <?php include './components/header.php'; ?>
            <script>
            <?php if($_SESSION['livello'] <= 1 || $_SESSION['id'] == 107) { ?>
                var isAdmin_global = true;
            <?php } else { ?>
                var isAdmin_global = false;
            <?php } ?>
            </script>
        <script src="./js/functions-agenda.js?v=10" type="text/javascript"></script>
        <?php if ($op) { ?>
            <link type="text/css" rel="stylesheet" href="./js/jsgrid-1.5.1/jsgrid.min.css" />
            <link type="text/css" rel="stylesheet" href="./js/jsgrid-1.5.1/jsgrid-theme.min.css" />
<!--            <link type="text/css" href="./css/theme-app-jsgrid.css" rel="Stylesheet" />-->
            <script type="text/javascript" src="./js/jsgrid-1.5.1/jsgrid.min.js"></script>
            <script type="text/javascript" src="./js/jsgrid-1.5.1/i18n/jsgrid-it.js"></script>
            <?php
        }
        ?>
        <!-- full calendar -->
        <!--<link rel='stylesheet' href='./js/fullcalendar-3.0.1/lib/cupertino/jquery-ui.min.css' />-->
        <link rel='stylesheet' href='./js/jquery-ui-themes-1.12.1/themes/smoothness/jquery-ui.min.css' />
        <link rel='stylesheet' href='./js/fullcalendar-3.0.1/fullcalendar.css' />
        <!--        <link type="text/css" href="./css/theme-app-fullcalendar.css" rel="Stylesheet" />-->
        <script src='./js/fullcalendar-3.0.1/lib/moment.min.js'></script>
        <script src='./js/fullcalendar-3.0.1/fullcalendar.js'></script>
        <script src='./js/fullcalendar-3.0.1/locale/it.js'></script>
        <!-- timepicker-->
        <link rel='stylesheet' href='./js/jquery-timepicker-1.3.5/jquery.timepicker.min.css' />
        <script type="text/javascript" src="./js/jquery-timepicker-1.3.5/jquery.timepicker.min.js"></script>
        <!-- lingua italiana datepicker -->
        <script src="./js/jquery-ui-1.11.4/datepicker-it.js"></script>
        <!-- jquery confirm master -->
        <link rel="stylesheet" href="./js/jquery-confirm-master/css/jquery-confirm.css">
        <script src="./js/jquery-confirm-master/dist/jquery-confirm.min.js"></script>
        <script src="/js/select2-4.1.0/dist/js/select2.full.min.js"></script>
        <link type="text/css" href="/js/select2-4.1.0/dist/css/select2.min.css" rel="Stylesheet" />
        <script src="./js/functions-calendario.js?t=<?= time() ?>"></script>
        <?php if ($op == 'agenda_dip' || $op == 'agenda_dip_atelier') { ?>
            <link rel="stylesheet" href="./js/select2-4.1.0/dist/css/select2.min.css">
            <script src="./js/select2-4.1.0/dist/js/select2.full.min.js"></script>
        <?php } ?>
        <script type="text/javascript">
<?php
$pasqua = date("Y-m-d", easter_date(date('Y')));
$pasquetta = date("Y-m-d", (easter_date(date('Y')) + 3600 * 24));
?>
            var holidays = ["01-01", "01-06", "04-25", "05-01",
                "06-02", "08-15", "11-01", "12-08",
                "12-25", "12-26",
                "01-01", "01-06", "04-25", "05-01"];
            var pasqua = ["<?= $pasqua ?>", "<?= $pasquetta ?>"];
            var patrono = '';
            var aperture_spot = '';
            var arrApertureSpot = new Array();
            var chiusure_spot = '';
            var arrChiusureSpot = new Array();
            var chiuso_dal = '';
            var chiuso_al = '';
            $(document).ready(function () {

<?php
if ($op == "stampe") {
    ?>
                    mostraStampeappuntamenti();
<?php } elseif ($op == 'spoki') {
    ?>
                    mostraStatsSpoki();
<?php } elseif ($op == 'openai') {
    ?>
                    mostraOpenAI();
<?php } elseif ($op == 'agenda_dip') {
    ?>
                    agendaDip();
<?php } elseif ($op == 'agenda_dip_atelier') {
    ?>
                    agendaDipAtelier();
<?php } elseif ($op == 'file_dip') {
    ?>
                    fileDip();
<?php } elseif ($op == 'report_app') {
    ?>
                    reportApp();
<?php } elseif ($op == 'report_app_tot') {
    ?>
                    reportAppTot();
<?php } else {
    ?>

                    /* form inserimento lavoro */

                    $.validator.messages.required = '';
                    $("#insmodlavoro").validate({
                        rules: {
                            email: {
                                email: true
                            }
                        },
                        submitHandler: function () {
                            $("#submitinsmodlavoro").ready(function () {
                                var datastring = $("#insmodlavoro *").not(".nopost").serialize();
                                $.ajax({
                                    type: "POST",
                                    url: "./mrgest.php",
                                    data: datastring + "&submit=submitinsmodlavoro",
                                    dataType: "json",
                                    success: function (msg) {
                                        if (msg.msg == "modifica") {
                                            $.confirm({
                                                boxWidth: '50%',
                                                useBootstrap: false,
                                                title: 'ATTENZIONE!',
                                                content: 'Stai modificando un appuntamento esistente!',
                                                type: 'blue',
                                                buttons: {
                                                    confirm: {
                                                        text: 'SI, confermo la modifica',
                                                        btnClass: 'btn-blue',
                                                        keys: ['enter', 'shift'],
                                                        action: function () {
                                                            $.ajax({
                                                                type: "POST",
                                                                url: "./mrgest.php",
                                                                data: datastring + "&idclientemod=" + msg.idcliente + "&clientemod=" + msg.cliente + "&submit=submitmodificalavoro",
                                                                dataType: "json",
                                                                success: function (msg) {
                                                                    $('#insmodlavoro').trigger('reset');
                                                                    $('#idcliente').val("");
                                                                    $('#idlavoro').val("");
                                                                    $('#data').val("");
                                                                    $('#datamatrimonio').val("");
                                                                    $('#comune').html('<option value=\'\'>Seleziona Comune</option>');
                                                                    $('#messaggio').slideToggle('fast').delay(2000).slideToggle('slow');
                                                                    $('#insmodlavoro *').removeClass('inEdit');
                                                                    $('#tipoappuntamento').prop('disabled', false);
                                                                    calendario();
                                                                }
                                                            });
                                                        }
                                                    },
                                                    somethingElse: {
                                                        text: 'NO, voglio inserirlo come nuovo',
                                                        btnClass: 'btn-blue',
                                                        keys: ['enter', 'shift'],
                                                        action: function () {
                                                            $('#idlavoro').val('');
                                                            var datastring2 = $("#insmodlavoro *").not(".nopost").serialize();
                                                            $.ajax({
                                                                type: "POST",
                                                                url: "./mrgest.php",
                                                                data: datastring2 + "&submit=submitinsmodlavoro",
                                                                dataType: "json",
                                                                success: function (msg) {
                                                                    $('#insmodlavoro').trigger('reset');
                                                                    $('#idcliente').val("");
                                                                    $('#idlavoro').val("");
                                                                    $('#data').val("");
                                                                    $('#datamatrimonio').val("");
                                                                    $('#tipoappuntamento').prop('disabled', false);
                                                                    $('#comune').html('<option value=\'\'>Seleziona Comune</option>');
                                                                    $('#messaggio').slideToggle('fast').delay(2000).slideToggle('slow');
                                                                    calendario();
                                                                }
                                                            });
                                                        }
                                                    },
                                                    cancel: {
                                                        text: 'Cancella',
                                                        btnClass: 'btn-blue',
                                                        keys: ['enter', 'shift'],
                                                        action: function () {

                                                        }
                                                    }
                                                }
                                            });
                                        } else if (msg.msg == "ok") {
                                            $('#insmodlavoro').trigger('reset');
                                            $('#idcliente').val("");
                                            $('#idlavoro').val("");
                                            $('#data').val("");
                                            $('#datamatrimonio').val("");
                                            $('#comune').html('<option value=\'\'>Seleziona Comune</option>');
                                            $('#messaggio').slideToggle('fast').delay(2000).slideToggle('slow');
                                            calendario();
                                        } else if (msg.msg == "ko") {
                                            alert(msg.ko);
                                            return false;
                                        }
                                    }
                                });
                            });
                        }
                    });

                    /**/
                    /**/

                    /* mostra calendario */
                    calendario();

                    /* data */
                    $.datepicker.setDefaults($.datepicker.regional[ "it" ]);
                    $("#datap").datepicker({
                        altFormat: "yy-mm-dd",
                        altField: "#data"//,
                                //                        beforeShowDay: function (date) {
                                //                            var day = date.getDay();
                                //                            var result = true;
                                //                            var datestring = jQuery.datepicker.formatDate('yy-mm-dd', date);
                                //                            var datestring_ma = jQuery.datepicker.formatDate('mm-dd', date);
                                //                            if (pasqua.indexOf(datestring) >= 0)
                                //                                result = false;
                                //                            if (holidays.indexOf(datestring_ma) >= 0)
                                //                                result = false;
                                //                            //console.log(holidays);
                                //                            return [result];
                                //                        }
                    });
                    /* datamatrimonio */

                    $("#datamatrimoniop").datepicker({
                        altFormat: "yy-mm-dd",
                        altField: "#datamatrimonio"
                    });

                    //richiamaclienti();

                    $('#cliente').autocomplete({
                        minLength: 3,
                        source: function (request, response) {
                            var idatelier__ = 0;
                            var solo_sartoria__ = 0;
                            if ($('#idatelier').length > 0) {
                                if ($('#idatelier').val() > 0) {
                                    idatelier__ = $('#idatelier').val();
                                    solo_sartoria__ = $('#idatelier option:selected').data('solo_sartoria');
                                } else {
                                    idatelier__ = <?= ($_SESSION['livello'] == 0 || $_SESSION['livello'] == 1 ? 0 : $_SESSION['id']) ?>;
                                    solo_sartoria__ = <?= (int) $_SESSION['solo_sartoria'] ?>;
                                }
                            } else {
                                idatelier__ = <?= ($_SESSION['livello'] == 0 || $_SESSION['livello'] == 1 ? 0 : $_SESSION['id']) ?>;
                                solo_sartoria__ = <?= (int) $_SESSION['solo_sartoria'] ?>;
                            }

                            $.ajax({
                                type: "POST",
                                url: 'mrgest.php',
                                dataType: "jsonp",
                                data: {
                                    q: request.term,
                                    idatelier: idatelier__,
                                    solo_sartoria: solo_sartoria__,
                                    submit: 'cercaClienti'
                                },
                                success: function (data) {
                                    //console.log(data);
                                    response($.map(data, function (item) {
                                        return {
                                            label: item.cognome + ' ' + item.nome,
                                            value: item.id,
                                            nome: item.nome,
                                            cognome: item.cognome,
                                            sesso: item.sesso,
                                            provincia: item.provincia,
                                            comune: item.comune,
                                            telefono: item.telefono,
                                            email: item.email
                                        }
                                    }));
                                }
                            });
                        },
                        //source: arrCat,
                        select: function (event, ui) {
                            event.preventDefault();
                            //console.log(ui.item);
                            $('#idcliente').val(ui.item.id);
                            $('#nome').val(ui.item.nome);
                            $('#cognome').val(ui.item.cognome);
                            $('#sesso').val(ui.item.sesso);
                            $('#provincia').val(ui.item.provincia);
                            $('#comune').val(ui.item.comune);
                            $('#telefono').val(ui.item.telefono);
                            $('#email').val(ui.item.email);
                            selcomune(ui.item.provincia, ui.item.comune);
                        }
                    });
                    $('#tipoappuntamento').change(function () {
                        var tipoappuntamento = $(this).val();
                        if (tipoappuntamento == 7 || tipoappuntamento == 8) {
                            $('#provenienza').removeClass('required');
                        } else {
                            $('#provenienza').addClass('required');
                        }
                        getAddetti();
                    });
<?php } ?>
<?php if ($_GET['idatelier'] != '') { ?>
                    $('#idatelier').val(<?= $_GET['idatelier'] ?>).trigger('change');
<?php } ?>
            });
        </script>
        <style>
            .ui-state-highlight, .ui-widget-content .ui-state-highlight, .ui-widget-header .ui-state-highlight {
                background-image: none;
            }
        </style>
    </head>
    <body  class="colormodulo">
        <?php include './components/top.php'; ?>
        <div class="barra_submenu sizing">
            <li class="box_submenu sizing"><a href="mrgest.php"><i class="fa fa-calendar-o fa-lg" aria-hidden="true"></i> Agenda appuntamenti</a></li>
            <?php if (($_SESSION['livello'] == 3 && $_SESSION['ruolo'] == 0) || $_SESSION['ruolo'] == RISORSE_UMANE || $_SESSION['livello'] <= 1) { ?>
                <li class="box_submenu sizing"><a href="mrgest.php?op=agenda_dip"><i class="fa fa-calendar-o fa-lg" aria-hidden="true"></i> Calendario Turni<?= ($_SESSION['stato_dipendente'] == 3 || $_SESSION['livello'] <= 1 ? ' / Richieste' : '') ?></a></li>
            <?php } ?>
            <?php if (($_SESSION['livello'] == 5 && $_SESSION["diraff"] == 'd') || $_SESSION['ruolo'] == 1 || $_SESSION['ruolo'] == 2) { ?>
                <li class="box_submenu sizing"><a href="mrgest.php?op=agenda_dip_atelier"><i class="fa fa-calendar-o fa-lg" aria-hidden="true"></i> Calendario Turni</a></li>
            <?php } ?>     
            <?php if ($_SESSION['ruolo'] == 7 || $_SESSION['livello'] <= 1) { ?>
                <li class="box_submenu sizing"><a href="mrgest.php?op=file_dip"><i class="fa fa-exchange fa-lg" aria-hidden="true"></i> Esporta Excel Presenze</a></li>
            <?php } ?>
            <li class="box_submenu sizing"><a href="mrgest.php?op=stampe"><i class="fa fa-search fa-lg" aria-hidden="true"></i> Ricerca appuntamenti</a></li>
            <?php if ($_SESSION['livello'] <= 1) { ?>
                <li class="box_submenu sizing"><a href="mrgest.php?op=spoki"><i class="fa fa-bar-chart fa-lg" aria-hidden="true"></i> Statistiche Spoki</a></li>
                <?php if ($_SESSION['assistant_id'] != '') { ?>
                    <li class="box_submenu sizing"><a href="mrgest.php?op=openai"><i class="fa fa-android fa-lg" aria-hidden="true"></i> Open AI</a></li>
                <?php } ?>
            <?php } ?>
            <?php if ($_SESSION['ruolo'] == CENTRALINO || $_SESSION['livello'] <= 1 || $_SESSION['ruolo'] == RISORSE_UMANE) { ?>
                <li class="box_submenu sizing"><a href="mrgest.php?op=report_app"><i class="fa fa-bar-chart fa-lg" aria-hidden="true"></i> Report Appuntamenti centralino</a></li>
            <?php } ?>
            <?php if ($_SESSION['livello'] <= 1) { ?>
                <li class="box_submenu sizing"><a href="mrgest.php?op=report_app_tot"><i class="fa fa-bar-chart fa-lg" aria-hidden="true"></i> Report Appuntamenti totali</a></li>
            <?php } ?>
        </div>
        <?php
        if ($op == "stampe") {
            ?>

            <div class="content sizing">
                <div class="barra_op sizing">
                    Ricerca Appuntamenti
                    <div id="messaggio" class="messaggiook">Operazione avvenuta con successo</div>
                </div>
                <div class="showcont sizing" style="font-size: 1.2em; font-family: Arial">

                </div>
            </div>
        <?php } elseif ($op == 'spoki') {
            ?>
            <div class="content sizing">
                <div class="barra_op sizing">
                    Statistiche Spoki
                    <div id="messaggio" class="messaggiook">Operazione avvenuta con successo</div>
                </div>
                <div class="showcont sizing" style="font-size: 1.2em; font-family: Arial">

                </div>
            </div>
        <?php } elseif ($op == 'openai') {
            ?>
            <div class="content sizing" style="height: 100%;">
                <div class="barra_op sizing">
                    Open AI
                    <div id="messaggio" class="messaggiook">Operazione avvenuta con successo</div>
                </div>
                <div class="showcont sizing" style="font-size: 1.2em; font-family: Arial">

                </div>
            </div>
            <?php
        } elseif ($op == 'agenda_dip') {
            $eventi_tipo = getEventiTipo();
            ?>
            <div class="content sizing" style="height: 100%;">
                <div class="barra_op sizing">
                    Calendario Turni<?= ($_SESSION['stato_dipendente'] == 3 || $_SESSION['livello'] <= 1 ? ' / Richieste' : '') ?>
                    <div id="messaggio" class="messaggiook">Operazione avvenuta con successo</div>
                </div>
                <div class="showcont sizing" style="font-size: 1.2em; font-family: Arial">
                    <div class="mostracal-dip sizing">
                        <div class="dipendenti sizing">
                            <?php
                            if ($_SESSION['stato_dipendente'] == 3 || $_SESSION['livello'] <= 1 || $_SESSION['ruolo'] == RISORSE_UMANE) {
                                $all_dip = getDipendentiAll();
                                $all_atelier = getAtelier('');
                                ?>
                                <select name="iddipendente" id="iddipendente" class="input_moduli float_moduli">
                                    <option value="">Inserisci il cognome del dipendente</option>
                                    <?php
                                    foreach ($all_dip as $dip) {
                                        $atelier_user = getAtelierUser($dip['id']);
                                        $arrAtelier = [];
                                        foreach ($atelier_user as $at) {
                                            $arrAtelier[] = $at['idatelier'];
                                        }
                                        ?>
                                        <option value="<?= $dip['id'] ?>" data-atelier="<?= join(",", $arrAtelier) ?>"><?= $dip['cognome'] ?> <?= $dip['nome'] ?></option>
                                    <?php } ?>
                                </select>
                                <div class="chiudi" style="height: 20px;"></div>
                                <select name="idatelier" id="idatelier" class="input_moduli float_moduli">
                                    <option value="">Inserisci l'atelier</option>
                                    <?php foreach ($all_atelier as $dip) { ?>
                                        <option value="<?= $dip['id'] ?>"><?= $dip['nominativo'] ?></option>
                                    <?php } ?>
                                </select>
                                <div class="chiudi" style="height: 20px;"></div>
                                <input type="checkbox" id="solo_richieste" name="solo_richieste" value="1" /> Mostra tutte le richieste
                                <div class="chiudi" style="height: 20px;"></div>
                                <div id="cnt-richieste" style="margin:20px 0;"></div>
                                <div class="chiudi" style="height: 20px;"></div>
                                <button id="reset-filtri">Reset filtri</button>
                            <?php } else { ?>

                            <?php } ?>
                            <?php if ($_SESSION['livello'] <= 1 || $_SESSION['ruolo'] == RISORSE_UMANE) { ?>    
                                <div style="font-size: 1.2em;margin:20px 0;"><a class="addTurnoDip btn-inserimento" href="javascript:;"><i class="fa fa-plus-circle" aria-hidden="true"></i> Nuovo inserimento</a></div>
                            <?php } ?>
                            <div style="font-size: 1.2em;margin:20px 0;"><a class="addRichiestaDip btn-inserimento" href="javascript:;"><i class="fa fa-plus-circle" aria-hidden="true"></i> Nuova richiesta</a></div>
                            <?php if ($_SESSION['livello'] <= 1 || $_SESSION['ruolo'] == RISORSE_UMANE) { ?> 
                                <div id="cnt-ore-dip"></div>
                            <?php } ?>
                            <div id="calendarDipendenti"></div>
                        </div>
                    </div>
                    <select id="html-richiesta" style="display: none;">
                        <option value="">Seleziona il tipo</option>
                        <?php
                        foreach ($eventi_tipo as $tipo) {
                            if ($tipo['solo_admin'] == 0 || $_SESSION['stato_dipendente'] == 3 || $_SESSION['livello'] <= 1 || $_SESSION['ruolo'] == RISORSE_UMANE) {
                                ?>
                                <option value="<?= $tipo['id'] ?>"><?= $tipo['valore'] ?></option>
                                <?php
                            }
                        }
                        ?>
                    </select>
                    <?php if ($_SESSION['livello'] == 3 || $_SESSION['livello'] <= 1 || $_SESSION['ruolo'] == RISORSE_UMANE) { ?>
                        <select id="html-atelier" style="display: none;">
                            <?php
                            if ($_SESSION['livello'] == 3) {
                                $list_atelier = getAtelierUser($_SESSION['id']);
                                $campo = 'nome';
                                $campo_id = 'idatelier';
                            } elseif ($_SESSION['livello'] <= 1 || $_SESSION['ruolo'] == RISORSE_UMANE) {
                                $list_atelier = getAtelier("");
                                $campo = 'nominativo';
                                $campo_id = 'id';
                            }
                            foreach ($list_atelier as $atelier) {
                                ?>
                                <option value="<?= $atelier[$campo_id] ?>"><?= $atelier[$campo] ?></option>
                            <?php }
                            ?>
                        </select>
                    <?php } ?>
                    <?php if ($_SESSION['livello'] <= 1 || $_SESSION['ruolo'] == RISORSE_UMANE) { ?>
                        <select id="html-dipendenti" style="display: none;">
                            <?php
                            foreach ($all_dip as $dip) {
                                $atelier_user = getAtelierUser($dip['id']);
                                $arrAtelier = [];
                                foreach ($atelier_user as $at) {
                                    $arrAtelier[] = $at['idatelier'];
                                }
                                ?>
                                <option value="<?= $dip['id'] ?>" data-atelier="<?= join(",", $arrAtelier) ?>"><?= $dip['cognome'] ?> <?= $dip['nome'] ?></option>
                            <?php } ?>
                        </select>
                    <?php } ?> 
                </div>
            </div>
            <?php
        } elseif ($op == 'agenda_dip_atelier') {// atelier o district manager o storemanager
            $eventi_tipo = getEventiTipo();
            ?>
            <div class="content sizing" style="height: 100%;">
                <div class="barra_op sizing">
                    Calendario Turni
                    <div id="messaggio" class="messaggiook">Operazione avvenuta con successo</div>
                </div>
                <div class="showcont sizing" style="font-size: 1.2em; font-family: Arial">
                    <div class="mostracal-dip sizing">
                        <div class="dipendenti sizing">
                            <?php
                            if ($_SESSION['livello'] == 5) {
                                $all_dip = getDipendentiAtelier($_SESSION['id'], true);
                                ?>
                                <select name="iddipendente" id="iddipendente" data-idatelier="<?= $_SESSION['id'] ?>" class="input_moduli float_moduli">
                                    <option value="">Inserisci il cognome del dipendente</option>
                                    <?php foreach ($all_dip as $dip) { ?>
                                        <option value="<?= $dip['id'] ?>"><?= $dip['cognome'] ?> <?= $dip['nome'] ?></option>
                                    <?php } ?>
                                </select>
                                <div class="chiudi" style="height: 20px;"></div>
                                <button id="reset-filtri">Reset filtri</button>
                                <?php
                            } elseif ($_SESSION['livello'] == 3 && ($_SESSION['ruolo'] == 1 || $_SESSION['ruolo'] == 2 || $_SESSION['ruolo'] == RISORSE_UMANE)) {
                                $all_atelier = getAtelierUser($_SESSION['id']);
                                ?>
                                <select name="idatelier" id="idatelier" class="input_moduli float_moduli">
                                    <option value="">Inserisci l'atelier</option>
                                    <?php foreach ($all_atelier as $dip) { ?>
                                        <option value="<?= $dip['idatelier'] ?>"><?= $dip['nome'] ?></option>
                                    <?php } ?>
                                </select>
                                <div class="chiudi" style="height: 20px;"></div>
                                <button id="reset-filtri">Reset filtri</button>
                            <?php } ?>
                            <?php if ($_SESSION['livello'] == 5 || ($_SESSION['livello'] == 3 && $_SESSION['ruolo'] == 2)) { ?>
                                <div style="font-size: 1.2em;margin:20px 0;"><a class="addTurnoDip btn-inserimento" href="javascript:;"><i class="fa fa-plus-circle" aria-hidden="true"></i> Nuovo inserimento</a></div>
                            <?php } ?>
                            <?php if ($_SESSION['livello'] == 3 && ($_SESSION['ruolo'] == 1 || $_SESSION['ruolo'] == 2 || $_SESSION['ruolo'] == RISORSE_UMANE)) { ?>
                                <div style="font-size: 1.2em;margin:20px 0;"><a class="addRichiestaDip btn-inserimento" href="javascript:;"><i class="fa fa-plus-circle" aria-hidden="true"></i> Nuova richiesta</a></div>
                            <?php } ?>
                            <div id="cnt-ore-dip">
                                <?php foreach ($all_dip as $dip) { ?>
                                    <div style="margin:10px 0;"><?= $dip['cognome'] . ' ' . $dip['nome'] ?>: <span style="font-weight:bold;"><?= floatval($dip['ore_settimana']) ?> </span> ORE SETTIMANA - <b>ORE INSERITE: </b><span style="font-weight: bold;" id="ore-<?= $dip['id'] ?>">0</span></div>
                                <?php } ?>
                            </div>
                            <div id="calendarDipendenti"></div>
                        </div>
                    </div>
                    <?php if ($_SESSION['livello'] == 5 || $_SESSION['livello'] <= 1 || $_SESSION['ruolo'] == RISORSE_UMANE) { ?>
                        <select id="html-dipendenti" style="display: none;">
                            <?php foreach ($all_dip as $dip) { ?>
                                <option value="<?= $dip['id'] ?>"><?= $dip['cognome'] ?> <?= $dip['nome'] ?></option>
                            <?php } ?>
                        </select>
                    <?php } elseif ($_SESSION['ruolo'] == 1 || $_SESSION['ruolo'] == 2) { ?>
                        <select id="html-dipendenti" style="display: none;"></select>
                    <?php } ?>                    
                    <?php if ($_SESSION['ruolo'] == 1 || $_SESSION['ruolo'] == 2 || $_SESSION['livello'] <= 1 || $_SESSION['ruolo'] == RISORSE_UMANE) { ?>
                        <select id="html-atelier" style="display: none;">
                            <?php
                            $list_atelier = getAtelierUser($_SESSION['id']);
                            foreach ($list_atelier as $atelier) {
                                ?>
                                <option value="<?= $atelier['idatelier'] ?>"><?= $atelier['nome'] ?></option>
                            <?php }
                            ?>
                        </select>
                        <select id="html-richiesta" style="display: none;">
                            <option value="">Seleziona il tipo</option>
                            <?php
                            foreach ($eventi_tipo as $tipo) {
                                if ($tipo['solo_admin'] == 0 || $_SESSION['stato_dipendente'] == 3 || $_SESSION['livello'] <= 1 || $_SESSION['ruolo'] == RISORSE_UMANE) {
                                    ?>
                                    <option value="<?= $tipo['id'] ?>"><?= $tipo['valore'] ?></option>
                                    <?php
                                }
                            }
                            ?>
                        </select>
                    <?php } ?>
                </div>
            </div>
        <?php } elseif ($op == 'file_dip') {
            ?>
            <div class="content sizing" style="height: 100%;">
                <div class="barra_op sizing">
                    Esporta Excel Presenze
                    <div id="messaggio" class="messaggiook">Operazione avvenuta con successo</div>
                </div>
                <div class="showcont sizing">

                </div>
            </div>
            <?php
        } elseif ($op == 'report_app') {
            ?>
            <div class="content sizing" style="height: 100%;">
                <div class="barra_op sizing">
                    Esporta Report Appuntamenti centralino
                    <div id="messaggio" class="messaggiook">Operazione avvenuta con successo</div>
                </div>
                <div class="showcont sizing">

                </div>
            </div>
            <?php
        } elseif ($op == 'report_app_tot') {
            ?>
            <div class="content sizing" style="height: 100%;">
                <div class="barra_op sizing">
                    Esporta Report Appuntamenti totali
                    <div id="messaggio" class="messaggiook">Operazione avvenuta con successo</div>
                </div>
                <div class="showcont sizing">

                </div>
            </div>
            <?php
        } else {
            ?>
            <div class="content sizing">
                <div class="barra_op sizing">

                    <div id="messaggio" class="messaggiook">Operazione avvenuta con successo</div>
                </div>
                <div class="showcont sizing" style="font-size: 1.2em; font-family: Arial">   
                    <!-- form inserimento lavoro in agenda -->
                    <form method="post" action="" id="insmodlavoro" name="insmodlavoro">
                        <input type="hidden" id="idlavoro" name="idlavoro" value="" />
                        <input type="text" name="datap" id="datap" class="required input_moduli sizing float_moduli_small_10 nopost" placeholder="Data appuntamento" title="Data appuntamento" /> 
                        <input type="hidden" name="data" id="data" />
                        <input type="text" name="orario" id="orario" class="timepicker input_moduli sizing float_moduli_small_10 required" placeholder="Orario" title="Orario" />                     
                        <input type="text" name="cliente" id="cliente" onkeyup="pulisciidcliente();" class="input_moduli sizing float_moduli" placeholder="Chi: Cliente" title="Chi: Cliente" /> 
                        <input type="hidden" name="idcliente" id="idcliente" value="" />                    
                        <div class="chiudi"></div>
                        <input type="text" name="nome" id="nome" class="input_moduli sizing float_moduli_small required" placeholder="Nome" title="Nome" />   
                        <input type="text" name="cognome" id="cognome" class="input_moduli sizing float_moduli_small required" placeholder="Cognome" title="Cognome" /> 
                        <input type="text" name="telefono" id="telefono" class="input_moduli sizing float_moduli_small required" placeholder="Telefono" title="Telefono" />   
                        <input type="text" name="email" id="email" class="input_moduli sizing float_moduli_small" placeholder="Email" title="Email" />   
                        <select name="tipoappuntamento" id="tipoappuntamento" class=" required input_moduli sizing float_moduli_small_15">
                            <?php if ($_SESSION['solo_sartoria'] == 1) { ?>
                                <option value="5">Sartoria</option>
                            <?php } else { ?>
                                <option value="">Seleziona Tipo appuntamento</option>
                                <option value="1">Appuntamento Sposa</option>
                                <option value="2">Appuntamento Sposo</option>
                                <option value="6">Appuntamento Sposa e Sposo</option>
                                <option value="3">Cerimonia Donna</option>
                                <option value="4">Cerimonia Uomo</option>
                                <option value="5">Sartoria</option>
                                <option value="7">Ferie</option>
                                <option value="8">Lista</option>
                                <option value="9">Trunk Show</option>
                            <?php } ?>
                        </select>
                        <div class="chiudi"></div>
                        <?php if ($_SESSION['solo_sartoria'] != 1) { ?>
                            <select name="provincia" id="provincia" class="input_moduli sizing float_moduli_small" onchange="selcomune(this.value);">
                                <?php
                                $pr = getDati("province", "GROUP BY sigla ORDER BY regione ");
                                echo "<option value=\"\">Seleziona Provincia</option>";
                                foreach ($pr as $prd) {
                                    echo "<option value=\"" . $prd['sigla'] . "\">" . $prd['provincia'] . "</option>";
                                }
                                ?>
                            </select>
                            <select name="comune" id="comune" class="input_moduli sizing float_moduli_small">
                                <option value="">Seleziona Comune</option>                        
                            </select>
                        <?php } else { ?>
                            <input type="hidden" name="provincia" id="provincia" value="" />
                            <input type="hidden" name="comune" id="comune" value="" />
                        <?php } ?>
                        <input type="text" name="numero_contratto" id="numero_contratto" class="input_moduli sizing float_moduli_small" placeholder="Numero contratto" title="Numero contratto" style="<?= ($_SESSION['solo_sartoria'] != 1 ? 'display:none;' : '') ?>" /> 
                        <input type="text" name="datamatrimoniop" id="datamatrimoniop" class="input_moduli sizing float_moduli_small nopost" placeholder="Data Matrimonio" title="Data Matrimonio" />   
                        <input type="hidden" name="datamatrimonio" id="datamatrimonio" />
                        <?php if ($_SESSION['solo_sartoria'] != 1) { ?>
                            <select name="provenienza" id="provenienza" class="input_moduli sizing float_moduli_small <?= ($_SESSION['solo_sartoria'] == 1 ? '' : 'required') ?>">
                                <?php
                                $arrCentralino = Array(11, 9, 12, 16, 5);
                                $come = getDati("centri_costo", "ORDER BY id ");
                                echo "<option value=\"\">Seleziona Provenienza Contatto</option>";
                                foreach ($come as $comed) {
                                    if (($_SESSION['ruolo'] == CENTRALINO || $_SESSION['ruolo'] == RISORSE_UMANE) && in_array($comed['id'], $arrCentralino)) {
                                        echo "<option value=\"" . $comed['nome'] . "\">" . $comed['nome'] . "</option>";
                                    } elseif ($_SESSION['ruolo'] != CENTRALINO) {
                                        echo "<option value=\"" . $comed['nome'] . "\">" . $comed['nome'] . "</option>";
                                    }
                                }
                                ?>
                            </select>
                            <select name="disdetto" id="disdetto" class="input_moduli sizing float_moduli_small_10" title="Appuntamento Disdetto" placeholder="Appuntamento Disdetto">
                                <option value="0">NO</option>
                                <option value="1">SI</option>
                            </select>
                        <?php } else { ?>
                            <input type="hidden" name="provenienza" id="provenienza" value="" />
                            <input type="hidden" name="disdetto" id="disdetto" value="0" />
                        <?php } ?>
                        <div class="chiudi"></div>

                        <textarea name="note" id="note" class="textarea_moduli_small sizing float_moduli_50" title="Note" placeholder="Note"></textarea>
                        <div class="float_lft sizing" style="margin: 20px 0;">
                            <div class="float_lft" style="height: 12px; width: 12px; background-color: #db00cc; margin: 0px 5px;"></div>
                            <div class="float_lft sizing">Sposa</div>
                            <div class="float_lft" style="height: 12px; width: 12px; background-color: #1a00a4; margin: 0px 5px;"></div>
                            <div class="float_lft sizing">Sposo</div>
                            <div class="float_lft" style="height: 12px; width: 12px; background-color: #75507b; margin: 0px 5px;"></div>
                            <div class="float_lft sizing">Sposa e Sposo</div>
                            <div class="float_lft" style="height: 12px; width: 12px; background-color: #f5a9f0; margin: 0px 5px;"></div>
                            <div class="float_lft sizing">Cerimonia Donna</div>
                            <div class="float_lft" style="height: 12px; width: 12px; background-color: #69a2de; margin: 0px 5px;"></div>
                            <div class="float_lft sizing">Cerimonia Uomo</div>
                            <div class="float_lft" style="height: 12px; width: 12px; background-color: #c9b8a7; margin: 0px 5px;"></div>
                            <div class="float_lft sizing">Trunk show</div>
                            <div class="float_lft" style="height: 12px; width: 12px; background-color: #229f47; margin: 0px 5px;"></div>
                            <div class="float_lft sizing">Saldo/Ritiro</div>
                            <div class="float_lft" style="height: 12px; width: 12px; background-color: #696969; margin: 0px 5px;"></div>
                            <div class="float_lft sizing">Sartoria</div>
                            <div class="float_lft" style="height: 12px; width: 12px; background-color: red; margin: 0px 5px;"></div>
                            <div class="float_lft sizing">Disdetto</div>
                            <div class="float_lft" style="height: 12px; width: 12px; background-color: #cccc00; margin: 0px 5px;"></div>
                            <div class="float_lft sizing">Ferie</div>
                            <div class="float_lft" style="height: 12px; width: 12px; background-color: #ffa343; margin: 0px 5px;"></div>
                            <div class="float_lft sizing">Lista</div>
                            <div class="float_lft" style="height: 12px; width: 12px; background-color: #FF5F1F; margin: 0px 5px;"></div>
                            <div class="float_lft sizing">Fiera</div>
                            <div class="chiudi"></div>
                        </div>                   
                        <div class="chiudi"></div>
                        <div class="chiudi"></div>
                        <?php
                        if ($_SESSION["livello"] == 0 || $_SESSION["livello"] == 1 || $_SESSION["livello"] == 2 || $_SESSION['ruolo'] == CENTRALINO || $_SESSION['ruolo'] == RISORSE_UMANE) {
                            ?>
                            <div class="nomeacc sizing" style="width: auto; font-weight: bolder;">Atelier:</div>
                            <select name="idatelier" id="idatelier" class="input_moduli sizing float_moduli_small required" onchange="javascript:calendario(this.value), getDipat(this.value), setCampiSartoria();">
                                <?php
//                                if ($_SESSION['livello'] == '5') {
//                                    $and = "AND id = " . $_SESSION['id'] . "";
//                                }
                                $atelier = getAtelier("");
//                                if ($_SESSION["livello"] == 0 || $_SESSION["livello"] == 1) {
                                echo "<option value=\"\">Seleziona Atelier</option>";
//                                }
                                foreach ($atelier as $atelierd) {
                                    echo "<option value=\"" . $atelierd['id'] . "\" data-solo_sartoria=\"" . $atelierd['solo_sartoria'] . "\" data-patrono=\"" . $atelierd['patrono'] . "\" "
                                    . "data-aperture_spot=\"" . $atelierd['aperture_spot'] . "\" "
                                    . " data-chiusure_spot=\"" . $atelierd['chiusure_spot'] . "\""
                                    . " data-chiuso_dal=\"" . $atelierd['chiuso_dal'] . "\""
                                    . " data-chiuso_al=\"" . $atelierd['chiuso_al'] . "\">" . $atelierd['nominativo'] . "</option>";
                                }
                                ?>
                            </select>
        <!--                            <select name="idutente" id="idutente" class="input_moduli sizing float_moduli_small">
                                <option value="">Seleziona dipendente</option>
                            </select>-->
                            <?php
                        } else if ($_SESSION["livello"] == 5) {

                            $and = "AND (id = " . $_SESSION['id'] . ")";

                            $atelier = getAtelier($and);
                            echo "<input type=\"hidden\" name=\"idatelier\" id=\"idatelier\" "
                            . "data-patrono=\"{$atelier[0]['patrono']}\" data-aperture_spot=\"{$atelier[0]['aperture_spot']}\" data-chiusure_spot=\"" . $atelier[0]['chiusure_spot'] . "\""
                            . " data-chiuso_dal=\"" . $atelier[0]['chiuso_dal'] . "\" data-chiuso_al=\"" . $atelier[0]['chiuso_al'] . "\""
                            . " value=\"{$atelier[0]['id']}\" />"
                            . "<input type=\"hidden\" id=\"solo_sartoria\" name=\"solo_sartoria\" value=\"" . $_SESSION['solo_sartoria'] . "\" />";
                            ?>
        <!--                            <select name="idutente" id="idutente" class="input_moduli sizing float_moduli_small">
                                <option value="">Seleziona dipendente</option>
                            </select>-->
                            <?php
                        } else if ($_SESSION["livello"] == 3) {//dipendenti
                            $ateliers = getAtelierUser($_SESSION['id']);
                            if (count($ateliers) > 1) {//ha piu negozi 
                                ?>
                                <div class="nomeacc sizing" style="width: auto; font-weight: bolder;">Atelier:</div>
                                <select name="idatelier" id="idatelier" class="input_moduli sizing float_moduli_small required" onchange="javascript:calendario(this.value), getDipat(this.value), setCampiSartoria();">
                                    <?php
                                    echo "<option value=\"\">Seleziona Atelier</option>";
                                    foreach ($ateliers as $atelier) {
                                        $atelierd_tmp = getAtelier("and id=" . $atelier['idatelier']);
                                        $atelierd = $atelierd_tmp[0];
                                        echo "<option value=\"" . $atelierd['id'] . "\" data-solo_sartoria=\"" . $atelierd['solo_sartoria'] . "\" data-patrono=\"" . $atelierd['patrono'] . "\" "
                                        . "data-aperture_spot=\"" . $atelierd['aperture_spot'] . "\" "
                                        . " data-chiusure_spot=\"" . $atelierd['chiusure_spot'] . "\""
                                        . " data-chiuso_dal=\"" . $atelierd['chiuso_dal'] . "\""
                                        . " data-chiuso_al=\"" . $atelierd['chiuso_al'] . "\" " . ($atelierd['id'] == $_SESSION['idatelier'] && false ? 'selected' : '') . ">" . $atelierd['nominativo'] . "</option>";
                                    }
                                    ?>
                                </select>
            <!--                                <select name="idutente" id="idutente" class="input_moduli sizing float_moduli_small">
                                    <option value="">Seleziona dipendente</option>
                                </select>-->
                                <?php
                            } else {//un solo negozio
                            }
                        }
                        ?>


                        <div class="chiudi"></div>
                        <div id="contienidip">
                        </div>
                        <div class="chiudi"></div>
                        <input type="submit" class="submit_form submit_form_10 nopost" value="INVIA" id="submitinsmodlavoro" />  <a style="text-align: center; color: #ffffff; background-color: darkred;line-height: 35px;" class="margin-l_10 float_lft submit_form submit_form_10 nopost" href="javascript:;" onclick="$('#insmodlavoro').trigger('reset'), $('#data').val(''), $('#datamatrimonio').val(''), $('#idcliente').val(''), $('#idlavoro').val(''), $('#comune').html('<option value=\'\'>Seleziona Comune</option>'), calendario();">RESET CAMPI</a>      
                        <div class="chiudi" style="height: 20px;"></div>
                        <?php
                        if ($_SESSION["livello"] <= 1 || $_SESSION['ruolo'] == CENTRALINO || $_SESSION['ruolo'] == RISORSE_UMANE) {
                            $now = date('Y-m-d');
                            $qry = "select count from telefonate_noapp where data_app=? limit 1;";
                            $rs_tel = $db->prepare($qry);
                            $valori = Array($now);
                            $rs_tel->execute($valori);
                            $count_tel = (int) $rs_tel->fetchColumn();
                            ?>
                            <div style="margin-top:20px;font-size: 16px;">Contatore telefonate senza appuntamento per il <?= date('d/m/Y') ?>: <input type="text" id="count_tel" value="<?= $count_tel ?>" disabled style="width: 100px;text-align: right;margin-right: 10px;" /><button type="button" class="btn btn-secondary btn-tel" data-inc="+1">+</button><button type="button" class="btn btn-secondary btn-tel" data-inc="-1">-</button></div>
                            <?php } ?>
                        <div id="contaperture"></div>
                        <div id="contaddetti"></div>
                    </form>

                    <script type="text/javascript">
                        $(document).ready(function () {
                            $('form').attr('autocomplete', 'off');
                            /* orario */
                            $('input.timepicker').timepicker({
                                timeFormat: 'HH:mm',
                                minTime: new Date(0, 0, 0, 6, 0, 0),
                                maxTime: new Date(0, 0, 0, 21, 0, 0),
                                interval: 30,
                                dynamic: false,
                                dropdown: true,
                                scrollbar: true
                            });
    <?php
    if ($_SESSION["livello"] == 0 || $_SESSION["livello"] == 1 || $_SESSION["livello"] == 5 || $_SESSION["livello"] == 2) {
        ?>
                                getDipat("<?php echo $_SESSION['id'] ?>");
    <?php } ?>
    <?php if ($_SESSION["livello"] <= 1 || $_SESSION['ruolo'] == CENTRALINO || $_SESSION['ruolo'] == RISORSE_UMANE) { ?>
                                $('.btn-tel').unbind('click').click(function () {
                                    var inc = $(this).data('inc');
                                    var count_tel = parseInt($('#count_tel').val());
                                    if (parseInt(inc) < 0 && count_tel == 0) {
                                        return false;
                                    }
                                    $.ajax({
                                        type: "POST",
                                        url: "./dipendenti.php",
                                        data: "inc=" + encodeURIComponent(inc) + "&submit=setNumTel",
                                        dataType: "json",
                                        success: function (msg) {
                                            count_tel = count_tel + parseInt(inc);
                                            $('#count_tel').val(count_tel);
                                        }
                                    });
                                });
    <?php } ?>
                        });
                    </script>
                    <div class="chiudi"></div>
                    <!-- mostro il calendario -->
                    <div class="mostracal sizing">
                        <!-- qui mostra il calendario --> 
                    </div>
                </div>
            </div>
        <?php } ?>
    </body>
</html>
