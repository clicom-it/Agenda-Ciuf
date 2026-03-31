<?php
ob_start('ob_gzhandler');
include './library/connessione.php';
include './library/controllo.php';
include './library/config.php';
include './library/functions.php';
include './library/basic.class.php';
/**/
/*
  cliente = 1
  fornitore = 2
 */
/**/
$op = "";
if (isset($_GET['op'])) {
    $op = $_GET['op'];
}

$submit = "";
if (isset($_POST['submit'])) {
    $submit = $_POST['submit'];
}
if (isset($_GET['submit'])) {
    $submit = $_GET['submit'];
}
switch ($submit) {

    case 'lista-premi':
        if ($_SESSION['livello'] <= 1) {
            require_once './library/spout-master/src/Spout/Autoloader/autoload.php';
            $month = $_GET['month'];
            $year = $_GET['year'];
            $qry = "select * from premi_operatori where month=? and year=? order by operator_name;";
            $rs = $db->prepare($qry);
            $valori = [$month, $year];
            $rs->execute($valori);
            $rows = Array();
            $riga1 = ['OPERATORE', 'SEDE ASSUNZIONE', 'FATTURATO', 'PREMIO BUDGET', 'PREMIO TARGET BUDGET', 'PREMIO GARA 1', 'PREMIO GARA 2', 'PREMIO GARA 3', 'PREMIO GARA 4', 'PREMIO GARA 5', 'PREMIO GARA 6', 'TOTALE'];
            $rows[] = $riga1;
            while ($col = $rs->fetch(PDO::FETCH_ASSOC)) {
                $qry = "select SUM(total_sold_amount) as fatturato,(select u.nominativo from utenti u inner join utenti_dipendenti ud on u.id=ud.idatelier_sede where idutente=" . $col['operator_code'] . " limit 1) as sede_assunzione from "
                        . "operator_month_kpis t1 where t1.operator_code=" . $col['operator_code'] . " and "
                        . "kpi_month=$month and kpi_year=$year and store_code in (select store_code from store_budget_targets where period_id=(select id from budget_periods where year=$year and month=$month limit 1)) group by operator_code limit 1;";
                //die($qry);
                $rs2 = $db->prepare($qry);
                $rs2->execute();
                $col2 = $rs2->fetch(PDO::FETCH_ASSOC);
                $fatturato = (float) $col2['fatturato'];
                $totale = $col['p_budget'] + $col['p_target'] + $col['p_gara1'] + $col['p_gara2'] + $col['p_gara3'] + $col['p_gara4'] + $col['p_gara5'] + $col['p_gara6'];
                $rows[] = [$col['operator_name'], $col2['sede_assunzione'], (float) $fatturato, (float) $col['p_budget'], (float) $col['p_target'], (float) $col['p_gara1'], (float) $col['p_gara2'], (float) $col['p_gara3'], (float) $col['p_gara4'], (float) $col['p_gara5'], (float) $col['p_gara6'], (float) $totale];
            }
            $path = 'tmp/reportPremi_' . $month . '_' . $year . '.xlsx';
            $writer = Box\Spout\Writer\Common\Creator\WriterEntityFactory::createXLSXWriter();
            $writer->openToFile($path);
            foreach ($rows as $row) {
                $rowFromValues = Box\Spout\Writer\Common\Creator\WriterEntityFactory::createRowFromArray($row);
                $writer->addRow($rowFromValues);
            }
            $writer->close();
            header('Content-Type: application/octet-stream');
            header("Content-Transfer-Encoding: Binary");
            header("Content-disposition: attachment; filename=\"reportPremi_" . $month . "_" . $year . ".xlsx\"");

            // Actual download.
            readfile($path);
            die();
        } else {
            die();
        }
        break;

    case 'import-google':
        if ($_SESSION['livello'] <= 1) {
            $ch = curl_init("https://api.comeinunafavola.it/cron/import-budget.php");
            //$ch = curl_init("http://api.comeinunafavola/cron/import-budget.php");
            $data = "";
            curl_setopt_array($ch, array(
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_RETURNTRANSFER => TRUE,
                CURLOPT_HTTPHEADER => array(
                    //'Content-Type: application/json',
                    'Access-Control-Allow-Origin: *',
                    'Content-Length: ' . strlen($data),
                    'Content-Type:application/x-www-form-urlencoded'
                ),
                CURLOPT_POSTFIELDS => $data
            ));
            $response = curl_exec($ch);
            die('{"error":"", "response":' . json_encode($response, JSON_INVALID_UTF8_IGNORE) . '}');
        } else {
            die();
        }
        break;

    case 'config_dmsm':

        break;

    default:

        break;
}
if ($op == 'config_dmsm') {
    $period_id = $_POST['period_id'];
    //echo "$period_id";
    if ($period_id != '') {
        $stores = $_POST['stores'];
        foreach ($stores as $store_id) {
            $dm_id = $_POST['dm_' . $store_id];
            $sm_id = $_POST['sm_' . $store_id];
            //echo "$dm_id $sm_id<br>";
            $qry = "update store_budget_targets set districtM_id=$dm_id, storeM_id=$sm_id where store_id=$store_id and period_id=$period_id;";
            //echo "$qry<br>";
            $db->exec($qry);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="it">
    <head>
        <?php include './components/header.php'; ?>
        <!-- header del modulo -->
        <script type="text/javascript" src="./js/functions-budget.js"></script>
        <script src="./js/Chart.js"></script>
        <?php if ($op) { ?>
            <link type="text/css" rel="stylesheet" href="./js/jsgrid-1.5.1/jsgrid.min.css" />
            <link type="text/css" rel="stylesheet" href="./js/jsgrid-1.5.1/jsgrid-theme.min.css" />
            <link type="text/css" href="./css/theme-app-jsgrid.css" rel="Stylesheet" />
            <script type="text/javascript" src="./js/jsgrid-1.5.1/jsgrid.min.js"></script>
            <script type="text/javascript" src="./js/jsgrid-1.5.1/i18n/jsgrid-it.js"></script>
            <?php
        }
        ?>

        <script type="text/javascript">
            $(document).ready(function () {
<?php
if ($op == "clienti") {
    ?>

    <?php
} else if ($op == "fornitori") {
    ?>

    <?php
}
?>
            });
        </script>
    </head>
    <body class="colormodulo">
        <?php include './components/top.php'; ?>
        <?php if (moduloattivo("budget") > 0 || $_SESSION['livello'] == 3 || $_SESSION['livello'] == 5 || $_SESSION['ruolo'] == RISORSE_UMANE) { ?>
            <div class="barra_submenu sizing">
                <li class="box_submenu sizing"><a href="/budget.php"><i class="fa fa-line-chart fa-lg" aria-hidden="true"></i> Budget, Premi, Gare e Classifiche</a></li>
    <!--                <li class="box_submenu sizing"><a href="/budget.php?op=gare"><i class="fa fa-bar-chart fa-lg" aria-hidden="true"></i> Gare e Classifiche</a></li>-->
                <?php if ($_SESSION['livello'] <= 1) { ?>
                    <li class="box_submenu sizing"><a href="/budget.php?op=config"><i class="fa fa-cogs fa-lg" aria-hidden="true"></i> Configurazione</a></li>
                    <li class="box_submenu sizing"><a href="/budget.php?op=config_dmsm"><i class="fa fa-cogs fa-lg" aria-hidden="true"></i> District Manager e Store Manager</a></li>
                <?php } ?>
    <!--<li class="box_submenu sizing"><a href="/clientifornitori.php?op=fornitori"><i class="fa fa-archive fa-lg" aria-hidden="true"></i> Anagrafica Fornitori</a></li>-->
            </div>
            <div class="content sizing">
                <div class="barra_op sizing">
                    <?php
                    if ($op == "config") {
                        echo "Configurazione";
                    } elseif ($op == "config_dmsm") {
                        echo "District Manager e Store Manager";
                    } else if ($op == "gare") {
                        echo "Gare e Classifiche";
                    } else {
                        echo "Budget, Premi, Gare e Classifiche";
                    }
                    ?>
                    <div id="messaggio" class="messaggiook">Operazione avvenuta con successo</div>
                </div>
                <div class="showcont sizing">                            
                    <?php
                    if ($op == 'config') {
                        $pdo = $db;
                        $year = date('Y');
                        $month = date('m');
                        $qry = "select id from budget_periods where year=? and month=? limit 1;";
                        $rs = $pdo->prepare($qry);
                        $valori = [$year, $month];
                        $rs->execute($valori);
                        $period_id = $rs->fetchColumn();
                        $CURRENCY = '€';
                        /* ============ PERIODI (ordinati) ============ */
                        $periods = $pdo->query("
  SELECT id, year, month, perc_budget
  FROM budget_periods
  ORDER BY year ASC, month ASC
")->fetchAll();

                        if (!$periods) {
                            echo 'Nessun periodo trovato. Assicurati di aver eseguito l’import.';
                        }

                        /* ============ PREMI per periodo/tier ============ */
                        $prizeStmt = $pdo->query("
  SELECT period_id, tier_number, prize_amount
  FROM budget_prizes
");
                        $prizes = [];
                        foreach ($prizeStmt as $row) {
                            $prizes[(int) $row['period_id']][(int) $row['tier_number']] = (float) $row['prize_amount'];
                        }

                        /* ============ NEGOZI + MANAGER ============ */
                        $stores = $pdo->query("
  SELECT
    s.id as store_id,
    s.nominativo as store_name
  FROM utenti s
  where attivo=1 and livello='5'
  and s.id in (select store_id from store_budget_targets)
  ORDER BY s.nominativo ASC")->fetchAll(PDO::FETCH_ASSOC);

                        /* ============ TARGETS (store,period,tier) ============ */
                        $targetStmt = $pdo->query("
  SELECT *,
  COALESCE((select cognome from utenti u where u.id=s.districtM_id limit 1),'') as cognome_district,
  COALESCE((select cognome from utenti u where u.id=s.storeM_id limit 1),'') as cognome_store
  FROM store_budget_targets s
");
                        $targets = $targetSD = [];
                        foreach ($targetStmt as $row) {
                            $targets[(int) $row['store_id']][(int) $row['period_id']][(int) $row['tier_number']] = (float) $row['target_value'];
                            $targetSD[(int) $row['store_id']][(int) $row['period_id']] = ['cognome_district' => $row['cognome_district'], 'cognome_store' => $row['cognome_store']];
                        }

                        //var_dump($targetSD[136][4]);
                        /* ============ HELPERS ============ */

                        function fmtMoney(?float $v, string $currency = '€'): string {
                            if ($v === null)
                                return '';
                            // Formato italiano: 1.234,56
                            return number_format($v, 2, ',', '.') . ' ' . $currency;
                        }

                        function monthItShort(int $m): string {
                            return ['Gen', 'Feb', 'Mar', 'Apr', 'Mag', 'Giu', 'Lug', 'Ago', 'Set', 'Ott', 'Nov', 'Dic'][$m - 1] ?? (string) $m;
                        }
                        ?>
                        <style>
                            :root{
                                --bg:#f6f8fb;
                                --card:#ffffff;
                                --muted:#5e6a7a;
                                --text:#0b1220;
                                --accent:#0b79d0;
                                --sticky:#ffffff;
                                --grid:#e6edf3;
                                --good:#1db954;
                                --warn:#ffb020;
                            }
                            *{
                                box-sizing:border-box
                            }

                            header{
                                padding:16px 20px;
                                position:sticky;
                                top:0;
                                z-index:10;
                                background: linear-gradient(180deg, rgba(255,255,255,.95), rgba(255,255,255,.85));
                                backdrop-filter: blur(6px);
                                border-bottom:1px solid #e6edf3;
                            }
                            h1{
                                margin:0;
                                font-size:18px;
                                letter-spacing:.2px;
                                color:var(--text);
                            }
                            .sub{
                                color:var(--muted);
                                font-size:12px;
                                margin-top:4px;
                            }
                            .wrap{
                                padding:18px;
                            }

                            .table-wrap{
                                border:1px solid #e6edf3;
                                border-radius:14px;
                                overflow:auto;
                                background:var(--card);
                                box-shadow: 0 6px 18px rgba(0,0,0,.06), inset 0 1px 0 rgba(255,255,255,.7);
                            }
                            table{
                                width:max-content;
                                border-collapse:separate;
                                border-spacing:0;
                                min-width:100%;
                            }
                            thead th{
                                position:sticky;
                                top:0;
                                z-index:2;
                                background:linear-gradient(180deg, var(--sticky), #f7f9fc);
                                color:var(--text);
                                border-bottom:1px solid #dbe3eb;
                                padding:10px 12px;
                                white-space:nowrap;
                                font-weight:600;
                            }
                            thead .subhead{
                                font-size:12px;
                                color:var(--muted);
                                font-weight:500;
                            }
                            tbody td{
                                border-bottom:1px solid #edf2f7;
                                padding:10px 12px;
                                white-space:nowrap;
                                color:var(--text);
                            }
                            tbody tr:nth-child(odd) td{
                                background:rgba(0,0,0,.02); /* leggero zebra */
                            }

                            .sticky{
                                position:sticky;
                                left:0;
                                z-index:1;
                                background:linear-gradient(90deg, var(--sticky), #f7f9fc);
                                border-right:1px solid #e6edf3;
                            }
                            .sticky-2{
                                left:118px;
                            }
                            .sticky-3{
                                left:248px;
                                width:228px;
                            }
                            .sticky-4{
                                left:477px;
                            }

                            .badge{
                                display:inline-block;
                                padding:2px 8px;
                                border-radius:999px;
                                font-size:12px;
                                background:#f0f6ff;
                                border:1px solid #cfe0ff;
                                color:#0b3a75;
                            }
                            .colgroup{
                                text-align:center;
                                border-left:1px solid #e6edf3;
                                border-right:1px solid #e6edf3;
                            }
                            .tier{
                                font-size:12px;
                                color:#6e7f93; /* tono più soft in chiaro */
                            }

                            .prize-row td{
                                position:sticky;
                                top:40px;
                                z-index:1; /* sotto header principale */
                                background:linear-gradient(180deg, #f9fbfe, #f7f9fc);
                                border-bottom:1px solid #e6edf3;
                                font-weight:600;
                                text-align:right;
                                color:var(--text);
                            }
                            .prize-label{
                                color:var(--muted);
                                font-weight:500;
                                text-align:left !important;
                            }

                            .cell-right{
                                text-align:right;
                            }

                            .pill{
                                font-size:11px;
                                padding:2px 6px;
                                border-radius:6px;
                                background:#f5f8ff;
                                border:1px solid #d8e5ff;
                                color:#0b3a75;
                            }

                            .hint{
                                color:var(--muted);
                                font-size:12px;
                                margin:8px 2px 16px;
                            }

                            .controls{
                                display:flex;
                                gap:10px;
                                align-items:center;
                                margin:0 0 12px;
                            }
                            input[type=search]{
                                background:#ffffff;
                                border:1px solid #dbe3eb;
                                color:var(--text);
                                padding:10px 12px;
                                border-radius:10px;
                                min-width:260px;
                                outline:none;
                            }
                            input[type=search]:focus{
                                border-color:#b6c8e1;
                                box-shadow:0 0 0 3px rgba(22,119,255,.15);
                            }

                            .legend{
                                color:var(--muted);
                                font-size:12px;
                            }
                            .cap{
                                text-transform:capitalize;
                            }

                        </style>
                        <div style="width: 100%;margin: 20px 0 50px;">
                            <input type="submit" class="submit_form submit_form_10 nopost" value="Importa dati da Google" id="import-google" />
                        </div>
                        <header>
                            <h1>Budget</h1>
                            <div class="sub">Premi e obiettivi per negozio, raggruppati per <strong>mese/anno</strong></div>
                        </header>

                        <div class="wrap">
                            <div class="controls">
                                <input type="search" id="filter" placeholder="Filtra per punto vendita, SM o DM…">
                                <div class="legend">Tier ↑ da sinistra a destra · Valute in EUR</div>
                            </div>

                            <div class="table-wrap">
                                <table id="budget-table" aria-describedby="tabella budget per periodi">
                                    <thead>
                                        <tr>
                                            <th class="sticky">Store Manager</th>
                                            <th class="sticky sticky-2">District Manager</th>
                                            <th class="sticky sticky-3">Punto Vendita</th>
                                            <th class="sticky sticky-4 subhead">Legenda</th>
                                            <?php foreach ($periods as $p): ?>
                                                <th class="colgroup" colspan="5">
                                                    <?= monthItShort((int) $p['month']) . ' ' . $p['year'] ?>
                                                    <div class="tier">T1 · T2 · T3 · T4 · T5</div>
                                                </th>
                                            <?php endforeach; ?>
                                        </tr>
                                        <tr>
                                            <th class="sticky subhead"></th>
                                            <th class="sticky sticky-2 subhead"></th>
                                            <th class="sticky sticky-3 subhead"></th>
                                            <th class="sticky sticky-4 subhead">Premi:</th>
                                            <?php foreach ($periods as $p): ?>
                                                <th class="subhead">T1</th>
                                                <th class="subhead">T2</th>
                                                <th class="subhead">T3</th>
                                                <th class="subhead">T4</th>
                                                <th class="subhead">T5</th>
                                            <?php endforeach; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Riga premi (sticky sotto header), replica riga 2 del CSV -->
                                        <tr class="prize-row">
                                            <td class="sticky prize-label">Premio</td>
                                            <td class="sticky sticky-2"></td>
                                            <td class="sticky sticky-3"></td>
                                            <td class="sticky sticky-4"><?= $periods[0]['perc_budget'] ?>%</td>
                                            <?php
                                            foreach ($periods as $p):
                                                $pid = (int) $p['id'];
                                                for ($tier = 1; $tier <= 5; $tier++):
                                                    $val = $prizes[$pid][$tier] ?? null;
                                                    ?>
                                                    <td class="cell-right"><?= $val !== null ? fmtMoney($val, $CURRENCY) : '' ?></td>
                                                    <?php
                                                endfor;
                                            endforeach;
                                            ?>
                                        </tr>

                                        <!-- Righe negozi -->
                                        <?php
                                        foreach ($stores as $s):
                                            $sid = (int) $s['store_id'];
                                            ?>
                                            <tr data-row>
                                                <td class="sticky"><?= htmlspecialchars(trim(($targetSD[$sid][$period_id]['cognome_store'] ?? ''))) ?></td>
                                                <td class="sticky sticky-2"><?= htmlspecialchars(trim(($targetSD[$sid][$period_id]['cognome_district'] ?? ''))) ?></td>
                                                <td class="sticky sticky-3"><span class="badge"><?= htmlspecialchars($s['store_name']) ?></span></td>
                                                <td class="sticky sticky-4"><span class="pill">T1→T5</span></td>
                                                <?php
                                                foreach ($periods as $p):
                                                    $pid = (int) $p['id'];
                                                    for ($tier = 1; $tier <= 5; $tier++):
                                                        $v = $targets[$sid][$pid][$tier] ?? null;
                                                        ?>
                                                        <td class="cell-right"><?= $v !== null ? fmtMoney($v, $CURRENCY) : '' ?></td>
                                                        <?php
                                                    endfor;
                                                endforeach;
                                                ?>
                                            </tr>
                                            <?php
                                        endforeach;
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <script>
                            // Filtro semplice lato client su SM, DM, Store
                            const q = document.getElementById('filter');
                            const rows = Array.from(document.querySelectorAll('tr[data-row]'));
                            q.addEventListener('input', () => {
                            const v = q.value.toLowerCase().trim();
                            rows.forEach(tr => {
                            const t = tr.textContent.toLowerCase();
                            tr.style.display = v === '' || t.includes(v) ? '' : 'none';
                            });
                            });
                            $(function() {
                            $('#import-google').unbind('click').click(function() {
                            $.ajax({
                            type: "POST",
                                    url: "./budget.php",
                                    data: "submit=import-google",
                                    dataType: "json",
                                    success: function (msg) {
                                    alert('Dati importati con successo!');
                                    return false;
                                    }
                            });
                            });
                            });
                        </script>
                        <?php
                    } elseif ($op == 'config_dmsm') {
                        $pdo = $db;
                        $periods = $pdo->query("
  SELECT id, year, month, perc_budget
  FROM budget_periods
  ORDER BY year ASC, month ASC
")->fetchAll();

                        //var_dump($periods);
                        function findPeriodId(array $periods, int $y, int $m): ?array {
                            foreach ($periods as $p)
                                if ((int) $p['year'] === $y && (int) $p['month'] === $m)
                                    return Array($p['id'], $p['perc_budget'], $p['perc_district']);
                            return null;
                        }

                        function monthItShort(int $m): string {
                            return ['Gen', 'Feb', 'Mar', 'Apr', 'Mag', 'Giu', 'Lug', 'Ago', 'Set', 'Ott', 'Nov', 'Dic'][$m - 1] ?? (string) $m;
                        }

                        /* ====== SCELTA PERIODO (default: mese corrente) ====== */
                        $curY = (int) date('Y');
                        $curM = (int) date('n');
                        $curG = (int) date('d');
                        $dateTime = new DateTime();
                        $dateTime->modify('-1 MONTH');
                        $prev_month = $dateTime->format('m');
                        $sel = isset($_GET['period']) ? trim((string) $_GET['period']) : ''; // formato atteso: YYYY-MM
                        $selY = $curY;
                        $selM = $curM;

                        if ($sel && preg_match('~^(\d{4})-(\d{2})$~', $sel, $m)) {
                            $selY = (int) $m[1];
                            $selM = (int) $m[2];
                        }

                        list($period_id, $percBudget, $percDistrict) = findPeriodId($periods, $selY, $selM);
                        //die("$period_id, $percBudget");
                        if (!$period_id) {
                            // se il mese corrente non c'è, usa il primo disponibile (più recente)
                            $period_id = (int) $periods[0]['id'];
                            $selY = (int) $periods[0]['year'];
                            $selM = (int) $periods[0]['month'];
                        }
                        $qry = "SELECT
    s.id as store_id,
    s.nominativo as store_name,
    COALESCE((select u.id from utenti u inner join store_budget_targets sbt on u.id=sbt.districtM_id and sbt.store_id=s.id where period_id=$period_id limit 1),'') as id_district_m,
    COALESCE((select u.id from utenti u inner join store_budget_targets sbt on u.id=sbt.storeM_id and sbt.store_id=s.id where period_id=$period_id limit 1),'') as id_store_m,
    COALESCE((select cognome from utenti u inner join store_budget_targets sbt on u.id=sbt.districtM_id and sbt.store_id=s.id where period_id=$period_id limit 1),'') as cognome_district,
    COALESCE((select cognome from utenti u inner join store_budget_targets sbt on u.id=sbt.storeM_id and sbt.store_id=s.id where period_id=$period_id limit 1),'') as cognome_store
  FROM utenti s
  where attivo=1 and livello='5' and (diraff='d' or s.id=55) and s.id in (select store_id from store_budget_targets)
  ORDER BY s.nominativo ASC";
                        //echo "$qry";
                        $stores = $pdo->query($qry)->fetchAll(PDO::FETCH_ASSOC);
                        ?>
                        <style>
                            :root{
                                --bg:#f6f8fb;
                                --card:#ffffff;
                                --text:#0b1220;
                                --muted:#5e6a7a;

                                --tick:#c6d3e0;
                                --bar:#eef2f6;
                                --fill:#1677ff;

                                --badge:#f2f7ff;
                                --badge-br:#d6e6ff;
                            }

                            *{
                                box-sizing:border-box
                            }

                            body{
                                color:var(--text);
                            }

                            header{
                                padding:16px 20px;
                                position:sticky;
                                top:0;
                                z-index:10;
                                background:linear-gradient(180deg,rgba(255,255,255,.95),rgba(255,255,255,.85));
                                border-bottom:1px solid #e6edf3;
                                backdrop-filter:blur(6px);
                            }

                            h1{
                                margin:0;
                                font-size:18px
                            }
                            .sub{
                                color:var(--muted);
                                font-size:12px;
                                margin-top:6px
                            }

                            .wrap{
                                padding:18px;
                                max-width:1600px;
                                margin:0 auto;
                            }

                            .toolbar{
                                display:flex;
                                gap:12px;
                                align-items:center;
                                margin:12px 0 16px;
                                flex-wrap:wrap;
                            }

                            select, input[type=month]{
                                background:#ffffff;
                                border:1px solid #dbe3eb;
                                color:var(--text);
                                padding:10px 12px;
                                border-radius:10px;
                                outline:none;
                            }
                            input[type=search]{
                                background:#ffffff;
                                border:1px solid #dbe3eb;
                                color:var(--text);
                                padding:10px 12px;
                                border-radius:10px;
                                min-width:260px;
                                outline:none;
                            }
                            select:focus, input[type=month]:focus, input[type=search]:focus{
                                border-color:#b6c8e1;
                                box-shadow:0 0 0 3px rgba(22,119,255,.15);
                            }

                            .card{
                                background:var(--card);
                                border:1px solid #e6edf3;
                                border-radius:14px;
                                padding:16px 16px 12px;
                                margin-bottom:12px;
                                box-shadow:0 6px 18px rgba(0,0,0,.06), inset 0 1px 0 rgba(255,255,255,.7);
                            }

                            .row{
                                display:grid;
                                grid-template-columns: 180px 1fr 300px;
                                gap:14px;
                                align-items:center;
                                padding:12px 0;
                            }
                            .row + .row{
                                border-top:1px dashed #e6edf3;
                            }

                            .store{
                                display:flex;
                                flex-direction:column;
                                gap:2px;
                            }
                            .store .name{
                                font-weight:600
                            }
                            .store .meta{
                                color:var(--muted);
                                font-size:12px
                            }

                            /* Barra (scala da T1) + etichette sotto */
                            .bar-block{
                                position:relative;
                            }

                            .bar-wrap{
                                position:relative;
                                height:36px;
                                background:var(--bar);
                                border-radius:999px;
                                overflow:hidden;
                                border:1px solid #dbe3eb;
                            }
                            .fill{
                                position:absolute;
                                top:0;
                                left:0;
                                height:100%;
                                width:0%;
                                background:linear-gradient(90deg,var(--fill),#69a9ff);
                            }
                            .ticks{
                                position:absolute;
                                inset:0;
                                pointer-events:none;
                            }
                            .tick{
                                position:absolute;
                                top:0;
                                bottom:0;
                                width:2px;
                                background:var(--tick);
                                opacity:.9;
                            }

                            .labels{
                                position:relative;
                                height:45px;
                                margin-top:6px;
                            }
                            .tick-badge{
                                position:absolute;
                                top:0;
                                transform:translateX(-50%);
                                font-size:11px;
                                background:var(--badge);
                                color:#103a73;
                                border:1px solid var(--badge-br);
                                padding:2px 6px;
                                border-radius:8px;
                                white-space:nowrap;
                                pointer-events:none;
                                box-shadow:0 2px 6px rgba(16,58,115,.08);
                            }
                            .tick-badge.left-edge{
                                left:6px !important;
                                transform:none;
                            }
                            .tick-badge.right-edge{
                                right:6px !important;
                                left:auto !important;
                                transform:none;
                            }

                            .kpi{
                                text-align:right;
                                display:flex;
                                flex-direction:column;
                                gap:4px;
                            }
                            .kpi .val{
                                font-weight:700
                            }
                            .kpi .prize{
                                font-size:12px;
                                color:var(--muted)
                            }

                            .chip{
                                padding:2px 8px;
                                border-radius:999px;
                                background:#f5f8ff;
                                border:1px solid #d8e5ff;
                                color:#103a73;
                                font-size:12px;
                            }
                            .legenda {
                                position: relative;
                                line-height: 130%;
                            }
                            .sticky{
                                position:sticky;
                                left:0;
                                z-index:1;
                                background:linear-gradient(90deg, var(--sticky), #f7f9fc);
                                border-right:1px solid #e6edf3;
                            }
                            table{
                                width:max-content;
                                border-collapse:separate;
                                border-spacing:0;
                                min-width:100%;
                            }
                            thead th{
                                position:sticky;
                                top:0;
                                z-index:2;
                                background:linear-gradient(180deg, var(--sticky), #f7f9fc);
                                color:var(--text);
                                border-bottom:1px solid #dbe3eb;
                                padding:10px 12px;
                                white-space:nowrap;
                                font-weight:600;
                            }
                            thead .subhead{
                                font-size:12px;
                                color:var(--muted);
                                font-weight:500;
                            }
                            tbody td{
                                border-bottom:1px solid #edf2f7;
                                padding:10px 12px;
                                white-space:nowrap;
                                color:var(--text);
                            }
                            tbody tr:nth-child(odd) td{
                                background:rgba(0,0,0,.02); /* leggero zebra */
                            }
                        </style>
                        <div class="wrap">
                            <div class="toolbar">
                                <!-- Selettore basato sui periodi in DB -->
                                <form id="period-form" method="get" action="">
                                    <input type="hidden" name="op" value="config_dmsm" />
                                    <label class="chip" for="period">Periodo:</label>
                                    <select id="period" name="period" onchange="this.form.submit()">
                                        <?php
                                        foreach ($periods as $p) {
                                            $optY = (int) $p['year'];
                                            $optM = (int) $p['month'];
                                            $val = sprintf('%04d-%02d', $optY, $optM);
                                            $selAttr = ($optY === $selY && $optM === $selM) ? ' selected' : '';
                                            echo '<option value="' . $val . '"' . $selAttr . '>' . sprintf('%s %d', monthItShort($optM), $optY) . '</option>';
                                        }
                                        ?>
                                    </select>
                                    <!-- Quick jump con input month (facoltativo): normalizza su un valore presente -->
        <!--                                        <input type="month" id="quick" value="<?= sprintf('%04d-%02d', $selY, $selM) ?>" oninput="jumpToMonth(this.value)">-->
                                    <script>
                                        function jumpToMonth(v){
                                        if (!v) return;
                                        // prova a trovare un option che inizi con v (YYYY-MM)
                                        const sel = document.getElementById('period');
                                        const want = v;
                                        let found = false;
                                        for (const opt of sel.options){
                                        if (opt.value === want){ sel.value = want; found = true; break; }
                                        }
                                        if (found) sel.form.submit();
                                        else alert('Periodo non presente in database. Aggiungi il periodo tramite import.');
                                        }
                                    </script>
                                </form>
                            </div>
                            <div class="table-wrap">
                                <?php
                                $qry = "select id,nome,cognome from utenti where livello='3' order by cognome;";
                                $users = $pdo->query($qry)->fetchAll(PDO::FETCH_ASSOC);
                                ?>
                                <form id="dmsm-form" method="post" action="">
                                    <input type="hidden" name="period_id" value="<?= $period_id ?>" />
                                    <div style="margin:20px 0;"><button class="btn">SALVA MODIFICHE</button></div>
                                    <table id="budget-table" aria-describedby="tabella budget per periodi">
                                        <thead>
                                            <tr data-row>
                                                <th class="sticky sticky-3">Punto Vendita</th>
                                                <th class="sticky sticky-2">District Manager</th>
                                                <th class="sticky">Store Manager</th>
                                            </tr>
                                        </thead>
                                        <tbody>
        <?php foreach ($stores as $store) { ?>
                                                <tr data-row>
                                            <input type="hidden" name="stores[]" value="<?= $store['store_id'] ?>" />
                                            <td class="sticky"><?= $store['store_name'] ?></td>
                                            <td><select name="dm_<?= $store['store_id'] ?>">
                                                    <option value="0"></option>
                                                    <?php foreach ($users as $user) { ?>
                                                        <option value="<?= $user['id'] ?>"<?= ($user['id'] == $store['id_district_m'] ? ' selected' : '') ?>><?= $user['cognome'] ?> <?= $user['nome'] ?></option>
            <?php } ?>
                                                </select></td>
                                            <td><select name="sm_<?= $store['store_id'] ?>">
                                                    <option value="0"></option>
                                                    <?php foreach ($users as $user) { ?>
                                                        <option value="<?= $user['id'] ?>"<?= ($user['id'] == $store['id_store_m'] ? ' selected' : '') ?>><?= $user['cognome'] ?> <?= $user['nome'] ?></option>
            <?php } ?>
                                                </select></td>
                                            </tr>
        <?php } ?>
                                        </tbody>
                                    </table>  
                                </form>
                            </div>
                        </div>
                        <?php
                    } else {
                        if ($_SESSION['livello'] <= 5) {
                            $pdo = $db;
                            $CURRENCY = '€';
                            /* ====== PERIODI DISPONIBILI ====== */
                            $periods = $pdo->query("SELECT *  FROM budget_periods ORDER BY year DESC, month DESC")->fetchAll(PDO::FETCH_ASSOC);
                            if (!$periods) {
                                die("Nessun periodo definito. Esegui l’import e riprova.");
                            }

                            /* helper: trova period_id per y/m */

                            function findPeriodId(array $periods, int $y, int $m): ?array {
                                foreach ($periods as $p)
                                    if ((int) $p['year'] === $y && (int) $p['month'] === $m)
                                        return Array($p['id'], $p['perc_budget'], $p['perc_district']);
                                return null;
                            }

                            /* ====== SCELTA PERIODO (default: mese corrente) ====== */
                            $curY = (int) date('Y');
                            $curM = (int) date('n');
                            $curG = (int) date('d');
                            $dateTime = new DateTime();
                            $dateTime->modify('-1 MONTH');
                            $prev_month = $dateTime->format('m');
                            $sel = isset($_GET['period']) ? trim((string) $_GET['period']) : ''; // formato atteso: YYYY-MM
                            $selY = $curY;
                            $selM = $curM;

                            if ($sel && preg_match('~^(\d{4})-(\d{2})$~', $sel, $m)) {
                                $selY = (int) $m[1];
                                $selM = (int) $m[2];
                                if ($_SESSION['livello'] > 1 && ($selM < $prev_month || ($selM == $prev_month && $curG > 15))) {
                                    die('Non hai i permessi per visualizzare questa pagina');
                                }
                            }

                            list($period_id, $percBudget, $percDistrict) = findPeriodId($periods, $selY, $selM);
                            //die("$period_id, $percBudget");
                            if (!$period_id) {
                                // se il mese corrente non c'è, usa il primo disponibile (più recente)
                                $period_id = (int) $periods[0]['id'];
                                $selY = (int) $periods[0]['year'];
                                $selM = (int) $periods[0]['month'];
                            }

                            /* ====== PREMI DEL PERIODO ====== */
                            $prizeStmt = $pdo->prepare("
  SELECT tier_number, prize_amount
  FROM budget_prizes
  WHERE period_id = ?
  ORDER BY tier_number ASC
");
                            $prizeStmt->execute([$period_id]);
                            $prizes = []; // [tier => amount]
                            foreach ($prizeStmt as $row) {
                                $prizes[(int) $row['tier_number']] = (float) $row['prize_amount'];
                            }

                            /* ====== TARGETS T1..T4 PER NEGOZIO ====== */
                            $targetsStmt = $pdo->prepare("
  SELECT store_id, tier_number, target_value
  FROM store_budget_targets
  WHERE period_id = ?
");
                            $targetsStmt->execute([$period_id]);
                            $targets = []; // [store_id][tier] = value
                            foreach ($targetsStmt as $row) {
                                $targets[(int) $row['store_id']][(int) $row['tier_number']] = (float) $row['target_value'];
                            }

                            /* ====== ACTUALS (fatturato reale) ====== */
                            $actualsStmt = $pdo->prepare("
  SELECT store_id, actual_value
  FROM store_budget_actuals
  WHERE period_id = ?
");
                            $actualsStmt->execute([$period_id]);
                            $actuals = []; // [store_id] = actual_value
                            foreach ($actualsStmt as $row) {
                                $actuals[(int) $row['store_id']] = (float) $row['actual_value'];
                            }

                            /* ====== NEGOZI ====== */
                            if ($_SESSION['livello'] == '5') {
                                $qry_atelier = " and s.id=" . $_SESSION['id'];
                            } elseif ($_SESSION['livello'] == '3') {
                                if ($_SESSION['ruolo'] == DISTRICT_MANAGER) {
                                    $qry_atelier = " and s.id in (select store_id from store_budget_targets o where districtM_id={$_SESSION['id']} and period_id=$period_id)";
                                } else {
                                    $qry_atelier = " and s.id in (select store_code from operator_month_kpis o where operator_code={$_SESSION['id']} and kpi_year=$selY and kpi_month=$selM)";
                                }
                            } else {
                                $qry_atelier = " and s.id in (select store_id from store_budget_targets)";
                            }
                            if ($_SESSION['ruolo'] == RISORSE_UMANE) {
                                $qry_atelier = " and s.id in (select store_id from store_budget_targets)";
                            }
                            $qry = "
  SELECT
    s.id as store_id,
    s.nominativo as store_name,
    COALESCE((select u.id from utenti u inner join store_budget_targets sbt on u.id=sbt.districtM_id and sbt.store_id=s.id where period_id=$period_id limit 1),'') as id_district_m,
    COALESCE((select u.id from utenti u inner join store_budget_targets sbt on u.id=sbt.storeM_id and sbt.store_id=s.id where period_id=$period_id limit 1),'') as id_store_m,
    COALESCE((select cognome from utenti u inner join store_budget_targets sbt on u.id=sbt.districtM_id and sbt.store_id=s.id where period_id=$period_id limit 1),'') as cognome_district,
    COALESCE((select cognome from utenti u inner join store_budget_targets sbt on u.id=sbt.storeM_id and sbt.store_id=s.id where period_id=$period_id limit 1),'') as cognome_store
  FROM utenti s
  where attivo=1 and livello='5' and (diraff='d' or s.id=55) $qry_atelier
  ORDER BY s.nominativo ASC";
                            //echo "$qry";
                            $stores = $pdo->query($qry)->fetchAll(PDO::FETCH_ASSOC);

                            /* ====== HELPERS ====== */

                            function fmtMoney(?float $v, string $currency = '€'): string {
                                if ($v === null)
                                    return '';
                                return number_format($v, 2, ',', '.') . ' ' . $currency;
                            }

                            function fmtMoneyLabels(?float $v, string $currency = '€'): string {
                                if ($v === null)
                                    return '';
                                // Formato italiano: 1.234,56
                                return number_format($v / 1000, 2, ',', '.') . 'K ' . $currency;
                            }

                            function monthItShort(int $m): string {
                                return ['Gen', 'Feb', 'Mar', 'Apr', 'Mag', 'Giu', 'Lug', 'Ago', 'Set', 'Ott', 'Nov', 'Dic'][$m - 1] ?? (string) $m;
                            }

                            function computePrize(float $actual, int $tierReached, array $prizes, float $percBudget = 0.01, float $percDistrict): array {// [ budget, store, district ]
                                if ($tierReached <= 0)
                                    return [0, 0, 0];
                                if (isset($prizes[$tierReached]) && $prizes[$tierReached] > 0) {
//                                    if ($_SESSION['ruolo'] == STORE_MANAGER) {
//                                        return (float) round($actual * $percBudget, 2) + $prizes[$tierReached];
//                                    } elseif ($_SESSION['ruolo'] == DISTRICT_MANAGER) {
//                                        return (float) round($actual * $percBudget, 2) + round($prizes[$tierReached] * $percDistrict, 2);
//                                    } else {
//                                        return (float) round($actual * $percBudget, 2);
//                                    }
                                    return [round($actual * $percBudget, 2), round($prizes[$tierReached], 2), round($prizes[$tierReached] * $percDistrict, 2)];
                                }
                                if ($tierReached === 1)
                                    return [round($actual * $percBudget, 2), 0, 0];                // fallback 1% su T1
                                for ($t = $tierReached - 1; $t >= 1; $t--) {                            // fallback: ultimo tier valido
                                    if (isset($prizes[$t]) && $prizes[$t] > 0) {
//                                        if ($_SESSION['ruolo'] == STORE_MANAGER) {
//                                            return [(float) round($actual * $percBudget, 2), round($prizes[$t],2)];
//                                        } elseif ($_SESSION['ruolo'] == DISTRICT_MANAGER) {
//                                            return (float) round($actual * $percBudget, 2) + round($prizes[$tierReached] * $percDistrict, 2);
//                                            return (float) round($actual * $percBudget, 2) + round($prizes[$t] * $percDistrict, 2);
//                                        } else {
//                                            return (float) round($actual * $percBudget, 2);
//                                        }
                                        return [round($actual * $percBudget, 2), round($prizes[$tierReached], 2), round($prizes[$tierReached] * $percDistrict, 2)];
                                    }
                                }
                                return round($actual * 0.01, 2);
                            }

                            $totale_premi = 0;
                            ?>
                            <style>
                                :root{
                                    --bg:#f6f8fb;
                                    --card:#ffffff;
                                    --text:#0b1220;
                                    --muted:#5e6a7a;

                                    --tick:#c6d3e0;
                                    --bar:#eef2f6;
                                    --fill:#1677ff;

                                    --badge:#f2f7ff;
                                    --badge-br:#d6e6ff;
                                }

                                *{
                                    box-sizing:border-box
                                }

                                body{
                                    color:var(--text);
                                }

                                header{
                                    padding:16px 20px;
                                    position:sticky;
                                    top:0;
                                    z-index:10;
                                    background:linear-gradient(180deg,rgba(255,255,255,.95),rgba(255,255,255,.85));
                                    border-bottom:1px solid #e6edf3;
                                    backdrop-filter:blur(6px);
                                }

                                h1{
                                    margin:0;
                                    font-size:18px
                                }
                                .sub{
                                    color:var(--muted);
                                    font-size:12px;
                                    margin-top:6px
                                }

                                .wrap{
                                    padding:18px;
                                    max-width:1600px;
                                    margin:0 auto;
                                }

                                .toolbar{
                                    display:flex;
                                    gap:12px;
                                    align-items:center;
                                    margin:12px 0 16px;
                                    flex-wrap:wrap;
                                }

                                select, input[type=month]{
                                    background:#ffffff;
                                    border:1px solid #dbe3eb;
                                    color:var(--text);
                                    padding:10px 12px;
                                    border-radius:10px;
                                    outline:none;
                                }
                                input[type=search]{
                                    background:#ffffff;
                                    border:1px solid #dbe3eb;
                                    color:var(--text);
                                    padding:10px 12px;
                                    border-radius:10px;
                                    min-width:260px;
                                    outline:none;
                                }
                                select:focus, input[type=month]:focus, input[type=search]:focus{
                                    border-color:#b6c8e1;
                                    box-shadow:0 0 0 3px rgba(22,119,255,.15);
                                }

                                .card{
                                    background:var(--card);
                                    border:1px solid #e6edf3;
                                    border-radius:14px;
                                    padding:16px 16px 12px;
                                    margin-bottom:12px;
                                    box-shadow:0 6px 18px rgba(0,0,0,.06), inset 0 1px 0 rgba(255,255,255,.7);
                                }

                                .row{
                                    display:grid;
                                    grid-template-columns: 180px 1fr 300px;
                                    gap:14px;
                                    align-items:center;
                                    padding:12px 0;
                                }
                                .row + .row{
                                    border-top:1px dashed #e6edf3;
                                }

                                .store{
                                    display:flex;
                                    flex-direction:column;
                                    gap:2px;
                                }
                                .store .name{
                                    font-weight:600
                                }
                                .store .meta{
                                    color:var(--muted);
                                    font-size:12px
                                }

                                /* Barra (scala da T1) + etichette sotto */
                                .bar-block{
                                    position:relative;
                                }

                                .bar-wrap{
                                    position:relative;
                                    height:36px;
                                    background:var(--bar);
                                    border-radius:999px;
                                    overflow:hidden;
                                    border:1px solid #dbe3eb;
                                }
                                .fill{
                                    position:absolute;
                                    top:0;
                                    left:0;
                                    height:100%;
                                    width:0%;
                                    background:linear-gradient(90deg,var(--fill),#69a9ff);
                                }
                                .ticks{
                                    position:absolute;
                                    inset:0;
                                    pointer-events:none;
                                }
                                .tick{
                                    position:absolute;
                                    top:0;
                                    bottom:0;
                                    width:2px;
                                    background:var(--tick);
                                    opacity:.9;
                                }

                                .labels{
                                    position:relative;
                                    height:45px;
                                    margin-top:6px;
                                }
                                .tick-badge{
                                    position:absolute;
                                    top:0;
                                    transform:translateX(-50%);
                                    font-size:11px;
                                    background:var(--badge);
                                    color:#103a73;
                                    border:1px solid var(--badge-br);
                                    padding:2px 6px;
                                    border-radius:8px;
                                    white-space:nowrap;
                                    pointer-events:none;
                                    box-shadow:0 2px 6px rgba(16,58,115,.08);
                                }
                                .tick-badge.left-edge{
                                    left:6px !important;
                                    transform:none;
                                }
                                .tick-badge.right-edge{
                                    right:6px !important;
                                    left:auto !important;
                                    transform:none;
                                }

                                .kpi{
                                    text-align:right;
                                    display:flex;
                                    flex-direction:column;
                                    gap:4px;
                                }
                                .kpi .val{
                                    font-weight:700
                                }
                                .kpi .prize{
                                    font-size:12px;
                                    color:var(--muted)
                                }

                                .chip{
                                    padding:2px 8px;
                                    border-radius:999px;
                                    background:#f5f8ff;
                                    border:1px solid #d8e5ff;
                                    color:#103a73;
                                    font-size:12px;
                                }
                                .legenda {
                                    position: relative;
                                    line-height: 130%;
                                }
                            </style>
                            <div class="wrap">
                                <div class="toolbar">
                                    <!-- Selettore basato sui periodi in DB -->
                                    <form id="period-form" method="get" action="">
                                        <label class="chip" for="period">Periodo:</label>
                                        <select id="period" name="period" onchange="this.form.submit()">
                                            <?php
                                            foreach ($periods as $p) {
                                                $optY = (int) $p['year'];
                                                $optM = (int) $p['month'];
                                                $val = sprintf('%04d-%02d', $optY, $optM);
                                                $selAttr = ($optY === $selY && $optM === $selM) ? ' selected' : '';
                                                if (($_SESSION['livello'] <= 1 || $_SESSION['ruolo'] == RISORSE_UMANE) || ($curG <= 15 && $optM == $prev_month) || $optM == $curM) {
                                                    echo '<option value="' . $val . '"' . $selAttr . '>' . sprintf('%s %d', monthItShort($optM), $optY) . '</option>';
                                                }
                                            }
                                            ?>
                                        </select>
                                        <!-- Quick jump con input month (facoltativo): normalizza su un valore presente -->
            <!--                                        <input type="month" id="quick" value="<?= sprintf('%04d-%02d', $selY, $selM) ?>" oninput="jumpToMonth(this.value)">-->
                                        <script>
                                            function jumpToMonth(v){
                                            if (!v) return;
                                            // prova a trovare un option che inizi con v (YYYY-MM)
                                            const sel = document.getElementById('period');
                                            const want = v;
                                            let found = false;
                                            for (const opt of sel.options){
                                            if (opt.value === want){ sel.value = want; found = true; break; }
                                            }
                                            if (found) sel.form.submit();
                                            else alert('Periodo non presente in database. Aggiungi il periodo tramite import.');
                                            }
                                        </script>
                                    </form>
                                    <input type="search" id="q" placeholder="Filtra per negozio, SM o DM…">
            <?php if ($_SESSION['livello'] <= 1) { ?>
                                        <div style="width: 100%;margin: 20px 0 20px;">
                                            <a class="submit_form submit_form_10 nopost" target="_blank" style="color:#ffffff;line-height: 35px;text-align: center;font-weight: bold;width: 250px;font-size: 14px;" href="/budget.php?submit=lista-premi&year=<?= $selY ?>&month=<?= $selM ?>">Esporta Lista Premi per Operatore</a>
                                        </div>
                                <?php } ?>
                                </div>
            <?php if ($_SESSION['livello'] == '3' && $_SESSION['ruolo'] != RISORSE_UMANE) { ?>
                                    <div class="card">
                                        <h1>La tua situazione nel mese di <?= $arrmesiesteso[$selM] ?></h1>
                                        <div style="font-size: 14px;line-height: 150%;margin:20px 0;">
                                            <?php
                                            $arrStoreDM = $arrStoreSM = $arrStoreAV = $arrStoreAll = [];
                                            $arrStoreAV[] = -1;
                                            $qry = " SELECT s.id as store_id, s.nominativo as store_name, "
                                                    . "COALESCE((select districtM_id from store_budget_targets where period_id=$period_id and store_id=s.id limit 1),0) as id_district,"
                                                    . "COALESCE((select storeM_id from store_budget_targets where period_id=$period_id and store_id=s.id limit 1),0) as id_store "
                                                    . "FROM utenti s where attivo=1 and livello='5' and s.id in (select store_code from operator_month_kpis o where "
                                                    . "operator_code={$_SESSION['id']} and kpi_year=$selY and kpi_month=$selM) ORDER BY s.nominativo ASC";
                                            //echo "$qry<br>";
                                            $rs = $pdo->prepare($qry);
                                            $rs->execute();
                                            while ($col = $rs->fetch(PDO::FETCH_ASSOC)) {
                                                $sid = $col['store_id'];
                                                $arrStoreAll[] = $sid;
                                                $qry = "select u.nome, u.cognome, j.total_sold_amount from utenti u inner join operator_month_kpis j on j.operator_code=u.id where u.livello='3' "
                                                        . "and kpi_year=$selY and kpi_month=$selM and store_code=$sid and operator_code={$_SESSION['id']} limit 1;";
                                                //echo "$qry";
                                                $rs_op = $pdo->prepare($qry);
                                                $rs_op->execute();
                                                $col_op = $rs_op->fetch(PDO::FETCH_ASSOC);
                                                $t1 = $targets[$sid][1] ?? null;
                                                $t2 = $targets[$sid][2] ?? null;
                                                $t3 = $targets[$sid][3] ?? null;
                                                $t4 = $targets[$sid][4] ?? null;
                                                $t5 = $targets[$sid][5] ?? null;
                                                $act = $actuals[$sid] ?? 0.0;
                                                $tierReached = 0;
                                                if ($act >= $t1)
                                                    $tierReached = 1;
                                                if ($t2 !== null && $act >= $t2)
                                                    $tierReached = 2;
                                                if ($t3 !== null && $act >= $t3)
                                                    $tierReached = 3;
                                                if ($t4 !== null && $act >= $t4)
                                                    $tierReached = 4;
                                                if ($t5 !== null && $act >= $t5)
                                                    $tierReached = 5;
                                                if ($tierReached > 0) {
                                                    $premioPerc = $col_op['total_sold_amount'] * $percBudget / 100;
                                                } else {
                                                    $premioPerc = 0;
                                                }
                                                $totale_premi += $premioPerc;
                                                //echo "$tierReached<br>";
                                                if ($_SESSION['id'] == $col['id_store']) {
                                                    $arrStoreSM[] = $sid;
                                                    if (isset($prizes[$tierReached]) && $prizes[$tierReached] > 0) {
                                                        $premio_sm = ' - <span style="color:green;font-weight:bold;">Premio SM: ' . fmtMoney($prizes[$tierReached], $CURRENCY) . '</span>';
                                                        $totale_premi += $prizes[$tierReached];
                                                    } else {
                                                        $premio_sm = '';
                                                    }
                                                } elseif ($_SESSION['id'] == $col['id_district']) {
                                                    $arrStoreDM[] = $sid;
                                                    if (isset($prizes[$tierReached]) && $prizes[$tierReached] > 0) {
                                                        $premio_dm = ' - <span style="color:green;font-weight:bold;">Premio DM: ' . fmtMoney($prizes[$tierReached] * $percDistrict / 100, $CURRENCY) . '</span>';
                                                        $totale_premi += $prizes[$tierReached] * $percDistrict / 100;
                                                    } else {
                                                        $premio_dm = '';
                                                    }
                                                } else {
                                                    $premio_sm = $premio_dm = '';
                                                    $arrStoreAV[] = $sid;
                                                }
                                                echo '<p><b>' . $col['store_name'] . ': </b> Fatturato individuale ' . fmtMoney($col_op['total_sold_amount'], $CURRENCY) .
                                                ($premioPerc > 0 ? ' - <span style="color:green;font-weight:bold;">Premio ' . $percBudget . '%: ' . fmtMoney($premioPerc, $CURRENCY) . '</span>' : '') . $premio_sm . $premio_dm . '</p>';
                                            }
                                            #cerco gli esclusi se DM o SM
                                            $qry = " SELECT s.id as store_id, s.nominativo as store_name, "
                                                    . "COALESCE((select districtM_id from store_budget_targets where period_id=$period_id and store_id=s.id limit 1),0) as id_district,"
                                                    . "COALESCE((select storeM_id from store_budget_targets where period_id=$period_id and store_id=s.id limit 1),0) as id_store "
                                                    . "FROM utenti s where attivo=1 and livello='5' and s.id not in (" . join(",", $arrStoreAll) . ") having id_district={$_SESSION['id']} or id_store={$_SESSION['id']} ORDER BY s.nominativo ASC";
                                            //echo "$qry<br>";
                                            $rs = $pdo->prepare($qry);
                                            $rs->execute();
                                            while ($col = $rs->fetch(PDO::FETCH_ASSOC)) {
                                                $sid = $col['store_id'];
                                                $t1 = $targets[$sid][1] ?? null;
                                                $t2 = $targets[$sid][2] ?? null;
                                                $t3 = $targets[$sid][3] ?? null;
                                                $t4 = $targets[$sid][4] ?? null;
                                                $t5 = $targets[$sid][5] ?? null;
                                                $act = $actuals[$sid] ?? 0.0;
                                                $tierReached = 0;
                                                if ($act >= $t1)
                                                    $tierReached = 1;
                                                if ($t2 !== null && $act >= $t2)
                                                    $tierReached = 2;
                                                if ($t3 !== null && $act >= $t3)
                                                    $tierReached = 3;
                                                if ($t4 !== null && $act >= $t4)
                                                    $tierReached = 4;
                                                if ($t5 !== null && $act >= $t5)
                                                    $tierReached = 5;
                                                $targetS = ($tierReached - 1);
                                                if ($_SESSION['id'] == $col['id_store']) {
                                                    $arrStoreSM[] = $sid;
                                                    if (isset($prizes[$tierReached]) && $prizes[$tierReached] > 0) {
                                                        $premio_sm = ' Target S'.$targetS.' - <span style="color:green;font-weight:bold;">Premio SM: ' . fmtMoney($prizes[$tierReached], $CURRENCY) . '</span>';
                                                        $totale_premi += $prizes[$tierReached];
                                                    } else {
                                                        $premio_sm = '';
                                                    }
                                                } elseif ($_SESSION['id'] == $col['id_district']) {
                                                    $arrStoreDM[] = $sid;
                                                    if (isset($prizes[$tierReached]) && $prizes[$tierReached] > 0) {
                                                        $premio_dm = ' Target S'.$targetS.' - <span style="color:green;font-weight:bold;">Premio DM: ' . fmtMoney($prizes[$tierReached] * $percDistrict / 100, $CURRENCY) . '</span>';
                                                        $totale_premi += $prizes[$tierReached] * $percDistrict / 100;
                                                    } else {
                                                        $premio_dm = '';
                                                    }
                                                }
                                                if($premio_dm !='' || $premio_sm != '') {
                                                    echo '<p><b>' . $col['store_name'] . ': </b> ' . $premio_sm . $premio_dm . '</p>';
                                                }
                                            }
                                            ?>
                                        </div>
                                        <div style="font-size: 14px;line-height: 150%;margin:20px 0;">
                                            <h2>Le gare a cui partecipi</h2>
                                            <?php
                                            if (count($arrStoreSM) > 0 || count($arrStoreDM) > 0 || $_SESSION['ruolo'] == ADDETTO_VENDITE) {
                                                $arrGareDistrict = [];
                                                if (count($arrStoreSM) > 0 || $_SESSION['ruolo'] == ADDETTO_VENDITE) {
                                                    ?>
                                                    <div id="res-gara1" style="margin: 10px 0">
                                                        <?php
                                                        $n_gara = 1;
                                                        $qry = "select *,(select nominativo from utenti u where u.id=c.store_code limit 1) as store_name from classifica_gara$n_gara c "
                                                                . "where month=$selM and year=$selY and (store_m={$_SESSION['id']} or store_code in (" . join(",", $arrStoreAV) . "));";
                                                        //echo "$qry";
                                                        $rs_1 = $pdo->prepare($qry);
                                                        $rs_1->execute();
                                                        if ($rs_1->RowCount() > 0) {
                                                            ?>
                                                            <p><b>Miglior rapporto recensioni / abiti venduti</b></p>
                                                            <?php
                                                            while ($col_1 = $rs_1->fetch(PDO::FETCH_ASSOC)) {
                                                                if ($col_1['store_m'] == $_SESSION['id']) {
                                                                    $qry = "select premio_sm from premi_gare where num_gara=$n_gara order by valido_fino desc limit 1;";
                                                                } else {
                                                                    $qry = "select premio_av from premi_gare where num_gara=$n_gara order by valido_fino desc limit 1;";
                                                                }
                                                                //echo "$qry";
                                                                $rs_p1 = $pdo->prepare($qry);
                                                                $rs_p1->execute();
                                                                $premio_1 = $rs_p1->fetch(PDO::FETCH_ASSOC);
                                                                if ($col_1['rank'] == 1) {
                                                                    $premio_1['premio_sm'] != '' ? $totale_premi += $premio_1['premio_sm'] : $totale_premi += $premio_1['premio_av'];
                                                                }
                                                                echo '<p>' . $col_1['store_name'] . ' (' . $col_1['rank'] . ' pos.) con ' . $col_1['rapporto'] . ' ' . ($col_1['rank'] == 1 ? ' - <span style="color:green;font-weight:bold;">Premio ' . ($premio_1['premio_sm'] != '' ? 'SM: ' . fmtMoney($premio_1['premio_sm'], $CURRENCY) : 'AV:' . fmtMoney($premio_1['premio_av'], $CURRENCY)) . '</span>' : '') . '</p>';
                                                            }
                                                        }
                                                        ?>
                                                    </div>
                                                    <div id="res-gara2" style="margin: 10px 0">
                                                        <?php
                                                        $n_gara = 2;
                                                        $qry = "select *,(select nominativo from utenti u where u.id=c.store_code limit 1) as store_name from classifica_gara$n_gara c "
                                                                . "where month=$selM and year=$selY and (store_m={$_SESSION['id']} or store_code in (-1, " . join(",", $arrStoreAV) . "));";
                                                        //echo "$qry";
                                                        $rs_1 = $pdo->prepare($qry);
                                                        $rs_1->execute();
                                                        if ($rs_1->RowCount() > 0) {
                                                            ?>
                                                            <p><b>Maggior numero di recensioni</b></p>
                                                            <?php
                                                            while ($col_1 = $rs_1->fetch(PDO::FETCH_ASSOC)) {
                                                                if ($col_1['store_m'] == $_SESSION['id']) {
                                                                    $qry = "select premio_sm from premi_gare where num_gara=$n_gara order by valido_fino desc limit 1;";
                                                                } else {
                                                                    $qry = "select premio_av from premi_gare where num_gara=$n_gara order by valido_fino desc limit 1;";
                                                                }
                                                                //echo "$qry";
                                                                $rs_p1 = $pdo->prepare($qry);
                                                                $rs_p1->execute();
                                                                $premio_1 = $rs_p1->fetch(PDO::FETCH_ASSOC);
                                                                if ($col_1['rank'] == 1) {
                                                                    $premio_1['premio_sm'] != '' ? $totale_premi += $premio_1['premio_sm'] : $totale_premi += $premio_1['premio_av'];
                                                                }
                                                                echo '<p>' . $col_1['store_name'] . ' (' . $col_1['rank'] . ' pos.) con ' . $col_1['rec_ranking'] . ' ' . ($col_1['rank'] == 1 ? ' - <span style="color:green;font-weight:bold;">Premio ' . ($premio_1['premio_sm'] != '' ? 'SM: ' . fmtMoney($premio_1['premio_sm'], $CURRENCY) : 'AV:' . fmtMoney($premio_1['premio_av'], $CURRENCY)) . '</span>' : '') . '</p>';
                                                            }
                                                        }
                                                        ?>
                                                    </div>
                                                    <?php } ?>
                                                <div id="res-gara3" style="margin: 10px 0">
                                                    <?php
                                                    $n_gara = 3;
                                                    $qry = "select *,(select nominativo from utenti u where u.id=c.store_code limit 1) as store_name from classifica_gara$n_gara c where month=$selM and year=$selY and (store_m={$_SESSION['id']} or district_m={$_SESSION['id']});";
                                                    //echo "$qry";
                                                    $rs_1 = $pdo->prepare($qry);
                                                    $rs_1->execute();
                                                    if ($rs_1->RowCount() > 0) {
                                                        if (count($arrStoreDM) > 0) {
                                                            $arrGareDistrict[] = $n_gara;
                                                        }
                                                        ?>
                                                        <p><b>Miglior rapporto accessori / abiti</b></p>
                                                        <?php
                                                        while ($col_1 = $rs_1->fetch(PDO::FETCH_ASSOC)) {
                                                            if ($col_1['store_m'] == $_SESSION['id']) {
                                                                $qry = "select premio_sm from premi_gare where num_gara=$n_gara order by valido_fino desc limit 1;";
                                                            } elseif ($col_1['district_m'] == $_SESSION['id']) {
                                                                $qry = "select premio_dm from premi_gare where num_gara=$n_gara order by valido_fino desc limit 1;";
                                                            }
                                                            //echo "$qry";
                                                            $rs_p1 = $pdo->prepare($qry);
                                                            $rs_p1->execute();
                                                            $premio_1 = $rs_p1->fetch(PDO::FETCH_ASSOC);
                                                            if ($col_1['rank'] == 1) {
                                                                $premio_1['premio_sm'] != '' ? $totale_premi += $premio_1['premio_sm'] : $totale_premi += $premio_1['premio_dm'];
                                                            }
                                                            echo '<p>' . $col_1['store_name'] . ' (' . $col_1['rank'] . ' pos.)  con ' . $col_1['rapporto'] . ' ' . ($col_1['rank'] == 1 ? ' - <span style="color:green;font-weight:bold;">Premio ' . ($premio_1['premio_sm'] != '' ? 'SM: ' . fmtMoney($premio_1['premio_sm'], $CURRENCY) : 'DM:' . fmtMoney($premio_1['premio_dm'], $CURRENCY)) . '</span>' : '') . '</p>';
                                                        }
                                                    }
                                                    ?>
                                                </div>
                                                <div id="res-gara4" style="margin: 10px 0">
                                                    <?php
                                                    $n_gara = 4;
                                                    $qry = "select *,(select nominativo from utenti u where u.id=c.store_code limit 1) as store_name from classifica_gara$n_gara c where month=$selM and year=$selY and (store_m={$_SESSION['id']} or district_m={$_SESSION['id']} or store_code={$_SESSION['id']}) and village=0;";
                                                    //echo "$qry";
                                                    $rs_1 = $pdo->prepare($qry);
                                                    $rs_1->execute();
                                                    if ($rs_1->RowCount() > 0) {
                                                        if (count($arrStoreDM) > 0) {
                                                            $arrGareDistrict[] = $n_gara;
                                                        }
                                                        ?>
                                                        <p><b>Miglior Negozio per conversioni</b></p>
                                                        <?php
                                                        while ($col_1 = $rs_1->fetch(PDO::FETCH_ASSOC)) {
                                                            if ($col_1['store_m'] == $_SESSION['id']) {
                                                                $qry = "select premio_sm from premi_gare where num_gara=$n_gara order by valido_fino desc limit 1;";
                                                            } elseif ($col_1['district_m'] == $_SESSION['id']) {
                                                                $qry = "select premio_dm from premi_gare where num_gara=$n_gara order by valido_fino desc limit 1;";
                                                            }
                                                            //echo "$qry";
                                                            $rs_p1 = $pdo->prepare($qry);
                                                            $rs_p1->execute();
                                                            $premio_1 = $rs_p1->fetch(PDO::FETCH_ASSOC);
                                                            if ($col_1['rank'] == 1) {
                                                                $premio_1['premio_sm'] != '' ? $totale_premi += $premio_1['premio_sm'] : $totale_premi += $premio_1['premio_dm'];
                                                            }
                                                            echo '<p>' . $col_1['store_name'] . ' (' . $col_1['rank'] . ' pos.) con ' . $col_1['conv'] . '% ' . ($col_1['rank'] == 1 ? ' - <span style="color:green;font-weight:bold;">Premio ' . ($premio_1['premio_sm'] != '' ? 'SM: ' . fmtMoney($premio_1['premio_sm'], $CURRENCY) : 'DM:' . fmtMoney($premio_1['premio_dm'], $CURRENCY)) . '</span>' : '') . '</p>';
                                                        }
                                                    }
                                                    ?>
                                                </div>
                                                <div id="res-gara5" style="margin: 10px 0">
                                                    <?php
                                                    $n_gara = 4;
                                                    $qry = "select *,(select nominativo from utenti u where u.id=c.store_code limit 1) as store_name from classifica_gara$n_gara c where month=$selM and year=$selY and (store_m={$_SESSION['id']} or district_m={$_SESSION['id']}) and village=1;";
                                                    //echo "$qry";
                                                    $rs_1 = $pdo->prepare($qry);
                                                    $rs_1->execute();
                                                    if ($rs_1->RowCount() > 0) {
                                                        if (count($arrStoreDM) > 0) {
                                                            $arrGareDistrict[] = $n_gara;
                                                        }
                                                        ?>
                                                        <p><b>Miglior Village per conversioni</b></p>
                                                        <?php
                                                        while ($col_1 = $rs_1->fetch(PDO::FETCH_ASSOC)) {
                                                            if ($col_1['store_m'] == $_SESSION['id']) {
                                                                $qry = "select premio_sm from premi_gare where num_gara=$n_gara order by valido_fino desc limit 1;";
                                                            } elseif ($col_1['district_m'] == $_SESSION['id']) {
                                                                $qry = "select premio_dm from premi_gare where num_gara=$n_gara order by valido_fino desc limit 1;";
                                                            }
                                                            //echo "$qry";
                                                            $rs_p1 = $pdo->prepare($qry);
                                                            $rs_p1->execute();
                                                            $premio_1 = $rs_p1->fetch(PDO::FETCH_ASSOC);
                                                            if ($col_1['rank'] == 1) {
                                                                $premio_1['premio_sm'] != '' ? $totale_premi += $premio_1['premio_sm'] : $totale_premi += $premio_1['premio_dm'];
                                                            }
                                                            echo '<p>' . $col_1['store_name'] . ' (' . $col_1['rank'] . ' pos.) con ' . $col_1['conv'] . '% ' . ($col_1['rank'] == 1 ? ' - <span style="color:green;font-weight:bold;">Premio ' . ($premio_1['premio_sm'] != '' ? 'SM: ' . fmtMoney($premio_1['premio_sm'], $CURRENCY) : 'DM:' . fmtMoney($premio_1['premio_dm'], $CURRENCY)) . '</span>' : '') . '</p>';
                                                        }
                                                    }
                                                    ?>
                                                </div>
                                                <div id="res-gara6" style="margin: 10px 0">
                                                    <?php
                                                    $n_gara = 6;
                                                    $qry = "select * from classifica_gara$n_gara c where month=$selM and year=$selY and operator_code={$_SESSION['id']} limit 1;";
                                                    //echo "$qry";
                                                    $rs_1 = $pdo->prepare($qry);
                                                    $rs_1->execute();
                                                    if ($rs_1->RowCount() > 0) {
                                                        $arrGareDistrict[] = $n_gara;
                                                        $qry = "select * from premi_gare where num_gara=$n_gara order by valido_fino desc limit 1;";
                                                        $rs_p6 = $pdo->prepare($qry);
                                                        $rs_p6->execute();
                                                        $premio_1 = $rs_p6->fetch(PDO::FETCH_ASSOC);
                                                        ?>
                                                        <p><b>Miglior venditrice per fatturato</b></p>
                                                        <?php
                                                        while ($col_1 = $rs_1->fetch(PDO::FETCH_ASSOC)) {
                                                            switch ($col_1['rank']) {
                                                                case 1:
                                                                    $premio_6 = $premio_1['premio_sm'];
                                                                    break;

                                                                case 2:
                                                                    $premio_6 = $premio_1['premio_av'];
                                                                    break;

                                                                case 3:
                                                                    $premio_6 = $premio_1['premio_dm'];
                                                                    break;

                                                                default :
                                                                    $premio_6 = 0;
                                                                    break;
                                                            }
                                                            //echo "$qry";
                                                            $rs_p1 = $pdo->prepare($qry);
                                                            $rs_p1->execute();
                                                            $premio_1 = $rs_p1->fetch(PDO::FETCH_ASSOC);
                                                            if ($premio_6 > 0) {
                                                                $totale_premi += $premio_6;
                                                            }
                                                            echo '<p>Posizione ' . $col_1['rank'] . ' con ' . fmtMoney($col_1['fatturato'], $CURRENCY) . ' ' . ($premio_6 > 0 ? ' - <span style="color:green;font-weight:bold;">Premio ' . fmtMoney($premio_6, $CURRENCY) . '</span>' : '') . '</p>';
                                                        }
                                                    }
                                                    ?>
                                                </div>
                <?php } else if ($_SESSION['livello'] == '3') { ?>
                                                <div id="res-gara1" style="margin: 10px 0"></div>
                                                <div id="res-gara2" style="margin: 10px 0"></div>
                                                <div id="res-gara6" style="margin: 10px 0"></div>
                                            <?php } ?> 
                                            <?php if ($totale_premi > 0) { ?>
                                                <p style="font-size:18px;font-weight: bold;">TOTALE PREMI: <span style="color:green;"><?= fmtMoney($totale_premi, $CURRENCY) ?></span></p>
                <?php } ?>
                                        </div>
                                    </div>
            <?php } ?>
                                <div class="card">
                                    <div class="legenda">
                                        <p><b>Legenda</b></p>
                                        <p>SM = store manager</p>
                                        <p>DM = district manager</p>
                                        <p>AV = addetto vendite</p>
                                    </div>
                                    <?php

                                    function rowHtml(array $s, array $targets, array $actuals, array $prizes, string $CURRENCY, float $percBudget, float $percDistrict): string {
                                        global $selY, $selM, $pdo;
                                        $sid = (int) $s['store_id'];
                                        $store = $s['store_name'];
                                        $sm = trim($s['cognome_store']);
                                        $dm = trim($s['cognome_district']);
                                        $id_store_m = $s['id_store_m'];
                                        $id_district_m = $s['id_district_m'];
                                        $t1 = $targets[$sid][1] ?? null;
                                        $t2 = $targets[$sid][2] ?? null;
                                        $t3 = $targets[$sid][3] ?? null;
                                        $t4 = $targets[$sid][4] ?? null;
                                        $t5 = $targets[$sid][5] ?? null;

                                        $act = $actuals[$sid] ?? 0.0;

                                        ob_start();
                                        echo '<div class="row" data-row="' . htmlspecialchars(strtolower("$store $sm $dm")) . '">';
                                        echo '  <div class="store"><div class="name">' . htmlspecialchars($store) . '</div><div class="meta">DM: ' . htmlspecialchars($dm) . '<br>SM: ' . htmlspecialchars($sm) . '</div></div>';

                                        if ($t1 === null || $t5 === null) {
                                            echo '  <div class="bar-wrap"><div class="fill" style="width:0%"></div></div>';
                                            echo '  <div class="kpi"><div class="val">' . fmtMoney($act, $CURRENCY) . '</div><div class="prize">Premio: ' . fmtMoney(0, $CURRENCY) . '</div></div>';
                                            echo '</div>';
                                            return ob_get_clean();
                                        }

                                        // ===== Scala ancorata a T1 =====
                                        $maxScaleAbs = max((float) $t5, (float) $act, (float) $t1 + 0.01);      // massimo assoluto (>= T1)
                                        $den = max($maxScaleAbs - (float) $t1, 0.01);                         // denominatore (range da T1 a max)
                                        $pos = function (float $x) use ($t1, $den): float {
                                            return max(0.0, min(100.0, (($x - (float) $t1) / $den) * 100.0));
                                        };

                                        $fillPct = $pos((float) $act);                                        // 0% se actual < T1
                                        $p1 = 0.0;                                                           // T1 = inizio barra
                                        $p2 = $t2 !== null ? $pos((float) $t2) : null;
                                        $p3 = $t3 !== null ? $pos((float) $t3) : null;
                                        $p4 = $t4 !== null ? $pos((float) $t4) : null;
                                        $p5 = $t5 !== null ? $pos((float) $t5) : null;

                                        // Tier raggiunto (valutazione assoluta, NON relativa)
                                        $tierReached = 0;
                                        if ($act >= $t1)
                                            $tierReached = 1;
                                        if ($t2 !== null && $act >= $t2)
                                            $tierReached = 2;
                                        if ($t3 !== null && $act >= $t3)
                                            $tierReached = 3;
                                        if ($t4 !== null && $act >= $t4)
                                            $tierReached = 4;
                                        if ($t5 !== null && $act >= $t5)
                                            $tierReached = 5;
                                        $premi = computePrize($act, $tierReached, $prizes, ($percBudget / 100), ($percDistrict / 100));

                                        $edge = function (float $pct) {
                                            if ($pct < 6)
                                                return ' left-edge';
                                            if ($pct > 94)
                                                return ' right-edge';
                                            return '';
                                        };

                                        echo '  <div class="bar-block">';
                                        echo '    <div class="labels">';
                                        if ($_SESSION['id'] == $id_store_m || $_SESSION['id'] == $id_district_m || $_SESSION['livello'] <= 1 || $_SESSION['ruolo'] == RISORSE_UMANE) {
                                            if ($p2 !== null)
                                                echo ' <div class="tick-badge' . $edge($p2) . '" style="left:' . number_format($p2, 2, '.', '') . '%"><span style="color:#ff0000;">S1:</span> ' . fmtMoney($t2, $CURRENCY) . '<br>SM: +' . fmtMoney($prizes[2], $CURRENCY) . ($_SESSION['id'] == $id_district_m || $_SESSION['livello'] <= 1 ? ' DM: +' . fmtMoney($prizes[2] * $percDistrict / 100, $CURRENCY) : '') . '</div>';
                                            if ($p4 !== null)
                                                echo ' <div class="tick-badge' . $edge($p4) . '" style="left:' . number_format($p4, 2, '.', '') . '%"><span style="color:#ff0000;">S3:</span> ' . fmtMoney($t4, $CURRENCY) . '<br>SM: +' . fmtMoney($prizes[4], $CURRENCY) . ($_SESSION['id'] == $id_district_m || $_SESSION['livello'] <= 1 ? ' DM: +' . fmtMoney($prizes[4] * $percDistrict / 100, $CURRENCY) : '') . '</div>';
                                        }
                                        echo '    </div>';
                                        echo '    <div class="bar-wrap">';
                                        echo '      <div class="fill" style="width:' . number_format($fillPct, 2, '.', '') . '%;"></div>';
                                        echo '      <div class="ticks">';
                                        echo '        <div class="tick" style="left:0%"></div>';
                                        if ($_SESSION['id'] == $id_store_m || $_SESSION['id'] == $id_district_m || $_SESSION['livello'] <= 1 || $_SESSION['ruolo'] == RISORSE_UMANE) {
                                            if ($p2 !== null)
                                                echo '   <div class="tick" style="left:' . number_format($p2, 2, '.', '') . '%"></div>';
                                            if ($p3 !== null)
                                                echo '   <div class="tick" style="left:' . number_format($p3, 2, '.', '') . '%"></div>';
                                            if ($p4 !== null)
                                                echo '   <div class="tick" style="left:' . number_format($p4, 2, '.', '') . '%"></div>';
                                            if ($p5 !== null)
                                                echo '   <div class="tick" style="left:' . number_format($p5, 2, '.', '') . '%"></div>';
                                        }
                                        echo '      </div>';
                                        echo '    </div>';

                                        echo '    <div class="labels">';
                                        echo '      <div class="tick-badge' . $edge($p1) . '" style="left:0%">' . fmtMoney($t1, $CURRENCY) . '</div>';
                                        if ($_SESSION['id'] == $id_store_m || $_SESSION['id'] == $id_district_m || $_SESSION['livello'] <= 1 || $_SESSION['ruolo'] == RISORSE_UMANE) {
                                            if ($p3 !== null)
                                                echo ' <div class="tick-badge' . $edge($p3) . '" style="left:' . number_format($p3, 2, '.', '') . '%"><span style="color:#ff0000;">S2:</span> ' . fmtMoney($t3, $CURRENCY) . '<br>SM: +' . fmtMoney($prizes[3], $CURRENCY) . ($_SESSION['id'] == $id_district_m || $_SESSION['livello'] <= 1 ? ' DM: +' . fmtMoney($prizes[3] * $percDistrict / 100, $CURRENCY) : '') . '</div>';
                                            if ($p5 !== null)
                                                echo ' <div class="tick-badge' . $edge($p5) . '" style="left:' . number_format($p5, 2, '.', '') . '%"><span style="color:#ff0000;">S4:</span> ' . fmtMoney($t5, $CURRENCY) . '<br>SM: +' . fmtMoney($prizes[5], $CURRENCY) . ($_SESSION['id'] == $id_district_m || $_SESSION['livello'] <= 1 ? ' DM: +' . fmtMoney($prizes[5] * $percDistrict / 100, $CURRENCY) : '') . '</div>';
                                        }
                                        echo '    </div>';
                                        echo '  </div>';

                                        echo '  <div class="kpi"><div class="val">Fatturato attuale: ' . fmtMoney($act, $CURRENCY) . '</div>'
                                        . '<div class="prize">Premio ' . $percBudget . '%</strong></div>';
                                        # lista addetti vendita che hanno preso il premio
                                        $arrChart = [];
                                        if ($premi[0] > 0) {
                                            $qry = "select u.nome, u.cognome, j.total_sold_amount from utenti u inner join operator_month_kpis j on j.operator_code=u.id where u.livello='3' and kpi_year=$selY and kpi_month=$selM and store_code=$sid;";
                                            $rs = $pdo->prepare($qry);
                                            $rs->execute();
                                            while ($user = $rs->fetch(PDO::FETCH_ASSOC)) {
                                                $arrChart[strtoupper(substr($user['nome'], 0, 1)) . '. ' . $user['cognome']] = $user['total_sold_amount'];
                                                echo '<p>' . strtoupper(substr($user['nome'], 0, 1)) . '. ' . $user['cognome'] . ': ' . fmtMoney($user['total_sold_amount'], $CURRENCY) . ' <span style="color:green;font-weight:bold;">Premio ' . $percBudget . '%: ' . fmtMoney($user['total_sold_amount'] * $percBudget / 100, $CURRENCY) . '</span></p>';
                                            }
                                        }
                                        echo '<p>&nbsp;</p>';
                                        if ($_SESSION['id'] == $id_district_m || $_SESSION['livello'] <= 1 || $_SESSION['ruolo'] == RISORSE_UMANE) {
                                            echo ((float) $premi[2] > 0 ? '<div class="prize">Premio DM: <strong>' . fmtMoney($premi[2], $CURRENCY) . '</strong></div>' : '')
                                            . ((float) $premi[1] > 0 ? '<div class="prize">Premio SM: <strong>' . fmtMoney($premi[1], $CURRENCY) . '</strong></div>' : '');
                                        } elseif ($_SESSION['id'] == $id_store_m) {
                                            echo ((float) $premi[1] > 0 ? '<div class="prize">Premio SM: <strong>' . fmtMoney($premi[1], $CURRENCY) . '</strong></div>' : '');
                                        } else {
                                            
                                        }
                                        echo '</div>';
                                        echo '</div>';
                                        echo '<div id="chart' . $sid . '" style="width:40%;margin:0 auto;"></div>';
                                        if (count($arrChart) > 0) {
                                            ?>
                                            <script>
                                                $(function() {
                                                var canvas2 = "<canvas id=\"myChart<?= $sid ?>\" style=\"width:100%;\"></canvas>";
                                                $('#chart<?= $sid ?>').html("").append(canvas2);
                                                var labels2 = new Array();
                                                var valori2 = new Array();
                    <?php
                    foreach ($arrChart as $name_op => $chart) {
                        echo 'labels2.push("' . trim($name_op) . '");' . "\n";
                        echo 'valori2.push(' . $chart . ')' . ";\n";
                    }
                    ?>
                                                //console.log(labels2);
                                                var ctx2 = document.getElementById("myChart<?= $sid ?>");
                                                var myChart2 = new Chart(ctx2, {
                                                type: 'pie',
                                                        data: {
                                                        labels: labels2,
                                                                datasets: [{
                                                                label: 'fatturato',
                                                                        data: valori2,
                                                                        backgroundColor: [
                                                                                "#009b00",
                                                                                "#FF6384",
                                                                                "#36A2EB",
                                                                                "#FFCE56",
                                                                                "#ab11de",
                                                                                "#de7600",
                                                                                "#de0000",
                                                                                "#0039de",
                                                                                "#ff0000"
                                                                        ],
                                                                        borderColor: '#E9E9E9',
                                                                        borderWidth: 3
                                                                }]
                                                        },
                                                        options: {
                                                        tooltips: {
                                                        callbacks: {
                                                        label: function (tooltipItem, data) {
                                                        var allData = data.datasets[tooltipItem.datasetIndex].data;
                                                        var tooltipLabel = data.labels[tooltipItem.index];
                                                        var tooltipData = allData[tooltipItem.index];
                                                        var total = 0;
                                                        for (var i in allData) {
                                                        total += allData[i];
                                                        }
                                                        var tooltipPercentage = Math.round((tooltipData / total) * 100);
                                                        return tooltipLabel + ': ' + tooltipData + ' (' + tooltipPercentage + '%)';
                                                        }
                                                        }
                                                        }
                                                        }
                                                });
                                                });
                                            </script>
                                            <?php
                                        }
                                        return ob_get_clean();
                                    }

                                    foreach ($stores as $s) {
                                        echo rowHtml($s, $targets, $actuals, $prizes, $CURRENCY, $percBudget, $percDistrict);
                                    }
                                    ?>
                                </div>
                            </div>

                            <script>
                                // Filtro client-side
                                const q = document.getElementById('q');
                                const rows = Array.from(document.querySelectorAll('.row[data-row]'));
                                q.addEventListener('input', () => {
                                const v = q.value.toLowerCase().trim();
                                rows.forEach(r => r.style.display = v === '' || r.dataset.row.includes(v) ? '' : 'none');
                                });
                            </script>
                            <?php
                            ## GARE ##
                            $curY = (int) date('Y');
                            $curM = (int) date('n');
                            $curG = (int) date('d');
                            $dateTime = new DateTime();
                            $dateTime->modify('-1 MONTH');
                            $prev_month = $dateTime->format('m');
                            $pdo = $db;
                            // ------- INPUT -------
                            $year = $selY;
                            $month = $selM;
                            $from = sprintf('%04d-%02d-01', (int) $year, (int) $month);
                            $to = date('Y-m-d', strtotime($from . ' +1 month'));
// opzionale: limitare a uno store (per ora non mostrato in UI)
                            $storeFilter = isset($_GET['store']) ? trim((string) $_GET['store']) : null;

// PIECES_FORMULA: per Gara 1 puoi scegliere cosa intendi per "pezzi venduti"
// - 'dresses'  => solo abiti
// - 'dresses_plus_accessories' => abiti+accessori
                            $piecesFormula = 'dresses'; // cambia qui se vuoi includere gli accessori

// ------- HELPERS -------
                            function fetchAll(PDO $pdo, string $sql, array $params = []): array {
                                $stmt = $pdo->prepare($sql);
                                $stmt->execute($params);
                                return $stmt->fetchAll(PDO::FETCH_ASSOC);
                            }

                            function h(?string $s): string {
                                return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
                            }

                            function monthName(int $m): string {
                                $map = [1 => 'Gen', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mag', 6 => 'Giu', 7 => 'Lug', 8 => 'Ago', 9 => 'Set', 10 => 'Ott', 11 => 'Nov', 12 => 'Dic'];
                                return $map[$m] ?? (string) $m;
                            }

// ------- LOOKUP NEGOZI / OPERATORI -------
                            $stores = fetchAll($pdo, "SELECT id as store_code, nominativo AS name FROM utenti where livello='5' and attivo=1 ORDER BY name;");
                            $storeName = function (string $code) use ($stores): string {
                                foreach ($stores as $s)
                                    if ($s['store_code'] === $code)
                                        return (string) $s['name'];
                                return $code;
                            };
                            $operators = fetchAll($pdo, "SELECT id as operator_code, CONCAT(cognome,' ', nome) AS name FROM utenti where livello='3' and attivo=1 order by name;");
                            $operatorName = function (string $code) use ($operators): string {
                                foreach ($operators as $o)
                                    if ($o['operator_code'] === $code)
                                        return (string) $o['name'];
                                return $code;
                            };
                            // ------- QUERY: GARE -------
// GARA 1: Miglior rapporto recensioni / pezzi venduti (per negozio)
                            if ($piecesFormula === 'dresses_plus_accessories') {
                                $piecesExpr = 'COALESCE(k.dresses_sold,0) + COALESCE(k.accessories_sold,0)';
                            } else {
                                $piecesExpr = 'COALESCE(SUM(k.dresses_sold),0)'; // default: solo abiti
                            }
//                        $sql1 = "
//SELECT
//  s.store_code,
//  COALESCE(r.reviews_ranking,0) AS reviews_count,
//  $piecesExpr                 AS pieces_sold,
//  CASE
//    WHEN $piecesExpr = 0 THEN NULL
//    ELSE COALESCE(r.reviews_ranking,0) / NULLIF($piecesExpr,0)
//  END AS ratio,
//  COALESCE(SUM(CASE WHEN gr.rating > 0 THEN 1 ELSE 0 END),0) AS reviews_pos,
//  COALESCE(SUM(CASE WHEN gr.rating < 0 THEN 1 ELSE 0 END),0) AS reviews_neg
//FROM operator_month_kpis k
//JOIN (SELECT DISTINCT id as store_code FROM utenti where livello='5' and attivo=1) s ON s.store_code = k.store_code
//LEFT JOIN store_monthly_reviews r
//  ON r.store_code = k.store_code AND r.year = k.kpi_year AND r.month = k.kpi_month
//WHERE k.kpi_year = :y AND k.kpi_month = :m
//" . ($storeFilter ? " AND k.store_code = :store " : "") . " group by s.store_code
//UNION
///*nel caso un negozio abbia solo recensioni ma 0 righe in kpis, lo includiamo a 0 pezzi*/
//SELECT
//  r.store_code,
//  COALESCE(r.reviews_ranking,0) AS reviews_count,
//  0 AS pieces_sold,
//  NULL AS ratio,
//  COALESCE(SUM(CASE WHEN gr.rating > 0 THEN 1 ELSE 0 END),0) AS reviews_pos,
//  COALESCE(SUM(CASE WHEN gr.rating < 0 THEN 1 ELSE 0 END),0) AS reviews_neg
//FROM store_monthly_reviews r
//LEFT JOIN operator_month_kpis k
//  ON k.store_code = r.store_code AND r.year = k.kpi_year AND r.month = k.kpi_month
//WHERE k.kpi_year = :y AND k.kpi_month = :m
//" . ($storeFilter ? " AND r.store_code = :store " : "") . "
//AND k.store_code IS NULL
//ORDER BY ratio DESC, ratio DESC, reviews_count DESC
//";
                            $sql1 = "SELECT
  s.store_code,
  COALESCE(r.reviews_ranking, 0) AS reviews_count,
  COALESCE(k.pieces_sold, 0)     AS pieces_sold,
  CASE
    WHEN COALESCE(k.pieces_sold,0) = 0 THEN NULL
    ELSE COALESCE(r.reviews_ranking,0) / NULLIF(k.pieces_sold,0)
  END                             AS ratio,
  COALESCE(gr.reviews_pos, 0)     AS reviews_pos,
  COALESCE(gr.reviews_neg, 0)     AS reviews_neg
FROM (
  SELECT DISTINCT id AS store_code
  FROM utenti
  WHERE livello = '5' AND attivo = 1
) s
JOIN (
  SELECT store_code, kpi_year, kpi_month,
         SUM(dresses_sold) AS pieces_sold
  FROM operator_month_kpis
  WHERE kpi_year = :y AND kpi_month = :m
  GROUP BY store_code, kpi_year, kpi_month
) k
  ON k.store_code = s.store_code
LEFT JOIN store_monthly_reviews r
  ON r.store_code = k.store_code
 AND r.year = k.kpi_year
 AND r.month = k.kpi_month
LEFT JOIN (
  SELECT store_code,
         SUM(CASE WHEN rating > 0 THEN 1 ELSE 0 END) AS reviews_pos,
         SUM(CASE WHEN rating < 0 THEN 1 ELSE 0 END) AS reviews_neg
  FROM (
    SELECT DISTINCT review_id, store_code, rating
    FROM gbp_reviews
    WHERE YEAR(create_time) = :y
      AND MONTH(create_time) = :m
  ) dr
  GROUP BY store_code
) gr
  ON gr.store_code = k.store_code

UNION

-- Negozi con sole review (nessuna riga KPI nel mese)
SELECT
  gr.store_code,
  COALESCE(r.reviews_ranking, 0) AS reviews_count,
  0                              AS pieces_sold,
  NULL                           AS ratio,
  gr.reviews_pos,
  gr.reviews_neg
FROM (
  SELECT store_code,
         SUM(CASE WHEN rating > 0 THEN 1 ELSE 0 END) AS reviews_pos,
         SUM(CASE WHEN rating < 0 THEN 1 ELSE 0 END) AS reviews_neg
  FROM (
    SELECT DISTINCT review_id, store_code, rating
    FROM gbp_reviews
    WHERE YEAR(create_time) = :y
      AND MONTH(create_time) = :m
  ) dr
  GROUP BY store_code
) gr
LEFT JOIN (
  SELECT store_code
  FROM operator_month_kpis
  WHERE kpi_year = :y AND kpi_month = :m
  GROUP BY store_code
) k
  ON k.store_code = gr.store_code
LEFT JOIN store_monthly_reviews r
  ON r.store_code = gr.store_code
 AND r.year = :y
 AND r.month = :m
WHERE k.store_code IS NULL

ORDER BY ratio DESC, reviews_count DESC;
";
                            //echo $sql1;
                            $params1 = [':y' => $year, ':m' => $month];
                            if ($storeFilter)
                                $params1[':store'] = $storeFilter;
                            //$gara1 = fetchAll($pdo, $sql1, $params1);
                            $gara1 = getClassificaGara($pdo, $month, $year, 1);

// GARA 2: Maggior numero di recensioni assolute (per negozio)
                            $sql2 = "
SELECT r.store_code, r.reviews_ranking,
COALESCE(gr.reviews_pos, 0)     AS reviews_pos,
  COALESCE(gr.reviews_neg, 0)     AS reviews_neg
FROM store_monthly_reviews r 
LEFT JOIN (
  SELECT store_code,
         SUM(CASE WHEN rating > 0 THEN 1 ELSE 0 END) AS reviews_pos,
         SUM(CASE WHEN rating < 0 THEN 1 ELSE 0 END) AS reviews_neg
  FROM (
    SELECT DISTINCT review_id, store_code, rating
    FROM gbp_reviews
    WHERE YEAR(create_time) = :y
      AND MONTH(create_time) = :m
  ) dr
  GROUP BY store_code
) gr
  ON gr.store_code = r.store_code
WHERE r.year = :y AND r.month = :m
" . ($storeFilter ? " AND r.store_code = :store " : "") . "
ORDER BY r.reviews_ranking DESC, r.store_code ASC
";
                            //die($sql2);
                            $params2 = [':y' => $year, ':m' => $month];
                            if ($storeFilter)
                                $params2[':store'] = $storeFilter;
                            //$gara2 = fetchAll($pdo, $sql2, $params2);
                            $gara2 = getClassificaGara($pdo, $month, $year, 2);
// GARA 3: Miglior rapporto accessori/abiti (per operatore/negozio)
                            $sql3 = "
SELECT
  k.store_code,
  SUM(k.accessories_sold) as accessories_sold,
  SUM(k.dresses_sold) as dresses_sold,
  CASE WHEN dresses_sold=0 THEN NULL
       ELSE accessories_sold / NULLIF(dresses_sold,0)
  END AS ratio
FROM operator_month_kpis k
WHERE k.kpi_year = :y AND k.kpi_month = :m
" . ($storeFilter ? " AND k.store_code = :store " : "") . "
GROUP BY k.store_code HAVING accessories_sold >= 20
ORDER BY ratio DESC, ratio DESC, k.accessories_sold DESC limit 40
";
                            //echo "$sql3";
                            $params3 = [':y' => $year, ':m' => $month];
                            if ($storeFilter)
                                $params3[':store'] = $storeFilter;
                            //$gara3 = fetchAll($pdo, $sql3, $params3);
                            $gara3 = getClassificaGara($pdo, $month, $year, 3);

// GARA 4: Miglior venditrice del mese per conversioni
                            function ordinaTop40(array $gara4_list) {
                                // Ordina decrescente per convPerc (rimuove il simbolo % per confrontare numericamente)
                                usort($gara4_list, function ($a, $b) {
                                    return intval($b['convPerc']) - intval($a['convPerc']);
                                });

                                // Mantiene solo i primi 40
                                return array_slice($gara4_list, 0, 40);
                            }

                            $gara4 = getClassificaGara($pdo, $month, $year, 4, 0);
                            $gara4_v = getClassificaGara($pdo, $month, $year, 4, 1);
                            $gara5 = getClassificaGara($pdo, $month, $year, 6);
                            ?>
                            <style>
                                :root{
                                    --bg:#f8fafc;
                                    --card:#ffffff;
                                    --muted:#6b7280;
                                    --text:#0f172a;
                                    --accent:#2563eb;
                                    --border:#e5e7eb;
                                    --good:#16a34a;
                                    --warn:#f59e0b;
                                    --bad:#dc2626;
                                    --chip:#eef2ff;
                                }
                                *{
                                    box-sizing:border-box
                                }
                                body{
                                    margin:0;
                                    background:var(--bg);
                                    color:var(--text);
                                    font-family:system-ui,Segoe UI,Roboto,Arial,sans-serif
                                }
                                header{
                                    position:sticky;
                                    top:0;
                                    z-index:10;
                                    background:rgba(255,255,255,.8);
                                    backdrop-filter:blur(8px);
                                    border-bottom:1px solid var(--border);
                                }
                                .wrap{
                                    max-width:1600px;
                                    margin:0 auto;
                                    padding:16px 20px
                                }
                                h1{
                                    margin:0;
                                    font-size:22px
                                }
                                .sub{
                                    color:var(--muted);
                                    font-size:13px;
                                    margin-top:4px
                                }
                                .filters{
                                    display:flex;
                                    gap:8px;
                                    align-items:center;
                                    margin-top:12px;
                                    flex-wrap:wrap
                                }
                                .filters input,.filters select{
                                    border:1px solid var(--border);
                                    border-radius:10px;
                                    padding:8px 10px;
                                    background:#fff;
                                    color:var(--text)
                                }
                                .grid{
                                    max-width:1600px;
                                    margin:18px auto;
                                    display:grid;
                                    gap:16px;
                                    grid-template-columns: repeat(12, 1fr);
                                    padding:0 20px 24px;
                                }
                                .card{
                                    grid-column: span 12;
                                    background:var(--card);
                                    border:1px solid var(--border);
                                    border-radius:16px;
                                    padding:16px
                                }
                                @media(min-width:900px){
                                    .card.half{
                                        grid-column: span 6
                                    }
                                }
                                .card h2{
                                    margin:0 0 10px;
                                    font-size:18px
                                }
                                .card h3{
                                    margin:0 0 10px;
                                    font-size:15px;
                                    font-weight: bold;
                                }
                                table{
                                    width:100%;
                                    border-collapse:separate;
                                    border-spacing:0
                                }
                                thead th{
                                    text-align:left;
                                    font-size:12px;
                                    color:var(--muted);
                                    font-weight:600;
                                    border-bottom:1px solid var(--border);
                                    padding:10px 8px
                                }
                                tbody td{
                                    padding:10px 8px;
                                    border-bottom:1px solid var(--border);
                                    font-size:14px
                                }
                                tbody tr:hover{
                                    background:#fafafa
                                }
                                .rank{
                                    width:36px;
                                    text-align:center;
                                    font-weight:700
                                }
                                .chip{
                                    display:inline-block;
                                    background:var(--chip);
                                    color:var(--accent);
                                    font-weight:600;
                                    border-radius:999px;
                                    padding:4px 10px;
                                    font-size:12px
                                }
                                .muted{
                                    color:var(--muted)
                                }
                                .kpi{
                                    font-weight:700
                                }
                                .pill{
                                    display:inline-block;
                                    border-radius:8px;
                                    padding:2px 8px;
                                    background:#f1f5f9;
                                    font-size:12px;
                                    color:#0f172a;
                                    border:1px solid var(--border)
                                }
                                .rowhead{
                                    display:flex;
                                    align-items:center;
                                    gap:8px
                                }
                                .store-badge{
                                    background:#eef2ff;
                                    color:#3730a3;
                                    border:1px solid #c7d2fe
                                }
                                .op-badge{
                                    background:#ecfeff;
                                    color:#155e75;
                                    border:1px solid #bae6fd
                                }
                                .footnote{
                                    font-size:12px;
                                    color:var(--muted);
                                    margin-top:6px
                                }
                                .tag{
                                    font-size:11px;
                                    color:#334155;
                                    background:#f1f5f9;
                                    border:1px solid var(--border);
                                    padding:2px 6px;
                                    border-radius:6px
                                }
                                .tit-gara {
                                    cursor:pointer;
                                    width: 100%;
                                    grid-column: span 12;
                                    font-size: 18px;
                                }
                                .cnt-gara {
                                    display: none;
                                    grid-column: span 12;
                                    margin: 0 0 20px 0;
                                }
                            </style>
                            <header>
                                <div class="wrap">
                                    <h1>Classifiche del mese di <?= $arrmesiesteso[$month] ?></h1>
                                    <div class="sub">Mese: <?= h(monthName($month)) ?> <?= h((string) $year) ?></div>
                                    <!--                                <form class="filters" method="get">
                                                                        <label>Anno
                                                                            <select name="year">
                                    <?php for ($y = date('Y') - 2; $y <= date('Y') + 1; $y++): ?>
                                                                                                                        <option value="<?= $y ?>" <?= $y === $year ? 'selected' : '' ?>><?= $y ?></option>
            <?php endfor; ?>
                                                                            </select>
                                                                        </label>
                                                                        <label>Mese
                                                                            <select name="month">
                                    <?php
                                    for ($m = 1; $m <= 12; $m++):
                                        $optM = (int) $m;
                                        if ($_SESSION['livello'] <= 1 || ($curG <= 15 && $optM == $prev_month) || $optM == $curM) {
                                            ?>
                                                                                                                                                            <option value="<?= $m ?>" <?= $m === $month ? 'selected' : '' ?>><?= h(monthName($m)) ?></option>
                <?php } endfor; ?>
                                                                            </select>
                                                                        </label>
            <?php if ($_SESSION['livello'] <= 1) { ?>
                                                                                                                <label>Negozio
                                                                                                                    <select name="store">
                                                                                                                        <option value="">Tutti</option>
                                        <?php foreach ($stores as $s): ?>
                                                                                                                                                                <option value="<?= h($s['store_code']) ?>" <?= ($storeFilter === $s['store_code']) ? 'selected' : '' ?>>
                                            <?= h($s['name']) ?>
                                                                                                                                                                </option>
                <?php endforeach; ?>
                                                                                                                    </select>
                                                                                                                </label>
            <?php } ?>
                                                                        <button class="pill" type="submit">Aggiorna</button>
                                                                    </form>-->
                                </div>
                            </header>
                            <!-- GARA 1 -->
                            <section class="grid">
                                <h2 class="tit-gara">1) Miglior rapporto recensioni / abiti venduti</h2>
                                <div id="gara1" class="cnt-gara">
                                    <?php
                                    $qry = "select * from premi_gare where num_gara=1 order by valido_fino desc limit 1;";
                                    $rs_p = $pdo->prepare($qry);
                                    $rs_p->execute();
                                    $premi_g1 = $rs_p->fetch(PDO::FETCH_ASSOC);
                                    ?>
                                    <div style="font-size: 14px;line-height: 120%;margin-bottom: 30px;">
            <?php if (count($gara1) > 0) { ?>
                                            <h3><b>Classifica aggiornata al <?= formatDateOra($gara1[0]['last_update']) ?></b></h3>
                                            <p>&nbsp;<p/>
                                        <?php } ?>
                                        <p>Premio al primo classificato: </p>
            <?php if ($_SESSION['livello'] <= 1 || $_SESSION['ruolo'] == RISORSE_UMANE || $_SESSION['ruolo'] == DISTRICT_MANAGER || $_SESSION['ruolo'] == STORE_MANAGER) { ?>
                                            <p>Store manager: <b><?= fmtMoney($premi_g1['premio_sm'], $CURRENCY) ?></b></p>
                                            <p>Addetto vendita: <b><?= fmtMoney($premi_g1['premio_av'], $CURRENCY) ?></b></p>
                                        <?php } else { ?>
                                            <p>Addetto vendita: <b><?= fmtMoney($premi_g1['premio_av'], $CURRENCY) ?></b></p>
            <?php } ?>
                                    </div>
                                    <div class="card half">
                                        <div class="legenda">
                                            <p><b>Legenda</b></p>
                                            <p>Recensioni positive: 4 o 5 stelle, valore ranking 1</p>
                                            <p>Recensioni negative: 3 stelle valore ranking -1, 2 stelle valore ranking -2, 1 stella valore ranking -3</p>
                                            <p>Ranking recensioni: somma dei valori</p>
                                            <p>&nbsp;</p>
                                        </div>
                                        <h3>Negozi</h3>
                                        <table>
                                            <thead>
                                                <tr>
                                                    <th class="rank">#</th>
                                                    <th>Negozio</th>
                                                    <?php if ($_SESSION['ruolo'] != ADDETTO_VENDITE || $_SESSION['livello'] <= 1 || $_SESSION['ruolo'] == RISORSE_UMANE) { ?>
                                                        <th>Store manager</th>
                                                    <?php } ?>
                                                    <?php if ($_SESSION['ruolo'] == DISTRICT_MANAGER || $_SESSION['livello'] <= 1 || $_SESSION['ruolo'] == RISORSE_UMANE) { ?>
                                                        <th>District manager</th>
            <?php } ?>
                                                    <th>Recensioni positive</th>
                                                    <th>Recensioni negative</th>
                                                    <th>Ranking Recensioni</th>
                                                    <th>Pezzi</th>
                                                    <th>Rapporto</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (!$gara1): ?>
                                                    <tr><td colspan="5" class="muted">Nessun dato</td></tr>
                                                    <?php
                                                else: $i = 1;
                                                    foreach ($gara1 as $row):
                                                        $storeM = getSmDmStore($row['store_code'], $year, $month, 'storeM_id', 1);
                                                        $districtM = getSmDmStore($row['store_code'], $year, $month, 'districtM_id', 2);
                                                        ?>
                                                        <tr<?= ($row['store_code'] == $_SESSION['id'] ? ' style="background-color:#cccccc;"' : '') ?>>
                                                            <td class="rank"><?= $row['rank'] ?></td>
                                                            <td class="rowhead">
                                                                <span><?= h($storeName($row['store_code'])) . ($row['rank'] == 1 ? ' <i class="fa fa-trophy" aria-hidden="true"></i> ' : '') ?></span>
                                                            </td>
                    <?php if ($_SESSION['ruolo'] != ADDETTO_VENDITE || $_SESSION['livello'] <= 1 || $_SESSION['ruolo'] == RISORSE_UMANE) { ?>
                                                                <td>
                                                                    <span><?= h($storeM['nome'] . ' ' . $storeM['cognome']) ?></span>
                                                                </td>
                                                            <?php } ?>
                    <?php if ($_SESSION['ruolo'] == DISTRICT_MANAGER || $_SESSION['livello'] <= 1 || $_SESSION['ruolo'] == RISORSE_UMANE) { ?>
                                                                <td>
                                                                    <span><?= h($districtM['nome'] . ' ' . $districtM['cognome']) ?></span>
                                                                </td>
                    <?php } ?>
                                                            <td>
                                                                <span><?= h((string) $row['rec_pos']) ?></span>
                                                            </td>
                                                            <td>
                                                                <span><?= h((string) $row['rec_neg']) ?></span>
                                                            </td>
                                                            <td class="kpi"><?= h((string) $row['rec_ranking']) ?></td>
                                                            <td><?= h((string) $row['pezzi']) ?></td>
                                                            <td>
                                                                <?php if ($row['rapporto'] === null): ?>
                                                                    <span class="muted">—</span>
                                                                <?php else: ?>
                                                                    <span class="kpi"><?= number_format((float) $row['rapporto'], 3, ',', '.') ?></span>
                    <?php endif; ?>
                                                            </td>
                                                        </tr>
                                                        <?php
                                                    endforeach;
                                                endif;
                                                ?>
                                            </tbody>
                                        </table>
                                        <div class="footnote">Ordine: rapporto desc, poi recensioni.</div>
                                    </div>
                                </div>
                                <h2 class="tit-gara">2) Maggior numero di recensioni</h2>
                                <?php
                                $qry = "select * from premi_gare where num_gara=2 order by valido_fino desc limit 1;";
                                $rs_p = $pdo->prepare($qry);
                                $rs_p->execute();
                                $premi_g2 = $rs_p->fetch(PDO::FETCH_ASSOC);
                                ?>
                                <div id="gara2" class="cnt-gara">
            <?php if (count($gara2) > 0) { ?>
                                        <h3><b>Classifica aggiornata al <?= formatDateOra($gara2[0]['last_update']) ?></b></h3>
                                        <p>&nbsp;<p/>
            <?php } ?>
                                    <div style="font-size: 14px;line-height: 120%;margin-bottom: 30px;">
                                        <p>Premio al primo classificato: </p>
            <?php if ($_SESSION['livello'] <= 1 || $_SESSION['ruolo'] == RISORSE_UMANE || $_SESSION['ruolo'] == DISTRICT_MANAGER || $_SESSION['ruolo'] == STORE_MANAGER) { ?>
                                            <p>Store manager: <b><?= fmtMoney($premi_g2['premio_sm'], $CURRENCY) ?></b></p>
                                            <p>Addetto vendita: <b><?= fmtMoney($premi_g2['premio_av'], $CURRENCY) ?></b></p>
                                        <?php } else { ?>
                                            <p>Addetto vendita: <b><?= fmtMoney($premi_g2['premio_av'], $CURRENCY) ?></b></p>
            <?php } ?>
                                    </div>
                                    <!-- GARA 2 -->
                                    <div class="card half">
                                        <div class="legenda">
                                            <p><b>Legenda</b></p>
                                            <p>Recensioni positive: 4 o 5 stelle, valore ranking 1</p>
                                            <p>Recensioni negative: 3 stelle valore ranking -1, 2 stelle valore ranking -2, 1 stella valore ranking -3</p>
                                            <p>Ranking recensioni: somma dei valori</p>
                                            <p>&nbsp;</p>
                                        </div>
                                        <h3>Negozi</h3>
                                        <table>
                                            <thead>
                                                <tr>
                                                    <th class="rank">#</th>
                                                    <th>Negozio</th>
                                                    <?php if ($_SESSION['ruolo'] != ADDETTO_VENDITE || $_SESSION['livello'] <= 1 || $_SESSION['ruolo'] == RISORSE_UMANE) { ?>
                                                        <th>Store manager</th>
                                                    <?php } ?>
                                                    <?php if ($_SESSION['ruolo'] == DISTRICT_MANAGER || $_SESSION['livello'] <= 1 || $_SESSION['ruolo'] == RISORSE_UMANE) { ?>
                                                        <th>District manager</th>
            <?php } ?>
                                                    <th>Recensioni positive</th>
                                                    <th>Recensioni negative</th>
                                                    <th>Ranking Recensioni</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (!$gara2): ?>
                                                    <tr><td colspan="3" class="muted">Nessun dato</td></tr>
                                                    <?php
                                                else: $i = 1;
                                                    foreach ($gara2 as $row):
                                                        $storeM = getSmDmStore($row['store_code'], $year, $month, 'storeM_id', 1);
                                                        $districtM = getSmDmStore($row['store_code'], $year, $month, 'districtM_id', 2);
                                                        ?>
                                                        <tr<?= ($row['store_code'] == $_SESSION['id'] ? ' style="background-color:#cccccc;"' : '') ?>>
                                                            <td class="rank"><?= $row['rank'] ?></td>
                                                            <td class="rowhead">
                                                                <span><?= h($storeName($row['store_code'])) . ($row['rank'] == 1 ? ' <i class="fa fa-trophy" aria-hidden="true"></i> ' : '') ?></span>
                                                            </td>
                    <?php if ($_SESSION['ruolo'] != ADDETTO_VENDITE || $_SESSION['livello'] <= 1 || $_SESSION['ruolo'] == RISORSE_UMANE) { ?>
                                                                <td>
                                                                    <span><?= h($storeM['nome'] . ' ' . $storeM['cognome']) ?></span>
                                                                </td>
                                                            <?php } ?>
                    <?php if ($_SESSION['ruolo'] == DISTRICT_MANAGER || $_SESSION['livello'] <= 1 || $_SESSION['ruolo'] == RISORSE_UMANE) { ?>
                                                                <td>
                                                                    <span><?= h($districtM['nome'] . ' ' . $districtM['cognome']) ?></span>
                                                                </td>
                    <?php } ?>
                                                            <td>
                                                                <span><?= h((string) $row['rec_pos']) ?></span>
                                                            </td>
                                                            <td>
                                                                <span><?= h((string) $row['rec_neg']) ?></span>
                                                            </td>
                                                            <td class="kpi"><?= h((string) $row['rec_ranking']) ?></td>
                                                        </tr>
                                                        <?php
                                                    endforeach;
                                                endif;
                                                ?>
                                            </tbody>
                                        </table>
                                        <div class="footnote">Ordine: recensioni desc.</div>
                                    </div>    
                                </div>
                                <?php if ($_SESSION['ruolo'] == STORE_MANAGER || $_SESSION['ruolo'] == DISTRICT_MANAGER || $_SESSION['livello'] <= 1 || $_SESSION['ruolo'] == RISORSE_UMANE || $_SESSION['livello'] == '5') { ?>
                                    <h2 class="tit-gara">3) Miglior rapporto accessori / abiti<?= (in_array(3, $arrGareDistrict) ? ' <i class="fa fa-flag-checkered" aria-hidden="true"></i>' : '') ?></h2>
                                    <?php
                                    $qry = "select * from premi_gare where num_gara=3 order by valido_fino desc limit 1;";
                                    $rs_p = $pdo->prepare($qry);
                                    $rs_p->execute();
                                    $premi_g3 = $rs_p->fetch(PDO::FETCH_ASSOC);
                                    ?>
                                    <div id="gara3" class="cnt-gara">
                                        <div style="font-size: 14px;line-height: 120%;margin-bottom: 30px;">
                <?php if (count($gara3) > 0) { ?>
                                                <h3><b>Classifica aggiornata al <?= formatDateOra($gara3[0]['last_update']) ?></b></h3>
                                                <p>&nbsp;<p/>
                                            <?php } ?>
                                            <p>Premio al primo classificato: </p>
                <?php if ($_SESSION['livello'] <= 1 || $_SESSION['ruolo'] == DISTRICT_MANAGER || $_SESSION['ruolo'] == RISORSE_UMANE) { ?>
                                                <p>Store manager: <b><?= fmtMoney($premi_g3['premio_sm'], $CURRENCY) ?></b></p>
                                                <p>District manager: <b><?= fmtMoney($premi_g3['premio_dm'], $CURRENCY) ?></b></p>
                                            <?php } elseif ($_SESSION['ruolo'] == STORE_MANAGER) { ?>
                                                <p>Store manager: <b><?= fmtMoney($premi_g3['premio_sm'], $CURRENCY) ?></b></p>
                <?php } ?>
                                        </div>
                                        <!-- GARA 3 -->
                                        <div class="card half">
                                            <h3>Negozi</h3>
                                            <p><b>Minimo 20 accessori</b></p>
                                            <p>&nbsp;</p>
                                            <table>
                                                <thead>
                                                    <tr>
                                                        <th class="rank">#</th>
                                                        <th>Negozio</th>
                                                        <?php if ($_SESSION['ruolo'] != ADDETTO_VENDITE || $_SESSION['livello'] <= 1 || $_SESSION['ruolo'] == RISORSE_UMANE) { ?>
                                                            <th>Store manager</th>
                                                        <?php } ?>
                                                        <?php if ($_SESSION['ruolo'] == DISTRICT_MANAGER || $_SESSION['livello'] <= 1 || $_SESSION['ruolo'] == RISORSE_UMANE) { ?>
                                                            <th>District manager</th>
                <?php } ?>
                                                        <th>Accessori</th>
                                                        <th>Abiti</th>
                                                        <th>Rapporto</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php if (!$gara3): ?>
                                                        <tr><td colspan="6" class="muted">Nessun dato</td></tr>
                                                        <?php
                                                    else: $i = 1;
                                                        $SM_gara3 = $DM_gara3 = [];
                                                        foreach ($gara3 as $row):
                                                            $storeM = getSmDmStore($row['store_code'], $year, $month, 'storeM_id', 1);
                                                            if ($_SESSION['id'] == $storeM['id']) {
                                                                $SM_gara3[] = [
                                                                    'position' => $i,
                                                                    'store' => h($storeName($row['store_code']))
                                                                ];
                                                            }
                                                            $districtM = getSmDmStore($row['store_code'], $year, $month, 'districtM_id', 2);
                                                            if ($_SESSION['id'] == $districtM['id']) {
                                                                $DM_gara3[] = [
                                                                    'position' => $i,
                                                                    'store' => h($storeName($row['store_code']))
                                                                ];
                                                            }
                                                            ?>
                                                            <tr>
                                                                <td class="rank"><?= $row['rank'] ?></td>
                                                                <td class="rowhead">
                                                                    <span><?= h($storeName($row['store_code'])) . ($row['rank'] == 1 ? ' <i class="fa fa-trophy" aria-hidden="true"></i> ' : '') ?></span>
                                                                </td>
                        <?php if ($_SESSION['ruolo'] != ADDETTO_VENDITE || $_SESSION['livello'] <= 1 || $_SESSION['ruolo'] == RISORSE_UMANE) { ?>
                                                                    <td>
                                                                        <span><?= h($storeM['nome'] . ' ' . $storeM['cognome']) ?></span>
                                                                    </td>
                                                                <?php } ?>
                        <?php if ($_SESSION['ruolo'] == DISTRICT_MANAGER || $_SESSION['livello'] <= 1 || $_SESSION['ruolo'] == RISORSE_UMANE) { ?>
                                                                    <td>
                                                                        <span><?= h($districtM['nome'] . ' ' . $districtM['cognome']) ?></span>
                                                                    </td>
                        <?php } ?>
                                                                <td><?= h((string) $row['accessori']) ?></td>
                                                                <td><?= h((string) $row['abiti']) ?></td>
                                                                <td>
                                                                    <?php if ($row['rapporto'] === null): ?>
                                                                        <span class="muted">—</span>
                                                                    <?php else: ?>
                                                                        <span class="kpi"><?= number_format((float) $row['rapporto'], 3, ',', '.') ?></span>
                        <?php endif; ?>
                                                                </td>
                                                            </tr>
                                                            <?php
                                                        endforeach;
                                                    endif;
                                                    ?>
                                                </tbody>
                                            </table>
                                            <div class="footnote">Ordine: rapporto desc, poi accessori.</div>
                                        </div>
                                    </div>
                                    <!-- GARA 4 -->
                                    <h2 class="tit-gara">4) Miglior Negozio per conversioni<?= (in_array(4, $arrGareDistrict) ? ' <i class="fa fa-flag-checkered" aria-hidden="true"></i>' : '') ?></h2>
                                    <?php
                                    $qry = "select * from premi_gare where num_gara=4 order by valido_fino desc limit 1;";
                                    $rs_p = $pdo->prepare($qry);
                                    $rs_p->execute();
                                    $premi_g4 = $rs_p->fetch(PDO::FETCH_ASSOC);
                                    ?>
                                    <div id="gara4" class="cnt-gara">
                                        <div style="font-size: 14px;line-height: 120%;margin-bottom: 30px;">
                <?php if (count($gara4) > 0) { ?>
                                                <h3><b>Classifica aggiornata al <?= formatDateOra($gara4[0]['last_update']) ?></b></h3>
                                                <p>&nbsp;<p/>
                                            <?php } ?>
                                            <p>Premio al primo classificato: </p>
                <?php if ($_SESSION['livello'] <= 1 || $_SESSION['ruolo'] == RISORSE_UMANE || $_SESSION['ruolo'] == DISTRICT_MANAGER) { ?>
                                                <p>Store manager: <b><?= fmtMoney($premi_g4['premio_sm'], $CURRENCY) ?></b></p>
                                                <p>District manager: <b><?= fmtMoney($premi_g4['premio_dm'], $CURRENCY) ?></b></p>
                <?php } elseif ($_SESSION['ruolo'] == STORE_MANAGER) { ?>
                                                <p>Store manager: <b><?= fmtMoney($premi_g4['premio_sm'], $CURRENCY) ?></b></p>

                <?php } ?>
                                        </div>
                                        <div class="card half">
                                            <h3>Negozi</h3>
                                            <p><b>Minimo 34% e 30 abiti</b></p>
                                            <p>&nbsp;</p>
                                            <table>
                                                <thead>
                                                    <tr>
                                                        <th class="rank">#</th>
                                                        <th>Negozio</th>
                                                        <?php if ($_SESSION['ruolo'] != ADDETTO_VENDITE || $_SESSION['livello'] <= 1 || $_SESSION['ruolo'] == RISORSE_UMANE) { ?>
                                                            <th>Store manager</th>
                                                        <?php } ?>
                                                        <?php if ($_SESSION['ruolo'] == DISTRICT_MANAGER || $_SESSION['livello'] <= 1 || $_SESSION['ruolo'] == RISORSE_UMANE) { ?>
                                                            <th>District manager</th>
                <?php } ?>
                                                        <th>Totale presi</th>
                                                        <th>Totale convertiti</th>
                                                        <th>Abiti venduti</th>
                                                        <th>Conversioni</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php if (!$gara4): ?>
                                                        <tr><td colspan="4" class="muted">Nessun dato</td></tr>
                                                        <?php
                                                    else: $i = 1;
                                                        $SM_gara4 = $DM_gara4 = [];
                                                        foreach ($gara4 as $row):
                                                            $storeM = getSmDmStore($row['store_code'], $year, $month, 'storeM_id', 1);
                                                            if ($_SESSION['id'] == $storeM['id']) {
                                                                $SM_gara4[] = [
                                                                    'position' => $i,
                                                                    'store' => h($storeName($row['store_code']))
                                                                ];
                                                            }
                                                            $districtM = getSmDmStore($row['store_code'], $year, $month, 'districtM_id', 2);
                                                            if ($_SESSION['id'] == $districtM['id']) {
                                                                $DM_gara4[] = [
                                                                    'position' => $i,
                                                                    'store' => h($storeName($row['store_code']))
                                                                ];
                                                            }
                                                            ?>
                                                            <tr>
                                                                <td class="rank"><?= $row['rank'] ?></td>
                                                                <td class="rowhead">
                                                                    <span><?= h($storeName($row['store_code'])) . ($row['rank'] == 1 ? ' <i class="fa fa-trophy" aria-hidden="true"></i> ' : '') ?></span>
                                                                </td>
                        <?php if ($_SESSION['ruolo'] != ADDETTO_VENDITE || $_SESSION['livello'] <= 1 || $_SESSION['ruolo'] == RISORSE_UMANE) { ?>
                                                                    <td>
                                                                        <span><?= h($storeM['nome'] . ' ' . $storeM['cognome']) ?></span>
                                                                    </td>
                                                                <?php } ?>
                        <?php if ($_SESSION['ruolo'] == DISTRICT_MANAGER || $_SESSION['livello'] <= 1 || $_SESSION['ruolo'] == RISORSE_UMANE) { ?>
                                                                    <td>
                                                                        <span><?= h($districtM['nome'] . ' ' . $districtM['cognome']) ?></span>
                                                                    </td>
                        <?php } ?>
                                                                <td><?= h($row['app_presi']) ?></td>
                                                                <td><?= h($row['app_conv']) ?></td>
                                                                <td><?= $row['abiti'] ?></td>
                                                                <td class="kpi"><?= h((string) $row['conv']) ?>%</td>
                                                            </tr>
                                                            <?php
                                                        endforeach;
                                                    endif;
                                                    ?>
                                                </tbody>
                                            </table>
                                            <div class="footnote">Ordine: conversioni</div>
                                        </div>
                                    </div>
                                    <h2 class="tit-gara">5) Miglior Village per conversioni<?= (in_array(5, $arrGareDistrict) ? ' <i class="fa fa-flag-checkered" aria-hidden="true"></i>' : '') ?></h2>
                                    <?php
                                    $qry = "select * from premi_gare where num_gara=4 order by valido_fino desc limit 1;";
                                    $rs_p = $pdo->prepare($qry);
                                    $rs_p->execute();
                                    $premi_g4 = $rs_p->fetch(PDO::FETCH_ASSOC);
                                    ?>
                                    <div id="gara5" class="cnt-gara">
                                        <div style="font-size: 14px;line-height: 120%;margin-bottom: 30px;">
                <?php if (count($gara4_v) > 0) { ?>
                                                <h3><b>Classifica aggiornata al <?= formatDateOra($gara4_v[0]['last_update']) ?></b></h3>
                                                <p>&nbsp;<p/>
                                            <?php } ?>
                                            <p>Premio al primo classificato: </p>
                <?php if ($_SESSION['livello'] <= 1 || $_SESSION['ruolo'] == RISORSE_UMANE || $_SESSION['ruolo'] == DISTRICT_MANAGER) { ?>
                                                <p>Store manager: <b><?= fmtMoney($premi_g4['premio_sm'], $CURRENCY) ?></b></p>
                                                <p>District manager: <b><?= fmtMoney($premi_g4['premio_dm'], $CURRENCY) ?></b></p>
                <?php } elseif ($_SESSION['ruolo'] == STORE_MANAGER) { ?>
                                                <p>Store manager: <b><?= fmtMoney($premi_g4['premio_sm'], $CURRENCY) ?></b></p>

                <?php } ?>
                                        </div>
                                        <div class="card half">
                                            <h3>Village</h3>
                                            <p><b>Minimo 48% e 30 abiti</b></p>
                                            <p>&nbsp;</p>
                                            <table>
                                                <thead>
                                                    <tr>
                                                        <th class="rank">#</th>
                                                        <th>Negozio</th>
                                                        <?php if ($_SESSION['ruolo'] != ADDETTO_VENDITE || $_SESSION['livello'] <= 1 || $_SESSION['ruolo'] == RISORSE_UMANE) { ?>
                                                            <th>Store manager</th>
                                                        <?php } ?>
                                                        <?php if ($_SESSION['ruolo'] == DISTRICT_MANAGER || $_SESSION['livello'] <= 1 || $_SESSION['ruolo'] == RISORSE_UMANE) { ?>
                                                            <th>District manager</th>
                <?php } ?>
                                                        <th>Totale presi</th>
                                                        <th>Totale convertiti</th>
                                                        <th>Abiti venduti</th>
                                                        <th>Conversioni</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php if (!$gara4_v): ?>
                                                        <tr><td colspan="4" class="muted">Nessun dato</td></tr>
                                                        <?php
                                                    else: $i = 1;
                                                        $SM_gara5 = $DM_gara5 = [];
                                                        foreach ($gara4_v as $row):
                                                            $storeM = getSmDmStore($row['store_code'], $year, $month, 'storeM_id', 1);
                                                            if ($_SESSION['id'] == $storeM['id']) {
                                                                $SM_gara5[] = [
                                                                    'position' => $i,
                                                                    'store' => h($storeName($row['store_code']))
                                                                ];
                                                            }
                                                            $districtM = getSmDmStore($row['store_code'], $year, $month, 'districtM_id', 2);
                                                            if ($_SESSION['id'] == $districtM['id']) {
                                                                $DM_gara5[] = [
                                                                    'position' => $i,
                                                                    'store' => h($storeName($row['store_code']))
                                                                ];
                                                            }
                                                            ?>
                                                            <tr>
                                                                <td class="rank"><?= $row['rank'] ?></td>
                                                                <td class="rowhead">
                                                                    <span><?= h($storeName($row['store_code'])) . ($row['rank'] == 1 ? ' <i class="fa fa-trophy" aria-hidden="true"></i> ' : '') ?></span>
                                                                </td>
                        <?php if ($_SESSION['ruolo'] != ADDETTO_VENDITE || $_SESSION['livello'] <= 1 || $_SESSION['ruolo'] == RISORSE_UMANE) { ?>
                                                                    <td>
                                                                        <span><?= h($storeM['nome'] . ' ' . $storeM['cognome']) ?></span>
                                                                    </td>
                                                                <?php } ?>
                        <?php if ($_SESSION['ruolo'] == DISTRICT_MANAGER || $_SESSION['livello'] <= 1 || $_SESSION['ruolo'] == RISORSE_UMANE) { ?>
                                                                    <td>
                                                                        <span><?= h($districtM['nome'] . ' ' . $districtM['cognome']) ?></span>
                                                                    </td>
                        <?php } ?>
                                                                <td><?= h($row['app_presi']) ?></td>
                                                                <td><?= h($row['app_conv']) ?></td>
                                                                <td><?= $row['abiti'] ?></td>
                                                                <td class="kpi"><?= h((string) $row['conv']) ?>%</td>
                                                            </tr>
                                                            <?php
                                                        endforeach;
                                                    endif;
                                                    ?>
                                                </tbody>
                                            </table>
                                            <div class="footnote">Ordine: conversioni</div>
                                        </div>
                                    </div>
            <?php } ?>
                                <h2 class="tit-gara"><?= (($_SESSION['livello'] == '3' && $_SESSION['ruolo'] == ADDETTO_VENDITE) ? '3' : '6') ?>) Miglior venditrice per fatturato<?= (in_array(6, $arrGareDistrict) && $_SESSION['ruolo'] == DISTRICT_MANAGER ? ' <i class="fa fa-flag-checkered" aria-hidden="true"></i>' : '') ?></h2>
                                <div id="gara6" class="cnt-gara">
                                    <!-- GARA 5 -->
                                    <div style="font-size: 14px;line-height: 120%;margin-bottom: 30px;">
            <?php if (count($gara5) > 0) { ?>
                                            <h3><b>Classifica aggiornata al <?= formatDateOra($gara5[0]['last_update']) ?></b></h3>
                                            <p>&nbsp;<p/>
                                        <?php } ?>
                                        <?php
                                        $qry = "select * from premi_gare where num_gara=6 order by valido_fino desc limit 1;";
                                        $rs_p = $pdo->prepare($qry);
                                        $rs_p->execute();
                                        $premi_g6 = $rs_p->fetch(PDO::FETCH_ASSOC);
                                        ?>
                                        <p>Premio al primo classificato: <?= fmtMoney($premi_g6['premio_sm'], $CURRENCY) ?></p>
                                        <p>Premio al secondo classificato: <?= fmtMoney($premi_g6['premio_av'], $CURRENCY) ?></p>
                                        <p>Premio al terzo classificato: <?= fmtMoney($premi_g6['premio_dm'], $CURRENCY) ?></p>
                                    </div>
                                    <div class="card half">
                                        <h3> Venditori</h3>
                                        <p>&nbsp;</p>
                                        <table>
                                            <thead>
                                                <tr>
                                                    <th class="rank">#</th>
                                                    <th>Operatore</th>
            <!--                                                    <th>Ruolo</th>
                                                    <th>Abiti</th>
                                                    <th>Accessori</th>-->
                                                    <th style="text-align: right;">Fatturato(&euro;)</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (!$gara5): ?>
                                                    <tr><td colspan="4" class="muted">Nessun dato</td></tr>
                                                    <?php
                                                else: $i = 1;
                                                    $res_gara6 = [];
                                                    foreach ($gara5 as $row):
                                                        if ($row['operator_code'] == $_SESSION['id']) {
                                                            $res_gara6 = [
                                                                'position' => $i,
                                                                'amount' => number_format($row['fatturato'], 2, ",", ".")
                                                            ];
                                                        }
                                                        ?>
                                                        <tr<?= ($row['operator_code'] == $_SESSION['id'] ? ' style="background-color:#cccccc;"' : '') ?>>
                                                            <td class="rank"><?= $row['rank'] ?></td>
                                                            <td class="rowhead">
                                                                <span><?= h($operatorName($row['operator_code'])) . ($row['rank'] == 1 || $row['rank'] == 2 || $row['rank'] == 3 ? ' <i class="fa fa-trophy" aria-hidden="true"></i> ' : '') . ($row['fatturato'] > 50000 ? '  <span style="color:#ffd43b;">+50K</span>' : '') ?></span>
                                                            </td>
                    <!--                                                            <td><?= getRuoloUtente($row['ruolo']) ?></td>
                                                            <td><?= h((string) $row['dresses_sold']) ?></td>
                                                            <td><?= h((string) $row['accessories_sold']) ?></td>-->
                                                            <td style="text-align: right;"><b><?= number_format($row['fatturato'], 2, ",", ".") ?></b></td>
                                                        </tr>
                                                        <?php
                                                    endforeach;
                                                endif;
                                                ?>
                                            </tbody>
                                        </table>
                                        <div class="footnote">Ordine: abiti desc, tie-break accessori.</div>
                                    </div>
                                </div>                            
                            </section>
                            <script>
                                $(function() {
                                $('.tit-gara').unbind('click').click(function() {
                                var btn = $(this);
                                btn.next('div.cnt-gara').slideToggle('slow');
                                });
            <?php
//            if (count($SM_gara3) > 0 || count($DM_gara3) > 0) {
//                $resGara3 = '';
////                if (count($SM_gara3) > 0) {
////                    foreach ($SM_gara3 as $arrGara3) {
////                        $resGara3 .= '<p>' . $arrGara3['store'] . ' (' . $arrGara3['position'] . ' pos.) ' . ($arrGara3['position'] == 1 ? ' - <span style=\"color:green;\">Premio SM: 200 &euro;</span>' : '') . '</p>';
////                    }
////                    if ($resGara3 != '') {
////                        echo 'var htmlGara3="' . $resGara3 . '";' . "\n";
////                    } else {
////                        echo 'var htmlGara3="Non ancora classificato";' . "\n";
////                    }
////                    echo '$("#res-gara3").append(htmlGara3);' . "\n";
////                }
////                if (count($DM_gara3) > 0) {
////                    foreach ($DM_gara3 as $arrGara3) {
////                        $resGara3 .= '<p>' . $arrGara3['store'] . ' (' . $arrGara3['position'] . ' pos.) ' . ($arrGara3['position'] == 1 ? ' - <span style=\"color:green;\">Premio DM: 100 &euro;</span>' : '') . '</p>';
////                    }
////                    if ($resGara3 != '') {
////                        echo 'var htmlGara3="' . $resGara3 . '";' . "\n";
////                    } else {
////                        echo 'var htmlGara3="Non ancora classificato";' . "\n";
////                    }
////                    echo '$("#res-gara3").append(htmlGara3);' . "\n";
////                }
//            }
//            if (count($SM_gara4) > 0 || count($DM_gara4) > 0) {
//                $resGara4 = '';
//                if (count($SM_gara4) > 0) {
//                    foreach ($SM_gara4 as $arrGara4) {
//                        $resGara4 .= '<p>' . $arrGara4['store'] . ' (' . $arrGara3['position'] . ' pos.) ' . ($arrGara4['position'] == 1 ? ' - <span style=\"color:green;\">Premio SM: 200 &euro;</span>' : '') . '</p>';
//                    }
//                    if ($resGara4 != '') {
//                        echo 'var htmlGara4="' . $resGara4 . '";' . "\n";
//                    } else {
//                        echo 'var htmlGara4="Non ancora classificato";' . "\n";
//                    }
//                    echo '$("#res-gara4").append(htmlGara4);' . "\n";
//                }
//                if (count($DM_gara4) > 0) {
//                    foreach ($DM_gara4 as $arrGara4) {
//                        $resGara4 .= '<p>' . $arrGara4['store'] . ' (' . $arrGara4['position'] . ' pos.) ' . ($arrGara4['position'] == 1 ? ' - <span style=\"color:green;\">Premio DM: 100 &euro;</span>' : '') . '</p>';
//                    }
//                    if ($resGara4 != '') {
//                        echo 'var htmlGara4="' . $resGara4 . '";' . "\n";
//                    } else {
//                        echo 'var htmlGara4="Non ancora classificato";' . "\n";
//                    }
//                    echo '$("#res-gara4").append(htmlGara4);' . "\n";
//                }
//            }
//            if (count($SM_gara5) > 0 || count($DM_gara5) > 0) {
//                $resGara5 = '';
//                if (count($SM_gara5) > 0) {
//                    foreach ($SM_gara5 as $arrGara5) {
//                        $resGara5 .= '<p>' . $arrGara5['store'] . ' (' . $arrGara5['position'] . ' pos.) ' . ($arrGara5['position'] == 1 ? ' - <span style=\"color:green;\">Premio SM: 200 &euro;</span>' : '') . '</p>';
//                    }
//                    if ($resGara5 != '') {
//                        echo 'var htmlGara5="' . $resGara5 . '";' . "\n";
//                    } else {
//                        echo 'var htmlGara5="Non ancora classificato";' . "\n";
//                    }
//                    echo '$("#res-gara5").append(htmlGara5);' . "\n";
//                }
//                if (count($DM_gara5) > 0) {
//                    foreach ($DM_gara5 as $arrGara5) {
//                        $resGara5 .= '<p>' . $arrGara5['store'] . ' (' . $arrGara5['position'] . ' pos.) ' . ($arrGara5['position'] == 1 ? ' - <span style=\"color:green;\">Premio DM: 100 &euro;</span>' : '') . '</p>';
//                    }
//                    if ($resGara5 != '') {
//                        echo 'var htmlGara5="' . $resGara5 . '";' . "\n";
//                    } else {
//                        echo 'var htmlGara5="Non ancora classificato";' . "\n";
//                    }
//                    echo '$("#res-gara5").append(htmlGara5);' . "\n";
//                }
//            }
//            if (count($res_gara6) > 0) {
//                $resGara6 = '<p><b>Miglior venditrice per fatturato</b></p>'
//                        . '<p>Posizione ' . $res_gara6['position'] . ' con ' . $res_gara6['amount'] . '&euro;</p>';
//                echo 'var htmlGara6="' . $resGara6 . '";' . "\n";
//                echo '$("#res-gara6").html(htmlGara6);' . "\n";
//            }
            ?>
                                });
                            </script>
                            <?php
                        }
                    }
                    ?>
                </div>
            </div>
<?php } ?>
    </body>
</html>