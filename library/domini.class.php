<?php

class domini extends basic {

    public $id;
    public $tabella;

    function __construct($id, $tabella) {
        $this->id = $id;
        $this->tabella = $tabella;
    }

    function aggiungi($campi, $valori) {
        global $db;
        parent::aggiungi($campi, $valori);
        $iddominio = $db->lastInsertId();        
        
        return $iddominio;
    }

    function cancella() {
        parent::cancella();        
    }

    function richiama() {
        return parent::richiama();
    }

    function aggiorna($campi, $valori) {
        /* aggiorno dominio */
        parent::aggiorna($campi, $valori);
    }

}
