<?php
include_once("../Partial/config.php");
include_once("../Partial/header.php");
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Criar Notícia - DnNerds</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../Styles/Criador.css?v=6">




</head>

<body>

    <div class="container">
        <h2>📰 Criar Nova Notícia</h2>

        <form action="../Save/salvarNoticia.php" method="POST" enctype="multipart/form-data">

            <label>Título</label>
            <input type="text" name="titulo" required>

            <label>Texto da notícia</label>
            <textarea name="texto" rows="8" required></textarea>

            <label>Imagem (Upload)</label>
            <input type="file" name="imagem_upload" accept="image/*">

            <label>OU URL da imagem</label>
            <input type="text" name="imagem_url" placeholder="https://site.com/imagem.jpg">

            <label>Categoria</label>
            <select name="categoria" required>
                <option value="">Selecione</option>
                <option value="Jogos">Jogos</option>
                <option value="Animes">Animes</option>
                <option value="Series/Filmes">Séries/Filmes</option>
                <option value="Livros">Livros</option>
                <option value="Tecnologia">Tecnologia</option>
                <option value="RPG">RPG</option>
            </select>

            <label>Palavra-chave (URL)</label>
            <input type="text" name="palavrachave" placeholder="ex: batman-novo-filme" required>

            <button type="submit">Publicar Notícia</button>

        </form>
    </div>

</body>

</html>
<?php include_once("../Partial/footer.php"); ?>