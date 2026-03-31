<?php
// =============================================================
// MODULO SICUREZZA — Motore di Logica Principale
// =============================================================
// Questo file è il cuore del sistema.
// Contiene il porting completo dello script Google Apps Script
// (logica.js) in PHP puro.
//
// Le funzioni qui presenti calcolano:
// - Se ogni dipendente copre il ruolo richiesto (attestato ok/scaduto/assente)
// - Se ogni negozio è "Coperto", "Parziale" o "Critico"
// - Lo stato del DVR per ogni negozio
// - Il report dei corsi necessari
//
// Le funzioni sono identiche nella logica al file JS originale,
// adattate alla sintassi PHP.
// =============================================================

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/db.php';


// =============================================================
// FUNZIONI DI UTILITÀ
// =============================================================

/**
 * Normalizza un valore stringa: trim, spazi multipli → singolo, MAIUSCOLO.
 * Usata per confrontare stringhe in modo robusto (ignora maiuscole/minuscole
 * e spazi extra). Equivalente a normalizeValue() in logica.js.
 *
 * @param mixed $value
 * @return string
 */
function normalizeValue($value): string
{
    return strtoupper(trim(preg_replace('/\s+/', ' ', (string)($value ?? ''))));
}

/**
 * Alias di normalizeValue, usata per le chiavi degli array associativi.
 * Equivalente a normalizeKey_() in logica.js.
 *
 * @param mixed $value
 * @return string
 */
function normalizeKey($value): string
{
    return normalizeValue($value);
}

/**
 * Imposta un oggetto DateTime a mezzanotte (00:00:00).
 * Usata per confrontare solo le DATE, ignorando l'ora.
 * Equivalente a startOfDay() in logica.js.
 *
 * @param DateTime $date
 * @return DateTime
 */
function startOfDay(DateTime $date): DateTime
{
    $d = clone $date;   // Clona l'oggetto per non modificare l'originale
    $d->setTime(0, 0, 0, 0);
    return $d;
}

/**
 * Aggiunge N anni a una data e restituisce la nuova data.
 * Usata per calcolare la scadenza di un attestato.
 * Equivalente a addYears_() in logica.js.
 *
 * @param DateTime $date
 * @param int      $years
 * @return DateTime
 */
function addYears(DateTime $date, int $years): DateTime
{
    $d = clone $date;
    $d->modify("+{$years} years");
    return $d;
}

/**
 * Controlla se un array è "non vuoto" (ha almeno un valore non-spazio).
 * Usato per filtrare righe vuote. Equivalente a notEmptyRow_() in logica.js.
 *
 * @param array $row
 * @return bool
 */
function notEmptyRow(array $row): bool
{
    foreach ($row as $cell) {
        if (trim((string)$cell) !== '') return true;
    }
    return false;
}


// =============================================================
// CALCOLO STATO DVR
// =============================================================

/**
 * Calcola lo stato del DVR di un negozio confrontando:
 * - la data dell'ultimo DVR redatto
 * - la data dell'ultima variazione del personale/indirizzo
 *
 * Logica:
 *   - Nessun DVR → "DA EMETTERE"
 *   - DVR presente ma precedente all'ultima variazione → "DA AGGIORNARE"
 *   - DVR aggiornato → "OK"
 *
 * Equivalente a computeDvrStatus_() in logica.js.
 *
 * @param DateTime|null $dataDvr               Data dell'ultimo DVR
 * @param DateTime|null $dataUltimaVariazione   Data dell'ultima variazione
 * @return string  'OK' | 'DA AGGIORNARE' | 'DA EMETTERE'
 */
function computeDvrStatus(?DateTime $dataDvr, ?DateTime $dataUltimaVariazione): string
{
    if ($dataDvr === null) {
        return 'DA EMETTERE'; // Nessun DVR presente
    }

    if ($dataUltimaVariazione === null) {
        return 'OK'; // DVR presente, nessuna variazione registrata
    }

    $dvr    = startOfDay($dataDvr);
    $lastVar = startOfDay($dataUltimaVariazione);

    // Se il DVR è datato prima dell'ultima variazione, va aggiornato
    return $dvr >= $lastVar ? 'OK' : 'DA AGGIORNARE';
}


