<?php

/**
 * * FUNZIONI
 */
function add_related_products($main_skus, $related_skus) {
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
         * una volta finito di caricare i postfields per un main product
         * eseguo la chiamata POST e successivamente re-inizializzo l'array postfields.
         * Per evitare chiamate inutili con array vuoti inserisco questo if.
         */

        $curl = curl_init();
        $auth = getToken();

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

        if ($response === "true") {
            activate_auto_related($main_skus[$i]);
        } else {
            echo "\n\tErrore sul main " . $main_skus[$i] . ". Controllare il file error.json\n";
            file_put_contents("error.json", $response);
        }
        curl_close($curl);

        $postfields = array("items" => array());
    }
    echo "\n\tOperazione conclusa\n";
}

/**
 * devo attivare il flag 'auto related product' e per fare ciò sono costretto ad agire
 * sul prodotto principale
 */
function activate_auto_related($main_sku) {
    $curl = curl_init();
    $auth = getToken();

    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://www.forpen.it//rest/V1/products/' . $main_sku,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'PUT',
        CURLOPT_POSTFIELDS => '{
          "product": {
            "custom_attributes": [
              {
                "attribute_code": "mp_disable_auto_related",
                "value": "1"
              }
            ]
          }
        }',
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
            $auth,
            'Cookie: PHPSESSID=rsv9cd206m64deq3be7pcl98vc'
        ),
    ));

    curl_exec($curl);
    curl_close($curl);
}

// funziojne per prendere il token di accesso
function getToken() {
    // file di testo da cui prendo il il bearer token
    $fileToken = fopen("token.txt", "r");
    $auth = fgets($fileToken);
    fclose($fileToken);

    return $auth;
}
