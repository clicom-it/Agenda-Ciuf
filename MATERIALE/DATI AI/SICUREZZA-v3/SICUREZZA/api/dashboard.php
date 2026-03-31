<?php
// =============================================================
// API — Dati Dashboard Sicurezza
// =============================================================
// Calcola e restituisce lo stato di copertura di tutti i negozi.
//
// Equivale alla funzione ricostruisciDashboardSicurezza() + al
// foglio DASHBOARD SICUREZZA del vecchio sistema Sheets.
//
// Metodo HTTP: GET
// Risposta:    JSON array di oggetti negozio con stato semaforo
// =============================================================

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: same-origin');

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/logic.php';

try {
    $pdo  = getConnection();
    $data = getCoreData($pdo);         // Carica tutti i dati dal DB
    $rows = buildCoverageRows($data);  // Calcola la copertura per ogni negozio/dipendente

    // --- Inizializza le statistiche per ogni negozio ---
    $storeStats = [];
    foreach ($data['negoziMap'] as $key => $negozio) {
        $storeStats[$key] = [
            'nome'                 => $negozio['nome'],
            'ragioneSociale'       => $negozio['ragioneSociale'],
            'mail'                 => $negozio['mail'],
            'localita'             => $negozio['localita'],
            'provincia'            => $negozio['provincia'],
            // Formatta le date in italiano per l'interfaccia
            'dataDvr'              => $negozio['dataDvr']              ? $negozio['dataDvr']->format('d/m/Y')              : null,
            'dataUltimaVariazione' => $negozio['dataUltimaVariazione'] ? $negozio['dataUltimaVariazione']->format('d/m/Y') : null,
            'statoDvr'             => $negozio['statoDvr'],
            'nonCoperti'           => 0,       // Quanti ruoli non sono coperti
            'scaduti'              => 0,        // Quanti attestati sono scaduti
            'dettaglio'            => [],       // Lista dipendenti + esito per espansione card
        ];
    }

    // --- Aggrega le righe di copertura per negozio ---
    foreach ($rows as $row) {
        $key = normalizeKey($row['negozio']);
        if (!isset($storeStats[$key])) continue;

        // Aggiunge il dettaglio per la vista espansa
        $storeStats[$key]['dettaglio'][] = [
            'ruolo'        => $row['ruolo'],
            'nome'         => $row['nome'],
            'inquadramento'=> $row['inquadramento'],
            'esito'        => $row['esito'],
            'dettaglio'    => $row['dettaglio'],
            'dataScadenza' => $row['dataScadenza'] instanceof DateTime
                              ? $row['dataScadenza']->format('d/m/Y')
                              : null,
        ];

        // Incrementa i contatori
        if ($row['esito'] === 'NON COPERTO') {
            $storeStats[$key]['nonCoperti']++;
            if ($row['dettaglio'] === 'ATTESTATO SCADUTO') {
                $storeStats[$key]['scaduti']++;
            }
        }
    }

    // --- Calcola lo stato globale "semaforo" per ogni negozio ---
    // ROSSO   (critico):  DVR da emettere OPPURE 3+ ruoli non coperti
    // GIALLO  (parziale): DVR da aggiornare OPPURE 1-2 ruoli non coperti
    // VERDE   (coperto):  DVR ok E tutti i ruoli coperti
    foreach ($storeStats as &$store) {
        $dvr = $store['statoDvr'];
        $nc  = $store['nonCoperti'];

        if ($dvr === 'DA EMETTERE' || $nc >= 3) {
            $store['globalStatus'] = 'critico';
        } elseif ($dvr === 'DA AGGIORNARE' || $nc > 0) {
            $store['globalStatus'] = 'parziale';
        } else {
            $store['globalStatus'] = 'coperto';
        }
    }
    unset($store); // Pulisce il riferimento dell'ultimo ciclo

    // Restituisce come array indicizzato (non oggetto)
    echo json_encode(array_values($storeStats));

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
