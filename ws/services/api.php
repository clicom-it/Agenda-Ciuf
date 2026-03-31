<?php

include $_SERVER['DOCUMENT_ROOT'] . '/library/config.php';
include $_SERVER['DOCUMENT_ROOT'] . '/library/connessione.php';
include $_SERVER['DOCUMENT_ROOT'] . '/library/functions.php';

require_once("Rest.inc.php");

class API extends REST {

    public $data = "";

    const DB_SERVER = "localhost";
    const DB_USER = "";
    const DB_PASSWORD = "";
    const DB = "";
    const USER_LOGIN = "ciuf_usr";
    const PW_LOGIN = "#Ciuf2117!";

    private $db = NULL;
    private $mysqli = NULL;

    public function __construct() {
        global $db;
        parent::__construct();    // Init parent contructor
        $this->dbConnect($db);     // Initiate Database connection
    }

    /*
     *  Connect to Database
     */

    private function dbConnect($db_pdo) {
        //$this->mysqli = new mysqli(self::DB_SERVER, self::DB_USER, self::DB_PASSWORD, self::DB);
        $this->mysqli = $db_pdo;
    }

    /*
     * Dynmically call the method based on the query string
     */

    public function processApi() {
        if ($this->_request['user'] == self::USER_LOGIN && $this->_request['pw'] == self::PW_LOGIN) {
            $func = strtolower(trim(str_replace("/", "", $_REQUEST['x'])));
            if ((int) method_exists($this, $func) > 0)
                $this->$func();
            else
                $this->response('', 404); // If the method not exist with in this class "Page not found".
        } else {
            $this->response('', 404);
        }
    }

    private function getStore() {
        if ($this->get_request_method() != "POST") {
            $this->response('', 406);
        }
        $comune = stripslashes($this->_request['comune']);
        $db = $this->mysqli;
        $qry = "SELECT id,comune,indirizzo,cap,email,nominativo, patrono,aperture,"
                . "`inizio0`, `inizio1`, `inizio2`, `inizio3`, `inizio4`, `inizio5`, `inizio6`, `fine0`, `fine1`, `fine2`, `fine3`, `fine4`, `fine5`, `fine6`, "
                . "`iniziop0`, `iniziop1`, `iniziop2`, `iniziop3`, `iniziop4`, `iniziop5`, `iniziop6`, `finep0`, `finep1`, `finep2`, `finep3`, `finep4`, `finep5`, `finep6`, "
                . "data_apertura, aperture_spot, chiusure_spot, chiuso_dal, chiuso_al, date_trunk, titolo_trunk, orari_trunk, cellulare, telefono, non_gestito, "
                . "(select provincia from province where sigla=u.provincia limit 1) as provincia, "
                . "(select regione from province where sigla=u.provincia limit 1) as regione "
                . "FROM utenti u WHERE attivo = ? and idatelier=0 and livello='5' and online=1 " . ($comune != "" ? " having provincia=? or comune=?" : " GROUP BY comune") . " order by regione, provincia;";
        //$this->response($this->json(Array($qry, $comune)), 200);
        $sql = $db->prepare($qry);
        try {
            $valori = Array("1");
            if ($comune != "") {
                $valori[] = $comune;
                $valori[] = $comune;
            }
            $sql->execute($valori);
            $res = $sql->fetchAll(PDO::FETCH_ASSOC);
            $this->response($this->json($res), 200);
        } catch (PDOException $e) {
            die('Connessione fallita: ' . $e->getMessage());
        }
    }

    private function getStoreAll() {
        if ($this->get_request_method() != "POST") {
            $this->response('', 406);
        }
        $db = $this->mysqli;
        $year = date('Y');
        $month = date('m');
        
        $qry = "SELECT id,comune,indirizzo,cap,email,nominativo, patrono,"
                . "`inizio0`, `inizio1`, `inizio2`, `inizio3`, `inizio4`, `inizio5`, `inizio6`, `fine0`, `fine1`, `fine2`, `fine3`, `fine4`, `fine5`, `fine6`, "
                . "`iniziop0`, `iniziop1`, `iniziop2`, `iniziop3`, `iniziop4`, `iniziop5`, `iniziop6`, `finep0`, `finep1`, `finep2`, `finep3`, `finep4`, `finep5`, `finep6`, "
                . "data_apertura, aperture_spot, chiusure_spot, chiuso_dal, chiuso_al, date_trunk, titolo_trunk, orari_trunk, cellulare, telefono, non_gestito, "
                . "(select provincia from province where sigla=u.provincia limit 1) as provincia, "
                . "(select regione from province where sigla=u.provincia limit 1) as regione,"
                . "(select email from utenti u2 where livello='3' and id=(select districtM_id from store_budget_targets s left join budget_periods b on s.period_id=b.id where s.store_id=u.id and b.year=$year and b.month=$month limit 1)) as email_district "
                . "FROM utenti u WHERE attivo = ? and idatelier=0 and livello='5' and online=1 order by nominativo;";
        //$this->response($this->json(Array($qry)), 200);
        $sql = $db->prepare($qry);
        try {
            $valori = Array("1");
            $sql->execute($valori);
            $res = $sql->fetchAll(PDO::FETCH_ASSOC);
            $this->response($this->json($res), 200);
        } catch (PDOException $e) {
            die('Connessione fallita: ' . $e->getMessage());
        }
    }

