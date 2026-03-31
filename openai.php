<?php

ob_start('ob_gzhandler');
include './library/connessione.php';
include './library/controllo.php';
include './library/config.php';
include './library/functions.php';
include './library/basic.class.php';
include './library/calendario.class.php';

//ini_set('display_errors', 'On');
$yourApiKey = API_KEY_OPENAI;
$op = "";
if (isset($_POST['op'])) {
    $op = $_POST['op'];
}
switch ($op) {
    case 'send_message':
        $first = $_POST['first'];
        $prompt = $_POST['prompt'];
        $assistant_id = $_SESSION['assistant_id'];
        $idutente = $_SESSION['id'];
        $endpoint = URL_WS_OPENAI . 'sendMessageOpenAI';
        $data = 'first=' . $first . '&prompt=' . $prompt . '&assistant_id=' . $assistant_id . '&idutente=' . $idutente;
        $response = callWS($endpoint, $data);
        //var_dump($endpoint, $data, $response);
        $json = json_decode($response);
        $text_response = $json->response;
        die('{"response": ' . json_encode($text_response, JSON_INVALID_UTF8_IGNORE) . '}');
        break;
}
?>