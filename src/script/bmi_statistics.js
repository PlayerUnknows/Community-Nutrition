document.addEventListener('DOMContentLoaded', function() {
    let bmiTable;
    let bmiChart = null;

    const initBMIChart = (data) => {
        const ctx = document.getElementById('bmiDistributionChart');
        if (!ctx) return;

        try {
            if (bmiChart instanceof Chart) {
                bmiChart.destroy();
            }
            
            const existingChart = Chart.getChart(ctx);
            if (existingChart) {
                existingChart.destroy();
            }

            // Initialize counters for each BMI type
            const bmiCounts = {
                'Severely Wasted': { female: 0, male: 0 },
                'Wasted': { female: 0, male: 0 },
                'Normal': { female: 0, male: 0 },
                'Obese': { female: 0, male: 0 }
            };

            // Count data
            data.forEach(record => {
                const bmiType = record.finding_bmi;
                const sex = record.sex?.toUpperCase();

                if (bmiCounts[bmiType] && (sex === 'M' || sex === 'F')) {
                    const gender = sex === 'M' ? 'male' : 'female';
                    bmiCounts[bmiType][gender]++;
                }
            });

            // Prepare chart data
            const labels = [];
            const chartData = [];
            const colors = [];

            Object.entries(bmiCounts).forEach(([bmiType, counts]) => {
                // Add Female, BMI Type, Male for each BMI category
                labels.push('Female', bmiType, 'Male');
                chartData.push(counts.female, counts.female + counts.male, counts.male);

                // Set colors for each group
                let bmiColor;
                switch(bmiType) {
                    case 'Severely Wasted': bmiColor = 'rgb(255, 0, 0)'; break;    // Red
                    case 'Wasted': bmiColor = 'rgb(0, 255, 0)'; break;            // Lime
                    case 'Normal': bmiColor = 'rgb(0, 128, 0)'; break;            // Green
                    case 'Obese': bmiColor = 'rgb(255, 69, 0)'; break;            // Red-Orange
                    default: bmiColor = 'rgb(128, 128, 128)';
                }
                colors.push('rgb(255, 192, 203)', bmiColor, 'rgb(0, 0, 255)');
            });

            // Create chart
            bmiChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        data: chartData,
                        backgroundColor: colors,
                        barPercentage: 0.8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'bottom',
                            labels: {
                                generateLabels: () => [
                                    { text: 'Female', fillStyle: 'rgb(255, 192, 203)' },
                                    { text: 'BMI Category', fillStyle: 'rgb(128, 128, 128)' },
                                    { text: 'Male', fillStyle: 'rgb(0, 0, 255)' }
                                ]
                            }
                        },
                        title: {
                            display: true,
                            text: 'BMI Distribution by Category and Sex',
                            font: { size: 16, weight: 'bold' }
                        }
                    }
                }
            });

            // Update BMI Category Distribution table
            const tableBody = document.querySelector('#bmiCategoryTable tbody');
            if (tableBody) {
                const total = Object.values(bmiCounts).reduce((sum, counts) => 
                    sum + counts.female + counts.male, 0);

                tableBody.innerHTML = '';
                Object.entries(bmiCounts).forEach(([category, counts]) => {
                    const count = counts.female + counts.male;
                    const percentage = total > 0 ? ((count / total) * 100).toFixed(1) : 0;
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${category}</td>
                        <td>${count}</td>
                        <td>${percentage}%</td>
                    `;
                    tableBody.appendChild(row);
                });
            }

        } catch (error) {
            console.error('Error initializing BMI chart:', error);
        }
    };

    // Initialize DataTable
    const initDataTable = () => {
        try {
            if ($.fn.DataTable.isDataTable('#bmiTable')) {
                $('#bmiTable').DataTable().destroy();
            }

            const table = $('#bmiTable').DataTable({
                processing: true,
                serverSide: false,
                dom: '<"d-flex justify-content-between align-items-center mb-3"f>rt<"d-flex justify-content-between align-items-center mt-3"lip>',
                ajax: {
                    url: '../controllers/ReportController.php',
                    type: 'POST',
                    data: function(d) {
                        return {
                            action: 'getBMIDetails',
                            startDate: $('#dateRangePicker').data('startDate') || '',
                            endDate: $('#dateRangePicker').data('endDate') || ''
                        };
                    },
                    dataSrc: function(response) {
                        if (response.status === 'success') {
                            setTimeout(() => {
                                initBMIChart(response.data);
                            }, 0);
                            return response.data || [];
                        } else {
                            console.error('Server error:', response.message);
                            alert('Error loading data: ' + response.message);
                            return [];
                        }
                    },
                    error: function(xhr, error, thrown) {
                        console.error('AJAX error:', error, thrown);
                        alert('Error connecting to server. Please try again.');
                    }
                },
                columns: [
                    { 
                        data: 'checkup_date',
                        render: function(data) {
                            return moment(data).format('MMM DD, YYYY');
                        }
                    },
                    { data: 'patient_id' },
                    { 
                        data: 'age',
                        render: function(data) {
                            return data + ' years';
                        }
                    },
                    { data: 'finding_bmi' }
                ],
                order: [[0, 'desc']],
                pageLength: 10,
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
                responsive: true,
                scrollY: '400px',
                scrollCollapse: true
            });

            // Initialize daterangepicker
            $('#dateRangePicker').daterangepicker({
                autoUpdateInput: false,
                locale: {
                    cancelLabel: 'Clear'
                }
            });

            $('#dateRangePicker').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('MM/DD/YYYY') + ' - ' + picker.endDate.format('MM/DD/YYYY'));
                $(this).data('startDate', picker.startDate.format('YYYY-MM-DD'));
                $(this).data('endDate', picker.endDate.format('YYYY-MM-DD'));
            });

            $('#dateRangePicker').on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('');
                $(this).data('startDate', '');
                $(this).data('endDate', '');
            });

            // Handle entries select
            $('#entriesSelect').on('change', function() {
                table.page.len($(this).val()).draw();
            });

            // Handle apply date range
            $('#applyDateRange').on('click', function() {
                table.ajax.reload();
            });

        } catch (error) {
            console.error('Error initializing DataTable:', error);
        }
    };

    // Initialize DataTable when the page loads
    initDataTable();
}); 