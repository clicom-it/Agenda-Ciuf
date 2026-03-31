# BRIEFING DEV — Modulo Sicurezza sul Lavoro V2

> **Data**: Marzo 2026  
> **Progetto**: Integrazione Piattaforma Sicurezza → Gestionale esistente  
> **Stato**: Codice consegnato, configurazione da completare

---

## 1. Obiettivo

Integrare nel gestionale MrAge una sezione "Sicurezza sul Lavoro" che:

- Mantiene la **"foto aggiornata"** della compliance di ogni atelier (attestati validi, DVR aggiornato, semaforo verde/giallo/rosso)
- Si **aggancia alla piattaforma sicurezza esterna** via API per aggiornare automaticamente gli attestati quando un dipendente completa un corso
- Filtra automaticamente i dipendenti per **tipo contratto** (≥ 6 mesi salvo eccezioni per negozio) e **tipo atelier** (solo "diretto")
- Permette all'admin di configurare tutto **senza toccare il database** (interfaccia Admin integrata)
- Invia **email di notifica** ai dipendenti per i corsi scaduti/mancanti e alla società DVR

---

## 2. Logica del Sistema (in linguaggio naturale)

### 2.1 Chi entra nel percorso sicurezza?

Un dipendente viene incluso se:
1. L'atelier in cui lavora è di tipo **"diretto"** (campo `tipo_atelier` nell'API)
2. Il suo contratto dura **almeno 6 mesi** (configurabile per negozio)
   - Nessuna `data_fine_contratto` → **tempo indeterminato → INCLUSO**
   - `data_fine_contratto` presente → calcola durata → incluso solo se ≥ soglia mesi

### 2.2 Come si calcola lo stato del negozio?

Per ogni negozio la logica:
- Prende le **regole default** (es. "tutti devono avere Formazione, i Manager devono avere Preposto...")
- Applica eventuali **eccezioni per negozio** (nel tab Admin → anagrafica atelier)
- Verifica se ogni attestato è **valido**, **scaduto** (data emissione + anni validità < oggi) o **assente**
- Calcola il semaforo: **Verde** = tutto coperto, **Giallo** = qualcosa mancante/scaduto, **Rosso** = situazione critica + DVR assente

### 2.3 Integrazione con la piattaforma sicurezza esterna

**Flusso principale**:
```
[Cron job ogni 24h]
  → api/piattaforma.php?azione=polling
    → Per ogni dipendente: GET cognome+nome+data_nascita → piattaforma
    ← La piattaforma risponde con JSON dello storico corsi
  → Il sistema aggiorna sic_attestati automaticamente
```

**Registrazione nuovo dipendente**:
```
[Dopo sync dipendenti, per chi non è ancora registrato]
  → api/piattaforma.php?azione=registra
    → POST cognome, nome, CF, mansione, città nascita, 
           data nascita, sesso, email, password
    ← Piattaforma conferma registrazione
```

---

## 3. ⚙️ Cosa devi fare tu (Developer)

### 3.1 Installazione base

```bash
# 1. Copia la cartella sicurezza/ nella root del gestionale
cp -r sicurezza/ /percorso/gestionale/sicurezza/

# 2. Esegui la migrazione DB (DOPO schema.sql se è la prima volta):
mysql -u root -p nome_db < sicurezza/sql/schema.sql
mysql -u root -p nome_db < sicurezza/sql/schema_v2.sql
```

### 3.2 Configura config.php

Apri `sicurezza/config.php` e compila:

```php
// SEZIONE 1 — DB (usa lo stesso del gestionale o uno dedicato)
define('SIC_DB_NAME', 'nome_database');
define('SIC_DB_USER', 'utente_db');
define('SIC_DB_PASS', 'password_db');

// SEZIONE 2 — API GET dipendenti dal gestionale
// Devi creare questo endpoint (vedi §3.3)
define('GESTIONALE_API_URL', 'https://agenda.comeinunafavola.it/api/dipendenti-sicurezza');

// SEZIONE 6 — Piattaforma sicurezza esterna
define('PIATTAFORMA_API_URL',  'https://url-piattaforma.it/api');
define('PIATTAFORMA_API_KEY',  'TOKEN_FORNITO_DA_LORO');
define('PIATTAFORMA_ENDPOINT_CORSISTA', '/storico');  // endpoint GET
define('PIATTAFORMA_ENDPOINT_REGISTRA', '/corsisti'); // endpoint POST
define('PIATTAFORMA_ENDPOINT_CORSI',    '/corsi');    // endpoint GET lista

// Adatta i nomi dei campi nella risposta JSON della piattaforma:
define('PIATTAFORMA_RESP_ID_CORSO',        'id_corso');
define('PIATTAFORMA_RESP_DATA_SVOLGIMENTO','data_svolgimento');
define('PIATTAFORMA_RESP_NOME_CORSO',      'nome_corso');

// SEZIONE 7 — Email
define('SIC_EMAIL_FROM',      'sicurezza@comeinunafavola.it');
define('SIC_EMAIL_DVR_DEFAULT','studio@consulenzadvr.it');
```

