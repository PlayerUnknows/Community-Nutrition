// Create a global namespace for monitoring
window.MonitoringModule = (function() {
    let monitoringTable = null;
    let currentPatientId = null;
    let isInitialized = false;

    function displayMonitoringDetails(details) {
        $("#weightCategory").text(details.weight_category || "N/A");
        $("#bmiStatus").text(details.finding_bmi || "N/A");
        $("#growthStatus").text(details.finding_growth || "N/A");
        $("#armCircumference").text(details.arm_circumference || "N/A");
        $("#armStatus").text(details.arm_circumference_status || "N/A");
        $("#findings").text(details.findings || "N/A");

        var appointmentDate = details.date_of_appointment
            ? new Date(details.date_of_appointment).toLocaleDateString()
            : "N/A";
        var appointmentTime = details.time_of_appointment || "N/A";
        var createdAt = details.created_at
            ? new Date(details.created_at).toLocaleString()
            : "N/A";

        $("#appointmentDate").text(appointmentDate);
        $("#appointmentTime").text(appointmentTime);
        $("#place").text(details.place || "N/A");
        $("#createdAt").text(createdAt);
    }

    function handleViewDetails($button) {
        currentPatientId = $button.data('patient-id');
        
        $.ajax({
            url: "../backend/get_monitoring_details.php",
            method: "GET",
            data: { id: currentPatientId },
            success: function (response) {
                if (response.status === "success" && response.data) {
                    displayMonitoringDetails(response.data);
                    $("#monitoringDetailsModal").modal("show");
                } else {
                    alert("Failed to load details: " + (response.message || "Unknown error"));
                }
            },
            error: function(xhr, status, error) {
                console.error("Error fetching details:", error);
                alert("Failed to load patient details. Please try again.");
            }
        });
    }

    function handleFileImport() {
        const fileInput = $("#importFile")[0];
        if (!fileInput.files.length) {
            alert("Please select a file to import");
            return;
        }

        const formData = new FormData();
        formData.append("importFile", fileInput.files[0]);

        $.ajax({
            url: "../backend/import_monitoring.php",
            method: "POST",
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                if (response.status === "success") {
                    alert(response.message);
                    $("#importDataModal").modal("hide");
                    if (monitoringTable) {
                        monitoringTable.ajax.reload();
                    }
                } else {
                    alert("Import failed: " + response.message);
                }
            },
            error: function () {
                alert("Import failed. Please try again.");
            }
        });
    }

    function initializeTable() {
        try {
            // Always destroy existing instance first
            if (monitoringTable) {
                monitoringTable.destroy();
                monitoringTable = null;
                $('#monitoringTable tbody').empty();
            }

            // Create new instance
            monitoringTable = $('#monitoringTable').DataTable({
                retrieve: true, // Try to reuse existing instance
                processing: true,
                serverSide: false,
                pageLength: 5,
                lengthChange: true,
                lengthMenu: [
                    [5, 10, 25, 50, -1],
                    [5, 10, 25, 50, "All"],
                ],
                dom:
                    "<'row'<'col-sm-12'tr>>" +
                    "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
                searching: true,
                responsive: true,
                autoWidth: false,
                scrollX: false,
                scrollY: "50vh",
                scrollCollapse: false,
                scroller: false,
                fixedHeader: false,
                columns: [
                    {
                        data: "patient_id",
                        className: "dt-left",
                    },
                    {
                        data: "patient_fam_id",
                        className: "dt-left",
                    },
                    {
                        data: "age",
                        className: "dt-center",
                    },
                    {
                        data: "sex",
                        className: "dt-center",
                    },
                    {
                        data: "weight",
                        className: "dt-right",
                        render: function (data) {
                            return data ? parseFloat(data).toFixed(2) + " kg" : "";
                        },
                    },
                    {
                        data: "height",
                        className: "dt-right",
                        render: function (data) {
                            return data ? parseFloat(data).toFixed(2) + " cm" : "";
                        },
                    },
                    {
                        data: "bp",
                        className: "dt-center",
                    },
                    {
                        data: "temperature",
                        className: "dt-right",
                        render: function (data) {
                            return data ? parseFloat(data).toFixed(1) + " °C" : "";
                        },
                    },
                    {
                        data: null,
                        className: "dt-center",
                        render: function (data, type, row) {
                            return (
                                '<button class="btn btn-primary btn-sm btn-view" data-patient-id="' +
                                row.patient_id +
                                '"><i class="fas fa-eye"></i> View</button>'
                            );
                        },
                    },
                ],
                ajax: {
                    url: "../backend/fetch_monitoring.php",
                    dataSrc: function (json) {
                        return json.status === "success" ? json.data : [];
                    }
                },
                order: [[0, "desc"]],
                initComplete: function () {
                    setupEventHandlers();
                    setupModalHandlers();
                    this.api().columns.adjust();
                }
            });

            return monitoringTable;
        } catch (error) {
            console.error('Error initializing monitoring table:', error);
            return null;
        }
    }

    function setupEventHandlers() {
        // Remove existing handlers
        $(window).off('resize.monitoring');
        $("#monitoringTable").off('click', '.btn-view');
        $("#confirmImportBtn").off('click');
        $("#downloadTemplateBtn").off('click');

        // Add new handlers
        $(window).on('resize.monitoring', function() {
            if (monitoringTable) {
                monitoringTable.columns.adjust();
            }
        });

        $("#monitoringTable").on("click", ".btn-view", function() {
            handleViewDetails($(this));
        });

        $("#confirmImportBtn").on("click", handleFileImport);
        $("#downloadTemplateBtn").on("click", function() {
            window.location.href = "../backend/download_template.php";
        });
    }

    function setupModalHandlers() {
        // Remove previous handlers
        $("#checkupHistoryModal, #monitoringDetailsModal, #importDataModal").off();

        // Add new handlers
        $("#checkupHistoryModal")
            .on("show.bs.modal", function() {
                console.log("History modal is showing");
            })
            .on("hidden.bs.modal", function() {
                $("#monitoringDetailsModal").modal("show");
            });

        $("#monitoringDetailsModal").on("hidden.bs.modal", function() {
            currentPatientId = null;
        });

        $("#importDataModal").on("hidden.bs.modal", function() {
            $("#importFile").val("");
        });
    }

    function adjustTable() {
        if (monitoringTable) {
            monitoringTable.columns.adjust().draw();
        }
    }

    return {
        init: function() {
            if (!isInitialized) {
                initializeTable();
                setupEventHandlers();
                setupModalHandlers();
                isInitialized = true;
            }
        },
        getTable: function() {
            return monitoringTable;
        },
        refreshTable: function() {
            if (monitoringTable) {
                monitoringTable.ajax.reload(null, false);
            }
        },
        destroy: function() {
            if (monitoringTable) {
                monitoringTable.destroy();
                monitoringTable = null;
            }
            isInitialized = false;
        },
        adjustTable: adjustTable
    };
})();

// Handle tab events
$(document).ready(function() {
    // Initialize when monitoring tab is shown
    $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
        if ($(e.target).attr('id') === 'schedule-tab') {
            MonitoringModule.init();
        }
    });

    // Cleanup when leaving monitoring tab
    $('button[data-bs-toggle="tab"]').on('hide.bs.tab', function(e) {
        if ($(e.target).attr('id') === 'schedule-tab') {
            MonitoringModule.destroy();
        }
    });
});
