<?php

class magazzino extends basic {

    public $id;
    public $tabella;

    function __construct($id, $tabella) {
        $this->id = $id;
        $this->tabella = $tabella;
    }

    function aggiungi($campi, $valori) {
        global $db;
        parent::aggiungi($campi, $valori);
        $idmagazzino = $db->lastInsertId();       
        
        return $idmagazzino;
    }

    function cancella() {
        parent::cancella();        
    }

    function richiama() {
        return parent::richiama();
    }

    function aggiorna($campi, $valori) {
        /* aggiorno magazzino */
        parent::aggiorna($campi, $valori);
    }

}
