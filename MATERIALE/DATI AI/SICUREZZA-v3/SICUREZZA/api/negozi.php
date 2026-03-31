<?php
// =============================================================
// API — Gestione Negozi (CRUD)
// =============================================================
// Gestisce le operazioni sui negozi: lettura, inserimento,
// modifica (soprattutto date DVR) ed eliminazione.
//
// Metodi HTTP supportati:
//   GET    → lista tutti i negozi
//   POST   → inserisce un nuovo negozio
//   PUT    → aggiorna un negozio esistente (usa ?id=X)
//   DELETE → elimina un negozio (usa ?id=X)
// =============================================================

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/logic.php';

$pdo    = getConnection();
$method = $_SERVER['REQUEST_METHOD'];
$id     = isset($_GET['id']) ? (int)$_GET['id'] : null;

// Funzione per rispondere con errore e termmare
function apiError(string $msg, int $code = 400): void {
    http_response_code($code);
    echo json_encode(['success' => false, 'message' => $msg]);
    exit;
}

try {
    switch ($method) {

        // ---------------------------------------------------------
        // GET: Lista tutti i negozi con il loro stato DVR calcolato
        // ---------------------------------------------------------
        case 'GET':
            $rows = dbFetchAll($pdo, 'SELECT * FROM sic_negozi ORDER BY nome_negozio');

            // Aggiunge il campo stato_dvr calcolato dinamicamente
            // (oltre a quello salvato in DB, che potrebbe essere vecchio)
            foreach ($rows as &$row) {
                $dataDvr = $row['data_dvr']              ? new DateTime($row['data_dvr'])              : null;
                $dataVar = $row['data_ultima_variazione'] ? new DateTime($row['data_ultima_variazione']) : null;

                // Ricalcola lo stato ogni volta per sicurezza
                $row['stato_dvr_calcolato'] = computeDvrStatus($dataDvr, $dataVar);
                // Formato data leggibile
                $row['data_dvr_fmt']    = $dataDvr ? $dataDvr->format('d/m/Y')    : null;
                $row['data_var_fmt']    = $dataVar  ? $dataVar->format('d/m/Y')    : null;
            }
            unset($row);
            echo json_encode($rows);
            break;

        // ---------------------------------------------------------
        // POST: Inserisce un nuovo negozio
        // ---------------------------------------------------------
        case 'POST':
            $body = json_decode(file_get_contents('php://input'), true) ?? [];

            $nomeNegozio = trim($body['nome_negozio'] ?? '');
            if (!$nomeNegozio) apiError('Il campo "nome_negozio" è obbligatorio');

            dbExecute($pdo, '
                INSERT INTO sic_negozi
                    (nome_negozio, ragione_sociale, mail, via, localita, cap, provincia)
                VALUES
                    (:nome, :rs, :mail, :via, :loc, :cap, :prov)
            ', [
                ':nome' => $nomeNegozio,
                ':rs'   => trim($body['ragione_sociale'] ?? ''),
                ':mail' => trim($body['mail']            ?? ''),
                ':via'  => trim($body['via']             ?? ''),
                ':loc'  => trim($body['localita']        ?? ''),
                ':cap'  => trim($body['cap']             ?? ''),
                ':prov' => strtoupper(trim($body['provincia'] ?? '')),
            ]);

            echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
            break;

        // ---------------------------------------------------------
        // PUT: Aggiorna un negozio esistente
        // Tipicamente usato per aggiornare la data DVR
        // ---------------------------------------------------------
        case 'PUT':
            if (!$id) apiError('Parametro "id" mancante');

            $body = json_decode(file_get_contents('php://input'), true) ?? [];

            // Costruisce dinamicamente solo i campi inviati (non sovrascrive tutto)
            $sets   = [];
            $params = [':id' => $id];

            $campiPermessi = [
                'nome_negozio', 'ragione_sociale', 'mail',
                'via', 'localita', 'cap', 'provincia',
                'data_dvr', 'data_ultima_variazione',
            ];

            foreach ($campiPermessi as $campo) {
                if (array_key_exists($campo, $body)) {
                    $sets[]         = "`{$campo}` = :{$campo}";
                    $params[":{$campo}"] = $body[$campo] ?: null;  // Stringa vuota → NULL
                }
            }

            if (empty($sets)) apiError('Nessun campo da aggiornare');

            // Quando si aggiorna la data DVR, ricalcola anche lo stato
            if (isset($body['data_dvr']) || isset($body['data_ultima_variazione'])) {
                // Legge i valori attuali + eventuali nuovi per il ricalcolo
                $negozio = dbFetchOne($pdo, 'SELECT * FROM sic_negozi WHERE id = ?', [$id]);
                $dataDvr = isset($body['data_dvr'])
                    ? ($body['data_dvr'] ? new DateTime($body['data_dvr']) : null)
                    : ($negozio['data_dvr'] ? new DateTime($negozio['data_dvr']) : null);
                $dataVar = isset($body['data_ultima_variazione'])
                    ? ($body['data_ultima_variazione'] ? new DateTime($body['data_ultima_variazione']) : null)
                    : ($negozio['data_ultima_variazione'] ? new DateTime($negozio['data_ultima_variazione']) : null);

                $nuovoStato     = computeDvrStatus($dataDvr, $dataVar);
                $sets[]         = '`stato_dvr` = :stato_dvr';
                $params[':stato_dvr'] = $nuovoStato;
            }

            dbExecute($pdo, 'UPDATE sic_negozi SET ' . implode(', ', $sets) . ' WHERE id = :id', $params);
            echo json_encode(['success' => true]);
            break;

        // ---------------------------------------------------------
        // DELETE: Elimina un negozio
        // ATTENZIONE: elimina anche le eccezioni collegate!
        // I dipendenti e gli attestati con quella sede rimangono.
        // ---------------------------------------------------------
        case 'DELETE':
            if (!$id) apiError('Parametro "id" mancante');

            // Prima verifica che il negozio esista
            $negozio = dbFetchOne($pdo, 'SELECT nome_negozio FROM sic_negozi WHERE id = ?', [$id]);
            if (!$negozio) apiError('Negozio non trovato', 404);

            // Elimina le eccezioni collegate a questo negozio
            dbExecute($pdo,
                'DELETE FROM sic_regole_eccezioni WHERE UPPER(TRIM(nome_negozio)) = UPPER(TRIM(?))',
                [$negozio['nome_negozio']]
            );

            // Elimina il negozio
            dbExecute($pdo, 'DELETE FROM sic_negozi WHERE id = ?', [$id]);
            echo json_encode(['success' => true]);
            break;

        default:
            apiError('Metodo HTTP non supportato', 405);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
