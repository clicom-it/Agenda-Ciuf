<?php

ob_start('ob_gzhandler');
include '../library/controllo.php';
include '../library/config.php';
include '../library/connessione.php';
include '../library/functions.php';
include '../library/basic.class.php';
/* libreria del modulo */
include '../library/preventivi.class.php';

/* iva default */
$iva = getDati("iva", "WHERE predefinito = '1'");
$ivapred = $iva[0]['valore'];

$ivapercalcoli = 100 + $ivapred;

/* dati preventivo e voci preventivo */
$tabella = "preventivi";
$id = $_GET['idprev'];
$dati = new preventivi($id, $tabella);
$where = "id = $id";
$preventivo = $dati->richiamaWheredata($where);

foreach ($preventivo as $preventivod) {
    $daticliente = str_replace("\n", "<br />", $preventivod['daticliente']);

    $arrdaticli = explode("<br />", $daticliente);
    $firma = $arrdaticli[0];

    $data = $preventivod['datait'];
    $dataprev = $preventivod['data'];
    $numero = $preventivod['numero'];
    $rev = $preventivod['rev'];
    $note = $preventivod['note'];
    $titolo = $preventivod['titolo'];
    $titolo1 = $preventivod['titolo1'];
    $titolo2 = $preventivod['titolo2'];
    $descrizione = $preventivod['descrizione'];
    $descrizione1 = $preventivod['descrizione1'];
    $descrizione2 = $preventivod['descrizione2'];

    $noteprev = str_replace("\n", "<br />", $preventivod['noteprezzieprev']);

    $totaleprev = $preventivod['totaleprev'];
    $scontoprev = $preventivod['scontoprev'];
    $totalescontatoprev = $preventivod['totalescontatoprev'];

    $totaleiva = $totalescontatoprev * $ivapercalcoli / 100;

    $tempiconsegna = $preventivod['tempi_consegna'];
    $pagamento = $preventivod['pagamento'];
    $firmaaccettazione = $preventivod['accettazionepreventivo'];
    $mostraiva = $preventivod['mostraiva'];
}

if (strlen($rev) > 0) {
    $revprint = "- Rev. $rev";
}


/* voci */
$tabella = "preventivi_voci";
$where = "idprev = $id ORDER BY ordine";
$dativoci = new preventivi($id, $tabella);
$voci = $dativoci->richiamaWhere($where);

if ($voci) {
    $vociprint = "<page pageset=\"old\"><table style=\"width: 100%; margin-top: 20px;\" cellspacing=0>
    <tr>
    <td style=\"width: 100%; padding: 5px; font-weight:bold; font-size: 12pt; font-style: italic;\">Dettagli offerta</td>
    </tr></table><table style=\"width: 100%; border: 1px solid #cccccc; margin-top: 10px;\" cellspacing=0>
    <thead>
                <tr style=\"background-color: #cccccc;\">
                <th style=\"text-align: left; width: 14%; font-size: 8pt; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; padding:5px;\"><b>Codice</b></th>
                <th style=\"text-align: left; width: 54%; font-size: 8pt; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; padding:5px;\"><b>Descrizione</b></th>
                <th style=\"text-align: left; width: 5%; font-size: 8pt; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; padding:5px;\"><b>Qta</b></th>
                <th style=\"text-align: left; width: 10%; font-size: 8pt; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; padding:5px;\"><b>Prezzo</b></th>
                <th style=\"text-align: left; width: 7%; font-size: 8pt; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; padding:5px;\"><b>Sconto</b></th>
                <th style=\"text-align: left; width: 10%; font-size: 8pt; border-bottom: 1px solid #ffffff; padding:5px;\"><b>Totale</b></th>
                </tr></thead><tbody>";

    foreach ($voci as $vocid) {
        $nomevoce = $vocid['nome'];
        $descrizionevoce = str_replace("\n", "<br />", $vocid['descr']);
        $qtavoce = $vocid['qta'];
        $prezzovoce = $vocid['prezzo'];
        $scontovoce = $vocid['sconto'];
        $scontatovoce = $vocid['scontato'];



        $vociprint .= "<tr>
            <td valign=top style=\"text-align: left; width: 14%; height: 15px; font-size: 8pt; padding:5px; border-right: 1px solid #cccccc;\">$nomevoce</td>
            <td valign=top style=\"text-align: left; width: 54%; font-size: 8pt; padding:5px; border-right: 1px solid #cccccc;\">$descrizionevoce</td>
            <td valign=top style=\"text-align: left; width: 5%; font-size: 8pt; padding:5px; border-right: 1px solid #cccccc;\">$qtavoce</td>
            <td valign=top style=\"text-align: left; width: 10%; font-size: 8pt; padding:5px; border-right: 1px solid #cccccc;\">" . number_format($prezzovoce, 2, ',', '.') . "</td>
            <td valign=top style=\"text-align: left; width: 7%; font-size: 8pt; padding:5px; border-right: 1px solid #cccccc;\">" . number_format($scontovoce, 2, ',', '.') . "%</td>
            <td valign=top style=\"text-align: left; width: 10%; font-size: 8pt; padding:5px;\">" . number_format($scontatovoce, 2, ',', '.') . "</td>
            </tr>";
    }
    $vociprint .= "</tbody></table></page>";
}

