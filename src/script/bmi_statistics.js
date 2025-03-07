document.addEventListener("DOMContentLoaded", function () {
  let bmiTable;
  let bmiChart = null;
  let femaleBmiChart = null;
  let maleBmiChart = null;

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
      url: "../controllers/ReportController.php",
      type: "POST",
      data: {
        action: "getBMIDetails",
        startDate: startDate || "",
        endDate: endDate || "",
      },
      success: function (response) {
        if (response.status === "success" && Array.isArray(response.data)) {
          callback(response.data);
        } else {
          callback([]);
        }
      },
      error: function (xhr, error, thrown) {
        console.error("AJAX error:", { xhr, error, thrown });
        callback([]);
      },
    });
  };

  // Initialize date range pickers
  const initDateRangePickers = () => {
    // Set default dates to yesterday
    const yesterday = moment().subtract(1, "days");
    const defaultStartDate = yesterday.clone().startOf("day");
    const defaultEndDate = yesterday.clone().endOf("day");

    const dateRangeConfig = {
      autoUpdateInput: true, // Changed to true for default value
      startDate: defaultStartDate,
      endDate: defaultEndDate,
      locale: {
        cancelLabel: "Clear",
        format: "MM/DD/YYYY",
      },
    };

    // Initialize all date range pickers
    [
      "#overallDateRange",
      "#femaleDateRange",
      "#maleDateRange",
      "#dateRangePicker",
    ].forEach((selector) => {
      $(selector).daterangepicker(dateRangeConfig);

      // Set default values
      $(selector).val(
        defaultStartDate.format("MM/DD/YYYY") +
          " - " +
          defaultEndDate.format("MM/DD/YYYY")
      );
      $(selector).data("startDate", defaultStartDate.format("YYYY-MM-DD"));
      $(selector).data("endDate", defaultEndDate.format("YYYY-MM-DD"));

      $(selector).on("apply.daterangepicker", function (ev, picker) {
        $(this).val(
          picker.startDate.format("MM/DD/YYYY") +
            " - " +
            picker.endDate.format("MM/DD/YYYY")
        );
        $(this).data("startDate", picker.startDate.format("YYYY-MM-DD"));
        $(this).data("endDate", picker.endDate.format("YYYY-MM-DD"));
      });

      $(selector).on("cancel.daterangepicker", function (ev, picker) {
        $(this).val("");
        $(this).data("startDate", "");
        $(this).data("endDate", "");
      });
    });
  };

  // Function to check if Chart.js and plugins are loaded
  const isChartJsLoaded = () => {
    return (
      typeof Chart !== "undefined" && typeof ChartDataLabels !== "undefined"
    );
  };

  // Function to initialize charts with retry mechanism
  const initializeChartsWithRetry = (data, maxRetries = 5) => {
    let retryCount = 0;

    const tryInitialize = () => {
      if (!isChartJsLoaded()) {
        console.log("Chart.js not loaded yet, retrying...", retryCount);
        if (retryCount < maxRetries) {
          retryCount++;
          setTimeout(tryInitialize, 1000);
          return;
        } else {
          console.error(
            "Failed to load Chart.js after",
            maxRetries,
            "attempts"
          );
          return;
        }
      }

      console.log("Chart.js loaded, initializing charts...");
      initGenderCharts(data);
      initBMIChart(data);
    };

    tryInitialize();
  };

  // Add event listeners for date range buttons
  const initDateRangeButtons = () => {
    $("#applyOverallDateRange").on("click", function () {
      const startDate = $("#overallDateRange").data("startDate");
      const endDate = $("#overallDateRange").data("endDate");

      // Update all charts when overall date range changes
      fetchBMIData(startDate, endDate, function (data) {
        // Update main BMI chart
        initBMIChart(data);

        // Update both gender charts
        initGenderCharts(data);

        // Also update the date ranges for gender-specific charts
        $("#femaleDateRange").data("startDate", startDate);
        $("#femaleDateRange").data("endDate", endDate);
        $("#maleDateRange").data("startDate", startDate);
        $("#maleDateRange").data("endDate", endDate);

        // Update the display of date range pickers
        $("#femaleDateRange, #maleDateRange").val(
          moment(startDate).format("MM/DD/YYYY") +
            " - " +
            moment(endDate).format("MM/DD/YYYY")
        );
      });
    });

    $("#applyFemaleDateRange").on("click", function () {
      const startDate = $("#femaleDateRange").data("startDate");
      const endDate = $("#femaleDateRange").data("endDate");

      // Only update female chart
      fetchBMIData(startDate, endDate, function (data) {
        initGenderCharts(data, "female");
      });
    });

    $("#applyMaleDateRange").on("click", function () {
      const startDate = $("#maleDateRange").data("startDate");
      const endDate = $("#maleDateRange").data("endDate");

      // Only update male chart
      fetchBMIData(startDate, endDate, function (data) {
        initGenderCharts(data, "male");
      });
    });

    // Separate handler for DataTable date range
    $("#applyDateRange").on("click", function () {
      if (bmiTable) {
        bmiTable.ajax.reload();
      }
    });
  };

  // Function to safely destroy a chart
  const destroyChart = (chartInstance, canvasId) => {
    try {
      // First try to get the chart instance from Chart.js
      const existingChart = Chart.getChart(canvasId);
      if (existingChart) {
        existingChart.destroy();
      }
      // Also destroy the stored instance if it exists
      if (chartInstance && typeof chartInstance.destroy === "function") {
        chartInstance.destroy();
      }
    } catch (error) {
      console.warn(`Error destroying chart ${canvasId}:`, error);
    }
  };

  const initGenderCharts = (data, gender = null) => {
    console.log("Initializing gender charts with data:", data);

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
        console.log("Processing record:", record);
        const bmiType = record.finding_bmi;
        const sex = record.sex?.toUpperCase();

        if (sex === "F" && femaleCounts.hasOwnProperty(bmiType)) {
          femaleCounts[bmiType]++;
        } else if (sex === "M" && maleCounts.hasOwnProperty(bmiType)) {
          maleCounts[bmiType]++;
        }
      });

      console.log("Final counts:", {
        female: femaleCounts,
        male: maleCounts,
      });

      // Colors for BMI categories with opacity for better visibility
      const colors = {
        "Severely Wasted": "rgba(255, 0, 0, 0.8)",
        Wasted: "rgba(255, 165, 0, 0.8)",
        Normal: "rgba(0, 128, 0, 0.8)",
        Obese: "rgba(255, 69, 0, 0.8)",
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

      console.log("Charts created successfully");
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
        "rgb(255, 0, 0)", // Red for Severely Wasted
        "rgb(255, 165, 0)", // Orange for Wasted
        "rgb(0, 128, 0)", // Green for Normal
        "rgb(255, 69, 0)", // Red-Orange for Obese
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
      ajax: {
        url: "../controllers/ReportController.php",
        type: "POST",
        data: function (d) {
          return {
            action: "getBMIDetails",
            startDate: $("#dateRangePicker").data("startDate") || "",
            endDate: $("#dateRangePicker").data("endDate") || "",
          };
        },
        dataSrc: function (response) {
          console.log("DataTable response:", response); // Debug log

          if (response.status === "success") {
            if (!Array.isArray(response.data)) {
              console.error("Invalid data format received:", response.data);
              return [];
            }

            return response.data.map((item) => ({
              ...item,
              // Ensure checkup_date is properly formatted or null
              checkup_date: item.checkup_date || null,
              // Ensure other fields have fallback values
              patient_id: item.patient_id || "N/A",
              age: item.age || null,
              finding_bmi: item.finding_bmi || "N/A",
            }));
          }

          console.error("Invalid response format:", response);
          return [];
        },
      },
      columns: [
        {
          data: "checkup_date",
          title: "Date",
          render: function (data, type, row) {
            // For sorting/filtering, return the original data
            if (type === "sort" || type === "filter") {
              return data;
            }

            // For display, format the date
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
        },
      ],
      order: [[0, "desc"]],
      pageLength: 10,
      lengthMenu: [
        [10, 25, 50, -1],
        [10, 25, 50, "All"],
      ],
      dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rtip',
      language: {
        search: "_INPUT_",
        searchPlaceholder: "Search records...",
      },
    });

    // Handle entries select
    $("#entriesSelect").on("change", function () {
      bmiTable.page.len($(this).val()).draw();
    });
  };

  // Function to load initial data
  const loadInitialData = () => {
    const yesterday = moment().subtract(1, "days");
    const startDate = yesterday.clone().startOf("day").format("YYYY-MM-DD");
    const endDate = yesterday.clone().endOf("day").format("YYYY-MM-DD");

    // Load data for overall chart
    fetchBMIData(startDate, endDate, function (data) {
      initBMIChart(data);
    });

    // Load data for gender charts
    fetchBMIData(startDate, endDate, function (data) {
      initGenderCharts(data);
    });

    // DataTable will automatically load with default dates
    if (bmiTable) {
      bmiTable.ajax.reload();
    }
  };

  // Initialize everything when the page loads
  initDateRangePickers();
  initDateRangeButtons();
  initDataTable();
  loadInitialData();

  // Make sure to handle tab switching properly
  $('button[data-bs-toggle="tab"]').on("shown.bs.tab", function (e) {
    if ($(e.target).attr("data-bs-target") === "#bmi-statistics") {
      // Delay chart rendering slightly to ensure the canvas is visible
      setTimeout(() => {
        // Refresh your charts here
        updateCharts();
      }, 100);
    }
  });
});
