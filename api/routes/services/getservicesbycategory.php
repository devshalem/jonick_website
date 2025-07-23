<?php
require_once '../../initialize.php';

try {
    ApiHelper::requireMethod('POST');
    $input = ApiHelper::getJsonInput();

    // Validate required field
    ApiHelper::validateRequiredFields($input, ['category_id']);
    $categoryID = $input['category_id'];
    $services = Services::findServicesByCategory($categoryID);
    if (!empty($services)) {
        ApiHelper::sendJsonResponse([
            'status' => 'success',
            'data'   => $services
        ], 200);
    } else {
        ApiHelper::sendJsonResponse([
            'status'  => 'error',
            'message' => 'No services found for this category.'
        ], 404);
    }

} catch (Exception $e) {
    ApiHelper::sendJsonResponse([
        'status'  => 'error',
        'message' => 'Failed to fetch services by category.',
        'error'   => $e->getMessage()
    ], 500);
}
