<?php

class commesse extends basic {

    public $id;
    public $tabella;

    function __construct($id, $tabella) {
        $this->id = $id;
        $this->tabella = $tabella;
    }

    function aggiungicosto($campi, $valori) {
        global $db;
        parent::aggiungi($campi, $valori);
        $idcostocommessa = $db->lastInsertId();
        return $idcostocommessa;
    }

    function aggiornacosto($campi, $valori, $costoattuale) {
        global $db;
        
        $sql = $db->prepare("SELECT idcomm, costo FROM $this->tabella WHERE id = $this->id");
        $sql->execute();
        $res = $sql->fetch();
        $idcommessa = $res['idcomm'];
        $costoprecedente = $res['costo'];
        
        $sql = $db->prepare("UPDATE commesse SET totalecosti = totalecosti-$costoprecedente+$costoattuale WHERE id = $idcommessa");
        $sql->execute();
        
        
        /* aggiorno commessa */
        parent::aggiorna($campi, $valori);
    }

    function cancellacosto() {
        global $db;

        $sql = $db->prepare("SELECT idcomm, costo FROM $this->tabella WHERE id = $this->id");
        $sql->execute();
        $res = $sql->fetch();
        $idcommessa = $res['idcomm'];
        $costo = $res['costo'];

        $sql = $db->prepare("UPDATE commesse SET totalecosti = totalecosti-$costo WHERE id = $idcommessa");
        $sql->execute();

        parent::cancella();
    }

    function aggiungi($campi, $valori, $voci, $prevcoll) {
        global $db;
        parent::aggiungi($campi, $valori);
        $idcommessa = $db->lastInsertId();
        $old = umask(0);
        mkdir("./commesse/$idcommessa", 0777, true);
        umask($old);
        if ($voci) {
            $sql = $db->prepare("INSERT INTO commesse_voci (idcomm, nome, descr, idprofit, prezzocliente, costo, oreprev, statovoce, ordine) VALUES (?,?,?,?,?,?,?,?,?)");
            for ($i = 0; $i < count($voci); $i++) {
                $sql->execute(array($idcommessa, $voci[$i]['nome'], $voci[$i]['descr'], $voci[$i]['idprofit'], $voci[$i]['prezzocliente'], $voci[$i]['costo'], $voci[$i]['oreprev'], $voci[$i]['statovoce'], $voci[$i]['ordine']));
            }
        }
        if ($prevcoll) {
            $sql = $db->prepare("INSERT INTO commesse_preventivi (idcommessa, idpreventivo, numeropreventivo, datapreventivo, titolopreventivo, totalepreventivo) VALUES (?,?,?,?,?,?)");
            for ($i = 0; $i < count($prevcoll); $i++) {
                $sql->execute(array($idcommessa, $prevcoll[$i]['idpreventivo'], $prevcoll[$i]['numeropreventivo'], $prevcoll[$i]['datapreventivo'], $prevcoll[$i]['titolopreventivo'], $prevcoll[$i]['totalepreventivo']));
            }
        }
    }

    function cancella() {
        global $db;
        parent::cancella();
        /* cancello voci commessa */
        $sql = $db->prepare("DELETE FROM commesse_voci WHERE idcomm = ?");
        $sql->execute(array($this->id));
        /* cancello preventivi collegati */
        $sql = $db->prepare("DELETE FROM commesse_preventivi WHERE idcommessa = ?");
        $sql->execute(array($this->id));
        /* cancello contenuti e cartella */
        array_map('unlink', glob("./commesse/$this->id/*"));
        rmdir("./commesse/$this->id");
    }

    function richiama() {
        return parent::richiama();
    }

    function richiamavoci($where) {
        return parent::richiamaWhere($where);
    }

    function aggiorna($campi, $valori, $voci, $prevcoll) {
        global $db;
        /* aggiorno commessa */
        parent::aggiorna($campi, $valori);
        /* rimuovo voci */
        $sql = $db->prepare("DELETE FROM commesse_voci WHERE idcomm = ?");
        $sql->execute(array($this->id));
        /* rimuovo preventivi */
        $sql = $db->prepare("DELETE FROM commesse_preventivi WHERE idcommessa = ?");
        $sql->execute(array($this->id));
        /* aggiorno voci */
        if ($voci) {
            $sql = $db->prepare("INSERT INTO commesse_voci (id, idcomm, nome, descr, idprofit, prezzocliente, costo, oreprev, statovoce, ordine) VALUES (?,?,?,?,?,?,?,?,?,?)");
            for ($i = 0; $i < count($voci); $i++) {
                $sql->execute(array($voci[$i]['id'], $this->id, $voci[$i]['nome'], $voci[$i]['descr'], $voci[$i]['idprofit'], $voci[$i]['prezzocliente'], $voci[$i]['costo'], $voci[$i]['oreprev'], $voci[$i]['statovoce'], $voci[$i]['ordine']));
            }
        }
        if ($prevcoll) {
            $sql = $db->prepare("INSERT INTO commesse_preventivi (idcommessa, idpreventivo, numeropreventivo, datapreventivo, titolopreventivo, totalepreventivo) VALUES (?,?,?,?,?,?)");
            for ($i = 0; $i < count($prevcoll); $i++) {
                $sql->execute(array($this->id, $prevcoll[$i]['idpreventivo'], $prevcoll[$i]['numeropreventivo'], $prevcoll[$i]['datapreventivo'], $prevcoll[$i]['titolopreventivo'], $prevcoll[$i]['totalepreventivo']));
            }
        }
    }

}
