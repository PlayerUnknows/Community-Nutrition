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

      // Initialize new instance
      auditTable = $("#auditTable").DataTable({
        processing: true,
        serverSide: false,
        ajax: {
          url: "../backend/fetch_audit_trail.php",
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
              if (!data) return "N/A";
              try {
                if (row.action === "LOGIN" || row.action === "LOGOUT") {
                  return data || "User " + row.action.toLowerCase();
                }

                // Handle file operations
                if (
                  row.action === "FILE_DOWNLOAD" ||
                  row.action === "FILE_EXPORT" ||
                  row.action === "FILE_IMPORT"
                ) {
                  const details = JSON.parse(data);
                  let detailsHtml = '<div class="audit-details">';

                  // Common file details
                  if (details.filename) {
                    detailsHtml += `<div><strong>Filename:</strong> ${details.filename}</div>`;
                  }

                  // Operation-specific details
                  switch (row.action) {
                    case "FILE_DOWNLOAD":
                      if (details.file_type) {
                        detailsHtml += `<div><strong>File Type:</strong> ${details.file_type}</div>`;
                      }
                      break;
                    case "FILE_EXPORT":
                      if (details.format) {
                        detailsHtml += `<div><strong>Format:</strong> ${details.format}</div>`;
                      }
                      if (details.export_type) {
                        detailsHtml += `<div><strong>Export Type:</strong> ${details.export_type}</div>`;
                      }
                      break;
                    case "FILE_IMPORT":
                      if (details.import_type) {
                        detailsHtml += `<div><strong>Import Type:</strong> ${details.import_type}</div>`;
                      }
                      if (details.status) {
                        detailsHtml += `<div><strong>Status:</strong> ${details.status}</div>`;
                      }
                      if (details.additional_details) {
                        detailsHtml += `<div><strong>Additional Details:</strong> ${details.additional_details}</div>`;
                      }
                      break;
                  }

                  detailsHtml += "</div>";
                  return detailsHtml;
                }

                // For other actions, display all details
                const details = JSON.parse(data);
                let detailsHtml = '<div class="audit-details">';
                for (const [key, value] of Object.entries(details)) {
                  const displayKey = key
                    .replace(/_/g, " ")
                    .replace(/\b\w/g, (l) => l.toUpperCase());
                  detailsHtml += `<div><strong>${displayKey}:</strong> ${value}</div>`;
                }
                detailsHtml += "</div>";
                return detailsHtml;
              } catch (e) {
                return data || "N/A";
              }
            },
          },
          {
            data: "action_timestamp",
            width: "20%",
            render: function (data) {
              if (!data || data === "-") {
                return "N/A";
              }
              return moment(data).format("YYYY-MM-DD HH:mm:ss");
            },
          },
          {
            data: "count",
            width: "10%",
            defaultContent: "1",
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
        dom: `<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>
              <'row'<'col-sm-12'tr>>
              <'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>`,
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
        },
      });

      // Handle window resize
      $(window).on("resize", function () {
        updateScrollIndicator();
      });

      // Handle content toggle
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
      url: "../backend/fetch_audit_trail.php",
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

    // Update form submission handler
    $("#importDataForm").on("submit", async function (e) {
      e.preventDefault();

      const formData = new FormData(this);
      const modal = bootstrap.Modal.getInstance(
        document.getElementById("importDataModal")
      );

      showLoadingBackdrop();

      try {
        // Update this URL to match your project structure
        const response = await $.ajax({
          url: "../backend/audit_trail/import_audit_data.php", // Updated path
          type: "POST",
          data: formData,
          processData: false,
          contentType: false,
        });

        // Hide modal first
        if (modal) {
          modal.hide();
        }

        // Wait for backdrop to be fully removed
        await hideLoadingBackdrop();

        if (response.success) {
          await Swal.fire({
            title: "Success!",
            text: response.message || "Data imported successfully",
            icon: "success",
            confirmButtonText: "OK",
          });

          if (auditTable) {
            auditTable.ajax.reload();
          }
        } else {
          await Swal.fire({
            title: "Error!",
            text: response.message || "Failed to import data",
            icon: "error",
            confirmButtonText: "OK",
          });
        }
      } catch (error) {
        console.error("Import error:", error);

        // Hide modal first
        if (modal) {
          modal.hide();
        }

        // Wait for backdrop to be fully removed
        await hideLoadingBackdrop();

        await Swal.fire({
          title: "Error!",
          text: "An error occurred while importing data",
          icon: "error",
          confirmButtonText: "OK",
        });
      }
    });
  });

  // Add cleanup on page unload
  $(window).on("unload", function () {
    hideLoadingBackdrop();
  });
});
