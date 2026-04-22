<?php 
include("../auth.php");
include("../config/db.php");


if(!temPermissao('produtos')){
    die("Acesso negado");
}

// 🔥 EXCLUIR COM VALIDAÇÃO
if(isset($_GET['excluir'])){
    $id = intval($_GET['excluir']);

    // 🔎 VERIFICA SE TEM PRODUTOS
    $check = $conn->query("
    SELECT id FROM produtos WHERE categoria_id = $id LIMIT 1
    ");

    if($check->num_rows > 0){

        header("Location: listar.php?erro=1");
        exit;

    } else {

        $conn->query("DELETE FROM categorias WHERE id = $id");

        header("Location: listar.php?excluido=1");
        exit;
    }
}

include("../assets/layout.php");
?>

<style>
.card-box {
    background:#fff;
    border-radius:15px;
    padding:20px;
    box-shadow:0 10px 25px rgba(0,0,0,0.05);
}

.badge-cat {
    font-size: 14px;
    padding: 6px 12px;
    border-radius: 10px;
}

.actions {
    display: flex;
    gap: 8px;
    justify-content: center;
    flex-wrap: wrap;
}

.btn-sm {
    display: flex;
    align-items: center;
    gap: 5px;
}
</style>

<div class="container-fluid">

<div class="card-box">

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h4 class="mb-0">🏷️ Categorias</h4>

    <a href="cadastrar.php" class="btn btn-success">
        ➕ Nova Categoria
    </a>
</div>

<?php
if(isset($_GET['excluido'])){
    echo "<div class='alert alert-success'>✔ Categoria excluída com sucesso</div>";
}

if(isset($_GET['editado'])){
    echo "<div class='alert alert-success'>✔ Categoria atualizada</div>";
}

if(isset($_GET['ok'])){
    echo "<div class='alert alert-success'>✔ Categoria cadastrada</div>";
}

if(isset($_GET['erro'])){
    echo "<div class='alert alert-danger'>❌ Não é possível excluir: existem produtos vinculados a esta categoria</div>";
}
?>

<div class="table-responsive">
<table class="table table-bordered table-hover align-middle">

<tr class="table-light text-center">
    <th width="80">ID</th>
    <th>Nome</th>
    <th width="200">Ações</th>
</tr>

<?php
$res = $conn->query("SELECT * FROM categorias ORDER BY nome ASC");

if($res->num_rows == 0){
    echo "<tr><td colspan='3' class='text-center'>Nenhuma categoria cadastrada</td></tr>";
}

while($c = $res->fetch_assoc()){

    echo "<tr>
        <td class='text-center'>{$c['id']}</td>

        <td>
            <span class='badge bg-primary badge-cat'>
                {$c['nome']}
            </span>
        </td>

        <td>
            <div class='actions'>

                <a href='editar.php?id={$c['id']}' 
                   class='btn btn-primary btn-sm'>
                   <i class='bi bi-pencil'></i> Editar
                </a>

                <a href='?excluir={$c['id']}' 
                   class='btn btn-danger btn-sm'
                   onclick=\"return confirm('Deseja excluir esta categoria?')\">
                   <i class='bi bi-trash'></i> Excluir
                </a>

            </div>
        </td>
    </tr>";
}
?>

</table>
</div>

</div>

</div>

<?php include("../assets/footer.php"); ?>