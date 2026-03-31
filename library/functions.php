<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

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
    $qry = "SELECT *, "
            . "(select addetti from utenti u where u.id=c.idatelier limit 1) as addetti,"
            . "DATE_FORMAT(datasart1, '%d/%m/%Y') as datas1, "
            . "DATE_FORMAT(datasart2, '%d/%m/%Y') as datas2, DATE_FORMAT(datasart3, '%d/%m/%Y') as datas3, DATE_FORMAT(datasart4, '%d/%m/%Y') as datas4, "
            . "DATE_FORMAT(data, '%d/%m/%Y') as datait, DATE_FORMAT(datasaldo, '%d/%m/%Y') as datas, DATE_FORMAT(datamatrimonio, '%d/%m/%Y') as datamatrimonioit, "
            . "DATE_FORMAT(datacap, '%d/%m/%Y') as datac, DATE_FORMAT(datapag1, '%d/%m/%Y') as datap1, DATE_FORMAT(datapag2, '%d/%m/%Y') as datap2, DATE_FORMAT(datapag3, '%d/%m/%Y') as datap3, "
            . "DATE_FORMAT(dataeffettuatosaldo, '%d/%m/%Y') as dataes, TIME_FORMAT(orario, '%H:%i') as orariodb FROM calendario c $where";
    //die($qry);
    $sql = $db->prepare($qry);
    try {
        $sql->execute();
        $res = $sql->fetchAll(PDO::FETCH_ASSOC);
        return $res;
    } catch (PDOException $e) {
        die('Connessione fallita: ' . $e->getMessage());
    }
}

function getDatiCalAddetti($where) {
    global $db;
    $qry = "SELECT *, DATE_FORMAT(data_cal, '%d/%m/%Y') as datas1, TIME_FORMAT(ora_da, '%H:%i') as orario_da, TIME_FORMAT(ora_a, '%H:%i') as orario_a FROM addetti_atelier $where";
    $sql = $db->prepare($qry);

    try {
        $sql->execute();
        $res = $sql->fetchAll(PDO::FETCH_ASSOC);
        return $res;
    } catch (PDOException $e) {
        die('Connessione fallita: ' . $e->getMessage());
    }
}

function getDatiCalDipendenti($where) {
    global $db;
    $qry = "SELECT *, DATE_FORMAT(data_cal, '%d/%m/%Y') as datas1, TIME_FORMAT(ora_da, '%H:%i') as orario_da, TIME_FORMAT(ora_a, '%H:%i') as orario_a,"
            . "(select CONCAT(cognome,' ',nome) from utenti u where u.id=cp.idutente limit 1) as dipendente,"
            . "(select nominativo from utenti u where u.id=cp.idatelier limit 1) as nome_atelier"
            . " FROM calendario_dipendenti cp $where";
    //die($qry);
    $sql = $db->prepare($qry);

    try {
        $sql->execute();
        $res = $sql->fetchAll(PDO::FETCH_ASSOC);
        return $res;
    } catch (PDOException $e) {
        die('Connessione fallita: ' . $e->getMessage());
    }
}

function getDatiEventiDipendenti($where) {
    global $db;
    $qry = "SELECT *, DATE_FORMAT(data_cal, '%d/%m/%Y') as datas1, TIME_FORMAT(ora_da, '%H:%i') as orario_da, TIME_FORMAT(ora_a, '%H:%i') as orario_a,"
            . "(select CONCAT(cognome,' ',nome) from utenti u where u.id=cp.idutente limit 1) as dipendente,"
            . "(select valore from eventi_tipo e where e.id=cp.tipo limit 1) as evento_tipo,"
            . "(select solo_admin from eventi_tipo e where e.id=cp.tipo limit 1) as solo_admin,"
            . "(select nominativo from utenti u where u.id=cp.idatelier limit 1) as nome_atelier"
            . " FROM eventi_dipendenti cp $where";
    //die($qry);
    $sql = $db->prepare($qry);

    try {
        $sql->execute();
        $res = $sql->fetchAll(PDO::FETCH_ASSOC);
        return $res;
    } catch (PDOException $e) {
        die('Connessione fallita: ' . $e->getMessage());
    }
}

