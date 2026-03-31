<?php

ob_start('ob_gzhandler');
include '../library/controllo.php';
include '../library/config.php';
include '../library/connessione.php';
include '../library/functions.php';
include '../library/basic.class.php';
/* libreria del modulo */
include '../library/fatture.class.php';

/* dati preventivo e voci preventivo */
$tabella = "fatture";
$id = $_GET['idfatt'];
$day = $_GET['day'];
$dati = new fatture($id, $tabella);
$where = "id = $id";
$fattura = $dati->richiamaWheredata($where);

foreach ($fattura as $fatturad) {
    $tipo = $fatturad['tipo'];
    if ($tipo == 0) {
        $tipofattura = "FATTURA VENDITA";
    } else if ($tipo == 1) {
        $tipofattura = "FATTURA ACQUISTO";
    } else if ($tipo == 2) {
        $tipofattura = "PROFORMA";
    } else if ($tipo == 3) {
        $tipofattura = "NOTA DI CREDITO";
    }
    $idcliente = $fatturad['idcliente'];
    $daticliente = str_replace("\n", "<br />", $fatturad['daticliente']);
    $arrdaticli = explode("<br />", $daticliente);
    $firma = $arrdaticli[0];

    $indirizzospedizionefattura = str_replace("\n", "<br />", $fatturad['datispedizione']);

    $data = $fatturad['datait'];
    $datafatt = $fatturad['data'];

    $arrdata = explode("-", $datafatt);

    $numero = $fatturad['numero'];

    $totalefatt = $fatturad['totalefatt'];
    $totalefattiva = $fatturad['totalefatt_iva'];

    $bancapagamento = $fatturad['coord_bancarie'];
    $bancaperriba = $fatturad['coordinate'];

    $notefatt = $fatturad['note'];
    if ($day > 0) {
        $metodopagamento = "Bonifico 120 Giorni";
        $fine_vista = "Fine mese";
    } else {
        $metodopagamento = $fatturad['metodopagamento'];

        $fm_vf = $fatturad['finemese_vista'];
        if ($fm_vf > 0) {
            $fine_vista = "Vista fattura";
        } else {
            $fine_vista = "Fine mese";
        }
    }
}

/* banca per riba */
if (strlen($bancaperriba) > 0 && $bancaperriba != 0) {
    $sql = $db->prepare("SELECT $bancaperriba FROM clienti_fornitori WHERE id = ?");
    $sql->execute(array($idcliente));
    $bancaperribaprint = "Conto per riba:<br />" . str_replace("\n", "<br />", $sql->fetchColumn());
}

/* voci */
$tabella = "fatture_voci";
$where = "idfattura = $id ORDER BY ordine";
$dativoci = new fatture($id, $tabella);
$voci = $dativoci->richiamaWhere($where);

