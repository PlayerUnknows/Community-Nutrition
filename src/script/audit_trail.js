$(document).ready(function() {
    // WebSocket connection configuration
    const WS_URL = 'ws://localhost:8080';
    let socket = null;
    let reconnectAttempts = 0;
    const MAX_RECONNECT_ATTEMPTS = 5;

    function initWebSocket() {
        // TEMPORARILY DISABLED FOR PROTOTYPE
        console.log('WebSocket connection temporarily disabled');
        return;

        // Previous implementation commented out
        /*
        // Close existing socket if any
        if (socket) {
            socket.close();
        }

        // Create new WebSocket connection
        socket = new WebSocket(WS_URL);

        socket.onopen = function(e) {
            console.log('WebSocket connection established');
            reconnectAttempts = 0;  // Reset reconnect attempts on successful connection

            // Optional: Send initial ping
            socket.send(JSON.stringify({
                type: 'ping'
            }));
        };

        socket.onmessage = function(event) {
            try {
                const data = JSON.parse(event.data);
                
                // Handle different message types
                switch (data.type) {
                    case 'connection':
                        console.log('WebSocket Connection Info:', data.message);
                        break;
                    
                    case 'audit_update':
                        const newAudits = data.audits;
                        const auditTable = $('#auditTable').DataTable();

                        newAudits.forEach(audit => {
                            // Prepend new audit entry to the table
                            const formattedTimestamp = moment(audit.action_timestamp).format('YYYY-MM-DD HH:mm:ss');
                            const actionBadge = `<span class="badge bg-primary">${audit.action}</span>`;
                            
                            let detailsHtml = '';
                            try {
                                const parsedDetails = JSON.parse(audit.details || '{}');
                                detailsHtml = `<pre class="mb-0 small" style="max-height: 100px; overflow-y: auto; white-space: pre-wrap;">
                                    ${JSON.stringify(parsedDetails, null, 2)}
                                </pre>`;
                            } catch(e) {
                                detailsHtml = audit.details || '';
                            }

                            auditTable.row.add([
                                formattedTimestamp,
                                audit.username,
                                actionBadge,
                                detailsHtml
                            ]).draw(false);
                        });
                        break;
                }
            } catch (error) {
                console.error('Error parsing WebSocket message:', error);
            }
        };

        socket.onclose = function(event) {
            console.log('WebSocket connection closed');
            
            // Attempt to reconnect
            if (reconnectAttempts < MAX_RECONNECT_ATTEMPTS) {
                reconnectAttempts++;
                const timeout = Math.pow(2, reconnectAttempts) * 1000; // Exponential backoff
                
                console.log(`Attempting to reconnect in ${timeout/1000} seconds...`);
                
                setTimeout(initWebSocket, timeout);
            } else {
                console.error('Max reconnection attempts reached. Please check the WebSocket server.');
                
                // Optional: Show user-friendly notification
                Swal.fire({
                    icon: 'error',
                    title: 'Connection Error',
                    text: 'Unable to connect to real-time updates. Please refresh the page or contact support.',
                    confirmButtonText: 'Reload Page',
                    allowOutsideClick: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        location.reload();
                    }
                });
            }
        };

        socket.onerror = function(error) {
            console.error('WebSocket Error:', error);
        };
        */
    }

    // Initialize WebSocket connection (now a no-op)
    initWebSocket();

    // Only initialize if not already initialized
    if ($.fn.DataTable.isDataTable('#auditTable')) {
        return;
    }

    // Initialize DataTable with proper configuration
    var auditTable = $('#auditTable').DataTable({
        processing: true,
        serverSide: false,
        ajax: {
            url: '../backend/fetch_audit_trail.php',
            type: 'GET',
            dataSrc: 'data'
        },
        columns: [
            { 
                data: 'timestamp',
                className: 'text-center',
                render: function(data) {
                    return moment(data).format('YYYY-MM-DD HH:mm:ss');
                }
            },
            { 
                data: 'username',
                className: 'text-center'
            },
            { 
                data: 'action',
                className: 'text-center',
                render: function(data) {
                    return `<span class="badge bg-primary">${data}</span>`;
                }
            },
            { 
                data: 'details',
                render: function(data) {
                    if (!data) return '';
                    try {
                        const parsed = JSON.parse(data);
                        return `<pre class="mb-0 small" style="max-height: 100px; overflow-y: auto; white-space: pre-wrap;">
                                ${JSON.stringify(parsed, null, 2)}
                               </pre>`;
                    } catch(e) {
                        return data;
                    }
                }
            }
        ],
        order: [[0, 'desc']],
        responsive: true,
        dom: '<"row mb-3"<"col-md-6"B><"col-md-6"f>>' +
             '<"row"<"col-md-12"tr>>' +
             '<"row mt-3"<"col-md-4"l><"col-md-4 text-center"i><"col-md-4"p>>',
        buttons: [
            {
                extend: 'collection',
                text: '<i class="fas fa-download me-1"></i>Export',
                className: 'btn btn-primary',
                buttons: [
                    {
                        extend: 'copy',
                        className: 'btn btn-light',
                        text: '<i class="fas fa-copy me-1"></i>Copy'
                    },
                    {
                        extend: 'csv',
                        className: 'btn btn-light',
                        text: '<i class="fas fa-file-csv me-1"></i>CSV'
                    },
                    {
                        extend: 'excel',
                        className: 'btn btn-light',
                        text: '<i class="fas fa-file-excel me-1"></i>Excel'
                    },
                    {
                        extend: 'pdf',
                        className: 'btn btn-light',
                        text: '<i class="fas fa-file-pdf me-1"></i>PDF'
                    },
                    {
                        extend: 'print',
                        className: 'btn btn-light',
                        text: '<i class="fas fa-print me-1"></i>Print'
                    }
                ]
            }
        ],
        displayStart: 0,
        pageLength: 5,
        lengthMenu: [[5, 10, 25, 50, 100, -1], [5, 10, 25, 50, 100, "All"]],
        language: {
            paginate: {
                first: '<i class="fas fa-angle-double-left"></i>',
                previous: '<i class="fas fa-angle-left"></i>',
                next: '<i class="fas fa-angle-right"></i>',
                last: '<i class="fas fa-angle-double-right"></i>'
            },
            lengthMenu: "_MENU_ entries per page",
            info: "Showing _START_ to _END_ of _TOTAL_ entries",
            search: "<i class='fas fa-search me-1'></i>Search:",
            processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>'
        },
        drawCallback: function(settings) {
            // Add Bootstrap classes to pagination
            const pagination = $(this).closest('.dataTables_wrapper').find('.dataTables_paginate');
            pagination.addClass('pagination-sm');
            pagination.find('.paginate_button').addClass('px-3 py-2');
            pagination.find('.current').addClass('bg-primary text-white');
            
            // Ensure 5 is selected by default
            const lengthSelect = $(this).closest('.dataTables_wrapper').find('.dataTables_length select');
            if (lengthSelect.val() !== '5') {
                lengthSelect.val('5').trigger('change');
            }
        },
        initComplete: function(settings, json) {
            // Set initial page length to 5
            this.api().page.len(5).draw();
        }
    });

    // Register the table in the global tables object
    if (window.tables) {
        window.tables.auditTable = auditTable;
    }

    // Function to refresh audit trail data
    function refreshAuditTrailData() {
        fetch('../backend/audit_trail.php?action=get_audit_trails')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const auditTable = document.querySelector('#auditTable');
                    if (auditTable && $.fn.DataTable.isDataTable('#auditTable')) {
                        const dataTable = $(auditTable).DataTable();
                        dataTable.ajax.reload(null, false);
                    }
                }
            })
            .catch(error => console.error('Error refreshing audit trail:', error));
    }

    // Handle filter form submission
    $('#auditFilterForm').on('submit', function(e) {
        e.preventDefault();
        
        // Show loading indicator
        $('#auditTable').addClass('loading');
        
        // Get filter values
        var filters = $(this).serializeArray().reduce(function(obj, item) {
            obj[item.name] = item.value;
            return obj;
        }, {});

        // Reload table with filters
        auditTable.ajax.url('../backend/fetch_audit_trail.php?' + $.param(filters)).load(function() {
            $('#auditTable').removeClass('loading');
        });
    });

    // Add loading indicator styles
    $('<style>')
        .text(`
            .loading {
                position: relative;
                pointer-events: none;
            }
            .loading:after {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(255,255,255,0.8) url('../../assets/img/loading.gif') center no-repeat;
                background-size: 50px;
                z-index: 1;
            }
        `)
        .appendTo('head');
});
