<?php
// =============================================================
// API — Pacchetto Regole Default e Eccezioni (CRUD Admin)
// =============================================================
// Permette all'admin di gestire completamente le regole
// di copertura direttamente dall'interfaccia — senza phpMyAdmin.
//
// Gestisce:
//   GET  ?tipo=default         → lista regole default (sic_regole_attestati)
//   POST ?tipo=default         → aggiunge regola default
//   PUT  ?tipo=default&id=X    → modifica regola default
//   DELETE ?tipo=default&id=X  → elimina regola default
//
//   GET  ?tipo=eccezioni&negozio=NomeNegozio → eccezioni per un negozio
//   POST ?tipo=eccezioni       → aggiunge eccezione per negozio
//   DELETE ?tipo=eccezioni&id=X → elimina eccezione
//
//   GET  ?tipo=config          → legge parametri config (soglia mesi, ecc.)
//   PUT  ?tipo=config          → aggiorna un parametro config
// =============================================================

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';

$pdo    = getConnection();
$method = $_SERVER['REQUEST_METHOD'];
$tipo   = $_GET['tipo'] ?? 'default';
$id     = isset($_GET['id']) ? (int)$_GET['id'] : null;

function regoleError(string $msg, int $code = 400): void {
    http_response_code($code);
    echo json_encode(['success' => false, 'message' => $msg]);
    exit;
}

try {
    // ==========================================================
    // CONFIGURAZIONE GLOBALE (soglia mesi, email dvr default, ecc.)
    // ==========================================================
    if ($tipo === 'config') {
        if ($method === 'GET') {
            $rows = dbFetchAll($pdo, 'SELECT * FROM sic_config ORDER BY chiave');
            echo json_encode($rows);
            exit;
        }
        if ($method === 'PUT') {
            $body   = json_decode(file_get_contents('php://input'), true) ?? [];
            $chiave = $body['chiave'] ?? '';
            $valore = $body['valore'] ?? '';
            if (!$chiave) regoleError('Il campo "chiave" è obbligatorio');

            dbExecute($pdo,
                'INSERT INTO sic_config (chiave, valore) VALUES (?, ?)
                 ON DUPLICATE KEY UPDATE valore = VALUES(valore)',
                [$chiave, $valore]
            );
            echo json_encode(['success' => true]);
            exit;
        }
    }

    // ==========================================================
    // REGOLE DEFAULT (pacchetto globale per tutti i negozi)
    // ==========================================================
    if ($tipo === 'default') {
        switch ($method) {
            case 'GET':
                // Restituisce tutte le regole con il nome del corso e la durata
                $rows = dbFetchAll($pdo, '
                    SELECT r.*, c.id_piattaforma, c.anni_validita
                    FROM sic_regole_attestati r
                    LEFT JOIN sic_corsi_piattaforma c ON c.id = r.id_corso_piattaforma
                    ORDER BY r.nome_ruolo, r.inquadramento
                ');
                echo json_encode($rows);
                break;

            case 'POST':
                // Aggiunge una nuova regola al pacchetto default
                // Body: { inquadramento, nome_ruolo, fabbisogno, id_corso_piattaforma }
                $body = json_decode(file_get_contents('php://input'), true) ?? [];
                $inq  = strtoupper(trim($body['inquadramento'] ?? ''));
                $ruolo = strtoupper(trim($body['nome_ruolo']   ?? ''));
                if (!$inq || !$ruolo) regoleError('inquadramento e nome_ruolo sono obbligatori');

                dbExecute($pdo, '
                    INSERT INTO sic_regole_attestati
                        (inquadramento, nome_ruolo, fabbisogno, id_corso_piattaforma)
                    VALUES (?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE
                        fabbisogno = VALUES(fabbisogno),
                        id_corso_piattaforma = VALUES(id_corso_piattaforma)
                ', [
                    $inq, $ruolo,
                    strtoupper(trim($body['fabbisogno']          ?? '1')),
                    $body['id_corso_piattaforma'] ? (int)$body['id_corso_piattaforma'] : null,
                ]);
                echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
                break;

            case 'PUT':
                if (!$id) regoleError('Parametro "id" mancante');
                $body  = json_decode(file_get_contents('php://input'), true) ?? [];
                $sets  = [];
                $params = [':id' => $id];

                foreach (['inquadramento','nome_ruolo','fabbisogno','id_corso_piattaforma'] as $c) {
                    if (array_key_exists($c, $body)) {
                        $sets[]          = "`{$c}` = :{$c}";
                        $params[":{$c}"] = $body[$c] ?: null;
                    }
                }
                if (empty($sets)) regoleError('Nessun campo da aggiornare');
                dbExecute($pdo, 'UPDATE sic_regole_attestati SET ' . implode(', ', $sets) . ' WHERE id = :id', $params);
                echo json_encode(['success' => true]);
                break;

            case 'DELETE':
                if (!$id) regoleError('Parametro "id" mancante');
                dbExecute($pdo, 'DELETE FROM sic_regole_attestati WHERE id = ?', [$id]);
                echo json_encode(['success' => true]);
                break;

            default:
                regoleError('Metodo non supportato', 405);
        }
        exit;
    }

    // ==========================================================
    // ECCEZIONI PER NEGOZIO (modifiche al default per un atelier)
    // ==========================================================
    if ($tipo === 'eccezioni') {
        switch ($method) {
            case 'GET':
                $negozio = $_GET['negozio'] ?? '';
                $sql  = 'SELECT * FROM sic_regole_eccezioni';
                $params = [];
                if ($negozio) {
                    $sql .= ' WHERE UPPER(TRIM(nome_negozio)) = UPPER(TRIM(?))';
                    $params[] = $negozio;
                }
                echo json_encode(dbFetchAll($pdo, $sql . ' ORDER BY nome_negozio, nome_ruolo', $params));
                break;

            case 'POST':
                $body = json_decode(file_get_contents('php://input'), true) ?? [];
                foreach (['nome_negozio','inquadramento','nome_ruolo','tipo_eccezione'] as $req) {
                    if (empty($body[$req])) regoleError("Il campo '{$req}' è obbligatorio");
                }
                $tipoEc = strtoupper(trim($body['tipo_eccezione']));
                if (!in_array($tipoEc, ['AGGIUNGI','ESCLUDI'])) {
                    regoleError('tipo_eccezione deve essere "AGGIUNGI" o "ESCLUDI"');
                }
                dbExecute($pdo, '
                    INSERT INTO sic_regole_eccezioni
                        (nome_negozio, inquadramento, nome_ruolo, tipo_eccezione)
                    VALUES (?, ?, ?, ?)
                ', [
                    trim($body['nome_negozio']),
                    strtoupper(trim($body['inquadramento'])),
                    strtoupper(trim($body['nome_ruolo'])),
                    $tipoEc,
                ]);
                echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
                break;

            case 'DELETE':
                if (!$id) regoleError('Parametro "id" mancante');
                dbExecute($pdo, 'DELETE FROM sic_regole_eccezioni WHERE id = ?', [$id]);
                echo json_encode(['success' => true]);
                break;

            default:
                regoleError('Metodo non supportato', 405);
        }
        exit;
    }

    regoleError("Tipo '{$tipo}' non riconosciuto. Valori: default | eccezioni | config");

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
