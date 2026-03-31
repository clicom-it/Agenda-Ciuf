<?php

ob_start('ob_gzhandler');
include '../library/controllo.php';
include '../library/config.php';
include '../library/connessione.php';
include '../library/functions.php';
include '../library/basic.class.php';
/* libreria del modulo */
include '../library/calendario.class.php';
//ini_set('display_errors', 'On');
/* dati preventivo e voci preventivo */
$id = $_GET['idapp'];
$where = "WHERE id = $id";
$appuntamento = getDatiCal($where);

foreach ($appuntamento as $appuntamentod) {
    $dataappdb = $appuntamentod['data'];
    $dataapp = $appuntamentod['datait'];
    $orarioapp = $appuntamentod['orariodb'];
    $nome = $appuntamentod['nome'];
    $cognome = $appuntamentod['cognome'];
    $sesso = $appuntamentod['sesso'];
    $comune = $appuntamentod['comune'];
    $provincia = $appuntamentod['provincia'];
    $telefono = $appuntamentod['telefono'];
    $email = $appuntamentod['email'];

    $idatelier = $appuntamentod['idatelier'];

    $idutente = $appuntamentod['idutente'];

    $datamatrimonio = $appuntamentod['datamatrimonioit'];
    if ($datamatrimonio == "00/00/0000") {
        $datamatrimonio = "";
    }

    $provenienza = $appuntamentod['provenienza'];

    $acquistato = $appuntamentod['acquistato'];

    $nomenoacquisto = $appuntamentod['nomenoacquisto'];

    if ($acquistato > 0) {
        $acquistato = "SI";
        $printnoacquisto = "";
    } else if ($acquistato == '0') {
        $acquistato = "NO";
        $printnoacquisto = "<b>Motivo mancato acquisto:</b><span style=\"color: #ff0000;\"> $nomenoacquisto</span>";
    } else {
        $printnoacquisto = "";
    }

    $sartoria = $appuntamentod['sartoria'];

    if ($sartoria > 0) {
        $sartoria = "SI";
    } else {
        $sartoria = "NO";
    }
    $pagamenti_sart = getDati('sartoria_pagamenti', 'where idappuntamento='.$id.' limit 1;')[0];
    $caparra = $pagamenti_sart['caparra'];
    $pagcaparra = $pagamenti_sart['nomepagamentocaparra'];
    $datacaparra = formatDate($pagamenti_sart['datacap']);
    if ($datacaparra == "00/00/0000") {
        $datacaparra = "";
    }

    /* pagamenti */
    $pagamento1 = $pagamenti_sart['pag1'];
    $nomepagamento1 = $pagamenti_sart['nomepag1'];
    $datapag1 = formatDate($pagamenti_sart['datapag1']);
    if ($datapag1 == "00/00/0000") {
        $datapag1 = "";
    }

    $pagamento2 = $pagamenti_sart['pag2'];
    $nomepagamento2 = $pagamenti_sart['nomepag2'];
    $datapag2 = formatDate($pagamenti_sart['datapag2']);
    if ($datapag2 == "00/00/0000") {
        $datapag2 = "";
    }

    $pagamento3 = $pagamenti_sart['pag3'];
    $nomepagamento3 = $pagamenti_sart['nomepag3'];
    $datapag3 = formatDate($pagamenti_sart['datapag3']);
    if ($datapag3 == "00/00/0000") {
        $datapag3 = "";
    }
    /**/

    $saldo = $pagamenti_sart['saldo'];
    $dataentrocuisaldare = formatDate($pagamenti_sart['datasaldo']);
    if ($dataentrocuisaldare == "00/00/0000") {
        $dataentrocuisaldare = "";
    }

    $datasaldoeffettuato = formatDate($pagamenti_sart['dataeffettuatosaldo']);
    if ($datasaldoeffettuato == "00/00/0000") {
        $datasaldoeffettuato = "";
    }

    $pagsaldo = $pagamenti_sart['nomepagamentosaldo'];

    $totalespesa = $pagamenti_sart['totalespesa'];

    $tipoabito = $pagamenti_sart['nometipoabito'];
    $prezzoabito = $pagamenti_sart['prezzoabito'];

    $modelloabito = $pagamenti_sart['nomemodabito'];

    $note = $pagamenti_sart['note'];
}



