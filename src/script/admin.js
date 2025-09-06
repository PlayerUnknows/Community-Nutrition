// Admin page initialization
(function () {
  // Global table references
  const tableInstances = new Map();

  // Wait for dependencies
  const waitForDependencies = (callback) => {
    if (
      typeof jQuery === "undefined" ||
      typeof $.fn === "undefined" ||
      typeof $.fn.DataTable === "undefined"
    ) {
      setTimeout(() => waitForDependencies(callback), 100);
      return;
    }
    callback(jQuery);
  };

  // Check if table is already initialized
  const isTableInitialized = (tableId) => {
    return $.fn.DataTable.isDataTable(tableId) || tableInstances.has(tableId);
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
    if (
      currentTarget === "#schedule" &&
      window.MonitoringModule &&
      typeof window.MonitoringModule.adjustTable === "function"
    ) {
      MonitoringModule.adjustTable();
    }
  };

  // Initialize event handlers
  const initializeEventHandlers = ($) => {
    // Handle tab changes
    $('button[data-bs-toggle="tab"]')
      .off("shown.bs.tab")
      .on("shown.bs.tab", function (e) {
        const target = $(e.target).attr("data-bs-target");

        // Only proceed with table adjustments for relevant tabs
        if (target === "#schedule") {
          setTimeout(() => {
            $("#schedule .sub-content").hide();
            $("#monitoring-records").show();
            adjustTables(target);
          }, 200);
        }

        // Ensure any open modals are properly hidden before switching tabs
        $(".modal").modal("hide");
      });

    // Ensure modals are properly disposed when hidden
    $(".modal").on("hidden.bs.modal", function () {
      $(this).data("bs.modal", null);
    });

    // Handle sub-navigation for Nutrition Monitoring
    $("#monitoring-container .sub-nav-button")
      .off("click")
      .on("click", function (e) {
        e.preventDefault();
        const target = $(this).data("target");

        // Hide any open modals before showing new content
        $(".modal").modal("hide");

        // First, switch to the Nutrition Monitoring tab if not already active
        const scheduleTab = $("#schedule-tab");
        if (!scheduleTab.hasClass("active")) {
          scheduleTab.tab("show");
        }

        // Hide all sub-content first
        $("#schedule .sub-content").hide();
        
        // Show the clicked content
        $(`#${target}`).show();
        
        // If it's the overall report, initialize it
        if (target === "overall-report" && typeof window.generateOPTPlusReport === "function") {
          try {
            console.log("Initializing overall report"); // Debug
            window.generateOPTPlusReport();
          } catch (error) {
            console.error("Error initializing overall report:", error);
          }
        }
      });

    // Direct access to Overall Report via URL hash
    if (window.location.hash === "#overall-report") {
      setTimeout(() => {
        // First activate the schedule tab
        $('#schedule-tab').tab('show');
        
        // Then show the overall report content
        $("#schedule .sub-content").hide();
        $("#overall-report").show();
        
        // Initialize the report if the function exists
        if (typeof window.generateOPTPlusReport === "function") {
          window.generateOPTPlusReport();
        }
      }, 500);
    }

    // Show monitoring records by default
    $("#schedule-tab")
      .off("click shown.bs.tab")
      .on("click shown.bs.tab", function () {
        // Hide any open modals
        $(".modal").modal("hide");
        $("#schedule .sub-content").hide();
        $("#monitoring-records").show();
      });

    // Account Registration handling
    $("#acc-reg")
      .off("click shown.bs.tab")
      .on("click shown.bs.tab", function () {
        // Hide any open modals
        $(".modal").modal("hide");
        $(".sub-content").hide();
        $("#signupFormContainer").show();
      });

    // Initialize with signup form visible if account tab is active
    if ($("#acc-reg").hasClass("active")) {
      $(".sub-content").hide();
      $("#signupFormContainer").show();
    }

    // Sub-nav hover effects
    $("#monitoring-container")
      .off("mouseenter mouseleave")
      .hover(
        function () {
          $(this).find(".sub-nav").show();
        },
        function () {
          $(this).find(".sub-nav").hide();
        }
      );

    // Account Registration sub-navigation
    $("#acc-reg-container .sub-nav-button")
      .off("click")
      .on("click", function (e) {
        e.preventDefault();
        const target = $(this).data("target");

        // Hide any open modals
        $(".modal").modal("hide");

        // First, switch to the Create Account tab if not already active
        const accountTab = $("#acc-reg");
        if (!accountTab.hasClass("active")) {
          accountTab.tab("show");
        }

        $(".sub-content").hide();
        $(`#${target}`).show();

        if (target === "view-users" && typeof loadUsers === "function") {
          loadUsers();
        }
      });

    // Initialize toast
    const Toast = Swal.mixin({
      toast: true,
      position: "top-end",
      showConfirmButton: false,
      timer: 3000,
      timerProgressBar: true,
      didOpen: (toast) => {
        toast.addEventListener("mouseenter", Swal.stopTimer);
        toast.addEventListener("mouseleave", Swal.resumeTimer);
      },
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
      }
    } catch (error) {
      console.error("Error during initialization:", error);
    }
  };

  // Start initialization when dependencies are ready
  waitForDependencies(initialize);
})();
