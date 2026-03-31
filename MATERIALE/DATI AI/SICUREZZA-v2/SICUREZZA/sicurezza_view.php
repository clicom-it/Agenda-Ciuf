<?php
// =============================================================
// MODULO SICUREZZA V2 — Vista Integrata nel Gestionale
// =============================================================
// Includere da dipendenti.php:
//   case 'sicurezza':
//       require_once __DIR__ . '/sicurezza/sicurezza_view.php';
//       break;
//
// Non contiene layout (head/body/topbar/sidebar):
// eredita tutto dal gestionale.
// =============================================================

$sicBasePath = __DIR__;
require_once $sicBasePath . '/config.php';
$sicUrlBase = '/sicurezza'; // ← ADATTARE se la cartella ha nome diverso
?>

<link rel="stylesheet" href="<?= $sicUrlBase ?>/assets/style.css">

<div id="sic-app">

<!-- ============================================================
     BARRA TAB V2
     ============================================================ -->
<div class="sic-tabbar">
    <div class="sic-tabbar-left">
        <button class="sic-tab active" data-section="dashboard" onclick="sicShowSection('dashboard')">📊 Dashboard</button>
        <button class="sic-tab" data-section="attestati"  onclick="sicShowSection('attestati')">📜 Attestati</button>
        <button class="sic-tab" data-section="negozi"     onclick="sicShowSection('negozi')">🏪 Negozi &amp; DVR</button>
        <button class="sic-tab" data-section="report"     onclick="sicShowSection('report')">📋 Report Corsi</button>
        <button class="sic-tab sic-tab-admin" data-section="admin" onclick="sicShowSection('admin')">⚙️ Admin</button>
    </div>
    <div class="sic-tabbar-right">
        <span id="sic-sync-info" class="sic-sync-info"></span>
    </div>
</div>


<!-- ============================================================
     DASHBOARD — Semaforo negozi
     ============================================================ -->
<div id="sic-section-dashboard" class="sic-section active">
    <div class="sic-stats-row" id="sic-dashboard-stats"></div>
    <div class="sic-filter-bar">
        <input type="text" id="sic-search-negozio" placeholder="🔍 Cerca negozio..." oninput="sicFilterStores()">
        <select id="sic-filter-stato" onchange="sicFilterStores()">
            <option value="">Tutti gli stati</option>
            <option value="coperto">🟢 Coperto</option>
            <option value="parziale">🟡 Parziale</option>
            <option value="critico">🔴 Critico</option>
        </select>
        <select id="sic-filter-dvr" onchange="sicFilterStores()">
            <option value="">Tutti i DVR</option>
            <option value="OK">DVR OK</option>
            <option value="DA AGGIORNARE">DVR da aggiornare</option>
            <option value="DA EMETTERE">DVR da emettere</option>
        </select>
        <span class="sic-result-count" id="sic-store-count"></span>
    </div>
    <div id="sic-negozi-grid"><div class="sic-loading"><div class="sic-spinner"></div> Caricamento...</div></div>
</div>


<!-- ============================================================
     ATTESTATI — Con colonna email per notifica dipendenti
     ============================================================ -->
