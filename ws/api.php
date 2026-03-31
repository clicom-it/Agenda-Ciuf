<?php

define('URL_WS', 'http://agenda.comeinunafavola/ws/services/');
define('USER_WS', 'ciuf_usr');
define('PWD_WS', '#Ciuf2117!');
//$data = "user=attver&pw=" . urlencode("@AtT1L@__2117#") . "&idddt=8594&img=".urlencode($base64);
//$data = "user=attver&pw=" . urlencode("@AtT1L@__2117#");
//$data = "user=attver&pw=" . urlencode("@AtT1L@__2117#") . "&idtecnico=7&data_dal=25/06/2018&data_al=28/06/2018";
//$data = "user=attver&pw=" . urlencode("@AtT1L@__2117#") . ""
//        . "&idtecnico=7&data=2017-07-05&id_cliente=41793&fare_preventivo=0&note_admin=prova"
//        . "&colli=1&peso=2&vettore=MITTENTE&porto=PORTO FRANCO&causale=VENDITA&aspetto=A VISTA&num_tecnici=1"
//        . "&secondo_tecnico=&id_int=5808&destinazione=";
//$data = "user=attver&pw=" . urlencode("@AtT1L@__2117#") . ""
//        . "&tipo=2&idtecnico=295&data=2017-07-05&id_cliente=41793&fare_preventivo=0&note_admin=prova"
//        . "&colli=&peso=&vettore=&porto=&causale=&aspetto=&num_tecnici=1"
//        . "&secondo_tecnico=&id_int=5739&destinazione=";
//$json_righe_int = '[{"id":"","idmacchina":"","id_voci_intervento":7898, "nome":"100-X20", "guasto":"guasto test 1", "intervento":"prova testo int"}]';
//$json_righe_ddt = '[{"idpezzo":"372920","codice":"222222", "descrizione": "RIMBORSO KM", "qta":"1", "um":"Km", "prezzo":"0.60"}, '
//        . '{"idpezzo":"17266","codice":"3221034", "descrizione": "LAMPADA SPIA VERDE 400V", "qta":"2", "um":"N", "prezzo":"4.20"}]';
//$data .= "&righe_int=$json_righe_int&righe_ddt=$json_righe_ddt&id_ddt=";
//die($data);
//die(URL_WS . "getCatalogo/");
//$ch = curl_init(URL_WS . "getClasseEnergetica/");
//$ch = curl_init(URL_WS . "getGiardino/");
//$ch = curl_init(URL_WS . "getTipologia/");
//$ch = curl_init(URL_WS . "getDestinazione/");
$ch = curl_init(URL_WS . "dipendentiSicurezza/");
$data = "user=" . USER_WS . "&pw=" . urlencode(PWD_WS) .
            "&limit=100000";
curl_setopt_array($ch, array(
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_RETURNTRANSFER => TRUE,
    CURLOPT_HTTPHEADER => array(
        //'Content-Type: application/json',
        'Access-Control-Allow-Origin: *',
        'Content-Length: ' . strlen($data),
        'Content-Type:application/x-www-form-urlencoded'
    ),
    CURLOPT_POSTFIELDS => $data
));
//echo $json_req;
// Send the request
$response = curl_exec($ch);
die($response);
?>