<?php

require "vendor/autoload.php";
require "functions.php";
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

$reader = new Xlsx;
$spreadsheet = $reader->load("./excel_files/file.xlsx");  // leggiamo lo spreadsheet

$data = $spreadsheet->getSheet(0)->toArray();  // lo trasformiamo in array

$main_skus = array();   // array sku main
$related_skus = array(); // array sku collegati al main

foreach ($data as $i => $record) {
    // ignoriamo $record[0] in quanto si tratta degli header
    if ($i !== 0) {
        // entriamo nell'array con dentro main sku e related sku, rispettivamente in posizione 0 e 1  
        $temp1 = $record[0];
        $temp2 = explode(",", $record[1]);
        array_push($main_skus, $temp1);
        array_push($related_skus, $temp2);
    }
}

add_related_products($main_skus, $related_skus);
