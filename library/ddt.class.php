<?php

class ddt extends basic {

    public $id;
    public $tabella;

    function __construct($id, $tabella) {
        $this->id = $id;
        $this->tabella = $tabella;
    }

    function aggiungi($campi, $valori, $voci) {
        global $db;
        parent::aggiungi($campi, $valori);
        $idddt = $db->lastInsertId();
        $old = umask(0);
        mkdir("./ddt/$idddt", 0777, true);
        umask($old);
        if ($voci) {
            $sql = $db->prepare("INSERT INTO ddt_voci (idddt, nome, descr, qta, prezzo, sconto, scontato, ordine) VALUES (?,?,?,?,?,?,?,?)");
            for ($i = 0; $i < count($voci); $i++) {
                $sql->execute(array($idddt, $voci[$i]['nome'], $voci[$i]['descr'], $voci[$i]['qta'], $voci[$i]['prezzo'], $voci[$i]['sconto'], $voci[$i]['scontato'], $voci[$i]['ordine']));
            }
        }
    }

    function cancella() {
        global $db;
        parent::cancella();
        /* cancello voci preventivo */
        $sql = $db->prepare("DELETE FROM ddt_voci WHERE idddt = ?");
        $sql->execute(array($this->id));
        /* cancello contenuti e cartella */
        array_map('unlink', glob("./ddt/$this->id/*"));
        rmdir("./ddt/$this->id");
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
        $sql = $db->prepare("DELETE FROM ddt_voci WHERE idddt = ?");
        $sql->execute(array($this->id));
        /* aggiorno voci */
        if ($voci) {
            $sql = $db->prepare("INSERT INTO ddt_voci (idddt, nome, descr, qta, prezzo, sconto, scontato, ordine) VALUES (?,?,?,?,?,?,?,?)");
            for ($i = 0; $i < count($voci); $i++) {
                $sql->execute(array($this->id, $voci[$i]['nome'], $voci[$i]['descr'], $voci[$i]['qta'], $voci[$i]['prezzo'], $voci[$i]['sconto'], $voci[$i]['scontato'], $voci[$i]['ordine']));
            }
        }
    }

}
