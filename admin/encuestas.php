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
    <a href="visitas.php" class="btn-view">Visitas</a>
    <a href="logout.php" class="btn-view">Cerrar sesión</a>
  </nav>
</header>

<main class="admin-layout survey-admin-layout">
  <aside class="side-panel survey-side-panel">
    <div class="survey-side-head">
      <h3>Encuestas creadas</h3>
      <button class="btn-view" id="newSurveyBtn" type="button">+ Crear nueva encuesta</button>
    </div>
    <p class="survey-side-copy">Administra encuestas activas por ubicación, revisa resultados en tiempo real y descarga reportes.</p>
    <div id="surveyList" class="mensajes-list survey-list"></div>
  </aside>

  <section class="main-periodico-display survey-dashboard">
    <h2 id="panelTitle">Selecciona una encuesta</h2>
    <div class="survey-dashboard-grid">
      <div>
        <div class="survey-charts-row">
          <div class="survey-chart-card survey-chart-card-pie">
            <canvas id="pieChart"></canvas>
          </div>
          <div class="survey-chart-card">
            <canvas id="barChart"></canvas>
          </div>
        </div>
        <div id="statsTable" class="card survey-stats-card"></div>
      </div>
      <div class="side-panel survey-actions-panel">
        <h4>Acciones</h4>
        <button class="btn-view" id="editBtn" type="button">Editar encuesta</button>
        <button class="btn-view" id="finishBtn" type="button">Terminar encuesta</button>
        <button class="btn-view" id="saveBtn" type="button">Guardar datos</button>
        <button class="btn-view btn-danger" id="deleteBtn" type="button">Eliminar encuesta</button>
        <a class="btn-view" id="downloadBtn" href="#">Descargar datos en PDF</a>
      </div>
    </div>
  </section>
</main>

<div class="modal" id="surveyModal" aria-hidden="true">
  <div class="modal-content survey-modal-content">
    <div class="survey-modal-header">
      <h3 id="surveyModalTitle">Nueva encuesta</h3>
      <button type="button" class="survey-modal-close" id="closeSurveyModal" aria-label="Cerrar">×</button>
    </div>

    <form id="surveyFormCreate" class="survey-form">
      <label for="surveyTitulo">Título</label>
      <input id="surveyTitulo" name="titulo" required>

      <label for="surveyPregunta">Pregunta</label>
      <textarea id="surveyPregunta" name="pregunta" required></textarea>

      <label for="surveyUbicacion">Ubicación</label>
      <select id="surveyUbicacion" name="ubicacion" required>
        <option value="on_entry">Al entrar a la página</option>
        <option value="on_header_nav">Al moverse por menú del encabezado</option>
        <option value="on_virtual_read_end">Al terminar lectura virtual</option>
        <option value="on_download">Al descargar periódico</option>
        <option value="on_sections_menu">Al ir al menú de secciones</option>
      </select>

      <label for="surveyOpciones">Posibles respuestas (1 por línea)</label>
      <textarea id="surveyOpciones" name="opciones" placeholder="Sí&#10;No&#10;Tal vez" required></textarea>

      <div class="survey-form-actions">
        <button class="btn-view" type="submit" id="surveySubmitBtn">Guardar encuesta</button>
      </div>
    </form>
  </div>
</div>

<script>
let selectedId = null;
let pieChart, barChart;
let editingSurveyId = null;
const list = document.getElementById('surveyList');
const modal = document.getElementById('surveyModal');
const form = document.getElementById('surveyFormCreate');
const modalTitle = document.getElementById('surveyModalTitle');
const submitBtn = document.getElementById('surveySubmitBtn');

function openModal(editMode = false) {
  modal.style.display = 'block';
  modal.setAttribute('aria-hidden', 'false');
  modalTitle.textContent = editMode ? 'Editar encuesta' : 'Nueva encuesta';
  submitBtn.textContent = editMode ? 'Guardar cambios' : 'Guardar encuesta';
}

function closeModal() {
  modal.style.display = 'none';
  modal.setAttribute('aria-hidden', 'true');
  editingSurveyId = null;
  form.reset();
}

function locationLabel(code){
  const map = {
    on_entry: 'Entrada',
    on_header_nav: 'Menú',
    on_virtual_read_end: 'Lectura',
    on_download: 'Descarga',
    on_sections_menu: 'Secciones'
  };
  return map[code] || code;
}

async function loadList(){
  const res = await fetch('encuestas_api.php?action=list');
  const json = await res.json();
  list.innerHTML = '';
  if (!json.items || json.items.length === 0) {
    list.innerHTML = '<p class="survey-empty">No hay encuestas creadas todavía.</p>';
    return;
  }
  json.items.forEach(it => {
    const div = document.createElement('div');
    div.className = 'list-item survey-list-item';
    if (selectedId === Number(it.id)) {
      div.classList.add('is-selected');
    }
    div.innerHTML = `
      <strong>${it.titulo}</strong>
      <span>${locationLabel(it.ubicacion)} · ${it.activa == 1 ? 'Activa' : 'Finalizada'}</span>
    `;
    div.onclick = ()=> loadDetail(it.id);
    list.appendChild(div);
  });
}

