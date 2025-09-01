// Create a global namespace for monitoring
window.MonitoringModule = (function () {

  let isInitialized = false;

  // Make showToast globally accessible
  window.showToast = function(message, type = "success") {
    // Create toast container if it doesn't exist
    let toastContainer = document.querySelector(".toast-container");
    if (!toastContainer) {
      toastContainer = document.createElement("div");
      toastContainer.className = "toast-container position-fixed top-0 end-0 p-3";
      toastContainer.style.zIndex = "1060";
      document.body.appendChild(toastContainer);
    }

    // Create toast element
    const toastId = "toast-" + Date.now();
    const toastHtml = `
      <div id="${toastId}" class="toast align-items-center text-white bg-${type === 'error' ? 'danger' : 'success'} border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
          <div class="toast-body">
            ${message}
          </div>
          <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
      </div>
    `;

    toastContainer.insertAdjacentHTML("beforeend", toastHtml);

    // Show the toast
    const toastElement = document.getElementById(toastId);
    const toast = new bootstrap.Toast(toastElement, {
      autohide: true,
      delay: 5000
    });
    toast.show();

    // Remove the toast element after it's hidden
    toastElement.addEventListener("hidden.bs.toast", function() {
      toastElement.remove();
    });
  };

  // Make initializeToastStyles globally accessible
  window.initializeToastStyles = function() {
    // Check if styles are already added
    if (!document.getElementById("monitoring-toast-styles")) {
      const style = document.createElement("style");
      style.id = "monitoring-toast-styles";
      style.textContent = `
          .toast-container {
            z-index: 1060;
          }
          .toast {
            opacity: 1 !important;
            margin-bottom: 1rem;
          }
          .toast.bg-success {
            background-color: #28a745 !important;
          }
          .toast.bg-danger {
            background-color: #dc3545 !important;
          }
        `;
      document.head.appendChild(style);
    }
  }


  // Make setupEventHandlers globally accessible
  window.setupEventHandlers = function() {
    // Add search handler
    $("#monitoringSearch").on("keyup", function () {
      if (window.monitoringTable) {
        window.monitoringTable.search(this.value).draw();
      }
    });

    // Add length change handler
    $("#monitoringPerPage").on("change", function () {
      if (window.monitoringTable) {
        window.monitoringTable.page.len($(this).val()).draw();
      }
    });
  }

  return {
    init: function () {
      if (!isInitialized) {
        window.initializeToastStyles();
        initializeTable();
        window.setupEventHandlers();
        isInitialized = true;
      }
    },
    getTable: function () {
      return window.monitoringTable;
    },
    refreshTable: function () {
      if (window.monitoringTable) {
        window.monitoringTable.ajax.reload(null, false);
      }
    },
    destroy: function () {
      if (window.monitoringTable) {
        window.monitoringTable.destroy();
        window.monitoringTable = null;
      }
      isInitialized = false;
    },

  };
})();

// Handle tab events
$(document).ready(function () {
  // Initialize when monitoring tab is shown
  $('button[data-bs-toggle="tab"]').on("shown.bs.tab", function (e) {
    if ($(e.target).attr("id") === "schedule-tab") {
      MonitoringModule.init();
    }
  });
});
