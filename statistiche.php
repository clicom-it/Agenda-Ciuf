<?php
ob_start('ob_gzhandler');
include './library/connessione.php';
include './library/controllo.php';
include './library/config.php';
include './library/functions.php';
include './library/basic.class.php';
/* libreria del modulo */
include './library/statistiche.class.php';
/**/
$submit = "";
if (isset($_POST['submit'])) {
    $submit = $_POST['submit'];
} elseif (isset($_GET['submit'])) {
    $submit = $_GET['submit'];
}

$op = "";
if (isset($_GET['op'])) {
    $op = $_GET['op'];
}
$now = date('Y-m-d');
switch ($submit) {

    case "submitstatisticheadmin":

        $idat = $_POST['idatelier'];
        $data_dal = $_POST['data'];
        $data_al = $_POST['data2'];
        $diraff = $_POST['diraff'];

        /* statistiche per diretti/affiliati o singolo atelier */
        if ($diraff) {
            $sql = $db->prepare("SELECT id FROM utenti WHERE diraff = ?");
            $sql->execute(array($diraff));
        } else {
            $sql = $db->prepare("SELECT id FROM utenti WHERE id = ?");
            $sql->execute(array($idat));
        }
        /* statistiche per anno o intervallo di date */
        if (!$data_dal && !$data_al) {
            $where .= "YEAR(data) = '" . date(Y) . "'";
            $whereaccsart .= "YEAR(calendario.data) = '" . date(Y) . "'";
        } else {
            if ($data_al && !$data_dal) {
                $data_dal = DATE("Y-m-d");
            } else if ($data_dal && !$data_al) {
                $data_al = date('Y-m-d', strtotime($data_dal . ' +5 year'));
            }
            $where .= "(data BETWEEN '$data_dal' AND '$data_al')";
            $whereaccsart .= "(calendario.data BETWEEN '$data_dal' AND '$data_al')";
        }
        $res = $sql->fetchAll();
        foreach ($res as $row) {
            $idatelier = $row['id'];
            $nomeatelier = getDati("utenti", "WHERE id=$idatelier")[0]['nominativo'];
            $direttoaffiliato = getDati("utenti", "WHERE id=$idatelier")[0]['diraff'];

            $and = " AND idatelier = $idatelier and tipoappuntamento!='5'";
            $andaccsart = " AND calendario.idatelier = $idatelier";

            /* Appuntamenti totale */
            $qry = 'SELECT COUNT(*) totappuntamenti FROM calendario WHERE ' . $where . ' ' . $and . ' GROUP BY idatelier';
            //die($qry);
            $sql2 = $db->prepare($qry);
            $sql2->execute();
            $res2 = $sql2->fetch();
            $totaleappuntamenti = $res2['totappuntamenti'];
            if (!$totaleappuntamenti) {
                $totaleappuntamenti = 0;
            }
            /* Appuntamenti disdetti */
            $qry = 'SELECT COUNT(*) totappuntamenti FROM calendario WHERE disdetto=1 and ' . $where . ' ' . $and . ' GROUP BY idatelier';
            //die($qry);
            $sql2 = $db->prepare($qry);
            $sql2->execute();
            $res2 = $sql2->fetch();
            $totaleappuntamenti_disdetti = $res2['totappuntamenti'];
            if (!$totaleappuntamenti_disdetti) {
                $totaleappuntamenti_disdetti = 0;
            }
            /* Appuntamenti acquistato */
            $sql3 = $db->prepare('SELECT COUNT(*) acquistato, SUM(totalespesa) as totalespesa, AVG(totalespesa) as mediaspesa, SUM(saldo) as daincassare FROM calendario WHERE ' . $where . ' ' . $and . ' AND acquistato = ? GROUP BY idatelier');
            $sql3->execute(array(1));
            $res3 = $sql3->fetch();
            $totalespesa = $res3['totalespesa'];
//            $totaleincassato = $res3['totaleincassato'];
            $daincassare = $res3['daincassare'];
            $totaleincassato = $totalespesa - $daincassare;
            $mediaspesa = $res3['mediaspesa'];

            $acquistato_appuntamenti = $res3['acquistato'];
            if (!$acquistato_appuntamenti) {
                $acquistato_appuntamenti = 0;
                $datiappuntamenti['utp'] = 0;
            } else {

                $sqlma = $db->prepare('SELECT COUNT(*) totaccutp FROM `accessori_appuntamento` WHERE idappuntamento = ANY(SELECT id FROM calendario WHERE ' . $whereaccsart . ' ' . $andaccsart . ' AND calendario.id = accessori_appuntamento.idappuntamento AND calendario.acquistato = ?)');
//                die('SELECT COUNT(*) totaccutp FROM `accessori_appuntamento` WHERE idappuntamento = ANY(SELECT id FROM calendario WHERE ' . $whereaccsart . ' ' . $andaccsart . ' AND calendario.id = accessori_appuntamento.idappuntamento AND calendario.acquistato = ?)');
                $sqlma->execute(array(1));
                if ($sqlma->rowCount() > 0) {

                    $resma = $sqlma->fetch();

                    $totacc_perapp = $acquistato_appuntamenti + $resma['totaccutp'];
                    $utp = round($totacc_perapp / $acquistato_appuntamenti, 2);

                    $datiappuntamenti['utp'] = number_format($utp, 2, ".", "");
                } else {
                    $datiappuntamenti['utp'] = 0;
                }
            }

            /* Appuntamenti chiusi */
            $sql4 = $db->prepare('SELECT COUNT(*) totappuntamentichiusi FROM calendario WHERE ' . $where . ' ' . $and . ' AND acquistato != "" GROUP BY idatelier');
            $sql4->execute();
            $res4 = $sql4->fetch();
            $totaleappuntamentichiuisi = $res4['totappuntamentichiusi'];
            if (!$totaleappuntamentichiuisi) {
                $totaleappuntamentichiuisi = 0;
            }
            /* Appuntamenti non acquistato */
            $nonacquistato_appuntamenti = $totaleappuntamentichiuisi - $acquistato_appuntamenti;
            /* Rapporto acquistato */
            $rapporto_acquistato = $acquistato_appuntamenti * 100 / $totaleappuntamentichiuisi;
            if (is_nan($rapporto_acquistato)) {
                $rapporto_acquistato = 0;
            }
            /* Rapposto non acquistato */
            $rapporto_nonacquistato = $nonacquistato_appuntamenti * 100 / $totaleappuntamentichiuisi;
            if (is_nan($rapporto_nonacquistato)) {
                $rapporto_nonacquistato = 0;
            }

            /**/
            $datiappuntamenti['atelier'] = $nomeatelier;
            $datiappuntamenti['totaleappuntamenti'] = $totaleappuntamenti;
            if ($totaleappuntamenti_disdetti > 0) {
                $perc_disdetti = round(100 * $totaleappuntamenti_disdetti / $totaleappuntamenti, 0);
            } else {
                $perc_disdetti = 0;
            }
            $datiappuntamenti['totaleappuntamenti_disdetti'] = $totaleappuntamenti_disdetti . '(' . $perc_disdetti . '%)';
            $datiappuntamenti['totaleappuntamentichiusi'] = $totaleappuntamentichiuisi;
            $datiappuntamenti['acquistato'] = $acquistato_appuntamenti;
            $datiappuntamenti['nonacquistato'] = $nonacquistato_appuntamenti;
            $datiappuntamenti['totalespesa'] = number_format($totalespesa, 2, ".", "");
            $datiappuntamenti['totaleincassato'] = number_format($totaleincassato, 2, ".", "");
            $datiappuntamenti['daincassare'] = number_format($daincassare, 2, ".", "");
            $datiappuntamenti['mediaspesa'] = number_format($mediaspesa, 2, ".", "");

            $arrappuntamenti[] = $datiappuntamenti;

            /* tipo abito venduto */
            $datiabiti['atelier'] = $nomeatelier;
            $sql5 = $db->prepare('SELECT idtipoabito, COUNT(*) totaleabiti, nometipoabito, SUM(prezzoabito) as prezzoabiti, AVG(prezzoabito) as mediaprezzoabiti FROM calendario WHERE ' . $where . ' ' . $and . ' AND idtipoabito != "" AND idtipoabito != 0 AND acquistato = ? GROUP BY idtipoabito');
            $sql5->execute(array(1));
            $res5 = $sql5->fetchAll();
            foreach ($res5 as $row5) {
                $nometipoabito = $row5['nometipoabito'];
                $totaleabiti = $row5['totaleabiti'];
                $prezzoabiti = $row5['prezzoabiti'];
                $mediaprezzoabiti = $row5['mediaprezzoabiti'];

                $datiabiti['tipoabito'] = $nometipoabito;
                $datiabiti['numeroabiti'] = $totaleabiti;
                $datiabiti['prezzoabiti'] = number_format($prezzoabiti, 2, ".", "");
                $datiabiti['mediaprezzoabiti'] = number_format($mediaprezzoabiti, 2, ".", "");

                $arrdatiabiti[] = $datiabiti;
            }
            /* accessori */
            $qry = 'SELECT COUNT(*) totacc, SUM(prezzoaccessorio) as totprezzoacc, AVG(prezzoaccessorio) as totmediaacc, nomeaccessorio FROM `accessori_appuntamento` WHERE idappuntamento = ANY(SELECT id FROM calendario WHERE ' . $whereaccsart . ' ' . $andaccsart . ' AND calendario.id = accessori_appuntamento.idappuntamento AND calendario.acquistato = ?) GROUP BY idaccessorio ORDER BY totacc DESC';
            //die($qry);
            $sql6 = $db->prepare($qry);
            $sql6->execute(array(1));
            $res6 = $sql6->fetchAll();
            foreach ($res6 as $row6) {
                $totaleaccessori = $row6['totacc'];
                $totaleprezzoaccessori = $row6['totprezzoacc'];
                $mediaprezzoaccessori = $row6['totmediaacc'];
                $nomeaccessorio = $row6['nomeaccessorio'];

                $datiacc['atelier'] = $nomeatelier;
                $datiacc['nomeaccessorio'] = $nomeaccessorio;
                $datiacc['totaleacc'] = $totaleaccessori;
                $datiacc['totprezzoacc'] = number_format($totaleprezzoaccessori, 2, ".", "");
                $datiacc['totmediaacc'] = number_format($mediaprezzoaccessori, 2, ".", "");

                $arrdatiaccessori[] = $datiacc;
            }
            /* sartoria */

            $sql7 = $db->prepare('SELECT COUNT(*) totsart, SUM(prezzosartoria) as totprezzosart, SUM(costosartoria) as totcostisart, nomesartoria FROM `sartoria_appuntamento` WHERE idappuntamento = ANY(SELECT id FROM calendario WHERE ' . $whereaccsart . ' ' . $andaccsart . ' AND calendario.id = sartoria_appuntamento.idappuntamento AND calendario.acquistato = ? AND calendario.sartoria = ?) GROUP BY idsartoria ORDER BY totsart DESC');
            $sql7->execute(array(1, 1));
            $res7 = $sql7->fetchAll();
            foreach ($res7 as $row7) {
                $totalesartoria = $row7['totsart'];
                $totaleprezzosartoria = $row7['totprezzosart'];
                $totalecostisart = $row7['totcostisart'];
                $nomesartoria = $row7['nomesartoria'];

                $guadagno = $totaleprezzosartoria - $totalecostisart;

                $datisart['atelier'] = $nomeatelier;
                $datisart['nomesartoria'] = $nomesartoria;
                $datisart['totalesart'] = $totalesartoria;
                $datisart['totprezzosart'] = number_format($totaleprezzosartoria, 2, ".", "");
                $datisart['totcostisart'] = number_format($totalecostisart, 2, ".", "");
                $datisart['guadagno'] = number_format($guadagno, 2, ".", "");

                $arrdatisartoria[] = $datisart;
            }
            /* provenienza appuntamenti */
            $sql8 = $db->prepare('SELECT COUNT(*) totaleprov, provenienza, SUM(totalespesa) as totspesa FROM calendario WHERE ' . $where . ' ' . $and . '  AND provenienza != "" GROUP BY provenienza');
            $sql8->execute();
            $res8 = $sql8->fetchAll();
            foreach ($res8 as $row8) {
                $datiprov['atelier'] = $nomeatelier;
                $datiprov['provenienza'] = $row8['provenienza'];
                $datiprov['totaleprovenienza'] = $row8['totaleprov'];
                $datiprov['totspesa'] = $row8['totspesa'];

                $arrprov[] = $datiprov;
            }
            /* dettaglio motivi appuntamenti non acquistato */
            $sql9 = $db->prepare('SELECT COUNT(*) totalenoacq, nomenoacquisto FROM calendario WHERE ' . $where . ' ' . $and . ' AND acquistato = ? AND acquistato != "" GROUP BY idnoacquisto');
            $sql9->execute(array(0));
            $res9 = $sql9->fetchAll();
            foreach ($res9 as $row9) {
                $datimotnoacq['atelier'] = $nomeatelier;
                $datimotnoacq['nomenoacquisto'] = $row9['nomenoacquisto'];
                $datimotnoacq['totalenoacq'] = $row9['totalenoacq'];

                $arrdatimotnoacq[] = $datimotnoacq;
            }
            /* modello abito venduto */
            $sql10 = $db->prepare('SELECT idmodabito, COUNT(*) totaleabiti, nometipoabito, nomemodabito, SUM(prezzoabito) as prezzoab, AVG(prezzoabito) as mediaprezzoab FROM calendario WHERE ' . $where . ' ' . $and . ' AND idmodabito != "" AND idmodabito != 0 AND acquistato = ? GROUP BY idmodabito');
            $sql10->execute(array(1));
            $res10 = $sql10->fetchAll();

            foreach ($res10 as $row10) {
                $datimodabito['atelier'] = $nomeatelier;
                $datimodabito['totmodabiti'] = $row10['totaleabiti'];
                $datimodabito['nomemodabito'] = $row10['nometipoabito'] . " " . $row10['nomemodabito'];
                $datimodabito['prezzomodabito'] = number_format($row10['prezzoab'], 2, ".", "");
                $datimodabito['mediamodabito'] = number_format($row10['mediaprezzoab'], 2, ".", "");

                $arrdmodabito[] = $datimodabito;
            }

            /* statistiche su dipendenti */
            /* Appuntamenti totale dipendenti */
            $sql11 = $db->prepare('SELECT SUM(totalespesa) as totaledipspesa, AVG(CASE WHEN acquistato = 1 then totalespesa else null end) as mediadipspesa, COUNT(*) as totaleappdip, COUNT(CASE WHEN acquistato != "" THEN 1 ELSE NULL END) as totaleappchiusidip, COUNT(CASE WHEN acquistato = 1 THEN 1 ELSE NULL END) as totaleappacqdip, COUNT(CASE WHEN acquistato = 0 AND acquistato != "" THEN 1 ELSE NULL END) as totaleappnoacqdip, (SELECT u.nome FROM utenti u WHERE u.id = c.idutente) as nomedip, (SELECT u.cognome FROM utenti u WHERE u.id = c.idutente) as cognomedip FROM calendario c WHERE ' . $where . ' ' . $and . ' GROUP BY idutente');

            $sql11->execute();
            $res11 = $sql11->fetchAll();
            foreach ($res11 as $row11) {
                $totaleappdip = $row11['totaleappdip'];
                if (!$totaleappuntamentidip) {
                    $totaleappuntamentidip = 0;
                }
                $totaleappacqdip = $row11['totaleappacqdip'];
                if (!$totaleappacqdip) {
                    $totaleappacqdip = 0;
                }
                $totaleappnoacqdip = $row11['totaleappnoacqdip'];
                if (!$totaleappnoacqdip) {
                    $totaleappnoacqdip = 0;
                }
                $totaleappchiusidip = $row11['totaleappchiusidip'];
                if (!$totaleappchiusidip) {
                    $totaleappchiusidip = 0;
                }
                $totaledipspesa = $row11['totaledipspesa'];
                if (!$totaledipspesa) {
                    $totaledipspesa = 0;
                }
                $mediadipspesa = $row11['mediadipspesa'];
                if (!$mediadipspesa) {
                    $mediadipspesa = 0;
                }

                $datidip['atelier'] = $nomeatelier;
                $datidip['dipendente'] = $row11['nomedip'] . " " . $row11['cognomedip'];
                $datidip['totappdip'] = $totaleappdip;
                $datidip['totappchiusidip'] = $totaleappchiusidip;
                $datidip['totappacqdip'] = $totaleappacqdip;
                $datidip['totappnoacqdip'] = $totaleappnoacqdip;
                $datidip['totspesadip'] = number_format($totaledipspesa, 2, ".", "");
                $datidip['mediaspesadip'] = number_format($mediadipspesa, 2, ".", "");

                $arrdatidip[] = $datidip;
            }

            /* tipo bollino venduto */
            $datibollini['atelier'] = $nomeatelier;
            $sql12 = $db->prepare('SELECT idtipoabito, COUNT(*) totaleabiti, nometipoabito, bollino FROM calendario WHERE ' . $where . ' ' . $and . ' AND idtipoabito != "" AND idtipoabito != 0 AND acquistato = ? GROUP BY idtipoabito, idbollino');
//            die('SELECT idtipoabito, COUNT(*) totaleabiti, nometipoabito, bollino FROM calendario WHERE ' . $where . ' ' . $and . ' AND idtipoabito != "" AND idtipoabito != 0 AND acquistato = ? GROUP BY idtipoabito, idbollino');
            $sql12->execute(array(1));
            $res12 = $sql12->fetchAll();
            foreach ($res12 as $row12) {
                $nometipoabitob = $row12['nometipoabito'];
                $totaleabitib = $row12['totaleabiti'];
                $bollino = $row12['bollino'];
                if ($bollino == "") {
                    $bollino = "non assegnato";
                }

                $datibollini['tipoabitib'] = $nometipoabitob;
                $datibollini['numeroabitib'] = $totaleabitib;
                $datibollini['bollinib'] = $bollino;

                $arrdatibollini[] = $datibollini;
            }
        }

        die('{"msg" : "ok", "datibollini" : ' . json_encode($arrdatibollini) . ', "datidip" : ' . json_encode($arrdatidip) . ', "datimodabito" : ' . json_encode($arrdmodabito) . ', "datimotnoacq" : ' . json_encode($arrdatimotnoacq) . ', "datiprov" : ' . json_encode($arrprov) . ', "dati" : ' . json_encode($arrappuntamenti) . ', "datiabiti" : ' . json_encode($arrdatiabiti) . ', "datiaccessori" : ' . json_encode($arrdatiaccessori) . ', "datisartoria" : ' . json_encode($arrdatisartoria) . '}');

        break;

    case "submitstatisticheappuntamenti":
        global $arrmesi;
        $now = date('Y-m-d');
        $anno = $_POST['anno'];
        if ($anno == '') {
            $anno = $_GET['anno'];
        }
        $idatelier = $_POST['idatelier'];
        if ($idatelier == '') {
            $idatelier = $_GET['idatelier'];
        }
        $idatelier2 = $_POST['idatelier2'];
        $anno2 = $_POST['anno2'];

        if (!$idatelier && !$idatelier2) {
            $idatelier = $_SESSION['idatelier'];
        } else if ($idatelier2) {
            $idatelier = $idatelier2;
        }

        if ($anno2) {
            $anno = $anno2;
        }
        /* diretti / affiliati per media */
        $direttoaffiliato = getDati("utenti", "WHERE id = $idatelier")[0]['diraff'];

        $sql = $db->prepare("SELECT id FROM utenti WHERE id != ? AND livello = ? AND diraff = ?");
        $sql->execute(array($idatelier, 5, $direttoaffiliato));

        $res = $sql->fetchAll();
        foreach ($res as $row) {
            /* totale appuntamenti altri */
            $idaltroatelier = $row['id'];
            $sqlm = $db->prepare('SELECT COUNT(*) totappaltri FROM calendario WHERE YEAR(data) = ? AND idatelier = ? and tipoappuntamento!="5" and data <= "' . $now . '" and disdetto=0 and idnoacquisto!=1 GROUP BY idatelier');
            $sqlm->execute(array($anno, $idaltroatelier));
            $resm = $sqlm->fetchAll();
            foreach ($resm as $rowm) {
                $arrtotaltri[] = $rowm['totappaltri'];
            }
            /* totale appuntamenti chiusi altri */
            $sqlm = $db->prepare('SELECT COUNT(*) totappaltrichiusi FROM calendario WHERE YEAR(data) = ? AND idatelier = ? and data <= "' . $now . '" GROUP BY idatelier');
            $sqlm->execute(array($anno, $idaltroatelier));
            $resm = $sqlm->fetchAll();
            foreach ($resm as $rowm) {
                $arrtotchiusialtri[] = $rowm['totappaltrichiusi'];
            }
            /* totale appuntamenti acquistato altri */
            $sqlm = $db->prepare('SELECT COUNT(*) totappaltriacq FROM calendario WHERE YEAR(data) = ? AND idatelier = ? AND acquistato = ? and data <= "' . $now . '" GROUP BY idatelier');
            $sqlm->execute(array($anno, $idaltroatelier, 1));
            $resm = $sqlm->fetchAll();
            foreach ($resm as $rowm) {
                $arrtotacqaltri[] = $rowm['totappaltriacq'];
            }
            /* totale appuntamenti non acquistato altri */
            $sqlm = $db->prepare('SELECT COUNT(*) totappaltriacqno FROM calendario WHERE YEAR(data) = ? AND idatelier = ? AND acquistato = ? AND disdetto = 0 and data <= "' . $now . '" GROUP BY idatelier');
            $sqlm->execute(array($anno, $idaltroatelier, 0));
            $resm = $sqlm->fetchAll();
            foreach ($resm as $rowm) {
                $arrtotacqaltrino[] = $rowm['totappaltriacqno'];
            }
        }
        if ($arrtotaltri) {
            $mediatotaltri = array_sum($arrtotaltri) / count($arrtotaltri);
        } else {
            $mediatotaltri = 0;
        }
        if ($arrtotchiusialtri) {
            $mediachiusialtri = array_sum($arrtotchiusialtri) / count($arrtotchiusialtri);
        } else {
            $mediachiusialtri = 0;
        }
        if ($arrtotacqaltri) {
            $mediaacqaltri = array_sum($arrtotacqaltri) / count($arrtotacqaltri);
        } else {
            $mediaacqaltri = 0;
        }
        if ($arrtotacqaltrino) {
            $mediaacqaltrino = array_sum($arrtotacqaltrino) / count($arrtotacqaltrino);
        } else {
            $mediaacqaltrino = 0;
        }

        /*  */

        $where = "AND idatelier = $idatelier and tipoappuntamento!='5'";
        $datiatelier = getDati("utenti", "WHERE id = $idatelier");
        $nomeatelier = $datiatelier[0]['nominativo'];

        /* Appuntamenti totale anno */
        $sql = $db->prepare('SELECT COUNT(*) totappuntamenti FROM calendario WHERE YEAR(data) = ? ' . $where . '');
        $sql->execute(array($anno));
        $res = $sql->fetch();
        $totaleappuntamenti = $res['totappuntamenti'];

        /* Appuntamenti totale chiusi anno */
        //echo 'SELECT COUNT(*) totappuntamentichiusi FROM calendario WHERE YEAR(data) = ? and disdetto=0 and idnoacquisto!=1 ' . $where . '';
        $sql = $db->prepare('SELECT COUNT(*) totappuntamentichiusi FROM calendario WHERE YEAR(data) = ? and data <= "' . $now . '" ' . $where . '');
        $sql->execute(array($anno));
        $res = $sql->fetch(PDO::FETCH_ASSOC);
        $totappuntamentichiusi = $res['totappuntamentichiusi'];

        /* dettaglio appuntamenti  a buon fine */
        $sql = $db->prepare('SELECT COUNT(*) totacquistato FROM calendario WHERE YEAR(data) = ? AND acquistato = ? and data <= "' . $now . '" ' . $where . '');
        $sql->execute(array($anno, 1));
        $res = $sql->fetch();
        $totacquistato = $res['totacquistato'];

        /* dettaglio appuntamenti a non acquistato */
        $sql = $db->prepare('SELECT COUNT(*) totnoacquistato FROM calendario WHERE YEAR(data) = ? AND acquistato = ? and disdetto=0 and data <= "' . $now . '" ' . $where . '');
        $sql->execute(array($anno, 0));
        $res = $sql->fetch();
        $totnoacquistato = $res['totnoacquistato'];

        /* riepilogo no grafico */

        if ($_SESSION["livello"] == 0 || $_SESSION["livello"] == 1 || $direttoaffiliato == "d") {
            $statistiche .= "<span style=\"color: #2E65A1;\">Tra parentesi <i>(num)</i> la media degli altri Atelier</span><br /><br />";
            $btn_mediatot = "<span style=\"font-weight: normal; font-style: italic\">(" . number_format($mediatotaltri, 1, ",", ".") . ")</span>";
            $btn_mediaacq = "<span style=\"font-weight: normal; font-style: italic\">(" . number_format($mediachiusialtri, 1, ",", ".") . ")</span>";

            $btn_mediaacq = "<span style=\"font-weight: normal; font-style: italic\">(" . number_format($mediaacqaltri, 1, ",", ".") . ")</span>";
            $btn_medianoacq = "<span style=\"font-weight: normal; font-style: italic\">(" . number_format($mediaacqaltrino, 1, ",", ".") . ")</span>";
        }


        $statistiche .= "<div class=\"statprofit\">Totale appuntamenti presi anno " . $anno . " $nomeatelier:</div><div class=\"valoreprofit\"> $totaleappuntamenti $btn_mediatot</div><div class=\"chiudi\"></div>"
                . "<div class=\"statprofit\">Totale appuntamenti SVOLTI presi anno " . $anno . " $nomeatelier:</div><div class=\"valoreprofit\"> $totappuntamentichiusi $btn_mediaacq</div><div class=\"chiudi\"></div>"
                . "<div class=\"chiudi\" style=\"height: 20px;\"></div>"
                . "<strong>DETTAGLIO DEGLI APPUNTAMENTI</strong><br /><br />";

        $statistiche .= "<div class=\"statprofit\">Appuntamenti in cui hanno acquistato:</div><div class=\"valoreprofit\"> $totacquistato $btn_mediaacq</div><div class=\"chiudi\"></div>"
                . "<div class=\"statprofit\">Appuntamenti in cui NON hanno acquistato:</div><div class=\"valoreprofit\"> $totnoacquistato $btn_medianoacq</div><div class=\"chiudi\"></div>";

//        $datiprofit = totaleprofit($anno, $where);
//
//        foreach ($datiprofit as $k => $v) {
//            $statistiche .= "<div class=\"statprofit\">" . $k . "</div><div class=\"valoreprofit\">" . $fatt_note_tot = number_format($v, 2, ",", ".") . " &euro;</div><div class=\"chiudi\"></div>";
//        }

        $statistiche .= "<br /><br />";

        $arrsino["Hanno acquistato"] = round($totacquistato, 2, PHP_ROUND_HALF_UP);
        $arrsino["NON hanno acquistato"] = round($totnoacquistato, 2, PHP_ROUND_HALF_UP);

        /* dettaglio motivi appuntamenti non acquistato */
        $sql = $db->prepare('SELECT COUNT(*) totaleapp, nomenoacquisto FROM calendario WHERE YEAR(data) = ? AND acquistato = ? AND acquistato != "" and idnoacquisto!=1 and disdetto=0 ' . $where . ' GROUP BY idnoacquisto');
        $sql->execute(array($anno, 0));
        $resnoacq = $sql->fetchAll();

        foreach ($resnoacq as $resnoacqd) {
            $totaleappno = $resnoacqd['totaleapp'];
            $nomenoacquisto = $resnoacqd['nomenoacquisto'];
            $printmotivonoacquisto .= "<div class=\"statprofit\">$nomenoacquisto</div><div class=\"valoreprofit\"> $totaleappno </div><div class=\"chiudi\"></div>";
            $arrmotivo[$nomenoacquisto] = round($totaleappno, 2, PHP_ROUND_HALF_UP);
        }

        $statistiche2 = "<br /><br /><hr style=\"width: 80%; float: left;\"><div class=\"chiudi\"></div><br /><br />Appuntamenti in cui non hanno acquistato: <b>MOTIVO</b><br /><br />$printmotivonoacquisto<br /><br />";

        for ($i = 1; $i <= 12; $i++) {
            /* Appuntamenti totale per mese */
            $sql = $db->prepare('SELECT COUNT(*) totappuntamenti FROM calendario WHERE YEAR(data) = ? AND MONTH(data) = ? ' . $where . '');
            $sql->execute(array($anno, $i));
            $res = $sql->fetch();
            $totaleappuntamentimese = (int) $res['totappuntamenti'];
            $arrmese[$arrmesi[$i]]["Appuntamenti presi"] = round($totaleappuntamentimese, 2, PHP_ROUND_HALF_UP);
            $arrmesecsv[$arrmesi[$i]]["Appuntamenti presi"] = round($totaleappuntamentimese, 2, PHP_ROUND_HALF_UP);
            /* Appuntamenti totale chiusi per mese */
            $sql = $db->prepare('SELECT COUNT(*) totappuntamentichiusi FROM calendario WHERE YEAR(data) = ? AND MONTH(data) = ? and data <= "' . $now . '" ' . $where . '');
            $sql->execute(array($anno, $i));
            $res = $sql->fetch();
            $totappuntamentichiusimese = (int) $res['totappuntamentichiusi'];
            if ($totaleappuntamentimese > 0) {
                $perc_chiusi = round($totappuntamentichiusimese * 100 / $totaleappuntamentimese, 0);
            } else {
                $perc_chiusi = 0;
            }
            $arrmese[$arrmesi[$i]]["Appuntamenti chiusi($perc_chiusi%)"] = round($totappuntamentichiusimese, 2, PHP_ROUND_HALF_UP);
            $arrmesecsv[$arrmesi[$i]]["Appuntamenti chiusi"] = round($totappuntamentichiusimese, 2, PHP_ROUND_HALF_UP);
            $arrmesecsv[$arrmesi[$i]]["Appuntamenti chiusi %"] = "$perc_chiusi%";
            /* dettaglio appuntamenti  a buon fine per mese */
            $sql = $db->prepare('SELECT COUNT(*) totacquistato FROM calendario WHERE YEAR(data) = ? AND MONTH(data) = ? AND acquistato = ? and data <= "' . $now . '" ' . $where . '');
            $sql->execute(array($anno, $i, 1));
            $res = $sql->fetch();
            $totacquistatomese = (int) $res['totacquistato'];
            if ($totappuntamentichiusimese > 0) {
                $perc_acq = round($totacquistatomese * 100 / $totappuntamentichiusimese, 0);
            } else {
                $perc_acq = 0;
            }
            if ($totaleappuntamentimese > 0) {
                $perc_acq2 = round($totacquistatomese * 100 / $totaleappuntamentimese, 0);
            } else {
                $perc_acq2 = 0;
            }
            $arrmese[$arrmesi[$i]]["Acquistato($perc_acq% / $perc_acq2%)"] = round($totacquistatomese, 2, PHP_ROUND_HALF_UP);
            $arrmesecsv[$arrmesi[$i]]["Acquistato"] = round($totacquistatomese, 2, PHP_ROUND_HALF_UP);
            $arrmesecsv[$arrmesi[$i]]["Acquistato % su chiusi"] = "$perc_acq%";
            $arrmesecsv[$arrmesi[$i]]["Acquistato % su presi"] = "$perc_acq2%";
            /* dettaglio appuntamenti  non acquistato mese */
            $sql = $db->prepare('SELECT COUNT(*) totnoacquistato FROM calendario WHERE YEAR(data) = ? AND MONTH(data) = ? AND acquistato = ? AND disdetto = 0 and data <= "' . $now . '" ' . $where . '');
            $sql->execute(array($anno, $i, 0));
            $res = $sql->fetch();
            $totnoacquistatomese = (int) $res['totnoacquistato'];
            if ($totappuntamentichiusimese > 0) {
                $perc_noacq = round($totnoacquistatomese * 100 / $totappuntamentichiusimese, 0);
            } else {
                $perc_noacq = 0;
            }
            if ($totaleappuntamentimese > 0) {
                $perc_noacq2 = round($totnoacquistatomese * 100 / $totaleappuntamentimese, 0);
            } else {
                $perc_noacq2 = 0;
            }
            $arrmese[$arrmesi[$i]]["NON acquistato($perc_noacq% / $perc_noacq2%)"] = round($totnoacquistatomese, 2, PHP_ROUND_HALF_UP);
            $arrmesecsv[$arrmesi[$i]]["NON acquistato"] = round($totnoacquistatomese, 2, PHP_ROUND_HALF_UP);
            $arrmesecsv[$arrmesi[$i]]["NON acquistato % su chiusi"] = "$perc_noacq%";
            $arrmesecsv[$arrmesi[$i]]["NON acquistato % su presi"] = "$perc_noacq2%";
        }

        /**/
        for ($m = 1; $m <= 12; $m++) {
            $mesi[] = $arrmesiesteso[$m];
            $datimese[$m] = $arrmese[$arrmesi[$m]];
            $datimesecsv[$m] = $arrmesecsv[$arrmesi[$m]];
        }
        if (!$datimese) {
            $datimese = "";
        }
        /**/
        if ($_GET['csv'] == 1) {
            $atelier = getDati("utenti", "WHERE id = $idatelier")[0];
            $csv = '"' . $atelier['nominativo'] . '";"' . $anno . '";"";"";""' . "\n"
                    . '"Mese";"Appuntamenti presi";"Appuntamenti chiusi";"% Appuntamenti chiusi su presi";"Acquistato";"% Acquistato su chiusi";"% Acquistato su presi";"NON Acquistato";"% NON Acquistato su chiusi";"% NON Acquistato su presi"' . "\n";
            foreach ($datimesecsv as $mese => $value) {
                //var_dump($value);
                //die();
                $csv .= '"' . $mesi[$mese - 1] . '";"' . $value["Appuntamenti presi"] . '";"' . $value["Appuntamenti chiusi"] . '";"' . $value["Appuntamenti chiusi %"] . '";'
                        . '"' . $value["Acquistato"] . '";"' . $value["Acquistato % su chiusi"] . '";"' . $value["Acquistato % su presi"] . '";"' . $value["NON acquistato"] . '";"' . $value["NON acquistato % su chiusi"] . '";"' . $value["NON acquistato % su presi"] . '"' . "\n";
            }
            header("Content-Type: text/csv");
            header("Content-Disposition: inline; filename=export.csv");
            die($csv);
        } else {
            die('{"anno" : ' . json_encode($anno) . ', "statistiche": ' . json_encode($statistiche) . ', "mesi": ' . json_encode($mesi) . ', "datimese": ' . json_encode($datimese) . ', "datigrafico": ' . json_encode($arrsino) . ', "datigraficono": ' . json_encode($arrmotivo) . ', "statistiche2": ' . json_encode($statistiche2) . '}');
        }
        break;

    case 'statisticheconv_periodo':
        $now = date('Y-m-d');
        $dal = $_POST['data'];
        $al = $_POST['data2'];
        $list_atelier = getAtelier('');
        $ateliers = Array();
        foreach ($list_atelier as $atelier) {
            /* Appuntamenti totale per mese */
            $sql = $db->prepare("SELECT COUNT(*) totappuntamenti FROM calendario WHERE data between ? and ? and data < '$now' and idatelier=? and tipoappuntamento!=5;");
            $sql->execute(array($dal, $al, $atelier['id']));
            $res = $sql->fetch(PDO::FETCH_ASSOC);
            $totaleappuntamentimese = (int) $res['totappuntamenti'];
            $atelier["Appuntamenti presi"] = round($totaleappuntamentimese, 2, PHP_ROUND_HALF_UP);
            /* Appuntamenti totale chiusi per mese */
            $sql = $db->prepare('SELECT COUNT(*) totappuntamentichiusi FROM calendario WHERE data between ? and ? and idatelier=?;');
            $sql->execute(array($dal, $al, $atelier['id']));
            $res = $sql->fetch(PDO::FETCH_ASSOC);
            $totappuntamentichiusimese = (int) $res['totappuntamentichiusi'];
            if ($totaleappuntamentimese > 0) {
                $perc_chiusi = round($totappuntamentichiusimese * 100 / $totaleappuntamentimese, 0);
            } else {
                $perc_chiusi = 0;
            }
            $atelier["Appuntamenti chiusi"] = round($totappuntamentichiusimese, 2, PHP_ROUND_HALF_UP);
            $atelier["Appuntamenti chiusi %"] = "$perc_chiusi%";
            $sql = $db->prepare('SELECT COUNT(*) totappuntamentichiusi FROM calendario WHERE data between ? and ? and idatelier=? and tipoappuntamento=?;'); //sposa
            $sql->execute(array($dal, $al, $atelier['id'], '1'));
            $res = $sql->fetch(PDO::FETCH_ASSOC);
            $totappuntamentichiusitipo = (int) $res['totappuntamentichiusi'];
            $atelier["Appuntamenti chiusi Sposa"] = "$totappuntamentichiusitipo";
            $sql = $db->prepare('SELECT COUNT(*) totappuntamentichiusi FROM calendario WHERE data between ? and ? and idatelier=? and tipoappuntamento=?;'); //sposa
            $sql->execute(array($dal, $al, $atelier['id'], 2));
            $res = $sql->fetch(PDO::FETCH_ASSOC);
            $totappuntamentichiusitipo = (int) $res['totappuntamentichiusi'];
            $atelier["Appuntamenti chiusi Sposo"] = "$totappuntamentichiusitipo";
            $sql = $db->prepare('SELECT COUNT(*) totappuntamentichiusi FROM calendario WHERE data between ? and ? and idatelier=? and tipoappuntamento=?;'); //sposa
            $sql->execute(array($dal, $al, $atelier['id'], 6));
            $res = $sql->fetch(PDO::FETCH_ASSOC);
            $totappuntamentichiusitipo = (int) $res['totappuntamentichiusi'];
            $atelier["Appuntamenti chiusi Sposa e Sposo"] = "$totappuntamentichiusitipo";
            $sql = $db->prepare('SELECT COUNT(*) totappuntamentichiusi FROM calendario WHERE data between ? and ? and idatelier=? and tipoappuntamento=?;'); //sposa
            $sql->execute(array($dal, $al, $atelier['id'], 3));
            $res = $sql->fetch(PDO::FETCH_ASSOC);
            $totappuntamentichiusitipo = (int) $res['totappuntamentichiusi'];
            $atelier["Appuntamenti chiusi Cerimonia donna"] = "$totappuntamentichiusitipo";
            $sql = $db->prepare('SELECT COUNT(*) totappuntamentichiusi FROM calendario WHERE data between ? and ? and idatelier=? and tipoappuntamento=?;'); //sposa
            $sql->execute(array($dal, $al, $atelier['id'], 4));
            $res = $sql->fetch(PDO::FETCH_ASSOC);
            $totappuntamentichiusitipo = (int) $res['totappuntamentichiusi'];
            $atelier["Appuntamenti chiusi Cerimonia uomo"] = "$totappuntamentichiusitipo";
            $sql = $db->prepare('SELECT COUNT(*) totappuntamentichiusi FROM calendario WHERE data between ? and ? and idatelier=? and tipoappuntamento=?;'); //sposa
            $sql->execute(array($dal, $al, $atelier['id'], 5));
            $res = $sql->fetch(PDO::FETCH_ASSOC);
            $totappuntamentichiusitipo = (int) $res['totappuntamentichiusi'];
            $atelier["Appuntamenti chiusi sartoria"] = "$totappuntamentichiusitipo";
            /* dettaglio appuntamenti  a buon fine per mese */
            $sql = $db->prepare('SELECT COUNT(*) totacquistato FROM calendario WHERE data between ? and ? and idatelier=? AND acquistato = ?;');
            $sql->execute(array($dal, $al, $atelier['id'], 1));
            $res = $sql->fetch(PDO::FETCH_ASSOC);
            $totacquistatomese = (int) $res['totacquistato'];
            if ($totappuntamentichiusimese > 0) {
                $perc_acq = round($totacquistatomese * 100 / $totappuntamentichiusimese, 0);
            } else {
                $perc_acq = 0;
            }
            if ($totaleappuntamentimese > 0) {
                $perc_acq2 = round($totacquistatomese * 100 / $totaleappuntamentimese, 0);
            } else {
                $perc_acq2 = 0;
            }
            $sql = $db->prepare('SELECT COUNT(*) totacquistato FROM calendario WHERE data between ? and ? and idatelier=? AND acquistato = "" and disdetto = 0;');
            $sql->execute(array($dal, $al, $atelier['id']));
            $res = $sql->fetch(PDO::FETCH_ASSOC);
            $tot_non_compilato_mese = (int) $res['totacquistato'];
            $atelier["Acquistato"] = round($totacquistatomese, 2, PHP_ROUND_HALF_UP);
            $atelier["Acquistato % su chiusi"] = "$perc_acq%";
            $atelier["Acquistato % su presi"] = "$perc_acq2%";
            $atelier["Non Compilato"] = round($tot_non_compilato_mese, 2, PHP_ROUND_HALF_UP);
            /* dettaglio appuntamenti  non acquistato mese */
            $sql = $db->prepare('SELECT COUNT(*) totnoacquistato FROM calendario WHERE data between ? and ? and idatelier=? AND acquistato = ? AND disdetto = 0;');
            $sql->execute(array($dal, $al, $atelier['id'], 0));
            $res = $sql->fetch(PDO::FETCH_ASSOC);
            $totnoacquistatomese = (int) $res['totnoacquistato'];
            if ($totappuntamentichiusimese > 0) {
                $perc_noacq = round($totnoacquistatomese * 100 / $totappuntamentichiusimese, 0);
            } else {
                $perc_noacq = 0;
            }
            if ($totaleappuntamentimese > 0) {
                $perc_noacq2 = round($totnoacquistatomese * 100 / $totaleappuntamentimese, 0);
            } else {
                $perc_noacq2 = 0;
            }
            $atelier["NON acquistato"] = round($totnoacquistatomese, 2, PHP_ROUND_HALF_UP);
            $atelier["NON acquistato % su chiusi"] = "$perc_noacq%";
            $atelier["NON acquistato % su presi"] = "$perc_noacq2%";
            $ateliers[] = $atelier;
        }
        $csv = '"Statistiche appuntamenti dal";"' . formatDate($dal) . '";"al";"' . formatDate($al) . '";""' . "\n"
                . '"Atelier";"Diretto/Affiliato";"Appuntamenti presi";"Non Compilato";"Acquistato";"% Acquistato su presi";"";"Appuntamenti chiusi";"% Appuntamenti chiusi su presi";'
                . '"Appuntamenti chiusi Sposa";"Appuntamenti chiusi Sposo";"Appuntamenti chiusi Sposa e Sposo";'
                . '"Appuntamenti chiusi Cerimonia donna";"Appuntamenti chiusi Cerimonia uomo";"Appuntamenti chiusi sartoria";"% Acquistato su chiusi";"NON Acquistato";"% NON Acquistato su chiusi";"% NON Acquistato su presi"' . "\n";
        foreach ($ateliers as $atelier) {
            $csv .= '"' . $atelier['nominativo'] . '";"' . ($atelier['diraff'] == 'd' ? 'Diretto' : 'Affiliato') . '";'
                    . '"' . $atelier["Appuntamenti presi"] . '";"' . $atelier["Non Compilato"] . '";"' . $atelier["Acquistato"] . '";"' . $atelier["Acquistato % su presi"] . '";"";"' . $atelier["Appuntamenti chiusi"] . '";"' . $atelier["Appuntamenti chiusi %"] . '";'
                    . '"' . $atelier['Appuntamenti chiusi Sposa'] . '";"' . $atelier['Appuntamenti chiusi Sposo'] . '";"' . $atelier['Appuntamenti chiusi Sposa e Sposo'] . '";'
                    . '"' . $atelier['Appuntamenti chiusi Cerimonia uomo'] . '";"' . $atelier['Appuntamenti chiusi Cerimonia donna'] . '";"' . $atelier['Appuntamenti chiusi sartoria'] . '";'
                    . '"' . $atelier["Acquistato % su chiusi"] . '";"' . $atelier["NON acquistato"] . '";"'
                    . $atelier["NON acquistato % su chiusi"] . '";"' . $atelier["NON acquistato % su presi"] . '"' . "\n";
        }
        header("Content-Type: text/csv");
        header("Content-Disposition: inline; filename=export.csv");
        die($csv);
        break;

    /* PROVENIENZA PROVENIENZA */
    case "submitstatisticheprovenienza":
        global $arrmesi;

        $anno = $_POST['anno'];
        $idatelier = $_POST['idatelier'];

        $idatelier2 = $_POST['idatelier2'];
        $anno2 = $_POST['anno2'];

        if (!$idatelier && !$idatelier2) {
            $idatelier = $_SESSION['idatelier'];
        } else if ($idatelier2) {
            $idatelier = $idatelier2;
        }

        if ($anno2) {
            $anno = $anno2;
        }


        $where = "AND idatelier = $idatelier and tipoappuntamento!='5'";
        $datiatelier = getDati("utenti", "WHERE id = $idatelier");
        $nomeatelier = $datiatelier[0]['nominativo'];

        /* provenienza appuntamenti */
        $sql = $db->prepare('SELECT COUNT(*) totaleprov, provenienza FROM calendario WHERE YEAR(data) = ? AND provenienza != "" ' . $where . ' GROUP BY provenienza');
        $sql->execute(array($anno));
        $resnoacq = $sql->fetchAll();

        foreach ($resnoacq as $resnoacqd) {
            $totaleprovenienza = $resnoacqd['totaleprov'];
            $provenienza = $resnoacqd['provenienza'];
            $printprovenienza .= "<div class=\"statprofit\">$provenienza</div><div class=\"valoreprofit\"> $totaleprovenienza </div><div class=\"chiudi\"></div>";
            $arrprovenienza[$provenienza] = round($totaleprovenienza, 2, PHP_ROUND_HALF_UP);
        }

        $statistiche2 = "<b>Provenienza appuntamenti $nomeatelier</b><br /><br />$printprovenienza<br /><br />";

        for ($i = 1; $i <= 12; $i++) {
            /* Appuntamenti totale per mese */
            $sql = $db->prepare('SELECT COUNT(*) totappuntamenti, provenienza FROM calendario WHERE YEAR(data) = ? AND MONTH(data) = ? AND provenienza != "" ' . $where . ' GROUP BY provenienza');
            $sql->execute(array($anno, $i));
            $res = $sql->fetchAll();
            foreach ($res as $row) {
                $totaleappuntamentimese = $row['totappuntamenti'];
                $provenienzadett = $row['provenienza'];
                $arrmese[$arrmesi[$i]][$provenienzadett] = round($totaleappuntamentimese, 2, PHP_ROUND_HALF_UP);
            }
        }

        /**/
        for ($m = 1; $m <= 12; $m++) {
            $mesi[] = $arrmesiesteso[$m];
            $datimese[$m] = $arrmese[$arrmesi[$m]];
        }
        if (!$datimese) {
            $datimese = "";
        }
        /**/
        die('{"anno" : ' . json_encode($anno) . ', "mesi": ' . json_encode($mesi) . ', "datimese": ' . json_encode($datimese) . ', "datiprovenienza": ' . json_encode($arrprovenienza) . ', "statistiche2": ' . json_encode($statistiche2) . '}');
        break;

    /* ABITI ACQUISTATI */
    case "submitstatisticheabiti":
        global $arrmesi;

        $anno = $_POST['anno'];
        $idatelier = $_POST['idatelier'];

        $idatelier2 = $_POST['idatelier2'];
        $anno2 = $_POST['anno2'];

        if (!$idatelier && !$idatelier2) {
            $idatelier = $_SESSION['idatelier'];
        } else if ($idatelier2) {
            $idatelier = $idatelier2;
        }

        if ($anno2) {
            $anno = $anno2;
        }

        /* diretti / affiliati per media */
        $direttoaffiliato = getDati("utenti", "WHERE id = $idatelier")[0]['diraff'];
        /**/

        $where = "AND idatelier = $idatelier";
        $datiatelier = getDati("utenti", "WHERE id = $idatelier");
        $nomeatelier = $datiatelier[0]['nominativo'];

        /* tipo abito venduto */
        $sql = $db->prepare('SELECT idtipoabito, COUNT(*) totaleabiti, nometipoabito, SUM(prezzoabito) as prezzoab, AVG(prezzoabito) as mediaprezzoab FROM calendario WHERE YEAR(data) = ? AND idtipoabito != "" AND idtipoabito != 0 AND acquistato = ? ' . $where . ' GROUP BY idtipoabito');
        $sql->execute(array($anno, 1));
        $res = $sql->fetchAll();

        foreach ($res as $row) {
            $idtipoabito = $row['idtipoabito'];
            /* mediaaltri */
            $sqlat = $db->prepare("SELECT id FROM utenti WHERE id != ? AND livello = ? AND diraff = ?");
            $sqlat->execute(array($idatelier, 5, $direttoaffiliato));

            $resat = $sqlat->fetchAll();
            $arrtotabitimedia = array();
            $sommaabitimedia = array();
            $mediaabitialtri = array();
            foreach ($resat as $rowat) {
                /* totale tipo altri */
                $idaltroatelier = $rowat['id'];
                $sqlmedia = $db->prepare('SELECT COUNT(*) totaleabitimedia, SUM(prezzoabito) as prezzoabmedia, AVG(prezzoabito) as mediaprezzoabmedia FROM calendario WHERE YEAR(data) = ? AND idtipoabito = ? AND acquistato = ? AND idatelier = ? GROUP BY idtipoabito');
                $sqlmedia->execute(array($anno, $idtipoabito, 1, $idaltroatelier));
                $resmedia = $sqlmedia->fetchAll();
                foreach ($resmedia as $rowmedia) {
                    $arrtotabitimedia[] = $rowmedia['totaleabitimedia'];
                    $sommaabitimedia[] = $rowmedia['prezzoabmedia'];
                    $mediaabitialtri[] = $rowmedia['mediaprezzoabmedia'];
                }
            }
            $medianumabitialtri = 0;
            if ($arrtotabitimedia) {
                $medianumabitialtri = array_sum($arrtotabitimedia) / count($arrtotabitimedia);
            }
            $mediaprezzoabitialtri = 0;
            if ($sommaabitimedia) {
                $mediaprezzoabitialtri = array_sum($sommaabitimedia) / count($sommaabitimedia);
            }
            $mediamediaabitialtri = 0;
            if ($mediaabitialtri) {
                $mediamediaabitialtri = array_sum($mediaabitialtri) / count($mediaabitialtri);
            }
            /**/
            $totaleab = $row['totaleabiti'];
            $tipoabito = $row['nometipoabito'];
            $totprezzoabito = $row['prezzoab'];
            $mediaabiti = $row['mediaprezzoab'];
            $printtipoabito .= "<div class=\"statprofit\">$tipoabito</div><div class=\"valoreprofit\"> $totaleab <span style=\"font-weight: normal; font-style: italic;\">(" . number_format($medianumabitialtri, 1, ",", ".") . ")</span></div><div class=\"valoreprofit_big\">" . number_format($totprezzoabito, 2, ',', '.') . " &euro; <span style=\"font-weight: normal; font-style: italic;\">(" . number_format($mediaprezzoabitialtri, 2, ",", ".") . " &euro;)</span></div><div class=\"valoreprofit_big\">" . number_format($mediaabiti, 2, ',', '.') . " &euro; <span style=\"font-weight: normal; font-style: italic;\">(" . number_format($mediamediaabitialtri, 2, ",", ".") . " &euro;)</span></div><div class=\"chiudi\"></div>";
        }
        if ($_SESSION["livello"] == 0 || $_SESSION["livello"] == 1 || $direttoaffiliato == "d") {
            $statistiche2 .= "<span style=\"color: #2E65A1;\">Tra parentesi <i>(num)</i> la media degli altri Atelier</span><br /><br /><div class=\"statprofit\" style=\"font-weight: bolder;\">ABITI VENDUTI $nomeatelier</div><div class=\"valoreprofit\"> N° </div><div class=\"valoreprofit_big\"> TOTALE</div><div class=\"valoreprofit_big\">MEDIA</div><div class=\"chiudi\"></div><br />$printtipoabito<div class=\"chiudi\"></div><br /><br />";
        }

        /* modello abito venduto */
        $sql = $db->prepare('SELECT idmodabito, COUNT(*) totaleabiti, nometipoabito, nomemodabito, SUM(prezzoabito) as prezzoab, AVG(prezzoabito) as mediaprezzoab FROM calendario WHERE YEAR(data) = ? AND idmodabito != "" AND idmodabito != 0 AND acquistato = ? ' . $where . ' GROUP BY idmodabito');
        $sql->execute(array($anno, 1));
        $resnoacq = $sql->fetchAll();

        foreach ($resnoacq as $resnoacqd) {
            $idmodabito = $resnoacqd['idmodabito'];
            /* mediaaltri */
            $sqlat = $db->prepare("SELECT id FROM utenti WHERE id != ? AND livello = ? AND diraff = ?");
            $sqlat->execute(array($idatelier, 5, $direttoaffiliato));

            $resat = $sqlat->fetchAll();
            $arrtotabitimedia = array();
            $sommaabitimedia = array();
            $mediaabitialtri = array();
            foreach ($resat as $rowat) {
                /* totale modelli altri */
                $idaltroatelier = $rowat['id'];
                $sqlmedia = $db->prepare('SELECT COUNT(*) totaleabitimedia, SUM(prezzoabito) as prezzoabmedia, AVG(prezzoabito) as mediaprezzoabmedia FROM calendario WHERE YEAR(data) = ? AND idmodabito = ? AND acquistato = ? AND idatelier = ? GROUP BY idmodabito');
                $sqlmedia->execute(array($anno, $idmodabito, 1, $idaltroatelier));
                $resmedia = $sqlmedia->fetchAll();
                foreach ($resmedia as $rowmedia) {
                    $arrtotabitimedia[] = $rowmedia['totaleabitimedia'];
                    $sommaabitimedia[] = $rowmedia['prezzoabmedia'];
                    $mediaabitialtri[] = $rowmedia['mediaprezzoabmedia'];
                }
            }
            $medianumabitialtri = 0;
            if ($arrtotabitimedia) {
                $medianumabitialtri = array_sum($arrtotabitimedia) / count($arrtotabitimedia);
            }
            $mediaprezzoabitialtri = 0;
            if ($sommaabitimedia) {
                $mediaprezzoabitialtri = array_sum($sommaabitimedia) / count($sommaabitimedia);
            }
            $mediamediaabitialtri = 0;
            if ($mediaabitialtri) {
                $mediamediaabitialtri = array_sum($mediaabitialtri) / count($mediaabitialtri);
            }
            /**/
            $totaleabiti = $resnoacqd['totaleabiti'];
            $nomeabito = $resnoacqd['nometipoabito'] . " " . $resnoacqd['nomemodabito'];
            $totprezzoabit = $resnoacqd['prezzoab'];
            $mediaabit = $resnoacqd['mediaprezzoab'];

            if ($_SESSION["livello"] == 0 || $_SESSION["livello"] == 1 || $direttoaffiliato == "d") {
                $medianumprint = "<span style=\"font-weight: normal; font-style: italic;\">(" . number_format($medianumabitialtri, 1, ",", ".") . ")</span>";
                $mediaabititotaltriprint = "<span style=\"font-weight: normal; font-style: italic;\">(" . number_format($mediaprezzoabitialtri, 2, ",", ".") . " &euro;)</span>";
                $mediamediaaltritot = "<span style=\"font-weight: normal; font-style: italic;\">(" . number_format($mediamediaabitialtri, 2, ",", ".") . " &euro;)</span>";
            }


            $printabiti .= "<div class=\"statprofit\">$nomeabito</div><div class=\"valoreprofit\"> $totaleabiti $medianumprint</div><div class=\"valoreprofit_big\">" . number_format($totprezzoabit, 2, ',', '.') . " &euro; $mediaabititotaltriprint</div><div class=\"valoreprofit_big\">" . number_format($mediaabit, 2, ',', '.') . " &euro; $mediamediaaltritot</div><div class=\"chiudi\"></div>";
            $arrabiti[$nomeabito] = round($totaleabiti, 2, PHP_ROUND_HALF_UP);
        }

        $statistiche2 .= "<div class=\"statprofit\" style=\"font-weight: bolder;\">ABITI VENDUTI DETTAGLIO $nomeatelier</div><div class=\"valoreprofit\"> N° </div><div class=\"valoreprofit_big\"> TOTALE</div><div class=\"valoreprofit_big\">MEDIA</div><div class=\"chiudi\"></div><br />$printabiti<div class=\"chiudi\"></div><br /><br />";

        for ($i = 1; $i <= 12; $i++) {
            /* Appuntamenti totale per mese */
            $sql = $db->prepare('SELECT COUNT(*) totaleabiti, nometipoabito, nomemodabito FROM calendario WHERE YEAR(data) = ? AND MONTH(data) = ? AND acquistato = ? AND idmodabito != "" AND idmodabito != 0 ' . $where . ' GROUP BY idmodabito');
            $sql->execute(array($anno, $i, 1));
            $res = $sql->fetchAll();
            foreach ($res as $row) {
                $totaleabiti = $row['totaleabiti'];
                $nomeabito = $row['nometipoabito'] . " " . $row['nomemodabito'];
                $arrmese[$arrmesi[$i]][$nomeabito] = round($totaleabiti, 2, PHP_ROUND_HALF_UP);
            }
        }

        /**/
        for ($m = 1; $m <= 12; $m++) {
            $mesi[] = $arrmesiesteso[$m];
            $datimese[$m] = $arrmese[$arrmesi[$m]];
        }
        if (!$datimese) {
            $datimese = "";
        }
        /**/
        die('{"anno": ' . json_encode($anno) . ', "mesi": ' . json_encode($mesi) . ', "datimese": ' . json_encode($datimese) . ', "datiprovenienza": ' . json_encode($arrabiti) . ', "statistiche2": ' . json_encode($statistiche2) . '}');
        break;

    /* ACCESSORI ACQUISTATI */
    case "submitstatisticheaccessori":
        global $arrmesi;

        $anno = $_POST['anno'];
        $idatelier = $_POST['idatelier'];

        $idatelier2 = $_POST['idatelier2'];
        $anno2 = $_POST['anno2'];

        if (!$idatelier && !$idatelier2) {
            $idatelier = $_SESSION['idatelier'];
        } else if ($idatelier2) {
            $idatelier = $idatelier2;
        }

        if ($anno2) {
            $anno = $anno2;
        }

        /* diretti / affiliati per media */
        $direttoaffiliato = getDati("utenti", "WHERE id = $idatelier")[0]['diraff'];
        /**/

        $where = "AND calendario.idatelier = $idatelier";
        $datiatelier = getDati("utenti", "WHERE id = $idatelier");
        $nomeatelier = $datiatelier[0]['nominativo'];

        /* tipo accessorio venduto */

        $sql = $db->prepare('SELECT idaccessorio, COUNT(*) totacc, SUM(prezzoaccessorio) as totprezzoacc, AVG(prezzoaccessorio) as totmediaacc, nomeaccessorio FROM `accessori_appuntamento` WHERE idappuntamento = ANY(SELECT id FROM calendario WHERE calendario.id = accessori_appuntamento.idappuntamento ' . $where . ' AND YEAR(calendario.data) = ? AND calendario.acquistato = ?) GROUP BY idaccessorio ORDER BY totacc DESC');
        $sql->execute(array($anno, 1));
        $res = $sql->fetchAll();

        foreach ($res as $row) {
            $idaccessorio = $row['idaccessorio'];
            /* media altri */
            $sqlat = $db->prepare("SELECT id FROM utenti WHERE id != ? AND livello = ? AND diraff = ?");
            $sqlat->execute(array($idatelier, 5, $direttoaffiliato));

            $resat = $sqlat->fetchAll();
            $arrtotaccmedia = array();
            $sommaaccmedia = array();
            $mediaaccaltri = array();
            foreach ($resat as $rowat) {
                /* totale modelli altri */
                $idaltroatelier = $rowat['id'];
                $sqlmedia = $db->prepare('SELECT COUNT(*) totaccaltri, SUM(prezzoaccessorio) as totaccmediaaltri, AVG(prezzoaccessorio) as totmediaaccaltri FROM `accessori_appuntamento` WHERE idaccessorio = ? AND idappuntamento = ANY(SELECT id FROM calendario WHERE calendario.id = accessori_appuntamento.idappuntamento AND calendario.idatelier = ? AND YEAR(calendario.data) = ? AND calendario.acquistato = ?) GROUP BY idaccessorio');
                $sqlmedia->execute(array($idaccessorio, $idaltroatelier, $anno, 1));
                $resmedia = $sqlmedia->fetchAll();
                foreach ($resmedia as $rowmedia) {
                    $arrtotaccmedia[] = $rowmedia['totaccaltri'];
                    $sommaaccmedia[] = $rowmedia['totaccmediaaltri'];
                    $mediaaccaltri[] = $rowmedia['totmediaaccaltri'];
                }
            }
            $medianumaccaltri = 0;
            if ($arrtotaccmedia) {
                $medianumaccaltri = array_sum($arrtotaccmedia) / count($arrtotaccmedia);
            }
            $mediaprezzoaccaltri = 0;
            if ($sommaaccmedia) {
                $mediaprezzoaccaltri = array_sum($sommaaccmedia) / count($sommaaccmedia);
            }
            $mediamediaaccaltri = 0;
            if ($mediaaccaltri) {
                $mediamediaaccaltri = array_sum($mediaaccaltri) / count($mediaaccaltri);
            }
            /**/
            $totaleacc = $row['totacc'];
            $nomeacc = $row['nomeaccessorio'];
            $totprezzoacc = $row['totprezzoacc'];
            $mediaacc = $row['totmediaacc'];

            if ($_SESSION["livello"] == 0 || $_SESSION["livello"] == 1 || $direttoaffiliato == "d") {
                $altriaccprint = "<span style=\"font-weight: normal; font-style: italic;\">(" . number_format($medianumaccaltri, 1, ',', '.') . ")</span>";
                $altrimediaprezzoacc = "<span style=\"font-weight: normal; font-style: italic;\">(" . number_format($mediaprezzoaccaltri, 2, ',', '.') . " &euro;)</span>";
                $mediaaccaltriprint = "<span style=\"font-weight: normal; font-style: italic;\">(" . number_format($mediamediaaccaltri, 2, ',', '.') . " &euro;)</span>";
            }


            $printtipoacc .= "<div class=\"statprofit\">$nomeacc</div><div class=\"valoreprofit\"> $totaleacc $altriaccprint</div><div class=\"valoreprofit_big\">" . number_format($totprezzoacc, 2, ',', '.') . " &euro; </div><div class=\"valoreprofit_big\">" . number_format($mediaacc, 2, ',', '.') . " &euro; $mediaaccaltriprint</div><div class=\"chiudi\"></div>";
            $arraccessori[$nomeacc] = round($totaleacc, 2, PHP_ROUND_HALF_UP);
        }

        $statistiche2 .= "<div class=\"statprofit\" style=\"font-weight: bolder;\">ACCESSORI VENDUTI $nomeatelier</div><div class=\"valoreprofit\"> N° </div><div class=\"valoreprofit_big\"> TOTALE</div><div class=\"valoreprofit_big\">MEDIA</div><div class=\"chiudi\"></div><br />$printtipoacc<div class=\"chiudi\"></div><br /><br />";

        for ($i = 1; $i <= 12; $i++) {
            /* Appuntamenti totale per mese */
            $sql = $db->prepare('SELECT COUNT(*) totacc, nomeaccessorio FROM `accessori_appuntamento` WHERE idappuntamento = ANY(SELECT id FROM calendario WHERE calendario.id = accessori_appuntamento.idappuntamento ' . $where . ' AND YEAR(calendario.data) = ? AND MONTH(calendario.data) = ? AND calendario.acquistato = ?) GROUP BY idaccessorio');
            $sql->execute(array($anno, $i, 1));
            $res = $sql->fetchAll();
            foreach ($res as $row) {
                $totaleaccm = $row['totacc'];
                $nomeaccm = $row['nomeaccessorio'];
                $arrmese[$arrmesi[$i]][$nomeaccm] = round($totaleaccm, 2, PHP_ROUND_HALF_UP);
            }
        }

        /**/
        for ($m = 1; $m <= 12; $m++) {
            $mesi[] = $arrmesiesteso[$m];
            $datimese[$m] = $arrmese[$arrmesi[$m]];
        }
        if (!$datimese) {
            $datimese = "";
        }


        /**/
        die('{"anno": ' . json_encode($anno) . ', "mesi": ' . json_encode($mesi) . ', "datimese": ' . json_encode($datimese) . ', "datiprovenienza": ' . json_encode($arraccessori) . ', "statistiche2": ' . json_encode($statistiche2) . '}');
        break;

    /* STATISTICHE SARTORIA */
    case "submitstatistichesartoria":
        global $arrmesi;

        $anno = $_POST['anno'];
        $idatelier = $_POST['idatelier'];

        $idatelier2 = $_POST['idatelier2'];
        $anno2 = $_POST['anno2'];

        if (!$idatelier && !$idatelier2) {
            $idatelier = $_SESSION['idatelier'];
        } else if ($idatelier2) {
            $idatelier = $idatelier2;
        }

        if ($anno2) {
            $anno = $anno2;
        }
        $dal = $_POST['datap'];
        $al = $_POST['datap2'];
        if ($dal != "" && $al != "") {
            $data_dal = formatDateDb($dal);
            $data_al = formatDateDb($al);
        } else {
            $data_dal = $data_al = "";
        }
        /* diretti / affiliati per media */
        $direttoaffiliato = getDati("utenti", "WHERE id = $idatelier")[0]['diraff'];
        /**/

        $where = "AND calendario.idatelier = $idatelier";
        $datiatelier = getDati("utenti", "WHERE id = $idatelier");
        $nomeatelier = $datiatelier[0]['nominativo'];

        /* sartoria */
        if ($data_dal == '') {
            $sql = $db->prepare('SELECT idsartoria, COUNT(*) totacc, SUM(prezzosartoria) as totprezzoacc, SUM(costosartoria) as totmediaacc, nomesartoria FROM `sartoria_appuntamento` WHERE idappuntamento = ANY(SELECT id FROM calendario WHERE calendario.id = sartoria_appuntamento.idappuntamento ' . $where . ' AND YEAR(calendario.data) = ? AND calendario.acquistato = ? AND calendario.sartoria = ?) GROUP BY idsartoria ORDER BY totacc DESC');
            $sql->execute(array($anno, 1, 1));
        } else {
            $sql = $db->prepare('SELECT idsartoria, COUNT(*) totacc, SUM(prezzosartoria) as totprezzoacc, SUM(costosartoria) as totmediaacc, nomesartoria FROM `sartoria_appuntamento` WHERE '
                    . 'idappuntamento = ANY(SELECT id FROM calendario WHERE calendario.id = sartoria_appuntamento.idappuntamento ' . $where . ' AND calendario.data BETWEEN ? and ? AND calendario.acquistato = ? AND calendario.sartoria = ?) GROUP BY idsartoria ORDER BY totacc DESC');
            $sql->execute(array($data_dal, $data_al, 1, 1));
        }

        $res = $sql->fetchAll(PDO::FETCH_ASSOC);

        foreach ($res as $row) {
            $idsartoria = $row['idsartoria'];
            /* media altri */
            $sqlat = $db->prepare("SELECT id FROM utenti WHERE id != ? AND livello = ? AND diraff = ?");
            $sqlat->execute(array($idatelier, 5, $direttoaffiliato));

            $resat = $sqlat->fetchAll();
            $arrtotsartmedia = array();
            $sommasartmedia = array();
            $mediasartaltri = array();
            foreach ($resat as $rowat) {
                /* totale modelli altri */
                $idaltroatelier = $rowat['id'];
                if ($data_dal == '') {
                    $sqlmedia = $db->prepare('SELECT COUNT(*) totsartaltri, SUM(prezzosartoria) as totprezzosartaltri, SUM(costosartoria) as totmediasartaltri FROM `sartoria_appuntamento` WHERE idsartoria = ? AND idappuntamento = ANY(SELECT id FROM calendario WHERE calendario.id = sartoria_appuntamento.idappuntamento AND calendario.idatelier = ? AND YEAR(calendario.data) = ? AND calendario.acquistato = ? AND calendario.sartoria = ?) GROUP BY idsartoria');
                    $sqlmedia->execute(array($idsartoria, $idaltroatelier, $anno, 1, 1));
                } else {
                    $sqlmedia = $db->prepare('SELECT COUNT(*) totsartaltri, SUM(prezzosartoria) as totprezzosartaltri, SUM(costosartoria) as totmediasartaltri FROM `sartoria_appuntamento` WHERE '
                            . 'idsartoria = ? AND idappuntamento = ANY(SELECT id FROM calendario WHERE calendario.id = sartoria_appuntamento.idappuntamento AND calendario.idatelier = ? '
                            . 'AND calendario.data BETWEEN ? and ? AND calendario.acquistato = ? AND calendario.sartoria = ?) GROUP BY idsartoria');
                    $sqlmedia->execute(array($idsartoria, $idaltroatelier, $data_dal, $data_al, 1, 1));
                }

                $resmedia = $sqlmedia->fetchAll(PDO::FETCH_ASSOC);
                foreach ($resmedia as $rowmedia) {
                    $arrtotsartmedia[] = $rowmedia['totsartaltri'];
                    $sommasartmedia[] = $rowmedia['totprezzosartaltri'];
                    $mediaaccaltri[] = $rowmedia['totmediasartaltri'];
                }
            }
            $medianumsartaltri = 0;
            if ($arrtotsartmedia) {
                $medianumsartaltri = array_sum($arrtotsartmedia) / count($arrtotsartmedia);
            }
            $mediaprezzosartaltri = 0;
            if ($sommasartmedia) {
                $mediaprezzosartaltri = array_sum($sommasartmedia) / count($sommasartmedia);
            }
            $mediacostisartaltri = 0;
            if ($mediasartaltri) {
                $mediacostisartaltri = array_sum($mediasartaltri) / count($mediasartaltri);
            }
            /**/
            $totaleacc = $row['totacc'];
            $nomeacc = $row['nomesartoria'];
            $totprezzoacc = $row['totprezzoacc'];
            $mediaacc = $row['totmediaacc'];

            if ($_SESSION["livello"] == 0 || $_SESSION["livello"] == 1 || $direttoaffiliato == "d") {
                $nummediaaltrisartprint = "<span style=\"font-weight: normal; font-style: italic;\">(" . number_format($medianumsartaltri, 1, ",", ".") . ")</span>";
                $mediaprezzoaltrisartprint = "<span style=\"font-weight: normal; font-style: italic;\">(" . number_format($mediaprezzosartaltri, 2, ",", ".") . " &euro;)</span>";
                $mediacostialtrisartprint = "<span style=\"font-weight: normal; font-style: italic;\">(" . number_format($mediacostisartaltri, 2, ",", ".") . " &euro;)</span>";
            }


            $printtipoacc .= "<div class=\"statprofit\">$nomeacc</div><div class=\"valoreprofit\"> $totaleacc $nummediaaltrisartprint</div><div class=\"valoreprofit_big\">" . number_format($totprezzoacc, 2, ',', '.') . " &euro; $mediaprezzoaltrisartprint</div><div class=\"valoreprofit_big\">" . number_format($mediaacc, 2, ',', '.') . " &euro; $mediacostialtrisartprint</div><div class=\"chiudi\"></div>";
            $arraccessori[$nomeacc] = round($totaleacc, 2, PHP_ROUND_HALF_UP);
        }

        $statistiche2 .= "<div class=\"statprofit\" style=\"font-weight: bolder;\">TIPO DI RIPARAZIONE $nomeatelier</div><div class=\"valoreprofit\"> N° </div><div class=\"valoreprofit_big\"> TOTALE INCASSI</div><div class=\"valoreprofit_big\">TOTALE COSTI</div><div class=\"chiudi\"></div><br />$printtipoacc<div class=\"chiudi\"></div><br /><br />";

        for ($i = 1; $i <= 12; $i++) {
            /* Appuntamenti totale per mese */
            $sql = $db->prepare('SELECT COUNT(*) totacc, nomesartoria FROM `sartoria_appuntamento` WHERE idappuntamento = ANY(SELECT id FROM calendario WHERE calendario.id = sartoria_appuntamento.idappuntamento ' . $where . ' AND YEAR(calendario.data) = ? AND MONTH(calendario.data) = ? AND calendario.acquistato = ? AND calendario.sartoria = ?) GROUP BY idsartoria');
            $sql->execute(array($anno, $i, 1, 1));
            $res = $sql->fetchAll(PDO::FETCH_ASSOC);
            foreach ($res as $row) {
                $totaleaccm = $row['totacc'];
                $nomeaccm = $row['nomesartoria'];
                $arrmese[$arrmesi[$i]][$nomeaccm] = round($totaleaccm, 2, PHP_ROUND_HALF_UP);
            }
        }

        /**/
        for ($m = 1; $m <= 12; $m++) {
            $mesi[] = $arrmesiesteso[$m];
            $datimese[$m] = $arrmese[$arrmesi[$m]];
        }
        if (!$datimese) {
            $datimese = "";
        }


        /**/
        die('{"anno": ' . json_encode($anno) . ', "mesi": ' . json_encode($mesi) . ', "datimese": ' . json_encode($datimese) . ', "datiprovenienza": ' . json_encode($arraccessori) . ', "statistiche2": ' . json_encode($statistiche2) . '}');
        break;

    case "submitstatisticheconversioni":
        global $arrmesi;
        $now = date('Y-m-d');
        $anno = $_POST['anno'];
        $idatelier = $_POST['idatelier'];

        $idatelier2 = $_POST['idatelier2'];
        $anno2 = $_POST['anno2'];

        if (!$idatelier && !$idatelier2) {
            $idatelier = $_SESSION['idatelier'];
        } else if ($idatelier2) {
            $idatelier = $idatelier2;
        }

        if ($anno2) {
            $anno = $anno2;
        }
        $statistiche = "<div class=\"statprofit\" style=\"color: #2E65A1;\"><b>Dipendente</b></div>"
                . "<div class=\"valoreprofit\" style=\"color: #2E65A1;\"><b>Totale gestiti</b></div>"
                . "<div class=\"valoreprofit\" style=\"color: #2E65A1;\"><b>Totale convertiti</b></div>"
                . "<div class=\"valoreprofit\" style=\"color: #2E65A1;\"><b>Percentuale</b></div>"
                . "<div class=\"chiudi\"></div>";
        $qry = "select * from dipendenti_atelier where idatelier=$idatelier and attivo=1 order by cognome;";
        $rs = $db->prepare($qry);
        $rs->execute();
        $cols = $rs->fetchAll(PDO::FETCH_ASSOC);
        $qry = "select id, nome, cognome from utenti where livello='3' and (idatelier=$idatelier or id in (select idutente from atelier_utente where idatelier=$idatelier)) order by cognome";
        $rs = $db->prepare($qry);
        $rs->execute();
        $cols_new = $rs->fetchAll(PDO::FETCH_ASSOC);
        $anno_curr = date('Y');
        if ($anno < $anno_curr) {
            $mese = 12;
        } else {
            $mese = date('m');
        }

        for ($i = 1; $i <= $mese; $i++) {
            $statistiche .= "<div class=\"statprofit\"><b>" . $arrmesiesteso[$i] . "</b></div>"
                    . "<div class=\"valoreprofit\">&nbsp;</div>"
                    . "<div class=\"valoreprofit\">&nbsp;</div>"
                    . "<div class=\"valoreprofit\">&nbsp;</div>"
                    . "<div class=\"chiudi\">&nbsp;</div>";
            if ($anno < 2024 || ($anno == 2024 && $i <= 6)) {
                foreach ($cols as $col) {
                    $qry = "select COUNT(id) from calendario WHERE YEAR(data) = ? and MONTH(data) = ? AND idatelier = ? and iddip=? GROUP BY iddip";
                    $rs2 = $db->prepare($qry);
                    $rs2->execute(Array($anno, $i, $idatelier, $col['id']));
                    $totale_app = intval($rs2->fetchColumn());
                    $qry = "select COUNT(id) from calendario WHERE YEAR(data) = ? and MONTH(data) = ? AND idatelier = ? and iddip=? AND acquistato = 1  GROUP BY iddip";
                    $rs2 = $db->prepare($qry);
                    $rs2->execute(Array($anno, $i, $idatelier, $col['id']));
                    $totale_conv = intval($rs2->fetchColumn());
                    if ($anno == 2024 && $i == 6) {
                        $qry = "select id, nome, cognome from utenti where livello='3' and (idatelier=$idatelier or id in (select idutente from atelier_utente where idatelier=$idatelier)) "
                                . "and cognome='" . addslashes($col['cognome']) . "' limit 1;";
                        $rs_user = $db->prepare($qry);
                        $rs_user->execute();
                        if ($rs_user->RowCount() > 0) {
                            $idutente = $rs_user->fetchColumn();
                            $qry = "select COUNT(id) from calendario WHERE YEAR(data) = ? and MONTH(data) = ? AND idatelier = ? and idutente=? GROUP BY iddip";
                            $rs2 = $db->prepare($qry);
                            $rs2->execute(Array($anno, $i, $idatelier, $idutente));
                            $totale_app += intval($rs2->fetchColumn());
                            $qry = "select COUNT(id) from calendario WHERE YEAR(data) = ? and MONTH(data) = ? AND idatelier = ? and idutente=? AND acquistato = 1  GROUP BY iddip";
                            $rs2 = $db->prepare($qry);
                            $rs2->execute(Array($anno, $i, $idatelier, $idutente));
                            $totale_conv += intval($rs2->fetchColumn());
                        }
                    }
                    if ($totale_app > 0) {
                        $perc = round(100 * $totale_conv / $totale_app, 2);
                    } else {
                        $perc = 0;
                    }
                    $statistiche .= "<div class=\"statprofit\">{$col['cognome']} {$col['nome']}</div>"
                            . "<div class=\"valoreprofit\">$totale_app</div>"
                            . "<div class=\"valoreprofit\">$totale_conv</div>"
                            . "<div class=\"valoreprofit\">$perc%</div>"
                            . "<div class=\"chiudi\"></div>";
                }
            } elseif ($anno > 2024 || ($anno == 2024 && $i > 6)) {
                foreach ($cols_new as $col) {
                    $qry = "select COUNT(id) from calendario WHERE YEAR(data) = ? and MONTH(data) = ? and data <= '$now' AND idatelier = ? and idutente=? GROUP BY idutente";
                    $rs2 = $db->prepare($qry);
                    $rs2->execute(Array($anno, $i, $idatelier, $col['id']));
                    $totale_app = intval($rs2->fetchColumn());
                    $qry = "select COUNT(id) from calendario WHERE YEAR(data) = ? and MONTH(data) = ? and data <= '$now' AND idatelier = ? and idutente=? AND acquistato = 1 and disdetto=0  GROUP BY idutente";
                    $rs2 = $db->prepare($qry);
                    $rs2->execute(Array($anno, $i, $idatelier, $col['id']));
                    $totale_conv = intval($rs2->fetchColumn());
//                    if($col['id'] == 184) {
//                        echo "$anno, $i, $idatelier, {$col['id']} $totale_conv\n";
//                    }
                    if ($totale_app > 0) {
                        $perc = round(100 * $totale_conv / $totale_app, 2);
                    } else {
                        $perc = 0;
                    }
                    $statistiche .= "<div class=\"statprofit\">{$col['cognome']} {$col['nome']}</div>"
                            . "<div class=\"valoreprofit\">$totale_app</div>"
                            . "<div class=\"valoreprofit\">$totale_conv</div>"
                            . "<div class=\"valoreprofit\">$perc%</div>"
                            . "<div class=\"chiudi\"></div>";
                }
            }
        }

        /**/
        die('{"anno" : ' . json_encode($anno) . ', "statistiche": ' . json_encode($statistiche) . ', "statistiche2": ' . json_encode($statistiche2) . '}');
        break;

    case "submitstatisticheconversioni_dip":
        global $arrmesi;

        $idatelier = $_POST['idatelier'];
        $data_dal = $_POST['data'];
        $data_al = $_POST['data2'];
        $statistiche = "<div class=\"statprofit\" style=\"color: #2E65A1;\"><b>Dipendente</b></div>"
                . "<div class=\"valoreprofit\" style=\"color: #2E65A1;\"><b>Totale gestiti</b></div>"
                . "<div class=\"valoreprofit\" style=\"color: #2E65A1;\"><b>Totale convertiti</b></div>"
                . "<div class=\"valoreprofit\" style=\"color: #2E65A1;\"><b>Percentuale</b></div>"
                . "<div class=\"chiudi\"></div>";
        if (is_array($idatelier)) {
            $qry = "select id, nome, cognome from utenti where livello='3' and attivo=1 and (idatelier in (" . join(",", $idatelier) . ") or id in (select idutente from atelier_utente where idatelier in (" . join(",", $idatelier) . "))) "
                    . "order by cognome";
            $qry_atelier = " and idatelier in (" . join(",", $idatelier) . ")";
        } else {
            $qry = "select id, nome, cognome from utenti where livello='3' and attivo=1 order by cognome";
            $qry_atelier = '';
        }
        //die($qry);
        $rs = $db->prepare($qry);
        $rs->execute();
        $cols_new = $rs->fetchAll(PDO::FETCH_ASSOC);
        foreach ($cols_new as $col) {
            $qry = "select COUNT(id) from calendario WHERE data between ? and ? and tipoappuntamento!='5' and data < '$now' $qry_atelier and idutente=? GROUP BY idutente";
            $rs2 = $db->prepare($qry);
            $rs2->execute(Array($data_dal, $data_al, $col['id']));
            $totale_app = intval($rs2->fetchColumn());
            $qry = "select COUNT(id) from calendario WHERE data between ? and ? $qry_atelier and idutente=? AND acquistato = '1'  GROUP BY idutente";
            //die($qry);
            $rs2 = $db->prepare($qry);
            $rs2->execute(Array($data_dal, $data_al, $col['id']));
            $totale_conv = intval($rs2->fetchColumn());
            if ($totale_app > 0) {
                $perc = round(100 * $totale_conv / $totale_app, 2);
            } else {
                $perc = 0;
            }
            $statistiche .= "<div class=\"statprofit\">{$col['cognome']} {$col['nome']}</div>"
                    . "<div class=\"valoreprofit\">$totale_app</div>"
                    . "<div class=\"valoreprofit\">$totale_conv</div>"
                    . "<div class=\"valoreprofit\">$perc%</div>"
                    . "<div class=\"chiudi\"></div>";
        }

        /**/
        die('{"anno" : ' . json_encode($anno) . ', "statistiche": ' . json_encode($statistiche) . ', "statistiche2": ' . json_encode($statistiche2) . '}');
        break;

    case "submitstatisticheconversioni_dipcsv":
        $idatelier = $_GET['idatelier'];
        $data_dal = $_GET['data'];
        $data_al = $_GET['data2'];
        $csv = '"Dipendente";"Totale gestiti";"Totale convertiti";"Percentuale"' . "\n";
        if (is_array($idatelier)) {
            $qry = "select id, nome, cognome from utenti where livello='3' and (idatelier in (" . join(",", $idatelier) . ") or id in (select idutente from atelier_utente where idatelier in (" . join(",", $idatelier) . "))) "
                    . "order by cognome";
            $qry_atelier = " and idatelier in (" . join(",", $idatelier) . ")";
        } else {
            $qry = "select id, nome, cognome from utenti where livello='3' order by cognome";
            $qry_atelier = '';
        }
        //die($qry);
        $rs = $db->prepare($qry);
        $rs->execute();
        $cols_new = $rs->fetchAll(PDO::FETCH_ASSOC);
        foreach ($cols_new as $col) {
            $qry = "select COUNT(id) from calendario WHERE data between ? and ? and tipoappuntamento!='5' and data < '$now' $qry_atelier and idutente=? GROUP BY idutente";
            $rs2 = $db->prepare($qry);
            $rs2->execute(Array($data_dal, $data_al, $col['id']));
            $totale_app = intval($rs2->fetchColumn());
            $qry = "select COUNT(id) from calendario WHERE data between ? and ? $qry_atelier and idutente=? AND acquistato = 1  GROUP BY idutente";
            $rs2 = $db->prepare($qry);
            $rs2->execute(Array($data_dal, $data_al, $col['id']));
            $totale_conv = intval($rs2->fetchColumn());
            if ($totale_app > 0) {
                $perc = round(100 * $totale_conv / $totale_app, 2);
            } else {
                $perc = 0;
            }
            $csv .= '"' . str_replace('"', '', $col['cognome'] . ' ' . $col['nome']) . '";"' . $totale_app . '";"' . $totale_conv . '";"' . $perc . '%"' . "\n";
        }

        header("Content-Type: text/csv");
        header("Content-Disposition: inline; filename=export.csv");
        die($csv);
        break;

    case 'getEsportaConversioni':
        if ($_POST['data_dal'] != "" && $_POST['data_al'] != "") {
            $data_dal = $_POST['data_dal'];
            $data_al = $_POST['data_al'];
            require_once './library/spout-master/src/Spout/Autoloader/autoload.php';
            //use 'Box\Spout\Writer\Common\Creator\WriterEntityFactory';

            $rows = Array();
            $riga1 = ['Consulente', 'Appuntamenti svolti', 'Appuntamenti convertiti', '% Conversioni', 'Negozio'];
            $rows[] = $riga1;
            $dipendenti = getDipendentiAll();
            $tipo_atelier = $_POST['tipo_atelier'];
            if ($tipo_atelier != '') {
                $qry_atelier = "having tipo_atelier='$tipo_atelier'";
            }
            foreach ($dipendenti as $i => $dip) {
                $idutente = $dip['id'];
                $qry = "select idatelier,"
                        . "(select nominativo from utenti u2 where u2.id=a.idatelier limit 1) as nome_atelier,"
                        . "(select diraff from utenti u2 where u2.id=a.idatelier limit 1) as tipo_atelier"
                        . " from atelier_utente a where idutente=" . $idutente . " $qry_atelier;";
                $rs = $db->prepare($qry);
                $rs->execute();
//                if ($rs->RowCount() > 1) {
//                    die("" . ucfirst(strtolower($dip['nome'])) . ' ' . ucfirst(strtolower($dip['cognome'])));
//                }
                while ($col_atelier = $rs->fetch(PDO::FETCH_ASSOC)) {
                    $idatelier = $col_atelier['idatelier'];
                    $nome_atelier = $col_atelier['nome_atelier'];
                    switch ($nome_atelier) {
                        case 'Valmontone':
                            $nome_atelier = 'Valmontone Village';
                            break;

                        case 'Citta\' Sant\'Angelo':
                            $nome_atelier = 'Pescara';
                            break;

                        case 'Valdichiana':
                            $nome_atelier = 'Valdichiana Village';
                            break;

                        case 'Caserta':
                            $nome_atelier = 'Reggia Outlet Village';
                            break;

                        case 'Mantova':
                            $nome_atelier = 'Mantova Village';
                            break;

                        case 'Mondovi\'':
                            $nome_atelier = 'Mondovicino Village';
                            break;
                    }
                    $qry = "select COUNT(id) from calendario WHERE data between ? and ? and tipoappuntamento!='5' and data < '$now' AND idatelier = ? and idutente=? GROUP BY idutente";
                    $rs2 = $db->prepare($qry);
                    $rs2->execute(Array($data_dal, $data_al, $idatelier, $idutente));
                    $totale_app = intval($rs2->fetchColumn());
                    $qry = "select COUNT(id) from calendario WHERE data between ? and ? AND idatelier = ? and idutente=? AND acquistato = 1 and disdetto=0  GROUP BY idutente";
                    $rs2 = $db->prepare($qry);
                    $rs2->execute(Array($data_dal, $data_al, $idatelier, $idutente));
                    $totale_conv = intval($rs2->fetchColumn());
                    if ($totale_app > 0) {
                        $perc = round(100 * $totale_conv / $totale_app, 2);
                    } else {
                        $perc = 0;
                    }
                    if ($totale_app > 0) {
                        $row_dip = Array(
                            ucwords(strtolower($dip['nome'])) . ' ' . ucwords(strtolower($dip['cognome'])),
                            $totale_app,
                            $totale_conv,
                            number_format($perc, 2, ",", "."),
                            $nome_atelier
                        );
                        $rows[] = $row_dip;
                    }
                }
            }
            //var_dump($rows);
            //die();
            $path = 'tmp/conversioni-consulenti.xlsx';
            $writer = Box\Spout\Writer\Common\Creator\WriterEntityFactory::createXLSXWriter();
            $writer->openToFile($path);
            foreach ($rows as $row) {
                $rowFromValues = Box\Spout\Writer\Common\Creator\WriterEntityFactory::createRowFromArray($row);
                $writer->addRow($rowFromValues);
            }
            $writer->close();
            header('Content-Type: application/octet-stream');
            header("Content-Transfer-Encoding: Binary");
            header("Content-disposition: attachment; filename=\"conversioni-consulenti.xlsx\"");

// Actual download.
            readfile($path);
            die();
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
        <script type="text/javascript" src="./js/functions-statistiche.js?v=4"></script>
        <!-- lingua italiana datepicker -->
        <script src="./js/jquery-ui-1.11.4/datepicker-it.js"></script>
        <script src="./js/Chart.js"></script>
        <script src="/js/select2-4.1.0/dist/js/select2.full.min.js"></script>
        <link type="text/css" href="/js/select2-4.1.0/dist/css/select2.min.css" rel="Stylesheet" />
        <script type="text/javascript">
            $(document).ready(function () {
<?php if ($op == "provenienza") { ?>
                    mostraProvenienza('<?php echo DATE('Y'); ?>', '<?php echo $_SESSION['idatelier']; ?>');
<?php } else if ($op == "abiti") { ?>
                    mostraAbiti('<?php echo DATE('Y'); ?>', '<?php echo $_SESSION['idatelier']; ?>');
<?php } else if ($op == "accessori") { ?>
                    mostraAccessori('<?php echo DATE('Y'); ?>', '<?php echo $_SESSION['idatelier']; ?>');
<?php } else if ($op == "sartoria") { ?>
                    mostraSartoria('<?php echo DATE('Y'); ?>', '<?php echo $_SESSION['idatelier']; ?>');
<?php } else if ($op == "conversioni") { ?>
                    mostraConversioni('<?php echo DATE('Y'); ?>', '<?php echo $_SESSION['idatelier']; ?>');
<?php } else if ($op == "esporta") { ?>
                    mostraEsporta();
<?php } else if ($op == "conversioni_dip") {
    ?>
                    mostraConversioniDip('');
    <?php
} else if ($op == "admin") {
    if ($_SESSION["livello"] != 0 && $_SESSION["livello"] != 1 && $_SESSION["livello"] != 5) {
        header("location: /statistiche.php");
        die();
    }
    ?>
                    statisticheAdmin();

<?php } else { ?>
                    mostraAppuntamenti('<?php echo DATE('Y'); ?>', '<?php echo $_SESSION['idatelier']; ?>');
<?php } ?>
            });
        </script>
    </head>
    <body class="colormodulo">
        <?php include './components/top.php'; ?>
        <?php if (moduloattivo("statistiche") > 0 || $_SESSION['ruolo'] == CENTRALINO) { ?>
            <div class="barra_submenu sizing">
                <?php
                if ($_SESSION["livello"] == 0 || $_SESSION["livello"] == 1 || $_SESSION["livello"] == 5 || ($_SESSION["livello"] == 3 && $_SESSION['ruolo'] == 2)) {
                    ?>
                    <li class="box_submenu sizing"><a href="/statistiche.php?op=admin"><i class="fa fa-bar-chart fa-lg" aria-hidden="true"></i> Generiche Admin</a></li>
                <?php } ?>
                <li class="box_submenu sizing"><a href="/statistiche.php"><i class="fa fa-calendar fa-lg" aria-hidden="true"></i> Appuntamenti</a></li>
                <li class="box_submenu sizing"><a href="/statistiche.php?op=provenienza"><i class="fa fa-search fa-lg" aria-hidden="true"></i> Provenienza</a></li>
                <li class="box_submenu sizing"><a href="/statistiche.php?op=abiti"><i class="fa fa-female fa-lg" aria-hidden="true"></i> Abiti</a></li>
                <li class="box_submenu sizing"><a href="/statistiche.php?op=accessori"><i class="fa fa-diamond fa-lg" aria-hidden="true"></i> Accessori</a></li>
                <li class="box_submenu sizing"><a href="/statistiche.php?op=sartoria"><i class="fa fa-scissors fa-lg" aria-hidden="true"></i> Sartoria</a></li>
                <li class="box_submenu sizing"><a href="/statistiche.php?op=conversioni"><i class="fa fa-check-circle fa-lg" aria-hidden="true"></i> Conversioni</a></li>
                <li class="box_submenu sizing"><a href="/statistiche.php?op=conversioni_dip"><i class="fa fa-check fa-lg" aria-hidden="true"></i> Conversioni per dipendente</a></li>
                <?php if ($_SESSION['ruolo'] == 7 || $_SESSION['livello'] <= 1) { ?>
                    <li class="box_submenu sizing"><a href="/statistiche.php?op=esporta"><i class="fa fa-exchange fa-lg" aria-hidden="true"></i> Esporta dati</a></li>
                <?php } ?>
            </div>
            <div class="content sizing">
                <div class="barra_op sizing">
                    <?php
                    if ($op == "provenienza") {
                        echo "Statistiche provenienza clienti";
                    } else if ($op == "abiti") {
                        echo "Statistiche Abiti venduti";
                    } else if ($op == "accessori") {
                        echo "Statistiche Accessori venduti";
                    } else if ($op == "commesse") {
                        echo "Statistiche Commesse";
                    } else if ($op == "fornitoricommesse") {
                        echo "Statistiche fornitori commesse";
                    } else if ($op == "admin") {
                        echo "Statistiche amministratore";
                    } else if ($op == "conversioni") {
                        echo "Statistiche Conversioni";
                    } else if ($op == "conversioni_dip") {
                        echo "Statistiche Conversioni per dipendente";
                    } else if ($op == "esporta") {
                        echo "Esporta Dati";
                    } else {
                        echo "Statistiche Appuntamenti";
                    }
                    ?>
                    <div id="messaggio" class="messaggiook">Operazione avvenuta con successo</div>
                </div>
                <div class="showcont sizing">                            
                    <!-- qui contenuto del modulo --> 
                </div>
            </div>
        <?php } ?>
    </body>
</html>