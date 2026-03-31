<?php
ob_start('ob_gzhandler');
include './library/connessione.php';
include './library/controllo.php';
include './library/config.php';
include './library/functions.php';
include './library/basic.class.php';
/* libreria del modulo */
include './library/ecommerce.class.php';
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
    case "importa":
        /* richiamo dati connessione ecommerce da database */

        $dati = getDati("conf_ecommerce", "");
        foreach ($dati as $datigest) {
            $host_db = $datigest['host_db'];
            $nome_db = $datigest['nome_db'];
            $usr_db = $datigest['usr_db'];
            $pass_db = $datigest['pass_db'];
        }

        $colimport = 'mysql:host=' . $host_db . ';dbname=' . $nome_db . ';charset=latin1;';
// blocco try per il lancio dell'istruzione
        try {
            // connessione tramite creazione di un oggetto PDO
            $dbimport = new PDO($colimport, '' . $usr_db . '', '' . $pass_db . '', array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'latin1'"));
        }
// blocco catch per la gestione delle eccezioni
        catch (PDOException $e) {
            // notifica in caso di errorre
            echo 'Errore di connessione: ' . $e->getMessage();
        }

        $ecomm = new ecommerce($id, "ordini");
        $ecomm_prodotti = new ecommerce($id, "ordini_voci");

        /* seleziono ordini da importare  */
        $sql = $dbimport->prepare("SELECT id, idutente, data_ordine, num_ordine, commissione_pagamento, totale, peso, buono, codice_buono, appuntamento, pagamento, spedizione FROM ordini WHERE importato = ? AND num_ordine > 0 AND stato =?");
        $sql->execute(array(0, 2));
        if ($sql->rowCount() > 0) {
            $res = $sql->fetchAll();

            foreach ($res as $row) {
                $idordine = $row['id'];
                $idutente = $row['idutente'];
                $data_ordine = $row['data_ordine'];
                $num_ordine = $row['num_ordine'];
                $commissione_pagamento = $row['commissione_pagamento'];
                $totale = $row['totale'];
                $peso = $row['peso'];
                $buono = $row['buono'];
                $codice_buono = $row['codice_buono'];
                $appuntamento = $row['appuntamento'];
                $pagamento = $row['pagamento'];
                if ($pagamento == 4) {
                    $pagamento = 16;
                } else if ($pagamento == 9) {
                    $pagamento = 17;
                } else if ($pagamento == 8) {
                    $pagamento = 5;
                }

                $sqlp = $db->prepare("SELECT nome FROM metodi_pagamento WHERE id = ? LIMIT 1");
                $sqlp->execute(array($pagamento));
                $metodopagamento = $sqlp->fetchColumn();

                $spedizione = $row['spedizione'];

                /* dati utente da ordine */
                $sqlu = $dbimport->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
                $sqlu->execute(array($idutente));
                $resu = $sqlu->fetchAll();

                foreach ($resu as $rowu) {
                    $nome = $rowu['nome'];
                    $cognome = $rowu['cognome'];
                    $azienda = $rowu['azienda'];
                    if (!$azienda) {
                        $azienda = $nome . " " . $cognome;
                    }
                    $email = $rowu['email'];
                    $telefono = $rowu['telefono'];
                    if ($telefono[0] != 0) {
                        $cellulare = $telefono;
                        $telefono = "";
                    } else {
                        $cellulare = "";
                    }
                    $nazione = $rowu['nazione'];
                    $regione = $rowu['regione'];
                    $provincia = $rowu['provincia'];
                    $comune = $rowu['comune'];
                    $cap = $rowu['cap'];
                    $indirizzo = $rowu['indirizzo'];
                    $codicefiscale = $rowu['codicefiscale'];
                    $piva = $rowu['piva'];
                    $sdi = $rowu['sdi'];
                    $pec = $rowu['pec'];
                    $nominativo = $rowu['nominativo'];
                    $indirizzospedizione = $rowu['indirizzospedizione'];
                    $nazionespedizione = $rowu['nazionespedizione'];
                    $regionespedizione = $rowu['regionespedizione'];
                    $provinciaspedizione = $rowu['provinciaspedizione'];
                    $comunespedizione = $rowu['comunespedizione'];
                    $capspedizione = $rowu['capspedizione'];
                }
                /**/

                $campicliente = array("tipo", "nome", "cognome", "azienda", "email", "telefono", "cellulare", "nazione", "regione", "provincia", "comune", "cap", "indirizzo",
                    "codicefiscale", "piva", "fm_vf", "codice_sdi", "pec", "nominativo", "indirizzospedizione", "nazionespedizione", "regionespedizione", "provinciaspedizione",
                    "comunespedizione", "capspedizione", "metodopagamento");

                $valoricliente = array(1, $nome, $cognome, $azienda, $email, $telefono, $cellulare, $nazione, $regione, $provincia, $comune, $cap, $indirizzo,
                    $codicefiscale, $piva, 1, $sdi, $pec, $nominativo, $indirizzospedizione, $nazionespedizione, $regionespedizione, $provinciaspedizione, $comunespedizione,
                    $capspedizione, $pagamento);

                $sqlchecku = $db->prepare("SELECT * FROM clienti_fornitori WHERE (codicefiscale != '' AND codicefiscale = ?) OR (piva != '' AND piva = ?) LIMIT 1");
                $sqlchecku->execute(array($codicefiscale, $piva));
                if ($sqlchecku->rowCount() > 0) {
                    $reschecku = $sqlchecku->fetch();
                    $idcliente = $reschecku['id'];
                    $clienti = new ecommerce($idcliente, "clienti_fornitori");
                    $clienti->aggiorna($campicliente, $valoricliente);
                } else {
                    $clienti = new ecommerce($id, "clienti_fornitori");
                    $clienti->aggiungi($campicliente, $valoricliente);
                    $idcliente = $db->lastInsertId();
                }

                /* inserisco l'ordine */
                $campiordine = array("id", "idcliente", "daticliente", "destinazione", "data", "peso", "spedizione", "buono", "codice_buono", "idpagamento", "metodopagamento", "commissione_pagamento", "totale",
                    "numero", "appuntamento");

                $valoriordine = array($idordine, $idcliente, $nome . " " . $cognome . " " . $azienda . "\n" . $indirizzo . "\n" . $cap . " " . $comune . " (" . $provincia . ")",
                    $nominativo . "\n" . $indirizzospedizione . "\n" . $capspedizione . " " . $comunespedizione . " (" . $provinciaspedizione . ")", $data_ordine, $peso,
                    $spedizione, $buono, $codice_buono, $pagamento, $metodopagamento, $commissione_pagamento, $totale, $num_ordine, $appuntamento);

                $ecomm->aggiungi($campiordine, $valoriordine);


                /* inserisco prodotti dell'ordine */
                $sql2 = $dbimport->prepare("SELECT * FROM prodotti_ordini WHERE idordine = ?");
                $sql2->execute(array($idordine));
                $res2 = $sql2->fetchAll();
                foreach ($res2 as $row2) {
//                        $campi = array();
//                        $valori = array();
                    $campi = array("idordine", "descr", "qta", "prezzo", "scontato");
                    $valori = array($idordine, $row2['titolo_prodotto'] . " " . $row2['titolo_variante'], $row2['quantita'], $row2['prezzo_noiva'], $row2['quantita']*$row2['prezzo_noiva']);

                    $ecomm_prodotti->aggiungi($campi, $valori);
                }

                $sqlf = $dbimport->prepare("UPDATE ordini SET importato = ?, stato = ? WHERE id = ?");
                $sqlf->execute(array(1, 8, $idordine));
            }
        }

        die('{"msg" : "ok"}');
        break;

    case "editformordine":
        $tabella = "ordini";
        $id = $_POST['id'];
        $ordine = new ecommerce($id, $tabella);
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

        /* nome metodo di pagamento */
        $idmetodo = $_POST['idpagamento'];
        $sql = $db->prepare("SELECT nome FROM metodi_pagamento WHERE id = ?");
        $sql->execute(array($idmetodo));
        $_POST['metodopagamento'] = $sql->fetchColumn();
        /**/


        foreach ($_POST as $k => $v) {
            $campi[] = $k;
            $valori[] = $v;
        }

        $ordine->aggiorna($campi, $valori, $voci);
        die('{"msg": "ok"}');
        break;

    case "richiamaordine":
        $tabella = "ordini";
        $id = $_POST['id'];
        $dati = new ecommerce($id, $tabella);
        $ordine = $dati->richiama();
        /* voci */
        $tabellap = "ordini_voci";
        $where = "idordine = $id";
        $dativoci = new ecommerce($id, $tabellap);
        $voci = $dativoci->richiamaWhere($where);
        $magazzino = getDati("magazzino", "");
        die('{"valori" : ' . json_encode($ordine) . ', "voci" : ' . json_encode($voci) . ', "magazzino" : ' . json_encode($magazzino) . '}');
        break;

    case "delete":
        $tabella = "ordini";
        $id = $_POST['id'];
        $dati = new ecommerce($id, $tabella);
        $ecommerce = $dati->cancella();
        die('{"msg": "ok"}');
        break;

    case "mostraordini":
        $tabella = "ordini";
        $id = $_POST['id'];
        $dati = new ecommerce($id, $tabella);
        
        $arch = $_POST['arch'];
        if ($arch == 1) {
            $where = "stato = '1' ORDER BY data DESC, numero DESC";
        } else {
            $where = "numero > 0 AND stato = '0' ORDER BY data DESC, numero DESC";
        }
        
        
        $ordini = $dati->richiamaWhereOrdine($where);


        die('{"dati" : ' . json_encode($ordini) . '}');
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
        <script type="text/javascript" src="./js/functions-ecommerce.js"></script>

        <link type="text/css" rel="stylesheet" href="./js/jsgrid-1.5.1/jsgrid.min.css" />
        <link type="text/css" rel="stylesheet" href="./js/jsgrid-1.5.1/jsgrid-theme.min.css" />

        <script type="text/javascript" src="./js/jsgrid-1.5.1/jsgrid.min.js"></script>
        <script type="text/javascript" src="./js/jsgrid-1.5.1/i18n/jsgrid-it.js"></script>
        <!-- lingua italiana datepicker -->
        <script src="./js/jquery-ui-1.11.4/datepicker-it.js"></script>

        <script type="text/javascript">
            $(document).ready(function () {

<?php if ($op == "archivio") { ?>
                    mostraOrdini('1', '', '');
<?php } else { ?>
                    mostraOrdini('', '', '');
<?php } ?>
            });
        </script>


    </head>
    <body class="colormodulo">
        <?php include './components/top.php'; ?>
        <?php if (moduloattivo("ecommerce") > 0) { ?>
            <div class="barra_submenu sizing">
                <li class="box_submenu sizing"><a href="javascript:;" onclick="mostraOrdini();"><i class="fa fa-file-text fa-lg" aria-hidden="true"></i> Ordini</a></li>
                <li class="box_submenu sizing"><a href="/ecommerce.php?op=archivio" ><i class="fa fa-file-text fa-lg" aria-hidden="true"></i> Ordini Fatturati</a></li>
            </div>
            <div class="content sizing">
                <div class="barra_op sizing">
                    <div class="bottone sizing"><a href="javascript:;" onclick="importa();"><i class="fa fa-plus-circle fa-lg" aria-hidden="true"></i> Importa ordini</a></div>
                    <div id="messaggio" class="messaggiook">Operazione avvenuta con successo</div>

                </div>
                <div class="showcont sizing">
                    <!-- qui contenuto del mostra anagrafiche e aggiungi anagrafiche --> 
                </div>
            </div>
        <?php } ?>
    </body>
</html>