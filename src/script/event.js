function refreshEventContent() {
    // Refresh the events table
    fetch('../controllers/event_controller.php?action=getAll')
        .then(response => response.json())
        .then(events => {
            const tbody = document.querySelector('#event table tbody');
            tbody.innerHTML = '';
            
            events.forEach(event => {
                const row = document.createElement('tr');
                const eventTime = event.event_time ? new Date(`1970-01-01T${event.event_time}`) : new Date();
                const eventDate = event.event_date ? new Date(event.event_date) : new Date();
                
                row.innerHTML = `
                    <td>${event.event_type || ''}</td>
                    <td>${event.event_name_created || ''}</td>
                    <td>${eventTime.toLocaleTimeString('en-US', {hour: 'numeric', minute: '2-digit', hour12: true})}</td>
                    <td>${event.event_place || ''}</td>
                    <td>${eventDate.toLocaleDateString('en-US', {month: 'short', day: 'numeric', year: 'numeric'})}</td>
                    <td>${event.created_by || ''}</td>
                    <td>${event.event_creator_email || ''}</td>
                    <td>${event.event_editor_email || ''}</td>
                    <td>
                        <button class='btn btn-sm btn-outline-primary me-1' onclick='editEvent(${event.event_id}, "${event.event_type || ''}", "${event.event_name_created || ''}", "${event.event_time ? event.event_time.substring(0, 5) : ''}", "${event.event_date || ''}")'>
                            <i class='far fa-edit'></i>
                        </button>
                        <button class='btn btn-sm btn-outline-danger' onclick='confirmDeleteEvent(${event.event_id})'>
                            <i class='fa fa-trash'></i>
                        </button>
                    </td>
                `;
                tbody.appendChild(row);
            });
        })
        .catch(error => {
            console.error('Error refreshing events:', error);
        });
}

function reloadParentTab() {
    // Create form data to maintain the active tab
    const formData = new FormData();
    formData.append('active_tab', 'event');

    // Use fetch to post the data
    fetch('../view/admin.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        // Reload the entire page after successful post
        window.parent.location.reload();
    })
    .catch(error => {
        console.error('Error reloading:', error);
    });
}

function addEvent(formElement) {
    event.preventDefault();
    console.log('Adding event...');

    const formData = new FormData(formElement);
    fetch('../backend/event_handler.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Remove aria-hidden before closing modal
            const modal = document.getElementById('addEventModal');
            modal.removeAttribute('aria-hidden');
            
            // Hide modal properly
            const bsModal = bootstrap.Modal.getInstance(modal);
            if (bsModal) {
                bsModal.hide();
            }
            
            // Remove modal backdrop
            const backdrop = document.querySelector('.modal-backdrop');
            if (backdrop) {
                backdrop.remove();
            }
            
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: 'Event added successfully',
                showConfirmButton: false,
                timer: 1500
            }).then(() => {
                // Reset the form
                formElement.reset();
                // Reload the parent page
                reloadParentTab();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: data.message || 'Failed to add event'
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: 'An unexpected error occurred'
        });
    });
}

function updateEvent(formElement) {
    event.preventDefault();
    console.log('Updating event...');

    const formData = new FormData(formElement);
    fetch('../backend/event_handler.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Remove aria-hidden before closing modal
            const modal = document.getElementById('editEventModal');
            modal.removeAttribute('aria-hidden');
            
            // Hide modal properly
            const bsModal = bootstrap.Modal.getInstance(modal);
            if (bsModal) {
                bsModal.hide();
            }
            
            // Remove modal backdrop
            const backdrop = document.querySelector('.modal-backdrop');
            if (backdrop) {
                backdrop.remove();
            }
            
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: 'Event updated successfully',
                showConfirmButton: false,
                timer: 1500
            }).then(() => {
                // Reload the parent page
                reloadParentTab();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: data.message || 'Failed to update event'
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: 'An unexpected error occurred'
        });
    });
}

function deleteEvent(eventId) {
    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('event_id', eventId);

    fetch('../backend/event_handler.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Deleted!',
                text: 'Event has been deleted.',
                showConfirmButton: false,
                timer: 1500
            }).then(() => {
                // Reload the parent page
                reloadParentTab();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: data.message || 'Failed to delete event'
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: 'An unexpected error occurred'
        });
    });
}

function editEvent(eventId, eventType, eventName, eventTime, eventDate) {
    // Populate the edit modal with event data
    document.getElementById('editEventId').value = eventId;
    document.getElementById('editEventType').value = eventType;
    document.getElementById('editEventName').value = eventName;
    document.getElementById('editEventTime').value = eventTime;
    document.getElementById('editEventDate').value = eventDate;
    
    // Show the edit modal
    $('#editEventModal').modal('show');
}

function confirmDeleteEvent(eventId) {
    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            deleteEvent(eventId);
        }
    });
}

// Initialize tooltips and handle modal events
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.forEach(function(tooltipTriggerEl) {
        new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Handle modal events
    const addEventModal = document.getElementById('addEventModal');
    const editEventModal = document.getElementById('editEventModal');
    
    if (addEventModal) {
        addEventModal.addEventListener('show.bs.modal', function() {
            this.removeAttribute('aria-hidden');
        });
        
        addEventModal.addEventListener('hide.bs.modal', function() {
            const focusedElement = document.activeElement;
            if (focusedElement) {
                focusedElement.blur();
            }
            // Remove modal backdrop
            const backdrop = document.querySelector('.modal-backdrop');
            if (backdrop) {
                backdrop.remove();
            }
        });
    }
    
    if (editEventModal) {
        editEventModal.addEventListener('show.bs.modal', function() {
            this.removeAttribute('aria-hidden');
        });
        
        editEventModal.addEventListener('hide.bs.modal', function() {
            const focusedElement = document.activeElement;
            if (focusedElement) {
                focusedElement.blur();
            }
            // Remove modal backdrop
            const backdrop = document.querySelector('.modal-backdrop');
            if (backdrop) {
                backdrop.remove();
            }
        });
    }
});