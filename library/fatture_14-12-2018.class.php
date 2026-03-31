<?php

class fatture extends basic {

    public $id;
    public $tabella;

    function __construct($id, $tabella) {
        $this->id = $id;
        $this->tabella = $tabella;
    }

    function aggiungi($campi, $valori, $voci, $scadenze, $statofatt, $tipo) {
        global $db;
        parent::aggiungi($campi, $valori);
        $idfattura = $db->lastInsertId();
        $old = umask(0);
        mkdir("./fatture/$idfattura", 0777, true);
        umask($old);
        sort($voci);
        if ($voci) {
            $sql = $db->prepare("INSERT INTO fatture_voci (idfattura, descrizione, um, qta, importo, sconto, iva, idprev, idcomm, idvocecomm, idfatt, idprofit, ordine) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)");
            for ($i = 0; $i < count($voci); $i++) {
                $sql->execute(array($idfattura, $voci[$i]['descrizione'], $voci[$i]['um'], $voci[$i]['qta'], $voci[$i]['importo'], $voci[$i]['sconto'], $voci[$i]['iva'], $voci[$i]['idprev'], $voci[$i]['idcomm'], $voci[$i]['idvocecomm'], $voci[$i]['idfatt'], $voci[$i]['idprofit'], $voci[$i]['ordine']));
                /* array commesse */
                $arridcomm[] = $voci[$i]['idcomm'];
            }
        }
        if ($scadenze) {
            $sql = $db->prepare("INSERT INTO fatture_scadenze (idfattura, datascadenza, importoscadenza, notescadenza, stato, sufattura, tiposcadenza) VALUES (?,?,?,?,?,?,?)");
            for ($i = 0; $i < count($scadenze); $i++) {
                $sql->execute(array($idfattura, $scadenze[$i]['datascadenza'], $scadenze[$i]['importoscadenza'], $scadenze[$i]['notescadenza'], $statofatt, "0", $tipo));
                $sql->execute(array($idfattura, $scadenze[$i]['datascadenza'], $scadenze[$i]['importoscadenza'], $scadenze[$i]['notescadenza'], $statofatt, "1", $tipo));
            }
        }
        /* controllo se ho fatturato tutte le voci della commessa */
        $arrayidcomminfattura = array_unique($arridcomm);
        for ($n = 0; $n < count($arrayidcomminfattura); $n++) {
            $sql = $db->prepare("SELECT * FROM commesse_voci WHERE idcomm = ? AND statovoce = ?");
            $sql1 = $db->prepare("UPDATE commesse SET stato = ? WHERE id = ?");
            $sql->execute(array($arrayidcomminfattura[$n], 0));
            if ($sql->rowCount() > 0) {
                $sql1->execute(array(2, $arrayidcomminfattura[$n]));
            } else {
                $sql1->execute(array(3, $arrayidcomminfattura[$n]));
            }
        }
    }

    function cancella() {
        global $db;
        parent::cancella();
        /* ripristino "confermato" preventivi */
        $sql = $db->prepare("SELECT idprev FROM fatture_voci WHERE idfattura = ? AND idprev > 0 GROUP BY idprev");
        $sql->execute(array($this->id));
        $res = $sql->fetchAll();
        foreach ($res as $row) {
            $idprev = $row['idprev'];
            $sql1 = $db->prepare("UPDATE preventivi SET stato = ? WHERE id = ?");
            $sql1->execute(array(2, $idprev));
        }
        /* ripristino da fatturare voci commesse */
        $sql = $db->prepare("SELECT idvocecomm FROM fatture_voci WHERE idfattura = ? AND idvocecomm > 0");
        $sql->execute(array($this->id));
        $res = $sql->fetchAll();
        foreach ($res as $row) {
            $idvocecomm = $row['idvocecomm'];
            $sql1 = $db->prepare("UPDATE commesse_voci SET statovoce = ? WHERE id = ?");
            $sql1->execute(array(0, $idvocecomm));
        }
        /**/
        /* ripristino "da fatturare" commesse */
        $sql = $db->prepare("SELECT idcomm FROM fatture_voci WHERE idfattura = ? AND idcomm > 0 GROUP BY idcomm");
        $sql->execute(array($this->id));
        $res = $sql->fetchAll();
        foreach ($res as $row) {
            $idcomm = $row['idcomm'];
            $sql1 = $db->prepare("UPDATE commesse SET stato = ? WHERE id = ?");
            $sql1->execute(array(2, $idcomm));
        }
        /* cancello voci fattura */
        $sql = $db->prepare("DELETE FROM fatture_voci WHERE idfattura = ?");
        $sql->execute(array($this->id));
        /* cancello scadenze fattura */
        $sql = $db->prepare("DELETE FROM fatture_scadenze WHERE idfattura = ?");
        $sql->execute(array($this->id));
        /* cancello contenuti e cartella */
        array_map('unlink', glob("./fatture/$this->id/*"));
        rmdir("./fatture/$this->id");
    }

    function richiama() {
        return parent::richiama();
    }