    private function book() {
        if ($this->get_request_method() != "POST") {
            $this->response('', 406);
        }
        $db = $this->mysqli;
        $idstore = $this->_request['idstore'];
        $tipo = $this->_request['tipo'];
        $data_app = $this->_request['data_app'];
        list($g, $m, $a) = explode("/", $data_app);
        $data_app_db = "$a-$m-$g";
        $time_app = $this->_request['time_app'];
        $nome = $this->_request['nome'];
        $cognome = $this->_request['cognome'];
        $email = $this->_request['email'];
        $telefono = $this->_request['telefono'];
        $note = $this->_request['messaggio'];
        $data_matrimonio = $this->_request['data_matrimonio'];
        if ($data_matrimonio != "") {
            list($g, $m, $a) = explode("/", $data_matrimonio);
            $data_matrimonio_db = "$a-$m-$g";
        } else {
            $data_matrimonio_db = '';
        }
        $qry = "select id from clienti_fornitori where email=? and tipo='1' and idatelier=? limit 1;";
        $rs = $db->prepare($qry);
        $rs->execute(Array($email, $idstore));
        if ($rs->RowCount() > 0) {
            $idcliente = $rs->fetchColumn();
        } else {
            $qry = "insert into clienti_fornitori (tipo, nome, cognome, email, telefono, idatelier) values (?,?,?,?,?,?);";
            $rs = $db->prepare($qry);
            $valori = Array('1', $nome, $cognome, $email, $telefono, $idstore);
            $rs->execute($valori);
            $idcliente = $db->lastinsertId();
        }
        if ($idcliente != "" && $idstore != "") {
            $qry = "select * from utenti where id=? limit 1;";
            $rs = $db->prepare($qry);
            $rs->execute(Array($idstore));
            $emailatelier = '';
            if ($rs->RowCount() > 0) {
                $atelier = $rs->fetch(PDO::FETCH_ASSOC);
                $emailatelier = $atelier['email'];
            }
            $qry = "select id from calendario where nome=? and cognome=? and email=? and idatelier=? limit 1;";
            $rs = $db->prepare($qry);
            $rs->execute(Array($nome, $cognome, $email, $idstore));
            if ($rs->RowCount() > 0) {
                $result = Array('error' => 1);
            } else {
                $qry = "insert into calendario (cliente, data, orario, idcliente, idatelier, nome, cognome, telefono, email, provenienza, emailatelier, note, online, tipoappuntamento, datamatrimonio, data_insert) values "
                        . "(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);";
                $rs = $db->prepare($qry);
                $valori = Array($nome . ' ' . $cognome, $data_app_db, $time_app, $idcliente, $idstore, $nome, $cognome, $telefono, $email, 'Sito web', $emailatelier, $note, 1, $tipo, $data_matrimonio_db, date('Y-m-d'));
                $rs->execute($valori);
                $idapp = $db->lastinsertId();
                if ($idapp != "" && $idapp != "0") {
                    $result = Array('error' => '', 'success' => 1);
                } else {
                    $result = Array('error' => 1);
                }
                #SPOKI
                $phone = (strpos($telefono, '+39') === false ? '+39' : '') . $telefono;
                $first_name = $nome;
                $last_name = $cognome;
                $language = 'it';
                $citta = $atelier['nominativo'];
                $compleanno = '';
                $data_app = $data_app_db;
                $ora_app = $time_app;
                $idutente = $idcliente;
                $endpoint = URL_SPOKI . 'contacts/sync/';
                $link_modifica = '/' . $language . '/appuntamento/' . md5('ciuf' . $idapp) . '/';
                $arrRequest = Array(
                    'phone' => $phone,
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                    'email' => $email,
                    'language' => $language,
                    'custom_fields' => Array(
                        'CITTA' => $citta,
                        'COMPLEANNO' => $compleanno,
                        'DATA' => $data_app,
                        'ORA' => $ora_app,
                        'DATA_E_ORA' => $data_app . ' ' . $ora_app,
                        'MODIFICA_APPUNTAMENTO' => $link_modifica
                    )
                );
                $request = json_encode($arrRequest, JSON_INVALID_UTF8_IGNORE);
//echo "$request<br>";
                $response = callApi($endpoint, $request);
                logSpoki($request, $response);
//die("R: $response");
                $json = json_decode($response, false);
                //var_dump($json);
                if ($json->id) {
                    $qry = "insert into utenti_spoki (idutente, idspoki) values (?,?);";
                    $rs = $db->prepare($qry);
                    $valori = Array($idutente, $json->id);
                    $rs->execute($valori);
                    $arrRequest = Array(
                        'secret' => SECRET_AUTO_CONF_APP,
                        'phone' => $phone,
                        'first_name' => $first_name,
                        'last_name' => $last_name,
                        'email' => $email,
                        'language' => $language,
                        'custom_fields' => Array(
                            'CITTA' => $citta,
                            'COMPLEANNO' => $compleanno,
                            'DATA' => $data_app,
                            'ORA' => $ora_app,
                            'DATA_E_ORA' => $data_app . ' ' . $ora_app
                        )
                    );
                    $request = json_encode($arrRequest, JSON_INVALID_UTF8_IGNORE);
                    $endpoint = URL_AUTO_CONF_APP;
                    $response = callApi($endpoint, $request);
                    logSpoki($request, $response);
                    $json = json_decode($response, false);
                    if ($json->accepted) {
                        //$result = Array('error' => '', 'success' => 1);
                    } else {
                        //$result = Array('error' => '', 'success' => 0);
                    }
                } else {
                    //$result = Array('error' => '', 'success' => 0);
                }
            }
        } else {
            $result = Array('error' => 1);
        }
        $this->response($this->json($result), 200);
    }

