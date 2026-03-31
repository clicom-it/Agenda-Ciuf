<?php

class calendario extends basic {

    public $id;
    public $tabella;

    function __construct($id, $tabella) {
        $this->id = $id;
        $this->tabella = $tabella;
    }

    function aggiungi($campi, $valori) {
        global $db;
        parent::aggiungi($campi, $valori);
        $idappuntamento = $db->lastInsertId();
        $old = umask(0);
        mkdir("./appuntamenti/$idappuntamento", 0777, true);
        umask($old);
    }
    
    function aggiungiFile($campi, $valori) {
        global $db;
        parent::aggiungi($campi, $valori);
        $idfile = $db->lastInsertId();
        return $idfile;
    }

    function cancella() {
        global $db;
        $qry = "insert into calendario_del select * from calendario where id=$this->id;";
        //die($qry);
        $db->exec($qry);
        parent::cancella();
        /* cancello contenuti e cartella */
        array_map('unlink', glob("./appuntamenti/$this->id/*"));
        rmdir("./appuntamenti/$this->id");
    }
    
    function delFile($idappuntamento, $file) {
        unlink("./appuntamenti/$idappuntamento/$file");
        parent::cancella();
    }
    
    function richiama() {
        return parent::richiama();
    }
    
    function aggiorna($campi, $valori) {
        parent::aggiorna($campi, $valori);
    }

}
