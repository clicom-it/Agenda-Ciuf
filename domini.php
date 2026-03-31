<?php
ob_start('ob_gzhandler');
include './library/connessione.php';
include './library/controllo.php';
include './library/config.php';
include './library/functions.php';
include './library/basic.class.php';
/* libreria del modulo */
include './library/domini.class.php';

/**/
$submit = "";
if (isset($_POST['submit'])) {
    $submit = $_POST['submit'];
}

switch ($submit) {

    case "edit":
        $tabella = "domini";
        $id = $_POST['id'];
        $domini = new domini($id, $tabella);

        /* converto data in formato corretto */
        $datadb = date('Y-m-d', strtotime($_POST['dataattivazione']));
        $_POST['dataattivazione'] = $datadb;
        /**/
        /* sistemo idcliente e nomecliente */
        if ($_POST['cliente'] > 0) {
            $arrcli = explode("|||", $_POST['cliente']);
            $_POST['idcliente'] = $arrcli[0];
            $_POST['cliente'] = $arrcli[1];
        } else {
            unset($_POST['idcliente'], $_POST['cliente']);
        }

        /* tolgo dall'array submit e post che non mi servono */
        unset($_POST['submit']);

        foreach ($_POST as $k => $v) {
            $campi[] = $k;
            $valori[] = $v;
        }

        $domini->aggiorna($campi, $valori);
        die('{"msg": "ok"}');
        break;

    case "delete":
        $tabella = "domini";
        $id = $_POST['id'];
        $dati = new domini($id, $tabella);
        $domini = $dati->cancella();
        die('{"msg": "ok"}');
        break;

    case "mostradomini":
        $tabella = "domini";
        $id = $_POST['id'];
        $dati = new domini($id, $tabella);
        $domini = $dati->richiamaWhere("id > 0 ORDER BY idcliente");

        $tabella = "clienti_fornitori";
        $daticli = new domini($id, $tabella);
        $cli = $daticli->richiamaWhere("tipo = '1'");
        $arrcli[] = array("name" => "Seleziona cliente...", "Id" => "");
        foreach ($cli as $clid) {
            $arrcli[] = array("name" => $clid['azienda'], "Id" => $clid['id']);
        }


        die('{"dati" : ' . json_encode($domini) . ', "clientiautocomplete" : ' . json_encode($cli) . '}');
        break;

    case "insert":
        $tabella = "domini";

        $domini = new domini($id, $tabella);
        /* converto data in formato corretto */
        $datadb = date('Y-m-d', strtotime($_POST['dataattivazione']));
        $_POST['dataattivazione'] = $datadb;
        /**/
        /* sistemo idcliente e nomecliente */
        $arrcli = explode("|||", $_POST['cliente']);
        $_POST['idcliente'] = $arrcli[0];
        $_POST['cliente'] = $arrcli[1];

        /* tolgo dall'array submit e post che non mi servono */
        unset($_POST['submit']);

        foreach ($_POST as $k => $v) {
            $campi[] = $k;
            $valori[] = $v;
        }
        /* funzione aggiungi */
        $lastid = $domini->aggiungi($campi, $valori);

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
        <script type="text/javascript" src="./js/functions-domini.js"></script>

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
                mostraDomini('', '');
            });
        </script>


    </head>
    <body class="colormodulo">
        <?php include './components/top.php'; ?>
        <?php if (moduloattivo("domini") > 0) { ?>
            <div class="barra_submenu sizing">
                <li class="box_submenu sizing"><a href="domini.php"><i class="fa fa-file-text-o fa-lg" aria-hidden="true"></i> Domini</a></li>

            </div>
            <div class="content sizing">
                <div class="barra_op sizing">
                    <div class="bottone sizing"><a href="javascript:;" onclick="mostraDomini('', '');"><i class="fa fa-refresh fa-lg" aria-hidden="true"></i> Ricarica domini</a></div>                            
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