    function richiamavoci($where) {
        return parent::richiamaWhere($where);
    }

    function aggiorna($campi, $valori, $voci, $scadenze, $modificascadenze, $statofatt, $tipo) {
        global $db;
        /* aggiorno fattura */
        parent::aggiorna($campi, $valori);
        /* seleziono precedenti voci */
        $sql = $db->prepare("SELECT idvocecomm FROM fatture_voci WHERE idfattura = ?");
        $sql->execute(array($this->id));
        $res = $sql->fetchAll();
        $arr_idvocicom_prec = array();
        foreach ($res as $row) {
            $arr_idvocicom_prec[] = $row['idvocecomm'];
        }
        /* rimuovo voci */
        $sql = $db->prepare("DELETE FROM fatture_voci WHERE idfattura = ?");
        $sql->execute(array($this->id));
        /* aggiorno voci */
        $arr_idvocicom_attuali = array();
        sort($voci);
        if ($voci) {
            /**/
            $sql = $db->prepare("INSERT INTO fatture_voci (idfattura, descrizione, um, qta, importo, sconto, iva, idprev, idcomm, idvocecomm, idfatt, idprofit, ordine) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)");
            for ($i = 0; $i < count($voci); $i++) {
                /* vocicomm attuali */
                $arr_idvocicom_attuali[] = $voci[$i]['idvocecomm'];
                /* array commesse */
                $arridcomm[] = $voci[$i]['idcomm'];
                /**/
                $sql->execute(array($this->id, $voci[$i]['descrizione'], $voci[$i]['um'], $voci[$i]['qta'], $voci[$i]['importo'], $voci[$i]['sconto'], $voci[$i]['iva'], $voci[$i]['idprev'], $voci[$i]['idcomm'], $voci[$i]['idvocecomm'], $voci[$i]['idfatt'], $voci[$i]['idprofit'], $voci[$i]['ordine']));
            }
        }
        $array_idvocicomm_riattivare = array_diff($arr_idvocicom_prec, $arr_idvocicom_attuali);
        if ($array_idvocicomm_riattivare) {
            foreach ($array_idvocicomm_riattivare as $k => $v) {
                $sql = $db->prepare("UPDATE commesse_voci SET statovoce = ? WHERE id = ?");
                $sql->execute(array(0, $v));
            }
        }

        /**/
//        if ($tipo < 3 || $tipo == 4) {
            if ($modificascadenze > 0) {
                /* rimuovo scadenze */
                $sql = $db->prepare("DELETE FROM fatture_scadenze WHERE idfattura = ?");
                $sql->execute(array($this->id));

                if ($scadenze) {
                    $sql = $db->prepare("INSERT INTO fatture_scadenze (idfattura, datascadenza, importoscadenza, notescadenza, stato, sufattura, tiposcadenza) VALUES (?,?,?,?,?,?,?)");
                    for ($i = 0; $i < count($scadenze); $i++) {
                        $sql->execute(array($this->id, $scadenze[$i]['datascadenza'], $scadenze[$i]['importoscadenza'], $scadenze[$i]['notescadenza'], $statofatt, "0", $tipo));
                        $sql->execute(array($this->id, $scadenze[$i]['datascadenza'], $scadenze[$i]['importoscadenza'], $scadenze[$i]['notescadenza'], $statofatt, "1", $tipo));
                    }
                }
            } else {
                /* rimuovo scadenze */
                $sql = $db->prepare("DELETE FROM fatture_scadenze WHERE idfattura = ? AND sufattura = ?");
                $sql->execute(array($this->id, 0));
                if ($scadenze) {
                    $sql = $db->prepare("INSERT INTO fatture_scadenze (idfattura, datascadenza, importoscadenza, notescadenza, stato, sufattura, tiposcadenza) VALUES (?,?,?,?,?,?,?)");
                    for ($i = 0; $i < count($scadenze); $i++) {
                        $sql->execute(array($this->id, $scadenze[$i]['datascadenza'], $scadenze[$i]['importoscadenza'], $scadenze[$i]['notescadenza'], $statofatt, "0", $tipo));
                    }
                }
            }
//        } else {
//            /* rimuovo scadenze */
//            $sql = $db->prepare("DELETE FROM fatture_scadenze WHERE idfattura = ?");
//            $sql->execute(array($this->id));
//        }
        /* controllo se ho fatturato tutte le voci della commessa */
        $arrayidcomminfattura = array_unique($arridcomm);
        for ($n = 0; $n < count($arrayidcomminfattura); $n++) {
            $sql = $db->prepare("SELECT * FROM commesse_voci WHERE idcomm = ? AND statovoce = ?");
            $sql1 = $db->prepare("UPDATE commesse SET stato = ? WHERE id = ?");
            $sql->execute(array($arrayidcomminfattura[$n], 0));
            if ($sql->rowCount() > 0) {
                $sql1->execute(array(2, $arrayidcomminfattura[$n]));
            } else {
                $sql1->execute(array(3, $arrayidcomminfattura[$n]));
            }
        }
    }

}
