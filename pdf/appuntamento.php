<?php
require_once dirname(__FILE__).'/../vendor/autoload.php';
use Spipu\Html2Pdf\Html2Pdf;
use Spipu\Html2Pdf\Exception\Html2PdfException;
use Spipu\Html2Pdf\Exception\ExceptionFormatter;
ob_start('ob_gzhandler');
include '../library/controllo.php';
include '../library/config.php';
include '../library/connessione.php';
include '../library/functions.php';
include '../library/basic.class.php';
/* libreria del modulo */
include '../library/calendario.class.php';

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

    $caparra = $appuntamentod['caparra'];
    $pagcaparra = $appuntamentod['nomepagamentocaparra'];
    $datacaparra = $appuntamentod['datac'];
    if ($datacaparra == "00/00/0000") {
        $datacaparra = "";
    }

    /* pagamenti */
    $pagamento1 = $appuntamentod['pag1'];
    $nomepagamento1 = $appuntamentod['nomepag1'];
    $datapag1 = $appuntamentod['datap1'];
    if ($datapag1 == "00/00/0000") {
        $datapag1 = "";
    }

    $pagamento2 = $appuntamentod['pag2'];
    $nomepagamento2 = $appuntamentod['nomepag2'];
    $datapag2 = $appuntamentod['datap2'];
    if ($datapag2 == "00/00/0000") {
        $datapag2 = "";
    }

    $pagamento3 = $appuntamentod['pag3'];
    $nomepagamento3 = $appuntamentod['nomepag3'];
    $datapag3 = $appuntamentod['datap3'];
    if ($datapag3 == "00/00/0000") {
        $datapag3 = "";
    }
    /**/

    $saldo = $appuntamentod['saldo'];
    $dataentrocuisaldare = $appuntamentod['datas'];
    if ($dataentrocuisaldare == "00/00/0000") {
        $dataentrocuisaldare = "";
    }

    $datasaldoeffettuato = $appuntamentod['dataes'];
    if ($datasaldoeffettuato == "00/00/0000") {
        $datasaldoeffettuato = "";
    }

    $pagsaldo = $appuntamentod['nomepagamentosaldo'];

    $totalespesa = $appuntamentod['totalespesa'];

    $tipoabito = $appuntamentod['nometipoabito'];
    $prezzoabito = $appuntamentod['prezzoabito'];

    $modelloabito = $appuntamentod['nomemodabito'];

    $note = $appuntamentod['note'];
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

