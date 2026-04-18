<?php
include_once("../Partial/config.php");

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$titulo    = $_POST['titulo'] ?? '';
$descricao = $_POST['descricao'] ?? null;
$imagem    = $_POST['imagem'] ?? null;
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
        if (!$nome) continue;

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
        if (!$nome) continue;

        $img = $_POST['item_imagem'][$i] ?? '';

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
