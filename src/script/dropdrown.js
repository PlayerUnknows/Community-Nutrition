$(document).ready(function() {
    // Initialize dropdown using Bootstrap's data API
    const profileDropdownEl = document.getElementById('profileDropdown');
    if (profileDropdownEl) {
        new bootstrap.Dropdown(profileDropdownEl);
    }

    // Handle tab changes
    $(document).on('show.bs.tab', function(e) {
        // Ensure dropdown is initialized after tab change
        if (profileDropdownEl) {
            const dropdown = bootstrap.Dropdown.getInstance(profileDropdownEl);
            if (!dropdown) {
                new bootstrap.Dropdown(profileDropdownEl);
            }
        }

        if ($(e.target).attr('id') === 'event-tab') {
            // Immediate initialization for event tab
            if (profileDropdownEl) {
                const dropdown = bootstrap.Dropdown.getInstance(profileDropdownEl);
                if (dropdown) {
                    dropdown.dispose();
                }
                new bootstrap.Dropdown(profileDropdownEl);
            }
        }
    });

    // Additional initialization after tab is fully shown
    $(document).on('shown.bs.tab', '#event-tab, #appointments-tab, #monitoring-tab, #audit-tab, #acc-reg', function() {
        setTimeout(function() {
            if (profileDropdownEl) {
                const dropdown = bootstrap.Dropdown.getInstance(profileDropdownEl);
                if (dropdown) {
                    dropdown.dispose();
                }
                new bootstrap.Dropdown(profileDropdownEl);
            }
        }, 100);
    });

    // Handle profile button click
    $(document).on('click', '#profileButton', function(e) {
        e.preventDefault();
        const dropdown = bootstrap.Dropdown.getInstance(profileDropdownEl);
        if (dropdown) {
            dropdown.hide();
        }
        Swal.fire({
            title: "Profile",
            text: "Profile settings coming soon",
            icon: "info"
        });
    });

    // Handle settings button click
    $(document).on('click', '#settingsButton', function(e) {
        e.preventDefault();
        const dropdown = bootstrap.Dropdown.getInstance(profileDropdownEl);
        if (dropdown) {
            dropdown.hide();
        }
        Swal.fire({
            title: "Settings",
            text: "System settings coming soon",
            icon: "info"
        });
    });

    // Close dropdown when clicking outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.profile-dropdown').length) {
            const dropdown = bootstrap.Dropdown.getInstance(profileDropdownEl);
            if (dropdown) {
                dropdown.hide();
            }
        }
    });

    // Handle any dynamic content loading
    $(document).ajaxComplete(function() {
        setTimeout(function() {
            if (profileDropdownEl) {
                const dropdown = bootstrap.Dropdown.getInstance(profileDropdownEl);
                if (dropdown) {
                    dropdown.dispose();
                }
                new bootstrap.Dropdown(profileDropdownEl);
            }
        }, 100);
    });

    // Initialize dropdown when clicking event tab directly
    $('#event-tab').on('click', function() {
        if (profileDropdownEl) {
            const dropdown = bootstrap.Dropdown.getInstance(profileDropdownEl);
            if (dropdown) {
                dropdown.dispose();
            }
            new bootstrap.Dropdown(profileDropdownEl);
        }
    });
});