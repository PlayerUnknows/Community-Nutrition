// // Function to show the loading overlay
// function showLoadingOverlay() {
//     // Remove any existing overlay
//     const existingOverlay = document.getElementById('loading-overlay');
//     if (existingOverlay) {
//         existingOverlay.remove();
//     }

//     // Add a new overlay
//     const overlay = document.createElement('div');
//     overlay.id = 'loading-overlay';
//     overlay.style.cssText = `
//         position: fixed;
//         top: 0;
//         left: 0;
//         width: 100%;
//         height: 100%;
//         background: rgba(255, 255, 255, 0.9);
//         display: flex;
//         justify-content: center;
//         align-items: center;
//         z-index: 9999;
//     `;

//     const spinner = document.createElement('div');
//     spinner.className = 'spinner';
//     spinner.style.cssText = `
//         width: 50px;
//         height: 50px;
//         border: 5px solid #f3f3f3;
//         border-top: 5px solid #007bff;
//         border-radius: 50%;
//         animation: spin 1s linear infinite;
//     `;

//     overlay.appendChild(spinner);
//     document.body.prepend(overlay);
// }

// // Function to hide the loading overlay
// function hideLoadingOverlay() {
//     const overlay = document.getElementById('loading-overlay');
//     if (overlay) {
//         overlay.style.opacity = '0';
//         overlay.style.transition = 'opacity 300ms';
//         setTimeout(() => overlay.remove(), 300);
//     }
// }

// // Add the spin animation to your existing CSS
// if (!document.getElementById('spin-animation')) {
//     const style = document.createElement('style');
//     style.id = 'spin-animation';
//     style.textContent = `
//         @keyframes spin {
//             0% { transform: rotate(0deg); }
//             100% { transform: rotate(360deg); }
//         }
//     `;
//     document.head.appendChild(style);
// }

// // Function to check if this is first load
// function checkFirstLoad() {
//     if (!window.sessionStorage.getItem('pageLoaded')) {
//         window.sessionStorage.setItem('pageLoaded', 'true');
//         return true;
//     }
//     return false;
// }

// // Only show loading overlay on first load
// document.addEventListener('DOMContentLoaded', function() {
//     if (checkFirstLoad()) {
//         showLoadingOverlay();
//         window.addEventListener('load', function() {
//             setTimeout(hideLoadingOverlay, 500);
//         });
//     }

//     // Add event listeners for tab switching
//     const tabButtons = document.querySelectorAll('[data-bs-toggle="tab"]');
//     tabButtons.forEach(button => {
//         button.addEventListener('click', function() {
//             const targetId = this.getAttribute('data-bs-target');
//             const targetPane = document.querySelector(targetId);
            
//             if (targetPane) {
//                 // Show loading only for specific tabs that need dynamic content
//                 if (targetId === '#account' || targetId === '#viewer') {
//                     showLoadingOverlay();
//                     setTimeout(hideLoadingOverlay, 1000); // Adjust timeout as needed
//                 }
//             }
//         });
//     });

//     // Add event listeners for sub-nav buttons
//     const subNavButtons = document.querySelectorAll('.sub-nav-button');
//     subNavButtons.forEach(button => {
//         button.addEventListener('click', function() {
//             showLoadingOverlay();
//             setTimeout(hideLoadingOverlay, 1000); // Adjust timeout as needed
//         });
//     });
// });

// // Export functions for use in other scripts
// window.showLoadingOverlay = showLoadingOverlay;
// window.hideLoadingOverlay = hideLoadingOverlay;
// window.clearLoadingState = function() {
//     window.sessionStorage.removeItem('pageLoaded');
// };
