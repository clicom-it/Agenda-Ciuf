<?php
ob_start('ob_gzhandler');
include './library/connessione.php';
include './library/controllo.php';
include './library/config.php';
include './library/functions.php';
include './library/basic.class.php';
/* libreria del modulo */
include './library/preventivi.class.php';

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
    case "copyprev":
        $id = $_POST['id'];
        /* tabella temporanea */
        $sql = $db->prepare("CREATE TEMPORARY TABLE tabella_tmp SELECT * FROM preventivi WHERE id = ?");
        $sql->execute(array($id));
        $sql = $db->prepare("UPDATE tabella_tmp SET id = (SELECT MAX(id)+1 from preventivi), idp ='0', rev = '', data = ?, numero = (SELECT MAX(numero)+1 FROM preventivi WHERE YEAR(data) = ?) WHERE id = ?");
        $sql->execute(array(date("Y-m-g"), date("Y"), $id));
        /* inserisco nella tabella preventivi */
        $sql = $db->prepare("INSERT INTO preventivi SELECT * FROM tabella_tmp");
        $sql->execute();
        /* last id per voci prezzi preventivo */
        $last_id = $db->lastInsertId();
        /* cancello temporanea */
        $sql = $db->prepare("DROP TABLE tabella_tmp");
        $sql->execute();
        /**/
        /* copio voci prezzi */
        $sql = $db->prepare("CREATE TEMPORARY TABLE tabella_tmp SELECT * FROM preventivi_voci WHERE idprev = ?");
        $sql->execute(array($id));
        /* annullo id */
        $sql = $db->prepare("UPDATE tabella_tmp SET id = NULL, idprev = ?");
        $sql->execute(array($last_id));
        /* inserisco nella tabella preventivi_voci */
        $sql = $db->prepare("INSERT INTO preventivi_voci SELECT * FROM tabella_tmp");
        $sql->execute();
        /* cancello temporanea */
        $sql = $db->prepare("DROP TABLE tabella_tmp");
        $sql->execute();


        die('{"msg": "ok"}');

        break;
    case "editformpreventivo":
        $tabella = "preventivi";
        $id = $_POST['id'];
        $preventivi = new preventivi($id, $tabella);
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

        $preventivi->aggiorna($campi, $valori, $voci);
        die('{"msg": "ok"}');
        break;

    case "richiamapreventivo":
        $tabella = "preventivi";
        $id = $_POST['id'];
        $dati = new preventivi($id, $tabella);
        $preventivo = $dati->richiama();
        /* voci */
        $tabella = "preventivi_voci";
        $where = "idprev = $id ORDER BY ordine";
        $dativoci = new preventivi($id, $tabella);
        $voci = $dativoci->richiamaWhere($where);
        $magazzino = getDati("magazzino", "");
        die('{"valori" : ' . json_encode($preventivo) . ', "voci" : ' . json_encode($voci) . ', "magazzino" : ' . json_encode($magazzino) . '}');
        break;

    case "delete":
        $tabella = "preventivi";
        $id = $_POST['id'];
        $dati = new preventivi($id, $tabella);
        $preventivi = $dati->cancella();
        die('{"msg": "ok"}');
        break;

    case "mostrapreventivi":
        $tabella = "preventivi";
        $id = $_POST['id'];
        $dati = new preventivi($id, $tabella);
        $arch = $_POST['arch'];
        if ($arch == 1) {
            $where = "stato = '5' OR stato = '4' OR YEAR(data) != '" . DATE("Y") . "' ORDER BY numero DESC, rev DESC, data";
        } else {
            $where = "stato != '5' AND stato != '4' AND YEAR(data) = '" . DATE("Y") . "' ORDER BY numero DESC, rev DESC, data";
        }
        $preventivi = $dati->richiamaWheredata($where);

        die('{"dati" : ' . json_encode($preventivi) . '}');
        break;

    case "submitformpreventivi":
        $tabella = "preventivi";
        if ($_POST['revisione'] > 0) {
            /* controllo se è una revisione */
            if ($_POST['idp'] == 0) {
                $_POST['idp'] = $_POST['id'];
            }
            unset($_POST['id'], $_POST['revisione']);
            /* progressivo revisione */
            $sql = $db->prepare("SELECT MAX(rev) as maxrev FROM preventivi WHERE idp = ?");
            $sql->execute(array($_POST['idp']));
            $_POST['rev'] = $sql->fetchColumn() + 1;
        }
        $preventivi = new preventivi($id, $tabella);
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
        $preventivi->aggiungi($campi, $valori, $voci);

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
        <script type="text/javascript" src="./js/functions-preventivi.js"></script>

        <link type="text/css" rel="stylesheet" href="./js/jsgrid-1.5.1/jsgrid.min.css" />
        <link type="text/css" rel="stylesheet" href="./js/jsgrid-1.5.1/jsgrid-theme.min.css" />

        <script type="text/javascript" src="./js/jsgrid-1.5.1/jsgrid.min.js"></script>
        <script type="text/javascript" src="./js/jsgrid-1.5.1/i18n/jsgrid-it.js"></script>
        <!-- lingua italiana datepicker -->
        <script src="./js/jquery-ui-1.11.4/datepicker-it.js"></script>
        <!-- editor -->
        <script type="text/javascript" src="./tinymce/js/tinymce/tinymce.min.js"></script>
        <script type="text/javascript" src="./js/includeeditor.js"></script>

        <script type="text/javascript">
            $(document).ready(function () {

<?php if ($op == "archivio") { ?>
                    mostraPreventivi('1', '');
<?php } else { ?>
                    mostraPreventivi('', '');
<?php } ?>
            });
        </script>


    </head>
    <body class="colormodulo">
        <?php include './components/top.php'; ?>
        <?php if (moduloattivo("preventivi") > 0) { ?>
            <div class="barra_submenu sizing">
                <li class="box_submenu sizing"><a href="preventivi.php?op=preventivi"><i class="fa fa-file-text-o fa-lg" aria-hidden="true"></i> Preventivi</a></li>
                <li class="box_submenu sizing"><a href="preventivi.php?op=archivio"><i class="fa fa-file-archive-o fa-lg" aria-hidden="true"></i> Preventivi archiviati</a></li>

            </div>
            <div class="content sizing">
                <div class="barra_op sizing">
                    <div class="bottone sizing"><a href="javascript:;" onclick="aggiungi();"><i class="fa fa-user-plus fa-lg" aria-hidden="true"></i> Aggiungi preventivo</a></div>                            
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