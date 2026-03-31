-- =============================================================
-- MODULO SICUREZZA SUL LAVORO — Schema Database
-- =============================================================
-- Eseguire questo script UNA SOLA VOLTA sul database scelto
-- (può essere lo stesso DB del gestionale o uno separato).
-- Tutte le tabelle usano il prefisso "sic_" per non entrare
-- in conflitto con tabelle già esistenti nel gestionale.
-- =============================================================


-- -------------------------------------------------------------
-- sic_negozi
-- Anagrafica di ogni punto vendita/atelier.
-- Corrisponde al foglio NEGOZI del vecchio sistema Sheets.
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `sic_negozi` (
    `id`                      INT AUTO_INCREMENT PRIMARY KEY,
    `nome_negozio`            VARCHAR(255) NOT NULL UNIQUE  COMMENT 'Nome univoco del negozio (es. "Bari")',
    `ragione_sociale`         VARCHAR(255)                  COMMENT 'Ragione sociale dell\'azienda',
    `mail`                    VARCHAR(255)                  COMMENT 'Email di riferimento del negozio',
    `via`                     VARCHAR(255)                  COMMENT 'Indirizzo',
    `localita`                VARCHAR(255)                  COMMENT 'Città',
    `cap`                     VARCHAR(10)                   COMMENT 'CAP',
    `provincia`               VARCHAR(5)                    COMMENT 'Sigla provincia (es. BA)',
    `data_dvr`                DATE NULL                     COMMENT 'Data di emissione/ultimo aggiornamento del DVR',
    `data_ultima_variazione`  DATE NULL                     COMMENT 'Data dell\'ultima variazione del personale/indirizzo (aggiornata automaticamente)',
    `stato_dvr`               VARCHAR(50) DEFAULT 'DA EMETTERE'
                                                            COMMENT 'Calcolato automaticamente: OK | DA AGGIORNARE | DA EMETTERE',
    `created_at`              TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at`              TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Anagrafica negozi/atelier';


-- -------------------------------------------------------------
-- sic_dipendenti
-- Elenco dei dipendenti, sincronizzato dal gestionale.
-- NON modificare manualmente: viene aggiornato tramite l\'API
-- di sincronizzazione (api/sync.php).
-- Corrisponde al foglio DIPENDENTI del vecchio sistema Sheets.
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `sic_dipendenti` (
    `id`              INT AUTO_INCREMENT PRIMARY KEY,
    `gestionale_id`   INT NULL          COMMENT 'ID del dipendente nel gestionale principale (per tracciare gli aggiornamenti)',
    `cognome_nome`    VARCHAR(255) NOT NULL COMMENT 'Cognome e nome completo (es. "Rossi Mario")',
    `sede_assunzione` VARCHAR(255)      COMMENT 'Nome del negozio di assunzione (deve corrispondere a sic_negozi.nome_negozio)',
    `ore`             DECIMAL(5,2)      COMMENT 'Ore settimanali contrattuali',
    `inquadramento`   VARCHAR(255)      COMMENT 'Ruolo/qualifica (es. "Store Manager", "Addetto alle vendite")',
    `attivo`          TINYINT(1) DEFAULT 1 COMMENT '1 = in forza | 0 = uscito',
    `synced_at`       TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                                        COMMENT 'Data e ora dell\'ultima sincronizzazione',
    INDEX (`sede_assunzione`),
    INDEX (`cognome_nome`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Dipendenti attivi, sincronizzati dal gestionale';


-- -------------------------------------------------------------
-- sic_regole_attestati
-- Definisce quali corsi sono obbligatori per ogni inquadramento
-- e quante persone per negozio devono averli.
-- Corrisponde al foglio REGOLE_ATTESTATI.
-- Fabbisogno: "TUTTI" = tutti i dipendenti con quell'inquadramento
--             "1", "2"... = almeno N dipendenti per negozio
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `sic_regole_attestati` (
    `id`            INT AUTO_INCREMENT PRIMARY KEY,
    `inquadramento` VARCHAR(255) NOT NULL COMMENT 'Qualifica a cui si applica la regola',
    `nome_ruolo`    VARCHAR(255) NOT NULL COMMENT 'Nome del corso/certificazione richiesta',
    `fabbisogno`    VARCHAR(50)  DEFAULT '1'
                                 COMMENT '"TUTTI" = tutta la qualifica | numero = quanti ne bastano per negozio',
    UNIQUE KEY `uk_inq_ruolo` (`inquadramento`, `nome_ruolo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Regole di copertura attestati per inquadramento';


-- -------------------------------------------------------------
-- sic_regole_eccezioni
-- Permette di aggiungere o escludere regole per negozi specifici.
-- Corrisponde al foglio REGOLE_ECCEZIONI.
-- Tipo eccezione: "AGGIUNGI" = aggiunge la regola per quel negozio
--                "ESCLUDI"  = rimuove la regola per quel negozio
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `sic_regole_eccezioni` (
    `id`              INT AUTO_INCREMENT PRIMARY KEY,
    `nome_negozio`    VARCHAR(255) NOT NULL COMMENT 'Negozio a cui si applica l\'eccezione',
    `inquadramento`   VARCHAR(255) NOT NULL COMMENT 'Inquadramento coinvolto',
    `nome_ruolo`      VARCHAR(255) NOT NULL COMMENT 'Corso/ruolo coinvolto',
    `tipo_eccezione`  VARCHAR(50)  NOT NULL COMMENT '"AGGIUNGI" o "ESCLUDI"',
    INDEX (`nome_negozio`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Eccezioni per negozi specifici alle regole standard';


-- -------------------------------------------------------------
-- sic_attestati
-- Registro di tutti gli attestati emessi ai dipendenti.
-- La data di scadenza viene calcolata automaticamente sommando
-- gli anni di validità definiti in sic_durata_corsi.
-- Corrisponde al foglio ATTESTATI_INSERIMENTO.
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `sic_attestati` (
    `id`              INT AUTO_INCREMENT PRIMARY KEY,
    `cognome_nome`    VARCHAR(255) NOT NULL COMMENT 'Deve corrispondere a sic_dipendenti.cognome_nome',
    `sede_assunzione` VARCHAR(255) NOT NULL COMMENT 'Deve corrispondere a sic_dipendenti.sede_assunzione',
    `nome_attestato`  VARCHAR(255) NOT NULL COMMENT 'Deve corrispondere a sic_regole_attestati.nome_ruolo',
    `data_emissione`  DATE         NOT NULL COMMENT 'Data di emissione dell\'attestato',
    `note`            TEXT         NULL     COMMENT 'Note opzionali (es. ente formatore)',
    `created_at`      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at`      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    -- Chiave univoca: ogni dipendente può avere un solo attestato attivo per corso/sede
    UNIQUE KEY `uk_attestato` (`cognome_nome`, `sede_assunzione`, `nome_attestato`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Attestati formativi dei dipendenti';


-- -------------------------------------------------------------
-- sic_durata_corsi
-- Definisce per quanti anni è valido ogni tipo di corso.
-- Corrisponde al foglio DURATA_CORSI.
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `sic_durata_corsi` (
    `id`             INT AUTO_INCREMENT PRIMARY KEY,
    `corso`          VARCHAR(255) NOT NULL UNIQUE COMMENT 'Nome del corso (deve corrispondere esattamente a sic_attestati.nome_attestato)',
    `anni_validita`  INT          NOT NULL DEFAULT 5 COMMENT 'Anni di validità dell\'attestato'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Validità in anni per ogni tipologia di corso';


-- =============================================================
-- DATI INIZIALI
-- Questi valori replicano la configurazione attuale da Sheets.
-- Modificali liberamente dopo l'installazione.
-- =============================================================

-- Durata standard dei corsi
INSERT IGNORE INTO `sic_durata_corsi` (`corso`, `anni_validita`) VALUES
('FORMAZIONE GENERALE + SPECIFICA (RISCHIO BASSO)', 5),
('PREPOSTO',               5),
('ADDETTO ANTINCENDIO',    3),
('PRIMO SOCCORSO',         3),
('RLS',                    4);

-- Regole attestati standard (da foglio REGOLE_ATTESTATI)
INSERT IGNORE INTO `sic_regole_attestati` (`inquadramento`, `nome_ruolo`, `fabbisogno`) VALUES
('ADDETTO ALLE VENDITE', 'FORMAZIONE GENERALE + SPECIFICA (RISCHIO BASSO)', 'TUTTI'),
('STORE MANAGER',        'FORMAZIONE GENERALE + SPECIFICA (RISCHIO BASSO)', 'TUTTI'),
('STORE MANAGER',        'PREPOSTO',             '1'),
('STORE MANAGER',        'ADDETTO ANTINCENDIO',  '1'),
('STORE MANAGER',        'PRIMO SOCCORSO',       '1');