$atelierbloccati = array(49,57,59,60,61,81,65,79,82,135,86,129,90,91,93,134,97,100,126,111,136);

if ($_SESSION["livello"] > 1 && in_array($idatelier, $atelierbloccati)) {
    die("Funzione disabilitata");
}

$daticliente = "$nome $cognome <br />$comune ($provincia)<br />$telefono - $email";

/* voci accessori */
$tabella = "accessori_appuntamento";
$where = "idappuntamento = $id";
$datiacc = new calendario($id, $tabella);
$vociacc = $datiacc->richiamaWhere($where);

/* voci sartoria */
$tabellas = "sartoria_appuntamento";
$wheres = "idappuntamento = $id";
$datisart = new calendario($id, $tabellas);
$vocisart = $datisart->richiamaWhere($wheres);

if ($vocisart) {
    $vocisartprint = "<div style=\"width: 100%; padding-top: 10px; font-weight: bolder; text-align: center;\"><b>SARTORIA</b></div><table style=\"width: 100%; border: 1px solid #cccccc; margin-top: 10px;\" cellspacing=0>
    <thead>
                <tr style=\"background-color: #cccccc;\">
                <th style=\"text-align: left; width: 33%; font-size: 8pt; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; padding:5px;\"><b>Nome riparazione</b></th>
                <th style=\"text-align: left; width: 33%; font-size: 8pt; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; padding:5px;\"><b>Prezzo</b></th>
                <th style=\"text-align: left; width: 34%; font-size: 8pt; border-bottom: 1px solid #ffffff; padding:5px;\"><b>Note</b></th>
                </tr></thead><tbody>";
    foreach ($vocisart as $vocisartd) {
        $nomesart = $vocisartd['nomesartoria'];
        $prezzosart = $vocisartd['prezzosartoria'];
        $notesart = $vocisartd['notesartoria'];
        $vocisartprint .= "<tr>
            <td valign=top style=\"text-align: left; width: 33%; font-size: 8pt; padding:5px; border-right: 1px solid #cccccc;\">$nomesart</td>
            <td valign=top style=\"text-align: left; width: 33%; font-size: 8pt; padding:5px; border-right: 1px solid #cccccc;\">" . number_format($prezzosart, 2, ',', '.') . " &euro;</td>
            <td valign=top style=\"text-align: left; width: 34%; font-size: 8pt; padding:5px;\">$notesart</td>
            </tr>";
    }
    $vocisartprint .= "</tbody></table>";
}

/* dati gestionale */
$tabella = "dati_gestionale";
$dati = getDati($tabella, "LIMIT 1");
foreach ($dati as $datid) {
    $logo = $datid['logo'];
}

$datiatelier = getDati("utenti", "WHERE id = $idatelier");
$nominativoatelier = $datiatelier[0]['nominativo'];
$ragionesocialeatelier = $datiatelier[0]['ragionesociale'];
$sedelegaleatelier = $datiatelier[0]['sedelegale'];
$indirizzoatelier = $datiatelier[0]['indirizzo'];
$capatelier = $datiatelier[0]['cap'];
$comuneatelier = $datiatelier[0]['comune'];
$provinciaatelier = $datiatelier[0]['provincia'];
$pivaatelier = $datiatelier[0]['piva'];
$cfatelier = $datiatelier[0]['codicefiscale'];
$telefonoatelier = $datiatelier[0]['telefono'];
$cellatelier = $datiatelier[0]['cellulare'];
$emailatelier = $datiatelier[0]['email'];
$datipagamento = $datiatelier[0]['datipagamento'];

if ($cfatelier) {
    $cfatelierprint = " - C.F. $cfatelier";
}


