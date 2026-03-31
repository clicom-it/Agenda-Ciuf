<?php

class impostazioni extends basic {

    public $id;
    public $tabella;

    function __construct($id, $tabella) {
        $this->id = $id;
        $this->tabella = $tabella;
    }

    function aggiungiiva($campi, $valori) {
        parent::aggiungi($campi, $valori);
    }

    function cancellaiva() {
        parent::cancella();
    }
    
    function richiamaiva() {
        return parent::richiamaNocond();
    }
    
    function aggiornaiva($campi, $valori) {
        parent::aggiorna($campi, $valori);
    }
    
    function aggiungimetodo($campi, $valori) {
        parent::aggiungi($campi, $valori);
    }
    
    function richiamametodo() {
        return parent::richiamaNocond();
    }
    
    function aggiornametodo($campi, $valori) {
        parent::aggiorna($campi, $valori);
    }
    
    function cancellametodo() {
        parent::cancella();
    }
    
    function aggiornautente($campi, $valori) {
        parent:: aggiorna($campi, $valori);
    }
    
    function aggiungiutente($campi, $valori) {
        parent:: aggiungi($campi, $valori);
    }
    
    function richiamaprofit($where) {
        return parent::richiamaWhere($where);
    }
    
    function cancellaprofit($where) {
        parent::cancella();
        parent::cancellaWhere($where);
    }
    
    function aggiornaprofit($campi, $valori) {
        parent::aggiorna($campi, $valori);
    }
    
    function aggiungiprofit($campi, $valori) {
        parent::aggiungi($campi, $valori);
    }
    
    function richiamacentro($where) {
        return parent::richiamaWhere($where);
    }
    
    function aggiungicentro($campi, $valori) {
        parent::aggiungi($campi, $valori);
    }
    
    function aggiornacentro($campi, $valori) {
        parent::aggiorna($campi, $valori);
    }
    
    function cancellacentro() {
        parent::cancella();
    }
    
    function richiamaaccessori($where) {
        return parent::richiamaWhere($where);
    }
    
    function cancellaaccessori($where) {
        parent::cancella();
        parent::cancellaWhere($where);
    }
    
    function aggiornaaccessori($campi, $valori) {
        parent::aggiorna($campi, $valori);
    }
    
    function aggiungiaccessori($campi, $valori) {
        parent::aggiungi($campi, $valori);
    }
    
    function richiamasartoria($where) {
        return parent::richiamaWhere($where);
    }
    
    function cancellasartoria($where) {
        parent::cancella();
        parent::cancellaWhere($where);
    }
    
    function aggiornasartoria($campi, $valori) {
        parent::aggiorna($campi, $valori);
    }
    
    function aggiungisartoria($campi, $valori) {
        parent::aggiungi($campi, $valori);
    }
    
    function richiamamotivono($where) {
        return parent::richiamaWhere($where);
    }
    
    function cancellamotivono($where) {
        parent::cancella();
        parent::cancellaWhere($where);
    }
    
    function aggiornamotivono($campi, $valori) {
        parent::aggiorna($campi, $valori);
    }
    
    function aggiungimotivono($campi, $valori) {
        parent::aggiungi($campi, $valori);
    }
    
    function richiamabollini($where) {
        return parent::richiamaWhere($where);
    }
    
    function cancellabollini($where) {
        parent::cancella();
        parent::cancellaWhere($where);
    }
    
    function aggiornabollini($campi, $valori) {
        parent::aggiorna($campi, $valori);
    }
    
    function aggiungibollini($campi, $valori) {
        parent::aggiungi($campi, $valori);
    }
}
