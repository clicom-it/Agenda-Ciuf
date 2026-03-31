<?php

// =============================================================
// API — Sincronizzazione Dipendenti (V2)
// =============================================================
// In V2 il pulsante "Sincronizza" è diventato una funzione
// di background chiamata solo:
//   1. Dal tab Admin nell'interfaccia (manuale)
//   2. Da un cron job notturno
//
// Novità rispetto a V1:
//   - Filtra solo dipendenti di atelier "diretto"
//   - Filtra per durata contratto >= soglia mesi (default 6)
//   - Salva i dati demografici (CF, data nascita, ecc.) per
//     la registrazione sulla piattaforma sicurezza
//   - Per i dipendenti nuovi e non ancora registrati sulla
//     piattaforma, imposta piattaforma_registrato = 0
//     (la registrazione avviene via api/piattaforma.php)
//   - Aggiorna sic_negozi.tipo per mantenere l'info del tipo
//
// Metodo HTTP: POST
// Risposta: JSON { success, count, inclusi, esclusi, negoziVariati }
// =============================================================

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';

function syncError(string $msg, int $code = 500): void {
    http_response_code($code);
    echo json_encode(['success' => false, 'message' => $msg]);
    exit;
}

// =====================================================
// Passo 1: Recupera dipendenti dal gestionale via API
// =====================================================
$ch = curl_init(GESTIONALE_API_URL_SANDBOX);
$data = "user=" . USER_WS . "&pw=" . urlencode(PWD_WS);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_TIMEOUT => GESTIONALE_API_TIMEOUT,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTPHEADER => array(
        'Access-Control-Allow-Origin: *',
        'Content-Length: ' . strlen($data),
        'Content-Type:application/x-www-form-urlencoded'
    ),
    CURLOPT_POSTFIELDS => $data
]);
if (GESTIONALE_API_KEY !== '') {
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        GESTIONALE_API_TOKEN_HEADER . ': ' . GESTIONALE_API_KEY,
        'Accept: application/json',
    ]);
}
$response = curl_exec($ch);
//echo $response;
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

if ($curlError)
    syncError('Impossibile contattare il gestionale: ' . $curlError);
if ($httpCode < 200 || $httpCode >= 300)
    syncError("Il gestionale ha risposto con codice HTTP {$httpCode}");
$gestionaleRaw = json_decode($response, true);
if (!is_array($gestionaleRaw))
    syncError('Il gestionale non ha restituito JSON valido');

// =====================================================
// Passo 2: Legge la soglia mesi default dal DB o config
// =====================================================
$pdo = getConnection();
$configRow = dbFetchOne($pdo, "SELECT valore FROM sic_config WHERE chiave = 'soglia_mesi_default'");
$sogliaMesiDefault = (int) ($configRow['valore'] ?? 6);

// =====================================================
// Passo 3: Mappa, filtra e pulisce ogni dipendente
// =====================================================
$inclusi = [];  // entreranno nel percorso sicurezza
$esclusi = [];  // esclusi (atelier non diretto o contratto troppo breve)

