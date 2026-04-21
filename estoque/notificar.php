<?php
include("config/db.php");

$token = "bot8762773577:AAEPq_C2KnIuViHCYzFf3OfOAQb682ntwLo";
$chat_id = "1533249803";

$sql = "
SELECT f.*, p.nome as produto, t.nome as tecnico
FROM ferramentas_retiradas f
JOIN produtos p ON p.id = f.produto_id
JOIN tecnicos t ON t.id = f.tecnico_id
WHERE f.devolvido = 0
AND DATE(f.data_devolucao) <= CURDATE()
AND f.notificado = 0
";
$res = $conn->query($sql);

echo "Registros encontrados: " . $res->num_rows . "<br>";

while($row = $res->fetch_assoc()){

    if($row['data_devolucao'] < date('Y-m-d')){
        $tipo = "🚨 ATRASADO";
    } else {
        $tipo = "⚠️ VENCE HOJE";
    }

    $mensagem  = "$tipo\n";
    $mensagem .= "🧰 {$row['produto']}\n";
    $mensagem .= "👨‍🔧 {$row['tecnico']}\n";
    $mensagem .= "📅 {$row['data_devolucao']}";

    $url = "https://api.telegram.org/bot$token/sendMessage";

    $data = [
        'chat_id' => $chat_id,
        'text' => $mensagem
    ];

    $options = [
        'http' => [
            'header'  => "Content-type: application/x-www-form-urlencoded",
            'method'  => 'POST',
            'content' => http_build_query($data),
        ],
    ];

    $context  = stream_context_create($options);
    $result = file_get_contents($url, false, $context);

    echo "<pre>$result</pre>";

    $conn->query("UPDATE ferramentas_retiradas SET notificado = 1 WHERE id = {$row['id']}");
}