function getDatiEventoDipendente($id) {
    global $db;
    $qry = "SELECT *, DATE_FORMAT(data_cal, '%d/%m/%Y') as datas1, TIME_FORMAT(ora_da, '%H:%i') as orario_da, TIME_FORMAT(ora_a, '%H:%i') as orario_a,"
            . "(select CONCAT(cognome,' ',nome) from utenti u where u.id=cp.idutente limit 1) as dipendente,"
            . "(select valore from eventi_tipo e where e.id=cp.tipo limit 1) as evento_tipo,"
            . "(select solo_admin from eventi_tipo e where e.id=cp.tipo limit 1) as solo_admin,"
            . "(select nominativo from utenti u where u.id=cp.idatelier limit 1) as nome_atelier"
            . " FROM eventi_dipendenti cp where id=$id limit 1;";
    //die($qry);
    $sql = $db->prepare($qry);

    try {
        $sql->execute();
        $res = $sql->fetch(PDO::FETCH_ASSOC);
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

function totaleprofit($anno, $where='') {
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

function getDati($tabella, $where = '') {
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

function maxNum($tabella, $campo, $where = '') {
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

function sendMail($maildest, $subject, $message, $mailfrom, $nomefrom, $allegato = "", $bcc = Array()) {
    $error = true;
    $mail = new PHPmailer();
    $mail->CharSet = 'utf-8';
    $mail->IsSMTP();
    $mail->Host = 'out.postassl.it';
    $mail->SMTPAuth = true;
    $mail->Port = 465;
    $mail->SMTPSecure = 'ssl';
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
    if (count($bcc) > 0) {
        foreach ($bcc as $emailBcc) {
            $mail->addBCC($emailBcc);
        }
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

function getProvinciaAll() {
    global $db;
    $sql = $db->prepare("SELECT * FROM province ORDER BY provincia");
    try {
        $sql->execute();
        $res = $sql->fetchAll(PDO::FETCH_ASSOC);
        return $res;
    } catch (PDOException $e) {
        die('Connessione fallita: ' . $e->getMessage());
    }
}

function getComuneAll() {
    global $db;
    $sql = $db->prepare("SELECT * FROM cap_localita ORDER BY comune");
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

    $sql = $db->prepare("SELECT * FROM utenti WHERE livello = ? AND attivo = ? $and order by nominativo, cognome");
    try {
        $sql->execute(array(5, 1));
        $res = $sql->fetchAll(PDO::FETCH_ASSOC);
        return $res;
    } catch (PDOException $e) {
        die('Connessione fallita: ' . $e->getMessage());
    }
}

function getOperatori($and) {
    global $db;

    $sql = $db->prepare("SELECT * FROM utenti WHERE livello = ? AND attivo = ? $and order by cognome, nome");
    try {
        $sql->execute(array(3, 1));
        $res = $sql->fetchAll(PDO::FETCH_ASSOC);
        return $res;
    } catch (PDOException $e) {
        die('Connessione fallita: ' . $e->getMessage());
    }
}

function getAtelierUser($iduser) {
    global $db;
    $qry = "select idatelier, (select nominativo from utenti u where u.id=u2.idatelier limit 1) as nome "
            . "from atelier_utente u2 where "
            . "idutente=?;";
    $rs = $db->prepare($qry);
    $valori = Array($iduser);
    $rs->execute($valori);
    return $rs->fetchAll(PDO::FETCH_ASSOC);
}

function sendsms($telefonosms, $messaggiosms) {

    $usersms = 'comeinunafav';
    $passwordsms = 'C0m3i4U4Af';
    $sendersms = "COMEINUNAFA";

    //set POST variables
    $url = 'http://sms.clicom.it/sms/send.php';
    $fields = array(
        'user' => urlencode($usersms),
        'pass' => urlencode($passwordsms),
        'rcpt' => urlencode($telefonosms),
        'data' => urlencode($messaggiosms),
        'sender' => urlencode($sendersms),
        'qty' => 'h',
        'operation' => 'TEXT'
    );

    //url-ify the data for the POST
    foreach ($fields as $key => $value) {
        $fields_string .= $key . '=' . $value . '&';
    }
    rtrim($fields_string, '&');

    //open connection
    $ch = curl_init();

    //set the url, number of POST vars, POST data
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, count($fields));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    //execute post
    $result = curl_exec($ch);
    //close connection
    curl_close($ch);

    return $result;
}

function loginSMS($username, $password) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, BASEURL .
            'login?username=' . $username .
            '&password=' . $password);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    $info = curl_getinfo($ch);
    curl_close($ch);

    if ($info['http_code'] != 200) {

        return null;
    }

    return explode(";", $response);
}

function sendSMSnew($auth, $sendSMS) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, BASEURL . 'sms');
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-type: application/json',
        'user_key: ' . $auth[0],
        'Session_key: ' . $auth[1]
    ));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($sendSMS));
    $response = curl_exec($ch);
    $info = curl_getinfo($ch);
    curl_close($ch);

    if ($info['http_code'] != 201) {
        return null;
    }

    return json_decode($response);
}

