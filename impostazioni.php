<?php
ob_start('ob_gzhandler');
include './library/connessione.php';
include './library/controllo.php';
include './library/config.php';
include './library/functions.php';
include './library/basic.class.php';
/* libreria del modulo */
include './library/impostazioni.class.php';
/**/

$op = $_GET['op'];

$submit = "";
if (isset($_POST['submit'])) {
    $submit = $_POST['submit'];
}

switch ($submit) {
    /* sartoria */
    case "mostrasartoria":
        $idp = $_POST['idp'];
        if (!$idp) {
            $idp = 0;
        } else {
            $datipro = getDati("sartoria", "WHERE id = $idp");
            $titprofit = $datipro[0]['nome'];
        }
        $tabella = "sartoria";
        $dati = new impostazioni($id, $tabella);
        $profit = $dati->richiamasartoria("idp = $idp");

        die('{"titprofit": "' . $titprofit . '", "dati" : ' . json_encode($profit) . '}');
        break;

    case "deletesartoria":
        $tabella = "sartoria";
        $id = $_POST['id'];
        $dati = new impostazioni($id, $tabella);

        $dati->cancellasartoria("id = $id");

        die('{"msg": "ok"}');
        break;

    case "editsartoria":
        $tabella = "sartoria";
        $id = $_POST['id'];

        $dati = new impostazioni($id, $tabella);

        unset($_POST['submit']);

        foreach ($_POST as $k => $v) {
            $campi[] = $k;
            $valori[] = $v;
        }

        $dati->aggiornasartoria($campi, $valori);
        die('{"msg": "ok"}');
        break;

    case "insertsartoria":
        $tabella = "sartoria";
        $dati = new impostazioni($id, $tabella);

        unset($_POST['submit']);


        foreach ($_POST as $k => $v) {
            $campi[] = $k;
            $valori[] = $v;
        }

        $dati->aggiungisartoria($campi, $valori);
        die('{"msg": "ok"}');
        break;
    /* accessori */
    case "mostraaccessori":
        $idp = $_POST['idp'];
        if (!$idp) {
            $idp = 0;
        } else {
            $datipro = getDati("accessori", "WHERE id = $idp");
            $titprofit = $datipro[0]['nome'];
        }
        $tabella = "accessori";
        $dati = new impostazioni($id, $tabella);
        $profit = $dati->richiamaaccessori("idp = $idp");

        die('{"titprofit": "' . $titprofit . '", "dati" : ' . json_encode($profit) . '}');
        break;

    case "deleteaccessori":
        $tabella = "accessori";
        $id = $_POST['id'];
        $dati = new impostazioni($id, $tabella);

        $dati->cancellaaccessori("id = $id");

        die('{"msg": "ok"}');
        break;

    case "editaccessori":
        $tabella = "accessori";
        $id = $_POST['id'];

        $dati = new impostazioni($id, $tabella);

        unset($_POST['submit']);

        foreach ($_POST as $k => $v) {
            $campi[] = $k;
            $valori[] = $v;
        }

        $dati = $dati->aggiornaaccessori($campi, $valori);
        die('{"msg": "ok"}');
        break;

    case "insertaccessori":
        $tabella = "accessori";
        $dati = new impostazioni($id, $tabella);

        unset($_POST['submit']);


        foreach ($_POST as $k => $v) {
            $campi[] = $k;
            $valori[] = $v;
        }

        $dati = $dati->aggiungiaccessori($campi, $valori);
        die('{"msg": "ok"}');
        break;
    /* motivo no */
    case "mostramotivono":
        $idp = $_POST['idp'];
        if (!$idp) {
            $idp = 0;
        } else {
            $datipro = getDati("motivonoacq", "WHERE id = $idp");
            $titprofit = $datipro[0]['nome'];
        }
        $tabella = "motivonoacq";
        $dati = new impostazioni($id, $tabella);
        $profit = $dati->richiamamotivono("idp = $idp");

        die('{"titprofit": "' . $titprofit . '", "dati" : ' . json_encode($profit) . '}');
        break;

    case "deletemotivono":
        $tabella = "motivonoacq";
        $id = $_POST['id'];
        $dati = new impostazioni($id, $tabella);

        $dati->cancellamotivono("id = $id");

        die('{"msg": "ok"}');
        break;

    case "editmotivono":
        $tabella = "motivonoacq";
        $id = $_POST['id'];

        $dati = new impostazioni($id, $tabella);

        unset($_POST['submit']);

        foreach ($_POST as $k => $v) {
            $campi[] = $k;
            $valori[] = $v;
        }

        $dati = $dati->aggiornamotivono($campi, $valori);
        die('{"msg": "ok"}');
        break;

    case "insertmotivono":
        $tabella = "motivonoacq";
        $dati = new impostazioni($id, $tabella);

        unset($_POST['submit']);


        foreach ($_POST as $k => $v) {
            $campi[] = $k;
            $valori[] = $v;
        }

        $dati = $dati->aggiungimotivono($campi, $valori);
        die('{"msg": "ok"}');
        break;
    /* bollini */
    case "mostrabollini":
        $idp = $_POST['idp'];
        if (!$idp) {
            $idp = 0;
        } else {
            $datipro = getDati("bollini", "WHERE id = $idp");
            $titprofit = $datipro[0]['nome'];
        }
        $tabella = "bollini";
        $dati = new impostazioni($id, $tabella);
        $profit = $dati->richiamabollini("idp = $idp");

        die('{"titprofit": "' . $titprofit . '", "dati" : ' . json_encode($profit) . '}');
        break;

    case "deletebollino":
        $tabella = "bollini";
        $id = $_POST['id'];
        $dati = new impostazioni($id, $tabella);

        $dati->cancellabollini("id = $id");

        die('{"msg": "ok"}');
        break;

    case "editbollino":
        $tabella = "bollini";
        $id = $_POST['id'];

        $dati = new impostazioni($id, $tabella);

        unset($_POST['submit']);

        foreach ($_POST as $k => $v) {
            $campi[] = $k;
            $valori[] = $v;
        }

        $dati = $dati->aggiornabollini($campi, $valori);
        die('{"msg": "ok"}');
        break;

    case "insertbollino":
        $tabella = "bollini";
        $dati = new impostazioni($id, $tabella);

        unset($_POST['submit']);


        foreach ($_POST as $k => $v) {
            $campi[] = $k;
            $valori[] = $v;
        }

        $dati = $dati->aggiungibollini($campi, $valori);
        die('{"msg": "ok"}');
        break;
    /* e-commerce */
    case "sendformecommerce":

        $tabella = "conf_ecommerce";
        $dati = new impostazioni($id, $tabella);

        unset($_POST['submit']);

        if ($_POST['pass_db'] == "") {
            unset($_POST['pass_db']);
        }

        foreach ($_POST as $k => $v) {
            $campi[] = $k;
            $valori[] = $v;
        }
        $sql = $db->prepare("SELECT id FROM $tabella");
        $sql->execute();
        if ($sql->rowCount() > 0) {
            $id = $sql->fetchColumn();
            $dati = new impostazioni($id, $tabella);
            $dati->aggiorna($campi, $valori);
        } else {
            $dati = new impostazioni($id, $tabella);
            $dati->aggiungi($campi, $valori);
        }
        die('{"msg": "ok"}');

        break;

    /* gestione iva */
    case "insertiva":
        $tabella = "iva";
        $dati = new impostazioni($id, $tabella);

        unset($_POST['submit']);

        if ($_POST['predefinito'] == "false") {
            $_POST['predefinito'] = 0;
        } else {
            $_POST['predefinito'] = 1;
            $sql = $db->prepare("UPDATE $tabella SET predefinito = ?");
            $sql->execute(array(0));
        }

        foreach ($_POST as $k => $v) {
            $campi[] = $k;
            $valori[] = $v;
        }

        $dati = $dati->aggiungiiva($campi, $valori);
        die('{"msg": "ok"}');
        break;

    case "editiva":
        $tabella = "iva";
        $id = $_POST['id'];
        $dati = new impostazioni($id, $tabella);

        unset($_POST['submit']);

        if ($_POST['predefinito'] == "false") {
            $_POST['predefinito'] = 0;
        } else {
            $_POST['predefinito'] = 1;
            $sql = $db->prepare("UPDATE $tabella SET predefinito = ?");
            $sql->execute(array(0));
        }

        foreach ($_POST as $k => $v) {
            $campi[] = $k;
            $valori[] = $v;
        }

        $dati = $dati->aggiornaiva($campi, $valori);
        die('{"msg": "ok"}');
        break;

    case "mostraiva":
        $tabella = "iva";
        $dati = new impostazioni($id, $tabella);
        $iva = $dati->richiamaiva();

        for ($i = 0; $i < count($iva); $i++) {
            if ($iva[$i]['predefinito'] == "0") {
                $iva[$i]['predefinito'] = false;
            } else {
                $iva[$i]['predefinito'] = true;
            }
        }

        die('{"dati" : ' . json_encode($iva) . '}');
        break;

    case "deleteiva":
        $tabella = "iva";
        $id = $_POST['id'];
        $dati = new impostazioni($id, $tabella);

        $dati->cancellaiva();

        die('{"msg": "ok"}');
        break;

    /* centri di costo */

    case "mostracentridicosto":
        $datip = getDati("centri_costo", "WHERE id = 0");
        $titp = $dati[0]['nome'];
        $tabella = "centri_costo";
        $dati = new impostazioni($id, $tabella);
        $centricosto = $dati->richiamacentro("idp = 0");

        die('{"titprofit": "' . $titprofit . '", "dati" : ' . json_encode($centricosto) . '}');
        break;

    case "insertcentro":
        $tabella = "centri_costo";
        $dati = new impostazioni($id, $tabella);

        unset($_POST['submit']);


        foreach ($_POST as $k => $v) {
            $campi[] = $k;
            $valori[] = $v;
        }

        $dati = $dati->aggiungicentro($campi, $valori);
        die('{"msg": "ok"}');
        break;

    case "editcentro":
        $tabella = "centri_costo";
        $id = $_POST['id'];

        $dati = new impostazioni($id, $tabella);

        unset($_POST['submit']);

        foreach ($_POST as $k => $v) {
            $campi[] = $k;
            $valori[] = $v;
        }

        $dati = $dati->aggiornacentro($campi, $valori);
        die('{"msg": "ok"}');
        break;

    case "deletecentro":
        $tabella = "centri_costo";
        $id = $_POST['id'];
        $dati = new impostazioni($id, $tabella);

        $dati->cancellacentro();

        die('{"msg": "ok"}');
        break;

    /* profit center */

    case "mostraprofitcenter":
        $idp = $_POST['idp'];
        if (!$idp) {
            $idp = 0;
        } else {
            $datipro = getDati("profit_center", "WHERE id = $idp");
            $titprofit = $datipro[0]['nome'];
        }
        $tabella = "profit_center";
        $dati = new impostazioni($id, $tabella);
        $profit = $dati->richiamaprofit("idp = $idp");

        die('{"titprofit": "' . $titprofit . '", "dati" : ' . json_encode($profit) . '}');
        break;

    case "deleteprofit":
        $tabella = "profit_center";
        $id = $_POST['id'];
        $dati = new impostazioni($id, $tabella);

        $dati->cancellaprofit("idp = $id");

        die('{"msg": "ok"}');
        break;

    case "editprofit":
        $tabella = "profit_center";
        $id = $_POST['id'];

        $dati = new impostazioni($id, $tabella);

        unset($_POST['submit']);

        foreach ($_POST as $k => $v) {
            $campi[] = $k;
            $valori[] = $v;
        }

        $dati = $dati->aggiornaprofit($campi, $valori);
        die('{"msg": "ok"}');
        break;

    case "insertprofit":
        $tabella = "profit_center";
        $dati = new impostazioni($id, $tabella);

        unset($_POST['submit']);


        foreach ($_POST as $k => $v) {
            $campi[] = $k;
            $valori[] = $v;
        }

        $dati = $dati->aggiungiprofit($campi, $valori);
        die('{"msg": "ok"}');
        break;

    /* metodi di pagamento */

    case "mostrametodi":
        $tabella = "metodi_pagamento";
        $dati = new impostazioni($id, $tabella);
        $metodi = $dati->richiamametodo();

        die('{"dati" : ' . json_encode($metodi) . '}');
        break;

    case "insertmetodo":
        $tabella = "metodi_pagamento";
        $dati = new impostazioni($id, $tabella);

        unset($_POST['submit']);


        foreach ($_POST as $k => $v) {
            $campi[] = $k;
            $valori[] = $v;
        }

        $dati = $dati->aggiungimetodo($campi, $valori);
        die('{"msg": "ok"}');
        break;

    case "editmetodo":
        $tabella = "metodi_pagamento";
        $id = $_POST['id'];

        $dati = new impostazioni($id, $tabella);

        unset($_POST['submit']);

        foreach ($_POST as $k => $v) {
            $campi[] = $k;
            $valori[] = $v;
        }

        $dati = $dati->aggiornametodo($campi, $valori);
        die('{"msg": "ok"}');
        break;

    case "deletemetodo":
        $tabella = "metodi_pagamento";
        $id = $_POST['id'];
        $dati = new impostazioni($id, $tabella);

        $dati->cancellametodo();

        die('{"msg": "ok"}');
        break;

    /* dati gestionale e logo */

    case "sendlogo":
        $nomefile = $_POST['nomefile'];
        $tabella = "dati_gestionale";

        $campi[] = "logo";
        $valori[] = $nomefile;

        $sql = $db->prepare("SELECT id FROM $tabella");
        $sql->execute();
        if ($sql->rowCount() > 0) {
            $id = $sql->fetchColumn();
            $dati = new impostazioni($id, $tabella);
            $dati->aggiorna($campi, $valori);
        } else {
            $dati = new impostazioni($id, $tabella);
            $dati->aggiungi($campi, $valori);
            $id = $db->lastInsertId();
        }
        die('{"msg": "ok", "id" : "' . $id . '"}');
        break;

    case "dellogo":
        $tabella = "dati_gestionale";
        $id = $_POST['id'];
        $campi[] = "logo";
        $valori[] = "";
        $dati = new impostazioni($id, $tabella);
        $dati->aggiorna($campi, $valori);
        array_map('unlink', glob("./immagini/logo/*"));
        die('{"msg": "ok"}');
        break;

    case "sendformdatiazienda":
        $tabella = "dati_gestionale";
        $dati = new impostazioni($id, $tabella);

        unset($_POST['submit']);

        foreach ($_POST as $k => $v) {
            $campi[] = $k;
            $valori[] = $v;
        }
        $sql = $db->prepare("SELECT id FROM $tabella");
        $sql->execute();
        if ($sql->rowCount() > 0) {
            $id = $sql->fetchColumn();
            $dati = new impostazioni($id, $tabella);
            $dati->aggiorna($campi, $valori);
        } else {
            $dati = new impostazioni($id, $tabella);
            $dati->aggiungi($campi, $valori);
        }
        die('{"msg": "ok"}');
        break;

    /* utenti gestionale */

    case "mostrautentipermessi":
        $tabella = "utenti";
        $dati = new impostazioni($id, $tabella);
        $where = "livello != '0'";
        $utenti = $dati->richiamaWhere($where);
        for ($i = 0; $i < count($utenti); $i++) {
            if ($utenti[$i]['dipendenti'] == 0) {
                $utenti[$i]['dipendenti'] = false;
            } else {
                $utenti[$i]['dipendenti'] = true;
            }
            if ($utenti[$i]['clientifornitori'] == 0) {
                $utenti[$i]['clientifornitori'] = false;
            } else {
                $utenti[$i]['clientifornitori'] = true;
            }
            if ($utenti[$i]['preventivi'] == 0) {
                $utenti[$i]['preventivi'] = false;
            } else {
                $utenti[$i]['preventivi'] = true;
            }
            if ($utenti[$i]['commesse'] == 0) {
                $utenti[$i]['commesse'] = false;
            } else {
                $utenti[$i]['commesse'] = true;
            }
            if ($utenti[$i]['fatture'] == 0) {
                $utenti[$i]['fatture'] = false;
            } else {
                $utenti[$i]['fatture'] = true;
            }
            if ($utenti[$i]['scadenziario'] == 0) {
                $utenti[$i]['scadenziario'] = false;
            } else {
                $utenti[$i]['scadenziario'] = true;
            }
            if ($utenti[$i]['partitario'] == 0) {
                $utenti[$i]['partitario'] = false;
            } else {
                $utenti[$i]['partitario'] = true;
            }
            if ($utenti[$i]['impostazioni'] == 0) {
                $utenti[$i]['impostazioni'] = false;
            } else {
                $utenti[$i]['impostazioni'] = true;
            }
            if ($utenti[$i]['ore'] == 0) {
                $utenti[$i]['ore'] = false;
            } else {
                $utenti[$i]['ore'] = true;
            }
            if ($utenti[$i]['statistiche'] == 0) {
                $utenti[$i]['statistiche'] = false;
            } else {
                $utenti[$i]['statistiche'] = true;
            }
            if ($utenti[$i]['magazzino'] == 0) {
                $utenti[$i]['magazzino'] = false;
            } else {
                $utenti[$i]['magazzino'] = true;
            }
            if ($utenti[$i]['ddt'] == 0) {
                $utenti[$i]['ddt'] = false;
            } else {
                $utenti[$i]['ddt'] = true;
            }
            if ($utenti[$i]['ecommerce'] == 0) {
                $utenti[$i]['ecommerce'] = false;
            } else {
                $utenti[$i]['ecommerce'] = true;
            }
        }

        die('{"dati" : ' . json_encode($utenti) . '}');
        break;

    case "editutente":
        $tabella = "utenti";
        $id = $_POST['id'];
        unset($_POST['password']);
        if (strlen($_POST['pass']) > 0) {
            $_POST['password'] = $_POST['pass'];
        }

        unset($_POST['submit']);
        unset($_POST['pass']);

        if ($_POST['dipendenti'] == "false") {
            $_POST['dipendenti'] = 0;
        } else {
            $_POST['dipendenti'] = 1;
        }
        if ($_POST['clientifornitori'] == "false") {
            $_POST['clientifornitori'] = 0;
        } else {
            $_POST['clientifornitori'] = 1;
        }
        if ($_POST['preventivi'] == "false") {
            $_POST['preventivi'] = 0;
        } else {
            $_POST['preventivi'] = 1;
        }
        if ($_POST['commesse'] == "false") {
            $_POST['commesse'] = 0;
        } else {
            $_POST['commesse'] = 1;
        }
        if ($_POST['fatture'] == "false") {
            $_POST['fatture'] = 0;
        } else {
            $_POST['fatture'] = 1;
        }
        if ($_POST['scadenziario'] == "false") {
            $_POST['scadenziario'] = 0;
        } else {
            $_POST['scadenziario'] = 1;
        }
        if ($_POST['partitario'] == "false") {
            $_POST['partitario'] = 0;
        } else {
            $_POST['partitario'] = 1;
        }
        if ($_POST['impostazioni'] == "false") {
            $_POST['impostazioni'] = 0;
        } else {
            $_POST['impostazioni'] = 1;
        }
        if ($_POST['ore'] == "false") {
            $_POST['ore'] = 0;
        } else {
            $_POST['ore'] = 1;
        }
        if ($_POST['statistiche'] == "false") {
            $_POST['statistiche'] = 0;
        } else {
            $_POST['statistiche'] = 1;
        }
        if ($_POST['magazzino'] == "false") {
            $_POST['magazzino'] = 0;
        } else {
            $_POST['magazzino'] = 1;
        }
        if ($_POST['ddt'] == "false") {
            $_POST['ddt'] = 0;
        } else {
            $_POST['ddt'] = 1;
        }
        if ($_POST['ecommerce'] == "false") {
            $_POST['ecommerce'] = 0;
        } else {
            $_POST['ecommerce'] = 1;
        }

        foreach ($_POST as $k => $v) {
            if ($k == "email") {
                $sql = $db->prepare("SELECT * FROM $tabella WHERE email = ? AND id != " . $_POST['id'] . "");
                $sql->execute(array($v));
                if ($sql->rowCount() > 0) {
                    die('{"msg": "ko", "msgko" : "E-mail già utilizzata"}');
                }
            }
            if ($k == "username") {
                $sql = $db->prepare("SELECT * FROM $tabella WHERE username = ? AND id != " . $_POST['id'] . "");
                $sql->execute(array($v));
                if ($sql->rowCount() > 0) {
                    die('{"msg": "ko", "msgko" : "Username già utilizzato"}');
                }
            }
            $campi[] = $k;
            $valori[] = $v;
        }

        $dati = new impostazioni($id, $tabella);
        $dati->aggiornautente($campi, $valori);
        die('{"msg": "ok"}');

        break;

    case "insertutente":
        $tabella = "utenti";
        if (strlen($_POST['pass']) > 0) {
            $_POST['password'] = $_POST['pass'];
        } else {
            unset($_POST['password']);
        }
        unset($_POST['submit']);
        unset($_POST['pass']);

        if ($_POST['dipendenti'] == "false") {
            $_POST['dipendenti'] = 0;
        } else {
            $_POST['dipendenti'] = 1;
        }
        if ($_POST['clientifornitori'] == "false") {
            $_POST['clientifornitori'] = 0;
        } else {
            $_POST['clientifornitori'] = 1;
        }
        if ($_POST['preventivi'] == "false") {
            $_POST['preventivi'] = 0;
        } else {
            $_POST['preventivi'] = 1;
        }
        if ($_POST['commesse'] == "false") {
            $_POST['commesse'] = 0;
        } else {
            $_POST['commesse'] = 1;
        }
        if ($_POST['fatture'] == "false") {
            $_POST['fatture'] = 0;
        } else {
            $_POST['fatture'] = 1;
        }
        if ($_POST['scadenziario'] == "false") {
            $_POST['scadenziario'] = 0;
        } else {
            $_POST['scadenziario'] = 1;
        }
        if ($_POST['partitario'] == "false") {
            $_POST['partitario'] = 0;
        } else {
            $_POST['partitario'] = 1;
        }
        if ($_POST['impostazioni'] == "false") {
            $_POST['impostazioni'] = 0;
        } else {
            $_POST['impostazioni'] = 1;
        }
        if ($_POST['ore'] == "false") {
            $_POST['ore'] = 0;
        } else {
            $_POST['ore'] = 1;
        }
        if ($_POST['statistiche'] == "false") {
            $_POST['statistiche'] = 0;
        } else {
            $_POST['statistiche'] = 1;
        }
        if ($_POST['magazzino'] == "false") {
            $_POST['magazzino'] = 0;
        } else {
            $_POST['magazzino'] = 1;
        }
        if ($_POST['ddt'] == "false") {
            $_POST['ddt'] = 0;
        } else {
            $_POST['ddt'] = 1;
        }
        if ($_POST['ecommerce'] == "false") {
            $_POST['ecommerce'] = 0;
        } else {
            $_POST['ecommerce'] = 1;
        }

        foreach ($_POST as $k => $v) {
            if ($k == "email") {
                $sql = $db->prepare("SELECT * FROM $tabella WHERE email = ?");
                $sql->execute(array($v));
                if ($sql->rowCount() > 0) {
                    die('{"msg": "ko", "msgko" : "E-mail già utilizzata"}');
                }
            }
            if ($k == "username") {
                $sql = $db->prepare("SELECT * FROM $tabella WHERE username = ?");
                $sql->execute(array($v));
                if ($sql->rowCount() > 0) {
                    die('{"msg": "ko", "msgko" : "Username già utilizzato"}');
                }
            }
            $campi[] = $k;
            $valori[] = $v;
        }

        $dati = new impostazioni($id, $tabella);
        $dati->aggiungiutente($campi, $valori);
        die('{"msg": "ok"}');

        break;

    case "deleteutente":
        $tabella = "utenti";
        $id = $_POST['id'];
        $dati = new impostazioni($id, $tabella);
        $dati->cancella();
        die('{"msg": "ok"}');
        break;

    default:
        break;
}
?>
<!DOCTYPE html>
<html lang="it">
    <head>
        <?php include './components/header.php'; ?>
        <!-- header del modulo -->
        <script type="text/javascript" src="./js/functions-impostazioni.js"></script>

        <?php if ($op) { ?>
            <link type="text/css" rel="stylesheet" href="./js/jsgrid-1.5.1/jsgrid.min.css" />
            <link type="text/css" rel="stylesheet" href="./js/jsgrid-1.5.1/jsgrid-theme.min.css" />
<!--            <link type="text/css" href="./css/theme-app-jsgrid.css" rel="Stylesheet" />-->
            <script type="text/javascript" src="./js/jsgrid-1.5.1/jsgrid.min.js"></script>
            <script type="text/javascript" src="./js/jsgrid-1.5.1/i18n/jsgrid-it.js"></script>
            <?php
        }
        ?>
        <script type="text/javascript">
            $(document).ready(function () {
<?php
if ($op == "iva") {
    ?>
                    mostraIVA();
    <?php
} else if ($op == "metodi") {
    ?>
                    mostraMetodi();
                    //                    mostraClientifornitori('2');
    <?php
} else if ($op == "dati") {
    ?>
                    mostraDati();
<?php } else if ($op == "permessi") { ?>
                    mostraUtentipermessi();
<?php } else if ($op == "profit") { ?>
                    mostraProfitcenter('0');
<?php } else if ($op == "centri") { ?>
                    mostraCentridicosto();
<?php } else if ($op == "ecommerce") { ?>
                    mostraEcommerce();
<?php } else if ($op == "accessori") { ?>
                    mostraAccessori();
<?php } else if ($op == "sartoria") { ?>
                    mostraSartoria('0');
<?php } else if ($op == "motivono") { ?>
                    mostraMotivono();
<?php } else if ($op == "bollino") { ?>
                    mostraBollini();
<?php } ?>
            });
        </script>
    </head>
    <body class="colormodulo">
        <?php include './components/top.php'; ?>
        <?php if (moduloattivo("impostazioni") > 0) { ?>
            <div class="barra_submenu sizing">
                <!--<li class="box_submenu sizing"><a href="/impostazioni.php?op=iva"><i class="fa fa-percent fa-lg" aria-hidden="true"></i> Iva</a></li>-->
                <li class="box_submenu sizing"><a href="/impostazioni.php?op=metodi"><i class="fa fa-money fa-lg" aria-hidden="true"></i> Metodi di pagamento</a></li>
                <?php
                if ($_SESSION['livello'] == 0 || $_SESSION['livello'] == 1) {
                    ?>
                    <li class="box_submenu sizing"><a href="/impostazioni.php?op=dati"><i class="fa fa-info-circle fa-lg" aria-hidden="true"></i> Dati aziendali</a></li>
                    <?php
                }
                ?>
                <?php if (attivaimpostazioni() > 0) { ?>
                        <!--<li class="box_submenu sizing"><a href="/impostazioni.php?op=permessi"><i class="fa fa-key fa-lg" aria-hidden="true"></i> Permessi utenti</a></li>-->
                    <li class="box_submenu sizing"><a href="/impostazioni.php?op=profit"><i class="fa fa-female fa-lg" aria-hidden="true"></i> Tipo di abito</a></li>
                    <li class="box_submenu sizing"><a href="/impostazioni.php?op=accessori"><i class="fa fa-diamond fa-lg" aria-hidden="true"></i> Accessori</a></li>
                    <li class="box_submenu sizing"><a href="/impostazioni.php?op=sartoria"><i class="fa fa-cut fa-lg" aria-hidden="true"></i> Sartoria</a></li>
                    <li class="box_submenu sizing"><a href="/impostazioni.php?op=centri"><i class="fa fa-question fa-lg" aria-hidden="true"></i> Come ci hai trovato</a></li>
                    <li class="box_submenu sizing"><a href="/impostazioni.php?op=motivono"><i class="fa fa-thumbs-down fa-lg" aria-hidden="true"></i> Motivi mancato acquisto</a></li>
                    <li class="box_submenu sizing"><a href="/impostazioni.php?op=bollino"><i class="fa fa-circle fa-lg" aria-hidden="true"></i> Bollini</a></li>
                <?php } ?>
                <?php if (moduloattivo("ecommerce") > 0) { ?>
                    <li class="box_submenu sizing"><a href="/impostazioni.php?op=ecommerce"><i class="fa fa-shopping-bag fa-lg" aria-hidden="true"></i> E-commerce</a></li>
                <?php } ?>  
            </div>
            <div class="content sizing">
                <?php if ($op) { ?>
                    <?php
                    if ($op == "permessi" && ($_SESSION['livello'] == 2 || $_SESSION['livello'] == 3)) {
                        
                    } else {
                        ?>
                        <div class="barra_op sizing">
                            <div style="float:left; margin-right: 5px;">
                                <?php
                                if ($op == "iva") {
                                    echo "GESTIONE IVA";
                                } else if ($op == "metodi") {
                                    echo "GESTIONE METODI DI PAGAMENTO";
                                } else if ($op == "dati") {
                                    echo "GESTIONE DATI AZIENDALI";
                                } else if ($op == "permessi") {
                                    echo "GESTIONE PERMESSI UTENTI";
                                } else if ($op == "profit") {
                                    echo "GESTIONE ABITI";
                                } else if ($op == "centri") {
                                    echo "GESTIONE COME CI HAI TROVATO";
                                } else if ($op == "accessori") {
                                    echo "GESTIONE ACCESSORI";
                                } else if ($op == "sartoria") {
                                    echo "GESTIONE SARTORIA";
                                } else if ($op == "motivono") {
                                    echo "GESTIONE MOTIVI MANCATO ACQUISTO";
                                } else if ($op == "bollino") {
                                    echo "GESTIONE BOLLINI";
                                }
                                ?>                            
                            </div>
                            <div style="float:left; text-transform: uppercase" id="backprofit"></div>
                            <!-- messaggio conferma operazione  -->
                            <div id="messaggio" class="messaggiook">Operazione avvenuta con successo</div>
                        </div>
                        <div class="showcont sizing">
                            <!-- qui il contenuto dei moduli --> 
                        </div>
                    <?php } ?>
                <?php } ?>
            </div>
        <?php } ?>
    </body>
</html>