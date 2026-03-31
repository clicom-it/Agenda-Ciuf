<?php

require_once 'Excel/reader.php';

$data = new Spreadsheet_Excel_Reader();
$data->setOutputEncoding('CP1251');
$data->read('test.xls');
error_reporting(E_ALL ^ E_NOTICE);

for ($i = 1; $i <= $data->sheets[0]['numRows']; $i++) {
    $arr_cod[] = $data->sheets[0]['cells'][$i][1];
    $arr_nomi[] = $data->sheets[0]['cells'][$i][2];
}
print_r($arr_cod);
print("<br />");
print_r($arr_nomi);
?>