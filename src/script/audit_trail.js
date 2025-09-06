$(document).ready(function () {
  let auditTable = null;

  // Function to safely dispose modals
  function disposeModal(modalId) {
    const modalElement = document.querySelector(modalId);
    if (!modalElement) return;

    const modal = bootstrap.Modal.getInstance(modalElement);
    if (modal) {
      modal.dispose();
      document.querySelector(".modal-backdrop")?.remove();
      document.body.classList.remove("modal-open");
    }
  }

  // Function to get readable role name
  function getRoleName(role) {
    const roleMap = {
      1: "Parent",
      2: "Brgy Health Worker",
      3: "Administrator",
    };
    return roleMap[role] || role;
  }

  // Function to initialize audit table
  function initializeAuditTable() {
    try {
      // Destroy existing instance if it exists
      if ($.fn.DataTable.isDataTable("#auditTable")) {
        $("#auditTable").DataTable().destroy();
      }
  
      // Initialize DataTable
      auditTable = $("#auditTable").DataTable({
        processing: true,
        serverSide: false,
        ajax: {
          url: "../services/AuditTrailServices/fetch_audit_trail.php",
          type: "GET",
          dataSrc: "data",
        },
        columns: [
          {
            data: "username",
            defaultContent: "System",
            width: "15%",
          },
          {
            data: "action",
            width: "15%",
          },
          {
            data: "details",
            width: "40%",
            render: function (data, type, row) {
              // LOGIN / LOGOUT simple message - handle empty data first
              if (row.action === "LOGIN" || row.action === "LOGOUT") {
                return data || `User ${row.action.toLowerCase()} successfully`;
              }

              if (!data) return "N/A";

              try {
                // FILE operations
                if (
                  row.action === "FILE_DOWNLOAD" ||
                  row.action === "FILE_EXPORT" ||
                  row.action === "FILE_IMPORT"
                ) {
                  const details = JSON.parse(data);
                  let html = '<div class="audit-details">';

                  if (details.filename) {
                    html += `<div><strong>Filename:</strong> ${details.filename}</div>`;
                  }

                  switch (row.action) {
                    case "FILE_DOWNLOAD":
                      if (details.file_type) {
                        html += `<div><strong>File Type:</strong> ${details.file_type}</div>`;
                      }
                      break;

                    case "FILE_EXPORT":
                      if (details.format) {
                        html += `<div><strong>Format:</strong> ${details.format}</div>`;
                      }
                      if (details.export_type) {
                        html += `<div><strong>Export Type:</strong> ${details.export_type}</div>`;
                      }
                      break;

                    case "FILE_IMPORT":
                      if (details.import_type) {
                        html += `<div><strong>Import Type:</strong> ${details.import_type}</div>`;
                      }
                      if (details.status) {
                        html += `<div><strong>Status:</strong> ${details.status}</div>`;
                      }
                      if (details.additional_details) {
                        html += `<div><strong>Additional Details:</strong> ${details.additional_details}</div>`;
                      }
                      break;
                  }

                  html += "</div>";
                  return html;
                }

                // For CRUD operations (create/update/delete)
                const details = JSON.parse(data);
                let html = '<div class="audit-details">';

                for (const [key, value] of Object.entries(details)) {
                  const label = key
                    .replace(/_/g, " ")
                    .replace(/\b\w/g, (char) => char.toUpperCase());

                  if (typeof value === "object" && value !== null) {
                    if ("old" in value && "new" in value) {
                      html += `<div><strong>${label}:</strong> "${value.old}" → "${value.new}"</div>`;
                    } else {
                      html += `<div><strong>${label}:</strong> ${JSON.stringify(value)}</div>`;
                    }
                  } else {
                    html += `<div><strong>${label}:</strong> ${value}</div>`;
                  }
                }

                html += "</div>";
                return html;
              } catch (e) {
                return data || "N/A";
              }
            },
          },
          {
            data: "action_timestamp",
            width: "20%",
            render: function (data) {
              if (!data || data === "-") return "N/A";
              return moment(data).format("YYYY-MM-DD HH:mm:ss");
            },
          },
        ],
        order: [[3, "desc"]],
        pageLength: 10,
        lengthMenu: [
          [10, 25, 50, 100],
          [10, 25, 50, 100],
        ],
        responsive: true,
        scrollX: true,
        autoWidth: false,
        fixedHeader: true,
        dom: `
          <'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>
          <'row'<'col-sm-12'tr>>
          <'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>
        `,
        language: {
          lengthMenu: "Show _MENU_ entries",
          info: "Showing _START_ to _END_ of _TOTAL_ entries",
          processing: "Loading audit trail data...",
          emptyTable: "No audit trail data available",
          zeroRecords: "No matching records found",
        },
        drawCallback: function () {
          $(".dataTables_paginate > .pagination").addClass("pagination-sm");
          updateScrollIndicator();
          applyProfessionalPaginationStyling();
        },
      });
  
      // Update scroll indicator on resize
      $(window).on("resize", function () {
        updateScrollIndicator();
      });
  
      // Toggle full/short content (if any truncation exists)
      $("#auditTable").on("click", ".toggle-content", function () {
        const container = $(this).closest(".truncated-content");
        const shortContent = container.find(".short-content");
        const fullContent = container.find(".full-content");
  
        shortContent.toggleClass("d-none");
        fullContent.toggleClass("d-none");
  
        $(this).text(
          fullContent.hasClass("d-none") ? "Show More" : "Show Less"
        );
      });
  
      return auditTable;
    } catch (error) {
      console.error("Error initializing DataTable:", error);
      return null;
    }
  }
  


  // Handle filter form submission
  $("#auditFilterForm").on("submit", function (e) {
    e.preventDefault();
    const formData = new FormData(this);
    const params = new URLSearchParams(formData);

    $.ajax({
      url: "../services/AuditTrailServices/fetch_audit_trail.php",
      method: "GET",
      data: params.toString(),
      success: function (response) {
        if (!response || !response.data) {
          console.error("Invalid response format:", response);
          return;
        }

        if (auditTable) {
          auditTable.clear().rows.add(response.data).draw();
        } else {
          auditTable = initializeAuditTable();
        }
      },
      error: function (xhr, status, error) {
        console.error("Error fetching audit trail:", error);
        alert("Error fetching audit trail data. Please try again.");
      },
    });
  });

  // Initialize table on page load
  auditTable = initializeAuditTable();

  // Connect search and length controls
  $("#auditSearch").on("keyup", function () {
    if (auditTable) {
      auditTable.search(this.value).draw();
    }
  });

  $("#auditsPerPage").on("change", function () {
    if (auditTable) {
      auditTable.page.len($(this).val()).draw();
    }
  });

  // Handle tab switching
  $('button[data-bs-toggle="tab"]').on("shown.bs.tab", function (e) {
    if ($(e.target).attr("id") === "audit-tab") {
      if (!auditTable) {
        auditTable = initializeAuditTable();
      } else {
        auditTable.ajax.reload();
      }
    }
  });

  // Add modal cleanup on bootstrap modal hidden event
  $(".modal").on("hidden.bs.modal", function () {
    disposeModal(this.id ? `#${this.id}` : ".modal");
  });

  // Add this after your existing DataTable initialization
  function updateScrollIndicator() {
    const tableWrapper = document.querySelector(".table-responsive");
    if (!tableWrapper) return;

    const hasHorizontalScroll =
      tableWrapper.scrollWidth > tableWrapper.clientWidth;
    tableWrapper.classList.toggle("has-scroll", hasHorizontalScroll);
  }

  // Update the showLoadingBackdrop function
  function showLoadingBackdrop() {
    // Clean up any existing backdrops first
    hideLoadingBackdrop();

    let backdrop = document.createElement("div");
    backdrop.id = "loadingBackdrop";
    backdrop.className = "modal-backdrop fade";
    backdrop.setAttribute("role", "presentation");
    document.body.appendChild(backdrop);

    let spinnerContainer = document.createElement("div");
    spinnerContainer.id = "loadingSpinner";
    spinnerContainer.className =
      "position-fixed top-50 start-50 translate-middle text-center";
    spinnerContainer.setAttribute("role", "status");
    spinnerContainer.setAttribute("aria-live", "polite");
    spinnerContainer.innerHTML = `
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <div class="mt-2 text-white" aria-live="polite">Processing data...</div>
    `;
    document.body.appendChild(spinnerContainer);

    // Force reflow before adding show class
    backdrop.offsetHeight;
    backdrop.classList.add("show");
    document.body.classList.add("modal-open");
  }

  // Update the hideLoadingBackdrop function
  function hideLoadingBackdrop() {
    return new Promise((resolve) => {
      const backdrop = document.getElementById("loadingBackdrop");
      const spinner = document.getElementById("loadingSpinner");

      // Remove spinner immediately
      if (spinner) {
        spinner.remove();
      }

      // Remove backdrop with transition
      if (backdrop) {
        backdrop.classList.remove("show");
        backdrop.addEventListener("transitionend", function handler() {
          backdrop.removeEventListener("transitionend", handler);
          backdrop.remove();
          document.body.classList.remove("modal-open");
          // Clean up any other modal-related elements
          document
            .querySelectorAll(".modal-backdrop")
            .forEach((el) => el.remove());
          resolve();
        });

        // Fallback if transition doesn't fire
        setTimeout(() => {
          if (backdrop.parentElement) {
            backdrop.remove();
            document.body.classList.remove("modal-open");
            document
              .querySelectorAll(".modal-backdrop")
              .forEach((el) => el.remove());
            resolve();
          }
        }, 300);
      } else {
        resolve();
      }
    });
  }

  // Add this modal management code
  $(document).ready(function () {
    const importModal = document.getElementById("importDataModal");

    if (importModal) {
      // Store last focused element
      let lastFocusedElement;

      // Handle modal opening
      importModal.addEventListener("show.bs.modal", function () {
        // Store the current focused element
        lastFocusedElement = document.activeElement;

        // Remove problematic attributes
        this.removeAttribute("aria-hidden");

        // Set proper ARIA attributes
        this.setAttribute("aria-modal", "true");
        this.setAttribute("role", "dialog");
      });

      // Handle modal shown
      importModal.addEventListener("shown.bs.modal", function () {
        // Focus the first focusable element
        const firstFocusable = this.querySelector(
          'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
        );
        if (firstFocusable) {
          firstFocusable.focus();
        }
      });

      // Handle modal hiding
      importModal.addEventListener("hide.bs.modal", function () {
        // Remove aria-modal when closing
        this.removeAttribute("aria-modal");
      });

      // Handle modal hidden
      importModal.addEventListener("hidden.bs.modal", function () {
        // Restore focus to the last focused element
        if (lastFocusedElement) {
          lastFocusedElement.focus();
        }
      });

      // Trap focus within modal
      importModal.addEventListener("keydown", function (e) {
        if (e.key === "Tab") {
          const focusableElements = Array.from(
            this.querySelectorAll(
              'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
            )
          ).filter((el) => !el.hasAttribute("disabled"));

          if (!focusableElements.length) return;

          const firstFocusable = focusableElements[0];
          const lastFocusable = focusableElements[focusableElements.length - 1];
          const isTabPressed = e.key === "Tab";

          if (
            e.shiftKey &&
            isTabPressed &&
            document.activeElement === firstFocusable
          ) {
            lastFocusable.focus();
            e.preventDefault();
          } else if (
            !e.shiftKey &&
            isTabPressed &&
            document.activeElement === lastFocusable
          ) {
            firstFocusable.focus();
            e.preventDefault();
          }
        }
      });
    }

    // Update the modal initialization
    const bsModal = new bootstrap.Modal(importModal, {
      backdrop: "static", // Prevent closing by clicking outside
      keyboard: true, // Allow closing with Esc key
    });

    // Reserve for future purposes 
    // $("#importDataForm").on("submit", async function (e) {
    //   e.preventDefault();

    //   const formData = new FormData(this);
    //   const modal = bootstrap.Modal.getInstance(
    //     document.getElementById("importDataModal")
    //   );

    //   showLoadingBackdrop();

    //   try {
    //     // Update this URL to match your project structure
    //     const response = await $.ajax({
    //       url: "../services/audit_trail/import_audit_data.php", // Updated path
    //       type: "POST",
    //       data: formData,
    //       processData: false,
    //       contentType: false,
    //     });

    //     // Hide modal first
    //     if (modal) {
    //       modal.hide();
    //     }

    //     // Wait for backdrop to be fully removed
    //     await hideLoadingBackdrop();

    //     if (response.success) {
    //       await Swal.fire({
    //         title: "Success!",
    //         text: response.message || "Data imported successfully",
    //         icon: "success",
    //         confirmButtonText: "OK",
    //       });

    //       if (auditTable) {
    //         auditTable.ajax.reload();
    //       }
    //     } else {
    //       await Swal.fire({
    //         title: "Error!",
    //         text: response.message || "Failed to import data",
    //         icon: "error",
    //         confirmButtonText: "OK",
    //       });
    //     }
    //   } catch (error) {
    //     console.error("Import error:", error);

    //     // Hide modal first
    //     if (modal) {
    //       modal.hide();
    //     }

    //     // Wait for backdrop to be fully removed
    //     await hideLoadingBackdrop();

    //     await Swal.fire({
    //       title: "Error!",
    //       text: "An error occurred while importing data",
    //       icon: "error",
    //       confirmButtonText: "OK",
    //     });
    //   }
    // });
  });

  // Add cleanup on page unload
  $(window).on("unload", function () {
    hideLoadingBackdrop();
  });

  // Function to apply professional pagination styling
  function applyProfessionalPaginationStyling() {
    // Add professional styling to pagination
    $('.dataTables_paginate .pagination').css({
      'margin': '0',
      'justify-content': 'center'
    });

    // Style pagination buttons
    $('.dataTables_paginate .pagination .page-link').css({
      'color': '#007bff',
      'background-color': '#ffffff',
      'border': '1px solid #dee2e6',
      'border-radius': '6px',
      'margin': '0 2px',
      'padding': '8px 12px',
      'font-weight': '500',
      'transition': 'all 0.3s ease',
      'box-shadow': '0 1px 3px rgba(0,0,0,0.1)'
    });

    // Style active page
    $('.dataTables_paginate .pagination .page-item.active .page-link').css({
      'background-color': '#007bff',
      'border-color': '#007bff',
      'color': '#ffffff',
      'font-weight': '600',
      'box-shadow': '0 2px 6px rgba(0,123,255,0.3)'
    });

    // Style disabled buttons
    $('.dataTables_paginate .pagination .page-item.disabled .page-link').css({
      'color': '#6c757d',
      'background-color': '#f8f9fa',
      'border-color': '#dee2e6',
      'cursor': 'not-allowed'
    });

    // Add hover effects
    $('.dataTables_paginate .pagination .page-link').hover(
      function() {
        if (!$(this).parent().hasClass('disabled') && !$(this).parent().hasClass('active')) {
          $(this).css({
            'background-color': 'rgba(0,123,255,0.1)',
            'border-color': '#007bff',
            'transform': 'translateY(-1px)',
            'box-shadow': '0 2px 6px rgba(0,123,255,0.2)'
          });
        }
      },
      function() {
        if (!$(this).parent().hasClass('disabled') && !$(this).parent().hasClass('active')) {
          $(this).css({
            'background-color': '#ffffff',
            'border-color': '#dee2e6',
            'transform': 'translateY(0)',
            'box-shadow': '0 1px 3px rgba(0,0,0,0.1)'
          });
        }
      }
    );

    // Style info text
    $('.dataTables_info').css({
      'color': '#6c757d',
      'font-size': '14px',
      'font-weight': '500',
      'padding': '8px 0'
    });

    // Style length menu
    $('.dataTables_length select').css({
      'border': '1px solid #dee2e6',
      'border-radius': '6px',
      'padding': '6px 12px',
      'font-size': '14px',
      'color': '#495057',
      'background-color': '#ffffff',
      'box-shadow': '0 1px 3px rgba(0,0,0,0.1)'
    });

    $('.dataTables_length label').css({
      'color': '#495057',
      'font-weight': '500',
      'margin-bottom': '0'
    });
  }
});
