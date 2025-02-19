class ReportManager {
    constructor() {
        this.charts = {};
        this.initializeEventListeners();
        if (window.reportData) {
            this.initializeCharts();
        } else {
            console.error('Report data is not available');
        }
    }

    initializeEventListeners() {
        // Add date range picker listeners
        $('#startDate, #endDate').on('change', () => this.handleDateFilter());
        $('#dateRangeFilter').on('change', (e) => this.handlePresetDateRange(e.target.value));
    }

    handlePresetDateRange(range) {
        const today = moment();
        let startDate, endDate;

        switch(range) {
            case 'week':
                startDate = today.clone().subtract(1, 'week');
                break;
            case 'month':
                startDate = today.clone().subtract(1, 'month');
                break;
            case 'year':
                startDate = today.clone().subtract(1, 'year');
                break;
            default:
                startDate = null;
                endDate = null;
                break;
        }

        if (startDate) {
            $('#startDate').val(startDate.format('YYYY-MM-DD'));
            $('#endDate').val(today.format('YYYY-MM-DD'));
        } else {
            $('#startDate').val('');
            $('#endDate').val('');
        }

        this.handleDateFilter();
    }

    handleDateFilter() {
        const startDate = $('#startDate').val();
        const endDate = $('#endDate').val();

        // Make AJAX call to get filtered data
        $.ajax({
            url: '/src/api/report-data.php',
            method: 'GET',
            data: { startDate, endDate },
            success: (response) => {
                if (response.success) {
                    window.reportData = response.data;
                    this.updateCharts();
                } else {
                    console.error('Error in API response:', response.error);
                }
            },
            error: (err) => {
                console.error('Error fetching filtered data:', err);
            }
        });
    }

    updateCharts() {
        // Update growth trends chart
        if (this.charts.growthTrends) {
            this.charts.growthTrends.data.labels = window.reportData.dates;
            this.charts.growthTrends.data.datasets[0].data = window.reportData.weights;
            this.charts.growthTrends.data.datasets[1].data = window.reportData.heights;
            this.charts.growthTrends.update();
        }

        // Update BMI distribution chart
        if (this.charts.bmiDistribution) {
            this.charts.bmiDistribution.data.labels = window.reportData.dates;
            this.charts.bmiDistribution.data.datasets[0].data = window.reportData.bmis;
            this.charts.bmiDistribution.update();
        }

        // Update arm circumference chart
        if (this.charts.armCircumference) {
            this.charts.armCircumference.data.labels = window.reportData.dates;
            this.charts.armCircumference.data.datasets[0].data = window.reportData.armCircumferences;
            this.charts.armCircumference.update();
        }

        // Update the measurements table if DataTable is initialized
        const table = $('#measurementsTable').DataTable();
        if (table) {
            table.clear();
            window.reportData.dates.forEach((date, index) => {
                table.row.add([
                    moment(date).format('MMM D, YYYY'),
                    window.reportData.weights[index],
                    window.reportData.heights[index],
                    window.reportData.bmis[index],
                    window.reportData.armCircumferences[index],
                    // You might need to adjust this based on your status calculation
                    this.getStatusBadge(window.reportData.bmis[index])
                ]);
            });
            table.draw();
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

    // Helper method to generate status badge HTML
    getStatusBadge(bmi) {
        let status, className;
        if (bmi < 18.5) {
            status = 'Underweight';
            className = 'status-warning';
        } else if (bmi >= 25) {
            status = 'Overweight';
            className = 'status-alert';
        } else {
            status = 'Normal';
            className = 'status-normal';
        }
        return `<span class="status-badge ${className}">${status}</span>`;
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
