document.addEventListener("DOMContentLoaded", async function () {
  try {
    const resp = await fetch("api/dashboard_data.php");
    if (!resp.ok) throw new Error("Error HTTP " + resp.status);
    const data = await resp.json();

    // === KPIs ===
    document.getElementById("kpi-aprobadas").textContent = data.kpi.aprobadas + " %";
    document.getElementById("kpi-falla").textContent     = data.kpi.fallas + " %";
    document.getElementById("kpi-pendientes").textContent= data.kpi.pendientes;
    document.getElementById("kpi-fuerauso").textContent  = data.kpi.fueraUso;

    // === Gráfica barras (aprobadas vs fallas por semana) ===
    const ctxBar = document.getElementById("chartBar");
    new Chart(ctxBar, {
      type: "bar",
      data: {
        labels: data.series.map(r => r.semana),
        datasets: [
          { label: "Aprobadas", data: data.series.map(r => r.aprobadas), backgroundColor: "rgba(0, 200, 83, 0.7)" },
          { label: "Fallas",    data: data.series.map(r => r.fallas),    backgroundColor: "rgba(229, 57, 53, 0.7)" }
        ]
      },
      options: {
        responsive: true,
        plugins: {
          legend: { labels: { color: "#ffffff" } },
          tooltip: { titleColor: "#ffffff", bodyColor: "#ffffff" }
        },
        scales: {
          x: { ticks: { color: "#ffffff" }, grid: { color: "#444" } },
          y: { ticks: { color: "#ffffff" }, grid: { color: "#444" } }
        }
      }
    });

    // === Gráfica línea (% aprobadas por semana) ===
    const ctxLine = document.getElementById("chartLine");
    new Chart(ctxLine, {
      type: "line",
      data: {
        labels: data.series.map(r => r.semana),
        datasets: [
          {
            label: "% Aprobadas",
            data: data.series.map(r => r.pct_ok),
            fill: false,
            borderWidth: 2,
            tension: 0.25
          }
        ]
      },
      options: {
        responsive: true,
        plugins: {
          legend: { labels: { color: "#ffffff" } },
          tooltip: { titleColor: "#ffffff", bodyColor: "#ffffff" }
        },
        scales: {
          x: { ticks: { color: "#ffffff" }, grid: { color: "#444" } },
          y: { ticks: { color: "#ffffff" }, grid: { color: "#444" }, suggestedMin: 0, suggestedMax: 100 }
        }
      }
    });

    // === Gráfica donut (estados torques) ===
    const ctxDonut = document.getElementById("chartDonut");
    new Chart(ctxDonut, {
      type: "doughnut",
      data: {
        labels: Object.keys(data.estados),
        datasets: [{
          data: Object.values(data.estados),
          backgroundColor: [
            "rgba(123, 31, 162, 0.7)",
            "rgba(255, 193, 7, 0.7)",
            "rgba(244, 67, 54, 0.7)"
          ]
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: { labels: { color: "#ffffff" } },
          tooltip: { titleColor: "#ffffff", bodyColor: "#ffffff" }
        }
      }
    });

    // === Gráfica Pareto de fallas (barras) ===
    const ctxPareto = document.getElementById("chartPareto");
    new Chart(ctxPareto, {
      type: "bar",
      data: {
        labels: data.pareto.map(r => r.torqueID),
        datasets: [
          { label: "Fallas", data: data.pareto.map(r => r.fails), backgroundColor: "rgba(229, 57, 53, 0.8)" }
        ]
      },
      options: {
        responsive: true,
        plugins: {
          legend: { labels: { color: "#ffffff" } },
          tooltip: { titleColor: "#ffffff", bodyColor: "#ffffff" }
        },
        scales: {
          x: { ticks: { color: "#ffffff" }, grid: { color: "#444" } },
          y: { ticks: { color: "#ffffff" }, grid: { color: "#444" }, beginAtZero: true }
        }
      }
    });

        // === Tabla detalle ===
    const tbody = document.getElementById("tabla-detalle");
    tbody.innerHTML = "";
    data.tabla.forEach(f => {
      const tr = document.createElement("tr");
      const color = (f.resultado === "aprobado") ? "text-success" :
                    (f.resultado === "fuera de tolerancia") ? "text-danger" : "text-light";
      const promTxt = (f.promedio !== null && f.promedio !== undefined)
                        ? Number(f.promedio).toFixed(2)
                        : "-";
      tr.innerHTML = `<td>${f.id}</td>
                      <td>${f.ultima ?? "-"}</td>
                      <td>${promTxt}</td>
                      <td class="${color}">${f.resultado ?? "-"}</td>
                      <td>${f.proxima ?? "-"}</td>
                      <td>${f.estado}</td>`;
      tbody.appendChild(tr);
    });

  } catch (err) {
    console.error("Error cargando dashboard:", err);
    alert("No se pudieron cargar los datos del dashboard.");
  }
});
