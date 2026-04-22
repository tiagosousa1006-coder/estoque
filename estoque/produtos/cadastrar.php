<?php 
include("../auth.php");
include("../config/db.php");

// 🔥 PROCESSAR ANTES DO LAYOUT
if($_SERVER['REQUEST_METHOD'] == 'POST'){
    if (!csrf_validate($_POST['csrf_token'] ?? null)) {
        die("Token CSRF inválido");
    }

    $nome = $conn->real_escape_string($_POST['nome']);
    $estoque_minimo = intval($_POST['estoque_minimo']);
    $unidade = $conn->real_escape_string($_POST['unidade']);

    $imagem_nome = "";

    // 🔥 UPLOAD DA IMAGEM
    if(isset($_FILES['foto']) && $_FILES['foto']['error'] == 0){

        $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
        $permitidas = ['jpg','jpeg','png','webp'];

        $mime = mime_content_type($_FILES['foto']['tmp_name']);
        $mimes_permitidos = ['image/jpeg', 'image/png', 'image/webp'];
        $tamanho_maximo = 2 * 1024 * 1024; // 2MB

        if(in_array($ext, $permitidas) && in_array($mime, $mimes_permitidos) && $_FILES['foto']['size'] <= $tamanho_maximo){

            $imagem_nome = uniqid().".".$ext;
            $destino = "../uploads/".$imagem_nome;

            if (is_uploaded_file($_FILES['foto']['tmp_name'])) {
                move_uploaded_file($_FILES['foto']['tmp_name'], $destino);
            }
        }
    }

    // 🔥 INSERT
    $conn->query("
    INSERT INTO produtos (nome, estoque_minimo, unidade, imagem, ativo)
    VALUES ('$nome', $estoque_minimo, '$unidade', '$imagem_nome', 1)
    ");

    header("Location: listar.php?sucesso=1");
    exit;
}

// 👇 LAYOUT
include("../assets/layout.php");
?>

<div class="container-fluid">

<div class="card p-4">

<h4 class="mb-3">📦 Cadastro de Produto</h4>

<form method="POST" enctype="multipart/form-data">
<input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

<div class="mb-3">
<label>Nome do Produto</label>
<input type="text" name="nome" class="form-control" required>
</div>

<div class="mb-3">
<label>Estoque Mínimo</label>
<input type="number" name="estoque_minimo" class="form-control" value="0">
</div>

<div class="mb-3">
<label>Unidade</label>
<select name="unidade" class="form-control">
    <option value="un">Unidade</option>
    <option value="metro">Metro</option>
    <option value="kg">Kg</option>
    <option value="litro">Litro</option>
</select>
</div>

<div class="mb-3">
<label>Imagem do Produto</label>
<input type="file" name="foto" class="form-control" accept="image/*">
</div>

<button class="btn btn-success w-100">
💾 Salvar Produto
</button>

</form>

</div>

</div>

<?php include("../assets/footer.php"); ?>