$diraff = $datiatelier[0]['diraff'];

if ($diraff == "d") {
    $dati = getDati("dati_gestionale", "");
    foreach ($dati as $datigest) {

        $ragionesocialefooter = $datigest['ragione_sociale'];
        $sedelegalefooter = $datigest['sede_legale'];
        $pivafooter = $datigest['piva'];
        $cffooter = $datigest['cf'];
        if ($cffooter) {
            $printcffooter = " - C.F. $cffooter";
        }
    }

    $footer = $ragionesocialefooter . " - " . $sedelegalefooter . " - P.iva" . $pivafooter . "" . $printcffooter;
} else {
    $footer = $ragionesocialeatelier . " - " . $sedelegaleatelier . " - P.iva" . $pivaatelier . "" . $cfatelierprint;
}

$datigestore = getDati("utenti", "WHERE id = $idutente");

$headerpage = "<page backtop=230px backbottom=50px>
		<page_header>
		<table style=\"width:100%; font-size: 8pt;\">
		<tr><td style=\"width:50%;\" valign=\"top\">
		<span style=\"font-size:20pt;\">VPSPOSA SRL</span><br /><br />
		<b>$nominativoatelier</b><br />
		$indirizzoatelier - $capatelier $comuneatelier ($provinciaatelier)<br />
                Tel.: $telefonoatelier $cellatelier<br />
                $emailatelier
		</td>
		<td style=\"width:40%;\" align=right>
		<table border=0 style=\"width:295px;\" cellspacing=0 cellpadding=0>
		<tr><td align=left style=\"width:285px;\">
                <table border=0 style=\"width:100%;\" cellspacing=0 cellpadding=0>
                <tr><td style=\"width:80%;\">
		<span style=\"font-weight:bold; font-size: 14pt;\">SCHEDA SARTORIA</span>
                </td>
                <td style=\"width:20%; text-align: right;\">
		<span style=\"font-size: 8pt;\">pagina [[page_cu]]/[[page_nb]]</span>
                </td>
                </tr>
                </table>
		</td></tr>
		<tr>
                <td align=left style=\" height:90px; width: 350px; padding:5px; font-size: 8pt;\" valign=\"top\">
                <b>Cliente:</b><br /><br />
		$daticliente
		</td>
                </tr>
		</table>
		</td>
		</tr>
		</table>
		</page_header>";
$footerpage .= "<page_footer>
    <table style=\"width:100%; margin-top:10px;\" cellspacing=0 cellpadding=0>
		<tr>
		<td style=\"width: 100%; border-top: 1px solid #cccccc; padding-top: 10px; font-size: 8pt; text-align: center;\">$footer</td>
		</tr>
		</table>
    </page_footer></page>";

$content .= $headerpage . $footerpage;

//$content .= "<div style=\"width: 100%; padding: 5px; border: 1px solid #cccccc; font-size: 8pt;\"><b>Note:</b> $note</div>";

$content .= "<div style=\"width: 100%; padding-top: 10px; font-weight: bolder; text-align: center;\"><b>DATI PAGAMENTO</b></div>";
/* totale spesa */
$content .= "<table style=\"width:100%; margin-top:10px; border: 2px solid #000000;\" cellspacing=0 cellpadding=0>
		<tr>
		<td style=\"width: 33%; border-right: 1px solid #cccccc; padding:3px; font-size: 8pt; background-color: #e1bdaf; border:1px solid #000000;\" valign=top><b>Totale spesa</b></td>
		<td style=\"width: 33%; border-right: 1px solid #cccccc; padding:3px; font-size: 8pt; border-right: 1px solid #cccccc;\" valign=top></td>
                <td style=\"width: 33%; border-right: 1px solid #cccccc; padding:3px; font-size: 8pt; background-color: #e1bdaf; border:1px solid #000000;\" valign=top><b>Data entro cui saldare</b></td>
                </tr> 
                <tr>
		<td style=\"width: 33%; border-right: 1px solid #cccccc; padding:3px; text-align:right; font-size: 8pt;\" valign=top><b>" . number_format($totalespesa, 2, ',', '.') . " &euro;</b></td>
		<td style=\"width: 33%; text-align:right; border-right: 1px solid #cccccc; padding:3px; font-size: 8pt;\" valign=top><b>&nbsp;</b></td>
                <td style=\"width: 34%; text-align:right; padding:3px; font-size: 8pt;\" valign=top><b>$dataentrocuisaldare</b></td> 
		</tr>
		</table>";
