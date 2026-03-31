# BRIEFING DEV — Modulo Assunzioni & Firma Digitale

> **Data**: Marzo 2026
> **Progetto**: Automazione flusso assunzioni → Gestionale MrAge
> **Stato**: Schema DB e struttura file consegnati, configurazioni e aggancio OTP da completare

---

## 1. Obiettivo

Sostituire l'attuale processo manuale (Google Form → inserimento a mano nel gestionale) con un flusso digitale completo che:

- Permette ai **District Manager** di inviare richieste di assunzione e proroga da un'interfaccia web dedicata, caricando i documenti di identità direttamente
- Crea automaticamente (o aggiorna) l'**anagrafica dipendente** nel gestionale con tutti i dati e i documenti scansionati
- Notifica il **Consulente del Lavoro** via email ad ogni nuova richiesta: può consultare tutti i dati e caricare il contratto PDF
- Invia al **dipendente** una mail con link per la **firma digitale OTP** (integrazione API esterna — vedi §3.5)
- Traccia lo stato di ogni contratto (**In attesa / Da firmare / Firmato**) visibile a tutti e tre i ruoli
- Invia automaticamente la mail di benvenuto **FavolaWay** ai dipendenti di sedi di tipo `diretto` quando diventano attivi

---

## 2. Logica del Sistema (in linguaggio naturale)

### 2.1 Ruoli e accessi

| Ruolo | Cosa vede/fa |
|---|---|
| **District Manager** | Compila form assunzione + proroga, vede lista dipendenti negozi con scadenze contratti, vede stato firma contratti |
| **Consulente del Lavoro (CdL)** | Riceve notifica mail, vede tutte le richieste + dati + documenti, carica contratto PDF per ogni dipendente |
| **Admin** | Visibilità totale: tutte le richieste, tutte le anagrafiche, tutti gli stati, tutti i DM. Accede ai campi della richiesta direttamente dall'anagrafica dipendente |

### 2.2 Flusso Nuova Assunzione

```
[DM compila form] → salva in ass_richieste → crea/aggiorna sic_dipendenti
    → notifica mail al CdL ("nuova richiesta per X — clicca qui")
[CdL apre dashboard] → vede dati + documenti → carica contratto PDF
    → stato cambia a "da_firmare" → mail al dipendente con link firma
[Dipendente riceve mail] → apre link → firma via OTP (SMS sul telefono)
    → webhook/callback all'API nostra → stato aggiornato a "firmato"
[Se sede = "diretto"] → mail automatica FavolaWay al dipendente (vedi §2.5)
```

### 2.3 Flusso Proroga

Identico al flusso assunzione, con queste differenze:
- Il DM seleziona un dipendente esistente
- I campi anagrafici (nome, cognome, doc identità, CF, mail, telefono) sono **precompilati e bloccati**
- Solo i campi contrattuali sono modificabili: inquadramento, retribuzione, sede, date, ore
- La proroga aggiunge una **nuova riga** in `ass_storico_retribuzione` (non sovrascrive)

### 2.4 Contratti a tempo indeterminato

- Il DM può spuntare il flag **"Indeterminato"**
- Quando attivo: il campo `data_fine` viene disabilitato e salvato come `NULL` in DB
- Nell'interfaccia viene visualizzato come **"INDETERMINATO"**
- Il flag è salvato in `ass_richieste.indeterminato = 1` e in `sic_dipendenti.contratto_indeterminato = 1`

### 2.5 Mail automatica FavolaWay

**Trigger**: dipendente diventa attivo (`attivo = 1`) E la sua sede è di tipo `diretto` (campo `tipo` in `sic_negozi`, già presente dal modulo sicurezza).

**Mittente**: `hr@comeinunafavola.it`
**Oggetto**: `ISTRUZIONI APP AZIENDALE FAVOLA WAY`

Il testo della mail (fisso nel codice, in `includes/email.php`) include:
- Username → **email del dipendente** (inserita automaticamente)
- Password → **cognome tutto minuscolo** (inserita automaticamente)