    private function getOrariTrunk() {
        if ($this->get_request_method() != "POST") {
            $this->response('', 406);
        }
        $db = $this->mysqli;
        $idstore = $this->_request['idstore'];
        $data_app = $this->_request['data_app'];
        $day = $this->_request['day'];
        $durata = 60;
        $check_difference_h = 1;
        $check_difference_min = 0;
        list($g, $m, $a) = explode("/", $data_app);
        $data_app_db = "$a-$m-$g";
        $qry = "select * from utenti where id=? limit 1;";
        $rs = $db->prepare($qry);
        $rs->execute(Array($idstore));
        $store = $rs->fetch(PDO::FETCH_ASSOC);
        $arrOrari = Array();
        //$this->response($this->json(array($store)), 200);
        if ($store['orari_trunk'] != "") {
            list($ora_start, $ora_end) = explode('-', $store['orari_trunk']);
            if (strpos($ora_start, ':') === false) {
                $ora_start .= ':00';
            }
            if (strpos($ora_end, ':') === false) {
                $ora_end .= ':00';
            }
            $dateTimeObject1 = new DateTime($data_app_db . ' ' . $ora_end . ':00');
            $start = $ora_start;
            while ($start <= $ora_end) {
                $dateTimeObject2 = new DateTime($data_app_db . ' ' . $start . ':00');
                $difference = $dateTimeObject1->diff($dateTimeObject2);
                if ($difference->h > $check_difference_h || ($difference->h == $check_difference_h && $difference->i >= $check_difference_min)) {
                    $arrOrari[] = $start;
                }
                $dataObg = new DateTime("$data_app_db $start");
                $dataObg->modify('+' . $durata . ' minutes');
                $start = $dataObg->format('H:i');
            }
        }
        //$this->response($this->json(array($arrOrari)), 200);
        $result = Array();
        $addetti_agg = $store['app_trunk'];
        foreach ($arrOrari as $ora_start) {
            $qry = "select COUNT(id) from calendario where idatelier=$idstore and data='$data_app_db' and tipoappuntamento=9 and (orario = '$ora_start:00' or "
                    . "(orario > '$ora_start:00' and orario < DATE_FORMAT(DATE_ADD('$data_app_db $ora_start:00', INTERVAL $durata MINUTE), '%H:%i:%s')) or "
                    . "('$ora_start:00' > orario and '$ora_start:00' < "
                    . "IF(tipoappuntamento=1, DATE_FORMAT(DATE_ADD(CONCAT('$data_app_db ', orario), INTERVAL 90 MINUTE), '%H:%i:%s'), "
                    . "IF(tipoappuntamento=6 , DATE_FORMAT(DATE_ADD(CONCAT('$data_app_db ', orario), INTERVAL 150 MINUTE), '%H:%i:%s'), DATE_FORMAT(DATE_ADD(CONCAT('$data_app_db ', orario), INTERVAL 60 MINUTE), '%H:%i:%s')))"
                    . ")"
                    . ");";
            //$this->response($this->json(array($qry)), 200);
            //die($qry);
            $rs2 = $db->prepare($qry);
            $rs2->execute();
            $num_app = $rs2->fetchColumn();
            $result[] = Array('orario' => $ora_start, 'num_app' => $num_app, 'addetti' => (int) $addetti_agg);
        }
        //var_dump($arrOrari);
        $this->response($this->json($result), 200);
    }

