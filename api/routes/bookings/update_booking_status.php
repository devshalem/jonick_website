<?php
require_once '../../initialize.php';
header('Content-Type: application/json');

try {
    // Ensure POST method
    ApiHelper::requireMethod('POST');

    // Get input (JSON or form data)
    $input = ApiHelper::getJsonInput();
    $bookingID = $input['id'] ?? null;
    $newStatus = $input['status'] ?? null;

    // Validate required fields
    ApiHelper::validateRequiredFields($input, ['id', 'status']);

    // Validate allowed statuses
    $allowedStatuses = ['pending', 'confirmed', 'completed', 'cancelled'];
    if (!in_array($newStatus, $allowedStatuses)) {
        ApiHelper::sendJsonResponse([
            'status'  => 'error',
            'message' => 'Invalid status provided. Allowed: ' . implode(', ', $allowedStatuses)
        ], 400);
    }

    // Update booking status
    $response = Bookings::updateStatus($bookingID, $newStatus);

    // Return response
    ApiHelper::sendJsonResponse($response, $response['status'] === 'success' ? 200 : 400);

} catch (Exception $e) {
    ApiHelper::sendJsonResponse([
        'status'  => 'error',
        'message' => 'Failed to update booking status',
        'error'   => $e->getMessage()
    ], 500);
}
