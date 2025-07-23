<?php
require_once '../../initialize.php';
header('Content-Type: application/json');

try {
    // Ensure POST method
    ApiHelper::requireMethod('POST');

    // Get JSON or form-data input
    $input = ApiHelper::getJsonInput();

    // Validate that 'id' is present
    ApiHelper::validateRequiredFields($input, ['id']);

    // Find booking by ID
    $booking = Bookings::findBookingById($input['id']);
    if (!$booking) {
        ApiHelper::sendJsonResponse([
            'status' => 'error',
            'message' => 'Booking not found.'
        ], 404);
    }

    // Merge only allowed fields
    $allowedFields = ['user_id', 'service_id', 'professional_id', 'status', 'appointment_date', 'total_price'];
    $updateData = array_intersect_key($input, array_flip($allowedFields));
    
    $booking->mergeAttributes($updateData);
    $booking->updated_at = date('Y-m-d H:i:s'); // Set updated timestamp

    // Validate the booking
    $errors = $booking->validate();
    if (!empty($errors)) {
        ApiHelper::sendJsonResponse([
            'status'  => 'error',
            'message' => 'Validation failed',
            'errors'  => $errors
        ], 400);
    }

    // Save the booking
    $result = $booking->save();
    ApiHelper::sendJsonResponse([
        'status'  => $result ? 'success' : 'error',
        'message' => $result ? 'Booking updated successfully.' : 'Failed to update booking.'
    ], $result ? 200 : 500);

} catch (Exception $e) {
    ApiHelper::sendJsonResponse([
        'status'  => 'error',
        'message' => 'Booking update failed.',
        'error'   => $e->getMessage()
    ], 500);
}
