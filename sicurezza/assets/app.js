/**
 * =============================================================
 * MODULO SICUREZZA V2 — Frontend (app.js)
 * =============================================================
 * Tutte le funzioni usano il prefisso "sic" per non collidere
 * con il JavaScript già presente nel gestionale.
 *
 * Novità V2 rispetto a V1:
 *   - Sincronizzazione spostata nel tab Admin (non più in topbar)
 *   - Aggiunto tab Admin: gestione corsi, regole, config, polling
 *   - Colonna email nel tab Attestati (per-riga + bulk)
 *   - Colonna email nel tab Negozi (per DVR company)
 *   - Nessun form "aggiungi negozio" nella sezione Negozi
 * =============================================================
 */

'use strict';

// =============================================================
// NAVIGAZIONE
// =============================================================
function sicShowSection(sectionId) {
    document.querySelectorAll('#sic-app .sic-section').forEach(s => s.classList.remove('active'));
    document.getElementById('sic-section-' + sectionId)?.classList.add('active');
    document.querySelectorAll('#sic-app .sic-tab').forEach(t => t.classList.remove('active'));
    document.querySelector(`#sic-app .sic-tab[data-section="${sectionId}"]`)?.classList.add('active');

    const loaders = {
        dashboard: sicLoadDashboard,
        attestati: sicLoadAttestati,
        negozi:    sicLoadNegozi,
        report:    sicLoadReport,
        admin:     sicLoadAdmin,
    };
    loaders[sectionId]?.();
}

// =============================================================
// UTILITY
// =============================================================
function sicToast(msg, type = 'success', ms = 4000) {
    const c = document.getElementById('sic-toast-container');
    if (!c) return;
    const t = document.createElement('div');
    t.className = `sic-toast ${type}`;
    t.textContent = msg;
    c.appendChild(t);
    setTimeout(() => t.remove(), ms);
}

async function sicApiFetch(url, opts = {}) {
    const res  = await fetch(url, { headers: { 'Content-Type': 'application/json', ...opts.headers }, ...opts });
    const data = await res.json().catch(() => ({}));
    if (!res.ok) throw new Error(data.message || data.error || `HTTP ${res.status}`);
    return data;
}

function sicLoading(el) {
    if (el) el.innerHTML = '<div class="sic-loading"><div class="sic-spinner"></div> Caricamento...</div>';
}
function sicDvrClass(s) {
    if (s === 'OK') return 'ok';
    if (s === 'DA AGGIORNARE') return 'da_aggiornare';
    return 'da_emettere';
}

/**
 * Esporta un array di oggetti come file CSV scaricabile.
 * @param {Array}  rows     Array di oggetti da esportare
 * @param {Array}  cols     Colonne da includere: [{key, label}]
 * @param {string} filename Nome del file .csv
 */
