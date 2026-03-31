<?php

class ecommerce extends basic {

    public $id;
    public $tabella;

    function __construct($id, $tabella) {
        $this->id = $id;
        $this->tabella = $tabella;
    }

    function aggiungi($campi, $valori) {
        parent::aggiungi($campi, $valori);
    }

    function cancella() {
        global $db;
        parent::cancella();
        /* cancello voci ordine */
        $sql = $db->prepare("DELETE FROM ordini_voci WHERE idordine = ?");
        $sql->execute(array($this->id));
    }
    
    function richiama() {
        return parent::richiama();
    }
    
    function aggiorna($campi, $valori, $voci) {
        global $db;
        /* aggiorno ordine */
        parent::aggiorna($campi, $valori);
        /* rimuovo voci */
        $sql = $db->prepare("DELETE FROM ordini_voci WHERE idordine = ?");
        $sql->execute(array($this->id));
        /* aggiorno voci */
        if ($voci) {
            $sql = $db->prepare("INSERT INTO ordini_voci (idordine, nome, descr, qta, prezzo, sconto, scontato, ordine) VALUES (?,?,?,?,?,?,?,?)");
            for ($i = 0; $i < count($voci); $i++) {
                $sql->execute(array($this->id, $voci[$i]['nome'], $voci[$i]['descr'], $voci[$i]['qta'], $voci[$i]['prezzo'], $voci[$i]['sconto'], $voci[$i]['scontato'], $voci[$i]['ordine']));
            }
        }
    }

}
