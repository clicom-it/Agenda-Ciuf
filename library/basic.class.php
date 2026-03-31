<?php

class basic {
    

    public function stampaGen($where) {
        global $db;
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $sql = $db->prepare('SELECT * FROM ' . $this->tabella . ' WHERE ' . $where . ' ORDER BY ordine');
        try {
            $sql->execute();
            $res = $sql->fetchAll(PDO::FETCH_ASSOC);
            return $res;
        } catch (PDOException $e) {
            die('Connessione fallita: ' . $e->getMessage());
        }
    }

    /* tutti i dati senza "criterio" */

    public function richiama() {
        global $db;
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $sql = $db->prepare("SELECT * FROM $this->tabella WHERE id = ?");
        try {
            $sql->execute(array($this->id));
            $res = $sql->fetchAll(PDO::FETCH_ASSOC);
            return $res;
        } catch (PDOException $e) {
            die('Connessione fallita: ' . $e->getMessage());
        }
    }

    /* tutti i dati con "criterio" */

    public function richiamaWhere($where) {
        global $db;
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $sql = $db->prepare("SELECT * FROM $this->tabella WHERE $where");
        try {
            $sql->execute();
            $res = $sql->fetchAll(PDO::FETCH_ASSOC);
            return $res;
        } catch (PDOException $e) {
            die('Connessione fallita: ' . $e->getMessage());
        }
    }
    
    public function richiamaWheredata($where) {
        global $db;
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $sql = $db->prepare("SELECT *, DATE_FORMAT(data, '%d/%m/%Y') as datait FROM $this->tabella WHERE $where");
        try {
            $sql->execute();
            $res = $sql->fetchAll(PDO::FETCH_ASSOC);
            return $res;
        } catch (PDOException $e) {
            die('Connessione fallita: ' . $e->getMessage());
        }
    }
    
     public function richiamaWhereOrdine($where) {
        global $db;
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $sql = $db->prepare("SELECT *, DATE_FORMAT(data, '%d/%m/%Y') as datait FROM $this->tabella WHERE $where");
        try {
            $sql->execute();
            $res = $sql->fetchAll(PDO::FETCH_ASSOC);
            return $res;
        } catch (PDOException $e) {
            die('Connessione fallita: ' . $e->getMessage());
        }
    }
    
    public function richiamaWheredatascad($where) {
        global $db;
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $sql = $db->prepare("SELECT *, DATE_FORMAT(datascadenza, '%d/%m/%Y') as datait FROM $this->tabella WHERE $where");
        try {
            $sql->execute();
            $res = $sql->fetchAll(PDO::FETCH_ASSOC);
            return $res;
        } catch (PDOException $e) {
            die('Connessione fallita: ' . $e->getMessage());
        }
    }
    
    public function richiamaNocond() {
        global $db;
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $sql = $db->prepare("SELECT * FROM $this->tabella");
        try {
            $sql->execute();
            $res = $sql->fetchAll(PDO::FETCH_ASSOC);
            return $res;
        } catch (PDOException $e) {
            die('Connessione fallita: ' . $e->getMessage());
        }
    }

    /* aggiungi */

    public function aggiungi($campi, $valori) {
        global $db;

        $interrogativo = array();
        foreach ($valori as $val) {
            $interrogativo[] = "?";
        }

        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $sql = $db->prepare('INSERT INTO ' . $this->tabella . '(' . join(",", $campi) . ') VALUES (' . join(",", $interrogativo) . ')');
        try {
            $sql->execute($valori);
        } catch (PDOException $e) {
            die('Connessione fallita: ' . $e->getMessage());
        }
    }
    
    public function aggiungigen($campi, $valori) {
        global $db;

        $interrogativo = array();
        foreach ($valori as $val) {
            $interrogativo[] = "?";
        }

        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $sql = $db->prepare('INSERT INTO ' . $this->tabella . '(' . join(",", $campi) . ') VALUES (' . join(",", $interrogativo) . ')');
        try {
            $sql->execute($valori);
        } catch (PDOException $e) {
            die('Connessione fallita: ' . $e->getMessage());
        }
    }

    /* modifica */

    public function modifica($campi, $valori) {
        global $db;

        array_push($valori, $this->id, $this->lang);

        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $sql = $db->prepare('UPDATE ' . $this->tabella . ' SET ' . join("= ?,", $campi) . '= ? WHERE idl = ? AND lang = ?');
        try {
            $sql->execute($valori);
        } catch (PDOException $e) {
            die('Connessione fallita: ' . $e->getMessage());
        }
    }

    /* modifica generico */

    public function aggiorna($campi, $valori) {
        global $db;

        array_push($valori, $this->id);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $sql = $db->prepare('UPDATE ' . $this->tabella . ' SET ' . join("= ?,", $campi) . '= ? WHERE id = ?');
        
        
        try {
            $sql->execute($valori);
        } catch (PDOException $e) {
            die('Connessione fallita: ' . $e->getMessage());
        }
    }

    public function modificaTuttelingue($campi, $valori) {
        global $db;

        array_push($valori, $this->id);

        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $sql = $db->prepare('UPDATE ' . $this->tabella . ' SET ' . join("= ?,", $campi) . '= ? WHERE idl = ?');
        try {
            $sql->execute($valori);
        } catch (PDOException $e) {
            die('Connessione fallita: ' . $e->getMessage());
        }
    }

    /* cancella */

    public function cancella() {
        global $db;
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $sql = $db->prepare('DELETE FROM ' . $this->tabella . ' WHERE id = ' . $this->id . '');
        try {
            $sql->execute();
        } catch (PDOException $e) {
            die('Connessione fallita: ' . $e->getMessage());
        }
    }
    
    public function cancellaWhere($where) {
        global $db;
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $sql = $db->prepare('DELETE FROM ' . $this->tabella . ' WHERE '.$where.'');
        try {
            $sql->execute();
        } catch (PDOException $e) {
            die('Connessione fallita: ' . $e->getMessage());
        }
    }

}
