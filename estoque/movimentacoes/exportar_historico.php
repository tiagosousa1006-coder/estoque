<?php
include("../config/db.php");
require '../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// 🔍 FILTROS
$where = "1=1";

if(!empty($_GET['data_inicio'])){
    $where .= " AND DATE(m.data_movimentacao) >= '".$_GET['data_inicio']."'";
}

if(!empty($_GET['data_fim'])){
    $where .= " AND DATE(m.data_movimentacao) <= '".$_GET['data_fim']."'";
}

if(!empty($_GET['tipo'])){
    $where .= " AND m.tipo = '".$_GET['tipo']."'";
}

if(!empty($_GET['subtipo'])){
    $where .= " AND m.subtipo LIKE '%".$_GET['subtipo']."%'";
}

if(!empty($_GET['produto'])){
    $where .= " AND p.nome LIKE '%".$_GET['produto']."%'";
}

// 🔎 CONSULTA
$res = $conn->query("
SELECT m.*, p.nome as produto, a.nome as almoxarifado
FROM movimentacoes m
JOIN produtos p ON p.id = m.produto_id
LEFT JOIN almoxarifados a ON a.id = m.almoxarifado_id
WHERE $where
ORDER BY m.id DESC
");

// 📊 PLANILHA
$planilha = new Spreadsheet();
$sheet = $planilha->getActiveSheet();

// CABEÇALHO
$sheet->setCellValue('A1', 'Data');
$sheet->setCellValue('B1', 'Produto');
$sheet->setCellValue('C1', 'Almoxarifado');
$sheet->setCellValue('D1', 'Tipo');
$sheet->setCellValue('E1', 'Subtipo');
$sheet->setCellValue('F1', 'Quantidade');
$sheet->setCellValue('G1', 'Detalhes');

// DADOS
$linha = 2;

while($m = $res->fetch_assoc()){

    $detalhes = "";

    // ENTRADA
    if($m['tipo'] == 'entrada'){
        $detalhes = $m['observacao'] ?? '';
    }

    // SAÍDA
    if($m['tipo'] == 'saida'){

        if(!empty($m['cliente_nome'])){
            $detalhes = "Cliente: ".$m['cliente_nome'];
        }

        if(!empty($m['tecnico_nome'])){
            $detalhes = "Técnico: ".$m['tecnico_nome'];

            if(!empty($m['destino'])){
                $detalhes .= " | Destino: ".$m['destino'];
            }
        }
    }

    $sheet->setCellValue("A$linha", date('d/m/Y H:i', strtotime($m['data_movimentacao'])));
    $sheet->setCellValue("B$linha", $m['produto']);
    $sheet->setCellValue("C$linha", $m['almoxarifado']);
    $sheet->setCellValue("D$linha", $m['tipo']);
    $sheet->setCellValue("E$linha", $m['subtipo']);
    $sheet->setCellValue("F$linha", $m['quantidade']);
    $sheet->setCellValue("G$linha", $detalhes);

    $linha++;
}

// 🔽 DOWNLOAD
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="historico_completo.xlsx"');

$writer = new Xlsx($planilha);
$writer->save('php://output');
exit;