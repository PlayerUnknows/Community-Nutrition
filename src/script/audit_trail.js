$(document).ready(function() {
    let auditTable = null;

    // Function to initialize audit table
    window.initializeAuditTable = function() {
        // Destroy existing instance if it exists
        if ($.fn.DataTable.isDataTable('#auditTable')) {
            $('#auditTable').DataTable().destroy();
        }

        // Clear any existing event handlers
        $('#auditTable').off();

        // Initialize new instance
        auditTable = $('#auditTable').DataTable({
            order: [[0, 'desc']],
            pageLength: 10,
            lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
            responsive: true,
            dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
                 "<'row'<'col-sm-12'tr>>" +
                 "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
            language: {
                lengthMenu: "Show _MENU_ entries",
                search: "Search:",
                info: "Showing _START_ to _END_ of _TOTAL_ entries",
                paginate: {
                    first: "First",
                    last: "Last",
                    next: "Next",
                    previous: "Previous"
                }
            }
        });

        return auditTable;
    };

    // Initialize table when audit tab is shown
    $(document).on('shown.bs.tab', 'button[data-bs-toggle="tab"]', function(e) {
        if ($(e.target).attr('id') === 'audit-tab') {
            setTimeout(window.initializeAuditTable, 100);
        }
    });

    // Handle filter form submission
    $(document).on('submit', '.audit-filter-form', function(e) {
        e.preventDefault();
        
        // Show loading indicator
        $('#auditTable').addClass('loading');
        
        // Get form data
        const formData = new FormData(this);
        
        // Make AJAX request
        $.ajax({
            url: '../backend/fetch_audit_trail.php',
            method: 'GET',
            data: new URLSearchParams(formData).toString(),
            success: function(response) {
                // Clear existing table
                if ($.fn.DataTable.isDataTable('#auditTable')) {
                    $('#auditTable').DataTable().destroy();
                }
                $('#auditTable tbody').empty();
                
                // Add new data
                if (response && response.length > 0) {
                    response.forEach(function(audit) {
                        let detailsHtml = '';
                        if (audit.details) {
                            try {
                                const details = JSON.parse(audit.details);
                                detailsHtml = '<div class="audit-details">';
                                for (const [key, value] of Object.entries(details)) {
                                    const displayKey = key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                                    detailsHtml += `<div><strong>${displayKey}:</strong> ${value}</div>`;
                                }
                                detailsHtml += '</div>';
                            } catch (e) {
                                detailsHtml = audit.details;
                            }
                        }
                        
                        $('#auditTable tbody').append(`
                            <tr>
                                <td>${audit.action_timestamp}${audit.count > 1 ? '<br><small class="text-muted">(' + audit.count + ' similar actions)</small>' : ''}</td>
                                <td>${audit.username || 'System'}</td>
                                <td>${audit.action}</td>
                                <td>${detailsHtml}</td>
                            </tr>
                        `);
                    });
                }
                
                // Reinitialize DataTable
                window.initializeAuditTable();
            },
            error: function(xhr, status, error) {
                console.error('Error fetching audit trail:', error);
                alert('Error fetching audit trail data. Please try again.');
            },
            complete: function() {
                $('#auditTable').removeClass('loading');
            }
        });
    });
});
