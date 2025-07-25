<?php
require_once '../../initialize.php';

try {
    // Ensure the request method is GET
    ApiHelper::requireMethod('GET');

    // Fetch all services
    $services = Services::allServices(); // Using the allServices method

    if ($services) {
        ApiHelper::sendJsonResponse([
            'status' => 'success',
            'data'   => $services
        ], 200);
    } else {
        ApiHelper::sendJsonResponse([
            'status'  => 'error',
            'message' => 'No services found.'
        ], 404);
    }

} catch (Exception $e) {
    ApiHelper::sendJsonResponse([
        'status'  => 'error',
        'message' => 'Failed to retrieve services.',
        'error'   => $e->getMessage()
    ], 500);
}
