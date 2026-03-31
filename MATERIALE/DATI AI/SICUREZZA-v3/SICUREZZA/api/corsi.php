<?php
// =============================================================
// API — Catalogo Corsi della Piattaforma (CRUD)
// =============================================================
// Gestisce il catalogo dei corsi del modulo sicurezza,
// ciascuno dei quali può essere mappato all'ID corrispondente
// sulla piattaforma di sicurezza esterna.
//
// Questa tabella è il "ponte" tra i nomi dei corsi usati
// internamente (es. "PREPOSTO") e gli ID della piattaforma
// (es. id_piattaforma = "C004").
//
// Metodi HTTP:
//   GET    → lista tutti i corsi
//   POST   → aggiunge un nuovo corso
//   PUT    → aggiorna un corso (incluso l'id_piattaforma) → usa ?id=X
//   DELETE → elimina un corso → usa ?id=X
// =============================================================

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';

$pdo    = getConnection();
$method = $_SERVER['REQUEST_METHOD'];
$id     = isset($_GET['id']) ? (int)$_GET['id'] : null;

function corsoError(string $msg, int $code = 400): void {
    http_response_code($code);
    echo json_encode(['success' => false, 'message' => $msg]);
    exit;
}

try {
    switch ($method) {

        // GET: lista tutti i corsi con le loro configurazioni
        case 'GET':
            $rows = dbFetchAll($pdo, 'SELECT * FROM sic_corsi_piattaforma ORDER BY nome_locale');
            echo json_encode($rows);
            break;

        // POST: aggiunge un nuovo tipo di corso
        // Body JSON: { nome_locale, id_piattaforma, id_aggiornamento, anni_validita }
        case 'POST':
            $body = json_decode(file_get_contents('php://input'), true) ?? [];
            $nome = strtoupper(trim($body['nome_locale'] ?? ''));
            if (!$nome) corsoError('Il campo "nome_locale" è obbligatorio');

            dbExecute($pdo, '
                INSERT INTO sic_corsi_piattaforma
                    (nome_locale, id_piattaforma, id_aggiornamento, anni_validita, attivo)
                VALUES (?, ?, ?, ?, 1)
            ', [
                $nome,
                trim($body['id_piattaforma']   ?? '') ?: null,
                trim($body['id_aggiornamento'] ?? '') ?: null,
                (int)($body['anni_validita']   ?? 5),
            ]);

            // Sincronizza anche sic_durata_corsi per compatibilità con il motore logic.php
            dbExecute($pdo, '
                INSERT IGNORE INTO sic_durata_corsi (corso, anni_validita)
                VALUES (?, ?)
                ON DUPLICATE KEY UPDATE anni_validita = VALUES(anni_validita)
            ', [$nome, (int)($body['anni_validita'] ?? 5)]);

            echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
            break;

        // PUT: aggiorna un corso (tipicamente per inserire l'id_piattaforma ricevuto dal dev)
        case 'PUT':
            if (!$id) corsoError('Parametro "id" mancante');
            $body = json_decode(file_get_contents('php://input'), true) ?? [];

            $sets   = [];
            $params = [':id' => $id];

            $campi = ['nome_locale', 'id_piattaforma', 'id_aggiornamento', 'anni_validita', 'attivo'];
            foreach ($campi as $campo) {
                if (array_key_exists($campo, $body)) {
                    $valore = $campo === 'nome_locale' ? strtoupper(trim($body[$campo])) : $body[$campo];
                    $sets[]               = "`{$campo}` = :{$campo}";
                    $params[":{$campo}"]  = $valore ?: null;
                }
            }
            if (empty($sets)) corsoError('Nessun campo da aggiornare');

            dbExecute($pdo, 'UPDATE sic_corsi_piattaforma SET ' . implode(', ', $sets) . ' WHERE id = :id', $params);

            // Aggiorna anche sic_durata_corsi se è cambiata la validità o il nome
            if (isset($body['anni_validita']) || isset($body['nome_locale'])) {
                $corso = dbFetchOne($pdo, 'SELECT * FROM sic_corsi_piattaforma WHERE id = ?', [$id]);
                if ($corso) {
                    dbExecute($pdo, '
                        INSERT INTO sic_durata_corsi (corso, anni_validita)
                        VALUES (?, ?)
                        ON DUPLICATE KEY UPDATE anni_validita = VALUES(anni_validita)
                    ', [$corso['nome_locale'], $corso['anni_validita']]);
                }
            }

            echo json_encode(['success' => true]);
            break;

        // DELETE: elimina un corso
        // ATTENZIONE: non elimina gli attestati già emessi per questo corso.
        // Rimuoverlo significa che il sistema non lo considererà più nelle regole.
        case 'DELETE':
            if (!$id) corsoError('Parametro "id" mancante');
            // Controlla che non sia usato in regole attive
            $usato = dbFetchOne($pdo,
                'SELECT COUNT(*) AS n FROM sic_regole_attestati WHERE id_corso_piattaforma = ?',
                [$id]
            );
            if ($usato['n'] > 0) {
                corsoError('Corso in uso in ' . $usato['n'] . ' regole. Rimuovilo prima dalle regole.');
            }
            dbExecute($pdo, 'DELETE FROM sic_corsi_piattaforma WHERE id = ?', [$id]);
            echo json_encode(['success' => true]);
            break;

        default:
            corsoError('Metodo HTTP non supportato', 405);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
