<?php

if (isset($_COOKIE['cookieid'])) {
    session_start();

    if (!isset($_SESSION['username']) && !isset($_SESSION['password'])) {
        $sql = $db->prepare("SELECT *,(select assistant_id from AI_utenti ai where u.id=ai.idutente limit 1) as assistant_id FROM utenti u WHERE MD5(id) = ? LIMIT 1");
        try {
            $sql->execute(array($_COOKIE['cookieid']));
        } catch (PDOException $e) {
            die('Connessione fallita: ' . $e->getMessage());
        }
        
        if ($sql->rowCount() > 0) {
            $res = $sql->fetch(PDO::FETCH_ASSOC);
            $_SESSION["id"] = $res["id"];
            $_SESSION["nome"] = $res["nome"];
//            $_SESSION["email"] = $res["email"];
            /* per sessione */
            $_SESSION["username"] = $res["email"];
            $_SESSION["password"] = $res["password"];
            /* moduli attivi e livello */
            $_SESSION["livello"] = $res["livello"];
            $_SESSION["dipendenti"] = $res["dipendenti"];
            $_SESSION["clientifornitori"] = $res["clientifornitori"];
            $_SESSION["preventivi"] = $res["preventivi"];
            $_SESSION["commesse"] = $res["commesse"];
            $_SESSION["fatture"] = $res["fatture"];
            $_SESSION["scadenziario"] = $res["scadenziario"];
            $_SESSION["partitario"] = $res["partitario"];
            $_SESSION["impostazioni"] = $res["impostazioni"];
            $_SESSION["ore"] = $res["ore"];
            $_SESSION["statistiche"] = $res["statistiche"];
            $_SESSION["magazzino"] = $res["magazzino"];   
            $_SESSION["ddt"] = $res["ddt"];   
            $_SESSION["ecommerce"] = $res["ecommerce"];  
            $_SESSION["solo_sartoria"] = $res["solo_sartoria"];
            $_SESSION["diraff"] = $res["diraff"];
            $_SESSION["ruolo"] = $res["ruolo"];
            $_SESSION["assistant_id"] = $res["assistant_id"];
            $_SESSION["stato_dipendente"] = $res["stato_dipendente"]; 
            $idatelier = $res['idatelier'];
            if ($idatelier == 0) {
                $_SESSION['idatelier'] = $res['id'];
            } else {
                $_SESSION['idatelier'] = $res['idatelier'];
            }
            $qry = "select * from atelier_utente WHERE idutente = " . $_SESSION['id'] . ";";
            $rs2 = $db->prepare($qry);
            $rs2->execute();
            $atelier_collegati = $rs2->fetchAll(PDO::FETCH_ASSOC);
            $_SESSION['atelier_collegati'] = Array();
            if (count($atelier_collegati) > 0) {
                $_SESSION["statistiche"] = 1;
                $_SESSION['atelier_collegati'][] = $_SESSION['idatelier'];
                foreach ($atelier_collegati as $at) {
                    if (!in_array($at['idatelier'], $_SESSION['atelier_collegati'])) {
                        $_SESSION['atelier_collegati'][] = $at['idatelier'];
                    }
                }
            }
            $sql2 = $db->prepare("SELECT email FROM utenti WHERE id = ?");
            $sql2->execute(array($_SESSION['idatelier']));
            $_SESSION['email'] = $sql2->fetchColumn();

        }
    }
    
} else {
    header("Location:./index.php");
    die();
}