if ($vociacc) {
    $vociprint = "<div style=\"width: 100%; padding-top: 10px; font-weight: bolder; text-align: center;\"><b>ACCESSORI</b></div><table style=\"width: 100%; border: 1px solid #cccccc; margin-top: 10px;\" cellspacing=0>
    <thead>
                <tr style=\"background-color: #cccccc;\">
                <th style=\"text-align: left; width: 33%; font-size: 8pt; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; padding:5px;\"><b>Nome accessorio</b></th>
                <th style=\"text-align: left; width: 33%; font-size: 8pt; border-bottom: 1px solid #ffffff; border-right: 1px solid #ffffff; padding:5px;\"><b>Prezzo</b></th>
                <th style=\"text-align: left; width: 34%; font-size: 8pt; border-bottom: 1px solid #ffffff; padding:5px;\"><b>Note</b></th>
                </tr></thead><tbody>";
    foreach ($vociacc as $vocid) {
        $nomevoce = $vocid['nomeaccessorio'];
        $prezzovoce = $vocid['prezzoaccessorio'];
        $notevoce = $vocid['noteaccessorio'];
        $vociprint .= "<tr>
            <td valign=top style=\"text-align: left; width: 33%; font-size: 8pt; padding:5px; border-right: 1px solid #cccccc;\">$nomevoce</td>
            <td valign=top style=\"text-align: left; width: 33%; font-size: 8pt; padding:5px; border-right: 1px solid #cccccc;\">" . number_format($prezzovoce, 2, ',', '.') . " &euro;</td>
            <td valign=top style=\"text-align: left; width: 34%; font-size: 8pt; padding:5px;\">$notevoce</td>
            </tr>";
    }
    $vociprint .= "</tbody></table>";
}

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
		<img src=\"../immagini/logo/$logo\" alt=\"logo\" style=\"width:50%;\"><br /><br />
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
		<span style=\"font-weight:bold; font-size: 14pt;\">SCHEDA APPUNTAMENTO</span>
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
		<table style=\"width:100%; margin-top:10px; border: 1px solid #cccccc;\" cellspacing=0 cellpadding=0>
		<tr>
		<td style=\"width: 15%; border-right: 1px solid #cccccc; padding:3px; font-size: 8pt;\" valign=top>Data appuntamento</td>
		<td style=\"width: 15%; border-right: 1px solid #cccccc; padding:3px; font-size: 8pt; border-right: 1px solid #cccccc;\" valign=top>Orario Appuntamento</td>
                <td style=\"width: 30%; border-right: 1px solid #cccccc; padding:3px; font-size: 8pt;\" valign=top>Gestito da</td>
                <td style=\"width: 15%; border-right: 1px solid #cccccc; padding:3px; font-size: 8pt;\" valign=top>Data matrimonio</td>
                <td style=\"width: 25%; padding:3px; font-size: 8pt;\" valign=top>Come ci hai trovato?</td>                
		</tr>
		<tr>
		<td style=\"width: 15%; border-right: 1px solid #cccccc; padding:3px; text-align:right; font-size: 8pt;\" valign=top><b>$dataapp</b></td>
		<td style=\"width: 15%; text-align:right; border-right: 1px solid #cccccc; padding:3px; font-size: 8pt;\" valign=top><b>$orarioapp</b></td>
                <td style=\"width: 30%; text-align:right; border-right: 1px solid #cccccc; padding:3px; font-size: 8pt;\" valign=top><b>" . $datigestore[0]['nome'] . " " . $datigestore[0]['cognome'] . "</b></td>
                <td style=\"width: 15%; text-align:right; border-right: 1px solid #cccccc; padding:3px; font-size: 8pt;\" valign=top><b>$datamatrimonio</b></td>
                <td style=\"width: 25%; text-align:right; padding:3px; font-size: 8pt;\" valign=top><b>$provenienza</b></td>                
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

$content .= "<table style=\"width:100%; margin-top:10px;\" cellspacing=0 cellpadding=0>
<tr>
<td style=\"width: 25%; border: 1px solid #cccccc; padding:3px; font-size: 8pt;\" valign=top><b>L'acquisto è avvenuto?</b> <span style=\"color: #ff0000\">" . $acquistato . "</span></td>
    <td style=\"width: 75%; border: 1px solid #cccccc; border-left: 0px; padding:3px; font-size: 8pt;\" valign=top>$printnoacquisto</td>
</tr>    
</table>
<table style=\"width:100%; margin-top:10px;\" cellspacing=0 cellpadding=0>
<tr>
<td style=\"width: 25%; border: 1px solid #cccccc; padding:3px; font-size: 8pt;\" valign=top><b>Estremi per il pagamento</b></td>
    <td style=\"width: 75%; border: 1px solid #cccccc; border-left: 0px; padding:3px; font-size: 8pt;\" valign=top>$datipagamento</td>
