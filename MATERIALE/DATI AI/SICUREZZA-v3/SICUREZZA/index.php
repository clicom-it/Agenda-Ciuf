<?php
// =============================================================
// MODULO SICUREZZA V2 — Standalone Wrapper
// =============================================================
// Questo file funge da contenitore standalone per testare
// il modulo sicurezza direttamente dal browser (localhost).
// Include la vista "sicurezza_view.php".
// =============================================================

define('SIC_URL_BASE', ''); // Per localhost base, senza sottocartella /sicurezza
require_once __DIR__ . '/config.php';

// --- Controllo autenticazione (opzionale) ---
if (defined('SIC_SESSION_KEY')) {
    session_start();
    if (empty($_SESSION[SIC_SESSION_KEY])) {
        header('Location: ' . SIC_SESSION_REDIRECT);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars(SIC_APP_NAME) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            margin: 0; padding: 0;
            background-color: #f1f5f9;
        }
        .standalone-wrapper {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background: #fff;
            min-height: 100vh;
            box-shadow: 0 0 15px rgba(0,0,0,0.05);
        }
    </style>
</head>
<body>

    <div class="standalone-wrapper">
        <div style="padding-bottom: 15px; margin-bottom: 15px; border-bottom: 1px solid #e2e8f0;">
            <h1 style="margin:0; font-size:20px; font-family:'Inter', sans-serif; color:#0f172a;">🛡 <?= htmlspecialchars(SIC_APP_NAME) ?></h1>
            <p style="margin:4px 0 0 0; font-size:13px; color:#64748b; font-family:'Inter', sans-serif;">Modalità Standalone (V2)</p>
        </div>

        <?php
        // Forza l'inclusione di sicurezza_view.php
        require __DIR__ . '/sicurezza_view.php';
        ?>
    </div>

</body>
</html>
