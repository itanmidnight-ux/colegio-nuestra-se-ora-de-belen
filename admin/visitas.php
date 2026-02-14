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
  <title>Visitas por secciones - Admin</title>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
  <link href="style.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<header class="public-header">
  <div class="header-title-wrapper"><h1>📈 Analítica de visitas</h1></div>
  <nav class="header-actions">
    <a href="dashboard.php" class="btn-view">Periódicos</a>
    <a href="secciones.php" class="btn-view">Secciones</a>
    <a href="encuestas.php" class="btn-view">Encuestas</a>
    <a href="contactos.php" class="btn-view">Mensajes</a>
    <a href="logout.php" class="btn-view">Cerrar sesión</a>
  </nav>
</header>

<main class="admin-layout">
  <aside class="side-panel">
    <h3>Resumen general</h3>
    <div id="visitSummary" class="card">Cargando...</div>
  </aside>

  <section class="main-periodico-display">
    <h2>Carrusel de visitas diarias por sección</h2>
    <div class="visit-carousel-controls">
      <button type="button" class="btn-view" id="prevDayBtn">← Día anterior</button>
      <div id="currentDayLabel" class="visit-day-label">Sin datos</div>
      <button type="button" class="btn-view" id="nextDayBtn">Día siguiente →</button>
    </div>
    <div class="survey-chart-card" style="height:360px; max-width:none;">
      <canvas id="dailyVisitsChart"></canvas>
    </div>
    <div id="dayDetail" class="card survey-stats-card"></div>

    <div class="visit-carousel">
      <h2>Carrusel de visitas brutas por dispositivo</h2>
      <div class="visit-carousel-controls">
        <button type="button" class="btn-view" id="prevRawBtn">← Día anterior</button>
        <div id="currentRawLabel" class="visit-day-label">Sin datos</div>
        <button type="button" class="btn-view" id="nextRawBtn">Día siguiente →</button>
      </div>
      <div class="survey-chart-card" style="height:320px; max-width:none;">
        <canvas id="rawVisitsChart"></canvas>
      </div>
      <div id="rawDetail" class="card survey-stats-card"></div>
    </div>
  </section>
</main>

<script>
let days = [];
let dayIndex = 0;
let visitsChart;

let rawDays = [];
let rawIndex = 0;
let rawChart;

async function loadSummary() {
  const res = await fetch('visitas_api.php?action=summary');
  const json = await res.json();
  if (json.status !== 'ok') return;

  const s = json.summary;
  const sectionsHtml = (s.secciones || []).slice(0, 8).map(item => `<li><strong>${item.seccion}</strong>: ${item.total}</li>`).join('');
  document.getElementById('visitSummary').innerHTML = `
    <p><strong>Total de visitas:</strong> ${s.total_visitas}</p>
    <p><strong>Días con registro:</strong> ${s.total_dias}</p>
    <p><strong>Dispositivos registrados:</strong> ${s.total_dispositivos_registrados || 0}</p>
    <p><strong>Último día registrado:</strong> ${s.ultima_fecha || '—'}</p>
    <h4>Top de secciones</h4>
    <ul>${sectionsHtml || '<li>No hay datos todavía.</li>'}</ul>
  `;
}

async function loadCarousel() {
  const res = await fetch('visitas_api.php?action=daily_carousel');
  const json = await res.json();
  if (json.status !== 'ok') return;
  days = json.items || [];

  if (!days.length) {
    document.getElementById('currentDayLabel').textContent = 'Sin datos aún';
    document.getElementById('dayDetail').innerHTML = '<p>No hay registros diarios disponibles todavía.</p>';
    return;
  }

  dayIndex = days.length - 1;
  renderDay();
}

function renderDay() {
  const day = days[dayIndex];
  document.getElementById('currentDayLabel').textContent = `${day.fecha} · Total: ${day.total_visitas}`;

  const labels = day.secciones.map(s => s.seccion);
  const values = day.secciones.map(s => Number(s.visitas));

  visitsChart?.destroy();
  visitsChart = new Chart(document.getElementById('dailyVisitsChart'), {
    type: 'bar',
    data: {
      labels,
      datasets: [{
        label: 'Visitas por sección',
        data: values,
        backgroundColor: '#2f5fb8',
        borderRadius: 8,
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
    }
  });

  document.getElementById('dayDetail').innerHTML =
    '<h3>Detalle del día</h3>' +
    (day.secciones.length
      ? day.secciones.map(r => `<p><strong>${r.seccion}</strong>: ${r.visitas} visita(s)</p>`).join('')
      : '<p>No hubo visitas registradas para este día.</p>');

  document.getElementById('prevDayBtn').disabled = dayIndex === 0;
  document.getElementById('nextDayBtn').disabled = dayIndex === days.length - 1;
}

async function loadRawDevicesCarousel() {
  const res = await fetch('visitas_api.php?action=raw_devices_carousel');
  const json = await res.json();
  if (json.status !== 'ok') return;
  rawDays = json.items || [];

  if (!rawDays.length) {
    document.getElementById('currentRawLabel').textContent = 'Sin datos aún';
    document.getElementById('rawDetail').innerHTML = '<p>Aún no hay visitas brutas por dispositivos para mostrar.</p>';
    return;
  }

  rawIndex = rawDays.length - 1;
  renderRawDay();
}

function renderRawDay() {
  const day = rawDays[rawIndex];
  document.getElementById('currentRawLabel').textContent = `${day.fecha} · Dispositivos: ${day.dispositivos_brutos}`;

  rawChart?.destroy();
  rawChart = new Chart(document.getElementById('rawVisitsChart'), {
    type: 'bar',
    data: {
      labels: [day.fecha],
      datasets: [{
        label: 'Visitas brutas (dispositivos)',
        data: [Number(day.dispositivos_brutos)],
        backgroundColor: '#f2c600',
        borderRadius: 10,
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { display: true } },
      scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
    }
  });

  document.getElementById('rawDetail').innerHTML = `<h3>Detalle de visitas brutas</h3><p><strong>${day.fecha}</strong>: ${day.dispositivos_brutos} dispositivo(s) únicos detectados.</p>`;
  document.getElementById('prevRawBtn').disabled = rawIndex === 0;
  document.getElementById('nextRawBtn').disabled = rawIndex === rawDays.length - 1;
}

document.getElementById('prevDayBtn').addEventListener('click', () => {
  if (dayIndex > 0) {
    dayIndex -= 1;
    renderDay();
  }
});

document.getElementById('nextDayBtn').addEventListener('click', () => {
  if (dayIndex < days.length - 1) {
    dayIndex += 1;
    renderDay();
  }
});

document.getElementById('prevRawBtn').addEventListener('click', () => {
  if (rawIndex > 0) {
    rawIndex -= 1;
    renderRawDay();
  }
});

document.getElementById('nextRawBtn').addEventListener('click', () => {
  if (rawIndex < rawDays.length - 1) {
    rawIndex += 1;
    renderRawDay();
  }
});

loadSummary();
loadCarousel();
loadRawDevicesCarousel();
</script>
</body>
</html>
