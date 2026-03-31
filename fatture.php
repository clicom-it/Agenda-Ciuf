<?php
ob_start('ob_gzhandler');
include './library/connessione.php';
include './library/controllo.php';
include './library/config.php';
include './library/functions.php';
include './library/basic.class.php';
/* libreria del modulo */
include './library/fatture.class.php';

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

    case "submitstampe":
        die('{"msg": "ok"}');
        break;

    case "submitesporta":
        die('{"msg": "ok"}');
        break;

    case "calcoloscadenze":
        $metodopagamento = $_POST['metodopagamento'];
        $finemese_vista = $_POST['finemese_vista'];
        $datafatt = $_POST['datafatt'];
        $datafattarr = explode("-", $datafatt);
        $totalefatt_iva = $_POST['totalefatt_iva'];
        /* selezione dei giorni in cui dividere le scadenze */
        $sql = $db->prepare("SELECT tempo FROM metodi_pagamento WHERE id = ? LIMIT 1");
        $sql->execute(array($metodopagamento));
        $tempoarr = explode("-", $sql->fetchColumn());
        /* numero scadenze */
        $numeroscadenze = count($tempoarr);

        for ($d = 0; $d < $numeroscadenze; $d++) {
            if ($finemese_vista == 0) {
                if ($datafattarr[2] >= 4) {
                    $datascadarr[] = date('t/m/Y', strtotime($datafatt . '+ ' . $tempoarr[$d] . ' days - 2 days'));
                } else {
                    $datascadarr[] = date('t/m/Y', strtotime($datafatt . '+ ' . $tempoarr[$d] . ' days + 2 days'));
                }
            } else {
                $datascadarr[] = date('d/m/Y', strtotime($datafatt . '+ ' . $tempoarr[$d] . ' days'));
            }
        }

        /* valore scadenze */
        $valorescadenza = round($totalefatt_iva / $numeroscadenze, 2);
        if ($numeroscadenze > 1) {
            $valoreultimascadenza = $totalefatt_iva - ($valorescadenza * ($numeroscadenze - 1));

            for ($i = 0; $i < $numeroscadenze - 1; $i++) {
                $scadenzearr[] = $valorescadenza;
            }
            $scadenzearr[] = $valoreultimascadenza;
        } else {
            //$scadenzearr[] = "<input type=\"text\" name=\"datascadenza[]\" value=\"\" /> <input type=\"text\" name=\"importoscadenza[]\" value=\"$valorescadenza\" /> <input type=\"text\" name=\"notescadenza[]\" value=\"\" />";
            $scadenzearr[] = $valorescadenza;
        }
        for ($s = 0; $s < count($datascadarr); $s++) {
            $scadenzeprint .= "<div class=\"riga_prodotto_fatt sizing nosortable\"><input type=\"text\" class=\"input_moduli sizing float_moduli_small_10 datascad\" placeholder=\"Data scadenza\" title=\"Data scadenza\" name=\"datascadenza[]\" value=\"" . $datascadarr[$s] . "\" /> <input type=\"text\" class=\"input_moduli sizing float_moduli_small_10\" name=\"importoscadenza[]\" value=\"" . $scadenzearr[$s] . "\"  placeholder=\"Importo\" title=\"Importo\" /> <input type=\"text\" name=\"notescadenza[]\" value=\"\" class=\"input_moduli sizing float_moduli_small\"  placeholder=\"Note\" title=\"Note\" /><div class=\"chiudi\"></div></div>";
        }

        die('{"scadenze" : ' . json_encode($scadenzeprint) . '}');

        break;

    case "bancapreventivicommessefattureokcliente":
        $idcliente = $_POST['idcliente'];
        /* fatture */
        $tabella = "fatture";
        $dati = new fatture($id, $tabella);
        $where = "idcliente = $idcliente AND (tipo = '0' or tipo='5' or tipo='4') ORDER BY data DESC, numero DESC";
        $fatture = $dati->richiamaWheredata($where);
        /* preventivi */
        $tabella = "preventivi";
        $dati = new fatture($id, $tabella);
        $where = "idcliente = $idcliente AND stato = '2'";
        $preventivo = $dati->richiamaWheredata($where);
        /* commesse */
        $tabella = "commesse";
        $dati = new fatture($id, $tabella);
        $where = "idcliente = $idcliente AND stato = '2'";
        $commesse = $dati->richiamaWheredata($where);
        /* banca */
        $tabella = "clienti_fornitori";
        $dati = new fatture($idcliente, $tabella);
        $banche = $dati->richiama();
        /* domini */
        $tabella = "domini";
        $dati = new fatture($idcliente, $tabella);
        $where = "idcliente = $idcliente";
        $domini = $dati->richiamaWhere($where);
        /* ddt */
        $tabella = "ddt";
        $dati = new fatture($idcliente, $tabella);
        $where = "idcliente = $idcliente AND stato != '4'";
        $ddt = $dati->richiamaWheredata($where);
        /* ordini */
        $tabella = "ordini";
        $dati = new fatture($idcliente, $tabella);
        $where = "idcliente = $idcliente AND stato != '1'";
        $ordini = $dati->richiamaWheredata($where);
        die('{"ordini" : ' . json_encode($ordini) . ', "ddt" : ' . json_encode($ddt) . ', "domini" : ' . json_encode($domini) . ', "preventivi" : ' . json_encode($preventivo) . ', "commesse" : ' . json_encode($commesse) . ', "banche" : ' . json_encode($banche) . ', "fatture" : ' . json_encode($fatture) . '}');
        break;

    case "collegapreventivo":
        $tabella = "preventivi";
        $id = $_POST['id'];
        $dati = new fatture($id, $tabella);
        $preventivo = $dati->richiama();
        /* voci */
        $tabella = "preventivi_voci";
        $where = "idprev = $id ORDER BY ordine";
        $dativoci = new fatture($id, $tabella);
        $voci = $dativoci->richiamaWhere($where);
        /* iva */
        $iva = getDati("iva", "ORDER BY valore");
        foreach ($iva as $ivap) {
            $ivavalore = $ivap['valore'];
            $ivapred = $ivap['predefinito'];

            if ($ivapred > 0) {
                $ivaselected = "selected";
            } else {
                $ivaselected = "";
            }
            $ivaselect .= "<option value=\"$ivavalore" . ($ivap['codice_iva'] != "" ? " " . $ivap['codice_iva'] : "") . "\" data-codice_iva=\"{$ivap['codice_iva']}\" $ivaselected>$ivavalore {$ivap['codice_iva']}</option>";
        }
        /* iva default */
        $ivadef = getDati("iva", "WHERE predefinito = '1'");
        $ivadefault = $ivadef[0]['valore'];
        /**/
        /* elenco i profit center */
        $profit = getDati("profit_center", "WHERE idp='0'");
        foreach ($profit as $profitd) {
            $vociprofit .= "<option value=\"" . $profitd['id'] . "\">" . $profitd['nome'] . "</option>";
        }

        /* magazzino */
        $magazzino = getDati("magazzino", "");

        die('{"magazzino" : ' . json_encode($magazzino) . ', "valori" : ' . json_encode($preventivo) . ', "voci" : ' . json_encode($voci) . ', "ivaselect" : ' . json_encode($ivaselect) . ', "ivadefault" : ' . json_encode($ivadefault) . ', "vociprofit" : ' . json_encode($vociprofit) . '}');
        break;
        
        case "collegaddt":
        $tabella = "ddt";
        $id = $_POST['id'];
        $dati = new fatture($id, $tabella);
        $ddt = $dati->richiama();
        /* voci */
        $tabella = "ddt_voci";
        $where = "idddt = $id ORDER BY ordine";
        $dativoci = new fatture($id, $tabella);
        $voci = $dativoci->richiamaWhere($where);
        /* iva */
        $iva = getDati("iva", "ORDER BY valore");
        foreach ($iva as $ivap) {
            $ivavalore = $ivap['valore'];
            $ivapred = $ivap['predefinito'];

            if ($ivapred > 0) {
                $ivaselected = "selected";
            } else {
                $ivaselected = "";
            }
            $ivaselect .= "<option value=\"$ivavalore" . ($ivap['codice_iva'] != "" ? " " . $ivap['codice_iva'] : "") . "\" data-codice_iva=\"{$ivap['codice_iva']}\" $ivaselected>$ivavalore {$ivap['codice_iva']}</option>";
        }
        /* iva default */
        $ivadef = getDati("iva", "WHERE predefinito = '1'");
        $ivadefault = $ivadef[0]['valore'];
        /**/
        /* elenco i profit center */
        $profit = getDati("profit_center", "WHERE idp='0'");
        foreach ($profit as $profitd) {
            $vociprofit .= "<option value=\"" . $profitd['id'] . "\">" . $profitd['nome'] . "</option>";
        }

        /* magazzino */
        $magazzino = getDati("magazzino", "");

        die('{"magazzino" : ' . json_encode($magazzino) . ', "valori" : ' . json_encode($ddt) . ', "voci" : ' . json_encode($voci) . ', "ivaselect" : ' . json_encode($ivaselect) . ', "ivadefault" : ' . json_encode($ivadefault) . ', "vociprofit" : ' . json_encode($vociprofit) . '}');
        break;
        
        case "collegaordine":
        $tabella = "ordini";
        $id = $_POST['id'];
        $dati = new fatture($id, $tabella);
        $ordini = $dati->richiama();
        /* voci */
        $tabella1 = "ordini_voci";
        $where = "idordine = $id ORDER BY ordine";
        $dativoci = new fatture($id, $tabella1);
        $voci = $dativoci->richiamaWhere($where);
        /* iva */
        $iva = getDati("iva", "ORDER BY valore");
        foreach ($iva as $ivap) {
            $ivavalore = $ivap['valore'];
            $ivapred = $ivap['predefinito'];

            if ($ivapred > 0) {
                $ivaselected = "selected";
            } else {
                $ivaselected = "";
            }
            $ivaselect .= "<option value=\"$ivavalore" . ($ivap['codice_iva'] != "" ? " " . $ivap['codice_iva'] : "") . "\" data-codice_iva=\"{$ivap['codice_iva']}\" $ivaselected>$ivavalore {$ivap['codice_iva']}</option>";
        }
        /* iva default */
        $ivadef = getDati("iva", "WHERE predefinito = '1'");
        $ivadefault = $ivadef[0]['valore'];
        /**/
        /* elenco i profit center */
        $profit = getDati("profit_center", "WHERE idp='0'");
        foreach ($profit as $profitd) {
            $vociprofit .= "<option value=\"" . $profitd['id'] . "\">" . $profitd['nome'] . "</option>";
        }

        /* magazzino */
        $magazzino = getDati("magazzino", "");

        die('{"magazzino" : ' . json_encode($magazzino) . ', "valori" : ' . json_encode($ordini) . ', "voci" : ' . json_encode($voci) . ', "ivaselect" : ' . json_encode($ivaselect) . ', "ivadefault" : ' . json_encode($ivadefault) . ', "vociprofit" : ' . json_encode($vociprofit) . '}');
        break;

    case "collegadominio":
        $tabella = "domini";
        $id = $_POST['id'];
        $dati = new fatture($id, $tabella);
        $dominio = $dati->richiama();
        /* iva */
        $iva = getDati("iva", "ORDER BY valore");
        foreach ($iva as $ivap) {
            $ivavalore = $ivap['valore'];
            $ivapred = $ivap['predefinito'];

            if ($ivapred > 0) {
                $ivaselected = "selected";
            } else {
                $ivaselected = "";
            }
            $ivaselect .= "<option value=\"$ivavalore\" $ivaselected>$ivavalore</option>";
        }
        /* iva default */
        $ivadef = getDati("iva", "WHERE predefinito = '1'");
        $ivadefault = $ivadef[0]['valore'];
        /**/
        /* elenco i profit center */
        $profit = getDati("profit_center", "WHERE idp='0'");
        foreach ($profit as $profitd) {
            $vociprofit .= "<option value=\"" . $profitd['id'] . "\">" . $profitd['nome'] . "</option>";
        }
        /* magazzino */
        $magazzino = getDati("magazzino", "");

        die('{"magazzino" : ' . json_encode($magazzino) . ', "valori" : ' . json_encode($dominio) . ', "ivaselect" : ' . json_encode($ivaselect) . ', "ivadefault" : ' . json_encode($ivadefault) . ', "vociprofit" : ' . json_encode($vociprofit) . '}');
        break;

    case "collegafattura":
        $tabella = "fatture";
        $id = $_POST['id'];
        $dati = new fatture($id, $tabella);
        $fattura = $dati->richiama();
        /* voci */
        $tabella = "fatture_voci";
        $where = "idfattura = $id ORDER BY ordine";
        $dativoci = new fatture($id, $tabella);
        $voci = $dativoci->richiamaWhere($where);
        /* iva */
        $iva = getDati("iva", "ORDER BY valore");
        foreach ($iva as $ivap) {
            $ivavalore = $ivap['valore'];
            $ivapred = $ivap['predefinito'];

            if ($ivapred > 0) {
                $ivaselected = "selected";
            } else {
                $ivaselected = "";
            }
            $ivaselect .= "<option value=\"$ivavalore\" $ivaselected>$ivavalore</option>";
        }
        /* iva default */
        $ivadef = getDati("iva", "WHERE predefinito = '1'");
        $ivadefault = $ivadef[0]['valore'];
        /**/
        /* elenco i profit center */
        $profit = getDati("profit_center", "WHERE idp='0'");
        foreach ($profit as $profitd) {
            $vociprofit .= "<option value=\"" . $profitd['id'] . "\">" . $profitd['nome'] . "</option>";
        }
        
        /* magazzino */
