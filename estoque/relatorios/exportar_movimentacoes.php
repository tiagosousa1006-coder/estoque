<?php
include("../config/db.php");

// Cabeçalho para download
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=relatorio_movimentacoes.xls");

// Consulta
$sql = "
SELECT m.*, p.nome as produto_nome
FROM movimentacoes m
JOIN produtos p ON p.id = m.produto_id
ORDER BY m.id DESC
";

$res = $conn->query($sql);

// Tabela
echo "<table border='1'>";
echo "<tr>
<th>Data</th>
<th>Produto</th>
<th>Tipo</th>
<th>Subtipo</th>
<th>Quantidade</th>
<th>Cliente</th>
<th>Técnico</th>
<th>Destino</th>
</tr>";

while($row = $res->fetch_assoc()){
    echo "<tr>
        <td>{$row['data_movimentacao']}</td>
        <td>{$row['produto_nome']}</td>
        <td>{$row['tipo']}</td>
        <td>{$row['subtipo']}</td>
        <td>{$row['quantidade']}</td>
        <td>{$row['cliente_nome']}</td>
        <td>{$row['tecnico_nome']}</td>
        <td>{$row['destino']}</td>
    </tr>";
}

echo "</table>";