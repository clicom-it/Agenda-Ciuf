<?php

define('URL_WS', 'https://services.passepartout.cloud/webapi/risorse/');
define('DOMINIO', 'comeinunafavola');
define('USER_MEXAL', 'WEBAPP');
define('PWD_MEXAL', 'WEBAPP_2025');
define('AZIENDA', 'CIF');
//ob_start();
//$out = fopen('php://output', 'w');
//$ch = curl_init(URL_WS . "clienti?max=3");
//$ch = curl_init(URL_WS . "help?info=true");
//$ch = curl_init(URL_WS . "articoli?fields=codice,descrizione&max=10");
//$ch = curl_init(URL_WS . "articoli?max=3");
//$ch = curl_init(URL_WS . "alias-articoli?max=3");
//$ch = curl_init(URL_WS . "documenti/movimenti-magazzino?max=3");
$ch = curl_init(URL_WS . "prima-nota?max=3");
//$data = '{"username":"sysaid_ws_","password": "YDRkdea9#iRiBGaK"}';//sisal
//$data = '['
//        . '{"codice_mat": "111111", "um": "PZ.","descrizioni":['
//        . '{"lingua":"IT", "testo":"prova 1"},'
//        . '{"lingua":"EN", "testo":"test 1"}'
//        . '], "codici_cliente": ['
//        . '{"codice":"5214090", "prezzo":100.2, "divisa":"EUR", "unita_prezzo":"PZ"},'
//        . '{"codice":"5200928", "prezzo":97, "divisa":"EUR", "unita_prezzo":"PZ"},'
//        . '{"codice":"5212445", "prezzo":95, "divisa":"EUR", "unita_prezzo":"PZ"}'
//        . '], "codici_fornitore": ['
//        . '{"codice":"111762", "prezzo":90, "divisa":"EUR", "unita_prezzo":"PZ"},'
//        . '{"codice":"5207993", "prezzo":89, "divisa":"EUR", "unita_prezzo":"PZ"},'
//        . '{"codice":"109021", "prezzo":85, "divisa":"EUR", "unita_prezzo":"PZ"}'
//        . ']},'
//        . '{"codice_mat": 111112, "um": "PZ.","descrizioni":['
//        . '{"lingua":"IT", "testo":"prova 2"},'
//        . '{"lingua":"EN", "testo":"test 22"}'
//        . '], "codici_fornitore": ['
//        . '{"codice":"111762", "prezzo":8, "divisa":"EUR", "unita_prezzo":"PZ"},'
//        . '{"codice":"5207993", "prezzo":9, "divisa":"EUR", "unita_prezzo":"PZ"},'
//        . '{"codice":"109021", "prezzo":5, "divisa":"EUR", "unita_prezzo":"PZ"}'
//        . ']},'
//        . '{"codice_mat": 111113, "um": "PZ.","descrizioni":['
//        . '{"lingua":"IT", "testo":"prova 3"},'
//        . '{"lingua":"EN", "testo":"test 3"}'
//        . ']},'
//        . '{"codice_mat": 111114, "um": "PZ.","descrizioni":['
//        . '{"lingua":"IT", "testo":"prova 3"},'
//        . '{"lingua":"EN", "testo":"test 3"}'
//        . ']},'
//        . '{"codice_mat": 111115, "um": "PZ.","descrizioni":['
//        . '{"lingua":"IT", "testo":"prova 4"},'
//        . '{"lingua":"EN", "testo":"test 4"}'
//        . ']},'
//        . '{"codice_mat": 111116, "um": "N.","descrizioni":['
//        . '{"lingua":"IT", "testo":"prova 5"},'
//        . '{"lingua":"EN", "testo":"test 5"}'
//        . ']}'
//        . ']';
//$data = '['
//        . '{'
//        . '"codice":"2117", "rag_soc_inv":"Clicom di Moriconi Nico & C. Snc", "address_inv":"via Roberto Rossellini 17", "cap_inv":"42049", "location_inv":"Sant\'Ilario d\'Enza", "stato_inv":"IT", "org_acquisti":"Z000111", '
//        . '"vat_inv":"02524780356", "vat_inv2":"02524780356", "vat_inv_int":"IT02524780356", "cond_pag_code":"B30FM", "cond_pag_it":"bonifico", "cond_pag_en":"bank transfert"'
//        . '}'
//        . ']';
//die($data);
$token = base64_encode(USER_MEXAL . ':' . PWD_MEXAL);
curl_setopt_array($ch, array(
    CURLOPT_CUSTOMREQUEST => 'GET',
    CURLOPT_RETURNTRANSFER => TRUE,
    CURLOPT_HTTPHEADER => array(
        'Content-Type: application/json',
        'Coordinate-Gestionale: Azienda=' . AZIENDA . ' Anno=2024',
        'Content-Length: ' . strlen($data),
        'Authorization: Passepartout ' . $token . ' Dominio=' . DOMINIO,
    //'Content-Type:application/x-www-form-urlencoded'
    ),
    CURLINFO_HEADER_OUT => true,
    CURLOPT_VERBOSE => true,
    //CURLOPT_STDERR => $out,
    //CURLOPT_POSTFIELDS => $data
));
//echo $json_req;
// Send the request
$response = curl_exec($ch);
//$info = curl_getinfo($ch);
//$output = [];
//$output['result'] = $response;
//$output['info'] = $info;
//curl_close($ch);
//fclose($out);
//$output['debug'] = ob_get_clean();
//print_r($output);
header('Content-Type: application/json; charset=utf-8');
die($response);
?>