</tr>    
</table>
    <table style=\"width:100%; margin-top:10px;\" cellspacing=0 cellpadding=0>    
		<tr>
		<td style=\"width: 100%; text-align: center; padding:5px; font-size: 10pt; background-color: #e1bdaf;\" valign=top>DATI POST APPUNTAMENTO</td>
		</tr>
		</table>
                <div style=\"width: 100%; padding-top: 10px; font-weight: bolder; text-align: center;\"><b>DATI ABITO</b></div>
                <table style=\"width:100%; margin-top:10px; border: 1px solid #cccccc;\" cellspacing=0 cellpadding=0>
		<tr>
		<td style=\"width: 25%; border-right: 1px solid #cccccc; padding:3px; font-size: 8pt;\" valign=top>Tipo di abito</td>
		<td style=\"width: 25%; border-right: 1px solid #cccccc; padding:3px; font-size: 8pt; border-right: 1px solid #cccccc;\" valign=top>Modello di abito</td>
                <td style=\"width: 25%; border-right: 1px solid #cccccc; padding:3px; font-size: 8pt;\" valign=top>Prezzo abito</td>
                <td style=\"width: 25%; padding:3px; font-size: 8pt;\" valign=top>Necessita sartoria?</td>           
		</tr>
		<tr>
		<td style=\"width: 25%; border-right: 1px solid #cccccc; padding:3px; text-align:right; font-size: 8pt;\" valign=top><b>$tipoabito</b></td>
		<td style=\"width: 25%; text-align:right; border-right: 1px solid #cccccc; padding:3px; font-size: 8pt;\" valign=top><b>$modelloabito</b></td>
                <td style=\"width: 25%; text-align:right; border-right: 1px solid #cccccc; padding:3px; font-size: 8pt;\" valign=top><b>" . number_format($prezzoabito, 2, ',', '.') . " &euro;</b></td>
                <td style=\"width: 25%; text-align:right; padding:3px; font-size: 8pt;\" valign=top><b>$sartoria</b></td>
                </tr>
		</table>
                <div style=\"width: 100%; padding-top: 10px; font-weight: bolder; text-align: center;\"><b>DATI PAGAMENTO</b></div>";
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
        . "<table style=\"width: 70%; font-size: 12px\"><tr><td><div style=\"width: 100%; text-align: center; font-size: 16pt\"><strong>Contratto di ordine di acquisto</strong></div>"
        . "<br /<br />"
        . "<strong>Cliente:</strong><br />$daticliente<br /><br/><br />"
        . "<strong>Condizioni di vendita</strong>"
        . "<br /><br />"
        . "<ol>
            <li><strong>Termine per il pagamento e ritiro</strong><br/>
Gli articoli devono essere saldati e ritirati al momento dell’acquisto.
In alternativa, possono essere bloccati mediante versamento di una caparra confirmatoria pari ad almeno il 50% del prezzo finale.
In questa seconda ipotesi, il Cliente si obbliga al saldo ed al ritiro della merce entro e non oltre i 30 giorni successivi al versamento
della caparra.<br /><br /></li>
<li><strong>Conseguenze dell’inadempimento sulla caparra</strong><br/>
Qualora il cliente non provveda al saldo del prezzo entro il termine di 30 giorni indicato al punto precedente, l’Atelier si riserva il
diritto di recedere dal presente contratto con ritenzione di caparra ex art. 1385, comma 2, c.c. e conseguente reinserimento della
merce nell’inventario di vendita.
Parallelamente, il Cliente potrà esigere il doppio della caparra versata qualora l’Atelier non concluda il contratto ovvero, dopo il
versamento della caparra da parte del Cliente, receda ingiustificatamente da esso.
Qualora alla merce ordinata siano state apportate modifiche sartoriali per conto del Cliente e questi si renda inadempiente
all’obbligo di pagamento del prezzo entro il termine di 30 giorni di cui al punto 1, il Cliente dovrà corrispondere all’Atelier una penale
pari all’intero prezzo di vendita della merce. In questa ipotesi l’importo versato a titolo di caparra confirmatoria sarà trattenuto
dall’Atelier come acconto sul maggior importo dovuto a titolo di penale contrattuale.<br /><br />
</li>
<li><strong>Formula “Visto e Piaciuto”</strong><br />
Tutti i capi presenti in Atelier vengono venduti con la formula “Visto e Piaciuto”.
Il Cliente, acquistando la merce, dichiara dunque che la stessa è conforme al contratto di vendita in quanto presenta la qualità e le
prestazioni abituali di un bene dello stesso tipo, è conforme alla descrizione fatta dall’Atelier, risulta idonea tanto all’uso al quale
serve abitualmente quanto all’uso particolare voluto e manifestato dal Cliente.
Si esclude, di conseguenza, sia il reso che il cambio dei capi già acquistati.<br /><br />
</li>
<li><strong>Servizi accessori</strong><br />
Per i servizi accessori diversi dalla vendita (a mero titolo esemplificativo, servizi di sartoria e lavanderia) il Cliente dovrà
interfacciarsi direttamente col singolo prestatore del servizio  che agisce in nome proprio  stipulando un apposito contratto,
diverso ed autonomo rispetto al presente.
Tutti i costi connessi ai servizi accessori diversi dalla vendita sono interamente a carico del Cliente.
Rispetto ai suddetti servizi l’Atelier si dichiara esente da ogni responsabilità e dunque il Cliente, con riferimento ai servizi in parola,
rinuncia fin d’ora a qualsivoglia pretesa o azione, a qualunque titolo motivata, nei confronti dell’Atelier.
</li>
</ol>
<br /><br />
Luogo: $comuneatelier ($provinciaatelier)<br /><br />
Data: $dataapp<br /><br /><br /><br />
Letto, confermato e sottoscritto     __________________________<br /><br /><br />
Ai sensi e per gli effetti degli artt.1341 e 1342 c.c. l’acquirente dichiara di aver attentamente esaminato, di conoscere ed accettare
espressamente i punti 2 (Conseguenze dell’inadempimento sulla caparra), 3 (Formula “Visto e Piaciuto”), 4. (Servizi accessori).
<br /><br /><br />
Luogo: $comuneatelier ($provinciaatelier)<br /><br />
Data: $dataapp<br /><br /><br /><br />
Letto, confermato e sottoscritto     __________________________</td></tr></table>
<page_footer>
<table style=\"width:100%; margin-top:10px;\" cellspacing=0 cellpadding=0>
<tr>
<td style=\"width: 25%; border: 1px solid #cccccc; padding:3px; font-size: 8pt;\" valign=top><b>Estremi per il pagamento</b></td>
    <td style=\"width: 75%; border: 1px solid #cccccc; border-left: 0px; padding:3px; font-size: 8pt;\" valign=top>$datipagamento</td>
