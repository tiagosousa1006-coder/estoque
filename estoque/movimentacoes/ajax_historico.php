<?php
include("../config/db.php");

$limite = 20;
$pagina = $_POST['pagina'] ?? 1;
$offset = ($pagina - 1) * $limite;

$where = "1=1";

// filtros
if(!empty($_POST['data_inicio'])){
    $where .= " AND DATE(m.data_movimentacao) >= '".$_POST['data_inicio']."'";
}

if(!empty($_POST['data_fim'])){
    $where .= " AND DATE(m.data_movimentacao) <= '".$_POST['data_fim']."'";
}

if(!empty($_POST['tipo'])){
    $where .= " AND m.tipo = '".$_POST['tipo']."'";
}

if(!empty($_POST['subtipo'])){
    $where .= " AND m.subtipo LIKE '%".$_POST['subtipo']."%'";
}

if(!empty($_POST['produto'])){
    $where .= " AND p.nome LIKE '%".$_POST['produto']."%'";
}

// total
$total = $conn->query("
SELECT COUNT(*) as total
FROM movimentacoes m
JOIN produtos p ON p.id = m.produto_id
WHERE $where
")->fetch_assoc()['total'];

$total_paginas = ceil($total / $limite);

// dados
$res = $conn->query("
SELECT m.*, p.nome as produto, a.nome as almoxarifado
FROM movimentacoes m
JOIN produtos p ON p.id = m.produto_id
LEFT JOIN almoxarifados a ON a.id = m.almoxarifado_id
WHERE $where
ORDER BY m.id DESC
LIMIT $limite OFFSET $offset
");

echo "<div class='table-responsive'><table class='table table-bordered'>";

echo "<tr>
<th>Data</th>
<th>Produto</th>
<th>Tipo</th>
<th>Subtipo</th>
<th>Qtd</th>
<th>Detalhes</th>
</tr>";

while($m = $res->fetch_assoc()){

    $detalhes = "-";

    // 🔽 ENTRADA (COMPRA / RETORNO)
    if($m['tipo'] == 'entrada'){
        if(!empty($m['observacao'])){
            $detalhes = "🛒 ".$m['observacao'];
        }
    }

    // 🔼 SAÍDA
    if($m['tipo'] == 'saida'){

        if(!empty($m['cliente_nome'])){
            $detalhes = "👤 Cliente: ".$m['cliente_nome'];
        }

        if(!empty($m['tecnico_nome'])){
            $detalhes = "👷 Técnico: ".$m['tecnico_nome'];

            if(!empty($m['destino'])){
                $detalhes .= " | 📍 ".$m['destino'];
            }
        }
    }

    echo "<tr>
        <td>".date('d/m/Y H:i', strtotime($m['data_movimentacao']))."</td>
        <td>{$m['produto']}</td>
        <td><span class='badge bg-primary'>{$m['tipo']}</span></td>
        <td>{$m['subtipo']}</td>
        <td><strong>{$m['quantidade']}</strong></td>
        <td>$detalhes</td>
    </tr>";
}

echo "</table></div>";

// paginação ajax
echo "<nav><ul class='pagination'>";

for($i=1;$i<=$total_paginas;$i++){
    echo "<li class='page-item'>
        <a class='page-link' href='#' onclick='carregar($i)'>$i</a>
    </li>";
}

echo "</ul></nav>";