<?php 
include("../auth.php");
include("../config/db.php");


if(!isset($_SESSION['user_tipo'])){
    die("Acesso negado");
}

// permite admin e usuario
// EXPORTAR EXCEL
if(isset($_GET['export'])){

    require '../../vendor/autoload.php';

    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    $sheet->setCellValue('A1', 'Produto');
    $sheet->setCellValue('B1', 'Almoxarifado');
    $sheet->setCellValue('C1', 'Tipo');
    $sheet->setCellValue('D1', 'Pessoa');
    $sheet->setCellValue('E1', 'Quantidade');
    $sheet->setCellValue('F1', 'Data Empréstimo');
    $sheet->setCellValue('G1', 'Data Devolução');
    $sheet->setCellValue('H1', 'Status');

    $where = "WHERE 1=1";

    if(!empty($_GET['produto'])){
        $where .= " AND e.produto_id = " . intval($_GET['produto']);
    }

    if(!empty($_GET['tipo'])){
        $tipo = $conn->real_escape_string($_GET['tipo']);
        $where .= " AND e.tipo = '$tipo'";
    }

    if(!empty($_GET['almoxarifado'])){
        $where .= " AND e.almoxarifado_id = " . intval($_GET['almoxarifado']);
    }

    if(!empty($_GET['pessoa'])){
        $pessoa = $conn->real_escape_string($_GET['pessoa']);
        $where .= " AND e.nome_pessoa LIKE '%$pessoa%'";
    }

    if(!empty($_GET['status'])){
        if($_GET['status'] == 'pendente'){
            $where .= " AND e.devolvido = 0 AND (e.data_devolucao IS NULL OR e.data_devolucao >= CURDATE())";
        }
        if($_GET['status'] == 'atrasado'){
            $where .= " AND e.devolvido = 0 AND e.data_devolucao < CURDATE()";
        }
        if($_GET['status'] == 'devolvido'){
            $where .= " AND e.devolvido = 1";
        }
    }

    $sql = "
    SELECT e.*, p.nome as produto, a.nome as almoxarifado
    FROM emprestimos e
    JOIN produtos p ON p.id = e.produto_id
    JOIN almoxarifados a ON a.id = e.almoxarifado_id
    $where
    ORDER BY e.id DESC
    ";

    $res = $conn->query($sql);

    $linha = 2;

    while($row = $res->fetch_assoc()){

        if($row['devolvido']){
            $status = "Devolvido";
        } else if($row['data_devolucao'] && $row['data_devolucao'] < date('Y-m-d')){
            $status = "Atrasado";
        } else {
            $status = "Pendente";
        }

        $sheet->setCellValue("A$linha", $row['produto']);
        $sheet->setCellValue("B$linha", $row['almoxarifado']);
        $sheet->setCellValue("C$linha", $row['tipo']);
        $sheet->setCellValue("D$linha", $row['nome_pessoa']);
        $sheet->setCellValue("E$linha", $row['quantidade']);
        $sheet->setCellValue("F$linha", $row['data_emprestimo']);
        $sheet->setCellValue("G$linha", $row['data_devolucao']);
        $sheet->setCellValue("H$linha", $status);

        $linha++;
    }

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header("Content-Disposition: attachment; filename=emprestimos.xlsx");

    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
}

include("../assets/layout.php"); 
?>

<h3>📊 Relatório de Empréstimos</h3>

<form method="GET" class="row mb-3">

<div class="col">
<select name="produto" class="form-control select2">
<option value="">Produto</option>
<?php
$res = $conn->query("SELECT * FROM produtos");
while($p = $res->fetch_assoc()){
    $sel = ($_GET['produto'] ?? '') == $p['id'] ? 'selected' : '';
    echo "<option value='{$p['id']}' $sel>{$p['nome']}</option>";
}
?>
</select>
</div>

