<?php

class Budget extends basic {

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
        parent::cancella();
    }
    
    function richiama() {
        return parent::richiama();
    }
    
    function aggiorna($campi, $valori) {
        parent::aggiorna($campi, $valori);
    }

}
