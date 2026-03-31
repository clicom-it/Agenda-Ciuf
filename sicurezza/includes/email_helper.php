<?php
// =============================================================
// includes/email_helper.php — Helper Invio Email
// =============================================================
// Questo file fornisce la funzione sicInviaEmail() usata da
// api/email.php per inviare le notifiche.
//
// ⚠️ AZIONE RICHIESTA AL DEVELOPER:
//   Il gestionale ha già PHPMailer o una funzione smtp propria.
//   Sostituisci il corpo di sicInviaEmail() con il metodo che
//   usi già nel gestionale (richiamate qui tramite require_once).
//   In questo modo non duplichi le credenziali SMTP.
// =============================================================

require_once __DIR__ . '/../config.php';

/**
 * Invia una email.
 *
 * ⚠️ ADATTARE AL SISTEMA SMTP DEL GESTIONALE.
 * La firma (parametri) non va cambiata — è chiamata da api/email.php.
 *
 * @param string $to        Email destinatario
 * @param string $subject   Oggetto
 * @param string $body      Corpo HTML
 * @param string $attachPath Path assoluto a file allegato (opzionale)
 * @return bool             true se inviata, false se errore
 */
function sicInviaEmail(string $to, string $subject, string $body, string $attachPath = ''): bool
{
    // =========================================================
    // OPZIONE A — Integra con PHPMailer del gestionale
    // =========================================================
    // Esempio (adatta i percorsi e le credenziali):
    //
    // require_once __DIR__ . '/../../vendor/autoload.php'; // o il percorso PHPMailer
    // $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    // $mail->isSMTP();
    // $mail->Host       = 'smtp.tuoserver.it';
    // $mail->SMTPAuth   = true;
    // $mail->Username   = 'user@dominio.it';
    // $mail->Password   = 'password';
    // $mail->SMTPSecure = 'tls';
    // $mail->Port       = 587;
    // $mail->setFrom(SIC_EMAIL_FROM, SIC_EMAIL_FROM_NAME);
    // $mail->addAddress($to);
    // $mail->isHTML(true);
    // $mail->Subject = $subject;
    // $mail->Body    = $body;
    // if ($attachPath) $mail->addAttachment($attachPath);
    // return $mail->send();

    // =========================================================
    // OPZIONE B — mail() nativo PHP (fallback, senza allegati)
    // =========================================================
    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: " . SIC_EMAIL_FROM_NAME . " <" . SIC_EMAIL_FROM . ">\r\n";

    // Se c'è un allegato con mail() nativo devi gestire il multipart manualmente.
    // Si consiglia di usare PHPMailer (Opzione A) per gli allegati.
    if ($attachPath) {
        // Con mail() nativo gli allegati richiedono codifica manuale.
        // Usa Opzione A per i casi con allegato PDF (notifica DVR).
        // Per ora logga un warning e invia senza allegato:
        error_log("[SIC] Allegato non supportato con mail() nativo: {$attachPath}");
    }

    return mail($to, $subject, $body, $headers);
}
