<?php

include './library/connessione.php';
include './library/config.php';
include './library/functions.php';
include './library/basic.class.php';
include './library/calendario.class.php';
include './library/phpmailer/PHPMailerAutoload.php';
ini_set("max_execution_time", 600);
// collegamento al database sito comeinunafavola.it
$col = 'mysql:host=localhost;dbname=come3_db;charset=latin1;';
try {
    $db2 = new PDO($col, 'come3_usr', '!g4UTTH68zIYWx9#', array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'latin1'"));
}
catch (PDOException $e) {
    echo 'Errore di connessione: ' . $e->getMessage();
}
define('INFO_APP', 'INFORMAZIONI SULL\'APPUNTAMENTO');
define('APP_ONE_SHOT', '<b>Appuntamento One Shot:</b> puoi recarti un\'unica volta nei nostri negozi per effettuare la tua prima prova gratuita e senza obbligo d\'acquisto. Questo perchè essendo il nostro format unico come i nostri abiti, le richieste sono molto elevate e diamo a tutti la possibilità di recarsi da noi.');
define('MAX_3_ACC', 'Massimo <b>3 accompagnatori.</b>');
define('DISDIRE', 'Per <b><u>disdire l\'appuntamento</u></b> bisogna chiamare il numero <b>0522 126 6580</b> oppure inviare un\'email a <b>servizioclienti@comeinunafavola.it</b>');
$timenow = date('Y-m-d');
$date_obj = new DateTime();
$date_obj->modify('+1 days');
$date_2day = $date_obj->format('Y-m-d');
/* e-mail remind appuntamento */
$qry = "select *, DATE_FORMAT(data, '%d/%m/%Y') as data_it, DATE_FORMAT(orario, '%H:%i') as orario_it,"
        . "(select email from clienti_fornitori where id=c.idutente limit 1) as email "
        . "  from calendario c where "
        . "data = '$date_2day' and idatelier > 0 and idutente > 0 limit 1;";
//die($qry);
$rs = $db->prepare($qry);
$rs->execute();
while ($col = $rs->fetch(PDO::FETCH_ASSOC)) {
    $nome = $col['nome'];
    $data_app = $col['data_it'];
    $time_app = $col['orario_it'];
    $store = getAtelier(' and id=' . $col['idatelier']);
    $store_txt = $store[0]['indirizzo'] . ', ' . $store[0]['comune'];
    $messaggio_user = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <meta name="viewport" content="width=device-width"/>
        <style type="text/css">

            * { margin: 0; padding: 0; font-size: 100%; font-family: sans, sans-serif; line-height: 1.65; }

            img { max-width: 100%;display: block; }

            body, .body-wrap { width: 100% !important; height: 100%; background: #ffffff;font-size: 11px; }

            a { color: #000000; text-decoration: none; }

            a:hover { text-decoration: underline; }

            .text-center { text-align: center; }

            .text-right { text-align: right; }

            .text-left { text-align: left; }

            .button { display: inline-block; color: white; background: #ffffff; border: solid #ffffff; border-width: 10px 20px 8px; font-weight: bold; border-radius: 4px; }

            .button:hover { text-decoration: none; }

            h1, h2, h3, h4, h5, h6 { margin-bottom: 20px; line-height: 1.25; }

            h1 { font-size: 32px; }

            h2 { font-size: 28px; }

            h3 { font-size: 24px; }

            h4 { font-size: 20px; }

            h5 { font-size: 16px; }

            p, ul, ol { font-size: 22px; font-weight: normal; margin-bottom: 20px; }

            .container { display: block !important; clear: both !important; margin: 0 auto !important; max-width: 800px !important; font-size: 11px;}

            .container table { width: 100% !important; border-collapse: collapse; }
            table.body-wrap th,table.body-wrap td  {padding:5px;color:#000000;}

            .container .masthead { padding: 10px 0; background: #ffffff; color: white; }

            .container .masthead h1 { margin: 20px auto !important; max-width: 90%; text-transform: uppercase; }

            .container .bottom { padding: 5px 0; background: #ffffff; color: white; }
            table.tb-social {width:144px !important;}

            .container .content { background: white; padding: 30px 35px; }

            .container .content.footer { background: none; }

            .container .content.footer p { margin-bottom: 0; color: #888; text-align: center; font-size: 14px; }

            .container .content.footer a { color: #888; text-decoration: none; font-weight: bold; }

            .container .content.footer a:hover { text-decoration: underline; }
            table.tb-white {background-color:#ffffff;}
            table.tb-white td {padding: 30px;}
            table.tb-white h1 {margin:30px 0 20px;}
            table.tb-white p {text-align:center;}
            img.ico-social {float: right;margin-right: 10px;}
            img.ico-social-bottom {display: inline-block;margin-right: 10px;}
        </style>
    </head>
    <body>
        <table class="body-wrap">
            <tr>
                <td class="container">
                    <!-- Message start -->
                    <table>
                        <tr>
                            <td align="left" class="masthead" style="width:50%;text-align:left;">
                                <img src="https://www.comeinunafavola.it/immagini/logo-come-in-una-favola-v6.png" alt="Come in una favola" width="200" />
                            </td>
                            <td align="right" class="masthead" style="width:50%;text-align:right;">
                                <a href="https://www.facebook.com/comeinunafavola" target="new"><img src="https://www.comeinunafavola.it/immagini/ico-facebook-32.png" alt="Facebook" class="ico-social" /></a>
                                <a href="https://www.instagram.com/comeinunafavola_official/" target="new"><img src="https://www.comeinunafavola.it/immagini/ico-instagram-32.png" alt="Instagram" class="ico-social" /></a>
                                <a href="https://www.comeinunafavola.it" target="new"><img src="https://www.comeinunafavola.it/immagini/ico-world.png" alt="Sito web" class="ico-social" /></a>
                            </td>
                        </tr>
                        <tr><td colspan="2" style="background-color:#f4e2e2;padding:100px 20px;">
                        <table class="tb-white">
                            <tbody>
                                <tr>
                                    <td style="text-align:center;">
                                    <h1>MANCA POCHISSIMO AL TUO APPUNTAMENTO!</h1>
                                    <p>Ciao <b>' . ucfirst($nome) . '</b></p>
                                    <p style="margin-bottom: 60px;">ti ricordiamo il tuo appuntamento in <b>[indirizzo_store]</b> il giorno <b>[data_app]</b> alle ore <b>[ore_app]</b>.</p>
                                    <p style="margin-bottom: 60px;"><img style="display:inline-block;" src="https://www.comeinunafavola.it/immagini/ti-aspettiamo.jpg" title="Ti aspettiamo!" /></p>
                                    <h3>' . INFO_APP . '</h3>
                                    <p style="text-align: left;">- ' . APP_ONE_SHOT . '</p>
                                    <p style="text-align: left;margin-bottom: 40px;">- ' . MAX_3_ACC . '</p>
                                    <p>' . DISDIRE . '</p>
                                    </td>
                                </tr>
                            </tbody>
                       </table>
                        </td></tr>
                    </table>
                </td>
            </tr>
  <tr>
    <td class="container">
  <table>
    <tr>
        <td class="content footer" align="center">
    <a href="https://www.facebook.com/comeinunafavola" target="new"><img src="https://www.comeinunafavola.it/immagini/ico-facebook-32.png" alt="Facebook" class="ico-social-bottom" /></a>
    <a href="https://www.instagram.com/comeinunafavola_official/" target="new"><img src="https://www.comeinunafavola.it/immagini/ico-instagram-32.png" alt="Instagram" class="ico-social-bottom" /></a>
    <a href="https://www.comeinunafavola.it" target="new"><img src="https://www.comeinunafavola.it/immagini/ico-world.png" alt="Sito web" class="ico-social-bottom" /></a>
    </td>
    </tr>
    <tr>
        <td align="center">
            <p style="margin-bottom: 0;font-size: 14px"><b>COME IN UNA FAVOLA &reg;</b></p>
        <p style="margin-bottom: 0;font-size: 14px">Via Primo Carnera, 4</p>
        <p style="margin-bottom: 0;font-size: 14px">42123 Reggio Emilia (RE)</p>
        </td>
    </tr>
    </table>
    </td>
    </tr>
    </table>
    </body>
    </html>';
    $messaggio_user = str_replace('[indirizzo_store]', $store_txt, $messaggio_user);
    $messaggio_user = str_replace('[data_app]', $data_app, $messaggio_user);
    $messaggio_user = str_replace('[ore_app]', $time_app, $messaggio_user);
    //die($messaggio_user);
    $email = $col['email'];
    //$email = 'valeriabarberio@comeinunafavola.it';
    if ($email != "") {
        $subject = 'MANCA POCHISSIMO AL TUO APPUNTAMENTO!';
        sendMail($email, $subject, $messaggio_user, 'info@comeinunafavola.it', 'Come in una Favola', '');
        sleep(1);
        //die();
    }
}
//die(OK);
/* e-mail feedback */
$qry = "select COUNT(id) as num, SUM(sottotitolo) as totale from news where idp=46 limit 1;";
$rs = $db2->prepare($qry);
$rs->execute();
$col = $rs->fetch(PDO::FETCH_ASSOC);
$media = round($col['totale']/$col['num'], 1);
$qry = "SELECT *, DATE_FORMAT(data, '%M %d, %Y') as datanum FROM news WHERE idp = 46 AND attivo = 1 ORDER BY data DESC limit 3;";
$rs = $db2->prepare($qry);
$rs->execute();
$recensioni = $rs->fetchAll(PDO::FETCH_ASSOC);
//$timenow = '2023-05-09';
$qry = "select *, DATE_FORMAT(data, '%d/%m/%Y') as data_it, DATE_FORMAT(orario, '%H:%i') as orario_it,"
        . "(select email from clienti_fornitori where id=c.idutente limit 1) as email "
        . "  from calendario c where "
        . "DATE(DATE_ADD(data, INTERVAL 1 DAY)) = '$timenow' and idatelier > 0 and idutente > 0 limit 1;";
//die($qry);
$rs = $db->prepare($qry);
$rs->execute();
while ($col = $rs->fetch(PDO::FETCH_ASSOC)) {
    $nome = $col['nome'];
    $data_app = $col['data_it'];
    $time_app = $col['orario_it'];
    $store = getAtelier(' and id=' . $col['idatelier']);
    $store_txt = $store[0]['indirizzo'] . ', ' . $store[0]['comune'];
    $messaggio_user = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <meta name="viewport" content="width=device-width"/>
        <style type="text/css">

            * { margin: 0; padding: 0; font-size: 100%; font-family: sans, sans-serif; line-height: 1.65; }

            img { max-width: 100%;display: block; }

            body, .body-wrap { width: 100% !important; height: 100%; background: #ffffff;font-size: 11px; }

            a { color: #000000; text-decoration: none; }

            a:hover { text-decoration: underline; }

            .text-center { text-align: center; }

            .text-right { text-align: right; }

            .text-left { text-align: left; }

            .button { display: inline-block; color: white; background: #ffffff; border: solid #ffffff; border-width: 10px 20px 8px; font-weight: bold; border-radius: 4px; }

            .button:hover { text-decoration: none; }

            h1, h2, h3, h4, h5, h6 { margin-bottom: 20px; line-height: 1.25; }

            h1 { font-size: 28px; }

            h2 { font-size: 28px; }

            h3 { font-size: 16px; }

            h4 { font-size: 20px; }

            h5 { font-size: 16px; }

            p, ul, ol { font-size: 22px; font-weight: normal; margin-bottom: 20px; }

            .container { display: block !important; clear: both !important; margin: 0 auto !important; max-width: 800px !important; font-size: 11px;}

            .container table { width: 100% !important; border-collapse: collapse; }
            table.body-wrap th,table.body-wrap td  {padding:5px;color:#000000;}

            .container .masthead { padding: 10px 0; background: #ffffff; color: white; }

            .container .masthead h1 { margin: 20px auto !important; max-width: 90%; text-transform: uppercase; }

            .container .bottom { padding: 5px 0; background: #ffffff; color: white; }
            table.tb-social {width:144px !important;}

            .container .content { background: white; padding: 30px 35px; }

            .container .content.footer { background: none; }

            .container .content.footer p { margin-bottom: 0; color: #888; text-align: center; font-size: 14px; }

            .container .content.footer a { color: #888; text-decoration: none; font-weight: bold; }

            .container .content.footer a:hover { text-decoration: underline; }
            table.tb-white {background-color:#ffffff;}
            table.tb-white td {padding: 30px;}
            table.tb-white h1 {margin:30px 0 20px;}
            table.tb-white p {text-align:center;}
            img.ico-social {float: right;margin-right: 10px;}
            img.ico-social-bottom {display: inline-block;margin-right: 10px;}
            a.link-rec {display: inline-block;color:blue;padding:10px 20px;border:2px solid blue;font-size:14px;}
        </style>
    </head>
    <body>
        <table class="body-wrap">
            <tr>
                <td class="container">
                    <!-- Message start -->
                    <table>
                        <tr>
                            <td align="left" class="masthead" style="width:50%;text-align:left;">
                                <img src="https://www.comeinunafavola.it/immagini/logo-come-in-una-favola-v6.png" alt="Come in una favola" width="200" />
                            </td>
                            <td align="right" class="masthead" style="width:50%;text-align:right;">
                                <a href="https://www.facebook.com/comeinunafavola" target="new"><img src="https://www.comeinunafavola.it/immagini/ico-facebook-32.png" alt="Facebook" class="ico-social" /></a>
                                <a href="https://www.instagram.com/comeinunafavola_official/" target="new"><img src="https://www.comeinunafavola.it/immagini/ico-instagram-32.png" alt="Instagram" class="ico-social" /></a>
                                <a href="https://www.comeinunafavola.it" target="new"><img src="https://www.comeinunafavola.it/immagini/ico-world.png" alt="Sito web" class="ico-social" /></a>
                            </td>
                        </tr>
                        <tr><td colspan="2" style="background-color:#f4e2e2;padding:100px 20px;">
                        <table class="tb-white">
                            <tbody>
                                <tr>
                                    <td style="text-align:center;">
                                    <h1>LA TUA OPINIONE E\' IMPORTANTE PER NOI!</h1>
                                    <p>Ciao <b>' . ucfirst($nome) . '</b></p>
                                    <p>Ti chiediamo pochi secondi di tempo per valutare la tua esperienza presso l\'Atelier di <b>Come in una favola.</p>
                                    <p style="margin-bottom: 60px;"></b>E\' grazie ai vostri suggerimenti che riusciamo a migliorare il nostro servizio ogni giorno.</p>
                                    <p style="margin-bottom: 60px;"><img style="display:inline-block;" src="https://www.comeinunafavola.it/immagini/la-tua-felicita.jpg" title="La tua felicità è la nostra soddisfazione!" /></p>
                                    <h3>Dicono di noi: '.$media.' su 5 <img style="margin-left:5px;display:inline;width:25px;" src="https://www.comeinunafavola.it/immagini/star-rating.png" title="" /></h3>
                                    <table>
                                        <tbody>
                                        <tr>
                                            <td style="width:33%;text-align:center;vertical-align:top;padding:0 10px;">
                                                <p style="font-size:12px;">'.cropString(strip_tags($recensioni[0]['descrizione']), 200).'</p>
                                            </td>
                                            <td style="width:33%;text-align:center;font-size:12px;vertical-align:top;padding:0 10px;">
                                                <p style="font-size:12px;">'.cropString(strip_tags($recensioni[1]['descrizione']), 200).'</p>
                                            </td>
                                            <td style="width:33%;text-align:center;font-size:12px;vertical-align:top;padding:0 10px;">
                                                <p style="font-size:12px;">'.cropString(strip_tags($recensioni[2]['descrizione']), 200).'</p>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="width:33%;text-align:center;font-size:12px;vertical-align:top;padding:0;">
                                                <p style="font-size:12px;"><i>'.$recensioni[0]['titolo'].'</i></p>
                                            </td>
                                            <td style="width:33%;text-align:center;font-size:12px;vertical-align:top;padding:0;">
                                                <p style="font-size:12px;"><i>'.$recensioni[1]['titolo'].'</i></p>
                                            </td>
                                            <td style="width:33%;text-align:center;font-size:12px;vertical-align:top;padding:0;">
                                                <p style="font-size:12px;"><i>'.$recensioni[2]['titolo'].'</i></p>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="3" style="text-align:center;"><a class="link-rec" href="https://www.comeinunafavola.it/it/dicono-di-noi/?add=1">Lasciaci la tua recensione   &rarr;</a></td>
                                        </tr>
                                        </tbody>
                                    </table>
                                    <h1 style="margin-bottom: 60px;margin-top:100px;">Grazie per aver scelto Come in una favola!</h1>
                                    <p style="text-align:right;margin-bottom: 20px;">- Team di Come in una Favola</p>
                                    </td>
                                </tr>
                            </tbody>
                       </table>
                        </td></tr>
                    </table>
                </td>
            </tr>
  <tr>
    <td class="container">
  <table>
    <tr>
        <td class="content footer" align="center">
    <a href="https://www.facebook.com/comeinunafavola" target="new"><img src="https://www.comeinunafavola.it/immagini/ico-facebook-32.png" alt="Facebook" class="ico-social-bottom" /></a>
    <a href="https://www.instagram.com/comeinunafavola_official/" target="new"><img src="https://www.comeinunafavola.it/immagini/ico-instagram-32.png" alt="Instagram" class="ico-social-bottom" /></a>
    <a href="https://www.comeinunafavola.it" target="new"><img src="https://www.comeinunafavola.it/immagini/ico-world.png" alt="Sito web" class="ico-social-bottom" /></a>
    </td>
    </tr>
    <tr>
        <td align="center">
            <p style="margin-bottom: 0;font-size: 14px"><b>COME IN UNA FAVOLA &reg;</b></p>
        <p style="margin-bottom: 0;font-size: 14px">Via Primo Carnera, 4</p>
        <p style="margin-bottom: 0;font-size: 14px">42123 Reggio Emilia (RE)</p>
        </td>
    </tr>
    </table>
    </td>
    </tr>
    </table>
    </body>
    </html>';
    //die($messaggio_user);
    $email = $col['email'];
    //$email = 'valeriabarberio@comeinunafavola.it';
    //$email = 'max@clicom.it';
    if ($email != "") {
        $subject = 'LA TUA OPINIONE E\' IMPORTANTE PER NOI!';
        sendMail($email, $subject, $messaggio_user, 'info@comeinunafavola.it', 'Come in una Favola', '');
        sleep(1);
    }
}
?>