</tr>    
</table>
$footerheaderercontratto</page_footer></page>";

/* fast priority */
$content .= "<page backtop=60px backbottom=0px>"
        . "<page_header>$footerheaderercontratto</page_header>"
        . "<table style=\"width: 100%; font-size: 12px\"><tr><td><div style=\"width: 100%; text-align: center; font-size: 16pt\"><strong>Contratto di acquisto \"Fast Priority\"</strong></div>"
        . "<br /<br />"
        . "Fra le sottoscritte parti:<br /><br />"
        . "$footer<br />"
        . "<strong>Fornitore Concedente</strong>"
        . "<br /><br />e<br /><br />"
        . "<strong>Cliente:</strong><br />$daticliente<br />"
        . "<strong>Cliente Opzionario</strong>"
        . "</td></tr></table>"
        . "<br />"
        . "<span style=\"font-size: 12px;\">"
        . "<div style=\"text-align: center\">Premesso che</div><br /><br />
- il Concedente è la prima catena di outlet del mondo sposa in Italia ed è specializzata nell'offrire alla propria Opzionariola abiti provenienti dalle più grandi Firme nazionali ed internazionali a prezzi da togliere scontati fino all'80%;
<br />
- Per tale motivazione il Concedente dispone nel proprio Atelier di moltissimi abiti inquadrabili come Pezzo unico e, pertanto, tendenzialmente ogni Cliente avrà un abito diverso dall'altra;
<br />
- La Cliente ha dimostrato interesse all'acquisto di un abito rientrante nella categoria Pezzo Unico come meglio individuato nella scheda tecnica  che si allega(All. 1);
<br />
- La Cliente non è, tuttavia, ancora certa della propria scelta ed intende bloccare la vendita dell'abito per due settimane al fine di valutarne attentamente l'acquisto.
<br /><br />
Tutto quanto innanzi premesso  
<br /><br />
<div style=\"text-align: center;\">Si conviene e si stipula  quanto segue</div><br /><br />

