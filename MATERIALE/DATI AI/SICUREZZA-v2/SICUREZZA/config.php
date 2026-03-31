<?php
// =============================================================
// MODULO SICUREZZA SUL LAVORO — Configurazione
// =============================================================
// Questo è l'UNICO file che il developer deve modificare.
// Inserisci qui le credenziali del database e i parametri
// di connessione all'API del gestionale.
// =============================================================


// -------------------------------------------------------------
// 1. CONNESSIONE DATABASE
// -------------------------------------------------------------
// Puoi usare lo stesso database del gestionale (aggiungendo
// le tabelle "sic_*") oppure un database separato dedicato.
// In entrambi i casi, l'utente DB deve avere permessi su SELECT,
// INSERT, UPDATE, DELETE sulle tabelle sic_*.

define('SIC_DB_HOST',    'localhost');      // Host del database (di solito localhost)
define('SIC_DB_NAME',    'sicurezza');      // Nome del database
define('SIC_DB_USER',    'root');           // Utente MySQL
define('SIC_DB_PASS',    '');               // Password MySQL
define('SIC_DB_CHARSET', 'utf8mb4');        // Charset (non modificare)
define('SIC_DB_PORT',    3306);             // Porta MySQL (default 3306)


// -------------------------------------------------------------
// 2. API DEL GESTIONALE — Endpoint dipendenti attivi
// -------------------------------------------------------------
// Il gestionale deve esporre un endpoint JSON che restituisce
// la lista di tutti i dipendenti ATTIVI.
//
// Esempio di risposta attesa (array JSON):
// [
//   { "id": 1, "nome": "Mario", "cognome": "Rossi",
//     "sede_assunzione": "Bari", "ore_settimana": 40,
//     "ruolo": "Store Manager" },
//   ...
// ]
//
// NOTA: I nomi dei campi possono essere diversi — li mappi
// nella sezione 3 qui sotto.

define('GESTIONALE_API_URL', 'https://agenda.comeinunafavola.it/api/dipendenti-attivi');
// URL completo dell'endpoint. Il developer deve crearlo nel gestionale.

define('GESTIONALE_API_KEY', '');
// Token/chiave API per autenticare la richiesta.
// Lascia vuoto se l'endpoint non richiede autenticazione.

define('GESTIONALE_API_TOKEN_HEADER', 'Authorization');
// Header HTTP su cui inviare il token (es. "Authorization", "X-API-Key").
// Usato solo se GESTIONALE_API_KEY non è vuoto.

define('GESTIONALE_API_TIMEOUT', 30);
// Secondi massimi di attesa per la risposta dell'API.


// -------------------------------------------------------------
// 3. MAPPING CAMPI API → MODULO SICUREZZA
// -------------------------------------------------------------
// Qui indichi come si chiamano i campi nel JSON restituito
// dall'endpoint del gestionale, in modo che il modulo sicurezza
// li mappi correttamente sulle proprie colonne.

// Il gestionale restituisce nome e cognome SEPARATI?
// true  → usa FIELD_NOME e FIELD_COGNOME e li concatena (es. "Rossi Mario")
// false → usa FIELD_NOME come campo unico già completo
define('FIELD_NOME_SEPARATO', true);

define('FIELD_NOME',          'nome');           // Campo del nome (o nome completo)
define('FIELD_COGNOME',       'cognome');         // Campo del cognome (solo se FIELD_NOME_SEPARATO = true)
define('FIELD_SEDE',          'sede_assunzione'); // Campo della sede/negozio di assunzione
define('FIELD_ORE',           'ore_settimana');   // Campo delle ore settimanali
define('FIELD_INQUADRAMENTO', 'ruolo');           // Campo del ruolo/inquadramento
define('FIELD_ID_ESTERNO',    'id');              // Campo ID primario nel gestionale (per tracciabilità)

// Campo e valore che indicano se il dipendente è ATTIVO.
// Il filtro viene applicato lato API (richiedi solo attivi),
// ma viene riapplicato anche lato PHP come sicurezza aggiuntiva.
// Se l'API restituisce già solo attivi, lascia come da default.
define('FIELD_ATTIVO',        'attivo');  // Nome campo stato attività
define('FIELD_ATTIVO_VALORE', '1');       // Valore che significa "attivo" (può essere 1, "si", "attivo", true)

// Nuovi campi V2 — necessari per:
//   a) Registrazione sulla piattaforma sicurezza esterna
//   b) Filtro dipendenti per durata contratto
//   c) Filtro atelier per tipo (solo "diretto")

