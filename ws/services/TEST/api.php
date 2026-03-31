<?php

include $_SERVER['DOCUMENT_ROOT'] . '/config/config.php';
include $_SERVER['DOCUMENT_ROOT'] . '/config/connessione_test.php';
//include $_SERVER['DOCUMENT_ROOT'] . '/config/connessione.php';
include $_SERVER['DOCUMENT_ROOT'] . '/functions/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . '/functions/functions.api.php';
include $_SERVER['DOCUMENT_ROOT'] . '/library/basic.class.php';
include $_SERVER['DOCUMENT_ROOT'] . '/library/ddt.class.php';
include $_SERVER['DOCUMENT_ROOT'] . '/library/interventi.class.php';

//define('URL_WS', 'http://wgest.attilio/ws/services/');
require_once("Rest.inc.php");

class API extends REST {

    public $data = "";

    const DB_SERVER = "localhost";
    const DB_USER = "";
    const DB_PASSWORD = "";
    const DB = "wgestatt_db";
    const USER_LOGIN = "attver";
    const PW_LOGIN = "@AtT1L@__2117#";

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

    private function login() {
        if ($this->get_request_method() != "POST") {
            $this->response('', 406);
        }
        $db = $this->mysqli;
        $user = $this->_request['username'];
        $password = $this->_request['password'];
        if (!empty($user) and ! empty($password)) {
            //die(OK);
            $query = "SELECT * FROM utenti WHERE user = '$user' AND pass = '" . md5($password) . "' LIMIT 1";
            $r = $this->mysqli->prepare($query);
            $r->execute() or die($this->mysqli->error . __LINE__);

            if ($r->RowCount() > 0) {
                $result = $r->fetch(PDO::FETCH_ASSOC);
                //magazzino
                $where = "where (posizione='" . $result['identificativo'] . "' and qta > 0) or no_giacenza=1";
                //die($where);
                $magazzino = new ddt($id, "magazzino");
                $stampamagazzino = $magazzino->showDDT($where);
                foreach ($stampamagazzino as $k => $v) {
                    foreach ($stampamagazzino[$k] as $i => $val) {
                        $stampamagazzino[$k][$i] = utf8_decode($val);
                    }
                }
                $result['magazzino'] = $stampamagazzino;
                //interventi
                $interventi = listaInterventi($result['id'], date("d/m/Y"));
                $result['interventi'] = $interventi;
                $this->response($this->json($result), 200);
            }
            $this->response('', 204); // If no records "No Content" status
        }

        $error = array('status' => "Failed", "msg" => "Invalid Email address or Password");
        $this->response($this->json($error), 400);
    }

    private function getInterventi() {
        if ($this->get_request_method() != "POST") {
            $this->response('', 406);
        }
        $db = $this->mysqli;
        $idtecnico = $this->_request['idtecnico'];
        $result = listaInterventi($idtecnico, $this->_request['data_dal'], $this->_request['data_al']);
        $this->response($this->json($result), 200);
    }

    private function getRegistri() {
        if ($this->get_request_method() != "POST") {
            $this->response('', 406);
        }
        $db = $this->mysqli;
        $idcliente = $this->_request['idcliente'];
        $result = listaRegistri($idcliente);
        $this->response($this->json($result), 200);
    }

    private function getClienti() {
        if ($this->get_request_method() != "POST") {
            $this->response('', 406);
        }
        $db = $this->mysqli;
        // clienti
        $where = "where tipo='1' and gruppo=''";
        $anagrafica = new ddt($id, "clienti_fornitori");
        $stampa = $anagrafica->showDDT($where);
        foreach ($stampa as $k => $v) {
            foreach ($stampa[$k] as $i => $val) {
                $stampa[$k][$i] = utf8_decode($val);
            }
        }
        $result['clienti'] = $stampa;
        $this->response($this->json($result), 200);
    }

