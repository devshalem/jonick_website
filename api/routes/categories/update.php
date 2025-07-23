<?php
require_once '../../initialize.php';

try {
    ApiHelper::requireMethod('POST');
    $input = ApiHelper::getJsonInput();

    // Validate required fields
    ApiHelper::validateRequiredFields($input, ['id']);
    $categoryID = $input['id'];
    unset($input['id']); // Remove ID from update data

    $response = Categories::updateCategory($categoryID, $input);

    if (!is_array($response) || !isset($response['status'])) {
        throw new Exception('Invalid response from Categories::updateCategory');
    }

    ApiHelper::sendJsonResponse($response, $response['status'] === 'success' ? 200 : 400);

} catch (Exception $e) {
    ApiHelper::sendJsonResponse([
        'status'  => 'error',
        'message' => 'Failed to update category.',
        'error'   => $e->getMessage()
    ], 500);
}
