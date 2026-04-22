<?php 
include("../auth.php");
include("../config/db.php");

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
    $destino      = trim($_POST['destino'] ?? '');
    $observacao   = trim($_POST['observacao'] ?? '');

    // 🔒 DEFINE ALMOX
    if($user_tipo != 'admin'){
        $almoxarifado_id = intval($almox_usuario ?? 0);
    } else {
        $almoxarifado_id = intval($_POST['almoxarifado_id'] ?? 0);
    }

    // 🔥 TÉCNICO AUTOMÁTICO
    $tecnico_id = !empty($tecnico_usuario) ? intval($tecnico_usuario) : null;

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
        try {
            // 🔍 VERIFICA ESTOQUE ATUAL
            $stmtSaldo = $conn->prepare("SELECT IFNULL(SUM(quantidade),0) as total FROM estoque_local WHERE produto_id = ? AND almoxarifado_id = ?");
            $stmtSaldo->bind_param("ii", $produto_id, $almoxarifado_id);
            $stmtSaldo->execute();
            $stmtSaldo->bind_result($estoqueTotal);
            $stmtSaldo->fetch();
            $estoque = intval($estoqueTotal ?? 0);
            $stmtSaldo->close();

            if($estoque < $quantidade){
                $mensagem = "Estoque insuficiente (Disponível: {$estoque})";
                $mensagemTipo = 'danger';
            } else {
                $conn->begin_transaction();

                // 🔥 REGISTRA MOVIMENTAÇÃO
                $stmtMov = $conn->prepare("INSERT INTO movimentacoes (produto_id, quantidade, tipo, almoxarifado_id, tecnico_id, destino, observacao, data_movimentacao) VALUES (?, ?, 'saida', ?, ?, ?, ?, NOW())");
                $stmtMov->bind_param("iiiiss", $produto_id, $quantidade, $almoxarifado_id, $tecnico_id, $destino, $observacao);
                $stmtMov->execute();
                $stmtMov->close();

                // 🔥 ATUALIZA ESTOQUE (evita erro de chave duplicada)
                $stmtEstoque = $conn->prepare("INSERT INTO estoque_local (produto_id, almoxarifado_id, quantidade) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE quantidade = quantidade - VALUES(quantidade)");
                $stmtEstoqueQtd = $quantidade;
                $stmtEstoque->bind_param("iii", $produto_id, $almoxarifado_id, $stmtEstoqueQtd);
                $stmtEstoque->execute();
                $stmtEstoque->close();

                $conn->commit();
                $mensagem = "Saída registrada com sucesso";
                $mensagemTipo = 'success';
            }
        } catch (Throwable $e) {
            try {
                $conn->rollback();
            } catch (Throwable $ignored) {
                // sem transação ativa
            }
            $erroBanco = trim($conn->error);
            $mensagem = $erroBanco !== '' ? "Erro ao salvar saída: {$erroBanco}" : "Erro interno ao salvar saída";
            $mensagemTipo = 'danger';
        }
    }
}
?>
<?php include("../assets/layout.php"); ?>

<div class="container-fluid">
<div class="card p-4">

<h4 class="mb-3">📤 Saída de Produto</h4>

<?php if($mensagem): ?>
<div class="alert alert-<?= e($mensagemTipo) ?>">
    <?= e($mensagem) ?>
</div>
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
