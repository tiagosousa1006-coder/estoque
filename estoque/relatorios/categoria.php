<?php include("../config/db.php"); ?>
<?php include("../assets/layout.php"); ?>

<h3>📊 Relatório por Categoria</h3>

<table class="table table-bordered">
<tr>
    <th>Categoria</th>
    <th>Total Produtos</th>
    <th>Total Estoque</th>
</tr>

<?php

$sql = "
SELECT c.nome as categoria,
COUNT(p.id) as total_produtos,
SUM(p.estoque) as total_estoque
FROM categorias c
LEFT JOIN produtos p ON p.categoria_id = c.id
GROUP BY c.id
";

$res = $conn->query($sql);

while($row = $res->fetch_assoc()){
    echo "<tr>
        <td>{$row['categoria']}</td>
        <td>{$row['total_produtos']}</td>
        <td>{$row['total_estoque']}</td>
    </tr>";
}
?>

</table>

<?php include("../assets/footer.php"); ?>