// =============================================================
// LETTURA DATI DAL DATABASE
// =============================================================

/**
 * Legge tutte le tabelle necessarie dal DB e le organizza in
 * strutture dati ottimizzate per i calcoli di copertura.
 *
 * Restituisce un array associativo con:
 *   - negoziMap:         [chiave_negozio => dettagli_negozio]
 *   - dipPerNegozio:     [chiave_negozio => [dipendenti]]
 *   - rulesByRole:       [nome_ruolo => {fabbisogno, inquadramenti[]}]
 *   - eccezioniByNegozio:[chiave_negozio => [eccezioni]]
 *   - attestatiMap:      ["nome|||sede|||attestato" => DateTime data_emissione]
 *   - durataMap:         [nome_corso => anni_validita]
 *   - today:             DateTime di oggi a mezzanotte
 *
 * Equivalente a getCoreData_() in logica.js.
 *
 * @param PDO $pdo
 * @return array
 */
function getCoreData(PDO $pdo): array
{
    $today = new DateTime();
    $today->setTime(0, 0, 0, 0);

    // --- Negozi — solo atelier di tipo "diretto" (o senza tipo, per retrocompatibilità)
    $negoziRows = dbFetchAll($pdo,
        "SELECT * FROM sic_negozi WHERE (tipo IS NULL OR tipo = 'diretto') ORDER BY nome_negozio"
    );
    $negoziMap  = [];
    foreach ($negoziRows as $row) {
        $key     = normalizeKey($row['nome_negozio']);
        // Convertiamo le date da stringa MySQL a oggetti DateTime (o null)
        $dataDvr = $row['data_dvr']               ? new DateTime($row['data_dvr'])               : null;
        $dataVar = $row['data_ultima_variazione']  ? new DateTime($row['data_ultima_variazione']) : null;

        $negoziMap[$key] = [
            'nome'                 => $row['nome_negozio'],
            'ragioneSociale'       => $row['ragione_sociale']  ?? '',
            'mail'                 => $row['mail']             ?? '',
            'via'                  => $row['via']              ?? '',
            'localita'             => $row['localita']         ?? '',
            'cap'                  => $row['cap']              ?? '',
            'provincia'            => $row['provincia']        ?? '',
            'dataDvr'              => $dataDvr,
            'dataUltimaVariazione' => $dataVar,
            'statoDvr'             => computeDvrStatus($dataDvr, $dataVar),
        ];
    }

    // --- Dipendenti attivi e inclusi nel percorso sicurezza ---
    // incluso_sicurezza = 1: contratto >= soglia mesi del negozio
    $dipRows      = dbFetchAll($pdo, 'SELECT * FROM sic_dipendenti WHERE attivo = 1 AND incluso_sicurezza = 1');
    $dipPerNegozio = [];
    foreach ($dipRows as $row) {
        $key = normalizeKey($row['sede_assunzione']);
        if (!$key) continue;  // Salta dipendenti senza sede
        $dipPerNegozio[$key][] = [
            'nome'          => $row['cognome_nome'],
            'negozio'       => $row['sede_assunzione'],
            'ore'           => (float)($row['ore'] ?? 0),
            'inquadramento' => $row['inquadramento'] ?? '',
        ];
    }

    // --- Regole attestati: quali corsi servono per ogni inquadramento ---
    $regoleRows  = dbFetchAll($pdo, 'SELECT * FROM sic_regole_attestati');
    $rulesByRole = [];
    foreach ($regoleRows as $row) {
        $inq        = normalizeValue($row['inquadramento']);
        $ruolo      = normalizeValue($row['nome_ruolo']);
        $fabbisogno = strtoupper(trim($row['fabbisogno'] ?? '1'));

        if (!$inq || !$ruolo) continue;

        if (!isset($rulesByRole[$ruolo])) {
            // Inizializza la regola per questo ruolo/corso
            $rulesByRole[$ruolo] = [
                'fabbisogno'    => $fabbisogno ?: '1',
                'inquadramenti' => [],   // Lista degli inquadramenti che devono avere questo corso
            ];
        }

        // Aggiunge l'inquadramento alla lista se non già presente
        if (!in_array($inq, $rulesByRole[$ruolo]['inquadramenti'], true)) {
            $rulesByRole[$ruolo]['inquadramenti'][] = $inq;
        }
        if ($fabbisogno) {
            $rulesByRole[$ruolo]['fabbisogno'] = $fabbisogno;
        }
    }

    // --- Eccezioni per negozio ---
    $eccezioniRows      = dbFetchAll($pdo, 'SELECT * FROM sic_regole_eccezioni');
    $eccezioniByNegozio = [];
    foreach ($eccezioniRows as $row) {
        $negozio = normalizeKey($row['nome_negozio']);
        if (!$negozio) continue;
        $eccezioniByNegozio[$negozio][] = [
            'inquadramento' => normalizeValue($row['inquadramento']),
            'ruolo'         => normalizeValue($row['nome_ruolo']),
            'tipo'          => normalizeValue($row['tipo_eccezione']), // AGGIUNGI o ESCLUDI
        ];
    }

    // --- Mappa attestati: chiave composta per lookup O(1) ---
    // Chiave: "COGNOME NOME|||SEDE|||NOME CORSO" → data emissione
    $attRows     = dbFetchAll($pdo, 'SELECT * FROM sic_attestati');
    $attestatiMap = [];
    foreach ($attRows as $row) {
        $key = normalizeValue($row['cognome_nome'])
             . '|||' . normalizeKey($row['sede_assunzione'])
             . '|||' . normalizeValue($row['nome_attestato']);
        $attestatiMap[$key] = new DateTime($row['data_emissione']);
    }

    // --- Durata corsi: anni di validità per ogni corso ---
    $durateRows = dbFetchAll($pdo, 'SELECT * FROM sic_durata_corsi');
    $durataMap  = [];
    foreach ($durateRows as $row) {
        $corso = normalizeValue($row['corso']);
        $anni  = (int)$row['anni_validita'];
        if (!$corso || !$anni) continue;
        $durataMap[$corso] = $anni;
    }

    return [
        'negoziMap'          => $negoziMap,
        'dipPerNegozio'      => $dipPerNegozio,
        'rulesByRole'        => $rulesByRole,
        'eccezioniByNegozio' => $eccezioniByNegozio,
        'attestatiMap'       => $attestatiMap,
        'durataMap'          => $durataMap,
        'today'              => $today,
    ];
}


