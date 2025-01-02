class ReportManager {
    constructor() {
        this.charts = {};
        // Initialize only if we have the required data
        if (window.reportData) {
            this.initializeCharts();
        } else {
            console.error('Report data is not available');
        }
    }

    initializeCharts() {
        // Only initialize charts if the elements exist
        if (document.getElementById('growthTrendsChart')) {
            this.initializeGrowthTrendsChart();
        }
        if (document.getElementById('bmiDistributionChart')) {
            this.initializeBMIDistributionChart();
        }
        if (document.getElementById('armCircumferenceChart')) {
            this.initializeArmCircumferenceChart();
        }
        if (document.getElementById('nutritionBarChart')) {
            this.initializeNutritionBarChart();
        }
    }

    initializeGrowthTrendsChart() {
        const canvas = document.getElementById('growthTrendsChart');
        if (!canvas) return;

        const ctx = canvas.getContext('2d');
        this.charts.growthTrends = new Chart(ctx, {
            type: 'line',
            data: {
                labels: window.reportData.dates,
                datasets: [{
                    label: 'Weight (kg)',
                    data: window.reportData.weights,
                    borderColor: 'rgb(255, 99, 132)',
                    backgroundColor: 'rgba(255, 99, 132, 0.1)',
                    fill: true,
                    tension: 0.4
                }, {
                    label: 'Height (cm)',
                    data: window.reportData.heights,
                    borderColor: 'rgb(54, 162, 235)',
                    backgroundColor: 'rgba(54, 162, 235, 0.1)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                interaction: {
                    intersect: false,
                    mode: 'index'
                },
                plugins: {
                    legend: { position: 'top' },
                    title: {
                        display: true,
                        text: 'Patient Growth Progress'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: false,
                        grid: { drawBorder: false }
                    },
                    x: {
                        grid: { display: false }
                    }
                },
                animation: {
                    duration: 2000,
                    easing: 'easeInOutQuart'
                }
            }
        });
    }

    initializeBMIDistributionChart() {
        const canvas = document.getElementById('bmiDistributionChart');
        if (!canvas) return;

        const ctx = canvas.getContext('2d');
        this.charts.bmiDistribution = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: window.reportData.dates,
                datasets: [{
                    label: 'BMI',
                    data: window.reportData.bmis,
                    backgroundColor: 'rgba(75, 192, 192, 0.6)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'top' },
                    title: {
                        display: true,
                        text: 'BMI Changes Over Time'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: false,
                        grid: { drawBorder: false }
                    },
                    x: {
                        grid: { display: false }
                    }
                },
                animation: {
                    duration: 2000,
                    easing: 'easeInOutQuart'
                }
            }
        });
    }

    initializeArmCircumferenceChart() {
        const canvas = document.getElementById('armCircumferenceChart');
        if (!canvas) return;

        const ctx = canvas.getContext('2d');
        this.charts.armCircumference = new Chart(ctx, {
            type: 'line',
            data: {
                labels: window.reportData.dates,
                datasets: [{
                    label: 'Arm Circumference (cm)',
                    data: window.reportData.armCircumferences,
                    borderColor: 'rgb(153, 102, 255)',
                    backgroundColor: 'rgba(153, 102, 255, 0.1)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'top' },
                    title: {
                        display: true,
                        text: 'Arm Circumference Progress'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: false,
                        grid: { drawBorder: false }
                    },
                    x: {
                        grid: { display: false }
                    }
                },
                animation: {
                    duration: 2000,
                    easing: 'easeInOutQuart'
                }
            }
        });
    }

    initializeNutritionBarChart() {
        const canvas = document.getElementById('nutritionBarChart');
        if (!canvas) return;

        const ctx = canvas.getContext('2d');
        this.charts.nutritionBar = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Center 1', 'Center 2', 'Center 3', 'Center 4', 'Center 5', 'Center 6'],
                datasets: [{
                    label: 'Number of Patients',
                    data: [65, 59, 80, 81, 56, 55],
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.2)',
                        'rgba(54, 162, 235, 0.2)',
                        'rgba(255, 206, 86, 0.2)',
                        'rgba(75, 192, 192, 0.2)',
                        'rgba(153, 102, 255, 0.2)',
                        'rgba(255, 159, 64, 0.2)'
                    ],
                    borderColor: [
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(153, 102, 255, 1)',
                        'rgba(255, 159, 64, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Patient Distribution Across Centers'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }
}

// Initialize the report manager when the document is ready
$(document).ready(() => {
    try {
        window.reportManager = new ReportManager();
    } catch (error) {
        console.error('Error initializing ReportManager:', error);
    }
});