/* dati gestionale */
$tabella = "dati_gestionale";
$dati = getDati($tabella, "LIMIT 1");
foreach ($dati as $datid) {
    $logo = $datid['logo'];
    $ragionesociale = $datid['ragione_sociale'];
    $sedelegale = $datid['sede_legale'];
    $sedeoperativa1 = $datid['sede_operativa1'];
    $sedeoperativa2 = $datid['sede_operativa2'];
    $piva = "P.iva: " . $datid['piva'];
    $cf = "C.F.: " . $datid['cf'];
    $rea = "Rea: " . $datid['rea'];
    $tel = "Tel.: " . $datid['tel'];
    $fax = "Fax: " . $datid['fax'];
    $sito = $datid['sito'];
    $email = $datid['email'];
}
if (strlen($sedeoperativa1) > 0) {
    $sedeop1 = "Sede op.: $sedeoperativa1<br />";
}
if (strlen($sedeoperativa2) > 0) {
    $sedeop2 = "Sede op.2: $sedeoperativa2<br />";
}

$content = "<page backtop=\"0mm\" backbottom=\"0mm\" backleft=\"0mm\" backright=\"0mm\"><table style=\"width:100%; height: 99%; background-image:url(../immagini/mondo2.png); background-repeat: no-repeat; background-position: right; background-size: cover;\">"
        . "<tr><td style=\"width: 100%; height: 99%;\">"
        . "<img src=\"../immagini/logo/$logo\" alt=\"logo\" style=\"height: 60px;\"><br /><br /><br />"
        . "<span style=\"font-weight:bold; font-size: 16pt;\">$titolo</span>"
        . "</td></tr>"
        . "</table>"
        . "</page>";

