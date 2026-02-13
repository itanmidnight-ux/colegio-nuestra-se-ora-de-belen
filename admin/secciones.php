<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit;
}
require_once "../config.php";

function ensureColumnExists(mysqli $conn, string $table, string $column, string $definition): void
{
    $safeTable = $conn->real_escape_string($table);
    $safeColumn = $conn->real_escape_string($column);
    $exists = $conn->query("SHOW COLUMNS FROM {$safeTable} LIKE '{$safeColumn}'");
    if ($exists && $exists->num_rows === 0) {
        $conn->query("ALTER TABLE {$safeTable} ADD COLUMN {$column} {$definition}");
    }
}

$conn->query("CREATE TABLE IF NOT EXISTS secciones_periodico (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(180) NOT NULL,
    descripcion TEXT,
    contenido LONGTEXT,
    imagen VARCHAR(255),
    bloques_extra LONGTEXT,
    orden_visual INT DEFAULT 0,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

ensureColumnExists($conn, 'secciones_periodico', 'bloques_extra', 'LONGTEXT AFTER imagen');

$secciones = [];
$res = $conn->query("SELECT id, titulo, descripcion, contenido, imagen, bloques_extra, orden_visual FROM secciones_periodico ORDER BY orden_visual ASC, creado_en DESC");
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
  <nav class="header-actions" aria-label="Navegación del panel admin">
    <a href="dashboard.php" class="btn-view">Periódicos</a>
    <a href="contactos.php" class="btn-view">Mensajes</a>
    <a href="encuestas.php" class="btn-view">Encuestas</a>
    <a href="logout.php" class="btn-view">Cerrar sesión</a>
  </nav>
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
            <button class="btn-edit btn-open-edit"
                    data-id="<?= intval($s['id']) ?>"
                    data-titulo="<?= htmlspecialchars($s['titulo']) ?>"
                    data-descripcion="<?= htmlspecialchars($s['descripcion']) ?>"
                    data-contenido="<?= htmlspecialchars($s['contenido']) ?>"
                    data-imagen="<?= htmlspecialchars($s['imagen']) ?>"
                    data-bloques='<?= htmlspecialchars($s['bloques_extra'] ?: '[]', ENT_QUOTES, 'UTF-8') ?>'
                    data-orden="<?= intval($s['orden_visual']) ?>">✏️ Editar</button>
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
    <input type="hidden" name="bloques_json" id="add_bloques_json">
    <label>Título</label><input type="text" name="titulo" required>
    <label>Descripción corta</label><textarea name="descripcion"></textarea>
    <label>Contenido principal</label><textarea name="contenido"></textarea>
    <label>Orden visual</label><input type="number" name="orden_visual" value="0">
    <label>Imagen de portada de la sección (subir archivo)</label><input type="file" id="add_file" accept="image/jpeg,image/png,image/webp">
    <label>... o pegar ubicación (URL o ruta)</label><input type="text" id="add_imagen_url" placeholder="https://... o ../uploads/mi-imagen.jpg">
    <small id="add_image_status"></small>

    <div class="extras-panel">
      <h4>Contenido adicional</h4>
      <p>Agrega más información en el orden que deseas mostrarla: texto, imágenes o videos.</p>
      <div id="addBlocks" class="extra-blocks"></div>
      <button type="button" class="btn-view" id="addBlockBtn" style="margin-top:10px;">➕ Agregar bloque extra</button>
    </div>

    <button type="submit" class="btn-view">Guardar sección</button>
  </form>
</div></div>

<div id="modalEdit" class="modal"><div class="modal-content">
  <span class="close" data-close="modalEdit">&times;</span>
  <h3>Editar sección</h3>
  <form id="formEdit">
    <input type="hidden" name="id" id="edit_id">
    <input type="hidden" name="imagen" id="edit_imagen">
    <input type="hidden" name="bloques_json" id="edit_bloques_json">
    <label>Título</label><input type="text" name="titulo" id="edit_titulo" required>
    <label>Descripción corta</label><textarea name="descripcion" id="edit_descripcion"></textarea>
    <label>Contenido principal</label><textarea name="contenido" id="edit_contenido"></textarea>
    <label>Orden visual</label><input type="number" name="orden_visual" id="edit_orden" value="0">
    <label>Reemplazar imagen de portada (subir archivo)</label><input type="file" id="edit_file" accept="image/jpeg,image/png,image/webp">
    <label>... o actualizar ubicación (URL o ruta)</label><input type="text" id="edit_imagen_url" placeholder="https://... o ../uploads/mi-imagen.jpg">
    <small id="edit_image_status"></small>

    <div class="extras-panel">
      <h4>Contenido adicional</h4>
      <p>Estos bloques se mostrarán debajo del contenido principal.</p>
      <div id="editBlocks" class="extra-blocks"></div>
      <button type="button" class="btn-view" id="editBlockBtn" style="margin-top:10px;">➕ Agregar bloque extra</button>
    </div>

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
  if(out.status==='ok') {
    document.getElementById(hiddenId).value = out.filename;
    const statusId = hiddenId === 'add_imagen' ? 'add_image_status' : 'edit_image_status';
    const statusEl = document.getElementById(statusId);
    if (statusEl) statusEl.textContent = `Imagen subida: ${out.filename}`;
  } else {
    alert(out.message || 'No se pudo subir imagen');
  }
}

