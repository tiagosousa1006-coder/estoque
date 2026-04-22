<?php 
include("../auth.php");
include("../config/db.php");
$user_tipo = $_SESSION['user_tipo'] ?? 'usuario';
$almox_usuario = $_SESSION['almoxarifado_id'] ?? null;
$tecnico_usuario = $_SESSION['tecnico_id'] ?? null;

// 🔥 SALVAR
if($_SERVER['REQUEST_METHOD'] == 'POST'){

    $produto_id   = intval($_POST['produto_id']);
    $quantidade   = intval($_POST['quantidade']);
    $tipo_saida   = $_POST['tipo_saida'] ?? '';
    $destino      = $conn->real_escape_string(trim($_POST['destino'] ?? ''));
    $observacao   = $conn->real_escape_string(trim($_POST['observacao'] ?? ''));

    // 🔒 DEFINE ALMOX
    if($user_tipo != 'admin'){
        $almoxarifado_id = $almox_usuario;
    } else {
        $almoxarifado_id = intval($_POST['almoxarifado_id']);
    }

    // 🔥 TÉCNICO AUTOMÁTICO
    $tecnico_id = $tecnico_usuario;

    // 🔍 VALIDAÇÃO
    if(!in_array($tipo_saida, ['uso_proprio','manutencao','instalacao','emprestimo'], true)){
        echo "<div class='alert alert-danger'>Tipo de saída inválido</div>";
    } elseif($quantidade <= 0){
        echo "<div class='alert alert-danger'>Quantidade inválida</div>";
    } else {

        // 🔍 VERIFICA ESTOQUE ATUAL
        $res = $conn->query("
            SELECT SUM(quantidade) as total 
            FROM estoque_local 
            WHERE produto_id = $produto_id 
            AND almoxarifado_id = $almoxarifado_id
        ");

        $estoque = $res->fetch_assoc()['total'] ?? 0;

        if($estoque < $quantidade){

            echo "<div class='alert alert-danger'>
                    ❌ Estoque insuficiente (Disponível: $estoque)
                  </div>";

        } else {

            // 🔥 REGISTRA MOVIMENTAÇÃO
            $conn->query("
                INSERT INTO movimentacoes 
                (produto_id, quantidade, tipo, almoxarifado_id, tecnico_id, destino, observacao, data_movimentacao)
                VALUES 
                ($produto_id, $quantidade, 'saida', $almoxarifado_id, '$tecnico_id', '$destino', '$observacao', NOW())
            ");

            // 🔥 ATUALIZA ESTOQUE
            $conn->query("
                INSERT INTO estoque_local 
                (produto_id, almoxarifado_id, quantidade)
                VALUES 
                ($produto_id, $almoxarifado_id, -$quantidade)
            ");

            echo "<div class='alert alert-success'>✔ Saída registrada com sucesso</div>";
        }
    }
}
?>
<?php include("../assets/layout.php"); ?>

<div class="container-fluid">
<div class="card p-4">

<h4 class="mb-3">📤 Saída de Produto</h4>

<form method="POST">
<input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

<!-- PRODUTO -->
<div class="mb-3">
<label>Produto</label>
<select name="produto_id" class="form-control" required>

<option value="">Selecione</option>

<?php
$res = $conn->query("SELECT * FROM produtos ORDER BY nome ASC");
while($p = $res->fetch_assoc()){
    echo "<option value='{$p['id']}'>{$p['nome']}</option>";
}
?>

</select>
</div>

<!-- ALMOX -->
<?php if($user_tipo == 'admin'): ?>
<div class="mb-3">
<label>Almoxarifado</label>
<select name="almoxarifado_id" class="form-control" required>

<?php
$res = $conn->query("SELECT * FROM almoxarifados ORDER BY nome ASC");
while($a = $res->fetch_assoc()){
    echo "<option value='{$a['id']}'>{$a['nome']}</option>";
}
?>

</select>
</div>
<?php endif; ?>

<!-- QUANTIDADE -->
<div class="mb-3">
<label>Quantidade</label>
<input type="number" name="quantidade" class="form-control" required>
</div>

<!-- TIPO -->
<div class="mb-3">
<label>Tipo de saída</label>
<select name="tipo_saida" class="form-control" required>
<option value="uso_proprio">Uso próprio</option>
<option value="manutencao">Manutenção</option>
<option value="instalacao">Instalação</option>
<option value="emprestimo">Empréstimo</option>
</select>
</div>

<!-- DESTINO -->
<div class="mb-3">
<label>Destino / Cliente / Local</label>
<input type="text" name="destino" class="form-control" placeholder="Ex: João Silva, Cliente XPTO, Torre 3">
</div>

<!-- OBS -->
<div class="mb-3">
<label>Observação</label>
<textarea name="observacao" class="form-control"></textarea>
</div>

<button class="btn btn-primary w-100">Salvar</button>

</form>

</div>
</div>

<?php include("../assets/footer.php"); ?>