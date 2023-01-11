<?php

require "vendor/autoload.php";

use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

$reader = new Xlsx;
$spreadsheet = $reader->load("./test.xlsx");  // leggiamo lo spreadsheet

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
            "linkedProductType" => "simple",
            "position" => 0
        );
        array_push($postfields["items"], $postfield);
    }

    /**
     * una volta finito di caricare i postfileds per un main product
     * eseguo la chiamata POST e successivamente re-inizializzo l'array postfields.
     * Per evitare chiamate inutili con array vuoti inserisco questo if.
     */

    // $curl = curl_init();

    // // file di testo da cui prendo il il bearer token
    // $fileToken = fopen("token.txt", "r");
    // $auth = fgets($fileToken);

    // curl_setopt_array($curl, array(
    //     CURLOPT_URL => 'https://dominio.com/rest/V1/products/' . $main . '/links',
    //     CURLOPT_RETURNTRANSFER => true,
    //     CURLOPT_ENCODING => '',
    //     CURLOPT_MAXREDIRS => 10,
    //     CURLOPT_TIMEOUT => 0,
    //     CURLOPT_FOLLOWLOCATION => true,
    //     CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    //     CURLOPT_CUSTOMREQUEST => 'POST',
    //     CURLOPT_POSTFIELDS => json_encode($postfields),
    //     CURLOPT_HTTPHEADER => array(
    //         $auth,
    //         'Cookie: PHPSESSID=s88lk5blk224u8e6jlgjdpaedc'
    //     ),
    // ));

    // $response = curl_exec($curl);

    // curl_close($curl);
    // fclose($fileToken);
    echo "Ciclo $i\n" . json_encode($postfields, JSON_PRETTY_PRINT) . "\n";
    $postfields = array("items" => array());
}
