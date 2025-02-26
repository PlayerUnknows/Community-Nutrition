// Admin page initialization
(function() {
    // Global table references
    const tableInstances = new Map();
    
    // Wait for dependencies
    const waitForDependencies = (callback) => {
        if (typeof jQuery === 'undefined' || 
            typeof $.fn === 'undefined' || 
            typeof $.fn.DataTable === 'undefined') {
            setTimeout(() => waitForDependencies(callback), 100);
            return;
        }
        callback(jQuery);
    };

    // Check if table is already initialized
    const isTableInitialized = (tableId) => {
        return $.fn.DataTable.isDataTable(tableId) || tableInstances.has(tableId);
    };

    // Initialize a single table
    const initializeTable = ($, tableId, options = {}) => {
        try {
            const table = $(tableId);
            if (!table.length) return null;

            // Skip if already initialized
            if (isTableInitialized(tableId)) {
                return $(tableId).DataTable();
            }

            // Default options
            const defaultOptions = {
                responsive: true,
                pageLength: 10,
                lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, "All"]]
            };

            // Create new instance
            const instance = table.DataTable({
                ...defaultOptions,
                ...options
            });

            // Store instance reference
            tableInstances.set(tableId, instance);
            return instance;

        } catch (err) {
            console.warn(`Error initializing table ${tableId}:`, err);
            return null;
        }
    };

    // Initialize tables
    const initializeTables = ($) => {
        // No tables should be initialized here
        return;
    };

    // Only adjust tables that exist and are relevant to the current tab
    const adjustTables = (currentTarget) => {
        // Only adjust monitoring table if we're switching to the schedule tab
        // and if MonitoringModule exists and has the adjustTable function
        if (currentTarget === '#schedule' && 
            window.MonitoringModule && 
            typeof window.MonitoringModule.adjustTable === 'function') {
            MonitoringModule.adjustTable();
        }
    };

    // Initialize event handlers
    const initializeEventHandlers = ($) => {
        // Handle tab changes
        $('button[data-bs-toggle="tab"]').off('shown.bs.tab').on('shown.bs.tab', function(e) {
            const target = $(e.target).attr('data-bs-target');
            
            // Only proceed with table adjustments for relevant tabs
            if (target === '#schedule') {
                setTimeout(() => {
                    $('#schedule .sub-content').hide();
                    $('#monitoring-records').show();
                    adjustTables(target);
                }, 200);
            }

            // Ensure any open modals are properly hidden before switching tabs
            $('.modal').modal('hide');
        });

        // Ensure modals are properly disposed when hidden
        $('.modal').on('hidden.bs.modal', function() {
            $(this).data('bs.modal', null);
        });

        // Handle sub-navigation for Nutrition Monitoring
        $('#monitoring-container .sub-nav-button').off('click').on('click', function(e) {
            e.preventDefault();
            const target = $(this).data('target');
            
            // Hide any open modals before showing new content
            $('.modal').modal('hide');
            
            $('#schedule .sub-content').hide();
            $(`#${target}`).show();
        });

        // Show monitoring records by default
        $('#schedule-tab').off('click shown.bs.tab').on('click shown.bs.tab', function() {
            // Hide any open modals
            $('.modal').modal('hide');
            $('#schedule .sub-content').hide();
            $('#monitoring-records').show();
        });

        // Account Registration handling
        $('#acc-reg').off('click shown.bs.tab').on('click shown.bs.tab', function() {
            // Hide any open modals
            $('.modal').modal('hide');
            $('.sub-content').hide();
            $('#signupFormContainer').show();
        });

        // Initialize with signup form visible if account tab is active
        if ($('#acc-reg').hasClass('active')) {
            $('.sub-content').hide();
            $('#signupFormContainer').show();
        }

        // Sub-nav hover effects
        $('#monitoring-container').off('mouseenter mouseleave').hover(
            function() { $(this).find('.sub-nav').show(); },
            function() { $(this).find('.sub-nav').hide(); }
        );

        // Account Registration sub-navigation
        $('.sub-nav-button').off('click').on('click', function(e) {
            e.preventDefault();
            const target = $(this).data('target');
            
            // Hide any open modals
            $('.modal').modal('hide');
            
            $('.sub-content').hide();
            $(`#${target}`).show();

            if (target === 'view-users' && typeof loadUsers === 'function') {
                loadUsers();
            }
        });

        // Remove the hover handlers for dropdowns
        $('.nav-item.dropdown').off('mouseenter mouseleave');

        // Add click handler for dropdown toggle
        $('.dropdown-toggle').off('click').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const $dropdown = $(this).next('.dropdown-menu');
            $('.dropdown-menu').not($dropdown).removeClass('show');
            $dropdown.toggleClass('show');
        });

        // Close dropdown when clicking outside
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.dropdown').length) {
                $('.dropdown-menu').removeClass('show');
            }
        });

        // Profile Settings Handler
        $('#profileSettingsBtn').on('click', function(e) {
            e.preventDefault();
            Swal.fire({
                title: 'Profile Settings',
                html: `
                    <form id="profileSettingsForm" class="text-start">
                        <div class="mb-3">
                            <label for="currentEmail" class="form-label">Current Email</label>
                            <input type="email" class="form-control" id="currentEmail" value="${$('#username').text()}" disabled>
                        </div>
                        <div class="mb-3">
                            <label for="newEmail" class="form-label">New Email</label>
                            <input type="email" class="form-control" id="newEmail">
                        </div>
                        <div class="mb-3">
                            <label for="currentPassword" class="form-label">Current Password</label>
                            <input type="password" class="form-control" id="currentPassword">
                        </div>
                        <div class="mb-3">
                            <label for="newPassword" class="form-label">New Password</label>
                            <input type="password" class="form-control" id="newPassword">
                        </div>
                    </form>
                `,
                showCancelButton: true,
                confirmButtonText: 'Save Changes',
                cancelButtonText: 'Cancel',
                width: '500px',
                customClass: {
                    container: 'profile-settings-modal',
                    popup: 'profile-settings-modal'
                },
                preConfirm: () => {
                    // Add validation and submission logic here
                    const newEmail = $('#newEmail').val();
                    const currentPassword = $('#currentPassword').val();
                    const newPassword = $('#newPassword').val();
                    
                    // Add your update profile logic here
                    return { newEmail, currentPassword, newPassword };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    // Handle the form submission
                    // Add your AJAX call to update profile
                }
            });
        });

        // Display Settings Handler
        $('#displaySettingsBtn').on('click', function(e) {
            e.preventDefault();
            Swal.fire({
                title: 'Display Settings',
                html: `
                    <form id="displaySettingsForm" class="text-start">
                        <div class="mb-3">
                            <label class="form-label d-block">Theme</label>
                            <div class="btn-group" role="group">
                                <input type="radio" class="btn-check" name="theme" id="lightTheme" checked>
                                <label class="btn btn-outline-primary" for="lightTheme">Light</label>
                                <input type="radio" class="btn-check" name="theme" id="darkTheme">
                                <label class="btn btn-outline-primary" for="darkTheme">Dark</label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="fontSize" class="form-label">Font Size</label>
                            <select class="form-select" id="fontSize">
                                <option value="small">Small</option>
                                <option value="medium" selected>Medium</option>
                                <option value="large">Large</option>
                            </select>
                        </div>
                    </form>
                `,
                showCancelButton: true,
                confirmButtonText: 'Save Changes',
                cancelButtonText: 'Cancel',
                width: '500px',
                customClass: {
                    container: 'display-settings-modal',
                    popup: 'display-settings-modal'
                },
                preConfirm: () => {
                    const theme = $('#darkTheme').prop('checked') ? 'dark' : 'light';
                    const fontSize = $('#fontSize').val();
                    return { theme, fontSize };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    // Handle the display settings changes
                    // Add your logic to apply theme and font size
                }
            });
        });
    };

    // Main initialization
    const initialize = ($) => {
        try {
            // Only initialize once
            if (!window.adminInitialized) {
                initializeTables($);
                initializeEventHandlers($);
                window.adminInitialized = true;
                console.log('Admin page initialization complete');
            }
        } catch (error) {
            console.error('Error during initialization:', error);
        }
    };

    // Start initialization when dependencies are ready
    waitForDependencies(initialize);
})(); 