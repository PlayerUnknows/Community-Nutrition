// Initialize charts when the document is ready
document.addEventListener('DOMContentLoaded', function() {
    initBarChart();
    initLineChart();
    loadProgressDetails();
});

// Initialize the bar chart showing current nutrition status
function initBarChart() {
    const ctx = document.getElementById('nutritionBarChart').getContext('2d');
    const centers = ['Center 1', 'Center 2', 'Center 3', 'Center 4', 'Center 5', 'Center 6'];
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: centers,
            datasets: [
                {
                    label: '0-4 years',
                    data: [85, 82, 88, 87, 86, 83],
                    backgroundColor: 'rgba(75, 192, 192, 0.6)',
                },
                {
                    label: '5-9 years',
                    data: [88, 85, 87, 89, 84, 86],
                    backgroundColor: 'rgba(54, 162, 235, 0.6)',
                },
                {
                    label: '10-14 years',
                    data: [86, 88, 85, 84, 87, 89],
                    backgroundColor: 'rgba(153, 102, 255, 0.6)',
                }
            ]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    title: {
                        display: true,
                        text: 'Nutrition Score (%)'
                    }
                }
            },
            plugins: {
                title: {
                    display: true,
                    text: 'Nutrition Status by Age Group Across Centers'
                }
            }
        }
    });
}

// Initialize the line chart showing historical trends
function initLineChart() {
    const ctx = document.getElementById('nutritionLineChart').getContext('2d');
    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'];
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: months,
            datasets: [
                {
                    label: '0-4 years',
                    data: [80, 82, 83, 85, 86, 85],
                    borderColor: 'rgba(75, 192, 192, 1)',
                    tension: 0.1
                },
                {
                    label: '5-9 years',
                    data: [82, 84, 85, 87, 88, 88],
                    borderColor: 'rgba(54, 162, 235, 1)',
                    tension: 0.1
                },
                {
                    label: '10-14 years',
                    data: [81, 83, 84, 86, 87, 86],
                    borderColor: 'rgba(153, 102, 255, 1)',
                    tension: 0.1
                }
            ]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    title: {
                        display: true,
                        text: 'Nutrition Score (%)'
                    }
                }
            },
            plugins: {
                title: {
                    display: true,
                    text: 'Nutrition Progress Over Time'
                }
            }
        }
    });
}

// Load progress details for each age group
function loadProgressDetails() {
    const progressData = {
        '0-4': {
            improvements: '+5% overall improvement',
            keyMetrics: [
                'Weight-for-age: +3%',
                'Height-for-age: +4%',
                'BMI: Maintained healthy range'
            ]
        },
        '5-9': {
            improvements: '+6% overall improvement',
            keyMetrics: [
                'Weight-for-age: +4%',
                'Height-for-age: +5%',
                'BMI: Improved to healthy range'
            ]
        },
        '10-14': {
            improvements: '+4% overall improvement',
            keyMetrics: [
                'Weight-for-age: +3%',
                'Height-for-age: +3%',
                'BMI: Maintained healthy range'
            ]
        }
    };

    // Update progress details for each age group
    Object.keys(progressData).forEach(ageGroup => {
        const container = document.getElementById(`progress-${ageGroup.replace('-', '-')}`);
        const data = progressData[ageGroup];
        
        container.innerHTML = `
            <div class="card-body">
                <p class="text-success fw-bold">${data.improvements}</p>
                <ul class="list-unstyled">
                    ${data.keyMetrics.map(metric => `<li class="mb-2">• ${metric}</li>`).join('')}
                </ul>
            </div>
        `;
    });
}

// Function to update charts with new data (can be connected to backend API)
function updateChartData() {
    // This function can be implemented to fetch and update data from the backend
    // For now, it's a placeholder for future implementation
}
