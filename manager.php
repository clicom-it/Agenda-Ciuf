<?php

include './library/connessione.php';
include './library/controllo.php';
include './library/config.php';
include './library/functions.php';
include './library/basic.class.php';
/* vendite */
//$qry = "select id as idcalendario, idutente, idcliente, data, orario, LOWER(nome) as nome_cliente, LOWER(cognome) as cognome_cliente, email, nometipoabito, nomemodabito, sesso, CAST(totalespesa AS DECIMAL(10,2)) as totale, 
//(select nominativo from utenti u where u.id=c.idatelier limit 1) as atelier, nomepagamentocaparra, nomepagamentosaldo, CAST(prezzoabito AS DECIMAL(10,2)) as prezzo_abito,
//(select LOWER(CONCAT(nome, ' ',cognome)) from utenti u where u.id=c.idutente limit 1) as dipendente 
// from calendario c where data between '2020-01-01' and '2025-12-31' and acquistato='1' and nomemodabito!='';";
//$rs = $db->prepare($qry);
//$rs->execute();
//$cols = $rs->fetchAll(PDO::FETCH_ASSOC);
//$json = json_encode($cols, JSON_INVALID_UTF8_IGNORE);
//header('Content-disposition: attachment; filename=vendite_2020-2025.json');
//header('Content-type: application/json');
//echo $json;
/* atelier */
//$qry = "SELECT `id` as idatelier, `nominativo` as nome_atelier, `email`, `codicefiscale`, `piva`, `telefono`, `cellulare`, `nazione`, `regione`, `provincia`, `comune`, `cap`, `indirizzo`, `attivo`, `centro_costo`, `aperture`, `inizio0`, `inizio1`, `inizio2`, `inizio3`, `inizio4`, `inizio5`, `inizio6`, `fine0`, `fine1`, `fine2`, `fine3`, `fine4`, `fine5`, `fine6`, `iniziop0`, `iniziop1`, `iniziop2`, `iniziop3`, `iniziop4`, `iniziop5`, `iniziop6`, `finep0`, `finep1`, `finep2`, `finep3`, `finep4`, `finep5`, `finep6`, `ragionesociale`, `sedelegale`, `datipagamento`, `solo_sartoria`, `addetti`, `online`, `patrono`, `data_apertura`, `aperture_spot`, `chiusure_spot`, `chiuso_dal`, `chiuso_al`, `date_trunk`, `titolo_trunk`, `orari_trunk`, `app_trunk`, `non_gestito` FROM `utenti` WHERE livello='5'";
//$rs = $db->prepare($qry);
//$rs->execute();
//$cols = $rs->fetchAll(PDO::FETCH_ASSOC);
//$json = json_encode($cols, JSON_INVALID_UTF8_IGNORE);
//header('Content-disposition: attachment; filename=atelier-2025.json');
//header('Content-type: application/json');
//echo $json;
/* dipendenti */
$qry = "SELECT `id` as idutente, `nome`, `cognome`, (select valore from ruolo r where r.id=u.ruolo limit 1) as ruolo, `email`, `codicefiscale`, `telefono`, `cellulare`, `nazione`, `regione`, `provincia`, `comune`, `cap`, `indirizzo`, `stato_dipendente`, `data_nascita`, `iban`, `provincia_nascita`, `comune_nascita` FROM `utenti` u WHERE livello='3'";
$rs = $db->prepare($qry);
$rs->execute();
$cols = $rs->fetchAll(PDO::FETCH_ASSOC);
foreach ($cols as $i => $col) {
    $qry = "select id as idatelier, nominativo as nomeatelier from utenti where livello='5' and id in (select idatelier from atelier_utente where idutente={$col['idutente']});";
    //die($qry);
    $rs = $db->prepare($qry);
    $rs->execute();
    $atelier = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cols[$i]['atelier_gestiti'] = $atelier;
}
$json = json_encode($cols, JSON_INVALID_UTF8_IGNORE);
header('Content-disposition: attachment; filename=dipendenti-2025.json');
header('Content-type: application/json');
echo $json;
/* clienti */
//$qry = "SELECT id as idcliente, LOWER(nome), LOWER(cognome), email, telefono, cellulare, (select nominativo from utenti u where u.id=cf.idatelier limit 1) as nome_atelier, provincia as provincia_cliente, comune as comune_cliente FROM `clienti_fornitori` cf WHERE tipo='1'";
//$rs = $db->prepare($qry);
//$rs->execute();
//$cols = $rs->fetchAll(PDO::FETCH_ASSOC);
//$json = json_encode($cols, JSON_INVALID_UTF8_IGNORE);
//header('Content-disposition: attachment; filename=clienti-2025.json');
//header('Content-type: application/json');
//echo $json;
?>