$content .= "<page backtop=320px backbottom=100px>
		<page_header>
		<table style=\"width:100%; font-size: 8pt;\">
		<tr><td style=\"width:50%;\" valign=\"top\">
		<img src=\"../immagini/logo/$logo\" alt=\"logo\" style=\"width:50%;\"><br /><br />
		<b>$ragionesociale</b><br />
		$sedelegale<br />
                $piva - $cf<br />
                $rea<br />
                $sedeop1
                $sedeop2
		$tel&nbsp;&nbsp;&nbsp;$fax<br />
		$sito&nbsp;&nbsp;&nbsp;$email
		</td>
		<td style=\"width:40%;\" align=right>
		<table border=0 style=\"width:295px;\" cellspacing=0 cellpadding=0>
		<tr><td align=left style=\"width:285px;\">
                <table border=0 style=\"width:100%;\" cellspacing=0 cellpadding=0>
                <tr><td style=\"width:50%;\">
		<span style=\"font-weight:bold; font-size: 14pt;\"><span style=\"font-size: 6pt;\">Oggetto:</span> PREVENTIVO</span>
                </td>
                <td style=\"width:50%; text-align: right;\">
		<span style=\"font-size: 8pt;\">pagina [[page_cu]]/[[page_nb]]</span>
                </td>
                </tr>
                </table>
		</td></tr>
		<tr>
                <td align=left style=\" height:90px; width: 350px; padding:5px; font-size: 8pt;\" valign=\"top\">
                <b>Spett.le:</b><br /><br />
		$daticliente
		</td>
                </tr>
		</table>
		</td>
		</tr>
		</table>                
		<table style=\"width:100%; margin-top:10px; border: 1px solid #cccccc;\" cellspacing=0 cellpadding=0>
		<tr>
		<td style=\"width: 15%; border-right: 1px solid #cccccc; padding:5px; font-size: 8pt;\" valign=top>Numero Offerta</td>
		<td style=\"width: 15%; padding:5px; font-size: 8pt; border-right: 1px solid #cccccc;\" valign=top>Data Offerta</td>
                <td style=\"width: 70%; padding:5px; font-size: 8pt;\" valign=top>Info e note</td>
		</tr>
		<tr>
		<td style=\"width: 15%; border-right: 1px solid #cccccc; padding:5px; text-align:right;\" valign=top><b>$numero $revprint</b></td>
		<td style=\"width: 15%; text-align:right; border-right: 1px solid #cccccc; padding:5px;\" valign=top><b>$data</b></td>
                <td style=\"width: 70%; text-align:left; padding:5px; font-size: 8pt;\" valign=top>
                <table border: 0; style=\"width: 100%;\">
                <tr>
                <td style=\"width: 5%;\">&nbsp;</td>
                <td style=\"width: 95%;\">$note</td>
                </tr>
                </table>
                </td>
		</tr>
		</table>
                
                <table style=\"margin-top:10px; width: 100%; border: solid 1px #cccccc; background-color: #E9E9E9;\" cellspacing=0 cellpadding=0>
                <tr>
                <td style=\"width: 100%; padding:5px; font-size: 8pt;\" align=top>Oggetto dell'offerta:</td>
                </tr>
                <tr>
                <td style=\"padding:5px; text-align:left;\"><b>$titolo</b></td>
                </tr>
                </table>
		</page_header>";
$content .= "<page_footer>
    <table style=\"width:100%; cellspacing=0 cellpadding=0>
    <tr>
    <td style=\"width: 50%; height: 50px; font-size: 8pt;\" valign=top>
    <img src=\"../immagini/logo/$logo\" alt=\"logo\" style=\"height: 50px;\">
    </td>
    <td style=\"width: 50%; height: 50px; font-size: 8pt; text-align: right; color: #696969;\" valign=top>
    <b>$ragionesociale</b><br />
		$sedelegale<br />
                $piva - $cf<br />
                $sedeop1
                $sedeop2
		$tel&nbsp;&nbsp;&nbsp;$fax<br />
		$sito&nbsp;&nbsp;&nbsp;$email
    </td>
    </tr>
    </table>
    </page_footer></page>";

if (strlen($descrizione) > 0) {
    $descrizioneprint = "<strong style=\"font-size: 18px;\"><i>Dettaglio del progetto</i></strong><br /><br /> $descrizione";
}

if (strlen($titolo1) > 0) {
    $descrizione1print = "<br /><br /><strong><i>$titolo1</i></strong><br /><br /> $descrizione1";
}

if (strlen($titolo2) > 0) {
    $descrizione2print = "<br /><br /><strong><i>$titolo2</i></strong><br /><br />$descrizione2";
}

if (strlen($noteprev) > 0) {

    $noteprint = "<table style=\"width: 100%; margin-top: 20px;\" cellspacing=0>
    <tr>
    <td style=\"width: 100%; font-style: italic; padding: 5px; font-weight:bold; font-size: 12pt;\">Note</td>
    </tr>
    </table>
    <table style=\"width: 100%; border: 1px solid #cccccc; padding: 5px; border-top: 0px;\" cellspacing=0>
    <tr>
    <td style=\"width: 100%;\">
    $noteprev
    </td>
    </tr>
    </table>";
}

$content .= "$descrizioneprint $descrizione1print $descrizione2print $vociprint";

