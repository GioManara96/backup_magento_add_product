<?php

require "vendor/autoload.php";
require "functions.php";
use League\Csv\Reader;

//load the CSV document from a file path
$csv = Reader::createFromPath('./csv_files/file.csv', 'r');
$csv->setHeaderOffset(0);
$csv->setDelimiter(";");
$header = $csv->getHeader(); //returns the CSV header record
$records = $csv->getRecords(); //returns all the CSV records as an Iterator object

$main_skus = array();   // array sku main
$related_skus = array(); // array sku collegati al main
foreach ($records as $record) {
    $temp1 = $record["MAIN"];
    $temp2 = explode(",", $record["RELATED"]);
    array_push($main_skus, $temp1);
    array_push($related_skus, $temp2);
}

add_related_products($main_skus, $related_skus);