// =============================================================
// CALCOLO STATO ATTESTATO DI UN DIPENDENTE
// =============================================================

/**
 * Verifica se un dipendente ha l'attestato valido per un ruolo specifico.
 *
 * Cerca nella mappa attestati il record corrispondente a:
 *   - nome del dipendente
 *   - sede del negozio
 *   - nome del corso/ruolo
 *
 * Se lo trova, calcola la scadenza (data emissione + anni validità).
 * Restituisce l'esito: COPERTO, NON COPERTO (assente o scaduto).
 *
 * Equivalente a getEmployeeRoleStatus_() in logica.js.
 *
 * @param array     $emp          Dati del dipendente [nome, negozio, ore, inquadramento]
 * @param string    $ruolo        Nome del corso/ruolo da verificare
 * @param string    $negozioKey   Chiave normalizzata del negozio
 * @param array     $attestatiMap Mappa attestati dal DB
 * @param array     $durataMap    Mappa durata corsi dal DB
 * @param DateTime  $today        Data odierna a mezzanotte
 * @return array    [dataScadenza => DateTime|null, esito => string, dettaglio => string]
 */
function getEmployeeRoleStatus(
    array    $emp,
    string   $ruolo,
    string   $negozioKey,
    array    $attestatiMap,
    array    $durataMap,
    DateTime $today
): array {
    // Costruisce la chiave di lookup per trovare l'attestato
    $attKey = normalizeValue($emp['nome'])
            . '|||' . $negozioKey
            . '|||' . normalizeValue($ruolo);

    $dataEmissione = $attestatiMap[$attKey]              ?? null;
    $anniValidita  = $durataMap[normalizeValue($ruolo)]  ?? null;

    // Attestato non trovato oppure durata corso non configurata
    if ($dataEmissione === null || $anniValidita === null) {
        return [
            'dataScadenza' => null,
            'esito'        => 'NON COPERTO',
            'dettaglio'    => 'ATTESTATO ASSENTE',
        ];
    }

    // Calcola la data di scadenza
    $scadenza = addYears($dataEmissione, $anniValidita);
    $expiry   = startOfDay($scadenza);

    // Confronta con oggi
    if ($expiry < $today) {
        return [
            'dataScadenza' => $scadenza,
            'esito'        => 'NON COPERTO',
            'dettaglio'    => 'ATTESTATO SCADUTO',
        ];
    }

    return [
        'dataScadenza' => $scadenza,
        'esito'        => 'COPERTO',
        'dettaglio'    => 'OK',
    ];
}


