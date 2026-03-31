<?php
// =============================================================
// API — Report Corsi Necessari
// =============================================================
// Genera il report aggregato di quanti corsi/aggiornamenti
// sono necessari per ogni ragione sociale.
//
// Corrisponde al foglio REPORT_CORSI del vecchio sistema Sheets.
//
// Logica:
//   - "CORSO"        → dipendente senza attestato (mai formato)
//   - "AGGIORNAMENTO"→ attestato presente ma scaduto
//   - "DVR"          → negozio con DVR da emettere o aggiornare
//
// Metodo HTTP: GET
// Risposta:    JSON array { ragioneSociale, tipoRichiesta, nomeCorso, quantita }
// =============================================================

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/logic.php';

try {
    $pdo  = getConnection();
    $data = getCoreData($pdo);
    $rows = buildCoverageRows($data);

    // --- Aggrega i fabbisogni formativi per ragione sociale ---
    // Chiave del contatore: "RAGIONE SOCIALE|||TIPO RICHIESTA|||NOME CORSO"
    $counter = [];

    foreach ($rows as $row) {
        // Raggruppa per ragione sociale (non per singolo negozio)
        $rs      = trim($row['ragioneSociale']);
        $corso   = trim($row['ruolo']);
        $dettaglio = $row['dettaglio'];

        if (!$rs || !$corso) continue;

        // Determina il tipo di richiesta in base all'esito
        if ($dettaglio === 'ATTESTATO ASSENTE') {
            $tipo = 'CORSO';            // Prima formazione necessaria
        } elseif ($dettaglio === 'ATTESTATO SCADUTO') {
            $tipo = 'AGGIORNAMENTO';    // Rinnovo necessario
        } else {
            continue; // COPERTO → nessuna richiesta
        }

        $key = "{$rs}|||{$tipo}|||{$corso}";
        $counter[$key] = ($counter[$key] ?? 0) + 1;
    }

    // --- Aggiunge i fabbisogni DVR per negozio ---
    foreach ($data['negoziMap'] as $negozioInfo) {
        $rs     = trim($negozioInfo['ragioneSociale']);
        $statoDvr = $negozioInfo['statoDvr'];

        if (!$rs) continue;

        if ($statoDvr === 'DA EMETTERE') {
            $key = "{$rs}|||DVR|||DVR NUOVO";
            $counter[$key] = ($counter[$key] ?? 0) + 1;
        } elseif ($statoDvr === 'DA AGGIORNARE') {
            $key = "{$rs}|||DVR|||AGGIORNAMENTO DVR";
            $counter[$key] = ($counter[$key] ?? 0) + 1;
        }
    }

    // --- Ordina e costruisce l'output finale ---
    // Ordine: ragione sociale → tipo richiesta → nome corso
    ksort($counter);

    $output = [];
    foreach ($counter as $key => $quantita) {
        [$ragioneSociale, $tipoRichiesta, $nomeCorso] = explode('|||', $key, 3);
        $output[] = [
            'ragioneSociale' => $ragioneSociale,
            'tipoRichiesta'  => $tipoRichiesta,
            'nomeCorso'      => $nomeCorso,
            'quantita'       => $quantita,
        ];
    }

    echo json_encode($output);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
