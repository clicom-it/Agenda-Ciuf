<?php
// =============================================================
// MODULO SICUREZZA — Pagina Principale
// =============================================================
// Questa è l'unica pagina HTML del modulo.
// Carica CSS e JS, definisce tutta la struttura dell'interfaccia
// e lascia che app.js gestisca la logica interattiva via API.
//
// Per integrare nel gestionale esistente:
//   1. Copia questo file nella cartella del gestionale
//   2. Adatta il require_once di config.php al percorso corretto
//   3. Opzionale: aggiungi il controllo sessione in config.php
// =============================================================

require_once __DIR__ . '/config.php';

// --- Controllo autenticazione (opzionale, attivabile in config.php) ---
// Se abilitato in config.php, verifica che l'utente sia loggato nel gestionale
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
    <!-- Font Inter da Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- CSS del modulo -->
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

<!-- ============================================================
     TOPBAR — Barra superiore fissa
     ============================================================ -->
<header id="topbar">
    <div class="logo">🛡 <span><?= htmlspecialchars(SIC_APP_NAME) ?></span></div>
    <div class="spacer"></div>
    <!-- Testo ultima sincronizzazione (aggiornato da app.js) -->
    <span id="sync-info">Clicca per sincronizzare i dipendenti</span>
    <!-- Pulsante sincronizzazione: chiama syncDipendenti() in app.js -->
    <button id="btn-sync" onclick="syncDipendenti()" title="Sincronizza dipendenti dal gestionale">
        ⟳ Sincronizza Dipendenti
    </button>
</header>

<!-- ============================================================
     SIDEBAR — Menu di navigazione laterale
     ============================================================ -->
<nav id="sidebar">
    <!-- data-section: deve corrispondere all'ID della sezione (section-{data-section}) -->
    <div class="nav-item active" data-section="dashboard">
        <span class="nav-icon">📊</span> Dashboard
    </div>
    <div class="nav-item" data-section="attestati">
        <span class="nav-icon">📜</span> Attestati
    </div>
    <div class="nav-separator"></div>
    <div class="nav-item" data-section="negozi">
        <span class="nav-icon">🏪</span> Negozi & DVR
    </div>
    <div class="nav-item" data-section="report">
        <span class="nav-icon">📋</span> Report Corsi
    </div>
</nav>

<!-- ============================================================
     AREA CONTENUTO PRINCIPALE
     ============================================================ -->