    private function addDdt() {
        if ($this->get_request_method() != "POST") {
            $this->response('', 406);
        }
        $db = $this->mysqli;
        $tipo = $this->_request['tipo'];
        $idutente = $this->_request['idtecnico'];
        $arrayddt = json_decode($this->_request['righe_ddt']);
        $righe = json_decode($this->_request['righe_int']);
        $idddt = $this->_request['id_ddt'];
        $idcliente = $this->_request['id_cliente'];
        $qry = "select * from clienti_fornitori where id='$idcliente' limit 1;";
        $rs2 = $db->prepare($qry);
        $rs2->execute();
        $cliente = $rs2->fetch(PDO::FETCH_ASSOC);
        $indirizzo = $cliente['indirizzo'] . "\n" . $cliente['cap'] . " " . $cliente['localita'] . "(" . $cliente['provincia'] . ")";
        $dati_cliente = addslashes($cliente['azienda']) . "\n" . addslashes($indirizzo);
        if ((int) $cliente['idg'] > 0) {
            $qry = "select * from clienti_fornitori where id='{$cliente['idg']}' limit 1;";
            $rs2 = $db->prepare($qry);
            $rs2->execute();
            $col_cc = $rs2->fetch(PDO::FETCH_ASSOC);
            $cc = $cliente['idg'];
            $indirizzo = $col_cc['indirizzo'] . "\n" . $col_cc['cap'] . " " . $col_cc['localita'] . "(" . $col_cc['provincia'] . ")";
            $daticc = addslashes($col_cc['azienda']) . "\n" . addslashes($indirizzo);
        } else {
            $cc = 0;
            $daticc = '';
        }
        $destinazione = $this->_request['destinazione'];
        if ($destinazione == "")
            $destinazione = "Medesima";
        //controllo ore manodopera, viaggio e km
        if ($this->_request['ora_inizio'] != "" && $this->_request['ora_fine'] != "") {
            $data_iniziale = date("Y-m-d") . " " . $this->_request['ora_inizio'];
            $data_finale = date("Y-m-d") . " " . $this->_request['ora_fine'];
            $ore_man = round(delta_tempo($data_iniziale, $data_finale, 'h'), 2);
            $qry = "select * from magazzino where codice_interno='11111' limit 1;";
            $rs2 = $db->prepare($qry);
            $rs2->execute();
            $manodopera = $rs2->fetch(PDO::FETCH_ASSOC);
            $arrayddt[] = (object) Array("qta" => $ore_man, "idpezzo" => $manodopera['id'], "descrizione" => $manodopera['descrizione'],
                        "codice" => $manodopera['codice_interno'], "prezzo" => $manodopera['prezzo'], "um" => $manodopera['um']);
        }
        if ($this->_request['km'] != "" && $this->_request['km'] > 0) {
            $qry = "select * from magazzino where codice_interno='222222' limit 1;";
            $rs2 = $db->prepare($qry);
            $rs2->execute();
            $km_mag = $rs2->fetch(PDO::FETCH_ASSOC);
            $arrayddt[] = (object) Array("qta" => $this->_request['km'], "idpezzo" => $km_mag['id'], "descrizione" => $km_mag['descrizione'],
                        "codice" => $km_mag['codice_interno'], "prezzo" => $km_mag['prezzo'], "um" => $km_mag['um']);
        }
        if ($this->_request['ore_viaggio'] != "") {
            list($h, $m, $s) = explode(":", $this->_request['ore_viaggio']);
            $tot_min = $m + $h * 60;
            $ore_viaggio = round($tot_min / 60, 2);
            $qry = "select * from magazzino where codice_interno='VVV' limit 1;";
            $rs2 = $db->prepare($qry);
            $rs2->execute();
            $km_mag = $rs2->fetch(PDO::FETCH_ASSOC);
            $arrayddt[] = (object) Array("qta" => $ore_viaggio, "idpezzo" => $km_mag['id'], "descrizione" => $km_mag['descrizione'],
                        "codice" => $km_mag['codice_interno'], "prezzo" => $km_mag['prezzo'], "um" => $km_mag['um']);
        }
        $campi_post = Array("idutente", "data", "cc", "daticc", "tipo", "cliente", "daticliente", "destinazione", "fare_preventivo", "note_admin
", "colli", "peso", "vettore", "porto", "causale", "aspetto", "num_tecnici", "secondo_tecnico", "idint", "partenza", "ultima_modifica", "ora_inizio", "ora_fine", "km", "ore_viaggio");
        $valori_post = Array($idutente, $this->_request['data'], $cc, $daticc, $tipo, $idcliente, $dati_cliente, $destinazione, $this->_request['fare_preventivo'],
            $this->_request['note_admin'], $this->_request['colli'], $this->_request['peso'], $this->_request['vettore'], $this->_request['porto'], $this->_request['causale'],
            $this->_request['aspetto'], $this->_request['num_tecnici'], $this->_request['secondo_tecnico'], $this->_request['id_int'], date("Y-m-d H:i:s"), date("Y-m-d H:i:s"),
            $this->_request['ora_inizio'], $this->_request['ora_fine'], $this->_request['km'], $this->_request['ore_viaggio']);
        //var_dump($righe);
        //var_dump($arrayddt);
        //var_dump($this->_request);
        //var_dump($valori_post);
        //die();
        $result = addDDT($idddt, $valori_post, $campi_post, $righe, $tipo, $idutente, $arrayddt);
        $this->response($this->json($result), 200);
    }

    private function setFirma() {
        if ($this->get_request_method() != "POST") {
            $this->response('', 406);
        }
        $db = $this->mysqli;
        $idddt = $this->_request['idddt'];
        $img = $this->_request['img'];
        $result = setFirma($idddt, $img);
        $this->response($this->json($result), 200);
    }

    /*
     * 	Encode array into JSON
     */

    private function json($data) {
        if (is_array($data)) {
            return json_encode($data);
        }
    }

}

// Initiiate Library
$api = new API;
$api->processApi();
?>