<?php
require_once '../../initialize.php';

try {
    // Only allow GET requests
    ApiHelper::requireMethod('GET');

    // Check for optional booking ID
    $bookingId = isset($_GET['id']) ? (int)$_GET['id'] : null;

    // Fetch bookings (all or by ID)
    $bookings = Bookings::getBookings($bookingId);

    // Handle no records found
    if (!$bookings) {
        ApiHelper::sendJsonResponse([
            'status'  => 'error',
            'message' => $bookingId ? 'Booking not found' : 'No bookings found'
        ], statusCode: 404);
    }

    // Return success response
    ApiHelper::sendJsonResponse([
        'status' => 'success',
        'data'   => $bookings
    ], 200);

} catch (Exception $e) {
    // Handle unexpected errors
    ApiHelper::sendJsonResponse([
        'status'  => 'error',
        'message' => 'Failed to fetch booking(s)',
        'error'   => $e->getMessage()
    ], 500);
}