    private function getOrari() {
        if ($this->get_request_method() != "POST") {
            $this->response('', 406);
        }
        $db = $this->mysqli;
        $idstore = $this->_request['idstore'];
        $data_app = $this->_request['data_app'];
        $day = $this->_request['day'];
        $tipo = (int) $this->_request['tipo'];
        $arrVillage = Array(152, 234, 284, 207, 91);
        switch ($tipo) {
            case 1:
                if ($idstore == 140) {//BO  e PG
                    $durata = 120;
                    $check_difference_h = 2;
                    $check_difference_min = 0;
                } else {
                    $durata = 90;
                    $check_difference_h = 1;
                    $check_difference_min = 30;
                }
                break;

            case 6:
                if ($idstore == 140) {
                    $durata = 180;
                    $check_difference_h = 3;
                    $check_difference_min = 0;
                } else {
                    $durata = 150;
                    $check_difference_h = 2;
                    $check_difference_min = 30;
                }
                break;

            default :
                $durata = 60;
                $check_difference_h = 1;
                $check_difference_min = 0;
                break;
        }
        //$this->response($this->json(Array("$idstore, $data_app, $day")), 200);
        if ($data_app == '29/11/2024' && $idstore != 75) {
            if ($tipo == 1) {//sposa
                $durata = 45;
            } elseif ($tipo == 2) {
                $durata = 30;
            }
        }
        if (($data_app == '01/02/2025' || $data_app == '02/02/2025') && $idstore == 313) {
            if ($tipo == 1) {//sposa
                $durata = 60;
            } elseif ($tipo == 2) {
                $durata = 60;
            }
        }
        list($g, $m, $a) = explode("/", $data_app);
        $data_app_db = "$a-$m-$g";
        $qry = "select * from utenti where id=? limit 1;";
        $rs = $db->prepare($qry);
        $rs->execute(Array($idstore));
        $store = $rs->fetch(PDO::FETCH_ASSOC);
        $qry = "select * from atelier_addetti where idatelier=? limit 1;";
        $rs = $db->prepare($qry);
        $valori = Array($idstore);
        $rs->execute($valori);
        $col = $rs->fetch(PDO::FETCH_ASSOC);
        for ($i = 0; $i <= 6; $i++) {
            $store['addetti' . $i] = $col['addetti' . $i];
        }
        $arrOrari = Array();
//        if ($idstore == 149 && $data_app == '22/10/2023') {
//            $store['inizio' . $day] = '09:00';
//            $store['finep' . $day] = '19:00';
//        }
        if ($idstore == 97 && ($data_app == '27/12/2024' || $data_app == '28/12/2024')) {
            $store['finep' . $day] = '15:00';
        }
        if ($idstore == 140 && $data_app == '03/01/2025') {
            $store['finep' . $day] = '14:00';
        }
        if ($idstore == 90 && $data_app == '03/01/2025') {
            $store['finep' . $day] = '16:00';
        }
        if ($store['inizio' . $day] != "" && $store['fine' . $day] != "" && $store['iniziop' . $day] != "" && $store['finep' . $day] != "") {
            $start = $store['inizio' . $day];
            $dateTimeObject1 = new DateTime(date('Y-m-d') . ' ' . $store['fine' . $day] . ':00');
            while ($start <= $store['fine' . $day]) {
                $dateTimeObject2 = new DateTime(date('Y-m-d') . ' ' . $start . ':00');
                $difference = $dateTimeObject1->diff($dateTimeObject2);
                if ($difference->h > $check_difference_h || ($difference->h == $check_difference_h && $difference->i >= $check_difference_min)) {
                    $arrOrari[] = $start;
                }
                $dataObg = new DateTime("$data_app_db $start");
                $dataObg->modify('+' . $durata . ' minutes');
                $start = $dataObg->format('H:i');
            }
            $start = $store['iniziop' . $day];
            $dateTimeObject1 = new DateTime(date('Y-m-d') . ' ' . $store['finep' . $day] . ':00');
            while ($start <= $store['finep' . $day]) {
                $dateTimeObject2 = new DateTime(date('Y-m-d') . ' ' . $start . ':00');
                $difference = $dateTimeObject1->diff($dateTimeObject2);
                if ($difference->h > $check_difference_h || ($difference->h == $check_difference_h && $difference->i >= $check_difference_min)) {
                    $arrOrari[] = $start;
                }
                $dataObg = new DateTime("$data_app_db $start");
                $dataObg->modify('+' . $durata . ' minutes');
                $start = $dataObg->format('H:i');
            }
        } elseif ($store['inizio' . $day] != "" && $store['finep' . $day] != "") {
            $start = $store['inizio' . $day];
            $dateTimeObject1 = new DateTime(date('Y-m-d') . ' ' . $store['finep' . $day] . ':00');
            while ($start <= $store['finep' . $day]) {
                $dateTimeObject2 = new DateTime(date('Y-m-d') . ' ' . $start . ':00');
                $difference = $dateTimeObject1->diff($dateTimeObject2);
                if ($difference->h > $check_difference_h || ($difference->h == $check_difference_h && $difference->i >= $check_difference_min)) {
                    $arrOrari[] = $start;
                }
                $dataObg = new DateTime("$data_app_db $start");
                $dataObg->modify('+' . $durata . ' minutes');
                $start = $dataObg->format('H:i');
            }
        } elseif ($store['inizio' . $day] != "" && $store['fine' . $day] != "") {
            $start = $store['inizio' . $day];
            $dateTimeObject1 = new DateTime(date('Y-m-d') . ' ' . $store['fine' . $day] . ':00');
            while ($start <= $store['fine' . $day]) {
                $dateTimeObject2 = new DateTime(date('Y-m-d') . ' ' . $start . ':00');
                $difference = $dateTimeObject1->diff($dateTimeObject2);
                if ($difference->h > $check_difference_h || ($difference->h == $check_difference_h && $difference->i >= $check_difference_min)) {
                    $arrOrari[] = $start;
                }
                $dataObg = new DateTime("$data_app_db $start");
                $dataObg->modify('+' . $durata . ' minutes');
                $start = $dataObg->format('H:i');
            }
        } elseif ($store['iniziop' . $day] != "" && $store['finep' . $day] != "") {
            $start = $store['iniziop' . $day];
            $dateTimeObject1 = new DateTime(date('Y-m-d') . ' ' . $store['finep' . $day] . ':00');
            while ($start <= $store['finep' . $day]) {
                $dateTimeObject2 = new DateTime(date('Y-m-d') . ' ' . $start . ':00');
                $difference = $dateTimeObject1->diff($dateTimeObject2);
                if ($difference->h > $check_difference_h || ($difference->h == $check_difference_h && $difference->i >= $check_difference_min)) {
                    $arrOrari[] = $start;
                }
                $dataObg = new DateTime("$data_app_db $start");
                $dataObg->modify('+' . $durata . ' minutes');
                $start = $dataObg->format('H:i');
            }
        }
//        if ($idstore == 77) {//VERONA
//            foreach ($arrOrari as $i => $ora_start) {
//                if ($ora_start > '16:00') {
//                    unset($arrOrari[$i]);
//                }
//            }
//        }
//        if ($idstore == 126) {//PESCARA
//            foreach ($arrOrari as $i => $ora_start) {
//                if ($ora_start > '18:00') {
//                    unset($arrOrari[$i]);
//                }
//            }
//        }
//        if ($idstore == 73) {//VERONA
//            foreach ($arrOrari as $i => $ora_start) {
//                if ($ora_start > '16:30') {
//                    unset($arrOrari[$i]);
//                }
//            }
//        }
        if (in_array($idstore, $arrVillage)) {
            $last = count($arrOrari) - 1;
            $arrFine = explode(":", $store['finep' . $day]);
            $ora_ultimo_app = intval($arrFine[0] - 1) . ":00";
            if ($arrOrari[$last] != $ora_ultimo_app) {
                $arrOrari[] = $ora_ultimo_app;
            }
        }
        $result = Array();
        foreach ($arrOrari as $ora_start) {
            $qry = "select addetti from addetti_atelier where idatelier=$idstore and data_cal='$data_app_db' and "
                    . "('$ora_start' >= ora_da and ora_a >= DATE_FORMAT(DATE_ADD('$data_app_db $ora_start', INTERVAL $durata MINUTE), '%H:%i:%s'));";
            //$this->response($this->json(Array($qry)), 200);
            $rs2 = $db->prepare($qry);
            $rs2->execute();
            $addetti_agg = 0;
            while ($col2 = $rs2->fetch(PDO::FETCH_ASSOC)) {
                $addetti_agg += $col2['addetti'];
            }
            if ($idstore == 82) {//napoli
                //$addetti_agg = 1000;
            }
            if ($data_app == '02/02/2025' && $idstore == 313) {
                //$addetti_agg = 1000;
            }

//            if ($idstore == 65 && $day == 6) {//milano sabato
//                $addetti_agg += 1;
//            }
            $qry = "select id, tipoappuntamento from calendario where idatelier=$idstore and data='$data_app_db' and disdetto=0 and (orario = '$ora_start:00' or "
                    . "(orario > '$ora_start:00' and orario < DATE_FORMAT(DATE_ADD('$data_app_db $ora_start:00', INTERVAL $durata MINUTE), '%H:%i:%s')) or "
                    . "('$ora_start:00' > orario and '$ora_start:00' < "
                    . "IF(tipoappuntamento=1, DATE_FORMAT(DATE_ADD(CONCAT('$data_app_db ', orario), INTERVAL 90 MINUTE), '%H:%i:%s'), "
                    . "IF(tipoappuntamento=6 , DATE_FORMAT(DATE_ADD(CONCAT('$data_app_db ', orario), INTERVAL 150 MINUTE), '%H:%i:%s'), DATE_FORMAT(DATE_ADD(CONCAT('$data_app_db ', orario), INTERVAL 60 MINUTE), '%H:%i:%s')))"
                    . ")"
                    . ");";
            //$this->response($this->json(array($qry)), 200);
            //die($qry);
            $rs2 = $db->prepare($qry);
            $rs2->execute();
            $num_app = 0;
            while ($col2 = $rs2->fetch(PDO::FETCH_ASSOC)) {
                if ($col2['tipoappuntamento'] == 6) {
                    $num_app += 2;
                } else {
                    $num_app++;
                }
            }
            //$num_app = $rs2->fetchColumn();
            if ($store['addetti' . $day] > 0) {
                $addetti = $store['addetti' . $day];
            } else {
                $addetti = $store['addetti'];
            }
            $result[] = Array('orario' => $ora_start, 'num_app' => $num_app, 'addetti' => (int) $addetti + $addetti_agg);
        }
        //var_dump($arrOrari);
        $this->response($this->json($result), 200);
    }

