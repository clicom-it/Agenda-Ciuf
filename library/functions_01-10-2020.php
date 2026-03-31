<?php

function rand_color() {
    return '#' . str_pad(dechex(mt_rand(0, 0xFFFFFF)), 6, '0', STR_PAD_LEFT);
}

function getOredipforn($where, $anduser, $andcli) {
    global $db;

    $sql = $db->prepare("SELECT *, DATE_FORMAT(dataoredettaglio, '%d/%m/%Y') as giorno FROM ore_voci $where $anduser $andcli ORDER BY dataoredettaglio");

    try {
        $sql->execute();
        $res = $sql->fetchAll(PDO::FETCH_ASSOC);
        return $res;
    } catch (PDOException $e) {
        die('Connessione fallita: ' . $e->getMessage());
    }
}

function getDatiCal($where) {    
    global $db;
    $sql = $db->prepare("SELECT *, DATE_FORMAT(data, '%d/%m/%Y') as datait, DATE_FORMAT(datasaldo, '%d/%m/%Y') as datas, DATE_FORMAT(datamatrimonio, '%d/%m/%Y') as datamatrimonioit, TIME_FORMAT(orario, '%H:%i') as orariodb FROM calendario $where");

    try {
        $sql->execute();
        $res = $sql->fetchAll(PDO::FETCH_ASSOC);
        return $res;
    } catch (PDOException $e) {
        die('Connessione fallita: ' . $e->getMessage());
    }
}

function getDatiCalStampa($where) {
    global $db;
    $sql = $db->prepare("SELECT *, DATE_FORMAT(data, '%d/%m/%Y') as datait, TIME_FORMAT(orario, '%H:%i') as orariodb, DATE_FORMAT(datamatrimonio, '%d/%m/%Y') as datamatr FROM calendario $where ORDER BY data, orario");

    try {
        $sql->execute();
        $res = $sql->fetchAll(PDO::FETCH_ASSOC);
        return $res;
    } catch (PDOException $e) {
        die('Connessione fallita: ' . $e->getMessage());
    }
}

function meseprofit($anno, $mese, $where) {
    global $db;
    global $arrmesi;

    $sql = $db->prepare('SELECT id, tipo FROM fatture WHERE MONTH(data) = ? AND YEAR(data) = ? AND tipo IN("0","4","3","5","6") ' . $where . '');

    $sql1 = $db->prepare('SELECT SUM((qta*(importo - (importo * sconto / 100)))*(100+iva) / 100) as sommaprofit, (SELECT nome FROM profit_center WHERE id = idprofit) as nomeprofit, idprofit FROM fatture_voci WHERE idfattura = ? GROUP by idprofit');
    for ($i = 1; $i <= 12; $i++) {
        $sql->execute(array($i, $anno));
        $res = $sql->fetchAll();
        $arrprofit[$arrmesi[$i]] = array();
        foreach ($res as $row) {
            $idfatt = $row['id'];
            $tipofatt = $row['tipo'];
            $sql1->execute(array($idfatt));
            $res1 = $sql1->fetchAll();
            $arrdatiprofit = array();
            foreach ($res1 as $row1) {
                if ($row1['sommaprofit'] > 0) {
                    $sommafattura = round($row1['sommaprofit'], 2, PHP_ROUND_HALF_UP);
                    $nomeprofit = $row1['nomeprofit'];
                    if (!$nomeprofit) {
                        $nomeprofit = "Non assegnato";
                    } else {
                        $nomeprofit = $row1['nomeprofit'];
                    }
                    $idprofit = $row1['idprofit'];
                    if ($tipofatt == 3 || $tipofatt == 6) {
                        $sommafattura = -$sommafattura;
                    } else {
                        $sommafattura = $sommafattura;
                    }
                    $arrdatiprofit[] = array('nomeprofit' => $nomeprofit, 'sommaprofit' => $sommafattura, 'idprofit' => $idprofit);
                }
            }
            $sommaprofit = 0;
            foreach ($arrdatiprofit as $arrdatiprofitd) {
                $nomeprofit = $arrdatiprofitd['nomeprofit'];
                $sommaprofit = $arrdatiprofitd['sommaprofit'];
                $idprofit = $arrdatiprofitd['idprofit'];

                $arrprofit[$arrmesi[$i]][$nomeprofit] += round($sommaprofit, 2, PHP_ROUND_HALF_UP);
            }
        }
    }

    return $arrprofit[$arrmesi[$mese]];
}

