document.addEventListener('DOMContentLoaded', function() {
    const professionalsTableBody = $('#professionals-table tbody');
    const loader = $('#loader');
    const editProfessionalModal = $('#edit-professional-modal');
    const editProfessionalForm = $('#edit-professional-form');

    // Function to show toasts
    function showToast(message, type = 'green') {
        M.toast({html: message, classes: type});
    }

   // Initial dashboard stats fetch on load
        // Keep the if(token && localStorage.getItem('user_role') === 'admin') if you want to send token, but remove the else part
        if (token && localStorage.getItem('user_role') === 'admin') {
            fetchDashboardStats();
        }
// No else block here for redirect in previous version, but if there was, remove it.

    // Function to fetch and display professionals
    async function fetchProfessionals() {
        loader.show();
        try {
            const response = await fetch('api/routes/admin/professionals/get_professionals.php', {
                method: 'GET',
                headers: {
                    'Authorization': getAuthHeader()
                }
            });
            const result = await response.json();

            if (result.status === 'success') {
                displayProfessionals(result.data);
            } else {
                showToast(result.message || 'Failed to fetch professionals.', 'red');
                if (response.status === 401 || response.status === 403) {
                    setTimeout(() => window.location.href = 'index.html', 1500);
                }
            }
        } catch (error) {
            console.error('Error fetching professionals:', error);
            showToast('An error occurred while fetching professionals.', 'red');
        } finally {
            loader.hide();
        }
    }

    // Function to display professionals in the table
    function displayProfessionals(professionals) {
        professionalsTableBody.empty(); // Clear existing rows
        if (professionals.length === 0) {
            professionalsTableBody.append('<tr><td colspan="7" class="center-align">No professionals found.</td></tr>');
            return;
        }

        professionals.forEach(pro => {
            const statusClass = pro.status === 'approved' ? 'approved' : (pro.status === 'pending' ? 'pending' : 'rejected');
            const row = `
                <tr>
                    <td>${pro.id}</td>
                    <td>${pro.user_name || 'N/A'}</td>
                    <td>${pro.user_email || 'N/A'}</td>
                    <td>${pro.expertise}</td>
                    <td><span class="status-badge ${statusClass}">${pro.status.toUpperCase()}</span></td>
                    <td>${pro.created_at}</td>
                    <td class="action-btns">
                        <button class="btn btn-small blue lighten-1 edit-btn" data-id="${pro.id}"
                            data-expertise="${pro.expertise}" data-availability="${pro.availability}"
                            data-status="${pro.status}" data-user-name="${pro.user_name}">
                            <i class="material-icons">edit</i> Edit
                        </button>
                        ${pro.status !== 'approved' ? `<button class="btn btn-small green approve-btn" data-id="${pro.id}">
                            <i class="material-icons">check</i> Approve
                        </button>` : ''}
                        ${pro.status !== 'rejected' ? `<button class="btn btn-small orange darken-3 reject-btn" data-id="${pro.id}">
                            <i class="material-icons">block</i> Reject
                        </button>` : ''}
                        <button class="btn btn-small red darken-1 delete-btn" data-id="${pro.id}">
                            <i class="material-icons">delete</i> Delete
                        </button>
                    </td>
                </tr>
            `;
            professionalsTableBody.append(row);
        });

        // Attach event listeners to new buttons
        $('.edit-btn').on('click', openEditModal);
        $('.approve-btn').on('click', confirmApproveProfessional);
        $('.reject-btn').on('click', confirmRejectProfessional);
        $('.delete-btn').on('click', confirmDeleteProfessional);
    }

    // Open edit modal and populate with professional data
    function openEditModal() {
        const proId = $(this).data('id');
        const userName = $(this).data('user-name');
        const expertise = $(this).data('expertise');
        const availability = $(this).data('availability');
        const status = $(this).data('status');

        $('#edit-professional-id').val(proId);
        $('#edit-professional-user-name').val(userName);
        $('#edit-professional-expertise').val(expertise);
        $('#edit-professional-availability').val(availability);
        $('#edit-professional-status').val(status);

        M.updateTextFields(); // Re-initialize Materialize input labels
        $('select').formSelect(); // Re-initialize Materialize selects

        editProfessionalModal.modal('open');
    }

    // Handle edit form submission
    editProfessionalForm.on('submit', async function(e) {
        e.preventDefault();
        loader.show();
        const proId = $('#edit-professional-id').val();
        const updatedData = {
            id: proId,
            expertise: $('#edit-professional-expertise').val(),
            availability: $('#edit-professional-availability').val(),
            status: $('#edit-professional-status').val(),
        };

        try {
            const response = await fetch('api/routes/admin/professionals/update_professional.php', {
                method: 'POST', // Using POST as per backend
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': getAuthHeader()
                },
                body: JSON.stringify(updatedData)
            });
            const result = await response.json();

            if (result.status === 'success') {
                showToast(result.message || 'Professional updated successfully!', 'green');
                editProfessionalModal.modal('close');
                fetchProfessionals(); // Refresh the list
            } else {
                showToast(result.message || 'Failed to update professional.', 'red');
            }
        } catch (error) {
            console.error('Error updating professional:', error);
            showToast('An error occurred while updating professional.', 'red');
        } finally {
            loader.hide();
        }
    });

    // Function to handle status updates (Approve/Reject)
    async function updateProfessionalStatus(proId, newStatus) {
        loader.show();
        try {
            const response = await fetch(`api/routes/admin/professionals/${newStatus}_professional.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': getAuthHeader()
                },
                body: JSON.stringify({ id: proId })
            });
            const result = await response.json();

            if (result.status === 'success') {
                showToast(result.message || `Professional ${newStatus}ed successfully!`, 'green');
                fetchProfessionals(); // Refresh the list
            } else {
                showToast(result.message || `Failed to ${newStatus} professional.`, 'red');
            }
        } catch (error) {
            console.error(`Error ${newStatus}ing professional:`, error);
            showToast(`An error occurred while ${newStatus}ing professional.`, 'red');
        } finally {
            loader.hide();
        }
    }

    // Confirm Approve Professional
    function confirmApproveProfessional() {
        const proId = $(this).data('id');
        M.Modal.init(document.createElement('div'), {
            onOpenEnd: function(modal, trigger) {
                modal.innerHTML = `
                    <div class="modal-content">
                        <h4>Confirm Approval</h4>
                        <p>Are you sure you want to approve this professional (ID: ${proId})?</p>
                    </div>
                    <div class="modal-footer">
                        <button class="modal-close waves-effect waves-green btn-flat">Cancel</button>
                        <button class="waves-effect waves-green btn-flat green-text text-darken-4" id="confirm-approve-btn" data-id="${proId}">Approve</button>
                    </div>
                `;
                document.body.appendChild(modal);
                modal.M_Modal.open();

                $('#confirm-approve-btn').on('click', function() {
                    updateProfessionalStatus(proId, 'approve');
                    modal.M_Modal.close();
                    $(modal).remove();
                });
            }
        }).open();
    }

    // Confirm Reject Professional
    function confirmRejectProfessional() {
        const proId = $(this).data('id');
        M.Modal.init(document.createElement('div'), {
            onOpenEnd: function(modal, trigger) {
                modal.innerHTML = `
                    <div class="modal-content">
                        <h4>Confirm Rejection</h4>
                        <p>Are you sure you want to reject this professional (ID: ${proId})?</p>
                    </div>
                    <div class="modal-footer">
                        <button class="modal-close waves-effect waves-green btn-flat">Cancel</button>
                        <button class="waves-effect waves-red btn-flat red-text text-darken-4" id="confirm-reject-btn" data-id="${proId}">Reject</button>
                    </div>
                `;
                document.body.appendChild(modal);
                modal.M_Modal.open();

                $('#confirm-reject-btn').on('click', function() {
                    updateProfessionalStatus(proId, 'reject');
                    modal.M_Modal.close();
                    $(modal).remove();
                });
            }
        }).open();
    }

    // Confirm and delete professional
    function confirmDeleteProfessional() {
        const proId = $(this).data('id');
        M.Modal.init(document.createElement('div'), {
            onOpenEnd: function(modal, trigger) {
                modal.innerHTML = `
                    <div class="modal-content">
                        <h4>Confirm Deletion</h4>
                        <p>Are you sure you want to delete this professional (ID: ${proId})? This action is irreversible.</p>
                    </div>
                    <div class="modal-footer">
                        <button class="modal-close waves-effect waves-green btn-flat">Cancel</button>
                        <button class="waves-effect waves-red btn-flat red-text text-darken-4" id="confirm-delete-btn" data-id="${proId}">Delete</button>
                    </div>
                `;
                document.body.appendChild(modal);
                modal.M_Modal.open();

                $('#confirm-delete-btn').on('click', async function() {
                    loader.show();
                    try {
                        const response = await fetch('api/routes/admin/professionals/delete_professional.php', {
                            method: 'POST', // Using POST as per backend
                            headers: {
                                'Content-Type': 'application/json',
                                'Authorization': getAuthHeader()
                            },
                            body: JSON.stringify({ id: proId })
                        });
                        const result = await response.json();

                        if (result.status === 'success') {
                            showToast(result.message || 'Professional deleted successfully!', 'green');
                            fetchProfessionals(); // Refresh the list
                        } else {
                            showToast(result.message || 'Failed to delete professional.', 'red');
                        }
                    } catch (error) {
                        console.error('Error deleting professional:', error);
                        showToast('An error occurred while deleting professional.', 'red');
                    } finally {
                        loader.hide();
                        modal.M_Modal.close();
                        $(modal).remove();
                    }
                });
            }
        }).open();
    }

    // Initial fetch of professionals when the page loads
    if (getAuthHeader()) { // Only fetch if token exists (simple client-side check)
        fetchProfessionals();
    } else {
        showToast('Authentication required to access professional management.', 'red');
        setTimeout(() => window.location.href = 'index.html', 1500); // Redirect to login
    }
});