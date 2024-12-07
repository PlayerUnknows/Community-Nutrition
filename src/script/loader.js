$(document).ready(function () {
    // Function to show the loading overlay
    function showLoadingOverlay() {
        // Remove any existing overlay
        $('#loading-overlay').remove();

        // Add a new overlay
        $('body').prepend(`
            <div id="loading-overlay" style="
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 2px;
                background: linear-gradient(to left, #007bff 0%, #007bff 50%, #fff 50%, #fff 100%);
                background-size: 200% 100%;
                background-position: 0% 0%;
                transition: background-position 1s ease-in;
                z-index: 9999;
            "></div>
        `);

        // Start the animation
        setTimeout(() => {
            $('#loading-overlay').css('background-position', '100% 0%');
        }, 10); // Small delay to trigger animation
    }

    // Function to handle navigation with loading effect
    function navigateWithLoading(targetUrl) {
        showLoadingOverlay();

        // Allow the animation to finish before removing the overlay
        setTimeout(() => {
            // Remove the overlay and redirect
            $('#loading-overlay').remove();
            window.location.href = targetUrl;
        }, 1100); // Slightly longer than the animation duration (1000ms)
    }

    // Bind navigation logic to links
    $('.nav-link').click(function (e) {
        e.preventDefault();
        const targetUrl = $(this).attr('href');

        if (targetUrl) {
            navigateWithLoading(targetUrl);
        }
    });

    // Handle back button with the loading effect
    $('.back-button').click(function (e) {
        e.preventDefault();
        showLoadingOverlay();

        // Delay and then go back to the previous page
        setTimeout(() => {
            // Remove the overlay and go back
            $('#loading-overlay').remove();
            window.history.back();
        }, 1100); // Slightly longer than the animation duration (1000ms)
    });
});