    private function sendSms() {
        if ($this->get_request_method() != "POST") {
            $this->response('', 406);
        }
        $db = $this->mysqli;
        $usersms = 'goldoni@comeinunafavola.it';
        $passwordsms = 'Esendex2024!';
        $sendersms = "ComeinunaFa";
        $messaggiosms = $this->_request['messaggio'];
        $telefonosms = $this->_request['telefono'];
        if (strpos($telefonosms, '+39') === false) {
            $telefonosms = "+39" . $telefonosms;
        }
        $auth = loginSMS($usersms, $passwordsms);
        $smsSent = sendSMSnew($auth, array(
            "message" => $messaggiosms,
            "message_type" => MESSAGE_HIGH_QUALITY,
            "returnCredits" => false,
            "recipient" => array($telefonosms),
            "sender" => $sendersms     // Place here a custom sender if desired
        ));

        if ($smsSent->result == "OK") {
            $result = Array('error' => '', 'success' => 1);
        } else {
            $result = Array('error' => '', 'success' => 0);
        }
        $this->response($this->json($result), 200);
    }

    private function insertContattoSPoki() {
        if ($this->get_request_method() != "POST") {
            $this->response('', 406);
        }
        $now = date('Y-m-d');
        $time_now = date('H:i');
        $db = $this->mysqli;
        $phone = $this->_request['phone'];
        $first_name = $this->_request['first_name'];
        $last_name = $this->_request['last_name'];
        $email = $this->_request['email'];
        $language = $this->_request['language'];
        $citta = $this->_request['citta'];
        $compleanno = $this->_request['compleanno'];
        $data_app = $this->_request['data'];
        $ora_app = $this->_request['ora'];
        $idutente = $this->_request['idutente'];
        $idapp = $this->_request['idapp'];
        $link_modifica = '/' . $language . '/appuntamento/' . md5('ciuf' . $idapp) . '/';
        $endpoint = URL_SPOKI . 'contacts/sync/';
        $arrRequest = Array(
            'phone' => $phone,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'email' => $email,
            'language' => $language,
            'custom_fields' => Array(
                'CITTA' => $citta,
                'COMPLEANNO' => $compleanno,
                'DATA' => $data_app,
                'ORA' => $ora_app,
                'DATA_E_ORA' => $data_app . ' ' . $ora_app,
                'MODIFICA_APPUNTAMENTO' => $link_modifica
            )
        );
        $request = json_encode($arrRequest, JSON_INVALID_UTF8_IGNORE);
//echo "$request<br>";
        $response = callApi($endpoint, $request);
        logSpoki($request, $response);
//die("R: $response");
        $json = json_decode($response, false);
        //var_dump($json);
        if ($json->id) {
            $qry = "insert into utenti_spoki (idutente, idspoki) values (?,?);";
            $rs = $db->prepare($qry);
            $valori = Array($idutente, $json->id);
            $rs->execute($valori);
            if ($data_app > $now || ($data_app == $now && $ora_app > $time_now)) {
                $arrRequest = Array(
                    'secret' => SECRET_AUTO_CONF_APP,
                    'phone' => $phone,
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                    'email' => $email,
                    'language' => $language,
                    'custom_fields' => Array(
                        'CITTA' => $citta,
                        'COMPLEANNO' => $compleanno,
                        'DATA' => $data_app,
                        'ORA' => $ora_app,
                        'DATA_E_ORA' => $data_app . ' ' . $ora_app
                    )
                );
                $request = json_encode($arrRequest, JSON_INVALID_UTF8_IGNORE);
                $endpoint = URL_AUTO_CONF_APP;
                $response = callApi($endpoint, $request);
                logSpoki($request, $response);
                $json = json_decode($response, false);
                if ($json->accepted) {
                    $result = Array('error' => '', 'success' => 1);
                } else {
                    $result = Array('error' => '', 'success' => 0);
                }
            }
        } else {
            $result = Array('error' => '', 'success' => 0);
        }
        $this->response($this->json($result), 200);
    }

