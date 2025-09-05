<?php
// Footer includes for JavaScript libraries and custom scripts
?>

<!-- Core JavaScript Libraries -->
<script src="../../node_modules/jquery/dist/jquery.min.js"></script>
<script src="../../node_modules/@popperjs/core/dist/umd/popper.min.js"></script>
<script src="../../node_modules/bootstrap/dist/js/bootstrap.min.js"></script>
<script src="../../node_modules/moment/moment.js"></script>

<!-- DataTables JavaScript -->
<script src="../../node_modules/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="../../node_modules/datatables.net-bs5/js/dataTables.bootstrap5.min.js"></script>

<!-- DateRangePicker JavaScript -->
<script src="../../node_modules/daterangepicker/daterangepicker.js"></script>

<!-- Chart.js JavaScript -->
<script src="../../node_modules/chart.js/dist/chart.umd.js"></script>
<script src="../../node_modules/chartjs-plugin-datalabels/dist/chartjs-plugin-datalabels.min.js"></script>

<!-- SweetAlert2 JavaScript -->
<script src="../../node_modules/sweetalert2/dist/sweetalert2.all.min.js"></script>

<!-- Custom Scripts - Load in specific order -->
<script src="../script/bmi_statistics.js"></script>
<script src="../script/audit_trail.js"></script>
<script src="../script/user/users.js"></script>
<script src="../script/overall_report.js"></script>
<script src="../script/dropdrown.js"></script>
<script src="../script/user/logout.js"></script>
<script src="../script/session.js"></script>
<script src="../script/admin.js"></script>

<!-- Loading screen script -->
<script>
    window.addEventListener('load', function() {
        const loadingScreen = document.getElementById('loading-screen');
        if (loadingScreen) {
            loadingScreen.style.opacity = '0';
            loadingScreen.style.transition = 'opacity 0.5s ease';
            setTimeout(function() {
                loadingScreen.style.display = 'none';
            }, 500);
        }
    });
</script>
