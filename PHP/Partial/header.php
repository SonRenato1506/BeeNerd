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
    /* 🎨 Variáveis Globais */
    :root {
        --vermelho1: #E60012;
        --vermelho2: #B8000E;
        --vermelho-hover: #FF1A1A;
        --purple: #7a0099;

        --roxo: #531574;

        --texto-h2: #b429ff;
        --texto-header: #ffffff;

        --preto: #0f0f0f;
        --preto-escuro: #000000;

        --cinza-escuro: #242525;
        --cinza-medio: #4D524E;

        --branco: #ffffff;
    }

    /* ===================== */
    /* 🌐 Reset */
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    /* ===================== */
    /* 🧍 Body */
    body {
        background-color: var(--cinza-escuro);
        font-family: Arial, Helvetica, sans-serif;
    }

    /* ===================== */
    /* 📝 Header */
    header h1,
    header p,
    header a {
        color: var(--texto-header);
        text-transform: uppercase;
    }

    header h2 {
        color: var(--texto-h2);
        text-transform: uppercase;
    }

    /* ===================== */
    /* 📋 Navbar */
    .navbar {
        display: flex;
        align-items: center;
        /* centraliza na altura ✅ */
        justify-content: space-between;
        background-color: var(--preto);
        padding: 0 30px;
        /* remove padding vertical */
        position: fixed;
        top: 0;
        height: 80px;
        width: 100%;
        z-index: 10;
        gap: 20px;
        box-shadow: 0 0 95px var(--preto-escuro);
        border-bottom: 3px solid var(--preto-escuro);
    }

    .navbar.has-search {
        grid-template-columns: auto 1fr auto auto;
    }

    .navbar:not(.has-search) {
        grid-template-columns: auto 1fr auto;
    }

    /* ===================== */
    /* 🔗 Menu */
    .navbar ul {
        display: flex;
        justify-content: center;
        list-style: none;
        gap: 10px;
    }

    .navbar ul a {
        text-decoration: none;
        padding: 10px 18px;
        font-size: 14px;
        border-radius: 25px;
        transition: 0.3s;
    }

    .navbar ul li a:hover,
    .navbar ul li a.ativo {
        background-color: var(--roxo);
        border-radius: 30px;
    }

    /* ===================== */
    /* 🔘 Botões */
    .btn-navbar {
        background-color: var(--vermelho1);
        color: white;
        border: none;
        border-radius: 8px;
        padding: 8px 14px;
        cursor: pointer;
        font-size: 12px;
        text-decoration: none;
        transition: 0.3s;
    }

    .btn-navbar:hover {
        background-color: var(--vermelho-hover);
    }

    /* ===================== */
    /* 🔍 Busca */
    .search-container {
        display: flex;
        align-items: center;
        background-color: var(--cinza-medio);
        border-radius: 10px;
        overflow: hidden;
        height: 38px;
    }

    .btn-lupa {
        background: none;
        border: none;
        padding: 6px 10px;
        cursor: pointer;
        color: white;
    }

    .search-container input {
        border: none;
        padding: 6px 10px;
        outline: none;
        width: 180px;
        background-color: var(--cinza-medio);
    }

    .search-container:focus-within {
        box-shadow: 0 0 10px var(--roxo);
    }

    /* ===================== */
    /* 🧠 Logo */
    /* .title {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 28px;
        white-space: nowrap;
        font-family: 'Orbitron', sans-serif;
        font-size: 32px;
        font-weight: 800;
        letter-spacing: 3px;
        color: #000000;

        text-shadow:
            0 0 5px #a020f0,
            0 0 10px #a020f0,
            0 0 20px #7b2cbf,
            0 0 40px #7b2cbf,
            0 0 60px #5a189a;

        transition: 0.3s ease-in-out;
    } */

    .title {
        display: flex;
        align-items: center;
        /* garante alinhamento interno */
    }

    .title img {
        position: absolute;
        top: 13px;
        left: -50px;
        height: 50px;
        /* 🔥 logo maior */
        width: auto;
        background-color: transparent;
        /* transform: scale(5.5); */
    }

    .title img:hover {
        
        /* opcional, efeito suave */
    }


    /* ===================== */
    /* 👤 Usuário */
    .user-area,
    .auth-buttons {
        display: flex;
        align-items: center;
        gap: 12px;
        white-space: nowrap;
    }

    .user-photo {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        object-fit: cover;
        cursor: pointer;
        transition: 0.3s;
    }

    .user-photo:hover {
        transform: scale(1.1);
    }

    /* ===================== */
    /* 🍔 Hamburger */
    .hamburger {
        display: none;
        background: none;
        border: none;
        font-size: 26px;
        color: white;
        cursor: pointer;
    }

    /* ===================== */
    /* 📱 Side Menu */
    .side-menu {
        position: fixed;
        top: 0;
        left: -260px;
        width: 160px;
        height: 100%;
        background-color: var(--preto);
        box-shadow: 5px 0 25px rgba(0, 0, 0, 0.6);
        display: flex;
        flex-direction: column;
        padding: 25px;
        gap: 18px;
        transition: 0.3s;
        z-index: 20;
    }

    .side-menu a {
        color: white;
        text-decoration: none;
        padding: 10px;
        border-radius: 8px;
    }

    .side-menu a:hover {
        background-color: var(--roxo);
    }

    .close-menu {
        background: none;
        border: none;
        color: white;
        font-size: 22px;
        align-self: flex-end;
        cursor: pointer;
    }

    /* ===================== */
    /* 🌫 Overlay */
    .menu-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.5);
        opacity: 0;
        pointer-events: none;
        transition: 0.3s;
        z-index: 15;
    }

    .side-menu.open {
        left: 0;
    }

    .menu-overlay.open {
        opacity: 1;
        pointer-events: all;
    }

    /* ===================== */
    /* 📱 Responsivo */
    @media (max-width: 1200px) {
        .title {
            display: none;
        }
}

    @media (max-width: 900px) {

        .navbar ul,
        .auth-buttons,
        .title {
            display: none;
        }

        .hamburger {
            display: block;
        }

        .navbar {
            grid-template-columns: 1fr auto;
        }
    }

    
