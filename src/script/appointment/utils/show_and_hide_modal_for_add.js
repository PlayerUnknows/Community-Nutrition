// Utility functions for showing and hiding the add appointment modal
// These functions expect addModalEl and backdropEl to be defined in the calling scope

function showModal() {
    // Get modal elements from global scope or parameters
    const addModalEl = document.getElementById("addAppointmentModal");
    const backdropEl = document.querySelector(".modal-custom-backdrop");
    
    if (!addModalEl) {
        console.error("addModalEl not found");
        return;
    }

    // Show backdrop
    if (backdropEl) {
        backdropEl.classList.add("show");
    }

    // Show modal
    addModalEl.classList.add("show");
    addModalEl.style.display = "block";
    addModalEl.removeAttribute("aria-hidden");
    addModalEl.setAttribute("aria-modal", "true");
    addModalEl.setAttribute("role", "dialog");

    // Add body class
    document.body.classList.add("modal-open");
    document.body.style.overflow = "hidden";

    // Reset form (call the function from the main scope)
    if (typeof resetForm === 'function') {
        resetForm();
    }

    // Focus first input
    setTimeout(() => {
      const firstInput = addModalEl.querySelector("input, select, textarea");
      if (firstInput) firstInput.focus();
    }, 100);
  }

  // Function to hide modal manually
  function hideModal() {
    // Get modal elements from global scope or parameters
    const addModalEl = document.getElementById("addAppointmentModal");
    const backdropEl = document.querySelector(".modal-custom-backdrop");
    
    if (!addModalEl) {
        console.error("addModalEl not found");
        return;
    }


    // Hide backdrop
    if (backdropEl) {
      backdropEl.classList.remove("show");
    }

    // Hide modal
    addModalEl.classList.remove("show");
    addModalEl.style.display = "none";
    addModalEl.removeAttribute("aria-modal");
    addModalEl.removeAttribute("role");
    // DO NOT set aria-hidden true as it causes accessibility issues

    // Reset body
    document.body.classList.remove("modal-open");
    document.body.style.overflow = "";
    document.body.style.paddingRight = "";

    // Remove any existing bootstrap backdrops (cleanup)
    document.querySelectorAll(".modal-backdrop").forEach((el) => el.remove());
  }

// Make functions globally accessible with higher priority
// Override any existing showModal/hideModal functions
window.showModal = showModal;
window.hideModal = hideModal;

// Also create appointment-specific functions to avoid conflicts
window.showAppointmentModal = showModal;
window.hideAppointmentModal = hideModal;
