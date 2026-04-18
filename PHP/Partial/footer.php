<footer class="footer">
    <div class="footer-container">

        <!-- TEXTO -->
        <p class="footer-text">
            © <?= date('Y') ?> BeeNerds —
            Renato Matos, Natalia Macedo, Arthur Simões,
            Diego Toscano, Yuri Reis e Matheus
        </p>

        <!-- REDES SOCIAIS -->
        <div class="footer-links">
            <a href="https://www.youtube.com/" target="_blank" title="YouTube">
                <img src="../../Imagens/youtube.png" alt="YouTube">
            </a>

            <a href="https://www.instagram.com/DnNerds" target="_blank" title="Instagram">
                <img src="../../Imagens/instagram.jpeg" alt="Instagram">
            </a>

            <a href="https://www.facebook.com/" target="_blank" title="Facebook">
                <img src="../../Imagens/facebook.png" alt="Facebook">
            </a>

            <a href="https://www.tiktok.com/" target="_blank" title="TikTok">
                <img src="../../Imagens/tiktok.jpeg" alt="TikTok">
            </a>
        </div>

    </div>
</footer>

<style>
    /* =========================
   📌 FOOTER BASE
========================= */
    .footer {
        position: relative;
        background: #202020;
        color: white;

        padding: 15px 20px;
        text-align: center;

        font-family: "Anonymous Pro", monospace;

        box-shadow: 0 -10px 40px rgba(0, 0, 0, 0.6);
    }

    /* =========================
   ✨ LINHA DECORATIVA
========================= */
    .footer::before {
        content: "";
        position: absolute;

        top: 0;
        left: 50%;
        transform: translateX(-50%);

        width: 80%;
        height: 2px;

        background: linear-gradient(90deg,
                transparent,
                var(--texto-header),
                transparent);

        opacity: 0.4;
    }

    /* =========================
   📦 CONTAINER
========================= */
    .footer-container {
        max-width: 1200px;
        margin: 0 auto;

        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 12px;
    }

    /* =========================
   📝 TEXTO
========================= */
    .footer-text {
        font-size: 0.95rem;
        line-height: 1.5;
        opacity: 0.85;
    }

    /* =========================
   🔗 LINKS
========================= */
    .footer-links {
        display: flex;
        gap: 18px;
    }

    /* =========================
   🖼 ICONES
========================= */
    .footer-links img {
        width: 42px;
        height: 42px;

        border-radius: 50%;
        padding: 6px;

        transition: 0.3s ease;
    }

    /* =========================
   🎯 HOVER
========================= */
    .footer-links img:hover {
        transform: translateY(-4px) scale(1.1);
        filter: brightness(1.2);
    }
</style>