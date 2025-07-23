<?php
require_once '../../initialize.php';

try {
    ApiHelper::requireMethod('POST');
    $input = ApiHelper::getJsonInput();

    // Validate required fields
    ApiHelper::validateRequiredFields($input, ['name']);

    // Create category
    $response = Categories::createCategory($input);

    if (!is_array($response) || !isset($response['status'])) {
        throw new Exception('Invalid response from Categories::createCategory');
    }

    ApiHelper::sendJsonResponse($response, $response['status'] === 'success' ? 201 : 400);

} catch (Exception $e) {
    ApiHelper::sendJsonResponse([
        'status'  => 'error',
        'message' => 'Failed to create category.',
        'error'   => $e->getMessage()
    ], 500);
}