if ($voci) {
    $vociprint = "<table style=\"width: 100%; border: 1px solid #cccccc; margin-top: 10px;\" cellspacing=0>
    <thead>
                <tr style=\"background-color: #cccccc;\">
                <th style=\"text-align: left; width: 8%; font-size: 8pt; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; padding:5px;\"><b>Codice</b></th>
                <th style=\"text-align: left; width: 50%; font-size: 8pt; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; padding:5px;\"><b>Descrizione</b></th>
                <th style=\"text-align: left; width: 5%; font-size: 8pt; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; padding:5px;\"><b>U.M.</b></th>
                <th style=\"text-align: left; width: 5%; font-size: 8pt; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; padding:5px;\"><b>Qta</b></th>
                <th style=\"text-align: left; width: 10%; font-size: 8pt; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; padding:5px;\"><b>Importo</b></th>
                <th style=\"text-align: left; width: 7%; font-size: 8pt; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; padding:5px;\"><b>Sconto</b></th>
                <th style=\"text-align: left; width: 10%; font-size: 8pt; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; padding:5px;\"><b>Totale</b></th>
                <th style=\"text-align: left; width: 5%; font-size: 8pt; border-bottom: 1px solid #ffffff; padding:5px;\"><b>Iva</b></th>
                </tr></thead><tbody>";

    foreach ($voci as $vocid) {
        $descrizionearr = explode("\n", $vocid['descrizione']);
        $nomevoce = $descrizionearr[0];
        unset($descrizionearr[0]);
        $codice = $vocid['nome'];
        $um = $vocid['um'];
        $qta = $vocid['qta'];
        $importo = $vocid['importo'];
        $sconto = $vocid['sconto'];
        $totscontato = $qta * ($importo - ($importo * $sconto / 100));
        $iva = $vocid['iva'];

        if (!$sconto) {
            $sconto = "";
        } else {
            $sconto = "$sconto%";
        }
        if ($qta == 0) {
            $vociprint .= "<tr>
                <td valign=top style=\"text-align: left; width: 8%; font-size: 8pt; padding:5px; border-right: 1px solid #cccccc;\"><i><strong>&nbsp;</strong></i></td>
            <td valign=top style=\"text-align: left; width: 50%; font-size: 8pt; padding:5px; border-right: 1px solid #cccccc;\"><i><strong>$nomevoce</strong></i></td>
                <td valign=top style=\"text-align: left; width: 5%; font-size: 8pt; padding:5px; border-right: 1px solid #cccccc;\">&nbsp;</td>
            <td valign=top style=\"text-align: left; width: 5%; font-size: 8pt; padding:5px; border-right: 1px solid #cccccc;\">&nbsp;</td>
            <td valign=top style=\"text-align: left; width: 10%; font-size: 8pt; padding:5px; border-right: 1px solid #cccccc;\">&nbsp;</td>
            <td valign=top style=\"text-align: left; width: 7%; font-size: 8pt; padding:5px; border-right: 1px solid #cccccc;\">&nbsp;</td>
            <td valign=top style=\"text-align: left; width: 10%; font-size: 8pt; padding:5px; border-right: 1px solid #cccccc;\">&nbsp;</td>
                <td valign=top style=\"text-align: left; width: 5%; font-size: 8pt; padding:5px;\">&nbsp;</td>
            </tr>";
        } else {
            $vociprint .= "<tr>
                <td valign=top style=\"text-align: left; width: 8%; font-size: 8pt; padding:5px; border-right: 1px solid #cccccc;\">$codice</td>
            <td valign=top style=\"text-align: left; width: 50%; font-size: 8pt; padding:5px; border-right: 1px solid #cccccc;\"><strong>$nomevoce</strong><br />" . join("<br />", $descrizionearr) . "</td>
                <td valign=top style=\"text-align: left; width: 5%; font-size: 8pt; padding:5px; border-right: 1px solid #cccccc;\">$um</td>
            <td valign=top style=\"text-align: left; width: 5%; font-size: 8pt; padding:5px; border-right: 1px solid #cccccc;\">$qta</td>
            <td valign=top style=\"text-align: left; width: 10%; font-size: 8pt; padding:5px; border-right: 1px solid #cccccc;\">" . number_format($importo, 2, ',', '.') . "</td>
            <td valign=top style=\"text-align: left; width: 7%; font-size: 8pt; padding:5px; border-right: 1px solid #cccccc;\">" . number_format($sconto, 2, ',', '.') . "</td>
            <td valign=top style=\"text-align: left; width: 10%; font-size: 8pt; padding:5px; border-right: 1px solid #cccccc;\">" . number_format($totscontato, 2, ',', '.') . "</td>
                <td valign=top style=\"text-align: left; width: 5%; font-size: 8pt; padding:5px;\">$iva%</td>
            </tr>";
        }
    }
    $vociprint .= "</tbody></table>";
}
/* tabella iva */
$sql = $db->prepare("SELECT sum(qta*(importo-importo*sconto/100)) as tabimponibile, sum((qta*(importo-importo*sconto/100))*iva/100) tabimposta, iva FROM `fatture_voci` WHERE idfattura = ? GROUP BY iva ORDER BY iva ASC");
$sql->execute(array($id));
$res = $sql->fetchAll();
foreach ($res as $row) {
    $tabimponibile = $row['tabimponibile'];
    $tabimposta = $row['tabimposta'];
    $tabiva = $row['iva'];
    $dettagliotabellaiva .= "<tr>
	                <td style=\"text-align: left; width: 50%; font-size: 8pt; padding:2px;\">IVA al $tabiva%</td>
	                <td style=\"text-align: left; width: 20%; font-size: 8pt; padding:2px;text-align:right;\">" . number_format($tabimponibile, 2, ",", ".") . "</td>
	                <td style=\"text-align: left; width: 10%; font-size: 8pt; padding:2px;text-align:right;\">$tabiva</td>
	                <td style=\"text-align: left; width: 20%; font-size: 8pt; padding:2px;text-align:right;\">" . number_format($tabimposta, 2, ",", ".") . "</td>
	                </tr>";
}