function totaleprofit($anno, $where) {
    global $db;

    $sql = $db->prepare('SELECT id, tipo FROM fatture WHERE YEAR(data) = ? AND tipo IN ("0","3","4","5","6") ' . $where . '');
    $sql1 = $db->prepare('SELECT SUM((qta*(importo - (importo * sconto / 100)))*(100+iva) / 100) as sommaprofit, (SELECT nome FROM profit_center WHERE id = idprofit) as nomeprofit, idprofit FROM fatture_voci WHERE idfattura = ? GROUP by idprofit');

    $sql->execute(array($anno));
    $res = $sql->fetchAll();
    $arrprofit = array();
    foreach ($res as $row) {
        $idfatt = $row['id'];
        $tipofatt = $row['tipo'];
        $sql1->execute(array($idfatt));
        $res1 = $sql1->fetchAll();
        $arrdatiprofit = array();
        foreach ($res1 as $row1) {
            if ($row1['sommaprofit'] > 0) {
                $sommafattura = round($row1['sommaprofit'], 2, PHP_ROUND_HALF_UP);
                $nomeprofit = $row1['nomeprofit'];
                if (!$nomeprofit) {
                    $nomeprofit = "Non assegnato";
                } else {
                    $nomeprofit = $row1['nomeprofit'];
                }
                $idprofit = $row1['idprofit'];
                if ($tipofatt == 3 || $tipofatt == 6) {
                    $sommafattura = -$sommafattura;
                } else {
                    $sommafattura = $sommafattura;
                }
                $arrdatiprofit[] = array('nomeprofit' => $nomeprofit, 'sommaprofit' => $sommafattura, 'idprofit' => $idprofit);
            }
        }
        $sommaprofit = 0;
        foreach ($arrdatiprofit as $arrdatiprofitd) {
            $nomeprofit = $arrdatiprofitd['nomeprofit'];
            $sommaprofit = $arrdatiprofitd['sommaprofit'];
            $idprofit = $arrdatiprofitd['idprofit'];

            $arrprofit[$nomeprofit] += round($sommaprofit, 2, PHP_ROUND_HALF_UP);
        }
    }


    return $arrprofit;
}

function sommaorelavorate($times) {

    // loop throught all the times
    foreach ($times as $time) {
        list($hour, $minute) = explode(':', $time);
        $minutes += $hour * 60;
        $minutes += $minute;
    }

    $hours = floor($minutes / 60);
    $minutes -= $hours * 60;

    // returns the time already formatted
    return sprintf('%02d:%02d', $hours, $minutes);
}

function cropString($str, $chars) {
    if (strlen($str) <= $chars) {
        return $str;
    }
    $new = wordwrap($str, $chars, "|");
    $result = explode("|", $new);
    return $result[0] . " [...]";
}

function moduloattivo($modulo) {
    global $db;
    /* controllo se il modulo è attivo o no propio per il db */
    $sql = $db->prepare("SELECT attivo FROM moduli WHERE nome = ? LIMIT 1");
    $sql->execute(array($modulo));
    if ($sql->fetchColumn() > 0) {
        /* controllo se il modulo è attivo o no per l'utente non admin */
        if ((($_SESSION['livello'] == 2 || $_SESSION['livello'] == 3 || $_SESSION['livello'] == 5) && $_SESSION[$modulo] == 0)) {
            $attivo = 0;
        } else {
            $attivo = 1;
        }
    } else {
        $attivo = 0;
    }
    /**/
    return $attivo;
}

function attivaimpostazioni() {
    /* controllo se attivo o no per l'utente non admin */
    if ($_SESSION['livello'] == 2 || $_SESSION['livello'] == 3) {
        $attivo = 0;
    } else {
        $attivo = 1;
    }

    /**/

    return $attivo;
}

