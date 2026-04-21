<?php 
include("../auth.php");
include("../config/db.php");
include("../assets/layout.php");

// 🔒 DADOS DO USUÁRIO
$tecnico_usuario = $_SESSION['tecnico_id'] ?? null;
$tipo_usuario = $_SESSION['user_tipo'] ?? 'usuario';

// 🔎 WHERE BASE
$where = "1=1";

// 👤 USUÁRIO COMUM → BLOQUEIO TOTAL
if($tipo_usuario != 'admin'){

    if(!$tecnico_usuario){
        // ❌ NÃO TEM TÉCNICO → NÃO VÊ NADA
        $where .= " AND 1=0";
    } else {
        // ✔ SÓ SUAS FERRAMENTAS
        $where .= " AND f.tecnico_id = $tecnico_usuario";
    }
}

// 🔥 QUERY
$sql = "
SELECT f.*, p.nome as produto, t.nome as tecnico
FROM ferramentas_retiradas f
JOIN produtos p ON p.id = f.produto_id
JOIN tecnicos t ON t.id = f.tecnico_id
WHERE $where
ORDER BY f.id DESC
";

$res = $conn->query($sql);
?>

<div class="container-fluid">

<div class="card p-4" style="border-radius:15px; box-shadow:0 10px 25px rgba(0,0,0,0.05);">

<h4 class="mb-3">🛠 Relatório de Ferramentas</h4>

<?php if($tipo_usuario != 'admin'): ?>

    <?php if($tecnico_usuario): 
        $resT = $conn->query("SELECT nome FROM tecnicos WHERE id = $tecnico_usuario");
        $nomeTec = $resT->fetch_assoc()['nome'] ?? '';
    ?>
    
    <div class="alert alert-info">
    🧑‍🔧 Você está visualizando suas ferramentas (<?= $nomeTec ?>)
    </div>

    <?php else: ?>

    <div class="alert alert-warning">
    ⚠️ Seu usuário não está vinculado a um técnico.
    </div>

    <?php endif; ?>

<?php endif; ?>

<div class="table-responsive">
<table class="table table-bordered table-hover align-middle">

<tr class="table-light text-center">
    <th>Ferramenta</th>
    <th>Técnico</th>
    <th>Retirada</th>
    <th>Devolução</th>
    <th>Status</th>
</tr>

<?php
if($res->num_rows == 0){
    echo "<tr><td colspan='5' class='text-center'>Nenhuma ferramenta encontrada</td></tr>";
}

while($f = $res->fetch_assoc()){

    // STATUS
    if($f['devolvido'] == 1){
        $status = "<span class='badge bg-secondary'>Devolvido</span>";
    } elseif($f['data_devolucao'] && $f['data_devolucao'] < date('Y-m-d')){
        $status = "<span class='badge bg-danger'>Atrasado</span>";
    } else {
        $status = "<span class='badge bg-success'>Em uso</span>";
    }

    echo "<tr>
        <td>{$f['produto']}</td>
        <td>{$f['tecnico']}</td>
        <td>{$f['data_retirada']}</td>
        <td>".($f['data_devolucao'] ?: '-')."</td>
        <td class='text-center'>$status</td>
    </tr>";
}
?>

</table>
</div>

</div>

</div>

<?php include("../assets/footer.php"); ?>