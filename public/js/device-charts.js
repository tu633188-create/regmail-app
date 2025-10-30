(function () {
    const dataEl = document.getElementById('chart-data');
    const canvas = document.getElementById('registrationsChart');
    if (!dataEl || !canvas) return;

    try {
        const payload = JSON.parse(dataEl.textContent || '{}');
        const labels = Array.isArray(payload.labels) ? payload.labels : [];
        const values = Array.isArray(payload.values) ? payload.values : [];

        new Chart(canvas, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Registrations',
                    data: values,
                    backgroundColor: 'rgba(59,130,246,0.6)',
                    borderColor: '#3b82f6',
                    borderWidth: 1,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: { display: true },
                    y: { beginAtZero: true, ticks: { precision: 0 } },
                },
            },
        });
    } catch (e) {
        console.error('Failed to render chart', e);
    }
})();