function getDati($tabella, $where) {
    global $db;
    $sql = $db->prepare("SELECT * FROM $tabella $where");
    try {
        $sql->execute();
        $res = $sql->fetchAll(PDO::FETCH_ASSOC);
        return $res;
    } catch (PDOException $e) {
        die('Connessione fallita: ' . $e->getMessage());
    }
}

function getDatiVocicomm($idcomm, $and) {
    global $db;
    $sql = $db->prepare("SELECT *, SUM(prezzocliente) as somma, (SELECT nome FROM profit_center WHERE id = idprofit) as nomeprofit FROM commesse_voci WHERE idcomm = ? $and GROUP BY idprofit");
    try {
        $sql->execute(array($idcomm));
        $res = $sql->fetchAll(PDO::FETCH_ASSOC);
        return $res;
    } catch (PDOException $e) {
        die('Connessione fallita: ' . $e->getMessage());
    }
}

function getDatiVocicommCosti($idcomm, $and) {
    global $db;
    $sql = $db->prepare("SELECT * FROM commesse_voci WHERE idcomm = ? $and");
    try {
        $sql->execute(array($idcomm));
        $res = $sql->fetchAll(PDO::FETCH_ASSOC);
        return $res;
    } catch (PDOException $e) {
        die('Connessione fallita: ' . $e->getMessage());
    }
}

function getDatiCosticomm($idcomm, $and) {
    global $db;
    $sql = $db->prepare("SELECT *, SUM(costo) as somma, (SELECT nome FROM centri_costo WHERE id = idcentrodicosto) as nomecentrodicosto, (SELECT YEAR(data) FROM commesse WHERE id = idcomm) as anno, (SELECT MONTH(data) FROM commesse WHERE id = idcomm) as mese FROM commesse_costi WHERE idvocecomm = ? $and GROUP BY idcentrodicosto");
    try {
        $sql->execute(array($idcomm));
        $res = $sql->fetchAll(PDO::FETCH_ASSOC);
        return $res;
    } catch (PDOException $e) {
        die('Connessione fallita: ' . $e->getMessage());
    }
}

function getDatiOrecomm($idcomm, $and) {
    global $db;
    $sql = $db->prepare("SELECT *, SUM(costo) as somma, (SELECT nome FROM centri_costo WHERE id = centro_costo) as nomecentrodicosto FROM ore_voci  WHERE idvocecomm = ? $and");
    try {
        $sql->execute(array($idcomm));
        $res = $sql->fetchAll(PDO::FETCH_ASSOC);
        return $res;
    } catch (PDOException $e) {
        die('Connessione fallita: ' . $e->getMessage());
    }
}

function maxNum($tabella, $campo, $where) {
    global $db;
    $sql = $db->prepare("SELECT MAX($campo) as max FROM $tabella $where");
    try {
        $sql->execute();
        $res = $sql->fetchAll(PDO::FETCH_ASSOC);
        return $res;
    } catch (PDOException $e) {
        die('Connessione fallita: ' . $e->getMessage());
    }
}

function sendMail($maildest, $subject, $message, $mailfrom, $nomefrom, $allegato) {
    $error = true;
    $mail = new PHPmailer();
    $mail->CharSet = 'utf-8';
    $mail->IsSMTP();
    $mail->SMTPAuth = true;
//    $mail->SMTPSecure = 'tls'; // attivare o meno a seconda del server che si usa ad inviare
    $mail->Host = MAIL_HOST;
    $mail->Port = 587;
    $mail->Username = MAIL_USER;
    $mail->Password = MAIL_PWD;
    $mail->setFrom($mailfrom, $nomefrom);
    $mail->addReplyTo($mailfrom, $nomefrom);
    $mail->AddAddress($maildest);
    $mail->Subject = $subject;
    $mail->MsgHTML($message);
    if ($allegato) {
        $mail->addAttachment($allegato);
    }

    if (!$mail->Send()) {
        $error = false;
    }
    return $error;
}

