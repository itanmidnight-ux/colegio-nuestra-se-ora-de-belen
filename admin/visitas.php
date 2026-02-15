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

<main class="admin-layout visitas-layout">
  <aside class="side-panel survey-side-panel">
    <div>
      <h3>Resumen general</h3>
      <p class="survey-side-copy">Vista rápida de comportamiento, secciones destacadas y actividad diaria del sitio público.</p>
    </div>
    <div id="visitSummary" class="card visitas-summary-card">Cargando...</div>
  </aside>

  <section class="main-periodico-display visitas-main-panel">
    <h2>Resumen de visitas</h2>

    <div id="kpiRow" class="visitas-kpi-row"></div>

    <div class="visitas-grid-compact">
      <article class="survey-chart-card visitas-chart-card visitas-chart-small">
        <h3>Sesiones por día (sitio público)</h3>
        <canvas id="trendVisitsChart"></canvas>
      </article>
      <article class="survey-chart-card visitas-chart-card visitas-chart-small">
        <h3>Canales por sección (acumulado)</h3>
        <canvas id="sectionsShareChart"></canvas>
      </article>
    </div>

    <article class="survey-chart-card visitas-chart-card visitas-chart-medium">
      <h3>Visitas brutas semanales del sitio público</h3>
      <p class="visitas-chart-copy">Incluye todos los días de la semana (lunes a domingo), mostrando fecha exacta y total de dispositivos únicos detectados.</p>
      <canvas id="rawVisitsChart"></canvas>
      <div id="rawDetail" class="card survey-stats-card visitas-detail-card"></div>
    </article>
  </section>
</main>

<script>
const WEEKDAY_SHORT = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
const WEEKDAY_LONG = ['domingo', 'lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado'];

let trendChart;
let sectionsChart;
let rawChart;

function formatFullDate(isoDate) {
  const [year, month, day] = isoDate.split('-').map(Number);
  const localDate = new Date(year, month - 1, day);
  const weekday = WEEKDAY_LONG[localDate.getDay()];
  return `${weekday}, ${String(day).padStart(2, '0')}/${String(month).padStart(2, '0')}/${year}`;
}

function formatShortDate(isoDate) {
  const [year, month, day] = isoDate.split('-').map(Number);
  const localDate = new Date(year, month - 1, day);
  return `${WEEKDAY_SHORT[localDate.getDay()]} ${String(day).padStart(2, '0')}/${String(month).padStart(2, '0')}`;
}

function getWeekRangeFromDate(dateIso) {
  const [year, month, day] = dateIso.split('-').map(Number);
  const date = new Date(year, month - 1, day);
  const dayOfWeek = date.getDay();
  const mondayOffset = dayOfWeek === 0 ? -6 : 1 - dayOfWeek;

  const monday = new Date(date);
  monday.setDate(date.getDate() + mondayOffset);

  const week = [];
  for (let i = 0; i < 7; i += 1) {
    const current = new Date(monday);
    current.setDate(monday.getDate() + i);
    const y = current.getFullYear();
    const m = String(current.getMonth() + 1).padStart(2, '0');
    const d = String(current.getDate()).padStart(2, '0');
    week.push(`${y}-${m}-${d}`);
  }

  return week;
}

function buildKpiCards(summary) {
  const avgPerDay = summary.total_dias ? (summary.total_visitas / summary.total_dias).toFixed(1) : '0';
  return [
    { label: 'Sesiones totales', value: Number(summary.total_visitas).toLocaleString('es-CO') },
    { label: 'Días con registro', value: Number(summary.total_dias).toLocaleString('es-CO') },
    { label: 'Promedio por día', value: Number(avgPerDay).toLocaleString('es-CO') },
    { label: 'Dispositivos únicos', value: Number(summary.total_dispositivos_registrados || 0).toLocaleString('es-CO') }
  ];
}

async function loadSummary() {
  const [summaryRes, dailyRes, rawRes] = await Promise.all([
    fetch('visitas_api.php?action=summary'),
    fetch('visitas_api.php?action=daily_carousel'),
    fetch('visitas_api.php?action=raw_devices_carousel'),
  ]);

  const [summaryJson, dailyJson, rawJson] = await Promise.all([
    summaryRes.json(),
    dailyRes.json(),
    rawRes.json(),
  ]);

  if (summaryJson.status !== 'ok' || dailyJson.status !== 'ok' || rawJson.status !== 'ok') {
    document.getElementById('visitSummary').innerHTML = '<p>No se pudieron cargar las estadísticas.</p>';
    return;
  }

  renderSummary(summaryJson.summary);
  renderTrend(dailyJson.items || []);
  renderSectionShare(summaryJson.summary.secciones || []);
  renderRawWeekly(rawJson.items || []);
}

