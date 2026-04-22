<?php 
include("../auth.php");
include("../config/db.php");

$user_tipo = $_SESSION['user_tipo'] ?? 'usuario';
$almox_usuario = $_SESSION['almoxarifado_id'] ?? null;
$filtro_almox = $_GET['almoxarifado'] ?? '';

if($user_tipo != 'admin'){
    $filtro_almox = $almox_usuario;
}

// 🔥 EXPORTAÇÃO
if(isset($_GET['export']) && $_GET['export'] == 1){

    require __DIR__ . '/../../vendor/autoload.php';

    if(!empty($filtro_almox)){
        $sql = "
        SELECT 
            p.nome,
            IFNULL(SUM(e.quantidade),0) as total
        FROM produtos p
        LEFT JOIN estoque_local e 
            ON e.produto_id = p.id 
            AND e.almoxarifado_id = ".intval($filtro_almox)."
        LEFT JOIN almox_produtos ap 
            ON ap.produto_id = p.id 
            AND ap.almoxarifado_id = ".intval($filtro_almox)."
        WHERE e.almoxarifado_id IS NOT NULL OR ap.obrigatorio = 1
        GROUP BY p.id
        ORDER BY p.nome ASC
        ";
    } else {
        $sql = "
        SELECT p.nome, IFNULL(SUM(e.quantidade),0) as total
        FROM produtos p
        LEFT JOIN estoque_local e ON e.produto_id = p.id
        GROUP BY p.id
        ORDER BY p.nome ASC
        ";
    }

    $res = $conn->query($sql);

    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    $sheet->setCellValue('A1', 'Produto');
    $sheet->setCellValue('B1', 'Quantidade');

    $row = 2;

    while($p = $res->fetch_assoc()){
        $sheet->setCellValue('A'.$row, $p['nome']);
        $sheet->setCellValue('B'.$row, $p['total']);
        $row++;
    }

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="estoque.xlsx"');

    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
}

include("../assets/layout.php");
?>

<div class="container-fluid">
<div class="card p-4">

<h4 class="mb-3">📦 Estoque</h4>

<!-- 🔎 BUSCA -->
<div class="mb-3">
<input type="text" id="busca" class="form-control" placeholder="🔍 Buscar produto...">
</div>

<!-- 🔎 FILTRO ADMIN -->
<?php if($user_tipo == 'admin'): ?>
<form method="GET" class="row mb-3">

<div class="col-md-4">
<select name="almoxarifado" class="form-control" onchange="this.form.submit()">

<option value="">Saldo total</option>

<?php
$resA = $conn->query("SELECT * FROM almoxarifados ORDER BY nome ASC");
while($a = $resA->fetch_assoc()){
    $sel = ($filtro_almox == $a['id']) ? 'selected' : '';
    echo "<option value='{$a['id']}' $sel>{$a['nome']}</option>";
}
?>

</select>
</div>

</form>
<?php endif; ?>

<!-- 🔥 EXPORTAR -->
<a href="?export=1&almoxarifado=<?= $filtro_almox ?>" class="btn btn-success mb-3">
📊 Exportar Excel
</a>

<table class="table table-bordered">

<tr>
    <th>Produto</th>
    <th>Quantidade</th>
</tr>

<tbody id="tabela">

<?php
if(!empty($filtro_almox)){

    $sql = "
    SELECT 
        p.nome,
        p.estoque_minimo,
        IFNULL(SUM(e.quantidade),0) as total
    FROM produtos p
    LEFT JOIN estoque_local e 
        ON e.produto_id = p.id 
        AND e.almoxarifado_id = ".intval($filtro_almox)."
    LEFT JOIN almox_produtos ap 
        ON ap.produto_id = p.id 
        AND ap.almoxarifado_id = ".intval($filtro_almox)."
    WHERE e.almoxarifado_id IS NOT NULL OR ap.obrigatorio = 1
    GROUP BY p.id
    ORDER BY p.nome ASC
    ";

} else {

    $sql = "
    SELECT 
        p.nome,
        p.estoque_minimo,
        IFNULL(SUM(e.quantidade),0) as total
    FROM produtos p
    LEFT JOIN estoque_local e ON e.produto_id = p.id
    GROUP BY p.id
    ORDER BY p.nome ASC
    ";
}

$res = $conn->query($sql);

while($p = $res->fetch_assoc()){

    $saldo = $p['total'];
    $minimo = $p['estoque_minimo'];

    if($minimo > 0 && $saldo <= 0){
        $cor = "#f8d7da";
    } elseif($minimo > 0 && $saldo <= $minimo){
        $cor = "#fff3cd";
    } else {
        $cor = "";
    }

    echo "<tr>
        <td style='background:$cor'>{$p['nome']}</td>
        <td style='background:$cor'><strong>{$saldo}</strong></td>
    </tr>";
}
?>

</tbody>

</table>

</div>
</div>

<script>
document.getElementById("busca").addEventListener("keyup", function(){

    let valor = this.value;

    fetch("busca_ajax.php?busca=" + valor + "&almoxarifado=<?= $filtro_almox ?>")
    .then(res => res.text())
    .then(data => {
        document.getElementById("tabela").innerHTML = data;
    });

});
</script>

<?php include("../assets/footer.php"); ?>