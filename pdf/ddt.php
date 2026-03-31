<?php

ob_start('ob_gzhandler');
include '../library/controllo.php';
include '../library/config.php';
include '../library/connessione.php';
include '../library/functions.php';
include '../library/basic.class.php';
/* libreria del modulo */
include '../library/ddt.class.php';

/* iva default */
$iva = getDati("iva", "WHERE predefinito = '1'");
$ivapred = $iva[0]['valore'];

$ivapercalcoli = 100 + $ivapred;

/* dati preventivo e voci preventivo */
$tabella = "ddt";
$id = $_GET['idddt'];
$dati = new ddt($id, $tabella);
$where = "id = $id";
$ddt = $dati->richiamaWheredata($where);

foreach ($ddt as $ddtd) {
    $daticliente = str_replace("\n", "<br />", $ddtd['daticliente']);

    $data = $ddtd['datait'];
    $numero = $ddtd['numero'];
    $partenza = $ddtd['partenza'];
    $destinazione = str_replace("\n", "<br />", $ddtd['destinazione']);
    $causale = $ddtd['causale'];
    $aspetto = $ddtd['aspetto'];
    $vettore = $ddtd['vettore'];
    $porto = $ddtd['porto'];
    $colli = $ddtd['colli'];
    $peso = $ddtd['peso'];
}

/* voci */
$tabella = "ddt_voci";
$where = "idddt = $id ORDER BY ordine";
$dativoci = new ddt($id, $tabella);
$voci = $dativoci->richiamaWhere($where);


if ($voci) {
    $vociprint = "<table style=\"width: 100%; border: 1px solid #cccccc; margin-top: 10px;\" cellspacing=0>
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
    $vociprint .= "</tbody></table>";
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



$content .= "<page backtop=350px backbottom=100px>
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
                <tr><td style=\"width:80%;\">
		<span style=\"font-weight:bold; font-size: 14pt;\">DOCUMENTO DI TRASPORTO</span><br /><span style=\"font-size: 6pt;\">(D.P.R. n. 472 del 14/08/96)</span>
                </td>
                <td style=\"width:20%; text-align: right;\">
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
		<td style=\"width: 25%; border-right: 1px solid #cccccc; padding:5px; font-size: 8pt;\" valign=top>Numero Documento</td>
		<td style=\"width: 25%; padding:5px; font-size: 8pt; border-right: 1px solid #cccccc;\" valign=top>Data Documento</td>
                <td style=\"width: 25%; padding:5px; font-size: 8pt;border-right: 1px solid #cccccc;\" valign=top>Partenza (Data e Ora)</td>
                <td style=\"width: 25%; padding:5px; font-size: 8pt;\" valign=top>Destinazione</td>
		</tr>
		<tr>
		<td style=\"width: 25%; border-right: 1px solid #cccccc; padding:5px; text-align:right;\" valign=top><b>$numero</b></td>
		<td style=\"width: 25%; text-align:right; border-right: 1px solid #cccccc; padding:5px;\" valign=top><b>$data</b></td>
                <td style=\"width: 25%; text-align:right; border-right: 1px solid #cccccc; padding:5px;\" valign=top><b>$partenza</b></td>
                    <td style=\"width: 25%; text-align:right; padding:5px; font-size: 8pt;\" valign=top>$destinazione</td>
		</tr>
		</table>
                <table style=\"width:100%; margin-top:10px; border: 1px solid #cccccc;\" cellspacing=0 cellpadding=0>
		<tr>
		<td style=\"width: 16%; border-right: 1px solid #cccccc; padding:5px; font-size: 8pt;\" valign=top>Causale</td>
		<td style=\"width: 16%; padding:5px; font-size: 8pt; border-right: 1px solid #cccccc;\" valign=top>Aspetto</td>
                <td style=\"width: 16%; padding:5px; font-size: 8pt;border-right: 1px solid #cccccc;\" valign=top>Vettore</td>
                <td style=\"width: 16%; padding:5px; font-size: 8pt;border-right: 1px solid #cccccc;\" valign=top>Porto</td>
                <td style=\"width: 16%; padding:5px; font-size: 8pt;border-right: 1px solid #cccccc;\" valign=top>Colli</td>
                <td style=\"width: 20%; padding:5px; font-size: 8pt;\" valign=top>Peso</td>
		</tr>
		<tr>
		<td style=\"width: 16%; border-right: 1px solid #cccccc; padding:5px; text-align:right;\" valign=top><b>$causale</b></td>
		<td style=\"width: 16%; text-align:right; border-right: 1px solid #cccccc; padding:5px;\" valign=top><b>$aspetto</b></td>
                <td style=\"width: 16%; text-align:right; border-right: 1px solid #cccccc; padding:5px;\" valign=top><b>$vettore</b></td>
                    <td style=\"width: 16%; text-align:right; border-right: 1px solid #cccccc; padding:5px;\" valign=top><b>$porto</b></td>
                        <td style=\"width: 16%; text-align:right; border-right: 1px solid #cccccc; padding:5px;\" valign=top><b>$colli</b></td>
                            <td style=\"width: 20%; text-align:right; padding:5px;\" valign=top><b>peso</b></td>
                    
		</tr>
		</table>
		</page_header>";
$content .= "<page_footer>
    <table style=\"width:100%; margin-top:10px; border: 1px solid #cccccc;\" cellspacing=0 cellpadding=0>
		<tr>
		<td style=\"width: 33.333%; border-right: 1px solid #cccccc; padding:5px; font-size: 8pt;\" valign=top>Firma Vettore</td>
		<td style=\"width: 33.333%; padding:5px; font-size: 8pt; border-right: 1px solid #cccccc;\" valign=top>Firme Conducente</td>
                <td style=\"width: 33.333%; padding:5px; font-size: 8pt;\" valign=top>Firma Destinatario</td>
		</tr>
		<tr>
		<td style=\"width: 33.333%; border-right: 1px solid #cccccc; padding:5px; text-align:right;\" valign=top>&nbsp;<br />&nbsp;</td>
		<td style=\"width: 33.333%; text-align:right; border-right: 1px solid #cccccc; padding:5px;\" valign=top>&nbsp;<br />&nbsp;</td>
                <td style=\"width: 33.333%; text-align:right; padding:5px;\" valign=top>&nbsp;<br />&nbsp;</td>
		</tr>
		</table>
    </page_footer></page>";



$content .= "$vociprint";


require_once('../library/html2pdf/html2pdf.class.php');

$html2pdf = new HTML2PDF('P', 'A4', 'it');
$html2pdf->WriteHTML($content);
$html2pdf->Output('ddt_' . $numero . '_' . $data . '_' . pulisciImmagine($firma) . '.pdf');
?>
