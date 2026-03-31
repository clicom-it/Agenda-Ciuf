<?php

ob_start('ob_gzhandler');
include './library/connessione.php';
include './library/controllo.php';
include './library/config.php';
include './library/functions.php';
include './library/basic.class.php';
/* libreria del modulo */
include './library/dipendenti.class.php';
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

    case 'tuttiformazione':
        $tabella = "formazione";
        $dati = new dipendenti($id, $tabella);
        $where = "id > 0 order by ordine;";
        $messaggi = $dati->richiamaWhere($where);
        foreach ($messaggi as $i => $mess) {
            $ruolo = getDati("formazione_ruolo", "where idformazione=" . $mess['id']);
            $ruoli = Array();
            foreach ($ruolo as $r) {
                $nome_ruolo = getRuoloUtente($r['ruolo']);
                $ruoli[] = $nome_ruolo;
            }
            $messaggi[$i]['ruoli'] = join(" - ", $ruoli);
        }
        die('{"dati" : ' . json_encode($messaggi) . '}');
        break;

    case 'submitformformazione':
        $qry = "select MAX(ordine) as max_ordine from formazione limit 1;";
        $rs = $db->prepare($qry);
        $rs->execute();
        $max = (int) $rs->fetchColumn();
        $max++;
        $campi = Array('ordine', 'attivo');
        $valori = Array($max, 1);
        $tabella = "formazione";
        $dati = new dipendenti($id, $tabella);
        $ruolo = $_POST['ruolo'];
        unset($_POST['submit'], $_POST['id'], $_POST['ruolo']);
        foreach ($_POST as $k => $v) {
            $campi[] = $k;
            $valori[] = $v;
        }
        $dati->aggiungi($campi, $valori);
        $idformazione = $db->lastinsertId();
        $qry = "insert into formazione_ruolo (idformazione, ruolo) values (?,?);";
        $rs = $db->prepare($qry);
        foreach ($ruolo as $r) {
            $valori = Array($idformazione, $r);
            $rs->execute($valori);
        }
        die('{"msg" : "ok"}');
        break;

    case 'editFormazione':
        $tabella = "formazione";
        $id = $_POST['id'];
        $dati = new dipendenti($id, $tabella);
        $ruolo = $_POST['ruolo'];
        unset($_POST['submit'], $_POST['ruolo']);
        foreach ($_POST as $k => $v) {
            $campi[] = $k;
            $valori[] = $v;
        }
        //var_dump($campi, $valori);
        if (count($ruolo) > 0) {
            $qry = "delete from formazione_ruolo where idformazione=$id;";
            $db->exec($qry);
            $qry = "insert into formazione_ruolo (idformazione, ruolo) values (?,?);";
            $rs = $db->prepare($qry);
            foreach ($ruolo as $r) {
                $valori_ = Array($id, $r);
                $rs->execute($valori_);
            }
        }
        $clientefornitore = $dati->aggiorna($campi, $valori);
        die('{"msg": "ok"}');
        break;

    case "delFormazione":
        $tabella = "formazione";
        $id = $_POST['id'];
        $dati = new dipendenti($id, $tabella);
        $dipendenti = $dati->cancella();
        die('{"msg": "ok"}');
        break;

    case 'sortFormazione':
        $arrId = explode(",", $_POST['rows']);
        foreach ($arrId as $k => $id) {
            $qry = "update formazione set ordine=$k where id=$id;";
            $db->exec($qry);
        }
        die('{"msg": "ok"}');
        break;

    case "attivaFormazione":
        $qry = "update formazione set attivo=? where id=?;";
        $rs = $db->prepare($qry);
        $valori = Array($_POST['attivo'], $_POST['id']);
        $rs->execute($valori);
        die('{"msg": "ok"}');
        break;

    case 'mostraCorsi':
        $tabella = "formazione_corsi";
        $dati = new dipendenti($id, $tabella);
        $where = "idformazione = {$_POST['idformazione']} order by ordine;";
        $messaggi = $dati->richiamaWhere($where);
        die('{"dati" : ' . json_encode($messaggi) . '}');
        break;

    case 'addCorso':
        $tabella = "formazione_corsi";
        $qry = "select MAX(ordine) from $tabella where idformazione={$_POST['idformazione']} limit 1;";
        $rs = $db->prepare($qry);
        $rs->execute();
        $max = (int) $rs->fetchColumn();
        $max++;
        $campi = Array('ordine', 'attivo');
        $valori = Array($max, 1);
        $dati = new dipendenti($id, $tabella);
        unset($_POST['submit'], $_POST['id']);
        foreach ($_POST as $k => $v) {
            $campi[] = $k;
            $valori[] = $v;
        }
        $dati->aggiungi($campi, $valori);
        $id = $db->lastinsertID();
        if ($_POST['video'] != "") {
            $path_dest = $_SERVER['DOCUMENT_ROOT'] . "/" . FOLDER_FORMAZIONE . "/$id/";
            $path_src = $_SERVER['DOCUMENT_ROOT'] . "/" . FOLDER_TMP . "/";
            if (!is_dir($path_dest)) {
                mkdir($path_dest);
            }
            if (copy($path_src . $_POST['video'], $path_dest . $_POST['video'])) {
                unlink($path_src . $_POST['video']);
            }
        }
        die('{"msg" : "ok"}');
        break;

    case 'delCorso':
        $tabella = "formazione_corsi";
        $id = $_POST['id'];
        $dati = new dipendenti($id, $tabella);
        $corso = $dati->richiamaWhere("id=$id limit 1;");
        $dipendenti = $dati->cancella();
        if ($corso[0]['video'] != "") {
            unlink($_SERVER['DOCUMENT_ROOT'] . "/" . FOLDER_FORMAZIONE . "/$id/" . $corso[0]['video']);
        }
        die('{"msg": "ok"}');
        break;

    case 'editCorso':
        $tabella = "formazione_corsi";
        $id = $_POST['id'];
        $dati = new dipendenti($id, $tabella);
        $corso = $dati->richiamaWhere("id=$id limit 1;");
        unset($_POST['submit']);
        foreach ($_POST as $k => $v) {
            $campi[] = $k;
            $valori[] = $v;
        }
        $clientefornitore = $dati->aggiorna($campi, $valori);
        if ($corso['video'] != $_POST['video'] && $_POST['video'] != '') {
            $path_src = $_SERVER['DOCUMENT_ROOT'] . "/" . FOLDER_TMP . "/" . $_POST['video'];
            $path_dest = $_SERVER['DOCUMENT_ROOT'] . "/" . FOLDER_FORMAZIONE . "/$id/" . $_POST['video'];
            if (file_exists($path_src)) {
                if (copy($path_src, $path_dest)) {
                    unlink($path_src);
                    unlink($_SERVER['DOCUMENT_ROOT'] . "/" . FOLDER_FORMAZIONE . "/$id/" . $_POST['video']);
                }
            }
        }
        die('{"msg": "ok"}');
        break;

    case 'sortCorsi':
        $arrId = explode(",", $_POST['rows']);
        foreach ($arrId as $k => $id) {
            $qry = "update formazione_corsi set ordine=$k where id=$id;";
            $db->exec($qry);
        }
        die('{"msg": "ok"}');
        break;

    case "attivaCorso":
        $qry = "update formazione_corsi set attivo=? where id=?;";
        $rs = $db->prepare($qry);
        $valori = Array($_POST['attivo'], $_POST['id']);
        $rs->execute($valori);
        die('{"msg": "ok"}');
        break;

    case 'mostraMedia':
        $tabella = "media_corsi";
        $dati = new dipendenti($id, $tabella);
        $where = "idcorso = {$_POST['idcorso']} order by id;";
        $messaggi = $dati->richiamaWhere($where);
        die('{"dati" : ' . json_encode($messaggi) . '}');
        break;

    case 'addMedia':
        $tabella = "media_corsi";
        $dati = new dipendenti($id, $tabella);
        unset($_POST['submit'], $_POST['id']);
        foreach ($_POST as $k => $v) {
            if ($k == 'media')
                $k = 'nomefile';
            $campi[] = $k;
            $valori[] = $v;
        }
        $fileNameParts = explode('.', $_POST['media']);
        $ext = end($fileNameParts);
        $campi[] = 'ext';
        $valori[] = $ext;
        $dati->aggiungi($campi, $valori);
        $id = $_POST['idcorso'];
        if ($_POST['media'] != "") {
            $path_dest = $_SERVER['DOCUMENT_ROOT'] . "/" . FOLDER_FORMAZIONE . "/$id/";
            $path_src = $_SERVER['DOCUMENT_ROOT'] . "/" . FOLDER_TMP . "/";
            if (!is_dir($path_dest)) {
                mkdir($path_dest);
            }
            if (copy($path_src . $_POST['media'], $path_dest . $_POST['media'])) {
                unlink($path_src . $_POST['media']);
            }
        }
        die('{"msg" : "ok"}');
        break;

    case 'delMedia':
        $tabella = "media_corsi";
        $id = $_POST['id'];
        $dati = new dipendenti($id, $tabella);
        $media = $dati->richiamaWhere("id=$id limit 1;");
        $dipendenti = $dati->cancella();
        if ($media[0]['nomefile'] != "") {
            unlink($_SERVER['DOCUMENT_ROOT'] . "/" . FOLDER_FORMAZIONE . "/$id/" . $media[0]['nomefile']);
        }
        die('{"msg": "ok"}');
        break;

    case 'editMedia':
        $tabella = "media_corsi";
        $id = $_POST['id'];
        $dati = new dipendenti($id, $tabella);
        $corso = $dati->richiamaWhere("id=$id limit 1;");
        $campi[] = 'titolo';
        $valori[] = $_POST['titolo'];
        $clientefornitore = $dati->aggiorna($campi, $valori);
        die('{"msg": "ok"}');
        break;

    case 'mostraDocsUtenti':
        $tabella = "docs_utenti";
        $dati = new dipendenti($id, $tabella);
        $where = "idutente = {$_POST['idutente']} order by id;";
        $messaggi = $dati->richiamaWhere($where);
        die('{"dati" : ' . json_encode($messaggi) . '}');
        break;

    case 'addDocsUtenti':
        $tabella = "docs_utenti";
        $dati = new dipendenti($id, $tabella);
        unset($_POST['submit'], $_POST['id']);
        foreach ($_POST as $k => $v) {
            if ($k == 'media')
                $k = 'nomefile';
            $campi[] = $k;
            $valori[] = $v;
        }
        $fileNameParts = explode('.', $_POST['media']);
        $ext = end($fileNameParts);
        $campi[] = 'ext';
        $valori[] = $ext;
        $campi[] = 'size';
        $valori[] = filesize($_SERVER['DOCUMENT_ROOT'] . "/" . FOLDER_TMP . "/" . $_POST['media']);
        $dati->aggiungi($campi, $valori);
        $id = $_POST['idutente'];
        if ($_POST['media'] != "") {
            $path_dest = $_SERVER['DOCUMENT_ROOT'] . "/" . FOLDER_UTENTI . "/$id/";
            $path_src = $_SERVER['DOCUMENT_ROOT'] . "/" . FOLDER_TMP . "/";
            if (!is_dir($path_dest)) {
                mkdir($path_dest);
            }
            if (copy($path_src . $_POST['media'], $path_dest . $_POST['media'])) {
                unlink($path_src . $_POST['media']);
            }
        }
        die('{"msg" : "ok"}');
        break;

    case 'delDocsUtente':
        $tabella = "docs_utenti";
        $id = $_POST['id'];
        $dati = new dipendenti($id, $tabella);
        $media = $dati->richiamaWhere("id=$id limit 1;");
        $dipendenti = $dati->cancella();
        if ($media[0]['nomefile'] != "") {
            unlink($_SERVER['DOCUMENT_ROOT'] . "/" . FOLDER_UTENTI . "/$id/" . $media[0]['nomefile']);
        }
        die('{"msg": "ok"}');
        break;

    case 'editDocsUtenti':
        $tabella = "docs_utenti";
        $id = $_POST['id'];
        $dati = new dipendenti($id, $tabella);
        $corso = $dati->richiamaWhere("id=$id limit 1;");
        $campi[] = 'titolo';
        $valori[] = $_POST['titolo'];
        $clientefornitore = $dati->aggiorna($campi, $valori);
        die('{"msg": "ok"}');
        break;
