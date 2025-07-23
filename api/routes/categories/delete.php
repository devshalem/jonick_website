<?php
require_once '../../initialize.php';

try {
    ApiHelper::requireMethod('POST');
    $input = ApiHelper::getJsonInput();

    ApiHelper::validateRequiredFields($input, ['id']);
    $categoryID = $input['id'];

    $response = Categories::deleteCategory($categoryID);

    if (!is_array($response) || !isset($response['status'])) {
        throw new Exception('Invalid response from Categories::deleteCategory');
    }

    ApiHelper::sendJsonResponse($response, $response['status'] === 'success' ? 200 : 400);

} catch (Exception $e) {
    ApiHelper::sendJsonResponse([
        'status'  => 'error',
        'message' => 'Failed to delete category.',
        'error'   => $e->getMessage()
    ], 500);
}
