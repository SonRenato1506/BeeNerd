<?php
include_once("../Partial/config.php");
include_once("../Partial/header.php");
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Criar NerdList - DnNerds</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="../../Styles/Criador.css?v=2">
</head>

<body>

    <div class="container">
        <h2>🧠 Criar Nova NerdList</h2>

        <form action="../Save/salvarNerdList.php" method="POST">

            <label>Título da NerdList</label>
            <input type="text" name="titulo" required>

            <label>Descrição</label>
            <textarea name="descricao"></textarea>

            <label>Imagem (URL)</label>
            <input type="text" name="imagem">

            <label>Categoria</label>
            <select name="categoria" required>
                <option value="">Selecione</option>
                <option value="Animes">Animes</option>
                <option value="Games">Games</option>
                <option value="Filmes">Filmes</option>
                <option value="Series">Séries</option>
                <option value="Livros">Livros</option>
                <option value="Variados">Variados</option>
            </select>

            <hr>

            <h3>🏷️ Tiers</h3>

            <div id="tiers">
                <div class="item">
                    <label>Nome do Tier</label>
                    <input type="text" name="tier_nome[]" required>

                    <label>Cor do Tier</label>
                    <select name="tier_cor[]" onchange="previewTierColor(this)" required>
                        <option value="#e74c3c">Vermelho</option>
                        <option value="#e67e22">Laranja</option>
                        <option value="#f1c40f">Amarelo</option>
                        <option value="#2ecc71">Verde claro</option>
                        <option value="#27ae60">Verde escuro</option>
                        <option value="#3498db">Azul claro</option>
                        <option value="#2c3e50">Azul escuro</option>
                        <option value="#fd79a8">Rosa</option>
                        <option value="#9b59b6">Roxo</option>
                        <option value="#8e6e53">Marrom</option>
                        <option value="#7f8c8d">Cinza</option>
                        <option value="#ecf0f1">Branco</option>
                    </select>

                </div>
            </div>

            <button type="button" onclick="addTier()">➕ Adicionar Tier</button>

            <hr>

            <h3>🎮 Itens</h3>

            <div id="itens">
                <div class="item">
                    <label>Nome</label>
                    <input type="text" name="item_nome[]" required>
                    <label>Imagem (URL)</label>
                    <input type="text" name="item_imagem[]" required>
                </div>
            </div>

            <button type="button" class="Criar" onclick="addItem()">➕ Adicionar Item</button>

            <br><br>
            <button type="submit">Criar NerdList</button>
        </form>
    </div>

    <script>
        function addTier() {
            const div = document.createElement("div");
            div.className = "item";
            div.innerHTML = `
        <label>Nome do Tier</label>
        <input type="text" name="tier_nome[]" required>

        <label>Cor do Tier</label>
                    <select name="tier_cor[]"  onchange="previewTierColor(this)" required>
                        <option value="#e74c3c">Vermelho</option>
                        <option value="#e67e22">Laranja</option>
                        <option value="#f1c40f">Amarelo</option>
                        <option value="#2ecc71">Verde claro</option>
                        <option value="#27ae60">Verde escuro</option>
                        <option value="#3498db">Azul claro</option>
                        <option value="#2c3e50">Azul escuro</option>
                        <option value="#fd79a8">Rosa</option>
                        <option value="#9b59b6">Roxo</option>
                        <option value="#8e6e53">Marrom</option>
                        <option value="#7f8c8d">Cinza</option>
                        <option value="#ecf0f1">Branco</option>
                    </select>    `;
            document.getElementById("tiers").appendChild(div);
        }

        function addItem() {
            const div = document.createElement("div");
            div.className = "item";
            div.innerHTML = `
        <label>Nome</label>
        <input type="text" name="item_nome[]" required>

        <label>Imagem (URL)</label>
        <input type="text" name="item_imagem[]" required>
    `;
            document.getElementById("itens").appendChild(div);
        }
    </script>

    
    <script>
        function previewTierColor(select) {
            select.style.backgroundColor = select.value;
            select.style.color = "#000";
        }
    </script>
</body>

</html>

<?php include_once("../Partial/footer.php"); ?>
