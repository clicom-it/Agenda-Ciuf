-- =============================================================
-- SCHEMA V2 — Migrazione Modulo Sicurezza
-- =============================================================
-- Eseguire DOPO schema.sql (già installato).
-- Usa IF NOT EXISTS / IF EXISTS per essere sicuro di poter
-- rieseguire questo file senza errori in caso di doppia esecuzione.
-- =============================================================

-- -------------------------------------------------------------
-- 1. Nuovi campi in sic_dipendenti
--    Aggiunge i dati demografici necessari per la registrazione
--    sulla piattaforma di sicurezza e il filtro contrattuale.
-- -------------------------------------------------------------
ALTER TABLE `sic_dipendenti`
    ADD COLUMN IF NOT EXISTS `email`                  VARCHAR(255) NULL    COMMENT 'Email aziendale del dipendente (usata per invio mail corsi)',
    ADD COLUMN IF NOT EXISTS `codice_fiscale`         VARCHAR(16)  NULL    COMMENT 'CF — richiesto dalla piattaforma per la registrazione',
    ADD COLUMN IF NOT EXISTS `data_nascita`           DATE         NULL    COMMENT 'Data di nascita — richiesta dalla piattaforma per il lookup',
    ADD COLUMN IF NOT EXISTS `sesso`                  CHAR(1)      NULL    COMMENT 'M o F — richiesto dalla piattaforma',
    ADD COLUMN IF NOT EXISTS `citta_nascita`          VARCHAR(255) NULL    COMMENT 'Città di nascita — richiesta dalla piattaforma',
    ADD COLUMN IF NOT EXISTS `data_inizio_contratto`  DATE         NULL    COMMENT 'Inizio ultimo periodo contrattuale (da sez. Retribuzione)',
    ADD COLUMN IF NOT EXISTS `data_fine_contratto`    DATE         NULL    COMMENT 'Fine ultimo periodo contrattuale (NULL = tempo indeterminato)',
    ADD COLUMN IF NOT EXISTS `piattaforma_registrato` TINYINT(1)   DEFAULT 0 COMMENT '1 = già registrato sulla piattaforma sicurezza',
    ADD COLUMN IF NOT EXISTS `incluso_sicurezza`      TINYINT(1)   DEFAULT 1 COMMENT 'Calcolato: 1 se il contratto soddisfa la soglia minima';

-- -------------------------------------------------------------
-- 2. Nuovi campi in sic_negozi
--    Aggiunge soglia contrattuale per negozio, email per il DVR
--    e tipo (diretto/indiretto — sincronizzato dal gestionale).
--    Solo gli atelier "diretto" partecipano alla sicurezza.
-- -------------------------------------------------------------
ALTER TABLE `sic_negozi`
    ADD COLUMN IF NOT EXISTS `soglia_mesi` INT         DEFAULT 6    COMMENT 'Mesi minimi di contratto per entrare nel percorso sicurezza (default 6)',
    ADD COLUMN IF NOT EXISTS `email_dvr`   VARCHAR(255) NULL         COMMENT 'Email a cui inviare la foto DVR aggiornata. Se NULL usa SIC_EMAIL_DVR_DEFAULT in config.php',
    ADD COLUMN IF NOT EXISTS `tipo`        VARCHAR(50)  NULL         COMMENT 'Tipo atelier: "diretto" o "indiretto" — sincronizzato dal gestionale';

-- -------------------------------------------------------------
-- 3. Nuova tabella: catalogo corsi con ID piattaforma
--    Mappa i corsi che usiamo agli ID della piattaforma esterna.
--    Il developer della piattaforma fornirà gli ID da inserire.
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `sic_corsi_piattaforma` (
    `id`                        INT AUTO_INCREMENT PRIMARY KEY,
    `nome_locale`               VARCHAR(255) NOT NULL   COMMENT 'Nome del corso usato in questo sistema (es. "PREPOSTO")',
    `id_piattaforma`            VARCHAR(100) NULL        COMMENT 'ID corso sulla piattaforma di sicurezza (fornito dal dev della piattaforma)',
    `id_aggiornamento`          VARCHAR(100) NULL        COMMENT 'ID aggiornamento/rinnovo corso sulla piattaforma',
    `anni_validita`             INT          DEFAULT 5   COMMENT 'Anni di validità dell attestato',
    `attivo`                    TINYINT(1)   DEFAULT 1   COMMENT '1 = corso attivo nel sistema',
    UNIQUE KEY `uk_nome_locale` (`nome_locale`)
) ENGINE=MyIsam DEFAULT CHARSET=utf8mb4 COMMENT='Catalogo corsi con mapping agli ID della piattaforma esterna';

-- Inserisce i corsi di default già usati (senza ID piattaforma — il dev li aggiungerà)
INSERT IGNORE INTO `sic_corsi_piattaforma` (`nome_locale`, `anni_validita`) VALUES
('FORMAZIONE GENERALE + SPECIFICA (RISCHIO BASSO)', 5),
('PREPOSTO',               5),
('ADDETTO ANTINCENDIO',    3),
('PRIMO SOCCORSO',         3),
('RLS',                    4);

-- Allinea sic_durata_corsi con sic_corsi_piattaforma (retrocompatibilità)
-- I corsi già in sic_durata_corsi vengono aggiornati con l'ID piattaforma
ALTER TABLE `sic_regole_attestati`
    ADD COLUMN IF NOT EXISTS `id_corso_piattaforma` INT NULL
        COMMENT 'FK a sic_corsi_piattaforma.id — usato per il polling automatico';

-- Aggiorna il riferimento nelle regole esistenti
UPDATE `sic_regole_attestati` r
JOIN `sic_corsi_piattaforma` c ON UPPER(TRIM(c.nome_locale)) = UPPER(TRIM(r.nome_ruolo))
SET r.id_corso_piattaforma = c.id
WHERE r.id_corso_piattaforma IS NULL;

-- -------------------------------------------------------------
-- 4. Aggiunge configurazione globale (chiave-valore)
--    Usata per la soglia mesi default e altri parametri
--    modificabili dall'admin senza toccare il codice.
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `sic_config` (
    `chiave`  VARCHAR(100)  NOT NULL PRIMARY KEY  COMMENT 'Nome parametro',
    `valore`  TEXT          NULL                  COMMENT 'Valore parametro',
    `nota`    VARCHAR(255)  NULL                  COMMENT 'Spiegazione del parametro'
) ENGINE=MyIsam DEFAULT CHARSET=utf8mb4 COMMENT='Configurazione modificabile dall admin';

INSERT IGNORE INTO `sic_config` (`chiave`, `valore`, `nota`) VALUES
('soglia_mesi_default', '6',  'Mesi minimi di contratto per entrare nel percorso sicurezza'),
('email_dvr_default',   '',   'Email del destinatario default per notifiche DVR (es. studio_dvr@esempio.it)'),
('email_from',          '',   'Indirizzo mittente le email del modulo sicurezza'),
('polling_attivo',      '1',  '1 = polling piattaforma sicurezza attivo, 0 = disabilitato');

-- -------------------------------------------------------------
-- 5. Log delle operazioni sul modulo (per tracciabilità)
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `sic_log` (
    `id`          INT AUTO_INCREMENT PRIMARY KEY,
    `tipo`        VARCHAR(50)  NOT NULL COMMENT 'sync | polling | email | registrazione | errore',
    `messaggio`   TEXT         NOT NULL COMMENT 'Dettaglio operazione',
    `created_at`  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyIsam DEFAULT CHARSET=utf8mb4 COMMENT='Log operazioni automatiche del modulo sicurezza';
