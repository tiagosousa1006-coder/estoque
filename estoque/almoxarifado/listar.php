<?php 
include("../auth.php");
include("../config/db.php");
include("../assets/layout.php");

// 🔥 EXCLUIR
if(isset($_GET['excluir'])){
    $id = intval($_GET['excluir']);

    // 🚫 NÃO PERMITE EXCLUIR SE TIVER MOVIMENTAÇÃO
    $check = $conn->query("
    SELECT COUNT(*) as total 
    FROM movimentacoes 
    WHERE almoxarifado_id = $id
    ");

    if($check->fetch_assoc()['total'] > 0){
        header("Location: listar.php?erro=movimentacao");
        exit;
    }

    $conn->query("DELETE FROM almoxarifados WHERE id = $id");

    header("Location: listar.php?sucesso=excluido");
    exit;
}
?>

<div class="container-fluid">

<div class="card p-4">

<h4 class="mb-3">🏢 Almoxarifados</h4>

<!-- MENSAGENS -->
<?php if(isset($_GET['sucesso'])): ?>
<div class="alert alert-success">✔ Operação realizada com sucesso</div>
<?php endif; ?>

<?php if(isset($_GET['erro']) && $_GET['erro']=='movimentacao'): ?>
<div class="alert alert-danger">
❌ Não é possível excluir: existe movimentação vinculada
</div>
<?php endif; ?>

<a href="cadastrar.php" class="btn btn-primary mb-3">
➕ Novo Almoxarifado
</a>

<div class="table-responsive">
<table class="table table-bordered table-hover align-middle">

<tr class="table-light text-center">
    <th>Nome</th>
    <th>Ações</th>
</tr>

<?php
$res = $conn->query("SELECT * FROM almoxarifados ORDER BY nome ASC");

if($res->num_rows == 0){
    echo "<tr><td colspan='2' class='text-center'>Nenhum almoxarifado cadastrado</td></tr>";
}

while($a = $res->fetch_assoc()):
?>

<tr>

<td>
    <!-- 🔥 LINK PARA ESTOQUE FILTRADO -->
    <a href="/estoque/estoque/listar.php?almoxarifado=<?= $a['id'] ?>" 
       style="text-decoration:none;font-weight:600;color:#2563eb;">
       <?= $a['nome'] ?>
    </a>
</td>

<td class="text-center">

    <a href="editar.php?id=<?= $a['id'] ?>" 
       class="btn btn-sm btn-warning">
       ✏️ Editar
    </a>

    <a href="?excluir=<?= $a['id'] ?>" 
       class="btn btn-sm btn-danger"
       onclick="return confirm('Tem certeza que deseja excluir este almoxarifado?')">
       🗑️ Excluir
    </a>

</td>

</tr>

<?php endwhile; ?>

</table>
</div>

</div>

</div>

<?php include("../assets/footer.php"); ?>