$magazzino = getDati("magazzino", "");
        
        die('{"magazzino" : ' . json_encode($magazzino) . ', "valori" : ' . json_encode($fattura) . ', "voci" : ' . json_encode($voci) . ', "ivaselect" : ' . json_encode($ivaselect) . ', "ivadefault" : ' . json_encode($ivadefault) . ', "vociprofit" : ' . json_encode($vociprofit) . '}');
        break;

    case "collegacommessa":
        $tabella = "commesse";
        $id = $_POST['id'];
        $dati = new fatture($id, $tabella);
        $commessa = $dati->richiama();
        /* voci */
        $tabella = "commesse_voci";
        $where = "idcomm = $id ORDER BY ordine";
        $dativoci = new fatture($id, $tabella);
        $voci = $dativoci->richiamaWhere($where);
        /* iva */
        $iva = getDati("iva", "ORDER BY valore");
        foreach ($iva as $ivap) {
            $ivavalore = $ivap['valore'];
            $ivapred = $ivap['predefinito'];

            if ($ivapred > 0) {
                $ivaselected = "selected";
            } else {
                $ivaselected = "";
            }
            $ivaselect .= "<option value=\"$ivavalore\" $ivaselected>$ivavalore</option>";
        }
        /* iva default */
        $ivadef = getDati("iva", "WHERE predefinito = '1'");
        $ivadefault = $ivadef[0]['valore'];
        /**/
        /* elenco i profit center */
        $profit = getDati("profit_center", "WHERE idp='0'");
        foreach ($profit as $profitd) {
            $vociprofit .= "<option value=\"" . $profitd['id'] . "\">" . $profitd['nome'] . "</option>";
        }

        /* magazzino */
        $magazzino = getDati("magazzino", "");

        die('{"magazzino" : ' . json_encode($magazzino) . ', "valori" : ' . json_encode($commessa) . ', "voci" : ' . json_encode($voci) . ', "ivaselect" : ' . json_encode($ivaselect) . ', "ivadefault" : ' . json_encode($ivadefault) . ', "vociprofit" : ' . json_encode($vociprofit) . '}');
        break;

    case "editformfattura":

        $id = $_POST['id'];
        $tabella = "fatture";
        /* progressivo a seconda del tipo di fattura */
        $tipo = $_POST['tipo'];
        /* controllo data attuale per numerazione */
        $dataarr = explode("-", $_POST['data']);
        $annocontrollo = $dataarr[0];
        $data = $_POST['data'];
        $datifattattuale = getDati("fatture", "WHERE id = $id");
        $tipoattuale = $datifattattuale[0]['tipo'];
        if ($tipoattuale != $tipo) {
            $sql = $db->prepare("SELECT MAX(data) as datadb, YEAR(MAX(data)) as annodb FROM $tabella WHERE YEAR(data) = ? AND tipo = $tipo ORDER BY data DESC LIMIT 1");
            $sql->execute(array($annocontrollo));
            if ($sql->rowCount > 0) {
                $res = $sql->fetch();
                $datadbcontrollo = $res['datadb'];
                $annodb = $res['annodb'];
                if ($datadbcontrollo > $data && $annodb <= $annocontrollo) {
                    die('{"msg" : "ko", "msgko" : "Errore! Data fattura antecedente ad una fattura già emessa"}');
                }
            }
        }
        /**/
        /* controllo se fattura diversa e cambio numero */
        if ($tipoattuale != $tipo) {
            if ($tipo == 0 || $tipo == 3) {
                $sql = $db->prepare("SELECT MAX(numero) as maxnum FROM $tabella WHERE (tipo = ? || tipo = ?) AND id != $id AND YEAR(data) =?");
                $sql->execute(array(0, 3, $annocontrollo));
                $_POST['numero'] = $sql->fetchColumn() + 1;
            } else if ($tipo == 5 || $tipo == 6) {
                $sql = $db->prepare("SELECT MAX(numero) as maxnum FROM $tabella WHERE (tipo = ? || tipo = ?) AND YEAR(data) =?");
                $sql->execute(array(5, 6, $annocontrollo));
                $_POST['numero'] = $sql->fetchColumn() + 1;
            } else {
                $sql = $db->prepare("SELECT MAX(numero) as maxnum FROM $tabella WHERE tipo = ? AND id != $id AND YEAR(data)  =?");
                $sql->execute(array($tipo, $annocontrollo));
                $_POST['numero'] = $sql->fetchColumn() + 1;
            }
        }

        /* nome metodo di pagamento */
        $idmetodo = $_POST['idpagamento'];
        $sql = $db->prepare("SELECT nome FROM metodi_pagamento WHERE id = ?");
        $sql->execute(array($idmetodo));
        $_POST['metodopagamento'] = $sql->fetchColumn();
        /**/

        /* preventivi collegati */
        if ($_POST['idprevfatt'] > 0) {
            for ($p = 0; $p < count($_POST['idprevfatt']); $p++) {
                $sql = $db->prepare("UPDATE preventivi SET stato = ? WHERE id = ?");
                $sql->execute(array(4, $_POST['idprevfatt'][$p]));
            }
        }
        /**/
        /* ddt collegati */
        if ($_POST['idddt'] > 0) {
            for ($p = 0; $p < count($_POST['idddt']); $p++) {
                $sql = $db->prepare("UPDATE ddt SET stato = ? WHERE id = ?");
                $sql->execute(array(4, $_POST['idddt'][$p]));
            }
        }
        /* ordini collegati */
        if ($_POST['idordine'] > 0) {
            for ($p = 0; $p < count($_POST['idordine']); $p++) {
                $sql = $db->prepare("UPDATE ordini SET stato = ? WHERE id = ?");
                $sql->execute(array(1, $_POST['idordine'][$p]));
            }
        }

        $fatture = new fatture($id, $tabella);
        /* voci della fattura */
        for ($i = 0; $i < count($_POST['descrizione']); $i++) {
//            if ($_POST['descrizione'][$i] != "" && $_POST['qta'][$i] != "") {
            if ($_POST['um'] == "") {
                $_POST['um'] = "N";
            }
            if ($_POST['codice_iva'][$i] != "") {
                $_POST['iva'][$i] = 0;
            }
            $voci[$i] = array("nome" => $_POST['nome'][$i], "descrizione" => $_POST['descrizione'][$i], "um" => $_POST['um'][$i], "qta" => $_POST['qta'][$i], "importo" => $_POST['importo'][$i],
                "iva" => $_POST['iva'][$i], "sconto" => $_POST['sconto'][$i], "idprev" => $_POST['idprev'][$i], "idddt" => $_POST['idddt'][$i], "idordine" => $_POST['idordine'][$i], "idcomm" => $_POST['idcomm'][$i],
                "idvocecomm" => $_POST['idvocecomm'][$i], "idfatt" => $_POST['idfatt'][$i], "idprofit" => $_POST['idprofit'][$i], "ordine" => $i,
                "codice_iva" => $_POST['codice_iva'][$i]);
            /* setto fatturato voci commessa */
            if ($_POST['idvocecomm'][$i] > 0) {
                $sql = $db->prepare("UPDATE commesse_voci SET statovoce=? WHERE id = ?");
                $sql->execute(array('1', $_POST['idvocecomm'][$i]));
            } else {
                
            }
//            } else {
//                
//            }
        }
        array_filter($voci, 'strlen'); // controllo array vuoto

        /* scadenze */