$tabellaiva = "<table style=\"width: 100%;\" cellspacing=0>
	    <thead>
	                <tr>
	                <th style=\"text-align: left; width: 50%; font-size: 8pt; font-weight:normal; border-bottom: 1px solid #696969; padding:2px;border-right: 1px solid #696969;\"><b>Descrizione</b></th>
	                <th style=\"text-align: left; width: 20%; font-size: 8pt; font-weight:normal; border-bottom: 1px solid #696969; padding:2px;border-right: 1px solid #696969;\"><b>Imponibile</b></th>
					<th style=\"text-align: left; width: 10%; font-size: 8pt; font-weight:normal; border-bottom: 1px solid #696969; padding:2px;border-right: 1px solid #696969;\"><b>% IVA</b></th>
					<th style=\"text-align: left; width: 20%; font-size: 8pt; font-weight:normal; border-bottom: 1px solid #696969; padding:2px;\"><b>Imposta</b></th>				
	                </tr></thead><tbody>$dettagliotabellaiva</tbody></table>";

/* scadenze */
if ($day > 0) {
    $datascad = date('t/m/Y', strtotime($datafatt . '+ 120 days'));
    $scadenzeprint .= "<table style=\"width:100%; font-size: 7pt;\" cellspacing=0 cellpadding=0>"
            . "<tr>"
            . "<td>$datascad</td>"
            . "<td> " . number_format($totalefattiva, 2, ',', '.') . "&euro;</td>"
            . "</tr>"
            . "</table>";
} else {
    $scad = getDati("fatture_scadenze", "WHERE idfattura = $id AND sufattura = '0' ORDER BY datascadenza ASC");
    foreach ($scad as $scadd) {
        $dataarr = explode("-", $scadd['datascadenza']);
        $datait = $dataarr[2] . "/" . $dataarr[1] . "/" . $dataarr[0];
        $scadenzeprint .= "<table style=\"width:100%; font-size: 7pt;\" cellspacing=0 cellpadding=0>"
                . "<tr>"
                . "<td>$datait</td>"
                . "<td> " . number_format($scadd['importoscadenza'], 2, ',', '.') . "&euro;</td>"
                . "</tr>"
                . "</table>";
    }
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
    $coordinate_bancarie = str_replace("\n", "<br />", $datid[$bancapagamento]);
}
if (strlen($sedeoperativa1) > 0) {
    $sedeop1 = "Sede op.: $sedeoperativa1<br />";
}
if (strlen($sedeoperativa2) > 0) {
    $sedeop2 = "Sede op.2: $sedeoperativa2<br />";
}

$content .= "<page backtop=300px backbottom=100px>
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
		<span style=\"font-weight:bold; font-size: 12pt;\">$tipofattura</span>
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
		<td style=\"width: 25%; border-right: 1px solid #cccccc; padding:5px; font-size: 8pt;\" valign=top>Numero Documento</td>
		<td style=\"width: 25%; padding:5px; font-size: 8pt; border-right: 1px solid #cccccc;\" valign=top>Data Documento</td>
                <td style=\"width: 50%; padding:5px; font-size: 8pt;\" valign=top>Destinazione</td>
		</tr>
		<tr>
		<td style=\"width: 25%; border-right: 1px solid #cccccc; padding:5px; text-align:right;\" valign=top><b>$numero/" . $arrdata[0] . "</b></td>
		<td style=\"width: 25%; text-align:right; border-right: 1px solid #cccccc; padding:5px;\" valign=top><b>$data</b></td>
                <td style=\"width: 50%; text-align:left; padding:5px; font-size: 8pt;\" valign=top>
                <table border: 0; style=\"width: 100%;\">
                <tr>
                <td style=\"width: 5%;\">&nbsp;</td>
                <td style=\"width: 95%;\">$indirizzospedizionefattura</td>
                </tr>
                </table>
                </td>
		</tr>
		</table>
                <table style=\"margin-top:5px; width: 100%; border: solid 1px #cccccc;\">
			<tr>
			<td style=\"text-align: right; width: 100%\">pagina [[page_cu]]/[[page_nb]]</td>
			</tr>
			</table>
		</page_header>";