function sicExportCSV(rows, cols, filename) {
    const esc = (v) => '"' + String(v ?? '').replace(/"/g, '""') + '"';
    const header = cols.map(c => esc(c.label)).join(',');
    const body   = rows.map(r => cols.map(c => esc(r[c.key])).join(',')).join('\n');
    const blob   = new Blob(["\uFEFF" + header + '\n' + body], { type: 'text/csv;charset=utf-8;' });
    const url    = URL.createObjectURL(blob);
    const a      = document.createElement('a');
    a.href = url; a.download = filename; a.style.display = 'none';
    document.body.appendChild(a); a.click();
    setTimeout(() => { URL.revokeObjectURL(url); a.remove(); }, 500);
}


// =============================================================
// DASHBOARD
// =============================================================
let sicAllStores = [];

async function sicLoadDashboard() {
    const grid = document.getElementById('sic-negozi-grid');
    const statsEl = document.getElementById('sic-dashboard-stats');
    sicLoading(grid);
    try {
        sicAllStores = await sicApiFetch(SIC_API_BASE + 'dashboard.php');
        sicRenderStats(statsEl, sicAllStores);
        sicRenderStoreCards(grid, sicAllStores);
    } catch (e) {
        grid.innerHTML = `<div class="sic-empty" style="grid-column:1/-1"><div class="icon">⚠️</div><h3>${e.message}</h3></div>`;
    }
}

function sicRenderStats(el, stores) {
    if (!el) return;
    const n = (v) => stores.filter(s => s.globalStatus === v).length;
    el.innerHTML = `
        <div class="sic-stat-card"><span class="sic-stat-value">${stores.length}</span><span class="sic-stat-label">Negozi</span></div>
        <div class="sic-stat-card green"><span class="sic-stat-value">${n('coperto')}</span><span class="sic-stat-label">🟢 Coperti</span></div>
        <div class="sic-stat-card yellow"><span class="sic-stat-value">${n('parziale')}</span><span class="sic-stat-label">🟡 Parziali</span></div>
        <div class="sic-stat-card red"><span class="sic-stat-value">${n('critico')}</span><span class="sic-stat-label">🔴 Critici</span></div>`;
}

function sicRenderStoreCards(grid, stores) {
    if (!stores.length) {
        grid.innerHTML = '<div class="sic-empty" style="grid-column:1/-1"><div class="icon">🏪</div><h3>Nessun negozio</h3></div>';
        return;
    }
    document.getElementById('sic-store-count').textContent = `${stores.length} negozi`;
    grid.innerHTML = stores.map(store => {
        const bc  = store.globalStatus;
        const lbl = { coperto:'✅ Coperto', parziale:'⚠️ Parziale', critico:'🚨 Critico' }[bc];
        const dvrCls = sicDvrClass(store.statoDvr);
        const json = JSON.stringify(store).replace(/"/g,'&quot;');
        return `<div class="sic-store-card ${bc}" onclick="sicOpenModal(JSON.parse(this.dataset.store))" data-store="${json}">
            <div class="sic-card-header">
                <div><div class="sic-store-name">${store.nome}</div><div class="sic-company-name">${store.ragioneSociale||''}</div></div>
                <span class="sic-status-badge ${bc}"><span class="sic-status-dot"></span> ${lbl}</span>
            </div>
            <div class="sic-card-stats">
                <div class="sic-card-stat ${store.nonCoperti>0?'bad':'ok'}"><span class="val">${store.nonCoperti}</span><span class="lbl">Non coperti</span></div>
                <div class="sic-card-stat ${store.scaduti>0?'warn':'ok'}"><span class="val">${store.scaduti}</span><span class="lbl">Scaduti</span></div>
                <div class="sic-card-stat"><span class="val" style="font-size:13px">${store.dataDvr||'—'}</span><span class="lbl">Data DVR</span></div>
            </div>
            <div class="sic-dvr-badge ${dvrCls}">📄 DVR: ${store.statoDvr||'DA EMETTERE'}</div>
            <div class="sic-card-footer">👁 Apri dettaglio →</div>
        </div>`;
    }).join('');
}

function sicFilterStores() {
    const q  = document.getElementById('sic-search-negozio').value.toLowerCase().trim();
    const st = document.getElementById('sic-filter-stato').value;
    const dv = document.getElementById('sic-filter-dvr').value;
    sicRenderStoreCards(document.getElementById('sic-negozi-grid'),
        sicAllStores.filter(s =>
            (!q  || s.nome.toLowerCase().includes(q) || s.ragioneSociale.toLowerCase().includes(q)) &&
            (!st || s.globalStatus === st) &&
            (!dv || s.statoDvr === dv)
        ));
}


// =============================================================
// MODALE DETTAGLIO NEGOZIO
// =============================================================
function sicOpenModal(store) {
    document.getElementById('sic-modal-title').textContent = store.nome;
    document.getElementById('sic-modal-subtitle').textContent =
        `${store.ragioneSociale} — DVR: ${store.statoDvr} (${store.dataDvr||'non inserita'})`;
    const rows = (store.dettaglio||[]).map(r => {
        const rc = r.esito==='COPERTO'?'row-ok':r.dettaglio==='ATTESTATO SCADUTO'?'row-scaduto':'row-assente';
        const bc = r.esito==='COPERTO'?'ok':r.dettaglio==='ATTESTATO SCADUTO'?'scaduto':'assente';
        const bl = r.esito==='COPERTO'?'✅ Coperto':r.dettaglio==='ATTESTATO SCADUTO'?'⚠️ Scaduto':'❌ Assente';
        return `<tr class="${rc}"><td>${r.nome}</td><td>${r.inquadramento}</td><td>${r.ruolo}</td><td>${r.dataScadenza||'—'}</td><td><span class="sic-badge ${bc}">${bl}</span></td></tr>`;
    }).join('');
    document.getElementById('sic-modal-body').innerHTML = `<div class="sic-table-wrap"><table>
        <thead><tr><th>Dipendente</th><th>Inquadramento</th><th>Corso</th><th>Scadenza</th><th>Esito</th></tr></thead>
        <tbody>${rows||'<tr><td colspan="5" style="text-align:center;padding:20px;color:#94a3b8">Nessun dato</td></tr>'}</tbody></table></div>`;
    document.getElementById('sic-modal-overlay').classList.add('open');
}
function sicCloseModal() { document.getElementById('sic-modal-overlay').classList.remove('open'); }


// =============================================================
// ATTESTATI — Con email per riga e bulk
// =============================================================
let sicAllAttestati = [];
let sicDipendentiCache = []; // cache per auto-fill sede

async function sicLoadAttestati() {
    sicLoading(document.getElementById('sic-attestati-table-wrap'));
    try {
        sicAllAttestati = await sicApiFetch(SIC_API_BASE + 'attestati.php');
        sicRenderAttestati(sicAllAttestati);
    } catch(e) {
        document.getElementById('sic-attestati-table-wrap').innerHTML =
            `<div class="sic-empty"><div class="icon">⚠️</div><h3>${e.message}</h3></div>`;
    }
    // Popola le select del form "Aggiungi Attestato"
    sicLoadFormAttestatoOptions();
}

function sicRenderAttestati(attestati) {
    const wrap = document.getElementById('sic-attestati-table-wrap');
    if (!attestati.length) {
        wrap.innerHTML = '<div class="sic-empty"><div class="icon">📜</div><h3>Nessun attestato</h3></div>';
        return;
    }
    const stMap = { OK:'ok', SCADUTO:'scaduto', IN_SCADENZA:'scaduto', DURATA_NON_CONFIGURATA:'assente' };
    const stLbl = { OK:'✅ Valido', SCADUTO:'⚠️ Scaduto', IN_SCADENZA:'⏰ In scadenza', DURATA_NON_CONFIGURATA:'❓ N/D' };

    // Valori unici per i filtri colonna
    const sedi   = [...new Set(attestati.map(a => a.sede_assunzione))].sort();
    const corsi  = [...new Set(attestati.map(a => a.nome_attestato))].sort();
    const stati  = [...new Set(attestati.map(a => a.stato))].sort();

    const opts = (arr, def) => `<option value="">${def}</option>` + arr.map(v => `<option value="${v}">${v}</option>`).join('');

    // Riga filtri colonna sotto l'header
    const filterRow = `<tr class="sic-col-filter-row">
        <th><input type="text"  id="sic-f-att-nome"  placeholder="🔍" oninput="sicFilterAttestatiCols()" style="width:100%;box-sizing:border-box;padding:3px 5px;border:1px solid #e2e8f0;border-radius:4px;font-size:11px"></th>
        <th><select id="sic-f-att-sede" onchange="sicFilterAttestatiCols()" style="width:100%;font-size:11px">${opts(sedi,'Tutte le sedi')}</select></th>
        <th><select id="sic-f-att-corso" onchange="sicFilterAttestatiCols()" style="width:100%;font-size:11px">${opts(corsi,'Tutti i corsi')}</select></th>
        <th><input type="text"  id="sic-f-att-emis"  placeholder="🔍" oninput="sicFilterAttestatiCols()" style="width:100%;box-sizing:border-box;padding:3px 5px;border:1px solid #e2e8f0;border-radius:4px;font-size:11px"></th>
        <th><input type="text"  id="sic-f-att-scad"  placeholder="🔍" oninput="sicFilterAttestatiCols()" style="width:100%;box-sizing:border-box;padding:3px 5px;border:1px solid #e2e8f0;border-radius:4px;font-size:11px"></th>
        <th><select id="sic-f-att-stato" onchange="sicFilterAttestatiCols()" style="width:100%;font-size:11px">${opts(stati,'Tutti gli stati')}</select></th>
        <th></th><th></th>
    </tr>`;

    const rows = attestati.map(a => {
        // Il pulsante email è SEMPRE presente:
        //   - scaduto/assente → arancio, attivo
        //   - valido          → grigio chiaro, sempre cliccabile (es. per inviare reminder preventivo)
        const isScaduto = a.stato !== 'OK';
        const emailStyle = isScaduto ? 'sic-btn-warn' : 'sic-btn-outline';
        const emailTitle = isScaduto
            ? `Invia notifica corso scaduto/mancante a ${a.cognome_nome}`
            : `Invia reminder preventivo a ${a.cognome_nome}`;
        const emailBtn = `<button class="sic-btn ${emailStyle} sic-btn-sm"
            onclick="sicSendCorseMail(${a.id})" title="${emailTitle}">📧</button>`;

        return `<tr
            data-nome="${a.cognome_nome.toLowerCase()}"
            data-sede="${a.sede_assunzione}"
            data-corso="${a.nome_attestato}"
            data-emis="${a.emissione_fmt??''}"
            data-scad="${a.scadenza_fmt??''}"
            data-stato="${a.stato}">
            <td>${a.cognome_nome}</td><td>${a.sede_assunzione}</td><td>${a.nome_attestato}</td>
            <td>${a.emissione_fmt??'—'}</td><td>${a.scadenza_fmt??'—'}</td>
            <td><span class="sic-badge ${stMap[a.stato]??'assente'}">${stLbl[a.stato]??a.stato}</span></td>
            <td style="text-align:center">${emailBtn}</td>
            <td><button class="sic-btn sic-btn-danger sic-btn-sm"
                onclick="sicDeleteAttestato(${a.id},'${a.cognome_nome.replace(/'/g,"\\'")}','${a.nome_attestato.replace(/'/g,"\\'")}')">🗑</button></td>
        </tr>`;
    }).join('');

    wrap.innerHTML = `
        <div style="display:flex;justify-content:flex-end;margin-bottom:8px">
            <button class="sic-btn sic-btn-outline" onclick="sicExportAttestatiCSV()" style="font-size:12px">⬇ Esporta Excel / CSV</button>
        </div>
        <div class="sic-table-wrap"><table id="sic-att-table">
            <thead>
                <tr>
                    <th>Dipendente</th><th>Sede</th><th>Corso</th>
                    <th>Emissione</th><th>Scadenza</th><th>Stato</th>
                    <th>📧 Notifica</th><th></th>
                </tr>
                ${filterRow}
            </thead>
            <tbody>${rows}</tbody>
        </table></div>`;
}

/** Filtro barra di ricerca globale (già esistente) */
function sicFilterAttestati() {
    const q = document.getElementById('sic-search-att').value.toLowerCase().trim();
    sicRenderAttestati(sicAllAttestati.filter(a =>
        !q || a.cognome_nome.toLowerCase().includes(q) ||
        a.sede_assunzione.toLowerCase().includes(q) ||
        a.nome_attestato.toLowerCase().includes(q)
    ));
}

/** Filtro colonna per colonna (filtri nella riga sotto l'header) */
function sicFilterAttestatiCols() {
    const fNome  = document.getElementById('sic-f-att-nome')?.value.toLowerCase()  ?? '';
    const fSede  = document.getElementById('sic-f-att-sede')?.value  ?? '';
    const fCorso = document.getElementById('sic-f-att-corso')?.value ?? '';
    const fEmis  = document.getElementById('sic-f-att-emis')?.value.toLowerCase()  ?? '';
    const fScad  = document.getElementById('sic-f-att-scad')?.value.toLowerCase()  ?? '';
    const fStato = document.getElementById('sic-f-att-stato')?.value ?? '';

    document.querySelectorAll('#sic-att-table tbody tr').forEach(tr => {
        const ok =
            (!fNome  || tr.dataset.nome?.includes(fNome))   &&
            (!fSede  || tr.dataset.sede  === fSede)          &&
            (!fCorso || tr.dataset.corso === fCorso)         &&
            (!fEmis  || tr.dataset.emis?.includes(fEmis))   &&
            (!fScad  || tr.dataset.scad?.includes(fScad))   &&
            (!fStato || tr.dataset.stato === fStato);
        tr.style.display = ok ? '' : 'none';
    });
}

/** Esporta gli attestati visibili come CSV */
function sicExportAttestatiCSV() {
    const visible = sicAllAttestati.filter(a => {
        const fNome  = document.getElementById('sic-f-att-nome')?.value.toLowerCase()  ?? '';
        const fSede  = document.getElementById('sic-f-att-sede')?.value  ?? '';
        const fCorso = document.getElementById('sic-f-att-corso')?.value ?? '';
        const fStato = document.getElementById('sic-f-att-stato')?.value ?? '';
        return (!fNome  || a.cognome_nome.toLowerCase().includes(fNome)) &&
               (!fSede  || a.sede_assunzione === fSede)  &&
               (!fCorso || a.nome_attestato  === fCorso) &&
               (!fStato || a.stato === fStato);
    });
    sicExportCSV(visible, [
        { key: 'cognome_nome',    label: 'Dipendente' },
        { key: 'sede_assunzione', label: 'Sede' },
        { key: 'nome_attestato',  label: 'Corso' },
        { key: 'emissione_fmt',   label: 'Data Emissione' },
        { key: 'scadenza_fmt',    label: 'Data Scadenza' },
        { key: 'stato',           label: 'Stato' },
    ], 'attestati_sicurezza.csv');
}

/**
 * Carica le opzioni nelle 3 select del form "Aggiungi Attestato Manuale"
 * Dati: dipendenti attivi, negozi, catalogo corsi
 */
async function sicLoadFormAttestatoOptions() {
    try {
        const [dipendenti, negozi, corsi] = await Promise.all([
            sicApiFetch(SIC_API_BASE + 'dipendenti.php?attivi=1'),
            sicApiFetch(SIC_API_BASE + 'negozi.php'),
            sicApiFetch(SIC_API_BASE + 'corsi.php'),
        ]);

        sicDipendentiCache = dipendenti;

        // Popola select Dipendente
        const selDip = document.getElementById('sic-sel-dipendente');
        if (selDip) {
            const val = selDip.value; // preserva selezione corrente
            selDip.innerHTML = '<option value="">— Seleziona dipendente —</option>' +
                dipendenti.map(d => `<option value="${d.cognome_nome}" data-sede="${d.sede_assunzione || ''}">${d.cognome_nome}</option>`).join('');
            if (val) selDip.value = val;
        }

        // Popola select Sede
        const selSede = document.getElementById('sic-sel-sede');
        if (selSede) {
            const val = selSede.value;
            const sedi = [...new Set(negozi.map(n => n.nome_negozio))].sort();
            selSede.innerHTML = '<option value="">— Seleziona sede —</option>' +
                sedi.map(s => `<option value="${s}">${s}</option>`).join('');
            if (val) selSede.value = val;
        }

        // Popola select Corso
        const selCorso = document.getElementById('sic-sel-corso');
        if (selCorso) {
            const val = selCorso.value;
            selCorso.innerHTML = '<option value="">— Seleziona corso —</option>' +
                corsi.map(c => `<option value="${c.nome_locale}">${c.nome_locale}</option>`).join('');
            if (val) selCorso.value = val;
        }
    } catch(e) {
        console.warn('Errore caricamento opzioni form attestato:', e);
    }
}

/** Quando si seleziona un dipendente, auto-compila la sede */
function sicAutoFillSede(selectEl) {
    const nome = selectEl.value;
    if (!nome) return;
    const dip = sicDipendentiCache.find(d => d.cognome_nome === nome);
    if (dip && dip.sede_assunzione) {
        const selSede = document.getElementById('sic-sel-sede');
        if (selSede) selSede.value = dip.sede_assunzione;
    }
}

async function sicAddAttestato(event) {
    event.preventDefault();
    const form = event.target;
    const btn  = form.querySelector('[type=submit]');
    btn.disabled = true;
    try {
        await sicApiFetch(SIC_API_BASE + 'attestati.php', {
            method: 'POST',
            body: JSON.stringify({
                cognome_nome:    form.att_nome.value,
                sede_assunzione: form.att_sede.value,
                nome_attestato:  form.att_corso.value,
                data_emissione:  form.att_data.value,
                note: '',
            }),
        });
        sicToast('✅ Attestato aggiunto');
        form.reset();
        await sicLoadAttestati();
    } catch(e) { sicToast('❌ ' + e.message, 'error'); }
    finally    { btn.disabled = false; }
}

async function sicDeleteAttestato(id, nome, corso) {
    if (!confirm(`Eliminare l'attestato "${corso}" di ${nome}?`)) return;
    try {
        await sicApiFetch(SIC_API_BASE + `attestati.php?id=${id}`, { method: 'DELETE' });
        sicToast('✅ Attestato eliminato');
        await sicLoadAttestati();
    } catch(e) { sicToast('❌ ' + e.message, 'error'); }
}

/** Invia email al dipendente per un singolo attestato scaduto */
async function sicSendCorseMail(attestatoId) {
    if (!confirm('Inviare notifica email al dipendente per questo corso?')) return;
    try {
        const r = await sicApiFetch(SIC_API_BASE + 'email.php?tipo=corso', {
            method: 'POST',
            body: JSON.stringify({ attestato_id: attestatoId }),
        });
        sicToast(r.message || '✅ Email inviata', 'success');
    } catch(e) { sicToast('❌ ' + e.message, 'error'); }
}

/** Invia email a TUTTI i dipendenti con attestati scaduti/mancanti */
async function sicSendAllCorseMail() {
    if (!confirm('Inviare notifica a TUTTI i dipendenti con corsi scaduti o mancanti?')) return;
    try {
        const r = await sicApiFetch(SIC_API_BASE + 'email.php?tipo=corso', {
            method: 'POST',
            body: JSON.stringify({ bulk: true }),
        });
        sicToast(`📧 ${r.inviate} email inviate` + (r.errori?.length ? ` — ${r.errori.length} errori` : ''), 'success', 6000);
    } catch(e) { sicToast('❌ ' + e.message, 'error'); }
}


// =============================================================
// NEGOZI & DVR — Con email per DVR company
// =============================================================
async function sicLoadNegozi() {
    sicLoading(document.getElementById('sic-negozi-table-wrap'));
    try {
        const negozi = await sicApiFetch(SIC_API_BASE + 'negozi.php');
        sicRenderNegozi(negozi);
    } catch(e) {
        document.getElementById('sic-negozi-table-wrap').innerHTML =
            `<div class="sic-empty"><div class="icon">⚠️</div><h3>${e.message}</h3></div>`;
    }
}

// Salva l'ultimo array negozi per l'esportazione
let sicNegozioData = [];

function sicRenderNegozi(negozi) {
    sicNegozioData = negozi;
    const wrap = document.getElementById('sic-negozi-table-wrap');
    if (!negozi.length) {
        wrap.innerHTML = '<div class="sic-empty"><div class="icon">🏪</div><h3>Nessun negozio sincronizzato</h3></div>';
        return;
    }

    // Valori unici per i filtri colonna
    const rsList  = [...new Set(negozi.map(n => n.ragione_sociale).filter(Boolean))].sort();
    const citList = [...new Set(negozi.map(n => n.localita).filter(Boolean))].sort();
    const stList  = [...new Set(negozi.map(n => n.stato_dvr_calcolato ?? n.stato_dvr).filter(Boolean))];
    const opts = (arr, def) => `<option value="">${def}</option>` + arr.map(v => `<option value="${v}">${v}</option>`).join('');

    const filterRow = `<tr class="sic-col-filter-row">
        <th><input type="text" id="sic-f-dvr-nome" placeholder="🔍" oninput="sicFilterNegozi()" style="width:100%;box-sizing:border-box;padding:3px 5px;border:1px solid #e2e8f0;border-radius:4px;font-size:11px"></th>
        <th><select id="sic-f-dvr-rs" onchange="sicFilterNegozi()" style="width:100%;font-size:11px">${opts(rsList,'Tutte')}</select></th>
        <th><select id="sic-f-dvr-cit" onchange="sicFilterNegozi()" style="width:100%;font-size:11px">${opts(citList,'Tutte le città')}</select></th>
        <th></th><th></th>
        <th><select id="sic-f-dvr-stato" onchange="sicFilterNegozi()" style="width:100%;font-size:11px">${opts(stList,'Tutti gli stati')}</select></th>
        <th></th>
    </tr>`;

    const rows = negozi.map(n => {
        const statoFin = n.stato_dvr_calcolato ?? n.stato_dvr;
        const emailDvr = n.email_dvr || '';
        return `<tr
            data-nome="${n.nome_negozio.toLowerCase()}"
            data-rs="${n.ragione_sociale??''}"
            data-cit="${n.localita??''}"
            data-stato="${statoFin}">
            <td><strong>${n.nome_negozio}</strong></td>
            <td>${n.ragione_sociale??'—'}</td>
            <td>${n.localita??'—'}</td>
            <td><input type="date" value="${n.data_dvr??''}" data-id="${n.id}"
                style="border:1px solid #e2e8f0;border-radius:5px;padding:4px 8px;font-size:12px;font-family:inherit"
                onchange="sicUpdateDvr(this)"></td>
            <td>${n.data_var_fmt??'—'}</td>
            <td><span class="sic-badge ${sicDvrClass(statoFin)}">${statoFin}</span></td>
            <td>
                <button class="sic-btn sic-btn-warn sic-btn-sm"
                    onclick="sicSendDvrMail(${n.id},'${n.nome_negozio.replace(/'/g,"\\'")}')"
                    title="Invia email con foto DVR aggiornata a ${emailDvr||'consulente DVR'}">📧 Invia DVR</button>
            </td>
        </tr>`;
    }).join('');

    wrap.innerHTML = `
        <div style="display:flex;justify-content:flex-end;margin-bottom:8px">
            <button class="sic-btn sic-btn-outline" onclick="sicExportNegoziCSV()" style="font-size:12px">⬇ Esporta Excel / CSV</button>
        </div>
        <div class="sic-table-wrap"><table id="sic-dvr-table">
            <thead>
                <tr><th>Negozio</th><th>Ragione Sociale</th><th>Città</th><th>Data DVR</th><th>Ultima variazione</th><th>Stato DVR</th><th>📧 DVR</th></tr>
                ${filterRow}
            </thead>
            <tbody>${rows}</tbody>
        </table></div>`;
}

/** Filtro colonna per negozi/DVR */
function sicFilterNegozi() {
    const fNome  = document.getElementById('sic-f-dvr-nome')?.value.toLowerCase()  ?? '';
    const fRs    = document.getElementById('sic-f-dvr-rs')?.value   ?? '';
    const fCit   = document.getElementById('sic-f-dvr-cit')?.value  ?? '';
    const fStato = document.getElementById('sic-f-dvr-stato')?.value ?? '';
    document.querySelectorAll('#sic-dvr-table tbody tr').forEach(tr => {
        const ok =
            (!fNome  || tr.dataset.nome?.includes(fNome)) &&
            (!fRs    || tr.dataset.rs   === fRs)          &&
            (!fCit   || tr.dataset.cit  === fCit)         &&
            (!fStato || tr.dataset.stato === fStato);
        tr.style.display = ok ? '' : 'none';
    });
}

/** Esporta negozi come CSV (righe visibili) */
function sicExportNegoziCSV() {
    const fStato = document.getElementById('sic-f-dvr-stato')?.value ?? '';
    const visible = sicNegozioData.filter(n => !fStato || (n.stato_dvr_calcolato ?? n.stato_dvr) === fStato);
    sicExportCSV(visible, [
        { key: 'nome_negozio',     label: 'Negozio' },
        { key: 'ragione_sociale',  label: 'Ragione Sociale' },
        { key: 'localita',         label: 'Città' },
        { key: 'data_dvr',         label: 'Data DVR' },
        { key: 'data_var_fmt',     label: 'Ultima Variazione' },
        { key: 'stato_dvr',        label: 'Stato DVR' },
        { key: 'email_dvr',        label: 'Email DVR' },
    ], 'negozi_dvr.csv');
}

async function sicUpdateDvr(input) {
    try {
        await sicApiFetch(SIC_API_BASE + `negozi.php?id=${input.dataset.id}`, {
            method: 'PUT',
            body: JSON.stringify({ data_dvr: input.value || null }),
        });
        sicToast('✅ Data DVR aggiornata');
        await sicLoadNegozi();
    } catch(e) { sicToast('❌ ' + e.message, 'error'); }
}

/** Invia email alla società DVR per il negozio selezionato */
async function sicSendDvrMail(negozioId, nome) {
    if (!confirm(`Inviare email con la foto DVR aggiornata per "${nome}"?`)) return;
    try {
        const r = await sicApiFetch(SIC_API_BASE + 'email.php?tipo=dvr', {
            method: 'POST',
            body: JSON.stringify({ negozio_id: negozioId }),
        });
        sicToast(r.message || '✅ Email DVR inviata', r.success ? 'success' : 'error');
    } catch(e) { sicToast('❌ ' + e.message, 'error'); }
}


// =============================================================
// REPORT CORSI
// =============================================================
async function sicLoadReport() {
    sicLoading(document.getElementById('sic-report-wrap'));
    try {
        const data = await sicApiFetch(SIC_API_BASE + 'report.php');
        const wrap = document.getElementById('sic-report-wrap');
        if (!data.length) {
            wrap.innerHTML = '<div class="sic-empty"><div class="icon">🎉</div><h3>Tutto in regola!</h3></div>';
            return;
        }
        const bm = { CORSO:'corso', AGGIORNAMENTO:'aggiornamento', DVR:'dvr' };
        wrap.innerHTML = `<div class="sic-table-wrap"><table>
            <thead><tr><th>Ragione Sociale</th><th>Tipo</th><th>Corso / Intervento</th><th>Quantità</th></tr></thead>
            <tbody>${data.map(r=>`<tr>
                <td>${r.ragioneSociale}</td>
                <td><span class="sic-badge ${bm[r.tipoRichiesta]??'assente'}">${r.tipoRichiesta}</span></td>
                <td>${r.nomeCorso}</td><td><strong>${r.quantita}</strong></td>
            </tr>`).join('')}</tbody></table></div>`;
    } catch(e) {
        document.getElementById('sic-report-wrap').innerHTML =
            `<div class="sic-empty"><div class="icon">⚠️</div><h3>${e.message}</h3></div>`;
    }
}


// =============================================================
// ADMIN — Sync, Polling, Catalogo Corsi, Regole Default, Config
// =============================================================
async function sicLoadAdmin() {
    sicLoadCorsiAdmin();
    sicLoadRegoleAdmin();
    sicLoadConfigAdmin();
}

/** Sincronizzazione manuale dipendenti (solo dal tab Admin) */
async function sicSyncDipendenti() {
    const btn = document.querySelector('#sic-section-admin .sic-btn-primary');
    const res = document.getElementById('sic-sync-result');
    if (btn) { btn.disabled = true; btn.innerHTML = '<span class="spin">⟳</span> In corso...'; }
    try {
        const r = await sicApiFetch(SIC_API_BASE + 'sync.php', { method: 'POST' });
        const msg = `✅ ${r.count} dipendenti — ${r.changesDetected} negozi variati — ${r.esclusi} esclusi per contratto`;
        if (res) res.textContent = msg;
        sicToast(msg, 'success', 6000);
    } catch(e) {
        if (res) res.textContent = '❌ ' + e.message;
        sicToast('❌ ' + e.message, 'error');
    } finally {
        if (btn) { btn.disabled = false; btn.innerHTML = '⟳ Sincronizza Ora'; }
    }
}

/** Polling manuale della piattaforma corsi */
async function sicPollPiattaforma() {
    const btn = document.querySelector('#sic-section-admin .sic-btn:not(.sic-btn-primary)');
    if (btn) { btn.disabled = true; btn.textContent = 'Polling...'; }
    try {
        const r = await sicApiFetch(SIC_API_BASE + 'piattaforma.php?azione=polling', { method: 'POST' });
        sicToast(r.message || '✅ Polling completato', 'success', 5000);
    } catch(e) { sicToast('❌ ' + e.message, 'error'); }
    finally    { if (btn) { btn.disabled = false; btn.textContent = '📡 Polling Piattaforma'; } }
}

// --- CATALOGO CORSI ---
async function sicLoadCorsiAdmin() {
    sicLoading(document.getElementById('sic-corsi-table-wrap'));
    try {
        const corsi = await sicApiFetch(SIC_API_BASE + 'corsi.php');
        const rows = corsi.map(c => `<tr>
            <td>${c.nome_locale}</td>
            <td><input type="text" value="${c.id_piattaforma??''}" data-id="${c.id}" data-campo="id_piattaforma"
                class="sic-inline-edit" onchange="sicUpdateCorso(this)" style="width:90px;padding:3px 6px;border:1px solid #e2e8f0;border-radius:4px;font-size:12px"></td>
            <td><input type="text" value="${c.id_aggiornamento??''}" data-id="${c.id}" data-campo="id_aggiornamento"
                class="sic-inline-edit" onchange="sicUpdateCorso(this)" style="width:90px;padding:3px 6px;border:1px solid #e2e8f0;border-radius:4px;font-size:12px"></td>
            <td>${c.anni_validita} anni</td>
            <td><button class="sic-btn sic-btn-danger sic-btn-sm" onclick="sicDeleteCorso(${c.id},'${c.nome_locale.replace(/'/g,"\\'")}')">🗑</button></td>
        </tr>`).join('');
        document.getElementById('sic-corsi-table-wrap').innerHTML = `<div class="sic-table-wrap"><table>
            <thead><tr><th>Corso</th><th>ID Piattaforma</th><th>ID Aggiornamento</th><th>Validità</th><th></th></tr></thead>
            <tbody>${rows||'<tr><td colspan="5" style="text-align:center;padding:16px;color:#94a3b8">Nessun corso</td></tr>'}</tbody></table></div>`;
    } catch(e) { document.getElementById('sic-corsi-table-wrap').innerHTML = `<div class="sic-empty"><h3>${e.message}</h3></div>`; }
}

async function sicUpdateCorso(input) {
    try {
        await sicApiFetch(SIC_API_BASE + `corsi.php?id=${input.dataset.id}`, {
            method: 'PUT',
            body: JSON.stringify({ [input.dataset.campo]: input.value || null }),
        });
        sicToast('✅ Salvato');
    } catch(e) { sicToast('❌ ' + e.message, 'error'); }
}

async function sicAddCorso(event) {
    event.preventDefault();
    const f = event.target;
    const btn = f.querySelector('[type=submit]'); btn.disabled = true;
    try {
        await sicApiFetch(SIC_API_BASE + 'corsi.php', {
            method: 'POST',
            body: JSON.stringify({ nome_locale: f.c_nome.value, id_piattaforma: f.c_idpf.value, id_aggiornamento: f.c_idagg.value, anni_validita: f.c_anni.value }),
        });
        sicToast('✅ Corso aggiunto'); f.reset(); sicLoadCorsiAdmin();
    } catch(e) { sicToast('❌ ' + e.message, 'error'); }
    finally    { btn.disabled = false; }
}

async function sicDeleteCorso(id, nome) {
    if (!confirm(`Eliminare il corso "${nome}"?`)) return;
    try {
        await sicApiFetch(SIC_API_BASE + `corsi.php?id=${id}`, { method: 'DELETE' });
        sicToast('✅ Corso eliminato'); sicLoadCorsiAdmin();
    } catch(e) { sicToast('❌ ' + e.message, 'error'); }
}

// --- REGOLE DEFAULT ---
async function sicLoadRegoleAdmin() {
    sicLoading(document.getElementById('sic-regole-table-wrap'));
    try {
        const regole = await sicApiFetch(SIC_API_BASE + 'regole.php?tipo=default');
        const rows = regole.map(r => `<tr>
            <td>${r.inquadramento}</td><td>${r.nome_ruolo}</td><td>${r.fabbisogno}</td>
            <td>${r.id_piattaforma||'—'}</td>
            <td><button class="sic-btn sic-btn-danger sic-btn-sm" onclick="sicDeleteRegola(${r.id},'${r.nome_ruolo.replace(/'/g,"\\'")}')">🗑</button></td>
        </tr>`).join('');
        document.getElementById('sic-regole-table-wrap').innerHTML = `<div class="sic-table-wrap"><table>
            <thead><tr><th>Inquadramento</th><th>Corso Richiesto</th><th>Fabbisogno</th><th>ID Piattaforma</th><th></th></tr></thead>
            <tbody>${rows||'<tr><td colspan="5" style="text-align:center;padding:16px;color:#94a3b8">Nessuna regola</td></tr>'}</tbody></table></div>`;
    } catch(e) { document.getElementById('sic-regole-table-wrap').innerHTML = `<div class="sic-empty"><h3>${e.message}</h3></div>`; }
}

async function sicAddRegola(event) {
    event.preventDefault();
    const f = event.target;
    const btn = f.querySelector('[type=submit]'); btn.disabled = true;
    try {
        await sicApiFetch(SIC_API_BASE + 'regole.php?tipo=default', {
            method: 'POST',
            body: JSON.stringify({ inquadramento: f.r_inq.value, nome_ruolo: f.r_ruolo.value, fabbisogno: f.r_fab.value }),
        });
        sicToast('✅ Regola aggiunta'); f.reset(); sicLoadRegoleAdmin();
    } catch(e) { sicToast('❌ ' + e.message, 'error'); }
    finally    { btn.disabled = false; }
}

async function sicDeleteRegola(id, nome) {
    if (!confirm(`Eliminare la regola "${nome}"?`)) return;
    try {
        await sicApiFetch(SIC_API_BASE + `regole.php?tipo=default&id=${id}`, { method: 'DELETE' });
        sicToast('✅ Regola eliminata'); sicLoadRegoleAdmin();
    } catch(e) { sicToast('❌ ' + e.message, 'error'); }
}

// --- CONFIGURAZIONE GLOBALE ---
async function sicLoadConfigAdmin() {
    try {
        const cfg = await sicApiFetch(SIC_API_BASE + 'regole.php?tipo=config');
        const form = document.getElementById('sic-config-form');
        if (!form) return;
        form.innerHTML = cfg.map(c => `
            <div class="sic-form-row" style="margin-bottom:8px;align-items:center">
                <div class="sic-form-group" style="max-width:200px">
                    <label>${c.chiave}</label>
                    <small style="color:#94a3b8">${c.nota||''}</small>
                </div>
                <div class="sic-form-group" style="max-width:300px">
                    <input type="text" value="${c.valore||''}" data-chiave="${c.chiave}"
                        style="padding:6px 10px;border:1px solid #e2e8f0;border-radius:6px;font-family:inherit;font-size:13px;width:100%"
                        onchange="sicUpdateConfig(this)">
                </div>
            </div>`).join('');
    } catch(e) { /* config non critica */ }
}

async function sicUpdateConfig(input) {
    try {
        await sicApiFetch(SIC_API_BASE + 'regole.php?tipo=config', {
            method: 'PUT',
            body: JSON.stringify({ chiave: input.dataset.chiave, valore: input.value }),
        });
        sicToast('✅ Config aggiornata');
    } catch(e) { sicToast('❌ ' + e.message, 'error'); }
}


// =============================================================
// AVVIO
// =============================================================
document.addEventListener('DOMContentLoaded', () => {
    sicShowSection('dashboard');
});
