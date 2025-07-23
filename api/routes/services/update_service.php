<?php
require_once '../../initialize.php';

try {
    ApiHelper::requireMethod('POST');
    $input = ApiHelper::getJsonInput();

    // Validate required field
    ApiHelper::validateRequiredFields($input, ['id']);
    $serviceID = $input['id'];
    unset($input['id']); // Remove ID from data array for update

    // Call the updateService method
    $response = Services::updateService($serviceID, $input);

    // Validate response structure
    if (!is_array($response) || !isset($response['status'])) {
        throw new Exception('Invalid response from Services::updateService');
    }

    ApiHelper::sendJsonResponse($response, $response['status'] === 'success' ? 200 : 400);

} catch (Exception $e) {
    ApiHelper::sendJsonResponse([
        'status'  => 'error',
        'message' => 'Failed to update service.',
        'error'   => $e->getMessage()
    ], 500);
}