$content .= "$vociprint";

$content .= "<page_footer><table style=\"width:100%; border: 1px solid #696969;\" cellspacing=0 cellpadding=0>
			<tr>
			<td style=\"width: 60%; border-right: 1px solid #696969; font-size: 8pt;\" valign=top>
			<table style=\"width:100%;\" cellspacing=0 cellpadding=0>
				<tr>
				<td style=\"width:100%; border-bottom: 1px solid #696969;\" valign=\"top\">Dettaglio IVA</td>
				</tr>
				<tr>
				<td style=\"width:100%;\">
				$tabellaiva
				</td>
				</tr>
			</table>
			</td>
			<td style=\"width: 40%;  font-size: 8pt;\" align=top>
				<table style=\"width:100%; border: 2px solid #000000; background-color: #E6EEEE;\" cellspacing=0 cellpadding=0>
				<tr>
				<td style=\"width: 50%; border-right: 1px solid #696969;border-bottom: 1px solid #696969; padding:5px; font-size: 10pt;text-align:right;\">Totale imponibile</td>
				<td style=\"width: 50%; border-bottom: 1px solid #696969; padding:5px; font-size: 10pt;text-align:right;\">&euro; " . number_format($totalefatt, 2, ",", ".") . "</td>
				</tr>
				<tr>
				<td style=\"width: 50%; border-right: 1px solid #696969;border-bottom: 1px solid #696969; padding:5px; font-size: 10pt;text-align:right;\">Totale IVA</td>
				<td style=\"width: 50%; border-bottom: 1px solid #696969; padding:5px; font-size: 10pt;text-align:right;\">&euro; " . number_format($totalefattiva - $totalefatt, 2, ",", ".") . "</td>
				</tr>
				<tr>
				<td style=\"width: 50%; border-right: 1px solid #696969; padding:5px; font-size: 11pt;font-weight:bold;text-align:right;\">Totale</td>
				<td style=\"width: 50%;  padding:5px; font-size: 11pt;font-weight:bold;text-align:right;\">&euro; " . number_format($totalefattiva, 2, ",", ".") . "</td>
				</tr>
			</table>
			</td>
			</tr>
			</table> 
                        <table style=\"width:100%; margin-top:5px; border: 1px solid #696969; background-color: #ffffff;\" cellspacing=0 cellpadding=0>
			<tr>
			<td style=\"width: 50%; border-right: 1px solid #696969; font-size: 8pt; margin:3px;\" valign=top>Pagamenti</td>
                        <td style=\"width: 50%; border-right: 1px solid #696969; font-size: 8pt; margin:3px;\" valign=top>Note per Pagamenti</td>
			</tr>
			<tr>
			<td style=\"border-right: 1px solid #696969;  text-align:left;font-size: 8pt; margin:3px;\" valign=top>$metodopagamento $fine_vista<br />$scadenzeprint $bancaperribaprint</td>
                        <td style=\"border-right: 1px solid #696969; text-align:left;font-size: 8pt; margin:3px;\" valign=top>$coordinate_bancarie</td>
			</tr>
			</table></page_footer>
			";
$content .= "</page>";

require_once('../library/html2pdf/html2pdf.class.php');

$html2pdf = new HTML2PDF('P', 'A4', 'it');
$html2pdf->WriteHTML($content);
$html2pdf->Output('fattura_' . $numero . '_' . $datafatt . '_' . pulisciImmagine($firma) . '.pdf');
?>
