<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Encuestas - Admin</title>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
  <link href="style.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<header class="public-header">
  <div class="header-title-wrapper"><h1>📊 Gestión de Encuestas</h1></div>
  <nav class="header-actions">
    <a href="dashboard.php" class="btn-view">Periódicos</a>
    <a href="secciones.php" class="btn-view">Secciones</a>
    <a href="contactos.php" class="btn-view">Mensajes</a>
    <a href="logout.php" class="btn-view">Cerrar sesión</a>
  </nav>
</header>

<main class="admin-layout">
  <aside class="side-panel">
    <h3>Nueva encuesta</h3>
    <form id="surveyFormCreate">
      <label>Título</label>
      <input name="titulo" required>
      <label>Pregunta</label>
      <textarea name="pregunta" required></textarea>
      <label>Ubicación</label>
      <select name="ubicacion" required>
        <option value="on_entry">Al entrar a la página</option>
        <option value="on_header_nav">Al moverse por menú del encabezado</option>
        <option value="on_virtual_read_end">Al terminar lectura virtual</option>
        <option value="on_download">Al descargar periódico</option>
        <option value="on_sections_menu">Al ir al menú de secciones</option>
      </select>
      <label>Posibles respuestas (1 por línea)</label>
      <textarea name="opciones" placeholder="Sí\nNo\nTal vez" required></textarea>
      <button class="btn-view" type="submit">Guardar encuesta</button>
    </form>
    <hr>
    <h3>Encuestas creadas</h3>
    <div id="surveyList" class="mensajes-list"></div>
  </aside>

  <section class="main-periodico-display">
    <h2 id="panelTitle">Selecciona una encuesta</h2>
    <div style="display:grid; grid-template-columns: 1fr 270px; gap:16px;">
      <div>
        <canvas id="pieChart" height="180"></canvas>
        <canvas id="barChart" height="140" style="margin-top:16px"></canvas>
        <div id="statsTable" class="card" style="margin-top:16px"></div>
      </div>
      <div class="side-panel" style="position:static; top:auto; max-height:none;">
        <h4>Acciones</h4>
        <button class="btn-view" id="editBtn" type="button">Editar encuesta</button>
        <button class="btn-view" id="finishBtn" type="button">Terminar encuesta</button>
        <button class="btn-view" id="saveBtn" type="button">Guardar datos</button>
        <button class="btn-view" id="deleteBtn" type="button" style="background:#b41627;">Eliminar encuesta</button>
        <a class="btn-view" id="downloadBtn" href="#">Descargar datos en PDF</a>
      </div>
    </div>
  </section>
</main>

<script>
let selectedId = null;
let pieChart, barChart;
const list = document.getElementById('surveyList');

async function loadList(){
  const res = await fetch('encuestas_api.php?action=list');
  const json = await res.json();
  list.innerHTML = '';
  json.items.forEach(it => {
    const div = document.createElement('div');
    div.className = 'list-item';
    div.innerHTML = `<strong>${it.titulo}</strong><span>${it.ubicacion} · ${it.activa == 1 ? 'Activa':'Finalizada'}</span>`;
    div.onclick = ()=> loadDetail(it.id);
    list.appendChild(div);
  });
}

async function loadDetail(id){
  selectedId = id;
  const res = await fetch('encuestas_api.php?action=detail&id=' + id);
  const json = await res.json();
  document.getElementById('panelTitle').textContent = json.encuesta.titulo;
  document.getElementById('downloadBtn').href = 'encuestas_api.php?action=download_pdf&id=' + id;

  const labels = json.stats.map(x=>x.texto);
  const values = json.stats.map(x=>Number(x.total));

  pieChart?.destroy();
  barChart?.destroy();
  pieChart = new Chart(document.getElementById('pieChart'), {type:'pie', data:{labels, datasets:[{data:values, backgroundColor:['#3366cc','#dc3912','#ff9900','#109618','#990099','#0099c6']}]}});
  barChart = new Chart(document.getElementById('barChart'), {type:'bar', data:{labels, datasets:[{label:'Respuestas', data:values, backgroundColor:'#3366cc'}]}});

  let total = values.reduce((a,b)=>a+b,0);
  document.getElementById('statsTable').innerHTML = '<h3>Estadísticas</h3>' + json.stats.map(r=>{
    const p = total ? ((r.total*100)/total).toFixed(1) : '0.0';
    return `<p><strong>${r.texto}</strong>: ${r.total} respuestas (${p}%)</p>`;
  }).join('');
}

document.getElementById('surveyFormCreate').addEventListener('submit', async (e)=>{
  e.preventDefault();
  const fd = new FormData(e.target);
  fd.append('action','create');
  const res = await fetch('encuestas_api.php', {method:'POST', body:fd});
  const json = await res.json();
  if(json.status==='ok'){ e.target.reset(); loadList(); }
  else alert(json.message);
});

document.getElementById('finishBtn').onclick = async ()=>{
  if(!selectedId) return;
  const fd = new FormData(); fd.append('action','finish'); fd.append('id',selectedId);
  await fetch('encuestas_api.php',{method:'POST',body:fd});
  loadList();
};
document.getElementById('deleteBtn').onclick = async ()=>{
  if(!selectedId || !confirm('¿Eliminar encuesta?')) return;
  const fd = new FormData(); fd.append('action','delete'); fd.append('id',selectedId);
  await fetch('encuestas_api.php',{method:'POST',body:fd});
  selectedId=null; loadList();
};
document.getElementById('saveBtn').onclick = ()=> alert('Los datos se guardan automáticamente en base de datos.');
document.getElementById('editBtn').onclick = async ()=>{
  if(!selectedId) return;
  const res = await fetch('encuestas_api.php?action=detail&id=' + selectedId);
  const json = await res.json();
  const titulo = prompt('Editar título:', json.encuesta.titulo); if(!titulo) return;
  const pregunta = prompt('Editar pregunta:', json.encuesta.pregunta); if(!pregunta) return;
  const ubicacion = prompt('Ubicación (on_entry,on_header_nav,on_virtual_read_end,on_download,on_sections_menu):', json.encuesta.ubicacion); if(!ubicacion) return;
  const opciones = prompt('Opciones (separa con |):', json.opciones.map(o=>o.texto).join('|')); if(!opciones) return;
  const fd = new FormData();
  fd.append('action','save'); fd.append('id', selectedId); fd.append('titulo',titulo); fd.append('pregunta',pregunta); fd.append('ubicacion',ubicacion); fd.append('opciones',opciones.split('|').join('\n'));
  const saveRes = await fetch('encuestas_api.php',{method:'POST',body:fd});
  const saveJson = await saveRes.json();
  if(saveJson.status==='ok'){ loadDetail(selectedId); loadList(); }
};

loadList();
</script>
</body>
</html>
