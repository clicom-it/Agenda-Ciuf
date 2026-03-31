<?php
ob_start('ob_gzhandler');
include './library/connessione.php';
include './library/controllo.php';
include './library/config.php';
include './library/functions.php';
include './library/basic.class.php';
/* libreria del modulo */
include './library/commesse.class.php';

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
    
    case "editstatocommessa":
        $tabella = "commesse";
        $id = $_POST['id'];
        $stato = $_POST['stato'];

        $sql = $db->prepare("UPDATE $tabella SET stato = ? WHERE id = ?");
        $sql->execute(array($stato, $id));
        
        die('{"msg" : "ok"}');
        break;
    
    case "eliminacosto":
        $tabella = "commesse_costi";
        $id = $_POST['id'];
        $dati = new commesse($id, $tabella);
        $dati->cancellacosto();
        die('{"msg" : "ok"}');
        break;
    
    case "modificacosto":
        $tabella = "commesse_costi";
        $idcostocomm = $_POST['idcostocomm'];
        $fornitore = trim($_POST['rigafornitore_' . $idcostocomm . '']);
        $idfornitore = $_POST['rigaidfornitore_' . $idcostocomm . ''];
        $centrodicosto = $_POST['rigacentrodicosto_' . $idcostocomm . ''];
        $idcentrodicosto = $_POST['rigaidcentrodicosto_' . $idcostocomm . ''];
        $descrizione = $_POST['rigadescrizione_' . $idcostocomm . ''];
        
        $costojs = $_POST['rigacosto_' . $idcostocomm . ''];
        $costo = str_replace(",", ".", $costojs);                
        
        if (strlen($fornitore) > 0 && $costo > 0) {            
            $dati = new commesse($idcostocomm, $tabella);
            $campi = array("fornitore", "idfornitore", "centrodicosto", "idcentrodicosto", "descrizione", "costo");
            $valori = array($fornitore, $idfornitore, $centrodicosto, $idcentrodicosto, $descrizione, $costo);
            $dati->aggiornacosto($campi, $valori, $costo);
            die('{"msg" : "ok"}');
        } else {
            die('{"msg" : "ko", "msgko" : "Il Campo fornitore e il campo costo non possono essere vuoti"}');
        }

        break;

    case "aggiungicosto":
        /* dati fornitori */
        $fornitori = getDati("clienti_fornitori", "WHERE tipo = 2");
        /* centri di costo */
        $centridicosto = getDati("centri_costo", "");
        /**/        
        $tabella = "commesse_costi";
        $idcommessa = $_POST['idcomm'];
        $idvocecomm = $_POST['idvocecomm'];
        $fornitore = trim($_POST['fornitore_' . $idvocecomm . '']);
        $idfornitore = $_POST['idfornitore_' . $idvocecomm . ''];
        $centrodicosto = $_POST['centrodicosto_' . $idvocecomm . ''];
        $idcentrodicosto = $_POST['idcentrodicosto_' . $idvocecomm . ''];
        $descrizione = $_POST['descrizione_' . $idvocecomm . ''];
        $costojs = $_POST['costo_' . $idvocecomm . ''];
        $costo = str_replace(",", ".", $costojs);
        
        if (strlen($fornitore) > 0 && $costo > 0) {            
            $dati = new commesse($id, $tabella);
            $campi = array("idcomm", "idvocecomm", "fornitore", "idfornitore", "centrodicosto", "idcentrodicosto", "descrizione", "costo");
            $valori = array($idcommessa, $idvocecomm, $fornitore, $idfornitore, $centrodicosto, $idcentrodicosto, $descrizione, $costo);
            $idcostocomm = $dati->aggiungicosto($campi, $valori);

            $rigacosto = "<div id=\"rigariepilogocosti_$idcostocomm\" class=\"dettagliocosti sizing\">"
                    . "<input type=\"text\" disabled class=\"cercacdc nopost input_moduli sizing float_moduli_small_10\" value=\"$centrodicosto\" name=\"rigacentrodicosto_$idcostocomm\" id=\"rigacentrodicosto_$idcostocomm\" title=\"Centro di costo\" placeholder=\"Centro di costo\" /><input type=\"hidden\" value=\"$idcentrodicosto\" name=\"rigaidcentrodicosto_$idcostocomm\" id=\"rigaidcentrodicosto_$idcostocomm\" class=\"nopost\" />"
                    . "<input type=\"text\" disabled class=\"cercafornitore nopost input_moduli sizing float_moduli_40\" value=\"$fornitore\" name=\"rigafornitore_$idcostocomm\" id=\"rigafornitore_$idcostocomm\" title=\"Fornitore\" placeholder=\"Fornitore\" /><input type=\"hidden\" value=\"$idfornitore\" name=\"rigaidfornitore_$idcostocomm\" id=\"rigaidfornitore_$idcostocomm\" class=\"nopost\" />"
                    . "<input type=\"text\" disabled class=\"nopost input_moduli sizing float_moduli_40\" value=\"$descrizione\" name=\"rigadescrizione_$idcostocomm\" id=\"rigadescrizione_$idcostocomm\" title=\"Descrizione lavoro\" placeholder=\"Descrizione lavoro\" />"
                    . "<input type=\"text\" disabled class=\"nopost input_moduli sizing float_moduli_small_10\" value=\"$costo\" name=\"rigacosto_$idcostocomm\" id=\"rigacosto_$idcostocomm\" title=\"Costo\" placeholder=\"Costo\" />"
                    . "<div id=\"comandi_$idcostocomm\" style=\"float: left;\"><a href=\"javascript:;\" class=\"addbutton\" onclick=\"modificacosto('$idcostocomm', '$idvocecomm', '$costo');\"><i class=\"fa fa-pencil fa-lg\" aria-hidden=\"true\" style=\"color: #000000; line-height: 35px;\"></i></a> <a href=\"javascript:;\" class=\"addbutton\" onclick=\"eliminacosto('$idcostocomm', '$idvocecomm');\"><i class=\"fa fa-times fa-lg\" aria-hidden=\"true\" style=\"color: #cd0a0a; line-height: 35px;\"></i></a></div>
                    <div class=\"chiudi\"></div>
                    </div>";
            
            $sql = $db->prepare("UPDATE commesse SET totalecosti = totalecosti+$costo WHERE id = $idcommessa");
            $sql->execute();

            die('{"centridicosto" : ' . json_encode($centridicosto) . ', "fornitori" : ' . json_encode($fornitori) . ', "msg" : "ok", "rigacosto" : ' . json_encode($rigacosto) . '}');
        } else {
            die('{"msg" : "ko", "msgko" : "Il Campo fornitore e costo non possono essere vuoti"}');
        }

        break;

    case "collegapreventivo":
        $tabella = "preventivi";
        $id = $_POST['id'];
        $dati = new commesse($id, $tabella);
        $preventivo = $dati->richiama();
        /* voci */
        $tabella = "preventivi_voci";
        $where = "idprev = $id ORDER BY ordine";
        $dativoci = new commesse($id, $tabella);
        $voci = $dativoci->richiamaWhere($where);
        die('{"valori" : ' . json_encode($preventivo) . ', "voci" : ' . json_encode($voci) . '}');
        break;

    case "preventiviokcliente":
        $tabella = "preventivi";
        $idcliente = $_POST['idcliente'];
        $dati = new commesse($id, $tabella);
        $where = "idcliente = $idcliente AND stato = '2'";
        $preventivo = $dati->richiamaWheredata($where);
        die('{"preventivi" : ' . json_encode($preventivo) . '}');
        break;

    case "editformcommesse":
        $tabella = "commesse";
        $id = $_POST['id'];
        $commesse = new commesse($id, $tabella);
        /* voci della commessa */
        for ($i = 0; $i < count($_POST['idlavcomm']); $i++) {
            if ($_POST['nome'][$i] != "" || $_POST['descr'][$i] != "") {
                if ($_POST['idlavcomm'][$i] > 0) {
                    $idlavcomm = $_POST['idlavcomm'][$i];
                } else {
                    $idlavcomm = "";
                }
                $voci[$i] = array("id" => $idlavcomm, "nome" => $_POST['nome'][$i], "descr" => $_POST['descr'][$i], "idprofit" => $_POST['idprofit'][$i], "prezzocliente" => $_POST['prezzocliente'][$i], "costo" => $_POST['costo'][$i], "oreprev" => $_POST['oreprev'][$i], "statovoce" => $_POST['statovoce'][$i], "ordine" => $i);
            } else {
                
            }
        }
        array_filter($voci, 'strlen'); // controllo array vuoto

        /* preventivi collegati alla commessa */
        for ($i = 0; $i < count($_POST['idprevcomm']); $i++) {
            if ($_POST['idprevcomm'][$i] != "") {
                $prevcoll[$i] = array("idpreventivo" => $_POST['idprevcomm'][$i], "numeropreventivo" => $_POST['numeroprevcomm'][$i], "datapreventivo" => $_POST['dataprevcomm'][$i], "titolopreventivo" => $_POST['titoloprevcomm'][$i], "totalepreventivo" => $_POST['totaleprevcomm'][$i]);
            } else {
                
            }
        }
        array_filter($prevcoll, 'strlen'); // controllo array vuoto

        /* tolgo dall'array submit e post che non mi servono */
        unset($_POST['submit'], $_POST['nome'], $_POST['idlavcomm'], $_POST['descr'], $_POST['prezzocliente'], $_POST['costo'], $_POST['idprofit'], $_POST['idprevcomm'], $_POST['numeroprevcomm'], $_POST['oreprev'], $_POST['statovoce'], $_POST['dataprevcomm'], $_POST['titoloprevcomm'], $_POST['totaleprevcomm']);

        foreach ($_POST as $k => $v) {
            $campi[] = $k;
            $valori[] = $v;
        }

        $commesse->aggiorna($campi, $valori, $voci, $prevcoll);
        die('{"msg": "ok"}');
        break;

    case "richiamacommessa":
        /* dati fornitori */
        $fornitori = getDati("clienti_fornitori", "WHERE tipo = 2");
        /* centri di costo */
        $centridicosto = getDati("centri_costo", "");
        foreach ($centridicosto as $centridicostod) {
            $cdc_option .= "<option value=\"".$centridicostod['nome']."\">".$centridicostod['nome']."</option>";
        }
        /**/
        $tabella = "commesse";
        $id = $_POST['id'];
        $dati = new commesse($id, $tabella);
        $commesse = $dati->richiama();
        /* voci */
        $tabella = "commesse_voci";
        $where = "idcomm = $id ORDER BY ordine";
        $dativoci = new commesse($id, $tabella);
        $voci = $dativoci->richiamaWhere($where);
        /* preventivi */
        $tabella = "commesse_preventivi";
        $where = "idcommessa = $id";
        $datiprevcoll = new commesse($id, $tabella);
        $prevcoll = $datiprevcoll->richiamaWhere($where);

        /* array ore lavorazioni */
        $arrore[] = "00:30";
        for ($i = 1; $i <= 200; $i++) {
            if ($i < 10) {
                $pref = "0";
            } else {
                $pref = "";
            }
            $arrore[] = $pref . $i . ":00";
            $arrore[] = $pref . $i . ":30";
        }
        $orecomplete = join(",", $arrore);
        /**/
        /* dati per ore lavorate e costi */
        foreach ($voci as $vocid) {
            $idvocecomm = $vocid['id'];
            $nomevocecomm = $vocid['nome'];
            $descvocecomm = $vocid['descr'];
            $idprofitcomm = $vocid['idprofit'];
            $oreprevcomm = $vocid['oreprev'];
            $prezzocliente = $vocid['prezzocliente'];
            /* profit center */
            $sql = $db->prepare("SELECT nome FROM profit_center WHERE id = ?");
            $sql->execute(array($idprofitcomm));
            $nomeprofitcomm = $sql->fetchColumn();

            /* costi di lavorazione */
            
            /* righe costi*/
            $righecosti = "";
            $valoricosti = getDati("commesse_costi", "WHERE idvocecomm = $idvocecomm");
            $costototalelav = 0;
            foreach ($valoricosti as $valoricostid) {
                $idcostocomm = $valoricostid['id'];
                $centrodicosto =  $valoricostid['centrodicosto'];
                $idcentrodicosto = $valoricostid['idcentrodicosto'];
                $fornitore = $valoricostid['fornitore'];
                $idfornitore = $valoricostid['idfornitore'];
                $descrizione = $valoricostid['descrizione'];
                $costo = $valoricostid['costo'];
                $costototalelav += $costo;
            $righecosti .= "<div id=\"rigariepilogocosti_$idcostocomm\" class=\"dettagliocosti sizing\">"
                    . "<input type=\"text\" disabled class=\"cercacdc nopost input_moduli sizing float_moduli_small_10\" value=\"$centrodicosto\" name=\"rigacentrodicosto_$idcostocomm\" id=\"rigacentrodicosto_$idcostocomm\" title=\"Centro di costo\" placeholder=\"Centro di costo\" /><input type=\"hidden\" value=\"$idcentrodicosto\" name=\"rigaidcentrodicosto_$idcostocomm\" id=\"rigaidcentrodicosto_$idcostocomm\" class=\"nopost\" />"
                    . "<input type=\"text\" disabled class=\"cercafornitore nopost input_moduli sizing float_moduli_40\" value=\"$fornitore\" name=\"rigafornitore_$idcostocomm\" id=\"rigafornitore_$idcostocomm\" title=\"Fornitore\" placeholder=\"Fornitore\" /><input type=\"hidden\" value=\"$idfornitore\" name=\"rigaidfornitore_$idcostocomm\" id=\"rigaidfornitore_$idcostocomm\" class=\"nopost\" />"
                    . "<input type=\"text\" disabled class=\"nopost input_moduli sizing float_moduli_40\" value=\"$descrizione\" name=\"rigadescrizione_$idcostocomm\" id=\"rigadescrizione_$idcostocomm\" title=\"Descrizione lavoro\" placeholder=\"Descrizione lavoro\" />"
                    . "<input type=\"text\" disabled class=\"nopost input_moduli sizing float_moduli_small_10\" value=\"$costo\" name=\"rigacosto_$idcostocomm\" id=\"rigacosto_$idcostocomm\" title=\"Costo\" placeholder=\"Costo\" />"
                    . "<div id=\"comandi_$idcostocomm\" style=\"float: left;\"><a href=\"javascript:;\" class=\"addbutton\" onclick=\"modificacosto('$idcostocomm', '$idvocecomm', '$costo');\"><i class=\"fa fa-pencil fa-lg\" aria-hidden=\"true\" style=\"color: #000000; line-height: 35px;\"></i></a> <a href=\"javascript:;\" class=\"addbutton\" onclick=\"eliminacosto('$idcostocomm', '$idvocecomm');\"><i class=\"fa fa-times fa-lg\" aria-hidden=\"true\" style=\"color: #cd0a0a; line-height: 35px;\"></i></a></div>
                    <div class=\"chiudi\"></div>
                    </div>";
            }
            
            /**/

            $riepilogocosti .= "<div class=\"rigadettorecomm\">
            <div class=\"profitcomm nobolder sizing\">" . (strlen($nomeprofitcomm) > 0 ? $nomeprofitcomm : "&nbsp;" ) . "</div>
            <div class=\"nomevocecomm nobolder sizing\">" . (strlen($nomevocecomm) > 0 ? $nomevocecomm : "&nbsp;" ) . "</div>
            <div class=\"desccdc nobolder sizing\">" . (strlen($descvocecomm) > 0 ? $descvocecomm : "&nbsp;" ) . "</div>
               <div class=\"orelavoratecomm nobolder sizing\"><span id=\"costitotalilav_$idvocecomm\">" . number_format($costototalelav, 2, ',', '.') . "</span> &euro;</div> 
            <div class=\"comandilavoratecomm nobolder sizing\"><a href=\"javascript:;\" onclick=\"javascript:$('#riepilogocosti_$idvocecomm').slideToggle();\"><i class=\"fa fa-info-circle fa-lg\" aria-hidden=\"true\" style=\"color: #000000;\"></i></a></div>
            <div class=\"chiudi\"></div>
            </div>
            <div id=\"riepilogocosti_$idvocecomm\" class=\"dettaglioorelav sizing\">"
                    . "<div id=\"formadd_$idvocecomm\">"
                    . "<input type=\"text\" class=\"cercacdc nopost input_moduli sizing float_moduli_small_10\" name=\"centrodicosto_$idvocecomm\" id=\"centrodicosto_$idvocecomm\" title=\"Centro di costo\" placeholder=\"Centro di costo\" /><input type=\"hidden\" name=\"idcentrodicosto_$idvocecomm\" id=\"idcentrodicosto_$idvocecomm\" class=\"nopost\" />"
                    . "<input type=\"text\" class=\"cercafornitore nopost input_moduli sizing float_moduli_40\" name=\"fornitore_$idvocecomm\" id=\"fornitore_$idvocecomm\" title=\"Fornitore\" placeholder=\"Fornitore\" /><input type=\"hidden\" name=\"idfornitore_$idvocecomm\" id=\"idfornitore_$idvocecomm\" class=\"nopost\" />"
                    . "<input type=\"text\" class=\"nopost input_moduli sizing float_moduli_40\" name=\"descrizione_$idvocecomm\" id=\"descrizione_$idvocecomm\" title=\"Descrizione lavoro\" placeholder=\"Descrizione lavoro\" />"
                    . "<input type=\"text\" class=\"nopost input_moduli sizing float_moduli_small_10\" name=\"costo_$idvocecomm\" id=\"costo_$idvocecomm\" title=\"Costo\" placeholder=\"Costo\" />"
                    . "<a href=\"javascript:;\" class=\"addbutton\" onclick=\"aggiungicosto('$idvocecomm');\"><i class=\"fa fa-plus fa-lg\" aria-hidden=\"true\" style=\"color: #0a0; line-height: 35px;\"></i></a>
                    <div class=\"chiudi\"></div>
                    </div>
                    $righecosti
                    </div>";

            /**/




            /* ore lavorate */
            $sql = $db->prepare("SELECT orelavorate, DATE_FORMAT(dataoredettaglio, '%d/%m/%Y') as data, nomecognomeutente, descr FROM ore_voci WHERE idvocecomm = ? ORDER BY data");
            $orelavorate = array();
            $sql->execute(array($idvocecomm));
            $res = $sql->fetchAll();
            $dettaglioorelavorate = "";
            foreach ($res as $row) {
                $orelavorate[] = $row['orelavorate'];
                /* dettaglio ore lavorata */
                $datalavoro = $row['data'];
                $nomeutente = $row['nomecognomeutente'];
                $orelavorateutente = $row['orelavorate'];
                $descrizioneorelavoro = $row['descr'];
                $dettaglioorelavorate .= "<div class=\"datadettcomm sizing\">$datalavoro</div>"
                        . "<div class=\"nomedettcomm sizing\">$nomeutente</div>"
                        . "<div class=\"descdettcomm sizing\">$descrizioneorelavoro</div>"
                        . "<div class=\"oredettcomm sizing\">$orelavorateutente</div>"
                        . "<div class=\"chiudi\"></div>";
            }

            if ($orelavorate) {
                $orelavoratecomm = sommaorelavorate($orelavorate);
                if ($orelavoratecomm > $oreprevcomm) {
                    $colore = "red";
                } else {
                    $colore = "black";
                }

                /* totale ore lavorate utente */
                $sql0 = $db->prepare("SELECT idutente, nomecognomeutente FROM ore_voci WHERE idvocecomm = ? GROUP BY idutente");
                $sql0->execute(array($idvocecomm));
                $res0 = $sql0->fetchAll();
                $riepilogototaleoreutente = array();
                $costototaleutenti = 0;
                foreach ($res0 as $row0) {
                    $idutentecomm = $row0['idutente'];
                    $nomecognome = $row0['nomecognomeutente'];
                    $sql1 = $db->prepare("SELECT orelavorate, costo FROM ore_voci WHERE idvocecomm = ? AND idutente = ?");
                    $sql1->execute(array($idvocecomm, $idutentecomm));
                    $res1 = $sql1->fetchAll();
                    $orelavorateutente = array();
                    $costototutente = 0;
                    foreach ($res1 as $row1) {
                        $orelavorateutente[] = $row1['orelavorate'];
                        $costototutente += $row1['costo'];
                        $costototaleutenti += $row1['costo'];
                        $costototaleoredautenti += $row1['costo'];
                    }
                    $riepilogototaleoreutente[] = "<div class=\"nomedettcomm riepilogoutente sizing\">$nomecognome</div><div class=\"oredettcomm riepilogoutente sizing\">" . sommaorelavorate($orelavorateutente) . "</div><div class=\"oredettcomm riepilogoutente sizing\">" . number_format($costototutente, 2, ',', '.') . " &euro;</div><div class=\"chiudi\"></div>";
                }
                /**/



                $riepilogoore .= "<div class=\"rigadettorecomm\">
            <div class=\"profitcomm nobolder sizing\">" . (strlen($nomeprofitcomm) > 0 ? $nomeprofitcomm : "&nbsp;" ) . "</div>
            <div class=\"nomevocecomm nobolder sizing\">" . (strlen($nomevocecomm) > 0 ? $nomevocecomm : "&nbsp;" ) . "</div>
            <div class=\"descvocecomm nobolder sizing\">" . (strlen($descvocecomm) > 0 ? $descvocecomm : "&nbsp;" ) . "</div>
            <div class=\"orelavoratecomm nobolder sizing\">" . (strlen($orelavoratecomm) > 0 ? "<span style=\"color:$colore;\" class=\"orelavt\">" . $orelavoratecomm . "</span>" : "&nbsp;" ) . "</div>
            <div class=\"orelavoratecomm nobolder sizing\">" . (strlen($oreprevcomm) > 0 ? $oreprevcomm : "&nbsp;" ) . "</div>
               <div class=\"orelavoratecomm nobolder sizing\">" . number_format($costototaleutenti, 2, ',', '.') . " &euro;</div> 
            <div class=\"comandilavoratecomm nobolder sizing\"><a href=\"javascript:;\" onclick=\"javascript:$('#orecomm_$idvocecomm').slideToggle();\"><i class=\"fa fa-info-circle fa-lg\" aria-hidden=\"true\" style=\"color: #000000;\"></i></a></div>
            <div class=\"chiudi\"></div>
            </div>
            <div id=\"orecomm_$idvocecomm\" class=\"dettaglioorelav sizing\"><strong>TOTALI ORE LAVORATE / COSTO PER UTENTE</strong><div class=\"chiudi\" style=\"height: 5px;\"></div>" . join($riepilogototaleoreutente) . "<div class=\"chiudi\" style=\"height: 15px;\"></div><strong>DETTAGLIO ORE LAVORATE</strong><div class=\"chiudi\" style=\"height: 5px;\"></div>$dettaglioorelavorate</div>";
                $dettaglioore .= "<div id=\"orecomm_$idvocecomm\" class=\"dettaglioorelav sizing\"><strong>TOTALI ORE LAVORATE / COSTO PER UTENTE</strong><div class=\"chiudi\" style=\"height: 5px;\"></div>" . join($riepilogototaleoreutente) . "<div class=\"chiudi\" style=\"height: 15px;\"></div><strong>DETTAGLIO ORE LAVORATE</strong><div class=\"chiudi\" style=\"height: 5px;\"></div>$dettaglioorelavorate</div>";
            } else {
                $riepilogoore .= "";
                $costototaleutenti = 0;
                $dettaglioore = "<div id=\"orecomm_$idvocecomm\" class=\"dettaglioorelav sizing\">Non ci sono ore inserite per questa lavorazione</div>";
            }
            if ($prezzocliente-($costototaleutenti+$costototalelav) >= 0 ) {
                $colorern = "";
            } else {
                $colorern = "color: red";
            }
            
            $riepilogocostitotali .= "<div class=\"rigadettorecomm\">
            <div class=\"profitcomm nobolder sizing\">" . (strlen($nomeprofitcomm) > 0 ? $nomeprofitcomm : "&nbsp;" ) . "</div>
            <div class=\"nomevocecomm0 nobolder sizing\">" . (strlen($nomevocecomm) > 0 ? $nomevocecomm : "&nbsp;" ) . "</div>
            <div class=\"nomevocecomm2 nobolder sizing\">" . (strlen($descvocecomm) > 0 ? $descvocecomm : "&nbsp;" ) . "</div>                    
            <div class=\"orelavoratecomm nobolder sizing\"><span id=\"costitotaliorelav_$idvocecomm\">" . ($costototaleutenti > 0 ? number_format($costototaleutenti, 2, ',', '.')." &euro; ($orelavoratecomm)" : "0,00 &euro;") . "</span></div>
            <div class=\"orelavoratecomm nobolder sizing\"><span id=\"costitotalilav_$idvocecomm\">" . number_format($costototalelav, 2, ',', '.') . "</span> &euro;</div>
            <div class=\"orelavoratecomm nobolder sizing\"><span id=\"costieorelav_$idvocecomm\">" . number_format($costototaleutenti+$costototalelav, 2, ',', '.') . "</span> &euro;</div>     
            <div class=\"orelavoratecomm nobolder sizing\"><span id=\"prezzoclientelav_$idvocecomm\">" . number_format($prezzocliente, 2, ',', '.') . "</span> &euro;</div>    
                <div class=\"orelavoratecomm nobolder sizing\"><span id=\"marginelav_$idvocecomm\" style=\"$colorern\">" . number_format($prezzocliente-($costototaleutenti+$costototalelav), 2, ',', '.') . "</span> &euro;</div>
            <div class=\"comandilavoratecomm nobolder sizing\"><a href=\"javascript:;\" onclick=\"javascript:$('#riepilogocosti_$idvocecomm').slideToggle();\"><i class=\"fa fa-info-circle fa-lg\" aria-hidden=\"true\" style=\"color: #000000;\"></i></a> <a href=\"javascript:;\" onclick=\"javascript:$('#orecomm_$idvocecomm').slideToggle();\"><i class=\"fa fa-clock-o fa-lg\" aria-hidden=\"true\" style=\"color: #000000;\"></i></a></div>
            <div class=\"chiudi\"></div>
            </div>
            <div id=\"riepilogocosti_$idvocecomm\" class=\"dettaglioorelav sizing\">"
                    . "<div id=\"formadd_$idvocecomm\">";
                    $riepilogocostitotali .="<input type=\"text\" class=\"cercacdc nopost input_moduli sizing float_moduli_small_10\" name=\"centrodicosto_$idvocecomm\" id=\"centrodicosto_$idvocecomm\" title=\"Centro di costo\" placeholder=\"Centro di costo\" /><input type=\"hidden\" name=\"idcentrodicosto_$idvocecomm\" id=\"idcentrodicosto_$idvocecomm\" class=\"nopost\" />"
                    . "<input type=\"text\" class=\"cercafornitore nopost input_moduli sizing float_moduli_40\" name=\"fornitore_$idvocecomm\" id=\"fornitore_$idvocecomm\" title=\"Fornitore\" placeholder=\"Fornitore\" /><input type=\"hidden\" name=\"idfornitore_$idvocecomm\" id=\"idfornitore_$idvocecomm\" class=\"nopost\" />"
                    . "<input type=\"text\" class=\"nopost input_moduli sizing float_moduli_40\" name=\"descrizione_$idvocecomm\" id=\"descrizione_$idvocecomm\" title=\"Descrizione lavoro\" placeholder=\"Descrizione lavoro\" />"
                    . "<input type=\"text\" class=\"nopost input_moduli sizing float_moduli_small_10\" name=\"costo_$idvocecomm\" id=\"costo_$idvocecomm\" title=\"Costo\" placeholder=\"Costo\" />"
                    . "<a href=\"javascript:;\" class=\"addbutton\" onclick=\"aggiungicosto('$idvocecomm');\"><i class=\"fa fa-plus fa-lg\" aria-hidden=\"true\" style=\"color: #0a0; line-height: 35px;\"></i></a>
                    <div class=\"chiudi\"></div>
                    </div>
                    $righecosti
                    </div>"
                    . "$dettaglioore";         
                    
                    $totaleinterni += $costototaleutenti;
                    $totaleesterni += $costototalelav;
        }


        /* elenco i profit center */
        $profit = getDati("profit_center", "WHERE idp='0'");
        foreach ($profit as $profitd) {
            $vociprofit .= "<option value=\"" . $profitd['id'] . "\">" . $profitd['nome'] . "</option>";
        }        
        
        /* magazzino */
