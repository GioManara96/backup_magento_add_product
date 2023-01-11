<?php
require "vendor/autoload.php";
require "functions.php";

use League\Csv\Reader;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

$scelta = "";

while ($scelta != 0) {
    echo "\n\t\t\t\t\t\tMENU CARICAMENTO PRODOTTI\n";
    echo "\n\n\tINFO: Per utilizzare il menu assicurasi di avere le cartelle 'csv_files' e 'excel_files'\n";
    echo "\tallo stesso livello di questo script. I file da leggere al loro interno dovranno chiamarsi\n";
    echo "\trispettivamente'file.csv' o 'file.xlsx'. Nel caso dei .csv assicurarsi di chiamare gli headers\n";
    echo "\tdegli SKU del main e del related rispettivamente 'MAIN' e 'RELATED'\n";
    echo "\n\n\t1. Prendi i dati da 'csv_files/'\n";
    echo "\t2. Prendi i dati da 'excel_files/'\n";
    echo "\n\t0. ESCI\n\n\t";

    $scelta = readline("Inserire scelta: ");

    switch ($scelta) {
        case 1:
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
            break;
        case 2:
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
            break;
        default:
            break;
    }
}
