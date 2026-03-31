<?php
// =============================================================
// API — Integrazione Piattaforma Sicurezza Esterna
// =============================================================
// Gestisce le due operazioni principali con la piattaforma:
//
// POST  api/piattaforma.php?azione=registra
//   → Registra un singolo dipendente sulla piattaforma
//
// POST  api/piattaforma.php?azione=polling
//   → Polling: per ogni dipendente attivo e incluso nel percorso
//     sicurezza, chiede lo storico corsi alla piattaforma e
//     aggiorna automaticamente sic_attestati.
//     Da chiamare via CRON ogni 24h (o frequenza configurata).
//
// GET   api/piattaforma.php?azione=corsi_disponibili
//   → Recupera l'elenco corsi dalla piattaforma (per il tab Admin)
//
// ⚠️ PREREQUISITI DA CONFIGURARE IN config.php:
//   - PIATTAFORMA_API_URL
//   - PIATTAFORMA_API_KEY
//   - PIATTAFORMA_ENDPOINT_* (endpoint specifici)
//   - PIATTAFORMA_RESP_* (nomi campi nella risposta JSON)
// =============================================================

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';

$pdo    = getConnection();
$azione = $_GET['azione'] ?? $_POST['azione'] ?? '';


// =============================================================
// HELPER: chiamata HTTP alla piattaforma
// =============================================================

/**
 * Esegue una chiamata cURL alla piattaforma sicurezza.
 * Aggiunge automaticamente l'header di autenticazione se configurato.
 *
 * @param string $url     URL completo
 * @param string $method  'GET' | 'POST'
 * @param array  $data    Dati da inviare (per POST come JSON, per GET come querystring)
 * @return array          ['body' => string, 'status' => int, 'error' => string]
 */
function callPiattaforma(string $url, string $method = 'GET', array $data = []): array
{
    $ch = curl_init();
    $headers = ['Accept: application/json'];

    if (PIATTAFORMA_API_KEY !== '') {
        $headers[] = PIATTAFORMA_API_TOKEN_HEADER . ': ' . PIATTAFORMA_API_KEY;
    }

    if ($method === 'GET' && $data) {
        $url .= '?' . http_build_query($data);
    }

    curl_setopt($ch, CURLOPT_URL,            $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT,        PIATTAFORMA_API_TIMEOUT);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

    if ($method === 'POST') {
        $headers[] = 'Content-Type: application/json';
        curl_setopt($ch, CURLOPT_POST,       true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }

    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $body    = curl_exec($ch);
    $status  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error   = curl_error($ch);
    curl_close($ch);

    return ['body' => $body, 'status' => $status, 'error' => $error];
}

/**
 * Scrive un record nel log del modulo (tabella sic_log).
 */
function logSicurezza(PDO $pdo, string $tipo, string $messaggio): void
{
    try {
        dbExecute($pdo,
            'INSERT INTO sic_log (tipo, messaggio) VALUES (?, ?)',
            [$tipo, $messaggio]
        );
    } catch (Exception $e) {
        error_log('[SIC LOG ERROR] ' . $e->getMessage());
    }
}


// =============================================================
// REGISTRA UN SINGOLO DIPENDENTE SULLA PIATTAFORMA
// =============================================================
if ($azione === 'registra') {
    $dipId = (int)($_POST['id'] ?? 0);
    if (!$dipId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID dipendente mancante']);
        exit;
    }

    $dip = dbFetchOne($pdo, 'SELECT * FROM sic_dipendenti WHERE id = ?', [$dipId]);
    if (!$dip) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Dipendente non trovato']);
        exit;
    }

    if ($dip['piattaforma_registrato']) {
        echo json_encode(['success' => true, 'message' => 'Già registrato']);
        exit;
    }

    // Costruisce il payload POST per la piattaforma con i nomi campi configurati
    $dataNascita = $dip['data_nascita']
        ? (new DateTime($dip['data_nascita']))->format(PIATTAFORMA_DATA_FORMAT_INVIO)
        : '';

    $payload = [
        PIATTAFORMA_POST_COGNOME      => $dip['cognome_nome'], // es. "ROSSI MARIO" — il dev può separarlo se serve
        PIATTAFORMA_POST_NOME         => '',                   // i dati arrivano come "COGNOME NOME" dall'API
        PIATTAFORMA_POST_CF           => $dip['codice_fiscale']  ?? '',
        PIATTAFORMA_POST_MANSIONE     => $dip['inquadramento']   ?? '',
        PIATTAFORMA_POST_CITTA        => $dip['citta_nascita']   ?? '',
        PIATTAFORMA_POST_DATA_NASCITA => $dataNascita,
        PIATTAFORMA_POST_SESSO        => $dip['sesso']           ?? '',
        PIATTAFORMA_POST_EMAIL        => $dip['email']           ?? '',
        PIATTAFORMA_POST_PASSWORD     => PIATTAFORMA_PASSWORD_DEFAULT,
    ];

    // ⚠️ NOTA: il gestionale restituisce nome e cognome come "COGNOME NOME" separati.
    // La piattaforma potrebbe richiedere nome e cognome separati.
    // Il developer deve adattare la logica di split qui o nell'API del gestionale.
    // Esempio split: [$cognome, $nome] = explode(' ', $dip['cognome_nome'], 2);
    // $payload[PIATTAFORMA_POST_COGNOME] = $cognome;
    // $payload[PIATTAFORMA_POST_NOME]    = $nome;

    $url = PIATTAFORMA_API_URL . PIATTAFORMA_ENDPOINT_REGISTRA;
    $res = callPiattaforma($url, 'POST', $payload);

    if ($res['error'] || $res['status'] < 200 || $res['status'] >= 300) {
        $errMsg = $res['error'] ?: "HTTP {$res['status']}: {$res['body']}";
        logSicurezza($pdo, 'errore', "Registrazione fallita per {$dip['cognome_nome']}: {$errMsg}");
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $errMsg]);
        exit;
    }

    // Marca il dipendente come registrato
    dbExecute($pdo,
        'UPDATE sic_dipendenti SET piattaforma_registrato = 1 WHERE id = ?',
        [$dipId]
    );
    logSicurezza($pdo, 'registrazione', "Registrato: {$dip['cognome_nome']}");

    echo json_encode(['success' => true, 'message' => 'Dipendente registrato sulla piattaforma']);
    exit;
}


