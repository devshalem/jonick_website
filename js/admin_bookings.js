document.addEventListener('DOMContentLoaded', function() {
    const bookingsTableBody = $('#bookings-table tbody');
    const loader = $('#loader');
    const editBookingModal = $('#edit-booking-modal');
    const editBookingForm = $('#edit-booking-form');
    const professionalSelect = $('#edit-booking-professional-id');

    // Store all professionals for easy lookup
    let allProfessionals = [];

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

    // Fetch all professionals for the dropdown in the edit modal
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
                allProfessionals = result.data.filter(pro => pro.status === 'approved'); // Only approved professionals
                populateProfessionalDropdown(allProfessionals);
            } else {
                console.error('Failed to fetch professionals for dropdown:', result.message);
                showToast('Failed to load professionals for dropdown.', 'red');
            }
        } catch (error) {
            console.error('Error fetching professionals for dropdown:', error);
            showToast('An error occurred while loading professionals.', 'red');
        }
    }

    // Populate the professional dropdown
    function populateProfessionalDropdown(professionals) {
        professionalSelect.empty();
        professionalSelect.append('<option value="" disabled selected>Assign Professional</option>');
        professionals.forEach(pro => {
            professionalSelect.append(`<option value="${pro.id}">${pro.user_name} (${pro.expertise})</option>`);
        });
        $('select').formSelect(); // Re-initialize Materialize selects
    }


    // Function to fetch and display bookings
    async function fetchBookings() {
        loader.show();
        try {
            const response = await fetch('api/routes/admin/bookings/get_bookings.php', {
                method: 'GET',
                headers: {
                    'Authorization': getAuthHeader()
                }
            });
            const result = await response.json();

            if (result.status === 'success') {
                displayBookings(result.data);
            } else {
                showToast(result.message || 'Failed to fetch bookings.', 'red');
                if (response.status === 401 || response.status === 403) {
                    setTimeout(() => window.location.href = 'index.html', 1500);
                }
            }
        } catch (error) {
            console.error('Error fetching bookings:', error);
            showToast('An error occurred while fetching bookings.', 'red');
        } finally {
            loader.hide();
        }
    }

    // Function to display bookings in the table
    function displayBookings(bookings) {
        bookingsTableBody.empty(); // Clear existing rows
        if (bookings.length === 0) {
            bookingsTableBody.append('<tr><td colspan="8" class="center-align">No bookings found.</td></tr>');
            return;
        }

        bookings.forEach(booking => {
            const statusClass = booking.status; // pending, confirmed, completed, cancelled
            const row = `
                <tr>
                    <td>${booking.id}</td>
                    <td>${booking.user_name || 'N/A'} (${booking.user_email || 'N/A'})</td>
                    <td>${booking.service_name || 'N/A'}</td>
                    <td>${booking.professional_id ? (allProfessionals.find(p => p.id === booking.professional_id)?.user_name || 'Assigned') : 'Not Assigned'}</td>
                    <td>${booking.appointment_date}</td>
                    <td>$${parseFloat(booking.total_price).toFixed(2)}</td>
                    <td><span class="status-badge ${statusClass}">${booking.status.toUpperCase()}</span></td>
                    <td class="action-btns">
                        <button class="btn btn-small blue lighten-1 edit-btn" data-id="${booking.id}"
                            data-user-id="${booking.user_id}" data-service-id="${booking.service_id}"
                            data-professional-id="${booking.professional_id}" data-status="${booking.status}"
                            data-date="${booking.appointment_date}" data-price="${booking.total_price}"
                            data-user-name="${booking.user_name}" data-service-name="${booking.service_name}">
                            <i class="material-icons">edit</i> Edit
                        </button>
                        <div class="input-field inline" style="margin: 0; min-width: 120px;">
                            <select class="status-select" data-id="${booking.id}">
                                <option value="" disabled>Change Status</option>
                                <option value="pending" ${booking.status === 'pending' ? 'selected' : ''}>Pending</option>
                                <option value="confirmed" ${booking.status === 'confirmed' ? 'selected' : ''}>Confirmed</option>
                                <option value="completed" ${booking.status === 'completed' ? 'selected' : ''}>Completed</option>
                                <option value="cancelled" ${booking.status === 'cancelled' ? 'selected' : ''}>Cancelled</option>
                            </select>
                        </div>
                        <button class="btn btn-small red darken-1 delete-btn" data-id="${booking.id}">
                            <i class="material-icons">delete</i> Delete
                        </button>
                    </td>
                </tr>
            `;
            bookingsTableBody.append(row);
        });

        // Re-initialize Materialize selects for dynamically added rows
        $('select').formSelect();
        $('.edit-btn').on('click', openEditModal);
        $('.delete-btn').on('click', confirmDeleteBooking);
        $('.status-select').on('change', function() {
            const bookingId = $(this).data('id');
            const newStatus = $(this).val();
            confirmChangeStatus(bookingId, newStatus);
        });
    }

    // Open edit modal and populate with booking data
    function openEditModal() {
        const bookingId = $(this).data('id');
        const userId = $(this).data('user-id');
        const serviceId = $(this).data('service-id');
        const professionalId = $(this).data('professional-id');
        const status = $(this).data('status');
        const date = $(this).data('date');
        const price = parseFloat($(this).data('price')).toFixed(2);
        const userName = $(this).data('user-name');
        const serviceName = $(this).data('service-name');

        $('#edit-booking-id').val(bookingId);
        $('#edit-booking-user-id').val(userId);
        $('#edit-booking-service-id').val(serviceId);
        $('#edit-booking-user-name').val(userName);
        $('#edit-booking-service-name').val(serviceName);

        // Set the datepicker value
        M.Datepicker.getInstance($('#edit-booking-date')).setDate(new Date(date));
        $('#edit-booking-date').val(date); // Set value directly for consistency

        $('#edit-booking-total-price').val(price);
        $('#edit-booking-status').val(status);

        // Set professional dropdown
        professionalSelect.val(professionalId);

        M.updateTextFields(); // Re-initialize Materialize input labels
        $('select').formSelect(); // Re-initialize Materialize selects

        editBookingModal.modal('open');
    }

    // Handle edit form submission
    editBookingForm.on('submit', async function(e) {
        e.preventDefault();
        loader.show();
        const bookingId = $('#edit-booking-id').val();
        const updatedData = {
            id: bookingId,
            user_id: $('#edit-booking-user-id').val(),
            service_id: $('#edit-booking-service-id').val(),
            professional_id: professionalSelect.val() === '' ? null : professionalSelect.val(), // Send null if no professional selected
            status: $('#edit-booking-status').val(),
            appointment_date: $('#edit-booking-date').val(),
            total_price: parseFloat($('#edit-booking-total-price').val()),
        };

        try {
            const response = await fetch('api/routes/admin/bookings/update_booking.php', {
                method: 'POST', // Using POST as per backend
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': getAuthHeader()
                },
                body: JSON.stringify(updatedData)
            });
            const result = await response.json();

            if (result.status === 'success') {
                showToast(result.message || 'Booking updated successfully!', 'green');
                editBookingModal.modal('close');
                fetchBookings(); // Refresh the list
            } else {
                showToast(result.message || 'Failed to update booking.', 'red');
            }
        } catch (error) {
            console.error('Error updating booking:', error);
            showToast('An error occurred while updating booking.', 'red');
        } finally {
            loader.hide();
        }
    });

    // Confirm Change Status
    function confirmChangeStatus(bookingId, newStatus) {
        M.Modal.init(document.createElement('div'), {
            onOpenEnd: function(modal, trigger) {
                modal.innerHTML = `
                    <div class="modal-content">
                        <h4>Confirm Status Change</h4>
                        <p>Are you sure you want to change the status of booking (ID: ${bookingId}) to <strong>${newStatus.toUpperCase()}</strong>?</p>
                    </div>
                    <div class="modal-footer">
                        <button class="modal-close waves-effect waves-green btn-flat">Cancel</button>
                        <button class="waves-effect waves-green btn-flat green-text text-darken-4" id="confirm-status-change-btn" data-id="${bookingId}" data-status="${newStatus}">Confirm</button>
                    </div>
                `;
                document.body.appendChild(modal);
                modal.M_Modal.open();

                $('#confirm-status-change-btn').on('click', async function() {
                    loader.show();
                    try {
                        const response = await fetch('api/routes/admin/bookings/update_booking_status.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Authorization': getAuthHeader()
                            },
                            body: JSON.stringify({ id: bookingId, status: newStatus })
                        });
                        const result = await response.json();

                        if (result.status === 'success') {
                            showToast(result.message || 'Booking status updated successfully!', 'green');
                            fetchBookings(); // Refresh the list
                        } else {
                            showToast(result.message || 'Failed to update booking status.', 'red');
                        }
                    } catch (error) {
                        console.error('Error updating booking status:', error);
                        showToast('An error occurred while updating booking status.', 'red');
                    } finally {
                        loader.hide();
                        modal.M_Modal.close();
                        $(modal).remove();
                    }
                });
            }
        }).open();
    }

    // Confirm and delete booking
    function confirmDeleteBooking() {
        const bookingId = $(this).data('id');
        M.Modal.init(document.createElement('div'), {
            onOpenEnd: function(modal, trigger) {
                modal.innerHTML = `
                    <div class="modal-content">
                        <h4>Confirm Deletion</h4>
                        <p>Are you sure you want to delete this booking (ID: ${bookingId})? This action is irreversible.</p>
                    </div>
                    <div class="modal-footer">
                        <button class="modal-close waves-effect waves-green btn-flat">Cancel</button>
                        <button class="waves-effect waves-red btn-flat red-text text-darken-4" id="confirm-delete-btn" data-id="${bookingId}">Delete</button>
                    </div>
                `;
                document.body.appendChild(modal);
                modal.M_Modal.open();

                $('#confirm-delete-btn').on('click', async function() {
                    loader.show();
                    try {
                        const response = await fetch('api/routes/admin/bookings/delete_booking.php', {
                            method: 'POST', // Using POST as per backend
                            headers: {
                                'Content-Type': 'application/json',
                                'Authorization': getAuthHeader()
                            },
                            body: JSON.stringify({ id: bookingId })
                        });
                        const result = await response.json();

                        if (result.status === 'success') {
                            showToast(result.message || 'Booking deleted successfully!', 'green');
                            fetchBookings(); // Refresh the list
                        } else {
                            showToast(result.message || 'Failed to delete booking.', 'red');
                        }
                    } catch (error) {
                        console.error('Error deleting booking:', error);
                        showToast('An error occurred while deleting booking.', 'red');
                    } finally {
                        loader.hide();
                        modal.M_Modal.close();
                        $(modal).remove();
                    }
                });
            }
        }).open();
    }

    // Initial fetch of professionals and bookings when the page loads
    if (getAuthHeader()) { // Only fetch if token exists (simple client-side check)
        fetchProfessionalsForDropdown(); // Fetch professionals first
        fetchBookings(); // Then fetch bookings
    } else {
        showToast('Authentication required to access booking management.', 'red');
        setTimeout(() => window.location.href = 'index.html', 1500); // Redirect to login
    }
});