/**/
$content .= "<table style=\"width:100%; margin-top:10px; border: 1px solid #cccccc;\" cellspacing=0 cellpadding=0>
		<tr>
		<td style=\"width: 33%; border-right: 1px solid #cccccc; padding:3px; font-size: 8pt; background-color: #e1bdaf; border:1px solid #000000;\" valign=top><b>Caparra confirmatoria</b></td>
		<td style=\"width: 33%; border-right: 1px solid #cccccc; padding:3px; font-size: 8pt; border-right: 1px solid #cccccc;\" valign=top>Data pagamento caparra</td>
                <td style=\"width: 34%; padding:3px; font-size: 8pt;\" valign=top>Metodo pagamento caparra</td>
                </tr> 
                <tr>
		<td style=\"width: 33%; border-right: 1px solid #cccccc; padding:3px; text-align:right; font-size: 8pt;\" valign=top><b>" . number_format($caparra, 2, ',', '.') . " &euro;</b></td>
		<td style=\"width: 33%; text-align:right; border-right: 1px solid #cccccc; padding:3px; font-size: 8pt;\" valign=top><b>" . $datacaparra . "</b></td>
                <td style=\"width: 34%; text-align:right; padding:3px; font-size: 8pt;\" valign=top><b>$pagcaparra</b></td> 
		</tr>
		</table>";
$content .= "<table style=\"width:100%; margin-top:10px; border: 1px solid #cccccc;\" cellspacing=0 cellpadding=0>
		<tr>
		<td style=\"width: 33%; border-right: 1px solid #cccccc; padding:3px; font-size: 8pt; background-color: #e1bdaf; border:1px solid #000000;\" valign=top><b>Pagamento1</b></td>
		<td style=\"width: 33%; border-right: 1px solid #cccccc; padding:3px; font-size: 8pt; border-right: 1px solid #cccccc;\" valign=top>Data pagamento 1</td>
                <td style=\"width: 34%; padding:3px; font-size: 8pt;\" valign=top>Metodo pagamento 1</td>
                </tr> 
                <tr>
		<td style=\"width: 33%; border-right: 1px solid #cccccc; padding:3px; text-align:right; font-size: 8pt;\" valign=top><b>" . number_format($pagamento1, 2, ',', '.') . " &euro;</b></td>
		<td style=\"width: 33%; text-align:right; border-right: 1px solid #cccccc; padding:3px; font-size: 8pt;\" valign=top><b>" . $datapag1 . "</b></td>
                <td style=\"width: 34%; text-align:right; padding:3px; font-size: 8pt;\" valign=top><b>$nomepagamento1</b></td> 
		</tr>
		</table>";
$content .= "<table style=\"width:100%; margin-top:10px; border: 1px solid #cccccc;\" cellspacing=0 cellpadding=0>
		<tr>
		<td style=\"width: 33%; border-right: 1px solid #cccccc; padding:3px; font-size: 8pt; background-color: #e1bdaf; border:1px solid #000000;\" valign=top><b>Pagamento2</b></td>
		<td style=\"width: 33%; border-right: 1px solid #cccccc; padding:3px; font-size: 8pt; border-right: 1px solid #cccccc;\" valign=top>Data pagamento 2</td>
                <td style=\"width: 34%; padding:3px; font-size: 8pt;\" valign=top>Metodo pagamento 2</td>
                </tr> 
                <tr>
		<td style=\"width: 33%; border-right: 1px solid #cccccc; padding:3px; text-align:right; font-size: 8pt;\" valign=top><b>" . number_format($pagamento2, 2, ',', '.') . " &euro;</b></td>
		<td style=\"width: 33%; text-align:right; border-right: 1px solid #cccccc; padding:3px; font-size: 8pt;\" valign=top><b>" . $datapag2 . "</b></td>
                <td style=\"width: 34%; text-align:right; padding:3px; font-size: 8pt;\" valign=top><b>$nomepagamento2</b></td> 
		</tr>
		</table>";
