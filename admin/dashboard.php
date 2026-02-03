<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit;
}
require_once "../config.php";

// Obtener periódicos para listar
$sql = "SELECT id, titulo, director, publicado_en, archivo_pdf, descripcion 
        FROM periodicos ORDER BY publicado_en DESC";
$result = $conn->query($sql);
$periodicos_array = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $periodicos_array[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Panel Administrativo - ECO BELÉN</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <!-- Estilos -->
  <link href="style.css" rel="stylesheet">
  <link href="admin-style.css" rel="stylesheet">
</head>
<body>
<a class="corner-logo" href="dashboard.php" aria-label="Ir al panel">
    <img src="escudo.png" alt="Escudo Institucional">
</a>
<header class="public-header">
    <div class="header-title-wrapper">
        <h1>🌐 ECO BELÉN - Panel Admin</h1>
    </div>
    <a href="logout.php" class="btn-view" style="position:absolute; top:20px; right:20px;">Cerrar sesión</a>
</header>

<main class="admin-layout" id="main-content">

    <!-- Panel lateral: Últimas Ediciones -->
    <aside class="side-panel periodicos-panel" id="periodicosPanel">
        <button id="addPDFBtn" class="btn-view" style="width:100%; margin-bottom:15px;">➕ Agregar periódico</button>
        <h3>📰 Últimas Ediciones</h3>
        <div class="listado" id="periodicosList">
            <?php
            if (count($periodicos_array) > 0) {
                $i = 1;
                foreach ($periodicos_array as $row) {
                    echo "<div class='list-item'>
                            <div>
                                <strong>{$i}. {$row['titulo']}</strong><br>
                                <span>📅 {$row['publicado_en']} | Dir: {$row['director']}</span>
                            </div>
                            <div class='action-buttons'>
                                <button class='btn-view ver-btn' 
                                    data-pdf='../uploads/{$row['archivo_pdf']}' 
                                    data-titulo='{$row['titulo']}'>👁 Ver</button>
                                <button class='btn-edit' 
                                    data-id='{$row['id']}' 
                                    data-titulo='{$row['titulo']}' 
                                    data-director='{$row['director']}'
                                    data-fecha='{$row['publicado_en']}'
                                    data-descripcion='{$row['descripcion']}'>✏️ Editar</button>
                                <button class='btn-delete' data-id='{$row['id']}'>🗑️ Eliminar</button>
                            </div>
                          </div>";
                    $i++;
                }
            } else {
                echo "<p>No hay periódicos subidos aún.</p>";
            }
            ?>
        </div>
    </aside>

    <!-- Panel central -->
    <section class="main-periodico-display">
        <h2>📌 Última acción</h2>
        <div id="mainCard" class="card" style="text-align:center;">
            <p>Seleccione un periódico para verlo o agregue uno nuevo.</p>
        </div>
    </section>

</main>

<!-- Modal para agregar -->
<div id="fileDataModal" class="modal">
    <div class="modal-content">
        <span class="close" id="closeModalAdd">&times;</span>
        <h3>📌 Nuevo periódico</h3>
        <form id="fileDataForm">
            <input type="hidden" name="archivo_pdf" id="archivo_pdf">
            <label>Título</label>
            <input type="text" name="titulo" required>
            <label>Director</label>
            <input type="text" name="director" required>
            <label>Participantes</label>
            <textarea name="participantes"></textarea>
            <label>Fecha de publicación</label>
            <input type="date" name="fecha" required>
            <label>Descripción</label>
            <textarea name="descripcion"></textarea>
            <button type="submit" class="btn-view">✅ Subir y Guardar</button>
        </form>
    </div>
</div>

<!-- Modal para editar -->
<div id="editDataModal" class="modal">
    <div class="modal-content">
        <span class="close" id="closeModalEdit">&times;</span>
        <h3>✏️ Editar periódico</h3>
        <form id="editDataForm">
            <input type="hidden" name="id" id="edit_id">
            <label>Título</label>
            <input type="text" name="titulo" id="edit_titulo" required>
            <label>Director</label>
            <input type="text" name="director" id="edit_director" required>
            <label>Fecha de publicación</label>
            <input type="date" name="fecha" id="edit_fecha" required>
            <label>Descripción</label>
            <textarea name="descripcion" id="edit_descripcion"></textarea>
            <button type="submit" class="btn-view">💾 Guardar cambios</button>
        </form>
    </div>
</div>

<!-- Modal para confirmar eliminación -->
<div id="deleteConfirmModal" class="modal">
    <div class="modal-content">
        <span class="close" id="closeModalDelete">&times;</span>
        <h3>⚠️ Confirmar eliminación</h3>
        <p>¿Seguro que deseas eliminar este periódico? Esta acción no se puede deshacer.</p>
        <form id="deleteForm">
            <input type="hidden" name="id" id="delete_id">
            <button type="submit" class="btn-view" style="background:#e74c3c;">🗑️ Eliminar</button>
        </form>
    </div>
</div>

<!-- Modal para ver PDF (embed + fallback) -->
<div id="pdfModal" class="pdf-overlay">
  <div class="pdf-window">
    <div class="pdf-header">
      <h3 id="pdfTitle">📖 Visor de Periódico</h3>
      <div class="pdf-controls">
        <a id="downloadPdf" href="#" download class="btn-view">⬇ Descargar</a>
        <span id="closePdfModal" class="close-pdf">✖</span>
      </div>
    </div>
    <div class="pdf-body">
      <!-- usamos embed (mejor para PDF) -->
      <embed id="pdfEmbed" src="" type="application/pdf" width="100%" height="100%">
      <!-- fallback texto -->
      <p id="pdfFallback" style="display:none; text-align:center; padding:20px;">
        No se pudo mostrar el PDF embebido. <a id="openPdfNewTab" href="#" target="_blank">Abrir en nueva pestaña</a>
      </p>
    </div>
  </div>
</div>



<script src="dashboard.js"></script>
<script>
// Cerrar modales
document.getElementById("closeModalAdd").onclick = () => { document.getElementById("fileDataModal").style.display = "none"; };
document.getElementById("closeModalEdit").onclick = () => { document.getElementById("editDataModal").style.display = "none"; };
document.getElementById("closeModalDelete").onclick = () => { document.getElementById("deleteConfirmModal").style.display = "none"; };
document.getElementById("closePdfModal").onclick = () => { document.getElementById("pdfModal").style.display = "none"; };

// Abrir visor PDF
document.querySelectorAll(".ver-btn").forEach(btn => {
    btn.addEventListener("click", function() {
        const pdfUrl = this.dataset.pdf;
        const titulo = this.dataset.titulo;
        document.getElementById("pdfViewer").src = pdfUrl;
        document.getElementById("pdfTitle").innerText = "📖 " + titulo;
        document.getElementById("downloadPdf").href = pdfUrl;
        document.getElementById("pdfModal").style.display = "block";
    });
});
</script>
</body>
</html>
