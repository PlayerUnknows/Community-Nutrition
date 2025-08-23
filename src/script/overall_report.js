// Overall Report Module
const OverallReportModule = (function() {
  // Cache DOM elements
  let $dateRangeFilter;
  let $generateReportBtn;
  let $exportReportBtn;
  let $printReportBtn;
  let $refreshReportBtn;
  let $clearFiltersBtn;
  
  // Initialize module
  function init() {
    
    // Use a more robust selector for the report container
    $dateRangeFilter = $('#overall-report #overallDateRangeFilter');
    $generateReportBtn = $('#overall-report #generateReportBtn');
    $exportReportBtn = $('#overall-report #exportOverallReportBtn');
    $printReportBtn = $('#overall-report #printOverallReportBtn');
    $refreshReportBtn = $('#overall-report #refreshReportBtn');
    $clearFiltersBtn = $('#overall-report #clearFiltersBtn');
    
    // Debug: log the report container length
    
    // Initialize date range picker with default dates
    initializeDateRangePicker();
    
    // Bind events
    bindEvents();
    
    // Generate default report only if date range picker is ready
    if ($dateRangeFilter && $dateRangeFilter.length > 0) {
      // Add a small delay to ensure daterangepicker is fully initialized
      setTimeout(() => {
    generateReport();
      }, 200);
    } else {
      console.warn('Date range filter not found, cannot generate initial report');
    }
    
    // No barangay list to load
    // loadBarangays();
    
  }
  
  // Initialize date range picker
  function initializeDateRangePicker() {
    if (!$dateRangeFilter.length) {
      console.warn("Date range filter element not found");
      return;
    }
    
    try {
      // Get the first and last day of the current month
      const today = new Date();
      const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
      const lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0);
      
      $dateRangeFilter.daterangepicker({
        startDate: firstDay,
        endDate: lastDay,
        opens: 'left',
        autoUpdateInput: true,
        locale: {
          format: 'YYYY-MM-DD',
          separator: ' - ',
          applyLabel: 'Apply',
          cancelLabel: 'Clear'
        },
        ranges: {
          'This Month': [firstDay, lastDay],
          'Last Month': [new Date(today.getFullYear(), today.getMonth() - 1, 1), new Date(today.getFullYear(), today.getMonth(), 0)],
          'Last 3 Months': [new Date(today.getFullYear(), today.getMonth() - 3, 1), lastDay],
          'Last 6 Months': [new Date(today.getFullYear(), today.getMonth() - 6, 1), lastDay],
          'This Year': [new Date(today.getFullYear(), 0, 1), lastDay],
          'Last Year': [new Date(today.getFullYear() - 1, 0, 1), new Date(today.getFullYear() - 1, 11, 31)]
        }
      });
      
      // Set initial value
      $dateRangeFilter.val(firstDay.toISOString().slice(0,10) + ' - ' + lastDay.toISOString().slice(0,10));
      
      $dateRangeFilter.on('apply.daterangepicker', function(ev, picker) {
        $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
        generateReport(); // Automatically generate report when dates change
      });
      
      $dateRangeFilter.on('cancel.daterangepicker', function(ev, picker) {
        $(this).val('');
        generateReport(); // Generate report with no date filter
      });
    } catch (error) {
      console.error("Error initializing date range picker:", error);
    }
  }
  
  // Bind events
  function bindEvents() {
    if ($generateReportBtn.length) {
      $generateReportBtn.off('click').on('click', generateReport);
    }
    
    if ($refreshReportBtn.length) {
      $refreshReportBtn.off('click').on('click', generateReport);
    }
    
    if ($clearFiltersBtn.length) {
      $clearFiltersBtn.off('click').on('click', clearFilters);
    }
    
    if ($exportReportBtn.length) {
      $exportReportBtn.off('click').on('click', exportReport);
    } else {
      console.warn('Export button not found');
    }
    
    if ($printReportBtn.length) {
      $printReportBtn.off('click').on('click', printReport);
    }
  }
  
  function generateReport() {
    var $reportContainer = $('.report-content');
    
    // Show loading state
    $reportContainer.html(`
      <div class="text-center my-5">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
        <p class="mt-2 text-muted">Generating OPT Plus Nutrition Status Report...</p>
        <small class="text-muted">This may take a few moments depending on the data size</small>
      </div>
    `);
    
    // Add a small delay to show the loading state
    setTimeout(() => {
    fetchBMIStatsAndRender($reportContainer);
    }, 100);
  }
  window.generateReport = generateReport;
  window.exportReport = exportReport;
  
  async function fetchBMIStatsAndRender($reportContainer) {
    
    try {
      // Get the selected date range safely
      let startDate = null;
      let endDate = null;
      
      // Check if date range filter exists and has a value
      if ($dateRangeFilter && $dateRangeFilter.length > 0) {
        const dateRange = $dateRangeFilter.val();
        if (dateRange && dateRange.includes(' - ')) {
          const dates = dateRange.split(' - ');
          startDate = dates[0];
          endDate = dates[1];
        }
      } else {
        console.warn('Date range filter not found, proceeding without date filtering');
      }
      
      // Build the URL with date parameters
      let url = '/src/controllers/OverallReportController.php?action=getUnifiedOPTOverallReport';
      if (startDate && endDate) {
        url += `&startDate=${startDate}&endDate=${endDate}`;
      }
      
      
      // Fetch data from the unified overall report controller
      const response = await fetch(url);
      const result = await response.json();
      if (result.status !== 'success') throw new Error(result.message || 'Failed to fetch unified OPT stats');
      const data = result.data;
      
      if (!data || data.length === 0) {
        // Get available date range to show user what data is available
        fetch('/src/controllers/OverallReportController.php?action=getAvailableDateRange')
          .then(response => response.json())
          .then(result => {
            let availableDateInfo = '';
            if (result.status === 'success' && result.data) {
              const dateRange = result.data;
              availableDateInfo = `
                <div class="mt-3 p-3 bg-light rounded">
                  <h6>Available Data Range:</h6>
                  <p class="mb-1"><strong>Earliest Date:</strong> ${dateRange.earliest_date || 'No data'}</p>
                  <p class="mb-1"><strong>Latest Date:</strong> ${dateRange.latest_date || 'No data'}</p>
                  <p class="mb-0"><strong>Total Records:</strong> ${dateRange.total_records || 0}</p>
                </div>
              `;
            }
            
            $reportContainer.html(`
              <div class="text-center my-5">
                <div class="text-muted">
                  <i class="fas fa-exclamation-triangle fa-3x mb-3 text-warning"></i>
                  <h5 class="mb-3">No Data Available</h5>
                  <p class="mb-4">No nutrition data found for the selected date range: <strong>${startDate} to ${endDate}</strong></p>
                  <div class="row justify-content-center">
                    <div class="col-md-8">
                      <div class="alert alert-warning">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Possible reasons:</strong>
                        <ul class="text-start mb-0 mt-2">
                          <li>The selected date range is in the future</li>
                          <li>No checkup data exists for this period</li>
                          <li>The date range is outside the available data</li>
                        </ul>
                      </div>
                      ${availableDateInfo}
                      <div class="mt-3">
                        <button class="btn btn-primary btn-sm" onclick="clearFilters()">
                          <i class="fas fa-calendar-times me-1"></i> Clear Date Filter
                        </button>
                        <button class="btn btn-outline-secondary btn-sm ms-2" onclick="generateReport()">
                          <i class="fas fa-sync-alt me-1"></i> Try Again
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            `);
          })
          .catch(error => {
            console.error('Error fetching available date range:', error);
            // Fallback message without date range info
            $reportContainer.html(`
              <div class="text-center my-5">
                <div class="text-muted">
                  <i class="fas fa-exclamation-triangle fa-3x mb-3 text-warning"></i>
                  <h5 class="mb-3">No Data Available</h5>
                  <p class="mb-4">No nutrition data found for the selected date range: <strong>${startDate} to ${endDate}</strong></p>
                  <div class="row justify-content-center">
                    <div class="col-md-8">
                      <div class="alert alert-warning">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Possible reasons:</strong>
                        <ul class="text-start mb-0 mt-2">
                          <li>The selected date range is in the future</li>
                          <li>No checkup data exists for this period</li>
                          <li>The date range is outside the available data</li>
                        </ul>
                      </div>
                      <div class="mt-3">
                        <button class="btn btn-primary btn-sm" onclick="clearFilters()">
                          <i class="fas fa-calendar-times me-1"></i> Clear Date Filter
                        </button>
                        <button class="btn btn-outline-secondary btn-sm ms-2" onclick="generateReport()">
                          <i class="fas fa-sync-alt me-1"></i> Try Again
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            `);
          });
        return;
      }
      
      $reportContainer.html(generateUnifiedOverallTableHTML(data, true));
    } catch (error) {
      console.error('Error in fetchBMIStatsAndRender:', error);
      $reportContainer.html(`<div class="alert alert-danger">Error generating report: ${error.message}</div>`);
    }
  }
  
  function generateUnifiedOverallTableHTML(data, showSummaryBar = true) {
    // Get the current date range for display safely
    let dateRangeText = '';
    if ($dateRangeFilter && $dateRangeFilter.length > 0) {
      const dateRange = $dateRangeFilter.val();
      if (dateRange && dateRange.includes(' - ')) {
        const dates = dateRange.split(' - ');
        const startDate = new Date(dates[0]);
        const endDate = new Date(dates[1]);
        dateRangeText = ` (${startDate.toLocaleDateString()} - ${endDate.toLocaleDateString()})`;
      }
    }
    
    // All acronyms for the rows
    const allAcronyms = [
      'BMI-Severely Wasted', 'BMI-Wasted', 'BMI-Normal', 'BMI-Obese',
      'H-Stunted', 'H-Normal', 'H-Over',
      'A-Too Small', 'A-Normal', 'A-Over'
    ];
    const ageGroups = [
      '0-11', '12-23', '24-35', '36-47', '48-59', '60-71', '72-83', '84-95', '96-107', '108-119', '120-131', '132-143', '144-155', '156-167', '168-179', 'Other'
    ];

    // Indices for all age groups (0-179 months + Other)
    const idx_all = Array.from({length: ageGroups.length}, (_, i) => i);
    // Calculate column counts programmatically
    const numAgeCols = ageGroups.length * 3; // Each age group has 3 columns (Boys, Girls, Total)
    const numSummaryCols = 2 + 2 + 3 + 2; // 0-59 (2), 0-23 (2), IP (3), Total (2)
    const totalCols = 1 + numAgeCols + numSummaryCols; // 1 for acronym column
    // Helper to sum for a category and age indices
    function sumForCatsAndAges(categories, ageIdxs) {
      let sum = 0;
      categories.forEach(cat => {
        ageIdxs.forEach(i => {
          const cell = lookup[cat] && lookup[cat][ageGroups[i]] ? lookup[cat][ageGroups[i]] : {total: 0};
          sum += cell.total;
        });
      });
      return sum;
    }

    // Build a lookup: lookup[status][age_group] = {boys, girls, total}
    const lookup = {};
    data.forEach(row => {
      const status = row.status;
      const age = row.age_group;
      if (!lookup[status]) lookup[status] = {};
      lookup[status][age] = {
        boys: parseInt(row.boys) || 0,
        girls: parseInt(row.girls) || 0,
        total: parseInt(row.total) || 0
      };
    });

    // --- Compute upper summary row values ---
    // Use idx_all from above
    // Total BMI: sum all BMI rows
    const bmiCats = ['BMI-Severely Wasted', 'BMI-Wasted', 'BMI-Normal', 'BMI-Obese'];
    const totalBMI = sumForCatsAndAges(bmiCats, idx_all);
    // Total Height: sum all Height rows
    const heightCats = ['H-Stunted', 'H-Normal', 'H-Over'];
    const totalHeight = sumForCatsAndAges(heightCats, idx_all);
    // Total Arm Circumference: sum all Arm rows
    const armCats = ['A-Too Small', 'A-Normal', 'A-Over'];
    const totalArm = sumForCatsAndAges(armCats, idx_all);
    // Grand total: sum all categories
    let grandTotalAll = 0;
    allAcronyms.forEach(cat => { grandTotalAll += sumForCatsAndAges([cat], idx_all); });

    // Start html as an empty string
    let html = '';

    // Add summary bar above the main table (outside the table)
    if (showSummaryBar) {
      html += `
      <div class="mb-2" style="background:#f8f6f0; border:1px solid #ccc; border-radius:4px; padding:6px 12px; display:flex; gap:24px; font-weight:bold;">
        <div>Total BMI: <span>${totalBMI}</span></div>
        <div>Total Height: <span>${totalHeight}</span></div>
        <div>Total Arm Circumference: <span>${totalArm}</span></div>
        <div>Total: <span>${grandTotalAll}</span></div>
        <div style="margin-left: auto; color: #666; font-size: 0.9em;">${dateRangeText}</div>
      </div>
      `;
    }

    // Main table
    html += `<div class="table-responsive"><table class="table table-bordered table-sm" id="overallReportTable">
        <thead class="bg-light">
          <tr>
          <th rowspan="2" class="text-center bg-primary text-white">ACRONYMS & ABBREVIATIONS</th>`;
    // Add age group columns
    ageGroups.forEach(g => {
      html += `<th colspan="3" class="text-center">${g} Months</th>`;
    });
    // Add summary columns with correct colspan
    html += `
      <th colspan="2" class="text-center">0-59 Months (F1K)</th>
      <th colspan="2" class="text-center">0-23 Months (F1K)</th>
      <th colspan="3" class="text-center">IP Children</th>
      <th colspan="2" class="text-center">Total</th>
    </tr><tr>`;
    
    // Add subheaders for each age group
    ageGroups.forEach(() => {
      html += `<th class="text-center">Boys</th><th class="text-center">Girls</th><th class="text-center">Total</th>`;
    });
    // Add subheaders for summary columns
    html += `
      <th class="text-center">Total</th><th class="text-center">Prev</th>
      <th class="text-center">Total</th><th class="text-center">Prev</th>
      <th class="text-center">Boys</th><th class="text-center">Girls</th><th class="text-center">Total</th>
      <th class="text-center">Total</th><th class="text-center">Prev</th>
    </tr></thead><tbody>`;

    // --- Compute summary values from actual data ---
    // # Children 0-179 mos. affected by Undernutrition (Severely Wasted + Wasted)
    const undn_all = sumForCatsAndAges(['BMI-Severely Wasted','BMI-Wasted'], idx_all);
    // # Children 0-179 mos. with Overweight/Obesity (Obese)
    const obese_all = sumForCatsAndAges(['BMI-Obese'], idx_all);
    // # Children 0-179 mos. affected by Undernutrition (Severely Wasted + Wasted)
    // (already computed as undn_all)
    // # Children with weight but no height: leave as '--' unless you have this data
    const weightNoHeight = '--';
    // --- End summary computation ---

    // Calculate grand total for prevalence
    let grandTotal = 0;
    allAcronyms.forEach(cat => {
      ageGroups.forEach(g => {
        const cell = lookup[cat] && lookup[cat][g] ? lookup[cat][g] : {total: 0};
        grandTotal += cell.total;
      });
    });

    allAcronyms.forEach(cat => {
      html += `<tr><td><strong>${cat}</strong></td>`;
      // Age group columns
      ageGroups.forEach(g => {
        const cell = lookup[cat] && lookup[cat][g] ? lookup[cat][g] : {boys: 0, girls: 0, total: 0};
        html += `<td class="text-center">${cell.boys}</td><td class="text-center">${cell.girls}</td><td class="text-center">${cell.total}</td>`;
      });
      // 0-59 months (F1K)
      let f1k_0_59 = 0;
      idx_all.forEach(i => { f1k_0_59 += lookup[cat] && lookup[cat][ageGroups[i]] ? lookup[cat][ageGroups[i]].total : 0; });
      let f1k_0_59_prev = grandTotal ? ((f1k_0_59 / grandTotal) * 100).toFixed(1) + '%' : '0%';
      html += `<td class="text-center">${f1k_0_59}</td><td class="text-center">${f1k_0_59_prev}</td>`;
      // 0-23 months (F1K)
      let f1k_0_23 = 0;
      idx_all.forEach(i => { f1k_0_23 += lookup[cat] && lookup[cat][ageGroups[i]] ? lookup[cat][ageGroups[i]].total : 0; });
      let f1k_0_23_prev = grandTotal ? ((f1k_0_23 / grandTotal) * 100).toFixed(1) + '%' : '0%';
      html += `<td class="text-center">${f1k_0_23}</td><td class="text-center">${f1k_0_23_prev}</td>`;
      // IP Children (placeholders)
      html += `<td class="text-center">0</td><td class="text-center">0</td><td class="text-center">0</td>`;
      // Grand total for row
      let rowTotal = 0;
      ageGroups.forEach(g => { rowTotal += lookup[cat] && lookup[cat][g] ? lookup[cat][g].total : 0; });
      let rowPrev = grandTotal ? ((rowTotal / grandTotal) * 100).toFixed(1) + '%' : '0%';
      html += `<td class="text-center"><strong>${rowTotal}</strong></td><td class="text-center"><strong>${rowPrev}</strong></td>`;
      html += `</tr>`;
    });

    // Add total row with correct number of columns
    html += `<tr class="bg-secondary text-white"><td><strong>Total</strong></td>`;
    // Age group totals
    ageGroups.forEach(g => {
      let boysSum = 0, girlsSum = 0, totalSum = 0;
      allAcronyms.forEach(cat => {
        const cell = lookup[cat] && lookup[cat][g] ? lookup[cat][g] : {boys: 0, girls: 0, total: 0};
        boysSum += cell.boys;
        girlsSum += cell.girls;
        totalSum += cell.total;
      });
      html += `<td class="text-center"><strong>${boysSum}</strong></td><td class="text-center"><strong>${girlsSum}</strong></td><td class="text-center"><strong>${totalSum}</strong></td>`;
    });
    
    // Add summary columns for the total row
    html += `
      <td class="text-center">0</td><td class="text-center">0%</td>
      <td class="text-center">0</td><td class="text-center">0%</td>
      <td class="text-center">0</td><td class="text-center">0</td><td class="text-center">0</td>
      <td class="text-center"><strong>${grandTotalAll}</strong></td><td class="text-center">100%</td>
    </tr></tbody></table></div>`;

    // Add summary table below the main table, using computed values
    html += `
    <div class="mt-4">
      <table class="table table-bordered table-sm" style="background:#f8f6f0;">
        <thead>
          <tr style="background:#e6d3c2;">
            <th class="text-center">Summary of Children covered by e-OPT Plus</th>
            <th class="text-center">Mothers/Caregivers Summary</th>
            <th class="text-center">Data Summary</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td># Children 0-179 mos. affected by Undernutrition: <b>${undn_all}</b></td>
            <td>Total Number of M/Cs of children 0-179 mos. old: <b>--</b></td>
            <td># Children with weight but no height: <b>${weightNoHeight}</b></td>
          </tr>
          <tr>
            <td># Children 0-179 mos. with Overweight/Obesity: <b>${obese_all}</b></td>
            <td># M/Cs of 0-179 mos. children affected by Undernutrition: <b>--</b></td>
            <td></td>
          </tr>
          <tr>
            <td>Total Number of Children 0-179 mos. old: <b>${grandTotalAll}</b></td>
            <td># M/Cs of 0-179 mos. children with Overweight/Obesity: <b>--</b></td>
            <td></td>
          </tr>
          <tr>
            <td># Children 0-179 mos. affected by Undernutrition: <b>${undn_all}</b></td>
            <td>Total Number of M/Cs of children 0-179 mos. old: <b>--</b></td>
            <td></td>
          </tr>
          <tr>
            <td></td>
            <td># M/Cs of 0-179 mos. children affected by Undernutrition: <b>--</b></td>
            <td></td>
          </tr>
        </tbody>
      </table>
    </div>
    `;

    // Initialize DataTable
    setTimeout(() => {
      if (window.jQuery && window.jQuery.fn && window.jQuery.fn.DataTable) {
        $('#overallReportTable').DataTable({
          responsive: true,
          paging: false,
          searching: false, // Disable searching
          info: true,
          ordering: true,
          dom: 'rt<"bottom"ip>', // Simplified DOM structure
          language: {
            info: "Showing _TOTAL_ entries",
            infoEmpty: "No entries available"
          }
        });
      }
    }, 0);

    return html;
  }
  
  function clearFilters() {
    // Clear the date range picker
    if ($dateRangeFilter && $dateRangeFilter.length > 0) {
      $dateRangeFilter.val('');
      
      // Reset to default date range (current month)
      const today = new Date();
      const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
      const lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0);
      $dateRangeFilter.val(firstDay.toISOString().slice(0,10) + ' - ' + lastDay.toISOString().slice(0,10));
      
      // Regenerate report with default dates
      generateReport();
    }
  }
  
  // Export report
  function exportReport() {
    try {
      let startDate = null;
      let endDate = null;
      
      if ($dateRangeFilter && $dateRangeFilter.length > 0) {
        const dateRange = $dateRangeFilter.val();
        if (dateRange && dateRange.includes(' - ')) {
          const dates = dateRange.split(' - ');
          startDate = dates[0];
          endDate = dates[1];
        }
      }
      
      // Build the export URL
      let url = '/src/controllers/OverallReportController.php?action=exportReport';
      if (startDate && endDate) {
        url += `&startDate=${startDate}&endDate=${endDate}`;
      }
      
      // Use fetch to handle the response
      fetch(url)
        .then(response => {
          // Check if response is JSON (error/no data) or file download
          const contentType = response.headers.get('content-type');
          if (contentType && contentType.includes('application/json')) {
            // Handle JSON response (no data or error)
            return response.json();
          } else {
            // Handle file download
            return response.blob().then(blob => {
              // Create download link
              const downloadUrl = window.URL.createObjectURL(blob);
              const a = document.createElement('a');
              a.href = downloadUrl;
              a.download = response.headers.get('content-disposition')?.split('filename=')[1]?.replace(/"/g, '') || 'export.xlsx';
              document.body.appendChild(a);
              a.click();
              window.URL.revokeObjectURL(downloadUrl);
              document.body.removeChild(a);
              return { status: 'success', message: 'Export completed successfully' };
            });
          }
        })
        .then(data => {
          if (data.status === 'no_data') {
            // Show SweetAlert for no data
            Swal.fire({
              title: data.title,
              text: data.message,
              icon: data.icon,
              confirmButtonText: 'OK',
              confirmButtonColor: '#3085d6',
              showCancelButton: true,
              cancelButtonText: 'Clear Filters',
              cancelButtonColor: '#6c757d'
            }).then((result) => {
              if (result.dismiss === Swal.DismissReason.cancel) {
                clearFilters();
              }
            });
          } else if (data.status === 'error') {
            // Show SweetAlert for error
            Swal.fire({
              title: 'Export Error',
              text: data.message,
              icon: 'error',
              confirmButtonText: 'OK',
              confirmButtonColor: '#d33'
            });
          } else if (data.status === 'success') {
            // Show success message
            Swal.fire({
              title: 'Export Successful',
              text: data.message,
              icon: 'success',
              confirmButtonText: 'OK',
              confirmButtonColor: '#28a745'
            });
          }
        })
        .catch(error => {
          console.error('Error exporting report:', error);
          Swal.fire({
            title: 'Export Error',
            text: 'An error occurred while exporting the report. Please try again.',
            icon: 'error',
            confirmButtonText: 'OK',
            confirmButtonColor: '#d33'
          });
        });
      
    } catch (error) {
      console.error('Error exporting report:', error);
      Swal.fire({
        title: 'Export Error',
        text: 'An error occurred while exporting the report. Please try again.',
        icon: 'error',
        confirmButtonText: 'OK',
        confirmButtonColor: '#d33'
      });
    }
  }
  
  // Print report
  function printReport() {
    window.print();
  }
  
  // Public API
  return {
    init: init,
    generateReport: generateReport
  };
})();

// Initialize when document is ready
$(document).ready(function() {
  if (typeof OverallReportModule !== 'undefined') {
    OverallReportModule.init();
  }
});