$content .= "<table style=\"width:100%; margin-top:10px; border: 1px solid #cccccc;\" cellspacing=0 cellpadding=0>
		<tr>
		<td style=\"width: 33%; border-right: 1px solid #cccccc; padding:3px; font-size: 8pt; background-color: #e1bdaf; border:1px solid #000000;\" valign=top><b>Pagamento3</b></td>
		<td style=\"width: 33%; border-right: 1px solid #cccccc; padding:3px; font-size: 8pt; border-right: 1px solid #cccccc;\" valign=top>Data pagamento 3</td>
                <td style=\"width: 34%; padding:3px; font-size: 8pt;\" valign=top>Metodo pagamento 3</td>
                </tr> 
                <tr>
		<td style=\"width: 33%; border-right: 1px solid #cccccc; padding:3px; text-align:right; font-size: 8pt;\" valign=top><b>" . number_format($pagamento3, 2, ',', '.') . " &euro;</b></td>
		<td style=\"width: 33%; text-align:right; border-right: 1px solid #cccccc; padding:3px; font-size: 8pt;\" valign=top><b>" . $datapag3 . "</b></td>
                <td style=\"width: 34%; text-align:right; padding:3px; font-size: 8pt;\" valign=top><b>$nomepagamento3</b></td> 
		</tr>
		</table>";
$content .= "<table style=\"width:100%; margin-top:10px; border: 1px solid #cccccc;\" cellspacing=0 cellpadding=0>
		<tr>
		<td style=\"width: 33%; border-right: 1px solid #cccccc; padding:3px; font-size: 8pt; background-color: #e1bdaf; border:1px solid #000000;\" valign=top><b>Saldo</b></td>
		<td style=\"width: 33%; border-right: 1px solid #cccccc; padding:3px; font-size: 8pt; border-right: 1px solid #cccccc;\" valign=top>Data Saldo</td>
                <td style=\"width: 34%; padding:3px; font-size: 8pt;\" valign=top>Metodo pagamento saldo</td>
                </tr> 
                <tr>
		<td style=\"width: 33%; border-right: 1px solid #cccccc; padding:3px; text-align:right; font-size: 8pt;\" valign=top><b>" . number_format($saldo, 2, ',', '.') . " &euro;</b></td>
		<td style=\"width: 33%; text-align:right; border-right: 1px solid #cccccc; padding:3px; font-size: 8pt;\" valign=top><b>$datasaldoeffettuato</b></td>
                <td style=\"width: 34%; text-align:right; padding:3px; font-size: 8pt;\" valign=top><b>$pagsaldo</b></td> 
		</tr>
		</table>";

$content .= "$vociprint$vocisartprint";

$footerheaderercontratto = "
    <table style=\"width:100%; margin-top:10px;\" cellspacing=0 cellpadding=0>
		<tr>
		<td style=\"width: 100%; border-top: 1px solid #cccccc; padding-top: 10px; font-size: 9pt; text-align: center;\"><strong>$footer</strong></td>
		</tr>
		</table>
    ";

$content .= "<page backtop=60px backbottom=0px>"
        . "<page_header>$footerheaderercontratto</page_header>"
        . "<table style=\"width: 70%; font-size: 12px\"><tr><td><div style=\"width: 100%; text-align: center; font-size: 16pt\"><strong>Contratto Servizio Sartoria</strong></div>"
        . "<br /<br />"
        . "<br /><br />"
        . "Fra le sottoscritte Parti:  1) <b>V.P. Sposa Srl (P. IVA 08470851216) con sede in Napoli alla via Ferrante Imparato n. 198</b> (Fornitore) e 2) $nome $cognome (cellulare $telefono) (Cliente); 
