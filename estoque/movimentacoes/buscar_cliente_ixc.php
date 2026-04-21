<?php

$ixc_url = "https://ixc.agilityindependencia.com.br/webservice/v1/cliente";
$token   = "2:cc6d947e5959ad18930d8de348600a201d211109b8dda0ec8680ecca976bc370";

$busca = $_GET['nome'] ?? '';

if(strlen($busca) < 3){
    echo json_encode([]);
    exit;
}

$curl = curl_init();

curl_setopt_array($curl, [
    CURLOPT_URL => $ixc_url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        "Authorization: Basic " . base64_encode($token),
        "ixcsoft: listar"
    ],
]);

$response = curl_exec($curl);

if(curl_error($curl)){
    echo json_encode([]);
    exit;
}

curl_close($curl);

$data = json_decode($response, true);

$result = [];

if(isset($data['registros'])){

    foreach($data['registros'] as $c){

        if(!empty($c['razao']) && stripos($c['razao'], $busca) !== false){

            $result[] = [
                'id' => $c['id'],
                'nome' => $c['razao']
            ];
        }

        // limita retorno
        if(count($result) >= 15){
            break;
        }
    }
}

echo json_encode($result);