function randomPassword($char) {
    $alphabet = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789";
    $pass = array();
    $alphaLength = strlen($alphabet) - 1;
    for ($i = 0; $i < $char; $i++) {
        $n = rand(0, $alphaLength);
        $pass[] = $alphabet[$n];
    }
    return implode($pass);
}

function getNazioni() {
    global $db;
    $sql = $db->prepare("SELECT * FROM nazioni");
    try {
        $sql->execute();
        $res = $sql->fetchAll(PDO::FETCH_ASSOC);
        return $res;
    } catch (PDOException $e) {
        die('Connessione fallita: ' . $e->getMessage());
    }
}

function getRegione() {
    global $db;
    $sql = $db->prepare("SELECT * FROM province GROUP BY regione ORDER BY regione");
    try {
        $sql->execute();
        $res = $sql->fetchAll(PDO::FETCH_ASSOC);
        return $res;
    } catch (PDOException $e) {
        die('Connessione fallita: ' . $e->getMessage());
    }
}

function getProvincia($regione) {
    global $db;
    $sql = $db->prepare("SELECT * FROM province WHERE regione = ? ORDER BY provincia");
    try {
        $sql->execute(array($regione));
        $res = $sql->fetchAll(PDO::FETCH_ASSOC);
        return $res;
    } catch (PDOException $e) {
        die('Connessione fallita: ' . $e->getMessage());
    }
}

function getComune($provincia) {
    global $db;
    $sql = $db->prepare("SELECT * FROM cap_localita WHERE provincia = ? GROUP BY comune ORDER BY comune");
    try {
        $sql->execute(array($provincia));
        $res = $sql->fetchAll(PDO::FETCH_ASSOC);
        return $res;
    } catch (PDOException $e) {
        die('Connessione fallita: ' . $e->getMessage());
    }
}

function getCap($comune) {
    global $db;
    $sql = $db->prepare("SELECT * FROM cap_localita WHERE comune = ? ORDER BY cap");
    try {
        $sql->execute(array($comune));
        $res = $sql->fetchAll(PDO::FETCH_ASSOC);
        return $res;
    } catch (PDOException $e) {
        die('Connessione fallita: ' . $e->getMessage());
    }
}

function pulisciImmagine($string) {
    $strResult = str_ireplace("à", "a", $string);
    $strResult = str_ireplace("á", "a", $strResult);
    $strResult = str_ireplace("è", "e", $strResult);
    $strResult = str_ireplace("é", "e", $strResult);
    $strResult = str_ireplace("ì", "i", $strResult);
    $strResult = str_ireplace("í", "i", $strResult);
    $strResult = str_ireplace("ò", "o", $strResult);
    $strResult = str_ireplace("ó", "o", $strResult);
    $strResult = str_ireplace("ù", "u", $strResult);
    $strResult = str_ireplace("ú", "u", $strResult);
    $strResult = str_ireplace("ç", "c", $strResult);
    $strResult = str_ireplace("ö", "o", $strResult);
    $strResult = str_ireplace("û", "u", $strResult);
    $strResult = str_ireplace("ê", "e", $strResult);
    $strResult = str_ireplace("ü", "u", $strResult);
    $strResult = str_ireplace("ë", "e", $strResult);
    $strResult = str_ireplace("ä", "a", $strResult);
    $strResult = str_ireplace("'", " ", $strResult);

    $strResult = preg_replace('/[^A-Za-z0-9-_ ]/', "", $strResult);
    $strResult = trim($strResult);
    $strResult = preg_replace('/[ ]{2,}/', " ", $strResult);
    $strResult = str_replace(" ", "-", $strResult);
    $strResult = preg_replace('/[-]{2,}/', "-", $strResult);

    $strResult = strtolower($strResult);

    return $strResult;
}

function getTipoDocumentoFE($tipo) {
    switch ($tipo) {
        case 0:
        case 4:
        case 5:
            $result = 'TD01';
            break;
        case 6:
        case 3:
            $result = 'TD04';
            break;
    }
    return $result;
}