    private function updateContattoSPoki() {
        if ($this->get_request_method() != "POST") {
            $this->response('', 406);
        }
        $db = $this->mysqli;
        $phone = $this->_request['phone'];
        $first_name = $this->_request['first_name'];
        $last_name = $this->_request['last_name'];
        $email = $this->_request['email'];
        $language = $this->_request['language'];
        $citta = $this->_request['citta'];
        $compleanno = $this->_request['compleanno'];
        $data_app = $this->_request['data'];
        $ora_app = $this->_request['ora'];
        $campi_agg = $this->_request['campi_agg'];
        $disdetto = $this->_request['disdetto'];
        $idapp = $this->_request['idapp'];
        $now = date('Y-m-d');
        $time_now = date('H:i');
        $endpoint = URL_SPOKI . 'contacts/sync/';
        $data_app_db = formatDateDb($data_app);
        $arrRequest = Array(
            'phone' => $phone,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'email' => $email,
            'language' => $language,
            'custom_fields' => Array(
                'CITTA' => $citta,
                'COMPLEANNO' => $compleanno,
                'DATA' => $data_app,
                'ORA' => $ora_app,
                'DATA_E_ORA' => $data_app . ' ' . $ora_app,
                'DISDETTO' => ($disdetto == 1 ? 'SI' : 'NO')
            )
        );
        $request = json_encode($arrRequest, JSON_INVALID_UTF8_IGNORE);
//echo "$request<br>";
        $response = callApi($endpoint, $request);
        logSpoki($request, $response);
//die("R: $response");
        $json = json_decode($response, false);
        //var_dump($json);
        if ($json->id && count($campi_agg) > 0) {
            if ($data_app > $now || ($data_app == $now && $ora_app > $time_now)) {
                if ($disdetto == 0) {
                    $link_modifica = '/' . $language . '/appuntamento/' . md5('ciuf' . $idapp) . '/';
                    $arrRequest = Array(
                        'secret' => SECRET_AUTO_CONF_MODIFICA_APP,
                        'phone' => $phone,
                        'first_name' => $first_name,
                        'last_name' => $last_name,
                        'email' => $email,
                        'language' => $language,
                        'custom_fields' => Array(
                            'CITTA' => $citta,
                            'COMPLEANNO' => $compleanno,
                            'DATA' => $data_app,
                            'ORA' => $ora_app,
                            'DATA_E_ORA' => $data_app . ' ' . $ora_app,
                            'MODIFICA_APPUNTAMENTO' => $link_modifica
                        )
                    );
                    $request = json_encode($arrRequest, JSON_INVALID_UTF8_IGNORE);
                    $endpoint = URL_AUTO_CONF_MODIFICA_APP;
                    $response = callApi($endpoint, $request);
                    logSpoki($request, $response);
                    $json = json_decode($response, false);
                    if ($json->accepted) {
                        $result = Array('error' => '', 'success' => 1);
                    } else {
                        $result = Array('error' => '', 'success' => 0);
                    }
                } elseif ($disdetto == 1) {

                    $result = Array('error' => '', 'success' => 1);
                } else {
                    $result = Array('error' => '', 'success' => 0);
                }
            }
        } else {
            $result = Array('error' => '', 'success' => 0);
        }
        $this->response($this->json($result), 200);
    }

    private function rememberAppuntamentoSPoki() {
        if ($this->get_request_method() != "POST") {
            $this->response('', 406);
        }
        $arrUomo = Array(2, 4);
        $arrDonna = Array(1, 3);
        $db = $this->mysqli;
        $phone = $this->_request['phone'];
        $first_name = $this->_request['first_name'];
        $last_name = $this->_request['last_name'];
        $email = $this->_request['email'];
        $language = $this->_request['language'];
        $citta = $this->_request['citta'];
        $compleanno = $this->_request['compleanno'];
        $data_app = $this->_request['data'];
        $ora_app = $this->_request['ora'];
        $giorni = $this->_request['giorni'];
        $tipo = $this->_request['tipo'];
        if (in_array($tipo, $arrDonna)) {
            switch ($giorni) {
                case 2:
                    $endpoint = URL_AUTO_REMEMBER_APP_DONNA2;
                    $secret = SECRET_AUTO_REMEMBER_APP_DONNA2;
                    break;

                case 5:
                    $endpoint = URL_AUTO_REMEMBER_APP_DONNA5;
                    $secret = SECRET_AUTO_REMEMBER_APP_DONNA5;
                    break;

                case 20:
                    $endpoint = URL_AUTO_REMEMBER_APP_DONNA20;
                    $secret = SECRET_AUTO_REMEMBER_APP_DONNA20;
                    break;
            }
        } elseif (in_array($tipo, $arrUomo)) {
            switch ($giorni) {
                case 2:
                    $endpoint = URL_AUTO_REMEMBER_APP_UOMO2;
                    $secret = SECRET_AUTO_REMEMBER_APP_UOMO2;
                    break;
            }
        }
        //$this->response($this->json(Array($endpoint, $secret, $giorni, $tipo)), 200);
        if ($endpoint != '' && $secret != '') {
            $arrRequest = Array(
                'secret' => $secret,
                'phone' => $phone,
                'first_name' => $first_name,
                'last_name' => $last_name,
                'email' => $email,
                'language' => $language,
                'custom_fields' => Array(
                    'CITTA' => $citta,
                    'COMPLEANNO' => $compleanno,
                    'DATA' => $data_app,
                    'ORA' => $ora_app,
                    'DATA_E_ORA' => $data_app . ' ' . $ora_app
                )
            );
            $request = json_encode($arrRequest, JSON_INVALID_UTF8_IGNORE);
            $response = callApi($endpoint, $request);
            logSpoki($request, $response);
            $json = json_decode($response, false);
            if ($json->accepted) {
                $result = Array('error' => '', 'success' => 1);
            } else {
                $result = Array('error' => '', 'success' => 0);
            }
        } else {
            $result = Array('error' => '', 'success' => 0);
        }
        $this->response($this->json($result), 200);
    }