<div id="sic-section-attestati" class="sic-section">
    <div class="sic-form-card">
        <h3>➕ Aggiungi Attestato Manuale</h3>
        <p style="font-size:12px;color:#64748b;margin-bottom:12px">
            ℹ️ Gli attestati vengono aggiornati automaticamente tramite polling della piattaforma sicurezza.
            Usa questo form solo per inserimenti manuali di emergenza.
        </p>
        <form onsubmit="sicAddAttestato(event)">
            <div class="sic-form-row">
                <div class="sic-form-group">
                    <label>Dipendente (Cognome Nome)</label>
                    <input type="text" name="att_nome" placeholder="es. ROSSI MARIO" required>
                </div>
                <div class="sic-form-group">
                    <label>Sede</label>
                    <input type="text" name="att_sede" placeholder="es. Bari" required>
                </div>
                <div class="sic-form-group">
                    <label>Corso / Attestato</label>
                    <input type="text" name="att_corso" placeholder="es. PREPOSTO" required>
                </div>
                <div class="sic-form-group">
                    <label>Data Emissione</label>
                    <input type="date" name="att_data" required>
                </div>
                <div class="sic-form-group" style="justify-content:flex-end">
                    <label>&nbsp;</label>
                    <button type="submit" class="sic-btn sic-btn-primary">+ Aggiungi</button>
                </div>
            </div>
        </form>
    </div>
    <div class="sic-filter-bar">
        <input type="text" id="sic-search-att" placeholder="🔍 Cerca per dipendente, sede o corso..." oninput="sicFilterAttestati()">
        <!-- Pulsante bulk: invia mail a tutti i dipendenti con attestati scaduti/mancanti -->
        <button class="sic-btn sic-btn-warn" onclick="sicSendAllCorseMail()" title="Invia notifica a tutti i dipendenti con corsi scaduti o assenti">
            📧 Invia a tutti gli scaduti
        </button>
    </div>
    <div id="sic-attestati-table-wrap"><div class="sic-loading"><div class="sic-spinner"></div> Caricamento...</div></div>
</div>


<!-- ============================================================
     NEGOZI & DVR — Senza "aggiungi negozio" (si gestisce in anagrafica atelier)
     ============================================================ -->
<div id="sic-section-negozi" class="sic-section">
    <p style="font-size:12px;color:#64748b;margin-bottom:14px">
        💡 I negozi vengono sincronizzati automaticamente dall'anagrafica Atelier del gestionale (solo tipo "diretto").<br>
        Modifica la data DVR direttamente nella tabella. Per le notifiche al consulente DVR usa il pulsante 📧.
    </p>
    <div id="sic-negozi-table-wrap"><div class="sic-loading"><div class="sic-spinner"></div> Caricamento...</div></div>
</div>


<!-- ============================================================
     REPORT CORSI
     ============================================================ -->
<div id="sic-section-report" class="sic-section">
    <p style="font-size:13px;color:#64748b;margin-bottom:16px">
        Riepilogo aggregato per ragione sociale dei corsi da erogare, aggiornamenti necessari e DVR da gestire.
    </p>
    <div id="sic-report-wrap"><div class="sic-loading"><div class="sic-spinner"></div> Caricamento...</div></div>
</div>


<!-- ============================================================
     ADMIN — Pannello configurazione (solo utenti admin)
     ============================================================ -->
