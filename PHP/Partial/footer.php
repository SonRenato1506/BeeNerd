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
   📌 FOOTER MODERNO
========================= */
    .footer {
        position: relative;
        background: #0d0d0d;
        color: #eaeaea;

        padding: 25px 20px;
        text-align: center;

        border-top: 1px solid rgba(255, 208, 0, 0.2);
        box-shadow: 0 -10px 30px rgba(0, 0, 0, 0.8);
    }

    /* =========================
   ✨ LINHA NEON
========================= */
    .footer::before {
        content: "";
        position: absolute;

        top: 0;
        left: 50%;
        transform: translateX(-50%);

        width: 70%;
        height: 2px;

        background: linear-gradient(90deg,
                transparent,
                #ffd000,
                #00ff88,
                #ffd000,
                transparent);

        opacity: 0.6;
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
        gap: 14px;
    }

    /* =========================
   📝 TEXTO
========================= */
    .footer-text {
        font-size: 0.95rem;
        color: #b5b5b5;
        line-height: 1.5;
    }

    /* =========================
   🔗 LINKS
========================= */
    .footer-links {
        display: flex;
        gap: 16px;
    }

    /* =========================
   🖼 ICONES
========================= */
    .footer-links img {
        width: 42px;
        height: 42px;

        border-radius: 50%;
        padding: 6px;

        background: #151515;
        border: 1px solid transparent;

        transition: 0.3s ease;
    }

    /* =========================
   🎯 HOVER (EFEITO PREMIUM)
========================= */
    .footer-links img:hover {
        transform: translateY(-5px) scale(1.12);

        border: 1px solid #ffd000;

        box-shadow:
            0 0 12px rgba(255, 208, 0, 0.4),
            0 0 20px rgba(0, 255, 136, 0.2);
    }

    /* =========================
   📱 RESPONSIVO
========================= */
    @media (max-width: 600px) {
        .footer-text {
            font-size: 0.85rem;
            padding: 0 10px;
        }

        .footer-links img {
            width: 36px;
            height: 36px;
        }
    }
</style>