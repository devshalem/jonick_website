document.addEventListener('DOMContentLoaded', function () {
    const jobsTableBody = $('#jobs-table tbody');
    const loader = $('#loader');
    const editJobModal = $('#edit-job-modal');
    const editJobForm = $('#edit-job-form');
    const professionalSelect = $('#edit-job-professional-id');

    let allProfessionals = [];

    function showToast(message, type = 'green') {
        M.toast({ html: message, classes: type });
    }

    // Initial dashboard stats fetch on load
// Keep the if(token && localStorage.getItem('user_role') === 'admin') if you want to send token, but remove the else part
if (token && localStorage.getItem('user_role') === 'admin') {
    fetchDashboardStats();
}
// No else block here for redirect in previous version, but if there was, remove it.

    async function fetchProfessionalsForDropdown() {
        try {
            const response = await fetch('api/routes/admin/professionals/get_professionals.php', {
                method: 'GET',
                headers: {
                    'Authorization': getAuthHeader()
                }
            });
            const result = await response.json();

            if (result.status === 'success') {
                allProfessionals = result.data.filter(pro => pro.status === 'approved');
                populateProfessionalDropdown(allProfessionals);
            } else {
                console.error('Failed to fetch professionals:', result.message);
                showToast('Failed to load professionals.', 'red');
            }
        } catch (error) {
            console.error('Error:', error);
            showToast('Error loading professionals.', 'red');
        }
    }

    function populateProfessionalDropdown(professionals) {
        professionalSelect.empty();
        professionalSelect.append('<option value="" disabled selected>Assign Professional</option>');
        professionals.forEach(pro => {
            professionalSelect.append(`<option value="${pro.id}">${pro.user_name} (${pro.expertise})</option>`);
        });
        $('select').formSelect();
    }

    async function fetchJobs() {
        loader.show();
        try {
            const response = await fetch('api/routes/admin/jobs/get_jobs.php', {
                method: 'GET',
                headers: {
                    'Authorization': getAuthHeader()
                }
            });
            const result = await response.json();

            if (result.status === 'success') {
                displayJobs(result.data);
            } else {
                showToast(result.message || 'Failed to fetch jobs.', 'red');
                if (response.status === 401 || response.status === 403) {
                    setTimeout(() => window.location.href = 'index.html', 1500);
                }
            }
        } catch (error) {
            console.error('Error:', error);
            showToast('Error fetching jobs.', 'red');
        } finally {
            loader.hide();
        }
    }

    function displayJobs(jobs) {
        jobsTableBody.empty();
        if (jobs.length === 0) {
            jobsTableBody.append('<tr><td colspan="9" class="center-align">No jobs found.</td></tr>');
            return;
        }

        jobs.forEach(job => {
            const statusClass = job.status.replace(/_/g, '-');
            const professionalName = job.professional_id
                ? (allProfessionals.find(p => p.id == job.professional_id)?.user_name || 'Assigned')
                : 'Not Assigned';
            const row = `
                <tr>
                    <td>${job.id}</td>
                    <td>${job.user_name || 'N/A'} (${job.user_email || 'N/A'})</td>
                    <td>${job.service_name || 'N/A'}</td>
                    <td>${professionalName}</td>
                    <td>${job.description.length > 50 ? job.description.substring(0, 50) + '...' : job.description}</td>
                    <td>$${parseFloat(job.price).toFixed(2)}</td>
                    <td><span class="status-badge ${statusClass}">${job.status.toUpperCase().replace(/_/g, ' ')}</span></td>
                    <td>${job.created_at}</td>
                    <td class="action-btns">
                        <button class="btn btn-small blue lighten-1 edit-btn"
                            data-id="${job.id}"
                            data-user-id="${job.user_id}"
                            data-service-id="${job.service_id}"
                            data-professional-id="${job.professional_id}"
                            data-status="${job.status}"
                            data-description="${job.description}"
                            data-price="${job.price}"
                            data-user-name="${job.user_name}"
                            data-service-name="${job.service_name}">
                            <i class="material-icons">edit</i> Edit
                        </button>
                        <button class="btn btn-small red darken-1 delete-btn" data-id="${job.id}">
                            <i class="material-icons">delete</i> Delete
                        </button>
                    </td>
                </tr>`;
            jobsTableBody.append(row);
        });

        $('.edit-btn').on('click', openEditModal);
        $('.delete-btn').on('click', confirmDeleteJob);
    }

    function openEditModal() {
        const jobId = $(this).data('id');
        $('#edit-job-id').val(jobId);
        $('#edit-job-user-id').val($(this).data('user-id'));
        $('#edit-job-service-id').val($(this).data('service-id'));
        $('#edit-job-user-name').val($(this).data('user-name'));
        $('#edit-job-service-name').val($(this).data('service-name'));
        $('#edit-job-description').val($(this).data('description'));
        $('#edit-job-price').val(parseFloat($(this).data('price')).toFixed(2));
        $('#edit-job-status').val($(this).data('status'));
        professionalSelect.val($(this).data('professional-id'));

        M.updateTextFields();
        $('select').formSelect();
        editJobModal.modal('open');
    }

    editJobForm.on('submit', async function (e) {
        e.preventDefault();
        loader.show();

        const updatedData = {
            id: $('#edit-job-id').val(),
            user_id: $('#edit-job-user-id').val(),
            service_id: $('#edit-job-service-id').val(),
            professional_id: professionalSelect.val() === '' ? null : professionalSelect.val(),
            description: $('#edit-job-description').val(),
            price: parseFloat($('#edit-job-price').val()),
            status: $('#edit-job-status').val(),
        };

        try {
            const response = await fetch('api/routes/admin/jobs/update_job.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': getAuthHeader()
                },
                body: JSON.stringify(updatedData)
            });
            const result = await response.json();

            if (result.status === 'success') {
                showToast(result.message || 'Job updated successfully!');
                editJobModal.modal('close');
                fetchJobs();
            } else {
                showToast(result.message || 'Failed to update job.', 'red');
            }
        } catch (error) {
            console.error('Update error:', error);
            showToast('Error updating job.', 'red');
        } finally {
            loader.hide();
        }
    });

    function confirmDeleteJob() {
        const jobId = $(this).data('id');
        const modal = document.createElement('div');
        modal.classList.add('modal');
        modal.innerHTML = `
            <div class="modal-content">
                <h4>Confirm Deletion</h4>
                <p>Are you sure you want to delete this job (ID: ${jobId})? This action is irreversible.</p>
            </div>
            <div class="modal-footer">
                <button class="modal-close waves-effect waves-green btn-flat">Cancel</button>
                <button class="waves-effect waves-red btn-flat red-text text-darken-4" id="confirm-delete-btn" data-id="${jobId}">Delete</button>
            </div>
        `;
        document.body.appendChild(modal);
        const instance = M.Modal.init(modal, {
            onCloseEnd: () => {
                modal.remove();
            }
        });
        instance.open();

        $('#confirm-delete-btn').on('click', async function () {
            loader.show();
            try {
                const response = await fetch('api/routes/admin/jobs/delete_job.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': getAuthHeader()
                    },
                    body: JSON.stringify({ id: jobId })
                });
                const result = await response.json();

                if (result.status === 'success') {
                    showToast(result.message || 'Job deleted successfully!', 'green');
                    fetchJobs();
                } else {
                    showToast(result.message || 'Failed to delete job.', 'red');
                }
            } catch (error) {
                console.error('Delete error:', error);
                showToast('Error deleting job.', 'red');
            } finally {
                loader.hide();
                instance.close();
            }
        });
    }

    if (getAuthHeader()) {
        fetchProfessionalsForDropdown();
        fetchJobs();
    } else {
        showToast('Authentication required to access job management.', 'red');
        setTimeout(() => window.location.href = 'index.html', 1500);
    }
});
