// Overall Report Module
const OverallReportModule = (function() {
  // Cache DOM elements
  let $dateRangeFilter;
  let $generateReportBtn;
  let $exportReportBtn;
  let $printReportBtn;
  
  // Initialize module
  function init() {  
    // Use a more robust selector for the report container
    $dateRangeFilter = $('#overall-report #overallDateRangeFilter');
    $generateReportBtn = $('#overall-report #generateReportBtn');
    $exportReportBtn = $('#overall-report #exportOverallReportBtn');
    $printReportBtn = $('#overall-report #printOverallReportBtn');
    

    // Initialize date range picker with default dates
    initializeDateRangePicker();
    
    // Bind events
    bindEvents();
    
    // Generate default report
    generateReport();
    
    // No barangay list to load
    // loadBarangays();
    
    console.log("OverallReportModule initialized successfully");
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
    
    if ($exportReportBtn.length) {
      $exportReportBtn.off('click').on('click', exportReport);
    }
    
    if ($printReportBtn.length) {
      $printReportBtn.off('click').on('click', printReport);
    }
  }
  
  function generateReport() {
    var $reportContainer = $('.report-content');
    $reportContainer.html('<div class="text-center my-5"><div class="spinner-border" role="status"></div><p class="mt-2">Loading report data...</p></div>');
    fetchBMIStatsAndRender($reportContainer);
  }
  window.generateReport = generateReport;
  
  async function fetchBMIStatsAndRender($reportContainer) {
    try {
      // Get date range from the filter - check if it exists first
      let url = '/src/controllers/OverallReportController.php?action=getUnifiedOPTOverallReport';
      let statsUrl = '/src/controllers/OverallReportController.php?action=getLocationStatistics';
      
      if ($dateRangeFilter && $dateRangeFilter.length > 0) {
        const dateRangeValue = $dateRangeFilter.val();
        if (dateRangeValue) {
          const dates = dateRangeValue.split(' - ');
          if (dates.length === 2) {
            const dateParams = `&start_date=${encodeURIComponent(dates[0])}&end_date=${encodeURIComponent(dates[1])}`;
            url += dateParams;
            statsUrl += dateParams;
          }
        }
      } else {
        console.warn('Date range filter not initialized yet');
      }
      
      // Fetch both data and statistics
      const [response, statsResponse] = await Promise.all([
        fetch(url),
        fetch(statsUrl)
      ]);
      
      const result = await response.json();
      const statsResult = await statsResponse.json();
      
      if (result.status !== 'success') throw new Error(result.message || 'Failed to fetch unified OPT stats');
      if (statsResult.status === 'success') {
        updateLocationStatistics(statsResult.data);
      }
      
      const data = result.data;
      console.log('OPT Unified Overall Report Data:', data);
      $reportContainer.html(generateUnifiedOverallTableHTML(data, true));
    } catch (error) {
      console.error('Error in fetchBMIStatsAndRender:', error);
      $reportContainer.html(`<div class="alert alert-danger">Error generating report: ${error.message}</div>`);
    }
  }
  
  function updateLocationStatistics(stats) {
    // Update the header statistics with actual data
    if (stats.province) {
      $('#provinceInput').val(stats.province);

    }
    if (stats.region) {
      $('#regionInput').val(stats.region);

    }
    if (stats.barangay) {
      $('#barangayInput').val(stats.barangay);
   
    }
    if (stats.municipality) {
      $('#municipalityInput').val(stats.municipality);
 
    }
    if (stats.psgc) {
      $('#psgcInput').val(stats.psgc);

    }
    if (stats.total_population) {
      $('#totalPopnInput').val(stats.total_population);

    }
    if (stats.estimated_children_0_59) {
      $('#estimatedChildrenInput').val(stats.estimated_children_0_59);

    }
    if (stats.opt_coverage) {
      $('#optCoverageInput').val(stats.opt_coverage);

    }
    if (stats.total_children_measured) {
      $('#totalIndigenousInput').val(stats.total_children_measured);
      $('#totalIndigenousInput2').val(stats.children_0_59_months || stats.total_children_measured);

    }
    
    console.log('Location statistics update complete');
  }
  
  function generateUnifiedOverallTableHTML(data, showSummaryBar = true) {
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

    // Update summary cards
    $('#summaryTotalBMI').text(totalBMI.toLocaleString());
    $('#summaryTotalHeight').text(totalHeight.toLocaleString());
    $('#summaryTotalArm').text(totalArm.toLocaleString());
    $('#summaryGrandTotal').text(grandTotalAll.toLocaleString());

    // Start html as an empty string
    let html = '';

    // Main table (summary bar removed, now using cards above)
    html += `<div class=\"table-responsive\"><table class=\"table table-bordered table-sm\" id=\"overallReportTable\">\n        <thead class=\"bg-light\">\n          <tr>\n          <th rowspan=\"2\" class=\"text-center bg-primary text-white\">ACRONYMS & ABBREVIATIONS</th>`;
    // Add age group columns
    ageGroups.forEach(g => {
      html += `<th colspan=\"3\" class=\"text-center\">${g} Months</th>`;
    });
    // Add summary columns with correct colspan
    html += `
      <th colspan=\"2\" class=\"text-center\">0-59 Months (F1K)</th>
      <th colspan=\"2\" class=\"text-center\">0-23 Months (F1K)</th>
      <th colspan=\"3\" class=\"text-center\">IP Children</th>
      <th colspan=\"2\" class=\"text-center\">Total</th>
    </tr><tr>`;
    
    // Add subheaders for each age group
    ageGroups.forEach(() => {
      html += `<th class=\"text-center\">Boys</th><th class=\"text-center\">Girls</th><th class=\"text-center\">Total</th>`;
    });
    // Add subheaders for summary columns
    html += `
      <th class=\"text-center\">Total</th><th class=\"text-center\">Prev</th>
      <th class=\"text-center\">Total</th><th class=\"text-center\">Prev</th>
      <th class=\"text-center\">Boys</th><th class=\"text-center\">Girls</th><th class=\"text-center\">Total</th>
      <th class=\"text-center\">Total</th><th class=\"text-center\">Prev</th>
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
        html += `<td class=\"text-center\">${cell.boys}</td><td class=\"text-center\">${cell.girls}</td><td class=\"text-center\">${cell.total}</td>`;
      });
      // 0-59 months (F1K)
      let f1k_0_59 = 0;
      idx_all.forEach(i => { f1k_0_59 += lookup[cat] && lookup[cat][ageGroups[i]] ? lookup[cat][ageGroups[i]].total : 0; });
      let f1k_0_59_prev = grandTotal ? ((f1k_0_59 / grandTotal) * 100).toFixed(1) + '%' : '0%';
      html += `<td class=\"text-center\">${f1k_0_59}</td><td class=\"text-center\">${f1k_0_59_prev}</td>`;
      // 0-23 months (F1K)
      let f1k_0_23 = 0;
      idx_all.forEach(i => { f1k_0_23 += lookup[cat] && lookup[cat][ageGroups[i]] ? lookup[cat][ageGroups[i]].total : 0; });
      let f1k_0_23_prev = grandTotal ? ((f1k_0_23 / grandTotal) * 100).toFixed(1) + '%' : '0%';
      html += `<td class=\"text-center\">${f1k_0_23}</td><td class=\"text-center\">${f1k_0_23_prev}</td>`;
      // IP Children (placeholders)
      html += `<td class=\"text-center\">0</td><td class=\"text-center\">0</td><td class=\"text-center\">0</td>`;
      // Grand total for row
      let rowTotal = 0;
      ageGroups.forEach(g => { rowTotal += lookup[cat] && lookup[cat][g] ? lookup[cat][g].total : 0; });
      let rowPrev = grandTotal ? ((rowTotal / grandTotal) * 100).toFixed(1) + '%' : '0%';
      html += `<td class=\"text-center\"><strong>${rowTotal}</strong></td><td class=\"text-center\"><strong>${rowPrev}</strong></td>`;
      html += `</tr>`;
    });

    // Add total row with correct number of columns
    html += `<tr class=\"bg-secondary text-white\"><td><strong>Total</strong></td>`;
    // Age group totals
    ageGroups.forEach(g => {
      let boysSum = 0, girlsSum = 0, totalSum = 0;
      allAcronyms.forEach(cat => {
        const cell = lookup[cat] && lookup[cat][g] ? lookup[cat][g] : {boys: 0, girls: 0, total: 0};
        boysSum += cell.boys;
        girlsSum += cell.girls;
        totalSum += cell.total;
      });
      html += `<td class=\"text-center\"><strong>${boysSum}</strong></td><td class=\"text-center\"><strong>${girlsSum}</strong></td><td class=\"text-center\"><strong>${totalSum}</strong></td>`;
    });
    
    // Add summary columns for the total row
    html += `
      <td class=\"text-center\">0</td><td class=\"text-center\">0%</td>
      <td class=\"text-center\">0</td><td class=\"text-center\">0%</td>
      <td class=\"text-center\">0</td><td class=\"text-center\">0</td><td class=\"text-center\">0</td>
      <td class=\"text-center\"><strong>${grandTotalAll}</strong></td><td class=\"text-center\">100%</td>
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
  
  // Export report
  function exportReport() {
    const title = `OPT_Plus_Report_${new Date().toISOString().slice(0, 10)}`;
    const dateRange = $dateRangeFilter.val() ? $dateRangeFilter.val().replace(/\s/g, '') : 'All_Time';
    const filename = `${title}_${dateRange}.xlsx`;
    
    // Alert for now, would be implemented with actual export library
    alert(`Export functionality will be implemented here. File will be saved as: ${filename}`);

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

// BMI Details Table: Fetch and display
$(document).ready(function() {
  // fetchBMIDetailsAndRender(); // Commented out to prevent duplicate table rendering in the main report
  // Initialize the module instead of calling generateReport directly
  if (typeof OverallReportModule !== 'undefined' && OverallReportModule.init) {
    OverallReportModule.init();
  }
});

function fetchBMIDetailsAndRender() {
  const loading = $('#bmiDetailsLoading');
  const wrapper = $('#bmiDetailsTableWrapper');
  const tbody = $('#bmiDetailsTable tbody');
  loading.show();
  wrapper.hide();
  $.ajax({
    url: '/src/controllers/OverallReportController.php?action=getBMIDetails',
    method: 'GET',
    dataType: 'json',
    success: function(result) {
      if (result.status === 'success' && Array.isArray(result.data)) {
        tbody.empty();
        if (result.data.length === 0) {
          tbody.append('<tr><td colspan="5" class="text-center">No BMI details found.</td></tr>');
        } else {
          result.data.forEach(function(row) {
            tbody.append('<tr>' +
              '<td>' + escapeHtml(row.name) + '</td>' +
              '<td>' + escapeHtml(row.sex) + '</td>' +
              '<td>' + escapeHtml(row.age_months) + '</td>' +
              '<td>' + escapeHtml(row.finding_bmi) + '</td>' +
              '<td>' + escapeHtml(row.created_at) + '</td>' +
            '</tr>');
          });
        }
        loading.hide();
        wrapper.show();
        if (window.jQuery && window.jQuery.fn && window.jQuery.fn.DataTable) {
          $('#bmiDetailsTable').DataTable({
            responsive: true,
            paging: true,
            searching: true,
            info: true,
            ordering: true,
            destroy: true
          });
        }
      } else {
        tbody.html('<tr><td colspan="5" class="text-center text-danger">Failed to load BMI details.</td></tr>');
        loading.hide();
        wrapper.show();
      }
    },
    error: function() {
      tbody.html('<tr><td colspan="5" class="text-center text-danger">Error loading BMI details.</td></tr>');
      loading.hide();
      wrapper.show();
    }
  });
}

// Simple HTML escape to prevent XSS
function escapeHtml(text) {
  return text == null ? '' : String(text)
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#039;");
}

// Confirm script is loaded
console.log('overall_report.js loaded'); 