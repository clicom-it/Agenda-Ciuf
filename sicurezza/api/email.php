<?php
// =============================================================
// API — Invio Email Notifiche
// =============================================================
// Gestisce due tipi di notifica email:
//
// POST ?tipo=corso
//   → Manda al dipendente un'email per ogni corso scaduto/mancante
//   Body JSON: { dipendente_id, attestato_id } oppure
//              { sede: "Bari", bulk: true }   per inviare a tutti
//
// POST ?tipo=dvr
//   → Manda alla società DVR l'email con la "foto" della situazione
//     aggiornata del negozio (PDF della lista attestati corrente)
//   Body JSON: { negozio_id }
// =============================================================

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/logic.php';
require_once __DIR__ . '/../includes/email_helper.php';

$pdo  = getConnection();
$tipo = $_GET['tipo'] ?? '';
$body = json_decode(file_get_contents('php://input'), true) ?? [];

function emailApiError(string $msg, int $code = 400): void {
    http_response_code($code);
    echo json_encode(['success' => false, 'message' => $msg]);
    exit;
}


// =============================================================
// EMAIL CORSO — Notifica dipendente per corso scaduto/mancante
// =============================================================
if ($tipo === 'corso') {
    $inviateMail = [];
    $errori      = [];

    $attestatoId = $body['attestato_id'] ?? null;
    $bulk        = !empty($body['bulk']);      // true = manda a tutti i dipendenti con attestati scaduti

    if ($bulk) {
        // Recupera tutti gli attestati scaduti o assenti (li abbiamo già in sic_attestati)
        // Per gli assenti, li deriviamo dalla funzione buildCoverageRows
        $data = getCoreData($pdo);
        $rows = buildCoverageRows($data);

        foreach ($rows as $row) {
            if ($row['esito'] === 'COPERTO') continue;

            // Trova email del dipendente nella tabella dipendenti
            $dip = dbFetchOne($pdo,
                'SELECT email FROM sic_dipendenti WHERE UPPER(TRIM(cognome_nome)) = UPPER(TRIM(?)) AND attivo = 1 LIMIT 1',
                [$row['nome']]
            );
            if (!$dip || !$dip['email']) {
                $errori[] = "Email non trovata per: {$row['nome']}";
                continue;
            }

            $result = inviaEmailCorso($dip['email'], $row['nome'], $row['ruolo'], $row['negozio'], $row['dettaglio']);
            if ($result) {
                $inviateMail[] = $dip['email'];
            } else {
                $errori[] = "Invio fallito a: {$dip['email']}";
            }
        }
    } elseif ($attestatoId) {
        // Singolo attestato — usa i dati dell'attestato + dipendente
        $att = dbFetchOne($pdo, '
            SELECT a.*, d.email
            FROM sic_attestati a
            LEFT JOIN sic_dipendenti d ON UPPER(TRIM(d.cognome_nome)) = UPPER(TRIM(a.cognome_nome)) AND d.attivo = 1
            WHERE a.id = ?
        ', [(int)$attestatoId]);

        if (!$att)           emailApiError('Attestato non trovato', 404);
        if (!$att['email'])  emailApiError('Email dipendente non trovata in anagrafica');

        // Determina se scaduto o assente
        $statoTesto = 'scaduto';  // in questo caso siamo sempre in contesto scaduto/aggiornamento
        $result = inviaEmailCorso($att['email'], $att['cognome_nome'], $att['nome_attestato'], $att['sede_assunzione'], 'ATTESTATO SCADUTO');
        if ($result) $inviateMail[] = $att['email'];
        else $errori[] = "Invio fallito a: {$att['email']}";
    } else {
        emailApiError('Specificare "attestato_id" oppure "bulk: true"');
    }

    echo json_encode([
        'success'  => empty($errori),
        'inviate'  => count($inviateMail),
        'errori'   => $errori,
        'message'  => count($inviateMail) . ' email inviate' . (count($errori) ? ', ' . count($errori) . ' errori' : ''),
    ]);
    exit;
}


// =============================================================
// EMAIL DVR — Notifica alla società che redige i DVR
// =============================================================
if ($tipo === 'dvr') {
    $negozioId = (int)($body['negozio_id'] ?? 0);
    if (!$negozioId) emailApiError('Parametro "negozio_id" mancante');

    $negozio = dbFetchOne($pdo, 'SELECT * FROM sic_negozi WHERE id = ?', [$negozioId]);
    if (!$negozio) emailApiError('Negozio non trovato', 404);

    // Destinatario: email_dvr del negozio oppure DEFAULT da config
    $emailDvr = $negozio['email_dvr'] ?: SIC_EMAIL_DVR_DEFAULT;
    if (!$emailDvr) emailApiError('Nessun indirizzo email DVR configurato per questo negozio. Impostalo in Negozi & DVR oppure in config.php → SIC_EMAIL_DVR_DEFAULT');

    // Genera la "foto DVR" come HTML e la converte in PDF (o la invia come HTML)
    $data    = getCoreData($pdo);
    $rows    = buildCoverageRows($data);
    $negKey  = normalizeKey($negozio['nome_negozio']);

    // Filtra solo le righe di quel negozio
    $righeNegozio = array_filter($rows, fn($r) => normalizeKey($r['negozio']) === $negKey);

    // Genera l'HTML della foto DVR
    $htmlFoto = generaHtmlFotoDvr($negozio, array_values($righeNegozio));

    // Genera PDF da HTML (richiede MPDF, TCPDF o DOMPDF installato)
    // ⚠️ IL DEVELOPER DEVE SCEGLIERE E INSTALLARE UNA LIBRERIA PDF
    //    Opzioni raccomandate:
    //      - composer require mpdf/mpdf
    //      - composer require dompdf/dompdf
    //    Poi decommentare e adattare il blocco qui sotto:
    $pdfPath = null;
    /*
    // Esempio con MPDF:
    require_once __DIR__ . '/../../vendor/autoload.php';
    $mpdf = new \Mpdf\Mpdf();
    $mpdf->WriteHTML($htmlFoto);
    $pdfPath = sys_get_temp_dir() . '/dvr_' . $negozioId . '_' . time() . '.pdf';
    $mpdf->Output($pdfPath, 'F');
    */

    // Corpo dell'email
    $oggetto = "Aggiornamento DVR — Sede di {$negozio['nome_negozio']}";
    $rs = $negozio['ragione_sociale'] ?? '';
    $corpo   = "
        <p>Buongiorno,</p>
        <p>in allegato trovate le modifiche da apportare al DVR per la sede di:
        <strong>{$negozio['nome_negozio']}</strong> ({$rs}).</p>
        <p>Stato attuale: <strong>{$negozio['stato_dvr']}</strong></p>
        <p>Il documento in allegato riporta la situazione aggiornata al "
        . date('d/m/Y') . ".</p>
        <p>Cordiali saluti,<br>Ufficio Sicurezza</p>
    ";

    // Se PDF non generato, allega la foto HTML direttamente nel corpo
    if (!$pdfPath) {
        $corpo .= '<br><hr>' . $htmlFoto;
    }

    $inviata = sicInviaEmail($emailDvr, $oggetto, $corpo, $pdfPath ?? '');

    // Eventuale pulizia del PDF temporaneo
    if ($pdfPath && file_exists($pdfPath)) @unlink($pdfPath);

    echo json_encode([
        'success' => $inviata,
        'message' => $inviata ? "Email DVR inviata a {$emailDvr}" : "Invio fallito a {$emailDvr}",
    ]);
    exit;
}

emailApiError("Tipo '{$tipo}' non riconosciuto. Valori: corso | dvr");


// =============================================================
// FUNZIONI HELPER
// =============================================================

/**
 * Invia email al dipendente per notificargli che deve seguire un corso.
 *
 * @param string $email      Email del dipendente
 * @param string $nome       Cognome e nome
 * @param string $corso      Nome del corso richiesto
 * @param string $sede       Nome del negozio
 * @param string $motivo     ATTESTATO SCADUTO | ATTESTATO ASSENTE
 * @return bool
 */
function inviaEmailCorso(string $email, string $nome, string $corso, string $sede, string $motivo): bool
{
    $oggetto = "Notifica Sicurezza sul Lavoro — Corso: {$corso}";

    $titoloMotivo = $motivo === 'ATTESTATO SCADUTO'
        ? 'Il tuo attestato per questo corso è <strong>scaduto</strong> e deve essere rinnovato.'
        : 'Devi ancora completare questo corso obbligatorio.';

    $corpo = "
    <div style='font-family:Arial,sans-serif;max-width:560px;margin:0 auto;'>
        <div style='background:#1e293b;padding:20px;text-align:center;'>
            <h2 style='color:#fff;margin:0;'>🛡 Sicurezza sul Lavoro</h2>
        </div>
        <div style='padding:24px;background:#f8fafc;'>
            <p style='color:#334155;'>Gentile <strong>{$nome}</strong>,</p>
            <p style='color:#334155;'>ti scriviamo in merito al seguente corso obbligatorio per la sede di <strong>{$sede}</strong>:</p>
            <div style='background:#fff;border-left:4px solid #dc2626;padding:16px;border-radius:0 8px 8px 0;margin:16px 0;'>
                <strong style='font-size:16px;color:#1e293b;'>{$corso}</strong><br>
                <span style='color:#dc2626;margin-top:4px;display:block;'>{$titoloMotivo}</span>
            </div>
            <p style='color:#334155;'>Accedi alla piattaforma formativa per completare il percorso richiesto.</p>
            <p style='color:#64748b;font-size:12px;margin-top:24px;padding-top:12px;border-top:1px solid #e2e8f0;'>
                Email generata automaticamente dal sistema Sicurezza sul Lavoro.
            </p>
        </div>
    </div>";

    return sicInviaEmail($email, $oggetto, $corpo);
}

/**
 * Genera l'HTML della "foto DVR" di un negozio.
 * Viene usata sia nel corpo email (senza PDF) che come sorgente per la generazione PDF.
 *
 * @param array $negozio
 * @param array $righe     Righe di copertura del negozio
 * @return string          HTML
 */
function generaHtmlFotoDvr(array $negozio, array $righe): string
{
    $oggi = date('d/m/Y');
    $rows = '';
    foreach ($righe as $r) {
        $colore = $r['esito'] === 'COPERTO' ? '#16a34a' : '#dc2626';
        $scad   = $r['dataScadenza'] instanceof DateTime ? $r['dataScadenza']->format('d/m/Y') : '—';
        $rows   .= "<tr>
            <td style='padding:8px;border:1px solid #e2e8f0;'>{$r['nome']}</td>
            <td style='padding:8px;border:1px solid #e2e8f0;'>{$r['inquadramento']}</td>
            <td style='padding:8px;border:1px solid #e2e8f0;'>{$r['ruolo']}</td>
            <td style='padding:8px;border:1px solid #e2e8f0;'>{$scad}</td>
            <td style='padding:8px;border:1px solid #e2e8f0;color:{$colore};font-weight:bold;'>{$r['dettaglio']}</td>
        </tr>";
    }

    return "
    <html><body style='font-family:Arial,sans-serif;'>
    <h2>Foto DVR — {$negozio['nome_negozio']}</h2>
    <p>Ragione Sociale: {$negozio['ragione_sociale']}</p>
    <p>Stato DVR: <strong>{$negozio['stato_dvr']}</strong></p>
    <p>Data report: {$oggi}</p>
    <table style='width:100%;border-collapse:collapse;margin-top:16px;'>
        <thead>
            <tr style='background:#1e293b;color:#fff;'>
                <th style='padding:8px;'>Dipendente</th>
                <th style='padding:8px;'>Inquadramento</th>
                <th style='padding:8px;'>Corso richiesto</th>
                <th style='padding:8px;'>Scadenza</th>
                <th style='padding:8px;'>Stato</th>
            </tr>
        </thead>
        <tbody>{$rows}</tbody>
    </table>
    </body></html>";
}
