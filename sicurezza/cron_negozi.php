<?php

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';
$azione = $_GET['azione'] ?? $_POST['azione'] ?? '';
$pdo = getConnection();
if ($azione == '') {
    $qry = "select * from utenti where livello='5' and attivo=1;";
    $stores = dbFetchAll($pdo, $qry);
    foreach ($stores as $store) {
        $nominativo = $store['nominativo'];
        $qry = "select id from sic_negozi where nome_negozio=? limit 1;";
        $check_negozio = dbFetchAll($pdo, $qry, [$nominativo]);
        if (count($check_negozio) == 0) {
            $qry = '
                INSERT INTO sic_negozi
                    (nome_negozio, ragione_sociale, mail, via, localita, cap, provincia, tipo)
                VALUES
                    (:nome, :rs, :mail, :via, :loc, :cap, :prov, :tipo)
            ';
            dbExecute($pdo, $qry, [
                ':nome' => $nominativo,
                ':rs' => trim($store['ragionesociale'] ?? ''),
                ':mail' => trim($store['email'] ?? ''),
                ':via' => trim($store['indirizzo'] ?? ''),
                ':loc' => trim($store['comune'] ?? ''),
                ':cap' => trim($store['cap'] ?? ''),
                ':prov' => strtoupper(trim($store['provincia'] ?? '')),
                ':tipo' => ($store['diraff'] == 'd' ? 'diretto':'indiretto'),
            ]);
        } else {
            $idstore = $check_negozio[0]['id'];
            $qry = '
                update sic_negozi
                    set nome_negozio = ?, ragione_sociale = ?, mail = ?, via = ?, localita = ?, cap = ?, provincia = ?, tipo = ? 
                where id = ?
            ';
            $rowCount = dbExecute($pdo, $qry, [
                $nominativo,
                trim($store['ragionesociale'] ?? ''),
                trim($store['email'] ?? ''),
                trim($store['indirizzo'] ?? ''),
                trim($store['comune'] ?? ''),
                trim($store['cap'] ?? ''),
                strtoupper(trim($store['provincia'] ?? '')),
                ($store['diraff'] == 'd' ? 'diretto':'indiretto'),
                $idstore
            ]);
            //echo "$rowCount<br>";
        }
    }
}