### 3.3 Crea l'endpoint dipendenti nel gestionale

L'API che il modulo chiama deve restituire i dipendenti attivi con i **nuovi campi V2**.

**Endpoint**: `GET /api/dipendenti-sicurezza` → JSON array

**Campi necessari** per ogni dipendente:
```json
{
  "id": 42,
  "nome": "Mario",
  "cognome": "Rossi",
  "email": "mario.rossi@comeinunafavola.it",
  "sede_assunzione": "Bari",
  "ore_settimana": 40,
  "ruolo": "STORE MANAGER",
  "attivo": "1",
  "codice_fiscale": "RSSMRA80A01A662E",
  "data_nascita": "1980-01-01",
  "sesso": "M",
  "citta_nascita": "Bari",
  "tipo_atelier": "diretto",
  "data_inizio_contratto": "2023-01-15",
  "data_fine_contratto": null
}
```

> `data_fine_contratto = null` → tempo indeterminato → INCLUSO nel percorso sicurezza  
> `tipo_atelier = "indiretto"` → ESCLUSO dal percorso sicurezza

**In PHP, la query di base** (adattare alle tue tabelle):
```php
// Dipendenti attivi con join sull'anagrafica atelier per il campo "tipo"
$sql = "
  SELECT
    d.id, d.nome, d.cognome, d.email,
    a.nome_negozio AS sede_assunzione, d.ore_settimana, d.ruolo,
    d.attivo, d.codice_fiscale, d.data_nascita, d.sesso, d.localita_nascita AS citta_nascita,
    at.tipo AS tipo_atelier,
    c.data_inizio AS data_inizio_contratto,
    c.data_fine   AS data_fine_contratto
  FROM dipendenti d
  LEFT JOIN atelier a   ON a.id = d.atelier_id
  LEFT JOIN (
    SELECT dipendente_id, data_inizio, data_fine
    FROM contratti
    WHERE (dipendente_id, data_inizio) IN (
      SELECT dipendente_id, MAX(data_inizio) FROM contratti GROUP BY dipendente_id
    )
  ) c ON c.dipendente_id = d.id
  WHERE d.attivo = 1
";
```
> Adatta i nomi delle tabelle e dei campi alla struttura reale del gestionale.

### 3.4 Aggiungi la voce "Sicurezza" al menu Atelier in dipendenti.php

```php
// Nel file dipendenti.php, nella sezione del routing:
case 'sicurezza':
    require_once __DIR__ . '/sicurezza/sicurezza_view.php';
    break;

// Nel menu HTML (cerca il menu di Atelier e aggiungi):
// <a href="?op=sicurezza" class="...">🛡 Sicurezza</a>
```

> `sicurezza_view.php` **non ha** un proprio HTML/body, si inserisce nel layout del gestionale.

### 3.5 Configura l'SMTP per le email

Il modulo usa la funzione `sicInviaEmail()` in `includes/email_helper.php`.  
**Sostituisci il corpo** di quella funzione con l'SMTP già configurato nel gestionale:

```php
// Esempio se usi PHPMailer nel gestionale:
function sicInviaEmail(string $to, string $subject, string $body, string $attachPath = ''): bool
{
    // --- ADATTA CON LA TUA CONFIGURAZIONE SMTP ---
    require_once PERCORSO_PHPMAILER;
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host     = SMTP_HOST;     // le tue costanti già definite
    $mail->Username = SMTP_USER;
    $mail->Password = SMTP_PASS;
    $mail->setFrom(SIC_EMAIL_FROM, SIC_EMAIL_FROM_NAME);
    $mail->addAddress($to);
    $mail->isHTML(true);
    $mail->Subject = $subject;
    $mail->Body    = $body;
    if ($attachPath) $mail->addAttachment($attachPath);
    return $mail->send();
}
```

