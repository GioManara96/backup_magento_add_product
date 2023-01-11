<?php

require "vendor/autoload.php";

use League\Csv\Reader;

//load the CSV document from a file path
$csv = Reader::createFromPath('./csv_files/forpen.csv', 'r');
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

$postfields = array("items" => array());  // array dei postfields finale

// ciclo per entrare nell'array degli sku del main
for ($i = 0; $i < count($main_skus); $i++) {
    // ciclo per accedere effettivamente agli sku nell'array dei related
    foreach ($related_skus[$i] as $related) {
        // postfield con dentro tutti gli items associati ad 1 prodotto main
        $postfield = array(
            "sku" => $main_skus[$i],
            "linkType" => 'related',
            "linkedProductSku" => $related,
            "linkedProductType" => "configurable",
            "position" => 0
        );
        array_push($postfields["items"], $postfield);
    }

    /**
     * una volta finito di caricare i postfileds per un main product
     * eseguo la chiamata POST e successivamente re-inizializzo l'array postfields.
     * Per evitare chiamate inutili con array vuoti inserisco questo if.
     */

    $curl = curl_init();

    // file di testo da cui prendo il il bearer token
    $fileToken = fopen("token.txt", "r");
    $auth = fgets($fileToken);

    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://www.forpen.it/rest/V1/products/' . $main_skus[$i] . '/links',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => json_encode($postfields),
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
            $auth,
            'Cookie: PHPSESSID=s88lk5blk224u8e6jlgjdpaedc'
        ),
    ));

    $response = curl_exec($curl);
    curl_close($curl);
    fclose($fileToken);
    // echo "Ciclo $i\n" . json_encode($postfields, JSON_PRETTY_PRINT) . "\n";
    $postfields = array("items" => array());

    echo json_encode(json_decode($response), JSON_PRETTY_PRINT)."\n";
}
