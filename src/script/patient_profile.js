$(document).ready(function() {
    const patientTable = new DataTable('#patientTable', {
        pageLength: 5,
        lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, "All"]],
        dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
             '<"row"<"col-sm-12"tr>>' +
             '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
        scrollY: '50vh',
        scrollCollapse: true
    });

    // Handle view button click
    $('#patientTable').on('click', '.view-patient', async function() {
        const patientDetails = $(this).data('details');
        
        // Fetch family information
        let familyInfoHtml = '<div class="mt-3"><strong>Family Information:</strong></div>';
        try {
            const response = await fetch(`../controllers/family_controller.php?action=getFamilyInfo&patient_fam_id=${patientDetails.patient_fam_id}`);
            const result = await response.json();
            
            if (result.success && result.data) {
                const familyInfo = result.data;
                familyInfoHtml += `
                    <div class="table-responsive mt-2">
                        <table class="table table-sm table-bordered">
                            <tr>
                                <th colspan="2">Parents' Information</th>
                            </tr>
                            <tr>
                                <td><strong>Father:</strong></td>
                                <td>${familyInfo.father_fname} ${familyInfo.father_mi} ${familyInfo.father_lname} ${familyInfo.father_suffix || ''}</td>
                            </tr>
                            <tr>
                                <td><strong>Father's Occupation:</strong></td>
                                <td>${familyInfo.father_occupation || 'N/A'}</td>
                            </tr>
                            <tr>
                                <td><strong>Mother:</strong></td>
                                <td>${familyInfo.mother_fname} ${familyInfo.mother_mi} ${familyInfo.mother_lname} ${familyInfo.mother_suffix || ''}</td>
                            </tr>
                            <tr>
                                <td><strong>Mother's Occupation:</strong></td>
                                <td>${familyInfo.mother_occupation || 'N/A'}</td>
                            </tr>
                            <tr>
                                <td><strong>Contact:</strong></td>
                                <td>${familyInfo.contact_no || 'N/A'}</td>
                            </tr>
                            <tr>
                                <td><strong>Address:</strong></td>
                                <td>${[
                                    familyInfo.house_no,
                                    familyInfo.street_address,
                                    familyInfo.subdivision_sitio,
                                    familyInfo.baranggay
                                ].filter(Boolean).join(', ') || 'N/A'}</td>
                            </tr>
                            <tr>
                                <td><strong>Food Budget:</strong></td>
                                <td>₱${parseFloat(familyInfo.family_food_budget || 0).toLocaleString()}</td>
                            </tr>
                        </table>
                    </div>`;
            } else {
                familyInfoHtml += '<div class="text-muted">No family information recorded</div>';
            }
        } catch (error) {
            console.error('Error fetching family information:', error);
            familyInfoHtml += '<div class="text-danger">Error loading family information</div>';
        }

        Swal.fire({
            title: '<strong>Patient Details</strong>',
            html: '<div><strong>Middle Initial:</strong> ' + patientDetails.patient_mi + '</div>' +
                  '<div><strong>Suffix:</strong> ' + patientDetails.patient_suffix + '</div>' +
                  '<div><strong>Sex:</strong> ' + patientDetails.sex + '</div>' +
                  '<div><strong>Date of Birth:</strong> ' + patientDetails.date_of_birth + '</div>' +
                  '<div><strong>Food Restrictions:</strong> ' + patientDetails.patient_food_restrictions + '</div>' +
                  '<div><strong>Medical History:</strong> ' + patientDetails.patient_medical_history + '</div>' +
                  '<div><strong>Dietary Record:</strong> ' + patientDetails.dietary_consumption_record + '</div>' +
                  familyInfoHtml,
            showCloseButton: true,
            focusConfirm: false,
            confirmButtonText: 'Close',
            width: '400px',
            customClass: {
                container: 'small-modal',
                popup: 'small-modal',
                header: 'small-modal-header',
                title: 'small-modal-title',
                content: 'small-modal-content'
            }
        });
    });

    // Add/Edit Family Information button handler
    $('#patientTable').on('click', '.edit-family-info', async function() {
        const patientFamId = $(this).data('patient-fam-id');
        let familyInfo = null;
        
        try {
            const response = await fetch(`../controllers/family_controller.php?action=getFamilyInfo&patient_fam_id=${patientFamId}`);
            const result = await response.json();
            if (result.success) {
                familyInfo = result.data;
            }
        } catch (error) {
            console.error('Error fetching family information:', error);
        }
        
        const { value: formValues } = await Swal.fire({
            title: familyInfo ? 'Edit Family Info' : 'Add Family Info',
            html: `
                <form id="familyInfoForm" class="text-start">
                    <div class="mb-2">
                        <h6 class="mb-2">Father's Information</h6>
                        <div class="row g-1">
                            <div class="col-6">
                                <input type="text" class="form-control form-control-sm" id="father_fname" placeholder="First Name" value="${familyInfo?.father_fname || ''}" required>
                            </div>
                            <div class="col-2">
                                <input type="text" class="form-control form-control-sm" id="father_mi" placeholder="MI" value="${familyInfo?.father_mi || ''}">
                            </div>
                            <div class="col-4">
                                <input type="text" class="form-control form-control-sm" id="father_lname" placeholder="Last Name" value="${familyInfo?.father_lname || ''}" required>
                            </div>
                        </div>
                        <div class="row g-1 mt-1">
                            <div class="col-4">
                                <input type="text" class="form-control form-control-sm" id="father_suffix" placeholder="Suffix" value="${familyInfo?.father_suffix || ''}">
                            </div>
                            <div class="col-8">
                                <input type="text" class="form-control form-control-sm" id="father_occupation" placeholder="Occupation" value="${familyInfo?.father_occupation || ''}">
                            </div>
                        </div>
                    </div>
                    <div class="mb-2">
                        <h6 class="mb-2">Mother's Information</h6>
                        <div class="row g-1">
                            <div class="col-6">
                                <input type="text" class="form-control form-control-sm" id="mother_fname" placeholder="First Name" value="${familyInfo?.mother_fname || ''}" required>
                            </div>
                            <div class="col-2">
                                <input type="text" class="form-control form-control-sm" id="mother_mi" placeholder="MI" value="${familyInfo?.mother_mi || ''}">
                            </div>
                            <div class="col-4">
                                <input type="text" class="form-control form-control-sm" id="mother_lname" placeholder="Last Name" value="${familyInfo?.mother_lname || ''}" required>
                            </div>
                        </div>
                        <div class="row g-1 mt-1">
                            <div class="col-4">
                                <input type="text" class="form-control form-control-sm" id="mother_suffix" placeholder="Suffix" value="${familyInfo?.mother_suffix || ''}">
                            </div>
                            <div class="col-8">
                                <input type="text" class="form-control form-control-sm" id="mother_occupation" placeholder="Occupation" value="${familyInfo?.mother_occupation || ''}">
                            </div>
                        </div>
                    </div>
                    <div class="mb-2">
                        <h6 class="mb-2">Contact Information</h6>
                        <input type="text" class="form-control form-control-sm mb-1" id="contact_no" placeholder="Contact Number" value="${familyInfo?.contact_no || ''}">
                        <input type="text" class="form-control form-control-sm mb-1" id="house_no" placeholder="House Number" value="${familyInfo?.house_no || ''}">
                        <input type="text" class="form-control form-control-sm mb-1" id="street_address" placeholder="Street Address" value="${familyInfo?.street_address || ''}">
                        <input type="text" class="form-control form-control-sm mb-1" id="subdivision_sitio" placeholder="Subdivision/Sitio" value="${familyInfo?.subdivision_sitio || ''}">
                        <input type="text" class="form-control form-control-sm mb-1" id="baranggay" placeholder="Barangay" value="${familyInfo?.baranggay || ''}">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text">₱</span>
                            <input type="number" class="form-control form-control-sm" id="family_food_budget" placeholder="Food Budget" value="${familyInfo?.family_food_budget || '0'}" step="0.01">
                        </div>
                    </div>
                </form>`,
            focusConfirm: false,
            showCancelButton: true,
            confirmButtonText: familyInfo ? 'Update' : 'Add',
            cancelButtonText: 'Cancel',
            width: '400px',
            customClass: {
                container: 'small-modal',
                popup: 'small-modal',
                header: 'small-modal-header',
                title: 'small-modal-title',
                content: 'small-modal-content'
            },
            preConfirm: () => {
                const data = {
                    patient_fam_id: patientFamId,
                    father_fname: document.getElementById('father_fname').value,
                    father_mi: document.getElementById('father_mi').value,
                    father_lname: document.getElementById('father_lname').value,
                    father_suffix: document.getElementById('father_suffix').value,
                    father_occupation: document.getElementById('father_occupation').value,
                    mother_fname: document.getElementById('mother_fname').value,
                    mother_mi: document.getElementById('mother_mi').value,
                    mother_lname: document.getElementById('mother_lname').value,
                    mother_suffix: document.getElementById('mother_suffix').value,
                    mother_occupation: document.getElementById('mother_occupation').value,
                    contact_no: document.getElementById('contact_no').value,
                    house_no: document.getElementById('house_no').value,
                    street_address: document.getElementById('street_address').value,
                    subdivision_sitio: document.getElementById('subdivision_sitio').value,
                    baranggay: document.getElementById('baranggay').value,
                    family_food_budget: document.getElementById('family_food_budget').value
                };
                
                if (familyInfo) {
                    data.family_prikey = familyInfo.family_prikey;
                }
                
                return data;
            }
        });

        if (formValues) {
            try {
                const formData = new FormData();
                Object.entries(formValues).forEach(([key, value]) => {
                    formData.append(key, value);
                });
                formData.append('action', familyInfo ? 'updateFamilyInfo' : 'addFamilyInfo');

                const response = await fetch('../controllers/family_controller.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                if (result.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: result.message,
                        width: '400px',
                        customClass: {
                            container: 'small-modal',
                            popup: 'small-modal',
                            header: 'small-modal-header',
                            title: 'small-modal-title',
                            content: 'small-modal-content'
                        }
                    });
                } else {
                    throw new Error(result.message);
                }
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error.message || 'Failed to save family information',
                    width: '400px',
                    customClass: {
                        container: 'small-modal',
                        popup: 'small-modal',
                        header: 'small-modal-header',
                        title: 'small-modal-title',
                        content: 'small-modal-content'
                    }
                });
            }
        }
    });
});
