<?php 
include("../auth.php");
include("../config/db.php");

// 🔒 DADOS
$almox_usuario = $_SESSION['almoxarifado_id'] ?? null;
$filtro_almox  = $_GET['almoxarifado'] ?? '';

$data_inicio = $_GET['data_inicio'] ?? '';
$data_fim    = $_GET['data_fim'] ?? '';
$tipo        = $_GET['tipo'] ?? '';
$busca       = $_GET['busca'] ?? '';
$nf          = $_GET['nf'] ?? '';

// 🔥 WHERE BASE
$where = "1=1 AND (m.subtipo IS NULL OR m.subtipo != 'transferencia')";

// 👤 USUÁRIO
if($almox_usuario){
    $where .= " AND m.almoxarifado_id = $almox_usuario";
} else {
    if(!empty($filtro_almox)){
        $where .= " AND m.almoxarifado_id = ".intval($filtro_almox);
    }
}

// FILTROS
if($data_inicio){
    $where .= " AND DATE(m.data_movimentacao) >= '$data_inicio'";
}

if($data_fim){
    $where .= " AND DATE(m.data_movimentacao) <= '$data_fim'";
}

if($tipo){
    $where .= " AND m.tipo = '$tipo'";
}

if($busca){
    $busca = $conn->real_escape_string($busca);
    $where .= " AND p.nome LIKE '%$busca%'";
}

// 🔥 FILTRO NF
if($nf){
    $nf = $conn->real_escape_string($nf);
    $where .= " AND m.observacao LIKE '%$nf%'";
}

// 🔥 EXPORTAÇÃO
if(isset($_GET['export']) && $_GET['export'] == 1){

    require __DIR__ . '/../../vendor/autoload.php';

    $res = $conn->query("
    SELECT m.*, p.nome as produto, a.nome as almoxarifado
    FROM movimentacoes m
    JOIN produtos p ON p.id = m.produto_id
    LEFT JOIN almoxarifados a ON a.id = m.almoxarifado_id
    WHERE $where
    ORDER BY m.id DESC
    ");

    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    $sheet->setCellValue('A1', 'Data');
    $sheet->setCellValue('B1', 'Produto');
    $sheet->setCellValue('C1', 'Almoxarifado');
    $sheet->setCellValue('D1', 'Tipo');
    $sheet->setCellValue('E1', 'Qtd');
    $sheet->setCellValue('F1', 'NF');
    $sheet->setCellValue('G1', 'Destino');

    $row = 2;

    while($m = $res->fetch_assoc()){

        $nf = '';
        if(strpos($m['observacao'], 'NF:') !== false){
            $nf = $m['observacao'];
        }

        $sheet->setCellValue('A'.$row, $m['data_movimentacao']);
        $sheet->setCellValue('B'.$row, $m['produto']);
        $sheet->setCellValue('C'.$row, $m['almoxarifado']);
        $sheet->setCellValue('D'.$row, $m['tipo']);
        $sheet->setCellValue('E'.$row, $m['quantidade']);
        $sheet->setCellValue('F'.$row, $nf);
        $sheet->setCellValue('G'.$row, $m['cliente_nome'] ?: $m['destino']);

        $row++;
    }

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="historico.xlsx"');

    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
}

include("../assets/layout.php");

// PAGINAÇÃO
$pagina = $_GET['pagina'] ?? 1;
$limite = 20;
$offset = ($pagina - 1) * $limite;

$total = $conn->query("
SELECT COUNT(*) as total
FROM movimentacoes m
JOIN produtos p ON p.id = m.produto_id
WHERE $where
")->fetch_assoc()['total'];

$paginas = ceil($total / $limite);

$res = $conn->query("
SELECT m.*, p.nome as produto, a.nome as almoxarifado
FROM movimentacoes m
JOIN produtos p ON p.id = m.produto_id
LEFT JOIN almoxarifados a ON a.id = m.almoxarifado_id
WHERE $where
ORDER BY m.id DESC
LIMIT $limite OFFSET $offset
");
?>

<div class="container-fluid">
<div class="card p-4">

<h4 class="mb-3">📋 Histórico de Movimentações</h4>

<form method="GET" class="row g-2 mb-3">

<?php if(!$almox_usuario): ?>
<div class="col-md-2">
<select name="almoxarifado" class="form-control">
<option value="">Todos</option>
<?php
$resA = $conn->query("SELECT * FROM almoxarifados ORDER BY nome");
while($a = $resA->fetch_assoc()){
    $sel = ($filtro_almox == $a['id']) ? 'selected' : '';
    echo "<option value='{$a['id']}' $sel>{$a['nome']}</option>";
}
?>
</select>
</div>
<?php endif; ?>

<div class="col-md-2">
<input type="date" name="data_inicio" class="form-control" value="<?= $data_inicio ?>">
</div>

<div class="col-md-2">
<input type="date" name="data_fim" class="form-control" value="<?= $data_fim ?>">
</div>

<div class="col-md-2">
<select name="tipo" class="form-control">
<option value="">Tipo</option>
<option value="entrada" <?= $tipo=='entrada'?'selected':'' ?>>Entrada</option>
<option value="saida" <?= $tipo=='saida'?'selected':'' ?>>Saída</option>
</select>
</div>

<div class="col-md-2">
<input type="text" name="busca" class="form-control" placeholder="Produto..." value="<?= $busca ?>">
</div>

<div class="col-md-2">
<input type="text" name="nf" class="form-control" placeholder="NF..." value="<?= $nf ?>">
</div>

<div class="col-md-2">
<button class="btn btn-primary w-100">Filtrar</button>
</div>

</form>

<a href="?export=1&almoxarifado=<?= $filtro_almox ?>&data_inicio=<?= $data_inicio ?>&data_fim=<?= $data_fim ?>&tipo=<?= $tipo ?>&busca=<?= $busca ?>&nf=<?= $nf ?>" 
class="btn btn-success mb-3">
📊 Exportar Excel
</a>

<table class="table table-bordered table-hover">

<tr>
<th>Data</th>
<th>Produto</th>
<th>Almox</th>
<th>Tipo</th>
<th>Qtd</th>
<th>NF</th>
<th>Destino</th>
</tr>

<?php while($m = $res->fetch_assoc()): 

$cor = ($m['tipo'] == 'entrada') ? 'style="color:green;font-weight:bold;"' : 'style="color:red;font-weight:bold;"';

$nf = '';
if(strpos($m['observacao'], 'NF:') !== false){
    $nf = $m['observacao'];
}
?>

<tr>
<td><?= date('d/m/Y H:i', strtotime($m['data_movimentacao'])) ?></td>
<td><?= $m['produto'] ?></td>
<td><?= $m['almoxarifado'] ?></td>
<td <?= $cor ?>><?= $m['tipo'] ?></td>
<td <?= $cor ?>><?= $m['quantidade'] ?></td>
<td><?= $nf ?></td>
<td><?= $m['cliente_nome'] ?: $m['destino'] ?></td>
</tr>

<?php endwhile; ?>

</table>

</div>
</div>

<?php include("../assets/footer.php"); ?>