<main id="main">

    <!-- ----------------------------------------------------------
         SEZIONE: DASHBOARD
         Mostra le card di tutti i negozi con il semaforo di stato.
         I dati arrivano da api/dashboard.php.
         ---------------------------------------------------------- -->
    <section id="section-dashboard" class="section active">
        <div class="section-header">
            <div>
                <h1>📊 Dashboard Sicurezza</h1>
                <p>Panoramica su copertura attestati e stato DVR di tutti i negozi</p>
            </div>
        </div>

        <!-- Statistiche rapide in cima -->
        <div class="stats-row" id="dashboard-stats">
            <!-- Popolate da app.js → renderStats() -->
        </div>

        <!-- Barra filtri negozi -->
        <div class="filter-bar">
            <input type="text" id="search-negozio" placeholder="🔍 Cerca negozio o ragione sociale...">
            <select id="filter-stato">
                <option value="">Tutti gli stati</option>
                <option value="coperto">🟢 Coperto</option>
                <option value="parziale">🟡 Parziale</option>
                <option value="critico">🔴 Critico</option>
            </select>
            <select id="filter-dvr">
                <option value="">Tutti i DVR</option>
                <option value="OK">DVR OK</option>
                <option value="DA AGGIORNARE">DVR da aggiornare</option>
                <option value="DA EMETTERE">DVR da emettere</option>
            </select>
            <span class="result-count" id="store-count"></span>
        </div>

        <!-- Griglia card negozi — popolata da app.js → renderStoreCards() -->
        <div id="negozi-grid">
            <div class="loading"><div class="spinner"></div> Caricamento...</div>
        </div>
    </section>


    <!-- ----------------------------------------------------------
         SEZIONE: ATTESTATI
         Form per aggiungere nuovi attestati + tabella riepilogativa.
         I dati arrivano da api/attestati.php.
         ---------------------------------------------------------- -->
    <section id="section-attestati" class="section">
        <div class="section-header">
            <div>
                <h1>📜 Gestione Attestati</h1>
                <p>Aggiungi, visualizza ed elimina gli attestati formativi dei dipendenti</p>
            </div>
        </div>

        <!-- Form aggiunta attestato -->
        <div class="form-card">
            <h3>➕ Aggiungi Attestato</h3>
            <!-- onsubmit chiama addAttestato() in app.js -->
            <form onsubmit="addAttestato(event)">
                <div class="form-row">
                    <div class="form-group">
                        <label>Dipendente (Cognome Nome)</label>
                        <!-- Il nome deve corrispondere ESATTAMENTE a quello in sic_dipendenti.cognome_nome -->
                        <input type="text" name="att_nome" placeholder="es. ROSSI MARIO" required>
                    </div>
                    <div class="form-group">
                        <label>Sede</label>
                        <input type="text" name="att_sede" placeholder="es. Bari" required>
                    </div>
                    <div class="form-group">
                        <label>Corso / Attestato</label>
                        <input type="text" name="att_corso" placeholder="es. PREPOSTO" required>
                    </div>
                    <div class="form-group">
                        <label>Data Emissione</label>
                        <input type="date" name="att_data" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group" style="flex:2">
                        <label>Note (opzionale)</label>
                        <input type="text" name="att_note" placeholder="es. Ente formatore, numero attestato...">
                    </div>
                    <div class="form-group" style="flex:0;justify-content:flex-end">
                        <label>&nbsp;</label>
                        <button type="submit" class="btn btn-primary">+ Aggiungi Attestato</button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Filtro tabella attestati -->
        <div class="filter-bar">
            <input type="text" id="search-att" placeholder="🔍 Cerca per dipendente, sede o corso...">
        </div>

        <!-- Tabella attestati — popolata da app.js → renderAttestati() -->
        <div id="attestati-table-wrap">
            <div class="loading"><div class="spinner"></div> Caricamento...</div>
        </div>
    </section>


    <!-- ----------------------------------------------------------
         SEZIONE: NEGOZI & DVR
         Gestione anagrafica negozi con aggiornamento date DVR.
         I dati arrivano da api/negozi.php.
         ---------------------------------------------------------- -->
    <section id="section-negozi" class="section">
        <div class="section-header">
            <div>
                <h1>🏪 Negozi & DVR</h1>
                <p>Gestisci l'anagrafica dei negozi e aggiorna le date del Documento di Valutazione dei Rischi</p>
            </div>
        </div>

        <!-- Form aggiunta nuovo negozio -->
        <div class="form-card">
            <h3>➕ Aggiungi Negozio</h3>
            <form onsubmit="addNegozio(event)">
                <div class="form-row">
                    <div class="form-group">
                        <label>Nome Negozio *</label>
                        <input type="text" name="neg_nome" placeholder="es. Bari" required>
                    </div>
                    <div class="form-group">
                        <label>Ragione Sociale</label>
                        <input type="text" name="neg_rs" placeholder="es. Come in una Favola">
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="neg_mail" placeholder="bari@esempio.it">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group" style="flex:2">
                        <label>Via / Indirizzo</label>
                        <input type="text" name="neg_via" placeholder="Via Roma, 1">
                    </div>
                    <div class="form-group">
                        <label>Città</label>
                        <input type="text" name="neg_localita" placeholder="Bari">
                    </div>
                    <div class="form-group" style="flex:0;min-width:90px">
                        <label>CAP</label>
                        <input type="text" name="neg_cap" placeholder="70100" maxlength="5">
                    </div>
                    <div class="form-group" style="flex:0;min-width:70px">
                        <label>Prov.</label>
                        <input type="text" name="neg_prov" placeholder="BA" maxlength="2" style="text-transform:uppercase">
                    </div>
                    <div class="form-group" style="flex:0;justify-content:flex-end">
                        <label>&nbsp;</label>
                        <button type="submit" class="btn btn-primary">+ Aggiungi</button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Tabella negozi con Date DVR modificabili inline -->
        <p style="font-size:12px;color:#64748b;margin-bottom:10px">
            💡 <strong>Suggerimento:</strong> modifica la data DVR direttamente nella tabella — il sistema aggiorna lo stato automaticamente.
        </p>
        <div id="negozi-table-wrap">
            <div class="loading"><div class="spinner"></div> Caricamento...</div>
        </div>
    </section>


    <!-- ----------------------------------------------------------
         SEZIONE: REPORT CORSI
         Report aggregato dei corsi/aggiornamenti necessari.
         I dati arrivano da api/report.php.
         ---------------------------------------------------------- -->
    <section id="section-report" class="section">
        <div class="section-header">
            <div>
                <h1>📋 Report Corsi Necessari</h1>
                <p>Riepilogo aggregato per ragione sociale dei corsi da erogare e DVR da aggiornare</p>
            </div>
        </div>

        <!-- Tabella report — popolata da app.js → loadReport() -->
        <div id="report-wrap">
            <div class="loading"><div class="spinner"></div> Caricamento...</div>
        </div>
    </section>

</main><!-- fine #main -->


<!-- ============================================================
     MODALE DETTAGLIO NEGOZIO
     Appare quando si clicca su una card negozio nella Dashboard.
     Il contenuto viene iniettato dinamicamente da app.js → openStoreModal()
     ============================================================ -->
<div id="modal-overlay" class="modal-overlay" onclick="if(event.target===this)closeModal()">
    <div class="modal">
        <div class="modal-header">
            <div>
                <h2 id="modal-title">Negozio</h2>
                <p class="sub" id="modal-subtitle"></p>
            </div>
            <button class="modal-close" onclick="closeModal()">✕</button>
        </div>
        <div class="modal-body" id="modal-body">
            <!-- Contenuto iniettato da app.js -->
        </div>
    </div>
</div>


<!-- ============================================================
     CONTENITORE NOTIFICHE TOAST
     I messaggi temporanei vengono aggiunti qui da app.js → showToast()
     ============================================================ -->
<div id="toast-container"></div>


<!-- JavaScript del modulo (caricato in fondo per performance) -->
<script src="assets/app.js"></script>

</body>
</html>