function renderSummary(summary) {
  const sectionsHtml = (summary.secciones || []).slice(0, 6).map(item =>
    `<li><strong>${item.seccion}</strong><span>${Number(item.total).toLocaleString('es-CO')}</span></li>`).join('');

  document.getElementById('visitSummary').innerHTML = `
    <p><strong>Último registro:</strong> ${summary.ultima_fecha ? formatFullDate(summary.ultima_fecha) : '—'}</p>
    <h4>Top de secciones</h4>
    <ul class="visitas-ranking">${sectionsHtml || '<li>Sin datos por ahora.</li>'}</ul>
  `;

  const kpis = buildKpiCards(summary);
  document.getElementById('kpiRow').innerHTML = kpis.map(kpi => `
    <article class="visitas-kpi-card">
      <p>${kpi.label}</p>
      <strong>${kpi.value}</strong>
    </article>
  `).join('');
}

function renderTrend(days) {
  const labels = days.map(item => formatShortDate(item.fecha));
  const values = days.map(item => Number(item.total_visitas));

  trendChart?.destroy();
  trendChart = new Chart(document.getElementById('trendVisitsChart'), {
    type: 'line',
    data: {
      labels,
      datasets: [{
        label: 'Sesiones',
        data: values,
        borderColor: '#1d9bc7',
        backgroundColor: 'rgba(29, 155, 199, 0.20)',
        fill: true,
        tension: 0.35,
        pointRadius: 3,
        pointHoverRadius: 5,
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false },
      },
      scales: {
        y: { beginAtZero: true, ticks: { precision: 0 } },
        x: { ticks: { maxRotation: 0, autoSkip: true, maxTicksLimit: 8 } },
      }
    }
  });
}

function renderSectionShare(sections) {
  const labels = sections.slice(0, 6).map(s => s.seccion);
  const values = sections.slice(0, 6).map(s => Number(s.total));
  const palette = ['#1d9bc7', '#9ccc65', '#ffb74d', '#ef5350', '#8e8cd8', '#4db6ac'];

  sectionsChart?.destroy();
  sectionsChart = new Chart(document.getElementById('sectionsShareChart'), {
    type: 'doughnut',
    data: {
      labels,
      datasets: [{
        data: values,
        backgroundColor: palette,
        borderWidth: 1,
        borderColor: '#f8fbff',
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      cutout: '62%',
      plugins: {
        legend: {
          position: 'right',
          labels: { boxWidth: 12, usePointStyle: true },
        },
      }
    }
  });
}

function renderRawWeekly(rawDays) {
  const detail = document.getElementById('rawDetail');
  if (!rawDays.length) {
    detail.innerHTML = '<p>Aún no hay visitas brutas para mostrar.</p>';
    return;
  }

  const mapByDate = new Map(rawDays.map(item => [item.fecha, Number(item.dispositivos_brutos)]));
  const lastDate = rawDays[rawDays.length - 1].fecha;
  const fullWeek = getWeekRangeFromDate(lastDate);

  const labels = fullWeek.map(formatShortDate);
  const values = fullWeek.map(date => mapByDate.get(date) || 0);

  rawChart?.destroy();
  rawChart = new Chart(document.getElementById('rawVisitsChart'), {
    type: 'line',
    data: {
      labels,
      datasets: [{
        label: 'Visitas brutas por día',
        data: values,
        borderColor: '#8cc5e9',
        backgroundColor: 'rgba(140, 197, 233, 0.45)',
        fill: true,
        tension: 0.4,
        pointRadius: 4,
        pointBackgroundColor: '#4e9dd6',
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: true },
      },
      scales: {
        y: { beginAtZero: true, ticks: { precision: 0 } },
        x: { ticks: { maxRotation: 0, autoSkip: false } },
      }
    }
  });

  detail.innerHTML = '<h3>Detalle semanal exacto</h3>' + fullWeek.map((date, index) =>
    `<p><strong>${formatFullDate(date)}</strong>: ${values[index]} visita(s) bruta(s)</p>`).join('');
}

loadSummary();
</script>
</body>
</html>
