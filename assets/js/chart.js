// assets/js/chart.js

document.addEventListener('DOMContentLoaded', function() {
    console.log('chart.js loaded.');

    // This file will contain functions to create and update charts
    // using the Chart.js library.

    // Example function to create a simple line chart
    // This will be used later in patient-analytics.js or similar pages.
    window.createLineChart = function(canvasId, labels, data, labelText, borderColor, backgroundColor) {
        const ctx = document.getElementById(canvasId);
        if (!ctx) {
            console.warn(`Canvas with ID '${canvasId}' not found for chart.`);
            return null;
        }

        return new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: labelText,
                    data: data,
                    borderColor: borderColor,
                    backgroundColor: backgroundColor,
                    tension: 0.3, // Smooth curves
                    fill: false // No fill under the line
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false, // Allow canvas to resize freely
                scales: {
                    y: {
                        beginAtZero: true
                    }
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                    }
                }
            }
        });
    };

    // Example: Create a dummy chart on a specific page (e.g., admin dashboard)
    // This is just for demonstration if you want to see a chart immediately.
    // You'd typically call createLineChart from specific pages like patient-analytics.js
    /*
    const dummyChartCanvas = document.getElementById('dashboardPatientsChart');
    if (dummyChartCanvas) {
        const labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'];
        const data = [65, 59, 80, 81, 56, 55];
        createLineChart('dashboardPatientsChart', labels, data, 'New Patients', 'rgb(75, 192, 192)', 'rgba(75, 192, 192, 0.2)');
    }
    */
});