Art. 1 - Premesse ed allegati.  Le premesse ed allegati sono da intendersi quale parte integrante, essenziale e sostanziale della presente scrittura privata;
<br /><br />
Art. 2 - oggetto e durata del contratto.  con la presente scrittura il Concedente concede ex art. 1331 c.c. all'Opzionario che accetta - a fronte dell'integrale e contestuale versamento del corrispettivo pari ad euro 100,00(cento)- il diritto di opzione per la compravendita l'abito individuato nell'allegato n. 1 ed al prezzo indicato nel medesimo allegato. Il Concedente si considera vincolato irrevocabilmente per 7(sette) giorni calcolati a partire dalla data di stipula del presente accordo. Decorso tale termine senza che l'Opzionario abbia esercitato l'opzione il vincolo di cui sopra si estinguerà ed il presente contratto cesserà di produrre il proprio effetto. L'estinzione del vincolo è automatica e non comporta alcun obbligo di avviso da parte del Concedente nei confronti dell'Opzionario.
<br /><br />
Art. 3 - Effetti della scrittura privata. <br />
1. Il Opzionario è informato ed accetta che Il corrispettivo convenuto non è da intendersi come somma versata a titolo di caparra confirmatoria e, pertanto, non si produrranno gli effetti previsti dall'art. 1385 cc. 
<br />2. Il Opzionario è informato ed accetta che il corrispettivo versato per il presente contratto è, appunto, un corrispettivo per il servizio acquistato e specificato all'art. 2(opzione ex art. 1331 cc) e, pertanto, nel caso in cui decida di non acquistare l'abito o lo acquisti successivamente ai 7 giorni previsti detta somma non verrà restituita.
<br />3. II Concedente si obbliga a non alienare a terzi per 7 giorni a partire dalla data di stipula del presente accordo l'abito individuato nell'allegato n. 1.
<br />4. Il Concedente si obbliga, nel caso in cui la Opzionario decida di acquistare l'abito entro i 7 giorni previsti, ad imputare la somma già versata di euro 100,00(cento) come acconto a maggior dare del prezzo totale del bene pari alla somma indicata nell'allegato 1. 
<br /><br />Art. 4 - Limitazione di responsabilità. Gli obblighi e le responsabilità del Concedente verso il Opzionario sono esclusivamente quelli definiti dal contratto. Le Parti accettano che qualora una limitazione, esclusione, restrizione o altra disposizione contenuta in questo contratto sia giudicata nulla per un qualsivoglia motivo e il Concedente diventi responsabile, la conseguente richiesta di risarcimento del danno non potrà eccedere il valore del presente contratto. Le Parti accettano, altresì, che in caso di qualsiasi tipo di controversia inerente o derivante dal presente contratto per la quale il Concedente sia ritenuto responsabile in qualsiasi modo, la conseguente richiesta di risarcimento del danno, non potrà eccedere il valore del presente contratto. Resta espressamente escluso, qualsiasi altro indennizzo o risarcimento all'Opzionario per danni diretti o indiretti di qualsiasi natura e specie.
<br /><br />Art. 5 - Completezza del contratto. Questo accordo con i relativi allegati costituisce l'intero accordo ed annulla ogni precedente accordo o qualunque altro comtemporaneo accordo orale o scritto intervenuto tra le parti su questa materia. Nessuna aggiunta o modifica al presente accordo sarà considerata valida se non fatta per iscritto. Le intestazioni dei paragrafi o altre divisioni sono inserite al solo scopo di comodità e consultazione. Tali intestazioni non devono essere ritenute di controllare, limitare, modificare o in alcun altro modo influenzare lo scopo, significato o intento delle disposizioni di questo contratto o di qualsiasi sua parte, e non deve essergli altrimenti dato alcun effetto giuridico.
<br /><br />Art. 6 -  Clausola di salvaguardia. Nel caso una delle clausole del presente contratto dovesse essere ritenuta nulla e/o inefficace le Parti convengono di negoziare e concordare in buona fede affinché tali disposizioni o clausole siano sostituite con altre valide ed efficaci che abbiano sostanzialmente lo stesso effetto avuto riguardo all’oggetto e agli scopi del contratto. 
<br /><br />Art. 7 - Legge applicabile e foro competente. Il presente accordo è disciplinato dalla legge italiana e per ogni controversia nascente dall’interpretazione ed esecuzione dello stesso sarà esclusivamente competente il foro di Reggio Emilia
<br /><br />La presente scrittura è composto da 2 pagine oltre il seguente allegato che ne costituisce parte integrante e sostanziale: 1) Scheda abito composto di 1 pagina;
<br /><br />Letto, approvato e sottoscritto in  ogni suo foglio ed in calce alla presente.
<br /><br /><br />
Luogo: $comuneatelier ($provinciaatelier)<br /><br />
Data: $dataapp<br /><br /><br /><br />
Opzionario     __________________________&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;La Concedente     __________________________<br /><br /><br />
<br /><br /><br />
Ai sensi e per gli effetti degli artt.1341 e 1342 c.c. l’acquirente dichiara di aver attentamente esaminato, di conoscere ed accettare
espressamente i punti 2 (Conseguenze dell’inadempimento sulla caparra), 3 (Formula “Visto e Piaciuto”), 4. (Servizi accessori).
<br /><br /><br />
<br /><br /><br />
Opzionario     __________________________&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;La Concedente     __________________________
</span>
</page>";

