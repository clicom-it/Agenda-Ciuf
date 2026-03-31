<?php

class dipendenti extends basic {

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
    
    static function getCartelleFilesAtelier($idatelier, $idfather=0, $dir = 0) {
        global $db;
        $qry = "select * from docs_atelier where idatelier=$idatelier and dir=$dir and idfather=$idfather order by ordine;";
        $rs = $db->prepare($qry);
        $rs->execute();
        $cols = $rs->fetchAll(PDO::FETCH_ASSOC);
        return $cols;
    }
    
    static function addCartella($idatelier, $idfather) {
        global $db;
        $qry = "select MAX(ordine) from docs_atelier where idatelier=$idatelier and dir=1 and idfather=$idfather limit 1;";
        $rs = $db->prepare($qry);
        $rs->execute();
        $ordine = (int) $rs->fetchColumn();
        $ordine++;
        $qry = "insert into docs_atelier (idatelier, idfather, titolo, ordine, dir) values (?,?,?,?,?);";
        $rs = $db->prepare($qry);
        $rs->execute();
        $valori = Array($idatelier, $idfather, $_POST['titolo'], $ordine, 1);
        $rs->execute($valori);
        $id = $db->lastinsertId();
        return $id;
    }
    
    static function listCartelle($idatelier, $idfather, $dir = 1) {
        global $db;
        $qry = "select * from docs_atelier where idatelier=$idatelier and dir=$dir and idfather=$idfather order by ordine;";
        //die($qry);
        $rs = $db->prepare($qry);
        $rs->execute();
        $cols = $rs->fetchAll(PDO::FETCH_ASSOC);
        return $cols;
    }
}
