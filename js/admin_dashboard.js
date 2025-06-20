document.addEventListener('DOMContentLoaded', function() {
    const token = localStorage.getItem('jwt_token');

    // Function to fetch dashboard statistics
    async function fetchDashboardStats() {
        try {
            // Placeholder for actual API calls for dashboard stats
            // In a real application, you'd have backend APIs for these counts
            // For now, we'll just simulate some numbers or leave as 0
            const usersCount = 150; // Example
            const prosCount = 50;   // Example
            const bookingsCount = 200; // Example
            const paymentsCount = 120; // Example

            $('#total-users').text(usersCount);
            $('#total-pros').text(prosCount);
            $('#total-bookings').text(bookingsCount);
            $('#total-payments').text(paymentsCount);

        } catch (error) {
            console.error('Error fetching dashboard stats:', error);
            M.toast({html: 'Failed to load dashboard statistics.', classes: 'red'});
        }
    }

   // Initial dashboard stats fetch on load
// Keep the if(token && localStorage.getItem('user_role') === 'admin') if you want to send token, but remove the else part
if (token && localStorage.getItem('user_role') === 'admin') {
    fetchDashboardStats();
}
// No else block here for redirect in previous version, but if there was, remove it.
});