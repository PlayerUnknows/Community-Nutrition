// Make initializeDropdowns available globally
window.initializeDropdowns = function() {
    const dropdowns = document.querySelectorAll('[data-bs-toggle="dropdown"]');
    dropdowns.forEach(dropdown => {
        // Dispose existing dropdown if any
        const instance = bootstrap.Dropdown.getInstance(dropdown);
        if (instance) {
            instance.dispose();
        }
        // Create new dropdown instance
        new bootstrap.Dropdown(dropdown);
    });
};

$(document).ready(function () {
    // Function to reinitialize profile dropdown
    function reinitializeProfileDropdown() {
        const dropdownEl = document.getElementById('profileDropdown');
        if (!dropdownEl) return;

        // Clean up existing instance
        const oldDropdown = bootstrap.Dropdown.getInstance(dropdownEl);
        if (oldDropdown) {
            oldDropdown.dispose();
        }

        // Create new instance
        return new bootstrap.Dropdown(dropdownEl);
    }

    // Initialize on page load
    let profileDropdown = reinitializeProfileDropdown();

    // Handle tab changes
    $(document).on('shown.bs.tab', 'button[data-bs-toggle="tab"]', function () {
        profileDropdown = reinitializeProfileDropdown();
    });

    // Handle Create Account tab specifically
    $('#acc-reg').on('click', function() {
        // Force dropdown cleanup and reinit after a short delay
        setTimeout(() => {
            profileDropdown = reinitializeProfileDropdown();
        }, 150);
    });

    // Handle profile actions with proper dropdown control
    $(document).on('click', '#profileButton', function(e) {
        e.preventDefault();
        if (profileDropdown) {
            profileDropdown.hide();
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
            profileDropdown.hide();
        }
        Swal.fire({
            title: "Settings",
            text: "System settings coming soon",
            icon: "info"
        });
    });

    // Reinitialize after any AJAX call
    $(document).ajaxComplete(function() {
        setTimeout(() => {
            profileDropdown = reinitializeProfileDropdown();
        }, 150);
    });
});
