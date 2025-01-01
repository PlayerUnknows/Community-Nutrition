$(document).ready(function() {
    let auditTable = null;

    // Function to initialize audit table
    function initializeAuditTable() {
        // Destroy existing instance if it exists
        if ($.fn.DataTable.isDataTable('#auditTable')) {
            $('#auditTable').DataTable().destroy();
        }

        // Clear any existing event handlers
        $('#auditTable').off();

        // Initialize new instance
        auditTable = $('#auditTable').DataTable({
            order: [[0, 'desc']],
            pageLength: 5,
            lengthMenu: [[5, 10, 25, 50], [5, 10, 25, 50]],
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
    }

    // Initialize table when audit tab is shown
    $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
        if ($(e.target).attr('id') === 'audit-tab') {
            setTimeout(initializeAuditTable, 100);
        }
    });

    // Initialize if audit tab is active on page load
    if ($('#audit-tab').hasClass('active')) {
        setTimeout(initializeAuditTable, 100);
    }

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
                                if (audit.action === 'UPDATED_USER') {
                                    const roleMap = {
                                        '1': 'Parent',
                                        '2': 'Brgy Health Worker',
                                        '3': 'Administrator'
                                    };

                                    detailsHtml = '<div class="audit-details">';
                                    
                                    // User ID
                                    if (details.updated_user_id) {
                                        detailsHtml += `<div><strong>User Id:</strong> ${details.updated_user_id}</div>`;
                                    }
                                    
                                    // Email Changes
                                    if (details.old_email) {
                                        detailsHtml += `<div><strong>Old Email:</strong> ${details.old_email}</div>`;
                                    }
                                    if (details.updated_user_email) {
                                        detailsHtml += `<div><strong>New Email:</strong> ${details.updated_user_email}</div>`;
                                    }
                                    
                                    // Role Changes
                                    if (details.old_role) {
                                        const oldRole = roleMap[details.old_role] || 'Unknown';
                                        detailsHtml += `<div><strong>Old Role:</strong> ${oldRole}</div>`;
                                    }
                                    if (details.new_role) {
                                        const newRole = roleMap[details.new_role] || 'Unknown';
                                        detailsHtml += `<div><strong>New Role:</strong> ${newRole}</div>`;
                                    }
                                    
                                    // Password Status
                                    if (details.password_changed !== undefined) {
                                        detailsHtml += `<div><strong>Password Status:</strong> ${details.password_changed ? 'Changed' : 'Not Changed'}</div>`;
                                    }
                                    
                                    detailsHtml += '</div>';
                                } else {
                                    detailsHtml = '<div class="audit-details">';
                                    for (const [key, value] of Object.entries(details)) {
                                        const displayKey = key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                                        detailsHtml += `<div><strong>${displayKey}:</strong> ${value}</div>`;
                                    }
                                    detailsHtml += '</div>';
                                }
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
                initializeAuditTable();
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