function formatDateDb($date, $delimiter = "-") {
    if ($date == "" || $date == '0000-00-00')
        return "";
    list($g, $m, $a) = explode("/", $date);
    return $a . $delimiter . $m . $delimiter . $g;
}

function formatDate($date, $delimiter = "/") {
    if ($date == "" || $date == '0000-00-00')
        return "";
    list($a, $m, $g) = explode("-", $date);
    return $g . $delimiter . $m . $delimiter . $a;
}

function formatDateOra($date_ora, $delimiter = "/") {
    if ($date_ora == "" || $date_ora == '0000-00-00')
        return "";
    list($date, $ora) = explode(" ", $date_ora);
    list($a, $m, $g) = explode("-", $date);
    return $g . $delimiter . $m . $delimiter . $a . ' ' . $ora;
}

function getOrari() {
    global $db;
    $ch = curl_init(URL_WS . "getOrari/");
    $data = "user=" . USER_WS . "&pw=" . urlencode(PWD_WS);
    if (count($_POST) > 0) {
        foreach ($_POST as $k => $v) {
            $data .= "&$k=$v";
        }
    }
    //echo "$data<br>";
    curl_setopt_array($ch, array(
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_RETURNTRANSFER => TRUE,
        CURLOPT_HTTPHEADER => array(
            //'Content-Type: application/json',
            'Access-Control-Allow-Origin: *',
            'Content-Length: ' . strlen($data),
            'Content-Type:application/x-www-form-urlencoded'
        ),
        CURLOPT_POSTFIELDS => $data
    ));
    $res = curl_exec($ch);
    return json_decode($res, JSON_INVALID_UTF8_IGNORE);
}

function verificaToken($token) {
    global $db;
    $qry = "select * from log_accessi where MD5(token)=? and expire >= " . time() . " limit 1;";
    $rs = $db->prepare($qry);
    $valori = Array($token);
    $rs->execute($valori);
    if ($rs->RowCount() > 0) {
        $result = $rs->fetch(PDO::FETCH_ASSOC);
    } else {
        $result = Array();
    }
    return $result;
}

function inviaNotificaPushUtente($idutente, $idnotifica) {
    global $db;
    define('URL_PUSH', 'https://sendnotification-cnvsqtjuia-ew.a.run.app');
    $notifica = getNotifica($idnotifica);
    $tokens = getTokensUtente($idutente);
    $data = Array(
        'tokens' => $tokens,
        'message' => Array(
            'title' => $notifica['titolo_push'],
            'body' => $notifica['descrizione_push']
        )
    );
    $json = json_encode($data, JSON_INVALID_UTF8_IGNORE);
    //return $data;
    $ch = curl_init(URL_PUSH);
    curl_setopt_array($ch, array(
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_RETURNTRANSFER => TRUE,
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
            'Access-Control-Allow-Origin: *',
            'Content-Length: ' . strlen($json),
            'Authorization: zRPaU6Di5v0X',
        //'Content-Type:application/x-www-form-urlencoded'
        ),
        CURLOPT_POSTFIELDS => $json
    ));
    $response = curl_exec($ch);
    $json = json_decode($response, true);
    if ($json['success']) {
        $tokens = $json['newTokens'];
        if (count($tokens) > 0) {
            $qry = "delete from token_utente where idutente=?;";
            $rs = $db->prepare($qry);
            $valori = Array($idutente);
            $rs->execute($valori);
            $qry = "insert into token_utente (idutente, token) values (?,?);";
            $rs = $db->prepare($qry);
            foreach ($tokens as $token) {
                if ($token != "") {
                    $valori = Array($idutente, $token);
                    $rs->execute($valori);
                }
            }
        }
        $result = true;
    } else {
        $result = false;
    }
    return $result;
}

