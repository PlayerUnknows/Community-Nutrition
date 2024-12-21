$(document).ready(function() {
    // WebSocket connection configuration
    const WS_URL = 'ws://localhost:8080';
    let socket = null;
    let reconnectAttempts = 0;
    const MAX_RECONNECT_ATTEMPTS = 5;
    let initializeAttempts = 0;
    const MAX_INITIALIZE_ATTEMPTS = 30; // 3 seconds max wait time
    let auditTableInitialized = false;

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

    // Function to check if DataTable is available
    function isDataTableAvailable() {
        return typeof $.fn.DataTable !== 'undefined' && typeof $.fn.DataTable.ext !== 'undefined';
    }

    // Function to initialize or reinitialize audit table with retry logic
    function initializeAuditTable() {
        if (initializeAttempts >= MAX_INITIALIZE_ATTEMPTS) {
            console.error('Failed to initialize DataTable after maximum attempts');
            return;
        }

        // Check if DataTables is loaded
        if (!isDataTableAvailable()) {
            console.warn('DataTables not loaded yet. Waiting... (Attempt ' + (initializeAttempts + 1) + '/' + MAX_INITIALIZE_ATTEMPTS + ')');
            initializeAttempts++;
            setTimeout(initializeAuditTable, 100);
            return;
        }

        var table = $('#auditTable');
        if (!table.length) {
            return; // Table doesn't exist in DOM yet
        }

        try {
            // Destroy existing DataTable if it exists
            if ($.fn.DataTable.isDataTable(table)) {
                table.DataTable().destroy();
            }

            // Initialize DataTable with configurations
            table.DataTable({
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
                            return '<span class="badge bg-primary">' + data + '</span>';
                        }
                    },
                    { 
                        data: 'details',
                        className: 'text-start',
                        render: function(data) {
                            try {
                                const parsedDetails = JSON.parse(data || '{}');
                                return '<pre class="mb-0 small" style="max-height: 100px; overflow-y: auto; white-space: pre-wrap;">' +
                                    JSON.stringify(parsedDetails, null, 2) +
                                    '</pre>';
                            } catch(e) {
                                return data || '';
                            }
                        }
                    }
                ],
                order: [[0, 'desc']],
                pageLength: 10,
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
                responsive: true,
                language: {
                    emptyTable: "No audit records found",
                    processing: "Loading audit records..."
                }
            });

            console.log('Audit table initialized successfully');
            auditTableInitialized = true;
            initializeAttempts = 0; // Reset counter on successful initialization
            
        } catch (error) {
            console.error('Error initializing audit table:', error);
            initializeAttempts++;
            setTimeout(initializeAuditTable, 100);
        }
    }

    // Initialize table when audit tab is shown
    $(document).on('shown.bs.tab', 'button[data-bs-toggle="tab"]', function(e) {
        if ($(e.target).attr('id') === 'audit-tab') {
            setTimeout(function() {
                if (!auditTableInitialized) {
                    initializeAuditTable();
                }
            }, 100);
        }
    });

    // Also initialize if we're already on the audit tab
    if ($('#audit-tab').hasClass('active')) {
        setTimeout(initializeAuditTable, 100);
    }

    // Handle AJAX content loading
    $(document).ajaxComplete(function(event, xhr, settings) {
        if (settings.url && settings.url.includes('audit_trail.php')) {
            setTimeout(function() {
                if (!auditTableInitialized) {
                    initializeAuditTable();
                }
            }, 100);
        }
    });

    // Initialize WebSocket connection (now a no-op)
    initWebSocket();

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
        var auditTable = $('#auditTable').DataTable();
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
