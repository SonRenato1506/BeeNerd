<?php
include_once('../Partial/config.php');
include_once("../Partial/header.php");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DnNerds - Criar</title>
    <style>
        body {
            padding-top: 100px;
            padding-bottom: 20px;
        }

        .fileira {
            width: 100%;
            display: flex;
            gap: 50px;
            margin-top: 50px;
            justify-content: center;
            flex-wrap: wrap;
        }

        h1 {
            text-align: center;
            font-size: 32px;
            color: #b836ff;

        }

        a {
            text-decoration: none;
            color: black;
            /* color: #b836ff; */
        }

        p {
            display: none;
        }

        .fileira a>div {}

        .caixa {
            height: 200px;
            width: 250px;
            border: 2px solid black;
            border-radius: 20px;
            padding-top: 5px;

            background-image: url("../../Imagens/logo.jpeg?v=2");
            background-repeat: no-repeat;
            background-size: cover;
            background-position: top;
            
            box-shadow: 0 0 10px black;
            display: flex;
            flex-direction: column;
            /* justify-content: center; */
            /* align-items: center; */
            text-align: center;
        }
    </style>
</head>

<body>
    <h1>O que você deseja criar, jovem Padawan?</h1>
    <main>
        <div class="fileira">
            <a href="criadorNoticias.php">
                <div class="caixa">
                    <h2>Criar Notícia</h2>
                    <p>Crie a sua notícia aqui!</p>
                </div>
            </a>
            <a href="criadorNerdList.php">
                <div class="caixa">
                    <h2>Criar NerdList</h2>
                    <p>Crie a sua NerdList aqui!</p>
                </div>
            </a>
            <a href="criadorCopinhas.php">
                <div class="caixa">
                    <h2>Criar Copinha</h2>
                    <p>Crie a sua Copinha aqui!</p>
                </div>
            </a>
        </div>

        <div class="fileira">
            <a href="criadorQuiz.php">
                <div class="caixa">
                    <h2>Quiz Conhecimento</h2>
                    <p>Crie o seu Quiz de Conhecimento aqui!</p>
                </div>
            </a>
            <a href="criadorPersonalidade.php">
                <div class="caixa">
                    <h2>Quiz Personalidade</h2>
                    <p>Crie o seu Quiz de Personalidade aqui!</p>
                </div>
            </a>
            <a href="criadorQuizRank.php">
                <div class="caixa">
                    <h2>Quiz de Rank</h2>
                    <p>Crie o seu Quiz de Rank aqui!</p>
                </div>
            </a>
        </div>
    </main>
</body>

</html>