// =============================================================
// APPLICAZIONE ECCEZIONI
// =============================================================

/**
 * Modifica le regole di un negozio specifico applicando le eccezioni
 * definite in sic_regole_eccezioni.
 *
 * Tipo "AGGIUNGI": aggiunge un inquadramento alla lista di chi
 *   deve avere quel corso (es. "aggiungi Sartoria al corso Preposto")
 * Tipo "ESCLUDI": rimuove un inquadramento (es. "escludi Visual
 *   dal corso Antincendio in quel negozio perché non ci lavora")
 *
 * ATTENZIONE: modifica $rolesForStore per riferimento (&).
 * Equivalente a applyEccezioniToRoles_() in logica.js.
 *
 * @param array $rolesForStore  Regole del negozio (modificate in-place)
 * @param array $eccezioni      Lista eccezioni del negozio
 */
function applyEccezioniToRoles(array &$rolesForStore, array $eccezioni): void
{
    foreach ($eccezioni as $ec) {
        $ruolo = $ec['ruolo'];
        $inq   = $ec['inquadramento'];
        $tipo  = $ec['tipo'];

        // Crea la regola se non esiste ancora (per eccezioni AGGIUNGI su ruoli nuovi)
        if (!isset($rolesForStore[$ruolo])) {
            $rolesForStore[$ruolo] = ['fabbisogno' => '1', 'inquadramenti' => []];
        }

        if ($tipo === 'AGGIUNGI') {
            if (!in_array($inq, $rolesForStore[$ruolo]['inquadramenti'], true)) {
                $rolesForStore[$ruolo]['inquadramenti'][] = $inq;
            }
        } elseif ($tipo === 'ESCLUDI') {
            // Rimuove l'inquadramento dalla lista mantenendo gli altri
            $rolesForStore[$ruolo]['inquadramenti'] = array_values(
                array_filter(
                    $rolesForStore[$ruolo]['inquadramenti'],
                    fn($x) => $x !== $inq
                )
            );
        }
    }
}

/**
 * Crea una copia profonda delle regole globali per un negozio specifico.
 * Necessario perché le eccezioni di un negozio non devono modificare
 * le regole degli altri negozi.
 * Equivalente a deepCloneRules_() in logica.js.
 *
 * @param array $rules  Regole globali
 * @return array        Copia indipendente
 */
function deepCloneRules(array $rules): array
{
    $out = [];
    foreach ($rules as $ruolo => $info) {
        $out[$ruolo] = [
            'fabbisogno'    => $info['fabbisogno'],
            'inquadramenti' => $info['inquadramenti'],  // Array di scalari: copiato per valore
        ];
    }
    return $out;
}


// =============================================================
// ORDINAMENTO CANDIDATI
// =============================================================

/**
 * Compara due candidati COPERTI per scegliere il "migliore".
 * Priorità:
 *   1. Scadenza più lontana (chi dura di più va per primo)
 *   2. Inquadramento "preferito" (quello previsto dalla regola)
 *   3. Più ore settimanali (più presente in negozio)
 *   4. Nome alfabetico (tiebreaker)
 *
 * Da usare con usort() su un array di candidati.
 * Equivalente a compareCandidates_() in logica.js.
 */
