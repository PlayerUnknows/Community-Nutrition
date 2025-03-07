// Form validation
(function () {
  "use strict";

  const form = document.getElementById("signupForm");
  const alert = document.getElementById("formAlert");

  // Initialize toast
  const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
    didOpen: (toast) => {
      toast.addEventListener('mouseenter', Swal.stopTimer)
      toast.addEventListener('mouseleave', Swal.resumeTimer)
    }
  });

  // Copy button functionality
  document.querySelectorAll('.copy-btn').forEach(button => {
    button.addEventListener('click', function() {
      const inputId = this.getAttribute('data-copy');
      const input = document.getElementById(inputId);
      input.select();
      document.execCommand('copy');
      
      // Show feedback
      const originalText = this.innerHTML;
      this.innerHTML = '<i class="fas fa-check"></i>';
      setTimeout(() => {
        this.innerHTML = originalText;
      }, 2000);

      // Show toast for copy success
      Toast.fire({
        icon: 'success',
        title: 'Copied to clipboard'
      });
    });
  });

  form.addEventListener(
    "submit",
    function (event) {
      event.preventDefault();

      // Check privacy agreement
      const privacyCheckbox = document.getElementById('privacyAgreement');
      if (!privacyCheckbox.checked) {
        privacyCheckbox.classList.add('is-invalid');
        event.stopPropagation();
        alert.style.display = "block";
        return;
      }

      if (!form.checkValidity()) {
        event.stopPropagation();
        alert.style.display = "block";
      } else {
        alert.style.display = "none";

        const submitButton = $(this).find('button[type="submit"]');
        const originalButtonText = submitButton.html();
        
        // Show loading state in button
        submitButton.prop("disabled", true).html(
          '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Creating account...'
        );

        setTimeout(() => {
          $.ajax({
            url: "../../src/controllers/UserController.php?action=signup",
            type: "POST",
            data: {
              firstName: $("#firstName").val().trim(),
              middleName: $("#middleName").val().trim(),
              lastName: $("#lastName").val().trim(),
              suffix: $("#suffix").val().trim(),
              email: $("#email").val().trim(),
              role: $("#role").val(),
            },
            success: function (response) {
              try {
                response = JSON.parse(response);
                if (response.success) {
                  // Show success toast
                  Toast.fire({
                    icon: 'success',
                    title: 'Account created successfully!'
                  });

                  // Display credentials in modal
                  $('#userIdDisplay').val(response.userId);
                  $('#tempPasswordDisplay').val(response.tempPassword);
                  
                  // Show modal using the global variable
                  if (typeof credentialsModal !== 'undefined') {
                    try {
                      // Hide any existing modals first
                      $('.modal').modal('hide');
                      // Show credentials modal
                      setTimeout(() => {
                        credentialsModal.show();
                      }, 1000); // Slight delay after toast
                    } catch (e) {
                      console.error('Modal error:', e);
                      // Fallback alert
                      Toast.fire({
                        icon: 'info',
                        title: 'Please save your credentials',
                        html: `User ID: ${response.userId}<br>Password: ${response.tempPassword}`
                      });
                    }
                  }

                  // Reset form
                  form.reset();
                  form.classList.remove("was-validated");
                  if (typeof refreshTable === "function") {
                    refreshTable("usersTable");
                    refreshTable("auditTable");
                  }
                } else {
                  // Show error toast
                  Toast.fire({
                    icon: 'error',
                    title: response.message || 'Failed to create account'
                  });
                }
              } catch (e) {
                console.error('Response parsing error:', e);
                Toast.fire({
                  icon: 'error',
                  title: 'An unexpected error occurred'
                });
              }
            },
            error: function (xhr, status, error) {
              console.error('Ajax error:', status, error);
              Toast.fire({
                icon: 'error',
                title: 'Failed to connect to the server'
              });
            },
            complete: function () {
              // Restore button state after minimum 3 seconds
              setTimeout(() => {
                submitButton.prop("disabled", false).html(originalButtonText);
              }, 3000);
            },
          });
        }, 1000); // Add initial delay before AJAX call
      }

      form.classList.add("was-validated");
    },
    false
  );

  // Hide alert when user starts fixing errors
  form.addEventListener("input", function (event) {
    if (event.target.id === 'privacyAgreement' && event.target.checked) {
        event.target.classList.remove('is-invalid');
    }
    if (form.checkValidity()) {
      alert.style.display = "none";
    }
    // Remove is-invalid class when user starts typing
    $(this).find(".is-invalid").removeClass("is-invalid");
  });
})();
