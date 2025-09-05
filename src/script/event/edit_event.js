$(document).ready(function() {
    // Function to find fuzzy matches for event types
    function findFuzzyMatch(eventType, selectElement) {
        const eventTypeLower = eventType.toLowerCase();
        const options = selectElement.find('option');
        
        // Define common synonyms and variations
        const synonyms = {
            'vaccination': 'Immunization',
            'vaccine': 'Immunization',
            'vaccines': 'Immunization',
            'immunisation': 'Immunization',
            'immunisations': 'Immunization',
            'vitamin': 'Vitamin A',
            'vitamins': 'Vitamin A',
            'deworm': 'Deworming',
            'deworming': 'Deworming',
            'timbang': 'Operation Timbang',
            'operation': 'Operation Timbang',
            'garantisado': 'Garantisadong Pambata',
            'pambata': 'Garantisadong Pambata',
            'nutrition': 'Nutrition Month',
            'feeding': 'Feeding Program',
            'health': 'Health Education',
            'education': 'Health Education',
            'medical': 'Medical Mission',
            'mission': 'Medical Mission'
        };
        
        // Check for exact synonym match
        if (synonyms[eventTypeLower]) {
            return synonyms[eventTypeLower];
        }
        
        // Check for partial synonym matches
        for (const [synonym, mappedValue] of Object.entries(synonyms)) {
            if (eventTypeLower.includes(synonym) || synonym.includes(eventTypeLower)) {
                return mappedValue;
            }
        }
        
        // Check for similar words using Levenshtein distance approximation
        let bestMatch = null;
        let bestScore = 0;
        
        options.each(function() {
            const optionValue = $(this).val();
            if (optionValue) {
                const optionLower = optionValue.toLowerCase();
                const score = calculateSimilarity(eventTypeLower, optionLower);
                if (score > bestScore && score > 0.6) { // Threshold of 60% similarity
                    bestScore = score;
                    bestMatch = optionValue;
                }
            }
        });
        
        return bestMatch;
    }
    
    // Simple similarity calculation (not true Levenshtein, but good enough)
    function calculateSimilarity(str1, str2) {
        if (str1 === str2) return 1.0;
        if (str1.length === 0 || str2.length === 0) return 0.0;
        
        const longer = str1.length > str2.length ? str1 : str2;
        const shorter = str1.length > str2.length ? str2 : str1;
        
        if (longer.length === 0) return 1.0;
        
        const editDistance = longer.length - shorter.length;
        const maxLength = longer.length;
        
        return (maxLength - editDistance) / maxLength;
    }

    const editModalEl = document.getElementById('editEventModal');
    if (!editModalEl) {
        console.error('editEventModal element not found');
        return;
    }

    // Add real-time validation for form fields
    $('#edit_event_type').on('change', function() {
        const value = $(this).val();
        if (!value || value.trim() === '') {
            showFieldError('edit_event_type', 'Event Type is required');
        } else {
            $(this).removeClass('is-invalid').addClass('is-valid');
            $('#edit_event_type_error').text('').hide();
            
            // Show hint text again
            $(this).next('.form-text').show();
        }
    });

    $('#edit_event_name').on('input', function() {
        const value = $(this).val();
        if (!value || value.trim() === '') {
            showFieldError('edit_event_name', 'Event Name is required');
        } else if (value.trim().length > 255) {
            showFieldError('edit_event_name', 'Event Name cannot exceed 255 characters');
        } else {
            $(this).removeClass('is-invalid').addClass('is-valid');
            $('#edit_event_name_error').text('').hide();
            
            // Show hint text again
            $(this).next('.form-text').show();
        }
    });

    $('#edit_event_time').on('change', function() {
        const value = $(this).val();
        if (!value || value.trim() === '') {
            showFieldError('edit_event_time', 'Event Time is required');
        } else {
            // Convert time to 24-hour format for comparison
            const timeParts = value.split(':');
            const hour = parseInt(timeParts[0]);
            const minute = parseInt(timeParts[1]);
            
            // Check if time is between 6:00 AM (06:00) and 5:00 PM (17:00)
            if (hour < 6 || hour > 17 || (hour === 17 && minute > 0)) {
                showFieldError('edit_event_time', 'Event Time must be between 6:00 AM and 5:00 PM');
                    } else {
            $(this).removeClass('is-invalid').addClass('is-valid');
            $('#edit_event_time_error').text('').hide();
            
            // Show hint text again
            $(this).next('.form-text').show();
        }
        }
    });

    $('#edit_event_place').on('input', function() {
        const value = $(this).val();
        if (!value || value.trim() === '') {
            showFieldError('edit_event_place', 'Event Place is required');
        } else if (value.trim().length > 255) {
            showFieldError('edit_event_place', 'Event Place cannot exceed 255 characters');
        } else {
            $(this).removeClass('is-invalid').addClass('is-valid');
            $('#edit_event_place_error').text('').hide();
            
            // Show hint text again
            $(this).next('.form-text').show();
        }
    });

    $('#edit_event_date').on('change', function() {
        const value = $(this).val();
        if (!value || value.trim() === '') {
            showFieldError('edit_event_date', 'Event Date is required');
        } else {
            const selectedDate = new Date(value);
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            if (selectedDate < today) {
                showFieldError('edit_event_date', 'Event Date cannot be in the past');
            } else {
                $(this).removeClass('is-invalid').addClass('is-valid');
                $('#edit_event_date_error').text('').hide();
                
                // Show hint text again
                $(this).next('.form-text').show();
            }
        }
    });

    $('#edit_min_age').on('input', function() {
        const value = parseInt($(this).val());
        if (isNaN(value) || value <= 0) {
            showFieldError('edit_min_age', 'Minimum Age must be greater than 0');
        } else {
            $(this).removeClass('is-invalid').addClass('is-valid');
            $('#edit_min_age_error').text('').hide();
            
            // Show hint text again
            $(this).next('.form-text').show();
        }
    });

    $('#edit_max_age').on('input', function() {
        const value = parseInt($(this).val());
        if (isNaN(value) || value > 14) {
            showFieldError('edit_max_age', 'Maximum Age cannot exceed 14 years');
        } else {
            $(this).removeClass('is-invalid').addClass('is-valid');
            $('#edit_max_age_error').text('').hide();
            
            // Show hint text again
            $(this).next('.form-text').show();
        }
    });

    // Reset form when modal is shown
    $('#editEventModal').on('shown.bs.modal', function() {
        console.log('Edit event modal shown, ensuring form is ready');
        
        // Ensure the form is properly initialized
        const form = document.getElementById('editEventForm');
        if (form) {
            // Remove any validation classes and clear error messages
            form.querySelectorAll('.form-control, .form-select').forEach(input => {
                input.classList.remove('is-valid', 'is-invalid');
            });
            clearValidationErrors();
        }
        
        // Ensure the event type select is properly initialized
        const eventTypeSelect = $('#edit_event_type');
        if (eventTypeSelect.length > 0) {
            console.log('Event type select found, ensuring it\'s ready');
            // Force a refresh of the select element
            eventTypeSelect.trigger('change');
            
            // If there's a stored event type value, try to set it again
            if (window.storedEventType) {
                console.log('Attempting to set stored event type:', window.storedEventType);
                setTimeout(() => {
                    // Try to set the value with case-insensitive matching
                    let valueSet = false;
                    
                    // First try exact value match
                    eventTypeSelect.val(window.storedEventType);
                    if (eventTypeSelect.val() === window.storedEventType) {
                        valueSet = true;
                        console.log('Stored event type set to:', window.storedEventType);
                    } else {
                        // Try case-insensitive text matching
                        const matchingOption = eventTypeSelect.find('option').filter(function() {
                            const optionText = $(this).text().trim().toLowerCase();
                            const dataText = window.storedEventType.trim().toLowerCase();
                            return optionText === dataText;
                        });
                        
                        if (matchingOption.length > 0) {
                            eventTypeSelect.val(matchingOption.val());
                            valueSet = true;
                            console.log('Stored event type set using case-insensitive matching:', matchingOption.val());
                        } else {
                            // Try fuzzy matching as a last resort
                            const fuzzyMatch = findFuzzyMatch(window.storedEventType, eventTypeSelect);
                            if (fuzzyMatch) {
                                eventTypeSelect.val(fuzzyMatch);
                                valueSet = true;
                                console.log('Stored event type set using fuzzy matching:', fuzzyMatch);
                            }
                        }
                    }
                    
                    console.log('Stored event type successfully set:', valueSet);
                    console.log('Current select value:', eventTypeSelect.val());
                }, 50);
            }
        }
    });

    // Custom modal hide function (same as in event.js)
    function hideModal(modalEl) {
        if (!modalEl) {
            console.error('hideModal: modalEl is null');
            return;
        }
        
        // Hide backdrop
        const backdropEl = document.querySelector('.modal-custom-backdrop');
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

    // Function to clear all validation errors
    function clearValidationErrors() {
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('').hide();
        
        // Show hint text again
        $('.form-text').show();
    }

    // Function to show validation error for a specific field
    function showFieldError(fieldId, message) {
        $(`#${fieldId}`).addClass('is-invalid');
        $(`#${fieldId}_error`).text(message).show();
        
        // Hide the hint text when showing error
        $(`#${fieldId}`).next('.form-text').hide();
    }

    // Function to validate form fields
    function validateForm() {
        let isValid = true;
        clearValidationErrors();

        // Validate Event Type
        const eventType = $('#edit_event_type').val();
        if (!eventType || eventType.trim() === '') {
            showFieldError('edit_event_type', 'Event Type is required');
            isValid = false;
        }

        // Validate Event Name
        const eventName = $('#edit_event_name').val();
        if (!eventName || eventName.trim() === '') {
            showFieldError('edit_event_name', 'Event Name is required');
            isValid = false;
        } else if (eventName.trim().length > 255) {
            showFieldError('edit_event_name', 'Event Name cannot exceed 255 characters');
            isValid = false;
        }

        // Validate Event Time
        const eventTime = $('#edit_event_time').val();
        if (!eventTime || eventTime.trim() === '') {
            showFieldError('edit_event_time', 'Event Time is required');
            isValid = false;
        } else {
            // Check if time is between 6:00 AM and 5:00 PM
            const timeParts = eventTime.split(':');
            const hour = parseInt(timeParts[0]);
            const minute = parseInt(timeParts[1]);
            
            if (hour < 6 || hour > 17 || (hour === 17 && minute > 0)) {
                showFieldError('edit_event_time', 'Event Time must be between 6:00 AM and 5:00 PM');
                isValid = false;
            }
        }

        // Validate Event Place
        const eventPlace = $('#edit_event_place').val();
        if (!eventPlace || eventPlace.trim() === '') {
            showFieldError('edit_event_place', 'Event Place is required');
            isValid = false;
        } else if (eventPlace.trim().length > 255) {
            showFieldError('edit_event_place', 'Event Place cannot exceed 255 characters');
            isValid = false;
        }

        // Validate Event Date
        const eventDate = $('#edit_event_date').val();
        if (!eventDate || eventDate.trim() === '') {
            showFieldError('edit_event_date', 'Event Date is required');
            isValid = false;
        } else {
            // Check if date is in the past
            const selectedDate = new Date(eventDate);
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            if (selectedDate < today) {
                showFieldError('edit_event_date', 'Event Date cannot be in the past');
                isValid = false;
            }
        }

        // Validate Minimum Age
        const minAge = parseInt($('#edit_min_age').val());
        if (isNaN(minAge) || minAge <= 0) {
            showFieldError('edit_min_age', 'Minimum Age must be greater than 0');
            isValid = false;
        }

        // Validate Maximum Age
        const maxAge = parseInt($('#edit_max_age').val());
        if (isNaN(maxAge) || maxAge > 14) {
            showFieldError('edit_max_age', 'Maximum Age cannot exceed 14 years');
            isValid = false;
        }

        // Validate Age Range
        if (isValid && minAge > maxAge) {
            showFieldError('edit_min_age', 'Minimum Age cannot be greater than Maximum Age');
            showFieldError('edit_max_age', 'Maximum Age cannot be less than Minimum Age');
            isValid = false;
        }

        return isValid;
    }

    $('#editEventForm').on('submit', function(e) {
        e.preventDefault();
        
        // Clear previous validation errors
        clearValidationErrors();
        
        // Validate form before submission
        if (!validateForm()) {
            return false;
        }

        const submitButton = $(this).find('button[type="submit"]');
        const originalHtml = submitButton.html();
        
        // Show only spinner in button
        submitButton.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>');
        submitButton.prop('disabled', true);

        const formData = new FormData(this);
        formData.append('action', 'updateEvent');

        setTimeout(() => {
            for (let [key, value] of formData.entries()) {
            }
            
            $.ajax({
                url: '../../src/controllers/EventController.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    // If response is a string, try to parse it as JSON
                    if (typeof response === 'string') {
                        try {
                            response = JSON.parse(response);
                        } catch (e) {
                            console.error('Failed to parse response as JSON:', e);
                        }
                    }
                    
                    if (response.success) {
                        // Hide modal using custom approach
                        hideModal(editModalEl);
                        $('#editEventForm')[0].reset();
                        
                        // Reload table immediately
                        window.eventTable.ajax.reload(null, false);
                        
                        // Show success message
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: 'Event updated successfully',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    } else {
                        // Show server-side validation errors
                        if (response.message) {
                            // Check if it's a specific field error or general error
                            if (response.message.includes('Event Type')) {
                                showFieldError('edit_event_type', response.message);
                            } else if (response.message.includes('Event Name')) {
                                showFieldError('edit_event_name', response.message);
                            } else if (response.message.includes('Event Time')) {
                                showFieldError('edit_event_time', response.message);
                            } else if (response.message.includes('Event Place')) {
                                showFieldError('edit_event_place', response.message);
                            } else if (response.message.includes('Event Date')) {
                                showFieldError('edit_event_date', response.message);
                            } else if (response.message.includes('Minimum Age') || response.message.includes('min_age')) {
                                showFieldError('edit_min_age', response.message);
                            } else if (response.message.includes('Maximum Age') || response.message.includes('max_age')) {
                                showFieldError('edit_max_age', response.message);
                            } else {
                                // General error - show at top of form
                                showFieldError('edit_event_type', response.message);
                            }
                        }
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', {xhr, status, error});
                    console.error('Response Text:', xhr.responseText);
                    
                    // Show connection error
                    showFieldError('edit_event_type', 'Failed to connect to the server. Please try again.');
                },
                complete: function() {
                    // Restore button state
                    submitButton.html(originalHtml);
                    submitButton.prop('disabled', false);
                }
            });
        }, 1000);
    });
});