Il template completo è in `includes/email.php` nella funzione `mailFavolaWay()`.

### 2.6 Documenti identità

Il DM sceglie tra **Carta d'Identità / Passaporto / Patente** e carica:
- Fronte + Retro del documento scelto
- Fronte + Retro del Codice Fiscale

Upload supportato in doppia modalità: immagine (JPG/PNG, convertita in PDF server-side) o PDF diretto.

I file vengono archiviati in `uploads/documenti/{dipendente_id}/` e i path salvati in `sic_dipendenti`.

---

## 3. ⚙️ Cosa devi fare tu (Developer)

### 3.1 Installazione DB

```bash
# Esegui nell'ordine (dopo che schema.sql e schema_v2.sql del modulo sicurezza sono già installati):
mysql -u root -p nome_db < assunzioni/sql/migration_dipendenti.sql
mysql -u root -p nome_db < assunzioni/sql/schema_assunzioni.sql
```

### 3.2 Configura config.php

```php
// DB — stesse credenziali del modulo sicurezza
define('ASS_DB_HOST', 'localhost');
define('ASS_DB_NAME', 'nome_database');
define('ASS_DB_USER', 'utente_db');
define('ASS_DB_PASS', 'password_db');

// URL base del modulo
define('ASS_BASE_URL', 'https://agenda.comeinunafavola.it/assunzioni');

// Email sistema (notifiche CdL e dipendente)
define('ASS_EMAIL_FROM',      'noreply@comeinunafavola.it');
define('ASS_EMAIL_FROM_NAME', 'Gestionale Come in una Favola');

// Email FavolaWay (mittente fisso per la mail app)
define('ASS_EMAIL_FAVOLAWAY_FROM', 'hr@comeinunafavola.it');

// Path upload documenti (assoluto, con slash finale)
define('ASS_UPLOAD_PATH', '/percorso/assoluto/assunzioni/uploads/documenti/');

// Chiave sessione condivisa con il gestionale (per auth)
define('ASS_SESSION_KEY', 'user_id'); // adatta alla tua sessione
```

### 3.3 Aggiungi la voce "Assunzioni" al menu del gestionale

```php
// Nel file del gestionale che gestisce il routing (es. index.php o mrgest.php):
case 'assunzioni':
    require_once __DIR__ . '/assunzioni/index.php';
    break;

// Nel menu HTML (nella sezione Atelier o Dipendenti):
// <a href="?op=assunzioni">📋 Assunzioni</a>
```

> Il modulo ha il proprio layout indipendente oppure può essere incluso nel layout esistente — vedi commento in `index.php`.

### 3.4 Configura l'SMTP per le email

Il modulo usa la funzione `assInviaEmail()` in `includes/email.php`. **Sostituisci il corpo** con l'SMTP già configurato nel gestionale:

```php
function assInviaEmail(string $to, string $subject, string $htmlBody, string $attachPath = ''): bool
{
    // --- ADATTA CON LA TUA CONFIGURAZIONE SMTP ---
    require_once PERCORSO_PHPMAILER;
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host     = SMTP_HOST;
    $mail->Username = SMTP_USER;
    $mail->Password = SMTP_PASS;
    $mail->setFrom(ASS_EMAIL_FROM, ASS_EMAIL_FROM_NAME);
    $mail->addAddress($to);
    $mail->isHTML(true);
    $mail->Subject = $subject;
    $mail->Body    = $htmlBody;
    if ($attachPath) $mail->addAttachment($attachPath);
    return $mail->send();
}
```

> Per la mail FavolaWay il mittente cambia: la funzione `mailFavolaWay()` usa `ASS_EMAIL_FAVOLAWAY_FROM` — assicurati che il tuo SMTP permetta l'invio da quell'indirizzo.

### 3.5 ⚡ Integrazione API Firma OTP — IL TUO LAVORO PRINCIPALE

Questo è il punto che richiede il tuo intervento. Il codice è **predisposto** in `api/firma_otp.php` con due funzioni stub:

```php
/**
 * AVVIA_FIRMA_OTP
 * Chiamata quando il CdL carica un contratto.
 * Deve: inviare il PDF all'API esterna, ottenere un token/URL firma, salvare il token.
 *
 * @param string $contrattoPdfPath  Path assoluto al PDF del contratto
 * @param string $emailDipendente   Email a cui inviare il link firma
 * @param string $telefonoDipendente Numero per l'OTP (formato +39XXXXXXXXXX)
 * @param int    $richiestaId       ID riga in ass_richieste
 * @return array ['ok' => bool, 'token' => string, 'url_firma' => string, 'errore' => string]
 */
function avviaFirmaOTP(string $contrattoPdfPath, string $emailDipendente, string $telefonoDipendente, int $richiestaId): array {
    // TODO: implementa la chiamata all'API OTP esterna
    // Il token restituito va salvato in ass_contratti.otp_token
    return ['ok' => false, 'errore' => 'API OTP non configurata'];
}

/**
 * VERIFICA_STATO_FIRMA
 * Usata per il webhook/callback o per polling manuale.
 * Deve: interrogare l'API OTP con il token, restituire se firmato.
 *
 * @param string $token  Token salvato al momento dell'avvio firma
 * @return array ['firmato' => bool, 'firmato_at' => string|null]
 */
function verificaStatoFirma(string $token): array {
    // TODO: implementa il check stato firma
    return ['firmato' => false, 'firmato_at' => null];
}
```