## CARTELLE ATELIER ###
    case 'addCartellaAtelier':
        $idatelier = $_POST['idatelier_media'];
        $idfather = $_POST['idfather'];
        $idatelier_new = dipendenti::addCartella($idatelier, $idfather);
        die('{"msg": "ok", "idatelier":' . $idatelier_new . ', "titolo": ' . json_encode($_POST['titolo'], JSON_INVALID_UTF8_IGNORE) . '}');
        break;

    case 'listFoldersAtelier':
        $idatelier = $_POST['idatelier'];
        $idfather = $_POST['idfather'];
        $folders = dipendenti::listCartelle($idatelier, $idfather, 1);
        $files = dipendenti::listCartelle($idatelier, $idfather, 0);
        die('{"folders": ' . json_encode($folders, JSON_INVALID_UTF8_IGNORE) . ', "files": ' . json_encode($files, JSON_INVALID_UTF8_IGNORE) . '}');
        break;

    case 'addDocsAtelier':
        $tabella = "docs_atelier";
        $dati = new dipendenti($id, $tabella);
        $idatelier = $_POST['idatelier_media'];
        $idfather = $_POST['idfather'];
        $qry = "select MAX(ordine) from docs_atelier where idatelier=$idatelier and dir=0 and idfather=$idfather limit 1;";
        $rs = $db->prepare($qry);
        $rs->execute();
        $ordine = (int) $rs->fetchColumn();
        $ordine++;
        unset($_POST['submit'], $_POST['id']);
        foreach ($_POST as $k => $v) {
            if ($k == 'media')
                $k = 'nomefile';
            if ($k == 'idatelier_media')
                $k = 'idatelier';
            $campi[] = $k;
            $valori[] = $v;
        }
        $fileNameParts = explode('.', $_POST['media']);
        $ext = end($fileNameParts);
        $campi[] = 'ext';
        $valori[] = $ext;
        $campi[] = 'size';
        $valori[] = filesize($_SERVER['DOCUMENT_ROOT'] . "/" . FOLDER_TMP . "/" . $_POST['media']);
        $campi[] = "ordine";
        $valori[] = $ordine;
        $dati->aggiungi($campi, $valori);
        $id = $_POST['idfather'];
        if ($_POST['media'] != "") {
            $path_dest0 = $_SERVER['DOCUMENT_ROOT'] . "/" . FOLDER_UTENTI . "/$idatelier/";
            $path_dest = $_SERVER['DOCUMENT_ROOT'] . "/" . FOLDER_UTENTI . "/$idatelier/$id/";
            $path_src = $_SERVER['DOCUMENT_ROOT'] . "/" . FOLDER_TMP . "/";
            if (!is_dir($path_dest0)) {                
                mkdir($path_dest0);
            }
            if (!is_dir($path_dest)) {                
                mkdir($path_dest);
            }
            if (copy($path_src . $_POST['media'], $path_dest . $_POST['media'])) {
                unlink($path_src . $_POST['media']);
            }
        }
        $folders = dipendenti::listCartelle($idatelier, $idfather, 1);
        $files = dipendenti::listCartelle($idatelier, $idfather, 0);
        die('{"folders": ' . json_encode($folders, JSON_INVALID_UTF8_IGNORE) . ', "files": ' . json_encode($files, JSON_INVALID_UTF8_IGNORE) . '}');
        break;

    case 'renameCartellaAtelier':
        $tabella = "docs_atelier";
        $id = $_POST['idfolder'];
        $dati = new dipendenti($id, $tabella);
        $corso = $dati->richiamaWhere("id=$id limit 1;");
        $campi[] = 'titolo';
        $valori[] = $_POST['titolo'];
        $clientefornitore = $dati->aggiorna($campi, $valori);
        die('{"msg": "ok"}');
        break;

    case 'deleteCartellaAtelier':
        $tabella = "docs_atelier";
        $id = $_POST['idfolder'];
        $dati = new dipendenti($id, $tabella);
        $media = $dati->richiamaWhere("id=$id limit 1;");
        $dipendenti = $dati->cancella();
        if ($media[0]['nomefile'] != "") {
            unlink($_SERVER['DOCUMENT_ROOT'] . "/" . FOLDER_UTENTI . "/" . $media[0]['idatelier'] . "/" . $media[0]['idfather'] . "/" . $media[0]['nomefile']);
        }
        die('{"msg": "ok"}');
        break;

    case 'ordinaFolderAtelier':
        foreach ($_POST['riga'] as $i => $id) {
            $qry = "update docs_atelier set ordine=$i where id=$id;";
            $db->exec($qry);
        }
        die('{"msg": "ok"}');
        break;
