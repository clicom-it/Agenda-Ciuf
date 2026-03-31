<?php
ob_start('ob_gzhandler');
include './library/connessione.php';
include './library/controllo.php';
include './library/config.php';
include './library/functions.php';
include './library/basic.class.php';
/* libreria del modulo */
include './library/clientifornitori.class.php';
/**/
/*
  cliente = 1
  fornitore = 2
 */
/**/
$op = "";
if (isset($_GET['op'])) {
    $op = $_GET['op'];
}

$submit = "";
if (isset($_POST['submit'])) {
    $submit = $_POST['submit'];
}

switch ($submit) {
    case "editformutenti":
        $tabella = "clienti_fornitori";
        $id = $_POST['id'];
        $dati = new clientifornitori($id, $tabella);

        unset($_POST['submit']);
        if (!$_POST['amministrazione']) {
            $_POST['amministrazione'] = 0;
        } else {
            $_POST['amministrazione'] = 1;
        }
        
        $tipo = $_POST['tipo'];

        foreach ($_POST as $k => $v) {
            /* controllo email */
//            if ($k == "email" && $v != "") {
//                $sql = $db->prepare("SELECT * FROM $tabella WHERE email = ? AND id != $id AND tipo = ?");
//                $sql->execute(array($v, $tipo));
//                if ($sql->rowCount() > 0) {
//                    die('{"msg": "ko", "msgko" : "E-mail già utilizzata"}');
//                }
//            }
            $campi[] = $k;
            $valori[] = $v;
        }

        $clientefornitore = $dati->aggiorna($campi, $valori);
        die('{"msg": "ok"}');
        break;

    case "richiamaclientefornitore":
        $tabella = "clienti_fornitori";
        $id = $_POST['id'];
        $dati = new clientifornitori($id, $tabella);
        $clientefornitore = $dati->richiama();
        die('{"valori" : ' . json_encode($clientefornitore) . '}');
        break;

    case "delete":
        $tabella = "clienti_fornitori";
        $id = $_POST['id'];
        $dati = new clientifornitori($id, $tabella);
        $clienti = $dati->cancella();
        die('{"msg": "ok"}');
        break;

    case "tutticlientifornitori":
        $tabella = "clienti_fornitori";
        $id = $_POST['id'];
        $dati = new clientifornitori($id, $tabella);
        if ($_SESSION['livello'] == 0 || $_SESSION['livello'] == 1) {
            $where = "tipo = '$id'";
        } else {
            $where = "tipo = '$id' AND idatelier = ".$_SESSION['idatelier']."";
        }        
        $clientifornitori = $dati->richiamaWhere($where);
        for ($i = 0; $i < count($clientifornitori); $i++) {
            if ($clientifornitori[$i]['amministrazione'] == "0") {
                $clientifornitori[$i]['amministrazione'] = false;
            } else {
                $clientifornitori[$i]['amministrazione'] = true;
            }

            $metodi = getDati("metodi_pagamento", "WHERE id=" . $clientifornitori[$i]['metodopagamento'] . "");
            foreach ($metodi as $datimetodi) {
                $clientifornitori[$i]['metodo'] .= $datimetodi['nome'];
            }
        }

        die('{"dati" : ' . json_encode($clientifornitori, JSON_INVALID_UTF8_IGNORE) . '}');
        break;
    case "submitformutenti":
        $tabella = "clienti_fornitori";
        $clientifornitori = new clientifornitori($id, $tabella);
        /* tolgo dall'array submit */
        unset($_POST['submit']);

        if ($_POST['amministrazione']) {
            $_POST['amministrazione'] = 1;
        } else {
            $_POST['amministrazione'] = 0;
        }
        
        $_POST['idatelier'] = $_SESSION['idatelier'];
        
        $tipo = $_POST['tipo'];

        foreach ($_POST as $k => $v) {
            /* controllo email */
//            if ($k == "email" && $v != "") {
//                $sql = $db->prepare("SELECT * FROM $tabella WHERE email = ? AND tipo = ?");
//                $sql->execute(array($v, $tipo));
//                if ($sql->rowCount() > 0) {
//                    die('{"msg": "ko", "msgko" : "E-mail già utilizzata"}');
//                }
//            }
            $campi[] = $k;
            $valori[] = $v;
        }
        /* funzione aggiungi */
        $clientifornitori->aggiungi($campi, $valori);


        die('{"msg" : "ok"}');

        break;

    case 'sendfile':
        global $db;
        $nomefile = $_POST['nomefile'];
        $tabella = "clienti_fornitori";
        $tipo = $_POST['tipo'];
        require_once './library/phpExcelReader/Excel/reader.php';

        $data = new Spreadsheet_Excel_Reader();
        $data->setOutputEncoding('UTF-8');
        $data->read("tmp/" . $nomefile);
        for ($i = 1; $i <= $data->sheets[0]['numRows']; $i++) {
            $arr_tipo[] = $tipo;
            $arr_cod[] = mb_convert_encoding(trim(utf8_encode($data->sheets[0]['cells'][$i][1])), "UTF-8", "ISO-8859-9");
            $arr_nome[] = mb_convert_encoding(trim(utf8_encode($data->sheets[0]['cells'][$i][2])), "UTF-8", "ISO-8859-9");
            $arr_cognome[] = mb_convert_encoding(trim(utf8_encode($data->sheets[0]['cells'][$i][3])), "UTF-8", "ISO-8859-9");
            $arr_azienda[] = mb_convert_encoding(trim(utf8_encode($data->sheets[0]['cells'][$i][4])), "UTF-8", "ISO-8859-9");
            $arr_cf[] = mb_convert_encoding(trim(utf8_encode($data->sheets[0]['cells'][$i][5])), "UTF-8", "ISO-8859-9");
            $arr_piva[] = mb_convert_encoding(trim(utf8_encode($data->sheets[0]['cells'][$i][6])), "UTF-8", "ISO-8859-9");
            $arr_indirizzo[] = mb_convert_encoding(trim(utf8_encode($data->sheets[0]['cells'][$i][7])), "UTF-8", "ISO-8859-9");
            $arr_cap[] = mb_convert_encoding(trim(utf8_encode($data->sheets[0]['cells'][$i][8])), "UTF-8", "ISO-8859-9");
            $arr_localita[] = mb_convert_encoding(trim(utf8_encode($data->sheets[0]['cells'][$i][9])), "UTF-8", "ISO-8859-9");
            $arr_provincia[] = mb_convert_encoding(trim(utf8_encode($data->sheets[0]['cells'][$i][10])), "UTF-8", "ISO-8859-9");
            if ($data->sheets[0]['cells'][$i][10] != "") {
                $arr_nazione[] = "IT";
                $sql = $db->prepare("SELECT regione FROM province WHERE sigla = ?");
                $sql->execute(array(mb_convert_encoding(trim(utf8_encode($data->sheets[0]['cells'][$i][10])), "UTF-8", "ISO-8859-9")));
                $arr_regione[] = $sql->fetchColumn();
            } else {
                $arr_nazione[] = "XX";
                $arr_regione[] = "";
            }
            $arr_tel[] = mb_convert_encoding(trim(utf8_encode($data->sheets[0]['cells'][$i][11])), "UTF-8", "ISO-8859-9");
            $arr_cell[] = mb_convert_encoding(trim(utf8_encode($data->sheets[0]['cells'][$i][12])), "UTF-8", "ISO-8859-9");
            $arr_email[] = mb_convert_encoding(trim(utf8_encode($data->sheets[0]['cells'][$i][13])), "UTF-8", "ISO-8859-9");
        }

        if ($arr_cod[0] != "codice" || $arr_nome[0] != "nome" || $arr_cognome[0] != "cognome" || $arr_azienda[0] != "azienda" || $arr_cf[0] != "codice fiscale" || $arr_piva[0] != "partita iva" || $arr_indirizzo[0] != "indirizzo" || $arr_cap[0] != "cap" || $arr_localita[0] != "localita" || $arr_provincia[0] != "provincia" || $arr_tel[0] != "telefono" || $arr_cell[0] != "cellulare" || $arr_email[0] != "email") {
            unlink("tmp/" . $nomefile);
            die('{"msg" : "ko"}');
        }

        $sql = $db->prepare('INSERT INTO ' . $tabella . ' (regione, nazione, tipo, codice, nome, cognome, azienda, codicefiscale, piva, indirizzo, cap, comune, provincia, telefono, cellulare, email) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');

        try {
            for ($n = 1; $n < count($arr_cod); $n++) {
                $sql->execute(array($arr_regione[$n], $arr_nazione[$n], $arr_tipo[$n], $arr_cod[$n], $arr_nome[$n], $arr_cognome[$n], $arr_azienda[$n], $arr_cf[$n], $arr_piva[$n], $arr_indirizzo[$n], $arr_cap[$n], $arr_localita[$n], $arr_provincia[$n], $arr_tel[$n], $arr_cell[$n], $arr_email[$n]));
            }
            unlink("tmp/" . $nomefile);
            die('{"msg" : "ok"}');
        } catch (PDOException $e) {
            die('Connessione fallita: ' . $e->getMessage());
        }
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
        <script type="text/javascript" src="./js/functions-clientifornitori.js"></script>
        <?php if ($op) { ?>
            <link type="text/css" rel="stylesheet" href="./js/jsgrid-1.5.1/jsgrid.min.css" />
            <link type="text/css" rel="stylesheet" href="./js/jsgrid-1.5.1/jsgrid-theme.min.css" />
            <link type="text/css" href="./css/theme-app-jsgrid.css" rel="Stylesheet" />
            <script type="text/javascript" src="./js/jsgrid-1.5.1/jsgrid.min.js"></script>
            <script type="text/javascript" src="./js/jsgrid-1.5.1/i18n/jsgrid-it.js"></script>
            <?php
        }
        ?>

        <script type="text/javascript">
            $(document).ready(function () {
<?php
if ($op == "clienti") {
    ?>
                    mostraClientifornitori('1', '');
    <?php
} else if ($op == "fornitori") {
    ?>
                    mostraClientifornitori('2', '');
    <?php
}
?>
            });
        </script>




    </head>
    <body class="colormodulo">
        <?php include './components/top.php'; ?>
        <?php if (moduloattivo("clientifornitori") > 0) { ?>
            <div class="barra_submenu sizing">
                <li class="box_submenu sizing"><a href="/clientifornitori.php?op=clienti"><i class="fa fa-user fa-lg" aria-hidden="true"></i> Anagrafica Clienti</a></li>
                <!--<li class="box_submenu sizing"><a href="/clientifornitori.php?op=fornitori"><i class="fa fa-archive fa-lg" aria-hidden="true"></i> Anagrafica Fornitori</a></li>-->
            </div>
            <div class="content sizing">
                <?php if ($op) { ?>
                    <div class="barra_op sizing">
                        <?php
                        if ($op == "clienti") {
                            ?>
                            <div class="bottone sizing"><a href="javascript:;" onclick="aggiungi('1');"><i class="fa fa-user-plus fa-lg" aria-hidden="true"></i> Aggiungi cliente</a></div>
                            <!--<div class="bottone sizing" style="margin-left: 10px;"><a href="javascript:;" onclick="importa('1');"><i class="fa fa-plus-circle fa-lg" aria-hidden="true"></i> Importa clienti</a></div>-->
                            <div id="messaggio" class="messaggiook">Operazione avvenuta con successo</div>
                            <?php
                        } else if ($op == "fornitori") {
                            ?>
                            <div class="bottone sizing"><a href="javascript:;" onclick="aggiungi('2');"><i class="fa fa-user-plus fa-lg" aria-hidden="true"></i> Aggiungi fornitore</a></div>
                            <!--<div class="bottone sizing" style="margin-left: 10px;"><a href="javascript:;" onclick="importa('2');"><i class="fa fa-plus-circle fa-lg" aria-hidden="true"></i> Importa fornitori</a></div>-->
                            <div id="messaggio" class="messaggiook">Operazione avvenuta con successo</div>
                            <?php
                        }
                        ?>
                    </div>
                    <div class="showcont sizing">
                        <!-- qui contenuto del mostra anagrafiche e aggiungi anagrafiche --> 
                    </div>
                <?php } ?>
            </div>
        <?php } ?>
    </body>
</html>