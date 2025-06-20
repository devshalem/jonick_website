document.addEventListener('DOMContentLoaded', function() {
    const servicesTableBody = $('#services-table tbody');
    const loader = $('#loader');
    const addServiceModal = $('#add-service-modal');
    const addServiceForm = $('#add-service-form');
    const editServiceModal = $('#edit-service-modal');
    const editServiceForm = $('#edit-service-form');

    // Show toast notifications
    function showToast(message, type = 'green') {
        M.toast({ html: message, classes: type });
    }

    // Initial dashboard stats fetch on load
        // Keep the if(token && localStorage.getItem('user_role') === 'admin') if you want to send token, but remove the else part
        if (token && localStorage.getItem('user_role') === 'admin') {
            fetchDashboardStats();
        }
// No else block here for redirect in previous version, but if there was, remove it.

    // Fetch and display services
    async function fetchServices() {
        loader.show();
        try {
            const response = await fetch('api/routes/admin/services/get_services.php', {
                method: 'GET',
                headers: {
                    'Authorization': getAuthHeader()
                }
            });
            const result = await response.json();

            if (result.status === 'success') {
                displayServices(result.data);
            } else {
                showToast(result.message || 'Failed to fetch services.', 'red');
                if (response.status === 401 || response.status === 403) {
                    setTimeout(() => window.location.href = 'index.html', 1500);
                }
            }
        } catch (error) {
            console.error('Error fetching services:', error);
            showToast('An error occurred while fetching services.', 'red');
        } finally {
            loader.hide();
        }
    }

    // Display services in table
    function displayServices(services) {
        servicesTableBody.empty();

        if (services.length === 0) {
            servicesTableBody.append('<tr><td colspan="7" class="center-align">No services found.</td></tr>');
            return;
        }

        services.forEach(service => {
            const row = `
                <tr>
                    <td>${service.id}</td>
                    <td>${service.name}</td>
                    <td>${service.description.length > 70 ? service.description.substring(0, 70) + '...' : service.description}</td>
                    <td>$${parseFloat(service.price).toFixed(2)}</td>
                    <td>${service.category_id || 'N/A'}</td>
                    <td>${service.created_at}</td>
                    <td class="action-btns">
                        <button class="btn btn-small blue lighten-1 edit-btn" 
                            data-id="${service.id}"
                            data-name="${service.name}"
                            data-description="${service.description}"
                            data-price="${service.price}"
                            data-category-id="${service.category_id}">
                            <i class="material-icons">edit</i> Edit
                        </button>
                        <button class="btn btn-small red darken-1 delete-btn" data-id="${service.id}">
                            <i class="material-icons">delete</i> Delete
                        </button>
                    </td>
                </tr>
            `;
            servicesTableBody.append(row);
        });

        $('.edit-btn').on('click', openEditModal);
        $('.delete-btn').on('click', confirmDeleteService);
    }

    // Add service form submission
    addServiceForm.on('submit', async function(e) {
        e.preventDefault();
        loader.show();

        const newServiceData = {
            name: $('#add-service-name').val(),
            description: $('#add-service-description').val(),
            price: parseFloat($('#add-service-price').val()),
            category_id: parseInt($('#add-service-category-id').val()),
        };

        try {
            const response = await fetch('api/routes/admin/services/add_service.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': getAuthHeader()
                },
                body: JSON.stringify(newServiceData)
            });

            const result = await response.json();

            if (result.status === 'success') {
                showToast(result.message || 'Service added successfully!');
                addServiceModal.modal('close');
                addServiceForm.trigger('reset');
                M.textareaAutoResize($('#add-service-description'));
                fetchServices();
            } else {
                showToast(result.message || 'Failed to add service.', 'red');
            }
        } catch (error) {
            console.error('Error adding service:', error);
            showToast('An error occurred while adding service.', 'red');
        } finally {
            loader.hide();
        }
    });

    // Open edit modal
    function openEditModal() {
        const serviceId = $(this).data('id');
        $('#edit-service-id').val(serviceId);
        $('#edit-service-name').val($(this).data('name'));
        $('#edit-service-description').val($(this).data('description'));
        $('#edit-service-price').val(parseFloat($(this).data('price')).toFixed(2));
        $('#edit-service-category-id').val($(this).data('category-id'));

        M.updateTextFields();
        M.textareaAutoResize($('#edit-service-description'));
        editServiceModal.modal('open');
    }

    // Edit service form submission
    editServiceForm.on('submit', async function(e) {
        e.preventDefault();
        loader.show();

        const updatedData = {
            id: $('#edit-service-id').val(),
            name: $('#edit-service-name').val(),
            description: $('#edit-service-description').val(),
            price: parseFloat($('#edit-service-price').val()),
            category_id: parseInt($('#edit-service-category-id').val()),
        };

        try {
            const response = await fetch('api/routes/admin/services/update_service.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': getAuthHeader()
                },
                body: JSON.stringify(updatedData)
            });

            const result = await response.json();

            if (result.status === 'success') {
                showToast(result.message || 'Service updated successfully!');
                editServiceModal.modal('close');
                fetchServices();
            } else {
                showToast(result.message || 'Failed to update service.', 'red');
            }
        } catch (error) {
            console.error('Error updating service:', error);
            showToast('An error occurred while updating service.', 'red');
        } finally {
            loader.hide();
        }
    });

    // Delete service confirmation
    function confirmDeleteService() {
        const serviceId = $(this).data('id');

        const modalElem = document.createElement('div');
        modalElem.classList.add('modal');
        modalElem.innerHTML = `
            <div class="modal-content">
                <h4>Confirm Deletion</h4>
                <p>Are you sure you want to delete this service (ID: <strong>${serviceId}</strong>)?</p>
            </div>
            <div class="modal-footer">
                <button class="modal-close waves-effect waves-green btn-flat">Cancel</button>
                <button class="waves-effect waves-red btn-flat red-text text-darken-4" id="confirm-delete-btn" data-id="${serviceId}">Delete</button>
            </div>
        `;

        document.body.appendChild(modalElem);
        const modalInstance = M.Modal.init(modalElem, {
            onCloseEnd: () => {
                modalElem.remove();
            }
        });

        modalInstance.open();

        $(modalElem).on('click', '#confirm-delete-btn', async function() {
            loader.show();
            try {
                const response = await fetch('api/routes/admin/services/delete_service.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': getAuthHeader()
                    },
                    body: JSON.stringify({ id: serviceId })
                });

                const result = await response.json();

                if (result.status === 'success') {
                    showToast(result.message || 'Service deleted successfully!');
                    fetchServices();
                } else {
                    showToast(result.message || 'Failed to delete service.', 'red');
                }
            } catch (error) {
                console.error('Error deleting service:', error);
                showToast('An error occurred while deleting service.', 'red');
            } finally {
                loader.hide();
                modalInstance.close();
            }
        });
    }

    // Initial fetch
    if (getAuthHeader()) {
        fetchServices();
    } else {
        showToast('Authentication required to access service management.', 'red');
        setTimeout(() => window.location.href = 'index.html', 1500);
    }
});