if ($scontoprev > 0) {
    $ulteriore_print = "<tr>
    <td style=\"width: 85%; text-align: right; font-size: 12pt;\">Ulteriore sconto:</td>
    <td style=\"width: 15%; text-align: right; font-size: 12pt; font-weight: bold;\">" . number_format($scontoprev, 2, ',', '.') . " %</td>
    </tr><tr>
    <td style=\"width: 85%; text-align: right; font-size: 12pt;\">Totale scontato:</td>
    <td style=\"width: 15%; text-align: right; font-size: 12pt; font-weight: bold;\">" . number_format($totalescontatoprev, 2, ',', '.') . " &euro;</td>
    </tr>";
}
//
/* riepilogo prezzi */
$content .= "<page pageset=\"old\"><table style=\"width: 100%; margin-top: 20px;\" cellspacing=0>
    <tr>
    <td style=\"width: 100%; padding: 5px; font-weight:bold; font-size: 12pt; font-style: italic;\">Totali offerta</td>
    </tr>
    </table>
    <table style=\"width: 100%; border: 1px solid #cccccc; padding: 5px; border-top: 0px;\" cellspacing=0>
    <tr>
    <td style=\"width: 85%; text-align: right; font-size: 12pt;\">Totale:</td>
    <td style=\"width: 15%; text-align: right; font-size: 12pt; font-weight: bold;\">" . number_format($totaleprev, 2, ',', '.') . " &euro;</td>
    </tr>
    $ulteriore_print";
if ($mostraiva > 0) {
    $content .= "<tr><td style=\"width: 85%; text-align: right; font-size: 12pt;\">IVA:</td>
    <td style=\"width: 15%; text-align: right; font-size: 12pt; font-weight: bold;\">$ivapred %</td>
    </tr>
    <tr>
    <td style=\"width: 85%; text-align: right; font-size: 12pt;\">Totale con iva:</td>
    <td style=\"width: 15%; text-align: right; font-size: 12pt; font-weight: bold;\"> " . number_format($totaleiva, 2, ',', '.') . " &euro;</td>
    </tr>";
}
$content .= " </table>";
/* condizione e tempi */
$content .= "<table style=\"width: 100%; margin-top: 20px;\" cellspacing=0>
    <tr>
    <td style=\"width: 100%; padding: 5px; font-weight:bold; font-size: 12pt; font-style: italic;\">Condizioni e tempi di consegna</td>
    </tr>
    </table>
    <table style=\"width: 100%; border: 1px solid #cccccc; padding: 5px; border-top: 0px;\" cellspacing=0>
    <tr>
    <td style=\"width: 20%; font-size: 10pt;\" valign=top>Pagamento:</td>
    <td style=\"width: 80%; font-size: 10pt; font-weight: bold;\" valign=top>$pagamento</td>
    </tr>
    <tr>
    <td style=\"width: 20%; font-size: 10pt;\" valign=top>Tempi di consegna:</td>
    <td style=\"width: 80%; font-size: 10pt; font-weight: bold;\" valign=top>$tempiconsegna</td>
    </tr>
    </table>
    $noteprint
    <table style=\"width:100%; margin-top: 70px;\">
    <tr><td style=\"width:50%;\">";
if ($firmaaccettazione > 0) {
    $content .= "Il cliente per accettazione:<br /><br /><br />
    <div style=\"height:50px; width:250px; border-bottom: 1px solid #000000;\"></div>
    <div style=\"width:250px; border-bottom: 1px solid #000000; margin-top:70px;\">Data</div>";
} else {
    $content .= "&nbsp;";
}
$content .= "</td><td style=\"width:50%; text-align:center;\">
    <b>$ragionesociale</b>
    </td>
    </tr>
    </table></page>";

require_once('../library/html2pdf/html2pdf.class.php');

$html2pdf = new HTML2PDF('P', 'A4', 'it');
$html2pdf->WriteHTML($content);
$html2pdf->Output('preventivo_' . $numero . '_' . $dataprev . '_' . pulisciImmagine($firma) . '.pdf');
?>
