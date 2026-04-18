<?php
session_start();
include_once("../Partial/config.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email']);
    $senha = $_POST['senha'];

    $sql = "SELECT id, nome, email, senha FROM usuarios WHERE email = ? LIMIT 1";
    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {

        $usuario = $result->fetch_assoc();

        if (password_verify($senha, $usuario['senha'])) {

            session_regenerate_id(true);

            $_SESSION['id'] = $usuario['id'];
            $_SESSION['nome'] = $usuario['nome'];
            $_SESSION['email'] = $usuario['email'];

            header("Location: ../Home/Noticias.php");
            exit;
        }

        $_SESSION['erro'] = "Senha incorreta!";
    } else {
        $_SESSION['erro'] = "Usuário não encontrado!";
    }

    header("Location: FazerLogin.php");
    exit;
}

include_once("../Partial/header.php");
?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DnNerds</title>
    <link rel="stylesheet" href="../../Styles/FazerLogin.css?v=14">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Anonymous+Pro:ital,wght@0,400;0,700;1,400;1,700&family=Caveat&family=Open+Sans:ital,wght@0,400;0,600;0,700;0,800;1,400&family=Poppins:wght@300;600;800&display=swap"
        rel="stylesheet">

</head>

<body>

<main class="container">

    <div class="bg"></div>

    <div id="login">

        <h1>Bem-Vindo!</h1>

        <p>
            Novo no DnNerds?
            <a href="CriarConta.php">Crie uma conta!</a>
        </p>

        <form action="FazerLogin.php" method="POST">

            <label>Email:</label>
            <input type="email" name="email" required>

            <label>Senha:</label>
            <input type="password" name="senha" required>

            <a href="#">Esqueci minha senha</a>

            <button type="submit" id="btn-entrar">
                Entrar
            </button>

        </form>

    </div>

</main>

</body>

</html>