define('FIELD_EMAIL',                'email');             // Email aziendale del dipendente
define('FIELD_CODICE_FISCALE',       'codice_fiscale');    // Codice fiscale
define('FIELD_DATA_NASCITA',         'data_nascita');      // Data di nascita (formato YYYY-MM-DD o quello del gestionale)
define('FIELD_DATA_NASCITA_FORMAT',  'Y-m-d');             // Formato della data nel JSON del gestionale
define('FIELD_SESSO',                'sesso');             // Sesso: 'M' o 'F'
define('FIELD_CITTA_NASCITA',        'citta_nascita');     // Città di nascita

// Date contratto (dalla sezione "Retribuzione" dell'anagrafica dipendente)
// Il developer deve includere l'ULTIMA riga contrattuale:
//   data_fine_contratto = NULL → tempo indeterminato → INCLUSO
//   data_fine_contratto presente → durata calcolata → incluso solo se ≥ soglia mesi
define('FIELD_DATA_INIZIO_CONTRATTO', 'data_inizio_contratto'); // es. "2023-01-15"
define('FIELD_DATA_FINE_CONTRATTO',   'data_fine_contratto');   // es. "2024-06-30" oppure null/""

// Tipo atelier: solo gli atelier "diretto" partecipano alla sicurezza
// Il campo proviene dall'anagrafica atelier tramite JOIN nella query del gestionale
define('FIELD_TIPO_ATELIER',           'tipo_atelier');  // Nome campo nel JSON
define('FIELD_TIPO_ATELIER_DIRETTO',   'diretto');       // Valore che identifica un atelier diretto


// -------------------------------------------------------------
// 4. AUTENTICAZIONE (opzionale)
// -------------------------------------------------------------
// Se il modulo sicurezza viene integrato DENTRO il gestionale
// (stesso server, stessa sessione PHP), puoi abilitare il
// controllo della sessione per proteggere le pagine.
//
// COME USARLO:
//   1. Decommenta le due righe define() qui sotto
//   2. Imposta SIC_SESSION_KEY con la variabile di sessione
//      che il gestionale usa per l'utente loggato
//      (es. $_SESSION['user_id'], $_SESSION['logged_in'], ecc.)
//   3. Imposta SIC_SESSION_REDIRECT con l'URL di login

// define('SIC_SESSION_KEY',      'user_id');          // chiave in $_SESSION che deve esistere
// define('SIC_SESSION_REDIRECT', '/mrgest.php');       // redirect se non autenticato


// -------------------------------------------------------------
// 5. IMPOSTAZIONI GENERALI
// -------------------------------------------------------------

define('SIC_TIMEZONE', 'Europe/Rome');   // Fuso orario per il calcolo delle scadenze
date_default_timezone_set(SIC_TIMEZONE);

define('SIC_APP_NAME', 'Sicurezza sul Lavoro'); // Nome visualizzato nell'interfaccia


// -------------------------------------------------------------
// 6. PIATTAFORMA SICUREZZA ESTERNA — Credenziali API
// -------------------------------------------------------------
// La piattaforma esterna espone API REST per:
//   - Registrare un nuovo corsista (POST)
//   - Ottenere lo storico corsi di un corsista (GET)
//   - Ottenere l'elenco dei corsi disponibili (GET)
//
// Il dev della piattaforma fornirà l'URL base e le credenziali.

define('PIATTAFORMA_API_URL', '');
// URL base del server della piattaforma (senza slash finale)
// es. 'https://sicurezza.example.it/api/v1'

define('PIATTAFORMA_API_KEY', '');
// Token/chiave API per autenticare le richieste
// Usato nell'header HTTP configurato sotto

define('PIATTAFORMA_API_TOKEN_HEADER', 'Authorization');
// Header HTTP su cui inviare il token (chiedi al dev della piattaforma)
// Esempi: 'Authorization', 'X-Api-Key', 'Bearer'

define('PIATTAFORMA_API_TIMEOUT', 30);
// Secondi massimi di attesa per risposta

// --- Endpoint della piattaforma ---
// Gli endpoint vengono concatenati a PIATTAFORMA_API_URL
// es. PIATTAFORMA_API_URL . PIATTAFORMA_ENDPOINT_CORSISTA

define('PIATTAFORMA_ENDPOINT_CORSISTA',  '');
// Endpoint GET storico corsi dipendente
// Il dev dichiara di accettare: cognome, nome, data_nascita come parametri GET
// es. '/corsisti/storico'

define('PIATTAFORMA_ENDPOINT_CORSI',     '');
// Endpoint GET elenco corsi disponibili (senza parametri)
// es. '/corsi'