foreach ($gestionaleRaw as $raw) {

    // --- Costruisce nome completo ---
    if (FIELD_NOME_SEPARATO) {
        $nome = trim((string) ($raw[FIELD_NOME] ?? ''));
        $cognome = trim((string) ($raw[FIELD_COGNOME] ?? ''));
        $nomeCompleto = strtoupper(trim("$cognome $nome"));
    } else {
        $nomeCompleto = strtoupper(trim((string) ($raw[FIELD_NOME] ?? '')));
    }
    if (!$nomeCompleto)
        continue;

    // --- Verifica attivo ---
    if ((string) ($raw[FIELD_ATTIVO] ?? '') !== (string) FIELD_ATTIVO_VALORE)
        continue;

    $sede = trim((string) ($raw[FIELD_SEDE] ?? ''));
    $ore = (float) ($raw[FIELD_ORE] ?? 0);
    $inquadramento = strtoupper(trim((string) ($raw[FIELD_INQUADRAMENTO] ?? '')));
    $idEsterno = $raw[FIELD_ID_ESTERNO] ?? null;

    // Nuovi campi V2
    $email = trim((string) ($raw[FIELD_EMAIL] ?? ''));
    $cf = strtoupper(trim((string) ($raw[FIELD_CODICE_FISCALE] ?? '')));
    $dataNascitaRaw = trim((string) ($raw[FIELD_DATA_NASCITA] ?? ''));
    $sesso = strtoupper(trim((string) ($raw[FIELD_SESSO] ?? '')));
    $cittaNascita = trim((string) ($raw[FIELD_CITTA_NASCITA] ?? ''));
    $dataInizioRaw = trim((string) ($raw[FIELD_DATA_INIZIO_CONTRATTO] ?? ''));
    $dataFineRaw = trim((string) ($raw[FIELD_DATA_FINE_CONTRATTO] ?? ''));
    $tipoAtelier = strtolower(trim((string) ($raw[FIELD_TIPO_ATELIER] ?? '')));

    if (!$sede)
        continue;  // salta se sede mancante

    // --- Filtra: solo atelier "diretto" ---
    if ($tipoAtelier !== '' && $tipoAtelier !== strtolower(FIELD_TIPO_ATELIER_DIRETTO)) {
        $esclusi[] = "$nomeCompleto ($sede) — atelier non diretto";
        continue;
    }

    // --- Converti data nascita in formato MySQL ---
    $dataNascitaMySQL = null;
    if ($dataNascitaRaw) {
        $dtNasc = DateTime::createFromFormat(FIELD_DATA_NASCITA_FORMAT, $dataNascitaRaw) ?: DateTime::createFromFormat('d/m/Y', $dataNascitaRaw) ?: DateTime::createFromFormat('d-m-Y', $dataNascitaRaw);
        if ($dtNasc)
            $dataNascitaMySQL = $dtNasc->format('Y-m-d');
    }

    // --- Calcola durata contratto e decide se includere ---
    //
    // Regola:
    //   - Nessuna data inizio → include (dati mancanti, decisione conservativa)
    //   - Data inizio senza data fine → tempo indeterminato → INCLUSO
    //   - Data inizio + data fine → calcola mesi → incluso se >= soglia negozio
    $dataInizioMySQL = null;
    $dataFineMySQL = null;
    $inclusoSicurezza = 1;  // default: incluso

    if ($dataInizioRaw) {
        $dtInizio = DateTime::createFromFormat('Y-m-d', $dataInizioRaw) ?: DateTime::createFromFormat('d/m/Y', $dataInizioRaw) ?: DateTime::createFromFormat('d-m-Y', $dataInizioRaw);
        if ($dtInizio)
            $dataInizioMySQL = $dtInizio->format('Y-m-d');

        if ($dataFineRaw) {
            $dtFine = DateTime::createFromFormat('Y-m-d', $dataFineRaw) ?: DateTime::createFromFormat('d/m/Y', $dataFineRaw) ?: DateTime::createFromFormat('d-m-Y', $dataFineRaw);
            if ($dtFine) {
                $dataFineMySQL = $dtFine->format('Y-m-d');

                // Recupera soglia specifica per questo negozio (o usa il default)
                $negozioRow = dbFetchOne($pdo,
                        "SELECT soglia_mesi FROM sic_negozi WHERE UPPER(TRIM(nome_negozio)) = UPPER(TRIM(?)) LIMIT 1",
                        [$sede]
                );
                $sogliaMesi = (int) ($negozioRow['soglia_mesi'] ?? $sogliaMesiDefault);

                // Calcola la durata in mesi
                $diff = $dtInizio->diff($dtFine);
                $mesi = $diff->y * 12 + $diff->m + ($diff->d > 0 ? 1 : 0); // arrotondamento

                if ($mesi < $sogliaMesi) {
                    $inclusoSicurezza = 0;
                    $esclusi[] = "$nomeCompleto ($sede) — contratto {$mesi} mesi < soglia {$sogliaMesi} mesi";
                    // Non fare continue: salviamo comunque il dipendente ma escluso
                }
            }
            // Se dtFine non parsabile, trattalo come tempo indeterminato
        }
        // data_fine vuota/null → tempo indeterminato → incluso
    }

    $inclusi[] = [
        'gestionale_id' => $idEsterno,
        'cognome_nome' => $nomeCompleto,
        'sede_assunzione' => $sede,
        'ore' => $ore,
        'inquadramento' => $inquadramento,
        'email' => $email,
        'codice_fiscale' => $cf ?: null,
        'data_nascita' => $dataNascitaMySQL,
        'sesso' => in_array($sesso, ['M', 'F']) ? $sesso : null,
        'citta_nascita' => $cittaNascita ?: null,
        'data_inizio_contratto' => $dataInizioMySQL,
        'data_fine_contratto' => $dataFineMySQL,
        'incluso_sicurezza' => $inclusoSicurezza,
        'tipo_atelier' => $tipoAtelier ?: null,
    ];
}

// =====================================================
// Passo 4: Rilevazione variazioni nei negozi
// =====================================================
$vecchiDip = dbFetchAll($pdo,
        'SELECT cognome_nome, sede_assunzione, inquadramento, incluso_sicurezza FROM sic_dipendenti WHERE attivo = 1'
);
$mappaVecchi = [];
foreach ($vecchiDip as $d) {
    $key = strtoupper($d['cognome_nome']) . '|||' . strtoupper($d['sede_assunzione']);
    $mappaVecchi[$key] = [$d['inquadramento'], $d['incluso_sicurezza']];
}
$mappaNuovi = [];
foreach ($inclusi as $d) {
    $key = $d['cognome_nome'] . '|||' . strtoupper($d['sede_assunzione']);
    $mappaNuovi[$key] = [$d['inquadramento'], $d['incluso_sicurezza']];
}

