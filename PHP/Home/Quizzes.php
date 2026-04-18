<?php
include_once('../Partial/config.php');
include_once("../Partial/header.php");

/* ===============================
   CATEGORIAS
================================ */


/* ===============================
   TIPO DE QUIZ
================================ */
$tipo = $_GET['tipo'] ?? 'normal';

switch ($tipo) {

    case 'personalidade':
        $tabela = 'personalidade';
        $paginaQuiz = 'quiz_personalidade.php';
        $categorias = [
            'Games' => 'Games',
            'Anime' => 'Anime',
            'Series' => 'Séries',
            'Filmes' => 'Filmes',
            'Livros' => 'Livros',
            'Variados' => 'Variados'
        ];
        break;

    case 'rank':
        $tabela = 'quizzes_rank';
        $paginaQuiz = 'quizRank.php';
        $categorias = [
            'jogos' => 'Jogos',
            'animes' => 'Animes',
            'filmes/series' => 'Filmes / Séries',
            'futebol' => 'Futebol',
            'basquete' => 'Basquete',
            'variados' => 'Variados'
        ];
        break;

    case 'list':
        $tabela = 'quizzes_list';
        $paginaQuiz = 'quiz_list.php';
        $categorias = [
            'Games' => 'Games',
            'Anime' => 'Anime',
            'Series' => 'Séries',
            'Filmes' => 'Filmes',
            'Livros' => 'Livros',
            'Variados' => 'Variados'
        ];
        break;

    default:
        $tabela = 'quizzes';
        $paginaQuiz = 'quiz.php';
        $categorias = [
            'Games' => 'Games',
            'Anime' => 'Anime',
            'Series' => 'Séries',
            'Filmes' => 'Filmes',
            'Livros' => 'Livros',
            'Variados' => 'Variados'
        ];
        break;
}



if ($tipo === 'personalidade') {
    $linkEditor = 'criadorPersonalidade.php';
} elseif ($tipo === 'rank') {
    $linkEditor = 'criadorQuizRank.php';
} else {
    $linkEditor = 'criadorQuiz.php';
}


?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>DnNerds - Quizzes</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- CSS -->
    <link rel="stylesheet" href="../../Styles/Noticias.css?v=29">


</head>

<body>
    <main>

        <!-- TIPOS DE QUIZ -->
        <div id="tipoQuiz">


            <a href="Quizzes.php?tipo=normal" class="tipo-link <?= $tipo === 'normal' ? 'ativo' : '' ?>">
                Quizzes
            </a>

            <a href="Quizzes.php?tipo=personalidade" class="tipo-link <?= $tipo === 'personalidade' ? 'ativo' : '' ?>">
                Quiz de Personalidade
            </a>

            <a href="Quizzes.php?tipo=rank" class="tipo-link <?= $tipo === 'rank' ? 'ativo' : '' ?>">
                Quiz Rank
            </a>

        </div>


        <?php
        /* ===============================
           BUSCA
        ================================ */
        if (!empty($_GET['q'])) {
            $termo = $conexao->real_escape_string(trim($_GET['q']));
            echo "<h2 class='classe'>Resultados para: " . htmlspecialchars($termo) . "</h2>";

            $sqlBusca = "
            SELECT * FROM quizzes
            WHERE titulo LIKE '%$termo%'
               OR descricao LIKE '%$termo%'
            ORDER BY id DESC
        ";

            $resultBusca = $conexao->query($sqlBusca);

            if ($resultBusca && $resultBusca->num_rows > 0) {
                echo '<div class="carousel-container">
                    <button class="carousel-btn left">&#10094;</button>
                    <div class="carousel">';

                while ($quiz = $resultBusca->fetch_assoc()) {
                    ?>
                    <a href="../Content/<?= $paginaQuiz ?>?id=<?= $quiz['id'] ?>">
                        <div id="caixa">
                            <img src="../<?= htmlspecialchars($quiz['imagem'] ?: 'quizdefault.jpg') ?>">">
                            <p><b><?= htmlspecialchars($quiz['titulo']) ?></b></p>
                            <!-- <small><?= htmlspecialchars(substr($quiz['descricao'], 0, 80)) ?>...</small> -->
                        </div>
                    </a>
                    <?php
                }

                echo '</div><button class="carousel-btn right">&#10095;</button></div>';
            } else {
                echo "<p>Nenhum quiz encontrado.</p>";
            }
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
                $sql = "SELECT * FROM $tabela WHERE categoria='$key' ORDER BY id DESC LIMIT 10";
                $result = $conexao->query($sql);
                ?>

                <div class="carousel-container">
                    <button class="carousel-btn left">&#10094;</button>

                    <div class="carousel">
                        <?php if ($result && $result->num_rows > 0): ?>
                            <?php while ($quiz = $result->fetch_assoc()): ?>
                                <a href="../Content/<?= $paginaQuiz ?>?id=<?= $quiz['id'] ?>">
                                    <div id="caixa">
                                        <img src="../<?= htmlspecialchars($quiz['imagem'] ?: 'quizdefault.jpg') ?>">
                                        <p><b><?= htmlspecialchars($quiz['titulo']) ?></b></p>
                                        <!-- <small><?= htmlspecialchars(substr($quiz['descricao'], 0, 80)) ?>...</small> -->
                                    </div>
                                </a>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p>Nenhum quiz nesta categoria.</p>
                        <?php endif; ?>
                    </div>

                    <button class="carousel-btn right">&#10095;</button>
                </div>

            <?php endforeach; ?>
        </section>

    </main>

    <!-- SCRIPT -->
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