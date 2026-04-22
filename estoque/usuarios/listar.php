<?php 
include_once("../auth.php");
include_once("../config/db.php");

// 🔒 SOMENTE ADMIN
if($_SESSION['user_tipo'] !== 'admin'){
    die("Acesso restrito");
}

// 🔥 EXCLUIR USUÁRIO
if(isset($_GET['excluir'])){

    $id = intval($_GET['excluir']);

    // NÃO PERMITE EXCLUIR A SI MESMO
    if($id == $_SESSION['user_id']){
        header("Location: listar.php?erro=1");
        exit;
    }

    // VERIFICA SE EXISTE
    $check = $conn->query("SELECT id FROM usuarios WHERE id = $id");

    if($check && $check->num_rows > 0){
        $conn->query("DELETE FROM usuarios WHERE id = $id");
    }

    header("Location: listar.php?sucesso=1");
    exit;
}

include_once("../assets/layout.php"); 
?>

<h3>👤 Usuários</h3>

<?php
if(isset($_GET['sucesso'])){
    echo "<div class='alert alert-success'>Usuário excluído com sucesso</div>";
}

if(isset($_GET['erro'])){
    echo "<div class='alert alert-danger'>Você não pode excluir seu próprio usuário</div>";
}
?>

<div class="mb-3">
    <a href="cadastrar.php" class="btn btn-success">➕ Novo Usuário</a>
</div>

<div class="table-responsive">
<table class="table table-bordered table-striped">

<tr>
    <th>ID</th>
    <th>Nome</th>
    <th>Usuário</th>
    <th>Tipo</th>
    <th>Ação</th>
</tr>

<?php
$res = $conn->query("SELECT * FROM usuarios ORDER BY nome ASC");

if(!$res || $res->num_rows == 0){
    echo "<tr><td colspan='5'>Nenhum usuário encontrado</td></tr>";
}

while($u = $res->fetch_assoc()){

    $tipo = $u['tipo'] == 'admin'
        ? "<span class='badge bg-danger'>Admin</span>"
        : "<span class='badge bg-secondary'>Usuário</span>";

    echo "<tr>
        <td>{$u['id']}</td>
        <td>{$u['nome']}</td>
        <td>{$u['usuario']}</td>
        <td>$tipo</td>
        <td>
            <a href='editar.php?id={$u['id']}' class='btn btn-primary btn-sm'>Editar</a>
            
            <a href='?excluir={$u['id']}' 
               class='btn btn-danger btn-sm'
               onclick=\"return confirm('Deseja excluir este usuário?')\">
               Excluir
            </a>
        </td>
    </tr>";
}
?>

</table>
</div>

<?php include_once("../assets/footer.php"); ?>