<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit;
}
require_once "../config.php";

$conn->query("CREATE TABLE IF NOT EXISTS secciones_periodico (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(180) NOT NULL,
    descripcion TEXT,
    contenido LONGTEXT,
    imagen VARCHAR(255),
    orden_visual INT DEFAULT 0,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$secciones = [];
$res = $conn->query("SELECT id, titulo, descripcion, contenido, imagen, orden_visual FROM secciones_periodico ORDER BY orden_visual ASC, creado_en DESC");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $secciones[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Secciones - ECO BELÉN</title>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
  <link href="style.css" rel="stylesheet">
  <link href="admin-style.css" rel="stylesheet">
</head>
<body>
<a class="corner-logo" href="dashboard.php" aria-label="Ir al panel"><img src="escudo.png" alt="Escudo"></a>
<header class="public-header">
  <div class="header-title-wrapper"><h1>🧩 Gestión de Secciones</h1></div>
  <a href="dashboard.php" class="btn-view" style="position:absolute; top:20px; right:180px;">Periódicos</a>
  <a href="logout.php" class="btn-view" style="position:absolute; top:20px; right:20px;">Cerrar sesión</a>
</header>

<main class="admin-layout" style="display:block; max-width:1100px; margin:20px auto;">
  <button class="btn-view" id="openAddSection" style="margin-bottom:15px;">➕ Nueva sección</button>
  <div class="listado">
    <?php if (count($secciones) > 0): ?>
      <?php foreach ($secciones as $s): ?>
        <div class="list-item" style="margin-bottom:12px;">
          <div>
            <strong><?= htmlspecialchars($s['titulo']) ?></strong>
            <div>Orden: <?= intval($s['orden_visual']) ?></div>
            <span><?= htmlspecialchars($s['descripcion']) ?></span>
          </div>
          <div class="action-buttons">
            <button class="btn-edit btn-open-edit" data-id="<?= intval($s['id']) ?>" data-titulo="<?= htmlspecialchars($s['titulo']) ?>" data-descripcion="<?= htmlspecialchars($s['descripcion']) ?>" data-contenido="<?= htmlspecialchars($s['contenido']) ?>" data-imagen="<?= htmlspecialchars($s['imagen']) ?>" data-orden="<?= intval($s['orden_visual']) ?>">✏️ Editar</button>
            <button class="btn-delete btn-open-delete" data-id="<?= intval($s['id']) ?>">🗑️ Eliminar</button>
          </div>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <p>No hay secciones creadas todavía.</p>
    <?php endif; ?>
  </div>
</main>

<div id="modalAdd" class="modal"><div class="modal-content">
  <span class="close" data-close="modalAdd">&times;</span>
  <h3>Nueva sección</h3>
  <form id="formAdd">
    <input type="hidden" name="imagen" id="add_imagen">
    <label>Título</label><input type="text" name="titulo" required>
    <label>Descripción corta</label><textarea name="descripcion"></textarea>
    <label>Contenido</label><textarea name="contenido"></textarea>
    <label>Orden visual</label><input type="number" name="orden_visual" value="0">
    <label>Imagen de la sección</label><input type="file" id="add_file" accept="image/jpeg,image/png,image/webp">
    <button type="submit" class="btn-view">Guardar sección</button>
  </form>
</div></div>

<div id="modalEdit" class="modal"><div class="modal-content">
  <span class="close" data-close="modalEdit">&times;</span>
  <h3>Editar sección</h3>
  <form id="formEdit">
    <input type="hidden" name="id" id="edit_id">
    <input type="hidden" name="imagen" id="edit_imagen">
    <label>Título</label><input type="text" name="titulo" id="edit_titulo" required>
    <label>Descripción corta</label><textarea name="descripcion" id="edit_descripcion"></textarea>
    <label>Contenido</label><textarea name="contenido" id="edit_contenido"></textarea>
    <label>Orden visual</label><input type="number" name="orden_visual" id="edit_orden" value="0">
    <label>Reemplazar imagen</label><input type="file" id="edit_file" accept="image/jpeg,image/png,image/webp">
    <button type="submit" class="btn-view">Actualizar sección</button>
  </form>
</div></div>

<div id="modalDelete" class="modal"><div class="modal-content">
  <span class="close" data-close="modalDelete">&times;</span>
  <h3>Confirmar eliminación</h3>
  <form id="formDelete">
    <input type="hidden" name="id" id="delete_id">
    <button class="btn-view" style="background:#e74c3c;" type="submit">Eliminar</button>
  </form>
</div></div>

<script>
const open = (id) => document.getElementById(id).style.display = 'block';
const close = (id) => document.getElementById(id).style.display = 'none';
document.getElementById('openAddSection').addEventListener('click', ()=> open('modalAdd'));
document.querySelectorAll('[data-close]').forEach(btn=>btn.addEventListener('click',()=>close(btn.dataset.close)));

async function uploadImage(input, hiddenId){
  if (!input.files[0]) return;
  const fd = new FormData();
  fd.append('action','upload_image');
  fd.append('imagen', input.files[0]);
  const res = await fetch('manage_secciones.php',{method:'POST', body:fd});
  const out = await res.json();
  if(out.status==='ok') document.getElementById(hiddenId).value = out.filename;
  else alert(out.message || 'No se pudo subir imagen');
}
document.getElementById('add_file').addEventListener('change',()=>uploadImage(document.getElementById('add_file'),'add_imagen'));
document.getElementById('edit_file').addEventListener('change',()=>uploadImage(document.getElementById('edit_file'),'edit_imagen'));

document.querySelectorAll('.btn-open-edit').forEach(btn=>btn.addEventListener('click',()=>{
  edit_id.value = btn.dataset.id;
  edit_titulo.value = btn.dataset.titulo;
  edit_descripcion.value = btn.dataset.descripcion;
  edit_contenido.value = btn.dataset.contenido;
  edit_imagen.value = btn.dataset.imagen;
  edit_orden.value = btn.dataset.orden;
  open('modalEdit');
}));

document.querySelectorAll('.btn-open-delete').forEach(btn=>btn.addEventListener('click',()=>{
  delete_id.value = btn.dataset.id;
  open('modalDelete');
}));

async function sendForm(form, action, modal){
  const fd = new FormData(form);
  fd.append('action', action);
  const res = await fetch('manage_secciones.php',{method:'POST', body:fd});
  const out = await res.json();
  alert(out.message || 'Acción ejecutada');
  if(out.status==='ok') {
    close(modal);
    setTimeout(()=>location.reload(),250);
  }
}
formAdd.addEventListener('submit',(e)=>{e.preventDefault(); sendForm(formAdd,'add','modalAdd');});
formEdit.addEventListener('submit',(e)=>{e.preventDefault(); sendForm(formEdit,'edit','modalEdit');});
formDelete.addEventListener('submit',(e)=>{e.preventDefault(); sendForm(formDelete,'delete','modalDelete');});
</script>
</body>
</html>