### 3.6 Configura i Cron Job

```bash
# Sync dipendenti: ogni ora (o ogni notte)
0 * * * * /usr/bin/php /percorso/gestionale/sicurezza/cron_sync.php >> /var/log/sic_sync.log 2>&1

# Polling piattaforma corsi: ogni notte alle 2:00
0 2 * * * /usr/bin/php /percorso/gestionale/sicurezza/cron_polling.php >> /var/log/sic_polling.log 2>&1
```

Crea `cron_sync.php` e `cron_polling.php` come wrapper:
```php
<?php
// cron_sync.php
require_once __DIR__ . '/config.php';
$_SERVER['REQUEST_METHOD'] = 'POST';
require_once __DIR__ . '/api/sync.php';
```
```php
<?php
// cron_polling.php
require_once __DIR__ . '/config.php';
$_GET['azione'] = 'polling';
$_SERVER['REQUEST_METHOD'] = 'POST';
require_once __DIR__ . '/api/piattaforma.php';
```

### 3.7 Inserisci gli ID piattaforma per i corsi

Dopo aver ricevuto gli ID dalla piattaforma, inseriscili dal tab **Admin → Catalogo Corsi**:

| Corso locale | ID Piattaforma | ID Aggiornamento |
|---|---|---|
| FORMAZIONE GENERALE + SPECIFICA (RISCHIO BASSO) | _(da platform dev)_ | _(da platform dev)_ |
| PREPOSTO | _(da platform dev)_ | _(da platform dev)_ |
| ADDETTO ANTINCENDIO | _(da platform dev)_ | _(da platform dev)_ |
| PRIMO SOCCORSO | _(da platform dev)_ | _(da platform dev)_ |

---

## 4. Struttura File del Modulo

```
sicurezza/
├── config.php                   ← UNICO file da configurare
├── sicurezza_view.php           ← Vista da includere in dipendenti.php
├── demo.php                     ← Demo locale (solo sviluppo)
│
├── api/
│   ├── sync.php                 ← Sync dipendenti dal gestionale (V2: filtro tipo+contratto)
│   ├── piattaforma.php          ← NEW: registrazione + polling piattaforma sicurezza
│   ├── corsi.php                ← NEW: CRUD catalogo corsi + ID piattaforma (Admin)
│   ├── regole.php               ← NEW: CRUD regole default + eccezioni + config (Admin)
│   ├── email.php                ← NEW: invio mail dipendenti (corso scaduto) e DVR company
│   ├── dashboard.php            ← Semaforo negozi
│   ├── attestati.php            ← CRUD attestati
│   ├── negozi.php               ← CRUD date DVR e campi negozio
│   └── report.php               ← Report aggregato corsi
│
├── includes/
│   ├── logic.php                ← Motore calcolo copertura (V2: filtri incluso_sicurezza + tipo)
│   ├── db.php                   ← Helper PDO
│   └── email_helper.php         ← NEW: funzione sicInviaEmail() da adattare allo SMTP del gestionale
│
├── assets/
│   ├── style.css                ← CSS scoped sotto #sic-app
│   └── app.js                   ← JS V2 con email + admin panel
│
├── sql/
│   ├── schema.sql               ← Schema tabelle (prima installazione)
│   └── schema_v2.sql            ← Migrazione V2 (ALTER + nuove tabelle)
│
├── cron_sync.php                ← Da creare (wrapper per cron)
└── cron_polling.php             ← Da creare (wrapper per cron)
```

---

## 5. URL di Accesso

```
https://agenda.comeinunafavola.it/dipendenti.php?op=sicurezza
```

---

## 6. Note Finali

- **CSS/JS isolati**: tutto sotto `#sic-app` con prefisso `sic-` — nessun conflitto col gestionale
- **Nessun dump DB manuale**: regole, corsi e configurazioni si gestiscono dall'interfaccia Admin
- **Backward compatible**: se `tipo_atelier` non è presente nell'API, tutti i negozi vengono inclusi
- **PDF DVR**: per l'allegato PDF in email c'è il blocco commentato in `api/email.php` pronto per MPDF/DomPDF — installare via `composer require mpdf/mpdf`
- **Log**: ogni operazione (sync, polling, email) viene loggata in `sic_log` per tracciabilità
