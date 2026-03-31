<?php
// =============================================================
// API — Lista Dipendenti Attivi (sola lettura)
// =============================================================
// GET → lista dipendenti attivi con cognome_nome e sede
//   Filtri opzionali: ?attivi=1 (default: solo attivi)
// =============================================================

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';

$pdo    = getConnection();
$method = $_SERVER['REQUEST_METHOD'];

if ($method !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Metodo HTTP non supportato']);
    exit;
}

try {
    $soloAttivi = ($_GET['attivi'] ?? '1') === '1';
    $sql = 'SELECT id, cognome_nome, sede_assunzione, inquadramento
            FROM sic_dipendenti';
    if ($soloAttivi) {
        $sql .= ' WHERE attivo = 1';
    }
    $sql .= ' ORDER BY cognome_nome';

    $rows = dbFetchAll($pdo, $sql);
    echo json_encode($rows);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
