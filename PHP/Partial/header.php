<?php
include_once("config.php");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$paginaAtual = basename($_SERVER['PHP_SELF']);
$paginasComBusca = ['Noticias.php', 'Quizzes.php', 'copinhas.php', 'nerdlists.php'];
$temBusca = in_array($paginaAtual, $paginasComBusca);

$fotoUsuario = null;

if (isset($_SESSION['id'])) {
    $sqlFoto = "SELECT foto FROM usuarios WHERE id = ?";
    if ($stmtFoto = $conexao->prepare($sqlFoto)) {
        $stmtFoto->bind_param("i", $_SESSION['id']);
        $stmtFoto->execute();

        $resultFoto = $stmtFoto->get_result();
        $dadosFoto = $resultFoto->fetch_assoc();

        $fotoUsuario = $dadosFoto['foto'] ?? null;
    }
}
?>

<style>
    /* ===================== */
    /* 🎨 NOVA PALETA */
    :root {
        --bg: #0d0d0d;
        --bg-soft: #151515;
        --card: #1c1c1c;

        --amarelo: #ffd000;
        --amarelo-soft: #ffdf4d;

        --verde: #00ff88;

        --texto: #eaeaea;
        --texto-sec: #b5b5b5;
    }

    /* RESET */
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    /* BODY */
    body {
        background: var(--bg);
        font-family: 'Segoe UI', Arial, sans-serif;
        color: var(--texto);
    }

    /* ===================== */
    /* 🧭 NAVBAR */
    .navbar {
        display: flex;
        align-items: center;
        justify-content: space-between;

        height: 80px;
        padding: 0 30px;

        position: fixed;
        top: 0;
        width: 100%;

        background: rgba(13, 13, 13, 0.9);
        backdrop-filter: blur(10px);

        border-bottom: 1px solid rgba(255, 208, 0, 0.2);
        box-shadow: 0 0 30px rgba(0, 0, 0, 0.8);

        z-index: 100;
    }

    /* ===================== */
    /* 🔗 MENU */
    .navbar ul {
        display: flex;
        gap: 15px;
        list-style: none;
    }

    .navbar ul a {
        color: var(--texto-sec);
        text-decoration: none;
        padding: 8px 12px;
        font-size: 14px;
        position: relative;
        transition: 0.3s;
    }

    /* LINHA ANIMADA */
    .navbar ul a::after {
        content: "";
        position: absolute;
        left: 0;
        bottom: -4px;
        width: 0%;
        height: 2px;
        background: var(--amarelo);
        transition: 0.3s;
    }

    .navbar ul a:hover::after,
    .navbar ul a.ativo::after {
        width: 100%;
    }

    .navbar ul a:hover,
    .navbar ul a.ativo {
        color: var(--amarelo);
    }

    /* ===================== */
    /* 🔘 BOTÕES */
    .btn-navbar {
        background: transparent;
        border: 1px solid var(--amarelo);
        color: var(--amarelo);

        padding: 6px 14px;
        border-radius: 8px;
        font-size: 13px;

        cursor: pointer;
        transition: 0.3s;
    }

    .btn-navbar:hover {
        background: var(--amarelo);
        color: black;
    }

    /* ===================== */
    /* 🔍 BUSCA */
    .search-container {
        display: flex;
        align-items: center;

        background: var(--bg-soft);
        border-radius: 10px;
        border: 1px solid transparent;

        overflow: hidden;
        height: 38px;

        transition: 0.3s;
    }

    .search-container input {
        border: none;
        background: transparent;
        color: white;
        padding: 8px;
        outline: none;
        width: 180px;
    }

    .btn-lupa {
        background: none;
        border: none;
        color: var(--amarelo);
        padding: 6px 10px;
        cursor: pointer;
    }

    .search-container:focus-within {
        border: 1px solid var(--amarelo);
        box-shadow: 0 0 10px rgba(255, 208, 0, 0.3);
    }

    /* ===================== */
    /* 🧠 LOGO */
    .title img {
        height: 50px;
        filter: drop-shadow(0 0 6px rgba(255, 208, 0, 0.4));
    }

    /* ===================== */
    /* 👤 USER */
    .user-area,
    .auth-buttons {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .user-photo {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        border: 2px solid transparent;
        transition: 0.3s;
    }

    .user-photo:hover {
        border: 2px solid var(--amarelo);
        transform: scale(1.08);
    }

    /* ===================== */
    /* 🍔 MENU MOBILE */
    .hamburger {
        display: none;
        font-size: 26px;
        color: var(--amarelo);
        background: none;
        border: none;
        cursor: pointer;
    }

    /* ===================== */
    /* 📱 SIDE MENU */
    .side-menu {
        position: fixed;
        top: 0;
        left: -260px;

        width: 220px;
        height: 100%;

        background: var(--bg);
        padding: 25px;

        display: flex;
        flex-direction: column;
        gap: 18px;

        transition: 0.3s;
        z-index: 200;

        border-right: 1px solid rgba(255, 208, 0, 0.2);
    }

    .side-menu a {
        color: var(--texto-sec);
        text-decoration: none;
        padding: 10px;
        border-radius: 8px;
        transition: 0.2s;
    }

    .side-menu a:hover {
        background: rgba(255, 208, 0, 0.1);
        color: var(--amarelo);
    }

    .close-menu {
        background: none;
        border: none;
        color: var(--amarelo);
        font-size: 22px;
        align-self: flex-end;
        cursor: pointer;
    }

    /* ===================== */
    /* 🌫 OVERLAY */
    .menu-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.6);
        opacity: 0;
        pointer-events: none;
        transition: 0.3s;
        z-index: 150;
    }

    .side-menu.open {
        left: 0;
    }

    .menu-overlay.open {
        opacity: 1;
        pointer-events: all;
    }

    /* ===================== */
    /* 📱 RESPONSIVO */
    @media (max-width: 900px) {

        .navbar ul,
        .auth-buttons {
            display: none;
        }

        .hamburger {
            display: block;
        }
    }

    /* ===================== */
    /* ✏️ BOTÃO EDITOR */
    #editor {
        position: fixed;
        bottom: 25px;
        right: 25px;

        background: var(--amarelo);
        color: black;

        border: none;
        padding: 14px 20px;
        border-radius: 10px;

        font-weight: bold;
        cursor: pointer;

        box-shadow: 0 0 15px rgba(255, 208, 0, 0.4);
        transition: 0.2s;
    }

    #editor:hover {
        transform: translateY(-3px);
        box-shadow: 0 0 25px rgba(255, 208, 0, 0.6);
    }