<div class="col">
<select name="almoxarifado" class="form-control">
<option value="">Almoxarifado</option>
<?php
$res = $conn->query("SELECT * FROM almoxarifados");
while($a = $res->fetch_assoc()){
    $sel = ($_GET['almoxarifado'] ?? '') == $a['id'] ? 'selected' : '';
    echo "<option value='{$a['id']}' $sel>{$a['nome']}</option>";
}
?>
</select>
</div>

<div class="col">
<select name="tipo" class="form-control">
<option value="">Tipo</option>
<option value="emprestado">Emprestado</option>
<option value="recebido">Recebido</option>
</select>
</div>

<div class="col">
<select name="status" class="form-control">
<option value="">Status</option>
<option value="pendente">Pendente</option>
<option value="atrasado">Atrasado</option>
<option value="devolvido">Devolvido</option>
</select>
</div>

<div class="col">
<input type="text" name="pessoa" placeholder="Pessoa" class="form-control">
</div>

<div class="col">
<button class="btn btn-primary">Filtrar</button>
</div>

</form>

<div class="mb-3">
<a href="?export=1<?= http_build_query($_GET) ? '&'.http_build_query($_GET) : '' ?>" 
class="btn btn-success">📊 Exportar Excel</a>
</div>

<table class="table table-bordered table-striped">
<tr>
<th>Produto</th>
<th>Almoxarifado</th>
<th>Tipo</th>
<th>Pessoa</th>
<th>Qtd</th>
<th>Data</th>
<th>Devolução</th>
<th>Status</th>
</tr>

<?php

$where = "WHERE 1=1";

if(!empty($_GET['produto'])){
    $where .= " AND e.produto_id = " . intval($_GET['produto']);
}

if(!empty($_GET['tipo'])){
    $where .= " AND e.tipo = '".$conn->real_escape_string($_GET['tipo'])."'";
}

if(!empty($_GET['almoxarifado'])){
    $where .= " AND e.almoxarifado_id = " . intval($_GET['almoxarifado']);
}

if(!empty($_GET['pessoa'])){
    $where .= " AND e.nome_pessoa LIKE '%".$conn->real_escape_string($_GET['pessoa'])."%'";
}

if(!empty($_GET['status'])){
    if($_GET['status'] == 'pendente'){
        $where .= " AND e.devolvido = 0 AND (e.data_devolucao IS NULL OR e.data_devolucao >= CURDATE())";
    }
    if($_GET['status'] == 'atrasado'){
        $where .= " AND e.devolvido = 0 AND e.data_devolucao < CURDATE()";
    }
    if($_GET['status'] == 'devolvido'){
        $where .= " AND e.devolvido = 1";
    }
}

$sql = "
SELECT e.*, p.nome as produto, a.nome as almoxarifado
FROM emprestimos e
JOIN produtos p ON p.id = e.produto_id
JOIN almoxarifados a ON a.id = e.almoxarifado_id
$where
ORDER BY e.id DESC
";

$res = $conn->query($sql);

if($res->num_rows == 0){
    echo "<tr><td colspan='8'>Nenhum registro encontrado</td></tr>";
}

while($row = $res->fetch_assoc()){

    if($row['devolvido']){
        $status = "<span class='badge bg-success'>Devolvido</span>";
    } else if($row['data_devolucao'] && $row['data_devolucao'] < date('Y-m-d')){
        $status = "<span class='badge bg-danger'>Atrasado</span>";
    } else {
        $status = "<span class='badge bg-warning'>Pendente</span>";
    }

    echo "<tr>
        <td>{$row['produto']}</td>
        <td>{$row['almoxarifado']}</td>
        <td>{$row['tipo']}</td>
        <td>{$row['nome_pessoa']}</td>
        <td>{$row['quantidade']}</td>
        <td>{$row['data_emprestimo']}</td>
        <td>{$row['data_devolucao']}</td>
        <td>$status</td>
    </tr>";
}
?>

</table>

<?php include("../assets/footer.php"); ?>