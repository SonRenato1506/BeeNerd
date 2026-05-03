<?php
include_once("../Partial/config.php");

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function uploadImagem($file)
{
    $tiposPermitidos = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];

    if ($file['error'] === 0 && in_array($file['type'], $tiposPermitidos)) {

        if ($file['size'] <= 2 * 1024 * 1024) {

            $pasta = "../../Imagens/";
            $nomeArquivo = uniqid() . "_" . basename($file["name"]);
            $caminho = $pasta . $nomeArquivo;

            if (move_uploaded_file($file["tmp_name"], $caminho)) {
                return $nomeArquivo;
            }
        }
    }

    return null;
}

$titulo = $_POST['titulo'] ?? '';
$descricao = $_POST['descricao'] ?? null;

$imagem = null;
if (isset($_FILES['imagem_upload']) && $_FILES['imagem_upload']['error'] === 0) {
    $imagem = uploadImagem($_FILES['imagem_upload']);
} elseif (!empty($_POST['imagem'])) {
    $imagem = $_POST['imagem'];
}

$categoria = $_POST['categoria'] ?? '';

if (!$titulo || !$categoria) {
    die("Título ou categoria vazios");
}

/* 1️⃣ NerdList */
$sql = "INSERT INTO nerdlist (titulo, descricao, imagem, categoria)
        VALUES (?, ?, ?, ?)";

$stmt = $conexao->prepare($sql);
$stmt->bind_param("ssss", $titulo, $descricao, $imagem, $categoria);
$stmt->execute();

$nerdlist_id = $stmt->insert_id;

if (!$nerdlist_id) {
    die("Erro ao criar NerdList");
}

/* 2️⃣ Tiers */
if (!empty($_POST['tier_nome'])) {
    foreach ($_POST['tier_nome'] as $i => $nome) {
        if (!$nome)
            continue;

        $cor = $_POST['tier_cor'][$i] ?? '#666666';

        $sqlTier = "INSERT INTO nerdlist_tiers (nerdlist_id, nome, cor, ordem)
                    VALUES (?, ?, ?, ?)";

        $stmtTier = $conexao->prepare($sqlTier);
        $stmtTier->bind_param("issi", $nerdlist_id, $nome, $cor, $i);
        $stmtTier->execute();
    }
}

/* 3️⃣ Itens */
if (!empty($_POST['item_nome'])) {
    foreach ($_POST['item_nome'] as $i => $nome) {
        if (!$nome)
            continue;

        $img = null;

        // 1️⃣ Upload
        if (
            isset($_FILES['item_imagem_upload']['name'][$i]) &&
            $_FILES['item_imagem_upload']['error'][$i] === 0
        ) {

            $file = [
                'name' => $_FILES['item_imagem_upload']['name'][$i],
                'type' => $_FILES['item_imagem_upload']['type'][$i],
                'tmp_name' => $_FILES['item_imagem_upload']['tmp_name'][$i],
                'error' => $_FILES['item_imagem_upload']['error'][$i],
                'size' => $_FILES['item_imagem_upload']['size'][$i],
            ];

            $img = uploadImagem($file);
        }

        // 2️⃣ URL (fallback)
        elseif (!empty($_POST['item_imagem'][$i])) {
            $img = $_POST['item_imagem'][$i];
        }

        $sqlItem = "INSERT INTO nerdlist_itens (nerdlist_id, nome, imagem)
                    VALUES (?, ?, ?)";

        $stmtItem = $conexao->prepare($sqlItem);
        $stmtItem->bind_param("iss", $nerdlist_id, $nome, $img);
        $stmtItem->execute();
    }
}

/* 4️⃣ Redireciona */
header("Location: ../Content/nerdlist.php?id=" . $nerdlist_id);
exit;
