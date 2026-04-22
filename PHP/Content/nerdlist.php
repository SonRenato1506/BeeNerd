<?php
include_once('../Partial/config.php');
include_once("../Partial/header.php");


if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "NerdList não encontrada.";
    exit;
}

$nerdlist_id = (int) $_GET['id'];

$sqlList = "SELECT * FROM nerdlist WHERE id = $nerdlist_id LIMIT 1";
$resultList = $conexao->query($sqlList);

if (!$resultList || $resultList->num_rows === 0) {
    echo "NerdList não encontrada.";
    exit;
}

$nerdlist = $resultList->fetch_assoc();

$sqlTiers = "
    SELECT * FROM nerdlist_tiers
    WHERE nerdlist_id = $nerdlist_id
    ORDER BY id
";
$tiers = $conexao->query($sqlTiers);

$sqlItens = "
    SELECT * FROM nerdlist_itens
    WHERE nerdlist_id = $nerdlist_id
";
$itens = $conexao->query($sqlItens);
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($nerdlist['titulo']) ?> - DnNerds</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="../../Styles/NerdList.css?v=3">


</head>

<body>
    <main class="tierlist-container">

        <h1><?= htmlspecialchars($nerdlist['titulo']) ?></h1>
        <p><?= htmlspecialchars($nerdlist['descricao']) ?></p>

        <!-- TIERS -->
        <?php while ($tier = $tiers->fetch_assoc()): ?>
            <div class="tier" data-tier-id="<?= $tier['id'] ?>">

                <div class="tier-title" style="background: <?= htmlspecialchars($tier['cor']) ?>;" contenteditable="true"
                    onblur="salvarNomeTier(this)">
                    <?= htmlspecialchars($tier['nome']) ?>
                </div>

                <div class="tier-drop" data-tier-id="<?= $tier['id'] ?>" ondrop="drop(event)" ondragover="allowDrop(event)">
                </div>

                <div class="settings-panel">
                    <div class="settings" onclick="openSettings(this)">
                        <img src="../../Imagens/Settings.jpeg" alt="Settings">
                    </div>

                    <div class="move-buttons">
                        <div class="up" onclick="moveTierUp(this)">
                            <img src="../../Imagens/move-up.jpeg" alt="Mover para cima">
                        </div>
                        <div class="down" onclick="moveTierDown(this)">
                            <img src="../../Imagens/move-down.jpeg" alt="Mover para baixo">
                        </div>
                    </div>
                </div>

            </div>
        <?php endwhile; ?>

        <!-- ITENS -->
        <h2 style="margin-top:30px;">Arraste os itens</h2>

        <div class="itens-pool" ondrop="drop(event)" ondragover="allowDrop(event)">

            <?php while ($item = $itens->fetch_assoc()): ?>

                <div class="item" draggable="true" id="item<?= $item['id'] ?>" ondragstart="drag(event)">

                    <img src="<?= htmlspecialchars(
                        filter_var($item['imagem'] ?: 'default.jpg', FILTER_VALIDATE_URL)
                        ? $item['imagem']
                        : '../../Imagens/' . ($item['imagem'] ?: 'default.jpg')
                    ) ?>" alt="">
                </div>

            <?php endwhile; ?>
        </div>

        <!-- ADD ITEM AREA -->
        <div class="add-item-area" ondragover="allowDrop(event)" ondrop="dropAddItem(event)">

            <span>Arraste a imagem aqui</span>
            <input type="file" id="uploadImagem" accept="image/*" hidden>
        </div>

    </main>

    <!-- SETTINGS MODAL (ÚNICO) -->
    <div id="settings-overlay" onclick="closeSettings(event)">
        <div id="settings-modal" onclick="event.stopPropagation()">

            <h3>Tier Settings</h3>

            <button onclick="deleteRow()">Delete row</button>
            <button onclick="clearRowImages()">Clear row images</button>
            <button onclick="addRowAbove()">Add row above</button>
            <button onclick="addRowBelow()">Add row below</button>

            <label>
                Label color
                <input type="color" onchange="changeTierColor(this)">
            </label>

            <button class="close-btn" onclick="closeSettings()">Close</button>
        </div>
    </div>

    <a href="../Edit/editorNerdList.php?id=<?= $nerdlist['id'] ?>">
        <button id="editor">
            Edite essa NerdList
        </button>
    </a>

    <button onclick="baixarTierMaker()">
        Baixar estilo TierMaker
    </button>

    <script>
        let activeTier = null;
        let isDragging = false;

        /* =====================
           SETTINGS MODAL
        ===================== */
        function openSettings(btn) {
            activeTier = btn.closest('.tier');
            document.getElementById('settings-overlay').style.display = 'flex';
        }

        function closeSettings(e) {
            if (!e || e.target.id === 'settings-overlay') {
                document.getElementById('settings-overlay').style.display = 'none';
                activeTier = null;
            }
        }

        /* =====================
           TIER ACTIONS
        ===================== */
        function deleteRow() {
            if (!activeTier) return;
            activeTier.remove();
            closeSettings();
        }

        function clearRowImages() {
            if (!activeTier) return;
            activeTier.querySelector('.tier-drop').innerHTML = '';
        }

        function addRowAbove() {
            if (!activeTier) return;
            const clone = activeTier.cloneNode(true);
            resetTier(clone);
            activeTier.parentNode.insertBefore(clone, activeTier);
        }

        function addRowBelow() {
            if (!activeTier) return;
            const clone = activeTier.cloneNode(true);
            resetTier(clone);
            activeTier.parentNode.insertBefore(clone, activeTier.nextSibling);
        }

        function changeTierColor(input) {
            if (!activeTier) return;
            activeTier.querySelector('.tier-title').style.background = input.value;
        }

        /* =====================
           MOVE TIERS
        ===================== */
        function moveTierUp(btn) {
            const tier = btn.closest('.tier');
            const prev = tier?.previousElementSibling;
            if (prev && prev.classList.contains('tier')) {
                tier.parentNode.insertBefore(tier, prev);
            }
        }

        function moveTierDown(btn) {
            const tier = btn.closest('.tier');
            const next = tier?.nextElementSibling;
            if (next && next.classList.contains('tier')) {
                tier.parentNode.insertBefore(next, tier);
            }
        }

        /* =====================
           HELPERS
        ===================== */
        function resetTier(tier) {
            tier.querySelector('.tier-title').textContent = 'New Tier';
            tier.querySelector('.tier-title').style.background = '#666';
            tier.querySelector('.tier-drop').innerHTML = '';
        }

        /* =====================
           DRAG & DROP
        ===================== */
        function allowDrop(ev) {
            ev.preventDefault();
        }

        function drag(ev) {
            isDragging = true;
            ev.dataTransfer.setData("text/plain", ev.target.id);
        }

        document.addEventListener("dragend", () => {
            isDragging = false;
        });


        function drop(ev) {
            ev.preventDefault();
            const id = ev.dataTransfer.getData("text/plain");
            const item = document.getElementById(id);
            if (!item) return;

            let destino = ev.target;
            while (
                destino &&
                !destino.classList.contains('tier-drop') &&
                !destino.classList.contains('itens-pool')
            ) {
                destino = destino.parentElement;
            }

            if (destino) destino.appendChild(item);
        }

        document.addEventListener("dragover", (e) => {
            if (!isDragging) return;

            const margem = 120; // distância da borda
            const velocidade = 15; // velocidade do scroll

            const y = e.clientY;
            const alturaTela = window.innerHeight;

            // Scroll pra cima
            if (y < margem) {
                window.scrollBy(0, -velocidade);
            }

            // Scroll pra baixo
            if (y > alturaTela - margem) {
                window.scrollBy(0, velocidade);
            }
        });

        document.addEventListener("wheel", (e) => {
            if (!isDragging) return;

            window.scrollBy(0, e.deltaY);
        });


        /* =====================
           ADD ITEM DROPZONE
        ===================== */
        const dropzone = document.querySelector(".add-item-area");

        if (dropzone) {
            dropzone.addEventListener("dragover", ev => {
                ev.preventDefault();
                dropzone.classList.add("dragover");
            });

            dropzone.addEventListener("dragleave", () => {
                dropzone.classList.remove("dragover");
            });
        }

        function dropAddItem(ev) {
            ev.preventDefault();
            dropzone?.classList.remove("dragover");

            const file = ev.dataTransfer.files[0];
            if (!file || !file.type.startsWith("image/")) return;

            const reader = new FileReader();

            reader.onload = e => {
                const pool = document.querySelector(".itens-pool");
                if (!pool) return;

                const div = document.createElement("div");
                div.className = "item";
                div.draggable = true;
                div.id = "item_" + crypto.randomUUID();
                div.addEventListener("dragstart", drag);

                const img = document.createElement("img");
                img.src = e.target.result;

                div.appendChild(img);
                pool.appendChild(div);
            };

            reader.readAsDataURL(file);
        }

        /* =====================
           UPLOAD VIA INPUT
        ===================== */
        function abrirUpload() {
            document.getElementById("uploadImagem")?.click();
        }

        const uploadInput = document.getElementById("uploadImagem");

        if (uploadInput) {
            uploadInput.addEventListener("change", function () {
                const file = this.files[0];
                if (!file || !file.type.startsWith("image/")) return;

                const reader = new FileReader();

                reader.onload = e => {
                    const pool = document.querySelector(".itens-pool");
                    const addItem = document.querySelector(".additem");
                    if (!pool) return;

                    const div = document.createElement("div");
                    div.className = "item";
                    div.draggable = true;
                    div.id = "item_" + crypto.randomUUID();
                    div.addEventListener("dragstart", drag);

                    const img = document.createElement("img");
                    img.src = e.target.result;

                    div.appendChild(img);
                    pool.insertBefore(div, addItem || null);
                };

                reader.readAsDataURL(file);
            });
        }

        /* =====================
           UX EXTRA
        ===================== */
        document.addEventListener("keydown", e => {
            if (e.key === "Escape") closeSettings();
        });

        function baixarImagem() {
            const area = document.querySelector(".tierlist-container");

            html2canvas(area, {
                backgroundColor: null, // mantém fundo transparente (ou remove se quiser branco)
                scale: 2 // melhora qualidade
            }).then(canvas => {
                const link = document.createElement("a");
                link.download = "tierlist.png";
                link.href = canvas.toDataURL("image/png");
                link.click();
            });
        }

        function baixarTierMaker() {
            const area = document.querySelector(".tierlist-container");

            // ativa modo exportação
            document.body.classList.add("exportando");

            window.scrollTo(0, 0);

            setTimeout(() => {
                html2canvas(area, {
                    backgroundColor: "#111",
                    scale: 3
                }).then(canvas => {
                    const link = document.createElement("a");
                    link.download = "tiermaker.png";
                    link.href = canvas.toDataURL("image/png");
                    link.click();

                    // remove modo exportação
                    document.body.classList.remove("exportando");
                });
            }, 300);
        }
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
</body>

</html>
<?php include_once("../Partial/footer.php"); ?>