function inviaMessaggio($idmessaggio) {
    global $db;
    $arrUtenti = $arrUtentiAtelier = $arrUtentiUsers = $atelier = $users = $ruoli = $stati = [];
    $cols = getDati("messaggio_atelier", "where idmessaggio=" . $idmessaggio);
    foreach ($cols as $col) {
        $atelier[] = $col['idatelier'];
    }
    $cols = getDati("messaggio_users", "where idmessaggio=" . $idmessaggio);
    foreach ($cols as $col) {
        $users[] = $col['idutente'];
    }
    $cols = getDati("messaggio_ruolo", "where idmessaggio=" . $idmessaggio);
    foreach ($cols as $col) {
        $ruoli[] = $col['ruolo'];
    }
    $cols = getDati("messaggio_stato", "where idmessaggio=" . $idmessaggio);
    foreach ($cols as $col) {
        $stati[] = $col['stato'];
    }
    if (count($atelier) > 0) {
        foreach ($atelier as $idatelier) {
            $qry = "SELECT idutente FROM `atelier_utente` WHERE idatelier=$idatelier limit 1;";
            $rs = $db->prepare($qry);
            $rs->execute();
            $idutente = $rs->fetchColumn();
            if (!in_array($idutente, $arrUtentiAtelier)) {
                $arrUtentiAtelier[] = $idutente;
            }
        }
    }
    if (count($users) > 0) {
        foreach ($users as $idutente) {
            if (!in_array($idutente, $arrUtentiUsers)) {
                $arrUtentiUsers[] = $idutente;
            }
        }
    }
    if (count($ruoli) > 0) {
        foreach ($ruoli as $ruolo) {
            $qry = "SELECT id FROM `utenti` WHERE ruolo=$ruolo and attivo=1;";
            $rs = $db->prepare($qry);
            $rs->execute();
            while ($col = $rs->fetch(PDO::FETCH_ASSOC)) {
                $idutente = $col['id'];
                if (!in_array($idutente, $arrUtenti)) {
                    $arrUtenti[] = $idutente;
                }
//                if (count($arrUtentiAtelier) > 0) {
//                    if (in_array($idutente, $arrUtentiAtelier) && !in_array($idutente, $arrUtenti)) {
//                        $arrUtenti[] = $idutente;
//                    }
//                } elseif (count($arrUtentiUsers) > 0) {
//                    if (in_array($idutente, $arrUtentiUsers) && !in_array($idutente, $arrUtenti)) {
//                        $arrUtenti[] = $idutente;
//                    }
//                } else {
//                    if (!in_array($idutente, $arrUtenti)) {
//                        $arrUtenti[] = $idutente;
//                    }
//                }
            }
        }
    }
    if (count($stati) > 0) {
        foreach ($stati as $stato) {
            $qry = "SELECT id FROM `utenti` WHERE stato_dipendente=$stato and attivo=1;";
            $rs = $db->prepare($qry);
            $rs->execute();
            while ($col = $rs->fetch(PDO::FETCH_ASSOC)) {
                $idutente = $col['id'];
                if (!in_array($idutente, $arrUtenti)) {
                    $arrUtenti[] = $idutente;
                }
//                if (count($arrUtentiAtelier) > 0) {
//                    if (in_array($idutente, $arrUtentiAtelier) && !in_array($idutente, $arrUtenti)) {
//                        $arrUtenti[] = $idutente;
//                    }
//                } elseif (count($arrUtentiUsers) > 0) {
//                    if (in_array($idutente, $arrUtentiUsers) && !in_array($idutente, $arrUtenti)) {
//                        $arrUtenti[] = $idutente;
//                    }
//                } else {
//                    if (!in_array($idutente, $arrUtenti)) {
//                        $arrUtenti[] = $idutente;
//                    }
//                }
            }
        }
    }
    //var_dump($ruoli, $stati, $arrUtentiAtelier, $arrUtentiUsers);
    if (count($ruoli) == 0 && count($stati) == 0) {
        foreach ($arrUtentiAtelier as $idutente) {
            if (!in_array($idutente, $arrUtenti)) {
                $arrUtenti[] = $idutente;
            }
        }
        foreach ($arrUtentiUsers as $idutente) {
            if (!in_array($idutente, $arrUtenti)) {
                $arrUtenti[] = $idutente;
            }
        }
    }
    //var_dump($arrUtenti);
    //die();
    if (count($arrUtenti) > 0) {
        $qry = "insert into log_invio_messaggio (data_ora, token, idutente, idmessaggio, inviato) values (?,?,?,?,?);";
        $rs = $db->prepare($qry);
        foreach ($arrUtenti as $idutente) {
            $tokens = getTokensUtente($idutente);
            foreach ($tokens as $token) {
                if ($token != '') {
                    $valori = Array(date('Y-m-d H:i:s'), $token, $idutente, $idmessaggio, 0);
                    $rs->execute($valori);
                }
            }
        }
        $qry = "insert into invio_messaggio (data_ora, idmessaggio, inviato) values (?,?,?);";
        $rs = $db->prepare($qry);
        $valori = Array(date('Y-m-d H:i:s'), $idmessaggio, 0);
        $rs->execute($valori);
        $error = 'Il messaggio e\' stato inserito nella coda di invio e sara\' processato nei prossimi minuti';
    } else {
        $error = 'Attenzione! Nessun utente trovato con i filtri impostati';
    }
    return $error;
}