/* ===============================
   BOTÃO EDITAR NOTÍCIA
================================ */

#editor {
    position: fixed;
    bottom: 30px;
    right: 30px;

    background: var(--purple);
    color: white;

    border: none;
    padding: 14px 22px;
    border-radius: 12px;

    font-size: 14px;
    font-weight: 600;
    letter-spacing: 0.5px;

    cursor: pointer;

    box-shadow: 0 8px 25px rgba(0,0,0,0.7);

    transition: 
        transform 0.2s ease,
        box-shadow 0.2s ease,
        background 0.2s ease;
}

/* Hover */
#editor:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 35px rgba(0,0,0,0.9);
    background: #9300c0;
}

/* Clique */
#editor:active {
    transform: scale(0.95);
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
            <li><a class="<?= $paginaAtual == 'Noticias.php' ? 'ativo' : '' ?>" href="../Home/Noticias.php">Notícias</a></li>
            <li><a class="<?= $paginaAtual == 'Quizzes.php' ? 'ativo' : '' ?>" href="../Home/Quizzes.php">Quizzes</a></li>
            <li><a class="<?= $paginaAtual == 'nerdlists.php' ? 'ativo' : '' ?>" href="../Home/nerdlists.php">NerdList</a></li>
            <li><a class="<?= $paginaAtual == 'copinhas.php' ? 'ativo' : '' ?>" href="../Home/copinhas.php">Copinhas</a></li>

            <?php if (isset($_SESSION['id'])): ?>
                <li><a class="<?= $paginaAtual == 'criador.php' ? 'ativo' : '' ?>" href="../CREATE/criador.php">Criador</a></li>
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