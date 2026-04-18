<?php
include_once('../Partial/config.php');
include_once("../Partial/header.php");

/* ===============================
   CATEGORIAS
================================ */
$categorias = [
    'Games' => 'Games',
    'Animes' => 'Anime',
    'Series' => 'Séries',
    'Filmes' => 'Filmes',
    'Livros' => 'Livros',
    'Variados' => 'Variados'
];

/* ===============================
   BUSCA
================================ */
$busca = $_GET['q'] ?? '';
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>DnNerds - NerdLists</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- CSS (MESMO DOS QUIZZES) -->
    <link rel="stylesheet" href="../../Styles/Noticias.css?v=30">

</head>

<body>

<main>

<?php
/* ===============================
   RESULTADO DE BUSCA
================================ */
if (!empty($busca)) {
    $termo = $conexao->real_escape_string($busca);

    echo "<h2 class='classe'>Resultados para: " . htmlspecialchars($busca) . "</h2>";

    $sqlBusca = "
        SELECT * FROM nerdlist
        WHERE titulo LIKE '%$termo%'
           OR descricao LIKE '%$termo%'
        ORDER BY id DESC
    ";

    $resultBusca = $conexao->query($sqlBusca);

    if ($resultBusca && $resultBusca->num_rows > 0) {
        echo '<div class="carousel-container">
                <button class="carousel-btn left">&#10094;</button>
                <div class="carousel">';

        while ($list = $resultBusca->fetch_assoc()) {
            ?>
            <a href="../Content/nerdlist.php?id=<?= $list['id'] ?>">
                <div id="caixa">
                    <img src="../<?= htmlspecialchars($list['imagem'] ?: 'nerdlistdefault.jpg') ?>">
                    <p><b><?= htmlspecialchars($list['titulo']) ?></b></p>
                    <!-- <small><?= htmlspecialchars(substr($list['descricao'], 0, 80)) ?>...</small> -->
                </div>
            </a>
            <?php
        }

        echo '</div>
              <button class="carousel-btn right">&#10095;</button>
              </div>';
    } else {
        echo "<p>Nenhuma NerdList encontrada.</p>";
    }

    echo "</main>";
    include_once("footer.php");
    exit;
}
?>

<!-- LISTAGEM POR CATEGORIA -->
<section id="quizzes">

<?php foreach ($categorias as $key => $titulo): ?>

    <div class="classe">
        <h2><?= $titulo ?></h2>
    </div>

    <?php
    $sql = "
        SELECT * FROM nerdlist
        WHERE categoria = '$key'
        ORDER BY id DESC
        LIMIT 10
    ";
    $result = $conexao->query($sql);
    ?>

    <div class="carousel-container">
        <button class="carousel-btn left">&#10094;</button>

        <div class="carousel">
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($list = $result->fetch_assoc()): ?>
                    <a href="../Content/nerdlist.php?id=<?= $list['id'] ?>">
                        <div id="caixa">
                            <img src="../<?= htmlspecialchars($list['imagem'] ?: 'nerdlistdefault.jpg') ?>">
                            <p><b><?= htmlspecialchars($list['titulo']) ?></b></p>
                            <!-- <small><?= htmlspecialchars(substr($list['descricao'], 0, 80)) ?>...</small> -->
                        </div>
                    </a>
                <?php endwhile; ?>
            <?php else: ?>
                <p>Nenhuma NerdList nesta categoria.</p>
            <?php endif; ?>
        </div>

        <button class="carousel-btn right">&#10095;</button>
    </div>

<?php endforeach; ?>

</section>

</main>

<!-- SCRIPT DO CARROSSEL (IGUAL AO QUIZ) -->
<script>
document.querySelectorAll(".carousel-container").forEach(container => {
    const carousel = container.querySelector(".carousel");

    container.querySelector(".left").onclick = () =>
        carousel.scrollBy({ left: -250, behavior: "smooth" });

    container.querySelector(".right").onclick = () =>
        carousel.scrollBy({ left: 250, behavior: "smooth" });
});
</script>

</body>
</html>

<?php include_once("../Partial/footer.php"); ?>