//        if ($tipo < 3 || $tipo == 4) {
        /* scadenze della fattura */
        for ($s = 0; $s < count($_POST['datascadenza']); $s++) {
            if ($_POST['datascadenza'][$s] != "") {
                $dataarr = explode("/", $_POST['datascadenza'][$s]);
                $datadb = $dataarr[2] . "-" . $dataarr[1] . "-" . $dataarr[0];

                $scadenze[$s] = array("datascadenza" => $datadb, "importoscadenza" => $_POST['importoscadenza'][$s], "notescadenza" => $_POST['notescadenza'][$s]);
            } else {
                
            }
        }
        array_filter($scadenze, 'strlen'); // controllo array vuoto
//        }
        $modificascadenze = $_POST['modificascadenze'];

        /* tolgo dall'array submit e post che non mi servono */
        unset($_POST['submit'], $_POST['nome'], $_POST['descrizione'], $_POST['um'], $_POST['qta'], $_POST['importo'], $_POST['iva'], $_POST['codice_iva'], $_POST['sconto'], $_POST['idprevfatt'], $_POST['idcommfatt'], $_POST['idprev'], $_POST['idddt'], $_POST['idordine'], $_POST['idcomm'], $_POST['idvocecomm'], $_POST['idfatt'], $_POST['idprofit'], $_POST['datascadenza'], $_POST['importoscadenza'], $_POST['notescadenza'], $_POST['modificascadenze']);

        foreach ($_POST as $k => $v) {
            $campi[] = $k;
            $valori[] = $v;
        }
        /* funzione aggiorna */
        $statofatt = $_POST['stato'];

        $fatture->aggiorna($campi, $valori, $voci, $scadenze, $modificascadenze, $statofatt, $tipo);
        die('{"msg": "ok"}');
        break;

    case "richiamafattura":
        $tabella = "fatture";
        $id = $_POST['id'];
        $dati = new fatture($id, $tabella);
        $fattura = $dati->richiama();
        /* voci */
        $tabella = "fatture_voci";
        $where = "idfattura = $id ORDER BY ordine";
        $dativoci = new fatture($id, $tabella);
        $voci = $dativoci->richiamaWhere($where);
        /* iva */
        $iva = getDati("iva", "ORDER BY valore");
        foreach ($iva as $ivap) {
            $ivavalore = $ivap['valore'];
            $ivapred = $ivap['predefinito'];

            if ($ivapred > 0) {
                $ivaselected = "selected";
            } else {
                $ivaselected = "";
            }
            $ivaselect .= "<option value=\"$ivavalore" . ($ivap['codice_iva'] != "" ? " " . $ivap['codice_iva'] : "") . "\" data-codice_iva=\"{$ivap['codice_iva']}\" $ivaselected>$ivavalore {$ivap['codice_iva']}</option>";
        }

        /* elenco i profit center */
        $profit = getDati("profit_center", "WHERE idp='0'");
        foreach ($profit as $profitd) {
            $vociprofit .= "<option value=\"" . $profitd['id'] . "\">" . $profitd['nome'] . "</option>";
        }

        /* scadenze */
        $scad = getDati("fatture_scadenze", "WHERE idfattura = $id AND sufattura = '0' ORDER BY datascadenza ASC");
        foreach ($scad as $scadd) {
            $dataarr = explode("-", $scadd['datascadenza']);
            $datait = $dataarr[2] . "/" . $dataarr[1] . "/" . $dataarr[0];
            $scadenzeprint .= "<div class=\"riga_prodotto_fatt sizing nosortable\"><input type=\"text\" class=\"input_moduli sizing float_moduli_small_10 datascad\" placeholder=\"Data scadenza\" title=\"Data scadenza\" name=\"datascadenza[]\" value=\"" . $datait . "\" /> <input type=\"text\" class=\"input_moduli sizing float_moduli_small_10\" name=\"importoscadenza[]\" value=\"" . $scadd['importoscadenza'] . "\"  placeholder=\"Importo\" title=\"Importo\" /> <input type=\"text\" name=\"notescadenza[]\" value=\"" . $scadd['notescadenza'] . "\" class=\"input_moduli sizing float_moduli_small\"  placeholder=\"Note\" title=\"Note\" /><div class=\"chiudi\"></div></div>";
        }

        /* magazzino */
        $magazzino = getDati("magazzino", "");

        die('{"magazzino" : ' . json_encode($magazzino) . ', "valori" : ' . json_encode($fattura) . ', "voci" : ' . json_encode($voci) . ', "ivaselect" : ' . json_encode($ivaselect) . ', "vociprofit" : ' . json_encode($vociprofit) . ', "scadenze" : ' . json_encode($scadenzeprint) . '}');
        break;

    case "delete":
        $tabella = "fatture";
        $id = $_POST['id'];
        $dati = new fatture($id, $tabella);
        $fatture = $dati->cancella();
        die('{"msg": "ok"}');
        break;

    case "mostrafatture":
        $tabella = "fatture";
        $id = $_POST['id'];
        $dati = new fatture($id, $tabella);
        $arch = $_POST['arch'];
        if ($arch == 1) {
            $where = "stato = '1' OR YEAR(data) != '" . DATE("Y") . "' ORDER BY data DESC, numero DESC";
        } else if ($arch == 2) {
            $where = "tipo = '1' ORDER BY data DESC, numero DESC";
        } else {
            $where = "stato = '0' AND tipo != '1' AND YEAR(data) = '" . DATE("Y") . "' ORDER BY data DESC, numero DESC";
        }
        $qry = "select *, DATE_FORMAT(data, '%d/%m/%Y') as datait,"
                . "(select IF(codice_sdi!='', codice_sdi, pec) from clienti_fornitori cf where cf.id=f.idcliente limit 1) as fe,"
                . "(select piva from clienti_fornitori  cf where cf.id=f.idcliente limit 1) as piva"
                . " from fatture f where $where;";
        $rs = $db->prepare($qry);
        $rs->execute();
        $fatture = $rs->fetchAll(PDO::FETCH_ASSOC);
        foreach ($fatture as $i => $col) {
            if ($col['fe'] == '' && $col['piva'] != '') {
                $col['daticliente'] = "* " . $col['daticliente'];
                $fatture[$i] = $col;
            }
        }
        //$fatture = $dati->richiamaWheredata($where);

        die('{"dati" : ' . json_encode($fatture) . '}');
        break;

    case "submitformfattura":
        $tabella = "fatture";
        /* progressivo a seconda del tipo di fattura */
        $tipo = $_POST['tipo'];
        /* controllo data attuale per numerazione */
        $dataarr = explode("-", $_POST['data']);
        $annocontrollo = $dataarr[0];
        $data = $_POST['data'];
        $sql = $db->prepare("SELECT MAX(data) as datadb, YEAR(MAX(data)) as annodb FROM $tabella WHERE YEAR(data) = ? AND tipo = ? ORDER BY data DESC LIMIT 1");
        $sql->execute(array($annocontrollo, $tipo));
        $res = $sql->fetch();
        $datadbcontrollo = $res['datadb'];
        $annodb = $res['annodb'];
        if ($datadbcontrollo > $data && $annodb <= $annocontrollo) {
            die('{"msg" : "ko", "msgko" : "Errore! Data fattura antecedente ad una fattura già emessa"}');
        }
        /**/
        if ($tipo == 0 || $tipo == 3) {
            $sql = $db->prepare("SELECT MAX(numero) as maxnum FROM $tabella WHERE (tipo = ? || tipo = ?) AND YEAR(data) =?");
            $sql->execute(array(0, 3, $annocontrollo));
            $_POST['numero'] = $sql->fetchColumn() + 1;
        } else if ($tipo == 5 || $tipo == 6) {
            $sql = $db->prepare("SELECT MAX(numero) as maxnum FROM $tabella WHERE (tipo = ? || tipo = ?) AND YEAR(data) =?");
            $sql->execute(array(5, 6, $annocontrollo));
            $_POST['numero'] = $sql->fetchColumn() + 1;
        } else {
            $sql = $db->prepare("SELECT MAX(numero) as maxnum FROM $tabella WHERE tipo = ? AND YEAR(data) =?");
            $sql->execute(array($tipo, $annocontrollo));
            $_POST['numero'] = $sql->fetchColumn() + 1;
        }

        /* scadenze */