function getTokensUtente($idutente) {
    global $db;
    $qry = "select * from token_utente where idutente=?;";
    $rs = $db->prepare($qry);
    $valori = Array($idutente);
    $rs->execute($valori);
    $tokens = Array();
    while ($col = $rs->fetch(PDO::FETCH_ASSOC)) {
        $tokens[] = $col['token'];
    }
    return $tokens;
}

function getNotifica($idnotifica) {
    global $db;
    $qry = "select * from messaggi_app where id=? limit 1;";
    $rs = $db->prepare($qry);
    $valori = Array($idnotifica);
    $rs->execute($valori);
    $col = $rs->fetch(PDO::FETCH_ASSOC);
    return $col;
}

function logAccesso($idutente, $operazione, $idatelier = 0) {
    global $db;
    $qry = "insert into log_accessi_agenda (idutente, data_ora, ip, operazione, idatelier) values (?,NOW(),?,?,?);";
    $rs = $db->prepare($qry);
    $valori = Array($idutente, $_SERVER['REMOTE_ADDR'], $operazione, $idatelier);
    $rs->execute($valori);
}

function getRuoloUtente($ruolo = "") {
    global $db;
    if ($ruolo != "") {
        $qry = "select valore from ruolo where id=? limit 1;";
        $rs = $db->prepare($qry);
        $rs->execute(Array($ruolo));
        $result = $rs->fetchColumn();
    } else {
        $qry = "select id, valore from ruolo order by id;";
        $rs = $db->prepare($qry);
        $rs->execute();
        while ($col = $rs->fetch(PDO::FETCH_ASSOC)) {
            $result[] = Array(
                'id' => $col['id'],
                'nome' => $col['valore']
            );
        }
    }
    return $result;
}

function callApi($endpoint, $request) {
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => $endpoint,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => $request,
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
            'X-Spoki-Api-Key: ' . API_SPOKI
        )
    ));

    $response = curl_exec($curl);
    curl_close($curl);
    return $response;
}

function callWS($endpoint, $data) {
    $ch = curl_init($endpoint);
    $data .= "&user=" . USER_WS . "&pw=" . urlencode(PWD_WS);
    curl_setopt_array($ch, array(
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_RETURNTRANSFER => TRUE,
        CURLOPT_HTTPHEADER => array(
            //'Content-Type: application/json',
            'Access-Control-Allow-Origin: *',
            'Content-Length: ' . strlen($data),
            'Content-Type:application/x-www-form-urlencoded'
        ),
        CURLOPT_POSTFIELDS => $data
    ));
//echo $json_req;
// Send the request
    $response = curl_exec($ch);
    return $response;
}

function logSpoki($request, $response) {
    global $db;
    $qry = "insert into log_spoki (data_ora, request, response) values (NOW(),?,?);";
    $rs = $db->prepare($qry);
    $valori = Array($request, $response);
    $rs->execute($valori);
}

function logReminder($qry, $time, $tipo) {
    global $db;
    $qry_log = "insert into log_reminder (data_ora, query, time, tipo) values (NOW(), ?, ?, ?);";
    $rs = $db->prepare($qry_log);
    $valori = Array($qry, $time, $tipo);
    $rs->execute($valori);
}

function getDipendentiAtelier($idatelier, $attivo = false) {
    global $db;
    $qry = "select id, cognome, nome,"
            . "(select ore_settimana from utenti_dipendenti ud where ud.idutente=u.id limit 1) as ore_settimana"
            . " from utenti u where livello='3' and id in (select idutente from atelier_utente where idatelier=$idatelier) " . ($attivo ? " and attivo=1" : "") . " order by cognome;";
    $rs = $db->prepare($qry);
    $rs->execute();
    $cols = $rs->fetchAll(PDO::FETCH_ASSOC);
    return $cols;
}

