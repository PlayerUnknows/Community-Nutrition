// Example: Bar Chart for Reports
const ctx = document.getElementById('reportChart').getContext('2d');
const reportChart = new Chart(ctx, {


    type: 'bar',
    data: {
        labels: ['Patient 1', 'Patient 2', 'Patient 3', 'Patient 4'],
        datasets: [{
            label: 'Check-Up Frequency',
            data: [5, 7, 3, 6],
            backgroundColor: 'rgba(54, 162, 235, 0.2)',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 1
        }]
    },
    options: {
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});
