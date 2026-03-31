<?php
ob_start('ob_gzhandler');
include './library/connessione.php';
include './library/controllo.php';
include './library/config.php';
include './library/functions.php';
include './library/basic.class.php';
/* libreria del modulo */
include './library/ddt.class.php';

/**/
$submit = "";
if (isset($_POST['submit'])) {
    $submit = $_POST['submit'];
}

$op = "";
if (isset($_GET['op'])) {
    $op = $_GET['op'];
}

switch ($submit) {
    case "copyddt":
        $id = $_POST['id'];
        /* tabella temporanea */
        $sql = $db->prepare("CREATE TEMPORARY TABLE tabella_tmp SELECT * FROM ddt WHERE id = ?");
        $sql->execute(array($id));
        $sql = $db->prepare("UPDATE tabella_tmp SET id = (SELECT MAX(id)+1 from ddt), data = ?, numero = (SELECT MAX(numero)+1 FROM ddt WHERE YEAR(data) = ?) WHERE id = ?");
        $sql->execute(array(date("Y-m-g"), date("Y"), $id));
        /* inserisco nella tabella ddt */
        $sql = $db->prepare("INSERT INTO ddt SELECT * FROM tabella_tmp");
        $sql->execute();
        /* last id per voci prezzi ddt */
        $last_id = $db->lastInsertId();
        /* cancello temporanea */
        $sql = $db->prepare("DROP TABLE tabella_tmp");
        $sql->execute();
        /**/
        /* copio voci prezzi */
        $sql = $db->prepare("CREATE TEMPORARY TABLE tabella_tmp SELECT * FROM ddt_voci WHERE idddt = ?");
        $sql->execute(array($id));
        /* annullo id */
        $sql = $db->prepare("UPDATE tabella_tmp SET id = NULL, idddt = ?");
        $sql->execute(array($last_id));
        /* inserisco nella tabella ddtvoci */
        $sql = $db->prepare("INSERT INTO ddt_voci SELECT * FROM tabella_tmp");
        $sql->execute();
        /* cancello temporanea */
        $sql = $db->prepare("DROP TABLE tabella_tmp");
        $sql->execute();


        die('{"msg": "ok"}');

        break;
    case "editformddt":
        $tabella = "ddt";
        $id = $_POST['id'];
        $ddt = new ddt($id, $tabella);
        /* voci del ddt */
        for ($i = 0; $i < count($_POST['nome']); $i++) {
            if ($_POST['nome'][$i] != "" || $_POST['descr'][$i] != "") {
                $voci[$i] = array("nome" => $_POST['nome'][$i], "descr" => $_POST['descr'][$i], "qta" => $_POST['qta'][$i], "prezzo" => $_POST['prezzo'][$i],
                    "sconto" => $_POST['sconto'][$i], "scontato" => $_POST['scontato'][$i], "ordine" => $i);
            } else {
                
            }
        }
        array_filter($voci, 'strlen'); // controllo array vuoto

        /* tolgo dall'array submit e post che non mi servono */
        unset($_POST['submit'], $_POST['nome'], $_POST['descr'], $_POST['qta'], $_POST['prezzo'], $_POST['sconto'], $_POST['scontato']);

        foreach ($_POST as $k => $v) {
            $campi[] = $k;
            $valori[] = $v;
        }

        $ddt->aggiorna($campi, $valori, $voci);
        die('{"msg": "ok"}');
        break;

    case "richiamaddt":
        $tabella = "ddt";
        $id = $_POST['id'];
        $dati = new ddt($id, $tabella);
        $ddt = $dati->richiama();
        /* voci */
        $tabella = "ddt_voci";
        $where = "idddt = $id ORDER BY ordine";
        $dativoci = new ddt($id, $tabella);
        $voci = $dativoci->richiamaWhere($where);
        $magazzino = getDati("magazzino", "");
        die('{"valori" : ' . json_encode($ddt) . ', "voci" : ' . json_encode($voci) . ', "magazzino" : ' . json_encode($magazzino) . '}');
        break;

    case "delete":
        $tabella = "ddt";
        $id = $_POST['id'];
        $dati = new ddt($id, $tabella);
        $ddt = $dati->cancella();
        die('{"msg": "ok"}');
        break;

    case "mostraddt":
        $tabella = "ddt";
        $id = $_POST['id'];
        $dati = new ddt($id, $tabella);
        $arch = $_POST['arch'];
        if ($arch == 1) {
            $where = "stato = '5' OR stato = '4' OR YEAR(data) != '" . DATE("Y") . "' ORDER BY numero DESC, data";
        } else {
            $where = "stato != '5' AND stato != '4' AND YEAR(data) = '" . DATE("Y") . "' ORDER BY numero DESC, data";
        }
        $ddt = $dati->richiamaWheredata($where);

        die('{"dati" : ' . json_encode($ddt) . '}');
        break;

    case "submitformddt":
        $tabella = "ddt";

        $ddt = new ddt($id, $tabella);
        /* voci del preventivo */
        for ($i = 0; $i < count($_POST['nome']); $i++) {
            if ($_POST['nome'][$i] != "" || $_POST['descr'][$i] != "") {
                $voci[$i] = array("nome" => $_POST['nome'][$i], "descr" => $_POST['descr'][$i], "qta" => $_POST['qta'][$i], "prezzo" => $_POST['prezzo'][$i],
                    "sconto" => $_POST['sconto'][$i], "scontato" => $_POST['scontato'][$i], "ordine" => $i);
            } else {
                
            }
        }
        array_filter($voci, 'strlen'); // controllo array vuoto

        /* tolgo dall'array submit e post che non mi servono */
        unset($_POST['submit'], $_POST['nome'], $_POST['descr'], $_POST['qta'], $_POST['prezzo'], $_POST['sconto'], $_POST['scontato']);

        foreach ($_POST as $k => $v) {
            $campi[] = $k;
            $valori[] = $v;
        }
        /* funzione aggiungi */
        $ddt->aggiungi($campi, $valori, $voci);

        die('{"msg" : "ok"}');

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
        <script type="text/javascript" src="./js/functions-ddt.js"></script>

        <link type="text/css" rel="stylesheet" href="./js/jsgrid-1.5.1/jsgrid.min.css" />
        <link type="text/css" rel="stylesheet" href="./js/jsgrid-1.5.1/jsgrid-theme.min.css" />

        <script type="text/javascript" src="./js/jsgrid-1.5.1/jsgrid.min.js"></script>
        <script type="text/javascript" src="./js/jsgrid-1.5.1/i18n/jsgrid-it.js"></script>
        <!-- lingua italiana datepicker -->
        <script src="./js/jquery-ui-1.11.4/datepicker-it.js"></script>
        <!-- datetimepicker -->
        <link rel="stylesheet" type="text/css" href="./js/datetimepicker-master/jquery.datetimepicker.css" />
        <script type="text/javascript" src="./js/datetimepicker-master/build/jquery.datetimepicker.full.min.js"></script>
        <!-- editor -->
        <script type="text/javascript" src="./tinymce/js/tinymce/tinymce.min.js"></script>
        <script type="text/javascript" src="./js/includeeditor.js"></script>

        <script type="text/javascript">
            $(document).ready(function () {

<?php if ($op == "archivio") { ?>
                    mostraDDT('1', '');
<?php } else { ?>
                    mostraDDT('', '');
<?php } ?>
            });
        </script>


    </head>
    <body class="colormodulo">
        <?php include './components/top.php'; ?>
        <?php if (moduloattivo("ddt") > 0) { ?>
            <div class="barra_submenu sizing">
                <li class="box_submenu sizing"><a href="ddt.php?op=preventivi"><i class="fa fa-file-text-o fa-lg" aria-hidden="true"></i> DDT</a></li>
                <li class="box_submenu sizing"><a href="ddt.php?op=archivio"><i class="fa fa-file-archive-o fa-lg" aria-hidden="true"></i> DDT archiviati</a></li>

            </div>
            <div class="content sizing">
                <div class="barra_op sizing">
                    <div class="bottone sizing"><a href="javascript:;" onclick="aggiungi();"><i class="fa fa-user-plus fa-lg" aria-hidden="true"></i> Aggiungi DDT</a></div>                            
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