<div id="sic-section-admin" class="sic-section">

    <!-- Sincronizzazione manuale dipendenti -->
    <div class="sic-admin-block">
        <h3>🔄 Sincronizzazione Dipendenti</h3>
        <p>Aggiorna manualmente i dipendenti dal gestionale (normalmente avviene automaticamente tramite cron job).</p>
        <button class="sic-btn sic-btn-primary" onclick="sicSyncDipendenti()">⟳ Sincronizza Ora</button>
        <button class="sic-btn" onclick="sicPollPiattaforma()" style="margin-left:8px">📡 Polling Piattaforma</button>
        <div id="sic-sync-result" style="margin-top:12px;font-size:12px;color:#64748b"></div>
    </div>

    <!-- Configurazione globale -->
    <div class="sic-admin-block" style="margin-top:20px">
        <h3>⚙️ Configurazione Globale</h3>
        <div id="sic-config-form"></div>
    </div>

    <!-- Catalogo corsi con ID piattaforma -->
    <div class="sic-admin-block" style="margin-top:20px">
        <h3>📚 Catalogo Corsi &amp; ID Piattaforma</h3>
        <p style="font-size:12px;color:#64748b;margin-bottom:12px">
            Mappa ogni corso al relativo ID sulla piattaforma sicurezza esterna.
            Gli ID ti vengono forniti dal developer della piattaforma.
        </p>
        <div class="sic-form-card" style="margin-bottom:12px">
            <form onsubmit="sicAddCorso(event)">
                <div class="sic-form-row">
                    <div class="sic-form-group" style="flex:2">
                        <label>Nome Corso (locale)</label>
                        <input type="text" name="c_nome" placeholder="es. PREPOSTO" required>
                    </div>
                    <div class="sic-form-group">
                        <label>ID Piattaforma</label>
                        <input type="text" name="c_idpf" placeholder="es. C004">
                    </div>
                    <div class="sic-form-group">
                        <label>ID Aggiornamento</label>
                        <input type="text" name="c_idagg" placeholder="es. A008">
                    </div>
                    <div class="sic-form-group" style="min-width:80px">
                        <label>Validità (anni)</label>
                        <input type="number" name="c_anni" value="5" min="1" max="20">
                    </div>
                    <div class="sic-form-group" style="justify-content:flex-end">
                        <label>&nbsp;</label>
                        <button type="submit" class="sic-btn sic-btn-primary">+ Aggiungi</button>
                    </div>
                </div>
            </form>
        </div>
        <div id="sic-corsi-table-wrap"><div class="sic-loading"><div class="sic-spinner"></div> Caricamento...</div></div>
    </div>

    <!-- Pacchetto regole default -->
    <div class="sic-admin-block" style="margin-top:20px">
        <h3>📋 Pacchetto Regole Default</h3>
        <p style="font-size:12px;color:#64748b;margin-bottom:12px">
            Definisce quali corsi sono obbligatori per ogni inquadramento in TUTTI i negozi.
            Modifica il default qui — le eccezioni per singolo negozio si gestiscono nell'anagrafica atelier.
        </p>
        <div class="sic-form-card" style="margin-bottom:12px">
            <form onsubmit="sicAddRegola(event)">
                <div class="sic-form-row">
                    <div class="sic-form-group">
                        <label>Inquadramento</label>
                        <input type="text" name="r_inq" placeholder="es. STORE MANAGER" required>
                    </div>
                    <div class="sic-form-group" style="flex:2">
                        <label>Corso (nome_ruolo)</label>
                        <input type="text" name="r_ruolo" placeholder="es. PREPOSTO" required>
                    </div>
                    <div class="sic-form-group" style="min-width:110px">
                        <label>Fabbisogno</label>
                        <input type="text" name="r_fab" value="TUTTI" placeholder="TUTTI o 1,2...">
                    </div>
                    <div class="sic-form-group" style="justify-content:flex-end">
                        <label>&nbsp;</label>
                        <button type="submit" class="sic-btn sic-btn-primary">+ Aggiungi</button>
                    </div>
                </div>
            </form>
        </div>
        <div id="sic-regole-table-wrap"><div class="sic-loading"><div class="sic-spinner"></div> Caricamento...</div></div>
    </div>

</div><!-- fine admin -->


<!-- ============================================================
     MODALE DETTAGLIO NEGOZIO
     ============================================================ -->
<div id="sic-modal-overlay" class="sic-modal-overlay" onclick="if(event.target===this)sicCloseModal()">
    <div class="sic-modal">
        <div class="sic-modal-header">
            <div>
                <h2 id="sic-modal-title">Negozio</h2>
                <p class="sic-modal-sub" id="sic-modal-subtitle"></p>
            </div>
            <button class="sic-modal-close" onclick="sicCloseModal()">✕</button>
        </div>
        <div class="sic-modal-body" id="sic-modal-body"></div>
    </div>
</div>

<div id="sic-toast-container"></div>
</div><!-- fine #sic-app -->

<script>
    const SIC_API_BASE = '<?= $sicUrlBase ?>/api/';
</script>
<script src="<?= $sicUrlBase ?>/assets/app.js"></script>
