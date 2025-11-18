document.addEventListener("DOMContentLoaded", function () {
  let bmiTable;
  let bmiChart = null;
  let femaleBmiChart = null;
  let maleBmiChart = null;
  let toastContainer = null;

  // Register the plugin globally
  Chart.register(ChartDataLabels);

  // Initialize date ranges
  const dateRanges = {
    overall: { start: null, end: null },
    female: { start: null, end: null },
    male: { start: null, end: null },
  };

  // Function to filter data by date range
  const filterDataByDateRange = (data, startDate, endDate) => {
    if (!startDate || !endDate) return data;
    return data.filter((record) => {
      const recordDate = moment(record.checkup_date);
      return recordDate.isBetween(startDate, endDate, "day", "[]");
    });
  };

  // Function to fetch BMI data with date range
  const fetchBMIData = (startDate, endDate, callback) => {
    $.ajax({
      url: "../controllers/BMIController.php",
      type: "POST",
      data: {
        action: "getBMIDetails",
        startDate: startDate,
        endDate: endDate,
      },
      success: function (response) {
        try {
          const parsedResponse =
            typeof response === "string" ? JSON.parse(response) : response;

          if (
            parsedResponse.status === "success" &&
            Array.isArray(parsedResponse.data)
          ) {
            // Filter the data based on the date range
            const filteredData = parsedResponse.data.filter((record) => {
              const recordDate = moment(record.checkup_date);
              return recordDate.isBetween(
                moment(startDate),
                moment(endDate),
                "day",
                "[]"
              );
            });
            callback(filteredData);
          } else {
            callback([]);
          }
        } catch (error) {
          callback([]);
        }
      },
      error: function (xhr, error, thrown) {
        callback([]);
      },
    });
  };

  // Initialize toast container for notifications
  const initializeToastContainer = () => {
    if (document.getElementById("toastContainer")) {
      return;
    }

    toastContainer = document.createElement("div");
    toastContainer.id = "toastContainer";
    toastContainer.className =
      "toast-container position-fixed bottom-0 end-0 p-3";
    toastContainer.style.zIndex = "5000";
    document.body.appendChild(toastContainer);
  };

  // Show toast notification
  const showToast = (message) => {
    if (!toastContainer) {
      initializeToastContainer();
    }

    const toastId = "toast-" + Date.now();
    const toastHtml = `
      <div id="${toastId}" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header">
          <strong class="me-auto">BMI Statistics</strong>
          <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body">
          ${message}
        </div>
      </div>
    `;

    toastContainer.insertAdjacentHTML("beforeend", toastHtml);
    const toastElement = document.getElementById(toastId);
    const toast = new bootstrap.Toast(toastElement, { delay: 5000 });

    toastElement.addEventListener("hidden.bs.toast", () => {
      toastElement.remove();
    });

    toast.show();
  };

  // Function to check if Chart.js is loaded
  const isChartJsLoaded = () => {
    return typeof Chart !== "undefined" && Chart.register;
  };

  // Initialize date range pickers
  const initDateRangePickers = () => {
    // Set default dates to yesterday
    const yesterday = moment().subtract(1, "days");
    const defaultStartDate = yesterday.clone().startOf("day");
    const defaultEndDate = yesterday.clone().endOf("day");

    const dateRangeConfig = {
      autoUpdateInput: true,
      startDate: defaultStartDate,
      endDate: defaultEndDate,
      locale: {
        cancelLabel: "Clear",
        format: "MM/DD/YYYY",
      },
      ranges: {
        Today: [moment(), moment()],
        Yesterday: [moment().subtract(1, "days"), moment().subtract(1, "days")],
        "Last 7 Days": [moment().subtract(6, "days"), moment()],
        "Last 30 Days": [moment().subtract(29, "days"), moment()],
        "This Month": [moment().startOf("month"), moment().endOf("month")],
        "Last Month": [
          moment().subtract(1, "month").startOf("month"),
          moment().subtract(1, "month").endOf("month"),
        ],
      },
    };

    // Initialize only the main date picker
    $("#bmiDateRange").daterangepicker(dateRangeConfig);

    // Set default values
    $("#bmiDateRange").val(
      defaultStartDate.format("MM/DD/YYYY") +
        " - " +
        defaultEndDate.format("MM/DD/YYYY")
    );
    $("#bmiDateRange").data("startDate", defaultStartDate.format("YYYY-MM-DD"));
    $("#bmiDateRange").data("endDate", defaultEndDate.format("YYYY-MM-DD"));

    // Handle date selection
    $("#bmiDateRange").on("apply.daterangepicker", function (ev, picker) {
      const startDate = picker.startDate.format("YYYY-MM-DD");
      const endDate = picker.endDate.format("YYYY-MM-DD");

      $(this).val(
        picker.startDate.format("MM/DD/YYYY") +
          " - " +
          picker.endDate.format("MM/DD/YYYY")
      );
      $(this).data("startDate", startDate);
      $(this).data("endDate", endDate);

      // Fetch and update data
      updateAllData(startDate, endDate);
    });

    // Handle clear
    $("#bmiDateRange").on("cancel.daterangepicker", function (ev, picker) {
      $(this).val("");
      $(this).data("startDate", "");
      $(this).data("endDate", "");

      // Clear all visualizations
      clearAllVisualizations();
    });
  };

  // Function to clear all visualizations
  const clearAllVisualizations = () => {
    // Destroy existing charts
    if (bmiChart) {
      bmiChart.destroy();
      bmiChart = null;
    }
    if (femaleBmiChart) {
      femaleBmiChart.destroy();
      femaleBmiChart = null;
    }
    if (maleBmiChart) {
      maleBmiChart.destroy();
      maleBmiChart = null;
    }

    // Initialize empty charts
    initializeEmptyCharts();

    // Clear table
    if (bmiTable) {
      bmiTable.clear().draw();
    }
  };

  // Function to update all data
  const updateAllData = (startDate, endDate) => {
    if (!startDate || !endDate) {
      showToast("Please select a valid date range");
      return;
    }

    // Convert dates to proper format if needed
    const formattedStartDate = moment(startDate).format("YYYY-MM-DD");
    const formattedEndDate = moment(endDate).format("YYYY-MM-DD");

    fetchBMIData(formattedStartDate, formattedEndDate, function (data) {
      // Clear existing charts first
      if (bmiChart) {
        bmiChart.destroy();
        bmiChart = null;
      }
      if (femaleBmiChart) {
        femaleBmiChart.destroy();
        femaleBmiChart = null;
      }
      if (maleBmiChart) {
        maleBmiChart.destroy();
        maleBmiChart = null;
      }

      if (data && data.length > 0) {
        // Initialize new charts with data
        initBMIChart(data);
        initGenderCharts(data);

        // Update table
        if (bmiTable) {
          bmiTable.clear().rows.add(data).draw();
        }

        // Update BMI Category Distribution table
        updateBMICategoryTable(data);
      } else {
        // Show empty states
        initializeEmptyCharts();
        if (bmiTable) {
          bmiTable.clear().draw();
        }
        // Clear BMI Category Distribution table
        updateBMICategoryTable([]);
        showToast("No data available for selected date range");
      }
    });
  };

  // Function to update BMI Category Distribution table
  const updateBMICategoryTable = (data) => {
    const bmiCounts = {
      "Severely Wasted": 0,
      Wasted: 0,
      Normal: 0,
      Obese: 0,
    };

    // Count data
    data.forEach((record) => {
      const bmiType = record.finding_bmi;
      if (bmiCounts.hasOwnProperty(bmiType)) {
        bmiCounts[bmiType]++;
      }
    });

    // Update table
    const tableBody = document.querySelector("#bmiCategoryTable tbody");
    if (tableBody) {
      const total = Object.values(bmiCounts).reduce(
        (sum, count) => sum + count,
        0
      );

      tableBody.innerHTML = "";
      Object.entries(bmiCounts).forEach(([category, count]) => {
        const percentage = total > 0 ? ((count / total) * 100).toFixed(1) : 0;
        const row = document.createElement("tr");
        row.innerHTML = `
          <td>${category}</td>
          <td>${count}</td>
          <td>${percentage}%</td>
        `;
        tableBody.appendChild(row);
      });
    }
  };

  // Initialize date range buttons
  const initDateRangeButtons = () => {
    // Overall BMI chart date range
    $("#applyBmiDateRange").on("click", function () {
      const startDate = $("#bmiDateRange").data("startDate");
      const endDate = $("#bmiDateRange").data("endDate");
      updateAllData(startDate, endDate);
    });
  };

  // Function to destroy a chart
  const destroyChart = (chartInstance, canvasId) => {
    if (chartInstance) {
      chartInstance.destroy();
      chartInstance = null;
    }
  };

  // Initialize export handlers
  const initializeExportHandlers = () => {
    console.log('Initializing BMI export handlers...');
    
    // Remove any existing BMI-specific handlers first to prevent duplicates
    $(document).off("click", "#bmi-statistics [data-preview]");
    $(document).off("click", "#bmi-statistics [data-export]");
    
    // Add click event listeners to preview buttons - scoped to BMI statistics only
    $(document).on("click", "#bmi-statistics [data-preview]", function (e) {
      e.preventDefault();
      console.log('BMI Preview button clicked');
      const previewType = $(this).data("preview");
      previewReport(previewType);
    });

    // Add click event listeners to export buttons - scoped to BMI statistics only
    $(document).on("click", "#bmi-statistics [data-export]", function (e) {
      e.preventDefault();
      console.log('BMI Export button clicked');
      const exportType = $(this).data("export");
      const contentType = $(this).data("type");
      console.log('BMI Export data attributes:', { exportType, contentType });

      // Only handle Excel exports
      if (exportType === 'excel') {
        handleExport(exportType, contentType);
      } else {
        console.log('Not an excel export, skipping');
      }
    });
    
    console.log('BMI Export handlers initialized');
  };

  // Function to preview report
  const previewReport = (type) => {
    const startDate = $("#bmiDateRange").data("startDate");
    const endDate = $("#bmiDateRange").data("endDate");

    if (!startDate || !endDate) {
      showToast("Please select a date range first");
      return;
    }

    // Open preview in new tab
    const previewUrl = `../controllers/BMIController.php?action=preview&type=${type}&startDate=${startDate}&endDate=${endDate}`;
    window.open(previewUrl, '_blank');
  };

  const handleExport = (exportType, contentType) => {
    console.log('Export clicked:', { exportType, contentType });
    
    try {
      // Get date range
      const startDate = $("#bmiDateRange").data("startDate");
      const endDate = $("#bmiDateRange").data("endDate");

      console.log('Date range:', { startDate, endDate });

      if (!startDate || !endDate) {
        showToast("Please select a date range first");
        return;
      }

      // Validate content type
      if (!contentType) {
        console.error('Content type is missing!');
        showToast("Export type is missing. Please try again.");
        return;
      }
      
      if (contentType !== 'bmi-category' && contentType !== 'bmi-table') {
        console.error('Invalid content type:', contentType);
        showToast("Invalid export type. Only BMI History and BMI Category Distribution can be exported.");
        return;
      }
      
      showToast(`Preparing ${exportType.toUpperCase()} export...`);

      // Show loading notification
      showToast(`Preparing ${contentType === 'bmi-category' ? 'BMI Category Distribution' : 'BMI History'} export...`);
      
      // Create form for POST submission
      const form = document.createElement('form');
      form.method = 'POST';
      form.action = '../controllers/BMIController.php'; // Use BMIController directly for BMI-specific data
      form.target = '_blank'; // This will open in a new tab but download directly
      
      // Add hidden fields
      const addHiddenField = (name, value) => {
        const field = document.createElement('input');
        field.type = 'hidden';
        field.name = name;
        field.value = value;
        form.appendChild(field);
      };
      
      // Add required fields
      addHiddenField('action', 'exportReport'); // This must match the action in BMIController
      addHiddenField('exportType', exportType);
      addHiddenField('contentType', contentType);
      addHiddenField('startDate', startDate);
      addHiddenField('endDate', endDate);
      
      console.log('Sending export request directly to BMIController.php with parameters:', {
        action: 'exportReport',
        exportType,
        contentType,
        startDate,
        endDate
      });
      
      // Submit the form
      document.body.appendChild(form);
      form.submit();
      
      // Remove the form after submission
      setTimeout(() => {
        document.body.removeChild(form);
        showToast("Export initiated successfully!");
      }, 1000);
    } catch (error) {
      console.error("Export error:", error);
      showToast("Export failed: " + (error.message || "Unknown error"));
    }
  };

  const initGenderCharts = (data, gender = null) => {
    // Get canvas elements
    const femaleCtx = document.getElementById("femaleBmiChart");
    const maleCtx = document.getElementById("maleBmiChart");

    if (!femaleCtx || !maleCtx) {
      console.error("Chart canvas elements not found");
      return;
    }

    try {
      // Use empty array if no data is provided
      const chartData = Array.isArray(data) ? data : [];

      // Destroy existing charts based on gender parameter
      if (!gender || gender === "female") {
        destroyChart(femaleBmiChart, "femaleBmiChart");
        femaleBmiChart = null;
      }
      if (!gender || gender === "male") {
        destroyChart(maleBmiChart, "maleBmiChart");
        maleBmiChart = null;
      }

      // Initialize counters for each gender
      const femaleCounts = {
        "Severely Wasted": 0,
        Wasted: 0,
        Normal: 0,
        Obese: 0,
      };
      const maleCounts = {
        "Severely Wasted": 0,
        Wasted: 0,
        Normal: 0,
        Obese: 0,
      };

      // Count data
      chartData.forEach((record) => {

        const bmiType = record.finding_bmi;
        const sex = record.sex?.toUpperCase();

        if (sex === "F" && femaleCounts.hasOwnProperty(bmiType)) {
          femaleCounts[bmiType]++;
        } else if (sex === "M" && maleCounts.hasOwnProperty(bmiType)) {
          maleCounts[bmiType]++;
        }
      });


      // Colors for BMI categories with opacity for better visibility
      const colors = {
        "Severely Wasted": "rgba(220, 53, 69, 0.8)", // Red
        Wasted: "rgba(255, 193, 7, 0.8)", // Yellow
        Normal: "rgba(40, 167, 69, 0.8)", // Green
        Obese: "rgba(255, 193, 7, 0.8)", // Yellow
      };

      // Set minimum height for chart containers
      femaleCtx.parentElement.style.minHeight = "300px";
      maleCtx.parentElement.style.minHeight = "300px";

      // Create new charts only if they should be created
      if (!gender || gender === "female") {
        femaleBmiChart = new Chart(femaleCtx, {
          type: "doughnut",
          data: {
            labels: Object.keys(femaleCounts),
            datasets: [
              {
                data: Object.values(femaleCounts),
                backgroundColor: Object.keys(femaleCounts).map(
                  (key) => colors[key]
                ),
                borderWidth: 2,
                borderColor: "#fff",
              },
            ],
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: {
                position: "right",
                labels: {
                  color: "#333",
                  padding: 20,
                  font: {
                    size: 12,
                  },
                },
              },
              tooltip: {
                callbacks: {
                  label: function (context) {
                    const value = context.raw;
                    const total = context.dataset.data.reduce(
                      (a, b) => a + b,
                      0
                    );
                    const percentage = ((value / total) * 100).toFixed(1);
                    return `${context.label}: ${value} (${percentage}%)`;
                  },
                },
              },
              datalabels: {
                color: "#fff",
                textStrokeColor: "#000",
                textStrokeWidth: 2,
                font: {
                  weight: "bold",
                  size: 12,
                },
                formatter: function (value, context) {
                  if (value === 0) return "";
                  const total = context.dataset.data.reduce((a, b) => a + b, 0);
                  const percentage = ((value / total) * 100).toFixed(1);
                  return `${value}\n(${percentage}%)`;
                },
                anchor: "center",
                align: "center",
                offset: 0,
              },
            },
          },
        });
      }

      if (!gender || gender === "male") {
        maleBmiChart = new Chart(maleCtx, {
          type: "doughnut",
          data: {
            labels: Object.keys(maleCounts),
            datasets: [
              {
                data: Object.values(maleCounts),
                backgroundColor: Object.keys(maleCounts).map(
                  (key) => colors[key]
                ),
                borderWidth: 2,
                borderColor: "#fff",
              },
            ],
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: {
                position: "right",
                labels: {
                  color: "#333",
                  padding: 20,
                  font: {
                    size: 12,
                  },
                },
              },
              tooltip: {
                callbacks: {
                  label: function (context) {
                    const value = context.raw;
                    const total = context.dataset.data.reduce(
                      (a, b) => a + b,
                      0
                    );
                    const percentage = ((value / total) * 100).toFixed(1);
                    return `${context.label}: ${value} (${percentage}%)`;
                  },
                },
              },
              datalabels: {
                color: "#fff",
                textStrokeColor: "#000",
                textStrokeWidth: 2,
                font: {
                  weight: "bold",
                  size: 12,
                },
                formatter: function (value, context) {
                  if (value === 0) return "";
                  const total = context.dataset.data.reduce((a, b) => a + b, 0);
                  const percentage = ((value / total) * 100).toFixed(1);
                  return `${value}\n(${percentage}%)`;
                },
                anchor: "center",
                align: "center",
                offset: 0,
              },
            },
          },
        });
      }
    } catch (error) {
      console.error("Error initializing gender charts:", error);
    }
  };

  const initBMIChart = (data) => {
    const ctx = document.getElementById("bmiDistributionChart");
    if (!ctx) return;

    try {
      // Destroy existing chart
      destroyChart(bmiChart, "bmiDistributionChart");
      bmiChart = null;

      // Initialize counters for each BMI type
      const bmiCounts = {
        "Severely Wasted": 0,
        Wasted: 0,
        Normal: 0,
        Obese: 0,
      };

      // Count data
      data.forEach((record) => {
        const bmiType = record.finding_bmi;
        if (bmiCounts.hasOwnProperty(bmiType)) {
          bmiCounts[bmiType]++;
        }
      });

      // Prepare chart data
      const labels = Object.keys(bmiCounts);
      const chartData = Object.values(bmiCounts);
      const colors = [
        "rgba(220, 53, 69, 0.8)", // Red
        "rgba(255, 193, 7, 0.8)", // Yellow
        "rgba(40, 167, 69, 0.8)", // Green
        "rgba(255, 193, 7, 0.8)", // Yellow
      ];

      // Create chart
      bmiChart = new Chart(ctx, {
        type: "bar",
        data: {
          labels: labels,
          datasets: [
            {
              data: chartData,
              backgroundColor: colors,
              barPercentage: 0.8,
            },
          ],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          scales: {
            y: {
              beginAtZero: true,
              ticks: {
                stepSize: 1,
              },
            },
          },
          plugins: {
            legend: {
              display: false,
            },
            title: {
              display: true,
              text: "BMI Distribution by Category",
              font: { size: 16, weight: "bold" },
            },
            datalabels: {
              display: function (context) {
                return context.dataset.data[context.dataIndex] > 0;
              },
              color: "#fff",
              textStrokeColor: "#000",
              textStrokeWidth: 2,
              font: {
                weight: "bold",
                size: 12,
              },
              formatter: function (value, context) {
                if (value === 0) return "";
                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                const percentage = ((value / total) * 100).toFixed(1);
                return `${value}\n(${percentage}%)`;
              },
              anchor: "center",
              align: "center",
              offset: 0,
            },
          },
        },
      });

      // Update BMI Category Distribution table
      updateBMICategoryTable(data);
    } catch (error) {
      console.error("Error initializing BMI chart:", error);
    }
  };

  // Function to initialize DataTable
  const initDataTable = () => {
    if ($.fn.DataTable.isDataTable("#bmiTable")) {
      $("#bmiTable").DataTable().destroy();
    }

    bmiTable = $("#bmiTable").DataTable({
      processing: true,
      serverSide: false,
      responsive: true,
      data: [], // Start with empty data
      search: {
        return: true,
      },
      columnDefs: [
        {
          targets: [0], // Date column
          responsivePriority: 1,
        },
        {
          targets: [1], // Patient ID
          responsivePriority: 2,
        },
        {
          targets: [2], // Patient Name
          responsivePriority: 3,
        },
        {
          targets: [3], // Age
          responsivePriority: 3,
        },
        {
          targets: [4], // Sex
          responsivePriority: 3,
        },
        {
          targets: [5], // BMI Status
          responsivePriority: 4,
        },
      ],
      columns: [
        {
          data: "checkup_date",
          title: "Date",
          render: function (data, type, row) {
            if (type === "sort" || type === "filter") {
              return data;
            }

            if (!data || data === "N/A" || data === "") {
              return "No date available";
            }

            try {
              const date = moment(data, "YYYY-MM-DD HH:mm:ss");
              if (date.isValid()) {
                return date.format("MMM DD, YYYY, hh:mm A");
              }
              return "Invalid date";
            } catch (error) {
              console.error("Date parsing error:", error);
              return "Invalid date";
            }
          },
        },
        {
          data: "patient_id",
          title: "Patient ID",
        },
        {
          data: "patient_name",
          title: "Patient Name",
        },
        {
          data: "age",
          title: "Age",
          render: function (data) {
            return data ? data + " years" : "N/A";
          },
        },
        {
          data: "sex",
          title: "Sex",
        },
        {
          data: "finding_bmi",
          title: "BMI Status",
          render: function (data) {
            let className, color;
            switch (data) {
              case "Severely Wasted":
                className = "bg-danger";
                color = "#dc3545";
                break;
              case "Wasted":
                className = "bg-warning";
                color = "#ffc107";
                break;
              case "Normal":
                className = "bg-success";
                color = "#28a745";
                break;
              case "Obese":
                className = "bg-warning";
                color = "#ffc107";
                break;
              default:
                className = "bg-secondary";
                color = "#6c757d";
            }
            return `<span class="status-badge ${className}" style="color: white; padding: 5px 10px; border-radius: 15px; font-size: 0.9em; display: inline-block;">${data}</span>`;
          },
        },
      ],
      order: [[0, "desc"]],
      pageLength: 10,
      lengthMenu: [
        [10, 25, 50, -1],
        [10, 25, 50, "All"],
      ],
      dom:
        '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
        '<"row"<"col-sm-12"tr>>' +
        '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
      language: {
        emptyTable: "No data available for selected date range",
        info: "Showing _START_ to _END_ of _TOTAL_ entries",
        infoEmpty: "Showing 0 to 0 of 0 entries",
        infoFiltered: "(filtered from _MAX_ total entries)",
        lengthMenu: "Show _MENU_ entries",
        search: "Search:",
        zeroRecords: "No matching records found",
      },
      initComplete: function () {
        $(".dataTables_filter input").addClass("form-control");
        $(".dataTables_length select").addClass("form-control");
      },
    });

    // BMI-specific search functionality
    $("#bmiTableSearch").on("keyup", function () {
      bmiTable.search(this.value).draw();
    });

    // BMI-specific entries select
    $("#bmiTableEntriesSelect").on("change", function () {
      bmiTable.page.len($(this).val()).draw();
    });

    // BMI-specific clear search
    const searchContainer = $("#bmiTableSearch").parent();
    searchContainer.append(
      '<button class="btn btn-outline-secondary bmi-clear-search" type="button"><i class="fas fa-times"></i></button>'
    );

    $(".bmi-clear-search").on("click", function () {
      $("#bmiTableSearch").val("").trigger("keyup");
    });
  };

  // Function to load initial data
  const loadInitialData = () => {
    // Initialize empty charts and table
    initializeEmptyCharts();
    initializeEmptyTable();
  };

  // Add styles
  const styles = `
    .bmi-clear-search {
      position: absolute;
      right: 0;
      top: 0;
      height: 100%;
      z-index: 4;
      border: none;
      background: transparent;
    }

    .bmi-clear-search:hover {
      color: #dc3545;
    }

    #bmiTableSearch {
      padding-right: 40px;
    }

    .input-group {
      position: relative;
    }
  `;

  const styleSheet = document.createElement("style");
  styleSheet.textContent = styles;
  document.head.appendChild(styleSheet);

  // Initialize everything when document is ready
  $(document).ready(function () {
    if (isChartJsLoaded()) {
      initDateRangePickers();
      initDateRangeButtons();
      initDataTable();
      loadInitialData();
      initializeToastContainer();
      initializeExportHandlers();

      // Listen for BMI statistics shown event from admin.js
      $(document).on('bmi-statistics-shown', function() {
        console.log('BMI statistics shown event received, reinitializing...');
        // Reinitialize export handlers to ensure they work after content is shown
        initializeExportHandlers();
      });

      // Make sure to handle tab switching properly
      $('button[data-bs-toggle="tab"]').on("shown.bs.tab", function (e) {
        if ($(e.target).attr("data-bs-target") === "#bmi-statistics") {
          setTimeout(() => {
            if (bmiChart) destroyChart(bmiChart, "bmiDistributionChart");
            if (femaleBmiChart) destroyChart(femaleBmiChart, "femaleBmiChart");
            if (maleBmiChart) destroyChart(maleBmiChart, "maleBmiChart");
            loadInitialData();
          }, 100);
        }
      });
    } else {
      console.error("Chart.js is not loaded");
    }
  });

  function initializeEmptyCharts() {
    // Initialize empty bar chart
    const barCtx = document.getElementById("bmiDistributionChart");
    if (barCtx && !bmiChart) {
      bmiChart = new Chart(barCtx, {
        type: "bar",
        data: {
          labels: ["Severely Wasted", "Wasted", "Normal", "Obese"],
          datasets: [
            {
              data: [0, 0, 0, 0],
              backgroundColor: [
                "rgba(220, 53, 69, 0.8)",
                "rgba(255, 193, 7, 0.8)",
                "rgba(40, 167, 69, 0.8)",
                "rgba(255, 193, 7, 0.8)",
              ],
            },
          ],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: { display: false },
            title: {
              display: true,
              text: "No data available for selected date range",
            },
          },
          scales: {
            y: { beginAtZero: true },
          },
        },
      });
    }

    // Initialize empty gender charts
    const femaleCtx = document.getElementById("femaleBmiChart");
    const maleCtx = document.getElementById("maleBmiChart");

    if (femaleCtx && !femaleBmiChart) {
      femaleBmiChart = new Chart(femaleCtx, {
        type: "doughnut",
        data: {
          labels: ["Severely Wasted", "Wasted", "Normal", "Obese"],
          datasets: [
            {
              data: [0, 0, 0, 0],
              backgroundColor: [
                "rgba(220, 53, 69, 0.8)",
                "rgba(255, 193, 7, 0.8)",
                "rgba(40, 167, 69, 0.8)",
                "rgba(255, 193, 7, 0.8)",
              ],
            },
          ],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: { position: "right" },
            title: {
              display: true,
              text: "No data available for selected date range",
            },
          },
        },
      });
    }

    if (maleCtx && !maleBmiChart) {
      maleBmiChart = new Chart(maleCtx, {
        type: "doughnut",
        data: {
          labels: ["Severely Wasted", "Wasted", "Normal", "Obese"],
          datasets: [
            {
              data: [0, 0, 0, 0],
              backgroundColor: [
                "rgba(220, 53, 69, 0.8)",
                "rgba(255, 193, 7, 0.8)",
                "rgba(40, 167, 69, 0.8)",
                "rgba(255, 193, 7, 0.8)",
              ],
            },
          ],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: { position: "right" },
            title: {
              display: true,
              text: "No data available for selected date range",
            },
          },
        },
      });
    }
  }

  function initializeEmptyTable() {
    if ($.fn.DataTable.isDataTable("#bmiTable")) {
      $("#bmiTable").DataTable().destroy();
    }

    bmiTable = $("#bmiTable").DataTable({
      processing: true,
      serverSide: false,
      responsive: true,
      data: [],
      columns: [
        {
          title: "Date",
          data: "checkup_date",
          render: function (data) {
            return data ? moment(data).format("MMM DD, YYYY") : "N/A";
          },
        },
        {
          title: "Patient ID",
          data: "patient_id",
        },
        {
          title: "Patient Name",
          data: "patient_name",
        },
        {
          title: "Age",
          data: "age",
          render: function (data) {
            return data ? data + " years" : "N/A";
          },
        },
        {
          title: "Sex",
          data: "sex",
          render: function (data) {
            return data ? data.toUpperCase() : "N/A";
          },
        },
        {
          title: "BMI Status",
          data: "finding_bmi",
          render: function (data) {
            let className = "";
            switch (data) {
              case "Severely Wasted":
                className = "bg-danger";
                break;
              case "Wasted":
                className = "bg-warning";
                break;
              case "Normal":
                className = "bg-success";
                break;
              case "Obese":
                className = "bg-warning";
                break;
              default:
                className = "bg-secondary";
            }
            return `<span class="badge ${className}">${data || "N/A"}</span>`;
          },
        },
      ],
      order: [[0, "desc"]],
      pageLength: 10,
      lengthMenu: [
        [10, 25, 50, -1],
        [10, 25, 50, "All"],
      ],
      language: {
        emptyTable: "No data available for selected date range",
        info: "Showing _START_ to _END_ of _TOTAL_ entries",
        infoEmpty: "Showing 0 to 0 of 0 entries",
        infoFiltered: "(filtered from _MAX_ total entries)",
        lengthMenu: "Show _MENU_ entries",
        search: "Search:",
        zeroRecords: "No matching records found",
      },
      dom:
        '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
        '<"row"<"col-sm-12"tr>>' +
        '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
      initComplete: function () {
        // Add custom search input
        $(".dataTables_filter input").addClass("form-control");
        $(".dataTables_length select").addClass("form-control");
      },
    });
  }

  function loadBMIStats(startDate, endDate, chartType = "all") {
    if (!startDate || !endDate) {

      return;
    }


    $.ajax({
      url: "/src/controllers/BMIController.php",
      method: "GET",
      data: {
        action: "getBMIDetails",
        startDate: startDate,
        endDate: endDate,
      },
      success: (response) => {
        if (
          response.status === "success" &&
          response.data &&
          response.data.length > 0
        ) {
          // Update only charts
          switch (chartType) {
            case "all":
              updateAllCharts(response.data);
              break;
            case "female":
              updateFemaleChart(response.data);
              break;
            case "male":
              updateMaleChart(response.data);
              break;
          }
          // Refresh table data
          if (bmiTable) {
            bmiTable.clear().rows.add(response.data).draw();
          }
        } else {
          // If no data, show empty state
          initializeEmptyCharts();
          if (bmiTable) {
            bmiTable.clear().draw();
          }
          showToast("No data available for selected date range");
        }
      },
      error: (xhr, status, error) => {
        console.error("Ajax request failed:", error);
        showToast("Failed to load BMI statistics");
      },
    });
  }
});
