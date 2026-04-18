<?php
include_once("../Partial/config.php");

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $titulo = trim($_POST['titulo']);
    $imagem = trim($_POST['imagem']);
    $categoria = $_POST['categoria'];

    $itens_nome = $_POST['item_nome'] ?? [];
    $itens_imagem = $_POST['item_imagem'] ?? [];

    if (empty($titulo) || empty($categoria) || count($itens_nome) < 2) {
        die("Preencha os dados corretamente. A copinha precisa de pelo menos 2 itens.");
    }

    /* ===============================
       INSERIR COPINHA
    ================================ */
    $stmt = $conexao->prepare("
        INSERT INTO copinha (titulo, imagem, categoria)
        VALUES (?, ?, ?)
    ");
    $stmt->bind_param("sss", $titulo, $imagem, $categoria);
    $stmt->execute();

    $copinha_id = $stmt->insert_id;

    /* ===============================
       INSERIR ITENS
    ================================ */
    $stmtItem = $conexao->prepare("
        INSERT INTO item_copinha (copinha_id, nome, imagem)
        VALUES (?, ?, ?)
    ");

    for ($i = 0; $i < count($itens_nome); $i++) {

        if (empty(trim($itens_nome[$i]))) continue;

        $nome = trim($itens_nome[$i]);
        $img  = trim($itens_imagem[$i]);

        $stmtItem->bind_param("iss", $copinha_id, $nome, $img);
        $stmtItem->execute();
    }

    header("Location: ../Homem/copinhas.php");
    exit;
}