/* ricevuta di consegna */

$content .= "<page backtop=60px backbottom=50px>"
        . "<page_header>$footerheaderercontratto</page_header>"
        . "<table style=\"width: 100%; font-size: 12px\"><tr><td><div style=\"width: 100%; text-align: center; font-size: 16pt\"><strong>Ricevuta di Consegna</strong></div>"
        . "<br /<br /><br /><br />"
        . "La sottoscritta $nome $cognome, Cliente  del contratto Servizi Sartoriali stipulato in data $dataapp con la società $footer"
        . "</td></tr></table>"
        . "<br />"
        . "<div style=\"text-align: center\">DICHIRA</div><br /><br />
Di aver ritirato in data odierna il proprio abito su cui sono stati effettuati tutti i servizi sartoriali richiesti. Gli stessi, dopo attento controllo, sono conformi alle mie richieste ed alla “scheda misure” compilata insieme allo Staff della società, pertanto, sono estremamente soddisfatta del lavoro svolto non avendo null’altro a pretendere  a qualsiasi titolo - per l’abito ed i servizi oggetto del contratto stipulato - dalla società $footer. 
<br /><br /><br />
Data: $dataapp<br /><br /><br /><br />
__________________________</page>";

/* ricevuta di delega */

$content .= "<page backtop=60px backbottom=50px>"
        . "<page_header>$footerheaderercontratto</page_header>"
        . "<table style=\"width: 100%; font-size: 12px\"><tr><td><div style=\"width: 100%; text-align: center; font-size: 16pt\"><strong>Ricevuta di Consegna</strong></div>"
        . "<br /><br /><br /><br />"
        . "La sottoscritta ______________________________ (c.f. _____________________), in qualità di delegata della sig.ra $nome $cognome, Cliente del contratto Servizi Sartoriali stipulato in data $dataapp con la società $footer"
        . "</td></tr></table>"
        . "<br />"
        . "<div style=\"text-align: center\">DICHIRA</div><br /><br />
Sotto la propria responsabilità di aver ritirato in data odierna l’abito di proprietà della delegante su cui sono stati effettuati tutti i servizi sartoriali dalla stessa richiesti. Gli stessi, dopo attento controllo, sono conformi alla “scheda misure” compilata dalla Cliente insieme allo Staff della società. Pertanto manlevo espressamente, prendendomene ogni responsabilità,  la Società come in una favola Srl da ogni tipologia di pretesa, anche di danni diretti ed indiretti, per lavoro svolto per l’abito ed i servizi oggetto del contratto stipulato. 
<br /><br /><br />
Data: $dataapp<br /><br /><br /><br />
__________________________</page>";

$html2pdf = new HTML2PDF('P', 'A4', 'it');
$html2pdf->WriteHTML($content);
$html2pdf->Output('appuntamento_' . $nome . '-' . $cognome . '_' . $numero . '_' . $dataappdb . '.pdf');
?>
