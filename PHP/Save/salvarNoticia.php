<?php
session_start();
include_once("../Partial/config.php");

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $titulo = $_POST['titulo'];
    $texto = $_POST['texto'];
    $categoria = $_POST['categoria'];
    $palavrachave = $_POST['palavrachave'];

    $criador = $_SESSION['id'];

    date_default_timezone_set('America/Sao_Paulo');
    $data_publicacao = date("Y-m-d H:i:s");

    $imagem = null;

    // =========================
    // 1. VERIFICA UPLOAD
    // =========================
    if (isset($_FILES['imagem_upload']) && $_FILES['imagem_upload']['error'] === 0) {

        $tiposPermitidos = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];

        if (in_array($_FILES['imagem_upload']['type'], $tiposPermitidos)) {

            if ($_FILES['imagem_upload']['size'] <= 2 * 1024 * 1024) {

                $pasta = "../../Imagens/";
                $nomeArquivo = uniqid() . "_" . basename($_FILES["imagem_upload"]["name"]);
                $caminho = $pasta . $nomeArquivo;

                if (move_uploaded_file($_FILES["imagem_upload"]["tmp_name"], $caminho)) {
                    $imagem = "" . $nomeArquivo;
                }

            } else {
                die("Imagem muito grande (máx 2MB)");
            }

        } else {
            die("Tipo de arquivo inválido");
        }

    // =========================
    // 2. SENÃO USA URL
    // =========================
    } elseif (!empty($_POST['imagem_url'])) {

        if (filter_var($_POST['imagem_url'], FILTER_VALIDATE_URL)) {
            $imagem = $_POST['imagem_url'];
        }

    }

    // =========================
    // INSERT
    // =========================
    $sql = "INSERT INTO noticias
    (titulo, texto, imagem, categoria, palavrachave, data_publicacao, criador)
    VALUES (?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conexao->prepare($sql);

    $stmt->bind_param(
        "ssssssi",
        $titulo,
        $texto,
        $imagem,
        $categoria,
        $palavrachave,
        $data_publicacao,
        $criador
    );

    if ($stmt->execute()) {
        echo "<script>alert('Notícia publicada com sucesso!'); window.location.href='../Home/Noticias.php';</script>";
    }
}