<br /><br />
<p style=\"text-align:center;\"><b>Premesso che</b></p><br />
il Fornitore è azienda specializzata nella sartoria di abiti da sposa e cerimonia. il Cliente dichiara di accettare integralmente le seguenti condizioni generali che ha compreso appieno per avervi preso ampia visione:<br /><br />
<p style=\"text-align:center;font-weight:bold;font-size:20pt;\">CONDIZIONI GENERALI </p>
<p><b>Art. 1 - Premesse ed allegati.</b> Le premesse ed allegati sono da intendersi quale parte integrante, essenziale e sostanziale della presente scrittura privata; <b>Art. 2 - oggetto del contratto</b><b> 2.1 - </b>con la presente scrittura il Fornitore - a fronte dell&rsquo;integrale e contestuale versamento del corrispettivo pattuito - si obbliga ad eseguire sull&rsquo;abito consegnato dal Cliente gli interventi ed operazioni sartoriali concordati e riportati nella scheda sartoriale che sar&agrave; allegata al presente contratto.Gli obblighi e le responsabilit&agrave; del Fornitore verso il Cliente sono esclusivamente quelli definiti dal contratto.<b>2.2 - </b>Nella scheda sartoriale verranno descritte le operazioni da svolgere, il prezzo totale, le misure, modalit&agrave; di pagamento, data del matrimonio entro cui effettuare la consegna ed ogni altro elemento utile alla corretta esecuzione del servizio sartoria. Il Cliente vista scheda sartoriale ne accetta, sottoscrivendo per presa visione, l&rsquo;intero contenuto ivi compreso il corrispettivo. <b>2.3</b> - La scheda sartoriale, una volta redatta, sar&agrave; allegata al presente contratto e ne former&agrave; parte essenziale, integrante e sostanziale. In caso di pi&ugrave; abiti e/o accessori verr&agrave; stilata una &ldquo;scheda sartoriale&rdquo; per ognuno di essi. La mancata sottoscrizione della scheda sartoriale da parte del Cliente comporta l&rsquo;impossibilit&agrave; da parte del Fornitore di eseguire il servizio sartoria. <b>2.4</b> - Il Fornitore si obbliga ad adempiere alle sole obbligazioni individuate specificatamente nella &ldquo;scheda sartoriale&rdquo; nelle modalit&agrave; descritte ed accettate dal Cliente nella stessa. Ogni altra, diversa e/o connessa obbligazione non specificamente concordata(es. smacchiare e/o lavare abiti, nuova messa a misura, modifiche ulteriori ecc.) non sar&agrave; effettuata salvo diverso ed ulteriore accordo e saldo del relativo preventivo di spesa. <b>2.5</b> - Il Cliente &egrave; consapevole ed accetta che il Fornitore effettuer&agrave;, salvo diverso accordo scritto, le operazioni individuate nella scheda sartoriale solo dopo l&rsquo;integrale pagamento del corrispettivo pattuito e consegna dell&rsquo;abito da modificare. In caso di mancato o ritardato pagamento(o consegna dell&rsquo;abito) da parte del Cliente il Fornitore &egrave; manlevato da qualsiasi eventuale danno derivante da ritardata o mancata consegna dell&rsquo;abito da modificare. <b>2.6</b> - Nel caso in cui il corrispettivo sia stato integralmente versato il Cliente &egrave; informato ed accetta che laddove non provveda al ritiro della merce entro 30 giorni dalla data prevista per il ritiro della stessa sar&agrave; facolt&agrave; del Fornitore, senza obbligo di preavviso alcuno, trattenere e divenire proprietario della merce a titolo di penale fatta salva la facolt&agrave; per lo stesso di agire a tutela dei propri diritti in caso di maggior danno. Detta disposizione si applica anche in caso di mancato e/o parziale pagamento dei servizi richiesti dal Cliente. <b>2.7 </b>- Al momento della consegna dell&rsquo;abito da parte del Cliente o da Suo delegato il Fornitore &egrave; costituito esclusivo custode dello stesso fino a riconsegna avvenuta presso le sedi del Fornitore o presso Atelier convenzionati. <b>Art. 3 - Trattamento dei dati personali e privacy. </b>Il Fornitore dichiara di essere conforme al Reg. UE 679/2016 e al D.Lgs. 196/2003, di osservare le prescrizioni in essi contenute nonch&eacute; i provvedimenti e le indicazioni dell&rsquo;Autorit&agrave; Garante in materia di trattamento dei dati personali degli Interessati. Il Cliente, visionata l&rsquo;informativa privacy, autorizza il Fornitore al trattamento dei dati personali per le finalit&agrave; e per le causali dedotte e connesse all&rsquo;accordo contrattuale stipulato nonch&eacute; ai fini della corretta esecuzione dello stesso e, previo espresso consenso dell&rsquo;Interessato, per finalit&agrave; di marketing. Il Fornitore potr&agrave; trasferire detti dati a terzi Incaricati o Responsabili esterni al trattamento solo per gli adempimenti contabili ed amministrativi previsti dalla legge. <b>Art. 4 - Limitazione di Responsabilit&agrave;.</b>Gli obblighi e le responsabilit&agrave; del Fornitore verso il Cliente sono esclusivamente quelli definiti dal contratto, pertanto, in qualsiasi caso di violazione o inadempimento imputabile al Fornitore esso non risponde per un importo superiore a quello versato dal Cliente per il singolo servizio, ordinato interessato dall&#39;evento dannoso. Resta espressamente escluso, qualsiasi altro indennizzo o risarcimento al Cliente per danni diretti o indiretti di qualsiasi natura e specie. <b>Art. 5 - Legge applicabile e foro competente. </b>Questo accordo con i relativi allegati costituisce l&rsquo;intero accordo ed annulla ogni precedente accordo o qualunque altro contemporaneo accordo orale o scritto intervenuto tra le parti su questa materia ed &egrave; disciplinato dalla legge italiana. Per ogni controversia nascente dall&rsquo;interpretazione ed esecuzione dello stesso sar&agrave; esclusivamente competente il foro di Napoli salvo che il Cliente non sia inquadrabile come consumatore ai sensi del codice del consumo dovendo, in quest&rsquo;ultimo caso, ritenersi competente in via esclusiva il foro del Consumatore.</p>
<br /><br />
<table style=\"width:100%; margin-top:10px;\" cellspacing=0 cellpadding=0>
<tr>
<td style=\"width:50%;\">Letto, approvato e sottoscritto il  __________________________</td><td style=\"text-align:right;width:50%;\"><b>Il Cliente</b><br />__________________________</td></tr></table>
</td></tr></table><br />
<p>Le Parti dichiarano di approvare specificatamente, agli effetti degli artt. 1341, 1342 c.c., i seguenti articoli, <b>2.5</b>(inizio operazioni), <b>2.6</b>(Penale), <b>3</b> (Trattamento dei dati personali e Privacy), <b>4</b>(Limitazione di Responsabilità), <b>5</b> (Legge applicabile e foro competente).</p>
<br />
<table style=\"width:100%; margin-top:10px;\" cellspacing=0 cellpadding=0>
<tr>
<td style=\"width:50%;\">Data  __________________________</td><td style=\"text-align:right;width:50%;\"><b>Il Cliente</b><br />__________________________</td></tr></table>
<br /><br />
<page_footer>
$footerheaderercontratto</page_footer></page>";
require_once('../library/html2pdf/html2pdf.class.php');

$html2pdf = new HTML2PDF('P', 'A4', 'it');
$html2pdf->WriteHTML($content);
$html2pdf->Output('sartoria_' . $nome . '-' . $cognome . '_' . $numero . '_' . $dataappdb . '.pdf');
?>
