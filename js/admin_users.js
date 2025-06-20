document.addEventListener('DOMContentLoaded', function() {
    const usersTableBody = $('#users-table tbody');
    const loader = $('#loader');
    const editUserModal = $('#edit-user-modal');
    const editUserForm = $('#edit-user-form');

    // Function to show toasts
    function showToast(message, type = 'green') {
        M.toast({html: message, classes: type});
    }

    // Function to get JWT token from local storage
    function getAuthHeader() {
        const token = localStorage.getItem('jwt_token');
        return token ? `Bearer ${token}` : '';
    }

    // Function to fetch and display users
    async function fetchUsers() {
        loader.show();
        try {
            const response = await fetch('api/routes/admin/users/get_users.php', {
                method: 'GET',
                headers: {
                    'Authorization': getAuthHeader()
                }
            });
            const result = await response.json();

            if (result.status === 'success') {
                displayUsers(result.data);
            } else {
                showToast(result.message || 'Failed to fetch users.', 'red');
                if (response.status === 401 || response.status === 403) {
                    // Redirect to login if unauthorized
                    setTimeout(() => window.location.href = 'index.html', 1500);
                }
            }
        } catch (error) {
            console.error('Error fetching users:', error);
            showToast('An error occurred while fetching users.', 'red');
        } finally {
            loader.hide();
        }
    }

    // Function to display users in the table
    function displayUsers(users) {
        usersTableBody.empty(); // Clear existing rows
        if (users.length === 0) {
            usersTableBody.append('<tr><td colspan="7" class="center-align">No users found.</td></tr>');
            return;
        }

        users.forEach(user => {
            const row = `
                <tr>
                    <td>${user.ID}</td>
                    <td>${user.NAME}</td>
                    <td>${user.EMAIL}</td>
                    <td>${user.PHONE || 'N/A'}</td>
                    <td>${user.ROLE}</td>
                    <td>${user.CREATED_AT}</td>
                    <td class="action-btns">
                        <button class="btn btn-small blue lighten-1 edit-btn" data-id="${user.ID}"
                            data-name="${user.NAME}" data-email="${user.EMAIL}"
                            data-phone="${user.PHONE}" data-role="${user.ROLE}">
                            <i class="material-icons">edit</i>
                        </button>
                        <button class="btn btn-small red darken-1 delete-btn" data-id="${user.ID}">
                            <i class="material-icons">delete</i>
                        </button>
                    </td>
                </tr>
            `;
            usersTableBody.append(row);
        });

        // Attach event listeners to new buttons
        $('.edit-btn').on('click', openEditModal);
        $('.delete-btn').on('click', confirmDeleteUser);
    }

    // Open edit modal and populate with user data
    function openEditModal() {
        const userId = $(this).data('id');
        const userName = $(this).data('name');
        const userEmail = $(this).data('email');
        const userPhone = $(this).data('phone');
        const userRole = $(this).data('role');

        $('#edit-user-id').val(userId);
        $('#edit-user-name').val(userName);
        $('#edit-user-email').val(userEmail);
        $('#edit-user-phone').val(userPhone);
        $('#edit-user-role').val(userRole);
        $('#edit-user-password').val(''); // Clear password field

        M.updateTextFields(); // Re-initialize Materialize input labels
        $('select').formSelect(); // Re-initialize Materialize selects

        editUserModal.modal('open');
    }

    // Handle edit form submission
    editUserForm.on('submit', async function(e) {
        e.preventDefault();
        loader.show();
        const userId = $('#edit-user-id').val();
        const updatedData = {
            ID: userId,
            NAME: $('#edit-user-name').val(),
            EMAIL: $('#edit-user-email').val(),
            PHONE: $('#edit-user-phone').val(),
            ROLE: $('#edit-user-role').val(),
        };

        const newPassword = $('#edit-user-password').val();
        if (newPassword) {
            updatedData.PASSWORD = newPassword;
        }

        try {
            const response = await fetch('api/routes/admin/users/update_user.php', {
                method: 'POST', // Using POST as per backend
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': getAuthHeader()
                },
                body: JSON.stringify(updatedData)
            });
            const result = await response.json();

            if (result.status === 'success') {
                showToast(result.message || 'User updated successfully!', 'green');
                editUserModal.modal('close');
                fetchUsers(); // Refresh the user list
            } else {
                showToast(result.message || 'Failed to update user.', 'red');
            }
        } catch (error) {
            console.error('Error updating user:', error);
            showToast('An error occurred while updating user.', 'red');
        } finally {
            loader.hide();
        }
    });

    // Confirm and delete user
    function confirmDeleteUser() {
        const userId = $(this).data('id');
        M.Modal.init(document.createElement('div'), {
            onOpenEnd: function(modal, trigger) {
                modal.innerHTML = `
                    <div class="modal-content">
                        <h4>Confirm Deletion</h4>
                        <p>Are you sure you want to delete this user (ID: ${userId})?</p>
                    </div>
                    <div class="modal-footer">
                        <button class="modal-close waves-effect waves-green btn-flat">Cancel</button>
                        <button class="waves-effect waves-red btn-flat red-text text-darken-4" id="confirm-delete-btn" data-id="${userId}">Delete</button>
                    </div>
                `;
                document.body.appendChild(modal);
                modal.M_Modal.open();

                $('#confirm-delete-btn').on('click', async function() {
                    loader.show();
                    try {
                        const response = await fetch('api/routes/admin/users/delete_user.php', {
                            method: 'POST', // Using POST as per backend
                            headers: {
                                'Content-Type': 'application/json',
                                'Authorization': getAuthHeader()
                            },
                            body: JSON.stringify({ ID: userId })
                        });
                        const result = await response.json();

                        if (result.status === 'success') {
                            showToast(result.message || 'User deleted successfully!', 'green');
                            fetchUsers(); // Refresh the user list
                        } else {
                            showToast(result.message || 'Failed to delete user.', 'red');
                        }
                    } catch (error) {
                        console.error('Error deleting user:', error);
                        showToast('An error occurred while deleting user.', 'red');
                    } finally {
                        loader.hide();
                        modal.M_Modal.close(); // Close the confirmation modal
                        $(modal).remove(); // Remove modal from DOM
                    }
                });
            }
        }).open();
    }


  // Initial fetch of users when the page loads
// Keep the if(getAuthHeader()) if you still want to send token, but remove the else part
if (getAuthHeader()) {
    fetchUsers();
}
// REMOVE OR COMMENT OUT THIS ELSE BLOCK
/*
else {
    showToast('Authentication required to access user management.', 'red');
    setTimeout(() => window.location.href = 'index.html', 1500); // Redirect to login
}
*/
});