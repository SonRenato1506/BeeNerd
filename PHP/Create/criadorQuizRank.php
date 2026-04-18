<?php include_once("../Partial/config.php"); include_once("../Partial/header.php");?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Criar Quiz Rank - DnNerds</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../Styles/Criador.css?v=3">
</head>

<body>


    <div class="container">

        <h2>🏆 Criar Quiz Rank</h2>

        <form action="../Save/salvarQuizRank.php" method="POST">

            <label>Título</label>
            <input type="text" name="titulo" required>

            <label>Descrição</label>
            <textarea name="descricao" required></textarea>

            <label>Categoria</label>
            <select name="categoria" required>
                <option value="">Selecione</option>
                <option value="jogos">Jogos</option>
                <option value="animes">Animes</option>
                <option value="filmes/series">Filmes / Séries</option>
                <option value="futebol">Futebol</option>
                <option value="basquete">Basquete</option>
                <option value="variados">Variados</option>
            </select>

            <label>Imagem</label>
            <input type="text" name="imagem" placeholder="../Imagens/quizdefault.jpg">

            <hr>

            <h3>📋 Itens do Rank</h3>

            <div id="itens"></div>

            <button type="button" onclick="addItem()">➕ Adicionar Item</button>
            <button type="submit">🚀 Salvar Quiz Rank</button>

        </form>

    </div>

    <script>
        let posicao = 0;

        function addItem() {
            posicao++;

            const div = document.createElement("div");
            div.className = "pergunta";

            div.innerHTML = `
        <h4>#${posicao}</h4>

        <input type="hidden" name="itens[${posicao}][posicao]" value="${posicao}">

        <label>Nome</label>
        <input type="text" name="itens[${posicao}][nome]" required>

        <label>Dica (opcional)</label>
        <input type="text" name="itens[${posicao}][dica]">

        <hr>
    `;

            document.getElementById("itens").appendChild(div);
        }
    </script>

</body>

</html>