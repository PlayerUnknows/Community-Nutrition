class ArmCircumferenceReportManager {
  constructor() {
    // Initialize default data first
    this.defaultData = {
      "Too Small": 0,
      Normal: 0,
      Over: 0,
    };

    this.defaultGenderData = {
      M: { "Too Small": 0, Normal: 0, Over: 0 },
      F: { "Too Small": 0, Normal: 0, Over: 0 },
    };

    this.barChart = null;
    this.maleChart = null;
    this.femaleChart = null;
    this.armTable = null;

    // Initialize toast container first
    this.initializeToastContainer();

    // Initialize date pickers
    this.initializeDateRangePickers();
    this.initializeEventListeners();
    this.initializeTable();

    // Load initial data
    this.loadInitialData();
  }

  initializeDateRangePickers() {
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

    // Initialize all date pickers
    ["#armDateRange", "#armFemaleDateRange", "#armMaleDateRange"].forEach(
      (picker) => {
        $(picker).daterangepicker(dateRangeConfig);

        // Set default values
        $(picker).val(
          defaultStartDate.format("MM/DD/YYYY") +
            " - " +
            defaultEndDate.format("MM/DD/YYYY")
        );
        $(picker).data("startDate", defaultStartDate.format("YYYY-MM-DD"));
        $(picker).data("endDate", defaultEndDate.format("YYYY-MM-DD"));

        // Handle date selection
        $(picker).on("apply.daterangepicker", (ev, picker) => {
          $(ev.target).val(
            picker.startDate.format("MM/DD/YYYY") +
              " - " +
              picker.endDate.format("MM/DD/YYYY")
          );
          $(ev.target).data("startDate", picker.startDate.format("YYYY-MM-DD"));
          $(ev.target).data("endDate", picker.endDate.format("YYYY-MM-DD"));
        });

        // Handle clear
        $(picker).on("cancel.daterangepicker", (ev, picker) => {
          $(ev.target).val("");
          $(ev.target).data("startDate", "");
          $(ev.target).data("endDate", "");
          picker.setStartDate(moment());
          picker.setEndDate(moment());
        });
      }
    );
  }

  initializeEventListeners() {
    // Overall chart date range
    $("#applyArmDateRange").on("click", () => {
      const startDate = $("#armDateRange").data("startDate");
      const endDate = $("#armDateRange").data("endDate");

      if (!startDate || !endDate) {
        this.showToast("Please select a date range");
        return;
      }

      // Load both charts and table data
      this.loadArmCircumferenceStats(startDate, endDate, "all");
    });

    // Female chart date range
    $("#applyArmFemaleDateRange").on("click", () => {
      const startDate = $("#armFemaleDateRange").data("startDate");
      const endDate = $("#armFemaleDateRange").data("endDate");

      if (!startDate || !endDate) {
        this.showToast("Please select a date range");
        return;
      }

      this.loadArmCircumferenceStats(startDate, endDate, "female");
    });

    // Male chart date range
    $("#applyArmMaleDateRange").on("click", () => {
      const startDate = $("#armMaleDateRange").data("startDate");
      const endDate = $("#armMaleDateRange").data("endDate");

      if (!startDate || !endDate) {
        this.showToast("Please select a date range");
        return;
      }

      this.loadArmCircumferenceStats(startDate, endDate, "male");
    });

    // Preview link click handler
    $(document).on("click", ".arm-preview-link", (e) => {
      e.preventDefault();
      const type = $(e.currentTarget).data("type");
      this.handlePreview(type);
    });

    // Export button handler - specific for Arm Circumference History table
    $("#exportTableBtn").on("click", (e) => {
      e.preventDefault();
      this.handleExport("table", e);
    });
    
    // Auto-apply dates when initialized
    setTimeout(() => {
      const startDate = $("#armDateRange").data("startDate");
      const endDate = $("#armDateRange").data("endDate");
      
      if (startDate && endDate) {
        this.loadArmCircumferenceStats(startDate, endDate, "all");
      }
    }, 500);
  }

  initializeTable() {
    try {
      // First destroy if exists
      if ($.fn.DataTable.isDataTable("#armCircumferenceTable")) {
        $("#armCircumferenceTable").DataTable().destroy();
      }

      // Initialize DataTable with specific configuration
      this.armTable = $("#armCircumferenceTable").DataTable({
        processing: true,
        serverSide: false,
        responsive: true,
        data: [], // Start with empty data
        columns: [
          {
            title: "Date",
            data: "created_at",
            render: function (data) {
              return data ? moment(data).format("MMM D, YYYY") : "";
            },
          },
          { title: "Patient Name", data: "patient_name" },
          { title: "Age", data: "age" },
          { title: "Gender", data: "sex" },
          { title: "Arm Circumference (cm)", data: "arm_circumference" },
          {
            title: "Status",
            data: "arm_circumference_status",
            render: (data, type, row) => {
              if (type === 'display') {
                return data ? this.getStatusBadge(data) : "";
              }
              return data || '';
            },
          },
        ],
        order: [[0, "desc"]],
        pageLength: 10,
        lengthMenu: [
          [10, 25, 50, -1],
          [10, 25, 50, "All"],
        ],
        dom: '<"row"<"col-sm-6"l><"col-sm-6"f>>rt<"row"<"col-sm-5"i><"col-sm-7"p>>',
        searching: true,
        language: {
          emptyTable: "No data available",
          zeroRecords: "No matching records found",
          info: "Showing _START_ to _END_ of _TOTAL_ entries",
          infoEmpty: "Showing 0 to 0 of 0 entries",
          infoFiltered: "(filtered from _MAX_ total entries)",
          search: "Search:"
        },
        // Ensure search is applied to all columns
        search: {
          caseInsensitive: true,
          smart: true
        }
      });

      // Initialize search functionality
      this.initializeSearchFunctionality(this.armTable);
      
      // Show/hide the DataTables search box
      $('.dataTables_filter').hide(); // Hide the built-in search if using external

      return this.armTable;
    } catch (error) {
     
      this.showToast("Error initializing table");
    }
  }

  loadInitialData() {
    // Initialize empty charts 
    this.createBarChart();
    this.createGenderCharts();

    // Initialize empty table
    if (this.armTable) {
      this.armTable.clear().draw();
    }
    
    // Get default date range
    const startDate = $("#armDateRange").data("startDate");
    const endDate = $("#armDateRange").data("endDate");
    
    // Load data if dates are available
    if (startDate && endDate) {
      this.loadArmCircumferenceStats(startDate, endDate, "all");
    }
  }

  loadArmCircumferenceStats(startDate, endDate, chartType = "all") {
    if (!startDate || !endDate) {
      return;
    }

    // Always load table data when date range changes
    this.loadTableData(startDate, endDate);

    // Load chart data
    $.ajax({
      url: "../controllers/ArmCircumferenceController.php",
      method: "POST",
      data: {
        action: "getArmCircumferenceStats",
        startDate: startDate,
        endDate: endDate,
      },
      success: (response) => {
        try {
          const parsedResponse = typeof response === "string" ? JSON.parse(response) : response;
          
          if (parsedResponse.success && parsedResponse.data) {
            // Check if there's any data
            const hasData =
              parsedResponse.data.summary && 
              Array.isArray(parsedResponse.data.summary) && 
              parsedResponse.data.summary.length > 0;

            if (!hasData) {
            
              
              // Initialize empty charts
              switch (chartType) {
                case "all":
                  this.createBarChart();
                  this.createGenderCharts();
                  break;
                case "female":
                  this.createGenderCharts({
                    ...this.defaultGenderData,
                    M: this.maleChart
                      ? this.maleChart.data.datasets[0].data
                      : [0, 0, 0],
                  });
                  break;
                case "male":
                  this.createGenderCharts({
                    ...this.defaultGenderData,
                    F: this.femaleChart
                      ? this.femaleChart.data.datasets[0].data
                      : [0, 0, 0],
                  });
                  break;
              }
              return;
            }

            // Update charts
            switch (chartType) {
              case "all":
                this.updateAllCharts(parsedResponse.data.summary);
                break;
              case "female":
                this.updateFemaleChart(parsedResponse.data.summary);
                break;
              case "male":
                this.updateMaleChart(parsedResponse.data.summary);
                break;
            }
          } else {
      
            this.showToast("Error loading arm circumference data");
            
            // Initialize empty charts
            switch (chartType) {
              case "all":
                this.createBarChart();
                this.createGenderCharts();
                break;
              case "female":
              case "male":
                this.createGenderCharts();
                break;
            }
          }
        } catch (error) {
        
          this.showToast("Error processing response: " + error.message);
          
          // Initialize empty charts
          switch (chartType) {
            case "all":
              this.createBarChart();
              this.createGenderCharts();
              break;
            case "female":
            case "male":
              this.createGenderCharts();
              break;
          }
        }
      },
      error: (xhr, status, error) => {
      
        this.showToast("Failed to load arm circumference data: " + (error || "Unknown error"));
        
        // Initialize empty charts
        switch (chartType) {
          case "all":
            this.createBarChart();
            this.createGenderCharts();
            break;
          case "female":
          case "male":
            this.createGenderCharts();
            break;
        }
      },
    });
  }

  loadTableData(startDate, endDate) {
    if (!startDate || !endDate) {
   
      return;
    }

    // Show loading indicator or message
    if (this.armTable) {
      this.armTable.clear().draw();
      $('#armCircumferenceTable tbody').html('<tr><td colspan="6" class="text-center">Loading data...</td></tr>');
    }

    $.ajax({
      url: "../controllers/ArmCircumferenceController.php",
      method: "POST",
      data: {
        action: "getArmCircumferenceTableData",
        startDate: startDate,
        endDate: endDate,
      },
      success: (response) => {
        try {
          const parsedResponse = typeof response === "string" ? JSON.parse(response) : response;
          
          if (parsedResponse.success && Array.isArray(parsedResponse.data)) {
            if (parsedResponse.data.length > 0) {
              // Make sure data is properly formatted before adding to table
              const formattedData = parsedResponse.data.map(item => {
                return {
                  ...item,
                  // Ensure all required fields exist
                  created_at: item.created_at || '',
                  patient_name: item.patient_name || '',
                  age: item.age || '',
                  sex: item.sex || '',
                  arm_circumference: item.arm_circumference || '',
                  arm_circumference_status: item.arm_circumference_status || ''
                };
              });
              
              if (this.armTable) {
                this.armTable.clear().rows.add(formattedData).draw();
              }
            } else {
              if (this.armTable) {
                this.armTable.clear().draw();
              }
              
              // Update table with "No data" message
              $('#armCircumferenceTable tbody').html('<tr><td colspan="6" class="text-center">No data available for the selected date range</td></tr>');
            }
          } else {
      
            this.showToast("Error loading table data: Invalid response format");
            if (this.armTable) {
              this.armTable.clear().draw();
            }
          }
        } catch (error) {
       
          this.showToast("Error processing table data");
          if (this.armTable) {
            this.armTable.clear().draw();
          }
        }
      },
      error: (xhr, status, error) => {
   
        this.showToast("Failed to load table data: " + (error || "Unknown error"));
        if (this.armTable) {
          this.armTable.clear().draw();
        }
      }
    });
  }

  updateAllCharts(data) {
    // Process data for charts
    const totalCounts = {
      "Too Small": 0,
      Normal: 0,
      Over: 0,
    };

    const genderCounts = {
      M: { "Too Small": 0, Normal: 0, Over: 0 },
      F: { "Too Small": 0, Normal: 0, Over: 0 },
    };

    // Count data
    if (Array.isArray(data)) {
      data.forEach((record) => {
        const status = record.arm_circumference_status;
        const sex = record.sex;
        const count = parseInt(record.count) || 0;

        if (totalCounts.hasOwnProperty(status)) {
          totalCounts[status] += count;
        }

        if (genderCounts[sex] && genderCounts[sex].hasOwnProperty(status)) {
          genderCounts[sex][status] += count;
        }
      });
    }

    // Update charts
    this.createBarChart(totalCounts);
    this.createGenderCharts(genderCounts);
  }

  createBarChart(data = this.defaultData) {
    const ctx = document.getElementById("armCircumferenceBarChart");
    if (!ctx) {
  
      return;
    }

    try {
      // Ensure data has all required properties
      const chartData = {
        "Too Small": data["Too Small"] || 0,
        Normal: data["Normal"] || 0,
        Over: data["Over"] || 0,
      };

      // Destroy existing chart
      this.destroyChart(this.barChart, "armCircumferenceBarChart");
      this.barChart = null;

      // Create new chart
      this.barChart = new Chart(ctx, {
        type: "bar",
        data: {
          labels: ["Too Small", "Normal", "Over"],
          datasets: [
            {
              data: [
                chartData["Too Small"],
                chartData["Normal"],
                chartData["Over"],
              ],
              backgroundColor: [
                "rgba(255, 99, 132, 0.8)",
                "rgba(75, 192, 192, 0.8)",
                "rgba(255, 159, 64, 0.8)",
              ],
              borderWidth: 1,
            },
          ],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: { display: false },
            datalabels: {
              color: "#fff",
              font: { weight: "bold" },
              formatter: (value) => value || "",
            },
          },
          scales: {
            y: {
              beginAtZero: true,
            },
          },
        },
      });
    } catch (error) {
   
    }
  }

  createGenderCharts(data = this.defaultGenderData) {
    try {
      // Get canvas elements
      const maleCtx = document.getElementById("maleCircumferenceChart");
      const femaleCtx = document.getElementById("femaleCircumferenceChart");

      if (!maleCtx || !femaleCtx) {
      
        return;
      }

      // Ensure data has all required properties
      const chartData = {
        M: {
          "Too Small": (data["M"] && data["M"]["Too Small"]) || 0,
          Normal: (data["M"] && data["M"]["Normal"]) || 0,
          Over: (data["M"] && data["M"]["Over"]) || 0,
        },
        F: {
          "Too Small": (data["F"] && data["F"]["Too Small"]) || 0,
          Normal: (data["F"] && data["F"]["Normal"]) || 0,
          Over: (data["F"] && data["F"]["Over"]) || 0,
        },
      };

      // Destroy existing charts
      this.destroyChart(this.maleChart, "maleCircumferenceChart");
      this.destroyChart(this.femaleChart, "femaleCircumferenceChart");
      this.maleChart = null;
      this.femaleChart = null;

      // Create male chart
      this.maleChart = new Chart(maleCtx, {
        type: "doughnut",
        data: {
          labels: ["Too Small", "Normal", "Over"],
          datasets: [
            {
              data: [
                chartData["M"]["Too Small"],
                chartData["M"]["Normal"],
                chartData["M"]["Over"],
              ],
              backgroundColor: [
                "rgba(54, 162, 235, 0.8)",
                "rgba(54, 162, 235, 0.6)",
                "rgba(54, 162, 235, 0.4)",
              ],
            },
          ],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: { position: "right" },
            datalabels: {
              color: "#fff",
              font: { weight: "bold" },
              formatter: (value) => value || "",
            },
          },
        },
      });

      // Create female chart
      this.femaleChart = new Chart(femaleCtx, {
        type: "doughnut",
        data: {
          labels: ["Too Small", "Normal", "Over"],
          datasets: [
            {
              data: [
                chartData["F"]["Too Small"],
                chartData["F"]["Normal"],
                chartData["F"]["Over"],
              ],
              backgroundColor: [
                "rgba(255, 99, 132, 0.8)",
                "rgba(255, 99, 132, 0.6)",
                "rgba(255, 99, 132, 0.4)",
              ],
            },
          ],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: { position: "right" },
            datalabels: {
              color: "#fff",
              font: { weight: "bold" },
              formatter: (value) => value || "",
            },
          },
        },
      });
    } catch (error) {
  
    }
  }

  destroyAllCharts() {
    if (this.barChart) {
      this.barChart.destroy();
      this.barChart = null;
    }
    if (this.maleChart) {
      this.maleChart.destroy();
      this.maleChart = null;
    }
    if (this.femaleChart) {
      this.femaleChart.destroy();
      this.femaleChart = null;
    }
  }

  handleDateFilter() {
    const startDate = $("#startDate").val();
    const endDate = $("#endDate").val();
    this.loadArmCircumferenceStats(startDate, endDate);
  }

  handlePresetDateRange(range) {
    const today = moment();
    let startDate, endDate;

    switch (range) {
      case "week":
        startDate = today.clone().subtract(1, "week");
        break;
      case "month":
        startDate = today.clone().subtract(1, "month");
        break;
      case "year":
        startDate = today.clone().subtract(1, "year");
        break;
      default:
        startDate = null;
        endDate = null;
    }

    if (startDate) {
      $("#startDate").val(startDate.format("YYYY-MM-DD"));
      $("#endDate").val(today.format("YYYY-MM-DD"));
    } else {
      $("#startDate").val("");
      $("#endDate").val("");
    }

    this.handleDateFilter();
  }

  initializeSearchFunctionality(table) {
    // Remove any existing event listeners
    $("#armTableSearch").off();
    $("#armTableEntriesSelect").off();

    // Add enhanced search functionality to search across all columns
    $("#armTableSearch").on("keyup", function() {
      const searchVal = $(this).val();
      
      // Apply search to DataTable
      table.search(searchVal).draw();
    });

    // Handle entries select
    $("#armTableEntriesSelect").on("change", function () {
      const val = $(this).val();
      table.page.len(parseInt(val)).draw();
    });
    
    // Make sure search box is empty initially
    $("#armTableSearch").val('');
    
    // Make sure the external search box is properly connected to table
    $(".dataTables_filter").hide(); // Hide built-in search box
  }

  getStatusBadge(status) {
    let className;
    const statusLower = status ? status.toLowerCase() : '';
    
    switch (statusLower) {
      case "too small":
        className = "status-alert";
        break;
      case "normal":
        className = "status-normal";
        break;
      case "over":
        className = "status-warning";
        break;
      default:
        className = "status-normal";
    }
    return `<span class="status-badge ${className}">${status || 'Unknown'}</span>`;
  }

  updateTable(data) {
    try {
      if (this.armTable) {
        this.armTable.clear();
        if (Array.isArray(data)) {
          this.armTable.rows.add(data).draw();
        }
      }
    } catch (error) {
   
    }
  }

  updateMaleChart(data) {
 
    if (!this.maleChart) {

      return;
    }

    // Process data for male chart
    const maleData = {
      "Too Small": 0,
      Normal: 0,
      Over: 0,
    };

    // Aggregate data for male
    data.forEach((record) => {
      if (
        record.sex === "M" &&
        maleData.hasOwnProperty(record.arm_circumference_status)
      ) {
        maleData[record.arm_circumference_status] += parseInt(record.count);
      }
    });

    // Update chart data
    this.maleChart.data.datasets[0].data = [
      maleData["Too Small"],
      maleData["Normal"],
      maleData["Over"],
    ];

    this.maleChart.update();
  }

  updateFemaleChart(data) {
    if (!this.femaleChart) {

      return;
    }

    // Process data for female chart
    const femaleData = {
      "Too Small": 0,
      Normal: 0,
      Over: 0,
    };

    // Aggregate data for female
    data.forEach((record) => {
      if (
        record.sex === "F" &&
        femaleData.hasOwnProperty(record.arm_circumference_status)
      ) {
        femaleData[record.arm_circumference_status] += parseInt(record.count);
      }
    });

    // Update chart data
    this.femaleChart.data.datasets[0].data = [
      femaleData["Too Small"],
      femaleData["Normal"],
      femaleData["Over"],
    ];

    this.femaleChart.update();
  }

  initializeToastContainer() {
    if (!document.getElementById("armToastContainer")) {
      const toastContainer = document.createElement("div");
      toastContainer.id = "armToastContainer";
      toastContainer.className =
        "toast-container position-fixed bottom-0 end-0 p-3";
      toastContainer.style.zIndex = "5000";
      document.body.appendChild(toastContainer);
    }
  }

  showToast(message) {
    const toastContainer = document.getElementById("armToastContainer");
    if (!toastContainer) {
      this.initializeToastContainer();
    }

    // Check for existing toasts with the same message
    const existingToasts = document.querySelectorAll(".toast-body");
    for (let toast of existingToasts) {
      if (toast.textContent.trim() === message.trim()) {
        return; // Skip creating duplicate toast
      }
    }

    const toastId = "toast-" + Date.now();
    const toastHtml = `
      <div id="${toastId}" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header">
          <strong class="me-auto">Arm Circumference Report</strong>
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
  }

  // Add this helper method to safely destroy charts
  destroyChart(chart, canvasId) {
    try {
      // First try to destroy any existing chart instance
      const existingChart = Chart.getChart(canvasId);
      if (existingChart) {
        existingChart.destroy();
      }

      // Also destroy our stored instance if it exists
      if (chart && typeof chart.destroy === "function") {
        chart.destroy();
      }
    } catch (error) {

    }
  }

  handleExport(contentType, event) {
    this.showToast("Preparing Arm Circumference History export...");

    try {
      // Get export type from the clicked element
      const exportType = $(event.currentTarget).data("export");

      // Get date range
      const startDate = $("#armDateRange").data("startDate") || "";
      const endDate = $("#armDateRange").data("endDate") || "";

      // Create form data
      const formData = new FormData();
      formData.append("action", "exportReport");
      formData.append("contentType", contentType);
      formData.append("startDate", startDate);
      formData.append("endDate", endDate);
      formData.append("exportType", exportType);

      // Create XMLHttpRequest for binary data
      const xhr = new XMLHttpRequest();
      xhr.open("POST", "../controllers/ArmCircumferenceController.php", true);
      xhr.responseType = "blob";

      // Handle response
      xhr.onload = () => {
        if (xhr.status === 200) {
          const contentType = xhr.getResponseHeader("Content-Type");

          // Check if response is JSON (error message)
          if (contentType && contentType.includes("application/json")) {
            // Handle error response
            const reader = new FileReader();
            reader.onload = () => {
              try {
                const error = JSON.parse(reader.result);
                this.showToast(error.error || "Export failed");
              } catch (e) {
                this.showToast("Export failed: Invalid response format");
              }
            };
            reader.readAsText(xhr.response);
            return;
          }

          // Set file extension and content type for Excel
          const fileExt = "xlsx";
          const blobType = "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet";

          // Create blob with the correct content type
          const blob = new Blob([xhr.response], { type: blobType });

          // Create download link
          const url = window.URL.createObjectURL(blob);
          const a = document.createElement("a");
          a.style.display = "none";
          a.href = url;
          
          // Set filename for arm circumference table
          const filename = "arm_circumference_history_" + moment().format("YYYY-MM-DD") + "." + fileExt;
          a.download = filename;

          // Trigger download
          document.body.appendChild(a);
          a.click();

          // Cleanup
          window.URL.revokeObjectURL(url);
          document.body.removeChild(a);

          this.showToast("Export completed successfully!");
        } else {
          this.showToast(`Export failed: Server returned status ${xhr.status}`);
        }
      };

      // Handle network errors
      xhr.onerror = () => {
        this.showToast("Export failed: Network error occurred");
      };

      // Send request
      xhr.send(formData);
    } catch (error) {
      this.showToast(`Export failed: ${error.message}`);
    }
  }

  initializeExportHandlers() {
    // Remove any existing event handlers first - scoped to arm circumference only
    $(document).off("click", "#arm-circumference [data-export]");

    // Handle export dropdown clicks - scoped to arm circumference only
    $(document).on("click", "#arm-circumference [data-export]", (e) => {
      e.preventDefault();
      e.stopPropagation(); // Prevent event bubbling

      const contentType = $(e.currentTarget).data("type");

      // Skip validation for table data export
      if (contentType === "arm-table") {
        this.handleExport(contentType, e);
        return;
      }

      // Ensure we have the required chart before proceeding
      if (!this.validateChartForExport(contentType)) {
        this.showToast(
          "Chart is not ready. Please wait a moment and try again."
        );
        return;
      }

      this.handleExport(contentType, e);
    });
  }

  // Add this helper method to validate charts before export
  validateChartForExport(contentType) {
    switch (contentType) {
      case "all-charts":
        return (
          document.getElementById("armCircumferenceBarChart") &&
          document.getElementById("femaleCircumferenceChart") &&
          document.getElementById("maleCircumferenceChart")
        );
      case "arm-distribution":
        return document.getElementById("armCircumferenceBarChart");
      case "arm-female":
        return document.getElementById("femaleCircumferenceChart");
      case "arm-male":
        return document.getElementById("maleCircumferenceChart");
      default:
        return false;
    }
  }

  handlePreview(type) {
    // Get the current date range from the main date picker
    const startDate = $("#armDateRange").data("startDate");
    const endDate = $("#armDateRange").data("endDate");

    if (!startDate || !endDate) {
      this.showToast("Please select a date range first");
      return;
    }

    // Open preview in new tab
    const url = `../controllers/ArmCircumferenceController.php?action=preview&type=${type}&startDate=${startDate}&endDate=${endDate}`;
    window.open(url, '_blank');
  }
}

// Single initialization point
let armCircumferenceReportManager = null;
$(document).ready(() => {
  // Register Chart.js plugins
  Chart.register(ChartDataLabels);

  if (!armCircumferenceReportManager) {
    try {
      // Remove any existing event handlers
      $(document).off("click", "[data-export]");
      armCircumferenceReportManager = new ArmCircumferenceReportManager();
    } catch (error) {
    
    }
  }
});
