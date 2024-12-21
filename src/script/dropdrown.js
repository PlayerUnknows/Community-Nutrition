$(document).ready(function () {
    // Global dropdown initialization function
    window.initializeDropdowns = function() {
        const dropdowns = document.querySelectorAll('[data-bs-toggle="dropdown"]');
        dropdowns.forEach(dropdown => {
            // Dispose existing dropdown if any
            const existingInstance = bootstrap.Dropdown.getInstance(dropdown);
            if (existingInstance) {
                existingInstance.dispose();
            }
            // Create new dropdown instance
            new bootstrap.Dropdown(dropdown);
        });
    };

    // Robust profile dropdown reinitialization
    function reinitializeProfileDropdown() {
        const dropdownEl = document.getElementById('profileDropdown');
        if (!dropdownEl) return null;

        // Clean up existing instance
        const oldDropdown = bootstrap.Dropdown.getInstance(dropdownEl);
        if (oldDropdown) {
            try {
                oldDropdown.dispose();
            } catch (error) {
                console.warn('Error disposing dropdown:', error);
            }
        }

        // Create and return new instance
        try {
            return new bootstrap.Dropdown(dropdownEl);
        } catch (error) {
            console.error('Failed to reinitialize dropdown:', error);
            return null;
        }
    }

    // Initialize on page load
    let profileDropdown = reinitializeProfileDropdown();

    // Handle tab changes with more robust reinitialization
    $(document).on('shown.bs.tab', 'button[data-bs-toggle="tab"]', function () {
        // Ensure any existing dropdown is properly cleaned up
        if (profileDropdown) {
            try {
                profileDropdown.dispose();
            } catch {}
        }
        
        // Reinitialize with a slight delay to ensure DOM is stable
        setTimeout(() => {
            profileDropdown = reinitializeProfileDropdown();
        }, 200);
    });

    // Handle Create Account tab with additional safeguards
    $('#acc-reg').on('click', function() {
        // Force dropdown cleanup and reinit with longer delay
        setTimeout(() => {
            if (profileDropdown) {
                try {
                    profileDropdown.dispose();
                } catch {}
            }
            profileDropdown = reinitializeProfileDropdown();
        }, 250);
    });

    // Profile and settings actions
    $(document).on('click', '#profileButton', function(e) {
        e.preventDefault();
        if (profileDropdown) {
            try {
                profileDropdown.hide();
            } catch {}
        }
        Swal.fire({
            title: "Profile",
            text: "Profile settings coming soon",
            icon: "info"
        });
    });

    $(document).on('click', '#settingsButton', function(e) {
        e.preventDefault();
        if (profileDropdown) {
            try {
                profileDropdown.hide();
            } catch {}
        }
        Swal.fire({
            title: "Settings",
            text: "System settings coming soon",
            icon: "info"
        });
    });

    // Reinitialize after any AJAX call with error handling
    $(document).ajaxComplete(function() {
        setTimeout(() => {
            if (profileDropdown) {
                try {
                    profileDropdown.dispose();
                } catch {}
            }
            profileDropdown = reinitializeProfileDropdown();
        }, 200);
    });

    // Fallback global dropdown initialization
    window.initializeDropdowns();

    function initializeDropdowns() {
        // Initialize all dropdowns
        var dropdownElementList = [].slice.call(document.querySelectorAll('[data-bs-toggle="dropdown"]'));
        dropdownElementList.map(function (dropdownToggleEl) {
            return new bootstrap.Dropdown(dropdownToggleEl);
        });

        // Handle dropdown item clicks
        $('.dropdown-item').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation(); // Prevent event bubbling
            var targetId = $(this).data('target');
            if (targetId) {
                $('.sub-content').hide();
                $('#' + targetId).show();
            }
        });
    }

    // Initial initialization
    initializeDropdowns();

    // Reinitialize dropdowns after AJAX content is loaded
    $(document).ajaxComplete(function() {
        initializeDropdowns();
    });

    // Handle manual dropdown toggle
    $(document).on('click', '[data-bs-toggle="dropdown"]', function(e) {
        e.preventDefault();
        var dropdown = bootstrap.Dropdown.getInstance(this);
        if (!dropdown) {
            dropdown = new bootstrap.Dropdown(this);
        }
        dropdown.toggle();
    });
});