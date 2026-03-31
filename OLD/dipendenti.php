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
    case "editformdipendenti":
        $tabella = "utenti";
        $id = $_POST['id'];
        $dati = new dipendenti($id, $tabella);

        unset($_POST['submit']);

        if ($_POST['password'] == "") {
            unset($_POST['password']);
        } else {
            $operazione = "Modifica password";
            logAccesso($_SESSION["id"], $operazione, $id);
        }

        $_POST['ore'] = 1;

        $_POST['aperture'] = join(",", $_POST['apert']);

        unset($_POST['submit'], $_POST['apert']);
        if (isset($_POST['village'])) {
            if ($_POST['village'] == '') {
                $_POST['village'] = 0;
            }
        }
        if ($_POST['solo_sartoria'] == '') {
            $_POST['solo_sartoria'] = 0;
        }

        if ($_POST['online'] == '') {
            $_POST['online'] = 0;
        }
        if ($_POST['non_gestito'] == '') {
            $_POST['non_gestito'] = 0;
        }
        if ($_POST['no_mrage'] == '') {
            $_POST['no_mrage'] = 0;
        }
        if ($_POST['atelier_all'] == '') {
            $_POST['atelier_all'] = 0;
        }
        if ($_POST['data_apertura'] != '') {
            $_POST['data_apertura'] = formatDateDb($_POST['data_apertura']);
        } else {
            $_POST['data_apertura'] = null;
        }
        if ($_POST['chiuso_dal'] != '') {
            $_POST['chiuso_dal'] = formatDateDb($_POST['chiuso_dal']);
        } else {
            $_POST['chiuso_dal'] = null;
        }
        if ($_POST['chiuso_al'] != '') {
            $_POST['chiuso_al'] = formatDateDb($_POST['chiuso_al']);
        } else {
            $_POST['chiuso_al'] = null;
        }
        if ($_POST['data_nascita'] != '') {
            $_POST['data_nascita'] = formatDateDb($_POST['data_nascita']);
        } else {
            $_POST['data_nascita'] = null;
        }
        $village = $_POST['village'];
        unset($_POST['village']);
        if ($_POST["livello"] == '5' && $village != '') {//Atelier
            $idatelier = $_POST['id'];
            $qry = "insert into utenti_dati_atelier (idatelier, village) values "
                    . "(?,?) "
                    . "ON DUPLICATE KEY UPDATE "
                    . "village = VALUES(village);";
            $rs_atelier = $db->prepare($qry);
            $valori = [$idatelier, $village];
            $rs_atelier->execute($valori);
        }
        if ($_SESSION["livello"] > 1) {
            unset($_POST['solo_sartoria']);
            unset($_POST['non_gestito'], $_POST['no_mrage'], $_POST['atelier_all']);
        }
        $addetti = Array();
        for ($i = 0; $i <= 6; $i++) {
            $addetti[$i] = intval($_POST['addetti' . $i]);
            unset($_POST['addetti' . $i]);
        }
        if (isset($_POST['ore_settimana'])) {
            $qry = "select idutente from utenti_dipendenti where idutente=? limit 1;";
            $rs = $db->prepare($qry);
            $valori = Array($id);
            $rs->execute($valori);
            $ore_settimana = $_POST['ore_settimana'];
            $stipendio_netto = $_POST['stipendio_netto'];
            $idatelier_sede = $_POST['idatelier_sede'];
            $dati_retribuzione = Array();
            foreach ($_POST['data_inizio_r'] as $i => $data_inizio_r) {
                $dati_retribuzione[] = Array(
                    'data_inizio_r' => $data_inizio_r,
                    'retribuzione' => $_POST['retribuzione'][$i],
                    'data_fine_r' => $_POST['data_fine_r'][$i]
                );
            }
            if ($rs->RowCount() > 0) {
                $qry = "update utenti_dipendenti set ore_settimana=?, stipendio_netto=?, dati_retribuzione=?, idatelier_sede=? where idutente=?;";
                $valori = Array($ore_settimana, $stipendio_netto, json_encode($dati_retribuzione), $idatelier_sede, $id);
            } else {
                $qry = "insert into utenti_dipendenti (idutente, ore_settimana, stipendio_netto, dati_retribuzione, idatelier_sede) values (?,?,?,?,?);";
                $valori = Array($id, $ore_settimana, $stipendio_netto, json_encode($dati_retribuzione), $idatelier_sede);
            }
            //var_dump($valori);
            //die($qry);
            $rs = $db->prepare($qry);
            $rs->execute($valori);
            //var_dump($dati_retribuzione);
            //die();
            unset($_POST['ore_settimana'], $_POST['stipendio_netto'], $_POST['data_inizio_r'], $_POST['retribuzione'], $_POST['data_fine_r'], $_POST['idatelier_sede']);
        }
        //var_dump($_POST);
        $valori = Array();
        foreach ($_POST as $k => $v) {
            if ($k == "email") {
                $sql = $db->prepare("SELECT * FROM $tabella WHERE email = ? AND email != '' AND id != " . $_POST['id'] . "");
                $sql->execute(array($v));
                if ($sql->rowCount() > 0) {
                    die('{"msg": "ko", "msgko" : "E-mail già utilizzata"}');
                }
            }
            if ($k == "username" && false) {
                //die("SELECT * FROM $tabella WHERE username = '$v' AND id != " . $_POST['id'] . "");
                $sql = $db->prepare("SELECT * FROM $tabella WHERE username = ? AND id != " . $_POST['id'] . "");
                $sql->execute(array($v));
                if ($sql->rowCount() > 0) {
                    die('{"msg": "ko", "msgko" : "Username già utilizzato"}');
                }
            }
            $campi[] = $k;
            $valori[] = $v;
        }
        //var_dump($campi, $valori);
        $clientefornitore = $dati->aggiorna($campi, $valori);
        $qry = "delete from atelier_addetti where idatelier=$id;";
        $db->exec($qry);
        $qry = "insert into atelier_addetti (idatelier, addetti0, addetti1, addetti2, addetti3, addetti4, addetti5, addetti6) values (?,?,?,?,?,?,?,?);";
        $rs = $db->prepare($qry);
        $valori = Array($id);
        foreach ($addetti as $addetto) {
            $valori[] = $addetto;
        }
        $rs->execute($valori);
        die('{"msg": "ok"}');
        break;

    case "richiamadipendente":
        $tabella = "utenti";
        $id = $_POST['id'];
        $dati = new dipendenti($id, $tabella);
        $dipendenti = $dati->richiama();
        if ($dipendenti[0]['data_apertura'] != "") {
            $dipendenti[0]['data_apertura'] = formatDate($dipendenti[0]['data_apertura']);
        }
        if ($dipendenti[0]['chiuso_dal'] != "") {
            $dipendenti[0]['chiuso_dal'] = formatDate($dipendenti[0]['chiuso_dal']);
        }
        if ($dipendenti[0]['chiuso_al'] != "") {
            $dipendenti[0]['chiuso_al'] = formatDate($dipendenti[0]['chiuso_al']);
        }
        if ($dipendenti[0]['data_nascita'] != "") {
            $dipendenti[0]['data_nascita'] = formatDate($dipendenti[0]['data_nascita']);
        }
        $qry = "select * from atelier_addetti where idatelier=? limit 1;";
        $rs = $db->prepare($qry);
        $valori = Array($id);
        $rs->execute($valori);
        $col = $rs->fetch(PDO::FETCH_ASSOC);
        for ($i = 0; $i <= 6; $i++) {
            $dipendenti[0]['addetti' . $i] = $col['addetti' . $i];
        }
        $qry = "select * from utenti_dipendenti where idutente=?;";
        $rs = $db->prepare($qry);
        $valori = Array($id);
        $rs->execute($valori);
        $cols = $rs->fetchAll(PDO::FETCH_ASSOC);
        foreach ($cols as $i => $col) {
            $col['dati_retribuzione'] = json_decode($col['dati_retribuzione']);
            $cols[$i] = $col;
        }
        $dipendenti[0]['retribuzione'] = $cols;
        $qry = "select village from utenti_dati_atelier where idatelier=? limit 1;";
        $rs = $db->prepare($qry);
        $valori = Array($id);
        $rs->execute($valori);
        $village = (int) $rs->fetchColumn();
        $dipendenti[0]['village'] = $village;
        die('{"valori" : ' . json_encode($dipendenti, JSON_INVALID_UTF8_IGNORE) . '}');
        break;

    case "delete":
        $tabella = "utenti";
        $id = $_POST['id'];
        $dati = new dipendenti($id, $tabella);
        $dipendenti = $dati->cancella();
        die('{"msg": "ok"}');
        break;

    case "tuttidipendenti":
        $tabella = "utenti";
        $id = $_POST['id'];
        $dati = new dipendenti($id, $tabella);
        $tipo = $_POST['tipo'];

        if ($_SESSION["livello"] == 0 || $_SESSION["livello"] == 1 || $_SESSION['ruolo'] == CENTRALINO || $_SESSION['ruolo'] == RISORSE_UMANE) {
            $where = "livello = '" . $tipo . "'";
        } else if ($_SESSION["livello"] == 5 && $tipo == 5) {
            $where = "id = '" . $_SESSION['id'] . "'";
        } else {
            $where = "livello = '" . $tipo . "' AND idatelier = '" . $_SESSION['id'] . "'";
        }

        $dipendenti = $dati->richiamaWhere($where);

        for ($i = 0; $i < count($dipendenti); $i++) {

            $centri = getDati("centri_costo", "WHERE id=" . $dipendenti[$i]['centro_costo'] . "");
            foreach ($centri as $centrid) {
                $dipendenti[$i]['nomecentro'] .= $centrid['nome'];
            }
            $qry = "select nominativo from utenti where id=(select idatelier_sede from utenti_dipendenti ud where ud.idutente=? limit 1) limit 1;";
            $rs = $db->prepare($qry);
            $rs->execute(Array($dipendenti[$i]['id']));
            $nomeatelier = $rs->fetchColumn();
            $dipendenti[$i]['nomeatelier'] = "$nomeatelier";
            $dipendenti[$i]['ruolo_txt'] = getRuoloUtente($dipendenti[$i]['ruolo']);
            $dipendenti[$i]['atelier_all_str'] = ($dipendenti[$i]['atelier_all'] == 1 ? 'si' : 'no');
            if ($tipo == '5') {
                $dipendenti[$i]['ruolo_txt'] = '';
            }
        }

        die('{"dati" : ' . json_encode($dipendenti) . '}');
        break;

    case "tuttidipendentiDip":
        $tabella = "dipendenti_atelier";
        $id = $_POST['idatelier'];
        $dati = new dipendenti($id, $tabella);
        $where = " idatelier = '" . $id . "'";
        $dipendenti = $dati->richiamaWhere($where);
        die('{"dati" : ' . json_encode($dipendenti) . '}');
        break;

    case "tuttidipendentiGest":
        $tabella = "utenti_collegati";
        $dati = new dipendenti($id, $tabella);
        $idutente = $_POST['idutente'];
        $where = " idformatore = " . $idutente;
        $dipendenti = $dati->richiamaWhere($where);
        $tabella = "utenti";
        $dati = new dipendenti($id, $tabella);
        $where = " livello='3' order by cognome;";
        $users = $dati->richiamaWhere($where);
        $utenti = Array();
        foreach ($users as $k => $user) {
            $utenti[] = Array('id' => $user['id'], 'cognome_nome' => $user['cognome'] . ' ' . $user['nome']);
        }
        die('{"dati" : ' . json_encode($dipendenti) . ', "utenti": ' . json_encode($utenti, JSON_INVALID_UTF8_IGNORE) . '}');
        break;

    case "submitformdipendenti":
        $tabella = "utenti";
        $clientifornitori = new dipendenti($id, $tabella);
        /* tolgo dall'array submit */
        unset($_POST['submit']);

        $_POST['ore'] = 1;

        if ($_POST['livello'] == 5) {
            $_POST['dipendenti'] = 1;
            $_POST['clientifornitori'] = 1;
            $_POST['statistiche'] = 1;
        }
        if ($_POST['apert']) {
            $_POST['aperture'] = join(",", $_POST['apert']);
        } else {
            $_POST['aperture'] = "";
        }

        unset($_POST['submit'], $_POST['apert']);

        /* nomeatelier */
        if ($_POST['livello'] != 5) {
            $at = getDati("utenti", "WHERE id = " . $_POST['idatelier'] . "");
            $_POST['nomeatelier'] = $at[0]['nominativo'];
        }
        if ($_POST['data_apertura'] != '') {
            $_POST['data_apertura'] = formatDateDb($_POST['data_apertura']);
        } else {
            $_POST['data_apertura'] = null;
        }
        if ($_POST['chiuso_dal'] != '') {
            $_POST['chiuso_dal'] = formatDateDb($_POST['chiuso_dal']);
        } else {
            $_POST['chiuso_dal'] = null;
        }
        if ($_POST['chiuso_al'] != '') {
            $_POST['chiuso_al'] = formatDateDb($_POST['chiuso_al']);
        } else {
            $_POST['chiuso_al'] = null;
        }
        if ($_POST['data_nascita'] != '') {
            $_POST['data_nascita'] = formatDateDb($_POST['data_nascita']);
        } else {
            $_POST['data_nascita'] = null;
        }
        if (isset($_POST['village'])) {
            if ($_POST['village'] == '') {
                $_POST['village'] = 0;
            }
        }
        $village = $_POST['village'];
        unset($_POST['village']);
        $addetti = Array();
        for ($i = 0; $i <= 6; $i++) {
            $addetti[$i] = intval($_POST['addetti' . $i]);
            unset($_POST['addetti' . $i]);
        }
        if (isset($_POST['ore_settimana'])) {
            $ore_settimana = $_POST['ore_settimana'];
            $stipendio_netto = $_POST['stipendio_netto'];
            $dati_retribuzione = Array();
            foreach ($_POST['data_inizio_r'] as $i => $data_inizio_r) {
                $dati_retribuzione[] = Array(
                    'data_inizio_r' => $data_inizio_r,
                    'retribuzione' => $_POST['retribuzione'][$i],
                    'data_fine_r' => $_POST['data_fine_r'][$i]
                );
            }
            $idatelier_sede = $_POST['idatelier_sede'];
            unset($_POST['ore_settimana'], $_POST['stipendio_netto'], $_POST['data_inizio_r'], $_POST['retribuzione'], $_POST['data_fine_r'], $_POST['idatelier_sede']);
        } else {
            unset($_POST['idatelier_sede']);
        }
        
        foreach ($_POST as $k => $v) {
            /* controllo email */
            if ($k == "email") {
                $sql = $db->prepare("SELECT * FROM $tabella WHERE email = ? AND email != ''");
                $sql->execute(array($v));
                if ($sql->rowCount() > 0) {
                    die('{"msg": "ko", "msgko" : "E-mail già utilizzata"}');
                }
            }
            if ($k == "username" && $v != '') {
                $sql = $db->prepare("SELECT * FROM $tabella WHERE username = ?");
                $sql->execute(array($v));
                if ($sql->rowCount() > 0) {
                    die('{"msg": "ko", "msgko" : "Username già utilizzato"}');
                }
            }
            $campi[] = $k;
            $valori[] = $v;
        }
        /* funzione aggiungi */
        $clientifornitori->aggiungi($campi, $valori);
        $id = $db->lastinsertID();
        $qry = "insert into atelier_addetti (idatelier, addetti0, addetti1, addetti2, addetti3, addetti4, addetti5, addetti6) values (?,?,?,?,?,?,?,?);";
        $rs = $db->prepare($qry);
        $valori = Array($id);
        foreach ($addetti as $addetto) {
            $valori[] = $addetto;
        }
        $rs->execute($valori);
        $qry = "insert into atelier_utente (idatelier, idutente) values (?,?);";
        $rs = $db->prepare($qry);
        $valori = Array($_POST['idatelier'], $id);
        $rs->execute($valori);
        if (isset($ore_settimana)) {
            $qry = "insert into utenti_dipendenti (idutente, ore_settimana, stipendio_netto, dati_retribuzione, idatelier_sede) values (?,?,?,?,?);";
            $valori = Array($id, $ore_settimana, $stipendio_netto, json_encode($dati_retribuzione), $idatelier_sede);
            $rs = $db->prepare($qry);
            $rs->execute($valori);
        }
        if ($_POST["livello"] == '5' && $village != '') {//Atelier
            $idatelier = $id;
            $qry = "insert into utenti_dati_atelier (idatelier, village) values "
                    . "(?,?) "
                    . "ON DUPLICATE KEY UPDATE "
                    . "village = VALUES(village);";
            $rs_atelier = $db->prepare($qry);
            $valori = [$idatelier, $village];
            $rs_atelier->execute($valori);
        }
        die('{"msg" : "ok"}');

        break;

    case 'insertDip':
        $tabella = "dipendenti_atelier";
        $clientifornitori = new dipendenti($id, $tabella);
        $campi = Array('nome', 'cognome', 'idatelier', 'attivo');
        $valori = Array($_POST['nome'], $_POST['cognome'], $_POST['idatelier'], $_POST['attivo']);
        $clientifornitori->aggiungi($campi, $valori);
        die('{"msg" : "ok"}');
        break;

    case "deleteDip":
        $tabella = "dipendenti_atelier";
        $id = $_POST['id'];
        $dati = new dipendenti($id, $tabella);

        $dati->cancellaWhere("id = $id");

        die('{"msg": "ok"}');
        break;

    case "editDip":
        $tabella = "dipendenti_atelier";
        $id = $_POST['id'];

        $dati = new dipendenti($id, $tabella);

        unset($_POST['submit']);

        foreach ($_POST as $k => $v) {
            $campi[] = $k;
            $valori[] = $v;
        }

        $dati->aggiorna($campi, $valori);
        die('{"msg": "ok"}');
        break;

    case 'insertDipGest':
        $tabella = "utenti_collegati";
        $clientifornitori = new dipendenti($id, $tabella);
        $campi = Array('idformatore', 'idutente');
        $valori = Array($_POST['idformatore'], $_POST['idutente']);
        $clientifornitori->aggiungi($campi, $valori);
        die('{"msg" : "ok"}');
        break;

    case 'editDipGest':
        $tabella = "utenti_collegati";
        $id = $_POST['id'];
        $dati = new dipendenti($id, $tabella);
        unset($_POST['submit']);

        foreach ($_POST as $k => $v) {
            $campi[] = $k;
            $valori[] = $v;
        }

        $dati->aggiorna($campi, $valori);
        die('{"msg": "ok"}');
        break;

    case 'deleteDipGest':
        $tabella = "utenti_collegati";
        $id = $_POST['id'];
        $dati = new dipendenti($id, $tabella);

        $dati->cancellaWhere("id = $id");

        die('{"msg": "ok"}');
        break;

    case 'inserisciAddetti':
        $tabella = "addetti_atelier";
        $clientifornitori = new dipendenti($id, $tabella);
        $campi = Array('idatelier', 'data_cal', 'ora_da', 'ora_a', 'addetti');
        if ($_POST['data_cal_al'] != '') {
            $current = formatDateDb($_POST['data_cal_dal']);
            while ($current <= formatDateDb($_POST['data_cal_al'])) {
                //echo $current;
                $valori = Array($_POST['idatelier'], $current, $_POST['ora_da'], $_POST['ora_a'], $_POST['addetti']);
                $clientifornitori->aggiungi($campi, $valori);
                $dataObj = new DateTime($current);
                $dataObj->modify('+1 day');
                $current = $dataObj->format('Y-m-d');
            }
        } else {
            $valori = Array($_POST['idatelier'], formatDateDb($_POST['data_cal_dal']), $_POST['ora_da'], $_POST['ora_a'], $_POST['addetti']);
            $clientifornitori->aggiungi($campi, $valori);
        }

        die('{"msg" : "ok"}');
        break;

    case 'getAddetti':
        $id = $_POST['id'];
        $tabella = "addetti_atelier";
        $dati = new dipendenti($id, $tabella);
        $where = " id = '" . $id . "'";
        $dipendenti = $dati->richiamaWhere($where);
        $dipendenti[0]['data_cal'] = formatDate($dipendenti[0]['data_cal']);
        die('{"dati" : ' . json_encode($dipendenti) . '}');
        break;

    case 'aggiornaAddetti':
        $id = $_POST['id'];
        $tabella = "addetti_atelier";
        $clientifornitori = new dipendenti($id, $tabella);
        $campi = Array('data_cal', 'ora_da', 'ora_a', 'addetti');
        $valori = Array(formatDateDb($_POST['data_cal']), $_POST['ora_da'], $_POST['ora_a'], $_POST['addetti']);
        $clientifornitori->aggiorna($campi, $valori);
        die('{"msg" : "ok"}');
        break;

    case "eliminaAddetti":
        $tabella = "addetti_atelier";
        $id = $_POST['id'];
        $dati = new dipendenti($id, $tabella);
        $dati->cancellaWhere("id = $id");
        die('{"msg": "ok"}');
        break;

    /* calendario turni */

    case 'inserisciTurnoDip':
        $tabella = "calendario_dipendenti";
        $clientifornitori = new dipendenti($id, $tabella);
        $campi = Array('idatelier', 'data_cal', 'ora_da', 'ora_a', 'idutente');
        if ($_POST['data_cal_al'] != '') {
            $current = formatDateDb($_POST['data_cal_dal']);
            while ($current <= formatDateDb($_POST['data_cal_al'])) {
                //echo $current;
                $valori = Array($_POST['idatelier'], $current, $_POST['ora_da'], $_POST['ora_a'], $_POST['idutente']);
                $clientifornitori->aggiungi($campi, $valori);
                $dataObj = new DateTime($current);
                $dataObj->modify('+1 day');
                $current = $dataObj->format('Y-m-d');
            }
        } else {
            $valori = Array($_POST['idatelier'], formatDateDb($_POST['data_cal_dal']), $_POST['ora_da'], $_POST['ora_a'], $_POST['idutente']);
            $clientifornitori->aggiungi($campi, $valori);
        }

        die('{"msg" : "ok"}');
        break;

    case 'getTurnoDip':
        $id = $_POST['id'];
        $tabella = "calendario_dipendenti";
        $dati = new dipendenti($id, $tabella);
        $where = " id = '" . $id . "'";
        $dipendenti = $dati->richiamaWhere($where);
        $dipendenti[0]['data_cal'] = formatDate($dipendenti[0]['data_cal']);
        die('{"dati" : ' . json_encode($dipendenti) . '}');
        break;

    case 'aggiornaTurnoDip':
        $id = $_POST['id'];
        $tabella = "calendario_dipendenti";
        $clientifornitori = new dipendenti($id, $tabella);
        $campi = Array('data_cal', 'ora_da', 'ora_a', 'idutente');
        $valori = Array(formatDateDb($_POST['data_cal']), $_POST['ora_da'], $_POST['ora_a'], $_POST['idutente']);
        $clientifornitori->aggiorna($campi, $valori);
        die('{"msg" : "ok"}');
        break;

    case "eliminaTurnoDip":
        $tabella = "calendario_dipendenti";
        $id = $_POST['id'];
        $dati = new dipendenti($id, $tabella);
        $dati->cancellaWhere("id = $id");
        die('{"msg": "ok"}');
        break;

    case "tuttiAtelierCollegati":
        if ($_POST['table'] != "") {
            $tabella = $_POST['table'];
            $id = $_POST['idutente'];
            $where = " idutente = '" . $id . "'";
        } else {
            $tabella = "atelier_collegati";
            $id = $_POST['idatelier'];
            $where = " idatelier = '" . $id . "'";
        }

        $dati = new dipendenti($id, $tabella);

        $dipendenti = $dati->richiamaWhere($where);
        $tabella = "utenti";
        $where = " attivo=1 and idatelier=0 and id!=$id and nominativo!='' order by nominativo;";
        $dati = new dipendenti($id, $tabella);
        $atelier = $dati->richiamaWhere($where);
        die('{"dati" : ' . json_encode($dipendenti) . ', "atelier": ' . json_encode($atelier) . '}');
        break;

    case 'insertAtelierCollegati':
        $insert = true;
        if ($_POST['table'] != "") {
            $tabella = $_POST['table'];
            $campi = Array('idatelier', 'idutente');
            $valori = Array($_POST['idatelier'], $_POST['idutente']);
            $qry = "select id from $tabella where idatelier=" . $_POST['idatelier'] . " and idutente=" . $_POST['idutente'] . " limit 1;";
            $rs = $db->prepare($qry);
            $rs->execute();
            if ($rs->RowCount() > 0) {
                $insert = false;
            }
        } else {
            $tabella = "atelier_collegati";
            $campi = Array('idatelier', 'idatelier2');
            $valori = Array($_POST['idatelier'], $_POST['idatelier2']);
            $qry = "select id from $tabella where idatelier=" . $_POST['idatelier'] . " and idatelier2=" . $_POST['idatelier2'] . " limit 1;";
            $rs = $db->prepare($qry);
            $rs->execute();
            if ($rs->RowCount() > 0) {
                $insert = false;
            }
        }
        if ($insert) {
            $clientifornitori = new dipendenti($id, $tabella);
            $clientifornitori->aggiungi($campi, $valori);
        }
        die('{"msg" : "ok"}');
        break;

    case "deleteAtelierCollegati":
        if ($_POST['table'] != "") {
            $tabella = $_POST['table'];
        } else {
            $tabella = "atelier_collegati";
        }$id = $_POST['id'];
        $dati = new dipendenti($id, $tabella);

        $dati->cancellaWhere("id = $id");

        die('{"msg": "ok"}');
        break;

    case 'tuttimessaggi':
        $tabella = "messaggi_app";
        $dati = new dipendenti($id, $tabella);
        $where = "id > 0 and idutente=0 order by id desc;";
        $messaggi = $dati->richiamaWhere($where);
        foreach ($messaggi as $k => $col) {
            $atelier = getDati("utenti", "where id in (select idatelier from messaggio_atelier where idmessaggio={$col['id']})");
            if (count($atelier) == 0) {
                $col['nomeatelier'] = 'Tutti';
            } else {
                foreach ($atelier as $col2) {
                    $col['nomeatelier'] .= $col2['nominativo'] . ' ';
                }
            }
            $utente = getDati("utenti", "where id in (select idutente from messaggio_users where idmessaggio={$col['id']})");
            if (count($utente) == 0) {
                $col['nomeutente'] = 'Tutti';
            } else {
                foreach ($utente as $col2) {
                    $col['nomeutente'] .= $col2['cognome'] . ' ' . $col2['nome'];
                }
            }
            $inviati = getDati("invio_messaggio", " where idmessaggio={$col['id']} and inviato=1;");
            if (count($inviati) == 0) {
                $col['date_invio'] = '';
            } else {
                foreach ($inviati as $col2) {
                    $col['date_invio'] .= formatDateOra($col2['data_ora']) . ' ';
                }
            }
            switch ($col['tipo']) {
                case 0:
                    $col['tipo_str'] = 'Generico';
                    break;
                case 1:
                    $col['tipo_str'] = 'Appuntamento';
                    break;
                case 2:
                    $col['tipo_str'] = 'Formazione';
                    break;
            }
            $messaggi[$k] = $col;
        }
        die('{"dati" : ' . json_encode($messaggi) . '}');
        break;

    case "submitformmessaggio":
        $tabella = "messaggi_app";
        $clientifornitori = new dipendenti($id, $tabella);
        unset($_POST['submit'], $_POST['id']);
        if (is_array($_POST['idatelier'])) {
            $atelier = $_POST['idatelier'];
        } else {
            $atelier = Array($_POST['idatelier[]']);
        }
        if (is_array($_POST['idutente'])) {
            $users = $_POST['idutente'];
        } else {
            $users = Array($_POST['idutente[]']);
        }
        if (is_array($_POST['ruolo'])) {
            $ruoli = $_POST['ruolo'];
        } else {
            $ruoli = Array($_POST['ruolo[]']);
        }
        if (is_array($_POST['stato'])) {
            $stati = $_POST['stato'];
        } else {
            $stati = Array($_POST['stato[]']);
        }
        unset($_POST['idatelier'], $_POST['idutente'], $_POST['ruolo'], $_POST['stato']);
        foreach ($_POST as $k => $v) {
            $campi[] = $k;
            $valori[] = $v;
        }
        /* funzione aggiungi */
        $clientifornitori->aggiungi($campi, $valori);
        $id = $db->lastinsertId();
        if (count($atelier) > 0) {
            $qry = "insert into messaggio_atelier (idmessaggio, idatelier) values (?, ?);";
            $rs = $db->prepare($qry);
            foreach ($atelier as $idatelier) {
                if ((int) $idatelier > 0) {
                    $valori = Array($id, $idatelier);
                    $rs->execute($valori);
                }
            }
        }
        if (count($users) > 0) {
            $qry = "insert into messaggio_users (idmessaggio, idutente) values (?, ?);";
            $rs = $db->prepare($qry);
            foreach ($users as $idutente) {
                if ((int) $idutente > 0) {
                    $valori = Array($id, $idutente);
                    $rs->execute($valori);
                }
            }
        }
        if (count($ruoli) > 0) {
            $qry = "insert into messaggio_ruolo (idmessaggio, ruolo) values (?, ?);";
            $rs = $db->prepare($qry);
            foreach ($ruoli as $ruolo) {
                if ($ruolo != '') {
                    $valori = Array($id, $ruolo);
                    $rs->execute($valori);
                }
            }
        }
        if (count($stati) > 0) {
            $qry = "insert into messaggio_stato (idmessaggio, stato) values (?, ?);";
            $rs = $db->prepare($qry);
            foreach ($stati as $stato) {
                if ((int) $stato > 0) {
                    $valori = Array($id, $stato);
                    $rs->execute($valori);
                }
            }
        }
        //inviaNotifichePush($id);
        die('{"msg" : "ok"}');
        break;

    case "delMessaggio":
        $tabella = "messaggi_app";
        $id = $_POST['id'];
        $dati = new dipendenti($id, $tabella);
        $dipendenti = $dati->cancella();
        die('{"msg": "ok"}');
        break;

    case "editformmessaggio":
        $tabella = "messaggi_app";
        $id = $_POST['id'];
        $dati = new dipendenti($id, $tabella);

        unset($_POST['submit']);
        if (is_array($_POST['idatelier'])) {
            $atelier = $_POST['idatelier'];
        } else {
            $atelier = Array($_POST['idatelier[]']);
        }
        //var_dump($atelier);
        if (is_array($_POST['idutente'])) {
            $users = $_POST['idutente'];
        } else {
            $users = Array($_POST['idutente[]']);
        }
        if (is_array($_POST['ruolo'])) {
            $ruoli = $_POST['ruolo'];
        } else {
            $ruoli = Array($_POST['ruolo[]']);
        }
        if (is_array($_POST['stato'])) {
            $stati = $_POST['stato'];
        } else {
            $stati = Array($_POST['stato[]']);
        }
        unset($_POST['idatelier'], $_POST['idutente'], $_POST['ruolo'], $_POST['stato']);
        foreach ($_POST as $k => $v) {
            $campi[] = $k;
            $valori[] = $v;
        }

        $clientefornitore = $dati->aggiorna($campi, $valori);
        if (count($atelier) > 0) {
            $qry = "delete from messaggio_atelier where idmessaggio=$id;";
            $db->exec($qry);
            $qry = "insert into messaggio_atelier (idmessaggio, idatelier) values (?, ?);";
            $rs = $db->prepare($qry);
            foreach ($atelier as $idatelier) {
                if ((int) $idatelier > 0) {
                    $valori = Array($id, $idatelier);
                    $rs->execute($valori);
                }
            }
        }
        if (count($users) > 0) {
            $qry = "delete from messaggio_users where idmessaggio=$id;";
            $db->exec($qry);
            $qry = "insert into messaggio_users (idmessaggio, idutente) values (?, ?);";
            $rs = $db->prepare($qry);
            foreach ($users as $idutente) {
                if ((int) $idutente > 0) {
                    $valori = Array($id, $idutente);
                    $rs->execute($valori);
                }
            }
        }
        if (count($ruoli) > 0) {
            $qry = "delete from messaggio_ruolo where idmessaggio=$id;";
            $db->exec($qry);
            $qry = "insert into messaggio_ruolo (idmessaggio, ruolo) values (?, ?);";
            $rs = $db->prepare($qry);
            foreach ($ruoli as $ruolo) {
                if ($ruolo != '') {
                    $valori = Array($id, $ruolo);
                    $rs->execute($valori);
                }
            }
        }
        if (count($stati) > 0) {
            $qry = "delete from messaggio_stato where idmessaggio=$id;";
            $db->exec($qry);
            $qry = "insert into messaggio_stato (idmessaggio, stato) values (?, ?);";
            $rs = $db->prepare($qry);
            foreach ($stati as $stato) {
                if ((int) $stato > 0) {
                    $valori = Array($id, $stato);
                    $rs->execute($valori);
                }
            }
        }
        die('{"msg": "ok"}');
        break;

    case "attivaMessaggi":
        $qry = "update messaggi_app set attivo=? where id=?;";
        $rs = $db->prepare($qry);
        $valori = Array($_POST['attivo'], $_POST['id']);
        $rs->execute($valori);
        die('{"msg": "ok"}');
        break;

    case "inviaMessaggio":
        $id = $_POST['id'];
        $error = inviaMessaggio($id);
        die('{"error": ' . json_encode($error, JSON_INVALID_UTF8_IGNORE) . '}');
        break;

    case 'tuttilink':
        $tabella = "link_app";
        $dati = new dipendenti($id, $tabella);
        $where = "id > 0 order by id desc;";
        $messaggi = $dati->richiamaWhere($where);
        die('{"dati" : ' . json_encode($messaggi, JSON_INVALID_UTF8_IGNORE) . '}');
        break;

    case "submitformlink":
        $tabella = "link_app";
        $clientifornitori = new dipendenti($id, $tabella);
        unset($_POST['submit'], $_POST['id']);
        foreach ($_POST as $k => $v) {
            $campi[] = $k;
            $valori[] = $v;
        }
        $campi[] = 'attivo';
        $valori[] = 1;
        /* funzione aggiungi */
        $clientifornitori->aggiungi($campi, $valori);
        die('{"msg" : "ok"}');
        break;

    case "delLink":
        $tabella = "link_app";
        $id = $_POST['id'];
        $dati = new dipendenti($id, $tabella);
        $dipendenti = $dati->cancella();
        die('{"msg": "ok"}');
        break;

    case "editformLink":
        $tabella = "link_app";
        $id = $_POST['id'];
        $dati = new dipendenti($id, $tabella);

        unset($_POST['submit']);

        foreach ($_POST as $k => $v) {
            $campi[] = $k;
            $valori[] = $v;
        }

        $clientefornitore = $dati->aggiorna($campi, $valori);
        die('{"msg": "ok"}');
        break;

    case "attivaLink":
        $qry = "update link_app set attivo=? where id=?;";
        $rs = $db->prepare($qry);
        $valori = Array($_POST['attivo'], $_POST['id']);
        $rs->execute($valori);
        die('{"msg": "ok"}');
        break;

    case 'logAtelier':
        $id = $_POST['idatelier'];
        $qry = "select *,DATE_FORMAT(data_ora, '%d/%m/%Y %H:%i') as data_ora_it, (select IF(livello='3',CONCAT(nome, ' ', cognome),nominativo) from utenti u where u.id=laa.idutente) as utente "
                . "from log_accessi_agenda laa where idatelier=? order by data_ora desc;";
        //die($qry);
        $rs = $db->prepare($qry);
        $valori = Array($id);
        $rs->execute($valori);
        $logs = Array();
        while ($log = $rs->fetch(PDO::FETCH_ASSOC)) {
            $log['location'] = '<a href="https://whatismyipaddress.com/ip/' . $log['ip'] . '" target="_blank">' . $log['ip'] . '</a>';
            $logs[] = $log;
        }
        die('{"dati" : ' . json_encode($logs) . '}');
        break;

    case 'resetFormazione':
        $idutente = $_POST['id'];
        $qry = "delete from utenti_formazione where idutente=$idutente;";
        $db->exec($qry);
        $qry = "delete from utenti_corsi where idutente=$idutente;";
        $db->exec($qry);
        die('{"msg": "ok"}');
        break;

    case 'getDipendentiAtelier':
        $idatelier = $_POST['idatelier'];
        $dipendenti = getDipendentiAtelier($idatelier);
        die('{"dipendenti": ' . json_encode($dipendenti, JSON_INVALID_UTF8_IGNORE) . '}');
        break;

    case 'getOreSettimana':
        $idatelier = $_POST['idatelier'];
        $dipendenti = getDipendentiAtelier($idatelier);
        $settimana_dal = $_POST['settimana_dal'];
        $settimana_al = $_POST['settimana_al'];
        foreach ($dipendenti as $i => $dip) {
            $idutente = $dip['id'];
            $qry = "select HOUR(TIMEDIFF(ora_a, ora_da)) as somma_ore,MINUTE(TIMEDIFF(ora_a, ora_da)) as somma_min from calendario_dipendenti where idatelier=? and idutente=? and data_cal >= ? and data_cal < ?;";
            $rs = $db->prepare($qry);
            $valori = Array($idatelier, $dip['id'], $settimana_dal, $settimana_al);
            $rs->execute($valori);
            $dip['somma_ore'] = 0;
            while ($col = $rs->fetch(PDO::FETCH_ASSOC)) {
                $dip['somma_ore'] += floatval($col['somma_ore']) + floatval($col['somma_min'] / 60);
            }
            $dipendenti[$i] = $dip;
        }
        die('{"dipendenti": ' . json_encode($dipendenti, JSON_INVALID_UTF8_IGNORE) . '}');
        break;

    case 'inserisciEventoDip':
        $tabella = "eventi_dipendenti";
        $clientifornitori = new dipendenti($id, $tabella);
        $campi = Array('tipo', 'data_cal', 'ora_da', 'ora_a', 'idutente', 'attivo', 'allday', 'note', 'data_insert', 'idatelier');
        if ($_POST['allday'] == 1) {
            $_POST['ora_da'] = null;
            $_POST['ora_a'] = null;
        }
        if (isset($_POST['idutente'])) {
            $idutente = $_POST['idutente'];
            $inviaEmail = false;
            $attivo = 1;
        } else {
            $idutente = $_SESSION['id'];
            $inviaEmail = true;
            $attivo = 0;
        }
        $now = date('Y-m-d H:i:00');
        if ($_POST['data_cal_al'] != '') {
            $current = formatDateDb($_POST['data_cal_dal']);
            while ($current <= formatDateDb($_POST['data_cal_al'])) {
                //echo $current;
                $valori = Array($_POST['evento_tipo'], $current, $_POST['ora_da'], $_POST['ora_a'], $idutente, $attivo, $_POST['allday'], $_POST['note'], $now, $_POST['idatelier']);
                $clientifornitori->aggiungi($campi, $valori);
                $dataObj = new DateTime($current);
                $dataObj->modify('+1 day');
                $current = $dataObj->format('Y-m-d');
            }
        } else {
            $valori = Array($_POST['evento_tipo'], formatDateDb($_POST['data_cal_dal']), $_POST['ora_da'], $_POST['ora_a'], $idutente, $attivo, $_POST['allday'], $_POST['note'], $now, $_POST['idatelier']);
            $clientifornitori->aggiungi($campi, $valori);
        }
        if ($inviaEmail) {
            $atelier = getAtelier(" and id=" . $_POST['idatelier']);
            #invio e-mail notifica
            include './library/phpmailer/PHPMailerAutoload.php';
            $email_amm = 'hr@comeinunafavola.it';//zampetti@comeinunafavola.it
            $bcc = ['zampetti@comeinunafavola.it'];
            //$email_amm = 'max@clicom.it';
            $dati = new dipendenti($idutente, 'utenti');
            $dipendente = $dati->richiama();
            $emaildip = $dipendente[0]['email'];
            $subject = 'Inserimento nuova richiesta M.Age';
            $body = '<p>Nuova richiesta inserita</p>'
                    . '<p>Dipendente: ' . $dipendente[0]['nome'] . ' ' . $dipendente[0]['cognome'] . '</p>'
                    . '<p>Atelier: ' . $atelier[0]['nominativo'] . '</p>'
                    . '<p>Data dal: ' . $_POST['data_cal_dal'] . '</p>'
                    . '<p>Data al: ' . $_POST['data_cal_al'] . '</p>'
                    . '<p>Ora da: ' . $_POST['ora_da'] . '</p>'
                    . '<p>Ora a: ' . $_POST['ora_a'] . '</p>'
                    . '<p>Tipo: ' . getEventoTipo($_POST['evento_tipo']) . '</p>'
                    . '<p>Note: ' . $_POST['note'] . '</p>'
                    . '<p>Tutto il giorno: ' . ($_POST['allday'] == 1 ? 'si' : 'no') . '</p>';
            $invio = sendMail($emaildip, $subject, $body, $email_amm, "Mr.Age - Come in una favola", "");
            $invio = sendMail($email_amm, $subject, $body, 'noreply@comeinunafavola.it', "Mr.Age - Come in una favola", "", $bcc);
        }
        die('{"msg" : "ok"}');
        break;

    case 'getEventoDip':
        $id = $_POST['id'];
        $tabella = "eventi_dipendenti";
        $dati = new dipendenti($id, $tabella);
        $where = " id = '" . $id . "'";
        $dipendenti = $dati->richiamaWhere($where);
        $dipendenti[0]['data_cal'] = formatDate($dipendenti[0]['data_cal']);
        $atelier = getAtelierUser($dipendenti[0]['idutente']);
        die('{"dati" : ' . json_encode($dipendenti) . ', "atelier": ' . json_encode($atelier, JSON_INVALID_UTF8_IGNORE) . '}');
        break;

    case 'aggiornaEventoDip':
        $id = $_POST['id'];
        $evento = getEventoDipendente($id);
        $tabella = "eventi_dipendenti";
        $clientifornitori = new dipendenti($id, $tabella);
        $campi = Array('tipo', 'data_cal', 'ora_da', 'ora_a', 'attivo', 'allday', 'note', 'idatelier', 'id');
        $valori = Array($_POST['evento_tipo'], formatDateDb($_POST['data_cal_dal']), $_POST['ora_da'], $_POST['ora_a'], $_POST['attivo'], $_POST['allday'], $_POST['note'], $_POST['idatelier'], $id);
        $clientifornitori->aggiorna($campi, $valori);
        if ($evento['attivo'] == 0 && $_POST['attivo'] != 0) {
            if ($_POST['attivo'] == 1) {
                $attivo = 'Approvato';
            } else {
                $attivo = "Rifiutato";
            }
            $atelier = getAtelier(" and id=" . $_POST['idatelier']);
            $idutente = $evento['idutente'];
            include './library/phpmailer/PHPMailerAutoload.php';
            $email_amm = 'noreply@comeinunafavola.it';
            $dati = new dipendenti($idutente, 'utenti');
            $dipendente = $dati->richiama();
            $emaildip = $dipendente[0]['email'];
            $subject = 'Esito richiesta su M.Age';
            $body = '<p>Esito della richiesta: ' . $attivo . '</p>'
                    . '<p>Dipendente: ' . $dipendente[0]['nome'] . ' ' . $dipendente[0]['cognome'] . '</p>'
                    . '<p>Atelier: ' . $atelier[0]['nominativo'] . '</p>'
                    . '<p>Data dal: ' . $_POST['data_cal_dal'] . '</p>'
                    . '<p>Data al: ' . $_POST['data_cal_al'] . '</p>'
                    . '<p>Ora da: ' . $_POST['ora_da'] . '</p>'
                    . '<p>Ora a: ' . $_POST['ora_a'] . '</p>'
                    . '<p>Tipo: ' . getEventoTipo($_POST['evento_tipo']) . '</p>'
                    . '<p>Note: ' . $_POST['note'] . '</p>'
                    . '<p>Tutto il giorno: ' . ($_POST['allday'] == 1 ? 'si' : 'no') . '</p>';
            $invio = sendMail($emaildip, $subject, $body, $email_amm, "Mr.Age - Come in una favola", "");
        }
        die('{"msg" : "ok"}');
        break;

    case "eliminaEventoDip":
        $tabella = "eventi_dipendenti";
        $id = $_POST['id'];
        $dati = new dipendenti($id, $tabella);
        $dati->cancellaWhere("id = $id");
        die('{"msg": "ok"}');
        break;

    case "eliminaEventoDipRichiesta":
        $tabella = "eventi_dipendenti";
        $id = $_POST['id'];
        $dati = new dipendenti($id, $tabella);
        $where = " id = '" . $id . "'";
        $dipendenti = $dati->richiamaWhere($where);
        $idutente = $dipendenti[0]['idutente'];
        $atelier = getAtelier(" and id=" . $dipendenti[0]['idatelier']);
        #invio e-mail notifica
        include './library/phpmailer/PHPMailerAutoload.php';
        $email_amm = 'hr@comeinunafavola.it';
        $bcc = ['zampetti@comeinunafavola.it'];
        //$email_amm = 'max@clicom.it';
        $dati = new dipendenti($idutente, 'utenti');
        $dipendente = $dati->richiama();
        $emaildip = $dipendente[0]['email'];
        $subject = 'Cancellazione richiesta M.Age';
        $body = '<p>Richiesta per eliminazione evento</p>'
                . '<p>Dipendente: ' . $dipendente[0]['nome'] . ' ' . $dipendente[0]['cognome'] . '</p>'
                . '<p>Atelier: ' . $atelier[0]['nominativo'] . '</p>'
                . '<p>Data: ' . $dipendenti[0]['data_cal'] . '</p>'
                . '<p>Ora da: ' . $dipendenti[0]['ora_da'] . '</p>'
                . '<p>Ora a: ' . $dipendenti[0]['ora_a'] . '</p>'
                . '<p>Tipo: ' . getEventoTipo($dipendenti[0]['tipo']) . '</p>'
                . '<p>Note: ' . $dipendenti[0]['note'] . '</p>'
                . '<p>Tutto il giorno: ' . ($dipendenti[0]['allday'] == 1 ? 'si' : 'no') . '</p>';
        $invio = sendMail($email_amm, $subject, $body, 'noreply@comeinunafavola.it', "Mr.Age - Come in una favola", "", $bcc);
        die('{"msg": "ok"}');
        break;

    case "tutte_richieste":
        $dati = getDatiEventiDipendenti(" where attivo=0 order by data_cal desc;");
        $html = '<table class="tb-richieste">'
                . '<tr>'
                . '<td colspan="4"><select id="attivo_mod">'
                . '<option value="1">Approva selezionati</option>'
                . '<option value="2">Rifiuta selezionati</option>'
                . '</select><button type="button" id="btn-approva-all">PROCEDI</button></td>'
                . '</tr>'
                . '<tr>'
                . '<td colspan="4">Cerca nel testo: <input type="text" id="cerca_txt" /> <button type="button" id="btn-cerca-txt">CERCA</button> <button type="button" id="reset-cerca-txt">RESET</button></td>'
                . '</tr>';
        foreach ($dati as $datid) {
            switch ($datid['attivo']) {
                case 0:
                    $arrayore[$i]['color'] = '#69a2de';
                    $attivo = 'Da approvare';
                    if ($datid['data_cal'] > $now) {
                        $elimina = true;
                    }
                    break;

                case 1:
                    $arrayore[$i]['color'] = '#14a64e';
                    $attivo = 'Approvato';
                    if ($datid['data_cal'] > $now) {
                        $richiesta_elimina = true;
                    }
                    break;

                case 2:
                    $arrayore[$i]['color'] = '#ff0000';
                    $attivo = 'Rifiutato';
                    break;
            }
            $html .= '<tr class="row-richieste">'
                    . '<td><input type="checkbox" name="idevento[]" value="' . $datid['id'] . '" /></td>'
                    . '<td class="col-richieste">' . formatDate($datid['data_cal']) . " " . $datid['ora_da'] . " - " . $datid['ora_a'] . " " . $datid['nome_atelier'] . " - " . $datid['dipendente'] . " - " . $datid['evento_tipo'] . '</td>'
                    . '<td id="attivo_' . $datid['id'] . '">' . $attivo . '</td>'
                    . '<td align="center">'
                    . "<span class='deleteevent' style='color: #990000; float: right; padding-left: 3px;position:relative;z-index:99;cursor:pointer;' data-idevento=\"{$datid['id']}\"><i class=\"fa fa-times-circle fa-lg\" aria-hidden=\"true\"></i></span>"
                    . "<span class='editevent' style='color: #000000; float: right;position:relative;z-index:99;cursor:pointer;margin-right:10px;' data-idevento=\"{$datid['id']}\"><i class=\"fa fa-pencil-square-o fa-lg\" aria-hidden=\"true\"></i></span>"
                    . '</td>'
                    . '</tr>';
        }
        $html .= '</table>';
        die('{"html": ' . json_encode($html, JSON_INVALID_UTF8_IGNORE) . '}');
        break;

    case 'modAttivoEvento':
        $attivo = $_POST['attivo'];
        $eventi = Array();
        foreach ($_POST['idevento'] as $i => $idevento) {
            $qry = "update eventi_dipendenti set attivo=? where id=?;";
            $rs = $db->prepare($qry);
            $valori = Array($attivo, $idevento);
            $rs->execute($valori);
            $evento = getDatiEventoDipendente($idevento);
            if (!is_array($eventi[$evento['idutente']])) {
                $eventi[$evento['idutente']] = $evento;
            }
        }
        foreach ($eventi as $evento) {
            if ($attivo == 1) {
                $attivo = 'Approvato';
            } else {
                $attivo = "Rifiutato";
            }
            $atelier = getAtelier(" and id=" . $evento['idatelier']);
            $idutente = $evento['idutente'];
            include './library/phpmailer/PHPMailerAutoload.php';
            $email_amm = 'noreply@comeinunafavola.it';
            $dati = new dipendenti($idutente, 'utenti');
            $dipendente = $dati->richiama();
            $emaildip = $dipendente[0]['email'];
            //$emaildip = "max@clicom.it";
            $subject = 'Esito richiesta su M.Age';
            $body = '<p>Esito della richiesta: ' . $attivo . '</p>'
                    . '<p>Dipendente: ' . $dipendente[0]['nome'] . ' ' . $dipendente[0]['cognome'] . '</p>'
                    . '<p>Atelier: ' . $atelier[0]['nominativo'] . '</p>'
                    . '<p>Tipo: ' . getEventoTipo($evento['tipo']) . '</p>'
                    . '<p>Note: ' . $evento['note'] . '</p>';
            $invio = sendMail($emaildip, $subject, $body, $email_amm, "Mr.Age - Come in una favola", "");
        }
        die('{"msg": "ok"}');
        break;

    case 'getFileDip':
        if ($_POST['data_dal'] != "" && $_POST['data_al'] != "") {
            require_once './library/spout-master/src/Spout/Autoloader/autoload.php';
            //use 'Box\Spout\Writer\Common\Creator\WriterEntityFactory';

            $rows = Array();
            $riga1 = ['COGNOME E NOME', 'RUOLO', 'SEDE ASSUNZIONE', 'DIRETTO/AFFILIATO', 'ORE', 'NETTO'];
            $riga2 = ['', '', '', '', '', ''];
            $current = $_POST['data_dal'];
            $num_giorni = 0;
            while ($current <= $_POST['data_al']) {
                //echo $current;
                $dataObj = new DateTime($current);
                $giorno_num = $dataObj->format('d');
                $riga1[] = $giorno_num;
                $giorno_nome = $dataObj->format('w');
                $riga2[] = $arrgiorniRid[$giorno_nome];
                $dataObj->modify('+1 day');
                $current = $dataObj->format('Y-m-d');
                $num_giorni++;
            }
            $riga1[] = 'TOT. ORE PERMESSO';
            $riga1[] = 'TOT. ORE FERIE';
            $riga1[] = 'NOTE';
            $riga2[] = '';
            $riga2[] = '';
            $riga2[] = '';
            $rows[] = $riga1;
            $rows[] = $riga2;
            $dipendenti = getDipendentiAll();
            foreach ($dipendenti as $i => $dip) {
                $row_dip = [
                    $dip['cognome'] . ' ' . $dip['nome'],
                    getRuoloUtente($dip['ruolo']),
                    $dip['nome_sede'],
                    ($dip['diraff'] == 'd' ? 'Diretto':'Affiliato'),
                    $dip['ore_settimana'],
                    $dip['stipendio_netto']
                ];
                $current = $_POST['data_dal'];
                $ore_lav = $ore_perm = $ore_ferie = $ore_mal = 0;
                $note = '';
                while ($current <= $_POST['data_al']) {
                    //echo $current;
                    $dataObj = new DateTime($current);
                    $qry = "select *,HOUR(TIMEDIFF(ora_a, ora_da)) as somma_ore,MINUTE(TIMEDIFF(ora_a, ora_da)) as somma_min from calendario_dipendenti where data_cal=? and idutente=?;";
                    $rs = $db->prepare($qry);
                    $valori = Array($current, $dip['id']);
                    $rs->execute($valori);
                    $ore_lav_giorno = 0;
                    if ($rs->RowCount() > 0) {
                        while ($col = $rs->fetch(PDO::FETCH_ASSOC)) {
                            $ore_lav += floatval($col['somma_ore']) + floatval($col['somma_min'] / 60);
                            $ore_lav_giorno += floatval($col['somma_ore']) + floatval($col['somma_min'] / 60);
                        }
                        $cell_dip = 'X';
                        $qry = "select *,HOUR(TIMEDIFF(ora_a, ora_da)) as somma_ore,MINUTE(TIMEDIFF(ora_a, ora_da)) as somma_min from eventi_dipendenti where data_cal=? and idutente=? and attivo=?;";
                        $rs2 = $db->prepare($qry);
                        $valori = Array($current, $dip['id'], 1);
                        $rs2->execute($valori);
                        if ($rs2->RowCount() > 0) {
                            while ($col2 = $rs2->fetch(PDO::FETCH_ASSOC)) {
                                switch ($col2['tipo']) {
                                    case 'P'://permesso
                                        $cell_dip .= 'P' . floatval(floatval($col2['somma_ore']) + floatval($col2['somma_min'] / 60));
                                        $ore_perm += floatval($col2['somma_ore']) + floatval($col2['somma_min'] / 60);
                                        break;

                                    case 'F':
                                        $cell_dip = 'F';
                                        if ($col2['allday'] == 1) {
                                            $ore_ferie += floatval($ore_lav_giorno);
                                        } else {
                                            $ore_ferie += floatval($col2['somma_ore']) + floatval($col2['somma_min'] / 60);
                                        }
                                        break;

                                    case 'M':
                                        $cell_dip = 'M';
                                        $ore_mal += floatval($col2['somma_ore']) + floatval($col2['somma_min'] / 60);
                                        break;

                                    case 'C':
                                        //echo $col2['somma_min'].'<br>';
                                        $cell_dip .= 'C' . floatval(floatval($col2['somma_ore']) + floatval($col2['somma_min'] / 60));
                                        break;

                                    case 'L':
                                        $cell_dip .= 'L' . floatval(floatval($col2['somma_ore']) + floatval($col2['somma_min'] / 60));
                                        break;

                                    case 'T':
                                        $cell_dip = 'T';
                                        break;
                                }
                                if ($col2['note'] != '') {
                                    $note .= $col2['note'] . "\n";
                                }
                            }
                        }
                    } else {
                        //verifico se ferie o trasferta
                        $qry = "select *,DATE_FORMAT(data_cal, '%w') as w,HOUR(TIMEDIFF(ora_a, ora_da)) as somma_ore,MINUTE(TIMEDIFF(ora_a, ora_da)) as somma_min from eventi_dipendenti where data_cal=? and idutente=? and attivo=? and tipo in (?,?,?,?) limit 1;";
                        $rs2 = $db->prepare($qry);
                        $valori = Array($current, $dip['id'], 1, 'F', 'M', 'T', 'P');
                        $rs2->execute($valori);
                        if ($rs2->RowCount() > 0) {
                            $col2 = $rs2->fetch(PDO::FETCH_ASSOC);
                            $w = $col2['w'];
                            $qry = "select *,HOUR(TIMEDIFF(ora_a, ora_da)) as somma_ore,MINUTE(TIMEDIFF(ora_a, ora_da)) as somma_min from calendario_dipendenti cd where "
                                    . " idutente=? and DATE_FORMAT(data_cal, '%w')=? and idutente not in "
                                    . "(select idutente from eventi_dipendenti ed where ed.data_cal=cd.data_cal and idutente=? and tipo in ('F', 'M', 'T', 'P')) "
                                    . "order by data_cal desc limit 1;";
                            $rs3 = $db->prepare($qry);
                            $valori = Array($dip['id'], $w, $dip['id']);
                            //var_dump($valori);
                            //die();
                            $rs3->execute($valori);
                            if ($rs3->RowCount() > 0) {
                                $col3 = $rs3->fetch(PDO::FETCH_ASSOC);
                                $data_cal3 = $col3['data_cal'];
                                $qry = "select *,HOUR(TIMEDIFF(ora_a, ora_da)) as somma_ore,MINUTE(TIMEDIFF(ora_a, ora_da)) as somma_min from calendario_dipendenti where "
                                        . " idutente=? and data_cal=?;";
                                $rs3 = $db->prepare($qry);
                                $valori = Array($dip['id'], $data_cal3);
                                $rs3->execute($valori);
                                $ore_lav3 = 0;
                                while ($col3 = $rs3->fetch(PDO::FETCH_ASSOC)) {
                                    $ore_lav3 += floatval($col3['somma_ore']) + floatval($col3['somma_min'] / 60);
                                }
                                //var_dump($col3);
                                //die();                                
                                //echo "$ore_lav3 " . $col2['tipo'] . "<br>";
                                switch ($col2['tipo']) {
                                    case 'F':
                                        $cell_dip = 'F';
                                        if ($col2['allday'] == 1) {
                                            $ore_ferie += floatval($ore_lav3);
                                        } else {
                                            
                                        }
                                        break;

                                    case 'M':
                                        $cell_dip = 'M';
                                        $ore_mal += floatval($ore_lav3);
                                        break;

                                    case 'T':
                                        $cell_dip = 'T';
                                        break;
                                }
                            }
                        } else {
                            $cell_dip = 'R';
                        }
                    }
                    $row_dip[] = $cell_dip;
                    $dataObj->modify('+1 day');
                    $current = $dataObj->format('Y-m-d');
                }
                //$row_dip[] = $ore_lav;
                $row_dip[] = $ore_perm;
                $row_dip[] = $ore_ferie;
                $row_dip[] = $note;
                $rows[] = $row_dip;
            }
            //var_dump($rows);
            //die();
            $path = 'tmp/report.xlsx';
            $writer = Box\Spout\Writer\Common\Creator\WriterEntityFactory::createXLSXWriter();
            $writer->openToFile($path);
            foreach ($rows as $row) {
                $rowFromValues = Box\Spout\Writer\Common\Creator\WriterEntityFactory::createRowFromArray($row);
                $writer->addRow($rowFromValues);
            }
            $writer->close();
            header('Content-Type: application/octet-stream');
            header("Content-Transfer-Encoding: Binary");
            header("Content-disposition: attachment; filename=\"report.xlsx\"");

// Actual download.
            readfile($path);
            die();
        }
        break;

    case 'getReportApp':
        if ($_POST['data_dal'] != "") {
            require_once './library/spout-master/src/Spout/Autoloader/autoload.php';
            //use 'Box\Spout\Writer\Common\Creator\WriterEntityFactory';
            $arrCentralino = Array(11, 9, 12, 16);
            $rows = Array();
            $riga1 = [/* 'ID', */'ATELIER', 'TELEFONATE', 'Telefono', 'Matrimonio.com', 'Online', 'WhatsApp', 'Google', 'APPUNTAMENTI TOTALI'];
            $data_dal = $_POST['data_dal'];
            $data_al = $_POST['data_al'];
            $rows[] = $riga1;
            if ($data_dal != '' && $data_al != '') {
                $qry_data_cal = "and data_insert between '$data_dal' and '$data_al'";
                $qry_data_tel = "and data_app between '$data_dal' and '$data_al'";
            } else {
                $qry_data_cal = "and data_insert='$data_dal'";
                $qry_data_tel = "and data_app='$data_dal'";
            }
            $ateliers = getAtelier("and id not in (225,229,224,71,137,204,77,150,75)");
            $tot_app = $tot_mat = $tot_online = $tot_what = $tot_goo = $tot_tel = 0;
            foreach ($ateliers as $atelier) {
                $app = $mat = $online = $what = $goo = $tel = 0;
                $qry = "select * from calendario where id > 0 $qry_data_cal and idatelier=" . $atelier['id'] . " and "
                        . "(online=1 or provenienza in ('Whatsapp', 'Matrimonio.com', 'Google', 'Telefono')) and (idutente_insert in "
                        . "(select id from utenti where ruolo=" . CENTRALINO . ") or online=1);";
                $rs = $db->prepare($qry);
                $rs->execute();
                while ($col = $rs->fetch(PDO::FETCH_ASSOC)) {
                    $app++;
                    if ($col['online'] == 1) {
                        $online++;
                    } else {
                        switch ($col['provenienza']) {
                            case 'Whatsapp':
                                $what++;
                                break;

                            case 'Matrimonio.com':
                                $mat++;
                                break;

                            case 'Google':
                                $goo++;
                                break;

                            case 'Telefono':
                                $tel++;
                                break;
                        }
                    }
                }
                $tot_app += $app;
                $tot_mat += $mat;
                $tot_online += $online;
                $tot_what += $what;
                $tot_goo += $goo;
                $tot_tel += $tel;
                $riga_atelier = [
                    /* $atelier['id'], */
                    $atelier['nominativo'],
                    $tel,
                    $tel,
                    $mat,
                    $online,
                    $what,
                    $goo,
                    $app
                ];
                $rows[] = $riga_atelier;
            }
            $qry = "select SUM(count) from telefonate_noapp where id > 0 $qry_data_tel limit 1;";
            //die($qry);
            $rs_tel = $db->prepare($qry);
            $rs_tel->execute();
            $tot_richieste = (int) $rs_tel->fetchColumn();
            $riga_totali = [
                'TOTALI',
                ($tot_richieste + $tot_tel),
                $tot_tel,
                $tot_mat,
                $tot_online,
                $tot_what,
                $tot_goo,
                $tot_app
            ];
            $rows[] = $riga_totali;
            //var_dump($rows);
            //die();
            $path = 'tmp/reportAppuntamenti.xlsx';
            $writer = Box\Spout\Writer\Common\Creator\WriterEntityFactory::createXLSXWriter();
            $writer->openToFile($path);
            foreach ($rows as $row) {
                $rowFromValues = Box\Spout\Writer\Common\Creator\WriterEntityFactory::createRowFromArray($row);
                $writer->addRow($rowFromValues);
            }
            $writer->close();
            header('Content-Type: application/octet-stream');
            header("Content-Transfer-Encoding: Binary");
            header("Content-disposition: attachment; filename=\"reportAppuntamenti.xlsx\"");

// Actual download.
            readfile($path);
            die();
        }
        break;

    case 'getReportAppTot':
        if ($_POST['data_dal'] != "") {
            require_once './library/spout-master/src/Spout/Autoloader/autoload.php';
            //var_dump($_POST);
            //die();
            if (is_array($_POST['iduser'])) {
                $arr_user = $_POST['iduser'];
                $rows = Array();
                $data_dal = $_POST['data_dal'];
                $data_al = $_POST['data_al'];
                if ($data_dal != '' && $data_al != '') {
                    $qry_data_cal = "and data_insert between '$data_dal' and '$data_al'";
                    $qry_data_tel = "and data_app between '$data_dal' and '$data_al'";
                } else {
                    $qry_data_cal = "and data_insert='$data_dal'";
                    $qry_data_tel = "and data_app='$data_dal'";
                }
                $riga1 = [''];
                $tot_user = $tot_atelier = [];
                $totale_app = 0;
                foreach ($arr_user as $iduser) {
                    $operatore = getDati('utenti', "where id=$iduser limit 1;");
                    $riga1[] = $operatore[0]['cognome'] . ' ' . $operatore[0]['nome'];
                    $tot_user[$iduser] = 0;
                }
                $riga1[] = 'Totali';
                $rows[] = $riga1;
                $qry = "select idatelier,(select nominativo from utenti u where u.id=c.idatelier limit 1) as atelier from calendario c "
                        . "where id > 0 $qry_data_cal and idutente_insert in (" . join(",", $arr_user) . ") group by idatelier;";
                //die($qry);
                $rs = $db->prepare($qry);
                $rs->execute();
                while ($col_atelier = $rs->fetch(PDO::FETCH_ASSOC)) {
                    $tot_atelier[$col_atelier['idatelier']] = 0;
                    $listApp = [];
                    foreach ($arr_user as $iduser) {
                        $qry = "select COUNT(id) as num from calendario c "
                                . "where id > 0 $qry_data_cal and idutente_insert=" . $iduser . " and idatelier=" . $col_atelier['idatelier'] . " and tipoappuntamento!=6 limit 1;";
                        //die($qry);
                        $rs2 = $db->prepare($qry);
                        $rs2->execute();
                        $col_app = $rs2->fetch(PDO::FETCH_ASSOC);
                        $num_app = (int) $col_app['num'];
                        $qry = "select COUNT(id) as num from calendario c "
                                . "where id > 0 $qry_data_cal and idutente_insert=" . $iduser . " and idatelier=" . $col_atelier['idatelier'] . " and tipoappuntamento=6 limit 1;";
                        //die($qry);
                        $rs2 = $db->prepare($qry);
                        $rs2->execute();
                        $col_app = $rs2->fetch(PDO::FETCH_ASSOC);
                        $num_app += (int) $col_app['num'] * 2;
                        $listApp[] = $num_app;
                        $tot_user[$iduser] += $num_app;
                        $tot_atelier[$col_atelier['idatelier']] += $num_app;
                        $totale_app += $num_app;
                    }
                    $rows[] = [
                        $col_atelier['atelier'],
                        ...$listApp,
                        $tot_atelier[$col_atelier['idatelier']]
                    ];
                }
                $rows[] = [
                    'Totali',
                    ...$tot_user,
                    $totale_app
                ];
                //var_dump($rows);
                //die();
                $path = 'tmp/reportAppuntamentiUser.xlsx';
                $writer = Box\Spout\Writer\Common\Creator\WriterEntityFactory::createXLSXWriter();
                $writer->openToFile($path);
                foreach ($rows as $row) {
                    $rowFromValues = Box\Spout\Writer\Common\Creator\WriterEntityFactory::createRowFromArray($row);
                    $writer->addRow($rowFromValues);
                }
                $writer->close();
                header('Content-Type: application/octet-stream');
                header("Content-Transfer-Encoding: Binary");
                header("Content-disposition: attachment; filename=\"reportAppuntamentiUser.xlsx\"");
                readfile($path);
                die();
            } else {
                //use 'Box\Spout\Writer\Common\Creator\WriterEntityFactory';
                $arrCentralino = Array(11, 9, 12, 16);
                $rows = Array();
                $riga1 = [/* 'ID', */'ATELIER', 'Di passaggio', 'Instagram', 'Fiera', 'Telefono', 'Matrimonio.com', 'Online', 'WhatsApp', 'Google', 'APPUNTAMENTI TOTALI'];
                $data_dal = $_POST['data_dal'];
                $data_al = $_POST['data_al'];
                $rows[] = $riga1;
                if ($data_dal != '' && $data_al != '') {
                    $qry_data_cal = "and data_insert between '$data_dal' and '$data_al'";
                    $qry_data_tel = "and data_app between '$data_dal' and '$data_al'";
                } else {
                    $qry_data_cal = "and data_insert='$data_dal'";
                    $qry_data_tel = "and data_app='$data_dal'";
                }
                $ateliers = getAtelier("and id not in (225,229,224,71,137,204,77,150)");
                $tot_app = $tot_mat = $tot_online = $tot_what = $tot_goo = $tot_tel = $tot_pass = $tot_inst = $tot_fiera = 0;
                foreach ($ateliers as $atelier) {
                    $app = $mat = $online = $what = $goo = $tel = $pass = $inst = $fiera = 0;
                    $qry = "select * from calendario where id > 0 $qry_data_cal and idatelier=" . $atelier['id'] . " and "
                            . "(online=1 or provenienza in ('Di passaggio', 'Instagram', 'Fiera' ,'Whatsapp', 'Matrimonio.com', 'Google', 'Telefono'));";
                    $rs = $db->prepare($qry);
                    $rs->execute();
                    while ($col = $rs->fetch(PDO::FETCH_ASSOC)) {
                        $app++;
                        if ($col['online'] == 1) {
                            $online++;
                        } else {
                            switch ($col['provenienza']) {
                                case 'Di passaggio':
                                    $pass++;
                                    break;

                                case 'Instagram':
                                    $inst++;
                                    break;

                                case 'Fiera':
                                    $fiera++;
                                    break;

                                case 'Whatsapp':
                                    $what++;
                                    break;

                                case 'Matrimonio.com':
                                    $mat++;
                                    break;

                                case 'Google':
                                    $goo++;
                                    break;

                                case 'Telefono':
                                    $tel++;
                                    break;
                            }
                        }
                    }
                    $tot_app += $app;
                    $tot_mat += $mat;
                    $tot_online += $online;
                    $tot_what += $what;
                    $tot_goo += $goo;
                    $tot_tel += $tel;
                    $tot_pass += $pass;
                    $tot_fiera += $fiera;
                    $tot_inst += $inst;
                    $riga_atelier = [
                        /* $atelier['id'], */
                        $atelier['nominativo'],
                        $pass,
                        $inst,
                        $fiera,
                        $tel,
                        $mat,
                        $online,
                        $what,
                        $goo,
                        $app
                    ];
                    $rows[] = $riga_atelier;
                }
                $qry = "select SUM(count) from telefonate_noapp where id > 0 $qry_data_tel limit 1;";
                //die($qry);
                $rs_tel = $db->prepare($qry);
                $rs_tel->execute();
                $tot_richieste = (int) $rs_tel->fetchColumn();
                $riga_totali = [
                    'TOTALI',
                    //($tot_richieste + $tot_tel),
                    $tot_pass,
                    $tot_inst,
                    $tot_fiera,
                    $tot_tel,
                    $tot_mat,
                    $tot_online,
                    $tot_what,
                    $tot_goo,
                    $tot_app
                ];
                $rows[] = $riga_totali;
                //var_dump($rows);
                //die();
                $path = 'tmp/reportAppuntamentiTot.xlsx';
                $writer = Box\Spout\Writer\Common\Creator\WriterEntityFactory::createXLSXWriter();
                $writer->openToFile($path);
                foreach ($rows as $row) {
                    $rowFromValues = Box\Spout\Writer\Common\Creator\WriterEntityFactory::createRowFromArray($row);
                    $writer->addRow($rowFromValues);
                }
                $writer->close();
                header('Content-Type: application/octet-stream');
                header("Content-Transfer-Encoding: Binary");
                header("Content-disposition: attachment; filename=\"reportAppuntamentiTot.xlsx\"");
                readfile($path);
                die();
            }
        } else {
            die();
        }
        break;

    case 'setNumTel':
        $inc = (string) $_POST['inc'];
        $now = date('Y-m-d');
        $qry = "select id from telefonate_noapp where data_app=? limit 1;";
        $rs_tel = $db->prepare($qry);
        $valori = Array($now);
        $rs_tel->execute($valori);
        if ($rs_tel->RowCount() == 0) {
            $qry = "insert into telefonate_noapp (data_app, count) values (?,?);";
            $rs_tel = $db->prepare($qry);
            $valori = Array($now, 0);
            $rs_tel->execute($valori);
            $id = $db->lastinsertId();
        } else {
            $id = $rs_tel->fetchColumn();
        }
        $qry = "update telefonate_noapp set count=count $inc where id=$id;";
        $db->exec($qry);
        die('{"error":""}');
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
        <script type="text/javascript" src="./js/functions-dipendenti.js?t=<?= time() ?>"></script>

        <link type="text/css" rel="stylesheet" href="./js/jsgrid-1.5.1/jsgrid.min.css" />
        <link type="text/css" rel="stylesheet" href="./js/jsgrid-1.5.1/jsgrid-theme.min.css" />

        <script type="text/javascript" src="./js/jsgrid-1.5.1/jsgrid.min.js"></script>
        <script type="text/javascript" src="./js/jsgrid-1.5.1/i18n/jsgrid-it.js"></script>
        <!-- timepicker-->
        <link rel='stylesheet' href='./js/jquery-timepicker-1.3.5/jquery.timepicker.min.css' />
        <script type="text/javascript" src="./js/jquery-timepicker-1.3.5/jquery.timepicker.min.js"></script>
        <script src="/js/select2-4.1.0/dist/js/select2.full.min.js"></script>
        <link type="text/css" href="/js/select2-4.1.0/dist/css/select2.min.css" rel="Stylesheet" />
        <script type="text/javascript">//