**Webhook callback** (se l'API OTP supporta notifica asincrona):
L'endpoint `api/firma_otp.php?action=callback` è predisposto per ricevere il webhook. Implementa il parsing della risposta e aggiorna `ass_richieste.stato = 'firmato'` e `ass_contratti.firmato_at`.

### 3.6 Crea gli account utente iniziali

> **Nota**: l'**Admin NON ha un account in `ass_utenti`**. Usa la sessione del gestionale già attiva (stessa variabile `ASS_SESSION_KEY` configurata in `config.php`). Il modulo rileva automaticamente il ruolo admin dalla sessione esistente e mostra la dashboard completa.

Devi creare solo gli account per **CdL e District Manager** (via SQL, password hashata con bcrypt):

```sql
-- Consulente del Lavoro
INSERT INTO ass_utenti (email, password, nome, ruolo) VALUES
('cdl@studioXYZ.it', '$2y$10$HASH_BCRYPT', 'Nome CdL', 'consulente_lavoro');

-- District Manager (uno per ogni DM — ripeti per ognuno)
INSERT INTO ass_utenti (email, password, nome, ruolo) VALUES
('dm.nome@comeinunafavola.it', '$2y$10$HASH_BCRYPT', 'Nome DM', 'district_manager');
```

Per generare l'hash in PHP: `echo password_hash('password_scelta', PASSWORD_BCRYPT);`

> Adatta la query `auth.php` al modo in cui il tuo gestionale identifica l'admin nella sessione (es. `$_SESSION['ruolo'] === 'admin'` o `$_SESSION['is_admin'] === true`).

### 3.7 Proteggi la cartella uploads/

La cartella `uploads/documenti/` contiene documenti sensibili. Aggiungi:

```apache
# uploads/.htaccess
Options -Indexes
<FilesMatch "\.php$">
    Deny from all
</FilesMatch>
```

E assicurati che i file siano serviti **solo via PHP autenticato** (la funzione `serveDocumento()` in `includes/helpers.php` serve i file con controllo sessione — usala sempre, non linkare direttamente i path).

---

## 4. Struttura File del Modulo

```
assunzioni/
├── config.php                        ← UNICO file da configurare
├── index.php                         ← Router principale + check login
├── login.php                         ← Pagina login
├── dashboard_dm.php                  ← Interfaccia District Manager
├── dashboard_cdl.php                 ← Interfaccia Consulente del Lavoro
├── dashboard_admin.php               ← Interfaccia Admin (visibilità totale)
│
├── api/
│   ├── assunzione.php                ← POST nuova richiesta assunzione
│   ├── proroga.php                   ← POST richiesta proroga
│   ├── upload_documento.php          ← POST upload fronte/retro documenti
│   ├── upload_contratto.php          ← POST caricamento contratto PDF (CdL)
│   ├── stato_contratto.php           ← GET lista richieste con stato
│   └── firma_otp.php                 ← ⚡ STUB OTP — da implementare
│
├── includes/
│   ├── db.php                        ← Connessione PDO (riusa credenziali config)
│   ├── auth.php                      ← Controllo sessione e ruolo
│   ├── email.php                     ← Tutte le mail: CdL, dipendente, FavolaWay
│   └── helpers.php                   ← PDF generation, serve file, date, util
│
├── uploads/
│   ├── .htaccess                     ← Blocca accesso diretto (vedi §3.7)
│   └── documenti/                    ← Archiviazione PDF per dipendente_id/
│
└── sql/
    ├── migration_dipendenti.sql      ← ALTER TABLE sic_dipendenti (nuovi campi)
    └── schema_assunzioni.sql         ← Nuove tabelle ass_* (richieste, contratti, storico, utenti)
```

---

## 5. Tabelle DB — Panoramica

| Tabella | Tipo | Descrizione |
|---|---|---|
| `sic_dipendenti` | **Modificata** | Aggiunti: livello_istruzione, inquadramento_contratto, giorni_prova, note_assunzione, doc_tipo, doc_identita_fronte/retro, doc_cf_fronte/retro, telefono, contratto_indeterminato |
| `sic_negozi` | Esistente | Usata in sola lettura per il dropdown sedi e il filtro tipo `diretto` |
| `ass_richieste` | **Nuova** | Ogni richiesta DM (assunzione o proroga) con tutti i dati e lo stato contratto |
| `ass_contratti` | **Nuova** | PDF contratto caricato dal CdL + token OTP + data firma |
| `ass_storico_retribuzione` | **Nuova** | Storico multi-riga retribuzione per dipendente (riga 1 = assunzione, righe 2+ = proroghe) |
| `ass_utenti` | **Nuova** | Credenziali e ruoli per **DM e CdL** — l'Admin usa la sessione del gestionale esistente |

Vedi la struttura completa con tutti i campi in `sql/schema_assunzioni.sql`.

---

## 6. URL di Accesso

```
# Login modulo assunzioni
https://agenda.comeinunafavola.it/assunzioni/

# Oppure, se integrato nel routing del gestionale:
https://agenda.comeinunafavola.it/mrgest.php?op=assunzioni
```

---

## 7. Note Finali

- **Isolamento CSS/JS**: tutto sotto `#ass-app` con prefisso `ass-` — nessun conflitto col gestionale o col modulo sicurezza
- **Documenti sensibili**: i file in `uploads/` sono serviti solo via PHP autenticato — mai linkare i path direttamente in HTML
- **campo `inquadramento_contratto`**: è un campo nuovo distinto da `inquadramento` (già presente in `sic_dipendenti` e usato dal modulo sicurezza con valori liberi). Questo nuovo campo usa valori standardizzati (D2, D1, C2, C1, B2) — non toccare l'`inquadramento` esistente
- **Retrocompatibilità sicurezza**: le modifiche a `sic_dipendenti` usano `ADD COLUMN IF NOT EXISTS` — nessun rischio sul modulo sicurezza già funzionante
- **Auth Admin**: l'admin accede tramite la sessione gestionale già attiva, non ha un record in `ass_utenti`. In `includes/auth.php` c'è il commento `// ADMIN: rilevato da sessione gestionale` che indica dove adattare il controllo al tuo sistema
- **OTP stub**: `api/firma_otp.php` compila e non dà errori ma ritorna sempre `ok: false` finché non implementi le funzioni reali — il sistema gestisce questo caso mostrando un alert nell'interfaccia CdL
- **Log**: ogni operazione rilevante (creazione richiesta, upload contratto, cambio stato, invio mail) viene registrata in `sic_log` (tabella già esistente dal modulo sicurezza) con tipo `assunzione`