// =============================================================
// POLLING CORSI — Per ogni dipendente attivo, aggiorna gli attestati
// =============================================================
if ($azione === 'polling') {

    // Recupera tutti i dipendenti attivi, inclusi nel percorso sicurezza
    // e già registrati sulla piattaforma
    $dipendenti = dbFetchAll($pdo, '
        SELECT * FROM sic_dipendenti
        WHERE attivo = 1
          AND incluso_sicurezza = 1
          AND piattaforma_registrato = 1
          AND cognome_nome IS NOT NULL
          AND data_nascita IS NOT NULL
    ');

    if (!$dipendenti) {
        echo json_encode(['success' => true, 'aggiornati' => 0, 'message' => 'Nessun dipendente da interrogare']);
        exit;
    }

    // Carica la mappatura corsi piattaforma → nome locale
    // Usata per capire a quale corso locale corrisponde l'ID ricevuto dalla piattaforma
    $corsiMap = dbFetchAll($pdo, 'SELECT * FROM sic_corsi_piattaforma WHERE attivo = 1');
    $corsiById = [];
    foreach ($corsiMap as $corso) {
        // Indicizza per id_piattaforma → nome_locale
        if ($corso['id_piattaforma']) {
            $corsiById[$corso['id_piattaforma']] = $corso['nome_locale'];
        }
    }

    $totAggiornati = 0;
    $errori        = [];
    $url           = PIATTAFORMA_API_URL . PIATTAFORMA_ENDPOINT_CORSISTA;

    foreach ($dipendenti as $dip) {
        // Costruisce i parametri GET per il lookup del corsista
        $dataNascita = (new DateTime($dip['data_nascita']))->format(PIATTAFORMA_DATA_FORMAT_INVIO);

        $params = [
            PIATTAFORMA_GET_COGNOME      => $dip['cognome_nome'],
            PIATTAFORMA_GET_NOME         => '',         // vedi nota sopra sul split nome/cognome
            PIATTAFORMA_GET_DATA_NASCITA => $dataNascita,
        ];

        $res = callPiattaforma($url, 'GET', $params);

        if ($res['error'] || $res['status'] < 200 || $res['status'] >= 300) {
            $errori[] = "Polling fallito per {$dip['cognome_nome']}: " . ($res['error'] ?: "HTTP {$res['status']}");
            continue;
        }

        // Decodifica la risposta JSON della piattaforma
        $storico = json_decode($res['body'], true);
        if (!is_array($storico)) {
            $errori[] = "Risposta non valida per {$dip['cognome_nome']}";
            continue;
        }

        // Aggiorna sic_attestati con i corsi completati
        foreach ($storico as $corso) {
            $idCorso     = $corso[PIATTAFORMA_RESP_ID_CORSO]        ?? null;
            $dataSvolta  = $corso[PIATTAFORMA_RESP_DATA_SVOLGIMENTO] ?? null;
            $nomeCorso   = $corso[PIATTAFORMA_RESP_NOME_CORSO]      ?? null;

            if (!$idCorso || !$dataSvolta) continue;

            // Converte la data dal formato della piattaforma → MySQL (Y-m-d)
            $dataObj = DateTime::createFromFormat(PIATTAFORMA_DATA_FORMAT_RICEVI, $dataSvolta);
            if (!$dataObj) continue;
            $dataMySQL = $dataObj->format('Y-m-d');

            // Determina il nome locale del corso (tramite id_piattaforma o nome diretto)
            $nomeLocale = $corsiById[$idCorso] ?? strtoupper(trim($nomeCorso ?? ''));
            if (!$nomeLocale) continue;

            // UPSERT nell'attestato: se esiste aggiorna la data, altrimenti inserisce
            dbExecute($pdo, '
                INSERT INTO sic_attestati
                    (cognome_nome, sede_assunzione, nome_attestato, data_emissione, note)
                VALUES (?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    data_emissione = VALUES(data_emissione),
                    note           = VALUES(note),
                    updated_at     = NOW()
            ', [
                strtoupper($dip['cognome_nome']),
                $dip['sede_assunzione'],
                $nomeLocale,
                $dataMySQL,
                "Aggiornato automaticamente via polling piattaforma ({$idCorso})",
            ]);
        }

        $totAggiornati++;
    }

    logSicurezza($pdo, 'polling',
        "Polling completato: {$totAggiornati} dipendenti aggiornati. Errori: " . count($errori)
    );

    $totDip = count($dipendenti);
    echo json_encode([
        'success'       => true,
        'aggiornati'    => $totAggiornati,
        'errori'        => $errori,
        'message'       => "Polling completato — {$totAggiornati}/{$totDip} dipendenti aggiornati",
    ]);
    exit;
}


// =============================================================
// CORSI DISPONIBILI — Recupera catalogo dalla piattaforma
// =============================================================
if ($azione === 'corsi_disponibili') {
    $url = PIATTAFORMA_API_URL . PIATTAFORMA_ENDPOINT_CORSI;
    $res = callPiattaforma($url, 'GET');

    if ($res['error'] || $res['status'] < 200 || $res['status'] >= 300) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $res['error'] ?: "HTTP {$res['status']}"]);
        exit;
    }

    $corsi = json_decode($res['body'], true);
    echo json_encode(['success' => true, 'corsi' => $corsi]);
    exit;
}

// DIPENDENTI REGISTRATI
if ($azione === 'test') {
    $id = $_GET['id'];
    $cod_fis = $_GET['cod_fis'];
    if($id) {
        $payload = [
            PIATTAFORMA_GET_ID => $id
         ];
    } elseif($cod_fis) {
        $payload = [
            PIATTAFORMA_GET_CODFIS => $cod_fis
         ];
    } else {
        $payload = [];
    }
    
    $url = PIATTAFORMA_API_URL;
    $res = callPiattaforma($url, 'GET', $payload);
    $dipendenti = json_decode($res['body'], true);
    echo json_encode(['success' => true, 'dipendenti' => $dipendenti]);
    exit;
}

// Azione sconosciuta
http_response_code(400);
echo json_encode(['success' => false, 'message' => "Azione '{$azione}' non riconosciuta. Valori: registra | polling | corsi_disponibili"]);
