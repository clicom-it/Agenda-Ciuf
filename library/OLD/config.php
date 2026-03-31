<?php
ini_set('error_reporting', E_ERROR);
// settare a on in caso di sviluppo
ini_set('display_errors', 'Off');
date_default_timezone_set('Europe/Rome');
$cms = "MrGest";
$slogan = "Gestionale Online";
$version = "V 1.5";

$sito = "MrGest";
// usato per i prodotti e per e-commerce
$sitocomunicazioni = "www.clicom.it";
$mailcomunicazioni = "info@clicom.it";
$firma = "Clicom";
// usato per cambio password o assistenza
$noreply = "noreply@clicom.it";
$adminmail = "info@clicom.it";
define('MAIL_HOST', 'mail.clicom.it');
define('MAIL_USER', 'noreply@clicom.it');
define('MAIL_PWD', 'clicom210317');
define('MAIL_FROM', 'Clicom');
define('MAIL_ADMIN', 'info@clicom.it');
define('RMA_CODE', 10);
# EMAIL AUTO #
define('EMAIL_CONFERMA', 1);
define('EMAIL_TRACKING', 2);
define('EMAIL_FIRMA', 3);

$arrmesiesteso = array(
    "1" => "Gennaio",
    "2" => "Febbraio",
    "3" => "Marzo",
    "4" => "Aprile",
    "5" => "Maggio",
    "6" => "Giugno",
    "7" => "Luglio",
    "8" => "Agosto",
    "9" => "Settembre",
    "10" => "Ottobre",
    "11" => "Novembre",
    "12" => "Dicembre"
);

$arrmesi = array(
    "1" => "Gen",
    "2" => "Feb",
    "3" => "Mar",
    "4" => "Apr",
    "5" => "Mag",
    "6" => "Giu",
    "7" => "Lug",
    "8" => "Ago",
    "9" => "Set",
    "10" => "Ott",
    "11" => "Nov",
    "12" => "Dic"
);


$arrgiorni = array(
    "0" => "Domenica",
    "1" => "Lunedì",
    "2" => "Martedì",
    "3" => "Mercoledì",
    "4" => "Giovedì",
    "5" => "Venerdì",
    "6" => "Sabato",
);
$arrgiorniRid = array(
    "0" => "D",
    "1" => "L",
    "2" => "M",
    "3" => "M",
    "4" => "G",
    "5" => "V",
    "6" => "S",
);

define("BASEURL", "https://app.esendex.it/API/v1.0/REST/");

define("MESSAGE_HIGH_QUALITY", "N");
define("MESSAGE_MEDIUM_QUALITY", "L");
define("MESSAGE_LOW_QUALITY", "LL");
//define('URL_WS', 'http://agenda.comeinunafavola/ws/services/');
define('URL_WS', 'https://agenda.comeinunafavola.it/ws/services/');
define('USER_WS', 'ciuf_usr');
define('PWD_WS', '#Ciuf2117!');
define('FOLDER_FORMAZIONE', 'formazione');
define('FOLDER_TMP', 'tmp');
define('FOLDER_UTENTI', 'utenti');
define('ADDETTO_VENDITE', 0);
define('STORE_MANAGER', 1);
define('DISTRICT_MANAGER', 2);
define('CENTRALINO', 9);
define('RISORSE_UMANE', 7);
define('FRANCHISING', 12);
define('URL_SITO', 'https://www.comeinunafavola.it');
define('API_SPOKI', 'e7f9273488654584b1409c5fdc1d0d1d');
define('URL_SPOKI', 'https://app.spoki.it/api/1/');
define('URL_AUTO_CONF_APP', 'https://app.spoki.it/wh/ap/b637dd7f-edd6-4581-a938-504354097f23/');
define('SECRET_AUTO_CONF_APP', '81315f7fd32c44ff806bc175405a73b8');
define('URL_AUTO_CONF_MODIFICA_APP', 'https://app.spoki.it/wh/ap/27f4287c-d4a3-4e37-8878-b4320ea26452/');
define('SECRET_AUTO_CONF_MODIFICA_APP', 'ab7aa43393434799a34c916f3e75f1ac');
define('URL_AUTO_REMEMBER_APP_DONNA2', 'https://app.spoki.it/wh/ap/beab7228-66b5-4bea-9da8-4a21a57324e0/');
define('SECRET_AUTO_REMEMBER_APP_DONNA2', 'b96cf7c14e12404d972835a728c74927');
define('URL_AUTO_REMEMBER_APP_UOMO2', 'https://app.spoki.it/wh/ap/2547b267-ab03-4b17-86d2-ac22ff24c4f3/');
define('SECRET_AUTO_REMEMBER_APP_UOMO2', '6adc9fd0754f4faa99258f7361e5adce');
define('URL_AUTO_REMEMBER_APP_DONNA5', 'https://app.spoki.it/wh/ap/1ca71bdb-8c89-489b-8a37-312f67f2b28d/');
define('SECRET_AUTO_REMEMBER_APP_DONNA5', '0c9b5db6c6a540f6aab599fce0f441e1');
define('URL_AUTO_REMEMBER_APP_DONNA20', 'https://app.spoki.it/wh/ap/6aea6248-72ab-42da-81db-77f15c24e12b/');
define('SECRET_AUTO_REMEMBER_APP_DONNA20', 'fb32e5e62d704a458b7bee12b1c6e984');
define('URL_AUTO_MODIFICA_APP', 'https://app.spoki.it/wh/ap/52471257-0e43-43c8-9054-88c29ca106dc/');
define('SECRET_AUTO_MODIFICA_APP', '2a3611f1d64a45778d2089a471ca5ce4');
define('URL_AUTO_CONF_APP_SALES10', 'https://app.spoki.it/wh/ap/f30ab80f-e236-4f2f-b4da-fbbf185d0923/');
define('SECRET_AUTO_CONF_APP_SALES10', '457b7ae2158d4d2eb5085ea858af102a');
define('URL_AUTO_APP_DISDETTO', 'https://app.spoki.it/wh/ap/23997964-0527-477d-9e61-6a8f3988cf97/');
define('SECRET_AUTO_APP_DISDETTO', '2a10e08d7b2641cc80ec33a947971ddb');
define('URL_AUTO_APP_ACQUISTATO_DONNA', 'https://app.spoki.it/wh/ap/ce45d007-53bd-4d7c-b964-6983b53eef2b/');
define('SECRET_AUTO_APP_ACQUISTATO_DONNA', '35ad300c210b4191a41b0bd334ea4344');
define('URL_AUTO_APP_ACQUISTATO_UOMO', 'https://app.spoki.it/wh/ap/fb7d2c99-6019-419c-84fa-51787ade3077/');
define('SECRET_AUTO_APP_ACQUISTATO_UOMO', '94f9b4a9e5544092a2fbacc351ceb567');
define('URL_AUTO_APP_NON_ACQUISTATO', 'https://app.spoki.it/wh/ap/4ccd35cd-ac7a-4f8c-81b6-2f4f28497215/');
define('SECRET_AUTO_APP_NON_ACQUISTATO', '3aff3b638588473db858b08cfb26479e');
define('API_KEY_OPENAI', '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');
define('URL_WS_OPENAI', 'https://openai.comeinunafavola.it/services/');
