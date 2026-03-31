<?php
// =============================================================
// MODULO SICUREZZA — Connessione al Database
// =============================================================
// Questo file crea e restituisce una connessione PDO al database.
// Usa i parametri definiti in config.php.
// PDO è lo standard moderno PHP per gestire i database in modo
// sicuro (previene SQL injection tramite prepared statements).
// =============================================================

require_once __DIR__ . '/../config.php';

/**
 * Restituisce una connessione PDO al database del modulo sicurezza.
 * La connessione viene creata una sola volta e riutilizzata (pattern Singleton).
 *
 * @return PDO
 * @throws PDOException se la connessione fallisce
 */
function getConnection(): PDO
{
    // Variabile statica: viene inizializzata solo alla prima chiamata
    static $pdo = null;

    if ($pdo === null) {
        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            SIC_DB_HOST,
            SIC_DB_PORT,
            SIC_DB_NAME,
            SIC_DB_CHARSET
        );

        $options = [
            // Lancia eccezioni in caso di errore SQL (più sicuro dei codici di errore)
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            // Restituisce i risultati come array associativi di default
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            // Disabilita l'emulazione dei prepared statements (più sicuro)
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        $pdo = new PDO($dsn, SIC_DB_USER, SIC_DB_PASS, $options);
    }

    return $pdo;
}

/**
 * Helper: esegue una query con parametri e restituisce tutti i risultati.
 * Uso: $rows = dbFetchAll($pdo, "SELECT * FROM sic_negozi WHERE provincia = ?", ['BA']);
 *
 * @param PDO    $pdo    Connessione database
 * @param string $sql    Query SQL con placeholder "?"
 * @param array  $params Valori da legare ai placeholder
 * @return array
 */
function dbFetchAll(PDO $pdo, string $sql, array $params = []): array
{
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/**
 * Helper: esegue una query con parametri e restituisce la prima riga.
 * Restituisce null se non trova nulla.
 *
 * @param PDO    $pdo
 * @param string $sql
 * @param array  $params
 * @return array|null
 */
function dbFetchOne(PDO $pdo, string $sql, array $params = []): ?array
{
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $row = $stmt->fetch();
    return $row !== false ? $row : null;
}

/**
 * Helper: esegue INSERT/UPDATE/DELETE e restituisce il numero di righe modificate.
 *
 * @param PDO    $pdo
 * @param string $sql
 * @param array  $params
 * @return int numero di righe affette
 */
function dbExecute(PDO $pdo, string $sql, array $params = []): int
{
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->rowCount();
}
