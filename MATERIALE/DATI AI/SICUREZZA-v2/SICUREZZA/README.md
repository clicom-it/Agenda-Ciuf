# 🛡 Modulo Sicurezza sul Lavoro — Guida Installazione

Interfaccia web completa per la gestione di attestati sicurezza, DVR e copertura personale.
Sostituisce il precedente sistema Google Sheets + Apps Script.

---

## 📁 Struttura File

```
sicurezza/
├── config.php               ← ⚠️ UNICO FILE DA MODIFICARE
├── index.php                ← Pagina principale (apri questa nel browser)
│
├── sql/
│   └── schema.sql           ← Eseguire UNA SOLA VOLTA sul DB
│
├── includes/
│   ├── db.php               ← Connessione database (non modificare)
│   └── logic.php            ← Motore di calcolo (non modificare)
│
├── api/
│   ├── sync.php             ← Sincronizza dipendenti dal gestionale
│   ├── dashboard.php        ← Dati dashboard (semafori negozi)
│   ├── negozi.php           ← CRUD negozi
│   ├── attestati.php        ← CRUD attestati
│   └── report.php           ← Report corsi necessari
│
└── assets/
    ├── style.css            ← Stile interfaccia
    └── app.js               ← Logica frontend
```

---

## 🚀 Installazione in 3 Passi

### Passo 1 — Crea il Database

Esegui lo script SQL **una sola volta** sul database scelto:

```bash
mysql -u root -p nome_database < sql/schema.sql
```

Oppure importalo da phpMyAdmin o da qualsiasi client SQL.

> **Nota:** Lo script crea solo tabelle con prefisso `sic_`.
> Non tocca le tabelle esistenti del gestionale.

---

### Passo 2 — Configura `config.php`

Apri `config.php` e modifica **solo** la sezione che ti interessa:

```php
// Connessione database
define('SIC_DB_HOST', 'localhost');
define('SIC_DB_NAME', 'nome_del_tuo_db');
define('SIC_DB_USER', 'utente_db');
define('SIC_DB_PASS', 'password_db');

// URL del tuo endpoint dipendenti
define('GESTIONALE_API_URL', 'https://tuogestionale.it/api/dipendenti-attivi');

// Mapping campi (nomi dei campi nel JSON che l'API restituisce)
define('FIELD_NOME',          'nome');
define('FIELD_COGNOME',       'cognome');
define('FIELD_SEDE',          'sede_assunzione');
define('FIELD_ORE',           'ore_settimana');
define('FIELD_INQUADRAMENTO', 'ruolo');
```

---

### Passo 3 — Crea l'Endpoint nel Gestionale

Il gestionale deve esporre **un solo endpoint** che restituisce i dipendenti attivi in formato JSON:

**Esempio endpoint PHP da aggiungere al gestionale:**

```php
// file: api/dipendenti-attivi.php
<?php
// Connessione al tuo DB
$pdo = new PDO('mysql:host=localhost;dbname=tuo_db', 'user', 'pass');

// Query: seleziona dipendenti attivi con sede e ore
// ADATTA i nomi di tabella e colonne al tuo schema!
$stmt = $pdo->query("
    SELECT
        d.id,
        d.nome,
        d.cognome,
        a.nome AS sede_assunzione,   /* nome del punto vendita */
        d.ore_settimana,
        r.nome AS ruolo              /* ruolo/inquadramento */
    FROM dipendenti d
    JOIN atelier a ON d.id_atelier = a.id
    JOIN ruoli r   ON d.id_ruolo   = r.id
    WHERE d.attivo = 1              /* solo dipendenti in forza */
");

header('Content-Type: application/json');
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
```

> ⚠️ **I nomi delle colonne nel SELECT devono corrispondere
> ai `FIELD_*` definiti in `config.php`.**

---

## ▶️ Avvio

Copia la cartella `sicurezza/` nella document root del server del gestionale,
poi apri nel browser:

```
https://tuogestionale.it/sicurezza/index.php
```

---

## 🔄 Flusso di Utilizzo

1. **Prima volta**: clicca "Sincronizza Dipendenti" per caricare il personale dal gestionale
2. **Aggiungi negozi** dalla sezione "Negozi & DVR" se non si creano automaticamente
3. **Aggiungi attestati** dalla sezione "Attestati" man mano che vengono rilasciati
4. La **Dashboard** si aggiorna automaticamente mostrando lo stato di ogni negozio
5. Usa il **Report Corsi** per pianificare le sessioni formative

---

## 🤖 Sincronizzazione Automatica (Cron)

Per aggiornare i dipendenti automaticamente ogni notte, aggiungi un cron job:

```bash
# Ogni notte alle 02:00 — aggiorna dipendenti dal gestionale
0 2 * * * curl -s -X POST https://tuogestionale.it/sicurezza/api/sync.php > /dev/null
```

---

## 📌 Note Tecniche

| Requisito     | Versione minima |
|---------------|----------------|
| PHP           | 8.0+           |
| MySQL/MariaDB | 5.7+           |
| Estensione    | PDO + curl     |

---

*Modulo sviluppato per Come in una Favola — Sicurezza sul Lavoro*