async function loadDetail(id){
  selectedId = Number(id);
  const res = await fetch('encuestas_api.php?action=detail&id=' + id);
  const json = await res.json();
  if (json.status !== 'ok') {
    alert(json.message || 'No se pudo cargar la encuesta');
    return;
  }

  document.getElementById('panelTitle').textContent = `${json.encuesta.titulo} · ${locationLabel(json.encuesta.ubicacion)}`;
  document.getElementById('downloadBtn').href = 'encuestas_api.php?action=download_pdf&id=' + id;

  const labels = json.stats.map(x => x.texto);
  const values = json.stats.map(x => Number(x.total));

  pieChart?.destroy();
  barChart?.destroy();
  pieChart = new Chart(document.getElementById('pieChart'), {
    type:'pie',
    data:{labels, datasets:[{data:values, backgroundColor:['#3366cc','#dc3912','#ff9900','#109618','#990099','#0099c6']}]} ,
    options:{responsive:true, maintainAspectRatio:false, plugins:{legend:{position:'bottom'}}}
  });
  barChart = new Chart(document.getElementById('barChart'), {
    type:'bar',
    data:{labels, datasets:[{label:'Respuestas', data:values, backgroundColor:'#3366cc', borderRadius:8}]},
    options:{responsive:true, maintainAspectRatio:false, plugins:{legend:{display:false}}, scales:{y:{beginAtZero:true, ticks:{precision:0}}}}
  });

  const total = values.reduce((a,b)=>a+b,0);
  document.getElementById('statsTable').innerHTML = '<h3>Estadísticas</h3>' + json.stats.map(r=>{
    const p = total ? ((r.total*100)/total).toFixed(1) : '0.0';
    return `<p><strong>${r.texto}</strong>: ${r.total} respuestas (${p}%)</p>`;
  }).join('');

  loadList();
}

form.addEventListener('submit', async (e)=>{
  e.preventDefault();
  const fd = new FormData(form);
  fd.append('action', editingSurveyId ? 'save' : 'create');
  if (editingSurveyId) fd.append('id', editingSurveyId);

  const res = await fetch('encuestas_api.php', {method:'POST', body:fd});
  const json = await res.json();
  if(json.status === 'ok'){
    closeModal();
    loadList();
    if (editingSurveyId) {
      loadDetail(editingSurveyId);
    }
  } else {
    alert(json.message || 'No se pudo guardar la encuesta');
  }
});

document.getElementById('newSurveyBtn').onclick = ()=> {
  editingSurveyId = null;
  form.reset();
  openModal(false);
};
document.getElementById('closeSurveyModal').onclick = closeModal;
window.addEventListener('click', (e)=> { if (e.target === modal) closeModal(); });
window.addEventListener('keydown', (e)=> { if (e.key === 'Escape' && modal.style.display === 'block') closeModal(); });

document.getElementById('finishBtn').onclick = async ()=>{
  if(!selectedId) return alert('Primero selecciona una encuesta.');
  const fd = new FormData(); fd.append('action','finish'); fd.append('id',selectedId);
  await fetch('encuestas_api.php',{method:'POST',body:fd});
  loadList();
  loadDetail(selectedId);
};

document.getElementById('deleteBtn').onclick = async ()=>{
  if(!selectedId || !confirm('¿Eliminar encuesta?')) return;
  const fd = new FormData(); fd.append('action','delete'); fd.append('id',selectedId);
  await fetch('encuestas_api.php',{method:'POST',body:fd});
  selectedId = null;
  document.getElementById('panelTitle').textContent = 'Selecciona una encuesta';
  document.getElementById('statsTable').innerHTML = '';
  pieChart?.destroy();
  barChart?.destroy();
  loadList();
};

document.getElementById('saveBtn').onclick = ()=> alert('Los datos se guardan automáticamente en la base de datos.');

document.getElementById('editBtn').onclick = async ()=>{
  if(!selectedId) return alert('Primero selecciona una encuesta.');
  const res = await fetch('encuestas_api.php?action=detail&id=' + selectedId);
  const json = await res.json();
  if (json.status !== 'ok') return alert(json.message || 'No se pudo cargar');

  editingSurveyId = String(selectedId);
  form.titulo.value = json.encuesta.titulo;
  form.pregunta.value = json.encuesta.pregunta;
  form.ubicacion.value = json.encuesta.ubicacion;
  form.opciones.value = json.opciones.map(o => o.texto).join('\n');
  openModal(true);
};

loadList();
</script>
</body>
</html>
