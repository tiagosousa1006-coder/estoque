<?php 
include("../auth.php");
include("../config/db.php");


if(!temPermissao('produtos')){
    die("Acesso negado");
}

if(isset($_GET['excluir'])){
    $id = intval($_GET['excluir']);

    $check = $conn->query("SELECT COUNT(*) as total FROM ferramentas_retiradas WHERE tecnico_id = $id")->fetch_assoc();

    if($check['total'] > 0){
        echo "<script>alert('Não é possível excluir: técnico possui movimentações');</script>";
    } else {
        $conn->query("DELETE FROM tecnicos WHERE id = $id");
        header("Location: listar.php");
        exit;
    }
}

include("../assets/layout.php"); 
?>

<h3>👨‍🔧 Técnicos</h3>

<div class="mb-3">
    <a href="cadastrar.php" class="btn btn-success">➕ Novo Técnico</a>
</div>

<table class="table table-bordered table-striped">
<tr>
    <th>ID</th>
    <th>Nome</th>
    <th>Ação</th>
</tr>

<?php
$res = $conn->query("SELECT * FROM tecnicos ORDER BY nome ASC");

if($res->num_rows == 0){
    echo "<tr><td colspan='3'>Nenhum técnico cadastrado</td></tr>";
}

while($t = $res->fetch_assoc()){

    echo "<tr>
        <td>{$t['id']}</td>
        <td>{$t['nome']}</td>
        <td>
            <a href='?excluir={$t['id']}' 
               class='btn btn-danger btn-sm'
               onclick=\"return confirm('Deseja excluir este técnico?')\">
               Excluir
            </a>
        </td>
    </tr>";
}
?>

</table>

<?php include("../assets/footer.php"); ?>