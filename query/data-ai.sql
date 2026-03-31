-- vendite
select id as idcalendario, idutente, idcliente, data as `data appuntamento`, 
orario as `ora appuntamento`, 
LOWER(nome) as `nome cliente`, 
LOWER(cognome) as `cognome cliente`, 
email as `e-mail cliente`, 
nometipoabito as `tipo di abito`, 
nomemodabito as `modello di abito`, sesso as `sesso del cliente`, 
CAST(totalespesa AS DECIMAL(10,2)) as `totale della vendita`, 
(select nominativo from utenti u where u.id=c.idatelier limit 1) as `nome atelier`, 
nomepagamentocaparra as `tipo di pagamento per la caparra`, 
nomepagamentosaldo as `tipo di pagamento per il saldo vendita`, 
CAST(prezzoabito AS DECIMAL(10,2)) as `prezzo abito`,
(select LOWER(CONCAT(nome, ' ',cognome)) from utenti u where u.id=c.idutente limit 1) as `nome del dipendente che ha servito il cliente`
 from calendario c where data between '2020-01-01' and '2025-12-31' and acquistato='1' and nomemodabito!='';
-- atelier
SELECT `id` as idatelier, 
`nominativo` as `nome atelier`, 
`email` as `e-mail atelier`, 
`codicefiscale` as `codice fiscale atelier`, 
`piva` as `partita iva atelier`, 
`telefono` as `telefono atelier`, 
`cellulare` as  `cellulare atelier`, 
`nazione` as `nazione atelier`, 
`regione` as `regione atelier`, 
`provincia` as `provincia atelier`, 
`comune` as `comune atelier`, 
`cap` as `cap atelier`, 
`indirizzo` as `indirizzo atelier` FROM `utenti` WHERE livello='5'
-- dipendenti
SELECT `id` as idutente, 
`nome` as `nome dipendente`,
`cognome` as `cognome dipendente`, 
(select valore from ruolo r where r.id=u.ruolo limit 1) as `ruolo del dipendente`, 
`email` as `e-mail del dipendente`, 
`codicefiscale` as `codice fiscale del dipendente`, 
`telefono` as `telefono del dipendente`, 
`cellulare` as `cellulare del dipendente`, 
`nazione` as `nazione del dipendente`, 
`regione` as `regione del dipendente`, 
`provincia` as `provincia del dipendente`, 
`comune` as `comune del dipendente`, 
`cap` as `cap del dipendente`, 
`indirizzo` as `indirizzo del dipendente`,
 `data_nascita` as `data di nascita del dipendente`, 
`iban` as `iban del dipendente`, 
`provincia_nascita` as `provincia di nascita del dipendente`, 
`comune_nascita` as `comunedi nascita del dipendente` FROM `utenti` u WHERE livello='3'
-- clienti
SELECT id as idcliente, 
LOWER(nome) as `nome cliente`, 
LOWER(cognome) as `cognome cliente`, 
email as `e-mail cliente`, 
telefono as `telefono del cliente`, 
cellulare as `cellulare del cliente`, 
(select nominativo from utenti u where u.id=cf.idatelier limit 1) as `nome atelier`, 
provincia as `provincia del cliente`, 
comune as `comune del cliente` 
FROM `clienti_fornitori` cf WHERE tipo='1'
-- disdetti
select id as idappuntamento_disdetto, idutente, idcliente, 
data as `data appuntamento disdetto`, 
orario as `ora appuntamento disdetto`, 
LOWER(nome) as `nome cliente`, 
LOWER(cognome) as `cognome cliente`, 
email as `e-mail cliente`, 
(select nome from motivonoacq m where m.id=c.idnoacquisto limit 1) as `motivo del non acquisto`, 
(select nominativo from utenti u where u.id=c.idatelier limit 1) as `nome atelier`,
(select LOWER(CONCAT(nome, ' ',cognome)) from utenti u where u.id=c.idutente limit 1) as `nome del dipendente` 
 from calendario c where data between '2020-01-01' and '2025-12-31' and acquistato='0' and idnoacquisto!='';

select DATE_FORMAT(data, '%d/%m/%Y') as data_appuntamento,nome,cognome,email, telefono, DATE_FORMAT(datamatrimonio, '%d/%m/%Y') as data_matrimonio, acquistato, disdetto from calendario where YEAR(data)=2024 and provenienza='Fiera';
select DATE_FORMAT(data, '%d/%m/%Y') as data_appuntamento,nome,cognome,email, telefono, DATE_FORMAT(datamatrimonio, '%d/%m/%Y') as data_matrimonio, acquistato, disdetto from calendario where (YEAR(datamatrimonio)=2025 or YEAR(datamatrimonio)=2026) and provenienza='Fiera';
select DATE_FORMAT(data, '%d/%m/%Y') as data_appuntamento,nome,cognome,email, telefono, provincia, DATE_FORMAT(datamatrimonio, '%d/%m/%Y') as data_matrimonio, acquistato, disdetto,(select nome from motivonoacq where id=c.idnoacquisto limit 1) as motivo from calendario c where 
(YEAR(datamatrimonio)=2025 or YEAR(datamatrimonio)=2026) and  provincia in ('PA', 'CT', 'SR', 'AG', 'CL', 'EN', 'RG', 'ME', 'TP') and disdetto=0 and acquistato=0 and data < NOW();