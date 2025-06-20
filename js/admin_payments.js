document.addEventListener('DOMContentLoaded', function() {
    const paymentsTableBody = $('#payments-table tbody');
    const loader = $('#loader');
    const editPaymentModal = $('#edit-payment-modal');
    const editPaymentForm = $('#edit-payment-form');

    function showToast(message, type = 'green') {
        M.toast({ html: message, classes: type });
    }

    // Initial dashboard stats fetch on load
    // Keep the if(token && localStorage.getItem('user_role') === 'admin') if you want to send token, but remove the else part
    if (token && localStorage.getItem('user_role') === 'admin') {
        fetchDashboardStats();
    }
// No else block here for redirect in previous version, but if there was, remove it.

    async function fetchPayments() {
        loader.show();
        try {
            const response = await fetch('api/routes/admin/payments/get_payments.php', {
                method: 'GET',
                headers: { 'Authorization': getAuthHeader() }
            });
            const result = await response.json();

            if (result.status === 'success') {
                displayPayments(result.data);
            } else {
                showToast(result.message || 'Failed to fetch payments.', 'red');
                if (response.status === 401 || response.status === 403) {
                    setTimeout(() => window.location.href = 'index.html', 1500);
                }
            }
        } catch (error) {
            console.error('Error fetching payments:', error);
            showToast('An error occurred while fetching payments.', 'red');
        } finally {
            loader.hide();
        }
    }

    function displayPayments(payments) {
        paymentsTableBody.empty();
        if (payments.length === 0) {
            paymentsTableBody.append('<tr><td colspan="9" class="center-align">No payments found.</td></tr>');
            return;
        }

        payments.forEach(payment => {
            const statusClass = payment.status.toLowerCase();
            const row = `
                <tr>
                    <td>${payment.id}</td>
                    <td>${payment.user_name || 'N/A'} (${payment.user_email || 'N/A'})</td>
                    <td>${payment.job_id ? (payment.job_description || 'Job ID: ' + payment.job_id) : 'N/A'}</td>
                    <td>$${parseFloat(payment.amount).toFixed(2)}</td>
                    <td><span class="status-badge ${statusClass}">${payment.status.toUpperCase()}</span></td>
                    <td>${payment.transaction_id || 'N/A'}</td>
                    <td>${payment.method || 'N/A'}</td>
                    <td>${payment.created_at}</td>
                    <td class="action-btns">
                        <button class="btn btn-small blue lighten-1 edit-btn" 
                            data-id="${payment.id}"
                            data-user-name="${payment.user_name}"
                            data-job-description="${payment.job_description}"
                            data-amount="${payment.amount}"
                            data-status="${payment.status}"
                            data-transaction-id="${payment.transaction_id}"
                            data-method="${payment.method}">
                            <i class="material-icons">edit</i> Edit
                        </button>
                        <button class="btn btn-small red darken-1 delete-btn" data-id="${payment.id}">
                            <i class="material-icons">delete</i> Delete
                        </button>
                    </td>
                </tr>
            `;
            paymentsTableBody.append(row);
        });

        $('.edit-btn').on('click', openEditModal);
        $('.delete-btn').on('click', confirmDeletePayment);
    }

    function openEditModal() {
        const paymentId = $(this).data('id');
        const userName = $(this).data('user-name');
        const jobDescription = $(this).data('job-description');
        const amount = parseFloat($(this).data('amount')).toFixed(2);
        const status = $(this).data('status');
        const transactionId = $(this).data('transaction-id');
        const method = $(this).data('method');

        $('#edit-payment-id').val(paymentId);
        $('#edit-payment-user-name').val(userName);
        $('#edit-payment-job-description').val(jobDescription);
        $('#edit-payment-amount').val(amount);
        $('#edit-payment-transaction-id').val(transactionId);
        $('#edit-payment-method').val(method);
        $('#edit-payment-status').val(status);

        M.updateTextFields();
        $('select').formSelect();

        editPaymentModal.modal('open');
    }

    editPaymentForm.on('submit', async function(e) {
        e.preventDefault();
        loader.show();
        const paymentId = $('#edit-payment-id').val();
        const updatedData = {
            id: paymentId,
            amount: parseFloat($('#edit-payment-amount').val()),
            status: $('#edit-payment-status').val(),
            transaction_id: $('#edit-payment-transaction-id').val(),
            method: $('#edit-payment-method').val(),
        };

        try {
            const response = await fetch('api/routes/admin/payments/update_payment.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': getAuthHeader()
                },
                body: JSON.stringify(updatedData)
            });
            const result = await response.json();

            if (result.status === 'success') {
                showToast(result.message || 'Payment updated successfully!', 'green');
                editPaymentModal.modal('close');
                fetchPayments();
            } else {
                showToast(result.message || 'Failed to update payment.', 'red');
            }
        } catch (error) {
            console.error('Error updating payment:', error);
            showToast('An error occurred while updating payment.', 'red');
        } finally {
            loader.hide();
        }
    });

    function confirmDeletePayment() {
        const paymentId = $(this).data('id');
        const modalDiv = document.createElement('div');

        const modalInstance = M.Modal.init(modalDiv, {
            onOpenEnd: function() {
                modalDiv.innerHTML = `
                    <div class="modal-content">
                        <h4>Confirm Deletion</h4>
                        <p>Are you sure you want to delete this payment (ID: ${paymentId})? This action is irreversible.</p>
                    </div>
                    <div class="modal-footer">
                        <button class="modal-close waves-effect waves-green btn-flat">Cancel</button>
                        <button class="waves-effect waves-red btn-flat red-text text-darken-4" id="confirm-delete-btn" data-id="${paymentId}">Delete</button>
                    </div>
                `;
                document.body.appendChild(modalDiv);
                modalInstance.open();

                $('#confirm-delete-btn').on('click', async function() {
                    loader.show();
                    try {
                        const response = await fetch('api/routes/admin/payments/delete_payment.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Authorization': getAuthHeader()
                            },
                            body: JSON.stringify({ id: paymentId })
                        });
                        const result = await response.json();

                        if (result.status === 'success') {
                            showToast(result.message || 'Payment deleted successfully!', 'green');
                            fetchPayments();
                        } else {
                            showToast(result.message || 'Failed to delete payment.', 'red');
                        }
                    } catch (error) {
                        console.error('Error deleting payment:', error);
                        showToast('An error occurred while deleting payment.', 'red');
                    } finally {
                        loader.hide();
                        modalInstance.close();
                        $(modalDiv).remove();
                    }
                });
            }
        });

        modalInstance.open();
    }

    if (getAuthHeader()) {
        fetchPayments();
    } else {
        showToast('Authentication required to access payment management.', 'red');
        setTimeout(() => window.location.href = 'index.html', 1500);
    }
});
