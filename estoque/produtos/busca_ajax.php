<?php 
include("../config/db.php");

$busca = $_GET['busca'] ?? '';
$busca_sql = $conn->real_escape_string($busca);

$where = "";
if(!empty($busca_sql)){
    $where = "WHERE p.nome LIKE '%$busca_sql%'";
}

$res = $conn->query("
SELECT 
    p.*,
    IFNULL(SUM(e.quantidade),0) as saldo
FROM produtos p
LEFT JOIN estoque_local e ON e.produto_id = p.id
$where
GROUP BY p.id
ORDER BY p.nome ASC
");

while($p = $res->fetch_assoc()){

    $saldo = $p['saldo'];
    $minimo = $p['estoque_minimo'];

    if($saldo <= 0){
        $cor = "#f8d7da";
    } elseif($saldo <= $minimo){
        $cor = "#fff3cd";
    } else {
        $cor = "";
    }

    $caminho = "../uploads/".$p['imagem'];

    if(!empty($p['imagem']) && file_exists($caminho)){
        $img = "<img src='$caminho' style='width:50px;height:50px;object-fit:cover;border-radius:8px;'>";
    } else {
        $img = "<span class='text-muted'>Sem imagem</span>";
    }

    echo "<tr>

        <td style='background:$cor' class='text-center'>$img</td>
        <td style='background:$cor'>{$p['nome']}</td>
        <td style='background:$cor' class='text-center'><strong>{$saldo}</strong></td>
        <td style='background:$cor' class='text-center'>{$minimo}</td>

        <td style='background:$cor' class='text-center'>
            <a href='editar.php?id={$p['id']}' class='btn btn-sm btn-warning'>✏️</a>
            <a href='?excluir={$p['id']}' class='btn btn-sm btn-danger' onclick=\"return confirm('Tem certeza?')\">🗑️</a>
        </td>

    </tr>";
}