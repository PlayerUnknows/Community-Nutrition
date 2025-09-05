$(document).ready(function() {
    // Get references to modal elements
    const addModalEl = document.getElementById("addEventModal");
    // Create a backdrop div if needed
    let backdropEl = document.querySelector(".modal-custom-backdrop");
    
    if (!backdropEl) {
        backdropEl = document.createElement("div");
        backdropEl.className = "modal-custom-backdrop";
        document.body.appendChild(backdropEl);

        // Add styles for custom backdrop and validation
        const style = document.createElement("style");
        style.textContent = `
            .modal-custom-backdrop {
                position: fixed;
                top: 0;
                left: 0;
                z-index: 1040;
                width: 100vw;
                height: 100vh;
                background-color: rgba(0, 0, 0, 0.5);
                display: none;
            }
            .modal-custom-backdrop.show {
                display: block;
            }
            .custom-error {
                display: none;
                width: 100%;
                margin-top: 0.25rem;
                font-size: 0.875em;
                color: #dc3545;
            }
            .custom-error.show {
                display: block;
            }
            .form-control.has-error {
                border-color: #dc3545;
                padding-right: calc(1.5em + 0.75rem);
                background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'%3e%3c/svg%3e");
                background-repeat: no-repeat;
                background-position: right calc(0.375em + 0.1875rem) center;
                background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
            }
            .form-control.is-valid {
                border-color: #198754;
                padding-right: calc(1.5em + 0.75rem);
                background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 8 8'%3e%3cpath fill='%23198754' d='M2.3 6.73L.6 4.53c-.4-1.04.46-1.4 1.1-.8l1.1 1.4 3.4-3.8c.6-.63 1.6-.27 1.2.7l-4 4.6c-.43.5-.8.4-1.1.1z'/%3e%3c/svg%3e");
                background-repeat: no-repeat;
                background-position: right calc(0.375em + 0.1875rem) center;
                background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
            }
        `;
        document.head.appendChild(style);
    }

    // Add real-time validation to form inputs
    const setupFormValidation = () => {
        const form = document.getElementById("addEventForm");
        if (form) {
            // Add validation on input change
            form.querySelectorAll(".form-control").forEach((input) => {
                input.addEventListener("blur", function () {
                    validateInput(this);
                });

                input.addEventListener("input", function () {
                    validateInput(this);
                });

                // For date/time fields
                input.addEventListener("change", function () {
                    validateInput(this);
                });
            });
        }
    };

    // Validation function for individual inputs
    const validateInput = (input) => {
        const fieldName = input.name;
        const value = input.value.trim();
        let isValid = true;
        let errorMessage = '';

        // Remove existing error styling
        input.classList.remove('has-error');
        hideError(input);

        // Validation rules
        switch (fieldName) {
            case 'event_type':
                if (!value) {
                    isValid = false;
                    errorMessage = 'Event type is required';
                }
                break;
            
            case 'event_name':
                if (!value) {
                    isValid = false;
                    errorMessage = 'Event name is required';
                } else if (value.length < 2) {
                    isValid = false;
                    errorMessage = 'Event name must be at least 2 characters long';
                } else if (value.length > 255) {
                    isValid = false;
                    errorMessage = 'Event name cannot exceed 255 characters';
                }
                break;
            
            case 'event_time':
                if (!value) {
                    isValid = false;
                    errorMessage = 'Event time is required';
                } else {
                    const timePattern = /^([01]?[0-9]|2[0-3]):[0-5][0-9]$/;
                    if (!timePattern.test(value)) {
                        isValid = false;
                        errorMessage = 'Invalid time format. Use HH:MM (24-hour format)';
                    }
                }
                break;
            
            case 'event_place':
                if (!value) {
                    isValid = false;
                    errorMessage = 'Event place is required';
                } else if (value.length < 2) {
                    isValid = false;
                    errorMessage = 'Event place must be at least 2 characters long';
                } else if (value.length > 255) {
                    isValid = false;
                    errorMessage = 'Event place cannot exceed 255 characters';
                }
                break;
            
            case 'event_date':
                if (!value) {
                    isValid = false;
                    errorMessage = 'Event date is required';
                } else {
                    const selectedDate = new Date(value);
                    const today = new Date();
                    today.setHours(0, 0, 0, 0);
                    
                    if (selectedDate < today) {
                        isValid = false;
                        errorMessage = 'Event date must be today or a future date';
                    }
                }
                break;
            
            case 'min_age':
                if (!value) {
                    isValid = false;
                    errorMessage = 'Minimum age is required';
                } else {
                    const minAge = parseInt(value);
                    if (minAge <= 0) {
                        isValid = false;
                        errorMessage = 'Minimum age cannot be 0 or negative';
                    } else if (minAge > 14) {
                        isValid = false;
                        errorMessage = 'Minimum age cannot exceed 14 years';
                    }
                }
                break;
            
            case 'max_age':
                if (!value) {
                    isValid = false;
                    errorMessage = 'Maximum age is required';
                } else {
                    const maxAge = parseInt(value);
                    if (maxAge <= 0) {
                        isValid = false;
                        errorMessage = 'Maximum age cannot be 0 or negative';
                    } else if (maxAge > 14) {
                        isValid = false;
                        errorMessage = 'Maximum age cannot exceed 14 years';
                    } else {
                        // Check if max_age is less than min_age
                        const minAgeInput = document.querySelector('input[name="min_age"]');
                        if (minAgeInput && minAgeInput.value) {
                            const minAge = parseInt(minAgeInput.value);
                            if (maxAge < minAge) {
                                isValid = false;
                                errorMessage = 'Maximum age cannot be less than minimum age';
                            }
                        }
                    }
                }
                break;
        }

        // Apply validation result
        if (!isValid) {
            input.classList.add('has-error');
            showError(input, errorMessage);
        } else {
            input.classList.remove('has-error');
            hideError(input);
        }

        return isValid;
    };

    // Show error message under input
    const showError = (input, message) => {
        // Remove existing error message
        hideError(input);
        
        // Create error element
        const errorDiv = document.createElement('div');
        errorDiv.className = 'custom-error show';
        errorDiv.textContent = message;
        
        // Insert after the input
        input.parentNode.appendChild(errorDiv);
    };

    // Hide error message
    const hideError = (input) => {
        const existingError = input.parentNode.querySelector('.custom-error');
        if (existingError) {
            existingError.remove();
        }
    };

    // Validate entire form
    const validateForm = () => {
        const form = document.getElementById("addEventForm");
        if (!form) return false;

        let isValid = true;
        const inputs = form.querySelectorAll('.form-control');
        
        inputs.forEach(input => {
            if (!validateInput(input)) {
                isValid = false;
            }
        });

        return isValid;
    };

    // Initial setup of form validation
    setupFormValidation();

    // Custom modal functions
    function showModal(modalEl) {
        if (!modalEl) {
            console.error('showModal: modalEl is null');
            return;
        }
        
        // Show backdrop
        if (backdropEl) {
            backdropEl.classList.add('show');
        }
        
        // Show modal
        modalEl.classList.add('show');
        modalEl.style.display = 'block';
        modalEl.removeAttribute('aria-hidden');
        modalEl.setAttribute('aria-modal', 'true');
        modalEl.setAttribute('role', 'dialog');
        
        // Add body class
        document.body.classList.add('modal-open');
        document.body.style.overflow = 'hidden';
                
        // Focus first input
        setTimeout(() => {
            const firstInput = modalEl.querySelector('input, select, textarea');
            if (firstInput) firstInput.focus();
        }, 100);
    }
    
    function hideModal(modalEl) {
        if (!modalEl) {
            console.error('hideModal: modalEl is null');
            return;
        }
        
        // Hide backdrop
        if (backdropEl) {
            backdropEl.classList.remove('show');
        }
        
        // Hide modal
        modalEl.classList.remove('show');
        modalEl.style.display = 'none';
        modalEl.removeAttribute('aria-modal');
        modalEl.removeAttribute('role');
        
        // Reset body
        document.body.classList.remove('modal-open');
        document.body.style.overflow = '';
        document.body.style.paddingRight = '';
        
        // Remove any existing bootstrap backdrops (cleanup)
        document.querySelectorAll('.modal-backdrop').forEach((el) => el.remove());
        
    }

    $('#addEventForm').on('submit', function(e) {
        e.preventDefault();
        
        // First validate the entire form
        if (!validateForm()) {
            // Form is invalid - scroll to first error
            const firstError = document.querySelector('.form-control.has-error');
            if (firstError) {
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                firstError.focus();
            }
            return;
        }

        const submitButton = $(this).find('button[type="submit"]');
        const originalHtml = submitButton.html();
        
        // Show only spinner in button
        submitButton.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>');
        submitButton.prop('disabled', true);

        const formData = new FormData(this);
        formData.append('action', 'addEvent');

        // Define Toast before using it
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        });

        setTimeout(() => {
            $.ajax({
                url: '../controllers/EventController.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        // Hide modal using custom approach
                        hideModal(addModalEl);
                        $('#addEventForm')[0].reset();
                        
                        // Reload table immediately
                        if (window.eventTable) {
                            window.eventTable.ajax.reload();
                        } else {
                            console.warn('eventTable not available yet');
                        }
                        
                        // Show toast notification
                        Toast.fire({
                            icon: 'success',
                            title: 'Successfully added!'
                        });
                    } else {
                        Toast.fire({
                            icon: 'error',
                            title: response.message || 'Failed to add event'
                        });
                    }
                },
                error: function() {
                    Toast.fire({
                        icon: 'error',
                        title: 'Failed to connect to the server'
                    });
                },
                complete: function() {
                    // Restore button state
                    submitButton.html(originalHtml);
                    submitButton.prop('disabled', false);
                }
            });
        }, 1000);
    });

    // Handle close buttons - replace Bootstrap's handlers with our own
    if (addModalEl) {
        addModalEl
            .querySelectorAll('[data-bs-dismiss="modal"]')
            .forEach((button) => {
                // Remove bootstrap's default handler
                button.removeAttribute("data-bs-dismiss");

                button.addEventListener("click", function (e) {
                    e.preventDefault();
                    e.stopPropagation(); // Stop event propagation
                    hideModal(addModalEl);
                });
            });
    }

    // Handle ESC key
    document.addEventListener("keydown", function (e) {
        if (
            e.key === "Escape" &&
            addModalEl &&
            addModalEl.classList.contains("show")
        ) {
            hideModal(addModalEl);
        }
    });

    // Handle backdrop click
    if (backdropEl) {
        backdropEl.addEventListener("click", function (e) {
            if (e.target === backdropEl) {
                hideModal(addModalEl);
            }
        });
    }

    // Initial cleanup
    hideModal(addModalEl);
});


  