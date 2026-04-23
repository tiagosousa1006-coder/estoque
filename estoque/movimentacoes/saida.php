<?php 
include("../auth.php");
include("../config/db.php");

mysqli_report(MYSQLI_REPORT_OFF);

$user_tipo = $_SESSION['user_tipo'] ?? 'usuario';
$almox_usuario = $_SESSION['almoxarifado_id'] ?? null;
$tecnico_usuario = $_SESSION['tecnico_id'] ?? null;

$mensagem = '';
$mensagemTipo = '';

// 🔥 SALVAR
if($_SERVER['REQUEST_METHOD'] == 'POST'){

    $produto_id   = intval($_POST['produto_id'] ?? 0);
    $quantidade   = intval($_POST['quantidade'] ?? 0);
    $tipo_saida   = $_POST['tipo_saida'] ?? '';
    $destino      = $conn->real_escape_string(trim($_POST['destino'] ?? ''));
    $observacao   = $conn->real_escape_string(trim($_POST['observacao'] ?? ''));

    // 🔒 DEFINE ALMOX
    if($user_tipo != 'admin'){
        $almoxarifado_id = intval($almox_usuario ?? 0);
    } else {
        $almoxarifado_id = intval($_POST['almoxarifado_id'] ?? 0);
    }

    // 🔥 TÉCNICO AUTOMÁTICO
    $tecnico_id = !empty($tecnico_usuario) ? intval($tecnico_usuario) : 'NULL';

    // 🔍 VALIDAÇÃO
    if($produto_id <= 0){
        $mensagem = "Selecione um produto válido";
        $mensagemTipo = 'danger';
    } elseif($almoxarifado_id <= 0){
        $mensagem = "Almoxarifado inválido";
        $mensagemTipo = 'danger';
    } elseif(!in_array($tipo_saida, ['uso_proprio','manutencao','instalacao','emprestimo'], true)){
        $mensagem = "Tipo de saída inválido";
        $mensagemTipo = 'danger';
    } elseif($quantidade <= 0){
        $mensagem = "Quantidade inválida";
        $mensagemTipo = 'danger';
    } else {

        // 🔍 VERIFICA ESTOQUE ATUAL
        $res = $conn->query("\n            SELECT IFNULL(SUM(quantidade),0) as total \n            FROM estoque_local \n            WHERE produto_id = $produto_id \n            AND almoxarifado_id = $almoxarifado_id\n        ");

        if(!$res){
            $mensagem = "Erro ao consultar estoque: " . $conn->error;
            $mensagemTipo = 'danger';
        } else {
            $estoque = intval($res->fetch_assoc()['total'] ?? 0);

            if($estoque < $quantidade){
                $mensagem = "Estoque insuficiente (Disponível: $estoque)";
                $mensagemTipo = 'danger';
            } else {

                // 🔥 REGISTRA MOVIMENTAÇÃO
                $sqlMov = "\n                    INSERT INTO movimentacoes \n                    (produto_id, quantidade, tipo, almoxarifado_id, tecnico_id, destino, observacao, data_movimentacao)\n                    VALUES \n                    ($produto_id, $quantidade, 'saida', $almoxarifado_id, $tecnico_id, '$destino', '$observacao', NOW())\n                ";

                if(!$conn->query($sqlMov)){
                    $mensagem = "Erro ao salvar saída: " . $conn->error;
                    $mensagemTipo = 'danger';
                } else {
                    // 🔥 ATUALIZA ESTOQUE (evita erro de chave duplicada)
                    $sqlEstoque = "\n                        INSERT INTO estoque_local \n                        (produto_id, almoxarifado_id, quantidade)\n                        VALUES \n                        ($produto_id, $almoxarifado_id, -$quantidade)\n                        ON DUPLICATE KEY UPDATE quantidade = quantidade - $quantidade\n                    ";

                    if(!$conn->query($sqlEstoque)){
                        $mensagem = "Erro ao atualizar estoque: " . $conn->error;
                        $mensagemTipo = 'danger';
                    } else {
                        $mensagem = "Saída registrada com sucesso";
                        $mensagemTipo = 'success';
                    }
                }
            }
        }
    }
}

include("../assets/layout.php");
?>

<div class="container-fluid">
<div class="card p-4">

<h4 class="mb-3">📤 Saída de Produto</h4>

<?php if($mensagem): ?>
<div class="alert alert-<?= e($mensagemTipo) ?>"><?= e($mensagem) ?></div>
<?php endif; ?>

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