//        if ($tipo < 3 || $tipo == 4) {
        /* scadenze della fattura */
        for ($s = 0; $s < count($_POST['datascadenza']); $s++) {
            if ($_POST['datascadenza'][$s] != "") {
                $dataarr = explode("/", $_POST['datascadenza'][$s]);
                $datadb = $dataarr[2] . "-" . $dataarr[1] . "-" . $dataarr[0];

                $scadenze[$s] = array("datascadenza" => $datadb, "importoscadenza" => $_POST['importoscadenza'][$s], "notescadenza" => $_POST['notescadenza'][$s]);
            } else {
                
            }
        }
        array_filter($scadenze, 'strlen'); // controllo array vuoto
//        }

        /* nome metodo di pagamento */
        $idmetodo = $_POST['idpagamento'];
        $sql = $db->prepare("SELECT nome FROM metodi_pagamento WHERE id = ?");
        $sql->execute(array($idmetodo));
        $_POST['metodopagamento'] = $sql->fetchColumn();
        /**/

        /* preventivi collegati */
        if ($_POST['idprevfatt'] > 0) {
            for ($p = 0; $p < count($_POST['idprevfatt']); $p++) {
                $sql = $db->prepare("UPDATE preventivi SET stato = ? WHERE id = ?");
                $sql->execute(array(4, $_POST['idprevfatt'][$p]));
            }
        }
        /**/
        /* ddt collegati */
        if ($_POST['idddt'] > 0) {
            for ($d = 0; $d < count($_POST['idddt']); $d++) {
                $sql = $db->prepare("UPDATE ddt SET stato = ? WHERE id = ?");
                $sql->execute(array(4, $_POST['idddt'][$d]));
            }
        }
        /**/
        /* ddt collegati */
        if ($_POST['idordine'] > 0) {
            for ($o = 0; $o < count($_POST['idordine']); $o++) {
                $sql = $db->prepare("UPDATE ordini SET stato = ? WHERE id = ?");
                $sql->execute(array(1, $_POST['idordine'][$o]));
            }
        }

        $fatture = new fatture($id, $tabella);
        /* voci della fattura */
        for ($i = 0; $i < count($_POST['descrizione']); $i++) {
//            if ($_POST['descrizione'][$i] != "" && $_POST['qta'][$i] != "") {
            if ($_POST['um'] == "") {
                $_POST['um'] = "N";
            }
            if ($_POST['codice_iva'][$i] != "") {
                $_POST['iva'][$i] = 0;
            }
            $voci[$i] = array("nome" => $_POST['nome'][$i], "descrizione" => $_POST['descrizione'][$i], "um" => $_POST['um'][$i], "qta" => $_POST['qta'][$i], "importo" => $_POST['importo'][$i],
                "iva" => $_POST['iva'][$i], "sconto" => $_POST['sconto'][$i], "idprev" => $_POST['idprev'][$i], "idddt" => $_POST['idddt'][$i], "idordine" => $_POST['idordine'][$i], "idcomm" => $_POST['idcomm'][$i],
                "idvocecomm" => $_POST['idvocecomm'][$i], "idfatt" => $_POST['idfatt'][$i], "idprofit" => $_POST['idprofit'][$i], "ordine" => $i,
                "codice_iva" => $_POST['codice_iva'][$i]);
            /* setto fatturato voci commessa */
            if ($_POST['idvocecomm'][$i] > 0) {
                $sql = $db->prepare("UPDATE commesse_voci SET statovoce=? WHERE id = ?");
                $sql->execute(array('1', $_POST['idvocecomm'][$i]));
            } else {
                
            }
            /**/
//            } else {
//                
//            }
        }
        array_filter($voci, 'strlen'); // controllo array vuoto

        /* tolgo dall'array submit e post che non mi servono */
        unset($_POST['submit'], $_POST['nome'], $_POST['descrizione'], $_POST['um'], $_POST['qta'], $_POST['importo'], $_POST['iva'], $_POST['codice_iva'], $_POST['sconto'], $_POST['idprevfatt'], $_POST['idcommfatt'], $_POST['idprev'], $_POST['idddt'], $_POST['idordine'], $_POST['idcomm'], $_POST['idvocecomm'], $_POST['idfatt'], $_POST['idprofit'], $_POST['datascadenza'], $_POST['importoscadenza'], $_POST['notescadenza'], $_POST['modificascadenze']);

        foreach ($_POST as $k => $v) {
            $campi[] = $k;
            $valori[] = $v;
        }
        /* funzione aggiungi */
        $statofatt = $_POST['stato'];
        $fatture->aggiungi($campi, $valori, $voci, $scadenze, $statofatt, $tipo);

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
        <script type="text/javascript" src="./js/functions-fatture.js"></script>

        <link type="text/css" rel="stylesheet" href="./js/jsgrid-1.5.1/jsgrid.min.css" />
        <link type="text/css" rel="stylesheet" href="./js/jsgrid-1.5.1/jsgrid-theme.min.css" />

        <script type="text/javascript" src="./js/jsgrid-1.5.1/jsgrid.min.js"></script>
        <script type="text/javascript" src="./js/jsgrid-1.5.1/i18n/jsgrid-it.js"></script>
        <!-- lingua italiana datepicker -->
        <script src="./js/jquery-ui-1.11.4/datepicker-it.js"></script>

        <script type="text/javascript">
            $(document).ready(function () {

<?php if ($op == "archivio") { ?>
                    mostraFatture('1');
<?php } else if ($op == "acquisto") { ?>
                    mostraFatture('2', '');
<?php } else if ($op == "stampe") { ?>
                    mostraStampe();
<?php } else if ($op == "esportafe") { ?>
                    mostraEsportaFE();
<?php } else if ($op == "esporta") { ?>
                    mostraEsporta();
<?php } else { ?>
                    mostraFatture('', '');
<?php } ?>
            });
        </script>


    </head>
    <body class="colormodulo">
        <?php include './components/top.php'; ?>
        <?php if (moduloattivo("fatture") > 0) { ?>
            <div class="barra_submenu sizing">
                <li class="box_submenu sizing"><a href="fatture.php?op=fatture"><i class="fa fa-file-text-o fa-lg" aria-hidden="true"></i> Fatture</a></li>
                <li class="box_submenu sizing"><a href="fatture.php?op=archivio"><i class="fa fa-file-archive-o fa-lg" aria-hidden="true"></i> Fatture altri anni / pagate</a></li>
                <li class="box_submenu sizing"><a href="fatture.php?op=acquisto"><i class="fa fa-file-text-o fa-lg" aria-hidden="true"></i> Fatture acquisto</a></li>
                <li class="box_submenu sizing"><a href="fatture.php?op=stampe"><i class="fa fa-print fa-lg" aria-hidden="true"></i> Stampe Excel&reg;</a></li>
                <li class="box_submenu sizing"><a href="fatture.php?op=esporta"><i class="fa fa-file-pdf-o fa-lg" aria-hidden="true"></i> Stampe PDF</a></li>
                <li class="box_submenu sizing"><a href="fatture.php?op=esportafe"><i class="fa fa-file-code-o fa-lg" aria-hidden="true"></i> Esporta XML</a></li>
            </div>
            <div class="content sizing">
                <div class="barra_op sizing">                                               
                    <?php
                    if ($op == "esporta") {
                        ?>
                        Esportazione in Adobe PDF
                        <?php
                    } else if ($op == "esportafe") {
                        ?>
                        Esportazione XML per fatturazione elettronica
                        <?php
                    } else if ($op == "acquisto") {
                        ?>
                        <div class="bottone sizing"><a href="javascript:;" onclick="aggiungiAcquisto();"><i class="fa fa-user-plus fa-lg" aria-hidden="true"></i> Aggiungi fattura acquisto</a></div>
                    <?php } else if ($op == "stampe") {
                        ?>
                        Esportazione in Microsoft Excel&reg; 
                    <?php } else {
                        ?>
                        <div class="bottone sizing"><a href="javascript:;" onclick="aggiungi();"><i class="fa fa-user-plus fa-lg" aria-hidden="true"></i> Aggiungi fattura</a></div> 
                    <?php } ?>
                    <div id="messaggio" class="messaggiook">Operazione avvenuta con successo</div>

                </div>
                <div class="showcont sizing">
                    <!-- qui contenuto del mostra anagrafiche e aggiungi anagrafiche --> 
                </div>
            </div>
        <?php } ?>
    </body>
</html>