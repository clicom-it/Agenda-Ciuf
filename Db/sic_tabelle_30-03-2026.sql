-- phpMyAdmin SQL Dump
-- version 5.2.2deb1+deb13u1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Creato il: Mar 30, 2026 alle 12:33
-- Versione del server: 11.8.3-MariaDB-0+deb13u1 from Debian
-- Versione PHP: 8.2.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `agecome_db2`
--

-- --------------------------------------------------------

--
-- Struttura della tabella `sic_attestati`
--

DROP TABLE IF EXISTS `sic_attestati`;
CREATE TABLE `sic_attestati` (
  `id` int(11) NOT NULL,
  `cognome_nome` varchar(255) NOT NULL COMMENT 'Deve corrispondere a sic_dipendenti.cognome_nome',
  `sede_assunzione` varchar(255) NOT NULL COMMENT 'Deve corrispondere a sic_dipendenti.sede_assunzione',
  `nome_attestato` varchar(255) NOT NULL COMMENT 'Deve corrispondere a sic_regole_attestati.nome_ruolo',
  `data_emissione` date NOT NULL COMMENT 'Data di emissione dell''attestato',
  `note` text DEFAULT NULL COMMENT 'Note opzionali (es. ente formatore)',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci COMMENT='Attestati formativi dei dipendenti';

-- --------------------------------------------------------

--
-- Struttura della tabella `sic_config`
--

DROP TABLE IF EXISTS `sic_config`;
CREATE TABLE `sic_config` (
  `chiave` varchar(100) NOT NULL COMMENT 'Nome parametro',
  `valore` text DEFAULT NULL COMMENT 'Valore parametro',
  `nota` varchar(255) DEFAULT NULL COMMENT 'Spiegazione del parametro'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci COMMENT='Configurazione modificabile dall admin';

--
-- Dump dei dati per la tabella `sic_config`
--

INSERT INTO `sic_config` (`chiave`, `valore`, `nota`) VALUES
('soglia_mesi_default', '6', 'Mesi minimi di contratto per entrare nel percorso sicurezza'),
('email_dvr_default', '', 'Email del destinatario default per notifiche DVR (es. studio_dvr@esempio.it)'),
('email_from', '', 'Indirizzo mittente le email del modulo sicurezza'),
('polling_attivo', '1', '1 = polling piattaforma sicurezza attivo, 0 = disabilitato');

-- --------------------------------------------------------

--
-- Struttura della tabella `sic_corsi_piattaforma`
--

DROP TABLE IF EXISTS `sic_corsi_piattaforma`;
CREATE TABLE `sic_corsi_piattaforma` (
  `id` int(11) NOT NULL,
  `nome_locale` varchar(255) NOT NULL COMMENT 'Nome del corso usato in questo sistema (es. "PREPOSTO")',
  `id_piattaforma` varchar(100) DEFAULT NULL COMMENT 'ID corso sulla piattaforma di sicurezza (fornito dal dev della piattaforma)',
  `id_aggiornamento` varchar(100) DEFAULT NULL COMMENT 'ID aggiornamento/rinnovo corso sulla piattaforma',
  `anni_validita` int(11) DEFAULT 5 COMMENT 'Anni di validità dell attestato',
  `attivo` tinyint(1) DEFAULT 1 COMMENT '1 = corso attivo nel sistema'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci COMMENT='Catalogo corsi con mapping agli ID della piattaforma esterna';

--
-- Dump dei dati per la tabella `sic_corsi_piattaforma`
--

INSERT INTO `sic_corsi_piattaforma` (`id`, `nome_locale`, `id_piattaforma`, `id_aggiornamento`, `anni_validita`, `attivo`) VALUES
(1, 'FORMAZIONE GENERALE + SPECIFICA (RISCHIO BASSO)', NULL, NULL, 5, 1),
(2, 'PREPOSTO', NULL, NULL, 5, 1),
(3, 'ADDETTO ANTINCENDIO', NULL, NULL, 3, 1),
(4, 'PRIMO SOCCORSO', NULL, NULL, 3, 1),
(5, 'RLS', NULL, NULL, 4, 1);

-- --------------------------------------------------------

--
-- Struttura della tabella `sic_durata_corsi`
--

DROP TABLE IF EXISTS `sic_durata_corsi`;
CREATE TABLE `sic_durata_corsi` (
  `id` int(11) NOT NULL,
  `corso` varchar(255) NOT NULL COMMENT 'Nome del corso (deve corrispondere esattamente a sic_attestati.nome_attestato)',
  `anni_validita` int(11) NOT NULL DEFAULT 5 COMMENT 'Anni di validità dell''attestato'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci COMMENT='Validità in anni per ogni tipologia di corso';

--
-- Dump dei dati per la tabella `sic_durata_corsi`
--

INSERT INTO `sic_durata_corsi` (`id`, `corso`, `anni_validita`) VALUES
(1, 'FORMAZIONE GENERALE + SPECIFICA (RISCHIO BASSO)', 5),
(2, 'PREPOSTO', 5),
(3, 'ADDETTO ANTINCENDIO', 3),
(4, 'PRIMO SOCCORSO', 3),
(5, 'RLS', 4);

-- --------------------------------------------------------

--
-- Struttura della tabella `sic_log`
--

DROP TABLE IF EXISTS `sic_log`;
CREATE TABLE `sic_log` (
  `id` int(11) NOT NULL,
  `tipo` varchar(50) NOT NULL COMMENT 'sync | polling | email | registrazione | errore',
  `messaggio` text NOT NULL COMMENT 'Dettaglio operazione',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci COMMENT='Log operazioni automatiche del modulo sicurezza';

--
-- Dump dei dati per la tabella `sic_log`
--

INSERT INTO `sic_log` (`id`, `tipo`, `messaggio`, `created_at`) VALUES
(1, 'sync', 'Sync completato: 77 dipendenti salvati, 36 negozi variati, 1 dipendenti esclusi.', '2026-03-24 15:31:45'),
(2, 'sync', 'Sync completato: 77 dipendenti salvati, 0 negozi variati, 1 dipendenti esclusi.', '2026-03-27 14:35:55'),
(3, 'sync', 'Sync completato: 70 dipendenti salvati, 17 negozi variati, 1 dipendenti esclusi.', '2026-03-30 08:40:51'),
(4, 'sync', 'Sync completato: 70 dipendenti salvati, 0 negozi variati, 1 dipendenti esclusi.', '2026-03-30 08:44:51'),
(5, 'sync', 'Sync completato: 70 dipendenti salvati, 0 negozi variati, 1 dipendenti esclusi.', '2026-03-30 08:46:18'),
(6, 'sync', 'Sync completato: 70 dipendenti salvati, 34 negozi variati, 1 dipendenti esclusi.', '2026-03-30 08:50:03'),
(7, 'sync', 'Sync completato: 70 dipendenti salvati, 0 negozi variati, 1 dipendenti esclusi.', '2026-03-30 08:50:35'),
(8, 'sync', 'Sync completato: 70 dipendenti salvati, 34 negozi variati, 1 dipendenti esclusi.', '2026-03-30 09:29:44'),
(9, 'sync', 'Sync completato: 70 dipendenti salvati, 0 negozi variati, 1 dipendenti esclusi.', '2026-03-30 09:29:48'),
(10, 'sync', 'Sync completato: 70 dipendenti salvati, 0 negozi variati, 1 dipendenti esclusi.', '2026-03-30 09:31:02');

-- --------------------------------------------------------

--
-- Struttura della tabella `sic_regole_attestati`
--

DROP TABLE IF EXISTS `sic_regole_attestati`;
CREATE TABLE `sic_regole_attestati` (
  `id` int(11) NOT NULL,
  `inquadramento` varchar(255) NOT NULL COMMENT 'Qualifica a cui si applica la regola',
  `nome_ruolo` varchar(255) NOT NULL COMMENT 'Nome del corso/certificazione richiesta',
  `fabbisogno` varchar(50) DEFAULT '1' COMMENT '"TUTTI" = tutta la qualifica | numero = quanti ne bastano per negozio',
  `id_corso_piattaforma` int(11) DEFAULT NULL COMMENT 'FK a sic_corsi_piattaforma.id — usato per il polling automatico'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci COMMENT='Regole di copertura attestati per inquadramento';

--
-- Dump dei dati per la tabella `sic_regole_attestati`
--

INSERT INTO `sic_regole_attestati` (`id`, `inquadramento`, `nome_ruolo`, `fabbisogno`, `id_corso_piattaforma`) VALUES
(1, 'ADDETTO ALLE VENDITE', 'FORMAZIONE GENERALE + SPECIFICA (RISCHIO BASSO)', 'TUTTI', 1),
(2, 'STORE MANAGER', 'FORMAZIONE GENERALE + SPECIFICA (RISCHIO BASSO)', 'TUTTI', 1),
(3, 'STORE MANAGER', 'PREPOSTO', '1', 2),
(4, 'STORE MANAGER', 'ADDETTO ANTINCENDIO', '1', 3),
(5, 'STORE MANAGER', 'PRIMO SOCCORSO', '1', 4);

-- --------------------------------------------------------

--
-- Struttura della tabella `sic_regole_eccezioni`
--

DROP TABLE IF EXISTS `sic_regole_eccezioni`;
CREATE TABLE `sic_regole_eccezioni` (
  `id` int(11) NOT NULL,
  `nome_negozio` varchar(255) NOT NULL COMMENT 'Negozio a cui si applica l''eccezione',
  `inquadramento` varchar(255) NOT NULL COMMENT 'Inquadramento coinvolto',
  `nome_ruolo` varchar(255) NOT NULL COMMENT 'Corso/ruolo coinvolto',
  `tipo_eccezione` varchar(50) NOT NULL COMMENT '"AGGIUNGI" o "ESCLUDI"'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci COMMENT='Eccezioni per negozi specifici alle regole standard';

--
-- Indici per le tabelle scaricate
--

--
-- Indici per le tabelle `sic_attestati`
--
ALTER TABLE `sic_attestati`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_attestato` (`cognome_nome`,`sede_assunzione`,`nome_attestato`) USING HASH;

--
-- Indici per le tabelle `sic_config`
--
ALTER TABLE `sic_config`
  ADD PRIMARY KEY (`chiave`);

--
-- Indici per le tabelle `sic_corsi_piattaforma`
--
ALTER TABLE `sic_corsi_piattaforma`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_nome_locale` (`nome_locale`) USING HASH;

--
-- Indici per le tabelle `sic_durata_corsi`
--
ALTER TABLE `sic_durata_corsi`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `corso` (`corso`) USING HASH;

--
-- Indici per le tabelle `sic_log`
--
ALTER TABLE `sic_log`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `sic_regole_attestati`
--
ALTER TABLE `sic_regole_attestati`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_inq_ruolo` (`inquadramento`,`nome_ruolo`) USING HASH;

--
-- Indici per le tabelle `sic_regole_eccezioni`
--
ALTER TABLE `sic_regole_eccezioni`
  ADD PRIMARY KEY (`id`),
  ADD KEY `nome_negozio` (`nome_negozio`(250));

--
-- AUTO_INCREMENT per le tabelle scaricate
--

--
-- AUTO_INCREMENT per la tabella `sic_attestati`
--
ALTER TABLE `sic_attestati`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `sic_corsi_piattaforma`
--
ALTER TABLE `sic_corsi_piattaforma`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT per la tabella `sic_durata_corsi`
--
ALTER TABLE `sic_durata_corsi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT per la tabella `sic_log`
--
ALTER TABLE `sic_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT per la tabella `sic_regole_attestati`
--
ALTER TABLE `sic_regole_attestati`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT per la tabella `sic_regole_eccezioni`
--
ALTER TABLE `sic_regole_eccezioni`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
