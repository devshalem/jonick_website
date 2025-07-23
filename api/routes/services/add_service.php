<?php
require_once '../../initialize.php';

try {
    ApiHelper::requireMethod('POST');
    $input = ApiHelper::getJsonInput();

    if ($input === null) {
        ApiHelper::sendJsonResponse([
            'status'  => 'error',
            'message' => 'Invalid data input'
        ], 400);
    }

    // Call the createService method
    $response = Services::createService($input);

    // Ensure proper response structure
    if (!is_array($response) || !isset($response['status'])) {
        throw new Exception('Invalid response from Services::createService');
    }

    ApiHelper::sendJsonResponse($response, $response['status'] === 'success' ? 200 : 400);

} catch (Exception $e) {
    ApiHelper::sendJsonResponse([
        'status'  => 'error',
        'message' => 'Failed to create service.',
        'error'   => $e->getMessage()
    ], 500);
}