document.getElementById('add_file').addEventListener('change',()=>uploadImage(document.getElementById('add_file'),'add_imagen'));
document.getElementById('edit_file').addEventListener('change',()=>uploadImage(document.getElementById('edit_file'),'edit_imagen'));

document.getElementById('add_imagen_url').addEventListener('input',(e)=>{
  document.getElementById('add_imagen').value = e.target.value.trim();
});
document.getElementById('edit_imagen_url').addEventListener('input',(e)=>{
  document.getElementById('edit_imagen').value = e.target.value.trim();
});

function crearBloque({ tipo = 'texto', valor = '' } = {}) {
  const wrapper = document.createElement('div');
  wrapper.className = 'extra-block';

  wrapper.innerHTML = `
    <div class="extra-block-head">
      <strong>Bloque adicional</strong>
      <button type="button" class="btn-delete remove-block">Eliminar</button>
    </div>
    <label>Tipo</label>
    <select class="block-tipo">
      <option value="texto">Texto</option>
      <option value="imagen">Imagen</option>
      <option value="video">Video</option>
    </select>
    <label>Contenido / URL</label>
    <textarea class="block-valor" placeholder="Texto adicional o URL de imagen/video"></textarea>
    <label class="label-upload" style="display:none;">Subir imagen para este bloque</label>
    <input class="block-file" type="file" accept="image/jpeg,image/png,image/webp" style="display:none;">
    <small class="block-help"></small>
  `;

  const tipoSelect = wrapper.querySelector('.block-tipo');
  const valorInput = wrapper.querySelector('.block-valor');
  const fileInput = wrapper.querySelector('.block-file');
  const labelUpload = wrapper.querySelector('.label-upload');
  const help = wrapper.querySelector('.block-help');

  const refrescar = () => {
    const tipoActual = tipoSelect.value;
    if (tipoActual === 'texto') {
      labelUpload.style.display = 'none';
      fileInput.style.display = 'none';
      help.textContent = 'Escribe texto libre para ampliar la sección.';
    } else if (tipoActual === 'imagen') {
      labelUpload.style.display = 'block';
      fileInput.style.display = 'block';
      help.textContent = 'Puedes subir una imagen o pegar una URL.';
    } else {
      labelUpload.style.display = 'none';
      fileInput.style.display = 'none';
      help.textContent = 'Pega un enlace de YouTube/Vimeo (watch, share o embed) u otra plataforma compatible.';
    }
  };

  tipoSelect.value = tipo;
  valorInput.value = valor;
  refrescar();

  tipoSelect.addEventListener('change', refrescar);
  wrapper.querySelector('.remove-block').addEventListener('click', () => wrapper.remove());
  fileInput.addEventListener('change', async () => {
    if (!fileInput.files[0]) return;
    const fd = new FormData();
    fd.append('action','upload_image');
    fd.append('imagen', fileInput.files[0]);
    const res = await fetch('manage_secciones.php',{method:'POST', body:fd});
    const out = await res.json();
    if(out.status==='ok') valorInput.value = '../uploads/' + out.filename;
    else alert(out.message || 'No se pudo subir imagen');
  });

  return wrapper;
}

function getBloques(container) {
  return [...container.querySelectorAll('.extra-block')]
    .map((el) => ({
      tipo: el.querySelector('.block-tipo').value,
      valor: el.querySelector('.block-valor').value.trim()
    }))
    .filter((b) => b.valor !== '');
}

function setBloques(container, bloques = []) {
  container.innerHTML = '';
  if (!Array.isArray(bloques)) return;
  bloques.forEach((b) => container.appendChild(crearBloque(b)));
}

const addBlocks = document.getElementById('addBlocks');
const editBlocks = document.getElementById('editBlocks');
document.getElementById('addBlockBtn').addEventListener('click', () => addBlocks.appendChild(crearBloque()));
document.getElementById('editBlockBtn').addEventListener('click', () => editBlocks.appendChild(crearBloque()));

document.querySelectorAll('.btn-open-edit').forEach(btn=>btn.addEventListener('click',()=>{
  edit_id.value = btn.dataset.id;
  edit_titulo.value = btn.dataset.titulo;
  edit_descripcion.value = btn.dataset.descripcion;
  edit_contenido.value = btn.dataset.contenido;
  edit_imagen.value = btn.dataset.imagen;
  edit_imagen_url.value = btn.dataset.imagen;
  edit_orden.value = btn.dataset.orden;

  let bloques = [];
  try { bloques = JSON.parse(btn.dataset.bloques || '[]'); } catch(e) { bloques = []; }
  setBloques(editBlocks, bloques);

  open('modalEdit');
}));

document.querySelectorAll('.btn-open-delete').forEach(btn=>btn.addEventListener('click',()=>{
  delete_id.value = btn.dataset.id;
  open('modalDelete');
}));

async function sendForm(form, action, modal){
  if (action === 'add') {
    document.getElementById('add_bloques_json').value = JSON.stringify(getBloques(addBlocks));
  }
  if (action === 'edit') {
    document.getElementById('edit_bloques_json').value = JSON.stringify(getBloques(editBlocks));
  }

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