## END CARTELLE ##
    case 'mostraDomande':
        $tabella = "domande_test";
        $dati = new dipendenti($id, $tabella);
        if ($_POST['idlezione'] > 0) {
            $where = "idlezione = {$_POST['idlezione']} order by ordine;";
        } else {
            $where = "idformazione = {$_POST['idformazione']} order by ordine;";
        }
        $messaggi = $dati->richiamaWhere($where);
        die('{"dati" : ' . json_encode($messaggi) . '}');
        break;

    case 'addDomanda':
        $tabella = "domande_test";
        if ($_POST['idlezione'] > 0) {
            $qry = "select MAX(ordine) from $tabella where idlezione={$_POST['idlezione']} limit 1;";
        } else {
            $qry = "select MAX(ordine) from $tabella where idformazione={$_POST['idformazione']} limit 1;";
        }
        $rs = $db->prepare($qry);
        $rs->execute();
        $max = (int) $rs->fetchColumn();
        $max++;
        $campi = Array('ordine');
        $valori = Array($max);
        $dati = new dipendenti($id, $tabella);
        unset($_POST['submit'], $_POST['id']);
        foreach ($_POST as $k => $v) {
            $campi[] = $k;
            $valori[] = $v;
        }
        $dati->aggiungi($campi, $valori);
        $id = $db->lastinsertID();
        die('{"msg" : "ok"}');
        break;

    case 'delDomanda':
        $tabella = "domande_test";
        $id = $_POST['id'];
        $dati = new dipendenti($id, $tabella);
        $corso = $dati->richiamaWhere("id=$id limit 1;");
        $dipendenti = $dati->cancella();
        die('{"msg": "ok"}');
        break;

    case 'sortDomande':
        $tabella = "domande_test";
        $arrId = explode(",", $_POST['rows']);
        foreach ($arrId as $k => $id) {
            $qry = "update $tabella set ordine=$k where id=$id;";
            $db->exec($qry);
        }
        die('{"msg": "ok"}');
        break;

    case 'editDomanda':
        $tabella = "domande_test";
        $id = $_POST['id'];
        $dati = new dipendenti($id, $tabella);
        $corso = $dati->richiamaWhere("id=$id limit 1;");
        unset($_POST['submit']);
        foreach ($_POST as $k => $v) {
            $campi[] = $k;
            $valori[] = $v;
        }
        $clientefornitore = $dati->aggiorna($campi, $valori);
        die('{"msg": "ok"}');
        break;

    case 'mostraRisp':
        $tabella = "risposte_test";
        $dati = new dipendenti($id, $tabella);
        $where = "iddomanda = {$_POST['iddomanda']} order by ordine;";
        $messaggi = $dati->richiamaWhere($where);
        foreach ($messaggi as $k => $risp) {
            if ($risp['corretta'] == 0) {
                $risp['corretta'] = false;
            } else {
                $risp['corretta'] = true;
            }
            $messaggi[$k] = $risp;
        }
        die('{"dati" : ' . json_encode($messaggi) . '}');
        break;

    case 'addRisp':
        $tabella = "risposte_test";
        $qry = "select MAX(ordine) from $tabella where iddomanda={$_POST['iddomanda']} limit 1;";
        $rs = $db->prepare($qry);
        $rs->execute();
        $max = (int) $rs->fetchColumn();
        $max++;
        $campi = Array('ordine');
        $valori = Array($max);
        $dati = new dipendenti($id, $tabella);
        unset($_POST['submit'], $_POST['id']);
        foreach ($_POST as $k => $v) {
            if ($k == 'corretta') {
                if ($v == 'on' || $v == 'true') {
                    $v = 1;
                } else {
                    $v = 0;
                }
            }
            $campi[] = $k;
            $valori[] = $v;
        }
        $dati->aggiungi($campi, $valori);
        $id = $db->lastinsertID();
        die('{"msg" : "ok"}');
        break;

    case 'delRisp':
        $tabella = "risposte_test";
        $id = $_POST['id'];
        $dati = new dipendenti($id, $tabella);
        $corso = $dati->richiamaWhere("id=$id limit 1;");
        $dipendenti = $dati->cancella();
        die('{"msg": "ok"}');
        break;

    case 'sortRisp':
        $tabella = "risposte_test";
        $arrId = explode(",", $_POST['rows']);
        foreach ($arrId as $k => $id) {
            $qry = "update $tabella set ordine=$k where id=$id;";
            $db->exec($qry);
        }
        die('{"msg": "ok"}');
        break;

    case 'editRisp':
        $tabella = "risposte_test";
        $id = $_POST['id'];
        $dati = new dipendenti($id, $tabella);
        $corso = $dati->richiamaWhere("id=$id limit 1;");
        unset($_POST['submit']);
        if ($_POST['corretta'] == 'false') {
            $_POST['corretta'] = 0;
        } else {
            $_POST['corretta'] = 1;
        }
        foreach ($_POST as $k => $v) {
            $campi[] = $k;
            $valori[] = $v;
        }
        $clientefornitore = $dati->aggiorna($campi, $valori);
        die('{"msg": "ok"}');
        break;
}