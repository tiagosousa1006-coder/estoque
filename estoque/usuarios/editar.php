<?php 
include("../auth.php");
include("../admin.php");
include("../config/db.php");

$id = intval($_GET['id']);

$res = $conn->query("SELECT * FROM usuarios WHERE id = $id");

if($res->num_rows == 0){
    die("Usuário não encontrado");
}

$user = $res->fetch_assoc();

if($_POST){

    $nome = $conn->real_escape_string($_POST['nome']);
    $usuario = $conn->real_escape_string($_POST['usuario']);
    $tipo = $_POST['tipo'];

    $almox = !empty($_POST['almoxarifado_id']) 
        ? intval($_POST['almoxarifado_id']) 
        : "NULL";

    $tecnico = !empty($_POST['tecnico_id']) 
        ? intval($_POST['tecnico_id']) 
        : "NULL";

    $permissoes = isset($_POST['permissoes']) 
        ? implode(",", $_POST['permissoes']) 
        : '';

    // 🔐 SENHA OPCIONAL
    if(!empty($_POST['senha'])){
        $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);

        $sql = "
        UPDATE usuarios SET
            nome = '$nome',
            usuario = '$usuario',
            senha = '$senha',
            tipo = '$tipo',
            permissoes = '$permissoes',
            almoxarifado_id = $almox,
            tecnico_id = $tecnico
        WHERE id = $id
        ";

    } else {

        $sql = "
        UPDATE usuarios SET
            nome = '$nome',
            usuario = '$usuario',
            tipo = '$tipo',
            permissoes = '$permissoes',
            almoxarifado_id = $almox,
            tecnico_id = $tecnico
        WHERE id = $id
        ";
    }

    if(!$conn->query($sql)){
        echo "<div class='alert alert-danger'>Erro: ".$conn->error."</div>";
    } else {
        header("Location: listar.php?editado=1");
        exit;
    }
}

include("../assets/layout.php"); 
?>

<div class="container-fluid">

<div class="card p-4" style="border-radius:15px; box-shadow:0 10px 25px rgba(0,0,0,0.05);">

<h4 class="mb-3">✏️ Editar Usuário</h4>

<form method="POST">

<label>Nome</label>
<input type="text" name="nome" class="form-control mb-2" value="<?= $user['nome'] ?>" required>

<label>Usuário</label>
<input type="text" name="usuario" class="form-control mb-2" value="<?= $user['usuario'] ?>" required>

<label>Nova Senha (opcional)</label>
<input type="password" name="senha" class="form-control mb-2">

<label>Tipo</label>
<select name="tipo" class="form-control mb-2">
    <option value="usuario" <?= $user['tipo']=='usuario'?'selected':'' ?>>Usuário</option>
    <option value="admin" <?= $user['tipo']=='admin'?'selected':'' ?>>Admin</option>
</select>

<!-- 🔥 ALMOXARIFADO -->
<label>Almoxarifado</label>
<select name="almoxarifado_id" class="form-control mb-2">
    <option value="">Todos (Admin)</option>

<?php
$resA = $conn->query("SELECT * FROM almoxarifados ORDER BY nome ASC");

while($a = $resA->fetch_assoc()){
    $sel = ($user['almoxarifado_id'] == $a['id']) ? 'selected' : '';
    echo "<option value='{$a['id']}' $sel>{$a['nome']}</option>";
}
?>
</select>

<!-- 🔥 TÉCNICO VINCULADO -->
<label>Técnico vinculado</label>
<select name="tecnico_id" class="form-control mb-3">

<option value="">Nenhum</option>

<?php
$resT = $conn->query("SELECT * FROM tecnicos ORDER BY nome ASC");

while($t = $resT->fetch_assoc()){
    $sel = ($user['tecnico_id'] == $t['id']) ? 'selected' : '';
    echo "<option value='{$t['id']}' $sel>{$t['nome']}</option>";
}
?>
</select>

<label>Permissões</label><br>

<?php
$permissoes_user = explode(",", $user['permissoes']);
?>

<label><input type="checkbox" name="permissoes[]" value="saida" <?= in_array('saida',$permissoes_user)?'checked':'' ?>> Saída</label><br>

<label><input type="checkbox" name="permissoes[]" value="relatorios" <?= in_array('relatorios',$permissoes_user)?'checked':'' ?>> Relatórios</label><br>

<label><input type="checkbox" name="permissoes[]" value="entrada" <?= in_array('entrada',$permissoes_user)?'checked':'' ?>> Entrada</label><br>

<label><input type="checkbox" name="permissoes[]" value="produtos" <?= in_array('produtos',$permissoes_user)?'checked':'' ?>> Produtos</label><br>

<label><input type="checkbox" name="permissoes[]" value="usuarios" <?= in_array('usuarios',$permissoes_user)?'checked':'' ?>> Usuários</label><br><br>

<button class="btn btn-primary w-100">
💾 Salvar Alterações
</button>

</form>

</div>

</div>

<?php include("../assets/footer.php"); ?>