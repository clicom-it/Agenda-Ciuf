<?php

class scadenziario extends basic {

    public $id;
    public $tabella;

    function __construct($id, $tabella) {
        $this->id = $id;
        $this->tabella = $tabella;
    }
    function aggiorna($campi, $valori) {
        global $db;
        parent::aggiorna($campi, $valori);
        /* recupero idfattura */
        $sql = $db->prepare("SELECT idfattura FROM fatture_scadenze WHERE id = ? LIMIT 1");
        $sql->execute(array($this->id));
        $idfattura = $sql->fetchColumn();
        /* controllo le scadenze non pagate */
        $sql = $db->prepare("SELECT * FROM fatture_scadenze WHERE idfattura = ? AND stato = ? AND sufattura = ?");
        $sql->execute(array($idfattura, '0', '1'));
        if ($sql->rowCount() > 0) {
            $sql = $db->prepare("UPDATE fatture SET stato = ? WHERE id = ?");
            $sql->execute(array('0', $idfattura));
        } else {
            $sql = $db->prepare("UPDATE fatture SET stato = ? WHERE id = ?");
            $sql->execute(array('1', $idfattura));
        }
    }
}
