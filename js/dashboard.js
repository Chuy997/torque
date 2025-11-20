// /var/www/html/torque/js/dashboard.js
document.addEventListener('DOMContentLoaded', () => {
    const url = '/torque/api/dashboard_data.php';

    fetch(url)
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.json();
        })
        .then(data => {
            // KPIs
            document.getElementById('kpi-aprobadas').textContent = data.kpi.aprobadas_pct + '%';
            document.getElementById('kpi-falla').textContent = data.kpi.fallas_pct + '%';
            document.getElementById('kpi-pendientes').textContent = data.kpi.pendientes;
            document.getElementById('kpi-fuerauso').textContent = data.kpi.fuera_uso;

            // Gráfica Bar (aprobadas vs fallas por semana)
            const ctxBar = document.getElementById('chartBar').getContext('2d');
            new Chart(ctxBar, {
                type: 'bar',
                data: {
                    labels: data.chartBar.map(r => 'Semana ' + r.semana),
                    datasets: [
                        {
                            label: 'Aprobadas',
                            data: data.chartBar.map(r => r.aprobadas),
                            backgroundColor: '#28a745'
                        },
                        {
                            label: 'Fallas',
                            data: data.chartBar.map(r => r.fallas),
                            backgroundColor: '#dc3545'
                        }
                    ]
                },
                options: { responsive: true }
            });

            // Gráfica Line (tendencia histórica % aprobadas)
            const ctxLine = document.getElementById('chartLine').getContext('2d');
            new Chart(ctxLine, {
                type: 'line',
                data: {
                    labels: data.chartBar.map(r => 'Semana ' + r.semana),
                    datasets: [{
                        label: '% Aprobadas',
                        data: data.chartBar.map(r => r.aprobadas ? (r.aprobadas / (r.aprobadas + r.fallas)) * 100 : 0),
                        borderColor: '#007bff',
                        fill: false
                    }]
                },
                options: { responsive: true }
            });

            // Gráfica Donut (estados actuales)
            const ctxDonut = document.getElementById('chartDonut').getContext('2d');
            new Chart(ctxDonut, {
                type: 'doughnut',
                data: {
                    labels: data.chartDonut.map(r => r.status),
                    datasets: [{
                        data: data.chartDonut.map(r => r.count),
                        backgroundColor: ['#28a745', '#ffc107', '#dc3545']
                    }]
                },
                options: { responsive: true }
            });

            // Tabla dinámica
            const tbody = document.getElementById('tabla-detalle');
            tbody.innerHTML = '';
            data.tabla.forEach(row => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${row.torqueID}</td>
                    <td>${row.fechaCalibracion || 'Nunca'}</td>
                    <td>${row.promedio !== null ? row.promedio.toFixed(2) : 'Sin datos'}</td>
                    <td>${row.resultado || 'Sin calibración'}</td>
                    <td>—</td>
                    <td>${row.status || 'Desconocido'}</td>
                `;
                tbody.appendChild(tr);
            });

        })
        .catch(error => {
            console.error('Error cargando dashboard:', error);
            alert('Error al cargar los datos. Revisa la consola.');
        });
});