function compareCandidates(array $a, array $b): int
{
    $aTime = $a['dataScadenza'] instanceof DateTime ? $a['dataScadenza']->getTimestamp() : 0;
    $bTime = $b['dataScadenza'] instanceof DateTime ? $b['dataScadenza']->getTimestamp() : 0;

    if ($aTime !== $bTime) return $bTime - $aTime;  // Scadenza più lontana = migliore
    if ($a['preferred'] !== $b['preferred']) return $a['preferred'] ? -1 : 1;
    if ($a['emp']['ore'] !== $b['emp']['ore']) return $b['emp']['ore'] > $a['emp']['ore'] ? 1 : -1;
    return strcmp((string)$a['emp']['nome'], (string)$b['emp']['nome']);
}

/**
 * Compara due candidati NON coperti per la selezione di fallback.
 * Quando non ci sono abbastanza coperti, si sceglie il "meno peggio":
 *   1. Inquadramento preferito
 *   2. Scaduto (ha fatto il corso almeno una volta) vs. mai fatto
 *   3. Scadenza più recente (meno vecchia)
 *   4. Più ore settimanali
 *   5. Nome alfabetico
 *
 * Equivalente a compareFallbackCandidates_() in logica.js.
 */
function compareFallbackCandidates(array $a, array $b): int
{
    if ($a['preferred'] !== $b['preferred']) return $a['preferred'] ? -1 : 1;

    $aExp = $a['dettaglio'] === 'ATTESTATO SCADUTO';
    $bExp = $b['dettaglio'] === 'ATTESTATO SCADUTO';
    if ($aExp !== $bExp) return $aExp ? -1 : 1;  // Scaduto prima di assente

    $aTime = $a['dataScadenza'] instanceof DateTime ? $a['dataScadenza']->getTimestamp() : 0;
    $bTime = $b['dataScadenza'] instanceof DateTime ? $b['dataScadenza']->getTimestamp() : 0;
    if ($aTime !== $bTime) return $bTime - $aTime;

    if ($a['emp']['ore'] !== $b['emp']['ore']) return $b['emp']['ore'] > $a['emp']['ore'] ? 1 : -1;
    return strcmp((string)$a['emp']['nome'], (string)$b['emp']['nome']);
}


// =============================================================
// FUNZIONE PRINCIPALE: COSTRUZIONE RIGHE DI COPERTURA
// =============================================================

/**
 * Funzione centrale del sistema: per ogni negozio e per ogni ruolo/corso
 * previsto dalle regole, determina quale/i dipendente/i lo copre/coprono
 * e con quale esito (COPERTO / NON COPERTO + motivo).
 *
 * Logica:
 *   - Per ogni negozio, clona le regole globali e applica le eccezioni specifiche
 *   - Per ogni ruolo, distingue:
 *       * Fabbisogno "TUTTI": tutti i dipendenti dell'inquadramento devono avere il corso
 *       * Fabbisogno N:       almeno N dipendenti per negozio devono coprire quel ruolo.
 *                             Si scelgono prima i migliori coperti, poi i fallback.
 *
 * Restituisce una lista piatta di righe (una per dipendente/ruolo selezionato),
 * ordinate per negozio → ruolo → nome.
 *
 * Equivalente a buildCoverageRows_() in logica.js.
 *
 * @param array $data  Output di getCoreData()
 * @return array
 */
