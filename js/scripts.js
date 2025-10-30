document.addEventListener("DOMContentLoaded", async function () {
  const resp = await fetch("api/dashboard_data.php");
  const data = await resp.json();

  // === KPIs ===
  document.getElementById("kpi-aprobadas").textContent = data.kpi.aprobadas + " %";
  document.getElementById("kpi-falla").textContent = data.kpi.fallas + " %";
  document.getElementById("kpi-pendientes").textContent = data.kpi.pendientes;
  document.getElementById("kpi-fuerauso").textContent = data.kpi.fueraUso;

  // === Gráfica barras ===
  const ctxBar = document.getElementById("chartBar");
  new Chart(ctxBar, {
    type: "bar",
    data: {
      labels: data.series.map(r => r.semana),
      datasets: [
        {
          label: "Aprobadas",
          data: data.series.map(r => r.aprobadas),
          backgroundColor: "rgba(0, 200, 83, 0.7)"
        },
        {
          label: "Fallas",
          data: data.series.map(r => r.fallas),
          backgroundColor: "rgba(229, 57, 53, 0.7)"
        }
      ]
    },
    options: {
      responsive: true,
      plugins: { legend: { labels: { color: "#e0e0e0" } } },
      scales: {
        x: { ticks: { color: "#e0e0e0" } },
        y: { ticks: { color: "#e0e0e0" } }
      }
    }
  });

  // === Gráfica donut ===
// Forzamos 3 categorías fijas aunque vengan en 0 desde la API
const ctxDonut = document.getElementById("chartDonut");
const donutLabels = ["activo", "fuera de uso", "calibracion fallida"];
const donutValues = donutLabels.map(label => (data.estados && typeof data.estados[label] !== "undefined")
  ? data.estados[label]
  : 0
);

new Chart(ctxDonut, {
  type: "doughnut",
  data: {
    labels: donutLabels,
    datasets: [{
      data: donutValues,
      backgroundColor: [
        "rgba(123, 31, 162, 0.7)",  // activo
        "rgba(255, 193, 7, 0.7)",   // fuera de uso
        "rgba(244, 67, 54, 0.7)"    // calibracion fallida
      ],
      borderColor: "#111",
      borderWidth: 1
    }]
  },
  options: {
    responsive: true,
    plugins: {
      legend: { labels: { color: "#ffffff" } },
      tooltip: {
        titleColor: "#ffffff",
        bodyColor: "#ffffff"
      }
    }
  }
});


  // === Tabla ===
  const tbody = document.getElementById("tabla-detalle");
  tbody.innerHTML = "";
  data.tabla.forEach(f => {
    const tr = document.createElement("tr");
    tr.innerHTML = `<td>${f.id}</td>
                    <td>${f.ultima ?? "-"}</td>
                    <td>${f.resultado ?? "-"}</td>
                    <td>${f.proxima ?? "-"}</td>
                    <td>${f.estado}</td>`;
    tbody.appendChild(tr);
  });
});
