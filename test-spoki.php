<?php

include './library/connessione.php';
include './library/controllo.php';
include './library/config.php';
include './library/functions.php';
include './library/basic.class.php';
include './library/calendario.class.php';

$endpoint = URL_SPOKI . 'contacts/sync/';
$arrRequest = Array(
    'phone' => '+393883639940',
    'first_name' => 'Massimo',
    'last_name' => 'Anceschi',
    'email' => 'max@clicom.it',
    'language' => 'it',
    'custom_fields' => Array(
        'CITTA' => 'Parma',
        'COMPLEANNO' => '1977-01-19',
        'DATA' => '2024-12-07',
        'ORA' => '2024-12-07 14:00:00',
        'DATA_E_ORA' => '2024-12-07 14:00:00'
    )
);
$request = json_encode($arrRequest, JSON_INVALID_UTF8_IGNORE);
//echo "$request<br>";
$response = callApi($endpoint, $request);
//die("R: $response");
$json = json_decode($response, false);
var_dump($json);
if ($json->id) {
    $arrRequest = Array(
        'secret' => SECRET_AUTO_CONF_APP,
        'phone' => '+393883639940',
        'first_name' => 'Massimo',
        'last_name' => 'Anceschi',
        'email' => 'max@clicom.it',
        'language' => 'it',
        'custom_fields' => Array(
            'CITTA' => 'Parma',
            'COMPLEANNO' => '1977-01-19',
            'DATA' => '2024-12-07',
            'ORA' => '2024-12-07 14:00:00',
            'DATA_E_ORA' => '2024-12-07 14:00:00'
        )
    );
    $request = json_encode($arrRequest, JSON_INVALID_UTF8_IGNORE);
    $endpoint = URL_AUTO_CONF_APP;
    $response = callApi($endpoint, $request);
    echo "R: $response";
}