function buildCoverageRows(array $data): array
{
    $result = [];

    foreach ($data['negoziMap'] as $negozioKey => $negozioInfo) {
        $dipendenti = $data['dipPerNegozio'][$negozioKey] ?? [];
        $eccezioni  = $data['eccezioniByNegozio'][$negozioKey] ?? [];

        // Copia le regole globali e adattale per questo negozio
        $rolesForStore = deepCloneRules($data['rulesByRole']);
        applyEccezioniToRoles($rolesForStore, $eccezioni);

        foreach ($rolesForStore as $ruolo => $roleInfo) {
            $fabbisogno  = strtoupper((string)($roleInfo['fabbisogno'] ?? '1'));
            $preferredInq = $roleInfo['inquadramenti'];

            if ($fabbisogno === 'TUTTI') {
                // ------------------------------------------------
                // TUTTI: ogni dipendente con l'inquadramento giusto
                //        deve avere il corso
                // ------------------------------------------------
                foreach ($dipendenti as $emp) {
                    if (!in_array(normalizeValue($emp['inquadramento']), $preferredInq, true)) {
                        continue; // Salta se l'inquadramento non è previsto per questo ruolo
                    }
                    $st = getEmployeeRoleStatus(
                        $emp, $ruolo, $negozioKey,
                        $data['attestatiMap'], $data['durataMap'], $data['today']
                    );
                    $result[] = buildRow($negozioInfo, $ruolo, $emp, $st);
                }
            } else {
                // ------------------------------------------------
                // NUMERO: servono almeno N dipendenti coperti
                // ------------------------------------------------
                $needed = max((int)$fabbisogno ?: 1, 1);

                // Calcola stato per tutti i dipendenti del negozio
                $candidates = array_map(function ($emp) use (
                    $ruolo, $negozioKey, $data, $preferredInq
                ) {
                    $st = getEmployeeRoleStatus(
                        $emp, $ruolo, $negozioKey,
                        $data['attestatiMap'], $data['durataMap'], $data['today']
                    );
                    return [
                        'emp'         => $emp,
                        'dataScadenza'=> $st['dataScadenza'],
                        'esito'       => $st['esito'],
                        'dettaglio'   => $st['dettaglio'],
                        'preferred'   => in_array(normalizeValue($emp['inquadramento']), $preferredInq, true),
                    ];
                }, $dipendenti);

                // Prende prima i candidati COPERTI (ordinati per qualità)
                $validCandidates = array_filter($candidates, fn($c) => $c['esito'] === 'COPERTO');
                usort($validCandidates, 'compareCandidates');
                $validCandidates = array_values($validCandidates);

                $selected = array_slice($validCandidates, 0, $needed);

                // Se non bastano i coperti, riempie con i fallback non-coperti
                if (count($selected) < $needed) {
                    $selectedSet = $selected; // copia per l'in_array sotto
                    $remaining   = array_filter($candidates, fn($c) => !in_array($c, $selectedSet, true));
                    usort($remaining, 'compareFallbackCandidates');
                    foreach (array_values($remaining) as $r) {
                        if (count($selected) >= $needed) break;
                        $selected[] = $r;
                    }
                }

                foreach ($selected as $c) {
                    $result[] = buildRow($negozioInfo, $ruolo, $c['emp'], [
                        'dataScadenza' => $c['dataScadenza'],
                        'esito'        => $c['esito'],
                        'dettaglio'    => $c['dettaglio'],
                    ]);
                }
            }
        }
    }

    // Ordina il risultato finale per negozio → ruolo → nome dipendente
    usort($result, fn($a, $b) =>
        strcmp($a['negozio'], $b['negozio']) ?:
        strcmp($a['ruolo'],   $b['ruolo'])   ?:
        strcmp($a['nome'],    $b['nome'])
    );

    return $result;
}

/**
 * Helper interno: costruisce una singola riga di output
 * combinando i dati del negozio, del ruolo, del dipendente e dell'esito.
 */
function buildRow(array $negozioInfo, string $ruolo, array $emp, array $st): array
{
    return [
        'negozio'        => $negozioInfo['nome'],
        'ragioneSociale' => $negozioInfo['ragioneSociale'],
        'mail'           => $negozioInfo['mail'],
        'via'            => $negozioInfo['via'],
        'localita'       => $negozioInfo['localita'],
        'cap'            => $negozioInfo['cap'],
        'provincia'      => $negozioInfo['provincia'],
        'ruolo'          => $ruolo,
        'nome'           => $emp['nome'],
        'inquadramento'  => $emp['inquadramento'],
        'ore'            => $emp['ore'],
        'dataScadenza'   => $st['dataScadenza'],
        'esito'          => $st['esito'],
        'dettaglio'      => $st['dettaglio'],
    ];
}