function generaFatturaElettronica($fatturad) {
    global $db;
    //var_dump($fatturad);
    $idcliente = $fatturad['idcliente'];
    $id = $fatturad['id'];
    $qry = "select * from clienti_fornitori where id=$idcliente limit 1;";
    $rs = $db->prepare($qry);
    $rs->execute();
    $cliente = $rs->fetch(PDO::FETCH_ASSOC);
    //$cliente['indirizzo'] = str_replace("'", "&apos;", $cliente['indirizzo']);
    //$cliente['azienda'] = str_replace("'", "&apos;", $cliente['azienda']);
    $is_pa = (int) $cliente['pa'];
    $idcodice = '01879020517';
    $nazione_clicom = 'IT';
    $nazione = $cliente['nazione'];
    $iva_clicom = '02524780356';
    if ($cliente['piva'] != '' && $cliente['codicefiscale'] != "") {
        $cf = $cliente['codicefiscale'];
    } elseif ($cliente['piva'] == '' && $cliente['codicefiscale'] != "") {
        $cf = $cliente['codicefiscale'];
    } elseif ($cliente['piva'] != '' && $cliente['codicefiscale'] == "") {
        $cf = $cliente['piva'];
    }
    list($a, $m, $g) = explode("-", $fatturad['data']);
    //$anno_fatt = substr($a, -2);
    $anno_fatt = $a;
    $bancapagamento = $fatturad['coord_bancarie'];
    $qry = "select $bancapagamento from dati_gestionale limit 1;";
    $rs = $db->prepare($qry);
    $rs->execute();
    $banca = $rs->fetchColumn();
    list($banca_nome, $iban_tmp) = explode("IBAN CODE:", $banca);
    $iban = str_replace(" ", "", $iban_tmp);
    $sdi_default = '0000000';
    if ($nazione != 'IT')
        $sdi_default = 'xxxxxxx';
    $fatturad['tipo'] == '4' ? $pa = true : $pa = false;
    $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
            . '<q1:FatturaElettronica versione="FP' . ($pa ? 'A' : 'R') . '12" xmlns:ds="http://www.w3.org/2000/09/xmldsig#" xmlns:q1="http://ivaservizi.agenziaentrate.gov.it/docs/xsd/fatture/v1.2" '
            . 'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://ivaservizi.agenziaentrate.gov.it/docs/xsd/fatture/v1.2 http://www.fatturapa.gov.it/export/fatturazione/sdi/fatturapa/v1.2/Schema_del_file_xml_FatturaPA_versione_1.2.xsd">'
            . '<FatturaElettronicaHeader>
    <DatiTrasmissione>
      <IdTrasmittente>
        <IdPaese>IT</IdPaese>
        <IdCodice>' . $idcodice . '</IdCodice>
      </IdTrasmittente>
      <ProgressivoInvio>' . $fatturad['id'] . '</ProgressivoInvio>
      <FormatoTrasmissione>FP' . ($pa ? 'A' : 'R') . '12</FormatoTrasmissione>
      <CodiceDestinatario>' . ($cliente['codice_sdi'] != "" ? trim($cliente['codice_sdi']) : $sdi_default) . '</CodiceDestinatario>' .
            ($cliente['pec'] != "" && $cliente['codice_sdi'] == "" ? '<PECDestinatario>' . trim($cliente['pec']) . '</PECDestinatario>' : '') . '
    </DatiTrasmissione>
    <CedentePrestatore>
      <DatiAnagrafici>
        <IdFiscaleIVA>
          <IdPaese>' . $nazione_clicom . '</IdPaese>
          <IdCodice>' . $iva_clicom . '</IdCodice>
        </IdFiscaleIVA>
        <Anagrafica>
          <Denominazione>Clicom di Moriconi Nico &amp; C. Snc</Denominazione>
        </Anagrafica>
        <RegimeFiscale>RF01</RegimeFiscale>
      </DatiAnagrafici>
      <Sede>
        <Indirizzo>via Beatrice di Lorena 34/3</Indirizzo>
        <CAP>42026</CAP>
        <Comune>CANOSSA</Comune>
        <Provincia>RE</Provincia>
        <Nazione>IT</Nazione>
      </Sede>
      <Contatti>
        <Telefono>0522673093</Telefono>
        <Email>info@clicom.it</Email>
      </Contatti>
    </CedentePrestatore>
    <CessionarioCommittente>
      <DatiAnagrafici>' .
            ($cliente['piva'] != "" ?
            '<IdFiscaleIVA>
          <IdPaese>' . $nazione . '</IdPaese>
          <IdCodice>' . $cliente['piva'] . '</IdCodice>
        </IdFiscaleIVA>' :
            '') .
            '<CodiceFiscale>' . strtoupper($cf) . '</CodiceFiscale>' .
            '<Anagrafica>' .
            ($cliente['azienda'] != "" && ($cliente['piva'] != '' || ($cliente['nome'] == '' && $cliente['cognome'] == '')) ? '<Denominazione>' . str_replace("&", "&amp;", $cliente['azienda']) . '</Denominazione>' :
            '<Nome>' . $cliente['nome'] . '</Nome>
          <Cognome>' . $cliente['cognome'] . '</Cognome>') .
            '</Anagrafica>
      </DatiAnagrafici>
      <Sede>
        <Indirizzo>' . str_replace("&", "&amp;", str_replace(Array("/", "\\"), "", $cliente['indirizzo'])) . '</Indirizzo>
        <CAP>' . $cliente['cap'] . '</CAP>
        <Comune>' . $cliente['comune'] . '</Comune>
        <Provincia>' . strtoupper($cliente['provincia']) . '</Provincia>
        <Nazione>' . $nazione . '</Nazione>
      </Sede>
    </CessionarioCommittente>
  </FatturaElettronicaHeader>
    <FatturaElettronicaBody>
    <DatiGenerali>
      <DatiGeneraliDocumento>
        <TipoDocumento>' . getTipoDocumentoFE($fatturad['tipo']) . '</TipoDocumento>
        <Divisa>EUR</Divisa>
        <Data>' . $fatturad['data'] . '</Data>
        <Numero>' . ((int) $fatturad['tipo'] == 6 ? 'FPC' : '') . ((int) $fatturad['tipo'] == 5 ? 'FPC' : '') . ((int) $fatturad['tipo'] == 4 ? 'FPA' : '') . $fatturad['numero'] . '/' . $anno_fatt . '</Numero>
        <ImportoTotaleDocumento>' . number_format($fatturad['totalefatt_iva'], 2, ".", "") . '</ImportoTotaleDocumento>
      </DatiGeneraliDocumento>
    </DatiGenerali>
    <DatiBeniServizi>';
    /* voci */
    $tabella = "fatture_voci";
    $where = "idfattura = $id ORDER BY ordine";
    $dativoci = new fatture($id, $tabella);
    $voci = $dativoci->richiamaWhere($where);
    if ($voci) {
        $n = 1;
        foreach ($voci as $i => $vocid) {
            if ($vocid['qta'] > 0) {
                $descrizionearr = explode("\n", $vocid['descrizione']);
                $nomevoce = $descrizionearr[0];
                unset($descrizionearr[0]);
                $um = $vocid['um'];
                $qta = $vocid['qta'];
                $importo = $vocid['importo'];
                $sconto = $vocid['sconto'];
                $totscontato = ($importo - ($importo * $sconto / 100));
                $iva = $vocid['iva'];
                $codice_iva = $vocid['codice_iva'];
                $prezzototale = $qta * $totscontato;
                $xml .= '<DettaglioLinee>
        <NumeroLinea>' . $n . '</NumeroLinea>
        <Descrizione>' . str_replace("&", "&amp;", $vocid['descrizione']) . '</Descrizione>
        <Quantita>' . number_format($qta, 2, ".", "") . '</Quantita>
        <PrezzoUnitario>' . number_format($totscontato, 2, ".", "") . '</PrezzoUnitario>
        <PrezzoTotale>' . number_format($prezzototale, 2, ".", "") . '</PrezzoTotale>
        <AliquotaIVA>' . number_format($iva, 2, ".", "") . '</AliquotaIVA>' .
                        ($iva == 0 ? '<Natura>' . $codice_iva . '</Natura>' : '') .
                        '</DettaglioLinee>';
                $n++;
            }
        }
    }
    /* tabella iva */
    $sql = $db->prepare("SELECT sum(qta*(importo-importo*sconto/100)) as tabimponibile, sum((qta*(importo-importo*sconto/100))*iva/100) tabimposta, iva, codice_iva FROM `fatture_voci` "
            . "WHERE idfattura = ? GROUP BY iva ORDER BY iva ASC");
    $sql->execute(array($id));
    $res = $sql->fetchAll(PDO::FETCH_ASSOC);
    foreach ($res as $row) {
        $tabimponibile = $row['tabimponibile'];
        $tabimposta = $row['tabimposta'];
        $tabiva = $row['iva'];
        $codice_iva = $row['codice_iva'];
        if ($tabiva == 0) {
            $qry = "select normativa from iva where codice_iva='$codice_iva' limit 1;";
            $rs_iva = $db->prepare($qry);
            $rs_iva->execute();
            $normativa = $rs_iva->fetchColumn();
        } else {
            $normativa = '';
        }
        $xml .= '<DatiRiepilogo>
        <AliquotaIVA>' . number_format($tabiva, 2, ".", "") . '</AliquotaIVA>' .
                ($tabiva == 0 ? '<Natura>' . $codice_iva . '</Natura>' : '') .
                '<ImponibileImporto>' . number_format($tabimponibile, 2, ".", "") . '</ImponibileImporto>
        <Imposta>' . number_format($tabimposta, 2, ".", "") . '</Imposta>' .
                ($tabiva == 0 ? '<RiferimentoNormativo>' . $normativa . '</RiferimentoNormativo>' : '') .
                ($pa ? '<EsigibilitaIVA>S</EsigibilitaIVA>' : '') .
                '</DatiRiepilogo>';
    }
    $codice_pag = getCodicePagamentoFE($fatturad['metodopagamento']);
    $scad = getDati("fatture_scadenze", "WHERE idfattura = $id AND sufattura = '0' ORDER BY datascadenza ASC");
    $xml .= '
    </DatiBeniServizi>
    <DatiPagamento>' .
            '<CondizioniPagamento>' . (count($scad) > 1 ? 'TP01' : 'TP02') . '</CondizioniPagamento>';
    foreach ($scad as $scadd) {
        $dataarr = explode("-", $scadd['datascadenza']);
        $datait = $dataarr[2] . "/" . $dataarr[1] . "/" . $dataarr[0];
        $xml .= '<DettaglioPagamento>
        <Beneficiario>Clicom di Moriconi Nico &amp; C. Snc</Beneficiario>
        <ModalitaPagamento>' . $codice_pag . '</ModalitaPagamento>
        <DataScadenzaPagamento>' . $scadd['datascadenza'] . '</DataScadenzaPagamento>
        <ImportoPagamento>' . number_format($scadd['importoscadenza'], 2, ".", "") . '</ImportoPagamento>
        <IstitutoFinanziario>' . trim($banca_nome) . '</IstitutoFinanziario>
        <IBAN>' . trim($iban) . '</IBAN>
      </DettaglioPagamento>';
    }
    $xml .= '</DatiPagamento>
  </FatturaElettronicaBody>
</q1:FatturaElettronica>';
    return $xml;
}

function getCodicePagamentoFE($pagamento) {
    global $db;
    switch (true) {
        case strpos($pagamento, 'Bonifico') !== false:
            $result = 'MP05';
            break;

        case strpos($pagamento, 'Riba') !== false:
            $result = 'MP12';
            break;

        case strpos($pagamento, 'Contanti') !== false:
        case strpos($pagamento, 'Contrassegno') !== false:
            $result = 'MP01';
            break;

        case strpos($pagamento, 'credito') !== false:
            $result = 'MP08';
            break;
    }
    return $result;
}

function getAtelier($and) {
    global $db;

    $sql = $db->prepare("SELECT * FROM utenti WHERE livello = ? AND attivo = ? $and");
    try {
        $sql->execute(array(5, 1));
        $res = $sql->fetchAll(PDO::FETCH_ASSOC);
        return $res;
    } catch (PDOException $e) {
        die('Connessione fallita: ' . $e->getMessage());
    }
}