//            $(document).ready(function () {
//                mostraDipendenti('3');
//            });
        </script>
        <script type="text/javascript">
            $(document).ready(function () {
<?php
if ($op == "") {
    ?>
                    mostraDipendenti('3');
    <?php
} else if ($op == "atelier" && ($_SESSION["livello"] == 0 || $_SESSION["livello"] == 1 || $_SESSION["livello"] == 5 || $_SESSION['ruolo'] == CENTRALINO || $_SESSION['ruolo'] == RISORSE_UMANE)) {
    ?>
                    mostraDipendenti('5');
    <?php
} else if ($op == "messaggi" && ($_SESSION["livello"] == 0 || $_SESSION["livello"] == 1)) {
    ?>
                    mostraMessaggi();
    <?php
} else if ($op == "formazione" && ($_SESSION["livello"] == 0 || $_SESSION["livello"] == 1)) {
    ?>
                    mostraFormazione();
    <?php
} else if ($op == "link" && ($_SESSION["livello"] == 0 || $_SESSION["livello"] == 1)) {
    ?>
                    mostraLink();
    <?php
}
?>
            });
        </script>


    </head>
    <body class="colormodulo">
        <?php include './components/top.php'; ?>
        <?php if (moduloattivo("dipendenti") > 0 || $_SESSION['ruolo'] == CENTRALINO || $_SESSION['ruolo'] == RISORSE_UMANE || ($_SESSION['livello'] == '5' && $_SESSION["diraff"] == 'a')) { ?>
            <div class="barra_submenu sizing">
                <li class="box_submenu sizing"><a href="dipendenti.php"><i class="fa fa-user fa-lg" aria-hidden="true"></i> Anagrafica dipendenti</a></li>
                <?php if ($_SESSION["livello"] == 0 || $_SESSION["livello"] == 1 || $_SESSION["livello"] == 5 || $_SESSION['ruolo'] == CENTRALINO || $_SESSION['ruolo'] == RISORSE_UMANE) { ?>
                    <li class="box_submenu sizing"><a href="dipendenti.php?op=atelier"><i class="fa fa-user fa-lg" aria-hidden="true"></i> Anagrafica atelier</a></li>
                <?php } ?>
                <?php if ($_SESSION["livello"] == 0 || $_SESSION["livello"] == 1) { ?>
                    <li class="box_submenu sizing"><a href="dipendenti.php?op=messaggi"><i class="fa fa-text-width fa-lg" aria-hidden="true"></i> Applicazione</a></li>
                <?php } ?>
                <?php if ($_SESSION["livello"] == 0 || $_SESSION["livello"] == 1 || $_SESSION['ruolo'] == 6) {//marketing ?>
                    <li class="box_submenu sizing"><a href="dipendenti.php?op=formazione"><i class="fa fa-graduation-cap fa-lg" aria-hidden="true"></i> Formazione</a></li>
                <?php } ?>
                <?php if ($_SESSION["livello"] == 0 || $_SESSION["livello"] == 1) { ?>
                    <li class="box_submenu sizing"><a href="dipendenti.php?op=link"><i class="fa fa-link fa-lg" aria-hidden="true"></i> Link App</a></li>
                <?php } ?>
            </div>
            <div class="content sizing">
                <div class="barra_op sizing">
                    <?php
                    if ($op == "") {
                        ?>
                        <div class="bottone sizing"><a href="javascript:;" onclick="aggiungi('3');"><i class="fa fa-user-plus fa-lg" aria-hidden="true"></i> Aggiungi dipendente</a></div>
                    <?php } else if ($op == "atelier" && ($_SESSION["livello"] == 0 || $_SESSION["livello"] == 1)) { ?>
                        <div class="bottone sizing"><a href="javascript:;" onclick="aggiungi('5');"><i class="fa fa-user-plus fa-lg" aria-hidden="true"></i> Aggiungi Atelier</a></div>
                    <?php } else if ($op == "messaggi" && ($_SESSION["livello"] == 0 || $_SESSION["livello"] == 1)) { ?>
                        <div class="bottone sizing"><a href="javascript:;" onclick="aggiungiMessaggio();"><i class="fa fa-user-plus fa-lg" aria-hidden="true"></i> Aggiungi Messaggio</a></div>
                    <?php } else if ($op == "formazione" && ($_SESSION["livello"] == 0 || $_SESSION["livello"] == 1)) { ?>
                        <div class="bottone sizing"><a href="javascript:;" onclick="aggiungiFormazione();"><i class="fa fa-user-plus fa-lg" aria-hidden="true"></i> Aggiungi Modulo Formazione</a></div>
                    <?php } else if ($op == "link" && ($_SESSION["livello"] == 0 || $_SESSION["livello"] == 1)) { ?>
                        <div class="bottone sizing"><a href="javascript:;" onclick="aggiungiLink();"><i class="fa fa-user-plus fa-lg" aria-hidden="true"></i> Aggiungi Link</a></div>
                    <?php } ?>
                    <div id="messaggio" class="messaggiook">Operazione avvenuta con successo</div>
                </div>                
                <div class="showcont sizing">
                    <!-- qui contenuto del mostra anagrafiche e aggiungi anagrafiche --> 
                </div>
                <div class="showcont-form sizing"></div>
            </div>
        <?php } ?>
    </body>
</html>