</style>

<script>
    function abrirMenu() {
        document.querySelector(".side-menu").classList.add("open");
        document.querySelector(".menu-overlay").classList.add("open");
    }

    function fecharMenu() {
        document.querySelector(".side-menu").classList.remove("open");
        document.querySelector(".menu-overlay").classList.remove("open");
    }
</script>

<link rel="icon" type="image/jpeg" href="../../Imagens/logo.jpeg?v=2">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link
    href="https://fonts.googleapis.com/css2?family=Merriweather:ital,opsz,wght@0,18..144,300..900;1,18..144,300..900&family=Orbitron:wght@400..900&display=swap"
    rel="stylesheet">

<header>
    <nav class="navbar <?= $temBusca ? 'has-search' : '' ?>">

        <button class="hamburger" onclick="abrirMenu()">☰</button>

        <h2 class="title">
            <img src="../../Imagens/logo.png?v=2" alt="">
        </h2>

        <ul>
            <li><a class="<?= $paginaAtual == 'Noticias.php' ? 'ativo' : '' ?>" href="../Home/Noticias.php">Notícias</a>
            </li>
            <li><a class="<?= $paginaAtual == 'Quizzes.php' ? 'ativo' : '' ?>" href="../Home/Quizzes.php">Quizzes</a>
            </li>
            <li><a class="<?= $paginaAtual == 'nerdlists.php' ? 'ativo' : '' ?>"
                    href="../Home/nerdlists.php">NerdList</a></li>
            <li><a class="<?= $paginaAtual == 'copinhas.php' ? 'ativo' : '' ?>" href="../Home/copinhas.php">Copinhas</a>
            </li>

            <?php if (isset($_SESSION['id'])): ?>
                <li><a class="<?= $paginaAtual == 'criador.php' ? 'ativo' : '' ?>" href="../CREATE/criador.php">Criador</a>
                </li>
            <?php endif; ?>
        </ul>

        <?php if ($temBusca): ?>
            <form class="search-container" action="<?= htmlspecialchars($paginaAtual) ?>" method="GET">
                <button class="btn-lupa">🔍</button>
                <input type="text" name="q" placeholder="Buscar..." required autocomplete="off">
            </form>
        <?php endif; ?>

        <?php if (isset($_SESSION['id'])): ?>
            <div class="user-area">
                <a href="../User/user.php">
                    <img src="../<?= !empty($fotoUsuario) ? $fotoUsuario : '../../Imagens/user.png' ?>" class="user-photo">
                </a>
            </div>
        <?php else: ?>
            <div class="auth-buttons">
                <a href="../User/FazerLogin.php" class="btn-navbar">Login</a>
                <a href="../User/CriarConta.php" class="btn-navbar">Criar Conta</a>
            </div>
        <?php endif; ?>

    </nav>

    <div class="menu-overlay" onclick="fecharMenu()"></div>

    <aside class="side-menu">
        <button class="close-menu" onclick="fecharMenu()">✕</button>

        <a href="../Home/Noticias.php">Notícias</a>
        <a href="../Home/Quizzes.php">Quizzes</a>
        <a href="../Home/nerdlists.php">NerdList</a>
        <a href="../Home/copinhas.php">Copinhas</a>

        <?php if (isset($_SESSION['id'])): ?>
            <a href="../Criar/criador.php">Criador</a>
            <a href="../User/user.php">Perfil</a>
        <?php else: ?>
            <a href="../User/FazerLogin.php">Login</a>
            <a href="../User/CriarConta.php">Criar Conta</a>
        <?php endif; ?>
    </aside>
</header>