function getDipendentiAll() {
    global $db;
    $qry = "select id, cognome, nome,ruolo,"
            . "(select ore_settimana from utenti_dipendenti ud where ud.idutente=u.id limit 1) as ore_settimana,"
            . "(select stipendio_netto from utenti_dipendenti ud where ud.idutente=u.id limit 1) as stipendio_netto,"
            . "(select nominativo from utenti u2 where u2.id=u.idatelier limit 1) as nome_atelier,"
            . "(select nominativo from utenti u2 where u2.id=(select idatelier_sede from utenti_dipendenti ud where ud.idutente=u.id limit 1) limit 1) as nome_sede,"
            . "(select diraff from utenti u2 where u2.id=(select idatelier_sede from utenti_dipendenti ud where ud.idutente=u.id limit 1) limit 1) as diraff"
            . " from utenti u where livello='3' and attivo=1 order by cognome;";
    $rs = $db->prepare($qry);
    $rs->execute();
    $cols = $rs->fetchAll(PDO::FETCH_ASSOC);
    return $cols;
}

function getEventiTipo() {
    global $db;
    $qry = "select * from eventi_tipo order by id;";
    $rs = $db->prepare($qry);
    $rs->execute();
    $cols = $rs->fetchAll(PDO::FETCH_ASSOC);
    return $cols;
}

function getEventoTipo($tipo) {
    global $db;
    $qry = "select valore from eventi_tipo where id=? limit 1;";
    $rs = $db->prepare($qry);
    $rs->execute(Array($tipo));
    $col = $rs->fetchColumn();
    return $col;
}

function getEventoDipendente($id) {
    global $db;
    $qry = "select * from eventi_dipendenti where id=? limit 1;";
    $rs = $db->prepare($qry);
    $rs->execute(Array($id));
    $col = $rs->fetch(PDO::FETCH_ASSOC);
    return $col;
}

function getSmDmStore($store_code, $year, $month, $type, $ruolo) {
    global $db;
    $qry = "select * from budget_periods where year=? and month=? limit 1;";
    $rs = $db->prepare($qry);
    $valori = [$year, $month];
    $rs->execute($valori);
    $period = $rs->fetch(PDO::FETCH_ASSOC);
//var_dump($period);
    $period_id = (int) $period['id'];
    $qry = "select $type from store_budget_targets where period_id=$period_id and store_id=$store_code limit 1;";
    $rs = $db->prepare($qry);
    $rs->execute();
    $idutente = (int) $rs->fetchColumn();
    if ($idutente > 0) {
        $qry = "select id, nome,cognome from utenti where id=$idutente limit 1;";
        //die($qry);
        //echo "$qry<br>";
        $rs = $db->prepare($qry);
        $rs->execute();
        if ($rs->RowCount() > 0) {
            $col = $rs->fetch(PDO::FETCH_ASSOC);
        } else {
            $col = ['id' => '', 'nome' => '', 'cognome' => ''];
        }
    } else {
        $col = ['id' => '', 'nome' => '', 'cognome' => ''];
    }

    return $col;
}

function getClassificaGara($pdo, $month, $year, $gara, $village = null) {
    $qry = "select *, (select gara$gara from classifiche_update where month=? and year=? limit 1) as last_update from "
            . "classifica_gara$gara where month=? and year=? " . (!is_null($village) ? " and village=$village" : "") . " order by rank;";
    //echo "$qry<br>";
    $rs = $pdo->prepare($qry);
    $valori = [$month, $year, $month, $year];
    $rs->execute($valori);
    $cols = $rs->fetchAll(PDO::FETCH_ASSOC);
    return $cols;
}

function hasPremio50Perc($pdo, $month, $year, $store_code) {
    $result = false;
    $start = '2026-03-01';
    if($month < 10) {
        $m = '0'.$month;
    } else {
        $m = $month;
    }
    $date = "$year-$m-01";
    if ($date >= $start) {
        $qry = "select * from "
                . "classifica_gara4 where month=? and year=? and store_code=? limit 1;";
        $qry_debug = "select * from "
                . "classifica_gara4 where month=$month and year=$year and store_code=$store_code limit 1;";
        //echo "$qry_debug<br>";
        $rs = $pdo->prepare($qry);
        $valori = [$month, $year, $store_code];
        $rs->execute($valori);
        $col = $rs->fetch(PDO::FETCH_ASSOC);
        if ($col['village'] == 1) {
            if ((float) $col['conv'] < 48) {
                $result = true;
            }
        } else {
            if ((float) $col['conv'] < 34) {
                $result = true;
            }
        }
    }
    return $result;
}
