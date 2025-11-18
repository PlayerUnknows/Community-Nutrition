class ReportManager {
  constructor() {
    this.charts = {};
    this.heightTable = null;

    // Wait for DOM to be ready
    $(document).ready(() => {
      try {
        this.initializeToastContainer();
        this.initializeTableDateRangePicker();
        this.initializeEventListeners();
        this.initializeTable();

        // Initialize charts with proper destruction
        if (window.reportData) {
          this.destroyExistingCharts();
          this.initializeCharts();
        }

        // Add export handlers
        this.initializeExportHandlers();
        
        // Additional initialization for search functionality
        this.ensureSearchFunctionality();
      } catch (error) {
   
      }
    });
  }

  initializeToastContainer() {
    if (!document.getElementById("heightToastContainer")) {
      const toastHTML = `
                <div id="heightToastContainer" class="position-fixed top-0 end-0 p-3" style="z-index: 1070">
                    <div id="heightDateRangeToast" class="toast align-items-center text-white bg-danger border-0" role="alert" aria-atomic="true">
                        <div class="d-flex">
                            <div class="toast-body"></div>
                            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                        </div>
                    </div>
                </div>`;
      document.body.insertAdjacentHTML("beforeend", toastHTML);
    }
  }

  showToast(message) {
    const toastElement = document.getElementById("heightDateRangeToast");
    if (toastElement) {
      const toastBody = toastElement.querySelector(".toast-body");
      if (toastBody) {
        toastBody.textContent = message;
        const toast = new bootstrap.Toast(toastElement, {
          animation: true,
          autohide: true,
          delay: 3000,
        });
        toast.show();
      }
    }
  }

  initializeTableDateRangePicker() {
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

    // Initialize growth status date range picker with yesterday's date
    $("#growthStatusDateRange").daterangepicker(dateRangeConfig);
    
    // Set default values to yesterday
    $("#growthStatusDateRange")
      .val(
        defaultStartDate.format("MM/DD/YYYY") +
          " - " +
          defaultEndDate.format("MM/DD/YYYY")
      )
      .data("startDate", defaultStartDate.format("YYYY-MM-DD"))
      .data("endDate", defaultEndDate.format("YYYY-MM-DD"));

    // Trigger the update immediately with yesterday's date
    this.updateGrowthStatusChart(
      defaultStartDate.format("YYYY-MM-DD"),
      defaultEndDate.format("YYYY-MM-DD")
    );

    // Handle date selection for growth status
    $("#growthStatusDateRange").on("apply.daterangepicker", (ev, picker) => {
      const startDate = picker.startDate.format("YYYY-MM-DD");
      const endDate = picker.endDate.format("YYYY-MM-DD");

      $(ev.target)
        .val(
          picker.startDate.format("MM/DD/YYYY") +
            " - " +
            picker.endDate.format("MM/DD/YYYY")
        )
        .data("startDate", startDate)
        .data("endDate", endDate);
    });

    // Handle clear for growth status
    $("#growthStatusDateRange").on("cancel.daterangepicker", (ev, picker) => {
      $(ev.target).val("");
      $(ev.target).data("startDate", "");
      $(ev.target).data("endDate", "");
      picker.setStartDate(moment());
      picker.setEndDate(moment());
    });

    // Handle apply button click for growth status
    $("#applyGrowthStatusDateRange").on("click", () => {
      const startDate = $("#growthStatusDateRange").data("startDate");
      const endDate = $("#growthStatusDateRange").data("endDate");

      if (!startDate || !endDate) {
        this.showToast("Please select a date range");
        return;
      }

      // First update the growth status chart
      this.updateGrowthStatusChart(startDate, endDate);
      
      // Wait longer before updating the gender distribution chart to avoid conflicts
      // Note: We've added this update directly to the growth status chart update method
      // so we don't need it here anymore, but keeping the comment for documentation
    });

    // Initialize table date range picker
    $("#heightTableDateRange").daterangepicker(dateRangeConfig);

    // Set default values
    $("#heightTableDateRange")
      .val(
        defaultStartDate.format("MM/DD/YYYY") +
          " - " +
          defaultEndDate.format("MM/DD/YYYY")
      )
      .data("startDate", defaultStartDate.format("YYYY-MM-DD"))
      .data("endDate", defaultEndDate.format("YYYY-MM-DD"));

    // Handle date selection
    $("#heightTableDateRange").on("apply.daterangepicker", (ev, picker) => {
      const startDate = picker.startDate.format("YYYY-MM-DD");
      const endDate = picker.endDate.format("YYYY-MM-DD");

      $(ev.target)
        .val(
          picker.startDate.format("MM/DD/YYYY") +
            " - " +
            picker.endDate.format("MM/DD/YYYY")
        )
        .data("startDate", startDate)
        .data("endDate", endDate);
    });

    // Handle clear
    $("#heightTableDateRange").on("cancel.daterangepicker", (ev, picker) => {
      $(ev.target).val("");
      $(ev.target).data("startDate", "");
      $(ev.target).data("endDate", "");
      picker.setStartDate(moment());
      picker.setEndDate(moment());
    });

    // Handle apply button click
    $("#applyHeightTableDateRange").on("click", () => {
      const startDate = $("#heightTableDateRange").data("startDate");
      const endDate = $("#heightTableDateRange").data("endDate");

      if (!startDate || !endDate) {
        this.showToast("Please select a date range");
        return;
      }

      if (this.heightTable) {
        this.heightTable.ajax.reload();
      }
    });
  }

  initializeEventListeners() {
    // Add date range picker listeners
    $("#startDate, #endDate").on("change", () => this.handleDateFilter());
    $("#dateRangeFilter").on("change", (e) =>
      this.handlePresetDateRange(e.target.value)
    );

    // Preview link click handler - use specific class for growth report
    $(document).on("click", ".growth-preview-link", (e) => {
      e.preventDefault();
      const type = $(e.currentTarget).data("type");
      this.handlePreview(type);
    });
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
        break;
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

  handleDateFilter() {
    const startDate = $("#startDate").val();
    const endDate = $("#endDate").val();

    // Make AJAX call to get filtered data
    $.ajax({
      url: "/src/api/report-data.php",
      method: "GET",
      data: { startDate, endDate },
      success: (response) => {
        if (response.success) {
          window.reportData = response.data;
          this.updateCharts();
        } else {
    
        }
      },
      error: (err) => {

      },
    });
  }

  updateCharts() {

    // Destroy existing charts before updating
    this.destroyExistingCharts();

    if (this.charts.growthTrends) {
      // Update growth trends chart
      const chartData = window.reportData.growthTrends.map((item) => ({
        date: item.created_at,
        height: parseFloat(item.height),
        idealHeight: this.calculateIdealHeight(item.age, item.sex),
      }));

      this.charts.growthTrends.data.labels = chartData.map((item) =>
        moment(item.date).format("MMM D, YYYY")
      );
      this.charts.growthTrends.data.datasets[0].data = chartData.map(
        (item) => item.height
      );
      this.charts.growthTrends.data.datasets[1].data = chartData.map(
        (item) => item.idealHeight
      );
      this.charts.growthTrends.update();
    }

    // Update the measurements table if DataTable is initialized
    const table = $("#measurementsTable").DataTable();
    if (table && window.reportData.dates) {
      table.clear();
      window.reportData.dates.forEach((date, index) => {
        table.row.add([
          moment(date).format("MMM D, YYYY"),
          window.reportData.heights[index],
          window.reportData.idealHeights[index],
          this.getHeightStatus(
            window.reportData.heights[index],
            window.reportData.idealHeights[index]
          ),
        ]);
      });
      table.draw();
    }

    // Update growth statistics table
    const tableBody = document.querySelector(".growth-stats-table tbody");
    if (tableBody && window.reportData.growthStatsByGender) {
      tableBody.innerHTML = "";
      window.reportData.growthStatsByGender.forEach((stat) => {
        const avgIdealHeight = this.calculateIdealHeight(
          parseFloat(stat.age_group.split("-")[0]),
          stat.gender
        );
        const row = document.createElement("tr");
        row.innerHTML = `
                    <td>${stat.age_group}</td>
                    <td>${stat.gender}</td>
                    <td>${parseFloat(stat.avg_height).toFixed(1)}</td>
                    <td>${avgIdealHeight.toFixed(1)}</td>
                    <td>${stat.total_patients}</td>
                `;
        tableBody.appendChild(row);
      });
    }
  }

  initializeCharts() {
    // Only initialize charts if the elements exist
    try {
      if (document.getElementById("growthTrendsChart")) {
        this.initializeGrowthTrendsChart();
      }
      if (document.getElementById("nutritionBarChart")) {
        this.initializeNutritionBarChart();
      }
      if (document.getElementById("genderDistributionChart")) {
        this.initializeGenderDistributionChart();
      }
    } catch (error) {

    }
  }

  initializeGrowthTrendsChart() {
   
    const canvas = document.getElementById("growthTrendsChart");
    if (!canvas) {

      return;
    }

    // Destroy existing chart first
    this.destroyChart("growthTrendsChart");

    // Process data for overall growth status counts
    const growthCounts = {
      Stunted: 0,
      Normal: 0,
      Over: 0,
    };

    // Count total for each status
    if (
      window.reportData &&
      window.reportData.data &&
      window.reportData.data.growthTrends
    ) {
   
      window.reportData.data.growthTrends.forEach((record) => {
        const findingGrowth = record.finding_growth
          ? record.finding_growth.toLowerCase()
          : "";
        if (findingGrowth.includes("stunted")) {
          growthCounts["Stunted"]++;
        } else if (findingGrowth.includes("normal")) {
          growthCounts["Normal"]++;
        } else if (findingGrowth.includes("over")) {
          growthCounts["Over"]++;
        }
      });
    }

    // Calculate total and percentages
    const total = Object.values(growthCounts).reduce((a, b) => a + b, 0);
    const percentages = {};
    Object.keys(growthCounts).forEach((key) => {
      percentages[key] =
        total > 0 ? ((growthCounts[key] / total) * 100).toFixed(1) : "0.0";
    });

    const ctx = canvas.getContext("2d");
    this.charts.growthTrends = new Chart(ctx, {
      type: "bar",
      data: {
        labels: Object.keys(growthCounts),
        datasets: [
          {
            data: Object.values(growthCounts),
            backgroundColor: [
              "rgba(220, 53, 69, 0.8)", // Red for Stunted
              "rgba(40, 167, 69, 0.8)", // Green for Normal
              "rgba(255, 193, 7, 0.8)", // Yellow for Over
            ],
            borderColor: [
              "rgb(220, 53, 69)",
              "rgb(40, 167, 69)",
              "rgb(255, 193, 7)",
            ],
            borderWidth: 1,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false,
          },
          title: {
            display: true,
            text: "Growth Status Distribution",
            font: {
              size: 16,
              weight: "bold",
            },
          },
          tooltip: {
            callbacks: {
              label: function (context) {
                const value = context.raw;
                const percentage = ((value / total) * 100).toFixed(1);
                return `Count: ${value} (${percentage}%)`;
              },
            },
          },
        },
        scales: {
          y: {
            beginAtZero: true,
            title: {
              display: true,
              text: "Number of Children",
            },
            ticks: {
              stepSize: 1,
            },
          },
        },
      },
    });

    // Update the growth status summary table
    this.updateGrowthStatusSummaryTable(growthCounts, percentages, total);
  }

  updateGrowthStatusSummaryTable(counts, percentages, total) {
    const summaryContainer = document.getElementById(
      "growthStatusSummaryContainer"
    );
    if (!summaryContainer) {
 
      return;
    }

    summaryContainer.innerHTML = `
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">Growth Status Summary</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Status</th>
                                    <th>Count</th>
                                    <th>Percentage</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><span class="badge bg-danger">Stunted</span></td>
                                    <td class="text-center">${counts["Stunted"]}</td>
                                    <td class="text-center">${percentages["Stunted"]}%</td>
                                </tr>
                                <tr>
                                    <td><span class="badge bg-success">Normal</span></td>
                                    <td class="text-center">${counts["Normal"]}</td>
                                    <td class="text-center">${percentages["Normal"]}%</td>
                                </tr>
                                <tr>
                                    <td><span class="badge bg-warning">Over</span></td>
                                    <td class="text-center">${counts["Over"]}</td>
                                    <td class="text-center">${percentages["Over"]}%</td>
                                </tr>
                                <tr class="table-active fw-bold">
                                    <td>Total</td>
                                    <td class="text-center">${total}</td>
                                    <td class="text-center">100%</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        `;
  }

  initializeNutritionBarChart() {
    const canvas = document.getElementById("nutritionBarChart");
    if (!canvas) return;

    const ctx = canvas.getContext("2d");
    this.charts.nutritionBar = new Chart(ctx, {
      type: "bar",
      data: {
        labels: [
          "Center 1",
          "Center 2",
          "Center 3",
          "Center 4",
          "Center 5",
          "Center 6",
        ],
        datasets: [
          {
            label: "Number of Patients",
            data: [65, 59, 80, 81, 56, 55],
            backgroundColor: [
              "rgba(255, 99, 132, 0.2)",
              "rgba(54, 162, 235, 0.2)",
              "rgba(255, 206, 86, 0.2)",
              "rgba(75, 192, 192, 0.2)",
              "rgba(153, 102, 255, 0.2)",
              "rgba(255, 159, 64, 0.2)",
            ],
            borderColor: [
              "rgba(255, 99, 132, 1)",
              "rgba(54, 162, 235, 1)",
              "rgba(255, 206, 86, 1)",
              "rgba(75, 192, 192, 1)",
              "rgba(153, 102, 255, 1)",
              "rgba(255, 159, 64, 1)",
            ],
            borderWidth: 1,
          },
        ],
      },
      options: {
        responsive: true,
        plugins: {
          legend: {
            position: "top",
          },
          title: {
            display: true,
            text: "Patient Distribution Across Centers",
          },
        },
        scales: {
          y: {
            beginAtZero: true,
          },
        },
      },
    });
  }

  calculateIdealHeight(age, gender) {
    // WHO/CDC growth standards for children 0-14 years
    const idealHeights = {
      male: {
        0: 50.0, // 0-3 months
        0.25: 61.4, // 3-6 months
        0.5: 67.6, // 6-9 months
        0.75: 72.3, // 9-12 months
        1: 75.7,
        2: 87.1,
        3: 96.1,
        4: 103.3,
        5: 110.0,
        6: 116.1,
        7: 121.7,
        8: 127.3,
        9: 132.6,
        10: 137.8,
        11: 143.1,
        12: 149.1,
        13: 156.2,
        14: 163.8,
      },
      female: {
        0: 49.1, // 0-3 months
        0.25: 60.2, // 3-6 months
        0.5: 66.1, // 6-9 months
        0.75: 71.0, // 9-12 months
        1: 74.3,
        2: 85.7,
        3: 94.9,
        4: 102.0,
        5: 108.4,
        6: 114.6,
        7: 120.3,
        8: 126.0,
        9: 131.7,
        10: 137.5,
        11: 143.8,
        12: 150.0,
        13: 155.7,
        14: 159.8,
      },
    };

    const normalizedGender =
      gender.toLowerCase() === "m" || gender.toLowerCase() === "male"
        ? "male"
        : "female";
    const exactAge = parseFloat(age);

    // Find the closest age bracket
    const ages = Object.keys(idealHeights[normalizedGender]).map(Number);
    const closestAge = ages.reduce((prev, curr) => {
      return Math.abs(curr - exactAge) < Math.abs(prev - exactAge)
        ? curr
        : prev;
    });

    return idealHeights[normalizedGender][closestAge];
  }

  initializeTable() {
    try {
      // First check if the table element exists
      if (!document.getElementById("measurementsTable")) {

        return;
      }

      if ($.fn.DataTable.isDataTable("#measurementsTable")) {
        $("#measurementsTable").DataTable().destroy();
      }

      // Initialize DataTable
      this.heightTable = $("#measurementsTable").DataTable({
        processing: true,
        serverSide: false,
        responsive: true,
        ajax: {
          url: "../controllers/ReportController.php",
          type: "POST",
          data: function (d) {
            return {
              ...d, // Include all DataTables parameters
              action: "getHeightData",
              startDate: $("#growthStatusDateRange").data("startDate") || "",
              endDate: $("#growthStatusDateRange").data("endDate") || "",
            };
          },
          dataSrc: function (response) {
            if (response.status === "success" && Array.isArray(response.data)) {
              return response.data.map(
                function (item) {
                  return {
                    date: moment(item.measurement_date).format("MMM DD, YYYY"),
                    patient_name: item.patient_name || '',
                    age: (item.age ? item.age + " years" : ''),
                    sex: item.gender || '',
                    height: parseFloat(item.height || 0).toFixed(1),
                    ideal_height: this.calculateIdealHeight(
                      item.age || 0,
                      item.gender || ''
                    ).toFixed(1),
                    status: item.status || '',
                  };
                }.bind(this)
              );
            }
            return [];
          }.bind(this),
        },
        columns: [
          { data: "date" },
          { data: "patient_name" },
          { data: "age" },
          { data: "sex" },
          { data: "height" },
          { data: "ideal_height" },
          {
            data: "status",
            render: function (data, type, row) {
              if (type === "display") {
                let className = "";
                // Add null check before calling toLowerCase()
                const status = data ? data.toLowerCase() : '';
                
                if (status.includes("normal")) {
                  className = "bg-success";
                } else if (status.includes("stunted")) {
                  className = "bg-danger";
                } else if (status.includes("over")) {
                  className = "bg-warning";
                }
                return (
                  '<span class="status-badge ' +
                  className +
                  '">' +
                  (data || 'Unknown') +
                  "</span>"
                );
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
        // Enhanced search functionality
        search: {
          // Make search case-insensitive and match any part of the text
          caseInsensitive: true,
          regex: false,
          smart: true
        },
        language: {
          emptyTable: "No data available for the selected date range",
          zeroRecords: "No matching records found",
          info: "Showing _START_ to _END_ of _TOTAL_ entries",
          infoEmpty: "Showing 0 to 0 of 0 entries",
          infoFiltered: "(filtered from _MAX_ total entries)",
          search: "Search:"
        },
        // Custom search function to search across multiple columns
        initComplete: function() {
          // Remove any existing search function to avoid duplicates
          while ($.fn.dataTable.ext.search.length > 0) {
            $.fn.dataTable.ext.search.pop();
          }
          
          // Add new search logic with additional safety checks
          $.fn.dataTable.ext.search.push(
            function(settings, data, dataIndex) {
              // Only apply to this specific table
              if (settings.nTable.id !== "measurementsTable") {
                return true;
              }
              
              // Safely get the search value with null check
              const searchElement = document.getElementById("heightTableSearch");
              if (!searchElement) {
                return true; // If search element doesn't exist, show all rows
              }
              
              const searchInput = $(searchElement).val();
              const searchVal = searchInput ? searchInput.toLowerCase() : '';
              
              // When search is empty, always show all rows
              if (!searchVal || searchVal.trim() === '') {
                return true;
              }
              
              // Search in all columns
              for (let i = 0; i < data.length; i++) {
                const cellData = data[i] ? data[i].toString().toLowerCase() : '';
                if (cellData.includes(searchVal)) {
                  return true;
                }
              }
              return false;
            }
          );
          
          // Add a method to reset search properly
          $.fn.DataTable.ext.search.resetSearch = function() {
            const searchElement = document.getElementById("heightTableSearch");
            if (searchElement) {
              $(searchElement).val('');
            }
            
            const table = $("#measurementsTable").DataTable();
            if (table) {
              table.search('').columns().search('').draw();
            }
          };
        }
      });

      this.initializeSearchFunctionality();

      // Add event listener for date range changes
      const dateRangeElement = document.getElementById("growthStatusDateRange");
      if (dateRangeElement) {
        $(dateRangeElement).off("apply.daterangepicker").on("apply.daterangepicker", (ev, picker) => {
          const startDate = picker.startDate.format("YYYY-MM-DD");
          const endDate = picker.endDate.format("YYYY-MM-DD");

          $(ev.target).data("startDate", startDate).data("endDate", endDate);

          if (this.heightTable) {
            this.heightTable.ajax.reload();
          }
        });
      }

      return this.heightTable;
    } catch (error) {

    }
  }

  initializeSearchFunctionality() {
    try {
      // Check if search elements exist before attaching handlers
      const searchElement = document.getElementById("heightTableSearch");
      const entriesElement = document.getElementById("heightTableEntriesSelect");
      const clearElement = document.getElementById("clearSearch");

      if (searchElement) {
        // Remove any existing event handlers to prevent duplicates
        $(searchElement).off();
        
        // Add the keyup handler with safety checks
        $(searchElement).on("keyup", function(e) {
          if (!this.heightTable) return;
          
          // Safely get the value
          const searchVal = $(searchElement).val() || '';
          
          // Apply the search
          this.heightTable.search(searchVal).draw();
          
          // If search is cleared, reload the table data to ensure it's reset
          if (searchVal === '') {
            if (this.heightTable && this.heightTable.ajax) {
              this.heightTable.ajax.reload();
            }
          }
          
          // Handle Enter key
          if (e.keyCode === 13 && document.getElementById("measurementsTable")) {
            $("#measurementsTable").focus();
          }
        }.bind(this));
        
        // Clear the search field initially
        $(searchElement).val('');
      } else {
 
      }

      // Handle entries select if it exists
      if (entriesElement && this.heightTable) {
        $(entriesElement).off().on("change", function() {
          if (!this.heightTable) return;
          
          const val = $(entriesElement).val();
          this.heightTable.page.len(parseInt(val)).draw();
        }.bind(this));
      }

      // Handle clear search button if it exists
      if (clearElement && searchElement && this.heightTable) {
        $(clearElement).off().on("click", function() {
          // First clear the search input
          $(searchElement).val('');
          
          // Use our custom reset search method if available
          if ($.fn.DataTable.ext.search.resetSearch) {
            $.fn.DataTable.ext.search.resetSearch();
          } else {
            // Fallback to standard clearing
            if (this.heightTable) {
              this.heightTable.search('').columns().search('').draw();
              
              // Also reload the table data to ensure complete reset
              if (this.heightTable.ajax) {
                this.heightTable.ajax.reload();
              }
            }
          }
          
          // Force reload to ensure data is refreshed
          if (this.heightTable && this.heightTable.ajax) {
            this.heightTable.ajax.reload();
          }
        }.bind(this));
      }
      
      // Add a visible clear button next to the search box if it doesn't exist
      if (searchElement && !clearElement) {
        const clearBtn = $('<button>')
          .attr('id', 'clearSearch')
          .addClass('btn btn-sm btn-outline-secondary ms-1')
          .html('<i class="fas fa-times"></i>')
          .on('click', function() {
            $(searchElement).val('');
            if (this.heightTable) {
              this.heightTable.search('').columns().search('').draw();
              if (this.heightTable.ajax) {
                this.heightTable.ajax.reload();
              }
            }
          }.bind(this));
          
        $(searchElement).after(clearBtn);
      }

      // Hide DataTables search box if it exists
      if ($(".dataTables_filter").length) {
        $(".dataTables_filter").hide();
      }
    } catch (error) {

    }
  }

  getHeightStatus(status) {
    let className;
    // Add null check before calling toLowerCase()
    const statusLower = status ? status.toLowerCase() : '';
    
    switch (statusLower) {
      case "stunted":
        className = "bg-danger";
        break;
      case "normal":
        className = "bg-success";
        break;
      case "over":
        className = "bg-warning";
        break;
      default:
        className = "bg-secondary";
    }
    return `<span class="status-badge ${className}">${status || 'Unknown'}</span>`;
  }

  // Add this new method to safely destroy charts
  destroyChart(chartId) {
    try {
      // First clean up any existing chart in our registry
      if (chartId === "growthTrendsChart" && this.charts.growthTrends) {
        this.charts.growthTrends.destroy();
        this.charts.growthTrends = null;
      } else if (chartId === "genderDistributionChart" && this.charts.genderDistribution) {
        this.charts.genderDistribution.destroy();
        this.charts.genderDistribution = null;
      } else if (chartId === "nutritionBarChart" && this.charts.nutritionBar) {
        this.charts.nutritionBar.destroy();
        this.charts.nutritionBar = null;
      }
      
      // Additionally, check Chart.js registry
      const existingChart = Chart.getChart(chartId);
      if (existingChart) {
        existingChart.destroy();
      }
      
      // Force clean up on the canvas
      try {
        const canvas = document.getElementById(chartId);
        if (canvas) {
          // Create a fresh canvas
          const parent = canvas.parentNode;
          if (parent) {
            const newCanvas = document.createElement('canvas');
            newCanvas.id = chartId;
            newCanvas.width = canvas.width;
            newCanvas.height = canvas.height;
            newCanvas.className = canvas.className;
            
            // Replace old canvas with new one
            canvas.remove();
            parent.appendChild(newCanvas);
          }
        }
      } catch (e) {
    
      }
      
      return true;
    } catch (error) {

      return false;
    }
  }
  
  // Update this method to destroy all existing charts
  destroyExistingCharts() {
    try {
      // Always force garbage collection
      if (this.charts.growthTrends) {
        this.charts.growthTrends.destroy();
        this.charts.growthTrends = null;
      }
      
      if (this.charts.genderDistribution) {
        this.charts.genderDistribution.destroy();
        this.charts.genderDistribution = null;
      }
      
      
      if (this.charts.nutritionBar) {
        this.charts.nutritionBar.destroy();
        this.charts.nutritionBar = null;
      }
      
      // Also check Chart.js registry for each canvas
      this.destroyChart("growthTrendsChart");
      this.destroyChart("genderDistributionChart");
      this.destroyChart("nutritionBarChart");
    } catch (error) {
  
    }
  }

  // Update the updateGrowthStatusChart method
  updateGrowthStatusChart(startDate, endDate) {
    // Don't proceed if no dates
    if (!startDate || !endDate) {
  
      return;
    }
    
    $.ajax({
      url: "../controllers/ReportController.php",
      type: "POST",
      data: {
        action: "getGrowthStatsByAgeAndGender",
        startDate: startDate,
        endDate: endDate,
      },
      success: (response) => {
        try {
          // Make sure we properly destroy the existing chart
          this.destroyChart("growthTrendsChart");
          
          const data =
            typeof response === "string" ? JSON.parse(response) : response;
          if (data.status === "success" && Array.isArray(data.data)) {
            // Update the growth statistics table
            const tableBody = document.querySelector(
              ".growth-stats-table tbody"
            );
            if (tableBody) {
              tableBody.innerHTML = "";
              
              // Calculate total patients
              let totalPatients = 0;
              data.data.forEach((stat) => {
                totalPatients += parseInt(stat.total_patients);
              });
              
              // Update total in table footer
              const totalElement = document.getElementById("growthStatsTotal");
              if (totalElement) {
                totalElement.textContent = totalPatients;
              }
              
              // Add rows to table
              data.data.forEach((stat) => {
                const row = document.createElement("tr");
                row.innerHTML = `
                    <td>${stat.age_group}</td>
                    <td>${stat.gender}</td>
                    <td>${parseFloat(stat.avg_height).toFixed(1)}</td>
                    <td>${stat.total_patients}</td>
                `;
                tableBody.appendChild(row);
              });
            }

            // Check if the canvas exists
            const canvas = document.getElementById("growthTrendsChart");
            if (!canvas) {
      
              return;
            }

            // Force canvas replacement to prevent reuse errors
            if (canvas.parentNode) {
              const parent = canvas.parentNode;
              const newCanvas = document.createElement('canvas');
              newCanvas.id = "growthTrendsChart";
              newCanvas.width = canvas.width || 400;
              newCanvas.height = canvas.height || 300;
              newCanvas.className = canvas.className || '';
              canvas.remove();
              parent.appendChild(newCanvas);
            } else {
        
              return;
            }

            // Process data for chart
            const statusCounts = {
              Stunted: 0,
              Normal: 0,
              Over: 0,
            };

            // Count each record's status based on finding_growth
            data.data.forEach((record) => {
              const totalPatients = parseInt(record.total_patients);
              const status = record.status ? record.status.toLowerCase() : '';

              if (status.includes('stunted')) {
                statusCounts.Stunted += totalPatients;
              } else if (status.includes('normal')) {
                statusCounts.Normal += totalPatients;
              } else if (status.includes('over')) {
                statusCounts.Over += totalPatients;
              }
            });

            const total = Object.values(statusCounts).reduce(
              (a, b) => a + b,
              0
            );
            const percentages = {};
            Object.keys(statusCounts).forEach((key) => {
              percentages[key] =
                total > 0
                  ? ((statusCounts[key] / total) * 100).toFixed(1)
                  : "0.0";
            });

            // Use setTimeout to ensure DOM is ready
            setTimeout(() => {
              try {
                // Double-check the canvas still exists
                const canvasCheck = document.getElementById("growthTrendsChart");
                if (!canvasCheck) {
            
                  return;
                }
                
                // Check if there are any existing charts for this canvas
                const existingChart = Chart.getChart("growthTrendsChart");
                if (existingChart) {
                  existingChart.destroy();
                }
                
                const ctx = canvasCheck.getContext("2d");
                if (!ctx) {
           
                  return;
                }
                
                this.charts.growthTrends = new Chart(ctx, {
                  type: "bar",
                  data: {
                    labels: Object.keys(statusCounts),
                    datasets: [
                      {
                        data: Object.values(statusCounts),
                        backgroundColor: [
                          "rgba(220, 53, 69, 0.8)", // Red for Stunted
                          "rgba(40, 167, 69, 0.8)", // Green for Normal
                          "rgba(255, 193, 7, 0.8)", // Yellow for Over
                        ],
                        borderColor: [
                          "rgb(220, 53, 69)",
                          "rgb(40, 167, 69)",
                          "rgb(255, 193, 7)",
                        ],
                        borderWidth: 1,
                      },
                    ],
                  },
                  options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                      legend: {
                        display: false,
                      },
                      title: {
                        display: true,
                        text: "Growth Status Distribution",
                        font: {
                          size: 16,
                          weight: "bold",
                        },
                      },
                      tooltip: {
                        callbacks: {
                          label: function (context) {
                            const value = context.raw;
                            const percentage = ((value / total) * 100).toFixed(1);
                            return `Count: ${value} (${percentage}%)`;
                          },
                        },
                      },
                    },
                    scales: {
                      y: {
                        beginAtZero: true,
                        title: {
                          display: true,
                          text: "Number of Children",
                        },
                        ticks: {
                          stepSize: 1,
                        },
                      },
                    },
                  },
                });
              } catch (chartError) {
           
              }
            }, 200);
            
            // Update summary table
            this.updateGrowthStatusSummaryTable(
              statusCounts,
              percentages,
              total
            );
            
            // Also update the gender distribution chart with the same date range
            // Use a slightly longer delay to avoid conflicts
            setTimeout(() => {
              if (document.getElementById("genderDistributionChart")) {
    
                this.updateGenderDistributionChart(startDate, endDate);
              }
            }, 500);
          } else {
    
            this.showToast("Failed to load growth statistics data");
          }
        } catch (error) {
 
          this.showToast("Error processing growth statistics data");
        }
      },
      error: (xhr, status, error) => {

        this.showToast("Failed to fetch growth statistics data");
      },
    });
  }

  initializeExportHandlers() {
    // Remove any existing event handlers first - scoped to nutrition report only
    $(document).off("click", "#nutrition-report [data-export]");

    // Handle export dropdown clicks - scoped to nutrition report only
    $(document).on("click", "#nutrition-report [data-export]", (e) => {
      e.preventDefault();
      e.stopPropagation(); // Prevent event bubbling

      const exportType = $(e.currentTarget).data("export");
      const contentType = $(e.currentTarget).data("type");

      // For growth-chart exports, ensure the chart exists and is initialized
      if (contentType === "growth-chart") {
        const chartCanvas = document.getElementById("growthTrendsChart");
        if (!chartCanvas || !this.charts.growthTrends) {
          this.showToast(
            "Chart is not ready. Please wait a moment and try again."
          );
          return;
        }
      }

      this.handleExport(exportType, contentType);
    });
  }

  getExportData(contentType) {
    let exportData = {};

    switch (contentType) {
      case "growth-chart":
        return new Promise((resolve) => {
          // Get the chart canvas
          const chartCanvas = document.getElementById("growthTrendsChart");
          if (!chartCanvas) {
      
            resolve(null);
            return;
          }

          // Force chart update to ensure latest data
          if (this.charts.growthTrends) {
            this.charts.growthTrends.update();
          }

          // Use requestAnimationFrame to ensure chart is rendered
          requestAnimationFrame(() => {
            try {
              const chartImage = chartCanvas.toDataURL("image/png", 1.0);

              // Get summary data
              const chartData = [];
              const summaryTable = document.querySelector(
                "#growthStatusSummaryContainer table tbody"
              );
              if (summaryTable) {
                const rows = summaryTable.querySelectorAll(
                  "tr:not(.table-active)"
                );
                rows.forEach((row) => {
                  const cells = row.querySelectorAll("td");
                  if (cells.length >= 3) {
                    const status = cells[0]
                      .querySelector(".badge")
                      .textContent.trim();
                    const count = parseInt(cells[1].textContent.trim());
                    chartData.push({
                      status: status,
                      count: count,
                    });
                  }
                });
              }

              resolve({
                title: "Growth Status Distribution",
                chartImage: chartImage,
                data: chartData,
                date_range: $("#growthStatusDateRange").val(),
              });
            } catch (error) {
      
              resolve(null);
            }
          });
        });
        
      case "gender-distribution":
        return new Promise((resolve) => {
          // Get the chart canvas
          const chartCanvas = document.getElementById("genderDistributionChart");
          if (!chartCanvas) {
      
            resolve(null);
            return;
          }

          // Force chart update to ensure latest data
          if (this.charts.genderDistribution) {
            this.charts.genderDistribution.update();
          }

          // Use requestAnimationFrame to ensure chart is rendered
          requestAnimationFrame(() => {
            try {
              const chartImage = chartCanvas.toDataURL("image/png", 1.0);

              // Get summary data
              const chartData = [];
              const summaryTable = document.querySelector(
                "#genderDistributionSummary"
              );
              if (summaryTable) {
                const rows = summaryTable.querySelectorAll("tr");
                rows.forEach((row) => {
                  const cells = row.querySelectorAll("td");
                  if (cells.length >= 3) {
                    const gender = cells[0]
                      .querySelector(".badge")
                      .textContent.trim();
                    const count = parseInt(cells[1].textContent.trim());
                    chartData.push({
                      gender: gender,
                      count: count,
                    });
                  }
                });
              }

              resolve({
                title: "Gender Distribution",
                chartImage: chartImage,
                data: chartData,
                date_range: $("#genderDistributionDateRange").val(),
              });
            } catch (error) {
            
              resolve(null);
            }
          });
        });

      case "growth-summary":
        const summaryData = [];
        const growthSummaryTable = document.querySelector(
          "#growthStatusSummaryContainer table tbody"
        );
        if (growthSummaryTable) {
          const rows = growthSummaryTable.querySelectorAll(
            "tr:not(.table-active)"
          );
          rows.forEach((row) => {
            const cells = row.querySelectorAll("td");
            if (cells.length >= 3) {
              summaryData.push({
                status: cells[0].querySelector(".badge").textContent.trim(),
                count: parseInt(cells[1].textContent.trim()),
                percentage: parseFloat(cells[2].textContent),
              });
            }
          });
        }
        exportData = {
          title: "Growth Status Summary",
          data: summaryData,
          date_range: $("#growthStatusDateRange").val(),
        };
        break;

      case "growth-statistics":
        const statsData = [];
        const statsTable = document.querySelector(".growth-stats-table tbody");
        if (statsTable) {
          statsTable.querySelectorAll("tr").forEach((row) => {
            const cells = row.querySelectorAll("td");
            if (cells.length >= 4) {
              statsData.push({
                age_group: cells[0].textContent.trim(),
                gender: cells[1].textContent.trim(),
                avg_height: parseFloat(cells[2].textContent.trim()),
                total_patients: parseInt(cells[3].textContent.trim()),
              });
            }
          });
        }
        
        // Add overall total
        const totalElement = document.getElementById("growthStatsTotal");
        const totalPatients = totalElement ? parseInt(totalElement.textContent) : 0;
        
        exportData = {
          title: "Growth Statistics by Age & Gender",
          data: statsData,
          total_patients: totalPatients,
          date_range: $("#growthStatusDateRange").val(),
        };
        break;

      case "height-measurements":
        const measurementsData = [];
        if (this.heightTable) {
          // Get data from DataTable and ensure proper formatting
          const tableData = this.heightTable.data().toArray();
          tableData.forEach((row) => {
            measurementsData.push({
              date: row.date || "",
              patientName: row.patientName || "",
              age: row.age || "",
              sex: row.sex || "",
              currentHeight: row.currentHeight || "",
              status:
                typeof row.status === "string"
                  ? row.status.replace(/<[^>]*>/g, "")
                  : "",
            });
          });
        }
        exportData = {
          title: "Height Measurements History",
          data: measurementsData,
          date_range: $("#heightTableDateRange").val(),
        };
        break;

      case "all":
        exportData = {
          title: "Complete Nutrition Report",
          growth_chart: this.getExportData("growth-chart"),
          gender_distribution: this.getExportData("gender-distribution"),
          growth_summary: this.getExportData("growth-summary"),
          growth_statistics: this.getExportData("growth-statistics"),
          height_measurements: this.getExportData("height-measurements"),
          date_range: $("#growthStatusDateRange").val(),
        };
        break;
    }
    return exportData;
  }

  handleExport(exportType, contentType) {
    this.showToast(`Preparing ${exportType.toUpperCase()} export...`);

    try {
      Promise.resolve(this.getExportData(contentType))
        .then((exportData) => {
          if (!exportData) {
            throw new Error("Failed to prepare export data");
          }

          const formData = new FormData();
          formData.append("action", "exportReport");
          formData.append("exportType", exportType);
          formData.append("contentType", contentType);
          formData.append("data", JSON.stringify(exportData));

          return fetch("../controllers/ReportController.php", {
            method: "POST",
            body: formData,
          });
        })
        .then((response) => {
          if (!response.ok) {
            return response.json().then((err) => {
              throw new Error(err.error || "Export failed");
            });
          }
          return response.blob();
        })
        .then((blob) => {
          const url = window.URL.createObjectURL(blob);
          const a = document.createElement("a");
          a.style.display = "none";
          a.href = url;
          a.download = this.getExportFileName(exportType, contentType);
          document.body.appendChild(a);
          a.click();
          window.URL.revokeObjectURL(url);
          document.body.removeChild(a);
          this.showToast("Export completed successfully!");
        })
        .catch((error) => {
          this.showToast(error.message || "Export failed. Please try again.");
        });
    } catch (error) {
      this.showToast("Failed to prepare export data");
    }
  }

  getExportFileName(exportType, contentType) {
    const date = moment().format("YYYY-MM-DD");
    const type = contentType.replace(/-/g, "_");
    const extension = exportType === "excel" ? "xlsx" : exportType;
    return `nutrition_report_${type}_${date}.${extension}`;
  }

  // New method to ensure search functionality is properly initialized
  ensureSearchFunctionality() {
    // First attempt to initialize
    this.initializeSearchFunctionality();
    
    // Add a delay to re-initialize in case DOM elements loaded after initial load
    setTimeout(() => {
      if (document.getElementById("heightTableSearch")) {
        this.initializeSearchFunctionality();
        
        // Make sure the search box is empty
        $("#heightTableSearch").val('');
        
        // If table exists, make sure search is cleared
        if (this.heightTable) {
          this.heightTable.search('').draw();
        }
      }
    }, 1000);
  }

  /**
   * Initialize gender distribution pie chart
   */
  initializeGenderDistributionChart() {
    try {
      // Use the recreation method with empty data
      this.recreateGenderDistributionChart({Male: 0, Female: 0});
      
      // Get the date range values from the growth status date range picker
      const startDate = $("#growthStatusDateRange").data("startDate");
      const endDate = $("#growthStatusDateRange").data("endDate");
      
      // Fetch actual data if we have dates, but use setTimeout to ensure chart is fully ready
      if (startDate && endDate) {
        setTimeout(() => {
          // Check if the canvas still exists before trying to update
          if (document.getElementById("genderDistributionChart")) {
            this.updateGenderDistributionChart(startDate, endDate);
          }
        }, 300);
      } else {
        // If no dates available, just update with empty data to show the chart structure
        const emptyCounts = {Male: 0, Female: 0};
        const emptyPercentages = {Male: "0.0", Female: "0.0"};
        this.updateGenderDistributionSummaryTable(emptyCounts, emptyPercentages, 0);
      }
    } catch (error) {

    }
  }
  
  /**
   * Update gender distribution chart with data from server
   */
  updateGenderDistributionChart(startDate, endDate) {
    // Don't proceed if no dates
    if (!startDate || !endDate) {
      return;
    }
    
    // Check if canvas exists first
    const chartCanvas = document.getElementById("genderDistributionChart");
    if (!chartCanvas) {
      return;
    }
    
    
    $.ajax({
      url: "../controllers/ReportController.php",
      type: "POST",
      data: {
        action: "getGenderDistribution",
        startDate: startDate,
        endDate: endDate,
      },
      success: (response) => {

        try {
          // Parse the response if it's a string
          let data;
          try {
            data = typeof response === "string" ? JSON.parse(response) : response;
 
          } catch (parseError) {

            this.showToast("Error parsing gender distribution data");
            return;
          }
          
          if (!data || typeof data !== 'object') {
      
            this.showToast("Invalid gender distribution data format");
            return;
          }
          
          if (data.status === "success") {
            // Process data - get gender distribution from the data object
            const genderData = data.data || {};
        
            
            // Extract only the actual gender data (non-total entries)
            let genderArray = [];
            let totalCount = 0;
            
            // Handle the case where the data is an array with a 'total' property
            if (Array.isArray(genderData)) {
              // Filter out the 'total' property
              genderArray = genderData.filter(item => typeof item === 'object' && item.gender);
              
              // Get the total directly from the 'total' property if it exists
              if (genderData.total !== undefined) {
                totalCount = parseInt(genderData.total || 0);
              } else {
                // Otherwise calculate from the array
                totalCount = genderArray.reduce((sum, item) => sum + parseInt(item.count || 0), 0);
              }
              

            } else if (genderData && typeof genderData === 'object') {
              // Handle object format, separating real data from total
              for (const key in genderData) {
                if (key === 'total') {
                  totalCount = parseInt(genderData[key] || 0);
                } else if (!isNaN(parseInt(key))) {
                  // If key is numeric, it's part of the array data
                  const item = genderData[key];
                  if (item && typeof item === 'object' && item.gender) {
                    genderArray.push(item);
                  }
                }
              }
            }
            
            
            const genderCounts = {
              Male: 0,
              Female: 0,
            };
            
            // Process the gender counts
            genderArray.forEach(item => {
              if (item && typeof item === 'object') {
                if (item.gender === 'Male') {
                  genderCounts.Male = parseInt(item.count || 0);
                } else if (item.gender === 'Female') {
                  genderCounts.Female = parseInt(item.count || 0);
                }
              }
            });
            
            
            // Calculate percentages based on gender counts
            const calculatedTotal = genderCounts.Male + genderCounts.Female;
            // Use the calculated total if the totalCount isn't valid
            if (!totalCount || isNaN(totalCount) || totalCount < calculatedTotal) {
              totalCount = calculatedTotal;
            }
            
            const percentages = {};
            Object.keys(genderCounts).forEach((key) => {
              percentages[key] = totalCount > 0 ? ((genderCounts[key] / totalCount) * 100).toFixed(1) : "0.0";
            });
            
            
            // Update summary table first - this doesn't need the chart to be ready
            this.updateGenderDistributionSummaryTable(genderCounts, percentages, totalCount);
            
            // Use a more reliable approach for chart update - recreate the chart
            this.recreateGenderDistributionChart(genderCounts);
          } else {
       
            this.showToast("Failed to load gender distribution data: " + (data.message || "Unknown error"));
          }
        } catch (error) {
         
          this.showToast("Error processing gender distribution data");
        }
      },
      error: (xhr, status, error) => {
  
        this.showToast("Failed to fetch gender distribution data");
      },
    });
  }
  
  /**
   * Recreate the gender distribution chart with new data
   */
  recreateGenderDistributionChart(genderCounts) {
    try {
      // Ensure we have valid counts to display
      if (!genderCounts) genderCounts = {Male: 0, Female: 0};
      

      
      // First destroy any existing chart
      if (this.charts.genderDistribution) {
        this.charts.genderDistribution.destroy();
        this.charts.genderDistribution = null;
      }
      
      // Find the canvas and its parent
      const canvas = document.getElementById("genderDistributionChart");
      if (!canvas) {
 
        return;
      }
      
      const parent = canvas.parentNode;
      if (!parent) {
     
        return;
      }
      
      // Replace the canvas with a new one
      const newCanvas = document.createElement('canvas');
      newCanvas.id = "genderDistributionChart";
      newCanvas.width = canvas.width || 400;
      newCanvas.height = canvas.height || 300;
      newCanvas.className = canvas.className || '';
      
      // Replace the old canvas with the new one
      canvas.remove();
      parent.appendChild(newCanvas);
      
      // Get the context of the new canvas
      const ctx = newCanvas.getContext("2d");
      if (!ctx) {

        return;
      }
      
      // Ensure data has valid values
      const maleCount = parseInt(genderCounts.Male || 0);
      const femaleCount = parseInt(genderCounts.Female || 0);
      const totalCount = maleCount + femaleCount;
      
      // Create a new chart
      this.charts.genderDistribution = new Chart(ctx, {
        type: "pie",
        data: {
          labels: ["Male", "Female"],
          datasets: [
            {
              data: [maleCount, femaleCount],
              backgroundColor: [
                "rgba(54, 162, 235, 0.8)",  // Blue for Male
                "rgba(255, 99, 132, 0.8)",  // Pink for Female
              ],
              borderColor: [
                "rgb(54, 162, 235)",
                "rgb(255, 99, 132)",
              ],
              borderWidth: 1,
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
                font: {
                  size: 14
                }
              }
            },
            title: {
              display: true,
              text: totalCount > 0 ? "Gender Distribution" : "No data available for selected date range",
              font: {
                size: 16,
                weight: "bold",
              },
            },
            tooltip: {
              callbacks: {
                label: function (context) {
                  const value = context.raw;
                  const total = context.chart.data.datasets[0].data.reduce((a, b) => a + b, 0);
                  const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : "0.0";
                  return `${context.label}: ${value} (${percentage}%)`;
                },
              },
            },
          },
        },
      });
    } catch (error) {
  
    }
  }
  
  /**
   * Update the gender distribution summary table
   */
  updateGenderDistributionSummaryTable(counts, percentages, total) {
    try {
      const summaryElement = document.getElementById("genderDistributionSummary");
      const totalElement = document.getElementById("genderDistributionTotal");
      
      // Ensure we have valid data to display
      if (!counts) counts = {Male: 0, Female: 0};
      if (!percentages) {
        const calculatedTotal = (counts.Male || 0) + (counts.Female || 0);
        percentages = {
          Male: calculatedTotal > 0 ? ((counts.Male || 0) / calculatedTotal * 100).toFixed(1) : "0.0",
          Female: calculatedTotal > 0 ? ((counts.Female || 0) / calculatedTotal * 100).toFixed(1) : "0.0"
        };
      }
      if (total === undefined || total === null || isNaN(total)) {
        total = (counts.Male || 0) + (counts.Female || 0);
      }
      

      
      if (summaryElement) {
        summaryElement.innerHTML = `
          <tr>
            <td><span class="badge bg-primary">Male</span></td>
            <td class="text-center">${counts.Male || 0}</td>
            <td class="text-center">${percentages.Male || "0.0"}%</td>
          </tr>
          <tr>
            <td><span class="badge" style="background-color: #ff6384;">Female</span></td>
            <td class="text-center">${counts.Female || 0}</td>
            <td class="text-center">${percentages.Female || "0.0"}%</td>
          </tr>
        `;
      } else {

      }
      
      if (totalElement) {
        totalElement.textContent = total || 0;
      } else {
   
      }
    } catch (error) {

      // Continue execution despite errors
    }
  }

  handlePreview(type) {
    // Get the current date range from the main date picker
    const startDate = $("#growthStatusDateRange").data("startDate");
    const endDate = $("#growthStatusDateRange").data("endDate");

    if (!startDate || !endDate) {
      this.showToast("Please select a date range first");
      return;
    }

    try {
      // Open preview in new tab with correct controller
      const url = `../controllers/ReportController.php?action=preview&type=${type}&startDate=${startDate}&endDate=${endDate}`;
      window.open(url, '_blank');
    } catch (error) {
      console.error("Error opening preview:", error);
      this.showToast("Error opening preview. Please try again.");
    }
  }
}

// Single initialization point
let reportManager = null;
$(document).ready(() => {
  if (!reportManager) {
    try {
      // Remove any existing event handlers
      $(document).off("click", "[data-export]");
      reportManager = new ReportManager();
      
      // Add a delay to ensure everything is properly initialized
      setTimeout(() => {
        // Double check if search elements exist and initialize
        if (document.getElementById("heightTableSearch") && reportManager) {
          reportManager.initializeSearchFunctionality();
        }
      }, 500);
      
    } catch (error) {

    }
  }
});