    private function postAppuntamentoSPoki() {
        if ($this->get_request_method() != "POST") {
            $this->response('', 406);
        }
        $arrUomo = Array(2, 4);
        $arrDonna = Array(1, 3);
        $db = $this->mysqli;
        $phone = $this->_request['phone'];
        $first_name = $this->_request['first_name'];
        $last_name = $this->_request['last_name'];
        $email = $this->_request['email'];
        $language = $this->_request['language'];
        $citta = $this->_request['citta'];
        $compleanno = $this->_request['compleanno'];
        $data_app = $this->_request['data'];
        $ora_app = $this->_request['ora'];
        $giorni = $this->_request['giorni'];
        $tipo = $this->_request['tipo'];
        $action = $this->_request['action'];
        $citta_alternative = $this->_request['citta_alternative'];
        switch ($action) {
            case 'disdetto':
                $endpoint = URL_AUTO_APP_DISDETTO;
                $secret = SECRET_AUTO_APP_DISDETTO;
                break;

            case 'acquistato':
                if (in_array($tipo, $arrDonna)) {
                    $endpoint = URL_AUTO_APP_ACQUISTATO_DONNA;
                    $secret = SECRET_AUTO_APP_ACQUISTATO_DONNA;
                } else {
                    $endpoint = URL_AUTO_APP_ACQUISTATO_UOMO;
                    $secret = SECRET_AUTO_APP_ACQUISTATO_UOMO;
                }
                break;

            case 'non_acquistato':
                $endpoint = URL_AUTO_APP_NON_ACQUISTATO;
                $secret = SECRET_AUTO_APP_NON_ACQUISTATO;
                break;
        }
        //$this->response($this->json(Array($endpoint, $secret, $giorni, $tipo)), 200);
        if ($endpoint != '' && $secret != '') {
            $arrRequest = Array(
                'secret' => $secret,
                'phone' => $phone,
                'first_name' => $first_name,
                'last_name' => $last_name,
                'email' => $email,
                'language' => $language,
                'custom_fields' => Array(
                    'CITTA' => $citta,
                    'COMPLEANNO' => $compleanno,
                    'DATA' => $data_app,
                    'ORA' => $ora_app,
                    'DATA_E_ORA' => $data_app . ' ' . $ora_app,
                    'CITTA_ALTERNATIVE' => $citta_alternative
                )
            );
            $request = json_encode($arrRequest, JSON_INVALID_UTF8_IGNORE);
            $response = callApi($endpoint, $request);
            logSpoki($request, $response);
            $json = json_decode($response, false);
            if ($json->accepted) {
                $result = Array('error' => '', 'success' => 1);
            } else {
                $result = Array('error' => '', 'success' => 0);
            }
        } else {
            $result = Array('error' => '', 'success' => 0);
        }
        $this->response($this->json($result), 200);
    }

    private function modificaAppuntamentoSPoki() {
        if ($this->get_request_method() != "POST") {
            $this->response('', 406);
        }
        $db = $this->mysqli;
        $phone = $this->_request['phone'];
        $first_name = $this->_request['first_name'];
        $last_name = $this->_request['last_name'];
        $email = $this->_request['email'];
        $language = $this->_request['language'];
        $citta = $this->_request['citta'];
        $compleanno = $this->_request['compleanno'];
        $data_app = $this->_request['data'];
        $ora_app = $this->_request['ora'];
        $idapp = $this->_request['idapp'];
        $link_modifica = '/' . $language . '/appuntamento/' . md5('ciuf' . $idapp) . '/';
        $now = date('Y-m-d');
        $time_now = date('H:i');
        if ($data_app > $now || ($data_app == $now && $ora_app > $time_now)) {
            $arrRequest = Array(
                'secret' => SECRET_AUTO_MODIFICA_APP,
                'phone' => $phone,
                'first_name' => $first_name,
                'last_name' => $last_name,
                'email' => $email,
                'language' => $language,
                'custom_fields' => Array(
                    'CITTA' => $citta,
                    'COMPLEANNO' => $compleanno,
                    'DATA' => $data_app,
                    'ORA' => $ora_app,
                    'DATA_E_ORA' => $data_app . ' ' . $ora_app,
                    'MODIFICA_APPUNTAMENTO' => $link_modifica
                )
            );
            $request = json_encode($arrRequest, JSON_INVALID_UTF8_IGNORE);
            $endpoint = URL_AUTO_MODIFICA_APP;
            $response = callApi($endpoint, $request);
            logSpoki($request, $response);
            $json = json_decode($response, false);
            if ($json->accepted) {
                $result = Array('error' => '', 'success' => 1);
            } else {
                $result = Array('error' => '', 'success' => 0);
            }
        } else {
            $result = Array('error' => '', 'success' => 0);
        }
        $this->response($this->json($result), 200);
    }

    private function getAppuntamento() {
        if ($this->get_request_method() != "POST") {
            $this->response('', 406);
        }
        $db = $this->mysqli;
        $now = date('Y-m-d');
        $app = $this->_request['app'];
        $qry = "select *"
                . " from calendario c inner join calendario_spoki cs on cs.idcalendario=c.id where MD5(CONCAT('ciuf',id))=? and data > ? and disdetto=? and acquistato!=?  limit 1;";
        $rs = $db->prepare($qry);
        $valori = Array($app, $now, 0, '1');
        $rs->execute($valori);
        if ($rs->RowCount() > 0) {
            $cal = $rs->fetch(PDO::FETCH_ASSOC);
            $result = Array('error' => '', 'app' => $cal);
        } else {
            $result = Array('error' => 'Appuntamento non trovato o non piu\' modificabile');
        }
        $this->response($this->json($result), 200);
    }

