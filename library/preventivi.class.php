<?php

class preventivi extends basic {

    public $id;
    public $tabella;

    function __construct($id, $tabella) {
        $this->id = $id;
        $this->tabella = $tabella;
    }

    function aggiungi($campi, $valori, $voci) {
        global $db;
        parent::aggiungi($campi, $valori);
        $idpreventivo = $db->lastInsertId();
        $old = umask(0);
        mkdir("./preventivi/$idpreventivo", 0777, true);
        umask($old);
        if ($voci) {
            $sql = $db->prepare("INSERT INTO preventivi_voci (idprev, nome, descr, qta, prezzo, sconto, scontato, ordine) VALUES (?,?,?,?,?,?,?,?)");
            for ($i = 0; $i < count($voci); $i++) {
                $sql->execute(array($idpreventivo, $voci[$i]['nome'], $voci[$i]['descr'], $voci[$i]['qta'], $voci[$i]['prezzo'], $voci[$i]['sconto'], $voci[$i]['scontato'], $voci[$i]['ordine']));
            }
        }
    }

    function cancella() {
        global $db;
        parent::cancella();
        /* cancello voci preventivo */
        $sql = $db->prepare("DELETE FROM preventivi_voci WHERE idprev = ?");
        $sql->execute(array($this->id));
        /* cancello contenuti e cartella */
        array_map('unlink', glob("./preventivi/$this->id/*"));
        rmdir("./preventivi/$this->id");
    }

    function richiama() {
        return parent::richiama();
    }
    
    function richiamavoci($where) {
        return parent::richiamaWhere($where);
    }

    function aggiorna($campi, $valori, $voci) {
        global $db;
        /* aggiorno preventivo */
        parent::aggiorna($campi, $valori);
        /* rimuovo voci */
        $sql = $db->prepare("DELETE FROM preventivi_voci WHERE idprev = ?");
        $sql->execute(array($this->id));
        /* aggiorno voci */
        if ($voci) {
            $sql = $db->prepare("INSERT INTO preventivi_voci (idprev, nome, descr, qta, prezzo, sconto, scontato, ordine) VALUES (?,?,?,?,?,?,?,?)");
            for ($i = 0; $i < count($voci); $i++) {
                $sql->execute(array($this->id, $voci[$i]['nome'], $voci[$i]['descr'], $voci[$i]['qta'], $voci[$i]['prezzo'], $voci[$i]['sconto'], $voci[$i]['scontato'], $voci[$i]['ordine']));
            }
        }
    }

}
