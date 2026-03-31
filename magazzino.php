<?php
ob_start('ob_gzhandler');
include './library/connessione.php';
include './library/controllo.php';
include './library/config.php';
include './library/functions.php';
include './library/basic.class.php';
/* libreria del modulo */
include './library/magazzino.class.php';

/**/
$submit = "";
if (isset($_POST['submit'])) {
    $submit = $_POST['submit'];
}

switch ($submit) {

    case "edit":
        $tabella = "magazzino";
        $id = $_POST['id'];
        $magazzino = new magazzino($id, $tabella);
        $codice = $_POST['codice'];
        
        /* CONTROLLO CODICE SE GIÀ ESISTENTE */
        $mag = $magazzino->richiamaWhere("codice != '' AND id != $id");
        foreach ($mag as $magd) {
            $cod = $magd['codice'];
            if ($cod == $_POST['codice']) {
                die('{"msg" : "ko"}');
                exit();
            }
        }

        /**/
        /* sistemo idfornitore e nomefornitore */
        if ($_POST['fornitore'] != "") {
            $arrforn = explode("|||", $_POST['fornitore']);
            $_POST['idfornitore'] = $arrforn[0];
            $_POST['fornitore'] = $arrforn[1];
        } else {
           $_POST['idfornitore'] = "";
            $_POST['fornitore'] = "";
        }

        /* tolgo dall'array submit e post che non mi servono */
        unset($_POST['submit']);

        foreach ($_POST as $k => $v) {
            $campi[] = $k;
            $valori[] = $v;
        }

        $magazzino->aggiorna($campi, $valori);
        die('{"msg": "ok"}');
        break;

    case "delete":
        $tabella = "magazzino";
        $id = $_POST['id'];
        $dati = new magazzino($id, $tabella);
        $magazzino = $dati->cancella();
        die('{"msg": "ok"}');
        break;

    case "mostramagazzino":
        $tabella = "magazzino";
        $id = $_POST['id'];
        $dati = new magazzino($id, $tabella);
        $magazzino = $dati->richiamaWhere("id > 0 ORDER BY idfornitore");

        $tabella = "clienti_fornitori";
        $datiforn = new magazzino($id, $tabella);
        $forn = $datiforn->richiamaWhere("tipo = '2'");
        $arrforn[] = array("name" => "Seleziona fornitore...", "Id" => "");
        foreach ($forn as $fornd) {
            $arrforn[] = array("name" => $fornd['azienda'], "Id" => $fornd['id']);
        }


        die('{"dati" : ' . json_encode($magazzino) . ', "fornitoriautocomplete" : ' . json_encode($forn) . '}');
        break;

    case "insert":
        $tabella = "magazzino";

        $magazzino = new magazzino($id, $tabella);
        
        /* CONTROLLO CODICE SE GIÀ ESISTENTE */
        $mag = $magazzino->richiamaWhere("codice != ''");
        foreach ($mag as $magd) {
            $cod = $magd['codice'];
            if ($cod == $_POST['codice']) {
                die('{"msg" : "ko"}');
                exit();
            }
        }
        
        /**/
        /* sistemo idcliente e nomecliente */
        if ($_POST['fornitore'] != "") {
        $arrforn = explode("|||", $_POST['fornitore']);
        
            $_POST['idfornitore'] = $arrforn[0];
            $_POST['fornitore'] = $arrforn[1];
        } else {
            $_POST['idfornitore'] = "";
            $_POST['fornitore'] = "";
        }

        /* tolgo dall'array submit e post che non mi servono */
        unset($_POST['submit']);

        foreach ($_POST as $k => $v) {
            $campi[] = $k;
            $valori[] = $v;
        }
        /* funzione aggiungi */
        $lastid = $magazzino->aggiungi($campi, $valori);

        die('{"msg" : "ok", "lastitem": "' . $lastid . '"}');

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
        <script type="text/javascript" src="./js/functions-magazzino.js"></script>

        <link type="text/css" rel="stylesheet" href="./js/jsgrid-1.5.1/jsgrid.min.css" />
        <link type="text/css" rel="stylesheet" href="./js/jsgrid-1.5.1/jsgrid-theme.min.css" />

        <script type="text/javascript" src="./js/jsgrid-1.5.1/jsgrid.min.js"></script>
        <script type="text/javascript" src="./js/jsgrid-1.5.1/i18n/jsgrid-it.js"></script>
        <!-- lingua italiana datepicker -->
        <script src="./js/jquery-ui-1.11.4/datepicker-it.js"></script>
        <!-- editor -->
        <script type="text/javascript" src="./tinymce/js/tinymce/tinymce.min.js"></script>
        <script type="text/javascript" src="./js/includeeditor.js"></script>
        <!-- moment.js -->
        <script type="text/javascript" src="./js/moment.js"></script>

        <script type="text/javascript">
            $(document).ready(function () {
                mostraMagazzino('', '');
            });
        </script>


    </head>
    <body class="colormodulo">
        <?php include './components/top.php'; ?>
        <?php if (moduloattivo("magazzino") > 0) { ?>
            <div class="barra_submenu sizing">
                <li class="box_submenu sizing"><a href="magazzino.php"><i class="fa fa-file-text-o fa-lg" aria-hidden="true"></i> Magazzino</a></li>

            </div>
            <div class="content sizing">
                <div class="barra_op sizing">
                    <div class="bottone sizing"><a href="javascript:;" onclick="mostraMagazzino('', '');"><i class="fa fa-refresh fa-lg" aria-hidden="true"></i> Ricarica magazzino</a></div>                            
                    <?php
                    if ($op == "archivio") {
                        
                    }
                    ?>
                    <div id="messaggio" class="messaggiook">Operazione avvenuta con successo</div>

                </div>
                <div class="showcont sizing">
                    <!-- qui contenuto del mostra anagrafiche e aggiungi anagrafiche --> 
                </div>
            </div>
        <?php } ?>
    </body>
</html>