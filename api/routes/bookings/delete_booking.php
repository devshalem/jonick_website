<?php
require_once '../../initialize.php';

try {
    // Allow only POST requests
    ApiHelper::requireMethod('POST');

    // Get JSON or form input
    $data = ApiHelper::getJsonInput();
    $bookingID = $data['id'] ?? null;

    // Validate Booking ID
    ApiHelper::validateRequiredFields($data, ['id']);

    // Check if booking exists
    $booking = Bookings::findById($bookingID);
    if (!$booking) {
        ApiHelper::sendJsonResponse([
            'status'  => 'error',
            'message' => 'Booking not found.'
        ], 404);
    }
    // Attempt to delete booking
    $result = $booking->delete();

    if ($result) {
        ApiHelper::sendJsonResponse([
            'status'  => 'success',
            'message' => 'Booking deleted successfully.'
        ], 200);
    } else {
        ApiHelper::sendJsonResponse([
            'status'  => 'error',
            'message' => 'Failed to delete booking.'
        ], 400);
    }

} catch (Exception $e) {
    ApiHelper::sendJsonResponse([
        'status'  => 'error',
        'message' => 'An error occurred while deleting the booking.',
        'error'   => $e->getMessage()
    ], 500);
}