    private function updateAppuntamento() {
        include_once $_SERVER['DOCUMENT_ROOT'] . '/library/phpmailer/PHPMailerAutoload.php';
        if ($this->get_request_method() != "POST") {
            $this->response('', 406);
        }
        $db = $this->mysqli;
        $idcal = $this->_request['idcal'];
        $dati = getDati('calendario', 'where id=' . $idcal . ' limit 1;');
        $app_old = $dati[0];
        $idatelier = $this->_request['idatelier'];
        $data = $this->_request['data'];
        $orario = $this->_request['orario'];
        $qry = "update calendario set idatelier=?, data=?, orario=? where id=?;";
        $rs = $db->prepare($qry);
        $valori = Array($idatelier, formatDateDb($data), $orario, $idcal);
        $rs->execute($valori);
        $qry = "update calendario_spoki set modificato_sale10=? where idcalendario=?;";
        $rs = $db->prepare($qry);
        $valori = Array(1, $idcal);
        $rs->execute($valori);
        $dati = getDati('calendario', 'where id=' . $idcal . ' limit 1;');
        $app = $dati[0];
        $dati = getDati('utenti', 'where id=' . $idatelier . ' limit 1;');
        $atelier = $dati[0];
        $phone = $app['telefono'];
        $first_name = $app['nome'];
        $last_name = $app['cognome'];
        $email = $app['email'];
        $language = 'it';
        $citta = $atelier['nominativo'];
        $email_atelier = $atelier['email'];
        $compleanno = '';
        $data_app = $app['data'];
        $ora_app = $app['orario'];
        $disdetto = $app['disdetto'];
        $endpoint = URL_SPOKI . 'contacts/sync/';
        $link_modifica = '/' . $language . '/appuntamento/' . md5('ciuf' . $idcal) . '/';
        $arrRequest = Array(
            'phone' => $phone,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'email' => $email,
            'language' => $language,
            'custom_fields' => Array(
                'CITTA' => $citta,
                'COMPLEANNO' => $compleanno,
                'DATA' => $data_app,
                'ORA' => $ora_app,
                'DATA_E_ORA' => $data_app . ' ' . $ora_app,
                'MODIFICA_APPUNTAMENTO' => $link_modifica
            )
        );
        $request = json_encode($arrRequest, JSON_INVALID_UTF8_IGNORE);
//echo "$request<br>";
        $response = callApi($endpoint, $request);
        logSpoki($request, $response);
//die("R: $response");
        $json = json_decode($response, false);
        //var_dump($json);
        if ($json->id) {
            if ($disdetto == 0) {
                $arrRequest = Array(
                    'secret' => SECRET_AUTO_CONF_APP_SALES10,
                    'phone' => $phone,
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                    'email' => $email,
                    'language' => $language,
                    'custom_fields' => Array(
                        'CITTA' => $citta,
                        'COMPLEANNO' => $compleanno,
                        'DATA' => $data_app,
                        'ORA' => $ora_app,
                        'DATA_E_ORA' => $data_app . ' ' . $ora_app
                    )
                );
                $request = json_encode($arrRequest, JSON_INVALID_UTF8_IGNORE);
                $endpoint = URL_AUTO_CONF_APP_SALES10;
                $response = callApi($endpoint, $request);
                logSpoki($request, $response);
                $json = json_decode($response, false);
                if ($json->accepted) {
                    //$result = Array('error' => '', 'success' => 1);
                } else {
                    //$result = Array('error' => '', 'success' => 0);
                }
                //$email_atelier = 'ancio17@gmail.com';
                if ($email_atelier != '') {
                    $subject = 'Modifica appuntamento online';
                    $message = 'Cliente: ' . $first_name . ' ' . $last_name . '<br>'
                            . 'Data / orario originali: ' . formatDate($app_old['data']) . ' ore ' . $app_old['orario'] . '<br>'
                            . 'Data / orario modificati: ' . formatDate($data_app) . ' ore ' . $ora_app . '<br><br>';
                    $error_mail = sendMail($email_atelier, $subject, $message, 'servizioclienti@comeinunafavola.it', 'SERVIZIO CLIENTI', '');
                    $error_mail = sendMail('servizioclienti@comeinunafavola.it', $subject, $message, 'noreply@comeinunafavola.it', 'SERVIZIO CLIENTI', '');
                }
            }
        } else {
            //$result = Array('error' => '', 'success' => 0);
        }
        $result = Array('error' => '', 'success' => 1);
        $this->response($this->json($result), 200);
    }
    
    
    private function getReviews() {
        if ($this->get_request_method() != "POST") {
            $this->response('', 406);
        }
        $db = $this->mysqli;
        $qry = "select reviewer_name, create_time,comment,"
                . "(select nominativo from utenti u where u.id=g.store_code limit 1) as citta "
                . " from gbp_reviews g where rating=? order by create_time desc limit 20;";
        //die($qry);
        $rs = $db->prepare($qry);
        $valori = Array(1);
        $rs->execute($valori);
        if ($rs->RowCount() > 0) {
            $cal = $rs->fetchAll(PDO::FETCH_ASSOC);
            $result = Array('error' => '', 'reviews' => $cal);
        } else {
            $result = Array('error' => 'Nessuna recensione');
        }
        $this->response($this->json($result), 200);
    }
    
    private function dipendentiSicurezza() {
        if ($this->get_request_method() != "POST") {
            $this->response('', 406);
        }
        $db = $this->mysqli;
        $qry = "select u.id, u.nome, u.cognome, u.email, u.sesso, u.data_nascita, u.comune_nascita as citta_nascita,u.codicefiscale as codice_fiscale, u.attivo, "
                . "(select nominativo from utenti u2 where u2.id=d.idatelier_sede limit 1) as sede_assunzione,"
                . "(select IF(diraff='d','diretto','indiretto') from utenti u2 where u2.id=d.idatelier_sede limit 1) as tipo_atelier,"
                . "(select valore from ruolo r where r.id=u.ruolo limit 1) as ruolo,"
                . "d.data_inizio as data_inizio_contratto,d.data_fine as data_fine_contratto"
                . " from utenti u LEFT JOIN utenti_dipendenti d ON u.id=d.idutente where livello='3' and attivo=1";
        //$this->response($this->json(Array($qry)), 200);
        $rs = $db->prepare($qry);
        $rs->execute();
        $result = $rs->fetchAll(PDO::FETCH_ASSOC);
        $this->response($this->json($result), 200);
    }
    /*
     * 	Encode array into JSON
     */

    private function json($data) {
        if (is_array($data)) {
            return json_encode($data, JSON_INVALID_UTF8_IGNORE);
        }
    }
}

// Initiiate Library
$api = new API;
$api->processApi();
?>