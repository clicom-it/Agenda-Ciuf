<?php
// =============================================================
// API — Gestione Attestati (CRUD)
// =============================================================
// Gestisce gli attestati formativi dei dipendenti.
//
// Corrisponde al foglio ATTESTATI_INSERIMENTO del vecchio sistema.
//
// Metodi HTTP supportati:
//   GET    → lista attestati (filtrabile per sede e/o dipendente)
//   POST   → aggiunge un nuovo attestato
//   PUT    → aggiorna la data di emissione di un attestato (usa ?id=X)
//   DELETE → elimina un attestato (usa ?id=X)
// =============================================================

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/logic.php';

$pdo    = getConnection();
$method = $_SERVER['REQUEST_METHOD'];
$id     = isset($_GET['id']) ? (int)$_GET['id'] : null;

function attestatoError(string $msg, int $code = 400): void {
    http_response_code($code);
    echo json_encode(['success' => false, 'message' => $msg]);
    exit;
}

try {
    switch ($method) {

        // ---------------------------------------------------------
        // GET: Lista attestati con data di scadenza calcolata
        // Filtri opzionali via query string:
        //   ?sede=NomeNegozio
        //   ?dipendente=cognome_nome
        // ---------------------------------------------------------
        case 'GET':
            $sql    = 'SELECT a.*, d.anni_validita FROM sic_attestati a
                       LEFT JOIN sic_durata_corsi d
                              ON UPPER(TRIM(d.corso)) = UPPER(TRIM(a.nome_attestato))';
            $params = [];
            $where  = [];

            if (!empty($_GET['sede'])) {
                $where[]  = 'UPPER(TRIM(a.sede_assunzione)) = UPPER(TRIM(?))';
                $params[] = $_GET['sede'];
            }
            if (!empty($_GET['dipendente'])) {
                $where[]  = 'UPPER(TRIM(a.cognome_nome)) LIKE UPPER(TRIM(?))';
                $params[] = '%' . $_GET['dipendente'] . '%';
            }
            if ($where) $sql .= ' WHERE ' . implode(' AND ', $where);
            $sql .= ' ORDER BY a.sede_assunzione, a.cognome_nome, a.nome_attestato';

            $rows = dbFetchAll($pdo, $sql, $params);

            // Calcola la data di scadenza per ogni attestato
            foreach ($rows as &$row) {
                if ($row['anni_validita'] && $row['data_emissione']) {
                    $emissione = new DateTime($row['data_emissione']);
                    $scadenza  = addYears($emissione, (int)$row['anni_validita']);
                    $oggi      = new DateTime();
                    $oggi->setTime(0, 0, 0, 0);

                    $row['data_scadenza']  = $scadenza->format('Y-m-d');
                    $row['scadenza_fmt']   = $scadenza->format('d/m/Y');
                    $row['emissione_fmt']  = $emissione->format('d/m/Y');
                    // Stato: OK, SCADUTO, o IN SCADENZA (entro 60 giorni)
                    $giorniRimanenti = (int)$oggi->diff($scadenza)->days * ($scadenza >= $oggi ? 1 : -1);
                    if ($scadenza < $oggi) {
                        $row['stato'] = 'SCADUTO';
                    } elseif ($giorniRimanenti <= 60) {
                        $row['stato'] = 'IN_SCADENZA';
                    } else {
                        $row['stato'] = 'OK';
                    }
                    $row['giorni_rimanenti'] = $giorniRimanenti;
                } else {
                    $row['data_scadenza']    = null;
                    $row['scadenza_fmt']     = null;
                    $row['emissione_fmt']    = (new DateTime($row['data_emissione']))->format('d/m/Y');
                    $row['stato']            = 'DURATA_NON_CONFIGURATA';
                    $row['giorni_rimanenti'] = null;
                }
            }
            unset($row);
            echo json_encode($rows);
            break;

        // ---------------------------------------------------------
        // POST: Aggiunge un nuovo attestato
        //
        // Body JSON richiesto:
        // {
        //   "cognome_nome":    "ROSSI MARIO",
        //   "sede_assunzione": "Bari",
        //   "nome_attestato":  "PREPOSTO",
        //   "data_emissione":  "2024-01-15",   (formato YYYY-MM-DD)
        //   "note":            "..."            (opzionale)
        // }
        // ---------------------------------------------------------
        case 'POST':
            $body = json_decode(file_get_contents('php://input'), true) ?? [];

            // Validazione campi obbligatori
            foreach (['cognome_nome', 'sede_assunzione', 'nome_attestato', 'data_emissione'] as $campo) {
                if (empty($body[$campo])) attestatoError("Il campo '{$campo}' è obbligatorio");
            }

            // Verifica formato data
            $dataEmissione = DateTime::createFromFormat('Y-m-d', $body['data_emissione']);
            if (!$dataEmissione) attestatoError('Formato data non valido, usa YYYY-MM-DD');

            // INSERT con ON DUPLICATE KEY UPDATE:
            // Se esiste già un attestato per quella combo dipendente/sede/corso,
            // aggiorna la data invece di creare un duplicato
            dbExecute($pdo, '
                INSERT INTO sic_attestati
                    (cognome_nome, sede_assunzione, nome_attestato, data_emissione, note)
                VALUES
                    (:cn, :sede, :att, :data, :note)
                ON DUPLICATE KEY UPDATE
                    data_emissione = VALUES(data_emissione),
                    note           = VALUES(note),
                    updated_at     = NOW()
            ', [
                ':cn'   => strtoupper(trim($body['cognome_nome'])),
                ':sede' => trim($body['sede_assunzione']),
                ':att'  => strtoupper(trim($body['nome_attestato'])),
                ':data' => $body['data_emissione'],
                ':note' => $body['note'] ?? null,
            ]);

            echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
            break;

        // ---------------------------------------------------------
        // PUT: Aggiorna data emissione e/o note di un attestato
        // ---------------------------------------------------------
        case 'PUT':
            if (!$id) attestatoError('Parametro "id" mancante');
            $body = json_decode(file_get_contents('php://input'), true) ?? [];

            $sets   = [];
            $params = [':id' => $id];

            if (isset($body['data_emissione'])) {
                $d = DateTime::createFromFormat('Y-m-d', $body['data_emissione']);
                if (!$d) attestatoError('Formato data non valido');
                $sets[]             = 'data_emissione = :data_emissione';
                $params[':data_emissione'] = $body['data_emissione'];
            }
            if (array_key_exists('note', $body)) {
                $sets[]        = 'note = :note';
                $params[':note'] = $body['note'] ?: null;
            }
            if (empty($sets)) attestatoError('Nessun campo da aggiornare');

            dbExecute($pdo, 'UPDATE sic_attestati SET ' . implode(', ', $sets) . ' WHERE id = :id', $params);
            echo json_encode(['success' => true]);
            break;

        // ---------------------------------------------------------
        // DELETE: Elimina un attestato
        // ---------------------------------------------------------
        case 'DELETE':
            if (!$id) attestatoError('Parametro "id" mancante');
            $deleted = dbExecute($pdo, 'DELETE FROM sic_attestati WHERE id = ?', [$id]);
            if (!$deleted) attestatoError('Attestato non trovato', 404);
            echo json_encode(['success' => true]);
            break;

        default:
            attestatoError('Metodo HTTP non supportato', 405);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