define('PIATTAFORMA_ENDPOINT_REGISTRA',  '');
// Endpoint POST per registrare un nuovo corsista
// es. '/corsisti'

// --- Formato date per le API della piattaforma ---
// Il dev ha specificato formato gg-mm-aaaa per i parametri GET
define('PIATTAFORMA_DATA_FORMAT_INVIO',  'd-m-Y');  // formato da inviare
define('PIATTAFORMA_DATA_FORMAT_RICEVI', 'd-m-Y');  // formato nelle risposte JSON

// --- Mapping campi GET per identificare il corsista ---
// Parametri QueryString per l'endpoint CORSISTA
define('PIATTAFORMA_GET_COGNOME',     'cognome');     // nome parametro GET per cognome
define('PIATTAFORMA_GET_NOME',        'nome');        // nome parametro GET per nome
define('PIATTAFORMA_GET_DATA_NASCITA','data_nascita');// nome parametro GET per data nascita

// --- Mapping campi nella RISPOSTA JSON della piattaforma ---
// Il dev ha confermato: risponde con JSON dello storico corsista.
// Adattare questi nomi ai campi reali nella risposta della piattaforma.
// Esempio risposta attesa:
// [
//   {
//     "id_corso": "C001",
//     "id_aggiornamento": "A002",
//     "nome_corso": "Preposto",
//     "data_svolgimento": "15-06-2024",
//     "valido": true
//   }, ...
// ]
define('PIATTAFORMA_RESP_ID_CORSO',        'id_corso');        // campo ID corso
define('PIATTAFORMA_RESP_ID_AGGIORNAMENTO','id_aggiornamento');// campo ID aggiornamento
define('PIATTAFORMA_RESP_NOME_CORSO',      'nome_corso');      // campo nome corso
define('PIATTAFORMA_RESP_DATA_SVOLGIMENTO','data_svolgimento');// campo data svolgimento

// --- Mapping campi POST per registrare un corsista ---
// Richiesti dalla piattaforma: Cognome, Nome, CF, Mansione,
// Città nascita, Data nascita (gg-mm-aaaa), Sesso (M/F), email, Password
// !!!! Il dev della piattaforma deve confermare i nomi dei campi POST !!!!
define('PIATTAFORMA_POST_COGNOME',      'cognome');
define('PIATTAFORMA_POST_NOME',         'nome');
define('PIATTAFORMA_POST_CF',           'codice_fiscale');
define('PIATTAFORMA_POST_MANSIONE',     'mansione');
define('PIATTAFORMA_POST_CITTA',        'citta_nascita');
define('PIATTAFORMA_POST_DATA_NASCITA', 'data_nascita');
define('PIATTAFORMA_POST_SESSO',        'sesso');
define('PIATTAFORMA_POST_EMAIL',        'email');
define('PIATTAFORMA_POST_PASSWORD',     'password');

// Password default assegnata ai corsisti al momento della registrazione
// Il dipendente la cambierà al primo accesso
define('PIATTAFORMA_PASSWORD_DEFAULT', '');
// es. 'Benvenuto@2024' oppure lascia vuoto se la piattaforma genera da sola

// --- Polling ---
define('PIATTAFORMA_POLLING_ORE', 24);
// Ogni quante ore viene eseguito il polling dei corsi completati
// Configurare il cron job di conseguenza (es. ogni 24h)


// -------------------------------------------------------------
// 7. EMAIL — Configurazione invio messaggi
// -------------------------------------------------------------
// Il gestionale ha già un sistema SMTP configurato.
// OPZIONE A (consigliata): usa direttamente PHPMailer o la
//   funzione di invio del gestionale — sostituisci la funzione
//   sicInviaEmail() in includes/email_helper.php con la tua.
// OPZIONE B: usa il mail() nativo di PHP con i parametri qui sotto.

define('SIC_EMAIL_FROM',      '');
// Indirizzo mittente (es. 'sicurezza@comeinunafavola.it')

define('SIC_EMAIL_FROM_NAME', 'Sicurezza sul Lavoro');
// Nome mittente visualizzato nel client email

define('SIC_EMAIL_DVR_DEFAULT', '');
// Destinatario default per le mail DVR (es. lo studio che redige i DVR)
// Se un negozio ha il campo email_dvr impostato, viene usato quello;
// altrimenti si usa questo valore globale.

// Nota: i dati SMTP (host, porta, user, pass) vengono letti dalla
// configurazione del gestionale per non duplicare le credenziali.
// Adattare la funzione sicInviaEmail() in includes/email_helper.php.