$negoziVariati = [];
foreach ($mappaNuovi as $key => [$inq, $inc]) {
    [, $sede] = explode('|||', $key, 2);
    if (!isset($mappaVecchi[$key]) || $mappaVecchi[$key][0] !== $inq) {
        $negoziVariati[$sede] = true;
    }
}
foreach ($mappaVecchi as $key => $v) {
    [, $sede] = explode('|||', $key, 2);
    if (!isset($mappaNuovi[$key])) {
        $negoziVariati[$sede] = true;
    }
}

// =====================================================
// Passo 5: Salva nel DB
// =====================================================
try {
    $pdo->beginTransaction();

    // Marca tutti come inattivi — gli attivi vengono re-inseriti subito sotto
    dbExecute($pdo, 'UPDATE sic_dipendenti SET attivo = 0');

    $salvati = 0;
    foreach ($inclusi as $d) {
        // UPSERT: se il dipendente esiste (stesso gestionale_id) aggiorna,
        // altrimenti inserisce. piattaforma_registrato NON viene azzerato:
        // chi era già registrato rimane registrato.
        $pdo->prepare('
            INSERT INTO sic_dipendenti
                (gestionale_id, cognome_nome, sede_assunzione, ore, inquadramento,
                 email, codice_fiscale, data_nascita, sesso, citta_nascita,
                 data_inizio_contratto, data_fine_contratto, incluso_sicurezza, attivo)
            VALUES
                (:gid, :cn, :sede, :ore, :inq, :email, :cf, :dn, :sesso, :citta,
                 :di, :df, :inc, 1)
            ON DUPLICATE KEY UPDATE
                cognome_nome           = VALUES(cognome_nome),
                sede_assunzione        = VALUES(sede_assunzione),
                ore                    = VALUES(ore),
                inquadramento          = VALUES(inquadramento),
                email                  = VALUES(email),
                codice_fiscale         = COALESCE(VALUES(codice_fiscale), codice_fiscale),
                data_nascita           = COALESCE(VALUES(data_nascita), data_nascita),
                sesso                  = COALESCE(VALUES(sesso), sesso),
                citta_nascita          = COALESCE(VALUES(citta_nascita), citta_nascita),
                data_inizio_contratto  = VALUES(data_inizio_contratto),
                data_fine_contratto    = VALUES(data_fine_contratto),
                incluso_sicurezza      = VALUES(incluso_sicurezza),
                attivo                 = 1,
                synced_at              = NOW()
        ')->execute([
            ':gid' => $d['gestionale_id'],
            ':cn' => $d['cognome_nome'],
            ':sede' => $d['sede_assunzione'],
            ':ore' => $d['ore'],
            ':inq' => $d['inquadramento'],
            ':email' => $d['email'],
            ':cf' => $d['codice_fiscale'],
            ':dn' => $d['data_nascita'],
            ':sesso' => $d['sesso'],
            ':citta' => $d['citta_nascita'],
            ':di' => $d['data_inizio_contratto'],
            ':df' => $d['data_fine_contratto'],
            ':inc' => $d['incluso_sicurezza'],
        ]);
        $salvati++;
    }

    // Aggiorna data_ultima_variazione e tipo nei negozi variati
    $oggi = date('Y-m-d');
    foreach (array_keys($negoziVariati) as $sede) {
        $pdo->prepare('
            UPDATE sic_negozi
            SET data_ultima_variazione = ?
            WHERE UPPER(TRIM(nome_negozio)) = UPPER(TRIM(?))
        ')->execute([$oggi, $sede]);
    }

    // Aggiorna il campo tipo in sic_negozi per i negozi presenti nella risposta
    $tipiSedi = [];
    foreach ($inclusi as $d) {
        if ($d['tipo_atelier'] && $d['sede_assunzione']) {
            $tipiSedi[$d['sede_assunzione']] = $d['tipo_atelier'];
        }
    }
    foreach ($tipiSedi as $sede => $tipo) {
        $pdo->prepare('
            UPDATE sic_negozi SET tipo = ?
            WHERE UPPER(TRIM(nome_negozio)) = UPPER(TRIM(?))
        ')->execute([$tipo, $sede]);
    }

    // Log operazione
    dbExecute($pdo, 'INSERT INTO sic_log (tipo, messaggio) VALUES (?, ?)', [
        'sync',
        "Sync completato: {$salvati} dipendenti salvati, " . count($negoziVariati) . " negozi variati, "
        . count($esclusi) . " dipendenti esclusi.",
    ]);

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Sincronizzazione completata',
        'count' => $salvati,
        'esclusi' => count($esclusi),
        'dettaglio_esclusi' => $esclusi,
        'negoziVariati' => array_keys($negoziVariati),
        'changesDetected' => count($negoziVariati),
    ]);
} catch (Exception $e) {
    if ($pdo->inTransaction())
        $pdo->rollBack();
    syncError('Errore durante il salvataggio: ' . $e->getMessage());
}
