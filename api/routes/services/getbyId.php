<?php
require_once '../../initialize.php';

try {
    // Ensure the request method is GET
    ApiHelper::requireMethod('GET');

    // Get the service ID from query parameters
    $serviceID = $_GET['id'] ?? null;

    if (empty($serviceID)) {
        ApiHelper::sendJsonResponse([
            'status'  => 'error',
            'message' => 'Service ID is required.'
        ], 400);
    }

    // Fetch service by ID
    $service = Services::findById($serviceID);

    if ($service) {
        ApiHelper::sendJsonResponse([
            'status'  => 'success',
            'service' => $service
        ], 200);
    } else {
        ApiHelper::sendJsonResponse([
            'status'  => 'error',
            'message' => 'Service not found.'
        ], 404);
    }

} catch (Exception $e) {
    ApiHelper::sendJsonResponse([
        'status'  => 'error',
        'message' => 'Failed to retrieve service.',
        'error'   => $e->getMessage()
    ], 500);
}
