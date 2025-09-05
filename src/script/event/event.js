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
    
    // Get references to modal elements with better error handling
    let addModalEl, editModalEl, viewModalEl;
    
    // Function to safely get modal elements
    function getModalElements() {
        addModalEl = document.getElementById('addEventModal');
        editModalEl = document.getElementById('editEventModal');
        viewModalEl = document.getElementById('viewEventModal');
    
    }
    
    // Function to ensure a specific modal is available
    function ensureModalAvailable(modalType) {
        let modalEl;
        switch(modalType) {
            case 'add':
                modalEl = addModalEl || document.getElementById('addEventModal');
                if (modalEl) addModalEl = modalEl;
                break;
            case 'edit':
                modalEl = editModalEl || document.getElementById('editEventModal');
                if (modalEl) editModalEl = modalEl;
                break;
            case 'view':
                modalEl = viewModalEl || document.getElementById('viewEventModal');
                if (modalEl) viewModalEl = modalEl;
                break;
        }
        return modalEl;
    }
    
    // Get modal elements immediately
    getModalElements();
    
    // Retry getting modal elements if they're not found (fallback)
    if (!addModalEl || !editModalEl || !viewModalEl) {
        setTimeout(getModalElements, 100);
    }
    
    // Create custom backdrop
    let backdropEl = document.querySelector('.modal-custom-backdrop');
    if (!backdropEl) {
        backdropEl = document.createElement('div');
        backdropEl.className = 'modal-custom-backdrop';
        document.body.appendChild(backdropEl);
        
        // Add styles for custom backdrop
        const style = document.createElement('style');
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
            .modal.show {
                display: block !important;
                z-index: 1050;
            }
            .modal:not(.show) {
                display: none !important;
            }
        `;
        document.head.appendChild(style);
    }

    // Initialize DataTable with simplified configuration
    window.eventTable = $('#eventTable').DataTable({
        processing: true,
        serverSide: false,
        pageLength: 5,
        lengthMenu: [[5, 10, 25, 50], [5, 10, 25, 50]],
        order: [[4, 'desc']],
        ajax: {
            url: '../controllers/EventController.php',
            type: 'POST',
            data: { action: 'fetchAll' } 
        },
        columns: [
            { data: 'event_type' },
            { data: 'event_name_created' },
            { data: 'event_time' },
            { data: 'event_place' },
            { data: 'event_date' },
            { 
                data: null,
                render: function(data, type, row) {
                    return `${row.min_age} - ${row.max_age}`;
                },
                title: 'Age Range'
            },
            {
                data: null,
                render: function(data, type, row) {
                    return `
                        <button onclick="viewEventDetails(${row.event_prikey})" class="btn btn-sm btn-outline-info">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button onclick="editEvent(${row.event_prikey})" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger delete-event" data-id="${row.event_prikey}">
                            <i class="fas fa-trash"></i>
                        </button>
                    `;
                }
            }
        ]
    });

    // Custom modal functions
    function showModal(modalEl) {
        if (!modalEl) {
            console.error('showModal: modalEl is null');
            console.trace('Stack trace for showModal call');
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
    
    // Make modal functions globally available
    window.showModal = showModal;
    window.hideModal = hideModal;

    // Define editEvent function in global scope
    window.editEvent = function(eventId) {
        // Ensure modal element is available
        const modalEl = ensureModalAvailable('edit');
        if (!modalEl) {
            console.error('editModalEl still not found after retry');
            return;
        }
        
        const data = window.eventTable.rows().data().toArray()
            .find(row => row.event_prikey == eventId);
            
        if (data) {

            
            // Normalize event type for better matching
            const normalizedEventType = data.event_type ? data.event_type.trim() : '';

            // Store event type globally for fallback
            window.storedEventType = normalizedEventType;

            
            // Set form values
            $('#edit_event_prikey').val(data.event_prikey);
            
            // Set event type with debugging
            const eventTypeSelect = $('#edit_event_type');
         
            // Try to set the value
            eventTypeSelect.val(normalizedEventType);
            
            // Verify the value was set
            const selectedValue = eventTypeSelect.val();
            
            // If value wasn't set, try to find a matching option
            if (!selectedValue && normalizedEventType) {
                const matchingOption = eventTypeSelect.find(`option[value="${data.event_type}"]`);
                if (matchingOption.length > 0) {
                    eventTypeSelect.val(data.event_type);
                } else {
                    console.warn('No matching option found for event type:', normalizedEventType);
                    
                    // Try to find by text content (case-insensitive)
                    const textMatchingOption = eventTypeSelect.find('option').filter(function() {
                        const optionText = $(this).text().trim().toLowerCase();
                        const dataText = normalizedEventType.toLowerCase();
                        return optionText === dataText;
                    });
                    
                    if (textMatchingOption.length > 0) {
                        eventTypeSelect.val(textMatchingOption.val());
                    } else {
                        // Try to find by partial match (case-insensitive)
                        const partialMatchingOption = eventTypeSelect.find('option').filter(function() {
                            const optionText = $(this).text().trim().toLowerCase();
                            const dataText = normalizedEventType.toLowerCase();
                            return optionText.includes(dataText) || dataText.includes(optionText);
                        });
                        
                        if (partialMatchingOption.length > 0) {
                            eventTypeSelect.val(partialMatchingOption.val());
                        } else {
                            // Try fuzzy matching for common synonyms/misspellings
                            const fuzzyMatch = findFuzzyMatch(normalizedEventType, eventTypeSelect);
                            if (fuzzyMatch) {
                                eventTypeSelect.val(fuzzyMatch);
                            } else {
                                console.error('No matching option found even with case-insensitive, partial, and fuzzy matching');
                                console.warn('Consider adding this event type to the dropdown options or updating the database value');
                                console.warn('Available options:', eventTypeSelect.find('option').map(function() { return this.value; }).get());
                                
                                // Provide specific suggestions for common mismatches
                                if (normalizedEventType.toLowerCase() === 'vaccination') {
                                    console.warn('SUGGESTION: "Vaccination" should probably be "Immunization"');
                                    console.warn('You can either:');
                                    console.warn('1. Update the database to change "Vaccination" to "Immunization"');
                                    console.warn('2. Add "Vaccination" as a new option in the dropdown');
                                    
                                    // TEMPORARY: Auto-map Vaccination to Immunization for now
                                    eventTypeSelect.val('Immunization');
                                }
                            }
                        }
                    }
                }
            }
            
            $('#edit_event_name').val(data.event_name_created);
            $('#edit_event_time').val(data.event_time);
            $('#edit_event_place').val(data.event_place);
            $('#edit_event_date').val(data.event_date);
            $('#edit_min_age').val(data.min_age);
            $('#edit_max_age').val(data.max_age);

            // Show the modal using custom approach
            showModal(modalEl);
            
            // Ensure form values are set after modal is fully shown
            setTimeout(() => {
                
                // Re-set event type to ensure it's selected
                const eventTypeSelect = $('#edit_event_type');
                if (normalizedEventType) {
                    // Try to set the value with case-insensitive matching
                    let valueSet = false;
                    
                    // First try exact value match
                    eventTypeSelect.val(normalizedEventType);
                    if (eventTypeSelect.val() === normalizedEventType) {
                        valueSet = true;
                    } else {
                        // Try case-insensitive text matching
                        const matchingOption = eventTypeSelect.find('option').filter(function() {
                            const optionText = $(this).text().trim().toLowerCase();
                            const dataText = normalizedEventType.toLowerCase();
                            return optionText === dataText;
                        });
                        
                        if (matchingOption.length > 0) {
                            eventTypeSelect.val(matchingOption.val());
                            valueSet = true;
                         
            } else {
                            // Try fuzzy matching as a last resort
                            const fuzzyMatch = findFuzzyMatch(normalizedEventType, eventTypeSelect);
                            if (fuzzyMatch) {
                                eventTypeSelect.val(fuzzyMatch);
                                valueSet = true;
                             
                            }
                        }
                    }
                    
                }
                
                // Re-set other values to ensure they're properly set
                $('#edit_event_name').val(data.event_name_created);
                $('#edit_event_time').val(data.event_time);
                $('#edit_event_place').val(data.event_place);
                $('#edit_event_date').val(data.event_date);
                $('#edit_min_age').val(data.min_age);
                $('#edit_max_age').val(data.max_age);
            }, 100);
        } else {
            console.error('No data found for ID:', eventId);
        }
    };

    // Add this new function for viewing event details
    window.viewEventDetails = function(eventId) {
        // Ensure modal element is available
        const modalEl = ensureModalAvailable('view');
        if (!modalEl) {
            console.error('viewModalEl still not found after retry');
            return;
        }
        
        const data = window.eventTable.rows().data().toArray()
            .find(row => row.event_prikey == eventId);
            
        if (data) {
            // Populate modal with event details
            $('#view_created_by').text(data.created_by);
            $('#view_edited_by').text(data.edited_by || 'N/A');
            $('#view_created_at').text(data.created_at);
            $('#view_updated_at').text(data.updated_at || 'N/A');
            
            // Show the modal using custom approach
            showModal(modalEl);
        }
    };

    // Handle entries per page change
    $('#eventsPerPage').on('change', function() {
        window.eventTable.page.len($(this).val()).draw();
    });
    
    // Handle add event button click
    $('#addEventBtn').on('click', function(e) {
        e.preventDefault();
        
        // Ensure modal element is available
        const modalEl = ensureModalAvailable('add');
        if (!modalEl) {
            console.error('addModalEl still not found after retry');
            return;
        }
        
        // Reset form
        $('#addEventForm')[0].reset();
        // Show modal using custom approach
        showModal(modalEl);
    });

    // Handle search
    $('#eventSearch').on('keyup', function() {
        window.eventTable.search(this.value).draw();
    });

    // Handle close buttons for all modals
    [addModalEl, editModalEl, viewModalEl].forEach(modalEl => {
        if (modalEl) {
            // Handle all close buttons (including btn-close and Close buttons)
            modalEl.querySelectorAll('.btn-close, .btn-secondary').forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    hideModal(modalEl);
                });
            });
        }
    });

    // Handle ESC key for all modals
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            if (addModalEl && addModalEl.classList.contains('show')) {
                hideModal(addModalEl);
            }
            if (editModalEl && editModalEl.classList.contains('show')) {
                hideModal(editModalEl);
            }
            if (viewModalEl && viewModalEl.classList.contains('show')) {
                hideModal(viewModalEl);
            }
        }
    });

    // Handle backdrop click
    if (backdropEl) {
        backdropEl.addEventListener('click', function(e) {
            if (e.target === backdropEl) {
                if (addModalEl && addModalEl.classList.contains('show')) {
                    hideModal(addModalEl);
                }
                if (editModalEl && editModalEl.classList.contains('show')) {
                    hideModal(editModalEl);
                }
                if (viewModalEl && viewModalEl.classList.contains('show')) {
                    hideModal(viewModalEl);
                }
            }
        });
    }

    // Initial cleanup
    [addModalEl, editModalEl, viewModalEl].forEach(modalEl => {
        if (modalEl) {
            hideModal(modalEl);
        }
    });
    
    // Final verification that all modals are available
    setTimeout(() => {
        if (!addModalEl || !editModalEl || !viewModalEl) {
            console.warn('Some modal elements are still missing after initialization');
            getModalElements(); // Try one more time
        }
    }, 500);
});