$magazzino = getDati("magazzino", "");

        die('{"magazzino" :  ' . json_encode($magazzino) . ', "totaleinterni" : '.json_encode(number_format($totaleinterni, 2, '.', '')).', "totaleesterni" : '.json_encode(number_format($totaleesterni, 2, '.', '')).', "centridicosto" : ' . json_encode($centridicosto) . ', "fornitori" : ' . json_encode($fornitori) . ', "valori" : ' . json_encode($commesse) . ', "voci" : ' . json_encode($voci) . ', "vociprofit" : ' . json_encode($vociprofit) . ', "prevcoll" : ' . json_encode($prevcoll) . ', "ore": ' . json_encode($arrore) . ', "riepilogoore": ' . json_encode($riepilogoore) . ', "riepilogocostitotali": ' . json_encode($riepilogocostitotali) . ', "costototaleoredautenti": "' . json_encode($costototaleoredautenti) . '"}');
        break;

    case "delete":
        $tabella = "commesse";
        $id = $_POST['id'];
        $dati = new commesse($id, $tabella);
        $commesse = $dati->cancella();
        die('{"msg": "ok"}');
        break;

    case "mostracommesse":
        $tabella = "commesse";
        $id = $_POST['id'];
        $dati = new commesse($id, $tabella);
        $arch = $_POST['arch'];
        if ($arch == 1) {
            $where = "stato = '3' OR YEAR(data) != '" . DATE("Y") . "' ORDER BY numero DESC, data";
        } else {
            $where = "stato != '3' AND YEAR(data) = '" . DATE("Y") . "' ORDER BY numero DESC, data";
        }
        $commesse = $dati->richiamaWheredata($where);
        

        for ($i = 0; $i<count($commesse); $i++) {
            /* controllo commesse con costi */
            $sql = $db->prepare("SELECT * FROM commesse_costi WHERE idcomm = ? AND costo > 0");
            $sql->execute(array($commesse[$i]['id']));
            if ($sql->rowCount() >0) {
                $commesse[$i]['costifornitori'] = "1";
            } else {
                $commesse[$i]['costifornitori'] = "0";
            }
            /**/
            $margine = $commesse[$i]['totalecomm'] - $commesse[$i]['totalecosti'];
            if ($margine < 0) {
            $commesse[$i]['margine'] = "<span style=\"color: red;\">$margine</span>";
            } else {
                $commesse[$i]['margine'] = "$margine";
            }
        }
        die('{"dati" : ' . json_encode($commesse) . '}');
        break;

    case "submitformcommesse":
        $tabella = "commesse";
        $commesse = new commesse($id, $tabella);
        /* voci della commessa */
        for ($i = 0; $i < count($_POST['nome']); $i++) {
            if ($_POST['nome'][$i] != "" || $_POST['descr'][$i] != "") {
                $voci[$i] = array("nome" => $_POST['nome'][$i], "descr" => $_POST['descr'][$i], "idprofit" => $_POST['idprofit'][$i], "prezzocliente" => $_POST['prezzocliente'][$i], "costo" => $_POST['costo'][$i], "oreprev" => $_POST['oreprev'][$i], "statovoce" => $_POST['statovoce'][$i], "ordine" => $i);
            } else {
                
            }
        }
        array_filter($voci, 'strlen'); // controllo array vuoto

        /* preventivi collegati alla commessa */
        for ($i = 0; $i < count($_POST['idprevcomm']); $i++) {
            if ($_POST['idprevcomm'][$i] != "") {
                $prevcoll[$i] = array("idpreventivo" => $_POST['idprevcomm'][$i], "numeropreventivo" => $_POST['numeroprevcomm'][$i], "datapreventivo" => $_POST['dataprevcomm'][$i], "titolopreventivo" => $_POST['titoloprevcomm'][$i], "totalepreventivo" => $_POST['totaleprevcomm'][$i]);
            } else {
                
            }
        }
        array_filter($prevcoll, 'strlen'); // controllo array vuoto

        /* tolgo dall'array submit e post che non mi servono */
        unset($_POST['submit'], $_POST['nome'], $_POST['idlavcomm'], $_POST['descr'], $_POST['prezzocliente'], $_POST['costo'], $_POST['idprofit'], $_POST['idprevcomm'], $_POST['oreprev'], $_POST['numeroprevcomm'], $_POST['dataprevcomm'], $_POST['statovoce'], $_POST['titoloprevcomm'], $_POST['totaleprevcomm']);

        foreach ($_POST as $k => $v) {
            $campi[] = $k;
            $valori[] = $v;
        }
        /* funzione aggiungi */
        $commesse->aggiungi($campi, $valori, $voci, $prevcoll);

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
        <script type="text/javascript" src="./js/functions-commesse.js"></script>

        <link type="text/css" rel="stylesheet" href="./js/jsgrid-1.5.1/jsgrid.min.css" />
        <link type="text/css" rel="stylesheet" href="./js/jsgrid-1.5.1/jsgrid-theme.min.css" />

        <script type="text/javascript" src="./js/jsgrid-1.5.1/jsgrid.min.js"></script>
        <script type="text/javascript" src="./js/jsgrid-1.5.1/i18n/jsgrid-it.js"></script>
        <!-- lingua italiana datepicker -->
        <script src="./js/jquery-ui-1.11.4/datepicker-it.js"></script>
        <!-- timepicker-->
        <link rel='stylesheet' href='./js/jquery-timepicker-1.3.5/jquery.timepicker.min.css' />
        <script type="text/javascript" src="./js/jquery-timepicker-1.3.5/jquery.timepicker.min.js"></script>
        <script type="text/javascript">
            $(document).ready(function () {

<?php if ($op == "archivio") { ?>
                    mostraCommesse('1', '', '');
<?php } else { ?>
                    mostraCommesse('', '', '');
<?php } ?>
            });
        </script>

    </head>
    <body class="colormodulo">
        <?php include './components/top.php'; ?>
        <?php if (moduloattivo("preventivi") > 0) { ?>
            <div class="barra_submenu sizing">
                <li class="box_submenu sizing"><a href="commesse.php?op=commesse"><i class="fa fa-pie-chart fa-lg" aria-hidden="true"></i> Commesse</a></li>
                <li class="box_submenu sizing"><a href="commesse.php?op=archivio"><i class="fa fa-file-archive-o fa-lg" aria-hidden="true"></i> Commesse archiviate</a></li>

            </div>
            <div class="content sizing">
                <div class="barra_op sizing">
                    <div class="bottone sizing"><a href="javascript:;" onclick="aggiungi();"><i class="fa fa-user-plus fa-lg" aria-hidden="true"></i> Aggiungi commessa</a></div><div id="titcommsez" style="float: left; margin-left: 20px; font-weight: bolder;"></div>                            
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