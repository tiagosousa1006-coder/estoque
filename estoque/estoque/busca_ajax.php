<?php 
session_start();
include("../config/db.php");

$user_tipo = $_SESSION['user_tipo'] ?? 'usuario';
$almox_usuario = $_SESSION['almoxarifado_id'] ?? null;

$busca = $_GET['busca'] ?? '';
$filtro_almox = $_GET['almoxarifado'] ?? '';

if($user_tipo != 'admin'){
    $filtro_almox = $almox_usuario;
}

$busca_sql = $conn->real_escape_string($busca);

$where = "WHERE p.nome LIKE '%$busca_sql%'";

if(!empty($filtro_almox)){
    $where .= " AND e.almoxarifado_id = ".intval($filtro_almox);
}

$sql = "
SELECT 
    p.nome,
    p.estoque_minimo,
    IFNULL(SUM(e.quantidade),0) as total
FROM produtos p
LEFT JOIN estoque_local e ON e.produto_id = p.id
$where
GROUP BY p.id
ORDER BY p.nome ASC
";

$res = $conn->query($sql);

if($res->num_rows == 0){
    echo "<tr><td colspan='2' class='text-center'>Nenhum resultado</td></tr>";
    exit;
}

while($p = $res->fetch_assoc()){

    $saldo = $p['total'];
    $minimo = $p['estoque_minimo'];

    // 🔥 CORES
    if($minimo > 0 && $saldo <= 0){
        $cor = "#f8d7da"; // vermelho
    } elseif($minimo > 0 && $saldo <= $minimo){
        $cor = "#fff3cd"; // amarelo
    } else {
        $cor = "";
    }

    echo "<tr>
        <td style='background:$cor'>{$p['nome']}</td>
        <td style='background:$cor'><strong>{$saldo}</strong></td>
    </tr>";
}