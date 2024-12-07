$(document).ready(function() {
    // Only initialize if not already initialized
    if ($.fn.DataTable.isDataTable('#auditTable')) {
        return;
    }

    // Initialize DataTable with proper configuration
    var auditTable = $('#auditTable').DataTable({
        processing: true,
        serverSide: false,
        ajax: {
            url: '../backend/fetch_audit_trail.php', // Fix the path
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
        dom: '<"d-flex justify-content-between align-items-center mb-3"Bf>rt<"d-flex justify-content-between align-items-center"lip>',
        buttons: [
            {
                extend: 'collection',
                text: 'Export',
                buttons: ['copy', 'csv', 'excel', 'pdf', 'print']
            }
        ],
        pageLength: 10,
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]]
    });

    // Register the table in the global tables object
    if (window.tables) {
        window.tables.auditTable = auditTable;
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
