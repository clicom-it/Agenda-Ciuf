<?php
session_start();
ob_start('ob_gzhandler');
include './library/config.php';
include './library/connessione.php';
include './library/functions.php';

$submit = "";
if (isset($_POST['submit'])) {
    $submit = $_POST['submit'];
}

switch ($submit) {
    case "login":
        $username = $_POST['username'];
        $password = $_POST['password'];

        $sql = $db->prepare("SELECT *,(select assistant_id from AI_utenti ai where u.id=ai.idutente limit 1) as assistant_id FROM utenti u WHERE email = ? AND password = ? AND attivo = ? AND password != '' and email != '' and no_mrage=? LIMIT 1");
        try {
            $sql->execute(array($username, $password, 1, 0));
        } catch (PDOException $e) {
            die('Connessione fallita: ' . $e->getMessage());
        }
        if ($sql->rowCount() > 0) {
            $res = $sql->fetch(PDO::FETCH_ASSOC);
            $_SESSION["id"] = $res["id"];
            $_SESSION["nome"] = $res["nome"];

            /* per sessione */
            $_SESSION["username"] = $username;
            $_SESSION["password"] = $password;
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

//            $sessemail = $res["email"];
//            if ($sessemail == "") {
            $eml = getDati("utenti", "WHERE id = " . $_SESSION['idatelier'] . "");

            $_SESSION["email"] = $eml[0]['email'];
            if ($idatelier > 0) {
                $_SESSION["solo_sartoria"] = $eml[0]["solo_sartoria"];
            }
            $atelier_collegati = getDati("atelier_utente", "WHERE idutente = " . $_SESSION['id'] . "");
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
//            } else {
//                $_SESSION["email"]= $res["email"];
//            }

            setcookie("cookieid", MD5($res["id"]), time() + (3600 * 14));
            /**/
            logAccesso($res["id"], 'login');
            die('{"msg" : "1"}');
        } else {
            die('{"msg" : "0", "print" : "Username o password errati"}');
        }
        break;
    case "recupera":
        $email = $_POST['email'];

        $sql = $db->prepare("SELECT * FROM utenti WHERE email = ? AND attivo = ? AND email != '' LIMIT 1");
        try {
            $sql->execute(array($email, 1));
        } catch (PDOException $e) {
            die('Connessione fallita: ' . $e->getMessage());
        }

        if ($sql->rowCount() > 0) {

            include './library/phpmailer/PHPMailerAutoload.php';

            $password = randomPassword(8);
            $md5password = md5($password);
            $subject = "$cms - recupera password per CMS $sito";
            $body = "Gentile utente, come da te richiesto ecco la tua nuova password: $password<br />"
                    . "Ricordati di cambiarla al prossimo accesso<br /><br />"
                    . "Grazie MrGest";

            sendMail($email, $subject, $body, "noreply@clicom.it", "MrGest", "");

            $sql = $db->prepare("UPDATE utenti SET password = ? WHERE email = ?");
            $sql->execute(array($md5password, $email));

            die('{"msg" : "1", "print" : "Nuova password inviata con successo!"}');
        } else {
            die('{"msg" : "0", "print" : "E-mail non trovata, inseriscine una diversa"}');
        }
        break;
    default:
        break;
}
?>
<!DOCTYPE html>
<html lang="it">
    <head>
        <?php include './components/header_index.php'; ?>
    </head>
    <body style="background-color: #6d6e70;" onload="$('#username').focus();">
        <div class="bkg_black_loading"></div>
        <div class="bkg_black" id="boxrecupera">
            <div class="box_recupera sizing">
                <div class="close">
                    <a href="javascript:;" onclick="javascript:mostradiv('boxrecupera');"><img src="./immagini/close.png" /></a>
                </div>
                <div class="titrecupera">Recupera password</div>
                <form method="post" action="" name="formrecupera" id="formrecupera">
                    <input style="background-image:url('./immagini/mail.png'); width: 300px;" type="email" name="email" id="email" placeholder="Inserisci e-mail" class="input_login sizing required" />
                    <input type="submit" name="submit" value="Invia" id="recupera" class="submit_login sizing" style="width: 90px; float: right;" />
                    <div class="chiudi"></div>
                </form>

                <div class="errore sizing" id="errorerecupera"></div>
                <div class="noerrore sizing" id="noerrorerecupera"></div>
            </div>
        </div>
        <div class="login">
            <div class="titlogin">Login <?php echo $cms; ?></div>
            <form method="post" action="" name="formlogin" id="formlogin">
                <input style="background-image:url('./immagini/user.png');" type="text" name="username" id="username" placeholder="E-mail" class="input_login sizing required" />
                <input style="background-image:url('./immagini/pass.png');" type="password" name="password" id="password" placeholder="Password" class="input_login sizing required" />
                <div class="forgot">
                    <a href="javascript:;" onclick="javascript:mostradiv('boxrecupera');">Hai perso la password?</a>
                </div>
                <input type="submit" name="submit" value="LOGIN" id="login" class="submit_login sizing" />
            </form>
            <div class="errore sizing" id="errorelogin"></div>
        </div>
        <div class="ombra"></div>
    </body>
</html>