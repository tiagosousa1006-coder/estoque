<?php
include("../config/db.php");

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=relatorio_estoque.xls");

$sql = "
SELECT p.*, c.nome as categoria
FROM produtos p
LEFT JOIN categorias c ON c.id = p.categoria_id
";

$res = $conn->query($sql);

echo "<table border='1'>";
echo "<tr>
<th>Produto</th>
<th>Categoria</th>
<th>Estoque</th>
<th>Estoque Mínimo</th>
<th>Status</th>
</tr>";

while($p = $res->fetch_assoc()){

    $status = $p['ativo'] ? 'Ativo' : 'Inativo';

    echo "<tr>
        <td>{$p['nome']}</td>
        <td>{$p['categoria']}</td>
        <td>{$p['estoque']}</td>
        <td>{$p['estoque_minimo']}</td>
        <td>{$status}</td>
    </tr>";
}

echo "</table>";