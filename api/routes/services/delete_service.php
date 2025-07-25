<?php
require_once '../../initialize.php';

try {
    ApiHelper::requireMethod('POST');
    $input = ApiHelper::getJsonInput();

    // Validate required field
    ApiHelper::validateRequiredFields($input, ['id']);
    $serviceID = $input['id'];

    // Call the deleteService method
    $response = Services::deleteService($serviceID);

    // Ensure proper response structure
    if (!is_array($response) || !isset($response['status'])) {
        throw new Exception('Invalid response from Services::deleteService');
    }

    ApiHelper::sendJsonResponse($response, $response['status'] === 'success' ? 200 : 400);

} catch (Exception $e) {
    ApiHelper::sendJsonResponse([
        'status'  => 'error',
        'message' => 'Failed to delete service.',
        'error'   => $e->getMessage()
    ], 500);
}
